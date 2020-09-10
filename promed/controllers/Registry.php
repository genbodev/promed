<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
 * @property Registry_model dbmodel
*/
class Registry extends swController {
	var $dbgroup = "registry";
	var $scheme = "dbo";
	var $model_name = "Registry_model";
	var $error_deadlock = "При обращении к базе данных произошла ошибка.<br/>Скорее всего данная ошибка вызвана повышенной нагрузкой на сервер. <br/>Повторите попытку, и, если ошибка появится вновь - <br/>свяжитесь с технической поддержкой.";
	public $file_log = 'registry.log';
	public $file_log_access = 'a';

	/**
	 *	Запись в лог
	 */
	function writeLog($string) {
		if (false) // выключить
		{
			$f = fopen($this->file_log, $this->file_log_access);
			fputs($f, $string);
			fclose($f);
		}
	}

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		if ( !isset($_GET['m']) || $_GET['m'] != 'importRegistrySmoDataFromDbf' )
		{
			// Инициализация класса и настройки
			if ($this->usePostgreRegistry) {
				$this->load->database('postgres', false);
			}else{
				$this->load->database($this->dbgroup, false);
			}
			//Выставляем таймауты для выполнения запросов, пока вручную
			//$this->db->query_timeout = 600;
			$this->load->model($this->model_name, 'dbmodel');
		}

