<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 *  controller Регистра ИПРА (индивидуальная программа реабилитации и абилитации)
 *
 * @package			IPRA
 * @author			Васинский Игорь 
 * @version			24.02.2016
 */

class IPRARegister extends swController
{
    var $model = "IPRARegister_model";
    var $crazy = array('ufa' => array(11, 12, 13, 14, 16));
    var $MorbusType_id = 90;
    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model($this->model, 'dbmodel');
        
        $this->inputRules = array(
            'getMCElist' => array(),
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
            'IPRARegistry_save' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MorbusType_code',
                    'label' => 'MorbusType_code',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'searchRecomendation_text',
                    'label' => 'фильтр',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'IPRARegistry_import' => array(
                array(
                    'field' => 'IPRARegistry_import',
                    'label' => 'Файл Zip',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'IPRARegistryDopImport' => array(
                array(
                    'field' => 'IPRARegistry_import',
                    'label' => 'Файл Zip',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'getLpuAttachData' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'getPerson_id' => array(
                array(
                    'field' => 'Person_FirName',
                    'label' => 'Person_FirName',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_surName',
                    'label' => 'Person_surName',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_secName',
                    'label' => 'Person_secName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_BirthDay',
                    'label' => 'Person_BirthDay',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_Snils',
                    'label' => 'Person_Snils',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Document_Num',
                    'label' => 'Document_Num',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'getAllBureau' => array(),
            
            'getIdentityPacient' => array(
                 array(
                    'field' => 'idx',
                    'label' => 'idx',
                    'rules' => 'required',
                    'type' => 'int'
                ),               
                array(
                    'field' => 'Person_FirName',
                    'label' => 'Person_FirName',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_SecName',
                    'label' => 'Person_SecName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_BirthDate',
                    'label' => 'Person_BirthDate',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_Snils',
                    'label' => 'Person_Snils',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Document_Num',
                    'label' => 'Document_Num',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRAData_DirectionLPU_id',
                    'label' => 'IPRAData_DirectionLPU_id',
                    'rules' => '',
                    'type' => 'string'
                )
                
            ),
            
            'checkIPRAdataIsValid' => array(
                array(
                    'field' => 'idx',
                    'label' => 'idx',
                    'rules' => 'required',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_SelfService',
                    'label' => 'IPRARegistryData_SelfService',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Move',
                    'label' => 'IPRARegistryData_Move',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Orientation',
                    'label' => 'IPRARegistryData_Orientation',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Communicate',
                    'label' => 'IPRARegistryData_Communicate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Learn',
                    'label' => 'IPRARegistryData_Learn',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Work',
                    'label' => 'IPRARegistryData_Work',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Behavior',
                    'label' => 'IPRARegistryData_Behavior',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_isFirst',
                    'label' => 'Первичный или нет',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_issueDate',
                    'label' => 'Дата выписки ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_FGUMCEnumber',
                    'label' => 'Номер бюро МСЭ',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
                    'field' => 'IPRARegistry_Number',
                    'label' => 'Номер ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_Protocol',
                    'label' => 'Номер протокола ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_ProtocolDate',
                    'label' => 'Дата протокола ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_DevelopDate',
                    'label' => 'Дата разработки ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab',
                    'label' => 'IPRARegistryData_MedRehab',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab_begDate',
                    'label' => 'IPRARegistryData_MedRehab_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg',
                    'label' => 'IPRARegistryData_ReconstructSurg',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg_begDate',
                    'label' => 'IPRARegistryData_ReconstructSurg_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics',
                    'label' => 'IPRARegistryData_Orthotics',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics_begDate',
                    'label' => 'IPRARegistryData_Orthotics_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Restoration',
                    'label' => 'IPRARegistryData_Restoration',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Compensate',
                    'label' => 'IPRARegistryData_Compensate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrimaryProfession',
                    'label' => 'IPRARegistryData_PrimaryProfession',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_PrimaryProfessionExperience',
                    'label' => 'IPRARegistryData_PrimaryProfessionExperience',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Qualification',
                    'label' => 'IPRARegistryData_Qualification',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_CurrentJob',
                    'label' => 'IPRARegistryData_CurrentJob',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_NotWorkYears',
                    'label' => 'IPRARegistryData_NotWorkYears',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_EmploymentOrientationExists',
                    'label' => 'IPRARegistryData_EmploymentOrientationExists',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_IsRegisteredInEmploymentService',
                    'label' => 'IPRARegistryData_IsRegisteredInEmploymentService',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityGroup',
                    'label' => 'IPRARegistryData_DisabilityGroup',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityCause',
                    'label' => 'IPRARegistryData_DisabilityCause',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityGroupDate',
                    'label' => 'IPRARegistryData_DisabilityGroupDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_IsDisabilityGroupPrimary',
                    'label' => 'IPRARegistryData_IsDisabilityGroupPrimary',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityEndDate',
                    'label' => 'IPRARegistryData_DisabilityEndDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_SentOrgOGRN',
                    'label' => 'IPRARegistryData_SentOrgOGRN',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_SentOrgName',
                    'label' => 'IPRARegistryData_SentOrgName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RehabPotential',
                    'label' => 'IPRARegistryData_RehabPotential',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_RehabPrognoz',
                    'label' => 'IPRARegistryData_RehabPrognoz',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_IsIntramural',
                    'label' => 'IPRARegistryData_IsIntramural',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozSelfService',
                    'label' => 'IPRARegistryData_PrognozSelfService',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozMoveIndependetly',
                    'label' => 'IPRARegistryData_PrognozMoveIndependetly',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozOrientate',
                    'label' => 'IPRARegistryData_PrognozOrientate',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozCommunicate',
                    'label' => 'IPRARegistryData_PrognozCommunicate',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozBehaviorControl',
                    'label' => 'IPRARegistryData_PrognozBehaviorControl',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozLearning',
                    'label' => 'IPRARegistryData_PrognozLearning',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozWork',
                    'label' => 'IPRARegistryData_PrognozWork',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_Version',
                    'label' => 'IPRARegistry_Version',
                    'rules' => '',
                    'type'  => 'float'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_LastName',
                    'label' => 'IPRARegistryData_RepPerson_LastName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_FirstName',
                    'label' => 'IPRARegistryData_RepPerson_FirstName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_SecondName',
                    'label' => 'IPRARegistryData_RepPerson_SecondName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Title',
                    'label' => 'IPRARegistryData_RepPersonAD_Title',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Series',
                    'label' => 'IPRARegistryData_RepPersonAD_Series',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Number',
                    'label' => 'IPRARegistryData_RepPersonAD_Number',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Issuer',
                    'label' => 'IPRARegistryData_RepPersonAD_Issuer',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_IssueDate',
                    'label' => 'IPRARegistryData_RepPersonAD_IssueDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Title',
                    'label' => 'IPRARegistryData_RepPersonID_Title',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Series',
                    'label' => 'IPRARegistryData_RepPersonID_Series',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Number',
                    'label' => 'IPRARegistryData_RepPersonID_Number',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Issuer',
                    'label' => 'IPRARegistryData_RepPersonID_Issuer',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_IssueDate',
                    'label' => 'IPRARegistryData_RepPersonID_IssueDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_SNILS',
                    'label' => 'IPRARegistryData_RepPerson_SNILS',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityCauseOther',
                    'label' => 'IPRARegistryData_DisabilityCauseOther',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
			
            'IPRARegistry_ins' => array(
                //Если редактирование ошибок, то по этому ID удалить все данные из регистра - по сути это старый IPRARegistry_id
                array(
                    'field' => 'IPRARegistryEditError_id',
                    'label' => 'IPRARegistryEditError_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                        'field' => 'IPRARegistry_IPRAident',
                        'label' => 'Ключ ИПРА',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_Number',
                    'label' => 'Номер ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_issueDate',
                    'label' => 'Дата выписки ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_EndDate',
                    'label' => 'Срок ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_FGUMCEnumber',
                    'label' => 'Номер бюро МСЭ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_RecepientType',
                    'label' => 'Тип получачателя',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_Protocol',
                    'label' => 'Номер протокола ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_ProtocolDate',
                    'label' => 'Дата протокола ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_DevelopDate',
                    'label' => 'Дата разработки ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_isFirst',
                    'label' => 'Первичный или нет',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_Confirm',
                    'label' => 'Подтверждение от МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_DirectionLPU_id',
                    'label' => 'МО направления на МСЭ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_FileName',
                    'label' => 'Имя файла',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'МО соправождения пациента',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_SelfService',
                    'label' => 'IPRARegistryData_SelfService',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Move',
                    'label' => 'IPRARegistryData_Move',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Orientation',
                    'label' => 'IPRARegistryData_Orientation',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Communicate',
                    'label' => 'IPRARegistryData_Communicate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Learn',
                    'label' => 'IPRARegistryData_Learn',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Work',
                    'label' => 'IPRARegistryData_Work',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Behavior',
                    'label' => 'IPRARegistryData_Behavior',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab',
                    'label' => 'IPRARegistryData_MedRehab',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab_begDate',
                    'label' => 'IPRARegistryData_MedRehab_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab_endDate',
                    'label' => 'IPRARegistryData_MedRehab_endDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics',
                    'label' => 'IPRARegistryData_Orthotics',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics_begDate',
                    'label' => 'IPRARegistryData_Orthotics_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics_endDate',
                    'label' => 'IPRARegistryData_Orthotics_endDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg',
                    'label' => 'IPRARegistryData_ReconstructSurg',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg_begDate',
                    'label' => 'IPRARegistryData_ReconstructSurg_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg_endDate',
                    'label' => 'IPRARegistryData_ReconstructSurg_endDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Restoration',
                    'label' => 'IPRARegistryData_Restoration',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Compensate',
                    'label' => 'IPRARegistryData_Compensate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryError_SurName',
                    'label' => 'IPRARegistryError_SurName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryError_FirName',
                    'label' => 'IPRARegistryError_FirName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryError_SecName',
                    'label' => 'IPRARegistryError_SecName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryError_BirthDay',
                    'label' => 'IPRARegistryError_BirthDay',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_PrimaryProfession',
                    'label' => 'IPRARegistryData_PrimaryProfession',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_PrimaryProfessionExperience',
                    'label' => 'IPRARegistryData_PrimaryProfessionExperience',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Qualification',
                    'label' => 'IPRARegistryData_Qualification',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_CurrentJob',
                    'label' => 'IPRARegistryData_CurrentJob',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_NotWorkYears',
                    'label' => 'IPRARegistryData_NotWorkYears',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_EmploymentOrientationExists',
                    'label' => 'IPRARegistryData_EmploymentOrientationExists',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_IsRegisteredInEmploymentService',
                    'label' => 'IPRARegistryData_IsRegisteredInEmploymentService',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityGroup',
                    'label' => 'IPRARegistryData_DisabilityGroup',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityCause',
                    'label' => 'IPRARegistryData_DisabilityCause',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityGroupDate',
                    'label' => 'IPRARegistryData_DisabilityGroupDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_IsDisabilityGroupPrimary',
                    'label' => 'IPRARegistryData_IsDisabilityGroupPrimary',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityEndDate',
                    'label' => 'IPRARegistryData_DisabilityEndDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_SentOrgOGRN',
                    'label' => 'IPRARegistryData_SentOrgOGRN',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_SentOrgName',
                    'label' => 'IPRARegistryData_SentOrgName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RehabPotential',
                    'label' => 'IPRARegistryData_RehabPotential',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_RehabPrognoz',
                    'label' => 'IPRARegistryData_RehabPrognoz',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_IsIntramural',
                    'label' => 'IPRARegistryData_IsIntramural',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozSelfService',
                    'label' => 'IPRARegistryData_PrognozSelfService',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozMoveIndependetly',
                    'label' => 'IPRARegistryData_PrognozMoveIndependetly',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozOrientate',
                    'label' => 'IPRARegistryData_PrognozOrientate',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozCommunicate',
                    'label' => 'IPRARegistryData_PrognozCommunicate',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozBehaviorControl',
                    'label' => 'IPRARegistryData_PrognozBehaviorControl',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozLearning',
                    'label' => 'IPRARegistryData_PrognozLearning',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozWork',
                    'label' => 'IPRARegistryData_PrognozWork',
                    'rules' => '',
                    'type'  => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_Version',
                    'label' => 'IPRARegistry_Version',
                    'rules' => '',
                    'type'  => 'float'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_LastName',
                    'label' => 'IPRARegistryData_RepPerson_LastName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_FirstName',
                    'label' => 'IPRARegistryData_RepPerson_FirstName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_SecondName',
                    'label' => 'IPRARegistryData_RepPerson_SecondName',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Title',
                    'label' => 'IPRARegistryData_RepPersonAD_Title',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Series',
                    'label' => 'IPRARegistryData_RepPersonAD_Series',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Number',
                    'label' => 'IPRARegistryData_RepPersonAD_Number',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_Issuer',
                    'label' => 'IPRARegistryData_RepPersonAD_Issuer',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonAD_IssueDate',
                    'label' => 'IPRARegistryData_RepPersonAD_IssueDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Title',
                    'label' => 'IPRARegistryData_RepPersonID_Title',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Series',
                    'label' => 'IPRARegistryData_RepPersonID_Series',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Number',
                    'label' => 'IPRARegistryData_RepPersonID_Number',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_Issuer',
                    'label' => 'IPRARegistryData_RepPersonID_Issuer',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPersonID_IssueDate',
                    'label' => 'IPRARegistryData_RepPersonID_IssueDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_SNILS',
                    'label' => 'IPRARegistryData_RepPerson_SNILS',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityCauseOther',
                    'label' => 'IPRARegistryData_DisabilityCauseOther',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
            'saveInRegisterIPRA' => array(
                array(
                    'field' => 'jsondata',
                    'label' => 'jsondata',
                    'rules' => 'required',
                    'type' => 'string'
                )
            ),
            'IPRARegistry_upd' => array(
                array(
                    'field' => 'IPRARegistry_id',
                    'label' => 'IPRARegistry_id',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'IPRARegistry_IPRAident',
					'label' => 'Ключ ИПРА',
					'rules' => '',
					'type' => 'string'
				),
                array(
                    'field' => 'IPRARegistry_Number',
                    'label' => 'Номер ИПРА',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_issueDate',
                    'label' => 'Дата выписки ИПРА',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_EndDate',
                    'label' => 'Срок ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_FGUMCEnumber',
                    'label' => 'Номер бюро МСЭ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_RecepientType',
                    'label' => 'Тип получателя',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_Protocol',
                    'label' => 'Номер протокола ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_ProtocolDate',
                    'label' => 'Дата протокола ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_DevelopDate',
                    'label' => 'Дата разработки ИПРА',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_isFirst',
                    'label' => 'Первичный или нет',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistry_Confirm',
                    'label' => 'Подтверждение от МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_DirectionLPU_id',
                    'label' => 'МО направления на МСЭ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistry_FileName',
                    'label' => 'Имя файла',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'МО соправождения пациента',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_SelfService',
                    'label' => 'IPRARegistryData_SelfService',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Move',
                    'label' => 'IPRARegistryData_Move',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Orientation',
                    'label' => 'IPRARegistryData_Orientation',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Communicate',
                    'label' => 'IPRARegistryData_Communicate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Learn',
                    'label' => 'IPRARegistryData_Learn',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Work',
                    'label' => 'IPRARegistryData_Work',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Behavior',
                    'label' => 'IPRARegistryData_Behavior',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab',
                    'label' => 'IPRARegistryData_MedRehab',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab_begDate',
                    'label' => 'IPRARegistryData_MedRehab_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_MedRehab_endDate',
                    'label' => 'IPRARegistryData_MedRehab_endDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics',
                    'label' => 'IPRARegistryData_Orthotics',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics_begDate',
                    'label' => 'IPRARegistryData_Orthotics_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Orthotics_endDate',
                    'label' => 'IPRARegistryData_Orthotics_endDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg',
                    'label' => 'IPRARegistryData_ReconstructSurg',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg_begDate',
                    'label' => 'IPRARegistryData_ReconstructSurg_begDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_ReconstructSurg_endDate',
                    'label' => 'IPRARegistryData_ReconstructSurg_endDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Restoration',
                    'label' => 'IPRARegistryData_Restoration',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'IPRARegistryData_Compensate',
                    'label' => 'IPRARegistryData_Compensate',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'IPRARegistryData_PrimaryProfession',
                    'label' => 'IPRARegistryData_PrimaryProfession',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_PrimaryProfessionExperience',
                    'label' => 'IPRARegistryData_PrimaryProfessionExperience',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_Qualification',
                    'label' => 'IPRARegistryData_Qualification',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_CurrentJob',
                    'label' => 'IPRARegistryData_CurrentJob',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_NotWorkYears',
                    'label' => 'IPRARegistryData_NotWorkYears',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_ExistEmploymentOrientation',
                    'label' => 'IPRARegistryData_ExistEmploymentOrientation',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_isRegInEmplService',
                    'label' => 'IPRARegistryData_isRegInEmplService',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityGroup',
                    'label' => 'IPRARegistryData_DisabilityGroup',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityCause',
                    'label' => 'IPRARegistryData_DisabilityCause',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityGroupDate',
                    'label' => 'IPRARegistryData_DisabilityGroupDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_IsDisabilityGroupPrimary',
                    'label' => 'IPRARegistryData_IsDisabilityGroupPrimary',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityEndDate',
                    'label' => 'IPRARegistryData_DisabilityEndDate',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RehabPotential',
                    'label' => 'IPRARegistryData_RehabPotential',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_RehabPrognoz',
                    'label' => 'IPRARegistryData_RehabPrognoz',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_IsIntramural',
                    'label' => 'IPRARegistryData_IsIntramural',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozResult_SelfService',
                    'label' => 'IPRARegistryData_PrognozResult_SelfService',
                    'rules' => '',
                    'type' => 'int'
                ),
               array(
                    'field' => 'IPRARegistryData_PrognozResult_Independently',
                    'label' => 'IPRARegistryData_PrognozResult_Independently',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozResult_Orientate',
                    'label' => 'IPRARegistryData_PrognozResult_Orientate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozResult_Communicate',
                    'label' => 'IPRARegistryData_PrognozResult_Communicate',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozResult_BehaviorControl',
                    'label' => 'IPRARegistryData_PrognozResult_BehaviorControl',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozResult_Learning',
                    'label' => 'IPRARegistryData_PrognozResult_Learning',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IPRARegistryData_PrognozResult_Work',
                    'label' => 'IPRARegistryData_PrognozResult_Work',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_LastName',
                    'label' => 'IPRARegistryData_RepPerson_LastName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_FirstName',
                    'label' => 'IPRARegistryData_RepPerson_FirstName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_SecondName',
                    'label' => 'IPRARegistryData_RepPerson_SecondName',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_SNILS',
                    'label' => 'IPRARegistryData_RepPerson_SNILS',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_IdentifyDocType',
                    'label' => 'IPRARegistryData_RepPerson_IdentifyDocType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_IdentifyDocDep',
                    'label' => 'IPRARegistryData_RepPerson_IdentifyDocDep',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_IdentifyDocSeries',
                    'label' => 'IPRARegistryData_RepPerson_IdentifyDocSeries',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_IdentifyDocNum',
                    'label' => 'IPRARegistryData_RepPerson_IdentifyDocNum',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_IdentifyDocDate',
                    'label' => 'IPRARegistryData_RepPerson_IdentifyDocDate',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_DisabilityCauseOther',
                    'label' => 'IPRARegistryData_DisabilityCauseOther',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_AuthorityDocType',
                    'label' => 'IPRARegistryData_RepPerson_AuthorityDocType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_AuthorityDocDep',
                    'label' => 'IPRARegistryData_RepPerson_AuthorityDocDep',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_AuthorityDocSeries',
                    'label' => 'IPRARegistryData_RepPerson_AuthorityDocSeries',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_AuthorityDocNum',
                    'label' => 'IPRARegistryData_RepPerson_AuthorityDocNum',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'IPRARegistryData_RepPerson_AuthorityDocDate',
                    'label' => 'IPRARegistryData_RepPerson_AuthorityDocDate',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
            
            'getAllIPRARegistry' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'required',
                    'type' => 'int'
                )
            ),
            'getIPRARegistry' => array(
                array(
                    'field' => 'IPRARegistry_id',
                    'label' => 'IPRARegistry_id',
                    'rules' => 'required',
                    'type' => 'int'
                )
            ),
            'getIPRARegistryErrors' => array(
                array(
                    'field' => 'start',
                    'label' => 'start',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'limit',
                    'label' => 'limit',
                    'rules' => '',
                    'type' => 'int'
                ),                
                array(
                    'field' => 'sort',
                    'label' => 'sort',
                    'rules' => '',
                    'type' => 'string'
                ),                
                array(
                    'field' => 'dir',
                    'label' => 'dir',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'getMOAddressOgrn' => array(
                array(
                    'field' => 'DirectionLpu_id',
                    'label' => 'DirectionLpu_id',
                    'rules' => '',
                    'type'  => 'int'
                )
            )
        );
    }
    /**
     *  получения списка всех ИПРА с ошибками
     */
    function getIPRARegistryErrors()
    {
        $data = $this->ProcessInputData('getIPRARegistryErrors', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getIPRARegistryErrors($data);
        $this->ReturnData($response);
    }
    
    /**
     *  Сохранение регистра ИПРА (добавление данный в IPRARegistry)
     */
    function IPRARegistry_ins()
    {
        $data = $this->ProcessInputData('IPRARegistry_ins', true);
        if ($data === false) {
            return false;
        }

		//формируем флаг валидности данных ИПРА
		$data['IPRAData_isValid'] = $this->IPRAdataIsValid($data);
		//1 - Данные в норме
		//2 - Данные не заполнены
		
		//log_message('error', 'IPRARegistry_ins data:');
		//log_message('error', serialize($data));
        $response = $this->dbmodel->IPRARegistry_ins($data);
        //$this->ReturnData($response); 
        return $response;
    }
    
    /**
     *  Получение списка номеров ИПРА по пациенту
     */
    function getAllIPRARegistry()
    {
        $data = $this->ProcessInputData('getAllIPRARegistry', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getAllIPRARegistry($data);
        $this->ReturnData($response);
    }
    
    /**
     *  Получение выписки ИПРА по пациенту
     */
    function getIPRARegistry()
    {
        $data = $this->ProcessInputData('getIPRARegistry', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getIPRARegistry($data);
        $this->ReturnData($response);
    }
    
    /**
     *  Сохранение регистра ИПРА (редактирование данный в IPRARegistry)
     */
    function IPRARegistry_upd()
    {
        $data = $this->ProcessInputData('IPRARegistry_upd', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->IPRARegistry_upd($data);
        $this->ReturnData($response);
    }
    
    /**
     *  Получение ОГРН и Адреса МО
     */
    function getMOAddressOgrn()
    {
        $data = $this->ProcessInputData('getMOAddressOgrn', true);
        if ($data === false) {
            return false;
        }
        $response = $this->dbmodel->getMOAddressOgrn($data);
        $this->ReturnData($response);
    }
    
    /**
     *  Сохранение регистра ИПРА (полное)
     */
    function saveInRegisterIPRA()
    {
        $data = $this->ProcessInputData('saveInRegisterIPRA', true);
        if ($data === false) {
            return false;
        }

        $dataArray    = json_decode($data['jsondata'], 1);
        $errorsErrors = array();
        $errorsData   = array();
        //echo '<pre>' . print_r($dataArray, 1) . '</pre>';
        //exit;
        foreach ($dataArray as $type => $d) {
            //Пишем данные в Регистр ИПРА + даннаые в таблицу данных регистра
            if ($type == 'data') {
                foreach ($d as $k => $v) {

                    if (!empty($v['Person_id'])) {
                        /**
                         *  Добавление в PersonRegister
                         */
                        $_POST = array(
                                'Person_id' => $v['Person_id'],
                                'MorbusType_id' => 90,
                                'Diag_id' => null,
                                'PersonRegister_Code' => null,
                                'PersonRegister_setDate' => date('Y-m-d'),
                                'PersonRegister_disDate' => null,
                                'Morbus_id' => null,
                                'PersonRegisterOutCause_id' => null,
                                'MedPersonal_iid' => $data['session']['medpersonal_id'],
                                'Lpu_iid' => $data['Lpu_id'],
                                'MedPersonal_did' => null,
                                'Lpu_did' => null,
                                'EvnNotifyBase_id' => null,
                                'pmUser_id' => $data['pmUser_id']
                        );

                        $result = $this->saveInPersonRegister();

                        if ($result[0]['Error_Message'] != '') {

                                $IPRANumber = isset($v['IPRAData_Number']) ? $v['IPRAData_Number'] : $v['IPRARegistry_Number'];
                                $errors[$IPRANumber] = $result[0]['Error_Message'];
                        }

                        if(!empty($errors)){
                                $this->ReturnData(array(
                                        'success' => false,
                                        'Error_Code' => -1,
                                        'Error_Msg' => toUTF('Произошла ошибка при работе с БД.'),
                                        'ErrorText' => $result[0]['Error_Message'],
                                        'FullTextError'=>json_encode($errors)
                                ));
                                exit();
                        }
                    }

                    $_POST = array();
                    if (isset($v['IPRAData_Number'])) {
                        $_POST['IPRARegistry_IPRAident']                   = (empty($v['IPRAData_IPRAident'])) ? null : $v['IPRAData_IPRAident'];
                        $_POST['IPRARegistry_Number']                      = (empty($v['IPRAData_Number'])) ? null : $v['IPRAData_Number'];
                        $_POST['IPRARegistry_issueDate']                   = $v['IPRAData_issueDate'];
                        $_POST['IPRARegistry_EndDate']                     = $v['IPRAData_EndDate'];
                        $_POST['IPRARegistry_FGUMCEnumber']                = $v['IPRAData_FGUMCEnumber'];
                        $_POST['IPRARegistry_RecepientType']               = (empty($v['IPRAData_RecepientType'])) ? null : $v['IPRAData_RecepientType'];
                        $_POST['IPRARegistry_Protocol']                    = $v['IPRAData_Protocol'];
                        $_POST['IPRARegistry_ProtocolDate']                = $v['IPRAData_ProtocolDate'];
                        $_POST['IPRARegistry_DevelopDate']                 = $v['IPRAData_DevelopDate'];
                        $_POST['IPRARegistry_isFirst']                     = in_array($v['IPRAData_isFirst'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistry_Confirm']                     = $v['IPRAData_Confirm'];
                        $_POST['IPRARegistry_DirectionLPU_id']             = $v['IPRAData_DirectionLPU_id'];
                        $_POST['IPRARegistry_FileName']                    = !empty($v['filename'])?$v['filename']:null;
                        $_POST['Lpu_id']                                   = ($v['Lpu_id'] == '') ? null : $v['Lpu_id'];
                        $_POST['Person_id']                                = ($v['Person_id'] == '') ? null : $v['Person_id'];
                        $_POST['IPRARegistryData_SelfService']             = $v['IPRAData_SelfService'];
                        $_POST['IPRARegistryData_Move']                    = $v['IPRAData_Move'];
                        $_POST['IPRARegistryData_Orientation']             = $v['IPRAData_Orientation'];
                        $_POST['IPRARegistryData_Communicate']             = $v['IPRAData_Communicate'];
                        $_POST['IPRARegistryData_Learn']                   = $v['IPRAData_Learn'];
                        $_POST['IPRARegistryData_Work']                    = $v['IPRAData_Work'];
                        $_POST['IPRARegistryData_Behavior']                = $v['IPRAData_Behavior'];
                        $_POST['IPRARegistryData_MedRehab']                = in_array($v['IPRAData_MedRehab'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistryData_MedRehab_begDate']        = !empty($v['IPRAData_MedRehab_begDate']) ? $v['IPRAData_MedRehab_begDate'] : null;
                        $_POST['IPRARegistryData_MedRehab_endDate']        = !empty($v['IPRAData_MedRehab_endDate']) ? $v['IPRAData_MedRehab_endDate'] : null;
                        $_POST['IPRARegistryData_Orthotics']               = in_array($v['IPRAData_Orthotics'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistryData_Orthotics_begDate']       = !empty($v['IPRAData_Orthotics_begDate']) ? $v['IPRAData_Orthotics_begDate'] : null;
                        $_POST['IPRARegistryData_Orthotics_endDate']       = !empty($v['IPRAData_Orthotics_endDate']) ? $v['IPRAData_Orthotics_endDate'] : null;
                        $_POST['IPRARegistryData_ReconstructSurg']         = in_array($v['IPRAData_ReconstructSurg'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistryData_ReconstructSurg_begDate'] = !empty($v['IPRAData_ReconstructSurg_begDate']) ? $v['IPRAData_ReconstructSurg_begDate'] : null;
                        $_POST['IPRARegistryData_ReconstructSurg_endDate'] = !empty($v['IPRAData_ReconstructSurg_endDate']) ? $v['IPRAData_ReconstructSurg_endDate'] : null;
                        $_POST['IPRARegistryData_Restoration']             = !empty($v['IPRAData_Restoration']) ? $v['IPRAData_Restoration'] : null;
                        $_POST['IPRARegistryData_Compensate']              = !empty($v['IPRAData_Compensate']) ? $v['IPRAData_Compensate'] : null;
                        $_POST['IPRARegistryError_SurName']                = !empty($v['Person_SurName']) ? $v['Person_SurName'] : null;
                        $_POST['IPRARegistryError_FirName']                = !empty($v['Person_FirName']) ? $v['Person_FirName'] : null;
                        $_POST['IPRARegistryError_SecName']                = !empty($v['Person_SecName']) ? $v['Person_SecName'] : null;
                        $_POST['IPRARegistryError_BirthDay']               = !empty($v['IPRAData_BirthDate']) ? $v['IPRAData_BirthDate'] : null;
                        $_POST['pmUser_id']   = $data['pmUser_id'];
                        $_POST['IPRARegistry_Version']                          = !empty($v['IPRAData_Version'])                         ? $v['IPRAData_Version'] : null;
                        $_POST['IPRARegistryData_PrimaryProfession']                = !empty($v['IPRAData_PrimaryProfession'])               ? $v['IPRAData_PrimaryProfession'] : '';
                        $_POST['IPRARegistryData_PrimaryProfessionExperience']      = !empty($v['IPRAData_PrimaryProfessionExp'])            ? $v['IPRAData_PrimaryProfessionExp'] : '';
                        $_POST['IPRARegistryData_Qualification']                    = !empty($v['IPRAData_Qualification'])                   ? $v['IPRAData_Qualification'] : '';
                        $_POST['IPRARegistryData_CurrentJob']                       = !empty($v['IPRAData_CurrentJob'])                      ? $v['IPRAData_CurrentJob'] : '';
                        $_POST['IPRARegistryData_NotWorkYears']                     = !empty($v['IPRAData_NotWorkYears'])                    ? $v['IPRAData_NotWorkYears'] : '';
                        $_POST['IPRARegistryData_EmploymentOrientationExists']      = !empty($v['IPRAData_EmploymentOrientationExists'])     ? $v['IPRAData_EmploymentOrientationExists'] : null;
                        $_POST['IPRARegistryData_IsRegisteredInEmploymentService']  = !empty($v['IPRAData_IsRegisteredInEmploymentService']) ? $v['IPRAData_IsRegisteredInEmploymentService'] : null;
                        $_POST['IPRARegistryData_DisabilityGroup']                  = !empty($v['IPRAData_DisabilityGroup'])                 ? $v['IPRAData_DisabilityGroup'] : null;
                        $_POST['IPRARegistryData_DisabilityCause']                  = !empty($v['IPRAData_DisabilityCause'])                 ? $v['IPRAData_DisabilityCause'] : null;
                        $_POST['IPRARegistryData_DisabilityCauseOther']             = !empty($v['IPRAData_DisabilityCauseOther'])            ? $v['IPRAData_DisabilityCauseOther'] : '';
                        $_POST['IPRARegistryData_DisabilityGroupDate']              = !empty($v['IPRAData_DisabilityGroupDate'])             ? $v['IPRAData_DisabilityGroupDate'] : null;
                        $_POST['IPRARegistryData_IsDisabilityGroupPrimary']         = !empty($v['IPRAData_IsDisabilityGroupPrimary'])        ? $v['IPRAData_IsDisabilityGroupPrimary'] : null;
                        $_POST['IPRARegistryData_DisabilityEndDate']                = !empty($v['IPRAData_DisabilityEndDate'])               ? $v['IPRAData_DisabilityEndDate'] : null;
                        $_POST['IPRARegistryData_SentOrgOGRN']                      = !empty($v['IPRAData_SentOrgOGRN'])                     ? $v['IPRAData_SentOrgOGRN'] : '';
                        $_POST['IPRARegistryData_SentOrgName']                      = !empty($v['IPRAData_SentOrgName'])                     ? $v['IPRAData_SentOrgName'] : '';
                        $_POST['IPRARegistryData_RehabPotential']                   = !empty($v['IPRAData_RehabPotential'])                  ? $v['IPRAData_RehabPotential'] : null;
                        $_POST['IPRARegistryData_RehabPrognoz']                     = !empty($v['IPRAData_RehabPrognoz'])                    ? $v['IPRAData_RehabPrognoz'] : null;
                        $_POST['IPRARegistryData_IsIntramural']                     = !empty($v['IPRAData_IsIntramural'])                    ? $v['IPRAData_IsIntramural'] : null;
                        $_POST['IPRARegistryData_PrognozSelfService']               = !empty($v['IPRAData_PrognozSelfService'])              ? $v['IPRAData_PrognozSelfService'] : null;
                        $_POST['IPRARegistryData_PrognozMoveIndependetly']          = !empty($v['IPRAData_PrognozMoveIndependetly'])         ? $v['IPRAData_PrognozMoveIndependetly'] : null;
                        $_POST['IPRARegistryData_PrognozOrientate']                 = !empty($v['IPRAData_PrognozOrientate'])                ? $v['IPRAData_PrognozOrientate'] : null;
                        $_POST['IPRARegistryData_PrognozCommunicate']               = !empty($v['IPRAData_PrognozCommunicate'])              ? $v['IPRAData_PrognozCommunicate'] : null;
                        $_POST['IPRARegistryData_PrognozBehaviorControl']           = !empty($v['IPRAData_PrognozBehaviorControl'])          ? $v['IPRAData_PrognozBehaviorControl'] : null;
                        $_POST['IPRARegistryData_PrognozLearning']                  = !empty($v['IPRAData_PrognozLearning'])                 ? $v['IPRAData_PrognozLearning'] : null;
                        $_POST['IPRARegistryData_PrognozWork']                      = !empty($v['IPRAData_PrognozWork'])                     ? $v['IPRAData_PrognozWork'] : null;
                        $_POST['IPRARegistryData_RepPerson_LastName']               = !empty($v['IPRAData_RepPerson_LastName'])              ? $v['IPRAData_RepPerson_LastName'] : '';
                        $_POST['IPRARegistryData_RepPerson_FirstName']              = !empty($v['IPRAData_RepPerson_FirstName'])             ? $v['IPRAData_RepPerson_FirstName'] : '';
                        $_POST['IPRARegistryData_RepPerson_SecondName']             = !empty($v['IPRAData_RepPerson_SecondName'])            ? $v['IPRAData_RepPerson_SecondName'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Title']                = !empty($v['IPRAData_RepPersonAD_Title'])               ? $v['IPRAData_RepPersonAD_Title'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Series']               = !empty($v['IPRAData_RepPersonAD_Series'])              ? $v['IPRAData_RepPersonAD_Series'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Number']               = !empty($v['IPRAData_RepPersonAD_Number'])              ? $v['IPRAData_RepPersonAD_Number'] : null;
                        $_POST['IPRARegistryData_RepPersonAD_Issuer']               = !empty($v['IPRAData_RepPersonAD_Issuer'])              ? $v['IPRAData_RepPersonAD_Issuer'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_IssueDate']            = !empty($v['IPRAData_RepPersonAD_IssueDate'])           ? $v['IPRAData_RepPersonAD_IssueDate'] : null;
                        $_POST['IPRARegistryData_RepPersonID_Title']                = !empty($v['IPRAData_RepPersonID_Title'])               ? $v['IPRAData_RepPersonID_Title'] : '';
                        $_POST['IPRARegistryData_RepPersonID_Series']               = !empty($v['IPRAData_RepPersonID_Series'])              ? $v['IPRAData_RepPersonID_Series'] : '';
                        $_POST['IPRARegistryData_RepPersonID_Number']               = !empty($v['IPRAData_RepPersonID_Number'])              ? $v['IPRAData_RepPersonID_Number'] : null;
                        $_POST['IPRARegistryData_RepPersonID_Issuer']               = !empty($v['IPRAData_RepPersonID_Issuer'])              ? $v['IPRAData_RepPersonID_Issuer'] : '';
                        $_POST['IPRARegistryData_RepPersonID_IssueDate']            = !empty($v['IPRAData_RepPersonID_IssueDate'])           ? $v['IPRAData_RepPersonID_IssueDate'] : null;
                        $_POST['IPRARegistryData_RepPerson_SNILS']                  = !empty($v['IPRAData_RepPerson_SNILS'])                 ? $v['IPRAData_RepPerson_SNILS'] : null;   
                    } else {
                        $_POST = $v;
                        $_POST['IPRARegistry_isFirst']                     = in_array($v['IPRARegistry_isFirst'],array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistry_FileName']                    = !empty($v['filename'])?$v['filename']:null;
                        $_POST['Lpu_id']                                   = ($v['Lpu_id'] == '') ? null : $v['Lpu_id'];
                        $_POST['Person_id']                                = ($v['Person_id'] == '') ? null : $v['Person_id'];
                        $_POST['IPRARegistryData_SelfService']             = $v['IPRARegistryData_SelfService'];
                        $_POST['IPRARegistryData_Move']                    = $v['IPRARegistryData_Move'];
                        $_POST['IPRARegistryData_Orientation']             = $v['IPRARegistryData_Orientation'];
                        $_POST['IPRARegistryData_Communicate']             = $v['IPRARegistryData_Communicate'];
                        $_POST['IPRARegistryData_Learn']                   = $v['IPRARegistryData_Learn'];
                        $_POST['IPRARegistryData_Work']                    = $v['IPRARegistryData_Work'];
                        $_POST['IPRARegistryData_Behavior']                = $v['IPRARegistryData_Behavior'];
                        $_POST['IPRARegistryData_MedRehab']                = in_array($v['IPRARegistryData_MedRehab'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistryData_MedRehab_begDate']        = !empty($v['IPRARegistryData_MedRehab_begDate']) ? $v['IPRARegistryData_MedRehab_begDate'] : null;
                        $_POST['IPRARegistryData_MedRehab_endDate']        = !empty($v['IPRARegistryData_MedRehab_endDate']) ? $v['IPRARegistryData_MedRehab_endDate'] : null;
                        $_POST['IPRARegistryData_Orthotics']               = in_array($v['IPRARegistryData_Orthotics'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistryData_Orthotics_begDate']       = !empty($v['IPRARegistryData_Orthotics_begDate']) ? $v['IPRARegistryData_Orthotics_begDate'] : null;
                        $_POST['IPRARegistryData_Orthotics_endDate']       = !empty($v['IPRARegistryData_Orthotics_endDate']) ? $v['IPRARegistryData_Orthotics_endDate'] : null;
                        $_POST['IPRARegistryData_ReconstructSurg']         = in_array($v['IPRARegistryData_ReconstructSurg'], array('true', 2)) ? 2 : 1;
                        $_POST['IPRARegistryData_ReconstructSurg_begDate'] = !empty($v['IPRARegistryData_ReconstructSurg_begDate']) ? $v['IPRARegistryData_ReconstructSurg_begDate'] : null;
                        $_POST['IPRARegistryData_ReconstructSurg_endDate'] = !empty($v['IPRARegistryData_ReconstructSurg_endDate']) ? $v['IPRARegistryData_ReconstructSurg_endDate'] : null;
                        $_POST['IPRARegistryData_Restoration']             = !empty($v['IPRARegistryData_Restoration']) ? $v['IPRARegistryData_Restoration'] : null;
                        $_POST['IPRARegistryData_Compensate']              = !empty($v['IPRARegistryData_Compensate']) ? $v['IPRARegistryData_Compensate'] : null;
                        $_POST['IPRARegistryError_SurName']                = !empty($v['Person_SurName']) ? $v['Person_SurName'] : null;
                        $_POST['IPRARegistryError_FirName']                = !empty($v['Person_FirName']) ? $v['Person_FirName'] : null;
                        $_POST['IPRARegistryError_SecName']                = !empty($v['Person_SecName']) ? $v['Person_SecName'] : null;
                        $_POST['IPRARegistryError_BirthDay']               = !empty($v['IPRARegistryData_BirthDate']) ? $v['IPRARegistryData_BirthDate'] : null;
                        $_POST['IPRARegistry_Version']                          = !empty($v['IPRARegistry_Version'])                         ? $v['IPRARegistry_Version'] : null;
                        $_POST['IPRARegistryData_PrimaryProfession']                = !empty($v['IPRARegistryData_PrimaryProfession'])               ? $v['IPRARegistryData_PrimaryProfession'] : '';
                        $_POST['IPRARegistryData_PrimaryProfessionExperience']      = !empty($v['IPRARegistryData_PrimaryProfessionExperience'])     ? $v['IPRARegistryData_PrimaryProfessionExperience'] : '';
                        $_POST['IPRARegistryData_Qualification']                    = !empty($v['IPRARegistryData_Qualification'])                   ? $v['IPRARegistryData_Qualification'] : '';
                        $_POST['IPRARegistryData_CurrentJob']                       = !empty($v['IPRARegistryData_CurrentJob'])                      ? $v['IPRARegistryData_CurrentJob'] : '';
                        $_POST['IPRARegistryData_NotWorkYears']                     = !empty($v['IPRARegistryData_NotWorkYears'])                    ? $v['IPRARegistryData_NotWorkYears'] : '';
                        $_POST['IPRARegistryData_EmploymentOrientationExists']      = !empty($v['IPRARegistryData_ExistEmploymentOrientation'])      ? $v['IPRARegistryData_ExistEmploymentOrientation'] : null;
                        $_POST['IPRARegistryData_IsRegisteredInEmploymentService']  = !empty($v['IPRARegistryData_isRegInEmplService'])              ? $v['IPRARegistryData_isRegInEmplService'] : null;
                        $_POST['IPRARegistryData_DisabilityGroup']                  = !empty($v['IPRARegistryData_DisabilityGroup'])                 ? $v['IPRARegistryData_DisabilityGroup'] : null;
                        $_POST['IPRARegistryData_DisabilityCause']                  = !empty($v['IPRARegistryData_DisabilityCause'])                 ? $v['IPRARegistryData_DisabilityCause'] : null;
                        $_POST['IPRARegistryData_DisabilityCauseOther']             = !empty($v['IPRARegistryData_DisabilityCauseOther'])            ? $v['IPRARegistryData_DisabilityCauseOther'] : '';
                        $_POST['IPRARegistryData_DisabilityGroupDate']              = !empty($v['IPRARegistryData_DisabilityGroupDate'])             ? $v['IPRARegistryData_DisabilityGroupDate'] : null;
                        $_POST['IPRARegistryData_IsDisabilityGroupPrimary']         = !empty($v['IPRARegistryData_IsDisabilityGroupPrimary'])        ? $v['IPRARegistryData_IsDisabilityGroupPrimary'] : null;
                        $_POST['IPRARegistryData_DisabilityEndDate']                = !empty($v['IPRARegistryData_DisabilityEndDate'])               ? $v['IPRARegistryData_DisabilityEndDate'] : null;
                        $_POST['IPRARegistryData_SentOrgOGRN']                      = !empty($v['IPRARegistryData_SentOrgOGRN'])                     ? $v['IPRARegistryData_SentOrgOGRN'] : '';
                        $_POST['IPRARegistryData_SentOrgName']                      = !empty($v['IPRARegistryData_SentOrgName'])                     ? $v['IPRARegistryData_SentOrgName'] : '';
                        $_POST['IPRARegistryData_RehabPotential']                   = !empty($v['IPRARegistryData_RehabPotential'])                  ? $v['IPRARegistryData_RehabPotential'] : null;
                        $_POST['IPRARegistryData_RehabPrognoz']                     = !empty($v['IPRARegistryData_RehabPrognoz'])                    ? $v['IPRARegistryData_RehabPrognoz'] : null;
                        $_POST['IPRARegistryData_IsIntramural']                     = !empty($v['IPRARegistryData_IsIntramural'])                    ? $v['IPRARegistryData_IsIntramural'] : null;
                        $_POST['IPRARegistryData_PrognozSelfService']               = !empty($v['IPRARegistryData_PrognozResult_SelfService'])       ? $v['IPRARegistryData_PrognozResult_SelfService'] : null;
                        $_POST['IPRARegistryData_PrognozMoveIndependetly']          = !empty($v['IPRARegistryData_PrognozResult_Independently'])     ? $v['IPRARegistryData_PrognozResult_Independently'] : null;
                        $_POST['IPRARegistryData_PrognozOrientate']                 = !empty($v['IPRARegistryData_PrognozResult_Orientate'])         ? $v['IPRARegistryData_PrognozResult_Orientate'] : null;
                        $_POST['IPRARegistryData_PrognozCommunicate']               = !empty($v['IPRARegistryData_PrognozResult_Communicate'])       ? $v['IPRARegistryData_PrognozResult_Communicate'] : null;
                        $_POST['IPRARegistryData_PrognozBehaviorControl']           = !empty($v['IPRARegistryData_PrognozResult_BehaviorControl'])   ? $v['IPRARegistryData_PrognozResult_BehaviorControl'] : null;
                        $_POST['IPRARegistryData_PrognozLearning']                  = !empty($v['IPRARegistryData_PrognozResult_Learning'])          ? $v['IPRARegistryData_PrognozResult_Learning'] : null;
                        $_POST['IPRARegistryData_PrognozWork']                      = !empty($v['IPRARegistryData_PrognozResult_Work'])              ? $v['IPRARegistryData_PrognozResult_Work'] : null;
                        $_POST['IPRARegistryData_RepPerson_LastName']               = !empty($v['IPRARegistryData_RepPerson_LastName'])              ? $v['IPRARegistryData_RepPerson_LastName'] : '';
                        $_POST['IPRARegistryData_RepPerson_FirstName']              = !empty($v['IPRARegistryData_RepPerson_FirstName'])             ? $v['IPRARegistryData_RepPerson_FirstName'] : '';
                        $_POST['IPRARegistryData_RepPerson_SecondName']             = !empty($v['IPRARegistryData_RepPerson_SecondName'])            ? $v['IPRARegistryData_RepPerson_SecondName'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Title']                = !empty($v['IPRARegistryData_RepPerson_AuthorityDocType'])      ? $v['IPRARegistryData_RepPerson_AuthorityDocType'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Series']               = !empty($v['IPRARegistryData_RepPerson_AuthorityDocSeries'])    ? $v['IPRARegistryData_RepPerson_AuthorityDocSeries'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Number']               = !empty($v['IPRARegistryData_RepPerson_AuthorityDocNum'])       ? $v['IPRARegistryData_RepPerson_AuthorityDocNum'] : null;
                        $_POST['IPRARegistryData_RepPersonAD_Issuer']               = !empty($v['IPRARegistryData_RepPerson_AuthorityDocDep'])       ? $v['IPRARegistryData_RepPerson_AuthorityDocDep'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_IssueDate']            = !empty($v['IPRARegistryData_RepPerson_AuthorityDocDate'])      ? $v['IPRARegistryData_RepPerson_AuthorityDocDate'] : null;
                        $_POST['IPRARegistryData_RepPersonID_Title']                = !empty($v['IPRARegistryData_RepPerson_IdentifyDocType'])       ? $v['IPRARegistryData_RepPerson_IdentifyDocType'] : '';
                        $_POST['IPRARegistryData_RepPersonID_Series']               = !empty($v['IPRARegistryData_RepPerson_IdentifyDocSeries'])     ? $v['IPRARegistryData_RepPerson_IdentifyDocSeries'] : '';
                        $_POST['IPRARegistryData_RepPersonID_Number']               = !empty($v['IPRARegistryData_RepPerson_IdentifyDocNum'])        ? $v['IPRARegistryData_RepPerson_IdentifyDocNum'] : null;
                        $_POST['IPRARegistryData_RepPersonID_Issuer']               = !empty($v['IPRARegistryData_RepPerson_IdentifyDocDep'])        ? $v['IPRARegistryData_RepPerson_IdentifyDocDep'] : '';
                        $_POST['IPRARegistryData_RepPersonID_IssueDate']            = !empty($v['IPRARegistryData_RepPerson_IdentifyDocDate'])       ? $v['IPRARegistryData_RepPerson_IdentifyDocDate'] : null;
                        $_POST['IPRARegistryData_RepPerson_SNILS']                  = !empty($v['IPRARegistryData_RepPerson_SNILS'])                 ? $v['IPRARegistryData_RepPerson_SNILS'] : null;            
                    }

                    $response = $this->IPRARegistry_ins($data);
                    
                    if (!empty($response[0]['Error_Message']) && $response[0]['Error_Code'] != 309) {

                        $IPRANumber = isset($v['IPRAData_Number']) ? $v['IPRAData_Number'] : $v['IPRARegistry_Number'];
                        $errorsData[$IPRANumber] = $response[0]['Error_Message'];
                    }
                }
            } else {
                foreach ($d as $k => $v) {
                    
                        $_POST = array();
                        $_POST['IPRARegistry_IPRAident']                   = ($v['IPRAData_IPRAident'] == '') ? null : $v['IPRAData_IPRAident'];
                        $_POST['IPRARegistry_Number']                      = ($v['IPRAData_Number'] == '') ? null : $v['IPRAData_Number'];
                        $_POST['IPRARegistry_issueDate']                   = $v['IPRAData_issueDate'];
                        $_POST['IPRARegistry_EndDate']                     = $v['IPRAData_EndDate'];
                        $_POST['IPRARegistry_FGUMCEnumber']                = $v['IPRAData_FGUMCEnumber'];
                        $_POST['IPRARegistry_RecepientType']               = $v['IPRAData_RecepientType'];
                        $_POST['IPRARegistry_Protocol']                    = $v['IPRAData_Protocol'];
                        $_POST['IPRARegistry_ProtocolDate']                = $v['IPRAData_ProtocolDate'];
                        $_POST['IPRARegistry_DevelopDate']                 = $v['IPRAData_DevelopDate'];
                        $_POST['IPRARegistry_isFirst']                     = $v['IPRAData_isFirst'] === true ? 2 : 1;
                        $_POST['IPRARegistry_Confirm']                     = $v['IPRAData_Confirm'];
                        $_POST['IPRARegistry_DirectionLPU_id']             = $v['IPRAData_DirectionLPU_id'];
                        $_POST['IPRARegistry_FileName']                    = !empty($v['filename'])?$v['filename']:null;
                        $_POST['Lpu_id']                                   = ($v['Lpu_id'] == '') ? null : $v['Lpu_id'];
                        $_POST['Person_id']                                = ($v['Person_id'] == '') ? null : $v['Person_id'];
                        $_POST['IPRARegistryData_SelfService']             = $v['IPRAData_SelfService'];
                        $_POST['IPRARegistryData_Move']                    = $v['IPRAData_Move'];
                        $_POST['IPRARegistryData_Orientation']             = $v['IPRAData_Orientation'];
                        $_POST['IPRARegistryData_Communicate']             = $v['IPRAData_Communicate'];
                        $_POST['IPRARegistryData_Learn']                   = $v['IPRAData_Learn'];
                        $_POST['IPRARegistryData_Work']                    = $v['IPRAData_Work'];
                        $_POST['IPRARegistryData_Behavior']                = $v['IPRAData_Behavior'];
                        $_POST['IPRARegistryData_MedRehab']                = $v['IPRAData_MedRehab'];//($v['IPRAData_MedRehab'] === true) ? 1 : 2; 
                        $_POST['IPRARegistryData_MedRehab_begDate']        = !empty($v['IPRAData_MedRehab_begDate']) ? $v['IPRAData_MedRehab_begDate'] : null;
                        $_POST['IPRARegistryData_MedRehab_endDate']        = !empty($v['IPRAData_MedRehab_endDate']) ? $v['IPRAData_MedRehab_endDate'] : null;
                        $_POST['IPRARegistryData_Orthotics']               = $v['IPRAData_Orthotics'];//($v['IPRAData_Orthotics'] === true) ? 1 : 2;
                        $_POST['IPRARegistryData_Orthotics_begDate']       = !empty($v['IPRAData_Orthotics_begDate']) ? $v['IPRAData_Orthotics_begDate'] : null;
                        $_POST['IPRARegistryData_Orthotics_endDate']       = !empty($v['IPRAData_Orthotics_endDate']) ? $v['IPRAData_Orthotics_endDate'] : null;
                        $_POST['IPRARegistryData_ReconstructSurg']         = $v['IPRAData_ReconstructSurg'];//($v['IPRAData_ReconstructSurg'] === true) ? 1 : 2;
                        $_POST['IPRARegistryData_ReconstructSurg_begDate'] = !empty($v['IPRAData_ReconstructSurg_begDate']) ? $v['IPRAData_ReconstructSurg_begDate'] : null;
                        $_POST['IPRARegistryData_ReconstructSurg_endDate'] = !empty($v['IPRAData_ReconstructSurg_endDate']) ? $v['IPRAData_ReconstructSurg_endDate'] : null;
                        $_POST['IPRARegistryData_Restoration']             = !empty($v['IPRAData_Restoration']) ? $v['IPRAData_Restoration'] : null;
                        $_POST['IPRARegistryData_Compensate']              = !empty($v['IPRAData_Compensate']) ? $v['IPRAData_Compensate'] : null;
                        $_POST['IPRARegistryError_SurName']                = !empty($v['Person_SurName']) ? $v['Person_SurName'] : null;
                        $_POST['IPRARegistryError_FirName']                = !empty($v['Person_FirName']) ? $v['Person_FirName'] : null;
                        $_POST['IPRARegistryError_SecName']                = !empty($v['Person_SecName']) ? $v['Person_SecName'] : null;
                        $_POST['IPRARegistryError_BirthDay']               = !empty($v['IPRAData_BirthDate']) ? $v['IPRAData_BirthDate'] : null;
                        $_POST['pmUser_id']   = $data['pmUser_id'];
                        $_POST['IPRARegistry_Version']                          = !empty($v['IPRAData_Version'])                         ? $v['IPRAData_Version'] : null;
                        $_POST['IPRARegistryData_PrimaryProfession']                = !empty($v['IPRAData_PrimaryProfession'])               ? $v['IPRAData_PrimaryProfession'] : '';
                        $_POST['IPRARegistryData_PrimaryProfessionExperience']      = !empty($v['IPRAData_PrimaryProfessionExp'])            ? $v['IPRAData_PrimaryProfessionExp'] : '';
                        $_POST['IPRARegistryData_Qualification']                    = !empty($v['IPRAData_Qualification'])                   ? $v['IPRAData_Qualification'] : '';
                        $_POST['IPRARegistryData_CurrentJob']                       = !empty($v['IPRAData_CurrentJob'])                      ? $v['IPRAData_CurrentJob'] : '';
                        $_POST['IPRARegistryData_NotWorkYears']                     = !empty($v['IPRAData_NotWorkYears'])                    ? $v['IPRAData_NotWorkYears'] : '';
                        $_POST['IPRARegistryData_EmploymentOrientationExists']      = !empty($v['IPRAData_EmploymentOrientationExists'])     ? $v['IPRAData_EmploymentOrientationExists'] : null;
                        $_POST['IPRARegistryData_IsRegisteredInEmploymentService']  = !empty($v['IPRAData_IsRegisteredInEmploymentService']) ? $v['IPRAData_IsRegisteredInEmploymentService'] : null;
                        $_POST['IPRARegistryData_DisabilityGroup']                  = !empty($v['IPRAData_DisabilityGroup'])                 ? $v['IPRAData_DisabilityGroup'] : null;
                        $_POST['IPRARegistryData_DisabilityCause']                  = !empty($v['IPRAData_DisabilityCause'])                 ? $v['IPRAData_DisabilityCause'] : null;
                        $_POST['IPRARegistryData_DisabilityCauseOther']             = !empty($v['IPRAData_DisabilityCauseOther'])            ? $v['IPRAData_DisabilityCauseOther'] : '';
                        $_POST['IPRARegistryData_DisabilityGroupDate']              = !empty($v['IPRAData_DisabilityGroupDate'])             ? $v['IPRAData_DisabilityGroupDate'] : null;
                        $_POST['IPRARegistryData_IsDisabilityGroupPrimary']         = !empty($v['IPRAData_IsDisabilityGroupPrimary'])        ? $v['IPRAData_IsDisabilityGroupPrimary'] : null;
                        $_POST['IPRARegistryData_DisabilityEndDate']                = !empty($v['IPRAData_DisabilityEndDate'])               ? $v['IPRAData_DisabilityEndDate'] : null;
                        $_POST['IPRARegistryData_SentOrgOGRN']                      = !empty($v['IPRAData_SentOrgOGRN'])                     ? $v['IPRAData_SentOrgOGRN'] : '';
                        $_POST['IPRARegistryData_SentOrgName']                      = !empty($v['IPRAData_SentOrgName'])                     ? $v['IPRAData_SentOrgName'] : '';
                        $_POST['IPRARegistryData_RehabPotential']                   = !empty($v['IPRAData_RehabPotential'])                  ? $v['IPRAData_RehabPotential'] : null;
                        $_POST['IPRARegistryData_RehabPrognoz']                     = !empty($v['IPRAData_RehabPrognoz'])                    ? $v['IPRAData_RehabPrognoz'] : null;
                        $_POST['IPRARegistryData_IsIntramural']                     = !empty($v['IPRAData_IsIntramural'])                    ? $v['IPRAData_IsIntramural'] : null;
                        $_POST['IPRARegistryData_PrognozSelfService']               = !empty($v['IPRAData_PrognozSelfService'])              ? $v['IPRAData_PrognozSelfService'] : null;
                        $_POST['IPRARegistryData_PrognozMoveIndependetly']          = !empty($v['IPRAData_PrognozMoveIndependetly'])         ? $v['IPRAData_PrognozMoveIndependetly'] : null;
                        $_POST['IPRARegistryData_PrognozOrientate']                 = !empty($v['IPRAData_PrognozOrientate'])                ? $v['IPRAData_PrognozOrientate'] : null;
                        $_POST['IPRARegistryData_PrognozCommunicate']               = !empty($v['IPRAData_PrognozCommunicate'])              ? $v['IPRAData_PrognozCommunicate'] : null;
                        $_POST['IPRARegistryData_PrognozBehaviorControl']           = !empty($v['IPRAData_PrognozBehaviorControl'])          ? $v['IPRAData_PrognozBehaviorControl'] : null;
                        $_POST['IPRARegistryData_PrognozLearning']                  = !empty($v['IPRAData_PrognozLearning'])                 ? $v['IPRAData_PrognozLearning'] : null;
                        $_POST['IPRARegistryData_PrognozWork']                      = !empty($v['IPRAData_PrognozWork'])                     ? $v['IPRAData_PrognozWork'] : null;
                        $_POST['IPRARegistryData_RepPerson_LastName']               = !empty($v['IPRAData_RepPerson_LastName'])              ? $v['IPRAData_RepPerson_LastName'] : '';
                        $_POST['IPRARegistryData_RepPerson_FirstName']              = !empty($v['IPRAData_RepPerson_FirstName'])             ? $v['IPRAData_RepPerson_FirstName'] : '';
                        $_POST['IPRARegistryData_RepPerson_SecondName']             = !empty($v['IPRAData_RepPerson_SecondName'])            ? $v['IPRAData_RepPerson_SecondName'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Title']                = !empty($v['IPRAData_RepPersonAD_Title'])               ? $v['IPRAData_RepPersonAD_Title'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Series']               = !empty($v['IPRAData_RepPersonAD_Series'])              ? $v['IPRAData_RepPersonAD_Series'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_Number']               = !empty($v['IPRAData_RepPersonAD_Number'])              ? $v['IPRAData_RepPersonAD_Number'] : null;
                        $_POST['IPRARegistryData_RepPersonAD_Issuer']               = !empty($v['IPRAData_RepPersonAD_Issuer'])              ? $v['IPRAData_RepPersonAD_Issuer'] : '';
                        $_POST['IPRARegistryData_RepPersonAD_IssueDate']            = !empty($v['IPRAData_RepPersonAD_IssueDate'])           ? $v['IPRAData_RepPersonAD_IssueDate'] : null;
                        $_POST['IPRARegistryData_RepPersonID_Title']                = !empty($v['IPRAData_RepPersonID_Title'])               ? $v['IPRAData_RepPersonID_Title'] : '';
                        $_POST['IPRARegistryData_RepPersonID_Series']               = !empty($v['IPRAData_RepPersonID_Series'])              ? $v['IPRAData_RepPersonID_Series'] : '';
                        $_POST['IPRARegistryData_RepPersonID_Number']               = !empty($v['IPRAData_RepPersonID_Number'])              ? $v['IPRAData_RepPersonID_Number'] : null;
                        $_POST['IPRARegistryData_RepPersonID_Issuer']               = !empty($v['IPRAData_RepPersonID_Issuer'])              ? $v['IPRAData_RepPersonID_Issuer'] : '';
                        $_POST['IPRARegistryData_RepPersonID_IssueDate']            = !empty($v['IPRAData_RepPersonID_IssueDate'])           ? $v['IPRAData_RepPersonID_IssueDate'] : null;
                        $_POST['IPRARegistryData_RepPerson_SNILS']                  = !empty($v['IPRAData_RepPerson_SNILS'])                 ? $v['IPRAData_RepPerson_SNILS'] : null;
                    $response = $this->IPRARegistry_ins($data);
                    
                    if (!empty($response[0]['Error_Message']) && $response[0]['Error_Code'] != 309) {
                        $errorsErrors[$v['IPRAData_Number']] = $response[0]['Error_Message']; 
                    }
                    
                }
            }
        }

        if (!empty($errorsData) || !empty($errorsErrors)) {
            
            $this->ReturnData(array(
                'success' => false,
                'Error_Code' => -1,
                'Error_Msg' => toUTF('Произошла ошибка при работе с БД.'),
                'ErrorText' => $response[0]['Error_Message'],
                'FullTextError'=>json_encode($errorsErrors)
            ));
        } else {
            $this->ReturnData(array(
                array(
                    'success' => true,
                    'Error_Code' => 0
                )
            ));
        }
    }
    
    
    
    
    /**
     *  Сборный метод идентификации пациента и получения МО прикрепления
     */
    function getIdentityPacient()
    {
        $data = $this->ProcessInputData('getIdentityPacient', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getIdentityPacient($data);
        $this->ReturnData($response);
        //return $response;        
    }
    /**
     *  Получение списка бюро, которые проводят МСЭ
     */
    function getAllBureau()
    {
        $data = $this->ProcessInputData('getAllBureau', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getAllBureau($data);
        $this->ReturnData($response);
        //$this->ProcessModelSave($response, true)->ReturnData();              
    }
    /**
     *  Определение пациента в РМИАС
     */
    function getPerson_id()
    {
        $data = $this->ProcessInputData('getPerson_id', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getPerson_id($data);
        return $response;
        //$this->ProcessModelSave($response, true)->ReturnData();          
    }

    /**
     *  Определение ЛПУ прикрепления пациента
     */
    function getLpuAttachData()
    {
        $data = $this->ProcessInputData('getLpuAttachData', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->getLpuAttachData($data);
        return $response;
        //$this->ProcessModelSave($response, true)->ReturnData();        
    }
    
    /**
     * comment
     */
    function object_to_array($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        
        if(is_array($obj)) {
            $new = array();
            
            foreach($obj as $key => $val) {
                if(is_object($obj)){
                    $new[$key] = $this->object_to_array($val);
                }
                else{
                    $new[$key] = $val;
                }
            }
        }
        else $new = $obj;
        return $new;       
    }    
    
    /**
     * Добавления пациента в рагистр ИПРА
     */
    function IPRARegistry_import()
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", "0");
        ini_set("max_input_time", "0");
        ini_set("post_max_size", "220");
        ini_set("default_socket_timeout", "999");
        ini_set("upload_max_filesize", "220M");
        /**/
        
        $dirMCE        = 'mce';
        $upload_path   = './' . IMPORTPATH_ROOT . $dirMCE;
        
        $allowed_types = explode('|', 'zip');  
        
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }        

        if (!file_exists($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Ошибка загрузки файлов в директорию.')
            )));
        }


        
        $err = getInputParams($data, $this->inputRules['IPRARegistry_import']);
        if (!empty($err)) {
            $this->ReturnError($err);
            return false;
        }
        
        //echo '<pre>' . print_r($_FILES, 1) . '</pre>';
        
        //exit;
        if (!isset($_FILES['IPRARegistry_import'])) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100011,
                'Error_Msg' => toUTF('Не выбран файл для импорта!')
            )));
        }
        
        if (!is_uploaded_file($_FILES['IPRARegistry_import']['tmp_name'])) {
            $error = (!isset($_FILES['IPRARegistry_import']['error'])) ? 4 : $_FILES['IPRARegistry_import']['error'];
            switch ($error) {
                case 1:
                    $message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
                    break;
                case 2:
                    $message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
                    break;
                case 3:
                    $message = 'Этот файл был загружен не полностью.';
                    break;
                case 4:
                    $message = 'Вы не выбрали файл для загрузки.';
                    break;
                case 6:
                    $message = 'Временная директория не найдена.';
                    break;
                case 7:
                    $message = 'Файл не может быть записан на диск.';
                    break;
                case 8:
                    $message = 'Неверный формат файла.';
                    break;
                default:
                    $message = 'При загрузке файла произошла ошибка.';
                    break;
            }
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100012,
                'Error_Msg' => toUTF($message)
            )));
        }
        
        
        // Тип файла разрешен к загрузке?
        $x  = explode('.', $_FILES['IPRARegistry_import']['name']);
        $file_data['file_ext'] = end($x);
        if (!in_array(mb_strtolower($file_data['file_ext']), $allowed_types)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Данный тип файла не разрешен.')
            )));
        }
        
        // Правильно ли указана директория для загрузки?
        if (!@is_dir($upload_path)) {
            mkdir($upload_path);
        }
        
        if (!@is_dir($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100014,
                'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')
            )));
        }
        
        // Имеет ли директория для загрузки права на запись?
        if (!is_writable($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100015,
                'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')
            )));
        }
        
        $zip = new ZipArchive;
        
        if ($zip->open($_FILES['IPRARegistry_import']['tmp_name']) === TRUE) {
            $zip->extractTo($upload_path);
            
            $zip->close();
            
            $files = scandir($upload_path);
            
            $data = array();
            
            

            $result = array();
            
            $count = glob($upload_path.'/*.xml');

            if (sizeof($count) > 200) {
                foreach ($files as $k => $filename) {
                    $file = iconv('CP866', 'utf-8', $upload_path . '/' . $filename);
                    if (!in_array($filename, array(
                        '.',
                        '..'
                    ))) {
                        @unlink($file);
                    }
                }
                
                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Привышени лимит импорта. Максимальный размер архива 200 XML файлов')
                )));
            }
            
            $files = array_reverse($files);
            $count_xml = 0;
            foreach ($files as $k => $filename) {
                $file_info = pathinfo($filename);
                
                if (!in_array($filename, array('.','..')) && isset($file_info['extension']) && $file_info['extension'] == 'xml') {
                    $count_xml++;
                    $griddata = array();
                    //echo iconv('CP866','utf-8', $filename).PHP_EOL;
                    $file     = iconv('CP866', 'utf-8', $upload_path . '/' . $filename);
                    //echo $file."\r\n";

                    if (@file_get_contents($file) === false) {
                        
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Некорректное содержание ахива. Ожидаются XML файлы.')
                        )));
                    } else {
                        $xmlstr = file_get_contents($file);
                    }
                    
                    $xmlstr = strtr($xmlstr, array(
                        'ct:' => ''
                    ));
                    
                    try{
                        $xml = @new SimpleXMLElement($xmlstr);
                        
                    }
                    catch(Exception $e){
                        @unlink($file);
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Невалидный XML файл: '.$filename),
                            'Error_Txt' => $e->getMessage(),
                        )));                        
                    }

                    $xml_arr = $this->object_to_array($xml);
                    
                    //echo '<pre>' . print_r($xml_arr, 1) . '</pre>';                    
                    
                    foreach ($xml_arr as $j => $node) {   
                        $data[$k][$j] = $this->object_to_array($node);
                    }
                    
                    foreach ($data[$k] as $l => $v) {
                        $griddata[$k]['filename'] = basename($file,".xml");
                        //подтверждение МО 
                        $griddata[$k]['IPRAData_Confirm']         = 1;
                        //$griddata[$k]['IPRAData_DirectionLPU_id'] = '';
                        //Прикрепление по дефолту не определено
                        $griddata[$k]['Lpu_id']                   = '';
                        $griddata[$k]['LpuAttachName']            = '';
                        $griddata[$k]['IPRAData_LpuName']         = '';

						if ($l == 'Id') {
                                $griddata[$k]['IPRAData_IPRAident']    = $v;
						} elseif ($l == 'Buro') {
                            $griddata[$k]['IPRAData_FGUMCE']       = $v['FullName'];
                            $griddata[$k]['IPRAData_FGUMCEshort']  = 'Бюро №' . $v['Number'];
                            $griddata[$k]['IPRAData_FGUMCEnumber'] = $v['Number'];
                        } elseif ($l == 'Recipient') {
                            if (isset($v['RecipientType'])) {
                                    $RecipientType = $this->object_to_array($v['RecipientType']);

                                    $griddata[$k]['IPRAData_RecepientType'] = $RecipientType['Id'];
                            }
						} elseif ($l == 'MedSection') {
                            if (isset($v['SenderMedOrgName'])) {
                                $griddata[$k]['IPRAData_DirectionLPU_id'] = $v['SenderMedOrgName'];
                            }

                            $EventGroups = $this->object_to_array($v['EventGroups']);//(array) $v['EventGroups'];
                            $groups      = $EventGroups['Group'];

                            //Несколько групп
                            if (!is_array($groups) || !isset($groups[0])) {
                                $groups = array(
                                    0 => $groups
                                );
                            }

                            foreach ($groups as $n => $g) {
                                $g =  $this->object_to_array($g);
                                //var_dump($g);
                                if ($g['GroupType']->Id == 34) {
                                    //$griddata[$k]['IPRAData_MedRehab'] = $g['Need'];
                
                                    if ($g['Need'] == 'true') {
                                        $griddata[$k]['IPRAData_MedRehab'] = 2;
                                        $griddata[$k]['IPRAData_MedRehab_begDate'] = isset($g['PeriodFrom']) && $g['PeriodFrom']!='0001-01-01' ? (string) $g['PeriodFrom'] : '';
                                        $griddata[$k]['IPRAData_MedRehab_endDate']   = isset($g['PeriodTo']) && $g['PeriodTo']!='0001-01-01' ? (string) $g['PeriodTo'] : '';
                                        
                                    } else {
                                        $griddata[$k]['IPRAData_MedRehab'] = 1;
                                        $griddata[$k]['IPRAData_MedRehab_endDate']   = '';
                                        $griddata[$k]['IPRAData_MedRehab_begDate'] = '';
                                    }
                                } elseif ($g['GroupType']->Id == 35) {
                                    //$griddata[$k]['IPRAData_ReconstructSurg'] = $g['Need'];
                                    
                                    if ($g['Need'] == 'true') {
                                        $griddata[$k]['IPRAData_ReconstructSurg'] = 2;
                                        $griddata[$k]['IPRAData_ReconstructSurg_begDate'] = isset($g['PeriodFrom']) && $g['PeriodFrom']!='0001-01-01' ? (string) $g['PeriodFrom'] : '';
                                        $griddata[$k]['IPRAData_ReconstructSurg_endDate']   = isset($g['PeriodTo']) && $g['PeriodTo']!='0001-01-01' ? (string) $g['PeriodTo'] : '';
                                    } else {
                                        $griddata[$k]['IPRAData_ReconstructSurg'] = 1;
                                        $griddata[$k]['IPRAData_ReconstructSurg_endDate']   = '';
                                        $griddata[$k]['IPRAData_ReconstructSurg_begDate'] = '';
                                    }
                                } elseif ($g['GroupType']->Id == 36) {
                                    //$griddata[$k]['IPRAData_Orthotics'] = $g['Need'];
                                    
                                    if ($g['Need'] == 'true') {
                                        $griddata[$k]['IPRAData_Orthotics'] = 1;
                                        $griddata[$k]['IPRAData_Orthotics_begDate'] = isset($g['PeriodFrom']) && $g['PeriodFrom']!='0001-01-01' ? (string) $g['PeriodFrom'] : '';
                                        $griddata[$k]['IPRAData_Orthotics_endDate']   = isset($g['PeriodTo']) && $g['PeriodTo']!='0001-01-01' ? (string) $g['PeriodTo'] : '';
                                    } else {
                                        $griddata[$k]['IPRAData_Orthotics'] = 2;
                                        $griddata[$k]['IPRAData_Orthotics_endDate ']   = '';
                                        $griddata[$k]['IPRAData_Orthotics_begDate '] = '';
                                    }
                                }
                            }
                            if(!isset($griddata[$k]['IPRAData_MedRehab'])){
                                $griddata[$k]['IPRAData_MedRehab']          = 1;
                                $griddata[$k]['IPRAData_MedRehab_endDate']   = '';
                                $griddata[$k]['IPRAData_MedRehab_begDate'] = '';
                            }
                            if(!isset($griddata[$k]['IPRAData_ReconstructSurg'])){
                                $griddata[$k]['IPRAData_ReconstructSurg']        = 1;
                                $griddata[$k]['IPRAData_ReconstructSurg_endDate'] = '';
                                $griddata[$k]['IPRAData_ReconstructSurg_begDate']      = '';
                            }
                            if(!isset($griddata[$k]['IPRAData_Orthotics'])){
                                $griddata[$k]['IPRAData_Orthotics']          = 1;
                                $griddata[$k]['IPRAData_Orthotics_endDate']   = '';
                                $griddata[$k]['IPRAData_Orthotics_begDate'] = '';
                            }
                  
                            $PrognozResult    = $this->object_to_array($v['PrognozResult']);
                            $FuncRecovery     = $this->object_to_array($PrognozResult['FuncRecovery']);
                            $FuncCompensation = $this->object_to_array($PrognozResult['FuncCompensation']);
 
                            if(!isset($griddata[$k]['IPRAData_DirectionLPU_id'])){
                                if (isset($v['SenderMedOrgName'])) {
                                    $griddata[$k]['IPRAData_DirectionLPU_id'] = $v['SenderMedOrgName'];
                                } else {
                                    $griddata[$k]['IPRAData_DirectionLPU_id'] = '';
                                }
                            }
                            
                            //по дефолту
                            $griddata[$k]['IPRAData_Restoration'] = null;
                            $griddata[$k]['IPRAData_Compensate'] = null;
                            
                            if (isset($FuncRecovery['Id'])) {
								$griddata[$k]['IPRAData_Restoration'] = $FuncRecovery['Id'];
							}
                            if (isset($FuncCompensation['Id'])) {
								$griddata[$k]['IPRAData_Compensate'] = $FuncCompensation['Id'];
							}
                            
                        } elseif ($l == 'Number') {
                            $griddata[$k]['IPRAData_Number'] = $v;
                        } elseif ($l == 'ProtocolNum') {
                            $griddata[$k]['IPRAData_Protocol'] = $v;
                        } elseif ($l == 'ProtocolDate') {
                            $griddata[$k]['IPRAData_ProtocolDate'] = $v;
                        } elseif ($l == 'LifeRestrictions') {
                            $griddata[$k]['IPRAData_SelfService'] = !empty( $v['SelfCare'] ) ? $v['SelfCare'] : 0;
                            $griddata[$k]['IPRAData_Move']        = !empty( $v['Moving'] ) ? $v['Moving'] : 0;
                            $griddata[$k]['IPRAData_Orientation'] = !empty( $v['Orientation'] ) ? $v['Orientation'] : 0;
                            $griddata[$k]['IPRAData_Communicate'] = !empty( $v['Communication'] ) ? $v['Communication'] : 0;
                            $griddata[$k]['IPRAData_Learn']       = !empty( $v['Learn'] ) ? $v['Learn'] : 0;
                            $griddata[$k]['IPRAData_Work']        = !empty( $v['Work'] ) ? $v['Work'] : 0;
                            $griddata[$k]['IPRAData_Behavior']    = !empty( $v['BehaviorControl'] ) ? $v['BehaviorControl'] : 0;
                        } elseif ($l == 'EndDate') {
                            $griddata[$k]['IPRAData_EndDate'] = $v == '0001-01-01' ? '' : $v;
                        } elseif ($l == 'IssueDate') {
                            $griddata[$k]['IPRAData_issueDate'] =  $v == '0001-01-01' ? '' : $v;
                        } elseif ($l == 'IsFirst') {
                            $griddata[$k]['IPRAData_isFirst'] = $v;
                        } elseif ($l == 'Person') {
                            $person      = (array) $v['FIO'];
                            $IdentityDoc = (array) $v['IdentityDoc'];
                            
                            //echo '<pre>' . print_r($person, 1) . '</pre>';
                            $griddata[$k]['IPRAData_Document_Num'] = (string) isset($IdentityDoc['Number']) ? $IdentityDoc['Number'] : '';

							$FirstName = (string)$person['FirstName'];
							$LastName = (string)$person['LastName'];
							$SecondName = (string)$person['SecondName'];

                            $griddata[$k]['Person_FirName'] = !empty($FirstName)?$FirstName:null;
                            $griddata[$k]['Person_SurName'] = !empty($LastName)?$LastName:null;
                            $griddata[$k]['Person_SecName'] = !empty($SecondName)?$SecondName:null;
                            
                            $griddata[$k]['Person_id']          = '';
                            $griddata[$k]['IPRAData_BirthDate'] = $v['BirthDate'];
                            
                            
                            if(isset($v['SNILS'])){    
                                if (is_string($v['SNILS'])) {
                                    $griddata[$k]['IPRAData_SNILS'] = $v['SNILS'];
                                } else {
                                    $griddata[$k]['IPRAData_SNILS'] = $this->object_to_array($v['SNILS'][0]);//(array) $v['SNILS'][0];
                                }
                            }    
                            else{
                                $griddata[$k]['IPRAData_SNILS'] = '';
                            }
                            
                            $griddata[$k]['IPRAData_PersonFIO'] = $person['LastName'] . ' ' . $person['FirstName'] . ' ' . $person['SecondName'];
                        } elseif ($l == 'RequiredHelp') {
                            if (isset($v['HelpItems'])) {
                                $griddata[$k]['IPRAData_RequiredHelp'] = '';
                                // echo '<pre>' . print_r($v['HelpItems'], 1) . '</pre>';
                                // $RequiredHelp = $this->object_to_array($v['HelpItems']->HelpItem->HelpCategory);//(array) $v['HelpItems']->HelpItem->HelpCategory;
                                // $griddata[$k]['IPRAData_RequiredHelp'] = $RequiredHelp['Id'];
                            }// else {
                            //    $griddata[$k]['IPRAData_RequiredHelp'] = '';
                            //}
                        } elseif ($l == 'DevelopDate') {
                            if (isset($v[0]))
                                $griddata[$k]['IPRAData_DevelopDate'] = $v;
                        }
                        
                    }
  
                    //МО в которую определили инвалида
                    /**
                    Если пациент определён в РМИАС и имеет прикрепление с типом "Основное" - он определяется к данному МО (если не псих.)
                    Если пациент - писх. (по номеру бюро) - внезависимости от МО прикрепления - он определяется к МО направления
                    Если пациент не имеет прикрепления в РМИАС то он определяется в МО направления
                    в противном случае - ручной ввод МО
                    */
                      
                    
                    if (isset($griddata[$k]['IPRAData_DirectionLPU_id'])) {
                        $crazy = $this->crazy;
                        if (in_array($griddata[$k]['IPRAData_FGUMCEnumber'], $crazy['ufa'])) {
                            $griddata[$k]['IPRAData_LpuName'] = $griddata[$k]['IPRAData_DirectionLPU_id'];
                        } else {
                            if ($griddata[$k]['LpuAttachName'] == '') {
                                $griddata[$k]['IPRAData_LpuName'] = $griddata[$k]['IPRAData_DirectionLPU_id'];
                            } else {
                                $griddata[$k]['IPRAData_LpuName'] = $griddata[$k]['LpuAttachName'];
                                
                            }
                        }
                    } else {
                        $griddata[$k]['IPRAData_LpuName'] = '';
                    }           
                    
                    $result[] = $griddata[$k];
                    //echo '<pre>' . print_r($result, 1) . '</pre>';
                    @unlink($file);
                }
            }

            if($count_xml == 0){
                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Некорректное содержание ахива. Ожидаются XML файлы.')
                )));                
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $result
            ));
        } else {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 10002,
                'Error_Msg' => toUTF('Данный тип сжатия файлов не поддерживается.')
            )));
        }
        
        //@rmdir($upload_path);
    }

    /**
     * Добавление пациента в регистр ИПРА(новый формат)
     */
    function IPRARegistry_importNewFormat()
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", "0");
        ini_set("max_input_time", "0");
        ini_set("post_max_size", "220");
        ini_set("default_socket_timeout", "999");
        ini_set("upload_max_filesize", "220M");
        /**/
        
        $dirMCE        = 'mce';
        $upload_path   = './' . IMPORTPATH_ROOT . $dirMCE;
        
        $allowed_types = explode('|', 'zip');  
        
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }        

