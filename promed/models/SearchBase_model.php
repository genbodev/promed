<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Search - модель для форм поиска
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			?
 */
class SearchBase_model extends swModel {

	protected $schema = "dbo";  //региональная схема
	protected $comboSchema = "dbo";
	protected $lpuList = array();
	protected $optionRecompile = false;
	protected $archiveAlias = null;

	/**
	 * 	Конструктор
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		if($this->regionNick == 'kz' || $this->regionNick == 'ufa'){
			$this->schema = $config['regions'][getRegionNumber()]['schema'];
		}

		if($this->regionNick == 'kz'){
			$this->comboSchema = $config['regions'][getRegionNumber()]['schema'];
		}
	}

	/**
	 * 	Определяет вид фильтра по Lpu_id
	 */
	function getLpuIdFilter($data) {
		if ( count($this->lpuList) == 0 ) {
			if ( in_array($data['SearchFormType'],array('EvnPL','EvnVizitPL','EvnPLStom','EvnVizitPLStom')) && !isSuperAdmin() ) { //https://redmine.swan.perm.ru/issues/85161
				$resp = $this->queryResult("
					select L_t.Lpu_id
					from v_Lpu L_t with (nolock)
					where L_t.Lpu_pid = :Lpu_id or L_t.Lpu_id = :Lpu_id
				", $data);

				if ( $resp === false ) {
					$this->lpuList[] = $data['Lpu_id'];
				}
				else {
					foreach ( $resp as $row ) {
						if ( !in_array($row['Lpu_id'], $this->lpuList) ) {
							$this->lpuList[] = $row['Lpu_id'];
						}
					}
				}
			}
			else if ( array_key_exists('linkedLpuIdList', $data['session']) ) {
				$this->lpuList = $data['session']['linkedLpuIdList'];
			}

			if ( count($this->lpuList) == 0 ) {
				$this->lpuList[] = $data['Lpu_id'];
			}
		}

		return (count($this->lpuList) > 1 ? "in (" . implode(',', $this->lpuList) . ")" : "= :Lpu_id");
	}

	/**
	 * Фильтры по льготам
	 */
	function getPrivilegeFilters($data) {
		$this->pt_filter = '';
		// Льгота
		if ($data['RegisterSelector_id'] == 1) {
			$this->pt_filter .= " and PT.ReceptFinance_id = 1";
			$this->queryParams['RegisterSelector_id'] = $data['RegisterSelector_id'];
		} else if ($data['RegisterSelector_id'] == 2) {
			$this->pt_filter .= " and PT.ReceptFinance_id = 2";
			$this->queryParams['RegisterSelector_id'] = $data['RegisterSelector_id'];
		} else {
			// $this->pt_filter .= " and ((PT.ReceptFinance_id = 1 and PT.PrivilegeType_Code < 500) or (PT.ReceptFinance_id = 2 and PT.PrivilegeType_Code < 500" . ($data['SearchFormType'] == 'PersonPrivilege' ? " and PP.Lpu_id = :Lpu_id" : "") . "))";
		}

		if (!empty($data['PersonPrivilege_deleted'])) {
			$this->pt_filter .= " and isnull(PP.PersonPrivilege_deleted, 1) = :PersonPrivilege_deleted";
			$this->queryParams['PersonPrivilege_deleted'] = $data['PersonPrivilege_deleted'];
		}

		if (isset($data['Lpu_prid']) && $data['Lpu_prid'] > 0) {
			$this->pt_filter .= " and PP.Lpu_id = :Lpu_prid";
			$this->queryParams['Lpu_prid'] = $data['Lpu_prid'];
		}

		// Льгота
		if (isset($data['PrivilegeType_id'])) {
			$this->pt_filter .= " and PT.PrivilegeType_id = :PrivilegeType_id";
			$this->queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
		}

		// Подкатегория (KZ)
		if (isset($data['SubCategoryPrivType_id']) && $this->regionNick == 'kz') {
			$this->pt_filter .= " and PPSCPT.SubCategoryPrivType_id = :SubCategoryPrivType_id";
			$this->queryParams['SubCategoryPrivType_id'] = $data['SubCategoryPrivType_id'];
		}

		// Льгота
		if (isset($data['Privilege_begDate'])) {
			$this->pt_filter .= " and PP.PersonPrivilege_begDate = cast(:Privilege_begDate as datetime)";
			$this->queryParams['Privilege_begDate'] = $data['Privilege_begDate'];
		}

		// Льгота
		if (isset($data['Privilege_begDate_Range'][0])) {
			$this->pt_filter .= " and PP.PersonPrivilege_begDate >= cast(:Privilege_begDate_Range_0 as datetime)";
			$this->queryParams['Privilege_begDate_Range_0'] = $data['Privilege_begDate_Range'][0];
		}

		// Льгота
		if (isset($data['Privilege_begDate_Range'][1])) {
			$this->pt_filter .= " and PP.PersonPrivilege_begDate <= cast(:Privilege_begDate_Range_1 as datetime)";
			$this->queryParams['Privilege_begDate_Range_1'] = $data['Privilege_begDate_Range'][1];
		}

		// Льгота
		if (isset($data['Privilege_endDate'])) {
			$this->pt_filter .= " and PP.PersonPrivilege_endDate = cast(:Privilege_endDate as datetime)";
			$this->queryParams['Privilege_endDate'] = $data['Privilege_endDate'];
		}

		// Льгота
		if (isset($data['Privilege_endDate_Range'][0])) {
			$this->pt_filter .= " and PP.PersonPrivilege_endDate >= cast(:Privilege_endDate_Range_0 as datetime)";
			$this->queryParams['Privilege_endDate_Range_0'] = $data['Privilege_endDate_Range'][0];
		}

		// Льгота
		if (isset($data['Privilege_endDate_Range'][1])) {
			$this->pt_filter .= " and PP.PersonPrivilege_endDate <= cast(:Privilege_endDate_Range_1 as datetime)";
			$this->queryParams['Privilege_endDate_Range_1'] = $data['Privilege_endDate_Range'][1];
		}

		if (!empty($this->pt_filter)) {
			// Льгота
			if ($data['PrivilegeStateType_id'] == 1) {
				$this->pt_filter .= " and PP.PersonPrivilege_begDate is not null";
				$this->pt_filter .= " and PP.PersonPrivilege_begDate <= @getDT";
				$this->pt_filter .= " and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate > cast(convert(char(10), @getDT, 112) as datetime))";
			}

			$this->where .= " and exists (select personprivilege_id from v_PersonPrivilege PP with (nolock) ";
			$this->where .= " inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id";
			//$this->where .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = PP.PrivilegeType_id";
			$this->where .= " WHERE PP.Person_id = PS.Person_id";
			$this->where .= $this->pt_filter;

			if ($data['SearchFormType'] == 'EvnRecept') {
				$alias = 'ER'; //Для поиска рецептов PrivilegeType_id берем из ER
			} else {
				$alias = 'PT';
			}

			$privilegeFilter = getAccessRightsPrivilegeTypeFilter("{$alias}.PrivilegeType_id");
			if (!empty($privilegeFilter)) {
				$this->where .= " and $privilegeFilter";
			}

			if ($this->getRegionNick() == 'ufa') {

				if ($data['SearchFormType'] == 'PersonPrivilege') {
					$alias = 'PP';
				} elseif ($data['SearchFormType'] == 'EvnRecept') {
					$alias = 'ER';
				}

				$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
				$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
				$this->where .= " and exists (select top 1 PersonPrivilege_id from v_PersonPrivilege with (nolock) where PrivilegeType_id = {$alias}.PrivilegeType_id and Person_id = PS.Person_id {$lpuFilter})";
			}

			$this->where .= ") ";
		}
	}

	/**
	 * Фильтры по пользователю
	 */
	function getPmUserFilters($data) {
		// Пользователь
		if ($data['pmUser_insID'] > 0) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and " . $this->main_alias . ".pmUserBeg_insID = :pmUser_insID";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.pmUser_insID else " . $this->main_alias . ".pmUser_insID end = :pmUser_insID";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and Evn.pmUser_insID = :pmUser_insID";
			} else {
				$this->where .= " and " . $this->main_alias . ".pmUser_insID = :pmUser_insID";
			}
			$this->queryParams['pmUser_insID'] = $data['pmUser_insID'];
		}

