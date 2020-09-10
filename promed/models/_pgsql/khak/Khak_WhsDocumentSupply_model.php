<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Договора поставок
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Valery Bondarev
 * @version         01.0202
 */
require_once(APPPATH . 'models/_pgsql/WhsDocumentSupply_model.php');

class Khak_WhsDocumentSupply_model extends WhsDocumentSupply_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *  Генерация номера для ГК
	 */
	function generateNum($data)
	{
		$doc_num = "0";
		$year_str = "";
		$cost_str = "";

		if (!empty($data['WhsDocumentUc_pid'])) {
			$query = "
				select
					WhsDocumentUc_Num as \"WhsDocumentUc_Num\"
				from
					v_WhsDocumentUc
				where
					WhsDocumentUc_id = :WhsDocumentUc_pid;
			";
			$result = $this->getFirstResultFromQuery($query, $data);
			if (!empty($result)) {
				$doc_num = $result;
			}
		} else {
			$query = "
				select
					COALESCE(max(cast(WhsDocumentUc_Num as bigint)),0) + 1 as \"WhsDocumentUc_Num\"
				from
					v_WhsDocumentUc
				where
					WhsDocumentUc_Num not like '%.%' and
					WhsDocumentUc_Num not like '%,%' and
					isnumeric(WhsDocumentUc_Num) = 1 and
					len(WhsDocumentUc_Num) <= 18 and
					WhsDocumentType_id in (
						select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code in (3,6,18) -- 3 - Контракт на поставку; 6 - Контракт на поставку и отпуск; 18 - Контракт ввода остатков.
					);
			";
			$result = $this->getFirstResultFromQuery($query);
			if (!empty($result)) {
				$doc_num = $result;
			}
		}

		$year_str = date("y");

		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$query = "
				select
					WhsDocumentCostItemType_Nick as \"WhsDocumentCostItemType_Nick\"
				from
					v_WhsDocumentCostItemType
				where
					WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
			";
			$cost_nick = $this->getFirstResultFromQuery($query, $data);

			$query = "
				select
					DrugFinance_SysNick as \"DrugFinance_SysNick\"
				from
					v_DrugFinance
				where
					DrugFinance_id = :DrugFinance_id;
			";
			$fin_nick = $this->getFirstResultFromQuery($query, $data);

			$query = "
				select
					WhsDocumentType_Code as \"WhsDocumentType_Code\"
				from
					v_WhsDocumentType
				where
					WhsDocumentType_id = :WhsDocumentType_id;
			";
			$type_code = $this->getFirstResultFromQuery($query, $data);

			switch ($cost_nick) {
				case 'fl':
					if ($type_code == '3') { //3 - контракт с типом "на поставку";
						$cost_str = "(ДЛО)";
					}
					if ($type_code == '6') { //6 - контракт с типом "на поставку и отпуск";
						$cost_str = "(ПЗ ДЛО)";
					}
					break;
				case 'rl':
					if ($type_code == '3') { //3 - контракт с типом "на поставку";
						$cost_str = "(РП)";
					}
					if ($type_code == '6') { //6 - контракт с типом "на поставку и отпуск";
						$cost_str = "(ПЗ РП)";
					}
					break;
				case 'vzn':
					if ($type_code == '3') { //3 - контракт с типом "на поставку";
						$cost_str = "(ВЗН)";
					}
					break;
				case 'sakhar':
					if ($type_code == '3') { //3 - контракт с типом "на поставку";
						$cost_str = "(СД)";
					}
					break;
				case 'vich':
					if ($type_code == '3') { //3 - контракт с типом "на поставку";
						if ($fin_nick == 'fed') {
							$cost_str = "(ДЛО)(СПИД)";
						}
						if ($fin_nick == 'reg') {
							$cost_str = "(СПИД)";
						}
					}
					break;
			}

			if (!empty($cost_str)) {
				$cost_str = " " . $cost_str;
			}
		}

		$num = "{$doc_num}/806.{$year_str}.XXX{$cost_str}";

		return array(array('WhsDocumentUc_Num' => $num));
	}
}