<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MongoCache_model - модель для работы с кэшем Монго с формы управления кэшем
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Марков Андрей <markov@swan.perm.ru>
 * @version			07.2014
 *
 * @property		swMongodb mongo_db
 * @property		swMongoExt swmongoext
 * @property		swMongoCache swmongocache
 */

class MongoCache_model extends swModel {
	
	private $object = 'sysCache';
	private $prefix = 'cache';
	private $countRowOnCache = 10000; // Количество строк для fetch
	private $fields = array(
		'_id',
		'sysCache_id',
		'sysCache_name',
		'sysCache_object',
		'sysCache_ttl',
		'sysCache_time', // время жизни в секундах
		//'sysCache_setting',
		'sysCache_auto', // признак автоматически добавленного кэша
		'sysCache_insDT',
		'sysCache_uptDT',
		'pmUser_insID',
		'pmUser_updID'
	);
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		switch (checkMongoDb()) {
			case 'mongodb':
				require_once (APPPATH.'libraries/SwMongodbPHP7.php');
				$this->mongo_db = new swMongodbPHP7(array('config_file'=>'mongodbcache', 'fail_gracefully'=>true));
				break;
			default:
				require_once (APPPATH.'libraries/SwMongodb.php');
				$this->mongo_db = new swMongodb(array('config_file'=>'mongodbcache', 'fail_gracefully'=>true));
			break;
		}
		$this->load->library('swMongoExt');
		$this->load->library('swMongoCache');
		$this->load->helper('MongoDB');
	}
	/**
	 * Получает данные записи по идентификатору
	 */
	function getRecordOnId($id) {
		$record = $this->mongo_db->where(array('_id' => (int)$id))->get($this->object);
		if ($record && is_array($record) && is_array($record[0])) {
			return $record[0];
		} else {
			return array();
		}
	}
	
	/**
	 * Сохранение объекта кэширования
	 */
	function saveMongoCache($data) {
		array_walk($data, 'ConvertFromWin1251ToUTF8');
		
		// При сохранении всегда меняем дату/время редактирования
		$data['sysCache_updDT'] = date("d.m.Y H:i:s");
				
		// Если вставляем данные 
		if (empty($data['sysCache_id'])) {
			$data['sysCache_insDT'] =  date("d.m.Y H:i:s");
			$data['sysCache_auto'] =  0; // добавленный вручную объект кэширования
			// Генерим "человечный" код
			$data['sysCache_id'] = (int)$this->swmongoext->generateCode($this->object);
			// Ид тоже в нормальном виде
			$data['_id'] = $data['sysCache_id'];
			$this->mongo_db->insert($this->object, $data);
		} else {
			// Приводим к инту
			$data['sysCache_id'] = (int)$data['sysCache_id'];
			$data['sysCache_auto'] =  0; // при сохранении обычным способом любой кэш делаем ручным
			// Получаем данные по записи и объединяем, чтобы ничего не потерять
			$data = array_merge($this->getRecordOnId($data['sysCache_id']), $data);
			$this->mongo_db->where(array('_id' => $data['sysCache_id']))->update($this->object, $data);
		}
		return array('success' => true, 'sysCache_id' => $data['sysCache_id'], 'Error_Msg' => '');
	}

	/**
	 * Сохранение строки лога
	 */
	function saveMongoCacheQueryLog($data) {
		$object = 'MongoCachequerylog';
		$id = $object.'_id';
		$uc_id = '';

		$params = array();
		foreach($data as $field=>$value) {
			$index = strtolower($field);
			$params[$index] = $value;
			if ($index == $id) {
				$uc_id = $field;
			}
		}
		array_walk($params, 'convertFieldToInt');
		array_walk($params, 'ConvertFromWin1251ToUTF8');

		if (empty($params[$id]) || $params[$id] < 0) {
			$params[$id] = $this->swmongoext->generateCode($object);
			$this->mongo_db->insert($object, $params);
		} else {
			$this->mongo_db->wheres = array($id => $params[$id]);
			$this->mongo_db->update($object, $params);
		}

		return array('success' => true, $uc_id => $params[$id], 'Error_Msg' => '');
	}

	/**
	 * Удаление объекта кэширования
	 */
	function deleteMongoCache($data) {
		$recordCache = $this->getRecordOnId((int)$data['id']);
		if (!empty($recordCache['sysCache_name'])) {
			$this->swmongocache->drop($recordCache['sysCache_name']);
		}
		
		$res = $this->mongo_db->where(array('_id' => (int)$data['id']))->delete($this->object);
		
		if (!$res) {
			return array('success' => false, 'Error_Msg' => 'Удаление не выполнено');
		}
		return array('success' => true, 'Error_Msg' => '');
	}
	/**
	 * clearMongoCache
	 */
	function clearMongoCache($data) {
		$recordCache = $this->getRecordOnId($data['sysCache_id']);
		switch ($data['type']){
			case 'all':
				$res = $this->swmongocache->cacheClear($recordCache['sysCache_name']);
				break;
			case 'unactual':
				$res = $this->swmongocache->cacheClear($recordCache['sysCache_name'],$recordCache['sysCache_time']);
				break;
		}
		
		if (!$res) {
			return array('success' => false, 'Error_Msg' => 'Удаление не выполнено');
		}
		return array('success' => true, 'Error_Msg' => '');
	}
	/**
	 * Возвращает список кэшируемых объектов
	 */
	function loadMongoCacheList($data) {
		
		if (!empty($data['searchName'])) {
			$this->mongo_db->like('sysCache_name', $data['searchName']);
		}
		if (empty($data['searchAuto']) || $data['searchAuto']==0) { 
			$this->mongo_db->where('sysCache_auto', 0); // только не автоматические
		}
		
		$result = $this->mongo_db->get($this->object);
		foreach($result as $key=>&$record) {
			if (isset($record['sysCache_object']) && (!empty($record['sysCache_object']))) {
				$record['sysCache_count'] = $this->mongo_db->count($this->prefix.$record['sysCache_object']);
			} else {
				$record['sysCache_count'] = 0;
			}
		}
		return $result;
	}

	/**
	 * Возвращает часть кэша объекта
	 */
	function loadMongoCacheContent($data) {
		
		$object = $this->prefix.$data['sysCache_object'];
		$result = $this->mongo_db->limit(100)->get($object);
		return $result;
	}

	/**
	 * Перекэширование данных объекта
	 */
	function recacheMongoCache($data) {
		//$this->load->library('textlog', array('file' => 'recacheMongoCache.log'));
		//$this->textlog->add("Задействовано памяти до выгрузки: " . memory_get_usage() . "\n\r");
		
		$rc = 0;
		$sql = "";
		$params = array();
		$records = array();
		// получим SQL-запрос из MongoDB
		$recordCache = $this->getRecordOnId($data['sysCache_id']);
		// выполяем SQL и загоняем все полученные запросом данные в Mongo
		if (!empty($recordCache['sysCache_sql'])) {
			//echo ConvertDateTimeFormat($recordCache['sysCache_updDT'], 'Y-m-d H:i:s').' '.$recordCache['sysCache_name'];
			if($data['type']=='change'){
				$sql = "select * from (".$recordCache['sysCache_sql'].")obj where obj.".$recordCache['sysCache_name']."_updDT > :lastDT";
				$params['lastDT'] = ConvertDateTimeFormat($recordCache['sysCache_updDT'], 'Y-m-d H:i:s');
			}else if($data['type']=='Id'){
				ConvertFromWin1251ToUTF8($data['IDs']);
				$IDs = json_decode($_POST['IDs'], true);
				if (count($IDs) > 0) {
					$IdIn = implode($IDs, ',');
					if(substr($IdIn, strlen($IdIn)-1)==','){$IdIn =substr($IdIn,0, strlen($IdIn)-1);}
					$sql = "select * from (".$recordCache['sysCache_sql'].")obj where obj.".$recordCache['sysCache_name']."_id IN (".$IdIn.")";
				}
			}else{
				$sql = $recordCache['sysCache_sql'];
			}
			$result = $this->db->query($sql, $params);
			//$this->textlog->add("Выполнили запрос, памяти занято: ". memory_get_usage());
			if (is_object($result)) {
				// Поскольку нельзя определить сколько записей в результате, то фетчим данные всегда, чтобы не было падений с нехваткой памяти
				while( $record = $result->_fetch_assoc()) {
					$records[] = $record;
					if ($rc<$this->countRowOnCache) {
						$rc++;
					} else {
						$this->swmongocache->recache($recordCache['sysCache_object'], $records, false);
						//$this->textlog->add("Задействовано памяти после выполнения записи в файл порции в " . $rc . " записей: " . memory_get_usage() . "\n\r");
						$rc = 0;
						$records = array();
					}
				}
				if (count($records)>0) {
					$this->swmongocache->recache($recordCache['sysCache_object'], $records, false);
				}
			} else {
				DieWithError("Ошибка выполнения запроса: ".getDebugSql($recordCache['sysCache_sql'], array()));
			}
			return array('success'=>true);
		} else {
			// Запрос для перекэширования отсутствует
			DieWithError("Запрос для перекэширования отсутствует.");
		}
	}

	/**
	 * Удаление лога из mongodb
	 */
	function clearMongoCacheQueryLog($data) {
		$object = 'MongoCachequerylog';

		$result = $this->mongo_db->delete_all($object);

		return array('success' => true);
	}
}