<?php
require_once(APPPATH.'models/UslugaComplex_model.php');

/**
 * @property Usluga_model Usluga_model
 */
class Vologda_UslugaComplex_model extends UslugaComplex_model {
	/**
	 *    Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function loadForSelect($data) {
		$queryParams = array();
		$selectIsMes = 'null as UslugaComplex_IsByMes';
		$joinMes = '';
		$datefilter3 = "";

		if (!empty($data['EvnUsluga_pid'])) { // тариф на дату последнего посещения в ТАП.
			$filter_check = "";
			$checkParams = array(
				'EvnUsluga_pid' => $data['EvnUsluga_pid']
			);
			if (!empty($data['UslugaComplexTariff_Date'])) {
				$filter_check .= " and ev2.EvnVizit_setDate >= :UslugaComplexTariff_Date";
				$checkParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
			}
			$resp = $this->queryResult("
				select top 1
					convert(varchar(10), ev2.EvnVizit_setDate, 120) as EvnVizit_setDate
				from
					v_EvnVizit ev (nolock)
					inner join v_EvnVizit ev2 (nolock) on ev2.EvnVizit_pid = ev.EvnVizit_pid and ev2.EvnVizit_id <> ev.EvnVizit_id
				where
					ev.EvnVizit_id = :EvnUsluga_pid
					{$filter_check}
				order by
					ev2.EvnVizit_setDate desc		
			", $checkParams);

			if (!empty($resp[0]['EvnVizit_setDate'])) {
				$data['UslugaComplexTariff_Date'] = $resp[0]['EvnVizit_setDate'];
			}
		}

		if (!empty($data['Mes_id']) && isset($data['UslugaComplex_id'])) {
			$queryParams['Mes_id'] = $data['Mes_id'];
			$selectIsMes = 'case when mu.Mes_id is null then 1 else 2 end as UslugaComplex_IsByMes';
			$datefilter1 = "";
			$datefilter2 = "";

			if (!empty($data['UslugaComplexTariff_Date'])) {
				$queryParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
				$datefilter1 = "
					and UslugaComplexTariff_begDate <= :UslugaComplexTariff_Date
					and (UslugaComplexTariff_endDate >= :UslugaComplexTariff_Date or UslugaComplexTariff_endDate is null)
				";
				$datefilter2 = "
					and ISNULL(muc.MesOldUslugaComplex_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(muc.MesOldUslugaComplex_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
				$datefilter3 = "
					and ISNULL(UC.UslugaComplex_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(UC.UslugaComplex_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
			}
			$joinMes = "outer apply (
                select top 1 muc.Mes_id
                from v_MesOldUslugaComplex muc with (nolock)
                where muc.UslugaComplex_id = UC.UslugaComplex_2011id
                    and muc.Mes_id = :Mes_id
                    and exists (
                        select top 1 UslugaComplexTariff_id
                        from v_UslugaComplexTariff with (nolock)
                        where Lpu_id is null
                            and UslugaComplex_id = UC.UslugaComplex_id
                            {$datefilter1}
                    )
					{$datefilter2}
            ) muc";
		}
		$evnFilter = '';

		if ( !empty($data['doNotIncludeEvnUslugaDid']) ) {
			if (!empty($data['EvnUsluga_rid'])) {
				$queryParams['EvnUsluga_rid'] = $data['EvnUsluga_rid'];
				$evnFilter = ' and not exists (
					select top 1 eu.EvnUsluga_id
					from EvnUsluga eu with (nolock)
					inner join Evn (nolock) on Evn.Evn_id = eu.EvnUsluga_id and Evn.Evn_deleted = 1
					where eu.EvnUsluga_rid = :EvnUsluga_rid
						and eu.UslugaComplex_id = UC.UslugaComplex_id
				)';
			} else if (!empty($data['EvnUsluga_pid'])) {
				$edpjoin = "";
				$edpfilter = "";
				if (!empty($data['EvnDiagPLStom_id'])) {
					$queryParams['EvnDiagPLStom_id'] = $data['EvnDiagPLStom_id'];
					$edpjoin = " inner join EvnUslugaStom eus (nolock) on eus.EvnUslugaStom_id = eu.EvnUsluga_id";
					$edpfilter = " and eus.EvnDiagPLStom_id = :EvnDiagPLStom_id";
				}
				else {
					$edpfilter = " and eu.EvnUsluga_pid = :EvnUsluga_pid";
				}
				$queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
				$evnFilter = " and not exists (
					select top 1 eu.EvnUsluga_id
					from EvnUsluga eu with (nolock)
					inner join Evn (nolock) on Evn.Evn_id = eu.EvnUsluga_id and Evn.Evn_deleted = 1
					{$edpjoin}
					where
						eu.UslugaComplex_id = UC.UslugaComplex_id
						{$edpfilter}
				)";
			}
		}

		if (!empty($data['UslugaComplex_id'])) {
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			$sql = "
            select
                UC.UslugaComplex_id,
                UC.UslugaComplex_Code,
                UC.UslugaComplex_Name,
                {$selectIsMes},
                1 as EvnUsluga_Kolvo,
                0 as UslugaComplexTariff_Count,
                null as UslugaComplexTariff_id,
                0 as UslugaComplexTariff_Tariff,
                0 as UslugaComplexTariff_UED,
                0 as UslugaComplexTariff_UEM
            from v_UslugaComplexComposition UCC with (nolock)
            inner join v_UslugaComplex UC with (nolock) on UCC.UslugaComplex_id = UC.UslugaComplex_id
            {$joinMes}
            where UCC.UslugaComplex_pid = :UslugaComplex_id
            {$evnFilter}
            {$datefilter3}
            order by UC.UslugaComplex_Name
            ";
		} else if (!empty($data['Mes_id']) && !empty($data['EvnUsluga_pid'])) {
			$queryParams['Mes_id'] = $data['Mes_id'];
			$queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
			$datefilter1 = "";
			$datefilter2 = "";
			$datefilter3 = "";
			if (!empty($data['UslugaComplexTariff_Date'])) {
				$queryParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
				$datefilter1 = "
					and UslugaComplexTariff_begDate <= :UslugaComplexTariff_Date
					and (UslugaComplexTariff_endDate >= :UslugaComplexTariff_Date or UslugaComplexTariff_endDate is null)
				";
				$datefilter2 = "
					and ISNULL(muc.MesOldUslugaComplex_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(muc.MesOldUslugaComplex_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
				$datefilter3 = "
					and ISNULL(UC.UslugaComplex_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(UC.UslugaComplex_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
			}

			$joinuc = "inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_2011id = muc.UslugaComplex_id";
			if (!empty($data['EvnDiagPLStom_id'])) {
				// для КСГ надо напрямую джойнить
				$joinuc = "inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = muc.UslugaComplex_id";
			}

			$sql = "
            select
                UC.UslugaComplex_id,
                UC.UslugaComplex_Code,
                UC.UslugaComplex_Name,
                2 as UslugaComplex_IsByMes,
                1 as EvnUsluga_Kolvo,
                0 as UslugaComplexTariff_Count,
                null as UslugaComplexTariff_id,
                0 as UslugaComplexTariff_Tariff,
                0 as UslugaComplexTariff_UED,
                0 as UslugaComplexTariff_UEM,
				'' as MesUsluga_IsNeedUsluga
            from v_MesOldUslugaComplex muc with (nolock)
            inner join v_MesOld m with (nolock) on m.Mes_id = muc.Mes_id
            {$joinuc}
            where muc.Mes_id = :Mes_id
                and (m.MesType_id = 7 or exists (  -- данная фильтрация по тарифу не нужна для КСГ
                    select top 1 UslugaComplexTariff_id
                    from v_UslugaComplexTariff with (nolock)
                    where Lpu_id is null
                        and UslugaComplex_id = UC.UslugaComplex_id
                        and UslugaComplexTariff_UED is null
                        {$datefilter1}
                ))
                {$evnFilter}
                {$datefilter2}
                {$datefilter3}
            order by uc.UslugaComplex_Name
            ";
		} else {
			return false;
		}
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp = $result->result('array');
		if (empty($tmp)) {
			return array();
		}
		if ( empty($data['LpuSection_id']) || empty($data['PayType_id'])
			|| empty($data['Person_id']) || empty($data['UslugaComplexTariff_Date'])
		) {
			return $tmp;
		}
		// считаем тарифы
		$usluga_complex_list = array();
		$usluga_list = array();
		foreach ($tmp as $row) {
			$id = $row['UslugaComplex_id'];
			$usluga_complex_list[] = $id;
			$usluga_list[$id] = $row;
		}
		$data['UslugaComplex_id'] = null;
		$data['in_UslugaComplex_list'] = implode(', ', $usluga_complex_list);
		$this->load->model('Usluga_model', 'Usluga_model');
		$tariffList = $this->Usluga_model->loadUslugaComplexTariffList($data);
		if (!is_array($tariffList)) {
			return false;
		}
		foreach ($tariffList as $row) {
			$id = $row['UslugaComplex_id'];
			$usluga_list[$id]['UslugaComplexTariff_Count']++;
			$usluga_list[$id]['UslugaComplexTariff_id'] = $row['UslugaComplexTariff_id'];
			$usluga_list[$id]['UslugaComplexTariff_UED'] = $row['UslugaComplexTariff_UED'];
			$usluga_list[$id]['UslugaComplexTariff_UEM'] = $row['UslugaComplexTariff_UEM'];
			$usluga_list[$id]['UslugaComplexTariff_Tariff'] = $row['UslugaComplexTariff_Tariff'];
		}
		$response = array();
		foreach ($usluga_list as $row) {
			if ($row['UslugaComplexTariff_Count'] > 1) {
				$row['UslugaComplexTariff_id'] = null;
				$row['UslugaComplexTariff_UED'] = 0;
				$row['UslugaComplexTariff_UEM'] = 0;
				$row['UslugaComplexTariff_Tariff'] = 0;
			}
			$response[] = $row;
		}
		return $response;
	}
}