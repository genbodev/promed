<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MongoDB - контроллер для работы с MongoDB
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MongoDB
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Markoff A.A. <markov@swan.perm.ru>
* @version      июнь.2012
*
* @property MongoDBWork_model $dbmodel
*/

class MongoDBWork extends swController {
	public $inputRules = array(
		'getData' => array(
			array(
				'field' => 'object',
				'label' => 'Таблица',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'where',
				'label' => 'Условие',
				'rules' => '',
				'type' => 'string'
			)
			// Остальные данные возьмем из $_POST, потому что они для каждого справочника свои
		),
		'getDataAll' => array(
			array(
				'field' => 'data',
				'label' => 'Справочники',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getDataFromObject' => array(
			array(
				'field' => 'object',
				'label' => 'Объект',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'limit',
				'label' => 'Лимит',
				'rules' => '',
				'type' => 'int',
				'default' => 0
			)
		),
		'getLocalDBFiles' => array(
			array(
				'field' => 'LocalDBVersion_id',
				'label' => 'Версия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLocalDbList' => array(
			array(
				'field' => 'LocalDbList_name',
				'label' => 'Название справочника',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getLocalDbListRecord' => array(
			array(
				'field' => 'LocalDbList_id',
				'label' => 'Справочник',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveLocalDbList' => array(
			array('field' => 'LocalDbList_id', 'label' => 'Справочник', 'rules' => '', 'type' => 'id'),
			array('field' => 'LocalDbList_name', 'label' => 'Наименование справочника', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LocalDbList_prefix', 'label' => 'Префикс', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LocalDbList_nick', 'label' => 'Ник', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LocalDbList_schema', 'label' => 'Схема', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LocalDbList_key', 'label' => 'Ключевое поле', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LocalDbList_module', 'label' => 'Модуль', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LocalDbList_sql', 'label' => 'SQL-код', 'rules' => '', 'type' => 'string'),
			array('field' => 'LocalDbList_Descr', 'label' => 'Русское наименование', 'rules' => 'trim', 'type' => 'string')
		),
		'deleteLocalDbList' => array(
			array(
				'field' => 'id',
				'label' => 'Справочник',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getRegionalLocalDbList' => array(
			array('field' => 'LocalDbList_id', 'label' => 'Справочник', 'rules' => 'required', 'type' => 'id')
		),
		'getRegionalLocalDbListRecord' => array(
			array('field' => 'RegionalLocalDbList_id', 'label' => 'Запрос справочника', 'rules' => 'required', 'type' => 'id')
		),
		'deleteRegionalLocalDbList' => array(
			array('field' => 'id', 'label' => 'Запрос справочника', 'rules' => 'required', 'type' => 'id')
		),
		'saveRegionalLocalDbList' => array(
			array('field' => 'LocalDbList_id', 'label' => 'Справочник', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegionalLocalDbList_id', 'label' => 'Идентификатор запроса справочника', 'rules' => '', 'type' => 'id'),
			array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegionalLocalDbList_Sql', 'label' => 'SQL-код', 'rules' => '', 'type' => 'string'),
			array('field' => 'RegionalLocalDbList_PgSql', 'label' => 'SQL-код', 'rules' => '', 'type' => 'string'),
		),
		'createVersion' => array(
		),
		'fixedVersion' => array(
			array(
				'field' => 'tables',
				'label' => 'Справочники',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'LocalDBVersion_id',
				'label' => 'Версия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteVersion' => array(
			array(
				'field' => 'id',
				'label' => 'Версия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveData' => array(
			array(
				'field' => 'object',
				'label' => 'Объект',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'where',
				'label' => 'Условие',
				'rules' => '',
				'type' => 'string'
			)
		),
		'sendVersionMQmessage' => array(
			array(
				'field' => 'tables',
				'label' => 'Справочники',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'LocalDBVersion_id',
				'label' => 'Версия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadDirectoryListGrid' => array(
			array('field' => 'Directory_Name', 'label' => 'Наименование справочника', 'rules' => '', 'type' => 'string' )
		),
		'getDirectoryFields' => array(
			array('field' => 'Directory_Name', 'label' => 'Наименование справочника', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'Directory_Schema', 'label' => 'Схема', 'rules' => '', 'type' => 'string', 'default' => 'dbo' ),
		),
		'getDirectoryData' => array(
			array('field' => 'start', 'label' => 'начальная запись', 'rules' => '', 'type' => 'int', 'default' => 0 ),
			array('field' => 'limit', 'label' => 'всего записей', 'rules' => '', 'type' => 'int', 'default' => 50 ),
			array('field' => 'sort','label' => 'Поле для сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'dir','label' => 'Направление сортировки','rules' => 'trim','type' => 'string'),
			array('field' => 'Directory_Name', 'label' => 'Наименование справочника', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'Directory_Schema', 'label' => 'Схема', 'rules' => '', 'type' => 'string', 'default' => 'dbo' ),
			array('field' => 'filterElementsByName', 'label' => 'Фильтр элементов по наименованию', 'rules' => '', 'type' => 'string' ),
            array('field' => 'directoryContentSearchPanelType', 'label' => 'Тип поиска для справочников НСИ', 'rules' => '', 'type' => 'string' )
		),
		'getFormDataForDirectoryEditWindow' => array(
			array('field' => 'scheme', 'label' => 'Схема', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'table', 'label' => 'Наименование таблицы', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'keyName', 'label' => 'Наименование ключа', 'rules' => '', 'type' => 'string' ),
			array('field' => 'keyValue', 'label' => 'значение ключа', 'rules' => '', 'type' => 'id' )
		),
		'saveDirectoryRecord' => array(
			array('field' => 'scheme', 'label' => 'Схема', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'table', 'label' => 'Наименование таблицы', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'keyName', 'label' => 'Наименование первичного ключа таблицы', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'fieldsData', 'label' => 'json-строка {поле: значение}', 'rules' => 'required', 'type' => 'string' )
		),
		'deleteDirectoryRecord' => array(
			array('field' => 'scheme', 'label' => 'Схема', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'table', 'label' => 'Наименование таблицы', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'keyName', 'label' => 'Наименование первичного ключа таблицы', 'rules' => 'required', 'type' => 'string' ),
			array('field' => 'keyValue', 'label' => 'Значение первичного ключа таблицы', 'rules' => 'required', 'type' => 'id' )
		),
        'loadDirectoryFieldList' => array(
            array(
                'field' => 'Directory_Name',
                'label' => 'Наименование справочника',
                'rules' => 'required',
                'type' => 'string'
            ),
            array(
                'field' => 'Directory_Schema',
                'label' => 'Схема справочника',
                'rules' => 'required',
                'type' => 'string'
            ),
            array(
                'field' => 'Group',
                'label' => 'Признак связанной таблицы *Group',
                'rules' => '',
                'type' => 'string'
            )
        ),
        'loadCombosStore' => array(
            array(
                'field' => 'LocalDirectory_ImportPath',
                'label' => 'Путь к файлу справочника',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'LocalDirectory_FileType',
                'label' => 'Тип загружаемого файла',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'LocalDirectory_FileMask',
                'label' => 'Маска файла',
                'rules' => '',
                'type' => 'string'
            )
        ),
        'saveDirectoryChanges' => array(
            array(
                'field' => 'LocalDirectory_ImportPath',
                'label' => 'Путь к файлу справочника',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'LocalDirectory_FileType',
                'label' => 'Тип загружаемого файла',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'LocalDirectory_ComboValues',
                'label' => 'Соответствие полей',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'LocalDirectory_isPK',
                'label' => 'Поля первичного ключа',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'Directory_Name',
                'label' => 'Название обновляемого справочника',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'mode',
                'label' => 'режим просмотра',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'start',
                'label' => 'начальная запись',
                'rules' => '',
                'type' => 'int',
                'default' => 0
            ),
            array(
                'field' => 'limit',
                'label' => 'всего записей',
                'rules' => '',
                'type' => 'int',
                'default' => 50
            )
        ),
	);
	
	private $inputData = array();

	private $moduleMethods = [
		'getData',
		'getDataAll'
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->config->load('mongodb');
		$this->load->model('MongoDBWork_model', 'dbmodel');
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
		}
		$this->init();
	}

	/**
	 * Дополнительная инициализация
	*/
	private function init() {
		$method = $this->router->fetch_method();
		if (!$this->usePostgreLis || !in_array($method, $this->moduleMethods)) {
			$this->load->database();
		}
	}
	
	/**
	 * Проверка версии справочников в mongo и основной БД
	 */
	function checkVersion() {
		$response = [
			'success' => true,
			'Message' => 'Версии справочников совпадают'
		];
		
		$this->load->model("Options_model", "opmodel");
		
		if ($response['success']) {
			//Проверка соответвия региона в сессии и в подключенной БД
			$db_region_number = $this->opmodel->getDBRegionNumber();
			$app_region_number = getRegionNumber();
			
			if ($db_region_number != $app_region_number) {
				$response = [
					'success' => false,
					'Message' => "Ошибка региональности. На веб-сервере установлен регион: {$app_region_number}, на текущей БД регион: {$db_region_number}, необходимо провести работы по устранению ошибки."
				];
			}
		}
		
		if ($response['success']) {
			//Проверка соответствия номера версии локальных справочников в mongo и в основной БД
			$db_version = $this->opmodel->getLocalDBVersion();
			$mongo_version = $this->dbmodel->getVersion();
			
			if ($mongo_version != $db_version) {
				$response = [
					'success' => false,
					'Message' => 'Версии справочников не совпадают, необходима актуализация данных.'
				];
			}
		}
		
		$this->ReturnData($response);
	}

	/**
	 * Проверка доступности библиотеки MongoDB
	 */
	function checkMongo() {
		$response = array(
			'success' => true,
			'Message' => ''
		);
		$mongodb = checkMongoDb();
		if (empty($mongodb)) {
			$response = array(
				'success' => false,
				'Message' => toUtf('Библиотека MongoDB не доступна на данном вебсервере! <br/> Необходимо проинформировать технический отдел.')
			);
		}
		$this->ReturnData($response);
	}
	/**
	 * Просто эксперименты
	 */
	function checked() {
		
		var_dump($this->mongo_db->select_collection('sysVersion'));
		var_dump($this->mongo_db->select_collection('Session'));
		
		$row = $this->mongo_db->where(array('_id'=>0))->get('paytype');
		print_r($row);
		if (!is_array($row)) {
			$value = null;
		} else {
			if (isset($row['paytype_name'])) {
				$value = $this->compress ? gzuncompress($row['paytype_name']) : $row['paytype_name'];
			} else {
				$value = null;
			}
		}
		print $value;
	}
	/**
	 * Счетчик в MongoDB. Параметры: объект, новый счетчик в новый день, Lpu_id, MedService_id, минимальное значение, максимальное значение
	 */
	function inc() {
		$this->load->library('swMongoExt');
		$code = $this->swmongoext->generateCode('samples','day',array('Lpu_id'=>1, 'MedService_id'=>null));
		print $code;
	}
	

	/**
	 * Функция создает все необходимые справочники в памяти
	 */
	protected function _createData() {
		// поскольку первоначальное заполнение БД может выполняться достаточно долго, ставим интервал ожидания 30 минут
		set_time_limit(1800);
		$this->load->library('textlog', array('file'=>'MongoDB.log', 'logging' => false));
		$this->textlog->add('[createData] Start');
		
		$this->load->helper('MongoDB');
		// при несовпадении версий обновляем новые справочники
		// и если все хорошо обновилось изменяем версию
		// Если суперадмин, тогда можем сгенерировать все необходимые справочники
		$errors = array();

		if ( isSuperadmin() ) {
			// todo: временный код для перехода на версию с наименованиями справочников в нижнем регистре
			try {
				$r = $this->mongo_db->get('PayType');
				if ( is_array($r) && count($r) > 0 ) { // если такой объект существует и там есть данные
					$this->dbmodel->drop_db(); // прибиваем текущую БД MongoDB
				}
			}
			catch ( SoapFault $e ) {
				$this->ReturnData(array('Error_Msg' => $e->getMessage()));
				return false;
			}

			// Версия в БД
			$this->load->model("Options_model", "opmodel");
			$db_ver = $this->opmodel->getLocalDBVersion();
			//$mongo_ver = $this->setVersion(1);

			// храним в бд информацию о версии 
			$mongo_ver = $this->dbmodel->getVersion();

			if ( $mongo_ver < $db_ver ) { // Если версия справочников в MongoDB младше версии в БД, то загружаем список изменений
				$this->load->model('SprLoader_model', 'sprmodel');

				// Определяем с какой версии брать изменения
				$data['version'] = $mongo_ver;

				// TODO: надо сделать правильный выбор режима открытия формы 
				// Режим открытия формы
				$data['mode'] = 'promed';
				$data['region'] = $this->opmodel->getRegionNumber();

				// Получаем список изменившихся таблиц с версии $mongo_ver по текущую версию $db_ver
				$tables = $this->sprmodel->getSyncTablesAll($data);

				if ( is_array($tables) && !isset($tables[0]['Error_Msg']) ) { // Если список присутствует
					$i = 0;

					foreach ( $tables as $key => $val ) { // то открываем цикл по списку таблиц
						// получаем нужные данные по каждой таблице
						$i++;
						$this->textlog->add('[createData] Получаем справочник ' . $val['SyncTable_name'] . ' из БД');
						$response = $this->dbmodel->getObjectData($val, true);

						// поскольку MongoDB регистрозависимая, переводим название таблицы в нижний регистр
						$_name = strtolower($val['SyncTable_name']);

						if ( $this->mongo_db->fields_uncase ) { // Если преобразуем названия полей в нижний регистр
							$_key = strtolower($val['SyncTable_key']);
						}
						else {
							$_key = $val['SyncTable_key'];
						}

						// загоняем данные в монгодб предварительно очищая таблицу 
						//$this->mongo_db->delete_all($val['SyncTable_name']); // удаление данных
						$this->textlog->add('[createData] Предварительно перед заливкой удаляем справочник ' . $_name . ' (даже если данных для обновления нет)');
						$this->mongo_db->drop_collection($this->mongo_db->dbname, $_name); // удаление таблицы
						
						$this->textlog->add('[createData] Загружаем справочник ' . $_name . ' (' . $_key . ')');

						if ( is_array($response) ) {
							if ( !empty($response['Error_Msg']) ) {
								// Собираем ошибки
								array_push($errors, $val['SyncTable_name'] . ': ' . $response['Error_Msg']);
							}
							else {
								// Собираем ошибки
								array_push($errors, $val['SyncTable_name'] . ': ' . 'Исходный запрос не вернул данных');
							}
						}
						else {
							// Фетчим записи
							$key_empty = false;
							$recordsCount = 0;
							$insertedIds = [];

							$toMongoDb = array();
							// цикл по данным
							while ( $v = $response->_fetch_assoc() ) {
								$recordsCount++;
								$vlc = array_change_key_case($v);

								// Если преобразуем названия полей в нижний регистр
								if ( $this->mongo_db->fields_uncase ) {
									$v = $vlc;
								}

								// TODO: Надо ЕЩЁ подумать над большими и маленькими буквами в названии полей
								if ( isset($vlc[$_key]) ) {
									$v['_id'] = (float)$v[$_key];
									if (!in_array($v['_id'], $insertedIds)) {
										$insertedIds[] = $v['_id'];
										
										array_walk($v, 'ConvertFromWin1251ToUTF8');
										array_walk($v, 'convertFieldToInt');

										foreach($v as $field => $value) {
											if ($this->usePostgre && !empty($value) && preg_match('/_.+(date|dt)$/', $field)) {
												//Из postgre дата возвращается в виде строки. В mongo её нужно сохранить в виде объекта
												try {
													$v[$field] = date_create($value);
												} catch(Exception $e) {
													//не изменять значение
												}
											}
										}

										$toMongoDb[] = $v;

										if (count($toMongoDb) > 1000) {
											// Загоняем строки в БД
											$this->textlog->add('[createData] инсертим записи в mongodb');
											$this->mongo_db->batch_insert($_name, $toMongoDb);
											$toMongoDb = array();
										}
									} else {
										array_push($errors, $val['SyncTable_name'] . ': Обнаружено дублирование записи с идентфикатором ' . $v['_id'] . '.');
									}
								}
								else {
									$key_empty  = true;
								}
							}

							if (count($toMongoDb) > 0) {
								// Загоняем строки в БД
								$this->mongo_db->batch_insert($_name, $toMongoDb);
								unset($toMongoDb);
							}

							if ( $key_empty ) {
								// Собираем ошибки
								array_push($errors, $val['SyncTable_name'] . ': Требуемый первичный ключ ' . $val['SyncTable_key'] . ' в запросе отсутствует (должно быть заполнено поле LocalDbList_sql).');
							}

							$this->textlog->add('[createData] Количество записей: ' . $recordsCount);
						}

						/* вывод из монгодб */
						/*
						$spr = $this->mongo_db->get($val['SyncTable_name']);
						foreach ($spr as $row=>$str ) {
							array_walk($str, 'ConvertFromUTF8ToWin1251');
							print_r($str);
						}
						*/
					}
				}

				$this->dbmodel->setVersion($db_ver);
			}
			else{
				$this->dbmodel->setVersion($db_ver);
				array_push($errors, 'Версия локальных справочников в MongoDB была обновлена в соответствии с версией в БД. Пожалуйста, сгенерируйте новую версию локальных справочников.');
			}
		}

		$this->textlog->add('[createData] Done');

		return $errors;
	}
	
	/**
	 * Функция получает данные справочника из БД MongoDB согласно запросу
	 */
	function getData() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getData', false);
		if ($data === false) { return false; }

		// Пишем в лог, чтобы узнать какие справочники запрашиваются отдельным запросом
		// todo: Нужно будет периодически включать логирование, чтобы убрать ненужные обращения getData
		/*
		$this->load->library('textlog', array('file'=>'client_getData_'.date('Y-m-d').'.log'));
		$this->textlog->add( $data['object'].' : '.$data['where'] );
		*/
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getData($data, $_POST);
		$this->ReturnData($response);
	}

	/**
	 * Функция получает данные нескольких справочников из БД MongoDB согласно запросу
	 */
	function getDataAll() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getDataAll', false);

		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getDataAll($data, $_POST);
		$this->ReturnData($response);
	}

	/**
	 * Функция получает данные из БД для определенного объекта требует доработки
	 */
	function getDataFromObject() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getDataFromObject', false);
		$post = $_POST;
		unset($post['object']);
		unset($post['limit']);
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getDataFromObject($post, $data['object'], null, $data['limit']);
		$this->ReturnData($response);
	}

	/**
	 * Функция получает 25 последних версий
	 */
	function getLocalDBVersion() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getDataFromObject', false);
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getLocalDBVersion($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция получает список таблиц-справочников конкретной версии $data['LocalDBVersion_id']
	 */
	function getLocalDBFiles() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getLocalDBFiles', false);
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getLocalDBFiles($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Функция дропает БД, которая прописана в config/mongodb.php
	 */
	function getLocalDbDrop() {
		if (isSuperadmin()) {
			$this->load->model("MongoDBWork_model", "dbmodel");
			// Название БД берем из конфига 
			$config = & get_config();
			if (isset($config['mongo_db']) && strlen($config['mongo_db'])>0) {
				// прибиваем БД в Монго
				$this->mongo_db->drop_db($config['mongo_db']);
				echo json_encode(array('success'=>true));
			}
			else {
				echo json_encode(array('success'=>false, 'Error_Msg' => toUtf('В конфиге не указано БД MongoDB')));
			}
		} else {
			echo json_encode(array('success'=>false));
		}
	}

	/**
	 * Функция дропает коллекции в БД, которая прописана в config/mongodb.php
	 */
	function getLocalDbTablesDrop() {
		if (isSuperadmin()) {
			$this->load->model("MongoDBWork_model", "dbmodel");
			// Название БД берем из конфига
			$config = & get_config();
			$notDelTables = array('syscache','sysgen');
			$session_table = (isset($config['mongodb_session_settings']) && isset($config['mongodb_session_settings']['table']))?$config['mongodb_session_settings']['table']:'Session';
			$notDelTables[] = strtolower($session_table);
			//var_dump($notDelTables);die();
			if (isset($config['mongo_db']) && strlen($config['mongo_db'])>0) {
				// прибиваем БД в Монго
				$collections = $this->mongo_db->list_collections($config['mongo_db']);
				foreach($collections as $collection) {
					$name = $collection->getName();
					if (!in_array(strtolower($name),$notDelTables)) {
						$collection->drop();
					}
				}
				echo json_encode(array('success'=>true));
			}
			else {
				echo json_encode(array('success'=>false, 'Error_Msg' => toUtf('В конфиге не указано БД MongoDB')));
			}
		} else {
			echo json_encode(array('success'=>false));
		}
	}

	/**
	 * Функция получает список всех справочников
	 */
	function getLocalDbList() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getLocalDbList', false);
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getLocalDbList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция получает данные одного справочника по Id
	 */
	function getLocalDbListRecord() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getLocalDbListRecord', false);
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getLocalDbListRecord($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение данных в списке "локальных" справочников LocalDbList
	 */
	function saveLocalDbList() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('saveLocalDbList', true);
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->saveLocalDbList($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	 * Удаление записи из списка "локальных" справочников LocalDbList
	 */
	function deleteLocalDbList() {
		$data = $this->ProcessInputData('deleteLocalDbList', true);
		if($data) {
			$response = $this->dbmodel->deleteLocalDbList($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		}
	}

	/**
	 * Функция получает список всех региональных запросов для справочника
	 */
	function getRegionalLocalDbList() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getRegionalLocalDbList', false);
		if ($data === false) { return false; }
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getRegionalLocalDbList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция получает данные запроса справочника по Id
	 */
	function getRegionalLocalDbListRecord() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getRegionalLocalDbListRecord', false);
		if ($data === false) { return false; }
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->getRegionalLocalDbList($data,true);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение данных запроса в списке запросов "локальных" справочников RegionalLocalDbList
	 */
	function saveRegionalLocalDbList() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('saveRegionalLocalDbList', true);
		if ($data === false) { return false; }
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->saveRegionalLocalDbList($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}

	/**
	 * Удаление записи из списка запросов "локальных" справочников RegionalLocalDbList
	 */
	function deleteRegionalLocalDbList() {
		$data = $this->ProcessInputData('deleteRegionalLocalDbList', true);
		if($data) {
			$response = $this->dbmodel->deleteRegionalLocalDbList($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		}
	}


	/**
	 *	Получение названий полей конкретного справочника
	 */
	function loadDirectoryFieldList() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		$data = $this->ProcessInputData('loadDirectoryFieldList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDirectoryFieldList($data);
        //var_dump($response);die;
        //return $response;
        //ReturnData($response);
        $this->ProcessModelList($response, true, true)->ReturnData();

		//return true;
	}


	/**
     *  Получение store комбобоксов для определения соответствия полей в БД полям в загружаемом справочнике
     *  Входящие данные: $_file импорта
     *  На выходе: JSON-строка
     *  Используется: форма поиска диагноза
	 */
	function loadCombosStore()
	{
		$data = $this->ProcessInputData('loadCombosStore', true);
		if ($data === false) { return false; }

        $this->load->helper('Xml_helper');
        set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
        $upload_path = './'.DIRECTORYPATH;

        if (!empty($_SESSION['Org_id'])) {
            $upload_path = './'.DIRECTORYPATH.$_SESSION['Org_id'].'/';
        }

        $types = 'arj|xml|dbf|rar|zip';
        $allowed_types = explode('|',$types);

        if (!isset($_FILES['LocalDirectory_ImportFile'])) {
            //$this->PhpLog_model->insertPhpLog('endImportingRegistryNotSuccessful');
            $this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Не выбран файл реестра!') ) );
            return false;
        }

        if (!is_uploaded_file($_FILES['LocalDirectory_ImportFile']['tmp_name']))
        {
            $error = (!isset($_FILES['LocalDirectory_ImportFile']['error'])) ? 4 : $_FILES['LocalDirectory_ImportFile']['error'];
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
            //$this->PhpLog_model->insertPhpLog('endImportingRegistryNotSuccessful');
            $this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => iconv('windows-1251', 'utf-8', $message)));
            return false;
        }

        // Тип файла разрешен к загрузке?
        $x = explode('.', $_FILES['LocalDirectory_ImportFile']['name']);
        $file_data['file_ext'] = end($x);
        if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
            //$this->PhpLog_model->insertPhpLog('endImportingRegistryNotSuccessful');
            $this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Данный тип файла не разрешен.')));
            return false;
        }

        // Правильно ли указана директория для загрузки?
        if (!@is_dir($upload_path))
        {
            mkdir( $upload_path );
        }

        if (!@is_dir($upload_path)) {
            //$this->PhpLog_model->insertPhpLog('endImportingRegistryNotSuccessful');
            $this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Путь для загрузки файла справочника некорректен.')));
            return false;
        }

        // Имеет ли директория для загрузки права на запись?
        if (!is_writable($upload_path)) {
            //$this->PhpLog_model->insertPhpLog('endImportingRegistryNotSuccessful');
            $this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Загрузка файла не возможна из-за прав пользователя.')));
            return false;
        }

        //именуем файл, в зависимости от передаваемого типа
        //Если передаётся архив

        if (!isset($data['LocalDirectory_FileType'])) {

            switch(strtolower($file_data['file_ext']))
            {
                case 'rar':
                    $filename = 'archive_directory'.time().'.rar';
                break;

                case 'zip':
                    $filename = 'archive_directory'.time().'.zip';
                break;

                case 'arj':
                    $filename = 'archive_directory'.time().'.arj';
                break;
            }

        } else if ($data['LocalDirectory_FileType'] == 1) {
            $filename = 'loading_Directory'.time().'.xml';
        } else if ($data['LocalDirectory_FileType'] == 2) {
            $filename = 'loading_Directory'.time().'.dbf';
        }


        if ( move_uploaded_file ($_FILES['LocalDirectory_ImportFile']['tmp_name'], $upload_path.$filename) ) {

            $response = array();
            $outdata = array();
            $arch_file_type = '';

            if (!isset($data['LocalDirectory_FileType'])) {

                //Если тип не указан - значит это архив,  надо его распаковать и поределить по прееданной маске какой файл из этого архива нас интересует
                // создадим временную директорию, в которую распакуем архив и просканируем файлы по маске
                $archive_dir = 'extracted_'.time();
                if( !is_dir($upload_path.$archive_dir) ) {
                    if( !mkdir($upload_path.$archive_dir) ) {
                        return array('Error_Msg' => 'Не удалось создать директорию '.$upload_path.$archive_dir.'!');
                    }
                }

                switch(strtolower($file_data['file_ext']))
                {
                    case 'rar':
                        $rf = rar_open($upload_path.$filename);
                        if( !$rf ) {
                            rmdir($upload_path);
                            return array('Error_Msg' => 'Не удалось открыть архив '.$upload_path.$filename.'!');
                        }
                        $files = rar_list($rf);
                        foreach($files as $file) {
                            $file->extract($upload_path.$archive_dir);
                        }
                        rar_close($rf);
                    break;
                    case 'zip':
                        $zip = new ZipArchive;
                        if( $zip->open($upload_path.$filename) === true ) {
                            $zip->extractTo($upload_path.$archive_dir);
                            $zip->close();
                        } else {
                            rmdir($upload_path.$archive_dir);
                            return array('Error_Msg' => 'Не удалось открыть архив '.$upload_path.$filename.'!');
                        }
                    break;
                    case 'arj':
                        $base_name = ($_SERVER["DOCUMENT_ROOT"][strlen($_SERVER["DOCUMENT_ROOT"])-1]=="/")?$_SERVER["DOCUMENT_ROOT"]:$_SERVER["DOCUMENT_ROOT"]."/";
                        $run_command = $base_name.IMPORTPATH_ROOT."arj32.exe e -e ".$upload_path.$filename." ".$upload_path.$archive_dir;
                        $resp = exec($run_command, $out, $ret);
                        if (!isset($resp)) {
                            rmdir($upload_path.$archive_dir);
                            return array('Error_Msg' => 'Не удалось открыть архив '.$upload_path.$filename.'!');
                        }
                    break;
                    default:
                        $this->ReturnData( array('success' => false, 'Error_Code' => 100021 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Данный тип архива не допускается. Разрешены следующие типы архивов: ' . $allowed_types)));
                        return false;
                    break;
                }

                //удаляем архив
                unlink($upload_path.$filename);

                //ищем файл по маске
                $filenames = scandir($upload_path.$archive_dir);
                $k=0;
                foreach($filenames as $archfilename) {
                    if ( $k > 1 ) {
                        $this->ReturnData( array('success' => false, 'Error_Code' => 100018 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'По введенной маске найдено больше одного файла. Уточните маску и повторите попытку.')));
                        return false;
                    }

                    if (stristr($archfilename, $data['LocalDirectory_FileMask'])) {
                        $filename = $archive_dir.'/'.$archfilename;
                        if (substr($archfilename, -3) == 'xml') {
                            $arch_file_type = 'xml';
                        } else if (substr($archfilename, -3) == 'dbf') {
                            $arch_file_type = 'dbf';
                        } else {
                            $this->ReturnData( array('success' => false, 'Error_Code' => 100019 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'По введенной маске найдено больше одного файла. Уточните маску и повторите попытку.')));
                            return false;
                        }
                        $k +=1;
                    }
                }

                if ($k == 0) {
                    $this->ReturnData( array('success' => false, 'Error_Code' => 100020 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Не найдено ниодного файла с указаным фрагментом текста.')));
                    return false;
                }
            }

            if ($data['LocalDirectory_FileType'] == 2 || ($arch_file_type == 'dbf')) {

                //Проверяем dbf файл на валидность и загружаем сторе
                error_reporting(0);
                $h = dbase_open($upload_path.$filename, 0);

                if ( $h ) {
                    $r = dbase_numrecords($h);
                    for ( $i = 1; $i <= $r; $i++ ) {
                        $outdata[$i-1] = dbase_get_record_with_names($h, $i);
                        array_walk($outdata[$i-1], 'ConvertFromWin866ToCp1251');
                    }

                    dbase_close($h);
                    $head_row = $outdata[0];
                } else {
                    $this->ReturnData( array('success' => false, 'Error_Code' => 100016 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Загружаемый DBF файл содержит ошибки, исправьте ошибки в файле и повторите попытку.')));
                    return false;
                }

                error_reporting(-1);

            } else if ($data['LocalDirectory_FileType'] == 1 || ($arch_file_type == 'xml')) {

                //Проверяем xml файл на валидность и загружаем сторе
                libxml_use_internal_errors(true);

                if ($xml_file = simplexml_load_file($upload_path.$filename)) {
                    $outdata = simpleXMLToArray($xml_file);
                    //var_dump($outdata);die;
                    if (isset($outdata['REC'][0])) {
                        $head_row = $outdata['REC'][0];
                    } else {
                        $head_row = $outdata['REC'];
                    }

                } else {
                    $this->ReturnData( array('success' => false, 'Error_Code' => 100017 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Загружаемый XML файл не прошел проверку синтаксиса, исправьте ошибки в файле и повторите попытку.')));
                    return false;
                }
            }

            $combos_store = array();

            foreach ($head_row as $key => $value) {
                array_push($combos_store, $key);
            }

            $response['store'] = $combos_store;
            $response['LocalDirectory_FileType'] = $data['LocalDirectory_FileType'];
            $response['filepath'] = $upload_path.$filename;
            $response['success'] =  true;

            $this->ReturnData($response);
            return true;
        } else {
            //$this->PhpLog_model->insertPhpLog('endImportingRegistryNotSuccessful');
            $this->ReturnError('Не удалось переместить файл в директорию');
            return false;
        }
	}


	/**
	 *  Сохранение измененией в справочнике
	 *  На выходе: JSON-строка
	 *  Используется: справочники Промед
	 */
	function saveDirectoryChanges()
	{
		$data = $this->ProcessInputData('saveDirectoryChanges', true);
		if ($data === false) { return false; }

        $this->load->model("MongoDBWork_model", "dbmodel");

        $data['LocalDirectory_isPK'] = json_decode($data['LocalDirectory_isPK'], true);
        $data['LocalDirectory_ComboValues'] = json_decode($data['LocalDirectory_ComboValues'], true);

		$response = $this->dbmodel->saveDirectoryChanges($data);
        //$this->ProcessModelMultiList($response, true, true)->ReturnData();
        //return true;
        $response['success'] = true;
        $this->ReturnData($response);
	}


	/**
	 * Функция создания версии локальных справочников (создает сборочную версию)
	 */
	function createVersion() {
		if (isSuperadmin()) {
			$this->load->model("MongoDBWork_model", "dbmodel");
			// Получаем предопределенные параметры
			$data = $this->ProcessInputData('createVersion', true);
			// Передаем параметры в модель для получения необходимых данных
			$response = $this->dbmodel->createVersion($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		} else {
			echo json_encode(array('success'=>false));
		}
	}

	/**
	 * Функция фиксирует сборочную версию
	 */
	public function fixedVersion() {
		if (isSuperadmin()) {
			ignore_user_abort(true);
			set_time_limit(0);

			$this->load->model("MongoDBWork_model", "dbmodel");
			// Получаем предопределенные параметры
			$data = $this->ProcessInputData('fixedVersion', true);
			// Передаем параметры в модель для получения необходимых данных
			$response = $this->dbmodel->fixedVersion($data);
			// Еще при создании версии надо перегенерять справочники в MongoDB
			$result = array();
			$mongodb = checkMongoDb();
			if (!empty($mongodb)) { // если конечно библиотека MongoDB загружена
				$result = $this->_createData(); // todo: возможно здесь стоит сделать чтобы перегенерация в монгодб была только если версия нормально "зафиксировалась"
			}
			if (is_array($result) && (count($result)>0)) { // Если обнаружены ошибки, то выведем их админу
				$this->OutData = array(
					'success' => false,
					'Error_Msg' => toUtf(implode("<br/>", $result))
				);
				$this->ReturnData();
			} else {
				$this->ProcessModelSave($response, true, true)->ReturnData();
			}
		} else {
			echo json_encode(array('success'=>false));
		}
	}
	
	
	/**
	 * Функция отправляет сообщение в activeMQ о новой сборке
	 */
	function sendVersionMQmessage() {
		if (isSuperadmin()) {
			$this->load->model("MongoDBWork_model", "dbmodel");
			// Получаем предопределенные параметры
			$data = $this->ProcessInputData('sendVersionMQmessage', true);
			// Передаем параметры в модель для получения необходимых данных
			$response = $this->dbmodel->sendVersionMQmessage($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		}
		else {
			echo json_encode(array('success'=>false));
		}
	}

	/**
	 * Удаление сборочной версии
	 */
	function deleteVersion() {
		if (isSuperadmin()) {
			$this->load->model("MongoDBWork_model", "dbmodel");
			// Получаем предопределенные параметры
			$data = $this->ProcessInputData('deleteVersion', true);
			// Передаем параметры в модель для получения необходимых данных
			$response = $this->dbmodel->deleteVersion($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		} else {
			echo json_encode(array('success'=>false));
		}
	}
	/**
	 * Сохранение данных в "удаленном локальном" хранилище
	 */
	function saveData() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$sp = getSessionParams();
		$data = $this->ProcessInputData('saveData', false);
		$data['pmUser_id'] = $sp['session']['pmuser_id'];
		// Передаем параметры в модель для получения необходимых данных
		$response = $this->dbmodel->saveData($_POST, $data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	/**
	 *	Загрузка грида (список справочников)
	 */
	function loadDirectoryListGrid() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('loadDirectoryListGrid', false);
		$response = $this->dbmodel->loadDirectoryListGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение полей для отображения данных справочника
	 */
	function getDirectoryFields() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		$data = $this->ProcessInputData('getDirectoryFields', false);
        $response = $this->dbmodel->getDirectoryFields($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Чтение данных конкретного справочника
	 */
	function getDirectoryData() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getDirectoryData', false);
		if ($data === false) return;

		$response = $this->dbmodel->getDirectoryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Чтение полей формы добавления/редактир-я записи справочника
	 */
	function getFormDataForDirectoryEditWindow() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('getFormDataForDirectoryEditWindow', false);
		$response = $this->dbmodel->getFormDataForDirectoryEditWindow($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных')->ReturnData();
	}
	/**
	 *	saveDirectoryRecord
	 */
	function saveDirectoryRecord() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('saveDirectoryRecord', true);
		$fields = $this->inputRules['saveDirectoryRecord'];
		$saveData = array();
		foreach($fields as $f) {
			if( array_key_exists($f['field'], $data) ) {
				$saveData[$f['field']] = toUTF($data[$f['field']]);
			}
		}
		$saveData['fieldsData'] = json_decode($saveData['fieldsData'], true);
		$saveData['fieldsData']['pmUser_id'] = $data['pmUser_id'];

		$response = $this->dbmodel->saveDirectoryRecord($saveData);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	/**
	 *	deleteDirectoryRecord
	 */
	function deleteDirectoryRecord() {
		$this->load->model("MongoDBWork_model", "dbmodel");
		// Получаем предопределенные параметры
		$data = $this->ProcessInputData('deleteDirectoryRecord', true);
		$response = $this->dbmodel->deleteDirectoryRecord($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}


	/**
	 * Наполнение из таблицы неформализованных адресов
	 */
	public function importTableUnformalizedAddressDirectory(){
		$this->dbmodel->importTableUnformalizedAddressDirectory();
	}


	/**
	 * Наполнение из таблицы типов неформализованных адресов
	 */
	public function importTableUnformalizedAddressType(){
		$this->dbmodel->importTableUnformalizedAddressType();
	}


	/**
	 * Импорт таблиц в Mongodb
	 */
	public function importCommonTable(){
		set_time_limit(3600);

		$table = isset( $_GET['table'] ) ? $_GET['table'] : '';

		switch( $table ){
			case 'KLArea':
				$this->dbmodel->importCommonTable('KLArea',array(
					'KLArea_id' => 'int',
					'KLSocr_id' => 'int',
					'KLCountry_id' => 'int',
					'KLAreaLevel_id' => 'int',
					'KLArea_pid' => 'int',
					'KLAdr_Actual' => 'int',
					'KLArea_oid' => 'int',
					'pmUser_insID' => 'int',
					'pmUser_updID' => 'int',
					'KLArea_insDT' => 'datetime',
					'KLArea_updDT' => 'datetime',
					'Server_id' => 'int',
					'KLAreaCentreType_id' => 'int',
				));
			break;

			case 'UnformalizedAddressDirectory':
				$this->dbmodel->importCommonTable('UnformalizedAddressDirectory',array(
					'UnformalizedAddressDirectory_id' => 'int',
					'KLRgn_id' => 'int',
					'KLSubRgn_id' => 'int',
					'KLCity_id' => 'int',
					'KLTown_id' => 'int',
					'KLStreet_id' => 'int',
					'pmUser_insID' => 'int',
					'pmUser_updID' => 'int',
					'UnformalizedAddressDirectory_insDT' => 'datetime',
					'UnformalizedAddressDirectory_updDT' => 'datetime',
					'Lpu_id' => 'int',
					'UnformalizedAddressType_id'=> 'int',
				));
			break;

			case 'UnformalizedAddressType':
				$this->dbmodel->importCommonTable('UnformalizedAddressType',array(
					'UnformalizedAddressType_id' => 'int',
					'pmUser_insID' => 'int',
					'pmUser_updID' => 'int',
					'UnformalizedAddressType_insDT' => 'datetime',
					'UnformalizedAddressType_updDT' => 'datetime',
				));
			break;

			case 'KLStreet':
				$this->dbmodel->importCommonTable('KLStreet',array(
					'KLStreet_id' => 'int',
					'KLArea_id' => 'int',
					'KLSocr_id' => 'int',
					'KLAdr_Actual' => 'int',
					'KLStreet_oid' => 'int',
					'pmUser_insID' => 'int',
					'pmUser_updID' => 'int',
					'KLStreet_insDT' => 'datetime',
					'KLStreet_updDT' => 'datetime',
					'Server_id' => 'int',
				),array('where'=>'KLArea_id=3310')); // Улицы Перми
			break;

			case 'KLSocr':
				$this->dbmodel->importCommonTable('KLSocr',array(
					'KLSocr_id' => 'int',
					'KLAreaType_id' => 'int',
					'KLAreaLevel_id' => 'int',
					'KLSocr_ObjType' => 'int',
					'pmUser_insID' => 'int',
					'pmUser_updID' => 'int',
					'KLSocr_insDT' => 'datetime',
					'KLSocr_updDT' => 'datetime',
					'KLCountry_id' => 'int',
				));
			break;

			case 'LpuBuilding':
				$this->dbmodel->importCommonTable('LpuBuilding',array(
					'Server_id' => 'int',
					'LpuBuilding_id' => 'int',
					'Lpu_id' => 'int',
					'LpuBuildingType_id' => 'int',
					'Address_id' => 'int',
					'LpuBuilding_Code' => 'int',
					'pmUser_insID' => 'int',
					'pmUser_updID' => 'int',
					'LpuBuilding_insDT' => 'datetime',
					'LpuBuilding_updDT' => 'datetime',
					'LpuBuilding_begDate' => 'datetime',
					'LpuBuilding_endDate' => 'datetime',
					'PAddress_id' => 'int',
				));
			break;
		}
	}

	
}