		$this->inputRules = array(
			'loadRegistryCacheList' => array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'begDT', 'label' => 'Начало периода', 'rules' => '', 'type' => 'date'),
				array('field' => 'endDT', 'label' => 'Окончание периода', 'rules' => '', 'type' => 'date'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
			),
			'loadRegistryHistoryList' => array(
				array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
			),
			'loadRegistryEntiesGrid' => array(
				array(
					'field' => 'FLKSettings_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadRegistryEntiesForm' => array(
				array(
					'field' => 'FLKSettings_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'saveRegistryEntries' => array(
				array('field' => 'RegistryFileCase', 'label' => 'Шаблон файла со случаями', 'rules' => '', 'type' => 'string'),
				array('field' => 'RegistryFilePersonalData', 'label' => 'Шаблон файла с персональными данными', 'rules' => '', 'type' => 'string'),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryGroupType_id',
					'label' => 'Тип объединенного реестра',
					'type' => 'id'
				),
				array(
					'field' => 'FLKSettings_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'FLKSettings_begDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'FLKSettings_endDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
			),
			'correctErrors' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'offset',
					'label' => 'Смещение',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'deleteRegistryErrorTFOMS' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorTFOMS_id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveRegistryErrorTFOMS' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор случая',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorType_id',
					'label' => 'Ошибка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorTFOMSLevel_id',
					'label' => 'Уровень ошибки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorType_Code',
					'label' => 'Код ошибки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorTFOMS_FieldName',
					'label' => 'Имя поля',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorTFOMS_BaseElement',
					'label' => 'Базовый элемент',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorTFOMS_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRegistryHealDepResErrGrid' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_FIO',
					'label' => 'ФИО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryHealDepErrorType_Code',
					'label' => 'Код ошибки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryErrorTFOMS' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'Person_FIO',
						'label' => 'ФИО',
						'rules' => '',
						'type' => 'string'
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
						'label' => 'Отчество',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'LpuBuilding_id',
						'label' => 'Подразделение',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RegistryErrorType_Code',
						'label' => 'Код ошибки',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RegistryErrorClass_id',
						'label' => 'Вид ошибки',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RegistryErrorStageType_id',
						'label' => 'Тип стадии',
						'rules' => '',
						'type' => 'id'
					),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorTFOMS_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorType_Descr',
					'label' => 'Описание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'loadHistory',
					'label' => 'Признак загрузки истории ошибок',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_CheckStatusDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadRegistryErrorTFOMSFilter' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_FIO',
					'label' => 'ФИО',
					'rules' => '',
					'type' => 'string'
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
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorType_Code',
					'label' => 'Код ошибки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorClass_id',
					'label' => 'Вид ошибки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorStageType_id',
					'label' => 'Тип стадии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorTFOMS_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryErrorBDZ' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryErrorBDZType_id',
					'label' => 'Тип ошибки БДЗ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_FIO',
					'label' => 'ФИО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryDouble' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
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
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'filterIsZNO',
					'label' => 'ЗНО',
					'rules' => '',
					'type' => 'int'
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
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRegistryDoublePL' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
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
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryErrorType' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'dotted',
						'label' => '',
						'rules' => '',
						'type' => 'int'
					)
			),
			'loadRegistryQueue' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
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
			'exportRegistryToDbf' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'onlyLink',
						'label' => 'Флаг вывода только ссылки',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RegistryType_id',
						'label' => 'Тип реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'send',
						'label' => 'Флаг',
						'rules' => '',
						'type' => 'string'
					)
			),
			'exportOnko' => array(
				array(
					'field' => 'Registry_begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Registry_endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'exportRegistryToXml' => array(
				array(
						'field' => 'OverrideControlFlkStatus',
						'label' => 'Флаг пропуска контроля на статус Проведен контроль ФЛК',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'OverrideExportOneMoreOrUseExist',
						'label' => 'Флаг использования существующего или экспорта нового XML',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'onlyLink',
						'label' => 'Флаг вывода только ссылки',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => 'required',
						'type' => 'id'
					),
				array(
						'field' => 'RegistryType_id',
						'label' => 'Тип реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'KatNasel_id',
						'label' => 'Категория населения',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'send',
						'label' => 'Флаг',
						'rules' => '',
						'type' => 'string'
				),
				array(
					'field' => 'forSign',
					'label' => 'Флаг',
					'rules' => '',
					'type' => 'int'
					)
			),
			'exportRegistryToXmlCheckExist' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryData' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field'	=> 'RegistryData_RowNum',
					'label'	=> 'Номер записи',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
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
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
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
						'label' => 'Отчество',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Polis_Num',
						'label' => 'Полис',
						'rules' => '',
						'type' => 'string'
					),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'RegistryStatus_id',
                    'label' => 'Статус реестра',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'filterRecords',
                    'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
                    'rules' => '',
					'default' => 1,
                    'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'sort',
					'label' => 'Поле для сортировки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'dir',
					'label' => 'Направление сортировки',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'FilterGridPanel' => array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 1000,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRegistryDataFilter' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
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
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
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
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Polis_Num',
					'label' => 'Полис',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'Статус реестра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRegistryErrorComFilter' => [
				[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
				[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
				[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
				[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
				[ 'field' => 'Filter', 'label' => 'Строка с параметрами фильтрации', 'rules' => '', 'type' => 'string' ],
			],
			'loadRegistryDataPaid' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryErrorCom' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				)
			),
			'printRegistryError' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				)
			),
			'printRegistryData' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadRegistryError' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
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
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
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
						'label' => 'Отчество',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RegistryError_Code',
						'label' => 'Код ошибки',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RegistryErrorType_id',
						'label' => 'Ошибка',
						'rules' => '',
						'type' => 'id'
					),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'sort',
					'label' => 'Поле для сортировки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'dir',
					'label' => 'Направление сортировки',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadRegistryErrorFilter' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
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
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
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
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryError_Code',
					'label' => 'Код ошибки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorType_id',
					'label' => 'Ошибка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryNoPolis' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RegistryType_id',
						'label' => 'Идентификатор типа реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'sort',
					'label' => 'Поле для сортировки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'dir',
					'label' => 'Направление сортировки',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadRegistryNoPolisFilter' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Идентификатор типа реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Строка с параметрами фильтрации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryCheckStatusHistory' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryNoPay' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRegistryPerson' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'reformRegistry' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_IsNew',
					'label' => 'Признак новых реестров',
					'rules' => '',
					'default' => null,
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				),
			),
			'deletePersonEdit' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор записи реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'default' => '',
					'field' => 'object',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				)
			),
			'reformErrRegistry' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'setNeedReform' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор записи реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuOidList' => array(

			),
			'loadRegistry' => array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
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
						'field' => 'RegistryType_id',
						'label' => 'Тип реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RegistrySubType_id',
						'label' => 'Подтип реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RegistryStacType_id',
						'label' => 'Тип реестра стационара',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RegistryEventType_id',
						'label' => 'Тип случаев реестра',
						'rules' => '',
						'type' => 'id'
				),
				array(
						'field' => 'RegistryStatus_id',
						'label' => 'Статус реестра',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'Registry_accYear',
						'label' => 'год',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Registry_IsNew',
						'label' => 'Признак новых реестров',
						'rules' => '',
						'default' => null,
						'type' => 'id'
				),
				array(
					'field' => 'PayType_SysNick',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'string'
					)
			),
			'saveRegistry' => array(
				array(
					'default' => null,
					'field' => 'DispClass_id',
					'label' => 'Тип дисп-ции/медосмотра:',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => null,
					'field' => 'KatNasel_id',
					'label' => 'Категория населения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => null,
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => Null,
					'field' => 'LpuFilial_id',
					'label' => 'Филиал',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => Null,
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => Null,
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_Num',
					'label' => 'Номер счета',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'Статус реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryStacType_id',
					'label' => 'Тип реестра стационара',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryEventType_id',
					'label' => 'Тип случаев реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_IsActive',
					'label' => 'Признак активного регистра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgRSchet_id',
					'label' => 'Расчетный счет',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_mid',
					'label' => 'Организация МВД',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgRSchet_mid',
					'label' => 'Расчетный счет организации МВД',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_accDate',
					'label' => 'Дата счета',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Registry_begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Registry_endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Registry_IsNew',
					'label' => 'Признак новых реестров',
					'rules' => '',
					'default' => null,
					'type' => 'id'
				),
				array(
					'field' => 'Registry_IsRepeated',
					'label' => 'Повторная подача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgSMO_id',
					'label' => 'СМО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_IsZNO',
					'label' => 'ЗНО',
					'rules' => '',
					'type' => 'id'
				)
			),
		'setRegistryStatus' => array(
				array(
					'default' => null,
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'Статус реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'is_manual',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
				'field' => 'CheckingDuplicates',
				'label' => 'Признак игнорирования дублей посещения',
				'rules' => '',
				'type' => 'id'
			)
			),
		'setRegistryActive' => array(
				array(
					'default' => null,
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Registry_IsActive',
					'label' => 'Активность реестра',
					'rules' => '',
					'type' => 'id'
				)
			),
		'deleteRegistryData' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор записи в реестре',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_rid',
					'label' => 'Идентификатор родительского события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_ids',
					'label' => 'Идентификаторы записей в реестре',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryData_deleted',
					'label' => 'Признак удаления реестра',
					'rules' => '',
					'type' => 'int',
					'default' => 1
				)
			),
			'deleteRegistryDataAll' => array(
				array(
					'field' => 'type',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'filters',
					'label' => 'Список фильтров в JSON-формате',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryData_deleted',
					'label' => 'Признак удаления реестра',
					'rules' => '',
					'type' => 'int',
					'default' => 1
				)
			),
		'printRegistry' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор записи в реестре',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		'refreshRegistryData' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор записи в реестре',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				)
			),
		'deleteRegistryQueue' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				)
			),
		'savePersonEdit' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Сервер',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Посещение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'OMSSprTerr_id',
					'label' => 'Территория',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgSMO_id',
					'label' => 'СМО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Polis_Ser',
					'label' => 'Серия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Polis_Num',
					'label' => 'Номер',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PolisType_id',
					'label' => 'Тип полиса',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		'getPolisTypes' => array(
		),
		'getPersonEdit' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Посещение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Сервер',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'deleteRegistry' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteRegistryFLK' => array(
				array(
					'field' => 'FLKSettings_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryHealDepErrorTypeGrid' => array(
				array(
					'field' => 'RegistryHealDepErrorType_Code',
					'label' => 'Код',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Тип фильтрации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'doRegistryPersonIsDifferent' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MaxEvnPerson_id',
					'label' => 'Идентификатор строки в RegistryPerson',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'doPersonUnionFromRegistry' => array(
				array(
					'field' => 'Records',
					'label' => 'json-стркоа Records',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'fromRegistry',
					'label' => 'Флаг fromRegistry',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MaxEvnPerson_id',
					'label' => 'Идентификатор строки в RegistryPerson',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteRegistryDouble' => array(
				array(
					'field' => 'mode',
					'label' => '',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_id',
					'label' => 'Реестр',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getYearsList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryStacType_id',
					'label' => 'Тип реестра стационара',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryEventType_id',
					'label' => 'Тип случаев реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'Статус реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_SysNick',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'string'
				),
			),
			'getOrgSMOListForExportRegistry' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Реестр',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'setRegistryDataPaidFromJSON' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Реестр',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'Статус реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryDataPaid',
					'label' => 'Отметки об оплате случаев',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadRegistryMzTree' => array(
				array(
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'node',
					'label' => 'Узел',
					'rules' => '',
					'type' => 'string'
				)
			),
			'deleteRegistryHealDepErrorType' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор ошибки МЗ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveRegistryHealDepErrorType' => array(
				array(
					'field' => 'RegistryHealDepErrorType_id',
					'label' => 'Идентификатор ошибки МЗ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryHealDepErrorType_Code',
					'label' => 'Код',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryHealDepErrorType_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryHealDepErrorType_Descr',
					'label' => 'Описание',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryHealDepErrorType_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'RegistryHealDepErrorType_endDate',
					'label' => 'Дата окончания',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'loadRegistryHealDepErrorTypeEditWindow' => array(
				array(
					'field' => 'RegistryHealDepErrorType_id',
					'label' => 'Идентификатор ошибки МЗ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryMzGrid' => array(
				array(
					'field' => 'Status_SysNick',
					'label' => 'Статус',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'filterLpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryHealDepCheckJournal_sendHDDT_Range',
					'label' => 'Дата отправки',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'RegistryHealDepCheckJournal_sendDT_Range',
					'label' => 'Дата перевода на проверку',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'RegistryHealDepCheckJournal_endCheckDT_Range',
					'label' => 'Дата проверки',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'Registry_begDate_Range',
					'label' => 'Начало отчётного периода',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'Registry_endDate_Range',
					'label' => 'Окончание отчётного периода',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getRegistryDataMzGridCounters' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegistryDataMzGrid' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryHealDepResType_id',
					'label' => 'Результат проверки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryHealDepErrorType_id',
					'label' => 'Ошибка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'getIdsOnly',
					'label' => 'Признак получения иденификаторов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareBudgType_id',
					'label' => 'Тип мед. помощи',
					'rules' => '',
					'type' => 'int'
				)
			),
			'setRegistryMzCheckStatus' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryCheckStatus_SysNick',
					'label' => 'Статус',
					'rules' => '',
					'type' => 'string'
				)
			),
			'acceptRegistryMz' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Действие с непроверенными случаями',
					'rules' => '',
					'type' => 'string'
				)
			),
			'processRegistryDataMz' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_ids',
					'label' => 'Список случаев',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'RegistryHealDepErrorType_id',
					'label' => 'Ошибка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Действие со случаями',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRegistryHealDepErrorTypeCombo' => array(

			),
			'sendRegistryToMZ' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'signRegistry' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'documentSigned',
					'label' => 'Подпись',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'checkRegistryXmlExportExists' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);

		$this->inputRules['Filter'] =  [
			[ 'field' => 'Filter', 'label' => 'Строка с параметрами фильтрации', 'rules' => '', 'type' => 'string' ],
		];

		$this->inputRules['FilterGridPanel'] = [
			[ 'field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int' ],
		];

		$this->inputRules['loadRegistryDouble'] = array_merge(
			$this->inputRules['loadRegistryDouble'],
			$this->inputRules['Filter']
		);

		$this->inputRules['loadRegistryDoubleFilter'] = array_merge(
			$this->inputRules['FilterGridPanel'],
			$this->inputRules['loadRegistryDouble']
		);
	}


	/**
	*  Функция возвращает запись/записи реестра
	*  Входящие данные: _POST['Registry_id'],
	*  На выходе: JSON-строка
	*  Используется: форма редактирования реестра (счета)
	*/
	function loadRegistry()
	{
		$data = $this->ProcessInputData('loadRegistry', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistry($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	*  Функция возвращает список структурных подразделения
	*/
	function getLpuOidList()
	{
		$data = $this->ProcessInputData('getLpuOidList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuOidList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*	Возвращает список годов, в которых есть созданные реестры
	*/
	function getYearsList()
	{
		$data = $this->ProcessInputData('getYearsList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getYearsList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Функция получает позицию реестра в очереди для определенного ЛПУ
	*  Входящие данные:
	*  _POST['Registry_id'] необязательное поле, для определения позиции конкретного реестра,
	*  _POST['Lpu_id'] необязательное поле, берется из сессии в случае отсутствия в _POST
	*  На выходе: JSON-строка
	*  Используется: форма просмотра/редактирования реестра (счета)
	*/
	function loadRegistryQueue()
	{
		$data = $this->ProcessInputData('loadRegistryQueue', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryQueue($data);
		$outdata = $this->ProcessModelList($response, true, true)->GetOutData();
		if ($outdata) {
			$this->ReturnData($outdata[0]);
		}
	}

	/**
	 * Отправка реестра по почте
	 */
	function sendMail($file, $data)
	{
		// Настройки для отправки
		$email = array(
			1=>'cosperm@tfoms.perm.ru', // polka пермь // stac пермь
			2=>'cosrf@tfoms.perm.ru', // polka край // stac край
			3=>'markov@swan.perm.ru', // я
			4=>'dopdisp@tfoms.perm.ru' // dd
		);

		$this->load->library('email');

		$data = array_merge($data, getSessionParams());

		$r = $this->dbmodel->loadRegData($data);
		if ( is_array($r) && count($r) > 0 )
		{
			$Lpu_Email = $r[0]['Lpu_Email'];
			$Lpu_Nick = $r[0]['Lpu_Nick'];
			$Registry_Num = $r[0]['Registry_Num'];
			$KatNasel_id = $r[0]['KatNasel_id'];
			$RegistryType_id = $r[0]['RegistryType_id'];
			if ((empty($KatNasel_id)) && ($RegistryType_id == 4))
			{
				$KatNasel_id = 4;
			}
			if ((empty($KatNasel_id)) && ($RegistryType_id == 5))
			{
				$KatNasel_id = 4;
			}
			/* // Если когда то адреса для получения реестров будут отличаться, то достаточно будет открыть этот код и в массив дописать новые адреса (с) Night
			if ($RegistryType_id == 1) // Стац
			{
				if ($KatNasel_id = 1)
				{
					$KatNasel_id = 5;
				}
				else
				{
					$KatNasel_id = 6;
				}
			}
			*/
		}
		else
		{
			return false;
		}

		$to_email = $email[$KatNasel_id];
		$subject = "Реестр № ".$Registry_Num." от ЛПУ: ".$Lpu_Nick;
		$message = "Автоматическая отправка реестра № ".$Registry_Num." от ЛПУ ".$Lpu_Nick.chr(13).chr(10)."Сгенерировано: ".date("d.m.Y H:i:s");
		$this->email->attach($file);
		$this->email->bcc($email[3]);
		$resultsend = $this->email->sendPromed($to_email, $subject, $message, $Lpu_Nick);
		return $resultsend;
	}
	
	/**
	 * Удаление помеченных на удаление записей и пересчет реестра
	 */
	function refreshRegistry($data)
	{
		$this->load->model('Registry_model', 'dbmodel');

		$response = $this->dbmodel->refreshRegistry($data);
		if ($response===false) {
			return array('Error_Msg' => 'Пересчет реестра невозможен, обратитесь к разработчикам.');
		} elseif (isset($response[0]) && (strlen($response[0]['Error_Msg']) != 0)) {
			return $response[0];
		} else {
			return true;
		}
	}
	
	/**
	 * Удаление помеченных на удаление записей и пересчет реестра, вызов с формы
	 */
	function refreshRegistryData()
	{
		$data = $this->ProcessInputData('refreshRegistryData', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink($data)) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}
		$response = $this->refreshRegistry($data);
		if (is_array($response)) {
			$result = array('success' => false, 'Error_Msg' => toUTF($response['Error_Msg']));
		} else {
			$result = array('success' => true);
		}
		$this->ReturnData($result);
		return true;
	}
	
	/**
	 * Функция формирует и выводит в поток вывода .dbf файлы, обернутые в архив.
	 * @input $_POST (Registry_id)
	 * @task https://redmine.swan.perm.ru/issues/40204
	 */
	function exportRegistryToDbf() {
		$data = $this->ProcessInputData('exportRegistryToDbf', true);
		if ($data === false) { return false; }

		$this->load->library('textlog', array('file'=>'exportRegistryToDbf_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToDbf: Запуск формирования реестра (Registry_id=' . $data['Registry_id'] . ')');

		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToDbf: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.'));
			return false;
		}

		$type = $res[0]['RegistryType_id'];
		$this->textlog->add('exportRegistryToDbf: Тип реестра ' . $res[0]['RegistryType_id']);

		$response = $this->dbmodel->loadRegistryDataForDbfExport($data, $type);

		if ( !is_array($response) || count($response) == 0 || !array_key_exists('data', $response) || !is_array($response['data']) ) {
			$this->textlog->add('exportRegistryToDbf: Ошибка: Произошла ошибка при получении данных реестра. Сообщите об ошибке разработчикам.');
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Произошла ошибка при получении данных реестра. Сообщите об ошибке разработчикам.'));
			return false;
		}
		else if ( count($response['data']) == 0 ) {
			$this->textlog->add('exportRegistryToDbf: Отсутствуют данные для выгрузки.');
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Отсутствуют данные для выгрузки.'));
			return false;
		}

		$out_dir = "re_" . time() . "_" . $data['Registry_id'];
		mkdir(EXPORTPATH_REGISTRY . $out_dir);
		$files = array();

		$registry_def = array();

		foreach ( $response['data'][0] as $key => $value ) {
			$registry_def[] = array($key, "C", "255", "0");
		}

		$fname = "M" . $response['header'][0]['CODE_MO'] . "T" . $data['session']['region']['number'] . "_" . date('ym');

		$file_re_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $fname . ".dbf";
		$files[$fname . ".dbf"] = $file_re_name;
		$h = dbase_create($file_re_name, $registry_def);

		foreach ( $response['data'] as $row ) {
			array_walk($row, 'ConvertFromUtf8ToCp866');
			dbase_add_record($h, array_values($row));
		}

		dbase_close($h);

		$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/registry_dbf.zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

		foreach ( $files as $key => $value ) {
			$zip->AddFile($value, $key);
		}

		$zip->close();

		foreach ( $files as $key => $value ) {
			unlink($value);
		}

		if ( file_exists($file_zip_name) ) {
			$link = $file_zip_name;
			echo "{'success':true,'Link':'$link'}";
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка создания архива реестра!'));
		}
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */	
	function exportRegistryToXmlCheckExist()
	{
		$data = $this->ProcessInputData('exportRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }
		
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		if (is_array($res) && count($res) > 0) {
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath'])>0 && $res[0]['RegistryStatus_id'] != 4) // если уже выгружен реестр и не оплаченный
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath'])>0 && $res[0]['RegistryStatus_id'] == 4) // если уже выгружен реестр и оплаченный
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;			
			} else {
                $this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
                return true;
            }
		} else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Корректировка ошибок на реестрах со смещением
	 */
	function correctErrors() {
		$data = $this->ProcessInputData('correctErrors', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->correctErrors($data);
		$this->ProcessModelSave($response, true, 'Ошибка корректировки ошибок')->ReturnData(); // масло масляное :)

		return true;
	}
	
	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXml()
	{
	
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }
		/* тест */ 
		/*
		$data = array_merge($data, getSessionParams());
		$out_dir = "re_xml_1111111_";
		@mkdir( EXPORTPATH_REGISTRY.$out_dir );
		$file_zip_sign = 'HM5705003T59_11091';
		$file_re_data_sign = "HM5705003T59_11091";
		$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
		$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";
		
		$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/r1.xml";
		$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/l1.xml";
		$zip=new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_re_data_name, "r1" . ".xml" );
		$zip->AddFile( $file_re_pers_data_name, "l1" . ".xml" );
		$zip->close();
		if (file_exists($file_zip_name))
		{
			echo "{'success':true,'Link':'{$file_zip_name}'}";
		}
		else{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
		}
		return false;
		*/
		/*if (!isSuperadmin()) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Выгрузка и отправка в XML временно недоступна!')));
			return false;
		}*/
		
		if ( isset($data['Registry_id']) && trim($data['Registry_id']) > 0 )
		{
			$this->load->library('textlog', array('file'=>'exportRegistryToXml.log'));
			$this->textlog->add('');
			$this->textlog->add('exportRegistryToXml: Запуск');
			
			// Определяем надо ли при успешном формировании проставлять статус и соответсвенно не выводить ссылки
			if (!isset($data['send']))
				$data['send'] = 0;
			
			$type = 0;
			// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
			
			$this->textlog->add('exportRegistryToXml: GetRegistryXmlExport: Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр');
			$res = $this->dbmodel->GetRegistryXmlExport($data);
			$this->textlog->add('exportRegistryToXml: GetRegistryXmlExport: Проверка закончена');
			if (is_array($res) && count($res) > 0)
			{
				if (!empty($res[0]['RegistryCheckStatus_id'])) {
					$data['RegistryCheckStatus_id'] = $res[0]['RegistryCheckStatus_id'];
				}
				
				// Запрет отправки в ТФОМС реестра "Проведён контроль ФЛК"
				if (!isSuperAdmin() && $data['send'] == 1 && $res[0]['RegistryCheckStatus_id'] === '5') {
					$this->textlog->add('exportRegistryToXml: Выход с сообщением: При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС.');
					$this->ReturnError('При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС');
					return;
				}
				
				// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
				if (!empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2) {
					$this->textlog->add('exportRegistryToXml: Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.')));
					return;
				}
				
				// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
				if (!empty($res[0]['Registry_SumDifference'])) {
					$this->textlog->add('exportRegistryToXml: Выход с сообщением: Неверная сумма по счёту и реестрам.');
					// добавляем ошибку
					// $data['RegistryErrorType_Code'] = 3;
					// $res = $this->dbmodel->addRegistryErrorCom($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Экспорт невозможен. Неверная сумма по счёту и реестрам.'), 'Error_Code' => '12'));
					return;
				}
				
				// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
				if (empty($res[0]['RegistryData_Count'])) {
					$this->textlog->add('exportRegistryToXml: Выход с сообщением: Нет записей в реестре.');
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Экспорт невозможен. Нет случаев в реестре.'), 'Error_Code' => '13'));
					return;
				}
				
				$this->textlog->add('exportRegistryToXml: Получили путь из БД:'.$res[0]['Registry_xmlExportPath']);
				if ($data['send'] == 1) {
					if (($res[0]['RegistryCheckStatus_Code'] == 4) && (empty($data['OverrideControlFlkStatus']))) {
						$this->textlog->add('exportRegistryToXml: Выход с вопросом: Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?');
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?'), 'Error_Code' => '10'));
						return;
					}
					
					$data['OverrideExportOneMoreOrUseExist'] = 1;
				}
				
				if ($res[0]['Registry_xmlExportPath'] == '1')
				{
					$this->textlog->add('exportRegistryToXml: Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).')));
					return;
				}
				elseif (strlen($res[0]['Registry_xmlExportPath'])>0) // если уже выгружен реестр
				{
					$this->textlog->add('exportRegistryToXml: Реестр уже выгружен');

					if (empty($data['OverrideExportOneMoreOrUseExist'])) {
						$this->textlog->add('exportRegistryToXml: Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)'), 'Error_Code' => '11'));
						return;
					} elseif ($data['OverrideExportOneMoreOrUseExist'] == 1) {
						// Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено 
						if ($data['send']==1) {
							$this->textlog->add('exportRegistryToXml: Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено ');
							if (($res[0]['RegistryCheckStatus_id']>0) && (!in_array($res[0]['RegistryCheckStatus_Code'],array(4,6)))) {
								$this->textlog->add('exportRegistryToXml: Выход с сообщением: Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра невозможна. ');
								$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра невозможна. ')));
								return;
							} else {
								// Хотим отправить уже сформированный реестр 
								$data['RegistryCheckStatus_id'] = 1;
								$data['Status'] = $res[0]['Registry_xmlExportPath'];
								$this->dbmodel->SetXmlExportStatus($data);
								$this->textlog->add('exportRegistryToXml: SetXmlExportStatus: Отправка сформированного реестра. Путь: '.$res[0]['Registry_xmlExportPath']);
								$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id']));
								return;
							}
						}
						$link = $res[0]['Registry_xmlExportPath'];
						$usePrevXml = '';
						if (empty($data['onlyLink'])) {
							$usePrevXml = 'usePrevXml: true, ';
						}
						echo "{'success':true, $usePrevXml'Link':'$link'}";
						$this->textlog->add('exportRegistryToXml: Выход с передачей ссылкой: '.$link);
						return;
					} else {
						// https://redmine.swan.perm.ru/issues/48575
						if (
							(!empty($res[0]['RegistryCheckStatus_Code']) || $res[0]['RegistryCheckStatus_Code'] == 0)
							&& !in_array($res[0]['RegistryCheckStatus_Code'],array(-1,2,5,6))
						) {
							$this->textlog->add('exportRegistryToXml: Выход с сообщением: Формирование и отправка файла реестра в ТФОМС невозможна, т.к. <br/>текущий статус реестра: '.(($res[0]['RegistryCheckStatus_Name']=='')?'Не отправлен':$res[0]['RegistryCheckStatus_Name']).'!');
							$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Формирование и отправка файла реестра в ТФОМС невозможна, т.к. <br/>текущий статус реестра: '.(($res[0]['RegistryCheckStatus_Name']=='')?'Не отправлен':$res[0]['RegistryCheckStatus_Name']).'!')));
							return false;
						}

						$type = $res[0]['RegistryType_id'];
					}
				}
				else 
				{
					$type = $res[0]['RegistryType_id'];
				}
				$data['PayType_SysNick'] = null; 
				// Если вернули тип оплаты реестра, то будем его использовать 
				if (isset($res[0]['PayType_SysNick'])) {
					$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
				}
				$this->textlog->add('exportRegistryToXml: Тип оплаты реестра: '.$data['PayType_SysNick']);
			}
			else 
			{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.')));
				return;
			}
			
			$this->textlog->add('exportRegistryToXml: refreshRegistry: Пересчитываем реестр');
			// Удаление помеченных на удаление записей и пересчет реестра 
			$refreshData = $this->refreshRegistry($data);
			if (is_array($refreshData)) {
				// выход с ошибкой
				$this->textlog->add('exportRegistryToXml: refreshRegistry: При обновлении данных реестра произошла ошибка.');
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('При обновлении данных реестра произошла ошибка.')));
				return;
			}
			$this->textlog->add('exportRegistryToXml: refreshRegistry: Реестр пересчитали');
			
			
			$this->textlog->add('exportRegistryToXml: Тип реестра: '.$type);
			
			// Формирование XML в зависимости от типа. 
			// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
			try
			{
				$data['Status'] = '1';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: SetXmlExportStatus: Установили статус реестра в 1');
				set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
				// RegistryData 
				$registry_data_res = $this->dbmodel->loadRegistryDataForXmlUsingCommon($type, $data);
				array_walk_recursive($registry_data_res, 'ConvertFromUTF8ToWin1251', true);
                //var_dump($registry_data_res);
				$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsingCommon: Выбрали данные');

				if ($registry_data_res === false)
				{
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('exportRegistryToXml: Выход с ошибкой дедлока');
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
					return false;
				}
				if ( empty($registry_data_res) )
				{
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('exportRegistryToXml: Выход с ошибкой: Данных по требуемому реестру нет в базе данных.');
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по требуемому реестру нет в базе данных.')));
					return false;
				}
				
				$this->textlog->add('exportRegistryToXml: Получили все данные из БД ');
				$this->load->library('parser');
				
				// каталог в котором лежат выгружаемые файлы
				$out_dir = "re_xml_".time()."_".$data['Registry_id'];
				mkdir( EXPORTPATH_REGISTRY.$out_dir );
				/*
				реестр по поликлинике по РФ: это архив в котором два файла: Реестр (PR_H.... .xml) +ПеРс данные (PR_L.... .xml)
				реестр по поликлинике по ПК: это архив в котором два файла: Реестр (P_H.... .xml) +ПеРс данные (P_L.... .xml)
				реестр по ДД по ПК: это архив в котором два файла: Реестр (D_H.... .xml) +ПеРс данные (D_L.... .xml)
				реестр по ДД-Сирот по ПК: это архив в котором два файла: Реестр (U_H.... .xml) +ПеРс данные (U_L.... .xml)
				*/
				$pk = ( !(isset($data['KatNasel_id']) && $data['KatNasel_id'] == 2) );
				switch ($type) {
					case 1: // stac
						$t = "S";
						// для стаца для ОВД другое наименование реестра (XX_HPiNiPpNp_YYMMN.XML)
						if (isset($data['PayType_SysNick']) && ($data['PayType_SysNick']=='ovd')) {
							$t = "O";
						}
						$rname = ($pk)?$t."_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1":"SR_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = ($pk)?$t."_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1":"SR_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 2: //polka
					case 16: //stom
						$t = "P";
						// для полки для ОВД другое наименование реестра (XX_HPiNiPpNp_YYMMN.XML)
						if (isset($data['PayType_SysNick']) && ($data['PayType_SysNick']=='ovd')) {
							$t = "O";
						}
						$rname = ($pk)?$t."_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1":"PR_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = ($pk)?$t."_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1":"PR_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;			
					case 4: //dd
						$rname = "D_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "D_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 5: //orp
						$rname = "U_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "U_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 6: //smp
						$t = "C";
						$rname = ($pk)?$t."_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1":$t."R_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = ($pk)?$t."_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1":$t."R_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 7: //dd
						$rname = "D" . (!$pk?'R':'') . "_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "D" . (!$pk?'R':'') . "_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 8: //dd
						$rname = "D_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "D_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 9: //orp
						$rname = "U_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "U_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 10: //orp
						$rname = "U_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "U_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 11: //orp
						$rname = "F" . (!$pk?'R':'') . "_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "F" . (!$pk?'R':'') . "_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 12: //teen inspection
						$rname = "G" . (!$pk?'R':'') . "_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "G" . (!$pk?'R':'') . "_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 14: //htm
						$rname = "T" . (!$pk?'R':'') . "_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "T" . (!$pk?'R':'') . "_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					case 15: //par
						$rname = "PU" . (!$pk?'R':'') . "_HM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						$pname = "PU" . (!$pk?'R':'') . "_LM".$registry_data_res['SCHET'][0]['CODE_MO']."T".$_SESSION['region']['number']."_".date('ym')."1";
						break;
					default:
						return false;
				}
				
				
				$file_re_data_sign = $rname;
				$file_re_pers_data_sign = $pname;
				// файл-тело реестра
				$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";
				// файл-перс. данные
				$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_pers_data_sign.".xml";

				$registry_data_res['SCHET'][0]['FILENAME'] = $file_re_data_sign;

				$rgn = ($_SESSION['region']['nick']!='perm')?'_'.$_SESSION['region']['nick']:'';

				// Разбиваем на части, ибо парсер не может пережевать большие объемы данных
				// https://redmine.swan.perm.ru/issues/44830
				$templ_header = "registry".$rgn."_pl_header";
				$templ_body = "registry".$rgn."_pl_body";
				$templ_footer = "registry".$rgn."_pl_footer";

				if (isset($data['PayType_SysNick']) && ($data['PayType_SysNick']=='ovd') && ($_SESSION['region']['nick']=='perm')) { // ОВД только для перми 
					$templ_header = "registry_pl_ovd_header";
					$templ_body = "registry_pl_ovd_body";
					$templ_footer = "registry_pl_ovd_footer";
				}

				// Заголовок
				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/' . $templ_header, $registry_data_res['SCHET'][0], true);
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($file_re_data_name, $xml);

				$registryData = array();
				$i = 0;

				foreach ( $registry_data_res['ZAP'] as $array ) {
					$i++;
					$registryData[] = $array;

					if ( count($registryData) == 1000 ) {
						$xml = $this->parser->parse('export_xml/' . $templ_body, array('ZAP' => $registryData), true);
						$xml = str_replace('&', '&amp;', $xml);

						file_put_contents($file_re_data_name, $xml, FILE_APPEND);

						unset($xml);

						$registryData = array();
					}
				}

				if ( count($registryData) > 0 ) {
					$xml = $this->parser->parse('export_xml/' . $templ_body, array('ZAP' => $registryData), true);
					$xml = str_replace('&', '&amp;', $xml);

					file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				}

				unset($registryData);

				$xml = $this->parser->parse('export_xml/' . $templ_footer, array(), true);
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);

				reset($registry_data_res);

				$registry_data_res['ZGLV'][0]['FILENAME1'] = $file_re_data_sign;
				$registry_data_res['ZGLV'][0]['FILENAME'] = $file_re_pers_data_sign;

				$templ_header = "registry".$rgn."_person_header";
				$templ_body = "registry".$rgn."_person_body";
				$templ_footer = "registry".$rgn."_person_footer";

				if (isset($data['PayType_SysNick']) && ($data['PayType_SysNick']=='ovd') && ($_SESSION['region']['nick']=='perm')) { // ОВД только для перми 
					$templ_header = "registry_person_ovd_header";
					$templ_body = "registry_person_ovd_body";
					$templ_footer = "registry_person_ovd_footer";
				}

				$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/' . $templ_header, $registry_data_res['ZGLV'][0], true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers);

				$personData = array();
				$i = 0;

				foreach ( $registry_data_res['PACIENT'] as $array ) {
					$i++;
					$personData[] = $array;

					if ( count($personData) == 1000 ) {
						$xml_pers = $this->parser->parse('export_xml/' . $templ_body, array('PACIENT' => $personData), true);
						$xml_pers = str_replace('&', '&amp;', $xml_pers);

						file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

						unset($xml_pers);

						$personData = array();
					}
				}

				if ( count($personData) > 0 ) {
					$xml_pers = $this->parser->parse('export_xml/' . $templ_body, array('PACIENT' => $personData), true);
					$xml_pers = str_replace('&', '&amp;', $xml_pers);

					file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

					unset($xml_pers);
				}

				unset($personData);

				$xml_pers = $this->parser->parse('export_xml/' . $templ_footer, array(), true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

				unset($registry_data_res);

				$base_name = $_SERVER["DOCUMENT_ROOT"]."/";

				$file_zip_sign = $file_re_data_sign;
				$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
				$this->textlog->add('exportRegistryToXml: Создали XML-файлы: ('.$file_re_data_name.' и '.$file_re_pers_data_name.')');
				$zip=new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
				$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
				$zip->close();
				$this->textlog->add('exportRegistryToXml: Упаковали в ZIP '.$file_zip_name);
				
				$PersonData_registryValidate = true;
				$EvnData_registryValidate = true;
				if(array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
					$upload_path = 'RgistryFields/';
					// если включена проверка ФЛК в параметрах системы
					// получим xsd шаблон для проверки
					$settingsFLK = $this->dbmodel->loadRegistryEntiesSettings($res[0]);
					if($settingsFLK && count($settingsFLK) > 0){
						//если запись найдена
						$settingsFLK = $settingsFLK[0];
						$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
						$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;	

						//Проверка со случаями
						if($tplEvnDataXSD){
							//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
							$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
							//путь к файлу XSD
							$fileEvnDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplEvnDataXSD;
							//Файл с ошибками					
							$validateEvnData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_EvnData_".$dirTpl.'.html';						
							if(file_exists($fileEvnDataXSD)) {
								$EvnData_registryValidate = $this->dbmodel->Reconciliation($file_re_data_name, $fileEvnDataXSD, 'file', $validateEvnData_err_file);
							}
						}
						//Проверка с персональными данными
						if($tplPersonDataXSD){
							//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
							$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
							//путь к файлу XSD
							$filePersonDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplPersonDataXSD;
							//Файл с ошибками					
							$validatePersonData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_PersonData_".$dirTpl.'.html';
							if(file_exists($filePersonDataXSD)) {
								$PersonData_registryValidate = $this->dbmodel->Reconciliation($file_re_pers_data_name, $filePersonDataXSD, 'file', $validatePersonData_err_file);
							}
						}
					}
				}

				if($PersonData_registryValidate) unlink($file_re_pers_data_name);
				if($EvnData_registryValidate) unlink($file_re_data_name);
				if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
				/*
				unlink($file_re_data_name);
				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
				*/
				
				if(!$PersonData_registryValidate || !$EvnData_registryValidate){
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
							<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
							<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
							<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
							<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
				}elseif (!$PersonData_registryValidate) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br>
							<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
							<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
							<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
				} elseif (!$EvnData_registryValidate) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p><br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
							<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
							<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
				}elseif (file_exists($file_zip_name))
				{
					$data['Status'] = $file_zip_name;
					// Если не только формируем, но и хотим отправить, то пишем статус = готов к отправке
					if ($data['send']==1) {
						$data['RegistryCheckStatus_id'] = 1;
					}
					$this->dbmodel->SetXmlExportStatus($data);
					/*
					header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
					header("Content-Type: text/html");
					header("Pragma: no-cache");
					*/
					if ($data['send']==1) {
						$this->textlog->add('exportRegistryToXml: Реестр успешно отправлен');
						$this->ReturnData(array('success' => true));
					} else {
						$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
						//echo "{'success':true,'Link':'{$file_zip_name}'}";
					}
					$this->textlog->add("exportRegistryToXml: Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");
				}
				else{
					$this->textlog->add("exportRegistryToXml: Ошибка создания архива реестра!");
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
				}
				$this->textlog->add("exportRegistryToXml: Финиш");
				return true;
			}
			catch (Exception $e)
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add("exportRegistryToXml:".toUtf($e->getMessage()));
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf($e->getMessage())));
			}
		}
		else
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка. Не верно задан идентификатор счета!')));
			return false;
		}
	}

	/**
	 * Выгрузка принятых ОКНО случаев в XML
	 */
	function exportOnko() {
		$data = $this->ProcessInputData('exportOnko', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportOnko($data);
		$this->ProcessModelSave($response, true, 'Ошибка выгрузки ОНКО случаев')->ReturnData();
	}

	/**
	 * Функция возвращает общие данные реестра по Id реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
     *
     * @return string
     */
	function loadRegistryData()
	{
		$data = $this->ProcessInputData('loadRegistryData', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка данных в фильтр грида https://redmine.swan.perm.ru/issues/51270
	 */
	function loadRegistryDataFilter()
	{
		$data = $this->ProcessInputData('loadRegistryDataFilter', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryDataFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает общие ошибки реестра по Id реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryErrorCom()
	{
		$data = $this->ProcessInputData('loadRegistryErrorCom', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryErrorCom($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	public function loadRegistryErrorComFilter()
	{
		$data = $this->ProcessInputData('loadRegistryErrorComFilter', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryErrorComFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	public function loadRegistryDoubleFilter()
	{
		$data = $this->ProcessInputData('loadRegistryDoubleFilter', true);
		if($data === false) return false;

		$response = $this->dbmodel->loadRegistryDoubleFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает в XML список прикрепленного населения к указанной СМО на указанную дату
	 */
	function exportMorbusOrph()
	{
		$data = $this->ProcessInputData('exportMorbusOrph', true);
		if ($data === false) { return false; }

        set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
        $registry_data_res = $this->dbmodel->exportMorbusOrph($data);
        if ($registry_data_res === false)
        {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
            return false;
        }
        if ( ($registry_data_res['Error_Code']) && ($registry_data_res['Error_Code'] == 1) )
        {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данные по прикрепленному населению при указанных параметрах в базе данных отсутствуют.')));
            return false;
        }

        //$this->textlog->add('exportRegistryToXml: Получили все данные из БД ');
        $this->load->library('parser');

        // каталог в котором лежат выгружаемые файлы
        $out_dir = "re_xml_".time()."_"."attachedList";
        mkdir( EXPORTPATH_ATACHED_LIST.$out_dir );

        $pname = "AT_LI_".$registry_data_res['ZGLV']."T".$_SESSION['region']['number']."_".date('ym')."1";

        $file_re_pers_data_sign = $pname;
        // файл-перс. данные
        $file_re_pers_data_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_re_pers_data_sign.".xml";

        $registry_data_res['ZGLV'][0]['FILENAME'] = $file_re_pers_data_sign;

        //Для разных регионов
        $rgn = ($_SESSION['region']['nick']!='perm')?'_'.$_SESSION['region']['nick']:'';
        $templ = "person".$rgn;

        $xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, $registry_data_res, true);
        $xml = str_replace('&', '&amp;', $xml);

        file_put_contents($file_re_pers_data_name, $xml);

        $file_zip_sign = $file_re_pers_data_sign;
        $file_zip_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign.".zip";
        $zip = new ZipArchive();
        $zip->open($file_zip_name, ZIPARCHIVE::CREATE);
        $zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
        $zip->close();

        unlink($file_re_pers_data_name);

        if (file_exists($file_zip_name))
        {
            $this->ReturnData(array('success' => true,'Link' => $file_zip_name, 'Doc' => $registry_data_res['DOC']));
        }
        else {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
        }
        return true;
	}

    /**
	 *	Печать ошибок реестра
	 */
	function printRegistryError()
	{
		$this->load->library('parser');

		$template = '';

		$data = $this->ProcessInputData('printRegistryError', true);
		if ($data === false) { return false; }
		
		// Получаем данные по счету
		$data['nopaging'] = true;
		$response = $this->dbmodel->loadRegistryError($data);

		if (!is_array($response)) {
			echo 'Ошибка при получении данных по счету';
			return true;
		}

		foreach($response as &$one) {
			if ($one['Person_IsBDZ'] != 'false') {
				$one['Person_IsBDZ'] = 'Да';
			} else {
				$one['Person_IsBDZ'] = 'Нет';
			}
		}
		
		$parse_data = array(
			'items' => $response
		);
		
		$template = 'print_registryerror';

		if (isset($data['session']['region']['nick'])) {
			switch($data['session']['region']['nick']) {
				case 'ufa':
				case 'pskov':
					$parse_data['Usluga_Code_TH'] = 'Код посещения';
					if (isset($response['data'][0]['RegistryType_id']) && ($response['data'][0]['RegistryType_id'] == 1 || $response['data'][0]['RegistryType_id'] == 14)) {
						$parse_data['Usluga_Code_TH'] = 'МЭС';
					}
					$template = 'print_registryerror_ufa';
				break;
			}
		}

		$this->parser->parse($template, $parse_data);

		return true;
	}

	/**
	 * @return bool
	 */
	function loadRegistryErrorFilter()
	{

		$data = $this->ProcessInputData('loadRegistryErrorFilter', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryErrorFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция для печати всех данных реестра ОМС по Id реестра
	 http://redmine.swan.perm.ru/issues/19969
	 */
	 
	function printRegistryData()
	{
		$this->load->library('parser');

		$template = '';

		$data = $this->ProcessInputData('loadRegistryData', true);
		if ($data === false) { return false; }


		$data['nopaging'] = true;

		$response = $this->dbmodel->getRegistryType($data);
		$data['RegistryType_id'] = $response['RegistryType_id'];
		
		$response = $this->dbmodel->loadRegistryData($data);

		if (!is_array($response)) {
			echo 'Ошибка при получении данных';
			return true;
		}
		
		foreach($response as &$one) {
			if ($one['Person_IsBDZ'] != 'false') {
				$one['Person_IsBDZ'] = 'Да';
			} else {
				$one['Person_IsBDZ'] = 'Нет';
			}
		}
		
		$parse_data = array(
			'items' => $response
		);
		
		$template = 'print_registrydata';

		if (isset($data['session']['region']['nick'])) {
			switch($data['session']['region']['nick']) {
				case 'ufa':
				case 'pskov':
					$parse_data['Usluga_Code_TH'] = 'Код посещения';
                    $parse_data['EvnVizitPL_TH'] = 'Посещение';
                    $parse_data['RegistryData_Uet_TH'] = 'УЕТ';
					if (isset($data['RegistryType_id']) && ($data['RegistryType_id'] == 1 || $data['RegistryType_id'] == 14)) {
						$parse_data['Usluga_Code_TH'] = 'МЭС';
                        $parse_data['EvnVizitPL_TH'] = 'Поступление';
                        $parse_data['RegistryData_Uet_TH'] = 'К/д факт';
					}
					$template = 'print_registrydata_ufa';
				break;
			}
		}

		$this->parser->parse($template, $parse_data);

		return true;
	}
	
	/**
	 * Функция возвращает ошибки данных реестра по Id реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryError()
	{
		$data = $this->ProcessInputData('loadRegistryError', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryError($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает незастрахованных по Id реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryNoPolis()
	{
		$data = $this->ProcessInputData('loadRegistryNoPolis', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryNoPolis($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	function loadRegistryNoPolisFilter()
	{
		$data = $this->ProcessInputData('loadRegistryNoPolisFilter', true);
		if ($data === false) { return false; }
		//var_dump($data);die;
		$response = $this->dbmodel->loadRegistryNoPolisFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	/**
	 * Функция возвращает ошибки персданных реестра по Id реестра, если таковая проверка производилась
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryPerson()
	{
		$data = $this->ProcessInputData('loadRegistryPerson', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryPerson($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	
	/**
	 * Функция возвращает ошибки данных реестра по версии ТФОМС :)
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryErrorTFOMS()
	{
		$data = $this->ProcessInputData('loadRegistryErrorTFOMS', true);
		if($data)
		{
			$response = $this->dbmodel->loadRegistryErrorTFOMS($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			$this->ReturnData(array('success' => false));
			return false;
		}
	}

	/**
	 * Функция возвращает ошибки данных реестра по версии МЗ :)
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryHealDepResErrGrid()
	{
		$data = $this->ProcessInputData('loadRegistryHealDepResErrGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadRegistryHealDepResErrGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			$this->ReturnData(array('success' => false));
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadRegistryErrorTFOMSFilter()
	{
		$data = $this->ProcessInputData('loadRegistryErrorTFOMSFilter', true);
		if ($data === false) { return false; }
		//var_dump($data);die;
		$response = $this->dbmodel->loadRegistryErrorTFOMSFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление ошибки
	 */
	function deleteRegistryErrorTFOMS() {
		$data = $this->ProcessInputData('deleteRegistryErrorTFOMS', true);
		if($data === false) { return false; }

		$data['setNoErrorSum'] = true;
		$response = $this->dbmodel->deleteRegistryErrorTFOMS($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Удаление ошибки МЗ
	 */
	function deleteRegistryHealDepErrorType() {
		$data = $this->ProcessInputData('deleteRegistryHealDepErrorType', true);
		if($data === false) { return false; }

		$response = $this->dbmodel->deleteRegistryHealDepErrorType($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение ошибки МЗ
	 */
	function saveRegistryHealDepErrorType() {
		$data = $this->ProcessInputData('saveRegistryHealDepErrorType', true);
		if($data === false) { return false; }

		$response = $this->dbmodel->saveRegistryHealDepErrorType($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы редактирования ошибки МЗ
	 */
	function loadRegistryHealDepErrorTypeEditWindow() {
		$data = $this->ProcessInputData('loadRegistryHealDepErrorTypeEditWindow', true);
		if($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryHealDepErrorTypeEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение ошибки
	 */
	function saveRegistryErrorTFOMS() {
		$data = $this->ProcessInputData('saveRegistryErrorTFOMS', true);
		if($data === false) { return false; }

		$response = $this->dbmodel->saveRegistryErrorTFOMS($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
	
	/**
	 * Функция возвращает ошибки данных реестра стадии БДЗ
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadRegistryErrorBDZ()
	{
		$data = $this->ProcessInputData('loadRegistryErrorBDZ', true);
		if($data)
		{
			$response = $this->dbmodel->loadRegistryErrorBDZ($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			$this->ReturnData(array('success' => false));
			return false;
		}
	}
	
	/**
	 *	Неведомая фигня - выкуси, парсер
	 */
	function loadRegistryDouble()
	{
		$data = $this->ProcessInputData('loadRegistryDouble', true);
		if($data === false) return false;
		
		$response = $this->dbmodel->loadRegistryDouble($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *	Загрузка дублей посещений
	 */
	function loadRegistryDoublePL()
	{
		$data = $this->ProcessInputData('loadRegistryDoublePL', true);
		if($data === false) return false;

		$response = $this->dbmodel->loadRegistryDoublePL($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree()
	{
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "")
		{
			$val = array();
			$i = 0;
			if (!empty($node_id))
			{
				$node_id = "/".$node_id;
			}
			if ( $childrens != false && count($childrens) > 0 )
			{
				foreach ($childrens as $rows)
				{
					$node = array(
						'text'=>toUTF(trim($rows[$field['name']])),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						//'new'=>$rows['New'],
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
						);
					//$val[] = array_merge($node,$lrt,$lst);
					$val[] = $node;
				}

			}
			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = array();
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ((!isset($data['level'])) || (!is_numeric($data['level'])))
		{
			$val = array();//gabdushev: $val не определена в этом scope, добавил определение чтобы не было ворнинга, не проверял.
            $this->ReturnData($val);
			return;
		}

		$node = "";
		if (isset($data['node']))
		{
			$node = $data['node'];
		}

		$response = array();

		Switch ($data['level'])
		{
			case 0: // Уровень Root. ЛПУ
			{
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 1: // Уровень 1. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 2: // Уровень 2. Статусы реестров
			{
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = Array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
		}
		if ( count($c_two)>0 )
		{
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryMzTree()
	{
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "")
		{
			$val = array();
			$i = 0;
			if (!empty($node_id))
			{
				$node_id = "/".$node_id;
			}
			if ( $childrens != false && count($childrens) > 0 )
			{
				foreach ($childrens as $rows)
				{
					$node = array(
						'text' => toUTF(trim($rows[$field['name']])),
						'id' => $field['object'] . "." . $lvl . "." . $rows[$field['id']] . $node_id,
						//'new'=>$rows['New'],
						'object' => $field['object'],
						'object_id' => $field['id'],
						'object_value' => $rows[$field['id']],
						'leaf' => isset($rows['leaf']) ? $rows['leaf'] : $field['leaf'],
						'iconCls' => $field['iconCls'],
						'cls' => $field['cls']
					);
					//$val[] = array_merge($node,$lrt,$lst);
					$val[] = $node;
				}

			}
			return $val;
		}

		$data = $this->ProcessInputData('loadRegistryMzTree', true);
		if($data === false) return false;

		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ((!isset($data['level'])) || (!is_numeric($data['level'])))
		{
			$val = array();//gabdushev: $val не определена в этом scope, добавил определение чтобы не было ворнинга, не проверял.
            $this->ReturnData($val);
			return;
		}

		$node = "";
		if (isset($data['node']))
		{
			$node = $data['node'];
		}

		$data['PayType_SysNick'] = 'bud';

		$response = array();

		Switch ($data['level'])
		{
			case 0: // Уровень Root. Реестры/Справочник ошибок
			{
				$childrens = array(
					array('type_id' => 1, 'type_name' => 'Дерево реестров'),
					array('type_id' => 2, 'type_name' => 'Справочник ошибок', 'leaf' => true)
				);

				$field = Array('object' => "type",'id' => "type_id", 'name' => "type_name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 1: // Уровень 1. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 2: // Уровень 2. Статусы реестров
			{
				$childrens = $this->dbmodel->loadRegistryMzStatusNode($data);
				$field = Array('object' => "Status",'id' => "Status_SysNick", 'name' => "Status_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
		}
		if ( count($c_two)>0 )
		{
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);
	}

	/**
	 * Получение текущего статуса отправки реестра 
	 * Входящие данные: $data['Registry_id']
	 * На выходе: массив (RegistryCheckStatus_id, RegistryCheckStatus_Name)
	 * Используется: форма редактирования реестра (счета)
	 */
	function getRegistryCheckStatus($data) {
		$result = array('RegistryCheckStatus_id'=>0, 'RegistryCheckStatus_Name'=>'');
		if ($_SESSION['region']['nick'] == 'perm') {
			if (isset($data['Registry_id']) && ($data['Registry_id']>0)) {
				$response = $this->dbmodel->getRegistryCheckStatus($data);
				if ( is_array($response) && ((count($response) > 0)) ) {
					$result['RegistryCheckStatus_id'] = $response[0]['RegistryCheckStatus_id'];
					$result['RegistryCheckStatus_Name'] = $response[0]['RegistryCheckStatus_Name'];
				}
			}
		}
		return $result;
	}
	
	/**
	 * Сохранение реестра
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования реестра (счета)
	 */
	function saveRegistry()
	{
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('saveRegistry', true);
		if ($data === false) { return false; }

		if (!$this->dbmodel->checkRegistryTypeAllowedToReform($data)) {
			$this->ReturnError('Формирование реестров данного типа невозможно.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink($data)) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}

		if (!$this->dbmodel->checkRegistryNumUnique($data)) {
			$this->ReturnError('Невозможно сохранить реестр с таким номером. Номер должен быть уникальным по МО в году вне зависимости от того объединенный он или предварительный (требование ТФОМС)');
			return false;
		}

		$val  = array();

		// Для получения настроек нужно прицепиться к рабочей БД
		// https://redmine.swan.perm.ru/issues/53669
		if ( !isSuperadmin() ) {
			unset($this->db);
			$this->load->database('default');

			$this->load->model('Options_model', 'opmodel');
			$checkRegistryAccess = $this->opmodel->checkRegistryAccess($data);
			if (!empty($checkRegistryAccess['Error_Msg'])) {
				$this->ReturnError($checkRegistryAccess['Error_Msg']);
				return false;
			}

			if ( $checkRegistryAccess['access'] == false ) {
				$this->ReturnError('Формирование реестров временно недоступно.');
				return false;
			}
			/*if ((!isSuperadmin()) && ((isset($_POST['RegistryType_id'])) && (($_POST['RegistryType_id']=='1') || ($_POST['RegistryType_id']=='2'))))
			{
				$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров поликлиники и стационара временно недоступно.')));
				return false;
			}*/

			// Возвращаем соединение с реестровой БД
			// https://redmine.swan.perm.ru/issues/53669
			unset($this->db);
			if ($this->usePostgreRegistry) {
				$this->load->database('postgres');
			}else{
				$this->load->database('registry');
			}
			
		}

		// проверка статуса реестра 
		$status = $this->getRegistryCheckStatus($data);
		if (($status['RegistryCheckStatus_id'] == 1) || ($status['RegistryCheckStatus_id'] == 2)) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Переформирование реестра невозможно, т.к. текущий статус реестра:<br/>'.$status['RegistryCheckStatus_Name'].'!')));
			return;
		}
		
		// при добавлении дату счета берем текущую, независимо от того, передана она или нет. Вообще-то недо из запроса перед сохранением счета брать.
		if ( empty($data['Registry_id']) && (empty($data['Registry_accDate']) || !in_array($data['session']['region']['nick'], array('astra', 'krym','vologda', 'khak', 'yaroslavl'))) ) {
			$data['Registry_accDate'] = date("Y-m-d");
		}
		// Сохранение
		//$response = $this->dbmodel->saveRegistry($data);
		// Сохранение в очередь
		$response = $this->dbmodel->saveRegistryQueue($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Переформирование реестра
	 * Входящие данные: Registry_id
	 * На выходе: JSON-строка
	 * Используется: форма редактирования реестра (счета)
	 */
	function reformRegistry()
	{
		set_time_limit(0);
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('reformRegistry', true);
		if ($data === false) { return false; }

		if ( !isSuperadmin() ) {
			// Для получения настроек нужно прицепиться к рабочей БД
			unset($this->db);
			$this->load->database('default');

			$this->load->model('Options_model', 'opmodel');
			$checkRegistryAccess = $this->opmodel->checkRegistryAccess($data);
			if (!empty($checkRegistryAccess['Error_Msg'])) {
				$this->ReturnError($checkRegistryAccess['Error_Msg']);
				return false;
			}

			if ( $checkRegistryAccess['access'] == false ) {
				$this->ReturnError('Формирование реестров временно недоступно.');
				return false;
			}

			// Возвращаем соединение с реестровой БД
			unset($this->db);
			if ($this->usePostgreRegistry) {
				$this->load->database('postgres');
			}else{
				$this->load->database('registry');
			}
		}

		$response = $this->dbmodel->getRegistryType($data);
		if (empty($response['RegistryType_id'])) {
			$this->ReturnError('Ошибка определения типа реестра.');
			return false;
		}
		$data['RegistryType_id'] = $response['RegistryType_id'];

		if (!$this->dbmodel->checkRegistryTypeAllowedToReform($data)) {
			$this->ReturnError('Формирование реестров данного типа невозможно.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink($data)) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}

		// проверка статуса реестра 
		$status = $this->getRegistryCheckStatus($data);
		if (($status['RegistryCheckStatus_id'] == 1) || ($status['RegistryCheckStatus_id'] == 2)) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Переформирование реестра невозможно, т.к. текущий статус реестра:<br/>'.$status['RegistryCheckStatus_Name'].'!')));
			return;
		}
		
		$response = $this->dbmodel->reformRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Переформирование реестра по исправленным записям
	 * Входящие данные: Registry_id
	 * На выходе: JSON-строка
	 * Используется: форма редактирования реестра (счета)
	 */
	function reformErrRegistry()
	{
		set_time_limit(0);

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('reformErrRegistry', true);
		if ($data === false) { return false; }

		if ( !isSuperadmin() ) {
			// Для получения настроек нужно прицепиться к рабочей БД
			unset($this->db);
			$this->load->database('default');

			$this->load->model('Options_model', 'opmodel');
			$checkRegistryAccess = $this->opmodel->checkRegistryAccess($data);
			if (!empty($checkRegistryAccess['Error_Msg'])) {
				$this->ReturnError($checkRegistryAccess['Error_Msg']);
				return false;
			}

			if ( $checkRegistryAccess['access'] == false ) {
				$this->ReturnError('Формирование реестров временно недоступно.');
				return false;
			}

			// Возвращаем соединение с реестровой БД
			unset($this->db);
			if ($this->usePostgreRegistry) {
				$this->load->database('postgres');
			}else{
				$this->load->database('registry');
			}
		}

		// проверка статуса реестра 
		$status = $this->getRegistryCheckStatus($data);
		if (($status['RegistryCheckStatus_id'] == 1) || ($status['RegistryCheckStatus_id'] == 2)) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Переформирование реестра невозможно, т.к. текущий статус реестра:<br/>'.$status['RegistryCheckStatus_Name'].'!')));
			return;
		}
		
		// Сохранение
		$response = $this->dbmodel->reformErrRegistry($data);
		if (is_array($response) && count($response) > 0)
		{
			if (!isset($response[0]['success']))
			{
				if (strlen($response[0]['Error_Msg']) == 0)
				{
					$response[0]['success'] = true;
				}
				else
				{
					$response[0]['success'] = false;
				}
			}
			$val = $response[0];
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);
	}

	/**
	 * Оживление реестра
	 */
	function reviveRegistry()
	{
		$this->load->model('Utils_model', 'umodel');
		$this->load->model('Registry_model', 'dbmodel');
		$val  = array();

		$data = $this->ProcessInputData('deleteRegistryQueue', true);
		if ($data === false) { return false; }

		// https://redmine.swan.perm.ru/issues/55672
		if ($data['session']['region']['nick'] == 'perm') {
			$this->ReturnError('Запрещено восстановление реестров.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink($data)) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}
		
		$object = "RegistryQueue";
		$id = $data['Registry_id'];
		$response = $this->dbmodel->registryRevive($data, $id, $this->scheme);

		if (is_array($response) && count($response) > 0)
		{
			if (!isset($response[0]['success']))
			{
				if (strlen($response[0]['Error_Message']) == 0)
				{
					$response[0]['success'] = true; //
				}
				else
				{
					$response[0]['success'] = false;
					$response[0]['Error_Msg'] = $response[0]['Error_Message'];
				}
			}
			$val = $response[0];
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);
	}

	/**
	 * Удаление реестра из очереди
	 */
	function deleteRegistryQueue()
	{
		$this->load->model('Utils_model', 'umodel');
		$this->load->model('Registry_model', 'dbmodel');
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteRegistryQueue', true);
		if ($data === false) { return false; }

		$reform = $this->dbmodel->getRegistryQueueReformStatus($data);
		if ($reform == -1) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Реестр не найден в очереди на формирование'));
			return false;
		}
		if ($reform == 2) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Реестр в процессе формирования'));
			return false;
		}

		if ( $this->dbmodel->beforeDeleteRegistryQueue($data['Registry_id']) === false ) {
			$this->ReturnError('Ошибка при удалении реестра из очереди на формирование');
			return false;
		}
		
		$id = $data['Registry_id'];
		$response = $this->umodel->ObjectRecordDelete($data, 'RegistryQueue', true, $id, $this->scheme);

		if (is_array($response) && count($response) > 0)
		{
			if (!isset($response[0]['success']))
			{
				if (strlen($response[0]['Error_Message']) == 0)
				{
					// Делаем update истории реестров, если запись удалена из RegistryQueue
					$r = $this->dbmodel->closeRegistryQueueHistory($data);
					if (is_array($r) && count($r) > 0)
					{
						if (strlen($r[0]['Error_Msg']) > 0)
						{
							$val = $r[0];
						}
						else
						{
							$response[0]['success'] = true; // Удалено из очереди без ошибок
						}
					}
				}
				else
				{
					$response[0]['success'] = false;
					$response[0]['Error_Msg'] = $response[0]['Error_Message'];
				}
			}
			$val = $response[0];
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);
	}
	
	/**
	 * Помечаем запись в реестре на удаление
	 */
	function deleteRegistryData()
	{
		$this->load->model('Registry_model', 'dbmodel');
		
		$data = $this->ProcessInputData('deleteRegistryData', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink($data)) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}
		
		$data['RegistryData_deleted'] = ($data['RegistryData_deleted']==1)?2:1;
		
		$data['EvnIds'] = array();
		
		if (!empty($data['Evn_ids'])) {
			$data['EvnIds'] = json_decode($data['Evn_ids']);
		}
		
		if (!empty($data['Evn_id'])) {
			if ( method_exists($this->dbmodel, 'getEvnIdList') ) {
				$EvnIdList = $this->dbmodel->getEvnIdList($data);

				if ( is_array($EvnIdList) ) {
					$data['EvnIds'] = $EvnIdList;
				}
			}

			if ( count($data['EvnIds']) == 0 ) {
				$data['EvnIds'][] = $data['Evn_id'];
			}
		}

		
		if (count($data['EvnIds']) < 1) {
			$this->ReturnError('Не выбран случай для удаления');
			return false;		
		}

		$response = $this->dbmodel->deleteRegistryData($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->ReturnError($response[0]['Error_Msg']);
			return false;
		}

		$this->ReturnData(array('success' => true));
		return true;
	}

	/**
	 * Помечаем записи в реестре на удаление
	 */
	public function deleteRegistryDataAll()
	{
		$this->load->model('Registry_model', 'dbmodel');

		$data = $this->ProcessInputData('deleteRegistryDataAll', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink($data)) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}

		$data['RegistryData_deleted'] = ($data['RegistryData_deleted']==1)?2:1;

		$response = $this->dbmodel->deleteRegistryDataAll($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления записей')->ReturnData();

		return true;
	}

	/**
	 * Устанавливаем признак (needReform) для переформирования записи реестра по правилам YesNo
	 * Входящие данные: ID рееестра
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	function setNeedReform()
	{
		$data = $this->ProcessInputData('setNeedReform', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->setNeedReform($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}


	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	function setRegistryStatus()
	{
		$data = $this->ProcessInputData('setRegistryStatus', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInArchive($data)) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		// В Уфе можно переводить в оплаченные и к оплате без данной проверки
		if (getRegionNick() != 'ufa' || !in_array($data['RegistryStatus_id'], array(4,2))) {
			if ($this->dbmodel->checkRegistryInGroupLink($data)) {
				if (getRegionNick() == 'ufa' || getRegionNick() == 'vologda') {
					$this->ReturnError('Реестр включён в реестр по СМО, все действия над реестром запрещены.');
				} else {
					$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
				}
				return false;
			}
		}
		
		if ($data['RegistryStatus_id'] == 3 && $this->dbmodel->checkRegistryIsBlocked($data)) {
			$this->ReturnError('Реестр заблокирован, запрещено менять статус на "В работе".');
			return false;
		}
		
		$response = $this->dbmodel->setRegistryStatus($data);
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Отметка реестра активным
	 */
	function setRegistryActive()
	{
		$data = $this->ProcessInputData('setRegistryActive', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->setRegistryActive($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Отметки об оплате случаев
	 */
	function setRegistryDataPaidFromJSON()
	{
		$data = $this->ProcessInputData('setRegistryDataPaidFromJSON', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setRegistryDataPaidFromJSON($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Печать реестра
	 */
	function printRegistry() {
		$this->load->library('parser');

		$template = '';

		$data = $this->ProcessInputData('printRegistry', true);
		if ($data === false) { return false; }
		
		// Получаем данные по счету
		$response = $this->dbmodel->getRegistryFields($data);

		if ( (!is_array($response)) || (count($response) == 0) || (!isset($response['Registry_Sum'])) ) {
			echo 'Ошибка при получении данных по счету';
			return true;
		}
		else {
			switch ( $response['RegistryType_Code'] ) {
				case 1:
				case 14:
					$template = 'print_registry_account_stac';
				break;

				case 2:
				case 16:
					$template = 'print_registry_account_polka';
				break;

				case 4:
					$template = 'print_registry_account_dopdisp';
				break;

				case 5:
					$template = 'print_registry_account_orpdisp';
				break;
				
				case 6:
					$template = 'print_registry_account_smp';
				break;
				
				case 7:
					$template = 'print_registry_account_dopdispfirst';
				break;

				case 8:
					$template = 'print_registry_account_dopdispsecond';
				break;

				case 9:
					$template = 'print_registry_account_orpdispfirst';
				break;

				case 10:
					$template = 'print_registry_account_orpdispsecond';
				break;
				
				case 11:
					$template = 'print_registry_account_profsurvey';
				break;
				
				case 12:
					$template = 'print_registry_account_profteen';
				break;
				
				case 15:
					$template = 'print_registry_account_par';
				break;
				
				default:
					echo 'По данному реестру счет невозможно получить - функционал находится в разработке!';
					return true;
				break;
			}
		}

		$m = new money2str();
		$registry_sum_words = trim($m->work(number_format($response['Registry_Sum'], 2, '.', ''), 2));
		$registry_sum_words = strtoupper(substr($registry_sum_words, 0, 1)) . substr($registry_sum_words, 1, strlen($registry_sum_words) - 1);
		
		$registry_sumpaid_words = trim($m->work(number_format($response['Registry_SumPaid'], 2, '.', ''), 2));
		$registry_sumpaid_words = strtoupper(substr($registry_sumpaid_words, 0, 1)) . substr($registry_sumpaid_words, 1, strlen($registry_sumpaid_words) - 1);

		$month = array(
			'январе',
			'феврале',
			'марте',
			'апреле',
			'мае',
			'июне',
			'июле',
			'августе',
			'сентябре',
			'октябре',
			'ноябре',
			'декабре'
		);

		//array_walk($month, 'ConvertFromUTF8ToWin1251');

		$parse_data = array(
			'Lpu_Account' => !empty($response['Lpu_Account']) ? $response['Lpu_Account'] : '&nbsp;',
			'Lpu_Address' => !empty($response['Lpu_Address']) ? $response['Lpu_Address'] : '&nbsp;',
			'Lpu_Director' => !empty($response['Lpu_Director']) ? $response['Lpu_Director'] : '&nbsp;',
			'Lpu_GlavBuh' => !empty($response['Lpu_GlavBuh']) ? $response['Lpu_GlavBuh'] : '&nbsp;',
			'Lpu_INN' => !empty($response['Lpu_INN']) ? $response['Lpu_INN'] : '&nbsp;',
			'Lpu_KPP' => !empty($response['Lpu_KPP']) ? $response['Lpu_KPP'] : '&nbsp;',
			'Lpu_Name' => !empty($response['Lpu_Name']) ? $response['Lpu_Name'] : '&nbsp;',
			'Lpu_OKPO' => !empty($response['Lpu_OKPO']) ? $response['Lpu_OKPO'] : '&nbsp;',
			'Lpu_OKTMO' => !empty($response['Lpu_OKPO']) ? $response['Lpu_OKTMO'] : '&nbsp;',
			'Lpu_OKVED' => !empty($response['Lpu_OKVED']) ? $response['Lpu_OKVED'] : '&nbsp;',
			'Lpu_Phone' => !empty($response['Lpu_Phone']) ? $response['Lpu_Phone'] : '&nbsp;',
			'LpuBank_BIK' => !empty($response['LpuBank_BIK']) ? $response['LpuBank_BIK'] : '&nbsp;',
			'LpuBank_Name' => !empty($response['LpuBank_Name']) ? $response['LpuBank_Name'] : '&nbsp;',
			'Month' => !empty($response['Registry_Month']) && !empty($month[$response['Registry_Month'] - 1]) ? $month[$response['Registry_Month'] - 1] : '&nbsp;',
			'Registry_accDate' => !empty($response['Registry_accDate']) ? $response['Registry_accDate'] : '&nbsp;',
			'Registry_Num' => isset($response['Registry_Num']) ? $response['Registry_Num'] : '&nbsp;',
			'Registry_Sum' => isset($response['Registry_Sum']) ? number_format($response['Registry_Sum'], 2, '.', ' ') : '&nbsp;',
			'Registry_Sum_Words' => $registry_sum_words,
			'Registry_SumPaid' => isset($response['Registry_SumPaid']) ? number_format($response['Registry_SumPaid'], 2, '.', ' ') : '&nbsp;',
			'Registry_SumPaid_Words' => $registry_sumpaid_words,
			'Year' => !empty($response['Registry_Year']) ? $response['Registry_Year'] : '&nbsp;',
			'OrgP_RSchet' => !empty($response['OrgP_RSchet']) ? $response['OrgP_RSchet'] : '&nbsp;',
			'OrgP_Address' => !empty($response['OrgP_Address']) ? $response['OrgP_Address'] : '&nbsp;',
			'OrgP_INN' => !empty($response['OrgP_INN']) ? $response['OrgP_INN'] : '&nbsp;',
			'OrgP_KPP' => !empty($response['OrgP_KPP']) ? $response['OrgP_KPP'] : '&nbsp;',
			'OrgP_Name' => !empty($response['OrgP_Name']) ? $response['OrgP_Name'] : '&nbsp;',
			'OrgP_OKPO' => !empty($response['OrgP_OKPO']) ? $response['OrgP_OKPO'] : '&nbsp;',
			'OrgP_OKTMO' => !empty($response['OrgP_OKPO']) ? $response['OrgP_OKTMO'] : '&nbsp;',
			'OrgP_OKVED' => !empty($response['OrgP_OKVED']) ? $response['OrgP_OKVED'] : '&nbsp;',
			'OrgP_Phone' => !empty($response['OrgP_Phone']) ? $response['OrgP_Phone'] : '&nbsp;',
			'OrgP_BankBIK' => !empty($response['OrgP_BankBIK']) ? $response['OrgP_BankBIK'] : '&nbsp;',
			'OrgP_Bank' => !empty($response['OrgP_Bank']) ? $response['OrgP_Bank'] : '&nbsp;'
		);

		$this->parser->parse($template, $parse_data);

		return true;
	}

	/**
	 *	Получение типов полисов для формы "Человек в реестре"
	 */
	function getPolisTypes()
	{
		$data = $this->ProcessInputData('getPolisTypes', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getPolisTypes($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных человека из реестра для редактирования
	 */
	function getPersonEdit()
	{
		if (!isSuperadmin())
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'У вас нет прав для данной операции'));
			return false;
		}

		$data = $this->ProcessInputData('getPersonEdit', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getPersonEdit($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение данных человека в реестре
	 */
	function savePersonEdit()
	{
		if (!isSuperadmin())
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'У вас нет прав для данной операции'));
			return false;
		}

		$data = $this->ProcessInputData('savePersonEdit', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->savePersonEdit($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Удаление данных по человеку в реестре
	 */
	function deletePersonEdit($isMirror=false)
	{
		if (!isSuperadmin())
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'У вас нет прав для данной операции'));
			return false;
		}
		if ($this->usePostgreRegistry) {
			$this->load->database('postgres', false);
		}else{
			$this->load->database('registry', false);
		}
		$this->load->model('Registry_model', 'umodel');
		$Person_id = 0;
		$Evn_id = null;
		$object = '';
		
		$data = $this->ProcessInputData('deletePersonEdit', true);
		if ($data === false) { return false; }

		$Person_id = $data['Person_id'];
		$Evn_id = $data['Evn_id'];
		$object = $data['object'];

		$object = "RegistryDataLgot";

		$response = $this->umodel->deletePersonEdit($data, $object, $Person_id, $Evn_id);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Проверка возможности удаления реестра
	 */
	function checkDeleteRegistry($data)
	{
		return $this->dbmodel->checkDeleteRegistry($data);
	}

	/**
	 * Удаление реестра
	 */
	function deleteRegistry()
	{
		$this->load->model('Utils_model', 'umodel');

		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInArchive(array('Registry_id' => $data['id']))) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}

		if ($this->dbmodel->checkRegistryInGroupLink(array('Registry_id' => $data['id']))) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}

		$result = $this->checkDeleteRegistry($data);
		if ($result==true)
		{
			$response = $this->umodel->ObjectRecordDelete($data, "Registry", true, $data['id'], $this->scheme);
			if (isset($response[0]['Error_Message'])) { $response[0]['Error_Msg'] = $response[0]['Error_Message']; } else { $response[0]['Error_Msg'] = ''; }
			
			$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Извините, удаление реестра невозможно!');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
		}
	}
	
	/**
	 * Загрузка справочника ошибок по реестру
	 */
	function loadRegistryErrorType()
	{
		$data = $this->ProcessInputData('loadRegistryErrorType', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryErrorType($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных Случаи без оплаты (RegistryNoPay) для стационарных реестров
	 */
	function loadRegistryNoPay() {
		$data = $this->ProcessInputData('loadRegistryNoPay', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadRegistryNoPay($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *	Отметка человека как не двойника
	 */
	function doRegistryPersonIsDifferent()
	{
		$data = $this->ProcessInputData('doRegistryPersonIsDifferent', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->doRegistryPersonIsDifferent($data);
		$this->ProcessModelSave($response, true, 'Ошибка отметки человека как не двойника')->ReturnData();
		return true;
	}
	
	/**
	 * Объединение людей с вкладки "Ошибки перс.данных", предварительная обработка
	 */
	function doPersonUnionFromRegistry()
	{
		$data = $this->ProcessInputData('doPersonUnionFromRegistry', true);
		if ($data === false) { return false; }
		
		$data['Records'] = json_decode($data['Records'], true);
		$records = $data['Records'];
		$fromRegistry = ((isset($data['fromRegistry'])) && ($data['fromRegistry']==1));
		foreach ($records as $record)
		{
			if ($record['IsMainRec']==1) {
				$data['Person_id'] = $record['Person_id'];
			}
			else {
				$data['Person_did'] = $record['Person_id'];
			}
		}
		if ($fromRegistry) {
			$fromRegistry = ($this->dbmodel->getCountRegistryPerson($data)>0);

			if ( $data['session']['region']['nick'] == 'perm' && !empty($data['MaxEvnPerson_id']) && !empty($data['Registry_id']) ) {
				$response = $this->dbmodel->setRegistryPersonIsMerged($data);

				if ( !empty($response) ) {
					$this->ReturnError($response);
					return false;
				}
			}
		}
		$data['fromRegistry'] = $fromRegistry;
		// дальше лоадим рабочую базу
		unset($this->db);
		$this->load->database('default');
		$this->load->model("Utils_model", "umodel");

		$response = $this->umodel->doPersonUnion($data);
		if ($this->usePostgreRegistry) {
			$this->load->database('postgres', false);
		}else{
			$this->load->database('registry', false);
		}
		$this->ProcessModelSave($response, true, 'Извините, в данный момент сервис недоступен!')->ReturnData();
		/*
		if (is_array($response) && count($response) > 0)
		{
			if ( isset($response[0]['success']) && $response[0]['success'] ) {
				$val = $response[0];
			} else
			if ((isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0))
			{
				$val['success'] = true;
				// по идее сдесь если все путем, можно удалять из таблички RegistryPerson данные, но реестр надо переформировать, поэтому удалять пока не будем
			}
			else
			{
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Извините, в данный момент сервис недоступен!');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);
		*/
	}
	
	/**
	 * Удаление дублей
	 */
	public function deleteRegistryDouble() {
		$data = $this->ProcessInputData('deleteRegistryDouble', true);
		if ($data === false) return false;
		
		$this->dbmodel->setRegistryParamsByType($data);

		switch ( $this->dbmodel->RegistryType_id ) {
			case 6:
				$this->load->model("CmpCallCard_model", "Evn_model");
				$evnField = 'CmpCallCard_id';
				$keyField = 'CmpCallCard_id';
				$method = 'deleteCmpCallCard';
			break;

			default:
				$this->load->model("EvnVizit_model", "Evn_model");
				$evnField = 'Evn_id';
				$keyField = 'EvnVizitPL_id';
				$method = 'deleteEvnVizitPL';
			break;
		}

		// Сперва начитываем список событий для проверки наличия ЛВН, рецептов
		// @task https://redmine.swan.perm.ru/issues/132573
		$evnList = array();

		switch ( $data['mode'] ) {
			case 'current':
				$evnList[] = $data['Evn_id'];
				break;

			case 'all':
				// А здесь нужно найти все посещения по конкретному Registry_id, чтобы удалить их с боевой базы
				$data['withoutPaging'] = true;

				$doubles = $this->dbmodel->loadRegistryDouble($data);

				if( !is_array($doubles) || count($doubles) == 0 ) {
					$this->ReturnError('Нет записей для удаления');
					return false;
				}

				foreach ( $doubles as $row ) {
					$evnList[] = $row[$evnField];
				}
				break;

			default:
				return false;
				break;
		}

		// коннектимся к дефолтной базе
		unset($this->db);
		$this->load->database('default');

		if ( $this->dbmodel->RegistryType_id != 6 && count($evnList) > 0 ) {
			// Неясно, сколько может быть данных в $evnList. При большом объеме придется запрос выполнять по части записей.
			$evnClassList = $this->dbmodel->queryResult("
				with EvnRidList as (
					select Evn_rid
					from v_Evn with (nolock)
					where Evn_id in (" . implode(',', $evnList) . ")
				)

				select distinct e.EvnClass_SysNick
				from v_Evn e with (nolock)
				where Evn_rid in (select Evn_rid from EvnRidList)
			", array());

			if ( $evnClassList === false || !is_array($evnClassList) ) {
				$this->ReturnError('Ошибка при получении классов событий');
				return false;
			}
			else if ( count($evnClassList) > 0 ) {
				foreach ( $evnClassList as $row ) {
					if ( $row['EvnClass_SysNick'] == 'EvnStick' || $row['EvnClass_SysNick'] == 'EvnStickDop' ) {
						$this->ReturnError('Удаление документа невозможно, т.к. в рамках случая имеются выданные листы временной нетрудоспособности');
						return false;
					}
					else if ( substr($row['EvnClass_SysNick'], 0, 9) == 'EvnRecept' )  {
						$this->ReturnError('Удаление документа невозможно, т.к. в рамках случая имеются выписанные рецепты');
						return false;
					}
				}
			}
		}

		// Удаляем события
		foreach ( $evnList as $Evn_id ) {
			if ( empty($Evn_id) ) {
				continue;
			}

			$response = $this->Evn_model->$method(array(
				$keyField => $Evn_id,
				'pmUser_id' => $data['pmUser_id'],
				'RegistryDouble' => true
			), true);

			if ( !is_array($response) || !empty($response[0]['Error_Msg']) ) {
				break;
			}
		}

		// коннектимся к реестровой базе
		unset($this->db);
		if ($this->usePostgreRegistry) {
			$this->load->database('postgres', false);
		}else{
			$this->load->database('registry', false);
		}

		// Удаляем записи о дублях
		switch ( $data['mode'] ) {
			case 'current':
				$response = $this->dbmodel->deleteRegistryDouble($data);
				break;

			case 'all':
				$response = $this->dbmodel->deleteRegistryDoubleAll($data);
				break;
		}

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
	
	
	/**
	 * Получение истории статусов реестра
	 */
	function loadRegistryCheckStatusHistory() 
	{
		$data = $this->ProcessInputData('loadRegistryCheckStatusHistory', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadRegistryCheckStatusHistory($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение списк СМО входящих в реестр
	 */
	function getOrgSMOListForExportRegistry()
	{
		$data = $this->ProcessInputData('getOrgSMOListForExportRegistry', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->getOrgSMOListForExportRegistry($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Отметки об оплате случаев
	 */
	function loadRegistryDataPaid()
	{
		$data = $this->ProcessInputData('loadRegistryDataPaid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryDataPaid($data);
		$this->ProcessModelList($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
	
	/**
	 * сохранение регистров ФЛК
	 */
	function saveRegistryEntries(){
		$data = $this->ProcessInputData('saveRegistryEntries', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveRegistryEntries($data);
		
		$this->ProcessModelSave($response, true, 'При сохранении данных возникли ошибки')->ReturnData();
	}
	
	/**
	 * загрузка списка настроек регистров ФЛК
	 */
	function loadRegistryEntiesGrid(){
		$data = $this->ProcessInputData('loadRegistryEntiesGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryEntiesGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * Загрузка списка реестров для формы анализа изменений реестров
	 */
	public function loadRegistryCacheList(){
		$data = $this->ProcessInputData('loadRegistryCacheList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryCacheList($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * Загрузка списка событий реестра для формы анализа изменений реестра
	 */
	public function loadRegistryHistoryList(){
		$data = $this->ProcessInputData('loadRegistryHistoryList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadRegistryHistoryList($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * Возвращает данные для редактирования значения настройки ФЛК
	 * @return bool
	 */
	function loadRegistryEntiesForm()
	{
		$data = $this->ProcessInputData('loadRegistryEntiesForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryEntiesForm($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * удаляет запись из таблицы Настройка ФЛК
	 * @return bool
	 */
	function deleteRegistryFLK(){
		$data = $this->ProcessInputData('deleteRegistryFLK', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteRegistryFLK($data);
		//$this->ProcessModelList($response,true,true)->ReturnData();	
		$this->ProcessModelSave($response, true, 'При удалении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка ошибок
	 */
	function loadRegistryHealDepErrorTypeGrid() {
		$data = $this->ProcessInputData('loadRegistryHealDepErrorTypeGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryHealDepErrorTypeGrid($data);
		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка реестров
	 */
	function loadRegistryMzGrid() {
		$data = $this->ProcessInputData('loadRegistryMzGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryMzGrid($data);
		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка счётчиков списка случаев реестров
	 */
	function getRegistryDataMzGridCounters() {
		$data = $this->ProcessInputData('getRegistryDataMzGridCounters', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getRegistryDataMzGridCounters($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения счётчиков')->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка случаев реестров
	 */
	function loadRegistryDataMzGrid() {
		$data = $this->ProcessInputData('loadRegistryDataMzGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryDataMzGrid($data);
		if (!empty($data['getIdsOnly'])) {
			$this->ProcessModelSave($response, true, 'Ошибка получения записей реестра')->ReturnData();
		} else {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
		return true;
	}

	/**
	 * Изменяет статус реестра
	 */
	function setRegistryMzCheckStatus() {
		$data = $this->ProcessInputData('setRegistryMzCheckStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setRegistryMzCheckStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка изменения статуса реестра')->ReturnData();
		return true;
	}

	/**
	 * Принятие реестра
	 */
	function acceptRegistryMz() {
		$data = $this->ProcessInputData('acceptRegistryMz', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->acceptRegistryMz($data);
		$this->ProcessModelSave($response, true, 'Ошибка изменения статуса реестра')->ReturnData();
		return true;
	}

	/**
	 * Принятие случаев в реестре
	 */
	function processRegistryDataMz() {
		$data = $this->ProcessInputData('processRegistryDataMz', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->processRegistryDataMz($data);
		$this->ProcessModelSave($response, true, 'Ошибка изменения статуса реестра')->ReturnData();
		return true;
	}

	/**
	 * Получение списка ошибок для комбобокса
	 */
	function loadRegistryHealDepErrorTypeCombo() {
		$data = $this->ProcessInputData('loadRegistryHealDepErrorTypeCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryHealDepErrorTypeCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Отправка в МЗ
	 */
	function sendRegistryToMZ() {
		$data = $this->ProcessInputData('sendRegistryToMZ', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendRegistryToMZ($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отправке реестра в МЗ')->ReturnData();
	}

	/**
	 * Подписание реестра
	 */
	function signRegistry()
	{
		$data = $this->ProcessInputData('signRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->signRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при подписании реестра')->ReturnData();
	}

	/**
	 * Проверка существования файла экспорта реестра
	 */
	function checkRegistryXmlExportExists()
	{
		$data = $this->ProcessInputData('checkRegistryXmlExportExists', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkRegistryXmlExportExists($data);
		$this->ProcessModelSave($response, true, 'Ошибка при проверке существования файла экспорта реестра')->ReturnData();
	}
}
