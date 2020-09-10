<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * VaccineCtrl - контроллер для учета и планирования вакцинации
 *     - Перечень журналов вакцинации (loadJournals)
 *     - Журнал План Прививок (loadVacPlan)
 *     - Поиск в Журнале "Список карт профилактических прививок" (searchVacMap)
 *
 * @access       public
 * @copyright    Copyright (c) 2012 Progress
 * @author       ArslanovAZ
 * @version      16.04.2012
 */
class VaccineCtrl extends swController {

    /**
     * Описание правил для входящих параметров
     * @var array
     */
    public $inputRules = array();
    var $model_name = "VaccineCtrl_model";

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, "dbmodel");
        $this->inputRules = array(
			'loadContainerMedicinesViewGrid' => array(
				array(
					'field' => 'DocumentUcStr_id',
					'label' => 'Идентификатор строки документа учета',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveDocumentUcStrIDforJournalAccount' => array(
				array(
					'field' => 'DocumentUcStr_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'vacJournalAccount_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
            'loadJournals' => array(
                array(
                    'field' => 'Journal_id',
                    'label' => 'Идентификатор журнала',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Name',
                    'label' => 'Наименование',
                    'rules' => 'required',
                    'type' => 'string'
                )
            ),
            'loadImplFormInfo' => array(
                array(
                    'field' => 'vac_jaccount_id',
                    'label' => 'vac_jaccount_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'user_id',
                    'label' => 'user_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadImplVacNoPurpFormInfo' => array(
                array(
                    'field' => 'vaccine_id',
                    'label' => 'vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadPurpFormInfo' => array(
                array(
                    'field' => 'plan_id',
                    'label' => 'plan_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'user_id',
                    'label' => 'user_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Vac_Scheme_id',
                    'label' => 'Vac_Scheme_id',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'vacJournalAccount_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadMantuFormInfo' => array(
                array(
                    'field' => 'plan_tub_id',
                    'label' => 'plan_tub_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'user_id',
                    'label' => 'user_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadJournalMantuFormInfo' => array(
                array(
                    'field' => 'fix_tub_id',
                    'label' => 'fix_tub_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadRefuseFormInfo' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'refuse_id',
                    'label' => 'id медотвода',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'searchVacMap' => array(
					array(
						'field' => 'getCountOnly',
						'label' => 'Посчитать только каунт',
						'rules' => '',
						'type' => 'id'
					),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Sex_id',
                    'label' => 'Идентификатор пола',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'sex',
                    'label' => 'Пол',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Address',
                    'label' => 'Адрес',
                    'rules' => '',
                    'type' => 'string'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => '',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'SocStatus_Name',
                    'label' => 'Соц статус',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ вакцинации',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_Name',
                    'label' => 'Наименование ЛПУ',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'group_risk',
                    'label' => 'Группа риска',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Date_Change',
                    'label' => 'Период внесения изменений',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'default' => 'off',
                    'field' => 'ImplVacOnly',
                    'label' => 'Учитывать только исполненные прививки',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchVacPlan' => array(
				array(
						'field' => 'getCountOnly',
						'label' => 'Посчитать только каунт',
						'rules' => '',
						'type' => 'id'
					),
                array(
                    'default' => '',
                    'field' => 'storeType',
                    'label' => 'Тип store',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'planTmp_id',
                    'label' => 'planTmp_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'sex',
                    'label' => 'Пол',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Date_Plan',
                    'label' => 'План-дата',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_Name',
                    'label' => 'Наименование ЛПУ',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'group_risk',
                    'label' => 'Группа риска',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'type_name',
                    'label' => 'Тип',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Name',
                    'label' => 'Наименование',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SequenceVac',
                    'label' => 'SequenceVac',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'date_S',
                    'label' => 'Нач дата',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'date_E',
                    'label' => 'Конеч дата',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => '',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'VaccineType_id',
                    'label' => 'VaccineType_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchVacAssigned' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'vacJournalAccount_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Date_Purpose',
                    'label' => 'date_purpose',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'sex',
                    'label' => 'Пол',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_name',
                    'label' => 'vac_name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'NAME_TYPE_VAC',
                    'label' => 'NAME_TYPE_VAC',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'VACCINE_DOZA',
                    'label' => 'VACCINE_DOZA',
                    'rules' => '',
                    'type' => 'id'
                ),
                //            array(
                //                'field' => 'VaccineWay_id',
                //                'label' => 'VaccineWay_id',
                //                'rules' => '',
                //                'type' => 'id'
                //            ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'VaccineType_id',
                    'label' => 'VaccineType_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchVacRegistr' => array(
				array(
						'field' => 'getCountOnly',
						'label' => 'Посчитать только каунт',
						'rules' => '',
						'type' => 'id'
					),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'vacJournalAccount_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Date_Vac',
                    'label' => 'Date_Vac',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                //            array(
                //                'field' => 'Date_Purpose',
                //                'label' => 'Date_Purpose',
                //                'rules' => 'trim',
                //                'type' => 'date'
                //            ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                //            array(
                //                'field' => 'BirthDay',
                //                'label' => 'Дата рождения',
                //                'rules' => 'trim',
                //                'type' => 'date'
                //            ),
                //            array(
                //                'field' => 'sex',
                //                'label' => 'Пол',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_name',
                    'label' => 'vac_name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'NAME_TYPE_VAC',
                    'label' => 'NAME_TYPE_VAC',
                    'rules' => '',
                    'type' => 'string'
                ),
                //            array(
                //                'field' => 'VACCINE_DOZA',
                //                'label' => 'VACCINE_DOZA',
                //                'rules' => '',
                //                'type' => 'id'
                //            ),
                array(
                    'field' => 'WAY_PLACE',
                    'label' => 'WAY_PLACE',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'VaccineType_id',
                    'label' => 'VaccineType_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchTubPlan' => array(
				array(
						'field' => 'getCountOnly',
						'label' => 'Посчитать только каунт',
						'rules' => '',
						'type' => 'id'
					),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlanTuberkulin_id',
                    'label' => 'PlanTuberkulin_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Date_Plan',
                    'label' => 'Date_Plan',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'sex',
                    'label' => 'Пол',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => '',
                    'type' => 'string'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'Address',
                    'label' => 'Адрес',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_Name',
                    'label' => 'Lpu_Name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchTubAssigned' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Date_Purpose',
                    'label' => 'Date_Purpose',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => '',
                    'type' => 'string'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_Name',
                    'label' => 'Lpu_Name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchTubReaction' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'date_Vac',
                    'label' => 'date_Vac',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => '',
                    'type' => 'string'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_Name',
                    'label' => 'Lpu_Name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'searchVacRefuse' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'vacJournalMedTapRefusal_id',
                    'label' => 'vacJournalMedTapRefusal_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'sex',
                    'label' => 'Пол',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DateBegin',
                    'label' => 'DateBegin',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'DateEnd',
                    'label' => 'DateEnd',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Reason',
                    'label' => 'Reason',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'type_rec',
                    'label' => 'type_rec',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_Name',
                    'label' => 'Lpu_Name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                //            ),
                //            array(
                //                'field' => 'VaccineType_id',
                //                'label' => 'VaccineType_id',
                //                'rules' => '',
                //                'type' => 'id'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            'savePriviv' => array(
                array(
                    'field' => 'vaccine_way_place_id',
                    'label' => 'Способ и место введения',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'key_list',
                    'label' => 'key_list',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_doze',
                    'label' => 'Доза введения',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vaccine_id',
                    'label' => 'Вакцина',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'med_staff_fact_id',
                    'label' => 'Врач',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'date_purpose',
                    'label' => 'Дата назначения',
                    'rules' => 'required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'vac_period',
                    'label' => 'Срок годности',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'vac_seria',
                    'label' => 'Серия',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'medService_id',
                    'label' => 'medService_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'row_plan_parent',
                    'label' => 'Parent',
                    'rules' => 'required',
                    'type' => 'int'
                ),
				array(
                    'field' => 'EvnVizitPL_id',
                    'label' => 'посещение',
                    'rules' => '',
                    'type' => 'id'
                ),
            ),
            'saveMantu' => array(
                array(
                    'field' => 'person_id',
                    'label' => 'person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'plan_tub_id',
                    'label' => 'plan_tub_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'lpu_id',
                    'label' => 'lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'date_purpose',
                    'label' => 'Дата назначения',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'vac_presence_id',
                    'label' => 'vac_presence_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vac_period',
                    'label' => 'Срок годности',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'vac_seria',
                    'label' => 'Серия',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_doze',
                    'label' => 'Доза введения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vaccine_way_place_id',
                    'label' => 'Способ и место введения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'status_type_id',
                    'label' => 'status_type_id',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'med_staff_fact_id',
                    'label' => 'Врач',
                    'rules' => '', //required','required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'reaction_type',
                    'label' => 'reaction_type',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'reaction_size',
                    'label' => 'Реакция, [мм]',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 'false',
                    'field' => 'checkbox_reaction30min',
                    'label' => 'реакция на прививку через 30 мин.',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                //            array(
                //                'field' => 'reaction_type',
                //                'label' => 'reaction_type',
                //                'rules' => '',
                //                'type' => 'id'
                //            ),
                //            array(
                //                'field' => 'reaction_desc',
                //                'label' => 'Описание реакции Манту',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                //            array(
                //                'field' => 'local_reaction_desc',
                //                'label' => 'Описание реакции на прививку',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'date_react',
                    'label' => 'date_react',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'medService_id',
                    'label' => 'medService_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                   array(
                    'field' => 'diagnosis_type',
                    'label' => 'Метод диагностики',
                    'rules' => '',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'diaskin_type_reaction',
                    'label' => 'Степень выраженности',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'JournalMantu_ReactDescription',
					'label' => 'Описание реакции',
					'rules' => 'trim',
					'type' => 'string'
				),
            ),
            'saveMantuFixed' => array(
                array(
                    'field' => 'fix_tub_id',
                    'label' => 'fix_tub_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'person_id',
                    'label' => 'person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'lpu_id',
                    'label' => 'lpu_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'date_impl',
                    'label' => 'date_impl',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'med_staff_fact_id',
                    'label' => 'med_staff_fact_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vac_presence_id',
                    'label' => 'vac_presence_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vac_period',
                    'label' => 'Срок годности',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'vac_seria',
                    'label' => 'Серия',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_doze',
                    'label' => 'vac_doze',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vaccine_way_place_id',
                    'label' => 'vaccine_way_place_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'status_type_id',
                    'label' => 'status_type_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'reaction_type',
                    'label' => 'reaction_type',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'reaction_size',
                    'label' => 'Реакция, [мм]',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    //								'default' => 'off',
                    'default' => 'false',
                    'field' => 'checkbox_reaction30min',
                    'label' => 'реакция на прививку через 30 мин.',
                    'rules' => '',
                    //								'type' => 'string'
                    'type' => 'checkbox'
                ),
                //            array(
                //                'field' => 'reaction_desc',
                //                'label' => 'Описание реакции Манту',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                //            array(
                //                'field' => 'local_reaction_desc',
                //                'label' => 'Описание реакции на прививку',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'date_react',
                    'label' => 'date_react',
                    'rules' => '',
                    'type' => 'date'
                ),
                 array(
                    'field' => 'diagnosis_type',
                    'label' => 'Метод диагностики',
                    'rules' => '',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'diaskin_type_reaction',
                    'label' => 'Степень выраженности',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
					'field' => 'JournalMantu_ReactDescription',
					'label' => 'Описание реакции',
					'rules' => 'trim',
					'type' => 'string'
				),            ),
            'saveImplWithoutPurp' => array(
                array(
                    'field' => 'vaccine_way_place_id',
                    'label' => 'Способ и место введения',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'key_list',
                    'label' => 'key_list',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                 array(
                    'field' => 'key_list_plan',
                    'label' => 'key_list_plan',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_seria',
                    'label' => 'Серия',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_period',
                    'label' => 'Срок годности вакцины',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'vac_doze',
                    'label' => 'Доза введения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vaccine_id',
                    'label' => 'Вакцина',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'medservice_id',
                    'label' => 'Идентификатор службы',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'med_staff_impl_id',
                    'label' => 'Врач (исполнил)',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'date_vac',
                    'label' => 'Дата исполнения',
                    'rules' => 'required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'row_plan_parent',
                    'label' => 'Parent',
                    'rules' => '',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'vacJournalAccountOld_id',
                    'label' => 'vacJournalAccountOld_id',
                    'rules' => '',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'vacJournalAccount_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'vacOther',  
                    'label' => 'ПРизнак прочих приввивок',
                    'rules' => '',
                    'type' => 'int' 
                ),
                 array(
                    'field' => 'person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                ),  //  @StatusType_id
                 array(
                    'field' => 'statustype_id',
                    'label' => 'Статус записи',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadSimilarRecords' => array(
                array(
                    'field' => 'person_id',
                    'label' => 'person_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vaccine_id',
                    'label' => 'vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'status_type_id',
                    'label' => 'status_type_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'scheme_num',
                    'label' => 'scheme_num',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'plan_id',
                    'label' => 'plan_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'vacJournalAccount_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vac_type_id',
                    'label' => 'vac_type_id',
                    'rules' => 'trim',
                    'type'  => 'id'
                )
            ),
            'GetVaccineWay' => array(
                array(
                    'field' => 'vaccine_id',
                    'label' => 'vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'birthday',
                    'label' => 'birthday',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'date_purpose',
                    'label' => 'date_purpose',
                    'rules' => 'trim',
                    'type' => 'date'
                )
            ),
            'GetVaccineDoze' => array(
                array(
                    'field' => 'vaccine_id',
                    'label' => 'vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'birthday',
                    'label' => 'birthday',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'date_purpose',
                    'label' => 'date_purpose',
                    'rules' => 'trim',
                    'type' => 'date'
                )
            ),
            'loadVaccineList' => array(
                array(
                    'field' => 'vac_type_id',
                    'label' => 'vac_type_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'birthday',
                    'label' => 'birthday',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'date_purpose',
                    'label' => 'date_purpose',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'StoreKeyList',
                    'label' => 'StoreKeyList прививок',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'savePrivivImplement' => array(
                array(
                    'field' => 'vac_jaccount_id',
                    'label' => 'vac_jaccount_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'vac_seria',
                    'label' => 'Серия вакцины',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_period',
                    'label' => 'Срок годности вакцины',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'med_staff_impl_id',
                    'label' => 'Врач (исполнил)',
                    'rules' => '',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'medservice_id',
                    'label' => 'Служба',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'date_vac',
                    'label' => 'Дата исполнения',
                    'rules' => 'required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'react_local_desc',
                    'label' => 'Реакция местная',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'react_general_desc',
                    'label' => 'Реакция общая',
                    'rules' => 'trim',
                    'type' => 'string'
				 ),
				array(
                    'field' => 'vaccine_way_place_id',
                    'label' => 'Идентификатор способа и места введения',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'savePrivivRefuse' => array(
                array(
                    'field' => 'person_id',
                    'label' => 'Пациент',
                    'rules' => 'trim|is_natural',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vaccine_type_id',
                    'label' => 'Иммунизация',
                    'rules' => 'required|is_natural',
                    'type' => 'id'
                ),
                array(
                    'field' => 'date_refuse_range',
                    'label' => 'Период',
                    'rules' => 'required',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'vac_refuse_cause',
                    'label' => 'Причина',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'refuse_date',
                    'label' => 'Дата',
                    'rules' => 'required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'refusal_type_id',
                    'label' => 'Решение по медотводу/отказу/согласию',
                    'rules' => 'required|is_natural',
                    'type' => 'id'
                ),
                array(
                    'field' => 'user_id',
                    'label' => 'Пользователь',
                    'rules' => 'trim|is_natural',
                    'type' => 'id'
                ),
                array(
                    'field' => 'med_staff_refuse_id',
                    'label' => 'Врач',
                    'rules' => 'required|is_natural',
                    'type' => 'id'
                ),
                array(
                    'field' => 'refuse_id',
                    'label' => 'id медотвода',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'saveSprVaccine' => array(
                array(
                    'field' => 'Vaccine_id',
                    'label' => 'id вакцины',
                    //                'rules' => 'required|trim',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Vaccine_Name',
                    'label' => 'Наименование вакцины',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Vaccine_Nick',
                    'label' => 'Краткое наименование вакцины',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'AgeRange1',
                    'label' => 'Возраст - с какого',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AgeRange2',
                    'label' => 'Возраст - до какого',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'TypeInfections',
                    'label' => 'Типы инфекций против которых действует вакцина',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DozaAge',
                    'label' => 'возраст разграничения доз',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'DozaVal1',
                    'label' => 'Доза - значение 1',
                    'rules' => 'trim',
                    'type' => 'float'
                ),
                array(
                    'field' => 'DozaVal2',
                    'label' => 'Доза - значение 2',
                    'rules' => 'trim',
                    'type' => 'float'
                ),
                array(
                    'field' => 'DozeType1',
                    'label' => 'Доза - тип 1',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'DozeType2',
                    'label' => 'Доза - тип 2',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'WayAge',
                    'label' => 'возраст разграничения способа ввода',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'placeType1',
                    'label' => 'Место ввода - тип 1',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'placeType2',
                    'label' => 'Место ввода - тип 2',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'wayType1',
                    'label' => 'Способ ввода - тип 1',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'wayType2',
                    'label' => 'Способ ввода - тип 2',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            //            ,array(
            //                'field' => 'AgeRange_DozaCheck',
            //                'label' => 'xxx',
            //                'rules' => 'trim',
            //                'type' => 'string'
            //            )
            ), 
            'saveSprOtherVacScheme' => array(
                array(
                    'field' => 'Vaccine_id',
                    'label' => 'id вакцины', 
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'AgeTypeS1',
                    'label' =>  'Тип периода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AgeS1',
                    'label' =>  'Начало периода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AgeTypeS2',
                    'label' =>  'Тип периода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AgeS2',
                    'label' =>  'Начало периода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AgeE1',
                    'label' =>  'Окончание периода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AgeE2',
                    'label' =>  'Окончание периода',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Multiplicity1',
                    'label' =>  'Кратность',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Multiplicity2',
                    'label' =>  'Кратность',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MultiplicityRisk1',
                    'label' =>  'Кратность для группы риска',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MultiplicityRisk2',
                    'label' =>  'Кратность для группы риска',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Interval1',
                    'label' =>  'Интервал',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Interval2',
                    'label' =>  'Интервал',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IntervalRisk1',
                    'label' =>  'Интервал для группы риска',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'IntervalRisk2',
                    'label' =>  'Интервал для группы риска',
                    'rules' => '',
                    'type' => 'int'
                )
             ),   
            'loadVaccine4Other' => array(
                array(
                    'field' => 'person_id',
                    'label' => 'person_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vaccine_id',
                    'label' => 'vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
             ), 
            'deletePrivivRefuse' => array(
                array(
                    'field' => 'refuse_id',
                    'label' => 'id медотвода',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'deletePrivivImplement' => array(
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'id исполненной прививки',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ), 
            'vac_interval_exceeded' => array(
                array(
                    'field' => 'Inoculation_id',
                    'label' => 'id исполненной прививки',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),    
            'deleteMantu' => array(
                array(
                    'field' => 'JournalMantu_id',
                    'label' => 'id прививки манту',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'deleteSprVaccine' => array(
                array(
                    'field' => 'vaccine_id',
                    'label' => 'id вакцины',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'deleteSprNC' => array(
                array(
                    'field' => 'NationalCalendarVac_id',
                    'label' => 'NationalCalendarVac_id',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'getVaccineSeriaList' => array(
                array(
                    'field' => 'vaccine_id',
                    'label' => 'vaccine_id',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'getUchList' => array(
                array(
                    'field' => 'lpu_id',
                    'label' => 'lpu_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuBuilding_id',
                    'label' => 'LpuBuilding_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuUnit_id',
                    'label' => 'LpuUnit_id',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuSection_id',
                    'label' => 'LpuSection_id',
                    'rules' => 'trim',
                    'type' => 'int'
                )  
            ),
            'getPersonVac063' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'getPersonVacOther' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'getPersonVacDigest' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadSprVacFormInfo' => array(
                array(
                    'field' => 'Vaccine_id',
                    'label' => 'Vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadSprOtherVacFormInfo' => array(
                array(
                    'field' => 'Vaccine_id',
                    'label' => 'Vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadSprNCFormInfo' => array(
                array(
                    'field' => 'NationalCalendarVac_id',
                    'label' => 'NationalCalendarVac_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Scheme_id',
                    'label' => 'Scheme_id',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'loadVaccineRiskInfo' => array(
                array(
                    'field' => 'person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'saveVaccineRisk' => array(
                array(
                    'field' => 'person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'required|trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'vaccine_type_id',
                    'label' => 'Идентификатор инфекции',
                    'rules' => 'required|trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'user_id',
                    'label' => 'ID пользователя, который создал запись',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'deleteVaccineRisk' => array(
                array(
                    'field' => 'vaccine_risk_id',
                    'label' => 'Идентификатор записи',
                    'rules' => 'required|trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'user_id',
                    'label' => 'ID пользователя, который создал запись',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'getOrgJob2Lpu' => array(
                array(
                    'field' => 'lpu_id',
                    'label' => 'lpu_id',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'GetPlaceType' => array(
                array(
                    'field' => 'VaccineWay_id',
                    'label' => 'Идентификатор способа введения',
                    'rules' => '',
                    'type' => 'id'
                )
            ), 
            'GetVacAssigned4CabVac' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedService_id',
                    'label' => 'ИД службы',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'vacJournalAccount_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Search_FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Search_SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Search_SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Search_BirthDay',
                    'label' => 'Search_BirthDay',
                   'rules' => '',
		   'type' => 'date'
                ),
                array(
                    'field' => 'Date_Vac',
                    'label' => 'Date_Vac',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                   'field' => 'begDate',
                   'label' => 'begDate',
                   'rules' => 'trim|required',
		   'type' => 'date'
                ),
                array(
                    'field' => 'endDate',
                    'label' => 'endDate',
                   'rules' => 'trim|required',
		   'type' => 'date'
                ),
                //            array(
                //                'field' => 'Date_Purpose',
                //                'label' => 'Date_Purpose',
                //                'rules' => 'trim',
                //                'type' => 'date'
                //            ),
                //            array(
                //                'field' => 'uch',
                //                'label' => 'uch',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                //            array(
                //                'field' => 'BirthDay',
                //                'label' => 'Дата рождения',
                //                'rules' => 'trim',
                //                'type' => 'date'
                //            ),
                //            array(
                //                'field' => 'sex',
                //                'label' => 'Пол',
                //                'rules' => '',
                //                'type' => 'string'
                //            ),
                array(
                    'field' => 'age',
                    'label' => 'Возраст',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'vac_name',
                    'label' => 'vac_name',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'NAME_TYPE_VAC',
                    'label' => 'NAME_TYPE_VAC',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'WAY_PLACE',
                    'label' => 'WAY_PLACE',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'VaccineType_id',
                    'label' => 'VaccineType_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'SearchFormType',
                    'label' => 'SearchFormType',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'uch_id',
                    'label' => 'uch_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'lpu_id',
                    'label' => 'lpu_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_atid',
                    'label' => 'Идентификатор ЛПУ прикрепления',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'AttachMethod_id',
                    'label' => 'id тип привязки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgType_id',
                    'label' => 'OrgType_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'id организации',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Search_BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Filter',
                    'label' => 'Json строка для фильтра',
                    'rules' => '',
                    'type' => 'string'                   
                )
            ),
     
             'GetLpu4Report' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedService_id',
                    'label' => 'ИД службы',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
				array(
					'field' => 'PersonNoAddress',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLStreet_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Address_House', 
					'label' => 'Дом', 
					'rules' => '', 
					'type' => 'string'
				),
				 array(
					'field' => 'PersonAge_AgeFrom',
					'label' => 'Возраст с',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_AgeTo',
					'label' => 'Возраст по',
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'PersonSex_id',
					'label' => 'Пол',
					'rules' => '', 
					'type' => 'int'
				)
            ),
            
            'GeCountingKartVac' => array(
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedService_id',
                    'label' => 'ИД службы',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'calc',
                    'label' => 'Признак расчета',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'ARMType',
                    'label' => 'ARMType',
                    'rules' => '',
                    'type' => 'string'
                )
            ),       
            'getLpuBuildingServiceVac' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
            ),
            'getMedServiceVac' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
                                array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
            'getMedServiceVacExtended' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
                                array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
            'geComboVacMedPersonal' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
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
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPers_id_impl',
					'label' => 'Мед.работник выполнивший прививку',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'form',
					'label' => 'Форма из которой идет запрос',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isMidMedPersonalOnly',
					'label' => 'средний мед. персонал',
					'rules' => '',
					'type' => 'int'
				)
			), 
             'geComboVacMedPersonalFull' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			), 
                'VacDateAdd' => array(
                                array(
					'field' => 'Type',
					'label' => 'Тип периода для добавления',
					'rules' => '',
					'type' => 'int'
                                ),
                                array(
					'field' => 'Add_Num',
					'label' => 'Количество единиц добавления',
					'rules' => '',
					'type' => 'int'
                                ),    
                                array(
					'field' => 'BaseDate',
					'label' => 'Дата, к которой надо добавить период',
					'rules' => '',
					'type' => 'date' 
                                )    
                    ),
            'GetVacPresence' => array(
                array(
					'field' => 'Filter',
					'label' => 'Json строка для фильтра',
					'rules' => '',
					'type' => 'string'                   
				)
                ),
            'getVaccineGridDetail' => array(
                array(
                    'field' => 'Filter',
                    'label' => 'Json строка для фильтра',
                    'rules' => '',
                    'type' => 'string'
                )
            ),  
            'GetOtherVacScheme' => array(
                array(
                    'field' => 'Vaccine_id',
                    'label' => 'Vaccine_id',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
			'deleteVaccinationNotice' => array(
				array(
                    'field' => 'NotifyReaction_id',
                    'label' => 'Идентификатор',
                    'rules' => 'required',
                    'type' => 'id'
                ),
			),
			'saveVaccinationNotice' => array(
				array(
                    'field' => 'NotifyReaction_id',
                    'label' => 'Идентификатор',
                    'rules' => '',
                    'type' => 'id'
                ),
				 array(
                    'field' => 'NotifyReaction_Descr',
                    'label' => 'Описание неблагоприятной реакции',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'Идентификатор',
                    'rules' => 'required',
                    'type' => 'id'
                ),
				array(
                    'field' => 'MedPersonal_id',
                    'label' => 'Идентификатор',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'NotifyReaction_createDate',
					'label' => 'Дата создания извещения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'NotifyReaction_confirmDate',
					'label' => 'Дата подтверждения неблагоприятной реакции',
					'rules' => 'required',
					'type' => 'date'
				)               
			),
			'loadVaccinationNotice' => array(
				array(
                    'field' => 'vacJournalAccount_id',
                    'label' => 'Идентификатор',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
                    'field' => 'NotifyReaction_id',
                    'label' => 'Идентификатор извещения',
                    'rules' => '',
                    'type' => 'id'
                ),
			),
			'loadGridVaccinationNotice' => array(
				array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
				array(
                    'field' => 'Lpu_id',
                    'label' => 'МО',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
                    'field' => 'Person_SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'Person_FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'Person_SecName',
                    'label' => 'Отчесвто',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'vac_name',
                    'label' => 'Вакцина',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'Seria',
                    'label' => 'Серия',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'CreateMotification_datePeriod_beg',
					'label' => 'Период дат создания извещения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'CreateMotification_datePeriod_end',
					'label' => 'Период дат создания извещения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'VaccinationPerformance_datePeriod_beg',
					'label' => 'Период дат исполнения прививки',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'VaccinationPerformance_datePeriod_end',
					'label' => 'Период дат исполнения прививки',
					'rules' => '',
					'type' => 'date'
				),
			),
			'getVaccinePlan' => array(
				array(
					'field' => 'Person_id',
					'label' => 'идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
            ),
            'getMedServiceVac_allData' => array(
                array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
            'getVaccinesDosesVaccine_List'=> array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
                ),
                array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
                ),
                array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор кабинета вакцинации',
					'rules' => 'required',
					'type' => 'id'
                ),
                array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id'
                ),
                // фильтрация по национальному календарю
                array(
					'field' => 'NatCalendar',
					'label' => 'Национальный календарь',
					'rules' => '',
					'type' => 'int'
                ),
                // фильтр по Эпидпоказаниям
                array(
					'field' => 'Vaccination_isEpidemic',
					'label' => 'Эпидпоказания',
					'rules' => '',
					'type' => 'int'
				)
            ),
            'checkVaccinesDosesWindow' => array(
                array(
					'field' => 'Org_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
                ),
                array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'МО',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedPersonal_id',
                    'label' => 'Идентификатор',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
					'field' => 'NatCalendar',
					'label' => 'Национальный календарь',
					'rules' => 'trim',
					'type' => 'int'
                ),
                array(
					'field' => 'Vaccination_isEpidemic',
					'label' => 'Эпидпоказания',
					'rules' => 'trim',
					'type' => 'int'
                ),
                array(
					'field' => 'Prep_ID',
					'label' => 'Вакцина',
					'rules' => 'required',
					'type' => 'id'
                ),
                array(
					'field' => 'DosesQuantity',
					'label' => 'Количество доз',
					'rules' => 'trim',
					'type' => 'int'
                )
            )

        );
    }

    /**
     * Загрузка Перечня журналов вакцинации
     */
    function loadJournals() {
        $response = $this->dbmodel->loadJournals();
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        //$this->ReturnData(array('success'=>true, 'data'=>$response));
        $this->ReturnData(array('data' => $response));
    }

    //  /**
    //   * Загрузка доп инфы для формы исполнения прививки (режим редактирования)
    //   */
    //  function loadImplFormInfoEdit() {
    //    $data = $this->ProcessInputData('loadImplFormInfoEdit', true);
    //    if ($data === false) { return false; }
    //    
    //    $response = $this->dbmodel->loadImplFormInfoEdit($data);
    //    array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
    //    $this->ReturnData(array('data'=>$response));
    //  }

    /**
     * Загрузка доп инфы для формы исполнения прививки
     */
    function loadImplFormInfo() {
        $data = $this->ProcessInputData('loadImplFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadImplFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка доп инфы для формы исполнения прививки
     */
    function loadImplVacNoPurpFormInfo() {
        $data = $this->ProcessInputData('loadImplVacNoPurpFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadImplVacNoPurpFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка доп инфы для формы назначения прививки
     */
    function loadPurpFormInfo() {
        $data = $this->ProcessInputData('loadPurpFormInfo', true);
        log_message('debug', 'vacJournalAccount_id=' . $data['vacJournalAccount_id']);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadPurpFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка доп инфы для формы назначен манту
     */
    function loadMantuFormInfo() {
        $data = $this->ProcessInputData('loadMantuFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadMantuFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка доп инфы для формы исполнен манту
     */
    function loadJournalMantuFormInfo() {
        $data = $this->ProcessInputData('loadJournalMantuFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadJournalMantuFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка доп инфы для формы медотвода/отказа от прививки
     */
    function loadRefuseFormInfo() {
        $data = $this->ProcessInputData('loadRefuseFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadRefuseFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка журнала "План Прививок" (НЕ исп-ся - заменен на searchVacPlan)
     */
    function loadVacPlan() {
        $response = $this->dbmodel->loadVacPlan();
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка журнала "Список карт проф прививок" (НЕ исп-ся - заменен на searchVacMap)
     */
    function loadVacMap() {
        $response = $this->dbmodel->loadVacMap();
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Поиск в журнале "Список карт проф прививок"
     */
    function searchVacMap() {
        $data = $this->ProcessInputData('searchVacMap', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        //$listData = $this->dbmodel->searchVacMap($data);
		
		if (!empty($data['getCountOnly'])) {
			$this->db->query_timeout = 300;
			$listData = $this->dbmodel->searchVacMap($data, true, false);
			$this->ProcessModelSave($listData, true)->ReturnData();
		} else {
			$listData = $this->dbmodel->searchVacMap($data, false, false);
			//$this->ProcessModelMultiList($listData, true, true)->ReturnData();
			if ($isPrintForm) {
				$this->buildPrintForm(__FUNCTION__, $listData);
				return true;
			}

			$this->ProcessModelMultiList($listData, true, true)->ReturnData();
		}
		
    }

	  /**
     * Поиск в журнале "Список карт проф прививок" (старый)
     */
    function searchVacMapOld() {
        $data = $this->ProcessInputData('searchVacMap', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchVacMap($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            //      $start = $data['start'];
            //      $limit = $data['limit'];
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

    /**
     * Поиск в журнале "План прививок" (Старый)
     */
    function searchVacPlanOld() {
        $data = $this->ProcessInputData('searchVacPlan', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchVacPlan($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            //      $start = $data['start'];
            //      $limit = $data['limit'];
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

	
	  /**
     * Поиск в журнале "План прививок"
     */
	
	function searchVacPlan() {
        $data = $this->ProcessInputData('searchVacPlan', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');
		
		if (!empty($data['getCountOnly'])) {
			$this->db->query_timeout = 300;
			$listData = $this->dbmodel->searchVacPlan($data, true, false);
			$this->ProcessModelSave($listData, true)->ReturnData();
		} else {
			$listData = $this->dbmodel->searchVacPlan($data, false, false);

			if ($isPrintForm) {
				$this->buildPrintForm(__FUNCTION__, $listData);
				return true;
			}

			$this->ProcessModelMultiList($listData, true, true)->ReturnData();
		}
		
    }
	
    /**
     * Поиск в журнале "Журнал назначенных прививок"
     */
    function searchVacAssigned() {
        $data = $this->ProcessInputData('searchVacAssigned', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchVacAssigned($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            //      $start = $data['start'];
            //      $limit = $data['limit'];
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
					//          if ($data['SearchFormType'] != 'VacJournal') array_walk($row, 'ConvertFromWin1251ToUTF8');
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

    /**
     * Подготовка результатов поиска к печати
     * 
     * $modelName
     * $response
     */

    function buildPrintForm($modelName, $response) {
        $this->load->library('parser');
        $view = '';
		//		switch ($modelName) {
		//			case 'searchVacAssigned':
		//				$view = 'vac_journal_search_results';
		//				break;
		//
		//			default:
		//				break;
		//		}
        $view = $modelName . '_print';
        log_message('debug', 'isPrintForm=' . $view);
        for ($i = 0; $i < count($response['data']); $i++) {
			//				$response['data'][$i]['Record_Num'] = $i + 1;
            $response['data'][$i]['Record_Num'] = $i + 1;
        }
        $xml = $this->parser->parse($view, array('search_results' => $response['data'])); //$response['data']));
		//		return $xml;
    }

    /**
     * Поиск в журнале "Журнал Учет профилактических прививок"
     */
    function searchVacRegistr() {
        $data = $this->ProcessInputData('searchVacRegistr', false);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');
		
		if (!empty($data['getCountOnly'])) {
			$this->db->query_timeout = 300;
			$listData = $this->dbmodel->searchVacRegistr($data, true, false);
			$this->ProcessModelSave($listData, true)->ReturnData();
		} else {
			$listData = $this->dbmodel->searchVacRegistr($data, false, false);

			if ($isPrintForm) {
				$this->buildPrintForm(__FUNCTION__, $listData);
				return true;
			}

			$this->ProcessModelMultiList($listData, true, true)->ReturnData();
		}
		

    }

    /**
     * Поиск в журнале "Журнал Планирование туберкулинодиагностики"
     */
    function searchTubPlan() {
        $data = $this->ProcessInputData('searchTubPlan', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchTubPlan($data);
		
		if (!empty($data['getCountOnly'])) {
			$this->db->query_timeout = 300;
			$listData = $this->dbmodel->searchTubPlan($data, true, false);
			$this->ProcessModelSave($listData, true)->ReturnData();
		} else {
			$listData = $this->dbmodel->searchTubPlan($data, false, false);

			if ($isPrintForm) {
				$this->buildPrintForm(__FUNCTION__, $listData);
				return true;
			}

			$this->ProcessModelMultiList($listData, true, true)->ReturnData();
		
		}
    }

    /**
     * Поиск в журнале "Журнал Манту-назначено"
     */
    function searchTubAssigned() {
        $data = $this->ProcessInputData('searchTubAssigned', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchTubAssigned($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

    /**
     * Поиск в журнале "Журнал Манту-реакция"
     */
    function searchTubReaction() {
        $data = $this->ProcessInputData('searchTubReaction', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchTubReaction($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

    /**
     * Поиск в журнале "Журнал медотводов"
     */
    function searchVacRefuse() {
        $data = $this->ProcessInputData('searchVacRefuse', true);
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->searchVacRefuse($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
			//      $start = $data['start'];
			//      $limit = $data['limit'];
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

    /**
     * Получаем список "Способы и место введения вакцины"
     */
    public function GetVaccineWay() {
        //$data = array();
        $data = $this->ProcessInputData('GetVaccineWay', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        //$this->load->database();
        //$this->load->model('VaccineCtrl_Model', 'dbmodel');
        $response = $this->dbmodel->GetVaccineWay($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список "Доза введения вакцины"
     */
    public function GetVaccineDoze() {
        $data = $this->ProcessInputData('GetVaccineDoze', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->GetVaccineDoze($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список "Типов дозирования вакцины"
     */
    public function GetDozeType() {
        $data = array();
        $val = array();
        $response = $this->dbmodel->GetDozeType($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список "Типов способов введения вакцины"
     */
    public function GetWayType() {
        $data = array();
        $val = array();
        $response = $this->dbmodel->GetWayType($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список "Типов мест введения вакцины"
     */
    public function GetPlaceType() {
        $data = array();
        $data = $this->ProcessInputData('GetPlaceType', true);
        $val = array();
        $response = $this->dbmodel->GetPlaceType($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список "Способы и место введения вакцины"
     */
    public function GetVacPurpose() {
        $data = array();
        $val = array();
        $response = $this->dbmodel->GetVacPurpose($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Сохранение назначенной прививки
     */
    public function savePriviv() {
        $data = $this->ProcessInputData('savePriviv', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->savePriviv($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		//    Echo '{rows:'.json_encode($val).'}';
        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }

    /**
     * Сохранение манту
     */
    public function saveMantu() {
        $data = $this->ProcessInputData('saveMantu', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->saveMantu($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }

    /**
     * Сохранение манту (исполнение и редактирование исполненных манту)
     */
    public function saveMantuFixed() {
        $data = $this->ProcessInputData('saveMantuFixed', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->saveMantuFixed($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }

    /**
     * Сохранение Исполнения прививки минуя назначение
     */
    public function saveImplWithoutPurp() {
        log_message('debug', 'saveImplWithoutPurp1' );
        $data = $this->ProcessInputData('saveImplWithoutPurp', true);
        log_message('debug', 'saveImplWithoutPurp2' );
        if ($data === false) {
            log_message('debug', 'saveImplWithoutPurp_error' );
            return false;
        }
        log_message('debug', 'saveImplWithoutPurp3' );
        $val = array();
        $response = $this->dbmodel->saveImplWithoutPurp($data);
		//    $response = $this->dbmodel->savePrivivImplement($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }

    /**
     * поиск похожих записей для выбранной прививки (назначение прививки)
     */
    public function loadSimilarRecords() {
        $data = $this->ProcessInputData('loadSimilarRecords', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->loadSimilarRecords($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }
    
        /**
     * поиск  записей для выбранной прививки (для прочих прививок)
     */
    public function loadVaccine4Other() {
         log_message('debug', 'loadVaccine4Other 0' );
        $data = $this->ProcessInputData('loadVaccine4Other', true);
        log_message('debug', 'loadVaccine4Other 2' );
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->loadVaccine4Other($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Список вакцин
     */

    public function loadVaccineList() {
        $data = $this->ProcessInputData('loadVaccineList', true);
         log_message('debug', 'loadVaccineList: birthday0='.$data['birthday']);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->loadVaccineList($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Загрузка доп инфы для формы редактирования вакцины
     */
    function loadSprVacFormInfo() {
        $data = $this->ProcessInputData('loadSprVacFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadSprVacFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }
    
    /**
     * Загрузка доп инфы для формы редактирования справочника Схема вакцинации
     */
    function loadSprOtherVacFormInfo() {
        $data = $this->ProcessInputData('loadSprOtherVacFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadSprOtherVacFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Загрузка доп инфы для формы редактирования нац. календаря
     * Тагир 24.06.2013
     */
    function loadSprNCFormInfo() {
        $data = $this->ProcessInputData('loadSprNCFormInfo', true);
        if ($data === false) {
            return false;
        }
        log_message('debug', 'Scheme_id=' . $data['Scheme_id']);
        $response = $this->dbmodel->loadSprNCFormInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * Удаление записи из нац. календаря
     */
    public function deleteSprNC() {
        $data = $this->ProcessInputData('deleteSprNC', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->deleteSprNC($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }

    /**
     * Загрузка доп инфы для вкладки "Группы риска" формы "Карта проф прививок"
     */

    public function loadVaccineRiskInfo() {
        $data = $this->ProcessInputData('loadVaccineRiskInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadVaccineRiskInfo($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }

    /**
     * сохранение информации о группе риска пациента
     */

    public function saveVaccineRisk() {
        $data = $this->ProcessInputData('saveVaccineRisk', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->saveVaccineRisk($data);

		//    foreach ($response as $row) {
		//      array_walk($row, 'ConvertFromWin1251ToUTF8');
		//      $val[] = $row;
		//    }
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        echo json_encode(array('success' => true, 'rows' => $response));
        return true;
		//    $this->ReturnData(array('data'=>$response));
    }

    /**
     * удаление информации о группе риска пациента
     */

    public function deleteVaccineRisk() {
        $data = $this->ProcessInputData('deleteVaccineRisk', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->deleteVaccineRisk($data);

		//    foreach ($response as $row) {
		//      array_walk($row, 'ConvertFromWin1251ToUTF8');
		//      $val[] = $row;
		//    }
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        echo json_encode(array('success' => true, 'rows' => $response));
        return true;
    }

    /**
     * Сохранение исполненной прививки
     */
    public function savePrivivImplement() {
        $data = $this->ProcessInputData('savePrivivImplement', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->savePrivivImplement($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

		//    Echo '{rows:'.json_encode($val).'}';
        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }

    /**
     * Сохранение медотвода/отказа
     */
    public function savePrivivRefuse() {
        $data = $this->ProcessInputData('savePrivivRefuse', true);
        if ($data === false) {
            return false;
        }

        log_message('debug', 'user_id(cntr)=' . $_SESSION['pmuser_id']);

        if ($_SESSION['pmuser_id'] != $data['user_id']) {
            $errorMsg = 'Ошибка при выполнении запроса к базе данных (Сохранение медотвода)';
            //return array(array('Error_Msg' => $errorMsg, 'success' => false));
            //echo json_encode(Array('success' => false, 'Error_Msg' => $errorMsg));
            //echo json_encode(array_walk(array('success' => false, 'Error_Msg' => $errorMsg), 'ConvertFromWin1251ToUTF8'));
            //ConvertFromWin1251ToUTF8($errorMsg);
            echo json_encode(Array('success' => false, 'Error_Msg' => $errorMsg));
            return false;
        }

		//    if ($data['vaccine_type_id'] == 100) {
		//      $data['vaccine_type_id'] = null;
		//      $data['vaccine_type_all'] = 1;
		//    } else {
		//      $data['vaccine_type_all'] = 0;
		//    }

        $val = array();
        try {
            $response = $this->dbmodel->savePrivivRefuse($data);
        } catch (Exception $e) {
            $pattern = "/ERROR\:(.*)\n/";
            if(preg_match($pattern, $e->getMessage(), $m))
            echo json_encode(['Error_Msg' => $m[1], 'success' => false]);
            return false;
        }
		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        //Echo '{rows:'.json_encode($val).'}';
        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }

    /**
     * Сохранение вакцины (справочник вакцин)
     */
    public function saveSprVaccine() {
        $data = $this->ProcessInputData('saveSprVaccine', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->saveSprVaccine($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }
    
     /**
     * Сохранение схем для дополнительных прививок 
     */
    public function saveSprOtherVacScheme() {
        $data = $this->ProcessInputData('saveSprOtherVacScheme', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->saveSprOtherVacScheme($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }
    
    /**
     * Удаление медотвода/отказа
     */
    public function deletePrivivRefuse() {
        $data = $this->ProcessInputData('deletePrivivRefuse', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->deletePrivivRefuse($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }

    /**
     * Удаление вакцины из справочника вакцин
     */
    public function deleteSprVaccine() {
        $data = $this->ProcessInputData('deleteSprVaccine', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->deleteSprVaccine($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }

    /**
     * Удаление исполненной прививки
     */
    public function deletePrivivImplement() {
        $data = $this->ProcessInputData('deletePrivivImplement', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->deletePrivivImplement($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }

    /**
     * Превышен интерал вакцинации
     */
    public function vac_interval_exceeded() {
        $data = $this->ProcessInputData('vac_interval_exceeded', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->vac_interval_exceeded($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }
    
    /**
     * Удаление исполненной прививки манту
     */
    public function deleteMantu() {
        $data = $this->ProcessInputData('deleteMantu', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->deleteMantu($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }

    /**
     * Получаем список статусов вакцин (НЕ ЮЗАЕМ!!!)
     */
    public function getVaccineStatusList() {
        $data = $this->ProcessInputData('getVaccineStatusList', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->getVaccineStatusList($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список типов отказов
     */
    public function getVaccineRefusalTypeList() {
		//    $data = $this->ProcessInputData('getVaccineRefusalTypeList', true);
		//    if ($data === false) { return false; }

        $val = array();
		//    $response = $this->dbmodel->getVaccineRefusalTypeList($data);
        $response = $this->dbmodel->getVaccineRefusalTypeList();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }

    /**
     * Получаем список типов реакции манту
     */
    public function getTypeReactionList() {
        $val = array();
        $response = $this->dbmodel->getTypeReactionList();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }
    
      /**
     * Получаем список типов реакции диаскинтеста
     */
    public function getDiaskinTypeReactionList() {
        $val = array();
        $response = $this->dbmodel->getDiaskinTypeReactionList();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }
    
     /**
    * Получаем список ЛПУ, в которых есть служба "Кабинет вакцинации"
    */
    public function getLpuListServiceVac() {
        $val = array();
        $response = $this->dbmodel->getLpuListServiceVac();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }

     /**
     * Получаем Cписок служб "Кабинет вакцинации"
     */
    public function getMedServiceVac() {
        $val = array();
        $data = $this->ProcessInputData('getMedServiceVac', true, true);
        
        if ($data) {
			$response = $this->dbmodel->getMedServiceVac($data);
			if ( is_array($response) ) {
				foreach ($response as $row) {
					//array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
			}

            Echo '{rows:' . json_encode($val) . '}';
            return true;
        }
        else {
			return false;
		}

    }
    
         /**
     * Получаем Расширенный Cписок служб "Кабинет вакцинации" 
     */
    public function getMedServiceVacExtended() {
        $val = array();
        $data = $this->ProcessInputData('getMedServiceVacExtended', true, true);
        
        if ($data) {
			$response = $this->dbmodel->getMedServiceVacExtended($data);
			if ( is_array($response) ) {
				foreach ($response as $row) {
					//array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
			}

            Echo '{rows:' . json_encode($val) . '}';
            return true;
        }
		else {
			return false;
		}
             
    }
    
      /**
     * Получаем Список сотрудников службы "Кабинет вакцинации" 
     */
    public function geComboVacMedPersonal() {
        $val = array();
        $data = $this->ProcessInputData('geComboVacMedPersonal', true, true);
          log_message('debug', 'geComboVacMedPersonal: Lpu_id=' . $data['Lpu_id']);
        if ($data) {
			$response = $this->dbmodel->geComboVacMedPersonal($data);
			if ( is_array($response) ) {
				foreach ($response as $row) {
					//array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
			}

			Echo '{rows:' . json_encode($val) . '}';
			return true;
        }
		else {
			return false;
		}
             
    }
    
      /**
     * Получаем Список медперсонала 
     */
    public function geComboVacMedPersonalFull() {
        $val = array();
        $data = $this->ProcessInputData('geComboVacMedPersonalFull', true, true);
         log_message('debug', 'geComboVacMedPersonalFull: Lpu_id=' . $data['Lpu_id']);
        if ($data) {
            $response = $this->dbmodel->geComboVacMedPersonalFull($data);
			if ( is_array($response) ) {
				foreach ($response as $row) {
					//array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
			}

			Echo '{rows:' . json_encode($val) . '}';
			return true;
        }
		else {
			return false;
		}
             
    }
    
    	/**
	 *  Получение справочника подразделений,  в которых есть служба "Кабинет вакцинации"`
	 *  Входящие данные: $_POST['date']
	 *  На выходе: JSON-строка
	 */
	function getLpuBuildingServiceVac() {
		$data = $this->ProcessInputData('getLpuBuildingServiceVac', true, true);
		if ($data) {
			$response = $this->dbmodel->getLpuBuildingServiceVac($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
        
    /**
     * Получаем список доступных серий вакцин
     */
    public function getVaccineSeriaList() {
        $data = $this->ProcessInputData('getVaccineSeriaList', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->getVaccineSeriaList($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				$val[] = $row;
			}
		}

        echo '{rows:' . json_encode($val) . '}';
        return true;
    }

    /**
     * Получаем список участков
     */
    public function getUchList() {
        $data = $this->ProcessInputData('getUchList', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->getUchList($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }

    /**
     * Просмотр "Карта 063 (Обзор прививок новый)"
     */

    function getPersonVac063() {
        $data = $this->ProcessInputData('getPersonVac063', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->getPersonVac063($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo json_encode($val);
        return true;
    }
    
     /**
     * Просмотр прочих прививок, включая грипп
     */

    function getPersonVacOther() {
        $data = $this->ProcessInputData('getPersonVacOther', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->getPersonVacOther($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo json_encode($val);
        return true;
    }

    /**
     * Просмотр "Обзор прививок"
     */

    function getPersonVacDigest() {
        $data = $this->ProcessInputData('getPersonVacDigest', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->getPersonVacDigest($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        //Echo '{rows:'.json_encode($val).'}';
        Echo json_encode($val);
        return true;
    }

    /**
     * Получаем список типов инфекций
     */
    public function getVaccineTypeInfection() {
        $val = array();
        $response = $this->dbmodel->getVaccineTypeInfection();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }

    /**
     * получаем начальные установки из таблицы БД settings
     */
    public function getVacSettings() {
        $data = array();
        $val = array();
        $response = $this->dbmodel->getVacSettings($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

    /**
     * Получаем список организаций, обслуживаемых ЛПУ
     * Нигматуллин Тагир  08.05.2013
     */
    public function getOrgJob2Lpu() {
        //$data = array();
        $data = $this->ProcessInputData('getOrgJob2Lpu', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        //$this->load->database();
        //$this->load->model('VaccineCtrl_Model', 'dbmodel');
        $response = $this->dbmodel->getOrgJob2Lpu($data);

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';

        return true;
    }

      /**
     *  "Журнал назначенных прививок" для АРМ Кабинет вакцинации
     */

	public function GetVacAssigned4CabVac() {
                
        $data = $this->ProcessInputData('GetVacAssigned4CabVac', true);
        
        log_message('debug', 'Search_Birthday0=' . $data['Search_BirthDay']);
        
        if ($data === false) {
            return false;
        }
        $isPrintForm = ($data['SearchFormType'] == 'PrintForm');

        $listData = $this->dbmodel->GetVacAssigned4CabVac($data);
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            //      $start = $data['start'];
            //      $limit = $data['limit'];
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                } else {
					//          if ($data['SearchFormType'] != 'VacJournal') array_walk($row, 'ConvertFromWin1251ToUTF8');
                    /*if (!$isPrintForm)
                        array_walk($row, 'ConvertFromWin1251ToUTF8');*/
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        if ($isPrintForm) {
            $this->buildPrintForm(__FUNCTION__, $val);
            return true;
        }

        $this->ReturnData($val);
    }

        /**
         * Список подотчетных Уфе мед. организаций
         */
    public function GetLpu4Report() {
        
        $data = $this->ProcessInputData('GetLpu4Report', true);
        if ($data === false) {
            return false;
        }
         $listData = $this->dbmodel->GetLpu4Report($data);
          log_message('debug', 'GetLpu4Report_1');
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                }
                else {
                    //array_walk($row, 'ConvertFromWin1251ToUTF8');
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }

        $this->ReturnData($val);
    }
    
    
    
     /**
     * Список подотчетных Уфе мед. организаций c указанием количества введенных карт
     */
    public function GeCountingKartVac() {
        
        $data = $this->ProcessInputData('GeCountingKartVac', true);
        if ($data === false) {
            return false;
        }
         $listData = $this->dbmodel->GeCountingKartVac($data);
        if (is_array($listData) && count($listData) > 0) {
            $kol = 0;
            $kol0 = 0;
            $val = null;
            $val = array();
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                }
                else {
                    //array_walk($row, 'ConvertFromWin1251ToUTF8');
                    $val['data'][] = $row;
                    if (isset($row['kol'])){
                        $kol = $kol + $row['kol'];
                    }
                    if (isset($row['kol0'])){
                         $kol0 = $kol0 + $row['kol0'];
                     }                
                    $count++;
                }
            }
            $val['totalCount'] = $count;
            $val['kol'] = $kol;
            $val['kol0'] = $kol0;
            
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }


        $this->ReturnData($val);
    }
    
         /**
     * Запускает функцию Date_Add 
     * Нигматуллин Тагир  22.05.1014
     */
    public function VacDateAdd() {
        $data = $this->ProcessInputData('VacDateAdd', true);
        if ($data === false) {
            return false;
        }     
        $response = $this->dbmodel->VacDateAdd($data);
        //array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
        
    }
    
       /**
        * Формирование списка наличия вакцин
        */
    public function GetVacPresence() {
        $data = $this->ProcessInputData('GetVacPresence', true);     
        $response = $this->dbmodel->GetVacPresence($data);
        if(is_array($response)) {
                    $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
                        return false;
                }   
            
    }
	
		/**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */
	
    function getVaccineGridDetail() {
        log_message('debug', 'getVaccineGridDetail_Control');
        $data = $this->ProcessInputData('getVaccineGridDetail', true, true); 
        $response = $this->dbmodel->getVaccineGridDetail($data);
        //echo 'test';
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }                        
    }
    
     /**
     * Получаем список прививок (прочих)
     */
    public function GetListVaccineTypeOther() {
       

        $val = array();
        $response = $this->dbmodel->GetListVaccineTypeOther();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }  //  End GetListVaccineTypeOther
    
       /**
     * Получаем методы диакностики туберкулеза (манту, диаскинтест)
     */
    public function GetTubDiagnosisTypeCombo() {
       
        log_message('debug', 'GetTubDiagnosisTypeCombo');
        $val = array();
        $response = $this->dbmodel->GetTubDiagnosisTypeCombo();

		if ( is_array($response) ) {
			foreach ($response as $row) {
				//array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }  //  End GetTubDiagnosisTypeCombo
    
    
    
        /**
     * Получаем список схем (прочих)
     */
    public function GetOtherVacScheme() {

         $val = array();
        $data = $this->ProcessInputData('GetOtherVacScheme', true, true); 
        log_message('debug', 'GetOtherVacScheme0 :Vaccine_id ='.$data['Vaccine_id']);
       
        $response = $this->dbmodel->GetOtherVacScheme($data);
        //array_walk($response, 'ConvertFromUTF8ToWin1251');

		if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
             log_message('debug', 'GetOtherVacScheme0');
        } else {
                return false;
        }
    }  
    
     /**
     * Получаем список прививок для вывода на экран
     */
    public function GetVaccineTypeGrid() {
        log_message('debug', 'GetVaccineTypeGrid0');
        $val = array();
        $response = $this->dbmodel->GetVaccineTypeGrid();
         log_message('debug', 'GetVaccineTypeGrid10');
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
             log_message('debug', 'GetOtherVacScheme0');
        } else {
                return false;
        }
    }
	
	 /**
     * Сохранение извещения проф прививок
     */
    function saveVaccinationNotice() {
		$data = $this->ProcessInputData('saveVaccinationNotice', true, false);
		if ($data === false) {
            return false;
        }
		
        $response = $this->dbmodel->saveVaccinationNotice($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении извещения)')->ReturnData();
    }
	
	/**
	 * VaccinationNotice
	 */
	function deleteVaccinationNotice(){
		$data = $this->ProcessInputData('deleteVaccinationNotice', true, true);
		if ($data === false) {
            return false;
        }
        $response = $this->dbmodel->deleteVaccinationNotice($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
     * Получить извещения проф прививок
     */
    function loadVaccinationNotice() {
		$data = $this->ProcessInputData('loadVaccinationNotice', true, true);
		if ($data === false) {
            return false;
        }
        $response = $this->dbmodel->loadVaccinationNotice($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
    }
	
	/**
     * Получить извещения проф прививок
     */
    function loadGridVaccinationNotice() {
		$data = $this->ProcessInputData('loadGridVaccinationNotice', true, true);
		if ($data === false) {
            return false;
        }
        $response = $this->dbmodel->loadGridVaccinationNotice($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
    }
	
	/** 
	 * Получение медикамента в форме исполнения прививки
	 */
	function loadContainerMedicinesViewGrid() {
		$data = $this->ProcessInputData('loadContainerMedicinesViewGrid', false);
		if ($data === false) return false;

		$response = $this->dbmodel->loadContainerMedicinesViewGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/** 
	 * Сохранение идентификатор строки документа учета медикаментов в vacJournalAccount
	 */
	function saveDocumentUcStrIDforJournalAccount() {
		$data = $this->ProcessInputData('saveDocumentUcStrIDforJournalAccount', false);
		if ($data === false) return false;

		$response = $this->dbmodel->saveDocumentUcStrIDforJournalAccount($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * получение запланированной вакцины пациента
	 */
	function getVaccinePlan(){
		$data = $this->ProcessInputData('getVaccinePlan', false);
		if ($data === false) return false;

		$response = $this->dbmodel->getVaccinePlan($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
    }

     /**
     * Получаем Cписок служб "Кабинет вакцинации" со всеми данными кабинета
     */
    public function getMedServiceVac_allData() {
        $val = array();
        $data = $this->ProcessInputData('getMedServiceVac_allData', true, true);
        
        if ($data) {
			$response = $this->dbmodel->getMedServiceVac_allData($data);
			if ( is_array($response) ) {
				foreach ($response as $row) {
					//array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
			}

            Echo '{rows:' . json_encode($val) . '}';
            return true;
        }
        else {
			return false;
		}

    }
    

    /**
     * Получение списка вакцин для комбобокса Вакцина в форме Вакцины и дозы
     */
    function getVaccinesDosesVaccine_List() {
        $data = $this->ProcessInputData('getVaccinesDosesVaccine_List', false);
        if ($data === false) return false;
        
        $response = $this->dbmodel->getVaccinesDosesVaccine_List($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
    }



    /**
	 * Проверка возможности проведения вакцины в день обращения
	 */
    function checkVaccination_AvailableToday(){
        $data = $this->ProcessInputData('checkVaccinesDosesWindow', false);
        if ($data === false) return false;
        $response = $this->dbmodel->checkVaccination_AvailableToday($data);

        $vacinationEnable = array(); //доступно к вакцинации
        $personVaccinationRefuse = array(); // у пациента есть противопоказания
        $vaccine_max_age = array(); // максимальный возраст для вакцинации
        $vaccine_min_prev_period = array(); // минимальный период с предыдущей вакцинации по схеме
        $vaccine_min_age = array(); // минимальный возраст для вакцинации
        $personVaccinationRiskGroup = array(); // отношение вакцинации к группе риска пациента
        $Vaccination_event_date = array(); // Дата последней вакцинацции по этой прививке Vaccination_event_date
        $disabled_vaccines_all = array(); // все недоступные вакцины
        $vacinationEnable_inGroup = array(); // отсортированные вакцинации по группам (в каждой группе по 1)
        
		if ( is_array($response) ) {
			foreach ($response as $row) {
                // противоопоказания к вакцинации (проверка по наличию или дате окончания)
                if($row['PersonVaccinationRefuse_endDT'] !== null && strtotime( $row['PersonVaccinationRefuse_endDT'] ) >= strtotime( date('d.m.Y') ) ) {
                    $personVaccinationRefuse[] = $row; 
                    $disabled_vaccines_all[] = $row;
                }
                // максимальный возраст для вакцины
                else if( $row['Vaccine_MaxAge'] !== null && strtotime( $row['PersonBirthDay_BirthDay'] ) > strtotime( $row['Vaccine_MaxAge'] ) ) {
                    $vaccine_max_age[] = $row;
                    $disabled_vaccines_all[] = $row;
                }
                // минимальный период с предыдущей вакцинации по схеме
                else if( $row['Vaccination_pid_event_date'] !== null && strtotime( $row['Vaccination_pid_event_date'] ) > strtotime( $row['Vaccine_MinAge'] )) {
                    $vaccine_min_prev_period[] = $row;
                    $disabled_vaccines_all[] = $row;
                }
                // минимальный возраст для вакцинации
                else if ( $row['Vaccination_pid'] === null && $row['Vaccine_MinAge'] !== null && strtotime( $row['PersonBirthDay_BirthDay'] ) > strtotime( $row['Vaccine_MinAge'] )) {
                    $vaccine_min_age[] = $row;
                    $disabled_vaccines_all[] = $row;
                }
                // отношение вакцинации к группе риска пациента
                else if( $row['PersonVaccinationRiskGroup_insDT'] !== null ) {
                    $personVaccinationRiskGroup[] = $row;
                    $disabled_vaccines_all[] = $row;
                }
                else{
                    $vacinationEnable[] = $row;
                }
			}
        }
        
        // сортировка очередности вакцинаций по группам
        function cmp($a, $b) 
        {
            // очередность вакцинаций
            $rankings = array(
                'V' => 10,
                'V1' => 9,
                'V2' => 8,
                'V3' => 7,
                'V4' => 6,
                'R' => 5,
                'R1' => 4,
                'R2' => 3,
                'R3' => 2,
                'R4' => 1,
            );
            $a_Vaccination_Code = $a["Vaccination_Code"];
            $b_Vaccination_Code = $b["Vaccination_Code"];
            // если очередность соответствует установленным
            if (array_key_exists($a_Vaccination_Code, $rankings) && array_key_exists($b_Vaccination_Code, $rankings)) {
                if($rankings[$a_Vaccination_Code] < $rankings[$b_Vaccination_Code]) return 1;
                if($rankings[$a_Vaccination_Code] > $rankings[$b_Vaccination_Code]) return -1;
            }
            // если очередность не соответствует установленным
            else {
                if($a_Vaccination_Code > $b_Vaccination_Code) return 1;
                if($a_Vaccination_Code < $b_Vaccination_Code) return -1;
            }
            return 0;
        }


        $vaccination_types_unique = array_flip(array_unique(array_column($vacinationEnable, 'VaccinationType_id'))); // типы вакцинаций
        
        // деление списка доступных прививок на группы (схемы)
            foreach ($vaccination_types_unique as $key => $value) {
                $vaccination_types_unique[$key] = array();
            }
            foreach ($vacinationEnable as $key => $value) {
                $vaccination_types_unique[$value['VaccinationType_id']][] = $value;
            }

        // сортировка по очередности в каждой группе (по Vaccination_Code)
        foreach ($vaccination_types_unique as $key => $value) {
            usort($vaccination_types_unique[$key], "cmp");
        }

        // из каждой группы только подной вакцинации
        foreach ($vaccination_types_unique as $key => $value) {
            $vacinationEnable_inGroup[$key] = $vaccination_types_unique[$key][0];
        }


        Echo '{vacinationEnable:' . json_encode($vacinationEnable) 
            . ', personVaccinationRefuse:' . json_encode($personVaccinationRefuse) 
            . ', vaccine_max_age:' . json_encode($vaccine_max_age) 
            . ', vaccine_min_prev_period:' . json_encode($vaccine_min_prev_period) 
            . ', vaccine_min_age:' . json_encode($vaccine_min_age) 
            . ', personVaccinationRiskGroup:' . json_encode($personVaccinationRiskGroup) 
            . ', vacinationEnable_at_types:' . json_encode($vaccination_types_unique)
            . ', disabled_vaccines_all:' . json_encode($disabled_vaccines_all)
            . ', vacinationEnable_inGroup:' . json_encode($vacinationEnable_inGroup)
            .'}';
        return true;
    }


}
