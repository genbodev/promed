<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MongoDB
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Markoff A.A. <markov@swan.perm.ru>
 * @version      июнь.2012
 * @property Mongo_db mongo_db
 */

class MongoDBWork_model extends swPgModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		switch (checkMongoDb()) {
			case 'mongo':
			$this->load->library('swMongodb', null, 'mongo_db');
			break;
		case 'mongodb':
			$this->load->library('swMongodbPHP7', null, 'mongo_db');
			break;
		}
		parent::__construct();
	}

	/**
	 *	Выполнение любого пришедшего запроса 
	 */
	function getDataSql($sql, $params = array(), $returnResourceLink = false, $db = null) {
		if (isset($db)) {
			$result = $db->query($sql, $params);
		} else {
			$result = $this->db->query($sql, $params);
		}

		if ( !is_object($result) ) {
			return false;
		}

		if ( $returnResourceLink === true ) {
			return $result;
		}

		return $result->result('array');
	}

	/**
	 *	Функция устанавливает версию изменения справочников в БД
	 */
	function setVersion($v) {
		$ver = array('ver'=>$v);
		$countRec = $this->mongo_db->count('sysVersion');
		if ($countRec==0) {
			$ver['_id']=1;
			$this->mongo_db->insert('sysVersion', $ver);
		} else {
			$this->mongo_db->where(array('_id'=>1))->set('ver', $v)->update('sysVersion');
		}
		return $v;
	}

	/**
	 *	Функция узнает версию изменения справочников в БД
	 */
	function getVersion() {
		try {
			$r = $this->mongo_db->get('sysVersion');
			if (!is_array($r) || (count($r)==0)) {
				$r = array(array('ver'=>0));
			}
		}
		catch ( SoapFault $e ) {
			$r = array(array('ver'=>0));
		}
		return $r[0]['ver'];
	}

	/**
	 *	Функция по наименованию таблицы выгребает из бд данные
	 */
	public function getObjectData($table, $returnResourceLink = false) {
		if ( !is_array($table) ) {
			return array();
		}

		try {
			if ( !empty($table['SyncTable_sql']) ) {
				// Если запрос указан в БД, то выполним его
				$result = $this->getDataSql($table['SyncTable_sql'], array(), $returnResourceLink);
			}
			else {
				// Иначе сгенерируем запрос из остальных полей
				$schema = (!empty($table['SyncTable_schema']) ? $table['SyncTable_schema'] : 'dbo');

				if ($schema == 'EMD') {
					// Выгребаем из постгре базы
					$this->emddb = $this->load->database('emd', true); // своя БД на PostgreSQL
					$sql = 'select * from "' . $schema . '"."' . $table['SyncTable_nick'].'"';
					$result = $this->getDataSql($sql, array(), $returnResourceLink, $this->emddb);
				} else {
					// Поменял на префикс, потому что в таблицы справочники добавился регион и выбираем теперь из вьюх
					// можно проверку наличия поля Region_id, но зачем эти лишние запросы - SyncTable_prefix остается не задействованным
					// Выбираем из необходимой вьюхи
					$view = 'v_' . $table['SyncTable_prefix'];

					if (substr($table['SyncTable_nick'], 0, 2) == 'v_') {
						$view = $table['SyncTable_nick'];
					}

					$sql = "select * from " . $schema . "." . $view;
					$result = $this->getDataSql($sql, array(), $returnResourceLink);
				}
			}
		}
		catch ( Exception $e ) {
			// todo: все ошибки надо сообщать, нужно логирование и возможность вывода лога
			$result = array(
				'Error_Code' => $e->getCode(),
				'Error_Msg' => $table['SyncTable_name'] . ': ' . $e->getCode() . ' ' . $e->getMessage()
			);
		}

		return $result;
	}

	/**
	 *	Функция формирует массив полей справочника из пришедших в _POST параметров
	 */
	function getFields($post) {
		$result = array();
		foreach ($post as $row=>$val ) {
			$result[] = $row;
		}
		return $result;
	}

	/**
	 *	Функция формирует соответствие наименований полей пришедших и храняшихся в MongoDB
	 */
	function getMap($post) {
		$result = array();
		foreach ($post as $row=>$val) {
			$result[strtolower($row)] = $row;
		}
		return $result;
	}

	/**
	 * Функция обновляет заданную таблицу
	 */
	public function createDataTable($data) {
		$this->load->helper('MongoDB');
		$errors = array();

		// получаем данные о таблице (если она вообще есть)
		$this->load->model('SprLoader_model', 'sprmodel');
		$data['region'] = getRegionNumber();
		$tables = $this->sprmodel->getSyncTable($data);

		if (!count($tables)) return false;

		$val = $tables[0];

		// Генерим версию
		$sql = '
			select Error_Code as "Error_Code", Error_Message as "Error_Msg"
			from stg.xp_LocalDBVersionTable_generate(
				LocalDBList_id := :LocalDBList_id,
				pmUser_id := :pmUser_id,
				isTest := 0);
		';
		$this->db->query($sql, array(
			'LocalDBList_id' => $val['LocalDBList_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// тянем данные
		$response = $this->getObjectData($val, true);

		// поскольку MongoDB регистрозависимая, переводим название таблицы в нижний регистр
		$_name = strtolower($val['SyncTable_name']);

		if ( $this->mongo_db->fields_uncase ) { // Если преобразуем названия полей в нижний регистр
			$_key = strtolower($val['SyncTable_key']);
		}
		else {
			$_key = $val['SyncTable_key'];
		}

		$this->mongo_db->drop_collection($this->mongo_db->dbname, $_name); // удаление таблицы

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
			$key_empty = false;

			// цикл по данным
			while ( $v = $response->_fetch_assoc() ) {
				$vlc = array_change_key_case($v);

				if ( $this->mongo_db->fields_uncase ) {
					$v = $vlc;
				}

				if ( isset($vlc[$_key]) ) {
					$v['_id'] = (float)$v[$_key];
					array_walk($v, 'ConvertFromWin1251ToUTF8');
					array_walk($v, 'convertFieldToInt');

					// Загоняем строки в БД
					$this->mongo_db->insert($_name, $v);
				}
				else {
					$key_empty  = true;
				}
			}

			if ( $key_empty ) {
				// Собираем ошибки
				array_push($errors, $val['SyncTable_name'] . ': Требуемый первичный ключ ' . $val['SyncTable_key'] . ' в запросе отсутствует (должно быть заполнено поле LocalDbList_sql).');
			}
		}

		return $errors;
	}

	/**
	 *	Функция получает данные справочника {$data['object']} из MongoDB
	 */
	function getData($data, $post) {

		if ($this->mongo_db->error==0) { // если MongoDB запущен и соединение с БД установлено
			$this->load->helper('MongoDB');
			// Обрабатываем условия
			$where = (isset($post['where']))?$post['where']:'';
			//$where = "where UslugaType_id = 2 and Usluga_Code iLIKE '0102%' and ((Usluga_begDT is null OR Usluga_begDT <= '2012-10-01 00:00:00') AND (Usluga_endDT is null OR Usluga_endDT >= '2012-10-01' )) limit 100";

			// враппер
			//$where = "where VizitType_Code > 5"; //
			//$where = "where UslugaType_id = 2 and Usluga_Code in ( '02110140', 02110141, 02110142, 02110143, 02110144, 02110145, 02110146, 02110147, 02110148, 02110149, 02110150, 02110151, 02110171, 02110172, 02110173, 02110174, 04231108, 04231131, 02240132, 04280121 ) ";
			/*preg_match('/(?:WHERE\s+(.+?)(?:LEFT|JOIN|ON|RIGHT|CROSS|INNER|NATURAL|HAVING|GROUP|ORDER|LIMIT|$))/im', $where, $m);
		 	print_r($m);
			*/
			// Здесь надо будет еще включить случаи которые будут разбираться вручную
			// Разбираем Sql-запрос
			//$where = "where (Usluga_begDT = '' OR Usluga_begDT <= '2011-01-17')";
            // @todo поправить условие IN , чтобы работало не только в конце
			$this->mongo_db->wheres = mongo_getwhere($where);
			//var_dump($this->mongo_db->wheres);
			//print_r($this->mongo_db->wheres);
			$this->mongo_db->sorts = mongo_getorder($where);
			$w = mongo_getlimit($where);
			$this->mongo_db->limit = $w['limit'];
			$this->mongo_db->offset = $w['skip'];
			//$this->mongo_db->limit($w['limit']);
			//$this->mongo_db->offset($w['skip']);
			//$this->mongo_db->orderby($w['skip']);

			// Очищаем параметры от управляющих переменных
			unset($post['object']);
			if (array_key_exists('where', $post)) {
				unset($post['where']);
			}
			// поскольку MongoDB регистрозависимая, переводим название таблицы и названия полей в нижний регистр
			$data['object'] = strtolower($data['object']);
			$fields = array_change_key_case($post);
			$map = ($this->mongo_db->fields_uncase)?$this->getMap($post):array();
			// Получаем данные для требуемого справочника
			if (count($fields)>0) {
				/*
				if ($data['object']=='OrgSMO') {
					print_r($this->getFields($post));
				}
				*/
				$spr = $this->mongo_db->select($this->getFields($fields))->get($data['object'], $map);
			} else {
				$spr = $this->mongo_db->get($data['object'], $map);
			}
			// определяем есть ли в полученном списке поля типа дата, и если есть - обрабатываем список 
			if (count($spr)>0) {
				foreach ($spr as $i => $item) {
					foreach($item as $prop => $val) {
						if (is_object($val) && property_exists($val, 'date')) {
							$val = objectToArray($val);
						}
						if (is_array($val) && isset($val['date'])) {
							$spr[$i][$prop] = ConvertDateEx($val['date'], '-', '.'); // преобразуем дату из формата array(date) в формат d.m.y
						}
					}
				}
			}
			
		} else { // Если соединение с МонгоДБ не установлено, то выбираем из БД
			show_error("Unable to connect to MongoDB", 500);
			// todo: Это отключаю, вместо этого вернется ошибка
			/*
			 $where = (isset($post['where']))?$post['where']:'';
			$limit = null; $skip = 0;
			// limit перерабатываем для top
			if (strpos(strtoupper($where), 'LIMIT')!==false) {
				preg_match('/(?:LIMIT\s+([\d\*\,\s]+))/im', $where, $m);
				if (isset($m[1])) {
					$data = explode(',',$m[1]);
					if (count($data)>1) {
						$limit = $data[1];
						$skip = $data[0];
					} elseif (isset($data[0])) {
						$limit = $data[0];
					}
				}
				$where  = stristr($where,'limit', true); // todo: проверить правильность
			}
			// Очищаем параметры от управляющих переменных
			unset($post['object']);
			if (array_key_exists('where', $post)) {
				unset($post['where']);
			}
			// Получаем данные для требуемого справочника
			$spr = $this->getDataFromObject($post, $data['object'], $where, $limit);
			*/
		}
		//print_r($spr);
		return $spr;
	}

	/**
	 *	Функция получает данные справочника {$data['object']} из MongoDB
	 */
	function getDataAll($data, $post) {
		$result = array();
		$ref = json_decode($data['data'], true); // данные требуемых справочников
		if (is_array($ref) && (count($ref)>0)) {
			// выбираем данные из бд и собираем в один ассоц. массив
			foreach ($ref as $k => $v) {
				$post = (isset($v['baseparams']) && is_array($v['baseparams']))?$v['baseparams']:array();
				$post['where'] = (isset($v['params']) && isset($v['params']['where']))?$v['params']['where']:null;
				if (isset($post['object'])) {
					$data['object'] = $post['object'];
					//unset($post['object']);
				} else {
					$data['object'] = $k;
				}
				$result[$k] = $this->getData($data,$post);
			}
		}
		return $result;
	}

	/**
	 * Удаляет текущую БД MongoDB
	 * Используется в случаях когда данные в БД нужно "залить" заново. Функция доступна только пользователю с правами "суперадмин".
	 */
	function drop_db() {
		if (isSuperadmin() && (extension_loaded('mongo_db'))) {
			$this->mongo_db->drop_db($this->mongo_db->dbname);
		}
	}

	/**
	 *	Выполнение запроса выборки данных для комбо при неработающем MongoDB (не работает, нужно дорабатывать, если конечно нужно)
	 */
	function getDataFromObject($post, $object, $where, $limit) {
		$fields = '';
		$filter = 'where (1=1)';
		$params = array();
		foreach ($post as $index => $row) {
			if (!empty($row)) {
				if ($index == 'Server_id') { // обработка фильтра на Server_id
					$filter .= " and " . $index . " in (0, :Server_id)";
					$params['Server_id'] = $row;
				} else if (($row!='null') && ($row!='not null')) { // todo: обработка null и is null, топорно
					$filter .= " and " . $index . " = :" . $index;
					$params[$index] = $row;
				} else {
					$filter .= " and " . $index . " is " . $row;
				}
			}
			$fields .= $index . " as \"$index\", ";
		}
		if (!empty($fields)) {
			$fields = substr($fields, 0, strlen($fields) - 2);
		} else {
			$fields = "*";
		}
		$sql = 'Select ';
		$sql = $sql.' '.$fields.' ';
		// todo: вот здесь вот выборка должна быть не из объекта с этим названием, а надо еще определить объект из которого выбирать по таблице stg.LocalDbList
		$sql = $sql.'from v_'.$object;
		$sql = $sql.' '.$filter; //$where
        if (isset($limit) && ($limit>0)) {
            $sql = $sql.'limit '.$limit.' ';
        }
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Получение списка последних 25 версий
	 * @param $data
	 * @return bool|mixed
	 */
	function getLocalDBVersion($data) {
		$fields = '';
		$filter = 'where (1=1)';
		$params = array();
		$sql = "
			Select
				LocalDBVersion_id as \"LocalDBVersion_id\",
				LocalDBVersion_Ver as \"LocalDBVersion_Ver\",
				to_char(cast (LocalDBVersion_setDate as timestamp ), 'DD.MM.YYYY')
					|| ' ' || to_char(cast (LocalDBVersion_setDate as timestamp ), 'HH24:MI:SS'
				) as \"LocalDBVersion_setDate\"
			from stg.LocalDBVersion
			order by LocalDBVersion_id desc
			limit 25
		";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение списка таблиц-справочников конкретной версии $data['LocalDBVersion_id']
	 * @param $data
	 * @return bool|mixed
	 */
	function getLocalDBFiles($data) {
		$fields = '';
		$params = array('LocalDBVersion_id'=>$data['LocalDBVersion_id']);
		$sql = '
		Select LocalDBTables_id as "LocalDBTables_id", LocalDBVersion_id as "LocalDBVersion_id", LocalDBTables_Name as "LocalDBTables_Name"
		from stg.LocalDBTables
		where LocalDBVersion_id = :LocalDBVersion_id
		order by LocalDBTables_Name';
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка всех справочников, доступных для загрузки
	 * @param $data
	 * @return bool|mixed
	 */
	function getLocalDbList($data) {
		$fields = '';
		$filter = '(1=1)';
		$params = array();

		$filter .= " and (LocalDbList_module IN ('promed', 'emd'))"; // todo: Пока только для промеда, а вообще сюда надо передавать переменную с клиента (или определять ее на стороне сервера)

		if (strlen($data['LocalDbList_name'])>0) {
			$filter .= ' and (LocalDbList_name iLIKE :LocalDbList_name)';

			$params['LocalDbList_name'] = '%'.$data['LocalDbList_name'].'%';
		}
		$sql = '
		Select
			LocalDbList_id as "LocalDbList_id",
			LocalDbList_name as "LocalDbList_name",
			LocalDbList_prefix as "LocalDbList_prefix",
			LocalDbList_nick as "LocalDbList_nick",
			LocalDbList_schema as "LocalDbList_schema",
			LocalDbList_sql as "LocalDbList_sql",
			LocalDbList_key as "LocalDbList_key",
			LocalDbList_module as "LocalDbList_module",
			LocalDbList_Descr as "LocalDbList_Descr"
		from stg.LocalDbList
		where '.$filter.'
		order by LocalDbList_name';
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение одной записи из справочника LocalDbList по Id
	 * @param $data
	 * @return bool|mixed
	 */
	function getLocalDbListRecord($data) {
		$fields = '';
		$filter = '(1=1)';
		$params = array();

		$params['LocalDbList_id'] = $data['LocalDbList_id'];
		$sql = '
		Select
			LocalDbList_id as "LocalDbList_id",
			LocalDbList_name as "LocalDbList_name",
			LocalDbList_prefix as "LocalDbList_prefix",
			LocalDbList_nick as "LocalDbList_nick",
			LocalDbList_schema as "LocalDbList_schema",
			LocalDbList_sql as "LocalDbList_sql",
			LocalDbList_key as "LocalDbList_key",
			LocalDbList_module as "LocalDbList_module"
		from stg.LocalDbList
		where LocalDbList_id = :LocalDbList_id
		';
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение данных в списке "локальных" справочников LocalDbList
	 * @param $data
	 * @return bool|mixed
	 */
	function saveLocalDbList($data) {
		$fields = '';
		$filter = '(1=1)';
		$proc = 'stg.p_LocalDbList_ins';
		if ($data['LocalDbList_id']>0) {
			$proc = 'stg.p_LocalDbList_upd';
		}
		$sql = '
			select LocalDbList_id as "LocalDbList_id", Error_Code as "Error_Code", Error_Message as "Error_Msg"
			from '.$proc.'(
				LocalDbList_id := :LocalDbList_id,
 				LocalDbList_name := :LocalDbList_name,
				LocalDbList_prefix := :LocalDbList_prefix,
				LocalDbList_nick := :LocalDbList_nick,
				LocalDbList_schema := :LocalDbList_schema,
				LocalDbList_sql := :LocalDbList_sql,
				LocalDbList_key := :LocalDbList_key,
				LocalDbList_module := :LocalDbList_module,
				LocalDbList_Descr := :LocalDbList_Descr,
				pmUser_id := :pmUser_id);
		';
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление записи из списка "локальных" справочников LocalDbList
	 * @param $data
	 * @return bool|mixed
	 */
	function deleteLocalDbList($data) {
		$fields = '';
		try {
			$this->db->trans_begin();

			$result = $this->getRegionalLocalDbList($data);
			if(!is_array($result)){
				throw new Exception("Ошибка при получении списка региональных запросов.");
			}
			if(count($result)>0){
				foreach ($result as $value) {
					$res = $this->deleteRegionalLocalDbList(array('id'=>$value['RegionalLocalDbList_id']));
					if(!is_array($res)){
						throw new Exception("Ошибка при удалении регионального запроса.");
					}
					if(!empty($res['Error_Msg'])){
						throw new Exception("Ошибка. ".$res['Error_Msg']);
					}
				}
			}

			$sql = '
				select Error_Code as "Error_Code", Error_Message as "Error_Msg"
				from stg.p_LocalDbList_del(
					LocalDbList_id := :id);
			';
			$result = $this->db->query($sql, $data);
			if ( is_object($result) ) {
				$result = $result->result('array');
				if(!is_array($result)){
					throw new Exception("Ошибка при запросе к БД");
				}
				if(!empty($result['Error_Msg'])){
					throw new Exception("Ошибка. ".$result['Error_Msg']);
				}
			} else {
				throw new Exception("Ошибка при запросе к БД");
			}

			$this->db->trans_commit();
		} catch (Exception $e) {
			$this->db->trans_rollback();
			return array('success'=>false,'Error_Msg'=>$e->getMessage());
		}
		return array('success'=>true);
	}

	/**
	 * Получение списка региональных запросов для справочника
	 * @param $data
	 * @return bool|mixed
	 * $oneRecord - признак загрузки записи по идентификатору
	 */
	function getRegionalLocalDbList($data,$oneRecord = false) {
		$fields = '';
		$filter = '(1=1)';
		$params = array();

		if($oneRecord){
			if(!empty($data['RegionalLocalDbList_id'])){
				$filter .= " and RegionalLocalDbList_id = :RegionalLocalDbList_id";
				$params['RegionalLocalDbList_id'] = $data['RegionalLocalDbList_id'];
			} else {
				return array();
			}
		} else {
			if(!empty($data['LocalDbList_id'])){
				$filter .= " and LocalDbList_id = :LocalDbList_id";
				$params['LocalDbList_id'] = $data['LocalDbList_id'];
			} else {
				return array();
			}
		}

		$sql = '
		Select
			RegionalLocalDbList_id as "RegionalLocalDbList_id",
			LocalDbList_id as "LocalDbList_id",
			Region_id as "Region_id",
			RegionalLocalDbList_Sql as "RegionalLocalDbList_Sql",
			RegionalLocalDbList_PgSql as "RegionalLocalDbList_PgSql"
		from stg.RegionalLocalDbList
		where '.$filter.'
		order by Region_id';
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение данных в списке "локальных" справочников RegionalLocalDbList
	 * @param $data
	 * @return bool|mixed
	 */
	function saveRegionalLocalDbList($data) {
		$fields = '';
		$filter = '(1=1)';
		$proc = 'stg.p_RegionalLocalDbList_ins';
		if ($data['RegionalLocalDbList_id']>0) {
			$proc = 'stg.p_RegionalLocalDbList_upd';
		}
		$sql = '
			select RegionalLocalDbList_id as "RegionalLocalDbList_id", Error_Code as "Error_Code", Error_Message as "Error_Msg"
			from '.$proc.'(
				RegionalLocalDbList_id := :RegionalLocalDbList_id,
 				LocalDbList_id := :LocalDbList_id,
				Region_id := :Region_id,
				RegionalLocalDbList_Sql := :RegionalLocalDbList_Sql,
				RegionalLocalDbList_PgSql := :RegionalLocalDbList_PgSql,
				pmUser_id := :pmUser_id);
		';
		//echo getDebugSQL($sql, $data);exit;
		$result = $this->db->query($sql, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление записи из списка "локальных" справочников LocalDbList
	 * @param $data
	 * @return bool|mixed
	 */
	function deleteRegionalLocalDbList($data) {
		$fields = '';
		$sql = '
			select Error_Code as "Error_Code", Error_Message as "Error_Msg"
			from stg.p_RegionalLocalDbList_del(
				RegionalLocalDbList_id := :id);
		';
		$result = $this->db->query($sql, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Проверка наличия тестовой версии (сборочной)
	 * @param $data
	 * @return bool|mixed
	 */
	function getTestVersion($data) {
		$sql = '
		Select
			LocalDBVersion_id as "LocalDBVersion_id"
		from stg.LocalDBVersion
		where LocalDBVersion_Ver = 0
        limit 1';
		$result = $this->db->query($sql);
		if ( is_object($result) ) {
			if (count($result->result('array'))>0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка наличия таблиц в тестовой версии
	 * @param $data
	 * @return bool|mixed
	 */
	function isVersionFiles($data) {
		$sql = '
		Select 
			count(f.LocalDBTables_id) as "cnt"
		from stg.LocalDBVersion v 

		left join stg.LocalDBTables f on f.LocalDBVersion_id = v.LocalDBVersion_id
		where LocalDBVersion_Ver = 0
        limit 1';
		$result = $this->db->query($sql);
		if ( is_object($result) ) {
			$r = $result->result('array');
			if (count($r)>0) {
				if ($r[0]['cnt']>0)
					return true;
			}
		}
		return false;
	}

	/**
	 * Создание сборочной версии
	 * @param $data
	 * @return bool|mixed
	 */
	function createVersion($data) {
		if (!$this->getTestVersion($data)) {
			$params = array('pmUser_id'=>$data['pmUser_id']);
			$sql = '
			select Error_Code as "Error_Code", Error_Message as "Error_Msg"
			from stg.xp_LocalDBVersion_generate(
					pmUser_id := :pmUser_id,
					isTest := 1);'; // второе поле - это признак того что версия не рабочая, а "редактируемая"
			//echo getDebugSql($sql, $params);exit;
			$result = $this->db->query($sql, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return array(array('success'=>false, 'Error_Code'=>1, 'Error_Msg'=>'Сборочная версия уже существует.'));
		}

	}

	/**
	 * Фиксация сборочной версии
	 * @param $data
	 * @return bool|mixed
	 */
	function fixedVersion($data) {
		// предварительно сохраняем все справочники если они еще не сохранены
		$files = explode('|', $data['tables']);
		$params = array('pmUser_id'=>$data['pmUser_id'], 'LocalDBVersion_id'=>$data['LocalDBVersion_id']);

		for($i=0; $i<count($files); $i++) {
			if (strlen($files[$i])>0) {
				$params['LocalDBTables_name'] = $files[$i];
				$sql = '
				select 
					LocalDBTables_id as "LocalDBTables_id", 
					Error_Code as "Error_Code", 
					Error_Message as "Error_Msg"
				from stg.p_LocalDBTables_ins(
					LocalDBTables_id := null,
					LocalDBVersion_id := :LocalDBVersion_id,
					LocalDBTables_name := :LocalDBTables_name,
					pmUser_id := :pmUser_id
				);';
				$result = $this->db->query($sql, $params);
				if (!is_object($result)) {
					return false;
				}
				// todo: Надо обработку сохранения сделать
			}
		}

		if ($this->isVersionFiles($data)) {
			$params = array('pmUser_id'=>$data['pmUser_id']);
			$sql = '
			select 
				Error_Code as "Error_Code", 
				Error_Message as "Error_Msg"
			from stg.xp_LocalDBVersion_fixed(
				pmUser_id := :pmUser_id
			);';
			$result = $this->db->query($sql, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return array(array('success'=>false, 'Error_Code'=>1, 'Error_Msg'=>'Сборочная версия отсутствует или не содержит данных.'));
		}

	}

	/**
	 * Удаление сборочной версии
	 * @param $data
	 * @return bool|mixed
	 */
	function sendVersionMQmessage($data) {

		$sql = "Select
			LocalDBVersion_id as \"LocalDBVersion_id\", 
			LocalDBVersion_Ver as \"LocalDBVersion_Ver\",
			LocalDBVersion_setDate as \"LocalDBVersion_setDate\",
			to_char(cast(LocalDBVersion_setDate as timestamp), 'YYYY-MM-DD HH24:MI:SS') as \"LocalDBVersion_setDate\"

			


		from stg.LocalDBVersion 
		where LocalDBVersion_id = :LocalDBVersion_id
		order by LocalDBVersion_id desc
        limit 1";

		$result = $this->db->query($sql, array('LocalDBVersion_id' => $data['LocalDBVersion_id']) );

		if ( is_object($result) ) {

			$files = explode('|', $data['tables']);
			$addedVersionRecord = $result->result('array');
			$addedVersionRecord = $addedVersionRecord[0];

			$paramsMQ = array(
				"tables" => $files,
				"version" => $addedVersionRecord["LocalDBVersion_Ver"],
				"date" => $addedVersionRecord["LocalDBVersion_setDate"],
			);
			//var_dump($paramsMQ);
			if (defined('STOMPMQ_MESSAGE_DESTINATION_RULE')) {
				sendStompMQMessage($paramsMQ, 'Rule', STOMPMQ_MESSAGE_DESTINATION_RULE);
			}
			return array(array('success'=>true, 'Error_Code'=>null));

		} else {
			return array(array('success'=>false, 'Error_Code'=>1, 'Error_Msg'=>'Ошибка при получени информации о сборочной версии'));
		}
	}

	/**
	 * Удаление сборочной версии
	 * @param $data
	 * @return bool|mixed
	 */
	function deleteVersion($data) {
		if ($this->getTestVersion($data)) {
			$params = array('LocalDBVersion_id'=>$data['id']);
			$sql = '
			select 
				:LocalDBVersion_id as "LocalDBVersion_id", 
				Error_Code as "Error_Code", 
				Error_Message as "Error_Msg"
			from stg.p_LocalDBVersion_del(
				LocalDBVersion_id := :LocalDBVersion_id
			);';
			//echo getDebugSql($sql, $params);exit;
			$result = $this->db->query($sql, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return array(array('success'=>false, 'Error_Code'=>1, 'Error_Msg'=>'Сборочная версия не существует.'));
		}
	}

	/**
	 * Сохранение данных в "удаленном локальном" хранилище
	 * @param $post
	 * @param $data
	 * @return bool|mixed
	 */
	function saveData($post, $data) {
		//print_r($data);
		if ($this->mongo_db->error==0) { // если MongoDB запущен и соединение с БД установлено
			$this->load->helper('MongoDB');
			// Обрабатываем условия
			$where = (isset($data['where']))?$data['where']:'';
			$this->mongo_db->wheres = mongo_getwhere($where);
			$object = $data['object'];
			// Очищаем параметры от управляющих переменных
			unset($post['object']);
			if (array_key_exists('where', $post)) {
				unset($post['where']);
			}
			$post['pmUser_id'] = (int)$data['pmUser_id'];
			$this->mongo_db->wheres['pmUser_id'] = (int)$data['pmUser_id'];
			$wheres = $this->mongo_db->wheres;
			$spr = array();
			// Проверяем наличие данных по данной записи
			if (strlen($where)>0) {
				if (count($post)>0) {
					$spr = $this->mongo_db->select($this->getFields($post))->get($object);
				} else {
					$spr = $this->mongo_db->get($object);
				}
				//print_r($spr);
			} elseif (isset($data['_id'])) { // Если Id уже известен
				// здесь проверка наличия данных по конкретному ID
			}
			$this->mongo_db->wheres = $wheres;
			// Если такая запись в этом объекте уже существует (проверяем по where или _id), то апдейтим существующую запись, иначе создаем новую
			array_walk($post, 'ConvertFromWin1251ToUTF8');
			array_walk($post, 'convertFieldToInt');
			if (is_array($spr) && (count($spr)>0)) {
				// Загоняем строки в БД
				$this->mongo_db->update($object, $post);
			} else {
				/*
				print_r($spr);
				print_r($post);
				print_r($this->mongo_db->wheres);
				*/
				// а здесь вставка новых записей
				$this->mongo_db->insert($object, $post);
			}
		} else { // Если соединение с МонгоДБ не установлено, то выбираем из БД
			show_error("Unable to connect to MongoDB", 500);
		}
		return array(array(
			'success' => true,
			'Error_Msg' => ''
		));
	}


	/**
	 *	Комментарий
	 */
	function loadDirectoryListGrid($data) {
		$filter = "1=1";
		$queryParams = array();
		if( !empty($data['Directory_Name']) ) {
			$filter .= " and (
				LocalDbList_name ilike '%' || :Directory_Name || '%'
				or LocalDbList_Descr ilike '%' || :Directory_Name || '%'
				or col_description((c.Table_schema||'.'||c.table_name)::regclass, ordinal_position) ilike '%' || :Directory_Name || '%'
			)";
			$queryParams['Directory_Name'] = $data['Directory_Name'];
		}
		$filter .= " and LocalDbList_module = 'promed'";

		$query = "
			select
				LocalDbList_id as \"LocalDbList_id\",
				LocalDbList_name as \"LocalDbList_name\",
				LocalDbList_prefix as \"LocalDbList_prefix\",
				LocalDbList_nick as \"LocalDbList_nick\",
				LocalDbList_schema as \"LocalDbList_schema\",
				LocalDbList_sql as \"LocalDbList_sql\",
				LocalDbList_key as \"LocalDbList_key\",
				LocalDbList_module as \"LocalDbList_module\",
				coalesce(
					LocalDbList_Descr,
					col_description((c.Table_schema||'.'||c.table_name)::regclass, ordinal_position)
				) as LocalDbList_Descr,
				to_char(LocalDbList_insDT, 'DD.MM.YYYY') as \"LocalDbList_insDT\",
				to_char(LocalDbList_updDT, 'DD.MM.YYYY') as \"LocalDbList_updDT\"

			from
				stg.LocalDbList
				inner join information_schema.columns C on c.table_name = lower(LocalDbList_prefix)
					and c.table_schema = lower(LocalDbList_schema)
			where
				{$filter}
		";
		//echo getDebugSql($query, $queryParams);
		$result = $this->queryResult($query, $queryParams);
		if ($result) {
			foreach ($result as $i => $res) {
				foreach ($res as $key => $value) {
					$result[$i][$key] = mb_strtolower($value);
				}
			}

			return $result;
		} else {
			return false;
		}
	}

	/**
	 *	Возвращает массив полей, характерный для заданного справочника из РЛС
	 */
	function getRlsFields($dirName) {
		$map = array(
			'Countries' => array("id" => "COUNTRIES_ID", "Code" => "", "Name" => "NAME", "SysNick" => ""),
			'Firms' => array("id" => "FIRMS_ID", "Code" => "", "Name" => "FULLNAME", "SysNick" => ""),
			'Actmatters' => array("id" => "ACTMATTERS_ID", "Code" => "", "Name" => "RUSNAME", "SysNick" => ""),
			'Desctextes' => array("id" => "DESCID", "Code" => "", "Name" => "", "SysNick" => ""),
			'Clspharmagroup' => array("id" => "CLSPHARMAGROUP_ID", "Code" => "", "Name" => "NAME", "SysNick" => ""),
			'Clsiic' => array("id" => "CLSIIC_ID", "Code" => "", "Name" => "NAME", "SysNick" => ""),
			'Clsatc' => array("id" => "CLSATC_ID", "Code" => "", "Name" => "NAME", "SysNick" => ""),
			'Clsdrugforms' => array("id" => "CLSDRUGFORMS_ID", "Code" => "", "Name" => "FULLNAME", "SysNick" => ""),
			'Tradenames' => array("id" => "TRADENAMES_ID", "Code" => "", "Name" => "NAME", "SysNick" => "")
		);
		return $map[$dirName];
	}

	/**
	 * Получение строк inner join и select для запросов по справочникам НСИ
	 * @param $directory
	 * @param $prefix
	 * @return array
	 */
	function getDirectoryNsiInnerSelect( $directory, $prefix ) {
		$inner = '';
		$select = '';

		$requiredFields = [
			'RefTableRegistry' => [],
			'RefTableRegistryVersion' => [
				'RefTableRegistry' => [
					'columns' => ['RefTableRegistry_Nick','RefTableRegistry_Oid'],
					'inner' => "inner join nsi.RefTableRegistry RefTableRegistry on {$prefix}.RefTableRegistry_id = RefTableRegistry.RefTableRegistry_id "
				]
			],
			'RefTableRegistryVersionFile' => [
				'RefTableRegistryVersion' => [
					'columns' => ['RefTableRegistryVersion_Num'],
					'inner' => "inner join nsi.RefTableRegistryVersion RefTableRegistryVersion on {$prefix}.RefTableRegistryVersion_id = RefTableRegistryVersion.RefTableRegistryVersion_id "
				],
				'RefTableRegistry' => [
					'columns' => ['RefTableRegistry_Nick','RefTableRegistry_Oid'],
					'inner' => "inner join nsi.RefTableRegistry on RefTableRegistry.RefTableRegistry_id = RefTableRegistryVersion.RefTableRegistry_id "
				]
			]
		];

		if ( !empty( $requiredFields[$directory] ) ) {
			foreach ( $requiredFields[$directory] as $directName=>$direct ) {
				$inner = $inner . $direct['inner'];

				foreach ( $direct['columns'] as $column ) {
					$select = $select . $directName . '.' . $column . ', ';
				}
			}
		}

		return [ $inner, $select ];
	}


	/**
	 * @param $data
	 * @return array|bool
	 */
	function getDirectoryFields($data) {
		$scheme = $data['Directory_Schema'];
		if( preg_match("/([^_]+)_/", $data['Directory_Name'], $matches) ) {
			$scheme = $matches[1];
		}

		$data['Directory_Name']= $this->checkForFed($data['Directory_Name']);

		$dirName = str_replace($scheme."_", "", $data['Directory_Name']);

		if ( $scheme == 'nsi' ) {
			$columns = $this->getColumnsOnTableNsi(array('scheme' => $scheme, 'table' => $dirName));
		} else {
			$columns = $this->getColumnsOnTable(array('scheme' => $scheme, 'table' => $dirName));
		}

		if( count($columns) == 0 ) {
			DieWithError("В БД не существует справочника <b>{$scheme}.{$dirName}</b>, вероятно его наименование было изменено или он был удален.");
			return false;
		}

		$ignoreFields = array(
			'pmuser_insid',
			'pmuser_updid',
			$dirName.'_insdt',
			$dirName.'_upddt',
			'insdt',
			'upddt'
		);

		$fields = array();

		foreach($columns as $column) {
			if (in_array(strtolower($column['name']), $ignoreFields)) {
				continue;
			}
			$fields[] = $column;
		}

		$fields = array_merge($fields, array(
			array('name' => 'id', 'descr' => 'id', 'type' => 'bigint', 'hidden' => true),
			array('name' => 'keyName', 'descr' => 'keyName', 'type' => 'varchar', 'hidden' => true),
			array('name' => 'updDT', 'descr' => 'Изменен', 'type' => 'datetime'),
			array('name' => 'Editor', 'descr' => 'Редактор', 'type' => 'varchar'),
		));

		return $fields;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getDirectoryData($data) {
		$scheme = $data['Directory_Schema'];
		if( preg_match("/([^_]+)_/", $data['Directory_Name'], $matches) ) {
			$scheme = $matches[1];
		}

		$data['Directory_Name']= $this->checkForFed($data['Directory_Name']);

		$dirName = str_replace($scheme."_", "", $data['Directory_Name']);
		$prefix = "DIR";

		$fields = $this->getColumnsOnTable(array('scheme' => $scheme, 'table' => $dirName));
		if( count($fields) == 0 ) {
			DieWithError("В БД не существует справочника <b>{$scheme}.{$dirName}</b>, вероятно его наименование было изменено или он был удален.");
			return false;
		}

		list( $nsiInnerJoin, $nsiSelect ) = $this->getDirectoryNsiInnerSelect( $data['Directory_Name'], $prefix );

		$ignoreFields = array(
			'pmuser_insid',
			'pmuser_updid',
			$dirName.'_insdt',
			$dirName.'_upddt',
            $dirName.'_rowversion',
            'insdt',
            'upddt',
            'rowversion'
		);

		$params = array();

		$idField = null;
		$filterField = null;
		$orderBy = '';
		$select = [];
		$updDtField = "{$dirName}_updDT";

		if ( in_array($data['Directory_Name'],['RefTableRegistry','RefTableRegistryVersion','RefTableRegistryVersionFile']) ) {
			if ( $data['directoryContentSearchPanelType'] == 'byName' ) {
				$filterField = [ 'RefTableRegistry_Nick' ];
				if ( $dirName == 'RefTableRegistry' ) $filterField[] = 'RefTableRegistry_FullName';
			} else if ( $data['directoryContentSearchPanelType'] == 'byOid' ) {
				$filterField = [ 'RefTableRegistry_Oid' ];
			} else {
				$filterField = [ 'RefTableRegistry_SysNick' ];
			}
		}

		foreach($fields as $field) {
			if (strtolower($field['name']) == 'upddt') {
				$updDtField = 'upddt';
			}
			if (in_array(strtolower($field['name']), $ignoreFields)) {
				continue;
			}
			if ($field['primary_key']) {
				$idField = $field['name'];
			}
			if ($field['primary_key'] || strtolower($field['name']) == $dirName.'_code' || strtolower($field['name']) == 'code') {
				$orderBy = "{$prefix}.{$field['name']}";
			}

			if (!empty($data['directoryContentSearchPanelType']) && $data['directoryContentSearchPanelType'] == 'byName') {
				if ($field['name'] == $dirName.'_Name') {
					$filterField[] = $dirName.'_Name';
				}
				if ($field['name'] == $dirName.'_FullName') {
					$filterField[] = $dirName.'_FullName';
				}
				if (mb_strtolower($field['name']) == 'name') {
					$filterField[] = $field['name'];
				}
			}

			if ($field['type'] == 'date') {
				$select[] = "to_char({$field['name']}, 'YYYY-MM-DD HH24:MI:SS') as \"{$field['name']}\"";

			} else if ($field['type'] == 'timestamp') {
				$select[] = "to_char({$field['name']}, 'YYYY-MM-DD HH24:MI:SS') as \"{$field['name']}\"";

			} else {
				$select[] = "{$prefix}.{$field['name']}";
			}
		}
		$select = implode(",\n\t\t\t\t", $select);

		$viewName = "v_".$dirName;
		$query = "
			select column_name as \"column_name\"
			from v_columns 

			where table_name = '{$viewName}' 
			--and table_type = 'V' 
			and schema_name = '{$scheme}'
			limit 1
		";
		$resp = $this->queryResult($query);
		if (!is_array($resp) || count($resp) == 0) {
			$viewName = $dirName;
		}

		$where = '1=1';
		if (!empty($data['filterElementsByName'])) {
			if (!empty($filterField)) {
				$where = '';
				$params['filterElementsByName'] = $data['filterElementsByName'];
				foreach ( $filterField as $filter ) {
					$where = $where . "upper({$filter}) like upper('%' + :filterElementsByName+'%') or ";
				}
				$where = rtrim( $where, ' or ');
			} else {
				$where = '1=0';
			}
		}

		$select = $nsiSelect . $select;

		$query = "
			select
				-- select
				{$select},
				{$prefix}.{$idField} as \"id\",
				'{$idField}' as \"keyName\",
				to_char({$prefix}.{$updDtField}, 'YYYY-MM-DD HH24:MI:SS') as \"updDT\",

				case
					when PUC.PMUser_Login is not null
					then rtrim(PUC.PMUser_Login) || ' (' ||rtrim(PUC.PMUser_Name) || ')'
					else 'Система'
				end as \"Editor\"
				-- end select
			from
				-- from
				{$scheme}.{$viewName} {$prefix} 
				{$nsiInnerJoin}
				left join v_pmUserCache PUC on PUC.PMUser_id = {$prefix}.pmUser_updID
				-- end from
			where
				-- where
				{$where}
				-- end where
			order by
				-- order by
				{$orderBy}
				-- end order by
		";

		//echo getDebugSQL($query, $params);exit;

		$count = $this->queryResult(getCountSQLPH($query), $params);
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);

		return array(
			'totalCount' => $count[0]['cnt'],
			'data' => $result,
		);
	}

	/**
	 * Получение колонок для таблиц НСИ
	 * @param $data
	 * @return array
	 */
	function getColumnsOnTableNsi( $data ) {
		$directory = $data['table'];

		$requiredFields = [
			'RefTableRegistry' => [],
			'RefTableRegistryVersion' => [
				'RefTableRegistry' => ['RefTableRegistry_Nick','RefTableRegistry_Oid']
			],
			'RefTableRegistryVersionFile' => [
				'RefTableRegistry' => ['RefTableRegistry_Nick','RefTableRegistry_Oid'],
				'RefTableRegistryVersion' => ['RefTableRegistryVersion_Num']
			]
		];

		$fields = [];

		if ( !empty( $requiredFields[$directory] ) ) {
			foreach ( $requiredFields[$directory] as $direct=>$directColumns ) {

				$columns = $this->getColumnsOnTable( [ 'scheme' => $data['scheme'], 'table' => $direct ] );

				foreach($columns as $column) {
					if ( in_array($column['name'], $directColumns) ) {
						$column['ref'] = $direct;
						$fields[] = $column;
					}
				}
			}
		}

		$fields = array_merge( $fields, $this->getColumnsOnTable( $data ) );

		return $fields;
	}


	/**
	 *	Возвращает список колонок в таблице $data['table']
	 */
    function getColumnsOnTable($data) {
		if( !isset($data) || !is_array($data) || !isset($data['scheme']) || !isset($data['table']) ) {
			return false;
		}
		$query = "
			SELECT
				a.attname AS \"name\",
				t.typname AS \"type\",
				CASE WHEN ti.attnotnull THEN 0 ELSE 1 END AS \"is_nullable\",
				case when i.indisprimary then 1 else 0 end AS \"primary_key\",
				coalesce(ds.description, a.attname) AS \"descr\",
				fk.ftblname AS \"fk_schema\",
				fk.name AS \"fk_name\"
			FROM
				pg_attribute a
				INNER JOIN pg_class c ON a.attrelid = c.oid
				INNER JOIN pg_type t ON a.atttypid = t.oid
				INNER JOIN pg_namespace n ON t.typnamespace = n.oid
				INNER JOIN pg_namespace rn ON c.relnamespace = rn.oid
				LEFT JOIN LATERAL (
					select
						ti_a.attnotnull,
						ti_a.attidentity,
						ti_a.attnum,
						ti_c.relname,
						ti_c.oid,
						ti_rn.nspname
					from
						pg_attribute ti_a
						inner join pg_class ti_c on ti_c.oid = ti_a.attrelid
						inner join pg_namespace ti_rn on ti_rn.oid = ti_c.relnamespace
					where
						ti_a.attnum > 0
						and ti_a.attisdropped <> 't'
						and ti_c.relname = lower(:table)
						and ti_rn.nspname = lower(:scheme)
						and ti_a.attname = a.attname
					limit 1
				) as ti on true
				left join lateral (
					SELECT
						i.indisprimary
		            FROM
						pg_index i
						INNER JOIN pg_class c ON i.indrelid = c.oid
						INNER JOIN pg_namespace n ON c.relnamespace = n.oid
		            WHERE
						(i.indisprimary = 't' OR i.indisunique = 't')
						and c.relname = ti.relname
						and n.nspname = ti.nspname
						and i.indkey::text = ti.attnum::text
					limit 1
				) i on true
				LEFT OUTER JOIN pg_description ds ON ds.objoid = ti.oid AND ds.objsubid = ti.attnum
				left join LATERAL (
					select
						con.oid,
						conname AS name,
						tbl.relname,
						tf.relname AS ftblname,
						sf.nspname
					FROM
						(
							select oid,contype, connamespace,conrelid,confrelid,conkey[i] as conkey, confkey[i] as confkey,confdeltype,confupdtype , conname
							from (select oid, contype,connamespace,conrelid,confrelid,conkey,confkey,confdeltype,confupdtype ,
							generate_series(1,array_upper(conkey,1)) as i,conname
							from pg_constraint
							where contype = 'f') ss
						) con
						INNER JOIN pg_namespace nsp ON con.connamespace=nsp.oid
						inner JOIN pg_class tbl ON con.conrelid=tbl.oid
						inner join pg_attribute ap on ap.attnum = conkey and ap.attrelid = conrelid
						left join pg_attribute af on af.attnum = confkey and af.attrelid = confrelid
						left join pg_class tf on tf.oid=confrelid
						left join pg_namespace sf on sf.oid=tf.relnamespace
					WHERE
						lower(nsp.nspname) = ti.nspname
						and lower( tbl.relname ) = ti.relname
						and ap.attnum = ti.attnum
					limit 1
				) fk on true
			WHERE
				a.attnum > 0
				and a.attisdropped <> 't'
				and c.relname = lower('v_'||:table)
				and rn.nspname = lower(:scheme)
			ORDER BY
				a.attnum
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			foreach ($result as $key => $value) {
				if (in_array($value['name'], ['rowversion', $data['table'] . '_rowversion'])) {
					unset($result[$key]);
					continue;
				}
				$result[$key]['is_nullable'] = intval($value['is_nullable']);
				$result[$key]['primary_key'] = intval($value['primary_key']);
			}
			return $result;
		} else {
			return false;
		}
	}
	/**
	 *	Получение данных выбранной записи справочника
	 */
	function getDirectoryRecord($data) {
		$query = "
			select 
				*
			from
				{$data['scheme']}.{$data['table']}
			where
				{$data['keyName']} = {$data['keyValue']}
			limit 1
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение строки справочника НСИ
	 * @param $data
	 * @param $columns
	 * @return bool
	 */
	function getDirectoryRecordNsi($data,$columns) {
		list( $nsiInnerJoin, $nsiSelect ) = $this->getDirectoryNsiInnerSelect( $data['table'], 'DIR' );

		$select = '';

		foreach ( $columns as $column ) {
			$prefix = (empty($column['ref']))?'DIR':$column['ref'];
			$select = $select . $prefix . '.' . $column['name'] . ' as "' . $column['name'] . '", ';
		}

		$select = rtrim( $select, ', ');

		$query = "
			select
				{$select} 
			from
				{$data['scheme']}.{$data['table']} as DIR
				{$nsiInnerJoin}
			where
				DIR.{$data['keyName']} = {$data['keyValue']}
			limit 1
		";
		//echo getDebugSQL($query);exit;

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение данных выбранной записи справочника для формы редактирования
	 */
	function getFormDataForDirectoryEditWindow($data) {

		$data['table']= $this->checkForFed($data['table']);

		// Получаем все поля таблицы
		if ( $data['scheme'] == 'nsi' ) {
			$columns = $this->getColumnsOnTableNsi(array('scheme' => $data['scheme'], 'table' => $data['table']));
		} else {
			$columns = $this->getColumnsOnTable(array('scheme' => $data['scheme'], 'table' => $data['table']));
		}
		if( count($columns) == 0 ) {
			DieWithError("В БД не существует справочника <b>{$data['scheme']}.{$data['table']}</b>, вероятно его наименование было изменено или он был удален.");
			return;
		}

		if( !empty($data['keyValue']) ) {
			if ( $data['scheme'] == 'nsi' ) {
				$records = $this->getDirectoryRecordNsi($data,$columns);
			} else {
				$records = $this->getDirectoryRecord($data);
			}
			if( !is_array($records) || count($records) == 0 ) {
				DieWithError("Ошибка при получении данных выбранной записи!");
				return;
			}
			$record = $records[0];
		}

		// Массив исключений - это те поля которые НЕ нужны на форме
        $exc = array($data['table']."_insdt", $data['table']."_upddt", "pmuser_insid", "pmuser_updid", $data['table']."_rowversion", /* Далее для РЛС */ strtoupper($data['table'])."_insdt", strtoupper($data['table'])."_upddt");
		$exc_tables = array('Server', 'Region', 'Evn');

        // Массив таблиц связанных по ключу со справочником для которых допустимо автоматическое создание swcommonsprcombo (в дополнении к созданию по имени поля)
        $fk_tables = array('YesNo');

		$fields = array();
		foreach($columns as $column) {
			if( !in_array($column['name'], $exc) ) {
				$additional = array();
				switch($column['type']) {
					case 'bigint':
						$additional['xtype'] = "textfield";
						if( $column['primary_key'] != 1 && (preg_match("/([^_]+)_.*id$/", $column['name'], $matches) || in_array($column['fk_name'], $fk_tables)) ) {
							$table = count($matches) > 0 ? $matches[1] : $column['fk_name'];
							if ( !in_array($table, $exc_tables) ) {
								// маппинг компонентов
								switch ($table . ($column['fk_schema'] != 'dbo' ? ucfirst($column['fk_schema']) : '')) {
									case 'UslugaComplex':
										$additional['xtype'] = 'swuslugacomplexnewcombo';
									break;
									case 'Lpu':
										$additional['xtype'] = 'swlpucombo';
									break;
									case 'LpuUnit':
										$additional['xtype'] = 'swlpuunitcombo';
									break;
									case 'Org':
										$additional['xtype'] = 'sworgcombo';
									break;
									case 'LpuSection':
										$additional['xtype'] = 'swlpusectioncombo';
									break;
									case 'LpuSectionProfile':
										$additional['xtype'] = 'swlpusectionprofilecombo';
									break;
									case 'MedSpecFed':
										$additional['xtype'] = 'swmedspecfedcombo';
									break;
									case 'MedSpecOmsFed':
										$additional['xtype'] = 'swmedspecomsfedcombo';
									break;
									default:
										$additional['xtype'] = 'swcommonsprcombo';
										$additional['comboSubject'] = $table;
									break;
								}

								$additional['table'] = $table;
								$additional['hiddenName'] = $column['name'];
							}
						}
						break;
					case 'timestamp':
						$additional['xtype'] = "swdatefield";
						$additional['plugins'] = "[ new Ext.ux.InputTextMask('99.99.9999', false) ]";
						if (isset($record) && is_object($record[$column['name']])) {
							$record[$column['name']] = ConvertDateFormat($record[$column['name']], 'd.m.Y');
						}
						break;
					default:
						$additional['xtype'] = "textfield";
						break;
				}

				$comp = array_merge(array(
					'allowBlank' => (bool)($column['is_nullable'] || $column['primary_key'] == 1)
					,'name' => $column['name']
					,'fieldLabel' => toUtf($column['descr'])
					,'value' => isset($record) ? toUtf($record[$column['name']]) : null
				), $additional);

				$fields[] = $comp;
			}
			if( $column['primary_key'] ) {
				$pk = $column['name'];
			}
		}

		$fields[] = array('xtype' => 'hidden', 'name' => 'scheme', 'value' => $data['scheme']);
		$fields[] = array('xtype' => 'hidden', 'name' => 'table', 'value' => $data['table']);
		$fields[] = array('xtype' => 'hidden', 'name' => 'keyName', 'value' => $pk);

		//if ($data['table'] == 'RefTableRegistry' ) {
		if ( $data['scheme'] == 'nsi' ) {
			$where = '';
			if ( $data['table'] == 'RefTableRegistryVersion' ) { /*echo $record['RefTableRegistryVersion_Num']*/
				$where = 'rtrv.RefTableRegistryVersion_id = :ident';
			} elseif ( $data['table'] == 'RefTableRegistry' ) {
				$where = 'rtrv.RefTableRegistry_id = :ident';
			} elseif ( $data['table'] == 'RefTableRegistryVersionFile' ) {
				$where = 'rtrvf.RefTableRegistryVersionFile_id = :ident';
			}

			$link = null;
			if (!empty($data['keyValue'])) {
				// тянем файл с последней версией
				$resp = $this->queryResult("
					select 
						RefTableRegistryVersionFile_Path as \"RefTableRegistryVersionFile_Path\"
					from
						nsi.v_RefTableRegistryVersionFile rtrvf 

						inner join nsi.v_RefTableRegistryVersion rtrv  on rtrv.RefTableRegistryVersion_id = rtrvf.RefTableRegistryVersion_id 

					where
						{$where}
					order by
						rtrv.RefTableRegistryVersion_lastUpdateDate desc
					limit 1
				", array(
					'ident' => $data['keyValue']
				));

				if (!empty($resp[0]['RefTableRegistryVersionFile_Path']) && file_exists($resp[0]['RefTableRegistryVersionFile_Path'])) {
					$link = $resp[0]['RefTableRegistryVersionFile_Path'];
				}
			}

			$fields[] = array('xtype' => 'hidden', 'name' => 'downloadLink', 'value' => $link);
		}

		return array(
			'data' => $fields,
			'totalCount' => count($fields),
			'Error_Msg' => ''
		);
	}

	/**
	 *	Возвращает параметры процедуры
	 */
	function getParamsByProcedure($data) {
	
		$query = "
			select 
				substring(field.name, 1, length(field.name)) as \"name\",
				t.typname AS \"type\",
				CASE
					WHEN field.mode_id = 'b' THEN 1
					ELSE 0
				END AS \"is_output\"
			from (
					select 
						unnest(p.proargnames) as name,
						unnest(p.proargtypes) as type_id,
						unnest(p.proargmodes) as mode_id
					from 
						pg_catalog.pg_proc p
						INNER JOIN pg_namespace n ON p.pronamespace = n.oid
					where 
						p.proname =lower(:proc) AND
						n.nspname =lower(:scheme)
				) field
				LEFT JOIN pg_type t ON t.oid = field.type_id       
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     *	Сохранение записи в справочник
     */
    function saveDirectoryRecord($data) {
        $proc = "p_" . $data['table'] . "_" . ( empty($data['fieldsData'][$data['keyName']]) ? "ins" : "upd" );

        // Найдем все параметры с которыми работает хранимка
        $procParams = $this->getParamsByProcedure(array('proc' => $proc, 'scheme' => $data['scheme']));
        if( !is_array($procParams) || count($procParams) == 0 ) {
            DieWithError("Хранимая процедура с именем {$data['scheme']}.{$proc} не существует!");
            return;
        }

        $query = "
            select
                {$data['keyName']} as \"{$data['keyName']}\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from {$data['scheme']}.{$proc}(
        ";

        array_walk($data['fieldsData'], 'ConvertFromUTF8ToWin1251');
        foreach($procParams as $key=>$row) {
            $query .=  ( $key > 0 ? "\t\t\t\t" : "\t\t" ) . $row['name']." := ";
            if( $row['name'] === $data['keyName'] ) {
                $query .= ':' . $data['keyName'];
            } else {
                $query .= ':' . $row['name'];
            }
            if(!isset($data['fieldsData'][$row['name']] )) {
                $data['fieldsData'][$row['name']] = null;
            }

            $query .= (count($procParams) > ++$key ? "," : ")") . "\n";
        }

        //$query .= "\t\t\tselect @Res as {$data['keyName']}, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
        $saveData = [];
        foreach ($data['fieldsData'] as $key=>$value) {
            $key = strtolower($key);
            $saveData[$key] = $value;

            if (is_array($value)) {
                if (isset($value['type']) && $value['type'] == 'date') {
					if (is_array($value['value']) && array_key_exists('value', $value['value'])) {
						$value['value'] = $value['value']['value'];
					}
                    if (!empty($value['value'])) {
                        $saveData[$key] = date('Y-m-d', strtotime($value['value']));
                    } else {
                        $saveData[$key] = null;
                    }
                }
            } else if (empty($value)) {
                $saveData[$key] = null;
            }
        }
        $saveData['pmuser_id'] = $this->getPromedUserId();

        $result = $this->db->query($query, $saveData);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 *	Удаление записи из справочника
	 */
	function deleteDirectoryRecord($data) {

		$data['table']= $this->checkForFed($data['table']);

		$proc = "p_" . $data['table'] . "_del";

		// Найдем все параметры с которыми работает хранимка
		$procParams = $this->getParamsByProcedure(array('proc' => $proc, 'scheme' => $data['scheme']));
		if( !is_array($procParams) || count($procParams) == 0 ) {
			DieWithError("Хранимая процедура с именем {$data['scheme']}.{$proc} не существует!");
			return;
		}

		$queryParams = [
			$data['keyName'] => $data['keyValue'],
			'pmUser_id' => $data['pmUser_id'],
			'error_code' => null,
			'error_message' => null,
		];

		// Формируем запрос
		$query = "select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			 from {$data['scheme']}.{$proc}(
		";

		foreach($procParams as $key=>$row) {
			$query .=  ( $key > 0 ? "\t\t\t\t" : "\t\t" ) . "".$row['name']." := ";
			$query .= ":" . $row['name'];
			$query .= (count($procParams) > ++$key ? "," : ")") . "\n";
		}

		//$query .= "\t\t\tselect @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		//var_dump($query); die();

		return $this->queryResult($query, $queryParams);
	}


	/**
	 *	Получение значящих полей заданного справочника
	 */
	function loadDirectoryFieldList($data) {
		$queryParams = array();
        //$directory_strict = array('LpuSectionProfile', 'MedSpecOms');
        $queryParams['Directory_Name'] = $data['Directory_Name'];
        $queryParams['Directory_Schema'] = $data['Directory_Schema'];
        //var_dump($data);

        if (empty($data['Group'])) {
            $query = "
                SELECT
                    column_name as \"field_name\",
                    column_name as \"cm\",
                    null as \"isPK\",
                    '' as \"file_field\"
                FROM
                    dbo.v_columns 

                WHERE
                    table_name iLIKE :Directory_Name

                    and schema_name = :Directory_Schema
                    and column_name not iLIKE '%pmUser%'

                    and column_name not iLIKE :Directory_Name || '_insDT%'

                    and column_name not iLIKE :Directory_Name || '_updDT%'

            ";

            $result = $this->db->query($query, $queryParams);
            if ( is_object($result) ) {
                return $result->result('array');
            } else {
                return false;
            }
        } else {
            $query = "
                SELECT
                    column_name as \"field_name\",
                    column_name as \"cm\",
                    null as \"isPK\",
                    '' as \"file_field\"
                FROM
                    dbo.v_columns 

                WHERE
                    (table_name iLIKE :Directory_Name or table_name iLIKE :Directory_Name || 'GROUP')

                    and schema_name in ('r66', :Directory_Schema)
                    and column_name not iLIKE '%pmUser%'

                    and column_name not iLIKE :Directory_Name || 'GROUP' || '_id'

                    and column_name not iLIKE :Directory_Name || 'GROUP' || '_insDT%'

                    and column_name not iLIKE :Directory_Name || '_insDT%'

                    and column_name not iLIKE :Directory_Name || 'GROUP' || '_updDT%'

                    and column_name not iLIKE :Directory_Name || '_updDT%'

            ";
	            $result = $this->db->query($query, $queryParams);
            if ( is_object($result) ) {
                $response = $result->result('array');
                //$response = array_map("unserialize", array_unique( array_map("serialize", $response) ));
                return $response;

            } else {
                return false;
            }
        }
	}


	/**
	 *	Обновление справочника на основании загруженного файла и параметров, указанных на форме.
	 */
	function saveDirectoryChanges($data) {
        $this->load->helper('Xml_helper');

        //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
        set_time_limit(0);
        $file_type = substr($data['LocalDirectory_ImportPath'], -3);

        // Читаем файл в массив
        $load_data = array();
        $data['preview_counter'] = 0;
        $data['preview_group_counter'] = 0;
        $data['preview_uslugaComplex_counter'] = 0;
        $data['preview_mesold_counter'] = 0;
        $data['preview_mesusluga_counter'] = 0;

        //Для режима просмотра - создаём массив для измененного справочника и счетчик
        $data['preview_directory'] = array();
        $data['preview_directory_group'] = array();
        $data['preview_directory_complexusluga'] = array();
        $data['preview_mesold_complexusluga'] = array();
        $data['preview_mesusluga_complexusluga'] = array();

        if ($file_type == 'dbf') {
            $h = dbase_open($data['LocalDirectory_ImportPath'], 0);
            if ( $h ) {
                $r = dbase_numrecords($h);
                for ( $i = 1; $i <= $r; $i++ ) {
                    $load_data[$i-1] = dbase_get_record_with_names($h, $i);
                    array_walk($load_data[$i-1], 'ConvertFromWin866ToCp1251');
                }
                dbase_close($h);
            }
        } else if ($file_type == 'xml') {
            $xml_file = simplexml_load_file($data['LocalDirectory_ImportPath']);
            $load_data = simpleXMLToArray($xml_file);
            $load_data = $load_data['REC'];
            array_walk_recursive($load_data, 'ConvertFromUTF8ToWin1251');
        }

        //Если обновляем особый справочник (dbo.LpuSectionProfile или dbo.MedSpecOMS) переносим поля с GROUP в отдельный массив
        $data['group_array'] = array();
        if (in_array($data['Directory_Name'], array('LpuSectionProfile', 'MedSpecOMS'))) {
            foreach ($data['LocalDirectory_ComboValues'] as $key_g => $value_g) {
                if (strpos($key_g, 'GROUP')) {
                    $data['group_array'][$key_g] = $value_g;
                    unset($data['LocalDirectory_ComboValues'][$key_g]);
                }
            }
        }

        //Тянем значения обновляемого справочника из БД
        $select = '';

        foreach ($data['LocalDirectory_ComboValues'] as $key => $value) {

            if (strpos($key, 'begDT') || strpos($key, 'endDT') || strpos($key, 'begDate') || strpos($key, 'endDate') || strpos($key, 'updDT') || strpos($key, 'updDate') || strpos($key, 'insDT') || strpos($key, 'insDate')) {
                $key = 'to_char('. $key.", 'DD.MM.YYYY') as \"" .$key."\"";

            }

            $select = $select . $key . ', ';
        }

        $select = substr($select,0,-2);

        $query = "
            select
                {$select}
            from
                {$data['Directory_Name']} 

        ";

        //echo getDebugSql($query, array());
        $result = $this->db->query($query);

        if ( is_object($result) ) {
            $DB_data = $result->result('array');
        } else {
            return false;
        }

        //Создаем массив с записями, соответствующими ПК
        $isPK = array();
        foreach ($data['LocalDirectory_isPK'] as $key => $value) {
            if ($value) {
                array_push($isPK, $key);
            }
        }

        $data['return_array']= array();
        $data['return_array']['error'] = array();
        $data['return_array']['error_group'] = array();

        //Проходим по данным, передаваемым в файле, сравниваем по ПК с данными из БД, если находим - обновляем данные в БД и удаляем обновленные данные из переданного массива и из данных, загруженных из БД
        foreach ($load_data as $load_key => $load_row) {
            //добавление или апдейт
            $add_new_record = true;

            //Счетчик совпадения по первичному ключу
            $flag = 0;

            //Счетчик прохождения по первичному ключу
            $counter = 0;
            $where = "1=1";

            //проходим по всем записям справочника и сравниваем по ПК значения импортируемых значений и значений в БД
            foreach ($DB_data as $db_key => $db_row) {
                foreach ($isPK as $isPK_key => $isPK_value) {
                    if ( isset($db_row[$isPK_value]) && isset($load_row[$data['LocalDirectory_ComboValues'][$isPK_value]])
                        && $db_row[$isPK_value] == $load_row[$data['LocalDirectory_ComboValues'][$isPK_value]] ) {
                        $flag += 1;
                        $where .= " and " . $isPK_value . " = " . $db_row[$isPK_value] . " ";
                    }
                    $counter += 1;
                }

                //Обновление. Если запись с таким ПК найдена, то обновляем её
                if ($flag == $counter && $add_new_record) {

                    //тащим запросом ИДшник для обновления
                    $query = "
                        select
                            " . $data['Directory_Name'] . "_id" . " as \"" . $data['Directory_Name'] . "_id\" 
                        from
                            {$data['Directory_Name']} 

                        where
                            {$where}
                    ";

                    //echo getDebugSql($query, array());
                    $result = $this->db->query($query);

                    if ( is_object($result) ) {
                        $response = $result->result('array');

                        //Если такая запись одна, апдейтим её
                        if (count($response) == 1) {
                            $data[$data['Directory_Name'] . "_id"] = $response[0][$data['Directory_Name'] . "_id"];
                            $data['directory_id'] = $response[0][$data['Directory_Name'] . "_id"];
                            $proc = 'p_' . $data['Directory_Name'] . '_upd';
                            $data['proc'] = 'upd';

                            // Формируем запрос для обновления
                            /*
                            $query = "
                                declare
                                    @ErrCode int,
                                    @ErrMessage varchar(4000),
                                    @" . $data['Directory_Name'] ."_id bigint;
                                exec {$proc}
                            ";
                            */
                            $query = "select " . $data['Directory_Name'] ."_id as \"" . $data['Directory_Name'] ."_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                            from {$proc}(";


                            $data['preview_directory'][$data['preview_counter']] = array();

                            foreach ($data['LocalDirectory_ComboValues'] as $key => $value) {
                                if ($data['mode'] == 'view' && !empty($value)) {
                                    $data['preview_directory'][$data['preview_counter']][$key] = $load_row[$value];
                                }

                                if (!empty($value)) {
                                    $data[$key] = $load_row[$value];
                                } else {
                                    $data[$key] = null;
                                }

                                $query .= $key . " := :" . $key . ", \r\n";
                            }

                            $data['preview_counter'] += 1;

                            $query .= "pmUser_id := :pmUser_id)";

                            if ($data['mode'] != 'view') {
                                //echo getDebugSQL($query, $data);die;
                                $result = $this->db->query($query, $data);
                                if ( is_object($result) ) {

                                    $response_upd = $result->result('array');
                                    if (!empty($response_upd[0]['Error_Msg'])) {
                                        return array('success' => false, 'Error_Msg' => $response_upd[0]['Error_Msg']);
                                    }

                                    //GROUP Если обновляем особый справочник (dbo.LpuSectionProfile или dbo.MedSpecOMS) обновляем/добавляем записи в зависимые таблицы
                                    if (in_array($data['Directory_Name'], array('LpuSectionProfile', 'MedSpecOMS'))) {
                                        $data = $this->saveDirectoryGroupChanges($data, $load_row);

                                        if (!$data) {
                                            return array('success' => false, 'Error_Msg' => 'Ошибка при добавлении записи в связанную таблицу GROUP');
                                        }
                                    }

                                    //Если обновляем справочник услуг - UslugaComplex, то в импортируемом файле должны быть следующие поля: DIVISION, AGECAT, SEX, SPEC, PROF, BASEBUDG
                                    if ($data['Directory_Name'] === 'UslugaComplex') {
                                        $uslComplexChange = $this->saveDirectoryUslugaComplexChanges($data, $load_row);
                                        if (!$uslComplexChange) {
                                            return array('success' => false, 'Error_Msg' => 'Ошибка при попытке добавления в связанные таблицы для комплексной услуги');
                                        } else if (!empty($uslComplexChange['Error_Msg'])) {
                                            return array('success' => false, 'Error_Msg' => $uslComplexChange['Error_Msg']);
                                        } else if (!empty($uslComplexChange['Info_Message'])) {
                                            $data['return_array']['Info_Message'] = toUtf($uslComplexChange['Info_Message']);
                                        }
                                    }

                                } else {
                                    return array('success' => false, 'Error_Msg' => 'Не удалось обновить запись. Результат не является объектом.');
                                }
                            }

                            unset($DB_data[$db_key]);
                            unset($load_data[$load_key]);

                            $add_new_record = false;
                        }


                        //Если записей с таким ПК найдено несколько, то добавляем ошибку в массив ошибок
                        else {
                            return array('Error_Msg' => 'Найдено несколько записей связанных с переданным идентификатором');
                        }
                    } else {
                        return array('Error_Msg' => 'Не удалось получить идентификатор для обновления. Результат не является объектом.');
                    }
                }

                $flag = 0;
                $counter = 0;
            }
        }

        //Идём по недобавленным записям из БД и добавляем их в лист предварительного просмотра
        if ($data['mode'] == 'view') {
            foreach ($DB_data as $db_row) {
                foreach ($db_row as $db_key => $db_value) {
                    $data['preview_directory'][$data['preview_counter']][$db_key] = $db_value;
                }
                $data['preview_counter'] += 1;
            }
        }

        //Проходим по оставшимся записям из переданного файла и добавляем их в справочник/предварительный просмотр
        foreach ($load_data as $load_key => $load_row) {

            $run_query = 0;
            $proc = 'p_' . $data['Directory_Name'] . '_ins';
            $data['proc'] = 'ins';
            $data[$data['Directory_Name'] . "_id"] = null;

            $query = "
                select " . $data['Directory_Name'] ."_id as \"" . $data['Directory_Name'] ."_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                from {$proc}(
            ";

            foreach ($data['LocalDirectory_ComboValues'] as $key => $value) {
                if ($data['mode'] == 'view') {
                    if (!empty($load_row[$value])) {
                        $data['preview_directory'][$data['preview_counter']][$key] = $load_row[$value];
                    }
                }

                if ($key != $data['Directory_Name'] ."_id") {
                    $query .= "" . $key . " := :" . $key . ", \r\n";

                    if (isset($load_row[$value])) {
                        $data[$key] = $load_row[$value];
                    } else {
                        $data[$key] = null;
                        $run_query += 1;
                    }
                }
            }

            $data['preview_counter'] += 1;

            $query .= "
                pmUser_id := :pmUser_id);
           ";

            if ($run_query != count($data['LocalDirectory_ComboValues'])) {
                if ($data['mode'] != 'view') {

                    //echo getDebugSQL($query, $data);die;
                    $result = $this->db->query($query, $data);
                    if (is_object($result)) {
                        $response = $result->result('array');
                        $data['directory_id'] = $response[0][$data['Directory_Name'] . "_id"];

                        //GROUP Если добавляем запись в особый справочник (dbo.LpuSectionProfile или dbo.MedSpecOMS) добавляем записи в зависимые таблицы
                        if (in_array($data['Directory_Name'], array('LpuSectionProfile', 'MedSpecOMS'))) {
                            $data = $this->saveDirectoryGroupChanges($data, $load_row);

                            if (!$data) {
                                return array('Error_Msg' => 'ошибка при добавлении записи в связанную таблицу GROUP');
                            }
                        }

                        //Если обновляем справочник услуг (UslugaComplex)
                        if ($data['Directory_Name'] === 'UslugaComplex' ) {
                            $uslComplexChange = $this->saveDirectoryUslugaComplexChanges($data, $load_row);
                            if (!$uslComplexChange) {
                                return array('success' => false, 'Error_Msg' => 'Ошибка при попытке добавления в связанные таблицы для комплексной услуги');
                            } else if (!empty($uslComplexChange['Error_Msg'])) {
                                return array('success' => false, 'Error_Msg' => $uslComplexChange['Error_Msg']);
                            } else if (!empty($uslComplexChange['Info_Message'])) {
                                $data['return_array']['Info_Message'] = toUtf($uslComplexChange['Info_Message']);
                            }

                        }

                    } else {
                        array_push($data['return_array']['error'], 'Не удалось добавить запись ' . $load_row . '');
                    }
                }
            } else {
                array_push($data['return_array']['error'], 'Не удалось добавить запись - попытка добавить пустую строку');
            }
        }

        if ($data['mode'] == 'view') {

            //Удаляем дубли записей если есть
            $data['preview_directory'] = array_map("unserialize", array_unique( array_map("serialize", $data['preview_directory'])));
            array_walk_recursive($data['preview_directory'], 'ConvertFromWin1251ToUTF8');
            $preview_directory_return = array();

            $array_keys = array_keys($data['preview_directory']);
            for ($i = $data['start']; $i < $data['start'] + $data['limit']; $i++) {
                if (isset($array_keys[$i]) && !empty($data['preview_directory'][$array_keys[$i]])) {
                    array_push($preview_directory_return, $data['preview_directory'][$array_keys[$i]]);
                }
            }

            return array('success' => 'true', 'data' => $preview_directory_return, 'totalCount' => count($data['preview_directory']));
        } else {
            unlink($data['LocalDirectory_ImportPath']);
            return $data['return_array'];
        }
	}


	/**
	 *	Добавление/обновление связанной таблицы формата Directory_Name + GROUP
     *  На выходе $data
	 */
	function saveDirectoryGroupChanges($data, $load_row) {

        //счетчик пустых полей в записи
        $run_query = 0;

        if (empty($data) || empty ($load_row)) {
            return false;
        }

        if ($data['proc'] == 'upd') {

            //Тянем запись из справочника по ИДшнику, который только что добавили/обновили
            $query = "
                select
                    ". $data['Directory_Name'] ."GROUP_id as \"". $data['Directory_Name'] ."GROUP_id\"
                from
                    r66.". $data['Directory_Name'] ."GROUP
                where
                    ". $data['Directory_Name'] ."_id = :directory_id
            ";

            //echo getDebugSQL($query, $data); die;
            $result = $this->db->query($query, $data);
            if ( is_object($result) ) {
                $response = $result->result('array');
                //var_dump($response);die;

                if (count($response) == 0) {
                    $proc = 'r66.p_' . $data['Directory_Name'] . 'GROUP_ins';
                } else if (count($response) == 1) {
                    $proc = 'r66.p_' . $data['Directory_Name'] . 'GROUP_upd';
                    $data['directory_group_id'] = $response[0][$data['Directory_Name'] .'GROUP_id'];
                } else {
                    array_push($data['return_array']['error_group'], 'В зависимой таблице найдено несколько записей с ' . $data['Directory_Name'] . '_id = '. $data['directory_id'] . '');
                }
            } else {
                return false;
            }
        } else {
            $proc = 'r66.p_' . $data['Directory_Name'] . 'GROUP_ins';
        }

        // Формируем запрос для добавления/обновления
        $query = "

            select " . $data['Directory_Name'] ."GROUP_id as \"" . $data['Directory_Name'] ."_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            from {$proc}(
        ";
        //var_dump($data['LocalDirectory_ComboValues']);
        $data['preview_directory_group'][$data['preview_group_counter']] = array();

        foreach ($data['group_array'] as $key => $value) {
            if ($data['mode'] == 'view' && !empty($value)) {
                $data['preview_directory_group'][$data['preview_group_counter']][$key] = $load_row[$value];
            }

            if ($key == $data['Directory_Name'] .'GROUP_id' && !empty($data['directory_group_id'])) {
                $query .= $key . " := :directory_group_id, \r\n";
            } else {
                $query .= $key . " := :" . $key . ", \r\n";
            }

            if (empty($data[$key])) {
                $data[$key] = null;
            }

            if (!empty($value)) {
                $data[$key] = $load_row[$value];
            }

            if (empty($load_row[$value])) {
                $run_query += 1;
            }
        }

        $query .= $data['Directory_Name'] . "_id := :" . $data['Directory_Name'] . "_id , \r\n";

        $data['preview_group_counter'] += 1;

        $query .= "pmUser_id := :pmUser_id);";

        //echo getDebugSQL($query, $data); die;
        if ($data['mode'] != 'view') {
            if ($run_query != count($data['group_array'])) {
                //echo getDebugSQL($query, $data);die;
                $result = $this->db->query($query, $data);

                if (is_object($result)) {
                    return $data; //$result->result('array');
                } else {
                    return false;
                }
            } else {
                array_push($data['return_array']['error_group'], 'Попытка добавить пустую запись');
            }
        }
        return $data;
    }

	/**
	 *	Добавление/обновление связанной таблицы формата Directory_Name + GROUP
     *  На выходе $data
	 */
	function saveDirectoryUslugaComplexChanges($data, $load_row) {

        //счетчик пустых полей в записи
        $run_query = false;

        $queryParamsComplex = array(
            'UslugaComplexPartitionLink_id' => 0,
            'pmUser_id' => $data['pmUser_id'],
            'UslugaComplex_id' => $data['directory_id']
        );

        $proc = 'r66.p_UslugaComplexPartitionLink_ins';

        if (empty($data) || empty ($load_row)) {
            return false;
        }

        if ($data['proc'] == 'upd') {

            //Тянем запись из справочника по ИДшнику, который только что добавили/обновили
            $query = "
                select
                    UslugaComplexPartitionLink_id as \"UslugaComplexPartitionLink_id\"
                from
                    r66.UslugacomplexPartitionLink
                where
                    UslugaComplex_id = :directory_id
            ";

            //echo getDebugSQL($query, $data);
            $result = $this->db->query($query, $data);
            if ( is_object($result) ) {
                $response = $result->result('array');

                //var_dump($response);die;
                if (count($response) == 0) {
                    $proc = 'r66.p_UslugaComplexPartitionLink_ins';
                } else if (count($response) == 1) {
                    $proc = 'r66.p_UslugaComplexPartitionLink_upd';
                    $queryParamsComplex['UslugaComplexPartitionLink_id'] = $response[0]['UslugaComplexPartitionLink_id'];
                } else {

                    return array('success' => false, 'Error_Msg' => 'В таблице UslugacomplexPartitionLink найдено несколько записей с UslugaComplex_id ='. $data['directory_id'] . '');
                }
            } else {
                return false;
            }
        }

        //Тянем значения для записи из справочников
        if (!empty($load_row['DIVISION'])) {
            $query = "
                select
                    UslugaComplexPartition_id as \"UslugaComplexPartition_id\"
                from
                    r66.UslugaComplexPartition
                where
                    UslugacomplexPartition_Code = :DIVISION
            ";

            $result = $this->db->query($query, array('DIVISION' => $load_row['DIVISION']));

            if ( is_object($result) ) {

                $response = $result->result('array');

                if (count($response) == 1) {
                    $queryParamsComplex['UslugaComplexPartition_id'] = $response[0]['UslugaComplexPartition_id'];
                } else {
                    $queryParamsComplex['UslugaComplexPartition_id'] = null;
                }
            }

        } else {
            $queryParamsComplex['UslugaComplexPartition_id'] = null;
        }

        if (!empty($load_row['AGECAT']) && in_array($load_row['AGECAT'], array('1', '2'))) {
            $queryParamsComplex['PersonAgeGroup_id'] = $load_row['AGECAT'];
        } else {
            $queryParamsComplex['PersonAgeGroup_id'] = null;
        }

        if (isset($load_row['SEX']) && in_array($load_row['SEX'], array('1', '2', '0'))) {
            if ($load_row['SEX'] == '0') {
                $queryParamsComplex['Sex_id'] = 3;
            } else {
                $queryParamsComplex['Sex_id'] = $load_row['SEX'];
            }
        } else {
            $queryParamsComplex['Sex_id'] = null;
        }

        if (isset($load_row['BASEBUDG']) && in_array($load_row['BASEBUDG'], array('1', '0'))) {
            if ($load_row['BASEBUDG'] == '0') {
                $queryParamsComplex['PayType_id'] = 110;
            } else if ($load_row['BASEBUDG'] == '0') {
                $queryParamsComplex['PayType_id'] = 112;
            }
        } else {
            $queryParamsComplex['PayType_id'] = null;
        }

        if (!empty($load_row['SPEC'])) {

            $query = "
                select
                    MedSpecOMS_id as \"MedSpecOMS_id\"
                from
                    dbo.MedSpecOMS
                where
                    MedSpecOMS_Code = :SPEC
                    and region_id = 66
            ";

            $result = $this->db->query($query, array('SPEC' => $load_row['SPEC']));

            if ( is_object($result) ) {

                $response = $result->result('array');

                if (count($response) == 1) {
                    $queryParamsComplex['MedSpecOMS_id'] = $response[0]['MedSpecOMS_id'];
                } else {
                    $queryParamsComplex['MedSpecOMS_id'] = null;
                }
            }

        } else {
            $queryParamsComplex['MedSpecOMS_id'] = null;
        }

        if (!empty($load_row['PROF'])) {

            $query = "
                select
                    LpuSectionProfile_id as \"LpuSectionProfile_id\"
                from
                    dbo.LpuSectionProfile
                where
                    LpuSectionProfile_Code = :PROF
                    and region_id = 66
            ";

            $result = $this->db->query($query, array('PROF' => $load_row['PROF']));

            if ( is_object($result) ) {

                $response = $result->result('array');

                if (count($response) == 1) {
                    $queryParamsComplex['LpuSectionProfile_id'] = $response[0]['LpuSectionProfile_id'];
                } else {
                    $queryParamsComplex['LpuSectionProfile_id'] = null;
                }
            }

        } else {
            $queryParamsComplex['LpuSectionProfile_id'] = null;
        }

        // Формируем запрос для добавления/обновления
        $query = "
            
            select UslugaComplexPartitionLink_id as \"UslugaComplexPartitionLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            from {$proc}(
        ";

        //var_dump($data['LocalDirectory_ComboValues']);
        $data['preview_directory_group'][$data['preview_uslugaComplex_counter']] = array();

        foreach ($queryParamsComplex as $key => $value) {
            if ($data['mode'] == 'view' && !empty($value)) {
                $data['preview_directory_complexusluga'][$data['preview_uslugaComplex_counter']][$key] = $load_row[$value];
            }

            $query .= $key . " := :" . $key . ", \r\n";

            if (!empty($value)) {
                $run_query = true;
            }
        }

        $data['preview_uslugaComplex_counter'] += 1;

        $query .= ");
        ";

        if ($run_query) {
            $run_query = false;

            //var_dump('save UslugaComplexPartitionLink');
            //echo getDebugSQL($query, $queryParamsComplex);
            $result = $this->db->query($query, $queryParamsComplex);

            if (is_object($result)) {

                //Если Division == 101 или 201 то апдейтим MesOld и MesUsluga
                if ( in_array($load_row['DIVISION'], array('101', '201'))) {

                    $mesOldParams = array();

                    //Ищим в MesOld запись с кодом  UslugaComplex_Code
                    $query = "
                        select 
                            MO.Mes_id as \"Mes_id\",
                            UC.UslugaComplex_Code as \"UslugaComplex_Code\"
                        from
                            UslugaComplex UC
                            left join MesOld MO  on UC.UslugaComplex_Code = MO.Mes_Code

                        where
	                        UC.UslugaComplex_id = :directory_id
                        limit 1
                    ";

                    $result = $this->db->query($query, $data);
                    if ( is_object($result) ) {
                        $response = $result->result('array');

                        if (count($response) == 0 || empty($response[0]['Mes_id'])) {
                            return array('Info_Message' => 'Для добавляемой услуги требуется создание стандарта медицинской помощи (МЭС). Обратитесь к администратору.');
                        } else if (count($response) == 1 && !empty($response[0]['Mes_id'])) {
                            $mesOldParams['Mes_id'] = $response[0]['Mes_id'];
                            $mesOldParams['Mes_Code'] = !empty($response[0]['UslugaComplex_Code'])?$response[0]['UslugaComplex_Code']:null;
                            $mesOldParams['pmUser_id'] = $data['pmUser_id'];

                            //Формируем запрос для добавления/апдейта записи в MesOld
                            $query = "
                                select Mes_id as \"Mes_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                                from p_MesOld_upd(
                            ";

                            foreach ($mesOldParams as $key => $value) {

                                $query .= $key . " := :" . $key . ", \r\n";

                                if (!empty($value)) {
                                    $run_query = true;
                                }
                            }

                            $query .= ");
                            ";

                            if ($run_query) {
                                $run_query = false;

                                //Добавляем/апдейтим запись в MesOld
                                $result = $this->db->query($query, $mesOldParams);

                                if ( is_object($result) ) {

                                    $response = $result->result('array');
                                    if (is_array($response) && count($response) > 0 && empty($response[0]['Error_Msg']) /*&& !empty($response[0]['Mes_id'])*/) {

                                        $mesUslugaParams = array();
                                        $mesUslugaParams['Mes_id'] = $mesOldParams['Mes_id'];

                                        //Аналогично апдейтим MesUsluga
                                        //Ищим в MesUsluga запись с только что обновленным Mes_id
                                        $query = "
                                            select 
                                                MesUsluga_id as \"MesUsluga_id\"
                                            from
                                                MesUsluga
                                            where
                                                Mes_id = :Mes_id
                                            limit 1
                                        ";

                                        $result = $this->db->query($query, $mesUslugaParams);
                                        if ( is_object($result) ) {
                                            $response = $result->result('array');

                                            if (count($response) == 0 || empty($response[0]['MesUsluga_id'])) {
                                                return array('Info_Message' => 'Для добавляемой услуги требуется создание стандарта медицинской помощи (МЭС). Обратитесь к администратору.');
                                            } else if (count($response) == 1 && !empty($response[0]['MesUsluga_id'])) {
                                                $proc = 'p_MesUsluga_upd';
                                                $mesUslugaParams['MesUsluga_id'] = $response[0]['MesUsluga_id'];
                                                $mesUslugaParams['UslugaComplex_id'] = $data['directory_id'];
                                                $mesUslugaParams['pmUser_id'] = $data['pmUser_id'];

                                                //Формируем запрос для добавления/апдейта записи в MesOld
                                                $query = "
                                                    select MesUsluga_id as \"MesUsluga_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                                                    from {$proc}(
                                                ";

                                                foreach ($mesUslugaParams as $key => $value) {
                                                    $query .= $key . " := :" . $key . ", \r\n";

                                                    if (!empty($value)) {
                                                        $run_query = true;
                                                    }
                                                }

                                                //$data['preview_mesusluga_counter'] += 1;

                                                $query .= ");
                                                ";

                                                if ($run_query) {
                                                    $result = $this->db->query($query, $mesUslugaParams);

                                                    if ( is_object($result) ) {
                                                        $response = $result->result('array');

                                                        if (is_array($response) && empty($response[0]['Error_Msg'])) {
                                                            return true;
                                                        } else if (is_array($response) && !empty($response[0]['Error_Msg'])) {
                                                            return array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']);
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            return false;
                                        }

                                    } else {
                                        return array('success' => false, 'Error_Msg' => 'Ошибка при попытке добавления MesOld' . $response[0]['Error_Msg']);
                                    }
                                } else {
                                    return array('success' => false, 'Error_Msg' => 'Ошибка при обновлении записи в MesOld');
                                }
                            }
                        }
                    } else {
                        return array('success' => false, 'Error_Msg' => 'Ошибка при загрузке Mes_id');
                    }
                }
            } else {
                return array('success' => false, 'Error_Msg' => 'Ошибка при редактировании запиис в UslugaComplexPartitionLink');
            }
        } else {
             return array('success' => false, 'Error_Msg' => 'Попытка добавить пустую запись при обновлении услуги');
        }
        return true;
    }


	/**
	 * Наполнение из указанной таблицы
	 *
	 * @param string $collection Соответствует имени таблицы в БД
	 * @return output
	 */
	public function importCommonTable( $collection, $fields=array(), $params=array() ) {
		$page = 1;
		$offset = 100;
		$id = $collection."_id";

		$where1 = isset( $params['where'] ) ? " WHERE ".$params['where'] : "";
		$where2 = isset( $params['where'] ) ? " AND ".$params['where'] : "";

		do {
			$end = $page * $offset;
			$start = $end - $offset + 1;
			$sql = "
				WITH Table_CTE as (
					SELECT
						t1.*,
						ROW_NUMBER() OVER (ORDER BY ".$id.") as 'RowNum'
					FROM ".$collection." t1
					".$where1."
				)
				SELECT t1.* FROM Table_CTE as t1 WHERE t1.RowNum BETWEEN ".$start." AND ".$end." ".$where2."
			";
			$query = $this->db->query( $sql );
			$result = $query->result_array();
			if ( sizeof( $result ) ) {
				foreach( $result as &$v ) {
					// Приведение типов
					if ( sizeof( $fields ) ) {
						foreach( $fields as $field => $type ) {
							if ( array_key_exists( $field, $v ) ) {
								switch( $type ){
									case 'int':
									case 'integer':
										$v[ $field ] = intval( $v[ $field ] );
									break;
								}
							}
						}
					}

					$v['_id'] = $v[ $id ];
					$exists = $this->mongo_db->select(array('_id'))->get_where( $collection, array( '_id' => $v['_id'] ) );
					// Запись существует?
					if ( sizeof( $exists ) ) {
						$this->mongo_db->where( array( '_id' => 1 ) )->update( $collection, $v );
					} else {
						$this->mongo_db->insert( $collection, $v );
					}
				}
				echo "Page ".$page." end.<br />";
				$page++;
			} else {
				echo "Page ".$page.". Nothing to import";
			}
		} while( sizeof( $result ) );
	}


	/**
	 * Наполнение из таблицы неформализованных адресов
	 */
	public function importTableUnformalizedAddressDirectory(){

		$collection = 'UnformalizedAddressDirectory';

		$sql = "
			SELECT
                  unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                  unformalizedaddressdirectory_dom as \"unformalizedaddressdirectory_dom\",
                  unformalizedaddressdirectory_name as \"unformalizedaddressdirectory_name\",
                  unformalizedaddressdirectory_lat as \"unformalizedaddressdirectory_lat\",
                  unformalizedaddressdirectory_lng as \"unformalizedaddressdirectory_lng\",
                  klrgn_id as \"klrgn_id\",
                  klsubrgn_id as \"klsubrgn_id\",
                  klcity_id as \"klcity_id\",
                  kltown_id as \"kltown_id\",
                  klstreet_id as \"klstreet_id\",
                  pmuser_insid as \"pmuser_insid\",
                  pmuser_updid as \"pmuser_updid\",
                  unformalizedaddressdirectory_insdt as \"unformalizedaddressdirectory_insdt\",
                  unformalizedaddressdirectory_upddt as \"unformalizedaddressdirectory_upddt\",
                  lpu_id as \"lpu_id\",
                  unformalizedaddresstype_id as \"unformalizedaddresstype_id\",
                  lpubuilding_id as \"lpubuilding_id\",
                  unformalizedaddressdirectory_guid as \"unformalizedaddressdirectory_guid\",
                  lpu_aid as \"lpu_aid\",
                  unformalizedaddressdirectory_corpus as \"unformalizedaddressdirectory_corpus\"
			FROM
				UnformalizedAddressDirectory
			ORDER BY
				UnformalizedAddressDirectory_id
		";
		$query = $this->db->query( $sql );
		$result = $query->result_array();
		if ( sizeof( $result ) ) {
			foreach( $result as &$v ) {
				$v['_id'] = $v['UnformalizedAddressDirectory_id'];
				$exists = $this->mongo_db->select(array('_id'))->get_where( $collection, array( '_id' => $v['_id'] ) );

				// Запись существует?
				if ( sizeof( $exists ) ) {
					$this->mongo_db->where( array( '_id' => 1 ) )->update( $collection, $v );
				} else {
					$this->mongo_db->insert( $collection, $v );
				}
			}
			echo "Done";
		} else {
			echo "Nothing to import";
		}
	}


	/**
	 * Наполнение из таблицы типов неформализованных адресов
	 */
	public function importTableUnformalizedAddressType(){

		$collection = 'UnformalizedAddressType';

		$sql = "
			SELECT
              unformalizedaddresstype_id as \"unformalizedaddresstype_id\",
              unformalizedaddresstype_name as \"unformalizedaddresstype_name\",
              unformalizedaddresstype_socrnick as \"unformalizedaddresstype_socrnick\",
              pmuser_insid as \"pmuser_insid\",
              pmuser_updid as \"pmuser_updid\",
              unformalizedaddresstype_insdt as \"unformalizedaddresstype_insdt\",
              unformalizedaddresstype_upddt as \"unformalizedaddresstype_upddt\"
			FROM
				UnformalizedAddressType
			ORDER BY
				UnformalizedAddressType_id
		";
		$query = $this->db->query( $sql );
		$result = $query->result_array();
		if ( sizeof( $result ) ) {
			foreach( $result as &$v ) {
				$v['_id'] = $v['UnformalizedAddressType_id'];
				$exists = $this->mongo_db->select(array('_id'))->get_where( $collection, array( '_id' => $v['_id'] ) );

				// Запись существует?
				if ( sizeof( $exists ) ) {
					$this->mongo_db->where( array( '_id' => 1 ) )->update( $collection, $v );
				} else {
					$this->mongo_db->insert( $collection, $v );
				}
			}
			echo "Done";
		} else {
			echo "Nothing to import";
		}
	}

	//убераем Fed для локальных справочников, у которых есть он в конце
	public function checkForFed($table){

		$ignoreName = array(
			'DrugFed',
			'DrugRequestRegionFed',
			'TerrFed',
			'v_DrugFed',
			'PrivilegeTypeFed',
			'v_PrivilegeTypeFed'
		);

		if (!in_array($table, $ignoreName)) {
			$findme = 'Fed';
			if (substr($table, strlen($table) - strlen($findme)) == $findme) {
				$table=substr($table,0,-3);
			}
		}

		return $table;
	}
}