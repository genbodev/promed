<?php
/**
 * Class RecordUnion
 *
 * @property Utils_model $model
 */
class RecordUnion {
	private $model = null;

	private $origRecords = array();
	private $procRecords = array();
	private $newRecords = array();

	private $currIndex = 0;
	private $condition = null;

	private $mainParams = array();
	private $mainLinks = array();

	/**
	 * Конструктор
	 */
	function __construct(&$model, $records, $mainParams = array(), $mainLinks = array(), $condition = null) {
		$CI = &get_instance();
		$this->model = $model;
		$this->procRecords = $this->origRecords = $records;
		$this->mainParams = $mainParams;
		$this->mainLinks = $mainLinks;
		$this->condition = $condition;
	}

	/**
	 * Сохранение основных параметров обрабатываемых записей
	 * @param array $mainParams
	 */
	function setMainParams($mainParams) {
		$this->mainParams = $mainParams;
	}

	/**
	 * Получение записи из списка обрабатываемых записей
	 */
	function getRecord($index = null) {
		if ($index === null) {$index = $this->currIndex;}
		return (isset($index) && $index >= 0 && isset($this->procRecords[$index]))?$this->procRecords[$index]:null;
	}

	/**
	 * Получение записи из списка изначальных записей
	 */
	function getOrigRecord($index = null) {
		if ($index === null) {$index = $this->currIndex;}
		return (isset($index) && $index >= 0 && isset($this->origRecords[$index]))?$this->origRecords[$index]:null;
	}

	/**
	 * Получение записи из списка новых записей
	 */
	function getNewRecord($index) {
		return (isset($this->newRecords[$index]))?$this->newRecords[$index]:null;
	}

	/**
	 * Получение списка всех обрабатывемых и новых записей
	 */
	function getAllRecords($sortByStatus = false) {
		$records = array_merge($this->procRecords, $this->newRecords);
		if ($sortByStatus) {
			$statusList = array();
			foreach($records as $index => $record) {
				$statusList[$index] = $record['RecordStatus_Code'];
			}
			array_multisort($statusList, SORT_ASC, $records);
		}
		return $records;
	}

	/**
	 * Получение списка новых записей
	 */
	function getNewRecords() {
		return $this->newRecords;
	}

	/**
	 * Получение списка изначальных записей
	 */
	function getOrigRecords() {
		return $this->origRecords;
	}

	/**
	 * Изменения записи в списке обрабатываемых записей
	 */
	function setRecord($index, $record) {
		$this->procRecords[$index] = $record;
	}

	/**
	 * Изменения записи в списке новых записей
	 */
	function setNewRecord($index, $record) {
		$this->newRecords[$index] = $record;
	}

	/**
	 * Провека условия
	 */
	function checkCondition($checkIndex, $currIndex = null) {
		$currIndex = $this->getIndex($currIndex);

		$currRecord = $this->getRecord($currIndex);
		$checkRecord = $this->getRecord($checkIndex);

		return (!$this->condition || (
			$currRecord && $checkRecord && call_user_func($this->condition, $currRecord, $checkRecord)
		));
	}

	/**
	 * Установка текущего индекса
	 */
	function setCurrIndex($index) {
		$this->currIndex = $index;
	}

	/**
	 * Получение индекса из переданной переменной или текщего сохраненного индекса
	 */
	function getIndex($index = null) {
		return (isset($index) && $index >= 0)?$index:$this->currIndex;
	}

	/**
	 * Получение предыдущей записи, подходящей по условию
	 */
	function getPrevIndex($index = null) {
		$currIndex = $this->getIndex($index);
		for($prevIndex = $currIndex-1; $prev = $this->getRecord($prevIndex); $prevIndex--) {
			if ($prev['RecordStatus_Code'] != 3 && $this->checkCondition($prevIndex, $currIndex)) {
				return $prevIndex;
			}
		}
		return -1;
	}

