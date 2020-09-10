<?php
/**
 * EMD_model - модель для работы с электронными медицинскими документами (ЭМД)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		EMD
 * @access		public
 * @copyright	Copyright (c) 2010-2018 Swan Ltd.
 * @author		Dmitry Vlasenko
 */
class EMD_model extends swPgModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$this->emddb = $this->load->database('emd', true); // своя БД на PostgreSQL
		} else {
			throw new Exception('Не настроена база данных для хранения электронных медицинских документов');
		}
	}

	/**
	 * Так как таблицы соответствия имен классов и идентификаторов нет в РЭМД
	 */
	protected $doc_classes = array(
		3 => 'EvnPL',
		4 => 'EvnRecept',
		6 => 'EvnPLStom',
		11 => 'EvnVizitPL',
		13 => 'EvnVizitPLStom',
		22 => 'EvnUslugaCommon',
		27 => 'EvnDirection',
		30 => 'EvnPS',
		32 => 'EvnSection',
		47 => 'EvnUslugaPar',
		48 => 'EvnVK',
		71 => 'EvnPrescrMse',
		72 => 'EvnMse',
		78 => 'EvnLabRequest',
		160 => 'EvnUslugaTelemed',
		180 => 'EvnReceptGeneral',
		190 => 'EvnPLDispDriver'
	);

	/**
	 * Описания всех документов, которые можно подписываться
	 */
	protected $documents = array(
		'EvnPL' => array(
			'docName' => 'ТАП', // наименование
			'objectName' => 'EvnPL', // объект в БД
			'viewName' => 'v_EvnPL', // вьюха для выборки даты и номера
			'numberField' => 'EvnPL_NumCard', // поле номера
			'dateField' => 'EvnPL_setDT', // поле даты
			'idField' => 'EvnPL_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'f025-1u_all.rptdesign', // отчёт для подписи
					'reportParams' => '&prmFntPnt=1&prmBckPnt=1&s={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'EvnPLStom' => array(
			'docName' => 'Стомат. ТАП', // наименование
			'objectName' => 'EvnPLStom', // объект в БД
			'viewName' => 'v_EvnPLStom', // вьюха для выборки даты и номера
			'numberField' => 'EvnPLStom_NumCard', // поле номера
			'dateField' => 'EvnPLStom_setDT', // поле даты
			'idField' => 'EvnPLStom_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'f025-1u_all.rptdesign', // отчёт для подписи
					'reportParams' => '&prmFntPnt=1&prmBckPnt=1&s={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'EvnRecept' => array(
			'docName' => 'Льготный рецепт', // наименование
			'objectName' => 'EvnRecept', // объект в БД
			'viewName' => 'v_EvnRecept', // вьюха для выборки даты и номера
			'numberField' => 'EvnRecept_Num', // поле номера
			'dateField' => 'EvnRecept_setDT', // поле даты
			'idField' => 'EvnRecept_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'EvnReceptPrint4_2016_new.rptdesign', // отчёт для подписи
					'reportParams' => '&paramEvnRecept={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'EvnReceptGeneral' => array(
			'docName' => 'Рецепт', // наименование
			'objectName' => 'EvnReceptGeneral', // объект в БД
			'viewName' => 'v_EvnReceptGeneral', // вьюха для выборки даты и номера
			'numberField' => 'EvnReceptGeneral_Num', // поле номера
			'dateField' => 'EvnReceptGeneral_setDT', // поле даты
			'idField' => 'EvnReceptGeneral_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'Dlo_EvnRecept_model', // модель в которой есть функция печати
					'printMethod' => 'getEvnReceptGeneralJSON' // метод печати в модели
				)
			)
		),
		'EvnDirection' => array(
			'docName' => 'Направление', // наименование
			'objectName' => 'EvnDirection', // объект в БД
			'viewName' => 'v_EvnDirection_all', // вьюха для выборки даты и номера
			'numberField' => 'EvnDirection_Num', // поле номера
			'dateField' => 'EvnDirection_setDT', // поле даты
			'idField' => 'EvnDirection_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'printEvnDirection.rptdesign', // отчёт для подписи
					'reportParams' => '&paramEvnDirection={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'PersonDisp' => array(
			'docName' => 'Контрольная карта диспансерного наблюдения', // наименование
			'objectName' => 'PersonDisp', // объект в БД
			'viewName' => 'v_PersonDisp', // вьюха для выборки даты и номера
			'numberField' => 'PersonDisp_NumCard', // поле номера
			'dateField' => 'PersonDisp_begDate', // поле даты
			'idField' => 'PersonDisp_id', // поле идентификатора
			'signField' => 'PersonDisp_IsSignedEP', // поле с признаком подписи
			'signDateField' => 'PersonDisp_signDate', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'PersonDisp', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'PersonDispCard.rptdesign', // отчёт для подписи
					'reportParams' => '&paramPersonDisp={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'EvnStickStudent' => array(
			'docName' => 'Справка учащегося', // наименование
			'objectName' => 'EvnStickStudent', // объект в БД
			'viewName' => 'v_EvnStickStudent', // вьюха для выборки даты и номера
			'numberField' => 'EvnStickStudent_Num', // поле номера
			'dateField' => 'EvnStickStudent_setDT', // поле даты
			'idField' => 'EvnStickStudent_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'EvnStickStudent.rptdesign', // отчёт для подписи
					'reportParams' => '&paramEvnStickStudent={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'EvnXml' => array(
			'docName' => 'Протокол', // наименование
			'objectName' => 'EvnXml', // объект в БД
			'viewName' => 'v_EvnXml', // вьюха для выборки даты и номера
			'numberField' => '0', // поле номера
			'dateField' => 'EvnXml_updDT', // поле даты
			'idField' => 'EvnXml_id', // поле идентификатора
			'signField' => 'EvnXml_IsSigned', // поле с признаком подписи
			'signDateField' => 'EvnXml_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'EvnXml' // таблица для апдейта признака подписи
		),
		'EvnMse' => array(
			'docName' => 'Обратный талон', // наименование
			'objectName' => 'EvnMse', // объект в БД
			'viewName' => 'v_EvnMse', // вьюха для выборки даты и номера
			'numberField' => 'EvnMse_NumAct', // поле номера
			'dateField' => 'EvnMse_setDT', // поле даты
			'idField' => 'EvnMse_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'Mse_model', // модель в которой есть функция печати
					'printMethod' => 'printEvnMse' // метод печати в модели
				)
			)
		),
		'EvnVK' => array(
			'docName' => 'Протокол ВК', // наименование
			'objectName' => 'EvnVK', // объект в БД
			'viewName' => 'v_EvnVK', // вьюха для выборки даты и номера
			'numberField' => 'EvnVK_NumProtocol', // поле номера
			'dateField' => 'EvnVK_setDT', // поле даты
			'idField' => 'EvnVK_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'ClinExWork_model', // модель в которой есть функция печати
					'printMethod' => 'printEvnVK_Perm' // метод печати в модели
				)
			)
		),
		'EvnPrescrMse' => array(
			'docName' => 'Направление на МСЭ', // наименование
			'objectName' => 'EvnPrescrMse', // объект в БД
			'viewName' => 'v_EvnPrescrMse', // вьюха для выборки даты и номера
			'numberField' => 'EvnPrescrMse_id', // поле номера
			'dateField' => 'EvnPrescrMse_setDT', // поле даты
			'idField' => 'EvnPrescrMse_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'EMDHL7OutDoc_model', // модель в которой есть функция печати
					'printMethod' => 'PrintHL7', 	// метод печати в модели
					'documentCode' => 34				//1.2.643.5.1.13.13.99.2.195 - по фед справочнику
				)
			)
		),
		'EvnUslugaPar' => array(
			'docName' => 'Протокол лабораторного исследования', // наименование
			'objectName' => 'EvnUslugaPar', // объект в БД
			'viewName' => 'v_EvnUslugaPar', // вьюха для выборки даты и номера
			'numberField' => 'EvnUslugaPar_id', // поле номера
			'dateField' => 'EvnUslugaPar_setDT', // поле даты
			'idField' => 'EvnUslugaPar_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'reportName' => 'EvnParCard_list.rptdesign', // отчёт для подписи
					'reportParams' => '&paramEvnUslugaPar={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'ReportRun' => array(
			'docName' => 'Отчёт', // наименование
			'objectName' => 'ReportRun', // объект в БД
			'schemaName' => 'rpt', // схема в БД
			'viewName' => 'v_ReportRun', // вьюха для выборки даты и номера
			'numberField' => 'ReportRun_id', // поле номера
			'dateField' => 'ReportRun_insDT', // поле даты
			'idField' => 'ReportRun_id', // поле идентификатора
			'signField' => 'ReportRun_IsSigned', // поле с признаком подписи
			'signDateField' => 'ReportRun_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'ReportRun', // таблица для апдейта признака подписи
			'addStampAfter' => true, // добавление штампа после подписания (при просмотре документа)
			'printParams' => array(
				array(
					'printModel' => 'ReportRun_model', // модель в которой есть функция печати
					'printMethod' => 'getReportForSign' // метод печати в модели
				)
			)
		),
		'EvnPS' => array(
			'docName' => 'КВС', // наименование
			'objectName' => 'EvnPS', // объект в БД
			'viewName' => 'v_EvnPS', // вьюха для выборки даты и номера
			'numberField' => 'EvnPS_NumCard', // поле номера
			'dateField' => 'EvnPS_setDT', // поле даты
			'idField' => 'EvnPS_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'addStampAfter' => true, // добавление штампа после подписания (при просмотре документа)
			'printParams' => array(
				array(
					'printModel' => 'EvnPS_model', // модель в которой есть функция печати
					'printMethod' => 'printEvnPS' // метод печати в модели
				)
			)
		),
		'EvnPLDispDriver' => array(
			'docName' => 'Случай медицинского освидетельствования водителя', // наименование
			'objectName' => 'EvnPLDispDriver', // объект в БД
			'viewName' => 'v_EvnPLDispDriver', // вьюха для выборки даты и номера
			'numberField' => 'EvnPLDispDriver_Num', // поле номера
			'dateField' => 'EvnPLDispDriver_setDT', // поле даты
			'idField' => 'EvnPLDispDriver_id', // поле идентификатора
			'signField' => 'Evn_IsSigned', // поле с признаком подписи
			'signDateField' => 'Evn_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'Evn', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'EvnPLDispDriver_model', // модель в которой есть функция печати
					'printMethod' => 'printEvnPLDispDriverHL7' // метод печати в модели
				)
			)
		),
		'BirthSvid' => array(
			'docName' => 'Свидетельство о рождении', // наименование
			'objectName' => 'BirthSvid', // объект в БД
			'viewName' => 'v_BirthSvid', // вьюха для выборки даты и номера
			'numberField' => 'BirthSvid_Num', // поле номера
			'dateField' => 'BirthSvid_GiveDate', // поле даты
			'idField' => 'BirthSvid_id', // поле идентификатора
			'signField' => 'BirthSvid_IsSigned', // поле с признаком подписи
			'signDateField' => 'BirthSvid_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'BirthSvid', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'MedSvid_model', // модель в которой есть функция печати
					'printMethod' => 'printBirthSvidHL7' // метод печати в модели
				)
			)
		),
		'DeathSvid' => array(
			'docName' => 'Свидетельство о смерти', // наименование
			'objectName' => 'DeathSvid', // объект в БД
			'viewName' => 'v_DeathSvid', // вьюха для выборки даты и номера
			'numberField' => 'DeathSvid_Num', // поле номера
			'dateField' => 'DeathSvid_GiveDate', // поле даты
			'idField' => 'DeathSvid_id', // поле идентификатора
			'signField' => 'DeathSvid_IsSigned', // поле с признаком подписи
			'signDateField' => 'DeathSvid_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'DeathSvid', // таблица для апдейта признака подписи
			'printParams' => array(
				array(
					'printModel' => 'MedSvid_model', // модель в которой есть функция печати
					'printMethod' => 'printDeathSvidHL7' // метод печати в модели
				)
			)
		),
		'PersonPrivilegeReq' => array(
			'docName' => 'Запрос', // наименование
			'objectName' => 'PersonPrivilegeReq', // объект в БД
			'viewName' => 'v_PersonPrivilegeReq', // вьюха для выборки даты и номера
			'numberField' => 'PersonPrivilegeReq_id', // поле номера
			'dateField' => 'PersonPrivilegeReq_setDT', // поле даты
			'idField' => 'PersonPrivilegeReq_id', // поле идентификатора
			'signField' => 'PersonPrivilegeReq_IsSigned', // поле с признаком подписи
			'signDateField' => 'PersonPrivilegeReq_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'PersonPrivilegeReq', // таблица для апдейта признака подписи
			'addStampAfter' => true, // добавление штампа после подписания (при просмотре документа)
			'printParams' => array(
				array(
					'reportName' => 'PersonPrivilegeReqQuery.rptdesign', // отчёт для подписи
					'reportParams' => '&paramPersonPrivilegeReq={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
		'PersonPrivilegeReqAns' => array(
			'docName' => 'Ответ', // наименование
			'objectName' => 'PersonPrivilegeReqAns', // объект в БД
			'viewName' => 'v_PersonPrivilegeReqAns', // вьюха для выборки даты и номера
			'numberField' => 'PersonPrivilegeReqAns_id', // поле номера
			'dateField' => 'PersonPrivilegeReqAns_insDT', // поле даты
			'idField' => 'PersonPrivilegeReqAns_id', // поле идентификатора
			'signField' => 'PersonPrivilegeReqAns_IsSigned', // поле с признаком подписи
			'signDateField' => 'PersonPrivilegeReqAns_signDT', // поле с датой подписи
			'signUserField' => 'pmUser_signID', // поле с пользователем подписи
			'signObject' => 'PersonPrivilegeReqAns', // таблица для апдейта признака подписи
			'addStampAfter' => true, // добавление штампа после подписания (при просмотре документа)
			'printParams' => array(
				array(
					'reportName' => 'PersonPrivilegeReqAnswer.rptdesign', // отчёт для подписи
					'reportParams' => '&paramPersonPrivilegeReqAns={docId}&paramEMDCertificate_id={certId}', // параметры отчёта
					'reportFormat' => 'pdf' // формат отчёта
				)
			)
		),
	);

	/**
	 * Получение журнала запросов РЭМД ЕГИСЗ
	 * @param array $incomingParams
	 * @return mixed
	 */
	function loadEMDJournalQuery(array $incomingParams) {
		$filters = "";
		$queryParams = array();

		if ( empty($incomingParams['EMDJournalQuery_OutDT'][0]) ) {
			//по умолчанию текущая дата
			$incomingParams['EMDJournalQuery_OutDT'][0]=date("Y-m-d");
		}
		if ( empty($incomingParams['EMDJournalQuery_OutDT'][1]) ) {
			$incomingParams['EMDJournalQuery_OutDT'][1]=$incomingParams['EMDJournalQuery_OutDT'][0];
		}
		$filters .= ' and emdjq."EMDJournalQuery_OutDT" >= :EMDJournalQuery_OutDT_From 	and emdjq."EMDJournalQuery_OutDT"  <= :EMDJournalQuery_OutDT_To';
		$queryParams['EMDJournalQuery_OutDT_From'] = $incomingParams['EMDJournalQuery_OutDT'][0]." 00:00:00";
		$queryParams['EMDJournalQuery_OutDT_To'] = $incomingParams['EMDJournalQuery_OutDT'][1]." 23:59:59";
		
		if ( isset($incomingParams['EMDQueryType_id']) ) {
			$filters .= ' and emdjq."EMDQueryType_id" = :EMDQueryType_id';
			$queryParams['EMDQueryType_id'] = (int)$incomingParams['EMDQueryType_id'];
		}
		if ( isset($incomingParams['EMDDocumentTypeLocal_id']) ) {
			$filters .= ' and emdr."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id';
			$queryParams['EMDDocumentTypeLocal_id'] = (int)$incomingParams['EMDDocumentTypeLocal_id'];
		}
		if ( isset($incomingParams['EMDRegistry_Num']) ) {
			$filters .= ' and emdr."EMDRegistry_Num" = :EMDRegistry_Num';
			$queryParams['EMDRegistry_Num'] = $incomingParams['EMDRegistry_Num'];
		}
		if ( isset($incomingParams['EMDQueryStatus_id']) && (int)$incomingParams['EMDQueryStatus_id'] >=0 ) {
			$filters .= ' and emdjq."EMDQueryStatus_id" = :EMDQueryStatus_id';
			$queryParams['EMDQueryStatus_id'] =(int) $incomingParams['EMDQueryStatus_id'];
		}
		
		$filters .= ' and emdr."Lpu_id" = :EMDLpu_id';
		if ( isset($incomingParams['EMDLpu_id']) && $incomingParams['EMDLpu_id'] !== 'null' && isSuperAdmin()) {
			$queryParams['EMDLpu_id']= $incomingParams['EMDLpu_id'];
		} else {
			$queryParams['EMDLpu_id']=(int)$incomingParams['session']['lpu_id'];
		}

		//пагинация
		if ( !empty($incomingParams['start']) ) {
			$start=(int)$incomingParams['start'];
		} else {
			$start=0;
		}
		if ( !empty($incomingParams['limit']) ) {
			$limit=(int)$incomingParams['limit'];
		} else {
			$limit=0;
		}

		$query = '
			SELECT
				emdjq."EMDJournalQuery_id",
				emdjq."EMDRegistry_id",
				emdv."EMDVersion_FilePath",
				emddtl."EMDDocumentTypeLocal_Name",
				emdqt."EMDQueryType_Name",
				emdr."EMDRegistry_Num",
				to_char(emdjq."EMDJournalQuery_OutDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDJournalQuery_OutDT_RU",
				emdqs."EMDQueryStatus_Name",
				--emdjq."EMDJournalQuery_OutParam",
				--emdjq."EMDJournalQuery_InParam",
				to_char(emdjq."EMDJournalQuery_InDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDJournalQuery_InDT",
				emdr."Lpu_id",
				emdjq."EMDJournalQuery_OutDT",
				count(*) OVER() AS "totalCount" 		/*современная возможность получения кол-ва записей без учета LIMIT*/
				
			FROM
				"EMD"."EMDJournalQuery" emdjq
				left join "EMD"."EMDRegistry" emdr on emdr."EMDRegistry_id" = emdjq."EMDRegistry_id"
				left join "EMD"."EMDDocumentTypeLocal" emddtl on emddtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
				left join "EMD"."EMDQueryType" emdqt on emdqt."EMDQueryType_id" = emdjq."EMDQueryType_id"
				left join "EMD"."EMDQueryStatus" emdqs on emdqs."EMDQueryStatus_id" = emdjq."EMDQueryStatus_id"
				left join lateral (
					select
						"EMDVersion_FilePath"
					from
						"EMD"."EMDVersion"
					where
						"EMDRegistry_id" = emdjq."EMDRegistry_id" order by "EMDRegistry_id" desc
					fetch first 1 row only
				) emdv on true
			WHERE
				1=1
				' . $filters . ' '.' limit '.$limit.' OFFSET '.$start;
		$queryResults = $this->queryResult($query, $queryParams, $this->emddb);
		if (!empty($queryResults) ) {
			$lpu_ids = array();
			foreach ($queryResults as $doc) {
				if (!empty($doc["Lpu_id"]) && !in_array($doc["Lpu_id"],$queryResults))
					$lpu_ids[] = $doc["Lpu_id"];
			}
			
			if (count($lpu_ids) > 0) {
				$ids = implode(',', $lpu_ids);

				$query = "
					SELECT
						vl.Lpu_id,
						vl.Lpu_Name
					FROM
						v_Lpu vl
					WHERE
						vl.Lpu_id in ({$ids})
				";

				//#190815 - Вывод инфо о МО
				$lpu_data = $this->queryResult($query, array());
				$lpu_map = array_column($lpu_data, 'Lpu_Name', 'Lpu_id');

				$result = array();
				foreach ($queryResults as $obj) {
					if (array_key_exists($obj['Lpu_id'], $lpu_map)) {
						$obj['Lpu_Name'] = $lpu_map[$obj['Lpu_id']];
						$result[] = $obj;
					}
				}
				$result=[
					"data"=>$result,
					"totalCount"=>$queryResults[0]["totalCount"]
				];
				return $result;
			}
		}
		//аустой ответ
		return [
			"data"=>[],
			"totalCount"=>0
		];
	}

	/**
	 * Получение одной записи журнала запросов РЭМД ЕГИСЗ
	 * @param array $incomingParams
	 * @return mixed
	 */
	function loadEMDJournalQueryDetal(array $data) {

		$query = '
			SELECT
				emdjq."EMDJournalQuery_id",
				emdjq."EMDRegistry_id",
				emdv."EMDVersion_FilePath",
				emdr."EMDDocumentTypeLocal_id",
				emdjq."EMDQueryType_id",
				to_char(emdjq."EMDJournalQuery_OutDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDJournalQuery_OutDT_RU",
				emdjq."EMDQueryStatus_id",
				emdjq."EMDJournalQuery_OutParam",
				emdjq."EMDJournalQuery_InParam",
				to_char(emdjq."EMDJournalQuery_InDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDJournalQuery_InDT",
				emdr."Lpu_id",
				emdr."EMDRegistry_Num"
				
			FROM
				"EMD"."EMDJournalQuery" emdjq
				left join "EMD"."EMDRegistry" emdr on emdr."EMDRegistry_id" = emdjq."EMDRegistry_id"
				left join lateral (
					select
						"EMDVersion_FilePath"
					from
						"EMD"."EMDVersion"
					where
						"EMDRegistry_id" = emdjq."EMDRegistry_id" order by "EMDRegistry_id" desc
					fetch first 1 row only
				) emdv on true
			WHERE
				emdjq."EMDJournalQuery_id"= :EMDJournalQuery_id';

		return  $this->queryResult($query, $data, $this->emddb);
	}

	/**
	 * Получение ошибок для журнала запросов РЭМД ЕГИСЗ
	 */
	function loadEMDQueryError($data) {
		$query = '
			SELECT
				emdqe."EMDQueryError_id",
				emdel."EMDErrorList_Code",
				emdel."EMDErrorList_Name"
			FROM
				"EMD"."EMDQueryError" emdqe
				left join "EMD"."EMDErrorList" emdel on emdel."EMDErrorList_id" = emdqe."EMDErrorList_id"
			WHERE
				emdqe."EMDJournalQuery_id" = :EMDJournalQuery_id
		';

		return $this->queryResult($query, array(
			'EMDJournalQuery_id' => $data['EMDJournalQuery_id']
		), $this->emddb);
	}

	/**
	 * Получение списка сертификатов пользователя
	 */
	function loadEMDCertificateList($data) {

		$filter = "";

		if (!empty($data['isMOSign']) && !empty($data['session']['lpu_id'])) {

			$ogrn = $this->getFirstResultFromQuery('
					select
						Lpu_OGRN as "Lpu_OGRN"
					from v_Lpu where Lpu_id = :Lpu_id
					limit 1
				', array(
				'Lpu_id' => $data['session']['lpu_id']
			));

			if (!empty($ogrn)) {
				$filter = ' and "EMDCertificate_OGRN" = :EMDCertificate_OGRN ';
				$data['EMDCertificate_OGRN'] = $ogrn;
			}
		}

		$query = '
			SELECT
				"EMDCertificate_id",
				"EMDCertificate_Name",
				to_char("EMDCertificate_begDT", \'DD.MM.YYYY\') as "EMDCertificate_begDT",
				to_char("EMDCertificate_endDT", \'DD.MM.YYYY\') as "EMDCertificate_endDT",
				"EMDCertificate_CommonName",
				"EMDCertificate_SHA1",
				"EMDCertificate_OpenKey",
				"EMDCertificate_IsNotUse"
			FROM
				"EMD"."EMDCertificate"
			WHERE
				"pmUser_id" = :pmUser_id
				and coalesce("EMDCertificate_deleted", 1) = 1 '
			.$filter;

		$certs = $this->queryResult($query, $data, $this->emddb);
		$response = $certs;

		return $response;
	}

	/**
	 * Получение cправочника «РЭМД ЕГИСЗ. Виды регистрируемых электронных медицинских документов»
	 */
	function getDocumentTypeList($data) {
		$query = '
			SELECT
				"EMDDocumentType_id",
				"EMDDocumentType_Name"
			FROM "EMD"."EMDDocumentType"
			WHERE
				coalesce("EMDDocumentType_begRegDT", :curDate) <= :curDate
				and coalesce("EMDDocumentType_endRegDT", :curDate) >= :curDate
		';
		$result = $this->queryResult($query, [
			'curDate' => date('Y-m-d')
		], $this->emddb);
		return $result;
	}

	/**
	 * Получение списка документов
	 */
	function getDocumentTypeLocalList($data) {

		if (!empty($data['isGlobalAndActive']))
		{
			$from = ' INNER JOIN "EMD"."EMDDocumentType" dt ON dt."EMDDocumentType_id" = dtl."EMDDocumentType_id"';
			$where = ' AND dt."EMDDocumentType_endRegDT" IS NULL';
		}
		else
			$from = $where = "";

		$query = '
			SELECT
				dtl."EMDDocumentTypeLocal_id",
				dtl."EMDDocumentTypeLocal_Name"
			FROM
				"EMD"."EMDDocumentTypeLocal" dtl 
				INNER JOIN "EMD"."EMDDocumentType" dt ON dt."EMDDocumentType_id" = dtl."EMDDocumentType_id"
			WHERE
				dtl."EMDDocumentTypeLocal_IsREMD" = 2 AND dtl."EMDDocumentTypeLocal_endDate" IS NULL
		';

		$result = $this->queryResult($query, $data, $this->emddb);
		return $result;
	}

	/**
	 * Формирование запроса для результирующиего набора поиска документов РМИС
	 */
	function makeQuerySearchScope($data) {

		$scopes = array(); $query_scope = array();

		$docType = $data['docType'];
		$filter = $data['filter'];
		$join = $data['join'];

		$query_scope['EvnPL'][] = "
		-- ТАП
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_did as MedStaffFact_id,
				num.Doc_Num,
				e.EvnPL_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnPL e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnPL_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_did
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnPL_NumCard as varchar) as Doc_Num FROM v_EvnPL WHERE EvnPL_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 3
				{$filter}
			limit 100)
		";

		$query_scope['EvnVizitPL'][] = "
		-- Посещение поликлиники
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				e.EvnVizitPL_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnVizitPL e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnVizitPL_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnVizitPL_id as varchar) as Doc_Num FROM v_EvnVizitPL WHERE EvnVizitPL_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 11
				{$filter}
			limit 100)
		";

		$query_scope['EvnRecept'][] = "
		-- Выписка льготного рецепта
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				0 as MedStaffFact_id,
				num.Doc_Num,
				e.EvnRecept_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnRecept e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnRecept_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			{$join}
			left join lateral (
				SELECT (EvnRecept_Ser || EvnRecept_Num) as Doc_Num FROM v_EvnRecept WHERE EvnRecept_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 4
				{$filter}
			limit 100)
		";

		$query_scope['EvnReceptGeneral'][] = "
		-- Выписка рецепта
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				0 as MedStaffFact_id,
				num.Doc_Num,
				e.EvnReceptGeneral_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnReceptGeneral e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnReceptGeneral_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			{$join}
			left join lateral (
				SELECT (EvnReceptGeneral_Ser || EvnReceptGeneral_Num) as Doc_Num FROM v_EvnReceptGeneral WHERE EvnReceptGeneral_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 180
				{$filter}
			limit 100)
		";

		$query_scope['EvnDirection'][] = "
		-- Выписка направлений
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				e.EvnDirection_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnDirection_all e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnDirection_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnDirection_Num as varchar) as Doc_Num FROM v_EvnDirection_all WHERE EvnDirection_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 27
				and coalesce(e.EvnStatus_id, 16) not in (12,13)
				{$filter}
			limit 100)
		";

		$query_scope['EvnVK'][] = "
		-- Клинико-экспертная работа и протокол ВК
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				0 as MedStaffFact_id,
				num.Doc_Num,
				e.EvnVK_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnVK e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnVK_id
			LEFT JOIN v_MedService ms on ms.MedService_id = e.MedService_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			{$join}
			left join lateral (
				SELECT cast(EvnVK_NumProtocol as varchar) as Doc_Num FROM v_EvnVK WHERE EvnVK_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 48
				{$filter}
			limit 100)
		";

		$query_scope['EvnPrescrMse'][] = "
		-- Специф. сведения о назначении на МСЭ
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				0 as MedStaffFact_id,
				num.Doc_Num,
				e.EvnPrescrMse_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnPrescrMse e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnPrescrMse_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_sid
			{$join}
			left join lateral (
				SELECT cast(EvnPrescrMse_id as varchar) as Doc_Num FROM v_EvnPrescrMse WHERE EvnPrescrMse_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
			and e.EvnClass_id = 71
				{$filter}
			limit 100)
		";

		$query_scope['EvnUslugaPar'][] = "
		-- Протокол лабораторной услуги
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				ed.MedStaffFact_id,
				num.Doc_Num,
				e.EvnUslugaPar_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM
				v_EvnUslugaPar e
				INNER JOIN v_Evn evn on evn.Evn_id = e.EvnUslugaPar_id
				inner join v_EvnLabSample els on els.EvnLabSample_id = e.EvnLabSample_id
				LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
				LEFT JOIN v_EvnDirection_all ed on ed.EvnDirection_id = e.EvnDirection_id
				LEFT JOIN v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
				{$join}
				inner join lateral (
					SELECT cast(EvnUslugaPar_id as varchar) as Doc_Num FROM v_EvnUslugaPar WHERE EvnUslugaPar_id = evn.Evn_id limit 1
				) num on true
			WHERE (1=1)
				and e.EvnClass_id = 47
				and e.EvnDirection_id is not null
				{$filter}
			limit 100)
		";

		$query_scope['EvnMse'][] = "
		-- Обратный талон
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				null as MedStaffFact_id,
				num.Doc_Num,
				e.EvnMse_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnMse e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnMse_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedService ms on ms.MedService_id = e.MedService_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnMse_NumAct as varchar) as Doc_Num FROM v_EvnMse WHERE EvnMse_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 72
				{$filter}
			limit 100)
		";

		$query_scope['EvnPL'][3][] = "
		-- Протокол консультации
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_did as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnPL e on e.EvnPL_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_did
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 3
				and x.XmlType_id = 3
				{$filter}
			limit 100)
		";

		$query_scope['EvnSection'][8][] = "
		-- Осмотры
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnSection e on e.EvnSection_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 32
				and x.XmlType_id = 8
				{$filter}
			limit 100)
		";

		$query_scope['EvnSection'][9][] = "
		-- Дневниковые записи
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnSection e on e.EvnSection_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 32
				and x.XmlType_id = 9
				{$filter}
			limit 100)
		";

		$query_scope['EvnSection'][10][] = "
		-- Эпикризы
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnSection e on e.EvnSection_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 32
				and x.XmlType_id = 10
				{$filter}
			limit 100)
		";
		
		$query_scope['EvnSection'][8][] = "
		-- Осмотры
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnSection e on e.EvnSection_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 32
				and x.XmlType_id = 8
				{$filter}
			limit 100)
		";

		$query_scope['EvnSection'][9][] = "
		-- Дневниковые записи
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnSection e on e.EvnSection_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 32
				and x.XmlType_id = 9
				{$filter}
			limit 100)
		";

		$query_scope['EvnSection'][10][] = "
		-- Эпикризы
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id as MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnSection e on e.EvnSection_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 32
				and x.XmlType_id = 10
				{$filter}
			limit 100)
		";

		$query_scope['EvnVizitPL'][3][] = "
		-- Справка
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnVizitPL e on e.EvnVizitPL_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 11
				and x.XmlType_id = 3
				{$filter}
			limit 100)
		";

		$query_scope['EvnVizitPL'][2][7] = "
		-- Справка студенту / учащемуся / ребенку о болезни/карантине и прочих причинах отсутствия
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnVizitPL e on e.EvnVizitPL_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			LEFT JOIN v_XmlTemplate tpl on tpl.XmlTemplate_id = x.XmlTemplate_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 11
				and x.XmlType_id = 2
				and tpl.XmlTypeKind_id = 7
				{$filter}
			limit 100)
		";

		$query_scope['EvnVizitPLStom'][2][] = "
		-- Справка
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnVizitPLStom e on e.EvnVizitPLStom_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 13
				and x.XmlType_id = 2
				{$filter}
			limit 100)
		";

		$query_scope['EvnVizitPLStom'][2][7] = "
		-- Справка студенту / учащемуся / ребенку о болезни/карантине и прочих причинах отсутствия
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnVizitPLStom e on e.EvnVizitPLStom_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_id
			LEFT JOIN v_XmlTemplate tpl on tpl.XmlTemplate_id = x.XmlTemplate_id
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 13
				and x.XmlType_id = 2
				and tpl.XmlTypeKind_id = 7
				{$filter}
			limit 100)
		";

		$query_scope['EvnUslugaCommon'][4][] = "
		-- Протокол консультации
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnUslugaCommon e on e.EvnUslugaCommon_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_uid
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 22
				and x.XmlType_id = 4
				{$filter}
			limit 100)
		";

		$query_scope['EvnUslugaPar'][4][] = "
		-- Протокол инструментальных исследований
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnUslugaPar e on e.EvnUslugaPar_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_uid
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 47
				and x.XmlType_id = 4
				{$filter}
			limit 100)
		";

		$query_scope['EvnUslugaTelemed'][1][] = "
		-- Протокол консультации с применением телемедицинских технологий
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_id,
				num.Doc_Num,
				x.EvnXml_IsSigned as IsSigned,
				x.EvnXml_id
			FROM v_EvnXml x
			INNER JOIN v_Evn evn on evn.Evn_id = x.Evn_id
			INNER JOIN v_EvnUslugaTelemed e on e.EvnUslugaTelemed_id = evn.Evn_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = e.LpuSection_uid
			{$join}
			left join lateral (
				SELECT cast(EvnXml_id as varchar) as Doc_Num FROM v_EvnXml WHERE EvnXml_id = x.EvnXml_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 160
				and x.XmlType_id = 1
				{$filter}
			limit 100)
		";

		$query_scope['EvnPS'][] = "
		-- КВС
			(SELECT --flag
				evn.Evn_id,
				ls.LpuSection_id,
				ls.LpuBuilding_id,
				e.MedStaffFact_did as MedStaffFact_id,
				num.Doc_Num,
				e.EvnPS_IsSigned as IsSigned,
				CAST(null as bigint) as EvnXml_id
			FROM v_EvnPS e
			INNER JOIN v_Evn evn on evn.Evn_id = e.EvnPS_id
			LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
			LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = e.MedStaffFact_did
			LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$join}
			left join lateral (
				SELECT cast(EvnPS_NumCard as varchar) as Doc_Num FROM v_EvnPS WHERE EvnPS_id = evn.Evn_id limit 1
			) num on true
			WHERE (1=1)
				and e.EvnClass_id = 30
				{$filter}
			limit 100)
		";

		foreach ($docType as $scope) {
			$query = "";

			$merged_data = "SELECT
				{$scope['EMDDocumentTypeLocal_id']} as EMDDocumentTypeLocal_id,
				'{$scope['EMDDocumentTypeLocal_Name']}' as EMDDocumentTypeLocal_Name,
			";

			$cls_key = $this->doc_classes[$scope['EvnClass_id']];
			$type_key = 0;

			if (!empty($scope['XmlType_id'])) {

				$type_key = $scope['XmlType_id'];
				$subtype_key = 0;

				if (!empty($scope['XmlTypeKind_id'])) {

					$subtype_key = $scope['XmlTypeKind_id'];

					if (isset($query_scope[$cls_key][$type_key][$subtype_key])) {
						$query = $query_scope[$cls_key][$type_key][$subtype_key];
					}

				} else {

					if (isset($query_scope[$cls_key][$type_key])) {
						$query = $query_scope[$cls_key][$type_key][$subtype_key];
					}
				}

			} else {
				if (isset($query_scope[$cls_key][$type_key])) {
					$query = $query_scope[$cls_key][$type_key];
				}
			}

			if (!empty($query)) {
				$query = str_replace("SELECT --flag", $merged_data, $query);
				$scopes[] = $query;
			}
		}

		return $scopes;
	}

	/**
	 * Получение списка РМИС документов по фильтру
	 */
	function searchDocs($data) {

		$scope = array(); $response = array();

		// только супер админ может менять ЛПУ в фильтре
		if (!isSuperadmin()) {
			$user_lpu = !empty($data['session']['lpu_id']) ? $data['session']['lpu_id'] : 0;
			if ($data['Lpu_id'] != $user_lpu) return $response;
		}

		// фильтр по справочнику документов постгре (формируем область видимости)
		if (!empty($data['EMDDocumentTypeLocal_id'])) {

			$query = '
				SELECT
					dtl."EMDDocumentTypeLocal_id",
					dtl."EMDDocumentTypeLocal_Name",
					etype."EvnClass_id",
					etype."XmlType_id",
					etype."XmlTypeKind_id"
				FROM "EMD"."EMDDocumentTypeLocal" dtl
				INNER JOIN "EMD"."EMDEvnDocumentType" etype on etype."EMDDocumentTypeLocal_id" = dtl."EMDDocumentTypeLocal_id"
				WHERE dtl."EMDDocumentTypeLocal_id" in ('.$data['EMDDocumentTypeLocal_id'].')
			';

			$scope = $this->queryResult($query, $data, $this->emddb);
			if (empty($scope)) return array();
		}

		$join = "";
		$filter = " and evn.Person_id IS NOT NULL ";

		if (!empty($data['Lpu_id'])) {
			$filter .= ' and evn.Lpu_id = :Lpu_id ';
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= ' and ls.LpuBuilding_id = :LpuBuilding_id ';
		}

		if (!empty($data['LpuSection_id'])) {
			$filter .= ' and ls.LpuSection_id = :LpuSection_id ';
		}

		if (!empty($data['MedPersonal_id'])) {
			$join = "
				LEFT JOIN v_pmUserCache pmuIns on pmuIns.PMUser_id = evn.pmUser_insID
				LEFT JOIN v_pmUserCache pmuUpd on pmuUpd.PMUser_id = evn.pmUser_updID
			";
			$filter .= ' and (pmuIns.MedPersonal_id = :MedPersonal_id
							or pmuUpd.MedPersonal_id = :MedPersonal_id) ';
		}

		if (!empty($data['Person_FIO'])) {

			$fullName = explode(' ',trim($data['Person_FIO']));

			if (!empty($fullName[0])) {
				$filter .= " and ps.Person_SurName ilike '%' || :Person_Surname || '%' ";
				$data['Person_Surname'] = $fullName[0];
			}

			if (!empty($fullName[1])) {
				$filter .= " and ps.Person_FirName ilike '%' || :Person_Firname || '%' ";
				$data['Person_Firname'] = $fullName[1];
			}

			if (!empty($fullName[2])) {
				$filter .= " and ps.Person_SecName ilike '%' || :Person_Secname || '%' ";
				$data['Person_Secname'] = $fullName[2];
			}
		}

		if (!empty($data['Doc_Num'])) {
			$filter .= " and num.Doc_Num ilike :Doc_Num || '%'";
		}

		if (!empty($data['Evn_insDT_period'])) {

			$ins_period = explode('—',$data['Evn_insDT_period']);
			$data['insBegDt'] = DateTime::createFromFormat('d.m.Y', trim($ins_period[0]))->format('Y-m-d');

			if (!empty($ins_period[1])) $data['insEndDt'] = DateTime::createFromFormat('d.m.Y', trim($ins_period[1]))->format('Y-m-d');
			else $data['insEndDt'] = $data['insBegDt'];

			$filter .= '
				and cast(evn.Evn_insDT as date) >= :insBegDt and cast(evn.Evn_insDT as date) <= :insEndDt
			';
		}

		if (!empty($data['Evn_updDT_period'])) {

			$upd_period = explode('—',$data['Evn_updDT_period']);
			$data['updBegDt'] = DateTime::createFromFormat('d.m.Y', trim($upd_period[0]))->format('Y-m-d');

			if (!empty($upd_period[1])) $data['updEndDt'] = DateTime::createFromFormat('d.m.Y', trim($upd_period[1]))->format('Y-m-d');
			else $data['updEndDt'] = $data['updBegDt'];

			$filter .= '
				and cast(evn.Evn_updDT as date) >= :updBegDt and cast(evn.Evn_updDT as date) <= :updEndDt
			';
		}

		$scopes = $this->makeQuerySearchScope(array(
			'docType' => $scope,
			'filter' => $filter,
			'join' => $join
		));

		if (!empty($scopes)) {
			$filter = "";
			if (!empty($data['isWithoutSign']) && $data['isWithoutSign'] === 'on') {
				$filter .= ' and d.IsSigned IS NULL ';
			}

			$scopes = implode(' UNION ALL ',$scopes);

			$query = "
				with doc(
					EMDDocumentTypeLocal_id,
					EMDDocumentTypeLocal_Name,
					Evn_id,
					LpuSection_id,
					LpuBuilding_id,
					MedStaffFact_id,
					Doc_Num,
					IsSigned,
					EvnXml_id
				) as ( {$scopes} )
				SELECT
					coalesce(cast(d.EvnXml_id as varchar), cast(d.Evn_id as varchar)) as \"EMDRegistry_ObjectID\",
					case when d.EvnXml_id is not null then 'EvnXml' else cls.EvnClass_SysNick end as \"EMDRegistry_ObjectName\",
					d.LpuSection_id as \"LpuSection_id\",
					d.LpuBuilding_id as \"LpuBuilding_id\",
					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.Doc_Num as \"Doc_Num\",
					d.EMDDocumentTypeLocal_id as \"EMDDocumentTypeLocal_id\",
					d.EMDDocumentTypeLocal_Name as \"EMDDocumentTypeLocal_Name\",
					msf.Person_Fio as \"MedPersonal_Fio\",
					evn.Lpu_id as \"Lpu_id\",
					evn.pmUser_insID as \"pmUser_insID\",
					evn.pmUser_updID as \"pmUser_updID\",
					(to_char(evn.Evn_insDT, 'dd.mm.yyyy') || ' ' || to_char(evn.Evn_insDT, 'HH24:MI:SS')) as \"Evn_insDT\",
					(to_char(evn.Evn_updDT, 'dd.mm.yyyy') || ' ' || to_char(evn.Evn_updDT, 'HH24:MI:SS')) as \"Evn_updDT\",
					d.IsSigned as \"IsSigned\",
					evn.Person_id as \"Person_id\",
					ps.Person_id as \"Person_id\",
					ps.Person_SurName || coalesce(' ' || ps.Person_FirName,'') || coalesce(' ' || ps.Person_SecName,'') as \"Person_FIO\",
					to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
					evn.Evn_pid as \"Evn_pid\",
					parent.EvnClass_Name as \"parent_EvnClass_Name\"
				FROM doc d
					INNER JOIN v_Evn evn on evn.Evn_id = d.Evn_id
					LEFT JOIN v_EvnClass cls on cls.EvnClass_id = evn.EvnClass_id
					LEFT JOIN v_PersonState ps on ps.Person_id = evn.Person_id
					LEFT JOIN v_Evn parent on parent.Evn_id = evn.Evn_pid
					LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = d.MedStaffFact_id
				where (1=1)
					{$filter}
				limit 100
			";

			//echo '<pre>',print_r(getDebugSQL($query, $data)),'</pre>'; die();
			$response = $this->queryResult($query, $data);

			// получаем список Evn_id (для джойна доков из РЭМД)
			if (!empty($response)) {

				$evn_list = array();
				$merged_response = array();

				foreach ($response as $val) {
					$merged_response[$val['EMDRegistry_ObjectID']] = $val;
					$evn_list[] = $val['EMDRegistry_ObjectID'];
				}

				$evn_list = implode(',',$evn_list);

				$query = '
					SELECT
						emdr."EMDRegistry_id",
						emdr."EMDRegistry_ObjectID",
						emdv."EMDVersion_actualDT"
					FROM "EMD"."EMDRegistry" emdr
					left join lateral (
						SELECT
							to_char(emdv."EMDVersion_actualDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDVersion_actualDT"
						FROM "EMD"."EMDVersion" emdv
						WHERE emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
						ORDER BY emdv."EMDVersion_actualDT" desc
						LIMIT 1
					) emdv on true
					WHERE emdr."EMDRegistry_ObjectID" in ('.$evn_list.')
				';

				$emd_docs = $this->queryResult($query, $data, $this->emddb);
				if (!empty($emd_docs)) {
					foreach ($emd_docs as $doc) {
						if (isset($merged_response[$doc['EMDRegistry_ObjectID']]))
							$merged_response[$doc['EMDRegistry_ObjectID']]['EMDRegistry_id'] = $doc['EMDRegistry_id'];
						$merged_response[$doc['EMDRegistry_ObjectID']]['EMDVersion_actualDT'] = $doc['EMDVersion_actualDT'];
					}
				}

				$response = $merged_response;
			}
		}

		return $response;
	}

	/**
	 * Поиск документов ЭМД по фильтру
	 */
	function EMDSearch($data) {

		$filter = ""; $join = ""; $versions_join = "";

		$filter .= ' and coalesce(emdr."EMDRegistry_deleted", 1) = 1';

		// только супер админ может менять ЛПУ в фильтре
		if (!isSuperadmin()) {
			$user_lpu = !empty($data['session']['lpu_id']) ? $data['session']['lpu_id'] : 0;
			if ($data['Lpu_id'] != $user_lpu) return array();
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= ' and emdr."Lpu_id" = :Lpu_id ';
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= ' and emdr."LpuBuilding_id" = :LpuBuilding_id ';
		}

		if (!empty($data['Person_FIO'])) {

			$fullName = explode(' ',trim($data['Person_FIO']));

			$p_filter = "";
			$search_params = array();

			if (!empty($fullName[0])) {
				$p_filter .= " and ps.Person_SurName ilike '%' || :Person_Surname || '%' ";
				$search_params['Person_Surname'] = $fullName[0];
			}

			if (!empty($fullName[1])) {
				$p_filter .= " and ps.Person_FirName ilike '%' || :Person_Firname || '%' ";
				$search_params['Person_Firname'] = $fullName[1];
			}

			if (!empty($fullName[2])) {
				$p_filter .= " and ps.Person_SecName ilike '%' || :Person_Secname || '%' ";
				$search_params['Person_Secname'] = $fullName[2];
			}

			// ищем id по фио в БД промеда
			$query = "
				SELECT ps.Person_id as \"Person_id\"
				FROM v_PersonState ps
				WHERE (1=1) {$p_filter}
				limit 1000
			";

			$search_result = $this->queryResult($query, $search_params);

			// эти id передаем в поисковый запрос
			if (!empty($search_result)) {
				$person_in = implode(',',array_column($search_result,'Person_id'));
				$filter .= ' and emdr."Person_id" in ('.$person_in.') ';
			} else return array();
		}

		if (!empty($data['EMDDocumentTypeLocal_id'])) {
			$filter .= ' and emdr."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id ';
		}

		if (!empty($data['EMDRegistry_Num'])) {
			$filter .= ' and emdr."EMDRegistry_Num" ilike :EMDRegistry_Num || \'%\' ';
		}

		if (!empty($data['EMDRegistry_EMDDate_period'])) {

			$emd_dates = explode('—',$data['EMDRegistry_EMDDate_period']);
			$data['emdBegDt'] = DateTime::createFromFormat('d.m.Y', trim($emd_dates[0]))->format('Y-m-d');

			if (!empty($emd_dates[1])) $data['emdEndDt'] = DateTime::createFromFormat('d.m.Y', trim($emd_dates[1]))->format('Y-m-d');
			else $data['emdEndDt'] = $data['emdBegDt'];

			$filter .= '
				and to_char(emdr."EMDRegistry_EMDDate", \'YYYY-MM-DD\') >= :emdBegDt
				and to_char(emdr."EMDRegistry_EMDDate", \'YYYY-MM-DD\') <= :emdEndDt
			';
		}

		if (!empty($data['EMDVersion_RegistrationDate_period'])) {

			$emd_dates = explode('—',$data['EMDVersion_RegistrationDate_period']);
			$data['regBegDt'] = DateTime::createFromFormat('d.m.Y', trim($emd_dates[0]))->format('Y-m-d');

			if (!empty($emd_dates[1])) $data['regEndDt'] = DateTime::createFromFormat('d.m.Y', trim($emd_dates[1]))->format('Y-m-d');
			else $data['regEndDt'] = $data['regBegDt'];

			$filter .= '
				and to_char(emdv."EMDVersion_RegistrationDate", \'YYYY-MM-DD\') >= :regBegDt
				and to_char(emdv."EMDVersion_RegistrationDate", \'YYYY-MM-DD\') <= :regEndDt
			';

			$versions_join = ' left join "EMD"."EMDVersion" emdv on emdv."EMDRegistry_id" = emdr."EMDRegistry_id" ';
		}

		if (!empty($data['isLpuSignNeeded']) && $data['isLpuSignNeeded'] === 'on') {

			$filter .= '
				and dt."EMDDocumentType_IsNeedSignatures" = 1 and sign."MedStaffFact_id" is not null
			';

			$join .= ' left join "EMD"."EMDDocumentType" dt on dt."EMDDocumentType_id" = dtl."EMDDocumentType_id" ';
		}

		if (!empty($data['isWithoutRegistration']) && $data['isWithoutRegistration'] === 'on') {

			$filter .= '
				and emdv."EMDVersion_RegistrationDate" is null
			';

			$versions_join = ' left join "EMD"."EMDVersion" emdv on emdv."EMDRegistry_id" = emdr."EMDRegistry_id" ';
		}
		$data['page'] = (empty($data['page'])) ? 1 : $data['page'];
		$data['limit'] = (empty($data['limit'])) ? 50 : $data['limit'];
		$data['start'] = ($data['page'] - 1) * $data['limit'];

		//
		$query = '
			SELECT
			    -- select
				emdr."EMDRegistry_id",
				emdr."EMDRegistry_ObjectName",
				emdr."EMDRegistry_ObjectID",
				dtl."EMDDocumentTypeLocal_Name",
				emdr."EMDRegistry_Num",
				to_char(emdr."EMDRegistry_EMDDate", \'DD.MM.YYYY\') as "EMDRegistry_EMDDate",
				emdr."Person_id",
				emdr."pmUser_insID"
				-- end select
			FROM
				-- from
			     "EMD"."EMDRegistry" emdr
				left join "EMD"."EMDDocumentTypeLocal" dtl on dtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
				'.$join.'
				'.$versions_join.'
				left join lateral(
					SELECT
						emdv."EMDVersion_id",
						emds."MedStaffFact_id",
						emds."Signatures_id"
					FROM "EMD"."EMDVersion" emdv
					left join "EMD"."EMDSignatures" emds on emds."EMDVersion_id" = emdv."EMDVersion_id"
					WHERE emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
					ORDER BY emdv."EMDVersion_id" desc, emds."MedStaffFact_id" desc
					LIMIT 1
				) sign on true
				-- end from
			WHERE
				-- where
				sign."Signatures_id" is not null '.$filter.'
				-- end where
			LIMIT '.$data['limit'].'
			OFFSET '.$data['start'].'
		';

		$result = $this->queryResult($query, $data, $this->emddb);
		if (!empty($result)) {

			$docs = array();
			$persons = array();
			$signObjects = array();

			$totalCount = 100;
			if ($data['page'] != 1 || count($result) >= $data['limit']) {
			$get_count_query = '
			SELECT
			    -- select
				emdr."EMDRegistry_id",
				emdr."EMDRegistry_ObjectName",
				emdr."EMDRegistry_ObjectID",
				dtl."EMDDocumentTypeLocal_Name",
				emdr."EMDRegistry_Num",
				to_char(emdr."EMDRegistry_EMDDate", \'DD.MM.YYYY\') as "EMDRegistry_EMDDate",
				emdr."Person_id",
				emdr."pmUser_insID"
				-- end select
			FROM
				-- from
			     "EMD"."EMDRegistry" emdr
				left join "EMD"."EMDDocumentTypeLocal" dtl on dtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
				'.$join.'
				'.$versions_join.'
				left join lateral(
					SELECT
						emdv."EMDVersion_id",
						emds."MedStaffFact_id",
						emds."Signatures_id"
					FROM "EMD"."EMDVersion" emdv
					left join "EMD"."EMDSignatures" emds on emds."EMDVersion_id" = emdv."EMDVersion_id"
					WHERE emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
					ORDER BY emdv."EMDVersion_id" desc, emds."MedStaffFact_id" desc
					LIMIT 1
				) sign on true
				-- end from
			WHERE
				-- where
				sign."Signatures_id" is not null '.$filter.'
				-- end where
			';
				$resp_count = $this->queryResult($get_count_query, $data, $this->emddb);
				if (isset($resp_count[0]['cnt'])) {
					$totalCount = $resp_count[0]['cnt'];
				}
			}

			foreach ($result as $doc) {

				// каждому документу присвоим ключ по идентификатору
				$docs[$doc['EMDRegistry_id']] = $doc;

				if (!empty($doc['Person_id'])) {

					if (empty($persons[$doc['Person_id']])) $persons[$doc['Person_id']] = array();
					$persons[$doc['Person_id']][] = $doc['EMDRegistry_id'];
				}

				// для каждого документа надо проверить актуальность подписи + получить доп. поля (врач, отделение, родительский документ)
				if (isset($this->documents[$doc['EMDRegistry_ObjectName']])) {
					$docInfo = $this->documents[$doc['EMDRegistry_ObjectName']];
					$signObjects[$docInfo['signObject']]['docInfo'] = $docInfo;
					$signObjects[$docInfo['signObject']]['ids'][$doc['EMDRegistry_ObjectID']][] = $doc['EMDRegistry_id'];
				}
			}

			if (count($signObjects) > 0) {
				foreach($signObjects as $oneSignObject) {
					$docInfo = $oneSignObject['docInfo'];
					$ids = implode(',', array_keys($oneSignObject['ids']));

					$join = "";
					$fields = "";
					switch($docInfo['signObject']) {
						case 'Evn':
							$join .= " left join EvnRecept er on er.Evn_id = s.Evn_id";
							$join .= " left join EvnPrescrMse epm on epm.Evn_id = s.Evn_id";
							$join .= " left join EvnDirection ed on ed.Evn_id = s.Evn_id";
							$join .= "
								left join v_MedStaffFact msf on (
									(msf.LpuSection_id = er.LpuSection_id and msf.MedPersonal_id = er.MedPersonal_id)
									OR (msf.LpuSection_id = epm.LpuSection_sid and msf.MedPersonal_id = epm.MedPersonal_sid)
									OR (msf.MedStaffFact_id = ed.MedStaffFact_id)
								)
							";
							$join .= " left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id";
							$join .= " left join v_Evn r on r.Evn_id = s.Evn_rid";
							$join .= " left join EvnPL epl on epl.Evn_id = r.Evn_id";
							$join .= " left join EvnPS eps on eps.Evn_id = r.Evn_id";
							$fields .= ", msf.Person_Fio as \"MedPersonal_Fio\"";
							$fields .= ", ls.LpuSection_Name as \"LpuSection_Name\"";
							$fields .= "
								, case
									when r.EvnClass_id in (3, 6) then 'В составе ТАП №' || epl.EvnPL_NumCard
									when r.EvnClass_id in (30) then 'В составе КВС №' || eps.EvnPS_NumCard
								end as \"EMDRegistry_Descr\"
							";
							break;
						case 'EvnXml':
							$join .= " left join v_Evn e on e.Evn_id = s.Evn_id";
							$join .= " left join EvnVizit ev on ev.Evn_id = s.Evn_id";
							$join .= " left join EvnSection es on es.Evn_id = s.Evn_id";
							$join .= " left join EvnUsluga eu on eu.Evn_id = s.Evn_id";
							$join .= " left join v_MedStaffFact msf on msf.MedStaffFact_id = coalesce(ev.MedStaffFact_id, es.MedStaffFact_id, eu.MedStaffFact_id)";
							$join .= " left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id";
							$join .= " left join v_Evn r on r.Evn_id = e.Evn_rid";
							$join .= " left join EvnPL epl on epl.Evn_id = r.Evn_id";
							$join .= " left join EvnPS eps on eps.Evn_id = r.Evn_id";
							$fields .= ", msf.Person_Fio as \"MedPersonal_Fio\"";
							$fields .= ", ls.LpuSection_Name as \"LpuSection_Name\"";
							$fields .= "
								, case
									when r.EvnClass_id in (3, 6) then 'В составе ТАП №' || epl.EvnPL_NumCard
									when r.EvnClass_id in (30) then 'В составе КВС №' || eps.EvnPS_NumCard
								end as \"EMDRegistry_Descr\"
							";
							break;
					}

					$query = "
						select
							s.{$docInfo['signObject']}_id as \"signObjectId\",
							s.{$docInfo['signField']} as \"IsSigned\"
							{$fields}
						from
							{$docInfo['signObject']} s
							{$join}
						where
							s.{$docInfo['signObject']}_id in ({$ids})
					";

					$object_data = $this->queryResult($query, array());
					if (!empty($object_data)) {
						foreach ($object_data as $o) {
							if (!empty($oneSignObject['ids'][$o['signObjectId']])) {
								foreach ($oneSignObject['ids'][$o['signObjectId']] as $emd_key) {
									if (!empty($docs[$emd_key])) $docs[$emd_key] = array_merge($docs[$emd_key], $o);
								}
							}
						}
					}
				}
			}

			if (count($persons) > 0) {
				$persons_in = implode(',', array_keys($persons));

				$query = "
					SELECT
						ps.Person_id as \"Person_id\",
						ps.Person_SurName || coalesce(' ' || ps.Person_FirName,'') || coalesce(' ' || ps.Person_SecName,'') as \"Person_FIO\",
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
					FROM v_PersonState ps
					WHERE ps.Person_id in ({$persons_in})
				";

				$persons_data = $this->queryResult($query, array());
				if (!empty($persons_data)) {
					foreach ($persons_data as $p) {
						if (!empty($persons[$p['Person_id']])) {
							foreach ($persons[$p['Person_id']] as $emd_key) {
								if (!empty($docs[$emd_key])) $docs[$emd_key] = array_merge($docs[$emd_key], $p);
							}
						}
					}
				}
			}

			return ['totalCount' => $totalCount, 'data' => array_values($docs)];
		}
		return ['totalCount' => 0, 'data' => []];
	}

	/**
	 * Получение списка версий документов
	 */
	function loadEMDVersions($data) {

		$filter = "";

		if (!empty($data['isWithoutRegistration']) && $data['isWithoutRegistration'] === 'true') {

			$filter .= '
				and emdv."EMDVersion_RegistrationDate" is null
			';
		}

		if (!empty($data['EMDVersion_RegistrationDate_period'])) {

			$emd_dates = explode('—',$data['EMDVersion_RegistrationDate_period']);
			$data['regBegDt'] = DateTime::createFromFormat('d.m.Y', trim($emd_dates[0]))->format('Y-m-d');

			if (!empty($emd_dates[1])) $data['regEndDt'] = DateTime::createFromFormat('d.m.Y', trim($emd_dates[1]))->format('Y-m-d');
			else $data['regEndDt'] = $data['regBegDt'];

			$filter .= '
				and to_char(emdv."EMDVersion_RegistrationDate", \'YYYY-MM-DD\') >= :regBegDt
				and to_char(emdv."EMDVersion_RegistrationDate", \'YYYY-MM-DD\') <= :regEndDt
			';
		}

		$query = '
			SELECT
				emdv."EMDVersion_id",
				emdv."EMDVersion_VersionNum",
				to_char(emdv."EMDVersion_insDT", \'DD.MM.YYYY\') as "EMDVersion_insDT",
				CASE
					WHEN emdv."EMDVersion_EmdrId" IS NOT NULL AND emdv."EMDVersion_RegistrationDate" IS NOT NULL
					THEN (\'№ \' || emdv."EMDVersion_EmdrId" || \' от \' || to_char(emdv."EMDVersion_RegistrationDate", \'DD.MM.YYYY\'))
					ELSE \'\'
				END as "RegistrationInfo",
				emdv."EMDVersion_FilePath",
				case when dt."EMDDocumentType_IsNeedSignatures" = 1 then \' <font color="red"><b>!</b></font>\' else \'\' end as "mosigns"
			FROM
				"EMD"."EMDVersion" emdv
				left join "EMD"."EMDRegistry" emdr on emdr."EMDRegistry_id" = emdv."EMDRegistry_id"
				left join "EMD"."EMDDocumentTypeLocal" dtl on dtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
				left join "EMD"."EMDDocumentType" dt on dt."EMDDocumentType_id" = dtl."EMDDocumentType_id"
			WHERE (1=1)
				and emdv."EMDRegistry_id" = :EMDRegistry_id
				'.$filter.'
			ORDER BY emdv."EMDVersion_insDT" desc
		';

		$versions = $this->queryResult($query, $data, $this->emddb);
		$response = array();

		if (!empty($versions)) {

			foreach ($versions as $v) {
				$response[$v['EMDVersion_id']] = $v;
			}

			$version_list = implode(',', array_column($versions,'EMDVersion_id'));

			$query = '
				SELECT
					emdv."EMDVersion_id",					
					emds."EMDSignatures_id",
					to_char(s."Signatures_insDT", \'DD.MM.YYYY HH24:MI:SS\') as "Signatures_insDT",
					(emds."MedPersonal_SurName" || \' \' || emds."MedPersonal_FirName" || \' \' || emds."MedPersonal_SecName") as "PMUser_Name",
					emdc."EMDCertificate_CommonName",
					emdpr."EMDPersonRole_Name",
					dt."EMDDocumentType_IsNeedSignatures",
					emds."MedStaffFact_id"
				FROM "EMD"."EMDVersion" emdv
				left join "EMD"."EMDSignatures" emds on emds."EMDVersion_id" = emdv."EMDVersion_id"
				left join "EMD"."Signatures" s on s."Signatures_id" = emds."Signatures_id"
				left join "EMD"."EMDCertificate" emdc on emdc."EMDCertificate_id" = s."EMDCertificate_id"
				left join "EMD"."EMDPersonRole" emdpr on emdpr."EMDPersonRole_id" = emds."EMDPersonRole_id"
				left join "EMD"."EMDRegistry" emdr on emdr."EMDRegistry_id" = emdv."EMDRegistry_id"
				left join "EMD"."EMDDocumentTypeLocal" dtl on dtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
				left join "EMD"."EMDDocumentType" dt on dt."EMDDocumentType_id" = dtl."EMDDocumentType_id"
				WHERE (1=1)
					and emdv."EMDVersion_id" in ('.$version_list.')
			';

			$signs = $this->queryResult($query, $data, $this->emddb);
			if (!empty($signs)) {

				$versions_signs = array();
				$versions_mosigns = array();

				foreach ($signs as $sign) {
					if (!empty($sign['MedStaffFact_id'])) {
						if (!isset($versions_signs[$sign['EMDVersion_id']])) {
							$versions_signs[$sign['EMDVersion_id']] = "";
						}

						$versions_signs[$sign['EMDVersion_id']] .= '<br><a href="#" onClick="getWnd(\'swEMDSignatureInfoWindow\').show({EMDSignatures_id: ' . $sign['EMDSignatures_id'] . '})">'
							.$sign['EMDCertificate_CommonName']
							.(!empty($sign['EMDPersonRole_Name']) ? ' ('.$sign['EMDPersonRole_Name'].')' : '' ).' '
							.$sign['Signatures_insDT']
							."</a>";
					} else {
						if (!isset($versions_mosigns[$sign['EMDVersion_id']])) {
							$versions_mosigns[$sign['EMDVersion_id']] = "";
						}

						$versions_mosigns[$sign['EMDVersion_id']] .= '<br><a href="#" onClick="getWnd(\'swEMDSignatureInfoWindow\').show({EMDSignatures_id: ' . $sign['EMDSignatures_id'] . '})">'
							.$sign['Signatures_insDT']
							."</a>";
					}
				}

				foreach ($versions_signs as $key => $sign) {
					if (isset($response[$key])) {
						$response[$key]['signs'] = $sign;
					}
				}

				foreach ($versions_mosigns as $key => $sign) {
					if (isset($response[$key])) {
						$response[$key]['mosigns'] = $sign;
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Получение данных для повторного подписания документа
	 */
	function getEMDVersionSignData($data) {

		if (empty($data['EMDVersion_id'])) throw new Exception('Не указан идентификатор версии документа');

		$version_path = $this->queryResult('
			select 
				"EMDVersion_FilePath"
			from "EMD"."EMDVersion"
			where "EMDVersion_id" = :EMDVersion_id
		', array('EMDVersion_id' => $data['EMDVersion_id']), $this->emddb);

		if (!empty($version_path[0]['EMDVersion_FilePath'])) {
			$toSign = array();

			$file_path = $version_path[0]['EMDVersion_FilePath'];
			if (!file_exists($file_path)) throw new Exception('Не удалось получить файл');

			$response = file_get_contents($file_path);
			$docBase64 = base64_encode($response);

			$resp_cert = $this->queryResult('
				select
					"EMDCertificate_OpenKey"
				from
					"EMD"."EMDCertificate"
				where
					"EMDCertificate_id" = :EMDCertificate_id
			', array('EMDCertificate_id' => $data['EMDCertificate_id']), $this->emddb);

			if (empty($resp_cert[0]['EMDCertificate_OpenKey'])) {
				throw new Exception('Ошибка получения данных сертификата');
			}

			$this->load->helper('openssl');
			// считаем хэш
			$cryptoProHash = getCryptCpHash($response, $resp_cert[0]['EMDCertificate_OpenKey']);

			$toSign[] = array(
				'link' => $file_path,
				'hashBase64' => $cryptoProHash,
				'docBase64' => $docBase64,
				'EMDVersion_id' => $data['EMDVersion_id']
			);

			return array('Error_Msg' => '', 'toSign' => $toSign);
		} else {
			throw new Exception('Путь к файлу не указан');
		}
	}

	/**
	 * Получение данных для подписания
	 */
	function generateEMDRegistry($data) {
		if (empty($this->documents[$data['EMDRegistry_ObjectName']])) {
			return array('Error_Msg' => 'Подпись данных документов ещё не реализована');
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];

		$files = array();

		if ($data['EMDRegistry_ObjectName'] == 'EvnXml') {
			$this->load->model('XmlTemplate6E_model');
			$this->load->library('swEvnXml');
			$resp = $this->XmlTemplate6E_model->getParamsByXmlTemplateOrEvnXml(array(
				'EvnXml_id' => $data['EMDRegistry_ObjectID']
			));
			if (!$this->XmlTemplate6E_model->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			$files[] = array(
				'data' => swEvnXml::doPrint(
					$resp[0]['params'],
					getRegionNick(),
					false,
					false,
					false,
					false,
					$data['EMDCertificate_id']
				),
				'EMDFileFormat_id' => 1
			);
		} else {
			if ($data['EMDRegistry_ObjectName'] == 'EvnRecept') {
				$ReceptType_id = $this->getFirstResultFromQuery("select ReceptType_id as \"ReceptType_id\" from v_EvnRecept where EvnRecept_id = :EvnRecept_id", ['EvnRecept_id' => $data['EMDRegistry_ObjectID']]);
				if ($ReceptType_id == 3) {
					// для электронных подменяем печать на формат JSON
					$doc['printParams'] = [[
						'printModel' => 'Dlo_EvnRecept_model', // модель в которой есть функция печати
						'printMethod' => 'getEvnReceptJSON' // метод печати в модели
					]];
				}
			}
			foreach ($doc['printParams'] as $printParam) {
				if (!empty($printParam['printModel']) && !empty($printParam['printMethod'])) {
					$this->load->model($printParam['printModel'], 'printModel');

					$printMethod = $printParam['printMethod'];
					//это код по федеральному справочнику 1.2.643.5.1.13.13.99.2.195 если его указали
					$documentCode= (!empty($printParam['documentCode'])) ? $printParam['documentCode'] : null;
					$response = $this->printModel->$printMethod(array(
						$doc['idField'] => $data['EMDRegistry_ObjectID'],
						'EMDCertificate_id' => $data['EMDCertificate_id'],
						'MedStaffFact_id' => $data['MedStaffFact_id'],
						'Lpu_id' => $data['session']['lpu_id'],
						'session' => $data['session'],
						'returnString' => true
					),$documentCode);

					if (!empty($response['html'])) {
						$html = $response['html'];

						$PromedURL = $this->config->item('PromedURL');
						if (empty($PromedURL)) {
							$PromedURL = 'http' . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . '://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'];
						}

						$this->load->library('swEvnXml');
						$files[] = array(
							'data' => swEvnXml::doPrintPdf(
								null,
								array(),
								$html,
								false,
								true
							),
							'EMDFileFormat_id' => 1
						);
					} else if (!empty($response['pdf'])) {
						$files[] = array(
							'data' => $response['pdf'],
							'EMDFileFormat_id' => 1
						);
					} else if (!empty($response['xml'])) {
						$files[] = array(
							'data' => $response['xml'],
							'EMDFileFormat_id' => 2
						);
					} else if (!empty($response['json'])) {
						$files[] = array(
							'data' => $response['json'],
							'EMDFileFormat_id' => 3
						);
					} else if (!empty($response['Error_Msg'])) {
						throw new Exception($response['Error_Msg']);
					} else {
						throw new Exception('Ошибка получения данных документа');
					}
				} else {
					$data['Report_FileName'] = $printParam['reportName'];
					$data['Report_Params'] = $printParam['reportParams'];
					if ($this->usePostgre || ($this->usePostgreLis && $data['EMDRegistry_ObjectName'] == 'EvnUslugaPar')) {
						// добавляем префикс для отчетов pgsql
						$data['Report_FileName'] = str_replace('.rptdesign', '_pg.rptdesign', $data['Report_FileName']);
					}

					$data['Report_Params'] = strtr($data['Report_Params'], array(
						'{docId}' => $data['EMDRegistry_ObjectID'],
						'{certId}' => $data['EMDCertificate_id']
					));

					$data['Report_Format'] = $printParam['reportFormat'];
					$this->load->model('ReportRun_model');
					$files[] = array(
						'data' => $this->ReportRun_model->RunByFileName($data, true),
						'EMDFileFormat_id' => 1
					);
				}
			}
		}

		$resp_cert = $this->queryResult('
			select
				"EMDCertificate_OpenKey"
			from
				"EMD"."EMDCertificate"
			where
				"EMDCertificate_id" = :EMDCertificate_id
		', array(
			'EMDCertificate_id' => $data['EMDCertificate_id']
		), $this->emddb);

		if (empty($resp_cert[0]['EMDCertificate_OpenKey'])) {
			throw new Exception('Ошибка получения данных сертификата');
		}

		$resp = $this->saveEMDRegistry($data);

		$toSign = array();
		if (!empty($resp['emd_registry'][0]['EMDRegistry_id'])) {
			$this->load->helper('openssl');

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "emd_" . $doc['objectName'] . "_" . $data['EMDRegistry_ObjectID'];

			if (!file_exists(EXPORTPATH_EMD)) {
				mkdir(EXPORTPATH_EMD);
			}
			if (!file_exists(EXPORTPATH_EMD . $out_dir)) {
				mkdir(EXPORTPATH_EMD . $out_dir);
			}

			foreach ($files as $file) {
				// считаем хэш
				$cryptoProHash = getCryptCpHash($file['data'], $resp_cert[0]['EMDCertificate_OpenKey']);

				switch($file['EMDFileFormat_id']) {
					case 2:
						$format = 'xml';
						break;
					case 3:
						$format = 'json';
						break;
					default:
						$format = 'pdf';
						break;
				}
				$file_name = "emd_" . time() . "_" . rand(100000, 999999) . "." . $format;
				$file_path = EXPORTPATH_EMD . $out_dir . "/" . $file_name;

				$docBase64 = base64_encode($file['data']);
				file_put_contents($file_path, $file['data']);

				$data['EMDRegistry_id'] = $resp['emd_registry'][0]['EMDRegistry_id'];
				$data['EMDVersion_FileName'] = $file_name;
				$data['EMDVersion_FilePath'] = $file_path;

				if (!empty($resp['doc_data'])) $data = array_merge($data, $resp['doc_data']);

				$data['EMDFileFormat_id'] = $file['EMDFileFormat_id'];
				$resp_ver = $this->saveEMDVersion($data);
				if (empty($resp_ver[0]['EMDVersion_id'])) return array('Error_Msg' => 'Ошибка сохранения версии ЭМД');

				$toSign[] = array(
					'link' => $file_path,
					'hashBase64' => $cryptoProHash,
					'docBase64' => $docBase64,
					'EMDVersion_id' => $resp_ver[0]['EMDVersion_id']
				);
			}
		} else return array('Error_Msg' => 'Ошибка сохранения ЭМД' . (!empty($resp['Error_Msg']) ? ': ' . $resp['Error_Msg'] : ''));

		if (!empty($toSign)) {
			return array('Error_Msg' => '', 'toSign' => $toSign);
		} else {
			return array('Error_Msg' => 'Ошибка сохранения в регистр ЭМД');
		}
	}

	/**
	 * Сохранение\обновление ЭМД
	 */
	function saveEMDRegistry($data) {

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];
		$join = "";

		// тип документа
		$EMDDocumentTypeLocal_id = $this->getEMDDocumentTypeLocal([
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
		]);
		// номер документа
		$DocNum = null;

		// Условия оказания
		$MedicalCareType_id = null;

		// идентификатор события
		$data['Evn_id'] = null;
		// класс события
		$data['EvnClass_SysNick'] = null;
		
		/*1.2.643.5.1.13.13.99.2.195 - код документа по фед справочнику
			предназначена для получения метаданных подписываемого файла, что бы записать в схему EMD
			если >0 то идет обращение в минибиблиотеку EMDHL7OutDoc_model
		 	иначе обрабатывается здесь по старому
		 */
		$HL7DocCode=0;

		// если тип прокотол, получим из него связанный тип события
		if ($data['EMDRegistry_ObjectName'] == 'EvnXml') {

			// для протокола, номером документа будет его идентификатор
			$DocNum = $data['EMDRegistry_ObjectID'];

			// для EvnXml получим класс события и его идентификатор
			$query = "
					SELECT
						e.Evn_id as \"Evn_id\",
						e.EvnClass_SysNick as \"EvnClass_SysNick\",
						x.XmlType_id as \"XmlType_id\",
						t.XmlTypeKind_id as \"XmlTypeKind_id\"
					FROM v_EvnXml x
					INNER JOIN v_Evn e on e.Evn_id = x.Evn_id
					LEFT JOIN v_XmlTemplate t on t.XmlTemplate_id = x.XmlTemplate_id
					WHERE (1=1)
						and x.EvnXml_id = :EMDRegistry_ObjectID
					limit 1
				";

			$xml_evn_data = $this->queryResult($query, $data);

			if (!empty($xml_evn_data[0]['Evn_id'])) {

				$xml_evn_data = $xml_evn_data[0];

				// может быть и такое
				if ($xml_evn_data['EvnClass_SysNick'] === 'EvnUslugaTelemed') {
					// для EvnUslugaTelemed получим класс события и его идентификатор
					$query = "
					SELECT
						e.Evn_id as \"Evn_id\",
						e.EvnClass_SysNick as \"EvnClass_SysNick\"
					FROM v_EvnUslugaTelemed tele
					INNER JOIN v_Evn e on e.Evn_id = tele.EvnUslugaTelemed_pid
					WHERE (1=1)
						and tele.EvnUslugaTelemed_id = :Evn_id
					limit 1
				";

					$telemed_data = $this->queryResult($query, array(
						'Evn_id' => $xml_evn_data['Evn_id']
					));

					if (!empty($telemed_data[0]['Evn_id'])) {

						$telemed_data = $telemed_data[0];

						$data['Evn_id'] = $telemed_data['Evn_id'];
						$data['EvnClass_SysNick'] = $telemed_data['EvnClass_SysNick'];

					} else return array('Error_Msg' => 'Не удалось определить событие телемед. услуги');

				} else {
					$data['Evn_id'] = $xml_evn_data['Evn_id'];
					$data['EvnClass_SysNick'] = $xml_evn_data['EvnClass_SysNick'];
				}

				$data['XmlType_id'] = $xml_evn_data['XmlType_id'];

			} else return array('Error_Msg' => 'Не удалось определить событие шаблона');

			// неизвестно, попадает ли в этот блок сейчас, но на всякий оставлю...
		} elseif ($data['EMDRegistry_ObjectName'] == 'EvnUslugaTelemed') {

			$DocNum = $data['EMDRegistry_ObjectID'];

			// для EvnUslugaTelemed получим класс события и его идентификатор
			$query = "
					SELECT
						e.Evn_id as \"Evn_id\",
						e.EvnClass_SysNick as \"EvnClass_SysNick\"
					FROM v_EvnUslugaTelemed tele
					INNER JOIN v_Evn e on e.Evn_id = tele.EvnUslugaTelemed_pid
					WHERE (1=1)
						and tele.EvnUslugaTelemed_id = :EMDRegistry_ObjectID
					limit 1
				";

			$telemed_data = $this->queryResult($query, $data);

			if (!empty($telemed_data[0]['Evn_id'])) {

				$telemed_data = $telemed_data[0];

				$data['Evn_id'] = $telemed_data['Evn_id'];
				$data['EvnClass_SysNick'] = $telemed_data['EvnClass_SysNick'];

			} else return array('Error_Msg' => 'Не удалось определить событие телемед. услуги');

		} else {
			$data['Evn_id'] = $data['EMDRegistry_ObjectID'];

			// иначе определяем, является ли объект событием системы
			$query = "
					SELECT
						ec.EvnClass_SysNick as \"EvnClass_SysNick\"
					FROM v_EvnClass ec
					WHERE (1=1)
						and ec.EvnClass_SysNick = :EMDRegistry_ObjectName
					limit 1
				";

			$data['EvnClass_SysNick'] = $this->dbmodel->getFirstResultFromQuery($query, $data);
		}

		$frmoPriority = $this->config->item('EMD_FRMO_PRIORITY');
		$frmoPlace = $this->config->item('EMD_FRMO_PLACE');
		if (!empty($frmoPlace) && $frmoPlace == 'section') {
			if (!empty($frmoPriority) && $frmoPriority == 'service') {
				$LpuBuilding_frmo = "coalesce(ls.LpuSection_FRMOSectionId, fs.FRMOSection_OID)";
			} else {
				$LpuBuilding_frmo = "coalesce(fs.FRMOSection_OID, ls.LpuSection_FRMOSectionId)";
			}
		} else {
			if (!empty($frmoPriority) && $frmoPriority == 'service') {
				$LpuBuilding_frmo = "coalesce(lu.LpuUnit_FRMOUnitID, fu.FRMOUnit_OID)";
			} else {
				$LpuBuilding_frmo = "coalesce(fu.FRMOUnit_OID, lu.LpuUnit_FRMOUnitID)";
			}
		}


		// если объект не событие системы, выполняем для него
		// свой метод определения параметров получения данных для сохранения ЭМД
		if (empty($data['EvnClass_SysNick'])) {
			switch($data['EMDRegistry_ObjectName']) {
				case 'PersonPrivilegeReq':
					$DocNum = $data['EMDRegistry_ObjectID'];

					$doc_data = array(
						'docNum' => $DocNum,
						'Person_id' => null,
						'Lpu_id' => null,
						'LpuBuilding_id' => null
					);
					break;
				case 'PersonPrivilegeReqAns':
					$DocNum = $data['EMDRegistry_ObjectID'];

					$doc_data = array(
						'docNum' => $DocNum,
						'Person_id' => null,
						'Lpu_id' => null,
						'LpuBuilding_id' => null
					);
					break;
				case 'ReportRun':
					$DocNum = $data['EMDRegistry_ObjectID'];

					$doc_data = array(
						'docNum' => $DocNum,
						'Person_id' => null,
						'Lpu_id' => null,
						'LpuBuilding_id' => null
					);
					break;
				case 'PersonDisp':
					$DocNum = $data['EMDRegistry_ObjectID'];

					$query = "
						SELECT
							pd.Person_id as \"Person_id\",
							pd.Lpu_id as \"Lpu_id\",
							ls.LpuSection_id as \"LpuSection_id\",
							ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
							lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
							ls.LpuBuilding_id as \"LpuBuilding_id\",
							pd.PersonDisp_updDT as \"updDT\",
							{$LpuBuilding_frmo} as \"LpuBuilding_frmo\"
						FROM v_PersonDisp pd
						LEFT JOIN v_LpuSection ls on ls.LpuSection_id = pd.LpuSection_id
						LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
						LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
						LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
						WHERE (1=1)
							and pd.PersonDisp_id = :EMDRegistry_ObjectID
						limit 1
					";

					$doc_data = $this->dbmodel->getFirstRowFromQuery($query, $data);
					break;
				case 'BirthSvid':
					$DocNum = $data['EMDRegistry_ObjectID'];

					$query = "
						SELECT
							bs.Person_id as \"Person_id\",
							bs.Lpu_id as \"Lpu_id\",
							ls.LpuSection_id as \"LpuSection_id\",
							ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
							lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
							ls.LpuBuilding_id as \"LpuBuilding_id\",
							bs.BirthSvid_updDT as \"updDT\",
							{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
							lputid.PassportToken_tid as \"Lpu_tid\",
							LpuUnit_Name as \"LpuUnit_Name\",
							ps.Person_SurName as \"Person_SurName\",
							ps.Person_FirName as \"Person_FirName\",
							ps.Person_SecName as \"Person_SecName\",
							to_char(ps.Person_Birthday, 'yyyy-mm-dd') as \"Person_Birthday\",
							ps.Sex_id as \"Person_Gender\",
							ps.Person_Snils as \"Person_Snils\",
							ps.Person_EdNum as \"Person_EdNum\"
						FROM v_BirthSvid bs
						LEFT JOIN v_PersonState ps on ps.Person_id = bs.Person_id
						LEFT JOIN v_LpuSection ls on ls.LpuSection_id = bs.LpuSection_id
						LEFT JOIN fed.v_PassportToken lputid on lputid.Lpu_id = ls.Lpu_id
						LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
						LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
						LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
						WHERE (1=1)
							and bs.BirthSvid_id = :EMDRegistry_ObjectID
						limit 1
					";

					$doc_data = $this->dbmodel->getFirstRowFromQuery($query, $data);
					break;
				case 'DeathSvid':
					$DocNum = $data['EMDRegistry_ObjectID'];

					$query = "
						SELECT
							ds.Person_id as \"Person_id\",
							ds.Lpu_id as \"Lpu_id\",
							ls.LpuSection_id as \"LpuSection_id\",
							ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
							lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
							ls.LpuBuilding_id as \"LpuBuilding_id\",
							ds.DeathSvid_updDT as \"updDT\",
							{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
						 	lputid.PassportToken_tid as \"Lpu_tid\",
							LpuUnit_Name as \"LpuUnit_Name\",
							ps.Person_SurName as \"Person_SurName\",
							ps.Person_FirName as \"Person_FirName\",
							ps.Person_SecName as \"Person_SecName\",
							to_char(ps.Person_Birthday, 'yyyy-mm-dd') as \"Person_Birthday\",
							ps.Sex_id as \"Person_Gender\",
							ps.Person_Snils as \"Person_Snils\",
							ps.Person_EdNum as \"Person_EdNum\"
						FROM v_DeathSvid ds
						LEFT JOIN v_PersonState ps on ps.Person_id = ds.Person_id
						LEFT JOIN v_LpuSection ls on ls.LpuSection_id = ds.LpuSection_id
						LEFT JOIN fed.v_PassportToken lputid on lputid.Lpu_id = ls.Lpu_id
						LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
						LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
						LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
						WHERE (1=1)
							and ds.DeathSvid_id = :EMDRegistry_ObjectID
						limit 1
					";

					$doc_data = $this->dbmodel->getFirstRowFromQuery($query, $data);
					break;
			}
		} else {

			// иначе, для событий используем общий метод определения параметров сохранения
			// предварительно определив откуда брать поле номера и отделение

			// вьюха
			$viewName = 'v_' . $data['EvnClass_SysNick'];

			if ($data['EvnClass_SysNick'] === 'EvnPL') {

				$MedicalCareType_id = 2;

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnPL_NumCard as \"docNum\",
					-- специфические поля
					evn.Diag_id as \"Diag_id\",
					evn.ResultClass_id as \"ResultClass_id\",
					rc.ResultClass_Code as \"EMDVersion_Result\",
					evn.Diag_lid as \"Diag_lid\",
					evn.EvnPL_setDT as \"EMDVersion_begDate\",
					evn.EvnPL_disDT as \"EMDVersion_endDate\",
					d.Diag_Code as \"EMDVersion_BasicDiag\",
					zakd.Diag_Code as \"EMDVersion_FinalDiag\"
				";

				$join = "
					LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = evn.MedStaffFact_did
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = evn.Lpu_oid
					LEFT JOIN v_Diag d on d.Diag_id = evn.Diag_id
					LEFT JOIN v_Diag zakd on zakd.Diag_id = evn.Diag_lid
					LEFT JOIN v_ResultClass rc on rc.ResultClass_id = evn.ResultClass_id
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnPLStom') {

				$MedicalCareType_id = 2;

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnPLStom_NumCard as \"docNum\",
					-- специфические поля
					evn.Diag_id as \"Diag_id\",
					evn.ResultClass_id as \"ResultClass_id\",
					rc.ResultClass_Code as \"EMDVersion_Result\",
					evn.Diag_lid as \"Diag_lid\",
					evn.EvnPLStom_setDT as \"EMDVersion_begDate\",
					evn.EvnPLStom_disDT as \"EMDVersion_endDate\",
					d.Diag_Code as \"EMDVersion_BasicDiag\",
					zakd.Diag_Code as \"EMDVersion_FinalDiag\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = evn.Lpu_oid
					LEFT JOIN v_Diag d on d.Diag_id = evn.Diag_id
					LEFT JOIN v_Diag zakd on zakd.Diag_id = evn.Diag_lid
					LEFT JOIN v_ResultClass rc on rc.ResultClass_id = evn.ResultClass_id
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnVizitPL') {

				$MedicalCareType_id = 2;

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnVizitPL_id as \"docNum\",
					-- специфические поля
					evn.VizitClass_id as \"EMDVersion_AppealOrder\",
					parent.Diag_id as \"Diag_id\",
					parent.ResultClass_id as \"ResultClass_id\",
					rc.ResultClass_Code as \"EMDVersion_Result\",
					parent.Diag_lid as \"Diag_lid\",
					parent.EvnPL_setDT as \"EMDVersion_begDate\",
					parent.EvnPL_disDT as \"EMDVersion_endDate\",
					d.Diag_Code as \"EMDVersion_BasicDiag\",
					zakd.Diag_Code as \"EMDVersion_FinalDiag\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN v_EvnPL parent on parent.EvnPL_id = evn.EvnVizitPL_pid
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = parent.Lpu_oid
					LEFT JOIN v_Diag d on d.Diag_id = parent.Diag_id
					LEFT JOIN v_Diag zakd on zakd.Diag_id = parent.Diag_lid
					LEFT JOIN v_ResultClass rc on rc.ResultClass_id = parent.ResultClass_id
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnVizitPLStom') {

				$MedicalCareType_id = 2;

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnVizitPLStom_id as \"docNum\",
					-- специфические поля
					evn.VizitClass_id as \"EMDVersion_AppealOrder\",
					parent.Diag_id as \"Diag_id\",
					parent.ResultClass_id as \"ResultClass_id\",
					rc.ResultClass_Code as \"EMDVersion_Result\",
					parent.Diag_lid as \"Diag_lid\",
					parent.EvnPLStom_setDT as \"EMDVersion_begDate\",
					parent.EvnPLStom_disDT as \"EMDVersion_endDate\",
					d.Diag_Code as \"EMDVersion_BasicDiag\",
					zakd.Diag_Code as \"EMDVersion_FinalDiag\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN v_EvnPLStom parent on parent.EvnPLStom_id = evn.EvnVizitPLStom_pid
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = parent.Lpu_oid
					LEFT JOIN v_Diag d on d.Diag_id = parent.Diag_id
					LEFT JOIN v_Diag zakd on zakd.Diag_id = parent.Diag_lid
					LEFT JOIN v_ResultClass rc on rc.ResultClass_id = parent.ResultClass_id
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnPS') {

				// Шуршалова Екатерина (10:58:45 26/11/2018)
				// Для РЭМД, формируемых в условиях оказания стац.помощи  - № истории болезни/родов
				// Для РЭМД, формируемых в условиях оказания стац.помощи в дневном стационаре  - № истории болезни

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					-- 0 as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnPS_NumCard as \"docNum\",
					-- специфические поля
					evn.Diag_id as \"Diag_id\",
					evn.ResultClass_id as \"ResultClass_id\",
					rc.ResultClass_Code as \"EMDVersion_Result\",
					evn.EvnPS_setDT as \"EMDVersion_begDate\",
					evn.EvnPS_disDT as \"EMDVersion_endDate\",
					d.Diag_Code as \"EMDVersion_BasicDiag\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					2 as \"MedicalCareType_id\",
					evn.EvnPS_NumCard as \"PersonCard_Code\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = evn.Lpu_id
					LEFT JOIN v_Diag d on d.Diag_id = evn.Diag_id
					LEFT JOIN v_ResultClass rc on rc.ResultClass_id = evn.ResultClass_id
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnRecept') {
				$EMDDocumentTypeLocal_id = 3;
				$ReceptType_id = $this->getFirstResultFromQuery("select ReceptType_id as \"ReceptType_id\" from v_EvnRecept where EvnRecept_id = :EvnRecept_id", ['EvnRecept_id' => $data['EMDRegistry_ObjectID']]);
				if ($ReceptType_id == 3) {
					// для электронных отдельный тип
					$EMDDocumentTypeLocal_id = 26;
				}

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					-- 0 as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					(evn.EvnRecept_Ser || evn.EvnRecept_Num) as \"docNum\",
					-- специфические поля
					evn.Diag_id as \"Diag_id\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnReceptGeneral') {
				$EMDDocumentTypeLocal_id = 27;

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					-- 0 as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					(evn.EvnReceptGeneral_Ser || evn.EvnReceptGeneral_Num) as \"docNum\",
					-- специфические поля
					evn.Diag_id as \"Diag_id\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnUslugaCommon') {

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnUslugaCommon_id as \"docNum\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_uid
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = evn.Lpu_uid
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnUslugaPar') {

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnUslugaPar_id as \"docNum\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_uid
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = evn.Lpu_oid
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnMse') {

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					-- 0 as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					-- 0 as \"MedicalCareKind_id\",
					evn.EvnMse_NumAct as \"docNum\"
				";

				$join = "
					LEFT JOIN v_MedService ms on ms.MedService_id = evn.MedService_id
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnVK') {

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					-- 0 as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					-- 0 as \"MedicalCareKind_id\",
					evn.EvnVK_NumProtocol as \"docNum\"
				";

				$join = "
					LEFT JOIN v_MedService ms on ms.MedService_id = evn.MedService_id
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnPrescrMse') {
				$HL7DocCode=34; //это код исходящего МСЭ по фед. справочнику
				//запрос перешел в HL7/Doc34.php - это мини библиотека работы с HL7
				$select ="";
				$join="";
			} elseif ($data['EvnClass_SysNick'] === 'EvnDirection') {

				$viewName = 'v_EvnDirection_all';

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \"LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					lpusid.PassportToken_tid as \"Lpu_sid\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnDirection_Num as \"docNum\"
				";

				$join = "
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = evn.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
					LEFT JOIN fed.v_PassportToken lpusid on lpusid.Lpu_id = evn.Lpu_did
				";

			} elseif ($data['EvnClass_SysNick'] === 'EvnPLDispDriver') {

				$select = ",
					ls.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					lsp.LpuSectionProfile_fedid as \".LpuSectionProfile_fedid\",
					ls.LpuBuilding_id as \"LpuBuilding_id\",
					{$LpuBuilding_frmo} as \"LpuBuilding_frmo\",
					lu.LpuUnit_Name as \"LpuUnit_Name\",
					evn.EvnPLDispDriver_Num as \"docNum\"
				";

				$join = "
					left join lateral(
						select
							evdd.MedStaffFact_id
						from
							v_EvnVizitDispDop evdd
							inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
							inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						where
							EvnVizitDispDop_pid = evn.EvnPLDispDriver_id				
							and stl.SurveyType_id = 158 -- прием (осмотр) терапевтом
						limit 1	
					) evdd on true
					left join v_MedStaffFact msf on msf.MedStaffFact_id = evdd.MedStaffFact_id 
					LEFT JOIN v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu on fu.FRMOUnit_id = coalesce(ls.FRMOUnit_id, lu.FRMOUnit_id)
				";

			} else {

				$select = ",
					0 as \"LpuSection_id\",
					0 as \"LpuSectionProfile_id\",
					0 as \"LpuSectionProfile_fedid\",
					0 as \"LpuBuilding_id\",
					-- 0 as \"Lpu_sid\",
					-- 0 as \"LpuBuilding_frmo\",
					'{$data['EMDRegistry_ObjectID']}' as \"docNum\"
				";

			}

			$preQuery = "";
			$from = "{$viewName} evn";
			if ($this->usePostgreLis && $data['EMDRegistry_ObjectName'] == 'EvnUslugaPar') {
				$this->load->swapi('lis');
				$respLis = $this->lis->GET('EvnUsluga/EvnUslugaParInfo', [
					'EvnUslugaPar_id' => $data['EMDRegistry_ObjectID']
				]);

				$resp_eup = $respLis['data'] ?? [];

				$resp_eup[0]['EvnUslugaPar_id'] = $resp_eup[0]['EvnUslugaPar_id'] ?? 'null';
				$resp_eup[0]['Person_id'] = $resp_eup[0]['Person_id'] ?? 'null';
				$resp_eup[0]['Lpu_id'] = $resp_eup[0]['Lpu_id'] ?? 'null';
				$resp_eup[0]['LpuSection_uid'] = $resp_eup[0]['LpuSection_uid'] ?? 'null';
				$resp_eup[0]['Lpu_oid'] = $resp_eup[0]['Lpu_oid'] ?? 'null';

				$preQuery = "
					CREATE TEMP TABLE Evn (
						EvnUslugaPar_id bigint,
						Person_id bigint,
						Lpu_id bigint,
						LpuSection_uid bigint,
						Lpu_oid bigint
					);
					
					insert into Evn (
						EvnUslugaPar_id,
						Person_id,
						Lpu_id,
						LpuSection_uid,
						Lpu_oid
					) values (
						{$resp_eup[0]['EvnUslugaPar_id']},
						{$resp_eup[0]['Person_id']},
						{$resp_eup[0]['Lpu_id']},
						{$resp_eup[0]['LpuSection_uid']},
						{$resp_eup[0]['Lpu_oid']}
					);
				";

				$from = "Evn evn";
			}

			// получаем необходимые данные по событию
			$query = "
				{$preQuery}
				
				SELECT
					evn.Person_id as \"Person_id\",
					evn.Lpu_id as \"Lpu_id\",
					cls.EvnClass_id as \"EvnClass_id\",
					e.Evn_updDT as \"updDT\",
					lputid.PassportToken_tid as \"Lpu_tid\",
					ps.Person_SurName as \"Person_SurName\",
      				ps.Person_FirName as \"Person_FirName\",
      				ps.Person_SecName as \"Person_SecName\",
      				ps.Person_Snils as \"Person_Snils\",
					ps.Person_EdNum as \"Person_EdNum\",
					ps.Sex_id as \"Person_Gender\",
					to_char(ps.Person_Birthday, 'yyyy-mm-dd') as \"Person_Birthday\"
					{$select}
				FROM {$from}
				left join v_Evn e on e.Evn_id = evn.{$data['EvnClass_SysNick']}_id
				left join v_PersonState ps on ps.Person_id = evn.Person_id
				left join fed.v_PassportToken lputid on lputid.Lpu_id = evn.Lpu_id
				{$join}
				left join lateral(
					SELECT 
					EvnClass_id
					FROM v_EvnClass
					WHERE EvnClass_SysNick = :EvnClass_SysNick
					limit 1
				) as cls on true
				WHERE (1=1)
					and evn.{$data['EvnClass_SysNick']}_id = :Evn_id
				limit 1
			";
			if ($HL7DocCode>0){
				//если указан $HL7DocCode, обращаемся в минибиблиотеку по HL7
				$this->load->model("EMDHL7OutDoc_model","EMDHL7OutDoc");
				$this->EMDHL7OutDoc->setCode($HL7DocCode);
				$doc_data=$this->EMDHL7OutDoc->getMetaDoc($data);
			} else {
				$doc_data = $this->queryResult($query, $data);
				//echo '<pre>',print_r(getDebugSQL($query, $data)),'</pre>'; die();
			}
			if (!empty($doc_data[0])) { $doc_data = $doc_data[0]; }
		}

		$response = array();

		if (!empty($doc_data)) {

			// определяем номер карты
			if (empty($doc_data['PersonCard_Code']) && !empty($doc_data['LpuSectionProfile_fedid'])) {

				$LpuAttachType_id = 1;
				$select = " coalesce(curr.PersonCard_Code,alt.PersonCard_Code) as \"PersonCard_Code\"";
				$alter = "
						left join v_PersonCard alt on alt.Person_id = :Person_id and alt.LpuAttachType_id = 4
					";

				// посещение в гинекологии
				if (in_array($doc_data['LpuSectionProfile_fedid'], array(138, 139))) {
					$LpuAttachType_id = 2;
					$select = " coalesce(curr.PersonCard_Code,gen.PersonCard_Code,alt.PersonCard_Code) as \"PersonCard_Code\"";
					$alter = "
							left join v_PersonCard gen on gen.Person_id = :Person_id and gen.LpuAttachType_id = 1
							left join v_PersonCard alt on alt.Person_id = :Person_id and alt.LpuAttachType_id = 4
						";
				}

				// посещение в стоматологии
				if (in_array($doc_data['LpuSectionProfile_fedid'], array(87,88,89,90,91,92))) {
					$LpuAttachType_id = 3;
					$select = " coalesce(curr.PersonCard_Code,gen.PersonCard_Code,alt.PersonCard_Code) as \"PersonCard_Code\"";
					$alter = "
							left join v_PersonCard gen on gen.Person_id = :Person_id and gen.LpuAttachType_id = 1
							left join v_PersonCard alt on alt.Person_id = :Person_id and alt.LpuAttachType_id = 4
						";
				}

				// посещение в психиатрии
				if (in_array($doc_data['LpuSectionProfile_fedid'], array(74, 75))) {
					$LpuAttachType_id = 4;
					$select = " coalesce(curr.PersonCard_Code,gen.PersonCard_Code) as \"PersonCard_Code\"";
					$alter = "
							left join v_PersonCard gen on gen.Person_id = :Person_id and gen.LpuAttachType_id = 1
						";
				}

				$doc_data['PersonCard_Code'] = $this->getFirstResultFromQuery("
						select
							{$select}
						from v_PersonCard curr
						{$alter}
						where
							curr.Person_id = :Person_id
							and curr.LpuAttachType_id = :LpuAttachType_id
							and curr.PersonCard_endDate is null
						order by curr.PersonCard_begDate
						limit 1
					", array('LpuAttachType_id' => $LpuAttachType_id, 'Person_id' => $doc_data['Person_id'])
				);
			}

			$response['doc_data'] = $doc_data;

			if (empty($EMDDocumentTypeLocal_id)) {
				return array('Error_Msg' => 'Не удалось определить тип документа');
			}

			$queryParams = array(
				'EMDRegistry_Num' => !empty($DocNum) ? (string) $DocNum : (string) $doc_data['docNum'],
				'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
				'pmUser_insID' => $data['pmUser_id'],
				'pmUser_updID' => $data['pmUser_id'],
				'EMDRegistry_EMDDate' => (!empty($doc_data['updDT']) ? $doc_data['updDT'] : 'NOW()'),

				// доп. параметры
				'Lpu_tid' => (!empty($doc_data['Lpu_tid']) ? $doc_data['Lpu_tid'] : null),
				'LpuBuilding_frmo' => (!empty($doc_data['LpuBuilding_frmo']) ? $doc_data['LpuBuilding_frmo'] : null),
				'LpuBuilding_frmoName' => (!empty($doc_data['LpuUnit_Name']) ? $doc_data['LpuUnit_Name'] : null),
				'Person_SurName' => (!empty($doc_data['Person_SurName']) ? $doc_data['Person_SurName'] : null),
				'Person_FirName' => (!empty($doc_data['Person_FirName']) ? $doc_data['Person_FirName'] : null),
				'Person_SecName' => (!empty($doc_data['Person_SecName']) ? $doc_data['Person_SecName'] : null),
				'Person_Snils' => (!empty($doc_data['Person_Snils']) ? $doc_data['Person_Snils'] : null),
				'Person_EdNum' => (!empty($doc_data['Person_EdNum']) ? $doc_data['Person_EdNum'] : null),
				'Person_Gender' => (!empty($doc_data['Person_Gender']) ? $doc_data['Person_Gender'] : null),
				'Person_Birthday' => (!empty($doc_data['Person_Birthday']) ? $doc_data['Person_Birthday'] : null),
				'EMDRegistry_NumCard' => (!empty($doc_data['PersonCard_Code']) ? $doc_data['PersonCard_Code'] : null),
				'EMDRegistry_HealthCareClause' => (!empty($doc_data['MedicalCareType_id']) ? $doc_data['MedicalCareType_id'] : $MedicalCareType_id),
				'Lpu_sid' => (!empty($doc_data['Lpu_sid']) ? $doc_data['Lpu_sid'] : null),

				// todo: выпиливать все-таки не будем, чтобы сохранить связь с событиями в промеде
				'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
				'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID'],
			);

			$queryParams = array_merge($queryParams, $doc_data);

			$filter = ' and "LpuBuilding_id" is null ';
			if (!empty($queryParams['LpuBuilding_id'])) $filter = '  and "LpuBuilding_id" = :LpuBuilding_id ';

			// проверяем возможно уже есть такой ЭМД, если есть то обновляем
			$query = '
				SELECT
					"EMDRegistry_id",
					"pmUser_updID"
				FROM "EMD"."EMDRegistry"
				WHERE (1=1)
					and "EMDRegistry_Num" = :EMDRegistry_Num
					and "Person_id" = :Person_id
					and "Lpu_id" = :Lpu_id
					and "EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
					'.$filter
			;

			$emd = $this->queryResult($query, $queryParams, $this->emddb);
			if (!empty($emd[0]['EMDRegistry_id'])) {

				// обновляем дату и юзера
				$this->emddb->query('
					update "EMD"."EMDRegistry"
					set
						"pmUser_updID" = :pmUser_updID,
						"EMDRegistry_updDT" = NOW(),
						"EMDRegistry_EMDDate" = :EMDRegistry_EMDDate
					where "EMDRegistry_id" = :EMDRegistry_id
				', array(
					'EMDRegistry_id' => $emd[0]['EMDRegistry_id'],
					'pmUser_updID' => $data['pmUser_id'],
					'EMDRegistry_EMDDate' => (!empty($doc_data['updDT']) ? $doc_data['updDT'] : 'NOW()')
				));

				$response['emd_registry'] = $emd;

			} else {

				$response['emd_registry'] =  $this->queryResult('
					INSERT INTO "EMD"."EMDRegistry" (
						"EMDRegistry_ObjectName",
						"EMDRegistry_ObjectID",
						"EMDRegistry_Num",
						"Lpu_id",
						"LpuBuilding_id",
						"Person_id",
						"EMDDocumentTypeLocal_id",
						"pmUser_insID",
						"pmUser_updID",
						"EMDRegistry_insDT",
						"EMDRegistry_updDT",
						"EMDRegistry_EMDDate",
						"Lpu_tid",
						"LpuBuilding_frmo",
						"LpuBuilding_frmoName",
						"Person_SurName",
						"Person_FirName",
						"Person_SecName",
						"Person_Snils",
						"Person_EdNum",
						"Person_Gender",
						"Person_Birthday",
						"EMDRegistry_NumCard",
						"EMDRegistry_HealthCareClause",
						"Lpu_sid"
					)
					VALUES (
						:EMDRegistry_ObjectName,
						:EMDRegistry_ObjectID,
						:EMDRegistry_Num,
						:Lpu_id,
						:LpuBuilding_id,
						:Person_id,
						:EMDDocumentTypeLocal_id,
						:pmUser_insID,
						:pmUser_updID,
						NOW(), -- EMDRegistry_insDT,
						NOW(), -- EMDRegistry_updDT,
						:EMDRegistry_EMDDate,
						:Lpu_tid,
						:LpuBuilding_frmo,
						:LpuBuilding_frmoName,
						:Person_SurName,
						:Person_FirName,
						:Person_SecName,
						:Person_Snils,
						:Person_EdNum,
						:Person_Gender,
						:Person_Birthday,
						:EMDRegistry_NumCard,
						:EMDRegistry_HealthCareClause,
						:Lpu_sid
					)
					RETURNING "EMDRegistry_id"
				', $queryParams, $this->emddb);
			}

			return $response;

		} else return array('Error_Msg' => 'Не удалось получить данные документа');
	}

	/**
	 * Удаление ЭМД
	 */
	function deleteEMDRegistryByEvn($data) {
		$queryParams = array(
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID'],
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = '
			update "EMD"."EMDRegistry"
			set
				"EMDRegistry_deleted" = 2,
				"EMDRegistry_delDT" = now(),
				"pmUser_delID" = :pmUser_id
			where
				"EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
				and "EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
		';

		$this->emddb->query($query, $queryParams);
	}

	/**
	 * Сохранение версии ЭМД
	 */
	function saveEMDVersion($data) {
		$queryParams = array(
			'EMDRegistry_id' => $data['EMDRegistry_id'],
			'EMDVersion_VersionNum' => null, // логичнее проставлять версию после подписания, а в данный момент мы просто сохраняем сформированный файл в регистр (он ещё не подписан)
			'EMDVersion_FileName' => $data['EMDVersion_FileName'],
			'EMDVersion_FilePath' => $data['EMDVersion_FilePath'],
			'EMDVersion_EmdrId' => null, // по всей видимости обновляется после регистрации у федералов
			'EMDVersion_RegistrationDate' => null, // по всей видимости обновляется после регистрации у федералов
			'EMDVersion_StoreTillDate' => null, // по всей видимости обновляется после регистрации у федералов
			'EMDVersion_actualDT' => !empty($data['updDT']) ? $data['updDT'] : null,
			'EMDFileFormat_id' => $data['EMDFileFormat_id'],
			'pmUser_insID' => $data['pmUser_id'],
			'pmUser_updID' => $data['pmUser_id'],
			'EMDVersion_AppealOrder' => !empty($data['EMDVersion_AppealOrder']) ? $data['EMDVersion_AppealOrder'] : null,
			'EMDVersion_Result' => !empty($data['EMDVersion_Result']) ? $data['EMDVersion_Result'] : null,
			'EMDVersion_begDate' => !empty($data['EMDVersion_begDate']) ? $data['EMDVersion_begDate'] : null,
			'EMDVersion_endDate' => !empty($data['EMDVersion_endDate']) ? $data['EMDVersion_endDate'] : null,
			'EMDVersion_BasicDiag' => !empty($data['EMDVersion_BasicDiag']) ? $data['EMDVersion_BasicDiag'] : null,
			'EMDVersion_FinalDiag'  => !empty($data['EMDVersion_FinalDiag']) ? $data['EMDVersion_FinalDiag'] : null
		);

		return $this->queryResult('
			INSERT INTO "EMD"."EMDVersion" (
				"EMDRegistry_id",
				"EMDVersion_VersionNum",
				"EMDVersion_FileName",
				"EMDVersion_FilePath",
				"EMDVersion_EmdrId",
				"EMDVersion_RegistrationDate",
				"EMDVersion_StoreTillDate",
				"EMDVersion_actualDT",
				"EMDFileFormat_id",
				"pmUser_insID",
				"pmUser_updID",
				"EMDVersion_insDT",
				"EMDVersion_updDT",
				"EMDVersion_AppealOrder",
				"EMDVersion_Result",
				"EMDVersion_begDate",
				"EMDVersion_endDate",
				"EMDVersion_BasicDiag",
				"EMDVersion_FinalDiag"
			)
			VALUES (
				:EMDRegistry_id,
				:EMDVersion_VersionNum,
				:EMDVersion_FileName,
				:EMDVersion_FilePath,
				:EMDVersion_EmdrId,
				:EMDVersion_RegistrationDate,
				:EMDVersion_StoreTillDate,
				:EMDVersion_actualDT,
				:EMDFileFormat_id,
				:pmUser_insID,
				:pmUser_updID,
				NOW(), -- EMDVersion_insDT,
				NOW(), -- EMDVersion_updDT,
				:EMDVersion_AppealOrder,
				:EMDVersion_Result,
				:EMDVersion_begDate,
				:EMDVersion_endDate,
				:EMDVersion_BasicDiag,
				:EMDVersion_FinalDiag
			)			
			RETURNING "EMDVersion_id"
		', $queryParams, $this->emddb);
	}

	/**
	 * Сохранение запроса в журнал
	 */
	function saveEMDJournalQuery($data) {
		$queryParams = array(
			'EMDRegistry_id' => $data['EMDRegistry_id'],
			'EMDQueryType_id' => $data['EMDQueryType_id'],
			'EMDJournalQuery_OutDT' => null,
			'EMDJournalQuery_InDT' => null,
			'EMDJournalQuery_OutParam' => null,
			'EMDJournalQuery_InParam' => null,
			'EMDJournalQuery_OutAnswerDT' => null,
			'EMDJournalQuery_InAnswerDT' => null,
			'EMDQueryStatus_id' => '',
			'pmUser_insID' => $data['pmUser_id'],
			'pmUser_updID' => $data['pmUser_id']
		);

		return $this->queryResult('
			INSERT INTO "EMD"."EMDJournalQuery" (
				"EMDRegistry_id",
				"EMDQueryType_id",
				"EMDJournalQuery_OutDT",
				"EMDJournalQuery_InDT",
				"EMDJournalQuery_OutParam",
				"EMDJournalQuery_InParam",
				"EMDJournalQuery_OutAnswerDT",
				"EMDJournalQuery_InAnswerDT",
				"EMDQueryStatus_id",
				"pmUser_insID",
				"pmUser_updID",
				"EMDJournalQuery_insDT",
				"EMDJournalQuery_updDT"
			)
			VALUES (
				:EMDRegistry_id,
				:EMDQueryType_id,
				:EMDJournalQuery_OutDT,
				:EMDJournalQuery_InDT,
				:EMDJournalQuery_OutParam,
				:EMDJournalQuery_InParam,
				:EMDJournalQuery_OutAnswerDT,
				:EMDJournalQuery_InAnswerDT,
				:EMDQueryStatus_id,
				:pmUser_insID,
				:pmUser_updID,
				NOW(), -- EMDJournalQuery_insDT,
				NOW() -- EMDJournalQuery_updDT
			)			
			RETURNING "EMDJournalQuery_id"
		', $queryParams, $this->emddb);
	}

	/**
	 * Получение данных подписи
	 */
	function loadSignaturesInfo($data) {
		if (empty($this->documents[$data['EMDRegistry_ObjectName']])) {
			return array('Error_Msg' => 'Подпись данных документов ещё не реализована');
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];

		$query = '
			SELECT
				s."pmUser_insID",
				emdv."EMDVersion_VersionNum",
				to_char(s."Signatures_insDT", \'DD.MM.YYYY\') as "Signatures_insDate",
				to_char(s."Signatures_insDT", \'HH24:MI:SS\') as "Signatures_insTime",
				emdv."EMDVersion_FilePath",
				s."Signatures_Hash",
			    emdc."EMDCertificate_id",
				emdc."EMDCertificate_Serial",
				emdc."EMDCertificate_CommonName",
				to_char(emdc."EMDCertificate_begDT", \'DD.MM.YYYY\') as "EMDCertificate_begDate",
				to_char(emdc."EMDCertificate_endDT", \'DD.MM.YYYY\') as "EMDCertificate_endDate"
			FROM
				"EMD"."EMDRegistry" emdr
				inner join "EMD"."EMDVersion" emdv on emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
				inner join "EMD"."EMDSignatures" emds on emds."EMDVersion_id" = emdv."EMDVersion_id"
				inner join "EMD"."Signatures" s on s."Signatures_id" = emds."Signatures_id"
				inner join "EMD"."EMDCertificate" emdc on emdc."EMDCertificate_id" = s."EMDCertificate_id"
			WHERE
				emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
				and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
			ORDER BY
				s."Signatures_insDT" desc
			LIMIT 1
		';

		$resp = $this->queryResult($query, array(
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
		), $this->emddb);

		if (!empty($resp[0])) {
			if (!empty($resp[0]['EMDVersion_FilePath']) && file_exists($resp[0]['EMDVersion_FilePath'])) {
				$file_path = $resp[0]['EMDVersion_FilePath'];
				if (!empty($doc['addStampAfter'])) {
					$stamped_file_path = $this->createStampOverPdf($resp[0]);
					if (!empty($stamped_file_path)) {
						$file_path = $stamped_file_path;
					}
				}
				$resp[0]['Document_Link'] = '<a target="_blank" href="' . $file_path . '">Просмотреть</a>';
			} else {
				$resp[0]['Document_Link'] = '';
			}

			$result_user = $this->db->query("
				select
					pmUser_Name as \"pmUser_Name\"
				from
					v_pmUser
				where
					pmUser_id = :pmUser_id
			", array(
				'pmUser_id' => $resp[0]['pmUser_insID']
			));

			if (is_object($result_user)) {
				$resp_user = $result_user->result('array');
				if (!empty($resp_user[0]['pmUser_Name'])) {
					$resp[0]['pmUser_Name'] = $resp_user[0]['pmUser_Name'];
				}
			}
		}

		return $resp;
	}

	/**
	 * Проверка подписи документа
	 */
	function verifySignature($data) {
		$resp = $this->queryResult('
			select
				emdr."EMDRegistry_id",
				emdc."EMDCertificate_OpenKey",
				s."Signatures_SignedData",
				emdv."EMDVersion_FilePath"
			from
				"EMD"."EMDSignatures" emds 
				inner join "EMD"."EMDVersion" emdv on emds."EMDVersion_id" = emdv."EMDVersion_id"
				inner join "EMD"."EMDRegistry" emdr on emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
				inner join "EMD"."Signatures" s on s."Signatures_id" = emds."Signatures_id"
				inner join "EMD"."EMDCertificate" emdc on emdc."EMDCertificate_id" = s."EMDCertificate_id"
			where
				emds."EMDSignatures_id" = :EMDSignatures_id
		', array(
			'EMDSignatures_id' => $data['EMDSignatures_id']
		), $this->emddb);

		if (empty($resp[0]['EMDRegistry_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных документа');
		}

		$valid = 1;
		// с помощью OpenSSL:
		$this->load->helper('openssl');
		$verified = checkSignature($resp[0]['EMDCertificate_OpenKey'], $resp[0]['EMDVersion_FilePath'], base64_decode($resp[0]['Signatures_SignedData']), true, true);
		if ($verified) {
			$valid = 2;
		}

		return array('Error_Msg' => '', 'valid' => $valid);
	}

	/**
	 * Проверка возможности подписания
	 */
	function checkBeforeSign($data) {
		if (empty($this->documents[$data['EMDRegistry_ObjectName']])) {
			throw new Exception('Подпись данных документов ещё не реализована');
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];

		$this->load->helper('openssl');
		$resp_cert = $this->queryResult('
			select
				"EMDCertificate_id",
				"EMDCertificate_OGRN",
				"EMDCertificate_OpenKey"
			from "EMD"."EMDCertificate"
			where (1=1)
				and "EMDCertificate_id" = :EMDCertificate_id
		', [
			'EMDCertificate_id' => $data['EMDCertificate_id']
		], $this->emddb);

		if (empty($resp_cert[0]['EMDCertificate_id'])) {
			throw new Exception('Не найден указанный сертификат.');
		}
		if (!checkCertificateCenter($resp_cert[0]['EMDCertificate_OpenKey'])) {
			throw new Exception('Сертификат выдан неаккредитованным УЦ, подписание невозможно.');
		}

		if (!empty($data['isMOSign'])) {
			// проверки для подписи от МО
			if (empty($data['session']['lpu_id'])) {
				throw new Exception('Для подписания от МО пользователь должен быть связан с МО');
			}

			$ogrn = $this->getFirstResultFromQuery('
				select
					Lpu_OGRN as "Lpu_OGRN"
				from
					v_Lpu
				where Lpu_id = :Lpu_id
			  	limit 1
			', [
				'Lpu_id' => $data['session']['lpu_id']
			]);

			if (empty($ogrn)) {
				throw new Exception('Для текущей МО не указан номер ОГРН. Подписание невозможно');
			}
			if ($resp_cert[0]['EMDCertificate_OGRN'] != $ogrn) {
				throw new Exception('Данная подпись не является подписью вашей организации');
			}

			if (!empty($data['EMDVersion_id'])) {
				$reg_check = $this->queryResult('
					select
						"EMDVersion_RegistrationDate",
						"EMDVersion_id"
					from "EMD"."EMDVersion"
					where (1=1)
						and "EMDVersion_id" = :EMDVersion_id
						and "EMDVersion_RegistrationDate" is not null
				', ['EMDVersion_id' => $data['EMDVersion_id']], $this->emddb);

				if (!empty($reg_check[0]['EMDVersion_id'])) {
					throw new Exception('Версия документа зарегистрирована в РЭМД ЕГИСЗ. Подписание невозможно.');
				}

				$need_sign = $this->queryResult('
					select
						emdv."EMDVersion_id",
						dt."EMDDocumentType_IsNeedSignatures"
					from "EMD"."EMDVersion" emdv
					inner join "EMD"."EMDRegistry" emdr on emdr."EMDRegistry_id" = emdv."EMDRegistry_id"
					inner join "EMD"."EMDDocumentTypeLocal" dtl on dtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
					inner join "EMD"."EMDDocumentType" dt on dt."EMDDocumentType_id" = dtl."EMDDocumentType_id"
					where
						"EMDVersion_id" = :EMDVersion_id
						and dt."EMDDocumentType_IsNeedSignatures" is not null
				', ['EMDVersion_id' => $data['EMDVersion_id']], $this->emddb);

				if (empty($need_sign[0]['EMDVersion_id'])) {
					// разрашаем ещё подписывать, если предусмотрено листом согласования
					$need_sign = $this->queryResult('
						select
							almp."ApprovalListMedPersonal_id"
						from "EMD"."EMDVersion" emdv
							inner join "EMD"."EMDRegistry" emdr on emdr."EMDRegistry_id" = emdv."EMDRegistry_id"
							inner join "EMD"."ApprovalList" al on al."ApprovalList_ObjectName" = emdr."EMDRegistry_ObjectName" and al."ApprovalList_ObjectId" = emdr."EMDRegistry_ObjectID"
							inner join "EMD"."ApprovalListMedPersonal" almp on almp."ApprovalList_id" = al."ApprovalList_id"
						where
							"EMDVersion_id" = :EMDVersion_id						
							and almp."ApprovalListMedPersonal_IsSignature" = 2
						limit 1
					', ['EMDVersion_id' => $data['EMDVersion_id']], $this->emddb);

					if (empty($need_sign[0]['ApprovalListMedPersonal_id'])) {
						throw new Exception('Подписание от МО данного типа документа не предусмотрено.');
					}
				}
			}
		} else {
			if (empty($data['MedStaffFact_id'])) {
				throw new Exception('Подписание невозможно. Место работы сотрудника не определено');
			}

			if (!empty($data['session']['medpersonal_id'])) {
				// если передан медстаффакт, определим что он принадлежит этому медперсоналу
				$isMedPersonalMedStaffFact = $this->checkMedPersonalMedStaffFact($data);
				if (!$isMedPersonalMedStaffFact) {
					throw new Exception('Подписание невозможно. Переданное место работы не принадлежит этому сотруднику');
				}
			} else {
				throw new Exception('Подписание невозможно. Не удалось определить текущего сотрудника');
			}

			// проверки для подписи врача
			$resp_al = $this->queryResult('
				select
					"ApprovalList_id"
				from
					"EMD"."ApprovalList"
				where
					"ApprovalList_ObjectName" = :ApprovalList_ObjectName
					and "ApprovalList_ObjectId" = :ApprovalList_ObjectId
			', array(
				'ApprovalList_ObjectName' => $data['EMDRegistry_ObjectName'],
				'ApprovalList_ObjectId' => $data['EMDRegistry_ObjectID']
			), $this->emddb);

			if (!empty($resp_al[0]['ApprovalList_id'])) {
				$role = $this->queryResult('
					select
						almp."EMDPersonRole_id",
						empr."EMDPersonRole_Name"
					from
						"EMD"."EMDPersonRole" empr
						left join "EMD"."ApprovalListMedPersonal" almp on empr."EMDPersonRole_id" = almp."EMDPersonRole_id" and almp."ApprovalList_id" = :ApprovalList_id and almp."MedPersonal_id" = :MedPersonal_id
					where
						empr."EMDPersonRole_id" = :EMDPersonRole_id
					limit 1
				', [
					'ApprovalList_id' => $resp_al[0]['ApprovalList_id'],
					'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null,
					'EMDPersonRole_id' => $data['EMDPersonRole_id']
				], $this->emddb);

				if (empty($role[0]['EMDPersonRole_id'])) {
					// Подпись документа <данные из поля «Документ» текущей формы>  сотрудником <ФИО сотрудника> не требуется, так как он отсутствует в списке экспертов, визирующих документ
					$Person_Fin = $this->getFirstResultFromQuery("select Person_Fin as \"Person_Fin\" from v_MedPersonal where MedPersonal_id = :MedPersonal_id limit 1", [
						'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
					]);
					throw new Exception('Подпись документа сотрудником ' . $Person_Fin . ' (' . ($role[0]['EMDPersonRole_Name'] ?? 'роль не определена') . ') не требуется, так как он отсутствует в списке экспертов, визирующих документ');
				} else if (!empty($data['EMDVersion_id'])) {
					// если подпись требуется, то проверяем, не подписан ли уже версия данным врачом.
					$MedStaffFactIds = [];
					$resp_msf = $this->queryResult("select MedStaffFact_id as \"MedStaffFact_id\" from v_MedStaffFact where MedPersonal_id = :MedPersonal_id", [
						'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
					]);
					foreach ($resp_msf as $one_msf) {
						$MedStaffFactIds[] = $one_msf['MedStaffFact_id'];
					}

					if (!empty($MedStaffFactIds)) {
						$resp_emds = $this->queryResult('
							select
								"EMDSignatures_id"
							from
								"EMD"."EMDSignatures"
							where
								"EMDVersion_id" = :EMDVersion_id
								and "EMDPersonRole_id" = :EMDPersonRole_id
								and "MedStaffFact_id" in (' . implode(',', $MedStaffFactIds) . ')
							limit 1
						', [
							'EMDVersion_id' => $data['EMDVersion_id'],
							'EMDPersonRole_id' => $data['EMDPersonRole_id']
						], $this->emddb);

						if (!empty($resp_emds[0]['EMDSignatures_id'])) {
							$Person_Fin = $this->getFirstResultFromQuery("select Person_Fin as \"Person_Fin\" from v_MedPersonal where MedPersonal_id = :MedPersonal_id limit 1", [
								'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
							]);
							throw new Exception('Документ уже подписан сотрудником ' . $Person_Fin . ' (' . ($role[0]['EMDPersonRole_Name'] ?? 'роль не определена') . '), повторная подпись не требуется');
						}
					} else {
						throw new Exception('У пользователя не надено ни одного рабочего места врача');
					}
				}
			}

			$EMDDocumentTypeLocal_id = $this->getEMDDocumentTypeLocal([
				'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
				'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
			]);

			$postCheckPassed = false;
			$rules = $this->queryResult('
				select
					sr."EMDPersonRole_id",
					sr."EMDSignatureRules_Post",
				    sr."EMDSignatureRules_PostCheck"
				from
					"EMD"."EMDDocumentTypeLocal" tloc
					inner join "EMD"."EMDDocumentType" t on t."EMDDocumentType_id" = tloc."EMDDocumentType_id"
					inner join "EMD"."EMDSignatureRules" sr on sr."EMDDocumentType_id" = t."EMDDocumentType_id"
				where
					tloc."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
			', [
				'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
				'EMDPersonRole_id' => $data['EMDPersonRole_id']
			], $this->emddb);

			if (false && !empty($rules)) { // временно отключаем проверку должности
				$resp_msf = $this->queryResult("
					select
						mp.MedPost_Code as \"MedPost_Code\"
					from
						v_MedStaffFact msf
						inner join persis.Post p on p.id = msf.Post_id
						inner join nsi.MedPost mp on mp.MedPost_id = p.MedPost_id
					where
						msf.MedStaffFact_id = :MedStaffFact_id
					limit 1
				", [
					'MedStaffFact_id' => $data['MedStaffFact_id']
				]);
				if (!empty($resp_msf[0]['MedPost_Code'])) {
					foreach ($rules as $rule) {
						$postArr = [];
						if (!empty($rule['EMDSignatureRules_Post'])) {
							$rule['EMDSignatureRules_Post'] = preg_replace('/\s/u', '', $rule['EMDSignatureRules_Post']);
							$postArr = explode(',', $rule['EMDSignatureRules_Post']);
						}

						if (
							$rule['EMDPersonRole_id'] == $data['EMDPersonRole_id']
							&& (
								$rule['EMDSignatureRules_PostCheck'] == 1 // не строгая проверка
								|| empty($postArr) // не указаны должности
								|| in_array($resp_msf[0]['MedPost_Code'], $postArr) // должность соответствует
							)
						) {
							$postCheckPassed = true;
						}
					}
				}
			} else {
				$postCheckPassed = true;
			}

			if (!$postCheckPassed) {
				throw new Exception('Ваша должность не соответствует требованиям подписания документа "' . $doc['docName'] . '". Выберите другую роль или обратитесь к администратору для настройки параметров должности');
			}
		}

		return ['Error_Msg' => ''];
	}

	/**
	 * Сохранение подписи
	 */
	function saveEMDSignatures($data) {
		$this->checkBeforeSign($data);

		// Для документов с листом согласования, если подписание производится физ лицом (форма вызвана НЕ по кнопке «Подписать от МО»), выполняется проверка  наличия сотрудника, указанного в поле «Сотрудник» в списке мед. сотрудников листа согласования. Если такой сотрудник отсутствует в листе согласования, то сообщение об ошибке
		if (empty($data['isMOSign'])) {
			$resp_al = $this->queryResult('
				select
					"ApprovalList_id"
				from
					"EMD"."ApprovalList"
				where
					"ApprovalList_ObjectName" = :ApprovalList_ObjectName
					and "ApprovalList_ObjectId" = :ApprovalList_ObjectId
			', array(
				'ApprovalList_ObjectName' => $data['EMDRegistry_ObjectName'],
				'ApprovalList_ObjectId' => $data['EMDRegistry_ObjectID']
			), $this->emddb);

			if (!empty($resp_al[0]['ApprovalList_id'])) {
				$role = $this->queryResult('
					select
						almp."EMDPersonRole_id",
						empr."EMDPersonRole_Name"
					from
						"EMD"."EMDPersonRole" empr
						left join "EMD"."ApprovalListMedPersonal" almp on empr."EMDPersonRole_id" = almp."EMDPersonRole_id" and almp."ApprovalList_id" = :ApprovalList_id and almp."MedPersonal_id" = :MedPersonal_id
					where
						empr."EMDPersonRole_id" = :EMDPersonRole_id
					limit 1
				', [
					'ApprovalList_id' => $resp_al[0]['ApprovalList_id'],
					'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null,
					'EMDPersonRole_id' => $data['EMDPersonRole_id']
				], $this->emddb);

				if (empty($role[0]['EMDPersonRole_id'])) {
					// Подпись документа <данные из поля «Документ» текущей формы>  сотрудником <ФИО сотрудника> не требуется, так как он отсутствует в списке экспертов, визирующих документ
					$Person_Fin = $this->getFirstResultFromQuery("select Person_Fin as \"Person_Fin\" from v_MedPersonal where MedPersonal_id = :MedPersonal_id limit 1", [
						'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
					]);
					return ['Error_Msg' => 'Подпись документа сотрудником ' . $Person_Fin . ' (' . ($role[0]['EMDPersonRole_Name'] ?? 'роль не определена') . ') не требуется, так как он отсутствует в списке экспертов, визирующих документ'];
				} else {
					// если подпись требуется, то проверяем, не подписан ли уже версия данным врачом.
					$MedStaffFactIds = [];
					$resp_msf = $this->queryResult("select MedStaffFact_id as \"MedStaffFact_id\" from v_MedStaffFact where MedPersonal_id = :MedPersonal_id", [
						'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
					]);
					foreach ($resp_msf as $one_msf) {
						$MedStaffFactIds[] = $one_msf['MedStaffFact_id'];
					}

					if (!empty($MedStaffFactIds)) {
						$resp_emds = $this->queryResult('
							select
								"EMDSignatures_id"
							from
								"EMD"."EMDSignatures"
							where
								"EMDVersion_id" = :EMDVersion_id
								and "EMDPersonRole_id" = :EMDPersonRole_id
								and "MedStaffFact_id" in (' . implode(',', $MedStaffFactIds) . ')
							limit 1
						', [
							'EMDVersion_id' => $data['EMDVersion_id'],
							'EMDPersonRole_id' => $data['EMDPersonRole_id']
						], $this->emddb);

						if (!empty($resp_emds[0]['EMDSignatures_id'])) {
							$Person_Fin = $this->getFirstResultFromQuery("select Person_Fin as \"Person_Fin\" from v_MedPersonal where MedPersonal_id = :MedPersonal_id limit 1", [
								'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
							]);
							return ['Error_Msg' => 'Документ уже подписан сотрудником ' . $Person_Fin . ' (' . ($role[0]['EMDPersonRole_Name'] ?? 'роль не определена') . '), повторная подпись не требуется'];
						}
					} else {
						return ['Error_Msg' => 'У пользователя не надено ни одного рабочего места врача'];
					}
				}
			}
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];

		$queryParams = array(
			'SignaturesStatus_id' => 1,
			'Signatures_Hash' => $data['Signatures_Hash'],
			'EMDCertificate_id' => $data['EMDCertificate_id'],
			'pmUser_insID' => $data['pmUser_id'],
			'pmUser_updID' => $data['pmUser_id'],
			'Signatures_SignedData' => $data['Signatures_SignedData']
		);

		// это было актуально для сырой подписи, для cades не актуально видимо
		/*if (!empty($data['signType']) && $data['signType'] == 'cryptopro') {
			$hex = $data['Signatures_SignedData'];
			// HEX надо развернуть, криптопро зачем то делает повёрнутую подпись %)
			$newhex = '';
			while(strlen($hex) > 0) {
				$newhex = substr($hex, 0, 2) . $newhex;
				$hex = substr($hex, 2);
			}
			$queryParams['Signatures_SignedData'] = base64_encode(pack("H*", $newhex));
		}*/

		$resp = $this->queryResult('
			INSERT INTO "EMD"."Signatures" (
				"SignaturesStatus_id",
				"Signatures_Hash",
				"EMDCertificate_id",
				"pmUser_insID",
				"pmUser_updID",
				"Signatures_insDT",
				"Signatures_updDT",
				"Signatures_SignedData"
			)
			VALUES (
				:SignaturesStatus_id,
				:Signatures_Hash,
				:EMDCertificate_id,
				:pmUser_insID,
				:pmUser_updID,
				NOW(), -- Signatures_insDT,
				NOW(), -- Signatures_updDT,
				:Signatures_SignedData
			)
			RETURNING "Signatures_id"
		', $queryParams, $this->emddb);

		if (!empty($resp[0]['Signatures_id'])) {

			// получаем данные по мед. работнику службы, если место работы не передано с формы
			if (
				empty($data['MedStaffFact_id'])
				&& !empty($data['MedService_id'])
				&& !empty($data['session']['medpersonal_id'])
			) {
				$response = $this->getFirstResultFromQuery("
					select
						msf.MedStaffFact_id as \"MedStaffFact_id\"
					from v_MedService ms
					inner join v_MedStaffFact msf on msf.LpuSection_id = ms.LpuSection_id
					where
						ms.MedService_id = :MedService_id
						and msf.MedPersonal_id = :MedPersonal_id
					limit 1
				", array(
					'MedService_id' => $data['MedService_id'],
					'MedPersonal_id' => $data['session']['medpersonal_id']
				));
				if (!empty($response)) {
					$data['MedStaffFact_id'] = $response;
				}

			}


			// если передано место работы и это не подпись от МО
			if (!empty($data['MedStaffFact_id']) && empty($data['isMOSign'])) {

				if (!empty($data['session']['medpersonal_id'])) {
					// если передан медстаффакт, определим что он принадлежит этому медперсоналу
					$isMedPersonalMedStaffFact = $this->checkMedPersonalMedStaffFact($data);
					if (!$isMedPersonalMedStaffFact) {
						return array('Error_Msg' => 'Подписание невозможно. Переданное место работы не принадлежит этому сотруднику');
					}
				} else {
					return array('Error_Msg' => 'Подписание невозможно. Не удалось определить текущего сотрудника');
				}

			} else if (empty($data['MedStaffFact_id']) && !empty($data['session']['CurMedStaffFact_id'])) {
				// если место работы не передано, попробуем взять из сессии
				$data['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			}

			$MedPersonal_data = null;
			if (empty($data['isMOSign'])) {
				// получаем данные по мед. работнику
				$MedPersonal_data = $this->getFirstRowFromQuery("
					select
						msf.MedStaffFact_id as \"MedStaffFact_id\",
						msf.Person_SurName as \"MedPersonal_SurName\",
						msf.Person_FirName as \"MedPersonal_FirName\",
						msf.Person_SecName as \"MedPersonal_SecName\",
						msf.Person_BirthDay as \"MedPersonal_BirthDate\",
						msf.Person_Snils as \"MedPersonal_Snils\",
						frmpp.Frmr_id as \"MedPersonal_Post\",
					    frmns.FRMRNomenSpeciality_Code as \"MedPersonal_Speciality\"
					from
						v_MedStaffFact msf
						left join persis.Post Post on msf.Post_id = Post.id
						left join persis.FRMPPost frmpp on frmpp.id = Post.frmpEntry_id
						left join persis.Speciality spec on spec.id = Post.Speciality_id
						left join persis.FRMRNomenSpeciality frmns on frmns.FRMRNomenSpeciality_id = spec.FRMRNomenSpeciality_id
					where
						msf.MedStaffFact_id = :MedStaffFact_id
					limit 1
				", array(
					'MedStaffFact_id' => $data['MedStaffFact_id']
				));
			}

			$stage = 1;
			$current_stage = $this->queryResult('
				select
					MAX("EMDSignatures_Stage") as "EMDSignatures_Stage"
				from "EMD"."EMDSignatures"
				where "EMDVersion_id" = :EMDVersion_id
			', array('EMDVersion_id' => $data['EMDVersion_id']), $this->emddb);

			if (!empty($current_stage[0]['EMDSignatures_Stage'])) {
				$stage = intval($current_stage[0]['EMDSignatures_Stage']) + 1;
			}

			$queryParams = array(
				'EMDVersion_id' => $data['EMDVersion_id'],
				// если подписаваем от МО не проставляем медстаффакт
				'Signatures_id' => $resp[0]['Signatures_id'],
				'pmUser_insID' => $data['pmUser_id'],
				'pmUser_updID' => $data['pmUser_id'],
				'EMDSignatures_Stage' => $stage,
				'EMDPersonRole_id' => (!empty($data['isMOSign']) ? null : $data['EMDPersonRole_id'])
			);

			if (!empty($MedPersonal_data)) {
				$queryParams = array_merge($queryParams, $MedPersonal_data);
			} else {
				$queryParams = array_merge($queryParams, array(
					'MedStaffFact_id' => null,
					'MedPersonal_SurName' => null,
					'MedPersonal_FirName' => null,
					'MedPersonal_SecName' => null,
					'MedPersonal_BirthDate' => null,
					'MedPersonal_Snils' => null,
					'MedPersonal_Post' => null,
					'MedPersonal_Speciality' => null
				));
			}

			$resp_save = $this->queryResult('
				INSERT INTO "EMD"."EMDSignatures" (
					"EMDVersion_id",
					"MedStaffFact_id",
					"Signatures_id",
					"pmUser_insID",
					"pmUser_updID",
					"EMDSignatures_insDT",
					"EMDSignatures_updDT",
					"EMDSignatures_Stage",
					"EMDPersonRole_id",
					"MedPersonal_SurName",
					"MedPersonal_FirName",
					"MedPersonal_SecName",
					"MedPersonal_BirthDate",
					"MedPersonal_Snils",
					"MedPersonal_Post",
					"MedPersonal_Speciality"
				)
				VALUES (
					:EMDVersion_id,
					:MedStaffFact_id,
					:Signatures_id,
					:pmUser_insID,
					:pmUser_updID,
					-- EMDSignatures_insDT
					NOW(),
					-- EMDSignatures_updDT
					NOW(),
					:EMDSignatures_Stage,
					:EMDPersonRole_id,
					:MedPersonal_SurName,
					:MedPersonal_FirName,
					:MedPersonal_SecName,
					:MedPersonal_BirthDate,
					:MedPersonal_Snils,
					:MedPersonal_Post,
					:MedPersonal_Speciality
				)
				RETURNING "EMDSignatures_id"
			', $queryParams, $this->emddb);

			if (!empty($resp_save[0]['EMDSignatures_id'])) {

				// если этап подписания начальный, обновляем версию, простаялвяем признак
				if ($stage == 1) {
					// подпись сохранена
					// проставляем версию
					$this->emddb->query('
						update
							"EMD"."EMDVersion" e1
						set
							"EMDVersion_VersionNum" = (
								select
									COALESCE(MAX("EMDVersion_VersionNum") + 1, 1)
								from
									"EMD"."EMDVersion" e2
									inner join "EMD"."EMDRegistry" emdr on e2."EMDRegistry_id" = emdr."EMDRegistry_id"
								where
									emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
									and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
							)		
						where
							e1."EMDVersion_id" = :EMDVersion_id
					', array(
						'EMDVersion_id' => $data['EMDVersion_id'],
						'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
						'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
					));

					if ($this->usePostgreLis && $data['EMDRegistry_ObjectName'] == 'EvnUslugaPar') {
						$this->load->swapi('lis');
						$this->lis->POST('EMD/saveSignField', [
							'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
							'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
						]);
					} else {
						$this->saveSignField([
							'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
							'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID'],
							'pmUser_id' => $data['pmUser_id']
						]);
					}
				}

				// определяем должен ли быть зареган док
				$register_rules = $this->queryResult('
					select
						ver."EMDVersion_id",
						sr."EMDPersonRole_id",
						sr."EMDSignatureRules_MinCount",
						sr."EMDSignatureRules_MaxCount",
						t."EMDDocumentType_IsNeedSignatures"
					from
						"EMD"."EMDVersion" ver
						inner join "EMD"."EMDRegistry" reg on reg."EMDRegistry_id" = ver."EMDRegistry_id"
						inner join "EMD"."EMDDocumentTypeLocal" tloc on tloc."EMDDocumentTypeLocal_id" = reg."EMDDocumentTypeLocal_id"
						inner join "EMD"."EMDDocumentType" t on t."EMDDocumentType_id" = tloc."EMDDocumentType_id" and t."EMDFileFormat_id" = ver."EMDFileFormat_id"
						inner join "EMD"."EMDSignatureRules" sr on sr."EMDDocumentType_id" = t."EMDDocumentType_id"
					where
						ver."EMDVersion_id" = :EMDVersion_id
					', array('EMDVersion_id' => $data['EMDVersion_id']
				), $this->emddb);

				if (!empty($register_rules[0])) {
					// по умолчанию, проверка на подпись от МО является пройденной
					$passMoSign = true;
					// если по правилам подписания нужна подпись от МО, проверяем наличие этой подписи
					if (!empty($register_rules[0]['EMDDocumentType_IsNeedSignatures'])) {
						// ставим проверку на подпись от МО не пройденной
						$passMoSign = false;

						$moSign = $this->queryResult('
							select
								count("EMDVersion_id") as "count"
							from "EMD"."EMDSignatures" s
							where 
								s."EMDVersion_id" = :EMDVersion_id
								and s."MedStaffFact_id" is null
						', array('EMDVersion_id' => $data['EMDVersion_id']
						), $this->emddb);

						if (!empty($moSign[0]['count'])) {
							// если есть подпись от МО, то изменяем флаг
							$passMoSign = true;
						}
					}

					if ($passMoSign) {
						$passOtherSign = true;
						foreach($register_rules as $one_rule) {
							// получаем кол-во подписей для версии
							// исключая подписи от МО
							$signs = $this->queryResult('
								select
									count("EMDSignatures_id") as "count"
								from "EMD"."EMDSignatures" s
								where 
									s."EMDVersion_id" = :EMDVersion_id
									and s."EMDPersonRole_id" = :EMDPersonRole_id
							', [
								'EMDVersion_id' => $data['EMDVersion_id'],
								'EMDPersonRole_id' => $one_rule['EMDPersonRole_id']
							], $this->emddb);

							if (!isset($signs[0]['count']) || $signs[0]['count'] < $one_rule['EMDSignatureRules_MinCount'] || $signs[0]['count'] > $one_rule['EMDSignatureRules_MaxCount']) {
								$passOtherSign = false;
							}
						}

						if ($passOtherSign) {
							// проставляем признак готовности к регистрации
							$this->emddb->query('
								update
									"EMD"."EMDVersion"
								set
									"EMDVersion_IsReady" = 1
								where
									"EMDVersion_id" = :EMDVersion_id
							', array('EMDVersion_id' => $data['EMDVersion_id']));
						}
					}
				}

				return array('Error_Msg' => '', 'EMDSignatures_id' => $resp_save[0]['EMDSignatures_id']);
			}
		} else {
			return array('Error_Msg' => 'Ошибка сохранения подписи');
		}
	}

	/**
	 * Сохранение признака подписи в таблице
	 */
	function saveSignField($data) {
		// проставляем признак подписания в БД
		if ($data['EMDRegistry_ObjectName'] == 'ReportRun') {
			$db = $this->load->database('reports', true); // БД для отчётов
		} else {
			$db = $this->db;
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];
		$tableName = $doc['signObject'];
		if (!empty($doc['schemaName'])) {
			$tableName = $doc['schemaName'] . '.' . $tableName;
		}

		$db->query("
			update
				{$tableName}
			set
				{$doc['signField']} = 2,
				{$doc['signDateField']} = dbo.tzGetDate(),
				{$doc['signUserField']} = :pmUser_id
			where
				{$doc['signObject']}_id = :EMDRegistry_ObjectID
		", array(
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID'],
			'pmUser_id' => $data['pmUser_id']
		));

		switch ($data['EMDRegistry_ObjectName']) {
			case 'EvnUslugaPar':
				$resp_elr = $this->queryResult("
					select
						elr.EvnLabRequest_id as \"EvnLabRequest_id\"
					from
						v_EvnUslugaPar eup
						inner join v_EvnLabRequest elr on elr.EvnDirection_id = eup.EvnDirection_id
					where
						eup.EvnUslugaPar_id = :EvnUslugaPar_id
					limit 1
				", array(
					'EvnUslugaPar_id' => $data['EMDRegistry_ObjectID']
				));
				if (!empty($resp_elr[0]['EvnLabRequest_id'])) {
					// кэшируем статус подписи услуг в заявке
					$this->load->model('EvnLabRequest_model');
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
						'EvnLabRequest_id' => $resp_elr[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
				break;
			case 'EvnXml':
				$resp_efr = $this->queryResult("
					select
						efr.EvnFuncRequest_id as \"EvnFuncRequest_id\",
						eup.EvnDirection_id as \"EvnDirection_id\",
						efr.MedService_id as \"MedService_id\"
					from
						v_EvnXml ex
						inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id = ex.Evn_id
						inner join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = eup.EvnDirection_id
					where
						ex.EvnXml_id = :EvnXml_id
					limit 1
				", array(
					'EvnXml_id' => $data['EMDRegistry_ObjectID']
				));
				if (!empty($resp_efr[0]['EvnFuncRequest_id'])) {
					// кэшируем статус подписи услуг в заявке
					$this->load->model('EvnFuncRequest_model');
					$this->EvnFuncRequest_model->ReCacheFuncRequestUslugaCache(array(
						'MedService_id' => $resp_efr[0]['MedService_id'],
						'EvnFuncRequest_id' => $resp_efr[0]['EvnFuncRequest_id'],
						'EvnDirection_id' => $resp_efr[0]['EvnDirection_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
				break;
		}
	}
	
	/**
	 * Получение идентификатора справочника
	 */
	function getEMDDocumentTypeLocal($data) {
		switch($data['EMDRegistry_ObjectName']) {
			case 'PersonDisp':
				return 12;
				break;
			case 'ReportRun':
				return 15;
				break;
			case 'PersonPrivilegeReq':
				return 21;
				break;
			case 'PersonPrivilegeReqAns':
				return 22;
				break;
			case 'DeathSvid':
				return 23;
				break;
			case 'BirthSvid':
				return 24;
				break;
		}

		// остальные можно найти по связи с EvnClass
		$EvnClass_id = null;
		$XmlType_id = null;
		$XmlTypeKind_id = null;
		if ($data['EMDRegistry_ObjectName'] == 'EvnXml') {
			$resp = $this->queryResult("
				SELECT
					ex.Evn_id as \"Evn_id\",
					e.EvnClass_id as \"EvnClass_id\",
					ex.XmlType_id as \"XmlType_id\",
					xt.XmlTypeKind_id as \"XmlTypeKind_id\"
				FROM v_EvnXml ex
					inner join v_Evn e on e.Evn_id = ex.Evn_id
					left join v_XmlTemplate xt on xt.XmlTemplate_id = ex.XmlTemplate_id
				WHERE
					ex.EvnXml_id = :EMDRegistry_ObjectID
				limit 1
			", [
				'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
			]);
			if (!empty($resp[0]['EvnClass_id'])) {
				$EvnClass_id = $resp[0]['EvnClass_id'];
				$XmlType_id = $resp[0]['XmlType_id'];
				$XmlTypeKind_id = $resp[0]['XmlTypeKind_id'];
			}
		} else {
			$resp = $this->queryResult("
				SELECT
					EvnClass_id as \"EvnClass_id\"
				FROM v_EvnClass
				WHERE
					EvnClass_SysNick = :EMDRegistry_ObjectName
				limit 1
			", [
				'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName']
			]);
			if (!empty($resp[0]['EvnClass_id'])) {
				$EvnClass_id = $resp[0]['EvnClass_id'];
			}
		}

		$EMDDocumentTypeLocal_id = null;
		if (!empty($EvnClass_id)) {
			$queryParams = ['EvnClass_id' => $EvnClass_id];
			$filter_edtl = "";
			if (!empty($XmlType_id)) {
				$queryParams['XmlType_id'] = $XmlType_id;
				$filter_edtl .= ' and "XmlType_id" = :XmlType_id';
			} else {
				$filter_edtl .= ' and "XmlType_id" is null';
			}
			if (!empty($XmlTypeKind_id) && $XmlTypeKind_id == 7) {
				$filter_edtl = ' and "XmlTypeKind_id" = :XmlTypeKind_id';
				$queryParams['XmlTypeKind_id'] = $XmlTypeKind_id;
			}

			$resp_edtl = $this->queryResult('
				SELECT
					"EMDDocumentTypeLocal_id"
				FROM
					"EMD"."EMDEvnDocumentType"
				WHERE
					"EvnClass_id" = :EvnClass_id
					' . $filter_edtl . '
			', $queryParams, $this->emddb);

			if (!empty($resp_edtl[0]['EMDDocumentTypeLocal_id'])) {
				$EMDDocumentTypeLocal_id = $resp_edtl[0]['EMDDocumentTypeLocal_id'];
			}
		}

		return $EMDDocumentTypeLocal_id;
	}

	/**
	 * Проверка чтобы убедится что переданный с клиента MSF, действительно этого сотрудника
	 */
	function checkMedPersonalMedStaffFact($data){

		$result = false;

		$queryResult = $this->getFirstResultFromQuery("
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\"
			from v_MedStaffFact msf
			where 
				msf.MedStaffFact_id = :MedStaffFact_id
				and msf.MedPersonal_id = :MedPersonal_id
			limit 1
		", array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['session']['medpersonal_id'],
		));

		if (!empty($queryResult)) $result = true;
		return $result;
	}

	/**
	 * Получение списка документов для подписи
	 * @param $data
	 */
	function getEMDSignWindowGrid($data) {
		$doc = $this->documents[$data['EMDRegistry_ObjectName']];
		$view = $doc['viewName'];
		$table = $doc['signObject'];
		if (!empty($doc['schemaName'])) {
			$view = $doc['schemaName'] . '.' . $view;
			$table = $doc['schemaName'] . '.' . $table;
		}

		$fields = '';
		$joins = '';
		$db = $this->db;
		switch($data['EMDRegistry_ObjectName']) {
			case 'ReportRun':
				$db = $this->load->database('reports', true); // БД для отчётов
				$fields .= ', r.Report_Caption as "Report_Caption"';
				$fields .= ', t.Report_Params as "Report_Params"';
				$joins .= ' left join rpt.Report r on r.Report_id = t.Report_id';
				break;
			case 'EvnRecept':
				$fields .= ', t.EvnRecept_Ser as "EvnRecept_Ser"';
				$fields .= ', t.EvnRecept_IsKEK as "EvnRecept_IsKEK"';
				$fields .= ', t.CauseVK_id as "CauseVK_id"';
				$fields .= ', t.PrescrSpecCause_id as "PrescrSpecCause_id"';
				break;
			case 'DeathSvid':
				$fields = ",
					(case when psr.Person_SurName			is null then ' Фамилия получателя,'		ELSE '' end ||
					 case when psr.Person_FirName			is null then ' Имя получателя,'			ELSE '' end ||
					 case when dtfrmis.DocumentTypeFRMIS_id	is null then ' Тип документа,'			ELSE '' end ||
					 case when psr.Document_Ser				is null then ' Серия документа,'		ELSE '' end ||
					 case when psr.Document_Num				is null then ' Номер документа,'		ELSE '' end ||
					 case when doc.Document_begDate			is null then ' Дата документа,'			ELSE '' end ||
					 case when od.Org_id					is null then ' Кем выдан,'				ELSE '' end ||
					 case when t.DeathSvid_GiveDate			is null then ' Дата получения МСС,'		ELSE '' end ||
					 case when t.DeathSvidRelation_id		is null then ' Отношение к умершему,'	ELSE '' end )
					as \"MissingDataList\"";
				$joins = " left join v_PersonState psr on psr.Person_id = t.Person_rid
					left join v_Document doc on doc.Document_id = psr.Document_id
					left join v_DocumentType dt on dt.DocumentType_id = doc.DocumentType_id
					left join v_DocumentTypeFRMIS dtfrmis on dt.DocumentTypeFRMIS_id = dtfrmis.DocumentTypeFRMIS_id
					left join v_OrgDep od on doc.OrgDep_id = od.OrgDep_id
					";
				break;
		}

		$MedStaffFact_id = $data['session']['CurMedStaffFact_id'] ?? null;
		if (
			empty($MedStaffFact_id)
			&& !empty($data['session']['CurMedService_id'])
			&& !empty($data['session']['medpersonal_id'])
		) {
			// получаем данные по мед. работнику службы
			$resp_ms = $this->queryResult("
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\"
				from v_MedService ms
				inner join v_MedStaffFact msf on msf.LpuSection_id = ms.LpuSection_id
				where 
					ms.MedService_id = :MedService_id
					and msf.MedPersonal_id = :MedPersonal_id
				limit 1
			", array(
				'MedService_id' => $data['session']['CurMedService_id'],
				'MedPersonal_id' => $data['session']['medpersonal_id']
			));

			if (!empty($resp_ms[0]['MedStaffFact_id'])) {
				$MedStaffFact_id = $resp_ms[0]['MedStaffFact_id'];
			}
		}

		$numberField = (!empty($doc['numberField']) && $doc['numberField'] !== '0' ? 't.' : '') . $doc['numberField'];

		$result = $db->query("
			select
				t.{$doc['idField']} as \"docId\",
				to_char(t.{$doc['dateField']}, 'dd.mm.yyyy') as \"docDate\",
				{$numberField} as \"docNumber\",
				s.{$doc['signField']} as \"IsSigned\",
				msf.Person_Fio as \"Person_Fio\",
				p.name as \"Post_Name\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				pu.pmUser_Name as \"pmUser_Name\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.LpuBuilding_id as \"LpuBuilding_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.Lpu_id as \"Lpu_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\"
				{$fields}
			from
				{$view} t
				left join {$table} s on s.{$doc['signObject']}_id = t.{$doc['idField']}
				left join v_MedStaffFact msf on msf.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_Lpu l on l.Lpu_id = msf.Lpu_id
				left join persis.Post p on p.id = msf.Post_id
				left join pmUserCache pu on pu.pmUser_id = :pmUser_id
				{$joins}
			where
				t.{$doc['idField']} IN ('" . implode("','", $data['EMDRegistry_ObjectIDs']) . "')
		", array(
			'MedStaffFact_id' => $MedStaffFact_id,
			'pmUser_id' => $data['pmUser_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		}

		return [];
	}

	/**
	 * Загрузка формы подписания
	 */
	function loadEMDSignWindow($data) {
		if (empty($this->documents[$data['EMDRegistry_ObjectName']])) {
			throw new Exception('Подпись данных документов ещё не реализована');
		}

		$safeIds = [];
		foreach($data['EMDRegistry_ObjectIDs'] as $id) {
			$safeIds[] = preg_replace('/[^0-9]/', '', $id);
		}
		$data['EMDRegistry_ObjectIDs'] = $safeIds;
		if (count($data['EMDRegistry_ObjectIDs']) < 1) {
			return [];
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];

		// проверки на возможость подписния
		$modelForCheck = '';
		switch ($data['EMDRegistry_ObjectName']) {
			case 'EvnPLDispDriver':
				$modelForCheck = 'EvnPLDispDriver_model';
				break;
			case 'EvnVK':
				$modelForCheck = 'ClinExWork_model';
				break;
			case 'EvnPrescrMse':
				$modelForCheck = 'Mse_model';
				break;
		}
		if (!empty($modelForCheck)) {
			$this->load->model($modelForCheck);
			foreach ($data['EMDRegistry_ObjectIDs'] as $EMDRegistry_ObjectID) {
				if (!$this->$modelForCheck->checkSignAccess([
					$data['EMDRegistry_ObjectName'] . '_id' => $EMDRegistry_ObjectID,
					'session' => $data['session']
				])) {
					return false;
				}
			}
		}

		if ($this->usePostgreLis && $data['EMDRegistry_ObjectName'] == 'EvnUslugaPar') {
			$this->load->swapi('lis');
			$respLis = $this->lis->GET('EMD/EMDSignWindowGrid', [
				'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
				'EMDRegistry_ObjectIDs' => $data['EMDRegistry_ObjectIDs']
			]);
			
			$resp = $respLis['data'] ?? [];
		} else {
			$resp = $this->getEMDSignWindowGrid([
				'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
				'EMDRegistry_ObjectIDs' => $data['EMDRegistry_ObjectIDs'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		$response = [];
		foreach($resp as $respone) {
			if ($data['EMDRegistry_ObjectName'] == 'EvnUslugaPar') {
				$this->load->model('ApprovalList_model');
				$this->ApprovalList_model->saveApprovalList(array(
					'ApprovalList_ObjectName' => 'EvnUslugaPar',
					'ApprovalList_ObjectId' => $respone['docId'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
			
			$EMDDocumentTypeLocal_id = $this->getEMDDocumentTypeLocal([
				'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
				'EMDRegistry_ObjectID' => $respone['docId']
			]);

			switch($data['EMDRegistry_ObjectName']) {
				case 'ReportRun':
					$Document_Name = $doc['docName'] . ' "' . $respone['Report_Caption'] . '"' . ' с параметрами: ' . str_replace('&', ', ', $respone['Report_Params']);
					$Document_Num = '';
					$Document_Date = $respone['docDate'];
					break;
				case 'EvnRecept':
					$Document_Name = $doc['docName'];
					$Document_Num = '';
					if (!empty($respone['EvnRecept_Ser'])) {
						$Document_Num .= 'серия ' . $respone['EvnRecept_Ser'];
					}
					if (!empty($respone['docNumber'])) {
						$Document_Num .= ' №' . $respone['docNumber'];
					}
					if (!empty($respone['docDate'])) {
						$Document_Num .= ' от ' . $respone['docDate'];
					}
					if (!empty($respone['EvnRecept_IsKEK']) && $respone['EvnRecept_IsKEK'] == 2) {
						$Document_Num .= '<br>Решение ВК: Да';
					}
					if (!empty($respone['CauseVK_id']) && $respone['CauseVK_id'] == 1) {
						$Document_Num .= '<br>Назначение ЛП по ТН или ЛП не входящего в стандарты: Да';
					}
					if (!empty($respone['PrescrSpecCause_id']) && $respone['PrescrSpecCause_id'] == 1) {
						$Document_Num .= '<br>Увеличение кол-ва ЛП из перечня ПКУ: Да';
					}
					$Document_Date = $respone['docDate'];
					break;
				case 'DeathSvid':
					if (!empty($resp[0]['MissingDataList'])) {
						throw new Exception('Документ не подлежит регистрации в РЭМД, так как не заполнены следующие данные Получателя свидетельства: ' . substr_replace($resp[0]['MissingDataList'],'. ',-1));
					}
				default:
					$Document_Name = $doc['docName'];
					$Document_Num = $respone['docNumber'];
					$Document_Date = $respone['docDate'];
					break;
			}

			// Если для документа предусмотрено создание листа согласования  (наличие объекта в справочнике «Список объектов, для которых требуется лист согласования»  см. ТЗ Лист согласования.docx пункт 1.5.1 Описание сущности для хранения справочника «Список объектов, для которых требуется создание листа согласования» в разрезе страны/региона (регион Пользователя ИЛИ регион NULL), действующими на текущую дату ),
			$resp_aol = $this->queryResult('
				select
					"ApprovalObjectList_id"
				from
					"EMD"."ApprovalObjectList"
				where
					"EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
					and coalesce("Region_id", CAST(:Region_id as bigint)) = :Region_id
					and coalesce("ApprovalObjectList_begDate", CAST(:curDate as date)) <= :curDate
					and coalesce("ApprovalObjectList_endDate", CAST(:curDate as date)) >= :curDate
			', array(
				'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
				'curDate' => date('Y-m-d'),
				'Region_id' => getRegionNumber()
			), $this->emddb);

			if (!empty($resp_aol[0]['ApprovalObjectList_id'])) {
				// то выполняется поиск связанного с записью объекта, листа согласования
				$resp_al = $this->queryResult('
					select
						"ApprovalList_id"
					from
						"EMD"."ApprovalList"
					where
						"ApprovalList_ObjectName" = :ApprovalList_ObjectName
						and "ApprovalList_ObjectId" = :ApprovalList_ObjectId
				', array(
					'ApprovalList_ObjectName' => $data['EMDRegistry_ObjectName'],
					'ApprovalList_ObjectId' => $respone['docId']
				), $this->emddb);

				if (!empty($resp_al[0]['ApprovalList_id'])) {
					$role = $this->queryResult('
						select
							role."EMDPersonRole_id"
						from
							"EMD"."ApprovalListMedPersonal" role
						where
							role."ApprovalList_id" = :ApprovalList_id
							and role."MedPersonal_id" = :MedPersonal_id
						limit 1
					', array(
						'ApprovalList_id' => $resp_al[0]['ApprovalList_id'],
						'MedPersonal_id' => $data['session']['medpersonal_id'] ?? null
					), $this->emddb);
				} else {
					// Если лист согласования не найден, то сообщение об ошибке
					// Проверка соответствия информации о сотрудниках правилам подписания документа с учетом рациональности:
					// По требованиям ЕГИСЗ (EMD"."EMDSignatureRules);
					$resp_rules = $this->queryResult('
						select
							sr."EMDSignatureRules_MinCount" as mincount,
							sr."EMDSignatureRules_MaxCount" as maxcount,
							pr."EMDPersonRole_Name"
						from
							"EMD"."EMDDocumentTypeLocal" tloc
							inner join "EMD"."EMDSignatureRules" sr on sr."EMDDocumentType_id" = tloc."EMDDocumentType_id"
							left join "EMD"."EMDPersonRole" pr on pr."EMDPersonRole_id" = sr."EMDPersonRole_id"
						where
							tloc."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
					', array(
						'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id
					), $this->emddb);

					// Если требования ЕГИСЗ отсутствуют, то по «прочим» требованиям (EMD"."EMDSignatureRulesLocal")
					if (empty($resp_rules)) {
						$resp_rules = $this->queryResult('
							select
								sr."EMDSignatureRulesLocal_MinCount" as mincount,
								sr."EMDSignatureRulesLocal_MaxCount" as maxcount,
								pr."EMDPersonRole_Name"
							from
								"EMD"."EMDDocumentTypeLocal" tloc
								inner join "EMD"."EMDSignatureRulesLocal" sr on sr."EMDDocumentTypeLocal_id" = tloc."EMDDocumentTypeLocal_id"
								left join "EMD"."EMDPersonRole" pr on pr."EMDPersonRole_id" = sr."EMDPersonRole_id"
							where
								tloc."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
						', array(
							'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id
						), $this->emddb);
					}

					if (!empty($resp_rules)) {
						$rules = "";
						foreach ($resp_rules as $one_rule) {
							$rules .= "<br>- С ролью {$one_rule['EMDPersonRole_Name']}:  от {$one_rule['mincount']} до {$one_rule['maxcount']} мед. сотрудников;";
						}
						throw new Exception('Для подписания документа необходимо дополнить сведения о медицинских сотрудниках в документе.<br>Необходимый состав:' . $rules);
					}
				}
			}

			if (empty($role)) {
				$role = $this->queryResult('
					select
						role."EMDPersonRole_id"
					from "EMD"."EMDMedStaffFactRole" role
					where role."MedStaffFact_id" = :MedStaffFact_id
						and role."EMDMedStaffFactRole_IsDefault" = 1
					limit 1
				', array('MedStaffFact_id' => $respone['MedStaffFact_id']), $this->emddb);
			}

			$docData = [
				'EMDRegistry_ObjectID' => $respone['docId'],
				'Document_Name' => $Document_Name,
				'Document_Num' => $Document_Num,
				'Document_Date' => $Document_Date,
				'EMDPersonRole_id' => (!empty($role[0]['EMDPersonRole_id']) ? $role[0]['EMDPersonRole_id'] : null),
				'ApprovalObjectList_id' => $resp_aol[0]['ApprovalObjectList_id'] ?? null,
				'Error_Msg' => ''
			];

			if (!empty($respone['IsSigned']) && $respone['IsSigned'] == 2) {
				// если документ подписан, то при повторной подписи подписываем уже существующую версию
				$resp_emdv = $this->queryResult('
					select
						emdv."EMDVersion_id"
					from
						"EMD"."EMDRegistry" emdr
						inner join "EMD"."EMDVersion" emdv on emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
					where
						emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
						and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
						and emdv."EMDVersion_VersionNum" is not null
					order by
						emdv."EMDVersion_id" desc
					limit 1
				', [
					'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
					'EMDRegistry_ObjectID' => $respone['docId']
				], $this->emddb);

				if (!empty($resp_emdv[0]['EMDVersion_id'])) {
					$docData['EMDVersion_id'] = $resp_emdv[0]['EMDVersion_id'];
				}
			}

			$response[] = $docData;
		}
		return $response;
	}

	/**
	* Получение следуюущего номера версии документа, который будет подписан.
	* используется для записи номера версии документа до его подписи
	* на входе:
	* EMDRegistry_ObjectName - имя документа, обычно равно имени услуги
	* EMDRegistry_ObjectID - идентификатор услуги
	* возвращает число
	*/
	public function getEMDSignDocNextVersion($data)
	{
		$resp = $this->queryResult('
		select
			COALESCE(MAX("EMDVersion_VersionNum") + 1, 1) as "Version",
			COALESCE((select MAX("EMDVersion_id") from "EMD"."EMDVersion" ) + 1, 1) as "EMDVersion_id"
				from
					"EMD"."EMDVersion" e2
					inner join "EMD"."EMDRegistry" emdr on e2."EMDRegistry_id" = emdr."EMDRegistry_id"
						where
							emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
							and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
		', [
			'EMDRegistry_ObjectID' => $data["EMDRegistry_ObjectID"],
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName']
		], $this->emddb);
		return $resp[0];
	}

	/**
	 * Получение версии документа для просмотра
	 */
	function getStampedPdf($data) {
		// получаем PDF-ку и данные о её подписи
		$resp = $this->queryResult('
			select
				emds."EMDSignatures_id",
				emdv."EMDVersion_id",
				s."pmUser_insID",
				emdv."EMDVersion_VersionNum",
				to_char(s."Signatures_insDT", \'DD.MM.YYYY HH24:MI:SS\') as "Signatures_insDT",
				emdv."EMDVersion_FilePath",
				emdc."EMDCertificate_id",
				emdc."EMDCertificate_Serial",
				emdc."EMDCertificate_CommonName",
				to_char(emdc."EMDCertificate_begDT", \'DD.MM.YYYY\') as "EMDCertificate_begDate",
				to_char(emdc."EMDCertificate_endDT", \'DD.MM.YYYY\') as "EMDCertificate_endDate"
			from
				"EMD"."EMDVersion" emdv
				inner join "EMD"."EMDSignatures" emds on emds."EMDVersion_id" = emdv."EMDVersion_id"
				inner join "EMD"."Signatures" s on s."Signatures_id" = emds."Signatures_id"
				inner join "EMD"."EMDCertificate" emdc on emdc."EMDCertificate_id" = s."EMDCertificate_id"
			where
				emdv."EMDVersion_id" = :EMDVersion_id
		', array(
			'EMDVersion_id' => $data['EMDVersion_id']
		), $this->emddb);

		if (!empty($resp[0]['EMDVersion_FilePath']) && file_exists($resp[0]['EMDVersion_FilePath'])) {
			$file_path = $resp[0]['EMDVersion_FilePath'];
			$stamped_file_path = $this->createStampOverPdf($resp[0]);
			if (!empty($stamped_file_path)) {
				$file_path = $stamped_file_path;
			}
			return array('Error_Msg' => '', 'EMDVersion_FilePath' => $file_path);
		}

		return array('Error_Msg' => 'Файл версии документа не найден');
	}

	/**
	 * Создание PDF со штампом
	 */
	function createStampOverPdf($data) {
		$file_path = $data['EMDVersion_FilePath'];
		$stamp_file_path = $file_path . '.stamp.png';
		$stamped_file_path = mb_substr($file_path, 0, -4).'_stamped.pdf';
		if (file_exists($stamped_file_path)) {
			return $stamped_file_path;
		}

		$image = imagecreatefrompng('img/emd_stamp_template.png');
		imagealphablending($image, false);
		imagesavealpha($image, true);

		if (!empty($data['EMDCertificate_id'])) {
			$font = BASEPATH . '../extjs6/classic/theme-triton-sandbox/resources/fonts/Roboto-Light.ttf';
			$color = imagecolorallocate($image, 56, 58, 146);
			imagettftext($image, 20, 0, 45, 120, $color, $font, str_replace('0x', '', $data['EMDCertificate_Serial']));
			imagettftext($image, 20, 0, 205, 159, $color, $font, $data['EMDCertificate_CommonName']);
			imagettftext($image, 20, 0, 265, 192, $color, $font, 'с ' . $data['EMDCertificate_begDate'] . ' по ' . $data['EMDCertificate_endDate']);
		}

		imagefilter($image, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * 0.2);
		imagepng($image, $stamp_file_path);
		imagedestroy($image);

		$java_path = $this->config->item('JAVA_PATH');
		$stamp_result = exec("\"{$java_path}\" -jar applets/pdfstamp.jar -p -1 -i {$stamp_file_path} -l 50,50 {$file_path} -d 600", $output, $return_var);

		@unlink($stamp_file_path);

		if (file_exists($stamped_file_path)) {
			return $stamped_file_path;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка правил подисания
	 */
	function loadEMDDocumentSignGrid($data) {
		$queryParams = [];
		$filter_emdds = " and 1=0";

		$EMDDocumentTypeLocalIds = [];
		$resp = $this->queryResult('
			select
				"EMDDocumentTypeLocal_id"
			from
				"EMD"."EMDDocumentSignRulesLocal"
			where
				coalesce("Region_id", :Region_id) = :Region_id
				and coalesce("EMDDocumentSignRulesLocal_begDate", :curDate) <= :curDate
				and coalesce("EMDDocumentSignRulesLocal_endDate", :curDate) >= :curDate
		', [
			'curDate' => date('Y-m-d'),
			'Region_id' => getRegionNumber()
		], $this->emddb);
		foreach($resp as $respone) {
			$EMDDocumentTypeLocalIds[] = $respone['EMDDocumentTypeLocal_id'];
		}
		$count = count($EMDDocumentTypeLocalIds);
		if ($count < 1) {
			return [];
		}

		if (!empty($data['LpuSection_id'])) {
			$data['LpuSection_id'] = preg_replace('/[^0-9,]/uis', '', $data['LpuSection_id']);
			$LpuSection_ids = explode(',', $data['LpuSection_id']);
			if (!empty($LpuSection_ids)) {
				$filter_emdds = ' and emdds."LpuSection_id" in (' . implode(',', $LpuSection_ids) . ')';
			}
			$count = count($LpuSection_ids) * count($EMDDocumentTypeLocalIds);
		} else if (!empty($data['Lpu_id'])) {
			$data['Lpu_id'] = preg_replace('/[^0-9,]/uis', '', $data['Lpu_id']);
			$Lpu_ids = explode(',', $data['Lpu_id']);
			if (!empty($Lpu_ids)) {
				$filter_emdds = ' and emdds."Lpu_id" in (' . implode(',', $Lpu_ids) . ')';
			}
			$count = count($Lpu_ids) * count($EMDDocumentTypeLocalIds);
		} else {
			return [];
		}

		$resp = $this->queryResult('
			select
				emddtl."EMDDocumentTypeLocal_id",
				emddtl."EMDDocumentTypeLocal_Name",
				case when dc."EMDDocumentSignRules_id" is not null then 1 else 0 end as "EMDDocumentSign_HeadSign",
				case when hd."EMDDocumentSignRules_id" is not null then 1 else 0 end as "EMDDocumentSign_MainSign"
			from
				"EMD"."EMDDocumentTypeLocal" emddtl
				left join "EMD"."EMDDocumentSign" emdds on emddtl."EMDDocumentTypeLocal_id" = emdds."EMDDocumentTypeLocal_id" ' . $filter_emdds . '
				left join lateral (
					SELECT
						emddsr."EMDDocumentSignRules_id"
					FROM
						"EMD"."EMDDocumentSignRules" emddsr
						inner join "EMD"."EMDPersonRole" emdpr on emdpr."EMDPersonRole_id" = emddsr."EMDPersonRole_id"
					WHERE
						emddsr."EMDDocumentSign_id" = emdds."EMDDocumentSign_id"
						and emdpr."EMDPersonRole_Code" = \'HEAD_DOCTOR\'
					LIMIT 1
				) hd on true
				left join lateral (
					SELECT
						emddsr."EMDDocumentSignRules_id"
					FROM
						"EMD"."EMDDocumentSignRules" emddsr
						inner join "EMD"."EMDPersonRole" emdpr on emdpr."EMDPersonRole_id" = emddsr."EMDPersonRole_id"
					WHERE
						emddsr."EMDDocumentSign_id" = emdds."EMDDocumentSign_id"
						and emdpr."EMDPersonRole_Code" = \'DEP_CHIEF\'
					LIMIT 1
				) dc on true
			where
				emddtl."EMDDocumentTypeLocal_id" in (' . implode(',', $EMDDocumentTypeLocalIds) . ')
		', $queryParams, $this->emddb);

		$countSaved = count($resp);
		$response = [];
		foreach($resp as $respone) {
			if ($count != $countSaved) {
				$respone['EMDDocumentSign_HeadSign'] = 0;
				$respone['EMDDocumentSign_MainSign'] = 0;
				$respone['EMDDocumentSign_HeadSignWarn'] = 1;
				$respone['EMDDocumentSign_MainSignWarn'] = 1;
			}
			if (isset($response[$respone['EMDDocumentTypeLocal_id']])) {
				// надо проверить что значения совпадают
				if ($response[$respone['EMDDocumentTypeLocal_id']]['EMDDocumentSign_HeadSign'] != $respone['EMDDocumentSign_HeadSign']) {
					$response[$respone['EMDDocumentTypeLocal_id']]['EMDDocumentSign_HeadSign'] = 0;
					$response[$respone['EMDDocumentTypeLocal_id']]['EMDDocumentSign_HeadSignWarn'] = 1;
				}
				if ($response[$respone['EMDDocumentTypeLocal_id']]['EMDDocumentSign_MainSign'] != $respone['EMDDocumentSign_MainSign']) {
					$response[$respone['EMDDocumentTypeLocal_id']]['EMDDocumentSign_MainSign'] = 0;
					$response[$respone['EMDDocumentTypeLocal_id']]['EMDDocumentSign_MainSignWarn'] = 1;
				}
			} else {
				$response[$respone['EMDDocumentTypeLocal_id']] = $respone;
			}
		}

		return $response;
	}

	/**
	 * Получение правил для документа
	 */
	function getEMDDocumentSignRules($data) {
		$response = [
			'EMDDocumentSign_CountSign' => 1, // по умолчанию нужна подпись врача
			'roles' => []
		];

		$resp = $this->queryResult('
			select
				emdds."EMDDocumentSign_id",
				coalesce(emdds."EMDDocumentSign_CountSign", 0) as "EMDDocumentSign_CountSign"
			from
				"EMD"."EMDDocumentSign" emdds
			where
				emdds."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
				and (emdds."Lpu_id" = :Lpu_id or emdds."LpuSection_id" = :LpuSection_id)
			order by
				case when emdds."LpuSection_id" is not null then 0 else 1 end
		', $data, $this->emddb);

		if (!empty($resp[0]['EMDDocumentSign_CountSign'])) { // если нужны подписи
			$response['EMDDocumentSign_CountSign'] = $resp[0]['EMDDocumentSign_CountSign'];
			// получаем список необходимых ролей
			$resp_roles = $this->queryResult('
				SELECT
					emddsr."EMDDocumentSignRules_id",
					emddsr."EMDPersonRole_id",
				    emdpr."EMDPersonRole_Code"
				FROM
					"EMD"."EMDDocumentSignRules" emddsr
					inner join "EMD"."EMDPersonRole" emdpr on emdpr."EMDPersonRole_id" = emddsr."EMDPersonRole_id"
				WHERE
					emddsr."EMDDocumentSign_id" = :EMDDocumentSign_id
			', [
				'EMDDocumentSign_id' => $resp[0]['EMDDocumentSign_id']
			], $this->emddb);

			foreach($resp_roles as $one_role) {
				$response['roles'][$one_role['EMDPersonRole_Code']] = $one_role['EMDPersonRole_id'];
			}
		}

		return $response;
	}

	/**
	 * Сохранение списка правил подисания
	 */
	function saveEMDDocumentSignRules($data) {
		$LpuSection_ids = [];
		if (!empty($data['LpuSection_id'])) {
			$data['LpuSection_id'] = preg_replace('/[^0-9,]/uis', '', $data['LpuSection_id']);
			$LpuSection_ids = explode(',', $data['LpuSection_id']);
		}

		$Lpu_ids = [];
		if (!empty($data['Lpu_id'])) {
			$data['Lpu_id'] = preg_replace('/[^0-9,]/uis', '', $data['Lpu_id']);
			$Lpu_ids = explode(',', $data['Lpu_id']);
		}

		foreach($LpuSection_ids as $LpuSection_id) {
			foreach($data['records'] as $record) {
				if (!empty($record['EMDDocumentTypeLocal_id'])) {
					$this->saveEMDDocumentSign([
						'Lpu_id' => null,
						'LpuSection_id' => $LpuSection_id,
						'EMDDocumentTypeLocal_id' => $record['EMDDocumentTypeLocal_id'],
						'EMDDocumentSign_HeadSign' => $record['EMDDocumentSign_HeadSign'] ?? false,
						'EMDDocumentSign_MainSign' => $record['EMDDocumentSign_MainSign'] ?? false,
						'pmUser_id' => $data['pmUser_id']
					]);
				}
			}
		}

		foreach($Lpu_ids as $Lpu_id) {
			foreach($data['records'] as $record) {
				if (!empty($record['EMDDocumentTypeLocal_id'])) {
					$this->saveEMDDocumentSign([
						'Lpu_id' => $Lpu_id,
						'LpuSection_id' => null,
						'EMDDocumentTypeLocal_id' => $record['EMDDocumentTypeLocal_id'],
						'EMDDocumentSign_HeadSign' => $record['EMDDocumentSign_HeadSign'] ?? false,
						'EMDDocumentSign_MainSign' => $record['EMDDocumentSign_MainSign'] ?? false,
						'pmUser_id' => $data['pmUser_id']
					]);
				}
			}
		}

		return ['Error_Msg' => ''];
	}

	/**
	 * Сохранение правила для листа согласования
	 */
	function saveEMDDocumentSign($data) {
		// 1. сохраняем или апдейтим EMDDocumentSign
		$filter = "";
		if (!empty($data['LpuSection_id'])) {
			$filter .= ' and "LpuSection_id" = :LpuSection_id';
		} else if (!empty($data['Lpu_id'])) {
			$filter .= ' and "Lpu_id" = :Lpu_id';
		} else {
			return false;
		}

		$resp = $this->queryResult('
			select
				"EMDDocumentSign_id"
			from
				"EMD"."EMDDocumentSign"
			where
				"EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
				' . $filter . '
		', $data, $this->emddb);

		$data['EMDDocumentSign_CountSign'] = 1;
		if (!empty($data['EMDDocumentSign_HeadSign'])) {
			$data['EMDDocumentSign_CountSign']++;
		}
		if (!empty($data['EMDDocumentSign_MainSign'])) {
			$data['EMDDocumentSign_CountSign']++;
		}

		if (!empty($resp[0]['EMDDocumentSign_id'])) {
			$data['EMDDocumentSign_id'] = $resp[0]['EMDDocumentSign_id'];
			$this->emddb->query('
				UPDATE
					"EMD"."EMDDocumentSign"
				SET
					"EMDDocumentSign_CountSign" = :EMDDocumentSign_CountSign,
					"pmUser_updID" = :pmUser_id,
					"EMDDocumentSign_updDT" = NOW()
				WHERE
					"EMDDocumentSign_id" = :EMDDocumentSign_id
			', $data);
		} else {
			$resp = $this->queryResult('
				INSERT INTO "EMD"."EMDDocumentSign" (
					"Lpu_id",
					"LpuSection_id",
					"EMDDocumentTypeLocal_id",
					"EMDDocumentSign_CountSign",
					"pmUser_insID",
					"pmUser_updID",
					"EMDDocumentSign_insDT",
					"EMDDocumentSign_updDT"
				)
				VALUES (
					:Lpu_id,
					:LpuSection_id,
					:EMDDocumentTypeLocal_id,
					:EMDDocumentSign_CountSign,
					:pmUser_id,
					:pmUser_id,
					NOW(),
					NOW()
				)
				RETURNING "EMDDocumentSign_id"
			', $data, $this->emddb);
			if (!empty($resp[0]['EMDDocumentSign_id'])) {
				$data['EMDDocumentSign_id'] = $resp[0]['EMDDocumentSign_id'];
			}
		}

		if (!empty($data['EMDDocumentSign_id'])) {
			$resp_sr = $this->queryResult('
				select
					emddsr."EMDDocumentSignRules_id",
					emdpr."EMDPersonRole_Code"
				from
					"EMD"."EMDDocumentSignRules" emddsr
					inner join "EMD"."EMDPersonRole" emdpr on emddsr."EMDPersonRole_id" = emdpr."EMDPersonRole_id"
				where
					emddsr."EMDDocumentSign_id" = :EMDDocumentSign_id
			', $data, $this->emddb);

			$codeExists = [];
			// 2. удаляем лишние записи из EMDDocumentSignRules
			foreach($resp_sr as $one_sr) {
				if (
					($one_sr['EMDPersonRole_Code'] == 'DEP_CHIEF' && empty($data['EMDDocumentSign_HeadSign']))
					|| ($one_sr['EMDPersonRole_Code'] == 'HEAD_DOCTOR' && empty($data['EMDDocumentSign_MainSign']))
				) {
					$this->emddb->query('delete from "EMD"."EMDDocumentSignRules" where "EMDDocumentSignRules_id" = :EMDDocumentSignRules_id', [
						'EMDDocumentSignRules_id' => $one_sr['EMDDocumentSignRules_id']
					]);
				} else {
					$codeExists[] = $one_sr['EMDPersonRole_Code'];
				}
			}

			// 3. добавляем новые записи в EMDDocumentSignRules
			if (!empty($data['EMDDocumentSign_HeadSign']) && !in_array('DEP_CHIEF', $codeExists)) {
				$this->queryResult('
					INSERT INTO "EMD"."EMDDocumentSignRules" (
						"EMDDocumentSign_id",
						"EMDPersonRole_id",
						"pmUser_insID",
						"pmUser_updID",
						"EMDDocumentSignRules_insDT",
						"EMDDocumentSignRules_updDT"
					)
					VALUES (
						:EMDDocumentSign_id,
						(select "EMDPersonRole_id" from "EMD"."EMDPersonRole" where "EMDPersonRole_Code" = \'DEP_CHIEF\' limit 1),
						:pmUser_id,
						:pmUser_id,
						NOW(),
						NOW()
					)
					RETURNING "EMDDocumentSign_id"
				', $data, $this->emddb);
			}
			if (!empty($data['EMDDocumentSign_MainSign']) && !in_array('HEAD_DOCTOR', $codeExists)) {
				$this->queryResult('
					INSERT INTO "EMD"."EMDDocumentSignRules" (
						"EMDDocumentSign_id",
						"EMDPersonRole_id",
						"pmUser_insID",
						"pmUser_updID",
						"EMDDocumentSignRules_insDT",
						"EMDDocumentSignRules_updDT"
					)
					VALUES (
						:EMDDocumentSign_id,
						(select "EMDPersonRole_id" from "EMD"."EMDPersonRole" where "EMDPersonRole_Code" = \'HEAD_DOCTOR\' limit 1),
						:pmUser_id,
						:pmUser_id,
						NOW(),
						NOW()
					)
					RETURNING "EMDDocumentSign_id"
				', $data, $this->emddb);
			}
		}

		return true;
	}

	/**
	 * Загрузка списка версий документа
	 */
	function loadEMDVersionList($data) {
		$resp = $this->queryResult('
			select
				emds."EMDSignatures_id",
				emdv."EMDVersion_id",
				s."pmUser_insID",
				emds."MedPersonal_SurName",
				emds."MedPersonal_FirName",
				emds."MedPersonal_SecName",
				emdv."EMDVersion_VersionNum",
				to_char(emdv."EMDVersion_insDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDVersion_insDT",
				emdv."EMDVersion_FilePath",
				emdv."EMDFileFormat_id",
				emdff."EMDFileFormat_Name"
			from
				"EMD"."EMDRegistry" emdr
				inner join "EMD"."EMDVersion" emdv on emdr."EMDRegistry_id" = emdv."EMDRegistry_id"
				inner join "EMD"."EMDSignatures" emds on emds."EMDVersion_id" = emdv."EMDVersion_id"
				inner join "EMD"."Signatures" s on s."Signatures_id" = emds."Signatures_id"
				left join "EMD"."EMDFileFormat" emdff on emdff."EMDFileFormat_id" = emdv."EMDFileFormat_id"
			where
				emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
				and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
		', array(
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
		), $this->emddb);

		foreach($resp as $key => $value) {
			if (empty($value['MedPersonal_SurName'])) {
				$result_user = $this->db->query("
					select
						pmUser_Name as \"pmUser_Name\"
					from
						v_pmUser
					where
						pmUser_id = :pmUser_id
				", array(
					'pmUser_id' => $value['pmUser_insID']
				));

				if (is_object($result_user)) {
					$resp_user = $result_user->result('array');
					if (!empty($resp_user[0]['pmUser_Name'])) {
						$resp[$key]['pmUser_Name'] = $resp_user[0]['pmUser_Name'];
					}
				}
			} else {
				$resp[$key]['pmUser_Name'] = $value['MedPersonal_SurName'] . ' ' . $value['MedPersonal_FirName'];
			}
		}

		return $resp;
	}

	/**
	 * Загрузка формы редактирования сертификата
	 */
	function loadEMDCertificateEditWindow($data) {
		$query = '
			SELECT
				"EMDCertificate_id",
				"EMDCertificate_Version",
				"EMDCertificate_Serial",
				to_char("EMDCertificate_begDT", \'DD.MM.YYYY\') as "EMDCertificate_begDate",
				to_char("EMDCertificate_endDT", \'DD.MM.YYYY\') as "EMDCertificate_endDate",
				to_char("EMDCertificate_begDT", \'HH24:MI\') as "EMDCertificate_begTime",
				to_char("EMDCertificate_endDT", \'HH24:MI\') as "EMDCertificate_endTime",
				"EMDCertificate_Publisher",
				"EMDCertificate_CommonName",
				"EMDCertificate_SurName",
				"EMDCertificate_FirName",
				"EMDCertificate_Post",
				"EMDCertificate_Org",
				"EMDCertificate_Unit",
				"EMDCertificate_SignAlgorithm",
				"EMDCertificate_OpenKey",
				"EMDCertificate_SHA256",
				"EMDCertificate_SHA1",
				"EMDCertificate_PublisherUID",
				"EMDCertificate_SubjectUID",
				"Org_id",
				"pmUser_id",
				CASE WHEN "EMDCertificate_IsNotUse" = 2 THEN 1 ELSE 0 END as "EMDCertificate_IsNotUse",
				"EMDCertificate_Name",
				"EMDCertificate_OGRN"
			FROM
				"EMD"."EMDCertificate"
			WHERE
				"EMDCertificate_id" = :EMDCertificate_id
		';

		return $this->queryResult($query, array(
			'EMDCertificate_id' => $data['EMDCertificate_id']
		), $this->emddb);
	}

	/**
	 * Получение информации о сертификате для штампа ЭП
	 */
	function printStamp($data) {
		$resp = $this->queryResult('
			select
				"EMDCertificate_id",
				"EMDCertificate_Serial",
				"EMDCertificate_CommonName",
				"EMDCertificate_SurName",
				"EMDCertificate_FirName",
				to_char("EMDCertificate_begDT", \'DD.MM.YYYY\') as "EMDCertificate_begDate",
				to_char("EMDCertificate_endDT", \'DD.MM.YYYY\') as "EMDCertificate_endDate"
			from
				"EMD"."EMDCertificate"
			where
				"EMDCertificate_id" = :EMDCertificate_id
		', array(
			'EMDCertificate_id' => $data['EMDCertificate_id']
		), $this->emddb);

		$image = imagecreatefrompng('img/emd_stamp_template.png');

		if (!empty($resp[0]['EMDCertificate_id'])) {
			$font = BASEPATH.'../extjs6/classic/theme-triton-sandbox/resources/fonts/Roboto-Light.ttf';
			$color = imagecolorallocate($image, 56, 58, 146);
			imagettftext($image, 20, 0, 45, 110, $color, $font, str_replace('0x', '', $resp[0]['EMDCertificate_Serial']));
			imagettftext($image, 20, 0, 205, 147, $color, $font, $resp[0]['EMDCertificate_CommonName']);
			imagettftext($image, 20, 0, 265, 176, $color, $font, 'с ' . $resp[0]['EMDCertificate_begDate'] . ' по ' . $resp[0]['EMDCertificate_endDate']);
			imagettftext($image, 20, 0, 145, 208, $color, $font, $resp[0]['EMDCertificate_SurName'] . ' ' . $resp[0]['EMDCertificate_FirName']);
		}

		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	}

	/**
	 * Проверки имени кому выдан сертификат
	 */
	function isPMUserNameEqualToCertCommonName($data) {

		$result = false;
		if (!empty($data['cn'])
			&& !empty($data['user']['pmUser_SurName'])
			&& !empty($data['user']['pmUser_FirName'])
			&& !empty($data['user']['pmUser_SecName'])
		) {

			$cnName = str_replace('ё', 'е', mb_strtolower($data['cn']));

			$pmUserName = str_replace(
				'ё', 'е',
				mb_strtolower(
					$data['user']['pmUser_SurName'] . ' '
					. $data['user']['pmUser_FirName'] . ' '
					. $data['user']['pmUser_SecName']
				)
			);

			$result = ($cnName == $pmUserName);
		}
		return $result;
	}

	/**
	 * Сохранение сертификата
	 */
	function saveEMDCertificate($data) {

		if (empty($data['bypassStrictCommonName'])) {

			$resp_puc = $this->queryResult("
				select
					pmUser_id as \"pmUser_id\",
					pmUser_SurName as \"pmUser_SurName\",
					pmUser_FirName as \"pmUser_FirName\",
					pmUser_SecName as \"pmUser_SecName\"
				from
					v_pmUserCache
				where
					pmUser_id = :pmUser_id
			", array(
				'pmUser_id' => $data['pmUser_id']
			));

			if (!$this->isPMUserNameEqualToCertCommonName(array(
				'user' => $resp_puc[0],
				'cn' => $data['EMDCertificate_CommonName'],
			))) {
				return array('isNotEqual' => true);
			}
		}

		$queryParams = array(
			'EMDCertificate_id' => $data['EMDCertificate_id'],
			'EMDCertificate_Version' => $data['EMDCertificate_Version'],
			'EMDCertificate_Serial' => $data['EMDCertificate_Serial'],
			'EMDCertificate_begDT' => $data['EMDCertificate_begDate'],
			'EMDCertificate_endDT' => $data['EMDCertificate_endDate'],
			'EMDCertificate_Publisher' => $data['EMDCertificate_Publisher'],
			'EMDCertificate_CommonName' => $data['EMDCertificate_CommonName'],
			'EMDCertificate_SurName' => $data['EMDCertificate_SurName'],
			'EMDCertificate_FirName' => $data['EMDCertificate_FirName'],
			'EMDCertificate_Post' => $data['EMDCertificate_Post'],
			'EMDCertificate_Org' => $data['EMDCertificate_Org'],
			'EMDCertificate_Unit' => $data['EMDCertificate_Unit'],
			'EMDCertificate_SignAlgorithm' => $data['EMDCertificate_SignAlgorithm'],
			'EMDCertificate_OpenKey' => $data['EMDCertificate_OpenKey'],
			'EMDCertificate_SHA256' => $data['EMDCertificate_SHA256'],
			'EMDCertificate_SHA1' => $data['EMDCertificate_SHA1'],
			'EMDCertificate_PublisherUID' => $data['EMDCertificate_PublisherUID'],
			'EMDCertificate_SubjectUID' => $data['EMDCertificate_SubjectUID'],
			'Org_id' => $data['Org_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EMDCertificate_IsNotUse' => $data['EMDCertificate_IsNotUse'] ? 2 : 1,
			'pmUser_insID' => $data['session']['pmuser_id'],
			'pmUser_updID' => $data['session']['pmuser_id'],
			'EMDCertificate_Name' => $data['EMDCertificate_Name'],
			'EMDCertificate_OGRN' => $data['EMDCertificate_OGRN']
		);

		if (!empty($queryParams['EMDCertificate_begDT']) && !empty($data['EMDCertificate_begTime'])) {
			$queryParams['EMDCertificate_begDT'] .= ' ' . $data['EMDCertificate_begTime'];
		}

		if (!empty($queryParams['EMDCertificate_endDT']) && !empty($data['EMDCertificate_endTime'])) {
			$queryParams['EMDCertificate_endDT'] .= ' ' . $data['EMDCertificate_endTime'];
		}

		if (!empty($queryParams['EMDCertificate_id'])) {
			$this->emddb->query('
				UPDATE
					"EMD"."EMDCertificate"
				SET
					"Org_id" = :Org_id,
					"EMDCertificate_IsNotUse" = :EMDCertificate_IsNotUse,
					"EMDCertificate_updDT" = NOW(),
					"pmUser_updID" = :pmUser_updID,
					"EMDCertificate_Name" = :EMDCertificate_Name
				WHERE
					"EMDCertificate_id" = :EMDCertificate_id
			', $queryParams);
		} else {
			// проверка на дубли
			$resp = $this->queryResult('
				select
					"EMDCertificate_id"
				from
					"EMD"."EMDCertificate"
				where
					"EMDCertificate_SHA1" = :EMDCertificate_SHA1
					and "pmUser_id" = :pmUser_id
					and coalesce("EMDCertificate_deleted", 1) != 2
			', $queryParams, $this->emddb);

			if (!empty($resp[0]['EMDCertificate_id'])) {
				return array('Error_Msg' => 'Сертификат не может быть загружен, т.к. он уже есть в списке сертификатов учетной записи. Выберите другой сертификат');
			}

			$this->emddb->query('
				INSERT INTO "EMD"."EMDCertificate" (
					"EMDCertificate_Version",
					"EMDCertificate_Serial",
					"EMDCertificate_begDT",
					"EMDCertificate_endDT",
					"EMDCertificate_Publisher",
					"EMDCertificate_CommonName",
					"EMDCertificate_SurName",
					"EMDCertificate_FirName",
					"EMDCertificate_Post",
					"EMDCertificate_Org",
					"EMDCertificate_Unit",
					"EMDCertificate_SignAlgorithm",
					"EMDCertificate_OpenKey",
					"EMDCertificate_SHA256",
					"EMDCertificate_SHA1",
					"EMDCertificate_PublisherUID",
					"EMDCertificate_SubjectUID",
					"Org_id",
					"pmUser_id",
					"EMDCertificate_IsNotUse",
					"EMDCertificate_insDT",
					"EMDCertificate_updDT",
					"pmUser_insID",
					"pmUser_updID",
					"EMDCertificate_Name",
					"EMDCertificate_OGRN"
				)
				VALUES (
					:EMDCertificate_Version,
					:EMDCertificate_Serial,
					:EMDCertificate_begDT,
					:EMDCertificate_endDT,
					:EMDCertificate_Publisher,
					:EMDCertificate_CommonName,
					:EMDCertificate_SurName,
					:EMDCertificate_FirName,
					:EMDCertificate_Post,
					:EMDCertificate_Org,
					:EMDCertificate_Unit,
					:EMDCertificate_SignAlgorithm,
					:EMDCertificate_OpenKey,
					:EMDCertificate_SHA256,
					:EMDCertificate_SHA1,
					:EMDCertificate_PublisherUID,
					:EMDCertificate_SubjectUID,
					:Org_id,
					:pmUser_id,
					:EMDCertificate_IsNotUse,
					NOW(), -- EMDCertificate_insDT,
					NOW(), -- EMDCertificate_updDT,
					:pmUser_insID,
					:pmUser_updID,
					:EMDCertificate_Name,
					:EMDCertificate_OGRN
				)
			', $queryParams);
		}
		return array('Error_Msg' => '');
	}

	/**
	 * Удаление сертификата
	 */
	function deleteEMDCertificate($data) {
		$this->emddb->query('
			UPDATE
				"EMD"."EMDCertificate"
			SET
				"EMDCertificate_deleted" = 2,
				"EMDCertificate_delDT" = NOW(),
				"pmUser_delID" = :pmUser_delID
			WHERE
				"EMDCertificate_id" = :EMDCertificate_id
		', array(
			'EMDCertificate_id' => $data['EMDCertificate_id'],
			'pmUser_delID' => $data['pmUser_id']
		));
		return array('Error_Msg' => '');
	}

	/**
	 * Проверка наличия у пользователя хотя бы одного сертификата
	 */
	function checkUserHasEMDCertificate($data) {
		if (empty($data['session']['pmuser_id'])) {
			return false;
		}

		$resp = $this->queryResult('
			select
				"EMDCertificate_id"
			from
				"EMD"."EMDCertificate"
			where
				"pmUser_id" = :pmUser_id
				and coalesce("EMDCertificate_deleted", 1) = 1
			limit 1
		', array(
			'pmUser_id' => $data['session']['pmuser_id']
		), $this->emddb);

		if (!empty($resp[0]['EMDCertificate_id'])) {
			return true;
		}

		return false;
	}

	/**
	 * Проверка, что для документа необходима подпись
	 */
	function checkNeedSignature($data) {
		$response = array(
			'Error_Msg' => ''
		);

		if (empty($this->documents[$data['EMDRegistry_ObjectName']])) {
			return $response;
		}

		$doc = $this->documents[$data['EMDRegistry_ObjectName']];

		if ($data['EMDRegistry_ObjectName'] == 'ReportRun') {
			$db = $this->load->database('reports', true); // БД для отчётов
		} else {
			$db = $this->db;
		}

		// проверяем необходимость подписания
		$resp_check = $this->queryResult("
			select
				{$doc['signObject']}_id as \"id\"
			from
				{$doc['signObject']}
			where
				{$doc['signObject']}_id = :EMDRegistry_ObjectID
				and ({$doc['signDateField']} is null or {$doc['signDateField']} < {$doc['signObject']}_updDT)
		", array(
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
		), $db);

		if (!empty($resp_check[0]['id'])) {
			$response['needSignature'] = true;
		}

		return $response;
	}

	/**
	 * Список всех ролей для комбо
	 */
	function getPersonRoleList($data) {

		$query = '
			SELECT
				"EMDPersonRole_id",
				"EMDPersonRole_Name"
			FROM "EMD"."EMDPersonRole"
		';

		$result = $this->queryResult($query, $data, $this->emddb);

		return $result;
	}

	/**
	 * Сохранение роли для рабочего места врача
	 */
	function saveEMDMedStaffFactRoles($data) {

		$json_data = json_decode($data['RolesList']);

		$queryParams = array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Region_id' => getRegionNumber(),
			'pmUser_insID' => $data['pmUser_id'],
			'pmUser_updID' => $data['pmUser_id'],
		);

		$response = array();

		if (!empty($json_data)) {
			foreach ($json_data as $role) {

				$role = (array)$role;
				$queryParams['EMDMedStaffFactRole_id'] = (!empty($role['EMDMedStaffFactRole_id']) ? $role['EMDMedStaffFactRole_id'] : null);

				if (!empty($role['isDeleted']) && !empty($role['EMDMedStaffFactRole_id'])) {

					$resp = $this->queryResult('
						DELETE FROM "EMD"."EMDMedStaffFactRole"
						WHERE "EMDMedStaffFactRole_id" = :EMDMedStaffFactRole_id;
					', $queryParams, $this->emddb);

				} else {

					if (!empty($role['period'])) {

						$dates = explode('—',$role['period']);
						$queryParams['EMDMedStaffFactRole_begDate'] = DateTime::createFromFormat('d.m.Y', trim($dates[0]))->format('Y-m-d');

						if (!empty($dates[1])) $queryParams['EMDMedStaffFactRole_endDate'] = DateTime::createFromFormat('d.m.Y', trim($dates[1]))->format('Y-m-d');
						else $queryParams['EMDMedStaffFactRole_endDate'] = null;
					}

					$queryParams['EMDMedStaffFactRole_IsDefault'] = ($role['checked']) ? 1 : 0;
					$queryParams['EMDPersonRole_id'] = $role['EMDPersonRole_id'];

					if (empty($role['EMDMedStaffFactRole_id'])) {

						$resp = $this->queryResult('
						INSERT INTO "EMD"."EMDMedStaffFactRole" (
							"EMDPersonRole_id",
							"MedStaffFact_id",
							"EMDMedStaffFactRole_IsDefault",
							"Region_id",
							"EMDMedStaffFactRole_begDate",
							"EMDMedStaffFactRole_endDate",
							"pmUser_insID",
							"pmUser_updID",
							"EMDMedStaffFactRole_insDT",
							"EMDMedStaffFactRole_updDT"
						)
						VALUES (
							:EMDPersonRole_id,
							:MedStaffFact_id,
							:EMDMedStaffFactRole_IsDefault,
							:Region_id,
							:EMDMedStaffFactRole_begDate,
							:EMDMedStaffFactRole_endDate,
							:pmUser_insID,
							:pmUser_updID,
							NOW(), -- EMDMedStaffFactRole_insDT,
							NOW() -- EMDMedStaffFactRole_updDT
						)
						RETURNING "EMDMedStaffFactRole_id"
					', $queryParams, $this->emddb);

					} else {

						$resp = $this->queryResult('
						UPDATE "EMD"."EMDMedStaffFactRole"
						SET
							"EMDPersonRole_id" = :EMDPersonRole_id,
							"MedStaffFact_id" = :MedStaffFact_id,
							"EMDMedStaffFactRole_IsDefault" = :EMDMedStaffFactRole_IsDefault,
							"Region_id" = :Region_id,
							"EMDMedStaffFactRole_begDate" = :EMDMedStaffFactRole_begDate,
							"EMDMedStaffFactRole_endDate" = :EMDMedStaffFactRole_endDate,
							"pmUser_updID" = :pmUser_updID,
							"EMDMedStaffFactRole_updDT" = NOW()
						WHERE
							"EMDMedStaffFactRole_id" = :EMDMedStaffFactRole_id
					',  $queryParams, $this->emddb);
					}
				}

				if (!empty($resp['EMDMedStaffFactRole_id'])) {
					$response[] = $resp['EMDMedStaffFactRole_id'];
				}
			}
		}

		return array('Error_Msg' => '', 'SavedRoles' => $response);
	}

	/**
	 * Загрузка ролей для рабочего места врача
	 */
	function loadEMDMedStaffFactRoles($data) {

		$query = '
			SELECT
				"EMDMedStaffFactRole_id",
				"EMDPersonRole_id",
				"MedStaffFact_id",
				"EMDMedStaffFactRole_IsDefault",
				"EMDMedStaffFactRole_begDate",
				"EMDMedStaffFactRole_endDate"
			FROM "EMD"."EMDMedStaffFactRole"
			WHERE "MedStaffFact_id" = :MedStaffFact_id
		';

		$result = $this->queryResult($query, $data, $this->emddb);
		return $result;
	}

	/**
	 * Определяем есть ли в базе РЭМД определенные события\документы РМИС
	 */
	function getEMDDocumentListByEvn($data) {

		if (!empty($data['Evn_id'])) $object_id = $data['Evn_id'];
		else if (!empty($data['EvnXml_id'])) $object_id = $data['EvnXml_id'];
		else return array(array('Error_Msg' => 'Не указан документ для проверки по базе РЭМД'));

		// добавляем сам документ
		$raw_list = array(
			array(
				'ObjectID' => $object_id,
				'EvnClass_SysNick' => $data['EvnClass_SysNick']
			)
		);

		// если документ может иметь вложенные документы и события
		if (in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom'))) {

			// если это не случай
			if (!in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom'))
			) {
				$evn_query_filter = " evn.Evn_pid = :Evn_id ";
				$xml_query_filter = " xml.Evn_id = :Evn_id ";
			} else {
				// если это случай
				$evn_query_filter = " evn.Evn_rid = :Evn_id ";
				$xml_query_filter = " 
					xml.Evn_id in (
						select e.Evn_id
						from v_Evn e
						where e.Evn_rid = :Evn_id
						and e.EvnClass_id in (11, 13)
					)
				";
			}

			// для документов с типом Событие (направление, рецепты)
			$EvnList = $this->queryResult("
				select
					evn.Evn_id as \"ObjectID\",
					evn.Evn_id as \"Evn_id\",
					evn.EvnClass_Name as \"EvnClass_Name\",
					evn.EvnClass_SysNick as \"EvnClass_SysNick\"
				from v_Evn evn
				inner join v_Evn parent on parent.Evn_id = evn.Evn_pid
				left join v_EvnDirection_all ed on ed.EvnDirection_id = evn.Evn_id
				left join v_EvnRecept er on er.EvnRecept_id = evn.Evn_id
				where
					{$evn_query_filter}
					and evn.EvnClass_id in (4, 27)
					and coalesce(ED.EvnStatus_id, 16) not in (12,13)
			", $data);

			if (!empty($EvnList)) {
				$raw_list = array_merge($raw_list, $EvnList);
			}

			// для документов с типом XML событие
			$XmlList = $this->queryResult("
				select
					xml.EvnXml_id as \"ObjectID\",
					xml.EvnXml_id as \"EvnXml_id\",
					xml.EvnXml_Name as \"EvnXml_Name\",
					xml.XmlType_id as \"XmlType_id\"
				from v_EvnXml xml
				inner join v_Evn parent on parent.Evn_id = xml.Evn_id
				where
					{$xml_query_filter}
					and xml.XmlType_id in (2, 3, 4, 1)
			", $data);

			if (!empty($XmlList)) {
				$raw_list = array_merge($raw_list, $XmlList);
			}
		}

		$result = array();

		if (!empty($raw_list)) {

			$check_list = array();
			foreach ($raw_list as $item) $check_list[$item['ObjectID']] = $item;

			$object_list = implode(',', array_keys($check_list));
			$emd_docs = $this->getEMDDocumentsList(array('object_list' => $object_list));

			//echo '<pre>',print_r($emd_docs),'</pre>'; die();

			if (!empty($emd_docs)) {
				foreach ($emd_docs as $emd_item) {
					if (isset($check_list[$emd_item['EMDRegistry_ObjectID']])) {
						$result[] = $check_list[$emd_item['EMDRegistry_ObjectID']];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * получаем документы по списку ид
	 */
	function getEMDDocumentsList($data) {

		$object_list = $data['object_list'];

		// выгребаем ИД с актуальными датами
		$query = '
				SELECT
					reg."EMDRegistry_id",
					reg."EMDRegistry_ObjectID",
					reg."EMDRegistry_ObjectName",
					ver."EMDVersion_id",
					to_char(ver."EMDVersion_actualDT", \'DD.MM.YYYY HH24:MI:SS\') as "EMDVersion_actualDT"
				FROM "EMD"."EMDRegistry" reg
				LEFT JOIN LATERAL (
					SELECT
						ver."EMDVersion_id",
						ver."EMDVersion_actualDT"
					FROM "EMD"."EMDVersion" as ver 
					WHERE 
						ver."EMDRegistry_id" = reg."EMDRegistry_id"
					ORDER BY ver."EMDVersion_id"
					LIMIT 1
				) ver on true
				WHERE
					reg."EMDRegistry_ObjectID" in ('.$object_list.')
			';

		return $this->queryResult($query, array(), $this->emddb);
	}

	/**
	 * Контроли на наличие ЭП и актуальности ЭМД (в составе документов ТАП)
	 */
	function checkSignedEvnContent($data) {

		// сначала проверим, есть ли у пользователя небходимые для подписи сертификаты
		$certs = $this->loadEMDCertificateList($data);

		// если сертификатов нет то разворачиваем
		if (empty($certs)) return array();

		// для документов с типом Событие (направление, рецепты)
		$EvnList = $this->queryResult("
			select
				evn.Evn_id as \"ObjectID\",
				evn.EvnClass_SysNick as \"EvnClass_SysNick\",
				evn.EvnClass_id as \"docType\",
				evn.EvnClass_Name as \"docName\",
				evn.Evn_IsSigned as \"IsSigned\",
				evn.Evn_pid as \"Evn_pid\",
				(to_char(evn.Evn_updDT, 'dd.mm.yyyy HH24:MI:SS')) as \"actualDate\",
				parent.EvnClass_Name as \"parent_EvnClass_Name\",
				(to_char(parent.Evn_setDate, 'dd.mm.yyyy')) as \"parent_Evn_setDate\",
				coalesce(ed.EvnDirection_Num, er.EvnRecept_Num) as \"docNum\"
			from v_Evn evn
			inner join v_Evn parent on parent.Evn_id = evn.Evn_pid
			left join v_EvnDirection_all ed on ed.EvnDirection_id = evn.Evn_id
			left join v_EvnRecept er on er.EvnRecept_id = evn.Evn_id
			where
				evn.Evn_rid = :object_id
				and evn.EvnClass_id in (4, 27)
				and coalesce(ED.EvnStatus_id, 16) not in (12,13)
		", $data);

		$work_data = array(
			'check_list' => array(),
			'warning_list' => array(),
		);

		if (!empty($EvnList)) {
			$work_data = $this->makeWarningList($work_data, $EvnList);
		}

		// для документов с типом XML событие
		$XmlList = $this->queryResult("
			select
				xml.EvnXml_id as \"EvnXml_id\",
				xml.EvnXml_id as \"ObjectID\",
				xml.EvnXml_IsSigned as \"IsSigned\",
				xml.EvnXml_Name as \"docName\",
				xml.Evn_id as \"Evn_pid\",
				xml.XmlType_id as \"docType\",
				(to_char(xml.EvnXml_updDT, 'dd.mm.yyyy HH24:MI:SS')) as \"actualDate\",
				parent.EvnClass_Name as \"parent_EvnClass_Name\",
				(to_char(parent.Evn_setDate, 'dd.mm.yyyy')) as \"parent_Evn_setDate\"
			from v_EvnXml xml
			inner join v_Evn parent on parent.Evn_id = xml.Evn_id
			where
				xml.Evn_id in (
					select e.Evn_id
					from v_Evn e
					where e.Evn_rid = :object_id
					and e.EvnClass_id in (11, 13)
				)
				and xml.XmlType_id in (2, 3, 4, 1)
		", $data);

		if (!empty($XmlList)) {
			$work_data = $this->makeWarningList($work_data, $XmlList);
		}

		// проверяем актуальность по базе РЭМД
		if (!empty($work_data['check_list'])) {

			$check_list = $work_data['check_list'];
			$object_list = implode(',', array_keys($check_list));
			$emd_docs = $this->getEMDDocumentsList(array('object_list' => $object_list));

			if (!empty($emd_docs)) {

				$warning_list = &$work_data['warning_list'];

				foreach ($emd_docs as $emd_item) {

					if (isset($check_list[$emd_item['EMDRegistry_ObjectID']])) {
						$item = $check_list[$emd_item['EMDRegistry_ObjectID']];

						// если документ устарел, то сформировать сообщение что надо переподписать
						if ($item['actualDate'] != $emd_item['EMDVersion_actualDT']) {
							$item['isNotActual'] = true;
							$this->addWarningItem($warning_list, $item);
						}
					}
				}
			}
		}

		$response = array('data' => null);
		if (!empty($work_data['warning_list'])) {

			if (!empty($data['asRawData'])) {
				$response = $work_data['warning_list'];
			} else {
				$response['data']['text'] = "";
				foreach ($work_data['warning_list'] as $visit) {
					$response['data']['text'] .= "<ul class='emd-warning-list'><span>".$visit['parent_EvnClass_Name']." от ".$visit['parent_Evn_setDate'].":</span>";
					foreach ($visit['childs'] as $unsignedDoc) {
						$response['data']['text'] .= "<li>".$unsignedDoc['title']."</li>";
					}
					$response['data']['text'] .= "<ul>";
				}
			}
		}

		return $response;
	}

	/**
	 * создаем варнинг лист
	 */
	function makeWarningList($data, $list) {

		$warning_list = &$data['warning_list'];
		$check_list = &$data['check_list'];

		foreach ($list as $item) {

			// если подписи нет
			if (empty($item['IsSigned'])) {

				$this->addWarningItem($warning_list, $item);

			} else {
				// значит надо проверить в базе РЭМД дату актуальности
				$check_list[$item['ObjectID']] = $item;
			}
		}

		return $data;
	}

	/**
	 * добавляем варнинг
	 */
	function addWarningItem(&$warning_list, $item) {

		// значит надо сформировать сообщение что надо подписать
		if (empty($warning_list[$item['Evn_pid']])) {
			$warning_list[$item['Evn_pid']] = array(
				'parent_EvnClass_Name' => $item['parent_EvnClass_Name'],
				'parent_Evn_setDate' => $item['parent_Evn_setDate'],
				'childs' => array()
			);
		}

		$title = $item['docName'];
		$isEvnXml = !empty($item['EvnXml_id']);

		if (!$isEvnXml) {
			if ($item['docType'] == 27) $title = "Направление";
			else if ($item['docType'] == 4) $title = "Рецепт";
		} else {
			if ($item['docType'] == 3) $title = "Осмотр";
			$title = "Документ: ". $title;
		}

		$title .= (!empty($item['docNum']) ? ' №'.$item['docNum'] : '');
		$title .= (!empty($item['isNotActual']) ? ' (не актуален в РЭМД)' : '');

		$warning_list[$item['Evn_pid']]['childs'][] = array(
			'title' => $title,
			'type' => $item['docType'],
			'isEvnXml' => $isEvnXml
		);
	}

	/**
	 * Получить список ЭМД для формы "Реестр внешних ЭМД"
	 */
	function getEMDlist($data) {
		$query = '
				SELECT
					emdr."EMDOuterRegistry_id",
					emdd."EMDDocumentType_Name",
					emdr."EMDOuterRegistry_emdrId",
					CASE WHEN emdr."EMDOuterRegistry_FileData" IS NULL then 0 else 2 end as "EMDOuterRegistry_HasFile",
					to_char(emdr."EMDOuterRegistry_regDate", \'DD.MM.YYYY\') as "EMDOuterRegistry_regDate",
					to_char(emdj."EMDJournalQuery_insDT", \'DD.MM.YYYY\') as "EMDJournalQuery_insDT"
				FROM "EMD"."EMDOuterRegistry" emdr
					left join "EMD"."EMDDocumentType" emdd on emdr."EMDDocumentType_id"=emdd."EMDDocumentType_id"
					left join "EMD"."EMDOuterRegQueryLink" emdq on emdq."EMDOuterRegistry_id"=emdr."EMDOuterRegistry_id"
					left join "EMD"."EMDJournalQuery" emdj on emdj."EMDJournalQuery_id"=emdq."EMDJournalQuery_id"
				WHERE emdr."Person_id" = :Person_id
			';
		return $this->queryResult($query, array('Person_id'=>$data['Person_id']), $this->emddb);
	}

	/**
	 * Парсинг поля имени сертификата
	 */
	function parseCertAdditionalData($cert_data) {

		$params = explode('/', $cert_data);
		foreach ($params as $key => $np) {
			$values = explode('=', $np);
			if (!empty($values[0]) && count($values) > 1) {
				$params[$values[0]] = $values[1];
			}
			unset($params[$key]);
		}

		return $params;
	}

	/**
	 * Парсинг поля имени сертификата
	 */
	function getCertAdditionalParam($param, $certParams) {
		return isset($certParams[$param]) ? $certParams[$param] : null;
	}

	/**
	 * Получить файл
	 */
	function getEMDOuterRegistry_File($data) {
		$sql = '
			SELECT "EMDOuterRegistry_FileData"
			FROM "EMD"."EMDOuterRegistry" emdr
			WHERE emdr."EMDOuterRegistry_id" = :EMDOuterRegistry_id
		';
		$res = $this->getFirstResultFromQuery($sql, array('EMDOuterRegistry_id'=>$data['EMDOuterRegistry_id']), $this->emddb);
		return $res;
	}

	/**
	 * Получение статуса подписания документов
	 * кол-во подписей, кол-во необходимых подписей, подписан ли текущим пользователем
	 * @param $data
	 */
	function getSignStatus($data) {
		if (empty($data['EMDRegistry_ObjectIDs'])) {
			return [];
		}

		$filter_s = '';
		if ($data['EMDRegistry_ObjectName'] != 'EvnRecept') {
			$filter_s .= ' and "EMDPersonRole_id" is not null'; // в листах согалсования хранятся и подписи МО
		}

		$resp = $this->queryResult('
			select
				emdr."EMDRegistry_ObjectID",
				emds.cnt as signcount,
				almp.cnt as minsigncount,
				case when exists(select "EMDSignatures_id" from "EMD"."EMDSignatures" where "EMDVersion_id" = emdv."EMDVersion_id" and "MedStaffFact_id" = :MedStaffFact_id) then 2 else null end as signed
			from
				"EMD"."EMDRegistry" emdr
				inner join lateral (
					select
						"EMDVersion_id"
					from
						"EMD"."EMDVersion"
					where
						"EMDRegistry_id" = emdr."EMDRegistry_id"
						and "EMDVersion_VersionNum" is not null
					order by
						"EMDVersion_id" desc
					limit 1
				) emdv on true
				inner join lateral (
					select
						count("EMDSignatures_id") as cnt
					from
						"EMD"."EMDSignatures"
					where
						"EMDVersion_id" = emdv."EMDVersion_id"
						' . $filter_s . '
				) emds on true
				inner join lateral (
					select
						count("ApprovalListMedPersonal_id") as cnt
					from
						"EMD"."ApprovalList" al
						inner join "EMD"."ApprovalListMedPersonal" almp on almp."ApprovalList_id" = al."ApprovalList_id"
					where
						al."ApprovalList_ObjectName" = :EMDRegistry_ObjectName
						and al."ApprovalList_ObjectId" = emdr."EMDRegistry_ObjectID"
				) almp on true
			where
				emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
				and emdr."EMDRegistry_ObjectID" in ('.implode(',', $data['EMDRegistry_ObjectIDs']).')
		', [
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'MedStaffFact_id' => $data['MedStaffFact_id']
		], $this->emddb);

		$response = [];
		foreach($resp as $respone) {
			if ($respone['signcount'] >= $respone['minsigncount']) {
				$respone['signed'] = 2;
			}
			$response[$respone['EMDRegistry_ObjectID']] = $respone;
		}

		return $response;
	}
	
	/**
	 * Отмена готовности версии к отправке в РЭМД
	 * @param array $data
	 * @return array
	 */
	function setEMDVersionNotReady(array $data):array {
		// 1. ищем версии, готовые к отправке
		$resp_emdv = $this->queryResult('
			SELECT
				emdv."EMDVersion_id",
				emdv."EMDVersion_EmdrId"
			FROM
				"EMD"."EMDRegistry" emdr
				inner join "EMD"."EMDVersion" emdv on emdv."EMDRegistry_id" = emdr."EMDRegistry_id"
			WHERE
				emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
				and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
				and emdv."EMDVersion_IsReady" = 1
		', [
			'EMDRegistry_ObjectName' => $data['EMDRegistry_ObjectName'],
			'EMDRegistry_ObjectID' => $data['EMDRegistry_ObjectID']
		], $this->emddb);

		// 2. проставляем признак
		$EMDVersion_id = null;
		foreach($resp_emdv as $one_emdv) {
			$this->emddb->query('
				update
					"EMD"."EMDVersion"
				set
					"EMDVersion_IsReady" = 0
				where
					"EMDVersion_id" = :EMDVersion_id
			', ['EMDVersion_id' => $one_emdv['EMDVersion_id']]);

			if (!empty($one_emdv['EMDVersion_EmdrId'])) {
				$EMDVersion_id = $one_emdv['EMDVersion_id'];
			}
		}

		// 3. возвращаем данные об отправленной версии, если она есть
		if (!empty($EMDVersion_id)) {
			return [
				'EMDVersion_id' => $EMDVersion_id
			];
		}

		return [];
	}

    /**
     * @param array $data
     * @description Получение списка РЭМД документов по пациенту (для показа!)
     * @return array|false
     */
    public function getEMDlistByPersonForShow($data = []) {
        return $this->queryResult('
			SELECT
				emdr."EMDRegistry_id",
				emdr."EMDRegistry_ObjectName",
				emdr."EMDRegistry_ObjectID",
				dtl."EMDDocumentTypeLocal_Name",
				emdr."EMDRegistry_Num",
				to_char(emdr."EMDRegistry_EMDDate", \'DD.MM.YYYY\') as "EMDRegistry_EMDDate",
				emdv."EMDVersion_FilePath"
			FROM "EMD"."EMDRegistry" as emdr
				left join "EMD"."EMDDocumentTypeLocal" as dtl on dtl."EMDDocumentTypeLocal_id" = emdr."EMDDocumentTypeLocal_id"
				left join lateral (
					select "EMDVersion_FilePath"
					from "EMD"."EMDVersion"
					where "EMDRegistry_id" = emdr."EMDRegistry_id"
					fetch first 1 row only
				) emdv on true
			WHERE  
				emdr."Person_id" = :Person_id
				and coalesce(emdr."EMDRegistry_deleted", 1) = 1
				and emdv."EMDVersion_FilePath" is not null
			LIMIT 100
		', $data, $this->emddb);
    }

    /**
     * Получение строки правил подписания документа
     */
    public function getEmdSignatureRules($data)
    {

        $params = [
            'EMDPersonRole_id' => $data['EMDPersonRole_id'],
            'EMDDocumentType_id' => $data['EMDDocumentType_id']
        ];

        $resp = $this->queryResult('
			SELECT "EMDSignatureRules_Post"
			FROM "EMD"."EMDSignatureRules"
			where "EMDPersonRole_id" = :EMDPersonRole_id and "EMDDocumentType_id" = :EMDDocumentType_id
		',$params,$this->emddb);

        return $resp;
    }

    /**
     * Получение информации о подписи
     */
    public function getEMDSignaturesInfo($data = [])
    {
        return $this->queryResult('
			SELECT
				emds."EMDSignatures_id",
			    emdc."EMDCertificate_CommonName",
				emdv."EMDVersion_VersionNum",
			    to_char(s."Signatures_insDT", \'DD.MM.YYYY HH24:MI:SS\') as "Signatures_insDT",
				s."Signatures_Hash"
			FROM
				"EMD"."EMDSignatures" emds
				left join "EMD"."EMDVersion" emdv on emds."EMDVersion_id" = emdv."EMDVersion_id"
				inner join "EMD"."Signatures" s on s."Signatures_id" = emds."Signatures_id"
				inner join "EMD"."EMDCertificate" emdc on emdc."EMDCertificate_id" = s."EMDCertificate_id"
			WHERE
				emds."EMDSignatures_id" = :EMDSignatures_id
			LIMIT 1
		', $data, $this->emddb);
    }
    
	/**
	 * @description Получение списка статусов запросов ЭМД для комбобокса
	 */
	public function getEMDQueryStatus() {
		return $this->queryResult('
			SELECT
				emdqs."EMDQueryStatus_id",
			    emdqs."EMDQueryStatus_Code",
			    emdqs."EMDQueryStatus_Name"
			FROM
				"EMD"."EMDQueryStatus" emdqs
			
		', [], $this->emddb);
	}
}