		// Пользователь
		if ($data['pmUser_updID'] > 0) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and " . $this->main_alias . ".pmUserEnd_insID = :pmUser_updID";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.pmUser_updID else " . $this->main_alias . ".pmUser_updID end = :pmUser_updID";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and Evn.pmUser_updID = :pmUser_updID";
			} else {
				$this->where .= " and " . $this->main_alias . ".pmUser_updID = :pmUser_updID";
			}
			$this->queryParams['pmUser_updID'] = $data['pmUser_updID'];
		}
		
		// Пользователь
		if (isset($data['InsDate'])) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and cast(" . $this->main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) = cast(:InsDate as date)";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $this->main_table . "_insDT else " . $this->main_alias . "." . $this->main_table . "_insDT end as date) = cast(:InsDate as date)";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and cast(Evn.Evn_insDT as date) = cast(:InsDate as date)";
			} else {
				$this->where .= " and cast(" . $this->main_alias . "." . $this->main_table . "_insDT as date) = cast(:InsDate as date)";
			}
			$this->queryParams['InsDate'] = $data['InsDate'];
		}

		// Пользователь
		if (isset($data['InsDate_Range'][0])) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and cast(" . $this->main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) >= cast(:InsDate_Range_0 as date)";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $this->main_table . "_insDT else " . $this->main_alias . "." . $this->main_table . "_insDT end as date) >= cast(:InsDate_Range_0 as date)";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and cast(Evn.Evn_insDT as date) >= cast(:InsDate_Range_0 as date)";
			} else {
				$this->where .= " and cast(" . $this->main_alias . "." . $this->main_table . "_insDT as date) >= cast(:InsDate_Range_0 as date)";
			}
			$this->queryParams['InsDate_Range_0'] = $data['InsDate_Range'][0];
		}

		// Пользователь
		if (isset($data['InsDate_Range'][1])) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and cast(" . $this->main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) <= cast(:InsDate_Range_1 as date)";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $this->main_table . "_insDT else " . $this->main_alias . "." . $this->main_table . "_insDT end as date) <= cast(:InsDate_Range_1 as date)";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and cast(Evn.Evn_insDT as date) <= cast(:InsDate_Range_1 as date)";
			} else {
				$this->where .= " and cast(" . $this->main_alias . "." . $this->main_table . "_insDT as date) <= cast(:InsDate_Range_1 as date)";
			}
			$this->queryParams['InsDate_Range_1'] = $data['InsDate_Range'][1];
		}

		// Пользователь
		if (isset($data['UpdDate'])) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and cast(" . $this->main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) = cast(:UpdDate  as date)";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $this->main_table . "_updDT else " . $this->main_alias . "." . $this->main_table . "_updDT end as date) = cast(:UpdDate as date)";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and cast(Evn.Evn_updDT as date) = cast(:UpdDate as date)";
			} else {
				$this->where .= " and cast(" . $this->main_alias . "." . $this->main_table . "_updDT as date) = cast(:UpdDate as date)";
			}
			$this->queryParams['UpdDate'] = $data['UpdDate'];
		}

		// Пользователь
		if (isset($data['UpdDate_Range'][0])) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and cast(" . $this->main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) >= cast(:UpdDate_Range_0 as date)";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $this->main_table . "_updDT else " . $this->main_alias . "." . $this->main_table . "_updDT end as date) >= cast(:UpdDate_Range_0 as date)";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and cast(Evn.Evn_updDT as date) >= cast(:UpdDate_Range_0 as date)";
			} else {
				$this->where .= " and cast(" . $this->main_alias . "." . $this->main_table . "_updDT as date) >= cast(:UpdDate_Range_0 as date)";
			}
			$this->queryParams['UpdDate_Range_0'] = $data['UpdDate_Range'][0];
		}

		// Пользователь
		if (isset($data['UpdDate_Range'][1])) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$this->where .= " and cast(" . $this->main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) <= cast(:UpdDate_Range_1 as date)";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec' ) {
				$this->where .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $this->main_table . "_updDT else " . $this->main_alias . "." . $this->main_table . "_updDT end as date) <= cast(:UpdDate_Range_1 as date)";
			} else if ($data['SearchFormType'] == 'EvnPL' ) {
				$this->where .= " and cast(Evn.Evn_updDT as date) <= cast(:UpdDate_Range_1 as date)";
			} else {
				$this->where .= " and cast(" . $this->main_alias . "." . $this->main_table . "_updDT as date) <= cast(:UpdDate_Range_1 as date)";
			}
			$this->queryParams['UpdDate_Range_1'] = $data['UpdDate_Range'][1];
		}
		if (isset($data['FarRegistered'])) {//#123829 только записанные через ФЭР
			$this->where .= " and TTG.pmUser_updID = '999900'";
		}
	}

	/**
	 * Фильтры по прикреплению
	 */
	function getPersonCardFilters($data) {
		$this->pcfilter = '';
		// Прикрепление
		if (isset($data['PersonCard_endDate'])) {
			$this->pcfilter .= " and PC.PersonCard_endDate = :PersonCard_endDate";
			$this->queryParams['PersonCard_endDate'] = $data['PersonCard_endDate'];
		}

		// Прикрепление
		if (isset($data['PersonCard_endDate_Range'][0])) {
			$this->pcfilter .= " and PC.PersonCard_endDate >= :PersonCard_endDate_Range_0";
			$this->queryParams['PersonCard_endDate_Range_0'] = $data['PersonCard_endDate_Range'][0];
		}

		// Прикрепление
		if (isset($data['PersonCard_endDate_Range'][1])) {
			$this->pcfilter .= " and PC.PersonCard_endDate <= :PersonCard_endDate_Range_1";
			$this->queryParams['PersonCard_endDate_Range_1'] = $data['PersonCard_endDate_Range'][1];
		}

		// Прикрепление
		if ($data['AttachLpu_id'] > 0) {
			if ($data['AttachLpu_id'] == 666666 && in_array($data['SearchFormType'], array('EvnPLDispDop13', 'EvnPLDispDop13Sec')) && getRegionNick() === 'ekb' )
			{
				// Вариант "Без прикрепления к МО", используется только в екб на формах поиск двн 1 и 2 этап. Выводятся люди которые не имебт прикрепления ни к одной МО

			} else if ($data['AttachLpu_id'] == 100500)
			{
				$this->pcfilter .= " and PC.Lpu_id is not null and PC.Lpu_id <> :Lpu_id";
			}
			else
			{
				//Прикрепление для ИПРА вернул array('BskRegistry', 'IPRARegistry'))
				$this->pcfilter .= !in_array($data['SearchFormType'], array('BskRegistry'))  ? " and PC.Lpu_id = :AttachLpu_id" : "";
				$this->pcfilter .= " and PC.Lpu_id = :AttachLpu_id";
				//https://redmine.swan.perm.ru/issues/94164
				//пациенты на спец учёте доступны не всем
				//if($data['SearchFormType'] == 'IPRARegistry'){
				//	if(!in_array($data['AttachLpu_id'], array(338, 392, 393, 89, 86, 391, 394, 150016))){
				//		$this->pcfilter .= " and IR.IPRARegistry_FGUMCEnumber not in  (11,12,13,14,16)";
				//	}
				//}
			}


			$this->queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
		}

		// LPU_id - МО сопровождения
		if( $data['SearchFormType'] == 'IPRARegistry' && isset($data['LPU_id']) && !empty($data['LPU_id']) && $this->getRegionNick() == 'ufa') //!isMinZdrav()
		{
			$this->pcfilter .= " AND ( ( ";
			$this->pcfilter .= " IR.IPRARegistry_FGUMCEnumber IN (11,12,13,14,16)";
			$this->pcfilter .= " and IR.IPRARegistry_DirectionLPU_id IN (338, 392, 393, 89, 86, 391, 394, 150016)";
			$this->pcfilter .= " and IR.Lpu_id = :Lpu_id ";
			$this->pcfilter .= " and IR.Lpu_id IN (338, 392, 393, 89, 86, 391, 394, 150016)";
			$this->pcfilter .= " ) OR ";
			$this->pcfilter .= " IR.Lpu_id NOT IN (338, 392, 393, 89, 86, 391, 394, 150016) ) ";
			$this->queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		/* else { 											//https://redmine.swan.perm.ru/issues/19285
		  if ( $data['SearchFormType'] == 'PersonCard' )
		  $this->pcfilter .= " and PC.Lpu_id = :Lpu_id";
		  } */

		// Прикрепление
		if ($data['LpuAttachType_id'] > 0) {
			$this->pcfilter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$this->queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
		}

		// Пациент
		if (strlen($data['PersonCard_Code']) > 0) {
			if (!empty($data['PartMatchSearch'])) {
				// включен чекбокс "Поиск по частичному совпадению"
				if (!empty($this->config->config['blockSlowDownFunctions'])) {
					return array('Error_Msg' => 'Функционал поиска по частичному совпадению временно заблокирован. Приносим извинения за доставленные неудобства.');
				}

				$this->pcfilter .= " and PC.PersonCard_Code LIKE '%'+:PersonCard_Code+'%'";
				$orderby = "case when ISNULL(CHARINDEX(:PersonCard_Code, pc.PersonCard_Code), 0) > 0 then CHARINDEX(:PersonCard_Code, pc.PersonCard_Code) else 99 end,";
			} else {
				if(in_array($data['SearchFormType'],array('EvnPL','EvnVizitPL'))){
					//$this->pcfilter .= " and PAC.PersonAmbulatCard_Num = :PersonCard_Code";
					$pac_filter .= " and exists(select top 1 * from v_PersonAmbulatCard PAC2 where PAC2.PersonAmbulatCard_Num = :PersonCard_Code and PAC2.Person_id = PS.Person_id and PAC2.Lpu_id = ".$data['session']['lpu_id'].')';
				}
				else
					$this->pcfilter .= " and PC.PersonCard_Code = :PersonCard_Code";
			}

			$this->queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		}

		// Прикрепление
		if (isset($data['PersonCard_begDate'])) {
			$this->pcfilter .= " and cast(PC.PersonCard_begDate as date) = :PersonCard_begDate";
			$this->queryParams['PersonCard_begDate'] = $data['PersonCard_begDate'];
		}

		// Прикрепление
		if (isset($data['PersonCard_begDate_Range'][0])) {
			$this->pcfilter .= " and PC.PersonCard_begDate >= cast(:PersonCard_begDate_Range_0 as datetime)";
			$this->queryParams['PersonCard_begDate_Range_0'] = $data['PersonCard_begDate_Range'][0];
		}

		// Прикрепление
		if (isset($data['PersonCard_begDate_Range'][1])) {
			$this->pcfilter .= " and PC.PersonCard_begDate <= cast(:PersonCard_begDate_Range_1 as datetime)";
			$this->queryParams['PersonCard_begDate_Range_1'] = $data['PersonCard_begDate_Range'][1];
		}

		// Прикрепление
		if ($data['PersonCard_IsAttachCondit'] > 0) {
			$this->pcfilter .= " and ISNULL(PC.PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
			$this->queryParams['PersonCard_IsAttachCondit'] = $data['PersonCard_IsAttachCondit'];
		}
		if ($data['PersonCardAttach'] > 0) {
			if ($data['PersonCardAttach'] == 1) {
				$this->pcfilter .= " and PC.PersonCardAttach_id IS NULL";
			} else {
				$this->pcfilter .= " and PC.PersonCardAttach_id IS NOT NULL";
			}
		}

		// Прикрепление
		if ($data['PersonCard_IsDms'] > 0) {
			$exists = "";
			if ($data['PersonCard_IsDms'] == 1)
				$exists = " not ";
			$this->pcfilter .= " and " . $exists . " exists(
				select
					PersonCard_id
				from
					v_PersonCard (nolock)
				where
					Person_id = PC.Person_id
					and LpuAttachType_id = 5
					and PersonCard_endDate >= @getDT
					and CardCloseCause_id is null
			) ";
		}

		// Прикрепление
		if (isset($data['LpuRegion_id'])) {
			$this->queryParams['LpuRegion_id'] = $data['LpuRegion_id'];

			if ($data['LpuRegion_id'] == -1) {
				$this->pcfilter .= " and LR.LpuRegion_id is null";
			} else {
				$this->pcfilter .= " and LR.LpuRegion_id = :LpuRegion_id";
			}
		}
		if(isset($data['LpuRegion_Fapid'])){
			$this->queryParams['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
			$this->pcfilter .= " and LR_Fap.LpuRegion_id = :LpuRegion_Fapid";
		}
		// Прикрепление
		if ($data['LpuRegionType_id'] > 0) {
			$this->pcfilter .= " and LR.LpuRegionType_id = :LpuRegionType_id";
			$this->queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
		}
		
		if (!empty($this->pcfilter)) {
			if ($data['PersonCardStateType_id'] == 1) {
				$this->where .= " and exists (select top 1 personcard_id from v_PersonCard PC with (nolock) ";
				$this->where .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
				$this->where .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid"; //https://redmine.swan.perm.ru/issues/78988
				$this->where .= " WHERE PC.Person_id = PS.Person_id";
				$this->where .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @getDT)";
			} else {
				$this->where .= " and exists (select top 1 personcard_id from v_PersonCard_all PC with (nolock) ";
				$this->where .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
				$this->where .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid"; //https://redmine.swan.perm.ru/issues/78988
				$this->where .= " WHERE PC.Person_id = PS.Person_id";
			}
			$this->where .= $this->pcfilter . ') ';
		}
	}

	/**
	 * Фильтры по периодикам
	 */
	protected function getPersonPeriodicFilters($data) {
		if ($data['PersonPeriodicType_id'] == 3) {
			$this->where .= " and exists(
				select top 1 1
				from v_Person_all PStmp (nolock)
				where PStmp.Person_id = PS.Person_id
			";
			$alias = 'PStmp';
		} else {
			$alias = 'PS';
		}


		// Пациент
		if (isset($data['Person_Birthday'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) = cast(:Person_Birthday as datetime)";
			} else {
				$this->where .= " and " . $alias . ".Person_BirthDay = cast(:Person_Birthday as datetime)";
			}

			$this->queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}


		// Пациент
		if (isset($data['Person_BirthdayYear'])) {
			$this->where .= " and YEAR(" . $alias . ".Person_BirthDay) = :Person_BirthdayYear";
			$this->queryParams['Person_BirthdayYear'] = $data['Person_BirthdayYear'];
		}

		//Диапазон дат обследований
		if (isset($data['EvnPLWOW_setDate_Range'][0])) {
			$this->where .= " and EPW.EvnPLWow_setDate >= cast (:EvnPLWOW_setDate_Range_0 as datetime)";
			$this->queryParams['EvnPLWOW_setDate_Range_0'] = $data['EvnPLWOW_setDate_Range'][0];
		}
		if (isset($data['EvnPLWOW_setDate_Range'][1])) {
			$this->where .= " and EPW.EvnPLWow_setDate <= cast (:EvnPLWOW_setDate_Range_1 as datetime)";
			$this->queryParams['EvnPLWOW_setDate_Range_1'] = $data['EvnPLWOW_setDate_Range'][1];
		}

		// Пациент
		if (isset($data['Person_Birthday_Range'][0])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) >= cast(:Person_Birthday_Range_0 as datetime)";
			} else {
				$this->where .= " and " . $alias . ".Person_BirthDay >= cast(:Person_Birthday_Range_0 as datetime)";
			}

			$this->queryParams['Person_Birthday_Range_0'] = $data['Person_Birthday_Range'][0];
		}

		// Пациент
		if (isset($data['Person_Birthday_Range'][1])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) <= cast(:Person_Birthday_Range_1 as datetime)";
			} else {
				$this->where .= " and " . $alias . ".Person_BirthDay <= cast(:Person_Birthday_Range_1 as datetime)";
			}

			$this->queryParams['Person_Birthday_Range_1'] = $data['Person_Birthday_Range'][1];
		}

		// Пациент
		if (strlen($data['Person_Code']) > 0) {
			$this->where .= " and " . $alias . ".Person_EdNum = :Person_Code";
			$this->queryParams['Person_Code'] = $data['Person_Code'];
		}

		// Пациент
		if (strlen($data['Person_Firname']) > 0) {
			$this->optionRecompile = true;

			if ($data['SearchFormType'] == 'CmpCallCard') {
				$this->where .= " and ISNULL(" . $alias . ".Person_FirName, CCC.Person_FirName) like :Person_Firname";
			} else if ($data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and COALESCE(CLC.Name, " . $alias . ".Person_FirName, CCC.Person_FirName) like :Person_Firname";
			} else {
				$this->where .= " and " . $alias . ".Person_FirName like :Person_Firname";
			}

			$this->queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		// Пациент
		if (strlen($data['Person_Secname']) > 0) {
			$this->optionRecompile = true;

			if ($data['SearchFormType'] == 'CmpCallCard') {
				$this->where .= " and ISNULL(" . $alias . ".Person_SecName, CCC.Person_SecName) like :Person_Secname";
			} else if ($data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and COALESCE(CLC.Middle, " . $alias . ".Person_SecName, CCC.Person_SecName) like :Person_Secname";
			} else {
				$this->where .= " and " . $alias . ".Person_SecName like :Person_Secname";
			}

			$this->queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}

		// Пациент
		if (strlen($data['Person_Surname']) > 0) {
			$this->optionRecompile = true;

			$this->queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';

			switch($data['SearchFormType']) {
				case 'CmpCallCard':
					$this->where .= " and ISNULL(" . $alias . ".Person_SurName, CCC.Person_SurName) like :Person_Surname";
					break;

				case 'CmpCloseCard':
					$this->where .= " and COALESCE(CLC.Fam, " . $alias . ".Person_Surname, CCC.Person_SurName) like :Person_Surname";
					break;

				case 'EvnPS':
				case 'EvnSection':
				case 'EvnPL':
				case 'EvnPLStom':
				case 'EvnVizitPL':
				case 'EvnVizitPLStom':
				case 'PersonCard':
				case 'PersonPrivilege':
				case 'EvnUslugaPar':
				case 'EvnRecept':
				case 'EvnReceptGeneral':
				case 'EvnPLDispDop':
				case 'EvnPLDispDop13':
				case 'EvnPLDispDop13Sec':
				case 'EvnPLDispTeen14':
				case 'EvnPLDispProf':
				case 'EvnPLDispOrp':
				case 'EvnPLDispOrpSec':
				case 'EvnPLDispOrpOld':
				case 'EvnPLDispScreen':
				case 'EvnPLDispScreenChild':
				case 'EvnPLDispTeenInspectionPeriod':
				case 'EvnPLDispTeenInspectionProf':
				case 'EvnPLDispTeenInspectionPred':
				case 'EvnPLDispMigrant':
				case 'EvnPLDispDriver':
				case 'PersonDisp':
				case 'PersonDopDisp':
				case 'PersonDispOrp':
				case 'PersonDispOrpPeriod':
				case 'PersonDispOrpPred':
				case 'PersonDispOrpProf':
				case 'PersonDispOrpOld':
				case 'WorkPlacePolkaReg':
				case 'PersonCallCenter':
					if (allowPersonEncrypHIV($data['session']) && isSearchByPersonEncrypHIV($data['Person_Surname'])) {
						$this->queryParams['Person_Surname'] = rtrim($data['Person_Surname']);
						$this->where .= " and PEH.PersonEncrypHIV_Encryp like (:Person_Surname)";
					} else {
						$this->where .= " and " . $alias . ".Person_SurName like :Person_Surname";
					}
					break;
				default:
					$this->where .= " and " . $alias . ".Person_SurName like :Person_Surname";
			}
		}

		// В зависимости от типа поиска надо искать либо исходя из текущей даты, либо исходя из даты случая (для ТАП и КВС)
		$getdate = '@getDT';
		if (in_array($data['PersonPeriodicType_id'], array(2, 3))) {
			if (in_array($data['SearchFormType'], array('EvnVizitPL', 'EvnPS', 'EvnSection', 'EvnPLStom', 'EvnVizitPLStom'))) {
				$getdate = $this->main_alias . "." . $data['SearchFormType'] . "_setDate";
			}
			else if (in_array($data['SearchFormType'], array('EvnPL'))) {
				$getdate = "cast(Evn.Evn_setDT as date)";
			}
		}

		// проверка по возрасту на конец года диспансеризации
		if (in_array($data['SearchFormType'], array('EvnPLDispDop13', 'EvnPLDispDop13Sec'))) {
			$getdate = "@PersonDopDisp_YearEndDate";
		}


		// Возраст пациента
		if (isset($data['PersonAge'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and dbo.Age2(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay), {$getdate}) = :PersonAge";
			} else {
				$this->where .= " and dbo.Age2(" . $alias . ".Person_BirthDay, {$getdate}) = :PersonAge";
			}

			$this->queryParams['PersonAge'] = intval($data['PersonAge']);
		}

		// Возраст пациента (с)
		if (isset($data['PersonAge_Max'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and dbo.Age2(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay), {$getdate}) <= :PersonAge_Max";
			} else {
				$this->where .= " and dbo.Age2(" . $alias . ".Person_BirthDay, {$getdate}) <= :PersonAge_Max";
			}

			$this->queryParams['PersonAge_Max'] = intval($data['PersonAge_Max']);
		}

		// Возраст пациента (по)
		if (isset($data['PersonAge_Min'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and dbo.Age2(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay), {$getdate}) >= :PersonAge_Min";
			} else {
				$this->where .= " and dbo.Age2(" . $alias . ".Person_BirthDay, {$getdate}) >= :PersonAge_Min";
			}

			$this->queryParams['PersonAge_Min'] = intval($data['PersonAge_Min']);
		}


		// Пациент
		if (isset($data['PersonBirthdayYear'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and year(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay)) = :PersonBirthdayYear";
			} else {
				$this->where .= " and year(" . $alias . ".Person_BirthDay) = :PersonBirthdayYear";
			}

			$this->queryParams['PersonBirthdayYear'] = intval($data['PersonBirthdayYear']);
		}

		// Пациент
		if (isset($data['PersonBirthdayYear_Max'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and year(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay)) <= :PersonBirthdayYear_Max";
			} else {
				$this->where .= " and year(" . $alias . ".Person_BirthDay) <= :PersonBirthdayYear_Max";
			}

			$this->queryParams['PersonBirthdayYear_Max'] = intval($data['PersonBirthdayYear_Max']);
		}

		// Пациент
		if (isset($data['PersonBirthdayYear_Min'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and year(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay)) >= :PersonBirthdayYear_Min";
			} else {
				$this->where .= " and year(" . $alias . ".Person_BirthDay) >= :PersonBirthdayYear_Min";
			}

			$this->queryParams['PersonBirthdayYear_Min'] = intval($data['PersonBirthdayYear_Min']);
		}

		// Пациент (месяц рождения)
		if (isset($data['PersonBirthdayMonth'])) {
			$this->where .= " and month(" . $alias . ".Person_BirthDay) = :PersonBirthdayMonth";

			$this->queryParams['PersonBirthdayMonth'] = $data['PersonBirthdayMonth'];
		}

		// Наличие СНИЛС
		if (isset($data['SnilsExistence']) && $data['SnilsExistence'] != '') {
			if ($data['SnilsExistence'] == 1) {
				$this->where .= " and (PS.Person_Snils = '' OR PS.Person_Snils is null)";
			} else {
				$this->where .= " and (PS.Person_Snils <> '' OR PS.Person_Snils is not null)";
			}
		}

		// Пациент (доп.)
		if (strlen($data['Person_Snils']) > 0) {
			$this->where .= " and " . $alias . ".Person_Snils = :Person_Snils";
			$this->queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		// Пациент (доп.)
		if (strlen($data['Person_Inn']) > 0) {
			$this->where .= "
				and exists(
					select top 1 t.Person_id
					from v_PersonInn t with(nolock)
					where t.Person_id = " . $alias . ".Person_id and t.PersonInn_Inn = :Person_Inn
				)
			";
			$this->queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		// Пациент (доп.)
		if ($data['Sex_id'] > 0) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and ISNULL(" . $alias . ".Sex_id, CCC.Sex_id) = :Sex_id";
			} else {
				$this->where .= " and isnull(" . $alias . ".Sex_id, 3) = :Sex_id";
			}

			$this->queryParams['Sex_id'] = $data['Sex_id'];
		}

		// Пациент (доп.)
		if ($data['SocStatus_id'] > 0) {
			$this->where .= " and " . $alias . ".SocStatus_id = :SocStatus_id";
			$this->queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}

		// Пациент (доп.)
		if (isset($data['Person_IsBDZ'])) {
			$this->where .= " and exists (
				select top 1
					1
				from Person PTemp with (nolock)
				where PTemp.Person_id = " . $alias . ".Person_id
					and PTemp.Server_id " . ($data['Person_IsBDZ'] == 2 ? "=" : "<>") . " 0
			)";
		}
		// Пациент (доп.)
		if (isset($data['Person_isIdentified'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$this->where .= " and ISNULL(CCC.Person_id,0) " . ($data['Person_isIdentified'] == 2 ? "<>" : "=") . " 0";
			}
		}

		// Пациент (доп.)
		if (isset($data['Person_IsDisp'])) {
			$this->where .= " and " . ($data['Person_IsDisp'] == 1 ? "not" : "") . " exists (
				select top 1
					1
				from PersonDisp PDTemp with (nolock)
				where PDTemp.Person_id = " . $alias . ".Person_id
					and PDTemp.PersonDisp_begDate <= @getDT
					and (PDTemp.PersonDisp_endDate is null or PDTemp.PersonDisp_endDate > @getDT)
			)";
		}

		if ((strlen($data['Document_Num']) > 0) || (strlen($data['Document_Ser']) > 0) || ($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0)) {
			$this->where .= " and exists (select Document_id from Document with(nolock) ";
			$this->where .= " WHERE Document.Document_id = " . $alias . ".Document_id";

			// Пациент (доп.)
			if (strlen($data['Document_Num']) > 0) {
				$this->where .= " and Document.Document_Num = :Document_Num";
				$this->queryParams['Document_Num'] = $data['Document_Num'];
			}

			// Пациент (доп.)
			if (strlen($data['Document_Ser']) > 0) {
				$this->where .= " and Document.Document_Ser = :Document_Ser";
				$this->queryParams['Document_Ser'] = $data['Document_Ser'];
			}

			// Пациент (доп.)
			if ($data['DocumentType_id'] > 0) {
				$this->where .= " and Document.DocumentType_id = :DocumentType_id";
				$this->queryParams['DocumentType_id'] = $data['DocumentType_id'];
			}

			// Пациент (доп.)
			if ($data['OrgDep_id'] > 0) {
				$this->where .= " and Document.OrgDep_id = :OrgDep_id";
				$this->queryParams['OrgDep_id'] = $data['OrgDep_id'];
			}

			$this->where .= ") ";
		}

		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0)) {
			$this->where .= " and exists (select Job_id from Job  with(nolock)";
			$this->where .= " WHERE Job.Job_id = " . $alias . ".Job_id";

			// Пациент (доп.)
			if ($data['Org_id'] > 0) {
				$this->where .= " and Job.Org_id = :Org_id";
				$this->queryParams['Org_id'] = $data['Org_id'];
			}

			// Пациент (доп.)
			if ($data['Post_id'] > 0) {
				$this->where .= " and Job.Post_id = :Post_id";
				$this->queryParams['Post_id'] = $data['Post_id'];
			}

			$this->where .= ") ";
		}

		if (strtolower($data['Person_NoAddress']) == 'on') {
			switch ($data['AddressStateType_id']) {
				case 1:
					$this->where .= " and " . $alias . ".UAddress_id is null";
					break;

				case 2:
					$this->where .= " and " . $alias . ".PAddress_id is null";
					break;

				default:
					$this->where .= " and " . $alias . ".UAddress_id is null";
					$this->where .= " and " . $alias . ".PAddress_id is null";
					break;
			}
		} else if (($data['KLAreaType_id'] > 0) || ($data['KLCountry_id'] > 0) || ($data['KLRgn_id'] > 0) ||
			($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) ||
			($data['KLStreet_id'] > 0) ||(strlen($data['Address_Corpus']) > 0)|| (strlen($data['Address_House']) > 0) || (strlen($data['Address_Street']) > 0)
		) {
			if ($data['AddressStateType_id'] == 1) {
				$this->where .= " and exists (select AR.Address_id from Address AR with (nolock) ";

				if (!empty($data['Address_Street'])) {
					$this->where .= " inner join KLStreet KLS with (nolock) on KLS.KLStreet_id = AR.KLStreet_id ";
				}

				$this->where .= " where AR.Address_id = " . $alias . ".UAddress_id";

				// Адрес
				if (!empty($data['Address_Street'])) {
					$this->where .= " and KLS.KLStreet_Name like :Address_Street ";
					$this->queryParams['Address_Street'] = $data['Address_Street'] . '%';
				}

				// Адрес
				if ($data['KLCountry_id'] > 0) {
					$this->where .= " and AR.KLCountry_id = :KLCountry_id";
					$this->queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Адрес
				if ($data['KLRgn_id'] > 0) {
					$this->where .= " and AR.KLRgn_id = :KLRgn_id";
					$this->queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Адрес
				if ($data['KLSubRgn_id'] > 0) {
					$this->where .= " and AR.KLSubRgn_id = :KLSubRgn_id";
					$this->queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Адрес
				if ($data['KLCity_id'] > 0) {
					$this->where .= " and AR.KLCity_id = :KLCity_id";
					$this->queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Адрес
				if ($data['KLTown_id'] > 0) {
					$this->where .= " and AR.KLTown_id = :KLTown_id";
					$this->queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Адрес
				if ($data['KLStreet_id'] > 0) {
					$this->where .= " and AR.KLStreet_id = :KLStreet_id";
					$this->queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Адрес
				if (strlen($data['Address_House']) > 0) {
					$this->where .= " and AR.Address_House = :Address_House";
					$this->queryParams['Address_House'] = $data['Address_House'];
				}
				if (strlen($data['Address_Corpus']) > 0) {
					$this->where .= " and AR.Address_Corpus = :Address_Corpus";
					$this->queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}
				// Адрес
				if ($data['KLAreaType_id'] > 0) {
					$this->where .= " and AR.KLAreaType_id = :KLAreaType_id";
					$this->queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}

				$this->where .= ") ";
			} else if ($data['AddressStateType_id'] == 2) {
				$this->where .= " and exists (select AP.Address_id from Address AP with (nolock) ";

				if (!empty($data['Address_Street'])) {
					$this->where .= " inner join KLStreet KLS with (nolock) on KLS.KLStreet_id = AP.KLStreet_id ";
				}

				$this->where .= " where AP.Address_id = " . $alias . ".PAddress_id";

				// Адрес
				if (!empty($data['Address_Street'])) {
					$this->where .= " and KLS.KLStreet_Name like :Address_Street ";
					$this->queryParams['Address_Street'] = $data['Address_Street'] . '%';
				}

				// Адрес
				if ($data['KLCountry_id'] > 0) {
					$this->where .= " and AP.KLCountry_id = :KLCountry_id";
					$this->queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Адрес
				if ($data['KLRgn_id'] > 0) {
					$this->where .= " and AP.KLRgn_id = :KLRgn_id";
					$this->queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Адрес
				if ($data['KLSubRgn_id'] > 0) {
					$this->where .= " and AP.KLSubRgn_id = :KLSubRgn_id";
					$this->queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Адрес
				if ($data['KLCity_id'] > 0) {
					$this->where .= " and AP.KLCity_id = :KLCity_id";
					$this->queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Адрес
				if ($data['KLTown_id'] > 0) {
					$this->where .= " and AP.KLTown_id = :KLTown_id";
					$this->queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Адрес
				if ($data['KLStreet_id'] > 0) {
					$this->where .= " and AP.KLStreet_id = :KLStreet_id";
					$this->queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Адрес
				if (strlen($data['Address_House']) > 0) {
					$this->where .= " and AP.Address_House = :Address_House";
					$this->queryParams['Address_House'] = $data['Address_House'];
				}
				if (strlen($data['Address_Corpus']) > 0) {
					$this->where .= " and AP.Address_Corpus = :Address_Corpus";
					$this->queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}
				// Адрес
				if ($data['KLAreaType_id'] > 0) {
					$this->where .= " and AP.KLAreaType_id = :KLAreaType_id";
					$this->queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}

				$this->where .= ") ";
			} else {
				$this->where .= " and (exists (select Address_id from Address AR  with (nolock) ";
				$this->where .= " where (AR.Address_id = " . $alias . ".UAddress_id or AR.Address_id = " . $alias . ".PAddress_id)";

				// Адрес
				if ($data['KLCountry_id'] > 0 &&
					!($data['KLRgn_id'] > 0 || $data['KLSubRgn_id'] > 0 || $data['KLCity_id'] > 0 || $data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$this->where .= " and AR.KLCountry_id = :KLCountry_id";
					$this->queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Адрес
				if ($data['KLRgn_id'] > 0 &&
					!($data['KLSubRgn_id'] > 0 || $data['KLCity_id'] > 0 || $data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$this->where .= " and AR.KLRgn_id = :KLRgn_id";
					$this->queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Адрес
				if ($data['KLSubRgn_id'] > 0 &&
					!($data['KLCity_id'] > 0 || $data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$this->where .= " and AR.KLSubRgn_id = :KLSubRgn_id";
					$this->queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Адрес
				if ($data['KLCity_id'] > 0 &&
					!($data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$this->where .= " and AR.KLCity_id = :KLCity_id";
					$this->queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Адрес
				if ($data['KLTown_id'] > 0 &&
					!($data['KLStreet_id'] > 0)) {
					$this->where .= " and AR.KLTown_id = :KLTown_id";
					$this->queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Адрес
				if ($data['KLStreet_id'] > 0) {
					$this->where .= " and AR.KLStreet_id = :KLStreet_id";
					$this->queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Адрес
				if (strlen($data['Address_House']) > 0) {
					$this->where .= " and AR.Address_House = :Address_House";
					$this->queryParams['Address_House'] = $data['Address_House'];
				}
				if (strlen($data['Address_Corpus']) > 0) {
					$this->where .= " and AR.Address_Corpus = :Address_Corpus";
					$this->queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}
				// Адрес
				if ($data['KLAreaType_id'] > 0) {
					$this->where .= " and AR.KLAreaType_id = :KLAreaType_id";
					$this->queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}

				$this->where .= ")) ";
			}
		}

		if ($data['Person_NoPolis']) {
			$this->where .= " and " . $alias . ".Polis_id is null";
		} else {
			if ((strlen($data['Polis_Num']) > 0) || (strlen($data['Polis_Ser']) > 0) || ($data['PolisType_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['OMSSprTerr_id'] > 0) || $data['Person_NoOrgSMO']) {
				$this->where .= " and exists (select Polis_id from Polis with (nolock) left join OmsSprTerr with (nolock) on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id";
				$this->where .= " WHERE Polis.Polis_id = " . $alias . ".Polis_id";

				// Пациент
				if ($data['OMSSprTerr_id'] > 0) {
					if ($data['OMSSprTerr_id'] == 100500) {
						if (isset($data['session']['region']) && isset($data['session']['region']['number']) && $data['session']['region']['number'] > 0) {
							$this->where .= " and OmsSprTerr.KLRgn_id <> " . $data['session']['region']['number'];
						}
					} else {
						$this->where .= " and Polis.OmsSprTerr_id = :OMSSprTerr_id";
					}

					$this->queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
				}

				// Пациент
				if ($data['Person_NoOrgSMO']) {
					$this->where .= " and Polis.OrgSmo_id IS NULL";
				} elseif ($data['OrgSmo_id'] > 0) {
					$this->where .= " and Polis.OrgSmo_id = :OrgSmo_id";
					$this->queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
				}

				// Пациент
				if (strlen($data['Polis_Num']) > 0) {
					$this->where .= " and Polis.Polis_Num = :Polis_Num";
					$this->queryParams['Polis_Num'] = $data['Polis_Num'];
				}

				// Пациент
				if (strlen($data['Polis_Ser']) > 0) {
					$this->where .= " and Polis.Polis_Ser = :Polis_Ser";
					$this->queryParams['Polis_Ser'] = $data['Polis_Ser'];
				}

				// Пациент
				if ($data['PolisType_id'] > 0) {
					$this->where .= " and Polis.PolisType_id = :PolisType_id";
					$this->queryParams['PolisType_id'] = $data['PolisType_id'];
				}

				$this->where .= ") ";
			}
		}

		if (isset($data['soc_card_id']) && strlen($data['soc_card_id']) >= 25) {
			$this->where .= " and LEFT(" . $alias . ".Person_SocCardNum, 19) = :SocCardNum ";
			$this->queryParams['SocCardNum'] = substr($data['soc_card_id'], 0, 19);
		}

		if ($data['PersonPeriodicType_id'] == 3) {
			$this->where .= ") ";
		}
	}

	/**
	 * Возвращает данные для select'а в запросе
	 */
	function getSelect($data) {
		$this->select = "
			PS.Person_id
		";
	}

	/**
	 * Возвращает данные для фильтрации
	 */
	function getFromAndWhere($data) {
		$this->from = "
			v_PersonState PS (nolock)
		";
	}

	/**
	 * Возвращает данные для сортировки
	 */
	function getOrderBy($data) {
		$this->orderBy = "
			PS.Person_SurName,
			PS.Person_FirName,
			PS.Person_SecName
		";
	}

	/**
	 * Возвращает опции запроса
	 */
	function getOption($data) {
		$this->option = '';

		if ($this->optionRecompile == true) {
			$this->option = "
				OPTION (RECOMPILE)
			";
		}
	}

	/**
	 * Возвращает данные для блока with
	 */
	function getAdditWith($data) {

	}



	/**
	 * Формирование поискового запроса
	 */
	function getSql($data) {
		$this->additWith = array();
		$this->variables = array(
			'declare @getDT datetime = dbo.tzGetDate();'
		);
		$this->select = '';
		$this->from = '';
		$this->where = '(1=1)';
		$this->orderBy = '';
		$this->queryParams = array();

		// достаём поля которые нужны для выборки
		$this->getSelect($data);
		// достаём базовые фильтры
		$this->getPersonPeriodicFilters($data);
		$this->getPersonCardFilters($data);
		$this->getPrivilegeFilters($data);
		$this->getPmUserFilters($data);
		// достаём таблицы из которых будет выборка и фильтры заодно.
		$this->getFromAndWhere($data);
		// достаём блок with
		$this->getAdditWith($data);
		// достаём блок option
		$this->getOption($data);

		// достаём сортировку
		$this->getOrderBy($data);

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			if (empty($this->archiveAlias)) {
				$this->archiveAlias = $this->main_alias;
			}
			$this->select .= "
				, case when ISNULL({$this->archiveAlias}.{$this->main_table}_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$this->where .= " and ISNULL({$this->archiveAlias}.{$this->main_table}_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$data['start'] = $data['start'] - $data['archiveStart']; // начало архивных записей за вычетом уже показанных актуальных
				$this->where .= " and ISNULL({$this->archiveAlias}.{$this->main_table}_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$this->where .= "";
			}
		}

		$this->query = "";
		if (!empty($this->variables)) {
			$this->query .= "
				-- variables
				" . implode(' ', $this->variables) . "
				-- end variables
			";
		}
		if (!empty($this->additWith)) {
			$this->query .= "
				-- addit with
				with " . implode(', ', $this->additWith) . "
				-- end addit with
			";
		}
		$this->query .= "
			select
				-- select
				{$this->select}
				-- end select
			from
				-- from
				{$this->from}
				-- end from
			where
				-- where
				{$this->where}
				-- end where
			order by
				-- order by
				{$this->orderBy}
				-- end order by
			-- option
			{$this->option}
			-- end option
		";

		return array($this->query, $this->queryParams);
	}

	/**
	 * 	Выполнение поискового запроса
	 */
	function searchData($data) {
		list($query, $queryParams) = $this->getSql($data);
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if (strtolower($data['onlySQL']) == 'on' && isSuperAdmin()) {
			echo getDebugSQL($query, $queryParams);
			return false;
		}

		if (!empty($data['getCountOnly'])) {
			$get_count_query = getCountSQLPH($query);
			// приходится так делать из-за группировки
			if (in_array($data['SearchFormType'], array("EvnInfectNotify", "PersonDopDisp"))) {
				$get_count_query = getCountSQLPH($query, '*', '', '', true);
			}
			$get_count_result = $this->db->query($get_count_query, $queryParams);
			if (is_object($get_count_result)) {
				$resp = $get_count_result->result('array');
				return array(
					'Error_Msg' => '',
					'totalCount' => $resp[0]['cnt']
				);
			} else {
				return false;
			}
		}

		$response = array();
		if ($data['start'] >= 0 && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			//die(getDebugSQL($limit_query, $queryParams));
			$result = $this->db->query($limit_query, $queryParams);
		} else {
			$result = $this->db->query($query, $queryParams);
		}

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res)) {
				$response['data'] = $res;
				$response['totalCount'] = $data['start'] + count($res);
				if (count($res) >= $data['limit']) {
					if (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
						// для архивных всё равно необходимо количество, т.к. они грузятся когда количество уже известно
						$get_count_query = getCountSQLPH($query);
						// приходится так делать из-за группировки
						if (in_array($data['SearchFormType'], array("EvnInfectNotify", "PersonDopDisp"))) {
							$get_count_query = getCountSQLPH($query, '*', '', '', true);
						}

						$get_count_result = $this->db->query($get_count_query, $queryParams);

						if (is_object($get_count_result)) {
							$response['totalCount'] = $get_count_result->result('array');
							$response['totalCount'] = $response['totalCount'][0]['cnt'];
						}
					} else {
						$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}

		if (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
			if (!empty($response['totalCount'])) {
				$response['totalCount'] = $response['totalCount'] + $data['archiveStart'];
			}
		}

		return $response;
	}
}
