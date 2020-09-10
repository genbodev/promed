<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DrugRequest - контроллер для работы с заявкой медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Andrew Markoff 
 * @version      27.09.2009
 */

/**
 * @property DrugRequest_model $drmodel
 */
class Eco extends swController
{
	public $options = array();
	protected $inputRules = array(		
            'ecoChange' => array(
			array(
				'field' => 's_dateAdd',
				'label' => 'Дата включения',
				'rules' => 'required',
				'type' => 'string'
			),
                        array(
				'field' => 's_pers_id',
				'label' => 'персон ид',
				'rules' => 'required',
				'type' => 'int'
			),
                        array(
				'field' => 's_eco_id',
				'label' => 'эко ид',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_vid_oplod',
				'label' => 'Вид оплодотворения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_vid_oplat',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'int'
			),
                        array(
				'field' => 'dsOsn',
				'label' => 'основной диагноз',
				'rules' => '',
				'type' => 'int'
			),
                        array(
				'field' => 'vidBer',
				'label' => 'вид беременности',
				'rules' => '',
				'type' => 'int'
			),
                        array(
				'field' => 'countPlod',
				'label' => 'количество плодов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_gen_diag',
				'label' => 'Генет. Диагностика',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_count_embrion',
				'label' => 'Количество эмбрионов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_res_eco',
				'label' => 'Результат ЭКО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_ds_eco',
				'label' => 'Диагноз ЭКО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 's_res_date',
				'label' => 'Дата результата ЭКО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 's_pers_reg_id',
				'label' => 'Перс.Регист ид',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 's_oslognen',
				'label' => 'Осложнения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 's_pmUser',
				'label' => 'пользователь',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'lpu',
				'label' => 'lpu_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_sid',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'int'
			)
		),
            'loadEcoSluch' => array(
                array(
                        'field' => 'PersID',
                        'label' => 'ИД Человека',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'Eco_id',
                        'label' => 'ИД Эко',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'DateAdd',
                        'label' => 'Дата включения',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'PersonRegisterEco_ResultDate',
                        'label' => 'Дата результата ЭКО',
                        'rules' => '',
                        'type' => 'string'
                )
            ),
            
             'checkLastRes' => array(
                array(
                        'field' => 'Pers_id',
                        'label' => 'ИД Человека',
                        'rules' => 'required',
                        'type' => 'int'
                ),
                array(
                        'field' => 'no_res',
                        'label' => 'прошлый результат',
                        'rules' => '',
                        'type' => 'int'
                ),
            ),
            'loadEcoOsl' => array(
                array(
                        'field' => 'Eco_id',
                        'label' => 'ИД Эко',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'DateOsl',
                        'label' => 'дата чего то там',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'Osl',
                        'label' => 'осложнение',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'Osl_id',
                        'label' => 'ид осложнения',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'Ds',
                        'label' => 'Диагноз',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'Ds_int',
                        'label' => 'ИД диагноза',
                        'rules' => '',
                        'type' => 'int'
                ),
				array(
                        'field' => 'EcoOsl_id',
                        'label' => 'Идентификатор услуги',
                        'rules' => '',
                        'type' => 'int'
                ),				
            ),
            'loadEcoSluchData' => array(
                array(
                        'field' => 'Eco_id',
                        'label' => 'ИД Эко',
                        'rules' => 'required',
                        'type' => 'int'
                ),
                array(
                        'field' => 'DateAdd',
                        'label' => 'дата чего то там',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'VidOplod',
                        'label' => 'вид оплодотворения',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'VidOplat',
                        'label' => 'вид оплаты',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'GenetigDiag',
                        'label' => 'GenetigDiag',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'EnbrionCount',
                        'label' => 'EnbrionCount',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'Result',
                        'label' => 'Result',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'ResultDate',
                        'label' => 'Result',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'dsOsn',
                        'label' => 'основной диагноз',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'vidBer',
                        'label' => 'вид беременности',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'countPlod',
                        'label' => 'количество плодов',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                    'field' => 'pmUser_id',
                    'label' => 'пользователь',
                    'rules' => '',
                    'type' => 'int'
                ),array(
                    'field' => 'lpu_id',
                    'label' => 'ид лпу',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadEcoUsl' => array(
                array(
                        'field' => 'PersID',
                        'label' => 'ИД Человека',
                        'rules' => 'required',
                        'type' => 'int'
                ),                
                array(
                        'field' => 'DateUsl',
                        'label' => 'дата чего то там',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'CodeUsl',
                        'label' => 'код услуги',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'NameUsl',
                        'label' => 'Result',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'DateUslBeg',
                        'label' => 'код услуги',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'DateUslEnd',
                        'label' => 'Result',
                        'rules' => '',
                        'type' => 'string'
                )
            ),
            'checkCrossingSluch' => array(
                array(
                        'field' => 'Person_id',
                        'label' => 'ИД Человека',
                        'rules' => 'required',
                        'type' => 'int'
                ),                
                array(
                        'field' => 'ResDate',
                        'label' => 'дата чего то там',
                        'rules' => '',
                        'type' => 'string'
                )
            ),
            'checkOpenEco' => array(
                array(
                        'field' => 'Person_id',
                        'label' => 'ИД Человека',
                        'rules' => 'required',
                        'type' => 'int'
                ),
				array(
					'field' => 'lpu',
					'label' => 'lpu_id',
					'rules' => '',
					'type' => 'int'
				)
            ),			
            'addEcoUsl' => array(
                array(
                        'field' => 'persID',
                        'label' => 'ИД Человека',
                        'rules' => 'required',
                        'type' => 'int'
                ),
                array(
                        'field' => 'dateUsl',
                        'label' => 'дата услуги',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'codeUsl',
                        'label' => 'код услуги',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'nameUsl',
                        'label' => 'название услуги',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'pmUser',
                        'label' => 'пользователь',
                        'rules' => '',
                        'type' => 'int'
                ),
                array(
                        'field' => 'EcoUsluga_id',
                        'label' => 'Идентификатор услуги',
                        'rules' => '',
                        'type' => 'int'
                )
            ),
            'delEcoUsl' => array(
                array(
                        'field' => 'uslId',
                        'label' => 'ИД услуги',
                        'rules' => 'required',
                        'type' => 'int'
                )
            ),
            'getDiagList' => array(
                array(
                        'field' => 'Person_id',
                        'label' => 'ИД Человека',
                        'rules' => 'required',
                        'type' => 'int'
                ),
                array(
                        'field' => 'Diag_setDate',
                        'label' => 'дата диагноза',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'Diag_Code',
                        'label' => 'Шифр МКБ',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'Diag_Name',
                        'label' => 'Диагноз',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'Lpu_Nick',
                        'label' => 'ЛПУ',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'LpuSectionProfile_Name',
                        'label' => 'Профиль',
                        'rules' => '',
                        'type' => 'string'
                ),
                array(
                        'field' => 'MedPersonal_Fio',
                        'label' => 'Врач',
                        'rules' => '',
                        'type' => 'string'
                )
            ),					
			'getResultPregnancyTree' => array(
				array(
					'field' => 'node',
					'label' => 'Нода дерева',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор записи в регистре беременных',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnSection_id',
					'label' => 'Идентификатор движения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnSection_setDate',
					'label' => 'Дата начала движения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnSection_disDate',
					'label' => 'Дата окончания движения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'createCategoryMethod',
					'label' => 'Метод для создания категорий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'deleteCategoryMethod',
					'label' => 'Метод для удаления категорий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'allowCreateButton',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'allowDeleteButton',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'PersonRegisterEco_id',
					'label' => 'Идентификатор движения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Status',
					'label' => 'Статус дерева',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Eco_id',
					'label' => 'Идентификатор эко случая',
					'rules' => '',
					'type' => 'id'
				),				
			),
			'saveBirthSpecStac' => array(
				array(
					'field' => 'BirthSpecStac_id',
					'label' => 'Идентфикатор специфики по родам',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор записи в регистре беременных',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентфикатор события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnSection_id',
					'label' => 'Идентфикатор движения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'МО исхода',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_oid',
					'label' => 'Врач, создающий исход',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PregnancySpec_id',
					'label' => 'Идентификатор специфики по беременности в карте ДУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BirthSpecStac_CountPregnancy',
					'label' => 'Которая беременность',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'BirthSpecStac_OutcomDate',
					'label' => 'Дата исхода беременности',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'BirthSpecStac_OutcomTime',
					'label' => 'Время исхода беременности',
					'rules' => 'required',
					'type' => 'time'
				),
				array(
					'field' => 'BirthSpecStac_OutcomPeriod',
					'label' => 'Срок беременности',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'PregnancyResult_id',
					'label' => 'Исход беременности',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'BirthSpecStac_BloodLoss',
					'label' => 'Кровопотери',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'BirthSpecStac_IsRWtest',
					'label' => 'Обследование на сифилис',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsRW',
					'label' => 'Обследование на сифилис сероположительное',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsHIVtest',
					'label' => 'Обследование на ВИЧ',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsHIV',
					'label' => 'Обследование на ВИЧ сероположительное',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsHBtest',
					'label' => 'Обследование на гепатит B',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsHB',
					'label' => 'Обследование на гепатит B сероположительное',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsHCtest',
					'label' => 'Обследование на гепатит C',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_IsHC',
					'label' => 'Обследование на гепатит C сероположительное',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_CountPregnancy',
					'label' => 'Которая беременность',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'BirthSpecStac_CountChild',
					'label' => 'Количество плодов',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'BirthSpecStac_CountChildAlive',
					'label' => 'Количество живорожденных',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BirthSpecStac_CountBirth',
					'label' => 'Роды которые',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BirthPlace_id',
					'label' => 'Место родов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BirthSpec_id',
					'label' => 'Особенности родов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BirthCharactType_id',
					'label' => 'Характер родов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BirthSpecStac_SurgeryVolume',
					'label' => 'Объем оперативного вмешательства при внематочной беременности',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AbortLpuPlaceType_id',
					'label' => 'Место аборта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AbortLawType_id',
					'label' => 'Вид аборта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AbortMethod_id',
					'label' => 'Метод аборта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AbortIndicat_id',
					'label' => 'Показания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BirthSpecStac_InjectVMS',
					'label' => 'Введено ВМС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'BirthSpecStac_IsContrac',
					'label' => 'Послеродовая контрацепция',
					'rules' => '',
					'type' => 'swcheckbox'
				),
				array(
					'field' => 'BirthSpecStac_ContracDesc',
					'label' => 'Сведения о послеродовой контрацепции',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Answers',
					'label' => 'Ответы из анкеты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ChildDeathData',
					'label' => 'Данные о мертворожденных',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ignoreCheckBirthSpecStacDate',
					'label' => 'Признак игнорирования проверки даты исхода беременности',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ignoreCheckChildrenCount',
					'label' => 'Признак игнорирования проверки количества плодов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Status',
					'label' => 'Статус дерева',
					'rules' => '',
					'type' => 'int'
				),				
				array(
					'field' => 'Eco_id',
					'label' => 'Статус дерева',
					'rules' => '',
					'type' => 'int'
				),				
			),
			'getPersonRegisterEco' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор записи в регистре ЭКО',
					'rules' => 'required',
					'type' => 'id'
				),
			),		
			'Delete' => array(
				array(
					'field' => 'Eco_id',
					'label' => 'Идентификатор случая в записи регистра ЭКО',
					'rules' => 'required',
					'type' => 'int'
				),
			)			
	);

	/**
	 * Функция
	 */
	function __construct() {
		parent::__construct();
        $this->load->database();
		if (isset($_REQUEST['VolPeriod_id']))
		{
			if ($_REQUEST['VolPeriod_id']==12010)
			{
				$this->options['normativ_fed_lgot'] = 400;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 75;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==22010)
			{
				$this->options['normativ_fed_lgot'] = 560;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 125;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==32010)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 190;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==42010)
			{
				$this->options['normativ_fed_lgot'] = 570;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 190;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==12011)
			{
				$this->options['normativ_fed_lgot'] = 600;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 100;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==22011)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 110;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==32011)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 130;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==42011)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 130;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==12012)
			{
				$this->options['normativ_fed_lgot'] = 630;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 140;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==22012)
			{
				$this->options['normativ_fed_lgot'] = 630;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 140;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==52012)
			{
				$this->options['normativ_fed_lgot'] = 630;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 140;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==32012)
			{
				$this->options['normativ_fed_lgot'] = 800;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 180;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==42012)
			{
				$this->options['normativ_fed_lgot'] = 800;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 180;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['VolPeriod_id']==12013)
			{
				$this->options['normativ_fed_lgot'] = 700;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 220;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(22013, 32013, 42013, 12014, 22014, 32014, 42014)))
			{
				$this->options['normativ_fed_lgot'] = 650;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 250;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(42014)))
			{
				$this->options['normativ_fed_lgot'] = 380;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 50;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(12015)))
			{
				$this->options['normativ_fed_lgot'] = 390;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 70;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(22015, 32015, 42015)))
			{
				$this->options['normativ_fed_lgot'] = 390;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 75;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(62036)))
			{
				$this->options['normativ_fed_lgot'] = 310;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 90;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(62039)))
			{
				$this->options['normativ_fed_lgot'] = 408;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 130;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['VolPeriod_id'], array(62157)))
			{
				$this->options['normativ_fed_lgot'] = 425;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 138;
				$this->options['koef_reg_lgot'] = 1;
			}
		}
	}

    /**
	 * Получение методов
	 */

    
    function addEcoUsl() { 
        $data = $this->ProcessInputData('addEcoUsl', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->addEcoUsl($data); 
            $this->ProcessModelSave($response, true, $response)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * Изменение/добавление ЭКО
     */
    
    function ecoChange() { 
        $data = $this->ProcessInputData('ecoChange', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->ecoChange($data); 
            $this->ProcessModelSave($response, true, $response)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * Удаление услуги
     */
    function delEcoUsl() { 
        $data = $this->ProcessInputData('delEcoUsl', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->delEcoUsl($data); 
            $this->ProcessModelSave($response, true, $response)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * Проверка последнего результата
     */
    function checkLastRes() { 
        $data = $this->ProcessInputData('checkLastRes', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->checkLastRes($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * загрузка случаев ЭКО
     */
    function loadEcoSluch() { 
        $data = $this->ProcessInputData('loadEcoSluch', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->loadEcoSluch($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * загрузка данных случая
     */
    function loadEcoSluchData() { 
        $data = $this->ProcessInputData('loadEcoSluchData', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->loadEcoSluchData($data); 
            $this->ProcessModelList($response, true, $response)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * загрузка осложнений
     */
    function loadEcoOsl() { 
        $data = $this->ProcessInputData('loadEcoOsl', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->loadEcoOsl($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * загрузка услуг
     */
    function loadEcoUsl() { 
        $data = $this->ProcessInputData('loadEcoUsl', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->loadEcoUsl($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }    
    
    /**
     * загрузка уточненных диагнозов
     */
    function getDiagList() { 
        $data = $this->ProcessInputData('getDiagList', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->getDiagList($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
    
    /**
     * проверка пересечения случаев
     */
    function checkCrossingSluch() { 
        $data = $this->ProcessInputData('checkCrossingSluch', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->checkCrossingSluch($data); 
            $this->ProcessModelSave($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }
	
    /**
     * проверка наличия открытых случаев в других МО
     */
    function checkOpenEco() { 
        $data = $this->ProcessInputData('checkOpenEco', true, true); 
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->checkOpenEco($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 
    }	

	/**
	*  Получение дерева для просмотра/создания Исхода по Анкете Регистра беременности
	*  Входящие данные: -
	*  На выходе: JSON-строка
	*/
	function getResultPregnancyTree() {	
		
		$data = $this->ProcessInputData('getResultPregnancyTree', true);
		if ($data === false) { return false; }		

		//Если идентификатора еко нет, то не рисуем дерево при автозагрузке
		if ($data['PersonRegisterEco_id'] == '') return false;		
		
		$this->load->model('Eco_model');
		$this->load->model('PersonPregnancy_model');		
		
		//находим идентификатор исхода
		$arr_birthSpecStac = $this->Eco_model->getBirthSpecStacId($data);		
		if (is_array($arr_birthSpecStac) && sizeof($arr_birthSpecStac)>0){
			$data['BirthSpecStac_id'] = $arr_birthSpecStac[0]['BirthSpecStac_id'];
		}		
		
		$val = array();		
			
		$items = array(
			'Result' => array('id'=>'Result','object' =>'Result','text' => 'Исход','leaf' => true,'grid'=>false)
		);
		$allowPregnancyRegisterAccess = true;
					
		$createCategoryMethod = !empty($data['createCategoryMethod'])?$data['createCategoryMethod']:null;
		$allowCreateButton = (!empty($createCategoryMethod) && !empty($data['allowCreateButton']))?$data['allowCreateButton']:false;

		$deleteCategoryMethod = !empty($data['deleteCategoryMethod'])?$data['deleteCategoryMethod']:null;
		$allowDeleteButton = (!empty($deleteCategoryMethod) && !empty($data['allowDeleteButton']))?$data['allowDeleteButton']:false;					
					
		foreach($items as $name => &$item) {
			$allowCreate = true;
			switch($name) {
				case 'Result':
					$access = false;
					
					if (!empty($data['Evn_id'])) {
						$access = (empty($info['ResultEvn_id']) || $data['Evn_id'] == $info['ResultEvn_id']);
					} else if($allowPregnancyRegisterAccess) {
						$access = (empty($info['ResultEvn_id']) || $data['Lpu_id'] == $info['ResultLpu_id']);
					}
					
					$item['readOnly'] = true;
					if (empty($data['BirthSpecStac_id'])) {
						if ($allowCreate) {
							$item['text'] .= " <span class=\"link create\" onclick=\"{$createCategoryMethod}('Result')\">Создать</span>";
						}
						$item['key'] = null;
					} else {
						$item['key'] = $data['BirthSpecStac_id'];
						$risk = $this->PersonPregnancy_model->recalculatePregnancyQuestionBirthSpecStacRisk($data);
						$item['text'] .= " (Интранатальные факторы риска {$risk})";

						if ($access && $allowDeleteButton) {
							if ($data['Status'] != 4){
								$item['text'] .= " <span class=\"link delete\" onclick=\"{$deleteCategoryMethod}('Result', {$data['BirthSpecStac_id']})\">Удалить</span>";
							}
						}
					}
					break;
			}
		}

		$person_pregnancy_nodes = array_values($items);											
		$val = array_merge($val, $person_pregnancy_nodes);			
		$this->ReturnData($val);

		return true;
	}	
	
	/**
	 * Сохранение данных исхода беременности
	 */
	function saveBirthSpecStac() {
		$data = $this->ProcessInputData('saveBirthSpecStac', true);
		if ($data === false) { return false; }

		$this->load->model('Eco_model');		
		$response = $this->Eco_model->saveBirthSpecStac($data);		

		if (isset($response[0]) && !empty($response[0]['Error_Msg']) && $response[0]['Error_Msg'] == 'YesNo') {
			$response[0]['Alert_Msg'] = $this->dbmodel->getAlertMsg();
		}
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}		
	
	/**
	*  Получение списка случаев пациента в регистре ЭКО
	*/
	function getPersonRegisterEco() {			
		
        $data = $this->ProcessInputData('getPersonRegisterEco', true, true); 
		if ($data === false) { return false; }		
		
        if ($data) { 
            $this->load->model('Eco_model', 'Eco_model'); 
            $response = $this->Eco_model->getPersonRegisterEco($data); 
            $this->ProcessModelList($response, true, true)->ReturnData(); 
            return true; 
            }
        else { 
            return false; 
        } 		
	}	
	
	/**
	*  Удаление случая регистра ЭКО
	*/
	function Delete() {			
		
		$data = $this->ProcessInputData('Delete', true);
		if ($data === false) { return false; }
		
		$this->load->model('Eco_model');
		
		$response = $this->Eco_model->Delete($data);		

		if (isset($response[0]) && !empty($response[0]['Error_Msg']) && $response[0]['Error_Msg'] == 'YesNo') {
			$response[0]['Alert_Msg'] = $this->dbmodel->getAlertMsg();
		}
		
		return true; 		
	}		
}