	/**
	 * Получение следующей записи, подходящей по условию
	 */
	function getNextIndex($index = null) {
		$currIndex = $this->getIndex($index);
		for($nextIndex = $currIndex+1; $next = $this->getRecord($nextIndex); $nextIndex++) {
			if ($next['RecordStatus_Code'] != 3 && $this->checkCondition($nextIndex, $currIndex)) {
				return $nextIndex;
			}
		}
		return -1;
	}

	/**
	 * Добавление идентификатора для проверки
	 */
	function addCheck(&$record, $check_id) {
		if (!isset($record['check_ids']) || !in_array($check_id, $record['check_ids'])) {
			$record['check_ids'][] = $check_id;
		}
	}

	/**
	 * Модификация периода. Обрезается предыдущим и следующим найденными периодами
	 */
	function modifyPeriod($index = null) {
		$currIndex = $this->getIndex($index);
		$prevIndex = $this->getPrevIndex($currIndex);
		$nextIndex = $this->getNextIndex($currIndex);

		$curr = $this->getRecord($currIndex);
		$prev = $this->getRecord($prevIndex);
		$next = $this->getRecord($nextIndex);

		if ($prev && $prev['endDate'] && $prev['endDate'] > $curr['begDate']
			&& (!$curr['endDate'] || $prev['endDate'] <= $curr['endDate'])
		) {
			$curr['begDate'] = clone $prev['endDate'];
			date_modify($curr['begDate'], '+1 day');
			if ($curr['RecordStatus_Code'] == 1) {
				$curr['RecordStatus_Code'] = 2;	//Редактирование записи
			}
			$this->addCheck($prev, $curr['old_id']);
		}
		if ($next && $next['begDate'] >= $curr['begDate']
			&& (!$curr['endDate'] || $next['begDate'] < $curr['endDate'])
		) {
			$curr['endDate'] = clone $next['begDate'];
			if ($curr['endDate']) {
				date_modify($curr['endDate'], '-1 day');
			}
			if ($curr['RecordStatus_Code'] == 1) {
				$curr['RecordStatus_Code'] = 2;	//Редактирование записи
			}
			$this->addCheck($next, $curr['old_id']);
		}
		//Если текущий период полностью входит в предыдущий, то его можно удалить
		if ($prev && $prev['begDate'] <= $curr['begDate'] && (!$prev['endDate'] || ($curr['endDate'] && $prev['endDate'] >= $curr['endDate']))) {
			$this->addCheck($prev, $curr['old_id']);
			$curr['RecordStatus_Code'] = 3;	//Удаление записи
		}

		$this->procRecords[$currIndex] = $curr;
		if ($prev) {
			$this->procRecords[$prevIndex] = $prev;
		}
		if ($next) {
			$this->procRecords[$nextIndex] = $next;
		}

		return $curr;
	}

	/**
	 * Объединение пересекающихся периодов
	 */
	function unionPeriods($index = null) {
		$currIndex = $this->getIndex($index);
		$orig = $curr = $this->getRecord($currIndex);
		$prevIndex = $nextIndex = $currIndex;
		while(($prevIndex = $this->getPrevIndex($prevIndex)) >= 0 && $prev = $this->getRecord($prevIndex)) {
			if (!$prev['endDate'] || $prev['endDate'] >= $curr['begDate']) {
				$curr['begDate'] = clone $prev['begDate'];
				if (!$prev['endDate'] || $prev['endDate'] > $curr['endDate']) {
					$curr['endDate'] = $prev['endDate']?clone $prev['endDate']:null;
				}
				$this->addCheck($this->procRecords[$prevIndex], $curr['old_id']);
				$this->procRecords[$prevIndex]['RecordStatus_Code'] = 3;
			}
		}
		while(($nextIndex = $this->getNextIndex($nextIndex)) >= 0 && $next = $this->getRecord($nextIndex)) {
			if (!$curr['endDate'] || $next['begDate'] <= $curr['endDate']) {
				if (!$next['endDate'] || $next['endDate'] > $curr['endDate']) {
					$curr['endDate'] = $next['endDate']?clone $next['endDate']:null;
				}
				$this->addCheck($this->procRecords[$nextIndex], $curr['old_id']);
				$this->procRecords[$nextIndex]['RecordStatus_Code'] = 3;
			}
		}
		if ($curr['begDate'] != $orig['begDate'] || $curr['endDate'] != $orig['endDate']) {
			$curr['RecordStatus_Code'] = 2;
		}

		$this->procRecords[$currIndex] = $curr;
		return $curr;
	}

