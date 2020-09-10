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

class GoodsUnit_model extends swModel {
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
			$filters .= " and GU.GoodsUnit_Name like '%'+:GoodsUnit_Name+'%'";
			$params['GoodsUnit_Name'] = $data['GoodsUnit_Name'];
		}

		$query = "
			select
				-- select
				GU.GoodsUnit_id,
				GU.GoodsUnit_Nick,
				GU.GoodsUnit_Name,
				O.Okei_id,
				O.Okei_Code,
				O.Okei_Name
				-- end select
			from
				-- from
				v_GoodsUnit GU with(nolock)
				left join v_Okei O with(nolock) on O.Okei_id = GU.Okei_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :GoodsUnit_id;
			exec {$procedure}
				@GoodsUnit_id = @Res output,
				@GoodsUnit_Nick = :GoodsUnit_Nick,
				@GoodsUnit_Name = :GoodsUnit_Name,
				@GoodsUnit_Descr = :GoodsUnit_Descr,
				@Okei_id = :Okei_id,
				@Org_id = null,
				@Region_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as GoodsUnit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1 Org_id
			from v_GoodsUnit with(nolock)
			where GoodsUnit_id = :GoodsUnit_id
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
			select top 1 count(GoodsPackCount_id)
			from v_GoodsPackCount with(nolock)
			where GoodsUnit_id = :GoodsUnit_id
		", $params);
		if ($count === false) {
			return $this->createError('','Ошибка при определения использования единицы измерения товара');
		}
		if ($count > 0) {
			return $this->createError('','Единица измерения товара используется. Удаление невозможно');
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_GoodsUnit_del
				@GoodsUnit_id = :GoodsUnit_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных единицы измерения товара для редактирования
	 */
	function loadGoodsUnitForm($data) {
		$params = array('GoodsUnit_id' => $data['GoodsUnit_id']);

		$query = "
			select top 1
				GU.GoodsUnit_id,
				GU.GoodsUnit_Nick,
				GU.GoodsUnit_Name,
				GU.GoodsUnit_Descr,
				GU.Okei_id
			from v_GoodsUnit GU with(nolock)
			where GU.GoodsUnit_id = :GoodsUnit_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Импорт единиц измерения товара из справочников РЛС
	 */
	function importGoodsUnitFromRls($data) {
		$query = "
			select
				MU.FULLNAME as GoodsUnit_Name,
				MU.SHORTNAME as GoodsUnit_Nick,
				'единицы массы действующего вещества/лекарственного средства' as GoodsUnit_Descr
			from rls.MassUnits MU with(nolock)
			where
			rtrim(ltrim(MU.FULLNAME)) <> ''
			and lower(rtrim(ltrim(MU.FULLNAME))) not in (select lower(rtrim(ltrim(GoodsUnit_Name))) from v_GoodsUnit with(nolock))
			union
			select
				CU.FULLNAME as GoodsUnit_Name,
				CU.SHORTNAME as GoodsUnit_Nick,
				'единицы объема лекарственного средства' as GoodsUnit_Descr
			from rls.CUBICUNITS CU with(nolock)
			where
			rtrim(ltrim(CU.FULLNAME)) <> ''
			and lower(rtrim(ltrim(CU.FULLNAME))) not in (select lower(rtrim(ltrim(GoodsUnit_Name))) from v_GoodsUnit with(nolock))
			union
			select
				AU.FULLNAME as GoodsUnit_Name,
				AU.SHORTNAME as GoodsUnit_Nick,
				'единицы действия действующего вещества' as GoodsUnit_Descr
			from rls.ACTUNITS AU with(nolock)
			where
			rtrim(ltrim(AU.FULLNAME)) <> ''
			and lower(rtrim(ltrim(AU.FULLNAME))) not in (select lower(rtrim(ltrim(GoodsUnit_Name))) from v_GoodsUnit with(nolock))
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
				gu.GoodsUnit_id,
				gu.GoodsUnit_Nick,
				gu.GoodsUnit_Name
			from
				v_GoodsUnit gu with(nolock)
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