<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
* модификация оригинального Registry.php для групповой постановке реестров на очередь формирования Task#18011
*/
class RegistryVE extends swController{
	var $dbgroup = "registry";
	var $scheme = "dbo";
	var $model_name = "Registry_modelVE";
	var $error_deadlock = "При обращении к базе данных произошла ошибка.<br/>Скорее всего данная ошибка вызвана повышенной нагрузкой на сервер. <br/>Повторите попытку, и, если ошибка появится вновь - <br/>свяжитесь с технической поддержкой.";
	public $file_log = 'registry.log';
	public $file_log_access = 'a';
	/**
	* comment
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
	* comment
	*/ 
	function __construct() {
		parent::__construct();

		if ( !isset($_GET['m']) || $_GET['m'] != 'importRegistrySmoDataFromDbf' )
		{
			// Инициализация класса и настройки
			$this->load->database($this->dbgroup, false);
			//Выставляем таймауты для выполнения запросов, пока вручную
			//$this->db->query_timeout = 600;
			$this->load->model($this->model_name, 'dbmodel');
		}

		$this->inputRules = array(
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
						'field' => 'RegistryErrorType_Code',
						'label' => 'Код ошибки',
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
					'field' => 'Person_FIO',
					'label' => 'ФИО',
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
			'loadRegistryDouble' => array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
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
			//Групповой экспорт реестров в XML
			'exportRegistryGroupToXml' => array(
		
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
						'default' => null,
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
						'default' => null,
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
				)
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
				)
			),
            'saveUnionRegistry' => array(
                array(
                	'default' => null,
                    'field' => 'OrgSmo_id',
                    'label' => 'СМО',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuUnitSet_id',
                    'label' => 'Подразделение',
                    'rules' => '',
                    'type' => 'id'
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
                	'field' => 'RegistrySubType',
                    'label' => 'Подтип реестра',
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
                   	'field' => 'Registry_IsActive',
                   	'label' => 'Признак активного регистра',
                   	'rules' => '',
                   	'type' => 'id'
                ),
                array(
                  	'field' => 'Registry_accDate',
                  	'label' => 'Дата счета',
                  	'rules' => '',
                  	'type' => 'date'
                ),
                array(
                    'field' => 'Registry_begDate',
                    'label' => 'Начало периода',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Registry_endDate',
                    'label' => 'Окончание периода',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Registry_IsNotInsur',
                    'label' => 'Незастрахованные лица',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Registry_IsZNO',
                    'label' => 'ЗНО',
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
                    'field' => 'Registry_Comment',
                    'label' => 'Коментарий для случаев не соответствующих отчетному периоду',
                    'rules' => '',
                    'default' => '',
                    'type' => 'string'
                )
		    ),            
			'saveRegistry' => array(
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
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 0,
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
					'field' => 'RegistryEventType_id',
					'label' => 'Тип случаев реестра',
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
					'field' => 'Registry_accDate',
					'label' => 'Дата счета',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Registry_begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Smo_Name',
					'label' => 'Название СМО',
					'rules' => '',
					'type' => 'string'
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
				)
			),
		'setRegistryStatus' => array(
				array(
					'default' => 0,
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
				)
			),
		'setRegistryActive' => array(
				array(
					'default' => 0,
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
					'field' => 'Smo_Name',
					'label' => 'имя СМО',
					'rules' => '',
					'type' => 'string'
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
				)
			)
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
		if (isset($response[0]) && (strlen($response[0]['Error_Msg']) != 0)) {
			return false;
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

		$response = $this->refreshRegistry($data);
		if ($response==false) {
			$result = array('success' => false, 'Error_Msg' => toUTF('Пересчет реестра невозможен, обратитесь к разработчикам.'));
		} else {
			$result = array('success' => true);
		}
		$this->ReturnData($result);
		return true;
	}
	
	
	/**
	*  Функция формирует и выводит в поток вывода .dbf файлы, обернутые в архив.
	*  Входящие данные: $_GET (Registry_id),
	*  На выходе: вывод в поток вывода .dbf файлов, обернутых в архив или сообщение об ошибке.
	*  Update 4 мая 2010 г. Night: поскольку результирующие Dbf для каждого типа реестра отличаются,
	*  то будем использовать RegistryType_id (тип реестра), для определения, какие именно форматы Dbf использовать.
	*/
	function exportRegistryToDbf()
	{
		// Это решение, которое позволяет временно закрыть выгрузку реестров в Dbf
		/* if (!isSuperadmin())
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Выгрузка реестров в dbf недоступна!'));
			return false;
		}
		*/
		
		$data = $this->ProcessInputData('exportRegistryToDbf', true);
		if ($data === false) { return false; }
		
		if (!isset($data['RegistryType_id'])) {
			$r = $this->dbmodel->loadRegData($data);
			if (is_array($r) && count($r) > 0) {
				$data['RegistryType_id'] = $r[0]['RegistryType_id'];
			}
		}
		/*
		if ((!isSuperadmin()) && ((isset($data['RegistryType_id'])) && (($data['RegistryType_id']=='1') || ($data['RegistryType_id']=='2'))))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Выгрузка реестров поликлиники и стационара в DBF более недоступна.')));
			return false;
		}
		*/
		//Тест запуска, запускается под пользователем под которым запущен вебсервер ?

		/*
		$arj = "c:\\arj32.exe";
		$path_arj = "s:\d.arj";
		$path_file = "d:\arj-3.10.22.tar.gz";

		$out = exec("$arj a -e $path_arj $path_file ");

		$out = exec("c:\\arj32.exe a -e s:\d.arj d:\arj-3.10.22.tar.gz");
		print $r;
		$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Внимание! Автоматическая отправка реестров в данное время не работает.')));
		return false;
		*/


		$this->writeLog("\n\r[" . date('Y-m-d H:i:s') . "] Начало выполнения выгрузки реестра \n\r");

		if ( isset($data['Registry_id']) && trim($data['Registry_id']) > 0 )
		{
			$this->writeLog("[" . date('Y-m-d H:i:s') . "] Реестр ID={$data['Registry_id']} \n\r");
		
			// Удаление помеченных на удаление записей и пересчет реестра 
			if ($this->refreshRegistry($data)===false) {
				// выход с ошибкой
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('При обновлении данных реестра произошла ошибка.')));
				return;
			}
			
			if (!isset($data['send']))
				$data['send'] = 0;

			if ($data['send'] == '1')
			{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Внимание! Автоматическая отправка реестров в данное время не работает.')));
				return;
			}

			$type = 0;
			// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
			$this->writeLog("[" . date('Y-m-d H:i:s') . "] Проверяем наличие и состояние реестра \n\r");

			$res = $this->dbmodel->GetRegistryExport($data);
			if (is_array($res) && count($res) > 0)
			{
				// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
				if (!empty($res[0]['Registry_SumDifference'])) {
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] Выход с сообщением: Неверная сумма по счёту и реестрам.");
					// добавляем ошибку
					// $data['RegistryErrorType_Code'] = 3;
					// $res = $this->dbmodel->addRegistryErrorCom($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Экспорт невозможен. Неверная сумма по счёту и реестрам.'), 'Error_Code' => '12'));
					return;
				}
				
				if ($res[0]['Registry_ExportPath'] == '1')
				{
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).')));
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] Реестр уже экспортируется. Выход. \n\r");
					return;
				}
				elseif (strlen($res[0]['Registry_ExportPath'])>0) // если уже выгружен реестр
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] Уже выгружен. Возвращаем ссылку на уже выгруженный реестр ({$res[0]['Registry_ExportPath']}). Выход. \n\r");
					$link = $res[0]['Registry_ExportPath'];
					if ($data['send']!=1)
					{
						$usePrevDbf = '';
						if (empty($data['onlyLink'])) {
							$usePrevDbf = ', usePrevDbf: true';
						}
						echo "{'success':true,'Link':'$link'$usePrevDbf}";
					}
					else
					{
						try
						{
							// Отправка письма
							$sendresult = $this->sendMail($link, $data);
							if ($sendresult===true)
								echo "{'success':true}";
							else
								echo "{'success':false, Error_Msg: 'Send Mail #1 Send result (registry ready)'}";
						}
						catch (Exception $e)
						{
							echo "{'success':false, Error_Msg: 'Send Mail #2 Exception (registry ready)'}";
						}
					}
					return;
				}
				else
				{
					$type = $res[0]['RegistryType_id'];
				}
			}
			else
			{
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] Ошибка при выгрузке. Выход. \n\r");
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.')));
				return;
			}

			// Формирование Dbf в зависимости от типа.
			// В случае возникновения ошибки - необхолимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
			try
			{
				$data['Status'] = '1';
				$this->dbmodel->SetExportStatus($data);
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Установили статус что реестр выгружается. \n\r");

				// Шапка для всех типов реестров одна
				$registry_header = $this->dbmodel->loadRegistryForDbfUsing($data);
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Обратились в базу за шапкой реестра. \n\r");
				if ($registry_header === false)
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Ошибка при обращении. Возвращаем ошибку. Выход. \n\r");
					$data['Status'] = '';
					$this->dbmodel->SetExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
					return false;
				}
				if ( !is_array($registry_header) || !(count($registry_header) > 0) )
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Ошибка при обращении. Данных нет. Выход. \n\r");
					$data['Status'] = '';
					$this->dbmodel->SetExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Требуемого реестра нет в базе данных.')));
					return false;
				}
				// RegistryData
				$registry_data_res = $this->dbmodel->loadRegistryDataForDbfUsing($type, $data);
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Обратились в базу за данными реестра. \n\r");
				if ($registry_data_res === false)
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Ошибка при обращении. Возвращаем ошибку. Выход. \n\r");
					$data['Status'] = '';
					$this->dbmodel->SetExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
					return false;
				}
				if ( !is_object($registry_data_res) || !$registry_data_res->has_rows() )
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Ошибка при обращении. Данных нет. Выход. \n\r");
					$data['Status'] = '';
					$this->dbmodel->SetExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по требуемому реестру нет в базе данных.')));
					return false;
				}
				// RegistryUsluga
				$include_usluga = false;
				//if ($type!=1)
				{
				$registry_usl_data_res = $this->dbmodel->loadRegistryUslDataForDbfUsing($type, $data);
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Обратились в базу за услугами реестра. \n\r");
				if ($registry_usl_data_res === false)
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Ошибка при обращении. Возвращаем ошибку. Выход. \n\r");
					$data['Status'] = '';
					$this->dbmodel->SetExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
					return false;
				}
				$include_usluga = true;
				if ( !is_object($registry_usl_data_res) || !$registry_usl_data_res->has_rows() )
				{
					$include_usluga = false;
				}
				}
				set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
				// Формирование массивов для соответсвующих Dbf
				if ( $include_usluga )
				{
					if (($type == 1) || ($type == 2)) // stac и polka
					{
						$reestr_uslugi_def = array(
							array( "HC", "N",7 , 0 ),
							array( "RC_LPU", "N",4 , 0 ),
							array( "RN_LPU", "N",6 , 0 ),
							array( "NSH", "C",12 , 0 ),
							array( "DSH", "D",8 , 0 ),
							array( "RE", "N",2 , 0 ),
							array( "G1", "N",5 , 0 ),
							array( "KU", "C",8 , 0 ),
							array( "SN", "N",4 , 2 ),
							array( "TS", "N",13 , 3 ),
							array( "G13", "N",3 , 1 ),
							array( "EI3", "N",2 , 0 ),
							array( "G14", "N",13 , 3 ),
							array( "G15", "N",3 , 2 ),
							array( "G16", "N",13 , 3 ),
							array( "G10", "C",40 , 0 ),
							array( "DISMEN", "D",8 , 0 )
						);
					}

					if (($type == 4) || ($type == 5)) // dd и orp
					{
						$reestr_uslugi_def = array(
							array( "RC_LPU", "N",4 , 0 ),
							array( "RN_LPU", "N",6 , 0 ),
							array( "NSH", "C",12 , 0 ),
							array( "DSH", "D",8 , 0 ),
							array( "RE", "N",2 , 0 ),
							array( "G1", "N",5 , 0 ),
							array( "MES", "C",5 , 0 ),
							array( "KU", "C",8 , 0 ),
							array( "SN", "N",5 , 2 ),
							array( "SX", "N",4 , 1 ),
							array( "TR", "N",10 , 0 ),
							array( "TS", "N",13 , 3 ),
							array( "G13", "N",4 , 1 ),
							array( "EI3", "N",2 , 0 ),
							array( "G14", "N",13 , 3 ),
							array( "G15", "N",4 , 2 ),
							array( "G16", "N",13 , 2 ),
							array( "PR", "C",1 , 0 ),
							array( "G10", "C",40 , 0 ),
							array( "DISMEN", "D",8 , 0 ),
							array( "PRN", "C",1 , 0 )
						);
					}
				}

				$reestr_def = array(
					array( "NSH", "C",12 , 0 ),
					array( "DSH", "D",8 , 0 ),
					array( "RG", "N",4 , 0 ),
					array( "RNL", "N",6 , 0 ),
					array( "SUMS", "N",13 , 2 ),
					array( "TS", "N",1 , 0 ),
					array( "VR", "N",2 , 0 ),
					array( "RE", "N",2 , 0 ),
					array( "TX", "C",50 , 0 ),
					array( "K_BANR", "N",9 , 0 ),
					array( "KS_BAN", "C",20 , 0 ),
					array( "MFO_BAN", "N",6 , 0 ),
					array( "SCHET", "C",20 , 0 ),
					array( "RC_P", "N",4 , 0 ),
					array( "RN_P", "N",6 , 0 ),
					array( "DISMEN", "D",8 , 0 ),
					array( "INN", "N",10 , 0 ),
					array( "DNP", "D",8 , 0 ),
					array( "DKP", "D",8 , 0 ),
					array( "TEL", "C",8 , 0 ),
					array( "GL", "C",20 , 0 ),
					array( "ISP", "C",30 , 0 )
				);

				$reestr_data_def = array(
					array( "NSH", "C",12 , 0 ),
					array( "RE", "N",2 , 0 ),
					array( "G1", "N",5 , 0 ),
					array( "POLIS_S", "C",10 , 0 ),
					array( "POLIS_N", "C",16 , 0 ),
					array( "RC_SMO", "N",4 , 0 ),
					array( "RN_SMO", "N",6 , 0 ),
					array( "RC_LPU", "N",4 , 0 ),
					array( "RN_LPU", "N",6 , 0 ),
					array( "XL", "C",2 , 0 ),
					array( "OT", "C",4 , 0 ),
					array( "PA", "N",2 , 0 ),
					array( "MES1", "C",10 , 0 ),
					array( "MKB1", "C",3 , 0 ),
					array( "MKU1", "C",1 , 0 ),
					array( "G131", "N",5 , 2 ), // С какого то момента значность поменялась с 6 на 5, поэтому и мы (с) Night, 14 марта 2010 года
					array( "SN1", "N",3 , 0 ),
					array( "EI1", "N",2 , 0 ),
					array( "G141", "N",10 , 2 ),
					array( "G151", "N",4 , 2 ),
					array( "G161", "N",10 , 2 ),
					array( "PR1", "C",1 , 0 ),
					array( "FI", "N",2 , 0 ),
					array( "IN", "N",1 , 0 ),
					array( "MES2", "C",10 , 0 ),
					array( "MKB2", "C",3 , 0 ),
					array( "MKU2", "C",1 , 0 ),
					array( "G132", "N",3 , 0 ),
					array( "SN2", "N",3 , 0 ),
					array( "EI2", "N",2 , 0 ),
					array( "G142", "N",10 , 2 ),
					array( "G152", "N",4 , 2 ),
					array( "G162", "N",10 , 2 ),
					array( "PR2", "C",1 , 0 ),
					array( "MES3", "C",10 , 0 ),
					array( "MKB3", "C",3 , 0 ),
					array( "MKU3", "C",1 , 0 ),
					array( "G133", "N",3 , 0 ),
					array( "SN3", "N",3 , 0 ),
					array( "EI3", "N",2 , 0 ),
					array( "G143", "N",10 , 2 ),
					array( "G153", "N",4 , 2 ),
					array( "G163", "N",10 , 2 ),
					array( "PR3", "C",1 , 0 ),
					array( "ITOG", "N",10 , 2 ),
					array( "FAMIL", "C",40 , 0 ),
					array( "IMJA", "C",30 , 0 ),
					array( "OTCH", "C",30 , 0 ),
					array( "GR", "D",8 , 0 ),
					array( "POL", "N",1 , 0 ),
					array( "ADRES", "N",5 , 0 ),
					array( "IND", "N",6 , 0 ),
					array( "ADR", "C",80 , 0 ),
					array( "MES_R", "N",4 , 0 ),
					array( "MES_RN", "N",6 , 0 ),
					array( "G6", "C",50 , 0 ),
					array( "G7", "C",50 , 0 ),
					array( "G9", "C",10 , 0 ),
					array( "G11", "C",80 , 0 ),
					array( "DNL", "D",8 , 0 ),
					array( "DKL", "D",8 , 0 ),
					array( "DISMEN", "D",8 , 0 ),
					array( "POT", "N",4 , 0 ),
					array( "DOG_NO", "C",6 , 0 ),
					array( "NDOK", "C",30 , 0 ),
					array( "LPUOUT", "C",15 , 0 ),
					array( "TR", "N",9 , 3 ),
					array( "FI1", "N",2 , 0 ),
					array( "DSH", "D",8 , 0 ),
					array( "OBR", "N",2 , 0 ),
					array( "PCOD_VR", "C",5 , 0 ),
					array( "SS", "C",14 , 0 ),
					array( "RC_LPU_NP", "N",4 , 0 ),
					array( "RN_LPU_NP", "N",6 , 0 ),
					array( "NP_num", "N",6 , 0 ),
					array( "NP_data", "D",8 , 0 ),
					array( "CRIMINAL", "N",1 , 0 ),
					array( "DIRECT", "N",2 , 0 ),
					array( "DELIVER", "N",2 , 0 ),
					array( "QCOD", "C",8 , 0 ),
					array( "POLIS_RF", "C",20 , 0 ),
					array( "FAMIL_RD", "C",20 , 0 ),
					array( "IMJA_RD", "C",15 , 0 ),
					array( "OTCH_RD", "C",15 , 0 ),
					array( "KL_REG", "C",13 , 0 ),
					array( "KL_SUBRG", "C",13 , 0 ),
					array( "KL_CITY", "C",13 , 0 ),
					array( "KL_TOWN", "C",13 , 0 ),
					array( "KL_STR", "C",17 , 0 ),
					array( "HOUSE", "C",5 , 0 ),
					array( "CORPUS", "C",5 , 0 ),
					array( "FLAT", "C",5 , 0 ),
					array( "HC", "N",7 , 0 ),
					array( "RC_LPU_FD", "N",4 , 0 ),
					array( "RN_LPU_FD", "N",6 , 0 ),
					array( "HC_FD", "N",7 , 0 ),
					array( "HC_NP", "N",7 , 0 ),
					array( "KLADR", "C",17 , 0 ),
					array( "C_OKATO2", "C",5 , 0 ),
					array( "C_OKSM", "C",3 , 0 ),
					array( "DATE_N", "D",8 , 0 ),
					array( "DATE_E", "D",8 , 0 ),
					array( "STAT_P", "N",1 , 0 ),
					array( "C_DOC", "N",2 , 0 ),
					array( "S_DOC", "C",9 , 0 ),
					array( "STAT_PA", "N",2 , 0 ),
					array( "PRVS", "C",9 , 0 ),
					array( "NON_PERM", "N",1 , 0 ),
					array( "Q_OGRN", "C",15 , 0 ),
					array( "PR_TRAVM", "C",1 , 0 ),
					array( "PEREV", "N",2 , 0 ),
					array( "KLADR_BORN", "C",14 , 0 ),
					array( "PL_BORN", "C",100 , 0 ),
					array( "VPOLIS", "N",1 , 0 ),
					array( "W_P", "N",1 , 0 ),
					array( "DR_P", "D",8 , 0 ),
					array( "isPaid", "C",5 , 0 )
				);
				/*
				if ($type == 1)
				{
					$reestr_data_def[] = array( "PEREV", "N",2 , 0 );
				}
				*/
				$base_name = ($_SERVER["DOCUMENT_ROOT"][strlen($_SERVER["DOCUMENT_ROOT"])-1]=="/")?$_SERVER["DOCUMENT_ROOT"]:$_SERVER["DOCUMENT_ROOT"]."/";
				$path = EXPORTPATH_ROOT;

				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Базовый путь: $base_name\n\r");


				/*
				// Баловство с диском убрал
				if (strpos($base_name,'/')===0)
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Обнаружен сетевой базовый путь: $base_name\n\r");
					if (file_exists('z://register_files/ARJ32.exe')===false)
					{
						//exec("net use z: ".$base_name.EXPORTPATH_ROOT);
						$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Делаем попытку смонтировать диск Z: "."net use z: ".$base_name.(($path[strlen($path)-1]=='/')?substr($path,0,(strlen($path)-1)):$path)."\n\r");
						exec("net use z: ".$base_name.(($path[strlen($path)-1]=='/')?substr($path,0,(strlen($path)-1)):$path));
					}
					if (file_exists('z://register_files/ARJ32.exe')===false)
					{
						// смонтировалось, значит используем Z:\
						$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Диск Z: смонтирован \n\r");
						$base_name = "z://";
					}
				}
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Базовый путь после преобразования: $base_name\n\r");
				*/

				// создаем 2 файла файл с БД
				$out_dir = "re_".time()."_".$data['Registry_id'];
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Создаем папку (".EXPORTPATH_REGISTRY.$out_dir.")\n\r");
				if (!mkdir( EXPORTPATH_REGISTRY.$out_dir ))
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Не удалось создать папку (".EXPORTPATH_REGISTRY.$out_dir.")!\n\r");
				}


				$file_re_sign = "SHP_MU";
				$file_re_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_sign.".dbf";
				//if ($type!=1)
				{
					$file_re_usl_sign = "RE_UO";
					$file_re_usl_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_usl_sign.".dbf";
				}
				$file_re_data_sign = "RE_MU";
				$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".dbf";
				$file_arj_sign = "arch";
				$file_arj_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_arj_sign.".arj";
				$h = dbase_create( $file_re_name, $reestr_def );
				if (!$h) $this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Не удалось создать DBF-файл (".$file_re_name.")!\n\r");
				foreach ($registry_header as $row)
				{
					// определяем которые даты и конвертируем их
					foreach ($reestr_def as $descr)
					{
						if ( $descr[1] == "D" )
							if (!empty($row[$descr[0]]))
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
					}
					$row["TX"] = str_replace("TX1", date("Ymd",strtotime($row["DNP"])), $row["TX"]);
					$row["TX"] = str_replace("TX2", date("Ymd",strtotime($row["DKP"])), $row["TX"]);
					$NSH = $row["NSH"];
					$DSH = $row["DSH"];
					$RE = $row["RE"];
					$RC_LPU = $row["RG"];
					$RN_LPU = $row["RNL"];
					$HC = $row["HC"];
					unset($row["HC"]);
					array_walk($row, 'ConvertFromUtf8ToCp866');
					dbase_add_record( $h, array_values($row) );
				}
				dbase_close ($h);
				$h = dbase_create( $file_re_data_name, $reestr_data_def );
				if (!$h) $this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Не удалось создать DBF-файл (".$file_re_data_name.")!\n\r");
				$row_number = 1;
				$row_number_hash = array();
				while( $row = $registry_data_res->_fetch_assoc())
				{
					// определяем которые даты и конвертируем их
					foreach ($reestr_data_def as $descr)
					{
						//print $descr[0]." - ".$row[$descr[0]]."<br/>";
						if ( $descr[1] == "D" )
						{
							if (!empty($row[$descr[0]]))
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
							//print $row[$descr[0]]."<br/>";
						}
					}
					$row['G1'] = $row_number;
					// запоминаем номера
					if ((isset($row['MaxEvn_id'])) && ($row['MaxEvn_id']>0))
						$row_number_hash[$row['MaxEvn_id']] = $row_number;
					$row_number++;
					// удаляем лишнее
					unset($row['MaxEvn_id']);
					//print_r($row);
					array_walk($row, 'ConvertFromUtf8ToCp866');
					//print_r($row);
					dbase_add_record( $h, array_values($row) );
				}
				dbase_close ($h);
				/**
				* comment
				*/
				if ( $include_usluga )
				{
					$h = dbase_create( $file_re_usl_name, $reestr_uslugi_def );
					if (!$h) $this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Не удалось создать DBF-файл (".$file_re_usl_name.")!\n\r");
					while( $row = $registry_usl_data_res->_fetch_assoc())
					{
						// определяем которые даты и конвертируем их
						foreach ($reestr_uslugi_def as $descr)
						{
							if ( $descr[1] == "D" )
								if (!empty($row[$descr[0]]))
									$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
						}
						$row["NSH"] = $NSH;
						$row["DSH"] = $DSH;
						$row["RE"] = $RE;
						$row["RC_LPU"] = $RC_LPU;
						$row["RN_LPU"] = $RN_LPU;
						if ($type == 2)
						{
							$row["HC"] = $HC;
						}
						// выставляем нужный номер
						if ( isset($row_number_hash[$row['MaxEvn_id']]) )
						{
							$row['G1'] = $row_number_hash[$row['MaxEvn_id']];
							// если есть в реестре запись, то услугу включаем в выгрузку
							$vkl_usl = true;
						}
						// как такое может быть о_О
						else
						{
							$row['G1'] = '';
							$vkl_usl = false;
						}
						// удаляем лишнее
						//if ( isset($row['MaxEvn_id']) )
						unset($row['MaxEvn_id']);
						//var_dump ($row);

						// включаем услугу в выгрузку
						if ( $vkl_usl )
						{
							array_walk($row, 'ConvertFromUtf8ToCp866');
							dbase_add_record( $h, array_values($row) );
						}
					}
					dbase_close ($h);
				}

				// Проблема: по факту оказалось, что на кластере вызов exec из скрипта php, расположенному по сетевому пути не отрабатывает, поскольку система не разрешает это сделать
				// Решение: запускать архивацию из папки C:\\LocalPHP

				// Команда для выполнения, которую мы должны передать в локальный файл, если он есть
				$run_command = "".$base_name.EXPORTPATH_REGISTRY."arj32.exe a -e ".$base_name.$file_arj_name.(($include_usluga)?" ".$base_name.$file_re_usl_name:"")." ".$base_name.$file_re_name." ".$base_name.$file_re_data_name;

				//Из-за непонятных проблем при вызове arj повторяем архивацию, пока не получится.
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Выполняем архивацию.\n\r");
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} | Команда: {$run_command}\n\r");

				//
				$out = "";
				if (strpos($base_name,'/')===0) // мы находимся на кластере или сетевом пути
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} | Выполняем скрипт ".LOCALPHP."/"."registry.php."."\n\r");
					$r = exec_php(LOCALPHP."/"."registry.php",array('command'=>$run_command));
					$i = ($r == 1)?2:0;
				}
				else
				{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} | Выполняем скрипт.\n\r");
					$i=1;
					while (($out == "")) {
						$out = exec("".$run_command.'', $out);
						$this->writeLog("[" . date('Y-m-d H:i:s') . "] Попытка №{$i} \n\r");
						$i++;
					}
				}
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Архивация успешно выполнена.\n\r");

				unlink($file_re_name);
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Удаляем {$file_re_name}.\n\r");
				if ( $include_usluga )
				{
					unlink($file_re_usl_name);
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Удаляем {$file_re_usl_name}.\n\r");
				}
				unlink($file_re_data_name);
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Удаляем {$file_re_data_name}.\n\r");
				if (file_exists($base_name.$file_arj_name))
				{
					if ($data['send']!=1)
					{
						$link = $file_arj_name;
						$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} Возвращаем ссылку на файл {$link}.\n\r");
						//echo "{'success':true,'Link':'$link'}";
					}
					else
					{
						try
						{
							// Отправка письма
							//$this->sendMail($file_arj_name, $data);
							$sendresult = $this->sendMail($file_arj_name, $data);
							if ($sendresult===true)
								echo "{'success':true}";
							else
								echo "{'success':false, Error_Msg: 'Send Mail #1 Send result'}";
						}
						catch (Exception $e)
						{
							echo "{'success':false, Error_Msg: 'Send Mail #2 Exception'}";
						}
					}
					$data['Status'] = $file_arj_name;
					$this->dbmodel->SetExportStatus($data);
				}
				/*
				if ($fh = fopen($file_arj_name, "r")){
					header("Content-type: application/octet-stream");
					header("Content-Disposition: attachment; filename=arch.arj");
					$file = fread($fh, filesize($file_arj_name));
					print $file;
					fclose($fh);
				}
				*/
				else{
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} При создании архива реестра произошла ошибка. Файл архива (".$base_name.$file_arj_name.") отсутствует.\n\r");
					$this->writeLog("[" . date('Y-m-d H:i:s') . "] {$data['Registry_id']} {$out}.\n\r");
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка создания архива реестра!')));
					$data['Status'] = '';
					$this->dbmodel->SetExportStatus($data);
				}
				/*$zip=new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_re_name, "SHP_MU.dbf" );
				$zip->AddFile( $file_re_data_name, "RE_MU.dbf" );
				$zip->close();
				unlink($file_re_name);
				unlink($file_re_data_name);
				// отдаем файл клиенту
				if ($fh = fopen($file_zip_name, "r")){
					header("Content-type: application/octet-stream");
					header("Content-Disposition: attachment; filename=".$file_re_sign.".zip");
					$file = fread($fh, filesize($file_zip_name));
					print $file;
					fclose($fh);
				}
				else{
					echo "Ошибка создания архива реестра!";}
				*/
			}
			catch (Exception $e)
			{
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] При формировании файла реестра произошла ошибка .\n\r");
				$this->writeLog("[" . date('Y-m-d H:i:s') . "] ".$e->getMessage().".\n\r");
				$data['Status'] = '';
				$this->dbmodel->SetExportStatus($data);
				$this->ReturnData(array('success' => false, 'Error_Msg' => $this->error_deadlock));
			}
		}
		else
		{
			$this->writeLog("[" . date('Y-m-d H:i:s') . "] ID реестра некорректный.\n\r");
			$this->ReturnData(array('success' => true, 'Error_Msg' => toUTF('Ошибка. Не верно задан идентификатор счета!')));
			return false;
		}
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
	* comment
	*/ 	
	function loadRegistryDouble()
	{
		$data = $this->ProcessInputData('loadRegistryDouble', true);
		if($data === false) return false;
		
		$response = $this->dbmodel->loadRegistryDouble($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree()
	{
		/**
		* comment
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
	 * Входящие данные: ...POST
	 * На выходе: JSON-строка
	 * Используется: форма редактирования реестра (счета)
	 */
	function saveRegistry()
	{  
		$data  = array();

		$data = $this->ProcessInputDataArray('saveRegistry', true);
		if ($data === false) { return false; }

		$this->load->model('Options_model', 'opmodel');
		$checkRegistryAccess = $this->opmodel->checkRegistryAccess($data);
		if (!empty($checkRegistryAccess['Error_Msg'])) {
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF($checkRegistryAccess['Error_Msg'])));
			return false;
		}
		if ((!isSuperadmin()) && ($checkRegistryAccess['access'] == false))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров временно недоступно.')));
			return false;
		}
		/*if ((!isSuperadmin()) && ((isset($_POST['RegistryType_id'])) && (($_POST['RegistryType_id']=='1') || ($_POST['RegistryType_id']=='2'))))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров поликлиники и стационара временно недоступно.')));
			return false;
		}*/
		//var_dump($this->ProcessInputDataArray('saveRegistry', true));

		// Получаем сессионные переменные Task#18011
		
		// проверка статуса реестра 
		$status = $this->getRegistryCheckStatus($data);

		if (($status['RegistryCheckStatus_id'] == 1) || ($status['RegistryCheckStatus_id'] == 2)) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Переформирование реестра невозможно, т.к. текущий статус реестра:<br/>'.$status['RegistryCheckStatus_Name'].'!')));
			return;
		}
		
		//Данные у нас в многомерном массиве
		if(isset($data['Registry_id']) && count($data['Registry_id'])>0){
			foreach($data['Registry_id'] as $k=>$reg_id){
				// при добавлении дату счета берем текущую, независимо от того, передана она или нет. Вообще-то недо из запроса перед сохранением счета брать.
				 if ( $reg_id == 0 )
					$data['Registry_accDate'][$k] = ConvertDateFormat(date("d.m.Y"));
			}
		}

		//echo '<pre style="color:red!important">' . print_r($data, 1) . '</pre>';
		// Сохранение
		//$response = $this->dbmodel->saveRegistry($data);
		// Сохранение в очередь

		//Ушёл многомерный массив Task#18011
		$response = $this->dbmodel->saveRegistryQueue_array($data);

		//Task#18011
		$this->ProcessModelSave_array($response, false,  'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
        
	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry()
	{
		$data = $this->ProcessInputDataArray('saveUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUnionRegistry_array($data);
        //$this->returnData($response);
    	$this->ProcessModelSave_array($response, false,  'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}        

	/**
	 * Переформирование реестра
	 * Входящие данные: Registry_id
	 * На выходе: JSON-строка
	 * Используется: форма редактирования реестра (счета)
	 */
	function reformRegistry()
	{
		$val  = array();
		if ((!isSuperadmin()) && ($_SESSION['setting']['server']['check_registry_access']==1))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров временно недоступно.')));
			return false;
		}
		/*if ((!isSuperadmin()) && ((isset($_POST['RegistryType_id'])) && (($_POST['RegistryType_id']=='1') || ($_POST['RegistryType_id']=='2'))))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров поликлиники и стационара временно недоступно.')));
			return false;
		}*/
		set_time_limit(0);
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('reformRegistry', true);
		if ($data === false) { return false; }

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
		$val  = array();
		if ((!isSuperadmin()) && ($_SESSION['setting']['server']['check_registry_access']==1))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров временно недоступно.')));
			return false;
		}
		/*
		if ((!isSuperadmin()) && ((isset($_POST['RegistryType_id'])) && (($_POST['RegistryType_id']=='1') || ($_POST['RegistryType_id']=='2'))))
		{
			$this->ReturnData(array('success'=>false, 'Error_Msg' => toUTF('Формирование реестров поликлиники и стационара временно недоступно.')));
			return false;
		}*/
		set_time_limit(0);

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('reformErrRegistry', true);
		if ($data === false) { return false; }
		
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
		
		$sch = "dbo";
		if ($this->model_name == "RegistryUfa_model")
		{
			$sch = "r2";
		}
		$object = "RegistryQueue";
		$id = $data['Registry_id'];
		$response = $this->dbmodel->registryRevive($data, $id, $sch);

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
		
		$sch = $this->scheme;
		if ($this->model_name == "RegistryUfa_model")
		{
			$sch = "r2";
		}
		$id = $data['Registry_id'];
		$response = $this->umodel->ObjectRecordDelete($data, 'RegistryQueue', true, $id, $sch);

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
		
		$data['RegistryData_deleted'] = ($data['RegistryData_deleted']==1)?2:1;
		
		$data['EvnIds'] = array();
		
		if (!empty($data['Evn_ids'])) {
			$data['EvnIds'] = json_decode($data['Evn_ids']);
		}
		
		if (!empty($data['Evn_id'])) {
			$data['EvnIds'][] = $data['Evn_id'];
		}
		
		if (count($data['EvnIds']) < 1) {
			$this->ReturnError('Не выбран случай для удаления');
			return false;		
		}

		$response = $this->dbmodel->deleteRegistryData($data);
		if (strlen($response[0]['Error_Msg']) != 0) {
			$result = array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
		} else {
			$result = array('success' => true);
		}
		$this->ReturnData($result);
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
					$template = 'print_registry_account_stac';
				break;

				case 2:
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

				default:
					echo 'По данному реестру счет невозможно получить - функционал находится в разработке!';
					return true;
				break;
			}
		}

		$m = new money2str();
		$registry_sum_words = trim($m->work(number_format($response['Registry_Sum'], 2, '.', ''), 2));
		$registry_sum_words = strtoupper(substr($registry_sum_words, 0, 1)) . substr($registry_sum_words, 1, strlen($registry_sum_words) - 1);

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
			'Lpu_Account' => isset($response['Lpu_Account']) ? $response['Lpu_Account'] : '&nbsp;',
			'Lpu_Address' => isset($response['Lpu_Address']) ? $response['Lpu_Address'] : '&nbsp;',
			'Lpu_Director' => isset($response['Lpu_Director']) ? $response['Lpu_Director'] : '&nbsp;',
			'Lpu_GlavBuh' => isset($response['Lpu_GlavBuh']) ? $response['Lpu_GlavBuh'] : '&nbsp;',
			'Lpu_INN' => isset($response['Lpu_INN']) ? $response['Lpu_INN'] : '&nbsp;',
			'Lpu_KPP' => isset($response['Lpu_KPP']) ? $response['Lpu_KPP'] : '&nbsp;',
			'Lpu_Name' => isset($response['Lpu_Name']) ? $response['Lpu_Name'] : '&nbsp;',
			'Lpu_OKPO' => isset($response['Lpu_OKPO']) ? $response['Lpu_OKPO'] : '&nbsp;',
			'Lpu_OKVED' => isset($response['Lpu_OKVED']) ? $response['Lpu_OKVED'] : '&nbsp;',
			'Lpu_Phone' => isset($response['Lpu_Phone']) ? $response['Lpu_Phone'] : '&nbsp;',
			'LpuBank_BIK' => isset($response['LpuBank_BIK']) ? $response['LpuBank_BIK'] : '&nbsp;',
			'LpuBank_Name' => isset($response['LpuBank_Name']) ? $response['LpuBank_Name'] : '&nbsp;',
			'Month' => isset($response['Registry_Month']) && isset($month[$response['Registry_Month'] - 1]) ? $month[$response['Registry_Month'] - 1] : '&nbsp;',
			'Registry_accDate' => isset($response['Registry_accDate']) ? $response['Registry_accDate'] : '&nbsp;',
			'Registry_Num' => isset($response['Registry_Num']) ? $response['Registry_Num'] : '&nbsp;',
			'Registry_Sum' => isset($response['Registry_Sum']) ? number_format($response['Registry_Sum'], 2, '.', ' ') : '&nbsp;',
			'Registry_Sum_Words' => $registry_sum_words,
			'Year' => isset($response['Registry_Year']) ? $response['Registry_Year'] : '&nbsp;'
		);

		$this->parser->parse($template, $parse_data);

		return true;
	}

	/**
	* Получение типов полисов для формы "Человек в реестре"
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
		$this->load->database('registry', false);
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
		}
		$data['fromRegistry'] = $fromRegistry;
		// дальше лоадим рабочую базу
		unset($this->db);
		$this->load->database('default');
		$this->load->model("Utils_model", "umodel");

		$response = $this->umodel->doPersonUnion($data);
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
	* comment
	*/
	function deleteRegistryDouble()
	{
		$data = $this->ProcessInputData('deleteRegistryDouble', true);
		if ($data === false) return false;
		
		$dbConnection = getRegistryChecksDBConnection();
		if ( $dbConnection != 'default' ) {
			unset($this->db);
			$this->load->database($dbConnection);
		}
		
		switch($data['mode']) {
			case 'current':
				//здесь мы уже знаем посещение которое надо удалить с боевой $data['Evn_id']
				$response = $this->dbmodel->deleteRegistryDouble($data);
				break;
			case 'all':
				// А здесь нужно найти все посещения по конкретному Registry_id, чтобы удалить их с боевой базы
				$data['withoutPaging'] = true;
				$doubles = $this->dbmodel->loadRegistryDouble($data);
				//print_r($doubles); exit();
				if( !is_array($doubles) || count($doubles) == 0 ) {
					$this->ReturnError('Нет записей для удаления');
					return false;
				} elseif ( count($doubles) > 0 ) {
					// Удаляем все дубли
					$response = $this->dbmodel->deleteRegistryDoubleAll($data);
					$evn_vizits = array();
					foreach($doubles as $row) {
						$evn_vizits[] = $row['Evn_id'];
					}
				}
				break;
			default:
				return false;
				break;
		}

		if ( $dbConnection != 'default' ) {
			// коннектимся к дефолтной базе
			unset($this->db);
			$this->load->database('default');
		}
		$this->load->model("EvnVizit_model", "evnvpl_model");
		
		if( isset($evn_vizits) ) {
			for( $i=0; $i<count($evn_vizits); $i++ ) {
				$response = $this->evnvpl_model->deleteEvnVizitPL(array(
					'EvnVizitPL_id' => $evn_vizits[$i],
					'pmUser_id' => $data['pmUser_id'],
					'RegistryDouble' => true
				));
				if( !is_array($response) || (isset($response[0]['Error_Msg']) && strlen($response[0]['Error_Msg']) > 0) )
					break;
			}
		} else {
			if($data['mode'] == 'current') {
				$response = $this->evnvpl_model->deleteEvnVizitPL(array(
					'EvnVizitPL_id' => $data['Evn_id'],
					'pmUser_id' => $data['pmUser_id'],
					'RegistryDouble' => true
				));
			}
		}
		$this->ProcessModelSave($response)->ReturnData();
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
	* Task#18011
	* Данные приходят в многомерном массиве - необходима корректировка (сейчас изсменено только название метода=))
	* Необходима ручная валидация данных + формирование строк с текстом ошибок по конкретному реестру
	*/
	function ProcessInputDataArray($RuleName, $GetSessionParams = true, $CloseSession = true, $PreferSession = false, $ParamsFromPost = false, $convertUTF8 = true) {
		$data = array();
		
	  	// Заменяем $_POST на $_GET, если $_POST пустой.
		if(!$ParamsFromPost){
			if(empty($_POST)&&(!empty($_GET))){
				$_POST = $_GET;
			}
		}
		$data = $_POST;
		// Получаем сессионные переменные
		If ( $GetSessionParams && (!$PreferSession)) {
			$data = array_merge($data, getSessionParams());                
		}
		
		
		
		if ( isset($RuleName) ) {
			$err = $this->getInputParamsArray($data, $this->inputRules[ $RuleName ], $convertUTF8);
			if ( strlen($err) > 0 ) {
				echo json_return_errors($err);
				return false;
			}
		}
		If ( $GetSessionParams && $PreferSession)
			$data = array_merge(getSessionParams(), $data);

		// Всегда переносим сессию в переменную
		// Сделано согласно http://redmine.swan.perm.ru/issues/4439#note-8. Соответственно в моделях используем $data['session'] вместо $_SESSION
		if (isset($_SESSION)) {
			$data['session'] = $_SESSION;
			// TODO: Здесь нужно отсечь из переменной то, что никогда не будет использоваться далее
		}
		if ( $CloseSession )
			session_write_close();
		$this->InData = $data;
	
		return $data;
	}
	/**
	* 
	* END Task#18011
	*
	*/

	/**
	* Task#18011
	* аргументы используются частично, т.к. нет необходимости менять кодировку
	*/ 
	function ProcessModelSave_array($response, $ConvertToUTF8 = true, $ErrMsg = 'При записи данных произошла ошибка.', $ProcessFunc = NULL) {

        
        //echo '<pre>' . print_r($response, 1) . '</pre>';
        
        if(is_array($response)) {
            array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
            $response = json_encode($response);
        }

		$response = strtr($response, array('"['=>'[', ']"'=>']'));
        $response = toUTF($response);
		$res = json_decode(stripslashes($response));
	
		$this->OutData = array();
		// Если запрос вернул false, просто возвращаем пустой массив
		if ( $res=== false ) {
			return $this;
		}
        	 
		if ( is_array($res) && (count($res) > 0) ) {
			$this->OutData = $res;
		}
		else {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($ErrMsg)
			);
		}

		return $this;
	}
	/**
	* 
	* END Task#18011
	* 
	*/
	
	//Перенёс из InputHelpers.php
	/**
	* Task#18011 
	* Модификация для пакетной работы с реестрами 
	*/
	function getInputParamsArray(&$data, $inputRules, $convertUTF8 = true, $inData = null) {
		// $data = array(); // очищаем массив перед заполнением

		if (!$inData) {
			$inData = $_POST;
			$useCiCheck = true;
		} else {
			$useCiCheck = false;
		}

		$err = ""; // строка с описанием ошибки
		$CI = & get_instance();
		$CI->load->helper("Date");
		$CI->load->library("form_validation");
		$CI->form_validation->set_rules($inputRules);
		If ($convertUTF8) {
			array_walk($inData, 'ConvertFromUTF8ToWin1251');
		}

	
		//если имеем дело с постом,
		if ($useCiCheck) {
			//Сначала проверяем встроенным валидаторов CI
			if (count($inData)>0 && $CI->form_validation->run() == FALSE) {
				$err = validation_errors();
				return $err;
			}
		}

		foreach ( $inputRules as $rule ) { // проходим по всем правилам

			switch ( $rule['type'] ) { //
				case 'id': // идентификатор, целое число больше нуля
					foreach($inData[$rule['field']] as $k=>$inD){
						//echo  'Поле: '.$rule['field'].'<br/>';
						//echo  'Тип: '.$rule['type'].'<br/>';
						//echo  'Значение: '.$inD.'<hr/>';
						
						if ( !isset($inD) || $inD == '' ) { // если значение не передано через POST
							// если данные уже заданы, например в сессии, то не надо брать не значение по дефолту, 
							// и не обнулять значение - просто оставляем, то что есть 
							if ((!isset($data[$rule['field']][$k])) || (empty($data[$rule['field']][$k])) || ($data[$rule['field']][$k]==0))
							{
								if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
									$data[$rule['field']][$k] = $rule['default'];
								}
								else { // иначе по умолчанию ставим NULL
									$data[$rule['field']][$k] = NULL;
								}
							}
						}
						else {
							// Обходим баг в IE, преобразуем текстовое значение null
							if ( strtolower($inD) == 'null' ) {
								$data[$rule['field']][$k] = NULL;
							}
							else {
								if ( (!is_numeric($inD)) || ($inD < 0) ) {
									 $err .= "Неверный идентификатор в поле " . $rule['field'] . " (error) <br/>";
								}
								else {
									if ( $inD > 0 ) {
										$data[$rule['field']][$k] = $inD;
									}
									else {
										$data[$rule['field']][$k] = NULL;
									}
								}
							}
						}
				}
				break;
			
				case 'string': // строка

					foreach($inData[$rule['field']] as $k=>$inD){
						//echo  'Поле: '.$rule['field'].'<br/>';
						//echo  'Тип: '.$rule['type'].'<br/>';
						//echo 'Значение: '.$inD.'<hr/>';
						
						if ( !isset($inD) || (isset($inD) && $inD=='' && !(isset($rule['rules']) && strpos($rule['rules'], 'notnull') !== false)) ) { // если значение не передано через POST
							if (isset($rule['default'])) { // если задано значение по умолчанию то берем его
								$data[$rule['field']][$k] = $rule['default'];
							}
							else { // иначе по умолчанию ставим NULL
								$data[$rule['field']][$k] = NULL ;
							}
						}
						else
						// Проверяем переданное значение на соответствие числу
						if ( isset($inD) && is_string($inD) )
							$data[$rule['field']][$k] = $inD;
						else {
							$err .= "Неверное строковое значение в поле ".$rule['label']."<br/>";
						}
						// проверяем правило ban_percent (удаление знаков процента)
						if ( isset($rule['rules']) && strpos($rule['rules'], 'ban_percent') !== false && strlen($data[$rule['field']][$k]) > 0 && strpos($data[$rule['field']][$k], '%') !== false )
						{
							// удаляем везде знаки процента
							$data[$rule['field']][$k] = trim(str_replace('%', '', $data[$rule['field']][$k]));
							// проверяем, пустая ли строка получилась
							if ( strlen($data[$rule['field']][$k]) == 0 )
								// если значение обязательно
							if ( isset($rule['rules']) && ((strpos($rule['rules'], 'required') !== false) || (strpos($rule['rules'], 'min_length') !== false)) )
							{
								if ( isset($rule['default']) && strlen($rule['default']) > 0 )
									$data[$rule['field']][$k] = $rule['default'];
								else
									$err .= "Поле ".$rule['label']." обязательно для заполнения(использование знака процента недопустимо).\n";							
							}
							else
								if ( isset($rule['default']) )
									$data[$rule['field']][$k] = $rule['default'];
								else
									$data[$rule['field']][$k] = NULL;
						}
						// rules => spec_chars
						if ( isset($rule['rules']) && strpos($rule['rules'], 'spec_chars') !== false && strlen($data[$rule['field']][$k]) > 0 )
						{
							$data[$rule['field']][$k] = htmlspecialchars($data[$rule['field']][$k]);
						}
					} 
					break;

				case 'date'://дата
					foreach($inData[$rule['field']] as $k=>$inD){
						//echo  'Поле: '.$rule['field'].'<br/>';
						//echo  'Тип: '.$rule['type'].'<br/>';
						//echo  'Min: '.MIN_CORRECT_DATE.'<br/>';
						//echo  'Значение: '.ConvertDateFormat($inD).'<hr/>';

						if ( !isset($inD) || strlen($inD) == 0 ) { // если значение не передано через POST
							if (isset($rule['default'])) { // если задано значение по умолчанию то берем его
								$data[$rule['field']][$k] = $rule['default'];
							}
							else { // иначе по умолчанию ставим NULL
								$data[$rule['field']][$k] = NULL;
							}
						}
						else
						{
							// Проверяем переданное значение на соответствие дате в формате dd.mm.yyyy
							if ( isset($inD) && $inD == '__.__.____' ) {
								if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							 		$data[$rule['field']][$k] = $rule['default'];
								}
								else { // иначе по умолчанию ставим NULL
									$data[$rule['field']][$k] = NULL;
								}
							}
							else {
								if ( CheckDateFormat( $inD ) == 0 || $inD == '' ) {
									if (isset($rule['convertIntoObject']) && $rule['convertIntoObject']) {
										try {
											$data[$rule['field']][$k] = new DateTime($inD[0]);
										} catch (Exception $e) {
											$err .= "Неверно задана дата в поле ".$rule['label']."<br/>";
											$data[$rule['field']][$k] = NULL;
										}
								}
									else {
										$data[$rule['field']][$k] = ConvertDateFormat($inD);
							
										if ( $data[$rule['field']][$k] < MIN_CORRECT_DATE ) {
											$err .= "Неверно задана дата в поле ".$rule['label']."<br/>";
											$data[$rule['field']][$k] = NULL;
										}
									}
								}
								else {
									$err .= "Неверное значение даты в поле ".$rule['label']." (".$inD." )<br/>";
							}
						}
					}
				
						if ( isset($rule['maxValue']) && isset($data[$rule['field']][$k]) ) {
							if ( $rule['maxValue'] != 'now' ) {
								$dt = strtotime($rule['maxValue']);
							} else {
								$dt = strtotime(date('Y-m-d'));
						}
					
							if ( strtotime($data[$rule['field']][$k]) > $dt ) {
								$err .= "Выход за границы диапазона в поле ".$rule['label']."\n";
						}
					}
						if ( isset($rule['minValue']) && isset($data[$rule['field']][$k]) ) {
							if ( $rule['minValue'] != 'now' ) {
								// Признак того, что значением является другое значение из $inData
								$field_mark = 'field:'; 
								// За ним должно следовать имя поля формы
								$field_name = substr($rule['minValue'], strstr($rule['minValue'], $field_mark) + strlen($field_mark));
								$field_value = $inD[trim($field_name)];
								if ( $field_value ){
									$dt = strtotime($field_value);
								} else {
									$dt = strtotime($rule['maxValue']);
							}
							} else {
								$dt = strtotime(date('Y-m-d'));
						}
							if ( strtotime($data[$rule['field']][$k]) < $dt ) {
								$err .= "Выход за границы диапазона в поле ".$rule['label']."\n";
						}
					}
			}
				break;

				default:
					$err .= "Неправильный тип поля ".$rule['field']." - ".$rule['type']."\n";
				break;
		}

			//Если значение пришедшее от клиента пустое и есть поле в сессии, из которого можно взять значение
			if ( !isset($data[$rule['field']]) && isset($rule['session_value']) ) {
				if ( isset($_SESSION[$rule['session_value']]) ) {
					$data[$rule['field']] = $_SESSION[$rule['session_value']];
			}
		}
	}
		return $err;
}

	/**
	 * 
	 * END Task#18011
	 * 
	 */ 

	/**
	 * Обрабатывает данные возвращенные моделью в виде мульти-списка
	 * Возвращает сам объект, что позволяет делать цепочки
	 *
	 * @access public
	 * @param array|boolean $response Массив данных, полученный при выполнении запроса и возвращенный моделью, 
	 * или значение false в случае если запрос по каким-то причинам не удалось выполнить (не надо выполнять).
	 * @param boolean $ConvertToUTF8 По умолчанию: true. Установите false, если не требуется конвертировать данные в UTF формат.
	 * @param boolean $AllowBlank По умолчанию: false. При вызове с значением по умолчанию функция возвращает ошибку 
	 * при отсутствии данных в ответе. В случае, если нужно, чтобы вернулся пустой массив данных, установите значение true.
	 * @param string $ErrMsg Сообщение, которое отдается на клиент при ошибке запроса. По умолчанию "При запросе возникла ошибка.".
	 * @param callable $ProcessFunc Функция, осуществляющая дополнительную обработку строки данных, возвращает обработанную строку
	 * 
	 * @return swController Возвращает объект текущего класса.
	 */
	function ProcessModelMultiList($response, $ConvertToUTF8 = true, $AllowBlank = false, $ErrMsg = 'При запросе возникла ошибка.', $ProcessFunc = NULL, $IsAllData = false) {
		$this->OutData = array();
		// Если запрос вернул false, просто возвращаем пустой массив
		if ( $response === false ) {
			return $this;
		}
		if ( is_array($response['data']) && ((count($response['data']) > 0) || (count($response['data']) == 0 && $AllowBlank)) ) {
			$this->OutData['data'] = array();
			foreach ($response['data'] as $row) {
				if ( isset($ProcessFunc) ) {
					$row = $ProcessFunc($row, $this);
				}
				if ( $ConvertToUTF8 )
					array_walk($row, 'ConvertFromWin1251ToUTF8');
				$this->OutData['data'][] = $row;
			}
			if (isset($response['totalCount'])) {
				$this->OutData['totalCount'] = $response['totalCount'];
			}
		} else {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($ErrMsg)
			);
		}
		return $this;
	}
}
