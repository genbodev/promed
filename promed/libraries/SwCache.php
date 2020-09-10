<?php

/**
 * Класс для реализации функций кеширования данных с использованием нескольких источников
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			libraries
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Markoff A. (markov@swan.perm.ru)
 * @version			30.12.2014
 *
 * @property		Resource mongo_db
 * @property		Resource CI
 * @property		swMongodb swmongodb
 * @property		swMongoExt swmongoext
 * @property		swMongoExt swmongocache

 */
class SwCache
{
	//private $mongodb = NULL;
	private $CI = null;
	private $use = "mongo";
	private $config = array();
   
	/**
	 * Конструктор
	 */
    function __construct($config = array()) {
		$this->CI = get_instance();
		$this->config = $this->CI->config->item('cache');
		if ($this->config && $this->config['use']) {
			$this->use = $this->config['use'];
		}
		if (isset($config['use'])) {
			$this->use = $config['use'];
		}

		switch($this->use) {
			case 'mongo':
				$mongodb = checkMongoDb();
				if (!empty($mongodb)) {
					$this->CI->load->library('swMongoCache'); // в swMongoCache создается правильный экземпляр swMongodb
					$this->mongocache = $this->CI->swmongocache;
				} else {
					$this->use = 'none';
				}
				break;
			case 'apc':
				if (!function_exists('apc_fetch') || !function_exists('apc_store')) {
					$this->use = 'none';
				}
				break;
			case 'apcu':
				if (!function_exists('apcu_fetch') || !function_exists('apcu_store')) {
					$this->use = 'none';
				}
			case 'memcache':
				if (!function_exists('memcache_connect')) {
					$this->use = 'none';
				} else {
					$host = '127.0.0.1';
					$port = 11211;
					if (!empty($this->config['memcache_host'])) {
						$host = $this->config['memcache_host'];
					}
					if (!empty($this->config['memcache_port'])) {
						$port = $this->config['memcache_port'];
					}

					$this->memcache = new Memcache();
					$result = @$this->memcache->connect($host, $port);
					if (!$result) {
						$this->use = 'none';
					}
				}
				break;
		}
    }
	/**
	 * Деструктор
	 */
	function __destruct() {
		unset($this->CI);
	}
	
	/**
	 * Проверяет наличие закэшированных данных
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 * @param array $limit Ограничение по лимиту, ассоциативный массив
	 **/
	function get($object, $filter = array(), $limit = array('offset' => 0, 'limit' => 0)) {
		// MongoDB
		if ($this->use == 'mongo' && ($this->mongocache->isEntry($object, $filter))) {
			// получаем информацию о ttl и time и используем time в качестве фильтра по времени
			$settings = $this->mongocache->getSettings($object);
			// Если время хранения кэша истекло
			if (!is_array($settings) || ($settings['ttl']>0 && $settings['time']<time())) {
				// очистим кэш, так как время хранения кэши истекло / очищаем с фильтрацией
				$this->clear($object, $filter);
				// и вернем Null, для того, чтобы перестроить кэш
				return null;
			}
			// todo: Если со времением окажется, что реализация с хранением ttl в sysCache не подходит, то нужно будет 
			// добавлять для каждой записи время добавления записи в кэш в set-процедуре, а здесь нужно будет добавить фильтрацию по этому времени (с очисткой кэша)
			return $this->mongocache->get($object, $filter, $limit);
		}
		// APC
		if ($this->use == 'apc' && $resCache = apc_fetch($object)) {
			return $resCache;
		}
		// APCU
		if ($this->use == 'apcu' && $resCache = apcu_fetch($object)) {
			return $resCache;
		}
		// memcache
		if ($this->use == 'memcache' && $resCache = $this->memcache->get($object)) {
			return $resCache;
		}
		return null;
	}

