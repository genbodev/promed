<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Privilege - контроллер для работы со льготами людей
* вынесено из dlo_ivp и dlo_svb
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @originalauthor       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      16.07.2009
*/
/**
 * @property Privilege_model $dbmodel
 * @property PrivilegeAccessRights_model $parmodel
*/
class Privilege extends swController {
	/**
	*  Описание правил для входящих параметров
	*  @var array
	*  KLAreaStat_id
	*/
	public $inputRules = array();

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'exportPFRIdentificationData' => array(
				array('field' => 'allowEmptyTags', 'label' => 'Разрешить пустые теги', 'rules' => '', 'type' => 'int'),
				array('field' => 'KKK', 'label' => 'KKK', 'rules' => '', 'type' => 'int', 'default' => 999),
				array('field' => 'maxRecordsPerFile', 'label' => 'Максимальное количество записей в файле', 'rules' => '', 'type' => 'int', 'default' => 10000),
				array('field' => 'tableName', 'label' => 'Наименование таблицы', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'tableScheme', 'label' => 'Схема таблицы', 'rules' => '', 'type' => 'string', 'default' => 'tmp'),
				array('field' => 'VV', 'label' => 'Порядковый номер пакета', 'rules' => '', 'type' => 'int', 'default' => 1),
			),
			'exportPFRValidationData' => array(
				array('field' => 'allowEmptyTags', 'label' => 'Разрешить пустые теги', 'rules' => '', 'type' => 'int'),
				array('field' => 'KKK', 'label' => 'KKK', 'rules' => '', 'type' => 'int', 'default' => 999),
				array('field' => 'maxRecordsPerFile', 'label' => 'Максимальное количество записей в файле', 'rules' => '', 'type' => 'int', 'default' => 10000),
				array('field' => 'rejectEmptySNILS', 'label' => 'Не выгружать запсии с пустым СНИЛС', 'rules' => '', 'type' => 'int'),
				array('field' => 'tableName', 'label' => 'Наименование таблицы', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'tableScheme', 'label' => 'Схема таблицы', 'rules' => '', 'type' => 'string', 'default' => 'tmp'),
				array('field' => 'VV', 'label' => 'Порядковый номер пакета', 'rules' => '', 'type' => 'int', 'default' => 1),
			),
			'deletePersonPrivilege' => array(
				array(
					'field' => 'PersonPrivilege_id',
					'label' => 'Идентификатор льготы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLgotList' => array(
				array(
					'field' => 'PrivilegeStateType_id',
					'label' => 'Тип отображаемых льгот',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeSearchType_id',
					'label' => 'Тип поиска',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_prid',
					'label' => 'МО прикрепления',
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
				)
			),
			'getKardioPrivilegeConsentData' => array(
				array(
					'field' => 'EvnPS_id',
					'label' => 'Идентификатор КВС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadPersonPrivilegeList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadPrivilegeEditForm' => array(
				array(
					'field' => 'PersonPrivilege_id',
					'label' => 'Идентификатор льготы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'savePrivilege' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonPrivilege_IsAddMZ',
					'label' => 'Добавлено минздравом',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonPrivilege_id',
					'label' => 'Идентификатор льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Privilege_begDate',
					'label' => 'Дата начала действия льготы',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Privilege_endDate',
					'label' => 'Дата окончания действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'тип финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'checking_for_regional_benefits',
					'label' => 'проверка существования региональной льготы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'SubCategoryPrivType_id',
					'label' => 'Подкатегория льготы',
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
					'field' => 'DocumentPrivilege_id',
					'label' => 'Идентификатор документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentPrivilegeType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentPrivilege_Ser',
					'label' => 'Серия документа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentPrivilege_Num',
					'label' => 'Номер документа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DocumentPrivilege_begDate',
					'label' => 'Дата документа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DocumentPrivilege_Org',
					'label' => 'Организация документа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PrivilegeCloseType_id',
					'label' => 'Причина закрытия',
					'rules' => '',
					'type' => 'id'
				)
			),
			'savePrivilegeConsent' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Privilege_begDate',
					'label' => 'Дата начала действия льготы',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Privilege_endDate',
					'label' => 'Дата окончания действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonPrivilege_id',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'is_stac',
					'label' => 'Признак включения в стационаре',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadPersonCategoryList' => array(
				array(
					'default' => date('Y-m-d'),
					'field' => 'Date',
					'label' => 'Дата на которую выводим список льгот',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'CheckPersonHaveActiveFederalPrivilege' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Privilege_begDate',
					'label' => 'Дата проверки действия привилегии',
					'rules' => '',
					'type' => 'date',
					'default' => date('Y-m-d')
				),
				array(
					'field' => 'PrivilegeTypeCodeList',
					'label' => 'Список кодов льгот для проверки',
					'rules' => '',
					'type' => 'string'
				)
			),
			'exportForLaborDep' => array(
				array(
					'field' => 'LabExp_Period',
					'label' => 'Дпериод дат на который выводим список льгот',
					'rules' => 'required',
					'type' => 'daterange'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Идентификатор льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'KLAreaStat_idEdit',
					'label' => 'Идентификатор территории',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'KLArea_Name',
					'label' => 'Наимнование территории',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'ExportRrl' => array(
				array(
					'field' => 'PersonPrivilege_begDate',
					'label' => 'Дата начала действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonPrivilege_endDate',
					'label' => 'Дата окончания действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonPrivilege_begDateFrom',
					'label' => 'Дата начала действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonPrivilege_endDateFrom',
					'label' => 'Дата окончания действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonPrivilege_begDateTo',
					'label' => 'Дата начала действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonPrivilege_endDateTo',
					'label' => 'Дата окончания действия льготы',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'PersonPrivilege_onlyValid',
					'label' => 'Только действующие льготы',
					'rules' => '',
					'type' => 'int'
				)
			),
			'checkPersonPrivilege' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Идентификатор категории льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Privilege_begDate',
					'label' => 'Дата начала льготы',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'checkPersonPrivilegeExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkPersonCard' => array(
				array(
					'field' => 'Person_id',
					'label'	=> 'Идентификатор пациента',
					'rules'	=> 'required',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'Lpu_id',
					'label'	=> 'Идентификатор МО',
					'rules'	=> 'required',
					'type'	=> 'id'
				)
			),
			'createEgissoData' => array(
				array(
					'field' => 'EvnRecept_setDate',
					'label'	=> 'Дата рецепта',
					'rules'	=> 'required',
					'type'	=> 'string'
				),
				array(
					'field' => 'debug',
					'label'	=> 'Отладка',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field' => 'url',
					'label'	=> 'Отладка',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field' => 'options',
					'label'	=> 'Отладка',
					'rules'	=> '',
					'type'	=> 'json_array'
				)
			),
			'saveDocumentPrivilegeType' => array(
				array('field' => 'DocumentPrivilegeType_id', 'label' => 'Идентификатор типа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DocumentPrivilegeType_Code', 'label' => 'Код типа', 'rules' => '', 'type' => 'int'),
				array('field' => 'DocumentPrivilegeType_Name', 'label' => 'Наименование типа', 'rules' => 'trim', 'type' => 'string')
			),
			'savePersonPrivilegeReq' => array(
				array('field' => 'PersonPrivilegeReq_id', 'label' => 'Идентификатор запроса', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Идентификатор подразделения', 'type' => 'id'),
				array('field' => 'PostMed_id', 'label' => 'Идентификатор должности', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'type' => 'id'),
				array('field' => 'PrivilegeType_id', 'label' => 'Идентификатор льготной категории', 'type' => 'id'),
				array('field' => 'Diag_id', 'label' => 'Идентификатор диагноза', 'type' => 'id'),
				array('field' => 'PersonPrivilegeReq_begDT', 'label' => 'Дата начала действия льготы', 'type' => 'date'),
				array('field' => 'PersonPrivilegeReq_endDT', 'label' => 'Дата окончания действия льготы', 'type' => 'date'),
				array('field' => 'DocumentPrivilege_id', 'label' => 'Идентификатор документа', 'type' => 'id'),
				array('field' => 'DocumentPrivilegeType_id', 'label' => 'Идентификатор типа документа', 'type' => 'id'),
				array('field' => 'DocumentPrivilege_Ser', 'label' => 'Серия документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DocumentPrivilege_Num', 'label' => 'Номер документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DocumentPrivilege_begDate', 'label' => 'Дата документа', 'type' => 'date'),
				array('field' => 'DocumentPrivilege_Org', 'label' => 'Организация документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'send_to_expertise', 'label' => 'Флаг "отправить на экспертизу"', 'rules' => '', 'type' => 'int'),
				array('field' => 'ReceptFinance_id', 'label' => 'тип финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonSurNameAtBirth_SurName', 'label' => 'Фамилия при рождении', 'rules' => '', 'type' => 'string')
			),
			'savePersonPrivilegeReqExpertise' => array(
				array('field' => 'PersonPrivilegeReq_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonPrivilegeReqAns_DeclCause', 'label' => 'Причина отказа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'PersonPrivilegeReq_endDT', 'label' => 'Дата окончания действия льготы', 'type' => 'date'),
				array('field' => 'action', 'label' => 'Результат экспертизы', 'rules' => 'trim', 'type' => 'string')
			),
			'deletePersonPrivilegeReq' => array(
				array('field' => 'id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id')
			),
			'loadPersonPrivilegeReq' => array(
				array('field' => 'PersonPrivilegeReq_id', 'label' => 'Идентификатор запроса', 'rules' => 'required', 'type' => 'id')
			),
			'loadPersonPrivilegeReqList' => array(
				array('field' => 'begDate', 'label' => 'Дата начала периода', 'type' => 'date'),
				array('field' => 'endDate', 'label' => 'Дата окончания периода', 'type' => 'date'),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_BirthDay_Range', 'label' => 'Дата рождения', 'type' => 'daterange'),
				array('field' => 'PrivilegeType_id', 'label' => 'Льготная категория', 'type' => 'id'),
				array('field' => 'PersonPrivilegeReqStatus_id', 'label' => 'Статус запроса', 'type' => 'id'),
				array('field' => 'Result_Type', 'label' => 'Результат', 'type' => 'string'),
				array('default' => 0, 'field' => 'exclude_new_requests', 'label' => 'Запрет отображения записей со статусом "Новый"', 'rules' => 'trim', 'type' => 'int'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'type' => 'int')
			),
			'loadPersonSurNameAtBirth' => [
				['field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id']
			],
			'loadDocumentPrivilegeTypeCombo' => array(
				array('field' => 'DocumentPrivilegeType_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'loadDiagByPrivilegeTypeCombo' => array(
				array('field' => 'Diag_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
				array('field' => 'PrivilegeType_id', 'label' => 'Идентификатор льготы', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'loadPersonPrivilegeReqMedStaffFactCombo' => array(
				array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'loadPersonSurNameCombo' => [
				['field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string']
			],
			'checkPersonHaveActiveRegionalPrivilege' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Privilege_begDate', 'label' => 'Дата начала действия льготы', 'rules' => 'required', 'type' => 'date')
			),
			'checkPrivilegeMainOrServiceAttachment' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
		)
		);
	}

	/**
	 * Description
	 */
	function Index() {
		return false;
	}


	/**
	 * Метод сохраняет xml в файл
	 */
	function saveExportRrlXmlToFile($filename, $xml)
	{
		/**
		 * Зазипуй это!
		 */
		function zipit($ziparchivename, $filename)
		{
			$result = true;
			$zip = new ZipArchive();
			$result = $result && $zip->open($ziparchivename, ZIPARCHIVE::CREATE);
			$result = $result && $zip->AddFile($filename, basename($filename));
			$result = $result && $zip->close();
			return $result;
		}
		$data = array();
		$data = array_merge($data, getSessionParams());
		$base_name = ($_SERVER["DOCUMENT_ROOT"][strlen($_SERVER["DOCUMENT_ROOT"])-1]=="/")?$_SERVER["DOCUMENT_ROOT"]:$_SERVER["DOCUMENT_ROOT"]."/";
		if ( !file_exists($base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/') ) {
			//создаем директорию для LPU
			mkdir ($base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/');
		}
		$filepath = $base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/' . $filename . '.xml';
		$zipfilepath = $base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/' . $filename . '.zip';
		$filelink = '/export/rrl_files/' . $data['Lpu_id'] . '/' . $filename . '.zip';

		if ( file_exists($filepath) ) {
			unlink ($filepath);
		}
		$success = file_put_contents($filepath, $xml);
		if ( $success ) {
			if (!zipit($zipfilepath, $filepath)) {
				throw new Exception('Ошибка архивации ' . $dbf_full_name . '->' . $zip_full_name);
			} else {
				return $filelink;
			}
		}
			
		return false;
	}
	
	/**
	 * Сохранение ошибок выгрузки РРЛ в отдельный файл
	 */
	function saveExportRrlErrorsToFile($errors)
	{
		$data = array();
		$data = array_merge($data, getSessionParams());
		$base_name = ($_SERVER["DOCUMENT_ROOT"][strlen($_SERVER["DOCUMENT_ROOT"])-1]=="/")?$_SERVER["DOCUMENT_ROOT"]:$_SERVER["DOCUMENT_ROOT"]."/";
		if ( !file_exists($base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/') ) {
			//создаем директорию для LPU
				mkdir ($base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/');
		}
		$filepath = $base_name . '/export/rrl_files/' . $data['Lpu_id'] . '/errors.txt';
		$filelink = '/export/rrl_files/' . $data['Lpu_id'] . '/errors.txt';

		if ( file_exists($filepath) ) {
			unlink ($filepath);
		}
		$success = file_put_contents($filepath, $errors);
		if ( $success ) {
			return $filelink;
		}
			
		return false;
	}
	
	/**
	*  Экспорт регистра региональных льготников
	*  Используется: форма экспорта РРЛ
	*/	
	function ExportRrl() {
		@ini_set('max_execution_time', 0);

		$this->load->database();
		$this->load->model("Privilege_model", "dbmodel");
		
		$data = $this->ProcessInputData('ExportRrl', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getDataForExportRrl($data);

		$errorstxt = '';
		
		// проверка обязательных полей на заполненность.
		if (empty($response['SHORTNAME'])) { $errorstxt .= 'Не заполнено поле Краткое название МО (SHORTNAME)'."\r\n"; }
		if (empty($response['OGRN'])) { $errorstxt .= 'Не заполнено поле OGRN'."\r\n"; }
		if (empty($response['CODE_MO'])) { $errorstxt .= 'Не заполнено поле Код МО (CODE_MO)'."\r\n"; }
		if (empty($response['LPU_NAME'])) { $errorstxt .= 'Не заполнено поле Название МО (LPU_NAME)'."\r\n"; }
		if (empty($response['CREATE_DATE'])) { $errorstxt .= 'Не заполнено поле CREATE_DATE'."\r\n"; }
		
		foreach($response['PersonPrivilegies'] as $perskey => $pers) {
			$perserrors = '';
			if (empty($pers['ID'])) { $perserrors.= ', Идентификатор (ID)'; }
			if (empty($pers['LASTNAME'])) { $perserrors.= ', Фамилия (LASTNAME)'; }
			if (empty($pers['NAME'])) { $perserrors.= ', Имя (NAME)'; }
			//if (empty($pers['PATRONYMIC'])) { $perserrors.= ', Отчество (PATRONYMIC)'; }
			if (empty($pers['SEX'])) { $perserrors.= ', Пол (SEX)'; }
			if (empty($pers['BDAY'])) { $perserrors.= ', Дата рождения (BDAY)'; }
			if (empty($pers['ADDR_REG'])) { $perserrors.= ', Адрес регистрации (ADDR_REG)'; }
			if (empty($pers['REGION'])) { $perserrors.= ', Регион (REGION)'; }
			if (empty($pers['ADDR_TEXT'])) { $perserrors.= ', Полный адрес регистрации (ADDR_TEXT)'; }
			if (empty($pers['CODE'])) { $perserrors.= ', Код льготы (CODE)'; }
			if (empty($pers['START'])) { $perserrors.= ', Дата начала действия льготы (START)'; }

			if(!empty($perserrors)) {
				$perserrors = substr($perserrors,1);
				$perserrors = $pers['ID']. '. ' . $pers['LASTNAME']. ' ' . $pers['NAME']. ' ' . $pers['PATRONYMIC'] . '. Не заполнено:' . $perserrors . ''."\r\n";
				$errorstxt .= $perserrors;
				unset($response['PersonPrivilegies'][$perskey]);
			}
		}
		
		$this->load->library('parser');
		$xml = $this->parser->parse('rrl_export', $response, true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $xml;
		$xml = toUTF($xml);

		$filename = 'RRL' . (!empty($response['OGRN'])?$response['OGRN']:'')  . '_' . date('ymd');
		$filenameout = $filename.'.zip';
		
		$errornameout = '';
		$errorlink = '';
		
		if (!empty($errorstxt)) {
			$errornameout = 'errors.txt';
			$errorlink = $this->saveExportRrlErrorsToFile($errorstxt);
		}
		
		// пустые необязательные теги попадать в выгрузку не должны (удаляем)
		
		$pattern = array(
			'/<SNILS><\/SNILS>/',
			'/<SN_POL><\/SN_POL>/',
			'/<DOC><\/DOC>/',
			'/<DOC_SER><\/DOC_SER>/',
			'/<DOC_NMB><\/DOC_NMB>/',
			'/<STOP><\/STOP>/'
		);
		$xml = preg_replace($pattern, '', $xml);
		
		$link = $this->saveExportRrlXmlToFile($filename, $xml, $errorstxt);
		
		if ( $link )
		{
			$filedata = array(
				'link' => $link,
				'filename' => $filenameout,
				'errorlink' => $errorlink,
				'errorfilename' => $errornameout,
				'success' => true
			);
			$this->ReturnData($filedata);
		} else {
			$this->ReturnError('Не удалось сформировать файл выгрузки.');
			return false;
		}		
	}
	
	/**
	*  Удаление льготы
	*  Входящие данные: $_POST['PersonPrivilege_id']
	*  На выходе: JSON-строка
	*  Используется: форма поиска льгот
	*/
	function deletePersonPrivilege() {
		$data = $this->ProcessInputData('deletePersonPrivilege');
		if ($data === false) { return false; }

		$this->load->database();
		$this->load->model("Privilege_model", "dbmodel");

		$response = $this->dbmodel->deletePersonPrivilege($data);

		$this->ProcessModelSave($response, true, 'При удалении данных произошла ошибка')->ReturnData();
		return true;
	}

	/**
	* Получение дерева льгот
	*/
	function getLgotTree() {
		$this->load->database();
		$this->load->model("Privilege_model", "dbmodel");

		$data = getSessionParams();
		$val  = array();

		$response = $this->dbmodel->getLgotTree($data);
		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = array(
					'cls' => 'file',
					'id' => $row['PrivilegeType_id'],
					'leaf' => true,
					'text' => $row['PrivilegeType_VCode'] . ". " . $row['PrivilegeType_Name']
				);
			}
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	* Получение списка льготников по выбранной категории
	*/
	function getLgotList() {
		$this->load->database();
		$this->load->model("Privilege_model", "dbmodel");

		$data = $this->ProcessInputData('getLgotList', true, true);
		if ($data) {

			$response = $this->dbmodel->getLgotList($data);

//            if (sizeof($response) > 0){
//                $this->ProcessModelList($response, true, true)->ReturnLimitData(NULL, $data['start'], $data['limit']);
//                return true;
//            } else {
//                return false;
//            }
			$this->ProcessModelList($response, true, true)->ReturnLimitData(NULL, $data['start'], $data['limit']);
		} else {
			return false;
		}
	}

	/**
	 *	Определение необходимости получения подтверждения для включения в программу ДЛО Кардио
	 */
	function getKardioPrivilegeConsentData() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('getKardioPrivilegeConsentData', false);
		if ($data) {
			$response = $this->dbmodel->getKardioPrivilegeConsentData($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	*  Получение списка льгот человека
	*  Входящие данные: $_POST['Person_id'],
	*  На выходе: JSON-строка
	*  Используется: форма просмотра льгот
	*/
	function loadPersonPrivilegeList() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonPrivilegeList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadPersonPrivilegeList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	*  Получение данных для формы редактирования льготы
	*  Входящие данные: $_POST['PersonPrivilege_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования удостоверения льготника
	*/
	function loadPrivilegeEditForm() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPrivilegeEditForm', true, true);
		if ($data) {
			$response = $this->dbmodel->loadPrivilegeEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	function CheckPersonHaveActiveFederalPrivilege() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('CheckPersonHaveActiveFederalPrivilege', true, true);
		if ($data) {
			$response = $this->dbmodel->CheckPersonHaveActiveFederalPrivilege($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	*  Сохранение льготы
	*  Входящие данные: $_POST['PersonPrivilege_id'],
	*                   $_POST['Person_id'],
	*                   $_POST['PrivilegeType_id'],
	*                   $_POST['Privilege_begDate'],
	*                   $_POST['Privilege_endDate'],
	*  На выходе: JSON-строка
	*  Используется: форма редактирования льготы
	*/
	function savePrivilege() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = array();
		$val =  array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$err = getInputParams($data, $this->inputRules['savePrivilege']);

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		if ( isset($data['Privilege_begDate']) ) {
			$compare_result = swCompareDates(trim($_POST['Privilege_begDate']), date('d.m.Y'));
			if ( -1 == $compare_result[0] ) {
				$val = array('success' => false, 'Error_Msg' => 'Дата начала действия льготы не должна быть больше текущей даты');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}

			$compare_result = swCompareDates('01.01.1900', trim($_POST['Privilege_begDate']));
			if ( -1 == $compare_result[0] ) {
				$val = array('success' => false, 'Error_Msg' => 'Дата начала действия льготы должна быть больше 01.01.1900');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}
		}

		if ( isset($data['Privilege_endDate']) ) {
			$compare_result = swCompareDates('01.01.1900', trim($_POST['Privilege_endDate']));
			if ( -1 == $compare_result[0] ) {
				$val = array('success' => false, 'Error_Msg' => 'Дата окончания действия льготы должна быть больше 01.01.1900');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}
		}

		if ( (!isset($data['Person_id'])) || (!isset($data['PrivilegeType_id'])) ) {
			$val = array('success' => false, 'Error_Msg' => 'Неверно заданы обязательные параметры');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}

		if ( isset($data['Privilege_begDate']) && isset($data['Privilege_endDate']) ) {
			$compare_result = swCompareDates(trim($_POST['Privilege_begDate']), trim($_POST['Privilege_endDate']));

			if ( $compare_result[0] == 100 ) {
				$val = array('success' => false, 'Error_Msg' => 'Неверный формат даты начала или даты окончания действия льготы');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}
			else if ( (-1 == $compare_result[0]) || (0 == $compare_result[0]) ) {
				$val = array('success' => false, 'Error_Msg' => 'Дата начала действия льготы должна быть меньше даты окончания');
				array_walk($val, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData($val);
				return false;
			}
		}

		if($data['session']['region']['nick'] != 'kz') {
			$snils = $this->dbmodel->getSnilsNumber($data);

			if (!is_array($snils) or count($snils) == 0 or empty($snils[0]['Person_Snils'])) {
				$this->ReturnData(array(
					'success' => false,
					'nosnils' => true,
					'Error_Msg' => toUTF('Создание льготы невозможно. У пациента отсутствует СНИЛС. Добавить СНИЛС?')
				));
				return false;
			}
		}

		// Получаем тип финансирования льготы
		if(!in_array($data['session']['region']['nick'], array('kz', 'penza'))) {
			$response = $this->dbmodel->getPrivilegeReceptFinance($data);

			if ( !is_array($response) || count($response) == 0 ) {
				$this->ReturnData(array(
					'success' => false,
					'Error_Msg' => toUTF('Ошибка при выполнении запроса к базе данных (проверка типа финансирования услуги)')
				));
				return false;
			}

			if ( !isSuperadmin() && $response[0]['ReceptFinance_Code'] == 1 ) {
				if(!(havingGroup('ChiefLLO') && $data['session']['region']['nick'] == 'krym'))
				{
					$this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('У вас недостаточно прав для сохранения федеральной льготы)')
					));
					return false;
				}
			}
		}
		
		//проверка существования региональной льготы
		if(in_array($data['session']['region']['nick'], array('msk')) && !empty($data['ReceptFinance_id']) && $data['ReceptFinance_id'] == 1 && empty($data['checking_for_regional_benefits'])){
			$res = $this->dbmodel->CheckPersonHaveActiveRegionalPrivilege($data);
			if ( (is_array($res)) && (count($res) > 0) ) {
				if ( $res[0]['Privilege_Count'] > 0 ) {
					$this->ReturnData(array(
						'success' => false,
						'PrivilegeRegion_Count' => $res[0]['Privilege_Count'],
						'Alert_Msg' => toUTF('Добавить федеральную льготу и закрыть имеющиеся региональные льготы?')
					));
					return false;
				}
			}
		}

		//группа проверок льготы по ССЗ (только для Перми)
		$wdcit_data = $this->dbmodel->getWhsDocumentCostItemTypeByPrivilegeType($data);
		if (is_array($wdcit_data) && !empty($wdcit_data['WhsDocumentCostItemType_Nick']) && $wdcit_data['WhsDocumentCostItemType_Nick'] == 'acs') { //признак связи категории с програмой "ССЗ"
			if (getRegionNick() == 'kareliya') {
				// Контроль на наличие федеральной льготы
				$res = $this->dbmodel->checkPrivilegeAcsFedPrivilegeKareliya($data);
				if (is_array($res) && !$res['check_result']) {
					$this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF(!empty($res['Error_Msg']) ? $res['Error_Msg'] : 'При проверке данных произошла ошибка')
					));
					return false;
				}
			} else {
				// Контроль на наличие федеральной льготы или отказа от него
				$res = $this->dbmodel->checkPrivilegeAcsFedPrivilege($data);
				if (is_array($res) && !$res['check_result']) {
					$this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF(!empty($res['Error_Msg']) ? $res['Error_Msg'] : 'При проверке данных произошла ошибка')
					));
					return false;
				}
			}

			// Контроль на первышение периодом действия одного года
			if (!empty($data['Privilege_begDate']) && !empty($data['Privilege_endDate'])) {
				$beg_date_array = explode('-', $data['Privilege_begDate']);
				$max_end_date = count($beg_date_array) == 3 ? ($beg_date_array[0]+1).'-'.$beg_date_array[1].'-'.$beg_date_array[2] : null;
				if (!empty($max_end_date)) {
					$compare_result = swCompareDates(trim($_POST['Privilege_endDate']), $max_end_date);
					if (-1 == $compare_result[0]) {
						$this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF('Период включения в регистр ЛЛО ССЗ не может превышать одного года')
						));
						return false;
					}
				}
			}

			// Контроль на наличие ранее открытой льготы по программе
			$res = $this->dbmodel->checkPrivilegeAcsDoublePrivilege($data);
			if (is_array($res) && !$res['check_result']) {
				$this->ReturnData(array(
					'success' => false,
					'Error_Msg' => toUTF(!empty($res['Error_Msg']) ? $res['Error_Msg'] : 'При проверке данных произошла ошибка')
				));
				return false;
			}

			// Контроль на наличие основного прикрепления к МО региона
			$res = $this->dbmodel->checkPrivilegeAcsMainAttachment($data);
			if (is_array($res) && !$res['check_result']) {
				$this->ReturnData(array(
					'success' => false,
					'Error_Msg' => toUTF(!empty($res['Error_Msg']) ? $res['Error_Msg'] : 'При проверке данных произошла ошибка')
				));
				return false;
			}

			// Контроль на наличие карты диспансерного  наблюдения
			$res = $this->dbmodel->checkPrivilegeAcsPersonDisp($data);
			if (is_array($res) && !$res['check_result']) {
				$this->ReturnData(array(
					'success' => false,
					'Error_Msg' => toUTF(!empty($res['Error_Msg']) ? $res['Error_Msg'] : 'При проверке данных произошла ошибка')
				));
				return false;
			}
		}

		$result = array();
		$response = $this->dbmodel->savePrivilege($data);

		if ( is_array($response) && count($response) > 0 ) {
			$result = $response[0];
			if ( !isset($result['success']) ) {
				$result['success'] = ( strlen($response[0]['Error_Msg']) == 0 );
			}
			$result = array_merge($this->dbmodel->getSaveResponse(), $result);
		} else {
			$result = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		//сохранение или удаление данных документа
		if (!empty($result['PersonPrivilege_id'])) {
			if (!empty($data['DocumentPrivilegeType_id'])) { //считаем что если передан тип, то переданы и остальные данные о документе
				$save_result = $this->dbmodel->saveObject('DocumentPrivilege', array(
					'DocumentPrivilege_id' => !empty($data['DocumentPrivilege_id']) ? $data['DocumentPrivilege_id'] : null,
					'PersonPrivilege_id' => $result['PersonPrivilege_id'],
					'DocumentPrivilegeType_id' => !empty($data['DocumentPrivilegeType_id']) ? $data['DocumentPrivilegeType_id'] : null,
					'DocumentPrivilege_Ser' => !empty($data['DocumentPrivilege_Ser']) ? $data['DocumentPrivilege_Ser'] : null,
					'DocumentPrivilege_Num' => !empty($data['DocumentPrivilege_Num']) ? $data['DocumentPrivilege_Num'] : null,
					'DocumentPrivilege_begDate' => !empty($data['DocumentPrivilege_begDate']) ? $data['DocumentPrivilege_begDate'] : null,
					'DocumentPrivilege_Org' => !empty($data['DocumentPrivilege_Org']) ? $data['DocumentPrivilege_Org'] : null
				));
			} else if (!empty($data['DocumentPrivilege_id'])) { //если передан идентификатор документа, а тип нет, значит документ необходимо удалить
				$delete_result = $this->dbmodel->deleteObject('DocumentPrivilege', array(
					'DocumentPrivilege_id' => $data['DocumentPrivilege_id']
				));
			}
		}

		array_walk($result, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($result);
		return true;
	}


	/**
	*  Сохранение льготы
	*  Входящие данные: $_POST['Person_id'],
	*                   $_POST['PrivilegeType_id'],
	*                   $_POST['Privilege_begDate'],
	*                   $_POST['Privilege_endDate'],
	*  На выходе: JSON-строка
	*  Используется: форма получения согласия на включение в программу
	*/
	function savePrivilegeConsent() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('savePrivilegeConsent', false);
		if ($data) {
			// Получаем сессионные переменные
			//$session_data = array_merge($data, getSessionParams());
			//$data = array_merge($data, getSessionParams());

			if ( isset($data['Privilege_begDate']) ) {
				$compare_result = swCompareDates(trim($_POST['Privilege_begDate']), date('d.m.Y'));
				if ( -1 == $compare_result[0] ) {
					$this->ReturnError('Дата начала включения в программу не должна быть больше текущей даты');
					return false;
				}

				$compare_result = swCompareDates('01.01.1900', trim($_POST['Privilege_begDate']));
				if ( -1 == $compare_result[0] ) {
					$this->ReturnError('Дата начала включения в программу должна быть больше 01.01.1900');
					return false;
				}
			}

			if (isset($data['Privilege_endDate'])) {
				$this->load->model('Dlo_EvnRecept_model', 'dloermodel');
				//получаем дату выписки последнего рецепта по программе "ДЛО Кардио"
				$response = $this->dloermodel->getLastDLOKardioReceptDate($data);
				if (!empty($response['EvnRecept_setDT'])) {
					$EvnRecept_setDT = date("d.m.Y", strtotime($response['EvnRecept_setDT']));
					$compare_result = swCompareDates($EvnRecept_setDT, trim($_POST['Privilege_endDate']));
					if (-1 == $compare_result[0]) {
						$this->ReturnError('Дата окончания действия программы "ДЛО Кардио" не может быть раньше 
							даты выписки последнего рецепта по программе. Последний рецепт выписан' . ' ' . $EvnRecept_setDT . ' ' . 'г');
						return false;
					}
				}
			}

			if ( isset($data['Privilege_endDate']) ) {
				$compare_result = swCompareDates('01.01.1900', trim($_POST['Privilege_endDate']));
				if ( -1 == $compare_result[0] ) {
					$this->ReturnError('Дата окончания включения в программу должна быть больше 01.01.1900');
					return false;
				}
			}

			if ( (!isset($data['Person_id'])) || (!isset($data['PrivilegeType_id'])) ) {
				$this->ReturnError('Неверно заданы обязательные параметры');
				return false;
			}

			if ( isset($data['Privilege_begDate']) && isset($data['Privilege_endDate']) ) {
				$compare_result = swCompareDates(trim($_POST['Privilege_begDate']), trim($_POST['Privilege_endDate']));
				if ( $compare_result[0] == 100 ) {
					$this->ReturnError('Неверный формат даты начала или даты окончания включения в программу');
					return false;
				} else if ( (-1 == $compare_result[0]) || (0 == $compare_result[0]) ) {
					$this->ReturnError('Дата начала включения в программу должна быть меньше даты окончания');
					return false;
				}
			}

			$response = $this->dbmodel->savePrivilegeConsent($data);
			$this->ProcessModelSave($response, true, !empty($response['Error_Msg']) ? $response['Error_Msg'] : 'При сохранении произошла ошибка')->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	*  Получение списка льгот для человека
	*  Входящие данные: $_POST['Date'],
	*                   $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*                форма редактирования удостоверения льготника
	*/
	function loadPersonCategoryList() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonCategoryList', true, true);
		if ($data) {
			$response = $this->dbmodel->loadPersonCategoryList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	*  Экспорт данных о льготном лекарственном обеспечении детей-инвалидов в формате DBF
	*  Входящие данные: период, категория, территория
	*  На выходе: JSON-строка
	*  Используется: форма экспорта для Министерства труда
	*/
	function exportForLaborDep()
	{
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');
		set_time_limit(0);

		$data = $this->ProcessInputData('exportForLaborDep', true);
		if ($data === false) { return false; }


		$DBF_file_schema = array(
			array( "FAM",		"C",	50,	0),
			array( "IM",		"C",	50,	0),
			array( "OT",		"C",	50,	0),
			array( "DR",		"D",	8,	0),
			array( "ADRES_R",	"C",	100,0),
			array( "DATE_LS",	"D",	8,	0),
			array( "SUM",		"N",	15,	2),
		);

		$out_dir = "ld_".time();
		if ( !file_exists(EXPORTPATH_LABOR) )
			mkdir( EXPORTPATH_LABOR );
		mkdir( EXPORTPATH_LABOR.$out_dir );

		$klareastat_list = array();
		$files = array();

		if ($data['KLArea_Name'] == 'Все' || $data['KLAreaStat_idEdit'] == 9999999) {
			$klareastat_list = $this->dbmodel->getKLAreaStatList($data);
		} else {
			$klareastat_list[0] = array('KLAreaStat_idEdit' => $data['KLAreaStat_idEdit'], 'KLArea_Name' => $data['KLArea_Name']);
		}

		if (!empty($klareastat_list) && count($klareastat_list) > 0 ) {
			foreach ($klareastat_list as $key => $value) {
				$data['KLAreaStat_idEdit'] = $value['KLAreaStat_idEdit'];

				$File_name = "DETI_".$value['KLArea_Name'].".dbf";
				$File_path = EXPORTPATH_LABOR.$out_dir."/".$File_name;
				array_push($files, array('File_path' => $File_path, 'File_name' => $File_name));
				$h = dbase_create( $File_path, $DBF_file_schema );
				$result = $this->dbmodel->getLaborDepExportData($data);
				if (is_object($result)) {
					//$result->_data_seek(0);
					while ($row = $result->_fetch_assoc()) {
						array_walk($row, 'ConvertFromUTF8ToCp866');
						dbase_add_record( $h, array_values($row) );
					}
				}
				dbase_close ($h);
			}
		}

		// запаковываем
		$zip = new ZipArchive();
		$file_zip_name = EXPORTPATH_LABOR.$out_dir."/LaborDep.zip";
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		foreach ($files as $row) {
			ConvertFromUtf8ToCp866($row['File_name']);
			$zip->AddFile( $row['File_path'], $row['File_name'] );
		}
		$zip->close();

		foreach ($files as $roww) {
			unlink($roww['File_path']);
		}


		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true, 'filename' => $file_zip_name));
		}
		else
		{
			$this->ReturnError('Ошибка создания архива экспорта');
		}

		return true;
	}

	/**
	 * Проверка есть ли у человека действующая льгота данного типа
	 */
	function checkPersonPrivilege() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('checkPersonPrivilege');
		if (!$data) {
			return false;
		}

		$response = $this->dbmodel->CheckPersonPrivilege($data);

		if (is_array($response)) {
			$response = array(array(
				'success' => true,
				'check' => $response[0]['Privilege_Count'] > 0 ? true : false
			));
		}

		$this->ProcessModelSave($response, true, 'При проверке льгот произошла ошибка')->ReturnData();
		return true;
	}

	/**
	 * Проверка есть ли у человека действующая льгота на текущую дату
	 */
	function checkPersonPrivilegeExists() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('checkPersonPrivilegeExists');
		if (!$data) {
			return false;
		}

		$response = $this->dbmodel->checkPersonPrivilegeExists($data);

		if (is_array($response)) {
			$response = array(array(
				'success' => true,
				'check' => $response[0]['Privilege_Count'] > 0 ? true : false
			));
		}

		$this->ProcessModelSave($response, true, 'При проверке льгот произошла ошибка')->ReturnData();
		return true;
	}

	/**
	*	Проверка прикрепления для возможности добавить льготу для Крыма для задачи https://redmine.swan.perm.ru/issues/104566
	*/
	function checkPersonCard() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');
		$data = $this->ProcessInputData('checkPersonCard');
		if($data == false)
			return false;
		$response = $this->dbmodel->checkPersonCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Выгрузка данных для сверки с ПФР (валидация)
	 */
	public function exportPFRValidationData() {
		set_time_limit(60 * 60);

		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('exportPFRValidationData');
		if ( $data === false ) { return false; }

		if ( !isSuperadmin() ) {
			echo 'В доступе отказано';
			return false;
		}

		$this->load->library('parser');

		$response = $this->dbmodel->getPFRValidationDataForExport($data);

		if ( $response === true ) {
			echo 'Экспорт успешно завершен';
		}
		else if ( $response === false ) {
			echo 'Ошибка экспорта';
		}
		else {
			echo $response;
		}

		return true;
	}

	/**
	 * Выгрузка данных для сверки с ПФР (идентификация)
	 */
	public function exportPFRIdentificationData() {
		set_time_limit(60 * 60);

		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('exportPFRIdentificationData');
		if ( $data === false ) { return false; }

		if ( !isSuperadmin() ) {
			echo 'В доступе отказано';
			return false;
		}

		$this->load->library('parser');

		$response = $this->dbmodel->getPFRIdentificationDataForExport($data);

		if ( $response === true ) {
			echo 'Экспорт успешно завершен';
		}
		else if ( $response === false ) {
			echo 'Ошибка экспорта';
		}
		else {
			echo $response;
		}

		return true;
	}

	/**
	 * Формирование и передача данных для ЕГИССО
	 */
	function createEgissoData() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('createEgissoData', false);
		if (!$data) {
			return false;
		}

		$response = $this->dbmodel->createEgissoData($data);

		$this->ProcessModelSave($response, true, 'При проверке льгот произошла ошибка')->ReturnData();
		return true;
	}

	/**
	 * Сохранение типа документа о праве на льготу
	 */
	function saveDocumentPrivilegeType() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('saveDocumentPrivilegeType', false);
		if ($data) {
			$response = $this->dbmodel->saveDocumentPrivilegeType($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение запроса на включение в региональный регистр льготников
	 */
	function savePersonPrivilegeReq() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('savePersonPrivilegeReq', false);
		if ($data) {
			$response = $this->dbmodel->savePersonPrivilegeReq($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение запроса на включение в региональный регистр льготников (режим постмодерации)
	 */
	function savePersonPrivilegeReqPM() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('savePersonPrivilegeReq', false);
		if ($data) {
			$response = $this->dbmodel->savePersonPrivilegeReqPM($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение результата экспертизы запроса на включение в региональный регистр льготников
	 */
	function savePersonPrivilegeReqExpertise() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('savePersonPrivilegeReqExpertise', false);
		if ($data) {
			$response = $this->dbmodel->savePersonPrivilegeReqExpertise($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение результата экспертизы запроса на включение в региональный регистр льготников (режим постмодерации)
	 */
	function savePersonPrivilegeReqExpertisePM() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('savePersonPrivilegeReqExpertise', false);
		if ($data) {
			$response = $this->dbmodel->savePersonPrivilegeReqExpertisePM($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление= запроса на включение в региональный регистр льготников
	 */
	function deletePersonPrivilegeReq() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('deletePersonPrivilegeReq', false);
		if ($data) {
			$response = $this->dbmodel->deletePersonPrivilegeReq($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных запроса на включение в региональный регистр льготников
	 */
	function loadPersonPrivilegeReq() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonPrivilegeReq', false);
		if ($data) {
			$response = $this->dbmodel->loadPersonPrivilegeReq($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка запросов на включение в региональный регистр льготников
	 */
	function loadPersonPrivilegeReqList() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonPrivilegeReqList', false);
		if ($data) {
			$response = $this->dbmodel->loadPersonPrivilegeReqList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных о фамилии при рождении
	 */
	function loadPersonSurNameAtBirth() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonSurNameAtBirth', false);
		if ($data) {
			$response = $this->dbmodel->loadPersonSurNameAtBirth($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadDocumentPrivilegeTypeCombo() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadDocumentPrivilegeTypeCombo',false);
		if ($data) {
			$response = $this->dbmodel->loadDocumentPrivilegeTypeCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadDiagByPrivilegeTypeCombo() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadDiagByPrivilegeTypeCombo',false);
		if ($data) {
			$response = $this->dbmodel->loadDiagByPrivilegeTypeCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadPersonPrivilegeReqMedStaffFactCombo() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonPrivilegeReqMedStaffFactCombo',false);
		if ($data) {
			$response = $this->dbmodel->loadPersonPrivilegeReqMedStaffFactCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadPersonSurNameCombo() {
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonSurNameCombo',false);
		if ($data) {
			$response = $this->dbmodel->loadPersonSurNameCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * Проверка наличия у пациента региональной льготы
	 */
	function checkPersonHaveActiveRegionalPrivilege(){
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('checkPersonHaveActiveRegionalPrivilege', false);
		$response = $this->dbmodel->CheckPersonHaveActiveRegionalPrivilege($data);
		
		if (is_array($response)) {
			$response = array(array(
				'success' => true,
				'check' => ($response[0]['Privilege_Count'] > 0) ? true : false
			));
		}

		$this->ProcessModelSave($response, true, 'При проверке льгот произошла ошибка')->ReturnData();
		return true;
	}

	/*
	 * Проверка наличия у пациента основного или службеного прикрепления к МО
	 */
	function checkPrivilegeMainOrServiceAttachment(){
		$this->load->database();
		$this->load->model('Privilege_model', 'dbmodel');

		$data = $this->ProcessInputData('checkPrivilegeMainOrServiceAttachment');

		$response = $this->dbmodel->checkPrivilegeMainOrServiceAttachment($data);

		if (is_array($response)) {
			$response = array(array(
				'success' => true,
				'isAttachment' => $response[0]['cnt'] > 0 ? true : false
			));
		}

		$this->ProcessModelSave($response, true, 'При проверке прикрепления произошла ошибка')->ReturnData();
		return true;
	}


}
