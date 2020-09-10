<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Diag - модель для работы со справочником диагназов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.12.2013
 */

class Diag_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function Diag() {
		parent::__construct();
	}

	/**
	 * Возвращает данные для дерева диагнозов
	 * @return bool
	 */
	function getDiagTreeData($data)
	{
		$params = array();
		$where = '';

		if ($data['node'] == 'root') {
			$where .= ' and D.Diag_pid is null';
		} else {
			$where .= ' and D.Diag_pid = :Diag_pid';
			$params['Diag_pid'] = $data['node'];
		}
		$query = "
			select
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				D.Diag_endDate as \"Diag_endDate\",
				D.DiagLevel_id as \"DiagLevel_id\",
				D.Diag_id as \"id\",
				(D.Diag_Code||' '||D.Diag_Name) as \"text\",
				(case when DL.DiagLevel_Code = 3 then 1 else 0 end) as \"leaf\"
			from
				v_Diag D
				inner join DiagLevel DL on DL.DiagLevel_id = D.DiagLevel_id
			where
				(1=1)
				{$where}
			order by
				D.Diag_Code
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$result = $result->result('array');
			leafToInt($result);
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для дерева диагнозов с поиском в дереве
	 * С клиента передаются переменные:
	 * $data['node'], $data['Diag_Code'], $data['Diag_Name'], $data['DiagLevel_id']
	 * @return array|bool
	 */
	function getDiagTreeSearchData($data)
	{
		$params = array();
		$where = '';
		// Глубина дерева, где 0 - глубина корня
		$depth = 4;
		// Сколько уровней текущему node до листа. Одновременно совпадает с номером поддерева листа.
		$N = $depth - $data['DiagLevel_id'] - 1;

		if ($data['node'] == 'root') {
			$where .= ' and D0.Diag_pid is null';
		} else {
			$where .= ' and D0.Diag_pid = :Diag_pid';
			$params['Diag_pid'] = $data['node'];
		}

		//if (!empty($data['Diag_Code'])) {
		//	$where .= " and D$N.Diag_Code like :Diag_Code+'%'";
		//	$params['Diag_Code'] = $data['Diag_Code'];
		//}
		//gaf #109848 
		//27032018
		if (getRegionNick() == "ufa" && !empty($data['Diag_Code'])) {
			if (strpos($data['Diag_Code'],'AND')>0){
				$arr = explode('AND',$data['Diag_Code']);
				$innerwhere = '';
				foreach ($arr as $key => $value) {
					if (empty($innerwhere)){
						$innerwhere .= " D$N.Diag_Code like '".$value."%'";
					}else{
						$innerwhere .= " or D$N.Diag_Code like '".$value."%'";
					}
				}
				$where .= " and (".$innerwhere.") ";
			}else{
				$where .= " and D$N.Diag_Code like :Diag_Code||'%'";
				$params['Diag_Code'] = $data['Diag_Code'];
			}
		}

		if (!empty($data['Diag_Date'])) {
			$where .= " and (D$N.Diag_begDate is null or D$N.Diag_begDate <= :Diag_Date)";
			$where .= " and (D$N.Diag_endDate is null or D$N.Diag_endDate >= :Diag_Date)";
			$params['Diag_Date'] = $data['Diag_Date'];
		}

		if (!empty($data['Diag_Name'])) {
			$where .= " and D$N.Diag_Name like '%'||:Diag_Name||'%'";
			$params['Diag_Name'] = $data['Diag_Name'];
		}

		$vzn = "";
		if ( (!empty($data['MorbusType_SysNick']) && $data['MorbusType_SysNick'] == 'vzn')
			|| (!empty($data['PersonRegisterType_SysNick']) && $data['PersonRegisterType_SysNick'] == 'nolos')
		) {
			$vzn = " left join lateral
				(
					SELECT
						string_agg(v_PersonRegisterType.PersonRegisterType_SysNick, ',') as PersonRegisterType_List
					FROM
						v_PersonRegisterDiag
						inner join v_PersonRegisterType on v_PersonRegisterType.PersonRegisterType_id = v_PersonRegisterDiag.PersonRegisterType_id
					WHERE
						v_PersonRegisterDiag.Diag_id = D$N.Diag_id
				) as PPT$N on true";

			$where .= " and PPT$N.PersonRegisterType_List like '%nolos%' and D$N.Diag_Code not like 'E75.5'";
		} else if (!empty($data['MorbusType_SysNick']) && $data['MorbusType_SysNick'] == 'crazy') {
			if (!empty($data['PersonRegisterType_SysNick'])) {
				if($data['PersonRegisterType_SysNick'] == 'narkoRegistry'){
					$where .= " and D$N.Diag_Code like 'F1%'";
				} else if($data['PersonRegisterType_SysNick'] == 'crazyRegistry'){
					$where .= " and D$N.Diag_Code not like 'F1%'";
				} else {
					$where .= " and PPT.PersonRegisterType_List like '%'||:PersonRegisterType_SysNick||'%'";
					$params['PersonRegisterType_SysNick'] = $data['PersonRegisterType_SysNick'];
				}
			}

			if (!empty($data['MorbusType_SysNick'])) {
				$where .= " and MT.MorbusType_List like '%'||:MorbusType_SysNick||'%'";
				$params['MorbusType_SysNick'] = $data['MorbusType_SysNick'];
			}

		} else {
			if (!empty($data['PersonRegisterType_SysNick'])) {
				$where .= " and PPT.PersonRegisterType_List like '%'||:PersonRegisterType_SysNick||'%'";
				$params['PersonRegisterType_SysNick'] = $data['PersonRegisterType_SysNick'];
			}

			if (!empty($data['MorbusType_SysNick'])) {
				$where .= " and MT.MorbusType_List like '%'||:MorbusType_SysNick||'%'";
				$params['MorbusType_SysNick'] = $data['MorbusType_SysNick'];
			}
		}

		if (!empty($data['registryType'])) {
			switch($data['registryType']) {
				case 'ExternalCause':
					$where .= " and (SUBSTRING(D$N.Diag_Code,1,1)) in ('V','W','X','Y')";
					break;
				case 'palliat':
					$where .= " and D$N.Diag_Code not like 'Z%'";
					break;
				case 'BSKRegistry':
					$where .= " and D$N.Diag_Code like 'I%'";
					break;
			}
		}

		$diagFilter = getAccessRightsDiagFilter("D".$N.".Diag_Code");
		if ( !empty($diagFilter) ) {
			$where .= " and ($diagFilter)";
		}

		$query = "
			select
				D0.Diag_id as \"Diag_id\",
				D0.Diag_Code as \"Diag_Code\",
				D0.Diag_Name as \"Diag_Name\",
				D0.DiagLevel_id as \"DiagLevel_id\",
				D0.Diag_id as \"id\",
				(D0.Diag_Code||' '||D0.Diag_Name) as \"text\",
				(case when DL.DiagLevel_Code = $depth then 1 else 0 end) as \"leaf\",
				IsOms.YesNo_Code as \"DiagFinance_IsOms\"
			from
				v_Diag D0
				";
		// В зависимости от того, на каком уровне дерева находимся, подключим нужное количество поддеревьев
		for ($i=1; $i<=$N; $i++) {
			$query.= "inner join v_Diag D$i on D". ($i-1) .".Diag_id = D$i.Diag_pid ";
		}

		$query.= "inner join DiagLevel DL on DL.DiagLevel_id = D0.DiagLevel_id
				left join lateral
				(
					SELECT
						string_agg(v_PersonRegisterType.PersonRegisterType_SysNick, ',') as PersonRegisterType_List
					FROM
						v_PersonRegisterDiag
						inner join v_PersonRegisterType on v_PersonRegisterType.PersonRegisterType_id = v_PersonRegisterDiag.PersonRegisterType_id
					WHERE
						v_PersonRegisterDiag.Diag_id = D$N.Diag_id
				) as PPT on true
				{$vzn}
				left join lateral
				(
					SELECT 
						string_agg(v_MorbusType.MorbusType_SysNick, ',') as MorbusType_List
					FROM v_MorbusDiag
					inner join v_MorbusType on v_MorbusType.MorbusType_id = v_MorbusDiag.MorbusType_id
					WHERE
						v_MorbusDiag.Diag_id = D$N.Diag_id
				) as MT on true
				left join v_DiagFinance df on df.Diag_id = D0.Diag_id
				left join YesNo IsOms on IsOms.YesNo_id = df.DiagFinance_IsOms
			where
				(1=1) {$where}
			group by 
				D0.Diag_id,
				D0.Diag_Code,
				D0.Diag_Name,
				D0.DiagLevel_id,
				DL.DiagLevel_Code,
				IsOms.YesNo_Code
			order by
				D0.Diag_Code
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$result = $result->result('array');
			leafToInt($result);
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список диагназов
	 * @return bool
	 */
	function loadDiagGrid($data)
	{
		$where = $wherePid = '(1=1)';
		$params = array();
		$options = $this->getSessionParams();
		$mode = '';
		$query = '';


		if ( ! empty($data['Diag_pid']) )
		{
			$params = array(
				'Diag_pid' => $data['Diag_pid']);
		}

		if (!empty($data['Diag_Code'])) {
			$where .= " and D.Diag_Code like :Diag_Code||'%'";
			$params['Diag_Code'] = $data['Diag_Code'];
		}

		if (!empty($data['Diag_Name'])) {
			$where .= " and D.Diag_Name like '%'||:Diag_Name||'%'";
			$params['Diag_Name'] = $data['Diag_Name'];
		}
		if ( ! empty($data['query']) )
		{
			$where .= " and D.Diag_Code || ' ' || D.Diag_Name like '%'||:query||'%'";
			$params['query'] = $data['query'];
		}

		if ( ! empty($data['mode']) )
		{
			if ($data['mode'] === 'last')
			{
				$mode = 'last';
			}
		}



		$searchByPid = "
			with RECURSIVE Rec(Diag_id, Diag_pid, Diag_Code, Diag_Name, DiagLevel_id, Diag_endDate)
			as
			(
				select D.Diag_id, D.Diag_pid, D.Diag_Code, D.Diag_Name, D.DiagLevel_id, D.Diag_endDate
				from v_Diag D
				where
					D.Diag_pid = :Diag_pid
				union ALL
				select D.Diag_id, D.Diag_pid, D.Diag_Code, D.Diag_Name, D.DiagLevel_id, D.Diag_endDate
				from v_Diag D
					JOIN Rec R  on d.Diag_pid = R.Diag_id
			)
			select
				D.Diag_id as \"Diag_id\",
				D.Diag_pid as \"Diag_pid\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				D.DiagLevel_id as \"DiagLevel_id\",
				to_char(D.Diag_endDate, 'dd.mm.yyyy') as \"Diag_endDate\",
				DL.DiagLevel_Code as \"DiagLevel_Code\",
				DL.DiagLevel_Name as \"DiagLevel_Name\"
			from
				Rec D
				inner join DiagLevel DL on DL.DiagLevel_id = D.DiagLevel_id
			where
				{$where}
			order by
				D.Diag_Code
		";

		$searchByQuery = "
			select
				D.Diag_id as \"Diag_id\",
				D.Diag_pid as \"Diag_pid\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				D.DiagLevel_id as \"DiagLevel_id\",
				to_char(D.Diag_endDate, 'dd.mm.yyyy') as \"Diag_endDate\",
				DL.DiagLevel_Code as \"DiagLevel_Code\",
				DL.DiagLevel_Name as \"DiagLevel_Name\"
			from
				v_Diag D
			inner join 
				DiagLevel DL on DL.DiagLevel_id = D.DiagLevel_id
			where
				D.DiagLevel_id = 4 and
				{$where}
			order by
				D.Diag_Code
			limit 50
		";


		$lastDiags = "
			with mv as (
				select
					{$options['CurMedStaffFact_id']} as msf_id
			), EU as (
			select
			Diag_id,
			EvnUsluga_updDT as DT
			from v_EvnUsluga
			where MedStaffFact_id = (select msf_id from mv)
			and Diag_id IS NOT NULL
			order by DT desc
			limit 20),
			
			EPL as (
			select
			Diag_id,
			EvnPL_updDT as DT
			from v_EvnPl
			where MedPersonal_id = (
				SELECT MedPersonal_id
				FROM v_MedStaffFact
				where MedStaffFact_id = (select msf_id from mv)
			)
			and Diag_id IS NOT NULL
			order by DT desc
			limit 20),
			
			EVPL as (
			select
			Diag_id,
			EvnVizitPL_updDT as DT
			from v_EvnVizitPL
			where MedStaffFact_id = (select msf_id from mv)
			and Diag_id IS NOT NULL
			order by DT desc
			limit 20),
			
			EPS as (
			select
			Diag_id,
			EvnPS_updDT as DT
			from v_EvnPS
			where MedStaffFact_id = (select msf_id from mv)
			and Diag_id IS NOT NULL
			order by DT desc
			limit 20),
			
			ES as (
			select
			Diag_id,
			EvnSection_updDT as DT
			from v_EvnSection
			where MedStaffFact_id = (select msf_id from mv)
			and Diag_id IS NOT NULL
			order by DT desc
			limit 20),
			
			
			Diags as (
				SELECT * from EU
				
				UNION ALL
				
				SELECT * from EPL
				
				UNION ALL
				
				SELECT * from EVPL
				
				UNION ALL
				
				SELECT * from EPS
				
				UNION ALL
				
				SELECT * from ES
				)
				
			Select
			D.Diag_id as \"Diag_id\",
			D.Diag_pid as \"Diag_pid\",
			D.Diag_Code as \"Diag_Code\",
			D.Diag_Name as \"Diag_Name\",
			D.DiagLevel_id as \"DiagLevel_id\",
			to_char(D.Diag_endDate, 'dd.mm.yyyy') as \"Diag_endDate\",
			DL.DiagLevel_Code as \"DiagLevel_Code\",
			DL.DiagLevel_Name as \"DiagLevel_Name\",
			DT as \"DT\"
			from Diags 
			inner join v_Diag D on D.Diag_id = Diags.Diag_id
			inner join DiagLevel DL on DL.DiagLevel_id = D.DiagLevel_id
			
			order by DT desc
		";

		switch ($mode)
		{
			case 'last':
				$query = $lastDiags;
				break;
			default:
				$query = isset($params['Diag_pid']) ? $searchByPid : $searchByQuery;
				break;
		}

		$result = $this->db->query( $query, $params);


		if ( ! is_object($result) ) return false; else $result = $result->result('array');



		if ($mode = 'last')
		{
			$diags = array();

			/**
			 * Фильтруем повторяющиеся диагнозы
			 */
			function filterFn(&$item, $key, &$diags)
			{
				if ( ! in_array($item['Diag_id'], $diags) )
				{
					$diags[] = $item['Diag_id'];
				} else
				{
					$item = null;
				}
			}

			array_walk($result, 'filterFn', $diags);

			foreach ($result as $key => $item)
			{
				if ( ! is_array($item))
				{
					unset($result[$key]);
				}
			}
		}
		return ['data' => $result];
	}
	/**
	 * @param array $data
	 * @return array
	 */
	function checkIsOMS($data) {
		$response = array('Error_Msg' => '');

		$warnType = 'Error_Msg';
		if (getRegionNick() == 'kareliya') {
			$warnType = 'Alert_Msg';
		}

		$query = "
			select
				IsOms.YesNo_Code as \"DiagFinance_IsOms\",
				IsAlien.YesNo_Code as \"DiagFinance_IsAlien\",
				df.Sex_id as \"Diag_Sex\",
				a.PersonAgeGroup_Code as \"PersonAgeGroup_Code\",
				p.OmsSprTerr_Code as \"OmsSprTerr_Code\",
				p.Sex_id as \"Sex_id\",
				dbo.Age2(p.Person_BirthDay, :EvnSection_setDate) as \"Age\",
				pt.PayType_SysNick as \"PayType_SysNick\"
			from
				v_DiagFinance df
				left join PersonAgeGroup a on a.PersonAgeGroup_id = df.PersonAgeGroup_id
				left join YesNo IsAlien on IsAlien.YesNo_id = df.DiagFinance_IsAlien
				left join YesNo IsOms on IsOms.YesNo_id = df.DiagFinance_IsOms
				left join lateral (
					select
						ost.OmsSprTerr_Code,
						ps.Sex_id,
						ps.Person_BirthDay
					from
						v_PersonState ps
						left join v_Polis pls on pls.Polis_id = ps.Polis_id
						left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
					where ps.Person_id = :Person_id
					limit 1
				) p on true
				left join v_PayType pt on pt.PayType_id = :PayType_id
			where
				df.Diag_id = :Diag_id
			limit 1
		";
		$queryParams = array(
			'Diag_id' => $data['Diag_id'],
			'PayType_id' => $data['PayType_id'],
			'Person_id' => $data['Person_id'],
			'EvnSection_setDate' => $data['EvnSection_setDate']
		);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array('Error_Msg' => "Ошибка при выполнении запроса к базе данных");
		}

		$Oms = $result->result('array');

		if ( is_array($Oms) && count($Oms) > 0 ) {
			if ( $Oms[0]['PayType_SysNick'] == 'oms' && $Oms[0]['DiagFinance_IsOms'] === 0 ) {
				if (getRegionNick() == 'kareliya') {
					// если диагноз связан с КСГ 297, то разрешить выбирать даже если у этого диагноза DiagFinance_IsOms = 1.
					$resp_mouc = $this->queryResult("
						select
							mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\"
						from
							v_MesOldUslugaComplex mouc
							inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
						where
							mouc.Diag_id = :Diag_id
							and mo.Mes_Code = '297'
							and mouc.MesOldUslugaComplex_begDT <= :EvnSection_setDate
							and (coalesce(mouc.MesOldUslugaComplex_endDT,  :EvnSection_setDate) >= :EvnSection_setDate)
						limit 1
					", array(
						'Diag_id' => $data['Diag_id'],
						'EvnSection_setDate' => $data['EvnSection_setDate']
					));
					if (empty($resp_mouc[0]['MesOldUslugaComplex_id'])) {
						return array($warnType => "Диагноз не оплачивается по ОМС");
					}
				} else {
					return array($warnType => "Диагноз не оплачивается по ОМС");
				}
			}

			if ( $Oms[0]['Age'] < 0 ) {
				return array('Error_Msg' => "Ошибка при определении возраста пациента");
			}

			if ( empty($Oms[0]['Sex_id']) || !in_array($Oms[0]['Sex_id'], array(1, 2)) ) {
				return array('Error_Msg' => "Не указан пол пациента");
			}

			if (getRegionNick() != 'kareliya') { // для Карелии такого условия не было в проверке на клиенте.
				if (!empty($Oms[0]['PersonAgeGroup_Code'])) {
					if ($Oms[0]['Age'] >= 18 && $Oms[0]['PersonAgeGroup_Code'] == 2) {
						return array($warnType => "Диагноз не оплачивается для взрослых");
					} else if ($Oms[0]['Age'] < 18 && $Oms[0]['PersonAgeGroup_Code'] == 1) {
						return array($warnType => "Диагноз не оплачивается для детей");
					}
				}
			}

			if ( !empty($Oms[0]['Diag_Sex']) ) {
				if ( $Oms[0]['Sex_id'] == 1 && $Oms[0]['Diag_Sex'] == 2 ) {
					return array($warnType => "Диагноз не соответствует полу пациента");
				}
				else if ( $Oms[0]['Sex_id'] == 2 && $Oms[0]['Diag_Sex'] == 1 ) {
					return array($warnType => "Диагноз не соответствует полу пациента");
				}
			}

			if ( 'ufa' == $data['session']['region']['nick'] && $Oms[0]['OmsSprTerr_Code'] != 61 && $Oms[0]['DiagFinance_IsAlien'] === 0 ) {
				if ( $data['EvnSection_IsAdultEscort'] == 2 ) {
					$query = "
						SELECT
							coalesce(OmsSprTerr.OmsSprTerr_Code, 0) as \"OmsSprTerr_Code\"
						FROM v_PersonDeputy PDe
							inner join v_PersonState PS on PS.Person_id = PDe.Person_pid
							left join Polis on Polis.Polis_id = PS.Polis_id
							left join OmsSprTerr on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id
						WHERE  (1=1) and PDe.Person_id = :Person_id
						limit 1
					";

					$parent_terrCode = $this->getFirstResultFromQuery($query, array('Person_id' => $data['Person_id']));
					if ($parent_terrCode != 61) {
						return array($warnType => "Диагноз не оплачивается для пациентов, застрахованных не в РБ");
					}
				}
				else {
					return array($warnType => "Диагноз не оплачивается для пациентов, застрахованных не в РБ");
				}
			}
		}

		return $response;
	}

	/**
	 * Получение diag_id по параметрам
	 */
	function getDiagidByFilter($data) {
		$filter = [];
		if ($data['Diag_Code']) {
			$filter[] = " and Diag_Code ILIKE '%' || :Diag_Code || '%'";
		}
		if ($data['DiagLevel_id']) {
			$filter[] = " and DiagLevel_id = :DiagLevel_id";
		}

		$filter = implode('
		', $filter);

		if (empty($filter))
			return null;
		return $this->getFirstResultFromQuery("
			select
				Diag_id as \"Diag_id\"
			from v_Diag
			where (1=1)
			{$filter}
			limit 1
		", $data);
	}

	/**
	 * Получение записи по ID
	 */
	function getDiagById ($params) {
		return $this->getFirstRowFromQuery("
			select
				Diag_id as \"Diag_id\",
				Diag_Code as \"Diag_Code\",
				Diag_Name as \"Diag_Name\"
			from v_Diag
			where Diag_id = :Diag_id
			limit 1
		", $params);
	}
}
