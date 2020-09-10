<?php

/**
 * Класс для реализации функций кеширования документов в MongoDB-хранилище
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			libraries
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Markoff A. (markov@swan.perm.ru)
 * @version			06.06.2014
 *
 * @property		Resource mongo_db
 * @property		Resource CI
 * @property		swMongodb swmongodb
 * @property		swMongoExt swmongoext

 */
class SwMongoCache
{
    private $mongo_db = NULL;
	private $CI = null;
	private $prefix = 'cache';
	var $cacheTable = 'sysCache';
    
	/**
	 * Конструктор
	 * Класс использует библиотеку MongoDb
	 */
    function __construct() {
		// todo: Проверить загрузку библиотеки и изменить на другой вариант, который здесь прокатит
		switch (checkMongoDb()) {
			case 'mongo':
				require_once(APPPATH.'libraries/SwMongodb.php');
				$this->mongo_db = new swMongodb(array('config_file'=>'mongodbcache', 'fail_gracefully'=>true));
				$this->CI = get_instance();
				break;
			case 'mongodb':
				require_once(APPPATH.'libraries/SwMongodbPHP7.php');
				$this->mongo_db = new swMongodbPHP7(array('config_file'=>'mongodbcache', 'fail_gracefully'=>true));
				$this->CI = get_instance();
				break;
			default:
				// Здесь надо не сообщать об ошибке, а логировать ее и не работать с кэшем
				show_error("The MongoDB PECL extension has not been installed or enabled. Use session with MongoDB impossible.", 500);
				break;
		}
    }
	/**
	 * Деструктор
	 */
	function __destruct() {
		//unset($this->CI);
		//unset($this->mongo_db);
	}
	
	/**
	 * Возвращает запись или набор записей
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 * @return array|null
	 */
	function get($object = '', $filter = array(), $limit = array('offset' => 0, 'limit' => 0)) {
		$sql = null;
		if($sql != null){
			/*
			// todo: Преобразование из SQL пока не работает
			$where = $this->mongo_getwhere($sql);
			print_r($where);echo $sql;exit();
			$row = $this->mongo_db->wheres = $where;
			$row = $this->mongo_db->limit(100)->get($this->prefix.$object);
			*/
		}else{
			$row = $this->mongo_db->where($filter)->offset($limit['offset'])->limit($limit['limit'])->get($this->prefix.$object);
		}
		return (is_array($row))?$row:null;
	}

	/**
	 * Возвращает признак наличия записей по фильтру
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 * @return bool
	 */
	function isEntry($object = '', $filter=array()) {
		$count = $this->mongo_db->where($filter)->count($this->prefix.$object);
		return ($count>0)?true:false;
	}

	/**
	 * Возвращает признак наличия записи по идентификатору
	 * @param string $object Наименование объекта
	 * @param $id Идентификатор записи
	 * @return bool
	 */
	function isEntryOnId($object = '', $id) {
		return $this->isEntry($object, array('_id'=>$id));
	}

	/**
	 * Сохранение записи в любом объекте (с префиксом) в MongoDB
	 * @param string $object Наименование объекта, в котором требуется сохранить запись
	 * @param array $data Данные записи для сохранения, ассоциативный массив
	 * @return array
	 */
	function save($object = '', $data = array()) {
		$this->CI->load->library('swMongoExt');

		$id = $object.'_id';
		// получаем идентификатор для записи в объект: если исходный набор данных не содержит идентификатора, то он будет создан
		// todo: По идее если мы генерим код, то нужно однозначно добавлять запись
		$data['_id'] = isset($data[$object.'_id'])?$data[$object.'_id']:$this->CI->swmongoext->generateCode($object);
		//array_walk($data, 'convertFieldToInt');
		array_walk($data, 'ConvertFromWin1251ToUTF8');
		if ($this->isEntryOnId($object, $data['_id'])) {
			$this->mongo_db->where(array('_id' => $data['_id']))->update($this->prefix.$object, $data);
		} else {
			$this->mongo_db->insert($this->prefix.$object, $data);
		}
		return array('success' => true, $id => $data['_id'], 'Error_Msg' => '');
	}

	/**
	 * Сохраняет несколько записей в любом объекте (с префиксом) в MongoDB
	 * @param string $object Наименование объекта, в котором требуется сохранить запись
	 * @param array $data Данные записи для сохранения, набор ассоциативных массивов
	 * @return array
	 */
	function saveAll($object = '', $records = array()) {
		if (count($records)>0) {
			foreach ($records as $key => $record) {
				$this->save($object, $record);
			}
		}
	}
	
	/**
	 * Сохраняет несколько записей (без апдейтов) в любом объекте (с префиксом) в MongoDB
	 * @param string $object Наименование объекта, в котором требуется сохранить запись
	 * @param array $data Данные записи для сохранения, набор ассоциативных массивов
	 * @return array
	 */
	function insertBatch($object = '', $records = array()) {
		$count = $this->mongo_db->count($this->prefix . $object);
		if ($count > 0) {
			// если кэш уже не пустой, то и вставлять в него ничего не надо :)
			return array('success' => true, 'Error_Msg' => '');
		} else if (count($records) > 0) {
			$id = $object.'_id';
			array_walk($records, 'ConvertFromWin1251ToUTF8');
			$insertId = $this->mongo_db->batch_insert($this->prefix . $object, $records);
			return array('success' => true, $id => $insertId, 'Error_Msg' => '');
		}
	}
	
