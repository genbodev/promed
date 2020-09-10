<?php

/**
 * Класс для реализации функций работы с MongoDB-хранилищем
 * @property Resource mongo_db
 * @property Resource CI
 */
class SwMongoExt
{
    var $tableInc = 'sysGen'; # Таблица для хранения счетчиков
	private $mongo_db = NULL;
	private $CI = null;
    
	/**
	 * Конструктор
	 * Класс использует библиотеку MongoDb
	 */
    function __construct() {
		switch (checkMongoDb()) {
			case 'mongo':
				$this->CI = get_instance();
				$this->CI->load->library('swMongodb', array('config_file'=>'mongodbcache', 'fail_gracefully'=>true), 'swmongodb');
				$this->mongo_db = $this->CI->swmongodb;
				break;
			case 'mongodb':
				$this->CI = get_instance();
				$this->CI->load->library('swMongodbPHP7', array('config_file'=>'mongodbcache', 'fail_gracefully'=>true), 'swmongodb');
				$this->mongo_db = $this->CI->swmongodb;
				break;
			default:
				// Возвращаем ошибку что работа с МонгоДБ невозможна
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
	 * Аналог инкрементера pmGen в MongoDB, возвращает значение увеличенной на 1, взависимости от параметров.
	 * @param string $object наименование объекта, для которого считаем
	 * @param string $use признак создания нового счетчика для нового дня (при use='day')
	 * @param $params (Lpu_id, MedService_id)
	 * @param int $min минимально возможное значение, по умолчанию 1
	 * @param int $max максимально возможное значение, по умолчанию 0 (не ограничено)
	 * @return int
	 *
	 * Пример использования:
	 *   // Генерация кода для объекта Samples определенной службы МО в рамках одного дня
	 *   $code = $this->swmongoext->generateCode('Samples','day',array('Lpu_id'=>1, 'MedService_id'=>25));
	 *   // Генерация кода для объекта EvnPS для определенной МО
	 *   $code = $this->swmongoext->generateCode('EvnPS','',array('Lpu_id'=>1, 'MedService_id'=>null));
    */
	function generateCode($object='', $use='', $params = array(), $min=1, $max=0) {
		$withoutSave = false;
		if (!empty($params['withoutSave'])) {
			$withoutSave = $params['withoutSave'];
		}
		// todo: Нужно будет убрать прямое указание параметров (переделать на неявное)
		// читаем строку с текущей датой, если нет данных, то берем единицу и апдейтим запись
		if (!is_array($params) && (count($params)==0)) {
			$params['Lpu_id'] = null;
			$params['MedService_id'] = null;
		}
		// для фильтрации
		$wheres = array(
			'Lpu_id'=>isset($params['Lpu_id'])?$params['Lpu_id']:null,
			'MedService_id'=>isset($params['MedService_id'])?$params['MedService_id']:null,
			'object'=>$object
		);
		// для сохранения (сохранение выполняется с теми же параметрами что и фильтрация)
		$setdate = date("Y-m-d"); // todo: можно сделать сохранение именно в дату и сравнивать именно даты
		$params = $wheres;
		$params['date'] = $setdate;

		$row = $this->mongo_db->where($wheres)->get($this->tableInc);

		$params['value'] = $min;
		if (is_array($row)) {
			if ((count($row)>0) && is_array($row[0])) { // Если значение счетчика уже сохранено
				// При добавлении условий использовать switch, может не такой эпатажный, но все же. Вместо нижнего условия.
				switch ($use) {
					case 'day':
						if ($row[0]['date'] == $setdate) {  // Если на новый день счетчик запускается с нуля и даты одинаковым
							$params['value'] = $row[0]['value']+1;
						}
						break;
					case 'year':
						if (substr($row[0]['date'],0, 4) == substr($setdate,0, 4)) { // Если на новый год счетчик запускается с нуля и года одинаковым
							$params['value'] = $row[0]['value']+1;
						}
						break;
					default:
						$params['value'] = $row[0]['value']+1;
						break;
				}
				/*if (($use == 'day' && $row[0]['date'] == $setdate) || ($use != 'day')) { // Если на новый день счетчик запускается с нуля и даты одинаковым, или не учитывая новый день
					$params['value'] = $row[0]['value']+1;
				}*/

				// если значение счётчика меньше минимально требуемого, то присваиваем минимальное.
				if ($params['value'] < $min) {
					$params['value'] = $min;
				}

				if ($max!=0 && $params['value']>$max) { // Превышение максимально возможного значения
					// todo: выдать сообщение об ошибке или обнулить счетчик и начать по новой
				}
				// Пересохраняем значение
				if (!$withoutSave) {
					$res = $this->mongo_db->where(array('_id'=>$row[0]['_id']))->update($this->tableInc, $params);
				}
			} else {
				// Сохраняем значение впервые
				if (!$withoutSave) {
					$res = $this->mongo_db->insert($this->tableInc, $params);
				}
			}
		}
		return $params['value'];
	}

	/**
	 * Получает последнее сохраненное значение по определенному объекту, если ничего не сохранено, возвращает Null
	 * @param string $object наименование объекта, для которого считаем
	 * @param $params параметры инкементера
	 * @return int
	 *
	 */
	function getCode($object='', $params = array()) {
		if (!is_array($params) && (count($params)==0)) {
			$params['Lpu_id'] = null;
			$params['MedService_id'] = null;
		}
		// для фильтрации
		$wheres = array(
			'Lpu_id'=>isset($params['Lpu_id'])?$params['Lpu_id']:null,
			'MedService_id'=>isset($params['MedService_id'])?$params['MedService_id']:null,
			'object'=>$object
		);
		// для сохранения (сохранение выполняется с теми же параметрами что и фильтрация)
		$row = $this->mongo_db->where($wheres)->get($this->tableInc);
		$result = null;
		if (is_array($row)) {
			if ((count($row)>0) && is_array($row[0])) { // Если значение счетчика уже сохранено
				// При добавлении условий использовать switch, может не такой эпатажный, но все же. Вместо нижнего условия.
				$result = $row[0]['value'];
			}
		}
		return $result;
	}
}

?>