	/**
	 * Проверяет наличие закэшированных данных
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 * @param array $limit Ограничение по лимиту, ассоциативный массив
	 **/
	function getMulti($object, $filter = array(), $limit = array('offset' => 0, 'limit' => 0)) {
		// MongoDB
		if ($this->use == 'mongo' && ($this->mongocache->isEntry($object, $filter))) {
			// получаем информацию о ttl и time и используем time в качестве фильтра по времени
			$settings = $this->mongocache->getSettings($object);
			// Если время хранения кэша истекло
			if (!is_array($settings) || ($settings['ttl']>0 && $settings['time']<time())) {
				// очистим кэш, так как время хранения кэши истекло / очищаем с фильтрацией
				$this->clear($object, $filter);
				// и вернем Null, для того, чтобы перестроить кэш
				return null;
			}
			// todo: Если со времением окажется, что реализация с хранением ttl в sysCache не подходит, то нужно будет
			// добавлять для каждой записи время добавления записи в кэш в set-процедуре, а здесь нужно будет добавить фильтрацию по этому времени (с очисткой кэша)
			$records = $this->mongocache->get($object, $filter, $limit);
			return array(
				'data' => $records,
				'totalCount' => $settings['totalCount']
			);
		}
		return null;
	}
	
	/**
	 * Записывает кэшируемые данные
	 * @param string $object Наименование объекта
	 * @return array|null
	 */
	function set($object, $data, $config = array('ttl'=>0)) {
		$ttl = (is_array($config) && isset($config['ttl']))?$config['ttl']:0;
		// Если есть ttl в настройках, то берем из настроек promed.php
		if (/*$ttl==0 && */$this->config && isset($this->config['ttl'])) {
			$arr_obj = explode("_",$object);  // Обрезаем все что за _, чтобы взять нужную настройку для кэша
			$objname = $arr_obj[0];
			if (isset($this->config['ttl'][$objname])) { // Если такая настройка есть 
				$ttl = $this->config['ttl'][$objname];
			}
		}
		try {
			// MongoDB
			if ($this->use == 'mongo') {
				// Сохраняем или апдейтим запись в sysCache
				$this->mongocache->_post($object, $data, $ttl);
				// Сохраняем записи в кэш
				$this->mongocache->insertBatch($object, $data);
			}
			// APC
			if ($this->use == 'apc') {
				apc_store($object, $data, $ttl); // на $ttl секунд кэшируем данные 
			}
			// APCU
			if ($this->use == 'apcu') {
				apcu_store($object, $data, $ttl); // на $ttl секунд кэшируем данные
			}
			// memcache
			if ($this->use == 'memcache') {
				$this->memcache->set($object, $data, 0, $ttl); // на $ttl секунд кэшируем данные
			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Записывает кэшируемые данные
	 * @param string $object Наименование объекта
	 * @return array|null
	 */
	function setMulti($object, $data, $config = array('ttl'=>0)) {
		$ttl = (is_array($config) && isset($config['ttl']))?$config['ttl']:0;
		// Если есть ttl в настройках, то берем из настроек promed.php
		if (/*$ttl==0 && */$this->config && isset($this->config['ttl'])) {
			$arr_obj = explode("_",$object);  // Обрезаем все что за _, чтобы взять нужную настройку для кэша
			$objname = $arr_obj[0];
			if (isset($this->config['ttl'][$objname])) { // Если такая настройка есть
				$ttl = $this->config['ttl'][$objname];
			}
		}
		try {
			// MongoDB
			if ($this->use == 'mongo') {
				// Сохраняем или апдейтим запись в sysCache
				$this->mongocache->_post($object, $data, $ttl);
				// Сохраняем записи в кэш
				$this->mongocache->insertBatch($object, $data['data']);
			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Удаляет закэшированные данные
	 * @param string $object Наименование объекта
	 * @param array $filter Данные фильтра, ассоциативный массив
	 */
	function clear($object, $filter = array()) {
		try {
			// MongoDB
			if ($this->use == 'mongo') {
				$this->mongocache->clear($object, $filter);
			}
			// APC
			if ($this->use == 'apc') {
				apc_delete($object); 
			}
			// APCU
			if ($this->use == 'apcu') {
				apcu_delete($object);
			}
			// memcache
			if ($this->use == 'memcache') {
				$this->memcache->delete($object);
			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}
?>