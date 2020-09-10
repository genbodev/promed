<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Person - контроллер для управления людьми
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author		Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version		12.07.2009
 * @property Person_model dbmodel
*/
class Person extends swController {

	public $inputRules = array(
		'exportPersonProfData' => array(
			array('field' => 'Month', 'label' => 'Месяц', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Year', 'label' => 'Год', 'rules' => 'required', 'type' => 'int' ),
		),
		'loadPersonLpuInfoPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonSvidPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonSurgicalPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonEvnPLDispPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonDirFailPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonMantuReactionPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonInoculationPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonInoculationPlanPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonEvnIdByEvnId' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Evn_id',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'getAddressByPersonId'=>array(
			array(
				'field'=>'Person_id',
				'label'=>'Person_id',
				'rules'=>'required',
				'type'=>'id'
			),
		),
		'savePersonUAddress' => array(
			array(
				'field' => 'Person_id',
				'label'	=> 'Person_id',
				'rules'	=> 'required',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'Server_id',
				'label'	=> 'Server_id',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'insDT',
				'label'	=> 'Дата создания',
				'rules'	=> '',
				'type'	=> 'datetime'
			),
			array(
				'field'	=> 'PersonEvn_id',
				'label'	=> 'PersonEvn_id',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'KLCountry_id',
				'label'	=> 'KLCountry_id',
				'rules'	=> 'trim',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'KLRgn_id',
				'label'	=> 'KLRgn_id',
				'rules'	=> 'trim',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'KLSubRgn_id',
				'label'	=> 'KLSubRgn_id',
				'rules'	=> 'trim',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'KLCity_id',
				'label'	=> 'KLCity_id',
				'rules'	=> 'trim',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'KLTown_id',
				'label'	=> 'KLTown_id',
				'rules'	=> 'trim',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'KLStreet_id',
				'label'	=> 'KLStreet_id',
				'rules'	=> 'trim',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'Address_Zip',
				'label'	=> 'PersonEvn_id',
				'rules'	=> 'trim',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'Address_House',
				'label'	=> 'Address_House',
				'rules'	=> 'trim',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'Address_Corpus',
				'label'	=> 'Address_Corpus',
				'rules'	=> 'trim',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'Address_Flat',
				'label'	=> 'Address_Flat',
				'rules'	=> 'trim',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'PersonSprTerrDop_id',
				'label'	=> 'PersonSprTerrDop_id',
				'rules'	=> 'trim',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'Address_Address',
				'label'	=> 'Address_Address',
				'rules'	=> 'trim',
				'type'	=> 'string'
			)
		),
		'checkChildrenDuplicates'=>array(
			array(
				'field'=>'Person_BirthDay',
				'label'=>'Person_BirthDay',
				'rules'=>'trim',
				'type'=>'date'
			),
			array(
				'field'=>'Person_FirName',
				'label'=>'Person_FirName',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Person_SecName',
				'label'=>'Person_SecName',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Person_SurName',
				'label'=>'Person_SurName',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Person_pid',
				'label'=>'Person_pid',
				'rules'=>'trim',
				'type'=>'id'
			),
			
			array(
				'field'=>'DeputyKind_id',
				'label'=>'DeputyKind_id',
				'rules'=>'trim',
				'type'=>'id'
			),
			array(
				'field'=>'Sex_id',
				'label'=>'Sex_id',
				'rules'=>'trim',
				'type'=>'id'
			),
		),
		'getPersonIdentData'=>array(
			array(
				'field'=>'KLStreet_Name',
				'label'=>'KLStreet_Name',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'KLAdr_Ocatd',
				'label'=>'KLAdr_Ocatd',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Org_Name',
				'label'=>'Org_Name',
				'rules'=>'trim',
				'type'=>'string'
			)
		),
		'getPersonByAddress'=>array(
			array(
				'field'=>'Area_pid',
				'label'=>'Area_pid',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Town_id',
				'label'=>'Town_id',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'KLStreet_id',
				'label'=>'KLStreet_id',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Address_House',
				'label'=>'Address_House',
				'rules'=>'trim',
				'type'=>'string'
			),
			array(
				'field'=>'Address_Flat',
				'label'=>'Address_Flat',
				'rules'=>'trim',
				'type'=>'string'
			)
		),		
		'CheckSpecifics' => array(
			array(
				'field'=>'Records',
				'label'=>'Records',
				'rules'=>'trim|required',
				'type'=>'string'
			)
		),
		'getInfoForDouble' => array(
			array(
				'field'=>'Person_id',
				'label'=>'Идентификатор человека',
				'rules'=>'required',
				'type'=>'id'
			)
		),
		'getDiagnosesPersonOnDisp' => array(
			array(
				'field'=>'Person_id',
				'label'=>'Идентификатор человека',
				'rules'=>'required',
				'type'=>'id'
			),
			array(
				'field'=>'actualForToday',
				'label'=>'актуально на текущую дату',
				'rules'=>'',
				'type'=>'boolean'
			)
		),
		'getOrgSMO'=>array(
			array(
				'field' => 'Org_OGRN',
				'label' => 'ОГРН организации',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Org_OKATO',
				'label' => 'ОКАТО организации',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		
		'getPersonByBarcodeData' => array(
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_endDate',
				'label' => 'Дата окончания действия полиса',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Sex_Code',
				'label' => 'Пол',
				'rules' => 'trim',
				'type' => 'int'
			)
		),
		'getPersonSnils' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonCombo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePersonEvalEditWindow' => array(
			array(
				'field' => 'PersonEval_id',
				'label' => 'Идентификатор замеров',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEval_setDT',
				'label' => 'Дата замера',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonEval_Value',
				'label' => 'Значение замера',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonEval_IsAbnorm',
				'label' => 'Отклонение',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Вид',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvalAbnormType_id',
				'label' => 'Значение отклонения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvalType',
				'label' => 'Тип замера',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvalMeasureType_id',
				'label' => 'Вид замера',
				'rules' => '',
				'type' => 'int'
			),
		),
		'getPersonByUecData' => array(
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'ДР',
				'rules' => 'trim',
				'type' => 'date'
			)
		),
		'getPersonEditWindow' => array(
			array(
				'field' => 'person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		),
		'getPersonPolisInfo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonPhoneInfo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonJobInfo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'savePersonPhoneInfo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Phone_Promed',
				'label' => 'Телефон пациента в промеде',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPhone_id',
				'label' => 'Идентификатор телефона пациента в промеде',
				'rules' => '',
				'type' => 'id'
			)
		),
		'editPersonEvnDate' => array(
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор периодики',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Time',
				'label' => 'Время',
				'rules' => 'trim|required',
				'type' => 'time_with_seconds'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim|required',
				'type' => 'int'
			)
		),
		'getPersonEvnEditWindow' => array(
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDT',
				'label' => 'DT',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		),
		'editPersonEvnAttributeNew' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim|required',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Тип атрибута',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnType',
				'label' => 'Тип атрибута',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'NotEvnType',
				'label' => 'Не периодические атрибуты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'refresh',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'cancelCheckEvn',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'string'
			)
			//Продолжение в конструкторе
		),
		'editPersonEvnAttribute' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim|required',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnType',
				'label' => 'Тип атрибута',
				'rules' => 'trim|required',
				'type' => 'string'
			)
		),
		'deletePersonEvnAttribute' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события',
				'rules' => 'trim',
				'type' => 'id'
			),
		),
		'saveAttributeOnDate' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'PPersonSprTerrDop_id',
				'label' => 'Район города',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'BPersonSprTerrDop_id',
				'label' => 'Район города',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UPersonSprTerrDop_id',
				'label' => 'Район города',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvnClass_id',
				'label' => 'Идентификатор класса события',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnType',
				'label' => 'Тип атрибута',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'Date',
				'label' => 'Дата',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'Time',
				'label' => 'Время',
				'rules' => 'trim',
				'type' => 'time'
			),
			// остальные атрибуты
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'ДР',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'SocStatus_id',
				'label' => 'Социальный статус',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'FamilyStatus_id',
				'label' => 'Семейное положение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SNILS',
				'label' => 'СНИЛС',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'field' => 'PersonSex_id',
				'label' => 'Секс',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Federal_Num',
				'label' => 'Ед. номер полиса',
				'rules' => 'trim|is_numeric',
				'type' => 'string'
			),
			array(
				'field' => 'PersonRefuse_IsRefuse',
				'label' => 'Отказ от льготы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChildExist_IsChild',
				'label' => 'Есть дети до 16-ти',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCarExist_IsCar',
				'label' => 'Есть автомобиль',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonHeight_Height',
				'label' => '',
				'rules' => 'trim',
				'type' => 'float'
			),
			array(
				'default' => null,
				'field' => 'PersonWeight_Weight',
				'label' => '',
				'rules' => 'trim',
				'type' => 'float'
			),
			array(
				'default' => 37,
				'field' => 'Okei_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonSocCardNum_SocCardNum',
				'label' => 'Номер соц. карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonInn_Inn',
				'label' => 'ИНН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPhone_Phone',
				'label' => 'Телефон',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Post_id',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'OrgUnion_id',
				'label' => 'Подразделение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PKLCountry_id',
				'label' => 'Страна',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PKLRGN_id',
				'label' => 'Регион',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PKLSubRGN_id',
				'label' => 'Район',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PKLCity_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PKLTown_id',
				'label' => 'Нас. пункт',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PKLStreet_id',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PAddress_Zip',
				'label' => 'Индекс',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PAddress_House',
				'label' => 'Дом',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PAddress_Corpus',
				'label' => 'Корпус',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PAddress_Flat',
				'label' => 'Квартира',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PAddress_Address',
				'label' => 'Текст адреса',
				'rules' => 'trim',
				'type' => 'string'
			),
			/*array(
				'field' => 'PAddress_begDate',
				'label' => 'Дата начала проживания',
				'rules' => 'trim',
				'type' => 'date'
			),*/
			array(
				'field' => 'UKLCountry_id',
				'label' => 'Страна',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UKLRGN_id',
				'label' => 'Регион',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UKLSubRGN_id',
				'label' => 'Район',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UKLCity_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UKLTown_id',
				'label' => 'Нас. пункт',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UKLStreet_id',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'UAddress_Zip',
				'label' => 'Индекс',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UAddress_House',
				'label' => 'Дом',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UAddress_Corpus',
				'label' => 'Корпус',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UAddress_Flat',
				'label' => 'Квартира',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'UAddress_Address',
				'label' => 'Текст адреса',
				'rules' => 'trim',
				'type' => 'string'
			),
			/*array(
				'field' => 'UAddress_begDate',
				'label' => 'Дата регистрации',
				'rules' => 'trim',
				'type' => 'date'
			),*/
			array(
				'field' => 'DocumentType_id',
				'label' => 'Тип документа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'OrgDep_id',
				'label' => 'Кто выдал',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Document_Ser',
				'label' => 'Серия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Document_Num',
				'label' => 'Номер',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Document_begDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'KLCountry_id',
				'label' => 'Гражданство',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'NationalityStatus_IsTwoNation',
				'label' => 'Двойное гражданство',
				'rules' => 'trim',
				'type' => 'checkbox'
			),
			array(
				'field' => 'OMSSprTerr_id',
				'label' => 'Территория',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PolisType_id',
				'label' => 'Тип полиса',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSMO_id',
				'label' => 'Страховая организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия',
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
				'field' => 'Polis_Num',
				'label' => 'Номер',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_begDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ignoreOMSSprTerrDateCheck',
				'label' => 'пропустить проверку даты территории',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreСhecksumINN',
				'label' => 'пропустить проверку ИНН',
				'rules' => '',
				'type' => 'id'
			),

		),
		'checkPersonDoubles' => array(
			array(
					'field' => 'OMSSprTerr_id',
					'label' => 'Территория страхования',
					'rules' => '',
					'type' => 'id'
				),
			array(
					'default' => 0,
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim',
					'type' => 'id'
				),
			array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'trim|required',
					'type' => 'string'
				),
			array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'string'
				),
			array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				),
			array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'trim',
					'type' => 'date'
				),
			array(
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
					'field' => 'Polis_Num',
					'label' => 'Номер полиса',
					'rules' => 'trim',
					'type' => 'string'
				),
			array(
					'field' => 'Polis_Num',
					'label' => 'Номер полиса',
					'rules' => 'trim',
					'type' => 'string'
				),
			array(
					'field' => 'Federal_Num',
					'label' => 'Ед. номер',
					'rules' => 'trim',
					'type' => 'string'
				),
			array(
					'field' => 'Person_IsUnknown',
					'label' => 'признак Личность неизвестна',
					'rules' => 'trim',
					'type' => 'id'
				)
		),
		'checkSnilsDoubles' =>array(
			array(
				'field' => 'Person_SNILS',
				'label' => 'СНИЛС',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
		'verifyPersonSnils' =>array(
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор человека',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
		'getPersonSearchGrid' => array(

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
				'field' => 'getPersonWorkFields',
				'label' => 'Признак необходимости вытаскивать наименование организации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ParentARM',
				'label' => 'Тип арма, вызвавшего метод',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Персональный идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Double_ids',
				'label' => 'Идентификаторы двойников',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'showAll',
				'label' => 'Показывать всех',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isNotDead',
				'label' => 'isNotDead',
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
				'field' => 'personBirtDayFrom',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'personBirtDayTo',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonAge_AgeFrom',
				'label' => 'Возраст с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAge_AgeTo',
				'label' => 'Возраст по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				//'default' => 1800,
				'field' => 'PersonBirthYearFrom',
				'label' => 'Год рождения с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				//'default' => 3000,
				'field' => 'PersonBirthYearTo',
				'label' => 'Год рождения по',
				'rules' => 'trim',
				'type' => 'int'
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
				'field' => 'Person_Inn',
				'label' => 'ИНН',
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
			array(
				'default' => '',
				'field' => 'EvnPS_NumCard',
				'label' => 'Номер КВС',
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
				'field' => 'Year',
				'label' => 'Год включения в регистр',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Рабочий период заявки',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'LpuRegion_id',
                'label' => 'Участок',
                'rules' => '',
                'type' => 'id'
            ),
            array(
				'field' => 'PersonRefuse_IsRefuse',
				'label' => 'Отказ от льготы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array('field' => 'search_type','default' => 'full','label' => 'Тип поиска','rules' => 'trim','type' => 'string'),
			array('field' => 'oneQuery','label' => 'Отсутствие запроса count',	'rules' => 'trim','type' => 'string'),
			array('field' => 'checkForMainDB','label' => 'Проверка на основной базе', 'rules' => 'trim','type' => 'boolean','default' => false),
			array('field' => 'Person_ids','label' => 'Список Person_id ограничивающих поиск', 'rules' => 'trim','type' => 'string'),  //BOB - 21.03.2017
			array('field' => 'armMode', 'label' => 'АРМ вызова запроса', 'rules' => '', 'type' => 'string'),
			array('field' => 'getCountOnly', 'label' => '', 'rules' => '', 'type' => 'int'),
			/*
			array(
				'field' => 'DocumentType_id',
				'label' => 'Тип документа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Document_Ser',
				'label' => 'Серия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Document_Num',
				'label' => 'Номер',
				'rules' => 'trim',
				'type' => 'string'
			),*/
		),
		'getPersonCardGrid' => array(
			array(
				'field' => 'AttachLpu_id',
				'label' => 'ЛПУ прикрепления',
				'rules' => 'trim',
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
				'field' => 'PersonCard_IsAttachCondit',
				'label' => 'Условное прикрепление',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_IsActualPolis',
				'label' => 'Есть действующий полис',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'PersonAge_From',
				'label' => 'Возраст с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => 200,
				'field' => 'PersonAge_To',
				'label' => 'Возраст по',
				'rules' => 'trim',
				'type' => 'int'
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
				'field' => 'Person_Inn',
				'label' => 'ИНН',
				'rules' => 'trim|is_numeric',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PersonCard_Code',
				'label' => 'Код карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Refuse_id',
				'label' => 'Отказник',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseNextYear_id',
				'label' => 'Отказ на след. год',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => 'all',
				'field' => 'searchMode',
				'label' => 'Режим поиска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array('field' => 'dontShowUnknowns', 'label' => '', 'rules' => '', 'type' => 'int'),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'LpuRegionType_id',
				'label' => 'Тип участка',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'LpuRegion_id',
				'label' => 'Участок',
				'rules' => 'trim',
				'type' => 'int'
			),
            array(
                'default' => null,
                'field' => 'LpuRegion_Fapid',
                'label' => 'Участок',
                'rules' => 'trim',
                'type' => 'int'
            ),
			// Льгота
			array(
				'field' => 'RegisterSelector_id',
				'label' => 'Регистр льготников',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_begDate',
				'label' => 'Прикреплен',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonCard_endDate',
				'label' => 'Откреплен',
				'rules' => 'trim',
				'type' => 'daterange'
			),

			// Адрес
			array(
				'field' => 'Address_House',
				'label' => 'Номер дома ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Address_Corpus',
				'label' => 'Корпус ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'default' => 0,
				'field' => 'AddressStateType_id',
				'label' => 'Тип адреса ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLAreaType_id',
				'label' => 'Тип населенного пункта ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLCity_id',
				'label' => 'Город ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KLCountry_id',
				'label' => 'Страна ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLRgn_id',
				'label' => 'Регион ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLStreet_id',
				'label' => 'Улица ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLSubRgn_id',
				'label' => 'Район ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLTown_id',
				'label' => 'Населенный пункт ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_NoAddress',
				'label' => 'Без адреса ("Адрес")',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'PersonCardStateType_id',
				'label' => 'Актуальность прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getPersonGrid' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Birthday', 'label' => 'Дата рождения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Address_Street', 'label' => 'Улица', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PersonCard_Code', 'label' => 'Номер амбулаторной карты', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Inn', 'label' => 'ИИН', 'rules' => 'trim|is_numeric', 'type' => 'string'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'trim', 'type' => 'string'),
			array(
				'field' => 'PolisFormType_id',
				'label' => 'Форма полиса',
				'rules' => 'trim',
				'type' => 'id'
			),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Code', 'label' => 'Ед. номер', 'rules' => 'trim|is_numeric', 'type' => 'string'),
			array(
				'field' => 'PartMatchSearch',
				'label' => 'Поиск по частичному совпадению',
				'rules' => '',
				'type' => 'checkbox'
			),
			array('field' => 'dontShowUnknowns', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'type' => 'int')
		),
		'getPersonGridPersonCardAuto' => array(
			//array( 'field' => 'PSPCALpu_id',            'label' => 'ЛПУ прикрепления',  'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PSPCAOrg_id',            'label' => 'Организация (ЛПУ) прикрепления',  'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PSPCALpuRegionType_id',  'label' => 'Тип участка',       'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PSPCALpuRegion_id',      'label' => 'Участок',           'rules' => 'trim', 'type' => 'int', 'default' => null),
            array( 'field' => 'LpuRegion_Fapid',        'label' => 'ФАП участок',       'rules' => 'trim', 'type' => 'int', 'default' => null),
			array( 'field' => 'Sex_id',                 'label' =>'Пол',                'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PersonAge_Min',          'label' => 'Возраст с',         'rules' => 'trim', 'type' => 'int'),
			array( 'field' => 'PersonAge_Max',          'label' => 'Возраст по',        'rules' => 'trim', 'type' => 'int'),

			array( 'field' => 'PKLCountry_id',      'label' => 'Адрес проживания(страна)',      'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PKLRGN_id',          'label' => 'Адрес проживания(регион)',      'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PKLSubRGN_id',       'label' => 'Адрес проживания(район)',       'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PKLCity_id',         'label' => 'Адрес проживания(город)',       'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PKLTown_id',         'label' => 'Адрес проживания(нас.пункт)',   'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PKLStreet_id',       'label' => 'Адрес проживания(улица)',       'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'PAddress_House',     'label' => 'Адрес проживания(дом)',         'rules' => 'trim', 'type' => 'string'),
			array( 'field' => 'PAddress_Corpus',     'label' => 'Адрес проживания(корпус)',     'rules' => 'trim', 'type' => 'string'),
			array( 'field' => 'PAddress_Flat',     'label' => 'Адрес проживания(квартира)',     'rules' => 'trim', 'type' => 'string'),

			array( 'field' => 'UKLCountry_id',      'label' => 'Адрес регистрации(страна)',     'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'UKLRGN_id',          'label' => 'Адрес регистрации(регион)',     'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'UKLSubRGN_id',       'label' => 'Адрес регистрации(район)',      'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'UKLCity_id',         'label' => 'Адрес регистрации(город)',      'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'UKLTown_id',         'label' => 'Адрес регистрации(нас.пункт)',  'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'UKLStreet_id',       'label' => 'Адрес регистрации(улица)',      'rules' => 'trim', 'type' => 'id'),
			array( 'field' => 'UAddress_House',     'label' => 'Адрес регистрации(дом)',        'rules' => 'trim', 'type' => 'string'),
			array( 'field' => 'UAddress_Corpus',     'label' => 'Адрес проживания(корпус)',     'rules' => 'trim', 'type' => 'string'),
			array( 'field' => 'UAddress_Flat',     'label' => 'Адрес проживания(корпус)',     'rules' => 'trim', 'type' => 'string'),
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
			)
		),
		'exportPersonCardForIdentification' => array(
			array(
				'field' => 'AttachLpu_id',
				'label' => 'ЛПУ прикрепления',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_IsAttachCondit',
				'label' => 'Условное прикрепление',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_IsActualPolis',
				'label' => 'Есть действующий полис',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'ban_percent|trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'PersonAge_From',
				'label' => 'Возраст с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => 200,
				'field' => 'PersonAge_To',
				'label' => 'Возраст по',
				'rules' => 'trim',
				'type' => 'int'
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
				'field' => 'PersonCard_Code',
				'label' => 'Код карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Refuse_id',
				'label' => 'Отказник',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseNextYear_id',
				'label' => 'Отказ на след. год',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => 'all',
				'field' => 'searchMode',
				'label' => 'Режим поиска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'LpuRegionType_id',
				'label' => 'Тип участка',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'LpuRegion_id',
				'label' => 'Участок',
				'rules' => 'trim',
				'type' => 'int'
			),

			// Льгота
			array(
				'field' => 'RegisterSelector_id',
				'label' => 'Регистр льготников',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_begDate',
				'label' => 'Прикреплен',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonCard_endDate',
				'label' => 'Откреплен',
				'rules' => 'trim',
				'type' => 'daterange'
			),

			// Адрес
			array(
				'field' => 'Address_House',
				'label' => 'Номер дома ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'default' => 0,
				'field' => 'AddressStateType_id',
				'label' => 'Тип адреса ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLAreaType_id',
				'label' => 'Тип населенного пункта ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLCity_id',
				'label' => 'Город ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLCountry_id',
				'label' => 'Страна ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLRgn_id',
				'label' => 'Регион ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLStreet_id',
				'label' => 'Улица ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLSubRgn_id',
				'label' => 'Район ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLTown_id',
				'label' => 'Населенный пункт ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_NoAddress',
				'label' => 'Без адреса ("Адрес")',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'PersonCardStateType_id',
				'label' => 'Актуальность прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getAllPeriodics' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim|required',
				'type' => 'int'
			)
		),
		'loadPersonEval' => array(
			array(
				'field'=>'Person_id',
				'label'=>'Идентификатор человека',
				'rules'=>'required',
				'type'=>'id'
			)

		),
		'loadPolisData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор полиса',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim',
				'type' => 'int'
			)
		),
		'loadDocumentData' => array(
			array(
				'field' => 'Document_id',
				'label' => 'Идентификатор документа',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'loadNationalityStatusData' => array(
			array(
				'field' => 'NationalityStatus_id',
				'label' => 'Идентификатор гражданства человека',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'loadJobData' => array(
			array(
				'field' => 'Job_id',
				'label' => 'Идентификатор работы',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'getPersonEvalEditWindow' => array(
			array(
				'field' => 'PersonEval_id',
				'label' => 'Идентификатор работы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvalType',
				'label' => 'Идентификатор работы',
				'rules' => '',
				'type' => 'string'
			)
		),
		'savePersonEditWindow' => array(
			array(
				'field' => 'rz',
				'label' => 'RZ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field'=>'Person_IsInErz',
				'label'=>'Признак включения человека в ЦС ЕРЗ',
				'rules'=>'trim',
				'type'=>'id'
			),
			array(
				'field'=>'BDZ_id',
				'label'=>'BDZ_id',
				'rules'=>'trim',
				'type'=>'id'
			),
			array(
				'default' => null,
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Polis_CanAdded',
				'label' => 'Признак того, что разрешено добавлять полис',
				'rules' => 'trim',
				'type' => 'int'
			),
			
			array(
				'default' => null,
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'missSocStatus',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'Server_pid',
				'label' => 'Идентификатор сервера создавшего запись о человеке',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'PersonNationality_id',
				'label' => 'Гражданство',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Person_identDT',
				'label' => 'Дата и время актуальности данных идентификации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonIdentState_id',
				'label' => 'Статус идентификации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 'add',
				'field' => 'mode',
				'label' => 'Режим сохранения',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'oldValues',
				'label' => 'Старые данные',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim|uppercase',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim|uppercase',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim|uppercase',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PersonSocCardNum_SocCardNum',
				'label' => 'Номер социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PersonRefuse_IsRefuse',
				'label' => 'Отказ от льготы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChildExist_IsChild',
				'label' => 'Есть дети до 16-ти',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonHeight_Height',
				'label' => '',
				'rules' => 'trim',
				'type' => 'float'
			),
			array(
				'default' => null,
				'field' => 'PersonWeight_Weight',
				'label' => '',
				'rules' => 'trim',
				'type' => 'float'
			),
			array(
				'default' => 37,
				'field' => 'Okei_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCarExist_IsCar',
				'label' => 'Есть автомобиль',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'PersonPhone_Phone',
				'label' => 'Номер телефона',
				'rules' => 'trim',
				'type' => 'string'
			),
			/*array(
				'default' => '',
				'field' => 'PersonPhone_Comment',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),*/
			array(
				'default' => '',
				'field' => 'Person_Comment',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PersonInn_Inn',
				'label' => 'INN',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'Post_id',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'OrgUnion_id',
				'label' => 'Подразделение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'PostNew',
				'label' => 'Новая должность',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'OrgUnionNew',
				'label' => 'Новое подразделение',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Person_SNILS',
				'label' => 'СНИЛС',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'field' => 'PersonSex_id',
				'label' => 'Пол',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'SocStatus_id',
				'label' => 'Социальный статус',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'FamilyStatus_id',
				'label' => 'Семейное положение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonFamilyStatus_IsMarried',
				'label' => 'Состоит в зарегистрированном браке',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Federal_Num',
				'label' => 'Единый номер',
				'rules' => 'trim|is_numeric',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Federal_begDate',
				'label' => 'Дата начала единого номера',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => null,
				'field' => 'OMSSprTerr_id',
				'label' => 'Территория страхования',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'OrgUnion_id',
				'label' => 'Подразделение',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PolisType_id',
				'label' => 'Тип полиса',
				'rules' => 'trim',
				'type' => 'id'
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
				'default' => null,
				'field' => 'OrgSMO_id',
				'label' => 'Страховая организация',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Polis_begDate',
				'label' => 'Начало полиса',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => '',
				'field' => 'Polis_endDate',
				'label' => 'Окончание полиса',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => null,
				'field' => 'DocumentType_id',
				'label' => 'Тип документа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'OrgDep_id',
				'label' => 'Организация выдавшая документ',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Document_Ser',
				'label' => 'Серия документа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Document_Num',
				'label' => 'Номер документа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'Document_begDate',
				'label' => 'Дата выдачи документа',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => null,
				'field' => 'KLCountry_id',
				'label' => 'Гражданство',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'NationalityStatus_IsTwoNation',
				'label' => 'Двойное гражданство',
				'rules' => 'trim',
				'type' => 'checkbox'
			),
			array(
				'default' => null,
				'field' => 'NationalityStatus_IsTwoNation',
				'label' => 'Двойное гражданство',
				'rules' => 'trim',
				'type' => 'checkbox'
			),
			array(
				'default' => null,
				'field' => 'LegalStatusVZN_id',
				'label' => 'Правовой статус нерезидента',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'UKLCountry_id',
				'label' => 'Страна',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'UKLRGN_id',
				'label' => 'Регион',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'UKLSubRGN_id',
				'label' => 'Район',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'UKLCity_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'UPersonSprTerrDop_id',
				'label' => 'Район города',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'UAddressSpecObject_Value',
				'label' => 'Г',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'UAddressSpecObject_id',
				'label' => 'Р',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PAddressSpecObject_Value',
				'label' => 'Г',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PAddressSpecObject_id',
				'label' => 'Р',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'BAddressSpecObject_Value',
				'label' => 'Г',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'BAddressSpecObject_id',
				'label' => 'Р',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'UKLTown_id',
				'label' => 'Населенный пункт',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'UKLStreet_id',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'UAddress_House',
				'label' => 'Номер дома',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'UAddress_Corpus',
				'label' => 'Номер корпуса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'UAddress_Flat',
				'label' => 'Номер квартиры',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'UAddress_Zip',
				'label' => 'Индекс',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'UAddress_Address',
				'label' => 'Текстовое описание адреса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'UAddress_AddressText',
				'label' => 'Текстовое описание адреса',
				'rules' => 'trim',
				'type' => 'string'
			),
			/*array(
				'field' => 'UAddress_begDate',
				'label' => 'Дата регистрации',
				'rules' => 'trim',
				'type' => 'date'
			),*/
			array(
				'field' => 'BDZ_Guid',
				'label' => 'Идентификатор БДЗ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Guid',
				'label' => 'Идентификатор полиса БДЗ',
				'rules' => 'trim',
				'type' => 'string'
			),

			/* Начало входныхъ параметров адреса рождения */

			array(
				'default' => null,
				'field' => 'BKLCountry_id',
				'label' => 'Страна',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'BKLRGN_id',
				'label' => 'Регион',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'BKLSubRGN_id',
				'label' => 'Район',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'BKLCity_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'BPersonSprTerrDop_id',
				'label' => 'Район города',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'BKLTown_id',
				'label' => 'Населенный пункт',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'BKLStreet_id',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'BAddress_House',
				'label' => 'Номер дома',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'BAddress_Corpus',
				'label' => 'Номер корпуса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'BAddress_Flat',
				'label' => 'Номер квартиры',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'BAddress_Zip',
				'label' => 'Индекс',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'BAddress_Address',
				'label' => 'Текстовое описание адреса',
				'rules' => 'trim',
				'type' => 'string'
			),

				/* конец входныхъ параметров адреса рождения */

			array(
				'default' => null,
				'field' => 'PKLCountry_id',
				'label' => 'Страна',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PKLRGN_id',
				'label' => 'Регион',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PKLSubRGN_id',
				'label' => 'Район',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PKLCity_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PPersonSprTerrDop_id',
				'label' => 'Город',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PKLTown_id',
				'label' => 'Населенный пункт',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PKLStreet_id',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PAddress_House',
				'label' => 'Номер дома',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PAddress_Corpus',
				'label' => 'Номер корпуса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PAddress_Flat',
				'label' => 'Номер квартиры',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PAddress_Zip',
				'label' => 'Индекс',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'PAddress_Address',
				'label' => 'Текстовое описание адреса',
				'rules' => 'trim',
				'type' => 'string'
			),
			/*array(
				'field' => 'PAddress_begDate',
				'label' => 'Дата начала проживания',
				'rules' => 'trim',
				'type' => 'date'
			),*/
			array(
				'default' => null,
				'field' => 'DeputyKind_id',
				'label' => 'Статус представителя',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DeputyPerson_id',
				'label' => 'Представитель',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DocumentAuthority_id',
				'label' => 'Тип документа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DocumentDeputy_Ser',
				'label' => 'Серия',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'DocumentDeputy_Num',
				'label' => 'Номер',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'DocumentDeputy_Issue',
				'label' => 'Кем выдан',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'DocumentDeputy_begDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => null,
				'field' => 'FeedingType_id',
				'label' => 'Способ вскармливания',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_CountChild',
				'label' => 'Который по счету',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'default' => null,
				'field' => 'HealthAbnormVital_id',
				'label' => 'Нарушение жизнедеятельности',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'HealthAbnorm_id',
				'label' => 'Причины нарушения здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'HeightAbnormType_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonSprTerrDop_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsInvalid',
				'label' => 'Инвалидность',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'InvalidKind_id',
				'label' => 'Категория инвалидности',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsBad',
				'label' => 'Неблагополучная',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsYoungMother',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonHeight_IsAbnorm',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsIncomplete',
				'label' => 'Неполная',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsManyChild',
				'label' => 'Многодетная семья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsMigrant',
				'label' => 'Вынужденные переселенцы',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_IsTutor',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonWeight_IsAbnorm',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PersonChild_invDate',
				'label' => '',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => null,
				'field' => 'ResidPlace_id',
				'label' => 'Место воспитания',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'WeightAbnormType_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Diag_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoOccupationClass_id',
				'label' => 'Социально-профессиональная группа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Ethnos_id',
				'label' => 'Этническая группа',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Person_IsNotINN',
				'label' => 'Отказ от получения ИНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Person_IsUnknown',
				'label' => 'Неизвестный человек',
				'rules' => 'trim',
				'type' => 'checkbox'
			),
			array(
				'default' => 0,
				'field' => 'Person_IsAnonym',
				'label' => 'Анонимный человек',
				'rules' => 'trim',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Person_deadDT',
				'label' => 'Дата смерти',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'ignoreOMSSprTerrDateCheck',
				'label' => 'пропустить проверку даты территории',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreСhecksumINN',
				'label' => 'пропустить проверку ИНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreOMSSprTerrPolis',
				'label' => 'пропустить проверку переданной территории и существования полиса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEduLevel_id',
				'label' => 'Уровень образования id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EducationLevel_id',
				'label' => 'Уровень образования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEmployment_id',
				'label' => 'Занятость id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Employment_id',
				'label' => 'Занятость',
				'rules' => '',
				'type' => 'id'
			)
		),
		'exportPersonPolisToXml' => array(
			array(
				'field' => 'AttachLpu_id',
				'label' => 'МО прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPolis_ExportIndex',
				'label' => 'Порядковый номер выгрузки',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPolis_Date',
				'label' => 'Выгрузка на дату',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		'revivePerson' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'extendPersonHistory' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonAnonymCode' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getPersonAnonymData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadPersonRequestDataGrid' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
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
			)
		),
		'addPersonRequestData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required|trim',
				'type' => 'id'
			),
			array(
				'field' => 'Person_identDT',
				'label' => 'Идентифицировать на дату',
				'rules' => 'required',
				'type' => 'datetime'
			),
			array(
				'field' => 'PersonRequestSourceType_id',
				'label' => 'Источник запроса на идентификацию',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'fromClient',
				'label' => 'Данные передаются с клиента',
				'rules' => '',
				'type' => 'checkbox',
			),
			array(
				'field' => 'identImmediately',
				'label' => 'Идентифицировать сразу',
				'rules' => '',
				'type' => 'checkbox',
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Sex',
				'label' => 'Идентфикатор пола',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'День рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Person_ENP',
				'label' => 'Ед. номер',
				'rules' => 'trim|is_numeric',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Snils',
				'label' => 'СНИЛС',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'field' => 'DocumType_Code',
				'label' => 'Код типа документа',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Docum_Ser',
				'label' => 'Серия документа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Docum_Num',
				'label' => 'Номер документа',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PolisType_id',
				'label' => 'Тип полиса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		'savePersonLpuInfo' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required|trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonLpuInfo_IsAgree',
				'label' => 'Тип документа (Согласие/Отзыв согласия)',
				'rules' => '',
				'type' => 'int'
			)
		),
		'saveElectroReceptInfo' => array(
			array( 'field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required|trim', 'type' => 'id'),
			array( 'field' => 'ReceptElectronic_id', 'label' => 'Идентификатор отказа на оформление рецепта в форме электронного документа', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Refuse', 'label' => 'Признак отказа', 'rules' => '', 'type' => 'int')
		),
		'loadPersonLpuInfoList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required|trim',
				'type' => 'id'
			)
		),
		'savePersonWork' => array(
			array('field' => 'PersonWork_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgStruct_id', 'label' => 'Идентификатор структурного уровня организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonWork_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'PersonWork_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'pmUserCacheOrg_id', 'label' => 'Идентификатор учетной записи', 'rules' => '', 'type' => 'id'),
		),
		'deletePersonWork' => array(
			array('field' => 'PersonWork_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id'),
		),
		'loadPersonWorkForm' => array(
			array('field' => 'PersonWork_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id'),
		),
		'loadPersonWorkList' => array(
			array('field' => 'PersonWork_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
            array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
		),
		'loadPersonWorkGrid' => array(
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'OrgStruct_id', 'label' => 'Идентификатор структурного уровня организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
		),
		'checkPersonPhoneStatus' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'),
		),
		'sendPersonPhoneVerificationCode' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonPhone_Phone', 'label' => 'Номер телефона', 'rules' => 'required|trim', 'type' => 'string'),
		),
		'addPersonPhoneHist' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonPhone_Phone', 'label' => 'Номер телефона', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PersonPhoneStatus_id', 'label' => 'Идентификатор статуса номера телефона', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonPhoneFailCause_id', 'label' => 'Идентификатор причины отмены номера телефона', 'rules' => '', 'type' => 'id'),
		),
		'checkEvnZNO_last' => array (
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type'=>'id'),
			array('field' => 'object', 'label' => 'Объект', 'rules' => 'required', 'type'=>'string'),
		),
		'changeEvnZNO' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'isZNO', 'label' => 'Признак ЗНО', 'rules' => 'required', 'type' => 'id'),
			//~ array('field' => 'isZNOremove', 'label' => 'Признак снятия ЗНО', 'rules' => '', 'type' => 'id'),
			array('field' => 'object', 'label' => 'Объект', 'rules' => 'required', 'type'=>'string'),
			
		),
		'getEvnBiopsyDate' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			//~ array('field' => 'object', 'label' => 'Объект', 'rules' => 'required', 'type'=>'string'),
		),
		'isReceptElectronicStatus' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
		),
		'deletePersonHeight' => array(
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonHeightEditForm' => array(
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonHeightPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPersonHeightGrid' => array(
			array(
				'default' => 'all',
				'field' => 'mode',
				'label' => 'Режим',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'HeightMeasureType_id',
				'label' => 'Тип измерения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePersonHeight' => array(
			array(
				'field' => 'HeightAbnormType_id',
				'label' => 'Тип отклонения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HeightMeasureType_id',
				'label' => 'Вид замера',
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
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_Height',
				'label' => 'Рост',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_IsAbnorm',
				'label' => 'Отклонение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_setDate',
				'label' => 'Дата измерения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'saveAnthropometryData' => array(
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasureType_id',
				'label' => 'Вид замера',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonMeasure_setDate',
				'label' => 'Дата измерения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonHeight_Height',
				'label' => 'Рост',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_IsAbnorm',
				'label' => 'Рост отклонение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HeightAbnormType_id',
				'label' => 'Рост тип отклонения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeight_Weight',
				'label' => 'Вес',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonWeight_id',
				'label' => 'Идентификатор измерения веса пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Ед. измерения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeigh_IsAbnorm',
				'label' => 'Вес отклонение',
				'rules' => '',
				'type' => 'id'
			),array(
				'default' => null,
				'field' => 'WeightAbnormType_id',
				'label' => 'Вес тип отклонения',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'getDeviationHeight' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_Height',
				'label' => 'Рост',
				'rules' => 'required',
				'type' => 'float'
			),
		),
		'getDeviationWeight' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeight_Weight',
				'label' => 'Вес',
				'rules' => 'required',
				'type' => 'float'
			),
		),
		'removeAnthropometryData' => array(
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasureType_id',
				'label' => 'Вид замера',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonMeasure_setDate',
				'label' => 'Дата измерения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonHeight_Height',
				'label' => 'Рост',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonHeight_id',
				'label' => 'Идентификатор измерения роста пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonHeight_IsAbnorm',
				'label' => 'Рост отклонение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HeightAbnormType_id',
				'label' => 'Рост тип отклонения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeight_Weight',
				'label' => 'Вес',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PersonWeight_id',
				'label' => 'Идентификатор измерения веса пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Okei_id',
				'label' => 'Ед. измерения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonWeigh_IsAbnorm',
				'label' => 'Вес отклонение',
				'rules' => '',
				'type' => 'id'
			),array(
				'default' => null,
				'field' => 'WeightAbnormType_id',
				'label' => 'Вес тип отклонения',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'getAnthropometryPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			), array(
				'field' => 'limiter',
				'label' => 'Ограничение',
				'type' => 'float',
				'default' => null
			)
		),
		'getPersonDeputy' => [
			[
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			]
		]
	);

	private $moduleMethods = [
		'getPersonSearchGrid'
	];

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * @param array $rules1
		 * @param array $rules2
		 * @return array
		 */
		function mergeInputRules($rules1, $rules2) {
			$fields1 = array_map(function($rule){return $rule['field'];}, $rules1);

			foreach ($rules2 as $rule) {
				if (!in_array($rule['field'], $fields1)) {
					$rules1[] = $rule;
				}
			}
			return $rules1;
		}

		$this->inputRules['editPersonEvnAttributeNew'] = mergeInputRules(
			$this->inputRules['editPersonEvnAttributeNew'],
			$this->inputRules['savePersonEditWindow']
		);

		$this->init();
	}

	/**
	 * Дополнительная инициализация
	 */
	protected function init(){
		$armMode = isset($_REQUEST['armMode']) ? $_REQUEST['armMode'] : null;
		$method = $this->router->fetch_method();

		//PROMEDWEB-3712 если заполнен номер карты КВС,
		//то придется искать в основной БД, т.к. нужных таблиц в БД ЛИС нет
		if (!empty($_REQUEST['EvnPS_NumCard']) || !$this->usePostgreLis || !in_array($method, $this->moduleMethods) || $armMode !== 'LIS') {
			$this->load->database();
			$this->load->model("Person_model", "dbmodel");
		}
	}
	
	/**
	 * Проверка дат полиса на пересечение с другими полисами человека
	 */
	function checkPolisIntersection($data, $outputError = false)
	{
		if ($this->dbmodel->checkPolisIntersection($data,true)) {
			return true;
		} else {
			if ( $outputError === true ) {
				$this->ReturnError('Периоды полисов не могут пересекаться.');
			}
			return false;
		}
	}
	
	/**
	 * Проверка активности территории полиса
	 */
	function checkOMSSprTerrDate($data)
	{
		if ((isset($data['ignoreOMSSprTerrDateCheck']) && $data['ignoreOMSSprTerrDateCheck'] == 2) || $this->dbmodel->checkOMSSprTerrDate($data,true)) {
			return true;
		} else {
			$this->ReturnData(array(
				'Error_Msg' => '', 
				'Alert_Msg' => toUTF('Период действия полиса выходит за границы периода действия Территории страхования. Продолжить сохранение?'), 
				'Alert_Code' => 1, 
				'success' => true
			));
			return false;
		}
	}

	/**
	 * Проверка контрольной суммы ИНН
	 */
	function checksumINN($data)
	{
		if (getRegionNick() == 'kz') {
			return true; // в КЗ нет ИНН, есть только ИИН и для него эта проверка не подходит
		}
		if (empty($data['PersonInn_Inn']) || ((isset($data['ignoreСhecksumINN']) && $data['ignoreСhecksumINN'] == 2))) {
			return true;
		} else {
			$this->load->model("Options_model", "opmodel");
			$globalOptions = $this->opmodel->getOptionsGlobals($data);
			if( isset($globalOptions['globals']['inn_correctness_control']) && $globalOptions['globals']['inn_correctness_control']>1 ){
				if(CheckInn($data['PersonInn_Inn']) == false){
					if($globalOptions['globals']['inn_correctness_control'] == 2){
						$this->ReturnData(array(
							'Error_Msg' => '',
							'Alert_Msg' => toUTF('Ошибка проверки контрольной суммы в ИНН. Убедитесь, что ИНН указан верно. Продолжить?'),
							'ignoreСhecksumINN' => 2,
							'success' => true
						));
						return false;
					}elseif($globalOptions['globals']['inn_correctness_control'] == 3){
						$this->ReturnError('ИНН введен с ошибкой. Проверьте корректность введенных данных.');
						return false;
					}
				}
			}
		}
		return true;
	}

	/*
	 * Проверка переданной территории и полиса в активном состоянии человека
	 */
	function checkOMSSprTerrPolis($data){
		$policy = $this->dbmodel->getLastPeriodicalsByPolicy(array('Person_id' => $data['Person_id']));
		if (!empty($data['ignoreOMSSprTerrPolis']) && $data['ignoreOMSSprTerrPolis'] == 2) {
			return true;
		}
		if(empty($data['OMSSprTerr_id']) && !empty($policy['Polis_id']) && empty($policy['Polis_endDate'])) {
			// #174743. если в блоке полей «Полис» поле «Территория» не заполнено и в активном состоянии человека указан полис
			$this->ReturnData(array(
				'Error_Msg' => '',
				'Alert_Msg' => toUTF('При отсутствии данных полис пациента будет закрыт автоматически. Продолжить сохранение?'),
				'ignoreOMSSprTerrPolis' => 2,
				'success' => true
			));
			return false;		
		}else{
			return true;
		}
	}
	
	/**
	 * Проверка единого номера полиса на уникальность
	 */
	function checkFederalNumUnique($data, $success = false, $Cancel_Error_Handle = false)
	{
		if ($this->dbmodel->checkFederalNumUnique($data)) {
			return true;
		} else {
			$returnData = array(
				'success' => $success,
				'Error_Code' => toUTF('checkFederalNumUnique'),
				'Error_Msg' => toUTF('Такой номер полиса уже существует в базе.')
			);
			if ($Cancel_Error_Handle) {
				$returnData['Cancel_Error_Handle'] = true;
			}
			$this->ReturnData($returnData);
			return false;
		}
	}
	
	/**
	 * Сохранение периодики человека на дату
	 */
	function saveAttributeOnDate()
	{
		$data = $this->ProcessInputData('saveAttributeOnDate', true, true, true);
		if ($data === false) { return false; }
		
		// если сохраняется полис то проверить что его срок действия не пересекается с другими.
		$evn_types = explode('|', $data['EvnType']);
		for ( $i = 0; $i < count($evn_types); $i++ )
		{
			switch( $evn_types[$i] )
			{
				case 'Polis':
					if (!$this->checkOMSSprTerrDate($data)){
						return false;
					}	
					if (!$this->checkPolisIntersection($data)){
						return false;
					}
					if (!$this->checkFederalNumUnique($data)){
						return false;
					}
					break;
				case 'PersonInn_Inn':
					if($data['session']['region']['nick'] != 'kz'){
						if(!$this->checksumINN($data)){
							return false;
						}
					}
					break;
			}
		}
		
		$response = $this->dbmodel->saveAttributeOnDate($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении атрибута! Обратитесь к разработчикам.')->ReturnData();
		return true;
	}
	
	
	/**
	 * Удаление периодики человека
	 */
	function deletePersonEvnAttribute() {
		$data = $this->ProcessInputData('deletePersonEvnAttribute', true, false, true);
		if ($data === false) {
			return false;
		}
		if (isset($_POST['EvnArr'])) {
			ConvertFromWin1251ToUTF8($_POST['EvnArr']);
			$EvnArr = json_decode($_POST['EvnArr'], true);
			$resp = true;
			foreach ($EvnArr as $val) {
				$val['pmUser_id']=$data['pmUser_id'];
				if($resp){
					$response = $this->dbmodel->deletePersonEvnAttribute($val);
					if((isset($response[0]['Error_Msg']) && $response[0]['Error_Msg']!=null)||!$response){
						$resp=false;
					}
				}else{
					break;
				}
				
			}
		} else {
			return false;
			
		}
		if($data['Person_id']>0){
			$hist = $this->dbmodel->extendPersonHistory($data);
			if(!$hist['success']){
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при удалении атрибута! Обратитесь к разработчикам.'),
						'Cancel_Error_Handle' => true));

			}
		}
		$this->ProcessModelSave($response, true, 'Ошибка БД при удалении данных')->ReturnData();
	}

	/**
	 * Перенос даты действия периодики
	 */
	function editPersonEvnDate()
	{
		$data = $this->ProcessInputData('editPersonEvnDate', true, false, true);
		if ($data === false) { return false; }

		$resp = $this->dbmodel->getPersonEvnAndPolisData($data);
		if(is_array($resp) && count($resp)>0){
			if (!empty($resp['Error_Msg'])){
				$this->ReturnData(array('success'=>false, 'Error_Msg' => $resp['Error_Msg'],
				'Cancel_Error_Handle' => true));
				return false;
			}
			if (!$this->checkPolisIntersection($resp[0])){
				$this->ReturnData(array('success'=>false, 'Error_Msg' => 'Периоды полисов не могут пересекаться',
				'Cancel_Error_Handle' => true));
				return false;
			}
		}
		
		$response = $this->dbmodel->editPersonEvnDate($data);
		if ( $response === true )
			$this->ReturnData(array('success'=>true));
		else
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Ошибка при сохранении даты! Обратитесь к разработчикам.'),
				'Cancel_Error_Handle' => true));
	}
	
	
	/**
	 * Добавление новой периодики человека
	 */
	function editPersonEvnAttributeNew()
	{
		$data = $this->ProcessInputData('editPersonEvnAttributeNew', true, false, true);
		if ($data === false) { return false; }

		if (empty($data['EvnType']) && empty($data['NotEvnType'])) {
			$this->ReturnError('Не заполнен список атрибутов для изменения.');
			return false;
		}

		if($data['EvnType'] == 'PersonPhone_Phone') {
			if(! empty($data['EvnType'])) {// до этого можно было прислать телефон с буквами// #137611
				$data['PersonPhone_Phone'] = substr(preg_replace('~\D+~', '', trim((string)$data['PersonPhone_Phone'])), -10);
			}
		}

		// если сохраняется полис то проверить что его срок действия не пересекается с другими.
		$evn_types = explode('|', $data['EvnType']);
		for ( $i = 0; $i < count($evn_types); $i++ )
		{
			switch( $evn_types[$i] )
			{
				case 'Polis':
					//$data['PersonIdentState_id']=1;
					/*if (!$this->checkPolisIntersection($data)){
						return array('Error_Msg' => 'Периоды полисов не могут пересекаться');
					}*/
					if (!$this->dbmodel->checkPolisIntersection($data)) {
						$this->ReturnError('Периоды полисов не могут пересекаться.');
						return false;
					}
				break;
				case 'PersonInn_Inn':
					if($data['session']['region']['nick'] != 'kz'){
						$this->load->model("Options_model", "opmodel");
						$globalOptions = $this->opmodel->getOptionsGlobals($data);
						if( isset($globalOptions['globals']['inn_correctness_control']) && $globalOptions['globals']['inn_correctness_control'] == 3 ){
							if (CheckInn($data['PersonInn_Inn']) == false) {
								$this->ReturnError('ИНН введен с ошибкой. Проверьте корректность введенных данных.');
								return false;
							}
						}
					}
					break;
			}
		}

		if (!$this->checkFederalNumUnique($data, true, true)) {
			return false;
		}

		if ( !isSuperadmin() )
		{
			if ( !isset($data['refresh']) )
			{
				$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Для получения доступа к этому функционалу необходимо перезайти в систему.'), 'Cancel_Error_Handle' => true));
				return false;
			}
		}
		
		$response = $this->dbmodel->editPersonEvnAttributeNew($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 *
	 * @return type 
	 */
	function loadPersonEval(){
		$data = $this->ProcessInputData('loadPersonEval', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadPersonEval($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * @comment
	 */
	function checkChildrenDuplicates(){
		$data = $this->ProcessInputData('checkChildrenDuplicates', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkChildrenDuplicates($data);
		$resp=array();
		if ($response)
		{
			if(isset($response['warning'])){
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				$resp= array(
					array(
						'success'=>false,
						'child'=>$response
					)
				);
			}else{
				$resp= array(
					array(
						'success'=>true
						)
					);
			}
		}
		$this->ProcessModelList($resp, true, true)->ReturnData();
	}
	/**
	 * Проверка на двойника при редактировании человека
	 */
	function checkPersonDoubles()
	{
		set_time_limit(0);
		if ( ! $this->config->item('CHECK_PERSON_DOUBLES') ) {
			// Отключаем проверку на двойников, то есть всегда возвращаем что двойника нет
			$val = array(
				"success" => true,
				"Cancel_Error_Handle" => true
			);
			$this->ReturnData($val);
			return;
		}
		
		$data = $this->ProcessInputData('checkPersonDoubles', true, false, true);
		if ($data === false) { return false; }
		
		$val = array(
			"success" => true,
		);
		
		/*
		 * Проверяем ИНН на контрольный разряд
		 *
		 * Для Казахстана не надо, ибо https://redmine.swan.perm.ru/issues/31664
		 */
		if ( $data['session']['region']['nick'] != 'kz' && isset($data['Person_Inn']) && strlen($data['Person_Inn']) > 0 )
		{
			if ( CheckInn($data['Person_Inn']) === false )
			{
				$val = array(
					"success" => false,
					"Error_Msg" => toUTF('ИНН не прошел проверку по контрольному разряду.'),
					"Error_Code" => 444,
					"Cancel_Error_Handle" => true
				);
				$this->ReturnData($val);
				return false;
			}
		}

		$response = $this->dbmodel->checkPersonDoubles($data);
		if ( is_array($response) && count($response) > 0 ) {
			if ( (int)$response[0]['DoubleType_id'] > 0 ) {
				$msg = "Человек c такими данными уже присутствует в базе.";
				switch ( (int)$response[0]['DoubleType_id'] )
				{
					case 1:
						$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Серия-Номер полиса, Фамилия, Год рождения";
					break;
					case 2:
						$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Серия-Номер полиса, Имя, Отчество, Год рождения";
					break;
					case 3:
						$msg = "Данные человека не прошли проверку на дублирование по следующим полям: Фамилия, Имя, Отчество, Дата рождения";
					break;
					case 4:
						$msg = "Данные человека не прошли проверку на дублирование по следующим полям: ЕНП";
					break;
				}
				$val = array(
					"success" => false,
					"Error_Msg" => toUTF($msg),
					"Cancel_Error_Handle" => true
				);
			}
		}
		else {
			$val = array(
				'success' => false,
				'Error_Msg' => toUTF('Ошибка при проверке человека на дублирование данных! Обратитесь к разработчикам.'),
				'Cancel_Error_Handle' => true
			);
		}
		if(!in_array($data['session']['region']['nick'], array('kz', 'kareliya', 'astra', 'perm')) && isset($data['Federal_Num']) && $data['Federal_Num']!=''){ //redmine.swan.perm.ru/issues/93041
			$checkENP_Type = 1;
			$params = array(
				'Check_Type' => $checkENP_Type,
				'Person_id' => $data['Person_id'],
				'Federal_Num' => $data['Federal_Num'],
				'Person_SurName' => $data['Person_SurName'],
				'Person_FirName' => $data['Person_FirName'],
				'Person_SecName' => $data['Person_SecName'],
				'Person_BirthDay' => $data['Person_BirthDay']
			);
			$check_ENP = $this->dbmodel->check_ENP($params);
			if(!$check_ENP){
				$val = array(
					'success' => false,
					'Error_Msg' => toUTF('Данные человека не прошли проверку на дублирование по следующим полям: ЕНП, Фамилия, Год рождения'),
					'Cancel_Error_Handle' => true
				);
			}
			else
			{
				$checkENP_Type = 2;
				$params['Check_Type'] = $checkENP_Type;
				$check_ENP = $this->dbmodel->check_ENP($params);
				if(!$check_ENP){
					$val = array(
						'success' => false,
						'Error_Msg' => toUTF('Данные человека не прошли проверку на дублирование по следующим полям: ЕНП, Имя, Отчество, Год рождения'),
						'Cancel_Error_Handle' => true
					);
				}
			}
		}
		$this->ReturnData($val);
	}


	/**
	 * Проверка на дублирование номера СНИЛС
	 */
	function checkSnilsDoubles() {
		$data = $this->ProcessInputData('checkSnilsDoubles', true, false, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkSnilsDoubles($data);
		if(!empty($response[0]) and !empty($response[0]['PersonSurName_SurName'])) {
			$val = array(
				'success' => false,
				'Surname'=>$response[0]['PersonSurName_SurName'],
				'Name'=>$response[0]['PersonFirName_FirName'],
				'SecName'=>$response[0]['PersonSecName_SecName'],
				'BirthDay'=>$response[0]['PersonBirthDay']
			);
		} else {
			$val = array(
				'success' => true
			);
		}
		$this->ReturnData($val);
	}
	/**
	 * Получение всех периодик по человеку
	 */
	function getAllPeriodics()
	{
		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$this->db = null;
			$this->load->database('archive', false);
		}

		/*
		echo json_encode(array('success'=>false, 'Error_Msg' => toUTF('Редактирование периодик временно недоступно.')));
		exit;
		*/
		$data = array();
		
		$data = $this->ProcessInputData('getAllPeriodics', true, false, true);
		if ($data === false) { return false; }
				
		$info = $this->dbmodel->getAllPeriodics($data);
		
		if ( $info != false && count($info) > 0 )
		{						
			// нужно определить атрибуты, который не менялись, так как ни когда не изменившиеся атрибуты показывать не надо
                        // 25.01.2011 - уже не нужно
			// $used_classes = array();
			// foreach ($info as $rows)
                        // {
			// 	if ( isset($used_classes[$rows['PersonEvnClass_id']]) )
			// 		$used_classes[$rows['PersonEvnClass_id']]++;
			// 	else
			// 		$used_classes[$rows['PersonEvnClass_id']] = 1;
			// }
			$val = array();
			reset($info);
			foreach ($info as $rows)
                        {
				// if ( $used_classes[$rows['PersonEvnClass_id']] != 1 )
				// {
					array_walk($rows, 'ConvertFromWin1251ToUTF8');
					$val[] = $rows;
				// }
			}
                    $this->ReturnData($val);
                } else
        	ajaxErrorReturn();
	}
	
	/**
	 * Загрузка данных полиса
	 */
	function loadPolisData()
    {
		$data = $this->ProcessInputData('loadPolisData', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadPolisData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
    }

	/**
	 * Загрузка СНИЛС
	 */
	function getPersonSnils()
	{
		$data = $this->ProcessInputData('getPersonSnils', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonSnils($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении СНИЛС')->ReturnData();
	}

	/**
	 * Загрузка данных пациента
	 */
	function getPersonCombo()
	{
		$data = $this->ProcessInputData('getPersonCombo', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * CheckSpecifics
	 */
	function CheckSpecifics()
    {
		$data = $this->ProcessInputData('CheckSpecifics', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->CheckSpecifics($data);
		$this->ReturnData($response);
    }

	/**
	 * Получение диагнозов человека на диспансерном учете
	 */
	function getDiagnosesPersonOnDisp()
    {
		$data = $this->ProcessInputData('getDiagnosesPersonOnDisp', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDiagnosesPersonOnDisp($data);
		$this->ReturnData($response);
    }
	
	/**
	 * Получение данных человека для объединения
	 */
	function getInfoForDouble()
    {
		$data = $this->ProcessInputData('getInfoForDouble', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getInfoForDouble($data);
		$this->ReturnData($response);
    }

	/**
	 * Загрузка данных документа
	 */
	function loadDocumentData()
    {
		$data = $this->ProcessInputData('loadDocumentData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDocumentData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
    }


	/**
	 * Загрузка данных документа
	 */
	function loadNationalityStatusData()
    {
		$data = $this->ProcessInputData('loadNationalityStatusData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNationalityStatusData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
    }

	/**
	 *
	 * @return type 
	 */
	function getPersonIdentData(){
		$data = $this->ProcessInputData('getPersonIdentData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonIdentData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * Загрузка данных места работы
	 */
	function loadJobData()
    {
		$data = $this->ProcessInputData('loadJobData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadJobData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
    }

	/**
	 * Поиск людей
	 */
	function getPersonSearchGrid()
	{
		$data = $this->ProcessInputData('getPersonSearchGrid', true);
		if ($data === false) { return false; }

		//PROMEDWEB-3712 если заполнен номер карты КВС,
		//то придется искать в основной БД, т.к. нужных таблиц в БД ЛИС нет
		if ($this->usePostgreLis && !empty($data['armMode']) && $data['armMode'] == 'LIS' && empty($data['EvnPS_NumCard'])) {

			//некоторые символы плохо энкодятся, из-за чего ошибки в url запросах
			$keys = [
				'PersonSurName_SurName',
				'PersonFirName_FirName',
				'PersonSecName_SecName'
			];
			foreach($keys as $key) {
				if (isset($data[$key])) {
					$data[$key] = str_replace(' ', '*', $data[$key]);
					$data[$key] = json_encode($data[$key]);
				}
			}

			$this->load->swapi('lis');
			$response = $this->lis->GET('Person/search', $data);
			$this->ProcessRestResponse($response, true)->ReturnData();
		} else {
			$response = $this->dbmodel->getPersonSearchGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();

			return true;
		}
	}

	/**
	 * Валидация СНИЛС
	 */
	function verifyPersonSnils() {
		$data = $this->ProcessInputData('verifyPersonSnils', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->verifyPersonSnils($data);
		$this->ProcessModelSave($response, true, 'Ошибка постановки человека в очередь на валидацию СНИЛС')->ReturnData();

		return true;
	}
	
	/**
	 * Поиск людей в картотеке
	 * Используется: РПН: Прикрепление (swPersonCardViewAllWindow)
	 */
	function getPersonCardGrid() {
		$data = $this->ProcessInputData('getPersonCardGrid', true);
		if ( $data === false ) { return true; }

		if ( empty($data['soc_card_id']) && empty($data['Person_SurName'])&& (empty($data['Person_Inn']) || $data['session']['region']['nick'] != 'kz') ) {
			$this->ReturnError("Не задана Фамилия.");
			return false;
		}

		$response = $this->dbmodel->getPersonCardGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Поиск людей
	 * Используется: АРМ регистратора поликлиники (swWorkPlacePolkaRegWindow)
	 */
	function getPersonGrid() {
		$data = $this->ProcessInputData('getPersonGrid', true);
		if ( $data === false ) { return true; }

		$response = $this->dbmodel->getPersonGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Поргг
	 */
	function getOrgSMO() {
		$data = $this->ProcessInputData('getOrgSMO', true);
		if ( $data === false ) { return true; }

		$response = $this->dbmodel->getOrgSMO($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

    /**
     * Получение списка ЗЛ для автоприкрепления
     */
    function getPersonGridPersonCardAuto() {
        $data = $this->ProcessInputData('getPersonGridPersonCardAuto', true);
        if ($data === false){
            return false;
        }
        $response = $this->dbmodel->getPersonGridPersonCardAuto($data);
        //$this->ProcessModelList($response,true,true)->ReturnData();
        //var_dump($response);die;
        $this->ProcessModelMultiList($response, true, true)->ReturnData();

        // return true;
    }

    /**
	 * Экспорт людей из картотеки
	 * Используется: РПН: Прикрепление (swPersonCardViewAllWindow)
	 */
	function exportPersonCardForIdentification() {
		$data = $this->ProcessInputData('exportPersonCardForIdentification', true);
		if ( $data === false ) { return true; }

		if (getRegionNick() != 'ekb') {
			if (empty($data['session']['lpu_id'])) {
				$this->ReturnError('У пользователя не указана ЛПУ, выгрузка не возможна');
				return false;
			}

			$data['AttachLpu_id'] = $data['session']['lpu_id'];
		}
		
		$response = $this->dbmodel->exportPersonCardForIdentification($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение количества людей при поиске людей в картотеке
	 */
	function getCountPersonCardGrid()
	{
		$data = $this->ProcessInputData('getPersonCardGrid', true);
		if ($data === false) { return true; }

		$response = $this->dbmodel->getPersonCardGrid($data, false, true);

		$val = array();

		if ( $response != false && count($response) > 0 ) {
			$val['Records_Count'] = $response['totalCount'];
		}
		else {
			$val['Records_Count'] = 0;
		}

		$this->ReturnData($val);
	}
	
	
	/**
	 * Печать списка людей при поиске людей в картотеке
	 */
	function printPersonCardGrid()
	{
		$data = $this->ProcessInputData('getPersonCardGrid', true, true, false, false, false);
		if ( $data === false ) { return true; }

		$this->load->library('parser');

		$response = $this->dbmodel->getPersonCardGrid($data, true);

		$val = array();

		if ( is_array($response) && array_key_exists('data', $response) && count($response['data']) > 0 ) {
			$record_num = 1;

			foreach ( $response['data'] as $rows ) {
				$val[] = array(
					'Person_id'=>$rows['Person_id'],
					'Server_id'=>$rows['Server_id'],
					'Record_Num'=>$record_num,
					'PersonEvn_id'=>$rows['PersonEvn_id'],
					'Person_Surname'=>trim($rows['Person_SurName']),
					'Person_Firname'=>trim($rows['Person_FirName']),
					'Person_Secname'=>trim($rows['Person_SecName']),
					'Person_Birthday'=>trim($rows['PersonBirthDay']),
					'Lpu_Nick'=>trim($rows['Lpu_Nick']),
					'PersonCard_IsDms'=>trim($rows['PersonCard_IsDms'])=='true'?'Да':'Нет',
					'Person_IsFedLgot'=>trim($rows['Person_IsFedLgot'])=='true'?'Да':'Нет',
					'Person_IsRegLgot'=>trim($rows['Person_IsRegLgot'])=='true'?'Да':'Нет',
					'Person_Is7Noz'=>trim($rows['Person_Is7Noz'])=='true'?'Да':'Нет',
					'Person_IsBDZ'=>trim($rows['Person_IsBDZ'])=='true'?'Да':'Нет',
					'Person_IsRefuse'=>trim($rows['Person_IsRefuse'])=='true'?'Да':'Нет',
					'Person_UAddress'=>trim($rows['Person_UAddress']),
					'Person_PAddress'=>trim($rows['Person_PAddress']),
				);

				$record_num++;
			}
		}

		$this->parser->parse('person_search_grid_new', array('search_results' => $val));
	}
	
	/**
	 * Дополнительные проверки при сохранении, вынесены отдельно шаблонным методом, для переопределения в каждом регионе
	 */
	function validatePersonSaveRegional($data)
	{
		return true;
	}

	/**
	 * Проверки при сохранении
	 */
	function validatePersonSave($data)
	{
		// Исключения
		$firnameExceptions = array('И');
		$surnameExceptions = array('И', 'О', 'С', 'У', 'Ю', 'Е');

		// Фамилия
		if (!empty($data['Person_SurName'])
			&& !in_array($data['Person_SurName'], $surnameExceptions)
			&& (!preg_match("/^([А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]+([-]{1}|[\s]{1}|[\—]{1}|[\–]{1}){1,3}|[А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]+)+([А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]|[\']|[\`]){0,}$/ui", $data['Person_SurName'])
			&& (empty($data['Person_IsAnonym']) || !$data['Person_IsAnonym'])
				// || (substr($data['Person_SurName'], 0, 1) == 'Ъ')
				// || (substr($data['Person_SurName'], 0, 1) == 'Ь')
			)
		) {
			return array(
				"success" => false,
				"Error_Msg" => toUTF('Фамилия может содержать только буквы, дефис, апостроф и пробел'),
			);
		}
		// Имя
		else if ( !empty($data['Person_FirName'])
			&& !in_array($data['Person_FirName'], $firnameExceptions)
			&& (!preg_match("/^([А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]+([-]{1}|[\s]{1}|[\—]{1}|[\–]{1}){1,3}|[А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]+)+([А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]|[\']|[\`]){0,}$/ui", $data['Person_FirName'])
			&& (empty($data['Person_IsUnknown']) || !preg_match('/^([0-9А-ЯЁа-яё]+[ \-]?){0,2}[0-9А-ЯЁа-яё]+$/ui', $data['Person_FirName']))
				// || (substr($data['Person_FirName'], 0, 1) == 'Ъ')
				// || (substr($data['Person_FirName'], 0, 1) == 'Ь')
			)
		) {
			return array(
				"success" => false,
				"Error_Msg" => toUTF('Имя может содержать только буквы, дефис, апостроф и пробел'),
			);
		}
		// Отчество
		else if ( !empty($data['Person_SecName'])
			&& (!preg_match("/^([А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]+([-]{1}|[\s]{1}|[\—]{1}|[\–]{1}){1,3}|[А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]+)+([А-ЯЁа-яёәӘіІңҢғҒүҮұҰқҚөӨһҺ]|[\']|[\`]){0,}$/ui", $data['Person_SecName'])
				// || (substr($data['Person_SecName'], 0, 1) == 'Ъ')
				// || (substr($data['Person_SecName'], 0, 1) == 'Ь')
			)
		) {
			return array(
				"success" => false,
				"Error_Msg" => toUTF('Отчество может содержать только буквы, дефис, апостроф и пробел'),
			);
		}
		return true;
	}
	
	/**
	*  TODO:Метод для сохранение данных формы редактирования человека.
	*  Входящие данные: $_POST с данными формы
	*  На выходе: JSON-строка
	*  Используется: форма редактирования человека
	*/
	function savePersonEditWindow($toUtf = true)
	{
		$region = $_SESSION['region']['nick'];
		
		set_time_limit(0);
		$data = $this->ProcessInputData('savePersonEditWindow', true, false, true, false, $toUtf);

		/*$this->load->helper("Person");
		//var_dump($data); die();
		if ( ($data['PolisType_id'] == 4) && (!empty($data['Federal_Num'])) ) {
			$checkInfo = swCheckENPFormat($data['Federal_Num'], $data['Person_BirthDay'], $data['PersonSex_id']);
			if ( !empty($checkInfo) ) {
				$this->ReturnError($checkInfo);
				return false;
			}
		}*/
		
		if ($data === false) { return true; }
		$data['Server_id'] = $_SESSION['server_id'];

		// Чистим лишние пробелы
		$data['Person_SurName'] = trim(preg_replace('/ +/u', ' ', $data['Person_SurName']));
		$data['Person_FirName'] = trim(preg_replace('/ +/u', ' ', $data['Person_FirName']));
		$data['Person_SecName'] = trim(preg_replace('/ +/u', ' ', $data['Person_SecName']));

		if ( true !== ($errors = $this->validatePersonSaveRegional($data)) ) {
			$this->ReturnError($errors);
			return false;
		}
		
		if (!$this->checkOMSSprTerrDate($data)) {
			return false;
		}
		
		if (!$this->checkFederalNumUnique($data)) {
			return false;
		}

		if(!$this->checksumINN($data)){
			return false;
		}
		
		if(!$this->checkOMSSprTerrPolis($data)){
			return false;
		}

		if(!$this->checkOMSSprTerrPolis($data)){
			return false;
		}
		
		if($data['Person_IsAnonym'] /*&& $data['mode'] == 'add'*/) {
			$response = $this->dbmodel->checkAnonimCodeUnique($data);
			if(! $response[0]['success']) {
				$this->ProcessModelSave($response, true)->ReturnData();
				return false;
			}
		}

		$replace_symbols = array("-", "(", ")", " ");
		$data['PersonPhone_Phone'] = str_replace($replace_symbols, "", $data['PersonPhone_Phone']);
		$ret = $this->dbmodel->savePersonEditWindow($data);
		$outdata = $this->ProcessModelSave($ret,true)->getOutData();

		/*отправка данных в ActiveMQ (#88396)*/
		if( !isset($data["Person_id"]) && isset($outdata["Person_id"]) ) $data["Person_id"] = $outdata["Person_id"];
		$this->sendPersonToActiveMQ($data);
		
		$this->ReturnData($outdata);
	}
	/**
	 *
	 * @return type 
	 */
	function sendPersonToActiveMQ($data){
		
		$params = array(
			//'session-id' => $session_id,
			'action' => 'person',
			'id' => $data["Person_id"],
			'surname' => $data["Person_SurName"],
			'firname' => $data["Person_FirName"],
			'secname' => $data["Person_SecName"],				
			'birthday' => $data["Person_BirthDay"],
			'snils' => $data["Person_SNILS"],
			'sex.id' => $data["PersonSex_id"],
			/* ХЗ(адрес регистрации, код КЛАДР) 
			'uaddress.kladr' => $data["Person_SurName"],*/
			'uaddress.region' => $data["UKLRGN_id"],
			'uaddress.subregion' => $data["UKLSubRGN_id"],
			'uaddress.city' => $data["UKLCity_id"],
			'uaddress.town' => $data["UKLTown_id"],
			'uaddress.street' => $data["UKLStreet_id"],
			'uaddress.zip' => $data["UAddress_Zip"],
			'uaddress.house' => $data["UAddress_House"],
			'uaddress.corpus' => $data["UAddress_Corpus"],
			'uaddress.flat' => $data["UAddress_Flat"],
			/* ХЗ (адрес проживания, код КЛАДР)
			'paddress.kladr' => $data["Person_SurName"],*/
			'paddress.region' => $data["PKLRGN_id"],
			'paddress.subregion' => $data["PKLSubRGN_id"],
			'paddress.city' => $data["PKLCity_id"],
			'paddress.town' => $data["PKLTown_id"],
			'paddress.street' => $data["PKLStreet_id"],
			'paddress.zip' => $data["PAddress_Zip"],
			'paddress.house' => $data["PAddress_House"],
			'paddress.corpus' => $data["PAddress_Corpus"],
			'paddress.flat' => $data["PAddress_Flat"],
			'polis.series' => $data["Polis_Ser"],
			'polis.number' => $data["Polis_Num"],
			'polis.smo.id' => $data["OrgSMO_id"],
			'polis.polisType.id' => $data["PolisType_id"],
			'polis.begDate' => $data["Polis_begDate"]?$data["Polis_begDate"]:null,
			'polis.endDate' => $data["Polis_endDate"]?$data["Polis_endDate"]:null,
			'document.series' => $data["Document_Ser"],				
			'document.number' => $data["Document_Num"],
			'document.documentType.id' => $data["DocumentType_id"],
			'document.begDate' => $data["Document_begDate"]?$data["Document_begDate"]:null,
			/* ХЗ(дата окончания действия документа)
			'document.endDate  ' => $data["Person_SurName"]*/
			/* ХЗ(адрес рождения, код КЛАДР)
			'baddress.kladr  ' => $data["Person_SurName"]*/
			'baddress.region' => $data["BKLRGN_id"],
			'baddress.subregion' =>  $data["BKLSubRGN_id"],
			'baddress.city' =>  $data["BKLCity_id"],
			'baddress.town' =>  $data["BKLTown_id"],
			'baddress.street' =>  $data["BKLStreet_id"],
			'baddress.zip' =>  $data["BAddress_Zip"],
			'baddress.house' =>  $data["BAddress_House"],
			'baddress.corpus' =>  $data["BAddress_Corpus"],
			'baddress.flat' =>  $data["BAddress_Flat"],			
			'document.citizenship.id' =>  $data["PersonNationality_id"],
			'polis.polisFormType.id' =>  $data["PolisFormType_id"],
			'polis.omsSprTerr.id' =>  $data["OMSSprTerr_id"],
			/* ХЗ(адрес рождения, код КЛАДР)
			'smo.id' =>  $data["BAddress_Flat"],*/		
			'socStatus.id' =>  $data["SocStatus_id"]
		);

		if (defined('STOMPMQ_MESSAGE_DESTINATION_SLOT')) {
			sendStompMQMessage($params, 'Rule', STOMPMQ_MESSAGE_DESTINATION_SLOT);
		}
	}	

	function DeletePersonEval(){
		$data = $this->ProcessInputData('getPersonEvalEditWindow', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->deletePersonEval($data);
		$this->ReturnData($response);
	}

	/**
	 *
	 * @return type 
	 */
	function savePersonEvalEditWindow(){
		$data = $this->ProcessInputData('savePersonEvalEditWindow', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->savePersonEvalEditWindow($data);
	}

	/**
	 *
	 * @return type 
	 */
	function getPersonEvalEditWindow(){
		$data = $this->ProcessInputData('getPersonEvalEditWindow', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getPersonEvalEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		 //$this->ReturnData($response);
	}
	
	/**
	*  Метод для получения данных формы редактирования человека.
	*  Входящие данные: $_POST с идентификатором человека
	*  На выходе: JSON-строка
	*  Используется: форма редактирования человека
	*/
	function getPersonEditWindow()
	{
		$data = $this->ProcessInputData('getPersonEditWindow', true, false, true);
		if ($data === false) { return true; }

		$info = $this->dbmodel->getPersonEditWindow($data);
        $val = array();
        if ( $info != false && count($info) > 0 )
        {
			foreach ($info as $rows)
         	{
				array_walk($rows, 'ConvertFromWin1251ToUTF8');
				array_walk($rows, 'trim');
				$val[] = $rows;
			}
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
	}
	
	/**
	 * @comment
	 */
	function getAddressByPersonId(){
		 $data = $this->ProcessInputData('getAddressByPersonId',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getAddressByPersonId($data);
        $this->ProcessModelList($response,true)->ReturnData();
	}
	
	/**
	* Сохранение адреса регистрации
	*/
	function savePersonUAddress(){
		$data = $this->ProcessInputData('savePersonUAddress',true);
		if($data === false) {return false;}
		$response = $this->dbmodel->savePersonUAddress($data);
		$this->ProcessModelSave($response, true)->ReturnData();
        return true;
	}
	
	/**
	* Сохранение адреса проживания
	*/
	function savePersonPAddress(){
		$data = $this->ProcessInputData('savePersonUAddress',true);
		if($data === false) {return false;}
		$response = $this->dbmodel->savePersonUAddress($data, 'P');
		$this->ProcessModelSave($response, true)->ReturnData();
        return true;
	}

	/**
	*  Метод для получения данных формы редактирования человека на определенное событие.
	*  Входящие данные: $_POST с идентификатором человека
	*  На выходе: JSON-строка
	*  Используется: форма редактирования человека
	*/
	function getPersonEvnEditWindow()
	{
		$data = $this->ProcessInputData('getPersonEvnEditWindow', true, false, true);
		if ($data === false) { return true; }

		$info = $this->dbmodel->getPersonEvnEditWindow($data);
        $val = array();
        if ( $info != false && count($info) > 0 )
        {
			foreach ($info as $rows)
         	{
				array_walk($rows, 'ConvertFromWin1251ToUTF8');
				array_walk($rows, 'trim');
				$val[] = $rows;
			}
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
	}
	
	/**
	 * Получение Person_id по ФИО + Полис + Д/Р, полученные из УЕК
	 */
	function getPersonByUecData()
	{
        $data = $this->ProcessInputData('getPersonByUecData',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getPersonByUecData($data);
        $this->ProcessModelList($response,true)->ReturnData();
	}
	/**
	 *
	 * @return type 
	 */
	function getPersonEvnIdByEvnId(){
		$data = $this->ProcessInputData('getPersonEvnIdByEvnId',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getPersonEvnIdByEvnId($data);
        $this->ProcessModelList($response,true)->ReturnData();
	}
	
	/**
	 * Получение Person_id по ФИО + Полис + Д/Р, полученные из ЭП
	 */
	function getPersonByBdzData()
	{
        $data = $this->ProcessInputData('getPersonByUecData',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getPersonByUecData($data);
        $this->ProcessModelList($response,true)->ReturnData();
	}
	
	/**
	 *	Получение Person_id по ФИО + Полис + Д/Р + Пол, прочитанные из штрих-кода
	 */
	function getPersonByBarcodeData() {
        $data = $this->ProcessInputData('getPersonByBarcodeData',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getPersonByBarcodeData($data);
        $this->ProcessModelList($response,true)->ReturnData();
	}

	/**
	 *	Получение данныех полиса по Person_id
	 */
	function getPersonPolisInfo() {
        $data = $this->ProcessInputData('getPersonPolisInfo',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getPersonPolisInfo($data);
        //var_dump($response);die;
        $this->ProcessModelList($response,true)->ReturnData();
	}

	/**
	 *	Получение данных о месте работы по Person_id
	 *	Org_id
	 *  OrgUnion_id
	 *  Post_id
	 *  Post_Name
	 *
	 */
	function getPersonJobInfo() {
		$data = $this->ProcessInputData('getPersonJobInfo',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getPersonJobInfo($data);
		$this->ProcessModelList($response,true)->ReturnData();
	}

	/**
	 *	Получение данных телефона по Person_id
	 */
	function getPersonPhoneInfo() {
        $data = $this->ProcessInputData('getPersonPhoneInfo',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->getPersonPhoneInfo($data);
        //var_dump($response);die;
        $this->ProcessModelList($response,true)->ReturnData();
	}

	/**
	 *  Сохранение данных телефона в промед
	 */
	function savePersonPhoneInfo(){
		$data = $this->ProcessInputData('savePersonPhoneInfo', true);
		if ($data === false) { return false; }
		
		if(!empty($data['Phone_Promed'])){
			$replace_symbols = array("-", "(", ")", " ");
			$data['Phone_Promed'] = str_replace($replace_symbols, "", $data['Phone_Promed']);
		}
		$response = $this->dbmodel->savePersonPhoneInfo($data);

		$this->ProcessModelSave($response, true)->ReturnData();
        return true;
	}

	/**
	 * Экспорт реестров неработающих застрахованных лиц
	 */
	function exportPersonPolisToXml() {
		set_time_limit(0);
		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");
		
		unset($this->db);
		$this->load->database('search');

		$data = $this->ProcessInputData('exportPersonPolisToXml', true);
		if ($data === false) { return false; }
		$data['KLRgn_id'] = $data['session']['region']['number'];

		$this->load->library('textlog', array('file' => 'exportPersonPolisToXml.log'));
		$this->textlog->add("\n\r");
		$this->textlog->add("exportPersonPolisToXml: Запуск" . "\n\r");
		$this->textlog->add("Регион: " . $data['session']['region']['nick'] . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		// Возвращаем объект с данными, а не сами данные
		$list_object = $this->dbmodel->exportPersonPolisToXml($data);

		if ( !is_object($list_object) ) {
			$this->ReturnError('Ошибка при получении данных');
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->library('parser');

		$path = EXPORTPATH_ROOT."person_polis_list/";

		if (!file_exists($path)) {
			mkdir( $path );
		}

		$out_dir = "re_xml_".time()."_"."personPolisList";
		mkdir( $path.$out_dir );

		$data['PersonPolis_ExportIndex'] = sprintf('%02d', trim($data['PersonPolis_ExportIndex'], '_'));

		switch ( $data['session']['region']['nick'] ) {
			case 'kareliya':
				$file_name = sprintf('%02d', $data['session']['region']['number']) . date_format(date_create($data['PersonPolis_Date']), 'ym') . $data['PersonPolis_ExportIndex'];
			break;

			default:
				$file_name = sprintf('%02d', $data['session']['region']['number']) . date_format(date_create($data['PersonPolis_Date']), 'ym') . "ON" . $data['PersonPolis_ExportIndex'] . '001';
			break;
		}

		$file_path = $path.$out_dir."/".swGenRandomString()."_".$file_name.".xml";

		while ( file_exists($file_path) ) {
			$file_path = $path.$out_dir."/".swGenRandomString()."_".$file_name.".xml";
		}

		$file_path_tmp = $path.$out_dir."/".swGenRandomString()."_".$file_name."_tmp.xml";

		while ( file_exists($file_path) ) {
			$file_path_tmp = $path.$out_dir."/".swGenRandomString()."_".$file_name."_tmp.xml";
		}

		// Основные данные
		$i = 0;
		$mainData = array();

		switch ( $data['session']['region']['nick'] ) {
			case 'kareliya':
				$templ = 'person_polis_' . $data['session']['region']['nick'] . '_body';
			break;

			default:
				$templ = "person_polis_body";
			break;
		}

		while( $row = $list_object->_fetch_assoc()) {
			if ( $data['session']['region']['nick'] == 'kareliya' ) {
				if (
					($row['SocStatus_SysNick'] == 'child' && getCurrentAge($row['dr'], $data['PersonPolis_Date']) >= 18)
					|| ($row['SocStatus_SysNick'] == 'study' && getCurrentAge($row['dr'], $data['PersonPolis_Date']) >= 24)
				) {
					continue;
				}
			}

			$i++;
			$row['nomer_z'] = $i;
			$mainData[] = $row;

			if ( count($mainData) == 1000 ) {
				array_walk_recursive($mainData, 'ConvertFromUTF8ToWin1251', true);
				$xml = $this->parser->parse('export_xml/' . $templ, array('zl' => $mainData), true);
				$xml = str_replace('&', '&amp;', $xml);

				file_put_contents($file_path_tmp, $xml, FILE_APPEND);

				unset($xml);

				$mainData = array();

				$this->textlog->add("Задействовано памяти после выполнения записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");
			}
		}

		if ( $i == 0 ) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		if ( count($mainData) > 0 ) {
			array_walk_recursive($mainData, 'ConvertFromUTF8ToWin1251', true);
			$xml = $this->parser->parse('export_xml/' . $templ, array('zl' => $mainData), true);
			$xml = str_replace('&', '&amp;', $xml);

			file_put_contents($file_path_tmp, $xml, FILE_APPEND);

			unset($xml);
		}

		$this->textlog->add("Задействовано памяти после записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");

		unset($list_object);
		unset($mainData);

		$this->textlog->add("Задействовано памяти после очистки результатов запроса: " . memory_get_usage() . "\n\r");

		// Пишем данные в основной файл

		// Заголовок файла
		switch ( $data['session']['region']['nick'] ) {
			case 'kareliya':
				$templ = 'person_polis_' . $data['session']['region']['nick'] . '_header';
			break;

			default:
				$templ = 'person_polis_header';
			break;
		}

		$zglv = array(
			 'filename' => $file_name
			,'nfile' => '001'
			,'nrec' => $i
		);

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, $zglv, true);
		$xml = str_replace('&', '&amp;', $xml);
		file_put_contents($file_path, $xml, FILE_APPEND);

		// Тело файла начитываем из временного
		// Заменяем простую, но прожорливую конструкцию, на чтение побайтно
		// https://redmine.swan.perm.ru/issues/51529
		// file_put_contents($file_path, file_get_contents($file_path_tmp), FILE_APPEND);

		$fh = fopen($file_path_tmp, "rb");

		if ( $fh === false ) {
			$this->ReturnError('Ошибка при открытии файла');
			return false;
		}

		// Устанавливаем начитываемый объем данных
		$chunk = 10 * 1024 * 1024; // 10 MB

		while ( !feof($fh) ) {
			file_put_contents($file_path, fread($fh, $chunk), FILE_APPEND);
		}

		fclose($fh);

		// Конец файла
		switch ( $data['session']['region']['nick'] ) {
			case 'kareliya':
				$templ = 'person_polis_' . $data['session']['region']['nick'] . '_footer';
			break;

			default:
				$templ = 'person_polis_footer';
			break;
		}

		$xml = $this->parser->parse('export_xml/' . $templ, array(), true);
		$xml = str_replace('&', '&amp;', $xml);
		file_put_contents($file_path, $xml, FILE_APPEND);

		$file_zip_sign = $file_name;
		$file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_path, $file_name . ".xml" );
		$zip->close();

		unlink($file_path);
		unlink($file_path_tmp);

		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true,'Link' => $file_zip_name, 'Count' => $i));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива!')));
		}

		return true;
	}

	/**
	 *	Удаление признака смерти
	 */
	function revivePerson() {
        $data = $this->ProcessInputData('revivePerson',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->revivePerson($data);
        $this->ProcessModelSave($response,true)->ReturnData();
	}

	/**
	 *	Перечитать историю
	 */
	function extendPersonHistory() {
        $data = $this->ProcessInputData('extendPersonHistory',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->extendPersonHistory($data);
        $this->ProcessModelSave($response,true)->ReturnData();
	}

	/**
	 * Получение кода анонимного пациента
	 */
	function getPersonAnonymCode() {
		$data = $this->ProcessInputData('getPersonAnonymCode',true);
		if ($data === false) {return false;}
		if (in_array($data['session']['region']['nick'], array('ekb', 'vologda', 'buryatiya'))) {// getPersonAnonymCode запрашивается в Свердловской обласит, но могут подключиться другие регионы
			$response = $this->dbmodel->getPersonAnonymCodeExt($data);
		} else {
			$response = $this->dbmodel->getPersonAnonymCode($data);
		}
		$this->ProcessModelSave($response,true)->ReturnData();
	}

	/**
	 * Получение данных анонимного пациента
	 */
	function getPersonAnonymData() {
		$data = $this->ProcessInputData('getPersonAnonymData',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getPersonAnonymData($data);
		$this->ProcessModelSave($response,true)->ReturnData();
	}
	
	/**
	 * Получение кода анонимного пациента
	 */
	function getPersonByAddress() {
		$data = $this->ProcessInputData('getPersonByAddress',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getPersonByAddress($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}

	/**
	 * Получение кода анонимного пациента
	 */
	function loadPersonRequestDataGrid() {
		$data = $this->ProcessInputData('loadPersonRequestDataGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadPersonRequestDataGrid($data);
		$this->ProcessModelMultiList($response,true,true)->ReturnData();
	}

	/**
	 * Добавление данных человека на идентификацию в ЦС ЕРЗ
	 */
	function addPersonRequestData() {
        /* Структура для проверки (Вологда)
        echo '{
            "success": true,
            "Person_IsInErz": 2,
            "PersonIdentState_id": 1,
            "Person_identDT": "2019-10-09",
            "PersonPolis_id": null,
            "Person_id": "60636",
            "OrgSMO_id": "8000563",
            "OMSSprTerr_id": "2529",
            "PolisFormType_id": null,
            "PolisType_id": "4",
            "Polis_Ser": "",
            "Polis_Num": null,
            "Polis_begDate": "2013-11-28",
            "Polis_endDate": null,
            "Federal_Num": "3555620822001994"
        }';
        die();
        */

		$data = $this->ProcessInputData('addPersonRequestData',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->addPersonRequestData($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение согласия/отзыва согласия на обработку перс.данных
	 */
	function savePersonLpuInfo() {
		$data = $this->ProcessInputData('savePersonLpuInfo',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->savePersonLpuInfo($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение согласия/отзыва согласия на оформление рецепта в форме электронного документа
	 */
	function saveElectroReceptInfo() {
		$data = $this->ProcessInputData('saveElectroReceptInfo',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveElectroReceptInfo($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение списка согласия/отзыва согласия на обработку перс.данных
	 */
	function loadPersonLpuInfoList() {
		$data = $this->ProcessInputData('loadPersonLpuInfoList',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadPersonLpuInfoList($data);

		if(count($response) == 0)
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Отсутствует информация по согласию/отказу на обработку персональных данных по выбранному пациенту.')));
		}
		else
			$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Получение списка согласия/отзыва согласия на обработку перс.данных
	 */
	function savePersonWork() {
		$data = $this->ProcessInputData('savePersonWork', false);
		if ($data === false) {return false;}

        $response = $this->dbmodel->saveObject('PersonWork', $data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка согласия/отзыва согласия на обработку перс.данных
	 */
	function deletePersonWork() {
		$data = $this->ProcessInputData('deletePersonWork', false);
		if ($data === false) {return false;}

        $response = $this->dbmodel->deletePersonWork($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных о сотруднике организации для редактирования
	 */
	function loadPersonWorkForm() {
		$data = $this->ProcessInputData('loadPersonWorkForm',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadPersonWorkForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка сотрудников
	 */
	function loadPersonWorkList() {
		$data = $this->ProcessInputData('loadPersonWorkList',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadPersonWorkList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка сотрудников
	 */
	function loadPersonWorkGrid() {
		$data = $this->ProcessInputData('loadPersonWorkGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadPersonWorkGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Выгрузка данных по профилактическим мероприятиям
	 * @task https://redmine.swan.perm.ru/issues/111949
	 */
	public function exportPersonProfData() {
		set_time_limit(0);

		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");

		$data = $this->ProcessInputData('exportPersonProfData', true);
		if ($data === false) { return false; }

		if ( !isLpuAdmin($data['Lpu_id']) && empty($data['session']['isMedStatUser']) ) { // не админ МО и не статистик
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		$response = $this->dbmodel->exportPersonProfData($data);

		if ( $response === false || !is_array($response) || count($response) == 0 ) {
			$this->ReturnError('Ошибка при выгрузке данных');
			return false;
		}
		else if ( !empty($response['Error_Msg']) ) {
			$this->ReturnError($response['Error_Msg']);
			return false;
		}

		$this->load->library('parser');

		$path = EXPORTPATH_ROOT . "person_prof_list/";

		if ( !file_exists($path) ) {
			mkdir($path);
		}

		$links = array();

		// Формируются xml-файлы по СМО
		foreach ( $response['SMO_PERS'] as $key => $smo ) {
			$zglv = $smo['ZGLV'][0];

			$out_dir = "prof_" . time() . "_" . $data['pmUser_id'] . $zglv['CODE_MO'] . "_" . $zglv['SMO'];

			mkdir($path . $out_dir);

			$file_name = "PROF" . $zglv['SMO'] . $zglv['CODE_MO'] . "_" . $data['Year'] . sprintf('%02d', $data['Month']) . '_01';
			$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".XML";

			while ( file_exists($file_path) ) {
				$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".XML";
			}

			$zglv['FILENAME'] = $file_name . '.XML';

			// Заголовок файла
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse('export_xml/person_prof_header', $zglv, true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_path, $xml);

			// Тело файла
			$xml = $this->parser->parse_ext('export_xml/person_prof_body', array('PERS' => $smo['PERS']), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_path, $xml, FILE_APPEND);

			// Конец файла
			$xml = $this->parser->parse('export_xml/person_prof_footer', array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_path, $xml, FILE_APPEND);

			$file_zip_sign = $file_name;
			$file_zip_name = $path . $out_dir . "/" . $file_zip_sign . ".zip";

			if ( file_exists($file_zip_name) ) {
				unlink($file_zip_name);
			}

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_path, $file_name . ".XML");
			$zip->close();
			$links[] = $file_zip_name;
			
			unlink($file_path);
			unset($response['SMO_PERS'][$key]);
		}

		if ( count($links) > 0 ) {
			$this->ReturnData(array('success' => true, 'Links' => $links));
		}
		else {
			$this->ReturnError('Ошибка создания архива!');
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function checkPersonPhoneStatus() {
		$data = $this->ProcessInputData('checkPersonPhoneStatus', false);
		if ($data === false) return false;

		$response = $this->dbmodel->checkPersonPhoneStatus($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function sendPersonPhoneVerificationCode() {
		$data = $this->ProcessInputData('sendPersonPhoneVerificationCode', false);
		if ($data === false) return false;

		$code = sprintf('%04d', mt_rand(1, 9999));

		$response = array(
			'success' => false,
			'Error_Msg' => ''
		);

		try{
			$text = "Код подтверждения номера телефона: {$code}";
			$error_msg = "Ошибка при отправке смс-сообщения";

			set_error_handler(function()use($error_msg){throw new Exception($error_msg);}, E_ALL & ~E_NOTICE);
			$this->load->helper('Notify');
			$resp = sendPmUserNotifySMS(array(
				'sms_id' => $data['Person_id'].$code,
				'pmUser_Phone' => $data['PersonPhone_Phone'],
				'text' => $text
			));
			restore_error_handler();

			if (!$resp) {
				throw new Exception('Не удалось отправить СМС');
			}
		} catch(Exception $e) {
			$response['Error_Msg'] = $e->getMessage();
		}

		if (empty($response['Error_Msg'])) {
			$response['success'] = true;
			$response['PersonPhone_VerificationCode'] = $code;
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function addPersonPhoneHist() {
		$data = $this->ProcessInputData('addPersonPhoneHist', true);
		if ($data === false) return false;

		$replace_symbols = array("-", "(", ")", " ");
		$data['PersonPhone_Phone'] = str_replace($replace_symbols, "", $data['PersonPhone_Phone']);

		$response = $this->dbmodel->addPersonPhoneHist($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка согласий пациента для ЭМК
	 */
	function loadPersonLpuInfoPanel() {
		$data = $this->ProcessInputData('loadPersonLpuInfoPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonLpuInfoPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение списка свидетельств пациента для ЭМК
	 */
	function loadPersonSvidPanel() {
		$data = $this->ProcessInputData('loadPersonSvidPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonSvidInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение списка оперативных вмешательств пациента для ЭМК
	 */
	function loadPersonSurgicalPanel() {
		$data = $this->ProcessInputData('loadPersonSurgicalPanel', true, true, true);
		if ($data === false) { return false; }

		$this->load->model('EvnUsluga_model');
		$response = $this->EvnUsluga_model->getEvnUslugaOperViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение списка диспансеризаций пациента для ЭМК
	 */
	function loadPersonEvnPLDispPanel() {
		$data = $this->ProcessInputData('loadPersonEvnPLDispPanel', true, true, true);
		if ($data === false) { return false; }

		$this->load->model('EvnPLDisp_model');
		$response = $this->EvnPLDisp_model->getEvnPLDispInfoViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение списка отмененных направлений пациента для ЭМК
	 */
	function loadPersonDirFailPanel() {
		$data = $this->ProcessInputData('loadPersonDirFailPanel', true, true, true);
		if ($data === false) { return false; }

		$this->load->model('EvnDirection_model');
		$response = $this->EvnDirection_model->getDirFailListViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
	
	/**
	 * Получение списка реакций манту для ЭМК
	 */
	function loadPersonMantuReactionPanel() {
		$data = $this->ProcessInputData('loadPersonMantuReactionPanel', true, true, true);
		if ($data === false) { return false; }

		$this->load->model('Vaccine_model');
		$response = $this->Vaccine_model->GetMantuReaction($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
	/**
	 * Получение списка исполненных прививок
	 */
	function loadPersonInoculationPanel() {
		$data = $this->ProcessInputData('loadPersonInoculationPanel', true, true, true);
		if ($data === false) { return false; }

		$this->load->model('Vaccine_model');
		$response = $this->Vaccine_model->GetInoculationData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
	/**
	 * Получение списка планируемых прививок
	 */
	function loadPersonInoculationPlanPanel() {
		$data = $this->ProcessInputData('loadPersonInoculationPlanPanel', true, true, true);
		if ($data === false) { return false; }

		$this->load->model('Vaccine_model');
		$response = $this->Vaccine_model->GetInoculationPlanData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
	
	/**
	 * Проверка наличия ЗНО в последнем случае
	 */
	function checkEvnZNO_last() {
		$data = $this->ProcessInputData('checkEvnZNO_last', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->checkEvnZNO_last($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Изменение признака ЗНО
	 */
	function changeEvnZNO() {
		$data = $this->ProcessInputData('changeEvnZNO', true);
		if ( $data === false )  return;
		$response = $this->dbmodel->changeEvnZNO($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Получить дату взятия биопсии из последнего случая с признаком ЗНО
	 */
	function getEvnBiopsyDate() {
		$data = $this->ProcessInputData('getEvnBiopsyDate', true);
		if ( $data === false )  return;
		$result = $this->dbmodel->getEvnBiopsyDate($data);
		$this->ReturnData(array('success' => true, 'data' => $result ));
	}

	/**
	 * Получение списка согласий пациента для ЭМК
	 */
	function isReceptElectronicStatus() {
		$data = $this->ProcessInputData('isReceptElectronicStatus', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->isReceptElectronicStatus($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 *  Удаление измерения роста пациента
	 *  Входящие данные: $_POST['PersonHeight_id']
	 *  На выходе: JSON-строка
	 *  Используется: -
	 */
	function deletePersonHeight() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['deletePersonHeight']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->deletePersonHeight($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']) ) {
				$val['success'] = true;
			}
			else {
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При удалении измерения роста пациента возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


	/**
	 *  Получение данных для формы редактирования измерения роста пациента
	 *  Входящие данные: $_POST['PersonHeight_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования измерения роста пациента
	 */
	function loadPersonHeightEditForm() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonHeightEditForm']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonHeightEditForm($data);

		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	 *  Получение списка измерений роста пациента
	 *  Входящие данные: $_POST['Person_id'],
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования КВС
	 */
	function loadPersonHeightGrid() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadPersonHeightGrid']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadPersonHeightGrid($data);


		$result = array('success'=>true, 'data' => $response);
		$this->ReturnData($result);

		return true;
	}


	/**
	 *  Сохранение измерения роста пациента
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования измерения роста пациента
	 */
	function savePersonHeight() {
		$data = $this->ProcessInputData('savePersonHeight', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePersonHeight($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка измерений роста пациента для ЭМК
	 */
	function loadPersonHeightPanel() {
		$data = $this->ProcessInputData('loadPersonHeightPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonHeightPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Сохранение антропометрических данных
	 */
	function saveAnthropometryData() {
		$data = $this->ProcessInputData('saveAnthropometryData', true, true, true);
		if($data === false) {return false;}

		$data['HeightMeasureType_id'] = $data['MeasureType_id'];
		$data['WeightMeasureType_id'] = $data['MeasureType_id'];
		$data['PersonHeight_setDate'] = $data['PersonMeasure_setDate'];
		$data['PersonWeight_setDate'] = $data['PersonMeasure_setDate'];

		$this->load->model('PersonHeight_model');
		$this->load->model('PersonWeight_model');

		$response = $this->PersonHeight_model->savePersonHeight($data);
		if(isset($response[0]['Error_Msg'])) {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		$response = $this->PersonWeight_model->savePersonWeight($data);

		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Возвращает отклонения для роста
	 */
	function getDeviationHeight() {
		$data = $this->ProcessInputData('getDeviationHeight', true, true, true);
		if($data === false) {return false;}

		$this->load->model('DeviationType_model');
		$this->load->model('DeviationHeight_model');

		$person = $this->dbmodel->getPersonForInnova(['Person_id' => $data['Person_id']]);
		if (!$person) {
			return false;
		}

		$birthDate = new DateTime($person[0]['BirthDate']);
		$now = new DateTime();
		$diff = $now->diff($birthDate);

		$years = $diff->y;
		$months = 0;

		if ($years >= 0 && $years <= 7) {
			$months = $diff->m;
		} else {
			if ($years > 17) return false;
		}

		$deviationHeight = $this->DeviationHeight_model->getDeviationHeightByAge(['years' => $years, 'months' => $months, 'sex' => $person[0]['Sex'], 'height' => $data['PersonHeight_Height']]);
		if (!isset($deviationHeight['DeviationType_id'])) {
			return false;
		}
		$deviationType = $this->DeviationType_model->getDeviationTypeById(['DeviationType_id' => $deviationHeight['DeviationType_id']]);

		$deviationHeight['DeviationType_Name'] = $deviationType['DeviationType_Name'];

		unset($deviationHeight['DeviationType_id']);

		$this->ProcessModelList($deviationHeight, true, true)->ReturnData();

		return false;
	}

	/**
	 * Возвращает отклонения для веса
	 */
	function getDeviationWeight() {
		$data = $this->ProcessInputData('getDeviationWeight', true, true, true);
		if($data === false) {return false;}

		$this->load->model('DeviationType_model');
		$this->load->model('DeviationWeight_model');

		$person = $this->dbmodel->getPersonForInnova(['Person_id' => $data['Person_id']]);
		if (!$person) {
			return false;
		}

		$birthDate = new DateTime($person[0]['BirthDate']);
		$now = new DateTime();
		$diff = $now->diff($birthDate);

		$years = $diff->y;
		$months = 0;

		if ($years >= 0 && $years <= 6) {
			$months = $diff->m;
		} else {
			if ($years > 17) return false;
		}

		$deviationWeight = $this->DeviationWeight_model->getDeviationWeightByAge(['years' => $years, 'months' => $months, 'sex' => $person[0]['Sex'], 'weight' => $data['PersonWeight_Weight']]);
		if (!isset($deviationWeight['DeviationType_id'])) {
			return false;
		}
		$deviationType = $this->DeviationType_model->getDeviationTypeById(['DeviationType_id' => $deviationWeight['DeviationType_id']]);

		$deviationWeight['DeviationType_Name'] = $deviationType['DeviationType_Name'];

		unset($deviationWeight['DeviationType_id']);

		$this->ProcessModelList($deviationWeight, true, true)->ReturnData();

		return false;
	}

	/**
	 * Удаление антропометрических данных
	 */
	function removeAnthropometryData() {
		$data = $this->ProcessInputData('removeAnthropometryData', true, true, true);
		if($data === false) {return false;}

		$data['HeightMeasureType_id'] = $data['MeasureType_id'];
		$data['WeightMeasureType_id'] = $data['MeasureType_id'];
		$data['PersonHeight_setDate'] = $data['PersonMeasure_setDate'];
		$data['PersonWeight_setDate'] = $data['PersonMeasure_setDate'];

		$this->load->model('PersonHeight_model');
		$this->load->model('PersonWeight_model');

		$response = $this->PersonHeight_model->deletePersonHeight($data);
		if(isset($response[0]['Error_Msg'])) {
			$this->ProcessModelList($response, true, true)->ReturnData();
		}

		$response = $this->PersonWeight_model->deletePersonWeight($data);

		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Получение антропометрических параметров для построения графика
	 */
	function getAnthropometryPanel() {
		$data = $this->ProcessInputData('getAnthropometryPanel', true, true, true);
		if($data === false) {return false;}
		
		if($data['limiter'] == null)
			unset($data['limiter']);
		
		$this->load->model('PersonHeight_model');
		$this->load->model('PersonWeight_model');

		$response_height = $this->PersonHeight_model->loadPersonHeightPanel($data);

		if(isset($response_height[0]['Error_Msg'])) {
			$this->ProcessModelList($response_height, true, true)->ReturnData();
		}

		$response_weight = $this->PersonWeight_model->loadPersonWeightPanel($data);

		if(isset($response_weight[0]['Error_Msg'])) {
			$this->ProcessModelList($response_height, true, true)->ReturnData();
		}

		$response = array();
		$i = 0;

		for($weightStep = 0; $weightStep < count($response_weight); $weightStep++) {
			for($heightStep = 0; $heightStep < count($response_height); $heightStep++) {
				if($response_weight[$weightStep]['PersonWeight_setDate'] == $response_height[$heightStep]['PersonHeight_setDate']) {
					$response[$i] = array(
						'Person_id' => $response_weight[$weightStep]['Person_id'],
						'PersonWeight_id' => $response_weight[$weightStep]['PersonWeight_id'],
						'PersonWeight_Weight' => floatval($response_weight[$weightStep]['PersonWeight_Weight']),
						'MeasureType_Name' => $response_weight[$weightStep]['WeightMeasureType_Name'],
						'MeasureType_id' => $response_weight[$weightStep]['WeightMeasureType_Name'] == 'Плановый' ? 1 : ($response_weight[$weightStep]['WeightMeasureType_Name'] == 'При рождении' ? 3 : 2),
						'Measure_setDate' => $response_weight[$weightStep]['PersonWeight_setDate'],
						'Person_Imt' => $response_weight[$weightStep]['PersonWeight_Imt'],
						'PersonHeight_id' => $response_height[$heightStep]['PersonHeight_id'],
						'PersonHeight_Height' => floatval($response_height[$heightStep]['PersonHeight_Height'])
					);
					$i++;
					break;
				}
			}
		}

		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	public function getPersonDeputy(){
		$data = $this->ProcessInputData('getPersonDeputy', true);
		if($data === false) {return false;}
		
		$response = $this->dbmodel->getPersonDeputy($data, false, true);
		
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
}

