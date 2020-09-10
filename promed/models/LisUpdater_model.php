<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      LisUpdater
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Markoff Andrew <markov@swan.perm.ru>
* @version      10.2011
*/

class LisUpdater_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct() {
		$this->load->library('textlog', array('file'=>'LisUpdater.log'));
		parent::__construct();
	}
	
	/**
	 * Запись данных
	 */
	function createTableFromArray($spr, $name) {
		$this->textlog->add('createTableFromArray: Записываем данные из ЛИМС в Промед, справочник '.$name);
		$header = array();
		$cou = count($spr);
		if ($cou > 0) {
			// создаем запрос на создание таблицы и запрос на вставку таблицы
			$sql_create = "
				if OBJECT_ID(N'lis.{$name}') is not null
					Delete from lis.{$name}
				else 
					Create table lis.{$name} ( ";
			$sql_insert = "
				Insert into lis.{$name} ( ";
			
			//$header = array();
			
			foreach($spr[0] as $key => $value) {
				if (is_int($value)) {
					$type = "bigint";
				} else {
					$type = "varchar(1000)";
				}
				//$header[$key] = $type;
				if (is_numeric($key)) { // атавизм
					$key = "ff".$key;
				}
				$sql_create .= "{$key} {$type} NULL, ";
				$sql_insert .= "
					{$key}, ";
			}
			$sql_create = substr($sql_create,0,-2)." )"; 
			$sql_insert = substr($sql_insert,0,-2)." ) VALUES "; 

			$sql_insert_rows = "";
			$paramsCounter = 0;
			$paramsArray = array();
			// создаем таблицу
			try {
				$response = $this->db->query($sql_create, array());
				if (!isset($response)) {
					echo "<pre>".$sql_create."</pre>";
					$this->textlog->add("createTableFromArray:: Ошибка при выполнении запроса: n/r/".$sql_create);
					return false;
				}
			} catch (Exception $e) {
				echo "<pre>".$sql_create."</pre>";
				$this->textlog->add("createTableFromArray:: Ошибка ".$e->getMessage()." при выполнении запроса: n/r/".$sql_create);
				return false;
			}
			$this->textlog->add('createTableFromArray:: Запрос: '.$sql_create);
			$this->textlog->add('createTableFromArray:: Количество записей для загрузки: '.$cou);
			if (isset($response) && is_object($response)) {
				foreach ($spr as $i=>$row) { // по записям
					$sql_insert_row = "";
					foreach ($row as $k=>$v) { // по значениям
						$paramsCounter++;
						$paramsArray['params'.$paramsCounter] = $v;
						$sql_insert_row .= ":params".$paramsCounter.", ";
					}
					/*if ($name=='_requestForm' || $name=='_equipment') {
						$this->textlog->add('createTableFromArray:: Insert: '.$sql_insert_row);
					}*/
					$sql_insert_row = "( ".substr($sql_insert_row,0,-2)." ), "; 
					$sql_insert_rows .= $sql_insert_row;
					if (((($i+1) % 50) == 0) || (($i+1) == $cou)) { // вставляем по 50 записей или последние оставшиеся
						try {
							$sql_insert_rows = substr($sql_insert_rows,0,-2); // отсекаем последнюю запятую
							// выполняем запрос с 50 записями 
							//$this->textlog->add('createTableFromArray:: '.$i." ".$sql_insert.$sql_insert_rows);
							$res = $this->db->query($sql_insert.$sql_insert_rows, $paramsArray);
							if (!isset($res)) {
								$this->textlog->add("createTableFromArray:: Ошибка при выполнении запроса: n/r/".$sql_insert.$sql_insert_rows);
								return false;
							}
							if (is_object($res)) {
								// вставка прошла успешно 
								//echo "&nbsp;Вставлены {$i} записей<br/>";
							}
						} catch (Exception $e) {
							$this->textlog->add('createTableFromArray:: Ошибка: '.$e->getMessage());
							log_message('error', "Ошибка при выполнении запроса: ".$e->getMessage()."\n\r". $sql_insert.$sql_insert_rows);
						}
						$sql_insert_rows = "";
						$paramsCounter = 0;
						$paramsArray = array();
					}
				}
				$this->textlog->add('createTableFromArray:: Таблица: '.$name.', вставлено записей: '.$i);
			} else {
				log_message('error', 'Ошибка при выполнении запроса (возможно недостаточно прав): '. $sql_create);
			}
		}
		$this->textlog->add('createTableFromArray:: End: '.$name);
	}
}