	/**
	 * Создание новых периодов при разбивке одного существующего
	 */
	function createNextPeriods($index = null) {
		$currIndex = $this->getIndex($index);
		$nextIndex = $this->getNextIndex($currIndex);

		$curr = $this->getRecord($currIndex);
		$next = $this->getRecord($nextIndex);

		$periods = array();
		$newRecords = array();

		if ($curr['RecordStatus_Code'] != 2) {
			return $newRecords;
		}

		$curr = $this->getOrigRecord($currIndex);

		while($next) {
			$nextIndex = $this->getNextIndex($nextIndex);
			$next1 = $this->getRecord($nextIndex);
			if (
				$next['endDate'] && $curr['endDate'] != $next['endDate']
				//&& (!$curr['endDate'] || $diff = date_diff($next['endDate'], $curr['endDate']))
			) {
				$diff = null;
				if ($curr['endDate']) {
					$diff = date_diff($curr['endDate'], $next['endDate']);
				}
				if (!$diff || $diff->days > 0 && $diff->invert) {
					$begDate = date_modify(clone $next['endDate'], '+1 day');
					$endDate = null;
					if ($next1) {
						$endDate = date_modify(clone $next1['begDate'], '-1 day');
					} else if ($curr['endDate']) {
						$endDate = $curr['endDate'];
					}
					$periods[] = array(
						'begDate' => $begDate,
						'endDate' => $endDate,
						'RecordStatus_Code' => 0,	//Добавление записи
						'check_ids' => array($curr['old_id'])
					);
				}
			}
			$next = $next1;
		}
		if (!$curr['endDate'] && $next['endDate']) {
			$periods[] = array(
				'begDate' => date_modify(clone $next['endDate'], '+1 day'),
				'endDate' => null,
				'RecordStatus_Code' => 0,	//Добавление записи
				'check_ids' => array($curr['old_id'])
			);
		}
		if (count($periods) > 0) {
			foreach($periods as $period) {
				$newRecords[] = array_merge($curr, $period);
			}
		}

		$this->newRecords = array_merge($this->newRecords, $newRecords);
		return $newRecords;
	}

	/**
	 * Обработка записей подчиненных объектов
	 */
	function processRecords($processor) {
		//Обрабатывается первоначальный массив
		for($this->currIndex = 0; $this->currIndex < count($this->origRecords); $this->currIndex++) {
			$currIndex = $this->currIndex;
			$curr = $this->getRecord($currIndex);
			call_user_func($processor, $this, $currIndex, $curr);
		}
		//Изменяются главные параметры обработанных записей
		for($currIndex = 0; $currIndex < count($this->procRecords); $currIndex++) {
			$curr = $this->getRecord($currIndex);
			if (!$curr['isMain'] && $curr['RecordStatus_Code'] != 3) {
				foreach($this->mainParams as $key => $value) {
					if (array_key_exists($key, $curr) && !empty($curr[$key]) && $curr[$key] != $value) {
						$curr[$key] = $value;
						$curr['RecordStatus_Code'] = 2;
					}
				}
			}
			$this->setRecord($currIndex, $curr);
		}
		//Изменяются главные параметры новых записей
		for($currIndex = 0; $currIndex < count($this->newRecords); $currIndex++) {
			$curr = $this->getNewRecord($currIndex);
			foreach($this->mainParams as $key => $value) {
				if (array_key_exists($key, $curr) && !empty($curr[$key]) && $curr[$key] != $value) {
					$curr[$key] = $value;
				}
			}
			$this->setNewRecord($currIndex, $curr);
		}
		return $this;
	}

