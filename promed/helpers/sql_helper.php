<?php
/**
* Sql_helper - хелпер, с функциями для дополнительной обработки SQL-запросов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan  aka IVP (ipshon@rambler.ru)
* @version      06.11.2009
*/

/**
 * Преобразование SQL-запроса в SQL-запрос, возвращающий строки из заданного диапазона.
 * Если $start задан ноль, то к запросу просто добавляется "TOP $limit".
 *
 * Метод требует оформления SQL-запроса с плейсхолдерами:
 * -- variables - начало объявления variables
 * -- end variables - окончание объявления variables
 * -- select - начало основного select
 * -- end select - окончание основнго select
 * -- form - начало основного from
 * -- end from - окончание основного from
 * -- where - начало основного where
 * -- end where - окончание основного where
 * -- order by - начало основного order by
 * -- end order by - окончание основного order by
 * -- option - опции выполнения запроса
 * -- end option - окончание опций выполнения запроса
 *
 * @access	public
 * @param	string $query исходный запрос
 * @param	int $start = 0 начало выборки
 * @param	int $limit = 1000 диапазон
 * @return	string
 */
function getLimitSQLPH($query, $start = 0, $limit = 1000, $distinct = '', $addit_order_by = '', $addit_with = '', $usePostgre = false )
{
	$variables = "";
	$select = "";
	$where = "";
	$from = "";
	$order_by = "";
	$limit_sql = "";
	$option = "";
	$addit_with = "";
	$addit_with2 = "WITH ";
	
	// проверка входных параметров.
	if ( !($start >= 0) || !($limit >= 1) )
	{
		DieWithError("Неверно заданы параметры диапазона.");
	}
	
	$query = trim($query);
	// VARIABLES
	$exp = preg_match("/--[\s]*variables([\w\W]*)--[\s]*end variables/i", $query, $maches);
	if ( isset($maches[1]) ) {
		$variables = $maches[1];
	}
	// SELECT
	$exp = preg_match("/--[\s]*select([\w\W]*)--[\s]*end select/i", $query, $maches);
	$maches[1] = preg_replace("/TOP\s+\d+\s+/","",$maches[1]);
	$select = $maches[1];
	// FROM
	$exp = preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
	$from = $maches[1];
	// WHERE
	//$exp = preg_match("/\s+WHERE\s+([\w\W]+)(\s+GROUP\s+BY)|(\s+ORDER\s+BY)/i", $query, $maches);
	$exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);
	if ( isset($maches[1]) )
	{
		$where = $maches[1];
	}
	
	// addit with
	//$exp = preg_match("/\s+WHERE\s+([\w\W]+)(\s+GROUP\s+BY)|(\s+ORDER\s+BY)/i", $query, $maches);
	$exp = preg_match("/--[\s]*addit with([\w\W]*)--[\s]*end addit with/i", $query, $maches);
	if ( isset($maches[1]) )
	{
		$addit_with = $maches[1];
		$addit_with2 = ", ";
	}
	
	// ORDER BY
	$exp = preg_match("/--[\s]*order by([\w\W]*)--[\s]*end order by/i", $query, $maches);
	
	
	$order_sql = "";
	if ( isset($maches[1]) && (strlen($distinct)==0))
	{
		$order_by = $maches[1];
		$order_sql = "
						ORDER BY 
							".$order_by."";
		$order_row = $order_sql;
	}
	elseif  (isset($maches[1]) && (strlen($distinct)>0))
	{
		// Если используется distinct (что несомненно бывает, то сортировка выполяется автоматически по порядку полей в запросе)
		// то есть по идее сортировки не нужно
		$order_by = $maches[1];
		$order_row = "
						ORDER BY 
							".$order_by."";
		$order_sql = $order_row;
	}
	else 
	{
		DieWithError('В запросе отсутствует часть ORDER BY');
	}
	// OPTION
	$exp = preg_match("/--[\s]*option([\w\W]*)--[\s]*end option/i", $query, $maches);
	if ( isset($maches[1]) )
	{
		$option = $maches[1];
	}

	$ci =& get_instance();
	
	/*
	* если начальная позиция диапазона задана и она больше 0,
	* то преобразуем запрос, чтобы в результате вызвращались записи из заданого диапазона
	*/
	if ( $start >= 0 )
	{
		if ($ci->usePostgre || $usePostgre) {
			$limit_sql = "
				" . $variables . "
				" . $addit_with . "
				SELECT " . $distinct .
				$select . "
				FROM  
				" . $from . " " .
				(empty($where) ? "" : "WHERE " . $where) . "
				" . $order_sql . "
				OFFSET " . $start . " ROWS		
				FETCH NEXT " . $limit . " ROWS ONLY 
				" . $option;
		} else if ( $start > 0 )
		{
			if (defined('USE_NEW_SQL_PAGING') && USE_NEW_SQL_PAGING) {
				$limit_sql = "
				" . $variables . "
				" . $addit_with . "
				SELECT " . $distinct .
					$select . "
				FROM  
				" . $from . " " .
					(empty($where) ? "" : "WHERE " . $where) . "
				" . $order_sql . "
				OFFSET " . $start . " ROWS		
				FETCH NEXT " . $limit . " ROWS ONLY 
				" . $option;
			} else {
				// если начальная позиция больше нуля, то формируем запрос с использованием row_number() OVER()
				$top = $start + $limit;
				$betwstart = $start + 1;
				$betwend = $top;
				$limit_sql = "
				" . $variables . "
				" . $addit_with . "
				" . $addit_with2 . " LimitRows AS (
					SELECT " . $distinct . " TOP " . $top . " " .
					$select . "
						,row_number() OVER(
							" . $order_row . "
						) AS row_num
					FROM  
					" . $from . " " .
					(empty($where) ? "" : "WHERE " . $where) . "
					" . $order_sql . "
				)
				SELECT 
					* 
				FROM
					LimitRows
				WHERE
					row_num BETWEEN " . $betwstart . " and " . $betwend . " 
				" . $addit_order_by . " 
				" . $option;
			}
		}
		// иначе просто добавляем TOP
		else
		{
			$limit_sql = "
				" . $variables . "
				" . $addit_with . "
				SELECT ". $distinct ." TOP ".$limit." ".
				$select."
			FROM  
			" . $from ." ".
			(empty($where)?"":"WHERE ".$where)."
			" . $order_sql . "
			" . $option;
		}
	}
	else
	{
		$limit_sql = $query;
	}
	return $limit_sql;
}