	/**
	 * Перекеширует данные в любом объекте (с префиксом) в MongoDB
	 * @param string $object Наименование объекта, в котором требуется сохранить запись
	 * @param array $data Данные записи для сохранения, набор ассоциативных массивов
	 * @param bool $remove Признак предварительной очистки кэша
	 * @return array
	 */
	function recache($object = '', $records = array(), $remove = false) {
		if ($remove) {
			$this->clear($object); // Предварительно удаляем все данные в cache-объекте
		}
		// todo: Возможно здесь надо будет поменять на $this->mongo_db->batch_insert()
		$this->saveAll($object,  $records); // Записываем данные вновь
	}
	/**
	 *
	 * @param type $object
	 * @param type $sysCache_time
	 * @return type 
	 */
	function cacheClear($object = '', $sysCache_time = '') {
		//array_walk($data, 'ConvertFromWin1251ToUTF8');
		if ($sysCache_time!='' && $sysCache_time>0) {
			$this->mongo_db->where_lt("sysCache_time",time())->delete_all($this->prefix.$object);
		} else {
			$this->mongo_db->delete_all($this->prefix.$object);
		}
		// todo: Сделать обработку полей, когда записи могут не удалиться
		return true;
	}
	/**
	 * Удаление одной записи
	 * @param string $object Наименование объекта
	 * @param array $filterr Данные фильтра, ассоциативный массив
	 * @return bool
	 */
	function delete($object = '', $filter = array()) {
		array_walk($data, 'ConvertFromWin1251ToUTF8');
		$this->mongo_db->where($filter)->delete($this->prefix.$object);
		// todo: Сделать обработку полей, когда записи могут не удалиться
		return true;
	}
	/**
	 * Удаление всех данных (по фильтру) по объекту из кеша
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 */
	function clear($object = '', $filter = array()) {
		array_walk($filter, 'ConvertFromWin1251ToUTF8');
		$this->mongo_db->where($filter)->delete_all($this->prefix.$object);
	}
	
	/**
	 * Удаление объекта
	 * @param string $object Наименование объекта
	 * @return bool
	 */
	function drop($object) {
		$this->mongo_db->drop_collection($this->mongo_db->dbname, $object);
		return true;
	}
	/**
	 * Сохраняет настройки для объекта
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 */
	function setSettings($object = '', $data = array()) {
		// Не используется, сохранение настроек при редактировании кэша
	}
	
	/**
	 * Читает настройки для объекта
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 */
	function getSettings($object = '', $data = array()) {
		$settings = false;
		$row = $this->mongo_db->where(array($this->cacheTable.'_object'=>$object))->get($this->cacheTable);
		if (is_array($row) && count($row)>0) {
			$settings = array(
				'ttl'=>isset($row[0][$this->cacheTable.'_ttl'])?$row[0][$this->cacheTable.'_ttl']:0, 
				'time'=>isset($row[0][$this->cacheTable.'_time'])?$row[0][$this->cacheTable.'_time']:0,
				'totalCount'=>isset($row[0]['totalCount'])?$row[0]['totalCount']:0
			);
		}
		return $settings;
	}
	
	
	/**
	 * Создает/изменяет запись в основной таблице системного кэша (для автоматически добавляемых записей)
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 */
	function _post($object, $data = array(), $ttl = 0) {
		$date = date("d.m.Y H:i:s");
		$user_id = (isset($data['pmUser_id'])) ? $data['pmUser_id'] : 0;
		$rowsMongo = $this->mongo_db->where(array($this->cacheTable . '_object' => $object))->get($this->cacheTable);

		$rows = (is_array($rowsMongo) && count($rowsMongo) > 0) ? $rowsMongo[0] : array();
		if ($rows) { // Если есть запись с таким названием объекта
			// Апдейтим
			$rows[$this->cacheTable . '_updDT'] = $date;
			$rows['pmUser_updID'] = $user_id;
			$rows[$this->cacheTable . '_ttl'] = $ttl;
			$rows[$this->cacheTable . '_time'] = time() + $ttl;
			if (isset($data['totalCount'])) {
				$rows['totalCount'] = $data['totalCount'];
			}
			$this->mongo_db->where(array('_id' => $rows[$this->cacheTable . '_id']))->update($this->cacheTable, $rows);
		} else {
			$this->CI->load->library('swMongoExt');
			$rows[$this->cacheTable . '_id'] = (int)$this->CI->swmongoext->generateCode($this->cacheTable);
			$rows['_id'] = $rows[$this->cacheTable . '_id'];
			$rows[$this->cacheTable . '_insDT'] = $date;
			$rows[$this->cacheTable . '_updDT'] = $date;
			$rows[$this->cacheTable . '_name'] = $object;
			$rows[$this->cacheTable . '_object'] = $object;
			$rows[$this->cacheTable . '_ttl'] = $ttl;
			$rows[$this->cacheTable . '_time'] = time() + $ttl;
			if (isset($data['totalCount'])) {
				$rows['totalCount'] = $data['totalCount'];
			}
			$rows[$this->cacheTable . '_auto'] = 1; // запись добавлена автоматически
			$rows['pmUser_insID'] = $user_id;
			$rows['pmUser_updID'] = $user_id;
			$this->mongo_db->insert($this->cacheTable, $rows);
		}
	}
}

?>