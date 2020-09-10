<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FoodCook_model - модель для работы с рецептами блюд
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			01.10.2013
 */

class FoodCook_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список продуктов питания
	 */
	function loadFoodCookGrid($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$filterList = array('(1=1)');
		$params = array();

		if ( !empty($data['FoodCook_Code']) ) {
			$filterList[] = "FC.FoodCook_Code ilike :FoodCook_Code";
			$params['FoodCook_Code'] = $data['FoodCook_Code'] . '%';
		}

		if ( !empty($data['FoodCook_Name']) ) {
			$filterList[] = "FC.FoodCook_Name ilike :FoodCook_Name";
			$params['FoodCook_Name'] = $data['FoodCook_Name'] . '%';
		}

		$query = "
			select
				-- select
				FC.FoodCook_id as \"FoodCook_id\",
				FC.FoodCook_Code as \"FoodCook_Code\",
				FC.FoodCook_Name as \"FoodCook_Name\",
				FC.FoodCook_Caloric as \"FoodCook_Caloric\",
				FC.FoodCook_Protein as \"FoodCook_Protein\",
				FC.FoodCook_Fat as \"FoodCook_Fat\",
				FC.FoodCook_Carbohyd as \"FoodCook_Carbohyd\",
				FC.FoodCook_Time as \"FoodCook_Time\",
				FC.FoodCook_Mass as \"FoodCook_Mass\"
				-- end select
			from
				-- from
				v_FoodCook FC
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				FC.FoodCook_Name
				-- end order by
			";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if ( is_object($result_count) ) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else {
			$count = 0;
		}

		if ( is_object($result) ) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}

		return false;
	}

	/**
	 * Возвращает данные для редактировния рецепта блюда
	 */
	function loadFoodCookEditForm($data) {
		$query = "
			select
				FC.FoodCook_id as \"FoodCook_id\",
				FC.FoodCook_Code as \"FoodCook_Code\",
				FC.FoodCook_Name as \"FoodCook_Name\",
				FC.FoodCook_Descr as \"FoodCook_Descr\",
				FC.FoodCook_DescrOrgan as \"FoodCook_DescrOrgan\",
				FC.FoodCook_Caloric as \"FoodCook_Caloric\",
				FC.FoodCook_Protein as \"FoodCook_Protein\",
				FC.FoodCook_Fat as \"FoodCook_Fat\",
				FC.FoodCook_Carbohyd as \"FoodCook_Carbohyd\",
				FC.FoodCook_Time as \"FoodCook_Time\",
				FC.FoodCook_Mass as \"FoodCook_Mass\",
				FC.Okei_id as \"Okei_id\"
			from
				v_FoodCook FC
			where
				FC.FoodCook_id = :FoodCook_id
		";
		$result = $this->db->query($query, array('FoodCook_id' => $data['FoodCook_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохраняет запись о рецепте блюда
	 */
	function saveFoodCook($data) {
		$response = array(
			 'FoodCook_id' => null
			,'Error_Code' => null
			,'Error_Msg' => null
		);

		$this->beginTransaction();

		// Проверяем на дубли по коду и наименованию
		$query = "
			select
				FoodCook_id as \"FoodCook_id\"
			from v_FoodCook
			where (FoodCook_Code = :FoodCook_Code or FoodCook_Name = :FoodCook_Name)
				and FoodCook_id != :FoodCook_id
			limit 1
		";

		$params = array(
			'FoodCook_id' => (!empty($data['FoodCook_id']) && $data['FoodCook_id'] > 0 ? $data['FoodCook_id'] : null),
			'FoodCook_Code' => $data['FoodCook_Code'],
			'FoodCook_Name' => $data['FoodCook_Name']
		);

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка дублей рецепта блюда по коду или наименованию)';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при проверке дублей рецепта блюда по коду или наименованию';
			return array($response);
		}
		else if ( count($queryResponse) > 0 ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Обнаружены дубли рецепта блюда по коду/наименованию';
			return array($response);
		}

		// Сохраняем рецепт блюда
		$query = "
			select
				FoodCook_id as \"FoodCook_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_FoodCook_" . (!empty($data['FoodCook_id']) && $data['FoodCook_id'] > 0 ? "upd" : "ins") . "(
				FoodCook_id := :FoodCook_id,
				FoodCook_Code := :FoodCook_Code,
				FoodCook_Name := :FoodCook_Name,
				FoodCook_Descr := :FoodCook_Descr,
				FoodCook_DescrOrgan := :FoodCook_DescrOrgan,
				FoodCook_Protein := :FoodCook_Protein,
				FoodCook_Fat := :FoodCook_Fat,
				FoodCook_Carbohyd := :FoodCook_Carbohyd,
				FoodCook_Caloric := :FoodCook_Caloric,
				FoodCook_Time := :FoodCook_Time,
				FoodCook_Mass := :FoodCook_Mass,
				Okei_id := :Okei_id,
				pmUser_id := :pmUser_id
			)
		";

		$params = array(
			'FoodCook_id' => (!empty($data['FoodCook_id']) && $data['FoodCook_id'] > 0 ? $data['FoodCook_id'] : null),
			'FoodCook_Code' => $data['FoodCook_Code'],
			'FoodCook_Name' => $data['FoodCook_Name'],
			'FoodCook_Descr' => $data['FoodCook_Descr'],
			'FoodCook_DescrOrgan' => $data['FoodCook_DescrOrgan'],
			'FoodCook_Protein' => $data['FoodCook_Protein'],
			'FoodCook_Fat' => $data['FoodCook_Fat'],
			'FoodCook_Carbohyd' => $data['FoodCook_Carbohyd'],
			'FoodCook_Caloric' => $data['FoodCook_Caloric'],
			'FoodCook_Time' => $data['FoodCook_Time'],
			'FoodCook_Mass' => $data['FoodCook_Mass'],
			'Okei_id' => $data['Okei_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение рецепта блюда)';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при сохранении рецепта блюда';
			return array($response);
		}
		else if ( !empty($queryResponse[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $queryResponse[0]['Error_Msg'];
			return array($response);
		}

		$response['FoodCook_id'] = $queryResponse[0]['FoodCook_id'];

		// Обрабатываем список ингредиентов
		if ( !empty($data['FoodCookSpecData']) ) {
			ConvertFromWin1251ToUTF8($_POST['FoodCookSpecData']);
			$FoodCookSpecData = json_decode($_POST['FoodCookSpecData'], true);

			if ( is_array($FoodCookSpecData) ) {
				for ( $i = 0; $i < count($FoodCookSpecData); $i++ ) {
					if ( empty($FoodCookSpecData[$i]['FoodCookSpec_id']) || !is_numeric($FoodCookSpecData[$i]['FoodCookSpec_id']) ) {
						continue;
					}

					if ( !isset($FoodCookSpecData[$i]['RecordStatus_Code']) || !is_numeric($FoodCookSpecData[$i]['RecordStatus_Code']) || !in_array($FoodCookSpecData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$FoodCookSpec = array(
						 'FoodCook_id' => $response['FoodCook_id']
						,'FoodCookSpec_id' => $FoodCookSpecData[$i]['FoodCookSpec_id']
						,'FoodStuff_id' => $FoodCookSpecData[$i]['FoodStuff_id']
						,'FoodCookSpec_Priority' => $FoodCookSpecData[$i]['FoodCookSpec_Priority']
						,'FoodCookSpec_MassN' => $FoodCookSpecData[$i]['FoodCookSpec_MassN']
						,'FoodCookSpec_MassB' => $FoodCookSpecData[$i]['FoodCookSpec_MassB']
						,'Okei_nid' => $FoodCookSpecData[$i]['Okei_nid']
						,'Okei_bid' => $FoodCookSpecData[$i]['Okei_bid']
						,'FoodCookSpec_Time' => $FoodCookSpecData[$i]['FoodCookSpec_Time']
						,'FoodCookSpec_Descr' => toAnsi($FoodCookSpecData[$i]['FoodCookSpec_Descr'])
						,'pmUser_id' => $data['pmUser_id']
					);

					switch ( $FoodCookSpecData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveFoodCookSpec($FoodCookSpec);
						break;

						case 3:
							$queryResponse = $this->deleteFoodCookSpec($FoodCookSpec);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($FoodCookSpecData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' ингредиента';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Сохраняет ингредиент блюда
	 */
	function saveFoodCookSpec($data) {
		$query = "
			select
				FoodCookSpec_id as \"FoodCookSpec_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_FoodCookSpec_" . (!empty($data['FoodCookSpec_id']) && $data['FoodCookSpec_id'] > 0 ? "upd" : "ins") . "(
				FoodCookSpec_id := :FoodCookSpec_id,
				FoodCook_id := :FoodCook_id,
				FoodStuff_id := :FoodStuff_id,
				FoodCookSpec_Priority := :FoodCookSpec_Priority,
				FoodCookSpec_MassN := :FoodCookSpec_MassN,
				FoodCookSpec_MassB := :FoodCookSpec_MassB,
				Okei_nid := :Okei_nid,
				Okei_bid := :Okei_bid,
				FoodCookSpec_Time := :FoodCookSpec_Time,
				FoodCookSpec_Descr := :FoodCookSpec_Descr,
				pmUser_id := :pmUser_id
			);
		";

		$params = array(
			'FoodCookSpec_id' => (!empty($data['FoodCookSpec_id']) && $data['FoodCookSpec_id'] > 0 ? $data['FoodCookSpec_id'] : null),
			'FoodCook_id' => (!empty($data['FoodCook_id']) ? $data['FoodCook_id'] : null),
			'FoodStuff_id' => (!empty($data['FoodStuff_id']) ? $data['FoodStuff_id'] : null),
			'FoodCookSpec_Priority' => (!empty($data['FoodCookSpec_Priority']) ? $data['FoodCookSpec_Priority'] : null),
			'FoodCookSpec_MassN' => (!empty($data['FoodCookSpec_MassN']) ? $data['FoodCookSpec_MassN'] : null),
			'FoodCookSpec_MassB' => (!empty($data['FoodCookSpec_MassB']) ? $data['FoodCookSpec_MassB'] : null),
			'Okei_nid' => (!empty($data['Okei_nid']) ? $data['Okei_nid'] : null),
			'Okei_bid' => (!empty($data['Okei_bid']) ? $data['Okei_bid'] : null),
			'FoodCookSpec_Time' => (!empty($data['FoodCookSpec_Time']) ? $data['FoodCookSpec_Time'] : null),
			'FoodCookSpec_Descr' => (!empty($data['FoodCookSpec_Descr']) ? $data['FoodCookSpec_Descr'] : null),
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает список ингредиентов блюда
	 */
	function loadFoodCookSpecGrid($data) {
		$query = "
			select
				fcs.FoodCookSpec_id as \"FoodCookSpec_id\",
				1 as \"RecordStatus_Code\",
				fcs.FoodStuff_id as \"FoodStuff_id\",
				fcs.FoodCookSpec_Priority as \"FoodCookSpec_Priority\",
				fs.FoodStuff_Name as \"FoodStuff_Name\",
				fs.FoodStuff_Protein as \"FoodStuff_Protein\",
				fs.FoodStuff_Fat as \"FoodStuff_Fat\",
				fs.FoodStuff_Carbohyd as \"FoodStuff_Carbohyd\",
				fs.FoodStuff_Caloric as \"FoodStuff_Caloric\",
				fcs.FoodCookSpec_MassN as \"FoodCookSpec_MassN\",
				fcs.FoodCookSpec_MassB as \"FoodCookSpec_MassB\",
				fcs.Okei_nid as \"Okei_nid\",
				fcs.Okei_bid as \"Okei_bid\",
				fcs.FoodCookSpec_Time as \"FoodCookSpec_Time\",
				fcs.FoodCookSpec_Descr as \"FoodCookSpec_Descr\"
			from
				v_FoodCookSpec fcs
				left join v_FoodStuff fs on fs.FoodStuff_id = fcs.FoodStuff_id
			where
				fcs.FoodCook_id = :FoodCook_id
			order by
				fcs.FoodCookSpec_Priority
		";
		$result = $this->db->query($query, array('FoodCook_id' => $data['FoodCook_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление ингредиента блюда
	 */
	function deleteFoodCookSpec($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_FoodCookSpec_del(
				FoodCookSpec_id := :FoodCookSpec_id
			)
		";

		$queryParams = array(
			'FoodCookSpec_id' => $data['FoodCookSpec_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}