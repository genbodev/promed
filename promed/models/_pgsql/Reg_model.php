<?php

/**
 * Reg - модель для работы регистратуры
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      19.03.2012
 */
class Reg_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->model('LpuIndividualPeriod_model', 'lipmodel');
	}

	/**
	 * Получение списка подразделений для дерева структуры при ведении расписания
	 */
	function GetLpuUnitNodeList( $data ) {
		$params = array(
			'Lpu_id' => $data['object_id']
		);
		$filter = 'lu.Lpu_id = :Lpu_id';
		$from = '';
		$with = '';

		if ( isset($data['LpuUnit_id']) ) {
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
			$filter .= ' and lu.LpuUnit_id = :LpuUnit_id';
		}
		
		if (!empty($data['filterByArm'])) {
			if ($data['filterByArm'] == 'priem') {
				$filter .= ' and lut.LpuUnitType_SysNick in (\'stac\', \'dstac\', \'pstac\')';
				$from .= ' left join v_LpuUnitType lut on lu.LpuUnitType_id = lut.LpuUnitType_id';
				
				$params['UserLpuSection_id'] = $data['UserLpuSection_id'];
				$with = '
					with LpuSectionList as (
						select distinct
							ls.LpuUnit_id as LpuUnit_id
						from
							v_LpuSectionService lss
							inner join v_LpuSection ls on ls.LpuSection_id = lss.LpuSection_did
						where lss.LpuSection_id = :UserLpuSection_id
					)
				';
				$from .= ' inner join LpuSectionList lsl on lsl.LpuUnit_id = lu.LpuUnit_id';
			}
			if ($data['filterByArm'] == 'stac') {
				$params['UserMedStaffFact_id'] = $data['UserMedStaffFact_id'];
				$from .= ' inner join v_MedStaffFact msf on msf.LpuUnit_id = lu.LpuUnit_id and msf.MedStaffFact_id = :UserMedStaffFact_id';
			}
		}
		
		$sql = '
			'.$with.'
			select
				lu.LpuBuilding_id as "LpuBuilding_id",
				lu.LpuUnit_id as "LpuUnit_id",
				lu.LpuUnit_Name as "LpuUnit_Name",
				(Select count(*) from LpuSection where lu.LpuUnit_id = LpuSection.LpuUnit_id) +
				(Select count(*) from v_MedService ms where ms.LpuUnit_id = lu.LpuUnit_id)  as "leafcount",
				lu.LpuUnitType_id as "LpuUnitType_id",
				case when lu.LpuUnit_begDate < dbo.tzGetDate() and (lu.LpuUnit_endDate is null or cast(lu.LpuUnit_endDate as date) > dbo.tzGetDate()) then 0 else 1 end as "isClosed"
			from
				v_LpuUnit lu
				'. $from .'
			where ' . $filter . '
			order by lu.LpuUnit_Code';
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка отделений для дерева структуры при ведении расписания
	 */
	function GetLpuSectionNodeList( $data ) {
		$from = '';
		$with = '';
		if (!empty($data['filterByArm'])) {
			if ($data['filterByArm'] == 'priem') {
				$with = '
					with LpuSectionList as (
						select distinct
							lss.LpuSection_did
						from
							v_LpuSectionService lss
						where lss.LpuSection_id = '.$data['UserLpuSection_id'].'
					)
				';
				$from .= ' inner join LpuSectionList lsl on lsl.LpuSection_did = ls.LpuSection_id';
			}
			if ($data['filterByArm'] == 'stac') {
				$from .= ' inner join v_MedStaffFact msf on msf.LpuSection_id = ls.LpuSection_id and msf.MedStaffFact_id = '.$data['UserMedStaffFact_id'];
			}
		}
		
		$sql = "
			$with
			select
				ls.LpuUnit_id as \"LpuUnit_id\",
				ls.LpuSection_id as \"LpuSection_id\",
				(rtrim(LpuSection_Code) || '. ' || rtrim(LpuSection_Name)) as \"LpuSection_Name\",
				(Select count(*) from LpuSection LS1 where LS1.LpuSection_pid = ls.LpuSection_id) +
				(Select count(*) from v_MedService ms where ms.LpuSection_id = ls.LpuSection_id) as \"leafcount\",
				case when ls.LpuSection_setDate < dbo.tzGetDate() and (ls.LpuSection_disDate is null or cast(ls.LpuSection_disDate as date) > dbo.tzGetDate()) then 0 else 1 end as \"isClosed\",
				LpuUnitType_id as \"LpuUnitType_id\"
			from v_LpuSection ls
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			$from
			where ls.LpuUnit_id = " . $data['object_id'] . " and ls.LpuSection_pid is null
			order by ls.LpuSection_Code";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка подотделений для дерева структуры при ведении расписания
	 */
	function GetLpuSectionPidNodeList( $data ) {
		$sql = "select
				ls.LpuSection_pid as \"LpuSection_pid\",
				ls.LpuSection_id as \"LpuSection_id\",
				(rtrim(LpuSection_Code) || '. ' || rtrim(LpuSection_Name)) as \"LpuSection_Name\",
				case when ls.LpuSection_setDate < dbo.tzGetDate() and (ls.LpuSection_disDate is null or cast(ls.LpuSection_disDate as date) > dbo.tzGetDate()) then 0 else 1 end as \"isClosed\",
				LpuUnitType_id as \"LpuUnitType_id\"
			from v_LpuSection ls
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			where LpuSection_pid = " . $data['object_id'] . "
			order by ls.LpuSection_Code";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка служб
	 */
	function GetMedServiceNodeList( $data ) {
		$params = array();
		$filter = 'Lpu_id = :Lpu_id';
		$params['Lpu_id'] = $data['Lpu_id'];

		if ( $data['object'] == 'MedServices' ) {
			$filter .= ' and LpuUnit_id is null and LpuSection_id is null';
		} else if ( $data['object'] == 'LpuUnit' ) {
			$filter .= ' and LpuUnit_id = :LpuUnit_id and LpuSection_id is null';
			$params['LpuUnit_id'] = $data['object_id'];
		} else if ( $data['object'] == 'LpuSection' ) {
			$filter .= ' and LpuSection_id = :LpuSection_id';
			$params['LpuSection_id'] = $data['object_id'];
		}


        if ( !empty($data['MedServiceType_SysNick']) ) {
            $filter .= ' and mst.MedServiceType_SysNick = :MedServiceType_SysNick';
            $params['MedServiceType_SysNick'] = $data['MedServiceType_SysNick'];
        }
		//$filter .= " and (MedService_endDT is null or MedService_endDT > dbo.tzGetDate())";

		$sql = "
			select 
				ms.MedService_id as \"MedService_id\",
				ms.MedService_Name as \"MedService_Name\",
				mst.MedServiceType_id as \"MedServiceType_id\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				ms.LpuBuilding_id as \"LpuBuilding_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				ms.LpuUnit_id as \"LpuUnit_id\",
				ms.Lpu_id as \"Lpu_id\",
				to_char(ms.MedService_endDT, 'dd.mm.yyyy') as \"MedService_endDT\",
				case when ms.MedService_begDT < dbo.tzGetDate() and (ms.MedService_endDT is null or cast(ms.MedService_endDT as date) > dbo.tzGetDate()) then 0 else 1 end as \"isClosed\",
				0 as \"leafcount\"
			from
				v_MedService ms
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where
				{$filter}
			order by
				ms.MedService_Name
		";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * @comment
     */
    function getResourceListForSchedule( $data ) {
		$params = array();
		$filter_arr = array("1=1");
		$join_arr = array();
		$order = "res.Resource_id";

		if (!empty($data['UslugaComplexMedService_id'])) {
			$filter_arr[] = "exists(
				select UCMS.UslugaComplexMedService_id
				from v_UslugaComplexMedService UCMS
				inner join v_UslugaComplexResource UCR on UCR.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
				where UCR.Resource_id = res.Resource_id and UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id
			)";
			$params['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
		}

		if (!empty($data['UslugaComplex_ids'])) {
			$count = count($data['UslugaComplex_ids']);
			$filter_arr[] = "(select
					count(UCMS.UslugaComplexMedService_id)
				from
					v_UslugaComplexMedService UCMS
					inner join v_UslugaComplexResource UCR on UCR.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
				where
					UCR.Resource_id = res.Resource_id
					and UCMS.UslugaComplex_id IN ('".implode("','", $data['UslugaComplex_ids'])."')
				) = {$count}
			";
			$params['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
		}

		if (!empty($data['MedService_id'])) {
			// если указана служба, до по МО не фильтруем, т.к. уже фильтруется по конкретной службе.
			$filter_arr[] = "res.MedService_id = :MedService_id";
			$params['MedService_id'] = $data['MedService_id'];
		} else if (isset($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter_arr[] = "ms.Lpu_id = :Lpu_id";
		}
		if (isset($data['LpuUnit_id'])) {
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
			$filter_arr[] = "ms.LpuUnit_id = :LpuUnit_id";
		}
		if (isset($data['LpuSection_id'])) {
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$filter_arr[] = "ms.LpuSection_id = :LpuSection_id";
		}
		if (isset($data['LpuSectionPid_id'])) {
			$params['LpuSectionPid_id'] = $data['LpuSectionPid_id'];
			$filter_arr[] = "ms.LpuSection_id = :LpuSectionPid_id";
		}

		if (!empty($data['Resource_begDate'])) {
			$filter_arr[] = "res.Resource_begDT <= :Resource_begDate";
			$filter_arr[] = "(res.Resource_endDT is null or res.Resource_endDT > :Resource_begDate)";
			$params['Resource_begDate'] = $data['Resource_begDate'];
		}

		$select = "";
		if (!empty($data['TimetableResource_begDate'])) {
			$msflpu = $this->getFirstRowFromQuery("select Lpu_id as \"Lpu_id\" from v_MedService where MedService_id = ?", array($data['MedService_id']));
			$maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $data['MedService_id']);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$params['TimetableResource_begDate'] = date("Y-m-d"); // от текущей даты
			$params['TimetableResource_endDate'] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : date("Y-m-d", strtotime("+365 days", time()));

			$join_arr[] = "inner join lateral(
					select
						*
					from v_TimeTableResource_lite
					where
						Resource_id = res.Resource_id
						and Person_id is null
						and TimetableResource_begTime between :TimetableResource_begDate and :TimetableResource_endDate
					order by
						TimeTableResource_begTime
					limit 1
				) TTR on true";
			$order = "TTR.TimeTableResource_begTime";
			$select .= ", to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"TimetableResource_begDate\"";
		}

		$filters = implode(" and ", $filter_arr);
		$join = implode("\n", $join_arr);

		$sql = "
			select
				res.Resource_id as \"Resource_id\",
				res.Resource_Name as \"Resource_Name\",
				res.MedService_id as \"MedService_id\",
				ms.LpuUnit_id as \"LpuUnit_id\"
				{$select}
			from
				v_Resource res
				left join v_MedService ms on ms.MedService_id = res.MedService_id
				{$join}
			where
				{$filters}
			order by
				{$order}
		";

		$res = $this->db->query($sql, $params);
        //echo getDebugSQL($sql, $params);exit;
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка услуг
	 */
	function getUslugaComplexListForSchedule($data) {
        $params = array();

        $MedServiceType_SysNick = $this->getFirstResultFromQuery("SELECT mst.MedServiceType_SysNick as \"MedServiceType_SysNick\" FROM v_MedService ms inner join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id WHERE ms.MedService_id = :MedService_id", $data);

        if ( $MedServiceType_SysNick == 'lab' ) {
			$sql = "
				select ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
				      ,uc.UslugaComplex_Name as \"UslugaComplex_Name\"
				from v_UslugaComplex uc
					 inner join v_UslugaComplexMedService ucm
					     on uc.UslugaComplex_id = ucm.UslugaComplex_id
					 inner join lateral(
                             select
                                    at.AnalyzerTest_id
                             from lis.v_AnalyzerTest at
                                  inner join lis.v_Analyzer a
                                      on a.Analyzer_id = at.Analyzer_id
                             where at.UslugaComplexMedService_id = ucm.UslugaComplexMedService_id
                               and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1
                               and coalesce(a.Analyzer_IsNotActive, 1) = 1
                               and (at.AnalyzerTest_endDT >= dbo.tzGetDate() or at.AnalyzerTest_endDT is null)
                             limit 1
					     ) ATEST on true -- фильтрация услуг по активности тестов связанных с ними
				where ucm.MedService_id = :MedService_id
				  and ucm.UslugaComplexMedService_pid is null
				order by uc.UslugaComplex_Name
			";
        } else if ( $MedServiceType_SysNick == 'reglab' ) {
			$sql = "
                select 
                    ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
                    uc.UslugaComplex_Name as \"UslugaComplex_Name\"
                from 
                    v_UslugaComplex uc
                    inner join v_UslugaComplexMedService ucm on uc.UslugaComplex_id = ucm.UslugaComplex_id
                where 
                    ucm.MedService_id = :MedService_id
                    and ucm.UslugaComplexMedService_pid is null
                union all
                (select 
                    ucm.UslugaComplexMedService_id,
                    uc.UslugaComplex_Name
                from 
                    v_UslugaComplex uc
                    inner join v_UslugaComplexMedService ucm on uc.UslugaComplex_id = ucm.UslugaComplex_id
                where 
                    ucm.MedService_id in (select MedService_lid from v_MedServiceLink where MedService_id = :MedService_id)
                    and ucm.UslugaComplexMedService_pid is null
				order by 
				    uc.UslugaComplex_Name)
			";
		} else {
			$sql = "
				select 
				    ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				    uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				    ucm.UslugaComplexMedService_endDT as \"UslugaComplexMedService_endDT\"
				from 
				    v_UslugaComplex uc
				    inner join v_UslugaComplexMedService ucm on uc.UslugaComplex_id = ucm.UslugaComplex_id
				where 
				    ucm.MedService_id = :MedService_id
				    and ucm.UslugaComplexMedService_pid is null
				order by 
				    uc.UslugaComplex_Name
			";
		}
		$res = $this->db->query($sql, $data);
        //echo getDebugSQL($sql, $data);
        if ( is_object($res) )
            return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка подразделений ЛПУ для первого шага мастера записи
	 */
	function getRecordLpuUnitList( $data ) {
		$params = array();
		$filter = "";
		$with = "
			with mv as (
				select dbo.tzgetdate() as dt
			)
		";

		// Если пользователь не из ЦЗ и мы не выписываем направление
		// ограничиваем список только подразделениями свой ЛПУ
		if ( !isset($data['ListForDirection']) && !IsCZUser() && !isSMOUser() && !isTFOMSUser() && !isTOUZuser() ) {
			$params['Lpu_id'] = $data['session']['lpu_id'];
			$filter .= " and lu.Lpu_id = :Lpu_id";
		}

		// Фильтр по конкретному ЛПУ
		// Применяется на втором шаге, когда выбрано ЛПУ, для отображения его подразделений
		if ( isset($data['Filter_Lpu_id']) ) {
			$params['Lpu_id'] = $data['Filter_Lpu_id'];
			$filter .= " and lu.Lpu_id = :Lpu_id";
		}

		// Фильтр по подстроке в названии ЛПУ
		if ( isset($data['Filter_Lpu_Nick']) ) {
			$with .= "
				,LpuList as (
					select Lpu_id
					from v_Lpu
					where Lpu_Nick ilike :Lpu_Nick
				)
			";
			$filter .= " and l.Lpu_id in (select Lpu_id from LpuList)";
			$params['Lpu_Nick'] = '%' . $data['Filter_Lpu_Nick'] . '%';
		}

		// Фильтр по типу подразделения
		if ( isset($data['Filter_LpuUnitType_id']) ) {
			$params['LpuUnitType_id'] = $data['Filter_LpuUnitType_id'];
			$filter .= " and lu.LpuUnitType_id = :LpuUnitType_id";
		}
		if ( isset($data['ARMType']) && $data['ARMType'] == 'callcenter' ) {
			$filter .= " and lu.LpuUnit_Enabled = 1";
		}

		// Не показываем подразделения в которых все отделения с типом записи Без записи.
		if ( isset($data['ListForDirection']) && !((isset($data['Filter_Lpu_id']) && IsLpuRegUserByLpu($data['Filter_Lpu_id'])) || IsCZUser()) ) {
			// Фильтр по только разрешенным для направлений отделениям
			$ls_filter = " and (
				coalesce(LpuSectionHospType_id, 1) != 5
				and (
					case when lut.LpuUnitType_Code in ('2','3','5') then coalesce(ls.LpuSection_IsDirRec, 2) else 2 end = 2
					or lu.Lpu_id = :User_Lpu_id
				)
			) "; // требуем разрешение на выписку направления в отделение только для стационаров
			$params['User_Lpu_id'] = $data['session']['lpu_id'];
		} else {
			$ls_filter = " and coalesce(LpuSectionHospType_id, 1) != 5 ";
			// исключаем отделения у которых не стоит разрешение на запись из других МО (настройки есть только у стац + параклиники(1,6,7,9,3))
			$ls_filter .= " and (ls.Lpu_id = :User_Lpu_id OR (lu.LpuUnitType_id not in (1,6,7,9,3) OR coalesce(ls.LpuSection_IsDirRec, 1) = 2)) and ls.LpuUnit_id=lu.LpuUnit_id limit 1";
			$params['User_Lpu_id'] = $data['session']['lpu_id'];
		}

		// Фильтр по подстроке с которой начинается ФИО врача
		if ( isset($data['Filter_MedPersonal_FIO']) ) {
			// Для поликлиник ищем только работающих врачей, к которым разрешена запись
			// для стационаров берем всех работающих врачей
			$filter .= " and lu.LpuUnit_id in(
				select
					ls.LpuUnit_id
				from v_MedStaffFact_ER msf
				inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				inner join v_LpuUnit_ER lu on ls.LpuUnit_id = lu.LpuUnit_id
				where 
					msf.MedPersonal_FIO ilike :MedPersonal_FIO
					and coalesce(RecType_id, 6) != 6 
					and coalesce(MedStatus_id::bigint, 1) = 1 
					and coalesce(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= :Medstafffact_disDate
					and lu.LpuUnitType_SysNick in ('polka', 'ccenter', 'fap')
				union all
				select
					ls.LpuUnit_id
				from v_MedStaffFact_ER msf
				inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				inner join v_LpuUnit_ER lu on ls.LpuUnit_id = lu.LpuUnit_id
				where 
					msf.MedPersonal_FIO ilike :MedPersonal_FIO
					and coalesce(MedStatus_id::bigint, 1) = 1 
					and coalesce(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= :Medstafffact_disDate
					and lu.LpuUnitType_SysNick in ('stac', 'dstac', 'pstac')
					{$ls_filter}
				)
			";

			$params['MedPersonal_FIO'] = $data["Filter_MedPersonal_FIO"] . "%";

			$filter .= "  ";
			If ( !isset($data['Date']) ) {
				$params['Medstafffact_disDate'] = date("Y-m-d", time());
			} else {
				$params['Medstafffact_disDate'] = date("Y-m-d", strtotime($data['Date']));
			}
		}

        // Фильтр по профилю отделения
        if ( isset($data['Filter_LpuSectionProfile_id']) ) {
            $additionalLpuSectionProfileFilter = '';
            if ( !empty($data['Filter_includeDopProfiles']) ) {
                $additionalLpuSectionProfileFilter = "
					or lu.LpuUnit_id in(

					select
						ls.LpuUnit_id
					from v_LpuSection ls
						inner join v_LpuSectionLpuSectionProfile lslsp on lslsp.LpuSection_id = ls.LpuSection_id
					where lslsp.LpuSectionProfile_id = :LpuSectionProfile_id
						and lslsp.LpuSectionLpuSectionProfile_begDate <= (select dt from mv)
						and coalesce(lslsp.LpuSectionLpuSectionProfile_endDate, (select dt from mv)) >= (select dt from mv)
					{$ls_filter}
				";
            }
            $filter .= " and (lu.LpuUnit_id in(
				select
					ls.LpuUnit_id
				from v_LpuSection ls
				where ls.LpuSectionProfile_id = :LpuSectionProfile_id
				{$ls_filter}
				{$additionalLpuSectionProfileFilter}
				))
			";

            $params['LpuSectionProfile_id'] = $data["Filter_LpuSectionProfile_id"];
        }

		// Фильтр по типу прикрепления
		if ( !empty($data['Filter_LpuRegionType_id']) ) {
			$filter .= " and exists (
					select
						lst.LpuUnit_id
					from
						v_LpuSection lst
						inner join v_LpuRegionTypeSectionProfileLink LRTS on lst.LpuSectionProfile_id = LRTS.LpuSectionProfile_id
					where
						LRTS.LpuRegionType_id = :LpuRegionType_id
						and lst.LpuUnit_id = lu.LpuUnit_id
					limit 1
				)
			";

			$params['LpuRegionType_id'] = $data["Filter_LpuRegionType_id"];
		}

		// Если заданы данные по адресу участка, то будем искать ЛПУ, в которых есть подразделения, в которых работают врачи, работающие на этих участках
		// Дополнительно выбираем подразделения, в которых они работают, для выделения их жирным шрифтов
		if ( isset($data['Filter_KLStreet_Name']) || isset($data['Filter_KLTown_Name']) || isset($data['Filter_KLHouse']) ) {
			If ( !isset($data['Date']) ) {
				$params['Medstafffact_disDate'] = date("Y-m-d", time());
			} else {
				$params['Medstafffact_disDate'] = date("Y-m-d", strtotime($data['Date']));
			}
			$filter .= "
			and lu.Lpu_id in (
					select distinct 
						msf.Lpu_id 
					from v_MedStaffFact_ER msf
					left outer join v_MedstaffRegion msr on msf.MedPersonal_id = msr.MedPersonal_id and msf.Lpu_id = msr.Lpu_id
					where
						coalesce(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= :Medstafffact_disDate
						and coalesce(RecType_id, 6) != 6 and coalesce(MedStatus_id::bigint, 1) = 1";

			$Regions = $this->FindAddressRegions($data['Filter_KLTown_Name'], $data['Filter_KLStreet_Name'], $data['Filter_KLHouse'], $data['ARMType']);

			$RegionLpuUnit = array();
			if ( count($Regions) > 0 ) { // если участки есть
				$filter .= " and ( msr.LpuRegion_Id in( " . implode(", ", $Regions) . " )  ) )";

				// Генерируем массив подразделений, на которых находятся врачи, работающие на найденных участках
				$sql = "
					select distinct 
						msf.LpuUnit_id as \"LpuUnit_id\" 
					from v_MedStaffFact_ER msf
					left outer join v_MedstaffRegion msr on msf.MedPersonal_id = msr.MedPersonal_id and msf.Lpu_id = msr.Lpu_id
					where
						coalesce(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= :Medstafffact_disDate
						and coalesce(RecType_id, 6) != 6 and coalesce(MedStatus_id::bigint, 1) = 1
						and ( msr.LpuRegion_Id in ( " . implode(", ", $Regions) . " ) )";

				$res = $this->db->query($sql, $params);

				if ( is_object($res) ) {
					$res = $res->result('array');
					foreach ( $res as $row ) {
						$RegionLpuUnit[$row['LpuUnit_id']] = 1;
					}
				}
			} else {
				$filter .= " and ( 1 = 0 ) )";
			}
		}

		// Фильтр по подстроке в названии ЛПУ
		if ( isset($data['Filter_LpuUnit_Address']) ) {
			$params['Address_Address'] = '%' . $data['Filter_LpuUnit_Address'] . '%';
			$filter .= " and lua.Address_Address ilike :Address_Address";
		}


		// Фильтр по типу направления
		if ( isset($data['ListForDirection']) && isset($data['DirType_Code']) ) {
			if ( empty($data['Filter_LpuUnitType_id']) ) {
				// Если фильтр не установлен,
				// то в зависимости от типа направления доступны разные типы подразделений #21653
				switch ( $data['DirType_Code'] ) {
					case 1: case 5: case 6:
					    // только в стационар
					    $filter .= " and lut.LpuUnitType_Code in ('2','3','4','5')  ";
					    break;
					case 12:
					case 3:
						// только в поликлинику
						$filter .= " and lut.LpuUnitType_Code in ('1','7','11')  ";
						break;
					case 2:
						// в стационар, в поликлинику
						$filter .= " and lut.LpuUnitType_Code in ('1','2','3','5','4','7','11')  ";
						break;
					case 4:
						// в стационар, в поликлинику
						$filter .= " and lut.LpuUnitType_Code in ('1','2','3','5','4','7')  ";
						break;

				}
			}
		}
		if(getRegionNick() == 'perm' && empty($_SESSION['Lpu_IsTest'])){
			$filter .= " and coalesce(l.Lpu_IsTest, 1) <> 2 ";
		}

		if ( isset($data['Filter_LpuUnit_id']) ) {
			$filter .= " and lu.LpuUnit_id=:LpuUnit_id ";
			$params['LpuUnit_id'] = $data['Filter_LpuUnit_id'];
		} else // На всякий случай дополнительно фильтр по отделениям
        if ($data['ARMType']=='stacpriem' and isset($data['Filter_LpuSection_id']) ) {
                $params['LpuSection_id'] = $data['Filter_LpuSection_id'];
                $filter.=" and lu.LpuUnit_id in(
				select ls.LpuUnit_id
				from v_LpuSection ls 
				inner join LpuSectionService lss on lss.LpuSection_did=ls.LpuSection_id
				where lss.LpuSection_id = :LpuSection_id
				)";
        } else
				$filter .= " and exists(
				select
					ls.LpuUnit_id
				from v_LpuSection ls
				where (1=1) {$ls_filter}
				)
			";
        
        if( isset($data['Filter_LpuAgeType_id']) ) {
            $params['LpuAgeType_id'] = $data['Filter_LpuAgeType_id'];
            $filter .= " and l.MesAgeLpuType_id = :LpuAgeType_id";
        }

		$sql = "
			-- addit with
			" . $with . "
			-- end addit with
			
			select
				-- select
				l.Lpu_Name as \"Lpu_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				l.LpuType_id as \"LpuType_id\",
                l.MesAgeLpuType_id as \"LpuAgeType_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				lu.LpuUnit_Descr as \"LpuUnit_Descr\",
				luat.KLArea_Name as \"Town\",
				( Rtrim(luas.KLStreet_Name) || ' ' || lua.Address_House ) as \"LpuUnit_Address\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.Lpu_id as \"Lpu_id\",
				lu.LpuUnit_Phone as \"LpuUnit_Phone\",
				lu.LpuUnit_Enabled as \"LpuUnit_Enabled\",
				lu.LpuUnit_ExtMedCnt as \"ExtMed\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				rtrim(pmUser_Name) as \"pmuser_name\",
				to_char(LpuUnit_updDT, 'dd.mm.yyyy HH24:mi:ss') as \"LpuUnit_updDT\",
				LpuFondHolder_IsEnabled as \"LpuFondHolder_IsEnabled\",
				case when lfh.LpuFondHolder_IsEnabled = 2 then 'Да' end as \"FondHolder\",
				lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				-- end select
			from
				-- from
				v_LpuUnit_ER lu
				left join v_LpuUnitType lut on lu.LpuUnitType_id = lut.LpuUnitType_id
				left outer join Address lua on lu.Address_id = lua.Address_id
				left outer join KLStreet luas on lua.KLStreet_id = luas.KLStreet_id
				left outer join KLArea luat on lua.KLTown_id = luat.KLArea_id
				inner join v_Lpu l on l.Lpu_id = lu.Lpu_id
				left join LpuFondHolder lfh on l.Lpu_id = lfh.Lpu_id and LpuFondHolder_IsEnabled = 2
				left join v_pmUserCache pu on lu.pmUser_updId = pu.pmUser_id -- оптимизация - поменял на v_pmUserCache, т.к. остальные пользователе редактировать LpuUnit не могут
				-- end from
			WHERE
				-- where
				lut.LpuUnitType_Code != 10
				and (l.Lpu_endDate is null or l.Lpu_endDate > (select dt from mv))
				and (lu.LpuUnit_endDate is null or lu.LpuUnit_endDate > (select dt from mv))
				{$filter}
				and l.Lpu_id is not null --по крайней мере на тестовой базе есть подразделения, ЛПУ у которых нет в v_Lpu
				-- end where
			ORDER BY 
				-- order by
				lu.LpuUnit_Enabled DESC,
				l.Lpu_Nick ASC,
				lu.LpuUnit_Name ASC
				-- end order by
		";

		//echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die();

		$response = $this->getPagingResponse($sql, $params, $data['start'], $data['limit'], true);
		if ( isset($RegionLpuUnit) ) {
			$response['RegionLpuUnit'] = $RegionLpuUnit; // массив с подразделениями, на которых находятся врачи, работающие на найденных участках
		}
		return $response;
	}

	/**
	 * Получение списка врачей для переданного подразделения для мастера записи
	 */
	function getRecordMedPersonalList( $data ) {
		$params = array();
		$filter = "";

		$params['LpuUnit_id'] = $data['LpuUnit_id'];
		$params['Date'] = $data['Date'];
		$params['Day_id'] = TimeToDay(time());

		if ( isset($data['Filter_MedPersonal_FIO']) || isset($data['Filter_LpuSectionProfile_id']) ) {
			if ( isset($data['Filter_MedPersonal_FIO']) ) {
				$filter .= " and msf.MedPersonal_FIO ilike :MedPersonal_FIO ";
				$params['MedPersonal_FIO'] = $data["Filter_MedPersonal_FIO"] . "%";
			}

			if ( isset($data['Filter_LpuSectionProfile_id']) ) {
				$lspFilterList = array(
					'msf.LpuSectionProfile_id = :LpuSectionProfile_id'
				);
				$params['LpuSectionProfile_id'] = $data["Filter_LpuSectionProfile_id"];

				if ( $data['session']['region']['nick'] == 'ekb' ) {
					$lspFilterList[] = "exists(
						select lspmso.LpuSectionProfileMedSpecOms_id
						from v_LpuSectionProfileMedSpecOms lspmso
							inner join v_MedStaffFact msf1 on msf1.MedSpecOms_id = lspmso.MedSpecOms_id
						where msf1.MedStaffFact_id = msf.MedStaffFact_id
							and lspmso.LpuSectionProfileMedSpecOms_begDate <= (select dt from mv)
							and (lspmso.LpuSectionProfileMedSpecOms_endDate > (select dt from mv) or lspmso.LpuSectionProfileMedSpecOms_endDate is null)
							and lspmso.LpuSectionProfile_id = :LpuSectionProfile_id
						limit 1
					)";
				}

				if ( !empty($data['Filter_includeDopProfiles']) ) {
					$lspFilterList[] = "exists(
						select
							ls.LpuUnit_id
						from v_LpuSection ls
							inner join v_LpuSectionLpuSectionProfile lslsp on lslsp.LpuSection_id = ls.LpuSection_id
						where ls.LpuSection_id = msf.LpuSection_id
							and lslsp.LpuSectionProfile_id = :LpuSectionProfile_id
							and lslsp.LpuSectionLpuSectionProfile_begDate <= (select dt from mv)
							and coalesce(lslsp.LpuSectionLpuSectionProfile_endDate, (select dt from mv)) >= (select dt from mv)
						limit 1
					)";
				}

				$filter .= " and (" . implode(' or ', $lspFilterList) . ")";
			}
		}

		if ( isset($data['withDirection']) && $data['withDirection'] == 1 && !empty($_SESSION['lpu_id']) ) {
			$filter .= " and ( coalesce(msf.MedStaffFact_IsDirRec, 2) = 2 or msf.Lpu_id = :Lpu_id ) ";
			$params['Lpu_id'] = $_SESSION['lpu_id'];
		}

		if ( isset($data['ListForDirection']) ) {
			//Для пользователей других ЛПУ показываем только врачей, к которым разрешена запись через направления или у которых есть внешние бирки
			//теперь только по галочке #86459
			If ( isset($data['Filter_Lpu_id']) && !IsLpuRegUserByLpu($data['Filter_Lpu_id']) ) {
				$filter .= " and coalesce(msf.MedStaffFact_IsDirRec, 2) = 2 ";
			}
		}

		// Фильтр по типу прикрепления - врачи из отделений с профилями, осуществляющими примем по выбранному типу прикреплению
		if ( !empty($data['Filter_LpuRegionType_id']) ) {
			$filter .= " and exists (
					select
						lst.LpuSection_id
					from
						v_LpuSection lst
						inner join v_LpuRegionTypeSectionProfileLink LRTS on lst.LpuSectionProfile_id = LRTS.LpuSectionProfile_id
					where
						LRTS.LpuRegionType_id = :LpuRegionType_id
						and lst.LpuSection_id = msf.LpuSection_id
					limit 1
				)
			";

			$params['LpuRegionType_id'] = $data["Filter_LpuRegionType_id"];
		}

		if ( isset($data['WithoutChildLpuSectionAge']) && $data['WithoutChildLpuSectionAge'] == 1 ) {
			$filter .= " and coalesce(ls.LpuSectionAge_id, 3) in (1, 3)";
		}

		// Если заданы данные по адресу участка, то будем искать врачей, работающих на этих участках
		if ( isset($data['Filter_KLStreet_Name']) || isset($data['Filter_KLTown_Name']) || isset($data['Filter_KLHouse']) ) {
			$Regions = $this->FindAddressRegions($data['Filter_KLTown_Name'], $data['Filter_KLStreet_Name'], $data['Filter_KLHouse']);
			if ( count($Regions) > 0 ) { // если участки нашлись, фильтруем по ним
				$regions_filter = " and ( t1.LpuRegion_id in( " . implode(", ", $Regions) . " ) ) ";
			} else {
				$regions_filter = "";
				$Regions[] = 'null'; // добавляем пустой элемент, чтобы implode отработала правильно
			}
			$filter .= " and nullif(lr.LpuRegion_Name, '') is not null ";

			$lr_select = "
				select
					(
						select
							string_agg(
								case when t.LpuRegion_id in ( " . implode(", ", $Regions) . " )
									then '*' || t.LpuRegion_Name || '|' || cast(t.LpuRegion_id as varchar)
									else t.LpuRegion_Name || '|' || cast(t.LpuRegion_id as varchar)
								end, ', ')
						from (
							select
								t2.LpuRegion_id,
								LpuRegion_Name,
								lr2.LpuRegion_id
							from v_MedStaffRegion t2
							left join v_LpuRegion lr2 on t2.LpuRegion_id = lr2.LpuRegion_id
							where
								t1.MedPersonal_id = t2.MedPersonal_id
								and t2.Lpu_id = msf.Lpu_id
								and t2.MedStaffRegion_begDate <= dbo.tzGetDate()
								and (t2.MedStaffRegion_endDate is null or t2.MedStaffRegion_endDate >= dbo.tzGetDate())
								and lr2.LpuRegion_begDate <= dbo.tzGetDate()
								and (lr2.LpuRegion_endDate is null or lr2.LpuRegion_endDate >= dbo.tzGetDate())
							order by cast(lr2.LpuRegion_Name as integer)
						) t
					) as LpuRegion_Name
				from v_MedStaffRegion t1
				left join v_LpuRegion lr1 on t1.LpuRegion_id = lr1.LpuRegion_id
				where (t1.MedPersonal_id = msf.MedPersonal_id or t1.MedStaffFact_id= msf.MedStaffFact_id)
					-- Участки считаем только для определённых широких профилей
					and msf.LpuSectionProfile_id in (select LpuSectionProfile_id from v_LpuSectionProfile where LpuSectionProfile_IsArea = 2)
					and coalesce(msf.MedStaffFact_IsNotReception, 1) != 2
					and t1.MedStaffRegion_begDate <= dbo.tzGetDate()
					and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= dbo.tzGetDate())
					{$regions_filter}
				group by t1.MedPersonal_id";
		} else {
			$lr_select = "
				select
					(
						select
							string_agg(t.LpuRegion_Name || '|' || cast(t.LpuRegion_id as varchar), ', ')
							from (
								select
									LpuRegion_Name,
									lr2.LpuRegion_id
								from v_MedStaffRegion t2
								left join v_LpuRegion lr2 on t2.LpuRegion_id = lr2.LpuRegion_id
								where
									t1.MedPersonal_id = t2.MedPersonal_id
									and t2.Lpu_id = msf.Lpu_id
									and t2.MedStaffRegion_begDate <= dbo.tzGetDate()
									and (t2.MedStaffRegion_endDate is null or t2.MedStaffRegion_endDate >= dbo.tzGetDate())
									and lr2.LpuRegion_begDate <= dbo.tzGetDate()
									and (lr2.LpuRegion_endDate is null or lr2.LpuRegion_endDate >= dbo.tzGetDate())
								order by cast(lr2.LpuRegion_Name as integer)
							) t
					) as LpuRegion_Name
				from v_MedStaffRegion t1
				left join v_LpuRegion lr1 on t1.LpuRegion_id = lr1.LpuRegion_id
				where (t1.MedPersonal_id = msf.MedPersonal_id or t1.MedStaffFact_id= msf.MedStaffFact_id)
					-- Участки считаем только для определённых широких профилей
					and msf.LpuSectionProfile_id in (select LpuSectionProfile_id from v_LpuSectionProfile where LpuSectionProfile_IsArea = 2)
					and coalesce(msf.MedStaffFact_IsNotReception, 1) != 2
					and t1.MedStaffRegion_begDate <= dbo.tzGetDate()
					and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= dbo.tzGetDate())
				group by t1.MedPersonal_id";
		}

		$select = "";
		$outer_apply = "";

		if (!empty($data['fromApi'])) {
			$select .= " ,(to_char(ffd.TimetableGraf_begTime, 'dd.mm.yyyy HH24:MI:SS')) as \"FirstFreeDate\"";
			$outer_apply .= " 
				left join lateral(
					select ttg.TimetableGraf_begTime FROM v_TimetableGraf_lite ttg WHERE ttg.MedStaffFact_id = msf.MedStaffFact_id and Person_id IS NULL and TimetableGraf_Day >= :Day limit 1
				) ffd on true
			";
		}

		$params['Day'] = TimeToDay(time());

		$sql = "
			with mv as (
				select dbo.tzgetdate() as dt
			)
			select
				-- select
				rtrim(MedPersonal_FIO) as \"MedPersonal_FIO\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				msf.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				lsp.LpuSectionProfile_Name as \"MainLpuSectionProfile_Name\",
				ls.LpuSectionAge_id as \"LpuSectionAge_id\",
				lsa.LpuSectionAge_Name as \"LpuSectionAge_Name\",
				null as \"MedStaffFact_Descr\",
				case
					when mpd.MedpersonalDay_FreeRec > 0 then 'true' else 'false'
				end	as \"HasRecordsTooday\",
				cwr.CountWithoutRecord as \"CountWithoutRecord\",
				rtrim(u.pmuser_name) as \"pmuser_name\",
				msf.Lpu_id as \"Lpu_id\",
				msf.LpuUnit_id as \"LpuUnit_id\",
				msf.MedStaffFact_updDT as \"MedStaffFact_updDT\",
				msf.RecType_id as \"RecType_id\",
				msf.MedStatus_id as \"MedStatus_id\",
				case when nullif(lr.LpuRegion_Name, '') is not null then 1 end as \"isRegion\",
				rtrim(msf.LpuSection_Name) as \"LpuSection_Name\",
				case when lr.LpuRegion_Name <> '' then SUBSTRING ( lr.LpuRegion_Name from 1 for (length(lr.LpuRegion_Name)-1)) end as \"LpuRegion_Names\",
				coalesce(
					(select
						string_agg(cast(LpuSectionProfile_id as varchar), ',')
					FROM
						v_LpuSectionLpuSectionProfile
					WHERE
						LpuSection_id = ls.LpuSection_id
					) || ',', '') || cast(msf.LpuSectionProfile_id as varchar) as \"LpuSectionLpuSectionProfileList\"
				{$select}
				-- end select
			from
			-- from
				v_MedStaffFact_ER msf
			left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			left join v_LpuSectionAge lsa on lsa.LpuSectionAge_id = ls.LpuSectionAge_id
			left join MedpersonalDay mpd on mpd.MedStaffFact_id = msf.MedStaffFact_id and Day_id = :Day_id
			left join v_pmUser u on u.pmUser_id=msf.pmUser_updID
			left join lateral(
				{$lr_select}
			) lr on true
			left join lateral(
				select count(vttg.TimetableGraf_id) as CountWithoutRecord FROM v_TimetableGraf_lite vttg WHERE vttg.MedStaffFact_id = msf.MedStaffFact_id and Person_id IS NOT NULL and TimetableGraf_isDop = 1 and TimetableGraf_Day = :Day
			) cwr on true
			$outer_apply
			-- end from
			where
				-- where
				msf.LpuUnit_Id = :LpuUnit_id
				and (coalesce(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= :Date)
				and coalesce(MedStatus_id, '1') = '1' 
				and (coalesce(msf.RecType_id, 6) != 6)
				{$filter}
				-- end where
			order by
				-- order by
				\"isRegion\" DESC,
				MedPersonal_FIO ASC 
				-- end order by";

		$resp = $this->queryResult($sql, $params);
		if (empty($resp) || !empty($data['fromApi'])) {
			return $resp;
		}

		$this->load->model("Queue_model", "Queue_model");

		$MedStaffFactIds = array();
		$MedStaffFactLpuIds = array();
		$TimetableBlocked = array();
		foreach($resp as $respone) {
			if (!empty($respone['MedStaffFact_id']) && !in_array($respone['MedStaffFact_id'], $MedStaffFactIds)) {
				$MedStaffFactIds[] = $respone['MedStaffFact_id'];
				$MedStaffFactLpuIds[$respone['MedStaffFact_id']] = $respone['Lpu_id'];
				// блокировка бирок по профилю или по врачу (если включена авто-обработка очереди)
				$TimetableBlocked[$respone['MedStaffFact_id']] = $this->Queue_model->isTimetableBlockedByQueue($respone);
			}
		}

		$this->load->model("Annotation_model", "anmodel");

		// тянем первое свободное время для всех врачей
		$MedStaffFactDates = array();
		$Annotations = array();
		if (!empty($MedStaffFactIds)) {
			$MedStaffFactDates = $this->GetMedPersonalDates($data, $MedStaffFactIds, $MedStaffFactLpuIds, $TimetableBlocked);
			$Annotations = $this->anmodel->getMSFAnnotations($data, $MedStaffFactIds);
		}

		foreach($resp as &$row) {
			// Формирование дат
			if (isset($MedStaffFactDates[$row['MedStaffFact_id']])) {
				$rec = $MedStaffFactDates[$row['MedStaffFact_id']];
				$row['Dates'] = implode(" ", $rec['dates']);
			} else {
				$row['Dates'] = $this->GetEmptyDates();
			}

			// Примечания к врачам
			$fullname = $row['MedPersonal_FIO'];
			if (isset($Annotations[$row['MedStaffFact_id']])) {
				$comments = array();
				foreach ($Annotations[$row['MedStaffFact_id']] as $annotation) {
					$comments[] = "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($annotation['Annotation_Comment'])) . "<hr><font class=\'smallfont\'>" . nl2br(htmlspecialchars($annotation['pmUser_Name'])) . ", " . ConvertDateFormat($annotation['Annotation_updDT'],"H:i d.m.y") . "</font>\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> ";
				}
				$row['Comments'] = join('', $comments);
			} else {
				$row['Comments'] = "";
			};
			// Раскраска врачей в зависимости от типов приема
			If ( $row['MedStatus_id'] == 2 ) {
				$row['MedPersonal_FIO'] = $row['MedPersonal_FIO'] . "<font color=#444>{$fullname}</font>";
			} else {
				if ( !isset($row['RecType_id']) )
					$row['RecType_id'] = 1;
				if ( $this->recTypes[$row['RecType_id']]['fontcolor'] != '' ) {
					$row['MedPersonal_FIO'] = "<b><font color='" .
						$this->recTypes[$row['RecType_id']]['fontcolor'] . "' " .
						$this->recTypes[$row['RecType_id']]['tip'] .
						">{$row['MedPersonal_FIO']}</font></b>";
				} else {
					$row['MedPersonal_FIO'] = "<b><font " .
						$this->recTypes[$row['RecType_id']]['tip'] .
						">{$row['MedPersonal_FIO']}</font></b>";
				}
			}

			// Если заданы какие-то данные по адресам участков, будем жирным шрифтом выделять номера участков, на которых находятся эти улицы
			if ( isset($data['Filter_KLStreet_Name']) || isset($data['Filter_KLTown_Name']) || isset($data['Filter_KLHouse']) ) {
				if ( $row['LpuRegion_Names'] != '' ) {
					//Заменяем плейсхолдер * на жирный шрифт
					$regions = explode(', ', $row['LpuRegion_Names']);
					foreach ( $regions as &$region ) {
						$region = preg_replace('/\*(\d|\|+)/i', '<b>$1</b>', $region);
					}
					$row['LpuRegion_Names'] = implode(', ', $regions);
				}
			}

			// Преобразуем названия участков в ссылки
			if ( $row['LpuRegion_Names'] != '' ) {
				$regions = explode(', ', $row['LpuRegion_Names']);
				foreach ( $regions as &$region ) {
					$region_data = explode('|', $region);
					$region = "<a href=# onclick=\"getWnd('swRegionStreetListWindow').show({LpuRegion_id:" . $region_data[1] . "})\">" . $region_data[0] . "</a>";
				}
				$row['LpuRegion_Names'] = implode(', ', $regions);
			}
		}

		return $resp;
	}

	/**
	 * Получение списка отделений для переданного подразделения. Метод для API
	 */
	function getRecordLpuSectionListAPI( $data ) {
		if(empty($data['fromApi'])) $data['fromApi'] = true;
		$result = array();
		$group_result = array();
		$res = $this->getRecordLpuSectionList($data);
		if($res && is_array($res)){
			foreach ($res as $key => $value) {
				if( !isset($group_result[$value['LpuSectionProfile_id']]['LpuSectionProfile_id']) ){
					$group_result[$value['LpuSectionProfile_id']]['LpuSectionProfile_id'] = $value['LpuSectionProfile_id'];
					$group_result[$value['LpuSectionProfile_id']]['LpuSectionProfile_Name'] = $value['LpuSectionProfile_Name'];
				}
				$group_result[$value['LpuSectionProfile_id']]['LpuSectionList'][] = $value;
			}
			usort($group_result, function ($a, $b) { return strcmp($a["LpuSectionProfile_Name"], $b["LpuSectionProfile_Name"]); });
			$result = array_values($group_result);
		}
		return $result;
	}

	/**
	 * Получение списка отделений для переданного подразделения для мастера записи
	 */
	function getRecordLpuSectionList( $data ) {
		$params = array();
		$filter = "";
		$join = "";
		$order_by = " ls.LpuSection_Name ASC ";
		$params['LpuUnit_id'] = $data['LpuUnit_id'];
		$params['Date'] = $data['Date'];

		if ( isset($data['Filter_LpuSectionProfile_id']) ) {
			if ( !empty($data['Filter_includeDopProfiles']) ) {
				$filter .= " and (ls.LpuSectionProfile_id = :LpuSectionProfile_id or exists (
					select LpuSectionLpuSectionProfile_id
					from v_LpuSectionLpuSectionProfile
					where LpuSection_id = ls.LpuSection_id
						and LpuSectionProfile_id = :LpuSectionProfile_id
						and LpuSectionLpuSectionProfile_begDate <= cast(:Date as timestamp)
						and coalesce(LpuSectionLpuSectionProfile_endDate, cast(:Date as timestamp)) >= cast(:Date as timestamp)
					limit 1
				))
				";
			}
			else {
				$filter .= " and ls.LpuSectionProfile_id = :LpuSectionProfile_id ";
			}

			$params['LpuSectionProfile_id'] = $data["Filter_LpuSectionProfile_id"];
		}

		if ( !empty($_SESSION['lpu_id']) ) {
			$filter .= " and (coalesce(ls.LpuSection_IsDirRec, 2) = 2 or ls.Lpu_id = :Lpu_id) ";
			$params['Lpu_id'] = $_SESSION['lpu_id'];
		}

		if ( isset($data['WithoutChildLpuSectionAge']) && $data['WithoutChildLpuSectionAge'] == 1 ) {
			$filter .= " and coalesce(ls.LpuSectionAge_id, 3) in (1, 3)";
		}

		if ( isset($data['Filter_LpuSection_id']) ) {
			$params['LpuSection_id'] = $data['Filter_LpuSection_id'];
			if ($data['ARMType']=='stacpriem' and $data['FormName']=='swDirectionMasterWindow') {
				$filter = " and lss.LpuSection_id = :LpuSection_id ";
				$join .= " left join LpuSectionService lss on lss.LpuSection_did = ls.LpuSection_id ";
			} else {
				$filter .= " and ls.LpuSection_id=:LpuSection_id ";
			}
		}

		$select = "";
		if (empty($data['fromApi'])) {
			$select .= "
				,to_char(ls.LpuSection_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"LpuSection_updDT\"
				,rtrim(pmUser_Name) as \"pmuser_name\"
				,ls.LpuSection_pid as \"LpuSection_pid\"
			";
		}
		if (!empty($data['fromApi'])) {
			$order_by = " ls.LpuSectionProfile_id ASC ";
		}
		$sql = "
			select
				ls.Lpu_id as \"Lpu_id\",
				ls.LpuSection_id as \"LpuSection_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				ls.LpuSection_Descr as \"LpuSection_Descr\",
				ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ls.LpuSectionProfile_Name as \"MainLpuSectionProfile_Name\",
				ls.LpuSectionAge_id as \"LpuSectionAge_id\",
				lsa.LpuSectionAge_Name as \"LpuSectionAge_Name\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				lut.LpuUnitType_Name as \"LpuSectionType_Name\",
				lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				,coalesce(
					(select
						string_agg(cast(LpuSectionProfile_id as varchar), ',')
					FROM
						v_LpuSectionLpuSectionProfile
					WHERE
						LpuSection_id = ls.LpuSection_id
					) || ',','') || cast(ls.LpuSectionProfile_id as varchar) as \"LpuSectionLpuSectionProfileList\"
				{$select}
			from v_LpuSection ls
			left join v_LpuSectionAge lsa on lsa.LpuSectionAge_id = ls.LpuSectionAge_id
			left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
			left join v_pmUser pu on ls.pmUser_updId = pu.pmUser_id
			{$join}
			where
				ls.LpuUnit_Id = :LpuUnit_id
				and coalesce(LpuSectionHospType_id, 1) != 5
				and (coalesce(cast(ls.LpuSection_disDate as date), '2030-01-01') > :Date)
				{$filter}
			order by
				-- order by
				{$order_by} 
				-- end order by";

		//echo getDebugSQL($sql, $params); die();
		$resp = $this->queryResult($sql, $params);
		if (empty($resp)) {
			return $resp;
		}

		if ( !empty($data['fromApi']) ) {
			$resp = $this->GetLpuSectionDateAPI($data, $resp);
			return $resp;
		}

		$LpuSectionIds = array();
		$LpuSectionLpuIds = array();
		foreach($resp as $respone) {
			if (!empty($respone['LpuSection_id']) && !in_array($respone['LpuSection_id'], $LpuSectionIds)) {
				$LpuSectionIds[] = $respone['LpuSection_id'];
				$LpuSectionLpuIds[$respone['LpuSection_id']] = $respone['Lpu_id'];
			}
		}

		// тянем первое свободное время для всех отделений
		$LpuSectionDates = array();
		if (!empty($LpuSectionIds)) {
			$LpuSectionDates = $this->GetLpuSectionDates($data, $LpuSectionIds, $LpuSectionLpuIds);
		}

		foreach($resp as &$row) {
			// Формирование дат
			if (isset($LpuSectionDates[$row['LpuSection_id']])) {
				$rec = $LpuSectionDates[$row['LpuSection_id']];
				$row['Dates'] = implode(" ", array_slice($rec['dates'], 0, 11)) . '<br/>' . implode(" ", array_slice($rec['dates'], 11, 10));
			} else {
				$row['Dates'] = $this->GetEmptyDates();
			}

			// Примечания к отделениям
			$fullname = $row['LpuSection_Name'];
            $LpuSectionUpdateDate = DateTime::createFromFormat('d.m.Y H:i:s', $row['LpuSection_updDT']);
			If ( $row['LpuSection_Descr'] != "" ) {
				if ( $row['pmuser_name'] != "" ) {
					$row['Comments'] = "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($row['LpuSection_Descr'])) . "<hr><font class=\'smallfont\'>" . nl2br(htmlspecialchars($row['pmuser_name'])) . ", " . $LpuSectionUpdateDate->format("H:i d.m.y") . "</font>\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> ";
				} else {
					$row['Comments'] = "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($row['LpuSection_Descr'])) . "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\">";
				}
			} else {
				$row['Comments'] = "";
			};
		}

		return $resp;
	}

	/**
	 * Список широких профилей для которых отображаем участки
	 */
	function GetCommonProfilesList() {
		return "(2, 12, 21, 36, 111, 5080, 5084, 10000, 12013, 30001, 12003)";
	}

	/**
	 * Получение очереди для подразделения
	 */
	function GetLpuUnitQueue( $data ) {
		$eq_filters = "(1 = 1)";
		$ed_filters = '';

		if (!empty($data['Filter_Lpu_id'])) {
			// $eq_filters .= " and EQ.Lpu_id = :Filter_Lpu_id"; // если направляли из другой МО то будет неверное кол-во из-за этого фильтра.
			$ed_filters .= " and ED.Lpu_did = :Filter_Lpu_id";
		}
		if (!empty($data['LpuSectionProfile_id'])) {
			$eq_filters .= " and EQ.LpuSectionProfile_did = :LpuSectionProfile_id";
		}

		$sql = "
			select
				EQ.LpuSectionProfile_did as \"LpuSectionProfile_did\",
				count(*) as \"cnt\",
				MIN(Evn.Evn_updDT + interval '14 days') as \"EvnQueue_insDT\",
				date_part('day', dbo.tzGetDate() - MIN(Evn.Evn_setDT)) as \"EvnQueue_Days\"
			from
				EvnQueue EQ
				inner join Evn Evn on EQ.Evn_id = Evn.Evn_id and Evn.Evn_deleted = 1
				left join lateral(
					Select ED.EvnDirection_id from v_EvnDirection_all ED
					left join v_DirType DT on DT.DirType_id = ED.DirType_id
					where EQ.EvnDirection_id = ED.EvnDirection_id and DT.DirType_Code not in (7,14,16)
						and coalesce(ED.EvnStatus_id, 16) = 10
						{$ed_filters}
					limit 1
				) ED on true
			where
				{$eq_filters}
				and ED.EvnDirection_id is not null
				and EQ.EvnQueue_IsArchived is null
				and EQ.EvnQueue_recDT is null
				and coalesce(EQ.QueueFailCause_id, 0) = 0
			group by EQ.LpuSectionProfile_did
		";

		//echo getDebugSQL($sql, $data);die;
		$res = $this->db->query($sql, $data);

		if ( is_object($res) ) {
			$result = array();
			$res = $res->result('array');
			foreach ( $res as $row ) {
				$result[$row['LpuSectionProfile_did']] = array(
					'cnt' => $row['cnt'],
					'date' => $row['EvnQueue_insDT'],
					'days' => $row['EvnQueue_Days']
				);
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получение записей в очереди по службам
	 */
	function GetMedServiceQueue( $data ) {
		$filters = "";

		if (!empty($data['Filter_Lpu_id'])) {
			$filters .= " and ED.Lpu_did = :Filter_Lpu_id";
		}
		if (!empty($data['MedService_id'])) {
			$filters .= " and MS.MedService_id = :MedService_id";
		}

		$sql = "
			select
				ED.MedService_id as \"MedService_id\",
				COUNT(*) as \"cnt\",
				MIN(EQ.EvnQueue_updDT + interval '14 days') as \"EvnQueue_insDT\",
				date_part('day', dbo.tzGetDate() - MIN(EQ.EvnQueue_setDT)) as \"EvnQueue_Days\"
			from v_EvnDirection_all ED
			inner join v_EvnQueue EQ on EQ.EvnDirection_id = ED.EvnDirection_id
			inner join v_MedService MS on MS.MedService_id = ED.MedService_id
			inner join v_Lpu L on L.Lpu_id = MS.Lpu_id
			left join v_DirType DT on DT.DirType_id = ED.DirType_id
			where
				EQ.EvnQueue_IsArchived is null
				and EQ.EvnQueue_recDT is null
				and EQ.EvnQueue_failDT is null
				and coalesce(ED.EvnStatus_id,16) = 10
				and DT.DirType_Code not in (7,14,16)
				{$filters}
			group by ED.MedService_id
		";

		//echo getDebugSQL($sql, $data);die;
		$res = $this->db->query($sql, $data);

		if ( is_object($res) ) {
			$result = array();
			$res = $res->result('array');
			foreach ( $res as $row ) {
				$result[$row['MedService_id']] = array(
					'cnt' => $row['cnt'],
					'date' => $row['EvnQueue_insDT'],
					'days' => $row['EvnQueue_Days']
				);
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка дат с раскраской для приема для врача
	 */
	function GetMedPersonalDates($data, $MedStaffFactIds, $MedStaffFactLpuIds, $TimetableBlocked) {
		$maxDays = $this->GetMaxPolDayCount();
		if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;

		$FirstDay = TimeToDay(strtotime($data['Date']));
		$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит

		$add = "";
		if ( isset($data['ListForDirection']) ) {
			//Для направлений дополнительно делаем запрос по внешним биркам, если они есть незанятые, то день тоже будем считать зелеными
			$add .= ", (select count(*) from v_TimetableGraf_lite ttg where ttg.MedStaffFact_id = mpd.MedStaffFact_id and ttg.TimetableGraf_Day = mpd.Day_id and Person_id is null and TimetableType_id = 5 ) as \"MedpersonalDay_ExtRec\"";
		}
		//для Карелии делаем запрос по первичным и повторным биркам, если есть незанятые, то Первичная - бледно желтый, Повторная - ярко оранжевый
		if(getRegionNick() == 'kareliya') {
			$add .= ", (select count(*) from v_TimetableGraf_lite ttg where ttg.MedStaffFact_id = mpd.MedStaffFact_id and ttg.TimetableGraf_Day = mpd.Day_id and Person_id is null and TimetableType_id = 15 ) as \"MedpersonalDay_Re\"";
			$add .= ", (select count(*) from v_TimetableGraf_lite ttg where ttg.MedStaffFact_id = mpd.MedStaffFact_id and ttg.TimetableGraf_Day = mpd.Day_id and Person_id is null and TimetableType_id = 16 ) as \"MedpersonalDay_First\"";
		}
		$userGroups = array();
			if (!empty($_SESSION['groups']) && is_string($_SESSION['groups'])) {
				$userGroups = explode('|', $_SESSION['groups']);
			}
		$allowRepeatedReceptionAccess = in_array('RepeatedReception', $userGroups);
		$sql = "
			select
				mpd.MedStaffFact_id as \"MedStaffFact_id\",
				mpd.Day_id as \"Day_id\",
				mpd.MedpersonalDay_FreeRec as \"MedpersonalDay_FreeRec\",
				mpd.MedpersonalDay_ReservRec as \"MedpersonalDay_ReservRec\",
				mpd.MedpersonalDay_PayRec as \"MedpersonalDay_PayRec\",
				mpd.MedpersonalDay_CZRec as \"MedpersonalDay_CZRec\"
				{$add}
			from
				MedpersonalDay mpd
			where
				mpd.MedStaffFact_id IN ('" . implode("','", $MedStaffFactIds) . "')
				and mpd.Day_id >= :StartDay
				and mpd.Day_id < :EndDay
			order by
				mpd.Day_id
		";

		$resp = $this->queryResult($sql, array(
			'StartDay' => $FirstDay,
			'EndDay' => $LastDay
		));

		$dates = array();
		$msIndividualPeriod = array();

		if( $data['ARMType'] == 'regpol') {
			$msIndividualPeriod = $this->lipmodel->getObjectIndividualPeriod($data, 'MedStaffFact');
		}

		foreach($MedStaffFactIds as $MedStaffFact_id) {
			$template = array();
			$indLastDay = $LastDay;
			if( !empty($msIndividualPeriod[$MedStaffFact_id]) ) {
				$indLastDay = ($msIndividualPeriod[$MedStaffFact_id] > 14 )? $FirstDay + 14 : $FirstDay + $msIndividualPeriod[$MedStaffFact_id];
			}
			for ($Day = $FirstDay; $Day < $indLastDay; $Day++) {
				$template[$Day] = "<span style='background-color: #ffdddd; font-size: 12px; margin-bottom: 2px;'>" . date('d.m', DayMinuteToTime($Day, 0)) . "</span>";
			}
			$dates[$MedStaffFact_id]['dates'] = $template;
			$dates[$MedStaffFact_id]['count'] = 0;
		}

		if (!empty($resp)) {
			foreach ($resp as $key => $row) {
				if( !empty($msIndividualPeriod[$row['MedStaffFact_id']]) ) {
					$maxDays = ($msIndividualPeriod[$row['MedStaffFact_id']] > 14) ? 14 : $msIndividualPeriod[$row['MedStaffFact_id']];
				} else {
					$maxDays = $this->GetPolDayCount($MedStaffFactLpuIds[$row['MedStaffFact_id']]);
				}

				if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
				$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит
				if ($row['Day_id'] >= $LastDay) {
					continue;
				}

				if (!IsCZUser()) {
					If (!$TimetableBlocked[$row['MedStaffFact_id']] && (!empty($row['MedpersonalDay_FreeRec']) || (isset($row['MedpersonalDay_ExtRec']) && !empty($row['MedpersonalDay_ExtRec'])))) {
						$sColor = '#ddffdd';
					} else if (!empty($row['MedpersonalDay_ReservRec'])) {
						$sColor = 'yellow';
					} else if (!empty($row['MedpersonalDay_PayRec'])) {
						$sColor = '#ff99ff';
					} else if ( isset($row['MedpersonalDay_Re']) && $row['MedpersonalDay_Re'] != "" && $allowRepeatedReceptionAccess){ //#183266
						$sColor = '#f98718';
					} else if ( $data['ARMType'] == 'regpol' && isset($row['MedpersonalDay_First']) && $row['MedpersonalDay_First'] != ""){ //#183266
						$sColor = '#fbffbe';
					} else if (empty($row['MedpersonalDay_FreeRec']) && empty($row['MedpersonalDay_ReservRec']) && !empty($row['MedpersonalDay_PayRec'])) {
						$sColor = '#ffdddd';
					} else {
						$sColor = '#ffdddd';
					}
				} else {
					If (!$TimetableBlocked[$row['MedStaffFact_id']] && (!empty($row['MedpersonalDay_FreeRec']) || (isset($row['MedpersonalDay_ExtRec']) && !empty($row['MedpersonalDay_ExtRec'])))) {
						$sColor = '#ddffdd';
					} else if (!empty($row['MedpersonalDay_CZRec'])) {
						$sColor = '#66cccc';
					} else if (!empty($row['MedpersonalDay_ReservRec'])) {
						$sColor = 'yellow';
					} else if (!empty($row['MedpersonalDay_PayRec'])) {
						$sColor = '#ff99ff';
					} else if ( isset($row['MedpersonalDay_Re']) && $row['MedpersonalDay_Re'] != "" && $allowRepeatedReceptionAccess){ //#183266
						$sColor = '#f98718';
					} else if ( $data['ARMType'] == 'regpol' && isset($row['MedpersonalDay_First']) && $row['MedpersonalDay_First'] != ""){ //#183266
						$sColor = '#fbffbe';
					} else if (empty($row['MedpersonalDay_FreeRec']) && empty($row['MedpersonalDay_ReservRec']) && empty($row['MedpersonalDay_PayRec'])) {
						$sColor = '#ffdddd';
					} else {
						$sColor = '#ffdddd';
					}
				}

				$dates[$row['MedStaffFact_id']]['dates'][$row['Day_id']] = "<span style='background-color: {$sColor}; font-size: 12px;'>" . date('d.m', DayMinuteToTime($row['Day_id'], 0)) . "</span>";
				$dates[$row['MedStaffFact_id']]['count']++;
			}
		}

		return $dates;
	}

	/**
	 * Добавление первого свободного для записи дня. Метод для АПИ
	 */
	function GetLpuSectionDateAPI($data, $LpuSectionIds)
	{
		$maxDays = $this->GetMaxStacDayCount();
		$FirstDay = TimeToDay(strtotime($data['Date']));

		if(is_array($LpuSectionIds) && count($LpuSectionIds)>0){
			foreach ($LpuSectionIds as $key=>$value) {
				$LpuSectionIds[$key]['firstFreeDay'] = "";
				if(empty($value['Lpu_id']) || empty($value['LpuSection_id'])) continue;

				$maxDays = $this->GetStacDayCount($value['Lpu_id']);
				if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
				$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14);

				$params = array(
					'StartDay' => $FirstDay,
					'EndDay' => $LastDay,
					'LpuSection_id' => $value['LpuSection_id']
				);

				$result = $this->getFirstRowFromQuery("
					select
						lsd.LpuSection_id as \"LpuSection_id\",
						lsd.Day_id as \"Day_id\",
						lsd.LpuSectionDay_FreeRec as \"LpuSectionDay_FreeRec\",
						lsd.LpuSectionDay_ReservRec as \"LpuSectionDay_ReservRec\",
						lsd.LpuSectionDay_PayRec as \"LpuSectionDay_PayRec\"
					from
						LpuSectionDay lsd
					where
						lsd.LpuSection_id = :LpuSection_id
						and lsd.LpuSectionDay_FreeRec > 0
						and lsd.Day_id >= :StartDay
						and lsd.Day_id < :EndDay
					order by lsd.Day_id asc
					limit 1
				", $params);

				if(!empty($result['Day_id'])){
					$LpuSectionIds[$key]['firstFreeDay'] = date('d.m.Y', DayMinuteToTime($result['Day_id'], 0));
				}
			}
		}

		return $LpuSectionIds;
	}

	/**
	 * Получение списка дат с раскраской для приема для отделения
	 */
	function GetLpuSectionDates($data, $LpuSectionIds, $LpuSectionLpuIds)
	{
		$maxDays = $this->GetMaxStacDayCount();
		if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;

		$FirstDay = TimeToDay(strtotime($data['Date']));
		$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит

		$sql = "
			select
				lsd.LpuSection_id as \"LpuSection_id\",
				lsd.Day_id as \"Day_id\",
				lsd.LpuSectionDay_FreeRec as \"LpuSectionDay_FreeRec\",
				lsd.LpuSectionDay_ReservRec as \"LpuSectionDay_ReservRec\",
				lsd.LpuSectionDay_PayRec as \"LpuSectionDay_PayRec\"
			from
				LpuSectionDay lsd
			where
				lsd.LpuSection_id IN ('" . implode("','", $LpuSectionIds) . "')
				and lsd.Day_id >= :StartDay
				and lsd.Day_id < :EndDay
			order by
				lsd.Day_id
		";

		$resp = $this->queryResult($sql, array(
			'StartDay' => $FirstDay,
			'EndDay' => $LastDay
		));

		$dates = array();

		$lsIndividualPeriod =array();
		if($data['ARMType'] == 'regpol') {
			$lsIndividualPeriod = $this->lipmodel->getObjectIndividualPeriod($data, 'LpuSection');
		}

		foreach($LpuSectionIds as $LpuSection_id) {
			$template = array();
			$indLastDay = $LastDay;
			if(!empty($lsIndividualPeriod[$LpuSection_id])) {
				$indLastDay = ($lsIndividualPeriod[$LpuSection_id] > 14) ? $FirstDay + 14 : $FirstDay + $lsIndividualPeriod[$LpuSection_id];
			}
			for ($Day = $FirstDay; $Day < $indLastDay; $Day++) {
				$template[$Day] = "<span style='background-color: #ffdddd; font-size: 12px; margin-bottom: 2px;'>" . date('d.m', DayMinuteToTime($Day, 0)) . "</span>";
			}

			$dates[$LpuSection_id]['dates'] = $template;
			$dates[$LpuSection_id]['count'] = 0;
		}

		if (!empty($resp)) {
			foreach ($resp as $key => $row) {
				if( !empty($lsIndividualPeriod[$row['LpuSection_id']]) ) {
					$maxDays = ($lsIndividualPeriod[$row['LpuSection_id']] > 14) ? 14 : $lsIndividualPeriod[$row['LpuSection_id']];
				} else {
					$maxDays = $this->GetStacDayCount($LpuSectionLpuIds[$row['LpuSection_id']]);
				}

				if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
				$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит
				if ($row['Day_id'] >= $LastDay) {
					continue;
				}

				If ( $row['LpuSectionDay_FreeRec'] != "" ) {
					$sColor = '#ddffdd';
				} else {
					If ( $row['LpuSectionDay_ReservRec'] != "" )
						$sColor = 'yellow';
					else
					If ( $row['LpuSectionDay_PayRec'] != "" )
						$sColor = '#ff99ff';
					else
					If ( $row['LpuSectionDay_FreeRec'] == "" && $row['LpuSectionDay_ReservRec'] == "" && $row['LpuSectionDay_PayRec'] == "" )
						$sColor = '#ffdddd';
					else
						$sColor = '#ffdddd';
				}

				$dates[$row['LpuSection_id']]['dates'][$row['Day_id']] = "<span style='background-color: {$sColor}; font-size: 12px;'>" . date('d.m', DayMinuteToTime($row['Day_id'], 0)) . "</span>";
				$dates[$row['LpuSection_id']]['count']++;
			}
		}

		return $dates;
	}

    /**
     * Получает информацию об отделении
     */
    function getLpuSection($data) {
        if (empty($data['LpuSection_id'])) {return false;}
        $query = "
			select
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				ls.LpuSection_id as \"LpuSection_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				case when ls.LpuSection_setDate < dbo.tzGetDate() and (ls.LpuSection_disDate is null or cast(ls.LpuSection_disDate as date) > dbo.tzGetDate()) then 0 else 1 end as \"isClosed\"
			from v_LpuSection ls
			left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			where
				ls.LpuSection_id = :LpuSection_id
			limit 1
		";
        $response = $this->getFirstRowFromQuery($query, $data);

        return $response;
    }
	
	/**
	 * Возвращает все минимальные даты по всем подразделениям по заданному профилю $LpuSectionProfile_id
	 */
	function CacheFirstDates( $data, $LpuSectionProfile_id ) {

		$FirstDates = array();

		$params = array(
			'LpuSectionProfile_id' => $LpuSectionProfile_id,
			'cur_day' => TimeToDay(time())
		);

		$sql = "
			select
				ls.LpuUnit_id as \"LpuUnit_id\",
				ls.Lpu_id as \"Lpu_id\",
				(select Day_date from v_Day d where d.Day_id = min(lsd.Day_id) - 1) as \"MinDate\"
			from v_LpuSection ls
				left join LpuSectionDay lsd on ls.LpuSection_id = lsd.LpuSection_id and LpuSectionDay_FreeRec > 0
			where ls.LpuSectionProfile_id = :LpuSectionProfile_id
				and lsd.Day_id >= :cur_day
				and lsd.Day_id < :cur_day + 21 -- integer + integer
			group by ls.Lpu_id, ls.LpuUnit_id
		";


		$res = $this->db->query($sql, $params);

		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ( $res as $row ) {
				$FirstDates[$row['LpuUnit_id']] = $row['MinDate'];
			}
		}

		//Если время больше заданного, то берем 15 день
		if ( date("H:i") >= getShowNewDayTime() ) {
			$day = GetPolDayCount() + 1;
		} else {
			$day = GetPolDayCount();
		}

		$join = '';
		$filter = "";
		if ( isset($data['WithoutChildLpuSectionAge']) && $data['WithoutChildLpuSectionAge'] == 1 ) {
			$join = 'inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id';
			$filter .= " and coalesce(ls.LpuSectionAge_id, 3) in (1, 3)";
		}

		$sql = "
			select
				msf.LpuUnit_id as \"LpuUnit_id\",
				(select Day_date from v_Day d where d.Day_id = min(mpd.Day_id) - 1) as \"MinDate\"
			from v_Medstafffact_ER msf
			{$join}
			left join v_MedPersonalDay mpd on msf.MedStaffFact_id = mpd.MedStaffFact_id and MedPersonalDay_FreeRec > 0
			where msf.LpuSectionProfile_id = :LpuSectionProfile_id
				and mpd.Day_id >= :cur_day
				and mpd.Day_id < :cur_day + {$day} -- integer + integer
				and msf.MedstaffFact_disDate is null
				{$filter}
			group by msf.LpuUnit_id
		";

		$resl = $this->db->query($sql, $params);

		if ( is_object($resl) ) {
			$resl = $resl->result('array');
			foreach ( $resl as $roww ) {
				$FirstDates[$roww['LpuUnit_id']] = $roww['MinDate'];
			}
		}

		return $FirstDates;
	}

	/**
	 * Поиск участков по заданному населенному пункту, улице, номеру дома
	 */
	function FindAddressRegions( $sKLTown, $sStreet, $sHouse, $armType = null ) {
		$Regions = array();
		$params = array(
			'Street' => addslashes($sStreet) . '%',
			'KLTown' => addslashes($sKLTown) . '%',
			'KLCity' => addslashes($sKLTown) . '%'
		);
		if(!empty($sStreet) && !empty($armType) && $armType == 'callcenter'){
			$params['Street'] = '%'. addslashes($sStreet) . '%';
		}
		$sql = "
			select
				LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\",
				LpuRegion_id as \"LpuRegion_id\"
			from LpuRegionStreet
			where
			(KLStreet_id in 
				( select KLStreet_id from KLStreet where KLStreet_Name ilike :Street )
				or :Street = '%'
			)
			and 
			((KLTown_id in
				( select KLArea_id from KLArea where KLArea_Name ilike :KLTown )
				or :KLTown = '%'
			) or 
			(KLCity_id in
				( select KLArea_id from KLArea where KLArea_Name ilike :KLTown )
				or :KLCity = '%'
			))";

		$res = $this->db->query($sql, $params);

		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ( $res as $row ) {
				if ( (empty($sHouse) || HouseMatchRange(trim($sHouse), trim($row['LpuRegionStreet_HouseSet']))) && !in_array($row['LpuRegion_id'], $Regions) ) {
					$Regions[] = $row['LpuRegion_id'];
				}
			}
		}

		return $Regions;
	}

	/**
	 * Получение списка служб для переданного подразделения для мастера записи
	 */
	function getRecordMedServiceList( $data ) {
		$params = array();
		$join = "";
		if(!empty($data['Filter_MedService_id'])) $filter = "";
		else
		//Согласно #11411#note-8 исключаем данные типы служб из отображения
		$filter = " and mst.MedServiceType_SysNick not in ('patb', 'okadr', 'mstat', 'dpoint', 'merch', 'regpol', 'sprst', 'slneotl', 'smp', 'minzdravdlo', 'reglab', 'pzm')";

		if ( !empty($data['DirType_Code']) ) {
			// В зависимости от типа направления доступны разные типы служб
			switch ( $data['DirType_Code'] ) {
                case 6:
                    $filter = " and mst.MedServiceType_SysNick in ('osmotrgosp')";
                    break;
				case 8:
					$filter = " and mst.MedServiceType_SysNick in ('vk', 'mse')";
					break;
				case 9:
					if(!empty($data['Filter_MedService_id'])) break;
					$filter = " and mst.MedServiceType_SysNick in ('lab', 'func', 'microbiolab')";
					$data['LpuUnitLevel'] = null; //это службы уровня отделения
					break;
				case 10:
					$filter = " and mst.MedServiceType_SysNick in ('konsult')";
					$data['LpuUnitLevel'] = null; //это службы уровня отделения
					break;
				case 11:
					$filter = " and mst.MedServiceType_SysNick in ('prock', 'vac')";
					$data['LpuUnitLevel'] = null; //это службы уровня отделения
					break;
				case 13:
					$filter = " and mst.MedServiceType_SysNick in ('remoteconsultcenter')";
					$data['LpuUnitLevel'] = null; //это службы уровня отделения
					break;
			}
		}

		if ( !empty($data['MedServiceType_SysNick']) ) {
			$filter = " and mst.MedServiceType_SysNick = :MedServiceType_SysNick";
			$params['MedServiceType_SysNick'] = $data['MedServiceType_SysNick'];
		}

		if ( isset($data['LpuUnit_id']) ) {
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
			$filter .= " and ms.LpuUnit_id = :LpuUnit_id";
		} else {
			// на уровне отделений на данный момент только службы ВК и МСЭ, хотя понятно что через полчаса уже все в корне поменяется
			//$filter .= " and ms.LpuUnit_id is null and mst.MedServiceType_SysNick in ('vk', 'mse')";
		}
		if ( !empty($data['isOnlyPolka']) ) {
			// будем показывать только службы поликлинических отделений, в т.ч. стоматологических
			$filter .= " and lu.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')";
		}
		if ( empty($data['ListForDirection']) ) {
			if (isset($data['Filter_Lpu_id'])) {
				$params['Lpu_id'] = $data['Filter_Lpu_id'];
				$filter .= " and ms.Lpu_id = :Lpu_id";
			} elseif (isset($data['Lpu_id'])) {
				$params['Lpu_id'] = $data['Lpu_id'];
				$filter .= " and ms.Lpu_id = :Lpu_id";
			}
		}

		if (!empty($data['session']['lpu_id'])) {
			$params['CurrentLpu_id'] = $data['session']['lpu_id'];
			$filter .= " and (coalesce(ms.MedService_IsThisLPU, 1) = 1 or ms.Lpu_id = :CurrentLpu_id)";
		} else {
			$filter .= " and coalesce(ms.MedService_IsThisLPU, 1) = 1";
		}

		if ( empty($data['LpuUnitLevel']) ) {
			$filter .= " and ms.LpuSection_id is not null";
		}
		if ( isset($data['MedService_Caption']) ) {
			$filter .= " and ms.MedService_Name ilike :MedService_Caption ";
			$params['MedService_Caption'] = $data["MedService_Caption"] . "%";
		}
		if (!empty($data['UslugaComplexMedService_IsPay'])) {
			$filter .= " and ucms.UslugaComplexMedService_IsPay = :UslugaComplexMedService_IsPay";
			$params['UslugaComplexMedService_IsPay'] = $data['UslugaComplexMedService_IsPay'];
		}
		// Фильтр по ФИО врача
		if ( isset($data['Filter_MedPersonal_FIO']) || isset($data['Filter_LpuSectionProfile_id']) ) {
			$filter .= " and ms.MedService_id in(
				select
					msmp.MedService_id
				from v_MedServiceMedPersonal msmp
				inner join v_MedPersonal mp on msmp.MedPersonal_id = mp.MedPersonal_id
			";
			// Фильтр по подстроке с которой начинается ФИО врача
			if ( isset($data['Filter_MedPersonal_FIO']) ) {
				$filter .= " and mp.Person_FIO ilike :MedPersonal_FIO ";
				$params['MedPersonal_FIO'] = $data["Filter_MedPersonal_FIO"] . "%";
			}

			$filter .= " and coalesce(cast(mp.WorkData_endDate as date), '2030-01-01') >= :WorkData_endDate) ";
			If ( !isset($data['Date']) ) {
				$params['WorkData_endDate'] = date("Y-m-d", time());
			} else {
				$params['WorkData_endDate'] = date("Y-m-d", strtotime($data['Date']));
			}
		}

		// Фильтр по подстроке в названии ЛПУ
		if ( isset($data['Filter_Lpu_Nick']) ) {
			$params['Lpu_Nick'] = '%' . $data['Filter_Lpu_Nick'] . '%';
			$filter .= " and l.Lpu_Nick ilike :Lpu_Nick";
		}

		// Фильтр по подстроке в адресе группы отделений
		if ( isset($data['Filter_KLTown_Name']) ) {
			$params['Filter_KLTown_Name'] = '%' . $data['Filter_KLTown_Name'] . '%';
			$filter .= " and exists (
				select OST.OrgServiceTerr_id
				from
					v_OrgServiceTerr OST
					left join v_KLArea KLA on KLA.KLArea_id = COALESCE(OST.KLTown_id, OST.KLCity_id, OST.KLSubRgn_id, OST.KLRgn_id)
				where
					OST.Org_id = '56'
					and KLA.KLArea_Name ilike :Filter_KLTown_Name
				limit 1
			)";
		}

		//Фильтр по службе
		if ( isset($data['Filter_MedService_id'])) {
			$params['Filter_MedService_id'] = $data['Filter_MedService_id'];
			$filter .= " and (ucms.MedService_id = :Filter_MedService_id OR msl.MedService_id = :Filter_MedService_id) ";
			$join .= "left join MedServiceLink msl on msl.MedService_id = ms.MedService_id";
		}

		// Фильтр по подстроке в названии ЛПУ
		// Поиск осуществляется по вхождению введенной подстроки в поле «текстовый адрес» (Address_Address)  подразделений.
		// При наличии нескольких подстрок (определяем наличием пробела): каждая отдельная подстрока должна присутствовать в текстовом адресе подразделения.
		// !!! v_LpuUnit_ER выдает адрес подразделения, доп. джойны LpuBuilding не нужны
		if ( !empty($data['Filter_LpuUnit_Address']) ) {
			$addressParts = explode(' ', $data['Filter_LpuUnit_Address']);
			$i = 0;

			foreach ( $addressParts as $addressPart ) {
				if ( empty($addressPart) ) {
					continue;
				}

				$i++;

				$params['Address_Address_' . $i] = '%' . $addressPart . '%';
				$filter .= " and lua.Address_Address ilike :Address_Address_" . $i;
			}
		}

		$params['Date'] = $data['Date'];

		$MedServiceTypeWithResources = "'".implode("','", array('func'))."'";

		if (!empty($data['groupByMedService']) && empty($data['MedService_id'])) {
			// составной ключ UniqueKey_id (служба_услуга_ресурс)
			$sql = "
				(select
					ms.MedService_id as \"UniqueKey_id\",
					l.Lpu_Nick as \"Lpu_Nick\",
					ms.Lpu_id as \"Lpu_id\",
					ms.MedService_id as \"MedService_id\",
					ms.MedService_Name as \"MedService_Name\",
					ms.MedService_Nick as \"MedService_Nick\",
					ms.MedService_id as \"Group_id\"
				from v_MedService ms
				left join v_UslugaComplexMedService ucms on ucms.MedService_id = ms.MedService_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
				left join v_LpuUnit_ER lu on lu.LpuUnit_id = ms.LpuUnit_id
				left join Address lua on lu.Address_id = lua.Address_id
				left join v_Lpu l on ms.Lpu_id = l.Lpu_id
				left join v_pmUser pu on ms.pmUser_updId = pu.pmUser_id
				left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
				left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
				{$join}
				where
					(coalesce(cast(ms.MedService_endDT as date), '2030-01-01') > :Date)
					and ucms.UslugaComplexMedService_pid is null
					and mst.MedServiceType_SysNick not in ({$MedServiceTypeWithResources}) --список служб c ресурсами
					and (ucms.UslugaComplexMedService_endDT is null or ucms.UslugaComplexMedService_endDT >= :Date)
					{$filter}
					and (
						exists(
						select Analyzer.Analyzer_id
						from lis.v_AnalyzerTest AnalyzerTest
						inner join lis.v_Analyzer Analyzer on Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
						where AnalyzerTest.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						and coalesce(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1 and coalesce(Analyzer.Analyzer_IsNotActive, 1) = 1
						limit 1
					) or (
						mst.MedServiceType_SysNick != 'lab'
					))
				group by
					l.Lpu_Nick,
					ms.Lpu_id,
					ms.MedService_id,
					ms.MedService_Name,
					ms.MedService_Nick
				limit 1000)
					
				union
				
				(select
					ms.MedService_id as \"UniqueKey_id\",
					l.Lpu_Nick as \"Lpu_Nick\",
					ms.Lpu_id as \"Lpu_id\",
					ms.MedService_id as \"MedService_id\",
					ms.MedService_Name as \"MedService_Name\",
					ms.MedService_Nick as \"MedService_Nick\",
					ms.MedService_id as \"Group_id\"
				from v_MedService ms
				left join v_LpuUnit_ER lu on lu.LpuUnit_id = ms.LpuUnit_id
				left join Address lua on lu.Address_id = lua.Address_id
				left join v_Lpu l on ms.Lpu_id = l.Lpu_id
				left join v_pmUser pu on ms.pmUser_updId = pu.pmUser_id
				left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
				left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
				inner join v_UslugaComplexMedService ucms on ucms.MedService_id = ms.MedService_id
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
				inner join v_UslugaComplexResource ucr on ucr.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				inner join v_Resource r on r.Resource_id = ucr.Resource_id
				{$join}
				where
					(coalesce(cast(ms.MedService_endDT as date), '2030-01-01') > :Date)
					and mst.MedServiceType_SysNick in ({$MedServiceTypeWithResources})	--список служб с ресурасами
					and cast(r.Resource_begDT as date) <= :Date
					and (r.Resource_endDT is null or cast(r.Resource_endDT as date) > :Date)
					and cast(uc.UslugaComplex_begDT as date) <= :Date
					and (uc.UslugaComplex_endDT is null or cast(uc.UslugaComplex_endDT as date) > :Date)
					{$filter}
				group by
					l.Lpu_Nick,
					ms.Lpu_id,
					ms.MedService_id,
					ms.MedService_Name,
					ms.MedService_Nick
					
				order by
					ms.MedService_id ASC,
					ms.MedService_Nick ASC
				limit 1000)
			";
			//echo getDebugSQL($sql, $params); exit;
		} else {
			if (!empty($data['MedService_id'])) {
				$params['MedService_id'] = $data['MedService_id'];
				$filter .= " and ms.MedService_id = :MedService_id";
			}

			$sql = "
				(select
					ms.MedService_id as \"Group_id\",
					cast(ms.MedService_id as varchar)
						|| '_' || coalesce(cast(ucms.UslugaComplexMedService_id as varchar), '')
						|| '_' || 'null'
					as \"UniqueKey_id\",
					l.Lpu_Nick as \"Lpu_Nick\",
					l.Lpu_f003mcod as \"Lpu_f003mcod\",
					ms.Lpu_id as \"Lpu_id\",
					ms.MedService_id as \"MedService_id\",
					ms.MedServiceType_id as \"MedServiceType_id\",
					ms.MedService_Name as \"MedService_Name\",
					ms.MedService_Nick as \"MedService_Nick\",
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
					coalesce(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
					null as \"UslugaComplexResource_id\",
					null as \"Resource_id\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					'' as \"MedService_Descr\",
					rtrim(pmUser_Name) as \"pmuser_name\",
					ms.MedService_updDT as \"MedService_updDT\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					ms.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					1 as \"allowDirection\"
				from v_MedService ms
				left join v_UslugaComplexMedService ucms on ucms.MedService_id = ms.MedService_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
				left join v_LpuUnit_ER lu on lu.LpuUnit_id = ms.LpuUnit_id
				left join Address lua on lu.Address_id = lua.Address_id
				left join v_Lpu l on ms.Lpu_id = l.Lpu_id
				left join v_pmUser pu on ms.pmUser_updId = pu.pmUser_id
				left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
				left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
				{$join}
				where
					(coalesce(cast(ms.MedService_endDT as date), '2030-01-01') > :Date)
					and ucms.UslugaComplexMedService_pid is null
					and mst.MedServiceType_SysNick not in ({$MedServiceTypeWithResources}) --список служб c ресурсами
					and (ucms.UslugaComplexMedService_endDT is null or ucms.UslugaComplexMedService_endDT >= :Date)
					{$filter}
					and (
						exists(
						select Analyzer.Analyzer_id
						from lis.v_AnalyzerTest AnalyzerTest
						inner join lis.v_Analyzer Analyzer on Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
						where AnalyzerTest.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						and coalesce(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1 and coalesce(Analyzer.Analyzer_IsNotActive, 1) = 1
						limit 1
					) or (
						mst.MedServiceType_SysNick != 'lab'
					))
				limit 1000)
									
				union all

				(select
					ms.MedService_id as \"Group_id\",
					cast(ms.MedService_id as varchar)
						|| '_' || coalesce(cast(ucms.UslugaComplexMedService_id as varchar), '')
						|| '_' || coalesce(cast(ucr.UslugaComplexResource_id as varchar),'')
					as \"UniqueKey_id\",
					l.Lpu_Nick as \"Lpu_Nick\",
					l.Lpu_f003mcod as \"Lpu_f003mcod\",
					ms.Lpu_id as \"Lpu_id\",
					ms.MedService_id as \"MedService_id\",
					ms.MedServiceType_id as \"MedServiceType_id\",
					r.Resource_Name as \"MedService_Name\",
					ms.MedService_Nick as \"MedService_Nick\",
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
					coalesce(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
					ucr.UslugaComplexResource_id as \"UslugaComplexResource_id\",
					r.Resource_id as \"Resource_id\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					'' as \"MedService_Descr\",
					rtrim(pmUser_Name) as \"pmuser_name\",
					ms.MedService_updDT as \"MedService_updDT\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					ms.LpuSection_id as \"LpuSection_id\",
					ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					1 as \"allowDirection\"
				from v_MedService ms
				left join v_LpuUnit_ER lu on lu.LpuUnit_id = ms.LpuUnit_id
				left join Address lua on lu.Address_id = lua.Address_id
				left join v_Lpu l on ms.Lpu_id = l.Lpu_id
				left join v_pmUser pu on ms.pmUser_updId = pu.pmUser_id
				left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
				left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
				inner join v_UslugaComplexMedService ucms on ucms.MedService_id = ms.MedService_id
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
				inner join v_UslugaComplexResource ucr on ucr.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				inner join v_Resource r on r.Resource_id = ucr.Resource_id
				{$join}
				where
					(coalesce(cast(ms.MedService_endDT as date), '2030-01-01') > :Date)
					and mst.MedServiceType_SysNick in ({$MedServiceTypeWithResources})	--список служб с ресурасами
					and cast(r.Resource_begDT as date) <= :Date
					and (r.Resource_endDT is null or cast(r.Resource_endDT as date) > :Date)
					and cast(uc.UslugaComplex_begDT as date) <= :Date
					and (uc.UslugaComplex_endDT is null or cast(uc.UslugaComplex_endDT as date) > :Date)
					{$filter}
					
				order by
					ms.MedService_id ASC,
					ms.MedService_Nick ASC,
					\"UslugaComplex_Name\" ASC,
					\"MedService_Name\"
				limit 1000)
			";
		}

		$resp = $this->queryResult($sql, $params);
		if (empty($resp)) {
			return $resp;
		}

		if (empty($data['groupByMedService']) || !empty($data['MedService_id'])) {
			$ResourceIds = array();
			$ResourceLpuIds = array();
			$UslugaComplexMedServiceIds = array();
			$UslugaComplexMedServiceLpuIds = array();
			$MedServiceIds = array();
			$MedServiceLpuIds = array();
			foreach($resp as $respone) {
				if ($respone['allowDirection']) {
					if (!empty($respone['Resource_id']) && !in_array($respone['Resource_id'], $ResourceIds)) {
						$ResourceIds[] = $respone['Resource_id'];
						$ResourceLpuIds[$respone['Resource_id']] = $respone['Lpu_id'];
					}
					if (!empty($respone['UslugaComplexMedService_id']) && !in_array($respone['UslugaComplexMedService_id'], $UslugaComplexMedServiceIds)) {
						$UslugaComplexMedServiceIds[] = $respone['UslugaComplexMedService_id'];
						$UslugaComplexMedServiceLpuIds[$respone['UslugaComplexMedService_id']] = $respone['Lpu_id'];
					}
					if (!empty($respone['MedService_id']) && !in_array($respone['MedService_id'], $MedServiceIds)) {
						$MedServiceIds[] = $respone['MedService_id'];
						$MedServiceLpuIds[$respone['MedService_id']] = $respone['Lpu_id'];
					}
				}
			}

			// тянем первое свободное время для всех служб/ресурсов/услуг
			$ResourceDates = array();
			if (!empty($ResourceIds)) {
				$ResourceDates = $this->GetResourceDates($data, $ResourceIds, $ResourceLpuIds);
			}
			$UslugaComplexMedServiceDates = array();
			if (!empty($UslugaComplexMedServiceIds)) {
				$UslugaComplexMedServiceDates = $this->GetMedServiceUslugaComplexDates($data, $UslugaComplexMedServiceIds, $UslugaComplexMedServiceLpuIds);
			}
			$MedServiceDates = array();
			if (!empty($MedServiceIds)) {
				$MedServiceDates = $this->GetMedServiceDates($data, $MedServiceIds, $MedServiceLpuIds);
			}

			foreach($resp as &$row) {
				// Примечания к службам
				If ($row['MedService_Descr'] != "") {
					if ($row['pmuser_name'] != "") {
						$row['Comments'] = "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($row['MedService_Descr'])) . "<hr><font class=\'smallfont\'>" . nl2br(htmlspecialchars($row['pmuser_name'])) . ", " . ConvertDateFormat($row['MedService_updDT'], "H:i d.m.y") . "</font>\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> ";
					} else {
						$row['Comments'] = "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($row['MedService_Descr'])) . "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\">";
					}
				} else {
					$row['Comments'] = "";
				};

				$row['Queue'] = "";
				$row['Dates'] = false;

				$useMedService = 0;
				switch (true) {
					case !empty($row['UslugaComplexResource_id']):
						// Ресурс
						$row['MedService_Caption'] = $row['MedService_Name'];
						if (isset($ResourceDates[$row['Resource_id']])) {
							$rec = $ResourceDates[$row['Resource_id']];
							$rec['dates'] = implode(" ", $rec['dates']);
						} else {
							$rec = $this->GetEmptyDates();
						}
						break;

					case !empty($row['UslugaComplexMedService_id']):
						// Услуга
						$row['MedService_Caption'] = $row['MedService_Name'];
						if (isset($UslugaComplexMedServiceDates[$row['UslugaComplexMedService_id']])) {
							$rec = $UslugaComplexMedServiceDates[$row['UslugaComplexMedService_id']];
							$rec['dates'] = implode(" ", $rec['dates']);
							if ($rec['count'] == 0) {
								if (isset($MedServiceDates[$row['MedService_id']])) {
									$rec = $MedServiceDates[$row['MedService_id']];
									$rec['dates'] = implode(" ", $rec['dates']);
								} else {
									$rec = $this->GetEmptyDates();
								}
								$useMedService = 1;
							}
						} else {
							$rec = $this->GetEmptyDates();
						}
						break;

					default:
						// Служба
						$row['MedService_Caption'] = $row['MedService_Name'];
						if (isset($MedServiceDates[$row['MedService_id']])) {
							$rec = $MedServiceDates[$row['MedService_id']];
							$rec['dates'] = implode(" ", $rec['dates']);
						} else {
							$rec = $this->GetEmptyDates();
						}
				}
				if (is_array($rec) && isset($rec['dates'])) {
					$row['Dates'] = $rec['dates'];
					$row['useMedService'] = $useMedService;
				}
			}
		}

		return $resp;
	}

	/**
	 * Заглушка, когда не нужно отображать список дат
	 */
	function GetEmptyDates() {
		return array('count'=>0, 'dates'=>'------------------------------');
	}

	/**
	 * Получение списка дат с раскраской для приема для служб
	 */
	function GetMedServiceDates($data, $MedServiceIds, $MedServiceLpuIds) {
		$maxDays = $this->GetMaxMedServiceDayCount();
		if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;

		$FirstDay = TimeToDay(strtotime($data['Date']));
		$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит

		$sql = "
			select
				msd.MedService_id as \"MedService_id\",
				msd.Day_id as \"Day_id\",
				msd.MedServiceDay_FreeRec as \"MedServiceDay_FreeRec\",
				msd.MedServiceDay_ReservRec as \"MedServiceDay_ReservRec\",
				msd.MedServiceDay_PayRec as \"MedServiceDay_PayRec\"
			from
				MedServiceDay msd
			where
				msd.MedService_id IN ('" . implode("','", $MedServiceIds) . "')
				and msd.Day_id >= :StartDay
				and msd.Day_id < :EndDay
			order by
				msd.Day_id
		";

		$resp = $this->queryResult($sql, array(
			'StartDay' => $FirstDay,
			'EndDay' => $LastDay
		));

		$dates = array();
		$IndiviDualPeriod = array();
		if( $data['ARMType'] == 'regpol' ) {
			$IndiviDualPeriod = $this->lipmodel->getObjectIndividualPeriod($data, 'MedService');
		}

		foreach($MedServiceIds as $MedService_id) {
			$template = array();
			$indLastDay = $LastDay;
			if( !empty($IndiviDualPeriod[$MedService_id]) ) {
				$indLastDay = ($IndiviDualPeriod[$MedService_id] > 14) ? $FirstDay + 14 : $FirstDay + $IndiviDualPeriod[$MedService_id];
			}
			for ($Day = $FirstDay; $Day < $indLastDay; $Day++) {
				$template[$Day] = "<span style='background-color: #ffdddd; font-size: 12px; margin-bottom: 2px;'>" . date('d.m', DayMinuteToTime($Day, 0)) . "</span>";
			}
			    $dates[$MedService_id]['dates'] = $template;
			    $dates[$MedService_id]['count'] = 0;
		    }

		if (!empty($resp)) {
			foreach ($resp as $key => $row) {
				if( !empty($IndiviDualPeriod[$row['MedService_id']]) ) {
					$maxDays = ($IndiviDualPeriod[$row['MedService_id']] > 14) ? 14 : $IndiviDualPeriod[$row['MedService_id']];
				} else {
					$maxDays = $this->GetPolDayCount($MedServiceLpuIds[$row['MedService_id']]);
				}
				if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
				$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит
				if ($row['Day_id'] >= $LastDay) {
					continue;
				}

				If ( $row['MedServiceDay_FreeRec'] != "" ) {
					$sColor = '#ddffdd';
				} else {
					If ( $row['MedServiceDay_ReservRec'] != "" )
						$sColor = 'yellow';
					else
					If ( $row['MedServiceDay_PayRec'] != "" )
						$sColor = '#ff99ff';
					else
					If ( $row['MedServiceDay_FreeRec'] == "" && $row['MedServiceDay_ReservRec'] == "" && $row['MedServiceDay_PayRec'] == "" )
						$sColor = '#ffdddd';
					else
						$sColor = '#ffdddd';
				}
				$dates[$row['MedService_id']]['dates'][$row['Day_id']] = "<span style='background-color: {$sColor}; font-size: 12px;'>" . date('d.m', DayMinuteToTime($row['Day_id'], 0)) . "</span>";
				$dates[$row['MedService_id']]['count']++;
			}
		}

		return $dates;
	}

	/**
	 * Получение списка дат с раскраской для приема для услуг на службах
	 */
	function GetMedServiceUslugaComplexDates($data, $UslugaComplexMedServiceIds, $UslugaComplexMedServiceLpuIds) {
		$maxDays = $this->GetMaxMedServiceDayCount();
		if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;

		$FirstDay = TimeToDay(strtotime($data['Date']));
		$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит

		$sql = "
			select
				msd.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				msd.Day_id as \"Day_id\",
				msd.MedServiceDay_FreeRec as \"MedServiceDay_FreeRec\",
				msd.MedServiceDay_ReservRec as \"MedServiceDay_ReservRec\",
				msd.MedServiceDay_PayRec as \"MedServiceDay_PayRec\"
			from
				MedServiceDay msd
			where
				msd.UslugaComplexMedService_id IN ('" . implode("','", $UslugaComplexMedServiceIds) . "')
				and msd.Day_id >= :StartDay
				and msd.Day_id < :EndDay
			order by
				msd.Day_id
		";

		$resp = $this->queryResult($sql, array(
			'StartDay' => $FirstDay,
			'EndDay' => $LastDay
		));

		$dates = array();
		$template = array();

		for ($Day = $FirstDay; $Day < $LastDay; $Day++) {
			$template[$Day] = "<span style='background-color: #ffdddd; font-size: 12px; margin-bottom: 2px;'>" . date('d.m', DayMinuteToTime($Day, 0)) . "</span>";
		}

		foreach($UslugaComplexMedServiceIds as $UslugaComplexMedService_id) {
			$dates[$UslugaComplexMedService_id]['dates'] = $template;
			$dates[$UslugaComplexMedService_id]['count'] = 0;
		}

		if (!empty($resp)) {
			foreach ($resp as $key => $row) {
				$maxDays = $this->GetPolDayCount($UslugaComplexMedServiceLpuIds[$row['UslugaComplexMedService_id']]);
				if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
				$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит
				if ($row['Day_id'] >= $LastDay) {
					continue;
				}

				If ( $row['MedServiceDay_FreeRec'] != "" ) {
					$sColor = '#ddffdd';
				} else {
					If ( $row['MedServiceDay_ReservRec'] != "" )
						$sColor = 'yellow';
					else
					If ( $row['MedServiceDay_PayRec'] != "" )
						$sColor = '#ff99ff';
					else
					If ( $row['MedServiceDay_FreeRec'] == "" && $row['MedServiceDay_ReservRec'] == "" && $row['MedServiceDay_PayRec'] == "" )
						$sColor = '#ffdddd';
					else
						$sColor = '#ffdddd';
				}
				$dates[$row['UslugaComplexMedService_id']]['dates'][$row['Day_id']] = "<span style='background-color: {$sColor}; font-size: 12px;'>" . date('d.m', DayMinuteToTime($row['Day_id'], 0)) . "</span>";
				$dates[$row['UslugaComplexMedService_id']]['count']++;
			}
		}

		return $dates;
	}

	/**
	 *
	 * @param type $data
	 * @param type $Resource_id
	 * @return type
	 */
	function GetResourceDates($data, $ResourceIds, $ResourceLpuIds) {
		$maxDays = $this->GetMaxMedServiceDayCount();
		if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;

		$FirstDay = TimeToDay(strtotime($data['Date']));
		$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит

		$sql = "
			select
				rd.Resource_id as \"Resource_id\",
				rd.Day_id as \"Day_id\",
				rd.ResourceDay_FreeRec as \"ResourceDay_FreeRec\",
				rd.ResourceDay_ReservRec as \"ResourceDay_ReservRec\",
				rd.ResourceDay_PayRec as \"ResourceDay_PayRec\"
			from
				ResourceDay rd
			where
				rd.Resource_id IN ('" . implode("','", $ResourceIds) . "')
				and rd.Day_id >= :StartDay
				and rd.Day_id < :EndDay
			order by
				rd.Day_id
		";

		$resp = $this->queryResult($sql, array(
			'StartDay' => $FirstDay,
			'EndDay' => $LastDay
		));

		$dates = array();
		$template = array();

		for ($Day = $FirstDay; $Day < $LastDay; $Day++) {
			$template[$Day] = "<span style='background-color: #ffdddd; font-size: 12px; margin-bottom: 2px;'>" . date('d.m', DayMinuteToTime($Day, 0)) . "</span>";
		}

		foreach($ResourceIds as $Resource_id) {
			$dates[$Resource_id]['dates'] = $template;
			$dates[$Resource_id]['count'] = 0;
		}

		if (!empty($resp)) {
			foreach ($resp as $key => $row) {
				$maxDays = $this->GetPolDayCount($ResourceLpuIds[$row['Resource_id']]);
				if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
				$LastDay = $FirstDay + (!empty($maxDays) ? $maxDays : 14); // если не ограничено, но на форме выводить больше 14 не нужно, т.к. не влазит
				if ($row['Day_id'] >= $LastDay) {
					continue;
				}

				If ($row['ResourceDay_FreeRec'] != "") {
					$sColor = '#ddffdd';
				} else {
					If ($row['ResourceDay_ReservRec'] != "")
						$sColor = 'yellow';
					else
						If ($row['ResourceDay_PayRec'] != "")
							$sColor = '#ff99ff';
						else
							If ($row['ResourceDay_FreeRec'] == "" && $row['ResourceDay_ReservRec'] == "" && $row['ResourceDay_PayRec'] == "")
								$sColor = '#ffdddd';
							else
								$sColor = '#ffdddd';
				}
				$dates[$row['Resource_id']]['dates'][$row['Day_id']] = "<span style='background-color: {$sColor}; font-size: 12px;'>" . date('d.m', DayMinuteToTime($row['Day_id'], 0)) . "</span>";
				$dates[$row['Resource_id']]['count']++;
			}
		}

		return $dates;
	}

	/**
	 * Выбирает наиболее подходящее подразделение для записи
	 * Если передан идентификатор человека, то выбирается подразделение, где находится его участковый врач
	 * Если человек не передан, то по переданному идентификатору службы выбирается подразделение, где находится регистратура
	 * Если служба не привязана к подразделению, то выбирается первое поликлиническое подразделение в ЛПУ
	 */
	function getAppropriateLpuUnit( $data ) {

		if ( isset($data['Person_id']) ) {
			// Будем пытаться искать прикрепление человека
			$sql = "
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				from LpuRegionStreet lrs
				left join v_MedStaffRegion msr on msr.LpuRegion_id = lrs.LpuRegion_id 
				left join v_Medstafffact_ER msf on
					msf.MedPersonal_id = msr.MedPersonal_id
					and msf.Lpu_id = msr.Lpu_id
					and coalesce(RecType_id, 1) not in (5,6)
					and (msf.MedStaffFact_disDate is null or cast(msf.MedStaffFact_disDate as date)>dbo.tzGetDate())
				left join LpuSection ls on ls.LpuSection_id = msf.LpuSection_id 
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				where
					KLStreet_id = (
						select
							KLStreet_id
						from v_PersonState ps
						left join v_Address a on coalesce(ps.PAddress_id, ps.UAddress_id) = a.Address_id
						where
							Person_id = :Person_id
					)
					and lsp.LpuSectionProfile_IsArea = 2 -- только широкие профиля
					and lu.Lpu_id = :Lpu_id
					and lu.LpuUnitType_id = 2
				limit 1
			";

			$res = $this->db->query(
				            $sql, array(
					    'Lpu_id' => $data['Lpu_id'],
					    'Person_id' => $data['Person_id']
				            )
                    )->result('array');
			if ( isset($res[0]['LpuUnit_id']) ) {
				return $res;
			}
		}

		// Либо человек не был задан, либо по нему не нашлось подразделения
		// Ищем подразделение, где находится служба
		$sql = "
			select
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			from MedService ms
			left join v_LpuUnit lu on ms.LpuUnit_id = lu.LpuUnit_id
			where MedService_id = :MedService_id";

		$res = $this->db->query(
			            $sql, array(
                    'MedService_id' => $data['MedService_id'],
                        )
                )->result('array');

		if ( isset($res[0]['LpuUnit_id']) ) {
			return $res;
		} else {
			// Возвращаем первое по списку подразделение
			//
			$sql = "
			select
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			from v_LpuUnit lu
			where Lpu_id = :Lpu_id
			order by case when LpuUnitType_id = 2 then 0 else LpuUnitType_id end asc
			limit 1
			";

			$res = $this->db->query(
				            $sql, array(
					    'Lpu_id' => $data['Lpu_id'],
				            )
			        )->result('array');

			if ( isset($res[0]['LpuUnit_id']) ) {
				return $res;
			}
			// Возвращаем, что подходящее подразделение не найдено
			/* return array(
			  'LpuUnit_id' => null,
			  "LpuUnitType_SysNick" => "polka",
			  "success" => true
			  ); */
		}
	}

	/**
	 * Получение расписания у всех врачей подразделения на заданный день
	 */
	function getLpuUnitSchedule( $data ) {

		$param = array(
			'LpuUnit_id' => $data['LpuUnit_id'],
			'TimetableGraf_begTime' => date('Y-m-d', strtotime($data['Date']))
		);

		$sql = "
			select
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					rtrim(p.Person_Surname) || coalesce(' ' || rtrim(p.Person_Firname), '') || coalesce(' ' || rtrim(p.Person_Secname), '') as \"Person_FIO\",
					to_char(p.Person_BirthDay, 'dd.mm.yyyy HH24:MI:SS') as \"Person_BirthDay\",
					p.Person_Phone as \"Person_Phone\",
					p.PersonInfo_InternetPhone as \"Person_InetPhone\",
					case when a1.Address_id is not null
						then 
							a1.Address_Address
						else
							a.Address_Address
						end as \"Address_Address\",
					case when a1.Address_id is not null
						then 
							a1.KLTown_id
						else
							a.KLTown_id
						end as \"KLTown_id\",
					case when a1.Address_id is not null
						then 
							a1.KLStreet_id
						else
							a.KLStreet_id
						end as \"KLStreet_id\",
					case when a1.Address_id is not null
						then 
							a1.Address_House
						else
							a.Address_House
						end as \"Address_House\",
					t.pmUser_updID as \"pmUser_updID\",
					to_char(t.TimetableGraf_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableGraf_updDT\",
					t.TimetableGraf_id as \"TimetableGraf_id\",
					t.Person_id as \"Person_id\",
					t.TimetableGraf_Day as \"TimetableGraf_Day\",
					to_char(TimetableGraf_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableGraf_begTime\",
					t.TimetableType_id as \"TimetableType_id\",
					t.TimetableGraf_IsDop as \"TimetableGraf_IsDop\",
					p.PrivilegeType_id as \"PrivilegeType_id\",
					rtrim(p.Person_Firname) as \"Person_Firname\",
					rtrim(p.Person_Surname) as \"Person_Surname\",
					rtrim(p.Person_Secname) as \"Person_Secname\",
					t.PMUser_UpdID,
					case 
						when t.pmUser_updid=999000
						then 'Запись через КМИС'
						when t.pmUser_updid between 1000000 and 5000000
						then 'Запись через интернет'
						else u.PMUser_Name 
					end as \"PMUser_Name\", 
					j.Job_Name as \"Job_Name\",
					lpu.Lpu_Nick as \"Lpu_Nick\",
					lpud.Lpu_Nick as \"DirLpu_Nick\",
					d.Direction_Num as \"Direction_Num\",
					to_char(d.Direction_setDate, 'dd.mm.yyyy') as \"Direction_Date\",
					d.Direction_id as \"Direction_id\",
					qp.pmUser_Name as \"QpmUser_Name\",
					q.EvnQueue_insDT as \"EvnQueue_insDT\",
					dg.Diag_Code as \"Diag_Code\",
					u.Lpu_id as \"pmUser_Lpu_id\",
					t.TimetableGraf_IsModerated as \"TimetableGraf_IsModerated\",
					msf.Lpu_id as \"Lpu_id\",
					msf.MedPersonal_FIO as \"MedPersonal_FIO\",
					msf.LpuSection_Name as \"LpuSection_Name\",
					msf.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from v_TimetableGraf_lite t
				left outer join v_MedStaffFact_ER msf on msf.MedStaffFact_id = t.MedStaffFact_id
				left outer join v_LpuSection_ER ls on msf.LpuSection_Id = ls.LpuSection_Id
				left outer join v_Person_ER p on t.Person_id = p.Person_id
				left outer join Address a on p.UAddress_id = a.Address_id
				left outer join Address a1 on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
				left outer join v_Job_ER j on p.Job_id=j.Job_id
				left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
				left outer join v_Lpu lpu on lpu.Lpu_id = p.Lpu_id
				left outer join v_Direction_ER d on t.TimetableGraf_id=d.TimetableGraf_id and d.DirFailType_id is null and d.Person_id = t.Person_id
				left outer join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q on t.TimetableGraf_id = q.TimetableGraf_id and t.Person_id = q.Person_id
				left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
				left join Diag dg on dg.Diag_id=d.Diag_id
				where msf.LpuUnit_id = :LpuUnit_id
					and cast(t.TimetableGraf_begTime as date) = :TimetableGraf_begTime
					and TimetableGraf_begTime is not null
				order by msf.MedStaffFact_id, t.TimetableGraf_begTime";

		$res = $this->db->query($sql, $param);

		$ttgdata = $res->result('array');

		$outdata = array();
		foreach ( $ttgdata as $ttg ) {
			$outdata[$ttg['MedStaffFact_id']]['MedPersonal_FIO'] = $ttg['MedPersonal_FIO'];
			$outdata[$ttg['MedStaffFact_id']]['LpuSection_Name'] = $ttg['LpuSection_Name'];
			$outdata[$ttg['MedStaffFact_id']]['LpuSectionProfile_Name'] = $ttg['LpuSectionProfile_Name'];

			$rec = array(
				'Person_id' => $ttg['Person_id'],
				'Person_FIO' => $ttg['Person_FIO'],
				'Person_BirthDay' => (isset($ttg['Person_BirthDay']) ? ConvertDateFormat($ttg['Person_BirthDay'],'d.m.Y') : ''),
				'Person_Address' => $ttg['Address_Address'],
				'TimetableGraf_begTime' => ConvertDateFormat($ttg['TimetableGraf_begTime'],'H:i')
			);

			$outdata[$ttg['MedStaffFact_id']]['schedule'][] = $rec;
		}

		return $outdata;
	}

	/**
	 * Получение списка типов направлений
	 */
	function getDirTypeList( $data ) {
		$filter='';
		$params=array();
		if($data['isDead']){
			$filter.=' and dt.DirType_id in (7, 18)';
		} else {
			$filter.=' and dt.DirType_id not in (7, 18, 19, 20, 21)';
		}

		if (getRegionNick() != 'buryatiya') {
			$filter.=' and dt.DirType_id != 22'; // На консультацию в другую МИС
		}

		$sql = "
			select 
				DirType_id as \"DirType_id\",
				DirType_Code as \"DirType_Code\",
				DirType_Name as \"DirType_Name\"
			from v_DirType dt
			where (1=1) and dt.DirType_id != 24 ".$filter."
			order by dt.DirType_SortID";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка участков
	 */
	function getRegionsList( $data ) {
		$sql = "
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
                msf.Person_Fin as \"Person_Fin\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
                lrt.LpuRegionType_Name as \"LpuRegionType_Name\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				a.Address_Address as \"LpuUnit_Address\",
				l.Lpu_Nick as \"Lpu_Nick\",
				(
                	select
                		string_agg(lrs.LpuRegionStreet_HouseSet, ',')
                	from LpuRegionStreet lrs 
                    where 
                        lrs.LpuRegion_id = lr.LpuRegion_id and KLStreet_id = :KLStreet_id
                ) as \"LpuRegionStreet_HouseSet\",
				lr.LpuRegion_id as \"LpuRegion_id\"
			from v_LpuRegion lr
				left join v_LpuRegionType lrt on lr.LpuRegionType_id = lrt.LpuRegionType_id
				inner join v_MedStaffRegion msr on msr.LpuRegion_id = lr.LpuRegion_id
			    inner join v_MedStaffFact msf on msf.MedPersonal_id = msr.MedPersonal_id and msf.Lpu_id = msr.Lpu_id
			    inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			    inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id and LpuSectionProfile_IsArea = 2
				left join v_LpuUnit lu on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_Address a on a.Address_id = lu.Address_id
				left join v_Lpu l on lu.Lpu_id = l.Lpu_id
			where lu.LpuUnitType_id = 2
				and exists(select KLStreet_id from LpuRegionStreet lrs where lrs.LpuRegion_id = lr.LpuRegion_id and KLStreet_id = :KLStreet_id)
				/*and coalesce(msf.RecType_id, 6) not in (2,5,6,8)*/
                and coalesce(msf.WorkData_endDate, '2030-01-01') >= dbo.tzGetDate()
                and coalesce(msr.MedStaffRegion_endDate,'2030-01-01') >= dbo.tzGetDate()
			order by
                l.Lpu_id,
				cast(LpuRegion_Name as int)";
		$res = $this->db->query(
			    $sql, array(
			'KLStreet_id' => $data['KLStreet_id'],
			    )
		);
		/* echo getDebugSQL($sql,
		  array(
		  'KLStreet_id' => $data['KLStreet_id'],
		  )); */
		if ( is_object($res) ) {
			$res = $res->result('array');
			$result = array();
			foreach ( $res as $row ) {
				if ( ( $data['Address_House'] == '' ) || HouseMatchRange($data['Address_House'], trim($row['LpuRegionStreet_HouseSet'])) )
					$result[] = $row;
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получение времени открытия нового дня
	 */
	function getShowNewDayTime() {
		return (!empty($_SESSION['setting']) && !empty($_SESSION['setting']['server']) && !empty($_SESSION['setting']['server']['promed_new_day_time'])) ? $_SESSION['setting']['server']['promed_new_day_time'] : '17:00';
	}

	/**
	 * Получение времени запрета записи на завтра
	 */
	function getCloseNextDayRecordTime() {
		return (!empty($_SESSION['setting']) && !empty($_SESSION['setting']['server']) && !empty($_SESSION['setting']['server']['promed_close_next_day_record_time'])) ? $_SESSION['setting']['server']['promed_close_next_day_record_time'] : '17:00';
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись в другую МО
	 */
	function GetPortalDayCount() {
		return (!empty($_SESSION['setting']) && !empty($_SESSION['setting']['server']) && !empty($_SESSION['setting']['server']['portal_record_day_count'])) ? $_SESSION['setting']['server']['portal_record_day_count'] : 14;
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись в поликлинику
	 */
	function GetMaxPolDayCount() {
		$maxDayCount = 14;
		if (!empty($_SESSION['setting']['server']['pol_record_day_count']) && $_SESSION['setting']['server']['pol_record_day_count'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['pol_record_day_count'];
		}
		if (!empty($_SESSION['setting']['server']['pol_record_day_count_reg']) && $_SESSION['setting']['server']['pol_record_day_count_reg'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['pol_record_day_count_reg'];
		}
		if (!empty($_SESSION['setting']['server']['pol_record_day_count_cc']) && $_SESSION['setting']['server']['pol_record_day_count_cc'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['pol_record_day_count_cc'];
		}
		if (!empty($_SESSION['setting']['server']['pol_record_day_count_own']) && $_SESSION['setting']['server']['pol_record_day_count_own'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['pol_record_day_count_own'];
		}
		if (!empty($_SESSION['setting']['server']['pol_record_day_count_other']) && $_SESSION['setting']['server']['pol_record_day_count_other'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['pol_record_day_count_other'];
		}
		return $maxDayCount;
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись в поликлинику
	 */
    function GetPolDayCount($lpu_id, $MedStaffFact_id = null) {
		if (empty($_SESSION['setting']) || empty($_SESSION['setting']['server'])) {
			return 14;

        } elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol' && $_SESSION['lpu_id'] == $lpu_id) { // Для регистратора запись в свою МО
            $this->load->model('LpuIndividualPeriod_model', 'lipmodel');
            $individualPeriod = $this->lipmodel->getObjectIndividualPeriod(array('Lpu_id' => $_SESSION['lpu_id']), 'MedStaffFact');

            if( !empty($MedStaffFact_id) && !empty($individualPeriod[$MedStaffFact_id]) ) {
                $maxDays = $individualPeriod[$MedStaffFact_id];
            } else {
                $maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count']) ? $_SESSION['setting']['server']['pol_record_day_count'] : 14;
            }
            return $maxDays;

		} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol') { // Для регистратора запись в чужую МО
			return !empty($_SESSION['setting']['server']['pol_record_day_count_reg']) ? $_SESSION['setting']['server']['pol_record_day_count_reg'] : 14;

		} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'callcenter') { // Для оператора call-центра
			return !empty($_SESSION['setting']['server']['pol_record_day_count_cc']) ? $_SESSION['setting']['server']['pol_record_day_count_cc'] : 14;

		} elseif ($_SESSION['lpu_id'] == $lpu_id) { // Для остальных пользовалелей запись в свою МО
			return !empty($_SESSION['setting']['server']['pol_record_day_count_own']) ? $_SESSION['setting']['server']['pol_record_day_count_own'] : 14;

		} else { // Для остальных пользовалелей запись в чужую МО
			return !empty($_SESSION['setting']['server']['pol_record_day_count_other']) ? $_SESSION['setting']['server']['pol_record_day_count_other'] : 14;
		}
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись в стационар
	 */
	function GetMaxStacDayCount() {
		$maxDayCount = 21;
		if (!empty($_SESSION['setting']['server']['stac_record_day_count']) && $_SESSION['setting']['server']['stac_record_day_count'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count'];
		}
		if (!empty($_SESSION['setting']['server']['stac_record_day_count_reg']) && $_SESSION['setting']['server']['stac_record_day_count_reg'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count_reg'];
		}
		if (!empty($_SESSION['setting']['server']['stac_record_day_count_own']) && $_SESSION['setting']['server']['stac_record_day_count_own'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count_own'];
		}
		if (!empty($_SESSION['setting']['server']['stac_record_day_count_other']) && $_SESSION['setting']['server']['stac_record_day_count_other'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count_other'];
		}
		return $maxDayCount;
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись в стационар
	 */
    function GetStacDayCount($lpu_id, $LpuSection_id = null) {
		$maxDayCount = 21;
		if (!empty($_SESSION['setting']['server']['stac_record_day_count']) && $_SESSION['setting']['server']['stac_record_day_count'] > $maxDayCount) {
            $this->load->model('LpuIndividualPeriod_model', 'lipmodel');
            $individualPeriod = $this->lipmodel->getObjectIndividualPeriod(array('Lpu_id' => $_SESSION['lpu_id']), 'LpuSection');

            if( !empty($LpuSection_id) && !empty($individualPeriod[$LpuSection_id]) ) {
                $maxDays = $individualPeriod[$LpuSection_id];
            } else {
                $maxDays = !empty($_SESSION['setting']['server']['stac_record_day_count']) ? $_SESSION['setting']['server']['stac_record_day_count'] : 21;
            }
            return $maxDays;
		}
		if (!empty($_SESSION['setting']['server']['stac_record_day_count_reg']) && $_SESSION['setting']['server']['stac_record_day_count_reg'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count_reg'];
		}
		if (!empty($_SESSION['setting']['server']['stac_record_day_count_own']) && $_SESSION['setting']['server']['stac_record_day_count_own'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count_own'];
		}
		if (!empty($_SESSION['setting']['server']['stac_record_day_count_other']) && $_SESSION['setting']['server']['stac_record_day_count_other'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['stac_record_day_count_other'];
		}
		return $maxDayCount;
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись на службы
	 */
	function GetMaxMedServiceDayCount() {
		$maxDayCount = 14;
		if (!empty($_SESSION['setting']['server']['medservice_record_day_count']) && $_SESSION['setting']['server']['medservice_record_day_count'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['medservice_record_day_count'];
		}
		if (!empty($_SESSION['setting']['server']['medservice_record_day_count_reg']) && $_SESSION['setting']['server']['medservice_record_day_count_reg'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['medservice_record_day_count_reg'];
		}
		if (!empty($_SESSION['setting']['server']['medservice_record_day_count_own']) && $_SESSION['setting']['server']['medservice_record_day_count_own'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['medservice_record_day_count_own'];
		}
		if (!empty($_SESSION['setting']['server']['medservice_record_day_count_other']) && $_SESSION['setting']['server']['medservice_record_day_count_other'] > $maxDayCount) {
			$maxDayCount = $_SESSION['setting']['server']['medservice_record_day_count_other'];
		}
		return $maxDayCount;
	}

	/**
	 * Возвращает на сколько дней вперед разрешена запись на службы
	 */
    function GetMedServiceDayCount($lpu_id, $MedService_id = null) {
        if (empty($_SESSION['setting']) || empty($_SESSION['setting']['server'])) { // Вынес отдельно, чтобы не повторять
			return null;

		} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol' && $_SESSION['lpu_id'] == $lpu_id) { // Для регистратора запись в свою МО
			$this->load->model('LpuIndividualPeriod_model', 'lipmodel');
			$individualPeriod = $this->lipmodel->getObjectIndividualPeriod(array('Lpu_id' => $_SESSION['lpu_id']), 'MedService');

			if( !empty($MedService_id) && !empty($individualPeriod[$MedService_id]) ) {
				$maxDays = $individualPeriod[$MedService_id];
			} else {
				$maxDays = !empty($_SESSION['setting']['server']['medservice_record_day_count']) ? $_SESSION['setting']['server']['medservice_record_day_count'] : null;
			}
			return $maxDays;

		} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol') { // Для регистратора запись в чужую МО
			return !empty($_SESSION['setting']['server']['medservice_record_day_count_reg']) ? $_SESSION['setting']['server']['medservice_record_day_count_reg'] : null;

		} elseif ($_SESSION['lpu_id'] == $lpu_id) { // Для остальных пользовалелей запись в свою МО
			return !empty($_SESSION['setting']['server']['medservice_record_day_count_own']) ? $_SESSION['setting']['server']['medservice_record_day_count_own'] : null;

		} else { // Для остальных пользовалелей запись в чужую МО
			return !empty($_SESSION['setting']['server']['medservice_record_day_count_other']) ? $_SESSION['setting']['server']['medservice_record_day_count_other'] : null;
		}
	}

	/**
	 * Загрузка грида "Записи пациента"
	 *
	 * Используется форма АРМ регистратора
	 *
	 * @param integer $_POST['Person_id'] Идентификатор человека
	 * @return array
	 * @author       Alexander Permyakov
	 */
	function getListByPerson( $data ) {
		$params = array('Person_id' => $data['Person_id']);
		$region_number = getRegionNumber();
		$addFields = ( in_array($region_number, array(2, 77)) ) ? ',Timetable.TimetableGraf_Mark as "TimetableGraf_Mark"' : ',-1 as "TimetableGraf_Mark"';
		$addFilters = '';
		$isVizit = ( $region_number == 77 ) ? 'coalesce(PersonMark.PersonMark_Status,0) = 1' : 'Timetable.TimetableGraf_factTime is not null or Timetable.Evn_id is not null';
		$isVizitJoin = ( $region_number == 77 ) ? 'left join PersonMark on PersonMark.PersonMark_id = Timetable.TimetableGraf_Mark' : '';
		// Если пользователь - не суперадмин, тогда добавляем фильтр по направившему ЛПУ
		// [2013-11-11] ... и если не пользователь Call-центра
		// https://redmine.swan.perm.ru/issues/27449
		// [2014-04-21] Меняем фильтр https://redmine.swan.perm.ru/issues/36104
		if ( !isSuperadmin() && !IsCZUser() ) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$addFilters = ' and (Lpu.Lpu_id = :Lpu_id or DLpu.Lpu_id = :Lpu_id)';
		}
		$histFields = ",rtrim(pm.PMUser_surName) ||' 
		'||upper(SUBSTRING ( pm.PMUser_firName from 1 for 1))||' 
		'||upper(SUBSTRING ( pm.PMUser_secName from 1 for 1)) as \"pmUser_Name\"
		,to_char(ED.EvnDirection_insDT, 'dd.mm.yyyy') as \"EvnDirection_insDT\"";

		$histDirFields = ",rtrim(pm.PMUser_surName) ||' 
		'||upper(SUBSTRING ( pm.PMUser_firName from 1 for 1))||' 
		'||upper(SUBSTRING ( pm.PMUser_secName from 1 for 1)) as \"pmUser_Name\"
		,to_char(coalesce(ED.EvnDirection_insDT,Timetable.TimetableGraf_updDT), 'dd.mm.yyyy') as \"EvnDirection_insDT\""
		;

		$histEQFields = ",rtrim(pm.PMUser_surName) ||' 
		'||upper(SUBSTRING ( pm.PMUser_firName from 1 for 1))||' 
		'||upper(SUBSTRING ( pm.PMUser_secName from 1 for 1)) as \"pmUser_Name\"
		,to_char(coalesce(ED.EvnDirection_insDT,EQ.EvnQueue_setDT), 'dd.mm.yyyy') as \"EvnDirection_insDT\""
		;

		$query = "
			select
				Timetable.Person_id as \"Person_id\",
				'TimetableGraf_'|| Timetable.TimetableGraf_id as \"Item_id\",
				'Запись' as \"RecType_Name\",
				MS.MedService_Name as \"MedService_Name\",
				uslc.UslugaComplex_Name as \"UslugaComplex_Name\",
				null as \"EvnQueue_id\",
				null as \"TimetableMedService_id\",
				Timetable.TimetableGraf_id as \"TimetableGraf_id\",
				null as \"TimetableStac_id\",
				null as \"EvnDirection_desDT\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				Lpu.Lpu_Nick as \"DLpu_Name\",
				coalesce(DLpuUnit.LpuUnit_Name, 'Поликлинника') as \"DLpuUnit_Name\",
				DLpuUnit.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(coalesce(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime), 'dd.mm.yyyy') as \"recDate\",
				to_char(coalesce(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime), 'HH24:MI:SS') as \"recTime\",
				coalesce(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as \"recSort\",
				case when {$isVizit} then 'true' else 'false' end as \"TimetableGraf_isVizit\",
				DLpu.Lpu_Nick as \"Lpu_Name\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MSF.Person_Fio as \"MedPersonal_Name\",
				MSF.MedPersonal_id as \"MedPersonal_id\"
				{$addFields}
				,DT.DirType_Code as \"DirType_Code\"
				,DT.DirType_Name as \"DirType_Name\"
				{$histDirFields}
				,pm.PMUser_id as \"pmUser_id\"
			from
				v_TimetableGraf_lite Timetable
				left join v_MedStaffFact MSF on Timetable.MedStaffFact_id = MSF.MedStaffFact_id
				left join v_LpuSection LS on MSF.LpuSection_id = LS.LpuSection_id
				left join v_EvnDirection_all ED on Timetable.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu DLpu on DLpu.Lpu_id = coalesce(ED.Lpu_did, MSF.Lpu_id)
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				--left join v_LpuUnit DLpuUnit on ED.LpuUnit_did = DLpuUnit.LpuUnit_id
				left join v_UslugaComplexMedService usl on usl.MedService_id = MS.MedService_id
				left join v_UslugaComplex uslc on uslc.UslugaComplex_id = usl.UslugaComplex_id
				left join v_LpuSection DLpuSection on DLpuSection.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit DLpuUnit on coalesce(ED.LpuUnit_did,MS.LpuUnit_id,DLpuSection.LpuUnit_id) = DLpuUnit.LpuUnit_id
				left join v_Lpu Lpu on Lpu.Lpu_id = ED.Lpu_sid
				left join v_DirType DT on DT.DirType_id = ED.DirType_id
				left join v_pmUserCache pm on pm.PMUser_id=coalesce(ED.pmUser_insID,Timetable.pmUser_updID)
				{$isVizitJoin}
			where
				Timetable.Person_id = :Person_id
				and cast(coalesce(Timetable.TimetableGraf_begTime, Timetable.TimetableGraf_factTime) as DATE) >= cast(dbo.tzGetDate() as DATE)
                {$addFilters}
			union all
			select
				EQ.Person_id as \"Person_id\",
				'EvnQueue_'|| EQ.EvnQueue_id as \"Item_id\",
				'Очередь' as \"RecType_Name\",
				MS.MedService_Name as \"MedService_Name\",
				uslc.UslugaComplex_Name as \"UslugaComplex_Name\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				null as \"TimetableMedService_id\",
				null as \"TimetableGraf_id\",
				null as \"TimetableStac_id\",
				to_char(ED.EvnDirection_desDT, 'dd.mm.yyyy') as \"EvnDirection_desDT\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				Lpu.Lpu_Nick as \"DLpu_Name\",
				DLpuUnit.LpuUnit_Name as \"DLpuUnit_Name\",
				DLpuUnit.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(coalesce(ED.EvnDirection_desDT,EQ.EvnQueue_setDT), 'dd.mm.yyyy') as \"recDate\",
				to_char(EQ.EvnQueue_setDT, 'HH24:MI:SS') as \"recTime\",
				EQ.EvnQueue_setDT as \"recSort\",
				'false' as \"TimetableGraf_isVizit\",
				DLpu.Lpu_Nick as \"Lpu_Name\",
				null as \"LpuSection_Name\",
				0  as \"LpuSectionProfile_id\",
				'' as \"MedPersonal_Name\",
				0  as \"MedPersonal_id\",
				-1 as \"TimetableGraf_Mark\",
				DT.DirType_Code as \"DirType_Code\",
				DT.DirType_Name as \"DirType_Name\"
				{$histEQFields}
				,pm.PMUser_id as \"pmUser_id\"
			from
			v_EvnQueue EQ
				left join v_EvnDirection_all ED on EQ.EvnQueue_id = ED.EvnQueue_id
				left join v_LpuSection DLpuSection on DLpuSection.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit DLpuUnit on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,DLpuSection.LpuUnit_id) = DLpuUnit.LpuUnit_id
				left join v_Lpu DLpu on coalesce(ED.Lpu_did,DLpuUnit.Lpu_id) = DLpu.Lpu_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_LpuSectionProfile LSP on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_UslugaComplexMedService usl on usl.MedService_id = MS.MedService_id
				left join v_UslugaComplex uslc on uslc.UslugaComplex_id = usl.UslugaComplex_id
				left join v_Lpu Lpu on EQ.Lpu_id = Lpu.Lpu_id
				left join v_DirType DT on DT.DirType_id = ED.DirType_id
				left join v_pmUserCache pm on coalesce(ED.pmUser_insID,EQ.pmUser_updID)=pm.PMUser_id
			where
				EQ.Person_id = :Person_id
				and EQ.EvnQueue_recDT is null
				and EQ.EvnQueue_failDT is null
                {$addFilters}
            union all
			select
				TMS.Person_id as \"Person_id\",
				'TimetableMedService_'|| TMS.TimetableMedService_id as \"Item_id\",
				'Услуга' as \"RecType_Name\",
				MS.MedService_Name as \"MedService_Name\",
				uslc.UslugaComplex_Name as \"UslugaComplex_Name\",
				null as \"EvnQueue_id\",
				TMS.TimetableMedService_id as \"TimetableMedService_id\",
				null as \"TimetableGraf_id\",
				null as \"TimetableStac_id\",
				null as \"EvnDirection_desDT\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				Lpu.Lpu_Nick as \"DLpu_Name\",
				coalesce(DLpuUnit.LpuUnit_Name, 'Параклиника' ) as \"DLpuUnit_Name\",
				DLpuUnit.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				MS.MedService_Name as \"LpuSectionProfile_Name\",
				to_char(TMS.TimetableMedService_begTime, 'dd.mm.yyyy') as \"recDate\",
				to_char(TMS.TimetableMedService_begTime, 'HH24:MI:SS') as \"recTime\",
				TMS.TimetableMedService_begTime as \"recSort\",
				'false' as \"TimetableGraf_isVizit\",
				DLpu.Lpu_Nick as \"Lpu_Name\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				'' as \"MedPersonal_Name\",
				0  as \"MedPersonal_id\",
				-1 as \"TimetableGraf_Mark\",
				DT.DirType_Code as \"DirType_Code\",
				DT.DirType_Name as \"DirType_Name\"
				{$histFields}
				,pm.PMUser_id as \"pmUser_id\"
			from
			   v_TimetableMedService_lite TMS
			    left join v_EvnDirection_all ED on TMS.EvnDirection_id = ED.EvnDirection_id
			    left join v_Lpu Lpu on ED.Lpu_id = Lpu.Lpu_id
			    left join v_Lpu DLpu on ED.Lpu_did = DLpu.Lpu_id
			    left join v_LpuUnit DLpuUnit on ED.LpuUnit_did = DLpuUnit.LpuUnit_id
			    left join v_MedService MS on ED.Medservice_id = MS.Medservice_id
			    left join v_UslugaComplexMedService usl on usl.MedService_id = MS.MedService_id
			    left join v_UslugaComplex uslc on uslc.UslugaComplex_id = usl.UslugaComplex_id
			    left join v_LpuSection LS on MS.LpuSection_id = LS.LpuSection_id
			    left join v_DirType DT on DT.DirType_id = ED.DirType_id
				left join v_pmUserCache pm on pm.PMUser_id=ED.pmUser_insID
			where
				TMS.Person_id = :Person_id
				and cast(TMS.TimetableMedService_begTime as DATE) >= cast(dbo.tzGetDate() as DATE)
                {$addFilters}
			union all
			select
				TTS.Person_id as \"Person_id\",
				'TimetableStac_'|| TTS.TimetableStac_id as \"Item_id\",
				'Запись на койку' as \"RecType_Name\",
				MS.MedService_Name as \"MedService_Name\",
				uslc.UslugaComplex_Name as \"UslugaComplex_Name\",
				null as \"EvnQueue_id\",
				null as \"TimetableMedService_id\",
				null as \"TimetableGraf_id\",
				TTS.TimetableStac_id as \"TimetableStac_id\",
				null as \"EvnDirection_desDT\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				Lpu.Lpu_Nick as \"DLpu_Name\",
				coalesce(DLpuUnit.LpuUnit_Name, 'Поликлинника') as \"DLpuUnit_Name\",
				DLpuUnit.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				to_char(TTS.TimetableStac_setDate, 'dd.mm.yyyy') as \"recDate\",
				'' as \"recTime\",
				null  as \"recSort\",
				'' as \"TimetableGraf_isVizit\",
				DLpu.Lpu_Nick as \"Lpu_Name\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				'' as \"MedPersonal_Name\",
				0 as \"MedPersonal_id\",
				-1 as \"TimetableGraf_Mark\",
				DT.DirType_Code as \"DirType_Code\",
				DT.DirType_Name as \"DirType_Name\"
				{$histFields}
				,pm.PMUser_id as \"pmUser_id\"
			from
				v_TimetableStac_lite TTS
				left join v_EvnDirection_all ED on ED.EvnDirection_id = TTS.EvnDirection_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_LpuSection LS on ED.LpuSection_id = LS.LpuSection_id
				left join v_LpuSection DLpuSection on ED.LpuSection_did = DLpuSection.LpuSection_id
				left join v_Lpu DLpu on coalesce(ED.Lpu_did, LS.Lpu_id )= DLpu.Lpu_id
				--left join v_LpuUnit DLpuUnit on ED.LpuUnit_did = DLpuUnit.LpuUnit_id
				left join v_UslugaComplexMedService usl on usl.MedService_id = MS.MedService_id
				left join v_UslugaComplex uslc on uslc.UslugaComplex_id = usl.UslugaComplex_id
				left join v_LpuUnit DLpuUnit on coalesce(ED.LpuUnit_did,DLpuSection.LpuUnit_id,MS.LpuUnit_id) = DLpuUnit.LpuUnit_id
				left join v_Lpu Lpu on ED.Lpu_sid = Lpu.Lpu_id
				left join v_DirType DT on DT.DirType_id = ED.DirType_id
				left join v_pmUserCache pm on pm.PMUser_id=ED.pmUser_insID
			where
				TTS.Person_id = :Person_id
				and cast(TTS.TimetableStac_setDate as DATE) >= cast(dbo.tzGetDate() as DATE)
				{$addFilters}
			order by \"recSort\" desc
		";
		//echo getDebugSql($query, $params); die();
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка служб для услуги
	 */
	function getListMedServiceByUsluga( $data ) {
		if ( empty($data['Lpu_id']) ) {
			return false;
		}
		$params = array('Lpu_id' => $data['Lpu_id']);

		$uslugaList = explode(',', $data['uslugaList']);
		foreach ( $uslugaList as &$UslugaComplex_id ) {
			$UslugaComplex_id = trim($UslugaComplex_id);
			if ( !(is_numeric($UslugaComplex_id) && $UslugaComplex_id > 0) )
				return false;
		}
		$uslugaList = implode(',', $uslugaList);
		$query = "
			select
				-- select
				LS.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				LU.LpuUnitType_id as \"LpuUnitType_id\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				LS.LpuUnit_id as \"LpuUnit_id\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				UC.UslugaComplex_id as \"UslugaComplex_id\"
				-- end select
			from 
			-- from
				v_UslugaComplex UC 
				left join v_LpuSection LS on UC.LpuSection_id = LS.LpuSection_id
				left join v_LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id
				left join v_LpuUnitType LUT on LU.LpuUnitType_id = LUT.LpuUnitType_id
				left join v_Lpu Lpu on LS.Lpu_id = Lpu.Lpu_id
			-- end from
			where
				-- where 
				UC.UslugaComplex_id in ({$uslugaList}) 
				-- end where
			order by 
				-- order by
				UC.UslugaComplex_Name ASC 
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if ( is_object($result_count) ) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if ( is_object($result) ) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных о пользователе интернет портала
	 */
	function getInetUserInfo( $data ) {
		$portal_db = $this->load->database('UserPortal', true);

		$sql = "
		select 
			u.username as \"username\",
			u.email as \"email\",
			u.last_login as \"last_login\",
			u.surname || ' ' || u.first_name || coalesce(' ' || u.second_name, '') as \"Person_FIO\",
			u.birthday as \"birthday\",
			u.creating_date as \"creating_date\",
			'8' || un.UserNotify_Phone as \"UserNotify_Phone\"
		from users u
		left join usernotify un on u.id = un.User_id
		where u.id = :id
		";
		$res = $portal_db->query($sql, array('id' => $data['pmUser_id']));
		if ( is_object($res) ) {
			$row = $res->result('array');
			return $row[0];
		} else {
			return false;
		}
	}

	/**
	 * Получение списка типов бирок
	 */
	function getTimetableTypeList($data) {
		loadLibrary('TTimetableTypes');
		return TTimetableTypes::instance()->getTypes($data['Place_id']);
	}

	/**
	 * Загружаем праздники от переданного дня на месяц вперед
	 */
	function getHolidays($date) {

		$holidays = $this->queryResult("
			select 
					Calendar_date
			from Calendar 
			where nullif(Calendar_IsHoliday, 0) is not null
				and Calendar_date >= :date
				and Calendar_date <= :date::TIMESTAMP + INTERVAL '30 day'::INTERVAL;",array('date'=>$date->format('Y-m-d')));
		return $holidays;
	}

	/**
	 * Получение наименования LpuUnit
	 * @param $data
	 * @return string $LpuUnit_Name
	 */
	function getLpuUnitName($data) {
		$LpuUnit_Name = $this->getFirstResultFromQuery('SELECT LpuUnit_Name as "LpuUnit_Name" FROM v_LpuUnit WHERE LpuUnit_id = :LpuUnit_id limit 1', array('LpuUnit_id' => $data['LpuUnit_id']));

		return $LpuUnit_Name;
	}
}

?>