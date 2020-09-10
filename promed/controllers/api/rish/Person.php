<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Person - контроллер API для работы с МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			10.10.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Person extends SwREST_Controller {
	protected  $inputRules = array(
		'getPersonSignalInfo' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
		),
		'mPersonData' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
		),
		'getPerson' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonSurName_SurName', 'label' => 'Фамилия', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonFirName_FirName', 'label' => 'Имя', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonSecName_SecName', 'label' => 'Отчество', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonBirthDay_BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'PersonSnils_Snils', 'label' => 'СНИЛС', 'rules' => 'max_length[11]', 'type' => 'snils'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'max_length[16]', 'type' => 'string'),
			array('field' => 'Person_pid', 'label' => 'Ид человека – представителя', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'DeputyKind_id', 'label' => 'Ид типа представителя', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'Person_isUnknown', 'label' => 'Признак Личность неизвестна', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'Person_iin', 'label' => 'ИИН', 'rules' => '', 'type' => 'string')
		),
		'createPerson' => array(
			array('field' => 'PersonSurName_SurName', 'label' => 'Фамилия', 'rules' => 'required|max_length[50]', 'type' => 'string'),
			array('field' => 'PersonFirName_FirName', 'label' => 'Имя', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonSecName_SecName', 'label' => 'Отчество', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonBirthDay_BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'Person_Sex_id', 'label' => 'Идентификатор пола', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonPhone_Phone', 'label' => 'Телефонный номер', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'PersonSnils_Snils', 'label' => 'СНИЛС', 'rules' => 'max_length[11]', 'type' => 'snils'),
			array('field' => 'SocStatus_id', 'label' => 'Идентификатор соц.статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'UAddress_id', 'label' => 'Идентификатор адреса регистрации', 'rules' => '', 'type' => 'id'),
			array('field' => 'PAddress_id', 'label' => 'Идентификатор адреса проживания', 'rules' => '', 'type' => 'id'),
			array('field' => 'BAddress_id', 'label' => 'Идентификатор адреса рождения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор оргаинизации', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonInn_Inn', 'label' => 'ИНН', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_pid', 'label' => 'Ид человека – представителя', 'rules' => '', 'type' => 'int'),
			array('field' => 'DeputyKind_id', 'label' => 'Ид типа представителя', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_isUnknown', 'label' => 'Признак Личность неизвестна', 'rules' => '', 'type' => 'int'),
			array('field' => 'fromMobile', 'label' => 'Признак мобильного приложения', 'rules' => '', 'type' => 'boolean'),

			// полис
			array('field' => 'OMSSprTerr_id', 'label' => 'Территория страхования', 'rules' => '', 'type' => 'int'),
			array('field' => 'OrgSMO_id', 'label' => 'Полис выдан', 'rules' => '', 'type' => 'int'),
			array('field' => 'PolisType_id', 'label' => 'Тип полиса', 'rules' => '', 'type' => 'int'),
			array('field' => 'PolisFormType_id', 'label' => 'Форма полиса', 'rules' => '', 'type' => 'int'),
			array('field' => 'Polis_EdNum', 'label' => 'Ед. номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_begDate', 'label' => 'Дата выдачи полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'Polis_endDate', 'label' => 'Дата закрытия полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'max_length[16]', 'type' => 'string')
		),
		'updatePerson' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonSurName_SurName', 'label' => 'Фамилия', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonFirName_FirName', 'label' => 'Имя', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonSecName_SecName', 'label' => 'Отчество', 'rules' => 'max_length[50]', 'type' => 'string'),
			array('field' => 'PersonBirthDay_BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'Person_Sex_id', 'label' => 'Идентификатор пола', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonPhone_Phone', 'label' => 'Телефонный номер', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'PersonSnils_Snils', 'label' => 'СНИЛС', 'rules' => 'max_length[11]', 'type' => 'snils'),
			array('field' => 'SocStatus_id', 'label' => 'Идентификатор соц.статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'UAddress_id', 'label' => 'Идентификатор адреса регистрации', 'rules' => '', 'type' => 'id'),
			array('field' => 'PAddress_id', 'label' => 'Идентификатор адреса проживания', 'rules' => '', 'type' => 'id'),
			array('field' => 'BAddress_id', 'label' => 'Идентификатор адреса рождения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор оргаинизации', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonInn_Inn', 'label' => 'ИНН', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_pid', 'label' => 'Ид человека – представителя', 'rules' => '', 'type' => 'int'),
			array('field' => 'DeputyKind_id', 'label' => 'Ид типа представителя', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_isUnknown', 'label' => 'Признак Личность неизвестна', 'rules' => '', 'type' => 'int'),
			array('field' => 'fromMobile', 'label' => 'Признак мобильного приложения', 'rules' => '', 'type' => 'boolean'),
			// полис
			array('field' => 'OMSSprTerr_id', 'label' => 'Территория страхования', 'rules' => '', 'type' => 'int'),
			array('field' => 'OrgSMO_id', 'label' => 'Полис выдан', 'rules' => '', 'type' => 'int'),
			array('field' => 'PolisType_id', 'label' => 'Тип полиса', 'rules' => '', 'type' => 'int'),
			array('field' => 'PolisFormType_id', 'label' => 'Форма полиса', 'rules' => '', 'type' => 'int'),
			array('field' => 'Polis_EdNum', 'label' => 'Ед. номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_begDate', 'label' => 'Дата выдачи полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'Polis_endDate', 'label' => 'Дата закрытия полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'max_length[16]', 'type' => 'string'),
		),
		'loadPersonList' => array(
			array('field' => 'PersonSurName_SurName', 'label' => 'Фамилия', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'PersonFirName_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonSecName_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonBirthDay_BirthDay', 'label' => 'Дата рождения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'PersonSnils_Snils', 'label' => 'СНИЛС', 'rules' => '', 'type' => 'snils'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'offset', 'label' => 'Смещение', 'rules' => '', 'type' => 'int', 'default' => 0),
			
			array('field' => 'Person_pid', 'label' => 'Ид человека – представителя', 'rules' => '', 'type' => 'int'),
			array('field' => 'DeputyKind_id', 'label' => 'Ид типа представителя', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_isUnknown', 'label' => 'Признак Личность неизвестна', 'rules' => '', 'type' => 'int'),
		),
		'getPolis' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'Polis_id', 'label' => 'Идентификатор полиса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => '', 'type' => 'string'),
		),
		'createPolis' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OmsSprTerr_id', 'label' => 'Идентификатор территории', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PolisType_id', 'label' => 'Идентификатор вида полиса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'required|max_length[16]', 'type' => 'string'),
			array('field' => 'OrgSmo_id', 'label' => 'Идентификатор СМО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Polis_begDate', 'label' => 'Дата выдачи полиса', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Polis_endDate', 'label' => 'Дата закрытия полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'PolisFormType_id', 'label' => 'Идентификатор формы полиса', 'rules' => '', 'type' => 'id'),
		),
		'updatePolis' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'Polis_id', 'label' => 'Идентификатор полиса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OmsSprTerr_id', 'label' => 'Идентификатор территории', 'rules' => '', 'type' => 'id'),
			array('field' => 'PolisType_id', 'label' => 'Идентификатор вида полиса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'max_length[10]', 'type' => 'string'),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'max_length[16]', 'type' => 'string'),
			array('field' => 'OrgSmo_id', 'label' => 'Идентификатор СМО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Polis_begDate', 'label' => 'Дата выдачи полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'Polis_endDate', 'label' => 'Дата закрытия полиса', 'rules' => '', 'type' => 'date'),
			array('field' => 'PolisFormType_id', 'label' => 'Идентификатор формы полиса', 'rules' => '', 'type' => 'id'),
		),
		'getDocument' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
		),
		'createDocument' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocumentType_id', 'label' => 'Идентификатор вида документа', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Document_Ser', 'label' => 'Серия документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'Document_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'OrgDep_id', 'label' => 'Идентификатор организации выдачи документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'Document_begDate', 'label' => 'Дата выдачи документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор гражданства', 'rules' => '', 'type' => 'id'),
		),
		'updateDocument' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'Document_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocumentType_id', 'label' => 'Идентификатор вида документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'Document_Ser', 'label' => 'Серия документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'Document_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgDep_id', 'label' => 'Идентификатор организации выдачи документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'Document_begDate', 'label' => 'Дата выдачи документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор гражданства', 'rules' => '', 'type' => 'id'),
		),
		'getPersonID' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
		),
		'getPersonJobInfo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mgetPersonSearch' => array(
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
				'label' => 'Персональный идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'showAll',
				'label' => 'Показывать всех',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'PersonSurName_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PersonFirName_FirName',
				'label' => 'Имя',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PersonSecName_SecName',
				'label' => 'Отчество',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PersonBirthDay_BirthDay',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => '',
				'field' => 'Person_Snils',
				'label' => 'СНИЛС',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'default' => '',
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PolisFormType_id',
				'label' => 'Форма полиса',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Polis_EdNum',
				'label' => 'Единый номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 'all',
				'field' => 'searchMode',
				'label' => 'Режим поиска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonAge_AgeFrom',
				'label' => 'возраст с',
				'rules' => 'trim',
				'type' => 'int'
				),
			array(
				'field' => 'PersonAge_AgeTo',
				'label' => 'возраст по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthYearFrom',
				'label' => 'Год рождения с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthYearTo',
				'label' => 'Год рождения по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonCard_id',
				'label' => 'Идентификатор карты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonCard_Code',
				'label' => 'Код карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'EvnPS_NumCard',
				'label' => 'Номер КВС',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'EvnUdost_Ser',
				'label' => 'Серия полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'EvnUdost_Num',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		'mGetPersonOfflineData' => array(
			array('field' => 'data', 'label' => 'JSON данные с идентификаторами персон', 'rules' => 'required', 'type' => 'string'),
		),
		'mGetAttachedPersonList' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ ', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuAttachType_id','default' => 1, 'label' => 'Тип прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id','label' => 'Тип прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_fapid', 'label' => 'Тип прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => '', 'type' => 'id'),
		),
		'mLoadPersonPrivilegeList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mLoadPersonDirFailPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'showAll',
				'label' => 'флаг показать всех',
				'rules' => '',
				'type' => 'api_flag'
			)
		),
		'mLoadPersonEvnPLDispPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mLoadPersonSurgicalPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mLoadPersonRecords' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'mSendPersonCallNotify' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ARMType_id',
				'label' => 'Идентификатор места работы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonRecordsAllForPortal'=> array(
			array('field' => 'Person_list', 'label' => 'Спиок идентификатов людей', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'pastRecords', 'label' => 'Флаг Показать только прошедшие записи', 'rules' => '', 'type' => 'string'),
			array('field' => 'futureRecords', 'label' => 'Флаг Показать только будущие записи', 'rules' => '', 'type' => 'string'),
			array('field' => 'showTodayRecords', 'label' => 'Флаг Показать только записи на сегодня', 'rules' => '', 'type' => 'string'),
			array('field' => 'pmuser_id', 'label' => 'Идентифкатор аккаунта', 'rules' => '', 'type' => 'id')
		),
        'getEmail' =>array(
            array('field' => 'pmUsersList', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'string')
        )
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Person_model', 'dbmodel');
		$this->dbmodel->fromApi = true;
	}

	/**
	 * Получение данных о человеке
	 */
	function index_get() {
		$data = $this->ProcessInputData('getPerson');

		$resp = $this->dbmodel->loadPersonListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(isset($resp['error_msg'])){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['error_msg']
			));
		}
		/*
		if (count($resp['data']) > 1) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Найдено более одного человека'
			));
		}
		*/
		$response = array('error_code' => 0);
		$response['data'] = $this->filterOutFieldsInList($resp['data'], array(
			'Person_id',
			'PersonSurName_SurName',
			'PersonFirName_FirName',
			'PersonSecName_SecName',
			'PersonBirthDay_BirthDay',
			'Person_Sex_id',
			'PersonPhone_Phone',
			'PersonSnils_Snils',
			'PersonInn_Inn',
			'SocStatus_id',
			'UAddress_id',
			'PAddress_id',
			'BAddress_id',
			'Org_id',
			'Post_id',
			'BDZ_guid',
			'BDZ_id',
			'Person_pid',
			'DeputyKind_id',
			'Person_isUnknown'
		));

		$this->response($response);
	}

	/**
	 * Получение данных по сигнальной информации пациента
	 */
	function PersonSignalInfo_get() {
		$data = $this->ProcessInputData('getPersonSignalInfo');

		$resp = $this->dbmodel->getPersonSignalInfo($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных пациента
	 */
	function mPersonData_get() {
		$data = $this->ProcessInputData('mPersonData', null, true);

		$this->load->helper('Reg');

		$this->load->library('swFilterResponse');
		$this->load->database();
		$this->load->model('Common_model');
		$data['mode'] = 'MobileAppPanel';
		$resp = $this->Common_model->loadPersonData($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 *	Получение данных о месте работы
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"Org_id": "Идентификатор организации",
	 *			"OrgUnion_id": "Идентификатор",
	 * 			"Post_id": "Идентификатор должности",
	 * 			"Post_Name": "Наименование должности",
				"Org_Name": "Наименование организации",
	 			"Org_Nick": "краткое наименование организации",
				"Org_StickNick": "Наименование организации для печати"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
	 * 				"Org_id": 9400001980,
	 * 				"OrgUnion_id": null,
	 * 				"Post_id": 9400000981,
	 * 				"Post_Name": "УБОРЩИЦА",
	 				"Org_Nick" : "ОАО \"РАЙТЕПЛОЭНЕРГО-СЕРВИС\"",
					"Org_Name": "ОАО \"РАЙТЕПЛОЭНЕРГО-СЕРВИС\"",
					"Org_StickNick": null
	 * 			}
	 * 		}
	 * }
	 */
	function mGetPersonJobInfo_get() {

		$data = $this->ProcessInputData('getPersonJobInfo',null, true);
		if ($data === false) return false;

		$data['fromMobile'] = true;
		$resp = $this->dbmodel->getPersonJobInfo($data);

		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0,'data' => $resp));
	}


	/**
	 * Добавление данных о человеке
	 */
	function index_post() {

		$data = $this->ProcessInputData('createPerson', null, true, true, true);
			
		$this->load->helper('person');
		$this->load->helper('Options');
		$this->dbmodel->setParams($data);
		$options = $this->dbmodel->getGlobalOptions();
		$settings = $options['globals'];

		if ( !empty($data['PersonPhone_Phone']) && !preg_match("/^\d{10}$/", $data['PersonPhone_Phone']) ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Неверный формат телефонного номера. Телефонный номер должен состоять из 10 цифр.'
			));
		}
		
		if( empty($data['Person_isUnknown']) || $data['Person_isUnknown'] != 1 ){
			if( empty($data['PersonSurName_SurName']) || empty($data['PersonBirthDay_BirthDay']) || empty($data['Person_Sex_id']) || empty($data['SocStatus_id'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Отсутствует один из обязательных параметров PersonSurName_SurName, PersonBirthDay_BirthDay, Person_Sex_id, SocStatus_id'
				));
			}
			
			if (!empty($data['PersonSnils_Snils'])) {
				if (!checkPersonSnils($data['PersonSnils_Snils'])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'СНИЛС человека введен неверно! (не удовлетворяет правилам формирования СНИЛС).'
					));
				}
			} elseif ($settings['snils_control'] == 3) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Отсутствует параметр PersonSnils_Snils'
				));
			}
		}elseif( isset($data['Person_isUnknown']) && $data['Person_isUnknown'] == 1 ){
			if( empty($data['PersonSurName_SurName']) ){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'отсутствует параметр PersonSurName_SurName'
				));
			}
		}

		if ( !empty($data['PersonBirthDay_BirthDay']) && $data['PersonBirthDay_BirthDay'] > date('Y-m-d') ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Некорректное значение в поле PersonBirthDay_BirthDay'
			));
		}

		if ( !empty($data['PersonInn_Inn']) && $settings['inn_correctness_control'] == 3 ) {
			if (!CheckInn($data['PersonInn_Inn'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка проверки контрольной суммы в ИНН'
				));
			}
		}
		
		$mapping = array(
			'PersonSurName_SurName' => 'Person_SurName',
			'PersonFirName_FirName' => 'Person_FirName',
			'PersonSecName_SecName' => 'Person_SecName',
			'PersonBirthDay_BirthDay' => 'Person_BirthDay',
			'Person_Sex_id' => 'PersonSex_id',
			'PersonPhone_Phone' => 'PersonPhone_Phone',
			'PersonSnils_Snils' => 'Person_SNILS',
			'SocStatus_id' => 'SocStatus_id',
			'UAddress_id' => 'UAddress_id',
			'PAddress_id' => 'PAddress_id',
			'BAddress_id' => 'BAddress_id',
			'Org_id' => 'Org_id',
			'Post_id' => 'Post_id',
			'PersonInn_Inn' => 'PersonInn_Inn',
			'Polis_EdNum' => 'Federal_Num'
		);
		
		if( isset($data['Person_isUnknown']) ){
			if(!in_array($data['Person_isUnknown'], array(0,1))){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Значение поля Person_isUnknown не соответствует ожидаемому'
				));
			}
			$mapping['Person_isUnknown'] = 'Person_IsUnknown';
		}
		
		if( !empty($data['DeputyKind_id']) ){
			$mapping['DeputyKind_id'] = 'DeputyKind_id';
		}

		if( !empty($data['Person_pid']) ){
			$mapping['Person_pid'] = 'DeputyPerson_id';
		}

		$params = array_merge($this->convertParams($data, $mapping), array(
			'Person_id' => null,
			'mode' => 'add',
			'oldValues' => '',
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session']
		));
		
		$fields = ['PersonSex_id', 'SocStatus_id', 'UAddress_id', 'PAddress_id', 'BAddress_id', 'Org_id', 'Post_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $params);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}

		$resp = $this->dbmodel->checkPersonDoubles($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if ($resp[0]['DoubleType_id'] > 0) {
			$msg = 'Человек c такими данными уже присутствует в базе';
			switch ((int)$resp[0]['DoubleType_id']) {
				case 1:
					$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Серия-Номер полиса, Фамилия, Год рождения";
					break;
				case 2:
					$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Серия-Номер полиса, Имя, Отчество, Год рождения";
					break;
				case 3:
					$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Фамилия, Имя, Отчество, Дата рождения";
					break;
			}

			$this->response(array(
				'error_code' => 6,
				'error_msg' => $msg
			));
		}
		
		//Контроль корректности ЕНП
		if (!empty($data['Polis_EdNum'])){
			$checkEdNumFedSignature = $this->dbmodel->checkEdNumFedSignature($data['Polis_EdNum']);
			if(!$checkEdNumFedSignature){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Единый номер полиса не соответствует формату.'
				));
			}
		}
		
		//Проверка на дублирование СНИЛС-а
		if(!empty($data['PersonSnils_Snils'])){
			$doublesSnils = $this->dbmodel->checkPersonSnilsDoubles(array('Person_id' => null, 'Person_SNILS' => $data['PersonSnils_Snils']));
			if(!$doublesSnils){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Человек с введённым номером СНИЛС уже есть в базе.'
				));
			}
		}
		
		$this->load->model('Address_model');
		$addresses = array();
		foreach(array('U','P','B') as $A) {
			$id = (!empty($params[$A.'Address_id'])?$params[$A.'Address_id']:null);
			if (!empty($id)) {
				if (!isset($addresses[$id])) {
					$resp = $this->Address_model->loadAddressData(array('Address_id' => $id));
					if (!is_array($resp) || count($resp) == 0) {
						$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
					}
					$addresses[$id] = $resp[0];
				}
				if (isset($addresses[$id])) {
					foreach ($addresses[$id] as $key => $value) {
						$fieldName = $A . str_replace('Edit', '', $key);
						$params[$fieldName] = $value;
					}
				}
			}
		}

		$resp = $this->dbmodel->savePersonEditWindow($params, true);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		// сохраняем полис
		if (!empty($data['fromMobile'])) {

			$data['EvnType'] = 'Polis';
			$data['apiAction'] = 'create';

			$data['Person_id'] = $resp[0]['Person_id'];

			$app_polis_resp = $this->dbmodel->savePersonAttributeForApi($data);
			if (!empty($app_polis_resp['Error_Msg'])) {
				$warning = 'Не удалось сохранить полис:'.$app_polis_resp['Error_Msg'];
			}
		}

		$response = array(
			'error_code' => 0,
			'data' => array(
				array(
					'Person_id' => $resp[0]['Person_id'],
					'PersonEvn_id' => !empty($resp[0]['PersonEvn_id']) ? $resp[0]['PersonEvn_id']: null,
					'Server_id' => !empty($resp[0]['Server_id']) ? $resp[0]['Server_id']: null
				)
			)
		);

		if (!empty($warning)) $response['warning_msg'] = $warning;
		$this->response($response);
	}

	/**
	 * Измение данных о человеке
	 */
	function index_put() {

		$data = $this->ProcessInputData('updatePerson', null, true, true, true);

		if ( !empty($data['PersonPhone_Phone']) && !preg_match("/^\d{10}$/", $data['PersonPhone_Phone']) ) {
			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'error_msg' => 'Неверный формат телефонного номера. Телефонный номер должен состоять из 10 цифр.'
			));
		}

		if (!empty($data['PersonSnils_Snils'])) {
			$this->load->helper('person');
			
			if (!checkPersonSnils($data['PersonSnils_Snils'])) {
				$this->response(array(
					'success' => false,
					'error_code' => 6,
					'error_msg' => 'СНИЛС человека введен неверно! (не удовлетворяет правилам формирования СНИЛС).'
				));
			}

		}

		// убрал проверки если не указан признак неизвестного
		//...какие могут быть проверки на обязательные поля, если мы отправляем сюда Person_id
		// т.е. например, чтобы обновить отчество персоны, я что должен передать еще кучу других параметров? где логика?

		if( isset($data['Person_isUnknown']) ){
			if(!in_array($data['Person_isUnknown'], array(0,1))){
				$this->response(array(
					'success' => false,
					'error_code' => 6,
					'error_msg' => 'Значение поля Person_isUnknown не соответствует ожидаемому'
				));
			}
			$mapping['Person_isUnknown'] = 'Person_IsUnknown';
		}

		$mapping = array(
			'Person_id' => 'Person_id',
			'PersonSurName_SurName' => 'Person_SurName',
			'PersonFirName_FirName' => 'Person_FirName',
			'PersonSecName_SecName' => 'Person_SecName',
			'PersonBirthDay_BirthDay' => 'Person_BirthDay',
			'Person_Sex_id' => 'PersonSex_id',
			'PersonPhone_Phone' => 'PersonPhone_Phone',
			'PersonSnils_Snils' => 'Person_SNILS',
			'SocStatus_id' => 'SocStatus_id',
			'UAddress_id' => 'UAddress_id',
			'PAddress_id' => 'PAddress_id',
			'BAddress_id' => 'BAddress_id',
			'Org_id' => 'Org_id',
			'Post_id' => 'Post_id',
			'Polis_id' => 'Polis_id',
			'PersonInn_Inn' => 'PersonInn_Inn',
			'Polis_EdNum' => 'Federal_Num'
		);

		if( !empty($data['DeputyKind_id']) ) $mapping['DeputyKind_id'] = 'DeputyKind_id';
		if( !empty($data['Person_pid']) ) $mapping['Person_pid'] = 'DeputyPerson_id';

		$resp = $this->dbmodel->loadPersonListForAPI(array('Person_id' => $data['Person_id'], 'forMobile' => true));
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (count($resp['data']) == 0) {
			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'error_msg' => 'Пациент не найден в системе'
			));
		}

		$oldData = $this->convertParams($resp['data'][0], $mapping);
		$newData = $this->convertParams($data, $mapping);
		
		$fields = ['PersonSex_id', 'SocStatus_id', 'UAddress_id', 'PAddress_id', 'BAddress_id', 'Org_id', 'Post_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $newData);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}

		$oldValues = array();
		foreach($oldData as $key => $value) { $oldValues[] = $key.'='.$value; }

		foreach ($newData as $key => $value) {
			if(!isset($value)) unset($newData[$key]); // чтоб не возникало сложностей с NULL значениями
		}

		$params = array_merge($oldData, $newData, array(
			'mode' => 'edit',
			'oldValues' => urlencode(implode('&', $oldValues)),
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session']
		));

		$resp = $this->dbmodel->checkPersonDoubles($params);
		if (!is_array($resp) || !isset($resp[0])) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if ($resp[0]['DoubleType_id'] > 0) {

			$msg = 'Человек c такими данными уже присутствует в базе';

			switch ((int)$resp[0]['DoubleType_id']) {
				case 1:
					$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Серия-Номер полиса, Фамилия, Год рождения";
					break;
				case 2:
					$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Серия-Номер полиса, Имя, Отчество, Год рождения";
					break;
				case 3:
					$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Фамилия, Имя, Отчество, Дата рождения";
					break;
			}

			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'error_msg' => $msg
			));
		}

		//Контроль корректности ЕНП
		if (!empty($data['Polis_EdNum'])){
			$checkEdNumFedSignature = $this->dbmodel->checkEdNumFedSignature($data['Polis_EdNum']);
			if(!$checkEdNumFedSignature){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Единый номер полиса не соответствует формату.'
				));
			}
		}

		//Проверка на дублирование СНИЛС-а
		if(!empty($data['PersonSnils_Snils'])){
			$doublesSnils = $this->dbmodel->checkPersonSnilsDoubles(array('Person_id' => $params['Person_id'], 'Person_SNILS' => $data['PersonSnils_Snils']));
			if(!$doublesSnils){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Человек с введённым номером СНИЛС уже есть в базе.'
				));
			}
		}

		$this->load->model('Address_model');
		$addresses = array();

		foreach(array('U','P','B') as $A) {

			$id = (!empty($params[$A.'Address_id'])?$params[$A.'Address_id']:null);

			if (!empty($id)) {

				if (!isset($addresses[$id])) {

					$resp = $this->Address_model->loadAddressData(array('Address_id' => $id));
					if (!is_array($resp) || count($resp) == 0) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

					$addresses[$id] = $resp[0];
				}

				if (isset($addresses[$id])) {
					foreach($addresses[$id] as $key => $value) {
						$fieldName = $A.str_replace('Edit', '', $key);
						$params[$fieldName] = $value;
					}
				}
			}
		}

		//echo '<pre>',print_r($params),'</pre>'; die();
		$resp = $this->dbmodel->savePersonEditWindow($params, true);

		if (!is_array($resp) || !isset($resp[0])) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'success' => false,
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		// сохраняем полис
		if (!empty($data['fromMobile'])) {

			if (!empty($oldData['Polis_id'])) {

				$data['EvnType'] = 'Polis';
				$data['apiAction'] = 'update';
				$data['Polis_id'] = $oldData['Polis_id'];

			} else {
				$data['EvnType'] = 'Polis';
				$data['apiAction'] = 'create';
			}

			$app_polis_resp = $this->dbmodel->savePersonAttributeForApi($data);
			if (!empty($app_polis_resp['error_msg'])) {
				$warning = 'Не удалось сохранить полис:'.$app_polis_resp['error_msg'];
			}
		}

		$response = array('success' => true,'error_code' => 0,'data' => array(
			array(
				'Person_id' => $resp[0]['Person_id'],
				'PersonEvn_id' => !empty($resp[0]['PersonEvn_id']) ? $resp[0]['PersonEvn_id']: null,
				'Server_id' => !empty($resp[0]['Server_id']) ? $resp[0]['Server_id']: null
			)
		));// #106230#172
		if (!empty($warning)) $response['warning_msg'] = $warning;

		$this->response($response);
	}

	/**
	 * Полученеие списка людей
	 *
	 * @deprecated
	 */
	function PersonList_get() {
		$response = array('success' => false, 'error_msg' => 'Метод недоступен. Используйте api/Person с аналогичными параметрами');
		return $this->response($response);
	}

	/**
	 * Получение данных полиса
	 */
	function polis_get() {
		$data = $this->ProcessInputData('getPolis');

		$resp = $this->dbmodel->getLastPolisForAPI($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->response($resp);
	}

	/**
	 * Добавление данных о полисе
	 */
	function polis_post() {
		$data = $this->ProcessInputData('createPolis', null, true, true, true);

		$data['EvnType'] = 'Polis';
		$data['apiAction'] = 'create';
		$data['OMSSprTerr_id'] = !empty($data['OmsSprTerr_id']) ? $data['OmsSprTerr_id'] : null;
		$data['OrgSMO_id'] = !empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null;
		
		$fields = ['OmsSprTerr_id', 'PolisType_id', 'OrgSmo_id', 'PolisFormType_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		
		if (!empty($data['Polis_endDate']) && $data['Polis_endDate'] < $data['Polis_begDate']) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Дата закрытия полиса не может быть меньше даты открытия полиса'));
		}
		
		if (!empty($data['Polis_begDate']) && $data['Polis_begDate'] > date('Y-m-d')) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Дата открытия полиса не может быть больше текущей даты'));
		}
		
		$fields = ['PolisType_id', 'Polis_Ser', 'Polis_Num'];
		if ($this->commonCheckDoubles('Polis', $fields, $data) !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные полиса не прошли проверку на дублирование'));
		}
		
		$resp = $this->dbmodel->savePersonAttributeForApi($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array('error_code' => 6,'error_msg' => $resp['Error_Msg']));
		}
		if (!empty($resp['error_msg'])) {
			$this->response(array('error_code' => 6,'error_msg' => $resp['error_msg']));
		}
		if( empty($resp['Polis_id']) ) {
			$this->response(array('error_code' => 3,'error_msg' => 'при добавлении данных о полисе произошли ошибки'));
		}
		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array(
					'Polis_id' => $resp['Polis_id'],
					'Person_id' => $data['Person_id']
				)
			)
		));
	}

	/**
	 * Изменение данных о полисе
	 */
	function polis_put() {

		$data = $this->ProcessInputData('updatePolis', null, true, true, true);
		$data['EvnType'] = 'Polis';
		$data['apiAction'] = 'update';
		$data['OMSSprTerr_id'] = !empty($data['OmsSprTerr_id']) ? $data['OmsSprTerr_id'] : null;
		$data['OrgSMO_id'] = !empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null;
		
		if (!empty($data['Person_id']) && $this->checkPersonId($data['Person_id']) === false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Пациент не найден в системе'
			));
		}
		
		$resp = $this->dbmodel->getPolisForAPI($data);

		if (!is_array($resp) || !count($resp) || (!empty($data['Person_id']) && $data['Person_id'] != $resp[0]['Person_id'])) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Нет данных о полисе пациента с указанным Polis_id'));
		}
		
		if (!empty($data['Polis_endDate']) && $data['Polis_endDate'] < $data['Polis_begDate']) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Дата закрытия полиса не может быть меньше даты открытия полиса'));
		}
		
		if (!empty($data['Polis_begDate']) && $data['Polis_begDate'] > date('Y-m-d')) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Дата открытия полиса не может быть больше текущей даты'));
		}
		
		$fields = ['OmsSprTerr_id', 'PolisType_id', 'OrgSmo_id', 'PolisFormType_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		
		$fields = ['PolisType_id', 'Polis_Ser', 'Polis_Num'];
		if ($this->commonCheckDoubles('Polis', $fields, $data) !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные полиса не прошли проверку на дублирование'));
		}
		
		$resp = $this->dbmodel->savePersonAttributeForApi($data);

		if (!empty($resp['Error_Msg'])) {
			$this->response(array('error_code' => 6,'error_msg' => $resp['Error_Msg']));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Получение данных о документе
	 */
	function document_get() {
		$data = $this->ProcessInputData('getDocument');

		$resp = $this->dbmodel->getLastDocumentForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных о документе
	 */
	function document_post() {
		$data = $this->ProcessInputData('createDocument', null, true, true, true);

		$data['PersonEvn_id'] = $this->dbmodel->getFirstResultFromQuery("
			select top 1 PersonEvn_id from v_PersonState PS with(nolock) where PS.Person_id = :Person_id
		", $data);
		if (!$data['PersonEvn_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Пациент не найден в системе'
			));
		}

		$data['EvnType'] = 'Document';

		$error = $this->dbmodel->checkDocument($data);
		if (!empty($error)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $error
			));
		}
		
		$fields = ['DocumentType_id', 'OrgDep_id', 'KLCountry_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		
		$fields = ['DocumentType_id', 'Document_Ser', 'Document_Num'];
		if ($this->commonCheckDoubles('Document', $fields, $data) !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные документа не прошли проверку на дублирование'));
		}

		try{
			$this->dbmodel->exceptionOnValidation = true;
			$resp = $this->dbmodel->editPersonEvnAttributeNew($data);
			if (isset($resp[0]) && !empty($resp[0]['Error_Msg'])) {
				throw new Exception($resp[0]['Error_Msg']);
			}
			$this->dbmodel->exceptionOnValidation = false;
		} catch(Exception $e) {
			$this->dbmodel->exceptionOnValidation = false;
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $e->getMessage()
			));
		}
		$response = $this->dbmodel->getSaveResponse();

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array(
					'Person_id' => $data['Person_id'],
					'Document_id' => $response['Document_id'],
				)
			)
		));
	}

	/**
	 * Изменеие данных о документе
	 */
	function document_put() {
		$data = $this->ProcessInputData('updateDocument');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createDocument');
		$data = $this->unsetEmptyFields($data, $requiredFields);
		
		if (!empty($data['Person_id']) && $this->checkPersonId($data['Person_id']) === false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Пациент не найден в системе'
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['session'] = $sp['session'];

		$old_data = $this->dbmodel->getDocumentForAPI($data);
		if (!is_array($old_data)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($old_data[0])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Нет данных о документе пациента с указанным Document_id'
			));
		}
		
		$fields = ['DocumentType_id', 'OrgDep_id', 'KLCountry_id'];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		
		$data = array_merge($old_data[0], $data);
		
		$fields = ['DocumentType_id', 'Document_Ser', 'Document_Num'];
		if ($this->commonCheckDoubles('Document', $fields, $data) !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные документа не прошли проверку на дублирование'));
		}

		$error = $this->dbmodel->checkDocument($data);
		if (!empty($error)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $error
			));
		}

		$data['PersonEvn_id'] = $data['PersonDocument_id'];
		$data['EvnType'] = 'Document';
		$data['cancelCheckEvn'] = true;

		try{
			$this->dbmodel->exceptionOnValidation = true;
			$resp = $this->dbmodel->editPersonEvnAttributeNew($data);
			if (isset($resp[0]) && !empty($resp[0]['Error_Msg'])) {
				throw new Exception($resp[0]['Error_Msg']);
			}
			$this->dbmodel->exceptionOnValidation = false;
		} catch(Exception $e) {
			$this->dbmodel->exceptionOnValidation = false;
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $e->getMessage()
			));
		}
		$response = $this->dbmodel->getSaveResponse();

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Метод получения общей информации о пациенте и количестве записей для каждого из разделов сигнальной информации

	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"Person_id": "Идентификатор человека",
				"PersonCard_id": "Идентификатор карты",
				"Server_id": "Идентификатор сервера",
				"Sex_Name": "Пол",
				"Person_Birthday": "Дата рождения",
				"SocStatus_Name": "Соц. статус",
				"Person_Snils": "СНИЛС",
				"Person_UAddress": "Адрес прописки",
				"Person_PAddress": "Адрес проживания",
				"PersonLpuInfoCount": "Счетчик согласий",
				"PersonPrivilegeCount": "Счетчик льгот",
				"PersonBloodGroupCount": "Счетчик групп крови",
				"PersonMedHistoryCount": "Счетчик ",
				"PersonAllergicReactionCount": "Счетчик аллергический реакций",
				"PersonDispCount": "Счетчик осмотров и диспансеризаций",
				"EvnPLDispCount": "Счетчик талонов диспансеризации",
				"PersonHeightCount": "Счетчик измерений веса",
				"PersonWeightCount": "Счетчик измерений роста",
				"Polis_Ser": "Серия полиса",
				"Polis_Num": "Номер полиса",
				"Polis_begDate": "Дата начала полиса",
				"Polis_endDate": "Дата закрытия полиса",
				"Person_Polis": " Номер полиса",
				"Person_Document": "Информация о документа подтв. личности",
				"DocumentType_Name": "Наименования документа подтв. личности",
				"Document_Num": "Номер документа",
				"Document_Ser": "Серия документа",
				"Document_begDate": "Дата начала документа",
				"Document_endDate": "Дата окончани документа",
				"Person_Job": "Место работы",
				"Person_Post": "Должность",
				"Person_Attach": "Прикрепление",
				"EvnDiagCount": "Счетчик диагнозов",
				"DirFailListCount": "Счетчик отмененных направлений",
				"UslugaOperCount": "Счетчик опер. вмешательств"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"Person_id": "2272564",
					"PersonCard_id": "77306492155",
					"Server_id": "150185",
					"Sex_Name": "Мужской",
					"Person_Birthday": "06.10.1998",
					"SocStatus_Name": null,
					"Person_Snils": "13794614090",
					"Person_UAddress": "614065, РОССИЯ, ПЕРМСКИЙ КРАЙ, Г ПЕРМЬ, ИНДУСТРИАЛЬНЫЙ РАЙОН, КАЗАНЦЕВСКАЯ 2-Я УЛ, д. 10, кв. 4",
					"Person_PAddress": "614065, РОССИЯ, ПЕРМСКИЙ КРАЙ, Г ПЕРМЬ, ИНДУСТРИАЛЬНЫЙ РАЙОН, КАЗАНЦЕВСКАЯ 2-Я УЛ, д. 10, кв. 4",
					"PersonLpuInfoCount": 0,
					"PersonPrivilegeCount": 1,
					"PersonBloodGroupCount": 2,
					"PersonMedHistoryCount": 1,
					"PersonAllergicReactionCount": 1,
					"PersonDispCount": 0,
					"EvnPLDispCount": 23,
					"PersonHeightCount": 1,
					"PersonWeightCount": 0,
					"Polis_Ser": "",
					"Polis_Num": "5901958022200888",
					"Polis_begDate": "24.06.2017",
					"Polis_endDate": "29.07.2017",
					"Person_Polis": " 5901958022200888",
					"Person_Document": "Паспорт гражданина Российской Федерации 57 05 736423",
					"DocumentType_Name": "Паспорт гражданина Российской Федерации",
					"Document_Num": "736423",
					"Document_Ser": "57 05",
					"Document_begDate": "",
					"Document_endDate": "",
					"Person_Job": "ОАО \"РАЙТЕПЛОЭНЕРГО-СЕРВИС\"",
					"Person_Post": "УБОРЩИЦА",
					"Person_Attach": "ПЕРМЬ ККБ, участок: 1",
					"EvnDiagCount": 22,
					"DirFailListCount": 304,
					"UslugaOperCount": 2
	 * 			}
	 * 		}
	 * }
	 */
	function mloadPersonForm_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('EPH_model', 'EPH_model');
		$data['fromApi'] = true;
		$data['Lpu_id'] = null;

		$resp = $this->EPH_model->mLoadPersonForm($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Список информированных добровольных согласий
	 */
	function mloadPersonLpuInfoPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('Person_model', 'Person_model');
		$resp = $this->Person_model->loadPersonLpuInfoPanelForAPI($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение группы крови человека
	 */
	function mloadPersonBloodGroupPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('PersonBloodGroup_model', 'PersonBloodGroup_model');
		$resp = $this->PersonBloodGroup_model->loadPersonBloodGroupPanel($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение анамнеза жизни
	 */
	function mloadPersonMedHistoryPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('PersonMedHistory_model', 'PersonMedHistory_model');
		$resp = $this->PersonMedHistory_model->loadPersonMedHistoryPanelForAPI($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение аллегрологического анамнеза
	 */
	function mloadPersonAllergicReaction_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('PersonAllergicReaction_model', 'PersonAllergicReaction_model');
		$resp = $this->PersonAllergicReaction_model->loadPersonAllergicReaction($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение диспансерного учета пациента
	 */
	function mloadPersonDispPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('Polka_PersonDisp_model', 'Polka_PersonDisp_model');
		$resp = $this->Polka_PersonDisp_model->loadPersonDispPanel($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение списка уточненных диагнозов пациента
	 */
	function mloadPersonDiagPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		$data['Lpu_id'] = null;
		
		$this->load->model('EvnDiag_model', 'EvnDiag_model');
		$resp = $this->EvnDiag_model->loadPersonDiagPanel($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение списка измерений роста человека
	 */
	function mloadPersonHeightPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('PersonHeight_model', 'PersonHeight_model');
		$resp = $this->PersonHeight_model->loadPersonHeightPanel($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Получение списка измерений веса человека
	 */
	function mloadPersonWeightPanel_get(){
		$data = $this->ProcessInputData('getPersonID');
		
		$this->load->model('PersonWeight_model', 'PersonWeight_model');
		$resp = $this->PersonWeight_model->loadPersonWeightPanel($data);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка ЛВН
	 */
	function mloadPersonStickPanel_get(){
		$data = $this->ProcessInputData('getPersonID');

		$this->load->model('EvnStick_model');
		$resp = $this->EvnStick_model->getEvnStickOpenInfoViewData($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка диспансеризаций/медосмотров
	 */
	function mloadPersonEvnPLDispInfoPanel_get(){
		$data = $this->ProcessInputData('getPersonID');

		$this->load->model('EvnPLDisp_model');
		$resp = $this->EvnPLDisp_model->getEvnPLDispInfoViewData($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка свидетельств
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"Person_id": "Идентификатор персоны",
				"Server_id": "Идентификатор сервера",
				"PersonSvid_id": "Идентификатор свидетельства",
				"PersonSvidInfo_id": null,
				"PersonSvidType_Code": "Код типа свидетельства",
				"PersonSvidType_Name": "Наименование типа свидетельства",
				"PersonSvid_Ser": "Серия свидетельства",
				"PersonSvid_Num": "Номер свидетельства",
				"PersonSvid_GiveDate": "Дата выдачи"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
	 * 			}
	 * 		}
	 * }
	 */
	function mloadPersonSvidInfoPanel_get(){
		$data = $this->ProcessInputData('getPersonID');

		$resp = $this->dbmodel->getPersonSvidInfo($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка льгот и экспертный анамнез
	 */
	function mloadPersonPrivelegePanel_get(){
		$data = $this->ProcessInputData('getPersonID');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$this->load->model('Privilege_model');
		$resp = $this->Privilege_model->getPersonPrivilegeViewData($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * метод изменения данных человека
	 */
	function msavePersonEditWindow_post(){

		// признак того что мы должны обновить данные полиса если чо
		$this->_args['fromMobile'] = true;
		$this->index_put();
	}

	/**
	 * метод cохранения данных человека
	 */
	function mSavePerson_post(){

		// признак того что мы должны обновить данные полиса если чо
		$this->_args['fromMobile'] = true;
		$this->index_post();
	}

	/**
	 * Поиск человека по ФИО, д.р., серии, номеру полиса, СНИЛСу
	 * можно указать start и limit (по умолчанию 0-100)
	 */
	function mgetPersonSearch_get(){

		//$this->echoParamsInfo($this->inputRules,'mgetPersonSearch');
		if (!isset($this->_args['searchMode'])) $this->_args['searchMode'] = "all";
		$data = $this->ProcessInputData('mgetPersonSearch', null, true);

		if (empty($data['Lpu_id'])) {
			$session = getSessionParams();
			$data['Lpu_id'] = $session['Lpu_id'];
		}

		$resp = $this->dbmodel->getPersonSearchGrid($data);
		if (isset($resp['data'])) $resp = $resp['data'];

		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Карта оффлайн данных
	 */
	protected $offline_map = array(
		'blood_group' => array('model' => 'PersonBloodGroup_model', 'method' => 'loadPersonBloodGroupPanel'),
		'med_history' => array('model' =>'PersonMedHistory_model', 'method' => 'loadPersonMedHistoryPanelForAPI'),
		'allergic_reaction' => array('model' => 'PersonAllergicReaction_model', 'method' => 'loadPersonAllergicReaction'),
		'diag' => array('model' => 'EvnDiag_model', 'method' => 'loadPersonDiagPanel'),
		'height' => array('model' => 'PersonHeight_model', 'method' => 'loadPersonHeightPanel'),
		'weight' => array('model' => 'PersonWeight_model', 'method' => 'loadPersonWeightPanel'),
		'consent' => array('model' => 'Person_model', 'method' => 'loadPersonLpuInfoPanelForAPI'),
		'disp' => array('model' => 'Polka_PersonDisp_model', 'method' => 'loadPersonDispPanel'),
		'person_overview' => array('model' => 'EPH_model', 'method' => 'mLoadPersonForm'),
		'personal_data' => array('model' => 'Common_model', 'method' => 'loadPersonData'),
		'history' => array('model' => 'EPH_model', 'method' => 'getPersonHistoryForApi'),
	);

	/**
	 * Получаем персоны прикрепленные к ЛПУ
	 */
	function mGetAttachedPersonList_get(){

		$this->load->model("Polka_PersonCard_model");

		$data = $this->ProcessInputData('mGetAttachedPersonList');
		$data['isOffline'] = true;

		// переопределяем лпу если указан медстаффакт
		if (!empty($data['MedStaffFact_id']) && empty($data['Lpu_id'])) {
			$data['Lpu_id'] = $this->dbmodel->getFirstResultFromQuery("
				select top 1 Lpu_id
				from v_MedStaffFact (nolock)
				where MedStaffFact_id = :MedStaffFact_id
			", $data);
		}

		$persons = $this->Polka_PersonCard_model->getPersonCardAPI($data);
		$response['count'] = count($persons);
		$response['persons'] = array_column($persons, 'Person_id');

		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	 * Получаем данные для оффлайн режима по каждой персоне ЛПУ
	 */
	function mGetPersonOfflineData_get(){

		$data = $this->ProcessInputData('mGetPersonOfflineData', null, true);
		$json_array = (array) json_decode($data['data']);

		if (empty($json_array['persons']) || !empty($json_array['persons']) && !is_array($json_array['persons']))
			$this->response(array('error_code' => 6,'error_msg' => "Неверный формат входных данных"));

		//$persons = array(1599600,1599619,1599619,1599631,1599631,1599634,1599652,1599652,1599652,1599653,1599653,1599653,1599654,1599654,1599675,1599676,1599677,1599677,1599722,1599722,1599722,1599722,1599724,1599724,1599752,1599752,1599762,1599762,1599794,1599794,1599817,1599817,1599896,1599896,1599896,1599896,1599896,1599902,1599902,1599912,1599918,1599925,1599926,1599926,1599965,1599965,1600034,1600034,1600039,1600039,1600051,1600051,1600091,1600091,1600153,1600153,1600153,1600173,1600173,1600192,1600197,1600197,1600203,1600203,1600207,1600207,1600390,1600390,1600428,1600450,1600450,1600451,1600451,1600467,1600467,1600484,1600484,1600506,1600506,1600522,1600522,1600545,1600545,1600549,1600549,1600579,1600579,1600585,1600585,1600613,1600613,1600646,1600646,1600651,1600660,1600660,1600670,1600670,1600670,1600679);

		$this->load->helper('Reg');
		$this->load->library('swFilterResponse');

		$data['mode'] = 'PersonInfoPanel';

		// в моделях инжектим этот фильтр к фильтру по персону
		$data['person_in'] = implode(',',$json_array['persons']);

		// зануляем id-шник чтобы не ругался
		$data['Person_id'] = 0;

		// область видимости этой оффлайн выборки
		$scopes = array('blood_group','med_history','allergic_reaction','diag','height',
			'weight','consent','disp','person_overview','personal_data','history');

		$raw_data = array();

		foreach($scopes as $scope) {

			// если скопе есть в оффлайн-карте
			if (isset($this->offline_map[$scope])) {

				$config = $this->offline_map[$scope];

				// загружаем модель и выполняем метод выборки данных
				$this->load->model($config['model']);
				$raw_data[$scope] = $this->{$config['model']}->{$config['method']}($data);
			}
		}

		$offline_data = array();

		// раскидываем данные по каждой персоне отдельно
		foreach ($raw_data as $data_key => $data_response) {

			foreach ($data_response as $i => $item) {

				// если есть идентификатор персоны
				if (!empty($item['Person_id'])) {

					// если он еще не был установлен как ключ массива
					if (!isset($offline_data[$item['Person_id']])) {
						// инициализируем массив
						$offline_data[$item['Person_id']] = array();
						// счетчик диагнозов
						$offline_data[$item['Person_id']]['EvnDiagCount'] = 0;
						$offline_data[$item['Person_id']]['Person_id'] = $item['Person_id'];
					}

					$person_data = &$offline_data[$item['Person_id']];

					// убираем лишние данные
					//unset($item['Person_id']);

					// если это сигнальная информация, копируем туда счетчик диагнозов
					if ($data_key == 'person_overview') {
						$item['EvnDiagCount'] = $offline_data[$item['Person_id']]['EvnDiagCount'];
						unset($offline_data[$item['Person_id']]['EvnDiagCount']);
					}

					// добавляем данные к ключу, для нашего персона
					$person_data[$data_key][] = $item;

					// если это диагнозы увеличиваем счетчик
					if ($data_key == 'diag') $person_data['EvnDiagCount']++;
				}
			}
		}

		//die();

		$offline_data = array_values($offline_data);
		$this->response(array('error_code' => 0,'data' => $offline_data));
	}

	/**
	 * Получение списка льгот человека
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"Lpu_id": "Идентификатор ЛПУ",
				"Person_id": "Идентификатор персоны",
				"PersonEvn_id": "Идентификатор события персоны",
				"PersonPrivilege_id": "Идентификатор льготы-персоны",
				"Server_id": "Идентификатор сервера",
				"PrivilegeType_id": "Идентификатор льготы",
				"PrivilegeType_Code": "Код типа льготы",
				"PrivilegeType_Name": "Наименование льготы",
				"Privilege_begDate": "Дата начала",
				"Privilege_endDate": "Дата окончания",
				"Privilege_Refuse": "Отказ",
				"Privilege_RefuseNextYear": "Отказ на след. год",
				"Lpu_Name": "Наименование ЛПУ",
				"ReceptFinance_id": null,
				"ReceptFinance_Code": null
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"Lpu_id": "10010833",
					"Person_id": "2272564",
					"PersonEvn_id": "87059843",
					"PersonPrivilege_id": "68321524694",
					"Server_id": "10010833",
					"PrivilegeType_id": "325",
					"PrivilegeType_Code": 325,
					"PrivilegeType_Name": "Мозжечковая атаксия Мари",
					"Privilege_begDate": "06.11.2018",
					"Privilege_endDate": null,
					"Privilege_Refuse": "",
					"Privilege_RefuseNextYear": "",
					"Lpu_Name": "ПЕРМЬ ГП 2.",
					"ReceptFinance_id": "2",
					"ReceptFinance_Code": 2
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadPersonPrivilegeList_get()
	{
		$this->load->model('Privilege_model', 'Privilege_model');
		$data = $this->ProcessInputData('mLoadPersonPrivilegeList', false, true);

		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$response = $this->Privilege_model->loadPersonPrivilegeList($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение списка отмененных направлений
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"EvnDirection_id": "Идентификатор отменного направления",
				"Person_setFio": null,
				"Person_failFio": "Врач который отменил направление",
				"EvnDirection_setDate": "Дата создания направления",
				"sortDT": null,
				"EvnDirection_failDate": "Дата отмены направления",
				"FailCause_Name": null
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"EvnDirection_id": "730023881147688",
					"Person_setFio": null,
					"Person_failFio": "ВРАЧОВ ДОКТОР ПОЛИКЛИНИКОВИЧ",
					"EvnDirection_setDate": "19.10.2018",
					"sortDT": "2018-10-12 10:13:38",
					"EvnDirection_failDate": "12.10.2018",
					"FailCause_Name": null
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadPersonDirFailPanel_get()
	{
		$data = $this->ProcessInputData('mLoadPersonDirFailPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->load->model('EvnDirection_model', 'EvnDirection_model');
		$response = $this->EvnDirection_model->getDirFailListViewData($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение списка диспансеризаций и осмотров пациента
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"EvnPLDisp_id": "Идентификатор осмотра",
				"EvnPLDispInfo_id": null,
				"DispClass_id": "Идентификатор класса осмотра",
				"DispClass_Code": "Код класса осмотра",
				"DispClass_Name": "Наименование класса осмотра",
				"Lpu_id": "Идентификатор ЛПУ",
				"Lpu_Nick": "Наименование ЛПУ",
				"EvnPLDisp_setDate": "Дата начала осмотра",
				"EvnPLDisp_disDate": "Дата окончания осмотра",
				"HealthKind_Name": "Наименование группы здоровья",
				"Object": "Класс события осмотра",
				"Diag_FullName": "Наименование диагноза"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
				"EvnPLDisp_id": "730022509242288",
				"EvnPLDispInfo_id": "730022509242288",
				"DispClass_id": "1",
				"DispClass_Code": 1,
				"DispClass_Name": "Дисп-ция взр. населения 1-ый этап",
				"Lpu_id": "10010833",
				"Lpu_Nick": "ПЕРМЬ ГП 2.",
				"EvnPLDisp_setDate": "08.12.2015",
				"EvnPLDisp_disDate": null,
				"HealthKind_Name": null,
				"Object": "EvnPLDispDop13",
				"Diag_FullName": null
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadPersonEvnPLDispPanel_get()
	{
		$data = $this->ProcessInputData('mLoadPersonEvnPLDispPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
		$response = $this->EvnPLDisp_model->getEvnPLDispInfoViewData($data);

		if (!empty($response) && is_array($response)) $response = array_values($response);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение списка оперативных вмешательств пациента
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"Person_id": "Идентификатор человека",
				"EvnUslugaOper_id": "Идентификатор оперативной услуги",
				"Children_Count": null,
				"SurgicalList_id": null,
				"EvnUslugaOper_setDate": "Дата создания события",
				"Lpu_Nick": "Идентификатор ЛПУ",
				"Usluga_Code": "Код услуги",
				"Usluga_Name": "Наименование услуги"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"Person_id": "2272564",
					"EvnUslugaOper_id": "730022509293143",
					"Children_Count": 0,
					"SurgicalList_id": "730022509293143",
					"EvnUslugaOper_setDate": "08.07.2016",
					"Lpu_Nick": "ПЕРМЬ ГП 2.",
					"Usluga_Code": "A16.02.001",
					"Usluga_Name": "Разрез мышцы, сухожильной фасции и синовиальной сумки"
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadPersonSurgicalPanel_get()
	{
		$data = $this->ProcessInputData('mLoadPersonSurgicalPanel', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->load->model('EvnUsluga_model', 'EvnUsluga_model');
		$response = $this->EvnUsluga_model->getEvnUslugaOperViewData($data);

		//if (!empty($response) && is_array($response)) $response = array_values($response);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение списка записей к врачу
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"future_records": [{}],
				"complete_records": [
					{
						"Timetable_id": "Идентификатор бирки",
						"Timetable_Date": "Дата бирки",
						"Timetable_Time": "Время бирки",
						"Lpu_id": "Идентификатор ЛПУ",
						"Lpu_Nick": "Наименование ЛПУ",
						"LpuUnit_Name": "Наименование отделения",
						"LpuUnit_Address": "Адрес отделения",
						"Person_id": "Идентификатор человека",
						"is_moderated": "Признак что запись промодерирована",
						"MedPersonal_FIO": "К кому записан",
						"ProfileSpec_Name": "Профиль (специальность)"
					}
	 * 			]
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
				"future_records": [],
				"complete_records": [
					{
						"Timetable_id": "171845229",
						"Timetable_Date": "06.11.2018",
						"Timetable_Time": "14:15",
						"Lpu_id": "150185",
						"Lpu_Nick": "ПЕРМЬ ККБ",
						"LpuUnit_Name": "стомат",
						"LpuUnit_Address": "ПУШКИНА 66",
						"Person_FIO": "ПОРТАЛ ИНФОМАТ ГЕННАДЬЕВИЧ",
						"Person_id": "2272564",
						"is_moderated": null,
						"MedPersonal_FIO": "МЕЛЬНИКОВА ЛЮБОВЬ АЛЕКСЕЕВНА",
						"ProfileSpec_Name": "СТОМАТОЛОГ"
					}]
	 * 			}
	 * 		}
	 * }
	 */
	function mGetPersonRecords_get()
	{
		$data = $this->ProcessInputData('mLoadPersonRecords', false, true);
		if ($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$data['tt'] = 'TimetableGraf';
		$response = $this->dbmodel->getPersonRecords($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 *
	@OA\post(
	path="/api/rish/Person/mSendPersonCallNotify",
	tags={"Person"},
	summary="Вызов пациента(отправка уведомления)",

	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор человека",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="ARMType_id",
	in="query",
	description="Идентификатор места работы",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ответа",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	)

	)

	)
	)

	)
	 */
	function mSendPersonCallNotify_post() {

		$this->load->model('Person_model');
		$data = $this->ProcessInputData('mSendPersonCallNotify', null, true);

		$response = $this->Person_model->mSendPersonCallNotify($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	@OA\get(
	path="/api/rish/Person/getPersonRecordsAllForPortal",
	tags={"Person"},
	summary="Показать будущие и прошедшие записи",

	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор человека",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="pastRecords",
	in="query",
	description="Флаг Показать только прошедшие записи",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="futureRecords",
	in="query",
	description="Флаг Показать только будущие записи",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="showTodayRecords",
	in="query",
	description="Флаг Показать только записи на сегодня",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="pmuser_id",
	in="query",
	description="Идентифкатор аккаунта",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="TimetableGraf_id",
	description="Идентификатор бирки",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableGraf_Day",
	description="Идентификатор дня",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableGraf_updDT",
	description="Время обновления бирки",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableGraf_begTime",
	description="Дата и время бирки",
	type="string",

	)
	,
	@OA\Property(
	property="RecordSetDate",
	description="Дата бирки",
	type="string",

	)
	,
	@OA\Property(
	property="RecordSetTime",
	description="Время бирки",
	type="string",

	)
	,
	@OA\Property(
	property="DateDiff",
	description="Разница в минутах от текущего времени",
	type="string",

	)
	,
	@OA\Property(
	property="MonthDiff",
	description="Разница в месяцах от текущего времени",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="Lpu_Nick",
	description="Краткое наименование ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_Name",
	description="Полное наименование ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnit_id",
	description="Группы отделений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuUnit_Name",
	description="Группы отделений, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnit_Phone",
	description="Группы отделений, телефон",
	type="string",

	)
	,
	@OA\Property(
	property="LpuUnit_Address",
	description="Адрес подразделения",
	type="string",

	)
	,
	@OA\Property(
	property="Person_FIO",
	description="ФИО человека",
	type="string",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_TalonCode",
	description="Выписка направлений, Код бронирования электронной очереди",
	type="string",

	)
	,
	@OA\Property(
	property="ElectronicTalon_Num",
	description="Талон Электронной очереди, номер талона",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableGraf_IsModerated",
	description="Признак модерации бирки",
	type="boolean",

	)
	,
	@OA\Property(
	property="MedStaffFact_id",
	description="Кэш мест работы, идентификатор места работы",
	type="integer",

	)
	,
	@OA\Property(
	property="MedSpecOms_id",
	description="справочник специальностей врачей по ОМС, Идентификатор записи",
	type="integer",

	)
	,
	@OA\Property(
	property="MedStaffFactCache_IsPaidRec",
	description="Тип стоимости бирки
	 * 1- бесплатная
	 * 2 - платная",
	type="boolean",

	)
	,
	@OA\Property(
	property="MedStaffFactCache_CostRec",
	description="Стоимость приёма врача",
	type="string",

	)
	,
	@OA\Property(
	property="MedPersonal_FIO",
	description="ФИО врача",
	type="string",

	)
	,
	@OA\Property(
	property="MedPersonal_FullFIO",
	description="Полное ФИО",
	type="string",

	)
	,
	@OA\Property(
	property="ProfileSpec_Name",
	description="Название специальности",
	type="string",

	)
	,
	@OA\Property(
	property="Profile_id",
	description="Идентификатор профиля",
	type="integer",

	)
	,
	@OA\Property(
	property="cabinetDetectionZNO",
	description="Кабинет раннего выявления заболеваний",
	type="string",

	)

	)

	)

	)
	)

	)
	 */

	function getPersonRecordsAllForPortal_get() {
		//$this->load->model('Person_model');
		$data = $this->ProcessInputData('getPersonRecordsAllForPortal', null, false);
		$response = $this->dbmodel->getPersonRecordsAll($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	//подгрузка email с портала

    function getEmail_get() {
        $data = $this->ProcessInputData('getEmail', null, false);
        $this->load->model('InetPerson_model');
        unset($this->db);
        $this->load->database('UserPortal');

        $response = $this->InetPerson_model->getEmail($data);
        $this->response(array('error_code' => 0,'data' => $response));
    }
}