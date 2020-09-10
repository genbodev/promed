<?php
/**
* TTimetable - Абстрактный класс описывающий любую бирку
* Унаследовано от ЭР
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      05.10.2011
*/

abstract class TTimetable {
	
	
	/**
	 * Печать одной ячейки в таблице на 2 недели
	 * $IsForRow boolean Печать ячейки для ряда, с добавлением ФИО 
	 */
	abstract function PrintCell( $IsForRow = false ) ;
	
	
	/**
	 * Печать одной ячейки в таблице на 2 недели для печатного варианта
	 */
	abstract function PrintCellForPrint() ;
	
	
	/**
	 * Печать одной ячейки в таблице на 2 недели для редактирования
	 */
	abstract function PrintCellForEdit() ;
	
	
	/**
	 * Печать одной строки в списке на день
	 */
	abstract function PrintDayRow($IsPrint = false) ;
	
	/**
	 * Печать одной строки в списке на день для печатного варианта
	 */
	abstract function PrintDayRowForPrint() ;
	
	
	/**
	 * Всплывающая подсказка по записанному человеку
	 */
	abstract function GetPersonTip( ) ;
	
	
	/**
	 * Печать одной ячейки в таблице при выписке направлений
	 * $IsForRow boolean Печать ячейки для ряда, с добавлением ФИО 
	 */
	abstract function PrintCellForDirection( $IsForRow = false ) ;
}
?>