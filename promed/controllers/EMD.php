<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EMD - контроллер для работы с электронными медицинскими документами (ЭМД)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2010-2018 Swan Ltd.
* @author		Dmitry Vlasenko
*/
class EMD extends swController {
	public $inputRules = array(
		'loadEMDCertificateList' => array(
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isMOSign',
				'label' => 'признак загрузки сертификатов только на юр. лицо',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'getStampedPdf' => array(
			array(
				'field' => 'EMDVersion_id',
				'label' => 'Идентификатор версии документа',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEMDDocumentSignGrid' => [
			[
				'field' => 'Lpu_id',
				'label' => 'Список МО',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'LpuSection_id',
				'label' => 'Список отделений',
				'rules' => '',
				'type' => 'string'
			]
		],
		'saveEMDDocumentSignRules' => [
			[
				'field' => 'Lpu_id',
				'label' => 'Список МО',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'LpuSection_id',
				'label' => 'Список отделений',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'records',
				'label' => 'Список правил',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			]
		],
		'loadEMDJournalQuery' => array(
			array(
				'field' => 'EMDJournalQuery_OutDT',
				'label' => 'Дата запроса',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'EMDQueryType_id',
				'label' => 'Тип запроса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDDocumentTypeLocal_id',
				'label' => 'Тип документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDRegistry_Num',
				'label' => 'Номер ЭМД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDQueryStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDLpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'start',
				'label' => 'Начальная запись в базе для пагинации',
				'rules' => '',
				'type' => 'int',
				 'default' => 0
			),
			array(
				'field' => 'limit',
				'label' => 'Количество записей выборки для пагинации',
				'rules' => '',
				'type' => 'int',
				'default' => 100
			)
		),
		'loadEMDJournalQueryDetal' => array(
			array(
				'field' => 'EMDJournalQuery_id',
				'label' => 'Идентификатор записи в журнале запросов',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEMDQueryError' => array(
			array(
				'field' => 'EMDJournalQuery_id',
				'label' => 'Идентификатор записи в журнале запросов',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'EMDSearch' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Медицинская организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDDocumentTypeLocal_id',
				'label' => 'Вид документа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDRegistry_Num',
				'label' => 'Номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_EMDDate_period',
				'label' => 'Период: дата изменеия ЭМД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isLpuSignNeeded',
				'label' => 'Нужна подпись МО',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'isWithoutRegistration',
				'label' => 'Без регистрации',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'EMDVersion_RegistrationDate_period',
				'label' => 'Период: дата регистрации ЭМД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isGlobalAndActive',
				'label' => 'Задан вид РЭМД и пустая дата окончания',
				'rules' => '',
				'type' => 'boolean'
 			),
			array(
				'default' => 1,
				'field' => 'page',
				'label' => 'Номер страницы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isGlobalAndActive',
				'label' => 'Задан вид РЭМД и пустая дата окончания',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isSentToEGISZ',
				'label' => 'Отправлялась в ЕГИСЗ',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'EMDRegistrationStatus_Code',
				'label' => 'Статус регистрации',
				'rules' => '',
				'type' => 'int'
			)
		),
		'searchDocs' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Медицинская организация',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDDocumentTypeLocal_id',
				'label' => 'Вид документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_Num',
				'label' => 'Номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_insDT_period',
				'label' => 'Период: дата создания документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_updDT_period',
				'label' => 'Период: дата изменения документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isWithoutSign',
				'label' => 'Без подписи',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'loadEMDVersions' => array(
			array(
				'field' => 'EMDRegistry_id',
				'label' => 'Идентификатор ЭМД',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EMDVersion_RegistrationDate_period',
				'label' => 'Период: дата регистрации ЭМД',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'isWithoutRegistration',
				'label' => 'Без регистрации',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'loadEMDSignWindow' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectIDs',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'json_array'
			)
		),
		'loadEMDVersionList' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkBeforeSign' => [
			[
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			],
			[
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			],
			[
				'field' => 'EMDPersonRole_id',
				'label' => 'Роль',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'EMDCertificate_id',
				'label' => 'Идентификатор сертификата',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'isMOSign',
				'label' => 'признак подписания от МО',
				'rules' => '',
				'type' => 'boolean'
			]
		],
		'generateEMDRegistry' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EMDCertificate_id',
				'label' => 'Идентификатор сертификата',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDVersion_id',
				'label' => 'Идентификатор версии',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadSignaturesInfo' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'verifySignature' => array(
			array(
				'field' => 'EMDSignatures_id',
				'label' => 'Идентификатор подписи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'exportSignature' => array(
			array(
				'field' => 'EMDSignatures_id',
				'label' => 'Идентификатор подписи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEMDSignatures' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EMDVersion_id',
				'label' => 'Идентификатор версии в регистре',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_Hash',
				'label' => 'Хэш',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Signatures_SignedData',
				'label' => 'Подпись',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_id',
				'label' => 'Сертификат',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'signType',
				'label' => 'Тип подписи',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDPersonRole_id',
				'label' => 'Роль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isMOSign',
				'label' => 'признак подписания от МО',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'идентификатор рабочего места',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'идентификатор службы',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEMDCertificateEditWindow' => array(
			array(
				'field' => 'EMDCertificate_id',
				'label' => 'Идентификатор сертификата',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEMDCertificate' => array(
			array(
				'field' => 'bypassStrictCommonName',
				'label' => 'Пропуск предупреждения несоответствия сертификата',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'EMDCertificate_id',
				'label' => 'Идентификатор сертификата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDCertificate_Version',
				'label' => 'Версия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_Serial',
				'label' => 'Серийный номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_begDate',
				'label' => 'Дата начала',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EMDCertificate_endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EMDCertificate_begTime',
				'label' => 'Время начала',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EMDCertificate_endTime',
				'label' => 'Время окончания',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EMDCertificate_Publisher',
				'label' => 'Кем выдан',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_CommonName',
				'label' => 'Общее имя (CN)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_Post',
				'label' => 'Должность',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_Org',
				'label' => 'Организация (O)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_Unit',
				'label' => 'Подразделение (OU)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_SignAlgorithm',
				'label' => 'Алгоритм подписи',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_OpenKey',
				'label' => 'Открытый ключ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_SHA256',
				'label' => 'SHA-256',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_SHA1',
				'label' => 'SHA-1',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_PublisherUID',
				'label' => 'УИД издателя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EMDCertificate_SubjectUID',
				'label' => 'УИД субъекта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDCertificate_IsNotUse',
				'label' => 'Не используется',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'EMDCertificate_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'ignoreCertFio',
				'label' => 'Признак игнорирования проверки соответсвия ФИО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EMDCertificate_OGRN',
				'label' => 'Идентификатор ОГРН',
				'rules' => '',
				'type' => 'string'
			),
			[
				'field' => 'EMDCertificate_SurName',
				'label' => 'Общее имя (CN)',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'EMDCertificate_FirName',
				'label' => 'Фамилия (SN)',
				'rules' => '',
				'type' => 'string'
			],
		),
		'deleteEMDCertificate' => array(
			array(
				'field' => 'EMDCertificate_id',
				'label' => 'Идентификатор сертификата',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getCertificateFileInfo' => array(
			array(
				'field' => 'UserCertFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getDocumentTypeLocalList' => array(
			array(
				'field' => 'isGlobalAndActive',
				'label' => 'Задан вид РЭМД и пустая дата окончания',
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'getDocumentTypeList' => array(),
		'checkNeedSignature' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getSignStatus' => array(
			array(
				'field' => 'EMDRegistry_ObjectName',
				'label' => 'Наименование объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EMDRegistry_ObjectID',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonRoleList' => array(),
		'saveEMDMedStaffFactRoles' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RolesList',
				'label' => 'Список ролей рабочего места врача',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'loadEMDMedStaffFactRoles' => array(
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы врача',
					'rules' => 'required',
					'type' => 'id'
				)
		),
		'checkSignedEvnContent' => array(
			array(
				'field' => 'object',
				'label' => 'Объект проверки',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'object_id',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => "asRawData",
				'default' => 0,
				'label' => 'получить необработанные данные',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getEMDlist' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEMDOuterRegistry_File' => array(
			array(
				'field' => 'EMDOuterRegistry_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'searchRegistryItem' => [
			[
				'field' => 'Person_Snils',
				'label' => 'СНИЛС пациента',
				'rules' => 'required',
				'type' => 'string'
			], [
				'field' => 'EMDDocumentType_id',
				'label' => 'Тип документа',
				'rules' => '',
				'type' => 'id'
			]
		],
		'demandContent' => [
			[
				'field' => 'EMDOuterRegistry_emdrId',
				'label' => 'Номер в регистре',
				'rules' => 'required',
				'type' => 'string'
			]
		],
		'getEMDlistByPersonForShow' => [
			[ 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id' ]
		],
		'getEMDSignaturesInfo' => [
			[ 'field' => 'EMDSignatures_id', 'label' => 'Идентификатор подписи', 'rules' => 'required', 'type' => 'id' ]
		],
		'getEMDQueryStatus' => [],
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EMD_model', 'dbmodel');
	}
	
	/**
	 * Получение cправочника «РЭМД ЕГИСЗ. Виды регистрируемых электронных медицинских документов»
	 */
	function getDocumentTypeList() {
		$data = $this->ProcessInputData('getDocumentTypeList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDocumentTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка документов
	 */
	function getDocumentTypeLocalList() {
		$data = $this->ProcessInputData('getDocumentTypeLocalList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDocumentTypeLocalList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка РМИС документов
	 */
	function searchDocs() {
		$data = $this->ProcessInputData('searchDocs', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->searchDocs($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка РЭМД документов
	 */
	function EMDSearch() {
		$data = $this->ProcessInputData('EMDSearch', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->EMDSearch($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка версий документов (из поиска)
	 */
	function loadEMDVersions() {
		$data = $this->ProcessInputData('loadEMDVersions', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDVersions($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение журнала запросов РЭМД ЕГИСЗ
	 */
	function loadEMDJournalQuery() {
		$data = $this->ProcessInputData('loadEMDJournalQuery', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDJournalQuery($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение подробностей по выбранной записи из журнала РЭМД ЕГИСЗ
	 */
	function loadEMDJournalQueryDetal() {
		$data = $this->ProcessInputData('loadEMDJournalQueryDetal', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDJournalQueryDetal($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение ошибок для журнала запросов РЭМД ЕГИСЗ
	 */
	function loadEMDQueryError() {
		$data = $this->ProcessInputData('loadEMDQueryError', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDQueryError($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка сертификатов пользователя
	 */
	function loadEMDCertificateList() {
		$data = $this->ProcessInputData('loadEMDCertificateList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDCertificateList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы подписания
	 */
	function loadEMDSignWindow() {
		$data = $this->ProcessInputData('loadEMDSignWindow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDSignWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка версий документа
	 */
	function loadEMDVersionList() {
		$data = $this->ProcessInputData('loadEMDVersionList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDVersionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Проверка возможности подписания
	 */
	function checkBeforeSign() {
		$data = $this->ProcessInputData('checkBeforeSign', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkBeforeSign($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки возможности подписания')->ReturnData();

		return true;
	}

	/**
	 * Получение данных для подписания
	 */
	function generateEMDRegistry() {
		$data = $this->ProcessInputData('generateEMDRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->generateEMDRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных для подписания')->ReturnData();

		return true;
	}

	/**
	 * Получение данных для повторного подписания документа
	 */
	function getEMDVersionSignData() {
		$data = $this->ProcessInputData('generateEMDRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEMDVersionSignData($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных для подписания')->ReturnData();

		return true;
	}

	/**
	 * Получение версии документа для просмотра
	 */
	function getStampedPdf() {
		$data = $this->ProcessInputData('getStampedPdf', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getStampedPdf($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения версии документа для просмотра')->ReturnData();

		return true;
	}

	/**
	 * Получение списка правил подисания
	 */
	function loadEMDDocumentSignGrid() {
		$data = $this->ProcessInputData('loadEMDDocumentSignGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDDocumentSignGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение списка правил подисания
	 */
	function saveEMDDocumentSignRules() {
		$data = $this->ProcessInputData('saveEMDDocumentSignRules', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEMDDocumentSignRules($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных подписи
	 */
	function loadSignaturesInfo() {
		$data = $this->ProcessInputData('loadSignaturesInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSignaturesInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Проверка подписи документа
	 */
	function verifySignature() {
		$data = $this->ProcessInputData('verifySignature', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->verifySignature($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки подписи документа')->ReturnData();

		return true;
	}

	/**
	 * Экспорт подписи документа
	 */
	function exportSignature() {
		$data = $this->ProcessInputData('exportSignature', true);
		if ($data === false) { return false; }

		$this->dbmodel->exportSignature($data);
		return true;
	}

	/**
	 * Сохранение подписи
	 */
	function saveEMDSignatures() {
		$data = $this->ProcessInputData('saveEMDSignatures', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEMDSignatures($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения подписи')->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы редактирования сертификата
	 */
	function loadEMDCertificateEditWindow() {
		$data = $this->ProcessInputData('loadEMDCertificateEditWindow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDCertificateEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение сертификата
	 */
	function saveEMDCertificate() {
		$data = $this->ProcessInputData('saveEMDCertificate', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEMDCertificate($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении сертификата')->ReturnData();

		return true;
	}

	/**
	 * Удаление сертификата
	 */
	function deleteEMDCertificate() {
		$data = $this->ProcessInputData('deleteEMDCertificate', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEMDCertificate($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении сертификата')->ReturnData();

		return true;
	}

	/**
	 * Загрузка сертификата X.509
	 */
	function getCertificateFileInfo() {
		$data = $this->ProcessInputData('getCertificateFileInfo', true);
		if ($data === false) { return false; }

		$allowed_types = explode('|','cer|crt');

		if (!isset($_FILES['UserCertFile'])) {
			$this->ReturnError('Не выбран файл сертификата!');
			return false;
		}

		if (!is_uploaded_file($_FILES['UserCertFile']['tmp_name']))
		{
			$error = (!isset($_FILES['UserCertFile']['error'])) ? 4 : $_FILES['UserCertFile']['error'];
			switch($error)
			{
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
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}

			$this->ReturnError($message);
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['UserCertFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен.');
			return false;
		}

		if (!extension_loaded('openssl')) {
			$this->ReturnError('Не подключена библиотека openssl. Импорт сертификатов невозможен.');
			return false;
		}

		$cert = file_get_contents($_FILES["UserCertFile"]["tmp_name"]);

		$this->load->helper('openssl');
		$cert = getCertificateFromString($cert);

		$resource = @openssl_x509_read($cert);
		if ($resource === false) {
			$this->ReturnError('Неверный файл сертификата');
			return false;
		}

		$sha1 = null;
		$sha256 = null;
		$output = null;
		$result = openssl_x509_export($resource, $output);
		if($result !== false) {
			$cert_base64 = str_replace(array("\r\n", "\n", "\r", "-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----"), "", $output);
			$bin = base64_decode($cert_base64);
			$sha1 = sha1($bin);
			$sha256 = hash('sha256', $bin);
		} else {
			$this->ReturnError('Ошибка экспорта сертификата');
			return false;
		}

		$info = openssl_x509_parse($resource);
		$certAdditionalData = $this->dbmodel->parseCertAdditionalData($info['name']);
		if (!empty($certAdditionalData)) {
			$ogrn = $this->dbmodel->getCertAdditionalParam('1.2.643.100.1', $certAdditionalData); // ОГРН шифруется как 1.2.643.100.1
			if (empty($ogrn)) {
				$ogrn = $this->dbmodel->getCertAdditionalParam('OGRN', $certAdditionalData); // иногда ОГРН записывается так
			}
		}

		$signature_algorithm = '';
		openssl_x509_export($resource, $out, FALSE);
		if (preg_match('/^\s+Public Key Algorithm:\s*(.*)\s*$/m', $out, $match)) {
			$signature_algorithm = $match[1];
		}

		$resp = array(
			'success' => true,
			'EMDCertificate_Version' => !empty($info['version'])?$info['version']:'',
			'EMDCertificate_Serial' => !empty($info['serialNumber'])?$info['serialNumber']:'',
			'EMDCertificate_begDate' => !empty($info['validFrom_time_t'])?date('d.m.Y', $info['validFrom_time_t']):'',
			'EMDCertificate_begTime' => !empty($info['validFrom_time_t'])?date('H:i', $info['validFrom_time_t']):'',
			'EMDCertificate_endDate' => !empty($info['validTo_time_t'])?date('d.m.Y', $info['validTo_time_t']):'',
			'EMDCertificate_endTime' => !empty($info['validTo_time_t'])?date('H:i', $info['validTo_time_t']):'',
			'EMDCertificate_Publisher' => !empty($info['issuer']['CN'])?$info['issuer']['CN']:'',
			'EMDCertificate_CommonName' => !empty($info['subject']['CN'])?$info['subject']['CN']:'',
			'EMDCertificate_SurName' => !empty($info['subject']['SN'])?$info['subject']['SN']:'',
			'EMDCertificate_FirName' => !empty($info['subject']['GN'])?$info['subject']['GN']:'',
			'EMDCertificate_Post' => !empty($info['subject']['title'])?$info['subject']['title']:'',
			'EMDCertificate_Org' => !empty($info['subject']['O'])?$info['subject']['O']:'',
			'EMDCertificate_Unit' => !empty($info['subject']['OU'])?$info['subject']['OU']:'',
			'EMDCertificate_SignAlgorithm' => $signature_algorithm,
			'EMDCertificate_OpenKey' => $cert_base64,
			'EMDCertificate_SHA256' => $sha256,
			'EMDCertificate_SHA1' => $sha1,
			'EMDCertificate_PublisherUID' => '',
			'EMDCertificate_SubjectUID' => '',
			'EMDCertificate_OGRN' => !empty($ogrn) ? $ogrn : null
		);

		$this->ReturnData($resp);

		return true;
	}

	/**
	 * Проверка, что для документа необходима подпись
	 */
	function checkNeedSignature() {
		$data = $this->ProcessInputData('checkNeedSignature', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkNeedSignature($data);
		$this->ProcessModelSave($response, true, 'Ошибка при проверке необходимости подписания документа')->ReturnData();

		return true;
	}

	/**
	 * Получение статуса подписания и количества подписей
	 */
	function getSignStatus() {
		$data = $this->ProcessInputData('getSignStatus', true);
		if ($data === false) { return false; }

		$response = [
			'IsSigned' => 2,
			'SignCount' => 0,
			'MinSignCount' => 0,
			'success' => true
		];
		$resp = $this->dbmodel->getSignStatus([
			'EMDRegistry_ObjectIDs' => [$data['EMDRegistry_ObjectID']],
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
		]);

		if (isset($resp[$data['EMDRegistry_ObjectID']])) {
			$response['IsSigned'] = $resp[$data['EMDRegistry_ObjectID']]['signed'];
			$response['SignCount'] = $resp[$data['EMDRegistry_ObjectID']]['signcount'];
			$response['MinSignCount'] = $resp[$data['EMDRegistry_ObjectID']]['minsigncount'];
		}

		$this->ReturnData($response);

		return true;
	}

	/**
	 * Список всех ролей для комбо
	 */
	function getPersonRoleList() {

		$data = $this->ProcessInputData('getPersonRoleList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonRoleList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение роли для рабочего места врача
	 */
	function saveEMDMedStaffFactRoles() {
		$data = $this->ProcessInputData('saveEMDMedStaffFactRoles', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEMDMedStaffFactRoles($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении ролей')->ReturnData();

		return true;
	}

	/**
	 * Загрузка ролей для рабочего места врача
	 */
	function loadEMDMedStaffFactRoles() {

		$data = $this->ProcessInputData('loadEMDMedStaffFactRoles', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEMDMedStaffFactRoles($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Контроли на наличие ЭП и ЭМД (в составе документов ТАП)
	 */
	function checkSignedEvnContent() {

		$data = $this->ProcessInputData('checkSignedEvnContent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkSignedEvnContent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получить список ЭМД для формы "Реестр внешних ЭМД"
	 */
	function getEMDlist() {
		$data = $this->ProcessInputData('getEMDlist', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEMDlist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получить файл
	 */
	function getEMDOuterRegistry_File() {
		$data = $this->ProcessInputData('getEMDOuterRegistry_File', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEMDOuterRegistry_File($data);
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Запрос списка ЭМД пациента
	 */
	function searchRegistryItem() {
		$data = $this->ProcessInputData('searchRegistryItem', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->searchRegistryItem($data);
		$this->ProcessModelSave($response, true, 'Ошибка при запросе списка ЭМД пациента')->ReturnData();

		return true;
	}

	/**
	 * Запрос файла ЭМД
	 */
	function demandContent() {
		$data = $this->ProcessInputData('demandContent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->demandContent($data);
		$this->ProcessModelSave($response, true, 'Ошибка при запросе файла ЭМД')->ReturnData();

		return true;
	}

	/**
	 * Получение списка РЭМД документов по пациенту (для показа!)
	 */
	public function getEMDlistByPersonForShow() {
		$data = $this->ProcessInputData('getEMDlistByPersonForShow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEMDlistByPersonForShow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение информации о подписи
	 */
	public function getEMDSignaturesInfo() {
		$data = $this->ProcessInputData('getEMDSignaturesInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEMDSignaturesInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка статусов запросов ЭМД для комбобокса
	 */
	public function getEMDQueryStatus() {
		$data = $this->ProcessInputData('getEMDQueryStatus', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getEMDQueryStatus($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
}
