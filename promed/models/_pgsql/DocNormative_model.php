<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DocNormative_model - модель для работы с нормативными документами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.03.2015
 */

class DocNormative_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список нормативных документов
	 */
	function loadDocNormativeList($data) {
		$params = array();
		$filters = "";

		if (!empty($data['query'])) {
			$params['DocNormative_Name'] = $data['query'];
			$filters .= " and DN.DocNormative_Name iLIKE :DocNormative_Name||'%'";

		}
		if (!empty($data['DocNormative_id'])) {
			$params['DocNormative_id'] = $data['DocNormative_id'];
			$filters .= " and DN.DocNormative_id = :DocNormative_id";
		}
		if (!empty($data['DocNormativeType_id'])) {
			$params['DocNormativeType_id'] = $data['DocNormativeType_id'];
			$filters .= " and DNT.DocNormativeType_id = :DocNormativeType_id";
		}

		$query = "
			select
				DN.DocNormative_id as \"DocNormative_id\",
				DNT.DocNormativeType_id as \"DocNormativeType_id\",
				DNT.DocNormativeType_Code as \"DocNormativeType_Code\",
				DN.DocNormative_Editor as \"DocNormative_Editor\",
				DN.DocNormative_Num as \"DocNormative_Num\",
				DN.DocNormative_Name as \"DocNormative_Name\",
				to_char(DN.DocNormative_begDate, 'DD.MM.YYYY') as \"DocNormative_begDate\",

				to_char(DN.DocNormative_endDate, 'DD.MM.YYYY') as \"DocNormative_endDate\",

				DN.DocNormative_File as \"DocNormative_File\"
			from
				v_DocNormative DN 

				left join v_DocNormativeType DNT  on DNT.DocNormativeType_id = DN.DocNormativeType_id

			where
				(1=1)
				{$filters}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка нормативных документов
	 */
	function loadDocNormativeGrid($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['DocNormative_Num'])) {
			$filters .= " and DN.DocNormative_Num iLIKE :DocNormative_Num||'%'";

			$params['DocNormative_Num'] = $data['DocNormative_Num'];
		}
		if (!empty($data['DocNormative_Name'])) {
			$filters .= " and DN.DocNormative_Name iLIKE :DocNormative_Name||'%'";

			$params['DocNormative_Name'] = $data['DocNormative_Name'];
		}
		if (!empty($data['DocNormative_Editor'])) {
			$filters .= " and DN.DocNormative_Editor iLIKE :DocNormative_Editor||'%'";

			$params['DocNormative_Editor'] = $data['DocNormative_Editor'];
		}
		if (isset($data['DocNormative_DateRange']) && !empty($data['DocNormative_DateRange'][0]) && !empty($data['DocNormative_DateRange'][1])) {
			$filters .= " and DN.DocNormative_begDate between :DocNormative_begDate and :DocNormative_endDate";
			$filters .= " and (DN.DocNormative_endDate is null or DN.DocNormative_endDate >= :DocNormative_begDate)";
			$params['DocNormative_begDate'] = $data['DocNormative_DateRange'][0];
			$params['DocNormative_endDate'] = $data['DocNormative_DateRange'][1];
		}
		if (!empty($data['DocNormativeType_id'])) {
			$filters .= " and DN.DocNormativeType_id = :DocNormativeType_id";
			$params['DocNormativeType_id'] = $data['DocNormativeType_id'];
		}

		$query = "
			select
				-- select
				DN.DocNormative_id as \"DocNormative_id\",
				DN.DocNormative_Num as \"DocNormative_Num\",
				DN.DocNormative_Name as \"DocNormative_Name\",
				DN.DocNormative_Editor as \"DocNormative_Editor\",
				to_char(DN.DocNormative_begDate, 'DD.MM.YYYY') as \"DocNormative_begDate\",

				to_char(DN.DocNormative_endDate, 'DD.MM.YYYY') as \"DocNormative_endDate\",

				DNT.DocNormativeType_id as \"DocNormativeType_id\",
				DNT.DocNormativeType_Name as \"DocNormativeType_Name\"
				-- end select
			from
				-- from
				v_DocNormative DN 

				left join v_DocNormativeType DNT  on DNT.DocNormativeType_id = DN.DocNormativeType_id

				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				DN.DocNormative_begDate
				-- end order by
		";

		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
		if (is_array($result) && $count !== false) {
			return array(
				'data' => $result,
				'totalCount' => $count
			);
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для редактирования нормативного документа
	 */
	function loadDocNormativeForm($data) {
		$params = array('DocNormative_id' => $data['DocNormative_id']);
		$query = "
			select
				DN.DocNormative_id as \"DocNormative_id\",
				DN.DocNormative_Num as \"DocNormative_Num\",
				DN.DocNormative_Name as \"DocNormative_Name\",
				DN.DocNormative_Editor as \"DocNormative_Editor\",
				DN.DocNormative_File as \"DocNormative_File\",
				DN.DocNormativeType_id as \"DocNormativeType_id\",
				to_char(DN.DocNormative_begDate, 'DD.MM.YYYY') as \"DocNormative_begDate\",

				to_char(DN.DocNormative_endDate, 'DD.MM.YYYY') as \"DocNormative_endDate\"

			from v_DocNormative DN 

			where DN.DocNormative_id = :DocNormative_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохраняет нормативный документ
	 */
	function saveDocNormative($data) {
		$params = array(
			'DocNormative_id' => !empty($data['DocNormative_id'])?$data['DocNormative_id']:null,
			'DocNormativeType_id' => $data['DocNormativeType_id'],
			'DocNormative_Editor' => $data['DocNormative_Editor'],
			'DocNormative_Num' => $data['DocNormative_Num'],
			'DocNormative_begDate' => $data['DocNormative_begDate'],
			'DocNormative_endDate' => !empty($data['DocNormative_endDate'])?$data['DocNormative_endDate']:null,
			'DocNormative_Name' => $data['DocNormative_Name'],
			'DocNormative_File' => !empty($data['DocNormative_File'])?$data['DocNormative_File']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['DocNormative_id'])) {
			$procedure = 'p_DocNormative_ins';
		} else {
			$procedure = 'p_DocNormative_upd';
		}

		$query = "
			select DocNormative_id as \"DocNormative_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				DocNormative_id := :DocNormative_id,
				DocNormativeType_id := :DocNormativeType_id,
				DocNormative_Editor := :DocNormative_Editor,
				DocNormative_Num := :DocNormative_Num,
				DocNormative_begDate := :DocNormative_begDate,
				DocNormative_endDate := :DocNormative_endDate,
				DocNormative_Name := :DocNormative_Name,
				DocNormative_File := :DocNormative_File,
				pmUser_id := :pmUser_id);
		";

		return $this->queryResult($query, $params);
	}
}