/**
 * Преобразование SQL-запроса в SQL-запрос, возвращающий количество.
 *
 * Метод требует оформления SQL-запроса с плейсхолдерами:
 * -- select - начало основного select
 * -- end select - окончание основнго select
 * -- from - начало основного from
 * -- end from - окончание основного from
 * -- where - начало основного where
 * -- end where - окончание основного where
 * -- order by - начало основного order by
 * -- end order by - окончание основного order by
 * -- group by - начало основного group by
 * -- end group by - окончание основного group by
 *
 * Savage [2009-11-23 10:21] Добавил выпиливание group by, если указаны плейсхолдеры
 *     Если нужнен подсчет с группировкой, то плейсхолдеры не указывать
 *
 * @access	public
 * @param	string $query исходный запрос
 * @return	string
 */
function getCountSQLPH($sql, $field = '*', $distinct = '', $addit_with = '', $groupby = false) {
	// вставка count
	$sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " count( ". $distinct ." ".$field." ) AS cnt ", $sql);
	// удаление ORDER BY
	$sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);
	// удаление GROUP BY
	$sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);

	// для корректного подсчёта записей с group by'ем запрос надо обернуть ещё одним каунтом
	if ($groupby) {
		// если есть variables надо вынести их за запрос
		$variables = "";
		$exp = preg_match("/--[\s]*variables([\w\W]*)--[\s]*end variables/i", $sql, $maches);
		if ( isset($maches[1]) ) {
			$variables = $maches[1];
			// удаление variables
			$sql = preg_replace("/--[\s]*variables([\w\W]*)--[\s]*end variables/i", "", $sql);
		}
		$sql = $variables."select count(*) as cnt from ({$sql}) as query";
	}

	return $sql;
}


/**
 * Преобразование SQL-запроса в SQL-запрос, возвращающий строки из заданного диапазона.
 * Если $start задан ноль, то к запросу просто добавляется "TOP $limit".
 *
 * @access	public
 * @param	string $query исходный запрос
 * @param	int $start = 0 начало выборки
 * @param	int $limit = 1000 диапазон
 * @return	string
 */
