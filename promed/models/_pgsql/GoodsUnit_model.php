<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * GoodsUnit_model - модель для работы с единицами измерения товара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.01.2016
 */

class GoodsUnit_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка складов для грида
	 */
	function loadGoodsUnitGrid($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['GoodsUnit_Name'])) {
			$filters .= " and GU.GoodsUnit_Name like '%'||:GoodsUnit_Name||'%'";
			$params['GoodsUnit_Name'] = $data['GoodsUnit_Name'];
		}

		$query = "
			select
				-- select
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Nick as \"GoodsUnit_Nick\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\",
				O.Okei_id as \"Okei_id\",
				O.Okei_Code as \"Okei_Code\",
				O.Okei_Name as \"Okei_Name\"
				-- end select
			from
				-- from
				v_GoodsUnit GU
				left join v_Okei O on O.Okei_id = GU.Okei_id
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				GU.GoodsUnit_Name
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Сохранения единицы измерения товара
	 */
	function saveGoodsUnit($data) {
		$params = array(
			'GoodsUnit_id' => !empty($data['GoodsUnit_id'])?$data['GoodsUnit_id']:null,
			'GoodsUnit_Nick' => $data['GoodsUnit_Nick'],
			'GoodsUnit_Name' => $data['GoodsUnit_Name'],
			'GoodsUnit_Descr' => !empty($data['GoodsUnit_Descr'])?$data['GoodsUnit_Descr']:null,
			'Okei_id' => !empty($data['Okei_id'])?$data['Okei_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($data['GoodsUnit_id'])) {
			$procedure = 'p_GoodsUnit_ins';
		} else {
			$procedure = 'p_GoodsUnit_upd';
		}

		$query = "
			select
				GoodsUnit_id as \"GoodsUnit_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				GoodsUnit_id := :GoodsUnit_id,
				GoodsUnit_Nick := :GoodsUnit_Nick,
				GoodsUnit_Name := :GoodsUnit_Name,
				GoodsUnit_Descr := :GoodsUnit_Descr,
				Okei_id := :Okei_id,
				Org_id := null,
				Region_id := null,
				pmUser_id := :pmUser_id
			)
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Удаления единицы измерения товара
	 */
	function deleteGoodsUnit($data) {
		$params = array('GoodsUnit_id' => $data['GoodsUnit_id']);

		$allowDelete = false;

		$Org_id = $this->getFirstResultFromQuery("
			select
				Org_id as \"Org_id\"
			from v_GoodsUnit
			where GoodsUnit_id = :GoodsUnit_id
			limit 1
		", $params);
		if ($Org_id === false) {
			return $this->createError('','Ошибка при получении организации по единице измерения товара');
		}

		if (havingGroup(array('LpuAdmin')) && $Org_id == $data['session']['org_id']) {
			$allowDelete = true;
		}
		if (havingGroup(array('OuzAdmin','OuzUser')) && $Org_id === null) {
			$allowDelete = true;
		}
		if (!$allowDelete) {
			return $this->createError('','Отсутствуют права на удаление единицы измерения товара');
		}

		$count = $this->getFirstResultFromQuery("
			select
				count(GoodsPackCount_id)
			from v_GoodsPackCount
			where GoodsUnit_id = :GoodsUnit_id
			limit 1
		", $params);
		if ($count === false) {
			return $this->createError('','Ошибка при определения использования единицы измерения товара');
		}
		if ($count > 0) {
			return $this->createError('','Единица измерения товара используется. Удаление невозможно');
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_GoodsUnit_del(
				GoodsUnit_id := :GoodsUnit_id
			)
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных единицы измерения товара для редактирования
	 */
	function loadGoodsUnitForm($data) {
		$params = array('GoodsUnit_id' => $data['GoodsUnit_id']);

		$query = "
			select
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Nick as \"GoodsUnit_Nick\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\",
				GU.GoodsUnit_Descr as \"GoodsUnit_Descr\",
				GU.Okei_id as \"Okei_id\"
			from v_GoodsUnit GU
			where GU.GoodsUnit_id = :GoodsUnit_id
			limit 1
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Импорт единиц измерения товара из справочников РЛС
	 */
	function importGoodsUnitFromRls($data) {
		$query = "
			select
				MU.FULLNAME as \"GoodsUnit_Name\",
				MU.SHORTNAME as \"GoodsUnit_Nick\",
				'единицы массы действующего вещества/лекарственного средства' as \"GoodsUnit_Descr\"
			from rls.MassUnits MU
			where
			rtrim(ltrim(MU.FULLNAME)) <> ''
			and lower(rtrim(ltrim(MU.FULLNAME))) not in (select lower(rtrim(ltrim(GoodsUnit_Name))) from v_GoodsUnit)
			union
			select
				CU.FULLNAME as \"GoodsUnit_Name\",
				CU.SHORTNAME as \"GoodsUnit_Nick\",
				'единицы объема лекарственного средства' as \"GoodsUnit_Descr\"
			from rls.CUBICUNITS CU
			where
			rtrim(ltrim(CU.FULLNAME)) <> ''
			and lower(rtrim(ltrim(CU.FULLNAME))) not in (select lower(rtrim(ltrim(GoodsUnit_Name))) from v_GoodsUnit)
			union
			select
				AU.FULLNAME as \"GoodsUnit_Name\",
				AU.SHORTNAME as \"GoodsUnit_Nick\",
				'единицы действия действующего вещества' as \"GoodsUnit_Descr\"
			from rls.ACTUNITS AU
			where
			rtrim(ltrim(AU.FULLNAME)) <> ''
			and lower(rtrim(ltrim(AU.FULLNAME))) not in (select lower(rtrim(ltrim(GoodsUnit_Name))) from v_GoodsUnit)
		";

		$GoodsUnitList = $this->queryResult($query);

		$this->beginTransaction();
		$insCount = 0;

		foreach($GoodsUnitList as $GoodsUnit) {
			$GoodsUnit['pmUser_id'] = $data['pmUser_id'];

			$resp = $this->saveGoodsUnit($GoodsUnit);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
			$insCount++;
		}

		$this->commitTransaction();

		return array(array('success' => true, 'insCount' => $insCount));
	}

    /**
     * Получение списка ед. изм. для комбобокса
     */
    function loadGoodsUnitCombo($data = null) {
    	$where = "";
    	if(!empty($data['where'])){
    		$where = $data['where'];
    	}
        $query = "
			select
				gu.GoodsUnit_id as \"GoodsUnit_id\",
				gu.GoodsUnit_Nick as \"GoodsUnit_Nick\",
				gu.GoodsUnit_Name as \"GoodsUnit_Name\"
			from
				v_GoodsUnit gu
			where (1=1) {$where}
			order by
				GU.GoodsUnit_Name;
		";

        $result = $this->db->query($query);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}