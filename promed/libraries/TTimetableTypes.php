<?php
/**
* TTimetableTypes - класс, хранилище типов бирок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      29.07.2014
*/

class TTimetableTypes
{
	static private $types = null;
	static private $instance = null;
	
	/**
	 * Приватный конструктор, чтобы никто снаружи его не вызвал
	 */
	private function __construct() {
		self::loadTypes();
	}
	
	/**
	 * Реализация паттерна синглтон, возвращение инстанса класса
	 */
	public static function &instance() {
		if (!self::$instance) {
			self::$instance = new TTimetableTypes();
		}
		return self::$instance;
	}
	
	/**
	 * Загрузка списка типов бирок
	 */
	public static function loadTypes() {
		
		// Если типы бирок есть в кэше, берем оттуда
		if ( ( function_exists('apc_fetch') && $res = apc_fetch("TimetableTypes") ) ) {
			self::$types = $res;
			return;
		}
		
		$CI =& get_instance();
		$DB = $CI->load->database('default', TRUE);
		
		if ( $DB ) {
			$sql = "
				select
					tt.TimetableType_id,
					tt.TimetableType_Name,
					tt.TimetableType_Style,
					tt.TimetableType_StylePerson,
					tta.TimetableTypeAttribute_Code,
					tta.TimetableTypeAttribute_Type
				from dbo.v_TimetableType tt (nolock)
				left join TimetableTypeAttributeLink ttal (nolock) on tt.TimeTableType_id = ttal.TimeTableType_id
				left join TimetableTypeAttribute tta (nolock) on tta.TimetableTypeAttribute_id = ttal.TimetableTypeAttribute_id
				order by tt.TimetableType_id, tta.TimetableTypeAttribute_Type
			";
			$res = $DB->query(
				$sql
			)->result('array');
			
			$type = null;
			foreach($res as $row) {
				if ( !isset($type) || $type->id != $row['TimetableType_id'] ) {
					if ( isset($type) ) {
						self::$types[] = $type;
					}
					
					$type = new TTimetableType(
						$row['TimetableType_id'],
						$row['TimetableType_Name'],
						$row['TimetableType_Style'],
						$row['TimetableType_StylePerson'],
						array(),
						array()
					);
				}
				if ($row['TimetableTypeAttribute_Type'] == 'Place') {
					$type->addPlace($row['TimetableTypeAttribute_Code']);
				} else if ($row['TimetableTypeAttribute_Type'] == 'Source') {
					$type->addSource($row['TimetableTypeAttribute_Code']);
				}
				
			}
			self::$types[] = $type;
		}
		
		// Сохраняем типы бирок в кэш
		if (function_exists('apc_store'))
		apc_store('TimetableTypes', self::$types, 60 * 5);
		
		/*
		get_instance()->config->load('timetabletypes', TRUE);
		$config = & get_config();
		self::$types = array();
		foreach($config['timetabletypes']['types'] as $type) {
			self::$types[] = new TTimetableType(
				$type['id'],
				$type['name'],
				$type['style'],
				$type['style_person'],
				$type['sources'],
				$type['places']
			);
		}*/
	}
	
	/**
	 * Возвращает бирку неопределенного типа, в случае если переданный тип бирки не существует
	 */
	private static function getUndefinedType() {
		return new TTimetableType(
			999,
			'Неопределенная',
			'background-color: #dddddd;',
			'background-color: #ffdddd;',
			array(),
			array(1,2,3)
		);
	}
	
	/**
	 * Получение объекта типа с заданным идентификатором
	 */
	public static function getTimetableType($TimetableType_id) {
		if ( !isset(self::$types) ) {
			self::loadTypes();
		}
		foreach(self::$types as $TimetableType) {
			if ($TimetableType->id == $TimetableType_id) {
				return $TimetableType;
			}
		}
		return self::getUndefinedType(); // если бирка с типом не нашлась, возвращаем специальный тип - неопределенная бирка
	}
	
	/**
	 * Получение общего списка типов или для переданного места 
	 */
	public static function getTypes($place = null, $include_undefined = false) {
		if ( !isset(self::$types) ) {
			self::loadTypes();
		}
		if (empty($place)) {
			$res = self::$types;
		} else {
			$res = array_filter(self::$types, function($type) use(&$place) {
					return $type->forPlace($place);
				}
			);
		}
		if ($include_undefined) {
			$res[] = self::getUndefinedType();
		}

		return $res;
	}
}

class TTimetableType {
	/**
	 * Идентификатор типа
	 */
	var $id;
	
	/**
	 * Название типа бирки
	 */
	var $name;
	
	/**
	 * CSS стиль бирки
	 */
	private $style;
	
	/**
	 * CSS стиль занятой бирки
	 */
	private $style_person;
	
	/**
	 * Источники записи на бирку
	 */
	private $sources = array();
	
	/**
	 * В каких местах используется тип бирки
	 */
	private $places = array();
	
	/**
	 * Конструктор
	 */
	function __construct($id, $name, $style, $style_person, $sources, $places ) {
		$this->id = $id;
		$this->name = $name;
		$this->style = $style;
		$this->style_person = $style_person;
		$this->sources = $sources;
		$this->places = $places;
	}
	
	/**
	 * Подсказка к бирке
	 */
	function getTip($person_id = null) {
		return "<b>Свободно</b>" . ( empty($person_id) ? "<br/>".$this->name : "");
	}
	
	/**
	 * Класс бирки
	 */
	function getClass($person_id = null) {
		
		return 'TimetableType_' . $this->id . (!empty($person_id) ? '_person' : '');
	}
	
	/**
	 * Стиль бирки
	 */
	function getStyle($person_id = null) {
		return empty($person_id) ? $this->style : $this->style_person;
	}
	
	/**
	 * Тип для места
	 */
	function forPlace($place) {
		return in_array($place , $this->places);
	}
	
	/**
	 * Получение допустимых источников записи для типа бирки
	 */
	function getSources() {
		return $this->sources;
	}
	
	/**
	 * Переданный источник находится в источниках записи этого типа бирок
	 */
	function inSources($source) {
		return in_array($source, $this->sources);
	}
	
	/**
	 * Добавление места
	 */
	function addPlace($place) {
		$this->places[] = $place;
	}
	
	/**
	 * Добавление источника
	 */
	function addSource($source) {
		$this->sources[] = $source;
	}
	
}
?>