        if (!file_exists($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Ошибка загрузки файлов в директорию.')
            )));
        }


        
        $err = getInputParams($data, $this->inputRules['IPRARegistry_import']);
        if (!empty($err)) {
            $this->ReturnError($err);
            return false;
        }
        
        //echo '<pre>' . print_r($_FILES, 1) . '</pre>';
        
        //exit;
        if (!isset($_FILES['IPRARegistry_import'])) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100011,
                'Error_Msg' => toUTF('Не выбран файл для импорта!')
            )));
        }
        
        if (!is_uploaded_file($_FILES['IPRARegistry_import']['tmp_name'])) {
            $error = (!isset($_FILES['IPRARegistry_import']['error'])) ? 4 : $_FILES['IPRARegistry_import']['error'];
            switch ($error) {
                case 1:
                    $message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
                    break;
                case 2:
                    $message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
                    break;
                case 3:
                    $message = 'Этот файл был загружен не полностью.';
                    break;
                case 4:
                    $message = 'Вы не выбрали файл для загрузки.';
                    break;
                case 6:
                    $message = 'Временная директория не найдена.';
                    break;
                case 7:
                    $message = 'Файл не может быть записан на диск.';
                    break;
                case 8:
                    $message = 'Неверный формат файла.';
                    break;
                default:
                    $message = 'При загрузке файла произошла ошибка.';
                    break;
            }
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100012,
                'Error_Msg' => toUTF($message)
            )));
        }
        
        
        // Тип файла разрешен к загрузке?
        $x  = explode('.', $_FILES['IPRARegistry_import']['name']);
        $file_data['file_ext'] = end($x);
        if (!in_array(mb_strtolower($file_data['file_ext']), $allowed_types)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Данный тип файла не разрешен.')
            )));
        }
        
        // Правильно ли указана директория для загрузки?
        if (!@is_dir($upload_path)) {
            mkdir($upload_path);
        }
        
        if (!@is_dir($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100014,
                'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')
            )));
        }
        
        // Имеет ли директория для загрузки права на запись?
        if (!is_writable($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100015,
                'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')
            )));
        }
		
        $zip = new ZipArchive;
        
        if ($zip->open($_FILES['IPRARegistry_import']['tmp_name']) === TRUE) {
            $zip->extractTo($upload_path);
            
            $zip->close();
            
            $files = scandir($upload_path);
            
            $data = array();
            
            

            $result = array();
            
            $count = glob($upload_path.'/*.xml');

            if (sizeof($count) > 200) {
                foreach ($files as $k => $filename) {
                    $file = iconv('CP866', 'utf-8', $upload_path . '/' . $filename);
                    if (!in_array($filename, array(
                        '.',
                        '..'
                    ))) {
                        @unlink($file);
                    }
                }
                
                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Привышен лимит импорта. Максимальный размер архива 200 XML файлов')
                )));
            }
            
            $files = array_reverse($files);
            $count_xml = 0;
            foreach ($files as $k => $filename) {
                $file_info = pathinfo($filename);
                
                if (!in_array($filename, array('.','..')) && isset($file_info['extension']) && $file_info['extension'] == 'xml') {
                    $count_xml++;
                    $griddata = array();
                    //echo iconv('CP866','utf-8', $filename).PHP_EOL;
                    $file     = $upload_path . '/' . $filename;
                    //echo $file."\r\n";
                    
                    if (@file_get_contents($file) === false) {
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Некорректное содержание архива. Ожидаются XML файлы!!!!.')
                        )));
                    } else {
                        $xmlstr = file_get_contents($file);
                    }
                    
                    $xmlstr = strtr($xmlstr, array(
                        'ct:' => ''
                    ));
                    
                    try{
                        $xml = @new SimpleXMLElement($xmlstr);
                        
                    }
                    catch(Exception $e){
                        @unlink($file);
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Невалидный XML файл: '.$filename),
                            'Error_Txt' => $e->getMessage(),
                        )));                        
                    }

                    $xml_arr = $this->object_to_array($xml);
                    
                    //echo '<pre>' . print_r($xml_arr, 1) . '</pre>';                    
                    
                    foreach ($xml_arr as $j => $node) {   
                        $data[$k][$j] = $this->object_to_array($node);
                    }
                    
                    foreach ($data[$k] as $l => $v) {
                        $griddata[$k]['filename'] = iconv('CP866', 'utf-8', basename($file,".xml"));
                        //подтверждение МО 
                        $griddata[$k]['IPRAData_Confirm']         = 1;
                        //$griddata[$k]['IPRAData_DirectionLPU_id'] = '';
                        //Прикрепление по дефолту не определено
                        $griddata[$k]['Lpu_id']                   = '';
                        $griddata[$k]['LpuAttachName']            = '';
                        $griddata[$k]['IPRAData_LpuName']         = '';
                        
                        if ($l == '@attributes'){
                            if(isset($v['Version'])){
                                $griddata[$k]['IPRAData_Version'] = $v['Version'];
                            }
                            if(isset($v['Id'])){
                                $griddata[$k]['IPRAData_IPRAident'] = $v['Id'];
                            }
                        }
                        elseif ($l == 'Id') 
                        {
                            $griddata[$k]['IPRAData_IPRAident']    = $v;
                        } 
                        elseif ($l == 'Buro')
                        {
                            $griddata[$k]['IPRAData_FGUMCE']       = $v['FullName'];
                            
                            $griddata[$k]['IPRAData_FGUMCEshort']  = 'Бюро №' . $v['Number'];
                            $griddata[$k]['IPRAData_FGUMCEnumber'] = $v['Number'];
                        } 
                        elseif ($l == 'Recipient') 
                        {
                            if (isset($v['RecipientType'])) {
                                    $RecipientType = $this->object_to_array($v['RecipientType']);

                                    $griddata[$k]['IPRAData_RecepientType'] = $RecipientType['Id'];
                            }
                        } 
                        elseif ($l == 'MedSection')
                        {
                            if (isset($v['SenderMedOrgName'])) {
                                $griddata[$k]['IPRAData_DirectionLPU_id'] = $v['SenderMedOrgName'];
                            }

                            $EventGroups = $this->object_to_array($v['EventGroups']);//(array) $v['EventGroups'];
                            $groups      = $EventGroups['Group'];

                            //Несколько групп
                            if (!is_array($groups) || !isset($groups[0])) {
                                $groups = array(
                                    0 => $groups
                                );
                            }

                            foreach ($groups as $n => $g) {
                                $g =  $this->object_to_array($g);
                                //var_dump($g);
                                if ($g['GroupType']->Id == 34) {
                                    //$griddata[$k]['IPRAData_MedRehab'] = $g['Need'];

                                    if ($g['Need'] == 'true') {
                                        $griddata[$k]['IPRAData_MedRehab'] = 2;
                                        $griddata[$k]['IPRAData_MedRehab_begDate'] = isset($g['PeriodFrom']) && $g['PeriodFrom']!='0001-01-01' ? (string) $g['PeriodFrom'] : '';
                                        $griddata[$k]['IPRAData_MedRehab_endDate']   = isset($g['PeriodTo']) && $g['PeriodTo']!='0001-01-01' ? (string) $g['PeriodTo'] : '';
                                        
                                    } else {
                                        $griddata[$k]['IPRAData_MedRehab'] = 1;
                                        $griddata[$k]['IPRAData_MedRehab_endDate']   = '';
                                        $griddata[$k]['IPRAData_MedRehab_begDate'] = '';
                                    }
                                } 
                                elseif ($g['GroupType']->Id == 35) {
                                    //$griddata[$k]['IPRAData_ReconstructSurg'] = $g['Need'];
                                    
                                    if ($g['Need'] == 'true') {
                                        $griddata[$k]['IPRAData_ReconstructSurg'] = 2;
                                        $griddata[$k]['IPRAData_ReconstructSurg_begDate'] = isset($g['PeriodFrom']) && $g['PeriodFrom']!='0001-01-01' ? (string) $g['PeriodFrom'] : '';
                                        $griddata[$k]['IPRAData_ReconstructSurg_endDate']   = isset($g['PeriodTo']) && $g['PeriodTo']!='0001-01-01' ? (string) $g['PeriodTo'] : '';
                                    } else {
                                        $griddata[$k]['IPRAData_ReconstructSurg'] = 1;
                                        $griddata[$k]['IPRAData_ReconstructSurg_endDate']   = '';
                                        $griddata[$k]['IPRAData_ReconstructSurg_begDate'] = '';
                                    }
                                } 
                                elseif ($g['GroupType']->Id == 36) {
                                    //$griddata[$k]['IPRAData_Orthotics'] = $g['Need'];
                                    
                                    if ($g['Need'] == 'true') {
                                        $griddata[$k]['IPRAData_Orthotics'] = 1;
                                        $griddata[$k]['IPRAData_Orthotics_begDate'] = isset($g['PeriodFrom']) && $g['PeriodFrom']!='0001-01-01' ? (string) $g['PeriodFrom'] : '';
                                        $griddata[$k]['IPRAData_Orthotics_endDate']   = isset($g['PeriodTo']) && $g['PeriodTo']!='0001-01-01' ? (string) $g['PeriodTo'] : '';
                                    } else {
                                        $griddata[$k]['IPRAData_Orthotics'] = 2;
                                        $griddata[$k]['IPRAData_Orthotics_endDate ']   = '';
                                        $griddata[$k]['IPRAData_Orthotics_begDate '] = '';
                                    }
                                }
                            }
                            if(!isset($griddata[$k]['IPRAData_MedRehab'])){
                                $griddata[$k]['IPRAData_MedRehab']          = 1;
                                $griddata[$k]['IPRAData_MedRehab_endDate']   = '';
                                $griddata[$k]['IPRAData_MedRehab_begDate'] = '';
                            }
                            if(!isset($griddata[$k]['IPRAData_ReconstructSurg'])){
                                $griddata[$k]['IPRAData_ReconstructSurg']        = 1;
                                $griddata[$k]['IPRAData_ReconstructSurg_endDate'] = '';
                                $griddata[$k]['IPRAData_ReconstructSurg_begDate']      = '';
                            }
                            if(!isset($griddata[$k]['IPRAData_Orthotics'])){
                                $griddata[$k]['IPRAData_Orthotics']          = 1;
                                $griddata[$k]['IPRAData_Orthotics_endDate']   = '';
                                $griddata[$k]['IPRAData_Orthotics_begDate'] = '';
                            }
                  
 
                            if(!isset($griddata[$k]['IPRAData_DirectionLPU_id'])){
                                if (isset($v['SenderMedOrgName'])) {
                                    $griddata[$k]['IPRAData_DirectionLPU_id'] = $v['SenderMedOrgName'];
                                } else {
                                    $griddata[$k]['IPRAData_DirectionLPU_id'] = '';
                                }
                            }
                            
                            if(isset($v['PrognozResult'])){
                                $PrognozResult    = $this->object_to_array($v['PrognozResult']);
                                $FuncRecovery     = $this->object_to_array($PrognozResult['FuncRecovery']);
                                $FuncCompensation = $this->object_to_array($PrognozResult['FuncCompensation']);

                                //по дефолту
                                $griddata[$k]['IPRAData_Restoration'] = null;
                                $griddata[$k]['IPRAData_Compensate'] = null;

                                if (isset($FuncRecovery['Id'])) {
                                    $griddata[$k]['IPRAData_Restoration'] = $FuncRecovery['Id'];
                                }
                                if (isset($FuncCompensation['Id'])) {
                                    $griddata[$k]['IPRAData_Compensate'] = $FuncCompensation['Id'];
                                }
                            }
                            
                        } 
                        elseif ($l == 'Number')
                        {
                            $griddata[$k]['IPRAData_Number'] = $v;
                        } 
                        elseif ($l == 'ProtocolNum') 
                        {
                            $griddata[$k]['IPRAData_Protocol'] = $v;
                        } 
                        elseif ($l == 'ProtocolDate') 
                        {
                            $griddata[$k]['IPRAData_ProtocolDate'] = $v;
                        } 
                        elseif ($l == 'LifeRestrictions') 
                        {
                            $griddata[$k]['IPRAData_SelfService'] = !empty( $v['SelfCare'] ) ? $v['SelfCare'] : 0;
                            $griddata[$k]['IPRAData_Move']        = !empty( $v['Moving'] ) ? $v['Moving'] : 0;
                            $griddata[$k]['IPRAData_Orientation'] = !empty( $v['Orientation'] ) ? $v['Orientation'] : 0;
                            $griddata[$k]['IPRAData_Communicate'] = !empty( $v['Communication'] ) ? $v['Communication'] : 0;
                            $griddata[$k]['IPRAData_Learn']       = !empty( $v['Learn'] ) ? $v['Learn'] : 0;
                            $griddata[$k]['IPRAData_Work']        = !empty( $v['Work'] ) ? $v['Work'] : 0;
                            $griddata[$k]['IPRAData_Behavior']    = !empty( $v['BehaviorControl'] ) ? $v['BehaviorControl'] : 0;
                        } 
                        elseif ($l == 'EndDate') 
                        {
                            $griddata[$k]['IPRAData_EndDate'] = $v == '0001-01-01' ? '' : $v;
                        } 
                        elseif ($l == 'IssueDate') 
                        {
                            $griddata[$k]['IPRAData_issueDate'] =  $v == '0001-01-01' ? '' : $v;
                        } 
                        elseif ($l == 'IsFirst') 
                        {
                            $griddata[$k]['IPRAData_isFirst'] = $v;
                        } 
                        elseif ($l == 'Person') 
                        {
                            $person      = (array) $v['FIO'];
                            $IdentityDoc = (array) $v['IdentityDoc'];
                            
                            //echo '<pre>' . print_r($person, 1) . '</pre>';
                            $griddata[$k]['IPRAData_Document_Num'] = (string) isset($IdentityDoc['Number']) ? $IdentityDoc['Number'] : '';

                            $FirstName = (string)$person['FirstName'];
                            $LastName = (string)$person['LastName'];
                            $SecondName = (string)$person['SecondName'];

                            $griddata[$k]['Person_FirName'] = !empty($FirstName)?$FirstName:null;
                            $griddata[$k]['Person_SurName'] = !empty($LastName)?$LastName:null;
                            $griddata[$k]['Person_SecName'] = !empty($SecondName)?$SecondName:null;
                            
                            $griddata[$k]['Person_id']          = '';
                            $griddata[$k]['IPRAData_BirthDate'] = $v['BirthDate'];
                            
                            
                            if(isset($v['SNILS'])){    
                                if (is_string($v['SNILS'])) {
                                    $griddata[$k]['IPRAData_SNILS'] = $v['SNILS'];
                                } else {
                                    $griddata[$k]['IPRAData_SNILS'] = $this->object_to_array($v['SNILS'][0]);//(array) $v['SNILS'][0];
                                }
                            }    
                            else{
                                $griddata[$k]['IPRAData_SNILS'] = '';
                            }
                            
                            $griddata[$k]['IPRAData_PersonFIO'] = $person['LastName'] . ' ' . $person['FirstName'] . ' ' . $person['SecondName'];
                            if(!empty($v['PrimaryProfession']))
                                $griddata[$k]['IPRAData_PrimaryProfession'] = $v['PrimaryProfession'];
                            if(!empty($v['PrimaryProfessionExperience']))
                                $griddata[$k]['IPRAData_PrimaryProfessionExp'] = $v['PrimaryProfessionExperience'];
                            if(!empty($v['Qualification']))
                                $griddata[$k]['IPRAData_Qualification'] = $v['Qualification'];
                            if(!empty($v['CurrentJob']))
                                $griddata[$k]['IPRAData_CurrentJob'] = $v['CurrentJob'];
                            if(!empty($v['NotWorkYears']))
                                $griddata[$k]['IPRAData_NotWorkYears'] = $v['NotWorkYears'];
                            if(!empty($v['EmploymentOrientationExists']))
                                    $griddata[$k]['IPRAData_EmploymentOrientationExists'] = ($v['EmploymentOrientationExists'] == "true") ? 2 : 1;
                            if(!empty($v['IsRegisteredInEmploymentService']))
                                $griddata[$k]['IPRAData_IsRegisteredInEmploymentService'] = ($v['IsRegisteredInEmploymentService'] == "true") ? 2 : 1;
                            

                        } 
                        elseif ($l == 'RequiredHelp') 
                        {
                            if (isset($v['HelpItems'])) {
                                $griddata[$k]['IPRAData_RequiredHelp'] = '';
                                // echo '<pre>' . print_r($v['HelpItems'], 1) . '</pre>';
                                // $RequiredHelp = $this->object_to_array($v['HelpItems']->HelpItem->HelpCategory);//(array) $v['HelpItems']->HelpItem->HelpCategory;
                                // $griddata[$k]['IPRAData_RequiredHelp'] = $RequiredHelp['Id'];
                            }// else {
                            //    $griddata[$k]['IPRAData_RequiredHelp'] = '';
                            //}
                        } 
                        elseif ($l == 'DevelopDate') 
                        {
                            if (isset($v[0]))
                                $griddata[$k]['IPRAData_DevelopDate'] = $v;
                        }
                        elseif ($l == 'DisabilityGroup')
                        {
                            if (!empty($v)){
                                $DisabilityGroup = $this->object_to_array($v);
                                $griddata[$k]['IPRAData_DisabilityGroup'] = $DisabilityGroup['Id'];
                            }
                        }
                        elseif ($l == 'DisabilityCause')
                        {
                            if (!empty($v)){
                                $DisabilityCause = $this->object_to_array($v);
                                $griddata[$k]['IPRAData_DisabilityCause'] = $DisabilityCause['Id'];
                            }
                        }
                        elseif ($l == 'DisabilityCauseOther')
                        {
                            if (!empty($v[0])){
                                $griddata[$k]['IPRAData_DisabilityCauseOther'] = $v;
                            }
                        }
                        elseif ($l == 'DisabilityGroupDate')
                        {
                            if (!empty($v[0])) {
                                $griddata[$k]['IPRAData_DisabilityGroupDate'] = $v;
                            }
                        }
                        elseif ($l == 'IsDisabilityGroupPrimary')
                        {
                            if (!empty($v[0])) {
                                $griddata[$k]['IPRAData_IsDisabilityGroupPrimary'] = ($v == "true") ? 2 : 1;
                            }
                        }
                        elseif ($l == 'DisabilityEndDate')
                        {
                            if (!empty($v[0])) {
                                $griddata[$k]['IPRAData_DisabilityEndDate'] = $v;
                            }
                        }
                        elseif ($l == 'SentOrgOGRN')
                        {
                            if (!empty($v[0])) {
                                $griddata[$k]['IPRAData_SentOrgOGRN'] = $v;
                            }
                        }
                        elseif ($l == 'SentOrgName')
                        {
                            if (!empty($v[0])) {
                                $griddata[$k]['IPRAData_DirectionLPU_id'] = $v;
                            }
                        }
                        elseif($l == 'RehabPotential')
                        {
                            if (!empty($v)) {
                                $RehabPotential = $this->object_to_array($v);
                                $griddata[$k]['IPRAData_RehabPotential'] = $RehabPotential['Id'];
                            }
                        }
                        elseif($l == 'RehabPrognoz')
                        {
                            if (!empty($v)) {
                                $RehabPrognoz = $this->object_to_array($v);
                                $griddata[$k]['IPRAData_RehabPrognoz'] = $RehabPrognoz['Id'];
                            }
                        }
                        elseif ($l == 'IsIntramural')
                        {
                            if (!empty($v[0])) {
                                $griddata[$k]['IPRAData_IsIntramural'] = ($v == "true") ? 2 : 1;
                            }
                        }
                        elseif ($l =='PrognozResult')
                        {
                            if (!empty($v['FuncRecovery'])) {
                                $FuncRecovery = $this->object_to_array($v['FuncRecovery']);
                                $griddata[$k]['IPRAData_Restoration'] = $FuncRecovery['Id'];
                            }
                            if (!empty($v['FuncCompensation'])) {
                                $FuncCompensation = $this->object_to_array($v['FuncCompensation']);
                                $griddata[$k]['IPRAData_Compensate'] = $FuncCompensation['Id'];
                            }
                            if (!empty($v['SelfService'])) {
                                $SelfService = $this->object_to_array($v['SelfService']);
                                $griddata[$k]['IPRAData_PrognozSelfService'] = $SelfService['Id'];
                            }
                            if (!empty($v['MoveIndependently'])) {
                                $MoveIndependently = $this->object_to_array($v['MoveIndependently']);
                                $griddata[$k]['IPRAData_PrognozMoveIndependetly'] = $MoveIndependently['Id'];
                            }
                            if (!empty($v['Orientate'])) {
                                $Orientate = $this->object_to_array($v['Orientate']);
                                $griddata[$k]['IPRAData_PrognozOrientate'] = $Orientate['Id'];
                            }
                            if (!empty($v['Communicate'])) {
                                $Communicate = $this->object_to_array($v['Communicate']);
                                $griddata[$k]['IPRAData_PrognozCommunicate'] = $Communicate['Id'];
                            }
                            if (!empty($v['BehaviorControl'])) {
                                $BehaviorControl = $this->object_to_array($v['BehaviorControl']);
                                $griddata[$k]['IPRAData_PrognozBehaviorControl'] = $BehaviorControl['Id'];
                            }
                            if (!empty($v['Learning'])) {
                                $Learning = $this->object_to_array($v['Learning']);
                                $griddata[$k]['IPRAData_PrognozLearning'] = $Learning['Id'];
                            }
                            if (!empty($v['Work'])) {
                                $Work = $this->object_to_array($v['Work']);
                                $griddata[$k]['IPRAData_PrognozWork'] = $Work['Id'];
                            }
                        }
                        elseif ($l =='Representative') {
                            if(!empty($v['FIO'])){
                                $FIO = (array) $v['FIO'];
                                $griddata[$k]['IPRAData_RepPerson_LastName']    = !empty($FIO['LastName']) ? $FIO['LastName'] : '';
                                $griddata[$k]['IPRAData_RepPerson_FirstName']   = !empty($FIO['FirstName']) ? $FIO['FirstName'] : '';
                                $griddata[$k]['IPRAData_RepPerson_SecondName']  = !empty($FIO['SecondName']) ? $FIO['SecondName'] : '';
                            }
                            if(!empty($v['AuthorityDoc'])){
                                $AD = (array) $v['AuthorityDoc'];
                                $griddata[$k]['IPRAData_RepPersonAD_Title']     = !empty($AD['Title']) ? $AD['Title'] : '';
                                $griddata[$k]['IPRAData_RepPersonAD_Series']    = !empty($AD['Series']) ? $AD['Series'] : '';
                                $griddata[$k]['IPRAData_RepPersonAD_Number']    = !empty($AD['Number']) ? $AD['Number'] : '';
                                $griddata[$k]['IPRAData_RepPersonAD_Issuer']    = !empty($AD['Issuer']) ? $AD['Issuer'] : '';
                                $griddata[$k]['IPRAData_RepPersonAD_IssueDate'] = !empty($AD['IssueDate']) ? $AD['IssueDate'] : '';
                            }
                            if(!empty($v['IdentityDoc'])){
                                $ID = (array) $v['AuthorityDoc'];
                                $griddata[$k]['IPRAData_RepPersonID_Title']     = !empty($ID['Title']) ? $ID['Title'] : '';
                                $griddata[$k]['IPRAData_RepPersonID_Series']    = !empty($ID['Series']) ? $ID['Series'] : '';
                                $griddata[$k]['IPRAData_RepPersonID_Number']    = !empty($ID['Number']) ? $ID['Number'] : '';
                                $griddata[$k]['IPRAData_RepPersonID_Issuer']    = !empty($ID['Issuer']) ? $ID['Issuer'] : '';
                                $griddata[$k]['IPRAData_RepPersonID_IssueDate'] = !empty($ID['IssueDate']) ? $ID['IssueDate'] : '';
                            }
                            if(!empty($v['SNILS'])){
                                $griddata[$k]['IPRAData_RepPerson_SNILS'] = str_replace(array(' ','-'), '', $v['SNILS']);
                            }
                        }
                        /* 03.10.17
                         * elseif ($l == 'DysfunctionsDegrees')
                        {
                            $griddata[$k]['IPRAData_Vision']                    = !empty($v['Vision']) ? $v['Vision'] : 0;
                            $griddata[$k]['IPRAData_Hearing']                   = !empty($v['Hearing']) ? $v['Hearing'] : 0;
                            $griddata[$k]['IPRAData_VisionAndHearing']          = !empty($v['VisionAndHearing']) ? $v['VisionAndHearing'] : 0;
                            $griddata[$k]['IPRAData_UpperLimbs']                = !empty($v['UpperLimbs']) ? $v['UpperLimbs'] : 0;
                            $griddata[$k]['IPRAData_BottomLimbs']               = !empty($v['BottomLimbs']) ? $v['BottomLimbs'] : 0;
                            $griddata[$k]['IPRAData_WheelChair']                = !empty($v['WheelChair']) ? $v['WheelChair'] : 0;
                            $griddata[$k]['IPRAData_Intellect']                 = !empty($v['Intellect']) ? $v['Intellect'] : 0;
                            $griddata[$k]['IPRAData_Lingual']                   = !empty($v['Lingual']) ? $v['Lingual'] : 0;
                            $griddata[$k]['IPRAData_BloodCirculation']          = !empty($v['BloodCirculation']) ? $v['BloodCirculation'] : 0;
                            $griddata[$k]['IPRAData_Breath']                    = !empty($v['Breath']) ? $v['Breath'] : 0;
                            $griddata[$k]['IPRAData_Digestive']                 = !empty($v['Digestive']) ? $v['Digestive'] : 0;
                            $griddata[$k]['IPRAData_Metabolism']                = !empty($v['Metabolism']) ? $v['Metabolism'] : 0;
                            $griddata[$k]['IPRAData_BloodAndImmune']            = !empty($v['BloodAndImmune']) ? $v['BloodAndImmune'] : 0;
                            $griddata[$k]['IPRAData_Excretory']                 = !empty($v['Excretory']) ? $v['Excretory'] : 0;
                            $griddata[$k]['IPRAData_Skin']                      = !empty($v['Skin']) ? $v['Skin'] : 0;
                            $griddata[$k]['IPRAData_PhisicalDysfunction']       = !empty($v['PhisicalDysfunction']) ? $v['PhisicalDysfunction'] : 0;
                        }*/
                    }
                    
  
                    //МО в которую определили инвалида
                    /**
                    Если пациент определён в РМИАС и имеет прикрепление с типом "Основное" - он определяется к данному МО (если не псих.)
                    Если пациент - писх. (по номеру бюро) - внезависимости от МО прикрепления - он определяется к МО направления
                    Если пациент не имеет прикрепления в РМИАС то он определяется в МО направления
                    в противном случае - ручной ввод МО
                    */
                      
                    
                    if (isset($griddata[$k]['IPRAData_DirectionLPU_id'])) {
                        $crazy = $this->crazy;
                        if (in_array($griddata[$k]['IPRAData_FGUMCEnumber'], $crazy['ufa'])) {
                            $griddata[$k]['IPRAData_LpuName'] = $griddata[$k]['IPRAData_DirectionLPU_id'];
                        } 
                        else {
                            if ($griddata[$k]['LpuAttachName'] == '') {
                                $griddata[$k]['IPRAData_LpuName'] = $griddata[$k]['IPRAData_DirectionLPU_id'];
                            } 
                            else {
                                $griddata[$k]['IPRAData_LpuName'] = $griddata[$k]['LpuAttachName'];
                            }
                        }
                    } else {
                        $griddata[$k]['IPRAData_LpuName'] = '';
                    }           
                    
                    $result[] = $griddata[$k];
                    //echo '<pre>' . print_r($result, 1) . '</pre>';
                    @unlink($file);
                }
            }

            if($count_xml == 0){
                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Некорректное содержание архива. Ожидаются XML файлы.')
                )));                
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $result
            ));
        } 
        else {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 10002,
                'Error_Msg' => toUTF('Данный тип сжатия файлов не поддерживается.')
            )));
        }
        //@rmdir($upload_path);
    }
    
    /**
     * Импорт доп. полей
     */
    function IPRARegistryDopImport()
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", "0");
        ini_set("max_input_time", "0");
        ini_set("post_max_size", "220");
        ini_set("default_socket_timeout", "999");
        ini_set("upload_max_filesize", "220M");
        /**/

        $dirMCE        = 'mce';
        $upload_path   = './' . IMPORTPATH_ROOT . $dirMCE;

        $allowed_types = explode('|', 'zip');

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        if (!file_exists($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Ошибка загрузки файлов в директорию.')
            )));
        }



        $err = getInputParams($data, $this->inputRules['IPRARegistryDopImport']);
        if (!empty($err)) {
            $this->ReturnError($err);
            return false;
        }

        //echo '<pre>' . print_r($_FILES, 1) . '</pre>';

        //exit;
        if (!isset($_FILES['IPRARegistry_import'])) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100011,
                'Error_Msg' => toUTF('Не выбран файл для импорта!')
            )));
        }

        if (!is_uploaded_file($_FILES['IPRARegistry_import']['tmp_name'])) {
            $error = (!isset($_FILES['IPRARegistry_import']['error'])) ? 4 : $_FILES['IPRARegistry_import']['error'];
            switch ($error) {
                case 1:
                    $message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
                    break;
                case 2:
                    $message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
                    break;
                case 3:
                    $message = 'Этот файл был загружен не полностью.';
                    break;
                case 4:
                    $message = 'Вы не выбрали файл для загрузки.';
                    break;
                case 6:
                    $message = 'Временная директория не найдена.';
                    break;
                case 7:
                    $message = 'Файл не может быть записан на диск.';
                    break;
                case 8:
                    $message = 'Неверный формат файла.';
                    break;
                default:
                    $message = 'При загрузке файла произошла ошибка.';
                    break;
            }
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100012,
                'Error_Msg' => toUTF($message)
            )));
        }


        // Тип файла разрешен к загрузке?
        $x  = explode('.', $_FILES['IPRARegistry_import']['name']);
        $file_data['file_ext'] = end($x);
        if (!in_array(mb_strtolower($file_data['file_ext']), $allowed_types)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Данный тип файла не разрешен.')
            )));
        }

        // Правильно ли указана директория для загрузки?
        if (!@is_dir($upload_path)) {
            mkdir($upload_path);
        }

        if (!@is_dir($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100014,
                'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')
            )));
        }

        // Имеет ли директория для загрузки права на запись?
        if (!is_writable($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100015,
                'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')
            )));
        }

        $zip = new ZipArchive;

        if ($zip->open($_FILES['IPRARegistry_import']['tmp_name']) === TRUE) {
            $zip->extractTo($upload_path);

            $zip->close();

            $files = scandir($upload_path);

            $data = array();



            $result = array();

            $count = glob($upload_path.'/*.xml');

            if (sizeof($count) > 20000) {
                foreach ($files as $k => $filename) {
                    $file = iconv('CP866', 'utf-8', $upload_path . '/' . $filename);
                    if (!in_array($filename, array(
                        '.',
                        '..'
                    ))) {
                        @unlink($file);
                    }
                }

                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Привышени лимит импорта. Максимальный размер архива 20000 XML файлов')
                )));
            }

            $files = array_reverse($files);
            $count_xml = 0;
            foreach ($files as $k => $filename) {
                $file_info = pathinfo($filename);

                if (!in_array($filename, array('.','..')) && isset($file_info['extension']) && $file_info['extension'] == 'xml') {
                    $count_xml++;
                    //echo iconv('CP866','utf-8', $filename).PHP_EOL;
                    $file     = iconv('CP866', 'utf-8', $upload_path . '/' . $filename);
                    //echo $file."\r\n";

                    if (@file_get_contents($file) === false) {

                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Некорректное содержание ахива. Ожидаются XML файлы.')
                        )));
                    } else {
                        $xmlstr = file_get_contents($file);
                    }

                    $xmlstr = strtr($xmlstr, array(
                        'ct:' => ''
                    ));

                    try{
                        $xml = @new SimpleXMLElement($xmlstr);

                    }
                    catch(Exception $e){
                        @unlink($file);
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Невалидный XML файл: '.$filename),
                            'Error_Txt' => $e->getMessage(),
                        )));
                    }

                    $xml_arr = $this->object_to_array($xml);

                    //echo '<pre>' . print_r($xml_arr, 1) . '</pre>';

                    foreach ($xml_arr as $j => $node) {
                        $data[$k][$j] = $this->object_to_array($node);
                    }

                    $row = array();
                    foreach ($data[$k] as $l => $v) {
						if ($l == 'Id') {
							$row['IPRARegistry_IPRAident']    = $v;
						} elseif ($l == 'Recipient') {
							if (isset($v['RecipientType'])) {
								$RecipientType = $this->object_to_array($v['RecipientType']);

								$row['IPRARegistry_RecepientType'] = $RecipientType['Id'];
							}
						} elseif ($l == 'Number') {
							$row['IPRARegistry_Number'] = $v;
                        } elseif ($l == 'IssueDate') {
							$row['IPRARegistry_issueDate'] = $v;
						} elseif ($l == 'ProtocolNum') {
							$row['IPRARegistry_Protocol'] = $v;
						}
                    }

					// обновляем поля в БД
					if (!empty($row['IPRARegistry_Number'])) {
						$this->dbmodel->updateIPRARegisterDopFields($row);
					}

                    @unlink($file);
                }
            }

            if($count_xml == 0){
                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Некорректное содержание ахива. Ожидаются XML файлы.')
                )));
            }

            echo json_encode(array(
                'success' => true
            ));
        } else {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 10002,
                'Error_Msg' => toUTF('Данный тип сжатия файлов не поддерживается.')
            )));
        }

        //@rmdir($upload_path);
    }
    
    /**
     * Добавления пациента в рагистр ИПРА
     */
    function IPRARegistry_save()
    {
        
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
	 * Проверка поля на пустое значение
	 */
	function isEmpty($data)
    {
		if ( isset( $data ) && !empty( $data ) ) {
			log_message('error', 'isEmpty0 data: '.$data);
			return 0;
		}
		log_message('error', 'isEmpty1 data: '.$data);
		return 1;
	}
	
	/**
	 * Проверка полей реабилитации на пустое значение
	 */
	function rehabIsEmpty($data, $parName)
    {
		if ( isset($data[$parName]) && $data[$parName] == 2 ) {
			//log_message('error', 'rehabIsEmpty '.$parName.': '.$data[$parName]);
			//log_message('error', 'rehabIsEmpty '.$parName.'BeginDate: '.$data[$parName.'BeginDate']);
			log_message('error', 'rehabIsEmpty '.$parName.'_begDate: '.$data[$parName.'_begDate']);
			return $this->isEmpty( $data[$parName.'_begDate'] );
		}
		log_message('error', 'rehabIsEmpty0 '.$parName.': '.$data[$parName]);
		return 0;
	}
	
    /**
     *  Проверка корректности данных ИПРА
     */
    function IPRAdataIsValid($data)
    {
		if (
			/*$this->isEmpty( $data['IPRARegistryData_SelfService'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Move'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Orientation'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Communicate'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Learn'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Work'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Behavior'] ) ||*/
			$this->isEmpty( $data['IPRARegistry_isFirst'] ) ||
			$this->isEmpty( $data['IPRARegistry_issueDate'] ) ||
			$this->isEmpty( $data['IPRARegistry_FGUMCEnumber'] ) ||
			$this->isEmpty( $data['IPRARegistry_Number'] ) ||
			$this->isEmpty( $data['IPRARegistry_Protocol'] ) ||
			$this->isEmpty( $data['IPRARegistry_ProtocolDate'] ) ||
                        $this->isEmpty( $data['IPRARegistry_DevelopDate'] ) /*||

			$this->rehabIsEmpty( $data, 'IPRARegistryData_MedRehab' ) ||
			$this->rehabIsEmpty( $data, 'IPRARegistryData_ReconstructSurg' ) ||
			$this->rehabIsEmpty( $data, 'IPRARegistryData_Orthotics' ) ||

			$this->isEmpty( $data['IPRARegistryData_Restoration'] ) ||
			$this->isEmpty( $data['IPRARegistryData_Compensate'] ) */
		) {
			$result = 1;// данные Не валидны (имеются пустые поля)
		} else {
        		$result = 2;// данные валидны
		}
		return $result;
    }
    
    /**
     *  Проверка корректности данных ИПРА 2.0
     */
    function IPRAdataIsValid_NewFormat($data)
    {
		if (
			$this->IPRAdataIsValid($data) == 1 //||
                        //$this->isEmpty( $data['IPRARegistryData_DisabilityGroup'] ) ||
                        //$this->isEmpty( $data['IPRARegistryData_DisabilityCause'] ) ||
                        //$this->isEmpty( $data['IPRARegistryData_RepPerson_LastName'] )   ||
                        //$this->isEmpty( $data['IPRARegistryData_RepPerson_FirstName'] )  ||
                        //$this->isEmpty( $data['IPRARegistryData_RepPerson_SecondName'] ) ||
                        //$this->isEmpty( $data['IPRARegistryData_RepPerson_SNILS'] ) ||
                        //($data['IPRARegistryData_DisabilityCause'] == 16 && $this->isEmpty( $data['IPRARegistryData_DisabilityCauseOther'] ))
		) {
			$result = 1;// данные Не валидны (имеются пустые поля)
		} else {
        		$result = 2;// данные валидны
		}
		return $result;
    }
    
    /**
     *  Проверка корректности данных ИПРА - для вызова извне
     */
    function checkIPRAdataIsValid()
    {
        $data = $this->ProcessInputData('checkIPRAdataIsValid', true);
        if ($data === false) {
			log_message('error', 'checkIPRAdataIsValid data=false');
            return false;
        }
                $result = 1;
        if($data['IPRARegistry_Version'] < 2.0){
                    $result = $this->IPRAdataIsValid($data);
        } else {
                    $result = $this->IPRAdataIsValid_NewFormat($data);
                }
		$response = array('isValid' => $result, 'idx'=>$data['idx']);
		$this->ReturnData($response);
	}
    
    /**
     * Добавления пациента в PersonRegister
     */
    function saveInPersonRegister()
    {
        $checkPersonInRegister = $this->checkPersonInRegister();
        /**
         *  Выписок ИПРА для 1го пациента может быть несколько, но в PersonRegister - он попадёт 1 раз только
         */
        //echo '<pre>' . var_dump($checkPersonInRegister) . '</pre>';
        //exit;
        if ($checkPersonInRegister === false) {
            return false;
        } else {
            $data = $this->ProcessInputData('saveInPersonRegister', true);
            if ($data === false) {
                return false;
            }
            
            $response = $this->dbmodel->saveInPersonRegister($data);
            return $response;
            //$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
        }
    }
    
	/**
	 * Количество записей в регистре ИПРА
	 */
	function getIpraCount() {
		$response = $this->dbmodel->getIpraCount();
		$this->ReturnData($response);
	}
}