function getLimitSQL($query, $start = 0, $limit = 1000, $usePostgre = false)
{
	$select = "";
	$where = "";
	$from = "";
	$order_by = "";
	$limit_sql = "";
	
	// проверка входных параметров.
	if ( !($start >= 0) || !($limit >= 1) )
	{
		DieWithError("Неверно заданы параметры диапазона.");
	}
	
	$query = trim($query);
	// SELECT
	$exp = preg_match("/SELECT\s+([\w\W]*)\s+FROM/i", $query, $maches);
	$maches[1] = preg_replace("/TOP\s+\d+\s+/","",$maches[1]);
	$select = $maches[1];
	// FROM
	$exp = preg_match("/\s+FROM\s+([\w\W]*)\s+WHERE/i", $query, $maches);
	$from = $maches[1];
	// WHERE
	//$exp = preg_match("/\s+WHERE\s+([\w\W]+)(\s+GROUP\s+BY)|(\s+ORDER\s+BY)/i", $query, $maches);
	$exp = preg_match("/\s+WHERE\s+([\w\W]+)\s+ORDER\s+BY/i", $query, $maches);
	if ( isset($maches[1]) )
	{
		$where = $maches[1];
	}
	// ORDER BY
	$exp = preg_match("/\s+ORDER\s+BY\s+([\w\W]*)$/i", $query, $maches);
	if ( isset($maches[1]) )
	{
		$order_by = $maches[1];
	}
	else
	{
		DieWithError('В запросе отсутствует часть ORDER BY');
	}

	$ci =& get_instance();
	
	/*
	* если начальная позиция диапазона задана и она больше 0,
	* то преобразуем запрос, чтобы в результате вызвращались записи из заданого диапазона
	*/
	if ( $start >= 0 )
	{
		if ($ci->usePostgre|| $usePostgre) {
			$limit_sql = "SELECT " . $select . "
				FROM  
				" . $from . " " .
				(empty($where) ? "" : "WHERE " . $where) . "
				ORDER BY 
				" . $order_by . "
				OFFSET " . $start . " ROWS		
				FETCH NEXT " . $limit . " ROWS ONLY
			";
		} else if ( $start > 0 )
		{
			if (defined('USE_NEW_SQL_PAGING') && USE_NEW_SQL_PAGING) {
				$limit_sql = "SELECT " . $select . "
					FROM  
					" . $from . " " .
					(empty($where) ? "" : "WHERE " . $where) . "
					ORDER BY 
					" . $order_by . "
					OFFSET " . $start . " ROWS		
					FETCH NEXT " . $limit . " ROWS ONLY
				";
			} else {
				// если начальная позиция больше нуля, то формируем запрос с использованием row_number() OVER()
				$top = $start + $limit;
				$betwstart = $start + 1;
				$betwend = $top;
				$limit_sql = "WITH LimitRows AS (
					SELECT TOP " . $top . " " .
						$select . "
						,row_number() OVER(
							ORDER BY 
								" . $order_by . "
						) AS row_num
					FROM  
					" . $from . " " .
						(empty($where) ? "" : "WHERE " . $where) . "
					ORDER BY 
					" . $order_by . "
				)
				SELECT 
					* 
				FROM
					LimitRows
				WHERE
					row_num BETWEEN " . $betwstart . " and " . $betwend;
			}
			
		}
		// иначе просто добавляем TOP
		else
		{
			$limit_sql = "SELECT TOP ".$limit." ".
				$select."
			FROM  
			".$from." ".
			(empty($where)?"":"WHERE ".$where)."
			ORDER BY 
			".$order_by;
		}
	}
	else
	{
		$limit_sql = $query;
	}
	return $limit_sql;
}

/**
 * Преобразование SQL-запроса в SQL-запрос, возвращающий количество.
 *
 * @access	public
 * @param	string $query исходный запрос
 * @return	string
 */
function getCountSQL($sql)
{
	// вставка count
	$sql = preg_replace("/SELECT\s+[\w\W]*\s+FROM/i", "SELECT count(*) AS cnt FROM ", $sql);
	// удаление ORDER BY
	$sql = preg_replace("/\s+ORDER\s+BY\s+([\w\W]*)/i", "", $sql);
	return $sql;
}

/**
 * Генерирует условие WHERE с плейсхолдером
 *
 * @param array $pieces Набор условий
 * @return string Условие WHERE для SQL запроса
 */
function ImplodeWherePH($pieces) {
	If (count($pieces)>0)
		return "WHERE 
		-- where
		".Implode(' and ', $pieces)."
		-- end where
		";
	else
		return '';
}

/**
 * Генерирует условие WHERE
 *
 * @param array $pieces Набор условий
 * @return string Условие WHERE для SQL запроса
 */
function ImplodeWhere($pieces) {
	If (count($pieces)>0)
		return "WHERE ".Implode(' and ', $pieces);
	else
		return '';
}

/**
 * Генерирует чистый SQL текст из запроса с параметрами
 */
function getDebugSQL($sql, $queryParams = array()){
	$DB = new CI_DB(array());
	list($sql, $params) = $DB->ReplaceNamedParams($sql, $queryParams, true);
	return $DB->compile_binds($sql, $params);
}
?>