	/**
	 * Обновление связей подчиненных объектов
	 */
	function updateLinks($index, $links) {
		if (count($links) == 0) {
			return array(array('Error_Msg' => '', 'Error_Code' => ''));
		}
		$record = $this->getRecord($index);

		$queryUpdates = "";
		foreach($links as $link) {
			$update = "{$link['LinkObject_Schema']}.{$link['LinkObject_Name']}";
			$set = "{$link['LinkObject_Column']} = {$link['new_id']}";
			$where = "{$link['LinkObject_Column']} = {$link['old_id']}";

			if (isset($link['additSet'])) {
				foreach($link['additSet'] as $key => $value) {
					$set .= ", $key = '$value'";
				}
			}
			if (isset($link['additWhere'])) {
				foreach($link['additWhere'] as $key => $value) {
					$where .= " and $key = '$value'";
				}
			}
			$queryUpdates .= "
				update {$update}
				set {$set}
				where {$where}
			";
		}

		$query = "
			declare
				@Error_Code bigint = null,
				@Error_Message varchar(4000) = '';
			set nocount on
			begin try
				{$queryUpdates}
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		return $this->model->queryResult($query);
	}

	/**
	 * Выполнить перенос подчиненных объектов при объединении записей
	 */
	function doUnion($options) {
		$idField = $options['idField'];
		$begDateField = $options['begDateField'];
		$endDateField = $options['endDateField'];
		$pmUser_id = $options['pmUser_id'];

		$ins = $options['ins'];
		$upd = $options['upd'];
		$del = $options['del'];
		$getLinks = isset($options['getLinks'])?$options['getLinks']:null;

		$records = $this->getAllRecords(true);
		for($currIndex = 0; $currIndex < count($records); $currIndex++) {
			$curr = $records[$currIndex];

			$curr[$begDateField] = ($curr['begDate'] instanceof DateTime)?$curr['begDate']->format('Y-m-d H:i:s'):null;
			$curr[$endDateField] = ($curr['endDate'] instanceof DateTime)?$curr['endDate']->format('Y-m-d H:i:s'):null;
			$curr['begDateParam'] = $curr[$begDateField];
			$curr['endDateParam'] = $curr[$endDateField];
			$curr['pmUser_id'] = $pmUser_id;

			if ($curr['RecordStatus_Code'] == 0) {
				$curr[$idField] = null;
				if (!is_callable($ins)) {
					return $this->model->createError('', 'Не возможно вызвать метод для создания записи');
				}
				$result = call_user_func($ins, $this->model, $curr);
				if (!is_array($result) || !isset($result[0]) || !isset($result[0][$idField])) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении записи'));
				}
				if (!empty($result[0]['Error_Msg'])) {
					return $result;
				}
				$curr['new_id'] = $curr[$idField] = $result[0][$idField];
			}

			if ($getLinks && !empty($curr['check_ids']) && count($curr['check_ids']) > 0) {
				if (!is_callable($getLinks)) {
					return $this->model->createError('', 'Не возможно вызвать метод для получения связей подчиненных объектов');
				}
				$result = $this->updateLinks($currIndex, call_user_func($getLinks, $this->model, $curr));
				if (!is_array($result) || !isset($result[0])) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при обновлении связей'));
				} else if (!empty($result[0]['Error_Msg'])) {
					return $result;
				}
			}

			switch($curr['RecordStatus_Code']) {
				case 2:
					if (!is_callable($upd)) {
						return $this->model->createError('', 'Не возможно вызвать метод для обновления записи');
					}
					$result = call_user_func($upd, $this->model, $curr);
					if (!is_array($result) || !isset($result[0]) || (empty($result[0]['Error_Msg']) && !isset($result[0][$idField]))) {
						return $this->model->createError('', 'Ошибка при обновлении записи');
					} else if (!empty($result[0]['Error_Msg'])) {
						return $result;
					}
					break;
				case 3:
					if (!is_callable($del)) {
						return $this->model->createError('', 'Не возможно вызвать метод для удаления записи');
					}
					$result = call_user_func($del, $this->model, $curr);
					if (!is_array($result) || !isset($result[0])) {
						return $this->model->createError('', 'Ошибка при получении результата сохранения записи');
					} else if (!empty($result[0]['Error_Msg'])) {
						return $result;
					}
					break;
			}
		}
		return array(array('success' => true));
	}
}