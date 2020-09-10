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
class Search_model extends swModel {

	protected $schema = "dbo";  //региональная схема
	protected $comboSchema = "dbo";
	protected $lpuList = array();

	/**
	 * 	Конструктор
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		if($this->regionNick == 'kz'){
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
	function getPrivilegeFilters($data, &$filter, &$queryParams) {
		// Льгота
		if ($data['RegisterSelector_id'] == 1) {
			$filter .= " and PT.ReceptFinance_id = 1";
			$queryParams['RegisterSelector_id'] = $data['RegisterSelector_id'];
		} else if ($data['RegisterSelector_id'] == 2) {
			$filter .= " and PT.ReceptFinance_id = 2";
			$queryParams['RegisterSelector_id'] = $data['RegisterSelector_id'];
		} else {
			// $filter .= " and ((PT.ReceptFinance_id = 1 and PT.PrivilegeType_Code < 500) or (PT.ReceptFinance_id = 2 and PT.PrivilegeType_Code < 500" . ($data['SearchFormType'] == 'PersonPrivilege' ? " and PP.Lpu_id = :Lpu_id" : "") . "))";
		}
		// в связи с тем, что на форме swPrivilegeSearchWindow ("Регистр льготников: Поиск") закомментирован комбобокс RegisterSelector_id, для нее отдельно прописываем фильтр по типу финансирования
		// Кроме того, на всех формах, где используется поле RegisterSelector_id предусмотрены только два значения: Федеральный и Регоинальный, а на форме "Регистр льготников: Поиск"
		// добавлено значение "Федеральный бюджет ОССЗ" и др., для этих значений фильтрация тоже предусматривается в следующем блоке кода
		if ($data['SearchFormType'] == 'PersonPrivilege') {
			//в некторых регионах не выделены льготные категории для Программы ЛЛО со значением ВЗН (7 или 14 нозологий), там они являются региональными льготами
			if($data['ReceptFinance_id'] == 3 && in_array($this->getRegionNick(), ['perm', 'ufa'])) {
				$filter .= " and PT.ReceptFinance_id = 2 and PS.Person_Is7Noz = 1";
			} else if (!empty($data['ReceptFinance_id'])) {
				$filter .= " and PT.ReceptFinance_id = {$data['ReceptFinance_id']}";
			}
		}

		if (!empty($data['PersonPrivilege_deleted'])) {
			$filter .= " and isnull(PP.PersonPrivilege_deleted, 1) = :PersonPrivilege_deleted";
			$queryParams['PersonPrivilege_deleted'] = $data['PersonPrivilege_deleted'];
		}

		if (isset($data['Lpu_prid']) && $data['Lpu_prid'] > 0) {
			$filter .= " and PP.Lpu_id = :Lpu_prid";
			$queryParams['Lpu_prid'] = $data['Lpu_prid'];
		}

		// Льгота
		if (isset($data['PrivilegeType_id'])) {
			$filter .= " and PT.PrivilegeType_id = :PrivilegeType_id";
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
		}

		// Подкатегория (KZ)
		if (isset($data['SubCategoryPrivType_id']) && $this->regionNick == 'kz') {
			$filter .= " and PPSCPT.SubCategoryPrivType_id = :SubCategoryPrivType_id";
			$queryParams['SubCategoryPrivType_id'] = $data['SubCategoryPrivType_id'];
		}

		// Льгота
		if ($data['PrivilegeStateType_id'] == 1) {
			$filter .= " and PP.PersonPrivilege_begDate is not null";
			$filter .= " and PP.PersonPrivilege_begDate <= @getDT";
			$filter .= " and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate > cast(convert(char(10), @getDT, 112) as datetime))";
		}

		//  Для сигнальной информации, у которых заканчивается льгота не позднее 30 с предыдущего дня
		if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
			$filter .= " and PP.PersonPrivilege_endDate is not null";
			$filter .= " and PP.PersonPrivilege_endDate >  cast(convert(char(10),  DATEADD (day,-1, @getDT), 112) as datetime)";
			$filter .= " and PP.PersonPrivilege_endDate <  cast(convert(char(10),  (SELECT DATEADD (day, 30, DATEADD (day,-1, @getDT))), 112) as datetime)  ";
		}

		// Льгота
		if (isset($data['Privilege_begDate'])) {
			$filter .= " and PP.PersonPrivilege_begDate = cast(:Privilege_begDate as datetime)";
			$queryParams['Privilege_begDate'] = $data['Privilege_begDate'];
		}

		// Льгота
		if (isset($data['Privilege_begDate_Range'][0])) {
			$filter .= " and PP.PersonPrivilege_begDate >= cast(:Privilege_begDate_Range_0 as datetime)";
			$queryParams['Privilege_begDate_Range_0'] = $data['Privilege_begDate_Range'][0];
		}

		// Льгота
		if (isset($data['Privilege_begDate_Range'][1])) {
			$filter .= " and PP.PersonPrivilege_begDate <= cast(:Privilege_begDate_Range_1 as datetime)";
			$queryParams['Privilege_begDate_Range_1'] = $data['Privilege_begDate_Range'][1];
		}

		// Льгота
		if (isset($data['Privilege_endDate'])) {
			$filter .= " and PP.PersonPrivilege_endDate = cast(:Privilege_endDate as datetime)";
			$queryParams['Privilege_endDate'] = $data['Privilege_endDate'];
		}

		// Льгота
		if (isset($data['Privilege_endDate_Range'][0])) {
			$filter .= " and PP.PersonPrivilege_endDate >= cast(:Privilege_endDate_Range_0 as datetime)";
			$queryParams['Privilege_endDate_Range_0'] = $data['Privilege_endDate_Range'][0];
		}

		// Льгота
		if (isset($data['Privilege_endDate_Range'][1])) {
			$filter .= " and PP.PersonPrivilege_endDate <= cast(:Privilege_endDate_Range_1 as datetime)";
			$queryParams['Privilege_endDate_Range_1'] = $data['Privilege_endDate_Range'][1];
		}
	}

	/**
	 * Фильтр по правам доступа ко льготам
	 */
	function getPrivilegeAccessRightsFilters($data, &$filter, &$queryParams) {
		$groups = explode('|', $data['session']['groups']);
		$user_groups = "'" . $groups[0] . "'";
		if (count($groups) > 1) {
			for ($i = 1; $i < count($groups); $i++) {
				$user_groups .= ",'" . $groups[$i] . "'";
			}
		}
		//$filter .= " and ( PAR.PrivilegeAccessRights_id is null OR PAR.PrivilegeAccessRights_UserGroups in (".$user_groups.") OR PAR.Lpu_id = :Lpu_id )";
		if ($data['SearchFormType'] == 'EvnRecept') {
			$alias = 'ER'; //Для поиска рецептов PrivilegeType_id берем из ER
		} else {
			$alias = 'PT';
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("{$alias}.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}

		if ($this->getRegionNick() == 'ufa') {

			if ($data['SearchFormType'] == 'PersonPrivilege') {
				$alias = 'PP';
			} elseif ($data['SearchFormType'] == 'EvnRecept') {
				$alias = 'ER';
			}

			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= " and exists (select top 1 PersonPrivilege_id from v_PersonPrivilege with (nolock) where PrivilegeType_id = {$alias}.PrivilegeType_id and Person_id = PS.Person_id {$lpuFilter})";
		}

		$queryParams['Lpu_id'] = $data['Lpu_id'];
	}

	/**
	 * Фильтры по прикреплению
	 */
	function getPersonCardFilters($data, &$filter, &$queryParams, &$orderby, &$pac_filter) {
		// Прикрепление
		if (isset($data['PersonCard_endDate'])) {
			$filter .= " and PC.PersonCard_endDate = :PersonCard_endDate";
			$queryParams['PersonCard_endDate'] = $data['PersonCard_endDate'];
		}

		// Прикрепление
		if (isset($data['PersonCard_endDate_Range'][0])) {
			$filter .= " and PC.PersonCard_endDate >= :PersonCard_endDate_Range_0";
			$queryParams['PersonCard_endDate_Range_0'] = $data['PersonCard_endDate_Range'][0];
		}

		// Прикрепление
		if (isset($data['PersonCard_endDate_Range'][1])) {
			$filter .= " and PC.PersonCard_endDate <= :PersonCard_endDate_Range_1";
			$queryParams['PersonCard_endDate_Range_1'] = $data['PersonCard_endDate_Range'][1];
		}

		// Прикрепление
		if ($data['AttachLpu_id'] > 0) {
			if ($data['AttachLpu_id'] == 666666 && in_array($data['SearchFormType'], array('EvnPLDispDop13', 'EvnPLDispDop13Sec')) && getRegionNick() === 'ekb' )
			{
				// Вариант "Без прикрепления к МО", используется только в екб на формах поиск двн 1 и 2 этап. Выводятся люди которые не имебт прикрепления ни к одной МО

			} else if ($data['AttachLpu_id'] == 100500)
			{
				$filter .= " and PC.Lpu_id is not null and PC.Lpu_id <> :Lpu_id";
			}
			else
			{
				//Прикрепление для ИПРА вернул array('BskRegistry', 'IPRARegistry'))
				$filter .= !in_array($data['SearchFormType'], array('BskRegistry')) ? " and PC.Lpu_id = :AttachLpu_id" : "";
				$filter .= " and PC.Lpu_id = :AttachLpu_id";

				//Прикрепление для Сигнальной информации
				//для участковых врачей показываем только пациентов с его участка
				if (in_array($data['SearchFormType'], array('EvnUslugaPar', 'PersonPrivilege', 'CmpCloseCard'))) {

					if (isset($data['MedPersonal_id']) && ($data['MedPersonal_id'] > 0)) {
						$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
						$filter .= " and MedStaffRegion.MedPersonal_id = :MedPersonal_id";
					}
				}
				//https://redmine.swan.perm.ru/issues/94164
				//пациенты на спец учёте доступны не всем
				//if($data['SearchFormType'] == 'IPRARegistry'){
				//	if(!in_array($data['AttachLpu_id'], array(338, 392, 393, 89, 86, 391, 394, 150016))){
				//		$filter .= " and IR.IPRARegistry_FGUMCEnumber not in  (11,12,13,14,16)";
				//	}
				//}
			}


			$queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
		}

		// LPU_id - МО сопровождения
		if( $data['SearchFormType'] == 'IPRARegistry' && isset($data['LPU_id']) && !empty($data['LPU_id']) && $this->getRegionNick() == 'ufa') //!isMinZdrav()
		{
			$filter .= " AND ( ( ";
			$filter .= " IR.IPRARegistry_FGUMCEnumber IN (11,12,13,14,16)";
			$filter .= " and IR.IPRARegistry_DirectionLPU_id IN (338, 392, 393, 89, 86, 391, 394, 150016)";
			$filter .= " and IR.Lpu_id = :Lpu_id ";
			$filter .= " and IR.Lpu_id IN (338, 392, 393, 89, 86, 391, 394, 150016)";
			$filter .= " ) OR ";
			$filter .= " IR.Lpu_id NOT IN (338, 392, 393, 89, 86, 391, 394, 150016) ) ";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		/* else { 											//https://redmine.swan.perm.ru/issues/19285
		  if ( $data['SearchFormType'] == 'PersonCard' )
		  $filter .= " and PC.Lpu_id = :Lpu_id";
		  } */

		// Прикрепление
		if ($data['LpuAttachType_id'] > 0) {
			$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
		}

		// Пациент
		if (strlen($data['PersonCard_Code']) > 0) {
			if (!empty($data['PartMatchSearch'])) {
				// включен чекбокс "Поиск по частичному совпадению"
				if (!empty($this->config->config['blockSlowDownFunctions'])) {
					return array('Error_Msg' => 'Функционал поиска по частичному совпадению временно заблокирован. Приносим извинения за доставленные неудобства.');
				}

				$filter .= " and PC.PersonCard_Code LIKE '%'+:PersonCard_Code+'%'";
				$orderby = "case when ISNULL(CHARINDEX(:PersonCard_Code, pc.PersonCard_Code), 0) > 0 then CHARINDEX(:PersonCard_Code, pc.PersonCard_Code) else 99 end,";
			} else {
				if(in_array($data['SearchFormType'],array('EvnPL','EvnVizitPL'))){
					//$filter .= " and PAC.PersonAmbulatCard_Num = :PersonCard_Code";
					$pac_filter .= " and exists(select top 1 * from v_PersonAmbulatCard PAC2 where PAC2.PersonAmbulatCard_Num = :PersonCard_Code and PAC2.Person_id = PS.Person_id and PAC2.Lpu_id = ".$data['session']['lpu_id'].')';
				}
				else
					$filter .= " and PC.PersonCard_Code = :PersonCard_Code";
			}

			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		}

		// Прикрепление
		if (isset($data['PersonCard_begDate'])) {
			$filter .= " and cast(PC.PersonCard_begDate as date) = :PersonCard_begDate";
			$queryParams['PersonCard_begDate'] = $data['PersonCard_begDate'];
		}

		// Прикрепление
		if (isset($data['PersonCard_begDate_Range'][0])) {
			$filter .= " and PC.PersonCard_begDate >= cast(:PersonCard_begDate_Range_0 as datetime)";
			$queryParams['PersonCard_begDate_Range_0'] = $data['PersonCard_begDate_Range'][0];
		}

		// Прикрепление
		if (isset($data['PersonCard_begDate_Range'][1])) {
			$filter .= " and PC.PersonCard_begDate <= cast(:PersonCard_begDate_Range_1 as datetime)";
			$queryParams['PersonCard_begDate_Range_1'] = $data['PersonCard_begDate_Range'][1];
		}

		// Прикрепление
		if ($data['PersonCard_IsAttachCondit'] > 0) {
			$filter .= " and ISNULL(PC.PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
			$queryParams['PersonCard_IsAttachCondit'] = $data['PersonCard_IsAttachCondit'];
		}
		if ($data['PersonCardAttach'] > 0) {
			if ($data['PersonCardAttach'] == 1) {
				$filter .= " and PC.PersonCardAttach_id IS NULL";
			} else {
				$filter .= " and PC.PersonCardAttach_id IS NOT NULL";
			}
		}

		// Прикрепление
		if ($data['PersonCard_IsDms'] > 0) {
			$exists = "";
			if ($data['PersonCard_IsDms'] == 1)
				$exists = " not ";
			$filter .= " and " . $exists . " exists(
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
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];

			if ($data['LpuRegion_id'] == -1) {
				$filter .= " and LR.LpuRegion_id is null";
			} else {
				$filter .= " and LR.LpuRegion_id = :LpuRegion_id";
			}
		}
        if(isset($data['LpuRegion_Fapid'])){
            $queryParams['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
            $filter .= " and LR_Fap.LpuRegion_id = :LpuRegion_Fapid";
        }
		// Прикрепление
		if ($data['LpuRegionType_id'] > 0) {
			$filter .= " and LR.LpuRegionType_id = :LpuRegionType_id";
			$queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
		}
	}

	/**
	 * Фильтры по периодикам
	 */
	protected function getPersonPeriodicFilters($data, &$filter, &$queryParams, $main_alias, $alias = 'PS') {
		// Пациент
		if (isset($data['Person_Birthday'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) = cast(:Person_Birthday as datetime)";
			} else {
				$filter .= " and " . $alias . ".Person_BirthDay = cast(:Person_Birthday as datetime)";
			}

			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}


		// Пациент
		if (isset($data['Person_BirthdayYear'])) {
			$filter .= " and YEAR(" . $alias . ".Person_BirthDay) = :Person_BirthdayYear";
			$queryParams['Person_BirthdayYear'] = $data['Person_BirthdayYear'];
		}

		//Диапазон дат обследований
		if (isset($data['EvnPLWOW_setDate_Range'][0])) {
			$filter .= " and EPW.EvnPLWow_setDate >= cast (:EvnPLWOW_setDate_Range_0 as datetime)";
			$queryParams['EvnPLWOW_setDate_Range_0'] = $data['EvnPLWOW_setDate_Range'][0];
		}
		if (isset($data['EvnPLWOW_setDate_Range'][1])) {
			$filter .= " and EPW.EvnPLWow_setDate <= cast (:EvnPLWOW_setDate_Range_1 as datetime)";
			$queryParams['EvnPLWOW_setDate_Range_1'] = $data['EvnPLWOW_setDate_Range'][1];
		}

		// Пациент
		if (isset($data['Person_Birthday_Range'][0])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) >= cast(:Person_Birthday_Range_0 as datetime)";
			} else {
				$filter .= " and " . $alias . ".Person_BirthDay >= cast(:Person_Birthday_Range_0 as datetime)";
			}

			$queryParams['Person_Birthday_Range_0'] = $data['Person_Birthday_Range'][0];
		}

		// Пациент
		if (isset($data['Person_Birthday_Range'][1])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) <= cast(:Person_Birthday_Range_1 as datetime)";
			} else {
				$filter .= " and " . $alias . ".Person_BirthDay <= cast(:Person_Birthday_Range_1 as datetime)";
			}

			$queryParams['Person_Birthday_Range_1'] = $data['Person_Birthday_Range'][1];
		}

		// Пациент
		if (strlen($data['Person_Code']) > 0) {
			$filter .= " and " . $alias . ".Person_EdNum = :Person_Code";
			$queryParams['Person_Code'] = $data['Person_Code'];
		}

		// Пациент
		if (strlen($data['Person_Firname']) > 0) {
			if ($data['SearchFormType'] == 'CmpCallCard') {
				$filter .= " and ISNULL(" . $alias . ".Person_FirName, CCC.Person_FirName) like :Person_Firname";
			} else if ($data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and COALESCE(CLC.Name, " . $alias . ".Person_FirName, CCC.Person_FirName) like :Person_Firname";
			} else {
				$filter .= " and " . $alias . ".Person_FirName like :Person_Firname";
			}

			$queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		// Пациент
		if (strlen($data['Person_Secname']) > 0) {
			if ($data['SearchFormType'] == 'CmpCallCard') {
				$filter .= " and ISNULL(" . $alias . ".Person_SecName, CCC.Person_SecName) like :Person_Secname";
			} else if ($data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and COALESCE(CLC.Middle, " . $alias . ".Person_SecName, CCC.Person_SecName) like :Person_Secname";
			} else {
				$filter .= " and " . $alias . ".Person_SecName like :Person_Secname";
			}

			$queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}

		// Пациент
		if (strlen($data['Person_Surname']) > 0) {
			$queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';

			switch($data['SearchFormType']) {
				case 'CmpCallCard':
					$filter .= " and ISNULL(" . $alias . ".Person_SurName, CCC.Person_SurName) like :Person_Surname";
					break;

				case 'CmpCloseCard':
					$filter .= " and COALESCE(CLC.Fam, " . $alias . ".Person_Surname, CCC.Person_SurName) like :Person_Surname";
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
						$queryParams['Person_Surname'] = rtrim($data['Person_Surname']);
						$filter .= " and PEH.PersonEncrypHIV_Encryp like (:Person_Surname)";
					} else {
						$filter .= " and " . $alias . ".Person_SurName like :Person_Surname";
					}
					break;
				default:
					$filter .= " and " . $alias . ".Person_SurName like :Person_Surname";
			}
		}

		/*
		  // Пациент
		  if ( isset($data['PersonAge']) ) {
		  if ( $data['SearchFormType'] == 'CmpCallCard' ) {
		  $filter .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) between :PersonAge_Min and :PersonAge_Max";
		  }
		  else {
		  $filter .= " and " . $alias . ".Person_BirthDay between :PersonAge_Min and :PersonAge_Max";
		  }

		  $queryParams['PersonAge_Min'] = date('Y-m-d', strtotime("+1 day", strtotime("-" . intval($data['PersonAge'] + 1) . " years", time())));
		  $queryParams['PersonAge_Max'] = date('Y-m-d', strtotime("-" . intval($data['PersonAge']) . " years", time()));
		  }

		  // Возраст пациента
		  if ( isset($data['PersonAge_Max']) ) {
		  if ( $data['SearchFormType'] == 'CmpCallCard' ) {
		  $filter .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) > :PersonAge_Max";
		  }
		  else {
		  $filter .= " and " . $alias . ".Person_BirthDay > :PersonAge_Max";
		  }

		  $queryParams['PersonAge_Max'] = date('Y-m-d', strtotime("-" . intval($data['PersonAge_Max'] + 1) . " years", time()));
		  }

		  // Возраст пациента
		  if ( isset($data['PersonAge_Min']) ) {
		  if ( $data['SearchFormType'] == 'CmpCallCard' ) {
		  $filter .= " and ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay) <= :PersonAge_Min";
		  }
		  else {
		  $filter .= " and " . $alias . ".Person_BirthDay <= :PersonAge_Min";
		  }

		  $queryParams['PersonAge_Min'] = date('Y-m-d', strtotime("-" . intval($data['PersonAge_Min']) . " years", time()));
		  }
		 */

		// В зависимости от типа поиска надо искать либо исходя из текущей даты, либо исходя из даты случая (для ТАП и КВС)
		$getdate = '@getDT';
		if (in_array($data['PersonPeriodicType_id'], array(2, 3))) {
			if (in_array($data['SearchFormType'], array('EvnVizitPL', 'EvnPS', 'EvnSection', 'EvnPLStom', 'EvnVizitPLStom'))) {
				$getdate = $main_alias . "." . $data['SearchFormType'] . "_setDate";
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
				$filter .= " and dbo.Age2(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay), {$getdate}) = :PersonAge";
			} else {
				$filter .= " and dbo.Age2(" . $alias . ".Person_BirthDay, {$getdate}) = :PersonAge";
			}

			$queryParams['PersonAge'] = intval($data['PersonAge']);
		}

		// Возраст пациента (с)
		if (isset($data['PersonAge_Max'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and dbo.Age2(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay), {$getdate}) <= :PersonAge_Max";
			} else {
				$filter .= " and dbo.Age2(" . $alias . ".Person_BirthDay, {$getdate}) <= :PersonAge_Max";
			}

			$queryParams['PersonAge_Max'] = intval($data['PersonAge_Max']);
		}

		// Возраст пациента (по)
		if (isset($data['PersonAge_Min'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and dbo.Age2(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay), {$getdate}) >= :PersonAge_Min";
			} else {
				$filter .= " and dbo.Age2(" . $alias . ".Person_BirthDay, {$getdate}) >= :PersonAge_Min";
			}

			$queryParams['PersonAge_Min'] = intval($data['PersonAge_Min']);
		}


		// Пациент
		if (isset($data['PersonBirthdayYear'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and year(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay)) = :PersonBirthdayYear";
			} else {
				$filter .= " and year(" . $alias . ".Person_BirthDay) = :PersonBirthdayYear";
			}

			$queryParams['PersonBirthdayYear'] = intval($data['PersonBirthdayYear']);
		}

		// Пациент
		if (isset($data['PersonBirthdayYear_Max'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and year(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay)) <= :PersonBirthdayYear_Max";
			} else {
				$filter .= " and year(" . $alias . ".Person_BirthDay) <= :PersonBirthdayYear_Max";
			}

			$queryParams['PersonBirthdayYear_Max'] = intval($data['PersonBirthdayYear_Max']);
		}

		// Пациент
		if (isset($data['PersonBirthdayYear_Min'])) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and year(ISNULL(" . $alias . ".Person_BirthDay, CCC.Person_BirthDay)) >= :PersonBirthdayYear_Min";
			} else {
				$filter .= " and year(" . $alias . ".Person_BirthDay) >= :PersonBirthdayYear_Min";
			}

			$queryParams['PersonBirthdayYear_Min'] = intval($data['PersonBirthdayYear_Min']);
		}

		// Пациент (месяц рождения)
		if (isset($data['PersonBirthdayMonth'])) {
			$filter .= " and month(" . $alias . ".Person_BirthDay) = :PersonBirthdayMonth";

			$queryParams['PersonBirthdayMonth'] = $data['PersonBirthdayMonth'];
		}

		// Наличие СНИЛС
		if (isset($data['SnilsExistence']) && $data['SnilsExistence'] != '') {
			if ($data['SnilsExistence'] == 1) {
				$filter .= " and (PS.Person_Snils = '' OR PS.Person_Snils is null)";
			} else {
				$filter .= " and (PS.Person_Snils <> '' OR PS.Person_Snils is not null)";
			}
		}

		// Пациент (доп.)
		if (strlen($data['Person_Snils']) > 0) {
			$filter .= " and " . $alias . ".Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		// Пациент (доп.)
		if (strlen($data['Person_Inn']) > 0) {
			$filter .= "
				and exists(
					select top 1 t.Person_id
					from v_PersonInn t with(nolock)
					where t.Person_id = " . $alias . ".Person_id and t.PersonInn_Inn = :Person_Inn
				)
			";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		// Пациент (доп.)
		if ($data['Sex_id'] > 0) {
			if ($data['SearchFormType'] == 'CmpCallCard' || $data['SearchFormType'] == 'CmpCloseCard') {
				$filter .= " and ISNULL(" . $alias . ".Sex_id, CCC.Sex_id) = :Sex_id";
			} else {
				$filter .= " and isnull(" . $alias . ".Sex_id, 3) = :Sex_id";
			}

			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		// Пациент (доп.)
		if ($data['SocStatus_id'] > 0) {
			$filter .= " and " . $alias . ".SocStatus_id = :SocStatus_id";
			$queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}

		// Пациент (доп.)
		if (isset($data['Person_IsBDZ'])) {
			$filter .= " and exists (
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
				$filter .= " and ISNULL(CCC.Person_id,0) " . ($data['Person_isIdentified'] == 2 ? "<>" : "=") . " 0";
			}
		}

		// Пациент (доп.)
		if (isset($data['Person_IsDisp'])) {
			$filter .= " and " . ($data['Person_IsDisp'] == 1 ? "not" : "") . " exists (
				select top 1
					1
				from PersonDisp PDTemp with (nolock)
				where PDTemp.Person_id = " . $alias . ".Person_id
					and PDTemp.PersonDisp_begDate <= @getDT
					and (PDTemp.PersonDisp_endDate is null or PDTemp.PersonDisp_endDate > @getDT)
			)";
		}

		if ((strlen($data['Document_Num']) > 0) || (strlen($data['Document_Ser']) > 0) || ($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0)) {
			$filter .= " and exists (select Document_id from Document with(nolock) ";
			$filter .= " WHERE Document.Document_id = " . $alias . ".Document_id";

			// Пациент (доп.)
			if (strlen($data['Document_Num']) > 0) {
				$filter .= " and Document.Document_Num = :Document_Num";
				$queryParams['Document_Num'] = $data['Document_Num'];
			}

			// Пациент (доп.)
			if (strlen($data['Document_Ser']) > 0) {
				$filter .= " and Document.Document_Ser = :Document_Ser";
				$queryParams['Document_Ser'] = $data['Document_Ser'];
			}

			// Пациент (доп.)
			if ($data['DocumentType_id'] > 0) {
				$filter .= " and Document.DocumentType_id = :DocumentType_id";
				$queryParams['DocumentType_id'] = $data['DocumentType_id'];
			}

			// Пациент (доп.)
			if ($data['OrgDep_id'] > 0) {
				$filter .= " and Document.OrgDep_id = :OrgDep_id";
				$queryParams['OrgDep_id'] = $data['OrgDep_id'];
			}

			$filter .= ") ";
		}

		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0)) {
			$filter .= " and exists (select Job_id from Job  with(nolock)";
			$filter .= " WHERE Job.Job_id = " . $alias . ".Job_id";

			// Пациент (доп.)
			if ($data['Org_id'] > 0) {
				$filter .= " and Job.Org_id = :Org_id";
				$queryParams['Org_id'] = $data['Org_id'];
			}

			// Пациент (доп.)
			if ($data['Post_id'] > 0) {
				$filter .= " and Job.Post_id = :Post_id";
				$queryParams['Post_id'] = $data['Post_id'];
			}

			$filter .= ") ";
		}

		if (strtolower($data['Person_NoAddress']) == 'on') {
			switch ($data['AddressStateType_id']) {
				case 1:
					$filter .= " and " . $alias . ".UAddress_id is null";
					break;

				case 2:
					$filter .= " and " . $alias . ".PAddress_id is null";
					break;

				default:
					$filter .= " and " . $alias . ".UAddress_id is null";
					$filter .= " and " . $alias . ".PAddress_id is null";
					break;
			}
		} else if (($data['KLAreaType_id'] > 0) || ($data['KLCountry_id'] > 0) || (!empty($data['Person_citizen']) && $data['Person_citizen'] !=1) || ($data['KLRgn_id'] > 0) ||
				($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) ||
				($data['KLStreet_id'] > 0) ||(strlen($data['Address_Corpus']) > 0)|| (strlen($data['Address_House']) > 0) || (strlen($data['Address_Street']) > 0)
		) {
			if ($data['AddressStateType_id'] == 1) { /* (1 - Адрес регистрации, 2 - Адрес проживания, 3 - Адрес рождения) */

                // if (!empty($data['Person_citizen']) &&($data['Person_citizen']==3)) {
                //     $filter .= " and PS.KLCountry_id !=643";
                // }

			    $filter .= " and exists (select AR.Address_id from Address AR with (nolock) ";

				if (!empty($data['Address_Street'])) {
					$filter .= " inner join KLStreet KLS with (nolock) on KLS.KLStreet_id = AR.KLStreet_id ";
				}

				$filter .= " where AR.Address_id = " . $alias . ".UAddress_id";

				// Адрес
				if (!empty($data['Address_Street'])) {
					$filter .= " and KLS.KLStreet_Name like :Address_Street ";
					$queryParams['Address_Street'] = $data['Address_Street'] . '%';
				}

				// Адрес
				if ($data['KLCountry_id'] > 0) {
					$filter .= " and AR.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Адрес
				if ($data['KLRgn_id'] > 0) {
					$filter .= " and AR.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Адрес
				if ($data['KLSubRgn_id'] > 0) {
					$filter .= " and AR.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Адрес
				if ($data['KLCity_id'] > 0) {
					$filter .= " and AR.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Адрес
				if ($data['KLTown_id'] > 0) {
					$filter .= " and AR.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Адрес
				if ($data['KLStreet_id'] > 0) {
					$filter .= " and AR.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Адрес
				if (strlen($data['Address_House']) > 0) {
					$filter .= " and AR.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}
				if (strlen($data['Address_Corpus']) > 0) {
					$filter .= " and AR.Address_Corpus = :Address_Corpus";
					$queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}
				// Адрес
				if ($data['KLAreaType_id'] > 0) {
					if (getRegionNumber() == '50' && $data['KLAreaType_id'] == '1') {//#186092
						$filter .= " and AR.KLAreaType_id = 1";
					} else {
						$filter .= " and AR.KLAreaType_id = 2";
					}
					if (getRegionNumber() != '50') {
						$filter .= " and AR.KLAreaType_id = :KLAreaType_id";
					}

					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}

				$filter .= ") ";
			} else if ($data['AddressStateType_id'] == 2) {

                // if (!empty($data['Person_citizen']) &&($data['Person_citizen']==3)) {
                //     $filter .= " and PS.KLCountry_id !=643";
                // }

				$filter .= " and exists (select AP.Address_id from Address AP with (nolock) ";

				if (!empty($data['Address_Street'])) {
					$filter .= " inner join KLStreet KLS with (nolock) on KLS.KLStreet_id = AP.KLStreet_id ";
				}

				$filter .= " where AP.Address_id = " . $alias . ".PAddress_id";

				// Адрес
				if (!empty($data['Address_Street'])) {
					$filter .= " and KLS.KLStreet_Name like :Address_Street ";
					$queryParams['Address_Street'] = $data['Address_Street'] . '%';
				}

				// Адрес
				if ($data['KLCountry_id'] > 0) {
					$filter .= " and AP.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}


				// Адрес
				if ($data['KLRgn_id'] > 0) {
					$filter .= " and AP.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Адрес
				if ($data['KLSubRgn_id'] > 0) {
					$filter .= " and AP.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Адрес
				if ($data['KLCity_id'] > 0) {
					$filter .= " and AP.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Адрес
				if ($data['KLTown_id'] > 0) {
					$filter .= " and AP.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Адрес
				if ($data['KLStreet_id'] > 0) {
					$filter .= " and AP.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Адрес
				if (strlen($data['Address_House']) > 0) {
					$filter .= " and AP.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}
				if (strlen($data['Address_Corpus']) > 0) {
					$filter .= " and AP.Address_Corpus = :Address_Corpus";
					$queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}
				// Адрес
				if ($data['KLAreaType_id'] > 0) {
					if (getRegionNumber() == '50' && $data['KLAreaType_id'] == '1') {//#186092
						$filter .= " and AP.KLAreaType_id = 1";
					} else {
						$filter .= " and AP.KLAreaType_id = 2";
					}
					if (getRegionNumber() != '50') {
						$filter .= " and AP.KLAreaType_id = :KLAreaType_id";
					}
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}

				$filter .= ") ";
			} else {
				$filter .= " and exists (select Address_id from Address with (nolock)";

				$filter .= " where Address_id IN (" . $alias . ".PAddress_id, " . $alias. ".UAddress_id)";

				// Адрес
				if ($data['KLRgn_id'] > 0 &&
					!($data['KLSubRgn_id'] > 0 || $data['KLCity_id'] > 0 || $data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$filter .= " and KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Адрес
				if ($data['KLSubRgn_id'] > 0 &&
					!($data['KLCity_id'] > 0 || $data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$filter .= " and KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Адрес
				if ($data['KLCity_id'] > 0 &&
					!($data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
					$filter .= " and KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Адрес
				if ($data['KLTown_id'] > 0 &&
					!($data['KLStreet_id'] > 0)) {
					$filter .= " and KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Адрес
				if ($data['KLStreet_id'] > 0) {
					$filter .= " and KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Адрес
				if (strlen($data['Address_House']) > 0) {
					$filter .= " and Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}
				if (strlen($data['Address_Corpus']) > 0) {
					$filter .= " and Address_Corpus = :Address_Corpus";
					$queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}
				// Адрес
				if ($data['KLAreaType_id'] > 0) {
					$filter .= " and KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}

				$filter .= ") ";
			}

			$mainAlias = 'PS';
			if (!empty($data['Person_citizen']) &&($data['Person_citizen']==3) ) {//ищет по гражданству
				//$filter .= " and (exists (select KLCountry_id from v_PersonState PS  with (nolock) ";
				//$filter .= " and ((PS.KLCountry_id !=643"; // скобки поставил для того, чтобы не конфликтовало со строкой 1003 ($filter .= ")) "

				//список форм, для поиска по KLCountry_id из NationalityStatus
				$searchFormTypeArray = array('EvnPS',
					'EvnSection',
					'EvnDiag',
					'EvnLeave',
					'EvnStick',
					'KvsPerson',
					'KvsPersonCard',
					'KvsEvnDiag',
					'KvsEvnPS',
					'KvsEvnSection',
					'KvsNarrowBed',
					'KvsEvnUsluga',
					'KvsEvnUslugaOB',
					'KvsEvnUslugaAn',
					'KvsEvnUslugaOsl',
					'KvsEvnDrug',
					'KvsEvnLeave',
					'KvsEvnStick');
				if(isset($data['SearchFormType']) && in_array($data['SearchFormType'], $searchFormTypeArray)) {
					$mainAlias = 'ns';
				}

				if( $data['SearchFormType'] == 'EvnPL' && $data['PersonPeriodicType_id'] == 2 ){
					$mainAlias = 'ns';
				}

				$filter .= " and ((".$mainAlias.".KLCountry_id !=643";
			}

			if (empty($data['Person_citizen']) || ($data['Person_citizen']!=3)) {
				$filter .= " and (exists (select Address_id from Address AR  with (nolock) ";
				$filter .= " where (AR.Address_id = " . $alias . ".UAddress_id or AR.Address_id = " . $alias . ".PAddress_id)";
			}

			// Поиск по гражданству
			if ($data['PDKLCountry_id'] > 0 && !($data['KLRgn_id'] > 0 || $data['KLSubRgn_id'] > 0 || $data['KLCity_id'] > 0 || $data['KLTown_id'] > 0 || $data['KLStreet_id'] > 0)) {
				if (!empty($data['Person_citizen']) &&($data['Person_citizen']==3)) {
					$filter .= " and ".$mainAlias.".KLCountry_id = :PDKLCountry_id";
					$queryParams['PDKLCountry_id'] = $data['PDKLCountry_id'];
				}

				if (!empty($data['Person_citizen']) &&($data['Person_citizen']!=3)) {
					$filter .= " and AR.KLCountry_id = :PDKLCountry_id";
					$queryParams['PDKLCountry_id'] = $data['PDKLCountry_id'];
				}
			}

			$filter .= ")) ";

		}

		if ($data['Person_NoPolis']) {
			$filter .= " and " . $alias . ".Polis_id is null";
		} else {
			if ((strlen($data['Polis_Num']) > 0) || (strlen($data['Polis_Ser']) > 0) || ($data['PolisType_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['OMSSprTerr_id'] > 0) || $data['Person_NoOrgSMO']) {
				$filter .= " and exists (select Polis_id from Polis with (nolock) left join OmsSprTerr with (nolock) on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id";
				$filter .= " WHERE Polis.Polis_id = " . $alias . ".Polis_id";

				// Пациент
				if ($data['OMSSprTerr_id'] > 0) {
					if ($data['OMSSprTerr_id'] == 100500) {
						if (isset($data['session']['region']) && isset($data['session']['region']['number']) && $data['session']['region']['number'] > 0) {
							$filter .= " and OmsSprTerr.KLRgn_id <> " . $data['session']['region']['number'];
						}
					} else {
						$filter .= " and Polis.OmsSprTerr_id = :OMSSprTerr_id";
					}

					$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
				}

				// Пациент
				if ($data['Person_NoOrgSMO']) {
					$filter .= " and Polis.OrgSmo_id IS NULL";
				} elseif ($data['OrgSmo_id'] > 0) {
					$filter .= " and Polis.OrgSmo_id = :OrgSmo_id";
					$queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
				}

				// Пациент
				if (strlen($data['Polis_Num']) > 0) {
					$filter .= " and Polis.Polis_Num = :Polis_Num";
					$queryParams['Polis_Num'] = $data['Polis_Num'];
				}

				// Пациент
				if (strlen($data['Polis_Ser']) > 0) {
					$filter .= " and Polis.Polis_Ser = :Polis_Ser";
					$queryParams['Polis_Ser'] = $data['Polis_Ser'];
				}

				// Пациент
				if ($data['PolisType_id'] > 0) {
					$filter .= " and Polis.PolisType_id = :PolisType_id";
					$queryParams['PolisType_id'] = $data['PolisType_id'];
				}

				$filter .= ") ";
			}
		}
	}

	/**
	 * 	Формирование и выполнение поискового запроса
	 */
	function searchData($data, $getCount = false, $print = false, $dbf = false) {
		$filter = "(1 = 1)";
		$pac_filter = "";
		$main_alias = "";
		$queryParams = array();
		$variablesArray = array(
			"declare @getDT datetime = dbo.tzGetDate();"

		);
		$orderby = "";
		//   echo '<pre>' .         print_r($data['SearchFormType'], 1) . '</pre>';
		$archiveTable = null;
		$archiveTables = array('EvnPS', 'EvnSection', 'EvnRecept', 'EvnPL', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnPLDispDop13', 'EvnPLDispDop13Sec', 'EvnPLDispProf', 'EvnPLDispScreen', 'EvnPLDispScreenChild', 'EvnPLDispOrp', 'EvnPLDispTeenInspectionPeriod', 'EvnPLDispTeenInspectionProf', 'EvnPLDispTeenInspectionPred', 'EvnUslugaPar', 'CmpCallCard', 'CmpCloseCard', 'EvnPLDispTeen14');
		if (in_array($data['SearchFormType'], $archiveTables)) {
			$archiveTable = $data['SearchFormType'];
			switch ($data['SearchFormType']) {
				case 'EvnPLDispTeenInspectionPeriod':
				case 'EvnPLDispTeenInspectionProf':
				case 'EvnPLDispTeenInspectionPred':
					$archiveTable = 'EvnPLDispTeenInspection';
					break;

				case 'EvnPLDispDop13Sec':
					$archiveTable = 'EvnPLDispDop13';
					break;

				case 'EvnPL':
					$archiveTable = 'Evn';
					break;
			}
		}

		$archive_database_enable = $this->config->item('archive_database_enable');



		$query = "
			select
				-- select
		";

		$queryWithArray = array();

		if (($data['SearchFormType'] == 'EvnDiag' || substr($data['SearchFormType'], 0, 3) == 'Kvs' ||
				$data['SearchFormType'] == 'EPLPerson' ||
				$data['SearchFormType'] == 'EvnPL' ||
				$data['SearchFormType'] == 'EvnVizitPL' ||
				$data['SearchFormType'] == 'EvnUsluga' ||
				$data['SearchFormType'] == 'EvnAgg' ||
				$data['SearchFormType'] == 'EPLStomPerson' ||
				$data['SearchFormType'] == 'EvnPLStom' ||
				$data['SearchFormType'] == 'EvnVizitPLStom`' ||
				$data['SearchFormType'] == 'EvnUslugaStom' ||
				$data['SearchFormType'] == 'EvnAggStom'
				) && $dbf === true)
			$query .= "
				distinct
			";

		$isFarmacy = (isset($data['session']['OrgFarmacy_id']));
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$PS_prefix = 'PS'; //таблица для выборки данных по застрахованным
		if (isset($data['kvs_date_type']))
			if ((in_array($data['PersonPeriodicType_id'], array(2, 3)) && $data['kvs_date_type'] == 1) || ($data['PersonPeriodicType_id'] == 1 && $data['kvs_date_type'] == 2)) //требуется дополнительная таблица для выгрузки данных по застрахованному
				$PS_prefix = 'PS2';

		if (isset($data['and_kvsperson']) && $data['and_kvsperson']) {
			if ($dbf === true) {
				$query .= "
					{$PS_prefix}.PersonEvn_id as PCT_ID,
					{$PS_prefix}.Person_id as P_ID,
					{$PS_prefix}.Person_SurName as SURNAME,
					{$PS_prefix}.Person_FirName as FIRNAME,
					{$PS_prefix}.Person_SecName as SECNAME,
					rtrim(isnull(convert(varchar(10), {$PS_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
					{$PS_prefix}.Person_Snils as SNILS,
					PrsIsInv.YesNo_Code as INV_N,
					PrsInvD.Diag_Code as INV_DZ,
					rtrim(isnull(convert(varchar(10), PrsPCh.PersonChild_invDate, 104),'')) as INV_DATA,
					PrsSex.Sex_Name as SEX,
					PrsSoc.SocStatus_Name as SOC,
					PrsOMSST.OMSSprTerr_Code as P_TERK,
					PrsOMSST.OMSSprTerr_Name as P_TER,
					PrsPolTp.PolisType_Name as P_NAME,
					PrsPol.Polis_Ser as P_SER,
					PrsPol.Polis_Num as P_NUM,
					{$PS_prefix}.Person_EdNum as P_NUMED,
					rtrim(isnull(convert(varchar(10), PrsPol.Polis_begDate, 104),'')) as P_DATA,
					PrsOSO.Org_Code as SMOK,
					PrsOS.OrgSmo_Name as SMO,
					0 as AR_TP,
					PrsUA.Address_Zip as AR_IDX,
					PrsUA.KLCountry_Name as AR_LND,
					PrsUA.KLRGN_Name as AR_RGN,
					PrsUA.KLSubRGN_Name as AR_RN,
					PrsUA.KLCity_Name as AR_CTY,
					PrsUA.KLTown_Name as AR_NP,
					PrsUA.KLStreet_Name as AR_STR,
					PrsUA.Address_House as AR_DOM,
					PrsUA.Address_Corpus as AR_K,
					PrsUA.Address_Flat as AR_KV,
					0 as AP_TP,
					PrsPA.Address_Zip as AP_IDX,
					PrsPA.KLCountry_Name as AP_LND,
					PrsPA.KLRGN_Name as AP_RGN,
					PrsPA.KLSubRGN_Name as AP_RN,
					PrsPA.KLCity_Name as AP_CTY,
					PrsPA.KLTown_Name as AP_NP,
					PrsPA.KLStreet_Name as AP_STR,
					PrsPA.Address_House as AP_DOM,
					PrsPA.Address_Corpus as AP_K,
					PrsPA.Address_Flat as AP_KV,
					PrsDocTp.DocumentType_Name as D_TIP,
					PrsDoc.Document_Ser as D_SER,
					PrsDoc.Document_Num as D_NOM,
					PrsOrgD.Org_id as D_OUT,
					rtrim(isnull(convert(varchar(10), PrsDoc.Document_begDate, 104),'')) as D_DATA,
				";
			}
		}

		$PL_prefix = 'PS'; //таблица для выборки данных по застрахованным
		if (isset($data['epl_date_type']))
			if ((in_array($data['PersonPeriodicType_id'], array(2, 3)) && $data['epl_date_type'] == 1) || ($data['PersonPeriodicType_id'] == 1 && $data['epl_date_type'] == 2)) //требуется дополнительная таблица для выгрузки данных по застрахованному
				$PL_prefix = 'PS2';

		if (isset($data['and_eplperson']) && $data['and_eplperson']) {
			if ($dbf === true) {
				$query .= "
					{$PL_prefix}.PersonEvn_id as PCT_ID,
					{$PL_prefix}.Person_id as P_ID,
					{$PL_prefix}.Person_SurName as SURNAME,
					{$PL_prefix}.Person_FirName as FIRNAME,
					{$PL_prefix}.Person_SecName as SECNAME,
					rtrim(isnull(convert(varchar(10), {$PL_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
					{$PL_prefix}.Person_Snils as SNILS,
					PrsIsInv.YesNo_Code as INV_N,
					PrsInvD.Diag_Code as INV_DZ,
					rtrim(isnull(convert(varchar(10), PrsPCh.PersonChild_invDate, 104),'')) as INV_DATA,
					PrsSex.Sex_Name as SEX,
					PrsSoc.SocStatus_Name as SOC,
					PrsOMSST.OMSSprTerr_Code as P_TERK,
					PrsOMSST.OMSSprTerr_Name as P_TER,
					PrsPolTp.PolisType_Name as P_NAME,
					PrsPol.Polis_Ser as P_SER,
					PrsPol.Polis_Num as P_NUM,
					{$PL_prefix}.Person_EdNum as P_NUMED,
					rtrim(isnull(convert(varchar(10), PrsPol.Polis_begDate, 104),'')) as P_DATA,
					PrsOSO.Org_Code as SMOK,
					PrsOS.OrgSmo_Name as SMO,
					0 as AR_TP,
					PrsUA.Address_Zip as AR_IDX,
					PrsUA.KLCountry_Name as AR_LND,
					PrsUA.KLRGN_Name as AR_RGN,
					PrsUA.KLSubRGN_Name as AR_RN,
					PrsUA.KLCity_Name as AR_CTY,
					PrsUA.KLTown_Name as AR_NP,
					PrsUA.KLStreet_Name as AR_STR,
					PrsUA.Address_House as AR_DOM,
					PrsUA.Address_Corpus as AR_K,
					PrsUA.Address_Flat as AR_KV,
					0 as AP_TP,
					PrsPA.Address_Zip as AP_IDX,
					PrsPA.KLCountry_Name as AP_LND,
					PrsPA.KLRGN_Name as AP_RGN,
					PrsPA.KLSubRGN_Name as AP_RN,
					PrsPA.KLCity_Name as AP_CTY,
					PrsPA.KLTown_Name as AP_NP,
					PrsPA.KLStreet_Name as AP_STR,
					PrsPA.Address_House as AP_DOM,
					PrsPA.Address_Corpus as AP_K,
					PrsPA.Address_Flat as AP_KV,
					PrsDocTp.DocumentType_Name as D_TIP,
					PrsDoc.Document_Ser as D_SER,
					PrsDoc.Document_Num as D_NOM,
					PrsOrgD.Org_id as D_OUT,
					rtrim(isnull(convert(varchar(10), PrsDoc.Document_begDate, 104),'')) as D_DATA,
				";
			}
		}

		$PLS_prefix = 'PS'; //таблица для выборки данных по застрахованным
		if (isset($data['eplstom_date_type']))
			if ((in_array($data['PersonPeriodicType_id'], array(2, 3)) && $data['eplstom_date_type'] == 1) || ($data['PersonPeriodicType_id'] == 1 && $data['eplstom_date_type'] == 2)) //требуется дополнительная таблица для выгрузки данных по застрахованному
				$PLS_prefix = 'PS2';

		if (isset($data['and_eplstomperson']) && $data['and_eplstomperson']) {
			if ($dbf === true) {
				$query .= "
					{$PLS_prefix}.PersonEvn_id as PCT_ID,
					{$PLS_prefix}.Person_id as P_ID,
					{$PLS_prefix}.Person_SurName as SURNAME,
					{$PLS_prefix}.Person_FirName as FIRNAME,
					{$PLS_prefix}.Person_SecName as SECNAME,
					rtrim(isnull(convert(varchar(10), {$PLS_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
					{$PLS_prefix}.Person_Snils as SNILS,
					PrsIsInv.YesNo_Code as INV_N,
					PrsInvD.Diag_Code as INV_DZ,
					rtrim(isnull(convert(varchar(10), PrsPCh.PersonChild_invDate, 104),'')) as INV_DATA,
					PrsSex.Sex_Name as SEX,
					PrsSoc.SocStatus_Name as SOC,
					PrsOMSST.OMSSprTerr_Code as P_TERK,
					PrsOMSST.OMSSprTerr_Name as P_TER,
					PrsPolTp.PolisType_Name as P_NAME,
					PrsPol.Polis_Ser as P_SER,
					PrsPol.Polis_Num as P_NUM,
					{$PLS_prefix}.Person_EdNum as P_NUMED,
					rtrim(isnull(convert(varchar(10), PrsPol.Polis_begDate, 104),'')) as P_DATA,
					PrsOSO.Org_Code as SMOK,
					PrsOS.OrgSmo_Name as SMO,
					0 as AR_TP,
					PrsUA.Address_Zip as AR_IDX,
					PrsUA.KLCountry_Name as AR_LND,
					PrsUA.KLRGN_Name as AR_RGN,
					PrsUA.KLSubRGN_Name as AR_RN,
					PrsUA.KLCity_Name as AR_CTY,
					PrsUA.KLTown_Name as AR_NP,
					PrsUA.KLStreet_Name as AR_STR,
					PrsUA.Address_House as AR_DOM,
					PrsUA.Address_Corpus as AR_K,
					PrsUA.Address_Flat as AR_KV,
					0 as AP_TP,
					PrsPA.Address_Zip as AP_IDX,
					PrsPA.KLCountry_Name as AP_LND,
					PrsPA.KLRGN_Name as AP_RGN,
					PrsPA.KLSubRGN_Name as AP_RN,
					PrsPA.KLCity_Name as AP_CTY,
					PrsPA.KLTown_Name as AP_NP,
					PrsPA.KLStreet_Name as AP_STR,
					PrsPA.Address_House as AP_DOM,
					PrsPA.Address_Corpus as AP_K,
					PrsPA.Address_Flat as AP_KV,
					PrsDocTp.DocumentType_Name as D_TIP,
					PrsDoc.Document_Ser as D_SER,
					PrsDoc.Document_Num as D_NOM,
					PrsOrgD.Org_id as D_OUT,
					rtrim(isnull(convert(varchar(10), PrsDoc.Document_begDate, 104),'')) as D_DATA,
				";
			}
		}

		$isPerm = $data['session']['region']['nick'] == 'perm';
		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @getDT, 112) as datetime) THEN 'orange'
					ELSE CASE
						WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN ps.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as [Person_IsBDZ],";
		if ($isPerm) {
			$isBDZ = "case
				when ps.Server_pid = 0 then
	case when ps.Person_IsInErz = 1  then 'blue'
	else case when pls.Polis_endDate is not null and pls.Polis_endDate <= CAST( @getDT AS DATE) THEN
		case when ps.Person_deadDT is not null then 'red' else 'yellow' end
	else 'true' end end
	else 'false' end as [Person_IsBDZ],";
		}
		switch ($data['SearchFormType']) {
			case 'CmpCallCard':
				$main_alias = "CCC";
				$query .= "
					'' as accessType,
					convert(varchar(30), CCC.CmpCallCard_id) + convert(varchar(30),CLC.CmpCloseCard_id) as CmpCallCard_uid,
					CCC.CmpCallCard_id,
					ISNULL(CCC.MedPersonal_id, CLC.MedPersonal_id) as MedPersonal_id,
					PS.Person_id,
					PS.PersonEvn_id,
					PS.Server_id,
					ET.EmergencyTeamSpec_id,
					CLC.EmergencyTeamNum,
					LB.LpuBuilding_Name,
					convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
					convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
					convert(varchar(5), CCC.CmpCallCard_Przd, 108) as CmpCallCard_Przd,
					convert(varchar(16),CCC.CmpCallCard_Numv) + ' ' + ISNULL(CCC.CmpCallCard_NumvPr,'') as CmpCallCard_Numv,
					CCC.CmpCallCardInputType_id,
					case when ISNULL(CLC.CmpCloseCard_id,0)=0 then 0 else CLC.CmpCloseCard_id end as CmpCloseCard_id,
					RTRIM(ISNULL(PS.Person_Surname, CCC.Person_SurName)) as Person_Surname,
					RTRIM(ISNULL(PS.Person_Firname, CCC.Person_FirName)) as Person_Firname,
					RTRIM(ISNULL(PS.Person_Secname, CCC.Person_SecName)) as Person_Secname,
					convert(varchar(20), cast(ISNULL(CCC.Person_BirthDay, PS.Person_Birthday) as datetime), 104) as Person_Birthday,
					--convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
					ISNULL(dbo.Age2(PS.Person_BirthDay, @getDT), CCC.Person_Age) as Person_Age,
					case when PS.Person_id is not null and ISNULL(PS.Person_IsUnknown, 1) != 2 then 'true' else 'false' end as Person_IsIdentified,
					-- RTRIM(ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name,
					COALESCE(CR.CmpReason_Code + '. ', '') + CR.CmpReason_Name as CmpReason_Name,
					COALESCE(CSecondR.CmpReason_Code + '. ', '') + CSecondR.CmpReason_Name as CmpSecondReason_Name,
					--RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name,
					case when (CCC.CmpCallCardInputType_id in (1,2)) then COALESCE(Lpu.Lpu_Nick, Lpu.Lpu_Name, '')
                    	else COALESCE(LpuHid.Lpu_Nick, LpuHid.Lpu_Name, '') end
                    as CmpLpu_Name,
					RTRIM(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.CmpDiag_Code end) as CmpDiag_Name,
					RTRIM(ISNULL(D.Diag_Name, '')) as StacDiag_Name,
					" . $isBDZ . "
					COALESCE(PAddr.Address_Address, RTRIM(replace(CCC.CmpCallCard_PCity, '=', '')) + ', ' + RTRIM(CCC.CmpCallCard_PUlic) + ', ' + RTRIM(CCC.CmpCallCard_PDom) + ', ' + RTRIM(CCC.CmpCallCard_PKvar), '') as Person_Address,
					convert(varchar(10), ccp.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDT,
					case when ccp.CmpCallCardCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ccp.CmpCallCardCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as CmpCallCardCostPrint_IsNoPrintText,
					CCCInput.CmpCallCardInputType_Name,
					acceptNmpFlag.CmpCallCardEvent_id
				";
				break;

			case 'CmpCloseCard':
				$main_alias = "CCC";

				if (in_array($data['session']['region']['nick'], array('kz'))) {
					$reasonCase = "(COALESCE(CRTalon.CmpReason_Code + '. ', '') + CRTalon.CmpReason_Name)";
				} else {
					$reasonCase = "(case when CLC.CallPovod_id is not null then COALESCE(CR.CmpReason_Code + '. ', '') + CR.CmpReason_Name else COALESCE(CRTalon.CmpReason_Code + '. ', '') + CRTalon.CmpReason_Name end)";
				}

				$query .= "
					'' as accessType,
					CCC.CmpCallCard_id,
					convert(varchar(30), CCC.CmpCallCard_id) + convert(varchar(30),CLC.CmpCloseCard_id) as CmpCallCard_uid,
					ETLB.LpuBuilding_id as ETLBLpuBuilding_id,
					ISNULL(CCC.MedPersonal_id, CLC.MedPersonal_id) as MedPersonal_id,
					PS.Person_id,
					PS.PersonEvn_id,
					PS.Server_id,
					ET.EmergencyTeamSpec_id,
					CLC.EmergencyTeamNum,
					CCC.CmpCallCardInputType_id,
					convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
					convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
					convert(varchar(5), CCC.CmpCallCard_Przd, 108) as CmpCallCard_Przd,
					convert(varchar(16),COALESCE(CLC.Day_num,CCC.CmpCallCard_Numv)) + ' ' + ISNULL(CLC.CmpCloseCard_DayNumPr,'') as CmpCallCard_Numv,
					LB.LpuBuilding_Name,
					CLC.CmpCloseCard_id,
					RTRIM(COALESCE(CLC.Fam, PS.Person_Surname, CCC.Person_SurName)) as Person_Surname,
					RTRIM(COALESCE(CLC.Name, PS.Person_Firname, CCC.Person_FirName)) as Person_Firname,
					RTRIM(COALESCE(CLC.Middle, PS.Person_Secname, CCC.Person_SecName)) as Person_Secname,

					convert(varchar(10), COALESCE(PSCLC.Person_BirthDay,CCC.Person_BirthDay,PS.Person_BirthDay), 104) as Person_Birthday,
					ISNULL(dbo.Age2(PS.Person_BirthDay, @getDT), CLC.Age) as Person_Age,
					--case when COALESCE(CLC.Person_id,CCC.Person_id) is not null then 'true' else 'false' end as Person_IsIdentified,
					case when PS.Person_id is not null and ISNULL(PS.Person_IsUnknown, 1) != 2 then 'true' else 'false' end as Person_IsIdentified,
					$reasonCase as CmpReason_Name,
					--RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name,
					case when (CCC.CmpCallCardInputType_id in (1,2)) then COALESCE(Lpu.Lpu_Nick, Lpu.Lpu_Name, '')
                    	else COALESCE(LpuHid.Lpu_Nick, LpuHid.Lpu_Name, '') end
                    as CmpLpu_Name,
					--(case when CLD.Diag_Code is not null then CLD.Diag_Code+'. ' else '' end)+ISNULL(CLD.Diag_Name, '') as CmpDiag_Name,
					RTRIM(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.CmpDiag_Code end) as CmpDiag_Name,
					RTRIM(ISNULL(D.Diag_Name, '')) as StacDiag_Name,
					COALESCE(
						PAddr.Address_Address,
						RTRIM(replace(CCC.CmpCallCard_PCity, '=', ''))
						+ ', ' + RTRIM(CCC.CmpCallCard_PUlic)
						+ ', ' + RTRIM(CCC.CmpCallCard_PDom)
						+ ', ' + RTRIM(CCC.CmpCallCard_PKvar),
						''
					) as Person_Address,
					" . $isBDZ . "
					convert(varchar(10), ccp.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDT,
					case when ccp.CmpCallCardCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ccp.CmpCallCardCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as CmpCallCardCostPrint_IsNoPrintText,
					CCCInput.CmpCallCardInputType_Name
				";
				break;

			case 'PersonDopDisp':
				$main_alias = "DD";
				$query .= "
					DD.PersonDopDisp_id,
					PS.Person_id,
					IsNull(epldd.Server_id, PS.Server_id) as Server_id, -- проблема при редактировании талона по ДД (#6670) - с регистра передается текущая, с поиска талона - та которая в талоне.
					IsNull(epldd.PersonEvn_id, PS.PersonEvn_id) as PersonEvn_id, -- сделал так, что если есть талон, то бралась периодика с талона
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					okved1.Okved_Name as PersonOrg_Okved,
					org1.Org_OGRN as PersonOrg_OGRN,
					astat1.KLArea_Name as Person_KLAreaStat_Name,
					astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
					rtrim(addr1.Address_Nick) as UAddress_Address,
					isnull(rtrim(otherddlpu.Lpu_Nick), '') as OnDispInOtherLpu,
					max(epldd.EvnPLDispDop_id) as EvnPLDispDop_id,
					CASE WHEN max(epldd.EvnPLDispDop_id) is null THEN 'false' ELSE 'true' END as ExistsDDPL
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'PersonDispOrp':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					UAdd.Address_Nick as ua_name,
					PAdd.Address_Nick as pa_name,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					okved1.Okved_Name as PersonOrg_Okved,
					org1.Org_OGRN as PersonOrg_OGRN,
					astat1.KLArea_Name as Person_KLAreaStat_Name,
					astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
					rtrim(addr1.Address_Nick) as UAddress_Address,
					case when DO.Org_id IS NOT NULL then 'Да' else 'Нет' end as OrgExist,
					isnull(rtrim(ODL.Lpu_Nick), '') as OnDispInOtherLpu,
					EPLDO.EvnPLDispOrp_id,
					CASE WHEN EPLDO.EvnPLDispOrp_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'PersonDispOrpPeriod':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					DO.EducationInstitutionType_id,
					PS.Person_id,
					DO.Org_id,
					PS.Server_id,
					PS.PersonEvn_id,
					UAdd.Address_Nick as ua_name,
					PAdd.Address_Nick as pa_name,
					Sex.Sex_Name,
					LATT.Lpu_Nick as Lpu_Nick,
					EIT.EducationInstitutionType_Name,
					EPLDTI.EvnPLDispTeenInspection_id,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDirection,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'PersonDispOrpPred':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					DO.EducationInstitutionType_id,
					DO.Org_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					Sex.Sex_Name,
					LATT.Lpu_Nick as Lpu_Nick,
					EIT.EducationInstitutionType_Name,
					EPLDTI.EvnPLDispTeenInspection_id,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'PersonDispOrpProf':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					DO.AgeGroupDisp_id,
					DO.Org_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					Sex.Sex_Name,
					AGD.AgeGroupDisp_Name,
					convert(varchar,cast(DO.PersonDispOrp_begDate as datetime),104) as PersonDispOrp_begDate,
					EPLDTI.EvnPLDispTeenInspection_id,
					CASE WHEN DO.Org_id is null THEN 'false' ELSE 'true' END as OrgExist,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'PersonDispOrpOld':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					okved1.Okved_Name as PersonOrg_Okved,
					org1.Org_OGRN as PersonOrg_OGRN,
					astat1.KLArea_Name as Person_KLAreaStat_Name,
					astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
					rtrim(addr1.Address_Nick) as UAddress_Address,
					isnull(rtrim(ODL.Lpu_Nick), '') as OnDispInOtherLpu,
					EPLDO.EvnPLDispOrp_id,
					CASE WHEN EPLDO.EvnPLDispOrp_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispDop13':
				$main_alias = "EvnPLDispDop13";

				$query .= "
					cast(ISNULL([EPLDD13].[EvnPLDispDop13_id], 0) as varchar) + '_' + cast(isnull([EPLDD13].[Person_id], 0) as varchar) as [id],
					[EPLDD13].[EvnPLDispDop13_id] as [EvnPLDispDop13_id],
					ISNULL(EPLDD13.EvnPLDispDop13_IsTransit, 1) as EvnPLDispDop13_IsTransit,
					[EPLDD13].[Person_id] as [Person_id],
					[EPLDD13].[Server_id] as [Server_id],
					[EPLDD13].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([EPLDD13].[Person_Surname]) as [Person_Surname],
					RTRIM([EPLDD13].[Person_Firname]) as [Person_Firname],
					RTRIM([EPLDD13].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [EPLDD13].[Person_Birthday], 104) as [Person_Birthday],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop13_IsEndStage],
					[IsMobile].[YesNo_Name] as [EvnPLDispDop13_IsMobile],
					[IsFinishSecond].[YesNo_Name] as [EvnPLDispDop13Second_IsEndStage],
					convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) as [EvnPLDispDop13_setDate],
					convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) as [EvnPLDispDop13_disDate],
					HK.HealthKind_Name as [EvnPLDispDop13_HealthKind_Name],
					case when [EPLDD13].EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) else null end as EvnPLDispDop13Second_napDate,
					DopDispSecond.HealthKind_Name as EvnPLDispDop13Second_HealthKind_Name,
					case when EPLDD13.EvnPLDispDop13_IsRefusal = 2 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) else null end as EvnPLDispDop13_rejDate,
					case
						when EPLDD13AL.EvnPLDispDop13_id is not null then 4
						when EPLDD13.EvnPLDispDop13_id is null then 0
						when EPLDD13.Lpu_id = :Lpu_id then 0
						when EPLDD13.Lpu_id " . getLpuIdFilter($data) . " and ISNULL(EPLDD13.EvnPLDispDop13_IsTransit, 1) = 2 then 0
						else 4
					end as AccessType_Code,
					DopDispSecond.EvnPLDispDop13_id as EvnPLDispDop13Second_id,
					convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13Second_setDate,
					convert(varchar(10), DopDispSecond.EvnPLDispDop13_disDate, 104) as EvnPLDispDop13Second_disDate,
					case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) else null end as EvnPLDispDop13Second_rejDate,
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
				";
				if (in_array($data['session']['region']['nick'], array('ufa'))) {
					$query .= "
						,lpu.Lpu_Nick
					";
				}
				if (in_array($data['session']['region']['nick'], array('buryatiya'))) {
					$query .= "
						,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
					";
				}

				$query .= "
					,ISNULL(UAdd.Address_Nick, UAdd.Address_Address) as ua_name
					,ISNULL(PAdd.Address_Nick, PAdd.Address_Address) as pa_name
				";
				break;

			case 'EvnPLDispDop13Sec':
				$main_alias = "EPLDD13";

				$query .= "
					cast(ISNULL([EPLDD13].[EvnPLDispDop13_id], 0) as varchar) + '_' + cast(isnull([PS].[Person_id], 0) as varchar) as [id],
					[EPLDD13].[EvnPLDispDop13_id] as [EvnPLDispDop13_id],
					[PS].[Person_id] as [Person_id],
					[PS].[Server_id] as [Server_id],
					[EPLDD13].[PersonEvn_id] as [PersonEvn_id],
					[EPLDD13].[PayType_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop13_IsEndStage],
					[IsMobile].[YesNo_Name] as [EvnPLDispDop13_IsMobile],
					[IsFinishSecond].[YesNo_Name] as [EvnPLDispDop13Second_IsEndStage],
					convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) as [EvnPLDispDop13_setDate],
					convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) as [EvnPLDispDop13_disDate],
					HK.HealthKind_Name as [EvnPLDispDop13_HealthKind_Name],
					case when [EPLDD13].EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) else null end as EvnPLDispDop13Second_napDate,
					HK_SEC.HealthKind_Name as EvnPLDispDop13Second_HealthKind_Name,
					case when DDICData.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) else null end as EvnPLDispDop13_rejDate,
					case
						when EPLDD13AL.EvnPLDispDop13_id is not null then 4
						when DopDispSecond.EvnPLDispDop13_id is null then 0
						when EPLDD13.Lpu_id = :Lpu_id then 0
						when DopDispSecond.Lpu_id = :Lpu_id then 0
						when DopDispSecond.Lpu_id " . getLpuIdFilter($data) . " and ISNULL(DopDispSecond.EvnPLDispDop13_IsTransit, 1) = 2 then 0
						else 4
					end as AccessType_Code,
					DopDispSecond.EvnPLDispDop13_id as EvnPLDispDop13Second_id,
					convert(varchar(10), DOCOSMDT.EvnUslugaDispDop_disDate, 104) as VopOsm_EvnUslugaDispDop_disDate, -- дата осмотра врача-терапевта на первом этапе
					ISNULL(DopDispSecond.EvnPLDispDop13_IsTransit, 1) as EvnPLDispDop13Second_IsTransit,
					convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13Second_setDate,
					convert(varchar(10), DopDispSecond.EvnPLDispDop13_disDate, 104) as EvnPLDispDop13Second_disDate,
					case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) else null end as EvnPLDispDop13Second_rejDate,
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
				";
				if (in_array($data['session']['region']['nick'], array('ufa'))) {
					$query .= "
						,lpu.Lpu_Nick
					";
				}
				if (in_array($data['session']['region']['nick'], array('buryatiya'))) {
					$query .= "
						,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
					";
				}

				$query .= "
					,ISNULL(UAdd.Address_Nick, UAdd.Address_Address) as ua_name
					,ISNULL(PAdd.Address_Nick, PAdd.Address_Address) as pa_name
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispProf':
				$main_alias = "EPLDP";
				$query .= "
					[EPLDP].[EvnPLDispProf_id] as [EvnPLDispProf_id],
					ISNULL(EPLDP.EvnPLDispProf_IsTransit, 1) as EvnPLDispProf_IsTransit,
					[PS].[Person_id] as [Person_id],
					[PS].[Server_id] as [Server_id],
					[EPLDP].[PersonEvn_id] as [PersonEvn_id],
					ISNULL(UAdd.Address_Nick, UAdd.Address_Address) as ua_name,
					ISNULL(PAdd.Address_Nick, PAdd.Address_Address) as pa_name,
					[IsFinish].[YesNo_Name] as [EvnPLDispProf_IsEndStage],
					convert(varchar(10), [EPLDP].[EvnPLDispProf_consDT], 104) as [EvnPLDispProf_setDate],
					convert(varchar(10), [EPLDP].[EvnPLDispProf_disDate], 104) as [EvnPLDispProf_disDate],
					HK.HealthKind_Name as [EvnPLDispProf_HealthKind_Name],
					case when EPLDP.EvnPLDispProf_IsRefusal = 2 then convert(varchar(10), [EPLDP].[EvnPLDispProf_consDT], 104) else null end as EvnPLDispProf_rejDate,
					case
						when exists (select top 1 EvnPLDispProf_id from v_EvnPLDispProf (nolock) where Person_id = PS.Person_id and YEAR(EvnPLDispProf_setDate) = :PersonDopDisp_Year and Lpu_id " . getLpuIdFilter($data, true) . ") then 4
						when EPLDP.EvnPLDispProf_id is null then 0
						when EPLDP.Lpu_id = :Lpu_id then 0
						when EPLDP.Lpu_id " . getLpuIdFilter($data) . " and ISNULL(EPLDP.EvnPLDispProf_IsTransit, 1) = 2 then 0
						else 4
					end as AccessType_Code,
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
				";
				/* if (in_array($data['session']['region']['nick'], array('buryatiya'))) {
				  $query .= "
				  ,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
				  ";
				  } */
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispScreen':
				$main_alias = "EPLDS";
				$query .= "
					[EPLDS].[EvnPLDispScreen_id] as [EvnPLDispScreen_id],
					[PS].[Person_id] as [Person_id],
					[PS].[Server_id] as [Server_id],
					[EPLDS].[PersonEvn_id] as [PersonEvn_id],
					Sex.Sex_Name,
					AGD.AgeGroupDisp_Name,
					convert(varchar(10), [EPLDS].[EvnPLDispScreen_setDate], 104) as [EvnPLDispScreen_setDate],
					convert(varchar(10), [EPLDS].[EvnPLDispScreen_disDate], 104) as [EvnPLDispScreen_disDate],
					[IsFinish].[YesNo_Name] as [EvnPLDispScreen_IsEndStage]
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispScreenChild':
				$main_alias = "EPLDS";
				$query .= "
					[EPLDS].[EvnPLDispScreenChild_id] as [EvnPLDispScreenChild_id],
					[PS].[Person_id] as [Person_id],
					[PS].[Server_id] as [Server_id],
					[EPLDS].[PersonEvn_id] as [PersonEvn_id],
					Sex.Sex_Name,
					AGD.AgeGroupDisp_Name,
					convert(varchar(10), [EPLDS].[EvnPLDispScreenChild_setDate], 104) as [EvnPLDispScreenChild_setDate],
					convert(varchar(10), [EPLDS].[EvnPLDispScreenChild_disDate], 104) as [EvnPLDispScreenChild_disDate],
					[IsFinish].[YesNo_Name] as [EvnPLDispScreenChild_IsEndStage]
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispDop':
				$main_alias = "EPLDD";
				$query .= "
					[EPLDD].[EvnPLDispDop_id] as [EvnPLDispDop_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					[EPLDD].[EvnPLDispDop_VizitCount] as [EvnPLDispDop_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_setDate], 104) as [EvnPLDispDop_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_disDate], 104) as [EvnPLDispDop_disDate]
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispTeen14':
				$main_alias = "EPLDT14";
				$query .= "
					[EPLDT14].[EvnPLDispTeen14_id] as [EvnPLDispTeen14_id],
					[EPLDT14].[Person_id] as [Person_id],
					[EPLDT14].[Server_id] as [Server_id],
					[EPLDT14].[PersonEvn_id] as [PersonEvn_id],
					[EPLDT14].[EvnPLDispTeen14_VizitCount] as [EvnPLDispTeen14_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispTeen14_IsFinish],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_setDate], 104) as [EvnPLDispTeen14_setDate],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_disDate], 104) as [EvnPLDispTeen14_disDate]
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispOrp':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					ISNULL(EPLDO.EvnPLDispOrp_IsTransit, 1) as EvnPLDispOrp_IsTransit,
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					Sex.Sex_Name,
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[EPLDO].[DispClass_id] as [DispClass_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispOrp_IsTwoStage],
					HK.HealthKind_Name as [EvnPLDispOrp_HealthKind_Name],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate],
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					$query .= "
						,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
					";
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispOrpOld':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispOrpSec':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					Sex.Sex_Name,
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[EPLDO].[DispClass_id] as [DispClass_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					HK.HealthKind_Name as [EvnPLDispOrp_HealthKind_Name],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispOrp_IsTwoStage],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate],
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					$query .= "
						,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
					";
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
				$main_alias = "EPLDTI";
				$query .= "
					[EPLDTI].[EvnPLDispTeenInspection_id] as [EvnPLDispTeenInspection_id],
					[EPLDTI].[EvnPLDispTeenInspection_fid] as [EvnPLDispTeenInspection_fid],
					HGT.HealthGroupType_Name,
					ISNULL(EPLDTI.EvnPLDispTeenInspection_IsTransit, 1) as EvnPLDispTeenInspection_IsTransit,
					[EPLDTI].[Person_id] as [Person_id],
					[EPLDTI].[Server_id] as [Server_id],
					[EPLDTI].[PersonEvn_id] as [PersonEvn_id],
					ISNULL(UAdd.Address_Nick, UAdd.Address_Address) as ua_name,
					ISNULL(PAdd.Address_Nick, PAdd.Address_Address) as pa_name,
					Sex.Sex_Name,
					AGD.AgeGroupDisp_Name,
					case when ISNULL(EPLDTI.Org_id,PDORP.Org_id) IS NOT NULL then 'true' else 'false' end as OrgExist,
					[EPLDTI].[EvnPLDispTeenInspection_VizitCount] as [EvnPLDispTeenInspection_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispTeenInspection_IsFinish],
					HK.HealthKind_Name as [EvnPLDispTeenInspection_HealthKind_Name],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispTeenInspection_IsTwoStage],
					case when PDORP.PersonDispOrp_id is not null then 'true' else 'false' end as EvnPLDispTeenInspection_hasDirection,
					convert(varchar(10), [EPLDTI].[EvnPLDispTeenInspection_setDate], 104) as [EvnPLDispTeenInspection_setDate],
					convert(varchar(10), [EPLDTI].[EvnPLDispTeenInspection_disDate], 104) as [EvnPLDispTeenInspection_disDate],
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					$query .= "
						,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
					";
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispDopStream':
				$main_alias = "EPLDD";
				$query .= "
					[EPLDD].[EvnPLDispDop_id] as [EvnPLDispDop_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDD].[EvnPLDispDop_VizitCount] as [EvnPLDispDop_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_setDate], 104) as [EvnPLDispDop_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_disDate], 104) as [EvnPLDispDop_disDate]
				";
				break;

			case 'EvnPLDispTeen14Stream':
				$main_alias = "EPLDT14";
				$query .= "
					[EPLDT14].[EvnPLDispTeen14_id] as [EvnPLDispTeen14_id],
					[EPLDT14].[Person_id] as [Person_id],
					[EPLDT14].[Server_id] as [Server_id],
					[EPLDT14].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDT14].[EvnPLDispTeen14_VizitCount] as [EvnPLDispTeen14_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispTeen14_IsFinish],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_setDate], 104) as [EvnPLDispTeen14_setDate],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_disDate], 104) as [EvnPLDispTeen14_disDate]
				";
				break;

			case 'EvnPLDispOrpStream':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[EPLDO].[DispClass_id] as [DispClass_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispOrp_IsTwoStage],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
				";
				break;

			case 'EvnPLDispMigrant':
				$main_alias = "EPLDM";
				$query .= "
					[EPLDM].[EvnPLDispMigrant_id] as [EvnPLDispMigrant_id],
					[EPLDM].[Person_id] as [Person_id],
					[EPLDM].[Server_id] as [Server_id],
					[EPLDM].[PersonEvn_id] as [PersonEvn_id],
					convert(varchar(10), [EPLDM].[EvnPLDispMigrant_setDate], 104) as [EvnPLDispMigrant_setDate],
					case when [EPLDM].ResultDispMigrant_id is not null then convert(varchar(10), [EVDD].EvnVizitDispDop_setDate, 104) else null end as [EvnPLDispMigrant_disDate],
					[UA].Address_Nick as PersonUAddress,
					[PA].Address_Nick as PersonPAddress,
					[RDM].ResultDispMigrant_Name
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnPLDispDriver':
				$main_alias = "EPLDD";
				$query .= "
					[EPLDD].[EvnPLDispDriver_id] as [EvnPLDispDriver_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					[EPLDD].[EvnPLDispDriver_IsSigned] as [EvnPLDispDriver_IsSigned],
					convert(varchar(10), [EPLDD].[EvnPLDispDriver_signDT], 104) as [EvnPLDispDriver_signDT],
					convert(varchar(10), [EPLDD].[EvnPLDispDriver_setDate], 104) as [EvnPLDispDriver_setDate],
					convert(varchar(10), [EVDD].EvnVizitDispDop_setDate, 104) as [EvnPLDispDriver_disDate],
					[UA].Address_Nick as PersonUAddress,
					[PA].Address_Nick as PersonPAddress,
					[RDD].ResultDispDriver_Name
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'EvnUsluga':
				$main_alias = "EvnUsluga";
				if ($dbf === true) {
					if ($this->getRegionNick() != 'kareliya') {
						$add_pu = "";
					} else {
						if (isset($data['and_eplperson']) && $data['and_eplperson'])
							$add_pu = " '' as PCT_ID,";
						else
							$add_pu = " PS.PersonEvn_id as PCT_ID";
						$add_pu = (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,");
					}
					//" . (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
					$query .= "
						EPL.EvnPL_id as EPL_ID,
						{$add_pu}
						EvnUsluga.EvnUsluga_pid as EPZ_ID,
						EvnUsluga.EvnUsluga_id as EUS_ID,
						EvnUsluga.EvnClass_SysNick as EU_CLASS,
						RTRIM(ISNULL(convert(varchar(10), EvnUsluga.EvnUsluga_setDT, 104), '')) as SETDATE,
						EvnUsluga.EvnUsluga_setTime as SETTIME,
						dbfusluga.UslugaComplex_Code as USL_CODE,
						EvnUsluga.EvnUsluga_Kolvo as KOLVO,
						dbfup.UslugaPlace_Code as UP_CODE,
						dbfmp.MedPersonal_TabCode as MP_CODE,
						dbfpt.PayType_Code as PT_CODE
					";
				}
				break;

			case 'EvnUslugaStom':
				$main_alias = "EvnUsluga";
				if ($dbf === true) {
					$query .= "
						EPLS.EvnPLStom_id as EPL_ID,
						" . (isset($data['and_eplstomperson']) && $data['and_eplstomperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						EvnUsluga.EvnUslugaStom_pid as EPZ_ID,
						EvnUsluga.EvnUslugaStom_id as EUS_ID,
						(select EvnClass_SysNick from v_EvnClass where EvnClass_id = EvnUsluga.EvnClass_id ) as EU_CLASS,
						RTRIM(ISNULL(convert(varchar(10), EvnUsluga.EvnUslugaStom_setDT, 104), '')) as SETDATE,
						EvnUsluga.EvnUslugaStom_setTime as SETTIME,
						dbfusluga.UslugaComplex_Code as USL_CODE,
						EvnUsluga.EvnUslugaStom_Kolvo as KOLVO,
						dbfup.UslugaPlace_Code as UP_CODE,
						dbfmp.MedPersonal_TabCode as MP_CODE,
						dbfpt.PayType_Code as PT_CODE
					";
				}
				break;

			case 'EvnSection':
				$main_alias = "ESEC";
				if ($dbf === true) {
					$query .= "
						ESEC.EvnSection_id as ESEC_COD,
						ESEC.EvnSection_pid as EPS_COD,
						RTRIM(ISNULL(convert(varchar(10), ESEC.EvnSection_setDate, 104), '')) as SETDATE,
						ESEC.EvnSection_setTime as SETTIME,
						RTRIM(ISNULL(convert(varchar(10), ESEC.EvnSection_disDate, 104), '')) as DISDATE,
						ESEC.EvnSection_disTime as DISTIME,
						dbfsec.LpuSection_Code as LS_COD,
						dbfpay.PayType_Code as PAY_COD,
						dbftar.TariffClass_Code as TFCLS_COD,
						dbfmp.MedPersonal_TabCode as MP_COD
					";
				} else {
					$ksg_query = " ,ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSG";
					$kpg_query = " ,kpg.Mes_Code as EvnSection_KPG";

					if ($this->getRegionNick() == 'ekb') {
						$ksg_query = " ,ISNULL(sksg.Mes_Code, '') + ' ' +  ISNULL(sksg.Mes_Name, '') as EvnSection_KSG";
					}
					if ($this->getRegionNick() == 'kareliya') {
						$ksg_query = " ,case 
							when ksgkpg.Mes_id = ksg.Mes_id
							then ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '')
						end as EvnSection_KSG";
						$kpg_query = " ,case 
							when ksgkpg.Mes_id = kpg.Mes_id 
							then ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '')
						end as EvnSection_KPG";
					}

					$query .= "
						 ESEC.EvnSection_id
						,ESEC.EvnSection_pid
						,EPS.Person_id as Person_id
						,EPS.PersonEvn_id as PersonEvn_id
						,EPS.Server_id as Server_id
						,EPS.PrehospWaifRefuseCause_id
						,RTRIM(ISNULL(EPS.EvnPS_NumCard, '')) as EvnPS_NumCard
						,ISNULL(LS.LpuSection_Name, '') as LpuSection_Name
						,ISNULL(LSW.LpuSectionWard_Name, '') as LpuSectionWard_Name
						,ISNULL(Dtmp.Diag_FullName, '') as Diag_Name
						,convert(varchar(10), ESEC.EvnSection_setDate, 104) as EvnSection_setDate
						,convert(varchar(10), ESEC.EvnSection_disDate, 104) as EvnSection_disDate
						,ISNULL(LT.LeaveType_Name, '') as LeaveType_Name
						,PT.PayType_Name as PayType_Name
						,RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio
						,case when ESEC.EvnSection_disDate is not null
							then
								case
									when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1 > 1
									then DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate)
									else DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1
								end
							else null
						  end as EvnSection_KoikoDni
						 ,MES.Mes_Code
						 --,CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
						 ," . $isBDZ . "
						 CASE WHEN ESEC.EvnSection_Index = ESEC.EvnSection_Count-1 THEN 1 ELSE 0 END as EvnSection_isLast
						" . $ksg_query . "
						" . $kpg_query . "
						,case when ESEC.EvnSection_IsAdultEscort = 2 then 'Да' else 'Нет' end as EvnSection_IsAdultEscort
						,ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSGKPG
					";

					if (allowPersonEncrypHIV($data['session'])) {
						$query .= "
							,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_DeadDT, 104) else null end as Person_deadDT
						";
					} else {
						$query .= "
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT
						";
					}
					//#155391 фильтр по фед.сервису / EvnSection
					$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as fedservice_iemk";
					if(isset($data['Service1EvnStatus_id'])) {
						$filter.= " AND SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
						$queryParams['Service1EvnStatus_id'] = $data['Service1EvnStatus_id'];
					}
					
					// Фильтр "Врач"
					// Примечание: "MedPersonal_id" зарезервирован. Используем "MedPersonal_iid".
					if (isset($data['MedPersonal_iid'])) {
						$filter.= " AND {$main_alias}.MedPersonal_id = :MedPersonal_iid ";
						$queryParams['MedPersonal_iid'] = $data['MedPersonal_iid'];
					}
				}
				break;

			case 'EvnDiag':
				$main_alias = "EPSD";
				if ($dbf === true) {
					$query .= "
						EDPS.EvnDiagPS_id as EPSDZ_COD,
						EDPS.EvnDiagPS_pid as EVN_COD,
						RTRIM(ISNULL(convert(varchar(10), EDPS.EvnDiagPS_setDate, 104), '')) as SETDATE,
						EDPS.EvnDiagPS_setTime as SETTIME,
						EDPS.Diag_id as DZ_COD,
						EDPS.DiagSetClass_id as DZCLS_COD,
						EDPS.DiagSetType_id as DZTYP_COD,
						dbfmes.Mes_id as MES_COD
					";
				}
				break;

			case 'EvnLeave':
				$main_alias = "ELV";
				if ($dbf === true) {
					$query .= "
						COALESCE(ELV.EvnLeave_id, dbfeol.EvnOtherLpu_id, dbfed.EvnDie_id, dbfeost.EvnOtherStac_id, dbfeos.EvnOtherSection_id, dbfeosbp.EvnOtherSectionBedProfile_id) as ELV_COD,
						EPS.EvnPS_id as EPS_COD,
						EPS.LeaveType_id as LVTYP_COD,
						ISNULL(ELV.LeaveCause_id, ISNULL(dbfeol.LeaveCause_id, ISNULL(dbfeost.LeaveCause_id, dbfeos.LeaveCause_id))) as LVCS_COD,
						ISNULL(ELV.ResultDesease_id, ISNULL(dbfeol.ResultDesease_id, ISNULL(dbfeost.ResultDesease_id, dbfeos.ResultDesease_id))) as RSTDSS_COD,
						ELV.EvnLeave_IsAmbul as ISAMBUL,
						COALESCE(ELV.EvnLeave_UKL, dbfeol.EvnOtherLpu_UKL, dbfed.EvnDie_UKL, dbfeost.EvnOtherStac_UKL, dbfeos.EvnOtherSection_UKL, dbfeosbp.EvnOtherSectionBedProfile_UKL) as UKL,
						dbflu.LpuUnitType_id as LSTYP_COD,
						dbfeost.LeaveCause_id as OPSCS_COD,
						dbfls.LpuSection_id as LS_COD,
						dbfeol.Lpu_id as OLPU_OGRN,
						dbfeol.LeaveCause_id as OLPUCS_COD,
						dbfeos.LeaveCause_id as OLSCS_COD,
						dbfed.MedPersonal_id as MP_COD,
						dbfed.IsWait as ISWAIT,
						dbfed.EvnDie_IsAnatom as ISANATOM,
						null as ISBIRTH,
						dbfed.MedPersonal_aid as AMP_COD,
						dbfed.AnatomWhere_id as ANWHR_COD
					";
				}
				break;

			case 'EvnStick':
				$main_alias = "EST";
				if ($dbf === true) {
					$query .= "
						EST.EvnStick_id as EST_COD,
						EST.EvnStick_pid as EPS_COD,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnPS_setDate, 104), '')) as SETDATE,
						EST.StickType_id as STKTYP_COD,
						EST.EvnStick_Ser as SER,
						EST.EvnStick_Num as NUM,
						EST.StickOrder_id as ISCONT,
						EST.EvnStick_Age as AGE,
						RTRIM(ISNULL(convert(varchar(10), EST.EvnStick_begDate, 104), '')) as BEGDATE,
						RTRIM(ISNULL(convert(varchar(10), EST.EvnStick_endDate, 104), '')) as ENDDATE,
						EST.Sex_id as SEX_COD,
						EST.StickCause_id as STKCS_COD
					";
				}
				break;

			case 'KvsPerson':
				$main_alias = "PS";
				if ($dbf === true) {
					$query .= "
						{$PS_prefix}.PersonEvn_id as PCT_ID,
						{$PS_prefix}.Person_id as P_ID,
						{$PS_prefix}.Person_SurName as SURNAME,
						{$PS_prefix}.Person_FirName as FIRNAME,
						{$PS_prefix}.Person_SecName as SECNAME,
						rtrim(isnull(convert(varchar(10), {$PS_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
						{$PS_prefix}.Person_Snils as SNILS,
						IsInv.YesNo_Code as INV_N,
						InvD.Diag_Code as INV_DZ,
						rtrim(isnull(convert(varchar(10), PCh.PersonChild_invDate, 104),'')) as INV_DATA,
						Sex.Sex_Name as SEX,
						Soc.SocStatus_Name as SOC,
						OMSST.OMSSprTerr_Code as P_TERK,
						OMSST.OMSSprTerr_Name as P_TER,
						PolTp.PolisType_Name as P_NAME,
						Pol.Polis_Ser as P_SER,
						Pol.Polis_Num as P_NUM,
						{$PS_prefix}.Person_EdNum as P_NUMED,
						rtrim(isnull(convert(varchar(10), Pol.Polis_begDate, 104),'')) as P_DATA,
						OSO.Org_Code as SMOK,
						OS.OrgSmo_Name as SMO,
						0 as AR_TP,
						UA.Address_Zip as AR_IDX,
						UA.KLCountry_Name as AR_LND,
						UA.KLRGN_Name as AR_RGN,
						UA.KLSubRGN_Name as AR_RN,
						UA.KLCity_Name as AR_CTY,
						UA.KLTown_Name as AR_NP,
						UA.KLStreet_Name as AR_STR,
						UA.Address_House as AR_DOM,
						UA.Address_Corpus as AR_K,
						UA.Address_Flat as AR_KV,
						0 as AP_TP,
						PA.Address_Zip as AP_IDX,
						PA.KLCountry_Name as AP_LND,
						PA.KLRGN_Name as AP_RGN,
						PA.KLSubRGN_Name as AP_RN,
						PA.KLCity_Name as AP_CTY,
						PA.KLTown_Name as AP_NP,
						PA.KLStreet_Name as AP_STR,
						PA.Address_House as AP_DOM,
						PA.Address_Corpus as AP_K,
						PA.Address_Flat as AP_KV,
						DocTp.DocumentType_Name as D_TIP,
						Doc.Document_Ser as D_SER,
						Doc.Document_Num as D_NOM,
						OrgD.Org_id as D_OUT,
						rtrim(isnull(convert(varchar(10), Doc.Document_begDate, 104),'')) as D_DATA
					";
				}
				break;

			case 'EPLPerson':
				$main_alias = "PS";
				if ($dbf === true) {
					$query .= "
						{$PL_prefix}.PersonEvn_id as PCT_ID,
						{$PL_prefix}.Person_id as P_ID,
						{$PL_prefix}.Person_SurName as SURNAME,
						{$PL_prefix}.Person_FirName as FIRNAME,
						{$PL_prefix}.Person_SecName as SECNAME,
						rtrim(isnull(convert(varchar(10), {$PL_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
						{$PL_prefix}.Person_Snils as SNILS,
						IsInv.YesNo_Code as INV_N,
						InvD.Diag_Code as INV_DZ,
						rtrim(isnull(convert(varchar(10), PCh.PersonChild_invDate, 104),'')) as INV_DATA,
						Sex.Sex_Name as SEX,
						Soc.SocStatus_Name as SOC,
						OMSST.OMSSprTerr_Code as P_TERK,
						OMSST.OMSSprTerr_Name as P_TER,
						PolTp.PolisType_Name as P_NAME,
						Pol.Polis_Ser as P_SER,
						Pol.Polis_Num as P_NUM,
						{$PL_prefix}.Person_EdNum as P_NUMED,
						rtrim(isnull(convert(varchar(10), Pol.Polis_begDate, 104),'')) as P_DATA,
						OSO.Org_Code as SMOK,
						OS.OrgSmo_Name as SMO,
						0 as AR_TP,
						UA.Address_Zip as AR_IDX,
						UA.KLCountry_Name as AR_LND,
						UA.KLRGN_Name as AR_RGN,
						UA.KLSubRGN_Name as AR_RN,
						UA.KLCity_Name as AR_CTY,
						UA.KLTown_Name as AR_NP,
						UA.KLStreet_Name as AR_STR,
						UA.Address_House as AR_DOM,
						UA.Address_Corpus as AR_K,
						UA.Address_Flat as AR_KV,
						0 as AP_TP,
						PA.Address_Zip as AP_IDX,
						PA.KLCountry_Name as AP_LND,
						PA.KLRGN_Name as AP_RGN,
						PA.KLSubRGN_Name as AP_RN,
						PA.KLCity_Name as AP_CTY,
						PA.KLTown_Name as AP_NP,
						PA.KLStreet_Name as AP_STR,
						PA.Address_House as AP_DOM,
						PA.Address_Corpus as AP_K,
						PA.Address_Flat as AP_KV,
						DocTp.DocumentType_Name as D_TIP,
						Doc.Document_Ser as D_SER,
						Doc.Document_Num as D_NOM,
						OrgD.Org_id as D_OUT,
						rtrim(isnull(convert(varchar(10), Doc.Document_begDate, 104),'')) as D_DATA
					";
				}
				break;
			case 'EPLStomPerson':
				$main_alias = "PS";
				if ($dbf === true) {
					$query .= "
						{$PLS_prefix}.PersonEvn_id as PCT_ID,
						{$PLS_prefix}.Person_id as P_ID,
						{$PLS_prefix}.Person_SurName as SURNAME,
						{$PLS_prefix}.Person_FirName as FIRNAME,
						{$PLS_prefix}.Person_SecName as SECNAME,
						rtrim(isnull(convert(varchar(10), {$PLS_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
						{$PLS_prefix}.Person_Snils as SNILS,
						IsInv.YesNo_Code as INV_N,
						InvD.Diag_Code as INV_DZ,
						rtrim(isnull(convert(varchar(10), PCh.PersonChild_invDate, 104),'')) as INV_DATA,
						Sex.Sex_Name as SEX,
						Soc.SocStatus_Name as SOC,
						OMSST.OMSSprTerr_Code as P_TERK,
						OMSST.OMSSprTerr_Name as P_TER,
						PolTp.PolisType_Name as P_NAME,
						Pol.Polis_Ser as P_SER,
						Pol.Polis_Num as P_NUM,
						{$PLS_prefix}.Person_EdNum as P_NUMED,
						rtrim(isnull(convert(varchar(10), Pol.Polis_begDate, 104),'')) as P_DATA,
						OSO.Org_Code as SMOK,
						OS.OrgSmo_Name as SMO,
						0 as AR_TP,
						UA.Address_Zip as AR_IDX,
						UA.KLCountry_Name as AR_LND,
						UA.KLRGN_Name as AR_RGN,
						UA.KLSubRGN_Name as AR_RN,
						UA.KLCity_Name as AR_CTY,
						UA.KLTown_Name as AR_NP,
						UA.KLStreet_Name as AR_STR,
						UA.Address_House as AR_DOM,
						UA.Address_Corpus as AR_K,
						UA.Address_Flat as AR_KV,
						0 as AP_TP,
						PA.Address_Zip as AP_IDX,
						PA.KLCountry_Name as AP_LND,
						PA.KLRGN_Name as AP_RGN,
						PA.KLSubRGN_Name as AP_RN,
						PA.KLCity_Name as AP_CTY,
						PA.KLTown_Name as AP_NP,
						PA.KLStreet_Name as AP_STR,
						PA.Address_House as AP_DOM,
						PA.Address_Corpus as AP_K,
						PA.Address_Flat as AP_KV,
						DocTp.DocumentType_Name as D_TIP,
						Doc.Document_Ser as D_SER,
						Doc.Document_Num as D_NOM,
						OrgD.Org_id as D_OUT,
						rtrim(isnull(convert(varchar(10), Doc.Document_begDate, 104),'')) as D_DATA
					";
				}
				break;

			case 'KvsPersonCard':
				$main_alias = "PC";
				if ($dbf === true) {
					$query .= "
						PC.PersonCard_id as REG_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PC.Person_id as P_ID,
						PC.PersonCard_Code as PR_AK,
						PC.LpuAttachType_Name as PR_TP,
						rtrim(isnull(convert(varchar(10), PC.PersonCard_begDate, 104),'')) as PR_DATA,
						Lpu.Org_Code as LPUK,
						Lpu.Lpu_Name as LPU,
						PC.LpuRegionType_Name as TPLOT,
						PC.LpuRegion_Name as LOT
					";
				}
				break;

			case 'KvsEvnDiag':
				$main_alias = "EDPS";
				if ($dbf === true) {
					$query .= "
						EDPS.EvnDiagPS_id as DZ_ID,
						EPS.EvnPS_id as GSP_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						(case
							when EDPS.DiagSetType_id = 1 then 1
							when EDPS.DiagSetType_id = 2 then 2
							when EDPS.DiagSetType_id = 3 then 3
							else 0
						end) as KTO,
						Lpu.Org_Code as LPUK,
						Lpu.Lpu_Name as LPU,
						LS.LpuSection_Code as OTDK,
						LS.LpuSection_Name as OTD,
						rtrim(isnull(convert(varchar(10), EDPS.EvnDiagPS_setDT, 104),'')) as DZ_DATA,
						DSC.DiagSetClass_Name as DZ_W,
						DST.DiagSetType_Name as DZ_T,
						Diag.Diag_Code as DZ_DZ
					";
				}
				break;

			case 'KvsEvnPS':
				$main_alias = "EPS";
				if ($dbf === true) {
					$query .= "
						EPS.EvnPs_id as GSP_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						IsCont.YesNo_Code as KARTPR,
						EPS.EvnPS_NumCard as KART,
						PT.PayType_Name as WOPL,
						rtrim(isnull(convert(varchar(10), EPS.EvnPS_setDate, 104),'')) as DATAPOST,
						EPS.EvnPS_setTime as TIMEPOST,
						dbo.Age(PS.Person_BirthDay, EPS.EvnPS_setDate) as AGEPOST,
						PD.PrehospDirect_Name as KN_KT,
						LS.LpuSection_Code as KN_OTDLK,
						LS.LpuSection_Name as KN_OTDL,
						Org.Org_Code as KN_ORGK,
						Org.Org_Name as KN_ORG,
						IsFond.YesNo_Code as KN_FD,
						EPS.EvnDirection_Num as KN_N,
						rtrim(isnull(convert(varchar(10), EPS.EvnDirection_setDT, 104),'')) as KN_DATA,
						PA.PrehospArrive_Name as KD_KT,
						EPS.EvnPS_CodeConv as KD_KOD,
						EPS.EvnPS_NumConv as KD_NN,
						DiagD.Diag_Code as DZGOSP,
						IsImperHosp.YesNo_Code as DEF_NG,
						IsShortVolume.YesNo_Code as DEF_NOO,
						IsWrongCure.YesNo_Code as DEF_NTL,
						IsDiagMismatch.YesNo_Code as DEF_ND,
						Toxic.PrehospToxic_Name as ALKO,
						PType.PrehospType_Name as PR_GP,
						EPS.EvnPS_HospCount as PR_N,
						EPS.EvnPS_TimeDesease as PR_W,
						Trauma.TraumaType_Name as TR_T,
						IsUnLaw.YesNo_Code as TR_P,
						IsUnport.YesNo_Code as TR_N,
						pLS.LpuSection_Code as PRO_NK,
						pLS.LpuSection_Name as PRO_N,
						MP.MedPersonal_TabCode as PRO_DOCK,
						MP.Person_FIO as PRO_DOC,
						Diag.Diag_Code as PRO_DZ
					";
				}
				break;

			case 'KvsEvnSection':
				$main_alias = "ESEC";
				if ($dbf === true) {
					$query .= "
						ESEC.EvnSection_id as HSTRY_ID,
						EPS.EvnPS_id as GSP_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						rtrim(isnull(convert(varchar(10), ESEC.EvnSection_setDate, 104),'')) as DATAP,
						ESEC.EvnSection_setTime as TIMEP,
						rtrim(isnull(convert(varchar(10), ESEC.EvnSection_disDate, 104),'')) as DATAW,
						ESEC.EvnSection_disTime as TIMEW,
						LS.LpuSection_Code as OTDLK,
						LS.LpuSection_Name as OTDL,
						PT.PayType_Name as WO,
						TC.TariffClass_Name as WT,
						MP.MedPersonal_TabCode as DOCK,
						MP.Person_Fio as DOC,
						Diag.Diag_Code as DZ,
						Mes.Mes_Code as MES,
						Mes.Mes_KoikoDni as NORM,
						case when ESEC.EvnSection_disDate is not null
							then
								case
									when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1 > 1
									then DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate)
									else DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1
								end
							else null
						end as KDN
					";
				}
				break;

			case 'KvsNarrowBed':
				$main_alias = "ESNB";
				if ($dbf === true) {
					$query .= "
						ESNB.EvnSectionNarrowBed_id as UK_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						ESEC.EvnSection_id as HSTRY_ID,
						rtrim(isnull(convert(varchar(10), ESNB.EvnSectionNarrowBed_setDT, 104),'')) as DATAP,
						rtrim(isnull(convert(varchar(10), ESNB.EvnSectionNarrowBed_disDT, 104),'')) as DATAW,
						LS.LpuSection_Code as OTDLK,
						LS.LpuSection_Name as OTDL
					";
				}
				break;

			case 'KvsEvnUsluga':
				$main_alias = "EU";
				if ($dbf === true) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						UC.UslugaClass_Name as U_TIP,
						rtrim(isnull(convert(varchar(10), EU.EvnUsluga_setDate, 104),'')) as U_DATA,
						EU.EvnUsluga_setTime as U_TIME,
						UP.UslugaPlace_Name as U_MESTO,
						LS.LpuSection_Code as U_OTELK,
						LS.LpuSection_Name as U_OTEL,
						Lpu.Org_Code as U_LPUK,
						Lpu.Lpu_Name as U_LPU,
						Org.Org_Code as U_ORGK,
						Org.Org_Name as U_ORG,
						MP.MedPersonal_TabCode as U_DOCK,
						MP.Person_Fio as U_DOC,
						U.UslugaComplex_Code as U_USLKOD,
						U.UslugaComplex_Name as U_USL,
						PT.Paytype_Name as U_WO,
						OT.OperType_Name as U_TIPOP,
						OD.OperDiff_Name as U_KATSLOJ,
						IsEndoskop.YesNo_Code as U_PREND,
						IsLazer.YesNo_Code as U_PRLAS,
						IsKriogen.YesNo_Code as U_PRKRI,
						EU.EvnUsluga_Kolvo as U_KOL
					";
				}
				break;

			case 'KvsEvnUslugaOB':
				$main_alias = "EU";
				if ($dbf === true) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						ST.SurgType_Name as U_WID,
						MP.MedPersonal_TabCode as U_DOCK,
						MP.Person_Fio as U_DOC
					";
				}
				break;

			case 'KvsEvnUslugaAn':
				$main_alias = "EU";
				if ($dbf === true) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						AC.AnesthesiaClass_Name as U_ANEST
					";
				}
				break;

			case 'KvsEvnUslugaOsl':
				$main_alias = "EU";
				if ($dbf === true) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						rtrim(isnull(convert(varchar(10), EA.EvnAgg_setDate, 104),'')) as U_DATA,
						EA.EvnAgg_setTime as U_TIME,
						AT.AggType_Name as U_WID,
						AW.AggWhen_Name as U_KONT
					";
				}
				break;

			case 'KvsEvnDrug':
				$main_alias = "ED";
				if ($dbf === true) {
					$query .= "
						ED.EvnDrug_id as MED_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						rtrim(isnull(convert(varchar(10), ED.EvnDrug_setDate, 104),'')) as M_DATA,
						LS.LpuSection_Code as M_OTDLK,
						LS.LpuSection_Name as M_OTDL,
						(Mol.Person_SurName + ' ' + Mol.Person_FirName + ' ' + Mol.Person_SecName) as M_MOL,
						Drug.Drug_Code as MEDK,
						Drug.Drug_Name as MED,
						(
							'годн. ' + IsNull(convert(varchar(10), Part.DocumentUcStr_godnDate, 104),'отсут.') +
							', цена ' + cast(ROUND(IsNull(Part.DocumentUcStr_PriceR,0), 2) as varchar(20)) +
							', ост. ' + cast(Round(IsNull(Part.DocumentUcStr_Ost,0),4) as varchar(20)) +
							', фин. ' + RTRIM(RTRIM(ISNULL(Part.DrugFinance_Name, 'отсут.'))) +
							', серия ' + RTRIM(ISNULL(Part.DocumentUcStr_Ser, ''))
						) as M_PART,
						Drug.Drug_Fas as M_KOL,
						Drug.Drug_PackName as M_EU,
						DUS.DocumentUcStr_Count as M_EU_OCT,
						ED.EvnDrug_Kolvo as M_EU_KOL,
						Drug.DrugForm_Name as M_ED,
						DUS.DocumentUcStr_EdCount as M_ED_OCT,
						ED.EvnDrug_KolvoEd as M_ED_KOL,
						ED.EvnDrug_Price as M_CENA,
						ED.EvnDrug_Sum as M_SUM
					";
				}
				break;

			case 'KvsEvnLeave':
				$main_alias = "ELV";
				if ($dbf === true) {
					$query .= "
						(case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_id
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_id
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_id
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_id
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_id
						end) as ISCH_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						LType.LeaveType_Name as IG_W,
						rtrim(isnull(convert(varchar(10), (case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_setDate
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_setDate
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_setDate
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_setDate
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_setDate
						end), 104),'')) as IS_DATA,
						(case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_setTime
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_setTime
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_setTime
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_setTime
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_setTime
						end) as IS_TIME,
						(case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_UKL
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_UKL
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_UKL
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_UKL
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_UKL
						end) as IS_URUW,
						RD.ResultDesease_Name as IS_BOL,
						LC.LeaveCause_Name as IS_PR,
						IsAmbul.YesNo_Code as IS_NAPR,
						EOLpuL.Org_Code as IS_LPUK,
						EOLpuL.Org_Name as IS_LPU,
						EOStacLUT.LpuUnitType_Name as IS_TS,
						LS.LpuSection_Code as IS_STACK,
						LS.LpuSection_Name as IS_STAC,
						MP.MedPersonal_TabCode as IS_DOCK,
						MP.Person_Fio as IS_DOC,
						DieDiag.Diag_Code as IS_DZ
					";
				}
				break;

			case 'KvsEvnStick':
				$main_alias = "EST";
				if ($dbf === true) {
					$query .= "
						EST.EvnStick_id as LWN_ID,
						" . (isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						SO.StickOrder_Name as PORYAD,
						ISNULL(NULLIF(LTRIM(RTRIM(ISNULL(EST.EvnStick_Ser, '') + ' ' + ISNULL(EST.EvnStick_Num, '') + ', ' + rtrim(isnull(convert(varchar(10), EST.EvnStick_begDate, 104),'')))), 'от'), '') as LWNOLD,
						EST.EvnStick_Ser as LWN_S,
						EST.EvnStick_Num as LWN_N,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_begDate, 104),'')) as LWN_D,
						SC.StickCause_Name as LWN_PR,
						(ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '')) as ROD_FIO,
						dbo.Age(PS.Person_BirthDay, CURRENT_TIMESTAMP) as ROD_W,
						Sex.Sex_Name as ROD_POL,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_sstBegDate, 104),'')) as SKL_DN,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_sstEndDate, 104),'')) as SKL_DK,
						EST.EvnStick_sstNum as SKL_NOM,
						EST.EvnStick_sstPlace as SKL_LPU,
						SR.StickRegime_Name as LWN_R,
						SLT.StickLeaveType_Name as LWN_ISCH,
						EST.EvnStick_SerCont as LWN_SP,
						EST.EvnStick_NumCont as LWN_NP,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_workDT, 104),'')) as LWN_DR,
						MP.MedPersonal_TabCode as LWN_DOCK,
						MP.Person_Fio as LWN_DOC,
						Lpu.Org_Code as LWN_LPUK,
						Lpu.Lpu_Name as LWN_LPU,
						D1.Diag_Code as LWN_DZ1,
						'' as LWN_DZ2
						-- D2.Diag_Code as LWN_DZ2
					";
				}
				break;

			case 'EvnAgg':
				$main_alias = "EvnAgg";
				if ($dbf === true) {
					if ($this->getRegionNick() != 'kareliya') {
						$add_pa = "";
					} else {
						if (isset($data['and_eplperson']) && $data['and_eplperson'])
							$add_pa = " '' as PCT_ID,";
						else
							$add_pa = " PS.PersonEvn_id as PCT_ID";
						$add_pa = (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,");
					}
					//" . (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
					$query .= "
						EvnAgg.EvnAgg_pid as EUS_ID,
						{$add_pa}
						EvnAgg.EvnAgg_id as EAGG_ID,
						RTRIM(ISNULL(convert(varchar(10), EvnAgg.EvnAgg_setDT, 104), '')) as SETDATE,
						EvnAgg.EvnAgg_setTime as SETTIME,
						dbfaw.AggWhen_Code as AW_CODE,
						dbfat.AggType_Code as AT_CODE
					";
				}
				break;
			case 'EvnAggStom':
				$main_alias = "EvnAgg";
				if ($dbf === true) {
					$query .= "
						EvnAgg.EvnAgg_pid as EUS_ID,
						" . (isset($data['and_eplstomperson']) && $data['and_eplstomperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						EvnAgg.EvnAgg_id as EAGG_ID,
						RTRIM(ISNULL(convert(varchar(10), EvnAgg.EvnAgg_setDT, 104), '')) as SETDATE,
						EvnAgg.EvnAgg_setTime as SETTIME,
						dbfaw.AggWhen_Code as AW_CODE,
						dbfat.AggType_Code as AT_CODE
					";
				}
				break;
			case 'EvnVizitPL':
				$main_alias = "EVizitPL";
				if ($dbf === true) {
					if ($this->getRegionNick() != 'kareliya') {
						$add_pv = "";
					} else {
						if (isset($data['and_eplperson']) && $data['and_eplperson'])
							$add_pv = " '' as PCT_ID,";
						else
							$add_pv = " PS.PersonEvn_id as PCT_ID";
						$add_pv = (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,");
					}
					//" . (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
					$query .= "
						EVizitPL.EvnVizitPL_pid as EPL_ID,
						{$add_pv}
						EVizitPL.EvnVizitPL_id as EVZ_ID,
						EPL.EvnPL_NumCard as NUMCARD,
						rtrim(isnull(convert(varchar, cast(EVizitPL.EvnVizitPL_setDate as datetime),104),'')) as SETDATE,
						EVizitPL.EvnVizitPL_setTime as SETTIME,
						dbfvc.VizitClass_Code as PERVVTOR,
						dbfls.LpuSection_Code as LS_COD,
						rtrim(dbfls.LpuSection_Name) as LS_NAM,
						dbfmp.MedPersonal_TabCode as MP_COD,
						rtrim(dbfmp.Person_FIO) as MP_FIO,
						dbfpt.PayType_Code as PAY_COD,
						dbfvt.VizitType_Code as VZT_COD,
						dbfst.ServiceType_Code as SRT_COD,
						dbfpg.ProfGoal_code AS PRG_COD,
						dbfdiag.Diag_Code as DZ_COD,
						rtrim(dbfdiag.Diag_Name) as DZ_NAM,
						dbfdt.DeseaseType_Code as DST_COD
					";
				} else {
					$query .= "
						EVizitPL.EvnVizitPL_id,
						EVizitPL.EvnVizitPL_pid as EvnPL_id,
						Evn.Person_id,
						Evn.PersonEvn_id,
						Evn.Server_id,
						RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
						RTRIM(evpldiag.Diag_Code) + '. ' + RTRIM(evpldiag.Diag_Name) as Diag_Name,
						RTRIM(evplls.LpuSection_Name) as LpuSection_Name,--отделение
						RTRIM(evplmp.Person_Fio) as MedPersonal_Fio,
						-- Услуга
						" . (in_array($data['session']['region']['nick'], array('ufa')) ? "EU.UslugaComplex_Code, " : "NULL as UslugaComplex_Code,") . "
						convert(varchar(10), cast(EVizitPL.EvnVizitPL_setDate as datetime), 104) as EvnVizitPL_setDate,--Дата посещения,
						RTRIM(evplst.ServiceType_Name) as ServiceType_Name,--Место обслуживания,
						RTRIM(evplvt.VizitType_Name) as VizitType_Name, --Цель посещения,
						RTRIM(evplpt.PayType_Name) as PayType_Name,--Вид оплаты,
						" . $isBDZ . "
						RTRIM(evplhk.HealthKind_Name) as HealthKind_Name--Группа здоровья,
						--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ

					";

					if (allowPersonEncrypHIV($data['session'])) {
						$query .= "
							,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
						";
					} else {
						$query .= "
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
						";
					}

					if(!empty($data['InterruptLeaveType_id'])) {
						$queryParams['InterruptLeaveType_id'] = $data['InterruptLeaveType_id'];
						$filter .= " and EPL.InterruptLeaveType_id = :InterruptLeaveType_id";
					}
					//#155391 фильтр по фед.сервису / EvnVizitPL
					$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as fedservice_iemk";
					if(isset($data['Service1EvnStatus_id'])) {
						$filter.= " AND SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
						$queryParams['Service1EvnStatus_id'] = $data['Service1EvnStatus_id'];
					}
				}
				break;

			case 'EvnVizitPLStom':
				$main_alias = "EVPLS";
				if ($dbf === true) {
					$query .= "
						EVPLS.EvnVizitPLStom_pid as EPL_ID,
						" . (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						EVPLS.EvnVizitPLStom_id as EVZ_ID,
						EPLS.EvnPLStom_NumCard as NUMCARD,
						rtrim(isnull(convert(varchar, cast(EVPLS.EvnVizitPLStom_setDate as datetime),104),'')) as SETDATE,
						EVPLS.EvnVizitPLStom_setTime as SETTIME,
						dbfvc.VizitClass_Code as PERVVTOR,
						dbfls.LpuSection_Code as LS_COD,
						rtrim(dbfls.LpuSection_Name) as LS_NAM,
						dbfmp.MedPersonal_TabCode as MP_COD,
						rtrim(dbfmp.Person_FIO) as MP_FIO,
						dbfpt.PayType_Code as PAY_COD,
						dbfvt.VizitType_Code as VZT_COD,
						dbfst.ServiceType_Code as SRT_COD,
						dbfpg.ProfGoal_code AS PRG_COD,
						dbfdiag.Diag_Code as DZ_COD,
						rtrim(dbfdiag.Diag_Name) as DZ_NAM,
						dbfdt.DeseaseType_Code as DST_COD
					";
				} else {
					$query .= "
						EVPLS.EvnVizitPLStom_id as EvnVizitPLStom_id,
						EVPLS.EvnVizitPLStom_pid as EvnPLStom_id,
						EPLS.Person_id as Person_id,
						EPLS.PersonEvn_id as PersonEvn_id,
						EPLS.Server_id as Server_id,
						RTRIM(EPLS.EvnPLStom_NumCard) as EvnPLStom_NumCard,
						ISNULL((RTRIM(evpldiag.Diag_Code) + '. ' + RTRIM(evpldiag.Diag_Name)),'') as Diag_Name,
						RTRIM(evplls.LpuSection_Name) as LpuSection_Name,
						RTRIM(MP.Person_Fio) as MedPersonal_Fio,
						convert(varchar(10), cast(EVPLS.EvnVizitPLStom_setDate as datetime), 104) as EvnVizitPLStom_setDate,
						RTRIM(evplst.ServiceType_Name) as ServiceType_Name,
						RTRIM(evplvt.VizitType_Name) as VizitType_Name,
						RTRIM(evplpt.PayType_Name) as PayType_Name,
						" . $isBDZ . "
						EVPLS.EvnVizitPLStom_Uet as EvnVizitPLStom_Uet

						--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
					";

					if (allowPersonEncrypHIV($data['session'])) {
						$query .= "
							,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
						";
					} else {
						$query .= "
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
						";
					}

					if(!empty($data['InterruptLeaveType_id'])) {
						$queryParams['InterruptLeaveType_id'] = $data['InterruptLeaveType_id'];
						$filter .= " and EPLS.InterruptLeaveType_id = :InterruptLeaveType_id";
					}
				}
				break;

			case 'EvnPL':
				$main_alias = "EPL";
				if ($dbf === true) {
					if ($this->getRegionNick() != 'kareliya') {
						$add_p = "";
						$add_p2 = "
							PS.Person_SurName as SURNAME,
							PS.Person_FirName as FIRNAME,
							PS.Person_SecName as SECNAME,
							convert(varchar(10), PS.Person_BirthDay, 104) as BIRTHDAY,
							dbfsex.Sex_Code as POL_COD,
							dbfss.SocStatus_code as SOC_COD,
							ISNULL(dbfkls.Kladr_Code, dbfkla.Kladr_Code) as KOD_TER,
							PS.person_Snils as SNILS,
						";
					} else {
						if (isset($data['and_eplperson']) && $data['and_eplperson'])
							$add_p = " '' as PCT_ID,";
						else
							$add_p = " PS.PersonEvn_id as PCT_ID";
						$add_p = (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,");
						$add_p2 = "";
					}
					//" . (isset($data['and_eplperson']) && $data['and_eplperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
					$query .= "
						EPL.EvnPL_id as EPL_ID,
						{$add_p}
						dbfpd.PrehospDirect_Code as DIR_CODE,
						case
							when dbfpd.PrehospDirect_Code = 1 then ISNULL(NULLIF(RTRIM(dbflsd.LpuSection_Code), ''), '')
							when dbfpd.PrehospDirect_Code = 2 then ISNULL(NULLIF(RTRIM(dbfprehosplpu.Lpu_Ouz), ''), '')
							else '' end
						as PDO_CODE,
						convert(varchar(10), Evn.Evn_setDT, 104) as SETDATE,
						convert(varchar(5), Evn.Evn_setDT, 108) as SETTIME,
						convert(varchar(10), Evn.Evn_disDT, 104) as DISDATE,
						convert(varchar(5), Evn.Evn_disDT, 108) as DISTIME,
						EPL.EvnPL_NumCard as NUMCARD,
						dbfift.YesNo_Code as VPERV,
						ISNULL(EPL.EvnPL_Complexity, 0) as KATEGOR,
						dbflpu.Lpu_OGRN as OGRN,
						{$add_p2}
						--PS.Person_SurName as SURNAME,
						--PS.Person_FirName as FIRNAME,
						--PS.Person_SecName as SECNAME,
						--convert(varchar(10), PS.Person_BirthDay, 104) as BIRTHDAY,
						--dbfsex.Sex_Code as POL_COD,
						--dbfss.SocStatus_code as SOC_COD,
						--ISNULL(dbfkls.Kladr_Code, dbfkla.Kladr_Code) as KOD_TER,
						--PS.person_Snils as SNILS,
						case when EvnPLBase.EvnPLBase_IsFinish = 2 then '1' else '0' end as FINISH_ID,
						dbfrc.ResultClass_Code as RSC_COD,
						dbfdiag.Diag_Code as DZ_COD,
						dbfdiag.Diag_Name as DZ_NAM,
						dbfdt.DeseaseType_code as DST_COD,
						cast(EPL.EvnPL_UKL as float) as UKL,
						ISNULL(dbfdc.DirectClass_Code, 0) as CODE_NAP,
						case
							when dbfdc.DirectClass_Code = 1 then ISNULL(NULLIF(RTRIM(dbflsdir.LpuSection_Code), ''), '')
							when dbfdc.DirectClass_Code = 2 then ISNULL(NULLIF(RTRIM(dbflpudir.Lpu_Ouz), ''), '')
							else '' end
						as KUDA_NAP,
						dbfinv.PersonChild_IsInvalid_Code as INVALID,
						dbfinv.PermRegion_Code as REG_PERM,
						CASE WHEN PS.Server_pid = 0 THEN 'Да' ELSE 'Нет' END as BDZ
					";
				} else {
					$query .= "
						EPL.EvnPL_id,
						Evn.Person_id,
						Evn.PersonEvn_id,
						Evn.Server_id,

						CASE WHEN pls.PolisType_id = 4 THEN PS.Person_edNum ELSE pls.Polis_Num END as Polis_Num,

						ISNULL(Evn.Evn_IsTransit, 1) as EvnPL_IsTransit,
						RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
						EvnPLBase.EvnPLBase_VizitCount as EvnPL_VizitCount,
						IsFinish.YesNo_Name as EvnPL_IsFinish,
						RTRIM(EVPLD.Diag_Code) + '. ' + RTRIM(EVPLD.Diag_Name) as Diag_Name,
						RTRIM(MP.Person_Fio) as MedPersonal_Fio,
						convert(varchar(10), Evn.Evn_setDT, 104) as EvnPL_setDate,
						convert(varchar(10), Evn.Evn_disDT, 104) as EvnPL_disDate,
						RTRIM(HK.HealthKind_Name) as HealthKind_Name,
						" . $isBDZ . "
						isnull(VT.VizitType_Name,'') as VizitType_Name,
						convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
						case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
					";

					if(!empty($data['InterruptLeaveType_id'])) {
						$queryParams['InterruptLeaveType_id'] = $data['InterruptLeaveType_id'];
						$filter .= " and EPL.InterruptLeaveType_id = :InterruptLeaveType_id";
					}
					//#155391 фильтр по фед.сервису / EvnPL
					$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as fedservice_iemk";
					if(isset($data['Service1EvnStatus_id'])) {
						$filter.= " AND SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
						$queryParams['Service1EvnStatus_id'] = $data['Service1EvnStatus_id'];
					}
					if (allowPersonEncrypHIV($data['session'])) {
						$query .= "
							,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
						";
					} else {
						$query .= "
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
						";
					}

					if ($this->regionNick == 'kz') {
						$query .= " ,CASE WHEN air.AISResponse_id is not null THEN 'true' ELSE 'false' END as toAis25 ";
						$query .= " ,CASE WHEN air9.AISResponse_id is not null THEN 'true' ELSE 'false' END as toAis259 ";
					}
				}
				break;

			case 'EvnPLStom':
				$main_alias = "EPLS";
				if ($dbf === true) {
					$query .= "
						EPLS.EvnPLStom_id as EPL_ID,
						" . (isset($data['and_eplstomperson']) && $data['and_eplstomperson'] ? "" : "PS.PersonEvn_id as PCT_ID,") . "
						dbfpd.PrehospDirect_Code as DIR_CODE,
						case
							when dbfpd.PrehospDirect_Code = 1 then ISNULL(NULLIF(RTRIM(dbflsd.LpuSection_Code), ''), '')
							when dbfpd.PrehospDirect_Code = 2 then ISNULL(NULLIF(RTRIM(dbfprehosplpu.Lpu_Ouz), ''), '')
							else '' end
						as PDO_CODE,
						convert(varchar(10), EPLS.EvnPLStom_setDate, 104) as SETDATE,
						convert(varchar(5), EPLS.EvnPLStom_setDate, 108) as SETTIME,
						convert(varchar(10), EPLS.EvnPLStom_disDate, 104) as DISDATE,
						convert(varchar(5), EPLS.EvnPLStom_disDate, 108) as DISTIME,
						RTRIM(EPLS.EvnPLStom_NumCard) as NUMCARD,
						dbfift.YesNo_Code as VPERV,
						ISNULL(EPLS.EvnPLStom_Complexity, 0) as KATEGOR,
						dbflpu.Lpu_OGRN as OGRN,
						--dbfsex.Sex_Code as POL_COD,
						--dbfss.SocStatus_code as SOC_COD,
						--ISNULL(dbfkls.Kladr_Code, dbfkla.Kladr_Code) as KOD_TER,
						case when EPLS.EvnPLStom_IsFinish = 2 then '1' else '0' end as FINISH_ID,
						dbfrc.ResultClass_Code as RSC_COD,
						dbfdiag.Diag_Code as DZ_COD,
						dbfdiag.Diag_Name as DZ_NAM,
						dbfdt.DeseaseType_code as DST_COD,
						cast(EPLS.EvnPLStom_UKL as float) as UKL,
						ISNULL(dbfdc.DirectClass_Code, 0) as CODE_NAP,
						case
							when dbfdc.DirectClass_Code = 1 then ISNULL(NULLIF(RTRIM(dbflsdir.LpuSection_Code), ''), '')
							when dbfdc.DirectClass_Code = 2 then ISNULL(NULLIF(RTRIM(dbflpudir.Lpu_Ouz), ''), '')
							else '' end
						as KUDA_NAP,
						dbfinv.PersonChild_IsInvalid_Code as INVALID,
						dbfinv.PermRegion_Code as REG_PERM,
						CASE WHEN PS.Server_pid = 0 THEN 'Да' ELSE 'Нет' END as BDZ
					";
				} else {
					$query .= "
						EPLS.EvnPLStom_id as EvnPLStom_id,
						EPLS.Person_id as Person_id,
						EPLS.PersonEvn_id as PersonEvn_id,
						EPLS.Server_id as Server_id,
						ISNULL(EPLS.EvnPLStom_IsTransit, 1) as EvnPLStom_IsTransit,
						RTRIM(EPLS.EvnPLStom_NumCard) as EvnPLStom_NumCard,
						CNT.EvnPLStom_VizitCount as EvnPLStom_VizitCount,
						IsFinish.YesNo_Name as EvnPLStom_IsFinish,
						RTRIM(EVPLSD.Diag_Code) + '. ' + RTRIM(EVPLSD.Diag_Name) as Diag_Name,
						RTRIM(MP.Person_Fio) as MedPersonal_Fio,
						convert(varchar(10), EPLS.EvnPLStom_setDate, 104) as EvnPLStom_setDate,
						convert(varchar(10), EPLS.EvnPLStom_disDate, 104) as EvnPLStom_disDate,
						--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
						" . $isBDZ . "
						convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
						case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
					";

					if ($this->regionNick == 'kz') {
						$query .= " ,CASE WHEN air.AISResponse_id is not null THEN 'true' ELSE 'false' END as toAis25 ";
						$query .= " ,CASE WHEN air9.AISResponse_id is not null THEN 'true' ELSE 'false' END as toAis259 ";
					}

					### ПАРАМЕТРЫ ПОИСКА С КСГ ###
					if (isset($data['EvnPLStom_KSG'])) {

						if ($data['EvnPLStom_KSG'] == 2) {
							$ksg_filter = " AND EDPLS.Mes_id is not null";

							if (isset($data['EvnPLStom_KSG_Num'])) {
								$queryParams['Mes_Code'] = $data['EvnPLStom_KSG_Num'];
								$ksg_filter .= ' AND v_MesOld.Mes_Code = :Mes_Code';
							}
						}

						if ($data['EvnPLStom_KSG'] == 1) {
							$ksg_filter = " AND EDPLS.Mes_id is null";
						}

						$filter .= "
						AND EXISTS (
							SELECT TOP 1 EDPLS.*
	  						FROM v_EvnDiagPLStom EDPLS with(nolock)
	  						LEFT JOIN v_MesOld with(nolock) ON v_MesOld.Mes_id = EDPLS.Mes_id
	  						WHERE EDPLS.EvnDiagPLStom_pid = EVPLS.EvnVizitPLStom_id
	  						$ksg_filter
						)";
					}

					if (allowPersonEncrypHIV($data['session'])) {
						$query .= "
							,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					        ,case when PEH.PersonEncrypHIV_id is null then RTRIM(ua.Address_Address) else '' end as Person_AdrReg
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(pa.Address_Address) else '' end as Person_AdrProj
						";
					} else {
						$query .= "
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
							,ua.Address_Address as Person_AdrReg
							,pa.Address_Address as Person_AdrProj
						";
                    }
					if(!empty($data['InterruptLeaveType_id'])) {
						$queryParams['InterruptLeaveType_id'] = $data['InterruptLeaveType_id'];
						$filter .= " and EPLS.InterruptLeaveType_id = :InterruptLeaveType_id";
					}
				}
				break;

			case 'EvnPS':
				$main_alias = "EPS";
				if ($dbf === true) {
					$query .= "
						EPS.EvnPS_id as EPS_COD,
						EPS.EvnPS_IsCont as IS_CONT,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnPS_setDate, 104), '')) as SETDATE,
						EPS.EvnPS_setTime as SETTIME,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnPS_disDate, 104), '')) as DISDATE,
						EPS.EvnPS_disTime as DISTIME,
						EPS.Person_id as PERS_COD,
						dbflpu.Lpu_OGRN as LPU_OGRN,
						RTRIM(EPS.EvnPS_NumCard) as NUMBER,
						dbfpa.PrehospArrive_Code as PRHARR_COD,
						dbfpd.PrehospDirect_Code as PRHDIR_COD,
						dbfpt.PrehospToxic_Code as PRHTOX_COD,
						dbfpayt.PayType_Code as PAY_COD,
						dbfprtr.PrehospTrauma_Code as PRHTRV_COD,
						dbfprtype.PrehospType_Code as PRHTYP_COD,
						dbfdorg.Org_OGRN as ORG_OGRN,
						dbflsd.LpuSection_Code as LS_COD,
						EPS.EvnDirection_Num as NUMORD,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnDirection_setDT, 104), '')) as DATEORD,
						EPS.EvnPS_CodeConv as CODCON,
						EPS.EvnPS_NumConv as NUMCON,
						EPS.EvnPS_TimeDesease as DESSTIME,
						EPS.EvnPS_HospCount as HOSPCOUNT,
						dbfoorg.Org_OGRN as OLPU_OGRN,
						EPS.EvnPS_IsUnlaw as ISUNLAW,
						EPS.EvnPS_IsUnport as ISUNPORT,
						dbfmp.MedPersonal_TabCode as MP_COD
					";
				} else {
					$ksg_query = " ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSG,";
					$kpg_query = " kpg.Mes_Code as EvnSection_KPG,";

					if ($this->getRegionNick() == 'ekb') {
						$ksg_query = " ISNULL(sksg.Mes_Code, '') + ' ' +  ISNULL(sksg.Mes_Name, '') as EvnSection_KSG,";
					}
					if ($this->getRegionNick() == 'kareliya') {
						$ksg_query = "case 
							when ksgkpg.Mes_id = ksg.Mes_id
							then ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '')
						end as EvnSection_KSG,";
						$kpg_query = "case 
							when ksgkpg.Mes_id = kpg.Mes_id 
							then ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '')
						end as EvnSection_KPG,";
					}

					$dni_query = "
					case when LpuUnitType.LpuUnitType_SysNick = 'stac'
					then datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные
					else (datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные
					end as EvnPS_KoikoDni,
					";
					if ($this->getRegionNick() == 'kz') {
						$dni_query = "
						case when LpuUnitType.LpuUnitType_SysNick = 'stac'
						then datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные
						else (
							--- refs #128752 к/дней без выходных
							DATEDIFF(DD, EPS.EvnPS_setDate, EPS.EvnPS_disDate)
							- (datediff(day, -2, EPS.EvnPS_disDate)/7-datediff(day, -1, EPS.EvnPS_setDate)/7)
							- (datediff(day, -1, EPS.EvnPS_disDate)/7-datediff(day, 0, EPS.EvnPS_setDate)/7) 							
							+ 1
						) -- дневные
						end as EvnPS_KoikoDni,
						";
					}

					$query .= "
						EPS.EvnPS_id as EvnPS_id,
						-- CONCAT(objsync.ObjectSynchronLogService_id,EPS.EvnPS_id) as Evn_sendERSB, -- закомментировано, ибо #130972
						" . ($this->regionNick == 'kz' ? " ISNULL(cast(objsync.ObjectSynchronLogService_id as varchar(16)), '') + cast(EPS.EvnPS_id as varchar(16)) as Evn_sendERSB, " : "") . "
						
						EPS.Person_id as Person_id,
						EPS.PersonEvn_id as PersonEvn_id,
						EPS.Server_id as Server_id,
						ISNULL(EPS.EvnPS_IsTransit, 1) as EvnPS_IsTransit,
						RTRIM(EPS.EvnPS_NumCard) as EvnPS_NumCard,
						convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
						convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate,
						ISNULL(LStmp.LpuSection_Name, '') as LpuSection_Name,
						ISNULL(Dtmp.Diag_FullName, DP.Diag_FullName) as Diag_Name,
						-- поскольку в одном КВС не может быть движений по круглосуточным и дневным стационарам вместе (поскольку это делается через перевод и создание новой карты)
						-- то подсчет количества койкодней реализуем так (с) Night, 2011-06-22
						" . $dni_query . "
						--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
						" . $isBDZ . "
						dbfpayt.PayType_Name as PayType_Name, --Вид оплаты
						CASE
							WHEN LT.LeaveType_Name is not null THEN LT.LeaveType_Name
							WHEN EPS.PrehospWaifRefuseCause_id > 0 THEN pwrc.PrehospWaifRefuseCause_Name
							ELSE ''
						END as LeaveType_Name,
						LT.LeaveType_Code,
						CASE WHEN DeathSvid.DeathSvid_id is null then 'false'
						else 'true'
						end as DeadSvid,
						EPS.PrehospWaifRefuseCause_id,
						" . $ksg_query . "
						ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSGKPG,
						" . $kpg_query . "
						convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
						case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
						" . ($this->regionNick == 'kz' ? ",epsl.Hospitalization_id" : "") . "
					";

					if (allowPersonEncrypHIV($data['session'])) {
						$query .= "
							,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
							,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
							,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
							,case when PEH.PersonEncrypHIV_id is null then COALESCE(convert(varchar(10), DeathSvid.DeathSvid_DeathDate,104),convert(varchar(10), EvnDie.EvnDie_setDate, 104),convert(varchar(10), PS.Person_DeadDT, 104),'') else null end as Person_deadDT
						";
					} else {
						$query .= "
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,COALESCE(convert(varchar(10), DeathSvid.DeathSvid_DeathDate,104),convert(varchar(10), EvnDie.EvnDie_setDate, 104),convert(varchar(10), PS.Person_DeadDT, 104),'') as Person_deadDT
						";
					}
					//#155391 фильтр по фед.сервису / EvnPS
					$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as fedservice_iemk";
					if(isset($data['Service1EvnStatus_id'])) {
						$filter.= " AND SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
						$queryParams['Service1EvnStatus_id'] = $data['Service1EvnStatus_id'];
					}
				}
				break;

			case 'EvnRecept':
				$main_alias = "ER";
				$query .= "
					TOP 10000
					ER.EvnRecept_id,
					ER.Person_id,
					ER.PersonEvn_id,
					ER.Server_id,
					Lpu.Lpu_Nick,
					Lpu.Lpu_id,
					ER.Drug_id,
					ER.Drug_rlsid,
					ER.DrugComplexMnn_id,
					ER.ReceptDelayType_id,
					ER.OrgFarmacy_oid,
					case 
						when ER.ReceptDelayType_id in ('1','2','3','5') then ER_RDT.ReceptDelayType_Name 
						when (
							ER.ReceptDelayType_id is null 
							and (
								(RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value) >= @getDT)) 
								or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
								)
							and ((ER.EvnRecept_otpDT is null) and (ER.EvnRecept_obrDT is null)) 
							) then 'Выписан'
							-- and not exists(select top 1 RO.ReceptOtov_id from v_ReceptOtov RO with (nolock) where RO.EvnRecept_id = ER.EvnRecept_id)) then 'Выписан'
						when ((RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value) < @getDT)) or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30)) < @getDT))) then 'Просрочен'
						else ''
					end as ReceptDelayType_Name,
					(select top 1 Org_Name from v_OrgFarmacy ER_OF with(nolock) where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid) as OrgFarmacy_oName,
					(
						case when
							ER.ReceptDelayType_id  > 0
						then
							/*
							(select top 1 ReceptDelayType_Name from ReceptDelayType ER_RDT with(nolock) where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id) + isnull(' '+(select top 1 Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid),'')
							*/
							(select ReceptDelayType_Name from ReceptDelayType ER_RDT with(nolock) where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id) + isnull(' '+isnull((select Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid), '') + case when ER.ReceptDelayType_id = 3 and Wr.ReceptWrong_Decr is not null then ' (' + Wr.ReceptWrong_Decr + ')' else '' end, '')
						else
							''
						end
					) as Delay_info,
					convert(varchar(10), ER.EvnRecept_setDate,104) as EvnRecept_setDate,
					convert(varchar(10), COALESCE(ER.EvnRecept_otpDT,RecOt.EvnRecept_otpDate),104) as EvnRecept_otpDate,
					convert(varchar(10), COALESCE(ER.EvnRecept_obrDT,RecOt.EvnRecept_obrDate),104) as EvnRecept_obrDate,
					case when ((ER.EvnRecept_otpDT is not null) and (ER.EvnRecept_obrDT is not null)) then datediff(day,ER.EvnRecept_obrDT,ER.EvnRecept_otpDT) else null end as ServePeriod,
					RTRIM(wdcit.WhsDocumentCostItemType_Name) as WhsDocumentCostItemType_Name,
					RTRIM(drugFin.DrugFinance_Name) as DrugFinance_Name,
					ISNULL(RecOtovSum.recSum,0) as ReceptOtovSum,
					RTRIM(ER.EvnRecept_Ser) as EvnRecept_Ser,
					RTRIM(ER.EvnRecept_Num) as EvnRecept_Num,
					ROUND(ER.EvnRecept_Kolvo, 3) as EvnRecept_Kolvo,
					/*RTRIM(ERMP.Person_Fio) as MedPersonal_Fio,*/
					(RTRIM(ERMP.Person_Fio) + ' (' + ISNULL(Lpu.Lpu_Nick,'') + ')') as MedPersonal_Fio,
					RTRIM(COALESCE(ERDrugRls.Drug_Name, ERDrug.Drug_Name, DCM.DrugComplexMnn_RusName, ER.EvnRecept_ExtempContents)) as Drug_Name,
					RTRIM(DrugNomen.DrugNomen_Code) as DrugNomen_Code,
					--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					" . $isBDZ . "
					--CASE WHEN ER.EvnRecept_IsSigned = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsSigned,
					CASE WHEN (ER.EvnRecept_IsSigned = 2 or (ER.pmUser_signID is not null and ER.EvnRecept_signDT is not null)) THEN 'true' ELSE 'false' END as EvnRecept_IsSigned,
					CASE WHEN ER.EvnRecept_IsPrinted = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsPrinted,
					RecF.ReceptForm_id,
					RecF.ReceptForm_Code,
					RecT.ReceptType_Code,
					RecT.ReceptType_Name,
					CASE WHEN ER_RDT.ReceptDelayType_Code = 4 THEN 'true' ELSE 'false' END as Recept_MarkDeleted,
					ER.ReceptRemoveCauseType_id,
					pmUC.PMUser_Name,
					isnull(wdcit.MorbusType_id, 1) as MorbusType_id,
					DocUc.DocumentUc_id,
					-- Дата окончания срока действия рецепта
					case
						when ER.ReceptDelayType_id IS Not null then null -- Рецепт имеет статус
						when RV.ReceptValid_Code = 1 then convert(varchar(10), dateadd(month, 1, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = 'Месяц'
						when RV.ReceptValid_Code = 2 then convert(varchar(10), dateadd(month, 3, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = 'Три месяца'
						when RV.ReceptValid_Code = 3 then convert(varchar(10), dateadd(day, 14, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = '14 дней'
						when RV.ReceptValid_Code = 4 then convert(varchar(10), dateadd(day, 5, ER.EvnRecept_setDate), 104) -- when ReceptValid_Name = '5 дней'
						when RV.ReceptValid_Code = 5 then convert(varchar(10), dateadd(month, 2, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = 'Два месяца'
						when RV.ReceptValid_Code = 7 then convert(varchar(10), dateadd(day, 10, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = '10 дней'
						when RV.ReceptValid_Code = 8 then convert(varchar(10), dateadd(day, 60, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = '60 дней
						when RV.ReceptValid_Code = 9 then convert(varchar(10), dateadd(day, 30, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = '30 дней'
						when RV.ReceptValid_Code = 10 then convert(varchar(10), dateadd(day, 90, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = '90 дней'
						when RV.ReceptValid_Code = 11 then convert(varchar(10), dateadd(day, 15, ER.EvnRecept_setDate), 104) -- ReceptValid_Name = '15 дней'
						else
							 convert(varchar(10), ER.EvnRecept_setDate,104)
					end EvnRecept_DateCtrl,
					-- Превышение срока действия рецепта
					case
						when ER.ReceptDelayType_id IS Not null then 0 -- Рецепт имеет статус
						when RV.ReceptValid_Code = 1 and dateadd(month, 1, ER.EvnRecept_setDate) < GETDATE() then 1 -- ReceptValid_Name = 'Месяц'
						when RV.ReceptValid_Code = 2 and dateadd(month, 3, ER.EvnRecept_setDate) < GETDATE() then 1 -- ReceptValid_Name = 'Три месяца'
						when RV.ReceptValid_Code = 3 and dateadd(day, 14, ER.EvnRecept_setDate) < GETDATE() then 1 -- ReceptValid_Name = '14 дней'
						when RV.ReceptValid_Code = 4 and dateadd(day, 5, ER.EvnRecept_setDate)  < GETDATE() then 1 -- when ReceptValid_Name = '5 дней'
						when RV.ReceptValid_Code = 5 and dateadd(month, 2, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = 'Два месяца'
						when RV.ReceptValid_Code = 7 and dateadd(day, 10, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '10 дней'
						when RV.ReceptValid_Code = 8 and dateadd(day, 60, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '60 дней'
						when RV.ReceptValid_Code = 9 and dateadd(day, 30, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '30 дней'
						when RV.ReceptValid_Code = 10 and dateadd(day, 90, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '90 дней'
						when RV.ReceptValid_Code = 11 and dateadd(day, 15, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '15 дней'
						else 0
					end EvnRecept_Shelf,
					case when Wr.ReceptWrong_id is not null then 1 else 0 end as EvnRecept_IsWrong,
					case when ((RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value) >= @getDT)) or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))) then 0 else 1 end as inValidRecept
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}
				break;

			case 'EvnReceptGeneral':
				$main_alias = "ERG";
				$query .= "
					TOP 10000
					ERG.EvnReceptGeneral_id as EvnRecept_id,
					ERG.EvnReceptGeneral_id,
					ERG.Person_id,
					ERG.PersonEvn_id,
					ERG.Server_id,
					ERG.Drug_id,
					ERG.Drug_rlsid,
					ERG.DrugComplexMnn_id,
					ERG.ReceptDelayType_id,
					ERG.OrgFarmacy_oid,
					case 
						when ERG.ReceptDelayType_id in ('1','2','3','5') then ER_RDT.ReceptDelayType_Name 
						when (
							ERG.ReceptDelayType_id is null 
							and (
								(RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value) >= @getDT)) 
								or (RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
								)
							and ((ERG.EvnReceptGeneral_otpDT is null) and (ERG.EvnReceptGeneral_obrDT is null)) 
							) then 'Выписан'
							-- and not exists(select top 1 RO.ReceptOtov_id from v_ReceptOtov RO with (nolock) where RO.EvnRecept_id = ER.EvnRecept_id)) then 'Выписан'
						when ((RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value) < @getDT)) or (RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30)) < @getDT))) then 'Просрочен'
						else ''
					end as ReceptDelayType_Name,
					-- (select top 1 ReceptDelayType_Name from ReceptDelayType ER_RDT with(nolock) where ER_RDT.ReceptDelayType_id = ERG.ReceptDelayType_id) as ReceptDelayType_Name,
					(select top 1 Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ERG.OrgFarmacy_oid) as OrgFarmacy_oName,
					(
						case when
							ERG.ReceptDelayType_id  > 0
						then
							(select top 1 ReceptDelayType_Name from ReceptDelayType ER_RDT with(nolock) where ER_RDT.ReceptDelayType_id = ERG.ReceptDelayType_id) + isnull(' '+(select top 1 Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ERG.OrgFarmacy_oid),'')
						else
							''
						end
					) as Delay_info,
					convert(varchar(10), ERG.EvnReceptGeneral_setDate,104) as EvnRecept_setDate,
					convert(varchar(10), COALESCE(ERG.EvnReceptGeneral_otpDT,RecOt.EvnRecept_otpDate),104) as EvnRecept_otpDate,
					convert(varchar(10), COALESCE(ERG.EvnReceptGeneral_obrDT,RecOt.EvnRecept_obrDate),104) as EvnRecept_obrDate,
					case when (ERG.EvnReceptGeneral_otpDT is not null and ERG.EvnReceptGeneral_obrDT is not null) then datediff(day,ERG.EvnReceptGeneral_obrDT,ERG.EvnReceptGeneral_otpDT) else null end as ServePeriod,
					RTRIM(wdcit.WhsDocumentCostItemType_Name) as WhsDocumentCostItemType_Name,
					RTRIM(drugFin.DrugFinance_Name) as DrugFinance_Name,
					ISNULL(RecOtovSum.recSum,0) as ReceptOtovSum,
					RTRIM(ERG.EvnReceptGeneral_Ser) as EvnRecept_Ser,
					RTRIM(ERG.EvnReceptGeneral_Num) as EvnRecept_Num,
					ROUND(ERG.EvnReceptGeneral_Kolvo, 3) as EvnRecept_Kolvo,
					RTRIM(ERMP.Person_Fio) as MedPersonal_Fio,
					RTRIM(COALESCE(ERDrugRls.Drug_Name, ERDrug.Drug_Name, DCM.DrugComplexMnn_RusName, ERG.EvnReceptGeneral_ExtempContents)) as Drug_Name,
					RTRIM(DrugNomen.DrugNomen_Code) as DrugNomen_Code,
					" . $isBDZ . "
					--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					CASE WHEN ERG.EvnReceptGeneral_IsSigned = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsSigned,
					RecF.ReceptForm_id,
					RecF.ReceptForm_Code,
					ERG.ReceptRemoveCauseType_id,
					isnull(wdcit.MorbusType_id, 1) as MorbusType_id,
					DocUc.DocumentUc_id,
					case when ((RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value) >= @getDT)) or (RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))) then 0 else 1 end as inValidRecept
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}
				break;

			case 'EvnUslugaPar':


				$accessType = 'EUP.Lpu_id = :Lpu_id';
				//$accessType .= ' and (ED.EvnDirection_IsReceive = 2 OR (ED.MedService_id IS NULL and not exists(select top 1 EvnFuncRequest_id from v_EvnFuncRequest (nolock) where EvnFuncRequest_pid = EUP.EvnUslugaPar_id)))'; // не даём редактировать услуги связанные с направлением в лабораторию и с заявкой ФД
				if (!isSuperAdmin() && empty($data['session']['isMedStatUser']) && !empty($data['session']['medpersonal_id'])) {
					$accessType .= ' and ISNULL(EUP.MedPersonal_id,:user_MedPersonal_id) = :user_MedPersonal_id';
					$queryParams['user_MedPersonal_id'] = $data['session']['medpersonal_id'];
				}
				if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
					$result_deviation = "doc.XmlTemplate_HtmlTemplate,";
				} else {
					$result_deviation = "";
				}

				$main_alias = "EUP";


				$query .= "
					case when {$accessType} then 'edit' else 'view' end as accessType,
					doc.EvnXml_id,
					{$result_deviation}
					EUP.EvnUslugaPar_id,
					EvnUslugaPar_pid,
					EUP.Person_id,
					EUP.PersonEvn_id,
					EUP.Server_id,
					convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
					isnull(EUP.EvnUslugaPar_IsSigned, 1) as EvnUslugaPar_IsSigned,
					RTRIM(LS.LpuSection_Name) as LpuSection_Name,
					RTRIM(MP.Person_Fio) as MedPersonal_Fio,
					lpu.Lpu_Name,
					CASE
					WHEN EUP.UslugaComplex_id IS NULL THEN RTRIM(Usluga.Usluga_Code)
					ELSE RTRIM(UslugaComplex.UslugaComplex_Code)
					END as Usluga_Code,
					CASE
					WHEN EUP.UslugaComplex_id IS NULL THEN RTRIM(Usluga.Usluga_Name)
					ELSE RTRIM(UslugaComplex.UslugaComplex_Name)
					END as Usluga_Name,
					RTRIM(PT.PayType_Name) as PayType_Name,
					isnull(EUP.EvnUslugaPar_Kolvo, '') as EvnUslugaPar_Kolvo,
					isnull(PostMed.PostMed_Name, '') as PostMed_Name,
					convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
					case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText,
					coalesce(Org.Org_Nick, LD_sid.Lpu_nick, v_Lpu_org_1.Lpu_Nick, v_Lpu_org_2.Lpu_Nick, Org_3.Org_Nick) as Referral_Org_Nick, -- направившая МО
					LSD = (			-- это отделение, направившее пациента
						select
							LpuSection_Name
						from
							v_LpuSection (nolock)
						where
							LpuSection_id = EUP.LpuSection_did
					)
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then dbo.Age2(PS.Person_BirthDay, @getDT) else null end as Person_Age
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,dbo.Age2(PS.Person_BirthDay, @getDT)  as Person_Age
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}
				if ($this->regionNick == 'kz') {
					$query .= " ,CASE WHEN air.AISResponse_id is not null THEN 'true' ELSE 'false' END as toAis25 ";
					$query .= " ,CASE WHEN air9.AISResponse_id is not null THEN 'true' ELSE 'false' END as toAis259 ";
				}
				break;

			case 'WorkPlacePolkaReg':
				$main_alias = "PC";
				$query .= "
					PC.PersonCard_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					CASE WHEN PS.Person_DeadDT is not null  THEN 'true' ELSE 'false' END as Person_IsDead,
					PC.PersonCard_Code as PersonCard_Code,
					ISNULL('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' + CAST(PS.Person_id as varchar) + '});''>'+ case when PLS.PolisType_id = 4 and ISNULL(PS.Person_EdNum, '') != '' then PS.Person_EdNum else ISNULL(PS.Polis_Ser, '') + ' ' + ISNULL(PS.Polis_Num, '') end +'</a>','') as Person_PolisInfo,
					(select isnull(Person_Inn,'') from v_PersonState with(nolock) where Person_id = ps.Person_id) as Person_Inn,
					case
					    when [dbo].[getPersonPhones](PS.Person_id, '<br />') != '' then ISNULL('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(PS.Person_id as varchar) + '});''>'+ [dbo].[getPersonPhones](PS.Person_id, '<br />') +'</a>','')
					    else '<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(PS.Person_id as varchar) + '});''>'+ 'Отсутствует' +'</a>'
                    end as Person_Phone,
					case when PS.Person_id is not null then dbo.Age2(PS.Person_BirthDay, @getDT) end as Person_Age,
					ISNULL(AttachLpu.Lpu_Nick, 'Не прикреплен') as AttachLpu_Name,
					ISNULL(AttachLpu.Lpu_id, 0) as AttachLpu_id,
					convert(varchar(10), cast(PC.PersonCard_begDate as datetime), 104) as PersonCard_begDate,
					convert(varchar(10), cast(PC.PersonCard_endDate as datetime), 104) as PersonCard_endDate,
					PC.LpuAttachType_Name,
					PC.LpuRegionType_Name,
					LR.LpuRegion_Name,
					CASE WHEN ISNULL(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as PersonCard_IsAttachCondit,
					CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
					CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
					CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_NextYearRefuse,
					CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
					CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
					--CASE WHEN (PS.Person_Is7Noz = 1 and PD.Sickness_id IN (1,3,4,5,6,7,8)) THEN 'true' ELSE 'false' END as Person_Is7Noz,

					CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE
						CASE WHEN disp.OwnLpu is not null THEN 'gray'
										  ELSE 'false'
						END
					END as Person_Is7Noz,
					" . $isBDZ . "
					--CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					isnull(paddr.Address_Nick, paddr.Address_Address) as Person_PAddress,
					isnull(uaddr.Address_Nick, uaddr.Address_Address) as Person_UAddress
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}
				break;
			case 'PersonCard':
				$main_alias = "PC";
				$query .= "
					PC.PersonCard_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					CASE WHEN PS.Person_IsDead = 2 THEN 'true' ELSE 'false' END as Person_IsDead,
					PC.PersonCard_Code as PersonCard_Code,
					[dbo].[getPersonPhones](PS.Person_id, '<br />') as Person_Phone,
					ISNULL(AttachLpu.Lpu_Nick, 'Не прикреплен') as AttachLpu_Name,
					convert(varchar(10), cast(PC.PersonCard_begDate as datetime), 104) as PersonCard_begDate,
					convert(varchar(10), cast(PC.PersonCard_endDate as datetime), 104) as PersonCard_endDate,
					ISNULL(PC.LpuAttachType_id,'') as LpuAttachType_id,
					PC.LpuAttachType_Name,
					PC.LpuRegionType_Name,
					LR.LpuRegion_Name,
					ISNULL(LR_Fap.LpuRegion_Name,'') as LpuRegion_FapName,
					PACLT.AmbulatCardLocatType_Name,
					PACLT.PersonAmbulatCard_id,
					CASE WHEN ISNULL(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as PersonCard_IsAttachCondit,
					CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
					CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
					CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_NextYearRefuse,
					CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
					CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
					--CASE WHEN (PS.Person_Is7Noz = 1 and PD.Sickness_id IN (1,3,4,5,6,7,8)) THEN 'true' ELSE 'false' END as Person_Is7Noz,

					CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE
						CASE WHEN disp.OwnLpu is not null THEN 'gray'
										  ELSE 'false'
						END
					END as Person_Is7Noz,
					" . $isBDZ . "
					--CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					isnull(paddr.Address_Nick, paddr.Address_Address) as Person_PAddress,
					isnull(uaddr.Address_Nick, uaddr.Address_Address) as Person_UAddress
				";
				if (!empty($data['dontShowUnknowns'])) {// #158923 показывать ли неизвестных в РПН: Поиск
					$filter .= ' and isnull(PS.Person_IsUnknown,1) != 2 ';
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}

				if($this->getRegionNick()=='ufa' and $data['hasObrTalonMse']=='on') {
					$query .= ",OBTMSE.InvalidGroupType_Name as MseInvalidGroupType_Name, OBTMSE.Diag_Code as MseDiag_Code";
				}
				break;

			case 'PersonCallCenter':
				$main_alias = "PS";
				$query .= "
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					CASE WHEN PS.Person_DeadDT is not null  THEN 'true' ELSE 'false' END as Person_IsDead,
					case when PersonCard.Lpu_id = :Lpu_id then PersonCard.PersonCard_Code else null end as PersonCard_Code,
					ISNULL('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' + CAST(PS.Person_id as varchar) + '});''>'+ case when PLS.PolisType_id = 4 and ISNULL(PS.Person_EdNum, '') != '' then PS.Person_EdNum else ISNULL(PS.Polis_Ser, '') + ' ' + ISNULL(PS.Polis_Num, '') end +'</a>','') as Person_PolisInfo,
					case
					    when [dbo].[getPersonPhones](PS.Person_id, '<br />') != '' then ISNULL('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(PS.Person_id as varchar) + '});''>'+ [dbo].[getPersonPhones](PS.Person_id, '<br />') +'</a>','')
					    else '<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(PS.Person_id as varchar) + '});''>'+ 'Отсутствует' +'</a>'
                    end as Person_Phone,
					--[dbo].[getPersonPhones](PS.Person_id, '<br />') as Person_Phone,
					case when PS.Person_id is not null then dbo.Age2(PS.Person_BirthDay, @getDT) end as Person_Age,
					ISNULL(AttachLpu.Lpu_Nick, 'Не прикреплен') as AttachLpu_Name,
					convert(varchar(10), cast(PersonCard.PersonCard_begDate as datetime), 104) as PersonCard_begDate,
					convert(varchar(10), cast(PersonCard.PersonCard_endDate as datetime), 104) as PersonCard_endDate,
					PersonCard.LpuAttachType_Name,
					PersonCard.LpuRegionType_Name,
					LR.LpuRegion_Name,
					ISNULL(LR_Fap.LpuRegion_Name,'') as LpuRegion_FapName,
					NA.NewslatterAccept_id,
					ISNULL(convert(varchar(11), NA.NewslatterAccept_begDate, 104), 'Отсутствует') as NewslatterAccept,
					CASE
						WHEN PersonCard.PersonCard_IsAttachCondit = 1 then 'false'
						WHEN PersonCard.PersonCard_IsAttachCondit = 2 then 'true'
						ELSE null
					end as PersonCard_IsAttachCondit,
					CASE WHEN PersonCard.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
					CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
					CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_NextYearRefuse,
					CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
					CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
					CASE
						WHEN disp.OwnLpu = 1 THEN 'true'
						WHEN disp.OwnLpu is not null THEN 'gray'
						ELSE 'false'
					END as Person_Is7Noz,
					" . $isBDZ . "
					--CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					isnull(paddr.Address_Nick, paddr.Address_Address) as Person_PAddress,
					isnull(uaddr.Address_Nick, uaddr.Address_Address) as Person_UAddress
				";
				if (!empty($data['dontShowUnknowns'])) {// #158923 показывать ли неизвестных в АРМ оператора call-центра
					$filter .= ' and isnull(PS.Person_IsUnknown,1) != 2 ';
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}
				break;

			/* @todo: Журнал движения РПН. Просмотр Деталей. от select до from
			 *
			 */
			case 'PersonCardStateDetail':
				$main_alias = "PCSD";
				$query .= "
					PCSD.PersonCard_Code,
					PCSD.PersonCard_id,
					PCSD.Person_id,
					PCSD.Server_id,
					rtrim(pcc.Person_SurName) as Person_Surname,
					rtrim(pcc.Person_FirName) as Person_Firname,
					rtrim(pcc.Person_SecName) as Person_Secname,
					convert(varchar,cast(pcc.Person_BirthDay as datetime),104) as Person_BirthDay,
					convert(varchar,cast(PCSD.PersonCard_begDate as datetime),104) as PersonCard_begDate,
					convert(varchar,cast(PCSD.PersonCard_endDate as datetime),104) as PersonCard_endDate,
					lrt.LpuRegionType_Name,
					lr.LpuRegion_Name,
					isnull(ccc.CardCloseCause_Name, '') as CardCloseCause_Name,
					case when isnull(PCSD.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as PersonCard_IsAttachCondit,
					isnull(attcard.LpuRegion_Name, '') as ActiveLpuRegion_Name,
					isnull(rtrim(attcard.Lpu_Nick), '') as ActiveLpu_Nick,
					isnull(rtrim(Address.Address_Nick), '') as PAddress_Address,
					isnull(rtrim(Address1.Address_Nick), '') as UAddress_Address,
					isnull(rtrim(dmsOrgSmo.OrgSMO_Nick), '') as dmsOrgSmo_Nick,
					isnull(rtrim(omsOrgSmo.OrgSMO_Nick), '') as omsOrgSmo_Nick,
					case
					    when ps.Server_pid = 0 and Pols.Polis_id is not null then 'true'
					    else 'false'
					end as isBDZ
				";
				break;

			case 'PersonDisp':
				$main_alias = "PD";
				$query .= "
					PS.Person_SurName AS SurName,
					PS.Person_FirName AS FirName,
					PS.Person_SecName AS SecName,
					PD.PersonDisp_id,
					PD.Person_id,
					PD.Server_id,
					convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate,
					convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate,
					convert(varchar(10), isnull(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate), 104) as PersonDisp_NextDate,
					convert(
						varchar(10),
						case
							when LD.PersonDisp_LastDate is not null and lapdv.PersonDispVizit_NextFactDate is not null
							then
								case when LD.PersonDisp_LastDate > lapdv.PersonDispVizit_NextFactDate then LD.PersonDisp_LastDate else lapdv.PersonDispVizit_NextFactDate end
							else isnull(lapdv.PersonDispVizit_NextFactDate,LD.PersonDisp_LastDate)
						end,
						104
					) as PersonDisp_LastDate,
					dg1.Diag_Code,
					mp1.Person_Fio as MedPersonal_FIO,
					mph_last.MedPersonal_FIO_last as MedPersonalHist_FIO,
					lpus1.LpuSection_Name as LpuSection_Name,
					scks.Sickness_Name,
					rtrim(lpu1.Lpu_Nick) as Lpu_Nick,
					CASE WHEN PD.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as IsOurLpu,
					CASE WHEN noz.isnoz = 1
					THEN 'true' ELSE
						 CASE WHEN noz.isnoz is not null
							THEN 'gray'
							ELSE 'false'
						 END
					END as Is7Noz,
					ISNULL(PCA.LpuRegion_Name,'') as LpuRegion_Name
				";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
					";
				}
				break;

			case 'PersonPrivilege':
				$main_alias = "PP";
				$query .= "
					PP.Lpu_id,
					PS.Server_id,
					PS.Lpu_id as Lpu_did,
					PS.PersonEvn_id,
					PS.Person_id,
					PP.PersonPrivilege_id,
					PT.PrivilegeType_id,
					RF.ReceptFinance_id,
					RF.ReceptFinance_Code,
					RTRIM(PT.PrivilegeType_Code) as PrivilegeType_Code,
					RTRIM(isnull(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar))) as PrivilegeType_VCode,
					convert(varchar(10), PP.PersonPrivilege_begDate, 104) as Privilege_begDate,
					convert(varchar(10), PP.PersonPrivilege_endDate, 104) as Privilege_endDate,
					CASE WHEN PS.Person_IsRefuse = 1 and PT_WDCIT.WhsDocumentCostItemType_Nick = 'fl' THEN 'true' ELSE 'false' END as Person_IsRefuse,
					CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
					CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
					CASE WHEN PS.Person_Is7Noz = 1 THEN 'true' ELSE 'false' END as Person_Is7Noz,
					" . $isBDZ . "
					--CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					ISNULL(PUAdd.Address_Nick, PUAdd.Address_Address) as Person_Address,
					(
						convert(varchar(10), PP.PersonPrivilege_delDT, 104)+' '+convert(varchar(5), PP.PersonPrivilege_delDT, 108)+', '+
						rtrim(UserDel.PMUser_Login)+', '+rtrim(UserDel.PMUser_Name)
					) as PersonPrivilege_deletedInfo,
					isnull(PrivCT.PrivilegeCloseType_Name, '') as PrivilegeCloseType_Name,
					isnull(DocPriv.DocumentPrivilege_Data, '') as DocumentPrivilege_Data
				";
				if ($data['session']['region']['nick'] == 'krym')
					$query .= ",PCardChecks.cntPC as cntPC";
				else
					$query .= ",0 as cntPC";

				if ($this->regionNick == 'kz') {
					$query .= ",RTRIM(PT.PrivilegeType_Name) + isnull('<br>' + SCPT.SubCategoryPrivType_Name, '') as PrivilegeType_Name";
				} else {
					$query .= ",RTRIM(PT.PrivilegeType_Name) + isnull('<br>Диагноз: ' + Diag.Diag_Code + ' ' + Diag.Diag_Name, '') as PrivilegeType_Name";
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
						,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
						,case when PEH.PersonEncrypHIV_id is null then dbo.Age2(PS.Person_BirthDay, @getDT) else null end as Person_Age
						,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_deadDT, 104) else null end as Person_deadDT
					";
				} else {
					$query .= "
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,dbo.Age2(PS.Person_BirthDay, @getDT) as Person_Age
						,convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT
					";
				}
				break;

			// Регистр ветеранчегов
			case 'PersonPrivilegeWOW':
				$main_alias = "PPW";
				$query .= "
					PPW.PersonPrivilegeWOW_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					UAdd.Address_Nick as ua_name,
					PAdd.Address_Nick as pa_name,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					PTW.PrivilegeTypeWow_id,
					PTW.PrivilegeTypeWOW_Name,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday
					--isnull(rtrim(otherddlpu.Lpu_Nick), '') as OnDispInOtherLpu,
					--epldd1.EvnPLDispDop_id,
					--CASE WHEN epldd1.EvnPLDispDop_id is null THEN 'false' ELSE 'true' END as ExistsDDPL
				";
				break;
			// Скрининг населения 60+

			case 'RegisterSixtyPlus':
				$query .= "
					RPlus.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					PS.Sex_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					DATEDIFF (YY, PS.Person_BirthDay, @getDT) as Person_Age,
					RPlus.RegisterSixtyPlus_IMTMeasure,
					RPlus.RegisterSixtyPlus_CholesterolMeasure as RegisterSixtyPlus_CholesterolMeasure,
					RPlus.RegisterSixtyPlus_GlucoseMeasure as RegisterSixtyPlus_GlucoseMeasure,
					convert(varchar(10), RPlus.RegisterSixtyPlus_OAKsetDate, 104) as RegisterSixtyPlus_OAKsetDate,
					convert(varchar(10), RPlus.RegisterSixtyPlus_OAMsetDate, 104) as RegisterSixtyPlus_OAMsetDate,
					convert(varchar(10), RPlus.RegisterSixtyPlus_FluorographysetDate, 104) as RegisterSixtyPlus_FluorographysetDate,
					convert(varchar(10), RPlus.RegisterSixtyPlus_EKGsetDate, 104) as RegisterSixtyPlus_EKGsetDate,
					convert(varchar(10), RPlus.RegisterSixtyPlus_OnkoProfileDtBeg, 104) as RegisterSixtyPlus_OnkoProfileDtBeg,
					RPlus.RegisterSixtyPlus_OnkoControlIsNeeded,
					RPlus.RegisterSixtyPlus_isSetProfileBSK,
					RPlus.RegisterSixtyPlus_isSetProfileONMK,
					RPlus.RegisterSixtyPlus_isSetProfileOKS,
					RPlus.RegisterSixtyPlus_isSetProfileZNO,
					RPlus.RegisterSixtyPlus_isSetProfileDiabetes,
					RPlus.RegisterSixtyPlus_isSetPersonDisp,
					IGT.InvalidGroupType_Name,
					RPlus.Measure_id,
					isnull(rg.LpuRegionType_Name + ' №' + replicate(' ',2-len(rg.LpuRegion_Name)) + rg.LpuRegion_Name, ' ') as uch,
					LpuRegionFap.LpuRegion_Name as LpuRegion_fapid,
					LpuRegionFap.LpuRegion_Descr";

				break;

			case 'EvnPLWOW':
				$main_alias = "EPW";
				$query .= "
					EPW.EvnPLWOW_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					PLS.Polis_Ser,
					PLS.Polis_Num,
					PTW.PrivilegeTypeWow_id,
					PTW.PrivilegeTypeWOW_Name,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					convert(varchar(10), EPW.EvnPLWOW_setDate, 104) as EvnPLWOW_setDate,
					convert(varchar(10), EPW.EvnPLWOW_disDate, 104) as EvnPLWOW_disDate,
					EPW.EvnPLWOW_VizitCount as EvnPLWOW_VizitCount,
					YesNo_Name as EvnPLWOW_IsFinish
				";
				break;

			case 'EvnDtpWound':
				$main_alias = "EDW";
				$query .= "
					EDW.EvnDtpWound_id as EvnDtpWound_id,
					EDW.Person_id as Person_id,
					EDW.PersonEvn_id as PersonEvn_id,
					EDW.Server_id as Server_id,
					convert(varchar(10), EDW.EvnDtpWound_setDate, 104) as EvnDtpWound_setDate,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				";
				break;

			case 'EvnDtpDeath':
				$main_alias = "EDD";
				$query .= "
					EDD.EvnDtpDeath_id as EvnDtpDeath_id,
					EDD.Person_id as Person_id,
					EDD.PersonEvn_id as PersonEvn_id,
					EDD.Server_id as Server_id,
					convert(varchar(10), EDD.EvnDtpDeath_setDate, 104) as EvnDtpDeath_setDate,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					RTRIM(LTRIM(ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, ''))) as Person_Fio,
					sex.Sex_Name as Person_Sex,
					diag.Diag_Name as DiagDeath_Name,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), EDD.EvnDtpDeath_DeathDate, 104) as EvnDtpDeath_DeathDate
				";
				break;

			case 'OrphanRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyOrphan_id as EvnNotifyBase_id,
					M.Morbus_id,
					MO.MorbusOrphan_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'ACSRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyBase_id as EvnNotifyBase_id,
					M.Morbus_id,
					MA.MorbusACS_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					LpuAdd.Lpu_Nick as LpuAdd_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'CrazyRegistry':
				$main_alias = 'PR';
				$region = ($this->getRegionNick() == 'ufa') ? '(1=1)' : '(1=2)';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyCrazy_id as EvnNotifyCrazy_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusCrazy_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu2.Lpu_Nick as Lpu2_Nick,
					isnull(Diag.diag_FullName, PRDiag.diag_FullName) as Diag_Name,
					case when ' . $region . ' then PROUT.PersonRegisterOutCause_id else CCEST.CrazyCauseEndSurveyType_id end as PersonRegisterOutCause_id,
					case when ' . $region . ' then PROUT.PersonRegisterOutCause_Name else CCEST.CrazyCauseEndSurveyType_Name end as PersonRegisterOutCause_Name,
					--PROUT.PersonRegisterOutCause_id,
					--PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					Lpu.Lpu_Nick as Lpu_Nick
				';
				break;
			case 'NarkoRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					isnull(EN.EvnNotifyNarco_id,EC.EvnNotifyCrazy_id) as EvnNotifyCrazy_id,
					isnull(isnull(EN.Morbus_id,EC.Morbus_id),MO.Morbus_id) as Morbus_id,
					MO.MorbusCrazy_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu2.Lpu_Nick as Lpu2_Nick,
					isnull(Diag.diag_FullName, PRDiag.diag_FullName) as Diag_Name,
					CCEST.CrazyCauseEndSurveyType_id as PersonRegisterOutCause_id,
					CCEST.CrazyCauseEndSurveyType_Name as PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					Lpu.Lpu_Nick as Lpu_Nick
				';
				break;
			case 'PersonRegisterBase':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PRT.PersonRegisterType_SysNick,
					rtrim(MT.MorbusType_SysNick) as MorbusType_SysNick,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.EvnNotifyBase_id,
					PR.Morbus_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					LpuIns.Lpu_Nick as Lpu_insNick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'PalliatRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					MO.MorbusPalliat_id,
					PRT.PersonRegisterType_SysNick,
					rtrim(MT.MorbusType_SysNick) as MorbusType_SysNick,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.EvnNotifyBase_id,
					PR.Morbus_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), PS.Person_closeDT, 104) as Person_closeDT,
					Lpu.Lpu_Nick as Lpu_Nick,
					LpuIns.Lpu_Nick as Lpu_insNick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'NephroRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					COALESCE(DiabEvnDiagSpec.Diag_Code,DiabEvnSection.Diag_Code,DiabEvnVizitPL.Diag_Code,DiabEvnDiagPLSop.Diag_Code,
					DiabEvnDiagPS.Diag_Code,DiabEvnUslugaDispDop.Diag_Code,DiabEvnDiagDopDisp.Diag_Code) as Diab_Diag_Code,
					convert(varchar(10),lastVizitDate.EvnVizitPL_setDate,104) as lastVizitNefroDate,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyNephro_id as EvnNotifyBase_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusNephro_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'EndoRegistry':
				$main_alias = 'PR';
				$query .= '
					PRE.PersonRegisterEndo_id,
					PR.PersonRegister_id,
					PR.PersonRegister_Code,
					PS.Person_id,
					PS.PersonEvn_id,
					PS.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					dbo.Age2(PS.Person_BirthDay, @getDT) as Person_Age,
					Diag.diag_FullName as Diag_Name,
					CLDT.CategoryLifeDegreeType_Name,
					PT.ProsthesType_Name,
					Lpu.Lpu_Nick,
					MP.Person_Fio as MedPersonal_Fio,
					convert(varchar(10), PRE.PersonRegisterEndo_obrDate, 104) as PersonRegisterEndo_obrDate,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PRE.PersonRegisterEndo_callDate, 104) as PersonRegisterEndo_callDate,
					convert(varchar(10), PRE.PersonRegisterEndo_hospDate, 104) as PersonRegisterEndo_hospDate,
					convert(varchar(10), PRE.PersonRegisterEndo_operDate, 104) as PersonRegisterEndo_operDate,
					PRE.PersonRegisterEndo_Contacts,
					PRE.PersonRegisterEndo_Comment
				';
				break;
			case 'IBSRegistry':
				$main_alias = 'PR';
				$query .= "
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyBase_id,
					ISNULL(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusIBS_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					IBSType.IBSType_Name,
					CASE WHEN ISNULL(MO.MorbusIBS_IsKGFinished, 1) = 2 THEN 'true' ELSE 'false' END as MorbusIBS_IsKGFinished,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					CONVERT(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					CONVERT(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					CONVERT(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				";
				break;
			case 'ProfRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyProf_id as EvnNotifyBase_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusProf_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					mpd.MorbusProfDiag_Name,
					o.Org_Name,
					convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
					case when pcs.CardCloseCause_id = 5 then 1 else 0 end as Person_DeRegister,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'TubRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyTub_id as EvnNotifyTub_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusTub_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					v_MorbusType.MorbusType_id,
					v_MorbusType.MorbusType_SysNick,
					Drug.Request as DrugRequest,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'FmbaRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					Drug.Request as DrugRequest,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'HIVRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyHIV_id as EvnNotifyHIV_id,
					MH.MorbusHIV_NumImmun,
					MH.Morbus_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'VenerRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					EN.EvnNotifyVener_id as EvnNotifyVener_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusVener_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'HepatitisRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					ENH.EvnNotifyHepatitis_id as EvnNotifyBase_id,
					isnull(ENH.Morbus_id,PR.Morbus_id) as Morbus_id,
					MH.MorbusHepatitis_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					HDT.HepatitisDiagType_Name,
					HQT.HepatitisQueueType_Name,
					MHQ.MorbusHepatitisQueue_Num,
					IsCure.YesNo_Name as MorbusHepatitisQueue_IsCure,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;

			case 'OnkoRegistry':
				$main_alias = 'PR';
				$query .= "
					PR.PersonRegister_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.EvnNotifyBase_id,
					EONN.EvnOnkoNotifyNeglected_id,	
					MOV.MorbusOnkoVizitPLDop_id,
					MOL.MorbusOnkoLeave_id,
					MO.MorbusOnko_id,
					M.Morbus_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					CASE WHEN OnkoDiag.OnkoDiag_Name is null THEN '' ELSE OnkoDiag.OnkoDiag_Code + '. ' + OnkoDiag.OnkoDiag_Name END as OnkoDiag_Name,
					CASE WHEN (MO.MorbusOnko_IsMainTumor is null OR MO.MorbusOnko_IsMainTumor < 2)  THEN 'Нет' ELSE 'Да' END as MorbusOnko_IsMainTumor,
					TumorStage.TumorStage_Name as TumorStage_Name,
					convert(varchar(10), MO.MorbusOnko_setDiagDT, 104) as MorbusOnko_setDiagDT,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					RTRIM(MP.Person_Fio) as MedPersonal_Fio
				";
				break;
			case 'GeriatricsRegistry':
				$main_alias = 'PR';
				$query .= "
					PR.PersonRegister_id,
					MG.MorbusGeriatrics_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					PR.EvnNotifyBase_id,
					M.Morbus_id,
					D.Diag_id,
					D.Diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					SS.SocStatus_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					ANH.AgeNotHindrance_Name,
					IsKGO.YesNo_Name as MorbusGeriatrics_IsKGO,
					IsWheelChair.YesNo_Name as MorbusGeriatrics_IsWheelChair,
					IsFallDown.YesNo_Name as MorbusGeriatrics_IsFallDown,
					IsWeightDecrease.YesNo_Name as MorbusGeriatrics_IsWeightDecrease,
					IsCapacityDecrease.YesNo_Name as MorbusGeriatrics_IsCapacityDecrease,
					IsCognitiveDefect.YesNo_Name as MorbusGeriatrics_IsCognitiveDefect,
					IsMelancholia.YesNo_Name as MorbusGeriatrics_IsMelancholia,
					IsEnuresis.YesNo_Name as MorbusGeriatrics_IsEnuresis,
					IsPolyPragmasy.YesNo_Name as MorbusGeriatrics_IsPolyPragmasy,
					Lpu.Lpu_Nick as Lpu_Nick
				";
				break;
			case 'IPRARegistry':
				$main_alias = 'PR';
				$query .= "
					IR.IPRARegistry_id,
					IR.IPRARegistry_Number,
					convert(varchar(10), IR.IPRARegistry_issueDate, 104) as IPRARegistry_issueDate,
					convert(varchar(10), IR.IPRARegistry_EndDate, 104) as IPRARegistry_EndDate,
					IR.IPRARegistry_FGUMCEnumber,
					IR.IPRARegistry_Protocol,
					convert(varchar(10), IR.IPRARegistry_ProtocolDate, 104) as IPRARegistry_ProtocolDate,
					convert(varchar(10), IR.IPRARegistry_DevelopDate, 104) as IPRARegistry_DevelopDate,
					IR.IPRARegistry_isFirst,
					IR.IPRARegistry_Confirm,
					IR.IPRARegistry_DirectionLPU_id,
					IR.Lpu_id,
					IR.IPRARegistry_insDT,
					IR.IPRARegistry_updDT,
					IR.pmUser_insID,
					IR.pmUser_updID,
					DirLpu.Lpu_Nick as IPRARegistry_DirectionLPU_Name,
					ConfLpu.Lpu_Nick as LpuConfirm_Name,
					PR.PersonRegister_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					PR.EvnNotifyBase_id,
					PS.Person_id,
					PS.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					case
						when len(ps.Person_Snils) = 11 then left(ps.Person_Snils, 3) + '-' + substring(ps.Person_Snils, 4, 3) + '-' + 
								substring(ps.Person_Snils, 7, 3) + ' ' + right(ps.Person_Snils, 2)
						else ps.Person_Snils
					end as Person_Snils,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					pmUser.pmUser_name + ', ' +pmUser.pmUser_Login as pmUser_name
				";
				if ($this->getRegionNick() != 'ufa') {
					$query .= "
					,IsMeasuresComplete.Value as IsMeasuresComplete
					";
				}
				break;
			case 'ECORegistry':
				$main_alias = 'PR';
                if(isset($data['isRegion']) && $data['isRegion'] == 1){
                        $query .="(SELECT vER.Result
                           FROM dbo.v_ECORegistry vER
                          WHERE vER.PersonRegisterEco_AddDate IN
                                        (select 
                                                max(vER1.PersonRegisterEco_AddDate)
                                        from dbo.v_ECORegistry vER1 
                                        where vER1.PersonRegister_id=PR.PersonRegister_id)
                                AND vER.PersonRegister_id = PR.PersonRegister_id) ResEco,
                        (SELECT vER.ds_osn_name
                            FROM dbo.v_ECORegistry vER
                           WHERE vER.PersonRegisterEco_AddDate IN
                                       (select 
                                                max(vER1.PersonRegisterEco_AddDate)
                                        from dbo.v_ECORegistry vER1 
                                        where vER1.PersonRegister_id=PR.PersonRegister_id)
                                 AND vER.PersonRegister_id = PR.PersonRegister_id) ds_name,
                        (SELECT vER.opl_name
                            FROM dbo.v_ECORegistry vER
                           WHERE vER.PersonRegisterEco_AddDate IN
                                       (select 
                                                max(vER1.PersonRegisterEco_AddDate)
                                        from dbo.v_ECORegistry vER1 
                                        where vER1.PersonRegister_id=PR.PersonRegister_id)
                                 AND vER.PersonRegister_id = PR.PersonRegister_id) opl_name,
						(SELECT top 1 pn.Person_Fio
                            FROM dbo.v_ECORegistry vER
								LEFT JOIN [persis].[MedWorker] mw WITH (NOLOCK) ON mw.id=vER.MedPersonal_id
								LEFT JOIN v_Person_bdz pn WITH (NOLOCK) ON pn.Person_id=mw.Person_id
                           WHERE vER.PersonRegisterEco_AddDate IN
                                       (select 
                                                max(vER1.PersonRegisterEco_AddDate)
                                        from dbo.v_ECORegistry vER1 
                                        where vER1.PersonRegister_id=PR.PersonRegister_id)
                                 AND vER.PersonRegister_id = PR.PersonRegister_id) as MedPersonal_name,
								 ";
                }else{
                    $query .="(SELECT vER.Result
                                FROM dbo.v_ECORegistry vER
                               WHERE vER.PersonRegisterEco_AddDate IN
                                           (select 
                                                                              max(vER1.PersonRegisterEco_AddDate)
                                                                      from dbo.v_ECORegistry vER1 
                                                                      where vER1.PersonRegister_id=PR.PersonRegister_id
                                                                              AND vER1.lpu_id = :PersonRegister_Lpu_iid)
                                     AND vER.PersonRegister_id = PR.PersonRegister_id) ResEco,
                                 (SELECT vER.ds_osn_name
                                FROM dbo.v_ECORegistry vER
                               WHERE vER.PersonRegisterEco_AddDate IN
                                           (select 
                                                                              max(vER1.PersonRegisterEco_AddDate)
                                                                      from dbo.v_ECORegistry vER1 
                                                                      where vER1.PersonRegister_id=PR.PersonRegister_id
                                                                              AND vER1.lpu_id = :PersonRegister_Lpu_iid)
                                     AND vER.PersonRegister_id = PR.PersonRegister_id) ds_name,
                                (SELECT vER.opl_name
                                FROM dbo.v_ECORegistry vER
                               WHERE vER.PersonRegisterEco_AddDate IN
                                           (select 
                                                                              max(vER1.PersonRegisterEco_AddDate)
                                                                      from dbo.v_ECORegistry vER1 
                                                                      where vER1.PersonRegister_id=PR.PersonRegister_id
                                                                              AND vER1.lpu_id = :PersonRegister_Lpu_iid)
                                     AND vER.PersonRegister_id = PR.PersonRegister_id) opl_name,
									 
						(SELECT top 1 pn.Person_Fio
                            FROM dbo.v_ECORegistry vER
								LEFT JOIN [persis].[MedWorker] mw WITH (NOLOCK) ON mw.id=vER.MedPersonal_id
								LEFT JOIN v_Person_bdz pn WITH (NOLOCK) ON pn.Person_id=mw.Person_id
                           WHERE vER.PersonRegisterEco_AddDate IN
                                       (select 
                                                max(vER1.PersonRegisterEco_AddDate)
                                        from dbo.v_ECORegistry vER1 
                                        where vER1.PersonRegister_id=PR.PersonRegister_id AND vER1.lpu_id = :PersonRegister_Lpu_iid)
                                 AND vER.PersonRegister_id = PR.PersonRegister_id) as MedPersonal_name,

					";
                    $queryParams['PersonRegister_Lpu_iid'] = $data['EcoRegistryData_lpu_id'];

                };
				$query .= "
                        PR.PersonRegister_id,
                        PR.Lpu_iid,
                        PR.MorbusType_id,
                        PS.Person_id,
                        PS.Server_id,
                        ER.Result ResEco1,
                        RTRIM (PS.Person_SurName) AS Person_Surname,
                        RTRIM (PS.Person_FirName) AS Person_Firname,
                        RTRIM (PS.Person_SecName) AS Person_Secname,
                        convert (VARCHAR (10), PS.Person_Birthday, 104) AS Person_Birthday,
                        Lpu.Lpu_Nick AS Lpu_Nick,
                        LpuUch.Lpu_Nick AS Lpu_NickUch,
                        convert (VARCHAR (10), PR.PersonRegister_setDate, 104)
                           AS PersonRegister_setDate,
                        isnull ([PA].Address_Nick, [PA].Address_Address) AS Person_PAddress,
						(case when PPR.PregnancyResult_Name is not null then PPR.PregnancyResult_Name when PPR1.PregnancyResult_Name is not  null then PPR1.PregnancyResult_Name else '' end) as IsxBer,
						(case when (BSS.BirthSpecStac_CountChild is null and BSS1.BirthSpecStac_CountChild is null and ER.EcoChildCountType_Name is null) then ER.EmbrionCount_Name
							  when (BSS.BirthSpecStac_CountChild is null and BSS1.BirthSpecStac_CountChild is null and ER.EcoChildCountType_Name is not null) then ER.EcoChildCountType_Name else						    
							(case when BSS.BirthSpecStac_CountChild is not NULL then CAST(BSS.BirthSpecStac_CountChild as varchar(10)) else CAST(BSS1.BirthSpecStac_CountChild as varchar(10)) end)
						 end)
						 as Count_plod
                ";
				break;

			case 'BskRegistry':
				$main_alias = 'R';
				$query .= "
					PR.PersonRegister_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					PR.EvnNotifyBase_id,
					PS.Person_id,
					PS.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					isnull(convert(varchar(10),R.BSKRegistry_nextDate,21),case when PR.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then convert(varchar(10), (dateadd(MONTH, 18, R.BSKRegistry_setDate)), 21)
							 when PR.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then convert(varchar(10), (dateadd(MONTH, 12, R.BSKRegistry_setDate)), 21)
							 when PR.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then convert(varchar(10), (dateadd(MONTH, 6, R.BSKRegistry_setDate)), 21)
							 when PR.MorbusType_id = 50 then convert(varchar(10), (dateadd(MONTH, 6, R.BSKRegistry_setDate)), 21)
							 when PR.MorbusType_id = 89 then convert(varchar(10), (dateadd(MONTH, 6, R.BSKRegistry_setDate)), 21)
							 when PR.MorbusType_id = 88 then convert(varchar(10), (dateadd(MONTH, 6, R.BSKRegistry_setDate)), 21)
							 end) as BSKRegistry_setDateNext,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), ps.Person_deadDT , 104) as Person_deadDT,
					Lpu.Lpu_Nick as Lpu_Nick,
					BSKLpuGospital.BSKLpuGospital_data as Lpu_Gospital,
					BSKTLT.TLT_data as isTLT,
					case when BSKTLT.TLT_data is not null then '' else [dbo].[GetBSKTimeForTLT](PS.Person_id) end as TimeBeforeTlt,
					case when BSKCKV.CHKV_data = 'нет' then '' else BSKCKV.CHKV_data end as isCKV,
					[dbo].[GetBSKTimeForCKVKAG](PS.Person_id, 0) as CKVduringHour,
					BSKKAG.KAG_data as isKAG,
					[dbo].[GetBSKTimeForCKVKAG](PS.Person_id, 1) as KAGduringHour,
					DP.Diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
                    convert(varchar(10), R.BSKRegistry_setDate, 104) as BSKRegistry_setDate,
                    MP.PMUser_Name as PMUser_Name,
                    MP.pmUser_id as pmUser_id,
					R.BSKRegistry_isBrowsed
				";
				break;
			case 'ReabRegistry':
				$main_alias = 'R';
				$query .= "
					PS.Person_id,
					PS.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
                                        convert(varchar(10), ps.Person_deadDT , 104) as Person_deadDT,
					Lpu.Lpu_Nick as Lpu_Nick
				";
				break;
			case 'AdminVIPPerson':
				$main_alias = 'R';
				$query .= "
					PS.Person_id,
					PS.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
                    convert(varchar(10), R.VIPPerson_setDate , 104) as VIPPerson_setDate,
					convert(varchar(10), R.VIPPerson_disDate , 104) as VIPPerson_disDate,
					R.VIPPerson_id as VIPPerson_id,
					R.VIPPerson_deleted as VIPPerson_deleted,
					Lpu1.Lpu_Nick as Lpu_Nick,
					Lpu1.Lpu_id as Lpu_id,
					pmUser.PMUser_Name as PMUser_Name
				";
				break;
			case 'ZNOSuspectRegistry':
				$main_alias = 'R';
				$query .= "
					ZNORout.ZNOSuspectRout_id as ZNORout_id,
					PS.Person_id,
					PS.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
                    convert(varchar(10), ps.Person_deadDT , 104) as Person_deadDT,
					Lpu.Lpu_Nick as Lpu_Nick,
					lpu.Lpu_id as Lpu_iid,
					d.Diag_Code as Diag_CodeFirst,
					ZNORout.ZNOSuspectRout_IsTerms ,
					case
						when ZNOSuspectRout_IsTerms is null then null
						when ZNORout.ZNOSuspectRout_IsTerms = 1 then 'V'
					  else '!'
					end as Terms,
					ZNORout.ZNOSuspectRout_IsBiopsy,
					case
						when ZNORout.ZNOSuspectRout_IsBiopsy is null then null
					  when ZNORout.ZNOSuspectRout_IsBiopsy = 1 then 'V'
					  else '!'
					end as Biopsy,
					dd.Diag_id as Finish,
					case
					  when ZNORout.Diag_Fid > 0 then dd.Diag_Code
					  when ZNORout.Diag_Fid is null then null
					  else '!'
					end as Diag_CodeFinish,
					convert(varchar(10), ZNOReg.ZNOSuspectRegistry_setDate, 104) as Registry_setDate
				";
				break;

			//BOB - 23.01.2018
			case 'ReanimatRegistry':
				$main_alias = 'RR';
				$query .= "
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					--PR.PersonRegister_id,
					RR.Lpu_iid, --    PR.Lpu_iid,
					RR.MedPersonal_iid,  --PR.MedPersonal_iid,
					RR.MorbusType_id,
					convert(varchar(10), RR.ReanimatRegister_setDate, 104) as ReanimatRegister_setDate, --convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), RR.ReanimatRegister_disDate, 104) as ReanimatRegister_disDate,  --convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					Lpu.Lpu_Nick as Lpu_Nick,
					MP.PMUser_Name as PMUser_Name,
                    MP.pmUser_id as pmUser_id,
					RR.ReanimatRegister_id as ReanimatRegister_id,
					RR.EvnReanimatPeriod_id as EvnReanimatPeriod_id,
					RR.ReanimatRegister_IsPeriodNow as ReanimatRegister_IsPeriodNow,
					ISNULL(ERP.selrow, 0) as selrow,
					D.Diag_FullName as Diag,
					Lpu2.Lpu_Nick as Lpu_Nick_Curr
				";

				break;
			//BOB - 23.01.2018

			case 'EvnInfectNotify':
				$main_alias = "EIN";
				$query .= "
					EIN.EvnInfectNotify_id,
					convert(varchar(10), EIN.EvnInfectNotify_insDT, 104) as EvnInfectNotify_insDT,
					PS.Person_id,
					pc.Lpu_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					ISNULL ( Diag.diag_FullName, Diag1.diag_FullName ) as Diag_Name
				";
				break;

			case 'EvnNotifyHepatitis':
				$main_alias = "ENH";
				$query .= "
					ENH.EvnNotifyHepatitis_id,
					ENH.EvnNotifyHepatitis_pid,
					ENH.Morbus_id,
					ENH.MedPersonal_id,
					ENH.pmUser_updId,
					convert(varchar(10), ENH.EvnNotifyHepatitis_setDT, 104) as EvnNotifyHepatitis_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENH.EvnNotifyHepatitis_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate
				";
				break;

			case 'EvnOnkoNotify':
				$main_alias = "EON";
				$query .= "
					EON.EvnOnkoNotify_id,
					EONN.EvnOnkoNotifyNeglected_id,
					EON.EvnOnkoNotify_pid,
					EON.Morbus_id,
					EON.MedPersonal_id,
					EON.pmUser_updId,
					convert(varchar(10), EON.EvnOnkoNotify_setDT, 104) as EvnOnkoNotify_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					EON.Lpu_sid,
					Lpu1.Lpu_Nick as Lpu_sid_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					OnkoDiag.OnkoDiag_Name as OnkoDiag_Name,
					TumorStage.TumorStage_Name as TumorStage_Name,
					CASE WHEN EONN.EvnOnkoNotifyNeglected_id IS NOT NULL THEN 'true' ELSE 'false' END as isNeglected,
					convert(varchar(10), isnull(EON.EvnOnkoNotify_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					case 
						when EON.EvnOnkoNotify_niDate is null and PR.PersonRegister_setDate is not null then 'Да'
						when EON.EvnOnkoNotify_niDate is not null then 'Нет'
						else ''
					end as IsIncluded,
					case 
						when EON.EvnOnkoNotify_niDate is null and PR.PersonRegister_setDate is not null then 'Включено в регистр'
						when EON.EvnOnkoNotify_niDate is not null and EON.PersonRegisterFailIncludeCause_id = 1 then 'Отклонено (ошибка в Извещении)'
						when EON.EvnOnkoNotify_niDate is not null and EON.PersonRegisterFailIncludeCause_id = 2 then 'Отклонено (решение оператора)'
						else 'Отправлено'
					end as EvnNotifyStatus_Name,
					EON.EvnOnkoNotify_Comment,
					null as EvnOnkoNotify_CommentLink
				";
				break;

			case 'EvnNotifyRegister':
				$main_alias = 'EN';
				$query .= '
					EN.EvnNotifyRegister_id,
					E.Evn_pid as EvnNotifyRegister_pid,
					EN.EvnNotifyRegister_Num,
					convert(varchar(10), E.Evn_setDT, 104) as EvnNotifyRegister_setDT,
					EN.NotifyType_id,
					NT.NotifyType_Name,
					PRT.PersonRegisterType_SysNick,
					MT.MorbusType_SysNick,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					E.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					AttachLpu.Lpu_Nick as AttachLpu_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					E.Lpu_id as Lpu_did,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENB.MedPersonal_id,
					MP.Person_Fio as MedPersonal_Name,
					E.pmUser_updId
				';
				break;
			case 'EvnNotifyOrphan':
				$main_alias = 'ENO';
				$query .= '
					ENO.EvnNotifyOrphan_id,
					ENO.EvnNotifyOrphan_pid,
					convert(varchar(10), ENO.EvnNotifyOrphan_setDT, 104) as EvnNotifyOrphan_setDT,
					ENO.EvnNotifyType_Name,
					ENO.EvnNotifyType_SysNick,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENO.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENO.EvnNotifyOrphan_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENO.MedPersonal_id,
					MP.Person_Fio as MedPersonal_Name,
					ENO.pmUser_updId
				';
				break;

			case 'EvnNotifyCrazy': // Психиатрия
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyCrazy_id,
					ENC.EvnNotifyCrazy_pid,
					convert(varchar(10), ENC.EvnNotifyCrazy_setDT, 104) as EvnNotifyCrazy_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyCrazy_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyNarko': // Психиатрия
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyNarco_id,
					ENC.EvnNotifyNarco_pid,
					convert(varchar(10), ENC.EvnNotifyNarco_setDT, 104) as EvnNotifyNarco_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Lpu.Lpu_id as Lpu_id,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyNarco_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyTub': // Туберкулез
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyTub_id,
					ENC.EvnNotifyTub_pid,
					convert(varchar(10), ENC.EvnNotifyTub_setDT, 104) as EvnNotifyTub_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					isnull(DiagENC.Diag_id,Diag.Diag_id) as Diag_id,
					isnull(DiagENC.diag_FullName,Diag.diag_FullName) as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyTub_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					PR.PersonRegister_id,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyNephro': // Нефрология
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyNephro_id,
					ENC.EvnNotifyNephro_pid,
					convert(varchar(10), ENC.EvnNotifyNephro_setDT, 104) as EvnNotifyNephro_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyNephro_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyProf': // Профзаболевания
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyProf_id,
					ENC.EvnNotifyProf_pid,
					convert(varchar(10), ENC.EvnNotifyProf_setDT, 104) as EvnNotifyProf_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyProf_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyHIV': // ВИЧ
				$main_alias = 'ENB';
				$query .= '
					ENB.EvnNotifyBase_id,
					ENB.EvnNotifyBase_pid,
					convert(varchar(10), ENB.EvnNotifyBase_setDT, 104) as EvnNotifyBase_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENB.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENB.MedPersonal_id,
					EvnClass.EvnClass_Name,
					EvnClass.EvnClass_SysNick,
					ENB.pmUser_updId
				';
				break;
			case 'EvnNotifyVener': // Туберкулез
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyVener_id,
					ENC.EvnNotifyVener_pid,
					convert(varchar(10), ENC.EvnNotifyVener_setDT, 104) as EvnNotifyVener_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyVener_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'PalliatNotify':
				$main_alias = "PN";
				$query .= "
					PN.PalliatNotify_id,
					ENB.EvnNotifyBase_id,
					ENB.Morbus_id,
					ENB.MedPersonal_id,
					ENB.pmUser_updId,
					1 as NotifyType_id,
					convert(varchar(10), ENB.EvnNotifyBase_setDT, 104) as EvnNotifyBase_setDate,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_id as Lpu_did,
					Lpu.Lpu_Nick as Lpu_Nick,
					AttachLpu.Lpu_Nick as AttachLpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					PRT.PersonRegisterType_SysNick,
					case
						when ENB.EvnNotifyBase_niDate is not null then 1
						when PR.PersonRegister_id is not null then 2
					end as isInclude
				";
				break;
			case 'PersonDopDispPlan': // План профилактических мероприятий
				$main_alias = 'PDDP';
				$query .= '
					PS.Person_id,
					RTRIM(PS.Person_SurName) + \' \' + isnull(PS.Person_FirName, \'\') + \' \' + isnull(PS.Person_SecName, \'\') as Person_FIO,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					sex.Sex_Name as Person_Sex,
					convert(varchar(10), IsPersonDopDispPassed.EvnPLDisp_setDate, 104) as EvnPLDisp_setDate,
					convert(varchar(10), IsPersonDopDispPassed.EvnPLDisp_disDate, 104) as EvnPLDisp_disDate,
					2 as IsChecked
				';
				break;
			case 'RzhdRegistry':
				$main_alias = 'RR';
				$query .= "
					R.Register_id,
					RR.RzhdRegistry_id,
					R.Person_id,
					convert(varchar(10), R.Register_setDate, 104) as Register_setDate,
					convert(varchar(10), R.Register_disDate, 104) as Register_disDate,
					PS.Person_SurName as Person_Surname,
					PS.Person_FirName as Person_FirstName,
					PS.Person_SecName as Person_SecondName,
					convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT,
					PS.Polis_Num as Person_PolisNum,
					PS.Server_id as Server_id,
					case
						when len(PS.Person_Snils) = 11 then left(PS.Person_Snils, 3) + '-' + substring(PS.Person_Snils, 4, 3) + '-' +
							substring(PS.Person_Snils, 7, 3) + ' ' + right(PS.Person_Snils, 2)
						else PS.Person_Snils
					end as Person_Snils,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
					R.RegisterDisCause_id,
					RDC.RegisterDisCause_name,
					Lpu.Lpu_id as Lpu_id,
					Org.Org_Nick as Lpu_Nick
				";
				break;
            case 'ONMKRegistry':
                $main_alias = 'PR';
				$query .= '
					ROW_NUMBER () over (order by ONMKR.ONMKRegistry_id ) as vID,
					ONMKR.ONMKRegistry_id,
					PS.Person_id,
					ONMKR.ONMKRegistry_IsNew,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,					
					dbo.Age(PS.Person_BirthDay, getdate()) as Person_Age,
					
					convert(varchar, ONMKR.ONMKRegistry_EvnDT, 104) as ONMKRegistry_Evn_DTDesease,
					substring(convert(varchar, ONMKR.ONMKRegistry_EvnDT, 8), 0, 6) as ONMKRegistry_Evn_DTDesease_Time, 

					(case when ONMKR.ONMKRegistry_EvnDT = null then \'\' when ONMKR.ONMKRegistry_EvnDTDesease = null then \'\'					
					else [dbo].[GetPeriodName] (ONMKR.ONMKRegistry_EvnDTDesease, ONMKR.ONMKRegistry_EvnDT)
					end) 					
					as TimeBeforeStac,					
					Dg.Diag_Code+Dg.Diag_Name as Diag_Name,
					RS.RankinScale_code as Renkin,					
					convert(varchar, ONMKR.ONMKRegistry_TLTDT, 104) + \' \'+ substring(convert(varchar, ONMKR.ONMKRegistry_TLTDT, 8), 0, 6) as TLTDT,					
					(case when ONMKR.ONMKRegistry_TLTDT is null then [dbo].[GetONMKTimeForTLT](ONMKR.ONMKRegistry_EvnDTDesease) else \'\' end) as TimeBeforeTlt,
					ONMKR.ONMKRegistry_InsultScale as Nihss,
					ONMKR.ONMKRegistry_NIHSSAfterTLT as ONMKRegistry_NIHSSAfterTLT,
					convert(varchar, ONMKR.ONMKRegistry_KTDT, 104) + \' \'+ substring(convert(varchar, ONMKR.ONMKRegistry_KTDT, 8), 0, 6) as KTDT,
					convert(varchar, ONMKR.ONMKRegistry_MRTDT, 104) + \' \'+ substring(convert(varchar, ONMKR.ONMKRegistry_MRTDT, 8), 0, 6) as MRTDT,
					--CT.ConsciousType_Name as Conscious,
					[dbo].[GetReanimat] (ONMKR.EvnPS_id, 1) as ConsciousType_Name,
					[dbo].[GetReanimat] (ONMKR.EvnPS_id, 2) as BreathingType_Name,
					Lp.Lpu_Nick as Lpu_Nick,
					Lp.Lpu_id as Lpu_id,
					[dbo].[GetONMKMO] (Lp.Lpu_id) as MO_OK,
					ONMKR.ONMKRegistry_IsIteration as HasDiag,
					--ONMKR.ONMKRegistry_NumKVS as Lpu_Nick,					
					convert(varchar, ONMKR.ONMKRegistry_insDT, 104) as ONMKRegistry_SetDate,
					
					(case when ONMKR.ONMKRegistry_IsConfirmed = 2 then \'не подтвержден\' else LT.LeaveType_Name end) as LeaveType_Name
				';
				break;
			case 'SportRegistry':
				$main_alias = 'SR';
				$query .= "
					SRUMO.SportRegisterUMO_id,
					SRUMO.SportRegister_id,
					OC.PersonRegisterOutCause_id,
					OC.PersonRegisterOutCause_Name,
					SRUMO.Lpu_id,
					PS.Person_id,
					PS.Server_id,
					PS.Person_SurName,
					PS.Person_FirName,
					PS.Person_SecName,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
					ST.SportType_name,
					SS.SportStage_name,
					convert(varchar(10), SRUMO.SportRegisterUMO_UMODate, 104) as SportRegisterUMO_UMODate,
					UR.UMOResult_name,
					SC.SportCategory_name,
					IGT.InvalidGroupType_Name,
					SPG.SportParaGroup_name,
					SRUMO.SportRegisterUMO_IsTeamMember,
					convert(varchar(10), SRUMO.SportRegisterUMO_AdmissionDtBeg, 104) as SportRegisterUMO_AdmissionDtBeg,
					convert(varchar(10), SRUMO.SportRegisterUMO_AdmissionDtEnd, 104) as SportRegisterUMO_AdmissionDtEnd,
					convert(varchar(10), SR.SportRegister_insDT, 104) as SportRegister_insDT,
					convert(varchar(10), SR.SportRegister_updDT, 104) as SportRegister_updDT,
					convert(varchar(10), SR.SportRegister_delDT, 104) as SportRegister_delDT,
					OC.PersonRegisterOutCause_Name,
					RTRIM (MSFPSp.PersonSurName_SurName) + ' '+ RTRIM (MSFPSp.PersonFirName_FirName) + ' ' + RTRIM (MSFPSp.PersonSecName_SecName) AS MedPersonal_pname,
					SO.SportOrg_name,
					RTRIM (PSTr.PersonSurName_SurName) + ' '+ RTRIM (PSTr.PersonFirName_FirName) + ' ' + RTRIM (PSTr.PersonSecName_SecName) AS SportTrainer_name
				";
				break;
			case 'HTMRegister':
				$main_alias = 'HR';
				$query .= "
					HR.HTMRegister_id,
					HR.HTMRegister_IsSigned,
					HR.QueueNumber,
					R.Register_id,
					convert(varchar(10), R.Register_setDate, 104) as Register_setDate,
					convert(varchar(10), PS.Person_deadDT, 104) as Register_disDate,
					R.Person_id,
					PS.Person_SurName,
					PS.Person_FirName,
					PS.Person_SecName,
					PS.Server_id,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LpuEDH.Lpu_id as Lpu_sid,
					LpuEDH.Org_id as Org_sid,
					LpuEDH.Lpu_Nick as Lpu_Nick2,
					PS.Person_isDead,
					EDH.LpuSectionProfile_id,
					LSP.LpuSectionProfile_Name
				";
				break;
			case 'GibtRegistry':
				$main_alias = 'PR';
				$query .= "
					PR.PersonRegister_id,
					MG.MorbusGEBT_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					M.Morbus_id,
					D.Diag_id,
					D.Diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
					Lpu.Lpu_Nick as Lpu_Nick
				";
				break;
			case 'EvnERSBirthCertificate':
				$main_alias = 'ERS';
				$query .= "
					ERS.EvnERSBirthCertificate_id,
					ERS.ERSStatus_id,
					ERS.ErsRequest_id,
					ERS.Person_id,
					ERS.Lpu_id,
					ERSt.ErsRequestStatus_id,
					ERS.EvnERSBirthCertificate_Number,
					convert(varchar(10), ERS.EvnErsBirthCertificate_setDT, 104) as EvnErsBirthCertificate_setDT,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					CCT.ERSCloseCauseType_Name,
					ER.ERSRequest_ERSNumber,
					ES.ERSStatus_Name,
					convert(varchar(10), ERS.EvnErsBirthCertificate_PregnancyRegDate, 104) as EvnErsBirthCertificate_PregnancyRegDate,
					ERT.ErsRequestType_Name,
					ERSt.ErsRequestStatus_Name,
					substring(ERE.ERSRequestError, 1, len(ERE.ERSRequestError)-1) as ErsRequestError,
					ticket1.ERSStatus_Name as EvnERSTicket1,
					ticket2.ERSStatus_Name as EvnERSTicket2,
					ticket31.ERSStatus_Name as EvnERSTicket31,
					ticket32.ERSStatus_Name as EvnERSTicket32
				";
				break;
			default:
				return array('success' => false, 'Error_Msg' => 'Необходимо задать цель поиска.');
				break;
		}

		if (!empty($archive_database_enable) && !empty($main_alias) && !empty($archiveTable)) {
			$archive_main_alias = $main_alias;
			if ($archiveTable == 'CmpCloseCard') {
				$archive_main_alias = 'CLC';
			} else if ($archiveTable == 'Evn') {
				$archive_main_alias = 'Evn';
			}
			$select_main_alias = $archive_main_alias;
			if ($archive_main_alias == 'EvnPLDispDop13') {
				$select_main_alias = 'EPLDD13';
			} else if ($archive_main_alias == 'Evn') {
				$select_main_alias = 'Evn';
			}
			$query .= "
				, case when ISNULL({$select_main_alias}.{$archiveTable}_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL({$archive_main_alias}.{$archiveTable}_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$data['start'] = $data['start'] - $data['archiveStart']; // начало архивных записей за вычетом уже показанных актуальных
				$filter .= " and ISNULL({$archive_main_alias}.{$archiveTable}_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		$query .= "
			-- end select
			from
				-- from
		";

		switch ($data['SearchFormType']) {
			case 'PersonPrivilegeWOW':
				$query .= "
					v_PersonState PS with (nolock)
				";
				break;
			case 'RegisterSixtyPlus':
				$query .= "
					RegisterSixtyPlus RPlus with (nolock)
				";
				break;

			case 'EvnPLDispDop13':
				$query .= "
						EvnPLDispDop13Top EPLDD13 with (nolock)
					";
				break;
			case 'EvnPLDispDop':
			case 'EvnPLDispDop13Sec':
			case 'EvnPLDispProf':
			case 'EvnPLDispScreen':
			case 'EvnPLDispScreenChild':
			case 'EvnPLDispTeen14':
			case 'EvnPLDispDopStream':
			case 'EvnPLDispTeen14Stream':
			case 'EvnPLDispOrp':
			case 'EvnPLDispOrpOld':
			case 'EvnPLDispOrpSec':
			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
			case 'EvnPLDispOrpStream':
			case 'EvnPLDispMigrant':
			case 'EvnPLDispDriver':
			case 'EPLStomPerson':
			case 'EvnPLStom':
			case 'EvnVizitPLStom':
			case 'EvnUslugaStom':
			case 'EvnAggStom':
			case 'EvnPS':
			case 'EvnSection':
			case 'EvnDiag':
			case 'EvnLeave':
			case 'EvnStick':
			case 'KvsPerson':
			case 'KvsPersonCard':
			case 'KvsEvnDiag':
			case 'KvsEvnPS':
			case 'KvsEvnSection':
			case 'KvsNarrowBed':
			case 'KvsEvnUsluga':
			case 'KvsEvnUslugaOB':
			case 'KvsEvnUslugaAn':
			case 'KvsEvnUslugaOsl':
			case 'KvsEvnDrug':
			case 'KvsEvnLeave':
			case 'KvsEvnStick':
			case 'EvnRecept':
			case 'EvnReceptGeneral':
			case 'EvnPLWOW':
			case 'EvnDtpWound':
			case 'EvnDtpDeath':
			case 'EvnUslugaPar':
			case 'EvnInfectNotify':
			case 'EvnNotifyHepatitis':
			case 'EvnOnkoNotify':
			case 'EvnNotifyOrphan':
			case 'EvnNotifyRegister':
			case 'PersonRegisterBase':
			case 'PalliatRegistry':
			case 'EvnNotifyCrazy':
			case 'EvnNotifyNarko':
			case 'EvnNotifyTub':
			case 'EvnNotifyNephro':
			case 'EvnNotifyProf':
			case 'NephroRegistry':
			case 'EndoRegistry':
			case 'IBSRegistry':
			case 'ProfRegistry':
			case 'EvnNotifyHIV':
			case 'EvnNotifyVener':
			case 'PalliatNotify':
			case 'HepatitisRegistry':
			case 'OnkoRegistry':
			case 'GeriatricsRegistry':
			case 'BskRegistry':
			case 'ReabRegistry':
			case 'AdminVIPPerson':
			case 'ZNOSuspectRegistry':
			case 'ReanimatRegistry':	  //BOB - 13.10.2017
			case 'IPRARegistry':
			case 'ECORegistry':
			case 'OrphanRegistry':
			case 'ACSRegistry':
			case 'CrazyRegistry':
			case 'NarkoRegistry':
			case 'TubRegistry':
			case 'DiabetesRegistry':
			case 'FmbaRegistry':
			case 'LargeFamilyRegistry':
			case 'HIVRegistry':
			case 'VenerRegistry':
			case 'ONMKRegistry':
			case 'SportRegistry':
			case 'GibtRegistry':
			case 'EvnERSBirthCertificate':
				if (in_array($data['PersonPeriodicType_id'], array(2))) {
					$query .= "
						v_Person_all PS with (nolock)
					";
				} else {
					if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
						$query .= "
							v_PersonState PS with (nolock)
						";
					} else {
						$query .= "
							v_PersonState_all PS with (nolock)
						";
					}
				}
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
					left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id";
				}
				break;
			case 'PersonDopDisp':
				if (isset($data['Refuse_id']))
					$query .= "
							v_PersonState_All PS with (nolock)
					";
				else
					$query .= "
							v_PersonState PS with (nolock)
					";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id";
				}
				break;

			case 'WorkPlacePolkaReg':
			case 'PersonCallCenter':
			case 'PersonCard':
			case 'PersonDispOrp':
			case 'PersonDispOrpPeriod':
			case 'PersonDispOrpPred':
			case 'PersonDispOrpProf':
			case 'PersonDispOrpOld':
			case 'PersonDisp':
			case 'PersonCardStateDetail':
			case 'PersonPrivilege':
			case 'PersonDopDispPlan':
				$query .= "
						v_PersonState_All PS with (nolock)
					";
				if (allowPersonEncrypHIV($data['session'])) {
					$query .= "
						left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id";
				}
				if( $this->getRegionNick()=='ufa' and $data['SearchFormType']=='PersonCard' and $data['hasObrTalonMse']=='on') {
					$mseDiagFilter = '(1=1)';
					if(!empty($data['Diag_Code_From'])) {
						$mseDiagFilter.=" and D1oa.Diag_Code >= :Diag_Code_From";
						$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
					}
					if(!empty($data['Diag_Code_To'])) {
						$mseDiagFilter.=" and D1oa.Diag_Code <= :Diag_Code_To";
						$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
					}

					$query .= "cross apply(
						select top 1 
							D1oa.Diag_Code, 
							IGT.InvalidGroupType_Name
						from v_EvnMse mse (nolock)
							inner join InvalidGroupType IGT (nolock) on IGT.InvalidGroupType_id = mse.InvalidGroupType_id
							left join v_Diag D1oa (nolock) on D1oa.Diag_id = mse.Diag_id
						where {$mseDiagFilter}
						order by mse.EvnMse_SendStickDate DESC
					) OBTMSE";
				}

				break;
		}

		if (isset($data['soc_card_id']) && strlen($data['soc_card_id']) >= 25) {
			$filter .= " and LEFT(ps.Person_SocCardNum, 19) = :SocCardNum ";
			$queryParams['SocCardNum'] = substr($data['soc_card_id'], 0, 19);
		}

		switch ($data['SearchFormType']) {
			case 'CmpCallCard':
				$query .= "
					v_CmpCallCard CCC with (nolock)
					left join v_CmpCallCardCostPrint ccp (nolock) on ccp.CmpCallCard_id = ccc.CmpCallCard_id
					left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
					left join v_CmpReason CSecondR with (nolock) on CSecondR.CmpReason_id = CCC.CmpSecondReason_id
					left join v_CmpResult CRES with (nolock) on CRES.CmpResult_id = CCC.CmpResult_id
					left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
					left join EmergencyTeam ET with (nolock) on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
					left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CLC.CmpCallCard_id = CCC.CmpCallCard_id
					left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
					left join v_Lpu Lpu with (nolock) on CCC.CmpLpu_id = Lpu.Lpu_id
                    left join v_Lpu LpuHid with (nolock) on CCC.CmpLpu_id = LpuHid.Lpu_id
					left join v_Diag CLD with (nolock) on CLD.Diag_id = CLC.Diag_id
					left join v_CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
					left join v_Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
					left join v_Diag UD with (nolock) on UD.Diag_id = CCC.Diag_uid
					left join v_PersonState PS with (nolock) on CCC.Person_id = PS.Person_id
					left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
					left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id					
					left join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
					left join CmpCallCardInputType CCCInput with (nolock) on CCCInput.CmpCallCardInputType_id = CCC.CmpCallCardInputType_id
					OUTER apply (
						SELECT top 1
						CCCE.CmpCallCardEvent_id
						FROM v_CmpCallCardEvent CCCE with (nolock)
							LEFT JOIN v_CmpCallCardEventType CCCET with(nolock) on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id
						WHERE CCCE.CmpCallCard_id = CCC.CmpCallCard_id AND CCCET.CmpCallCardEventType_Code = 6
					) as acceptNmpFlag
				";
				//всегда отсеивать карты вызова дубли-версии
				//$filter .=" and (ISNULL(CCC.CmpCallCardStatusType_id, 0) <> 16)"; // redmine.swan.perm.ru/issues/78063#note-4
				// Фильтр по ЛПУ
				if (array_key_exists('linkedLpuIdList', $data['session'])) {
					$filter .= " and (LB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")
								or (CCC.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and CCC.LpuBuilding_id is NUll) )";
				} else if (!empty($data['Lpu_id'])) {
					$filter .= " and (LB.Lpu_id = :Lpu_id or (CCC.Lpu_id = :Lpu_id and CCC.LpuBuilding_id is NUll) )";
					$queryParams['Lpu_id'] = $data['Lpu_id'];
				}

				// Пациент (карта)
				if (!empty($data['CmpCloseCard_id'])) {
					$filter .= " and CLC.CmpCloseCard_id = :CmpCloseCard_id";
					$queryParams['CmpCloseCard_id'] = $data['CmpCloseCard_id'];
				}

				// Пациент (карта)
				if (!empty($data['CmpArea_gid'])) {
					$filter .= " and CCC.CmpArea_gid = :CmpArea_gid";
					$queryParams['CmpArea_gid'] = $data['CmpArea_gid'];
				}

				// Пациент (карта)
				if (isset($data['CmpCallCard_Expo'])) {
					$filter .= " and CCC.CmpCallCard_Expo = :CmpCallCard_Expo";
					$queryParams['CmpCallCard_Expo'] = $data['CmpCallCard_Expo'];
				}

				// Пациент (карта)
				if (!empty($data['CmpCallCard_IsAlco'])) {
					$filter .= " and CCC.CmpCallCard_IsAlco = :CmpCallCard_IsAlco";
					$queryParams['CmpCallCard_IsAlco'] = $data['CmpCallCard_IsAlco'];
				}

				// Пациент (карта)
				if (!empty($data['CmpCallCard_IsPoli'])) {
					$filter .= " and CCC.CmpCallCard_IsPoli = :CmpCallCard_IsPoli";
					$queryParams['CmpCallCard_IsPoli'] = $data['CmpCallCard_IsPoli'];
				}

				if (!empty($data['CmpCallCard_isPaid'])) {
					$filter .= " and ISNULL(CCC.CmpCallCard_isPaid,1) = :CmpCallCard_isPaid";
					$queryParams['CmpCallCard_isPaid'] = $data['CmpCallCard_isPaid'];
				}

				// Пациент (карта)
				if (!empty($data['CmpDiag_aid'])) {
					$filter .= " and CCC.CmpDiag_aid = :CmpDiag_aid";
					$queryParams['CmpDiag_aid'] = $data['CmpDiag_aid'];
				}

				// Пациент (карта)
				if (!empty($data['CmpDiag_oid'])) {
					$filter .= " and CCC.CmpDiag_oid = :CmpDiag_oid";
					$queryParams['CmpDiag_oid'] = $data['CmpDiag_oid'];
				}

				// Пациент (карта)
				if (!empty($data['CmpTalon_id'])) {
					$filter .= " and CCC.CmpTalon_id = :CmpTalon_id";
					$queryParams['CmpTalon_id'] = $data['CmpTalon_id'];
				}

				// Пациент (карта)
				if (!empty($data['CmpTrauma_id'])) {
					$filter .= " and CCC.CmpTrauma_id = :CmpTrauma_id";
					$queryParams['CmpTrauma_id'] = $data['CmpTrauma_id'];
				}

				// Пациент (карта)
				if (!empty($data['Diag_sid'])) {
					$filter .= " and CCC.Diag_sid = :Diag_sid";
					$queryParams['Diag_sid'] = $data['Diag_sid'];
				}

				// Пациент (карта)
				if (!empty($data['Diag_uCode_From'])) {
					$filter .= " and UD.Diag_Code >= :Diag_uCode_From";
					$queryParams['Diag_uCode_From'] = $data['Diag_uCode_From'];
				}

				// Пациент (карта)
				if (!empty($data['Diag_uCode_To'])) {
					$filter .= " and UD.Diag_Code <= :Diag_uCode_To";
					$queryParams['Diag_uCode_To'] = $data['Diag_uCode_To'];
				}

				// Пациент (карта)
				if (!empty($data['Lpu_oid'])) {
					$filter .= " and CL.Lpu_id = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Вызов
				if (!empty($data['CmpArea_id'])) {
					$filter .= " and CCC.CmpArea_id = :CmpArea_id";
					$queryParams['CmpArea_id'] = $data['CmpArea_id'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_City'])) {
					$filter .= " and CCC.CmpCallCard_City like :CmpCallCard_City";
					$queryParams['CmpCallCard_City'] = $data['CmpCallCard_City'] . '%';
				}

				// Вызов
				if (!empty($data['CmpCallCard_Dom'])) {
					$filter .= " and CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
					$queryParams['CmpCallCard_Dom'] = $data['CmpCallCard_Dom'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Ktov'])) {
					$filter .= " and CCC.CmpCallerType_id = :CmpCallCard_Ktov";
					$queryParams['CmpCallCard_Ktov'] = $data['CmpCallCard_Ktov'];
				}

				// Вызов
				if (!empty($data['CmpResult_Code_From']) || !empty($data['CmpResult_Code_To'])) {
					$filter .= " and isnumeric(RTRIM(LTRIM(CRES.CmpResult_Code)) + 'e0') = 1";

					if (!empty($data['CmpResult_Code_From'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CRES.CmpResult_Code)) as bigint) >= :CmpResult_Code_From)";
						$queryParams['CmpResult_Code_From'] = $data['CmpResult_Code_From'];
					}

					if (!empty($data['CmpResult_Code_To'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CRES.CmpResult_Code)) as bigint)<= :CmpResult_Code_To)";
						$queryParams['CmpResult_Code_To'] = $data['CmpResult_Code_To'];
					}
				}

				if (!empty($data['CmpCallCardInputType_id'])) {
					$filter .= " and CCC.CmpCallCardInputType_id = :CmpCallCardInputType_id";
					$queryParams['CmpCallCardInputType_id'] = $data['CmpCallCardInputType_id'];
				}

				if (!empty($data['ResultDeseaseType_id'])) {
					$filter .= " and CCC.ResultDeseaseType_id = :ResultDeseaseType_id";
					$queryParams['ResultDeseaseType_id'] = $data['ResultDeseaseType_id'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Kvar'])) {
					$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
					$queryParams['CmpCallCard_Kvar'] = $data['CmpCallCard_Kvar'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Line'])) {
					$filter .= " and CCC.CmpCallCard_Line = :CmpCallCard_Line";
					$queryParams['CmpCallCard_Line'] = $data['CmpCallCard_Line'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Ngod'])) {
					$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
					$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
				}

				//Признаки вызова за день и год
				if ( $this->regionNick == 'penza' ) {
					if (!empty($data['CmpCallCard_NumvPr'])) {
						switch ($data['CmpCallCard_NumvPr']) {
							case 'А':
							case 'П':
							case 'И':
							case 'К':
							case 'Н':
								$filter .= " and CCC.CmpCallCard_NumvPr = :CmpCallCard_NumvPr";
								$queryParams['CmpCallCard_NumvPr'] = $data['CmpCallCard_NumvPr'];
								break;
							case 'Все':
								$filter .= " and CCC.CmpCallCard_NumvPr is not null";
								break;
							case 'Без признака':
								$filter .= " and CCC.CmpCallCard_NumvPr is null";
								break;
						}
					}

					if (!empty($data['CmpCallCard_NgodPr'])) {

						switch ($data['CmpCallCard_NgodPr']) {
							case 'А':
							case 'П':
							case 'И':
							case 'К':
							case 'Н':
								$filter .= " and CCC.CmpCallCard_NgodPr = :CmpCallCard_NgodPr";
								$queryParams['CmpCallCard_NgodPr'] = $data['CmpCallCard_NgodPr'];
								break;
							case 'Все':
								$filter .= " and CCC.CmpCallCard_NgodPr is not null";
								break;
							case 'Без признака':
								$filter .= " and CCC.CmpCallCard_NgodPr is null";
								break;
						}
					}
				}

				// Вызов
				if (isset($data['CmpCallCard_Prty'])) {
					$filter .= " and CCC.CmpCallCard_Prty = :CmpCallCard_Prty";
					$queryParams['CmpCallCard_Prty'] = $data['CmpCallCard_Prty'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Numv'])) {
					$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
					$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
				}

				// Вызов
				if (!empty($data['LpuBuilding_id'])) {
					$filter .= " and CCC.LpuBuilding_id = :LpuBuilding_id";
					$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
				}

				// Вызов
				if (!empty($data['Diag_Code_From'])) {
					$filter .= " and CLD.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				// Вызов
				if (!empty($data['Diag_Code_To'])) {
					$filter .= " and CLD.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				// Диагноз (Уфа)
				if (!empty($data['Diag_id'])) {
					$filter .= " and CLC.Diag_id = :Diag_id";
					$queryParams['Diag_id'] = $data['Diag_id'];
				}

				// Профиль бригады (Уфа)
				if (!empty($data['EmergencyTeamSpec_id'])) {
					$filter .= " and ET.EmergencyTeamSpec_id = :EmergencyTeamSpec_id";
					$queryParams['EmergencyTeamSpec_id'] = $data['EmergencyTeamSpec_id'];
				}

				// Номер бригады (Уфа)
				if (!empty($data['EmergencyTeamNum'])) {
					$filter .= " and CLC.EmergencyTeamNum = :EmergencyTeamNum";
					$queryParams['EmergencyTeamNum'] = $data['EmergencyTeamNum'];
				}

				// Номер от до
				if (!empty($data['CmpNumber_From']) || !empty($data['CmpNumber_To'])) {

					if (!empty($data['CmpNumber_From'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Numv)) as bigint) >= :CmpNumber_From)";
						$queryParams['CmpNumber_From'] = $data['CmpNumber_From'];
					}

					if (!empty($data['CmpNumber_To'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Numv)) as bigint)<= :CmpNumber_To)";
						$queryParams['CmpNumber_To'] = $data['CmpNumber_To'];
					}
				}

				// Номер за год от до
				if (!empty($data['CmpNumberGod_From']) || !empty($data['CmpNumberGod_To'])) {

					if (!empty($data['CmpNumberGod_From'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Ngod)) as bigint) >= :CmpNumberGod_From)";
						$queryParams['CmpNumberGod_From'] = $data['CmpNumberGod_From'];
					}

					if (!empty($data['CmpNumberGod_To'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Ngod)) as bigint)<= :CmpNumberGod_To)";
						$queryParams['CmpNumberGod_To'] = $data['CmpNumberGod_To'];
					}
				}

				// Вид оплаты
				if (!empty($data['PayType_id'])) {
					$filter .= " and CLC.PayType_id = :PayType_id";
					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Вызов
				if (isset($data['CmpCallCard_prmDate_Range'][0])) {

					if (isset($data['CmpCallCard_begTime'])) {
						$filter .= " and CCC.CmpCallCard_prmDT >= cast(:CmpCallCard_prmDate_Range_0 as datetime)";
						$data['CmpCallCard_prmDate_Range'][0] .= ' ' . $data['CmpCallCard_begTime'] . ':00';
					}else{
						$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= cast(:CmpCallCard_prmDate_Range_0 as datetime)";
					}

					$queryParams['CmpCallCard_prmDate_Range_0'] = $data['CmpCallCard_prmDate_Range'][0];
				}

				// Вызов
				if (isset($data['CmpCallCard_prmDate_Range'][1])) {
					if (isset($data['CmpCallCard_endTime'])) {
						$filter .= " and CCC.CmpCallCard_prmDT <= cast(:CmpCallCard_prmDate_Range_1 as datetime)";
						$data['CmpCallCard_prmDate_Range'][1] .= ' ' . $data['CmpCallCard_endTime'];
					}else{
						$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= cast(:CmpCallCard_prmDate_Range_1 as datetime)";
					}
					$queryParams['CmpCallCard_prmDate_Range_1'] = $data['CmpCallCard_prmDate_Range'][1];
				}

				// Вызов
				if (isset($data['CmpCallCard_begTime']) && !isset($data['CmpCallCard_prmDate_Range'][0])) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as time) >= cast(:CmpCallCard_begTime as time)";
					$queryParams['CmpCallCard_begTime'] = $data['CmpCallCard_begTime'];
				}

				// Вызов
				if (isset($data['CmpCallCard_endTime']) && !isset($data['CmpCallCard_prmDate_Range'][1])) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as time) <= cast(:CmpCallCard_endTime as time)";
					$queryParams['CmpCallCard_endTime'] = $data['CmpCallCard_endTime'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Numv'])) {
					$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
					$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
				}

				// Вызов
				if (isset($data['CmpCallCard_Sect'])) {
					$filter .= " and CCC.CmpCallCard_Sect = :CmpCallCard_Sect";
					$queryParams['CmpCallCard_Sect'] = $data['CmpCallCard_Sect'];
				}

				// Вызов
				if (isset($data['CmpCallCard_Stan'])) {
					$filter .= " and CCC.CmpCallCard_Stan = :CmpCallCard_Stan";
					$queryParams['CmpCallCard_Stan'] = $data['CmpCallCard_Stan'];
				}

				// В рамках задачи http://redmine.swan.perm.ru/issues/17135
				// @task https://redmine.swan.perm.ru/issues/126119
				// Добавлен учет региона при поиске талонов вызова по признаку вхождения в реестр
				if (in_array($this->regionNick, array('ekb', 'perm')) && isset($data['CmpCallCard_InRegistry'])) {
					$CmpCallCard_InRegistry = $data['CmpCallCard_InRegistry'];
					if ($CmpCallCard_InRegistry == 1) { //Только карты, не вошедшие в реестр
						$filter .= " and isnull(CCC.CmpCallCard_IsInReg,1) = 1";
					}
					if ($CmpCallCard_InRegistry == 2) { //Только карты, вошедшие в реестр
						$filter .= " and isnull(CCC.CmpCallCard_IsInReg,1) = 2";
					}
				}
				// Вызов
				if (!empty($data['CmpCallCard_Ulic'])) {
					$filter .= " and CCC.CmpCallCard_Ulic like :CmpCallCard_Ulic";
					$queryParams['CmpCallCard_Ulic'] = $data['CmpCallCard_Ulic'] . '%';
				}

				// Вызов
				if (!empty($data['CmpCallType_id'])) {
					$filter .= " and CCC.CmpCallType_id = :CmpCallType_id";
					$queryParams['CmpCallType_id'] = $data['CmpCallType_id'];
				}

				// Вызов
				if (!empty($data['CmpPlace_id'])) {
					$filter .= " and CCC.CmpPlace_id = :CmpPlace_id";
					$queryParams['CmpPlace_id'] = $data['CmpPlace_id'];
				}

				// Вызов
				if (!empty($data['CmpProfile_cid'])) {
					$filter .= " and CCC.CmpProfile_cid = :CmpProfile_cid";
					$queryParams['CmpProfile_cid'] = $data['CmpProfile_cid'];
				}

				// Вызов
				if (!empty($data['CmpReason_id'])) {
					$filter .= " and CCC.CmpReason_id = :CmpReason_id";
					$queryParams['CmpReason_id'] = $data['CmpReason_id'];
				}

				// Вызов
				if (!empty($data['CmpResult_id'])) {
					$filter .= " and CCC.CmpResult_id = :CmpResult_id";
					$queryParams['CmpResult_id'] = $data['CmpResult_id'];
				}

				// Вызов
				if (!empty($data['IsExtra'])) {
					$filter .= " and CCC.CmpCallCard_IsExtra = :CmpCallCard_IsExtra";
					$queryParams['CmpCallCard_IsExtra'] = $data['IsExtra'];
				}

				// Вызов
				if (!empty($data['Lpu_ppdid'])) {
					$filter .= " and CCC.Lpu_ppdid = :Lpu_ppdid";
					$queryParams['Lpu_ppdid'] = $data['Lpu_ppdid'];
				}

				// Вызов
				if (!empty($data['Lpu_hid'])) {
					$filter .= " and CCC.Lpu_hid = :Lpu_hid";
					$queryParams['Lpu_hid'] = $data['Lpu_hid'];
				}

				// Вызов
				if (!empty($data['acceptPPD'])) {
					$filter .= " and CCC.Lpu_ppdid is not null";
					if ($data['acceptPPD'] == 1) {
						//$filter .= " and CCC.CmpCallCardStatusType_id = 1";
						$filter .= " and acceptNmpFlag.CmpCallCardEvent_id is null";
					} else {
						//$filter .= " and CCC.CmpCallCardStatusType_id != 1";
						$filter .= " and acceptNmpFlag.CmpCallCardEvent_id is not null";
					}
				}

				// Адрес вызова
				/*
				  if ($data['CardKLRgn_id'] > 0 ) {
				  $filter .= " and CCC.KLRgn_id = :CardKLRgn_id";
				  $queryParams['CardKLRgn_id'] = $data['CardKLRgn_id'];
				  }
				 */

				// Адрес вызова
				if ($data['CardKLSubRgn_id'] > 0) {
					$filter .= " and CCC.KLSubRgn_id = :CardKLSubRgn_id";
					$queryParams['CardKLSubRgn_id'] = $data['CardKLSubRgn_id'];
				}

				// Адрес вызова
				if ($data['CardKLCity_id'] > 0) {
					$filter .= " and CCC.KLCity_id = :CardKLCity_id";
					$queryParams['CardKLCity_id'] = $data['CardKLCity_id'];
				}

				// Адрес вызова
				if ($data['CardKLTown_id'] > 0) {
					$filter .= " and CCC.KLTown_id = :CardKLTown_id";
					$queryParams['CardKLTown_id'] = $data['CardKLTown_id'];
				}

				// Адрес вызова
				if ($data['CardKLStreet_id'] > 0) {
					$filter .= " and CCC.KLStreet_id = :CardKLStreet_id";
					$queryParams['CardKLStreet_id'] = $data['CardKLStreet_id'];
				}

				// Адрес вызова
				if (strlen($data['CardAddress_House']) > 0) {
					$filter .= " and CCC.CmpCallCard_Dom = :CardAddress_House";
					$queryParams['CardAddress_House'] = $data['CardAddress_House'];
				}

				// Адрес вызова
				if (strlen($data['CardAddress_Corpus']) > 0) {
					$filter .= " and CCC.CmpCallCard_Korp = :CardAddress_Corpus";
					$queryParams['CardAddress_Corpus'] = $data['CardAddress_Corpus'];
				}

				// Адрес вызова
				if (strlen($data['CardAddress_Office']) > 0) {
					$filter .= " and CCC.CmpCallCard_Kvar = :CardAddress_Office";
					$queryParams['CardAddress_Office'] = $data['CardAddress_Office'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Dokt'])) {
					$filter .= " and CCC.CmpCallCard_Dokt like :CmpCallCard_Dokt";
					$queryParams['CmpCallCard_Dokt'] = $data['CmpCallCard_Dokt'] . '%';
				}

				// Бригада СМП
				if (isset($data['CmpCallCard_Kakp'])) {
					$filter .= " and CCC.CmpCallCard_Kakp = :CmpCallCard_Kakp";
					$queryParams['CmpCallCard_Kakp'] = $data['CmpCallCard_Kakp'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Kilo'])) {
					$filter .= " and CCC.CmpCallCard_Kilo = :CmpCallCard_Kilo";
					$queryParams['CmpCallCard_Kilo'] = $data['CmpCallCard_Kilo'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Ncar'])) {
					$filter .= " and CCC.CmpCallCard_Ncar = :CmpCallCard_Ncar";
					$queryParams['CmpCallCard_Ncar'] = $data['CmpCallCard_Ncar'];
				}

				// Бригада СМП
				if (isset($data['CmpCallCard_Numb'])) {
					$filter .= " and (CCC.CmpCallCard_Numb = :CmpCallCard_Numb OR ET.EmergencyTeam_Num = :CmpCallCard_Numb OR CLC.EmergencyTeamNum = :CmpCallCard_Numb)";
					$queryParams['CmpCallCard_Numb'] = $data['CmpCallCard_Numb'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Smpb'])) {
					$filter .= " and CCC.CmpCallCard_Smpb = :CmpCallCard_Smpb";
					$queryParams['CmpCallCard_Smpb'] = $data['CmpCallCard_Smpb'];
				}

				// Бригада СМП
				if (isset($data['CmpCallCard_Stbb'])) {
					$filter .= " and CCC.CmpCallCard_Stbb = :CmpCallCard_Stbb";
					$queryParams['CmpCallCard_Stbb'] = $data['CmpCallCard_Stbb'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Stbr'])) {
					$filter .= " and CCC.CmpCallCard_Stbr = :CmpCallCard_Stbr";
					$queryParams['CmpCallCard_Stbr'] = $data['CmpCallCard_Stbr'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tab2'])) {
					$filter .= " and CCC.CmpCallCard_Tab2 = :CmpCallCard_Tab2";
					$queryParams['CmpCallCard_Tab2'] = $data['CmpCallCard_Tab2'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tab3'])) {
					$filter .= " and CCC.CmpCallCard_Tab3 = :CmpCallCard_Tab3";
					$queryParams['CmpCallCard_Tab3'] = $data['CmpCallCard_Tab3'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tab4'])) {
					$filter .= " and CCC.CmpCallCard_Tab4 = :CmpCallCard_Tab4";
					$queryParams['CmpCallCard_Tab4'] = $data['CmpCallCard_Tab4'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tabn'])) {
					$filter .= " and CCC.CmpCallCard_Tabn = :CmpCallCard_Tabn";
					$queryParams['CmpCallCard_Tabn'] = $data['CmpCallCard_Tabn'];
				}

				// Бригада СМП
				if (!empty($data['CmpProfile_bid'])) {
					$filter .= " and (CCC.CmpProfile_bid = :CmpProfile_bid OR ET.EmergencyTeamSpec_id = :CmpProfile_bid OR CLC.EmergencyTeamSpec_id = :CmpProfile_bid)";
					$queryParams['CmpProfile_bid'] = $data['CmpProfile_bid'];
				}

				// Бригада СМП
				if (!empty($data['CLLpuBuilding_id'])) {
					$filter .= " and CCC.LpuBuilding_id = :CLLpuBuilding_id";
					$queryParams['CLLpuBuilding_id'] = $data['CLLpuBuilding_id'];
				}
				// Бригада СМП
				if (!empty($data['ETMedStaffFact_id'])) {
					$filter .= " and ET.EmergencyTeam_HeadShiftWorkPlace = :ETMedStaffFact_id";
					$queryParams['ETMedStaffFact_id'] = $data['ETMedStaffFact_id'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_D201'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_D201, 1, PATINDEX('% %', CCC.CmpCallCard_D201)) = :CmpCallCard_D201";
					$queryParams['CmpCallCard_D201'] = $data['CmpCallCard_D201'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dlit'])) {
					$filter .= " and CCC.CmpCallCard_Dlit = :CmpCallCard_Dlit";
					$queryParams['CmpCallCard_Dlit'] = $data['CmpCallCard_Dlit'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dsp1'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp1, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp1)) = :CmpCallCard_Dsp1";
					$queryParams['CmpCallCard_Dsp1'] = $data['CmpCallCard_Dsp1'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dsp2'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp2, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp2)) = :CmpCallCard_Dsp2";
					$queryParams['CmpCallCard_Dsp2'] = $data['CmpCallCard_Dsp2'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dsp3'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp3, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp3)) = :CmpCallCard_Dsp3";
					$queryParams['CmpCallCard_Dsp3'] = $data['CmpCallCard_Dsp3'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dspp'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dspp, 1, PATINDEX('% %', CCC.CmpCallCard_Dspp)) = :CmpCallCard_Dspp";
					$queryParams['CmpCallCard_Dspp'] = $data['CmpCallCard_Dspp'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Prdl'])) {
					$filter .= " and CCC.CmpCallCard_Prdl = :CmpCallCard_Prdl";
					$queryParams['CmpCallCard_Prdl'] = $data['CmpCallCard_Prdl'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Smpp'])) {
					$filter .= " and CCC.CmpCallCard_Smpp = :CmpCallCard_Smpp";
					$queryParams['CmpCallCard_Smpp'] = $data['CmpCallCard_Smpp'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Vr51'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Vr51, 1, PATINDEX('% %', CCC.CmpCallCard_Vr51)) = :CmpCallCard_Vr51";
					$queryParams['CmpCallCard_Vr51'] = $data['CmpCallCard_Vr51'];
				}
				break;

			case 'CmpCloseCard':

				if (in_array($data['session']['region']['nick'], array('kz'))) {
					$reasonCase = "";
				} else {
					$reasonCase = "left join CmpReason CR with (nolock) on CR.CmpReason_id = CLC.CallPovod_id";
				}

				$query .= "
					v_CmpCallCard CCC with (nolock)
					left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CLC.CmpCallCard_id = CCC.CmpCallCard_id
					left join v_CmpCallCardCostPrint ccp (nolock) on ccp.CmpCallCard_id = ccc.CmpCallCard_id
					$reasonCase
					left join CmpReason CRTalon with (nolock) on CRTalon.CmpReason_id = CCC.CmpReason_id
					left join CmpResult CRES with (nolock) on CRES.CmpResult_id = CCC.CmpResult_id
					left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
					left join EmergencyTeam ET with (nolock) on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
					left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
					left join v_Lpu Lpu with (nolock) on CCC.CmpLpu_id = Lpu.Lpu_id
                    left join v_Lpu LpuHid with (nolock) on CCC.CmpLpu_id = LpuHid.Lpu_id
					left join v_Diag CLD with (nolock) on CLD.Diag_id = CLC.Diag_id					
					left join v_Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
					left join v_CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
					left join v_Diag UD with (nolock) on UD.Diag_id = CCC.Diag_uid
					left join v_PersonState PS with (nolock) on CCC.Person_id = PS.Person_id
					left join v_PersonState PSCLC with (nolock) on CLC.Person_id = PSCLC.Person_id
					left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
					left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
					left join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
					left join v_LpuBuilding ETLB with (nolock) on ETLB.LpuBuilding_id = ET.LpuBuilding_id
					left join CmpCallCardInputType CCCInput with (nolock) on CCCInput.CmpCallCardInputType_id = CCC.CmpCallCardInputType_id
					outer apply (
						SELECT TOP 1
							CCLCR.CmpCloseCardCombo_id,
							CCLCR.Localize
						FROM {$this->schema}.v_CmpCloseCardRel CCLCR with (nolock)
						LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo CCLCB (nolock) on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id
						WHERE CCLCR.CmpCloseCard_id = CLC.CmpCloseCard_id and CCLCB.CmpCloseCardCombo_Code = 693
						ORDER BY CCLCR.CmpCloseCardRel_id desc
					) isActiveCombo
					outer apply (
						SELECT TOP 1
							CCLCR.CmpCloseCardCombo_id,
							CCLCR.Localize
						FROM {$this->schema}.v_CmpCloseCardRel CCLCR with (nolock)
						LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo CCLCB (nolock) on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id
						LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo parentCCLCB (nolock) on parentCCLCB.CmpCloseCardCombo_id = CCLCB.Parent_id

						WHERE CCLCR.CmpCloseCard_id = CLC.CmpCloseCard_id and parentCCLCB.CmpCloseCardCombo_Code = 142
						ORDER BY CCLCR.CmpCloseCardRel_id desc
					) socialCombo
					outer apply (
						SELECT TOP 1
							CCLCR.CmpCloseCardCombo_id,
							CCLCR.Localize
						FROM {$this->schema}.v_CmpCloseCardRel CCLCR with (nolock)
						LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo CCLCB (nolock) on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id
						LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo parentCCLCB (nolock) on parentCCLCB.CmpCloseCardCombo_id = CCLCB.Parent_id

						WHERE CCLCR.CmpCloseCard_id = CLC.CmpCloseCard_id and parentCCLCB.CmpCloseCardCombo_Code = 223
						ORDER BY CCLCR.CmpCloseCardRel_id desc
					) resultCombo
					OUTER apply (
						SELECT top 1
						CCCE.CmpCallCardEvent_id
						FROM v_CmpCallCardEvent CCCE with (nolock)
							LEFT JOIN v_CmpCallCardEventType CCCET with(nolock) on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id
						WHERE CCCE.CmpCallCard_id = CCC.CmpCallCard_id AND CCCET.CmpCallCardEventType_Code = 6
					) as acceptNmpFlag

				";

				// Фильтр по ЛПУ
				if (array_key_exists('linkedLpuIdList', $data['session']) && false) {
					$filter .= " and (LB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")
								or (CCC.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and CCC.LpuBuilding_id is NUll ) )";
				} else if (!empty($data['Lpu_id'])) {
					$filter .= " and (LB.Lpu_id = :Lpu_id or (CCC.Lpu_id = :Lpu_id and CCC.LpuBuilding_id is NUll) )";
					$queryParams['Lpu_id'] = $data['Lpu_id'];
				}

				// Пациент (карта)
				if (!empty($data['CmpCloseCard_id'])) {
					$filter .= " and CLC.CmpCloseCard_id = :CmpCloseCard_id";
					$queryParams['CmpCloseCard_id'] = $data['CmpCloseCard_id'];
				}

				// Пациент (карта)
				if (!empty($data['CmpArea_gid'])) {
					$filter .= " and CCC.CmpArea_gid = :CmpArea_gid";
					$queryParams['CmpArea_gid'] = $data['CmpArea_gid'];
				}

				// Пациент (карта)
				if (isset($data['CmpCallCard_Expo'])) {
					$filter .= " and CCC.CmpCallCard_Expo = :CmpCallCard_Expo";
					$queryParams['CmpCallCard_Expo'] = $data['CmpCallCard_Expo'];
				}

				// Пациент (карта)
				if (!empty($data['CmpCallCard_IsAlco'])) {
					$filter .= " and CLC.isAlco = :CmpCallCard_IsAlco";
					$queryParams['CmpCallCard_IsAlco'] = $data['CmpCallCard_IsAlco'];
				}

				// Пациент (карта)
				if (!empty($data['CmpCallCard_IsPoli'])) {
					$filter .= " and CCC.CmpCallCard_IsPoli = :CmpCallCard_IsPoli";
					$queryParams['CmpCallCard_IsPoli'] = $data['CmpCallCard_IsPoli'];
				}

				if (!empty($data['CmpCallCard_isPaid'])) {
					$filter .= " and ISNULL(CCC.CmpCallCard_isPaid,1) = :CmpCallCard_isPaid";
					$queryParams['CmpCallCard_isPaid'] = $data['CmpCallCard_isPaid'];
				}

				// Пациент (карта)
				if (!empty($data['CmpDiag_aid'])) {
					$filter .= " and CCC.CmpDiag_aid = :CmpDiag_aid";
					$queryParams['CmpDiag_aid'] = $data['CmpDiag_aid'];
				}

				// Пациент (карта)
				if (!empty($data['CmpDiag_oid'])) {
					$filter .= " and CCC.CmpDiag_oid = :CmpDiag_oid";
					$queryParams['CmpDiag_oid'] = $data['CmpDiag_oid'];
				}

				// Пациент (карта)
				if (!empty($data['CmpTalon_id'])) {
					$filter .= " and CCC.CmpTalon_id = :CmpTalon_id";
					$queryParams['CmpTalon_id'] = $data['CmpTalon_id'];
				}

				// Пациент (карта)
				if (!empty($data['CmpTrauma_id'])) {
					$filter .= " and CCC.CmpTrauma_id = :CmpTrauma_id";
					$queryParams['CmpTrauma_id'] = $data['CmpTrauma_id'];
				}

				// Пациент (карта)
				if (!empty($data['Diag_sid'])) {
					$filter .= " and CCC.Diag_sid = :Diag_sid";
					$queryParams['Diag_sid'] = $data['Diag_sid'];
				}

				// Пациент (карта)
				if (!empty($data['Diag_uCode_From'])) {
					$filter .= " and UD.Diag_Code >= :Diag_uCode_From";
					$queryParams['Diag_uCode_From'] = $data['Diag_uCode_From'];
				}

				// Пациент (карта)
				if (!empty($data['Diag_uCode_To'])) {
					$filter .= " and UD.Diag_Code <= :Diag_uCode_To";
					$queryParams['Diag_uCode_To'] = $data['Diag_uCode_To'];
				}

				// Пациент (карта)
				if (!empty($data['Lpu_oid'])) {
					$filter .= " and CL.Lpu_id = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Вызов
				if (!empty($data['CmpArea_id'])) {
					$filter .= " and CCC.CmpArea_id = :CmpArea_id";
					$queryParams['CmpArea_id'] = $data['CmpArea_id'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_City'])) {
					$filter .= " and CCC.CmpCallCard_City like :CmpCallCard_City";
					$queryParams['CmpCallCard_City'] = $data['CmpCallCard_City'] . '%';
				}

				// Вызов
				if (!empty($data['CmpCallCard_Dom'])) {
					$filter .= " and CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
					$queryParams['CmpCallCard_Dom'] = $data['CmpCallCard_Dom'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Ktov'])) {
					$filter .= " and CCC.CmpCallerType_id = :CmpCallCard_Ktov";
					$queryParams['CmpCallCard_Ktov'] = $data['CmpCallCard_Ktov'];
				}

				// Вызов
				if (!empty($data['Diag_Code_From'])) {
					$filter .= " and CLD.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				// Вызов
				if (!empty($data['Diag_Code_To'])) {
					$filter .= " and CLD.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				// Вызов
				if (!empty($data['CmpResult_Code_From']) || !empty($data['CmpResult_Code_To'])) {
					$filter .= " and isnumeric(RTRIM(LTRIM(CRES.CmpResult_Code)) + 'e0') = 1";

					if (!empty($data['CmpResult_Code_From'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CRES.CmpResult_Code)) as bigint) >= :CmpResult_Code_From)";
						$queryParams['CmpResult_Code_From'] = $data['CmpResult_Code_From'];
					}

					if (!empty($data['CmpResult_Code_To'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CRES.CmpResult_Code)) as bigint)<= :CmpResult_Code_To)";
						$queryParams['CmpResult_Code_To'] = $data['CmpResult_Code_To'];
					}
				}
				if (!empty($data['CmpCallCardInputType_id'])) {
					$filter .= " and CCC.CmpCallCardInputType_id = :CmpCallCardInputType_id";
					$queryParams['CmpCallCardInputType_id'] = $data['CmpCallCardInputType_id'];
				}
				if (!empty($data['ResultDeseaseType_id'])) {
					$filter .= " and CCC.ResultDeseaseType_id = :ResultDeseaseType_id";
					$queryParams['ResultDeseaseType_id'] = $data['ResultDeseaseType_id'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Kvar'])) {
					$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
					$queryParams['CmpCallCard_Kvar'] = $data['CmpCallCard_Kvar'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Line'])) {
					$filter .= " and CCC.CmpCallCard_Line = :CmpCallCard_Line";
					$queryParams['CmpCallCard_Line'] = $data['CmpCallCard_Line'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Ngod'])) {
					$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
					$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
				}

				// Вызов
				if (isset($data['CmpCallCard_Prty'])) {
					$filter .= " and CCC.CmpCallCard_Prty = :CmpCallCard_Prty";
					$queryParams['CmpCallCard_Prty'] = $data['CmpCallCard_Prty'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Numv'])) {
					$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
					$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
				}

				// Вызов
				if (!empty($data['IsExtra'])) {
					$filter .= " and CCC.CmpCallCard_IsExtra = :CmpCallCard_IsExtra";
					$queryParams['CmpCallCard_IsExtra'] = $data['IsExtra'];
				}

				// Вызов
				if (!empty($data['Lpu_ppdid'])) {
					$filter .= " and CCC.Lpu_ppdid = :Lpu_ppdid";
					$queryParams['Lpu_ppdid'] = $data['Lpu_ppdid'];
				}

				// Вызов
				if (!empty($data['Lpu_hid'])) {
					$filter .= " and CCC.Lpu_hid = :Lpu_hid";
					$queryParams['Lpu_hid'] = $data['Lpu_hid'];
				}

				// Вызов
				if (!empty($data['acceptPPD'])) {
					$filter .= " and CCC.Lpu_ppdid is not null";
					if ($data['acceptPPD'] == 1) {
						//$filter .= " and CCC.CmpCallCardStatusType_id = 1";
						$filter .= " and acceptNmpFlag.CmpCallCardEvent_id is null";
					} else {
						//$filter .= " and CCC.CmpCallCardStatusType_id != 1";
						$filter .= " and acceptNmpFlag.CmpCallCardEvent_id is not null";
					}
				}

				// Вызов
				if (!empty($data['isActive'])) {
					if ($data['isActive'] == 1) {
						$filter .= " and isActiveCombo.CmpCloseCardCombo_id is null";
					} else {
						$filter .= " and isActiveCombo.CmpCloseCardCombo_id is not null";
					}
				}

				// Вызов
				if (!empty($data['PersonSocial_id'])) {
					$filter .= " and socialCombo.CmpCloseCardCombo_id = :PersonSocial_id";
					$queryParams['PersonSocial_id'] = $data['PersonSocial_id'];
				}

				// Диагноз (Уфа)
				if (!empty($data['Diag_id'])) {
					$filter .= " and CLC.Diag_id = :Diag_id";
					$queryParams['Diag_id'] = $data['Diag_id'];
				}

				// Профиль бригады (Уфа)
				if (!empty($data['EmergencyTeamSpec_id'])) {
					$filter .= " and (CLC.EmergencyTeamSpec_id = :EmergencyTeamSpec_id_CLC OR ET.EmergencyTeamSpec_id = :EmergencyTeamSpec_id_ET)";
					$queryParams['EmergencyTeamSpec_id_CLC'] = $data['EmergencyTeamSpec_id'];
					$queryParams['EmergencyTeamSpec_id_ET'] = $data['EmergencyTeamSpec_id'];
				}

				// Номер бригады (Уфа)
				if (!empty($data['EmergencyTeamNum'])) {
					$filter .= " and CLC.EmergencyTeamNum = :EmergencyTeamNum";
					$queryParams['EmergencyTeamNum'] = $data['EmergencyTeamNum'];
				}

				// Номер от до
				if (!empty($data['CmpNumber_From']) || !empty($data['CmpNumber_To'])) {

					if (!empty($data['CmpNumber_From'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Numv)) as bigint) >= :CmpNumber_From)";
						$queryParams['CmpNumber_From'] = $data['CmpNumber_From'];
					}

					if (!empty($data['CmpNumber_To'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Numv)) as bigint)<= :CmpNumber_To)";
						$queryParams['CmpNumber_To'] = $data['CmpNumber_To'];
					}
				}

				// Номер за год от до
				if (!empty($data['CmpNumberGod_From']) || !empty($data['CmpNumberGod_To'])) {

					if (!empty($data['CmpNumberGod_From'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Ngod)) as bigint) >= :CmpNumberGod_From)";
						$queryParams['CmpNumberGod_From'] = $data['CmpNumberGod_From'];
					}

					if (!empty($data['CmpNumberGod_To'])) {
						$filter .= " and (CAST(RTRIM(LTRIM(CCC.CmpCallCard_Ngod)) as bigint)<= :CmpNumberGod_To)";
						$queryParams['CmpNumberGod_To'] = $data['CmpNumberGod_To'];
					}
				}

				// Вид оплаты
				if (!empty($data['PayType_id'])) {
					$filter .= " and CLC.PayType_id = :PayType_id";
					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Вызов
				if (isset($data['CmpCallCard_prmDate_Range'][0])) {

					if (isset($data['CmpCallCard_begTime'])) {
						$filter .= " and CCC.CmpCallCard_prmDT >= cast(:CmpCallCard_prmDate_Range_0 as datetime)";
						$data['CmpCallCard_prmDate_Range'][0] .= ' ' . $data['CmpCallCard_begTime'];
					}else{
						$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= cast(:CmpCallCard_prmDate_Range_0 as datetime)";
					}
					$queryParams['CmpCallCard_prmDate_Range_0'] = $data['CmpCallCard_prmDate_Range'][0];
				}

				// Вызов
				if (isset($data['CmpCallCard_prmDate_Range'][1])) {

					if (isset($data['CmpCallCard_endTime'])) {
						$filter .= " and CCC.CmpCallCard_prmDT <= cast(:CmpCallCard_prmDate_Range_1 as datetime)";
						$data['CmpCallCard_prmDate_Range'][1] .= ' ' . $data['CmpCallCard_endTime'];
					}else{
						$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= cast(:CmpCallCard_prmDate_Range_1 as datetime)";
					}
					$queryParams['CmpCallCard_prmDate_Range_1'] = $data['CmpCallCard_prmDate_Range'][1];
				}

				// Вызов
				if (isset($data['CmpCallCard_begTime']) && !isset($data['CmpCallCard_prmDate_Range'][0])) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as time) >= cast(:CmpCallCard_begTime as time)";
					$queryParams['CmpCallCard_begTime'] = $data['CmpCallCard_begTime'];
				}

				// Вызов
				if (isset($data['CmpCallCard_endTime']) && !isset($data['CmpCallCard_prmDate_Range'][1])) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as time) <= cast(:CmpCallCard_endTime as time)";
					$queryParams['CmpCallCard_endTime'] = $data['CmpCallCard_endTime'];
				}

				// Вызов
				if (!empty($data['CmpCallCard_Numv'])) {
					$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
					$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
				}

				// Вызов
				if (isset($data['CmpCallCard_Sect'])) {
					$filter .= " and CCC.CmpCallCard_Sect = :CmpCallCard_Sect";
					$queryParams['CmpCallCard_Sect'] = $data['CmpCallCard_Sect'];
				}

				// Вызов
				if (isset($data['CmpCallCard_Stan'])) {
					$filter .= " and CCC.CmpCallCard_Stan = :CmpCallCard_Stan";
					$queryParams['CmpCallCard_Stan'] = $data['CmpCallCard_Stan'];
				}
				if (isset($data['CmpCallCard_InRegistry'])) { //В рамках задачи http://redmine.swan.perm.ru/issues/17135
					$CmpCallCard_InRegistry = $data['CmpCallCard_InRegistry'];
					// @task https://redmine.swan.perm.ru/issues/126119
					// Учетно поле, которое содержит признак вхождения в реестр
					$IsInRegField = (in_array($this->regionNick, array('ekb', 'perm')) ? 'CCC.CmpCallCard_IsInReg' : 'CLC.CmpCloseCard_IsInReg');
					if ($CmpCallCard_InRegistry == 1) { //Только карты, не вошедшие в реестр
						$filter .= " and isnull({$IsInRegField},1) = 1";
					}
					if ($CmpCallCard_InRegistry == 2) { //Только карты, вошедшие в реестр
						$filter .= " and isnull({$IsInRegField},1) = 2";
					}
				}
				// Вызов
				if (!empty($data['CmpCallCard_Ulic'])) {
					$filter .= " and CCC.CmpCallCard_Ulic like :CmpCallCard_Ulic";
					$queryParams['CmpCallCard_Ulic'] = $data['CmpCallCard_Ulic'] . '%';
				}

				// Вызов
				if (!empty($data['CmpCallType_id'])) {
					$filter .= " and CCC.CmpCallType_id = :CmpCallType_id";
					$queryParams['CmpCallType_id'] = $data['CmpCallType_id'];
				}

				// Вызов
				if (!empty($data['CmpPlace_id'])) {
					$filter .= " and CCC.CmpPlace_id = :CmpPlace_id";
					$queryParams['CmpPlace_id'] = $data['CmpPlace_id'];
				}

				// Вызов
				if (!empty($data['CmpProfile_cid'])) {
					$filter .= " and CCC.CmpProfile_cid = :CmpProfile_cid";
					$queryParams['CmpProfile_cid'] = $data['CmpProfile_cid'];
				}

				// Вызов
				if (!empty($data['CmpReason_id'])) {
					$filter .= " and CCC.CmpReason_id = :CmpReason_id";
					$queryParams['CmpReason_id'] = $data['CmpReason_id'];
				}

				// Вызов
				if (!empty($data['ResultUfa_id'])) {
					$filter .= " and resultCombo.CmpCloseCardCombo_id = :ResultUfa_id";
					$queryParams['ResultUfa_id'] = $data['ResultUfa_id'];
				}

				// Вызов
				if (!empty($data['CmpResult_id'])) {
					$filter .= " and ISNULL(CLC.CmpResult_id, CCC.CmpResult_id) = :CmpResult_id";
					$queryParams['CmpResult_id'] = $data['CmpResult_id'];
				}

				// Вызов
				if (!empty($data['LpuBuilding_id'])) {
					$filter .= " and CLC.LpuBuilding_id = :LpuBuilding_id";
					$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
				}

				// Вызов
				if ($data['session']['region']['nick'] == 'perm' || $data['session']['region']['nick'] == 'kareliya') {
					$filter .= " and (CLC.CmpCloseCard_id > 0 or CCC.CmpCallCardInputType_id is not null)";
				} else {
					$filter .= " and (CLC.CmpCloseCard_id > 0)";
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Dokt'])) {
					$filter .= " and CCC.CmpCallCard_Dokt like :CmpCallCard_Dokt";
					$queryParams['CmpCallCard_Dokt'] = $data['CmpCallCard_Dokt'] . '%';
				}

				// Бригада СМП
				if (isset($data['CmpCallCard_Kakp'])) {
					$filter .= " and CCC.CmpCallCard_Kakp = :CmpCallCard_Kakp";
					$queryParams['CmpCallCard_Kakp'] = $data['CmpCallCard_Kakp'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Kilo'])) {
					$filter .= " and CCC.CmpCallCard_Kilo = :CmpCallCard_Kilo";
					$queryParams['CmpCallCard_Kilo'] = $data['CmpCallCard_Kilo'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Ncar'])) {
					$filter .= " and CCC.CmpCallCard_Ncar = :CmpCallCard_Ncar";
					$queryParams['CmpCallCard_Ncar'] = $data['CmpCallCard_Ncar'];
				}

				// Бригада СМП
				if (isset($data['CmpCallCard_Numb'])) {
					$filter .= " and (CCC.CmpCallCard_Numb = :CmpCallCard_Numb OR ET.EmergencyTeam_Num = :CmpCallCard_Numb OR CLC.EmergencyTeamNum = :CmpCallCard_Numb)";
					$queryParams['CmpCallCard_Numb'] = $data['CmpCallCard_Numb'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Smpb'])) {
					$filter .= " and CCC.CmpCallCard_Smpb = :CmpCallCard_Smpb";
					$queryParams['CmpCallCard_Smpb'] = $data['CmpCallCard_Smpb'];
				}

				// Бригада СМП
				if (isset($data['CmpCallCard_Stbb'])) {
					$filter .= " and CCC.CmpCallCard_Stbb = :CmpCallCard_Stbb";
					$queryParams['CmpCallCard_Stbb'] = $data['CmpCallCard_Stbb'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Stbr'])) {
					$filter .= " and CCC.CmpCallCard_Stbr = :CmpCallCard_Stbr";
					$queryParams['CmpCallCard_Stbr'] = $data['CmpCallCard_Stbr'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tab2'])) {
					$filter .= " and CCC.CmpCallCard_Tab2 = :CmpCallCard_Tab2";
					$queryParams['CmpCallCard_Tab2'] = $data['CmpCallCard_Tab2'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tab3'])) {
					$filter .= " and CCC.CmpCallCard_Tab3 = :CmpCallCard_Tab3";
					$queryParams['CmpCallCard_Tab3'] = $data['CmpCallCard_Tab3'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tab4'])) {
					$filter .= " and CCC.CmpCallCard_Tab4 = :CmpCallCard_Tab4";
					$queryParams['CmpCallCard_Tab4'] = $data['CmpCallCard_Tab4'];
				}

				// Бригада СМП
				if (!empty($data['CmpCallCard_Tabn'])) {
					$filter .= " and CCC.CmpCallCard_Tabn = :CmpCallCard_Tabn";
					$queryParams['CmpCallCard_Tabn'] = $data['CmpCallCard_Tabn'];
				}

				// Бригада СМП
				if (!empty($data['CmpProfile_bid'])) {
					$filter .= " and (CCC.CmpProfile_bid = :CmpProfile_bid OR ET.EmergencyTeamSpec_id = :CmpProfile_bid OR CLC.EmergencyTeamSpec_id = :CmpProfile_bid)";
					$queryParams['CmpProfile_bid'] = $data['CmpProfile_bid'];
				}

				// Бригада СМП
				if (!empty($data['CLLpuBuilding_id'])) {
					$filter .= " and CLC.LpuBuilding_id = :CLLpuBuilding_id";
					$queryParams['CLLpuBuilding_id'] = $data['CLLpuBuilding_id'];
				}

				// Бригада СМП
				if (!empty($data['ETMedStaffFact_id'])) {
					$filter .= " and ET.EmergencyTeam_HeadShiftWorkPlace = :ETMedStaffFact_id";
					$queryParams['ETMedStaffFact_id'] = $data['ETMedStaffFact_id'];
				}

				// Адрес вызова
				/*
				  if ($data['CardKLRgn_id'] > 0 ) {
				  $filter .= " and CCC.KLRgn_id = :CardKLRgn_id";
				  $queryParams['CardKLRgn_id'] = $data['CardKLRgn_id'];
				  }
				 */

				// Адрес вызова
				if ($data['CardKLSubRgn_id'] > 0) {
					$filter .= " and CLC.Area_id = :CardKLSubRgn_id";
					$queryParams['CardKLSubRgn_id'] = $data['CardKLSubRgn_id'];
				}

				// Адрес вызова
				if ($data['CardKLCity_id'] > 0) {
					$filter .= " and CLC.City_id = :CardKLCity_id";
					$queryParams['CardKLCity_id'] = $data['CardKLCity_id'];
				}

				// Адрес вызова
				if ($data['CardKLTown_id'] > 0) {
					$filter .= " and CLC.Town_id = :CardKLTown_id";
					$queryParams['CardKLTown_id'] = $data['CardKLTown_id'];
				}

				// Адрес вызова
				if ($data['CardKLStreet_id'] > 0) {
					$filter .= " and CLC.Street_id = :CardKLStreet_id";
					$queryParams['CardKLStreet_id'] = $data['CardKLStreet_id'];
				}

				// Адрес вызова
				if (strlen($data['CardAddress_House']) > 0) {
					$filter .= " and CLC.House = :CardAddress_House";
					$queryParams['CardAddress_House'] = $data['CardAddress_House'];
				}

				// Адрес вызова
				if (strlen($data['CardAddress_Corpus']) > 0) {
					$filter .= " and CLC.Korpus = :CardAddress_Corpus";
					$queryParams['CardAddress_Corpus'] = $data['CardAddress_Corpus'];
				}

				// Адрес вызова
				if (strlen($data['CardAddress_Office']) > 0) {
					$filter .= " and CLC.Office = :CardAddress_Office";
					$queryParams['CardAddress_Office'] = $data['CardAddress_Office'];
				}


				// Управление вызовом
				if (!empty($data['CmpCallCard_D201'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_D201, 1, PATINDEX('% %', CCC.CmpCallCard_D201)) = :CmpCallCard_D201";
					$queryParams['CmpCallCard_D201'] = $data['CmpCallCard_D201'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dlit'])) {
					$filter .= " and CCC.CmpCallCard_Dlit = :CmpCallCard_Dlit";
					$queryParams['CmpCallCard_Dlit'] = $data['CmpCallCard_Dlit'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dsp1'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp1, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp1)) = :CmpCallCard_Dsp1";
					$queryParams['CmpCallCard_Dsp1'] = $data['CmpCallCard_Dsp1'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dsp2'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp2, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp2)) = :CmpCallCard_Dsp2";
					$queryParams['CmpCallCard_Dsp2'] = $data['CmpCallCard_Dsp2'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dsp3'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp3, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp3)) = :CmpCallCard_Dsp3";
					$queryParams['CmpCallCard_Dsp3'] = $data['CmpCallCard_Dsp3'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Dspp'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dspp, 1, PATINDEX('% %', CCC.CmpCallCard_Dspp)) = :CmpCallCard_Dspp";
					$queryParams['CmpCallCard_Dspp'] = $data['CmpCallCard_Dspp'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Prdl'])) {
					$filter .= " and CCC.CmpCallCard_Prdl = :CmpCallCard_Prdl";
					$queryParams['CmpCallCard_Prdl'] = $data['CmpCallCard_Prdl'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Smpp'])) {
					$filter .= " and CCC.CmpCallCard_Smpp = :CmpCallCard_Smpp";
					$queryParams['CmpCallCard_Smpp'] = $data['CmpCallCard_Smpp'];
				}

				// Управление вызовом
				if (!empty($data['CmpCallCard_Vr51'])) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Vr51, 1, PATINDEX('% %', CCC.CmpCallCard_Vr51)) = :CmpCallCard_Vr51";
					$queryParams['CmpCallCard_Vr51'] = $data['CmpCallCard_Vr51'];
				}
				break;

			case 'PersonPrivilegeWOW':
				// Хотя здесь должен быть inner - пока к сожалению на тестовой базе нет регистра (с) Night
				$query .= " inner join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id";
				$query .= " left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id";
				$query .= " left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id";
				$query .= " left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id";
				$query .= " left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id";
				if (isset($data['PrivilegeTypeWow_id'])) {
					$filter .= " and PTW.PrivilegeTypeWow_id = :PrivilegeTypeWow_id ";
					$queryParams['PrivilegeTypeWow_id'] = $data['PrivilegeTypeWow_id'];
				}
				break;

				//Скрининг населения 60+
			case 'RegisterSixtyPlus':
				$query .= "
				left join dbo.v_PersonState PS WITH (NOLOCK) on PS.Person_id = RPlus.Person_id
				left join dbo.v_PersonCardState card WITH (NOLOCK)  on RPlus.Person_id = card.Person_id and card.LpuAttachType_id = 1
				left join dbo.v_LpuRegion rg WITH (NOLOCK) on card.LpuRegion_id = rg.LpuRegion_id
				left join dbo.LpuRegion as LpuRegionFap on card.LpuRegion_fapid = LpuRegionFap.LpuRegion_id
				left join dbo.v_InvalidGroupType IGT with (nolock) on IGT.InvalidGroupType_id = RPlus.InvalidGroupType_id
					";

				// Тип записи регистра
				/*if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						$filter .= " and PS.Person_DeadDT is null ";
					} elseif ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and PS.Person_DeadDT is not null ";
					}
				}*/

				//Состоит на дисп учете
				if (isset($data['YesNo_id']) && $data['YesNo_id'] != '') {
					if ($data['YesNo_id'] == 2) {
						$filter .= " and RPlus.RegisterSixtyPlus_isSetPersonDisp = 1";
					} else {
						$filter .= " and RPlus.RegisterSixtyPlus_isSetPersonDisp = 0";
					}
				}

				//Профиль наблюдения
				switch ($data['ProfileData']) {
					case 1:
						$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileZNO = 1";
						break;
					case 2:
						$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileONMK = 1";
						break;
					case 3:
						$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileOKS = 1";
						break;
					case 4:
						$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileBSK = 1";
						break;
					case 5:
						$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileDiabetes = 1";
						break;
					default :
						$filter .= '';
						break;
				}
				//Инвалидность
				if (isset($data['DisabilityData_id']) && $data['DisabilityData_id'] != '') {
					$filter .= ' and RPlus.InvalidGroupType_id =:DisabilityData_id';
					$queryParams['DisabilityData_id'] = $data['DisabilityData_id'];
				}
				//онкоконтроль
				if (isset($data['OnkoCtrComment_id']) && $data['OnkoCtrComment_id'] != '') {
					if ($data['OnkoCtrComment_id'] == 1) {
						$filter .= " and RPlus.RegisterSixtyPlus_OnkoControlIsNeeded = 1";
					} else if ($data['OnkoCtrComment_id'] == 2) {
						$filter .= " and RPlus.RegisterSixtyPlus_OnkoControlIsNeeded = 0";
					} else {
						$filter .= " and RPlus.RegisterSixtyPlus_OnkoProfileDtBeg is not null";
					}
				}

				if (isset($data['Cholesterol_id']) && $data['Cholesterol_id'] != '') {
					if ($data['Cholesterol_id'] == 3) {
						$filter .= " and ( 
					( (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) > 3.6 ) and RPlus.RegisterSixtyPlus_isSetProfileBSK = 1 )
					or
					( (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) > 5.2 ) and RPlus.RegisterSixtyPlus_isSetProfileBSK = 0 )
					   ) ";
					} else {
						$filter .= " and ( 
					( (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) <= 3.6 ) and RPlus.RegisterSixtyPlus_isSetProfileBSK = 1 )
					or
					( (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) <= 5.2 ) and RPlus.RegisterSixtyPlus_isSetProfileBSK = 0 )
					   )";
					}
				}
				if (isset($data['Sugar_id']) && $data['Sugar_id'] != '') {
					if ($data['Sugar_id'] == 3) {
						$filter .= " and cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) > 6.1";
					} else {
						$filter .= " and (cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 4 and cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) <= 6.1) ";
					}
				}

				if (isset($data['IMT_id']) && $data['IMT_id'] != '') {
					if ($data['IMT_id'] == 3) {
						$filter .= " and cast (RPlus.RegisterSixtyPlus_IMTMeasure as float) > 24.9";
					} else {
						$filter .= " and (cast (RPlus.RegisterSixtyPlus_IMTMeasure as float ) >= 18.5 and cast (RPlus.RegisterSixtyPlus_IMTMeasure as float ) <= 24.9)";
					}
				}

				if (isset($data['RiskType_id']) && $data['RiskType_id'] != '') {
					if ($data['RiskType_id'] == 1) {
						$filter .= "  
							and  (case when (RPlus.RegisterSixtyPlus_IMTMeasure is null or cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 25.0 ) then 0
								 when (cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 25.0 and cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 30.0) then 1
								 when cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 30.0 then 2 end 
								 +
							case when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 5.1) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 5.1 and cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 7.1 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 7.1 then 2 end
								 +
							case when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 6.2) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 6.2 and cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 7.0 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 7.0 then 2 end
								) >= 6 
							";
					} else if ($data['RiskType_id'] == 2) {
						$filter .= "
							and  (case when (RPlus.RegisterSixtyPlus_IMTMeasure is null or cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 25.0 ) then 0
								 when (cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 25.0 and cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 30.0) then 1
								 when cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 30.0 then 2 end 
								 +
							case when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 5.1) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 5.1 and cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 7.1 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 7.1 then 2 end
								 +
							case when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 6.2) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 6.2 and cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 7.0 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 7.0 then 2 end
								) between 1 and 5
							";
					} else {
						$filter .= "
							and  (case when (RPlus.RegisterSixtyPlus_IMTMeasure is null or cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 25.0 ) then 0
								 when (cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 25.0 and cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 30.0) then 1
								 when cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 30.0 then 2 end 
								 +
							case when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 5.1) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 5.1 and cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 7.1 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 7.1 then 2 end
								 +
							case when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 6.2) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 6.2 and cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 7.0 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 7.0 then 2 end
								) = 0
							";
					}
				}

			 break;

			case 'EvnPLWOW':
				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnPLWOW EPW with (nolock) on EPW.Server_id = PS.Server_id and EPW.PersonEvn_id = PS.PersonEvn_id and EPW.Lpu_id " . $this->getLpuIdFilter($data);
				} else {
					$query .= " inner join v_EvnPLWOW EPW with (nolock) on EPW.Person_id = PS.Person_id and EPW.Lpu_id " . $this->getLpuIdFilter($data);
				}

				// $query .= " inner join v_EvnPLWOW EPW with(nolock) on EPW.Person_id = PS.Person_id and EPW.Lpu_id = :Lpu_id";
				//$query .= " left join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id";
				$query .= " outer apply ( select top 1 PrivilegeTypeWow_id from PersonPrivilegeWOW with (nolock) where Person_id = PS.Person_id ) as PPW";
				$query .= " left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id";
				$query .= " left join YesNo with (nolock) on YesNo.YesNo_id = EPW.EvnPLWOW_IsFinish";
				$query .= " left join Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id";
				if (isset($data['PrivilegeTypeWow_id'])) {
					$filter .= " and PTW.PrivilegeTypeWow_id = :PrivilegeTypeWow_id ";
					$queryParams['PrivilegeTypeWow_id'] = $data['PrivilegeTypeWow_id'];
				}

				break;

			case 'EvnDtpWound':
				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnDtpWound EDW with (nolock) on EDW.Server_id = PS.Server_id and EDW.PersonEvn_id = PS.PersonEvn_id and EDW.Lpu_id " . $this->getLpuIdFilter($data);
				} else {
					$query .= " inner join v_EvnDtpWound EDW with (nolock) on EDW.Person_id = PS.Person_id and EDW.Lpu_id " . $this->getLpuIdFilter($data);
				}

				// $query .= " inner join v_EvnDtpWound EDW with(nolock) on EDW.Person_id = PS.Person_id and EDW.Lpu_id = :Lpu_id";
				if (isset($data['EvnDtpWound_setDate_Range'][0])) {
					$filter .= " and EDW.EvnDtpWound_setDate >= :EvnDtpWound_setDate_Range_0";
					$queryParams['EvnDtpWound_setDate_Range_0'] = $data['EvnDtpWound_setDate_Range'][0];
				}
				if (isset($data['EvnDtpWound_setDate_Range'][1])) {
					$filter .= " and EDW.EvnDtpWound_setDate <= :EvnDtpWound_setDate_Range_1";
					$queryParams['EvnDtpWound_setDate_Range_1'] = $data['EvnDtpWound_setDate_Range'][1];
				}
				break;

			case 'EvnDtpDeath':
				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnDtpDeath EDD with (nolock) on EDD.Server_id = PS.Server_id and EDD.PersonEvn_id = PS.PersonEvn_id and EDD.Lpu_id " . $this->getLpuIdFilter($data);
				} else {
					$query .= " inner join v_EvnDtpDeath EDD with (nolock) on EDD.Person_id = PS.Person_id and EDD.Lpu_id " . $this->getLpuIdFilter($data);
				}
				$query .= " inner join v_Sex sex with (nolock) on sex.Sex_id = PS.Sex_id";
				$query .= " inner join v_Diag diag with (nolock) on diag.Diag_id = EDD.Diag_iid";
				if (isset($data['EvnDtpDeath_setDate_Range'][0])) {
					$filter .= " and EDW.EvnDtpDeath_setDate >= :EvnDtpDeath_setDate_Range_0";
					$queryParams['EvnDtpDeath_setDate_Range_0'] = $data['EvnDtpDeath_setDate_Range'][0];
				}
				if (isset($data['EvnDtpDeath_setDate_Range'][1])) {
					$filter .= " and EDW.EvnDtpDeath_setDate <= :EvnDtpDeath_setDate_Range_1";
					$queryParams['EvnDtpDeath_setDate_Range_1'] = $data['EvnDtpDeath_setDate_Range'][1];
				}
				if (isset($data['EvnDtpDeath_DeathDate_Range'][0])) {
					$filter .= " and EDW.EvnDtpDeath_DeathDate >= :EvnDtpDeath_DeathDate_Range_0";
					$queryParams['EvnDtpDeath_DeathDate_Range_0'] = $data['EvnDtpDeath_DeathDate_Range'][0];
				}
				if (isset($data['EvnDtpDeath_DeathDate_Range'][1])) {
					$filter .= " and EDW.EvnDtpDeath_DeathDate <= :EvnDtpDeath_DeathDate_Range_1";
					$queryParams['EvnDtpDeath_DeathDate_Range_1'] = $data['EvnDtpDeath_DeathDate_Range'][1];
				}
				break;

			case 'PersonDopDisp':
				$query .= " inner join PersonDopDisp DD with (nolock) on DD.Person_id = PS.Person_id and DD.Lpu_id " . $this->getLpuIdFilter($data);
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " 
					left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
					left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
					left join v_Okved as okved1 with (nolock) ON okved1.Okved_id=org1.Okved_id";
				$query .= " 
					left join v_Address as addr1 with (nolock) ON PS.UAddress_id=addr1.Address_id
					outer apply (
						select top 1 KLArea_Name
						from v_KLAreaStat with (nolock)
						where 
							((KLCountry_id = addr1.KLCountry_id) or (KLCountry_id is null))
							and ((KLRGN_id = addr1.KLRGN_id) or (KLRGN_id is null))
							and ((KLSubRGN_id = addr1.KLSubRGN_id) or (KLSubRGN_id is null))
							and ((KLCity_id = addr1.KLCity_id) or (KLCity_id is null))
							and ((KLTown_id = addr1.KLTown_id) or (KLTown_id is null))
						order by
							KLCountry_id desc,
							KLRGN_id desc,
							KLSubRGN_id desc,
							KLCity_id desc,
							KLTown_id desc
					) as astat1
					left join v_Address as addr2 with (nolock) ON org1.UAddress_id=addr2.Address_id
					outer apply (
						select top 1 KLArea_Name
						from v_KLAreaStat with (nolock)
						where 
							((KLCountry_id = addr2.KLCountry_id) or (KLCountry_id is null))
							and ((KLRGN_id = addr2.KLRGN_id) or (KLRGN_id is null))
							and ((KLSubRGN_id = addr2.KLSubRGN_id) or (KLSubRGN_id is null))
							and ((KLCity_id = addr2.KLCity_id) or (KLCity_id is null))
							and ((KLTown_id = addr2.KLTown_id) or (KLTown_id is null))
						order by
							KLCountry_id desc,
							KLRGN_id desc,
							KLSubRGN_id desc,
							KLCity_id desc,
							KLTown_id desc
					) as astat2
					";

				$query .= "
					outer apply (
						select top 1
							Lpu_Nick 
						from
							PersonDopDisp pdd with (nolock)
							inner join v_Lpu vlp with (nolock) on vlp.Lpu_id = pdd.Lpu_id
						where
							pdd.Person_id = PS.Person_id
							and pdd.Lpu_id <> :Lpu_id
							and pdd.PersonDopDisp_Year = :PersonDopDisp_Year
					) as otherddlpu ";

				$query .= "
					left join v_EvnPLDispDop epldd with (nolock) on
						epldd.Person_id = PS.Person_id
						and epldd.Lpu_id " . $this->getLpuIdFilter($data) . "
						and epldd.EvnPLDispDop_setDate between :PersonDopDisp_Year_Start and :PersonDopDisp_Year_End
				";

				if (isset($data['dop_disp_reg_beg_date']) && isset($data['dop_disp_reg_beg_time'])) {
					$filter .= " and DD.PersonDopDisp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['dop_disp_reg_beg_date'] . " " . $data['dop_disp_reg_beg_time'];
				}

				$filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
				if (isset($data['PersonDopDisp_Year']) && ($data['PersonDopDisp_Year'] > 2000)) {
					//как показало тестирование в реальных условиях
					//and epldd.EvnPLDispDop_setDate between '2010-01-01' and '2010-12-31'
					//работает на порядок быстрее, чем
					//and year(epldd.EvnPLDispDop_setDate) = 2010
					$queryParams['PersonDopDisp_Year_Start'] = $data['PersonDopDisp_Year'] . '-01-01';
					$queryParams['PersonDopDisp_Year_End'] = $data['PersonDopDisp_Year'] . '-12-31';
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				} else {
					$queryParams['PersonDopDisp_Year_Start'] = date('Y') . '-01-01';
					$queryParams['PersonDopDisp_Year_End'] = date('Y') . '-12-31';
					$queryParams['PersonDopDisp_Year'] = date('Y');
				}


				break;

			case 'PersonDispOrpPeriod':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id " . $this->getLpuIdFilter($data);
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
							left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id";
				$query .= " left join v_EvnPLDispTeenInspection EPLDTI with (nolock) on EPLDTI.PersonDispOrp_id = DO.PersonDispOrp_id";
				$query .= "
					outer apply (select top 1
							pc.Person_id as PersonCard_Person_id,
							pc.Lpu_id
						from v_PersonCard pc with (nolock)
						where
							pc.Person_id = ps.Person_id
							and LpuAttachType_id = 1
						order by PersonCard_begDate desc
						) as pcard
					left join v_Lpu LATT with (nolock) on pcard.Lpu_id=LATT.Lpu_id
					left join v_EducationInstitutionType EIT (nolock) on EIT.EducationInstitutionType_id = DO.EducationInstitutionType_id
				";

				if (isset($data['reg_beg_date']) && isset($data['reg_beg_time'])) {
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date'] . " " . $data['reg_beg_time'];
				}

				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if (isset($data['PersonDispOrp_Year'])) {
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				} else {
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if (!empty($data['EducationInstitutionType_id'])) {
					$filter .= " and DO.EducationInstitutionType_id = :EducationInstitutionType_id ";
					$queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
				}

				$filter .= " and DO.CategoryChildType_id IN (8)";

				break;

			case 'PersonDispOrpPred':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id " . $this->getLpuIdFilter($data);
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " left join v_EvnPLDispTeenInspection EPLDTI with (nolock) on EPLDTI.PersonDispOrp_id = DO.PersonDispOrp_id";
				$query .= "
					outer apply (select top 1
							pc.Person_id as PersonCard_Person_id,
							pc.Lpu_id
						from v_PersonCard pc with (nolock)
						where
							pc.Person_id = ps.Person_id
							and LpuAttachType_id = 1
						order by PersonCard_begDate desc
						) as pcard
					left join v_Lpu LATT with (nolock) on pcard.Lpu_id=LATT.Lpu_id
					left join v_EducationInstitutionType EIT (nolock) on EIT.EducationInstitutionType_id = DO.EducationInstitutionType_id
				";

				if (isset($data['reg_beg_date']) && isset($data['reg_beg_time'])) {
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date'] . " " . $data['reg_beg_time'];
				}

				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if (isset($data['PersonDispOrp_Year'])) {
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				} else {
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if (!empty($data['EducationInstitutionType_id'])) {
					$filter .= " and DO.EducationInstitutionType_id = :EducationInstitutionType_id ";
					$queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
				}

				$filter .= " and DO.CategoryChildType_id IN (9)";

				break;

			case 'PersonDispOrpProf':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id " . $this->getLpuIdFilter($data);
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " left join v_EvnPLDispTeenInspection EPLDTI with (nolock) on EPLDTI.PersonDispOrp_id = DO.PersonDispOrp_id";
				$query .= " left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = DO.AgeGroupDisp_id";

				if (isset($data['reg_beg_date']) && isset($data['reg_beg_time'])) {
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date'] . " " . $data['reg_beg_time'];
				}

				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if (isset($data['PersonDispOrp_Year'])) {
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				} else {
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if (!empty($data['AgeGroupDisp_id'])) {
					$filter .= " and DO.AgeGroupDisp_id = :AgeGroupDisp_id ";
					$queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
				}

				if (!empty($data['OrgExist'])) {
					if ($data['OrgExist'] == 2) {
						$filter .= " and DO.Org_id IS NOT NULL";
					} else {
						$filter .= " and DO.Org_id IS NULL";
					}
				}

				$filter .= " and DO.CategoryChildType_id IN (10)";

				break;

			case 'PersonDispOrp':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id " . $this->getLpuIdFilter($data);
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " 
					left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
					left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
					left join v_Okved as okved1 with (nolock) ON okved1.Okved_id=org1.Okved_id";
				$query .= "
					left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id
					left join v_Address as addr1 with (nolock) ON PS.UAddress_id=addr1.Address_id
					outer apply (
						select top 1 KLArea_Name
						from v_KLAreaStat with (nolock)
						where 
							((KLCountry_id = addr1.KLCountry_id) or (KLCountry_id is null))
							and ((KLRGN_id = addr1.KLRGN_id) or (KLRGN_id is null))
							and ((KLSubRGN_id = addr1.KLSubRGN_id) or (KLSubRGN_id is null))
							and ((KLCity_id = addr1.KLCity_id) or (KLCity_id is null))
							and ((KLTown_id = addr1.KLTown_id) or (KLTown_id is null))
						order by
							KLCountry_id desc,
							KLRGN_id desc,
							KLSubRGN_id desc,
							KLCity_id desc,
							KLTown_id desc
					) as astat1
					left join v_Address as addr2 with (nolock) ON org1.UAddress_id=addr2.Address_id
					outer apply (
						select top 1 KLArea_Name
						from v_KLAreaStat with (nolock)
						where 
							((KLCountry_id = addr2.KLCountry_id) or (KLCountry_id is null))
							and ((KLRGN_id = addr2.KLRGN_id) or (KLRGN_id is null))
							and ((KLSubRGN_id = addr2.KLSubRGN_id) or (KLSubRGN_id is null))
							and ((KLCity_id = addr2.KLCity_id) or (KLCity_id is null))
							and ((KLTown_id = addr2.KLTown_id) or (KLTown_id is null))
						order by
							KLCountry_id desc,
							KLRGN_id desc,
							KLSubRGN_id desc,
							KLCity_id desc,
							KLTown_id desc
					) as astat2
					";

				$query .= "
					outer apply (
						select
							Lpu_Nick 
						from
							PersonDispOrp pdd with (nolock)
							inner join v_Lpu vlp with (nolock) on vlp.Lpu_id = pdd.Lpu_id
						where
							pdd.Person_id = PS.Person_id
							and pdd.Lpu_id <> :Lpu_id
							and pdd.PersonDispOrp_Year = :PersonDispOrp_Year
					) as ODL ";

				if (isset($data['reg_beg_date']) && isset($data['reg_beg_time'])) {
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date'] . " " . $data['reg_beg_time'];
				}

				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if (isset($data['PersonDispOrp_Year'])) {
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				} else {
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if (empty($data['CategoryChildType'])) {
					$data['CategoryChildType'] = 'orp';
				}

				if (!empty($data['EducationInstitutionType_id'])) {
					$filter .= " and DO.EducationInstitutionType_id = :EducationInstitutionType_id ";
					$queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
				}

				$filterevnpl = "";
				switch ($data['CategoryChildType']) {
					case 'orp':
						$filterevnpl = " and epldd.DispClass_id = 3";
						$filter .= " and DO.CategoryChildType_id IN (1,2,3,4)";
						break;
					case 'orpadopted':
						$filterevnpl = " and epldd.DispClass_id = 7";
						$filter .= " and DO.CategoryChildType_id IN (5,6,7)";
						break;
				}

				$query .= "
					outer apply (
						select 
							top 1 EvnPLDispOrp_id
						from
							v_EvnPLDispOrp epldd with (nolock)
						where
							epldd.Person_id=PS.Person_id
							and epldd.Lpu_id " . $this->getLpuIdFilter($data) . "
							{$filterevnpl}
							and year(epldd.EvnPLDispOrp_setDate) = :PersonDispOrp_Year
					) as EPLDO ";

				break;

			case 'PersonDispOrpOld':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id " . $this->getLpuIdFilter($data);
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " 
					left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
					left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
					left join v_Okved as okved1 with (nolock) ON okved1.Okved_id=org1.Okved_id";
				$query .= " 
					left join v_Address as addr1 with (nolock) ON PS.UAddress_id=addr1.Address_id
					outer apply (
						select top 1 KLArea_Name
						from v_KLAreaStat with (nolock)
						where 
							((KLCountry_id = addr1.KLCountry_id) or (KLCountry_id is null))
							and ((KLRGN_id = addr1.KLRGN_id) or (KLRGN_id is null))
							and ((KLSubRGN_id = addr1.KLSubRGN_id) or (KLSubRGN_id is null))
							and ((KLCity_id = addr1.KLCity_id) or (KLCity_id is null))
							and ((KLTown_id = addr1.KLTown_id) or (KLTown_id is null))
						order by
							KLCountry_id desc,
							KLRGN_id desc,
							KLSubRGN_id desc,
							KLCity_id desc,
							KLTown_id desc
					) as astat1
					left join v_Address as addr2 with (nolock) ON org1.UAddress_id=addr2.Address_id
					outer apply (
						select top 1 KLArea_Name
						from v_KLAreaStat with (nolock)
						where 
							((KLCountry_id = addr2.KLCountry_id) or (KLCountry_id is null))
							and ((KLRGN_id = addr2.KLRGN_id) or (KLRGN_id is null))
							and ((KLSubRGN_id = addr2.KLSubRGN_id) or (KLSubRGN_id is null))
							and ((KLCity_id = addr2.KLCity_id) or (KLCity_id is null))
							and ((KLTown_id = addr2.KLTown_id) or (KLTown_id is null))
						order by
							KLCountry_id desc,
							KLRGN_id desc,
							KLSubRGN_id desc,
							KLCity_id desc,
							KLTown_id desc
					) as astat2
					";

				$query .= "
					outer apply (
						select
							Lpu_Nick 
						from
							PersonDispOrp pdd with (nolock)
							inner join v_Lpu vlp with (nolock) on vlp.Lpu_id = pdd.Lpu_id
						where
							pdd.Person_id = PS.Person_id
							and pdd.Lpu_id <> :Lpu_id
							and pdd.PersonDispOrp_Year = :PersonDispOrp_Year
					) as ODL ";

				$query .= "
					outer apply (
						select 
							top 1 EvnPLDispOrp_id
						from
							v_EvnPLDispOrp epldd with (nolock)
						where
							epldd.Person_id=PS.Person_id and
							epldd.Lpu_id " . $this->getLpuIdFilter($data) . "
							and year(epldd.EvnPLDispOrp_setDate) = :PersonDispOrp_Year
					) as EPLDO ";

				if (isset($data['reg_beg_date']) && isset($data['reg_beg_time'])) {
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date'] . " " . $data['reg_beg_time'];
				}

				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";

				// до 2013 года
				$filter .= " and DO.PersonDispOrp_Year <= 2012 ";

				if (isset($data['PersonDispOrp_Year'])) {
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				} else {
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}


				break;

			case 'EvnPLDispDop13':
				$filterEPLDD13 = "";
				$filterDopDispSecond = "";

				// https://redmine.swan.perm.ru/issues/37296
				$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

				if (isset($data['EvnPLDispDop13_setDate'])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13_setDate as datetime) ";
					$queryParams['EvnPLDispDop13_setDate'] = $data['EvnPLDispDop13_setDate'];
				}
				if (isset($data['EvnPLDispDop13_setDate_Range'][0])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13_setDate_Range_0'] = $data['EvnPLDispDop13_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13_setDate_Range'][1])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13_setDate_Range_1'] = $data['EvnPLDispDop13_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop13_disDate'])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13_disDate as datetime) ";
					$queryParams['EvnPLDispDop13_disDate'] = $data['EvnPLDispDop13_disDate'];
				}
				if (isset($data['EvnPLDispDop13_disDate_Range'][0])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13_disDate_Range_0'] = $data['EvnPLDispDop13_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13_disDate_Range'][1])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13_disDate_Range_1'] = $data['EvnPLDispDop13_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop13_IsFinish'])) {
					$filterEPLDD13 .= " and isnull([EvnPLDispDop13].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
					$queryParams['EvnPLDispDop13_IsFinish'] = $data['EvnPLDispDop13_IsFinish'];
				}
				if (isset($data['EvnPLDispDop13_IsRefusal'])) {
					$filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_IsRefusal = :EvnPLDispDop13_IsRefusal ";
					$queryParams['EvnPLDispDop13_IsRefusal'] = $data['EvnPLDispDop13_IsRefusal'];
				}
				if (isset($data['EvnPLDispDop13_IsTwoStage'])) {
					$filterEPLDD13 .= " and isnull([EvnPLDispDop13].EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
					$queryParams['EvnPLDispDop13_IsTwoStage'] = $data['EvnPLDispDop13_IsTwoStage'];
				}

				if (isset($data['EvnPLDispDop13_HealthKind_id'])) {
					$filterEPLDD13 .= " and [EvnPLDispDop13].HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
					$queryParams['EvnPLDispDop13_HealthKind_id'] = $data['EvnPLDispDop13_HealthKind_id'];
				}

				if (isset($data['EvnPLDispDop13_isPaid'])) {
					if ($data['session']['region']['nick'] == 'ufa') {
						// https://redmine.swan.perm.ru/issues/56232
						if ($data['EvnPLDispDop13_isPaid'] == 2) {
							$filter .= " and exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = [EvnPLDispDop13].EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						} else {
							$filter .= " and not exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = [EvnPLDispDop13].EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						}
					} else {
						$filterEPLDD13 .= " and ISNULL([EvnPLDispDop13].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13_isPaid ";
						$queryParams['EvnPLDispDop13_isPaid'] = $data['EvnPLDispDop13_isPaid'];
					}
				}

				if (isset($data['EvnPLDispDop13Second_isPaid'])) {
					if ($data['session']['region']['nick'] == 'ufa') {
						if ($data['EvnPLDispDop13Second_isPaid'] == 2) {
							$filter .= " and exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = [EPLDD13_SEC].EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						} else {
							$filter .= " and not exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = [EPLDD13_SEC].EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						}
					} else {
						$filterDopDispSecond .= " and ISNULL([EPLDD13_SEC].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13Second_isPaid ";
						$queryParams['EvnPLDispDop13Second_isPaid'] = $data['EvnPLDispDop13Second_isPaid'];
					}
				}

				if (isset($data['EvnPLDispDop13_isMobile'])) {
					$filterEPLDD13 .= " and ISNULL([EvnPLDispDop13].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
					$queryParams['EvnPLDispDop13_isMobile'] = $data['EvnPLDispDop13_isMobile'];
				}

				if (isset($data['EvnPLDispDop13Second_isMobile'])) {
					$filterDopDispSecond .= " and ISNULL([EPLDD13_SEC].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13Second_isMobile ";
					$queryParams['EvnPLDispDop13Second_isMobile'] = $data['EvnPLDispDop13Second_isMobile'];
				}

				if (isset($data['EvnPLDispDop13Second_setDate'])) {
					$filterDopDispSecond .= " and [EPLDD13_SEC].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13Second_setDate as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate'] = $data['EvnPLDispDop13Second_setDate'];
				}
				if (isset($data['EvnPLDispDop13Second_setDate_Range'][0])) {
					$filterDopDispSecond .= " and [EPLDD13_SEC].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13Second_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate_Range_0'] = $data['EvnPLDispDop13Second_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13Second_setDate_Range'][1])) {
					$filterDopDispSecond .= " and [EPLDD13_SEC].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13Second_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate_Range_1'] = $data['EvnPLDispDop13Second_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop13Second_disDate'])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13Second_disDate as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate'] = $data['EvnPLDispDop13Second_disDate'];
				}
				if (isset($data['EvnPLDispDop13Second_disDate_Range'][0])) {
					$filterDopDispSecond .= " and [EPLDD13_SEC].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13Second_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate_Range_0'] = $data['EvnPLDispDop13Second_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13Second_disDate_Range'][1])) {
					$filterDopDispSecond .= " and [EPLDD13_SEC].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13Second_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate_Range_1'] = $data['EvnPLDispDop13Second_disDate_Range'][1];
				}

				if (isset($data['EvnPLDispDop13Second_IsFinish'])) {
					$filterDopDispSecond .= " and isnull([EPLDD13_SEC].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
					$queryParams['EvnPLDispDop13Second_IsFinish'] = $data['EvnPLDispDop13Second_IsFinish'];
				}

				if (isset($data['EvnPLDispDop13Second_HealthKind_id'])) {
					$filterDopDispSecond .= " and [EPLDD13_SEC].HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
					$queryParams['EvnPLDispDop13Second_HealthKind_id'] = $data['EvnPLDispDop13Second_HealthKind_id'];
				}

				if (isset($data['PersonDopDisp_Year'])) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				} else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				// не отображать пациентов, которые умерли ранее выбранного года
				$filter .= " and (PS.Person_deadDT >= cast(:PersonDopDisp_Year as varchar) + '-01-01' OR PS.Person_deadDT IS NULL)";

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				$add_filter = "";
				$maxage = 999;
				$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList($queryParams['PersonDopDisp_Year'] . '-01-01');
				$variablesArray[] = "declare @PersonDopDisp_YearEndDate datetime = cast(:PersonDopDisp_Year as varchar) + '-12-31';";

				if (in_array($data['session']['region']['nick'], array('ufa', 'ekb', 'kareliya', 'penza', 'astra'))) {
					$add_filter .= " or exists (select top 1 PersonPrivilegeWOW_id from v_PersonPrivilegeWOW (nolock) where Person_id = PS.Person_id)";
				}

				$dateX = $this->EvnPLDispDop13_model->getNewDVNDate();
				if ( !empty($dateX) && $dateX <= date('Y-m-d') ) {
					$add_filter .= "
						or
						(dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) >= 40)
					";
				}
				else {
					// @task https://redmine.swan.perm.ru/issues/124302
					if (!in_array($data['session']['region']['nick'], array('kz')) && $data['PersonDopDisp_Year'] >= 2018) {
						$add_filter .= "
							or
							(PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) between 49 and 73 and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) % 2 = 1)
							or
							(PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) between 48 and 73)
						";
					}
				}

				if (count($personPrivilegeCodeList) > 0) {
					$add_filter .= "
						or (
							(dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) BETWEEN 18 AND {$maxage})
							and exists (
								select top 1 pp.PersonPrivilege_id
								from v_PersonPrivilege pp (nolock)
									inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
								where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
									and pp.Person_id = PS.Person_id
									and pp.PersonPrivilege_begDate <= @PersonDopDisp_YearEndDate
									and (pp.PersonPrivilege_endDate > @PersonDopDisp_YearEndDate or pp.PersonPrivilege_endDate is null)
							)
						) -- refs #23044
					";
				}

				// https://redmine.swan.perm.ru/issues/19835
				// Если пациент, состоит в регистре ВОВ, то на данной форме его отображать независимо от возраста http://redmine.swan.perm.ru/issues/22014
				$DDfilter = "
					(
						(dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) - 21) % 3 = 0)
						{$add_filter}
					)
					and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) <= {$maxage}
				";

				if ($data['session']['region']['nick'] == 'perm') {
					// нет карты профосмотра в указанному году
					$DDfilter .= "
						and not exists (select top 1 EvnPLDispProf_id from v_EvnPLDispProf (nolock) where YEAR(EvnPLDispProf_consDT) = :PersonDopDisp_Year and Person_id = PS.Person_id)
					";
				}
				if (!empty($data['Disp_MedStaffFact_id']) || !empty($data['Disp_LpuSection_id']) || !empty($data['Disp_LpuBuilding_id'])) {
					$disp_msf = "";
					$disp_msf2 = "";
					$disp_ls = "";
					$disp_ls2 = "";
					$disp_b = "";
					$disp_b2 = "";
					$join1 = "";
					$join2 = "";
					if (!empty($data['Disp_MedStaffFact_id'])) {
						$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
						$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
						$queryParams['MedStaffFact_id'] = $data['Disp_MedStaffFact_id'];
					} else {
						if (!empty($data['Disp_LpuSection_id'])) {
							$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
							$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
							$queryParams['LpuSection_id'] = $data['Disp_LpuSection_id'];
						}
						if (!empty($data['Disp_LpuBuilding_id'])) {
							$join1 = "
								left join v_LpuSection ls1 with (nolock) on ls1.LpuSection_id = msf1.LpuSection_id
							";
							$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
							$join2 = "
								left join v_LpuSection ls3 with (nolock) on ls3.LpuSection_id = msf2.LpuSection_uid
							";
							$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
							$queryParams['LpuBuilding_id'] = $data['Disp_LpuBuilding_id'];
						}
					}
					$filter .= " 
					and (exists (
						select top 1 msf1.EvnVizitDispDop_id 
						from v_EvnVizitDispDop msf1 with (nolock) 
						{$join1}
						where msf1.EvnVizitDispDop_pid = [EvnPLDispDop13].[EvnPLDispDop13_id] 
						{$disp_b}
						{$disp_msf}
						{$disp_ls}
					) or exists (
						select top 1 msf2.EvnUslugaDispDop_id 
						from v_EvnUslugaDispDop msf2 with (nolock)
						{$join2} 
						where msf2.EvnUslugaDispDop_pid = [EvnPLDispDop13].[EvnPLDispDop13_id] 
						{$disp_b2}
						{$disp_msf2}
						{$disp_ls2}
					))";
				}

				$filter .= " and (EvnPLDispDop13.EvnPLDispDop13_id is not null OR ({$DDfilter}))";

				$joinEPLDD13 = "left";
				if (!empty($filterEPLDD13)) {
					$joinEPLDD13 = "inner";
				}

				/* if ($data['PersonPeriodicType_id'] == 2) {
				  $query .= "
				  {$joinEPLDD13} join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on EPLDD13.Server_id = PS.Server_id and EPLDD13.PersonEvn_id = PS.PersonEvn_id and [EPLDD13].Lpu_id = :Lpu_id and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
				  ";
				  } else {
				  $query .= "
				  {$joinEPLDD13} join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id = :Lpu_id and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
				  ";
				  } */

				$joinDopDispSecond = "outer";
				if (!empty($filterDopDispSecond)) {
					$joinDopDispSecond = "cross";
				}

				if (isset($data['EvnPLDisp_UslugaComplex']) && $data['EvnPLDisp_UslugaComplex'] > 0) {
					$filter .= "
						and exists (
							select top 1 UslugaComplex_id
							from v_EvnUslugaDispDop with (nolock)
							where EvnUslugaDispDop_didDate is not null
								and UslugaComplex_id = :EvnPLDisp_UslugaComplex
								and EvnUslugaDispDop_rid = EPLDD13.EvnPLDispDop13_id
						)
					";
					$queryParams['EvnPLDisp_UslugaComplex'] = $data['EvnPLDisp_UslugaComplex'];
				}

				$query .= "
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDD13.EvnPLDispDop13_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_IsEndStage], 1)
					left join [YesNo] [IsMobile] with (nolock) on [IsMobile].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_isMobile], 1)
					left join v_HealthKind HK with (nolock) on HK.HealthKind_id = EPLDD13.HealthKind_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = EPLDD13.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = EPLDD13.PAddress_id
					{$joinDopDispSecond} apply(
						select top 1
							EPLDD13_SEC.EvnPLDispDop13_id,
							EPLDD13_SEC.EvnPLDispDop13_isPaid,
							EPLDD13_SEC.EvnPLDispDop13_IsTransit,
							EPLDD13_SEC.EvnPLDispDop13_setDate,
							EPLDD13_SEC.EvnPLDispDop13_disDate,
							EPLDD13_SEC.EvnPLDispDop13_consDT,
							EPLDD13_SEC.HealthKind_id,
							ISNULL(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
							HK_SEC.HealthKind_Name,
							EPLDD13_SEC.EvnPLDispDop13_insDT,
							EPLDD13_SEC.EvnPLDispDop13_updDT,
							EPLDD13_SEC.pmUser_insID,
							EPLDD13_SEC.pmUser_updID
						from
							v_EvnPLDispDop13 (nolock) EPLDD13_SEC
							left join v_HealthKind HK_SEC with (nolock) on HK_SEC.HealthKind_id = EPLDD13_SEC.HealthKind_id
						where
							EPLDD13_SEC.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
							{$filterDopDispSecond}
					) DopDispSecond -- данные по 2 этапу
					left join [YesNo] [IsFinishSecond] with (nolock) on [IsFinishSecond].[YesNo_id] = [DopDispSecond].[EvnPLDispDop13_IsEndStage]
					outer apply(
						select top 1
							DDIC.DopDispInfoConsent_IsAgree
						from
							v_DopDispInfoConsent DDIC (nolock) 
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id
							and ST.SurveyType_Code = 48
					) DDICDataSecond
					outer apply(
						select top 1 EvnPLDispDop13_id, Lpu_id
						from v_EvnPLDispDop13 (nolock)
						where Person_id = EPLDD13.Person_id
							and YEAR(EvnPLDispDop13_setDate) = :PersonDopDisp_Year
							and Lpu_id " . getLpuIdFilter($data, true) . "
							and ISNULL(DispClass_id,1) = 1
					) EPLDD13AL
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = ISNULL(EPLDD13.Lpu_id, EPLDD13AL.Lpu_id)
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					if (!empty($data['UslugaComplex_id'])) {
						$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
						$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
					}
					$query .= "
						outer apply(
							select top 1
								UslugaComplex_id
							from
								v_EvnUslugaDispDop (nolock)
							where
								EvnUslugaDispDop_IsVizitCode = 2
								and EvnUslugaDispDop_pid = EPLDD13.EvnPLDispDop13_id
						) euddvizit
						left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
					";
				}

				break;

			case 'EvnPLDispDop13Sec':
				$filterEPLDD13 = "";
				$filterDDICData = "";
				$filterDopDispSecond = "";

				// https://redmine.swan.perm.ru/issues/37296
				$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

				if (isset($data['EvnPLDispDop13_setDate'])) {
					$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13_setDate as datetime) ";
					$queryParams['EvnPLDispDop13_setDate'] = $data['EvnPLDispDop13_setDate'];
				}
				if (isset($data['EvnPLDispDop13_setDate_Range'][0])) {
					$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13_setDate_Range_0'] = $data['EvnPLDispDop13_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13_setDate_Range'][1])) {
					$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13_setDate_Range_1'] = $data['EvnPLDispDop13_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop13_disDate'])) {
					$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13_disDate as datetime) ";
					$queryParams['EvnPLDispDop13_disDate'] = $data['EvnPLDispDop13_disDate'];
				}
				if (isset($data['EvnPLDispDop13_disDate_Range'][0])) {
					$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13_disDate_Range_0'] = $data['EvnPLDispDop13_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13_disDate_Range'][1])) {
					$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13_disDate_Range_1'] = $data['EvnPLDispDop13_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop13_IsFinish'])) {
					$filterEPLDD13 .= " and isnull([EPLDD13].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
					$queryParams['EvnPLDispDop13_IsFinish'] = $data['EvnPLDispDop13_IsFinish'];
				}
				if (isset($data['EvnPLDispDop13_Cancel'])) {
					if ($data['EvnPLDispDop13_Cancel'] == 2) {
						$data['DopDispInfoConsent_IsAgree'] = 1;
					} else {
						$data['DopDispInfoConsent_IsAgree'] = 2;
					}
					$filterDDICData .= " and ISNULL(DDIC.DopDispInfoConsent_IsAgree,2) = :DopDispInfoConsent_IsAgree ";
					$queryParams['DopDispInfoConsent_IsAgree'] = $data['DopDispInfoConsent_IsAgree'];
				}
				if (isset($data['EvnPLDispDop13_IsTwoStage'])) {
					$filterEPLDD13 .= " and isnull([EPLDD13].EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
					$queryParams['EvnPLDispDop13_IsTwoStage'] = $data['EvnPLDispDop13_IsTwoStage'];
				}

				if (isset($data['EvnPLDispDop13_HealthKind_id'])) {
					$filterEPLDD13 .= " and [EPLDD13].HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
					$queryParams['EvnPLDispDop13_HealthKind_id'] = $data['EvnPLDispDop13_HealthKind_id'];
				}

				if (isset($data['EvnPLDispDop13_isPaid'])) {
					if ($data['session']['region']['nick'] == 'ufa') {
						// https://redmine.swan.perm.ru/issues/56232
						if ($data['EvnPLDispDop13_isPaid'] == 2) {
							$filter .= " and exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						} else {
							$filter .= " and not exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						}
					} else {
						$filterEPLDD13 .= " and ISNULL([EPLDD13].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13_isPaid ";
						$queryParams['EvnPLDispDop13_isPaid'] = $data['EvnPLDispDop13_isPaid'];
					}
				}

				if (isset($data['EvnPLDispDop13Second_isPaid'])) {
					if ($data['session']['region']['nick'] == 'ufa') {
						// https://redmine.swan.perm.ru/issues/77587
						if ($data['EvnPLDispDop13Second_isPaid'] == 2) {
							$filter .= " and exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = DopDispSecond.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						} else {
							$filter .= " and not exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = DopDispSecond.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
						}
					} else {
						$filterDopDispSecond .= " and ISNULL([DopDispSecond].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13Second_isPaid ";
						$queryParams['EvnPLDispDop13Second_isPaid'] = $data['EvnPLDispDop13Second_isPaid'];
					}
				}

				if (isset($data['EvnPLDispDop13_isMobile'])) {
					$filterEPLDD13 .= " and ISNULL([EPLDD13].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
					$queryParams['EvnPLDispDop13_isMobile'] = $data['EvnPLDispDop13_isMobile'];
				}

				if (isset($data['EvnPLDispDop13Second_isMobile'])) {
					$filterDopDispSecond .= " and ISNULL([DopDispSecond].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13Second_isMobile ";
					$queryParams['EvnPLDispDop13Second_isMobile'] = $data['EvnPLDispDop13Second_isMobile'];
				}

				if (isset($data['EvnPLDispDop13Second_setDate'])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13Second_setDate as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate'] = $data['EvnPLDispDop13Second_setDate'];
				}
				if (isset($data['EvnPLDispDop13Second_setDate_Range'][0])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13Second_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate_Range_0'] = $data['EvnPLDispDop13Second_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13Second_setDate_Range'][1])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13Second_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate_Range_1'] = $data['EvnPLDispDop13Second_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop13Second_disDate'])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13Second_disDate as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate'] = $data['EvnPLDispDop13Second_disDate'];
				}
				if (isset($data['EvnPLDispDop13Second_disDate_Range'][0])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13Second_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate_Range_0'] = $data['EvnPLDispDop13Second_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop13Second_disDate_Range'][1])) {
					$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13Second_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate_Range_1'] = $data['EvnPLDispDop13Second_disDate_Range'][1];
				}

				if (isset($data['EvnPLDispDop13Second_IsFinish'])) {
					$filterDopDispSecond .= " and isnull([DopDispSecond].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
					$queryParams['EvnPLDispDop13Second_IsFinish'] = $data['EvnPLDispDop13Second_IsFinish'];
				}

				if (isset($data['EvnPLDispDop13Second_HealthKind_id'])) {
					$filterDopDispSecond .= " and [DopDispSecond].HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
					$queryParams['EvnPLDispDop13Second_HealthKind_id'] = $data['EvnPLDispDop13Second_HealthKind_id'];
				}

				if (isset($data['PersonDopDisp_Year'])) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				} else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				$joinDopDisp = "inner";
				$mainFilterDopDispSecond = "(DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id)";
				if (getRegionNick() == 'ekb') {
					// не обязательно есть карта первого этапа
					$joinDopDisp = "left";
					$mainFilterDopDispSecond = "(
						DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
						OR (
							DopDispSecond.EvnPLDispDop13_id IS NOT NULL
							and DopDispSecond.Person_id = PS.Person_id
							and DopDispSecond.Lpu_id " . $this->getLpuIdFilter($data) . "
							and DopDispSecond.DispClass_id = 2
							and DopDispSecond.EvnPLDispDop13_fid IS NULL
							and YEAR(DopDispSecond.EvnPLDispDop13_consDT) = :PersonDopDisp_Year
						)
					)";
				}

				$variablesArray[] = "declare @PersonDopDisp_YearEndDate datetime = cast(:PersonDopDisp_Year as varchar) + '-12-31';";

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= "
						{$joinDopDisp} join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on EPLDD13.Server_id = PS.Server_id and EPLDD13.PersonEvn_id = PS.PersonEvn_id and [EPLDD13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
					";
				} else {
					// http://redmine.swan.perm.ru/issues/74240
					if ($this->getRegionNick() == 'ufa') {
						$query = str_replace("v_PersonState PS with (nolock)", "v_EvnPLDispDop13 [EPLDD13] with (nolock)
							inner join v_PersonState PS with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id]", $query);
						$newFilterString = "(1 = 1) and [EPLDD13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}";
						$filter = str_replace("(1 = 1)", $newFilterString, $filter);
					} else {
						$query .= "
							{$joinDopDisp} join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
						";
					}
				}

				$joinDDICData = "outer";
				if (!empty($filterDDICData)) {
					$joinDDICData = "cross";
				}

				$joinDopDispSecond = "left";
				if (!empty($filterDopDispSecond)) {
					$joinDopDispSecond = "inner";
				}

				$query .= "
					{$joinDopDispSecond} join v_EvnPLDispDop13 (nolock) DopDispSecond on {$mainFilterDopDispSecond} {$filterDopDispSecond}
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = DopDispSecond.EvnPLDispDop13_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_IsEndStage], 1)
					left join [YesNo] [IsMobile] with (nolock) on [IsMobile].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_isMobile], 1)
					left join v_HealthKind HK with (nolock) on HK.HealthKind_id = EPLDD13.HealthKind_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = PS.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = PS.PAddress_id
					{$joinDDICData} apply(
						select top 1
							DDIC.DopDispInfoConsent_IsAgree
						from
							v_DopDispInfoConsent DDIC (nolock)
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							DDIC.EvnPLDisp_id = EPLDD13.EvnPLDispDop13_id
							and ST.SurveyType_Code = 1
							{$filterDDICData}
					) DDICData
					outer apply( -- дата осмотра врачём-терапевтом из первого этапа
						select top 1
							ISNULL(EUDD.EvnUslugaDispDop_disDate, EUDD.EvnUslugaDispDop_didDate) as EvnUslugaDispDop_disDate
						from v_EvnUslugaDispDop EUDD (nolock)
							left join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
							left join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							EVDD.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id
							and ST.SurveyType_Code = 19
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) DOCOSMDT
					left join v_HealthKind HK_SEC with (nolock) on HK_SEC.HealthKind_id = DopDispSecond.HealthKind_id
					left join [YesNo] [IsFinishSecond] with (nolock) on [IsFinishSecond].[YesNo_id] = [DopDispSecond].[EvnPLDispDop13_IsEndStage]
					outer apply(
						select top 1
							DDIC.DopDispInfoConsent_IsAgree
						from
							v_DopDispInfoConsent DDIC (nolock)
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id
							and ST.SurveyType_Code = 48
					) DDICDataSecond
					outer apply(
						select top 1 EvnPLDispDop13_id, Lpu_id
						from v_EvnPLDispDop13 (nolock)
						where Person_id = PS.Person_id
							and YEAR(EvnPLDispDop13_setDate) = :PersonDopDisp_Year
							and Lpu_id " . getLpuIdFilter($data, true) . "
							and ISNULL(DispClass_id,1) = 1
					) EPLDD13AL
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = ISNULL(EPLDD13.Lpu_id, EPLDD13AL.Lpu_id)
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					if (!empty($data['UslugaComplex_id'])) {
						$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
						$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
					}
					$query .= "
						outer apply(
							select top 1
								UslugaComplex_id
							from
								v_EvnUslugaDispDop (nolock)
							where
								EvnUslugaDispDop_IsVizitCode = 2
								and EvnUslugaDispDop_pid = DopDispSecond.EvnPLDispDop13_id
						) euddvizit
						left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
					";
				}

				break;

			case 'EvnPLDispProf':
				if (isset($data['EvnPLDispProf_setDate'])) {
					$filter .= " and [EPLDP].EvnPLDispProf_setDate = cast(:EvnPLDispProf_setDate as datetime) ";
					$queryParams['EvnPLDispProf_setDate'] = $data['EvnPLDispProf_setDate'];
				}
				if (isset($data['EvnPLDispProf_setDate_Range'][0])) {
					$filter .= " and [EPLDP].EvnPLDispProf_setDate >= cast(:EvnPLDispProf_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispProf_setDate_Range_0'] = $data['EvnPLDispProf_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispProf_setDate_Range'][1])) {
					$filter .= " and [EPLDP].EvnPLDispProf_setDate <= cast(:EvnPLDispProf_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispProf_setDate_Range_1'] = $data['EvnPLDispProf_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispProf_disDate'])) {
					$filter .= " and [EPLDP].EvnPLDispProf_disDate = cast(:EvnPLDispProf_disDate as datetime) ";
					$queryParams['EvnPLDispProf_disDate'] = $data['EvnPLDispProf_disDate'];
				}
				if (isset($data['EvnPLDispProf_disDate_Range'][0])) {
					$filter .= " and [EPLDP].EvnPLDispProf_disDate >= cast(:EvnPLDispProf_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispProf_disDate_Range_0'] = $data['EvnPLDispProf_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispProf_disDate_Range'][1])) {
					$filter .= " and [EPLDP].EvnPLDispProf_disDate <= cast(:EvnPLDispProf_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispProf_disDate_Range_1'] = $data['EvnPLDispProf_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispProf_IsFinish'])) {
					$filter .= " and isnull([EPLDP].EvnPLDispProf_IsEndStage, 1) = :EvnPLDispProf_IsFinish ";
					$queryParams['EvnPLDispProf_IsFinish'] = $data['EvnPLDispProf_IsFinish'];
				}
				if (isset($data['EvnPLDispProf_isPaid'])) {
					$filter .= " and isnull([EPLDP].EvnPLDispProf_isPaid, 1) = :EvnPLDispProf_isPaid ";
					$queryParams['EvnPLDispProf_isPaid'] = $data['EvnPLDispProf_isPaid'];
				}
				if (isset($data['EvnPLDispProf_isMobile'])) {
					$filter .= " and ISNULL([EPLDP].EvnPLDispProf_isMobile,1) = :EvnPLDispProf_isMobile ";
					$queryParams['EvnPLDispProf_isMobile'] = $data['EvnPLDispProf_isMobile'];
				}
				if (isset($data['EvnPLDispProf_IsRefusal'])) {
					$filter .= " and EPLDP.EvnPLDispProf_IsRefusal = :EvnPLDispProf_IsRefusal ";
					$queryParams['EvnPLDispProf_IsRefusal'] = $data['EvnPLDispProf_IsRefusal'];
				}

				if (isset($data['EvnPLDispProf_HealthKind_id'])) {
					$filter .= " and [EPLDP].HealthKind_id = :EvnPLDispProf_HealthKind_id ";
					$queryParams['EvnPLDispProf_HealthKind_id'] = $data['EvnPLDispProf_HealthKind_id'];
				}

				if (isset($data['PersonDopDisp_Year'])) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				} else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispProf] [EPLDP] with (nolock) on EPLDP.Server_id = PS.Server_id and EPLDP.PersonEvn_id = PS.PersonEvn_id and [EPLDP].Lpu_id " . $this->getLpuIdFilter($data) . " and YEAR(EvnPLDispProf_setDate) = :PersonDopDisp_Year
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispProf] [EPLDP] with (nolock) on [PS].[Person_id] = [EPLDP].[Person_id] and [EPLDP].Lpu_id " . $this->getLpuIdFilter($data) . " and YEAR(EvnPLDispProf_setDate) = :PersonDopDisp_Year
					";
				}
				if (isset($data['EvnPLDisp_UslugaComplex']) && $data['EvnPLDisp_UslugaComplex'] > 0) {
					$filter .= "
						and exists (
							select top 1 UslugaComplex_id
							from v_EvnUslugaDispDop with (nolock)
							where EvnUslugaDispDop_didDate is not null
								and UslugaComplex_id = :EvnPLDisp_UslugaComplex
								and EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
						)
					";
					$queryParams['EvnPLDisp_UslugaComplex'] = $data['EvnPLDisp_UslugaComplex'];
				}

				$query .= "
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDP.EvnPLDispProf_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDP].[EvnPLDispProf_IsEndStage], 1)
					left join v_HealthKind HK with (nolock) on HK.HealthKind_id = EPLDP.HealthKind_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id
				";

				if (!empty($data['Disp_MedStaffFact_id']) || !empty($data['Disp_LpuSection_id']) || !empty($data['Disp_LpuBuilding_id'])) {
					$disp_msf = "";
					$disp_msf2 = "";
					$disp_ls = "";
					$disp_ls2 = "";
					$disp_b = "";
					$disp_b2 = "";
					$join1 = "";
					$join2 = "";
					if (!empty($data['Disp_MedStaffFact_id'])) {
						$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
						$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
						$queryParams['MedStaffFact_id'] = $data['Disp_MedStaffFact_id'];
					} else {
						if (!empty($data['Disp_LpuSection_id'])) {
							$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
							$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
							$queryParams['LpuSection_id'] = $data['Disp_LpuSection_id'];
						}
						if (!empty($data['Disp_LpuBuilding_id'])) {
							$join1 = "
								left join v_LpuSection ls1 with (nolock) on ls1.LpuSection_id = msf1.LpuSection_id
							";
							$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
							$join2 = "
								left join v_LpuSection ls3 with (nolock) on ls3.LpuSection_id = msf2.LpuSection_uid
							";
							$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
							$queryParams['LpuBuilding_id'] = $data['Disp_LpuBuilding_id'];
						}
					}
					$query .= "
						outer apply (
							select top 1 msf1.EvnVizitDispDop_id 
							from v_EvnVizitDispDop msf1 with (nolock) 
							{$join1}
							where msf1.EvnVizitDispDop_pid = EPLDP.EvnPLDispProf_id 
							{$disp_b}
							{$disp_msf}
							{$disp_ls}
						) evapply
						outer apply (
							select top 1 msf2.EvnUslugaDispDop_id 
							from v_EvnUslugaDispDop msf2 with (nolock)
							{$join2} 
							where msf2.EvnUslugaDispDop_pid = EPLDP.EvnPLDispProf_id
							{$disp_b2}
							{$disp_msf2}
							{$disp_ls2}
						) euapply
					";
					$filter .= " and (evapply.EvnVizitDispDop_id is not null or euapply.EvnUslugaDispDop_id is not null)";
				}

				/* if (in_array($data['session']['region']['nick'], array('buryatiya'))) {
				  if (!empty($data['UslugaComplex_id'])) {
				  $filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				  $queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
				  }
				  $query .= "
				  outer apply(
				  select top 1
				  UslugaComplex_id
				  from
				  v_EvnUslugaDispDop (nolock)
				  where
				  EvnUslugaDispDop_IsVizitCode = 2
				  and EvnUslugaDispDop_pid = EPLDP.EvnPLDispProf_id
				  ) euddvizit
				  left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
				  ";
				  } */

				break;

			case 'EvnPLDispScreen':
				if (isset($data['EvnPLDispScreen_setDate'])) {
					$filter .= " and [EPLDS].EvnPLDispScreen_setDate = cast(:EvnPLDispScreen_setDate as datetime) ";
					$queryParams['EvnPLDispScreen_setDate'] = $data['EvnPLDispScreen_setDate'];
				}
				if (isset($data['EvnPLDispScreen_setDate_Range'][0])) {
					$filter .= " and [EPLDS].EvnPLDispScreen_setDate >= cast(:EvnPLDispScreen_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispScreen_setDate_Range_0'] = $data['EvnPLDispScreen_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispScreen_setDate_Range'][1])) {
					$filter .= " and [EPLDS].EvnPLDispScreen_setDate <= cast(:EvnPLDispScreen_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispScreen_setDate_Range_1'] = $data['EvnPLDispScreen_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispScreen_disDate'])) {
					$filter .= " and [EPLDS].EvnPLDispScreen_disDate = cast(:EvnPLDispScreen_disDate as datetime) ";
					$queryParams['EvnPLDispScreen_disDate'] = $data['EvnPLDispScreen_disDate'];
				}
				if (isset($data['EvnPLDispScreen_disDate_Range'][0])) {
					$filter .= " and [EPLDS].EvnPLDispScreen_disDate >= cast(:EvnPLDispScreen_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispScreen_disDate_Range_0'] = $data['EvnPLDispScreen_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispScreen_disDate_Range'][1])) {
					$filter .= " and [EPLDS].EvnPLDispScreen_disDate <= cast(:EvnPLDispScreen_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispScreen_disDate_Range_1'] = $data['EvnPLDispScreen_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispScreen_IsEndStage'])) {
					$filter .= " and isnull([EPLDS].EvnPLDispScreen_IsEndStage, 1) = :EvnPLDispScreen_IsEndStage ";
					$queryParams['EvnPLDispScreen_IsEndStage'] = $data['EvnPLDispScreen_IsEndStage'];
				}
				if (isset($data['AgeGroupDisp_id'])) {
					$filter .= " and EPLDS.AgeGroupDisp_id = :AgeGroupDisp_id ";
					$queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
				}

				if (isset($data['PersonDopDisp_Year'])) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				} else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= "
						inner join [v_EvnPLDispScreen] [EPLDS] with (nolock) on EPLDS.Server_id = PS.Server_id and EPLDS.PersonEvn_id = PS.PersonEvn_id and [EPLDS].Lpu_id " . $this->getLpuIdFilter($data) . " and YEAR(EvnPLDispScreen_setDate) = :PersonDopDisp_Year
					";
				} else {
					$query .= "
						inner join [v_EvnPLDispScreen] [EPLDS] with (nolock) on [PS].[Person_id] = [EPLDS].[Person_id] and [EPLDS].Lpu_id " . $this->getLpuIdFilter($data) . " and YEAR(EvnPLDispScreen_setDate) = :PersonDopDisp_Year
					";
				}

				$query .= "
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = EPLDS.AgeGroupDisp_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDS].[EvnPLDispScreen_IsEndStage], 1)
				";

				break;

			case 'EvnPLDispScreenChild':
				if (isset($data['EvnPLDispScreenChild_setDate'])) {
					$filter .= " and [EPLDS].EvnPLDispScreenChild_setDate = cast(:EvnPLDispScreenChild_setDate as datetime) ";
					$queryParams['EvnPLDispScreenChild_setDate'] = $data['EvnPLDispScreenChild_setDate'];
				}
				if (isset($data['EvnPLDispScreenChild_setDate_Range'][0])) {
					$filter .= " and [EPLDS].EvnPLDispScreenChild_setDate >= cast(:EvnPLDispScreenChild_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispScreenChild_setDate_Range_0'] = $data['EvnPLDispScreenChild_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispScreenChild_setDate_Range'][1])) {
					$filter .= " and [EPLDS].EvnPLDispScreenChild_setDate <= cast(:EvnPLDispScreenChild_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispScreenChild_setDate_Range_1'] = $data['EvnPLDispScreenChild_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispScreenChild_disDate'])) {
					$filter .= " and [EPLDS].EvnPLDispScreenChild_disDate = cast(:EvnPLDispScreenChild_disDate as datetime) ";
					$queryParams['EvnPLDispScreenChild_disDate'] = $data['EvnPLDispScreenChild_disDate'];
				}
				if (isset($data['EvnPLDispScreenChild_disDate_Range'][0])) {
					$filter .= " and [EPLDS].EvnPLDispScreenChild_disDate >= cast(:EvnPLDispScreenChild_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispScreenChild_disDate_Range_0'] = $data['EvnPLDispScreenChild_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispScreenChild_disDate_Range'][1])) {
					$filter .= " and [EPLDS].EvnPLDispScreenChild_disDate <= cast(:EvnPLDispScreenChild_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispScreenChild_disDate_Range_1'] = $data['EvnPLDispScreenChild_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispScreenChild_IsEndStage'])) {
					$filter .= " and isnull([EPLDS].EvnPLDispScreenChild_IsEndStage, 1) = :EvnPLDispScreenChild_IsEndStage ";
					$queryParams['EvnPLDispScreenChild_IsEndStage'] = $data['EvnPLDispScreenChild_IsEndStage'];
				}
				if (isset($data['AgeGroupDisp_id'])) {
					$filter .= " and EPLDS.AgeGroupDisp_id = :AgeGroupDisp_id ";
					$queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
				}

				if (isset($data['PersonDopDisp_Year'])) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				} else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= "
						inner join [v_EvnPLDispScreenChild] [EPLDS] with (nolock) on EPLDS.Server_id = PS.Server_id and EPLDS.PersonEvn_id = PS.PersonEvn_id and [EPLDS].Lpu_id " . $this->getLpuIdFilter($data) . " and YEAR(EvnPLDispScreenChild_setDate) = :PersonDopDisp_Year
					";
				} else {
					$query .= "
						inner join [v_EvnPLDispScreenChild] [EPLDS] with (nolock) on [PS].[Person_id] = [EPLDS].[Person_id] and [EPLDS].Lpu_id " . $this->getLpuIdFilter($data) . " and YEAR(EvnPLDispScreenChild_setDate) = :PersonDopDisp_Year
					";
				}

				$query .= "
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = EPLDS.AgeGroupDisp_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDS].[EvnPLDispScreenChild_IsEndStage], 1)
				";

				break;

			case 'EvnPLDispDop':
				if (isset($data['EvnPLDispDop_setDate'])) {
					$filter .= " and [EPLDD].EvnPLDispDop_setDate = cast(:EvnPLDispDop_setDate as datetime) ";
					$queryParams['EvnPLDispDop_setDate'] = $data['EvnPLDispDop_setDate'];
				}
				if (isset($data['EvnPLDispDop_setDate_Range'][0])) {
					$filter .= " and [EPLDD].EvnPLDispDop_setDate >= cast(:EvnPLDispDop_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop_setDate_Range_0'] = $data['EvnPLDispDop_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop_setDate_Range'][1])) {
					$filter .= " and [EPLDD].EvnPLDispDop_setDate <= cast(:EvnPLDispDop_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop_setDate_Range_1'] = $data['EvnPLDispDop_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop_disDate'])) {
					$filter .= " and [EPLDD].EvnPLDispDop_disDate = cast(:EvnPLDispDop_disDate as datetime) ";
					$queryParams['EvnPLDispDop_disDate'] = $data['EvnPLDispDop_disDate'];
				}
				if (isset($data['EvnPLDispDop_disDate_Range'][0])) {
					$filter .= " and [EPLDD].EvnPLDispDop_disDate >= cast(:EvnPLDispDop_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop_disDate_Range_0'] = $data['EvnPLDispDop_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispDop_disDate_Range'][1])) {
					$filter .= " and [EPLDD].EvnPLDispDop_disDate <= cast(:EvnPLDispDop_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop_disDate_Range_1'] = $data['EvnPLDispDop_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispDop_VizitCount'])) {
					$filter .= " and [EPLDD].EvnPLDispDop_VizitCount = :EvnPLDispDop_VizitCount ";
					$queryParams['EvnPLDispDop_VizitCount'] = $data['EvnPLDispDop_VizitCount'];
				}
				if (isset($data['EvnPLDispDop_VizitCount_From'])) {
					$filter .= " and [EPLDD].EvnPLDispDop_VizitCount >= :EvnPLDispDop_VizitCount_From ";
					$queryParams['EvnPLDispDop_VizitCount_From'] = $data['EvnPLDispDop_VizitCount_From'];
				}
				if (isset($data['EvnPLDispDop_VizitCount_To'])) {
					$filter .= " and [EPLDD].EvnPLDispDop_VizitCount <= :EvnPLDispDop_VizitCount_To ";
					$queryParams['EvnPLDispDop_VizitCount_To'] = $data['EvnPLDispDop_VizitCount_To'];
				}
				if (isset($data['EvnPLDispDop_IsFinish'])) {
					$filter .= " and isnull([EPLDD].EvnPLDispDop_IsFinish, 1) = :EvnPLDispDop_IsFinish ";
					$queryParams['EvnPLDispDop_IsFinish'] = $data['EvnPLDispDop_IsFinish'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on EPLDD.Server_id = PS.Server_id and EPLDD.PersonEvn_id = PS.PersonEvn_id and [EPLDD].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}
				// группа здоровья
				if (isset($data['EvnPLDispDop_HealthKind_id'])) {

					$queryParams['EvnPLDispDop_HealthKind_id'] = $data['EvnPLDispDop_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDD].[EvnPLDispDop_id] and isnull([EPLDD].EvnPLDispDop_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispDop_HealthKind_id and EVPLDD.Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				";
				/*
				  $query .= "
				  inner join [v_EvnPLDispDop] [EPLDD] on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
				  left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				  ";
				 */
				break;

			case 'EvnPLDispTeen14':
				if (isset($data['EvnPLDispTeen14_setDate'])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_setDate = cast(:EvnPLDispTeen14_setDate as datetime) ";
					$queryParams['EvnPLDispTeen14_setDate'] = $data['EvnPLDispTeen14_setDate'];
				}
				if (isset($data['EvnPLDispTeen14_setDate_Range'][0])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_setDate >= cast(:EvnPLDispTeen14_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeen14_setDate_Range_0'] = $data['EvnPLDispTeen14_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispTeen14_setDate_Range'][1])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_setDate <= cast(:EvnPLDispTeen14_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeen14_setDate_Range_1'] = $data['EvnPLDispTeen14_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispTeen14_disDate'])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_disDate = cast(:EvnPLDispTeen14_disDate as datetime) ";
					$queryParams['EvnPLDispTeen14_disDate'] = $data['EvnPLDispTeen14_disDate'];
				}
				if (isset($data['EvnPLDispTeen14_disDate_Range'][0])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_disDate >= cast(:EvnPLDispTeen14_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeen14_disDate_Range_0'] = $data['EvnPLDispTeen14_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispTeen14_disDate_Range'][1])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_disDate <= cast(:EvnPLDispTeen14_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeen14_disDate_Range_1'] = $data['EvnPLDispTeen14_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispTeen14_VizitCount'])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_VizitCount = :EvnPLDispTeen14_VizitCount ";
					$queryParams['EvnPLDispTeen14_VizitCount'] = $data['EvnPLDispTeen14_VizitCount'];
				}
				if (isset($data['EvnPLDispTeen14_VizitCount_From'])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_VizitCount >= :EvnPLDispTeen14_VizitCount_From ";
					$queryParams['EvnPLDispTeen14_VizitCount_From'] = $data['EvnPLDispTeen14_VizitCount_From'];
				}
				if (isset($data['EvnPLDispTeen14_VizitCount_To'])) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_VizitCount <= :EvnPLDispTeen14_VizitCount_To ";
					$queryParams['EvnPLDispTeen14_VizitCount_To'] = $data['EvnPLDispTeen14_VizitCount_To'];
				}
				if (isset($data['EvnPLDispTeen14_IsFinish'])) {
					$filter .= " and isnull([EPLDT14].EvnPLDispTeen14_IsFinish, 1) = :EvnPLDispTeen14_IsFinish ";
					$queryParams['EvnPLDispTeen14_IsFinish'] = $data['EvnPLDispTeen14_IsFinish'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on EPLDT14.Server_id = PS.Server_id and EPLDT14.PersonEvn_id = PS.PersonEvn_id and [EPLDT14].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on [PS].[Person_id] = [EPLDT14].[Person_id] and [EPLDT14].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}
				// группа здоровья
				if (isset($data['EvnPLDispTeen14_HealthKind_id'])) {

					$queryParams['EvnPLDispTeen14_HealthKind_id'] = $data['EvnPLDispTeen14_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispTeen14] [EVPLDT14] with (nolock) on [EVPLDT14].[EvnVizitDispTeen14_pid] = [EPLDT14].[EvnPLDispTeen14_id] and isnull([EPLDT14].EvnPLDispTeen14_IsFinish, 1) = 2 and [EVPLDT14].[Teen14DispSpecType_id] = 1 and [EVPLDT14].[HealthKind_id] = :EvnPLDispTeen14_HealthKind_id and EVPLDT14.Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				";
				/*
				  $query .= "
				  inner join [v_EvnPLDispTeen14] [EPLDT14] on [PS].[Person_id] = [EPLDT14].[Person_id] and [EPLDT14].Lpu_id = :Lpu_id
				  left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				  ";
				 */
				break;

			case 'EvnPLDispOrp':
				if (isset($data['EvnPLDispOrp_setDate'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate = cast(:EvnPLDispOrp_setDate as datetime) ";
					$queryParams['EvnPLDispOrp_setDate'] = $data['EvnPLDispOrp_setDate'];
				}
				if (isset($data['EvnPLDispOrp_setDate_Range'][0])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate >= cast(:EvnPLDispOrp_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_0'] = $data['EvnPLDispOrp_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispOrp_setDate_Range'][1])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate <= cast(:EvnPLDispOrp_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_1'] = $data['EvnPLDispOrp_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispOrp_disDate'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate = cast(:EvnPLDispOrp_disDate as datetime) ";
					$queryParams['EvnPLDispOrp_disDate'] = $data['EvnPLDispOrp_disDate'];
				}
				if (isset($data['EvnPLDispOrp_disDate_Range'][0])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate >= cast(:EvnPLDispOrp_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_0'] = $data['EvnPLDispOrp_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispOrp_disDate_Range'][1])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate <= cast(:EvnPLDispOrp_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_1'] = $data['EvnPLDispOrp_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispOrp_VizitCount'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
					$queryParams['EvnPLDispOrp_VizitCount'] = $data['EvnPLDispOrp_VizitCount'];
				}
				if (isset($data['EvnPLDispOrp_VizitCount_From'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
					$queryParams['EvnPLDispOrp_VizitCount_From'] = $data['EvnPLDispOrp_VizitCount_From'];
				}
				if (isset($data['EvnPLDispOrp_VizitCount_To'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
					$queryParams['EvnPLDispOrp_VizitCount_To'] = $data['EvnPLDispOrp_VizitCount_To'];
				}
				if (isset($data['EvnPLDispOrp_IsFinish'])) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
					$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
				}
				if (isset($data['EvnPLDispOrp_isPaid'])) {
					if ($data['session']['region']['nick'] == 'ufa') {
						// https://redmine.swan.perm.ru/issues/56232
						if ($data['EvnPLDispOrp_isPaid'] == 2) {
							$filter .= " and exists (select top 1 EvnVizitDispOrp_id from v_EvnVizitDispOrp t1 with (nolock) where t1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id and ISNULL(t1.EvnVizitDispOrp_IsPaid, 1) = 2)";
							$filter .= " and not exists (select top 1 EvnVizitDispOrp_id from v_EvnVizitDispOrp t1 with (nolock) where t1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id and ISNULL(t1.EvnVizitDispOrp_IsPaid, 1) = 1)";
						} else {
							$filter .= " and case when ISNULL([EPLDO].EvnPLDispOrp_VizitCount, 0) = 0 then 1 else (select count(EvnVizitDispOrp_id) from v_EvnVizitDispOrp t1 with (nolock) where t1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id and ISNULL(t1.EvnVizitDispOrp_IsPaid, 1) = 1) end > 0";
						}
					} else {
						$filter .= " and isnull([EPLDO].EvnPLDispOrp_isPaid, 1) = :EvnPLDispOrp_isPaid ";
						$queryParams['EvnPLDispOrp_isPaid'] = $data['EvnPLDispOrp_isPaid'];
					}
				}
				if (isset($data['EvnPLDispOrp_isMobile'])) {
					$filter .= " and ISNULL([EPLDO].EvnPLDispOrp_isMobile,1) = :EvnPLDispOrp_isMobile ";
					$queryParams['EvnPLDispOrp_isMobile'] = $data['EvnPLDispOrp_isMobile'];
				}
				if (!empty($data['EvnPLDispOrp_IsRefusal'])) {
					$filter .= " and EPLDO.EvnPLDispOrp_IsRefusal = :EvnPLDispOrp_IsRefusal ";
					$queryParams['EvnPLDispOrp_IsRefusal'] = $data['EvnPLDispOrp_IsRefusal'];
				}
				if (isset($data['EvnPLDispOrp_IsTwoStage'])) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsTwoStage, 1) = :EvnPLDispOrp_IsTwoStage ";
					$queryParams['EvnPLDispOrp_IsTwoStage'] = $data['EvnPLDispOrp_IsTwoStage'];
				}
				if (isset($data['EvnPLDispOrp_HealthKind_id'])) {
					$filter .= " and [HK].HealthKind_id = :EvnPLDispOrp_HealthKind_id ";
					$queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
				}
				if (isset($data['EvnPLDispOrp_ChildStatusType_id'])) {
					$filter .= " and [EPLDO].ChildStatusType_id = :EvnPLDispOrp_ChildStatusType_id ";
					$queryParams['EvnPLDispOrp_ChildStatusType_id'] = $data['EvnPLDispOrp_ChildStatusType_id'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . " and [EPLDO].DispClass_id IN (3,7)
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . " and [EPLDO].DispClass_id IN (3,7)
					";
				}

				if (isset($data['EvnPLDisp_UslugaComplex']) && $data['EvnPLDisp_UslugaComplex'] > 0) {
					$query .= "
						left join v_EvnVizitDispOrp EVDO (nolock) on EVDO.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id
						inner join v_EvnUslugaDispOrp EUDO (nolock) on (
							EUDO.EvnUslugaDispOrp_pid in (EPLDO.EvnPLDispOrp_id, EVDO.EvnVizitDispOrp_id)
							and EUDO.UslugaComplex_id = :EvnPLDisp_UslugaComplex
							)
					";
					$queryParams['EvnPLDisp_UslugaComplex'] = $data['EvnPLDisp_UslugaComplex'];
				}
				if (!empty($data['Disp_MedStaffFact_id']) || !empty($data['Disp_LpuSection_id']) || !empty($data['Disp_LpuBuilding_id'])) {
					$disp_msf = "";
					$disp_msf2 = "";
					$disp_ls = "";
					$disp_ls2 = "";
					$disp_b = "";
					$disp_b2 = "";
					$join1 = "";
					$join2 = "";
					if (!empty($data['Disp_MedStaffFact_id'])) {
						$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
						$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
						$queryParams['MedStaffFact_id'] = $data['Disp_MedStaffFact_id'];
					} else {
						if (!empty($data['Disp_LpuSection_id'])) {
							$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
							$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
							$queryParams['LpuSection_id'] = $data['Disp_LpuSection_id'];
						}
						if (!empty($data['Disp_LpuBuilding_id'])) {
							$join1 = "
								left join v_LpuSection ls1 with (nolock) on ls1.LpuSection_id = msf1.LpuSection_id
							";
							$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
							$join2 = "
								left join v_LpuSection ls3 with (nolock) on ls3.LpuSection_id = msf2.LpuSection_uid
							";
							$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
							$queryParams['LpuBuilding_id'] = $data['Disp_LpuBuilding_id'];
						}
					}
					$filter .= " 
					and (exists (
						select top 1 msf1.EvnVizitDispOrp_id
						from v_EvnVizitDispOrp msf1 with (nolock) 
						left join v_EvnUslugaDispOrp msf2 (nolock) on msf2.EvnUslugaDispOrp_pid = msf1.EvnVizitDispOrp_id
						{$join1}
						where msf1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id 
						{$disp_b}
						{$disp_msf}
						{$disp_ls}
					) or exists (
						select top 1 msf2.EvnUslugaDispOrp_id
						from v_EvnUslugaDispOrp msf2 with (nolock)
						{$join2} 
						where msf2.EvnUslugaDispOrp_pid = EPLDO.EvnPLDispOrp_id 
						{$disp_b2}
						{$disp_msf2}
						{$disp_ls2}
					))";
				}
				// группа здоровья
				/* if ( isset($data['EvnPLDispOrp_HealthKind_id']) ) {

				  $queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
				  $query .= "
				  inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDO].[EvnPLDispOrp_id] and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispOrp_HealthKind_id and EVPLDD.Lpu_id = :Lpu_id
				  ";
				  } */

				$query .= "
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDO.EvnPLDispOrp_id
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
					left join [YesNo] [IsTwoStage] with (nolock) on [IsTwoStage].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsTwoStage]
					left join [v_AssessmentHealth] AH with (nolock) on [AH].[EvnPLDisp_id] = [EPLDO].[EvnPLDispOrp_id]
					left join [v_HealthKind] [HK] with (nolock) on [HK].[HealthKind_id] = [AH].[HealthKind_id]
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					if (!empty($data['UslugaComplex_id'])) {
						$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
						$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
					}
					$query .= "
						outer apply(
							select top 1
								UslugaComplex_id
							from
								v_EvnUslugaDispOrp (nolock)
							where
								EvnUslugaDispOrp_IsVizitCode = 2
								and EvnUslugaDispOrp_pid = EPLDO.EvnPLDispOrp_id
						) euddvizit
						left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
					";
				}

				break;

			case 'EvnPLDispOrpOld':
				if (isset($data['EvnPLDispOrp_setDate'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate = cast(:EvnPLDispOrp_setDate as datetime) ";
					$queryParams['EvnPLDispOrp_setDate'] = $data['EvnPLDispOrp_setDate'];
				}
				if (isset($data['EvnPLDispOrp_setDate_Range'][0])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate >= cast(:EvnPLDispOrp_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_0'] = $data['EvnPLDispOrp_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispOrp_setDate_Range'][1])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate <= cast(:EvnPLDispOrp_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_1'] = $data['EvnPLDispOrp_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispOrp_disDate'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate = cast(:EvnPLDispOrp_disDate as datetime) ";
					$queryParams['EvnPLDispOrp_disDate'] = $data['EvnPLDispOrp_disDate'];
				}
				if (isset($data['EvnPLDispOrp_disDate_Range'][0])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate >= cast(:EvnPLDispOrp_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_0'] = $data['EvnPLDispOrp_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispOrp_disDate_Range'][1])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate <= cast(:EvnPLDispOrp_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_1'] = $data['EvnPLDispOrp_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispOrp_VizitCount'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
					$queryParams['EvnPLDispOrp_VizitCount'] = $data['EvnPLDispOrp_VizitCount'];
				}
				if (isset($data['EvnPLDispOrp_VizitCount_From'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
					$queryParams['EvnPLDispOrp_VizitCount_From'] = $data['EvnPLDispOrp_VizitCount_From'];
				}
				if (isset($data['EvnPLDispOrp_VizitCount_To'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
					$queryParams['EvnPLDispOrp_VizitCount_To'] = $data['EvnPLDispOrp_VizitCount_To'];
				}
				if (isset($data['EvnPLDispOrp_IsFinish'])) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
					$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}
				// группа здоровья
				if (isset($data['EvnPLDispOrp_HealthKind_id'])) {

					$queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDO].[EvnPLDispOrp_id] and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispOrp_HealthKind_id and EVPLDD.Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
				";
				break;

			case 'EvnPLDispOrpSec':
				if (isset($data['EvnPLDispOrp_setDate'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate = cast(:EvnPLDispOrp_setDate as datetime) ";
					$queryParams['EvnPLDispOrp_setDate'] = $data['EvnPLDispOrp_setDate'];
				}
				if (isset($data['EvnPLDispOrp_setDate_Range'][0])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate >= cast(:EvnPLDispOrp_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_0'] = $data['EvnPLDispOrp_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispOrp_setDate_Range'][1])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate <= cast(:EvnPLDispOrp_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_1'] = $data['EvnPLDispOrp_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispOrp_disDate'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate = cast(:EvnPLDispOrp_disDate as datetime) ";
					$queryParams['EvnPLDispOrp_disDate'] = $data['EvnPLDispOrp_disDate'];
				}
				if (isset($data['EvnPLDispOrp_disDate_Range'][0])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate >= cast(:EvnPLDispOrp_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_0'] = $data['EvnPLDispOrp_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispOrp_disDate_Range'][1])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate <= cast(:EvnPLDispOrp_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_1'] = $data['EvnPLDispOrp_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispOrp_VizitCount'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
					$queryParams['EvnPLDispOrp_VizitCount'] = $data['EvnPLDispOrp_VizitCount'];
				}
				if (isset($data['EvnPLDispOrp_VizitCount_From'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
					$queryParams['EvnPLDispOrp_VizitCount_From'] = $data['EvnPLDispOrp_VizitCount_From'];
				}
				if (isset($data['EvnPLDispOrp_VizitCount_To'])) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
					$queryParams['EvnPLDispOrp_VizitCount_To'] = $data['EvnPLDispOrp_VizitCount_To'];
				}
				if (isset($data['EvnPLDispOrp_IsFinish'])) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
					$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
				}
				if (isset($data['EvnPLDispOrp_isPaid'])) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_isPaid, 1) = :EvnPLDispOrp_isPaid ";
					$queryParams['EvnPLDispOrp_isPaid'] = $data['EvnPLDispOrp_isPaid'];
				}
				if (isset($data['EvnPLDispOrp_HealthKind_id'])) {
					$filter .= " and [HK].HealthKind_id = :EvnPLDispOrp_HealthKind_id ";
					$queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
				}

				if (isset($data['EvnPLDispOrp_ChildStatusType_id'])) {
					$filter .= " and [EPLDO].ChildStatusType_id = :EvnPLDispOrp_ChildStatusType_id ";
					$queryParams['EvnPLDispOrp_ChildStatusType_id'] = $data['EvnPLDispOrp_ChildStatusType_id'];
				}

				if (isset($data['EvnPLDispOrp_IsTwoStage'])) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsTwoStage, 1) = :EvnPLDispOrp_IsTwoStage ";
					$queryParams['EvnPLDispOrp_IsTwoStage'] = $data['EvnPLDispOrp_IsTwoStage'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . " and [EPLDO].DispClass_id IN (4,8)
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . " and [EPLDO].DispClass_id IN (4,8)
					";
				}
				// группа здоровья
				/* if ( isset($data['EvnPLDispOrp_HealthKind_id']) ) {

				  $queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
				  $query .= "
				  inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDO].[EvnPLDispOrp_id] and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispOrp_HealthKind_id and EVPLDD.Lpu_id = :Lpu_id
				  ";
				  } */

				$query .= "
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDO.EvnPLDispOrp_id
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
					left join [YesNo] [IsTwoStage] with (nolock) on [IsTwoStage].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsTwoStage]
					left join [v_AssessmentHealth] AH with (nolock) on [AH].[EvnPLDisp_id] = [EPLDO].[EvnPLDispOrp_id]
					left join [v_HealthKind] [HK] with (nolock) on [HK].[HealthKind_id] = [AH].[HealthKind_id]
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					if (!empty($data['UslugaComplex_id'])) {
						$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
						$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
					}
					$query .= "
						outer apply(
							select top 1
								UslugaComplex_id
							from
								v_EvnUslugaDispOrp (nolock)
							where
								EvnUslugaDispOrp_IsVizitCode = 2
								and EvnUslugaDispOrp_pid = EPLDO.EvnPLDispOrp_id
						) euddvizit
						left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
					";
				}

				break;

			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
				if (isset($data['EvnPLDispTeenInspection_setDate'])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_setDate = cast(:EvnPLDispTeenInspection_setDate as datetime) ";
					$queryParams['EvnPLDispTeenInspection_setDate'] = $data['EvnPLDispTeenInspection_setDate'];
				}
				if (isset($data['EvnPLDispTeenInspection_setDate_Range'][0])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_setDate >= cast(:EvnPLDispTeenInspection_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_setDate_Range_0'] = $data['EvnPLDispTeenInspection_setDate_Range'][0];
				}
				if (isset($data['EvnPLDispTeenInspection_setDate_Range'][1])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_setDate <= cast(:EvnPLDispTeenInspection_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_setDate_Range_1'] = $data['EvnPLDispTeenInspection_setDate_Range'][1];
				}
				if (isset($data['EvnPLDispTeenInspection_HealthKind_id'])) {
					$filter .= " and [HK].HealthKind_id = :EvnPLDispTeenInspection_HealthKind_id ";
					$queryParams['EvnPLDispTeenInspection_HealthKind_id'] = $data['EvnPLDispTeenInspection_HealthKind_id'];
				}
				if (isset($data['EvnPLDispTeenInspection_isPaid'])) {
					if ($data['session']['region']['nick'] == 'ufa') {
						if ($data['EvnPLDispTeenInspection_isPaid'] == 2) {
							$filter .= " and exists (select top 1 EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection t1 with (nolock) where t1.EvnPLDispTeenInspection_pid = EPLDTI.EvnPLDispTeenInspection_id and ISNULL(t1.EvnPLDispTeenInspection_IsPaid, 1) = 2)";
						} else if ($data['EvnPLDispTeenInspection_isPaid'] == 1) {
							$filter .= " and case when ISNULL([EPLDTI].EvnPLDispTeenInspection_VizitCount, 0) = 0 then 0 else (select count(EvnPLDispTeenInspection_id) from v_EvnPLDispTeenInspection t1 with (nolock) where t1.EvnPLDispTeenInspection_pid = EPLDTI.EvnPLDispTeenInspection_id and ISNULL(t1.EvnPLDispTeenInspection_IsPaid, 1) = 2) end = 0";
						}
					} else {
						$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_isPaid, 1) = :EvnPLDispTeenInspection_isPaid ";
						$queryParams['EvnPLDispTeenInspection_isPaid'] = $data['EvnPLDispTeenInspection_isPaid'];
					}
				}
				if (isset($data['EvnPLDispTeenInspection_disDate'])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_disDate = cast(:EvnPLDispTeenInspection_disDate as datetime) ";
					$queryParams['EvnPLDispTeenInspection_disDate'] = $data['EvnPLDispTeenInspection_disDate'];
				}
				if (isset($data['EvnPLDispTeenInspection_disDate_Range'][0])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_disDate >= cast(:EvnPLDispTeenInspection_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_disDate_Range_0'] = $data['EvnPLDispTeenInspection_disDate_Range'][0];
				}
				if (isset($data['EvnPLDispTeenInspection_disDate_Range'][1])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_disDate <= cast(:EvnPLDispTeenInspection_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_disDate_Range_1'] = $data['EvnPLDispTeenInspection_disDate_Range'][1];
				}
				if (isset($data['EvnPLDispTeenInspection_IsFinish'])) {
					$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_IsFinish, 1) = :EvnPLDispTeenInspection_IsFinish ";
					$queryParams['EvnPLDispTeenInspection_IsFinish'] = $data['EvnPLDispTeenInspection_IsFinish'];
				}
				if (isset($data['EvnPLDispTeenInspection_IsRefusal'])) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_IsRefusal = :EvnPLDispTeenInspection_IsRefusal ";
					$queryParams['EvnPLDispTeenInspection_IsRefusal'] = $data['EvnPLDispTeenInspection_IsRefusal'];
				}
				if (isset($data['EvnPLDispTeenInspection_isMobile'])) {
					$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_isMobile, 1) = :EvnPLDispTeenInspection_isMobile ";
					$queryParams['EvnPLDispTeenInspection_isMobile'] = $data['EvnPLDispTeenInspection_isMobile'];
				}
				if (isset($data['EvnPLDispTeenInspection_IsTwoStage'])) {
					$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_IsTwoStage, 1) = :EvnPLDispTeenInspection_IsTwoStage ";
					$queryParams['EvnPLDispTeenInspection_IsTwoStage'] = $data['EvnPLDispTeenInspection_IsTwoStage'];
				}
				if (isset($data['AgeGroupDisp_id'])) {
					$filter .= " and isnull([EPLDTI].AgeGroupDisp_id, 0) = :AgeGroupDisp_id ";
					$queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
				}
				if (isset($data['DispClass_id'])) {
					$filter .= " and isnull([EPLDTI].DispClass_id, 6) = :DispClass_id ";
					$queryParams['DispClass_id'] = $data['DispClass_id'];
				}
				if (isset($data['HealthGroupType_id'])) {
					$filter .= " and AH.HealthGroupType_id = :HealthGroupType_id ";
					$queryParams['HealthGroupType_id'] = $data['HealthGroupType_id'];
				}
				if (isset($data['HealthGroupType_oid'])) {
					$filter .= " and AH.HealthGroupType_oid = :HealthGroupType_oid ";
					$queryParams['HealthGroupType_oid'] = $data['HealthGroupType_oid'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispTeenInspection] [EPLDTI] with (nolock) on EPLDTI.Server_id = PS.Server_id and EPLDTI.PersonEvn_id = PS.PersonEvn_id and [EPLDTI].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispTeenInspection] [EPLDTI] with (nolock) on [PS].[Person_id] = [EPLDTI].[Person_id] and [EPLDTI].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}
				if (isset($data['EvnPLDisp_UslugaComplex']) && $data['EvnPLDisp_UslugaComplex'] > 0) {
					$filter .= "
						and exists (
							select top 1 UslugaComplex_id
							from v_EvnUslugaDispDop with (nolock)
							where EvnUslugaDispDop_didDate is not null
								and UslugaComplex_id = :EvnPLDisp_UslugaComplex
								and EvnUslugaDispDop_rid = EPLDTI.EvnPLDispTeenInspection_id
						)
					";
					$queryParams['EvnPLDisp_UslugaComplex'] = $data['EvnPLDisp_UslugaComplex'];
				}
				if (!empty($data['Disp_MedStaffFact_id']) || !empty($data['Disp_LpuSection_id']) || !empty($data['Disp_LpuBuilding_id'])) {
					$disp_msf = "";
					$disp_msf2 = "";
					$disp_ls = "";
					$disp_ls2 = "";
					$disp_b = "";
					$disp_b2 = "";
					$join1 = "";
					$join2 = "";
					if (!empty($data['Disp_MedStaffFact_id'])) {
						$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
						$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
						$queryParams['MedStaffFact_id'] = $data['Disp_MedStaffFact_id'];
					} else {
						if (!empty($data['Disp_LpuSection_id'])) {
							$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
							$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
							$queryParams['LpuSection_id'] = $data['Disp_LpuSection_id'];
						}
						if (!empty($data['Disp_LpuBuilding_id'])) {
							$join1 = "
								left join v_LpuSection ls1 with (nolock) on ls1.LpuSection_id = msf1.LpuSection_id
							";
							$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
							$join2 = "
								left join v_LpuSection ls3 with (nolock) on ls3.LpuSection_id = msf2.LpuSection_uid
							";
							$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
							$queryParams['LpuBuilding_id'] = $data['Disp_LpuBuilding_id'];
						}
					}
					$filter .= " 
					and (exists (
						select top 1 msf1.EvnVizitDispDop_id 
						from v_EvnVizitDispDop msf1 with (nolock) 
						{$join1}
						where msf1.EvnVizitDispDop_pid = EPLDTI.EvnPLDispTeenInspection_id
						{$disp_b}
						{$disp_msf}
						{$disp_ls}
					) or exists (
						select top 1 msf2.EvnUslugaDispDop_id 
						from v_EvnUslugaDispDop msf2 with (nolock)
						{$join2} 
						where msf2.EvnUslugaDispDop_pid = EPLDTI.EvnPLDispTeenInspection_id
						{$disp_b2}
						{$disp_msf2}
						{$disp_ls2}
					))";
				}
				$filter .= ' and dbo.Age(PS.Person_BirthDay, @getDT) <= 18';
				$query .= "
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDTI.EvnPLDispTeenInspection_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDTI].[EvnPLDispTeenInspection_IsFinish],1)
					left join [YesNo] [IsTwoStage] with (nolock) on [IsTwoStage].[YesNo_id] = ISNULL([EPLDTI].[EvnPLDispTeenInspection_IsTwoStage],1)
					left join [v_AssessmentHealth] AH with (nolock) on [AH].[EvnPLDisp_id] = [EPLDTI].[EvnPLDispTeenInspection_id]
					left join v_HealthGroupType HGT with (nolock) on HGT.HealthGroupType_id = AH.HealthGroupType_id
					left join [v_HealthKind] [HK] with (nolock) on [HK].[HealthKind_id] = [AH].[HealthKind_id]
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id
					left join v_PersonDispOrp PDORP (nolock) on PDORP.PersonDispOrp_id = EPLDTI.PersonDispOrp_id
					left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = ISNULL(EPLDTI.AgeGroupDisp_id, PDORP.AgeGroupDisp_id)
				";
				if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
					if (!empty($data['UslugaComplex_id'])) {
						$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
						$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
					}
					$query .= "
						outer apply(
							select top 1
								UslugaComplex_id
							from
								v_EvnUslugaDispDop (nolock)
							where
								EvnUslugaDispDop_IsVizitCode = 2
								and EvnUslugaDispDop_pid = EPLDTI.EvnPLDispTeenInspection_id
						) euddvizit
						left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
					";
				}

				break;


			case 'EvnPLDispDopStream':
				$filter .= "
					and [EPLDD].EvnPLDispDop_updDT >= :EvnPLDispDop_date_time and [EPLDD].pmUser_updID = :pmUser_id 
				";
				$queryParams['EvnPLDispDop_date_time'] = $data['EvnPLDispDopStream_begDate'] . " " . $data['EvnPLDispDopStream_begTime'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on EPLDD.Server_id = PS.Server_id and EPLDD.PersonEvn_id = PS.PersonEvn_id and [EPLDD].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish]  with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				";
				/*
				  $query .= "
				  inner join [v_EvnPLDispDop] [EPLDD] on [PS].[Person_id] = [EPLDD].[Person_id] and Lpu_id = :Lpu_id
				  left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				  ";
				 */
				break;

			case 'EvnPLDispTeen14Stream':
				$filter .= "
					and [EPLDT14].EvnPLDispTeen14_updDT >= :EvnPLDispTeen14_date_time and [EPLDT14].pmUser_updID = :pmUser_id 
				";
				$queryParams['EvnPLDispTeen14_date_time'] = $data['EvnPLDispTeen14Stream_begDate'] . " " . $data['EvnPLDispTeen14Stream_begTime'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on EPLDT14.Server_id = PS.Server_id and EPLDT14.PersonEvn_id = PS.PersonEvn_id and [EPLDT14].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on [PS].[Person_id] = [EPLDT14].[Person_id] and [EPLDT14].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish]  with (nolock) on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				";
				/*
				  $query .= "
				  inner join [v_EvnPLDispTeen14] [EPLDT14] on [PS].[Person_id] = [EPLDT14].[Person_id] and Lpu_id = :Lpu_id
				  left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				  ";
				 */
				break;

			case 'EvnPLDispOrpStream':
				$filter .= "
					and [EPLDO].EvnPLDispOrp_updDT >= :EvnPLDispOrp_date_time and [EPLDO].pmUser_updID = :pmUser_id 
				";
				$queryParams['EvnPLDispOrp_date_time'] = $data['EvnPLDispOrpStream_begDate'] . " " . $data['EvnPLDispOrpStream_begTime'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				} else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id " . $this->getLpuIdFilter($data) . "
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish]  with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
					left join [YesNo] [IsTwoStage]  with (nolock) on [IsTwoStage].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsTwoStage]
				";
				break;

			case 'EvnPLDispMigrant':

				// результат
				if (isset($data['ResultDispMigrant_id'])) {
					$filter .= " and [EPLDM].ResultDispMigrant_id = :ResultDispMigrant_id ";
					$queryParams['ResultDispMigrant_id'] = $data['ResultDispMigrant_id'];
				}
				// вич
				if (isset($data['EvnPLDispMigran_SertHIVNumber'])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertHIVNumber like :EvnPLDispMigran_SertHIVNumber ";
					$queryParams['EvnPLDispMigran_SertHIVNumber'] = $data['EvnPLDispMigran_SertHIVNumber'] . '%';
				}
				if (isset($data['EvnPLDispMigran_SertHIVDate'])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertHIVDate = cast(:EvnPLDispMigran_SertHIVDate as datetime) ";
					$queryParams['EvnPLDispMigran_SertHIVDate'] = $data['EvnPLDispMigran_SertHIVDate'];
				}
				if (isset($data['EvnPLDispMigran_SertHIVDateRange'][0])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertHIVDate >= cast(:EvnPLDispMigran_SertHIVDateRange_0 as datetime) ";
					$queryParams['EvnPLDispMigran_SertHIVDateRange_0'] = $data['EvnPLDispMigran_SertHIVDateRange'][0];
				}
				if (isset($data['EvnPLDispMigran_SertHIVDateRange'][1])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertHIVDate <= cast(:EvnPLDispMigran_SertHIVDateRange_1 as datetime) ";
					$queryParams['EvnPLDispMigran_SertHIVDateRange_1'] = $data['EvnPLDispMigran_SertHIVDateRange'][1];
				}
				// инфект
				if (isset($data['EvnPLDispMigran_SertInfectNumber'])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertInfectNumber like :EvnPLDispMigran_SertInfectNumber ";
					$queryParams['EvnPLDispMigran_SertInfectNumber'] = $data['EvnPLDispMigran_SertInfectNumber'] . '%';
				}
				if (isset($data['EvnPLDispMigran_SertInfectDate'])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertInfectDate = cast(:EvnPLDispMigran_SertInfectDate as datetime) ";
					$queryParams['EvnPLDispMigran_SertInfectDate'] = $data['EvnPLDispMigran_SertInfectDate'];
				}
				if (isset($data['EvnPLDispMigran_SertInfectDateRange'][0])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertInfectDate >= cast(:EvnPLDispMigran_SertInfectDateRange_0 as datetime) ";
					$queryParams['EvnPLDispMigran_SertInfectDateRange_0'] = $data['EvnPLDispMigran_SertInfectDateRange'][0];
				}
				if (isset($data['EvnPLDispMigran_SertInfectDateRange'][1])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertInfectDate <= cast(:EvnPLDispMigran_SertInfectDateRange_1 as datetime) ";
					$queryParams['EvnPLDispMigran_SertInfectDateRange_1'] = $data['EvnPLDispMigran_SertInfectDateRange'][1];
				}
				// нарко
				if (isset($data['EvnPLDispMigran_SertNarcoNumber'])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertNarcoNumber like :EvnPLDispMigran_SertNarcoNumber ";
					$queryParams['EvnPLDispMigran_SertNarcoNumber'] = $data['EvnPLDispMigran_SertNarcoNumber'] . '%';
				}
				if (isset($data['EvnPLDispMigran_SertNarcoDate'])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertNarcoDate = cast(:EvnPLDispMigran_SertNarcoDate as datetime) ";
					$queryParams['EvnPLDispMigran_SertNarcoDate'] = $data['EvnPLDispMigran_SertNarcoDate'];
				}
				if (isset($data['EvnPLDispMigran_SertNarcoDateRange'][0])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertNarcoDate >= cast(:EvnPLDispMigran_SertNarcoDateRange_0 as datetime) ";
					$queryParams['EvnPLDispMigran_SertNarcoDateRange_0'] = $data['EvnPLDispMigran_SertNarcoDateRange'][0];
				}
				if (isset($data['EvnPLDispMigran_SertNarcoDateRange'][1])) {
					$filter .= " and [EPLDM].EvnPLDispMigran_SertNarcoDate <= cast(:EvnPLDispMigran_SertNarcoDateRange_1 as datetime) ";
					$queryParams['EvnPLDispMigran_SertNarcoDateRange_1'] = $data['EvnPLDispMigran_SertNarcoDateRange'][1];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];
				$query .= " 
					inner join [v_EvnPLDispMigrant] [EPLDM] with (nolock) on [PS].[Person_id] = [EPLDM].[Person_id] and [EPLDM].Lpu_id " . $this->getLpuIdFilter($data) . "
					left join [v_Address] [UA] with (nolock) on [UA].[Address_id] = [PS].[UAddress_id]
					left join [v_Address] [PA] with (nolock) on [PA].[Address_id] = [PS].[PAddress_id]
					left join [v_ResultDispMigrant] [RDM] with (nolock) on [RDM].[ResultDispMigrant_id] = [EPLDM].[ResultDispMigrant_id]
					outer apply (
						select top 1 EVDD.EvnVizitDispDop_setDate
						from v_EvnVizitDispDop EVDD (nolock) 
						inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where EVDD.EvnVizitDispDop_pid = EPLDM.EvnPLDispMigrant_id and ST.SurveyType_Code = 152
					) as EVDD
				";
				break;

			case 'EvnPLDispDriver':

				// результат
				if (isset($data['ResultDispDriver_id'])) {
					$filter .= " and [EPLDD].ResultDispDriver_id = :ResultDispDriver_id ";
					$queryParams['ResultDispDriver_id'] = $data['ResultDispDriver_id'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];
				$query .= " 
					inner join [v_EvnPLDispDriver] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id " . $this->getLpuIdFilter($data) . "
					left join [v_Address] [UA] with (nolock) on [UA].[Address_id] = [PS].[UAddress_id]
					left join [v_Address] [PA] with (nolock) on [PA].[Address_id] = [PS].[PAddress_id]
					left join [v_ResultDispDriver] [RDD] with (nolock) on [RDD].[ResultDispDriver_id] = [EPLDD].[ResultDispDriver_id]
					outer apply (
						select top 1 EVDD.EvnVizitDispDop_setDate
						from v_EvnVizitDispDop EVDD (nolock) 
						inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where EVDD.EvnVizitDispDop_pid = EPLDD.EvnPLDispDriver_id and ST.SurveyType_Code = 152
					) as EVDD
				";
				break;

			// для выгрузки данных в dbf, используется контроллером Search.php, методом exportSearchResultsToDbf. Для подгрузки посещений талона, оставлена обработка фильтров.
			case 'EPLPerson':
			case 'EvnAgg':
			case 'EvnPL':
			case 'EvnUsluga':
			case 'EvnVizitPL':
				if ('EvnVizitPL' == $data['SearchFormType']) {
					$query .= "
						v_EvnVizitPL EVizitPL with (nolock)
						inner join Evn with (nolock) on Evn.Evn_id = EVizitPL.EvnVizitPL_pid
							and Evn.EvnClass_id = 3
							and Evn.Evn_deleted = 1
							and Evn.Lpu_id " . $this->getLpuIdFilter($data) . "
						outer apply (
							Select top 1 EvnPLBase.EvnPLBase_IsFinish, EvnPLBase.EvnPLBase_VizitCount from EvnPLBase with (nolock) where EvnPLBase.EvnPLBase_id = Evn.Evn_id
						) EvnPLBase
						inner join EvnPL EPL with (nolock) on EPL.EvnPL_id = Evn.Evn_id
					";
					$filter .= " and EVizitPL.Lpu_id " . $this->getLpuIdFilter($data) . " ";
				} else {
					$query .= "
						EvnClass with (nolock)
						inner join Evn with (nolock) on Evn.EvnClass_id = EvnClass.EvnClass_id
							and EvnClass.EvnClass_id = 3
							and Evn.Evn_deleted = 1
							and Evn.Lpu_id " . $this->getLpuIdFilter($data) . "						
						outer apply (
							Select top 1 EvnPLBase.EvnPLBase_IsFinish, EvnPLBase.EvnPLBase_VizitCount from EvnPLBase with (nolock) where EvnPLBase.EvnPLBase_id = Evn.Evn_id
						) EvnPLBase
						inner join EvnPL EPL with (nolock) on EPL.EvnPL_id = Evn.Evn_id
						[EvnVizitTmp]
					";
				}
				if ($dbf !== true) {
					if ('EvnVizitPL' == $data['SearchFormType']) {
						$query .= "
							left join v_Diag as evpldiag with (nolock) on evpldiag.Diag_id=EVizitPL.Diag_id
							left join v_LpuSection as evplls with (nolock) on evplls.LpuSection_id=EVizitPL.LpuSection_id
							left join v_MedPersonal as evplmp with (nolock) on evplmp.MedPersonal_id=EVizitPL.MedPersonal_id and evplmp.Lpu_id = Evn.Lpu_id
							left join v_PayType as evplpt with (nolock) on evplpt.PayType_id=EVizitPL.PayType_id
							left join v_VizitType as evplvt with (nolock) on evplvt.VizitType_id=EVizitPL.VizitType_id
							left join v_ServiceType as evplst with (nolock) on evplst.ServiceType_id=EVizitPL.ServiceType_id
							left join v_HealthKind as evplhk with (nolock) on evplhk.HealthKind_id=EVizitPL.HealthKind_id
							left join v_VizitType VT with (nolock) on VT.VizitType_id = EVizitPL.VizitType_id
						";
						if (in_array($data['session']['region']['nick'], array('ufa'))) {
							$query .= "
								outer apply (
									select top 1
										t1.EvnUslugaCommon_id,
										t1.Usluga_id,
										t1.UslugaComplex_id as UslugaComplex_uid,
										t3.UslugaComplex_Code
									from
										v_EvnUslugaCommon t1 with (nolock)
										left join v_Usluga t2 with (nolock) on t2.Usluga_id = t1.Usluga_id
										left join v_UslugaComplex t3 with (nolock) on t3.UslugaComplex_id = t1.UslugaComplex_id
										left join v_UslugaCategory t4 with (nolock) on t4.UslugaCategory_id = isnull(t2.UslugaCategory_id, t3.UslugaCategory_id)
									where
										t1.EvnUslugaCommon_pid = EVizitPL.EvnVizitPL_id
										and t4.UslugaCategory_SysNick in ('tfoms', 'lpusection')
									order by
										t1.EvnUslugaCommon_setDT desc
								) EU
							";
						}

						if (in_array($data['PersonPeriodicType_id'], array(2))) {
							$query .= "
								inner join v_Person_all PS with (nolock) on EVizitPL.PersonEvn_id = PS.PersonEvn_id and EVizitPL.Server_id = PS.Server_id
							";
						} else {
							if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
								$query .= "
									inner join v_PersonState PS with (nolock) on EVizitPL.Person_id = PS.Person_id
								";
							} else {
								$query .= "
									inner join v_PersonState_all PS with (nolock) on EVizitPL.Person_id = PS.Person_id
								";
							}
						}

						$query .= "
							left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
						";
						if (allowPersonEncrypHIV($data['session'])) {
							$query .= "
								left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
							";
						}
					} else if ('EvnPL' == $data['SearchFormType']) {
						$query .= "
							outer apply (
								Select top 1 
									EVPL.EvnVizitPL_id, 
									EVPL.HealthKind_id, 
									EVPL.VizitType_id, 
									EVPL.MedPersonal_id, 
									EVPL.Lpu_id,
									EVPL.Diag_id,
									EVPL.LpuSection_id,
									EVPL.DeseaseType_id,
									EVPL.MedPersonal_sid,
									EVPL.EvnVizitPL_isPaid,
									EVPL.ServiceType_id,
									EVPL.TreatmentClass_id,
									EVPL.EvnVizitPL_setDT,
									EVPL.UslugaComplex_id,
									EVPL.EvnVizitPL_IsInReg,
									EVPL.VizitClass_id
								from v_EvnVizitPL EVPL with (nolock) 
								where EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_IndexMinusOne
							) EVPL
							left join v_Diag EVPLD with (nolock) on EVPLD.Diag_id = EPL.Diag_id
							left join v_YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EvnPLBase.EvnPLBase_IsFinish
							left join v_HealthKind as HK with (nolock) on HK.HealthKind_id=EVPL.HealthKind_id
							left join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
							left join v_Lpu dlpu with (nolock) on dlpu.Lpu_id = EPL.Lpu_did		                
							outer apply (
								select top 1
									MP.Person_Fio
								from
									v_MedPersonal MP with (nolock)
								where
									MP.MedPersonal_id = EVPL.MedPersonal_id
									and MP.Lpu_id = EVPL.Lpu_id
							) MP
						";

						if (in_array($data['PersonPeriodicType_id'], array(2))) {
							$query .= "
								inner join v_Person_all PS with (nolock) on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id
							";
						} else {
							if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
								$query .= "
									inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
								";
							} else {
								$query .= "
									inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
								";
							}
						}

						$query .= "
							left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
						";
						if (allowPersonEncrypHIV($data['session'])) {
							$query .= "
							left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id";
						}

						if ($this->regionNick == 'kz') {
							$query .= " left join r101.AISResponse air (nolock) on air.Evn_id = EPL.EvnPL_id and air.AISFormLoad_id = 1 ";
							$query .= " left join r101.AISResponse air9 (nolock) on air9.Evn_id = EPL.EvnPL_id and air9.AISFormLoad_id = 2 ";
							$query .= " outer apply (
								select top 1 eu.UslugaComplex_id
								from v_EvnUsluga_all eu (nolock)
								inner join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = eu.UslugaComplex_id
								where eu.EvnUsluga_rid = epl.EvnPL_id and ucl.AISFormLoad_id = 1
							) as euais ";
							$query .= " outer apply (
								select top 1 eu.UslugaComplex_id
								from v_EvnUsluga_all eu (nolock)
								inner join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = eu.UslugaComplex_id
								where eu.EvnUsluga_rid = epl.EvnPL_id and ucl.AISFormLoad_id = 1
							) as euais9 ";
							if ($data['toAis25'] == 2) {
								$filter .= " and air.AISResponse_id is not null";
							} elseif ($data['toAis25'] == 1) {
								$filter .= " and air.AISResponse_id is null and euais.UslugaComplex_id is not null";
							}
							if ($data['toAis259'] == 2) {
								$filter .= " and air9.AISResponse_id is not null";
							} elseif ($data['toAis259'] == 1) {
								$filter .= " and air9.AISResponse_id is null and euais9.UslugaComplex_id is not null";
							}
						}
					} else {
						$query .= "
							outer apply (
								Select top 1 
									EVPL.EvnVizitPL_id, 
									EVPL.HealthKind_id, 
									EVPL.VizitType_id, 
									EVPL.MedPersonal_id, 
									EVPL.Lpu_id,
									EVPL.Diag_id,
									EVPL.LpuSection_id,
									EVPL.DeseaseType_id,
									EVPL.MedPersonal_sid,
									EVPL.EvnVizitPL_isPaid,
									EVPL.ServiceType_id,
									EVPL.EvnVizitPL_setDT,
									EVPL.EvnVizitPL_IsInReg,
									EVPL.VizitClass_id
								from v_EvnVizitPL EVPL with (nolock) 
								where EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_IndexMinusOne
							) EVPL
							left join v_Diag EVPLD with (nolock) on EVPLD.Diag_id = EPL.Diag_id
							left join v_YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EvnPLBase.EvnPLBase_IsFinish
							left join v_HealthKind as HK with (nolock) on HK.HealthKind_id = EVPL.HealthKind_id
							left join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
							outer apply (
								select top 1
									MP.Person_Fio
								from
									v_MedPersonal MP with (nolock)
								where
									MP.MedPersonal_id = EVPL.MedPersonal_id
									and MP.Lpu_id = EVPL.Lpu_id
							) MP
						";
					}
					//$query .= " left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPL.EvnPL_id ";
					$query .= " OUTER APPLY (
									SELECT TOP 1
										EvnCostPrint_setDT,
										EvnCostPrint_IsNoPrint
									FROM v_EvnCostPrint ecp (NOLOCK)
									where ecp.Evn_id = EPL.EvnPL_id
								) ecp ";
				}
				// DBF
				else {
					if(isset($data['TreatmentClass_id'])) {
						$query .= "
							outer apply (
								Select top 1 
									EVPL.EvnVizitPL_id, 
									EVPL.HealthKind_id, 
									EVPL.VizitType_id, 
									EVPL.MedPersonal_id, 
									EVPL.Lpu_id,
									EVPL.Diag_id,
									EVPL.LpuSection_id,
									EVPL.DeseaseType_id,
									EVPL.MedPersonal_sid,
									EVPL.EvnVizitPL_isPaid,
									EVPL.ServiceType_id,
									EVPL.TreatmentClass_id,
									EVPL.EvnVizitPL_setDT,
									EVPL.UslugaComplex_id,
									EVPL.EvnVizitPL_IsInReg,
									EVPL.VizitClass_id
								from v_EvnVizitPL EVPL with (nolock) 
								where EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_IndexMinusOne
							) EVPL
						";
					}
					switch ($data['SearchFormType']) {
						case 'EPLPerson':
							$query .= " inner join v_EvnPL EPL2 (nolock) on EPL2.EvnPL_id = EPL.EvnPL_id ";
							if ($PL_prefix == 'PS2') { //требуется использование дополнительной таблицы
								if ($data['epl_date_type'] == 2) {
									$query .= "
										inner join v_Person_all PS2 with (nolock) on PS2.Server_id = EPL2.Server_id and PS2.PersonEvn_id = EPL2.PersonEvn_id
									";
								} else {
									$query .= "
										inner join v_PersonState PS2 with (nolock) on PS2.PersonEvn_id = Evn.PersonEvn_id/*PS2.Person_id = EPL.Person_id*/
									";
								}
							}
							$query .= "
								inner join v_PersonState PS (nolock) on PS.Person_id = EPL2.Person_id
								left join Sex with (nolock) on Sex.Sex_id = {$PL_prefix}.Sex_id
								left join SocStatus Soc with (nolock) on Soc.SocStatus_id = {$PL_prefix}.SocStatus_id
								left join PersonChild PCh with (nolock) on PCh.Person_id = {$PL_prefix}.Person_id
								left join YesNo IsInv with (nolock) on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
								left join Diag InvD with (nolock) on InvD.Diag_id = PCh.Diag_id
								left join Polis Pol with (nolock) on Pol.Polis_id = {$PL_prefix}.Polis_id
								left join OMSSprTerr OMSST with (nolock) on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
								left join PolisType PolTp with (nolock) on PolTp.PolisType_id = Pol.PolisType_id
								left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Pol.OrgSmo_id
								left join v_Org OSO with (nolock) on OSO.Org_id = OS.Org_id
								left join v_Address_all UA with (nolock) on UA.Address_id = {$PL_prefix}.UAddress_id 
								left join v_Address_all PA with (nolock) on PA.Address_id = {$PL_prefix}.PAddress_id
								left join Document Doc with (nolock) on Doc.Document_id = {$PL_prefix}.Document_id 
								left join DocumentType DocTp with (nolock) on DocTp.DocumentType_id = Doc.DocumentType_id
								left join v_OrgDep OrgD with (nolock) on OrgD.OrgDep_id = Doc.OrgDep_id
							";
							break;
						case 'EvnAgg':
							$query .= "
								inner join v_EvnUsluga EvnUsluga with (nolock) on EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id and EvnUsluga.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_EvnAgg EvnAgg with (nolock) on EvnAgg.EvnAgg_pid = EvnUsluga.EvnUsluga_id and EvnAgg.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join AggType as dbfat with (nolock) on dbfat.AggType_id = EvnAgg.AggType_id
								left join AggWhen as dbfaw with (nolock) on dbfaw.AggWhen_id = EvnAgg.AggWhen_id
							";
							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									inner join v_Person_all PS with (nolock) on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								}
							}
							break;
						case 'EvnPL':
							$query .= "
								left join v_Diag as dbfdiag with (nolock) on dbfdiag.Diag_id = EPL.Diag_id
								left join v_Lpu as dbflpu with (nolock) on dbflpu.Lpu_id = Evn.Lpu_id
								left join ResultClass dbfrc with (nolock) on dbfrc.ResultClass_id = EPL.ResultClass_id
								left join DeseaseType dbfdt with (nolock) on dbfdt.DeseaseType_id = EPL.DeseaseType_id
								left join PrehospDirect dbfpd with (nolock) on dbfpd.PrehospDirect_id = EPL.PrehospDirect_id
								left join v_Lpu dbfprehosplpu with (nolock) on dbfprehosplpu.Lpu_id = EPL.Lpu_did
								left join LpuSection dbflsd with (nolock) on dbflsd.LpuSection_id = EPL.LpuSection_did
								left join YesNo dbfift with (nolock) on dbfift.YesNo_id = EPL.EvnPL_IsFirstTime
								left join DirectClass dbfdc with (nolock) on dbfdc.DirectClass_id = EPL.DirectClass_id
								
								left join v_Lpu dbflpudir with (nolock) on dbflpudir.Lpu_id = EPL.Lpu_oid
								left join LpuSection dbflsdir with (nolock) on dbflsdir.LpuSection_id = EPL.LpuSection_oid
								outer apply (
									select top 1
										YesNo.YesNo_Code as PersonChild_IsInvalid_Code,
										PersonSprTerrDop.PersonSprTerrDop_Code as PermRegion_Code
									from PersonChild with (nolock)
										left join YesNo with (nolock) on YesNo.YesNo_id = PersonChild.PersonChild_IsInvalid
										left join PersonSprTerrDop with (nolock) on PersonSprTerrDop.PersonSprTerrDop_id = PersonChild.PersonSprTerrDop_id
									where PersonChild.Person_id = Evn.Person_id
									order by PersonChild.PersonChild_insDT desc
								) dbfinv
							";

							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									inner join v_Person_all PS with (nolock) on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id
									left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
									left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
									left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
									left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
									left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
									left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
										left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
										left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
										left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
										left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
										left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
										left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
										left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
										left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
										left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
										left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
										left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
										left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
									";
								}
							}
							break;

						case 'EvnUsluga':
							$query .= "
								inner join v_EvnUsluga EvnUsluga with (nolock) on EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id and EvnUsluga.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_PayType as dbfpt with (nolock) on dbfpt.PayType_id = EvnUsluga.PayType_id
								left join v_UslugaComplex as dbfusluga with (nolock) on dbfusluga.UslugaComplex_id = EvnUsluga.UslugaComplex_id
								left join v_UslugaPlace as dbfup with (nolock) on dbfup.UslugaPlace_id = EvnUsluga.UslugaPlace_id
								left join v_MedPersonal dbfmp with (nolock) on dbfmp.MedPersonal_id = EvnUsluga.MedPersonal_id
									and dbfmp.Lpu_id = Evn.Lpu_id
							";
							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									inner join v_Person_all PS with (nolock) on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								}
							}
							break;
						case 'EvnVizitPL':
							$query .= "
								left join v_Diag as dbfdiag with (nolock) on dbfdiag.Diag_id=EVizitPL.Diag_id
								left join LpuSection as dbfls with (nolock) on dbfls.LpuSection_id=EVizitPL.LpuSection_id
								left join v_MedPersonal as dbfmp with (nolock) on dbfmp.MedPersonal_id=EVizitPL.MedPersonal_id and dbfmp.Lpu_id = Evn.Lpu_id
								left join PayType as dbfpt with (nolock) on dbfpt.PayType_id=EVizitPL.PayType_id
								left join VizitClass as dbfvc with (nolock) on dbfvc.VizitClass_id=EVizitPL.VizitClass_id
								left join VizitType as dbfvt with (nolock) on dbfvt.VizitType_id=EVizitPL.VizitType_id
								left join DeseaseType as dbfdt with (nolock) on dbfdt.DeseaseType_id=EVizitPL.DeseaseType_id
								left join ServiceType as dbfst with (nolock) on dbfst.ServiceType_id=EVizitPL.ServiceType_id
								left join ProfGoal as dbfpg with (nolock) on dbfpg.ProfGoal_id=EVizitPL.ProfGoal_id
							";
							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									inner join v_Person_all PS with (nolock) on EVizitPL.PersonEvn_id = PS.PersonEvn_id and EVizitPL.Server_id = PS.Server_id
									left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										inner join v_PersonState PS with (nolock) on EVizitPL.Person_id = PS.Person_id
										left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on EVizitPL.Person_id = PS.Person_id
										left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
									";
								}
							}
							break;
					}
					if (isset($data['and_eplperson']) && $data['and_eplperson']) { //при комбинировании с таблицой персон добавляем дополнительные поля
						if ($PL_prefix == 'PS2') { //требуется использование дополнительной таблицы
							$query .= " inner join v_EvnPL EPL2 (nolock) on EPL2.EvnPL_id = EPL.EvnPL_id ";
							if ($data['epl_date_type'] == 2) {
								$query .= "
									left join v_Person_all PS2 with (nolock) on PS2.Server_id = EPL2.Server_id and PS2.PersonEvn_id = EPL2.PersonEvn_id
								";
							} else {
								$query .= "
									left join v_PersonState PS2 with (nolock) on PS2.Person_id = EPL2.Person_id
								";
							}
						}
						$query .= "
							left join Sex PrsSex with (nolock) on PrsSex.Sex_id = {$PL_prefix}.Sex_id
							left join SocStatus PrsSoc with (nolock) on PrsSoc.SocStatus_id = {$PL_prefix}.SocStatus_id
							left join PersonChild PrsPCh with (nolock) on PrsPCh.Person_id = {$PL_prefix}.Person_id
							left join YesNo PrsIsInv with (nolock) on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
							left join Diag PrsInvD with (nolock) on PrsInvD.Diag_id = PrsPCh.Diag_id
							left join Polis PrsPol with (nolock) on PrsPol.Polis_id = {$PL_prefix}.Polis_id
							left join OMSSprTerr PrsOMSST with (nolock) on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
							left join PolisType PrsPolTp with (nolock) on PrsPolTp.PolisType_id = PrsPol.PolisType_id
							left join v_OrgSmo PrsOS with (nolock) on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
							left join v_Org PrsOSO with (nolock) on PrsOSO.Org_id = PrsOS.Org_id
							left join v_Address_all PrsUA with (nolock) on PrsUA.Address_id = {$PL_prefix}.UAddress_id 
							left join v_Address_all PrsPA with (nolock) on PrsPA.Address_id = {$PL_prefix}.PAddress_id
							left join Document PrsDoc with (nolock) on PrsDoc.Document_id = {$PL_prefix}.Document_id 
							left join DocumentType PrsDocTp with (nolock) on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
							left join v_OrgDep PrsOrgD with (nolock) on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
						";
					}
				}// DBF

				// #107998 Фильтр по признаку включения реестр
				switch ($data['SearchFormType']) {
					case 'EvnPL':
						if(isset($data['EvnPL_InRegistry'])){
							if(in_array($this->regionNick, array('ekb'))){
								if($data['EvnPL_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП, не вошедшие в реестр');
									$filter .= " and (EPL.EvnPL_IsInReg = 1 or EPL.EvnPL_IsInReg is null)";
								}elseif($data['EvnPL_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП, вошедшие в реестр');
									$filter .= " and EPL.EvnPL_IsInReg = 2";
								}
							}
							elseif(in_array($this->regionNick, array('penza'))){
								if($data['EvnPL_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП, для движения которых не установлен признак вхожденияе в реестр');
									$filter .= " AND NOT EXISTS (SELECT TOP 1 EvnVizitPL_IsInReg FROM v_EvnVizitPL EVPL2 WITH (nolock) WHERE EVPL2.EvnVizitPL_rid = EPL.EvnPL_id AND EVPL2.EvnVizitPL_IsInReg = 2)";// ни одно из посещений в реестре
								}elseif($data['EvnPL_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП, для движения которых установлен признак вхожденияе в реестр');
									$filter .= " AND EXISTS (SELECT TOP 1 EvnVizitPL_IsInReg FROM v_EvnVizitPL EVPL2 WITH (nolock) WHERE EVPL2.EvnVizitPL_rid = EPL.EvnPL_id AND EVPL2.EvnVizitPL_IsInReg = 2)";// одно из посещений в реестре
								}
							}
						}
						break;
					case 'EvnVizitPL':
						if(isset($data['EvnPL_InRegistry'])){
							if(in_array($this->regionNick, array('ekb'))){
								if($data['EvnPL_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только посещения, не вошедшие в реестр');
									$filter .= " and (EPL.EvnPL_IsInReg = 1 or EPL.EvnPL_IsInReg is null)";
								}elseif($data['EvnPL_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только посещения, вошедшие в реестр');
									$filter .= " and EPL.EvnPL_IsInReg = 2";
								}
							}
							elseif(in_array($this->regionNick, array('penza'))){
								if($data['EvnPL_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только посещения, для движения которых не установлен признак вхожденияе в реестр');
									$filter .= " and (EVizitPL.EvnVizitPL_IsInReg = 1 or EVizitPL.EvnVizitPL_IsInReg is null)";
								}elseif($data['EvnPL_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только посещения, в движении которы установлен признак вхождения в реестр');
									$filter .= " and EVizitPL.EvnVizitPL_IsInReg = 2";
								}
							}
						}
						break;
				}
				//#155391 фильтр по фед.сервису
				$query .= "
					outer apply (
						select top 1 ServiceEvnStatus_id
						from v_ServiceEvnHist with (nolock)
						where Evn_id = EPL.EvnPL_id
							and ServiceEvnList_id = 1
						order by ServiceEvnHist_id desc
					) SEH1
					left join v_ServiceEvnStatus SES1 with(nolock) on SES1.ServiceEvnStatus_id = SEH1.ServiceEvnStatus_id
				";

				// // #195250 поиск по гражданству
				if( $data['PersonPeriodicType_id'] == 2 && !empty($data['Person_citizen']) && ($data['Person_citizen']==3) ) {
					//добавляем таблицу для поиска по гражданству, чтобы ссылаться на KLCountry_id из NationalityStatus, а не PS
					$query .= " left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = ps.NationalityStatus_id";
				}

				// Диагноз и услуги
				if (!empty($data['UslugaCategory_id']) || !empty($data['UslugaComplex_Code_From']) || !empty($data['UslugaComplex_Code_To'])) {
					// по задаче #10719
					if ('EvnVizitPL' == $data['SearchFormType']) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVizitPL.EvnVizitPL_id";
					} else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPL.EvnPL_id";
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							 " . $filter_evnvizit . "
							 and EU.Lpu_id " . $this->getLpuIdFilter($data) . "
							 and EU.EvnClass_SysNick in ('EvnUslugaCommon','EvnUslugaOper','EvnUslugaPar')
					";

					if (!empty($data['UslugaCategory_id'])) {
						$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id ";
						$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
					}

					if (!empty($data['UslugaComplex_Code_From'])) {
						$filter .= " and uc.UslugaComplex_Code >= :UslugaComplex_Code_From ";
						$queryParams['UslugaComplex_Code_From'] = $data['UslugaComplex_Code_From'];
					}

					if (!empty($data['UslugaComplex_Code_To'])) {
						$filter .= " and uc.UslugaComplex_Code <= :UslugaComplex_Code_To ";
						$queryParams['UslugaComplex_Code_To'] = $data['UslugaComplex_Code_To'];
					}

					$filter .= ")";
				}

				if (!empty($data['UslugaComplex_uid']) || !empty($data['UslugaComplex_Code'])) {
					if ('EvnVizitPL' == $data['SearchFormType']) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVizitPL.EvnVizitPL_id";
					} else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPL.EvnPL_id";
					}

					if (!empty($data['UslugaComplex_uid'])) {
						$filter_evnvizit .= " and uc.UslugaComplex_id = :UslugaComplex_uid";
						$queryParams['UslugaComplex_uid'] = $data['UslugaComplex_uid'];
					}

					if (!empty($data['UslugaComplex_Code'])) {
						$searchMode = 0;

						if (substr($data['UslugaComplex_Code'], 0, 1) == '%') {
							$searchMode += 1;
						}

						if (substr($data['UslugaComplex_Code'], -1) == '%') {
							$searchMode += 2;
						}

						$data['UslugaComplex_Code'] = str_replace('%', '', $data['UslugaComplex_Code']);

						switch ($searchMode) {
							case 0:
								$filter_evnvizit .= " and uc.UslugaComplex_Code = :UslugaComplexCode";
								$queryParams['UslugaComplexCode'] = $data['UslugaComplex_Code'];
								break;

							case 1:
								$filter_evnvizit .= " and uc.UslugaComplex_Code like :UslugaComplexCode";
								$queryParams['UslugaComplexCode'] = '%' . $data['UslugaComplex_Code'];
								break;

							case 2:
								$filter_evnvizit .= " and uc.UslugaComplex_Code like :UslugaComplexCode";
								$queryParams['UslugaComplexCode'] = $data['UslugaComplex_Code'] . '%';
								break;

							case 3:
								$filter_evnvizit .= " and uc.UslugaComplex_Code like :UslugaComplexCode";
								$queryParams['UslugaComplexCode'] = '%' . $data['UslugaComplex_Code'] . '%';
								break;
						}
					}

					$filter_usluga_category = '';
					switch ($this->getRegionNick()) {
						case 'perm':
							if (!empty($data['UslugaComplex_uid'])) {
								switch ($data['SearchFormType']) {
									case 'EvnPL':
										$filter .= " and EVPL.UslugaComplex_id = :UslugaComplex_uid";
										break;
									case 'EvnVizitPL':
										$filter .= " and EVizitPL.UslugaComplex_id = :UslugaComplex_uid";
										break;
								}
								$queryParams['UslugaComplex_uid'] = $data['UslugaComplex_uid'];
							}
							break;
						default:

							//Поиск по умолчанию оставляем как было
							$filter .= " and exists (
								select top 1
									uc.UslugaComplex_id
								from v_EvnUsluga EU with (nolock)
									inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
									inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
								where
									" . $filter_evnvizit . "
									and EU.EvnClass_SysNick in ('EvnUslugaCommon','EvnUslugaOper','EvnUslugaPar')
									{$filter_usluga_category}
								)
							";
							$filter_usluga_category = "and ucat.UslugaCategory_SysNick = 'lpusection'";
							break;
					}
				}

				// Посещение
				if (isset($data['EvnPL_IsUnlaw'])) {
					$filter .= " and EPL.EvnPL_IsUnlaw = :EvnPL_IsUnlaw";
					$queryParams['EvnPL_IsUnlaw'] = $data['EvnPL_IsUnlaw'];
				}

				// Посещение
				if (isset($data['EvnPL_IsUnport'])) {
					$filter .= " and EPL.EvnPL_IsUnport = :EvnPL_IsUnport";
					$queryParams['EvnPL_IsUnport'] = $data['EvnPL_IsUnport'];
				}

				// Посещение
				if (isset($data['EvnPL_NumCard'])) {
					$filter .= " and EPL.EvnPL_NumCard = :EvnPL_NumCard";
					$queryParams['EvnPL_NumCard'] = $data['EvnPL_NumCard'];
				}

				// Посещение
				if (isset($data['EvnPL_setDate_Range'][0])) {
					$filter .= " and cast(Evn.Evn_setDT as date) >= :EvnPL_setDate_Range_0";
					$queryParams['EvnPL_setDate_Range_0'] = $data['EvnPL_setDate_Range'][0];
				}

				// Посещение
				if (isset($data['EvnPL_setDate_Range'][1])) {
					$filter .= " and cast(Evn.Evn_setDT as date) <= :EvnPL_setDate_Range_1";
					$queryParams['EvnPL_setDate_Range_1'] = $data['EvnPL_setDate_Range'][1];
				}

				if (!empty($data['Diag_IsNotSet'])) {
					if ('EvnVizitPL' == $data['SearchFormType']) {
						$filter .= " and EVizitPL.Diag_id is null";
					} else {
						$filter .= " and EPL.Diag_id is null";
					}
				}

				// Посещение
				if (isset($data['EvnPL_disDate_Range'][0])) {
					$filter .= " and cast(Evn.Evn_disDT as date) >= :EvnPL_disDate_Range_0";
					$queryParams['EvnPL_disDate_Range_0'] = $data['EvnPL_disDate_Range'][0];
				}

				// Посещение
				if (isset($data['EvnPL_disDate_Range'][1])) {
					$filter .= " and cast(Evn.Evn_disDT as date) <= :EvnPL_disDate_Range_1";
					$queryParams['EvnPL_disDate_Range_1'] = $data['EvnPL_disDate_Range'][1];
				}

				// Посещение
				if (isset($data['TreatmentClass_id'])) {
					if ($data['SearchFormType'] == 'EvnVizitPL') {
						$filter .= " and EVizitPL.TreatmentClass_id = :TreatmentClass_id";
					} else {
						$filter .= " and EVPL.TreatmentClass_id = :TreatmentClass_id";
					}

					$queryParams['TreatmentClass_id'] = $data['TreatmentClass_id'];
				}

				if ($data['SearchFormType'] == 'EvnVizitPL') {
					$FilterDiagTableAlias = ($dbf !== true ? 'evpldiag' : 'dbfdiag');
					$FilterEvnVizitTableAlias = 'EVizitPL';
					$FilterLpuSectionTableAlias = 'evplls';
				}
				else {
					$FilterDiagTableAlias = 'D';
					$FilterEvnVizitTableAlias = 'EVPL';
					$FilterLpuSectionTableAlias = 'LS';
				}

				$queryWithAdditionalJoin = [];
				$queryWithAdditionalWhere = [];

				if (
					$data['SearchFormType'] != 'EvnVizitPL'
					&& (
						!empty($data['LpuSectionViz_id']) || !empty($data['LpuBuildingViz_id'])
						|| !empty($data['MedPersonalViz_id']) || !empty($data['MedStaffFactViz_id'])
						|| !empty($data['MedPersonalViz_sid']) || !empty($data['PayType_id'])
						|| !empty($data['ServiceType_id']) || !empty($data['Vizit_Date_Range'][0])
						|| !empty($data['Vizit_Date_Range'][1]) || !empty($data['VizitType_id'])
						|| !empty($data['DeseaseType_id']) || !empty($data['Diag_Code_From'])
						|| !empty($data['Diag_Code_To']) || !empty($data['HealthKind_id'])
						|| !empty($data['VizitClass_id'])
					)
				) {
					// Типа кагбэ оптимизация, переделываем exists на inner join
					// https://redmine.swan.perm.ru/issues/43268
					// Переделка запроса, ибо работает коряво
					// https://redmine.swan.perm.ru/issues/44109
					// Очередная переделка запроса
					// https://redmine.swan.perm.ru/issues/45337
					// Очередная переделка запроса
					// https://redmine.swan-it.ru/issues/181302

					$queryWithAdditionalWhere[] = $FilterEvnVizitTableAlias . '.Lpu_id ' . $this->getLpuIdFilter($data);

					if (!empty($data['Diag_Code_From']) || !empty($data['Diag_Code_To'])) {
						$queryWithAdditionalJoin[] = "inner join v_Diag as {$FilterDiagTableAlias} on {$FilterDiagTableAlias}.Diag_id = {$FilterEvnVizitTableAlias}.Diag_id";
					}

					if (!empty($data['LpuSectionViz_id']) || !empty($data['LpuBuildingViz_id'])) {
						$queryWithAdditionalJoin[] = "left join v_LpuSection as {$FilterLpuSectionTableAlias} on {$FilterLpuSectionTableAlias}.LpuSection_id = {$FilterEvnVizitTableAlias}.LpuSection_id";
					}

					// Дополнительный фильтр по датам: если не указан диапазон дат посещения, берем диапазоны дат начала и окончания случая
					if (
						empty($data['Vizit_Date_Range_0']) && empty($data['Vizit_Date_Range_1'])
						&& (
							!empty($data['EvnPL_setDate_Range'][0]) || !empty($data['EvnPL_setDate_Range'][1])
							|| !empty($data['EvnPL_disDate_Range'][0]) || !empty($data['EvnPL_disDate_Range'][1])
						)
					) {
						$queryWithAdditionalJoin[] = "inner join Evn as ParentEvn on ParentEvn.Evn_id = {$FilterEvnVizitTableAlias}.EvnVizitPL_pid";

						$queryWithAdditionalWhere[] = "ParentEvn.Lpu_id " . $this->getLpuIdFilter($data);
						$queryWithAdditionalWhere[] = "ParentEvn.EvnClass_id = 3";
						$queryWithAdditionalWhere[] = "ParentEvn.Evn_deleted = 1";

						// Посещение
						if (!empty($data['EvnPL_setDate_Range'][0])) {
							$queryWithAdditionalWhere[] = "cast(ParentEvn.Evn_setDT as date) >= :EvnPL_setDate_Range_0";
							$queryParams['EvnPL_setDate_Range_0'] = $data['EvnPL_setDate_Range'][0];
						}

						// Посещение
						if (!empty($data['EvnPL_setDate_Range'][1])) {
							$queryWithAdditionalWhere[] = "cast(ParentEvn.Evn_setDT as date) <= :EvnPL_setDate_Range_1";
							$queryParams['EvnPL_setDate_Range_1'] = $data['EvnPL_setDate_Range'][1];
						}

						// Посещение
						if (!empty($data['EvnPL_disDate_Range'][0])) {
							$queryWithAdditionalWhere[] = "cast(ParentEvn.Evn_disDT as date) >= :EvnPL_disDate_Range_0";
							$queryParams['EvnPL_disDate_Range_0'] = $data['EvnPL_disDate_Range'][0];
						}

						// Посещение
						if (!empty($data['EvnPL_disDate_Range'][1])) {
							$queryWithAdditionalWhere[] = "cast(ParentEvn.Evn_disDT as date) <= :EvnPL_disDate_Range_1";
							$queryParams['EvnPL_disDate_Range_1'] = $data['EvnPL_disDate_Range'][1];
						}
					}
				}

				// Диагноз и услуги
				if (!empty($data['DeseaseType_id'])) {
					if ($data['DeseaseType_id'] == 99) {
						$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.DeseaseType_id is null";
					}
					else {
						$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.DeseaseType_id = :DeseaseType_id";
						$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
					}
				}

				// Диагноз и услуги
				if (!empty($data['Diag_Code_From'])) {
					$queryWithAdditionalWhere[] = "{$FilterDiagTableAlias}.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				// Диагноз и услуги
				if (!empty($data['Diag_Code_To'])) {
					$queryWithAdditionalWhere[] = "{$FilterDiagTableAlias}.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				// Посещение
				if (!empty($data['LpuSectionViz_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.LpuSection_id = :LpuSectionViz_id";
					$queryParams['LpuSectionViz_id'] = $data['LpuSectionViz_id'];
				}
				else if (!empty($data['LpuBuildingViz_id'])) {
					$queryWithAdditionalJoin[] = "left join LpuUnit as LU on LU.LpuUnit_id = {$FilterLpuSectionTableAlias}.LpuUnit_id";
					$queryWithAdditionalWhere[] = "LU.LpuBuilding_id = :LpuBuildingViz_id";
					$queryParams['LpuBuildingViz_id'] = $data['LpuBuildingViz_id'];
				}

				// Посещение
				if (!empty($data['MedStaffFactViz_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.MedStaffFact_id = :MedStaffFactViz_id";
					$queryParams['MedStaffFactViz_id'] = $data['MedStaffFactViz_id'];
				}

				// Посещение
				if (!empty($data['MedPersonalViz_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.MedPersonal_id = :MedPersonalViz_id";
					$queryParams['MedPersonalViz_id'] = $data['MedPersonalViz_id'];
				}

				// Посещение
				if (!empty($data['MedPersonalViz_sid'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.MedPersonal_sid = :MedPersonalViz_sid";
					$queryParams['MedPersonalViz_sid'] = $data['MedPersonalViz_sid'];
				}

				// Посещение
				if (!empty($data['HealthKind_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.HealthKind_id = :HealthKind_id";
					$queryParams['HealthKind_id'] = $data['HealthKind_id'];
				}

				// Посещение
				if (!empty($data['PayType_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.PayType_id = :PayType_id";
					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Посещение
				if (!empty($data['ServiceType_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.ServiceType_id = :ServiceType_id";
					$queryParams['ServiceType_id'] = $data['ServiceType_id'];
				}

				// Посещение
				if (!empty($data['Vizit_Date_Range'][0])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.EvnVizitPL_setDate >= :Vizit_Date_Range_0";
					$queryParams['Vizit_Date_Range_0'] = $data['Vizit_Date_Range'][0];
				}

				// Посещение
				if (!empty($data['Vizit_Date_Range'][1])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.EvnVizitPL_setDate <= :Vizit_Date_Range_1";
					$queryParams['Vizit_Date_Range_1'] = $data['Vizit_Date_Range'][1];
				}

				// Посещение
				if (!empty($data['VizitClass_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.VizitClass_id = :VizitClass_id";
					$queryParams['VizitClass_id'] = $data['VizitClass_id'];
				}

				// Посещение
				if (!empty($data['VizitType_id'])) {
					$queryWithAdditionalWhere[] = "{$FilterEvnVizitTableAlias}.VizitType_id = :VizitType_id";
					$queryParams['VizitType_id'] = $data['VizitType_id'];
				}

				$clue = "";
				if ($data['SearchFormType'] == 'EvnVizitPL') {
					if (count($queryWithAdditionalJoin) > 0) {
						$query .= "
							" . implode(' ', $queryWithAdditionalJoin) . "
						";
					}

					if (count($queryWithAdditionalWhere) > 0) {
						$filter .= "
							and " . implode(' and ', $queryWithAdditionalWhere) . "
						";
					}
				}
				else if (count($queryWithAdditionalWhere) > 0) {
					$clue = "
						outer apply (
							select top 1 EVPL.EvnVizitPL_id
							from v_EvnVizitPL as EVPL with (nolock)
								" . implode(' ', $queryWithAdditionalJoin) . "
							where
								EVPL.EvnVizitPL_pid = Evn.Evn_id
								and " . implode(' and ', $queryWithAdditionalWhere) . "
						) as EvnVizitTmp
					";

					$filter .= "
						and EvnVizitTmp.EvnVizitPL_id is not null
					";
					/*$queryWithArray[] = "
						EvnVizitTmp (
							EvnVizitPL_id, EvnVizitPL_pid
						) as (
							select EVPL.EvnVizitPL_id, EVPL.EvnVizitPL_pid
							from v_EvnVizitPL as EVPL with (nolock)
								" . implode(' ', $queryWithAdditionalJoin) . "
							where
								" . implode(' and ', $queryWithAdditionalWhere) . "
						)
					";

					$filter .= "
						and exists (select top 1 EvnVizitPL_pid from EvnVizitTmp with(nolock) where EvnVizitPL_pid = EPL.EvnPL_id)
					";*/
				}
				
				//#PROMEDWEB-4790 Оптимизаия запроса. Подставлем условие в нужное место в join
				$query = str_replace("[EvnVizitTmp]", $clue, $query);
				
				unset($queryWithAdditionalJoin);
				unset($queryWithAdditionalWhere);

				// посещение оплачено
				if (!empty($data['EvnVizitPL_isPaid'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPL':
							switch ($this->getRegionNick()) {
								//последнее посещение
								case 'perm':
								case 'astra':
								case 'kareliya':
								case 'pskov':
									$query .= "
										outer apply (
											select top 1 EvnVizitPL_isPaid from v_EvnVizitPL with(nolock) where EvnVizitPL_pid = EPL.EvnPL_id and EvnVizitPL_Index = EvnVizitPL_Count - 1
										) LEVPL
									";
									$filter .= " and ISNULL(LEVPL.EvnVizitPL_isPaid, 1) = :EvnVizitPL_isPaid";
									break;
								//любое посещение
								case 'penza':
								case 'ufa':
								case 'vologda':
									$query .= "
									outer apply ( select top 1 EvnVizitPL_isPaid from v_EvnVizitPL with(nolock) where EvnVizitPL_pid = EPL.EvnPL_id and ISNULL(EvnVizitPL_isPaid, 1) = '2') LEVPLpaid
										";
									$filter .= " and ISNULL(LEVPLpaid.EvnVizitPL_isPaid, 1) = :EvnVizitPL_isPaid";
									break;
								//ТАП
								case 'khak':
								case 'ekb':
								case 'buryatiya':
									$filter .= " and ISNULL(EPL.EvnPL_isPaid, 1) = :EvnVizitPL_isPaid";
									break;
							}
							break;
						case 'EvnVizitPL':
							switch ($this->getRegionNick()) {
								//последнее посещение
								case 'perm':
								case 'astra':
								case 'kareliya':
								case 'pskov':
									$query .= "
											outer apply (
												select top 1 EvnVizitPL_isPaid from v_EvnVizitPL with (nolock) where EvnVizitPL_pid = EVizitPL.EvnVizitPL_pid and EvnVizitPL_Index = EvnVizitPL_Count - 1
											) LEVPL
										";
									$filter .= " and ISNULL(LEVPL.EvnVizitPL_isPaid, 1) = :EvnVizitPL_isPaid";
									break;
								//любое посещение
								case 'penza':
								case 'ufa':
								case 'vologda':
									$query .= "
										outer apply (
											select top 1 EvnVizitPL_isPaid from v_EvnVizitPL with (nolock) where EvnVizitPL_pid = EVizitPL.EvnVizitPL_pid and ISNULL(EvnVizitPL_isPaid, 1) = 2
										) LEVPLpaid
									";
									$filter .= " and ISNULL(LEVPLpaid.EvnVizitPL_isPaid, 1) = :EvnVizitPL_isPaid";
									break;
								//ТАП
								case 'khak':
								case 'ekb':
								case 'buryatiya':
									$filter .= " and ISNULL(EPL.EvnPL_isPaid, 1) = :EvnVizitPL_isPaid";
									break;
							}
							break;
					}
					$queryParams['EvnVizitPL_isPaid'] = $data['EvnVizitPL_isPaid'];
				}

				// Посещение
				if (isset($data['PrehospTrauma_id'])) {
					$filter .= " and EPL.PrehospTrauma_id = :PrehospTrauma_id";
					$queryParams['PrehospTrauma_id'] = $data['PrehospTrauma_id'];
				}
				//направление
				if (isset($data['PL_NumDirection'])) {
					$filter .= " and EPL.EvnDirection_Num = :PL_NumDirection";
					$queryParams['PL_NumDirection'] = $data['PL_NumDirection'];
				}
				//направление
				if (isset($data['PL_DirectionDate'])) {
					$filter .= " and EPL.EvnDirection_setDT = :PL_DirectionDate";
					$queryParams['PL_DirectionDate'] = $data['PL_DirectionDate'];
				}
				//направление
				if (isset($data['PL_ElDirection']) && $data['PL_ElDirection'] == 'on') {
					$filter .= " and EPL.EvnDirection_id is null";
				}
				//направление
				if (isset($data['PL_Org_id'])) {
					if ($data['SearchFormType'] == 'EvnPL' && $dbf !== true) {
						$filter .= " and (EPL.Org_did = :PL_Org_id or dlpu.Org_id = :PL_Org_id)";
					} else {
						$filter .= " and EPL.Org_did = :PL_Org_id";
					}
					$queryParams['PL_Org_id'] = $data['PL_Org_id'];
				}
				//направление
				if (isset($data['PL_LpuSection_id'])) {
					$filter .= " and EPL.LpuSection_did = :PL_LpuSection_id";
					$queryParams['PL_LpuSection_id'] = $data['PL_LpuSection_id'];
				}
				//направление
				if (isset($data['PL_Diag_id'])) {
					$filter .= " and EPL.Diag_did = :PL_Diag_id";
					$queryParams['PL_Diag_id'] = $data['PL_Diag_id'];
				}
				//направление
				if (isset($data['PL_PrehospDirect_id'])) {
					if ($data['PL_PrehospDirect_id'] == 99) {
						$filter .= " and EPL.PrehospDirect_id is null";
					} else {
						$filter .= " and EPL.PrehospDirect_id = :PL_PrehospDirect_id";
						$queryParams['PL_PrehospDirect_id'] = $data['PL_PrehospDirect_id'];
					}
				}
				// Результаты
				if (isset($data['DirectClass_id'])) {
					$filter .= " and EPL.DirectClass_id = :DirectClass_id";
					$queryParams['DirectClass_id'] = $data['DirectClass_id'];
				}

				// Результаты
				if (isset($data['DirectType_id'])) {
					$filter .= " and EPL.DirectType_id = :DirectType_id";
					$queryParams['DirectType_id'] = $data['DirectType_id'];
				}

				// Результаты
				if (isset($data['EvnPL_IsFinish'])) {
					$filter .= " and EvnPLBase.EvnPLBase_IsFinish = :EvnPL_IsFinish";
					$queryParams['EvnPL_IsFinish'] = $data['EvnPL_IsFinish'];
				}

				// Результаты
				if (isset($data['Lpu_oid'])) {
					$filter .= " and EPL.Lpu_oid = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Результаты
				if (isset($data['LpuSection_oid'])) {
					$filter .= " and EPL.LpuSection_oid = :LpuSection_oid";
					$queryParams['LpuSection_oid'] = $data['LpuSection_oid'];
				}

				// Результаты
				if (isset($data['ResultClass_id'])) {
					$filter .= " and EPL.ResultClass_id = :ResultClass_id";
					$queryParams['ResultClass_id'] = $data['ResultClass_id'];
				}

				if (isset($data['StickCause_id']) || isset($data['StickType_id']) ||
						isset($data['EvnStick_begDate_Range'][0]) || isset($data['EvnStick_begDate_Range'][1]) ||
						isset($data['EvnStick_endDate_Range'][0]) || isset($data['EvnStick_endDate_Range'][1])
				) {
					$evn_stick_filter = '';

					// Результаты
					if (isset($data['EvnStick_begDate_Range'][0])) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
						$queryParams['EvnStick_begDate_Range_0'] = $data['EvnStick_begDate_Range'][0];
					}

					// Результаты
					if (isset($data['EvnStick_begDate_Range'][1])) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
						$queryParams['EvnStick_begDate_Range_1'] = $data['EvnStick_begDate_Range'][1];
					}

					// Результаты
					if (isset($data['EvnStick_endDate_Range'][0])) {
						$evn_stick_filter .= " and (
							case
								when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0 then 1
								when ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0) then 1
								else 0
							end = 1
						)";
						$queryParams['EvnStick_endDate_Range_0'] = $data['EvnStick_endDate_Range'][0];
					}

					// Результаты
					if (isset($data['EvnStick_endDate_Range'][1])) {
						$evn_stick_filter .= " and (
							case
								when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1 then 1
								when ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1) then 1
								else 0
							end = 1
						)";
						$queryParams['EvnStick_endDate_Range_1'] = $data['EvnStick_endDate_Range'][1];
					}

					// Результаты
					if (isset($data['StickCause_id'])) {
						$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
						$queryParams['StickCause_id'] = $data['StickCause_id'];
					}

					// Результаты
					if (isset($data['StickType_id'])) {
						$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
						$queryParams['StickType_id'] = $data['StickType_id'];
					}

					// ЛВН должны быть созданы в рамках учетного документа (первый селект из v_EvnStickBase)
					// либо присоединены ЛВН из других учетных документов (второй селект из v_EvnLink)
					$filter .= "
						and exists (
							select EvnStickBase_id
							from v_EvnStickBase ESB with (nolock)
							where ESB.EvnStickBase_mid = EPL.EvnPL_id
								" . $evn_stick_filter . "
							union all
							select Evn_id as EvnStickBase_id
							from v_EvnLink EL with (nolock)
								inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
							where EL.Evn_id = EPL.EvnPL_id
								" . $evn_stick_filter . "
						)
					";
				}
				break;

			case 'EPLStomPerson':
			case 'EvnPLStom':
			case 'EvnVizitPLStom':
			case 'EvnUslugaStom':
			case 'EvnAggStom':
				$query .= " left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id ";
				if ($data['PersonPeriodicType_id'] == 2) {
					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$query .= " inner join v_EvnVizitPLStom as EVPLS with (nolock) on EVPLS.Server_id = PS.Server_id and EVPLS.PersonEvn_id = PS.PersonEvn_id and EVPLS.Lpu_id " . $this->getLpuIdFilter($data);
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid and EPLS.Lpu_id " . $this->getLpuIdFilter($data) . " and EPLS.EvnClass_id = 6";
					} else {
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.Server_id = PS.Server_id and EPLS.PersonEvn_id = PS.PersonEvn_id and EPLS.Lpu_id " . $this->getLpuIdFilter($data) . " and EPLS.EvnClass_id = 6";
					}
				} else {
					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$query .= " inner join v_EvnVizitPLStom as EVPLS with (nolock) on EVPLS.Person_id = PS.Person_id and EVPLS.Lpu_id " . $this->getLpuIdFilter($data);
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid and EPLS.Lpu_id " . $this->getLpuIdFilter($data) . " and EPLS.EvnClass_id = 6";
					} else {
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.Person_id = PS.Person_id and EPLS.Lpu_id " . $this->getLpuIdFilter($data) . " and EPLS.EvnClass_id = 6";
					}
				}

				// $query .= " inner join v_EvnPLStom EPLS with(nolock) on EPLS.Person_id = PS.Person_id and EPLS.Lpu_id = :Lpu_id and EPLS.EvnClass_id = 6";
				if ($dbf !== true) {
					if ('EvnPLStom' == $data['SearchFormType']) {
						$query .= " left join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_pid = EPLS.EvnPLStom_id and EVPLS.EvnVizitPLStom_Index = EVPLS.EvnVizitPLStom_Count - 1 and EVPLS.Lpu_id " . $this->getLpuIdFilter($data);
						$query .= " left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPLS.EvnPLStom_IsFinish";
						$query .= " left join v_Diag EVPLSD with (nolock) on EVPLSD.Diag_id = isnull(EPLS.Diag_id, EVPLS.Diag_id)";
						$query .= " left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EVPLS.MedStaffFact_id";
						$query .= " left join v_Address ua with (nolock) on ua.Address_id = PS.UAddress_id";
                        $query .= " left join v_Address pa with (nolock) on pa.Address_id = PS.PAddress_id";
						$query .= " outer apply (
	                        select
	                            count(EvnVizitPLStom_id) as EvnPLStom_VizitCount
	                        from
	                            v_EvnVizitPLStom with (nolock)
	                        where
	                            EvnVizitPLStom_pid = EPLS.EvnPLStom_id
	                    ) as CNT ";

						// https://redmine.swan.perm.ru/issues/16145
						if (!empty($data['DeseaseType_id']) || !empty($data['Diag_Code_From']) ||
								!empty($data['Diag_Code_To'])
						) {
							$filter .= " and exists (
								select t1.EvnVizitPLStom_id
								from v_EvnVizitPLStom t1 with (nolock)
									inner join v_Diag t2 with (nolock) on t2.Diag_id = t1.Diag_id
								where t1.EvnVizitPLStom_pid = EPLS.EvnPLStom_id
							";

							// Диагноз и услуги
							if (!empty($data['DeseaseType_id'])) {
								if ($data['DeseaseType_id'] == 99) {
									$filter .= " and t1.DeseaseType_id is null";
								} else {
									$filter .= " and t1.DeseaseType_id = :DeseaseType_id";
									$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
								}
							}

							// Диагноз и услуги
							if (!empty($data['Diag_Code_From'])) {
								$filter .= " and t2.Diag_Code >= :Diag_Code_From";
								$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
							}

							// Диагноз и услуги
							if (!empty($data['Diag_Code_To'])) {
								$filter .= " and t2.Diag_Code <= :Diag_Code_To";
								$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
							}

							$filter .= ")";
						}

						if ($this->regionNick == 'kz') {
							$query .= " left join r101.AISResponse air (nolock) on air.Evn_id = EPLS.EvnPLStom_id and air.AISFormLoad_id = 1 ";
							$query .= " left join r101.AISResponse air9 (nolock) on air9.Evn_id = EPLS.EvnPLStom_id and air9.AISFormLoad_id = 2 ";
							$query .= " outer apply (
								select top 1 eu.UslugaComplex_id
								from v_EvnUsluga_all eu (nolock)
								inner join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = eu.UslugaComplex_id
								where eu.EvnUsluga_rid = EPLS.EvnPLStom_id and ucl.AISFormLoad_id = 1
							) as euais ";
							$query .= " outer apply (
								select top 1 eu.UslugaComplex_id
								from v_EvnUsluga_all eu (nolock)
								inner join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = eu.UslugaComplex_id
								where eu.EvnUsluga_rid = EPLS.EvnPLStom_id and ucl.AISFormLoad_id = 1
							) as euais9 ";
							if ($data['toAis25'] == 2) {
								$filter .= " and air.AISResponse_id is not null";
							} elseif ($data['toAis25'] == 1) {
								$filter .= " and air.AISResponse_id is null and euais.UslugaComplex_id is not null";
							}
							if ($data['toAis259'] == 2) {
								$filter .= " and air9.AISResponse_id is not null";
							} elseif ($data['toAis259'] == 1) {
								$filter .= " and air9.AISResponse_id is null and euais9.UslugaComplex_id is not null";
							}
						}
					}
					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$query .= "
							left join v_LpuSection as evplls with (nolock) on evplls.LpuSection_id=EVPLS.LpuSection_id
							left join v_PayType as evplpt with (nolock) on evplpt.PayType_id=EVPLS.PayType_id
							left join v_VizitType as evplvt with (nolock) on evplvt.VizitType_id=EVPLS.VizitType_id
							left join v_ServiceType as evplst with (nolock) on evplst.ServiceType_id=EVPLS.ServiceType_id
							left join v_Diag as evpldiag with (nolock) on evpldiag.Diag_id = EVPLS.Diag_id
						";

						// https://redmine.swan.perm.ru/issues/16145
						if (!empty($data['DeseaseType_id']) || !empty($data['Diag_Code_From']) ||
								!empty($data['Diag_Code_To'])
						) {
							// Диагноз и услуги
							if (!empty($data['DeseaseType_id'])) {
								$filter .= " and EVPLS.DeseaseType_id = :DeseaseType_id";
								$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
							}

							// Диагноз и услуги
							if (!empty($data['Diag_Code_From'])) {
								$filter .= " and evpldiag.Diag_Code >= :Diag_Code_From";
								$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
							}

							// Диагноз и услуги
							if (!empty($data['Diag_Code_To'])) {
								$filter .= " and evpldiag.Diag_Code <= :Diag_Code_To";
								$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
							}
						}
					}
					$query .= " left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLS.EvnPLStom_id ";
				} else {
					switch ($data['SearchFormType']) {
						case 'EPLStomPerson':
							$query .= " inner join v_EvnPLStom EPLS2 (nolock) on EPLS2.EvnPLStom_id = EPLS.EvnPLStom_id ";
							if ($PLS_prefix == 'PS2') { //требуется использование дополнительной таблицы
								if ($data['eplstom_date_type'] == 2) {
									$query .= "
										inner join v_Person_all PS2 with (nolock) on PS2.Server_id = EPLS2.Server_id and PS2.PersonEvn_id = EPLS2.PersonEvn_id
									";
								} else {
									$query .= "
										inner join v_PersonState PS2 with (nolock) on PS2.Person_id = EPLS.Person_id
									";
								}
							}
							$query .= "
								--inner join v_PersonState PS (nolock) on PS.Person_id = EPLS2.Person_id
								left join Sex with (nolock) on Sex.Sex_id = {$PLS_prefix}.Sex_id
								left join SocStatus Soc with (nolock) on Soc.SocStatus_id = {$PLS_prefix}.SocStatus_id
								left join PersonChild PCh with (nolock) on PCh.Person_id = {$PLS_prefix}.Person_id
								left join YesNo IsInv with (nolock) on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
								left join Diag InvD with (nolock) on InvD.Diag_id = PCh.Diag_id
								left join Polis Pol with (nolock) on Pol.Polis_id = {$PLS_prefix}.Polis_id
								left join OMSSprTerr OMSST with (nolock) on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
								left join PolisType PolTp with (nolock) on PolTp.PolisType_id = Pol.PolisType_id
								left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Pol.OrgSmo_id
								left join v_Org OSO with (nolock) on OSO.Org_id = OS.Org_id
								left join v_Address_all UA with (nolock) on UA.Address_id = {$PLS_prefix}.UAddress_id 
								left join v_Address_all PA with (nolock) on PA.Address_id = {$PLS_prefix}.PAddress_id
								left join Document Doc with (nolock) on Doc.Document_id = {$PLS_prefix}.Document_id 
								left join DocumentType DocTp with (nolock) on DocTp.DocumentType_id = Doc.DocumentType_id
								left join v_OrgDep OrgD with (nolock) on OrgD.OrgDep_id = Doc.OrgDep_id
							";
							break;
						case 'EvnAggStom':
							$query .= "
								inner join v_EvnUslugaStom EvnUsluga with (nolock) on EvnUsluga.EvnUslugaStom_rid = EPLS.EvnPLStom_id and EvnUsluga.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_EvnAgg EvnAgg with (nolock) on EvnAgg.EvnAgg_pid = EvnUsluga.EvnUslugaStom_id and EvnAgg.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join AggType as dbfat with (nolock) on dbfat.AggType_id = EvnAgg.AggType_id
								left join AggWhen as dbfaw with (nolock) on dbfaw.AggWhen_id = EvnAgg.AggWhen_id
							";
							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									--inner join v_Person_all PS with (nolock) on EPL.Server_id = PS.Server_id and EPLS.PersonEvn_id = PS.PersonEvn_id
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										--inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								}
							}
							break;
						case 'EvnPLStom':
							$query .= "
								left join v_Diag as dbfdiag with (nolock) on dbfdiag.Diag_id = EPLS.Diag_id
								left join v_Lpu as dbflpu with (nolock) on dbflpu.Lpu_id = EPLS.Lpu_id
								left join ResultClass dbfrc with (nolock) on dbfrc.ResultClass_id = EPLS.ResultClass_id
								left join DeseaseType dbfdt with (nolock) on dbfdt.DeseaseType_id = EPLS.DeseaseType_id
								left join PrehospDirect dbfpd with (nolock) on dbfpd.PrehospDirect_id = EPLS.PrehospDirect_id
								left join v_Lpu dbfprehosplpu with (nolock) on dbfprehosplpu.Lpu_id = EPLS.Lpu_did
								left join LpuSection dbflsd with (nolock) on dbflsd.LpuSection_id = EPLS.LpuSection_did
								left join YesNo dbfift with (nolock) on dbfift.YesNo_id = EPLS.EvnPLStom_IsFirstTime
								left join DirectClass dbfdc with (nolock) on dbfdc.DirectClass_id = EPLS.DirectClass_id
								
								
								
								left join v_Lpu dbflpudir with (nolock) on dbflpudir.Lpu_id = EPLS.Lpu_oid
								left join LpuSection dbflsdir with (nolock) on dbflsdir.LpuSection_id = EPLS.LpuSection_oid
								outer apply (
									select top 1
										YesNo.YesNo_Code as PersonChild_IsInvalid_Code,
										PersonSprTerrDop.PersonSprTerrDop_Code as PermRegion_Code
									from PersonChild with (nolock)
										left join YesNo with (nolock) on YesNo.YesNo_id = PersonChild.PersonChild_IsInvalid
										left join PersonSprTerrDop with (nolock) on PersonSprTerrDop.PersonSprTerrDop_id = PersonChild.PersonSprTerrDop_id
									where PersonChild.Person_id = EPLS.Person_id
									order by PersonChild.PersonChild_insDT desc
								) dbfinv
							";

							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									--inner join v_Person_all PS with (nolock) on EPLS.Server_id = PS.Server_id and EPLS.PersonEvn_id = PS.PersonEvn_id
									--left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
									left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
									left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
									left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
									left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
									left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										--inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
										--left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
										left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
										left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
										left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
										left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
										left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
									";
								} else {
									$query .= "
										--inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
										left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
										left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
										left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
										left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
										left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
										left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
									";
								}
							}
							break;

						case 'EvnUslugaStom':
							$query .= "
								inner join v_EvnUslugaStom EvnUsluga with (nolock) on EvnUsluga.EvnUslugaStom_rid = EPLS.EvnPLStom_id and EvnUsluga.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_PayType as dbfpt with (nolock) on dbfpt.PayType_id = EvnUsluga.PayType_id
								left join v_UslugaComplex as dbfusluga with (nolock) on dbfusluga.UslugaComplex_id = EvnUsluga.UslugaComplex_id
								left join v_UslugaPlace as dbfup with (nolock) on dbfup.UslugaPlace_id = EvnUsluga.UslugaPlace_id
								left join v_MedPersonal dbfmp with (nolock) on dbfmp.MedPersonal_id = EvnUsluga.MedPersonal_id
									--and EPLS.Lpu_id = Evn.Lpu_id
							";
							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									--inner join v_Person_all PS with (nolock) on EPL.Server_id = PS.Server_id and EPLS.PersonEvn_id = PS.PersonEvn_id
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										--inner join v_PersonState PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on Evn.Person_id = PS.Person_id
									";
								}
							}
							break;
						case 'EvnVizitPLStom':
							$query .= "
								left join v_Diag as dbfdiag with (nolock) on dbfdiag.Diag_id=EVPLS.Diag_id
								left join LpuSection as dbfls with (nolock) on dbfls.LpuSection_id=EVPLS.LpuSection_id
								left join v_MedPersonal as dbfmp with (nolock) on dbfmp.MedPersonal_id=EVPLS.MedPersonal_id-- and EVPLS.Lpu_id = Evn.Lpu_id
								left join PayType as dbfpt with (nolock) on dbfpt.PayType_id=EVPLS.PayType_id
								left join VizitClass as dbfvc with (nolock) on dbfvc.VizitClass_id=EVPLS.VizitClass_id
								left join VizitType as dbfvt with (nolock) on dbfvt.VizitType_id=EVPLS.VizitType_id
								left join DeseaseType as dbfdt with (nolock) on dbfdt.DeseaseType_id=EVPLS.DeseaseType_id
								left join ServiceType as dbfst with (nolock) on dbfst.ServiceType_id=EVPLS.ServiceType_id
								
								left join ProfGoal as dbfpg with (nolock) on dbfpg.ProfGoal_id=EVPLS.ProfGoal_id
							";
							if (in_array($data['PersonPeriodicType_id'], array(2))) {
								$query .= "
									--inner join v_Person_all PS with (nolock) on EVPLS.PersonEvn_id = PS.PersonEvn_id
									--left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
								";
							} else {
								if (!isset($data['Refuse_id'])) { // данные по отказу есть только в v_PersonState_all
									$query .= "
										--inner join v_PersonState PS with (nolock) on EVPLS.Person_id = PS.Person_id
										--left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
									";
								} else {
									$query .= "
										inner join v_PersonState_all PS with (nolock) on EVPLS.Person_id = PS.Person_id
										left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
									";
								}
							}
							break;
					}
					if (isset($data['and_eplstomperson']) && $data['and_eplstomperson']) { //при комбинировании с таблицой персон добавляем дополнительные поля
						if ($PLS_prefix == 'PS2') { //требуется использование дополнительной таблицы
							$query .= " inner join v_EvnPLStom EPLS2 (nolock) on EPLS2.EvnPLStom_id = EPLS.EvnPLStom_id ";
							if ($data['eplstom_date_type'] == 2) {
								$query .= "
									left join v_Person_all PS2 with (nolock) on PS2.Server_id = EPLS2.Server_id and PS2.PersonEvn_id = EPLS2.PersonEvn_id
								";
							} else {
								$query .= "
									left join v_PersonState PS2 with (nolock) on PS2.Person_id = EPLS2.Person_id
								";
							}
						}
						$query .= "
							left join Sex PrsSex with (nolock) on PrsSex.Sex_id = {$PLS_prefix}.Sex_id
							left join SocStatus PrsSoc with (nolock) on PrsSoc.SocStatus_id = {$PLS_prefix}.SocStatus_id
							left join PersonChild PrsPCh with (nolock) on PrsPCh.Person_id = {$PLS_prefix}.Person_id
							left join YesNo PrsIsInv with (nolock) on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
							left join Diag PrsInvD with (nolock) on PrsInvD.Diag_id = PrsPCh.Diag_id
							left join Polis PrsPol with (nolock) on PrsPol.Polis_id = {$PLS_prefix}.Polis_id
							left join OMSSprTerr PrsOMSST with (nolock) on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
							left join PolisType PrsPolTp with (nolock) on PrsPolTp.PolisType_id = PrsPol.PolisType_id
							left join v_OrgSmo PrsOS with (nolock) on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
							left join v_Org PrsOSO with (nolock) on PrsOSO.Org_id = PrsOS.Org_id
							left join v_Address_all PrsUA with (nolock) on PrsUA.Address_id = {$PLS_prefix}.UAddress_id 
							left join v_Address_all PrsPA with (nolock) on PrsPA.Address_id = {$PLS_prefix}.PAddress_id
							left join Document PrsDoc with (nolock) on PrsDoc.Document_id = {$PLS_prefix}.Document_id 
							left join DocumentType PrsDocTp with (nolock) on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
							left join v_OrgDep PrsOrgD with (nolock) on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
						";
					}
				}
				// @todo переделать на outer apply
				if ('EvnVizitPLStom' == $data['SearchFormType']) {
					$mp_filter = 'MedPersonal_id = EVPLS.MedPersonal_id';
				} else if ('EvnPLStom' == $data['SearchFormType'] && !$dbf) {
					$mp_filter = 'MedPersonal_id = coalesce(EPLS.MedPersonal_id, EVPLS.MedPersonal_id, MSF.MedPersonal_id)';
				} else {
					$mp_filter = 'MedPersonal_id = EPLS.MedPersonal_id';
				}
				$query .= "
					outer apply (
						select top 1 *
						from v_MedPersonal with (nolock)
						where {$mp_filter}
							and Lpu_id " . $this->getLpuIdFilter($data) . "
						order by
							case when Lpu_id = :Lpu_id then 1 else 2 end
					) MP
				";

				if (!empty($data['UslugaCategory_id']) || !empty($data['UslugaComplex_Code_From']) || !empty($data['UslugaComplex_Code_To'])) {
					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id";
					} else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPLS.EvnPLStom_id";
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							" . $filter_evnvizit . "
							and EU.Lpu_id " . $this->getLpuIdFilter($data) . "
							and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaStom')
					";

					if (!empty($data['UslugaCategory_id'])) {
						$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id ";
						$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
					}

					if (!empty($data['UslugaComplex_Code_From'])) {
						$filter .= " and uc.UslugaComplex_Code >= :UslugaComplex_Code_From ";
						$queryParams['UslugaComplex_Code_From'] = $data['UslugaComplex_Code_From'];
					}

					if (!empty($data['UslugaComplex_Code_To'])) {
						$filter .= " and uc.UslugaComplex_Code <= :UslugaComplex_Code_To ";
						$queryParams['UslugaComplex_Code_To'] = $data['UslugaComplex_Code_To'];
					}

					$filter .= ")";
				}

				if (!empty($data['UslugaComplex_uid']) || !empty($data['UslugaComplex_Code'])) {
					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id";
					} else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPLS.EvnPLStom_id";
					}

					if (!empty($data['UslugaComplex_uid'])) {
						$filter_evnvizit .= " and uc.UslugaComplex_id = :UslugaComplex_uid";
						$queryParams['UslugaComplex_uid'] = $data['UslugaComplex_uid'];
					}

					if (!empty($data['UslugaComplex_Code'])) {
						$data['UslugaComplex_Code'] = str_replace('%', '', $data['UslugaComplex_Code']);

						$filter_evnvizit .= " and uc.UslugaComplex_Code like :UslugaComplexCode";
						$queryParams['UslugaComplexCode'] = '%' . $data['UslugaComplex_Code'];
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							 " . $filter_evnvizit . "
							 and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaStom')
						)
					";
				}

				//Посещение
				if (isset($data['TreatmentClass_id'])) {
					$filter .= " and EVPLS.TreatmentClass_id = :TreatmentClass_id";
					$queryParams['TreatmentClass_id'] = $data['TreatmentClass_id'];
				}

				// Посещение
				if (isset($data['EvnPL_IsUnlaw'])) {
					$filter .= " and EPLS.EvnPLStom_IsUnlaw = :EvnPLStom_IsUnlaw";
					$queryParams['EvnPLStom_IsUnlaw'] = $data['EvnPL_IsUnlaw'];
				}

				// Посещение
				if (isset($data['EvnPL_IsUnport'])) {
					$filter .= " and EPLS.EvnPLStom_IsUnport = :EvnPLStom_IsUnport";
					$queryParams['EvnPLStom_IsUnport'] = $data['EvnPL_IsUnport'];
				}

				// Посещение
				if (isset($data['EvnPL_NumCard'])) {
					$filter .= " and EPLS.EvnPLStom_NumCard = :EvnPLStom_NumCard";
					$queryParams['EvnPLStom_NumCard'] = $data['EvnPL_NumCard'];
				}

				// Посещение
				if (isset($data['EvnPL_setDate_Range'][0])) {
					$filter .= " and EPLS.EvnPLStom_setDate >= :EvnPLStom_setDate_Range_0";
					$queryParams['EvnPLStom_setDate_Range_0'] = $data['EvnPL_setDate_Range'][0];
				}

				// Посещение
				if (isset($data['EvnPL_setDate_Range'][1])) {
					$filter .= " and EPLS.EvnPLStom_setDate <= :EvnPLStom_setDate_Range_1";
					$queryParams['EvnPLStom_setDate_Range_1'] = $data['EvnPL_setDate_Range'][1];
				}

				// Посещение
				if (isset($data['EvnPL_disDate_Range'][0])) {
					$filter .= " and EPLS.EvnPLStom_disDate >= :EvnPLStom_disDate_Range_0";
					$queryParams['EvnPLStom_disDate_Range_0'] = $data['EvnPL_disDate_Range'][0];
				}

				// Посещение
				if (isset($data['EvnPL_disDate_Range'][1])) {
					$filter .= " and EPLS.EvnPLStom_disDate <= :EvnPLStom_disDate_Range_1";
					$queryParams['EvnPLStom_disDate_Range_1'] = $data['EvnPL_disDate_Range'][1];
				}

				// Посещение
				if (isset($data['PrehospTrauma_id'])) {
					$filter .= " and EPLS.PrehospTrauma_id = :PrehospTrauma_id";
					$queryParams['PrehospTrauma_id'] = $data['PrehospTrauma_id'];
				}

				// #107998 Фильтр по признаку включения реестр
				if(isset($data['EvnPLStom_InRegistry'])){
					if(in_array($this->regionNick, array('ekb'))){
						if($data['EvnPLStom_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП Стоматологии, не вошедшие в реестр');
							$filter .= " and (EPLS.EvnPLStom_IsInReg = 1 or EPLS.EvnPLStom_IsInReg is null)";
						}elseif($data['EvnPLStom_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП Стоматологии, вошедшие в реестр');
							$filter .= " and EPLS.EvnPLStom_IsInReg = 2";
						}
					}
					elseif(in_array($this->regionNick, array('penza'))){
						if($data['EvnPLStom_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП Стоматологии с заболеваниями в реестре');
							$filter .= " and not exists (select top 1 EvnDiagPLStom_id from v_EvnDiagPLStom EDPLS with (nolock) where EDPLS.EvnDiagPLStom_rid = EPLS.EvnPLStom_id and EDPLS.EvnDiagPLStom_IsInreg = 2)";
						}elseif($data['EvnPLStom_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только ТАП Стоматологии с заболеваниями не в реестре');
							$filter .= " and exists (select top 1 EvnDiagPLStom_id from v_EvnDiagPLStom EDPLS with (nolock) where EDPLS.EvnDiagPLStom_rid = EPLS.EvnPLStom_id and EDPLS.EvnDiagPLStom_IsInreg = 2)";
						}
					}
				}

				// Диагноз и услуги
				if (!empty($data['Diag_IsNotSet'])) {
					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$filter .= " and EVPLS.Diag_id is null";
					} else {
						$filter .= " and EPLS.Diag_id is null";
					}
				}

				// посещение оплачено
				if (!empty($data['EvnVizitPLStom_isPaid'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPLStom':
							switch ($this->getRegionNick()) {
								//последнее посещение
								case 'astra':
								case 'kareliya':
								case 'pskov':
									$query .= "
										outer apply (
											select top 1 EvnVizitPLStom_isPaid from v_EvnVizitPLStom with(nolock) where EvnVizitPLStom_pid = EPLS.EvnPLStom_id and EvnVizitPLStom_Index = EvnVizitPLStom_Count - 1
										) LEVPLS";
									$filter .= " and ISNULL(LEVPLS.EvnVizitPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
									break;
								//любое посещение
								case 'penza':
								case 'ufa':
								case 'vologda':
								case 'perm':
									$query .= "
									outer apply ( select top 1 EvnVizitPLStom_isPaid from v_EvnVizitPLStom with(nolock) where EvnVizitPLStom_pid = EPLS.EvnPLStom_id and ISNULL(EvnVizitPLStom_isPaid, 1) = '2') EVPLSpaid
										";
									$filter .= " and ISNULL(EVPLSpaid.EvnVizitPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
									break;
								//ТАП
								case 'khak':
								case 'ekb':
								case 'buryatiya':
									$filter .= " and ISNULL(EPLS.EvnPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
									break;
							}
							break;
						case 'EvnVizitPLStom':
							$filter .= " and ISNULL(EVPLS.EvnVizitPLStom_isPaid,1) = :EvnVizitPLStom_isPaid";
							break;
					}
					$queryParams['EvnVizitPLStom_isPaid'] = $data['EvnVizitPLStom_isPaid'];
				}

				if (isset($data['LpuSectionViz_id']) /* || isset($data['EvnVizitPLStom_isPaid']) */ || isset($data['LpuBuildingViz_id']) || isset($data['MedPersonalViz_id']) || isset($data['MedStaffFactViz_id']) || isset($data['MedPersonalViz_sid']) ||
						isset($data['PayType_id']) || isset($data['ServiceType_id']) || isset($data['Vizit_Date_Range'][0]) ||
						isset($data['Vizit_Date_Range'][1]) || isset($data['VizitType_id']) || isset($data['EvnVizitPLStom_IsPrimaryVizit'])
				) {
					$filter .= " and exists (
						select 1
						from v_EvnVizitPLStom EVPLS2 with (nolock)
						where (1 = 1) and EVPLS2.Lpu_id " . $this->getLpuIdFilter($data) . "
							
					";

					if ('EvnVizitPLStom' == $data['SearchFormType']) {
						$filter .= " and EVPLS2.EvnVizitPLStom_id = EVPLS.EvnVizitPLStom_id";
					} else {
						$filter .= " and EVPLS2.EvnVizitPLStom_pid = EPLS.EvnPLStom_id";
					}

					// Посещение
					if (isset($data['LpuSectionViz_id'])) {
						$filter .= " and EVPLS2.LpuSection_id = :LpuSectionViz_id";
						$queryParams['LpuSectionViz_id'] = $data['LpuSectionViz_id'];
					} elseif (isset($data['LpuBuildingViz_id'])) {
						$filter .= " and exists (
							select
								1
							from LpuSection LS with (nolock)
								left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
							where
								LU.LpuBuilding_id = :LpuBuildingViz_id AND
								LS.LpuSection_id = EVPLS2.LpuSection_id
							)";
						$queryParams['LpuBuildingViz_id'] = $data['LpuBuildingViz_id'];
					}

					// Посещение
					if (isset($data['MedStaffFactViz_id'])) {
						$filter .= " and EVPLS2.MedStaffFact_id = :MedStaffFactViz_id";
						$queryParams['MedStaffFactViz_id'] = $data['MedStaffFactViz_id'];
					}

					// Посещение
					if (isset($data['MedPersonalViz_id'])) {
						$filter .= " and EVPLS2.MedPersonal_id = :MedPersonalViz_id";
						$queryParams['MedPersonalViz_id'] = $data['MedPersonalViz_id'];
					}

					// Посещение
					/* if (isset($data['EvnVizitPLStom_isPaid'])) {
					  $filter .= " and ISNULL(EVPLS2.EvnVizitPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
					  $queryParams['EvnVizitPLStom_isPaid'] = $data['EvnVizitPLStom_isPaid'];
					  } */

					// Посещение
					if (isset($data['MedPersonalViz_sid'])) {
						$filter .= " and EVPLS2.MedPersonal_sid = :MedPersonalViz_sid";
						$queryParams['MedPersonalViz_sid'] = $data['MedPersonalViz_sid'];
					}

					// Посещение
					if (isset($data['PayType_id'])) {
						$filter .= " and EVPLS2.PayType_id = :PayType_id";
						$queryParams['PayType_id'] = $data['PayType_id'];
					}

					if (isset($data['EvnVizitPLStom_IsPrimaryVizit'])) {
						$filter .= " and EVPLS2.EvnVizitPLStom_IsPrimaryVizit = :EvnVizitPLStom_IsPrimaryVizit";
						$queryParams['EvnVizitPLStom_IsPrimaryVizit'] = $data['EvnVizitPLStom_IsPrimaryVizit'];
					}

					// Посещение
					if (isset($data['ServiceType_id'])) {
						$filter .= " and EVPLS2.ServiceType_id = :ServiceType_id";
						$queryParams['ServiceType_id'] = $data['ServiceType_id'];
					}

					// Посещение
					if (isset($data['Vizit_Date_Range'][0])) {
						$filter .= " and EVPLS2.EvnVizitPLStom_setDate >= :Vizit_Date_Range_0";
						$queryParams['Vizit_Date_Range_0'] = $data['Vizit_Date_Range'][0];
					}

					// Посещение
					if (isset($data['Vizit_Date_Range'][1])) {
						$filter .= " and EVPLS2.EvnVizitPLStom_setDate <= :Vizit_Date_Range_1";
						$queryParams['Vizit_Date_Range_1'] = $data['Vizit_Date_Range'][1];
					}

					// Посещение
					if (isset($data['VizitType_id'])) {
						$filter .= " and EVPLS2.VizitType_id = :VizitType_id";
						$queryParams['VizitType_id'] = $data['VizitType_id'];
					}

					$filter .= ")";
				}
				//направление
				if (isset($data['PL_NumDirection'])) {
					$filter .= " and EPLS.EvnDirection_Num = :PL_NumDirection";
					$queryParams['PL_NumDirection'] = $data['PL_NumDirection'];
				}
				//направление
				if (isset($data['PL_DirectionDate'])) {
					$filter .= " and isnull(EPLS.EvnDirection_setDT,EPLS.EvnPLStom_setDate) = :PL_DirectionDate";
					$queryParams['PL_DirectionDate'] = $data['PL_DirectionDate'];
				}
				//направление
				if (isset($data['PL_ElDirection']) && $data['PL_ElDirection'] == 'on') {
					$filter .= " and EPLS.EvnDirection_id is null";
				}
				//направление
				if (isset($data['PL_Org_id'])) {
					$filter .= " and EPLS.Org_did = :PL_Org_id";
					$queryParams['PL_Org_id'] = $data['PL_Org_id'];
				}
				//направление
				if (isset($data['PL_LpuSection_id'])) {
					$filter .= " and EPLS.LpuSection_did = :PL_LpuSection_id";
					$queryParams['PL_LpuSection_id'] = $data['PL_LpuSection_id'];
				}
				//направление
				if (isset($data['PL_Diag_id'])) {
					$filter .= " and EPLS.diag_did = :PL_Diag_id";
					$queryParams['PL_Diag_id'] = $data['PL_Diag_id'];
				}
				//направление
				if (isset($data['PL_PrehospDirect_id'])) {
					if ($data['PL_PrehospDirect_id'] == 99) {
						$filter .= " and EPLS.PrehospDirect_id is null";
					} else {
						$filter .= " and EPLS.PrehospDirect_id = :PL_PrehospDirect_id";
						$queryParams['PL_PrehospDirect_id'] = $data['PL_PrehospDirect_id'];
					}
				}
				// Результаты
				if (isset($data['DirectClass_id'])) {
					$filter .= " and EPLS.DirectClass_id = :DirectClass_id";
					$queryParams['DirectClass_id'] = $data['DirectClass_id'];
				}

				// Результаты
				if (isset($data['DirectType_id'])) {
					$filter .= " and EPLS.DirectType_id = :DirectType_id";
					$queryParams['DirectType_id'] = $data['DirectType_id'];
				}

				// Результаты
				if (isset($data['EvnPL_IsFinish'])) {
					$filter .= " and EPLS.EvnPLStom_IsFinish = :EvnPLStom_IsFinish";
					$queryParams['EvnPLStom_IsFinish'] = $data['EvnPL_IsFinish'];
				}

				// Результаты
				if (isset($data['Lpu_oid'])) {
					$filter .= " and EPLS.Lpu_oid = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Результаты
				if (isset($data['LpuSection_oid'])) {
					$filter .= " and EPLS.LpuSection_oid = :LpuSection_oid";
					$queryParams['LpuSection_oid'] = $data['LpuSection_oid'];
				}

				// Результаты
				if (isset($data['ResultClass_id'])) {
					$filter .= " and EPLS.ResultClass_id = :ResultClass_id";
					$queryParams['ResultClass_id'] = $data['ResultClass_id'];
				}

				if (isset($data['StickCause_id']) || isset($data['StickType_id']) ||
						isset($data['EvnStick_begDate_Range'][0]) || isset($data['EvnStick_begDate_Range'][1]) ||
						isset($data['EvnStick_endDate_Range'][0]) || isset($data['EvnStick_endDate_Range'][1])
				) {
					$evn_stick_filter = '';

					// Результаты
					if (isset($data['EvnStick_begDate_Range'][0])) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
						$queryParams['EvnStick_begDate_Range_0'] = $data['EvnStick_begDate_Range'][0];
					}

					// Результаты
					if (isset($data['EvnStick_begDate_Range'][1])) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
						$queryParams['EvnStick_begDate_Range_1'] = $data['EvnStick_begDate_Range'][1];
					}

					// Результаты
					if (isset($data['EvnStick_endDate_Range'][0])) {
						$evn_stick_filter .= " and (
							case
								when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0 then 1
								when ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0) then 1
								else 0
							end = 1
						)";
						$queryParams['EvnStick_endDate_Range_0'] = $data['EvnStick_endDate_Range'][0];
					}

					// Результаты
					if (isset($data['EvnStick_endDate_Range'][1])) {
						$evn_stick_filter .= " and (
							case
								when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1 then 1
								when ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1) then 1
								else 0
							end = 1
						)";
						$queryParams['EvnStick_endDate_Range_1'] = $data['EvnStick_endDate_Range'][1];
					}

					// Результаты
					if (isset($data['StickCause_id'])) {
						$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
						$queryParams['StickCause_id'] = $data['StickCause_id'];
					}

					// Результаты
					if (isset($data['StickType_id'])) {
						$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
						$queryParams['StickType_id'] = $data['StickType_id'];
					}


					$filter .= "
						and exists (
							select EvnStickBase_id
							from v_EvnStickBase ESB with (nolock)
							where ESB.EvnStickBase_mid = EPLS.EvnPLStom_id
								" . $evn_stick_filter . "
							union all
							select Evn_id as EvnStickBase_id
							from v_EvnLink EL with (nolock)
								inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
							where EL.Evn_id = EPLS.EvnPLStom_id
								" . $evn_stick_filter . "
						)
					";
				}
				break;

			case 'EvnPS':
			case 'EvnPL':
			case 'EvnSection':
			case 'EvnDiag':
			case 'EvnLeave':
			case 'EvnStick':
			case 'KvsPerson':
			case 'KvsPersonCard':
			case 'KvsEvnDiag':
			case 'KvsEvnPS':
			case 'KvsEvnSection':
			case 'KvsNarrowBed':
			case 'KvsEvnUsluga':
			case 'KvsEvnUslugaOB':
			case 'KvsEvnUslugaAn':
			case 'KvsEvnUslugaOsl':
			case 'KvsEvnDrug':
			case 'KvsEvnLeave':
			case 'KvsEvnStick':
				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnPS EPS with (nolock) on EPS.Server_id = PS.Server_id and EPS.PersonEvn_id = PS.PersonEvn_id and EPS.Lpu_id " . $this->getLpuIdFilter($data);
				} else {
					$query .= " inner join v_EvnPS EPS with (nolock) on EPS.Person_id = PS.Person_id and EPS.Lpu_id " . $this->getLpuIdFilter($data);
					if (!empty($data['Person_citizen']) &&($data['Person_citizen']==3)) {
						//добавляем таблицу для поиска по гражданству, чтобы ссылаться на KLCountry_id из NationalityStatus, а не PS, так как PS меняется при изменениях адреса регистрации/проживания
						$query .= " left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = ps.NationalityStatus_id";
					}
				}

				// $query .= " left join PayType dbfpayt with(nolock) on dbfpayt.PayType_id = EPS.PayType_id ";

				if ($dbf === true) {
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							$query .= "
								left join v_Lpu as dbflpu with (nolock) on dbflpu.Lpu_id = EPS.Lpu_id
								left join PrehospArrive dbfpa (nolock) on dbfpa.PrehospArrive_id = EPS.PrehospArrive_id
								left join PrehospDirect dbfpd (nolock) on dbfpd.PrehospDirect_id = EPS.PrehospDirect_id
								left join PrehospToxic dbfpt (nolock) on dbfpt.PrehospToxic_id = EPS.PrehospToxic_id
								left join PayType dbfpayt (nolock) on dbfpayt.PayType_id = EPS.PayType_id
								left join PrehospTrauma dbfprtr (nolock) on dbfprtr.PrehospTrauma_id = EPS.PrehospTrauma_id
								left join PrehospType dbfprtype (nolock) on dbfprtype.PrehospType_id = EPS.PrehospType_id
								left join Org dbfdorg (nolock) on dbfdorg.Org_id = EPS.Org_did
								left join LpuSection dbflsd (nolock) on dbflsd.LpuSection_id = EPS.LpuSection_did
								left join Lpu dbfdlpu (nolock) on dbfdlpu.Lpu_id = EPS.Lpu_did
								left join Org dbfoorg (nolock) on dbfoorg.Org_id = dbfdlpu.Org_id
								left join v_MedPersonal dbfmp (nolock) on dbfmp.MedPersonal_id = EPS.MedPersonal_pid and dbfmp.Lpu_id = EPS.Lpu_id
								left join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id " . $this->getLpuIdFilter($data) . "
						
							";
							break;
						case 'EvnSection':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_LpuSection as dbfsec with (nolock) on dbfsec.LpuSection_id = ESEC.LpuSection_id
								
								left join v_PayType as dbfpay with (nolock) on dbfpay.PayType_id = ESEC.PayType_id
								left join v_TariffClass as dbftar with (nolock) on dbftar.TariffClass_id = ESEC.TariffClass_id
								outer apply (
									select top 1 *
									from v_MedPersonal with (nolock)
									where MedPersonal_id = EPS.MedPersonal_id
										and Lpu_id = EPS.Lpu_id
								) dbfmp
							";
							break;
						case 'EvnDiag':
							$query .= "
								left join v_EvnSection sect (nolock) on sect.EvnSection_pid = EPS.EvnPS_id and sect.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnLeave leav (nolock) on leav.EvnLeave_pid = EPS.EvnPS_id and leav.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_EvnDiagPS EDPS (nolock) on EDPS.EvnDiagPS_pid = EPS.EvnPS_id 
									or EDPS.EvnDiagPS_pid = sect.EvnSection_id
									or EDPS.EvnDiagPS_pid = leav.EvnLeave_id
								outer apply 
									GetMesForEvnDiagPS(
										EDPS.Diag_id,
										EDPS.Person_id,
										EPS.Lpu_id,
										EPS.LpuSection_did,
										EDPS.EvnDiagPS_setDate
									) as dbfmes
							";
							break;
						case 'EvnLeave':
							$query .= "
								left join v_EvnLeave ELV (nolock) on ELV.EvnLeave_pid = EPS.EvnPS_id and EPS.LeaveType_id = 1 and ELV.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherLpu dbfeol (nolock) on dbfeol.EvnOtherLpu_pid = EPS.EvnPS_id and EPS.LeaveType_id = 2 and dbfeol.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnDie dbfed (nolock) on dbfed.EvnDie_pid = EPS.EvnPS_id and EPS.LeaveType_id = 3 and dbfed.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherStac dbfeost (nolock) on dbfeost.EvnOtherStac_pid = EPS.EvnPS_id and EPS.LeaveType_id = 4 and dbfeost.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherSection dbfeos (nolock) on dbfeos.EvnOtherSection_pid = EPS.EvnPS_id and EPS.LeaveType_id = 5  and dbfeos.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherSectionBedProfile dbfeosbp (nolock) on dbfeosbp.EvnOtherSectionBedProfile_pid = EPS.EvnPS_id and EPS.LeaveType_id = 6  and dbfeosbp.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_LeaveType dbflt (nolock) on dbflt.LeaveType_id = EPS.LeaveType_id and dbflt.Lpu_id " . $this->getLpuIdFilter($data) . "
									and (
										ELV.EvnLeave_pid = EPS.EvnPS_id
										or dbfeol.EvnOtherLpu_pid = EPS.EvnPS_id
										or dbfed.EvnDie_pid = EPS.EvnPS_id
										or dbfeost.EvnOtherStac_pid = EPS.EvnPS_id
										or dbfeos.EvnOtherSection_pid = EPS.EvnPS_id
										or dbfeosbp.EvnOtherSectionBedProfile_pid = EPS.EvnPS_id
									)
								left join v_LpuSection dbfls (nolock) on dbfls.LpuSection_id = EPS.LpuSection_did
								left join v_LpuUnit dbflu (nolock) on dbflu.LpuUnit_id = dbfls.LpuUnit_id
							";
							break;
						case 'EvnStick':
							$query .= "
								inner join v_EvnStick EST (nolock) on EST.EvnStick_pid = EPS.EvnPS_id and EST.Lpu_id " . $this->getLpuIdFilter($data) . "
							";
							break;
						case 'KvsPerson':
							if ($PS_prefix == 'PS2') { //требуется использование дополнительной таблицы
								if ($data['kvs_date_type'] == 2) {
									$query .= "
										inner join v_Person_all PS2 with (nolock) on PS2.Server_id = EPS.Server_id and PS2.PersonEvn_id = EPS.PersonEvn_id
									";
								} else {
									$query .= "
										inner join v_PersonState PS2 with (nolock) on PS2.Person_id = EPS.Person_id
									";
								}
							}
							$query .= "
								left join Sex with (nolock) on Sex.Sex_id = {$PS_prefix}.Sex_id
								left join SocStatus Soc with (nolock) on Soc.SocStatus_id = {$PS_prefix}.SocStatus_id
								left join PersonChild PCh with (nolock) on PCh.Person_id = {$PS_prefix}.Person_id
								left join YesNo IsInv with (nolock) on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
								left join Diag InvD with (nolock) on InvD.Diag_id = PCh.Diag_id
								left join Polis Pol with (nolock) on Pol.Polis_id = {$PS_prefix}.Polis_id
								left join OMSSprTerr OMSST with (nolock) on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
								left join PolisType PolTp with (nolock) on PolTp.PolisType_id = Pol.PolisType_id
								left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Pol.OrgSmo_id
								left join v_Org OSO with (nolock) on OSO.Org_id = OS.Org_id
								left join v_Address_all UA with (nolock) on UA.Address_id = {$PS_prefix}.UAddress_id 
								left join v_Address_all PA with (nolock) on PA.Address_id = {$PS_prefix}.PAddress_id
								left join Document Doc with (nolock) on Doc.Document_id = {$PS_prefix}.Document_id 
								left join DocumentType DocTp with (nolock) on DocTp.DocumentType_id = Doc.DocumentType_id
								left join v_OrgDep OrgD with (nolock) on OrgD.OrgDep_id = Doc.OrgDep_id
								left join v_EvnSection EPSLastES2 with (nolock) on EPSLastES2.EvnSection_pid = EPS.EvnPS_id and EPSLastES2.EvnSection_Index = EPSLastES2.EvnSection_Count-1 and EPSLastES2.Lpu_id " . $this->getLpuIdFilter($data) . "
							";
							break;
						case 'KvsPersonCard':
							$query .= "
								inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
								left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
							";
							break;
						case 'KvsEvnDiag':
							$query .= "
								left join v_EvnSection ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_EvnDiagPS EDPS with (nolock) on EDPS.EvnDiagPS_pid = EPS.EvnPS_id 
									or EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
								left join DiagSetClass DSC with (nolock) on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
								left join DiagSetType DST with (nolock) on DST.DiagSetType_id = EDPS.DiagSetType_id 
								left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
								left join v_Lpu Lpu with (nolock) on (
									(Lpu.Lpu_id = EPS.Lpu_did and EDPS.DiagSetType_id = 1) or
									(Lpu.Lpu_id = EPS.Lpu_id and EDPS.DiagSetType_id = 2) or
									(Lpu.Lpu_id = ESEC.Lpu_id and EDPS.DiagSetType_id = 3)
								)
								left join v_LpuSection LS with (nolock) on (
									(LS.LpuSection_id = EPS.LpuSection_did and EDPS.DiagSetType_id = 1) or
									(LS.LpuSection_id = EPS.LpuSection_pid and EDPS.DiagSetType_id = 2) or
									(LS.LpuSection_id = ESEC.LpuSection_id and EDPS.DiagSetType_id = 3)
								)
							";
							break;
						case 'KvsEvnPS':
							$query .= "
								left join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join Diag with (nolock) on Diag.Diag_id = EPS.Diag_pid
								left join YesNo IsCont with (nolock) on IsCont.YesNo_id = EPS.EvnPS_IsCont
								left join PayType PT with (nolock) on PT.PayType_id = EPS.PayType_id
								left join PrehospDirect PD with (nolock) on PD.PrehospDirect_id = EPS.PrehospDirect_id
								left join LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_did
								left join Lpu with (nolock) on EPS.Lpu_did = Lpu.Lpu_id
								left join Org with (nolock) on coalesce(Lpu.Org_id, EPS.Org_did, EPS.OrgMilitary_did) = Org.Org_id
								/*
								outer apply (
									Select Org.Org_Code, Org.Org_Name from Org with (nolock)
									left join Lpu with (nolock) on Lpu.Org_id = Org.Org_id 
									where 
									coalesce(EPS.Org_did, EPS.OrgMilitary_did) = Org.Org_id
									or EPS.Lpu_did = Lpu.Lpu_id
								) Org
								*/
								left join PrehospArrive PA with (nolock) on PA.PrehospArrive_id = EPS.PrehospArrive_id
								left join Lpu LpuF with (nolock) on LpuF.Org_id = EPS.Org_did
								left join YesNo IsFond with (nolock) on IsFond.YesNo_id = LpuF.Lpu_IsOMS
								left join Diag DiagD with (nolock) on DiagD.Diag_id = EPS.Diag_did
								left join PrehospToxic Toxic with (nolock) on Toxic.PrehospToxic_id = EPS.PrehospToxic_id
								left join v_PrehospTrauma Trauma with (nolock) on Trauma.PrehospTrauma_id = EPS.PrehospTrauma_id
								left join PrehospType PType with (nolock) on PType.PrehospType_id = EPS.PrehospType_id
								left join YesNo IsUnlaw with (nolock) on IsUnlaw.YesNo_id = EPS.EvnPS_IsUnlaw
								left join YesNo IsUnport with (nolock) on IsUnport.YesNo_id = EPS.EvnPS_IsUnport
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPS.MedPersonal_pid
									and MP.Lpu_id = EPS.Lpu_id
								left join v_LpuSection pLS with (nolock) on pLS.LpuSection_id = EPS.LpuSection_pid
								left join YesNo IsImperHosp with (nolock) on IsImperHosp.YesNo_id = EPS.EvnPS_IsImperHosp
								left join YesNo IsShortVolume with (nolock) on IsShortVolume.YesNo_id = EPS.EvnPS_IsShortVolume
								left join YesNo IsWrongCure with (nolock) on IsWrongCure.YesNo_id = EPS.EvnPS_IsWrongCure
								left join YesNo IsDiagMismatch with (nolock) on IsDiagMismatch.YesNo_id = EPS.EvnPS_IsDiagMismatch
							";

							// #107998 Фильтр по признаку включения реестр
							if(isset($data['EvnPS_InRegistry'])){
								if(in_array($this->regionNick, array('ekb'))){
									if($data['EvnPS_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, не вошедшие в реестр');
										$filter .= " and (EPS.EvnPS_IsInReg = 1 or EPS.EvnPS_IsInReg is null)";
									}elseif($data['EvnPS_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, вошедшие в реестр');
										$filter .= " and EPS.EvnPS_IsInReg = 2";
									}
								}
								else
								if(in_array($this->regionNick, array('penza'))){
									if($data['EvnPS_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, в движении которых не установлен признак вхожденияе в реестр');
										$filter .= " AND NOT EXISTS (SELECT TOP 1 EvnSection_IsInReg FROM v_EvnSection ES WITH (nolock) WHERE ES.EvnSection_rid = EPS.EvnPS_id AND ES.EvnSection_IsInReg = 2)";
									}elseif($data['EvnPS_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, в движении которых установлен признак вхожденияе в реестр');
										$filter .= " AND EXISTS (SELECT TOP 1 EvnSection_IsInReg FROM v_EvnSection ES WITH (nolock) WHERE ES.EvnSection_rid = EPS.EvnPS_id AND ES.EvnSection_IsInReg = 2)";
									}
								}
							}
							break;
						case 'KvsEvnSection':
							//Добавил inner join v_LpuSectionProfile LSProf with (nolock) on (LSProf.LpuSectionProfile_id = LS.LpuSectionProfile_id and LSProf.LpuSectionProfile_SysNick <> 'priem')
							//для задачи https://redmine.swan.perm.ru/issues/65234 - т.е. исключаем данные о движении пациента в приемном отделении
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ESEC.LpuSection_id
								inner join v_LpuSectionProfile LSProf with (nolock) on (LSProf.LpuSectionProfile_id = LS.LpuSectionProfile_id and LSProf.LpuSectionProfile_SysNick <> 'priem')
								left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично 
									-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1 with (nolock) where LS1.LpuSection_id = LS.LpuSection_pid)
								left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								outer apply (
									select top 1 *
									from v_MedPersonal with (nolock)
									where MedPersonal_id = ESEC.MedPersonal_id
										and Lpu_id = EPS.Lpu_id
								) MP
								left join Diag Diag with (nolock) on Diag.Diag_id = ESEC.Diag_id
								left join PayType PT with (nolock) on PT.PayType_id = ESEC.PayType_id
								left join TariffClass TC with (nolock) on TC.TariffClass_id = ESEC.TariffClass_id
								left join v_MesOld Mes with (nolock) on Mes.Mes_id = ESEC.Mes_id
							";
							break;
						case 'KvsNarrowBed':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_EvnSectionNarrowBed as ESNB with (nolock) on ESNB.EvnSectionNarrowBed_pid = ESEC.EvnSection_id and ESNB.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join LpuSection LS with (nolock) on LS.LpuSection_id = ESNB.LpuSection_id
							";
							break;
						case 'KvsEvnUsluga':
							$query .= "
								inner join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
								left join OperType OT with (nolock) on OT.OperType_id = EUO.OperType_id
								left join OperDiff OD with (nolock) on OD.OperDiff_id = EUO.OperDiff_id
								left join YesNo IsEndoskop with (nolock) on IsEndoskop.YesNo_id = EUO.EvnUslugaOper_IsEndoskop
								left join YesNo IsLazer with (nolock) on IsLazer.YesNo_id = EUO.EvnUslugaOper_IsLazer
								left join YesNo IsKriogen with (nolock) on IsKriogen.YesNo_id = EUO.EvnUslugaOper_IsKriogen
								left join v_PayType as PT with (nolock) on PT.PayType_id = EU.PayType_id
								left join v_UslugaComplex as U with (nolock) on U.UslugaComplex_id = EU.UslugaComplex_id
								left join v_UslugaPlace as UP with (nolock) on UP.UslugaPlace_id = EU.UslugaPlace_id
								outer apply (
									select top 1 MedPersonal_TabCode, Person_Fio
									from v_MedPersonal with (nolock)
									where MedPersonal_id = EU.MedPersonal_id
										and Lpu_id = EPS.Lpu_id
								) MP
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EU.LpuSection_uid
								left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EU.Lpu_uid
								left join v_Org Org with (nolock) on Org.Org_id = EU.Org_uid
								left join v_UslugaClass UC with (nolock) on UC.UslugaClass_SysNick = EU.EvnClass_SysNick
							";
							break;
						case 'KvsEvnUslugaOB':
							$query .= "
								left join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
								inner join v_EvnUslugaOperBrig EUOB with (nolock) on EUOB.EvnUslugaOper_id = EUO.EvnUslugaOper_id
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EUOB.MedPersonal_id
									and MP.Lpu_id = EPS.Lpu_id
								left join v_SurgType ST with (nolock) on ST.SurgType_id = EUOB.SurgType_id
							";
							break;
						case 'KvsEvnUslugaAn':
							$query .= "
								left join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
								inner join v_EvnUslugaOperAnest EUOA with (nolock) on EUOA.EvnUslugaOper_id = EUO.EvnUslugaOper_id
								left join v_AnesthesiaClass AC with (nolock) on AC.AnesthesiaClass_id = EUOA.AnesthesiaClass_id
							";
							break;
						case 'KvsEvnUslugaOsl':
							$query .= "
								left join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_EvnAgg EA with (nolock) on EA.EvnAgg_pid = EU.EvnUsluga_id and EA.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_AggType AT with (nolock) on AT.AggType_id = EA.AggType_id
								left join v_AggWhen AW with (nolock) on AW.AggWhen_id = EA.AggWhen_id
							";
							break;
						case 'KvsEvnDrug':
							$query .= "
								inner join v_EvnDrug as ED with (nolock) on ED.EvnDrug_rid = EPS.EvnPS_id and ED.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_DocumentUcOstat_Lite Part with (nolock) on Part.DocumentUcStr_id = ED.DocumentUcStr_oid
								inner join DocumentUcStr DUS with (nolock) on DUS.DocumentUcStr_id = ED.DocumentUcStr_id
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
								left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
								left join v_Mol Mol with (nolock) on Mol.Mol_id = ED.Mol_id
							";
							break;
						case 'KvsEvnLeave':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnLeave ELV with (nolock) on ELV.EvnLeave_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 1 and ELV.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherLpu EOLpu with (nolock) on EOLpu.EvnOtherLpu_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 2 and EOLpu.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnDie EDie with (nolock) on EDie.EvnDie_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 3 and EDie.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherStac EOStac with (nolock) on EOStac.EvnOtherStac_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 4 and EOStac.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherSection EOSect with (nolock) on EOSect.EvnOtherSection_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 5 and EOSect.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnOtherSectionBedProfile EOSectBP with (nolock) on EOSectBP.EvnOtherSectionBedProfile_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 6 and EOSectBP.Lpu_id " . $this->getLpuIdFilter($data) . "
								inner join v_LeaveType LType with (nolock) on LType.LeaveType_id = ESEC.LeaveType_id 
									and (
										ELV.EvnLeave_pid = ESEC.EvnSection_id
										or EOLpu.EvnOtherLpu_pid = ESEC.EvnSection_id
										or EDie.EvnDie_pid = ESEC.EvnSection_id
										or EOStac.EvnOtherStac_pid = ESEC.EvnSection_id
										or EOSect.EvnOtherSection_pid = ESEC.EvnSection_id
										or EOSectBP.EvnOtherSectionBedProfile_pid = ESEC.EvnSection_id
									)									
								left join ResultDesease RD with (nolock) on (
									RD.ResultDesease_id = ELV.ResultDesease_id or
									RD.ResultDesease_id = EOLpu.ResultDesease_id or
									RD.ResultDesease_id = EOStac.ResultDesease_id or
									RD.ResultDesease_id = EOSect.ResultDesease_id
								)
								left join LeaveCause LC with (nolock) on (
									LC.LeaveCause_id = ELV.LeaveCause_id or
									LC.LeaveCause_id = EOLpu.LeaveCause_id or
									LC.LeaveCause_id = EOStac.LeaveCause_id or
									LC.LeaveCause_id = EOSect.LeaveCause_id
								)
								left join YesNo IsAmbul with (nolock) on IsAmbul.YesNo_id = ELV.EvnLeave_IsAmbul
								left join v_Org EOLpuL with (nolock) on EOLpuL.Org_id = EOLpu.Org_oid
								left join v_LpuUnitType EOStacLUT with (nolock) on EOStacLUT.LpuUnitType_id = EOStac.LpuUnitType_oid
								left join v_LpuSection LS with (nolock) on (
									LS.LpuSection_id = EOStac.LpuSection_oid or
									LS.LpuSection_id = EOSect.LpuSection_oid or
									LS.LpuSection_id = EOSectBP.LpuSection_oid
								)
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDie.MedPersonal_aid
									and MP.Lpu_id = EPS.Lpu_id
								left join Diag DieDiag with (nolock) on DieDiag.Diag_id = EDie.Diag_aid
							";
							break;
						case 'KvsEvnStick':
							$query .= "
								inner join v_EvnStick EST with (nolock) on EST.EvnStick_pid = EPS.EvnPS_id and EST.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join StickOrder SO with (nolock) on SO.StickOrder_id = EST.StickOrder_id
								left join StickCause SC with (nolock) on SC.StickCause_id = EST.StickCause_id
								left join Sex with (nolock) on Sex.Sex_id = EST.Sex_id
								left join StickRegime SR with (nolock) on SR.StickRegime_id = EST.StickRegime_id
								left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = EST.StickLeaveType_id
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EST.MedPersonal_id and MP.Lpu_id = EST.Lpu_id
								left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EST.Lpu_id
								left join Diag D1 with (nolock) on D1.Diag_id = EST.Diag_pid
								-- left join Diag D2 with (nolock) on D2.Diag_id = EST.Diag_id
							";
							break;
					}

					if (isset($data['and_kvsperson']) && $data['and_kvsperson']) { //при комбинировании с таблицой персон добавляем дополнительные поля
						if ($PS_prefix == 'PS2') { //требуется использование дополнительной таблицы
							if ($data['kvs_date_type'] == 2) {
								$query .= "
									left join v_Person_all PS2 with (nolock) on PS2.Server_id = EPS.Server_id and PS2.PersonEvn_id = EPS.PersonEvn_id
								";
							} else {
								$query .= "
									left join v_PersonState PS2 with (nolock) on PS2.Person_id = EPS.Person_id
								";
							}
						}
						$query .= "
							left join Sex PrsSex with (nolock) on PrsSex.Sex_id = {$PS_prefix}.Sex_id
							left join SocStatus PrsSoc with (nolock) on PrsSoc.SocStatus_id = {$PS_prefix}.SocStatus_id
							left join PersonChild PrsPCh with (nolock) on PrsPCh.Person_id = {$PS_prefix}.Person_id
							left join YesNo PrsIsInv with (nolock) on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
							left join Diag PrsInvD with (nolock) on PrsInvD.Diag_id = PrsPCh.Diag_id
							left join Polis PrsPol with (nolock) on PrsPol.Polis_id = {$PS_prefix}.Polis_id
							left join OMSSprTerr PrsOMSST with (nolock) on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
							left join PolisType PrsPolTp with (nolock) on PrsPolTp.PolisType_id = PrsPol.PolisType_id
							left join v_OrgSmo PrsOS with (nolock) on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
							left join v_Org PrsOSO with (nolock) on PrsOSO.Org_id = PrsOS.Org_id
							left join v_Address_all PrsUA with (nolock) on PrsUA.Address_id = {$PS_prefix}.UAddress_id 
							left join v_Address_all PrsPA with (nolock) on PrsPA.Address_id = {$PS_prefix}.PAddress_id
							left join Document PrsDoc with (nolock) on PrsDoc.Document_id = {$PS_prefix}.Document_id 
							left join DocumentType PrsDocTp with (nolock) on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
							left join v_OrgDep PrsOrgD with (nolock) on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
						";
					}
				} else {
					// Обычный поиск, не выгрузка DBF
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							$query .= "
								left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPS.EvnPS_id
								-- этот джойн не нужен видимо (с) Night, 2011-06-22 
								--left join v_Lpu LpuD with (nolock) on LpuD.Lpu_id = EPS.Lpu_did
								-- Тарас сказал что Index и Count - это почти всегда правильно (с) Night, 2011-06-22 
								left join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join LpuSection LStmp with (nolock) on LStmp.LpuSection_id = EPSLastES.LpuSection_id
								left join v_Diag Dtmp with (nolock) on Dtmp.Diag_id = EPSLastES.Diag_id
								left join LeaveType LT with (nolock) on LT.LeaveType_id = ISNULL(EPSLastES.LeaveType_id, EPSLastES.LeaveType_prmid)
								outer apply (
									select top 1 *
									from v_MedPersonal with (nolock)
									where MedPersonal_id = EPSLastES.MedPersonal_id
										and Lpu_id " . $this->getLpuIdFilter($data) . "
									order by
										case when Lpu_id = :Lpu_id then 1 else 2 end
								) MP
								left join v_Diag DP with (nolock) on DP.Diag_id = EPS.Diag_pid
								left join LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_id
								left join v_PrehospWaifRefuseCause pwrc with(nolock) on pwrc.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
								left join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LStmp.LpuUnit_id 
								left join LpuUnitType with (nolock) on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
								left join PayType dbfpayt (nolock) on dbfpayt.PayType_id = EPS.PayType_id
								left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
								outer apply (
									select top 1 EvnDie_setDate
									from v_EvnDie with (nolock)
									where Person_id = PS.Person_id
									order by EvnDie_setDate
								) as EvnDie
								outer apply(
									SELECT TOP 1 DeathSvid_id,DeathSvid_DeathDate 
									FROM dbo.v_DeathSvid WITH (NOLOCK) 
									WHERE Person_id = PS.Person_id and (DeathSvid_IsBad is null or DeathSvid_IsBad = 1)
								) as DeathSvid
								left join v_MesTariff spmt (nolock) on EPSLastES.MesTariff_id = spmt.MesTariff_id
								left join v_MesOld as sksg with (nolock) on sksg.Mes_id = EPSLastES.Mes_sid
								left join v_MesOld as ksg with (nolock) on ksg.Mes_id = case
									when spmt.Mes_id in (EPSLastES.Mes_sid, EPSLastES.Mes_tid) then spmt.Mes_id
									else isnull(EPSLastES.Mes_sid, EPSLastES.Mes_tid)
								end
								left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
								left join v_MesOld as kpg with (nolock) on kpg.Mes_id = EPSLastES.Mes_kid
								" . ($this->regionNick == 'kz' ? "left join r101.v_EvnPSLink epsl (nolock) on epsl.EvnPS_id = EPS.EvnPS_id left join ObjectSynchronLog objsync with (nolock) on EPS.EvnPS_id=objsync.Object_id" : "") . "
								
								
							";
							if(!empty($data['EvnReanimatPeriod_setDate']) || !empty($data['EvnReanimatPeriod_disDate'])){
								$query .= "
								left join v_EvnReanimatPeriod ERP with (nolock) on ERP.EvnReanimatPeriod_rid = EPS.EvnPS_id
								";
							}
							if (isset($data['toERSB']) and $data['toERSB'] != 0 and $this->regionNick == 'kz')
								$filter .= " AND " . ($data['toERSB'] == 2 ? "NOT " : "") . "EXISTS (
									SELECT TOP 1 osl.ObjectSynchronLog_id FROM ObjectSynchronLog osl WITH (NOLOCK) WHERE osl.ObjectSynchronLogService_id = 6 AND osl.Object_id = eps.EvnPS_id
								) ";
							$acDiagFilter = getRevertAccessRightsDiagFilter("ESSDiag.Diag_Code");
							if (!empty($acDiagFilter)) {
								$accessDiagFilter = " outer apply (
									select top 1 ESS.EvnSection_id
									from v_EvnSection ESS with (nolock)
									inner join v_Diag ESSDiag with (nolock) on ESSDiag.Diag_id = ESS.Diag_id
									where ESS.EvnSection_pid = EPS.EvnPS_id
										and ESS.Lpu_id " . $this->getLpuIdFilter($data) . "
								";
								$accessDiagFilter .= " and ($acDiagFilter)";
								$accessDiagFilter .= ") adf";
								$query .= $accessDiagFilter;
								$filter .= " and adf.EvnSection_id is null";
							}
							break;

						case 'EvnSection':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id " . $this->getLpuIdFilter($data) . "
								left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPS.EvnPS_id
								left join v_Diag Dtmp with (nolock) on Dtmp.Diag_id = ESEC.Diag_id
								left join LpuSection LS with (nolock) on LS.LpuSection_id = ESEC.LpuSection_id
								inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
								inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ESEC.LpuSectionWard_id
								left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
								left join PayType as PT with (nolock) on PT.PayType_id = ESEC.PayType_id
								outer apply (
									select top 1 Person_Fio
									from v_MedPersonal with (nolock)
									where MedPersonal_id = ESEC.MedPersonal_id
								) MP
								left join v_MesOld as MES with (nolock) on MES.Mes_id = ESEC.Mes_id
								left join LeaveType LT with (nolock) on LT.LeaveType_id = ESEC.LeaveType_id
								left join v_MesTariff spmt (nolock) on ESEC.MesTariff_id = spmt.MesTariff_id
								left join v_MesOld as sksg with (nolock) on sksg.Mes_id = ESEC.Mes_sid
								left join v_MesOld as ksg with (nolock) on ksg.Mes_id = case
									when spmt.Mes_id in (ESEC.Mes_sid, ESEC.Mes_tid) then spmt.Mes_id
									else isnull(ESEC.Mes_sid, ESEC.Mes_tid)
								end
								left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
								left join v_MesOld as kpg with (nolock) on kpg.Mes_id = ESEC.Mes_kid
							";
							$acDiagFilter = getRevertAccessRightsDiagFilter("ESSDiag.Diag_Code");
							if (!empty($acDiagFilter)) {
								$accessDiagFilter = " outer apply (
									select top 1 ESS.EvnSection_id
									from v_EvnSection ESS with (nolock)
									inner join v_Diag ESSDiag with (nolock) on ESSDiag.Diag_id = ESS.Diag_id
									where ESS.EvnSection_pid = EPS.EvnPS_id
										and ESS.Lpu_id " . $this->getLpuIdFilter($data) . "
								";
								$accessDiagFilter .= " and ($acDiagFilter)";
								$accessDiagFilter .= ") adf";
								$query .= $accessDiagFilter;
								$filter .= " and adf.EvnSection_id is null";
							}
							break;
					}

					if ($data['PersonPeriodicType_id'] == 3) {
						$query .= "
							outer apply (
								select top 1 PersonEvn_id, Server_id
								from v_PersonState with (nolock)
								where Person_id = PS.Person_id
							) CPS
						";

						switch ($data['SearchFormType']) {
							case 'EvnPS':
								$filter .= "
									and PS.PersonEvn_id = ISNULL(EPSLastES.PersonEvn_id, CPS.PersonEvn_id)
									and PS.Server_id = ISNULL(EPSLastES.Server_id, CPS.Server_id)
								";
								break;

							case 'EvnSection':
								$filter .= "
									and PS.PersonEvn_id = ISNULL(ESEC.PersonEvn_id, CPS.PersonEvn_id)
									and PS.Server_id = ISNULL(ESEC.Server_id, CPS.Server_id)
								";
								break;
						}
					}
				}

				// #107998 Фильтр по признаку включения реестр
				switch ($data['SearchFormType']) {
					case 'EvnPS':
						if(isset($data['EvnPS_InRegistry'])){
							if(in_array($this->regionNick, array('ekb'))){
								if($data['EvnPS_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, не вошедшие в реестр');
									$filter .= " and (EPS.EvnPS_IsInReg = 1 or EPS.EvnPS_IsInReg is null)";
								}elseif($data['EvnPS_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, вошедшие в реестр');
									$filter .= " and EPS.EvnPS_IsInReg = 2";
								}
							}
							elseif(in_array($this->regionNick, array('penza'))){
								if($data['EvnPS_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, в движении которых не установлен признак вхожденияе в реестр');
									$filter .= " AND NOT EXISTS (SELECT TOP 1 EvnSection_IsInReg FROM v_EvnSection ES WITH (nolock) WHERE ES.EvnSection_rid = EPS.EvnPS_id AND ES.EvnSection_IsInReg = 2)";
								}elseif($data['EvnPS_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, в движении которых установлен признак вхожденияе в реестр');
									$filter .= " AND EXISTS (SELECT TOP 1 EvnSection_IsInReg FROM v_EvnSection ES WITH (nolock) WHERE ES.EvnSection_rid = EPS.EvnPS_id AND ES.EvnSection_IsInReg = 2)";
								}
							}
						}
						break;
					case 'EvnSection':
						if(isset($data['EvnPS_InRegistry'])){
							if(in_array($this->regionNick, array('ekb'))){
								if($data['EvnPS_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, не вошедшие в реестр');
									$filter .= " and (EPS.EvnPS_IsInReg = 1 or EPS.EvnPS_IsInReg is null)";
								}elseif($data['EvnPS_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, вошедшие в реестр');
									$filter .= " and EPS.EvnPS_IsInReg = 2";
								}
							}
							elseif(in_array($this->regionNick, array('penza'))){
								if($data['EvnPS_InRegistry'] == 1){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, в движении которых не установлен признак вхожденияе в реестр');
									$filter .= " and (ESEC.EvnSection_IsInReg = 1 or ESEC.EvnSection_IsInReg is null)";
								}elseif($data['EvnPS_InRegistry'] == 2){// log_message('error', __LINE__.' region: '.$this->regionNick.' Только КВС, в движении которы установлен признак вхождения в реестр');
									$filter .= " and ESEC.EvnSection_IsInReg = 2";
								}
							}
						}
						break;
				}

				// $query .= " inner join v_EvnPS EPS with(nolock) on EPS.Person_id = PS.Person_id and EPS.Lpu_id = :Lpu_id";
				// Госпитализация
				if (isset($data['EvnDirection_Num'])) {
					$filter .= " and EPS.EvnDirection_Num = :EvnDirection_Num";
					$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
				}

				// Сопровождается взрослым
				if (isset($data['EvnSection_IsAdultEscort'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							switch ($data['EvnSection_IsAdultEscort']) {
								case 2:
									$filter .= " and EPSLastES.EvnSection_IsAdultEscort = 2";
									break;
								case 1:
									$filter .= " and (EPSLastES.EvnSection_IsAdultEscort = 1 or EPSLastES.EvnSection_IsAdultEscort is null)";
									break;
							}
							break;
						case 'EvnSection':
							switch ($data['EvnSection_IsAdultEscort']) {
								case 2:
									$filter .= " and ESEC.EvnSection_IsAdultEscort = 2";
									break;
								case 1:
									$filter .= " and (ESEC.EvnSection_IsAdultEscort = 1 or ESEC.EvnSection_IsAdultEscort is null)";
									break;
							}
							break;
					}
				}

				// Госпитализация
				if (isset($data['EvnDirection_setDate_Range'][0])) {
					$filter .= " and EPS.EvnDirection_setDT >= cast(:EvnDirection_setDate_Range_0 as datetime)";
					$queryParams['EvnDirection_setDate_Range_0'] = $data['EvnDirection_setDate_Range'][0];
				}

				// Госпитализация
				if (isset($data['EvnDirection_setDate_Range'][1])) {
					$filter .= " and EPS.EvnDirection_setDT <= cast(:EvnDirection_setDate_Range_1 as datetime)";
					$queryParams['EvnDirection_setDate_Range_1'] = $data['EvnDirection_setDate_Range'][1];
				}

				// Приемное и госпитализация
				if (!empty($data['Hospitalization_id']) && $this->regionNick == 'kz') {
					switch ($data['Hospitalization_id']) {
						case 1:
							$filter .= " and epsl.Hospitalization_id IS NULL";
							break;
						case 2:
							$filter .= " and epsl.Hospitalization_id IS NOT NULL";
							break;
					}
				}

				// Госпитализация
				if (isset($data['EvnPS_disDate_Range'][0])) {
					$stats_hour = "+9 hours";
					if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
					$new_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_disDate_Range'][0])));
					if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
						$temp_date = date('Y-m-d H:i:s', strtotime("-1 days", strtotime($new_date)));
					else
						$temp_date = $new_date;

					switch ($data['SearchFormType']) {
						case 'EvnPS':
						case 'KvsEvnPS':
							if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
								$filter .= " and EPSLastES.EvnSection_disDT >= :EvnPS_disDate_Range_0";
								$queryParams['EvnPS_disDate_Range_0'] = $temp_date; //$new_date;
							} else {
								$filter .= " and EPSLastES.EvnSection_disDate >= :EvnPS_disDate_Range_0";
								$queryParams['EvnPS_disDate_Range_0'] = $data['EvnPS_disDate_Range'][0];
							}
							break;
						case 'KvsPerson':
							if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
								$filter .= " and EPSLastES2.EvnSection_disDT >= :EvnPS_disDate_Range_0";
								$queryParams['EvnPS_disDate_Range_0'] = $temp_date; //$new_date;
							} else {
								$filter .= " and EPSLastES2.EvnSection_disDate >= :EvnPS_disDate_Range_0";
								$queryParams['EvnPS_disDate_Range_0'] = $data['EvnPS_disDate_Range'][0];
							}
							break;
						case 'EvnSection':
						case 'KvsEvnSection':
							if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
								$filter .= " and ESEC.EvnSection_disDT >= :EvnPS_disDate_Range_0";
								$queryParams['EvnPS_disDate_Range_0'] = $temp_date; //$new_date;
							} else {
								$filter .= " and ESEC.EvnSection_disDate >= :EvnPS_disDate_Range_0";
								$queryParams['EvnPS_disDate_Range_0'] = $data['EvnPS_disDate_Range'][0];
							}
							break;
					}
				}

				// Госпитализация
				if (isset($data['EvnPS_disDate_Range'][1])) {
					$stats_hour = "+9 hours";
					if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
					$new_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_disDate_Range'][1])));
					if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
						$temp_date = $new_date;
					else
						$temp_date = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($new_date)));

					switch ($data['SearchFormType']) {
						case 'EvnPS':
						case 'KvsEvnPS':
							if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
								$filter .= " and EPSLastES.EvnSection_disDT < :EvnPS_disDate_Range_1";
								$queryParams['EvnPS_disDate_Range_1'] = $temp_date;
							} else {
								$filter .= " and EPSLastES.EvnSection_disDate <= :EvnPS_disDate_Range_1";
								$queryParams['EvnPS_disDate_Range_1'] = $data['EvnPS_disDate_Range'][1];
							}
							break;
						case 'KvsPerson':
							if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
								$filter .= " and EPSLastES2.EvnSection_disDT <= :EvnPS_disDate_Range_1";
								$queryParams['EvnPS_disDate_Range_1'] = $temp_date;
							} else {
								$filter .= " and EPSLastES2.EvnSection_disDate <= :EvnPS_disDate_Range_1";
								$queryParams['EvnPS_disDate_Range_1'] = $data['EvnPS_disDate_Range'][1];
							}
							break;
						case 'EvnSection':
						case 'KvsEvnSection':
							if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
								$filter .= " and ESEC.EvnSection_disDT <= :EvnPS_disDate_Range_1";
								$queryParams['EvnPS_disDate_Range_1'] = $temp_date;
							} else {
								$filter .= " and ESEC.EvnSection_disDate <= :EvnPS_disDate_Range_1";
								$queryParams['EvnPS_disDate_Range_1'] = $data['EvnPS_disDate_Range'][1];
							}
							break;
					}
				}

				// Госпитализация
				if (isset($data['EvnPS_HospCount_Max'])) {
					$filter .= " and EPS.EvnPS_HospCount <= :EvnPS_HospCount_Max";
					$queryParams['EvnPS_HospCount_Max'] = $data['EvnPS_HospCount_Max'];
				}

				// Госпитализация
				if (isset($data['EvnPS_HospCount_Min'])) {
					$filter .= " and EPS.EvnPS_HospCount >= :EvnPS_HospCount_Min";
					$queryParams['EvnPS_HospCount_Min'] = $data['EvnPS_HospCount_Min'];
				}

				// Госпитализация
				if (isset($data['EvnPS_IsUnlaw'])) {
					$filter .= " and EPS.EvnPS_IsUnlaw = :EvnPS_IsUnlaw";
					$queryParams['EvnPS_IsUnlaw'] = $data['EvnPS_IsUnlaw'];
				}

				// Госпитализация
				if (isset($data['EvnPS_IsUnport'])) {
					$filter .= " and EPS.EvnPS_IsUnport = :EvnPS_IsUnport";
					$queryParams['EvnPS_IsUnport'] = $data['EvnPS_IsUnport'];
				}

				// Форма помощи
				if (isset($data['MedicalCareFormType_id'])) {
					$filter .= " and EPS.MedicalCareFormType_id = :MedicalCareFormType_id";
					$queryParams['MedicalCareFormType_id'] = $data['MedicalCareFormType_id'];
				}

				// Госпитализация
				if (!empty($data['EvnPS_IsWithoutDirection'])) {
					$filter .= " and ISNULL(EvnDirection_id, 0) = 0";
				}

				// Госпитализация
				if (isset($data['EvnPS_NumCard'])) {
					$filter .= " and EPS.EvnPS_NumCard = :EvnPS_NumCard";
					$queryParams['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
				}

				if (isset($data['EvnSection_insideNumCard'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							$filter .= " and EPSLastES.EvnSection_insideNumCard = :EvnSection_insideNumCard";
							$queryParams['EvnSection_insideNumCard'] = $data['EvnSection_insideNumCard'];
							break;

						case 'EvnSection':
							$filter .= " and ESEC.EvnSection_insideNumCard = :EvnSection_insideNumCard";
							$queryParams['EvnSection_insideNumCard'] = $data['EvnSection_insideNumCard'];
							break;
					}
				}

				// Госпитализация
				if (isset($data['EvnPS_setDate_Range'][0])) {
					//$filter .= " and EPS.EvnPS_setDate >= :EvnPS_setDate_Range_0";
					$stats_hour = "+9 hours";
					if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
					$new_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_setDate_Range'][0])));
					if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
						$temp_date = date('Y-m-d H:i:s', strtotime("-1 days", strtotime($new_date)));
					else
						$temp_date = $new_date;
					if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
						$filter .= " and EPS.EvnPS_setDT >= :EvnPS_setDate_Range_0";
						$queryParams['EvnPS_setDate_Range_0'] = $temp_date; //$new_date;
					} else {
						$filter .= " and EPS.EvnPS_setDate >= :EvnPS_setDate_Range_0";
						$queryParams['EvnPS_setDate_Range_0'] = $data['EvnPS_setDate_Range'][0];
					}
				}

				// Госпитализация
				if (isset($data['EvnPS_setDate_Range'][1])) {
					//$filter .= " and EPS.EvnPS_setDate <= :EvnPS_setDate_Range_1";
					$stats_hour = "+9 hours";
					if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
					$temp_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_setDate_Range'][1])));
					if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
						$new_date = $temp_date;
					else
						$new_date = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($temp_date)));
					if (isset($data['Date_Type']) && $data['Date_Type'] == '2') {
						$filter .= " and EPS.EvnPS_setDT < :EvnPS_setDate_Range_1";
						$queryParams['EvnPS_setDate_Range_1'] = $new_date;
					} else {
						$filter .= " and EPS.EvnPS_setDate <= :EvnPS_setDate_Range_1";
						$queryParams['EvnPS_setDate_Range_1'] = $data['EvnPS_setDate_Range'][1];
					}
				}

				if(!empty($data['EvnReanimatPeriod_setDate'][0])){
					$filter .= " and ERP.EvnReanimatPeriod_setDate >= :EvnReanimatPeriod_setDate_Start";
					$queryParams['EvnReanimatPeriod_setDate_Start'] = $data['EvnReanimatPeriod_setDate'][0];
				}

				if(!empty($data['EvnReanimatPeriod_setDate'][1])){
					$filter .= " and ERP.EvnReanimatPeriod_setDate <= :EvnReanimatPeriod_setDate_End";
					$queryParams['EvnReanimatPeriod_setDate_End'] = $data['EvnReanimatPeriod_setDate'][1];
				}

				if(!empty($data['EvnReanimatPeriod_disDate'][0])){
					$filter .= " and ERP.EvnReanimatPeriod_disDate >= :EvnReanimatPeriod_disDate_Start";
					$queryParams['EvnReanimatPeriod_disDate_Start'] = $data['EvnReanimatPeriod_disDate'][0];
				}

				if(!empty($data['EvnReanimatPeriod_disDate'][1])){
					$filter .= " and ERP.EvnReanimatPeriod_disDate <= :EvnReanimatPeriod_disDate_End";
					$queryParams['EvnReanimatPeriod_disDate_End'] = $data['EvnReanimatPeriod_disDate'][1];
				}

				// Госпитализация
				if (isset($data['Lpu_IsFondHolder'])) {
					$filter .= " and exists(select top 1 1 from LpuFondHolder with (nolock) where Lpu_id = EPS.Lpu_did and ISNULL(LpuFondHolder_IsEnabled, 2) = :Lpu_IsFondHolder)";
					$queryParams['Lpu_IsFondHolder'] = $data['Lpu_IsFondHolder'];
				}

				// Госпитализация
				if (isset($data['LpuSection_did'])) {
					$filter .= " and EPS.LpuSection_did = :LpuSection_did";
					$queryParams['LpuSection_did'] = $data['LpuSection_did'];
				}

				// Госпитализация
				if (isset($data['Lpu_did'])) {
					$filter .= " and EPS.Lpu_did = :Lpu_did";
					$queryParams['Lpu_did'] = $data['Lpu_did'];
				} else if (isset($data['OrgMilitary_did'])) {
					$filter .= " and EPS.OrgMilitary_did = :OrgMilitary_did";
					$queryParams['OrgMilitary_did'] = $data['OrgMilitary_did'];
				} else if (isset($data['Org_did'])) {
					$filter .= " and EPS.Org_did = :Org_did";
					$queryParams['Org_did'] = $data['Org_did'];
				}

				// Госпитализация
				if (isset($data['PayType_id'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							$filter .= " and EPS.PayType_id = :PayType_id";
							break;

						case 'EvnSection':
							$filter .= " and ESEC.PayType_id = :PayType_id";
							break;
					}

					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Госпитализация
				if (isset($data['PrehospArrive_id'])) {
					$filter .= " and EPS.PrehospArrive_id = :PrehospArrive_id";
					$queryParams['PrehospArrive_id'] = $data['PrehospArrive_id'];
				}

				// Госпитализация
				if (isset($data['PrehospDirect_id'])) {
					if ($data['PrehospDirect_id'] > 0) {
						$filter .= " and EPS.PrehospDirect_id = :PrehospDirect_id";
						$queryParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
					} else if ($data['PrehospDirect_id'] == -1) {
						$filter .= " and EPS.PrehospDirect_id is null";
					}
				}

				// Госпитализация
				if (isset($data['PrehospToxic_id'])) {
					$filter .= " and EPS.PrehospToxic_id = :PrehospToxic_id";
					$queryParams['PrehospToxic_id'] = $data['PrehospToxic_id'];
				}

				// Госпитализация
				if (isset($data['PrehospTrauma_id'])) {
					$filter .= " and EPS.PrehospTrauma_id = :PrehospTrauma_id";
					$queryParams['PrehospTrauma_id'] = $data['PrehospTrauma_id'];
				}

				// Госпитализация
				if (isset($data['PrehospType_id'])) {
					$filter .= " and EPS.PrehospType_id = :PrehospType_id";
					$queryParams['PrehospType_id'] = $data['PrehospType_id'];
				}

				// Госпитализация
				if (isset($data['EvnPS_TimeDesease_Max'])) {
					$filter .= " and EPS.EvnPS_TimeDesease <= :EvnPS_TimeDesease_Max";
					$queryParams['EvnPS_TimeDesease_Max'] = $data['EvnPS_TimeDesease_Max'];
				}

				// Госпитализация
				if (isset($data['EvnPS_TimeDesease_Min'])) {
					$filter .= " and EPS.EvnPS_TimeDesease >= :EvnPS_TimeDesease_Min";
					$queryParams['EvnPS_TimeDesease_Min'] = $data['EvnPS_TimeDesease_Min'];
				}

				// Госпитализация
				if (isset($data['LpuSection_hid'])) {
					$filter .= " and EPS.LpuSection_id = :LpuSection_hid";
					$queryParams['LpuSection_hid'] = $data['LpuSection_hid'];
				}

				// Госпитализация
				if (isset($data['PrehospWaifRefuseCause_id'])) {
					$filter .= " and EPS.PrehospWaifRefuseCause_id = :PrehospWaifRefuseCause_id";
					$queryParams['PrehospWaifRefuseCause_id'] = $data['PrehospWaifRefuseCause_id'];
				}

				if (isset($data['Ksg_id'])) {
					if ($this->getRegionNick() == 'ekb') {
						$filter .= " and sksg.Mes_id = :Ksg_id";
						$queryParams['Ksg_id'] = $data['Ksg_id'];
					} else {
						$filter .= " and (ksgkpg.Mes_id = :Ksg_id)";
						$queryParams['Ksg_id'] = $data['Ksg_id'];
					}
				}

				if (isset($data['Kpg_id'])) {
					$filter .= " and (kpg.Mes_id = :Kpg_id)";
					$queryParams['Kpg_id'] = $data['Kpg_id'];
				}

				// Госпитализация
				if (isset($data['LpuUnitType_did'])) {
					$query .= "
						outer apply (
							select top 1 LU_tmp.LpuUnitType_id
							from v_EvnSection ES_tmp with (nolock)
								inner join LpuSection LS_tmp with (nolock) on LS_tmp.LpuSection_id = ES_tmp.LpuSection_id
								inner join LpuUnit LU_tmp with (nolock) on LU_tmp.LpuUnit_id = LS_tmp.LpuUnit_id
							where ES_tmp.EvnSection_rid = EPS.EvnPS_id
								and ES_tmp.Lpu_id " . $this->getLpuIdFilter($data) . "
								and ISNULL(ES_tmp.EvnSection_IsPriem, 1) = 1
							order by ES_tmp.EvnSection_setDT
						) ESHosp
					";
					$filter .= " and ESHosp.LpuUnitType_id = :LpuUnitType_did";
					$queryParams['LpuUnitType_did'] = $data['LpuUnitType_did'];
				}

				if (isset($data['EvnPS_IsWaif'])) {
					$filter .= " and EPS.EvnPS_IsWaif = :EvnPS_IsWaif";
					$queryParams['EvnPS_IsWaif'] = $data['EvnPS_IsWaif'];
				}

				if ($data['session']['region']['nick'] == 'ufa') {
					if (!empty($data['HTMedicalCareClass_id'])) {
						switch ($data['SearchFormType']) {
							case 'EvnPS':
								$filter .= " and EPSLastES.HTMedicalCareClass_id = :HTMedicalCareClass_id";
								$queryParams['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
								break;

							case 'EvnSection':
								$filter .= " and ESEC.HTMedicalCareClass_id = :HTMedicalCareClass_id";
								$queryParams['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
								break;
						}
					} else if (!empty($data['HTMedicalCareType_id'])) {
						switch ($data['SearchFormType']) {
							case 'EvnPS':
								$filter .= " and EPSLastES.HTMedicalCareClass_id in (
                                    select
                                        HTMedicalCareClass_id
                                    from
                                        nsi.HTMedicalCareClass with(nolock)
                                    where
                                        HTMedicalCareType_id = :HTMedicalCareType_id

                                )";
								$queryParams['HTMedicalCareType_id'] = $data['HTMedicalCareType_id'];
								break;

							case 'EvnSection':
								$filter .= " and ESEC.HTMedicalCareClass_id in (
                                    select
                                        HTMedicalCareClass_id
                                    from
                                        nsi.HTMedicalCareClass with(nolock)
                                    where
                                        HTMedicalCareType_id = :HTMedicalCareType_id

                                )";
								$queryParams['HTMedicalCareType_id'] = $data['HTMedicalCareType_id'];
								break;
						}
					}
				}

				//признак оплаченности случая
				if (!empty($data['EvnSection_isPaid'])) {

					switch ($data['SearchFormType']) {
						case 'EvnPS':
							switch ($this->getRegionNick()) {
								//Признак оплаты на самой КВС
								case 'ekb':
									$filter .= " and ISNULL(EPS.EvnPS_isPaid,1) = :EvnSection_isPaid";
									break;
								default:
									$this->load->model('EvnPS_model', 'EvnPS_model');

									// Для регионов без отдельного движения в приемном:
									// КВС оплачена, если все движения оплачены
									// Для регионов с отдельным движением в приемном:
									// КВС оплачена, если:
									// 1) есть движения в профильных отделениях и все они оплачены
									// 2) есть только движение в приемном и оно оплачено

									$query .= "
										outer apply (
											select top 1 1 as EvnSection_isPaid
											from v_EvnSection with (nolock)
											where EvnSection_pid = EPS.EvnPS_id
												" . (in_array($this->getRegionNick(), $this->EvnPS_model->getListRegionNickWithEvnSectionPriem()) ? "and (EvnSection_Count = 1 or ISNULL(EvnSection_IsPriem, 1) = 1)" : "") . "
												and ISNULL(EvnSection_isPaid, 1) = 1
										) ESpaid
									";
									$filter .= " and ISNULL(ESpaid.EvnSection_isPaid, 2) = :EvnSection_isPaid";
									break;
							}
							break;
						case 'EvnSection':
							$filter .= " and ISNULL(ESEC.EvnSection_isPaid,1) = :EvnSection_isPaid";
							break;
					}

					$queryParams['EvnSection_isPaid'] = $data['EvnSection_isPaid'];
				}

				if (isset($data['LpuSection_cid']) || isset($data['LpuBuilding_cid']) || isset($data['MedPersonal_cid']) || isset($data['MedStaffFact_cid']) || isset($data['EvnSection_disDate_Range'][0]) ||
						//isset($data['EvnSection_isPaid']) ||
						isset($data['EvnSection_disDate_Range'][1]) || isset($data['EvnSection_setDate_Range'][0]) ||
						isset($data['EvnSection_setDate_Range'][1]) || isset($data['DiagSetClass_id']) || isset($data['DiagSetType_id']) ||
						isset($data['Diag_Code_From']) || isset($data['Diag_Code_To'])
				) {
					switch ($data['SearchFormType']) {
						case 'KvsPerson':
							$filter .= " and exists (
									select top 1 1
									from v_EvnSection ES with (nolock)
									where ES.EvnSection_rid = EPS.EvnPS_id
										and ES.Lpu_id " . $this->getLpuIdFilter($data) . "
							";
							if (isset($data['EvnSection_disDate_Range'][0])) {
								$filter .= " and ES.EvnSection_disDate >= :EvnSection_disDate_Range_0";
								$queryParams['EvnSection_disDate_Range_0'] = $data['EvnSection_disDate_Range'][0];
							}

							// Лечение
							if (isset($data['EvnSection_disDate_Range'][1])) {
								$filter .= " and ES.EvnSection_disDate <= :EvnSection_disDate_Range_1";
								$queryParams['EvnSection_disDate_Range_1'] = $data['EvnSection_disDate_Range'][1];
							}

							// Лечение
							if (isset($data['EvnSection_setDate_Range'][0])) {
								$filter .= " and ES.EvnSection_setDate >= :EvnSection_setDate_Range_0";
								$queryParams['EvnSection_setDate_Range_0'] = $data['EvnSection_setDate_Range'][0];
							}

							// Лечение
							if (isset($data['EvnSection_setDate_Range'][1])) {
								$filter .= " and ES.EvnSection_setDate <= :EvnSection_setDate_Range_1";
								$queryParams['EvnSection_setDate_Range_1'] = $data['EvnSection_setDate_Range'][1];
							}
							$filter .= ")";
							break;
						case 'EvnPS':

							$filter .= " and exists (
								select top 1 1
								from v_EvnSection ES with (nolock)
								where ES.EvnSection_rid = EPS.EvnPS_id
									and ES.Lpu_id " . $this->getLpuIdFilter($data) . "
							";

							// Лечение
							if (isset($data['EvnSection_disDate_Range'][0])) {
								$filter .= " and ES.EvnSection_disDate >= :EvnSection_disDate_Range_0";
								$queryParams['EvnSection_disDate_Range_0'] = $data['EvnSection_disDate_Range'][0];
							}

							// Лечение
							if (isset($data['EvnSection_disDate_Range'][1])) {
								$filter .= " and ES.EvnSection_disDate <= :EvnSection_disDate_Range_1";
								$queryParams['EvnSection_disDate_Range_1'] = $data['EvnSection_disDate_Range'][1];
							}

							// Лечение
							if (isset($data['EvnSection_setDate_Range'][0])) {
								$filter .= " and ES.EvnSection_setDate >= :EvnSection_setDate_Range_0";
								$queryParams['EvnSection_setDate_Range_0'] = $data['EvnSection_setDate_Range'][0];
							}

							// Лечение
							if (isset($data['EvnSection_setDate_Range'][1])) {
								$filter .= " and ES.EvnSection_setDate <= :EvnSection_setDate_Range_1";
								$queryParams['EvnSection_setDate_Range_1'] = $data['EvnSection_setDate_Range'][1];
							}

							// Лечение
							if (isset($data['LpuSection_cid'])) {
								$filter .= " and ES.LpuSection_id = :LpuSection_id";
								$queryParams['LpuSection_id'] = $data['LpuSection_cid'];
							} elseif (isset($data['LpuBuilding_cid'])) {
								$filter .= " and exists (
									select
										1
									from LpuSection LS with (nolock)
										left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									where
										LU.LpuBuilding_id = :LpuBuilding_cid AND
										LS.LpuSection_id = ES.LpuSection_id
									)";
								$queryParams['LpuBuilding_cid'] = $data['LpuBuilding_cid'];
							}

							// Лечение
							if (isset($data['MedPersonal_cid'])) {
								$filter .= " and ES.MedPersonal_id = :MedPersonal_id";
								$queryParams['MedPersonal_id'] = $data['MedPersonal_cid'];
							}

							$filter .= ")";

							if (isset($data['DiagSetClass_id']) || isset($data['DiagSetType_id']) || isset($data['Diag_Code_From']) || isset($data['Diag_Code_To'])) {
								$filter .= " and exists (
									select
										1
									from v_EvnSection ES2 with (nolock)
										left join v_EvnDiagPS EDPS with (nolock) on EDPS.EvnDiagPS_pid = ES2.EvnSection_id
										left join Diag DiagES with (nolock) on DiagES.Diag_id = ES2.Diag_id
										left join Diag DiagEDPS with (nolock) on DiagEDPS.Diag_id = EDPS.Diag_id
										left join Diag DiagD with (nolock) on DiagD.Diag_id = EPS.Diag_did
										left join Diag DiagP with (nolock) on DiagP.Diag_id = EPS.Diag_pid
										left join v_LeaveType L with (nolock) on L.LeaveType_id = ES2.LeaveType_id
										left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ES2.EvnSection_id
										left join Diag DiagA with (nolock) on DiagA.Diag_id = ED.Diag_aid
									where
										ES2.EvnSection_rid = EPS.EvnPS_id
										 and ES2.Lpu_id " . $this->getLpuIdFilter($data) . "
								";

								if (isset($data['DiagSetType_id'])) {
									$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
									switch ($data['DiagSetType_id']) {
										case 1:
											$filter .= " and ((EDPS.DiagSetType_id = :DiagSetType_id ";
											if (isset($data['DiagSetClass_id'])) {
												$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id)";
												$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];
											} else {
												$filter .= ")";
											}
											$filter .= " or EPS.Diag_did is not null)";

											$filter .= " and ((1 = 1)";

											if (isset($data['Diag_Code_From'])) {
												$filter .= " and (DiagD.Diag_Code >= :Diag_Code_From or DiagEDPS.Diag_Code >= :Diag_Code_From)";
												$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
											}

											if (isset($data['Diag_Code_To'])) {
												$filter .= " and (DiagD.Diag_Code <= :Diag_Code_To or DiagEDPS.Diag_Code <= :Diag_Code_To)";
												$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
											}

											$filter .= ")";
											break;
										case 2:
											$filter .= " and ((EDPS.DiagSetType_id = :DiagSetType_id ";
											if (isset($data['DiagSetClass_id'])) {
												$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id)";
												$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];
											} else {
												$filter .= ")";
											}
											$filter .= " or EPS.Diag_pid is not null)";

											$filter .= " and ((1 = 1)";

											if (isset($data['Diag_Code_From'])) {
												$filter .= " and (DiagP.Diag_Code >= :Diag_Code_From or DiagEDPS.Diag_Code >= :Diag_Code_From)";
												$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
											}

											if (isset($data['Diag_Code_To'])) {
												$filter .= " and (DiagP.Diag_Code <= :Diag_Code_To or DiagEDPS.Diag_Code <= :Diag_Code_To)";
												$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
											}

											$filter .= ")";
											break;
										case 5:
											$filter .= " and (((EDPS.DiagSetType_id = :DiagSetType_id ";

											if (isset($data['DiagSetClass_id'])) {
												$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id)";
												$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];
											} else {
												$filter .= ")";
											}
											if (isset($data['Diag_Code_From']) || isset($data['Diag_Code_To'])) {
												$filter .= " and ((1 = 1)";

												if (isset($data['Diag_Code_From'])) {
													$filter .= " and (DiagEDPS.Diag_Code >= :Diag_Code_From)";
													$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
												}

												if (isset($data['Diag_Code_To'])) {
													$filter .= " and (DiagEDPS.Diag_Code <= :Diag_Code_To)";
													$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
												}

												$filter .= "))";
												$filter .= " or (L.LeaveType_SysNick like '%die%' ";
												$filter .= " and (((1 = 1)";

												if (isset($data['Diag_Code_From'])) {
													$filter .= " and (DiagA.Diag_Code >= :Diag_Code_From)";
													$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
												}

												if (isset($data['Diag_Code_To'])) {
													$filter .= " and (DiagA.Diag_Code <= :Diag_Code_To)";
													$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
												}

												$filter .= ")";

												$filter .= " or ((1 = 1)";

												if (isset($data['Diag_Code_From'])) {
													$filter .= " and (DiagES.Diag_Code >= :Diag_Code_From)";
													$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
												}

												if (isset($data['Diag_Code_To'])) {
													$filter .= " and (DiagES.Diag_Code <= :Diag_Code_To)";
													$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
												}

												$filter .= "))";


												$filter .= "))";
											} else {
												$filter .= ") or L.LeaveType_SysNick like '%die%')";
											}
											break;
										default:
											$filter .= " and ISNULL(ES2.EvnSection_IsPriem, 1) = 1 ";
											$filter .= " and (((EDPS.DiagSetType_id in (3,4) or EDPS.DiagSetType_id is null) ";
											if (isset($data['DiagSetClass_id']) && $data['DiagSetClass_id'] != 1) {
												$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id)";
												$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];
											} else {
												$filter .= ")";
											}
											$filter .= ")";

											$filter .= " and ((1 = 1)";
											if (!empty($data['DiagSetClass_id'])) {
												if ($data['DiagSetClass_id'] == 1) {
													if (isset($data['Diag_Code_From'])) {
														$filter .= " and ((DiagES.Diag_Code >= :Diag_Code_From))";
														$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
													}

													if (isset($data['Diag_Code_To'])) {
														$filter .= " and ((DiagES.Diag_Code <= :Diag_Code_To))";
														$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
													}
												} else {
													if (isset($data['Diag_Code_From'])) {
														$filter .= " and ((DiagEDPS.Diag_Code >= :Diag_Code_From))";
														$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
													}

													if (isset($data['Diag_Code_To'])) {
														$filter .= " and ((DiagEDPS.Diag_Code <= :Diag_Code_To))";
														$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
													}
												}
											} else {
												if (isset($data['Diag_Code_From'])) {
													$filter .= " and ((DiagEDPS.Diag_Code >= :Diag_Code_From) or (DiagES.Diag_Code >= :Diag_Code_From))";
													$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
												}

												if (isset($data['Diag_Code_To'])) {
													$filter .= " and ((DiagEDPS.Diag_Code <= :Diag_Code_To) or (DiagES.Diag_Code <= :Diag_Code_To))";
													$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
												}
											}

											$filter .= ")";
											break;
									}
								} else {
									if (isset($data['DiagSetClass_id'])) {
										if ($data['DiagSetClass_id'] == 1) {
											if (isset($data['Diag_Code_From'])) {
												$filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
												$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
											}

											if (isset($data['Diag_Code_To'])) {
												$filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
												$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
											}
										} else {
											$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id";
											$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];

											if (isset($data['Diag_Code_From'])) {
												$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
												$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
											}

											if (isset($data['Diag_Code_To'])) {
												$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
												$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
											}
										}
									} else {
										$filter .= " and (((1 = 1)";

										if (isset($data['Diag_Code_From'])) {
											$filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (isset($data['Diag_Code_To'])) {
											$filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										$filter .= ") or ((1 = 1)";

										if (isset($data['Diag_Code_From'])) {
											$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (isset($data['Diag_Code_To'])) {
											$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										$filter .= "))";
									}
								}

								/* if (isset($data['DiagSetClass_id'])) {
								  if ($data['DiagSetClass_id'] == 1) {
								  if (isset($data['Diag_Code_From'])) {
								  $filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
								  $queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
								  }

								  if (isset($data['Diag_Code_To'])) {
								  $filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
								  $queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
								  }

								  //if ( isset($data['DiagSetType_id']) && $data['DiagSetType_id'] != 3 ) {
								  //	$filter .= " and (1 = 0)";
								  //}
								  } else {
								  $filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id";
								  $queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];

								  if (isset($data['Diag_Code_From'])) {
								  $filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
								  $queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
								  }

								  if (isset($data['Diag_Code_To'])) {
								  $filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
								  $queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
								  }

								  if (isset($data['DiagSetType_id'])) {
								  if ($data['DiagSetType_id'] == 2){ //Предварительный
								  if (in_array($this->getRegionNick(), Array('perm', 'kareliya'))) {
								  $filter .= " and (EDPS.DiagSetType_id = :DiagSetType_id or ES2.EvnSection_IsPriem = 2)";
								  } else {
								  $filter .= " ) or EPS.Diag_pid is not null";
								  }
								  } else {
								  $filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
								  }
								  $queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
								  }
								  }
								  } else {
								  if (isset($data['DiagSetType_id'])) {
								  if ($data['DiagSetType_id'] == 2){ //Предварительный
								  if (in_array($this->getRegionNick(), Array('perm', 'kareliya'))) {
								  $filter .= " and (EDPS.DiagSetType_id = :DiagSetType_id or ES2.EvnSection_IsPriem = 2)";
								  }
								  } else {
								  $filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
								  }
								  $queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
								  }

								  $filter .= " and (((1 = 1)";

								  if (isset($data['Diag_Code_From'])) {
								  $filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
								  $queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
								  }

								  if (isset($data['Diag_Code_To'])) {
								  $filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
								  $queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
								  }

								  $filter .= ") or ((1 = 1)";

								  if (isset($data['Diag_Code_From'])) {
								  $filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
								  $queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
								  }

								  if (isset($data['Diag_Code_To'])) {
								  $filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
								  $queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
								  }

								  if ($data['DiagSetType_id'] == 2 && !in_array($this->getRegionNick(), Array('perm', 'kareliya'))) {
								  $filter .= " ) or (EPS.Diag_pid is not null))";
								  } else {
								  $filter .= "))";
								  }
								  } */

								$filter .= ")";
							}
							break;

						case 'EvnSection':
							// ФИО
							if (!empty($data['Person_Surname'])) {
								$filter .= " and PS.Person_SurName like (:Person_Surname+'%')";
								$queryParams['Person_Surname'] = $data['Person_Surname'];
							}
							if (!empty($data['Person_Firname'])) {
								$filter .= " and PS.Person_FirName like (:Person_Firname+'%')";
								$queryParams['Person_Firname'] = $data['Person_Firname'];
							}
							if (!empty($data['Person_Secname'])) {
								$filter .= " and PS.Person_SecName like (:Person_Secname+'%')";
								$queryParams['Person_Secname'] = $data['Person_Secname'];
							}
							// ДР
							if (!empty($data['Person_Birthday_Range'][0])) {
								$filter .= " and PS.Person_BirthDay >= :Person_Birthday_Range_0";
								$queryParams['Person_Birthday_Range_0'] = $data['Person_Birthday_Range'][0];
							}

							// ДР
							if (!empty($data['Person_Birthday_Range'][1])) {
								$filter .= " and PS.Person_BirthDay <= :Person_Birthday_Range_1";
								$queryParams['Person_Birthday_Range_1'] = $data['Person_Birthday_Range'][1];
							}

							// Лечение
							if (!empty($data['EvnSection_disDate_Range'][0])) {
								$filter .= " and ESEC.EvnSection_disDate >= :EvnSection_disDate_Range_0";
								$queryParams['EvnSection_disDate_Range_0'] = $data['EvnSection_disDate_Range'][0];
							}

							// Лечение
							if (!empty($data['EvnSection_disDate_Range'][1])) {
								$filter .= " and ESEC.EvnSection_disDate <= :EvnSection_disDate_Range_1";
								$queryParams['EvnSection_disDate_Range_1'] = $data['EvnSection_disDate_Range'][1];
							}

							if (!empty($data['EvnSection_isPaid'])) {
								$filter .= " and ISNULL(ESEC.EvnSection_isPaid,1) = :EvnSection_isPaid";
								$queryParams['EvnSection_isPaid'] = $data['EvnSection_isPaid'];
							}

							// Лечение
							if (!empty($data['EvnSection_setDate_Range'][0])) {
								$filter .= " and ESEC.EvnSection_setDate >= :EvnSection_setDate_Range_0";
								$queryParams['EvnSection_setDate_Range_0'] = $data['EvnSection_setDate_Range'][0];
							}

							// Лечение
							if (!empty($data['EvnSection_setDate_Range'][1])) {
								$filter .= " and ESEC.EvnSection_setDate <= :EvnSection_setDate_Range_1";
								$queryParams['EvnSection_setDate_Range_1'] = $data['EvnSection_setDate_Range'][1];
							}

							// Лечение
							if (!empty($data['LpuSection_cid'])) {
								$filter .= " and ESEC.LpuSection_id = :LpuSection_id";
								$queryParams['LpuSection_id'] = $data['LpuSection_cid'];
							} else if (!empty($data['LpuBuilding_cid'])) {
								$filter .= " and exists (
									select
										1
									from LpuSection LStmp with (nolock)
										left join LpuUnit LUtmp with (nolock) on LUtmp.LpuUnit_id = LStmp.LpuUnit_id
									where
										LUtmp.LpuBuilding_id = :LpuBuilding_cid
										and LStmp.LpuSection_id = ESEC.LpuSection_id
								)";
								$queryParams['LpuBuilding_cid'] = $data['LpuBuilding_cid'];
							}

							// Лечение
							if (!empty($data['MedPersonal_cid'])) {
								$filter .= " and ESEC.MedPersonal_id = :MedPersonal_id";
								$queryParams['MedPersonal_id'] = $data['MedPersonal_cid'];
							}

							if (!empty($data['DiagSetClass_id']) || !empty($data['DiagSetType_id']) || !empty($data['Diag_Code_From']) || !empty($data['Diag_Code_To'])) {
								if (!empty($data['DiagSetClass_id'])) {
									if ($data['DiagSetClass_id'] == 1) {
										if (!empty($data['Diag_Code_From'])) {
											$filter .= " and Dtmp.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (!empty($data['Diag_Code_To'])) {
											$filter .= " and Dtmp.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}
									} else {
										$filter .= " and exists (
											select
												EvnDiagPS_id
											from v_EvnDiagPS EDPS with (nolock)
												left join v_Diag DiagEDPS with (nolock) on DiagEDPS.Diag_id = EDPS.Diag_id
											where
												EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
												and EDPS.DiagSetClass_id = :DiagSetClass_id
										";
										$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];

										if (!empty($data['DiagSetType_id'])) {
											if ($data['DiagSetType_id'] == 2) { //Предварительный
												$filter .= " and (EDPS.DiagSetType_id = :DiagSetType_id or ESEC.EvnSection_IsPriem = 2)";
											} else {
												$filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
											}
											$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
										}

										if (!empty($data['Diag_Code_From'])) {
											$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (!empty($data['Diag_Code_To'])) {
											$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										$filter .= ")";
									}
								} else {
									$filter .= " and exists (
										select
											EvnDiagPS_id
										from v_EvnDiagPS EDPS with (nolock)
											left join v_Diag DiagEDPS with (nolock) on DiagEDPS.Diag_id = EDPS.Diag_id
											left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ESEC.EvnSection_id
											left join Diag DiagA with (nolock) on DiagA.Diag_id = ED.Diag_aid
										where
											EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
									";



									if (!empty($data['DiagSetType_id']) && $data['DiagSetType_id'] == 5) {
										$filter .= " and ((1 = 1)";
										$filter .= " and ED.EvnDie_id is not null";

										$filter .= " and (((1 = 1)";
										$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
										if (!empty($data['Diag_Code_From'])) {
											$filter .= " and DiagA.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (!empty($data['Diag_Code_To'])) {
											$filter .= " and DiagA.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}
										$filter .= ") or ((1 = 1)";
										if (!empty($data['Diag_Code_From'])) {
											$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (!empty($data['Diag_Code_To'])) {
											$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}
										$filter .= "))";
										$filter .= "))";
									} else {
										$filter .= " and (((1 = 1)";

										if (!empty($data['DiagSetType_id'])) {
											if ($data['DiagSetType_id'] == 2) { //Предварительный
												$filter .= " and (EDPS.DiagSetType_id = :DiagSetType_id or ESEC.EvnSection_IsPriem = 2)";
											} else {
												$filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
											}

											$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
										}

										if (!empty($data['Diag_Code_From'])) {
											$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (!empty($data['Diag_Code_To'])) {
											$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										$filter .= ") or ((1 = 1)";

										if (!empty($data['Diag_Code_From'])) {
											$filter .= " and Dtmp.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if (!empty($data['Diag_Code_To'])) {
											$filter .= " and Dtmp.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										$filter .= ")))";
									}
								}
							}
							break;
					}
				}
				//var_dump(isset($data['EvnUsluga_setDate_Range']));
				//return false;
				if (!empty($data['UslugaCategory_id']) || !empty($data['UslugaComplex_Code_From']) || !empty($data['UslugaComplex_Code_To']) || (isset($data['EvnUsluga_setDate_Range']) && is_array($data['EvnUsluga_setDate_Range']) && (!empty($data['EvnUsluga_setDate_Range'][0]) || !empty($data['EvnUsluga_setDate_Range'][1])))
				) {
					$filter .= " and exists (
						select
							t2.UslugaComplex_id
						from v_EvnUsluga t1 with (nolock)
							inner join UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						where
							t1.EvnUsluga_rid = EPS.EvnPS_id
							and t1.Lpu_id " . $this->getLpuIdFilter($data) . "
							and t1.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar')
					";

					// Лечение
					if (!empty($data['EvnUsluga_setDate_Range'][0])) {
						$filter .= " and cast(t1.EvnUsluga_setDT as date) >= cast(:EvnUsluga_setDate_Range_0 as date)";
						$queryParams['EvnUsluga_setDate_Range_0'] = $data['EvnUsluga_setDate_Range'][0];
					}

					// Лечение
					if (!empty($data['EvnUsluga_setDate_Range'][1])) {
						$filter .= " and cast(t1.EvnUsluga_setDT as date) <= cast(:EvnUsluga_setDate_Range_1 as date)";
						$queryParams['EvnUsluga_setDate_Range_1'] = $data['EvnUsluga_setDate_Range'][1];
					}

					// Лечение
					if (!empty($data['UslugaCategory_id'])) {
						$filter .= " and t2.UslugaCategory_id = :UslugaCategory_id ";
						$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
					}

					if (!empty($data['UslugaComplex_Code_From'])) {
						$filter .= " and t2.UslugaComplex_Code >= :UslugaComplex_Code_From ";
						$queryParams['UslugaComplex_Code_From'] = $data['UslugaComplex_Code_From'];
					}

					if (!empty($data['UslugaComplex_Code_To'])) {
						$filter .= " and t2.UslugaComplex_Code <= :UslugaComplex_Code_To ";
						$queryParams['UslugaComplex_Code_To'] = $data['UslugaComplex_Code_To'];
					}

					$filter .= ")";
				}

				if (isset($data['CureResult_id']) && !empty($data['CureResult_id'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							$filter .= " and EPSLastES.CureResult_id = :CureResult_id";
							$queryParams['CureResult_id'] = $data['CureResult_id'];
							break;
						case 'EvnSection':
							$filter .= " and ESEC.CureResult_id = :CureResult_id";
							$queryParams['CureResult_id'] = $data['CureResult_id'];
							break;
					}
				}

				if (!empty($data['EvnLeave_IsNotSet'])) {
					switch ($data['SearchFormType']) {
						case 'EvnPS':
							$filter .= " and EPS.LeaveType_id is null and EPS.PrehospWaifRefuseCause_id is null";
							break;

						case 'EvnSection':
							$filter .= " and ESEC.LeaveType_id is null and ISNULL(ESEC.EvnSection_IsPriem, 1) = 1";
							break;
					}
				} else {
					if (!empty($data['LeaveType_id'])) {
						switch ($data['SearchFormType']) {
							case 'EvnPS':
								$filter .= " and EPS.LeaveType_id = :LeaveType_id";
								//Добавил этот жуткий костыл в рамках задачи https://redmine.swan.perm.ru/issues/41387
								if ($data['session']['region']['nick'] == 'perm') {
									if (isset($data['EvnSection_disDate_Range'][0])) {
										$filter .= " and EPSLastES.EvnSection_disDate >= :EvnSectionLast_disDate_Range_0";
										$queryParams['EvnSectionLast_disDate_Range_0'] = $data['EvnSection_disDate_Range'][0];
									}
									if (isset($data['EvnSection_disDate_Range'][1])) {
										$filter .= " and EPSLastES.EvnSection_disDate <= :EvnSectionLast_disDate_Range_1";
										$queryParams['EvnSectionLast_disDate_Range_1'] = $data['EvnSection_disDate_Range'][1];
									}
								}

								break;

							case 'EvnSection':
								$filter .= " and ESEC.LeaveType_id = :LeaveType_id";
								break;
						}

						$queryParams['LeaveType_id'] = $data['LeaveType_id'];
					}

					if (isset($data['LeaveCause_id']) || isset($data['ResultDesease_id']) ||
							isset($data['Org_oid']) || isset($data['LpuUnitType_oid']) ||
							isset($data['LpuSection_oid']) || isset($data['EvnLeaveBase_UKL']) ||
							isset($data['EvnLeave_IsAmbul']) || isset($data['EvnDie_IsAnatom'])
					) {
						switch ($data['SearchFormType']) {
							case 'EvnPS':
								$query .= " left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('leave', 'ksleave', 'dsleave', 'inicpac', 'ksinicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod') and EL.Lpu_id " . $this->getLpuIdFilter($data);
								$query .= " left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('other', 'dsother', 'ksother', 'ksperitar') and EOL.Lpu_id " . $this->getLpuIdFilter($data);
								$query .= " left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in (" . ($this->regionNick == 'khak' ? "'leave', 'ksleave', 'dsleave', 'inicpac', 'ksinicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod', " : "") . "'die', 'ksdie', 'ksdiepp', 'diepp', 'dsdie', 'dsdiepp', 'kslet', 'ksletitar') and ED.Lpu_id " . $this->getLpuIdFilter($data);
								$query .= " left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('stac', 'ksstac', 'dsstac') and EOST.Lpu_id " . $this->getLpuIdFilter($data);
								$query .= " left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('section', 'dstac', 'kstac') and EOS.Lpu_id " . $this->getLpuIdFilter($data);
								$query .= " left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('ksper', 'dsper') and EOSBP.Lpu_id " . $this->getLpuIdFilter($data);
								break;

							case 'EvnSection':
								$query .= " left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('leave', 'ksleave', 'dsleave', 'ksinicpac', 'inicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod')";
								$query .= " left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('other', 'dsother', 'ksother', 'ksperitar')";
								$query .= " left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in (" . ($this->regionNick == 'khak' ? "'leave', 'ksleave', 'dsleave', 'inicpac', 'ksinicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod', " : "") . "'die', 'ksdie', 'ksdiepp', 'diepp', 'dsdie', 'dsdiepp', 'kslet', 'ksletitar')";
								$query .= " left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('stac', 'ksstac', 'dsstac')";
								$query .= " left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('section', 'dstac', 'kstac')";
								$query .= " left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('ksper', 'dsper')";
								break;
						}

						// Результат лечения
						if (isset($data['EvnDie_IsAnatom']) && !empty($data['EvnDie_IsAnatom'])) {
							$filter .= " and ED.EvnDie_IsAnatom = :EvnDie_IsAnatom";
							$queryParams['EvnDie_IsAnatom'] = $data['EvnDie_IsAnatom'];
						}

						// Результат лечения
						if (isset($data['LeaveCause_id'])) {
							$filter .= " and COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOST.LeaveCause_id, EOS.LeaveCause_id) = :LeaveCause_id";
							$queryParams['LeaveCause_id'] = $data['LeaveCause_id'];
						}

						// Результат лечения
						if (isset($data['ResultDesease_id'])) {
							$filter .= " and COALESCE(EL.ResultDesease_id, ED.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EOS.ResultDesease_id) = :ResultDesease_id";
							$queryParams['ResultDesease_id'] = $data['ResultDesease_id'];
						}

						// Результат лечения
						if (isset($data['Org_oid'])) {
							$filter .= " and EOL.Org_oid = :Org_oid";
							$queryParams['Org_oid'] = $data['Org_oid'];
						}

						// Результат лечения
						if (isset($data['LpuUnitType_oid'])) {
							$filter .= " and EOST.LpuUnitType_oid = :LpuUnitType_oid";
							$queryParams['LpuUnitType_oid'] = $data['LpuUnitType_oid'];
						}

						// Результат лечения
						if (isset($data['LpuSection_oid'])) {
							$filter .= " and ISNULL(EOS.LpuSection_oid, EOST.LpuSection_oid) = :LpuSection_oid";
							$queryParams['LpuSection_oid'] = $data['LpuSection_oid'];
						}

						// Результат лечения
						if (isset($data['EvnLeaveBase_UKL'])) {
							$filter .= " and COALESCE(ED.EvnDie_UKL, EL.EvnLeave_UKL, EOL.EvnOtherLpu_UKL, EOST.EvnOtherStac_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL) = :EvnLeaveBase_UKL";
							$queryParams['EvnLeaveBase_UKL'] = $data['EvnLeaveBase_UKL'];
						}

						// Результат лечения
						if (isset($data['EvnLeave_IsAmbul'])) {
							$filter .= " and ISNULL(EL.EvnLeave_IsAmbul, 1) = :EvnLeave_IsAmbul";
							$queryParams['EvnLeave_IsAmbul'] = $data['EvnLeave_IsAmbul'];
						}
					}
				}

				if (isset($data['StickCause_id']) || isset($data['StickType_id']) ||
						isset($data['EvnStick_begDate_Range'][0]) || isset($data['EvnStick_begDate_Range'][1]) ||
						isset($data['EvnStick_endDate_Range'][0]) || isset($data['EvnStick_endDate_Range'][1])
				) {
					$evn_stick_filter = '';

					// Результаты
					if (isset($data['EvnStick_begDate_Range'][0])) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
						$queryParams['EvnStick_begDate_Range_0'] = $data['EvnStick_begDate_Range'][0];
					}

					// Результаты
					if (isset($data['EvnStick_begDate_Range'][1])) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
						$queryParams['EvnStick_begDate_Range_1'] = $data['EvnStick_begDate_Range'][1];
					}

					// Результаты
					if (isset($data['EvnStick_endDate_Range'][0])) {
						$evn_stick_filter .= " and (
							case
								when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0 then 1
								when ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0) then 1
								else 0
							end = 1
						)";
						$queryParams['EvnStick_endDate_Range_0'] = $data['EvnStick_endDate_Range'][0];
					}

					// Результаты
					if (isset($data['EvnStick_endDate_Range'][1])) {
						$evn_stick_filter .= " and (
							case
								when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1 then 1
								when ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1) then 1
								else 0
							end = 1
						)";
						$queryParams['EvnStick_endDate_Range_1'] = $data['EvnStick_endDate_Range'][1];
					}

					// Результаты
					if (isset($data['StickCause_id'])) {
						$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
						$queryParams['StickCause_id'] = $data['StickCause_id'];
					}

					// Результаты
					if (isset($data['StickType_id'])) {
						$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
						$queryParams['StickType_id'] = $data['StickType_id'];
					}

					$filter .= "
						and exists (
							select EvnStickBase_id
							from v_EvnStickBase ESB with (nolock)
							where ESB.EvnStickBase_mid = EPS.EvnPS_id
								" . $evn_stick_filter . "
							union all
							select Evn_id as EvnStickBase_id
							from v_EvnLink EL with (nolock)
								inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
							where EL.Evn_id = EPS.EvnPS_id
								" . $evn_stick_filter . "
						)
					";
				}
				//#155391 фильтр по фед.сервису
				$query .= "
					outer apply (
						select top 1 ServiceEvnStatus_id
						from v_ServiceEvnHist with (nolock)
						where Evn_id = EPS.EvnPS_id
							and ServiceEvnList_id = 1
						order by ServiceEvnHist_id desc
					) SEH1
					left join v_ServiceEvnStatus SES1 with(nolock) on SES1.ServiceEvnStatus_id = SEH1.ServiceEvnStatus_id
				";
				break;

			case 'EvnRecept':
				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnRecept ER with (nolock) on ER.Server_id = PS.Server_id and ER.PersonEvn_id = PS.PersonEvn_id";

					if (!isMinZdrav() && !$isFarmacy && $data['Lpu_id'] > 0) {
						$query .= " and ER.Lpu_id " . $this->getLpuIdFilter($data);
					}
				} else {
					$query .= " inner join v_EvnRecept ER with (nolock) on ER.Person_id = PS.Person_id";

					if (!isMinZdrav() && !$isFarmacy && $data['Lpu_id'] > 0) {
						$query .= " and ER.Lpu_id " . $this->getLpuIdFilter($data);
					}
				}

				$query .= "
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = er.lpu_id
				";

				$query .= " left join dbo.v_ReceptValid RV with (nolock) on RV.ReceptValid_id = ER.ReceptValid_id";

				if (!empty($data['WithDrugComplexMnn']) && $data['WithDrugComplexMnn']) {
					$filter .= " and ISNULL(ER.DrugComplexMnn_id, ERDrugRls.DrugComplexMnn_id) is not null";
				}
				if (!empty($data['EvnRecept_MarkDeleted'])) {
					$filter .= " and ER_RDT.ReceptDelayType_Code = 4 ";
				}

				if (!empty($data['EvnRecept_IsSigned'])) {
					if ($data['EvnRecept_IsSigned'] == '2') {
						$filter .= " and (ER.EvnRecept_IsSigned = 2 or (ER.pmUser_signID is not null and ER.EvnRecept_signDT is not null)) ";
					}
					if ($data['EvnRecept_IsSigned'] == '1') {
						$filter .= " and (ISNULL(ER.EvnRecept_IsSigned,1) <> 2 and ER.pmUser_signID is null and ER.EvnRecept_signDT is null) ";
					}
				}
				if (!empty($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id']) {
					$filter .= " and wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
					$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
				}

				// $query .= " inner join v_EvnRecept ER with(nolock) on ER.Person_id = PS.Person_id and ER.Lpu_id = :Lpu_id";
				$query .= " left join v_ReceptForm RecF with (nolock) on RecF.ReceptForm_id = ER.ReceptForm_id";
				$query .= " left join v_ReceptType RecT with (nolock) on RecT.ReceptType_id = ER.ReceptType_id";
				$query .= " left join v_Drug ERDrug with (nolock) on ERDrug.Drug_id = ER.Drug_id";
				$query .= " left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id";
				$query .= " left join rls.v_Drug ERDrugRls with (nolock) on ERDrugRls.Drug_id = ER.Drug_rlsid";
				$query .= " left join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id";
				$query .= " left join rls.v_DrugNomen DrugNomen with (nolock) on DrugNomen.Drug_id = ER.Drug_rlsid or DrugNomen.DrugNomen_Code = cast(ERDrug.Drug_CodeG as varchar)";
				$query .= " left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id";
				$query .= " left join dbo.v_DrugFinance drugFin with (nolock) on drugFin.DrugFinance_id = ER.DrugFinance_id";
				$query .= " left join v_ReceptDelayType ER_RDT with (nolock) on ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id";
				$query .= " left join v_pmUserCache pmUC with (nolock) on pmUC.PMUser_id = ER.pmUser_updID";
				//$query .= " left join ReceptOtov RecOt with (nolock) on RecOt.EvnRecept_id = ER.EvnRecept_id";
				$query .= "
					outer apply (
						select top 1 
							RecOt_t.EvnRecept_otpDate,
							RecOt_t.EvnRecept_obrDate
						from ReceptOtov RecOt_t
						where RecOt_t.EvnRecept_id = ER.EvnRecept_id
						order by RecOt_t.ReceptOtov_insDT desc
					) RecOt
				";
				$query .= " outer apply (select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ER.MedPersonal_id) as ERMP";
				$query .= " outer apply (select sum((ROt.EvnRecept_Price * ROt.EvnRecept_Kolvo)) as recSum from ReceptOtov ROt with (nolock) where ROt.EvnRecept_id = ER.EvnRecept_id) as RecOtovSum";
				$query .= " outer apply (select top 1 dus.DocumentUc_id 
					from ReceptOtov ROtov with (nolock)
					left join DocumentUcStr dus with (nolock) on dus.ReceptOtov_id = ROtov.ReceptOtov_id
					where ROtov.EvnRecept_id = ER.EvnRecept_id) as DocUc";

				$query .= " left join v_Diag ERDiag with (nolock) on ERDiag.Diag_id = ER.Diag_id";
				if (!$isFarmacy && $data['Lpu_id'] > 0)
					$query .= " and ER.Lpu_id " . $this->getLpuIdFilter($data);

				if ((strlen($data['ER_Diag_Code_From']) > 0) || (strlen($data['ER_Diag_Code_To']) > 0)) {

					if (strlen($data['ER_Diag_Code_From']) > 0) {
						$filter .= " and ERDiag.Diag_Code >= :ER_Diag_Code_From";
						$queryParams['ER_Diag_Code_From'] = $data['ER_Diag_Code_From'];
					}

					if (strlen($data['ER_Diag_Code_To']) > 0) {
						$filter .= " and ERDiag.Diag_Code <= :ER_Diag_Code_To";
						$queryParams['ER_Diag_Code_To'] = $data['ER_Diag_Code_To'];
					}
				}

				//  Вставлено для отражения причины отказа
				//$query .= " left join ReceptWrong Wr with (nolock) on Wr.EvnRecept_id = ER.EvnRecept_id";
				$query .= "
                	 outer apply(
						select top 1 
							Wr_t.ReceptWrong_id,
							Wr_t.ReceptWrong_Decr
						from ReceptWrong Wr_t
						where Wr_t.EvnRecept_id = ER.EvnRecept_id
						order by Wr_t.ReceptWrong_updDT desc
					) as Wr
                ";

				//$query .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = ER.PrivilegeType_id";
				if (!$isFarmacy)
					$this->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);

				//$filter .= " and ER.ReceptRemoveCauseType_id is null";
				// Рецепт
				/* if ($data['EvnRecept_IsSigned'] > 0) {
				  $filter .= " and ER.EvnRecept_IsSigned = :EvnRecept_IsSigned";
				  $queryParams['EvnRecept_IsSigned'] = $data['EvnRecept_IsSigned'];
				  } */

				// Рецепт
				if ($data['Drug_id'] > 0) {
					$filter .= " and ERDrug.Drug_id = :Drug_id";
					$queryParams['Drug_id'] = $data['Drug_id'];
				}

				// Рецепт
				if ($data['DrugMnn_id'] > 0) {
					$filter .= " and ERDrug.DrugMnn_id = :DrugMnn_id";
					$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
				}

				// Рецепт
				if ($data['ER_MedPersonal_id'] > 0) {
					$filter .= " and ER.MedPersonal_id = :ER_MedPersonal_id";
					$queryParams['ER_MedPersonal_id'] = $data['ER_MedPersonal_id'];
				}

				// Рецепт
				if (isset($data['ER_PrivilegeType_id'])) {
					$filter .= " and ER.PrivilegeType_id = :ER_PrivilegeType_id";
					$queryParams['ER_PrivilegeType_id'] = $data['ER_PrivilegeType_id'];
				}

				// Рецепт
				if (isset($data['EvnRecept_Is7Noz'])) {
					$filter .= " and ISNULL(ER.EvnRecept_Is7Noz, 1) = :EvnRecept_Is7Noz";
					$queryParams['EvnRecept_Is7Noz'] = $data['EvnRecept_Is7Noz'];
				}
				if (isset($data['ReceptForm_id'])) {
					$filter .= " and ER.ReceptForm_id = :ReceptForm_id";
					$queryParams['ReceptForm_id'] = $data['ReceptForm_id'];
				}
				// Рецепт
				if ($data['EvnRecept_IsKEK'] > 0) {
					$filter .= " and ER.EvnRecept_IsKEK = :EvnRecept_IsKEK";
					$queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
				}
				if (!empty($data['EvnRecept_VKProtocolNum'])) {
					$filter .= " and ER.EvnRecept_VKProtocolNum = :EvnRecept_VKProtocolNum";
					$queryParams['EvnRecept_VKProtocolNum'] = $data['EvnRecept_VKProtocolNum'];
				}
				if (!empty($data['EvnRecept_VKProtocolDT'])) {
					$filter .= " and ER.EvnRecept_VKProtocolDT = :EvnRecept_VKProtocolDT";
					$queryParams['EvnRecept_VKProtocolDT'] = $data['EvnRecept_VKProtocolDT'];
				}

				// Рецепт
				if ($data['EvnRecept_IsNotOstat'] > 0) {
					$filter .= " and ISNULL(ER.EvnRecept_IsNotOstat, 1) = :EvnRecept_IsNotOstat";
					$queryParams['EvnRecept_IsNotOstat'] = $data['EvnRecept_IsNotOstat'];
				}

				// Рецепт
				if (strlen($data['EvnRecept_Num']) > 0) {
					//$filter .= " and ER.EvnRecept_Num = :EvnRecept_Num";
					$filter .= " and replace(ltrim(replace(ER.EvnRecept_Num, '0', ' ')), ' ', 0) = replace(ltrim(replace(:EvnRecept_Num, '0', ' ')), ' ', 0)";
					$queryParams['EvnRecept_Num'] = $data['EvnRecept_Num'];
				}

				// Рецепт
				if (strlen($data['EvnRecept_Ser']) > 0) {
					$filter .= " and ER.EvnRecept_Ser = :EvnRecept_Ser";
					$queryParams['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
				}


				// Рецепт
				if (isset($data['EvnRecept_setDate'])) {
					$queryParams['EvnRecept_setDate'] = $data['EvnRecept_setDate'];
				}

				// Рецепт
				if (isset($data['EvnRecept_setDate_Range'][0])) {
					$queryParams['EvnRecept_setDate_Range_0'] = $data['EvnRecept_setDate_Range'][0];
				}

				// Рецепт
				if (isset($data['EvnRecept_setDate_Range'][1])) {
					$queryParams['EvnRecept_setDate_Range_1'] = $data['EvnRecept_setDate_Range'][1];
				}
				switch ($data['EvnReceptSearchDateType']) {
					case 'vypis':
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ER.EvnRecept_setDate = cast(:EvnRecept_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ER.EvnRecept_setDate >= cast(:EvnRecept_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ER.EvnRecept_setDate <= cast(:EvnRecept_setDate_Range_1 as datetime)";
						}
						break;
					case 'obr':
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ER.EvnRecept_obrDT = cast(:EvnRecept_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ER.EvnRecept_obrDT >= cast(:EvnRecept_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ER.EvnRecept_obrDT <= cast(:EvnRecept_setDate_Range_1 as datetime)";
						}
						break;
					case 'obesp':
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ER.EvnRecept_otpDT = cast(:EvnRecept_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ER.EvnRecept_otpDT >= cast(:EvnRecept_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ER.EvnRecept_otpDT <= cast(:EvnRecept_setDate_Range_1 as datetime)";
						}
						break;
					case 'otkaz':
						$query .= " left join v_WhsDocumentUcActReceptList WDUARL with (nolock) on WDUARL.EvnRecept_id = ER.EvnRecept_id
									left join v_WhsDocumentUcActReceptOut WDUARO with (nolock) on WDUARO.WhsDocumentUcActReceptOut_id = WDUARL.WhsDocumentUcActReceptOut_id";
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and (
								(Wr.ReceptWrong_id is not null and ER.EvnRecept_obrDT = cast(:EvnRecept_setDate as datetime))
								or (WDUARO.WhsDocumentUcActReceptOut_id is not null and WDUARO.WhsDocumentUcActReceptOut_setDT = cast(:EvnRecept_setDate as datetime))
								)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and (
								(Wr.ReceptWrong_id is not null and ER.EvnRecept_obrDT = cast(:EvnRecept_setDate_Range_0 as datetime))
								or (WDUARO.WhsDocumentUcActReceptOut_id is not null and WDUARO.WhsDocumentUcActReceptOut_setDT = cast(:EvnRecept_setDate_Range_0 as datetime))
								)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and (
								(Wr.ReceptWrong_id is not null and ER.EvnRecept_obrDT = cast(:EvnRecept_setDate_Range_1 as datetime))
								or (WDUARO.WhsDocumentUcActReceptOut_id is not null and WDUARO.WhsDocumentUcActReceptOut_setDT = cast(:EvnRecept_setDate_Range_1 as datetime))
								)";
						}
						break;

					default:
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ER.EvnRecept_setDate = cast(:EvnRecept_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ER.EvnRecept_setDate >= cast(:EvnRecept_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ER.EvnRecept_setDate <= cast(:EvnRecept_setDate_Range_1 as datetime)";
						}
						break;
				}


				// Рецепт (доп.)
				if (isset($data['OrgFarmacy_id'])) {
					$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

					if ($data['OrgFarmacy_id'] == -1) {
						$filter .= " and ER.OrgFarmacy_id is null";
					} else {
						$filter .= " and ER.OrgFarmacy_id = :OrgFarmacy_id";
					}
				}

				// Рецепт (доп.)
				if (isset($data['OrgFarmacyIndex_OrgFarmacy_id'])) {
					$queryParams['OrgFarmacyIndex_OrgFarmacy_id'] = $data['OrgFarmacyIndex_OrgFarmacy_id'];

					if ($data['OrgFarmacyIndex_OrgFarmacy_id'] > 0) {
						$filter .= " and ER.Lpu_id in (select Lpu_id from v_OrgFarmacyIndex with(nolock) where OrgFarmacy_id = :OrgFarmacyIndex_OrgFarmacy_id) and isnull(ER.OrgFarmacy_id, '') = :OrgFarmacyIndex_OrgFarmacy_id";
					}
				}

				// Рецепт (доп.)
				if ($data['ReceptDiscount_id'] > 0) {
					$filter .= " and ER.ReceptDiscount_id = :ReceptDiscount_id";
					$queryParams['ReceptDiscount_id'] = $data['ReceptDiscount_id'];
				}

				// Рецепт (доп.)
				if ($data['ReceptFinance_id'] > 0) {
					$filter .= " and ER.ReceptFinance_id = :ReceptFinance_id";
					$queryParams['ReceptFinance_id'] = $data['ReceptFinance_id'];
				}

				// Рецепт (доп.)
				if ($data['ReceptType_id'] > 0) {
					$filter .= " and ER.ReceptType_id = :ReceptType_id";
					$queryParams['ReceptType_id'] = $data['ReceptType_id'];
				}

				// Рецепт (доп.)
				if ($data['ReceptValid_id'] > 0) {
					$filter .= " and ER.ReceptValid_id = :ReceptValid_id";
					$queryParams['ReceptValid_id'] = $data['ReceptValid_id'];
				}

				// Рецепт (доп.)
				if (isset($data['EvnRecept_IsExtemp'])) {
					$filter .= " and ISNULL(ER.EvnRecept_IsExtemp, 1) = :EvnRecept_IsExtemp";
					$queryParams['EvnRecept_IsExtemp'] = $data['EvnRecept_IsExtemp'];
				}

				if (!(isset($data['inValidRecept']) && $data['inValidRecept'] == 1) && (isset($data['DistributionPoint']) && $data['DistributionPoint'] == 1)) {
					$filter .= " and (
						(RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value) >= @getDT)) 
						or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
						)";
				}

				if (isset($data['ReceptDelayType_id']) && $data['ReceptDelayType_id'] != 7) {
					if ($data['ReceptDelayType_id'] != 6) {
						$filter .= " and ER.ReceptDelayType_id = :ReceptDelayType_id";
						$queryParams['ReceptDelayType_id'] = $data['ReceptDelayType_id'];
					} else {
						$filter .= " and ER.ReceptDelayType_id is null and (
						(RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value) >= @getDT)) 
						or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
						)";
						/* $filter .= " and ER.ReceptDelayType_id is null and (
						  (RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value) >= @getDT))
						  or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
						  ) and not exists(select top 1 RO.ReceptOtov_id from v_ReceptOtov RO with (nolock) where RO.EvnRecept_id = ER.EvnRecept_id)"; */
					}
				}

				if (isset($data['Drug_Name'])) {
					$filter .= " and ((DCM.DrugComplexMnn_RusName like :Drug_Name) or (ERDrug.Drug_Name like :Drug_Name) or (ERDrugRls.Drug_Name like :Drug_Name) or (ER.EvnRecept_ExtempContents like :Drug_Name))";
					$queryParams['Drug_Name'] = '%' . $data['Drug_Name'] . '%';
				}
				break;

			case 'EvnReceptGeneral':
				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnReceptGeneral ERG with (nolock) on ERG.Server_id = PS.Server_id and ERG.PersonEvn_id = PS.PersonEvn_id";
				} else {
					$query .= " inner join v_EvnReceptGeneral ERG with (nolock) on ERG.Person_id = PS.Person_id";
				}
				if (!empty($data['WithDrugComplexMnn'])) {
					$query .= " and ERG.DrugComplexMnn_id is not null";
				}

				if (!isMinZdrav() && !$isFarmacy && $data['Lpu_id'] > 0) {
					$query .= " and ERG.Lpu_id " . $this->getLpuIdFilter($data);
				}

				$query .= " left join dbo.v_ReceptValid RV with (nolock) on RV.ReceptValid_id = ERG.ReceptValid_id";
				// $query .= " inner join v_EvnRecept ER with(nolock) on ER.Person_id = PS.Person_id and ER.Lpu_id = :Lpu_id";
				$query .= " left join v_ReceptForm RecF with (nolock) on RecF.ReceptForm_id = ERG.ReceptForm_id";
				$query .= " left join v_Drug ERDrug with (nolock) on ERDrug.Drug_id = ERG.Drug_id";
				$query .= " left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id";
				$query .= " left join rls.v_Drug ERDrugRls with (nolock) on ERDrugRls.Drug_id = ERG.Drug_rlsid";
				$query .= " left join rls.v_DrugComplexMnn DCM with (nolock) on DCM.DrugComplexMnn_id = ERG.DrugComplexMnn_id";
				$query .= " left join rls.v_DrugNomen DrugNomen with (nolock) on DrugNomen.Drug_id = ERG.Drug_rlsid";
				$query .= " left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = ERG.WhsDocumentCostItemType_id";
				$query .= " left join dbo.v_DrugFinance drugFin with (nolock) on drugFin.DrugFinance_id = ERG.DrugFinance_id";
				$query .= " left join v_ReceptDelayType ER_RDT with (nolock) on ER_RDT.ReceptDelayType_id = ERG.ReceptDelayType_id";
				$query .= " left join ReceptOtov RecOt with (nolock) on RecOt.EvnRecept_id = ERG.EvnReceptGeneral_id";
				$query .= " outer apply (select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ERG.MedPersonal_id) as ERMP";
				$query .= " outer apply (select sum((ROt.EvnRecept_Price * ROt.EvnRecept_Kolvo)) as recSum from ReceptOtov ROt with (nolock) where ROt.EvnRecept_id = ERG.EvnReceptGeneral_id) as RecOtovSum";
				$query .= " outer apply (select top 1 dus.DocumentUc_id 
					from ReceptOtov ROtov with (nolock)
					left join DocumentUcStr dus with (nolock) on dus.ReceptOtov_id = ROtov.ReceptOtov_id
					where ROtov.EvnRecept_id = ERG.EvnReceptGeneral_id) as DocUc";

				$query .= " left join v_Diag ERDiag with (nolock) on ERDiag.Diag_id = ERG.Diag_id";
				if (!$isFarmacy && $data['Lpu_id'] > 0)
					$query .= " and ERG.Lpu_id " . $this->getLpuIdFilter($data);

				/* if ((strlen($data['ER_Diag_Code_From']) > 0) || (strlen($data['ER_Diag_Code_To']) > 0)) {

				  if (strlen($data['ER_Diag_Code_From']) > 0) {
				  $filter .= " and ERDiag.Diag_Code >= :ER_Diag_Code_From";
				  $queryParams['ER_Diag_Code_From'] = $data['ER_Diag_Code_From'];
				  }

				  if (strlen($data['ER_Diag_Code_To']) > 0) {
				  $filter .= " and ERDiag.Diag_Code <= :ER_Diag_Code_To";
				  $queryParams['ER_Diag_Code_To'] = $data['ER_Diag_Code_To'];
				  }
				  }

				  //$query .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = ERG.PrivilegeType_id";
				  $this->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);

				  $filter .= " and ERG.ReceptRemoveCauseType_id is null";
				 */
				// Рецепт
				if ($data['EvnRecept_IsSigned'] > 0) {
					$filter .= " and ERG.EvnReceptGeneral_IsSigned = :EvnRecept_IsSigned";
					$queryParams['EvnRecept_IsSigned'] = $data['EvnRecept_IsSigned'];
				}

				// Рецепт
				if ($data['Drug_id'] > 0) {
					$filter .= " and ERDrug.Drug_id = :Drug_id";
					$queryParams['Drug_id'] = $data['Drug_id'];
				}

				// Рецепт
				/* if ($data['DrugMnn_id'] > 0) {
				  $filter .= " and ERDrug.DrugMnn_id = :DrugMnn_id";
				  $queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
				  } */

				// Рецепт
				if ($data['ER_MedPersonal_id'] > 0) {
					$filter .= " and ERG.MedPersonal_id = :ER_MedPersonal_id";
					$queryParams['ER_MedPersonal_id'] = $data['ER_MedPersonal_id'];
				}

				// Рецепт
				/* if (isset($data['ER_PrivilegeType_id'])) {
				  $filter .= " and ERG.PrivilegeType_id = :ER_PrivilegeType_id";
				  $queryParams['ER_PrivilegeType_id'] = $data['ER_PrivilegeType_id'];
				  }

				  // Рецепт
				  if (isset($data['EvnRecept_Is7Noz'])) {
				  $filter .= " and ISNULL(ERG.EvnRecept_Is7Noz, 1) = :EvnRecept_Is7Noz";
				  $queryParams['EvnRecept_Is7Noz'] = $data['EvnRecept_Is7Noz'];
				  }
				  if (isset($data['ReceptForm_id'])) {
				  $filter .= " and ERG.ReceptForm_id = :ReceptForm_id";
				  $queryParams['ReceptForm_id'] = $data['ReceptForm_id'];
				  }
				  // Рецепт
				  if ($data['EvnRecept_IsKEK'] > 0) {
				  $filter .= " and ERG.EvnRecept_IsKEK = :EvnRecept_IsKEK";
				  $queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
				  }

				  // Рецепт
				  if ($data['EvnRecept_IsNotOstat'] > 0) {
				  $filter .= " and ISNULL(ERG.EvnRecept_IsNotOstat, 1) = :EvnRecept_IsNotOstat";
				  $queryParams['EvnRecept_IsNotOstat'] = $data['EvnRecept_IsNotOstat'];
				  } */

				// Рецепт
				if (strlen($data['EvnRecept_Num']) > 0) {
					$filter .= " and ERG.EvnReceptGeneral_Num = :EvnReceptGeneral_Num";
					$queryParams['EvnReceptGeneral_Num'] = $data['EvnRecept_Num'];
				}

				// Рецепт
				if (strlen($data['EvnRecept_Ser']) > 0) {
					$filter .= " and ERG.EvnReceptGeneral_Ser = :EvnReceptGeneral_Ser";
					$queryParams['EvnReceptGeneral_Ser'] = $data['EvnRecept_Ser'];
				}


				// Рецепт
				if (isset($data['EvnRecept_setDate'])) {
					$queryParams['EvnReceptGeneral_setDate'] = $data['EvnRecept_setDate'];
				}

				// Рецепт
				if (isset($data['EvnRecept_setDate_Range'][0])) {
					$queryParams['EvnReceptGeneral_setDate_Range_0'] = $data['EvnRecept_setDate_Range'][0];
				}

				// Рецепт
				if (isset($data['EvnRecept_setDate_Range'][1])) {
					$queryParams['EvnReceptGeneral_setDate_Range_1'] = $data['EvnRecept_setDate_Range'][1];
				}
				switch ($data['EvnReceptSearchDateType']) {
					case 'vypis':
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ERG.EvnReceptGeneral_setDate = cast(:EvnReceptGeneral_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ERG.EvnReceptGeneral_setDate >= cast(:EvnReceptGeneral_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ERG.EvnReceptGeneral_setDate <= cast(:EvnReceptGeneral_setDate_Range_1 as datetime)";
						}
						break;
					case 'obr':
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ERG.EvnReceptGeneral_obrDT = cast(:EvnReceptGeneral_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ERG.EvnReceptGeneral_obrDT >= cast(:EvnReceptGeneral_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ERG.EvnReceptGeneral_obrDT <= cast(:EvnReceptGeneral_setDate_Range_1 as datetime)";
						}
						break;
					case 'obesp':
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ERG.EvnReceptGeneral_otpDT = cast(:EvnReceptGeneral_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ERG.EvnReceptGeneral_otpDT >= cast(:EvnReceptGeneral_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ERG.EvnReceptGeneral_otpDT <= cast(:EvnReceptGeneral_setDate_Range_1 as datetime)";
						}
						break;

					default:
						// Рецепт
						if (isset($data['EvnRecept_setDate'])) {
							$filter .= " and ERG.EvnReceptGeneral_setDate = cast(:EvnReceptGeneral_setDate as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][0])) {
							$filter .= " and ERG.EvnReceptGeneral_setDate >= cast(:EvnReceptGeneral_setDate_Range_0 as datetime)";
						}

						// Рецепт
						if (isset($data['EvnRecept_setDate_Range'][1])) {
							$filter .= " and ERG.EvnReceptGeneral_setDate <= cast(:EvnReceptGeneral_setDate_Range_1 as datetime)";
						}
						break;
				}

				/* // Рецепт (доп.)
				  if (isset($data['OrgFarmacy_id'])) {
				  $queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

				  if ($data['OrgFarmacy_id'] == -1) {
				  $filter .= " and ERG.OrgFarmacy_id is null";
				  } else {
				  $filter .= " and ERG.OrgFarmacy_id = :OrgFarmacy_id";
				  }
				  } */

				// Рецепт (доп.)
				if (isset($data['OrgFarmacyIndex_OrgFarmacy_id'])) {
					$queryParams['OrgFarmacyIndex_OrgFarmacy_id'] = $data['OrgFarmacyIndex_OrgFarmacy_id'];

					if ($data['OrgFarmacyIndex_OrgFarmacy_id'] > 0) {
						$filter .= " and ERG.Lpu_id in (select Lpu_id from v_OrgFarmacyIndex with(nolock) where OrgFarmacy_id = :OrgFarmacyIndex_OrgFarmacy_id)";
					}
				}

				// Рецепт (доп.)
				/* if ($data['ReceptDiscount_id'] > 0) {
				  $filter .= " and ERG.ReceptDiscount_id = :ReceptDiscount_id";
				  $queryParams['ReceptDiscount_id'] = $data['ReceptDiscount_id'];
				  }

				  // Рецепт (доп.)
				  if ($data['ReceptFinance_id'] > 0) {
				  $filter .= " and ERG.ReceptFinance_id = :ReceptFinance_id";
				  $queryParams['ReceptFinance_id'] = $data['ReceptFinance_id'];
				  }

				  // Рецепт (доп.)
				  if ($data['ReceptType_id'] > 0) {
				  $filter .= " and ERG.ReceptType_id = :ReceptType_id";
				  $queryParams['ReceptType_id'] = $data['ReceptType_id'];
				  }

				  // Рецепт (доп.)
				  if ($data['ReceptValid_id'] > 0) {
				  $filter .= " and ERG.ReceptValid_id = :ReceptValid_id";
				  $queryParams['ReceptValid_id'] = $data['ReceptValid_id'];
				  }

				  // Рецепт (доп.)
				  if (isset($data['EvnRecept_IsExtemp'])) {
				  $filter .= " and ISNULL(ERG.EvnRecept_IsExtemp, 1) = :EvnRecept_IsExtemp";
				  $queryParams['EvnRecept_IsExtemp'] = $data['EvnRecept_IsExtemp'];
				  } */

				if (!(isset($data['inValidRecept']) && $data['inValidRecept'] == 1)) {
					$filter .= " and (
						(RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value) >= @getDT)) 
						or (RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
						)";
				}
				if (isset($data['ReceptDelayType_id']) && $data['ReceptDelayType_id'] != 7) {
					if ($data['ReceptDelayType_id'] != 6) {
						$filter .= " and ERG.ReceptDelayType_id = :ReceptDelayType_id";
						$queryParams['ReceptDelayType_id'] = $data['ReceptDelayType_id'];
					} else {
						$filter .= " and ERG.ReceptDelayType_id is null and (
						(RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value) >= @getDT)) 
						or (RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30)) >= @getDT))
						) and not exists(select top 1 RO.ReceptOtov_id from v_ReceptOtov RO with (nolock) where RO.EvnRecept_id = ERG.EvnReceptGeneral_id)";
					}
				}

				if (isset($data['Drug_Name'])) {
					$filter .= " and ((DCM.DrugComplexMnn_RusName like :Drug_Name) or (ERDrug.Drug_Name like :Drug_Name) or (ERDrugRls.Drug_Name like :Drug_Name))";
					$queryParams['Drug_Name'] = '%' . $data['Drug_Name'] . '%';
				}
				break;

			case 'EvnUslugaPar':
				//#164327
				$lpuIdFilter = " and EUP.Lpu_id " . $this->getLpuIdFilter($data);
				if (getRegionNick() == 'penza' && havingGroup('OuzSpec')) {
					$lpuIdFilter = '';
					if (isset($data['EUPSWLpu_id'])) {
						$filter .= " and lpu.Lpu_id = :EUPSWLpu_id";
						$queryParams['EUPSWLpu_id'] = $data['EUPSWLpu_id'];
					} else {
						$filter .= " and lpu.Region_id = 58";
					}
				}

				if ($data['PersonPeriodicType_id'] == 2) {
					$query .= " inner join v_EvnUslugaPar EUP with (nolock) on EUP.Server_id = PS.Server_id and EUP.PersonEvn_id = PS.PersonEvn_id" . $lpuIdFilter;
				} else {
					$query .= " inner join v_EvnUslugaPar EUP with (nolock) on EUP.Person_id = PS.Person_id" . $lpuIdFilter;
				}

				$query .= "
					left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EUP.EvnUslugaPar_id
					left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EUP.LpuSection_uid
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = LS.Lpu_id
					outer apply (
						select top 1 Person_Fio
						from v_MedPersonal with (nolock)
						where MedPersonal_id = EUP.MedPersonal_id
					) MP
					left join Usluga with (nolock) on Usluga.Usluga_id = EUP.Usluga_id
					left join UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id
					left join v_Evn EvnParent with(nolock) on EvnParent.Evn_id = EUP.EvnUslugaPar_pid
					left join v_PayType PT with (nolock) on PT.PayType_id = EUP.PayType_id
					left join v_MedStaffFact MedStaffFact with(nolock) on MedStaffFact.MedStaffFact_id = EUP.MedStaffFact_id
					left join v_PostMed PostMed (nolock) on PostMed.PostMed_id = MedStaffFact.Post_id
					left join v_EvnDirection_all ED with (nolock) on EUP.EvnDirection_id = ED.EvnDirection_id
					left join v_Lpu as LD on LD.Lpu_id = ED.Lpu_sid
					LEFT JOIN v_Lpu L2 (NOLOCK) ON L2.Lpu_id = EUP.Lpu_did -- NGS
					LEFT JOIN v_Lpu L (NOLOCK) ON L.Lpu_id = ED.Lpu_sid -- NGS
					left join v_Lpu LD_sid with (nolock) on LD_sid.Lpu_id = ED.Lpu_sid
					left join Org with (nolock) on Org.Org_id = ED.Org_sid
					left join v_Lpu v_Lpu_org_1 with (nolock) on v_Lpu_org_1.Org_id = EUP.Org_did
					left join v_Lpu v_Lpu_org_2 with (nolock) on v_Lpu_org_2.Lpu_id = EUP.Lpu_did
					left join Org as Org_3 with (nolock) on Org_3.Org_id = EUP.Org_did
				";
				if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
					$query .= "outer apply (
						select top 1 EvnXml.EvnXml_id, xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate
						from v_EvnXml  EvnXml with (NOLOCK)
						left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc -- костыль, должен быть только один протокол
					) doc";
				} else {
					$query .= "outer apply (
						select top 1 EvnXml.EvnXml_id
						from v_EvnXml  EvnXml with (NOLOCK)
						where Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc -- костыль, должен быть только один протокол
					) doc";
				}

				if (!empty($data['UslugaExecutionType_id'])) {
					$query .= "
						cross apply (
							SELECT TOP 1
								EvnLabRequest_id
							FROM 
								v_EvnLabRequestUslugaComplex ELRUC (nolock)
							WHERE
								ELRUC.EvnUslugaPar_id = EUP.EvnUslugaPar_id
						) ELRUC
						inner join v_EvnLabRequest (nolock) ELR on ELR.EvnLabRequest_id = ELRUC.EvnLabRequest_id
					";
					$filter .= ' and ISNULL(ELR.UslugaExecutionType_id,4) = :UslugaExecutionType_id';
					$queryParams['UslugaExecutionType_id'] = $data['UslugaExecutionType_id'];

					if (in_array($data['UslugaExecutionType_id'], array(1,2))) {
						//Только выполненные услуги http://redmine.swan.perm.ru/issues/95609
						$filter .= " and EUP.EvnUslugaPar_setDate is not null";
					}
				}


				//Только основные услуги
                $evnUC_filter = "";
				$evnUC_filter .= " and (EvnParent.EvnClass_SysNick <> 'EvnUslugaPar' or EvnParent.EvnClass_SysNick is null)";

				// Услуга

				$filter .= ' and eup.EvnUslugaPar_setDate is not null';

				if (isset($data['EvnDirection_Num'])) {
					$filter .= " AND CASE WHEN ED.EvnDirection_id IS NOT NULL THEN ED.EvnDirection_Num ELSE EUP.EvnDirection_Num END  = :EvnDirection_Num"; // NGS
					//$filter .= " and ED.EvnDirection_Num = :EvnDirection_Num";
					$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
				}

				// Услуга
				if (isset($data['EvnDirection_setDate'])) {
					$filter .= " and ED.EvnDirection_setDT = :EvnDirection_setDate";
					$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
				}

				// Услуга
				if (isset($data['EvnUslugaPar_setDate_Range'][0])) {
					$filter .= " AND CAST((CASE WHEN ED.EvnDirection_id IS NOT NULL THEN ED.EvnDirection_setDT ELSE EUP.EvnDirection_setDT END) AS DATE) 
					>= CAST(:EvnUslugaPar_setDate_Range_0 AS DATE)"; // NGS
					//$filter .= " and cast(EUP.EvnUslugaPar_setDT as date) >= cast(:EvnUslugaPar_setDate_Range_0 as date)";
					$queryParams['EvnUslugaPar_setDate_Range_0'] = $data['EvnUslugaPar_setDate_Range'][0];
				}

				// Услуга
				if (isset($data['EvnUslugaPar_setDate_Range'][1])) {
					$filter .= " AND CAST((CASE WHEN ED.EvnDirection_id IS NOT NULL THEN ED.EvnDirection_setDT ELSE EUP.EvnDirection_setDT END) AS DATE) 
					<=  CAST(:EvnUslugaPar_setDate_Range_1 AS DATE)"; // NGS
					//$filter .= " and cast(EUP.EvnUslugaPar_setDT as date) <= cast(:EvnUslugaPar_setDate_Range_1 as date)";
					$queryParams['EvnUslugaPar_setDate_Range_1'] = $data['EvnUslugaPar_setDate_Range'][1];
				}

				// Услуга
				if (isset($data['LpuSection_uid'])) {
					$filter .= " and EUP.LpuSection_uid = :LpuSection_uid";
					$queryParams['LpuSection_uid'] = $data['LpuSection_uid'];
				}

				// Услуга
				if (isset($data['LpuSection_did'])) {
					$filter .= " AND CASE WHEN ED.LpuSection_id IS NOT NULL THEN ED.LpuSection_id ELSE EUP.LpuSection_did END = :LpuSection_did"; //NGS
					$queryParams['LpuSection_did'] = $data['LpuSection_did'];
				}

				// Услуга
				if (isset($data['MedPersonal_did'])) {
					$filter .= " AND CASE WHEN ED.MedPersonal_id IS NOT NULL THEN ED.MedPersonal_id ELSE EUP.MedPersonal_did END = :MedPersonal_did"; //NGS
					//$filter .= " and EUP.MedPersonal_did = :MedPersonal_did"; 
					$queryParams['MedPersonal_did'] = $data['MedPersonal_did'];
				}

				// Услуга
				if (isset($data['MedPersonal_uid'])) {
					$filter .= " and EUP.MedPersonal_id = :MedPersonal_uid";
					$queryParams['MedPersonal_uid'] = $data['MedPersonal_uid'];
				}

				// Услуга
				if (isset($data['PayType_id'])) {
					$filter .= " and EUP.PayType_id = :PayType_id";
					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Услуга
				if (isset($data['PrehospDirect_id'])) {
					$filter .= " AND CASE WHEN ED.PrehospDirect_id IS NOT NULL THEN ED.PrehospDirect_id ELSE EUP.PrehospDirect_id END = :PrehospDirect_id"; // NGS
					//$filter .= " and ISNULL(ED.PrehospDirect_id, EUP.PrehospDirect_id) = :PrehospDirect_id";
					$queryParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
				}

				// Услуга
				if (isset($data['UslugaCategory_id'])) {
					$filter .= " and UslugaComplex.UslugaCategory_id = :UslugaCategory_id";
					$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
				}

				// Комплексная услуга
				if (isset($data['UslugaComplex_id'])) {
                    $evnUC_filter .= " and EUP.UslugaComplex_id = :UslugaComplex_id";
					$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
				}
				// органицация
				if (isset($data['Org_did'])) {
					$filter .= " and ISNULL(LD_sid.Org_id, EUP.Org_did) = :Org_did";
					$queryParams['Org_did'] = $data['Org_did'];
				}


				if ($this->regionNick == 'kz') {
					$query .= " left join r101.AISResponse air (nolock) on air.Evn_id = EUP.EvnUslugaPar_id  and air.AISFormLoad_id = 1 ";
					$query .= " left join r101.AISResponse air9 (nolock) on air9.Evn_id = EUP.EvnUslugaPar_id  and air9.AISFormLoad_id = 2 ";
					$query .= " left join r101.AISUslugaComplexLink ucl (nolock) on ucl.UslugaComplex_id = EUP.UslugaComplex_id ";
					if ($data['toAis25'] == 2) {
						$filter .= " and air.AISResponse_id is not null";
					} elseif ($data['toAis25'] == 1) {
						$filter .= " and air.AISResponse_id is null and ucl.AISFormLoad_id = 1";
					}
					if ($data['toAis259'] == 2) {
						$filter .= " and air9.AISResponse_id is not null";
					} elseif ($data['toAis259'] == 1) {
						$filter .= " and air9.AISResponse_id is null and ucl.AISFormLoad_id = 2";
					}
				}

                if (!empty($data['Part_of_the_study']) && $data['Part_of_the_study'] && !empty($data['UslugaComplex_id'])) {

                    $uc_filter = "";
                    $uc_filter.= "
                    UNION ALL";
                    $uc_filter.= $query;
                    $uc_filter.= "
                       where (1=1)
					-- where
					    and EUP.EvnUslugaPar_id in (
					    SELECT eup2.EvnUslugaPar_rid 
					    FROM v_EvnUslugaPar eup2 
					    LEFT JOIN v_UslugaTest ut with (nolock) on ut.UslugaTest_pid = eup2.evnuslugapar_id
					    WHERE ut.UslugaComplex_id = :UslugaComplex_id)
					    and";
                }


				/*
				  // Услуга
				  if ( isset($data['UslugaPlace_id']) ) {
				  $filter .= " and EUP.UslugaPlace_id = :UslugaPlace_id";
				  $queryParams['UslugaPlace_id'] = $data['UslugaPlace_id'];
				  }
				 */
				break;

			case 'PersonCardStateDetail':
				// полный пиздец и содомия с 4441 по 4461 строки
				$query .= "
					-- смысл в том что v_PersonCard_All возвращает не те данные... пришлось сделать как в исходном запросе
					cross apply (
						select
							max(Personcard_id) as Personcard_id
						from v_PersonCard_all pc with (nolock)
						left join v_LpuRegion lr (nolock) on lr.LpuRegion_id = pc.LpuRegion_id
						WHERE pc.Lpu_id = :Lpu_id and pc.Person_id = PS.Person_id
						group by
							pc.Personcard_Code,
							pc.LpuRegion_id,
							pc.Person_id,
							pc.Server_id,
							pc.Lpu_id,
							pc.LpuAttachType_id,
							pc.PersonCard_begDate,
							pc.PersonCard_endDate
					) as PCSD1
					inner join v_PersonCard_all PCSD with (nolock) on PCSD1.PersonCard_id = PCSD.PersonCard_id
					left join v_PersonState pcc (nolock) on pcc.Person_id = PCSD.Person_id
					--участок по ОМС
					left join v_LpuRegion lr (nolock) on lr.LpuRegion_id = PCSD.LpuRegion_id
					left join LpuRegionType lrt (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
					--inner join v_PersonCard_All PCSD with (nolock) on PCSD.Person_id = PS.Person_id
					left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
					-- заменил на outer apply, потому что двоилось из-за беспорядка в базе
					outer apply (
						select top 1
							pc1.LpuRegion_Name,
							lp.Lpu_Nick
						from
							v_PersonCard pc1 with (nolock)
							inner join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
						where
							PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
						order by
							pc1.PersonCard_Begdate
					) as attcard
					--left join v_PersonCard pc1 with (nolock) on PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
					--left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
					--left join PersonState ps1 with (nolock) on ps1.Person_id = PCSD.Person_id
					left join Address with (nolock) on ps.PAddress_id = Address.Address_id
					left join Address Address1 with (nolock) on ps.UAddress_id = Address1.Address_id
					-- текущий полис
					left join v_Polis pols with (nolock) on pols.Polis_id = PS.Polis_id and cast(pols.Polis_begDate as date) < cast(@getDT as date) and ( pols.Polis_EndDate is null or cast(Pols.Polis_endDate as date) > cast(@getDT as date) )
					left join v_OrgSmo omsOrgSmo with (nolock) on pols.PolisType_id in (1,4) and pols.OrgSmo_id = omsOrgSmo.OrgSmo_id
					--для СМО по ДМС
					left join v_PersonCard dmspc with (nolock) on dmspc.Lpu_id = :Lpu_id 
						and dmspc.Person_id = PS.Person_id 
						and  dmspc.LpuAttachType_id = 5 
						and cast(dmspc.PersonCard_begDate as date) < cast(@getDT as date) 
						and ( dmspc.PersonCard_endDate is null or cast(dmspc.PersonCard_endDate as date) > cast(@getDT as date) )
					left join v_OrgSmo dmsOrgSmo with (nolock) on dmspc.OrgSmo_id = dmsOrgSmo.OrgSmo_id
					  outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								/*and Person.Server_pid = 0*/
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < :PCSD_begDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) >= :PCSD_begDate) )
                        ) as PolisBeg
                        outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								/*and Person.Server_pid = 0*/
								and cast(Polis.Polis_begDate as date) <= :PCSD_endDate
								and ( Polis.Polis_endDate is null or (cast(Polis.Polis_endDate as date) > :PCSD_endDate) )
                        ) as PolisEnd
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id < PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id desc
                     ) as LastCard
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id,
								pclast.PersonCard_begDate
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id > PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id asc
                     ) as NextCard
				";

				if ($data['PCSD_LpuMotion_id'] == 2)
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
				if ($data['PCSD_LpuMotion_id'] == 3)
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";

				if (isset($data['PCSD_FromLpu_id']) && (int) $data['PCSD_FromLpu_id'] > 0) {
					$queryParams['FromLpu_id'] = (int) $data['PCSD_FromLpu_id'];
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
				}

				if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
					$queryParams['ToLpu_id'] = (int) $data['PCSD_ToLpu_id'];
					$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
				}

				if (isset($data['PCSD_LpuRegion_id'])) {
					$queryParams['PCSD_LpuRegion_id'] = $data['PCSD_LpuRegion_id'];
					$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
				} else {
					$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
				}

				if (isset($data['PCSD_LpuAttachType_id']) && $data['PCSD_LpuAttachType_id'] > 0) {
					$queryParams['PCSD_LpuAttachType_id'] = $data['PCSD_LpuAttachType_id'];
					$filter .= " and PCSD.LpuAttachType_id = :PCSD_LpuAttachType_id ";
				}

				/*
				 * @todo: Фильтры, Журнал движения РПН, Просмотр Деталей.
				 */
				switch ($data['PCSD_mode']) {
					case 'BegCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'BegCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (PolisBeg.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'BegCountNotInBDZ':
						$filter .= "
							and cast(PCSD.PersonCard_begDate as date) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (PolisBeg.Polis_id is null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCount':
						$filter .= "
							and cast(PCSD.PersonCard_begDate as date) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(PCSD.PersonCard_endDate as date) > :PCSD_endDate)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCountBDZ':
						$filter .= "
							and cast(PCSD.PersonCard_begDate as date) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(PCSD.PersonCard_endDate as date) > :PCSD_endDate)
							and (PolisEnd.Polis_id is not null)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCountNotInBDZ':
						$filter .= "
							and cast(PCSD.PersonCard_begDate as date) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(PCSD.PersonCard_endDate as date) > :PCSD_endDate)
							and (PolisEnd.Polis_id is null)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							and (Polis.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachIncomeBDZ':
						$filter .= "
							and cast(PCSD.PersonCard_begDate as date) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(PCSD.PersonCard_endDate as date) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and hasPolis.cnt is not null
							and notHasPolis.cnt is null
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachOutcomeBDZ':
						$filter .= "
							and cast(PCSD.PersonCard_begDate as date) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(PCSD.PersonCard_endDate as date) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and HasPolisBefore.cnt is not null
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'DettachCount':
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= "
								and cast(NextCard.PersonCard_begDate as date) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
							";
						} else {
							$filter .= "
								and cast(PCSD.PersonCard_endDate as date) between :PCSD_begDate and :PCSD_endDate
							";
						}
						break;
					case 'DettachCountBDZ':
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= "
								and cast(NextCard.PersonCard_begDate as date) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
								and (Polis.Polis_id is not null)
							";
						} else {
							$filter .= "
								and cast(PCSD.PersonCard_endDate as date) between :PCSD_begDate and :PCSD_endDate
								and (Polis.Polis_id is not null )
							";
						}
						break;
				}
				$queryParams['PCSD_begDate'] = $data['PCSD_StartDate'];
				$queryParams['PCSD_endDate'] = $data['PCSD_EndDate'];
				break;
			case 'PersonCardStateDetail_old':
				$query .= "
					inner join v_PersonCard_All PCSD with (nolock) on PCSD.Person_id = PS.Person_id
					left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
					-- заменил на outer apply, потому что двоилось из-за беспорядка в базе
					outer apply (
						select top 1
							pc1.LpuRegion_Name,
							lp.Lpu_Nick
						from
							v_PersonCard pc1 with (nolock)
							inner join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
						where
							PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
						order by
							pc1.PersonCard_Begdate
					) as attcard
					--left join v_PersonCard pc1 with (nolock) on PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
					--left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
					--left join PersonState ps1 with (nolock) on ps1.Person_id = PCSD.Person_id
					left join Address with (nolock) on ps.PAddress_id = Address.Address_id
					left join Address Address1 with (nolock) on ps.UAddress_id = Address1.Address_id
					-- текущий полис
					left join v_Polis pols with(nolock) on pols.Polis_id = PS.Polis_id and cast(convert(char(10), pols.Polis_begDate, 112) as datetime) < cast(convert(char(10), @getDT, 112) as datetime) and ( pols.Polis_EndDate is null or cast(convert(char(10), Pols.Polis_endDate, 112) as datetime) > cast(convert(char(10), @getDT, 112) as datetime) )
					  outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								and Person.Server_id = 0
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < :PCSD_begDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) >= :PCSD_begDate) )
                        ) as PolisBeg
                        outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								and Person.Server_id = 0
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) <= :PCSD_endDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) > :PCSD_endDate) )
                        ) as PolisEnd
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id < PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id desc
                     ) as LastCard
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id,
								pclast.PersonCard_begDate
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id > PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id asc
                     ) as NextCard
				";

				if ($data['PCSD_LpuMotion_id'] == 2)
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
				if ($data['PCSD_LpuMotion_id'] == 3)
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";

				if (isset($data['PCSD_FromLpu_id']) && (int) $data['PCSD_FromLpu_id'] > 0) {
					$queryParams['FromLpu_id'] = (int) $data['PCSD_FromLpu_id'];
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
				}

				if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
					$queryParams['ToLpu_id'] = (int) $data['PCSD_ToLpu_id'];
					$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
				}

				if (isset($data['PCSD_LpuRegion_id'])) {
					$queryParams['PCSD_LpuRegion_id'] = $data['PCSD_LpuRegion_id'];
					$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
				} else {
					$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
				}
				/*
				 * @todo: Фильтры, Журнал движения РПН, Просмотр Деталей.
				 */
				switch ($data['PCSD_mode']) {
					case 'BegCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'BegCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (PolisBeg.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
							and (PolisEnd.Polis_id is not null)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							and (Polis.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachIncomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and hasPolis.cnt is not null
							and notHasPolis.cnt is null
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachOutcomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and HasPolisBefore.cnt is not null
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'DettachCount':
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
							";
						} else {
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							";
						}
						break;
					case 'DettachCountBDZ':
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
								and (Polis.Polis_id is not null)
							";
						} else {
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
								and (Polis.Polis_id is not null )
							";
						}
						break;
				}
				$queryParams['PCSD_begDate'] = $data['PCSD_StartDate'];
				$queryParams['PCSD_endDate'] = $data['PCSD_EndDate'];
				break;
			case 'PersonCardStateDetail_old':
				$query .= "
					inner join v_PersonCard_All PCSD with (nolock) on PCSD.Person_id = PS.Person_id
					left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
					-- заменил на outer apply, потому что двоилось из-за беспорядка в базе
					outer apply (
						select top 1
							pc1.LpuRegion_Name,
							lp.Lpu_Nick
						from
							v_PersonCard pc1 with (nolock)
							inner join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
						where
							PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
						order by
							pc1.PersonCard_Begdate
					) as attcard
					--left join v_PersonCard pc1 with (nolock) on PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
					--left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
					--left join PersonState ps1 with (nolock) on ps1.Person_id = PCSD.Person_id
					left join Address with (nolock) on ps.PAddress_id = Address.Address_id
					left join Address Address1 with (nolock) on ps.UAddress_id = Address1.Address_id
					outer apply (
						select
							top 1 pall.Person_id as cnt
						from
							v_Person_all  pall  with (nolock)
							inner join v_Polis pol  with (nolock) on pall.Polis_id = pol.Polis_id
						where
							pall.Person_id = ps.Person_id
							and pall.Server_id = ps.Server_id
							and pol.Polis_begDate < :PCSD_begDate
							and ( pol.Polis_endDate is null or pol.Polis_endDate > :PCSD_begDate )
					) as notHasPolis
					-- полис открылся внутри периода
					outer apply (
						-- есть открытие полиса в рамках периода
						select
							top 1 pall.Person_id as cnt
						from
							v_Person_all pall  with (nolock)
							inner join v_Polis pol  with (nolock) on pall.Polis_id = pol.Polis_id
						where
							pall.Person_id = ps.Person_id
							and pall.Server_id = ps.Server_id
							and pol.Polis_begDate >= :PCSD_begDate
							and pol.Polis_begDate <= :PCSD_endDate
					) as hasPolis
					-- был полис, но до начала периода и он закрылся
					outer apply (
						select
							top 1 pall.Person_id as cnt
						from
							v_Person_all  pall  with (nolock)
							inner join v_Polis pol  with (nolock) on pall.Polis_id = pol.Polis_id
						where
							pall.Person_id = ps.Person_id
							and pall.Server_id = ps.Server_id
							and pol.Polis_begDate < :PCSD_begDate
							and pol.Polis_endDate >= :PCSD_begDate
							and pol.Polis_endDate <= :PCSD_endDate
					) as HasPolisBefore
					outer apply (
					   Select top 1 Polis.Polis_id
					   from
						v_Person_all Person (nolock)
						left join v_Polis Polis (nolock) on Person.Polis_id = Polis.Polis_id
					   where
						Person.Person_id = PS.Person_id
						and Person.Server_id = 0
						and ( cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < :PCSD_endDate
						and (Polis.Polis_endDate is null or cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) > :PCSD_endDate))
					  ) as Polis
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id < PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id desc
                     ) as LastCard
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id,
								pclast.PersonCard_begDate
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id > PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id asc
                     ) as NextCard
				";

				if ($data['PCSD_LpuMotion_id'] == 2)
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
				if ($data['PCSD_LpuMotion_id'] == 3)
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";

				if (isset($data['PCSD_FromLpu_id']) && (int) $data['PCSD_FromLpu_id'] > 0) {
					$queryParams['FromLpu_id'] = (int) $data['PCSD_FromLpu_id'];
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
				}

				if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
					$queryParams['ToLpu_id'] = (int) $data['PCSD_ToLpu_id'];
					$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
				}

				if (isset($data['PCSD_LpuRegion_id'])) {
					$queryParams['PCSD_LpuRegion_id'] = $data['PCSD_LpuRegion_id'];
					$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
				} else {
					$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
				}
				/*
				 * @todo: Фильтры, Журнал движения РПН, Просмотр Деталей.
				 */
				switch ($data['PCSD_mode']) {
					case 'BegCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'BegCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (Polis.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'EndCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
							and (Polis.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							and (Polis.Polis_id is not null )
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachIncomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and hasPolis.cnt is not null
							and notHasPolis.cnt is null
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'AttachOutcomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and HasPolisBefore.cnt is not null
						";
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= ' and 1 = 2 ';
						}
						break;
					case 'DettachCount':
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
							";
						} else {
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							";
						}
						break;
					case 'DettachCountBDZ':
						if (isset($data['PCSD_ToLpu_id']) && (int) $data['PCSD_ToLpu_id'] > 0) {
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
								and (Polis.Polis_id is not null)
							";
						} else {
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
								and (Polis.Polis_id is not null )
							";
						}
						break;
				}
				$queryParams['PCSD_begDate'] = $data['PCSD_StartDate'];
				$queryParams['PCSD_endDate'] = $data['PCSD_EndDate'];
				break;
			case 'PersonDisp':
				$sysNick = 'disp';
				switch (getRegionNumber()) {
					case '10':
						$sysNick = 'consulspec';
						break;
					case '201':
						$sysNick = 'dispdinnabl';
						break;
					case '3':
						$sysNick = 'desease';
						break;
					default:
						$sysNick = 'disp';
						break;
				}
				$lpu_id = (isset($_SESSION['lpu_id'])) ? $_SESSION['lpu_id'] : '';
				$query .= " inner join v_PersonDisp PD with (nolock) on PD.Person_id = PS.Person_id ";
				$query .= " left join v_Diag dg1 with (nolock) on PD.Diag_id = dg1.Diag_id ";
				$query .= " left join v_Diag dg2 with (nolock) on PD.Diag_pid = dg2.Diag_id ";
				$query .= " left join v_Diag dg3 with (nolock) on PD.Diag_nid = dg3.Diag_id ";
				$query .= " left join v_MedPersonal mp1 with (nolock) on PD.MedPersonal_id = mp1.MedPersonal_id and PD.Lpu_id = mp1.Lpu_id ";
				$query .= " left join LpuSection lpus1 with (nolock) on PD.LpuSection_id = lpus1.LpuSection_id ";
				$query .= " left join Sickness scks with (nolock) on PD.Sickness_id = scks.Sickness_id ";
				$query .= " left join v_Lpu lpu1 with (nolock) on PD.Lpu_id = lpu1.Lpu_id ";
				$query .= " outer apply(
					select top 1
						EVPL.EvnVizitPL_setDT as PersonDisp_LastDate
					from
						v_EvnVizitPL EVPL with(nolock)
						left join v_VizitType VT with(nolock) on VT.VizitType_id = EVPL.VizitType_id
					where
						VT.VizitType_SysNick='" . $sysNick . "'
						and cast(PD.PersonDisp_begDate as date)<=cast(EVPL.EvnVizitPL_setDT as date)
						and PD.Diag_id = EVPL.Diag_id
						and EVPL.Person_id = PD.Person_id
					order by
						EVPL.EvnVizitPL_setDT desc
				) LD";
				$query .= "	outer apply(
						select top 1 pdv.PersonDispVizit_NextFactDate from v_PersonDispVizit pdv with (nolock) where pdv.PersonDisp_id = PD.PersonDisp_id order by pdv.PersonDispVizit_NextFactDate desc
					)lapdv ";
				$query .= "	outer apply(
						select top 1 pdv.PersonDispVizit_NextDate from v_PersonDispVizit pdv with (nolock) where (pdv.PersonDisp_id = PD.PersonDisp_id) and (cast(pdv.PersonDispVizit_NextDate as date) >= cast(@getDT as date) and (pdv.PersonDispVizit_NextFactDate is null)) order by pdv.PersonDispVizit_NextDate asc
					)oapdv ";
				if (!empty($data['session']['lpu_id'])) {
					$query .= " outer apply (
							select top 1 PersonCard_id,
							LpuRegion_Name
							from v_PersonCard_all where Person_id = PS.Person_id and LpuAttachType_id = 1 and Lpu_id = " . $data['session']['lpu_id'] . " order by PersonCard_begDate desc
						) PCA";
				} else {
					$query .= " outer apply (
							select 
								null as  PersonCard_id,
								null as LpuRegion_Name
						) PCA";
				}
				$query .= " outer apply (
					select
						max(
							case when 
								convert(varchar(10), PersonDispMedicament_begDate, 112) <= convert(varchar(10), @getDT, 112)
								and (
									convert(varchar(10), PersonDispMedicament_endDate, 112) >= convert(varchar(10), @getDT, 112)
									or PersonDispMedicament_endDate is null
									)
							then 1 
							else 
								case when 
									convert(varchar(10), PersonDispMedicament_begDate, 112) > convert(varchar(10), @getDT, 112)
								then 
									0 
								else 
									NULL 
								end
							end
						) as isnoz
					from
						PersonDispMedicament pdm  with (nolock)
					where
						pdm.PersonDisp_id = PD.PersonDisp_id
						and pdm.PersonDispMedicament_begDate is not null
				) as noz ";

				$query .= "
					outer apply(
						select top 1 
							MP_L.MedPersonal_id as MedPersonal_id_last,
							MP_L.Person_Fio as MedPersonal_FIO_last
						from v_PersonDispHist PDH_L (nolock)
						left join v_MedPersonal MP_L (nolock) on MP_L.MedPersonal_id = PDH_L.MedPersonal_id
						where PDH_L.PersonDisp_id = PD.PersonDisp_id
						order by PDH_L.PersonDispHist_begDate desc					
					) mph_last
				";

				if(!empty($data['session']['lpu_id']))
					$filter .= " and PD.Lpu_id = " . $data['session']['lpu_id'] . " ";

				// Отображать карты ДУ
				if (isset($data['ViewAll_id']) && $data['ViewAll_id'] == 1)
					$filter .= " and ( PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > @getDT) ";
				// Отделение
				if (isset($data['DispLpuSection_id'])) {
					$filter .= " and PD.LpuSection_id = :DispLpuSection_id ";
					$queryParams['DispLpuSection_id'] = $data['DispLpuSection_id'];
				}
				if (isset($data['DispLpuSectionProfile_id'])) {
					$filter .= " and lpus1.LpuSectionProfile_id = :DispLpuSectionProfile_id ";
					$queryParams['DispLpuSectionProfile_id'] = $data['DispLpuSectionProfile_id'];
				}
				// Врач
				if (isset($data['DispMedPersonal_id'])) {
					$filter .= " and PD.MedPersonal_id = :DispMedPersonal_id ";
					$queryParams['DispMedPersonal_id'] = $data['DispMedPersonal_id'];
				}
				if (isset($data['HistMedPersonal_id'])) {
					$and_filter = '';
					if (empty($data['checkMPHistory'])) {
						$and_filter = ' and PerDH.MedPersonal_id = mph_last.MedPersonal_id_last';
					}
					$filter .= "
						and exists (
							select top 1 1 
							from v_PersonDispHist PerDH (nolock)
							where PerDH.PersonDisp_id = PD.PersonDisp_id
							and PerDH.MedPersonal_id = :HistMedPersonal_id
							{$and_filter}
						)
					";
					$queryParams['HistMedPersonal_id'] = $data['HistMedPersonal_id'];
				}
				// Дата постановки
				if (isset($data['PersonDisp_begDate'])) {
					$filter .= " and PD.PersonDisp_begDate = cast(:PersonDisp_begDate as datetime) ";
					$queryParams['PersonDisp_begDate'] = $data['PersonDisp_begDate'];
				}
				if (isset($data['PersonDisp_begDate_Range'][0])) {
					$filter .= " and PD.PersonDisp_begDate >= cast(:PersonDisp_begDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_begDate_Range_0'] = $data['PersonDisp_begDate_Range'][0];
				}
				if (isset($data['PersonDisp_begDate_Range'][1])) {
					$filter .= " and PD.PersonDisp_begDate <= cast(:PersonDisp_begDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_begDate_Range_1'] = $data['PersonDisp_begDate_Range'][1];
				}
				// Дата снятия
				if (isset($data['PersonDisp_endDate'])) {
					$filter .= " and PD.PersonDisp_endDate = cast(:PersonDisp_endDate as datetime) ";
					$queryParams['PersonDisp_endDate'] = $data['PersonDisp_endDate'];
				}
				if (isset($data['PersonDisp_endDate_Range'][0])) {
					$filter .= " and PD.PersonDisp_endDate >= cast(:PersonDisp_endDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_endDate_Range_0'] = $data['PersonDisp_endDate_Range'][0];
				}
				if (isset($data['PersonDisp_endDate_Range'][1])) {
					$filter .= " and PD.PersonDisp_endDate <= cast(:PersonDisp_endDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_endDate_Range_1'] = $data['PersonDisp_endDate_Range'][1];
				}
				// Дата следующего посещения
				if (isset($data['PersonDisp_NextDate'])) {
					$filter .= " and cast(isnull(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate) as date) = cast(:PersonDisp_NextDate as date) ";
					$queryParams['PersonDisp_NextDate'] = $data['PersonDisp_NextDate'];
				}
				if (isset($data['PersonDisp_NextDate_Range'][0])) {
					$filter .= " and cast(isnull(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate) as date) >= cast(:PersonDisp_NextDate_Range_0 as date) ";
					$queryParams['PersonDisp_NextDate_Range_0'] = $data['PersonDisp_NextDate_Range'][0];
				}
				if (isset($data['PersonDisp_NextDate_Range'][1])) {
					$filter .= " and cast(isnull(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate) as date) <= cast(:PersonDisp_NextDate_Range_1 as date) ";
					$queryParams['PersonDisp_NextDate_Range_1'] = $data['PersonDisp_NextDate_Range'][1];
				}
				// Дата последней явки
				if (isset($data['PersonDisp_LastDate'])) {
					$filter .= " and cast(isnull(LD.PersonDisp_LastDate,lapdv.PersonDispVizit_NextFactDate) as date) = cast(:PersonDisp_LastDate as date) ";
					$queryParams['PersonDisp_LastDate'] = $data['PersonDisp_LastDate'];
				}
				if (isset($data['PersonDisp_LastDate_Range'][0])) {
					$filter .= " and cast(isnull(LD.PersonDisp_LastDate,lapdv.PersonDispVizit_NextFactDate) as date) >= cast(:PersonDisp_LastDate_Range_0 as date) ";
					$queryParams['PersonDisp_LastDate_Range_0'] = $data['PersonDisp_LastDate_Range'][0];
				}
				if (isset($data['PersonDisp_LastDate_Range'][1])) {
					$filter .= " and cast(isnull(LD.PersonDisp_LastDate,lapdv.PersonDispVizit_NextFactDate) as date) <= cast(:PersonDisp_LastDate_Range_1 as date) ";
					$queryParams['PersonDisp_LastDate_Range_1'] = $data['PersonDisp_LastDate_Range'][1];
				}

				//Закрыта автоматически - https://redmine.swan.perm.ru/issues/72643
				if (isset($data['PersonDisp_IsAutoClose']) && $data['PersonDisp_IsAutoClose'] == 'on') {
					$filter .= " and PD.PersonDisp_IsAutoClose = 2";
				}
				// Причина снятия
				if (isset($data['DispOutType_id'])) {
					$filter .= " and PD.DispOutType_id = :DispOutType_id ";
					$queryParams['DispOutType_id'] = $data['DispOutType_id'];
				}
				// По результатам доп. дисп.
				if (isset($data['PersonDisp_IsDop'])) {
					$filter .= " and isnull(PD.PersonDisp_IsDop, 1) = :PersonDisp_IsDop ";
					$queryParams['PersonDisp_IsDop'] = $data['PersonDisp_IsDop'];
				}
				// По результатам профосмотров
				if (isset($data['DiagDetectType'])) {
					$filter .= " and isnull(PD.DiagDetectType_id, 1) = :DiagDetectType ";
					$queryParams['DiagDetectType'] = $data['DiagDetectType'];
				}
				// диагнозы
				if (isset($data['Sickness_id'])) {
					$filter .= " and PD.Sickness_id = :Sickness_id ";
					$queryParams['Sickness_id'] = $data['Sickness_id'];
				}
				if (isset($data['Disp_Diag_id'])) {
					$filter .= " and PD.Diag_id = :Disp_Diag_id ";
					$queryParams['Disp_Diag_id'] = $data['Disp_Diag_id'];
				}
				if (strlen($data['Disp_Diag_Code_From']) > 0) {
					$filter .= " and dg1.Diag_Code >= :Disp_Diag_Code_From";
					$queryParams['Disp_Diag_Code_From'] = $data['Disp_Diag_Code_From'];
				}
				if (strlen($data['Disp_Diag_Code_To']) > 0) {
					$filter .= " and dg1.Diag_Code <= :Disp_Diag_Code_To";
					$queryParams['Disp_Diag_Code_To'] = $data['Disp_Diag_Code_To'];
				}

				if (isset($data['Disp_Diag_pid'])) {
					$filter .= " and PD.Diag_pid = :Disp_Diag_pid ";
					$queryParams['Disp_Diag_pid'] = $data['Disp_Diag_pid'];
				}
				if (strlen($data['Disp_PredDiag_Code_From']) > 0) {
					$filter .= " and dg2.Diag_Code >= :Disp_PredDiag_Code_From";
					$queryParams['Disp_PredDiag_Code_From'] = $data['Disp_PredDiag_Code_From'];
				}
				if (strlen($data['Disp_PredDiag_Code_To']) > 0) {
					$filter .= " and dg2.Diag_Code <= :Disp_PredDiag_Code_To";
					$queryParams['Disp_PredDiag_Code_To'] = $data['Disp_PredDiag_Code_To'];
				}

				if (isset($data['Disp_Diag_nid'])) {
					$filter .= " and PD.Diag_nid = :Disp_Diag_nid ";
					$queryParams['Disp_Diag_nid'] = $data['Disp_Diag_nid'];
				}
				if (strlen($data['Disp_NewDiag_Code_From']) > 0) {
					$filter .= " and dg3.Diag_Code >= :Disp_NewDiag_Code_From";
					$queryParams['Disp_NewDiag_Code_From'] = $data['Disp_NewDiag_Code_From'];
				}
				if (strlen($data['Disp_NewDiag_Code_To']) > 0) {
					$filter .= " and dg3.Diag_Code <= :Disp_NewDiag_Code_To";
					$queryParams['Disp_NewDiag_Code_To'] = $data['Disp_NewDiag_Code_To'];
				}
				
				if(!empty($data['PersonCardStateType_id'])){
					$wherePersonCard = "";
					$joinPersonCard = "";
					$filter .= ' and PC.PersonCard_id is not null';
					
					if (!empty($data['AttachLpu_id'])) {
						$wherePersonCard .= " and PC.Lpu_id  = :AttachLpu_id";
						$queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
					}
					if(!empty($data['LpuAttachType_id'])){
						$wherePersonCard .= " and PC.LpuAttachType_id  = :LpuAttachType_id";
						$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
					}
					if(!empty($data['LpuRegionType_id'])){
						$wherePersonCard .= " and LR.LpuRegionType_id  = :LpuRegionType_id";
						$queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
					}
					if (isset($data['LpuRegion_id'])) {
						$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
						if ($data['LpuRegion_id'] == -1) {
							$wherePersonCard .= " and LR.LpuRegion_id is null";
						} else {
							$wherePersonCard .= " and LR.LpuRegion_id = :LpuRegion_id";
						}
					}
					if(isset($data['LpuRegion_Fapid'])){
						$queryParams['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
						$wherePersonCard .= " and LR_Fap.LpuRegion_id = :LpuRegion_Fapid";
					}
					if (isset($data['PersonCard_begDate'])) {
						$wherePersonCard .= " and cast(PC.PersonCard_begDate as date) = :PersonCard_begDate";
						$queryParams['PersonCard_begDate'] = $data['PersonCard_begDate'];
					}
					if (isset($data['PersonCard_endDate'])) {
						$wherePersonCard .= " and PC.PersonCard_endDate = :PersonCard_endDate";
						$queryParams['PersonCard_endDate'] = $data['PersonCard_endDate'];
					}
					if (isset($data['PersonCard_begDate_Range'][0])) {
						$wherePersonCard .= " and PC.PersonCard_begDate >= cast(:PersonCard_begDate_Range_0 as datetime)";
						$queryParams['PersonCard_begDate_Range_0'] = $data['PersonCard_begDate_Range'][0];
					}
					if (isset($data['PersonCard_begDate_Range'][1])) {
						$wherePersonCard .= " and PC.PersonCard_begDate <= cast(:PersonCard_begDate_Range_1 as datetime)";
						$queryParams['PersonCard_begDate_Range_1'] = $data['PersonCard_begDate_Range'][1];
					}
					if ($data['PersonCard_IsAttachCondit'] > 0) {
						$wherePersonCard .= " and ISNULL(PC.PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
						$queryParams['PersonCard_IsAttachCondit'] = $data['PersonCard_IsAttachCondit'];
					}
					if (!empty($data['PersonCard_IsDms']) && $data['PersonCard_IsDms'] > 0) {
						$wherePersonCard .= " and PC.LpuAttachType_id = 5 and PC.PersonCard_endDate >= @getDT and PC.CardCloseCause_id is null";
					}
					if ($data['PersonCardStateType_id'] == 1){
						$query .= "
							outer apply (
								select top 1
									pc.*
								from v_PersonCard pc with (nolock)
									left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
									{$joinPersonCard}
								where
									pc.Person_id = ps.Person_id
									and LpuAttachType_id is not null
									{$wherePersonCard}
								order by PersonCard_begDate desc
							) as PC
						";
					}else{
						$query .= "
							outer apply (
								select top 1
									pc.*
								from v_PersonCard_all pc with (nolock)
									left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
									{$joinPersonCard}
								where
									pc.Person_id = ps.Person_id
									{$wherePersonCard}
								order by PersonCard_begDate desc
							) as PC
						";
					}
				}
				break;

			case 'EvnInfectNotify':
				$query .= " inner join v_EvnInfectNotify EIN with (nolock) on " . ( ($data['PersonPeriodicType_id'] == 2) ? 'EIN.PersonEvn_id = PS.PersonEvn_id and EIN.Server_id = PS.Server_id' : 'EIN.Person_id = PS.Person_id' );
				$query .= " left join v_EvnVizitPL EVPL with (nolock) on EIN.EvnInfectNotify_pid = EVPL.EvnVizitPL_id ";
				$query .= " left join v_EvnSection ES with (nolock) on EIN.EvnInfectNotify_pid = ES.EvnSection_id ";
				$query .= " 					
					outer apply (
						select TOP 1
							Lpu_id
						from 
							v_PersonCard pc with (nolock)
						WHERE 
							pc.Person_id = PS.Person_id
					) as PC ";
				$query .= " left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id ";
				$query .= " left join v_Diag Diag with (nolock) on Diag.Diag_id = EVPL.Diag_id ";
				$query .= " left join v_Diag Diag1 with (nolock) on Diag1.Diag_id = ES.Diag_id ";
				$query .= " left join v_MorbusDiag MD with(nolock) on MD.Diag_id = ISNULL(Diag.Diag_id, Diag1.Diag_id) ";
				$query .= " left join v_MorbusType MT with(nolock) on MT.MorbusType_id = MD.MorbusType_id ";

				if (isset($data['Diag_Code_From'])) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From or Diag1.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To or Diag1.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= " and EIN.EvnInfectNotify_insDT >= cast(:EvnInfectNotify_insDT_Range_0 as datetime) ";
					$queryParams['EvnInfectNotify_insDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0] . ' 00:00:00';
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= " and EIN.EvnInfectNotify_insDT <= cast(:EvnInfectNotify_insDT_Range_1 as datetime) ";
					$queryParams['EvnInfectNotify_insDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1] . ' 23:59:59';
				}

				if (!havingGroup('HIVRegistry')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'hiv'";
				}
				if (!havingGroup('HepatitisRegistry')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'hepa'";
				}
				if (!havingGroup('TubRegistry')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'tub'";
				}
				if (!havingGroup('NephroRegistry')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'nephro'";
				}
				if (!havingGroup('ProfRegistry')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'prof'";
				}
				if (!havingGroup('IBSRegistry')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'ibs'";
				}
				if (havingGroup('NarkoMORegistry')) {
					$filter .= " and EIN.Lpu_id " . $this->getLpuIdFilter($data);
				} else if (!havingGroup(array('NarkoRegistry', 'NarkoMORegistry'))) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'narc'";
				}
				if (!havingGroup('CrazyRegister')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'crazy'";
				}
				if (!havingGroup('VenerRegister')) {
					$filter .= " and isnull(MT.MorbusType_SysNick,'') not like 'vener'";
				}

				break;

			case 'EvnNotifyHepatitis':
				$query .= '
					inner join v_EvnNotifyHepatitis ENH with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENH.PersonEvn_id = PS.PersonEvn_id and ENH.Server_id = PS.Server_id' : 'ENH.Person_id = PS.Person_id' ) . '
					inner join v_MorbusHepatitis MH with (nolock) on MH.Morbus_id = ENH.Morbus_id 
					outer apply (
						select TOP 1
							Lpu_id
						from 
							v_PersonCard pc with (nolock)
						WHERE 
							pc.Person_id = PS.Person_id
					) as PC
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id 
					left join v_PersonRegister PR with (nolock) on ENH.EvnNotifyHepatitis_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MH.Diag_id,PR.Diag_id)
				';

				if (empty($data['AttachLpu_id']) && (empty($data['session']['groups']) || strpos($data['session']['groups'], 'HepatitisRegistry') < 0)) {
					$filter .= " and ( PC.Lpu_id  = :AttachLpu_id ) ";
					$queryParams['AttachLpu_id'] = $data['session']['lpu_id'];
				}

				if (isset($data['Diag_Code_From'])) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= " and ENH.EvnNotifyHepatitis_setDT >= cast(:EvnNotifyHepatitis_setDT_Range_0 as datetime) ";
					$queryParams['EvnNotifyHepatitis_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= " and ENH.EvnNotifyHepatitis_setDT <= cast(:EvnNotifyHepatitis_setDT_Range_1 as datetime) ";
					$queryParams['EvnNotifyHepatitis_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENH.EvnNotifyHepatitis_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENH.EvnNotifyHepatitis_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}

				break;

			case 'EvnOnkoNotify':
				$query .= '
					inner join v_EvnOnkoNotify EON with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'EON.PersonEvn_id = PS.PersonEvn_id and EON.Server_id = PS.Server_id' : 'EON.Person_id = PS.Person_id' ) . '
					inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id 
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id 
					outer apply (
						select TOP 1
							Lpu_id
						from 
							v_PersonCard pc with (nolock)
						WHERE 
							pc.Person_id = PS.Person_id
					) as PC
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id 
					left join v_Lpu Lpu1 with (nolock) on Lpu1.Lpu_id = EON.Lpu_sid 
					left join v_Diag Diag with (nolock) on Diag.Diag_id = M.Diag_id 
					left join v_OnkoDiag OnkoDiag with (nolock) on OnkoDiag.OnkoDiag_id = MO.OnkoDiag_mid 
					left join v_TumorStage TumorStage with (nolock) on TumorStage.TumorStage_id = MO.TumorStage_id 
					left join v_EvnOnkoNotifyNeglected EONN with (nolock) on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id 
					left join v_PersonRegister PR with (nolock) on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
				';

				if (empty($data['isOnlyTheir']) && (empty($data['session']['groups']) || strpos($data['session']['groups'], 'OnkoRegistry') < 0)) {
					$filter .= " and ( EON.pmUser_insID  = :pmUser_id OR EONN.pmUser_insID  = :pmUser_id ) ";
					$queryParams['pmUser_id'] = $data['pmUser_id'];
				}

				if (isset($data['Diag_Code_From'])) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['OnkoDiag_Code_From'])) {
					$filter .= " and ( OnkoDiag.OnkoDiag_id >= :OnkoDiag_Code_From ) ";
					$queryParams['OnkoDiag_Code_From'] = $data['OnkoDiag_Code_From'];
				}

				if (isset($data['OnkoDiag_Code_To'])) {
					$filter .= " and ( OnkoDiag.OnkoDiag_id <= :OnkoDiag_Code_To ) ";
					$queryParams['OnkoDiag_Code_To'] = $data['OnkoDiag_Code_To'];
				}

				if (isset($data['Lpu_sid'])) {
					$filter .= " and EON.Lpu_sid = :Lpu_sid ";
					$queryParams['Lpu_sid'] = $data['Lpu_sid'];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= " and EON.EvnOnkoNotify_setDT >= cast(:EvnOnkoNotify_setDT_Range_0 as datetime) ";
					$queryParams['EvnOnkoNotify_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= " and EON.EvnOnkoNotify_setDT <= cast(:EvnOnkoNotify_setDT_Range_1 as datetime) ";
					$queryParams['EvnOnkoNotify_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if (isset($data['isNeglected'])) {
					if ($data['isNeglected'] == 1) {
						$filter .= " and EONN.EvnOnkoNotifyNeglected_id IS NULL ";
					} elseif ($data['isNeglected'] == 2) {
						$filter .= " and EONN.EvnOnkoNotifyNeglected_id IS NOT NULL ";
					}
				}

				if (isset($data['TumorStage_id'])) {
					$filter .= " and MO.TumorStage_id = :TumorStage_id ";
					$queryParams['TumorStage_id'] = $data['TumorStage_id'];
				}

				if (isset($data['TumorCircumIdentType_id'])) {
					$filter .= " and MO.TumorCircumIdentType_id = :TumorCircumIdentType_id ";
					$queryParams['TumorCircumIdentType_id'] = $data['TumorCircumIdentType_id'];
				}

				if (!empty($data['EvnNotifyStatus_id'])) {
					switch ($data['EvnNotifyStatus_id']) {
						case 1:
							$filter .= '
								and EON.EvnOnkoNotify_niDate is null
								and PR.PersonRegister_id is null
							';
							break;
						case 2:
							$filter .= '
								and PR.PersonRegister_id is not null
							';
							break;
						case 3:
							$filter .= '
								and EON.EvnOnkoNotify_niDate is not null
								and EON.PersonRegisterFailIncludeCause_id = 1
							';
							break;
						case 4:
							$filter .= '
								and EON.EvnOnkoNotify_niDate is not null
								and EON.PersonRegisterFailIncludeCause_id = 2
							';
							break;
					}
				}

				if (!empty($data['IsIncluded'])) {
					if ($data['IsIncluded'] == 2) {
						$filter .= '
							and EON.EvnOnkoNotify_niDate is null
							and PR.PersonRegister_setDate is not null
						';
					} else {
						$filter .= '
							and EON.EvnOnkoNotify_niDate is not null
						';
					}
				}

				break;

			case 'OnkoRegistry':
				//$queryParams['MorbusType_SysNick'] = 'onko';
				//$queryParams['PersonRegisterType_SysNick'] = 'onko';
				$filter .= " and PR.PersonRegisterType_id = 3 "; // Упростил фильтрацию. Этого должно быть достаточно
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnOnkoNotify EON with (nolock) on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
					left join v_EvnOnkoNotifyNeglected EONN with (nolock) on EONN.EvnOnkoNotify_id = PR.EvnNotifyBase_id
					left join v_Morbus M with (nolock) on M.Morbus_id = isnull(EON.Morbus_id,PR.Morbus_id)
					left join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					left join v_MorbusOnkoBase MOB with (nolock) on MOB.MorbusBase_id = M.MorbusBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					left join v_OnkoDiag OnkoDiag with (nolock) on OnkoDiag.OnkoDiag_id = MO.OnkoDiag_mid
					left join v_TumorStage TumorStage with (nolock) on TumorStage.TumorStage_id = MO.TumorStage_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = PR.MedPersonal_iid
					outer apply (
                        select top 1 MOL2.MorbusOnkoLeave_id
                        from v_EvnSection ES with (nolock)
                        inner join v_MorbusOnkoLeave MOL2 with (nolock) on ES.EvnSection_id = MOL2.EvnSection_id
                        where MOL2.Diag_id = PR.Diag_id and ES.Person_id = PS.Person_id
					) MOL
					outer apply (
						select top 1 MOLd2.MorbusOnkoVizitPLDop_id
						from v_MorbusOnkoVizitPLDop MOLd2 with (nolock) 
						inner join v_EvnVizitPL EVP with (nolock) on EVP.EvnVizitPL_id = MOLd2.EvnVizit_id
						where MOLd2.Diag_id = PR.Diag_id and EVP.Person_id = PS.Person_id
					) MOV
				';

				// Регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						$filter .= " and PR.PersonRegister_disDate is null ";
					} elseif ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and PR.PersonRegister_disDate is not null ";
					}
				}


				switch ($data['PersonRegisterRecordType_id']) {
					case 1: // все
						break;
					case 2: // все, состоящие на учете
						$filter .= " and MOB.OnkoRegType_id is not null and MOB.OnkoRegOutType_id is null ";
						break;
					case 3: // все выехавшие
						$filter .= " and MOB.OnkoRegOutType_id = 1 ";
						break;
					case 4: // все, у которых диагноз не подтвердился
						$filter .= " and MOB.OnkoRegOutType_id = 2 ";
						break;
					case 5: // все, «снятые по базалиоме»
						$filter .= " and MOB.OnkoRegOutType_id = 3 ";
						break;
					case 6: // все умершие
						$filter .= " and MOB.OnkoRegOutType_id in (4,5,6) ";
						break;
				}

				if (isset($data['PersonRegister_setDate_Range'][0])) {
					$filter .= " and PR.PersonRegister_setDate >= cast(:PersonRegister_setDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
				}
				if (isset($data['PersonRegister_setDate_Range'][1])) {
					$filter .= " and PR.PersonRegister_setDate <= cast(:PersonRegister_setDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
				}

				if (isset($data['PersonRegister_disDate_Range'][0])){
					$filter .= " and PR.PersonRegister_disDate >= cast(:PersonRegister_disDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
				}
				if (isset($data['PersonRegister_disDate_Range'][1])){
					$filter .= " and PR.PersonRegister_disDate <= cast(:PersonRegister_disDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
				}

				if (isset($data['PersonRegister_onkoDeathDate_Range'][0])){
					$filter .= " and MOB.MorbusOnkoBase_deadDT >= cast(:PersonRegister_onkoDeathDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_onkoDeathDate_Range_0'] = $data['PersonRegister_onkoDeathDate_Range'][0];
				}
				
				if (isset($data['PersonRegister_onkoDeathDate_Range'][1])){
					$filter .= " and MOB.MorbusOnkoBase_deadDT <= cast(:PersonRegister_onkoDeathDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_onkoDeathDate_Range_1'] = $data['PersonRegister_onkoDeathDate_Range'][1];
				}
				
				if(isset($data['PersonRegister_onkoDiagDeath'])){
					$query.='
					left join v_Diag DiagDeath with (nolock) on DiagDeath.Diag_id = mob.Diag_did
					';
					$filter.='and DiagDeath.diag_code=:PersonRegister_onkoDiagDeath';					
					$queryParams['PersonRegister_onkoDiagDeath'] = $data['PersonRegister_onkoDiagDeath'];
				}
				
				if (isset($data['MorbusOnkoBase_NumCard'])) {
					$filter .= "and MOB.MorbusOnkoBase_NumCard like :MorbusOnkoBase_NumCard ";
					$data['MorbusOnkoBase_NumCard'] = str_replace('%', '[%]', $data['MorbusOnkoBase_NumCard']);
					$data['MorbusOnkoBase_NumCard'] = str_replace('_', '[_]', $data['MorbusOnkoBase_NumCard']);
					if (mb_strlen($data['MorbusOnkoBase_NumCard'] < 10))
						$data['MorbusOnkoBase_NumCard'] .= '%';
					$queryParams['MorbusOnkoBase_NumCard'] = $data['MorbusOnkoBase_NumCard'];
				}

				if (!empty($data['PersonRegister_evnSection_Range'][0]) || !empty($data['PersonRegister_evnSection_Range'][1])) {
					$query .= '
						left join v_EvnSection ES with (nolock) on ES.Person_id = PS.Person_id
						left join v_Diag ESD with (nolock) on ES.Diag_id = ESD.Diag_id
					';

					if (!empty($data['PersonRegister_evnSection_Range'][0]) && !empty($data['PersonRegister_evnSection_Range'][1])) {
						$filter .= " and ES.EvnSection_setDate >= cast(:PersonRegister_evnSection_setDate as datetime) and ES.EvnSection_setDate <= cast(:PersonRegister_evnSection_disDate as datetime)";
						$queryParams['PersonRegister_evnSection_setDate'] = $data['PersonRegister_evnSection_Range'][0];
						$queryParams['PersonRegister_evnSection_disDate'] = $data['PersonRegister_evnSection_Range'][1];
					} else if (!empty($data['PersonRegister_evnSection_Range'][0])) {
						$filter .= " and (ES.EvnSection_setDate >= cast(:PersonRegister_evnSection_setDate as datetime) or ES.EvnSection_disDate >= cast(:PersonRegister_evnSection_setDate as datetime) or ES.EvnSection_disDate is null)";
						$queryParams['PersonRegister_evnSection_setDate'] = $data['PersonRegister_evnSection_Range'][0];
					} else if (!empty($data['PersonRegister_evnSection_Range'][1])) {
						$filter .= " and ES.EvnSection_setDate <= cast(:PersonRegister_evnSection_disDate as datetime)";
						$queryParams['PersonRegister_evnSection_disDate'] = $data['PersonRegister_evnSection_Range'][1];
					}

					$filter .= " and ((ESD.Diag_Code >= 'C00' and ESD.Diag_Code <= 'C97') or (ESD.Diag_Code >= 'D00' and ESD.Diag_Code <= 'D09')) ";
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}
				if (isset($data['Diag_Code_To'])) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				if (!empty($data['MorbusOnko_setDiagDT_Range'][0])) {
					$filter .= " and MO.MorbusOnko_setDiagDT >= cast(:MorbusOnko_setDiagDT_Range_0 as datetime) ";
					$queryParams['MorbusOnko_setDiagDT_Range_0'] = $data['MorbusOnko_setDiagDT_Range'][0];
				}
				if (!empty($data['MorbusOnko_setDiagDT_Range'][1])) {
					$filter .= " and MO.MorbusOnko_setDiagDT <= cast(:MorbusOnko_setDiagDT_Range_1 as datetime) ";
					$queryParams['MorbusOnko_setDiagDT_Range_1'] = $data['MorbusOnko_setDiagDT_Range'][1];
				}
				if (!empty($data['MorbusOnko_IsMainTumor'])) {
					$filter .= " and isnull(MO.MorbusOnko_IsMainTumor,1) = :MorbusOnko_IsMainTumor ";
					$queryParams['MorbusOnko_IsMainTumor'] = $data['MorbusOnko_IsMainTumor'];
				}
				if (!empty($data['Diag_mid'])) {
					$filter .= " and MO.OnkoDiag_mid = :Diag_mid ";
					$queryParams['Diag_mid'] = $data['Diag_mid'];
				}
				if (!empty($data['TumorStage_id'])) {
					$filter .= " and MO.TumorStage_id = :TumorStage_id ";
					$queryParams['TumorStage_id'] = $data['TumorStage_id'];
				}

				// Спец. лечение
				if (!empty($data['TumorPrimaryTreatType_id'])) {
					$filter .= ' and MO.TumorPrimaryTreatType_id = :TumorPrimaryTreatType_id ';
					$queryParams['TumorPrimaryTreatType_id'] = $data['TumorPrimaryTreatType_id'];
				}
				if (!empty($data['TumorRadicalTreatIncomplType_id'])) {
					$filter .= ' and MO.TumorRadicalTreatIncomplType_id = :TumorRadicalTreatIncomplType_id ';
					$queryParams['TumorRadicalTreatIncomplType_id'] = $data['TumorRadicalTreatIncomplType_id'];
				}
				if (isset($data['MorbusOnkoSpecTreat_begDate_Range'][0]) && isset($data['MorbusOnkoSpecTreat_begDate_Range'][1])) {
					$filter .= ' and MO.MorbusOnko_specSetDT between cast(:MorbusOnkoSpecTreat_begDate_Range_0 as datetime) and cast(:MorbusOnkoSpecTreat_begDate_Range_1 as datetime) ';
					$queryParams['MorbusOnkoSpecTreat_begDate_Range_0'] = $data['MorbusOnkoSpecTreat_begDate_Range'][0];
					$queryParams['MorbusOnkoSpecTreat_begDate_Range_1'] = $data['MorbusOnkoSpecTreat_begDate_Range'][1];
				}
				if (isset($data['MorbusOnkoSpecTreat_endDate_Range'][0]) && isset($data['MorbusOnkoSpecTreat_endDate_Range'][1])) {
					$filter .= ' and MO.MorbusOnko_specDisDT between cast(:MorbusOnkoSpecTreat_endDate_Range_0 as datetime) and cast(:MorbusOnkoSpecTreat_endDate_Range_1 as datetime) ';
					$queryParams['MorbusOnkoSpecTreat_endDate_Range_0'] = $data['MorbusOnkoSpecTreat_endDate_Range'][0];
					$queryParams['MorbusOnkoSpecTreat_endDate_Range_1'] = $data['MorbusOnkoSpecTreat_endDate_Range'][1];
				}

				/*
				  if(
				  )
				  {
				  $filterDop = '';
				  $query .= '
				  inner join v_MorbusOnkoSpecTreat MOST with (nolock) on MOST.MorbusOnko_id = MO.MorbusOnko_id '. $filterDop ;
				  }
				 */

				// Контроль состояния
				if (!empty($data['OnkoTumorStatusType_id'])) {
					$filter .= ' and MO.OnkoTumorStatusType_id = :OnkoTumorStatusType_id ';
					$queryParams['OnkoTumorStatusType_id'] = $data['OnkoTumorStatusType_id'];
				}

				if (!empty($data['OnkoPersonStateType_id'])) {
					$filter .= ' and MOBPS.OnkoPersonStateType_id = :OnkoPersonStateType_id ';
					$query .= '
						left join v_MorbusOnkoBasePersonState MOBPS with (nolock) on MOBPS.MorbusOnkoBase_id = MOB.MorbusOnkoBase_id
					';
					$queryParams['OnkoPersonStateType_id'] = $data['OnkoPersonStateType_id'];
				}

				if (!empty($data['OnkoStatusYearEndType_id'])) {
					$filter .= ' and MOB.OnkoStatusYearEndType_id = :OnkoStatusYearEndType_id ';
					$queryParams['OnkoStatusYearEndType_id'] = $data['OnkoStatusYearEndType_id'];
				}

				break;

			case 'GeriatricsRegistry':
				$filter .= " and PR.PersonRegisterType_id = 67 ";
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id
					inner join v_Diag D with (nolock) on D.Diag_id = PR.Diag_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyGeriatrics ENG with (nolock) on ENG.EvnNotifyGeriatrics_id = PR.EvnNotifyBase_id
					left join v_Morbus M with (nolock) on M.Morbus_id = PR.Morbus_id --isnull(EGN.Morbus_id, PR.Morbus_id)
					left join v_MorbusGeriatrics MG with (nolock) on MG.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_SocStatus SS with (nolock) on SS.SocStatus_id = PS.SocStatus_id
					left join v_AgeNotHindrance ANH with (nolock) on ANH.AgeNotHindrance_id = MG.AgeNotHindrance_id
					left join v_YesNo IsKGO with (nolock) on IsKGO.YesNo_id = MG.MorbusGeriatrics_IsKGO
					left join v_YesNo IsWheelChair with (nolock) on IsWheelChair.YesNo_id = MG.MorbusGeriatrics_IsWheelChair
					left join v_YesNo IsFallDown with (nolock) on IsFallDown.YesNo_id = MG.MorbusGeriatrics_IsFallDown
					left join v_YesNo IsWeightDecrease with (nolock) on IsWeightDecrease.YesNo_id = MG.MorbusGeriatrics_IsWeightDecrease
					left join v_YesNo IsCapacityDecrease with (nolock) on IsCapacityDecrease.YesNo_id = MG.MorbusGeriatrics_IsCapacityDecrease
					left join v_YesNo IsCognitiveDefect with (nolock) on IsCognitiveDefect.YesNo_id = MG.MorbusGeriatrics_IsCognitiveDefect
					left join v_YesNo IsMelancholia with (nolock) on IsMelancholia.YesNo_id = MG.MorbusGeriatrics_IsMelancholia
					left join v_YesNo IsEnuresis with (nolock) on IsEnuresis.YesNo_id = MG.MorbusGeriatrics_IsEnuresis
					left join v_YesNo IsPolyPragmasy with (nolock) on IsPolyPragmasy.YesNo_id = MG.MorbusGeriatrics_IsPolyPragmasy
				';

				// Регистр
				if ( !empty($data['PersonRegisterType_id']) ) {
					if ( $data['PersonRegisterType_id'] == 2 ) {
						$filter .= " and PR.PersonRegister_disDate is null ";
					}
					else if ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and PR.PersonRegister_disDate is not null ";
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) ) {
					$filter .= " and PR.PersonRegister_setDate >= cast(:PersonRegister_setDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
				}

				if ( isset($data['PersonRegister_setDate_Range'][1]) ) {
					$filter .= " and PR.PersonRegister_setDate <= cast(:PersonRegister_setDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) ) {
					$filter .= " and PR.PersonRegister_disDate >= cast(:PersonRegister_disDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
				}

				if ( isset($data['PersonRegister_disDate_Range'][1]) ) {
					$filter .= " and PR.PersonRegister_disDate <= cast(:PersonRegister_disDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
				}

				if ( !empty($data['AgeNotHindrance_id']) ) {
					$filter .= " and MG.AgeNotHindrance_id = :AgeNotHindrance_id ";
					$queryParams['AgeNotHindrance_id'] = $data['AgeNotHindrance_id'];
				}

				if ( !empty($data['MorbusGeriatrics_IsKGO']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsKGO = :MorbusGeriatrics_IsKGO ";
					$queryParams['MorbusGeriatrics_IsKGO'] = $data['MorbusGeriatrics_IsKGO'];
				}

				if ( !empty($data['MorbusGeriatrics_IsWheelChair']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsWheelChair = :MorbusGeriatrics_IsWheelChair ";
					$queryParams['MorbusGeriatrics_IsWheelChair'] = $data['MorbusGeriatrics_IsWheelChair'];
				}

				if ( !empty($data['MorbusGeriatrics_IsFallDown']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsFallDown = :MorbusGeriatrics_IsFallDown ";
					$queryParams['MorbusGeriatrics_IsFallDown'] = $data['MorbusGeriatrics_IsFallDown'];
				}

				if ( !empty($data['MorbusGeriatrics_IsWeightDecrease']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsWeightDecrease = :MorbusGeriatrics_IsWeightDecrease ";
					$queryParams['MorbusGeriatrics_IsWeightDecrease'] = $data['MorbusGeriatrics_IsWeightDecrease'];
				}

				if ( !empty($data['MorbusGeriatrics_IsCapacityDecrease']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsCapacityDecrease = :MorbusGeriatrics_IsCapacityDecrease ";
					$queryParams['MorbusGeriatrics_IsCapacityDecrease'] = $data['MorbusGeriatrics_IsCapacityDecrease'];
				}

				if ( !empty($data['MorbusGeriatrics_IsCognitiveDefect']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsCognitiveDefect = :MorbusGeriatrics_IsCognitiveDefect ";
					$queryParams['MorbusGeriatrics_IsCognitiveDefect'] = $data['MorbusGeriatrics_IsCognitiveDefect'];
				}

				if ( !empty($data['MorbusGeriatrics_IsMelancholia']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsMelancholia = :MorbusGeriatrics_IsMelancholia ";
					$queryParams['MorbusGeriatrics_IsMelancholia'] = $data['MorbusGeriatrics_IsMelancholia'];
				}

				if ( !empty($data['MorbusGeriatrics_IsEnuresis']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsEnuresis = :MorbusGeriatrics_IsEnuresis ";
					$queryParams['MorbusGeriatrics_IsEnuresis'] = $data['MorbusGeriatrics_IsEnuresis'];
				}

				if ( !empty($data['MorbusGeriatrics_IsPolyPragmasy']) ) {
					$filter .= " and MG.MorbusGeriatrics_IsPolyPragmasy = :MorbusGeriatrics_IsPolyPragmasy ";
					$queryParams['MorbusGeriatrics_IsPolyPragmasy'] = $data['MorbusGeriatrics_IsPolyPragmasy'];
				}
				break;

			case 'IPRARegistry':
				//Дата исключения из регистра
				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}
				//Дата включения в регистр
				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				//Дата окончания срока ИПРА
				if (isset($data['IPRARegistry_EndDate_Range'][0]) && isset($data['IPRARegistry_EndDate_Range'][1])) {
					$queryParams['IPRARegistry_EndDate_Range_0'] = $data['IPRARegistry_EndDate_Range'][0];
					$queryParams['IPRARegistry_EndDate_Range_1'] = $data['IPRARegistry_EndDate_Range'][1];
					$filter .= ' and IR.IPRARegistry_EndDate between cast(:IPRARegistry_EndDate_Range_0 as date) and cast(:IPRARegistry_EndDate_Range_1 as date) ';
				}
				//Дата выдачи ИПРА
				if (isset($data['IPRARegistry_issueDate_Range'][0]) && isset($data['IPRARegistry_issueDate_Range'][1])) {
					$queryParams['IPRARegistry_issueDate_Range_0'] = $data['IPRARegistry_issueDate_Range'][0];
					$queryParams['IPRARegistry_issueDate_Range_1'] = $data['IPRARegistry_issueDate_Range'][1];
					$filter .= ' and IR.IPRARegistry_issueDate between cast(:IPRARegistry_issueDate_Range_0 as date) and cast(:IPRARegistry_issueDate_Range_1 as date) ';
				}
				//Медицинская реабилитация
				if (isset($data['IPRARegistryData_MedRehab_yn']) && $data['IPRARegistryData_MedRehab_yn'] != '') {
					$filter .= ' and IRD.IPRARegistryData_MedRehab =:IPRARegistryData_MedRehab_yn';
					$queryParams['IPRARegistryData_MedRehab_yn'] = $data['IPRARegistryData_MedRehab_yn'];
				}
				//Реконструктивная хирургия
				if (isset($data['IPRARegistryData_ReconstructSurg_yn']) && $data['IPRARegistryData_ReconstructSurg_yn'] != '') {
					$filter .= ' and IRD.IPRARegistryData_ReconstructSurg =:IPRARegistryData_ReconstructSurg_yn';
					$queryParams['IPRARegistryData_ReconstructSurg_yn'] = $data['IPRARegistryData_ReconstructSurg_yn'];
				}
				//Протезирование и ортезирование
				if (isset($data['IPRARegistryData_Orthotics_yn']) && $data['IPRARegistryData_Orthotics_yn'] != '') {
					$filter .= ' and IRD.IPRARegistryData_Orthotics =:IPRARegistryData_Orthotics_yn';
					$queryParams['IPRARegistryData_Orthotics_yn'] = $data['IPRARegistryData_Orthotics_yn'];
				}

				$query .= " 
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id
						and PR.MorbusType_id = 90
					outer apply (
						select top 1 *
						from dbo.IPRARegistry with (nolock) 
						where Person_id = PS.Person_id
						order by IPRARegistry_issueDate desc
					) IR
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
						and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
					inner join v_IPRARegistryData ird with(nolock) on ird.IPRARegistry_id = IR.IPRARegistry_id
					left join v_Lpu DirLpu with (nolock) on DirLpu.Lpu_id = IR.IPRARegistry_DirectionLPU_id
					left join v_Lpu ConfLpu with (nolock) on ConfLpu.Lpu_id = IR.Lpu_id
					left join v_pmUserCache pmUser with(nolock) on IR.pmUser_confirmID = pmUser.pmUser_id
				";
				$filter .= ' and exists (select top 1 IPRARegistryData_id from dbo.v_IPRARegistryData with (nolock) where IPRARegistry_id = IR.IPRARegistry_id)';
				if ($this->getRegionNick() != 'ufa') {
					$query .= "
					outer apply(
						select case when (
							isnull(ird.IPRARegistryData_MedRehab, 1) = 1
							or exists(
								select * from v_MeasuresRehab with(nolock) 
								where IPRARegistry_id = IR.IPRARegistry_id and MeasuresRehabType_id = 1
							)
						) and (
							isnull(ird.IPRARegistryData_ReconstructSurg, 1) = 1
							or exists(
								select * from v_MeasuresRehab with(nolock) 
								where IPRARegistry_id = IR.IPRARegistry_id and MeasuresRehabType_id = 2
							)
						) and (
							isnull(ird.IPRARegistryData_Orthotics, 1) = 1
							or exists(
								select * from v_MeasuresRehab with(nolock) 
								where IPRARegistry_id = IR.IPRARegistry_id and MeasuresRehabType_id = 3
							)
						) then 2 else 1 end as Value
					) IsMeasuresComplete
					";
				}


				if (isset($data['PersonRegister_number_IPRA']) && $data['PersonRegister_number_IPRA'] != '') {
					$filter .= ' and IR.IPRARegistry_Number =:PersonRegister_number_IPRA';
					$queryParams['PersonRegister_number_IPRA'] = $data['PersonRegister_number_IPRA'];
				}

				if (isset($data['PersonRegister_buro_MCE']) && $data['PersonRegister_buro_MCE'] != '') {
					$filter .= ' and IR.IPRARegistry_FGUMCEnumber =:PersonRegister_buro_MCE';
					$queryParams['PersonRegister_buro_MCE'] = $data['PersonRegister_buro_MCE'];
				}

				if (isset($data['PersonRegister_confirm_IPRA']) && $data['PersonRegister_confirm_IPRA'] != '') {
					$filter .= ' and IR.IPRARegistry_confirm =:PersonRegister_confirm_IPRA';
					$queryParams['PersonRegister_confirm_IPRA'] = $data['PersonRegister_confirm_IPRA'];
				}

				if (isset($data['IPRARegistry_DirectionLPU_id']) && $data['IPRARegistry_DirectionLPU_id'] != '') {
					$filter .= ' and IR.IPRARegistry_DirectionLPU_id =:IPRARegistry_DirectionLPU_id';
					$queryParams['IPRARegistry_DirectionLPU_id'] = $data['IPRARegistry_DirectionLPU_id'];
				}

				if (isset($data['LPU_id']) && $data['LPU_id'] != '' && $this->getRegionNick() == 'ufa') {
					$filter .= ' and IR.Lpu_id =:LPU_id';
					$queryParams['LPU_id'] = $data['LPU_id'];
				}

				if ($this->getRegionNick() != 'ufa' && isset($data['PersonRegister_FilterBy']) && $data['PersonRegister_FilterBy'] != '' && !isOuzSpec()) {
					if($data['PersonRegister_FilterBy'] == 'Attachment'){
						//•	Прикреплению
						//выводятся те записи регистра, пациенты которых прикреплены к МО пользователя по основному типу прикреплени
                        //Добавлена проверка на наличие данных в поле "МО прикрепления"
                        //Заменено $filter .= ' AND PC.Lpu_id = :Lpu_id AND pc.LpuAttachType_id = 1';
                        if(isset($data["AttachLpu_id"]) && $data["AttachLpu_id"] !== 0) {
                            //Так как нужно обращаться именно к МО прикрепления
                            $filter .= ' AND PC.Lpu_id = :AttachLpu_id AND pc.LpuAttachType_id = 1';
                        } else {
                            //сохраенение старого функционала для случая, если поле "МО прикрепления" пустое
                            $filter .= ' AND PC.Lpu_id = :Lpu_id AND pc.LpuAttachType_id = 1';
                        }
					}elseif($data['PersonRegister_FilterBy'] == 'DirectionToMse'){
						//•	Направлению на МСЭ
						//выводятся те записи регистра, пациенты которых не имеют основного прикрепления и были направлены на МСЭ из МО пользователя
						$filter .= ' AND IR.IPRARegistry_DirectionLPU_id = :Lpu_id AND (pc.LpuAttachType_id != 1 OR pc.LpuAttachType_id is null)';
					}
				}

				// Регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						$filter .= " and PR.PersonRegister_disDate is null ";
					} elseif ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and PR.PersonRegister_disDate is not null ";
					}
				}

				$filter .= ' and PR.PersonRegister_insDT is not null ';

				if ($this->getRegionNick() != 'ufa' && !isOuzSpec()) {
					if (empty($data['IPRARegistryEdit'])) {
						$filter .= ' and (
                			(PC.PersonCard_id is not null and PC.Lpu_id = :Lpu_id)
                			or (PC.PersonCard_id is null and IR.IPRARegistry_DirectionLPU_id = :Lpu_id)
                		)';
					}
					if (!empty($data['IsMeasuresComplete'])) {
						$filter .= "and IsMeasuresComplete.Value = :IsMeasuresComplete";
						$queryParams['IsMeasuresComplete'] = $data['IsMeasuresComplete'];
					}
				}

				if (!empty($data['pmUser_confirmID'])) {
					$queryParams['pmUser_confirmID'] = $data['pmUser_confirmID'];
					$filter .= " and IR.pmUser_confirmID=:pmUser_confirmID ";
				}
				break;
			case 'ECORegistry':
				$ECOSchema = 'dbo';
				if (isset($data['isRegion']) && $data['isRegion'] == 1) {
					$query .= "
						inner join v_PersonRegister PR with (nolock)
							on PR.Person_id = PS.Person_id and PR.MorbusType_id in
							(
								SELECT MorbusType_id
								FROM dbo.MorbusType
								WHERE MorbusType_SysNick = 'eco'
							)
						inner join
							(
								SELECT vER.*, EmC.EmbrionCount_Name, ChildCount.EcoChildCountType_Name
								FROM {$ECOSchema}.v_ECORegistry vER with (nolock)
								left join v_EmbrionCount EmC on EmC.EmbrionCount_id = vER.EmbrionCount_id 
								left join v_EcoChildCountType ChildCount on ChildCount.EcoChildCountType_id = vER.EcoChildCountType_id 
								WHERE vER.PersonRegisterEco_AddDate IN
								(
									select max(vER1.PersonRegisterEco_AddDate)
									from {$ECOSchema}.v_ECORegistry vER1 with (nolock)
									where vER.PersonRegister_id=vER1.PersonRegister_id
								)
							) ER
							on ER.Person_id = PS.Person_id
						left join v_PersonRegisterOutCause PROUT with (nolock)
							on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
						LEFT JOIN v_PersonPregnancy PP WITH (NOLOCK)
							ON PP.PersonRegisterEco_id = ER.PersonRegisterEco_id
						LEFT JOIN dbo.BirthSpecStac BSS
							on BSS.PersonRegister_id=PP.PersonRegister_id
						LEFT JOIN dbo.PregnancyResult PPR
							on PPR.PregnancyResult_id=BSS.PregnancyResult_id
						LEFT JOIN dbo.BirthSpecStac BSS1
							on BSS1.BirthSpecStac_id=ER.BirthSpecStac_id
						LEFT JOIN dbo.PregnancyResult PPR1
							on PPR1.PregnancyResult_id=BSS1.PregnancyResult_id
						LEFT JOIN v_Lpu LpuUch WITH (NOLOCK)
							ON LpuUch.Lpu_id = PR.Lpu_iid";

					if (isset($data['EcoRegistryData_lpu_id']) && $data['EcoRegistryData_lpu_id'] != '') {
						$filter .= ' and PR.Lpu_iid =:PersonRegister_Lpu_iid';
						$queryParams['PersonRegister_Lpu_iid'] = $data['EcoRegistryData_lpu_id'];
					};
				} else {
					$query .= "
					INNER JOIN v_PersonRegister PR with (nolock)
						ON PR.Person_id = PS.Person_id and PR.MorbusType_id in
							(
							SELECT MorbusType_id
							FROM dbo.MorbusType
							WHERE MorbusType_SysNick = 'eco'
							)
					LEFT JOIN
						(
							SELECT vER.*, EmC.EmbrionCount_Name, ChildCount.EcoChildCountType_Name
							FROM {$ECOSchema}.v_ECORegistry vER with (nolock)
							LEFT JOIN v_EmbrionCount EmC
								ON EmC.EmbrionCount_id = vER.EmbrionCount_id
							LEFT JOIN v_EcoChildCountType ChildCount
								ON ChildCount.EcoChildCountType_id = vER.EcoChildCountType_id
							WHERE vER.PersonRegisterEco_AddDate IN
								(
									select max(vER1.PersonRegisterEco_AddDate)
									from {$ECOSchema}.v_ECORegistry vER1 with (nolock)
									where vER.PersonRegister_id=vER1.PersonRegister_id
								)
						) ER
						ON ER.Person_id = PS.Person_id
					LEFT JOIN v_PersonRegisterOutCause PROUT with (nolock)
						on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					LEFT JOIN v_PersonPregnancy PP WITH (NOLOCK)
						ON PP.PersonRegisterEco_id = ER.PersonRegisterEco_id
					LEFT JOIN dbo.BirthSpecStac BSS
						on BSS.PersonRegister_id=PP.PersonRegister_id
					LEFT JOIN dbo.PregnancyResult PPR
						on PPR.PregnancyResult_id=BSS.PregnancyResult_id
					LEFT JOIN dbo.BirthSpecStac BSS1
						on BSS1.BirthSpecStac_id=ER.BirthSpecStac_id
					LEFT JOIN dbo.PregnancyResult PPR1
						on PPR1.PregnancyResult_id=BSS1.PregnancyResult_id
					LEFT JOIN v_Lpu LpuUch WITH (NOLOCK)
						ON LpuUch.Lpu_id = :PersonRegister_Lpu_iid";
					$filter .= ' and (pr.lpu_iid = :PersonRegister_Lpu_iid or '
							. ' exists(select 1 from dbo.v_ECORegistry ee where ee.lpu_id = :PersonRegister_Lpu_iid and ee.PersonRegister_id = pr.PersonRegister_id) '
							. ') ';
					$queryParams['PersonRegister_Lpu_iid'] = $data['EcoRegistryData_lpu_id'];
				}

				$query .= " 
					outer apply
					(
					select top 1
						PC.PersonCard_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						PC.LpuAttachType_id,
						PC.PersonCard_begDate,
						PC.PersonCard_endDate,
						PC.PersonCard_IsAttachCondit,
						PC.LpuRegion_fapid
					from dbo.v_PersonCard_all PC with (nolock)
					inner join dbo.Lpu with (nolock)
						on Lpu.Lpu_id = PC.Lpu_id
					inner join dbo.Org with (nolock)
						on Org.Org_id = Lpu.Org_id
					where PC.Person_id = PS.Person_id
							and PC.LpuAttachType_id = 1
					order by pc.PersonCard_begDate desc
					) PC
					LEFT JOIN v_Lpu Lpu with (nolock)
						ON Lpu.Lpu_id = PC.Lpu_id
					LEFT JOIN v_LpuRegion LR with (nolock)
						ON LR.LpuRegion_id = PC.LpuRegion_id
					LEFT JOIN v_LpuRegion LR_Fap with (nolock)
						ON LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
					LEFT JOIN [v_Address] [PA] with (nolock)
						ON [PA].[Address_id] = [PS].[UAddress_id]
				";

				if (isset($data['EcoRegistryData_dateRange'][0]) && isset($data['EcoRegistryData_dateRange'][1])) {
					$queryParams['EcoRegistryData_dateRange_0'] = $data['EcoRegistryData_dateRange'][0];
					$queryParams['EcoRegistryData_dateRange_1'] = $data['EcoRegistryData_dateRange'][1];
					$filter .= ' and cast(ER.PersonRegisterEco_AddDate as date) between :EcoRegistryData_dateRange_0 and :EcoRegistryData_dateRange_1 ';
				};

				if (isset($data['EcoRegistryData_vidOplod']) && $data['EcoRegistryData_vidOplod'] != '') {
					$filter .= ' and ER.EcoOplodType_id =:PersonRegister_VidOplod';
					$queryParams['PersonRegister_VidOplod'] = $data['EcoRegistryData_vidOplod'];
				}

				if (isset($data['EcoRegistryData_countMoveEmbroin']) && $data['EcoRegistryData_countMoveEmbroin'] != '') {
					$filter .= ' and ER.EmbrionCount_id =:PersonRegister_EmbrionCount';
					$queryParams['PersonRegister_EmbrionCount'] = $data['EcoRegistryData_countMoveEmbroin'];
				}

				if (isset($data['EcoRegistryData_ds1_from']) && $data['EcoRegistryData_ds1_from'] != '') {
					$filter .= ' and ER.ds_osn_code >=:PersonRegister_ds1_from';
					$queryParams['PersonRegister_ds1_from'] = $data['EcoRegistryData_ds1_from'];
				}

				if (isset($data['EcoRegistryData_ds1_to']) && $data['EcoRegistryData_ds1_to'] != '') {
					$filter .= ' and ER.ds_osn_code <=:PersonRegister_ds1_to';
					$queryParams['PersonRegister_ds1_to'] = $data['EcoRegistryData_ds1_to'];
				}

				if (isset($data['EcoPregnancyType_id']) && $data['EcoPregnancyType_id'] != '') {
					$filter .= ' and ER.EcoPregnancyType_id =:EcoPregnancyType_id';
					$queryParams['EcoPregnancyType_id'] = $data['EcoPregnancyType_id'];
				}

				if (isset($data['PayType_id']) && $data['PayType_id'] != '') {
					$filter .= ' and ER.PayType_id =:PayType_id';
					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				if (isset($data['EcoRegistryData_genDiag']) && $data['EcoRegistryData_genDiag'] != '') {
					$filter .= ' and ER.PersonRegisterEco_IsGeneting =:PersonRegister_GenetigDiag';
					$queryParams['PersonRegister_GenetigDiag'] = $data['EcoRegistryData_genDiag'];
				}

				if (isset($data['EcoRegistryData_resEco']) && $data['EcoRegistryData_resEco'] != '') {
					$filter .= ' and ER.res_code =:PersonRegister_Result';
					$queryParams['PersonRegister_Result'] = $data['EcoRegistryData_resEco'];
				}

				if (isset($data['EcoRegistryData_noRes']) && $data['EcoRegistryData_noRes'] == 1) {
					$filter .= ' and ER.Result is null';
				}

                if(isset($data['MedPersonal_iid'])){
                        $filter  .= ' and ER.MedPersonal_id = :MedPersonal_iid';
						$queryParams['MedPersonal_iid'] = $data['MedPersonal_iid'];
				}

				break;

			case 'BskRegistry':
				$query .= '
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id 
				';

				if ($data['LpuAttachType_id'] > 0) {
					$query .= " and PC.LpuAttachType_id = :LpuAttachType_id ";
					//$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
					$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
					$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
				} else {
					$query .= " and PC.LpuAttachType_id = 1 ";
				}
				//$query .= ' and PC.LpuAttachType_id = 1 ';

				$query .= '
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					--left join dbo.BSKRegistry R with (nolock) on R.Person_id = PR.Person_id and R.MorbusType_id = PR.MorbusType_id
					--1.для фильтров последняя заполненная анкета по ПН
					OUTER APPLY (
						select top 1 *
						from dbo.BSKRegistry r with (nolock)
						where (1=1)
							and r.Person_id = PS.Person_id
							and r.MorbusType_id in (19,88,84,89,50,110,111,112,113)
						order by r.BSKRegistry_setDate desc
					) as R

					cross apply (
						select top 1  *
						from v_PersonRegister PR (nolock)
						where 
							PR.Person_id = PS.Person_id and 
							PR.MorbusType_id in (19,88,84,89,50,110,111,112,113) and 
							isnull(R.MorbusType_id, PR.MorbusType_id) = PR.MorbusType_id
						order by PR.PersonRegister_setDate desc
					) PR
					
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					
					--2.для таблицы МО госпитализации
					OUTER APPLY (
					select top 1 isnull(rd.BSKRegistryData_AnswerText,rd.BSKRegistryData_data) BSKLpuGospital_data
					from dbo.BSKRegistry r with (nolock)
					inner join dbo.BSKRegistryData rd with (nolock) on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 304
					where (1=1)
					and r.Person_id = PR.Person_id
					and r.MorbusType_id = 19
					order by r.BSKRegistry_setDate desc
					) BSKLpuGospital

					--3.для таблицы ЧКВ
					OUTER APPLY (
					select top 1 isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) CHKV_data
					from dbo.BSKRegistry r with (nolock)
					inner join dbo.BSKRegistryData rd with (nolock) on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 302 and isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) != \'нет\'
					where (1=1)
					and r.Person_id = PR.Person_id
					and r.MorbusType_id = 19
					order by r.BSKRegistry_setDate desc
					) BSKCKV

					--4.для таблицы ТЛТ
					OUTER APPLY (
					select top 1 isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) TLT_data
					from dbo.BSKRegistry r with (nolock)
					inner join dbo.BSKRegistryData rd with (nolock) on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 274
					cross apply (select top 1 LastR.BSKRegistry_setDate
					from  dbo.BSKRegistry LastR with (nolock)
					where (1=1)
					and LastR.Person_id = r.Person_id and LastR.MorbusType_id = r.MorbusType_id
					order by LastR.BSKRegistry_setDate desc
					) LastR 
					where (1=1)
					and r.Person_id = PR.Person_id
					and r.MorbusType_id = 19
					and r.BSKRegistry_setDate >= DATEADD(DAY,-3,LastR.BSKRegistry_setDate)
					and isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) != \'\'
					and isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) != \'нет\'
					order by r.BSKRegistry_setDate desc
					) BSKTLT
					
					--5.для таблицы КАГ
					OUTER APPLY (
					select top 1 isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) KAG_data
					from dbo.BSKRegistry r with (nolock)
					inner join dbo.BSKRegistryData rd with (nolock) on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 413 and isnull(convert(varchar,rd.BSKRegistryData_AnswerDT,120),rd.BSKRegistryData_data) != \'нет\'
					where (1=1)
					and r.Person_id = PR.Person_id
					and r.MorbusType_id = 19
					order by r.BSKRegistry_setDate desc
					) BSKKAG

					left join v_Diag DP with (nolock) on PR.Diag_id = DP.Diag_id
					OUTER APPLY (
						select top 1 *
						from v_EvnPS with (nolock)
						where Person_id = PS.Person_id
							and PR.diag_id = case
								when PR.diag_id = Diag_id then Diag_id
								when PR.diag_id = Diag_pid then Diag_pid
								when PR.diag_id = Diag_did then Diag_did
								else null
							end
						order by EvnPS_setDT desc
					) as EvnPSDD
					left join dbo.v_pmUserCache MP with (nolock) on MP.PMUser_id = R.pmUser_insID';
				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				};
				if (!empty($data['PersonRegister_disDate_Range'][0]) && !empty($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				if (isset($data['MorbusType_id']) && $data['MorbusType_id'] != '') {
					$filter .= ' and PR.MorbusType_id =:MorbusType_id';
					//спустя 28 дней для ибс от окс
					if($data['MorbusType_id'] == '50'){
						$filter .= ' and ((DP.Diag_FullName is not null and (dateadd(day, 28, PR.PersonRegister_setDate) < getdate())) or
						DP.Diag_FullName is null )';
					}
					$queryParams['MorbusType_id'] = $data['MorbusType_id'];
					if (isset($data['quest_id']) && $data['quest_id'] != '') {
						if ($data['quest_id'] == 1) {
							$filter .= ' and BSKRegistry_setDate is not null';
						} else {
							$filter .= ' and BSKRegistry_setDate is null';
						}
					}
					if (isset($data['pmUser_docupdID']) && $data['pmUser_docupdID'] != '') {
						//$filter  .= ' and isnull(R.pmUser_updID, R.pmUser_insID) =:pmUser_docupdID';
						$filter .= ' and R.BSKRegistry_id in 
						(
							Select T.BSKRegistry_id
                            from (
                            select 
                            ROW_NUMBER() OVER(PARTITION BY R.Person_id ORDER BY isnull(R.BSKRegistry_insDT, R.BSKRegistry_updDT) DESC) num, * 
                            from dbo.BSKRegistry R with (nolock)
                            ) as T
                            where T.num = 1
						)';
						$filter .= ' and isnull(R.pmUser_updID, R.pmUser_insID) =:pmUser_docupdID';
						$queryParams['pmUser_docupdID'] = $data['pmUser_docupdID'];
					}
				}
				if (!empty($data['PersonRegisterType_id'])) {
					switch ($data['PersonRegisterType_id']) {
						case '2':
							$filter .= ' and PR.PersonRegister_disDate is not null ';
							break;
						case '3':
							$filter .= ' and PS.Person_deadDT is not null ';
							break;
					}
				}
				break;
			case 'ZNOSuspectRegistry':
				$query .= " left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1";

				if ($data['LpuAttachType_id'] > 0) {
					$query .= " and PC.LpuAttachType_id = :LpuAttachType_id ";
					$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
					$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
				} else {
					$query .= " and PC.LpuAttachType_id = 1 ";
				}
				$ShemZno = 'dbo';
				$query .= "    
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
                inner join {$ShemZno}.ZNOSuspectRegistry ZNOReg with(nolock) on ZNOReg.Person_id = ps.Person_id
				inner join {$ShemZno}.ZNOSuspectRout ZNORout with(nolock) on ZNORout.ZNOSuspectRegistry_id= ZNOReg.ZNOSuspectRegistry_id    
				inner join v_Diag d with (nolock) on d.Diag_id = ZNORout.Diag_id
				left join v_Diag dd with (nolock) on dd.Diag_id = ZNORout.Diag_Fid";

				$filter .= " and ZNOReg.ZNOSuspectRegistry_deleted = 1 and ZNORout.ZNOSuspectRout_deleted = 1 ";

				// Видимость
				//echo '<pre>' . print_r($aparam, 1) . '</pre>';
				if ($data['ZnoViewLpu_id'] >= 0) {
					$filter .= "and Lpu.Lpu_id = :ZnoViewLpu_id ";
					$queryParams['ZnoViewLpu_id'] = $data['ZnoViewLpu_id'];
				};

				//Тип записи регистра
				if(isset($data['PersonRegisterType_id']))
				{
					if($data['PersonRegisterType_id'] == 2 )
					{
						$filter .= " and ps.Person_deadDT is null ";
					};
					if($data['PersonRegisterType_id'] == 3 )
					{
						$filter .= " and ps.Person_deadDT is not null ";
					}
				}
				else{
					$filter .= " and 1<> 1 ";
				}

				//тип наблюдения  dd.Diag_id
				if (isset($data['ObservType_id'])) {
					if ($data['ObservType_id'] == 1) {
						$filter .= " and (dd.Diag_Code like'D0%' or dd.Diag_Code like'C%') ";
					};
					if ($data['ObservType_id'] == 2) {
						$filter .= " and dd.Diag_Code >='D10' and dd.Diag_Code <'D49' ";
					};
					if ($data['ObservType_id'] == 3) {
						$filter .= " and dd.Diag_Code not like'D0%' and dd.Diag_Code not like'C%' and (dd.Diag_Code < 'D10' or dd.Diag_Code >='D49') ";
					};
					if ($data['ObservType_id'] == 4) {
						$filter .= " and dd.Diag_id is null  ";
					};
				};
				//Нарушение сроков ZNOSuspectRout_IsTerms
				if (isset($data['DeadlineZNO_id'])) {
					if ($data['DeadlineZNO_id'] == 1) {
						$filter .= " and ZNORout.ZNOSuspectRout_IsTerms = 2 ";
					};
					if ($data['DeadlineZNO_id'] == 2) {
						$filter .= " and ZNORout.ZNOSuspectRout_IsTerms = 1 ";
					};

				};

				//Направление на биопсию ZNORout.ZNOSuspectRout_IsBiopsy
				if (isset($data['BiopsyRefZNO_id'])) {
					if ($data['BiopsyRefZNO_id'] == 1) {
						$filter .= " and ZNORout.ZNOSuspectRout_IsBiopsy is not null ";
					};
					if ($data['BiopsyRefZNO_id'] == 2) {
						$filter .= " and ZNORout.ZNOSuspectRout_IsBiopsy is null ";
					};

				};

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(ZNOReg.ZNOSuspectRegistry_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				};
				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and ps.Person_deadDT is not null and cast(ps.Person_deadDT as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				};

				break;
			case 'AdminVIPPerson':

				$query .= " left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1";

				if ($data['LpuAttachType_id'] > 0) {
					$query .= " and PC.LpuAttachType_id = :LpuAttachType_id ";
					$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
					$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
				} else {
					$query .= " and PC.LpuAttachType_id = 1 ";
				}

				$query .= "    
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
                inner join dbo.VIPPerson R with (nolock) on R.Person_id = Ps.Person_id
				inner join v_Lpu Lpu1 with (nolock) on Lpu1.Lpu_id = r.Lpu_id
				left join dbo.pmUserCache pmUser with (nolock) on pmUser.PMUser_id = R.pmUser_updID ";

				//$filter .= " and ZNOReg.ZNOSuspectRegistry_deleted = 1 and ZNORout.ZNOSuspectRout_deleted = 1 ";

				// Видимость
				//echo '<pre>' . print_r($aparam, 1) . '</pre>';
				if ($data['AdminVIPPersonLpu_id'] >= 0) {
					$filter .= " and Lpu1.Lpu_id = :AdminVIPPersonLpu_id ";
					$queryParams['AdminVIPPersonLpu_id'] = $data['AdminVIPPersonLpu_id'];
				};

				//Тип записи регистра
				if(isset($data['PersonRegisterType_id']))
				{
					if($data['PersonRegisterType_id'] == 2 )
					{
						$filter .= " and R.VIPPerson_disDate is null ";
					};
					if($data['PersonRegisterType_id'] == 3 )
					{
						$filter .= " and R.VIPPerson_disDate is not null ";
					}
				}
				else{
					$filter .= " and 1<> 1 ";
				}


				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(R.VIPPerson_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				};
				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(R.VIPPerson_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				};

				break;
			case 'ReabRegistry':

				$query .= " left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id ";

				if ($data['LpuAttachType_id'] > 0) {
					$query .= " and PC.LpuAttachType_id = :LpuAttachType_id ";
					$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
					$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
				} else {
					$query .= " and PC.LpuAttachType_id = 1 ";
				}

				$query .= "    
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
                                cross apply (select distinct dd.Person_id as pPerson_id  from r2.ReabEvent dd with (nolock) where dd.Person_id = PS.Person_id and dd.ReabEvent_Deleted = 1) PR";

				// Пользователь
				// Добавил
				$insID = "";
				if ($data['pmUser_insID'] > 0) {
					$queryParams['pmUser_insID'] = $data['pmUser_insID'];
					$insID .= " and gg.pmUser_insID = :pmUser_insID ";
					$data['pmUser_insID'] = 0;
				}
				if (isset($data['InsDate'])) {
					$queryParams['InsDate'] = $data['InsDate'];
					$insID .= " and cast(gg.ReabEvent_insDT as date) = cast(:InsDate as date)";
					$data['InsDate'] = null;
				}
				if (isset($data['InsDate_Range'][0]) && isset($data['InsDate_Range'][1])) {
					$queryParams['InsDate_Range_0'] = $data['InsDate_Range'][0];
					$queryParams['InsDate_Range_1'] = $data['InsDate_Range'][1];
					$insID .= ' and cast(gg.ReabEvent_insDT as date) between :InsDate_Range_0 and :InsDate_Range_1 ';
					$data['InsDate_Range'] = null;
				};

				//Изменил
				if ($data['pmUser_updID'] > 0) {
					$queryParams['pmUser_updID'] = $data['pmUser_updID'];
					$insID .= " and gg.pmUser_updID = :pmUser_updID ";
					$data['pmUser_updID'] = 0;
				}
				if (isset($data['UpdDate'])) {
					$queryParams['UpdDate'] = $data['UpdDate'];
					$insID .= " and cast(gg.ReabEvent_updDT as date) = cast(:UpdDate as date)";
					$data['UpdDate'] = null;
				}
				if (isset($data['UpdDate_Range'][0]) && isset($data['UpdDate_Range'][1])) {
					$queryParams['UpdDate_Range_0'] = $data['UpdDate_Range'][0];
					$queryParams['UpdDate_Range_1'] = $data['UpdDate_Range'][1];
					$insID .= ' and cast(gg.ReabEvent_updDT as date) between :UpdDate_Range_0 and :UpdDate_Range_1 ';
					$data['UpdDate_Range'] = null;
				};

				//Регистр
				if (empty($data['DirectType_id']) || $data['DirectType_id'] == '') { //отсутствует профиль, этап, анкеты, шкалы
					$filter .= "and EXISTS (select 1 from r2.ReabEvent gg with (nolock) where gg.Person_id = PR.pPerson_id ";
					if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] == 2) {
						$filter .= " and gg.ReabOutCause_id is null ";
					}
					if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] == 3) {
						$filter .= " and gg.ReabOutCause_id is not null ";
					}
					if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
						$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
						$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
						$filter .= ' and cast(gg.ReabEvent_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
					};
					if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
						$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
						$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
						$filter .= ' and cast(gg.ReabEvent_disDate  as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
					};
					$filter .= $insID . ")";
				} else {
					$reabfilter = "where gg.Person_id = PR.pPerson_id ";
					$filter .= "and EXISTS (select 1 from r2.ReabEvent gg with (nolock)";

					//					echo 'отлавливаем ошибку1 = ';
					//					echo '<pre>' . print_r($data['Reabquest_yn'], 1) . '</pre>';
					//					echo '<pre>' . print_r($data['ReabScale_yn'], 1) . '</pre>';
					//Присутствие анкет
					if (!empty($data['Reabquest_yn']) && $data['Reabquest_yn'] == 1) {
						$filter .= ", r2.ReabQuestion gg1 with (nolock) ";
						$reabfilter .= " and gg1.ReabEvent_id = gg.ReabEvent_id ";
					}
					//Заполненные шкалы
					if (!empty($data['ReabScale_yn']) && $data['ReabScale_yn'] == 1) {
						$filter .= ", r2.ReabScaleCondit gg2 with (nolock) ";
						$reabfilter .= " and gg2.ReabEvent_id = gg.ReabEvent_id ";
					}
					$queryParams['DirectType_id'] = $data['DirectType_id'];
					$filter .= $reabfilter . " and gg.ReabDirectType_id = :DirectType_id ";
					//Этап реабилитации
					if (!empty($data['StageType_id']) && $data['StageType_id'] != '') {
						$queryParams['StageType_id'] = $data['StageType_id'];
						$filter .= " and gg.ReabStageType_id = :StageType_id ";
					}
					if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] == 2) {
						$filter .= " and gg.ReabOutCause_id is null ";
					}
					if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] == 3) {
						$filter .= " and gg.ReabOutCause_id is not null ";
					}
					if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
						$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
						$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
						$filter .= ' and cast(gg.ReabEvent_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
					};
					if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
						$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
						$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
						$filter .= ' and cast(gg.ReabEvent_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
					};
					$filter .= $insID . ")";
				}
				break;
			//BOB - 13.10.2017
			case 'ReanimatRegistry':

				//echo '<pre>' . print_r($data['ReanimatLpu'].'~'.$data['RRW_BeginDate'].'~'.$data['RRW_EndDate'].'~'.$data['EvnScaleType'].'~'.$data['EvnScaleFrom'].'~'.$data['EvnScaleTo'].'~'.$data['ReanimatActionType'].'~'.$data['RA_DrugNames'], 1) . '</pre>'; //BOB - 04.11.2017


				$HowJoin = $data['HardOnly'] == 1 ? 'inner ' : 'left ';  // только тяжёлые
				//BOB - 23.01.2018
				$query .= "
					--inner join v_PersonRegister PR  with (nolock) on PR.Person_id = PS.Person_id 
					--  and PR.PersonRegisterType_Id = (select top 1 PersonRegisterType_id  from dbo.PersonRegisterType with (nolock) where PersonRegisterType_SysNick = 'reanimat') 
					inner join v_ReanimatRegister RR with (nolock) on RR.Person_id = PS.Person_id 
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = RR.PersonRegisterOutCause_id   --   PR.PersonRegisterOutCause_id
                    left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					left join dbo.v_pmUserCache MP with (nolock) on MP.PMUser_id = RR.pmUser_insID  --  PR.pmUser_insID 
					left join v_EvnReanimatPeriod ERP2 with (nolock) on ERP2.EvnReanimatPeriod_id = RR.EvnReanimatPeriod_id
					left join v_EvnSection ES with (nolock) on ES.EvnSection_id = ERP2.EvnReanimatPeriod_pid
					left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ES.EvnSection_pid
					left join v_Diag D with (nolock) on  D.Diag_id	= isnull(ES.Diag_id, EPS.Diag_pid)
					left join v_Lpu Lpu2 with (nolock) on Lpu2.Lpu_id = ES.Lpu_id					 				
					";
				//BOB - 23.01.2018

				$query .= $HowJoin . "join (select 1 as selrow, EvnReanimatPeriod_id from v_EvnReanimatPeriod ERP0 with (nolock) 	
								where ERP0.EvnReanimatPeriod_disDT is null
								  and (exists (select top 1 1 from v_EvnReanimatAction with (nolock)  --находится ли на искусственной вентиляции
											   where EvnReanimatAction_pid = ERP0.EvnReanimatPeriod_id
												 and ReanimatActionType_SysNick = 'lung_ventilation'
											     and (EvnReanimatAction_disDT is null or 	EvnReanimatAction_disDT >= GetDate())) -- дата окончания отсутствует или не наступила
								   or exists (select top 1 1 from v_EvnScale with (nolock)  --имеется ли значение шкалы SOFA > 0
											   where EvnScale_pid = ERP0.EvnReanimatPeriod_id
												 and ScaleType_id = (select top 1 ScaleType_id from dbo.ScaleType where ScaleType_SysNick = 'sofa' )      
												 and EvnScale_setDT >=DATEADD(day, -3, cast(cast(GetDate() as date) as datetime)) -- за 3 последних дня с округлением даты начала,т.е. фактически за 4
												 and EvnScale_Result > 2)))  as ERP on ERP.EvnReanimatPeriod_id = RR.EvnReanimatPeriod_id					
				";

				// Регистр: все / неисключонные / исключонные
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						$filter .= " and RR.ReanimatRegister_disDate is null ";  //BOB - 23.01.2018
					} elseif ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and RR.ReanimatRegister_disDate is not null ";
					}
				}

				//Дата включения в регистр
				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['ReanimatRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];		  //BOB - 23.01.2018
					$queryParams['ReanimatRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(RR.ReanimatRegister_setDate as date) between :ReanimatRegister_setDate_Range_0 and :ReanimatRegister_setDate_Range_1 ';
				}

				//Дата исключения из регистра
				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['ReanimatRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];   //BOB - 23.01.2018
					$queryParams['ReanimatRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(RR.ReanimatRegister_disDate as date) between :ReanimatRegister_disDate_Range_0 and :ReanimatRegister_disDate_Range_1 ';
				}

				//если непустой MorbusType_id
				if (!empty($data['MorbusType_id'])) {
					$filter .= ' 
								and RR.MorbusType_id = :MorbusType_id  ';
					$queryParams['MorbusType_id'] = $data['MorbusType_id'];
				}

				//в реанимации сейчас  //BOB - 28.03.2018  изменил в логическом выражении "== 1" на "== 2" кто бы мог подумать, что 1 - это "нет"!!!!!!!!
				$data['ReanimatRegister_IsPeriodNow'] = $data['ReanimatRegister_IsPeriodNow'] + 1;
				if ($data['ReanimatRegister_IsPeriodNow'] == 2) {
					$filter .= ' 
								and RR.ReanimatRegister_IsPeriodNow = :ReanimatRegister_IsPeriodNow  ';
					$queryParams['ReanimatRegister_IsPeriodNow'] = $data['ReanimatRegister_IsPeriodNow'];
				}

				//!!!!!!!!!ДОБАВЛЯТЬ НОВЫЕ РЕКВИЗИТЫ ПОИСКА
				//Интервал поиска нахождения в реанимации должен быть полным
				if ((!empty($data['RRW_BeginDate'])) && (!empty($data['RRW_EndDate']))) {
					$filter .= ' 
						and exists (select top 1 1  from v_EvnReanimatPeriod ERP3  with (nolock)
								 where ERP3.Person_id = PS.Person_id 
								   and ERP3.EvnReanimatPeriod_setDT <=   dateadd(day,1, cast(:RRW_EndDate as date))
								   and (ERP3.EvnReanimatPeriod_disDT > :RRW_BeginDate or ERP3.EvnReanimatPeriod_disDT is null))
								   ';
					$queryParams['RRW_BeginDate'] = $data['RRW_BeginDate'];
					$queryParams['RRW_EndDate'] = $data['RRW_EndDate'];
				}

				//Тип шкал и результаты исследований
				if (!empty($data['EvnScaleType'])) {
					$filter .= ' 
						and exists (select top 1 1  from v_EvnScale ES with (nolock) 
								 where ES.EvnScale_pid = ERP2.EvnReanimatPeriod_id --  ES.Person_id = PS.Person_id 
									and ES.ScaleType_id = :EvnScaleType
									';
					$queryParams['EvnScaleType'] = $data['EvnScaleType'];

					if (!empty($data['EvnScaleFrom'])) {
						$filter .= 'and ES.EvnScale_Result >= :EvnScaleFrom
							';
						$queryParams['EvnScaleFrom'] = $data['EvnScaleFrom'];
					}
					if (!empty($data['EvnScaleTo'])) {
						$filter .= 'and ES.EvnScale_Result <= :EvnScaleTo
							';
						$queryParams['EvnScaleTo'] = $data['EvnScaleTo'];
					}

					$filter .= ' )
								 ';
				}

				//Тип реанимационных мероприятий и применённый медикамент
				if ((!empty($data['ReanimatActionType'])) || (!empty($data['RA_DrugNames']))) {
					$filter .= '
						and exists (select top 1 1  from v_EvnReanimatAction ERA with (nolock) 
					             where ERA.EvnReanimatAction_pid = ERP2.EvnReanimatPeriod_id    -- ERA.Person_id = PS.Person_id 
									';

					if (!empty($data['ReanimatActionType'])) {
						$filter .= ' and ERA.ReanimatActionType_id = :ReanimatActionType
									';
						$queryParams['ReanimatActionType'] = $data['ReanimatActionType'];
					}
					if (!empty($data['RA_DrugNames'])) {
						$filter .= ' and ERA.ReanimDrugType_id = :RA_DrugNames
									';
						$queryParams['RA_DrugNames'] = $data['RA_DrugNames'];
					}
					$filter .= ' )
								 ';
				}

				//BOB - 27.12.2018
				if (!empty($data['ReanimatLpu'])) {
					$filter .= ' 
								and Lpu2.Lpu_id = :ReanimatLpu
								';
					$queryParams['ReanimatLpu'] = $data['ReanimatLpu'];
				}

				break;
			//BOB - 13.10.2017

			case 'HepatitisRegistry':
				$queryParams['MorbusType_SysNick'] = 'hepa';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyHepatitis ENH with (nolock) on ENH.EvnNotifyHepatitis_id = PR.EvnNotifyBase_id
					left join v_MorbusHepatitis MH with (nolock) on MH.Morbus_id = isnull(ENH.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
					outer apply (
						select TOP 1
							HepatitisDiagType_id
							,MorbusHepatitisDiag_setDT
							,HepatitisDiagActiveType_id
							,HepatitisFibrosisType_id
						from 
							v_MorbusHepatitisDiag MHD with (nolock)
						WHERE 
							MH.MorbusHepatitis_id = MHD.MorbusHepatitis_id
						ORDER BY
							MorbusHepatitisDiag_setDT DESC
					) as MHD
					left join v_HepatitisDiagType HDT with (nolock) on HDT.HepatitisDiagType_id = MHD.HepatitisDiagType_id  
					outer apply (
						select TOP 1
							MorbusHepatitisQueue_Num,
							HepatitisQueueType_id,
							MorbusHepatitisQueue_IsCure
						from 
							v_MorbusHepatitisQueue MHQ with (nolock)
						WHERE 
							MH.MorbusHepatitis_id = MHQ.MorbusHepatitis_id
						ORDER BY
							MorbusHepatitisQueue_IsCure ASC
					) as MHQ
					left join v_HepatitisQueueType HQT with (nolock) on HQT.HepatitisQueueType_id = MHQ.HepatitisQueueType_id  
					left join v_YesNo IsCure with (nolock) on IsCure.YesNo_id = ISNULL(MHQ.MorbusHepatitisQueue_IsCure,1) 
				';

				if (empty($data['AttachLpu_id']) && (empty($data['session']['groups']) || strpos($data['session']['groups'], 'HepatitisRegistry') < 0)) {
					$filter .= " and ( PC.Lpu_id  = :AttachLpu_id ) ";
					$queryParams['AttachLpu_id'] = $data['session']['lpu_id'];
				}

				if (isset($data['Diag_Code_From'])) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['Diag_id'])) {
					$filter .= " and ( PR.Diag_id = :Diag_id ) ";
					$queryParams['Diag_id'] = $data['Diag_id'];
				}

				// Диспучёт
				/*
				  if ( isset($data['DispLpu_id']) )
				  {
				  $filter .= " and PD.Lpu_id = :DispLpu_id ";
				  $queryParams['DispLpu_id'] = $data['DispLpu_id'];
				  }
				  if ( isset($data['DispLpuSection_id']) )
				  {
				  $filter .= " and PD.LpuSection_id = :DispLpuSection_id ";
				  $queryParams['DispLpuSection_id'] = $data['DispLpuSection_id'];
				  }
				  if ( isset($data['DispMedPersonal_id']) )
				  {
				  $filter .= " and PD.MedPersonal_id = :DispMedPersonal_id ";
				  $queryParams['DispMedPersonal_id'] = $data['DispMedPersonal_id'];
				  }
				  if ( isset($data['PersonDisp_begDate_Range'][0]) ) {
				  $filter .= " and PD.PersonDisp_begDate >= cast(:PersonDisp_begDate_Range_0 as datetime) ";
				  $queryParams['PersonDisp_begDate_Range_0'] = $data['PersonDisp_begDate_Range'][0];
				  }
				  if ( isset($data['PersonDisp_begDate_Range'][1]) ) {
				  $filter .= " and PD.PersonDisp_begDate <= cast(:PersonDisp_begDate_Range_1 as datetime) ";
				  $queryParams['PersonDisp_begDate_Range_1'] = $data['PersonDisp_begDate_Range'][1];
				  }
				  if ( isset($data['PersonDisp_endDate_Range'][0]) ) {
				  $filter .= " and PD.PersonDisp_endDate >= cast(:PersonDisp_endDate_Range_0 as datetime) ";
				  $queryParams['PersonDisp_endDate_Range_0'] = $data['PersonDisp_endDate_Range'][0];
				  }
				  if ( isset($data['PersonDisp_endDate_Range'][1]) ) {
				  $filter .= " and PD.PersonDisp_endDate <= cast(:PersonDisp_endDate_Range_1 as datetime) ";
				  $queryParams['PersonDisp_endDate_Range_1'] = $data['PersonDisp_endDate_Range'][1];
				  }
				  if ( isset($data['DispOutType_id']) )
				  {
				  $filter .= " and PD.DispOutType_id = :DispOutType_id ";
				  $queryParams['DispOutType_id'] = $data['DispOutType_id'];
				  }

				  if ( !empty($data['isDispAttachAddress']) )
				  {
				  if ( $data['isDispAttachAddress'] == 1 ) {
				  $filter .= " and PD.Lpu_id <> PC.Lpu_id ";
				  } elseif ( $data['isDispAttachAddress'] == 2 ) {
				  $filter .= " and PD.Lpu_id = PC.Lpu_id ";
				  }
				  }
				 */
				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (!empty($data['MorbusHepatitisDiag_setDT_Range'][0])) {
					$filter .= " and [MHD].MorbusHepatitisDiag_setDT >= cast(:MorbusHepatitisDiag_setDT_Range_0 as datetime) ";
					$queryParams['MorbusHepatitisDiag_setDT_Range_0'] = $data['MorbusHepatitisDiag_setDT_Range'][0];
				}
				if (!empty($data['MorbusHepatitisDiag_setDT_Range'][1])) {
					$filter .= " and [MHD].MorbusHepatitisDiag_setDT <= cast(:MorbusHepatitisDiag_setDT_Range_1 as datetime) ";
					$queryParams['MorbusHepatitisDiag_setDT_Range_1'] = $data['MorbusHepatitisDiag_setDT_Range'][1];
				}

				if (!empty($data['HepatitisDiagType_id'])) {
					$filter .= " and MHD.HepatitisDiagType_id = :HepatitisDiagType_id ";
					$queryParams['HepatitisDiagType_id'] = $data['HepatitisDiagType_id'];
				}

				if (!empty($data['HepatitisDiagActiveType_id'])) {
					$filter .= " and MHD.HepatitisDiagActiveType_id = :HepatitisDiagActiveType_id ";
					$queryParams['HepatitisDiagActiveType_id'] = $data['HepatitisDiagActiveType_id'];
				}

				if (!empty($data['HepatitisFibrosisType_id'])) {
					$filter .= " and MHD.HepatitisFibrosisType_id = :HepatitisFibrosisType_id ";
					$queryParams['HepatitisFibrosisType_id'] = $data['HepatitisFibrosisType_id'];
				}

				if (!empty($data['HepatitisEpidemicMedHistoryType_id'])) {
					$filter .= " and MH.HepatitisEpidemicMedHistoryType_id = :HepatitisEpidemicMedHistoryType_id ";
					$queryParams['HepatitisEpidemicMedHistoryType_id'] = $data['HepatitisEpidemicMedHistoryType_id'];
				}

				if (!empty($data['MorbusHepatitis_EpidNum'])) {
					$filter .= " and MH.MorbusHepatitis_EpidNum = :MorbusHepatitis_EpidNum ";
					$queryParams['MorbusHepatitis_EpidNum'] = $data['MorbusHepatitis_EpidNum'];
				}

				// Лаб. подтверждения
				if ((isset($data['MorbusHepatitisLabConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisLabConfirm_setDT_Range'][1])) || !empty($data['HepatitisLabConfirmType_id']) || !empty($data['MorbusHepatitisLabConfirm_Result'])) {
					$filterDop = '';
					if (isset($data['MorbusHepatitisLabConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisLabConfirm_setDT_Range'][1])) {
						$queryParams['MorbusHepatitisLabConfirm_setDT_Range_0'] = $data['MorbusHepatitisLabConfirm_setDT_Range'][0];
						$queryParams['MorbusHepatitisLabConfirm_setDT_Range_1'] = $data['MorbusHepatitisLabConfirm_setDT_Range'][1];
						$filterDop .= ' and cast(MHLC.MorbusHepatitisLabConfirm_setDT as date) between :MorbusHepatitisLabConfirm_setDT_Range_0 and :MorbusHepatitisLabConfirm_setDT_Range_1 ';
					}

					if (!empty($data['HepatitisLabConfirmType_id'])) {
						$queryParams['HepatitisLabConfirmType_id'] = $data['HepatitisLabConfirmType_id'];
						$filterDop .= ' and MHLC.HepatitisLabConfirmType_id = :HepatitisLabConfirmType_id ';
					}

					if (!empty($data['MorbusHepatitisLabConfirm_Result'])) {
						$queryParams['MorbusHepatitisLabConfirm_Result'] = '%' . $data['MorbusHepatitisLabConfirm_Result'] . '%';
						$filterDop .= ' and MHLC.MorbusHepatitisLabConfirm_Result LIKE :MorbusHepatitisLabConfirm_Result ';
					}
					$query .= '
						inner join v_MorbusHepatitisLabConfirm MHLC with (nolock) on MH.MorbusHepatitis_id = MHLC.MorbusHepatitis_id ' . $filterDop;
				}

				// Инстр. подтверждения
				if ((isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][1])) || !empty($data['HepatitisFuncConfirmType_id']) || !empty($data['MorbusHepatitisFuncConfirm_Result'])) {
					$filterDop = '';
					if (isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][1])) {
						$queryParams['MorbusHepatitisFuncConfirm_setDT_Range_0'] = $data['MorbusHepatitisFuncConfirm_setDT_Range'][0];
						$queryParams['MorbusHepatitisFuncConfirm_setDT_Range_1'] = $data['MorbusHepatitisFuncConfirm_setDT_Range'][1];
						$filterDop .= ' and cast(MHFC.MorbusHepatitisLabConfirm_setDT as date) between :MorbusHepatitisFuncConfirm_setDT_Range_0 and :MorbusHepatitisFuncConfirm_setDT_Range_1 ';
					}

					if (!empty($data['HepatitisFuncConfirmType_id'])) {
						$queryParams['HepatitisFuncConfirmType_id'] = $data['HepatitisFuncConfirmType_id'];
						$filterDop .= ' and MHFC.HepatitisFuncConfirmType_id = :HepatitisFuncConfirmType_id ';
					}

					if (!empty($data['MorbusHepatitisFuncConfirm_Result'])) {
						$queryParams['MorbusHepatitisFuncConfirm_Result'] = '%' . $data['MorbusHepatitisFuncConfirm_Result'] . '%';
						$filterDop .= ' and MHFC.MorbusHepatitisFuncConfirm_Result LIKE :MorbusHepatitisFuncConfirm_Result ';
					}
					$query .= '
						inner join v_MorbusHepatitisFuncConfirm MHFC with (nolock) on MH.MorbusHepatitis_id = MHFC.MorbusHepatitis_id ' . $filterDop;
				}

				// Лечение
				if (isset($data['MorbusHepatitisCure_begDT']) || isset($data['MorbusHepatitisCure_endDT']) || !empty($data['HepatitisResultClass_id']) || !empty($data['HepatitisSideEffectType_id']) || !empty($data['MorbusHepatitisCure_Drug'])) {
					$filterDop = '';
					if (isset($data['MorbusHepatitisCure_begDT'])) {
						$queryParams['MorbusHepatitisCure_begDT'] = $data['MorbusHepatitisCure_begDT'];
						$filterDop .= ' and cast(MHC.MorbusHepatitisCure_begDT as date) >= :MorbusHepatitisCure_begDT and MHC.MorbusHepatitisCure_begDT is not null ';
					}

					if (isset($data['MorbusHepatitisCure_endDT'])) {
						$queryParams['MorbusHepatitisCure_endDT'] = $data['MorbusHepatitisCure_endDT'];
						$filterDop .= ' and cast(MHC.MorbusHepatitisCure_endDT as date) <= :MorbusHepatitisCure_endDT and MHC.MorbusHepatitisCure_endDT is not null ';
					}

					if (!empty($data['HepatitisResultClass_id'])) {
						$queryParams['HepatitisResultClass_id'] = $data['HepatitisResultClass_id'];
						$filterDop .= ' and MHC.HepatitisResultClass_id = :HepatitisResultClass_id ';
					}

					if (!empty($data['HepatitisSideEffectType_id'])) {
						$queryParams['HepatitisSideEffectType_id'] = $data['HepatitisSideEffectType_id'];
						$filterDop .= ' and MHC.HepatitisSideEffectType_id = :HepatitisSideEffectType_id ';
					}

					if (!empty($data['MorbusHepatitisCure_Drug'])) {
						$queryParams['MorbusHepatitisCure_Drug'] = '%' . $data['MorbusHepatitisCure_Drug'] . '%';
						$filterDop .= ' and MHC.MorbusHepatitisCure_Drug LIKE :MorbusHepatitisCure_Drug ';
					}
					$query .= '
						inner join v_MorbusHepatitisCure MHC with (nolock) on MH.MorbusHepatitis_id = MHC.MorbusHepatitis_id ' . $filterDop;
				}

				// Очередь

				if (!empty($data['HepatitisQueueType_id'])) {
					$filter .= " and MHQ.HepatitisQueueType_id = :HepatitisQueueType_id ";
					$queryParams['HepatitisQueueType_id'] = $data['HepatitisQueueType_id'];
				}

				if (!empty($data['MorbusHepatitisQueue_Num'])) {
					$filter .= " and MHQ.MorbusHepatitisQueue_Num = :MorbusHepatitisQueue_Num ";
					$queryParams['MorbusHepatitisQueue_Num'] = $data['MorbusHepatitisQueue_Num'];
				}

				if (!empty($data['MorbusHepatitisQueue_IsCure'])) {
					$filter .= " and isnull(MHQ.MorbusHepatitisQueue_IsCure,1) = :MorbusHepatitisQueue_IsCure ";
					$queryParams['MorbusHepatitisQueue_IsCure'] = $data['MorbusHepatitisQueue_IsCure'];
				}


				//print_r($data); exit;

				break;

			case 'EvnNotifyRegister':
				$this->load->library('swPersonRegister');

				if (empty($data['PersonRegisterType_SysNick']) || false == swPersonRegister::isAllow($data['PersonRegisterType_SysNick'])) {
					return false;
				}
				$queryParams['PersonRegisterType_SysNick'] = $data['PersonRegisterType_SysNick'];

				if (!empty($data['NotifyType_id'])) {
					$filter .= ' and EN.NotifyType_id = :NotifyType_id ';
					$queryParams['NotifyType_id'] = $data['NotifyType_id'];
				}
				if ($data['PersonPeriodicType_id'] == 2) {
					$joinEvnNotifyRegisterOn = 'E.PersonEvn_id = PS.PersonEvn_id and E.Server_id = PS.Server_id';
				} else {
					$joinEvnNotifyRegisterOn = 'E.Person_id = PS.Person_id';
				}

				$filter .= "
					and PRT.PersonRegisterType_SysNick = :PersonRegisterType_SysNick
					and ISNULL(E.Evn_deleted, 1) = 1
					and E.EvnClass_id = 176
				";

				$query .= "
					inner join Evn E with (nolock) on {$joinEvnNotifyRegisterOn}
					inner join EvnNotifyRegister EN with (nolock) on EN.EvnNotifyRegister_id = E.Evn_id
					inner join EvnNotifyBase ENB with (nolock) on ENB.EvnNotifyBase_id = E.Evn_id
					inner join v_PersonRegisterType PRT with (nolock) on PRT.PersonRegisterType_id = EN.PersonRegisterType_id
					inner join v_NotifyType NT with (nolock) on NT.NotifyType_id = EN.NotifyType_id
					left join v_PersonRegister PR with (nolock) on (EN.NotifyType_id in (2,3) AND PR.PersonRegister_id = EN.PersonRegister_id) OR (1 = EN.NotifyType_id AND PR.EvnNotifyBase_id = EN.EvnNotifyRegister_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(EN.Diag_id,PR.Diag_id)
					left join v_Diag DiagGroup with (nolock) on DiagGroup.Diag_id = Diag.Diag_pid
					left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ENB.MedPersonal_id and MP.Lpu_id = E.Lpu_id
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = E.Lpu_id
					left join v_MorbusType MT with (nolock) on MT.MorbusType_id = ENB.MorbusType_id
				";
				/*
				  outer apply (
				  select top 1 MT.MorbusType_SysNick
				  from v_PersonRegisterDiag PRD with (nolock)
				  left join v_MorbusType MT with (nolock) on MT.MorbusType_id = PRD.MorbusType_id
				  where PRD.PersonRegisterType_id = PRT.PersonRegisterType_id
				  and PRD.Diag_id = Diag.Diag_id
				  ) MT
				 */

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['Diag_Code_Group'])) {
					$filter .= ' and ( DiagGroup.Diag_id = :Diag_Code_Group or Diag.Diag_id = :Diag_Code_Group ) ';
					$queryParams['Diag_Code_Group'] = $data['Diag_Code_Group'];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and cast(E.Evn_setDT as date) >= cast(:EvnNotifyBase_setDT_Range_0 as date) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and cast(E.Evn_setDT as date) <= cast(:EvnNotifyBase_setDT_Range_1 as date) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
							and EN.NotifyType_id = 1
							and ENB.EvnNotifyBase_niDate is null
							and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
							and EN.NotifyType_id = 1
							and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}

				if (isset($data['Lpu_oid'])) {
					$filter .= ' and EN.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				if ($this->getRegionNick() == 'khak' && $data['PersonRegisterType_SysNick'] == 'orphan') {
					$filter .= " and ( Diag.Diag_Code in 
						('D59.3','D59.5','D61.9','D68.2','D69.3','D84.1','E22.8','E70.0','E70.1','E70.2','E71.0','E71.1','E71.3','E72.1','E72.3','E74.2','E75.2','E76.0','E76.1','E76.2','E80.2','E83.0','I27.0','M08.2','Q78.0') ) ";
				}
				break;

			case 'EvnNotifyOrphan':
				$query .= "
					outer apply (
						select
							EvnNotifyOrphan.EvnNotifyOrphan_id,
							EvnNotifyOrphan.EvnNotifyOrphan_pid,
							EvnNotifyOrphan.EvnNotifyOrphan_setDT,
							'Направление на включение в регистр' as EvnNotifyType_Name,
							'EvnNotifyOrphan' as EvnNotifyType_SysNick,
							EvnNotifyOrphan.Morbus_id,
							EvnNotifyOrphan.EvnNotifyOrphan_niDate,
							EvnNotifyOrphan.Lpu_oid,
							EvnNotifyOrphan.Lpu_id,
							EvnNotifyOrphan.MedPersonal_id,
							EvnNotifyOrphan.pmUser_updId,
							PersonRegister.PersonRegister_id
						from v_EvnNotifyOrphan EvnNotifyOrphan with (nolock) 
						left join PersonRegister with (nolock) on EvnNotifyOrphan.EvnNotifyOrphan_id = PersonRegister.EvnNotifyBase_id
						where " . ( ($data['PersonPeriodicType_id'] == 2) ? 'EvnNotifyOrphan.PersonEvn_id = PS.PersonEvn_id and EvnNotifyOrphan.Server_id = PS.Server_id' : 'EvnNotifyOrphan.Person_id = PS.Person_id' ) . "
						union all
						select
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_id as EvnNotifyOrphan_id,
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_pid as EvnNotifyOrphan_pid,
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_setDT as EvnNotifyOrphan_setDT,
							' Извещение на исключение из регистра' as EvnNotifyType_Name,
							'EvnNotifyOrphanOut' as EvnNotifyType_SysNick,
							EvnNotifyOrphanOut.Morbus_id,
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_niDate as EvnNotifyOrphan_niDate,
							null as Lpu_oid,
							EvnNotifyOrphanOut.Lpu_id,
							EvnNotifyOrphanOut.MedPersonal_id,
							EvnNotifyOrphanOut.pmUser_updId,
							PersonRegister.PersonRegister_id
						from v_EvnNotifyOrphanOut EvnNotifyOrphanOut with (nolock) 
						inner join PersonRegister with (nolock) on EvnNotifyOrphanOut.Morbus_id = PersonRegister.Morbus_id
						where " . ( ($data['PersonPeriodicType_id'] == 2) ? 'EvnNotifyOrphanOut.PersonEvn_id = PS.PersonEvn_id and EvnNotifyOrphanOut.Server_id = PS.Server_id' : 'EvnNotifyOrphanOut.Person_id = PS.Person_id' ) . "
						union all
						select
							EvnDirectionOrphan.EvnDirectionOrphan_id as EvnNotifyOrphan_id,
							EvnDirectionOrphan.EvnDirectionOrphan_pid as EvnNotifyOrphan_pid,
							EvnDirectionOrphan.EvnDirectionOrphan_setDT as EvnNotifyOrphan_setDT,
							'Направление на внесение изменений в регистр' as EvnNotifyType_Name,
							'EvnDirectionOrphan' as EvnNotifyType_SysNick,
							EvnDirectionOrphan.Morbus_id,
							null as EvnNotifyOrphan_niDate,
							null as Lpu_oid,
							EvnDirectionOrphan.Lpu_id,
							EvnDirectionOrphan.MedPersonal_id,
							EvnDirectionOrphan.pmUser_updId,
							PersonRegister.PersonRegister_id
						from v_EvnDirectionOrphan EvnDirectionOrphan with (nolock) 
						inner join PersonRegister with (nolock) on EvnDirectionOrphan.Morbus_id = PersonRegister.Morbus_id
						where " . ( ($data['PersonPeriodicType_id'] == 2) ? 'EvnDirectionOrphan.PersonEvn_id = PS.PersonEvn_id and EvnDirectionOrphan.Server_id = PS.Server_id' : 'EvnDirectionOrphan.Person_id = PS.Person_id' ) . "
					) ENO
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENO.Morbus_id 
					left join v_PersonRegister PR with (nolock) on ENO.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
					left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ENO.MedPersonal_id and MP.Lpu_id = ENO.Lpu_id
				";

				switch ($data['EvnNotifyType_SysNick']) {
					case 'EvnNotifyOrphan':
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphan\'
					';
						break;
					case 'EvnNotifyOrphanOut':
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphanOut\'
					';
						break;
					case 'EvnDirectionOrphan':
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnDirectionOrphan\'
					';
						break;
					default: //all
						break;
				}
				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and ENO.EvnNotifyOrphan_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and ENO.EvnNotifyOrphan_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if (isset($data['Lpu_sid'])) {
					$filter .= ' and ENO.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_sid'];
				}

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphan\'
					and ENO.EvnNotifyOrphan_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphan\'
					and (ENO.EvnNotifyOrphan_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;

			case 'EvnNotifyCrazy': // Психиатрия
				$query .= '
					inner join v_EvnNotifyCrazy ENC with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) . '
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
				';
				// ограничение по группе диагнозов по психиатрии
				$filter .= ' and ( Diag_pid not in (705,706,707,708,709,710,711,712,713,714) ) ';

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and ENC.EvnNotifyCrazy_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and ENC.EvnNotifyCrazy_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/* if ( isset($data['Lpu_sid']) ) {
				  $filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
				  $queryParams['Lpu_oid'] = $data['Lpu_sid'];
				  } */

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENC.EvnNotifyCrazy_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENC.EvnNotifyCrazy_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyNarko': // Психиатрия
				$query .= '
					inner join v_EvnNotifyNarco ENC with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) . '
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyNarco_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
				';
				// ограничение по группе диагнозов по наркологии
				$filter .= ' and ( Diag_pid in (705,706,707,708,709,710,711,712,713,714) ) ';

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and ENC.EvnNotifyNarco_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and ENC.EvnNotifyNarco_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/* if ( isset($data['Lpu_sid']) ) {
				  $filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
				  $queryParams['Lpu_oid'] = $data['Lpu_sid'];
				  } */

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENC.EvnNotifyNarco_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENC.EvnNotifyNarco_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyNephro': // Нефрология
				$query .= '
					inner join v_EvnNotifyNephro ENC with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) . '
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyNephro_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
					left join v_Diag Diag with (nolock) on Diag.Diag_id = ENC.Diag_id -- isnull(PR.Diag_id, MO.Diag_id)
				';

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and CONVERT(varchar(10), ENC.EvnNotifyNephro_setDT, 120) >= :EvnNotifyBase_setDT_Range_0 ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and CONVERT(varchar(10), ENC.EvnNotifyNephro_setDT, 120) <= :EvnNotifyBase_setDT_Range_1 ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/* if ( isset($data['Lpu_sid']) ) {
				  $filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
				  $queryParams['Lpu_oid'] = $data['Lpu_sid'];
				  } */

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENC.EvnNotifyNephro_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENC.EvnNotifyNephro_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}

				if (!empty($data['isNotVizitMonth']) && $data['isNotVizitMonth'] == 2) {
					$filter .= ' and not exists (
						select top 1 1
						from v_EvnVizitPL vp with (nolock)
						left join v_MedStaffFactCache msfc with (nolock) on vp.MedStaffFact_id = msfc.MedStaffFact_id
						where 
							vp.Person_id = PS.Person_id 
							and vp.EvnVizitPL_setDT >= ENC.EvnNotifyNephro_setDT
							and msfc.Post_id = 39 -- врач-нефролог
					) ';
				}
				break;
			case 'EvnNotifyProf': // Профзаболевания
				$query .= '
					inner join v_EvnNotifyProf ENC with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) . '
					-- left join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyProf_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(PR.Diag_id, ENC.Diag_id)
				';

				if (isset($data['Lpu_did'])) {
					$filter .= ' and ENC.Lpu_did = :Lpu_did ';
					$queryParams['Lpu_did'] = $data['Lpu_did'];
				}

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and CONVERT(varchar(10), ENC.EvnNotifyProf_setDT, 120) >= :EvnNotifyBase_setDT_Range_0 ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and CONVERT(varchar(10), ENC.EvnNotifyProf_setDT, 120) <= :EvnNotifyBase_setDT_Range_1 ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/* if ( isset($data['Lpu_sid']) ) {
				  $filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
				  $queryParams['Lpu_oid'] = $data['Lpu_sid'];
				  } */

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENC.EvnNotifyProf_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENC.EvnNotifyProf_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}

				if (isset($data['OrgWork_id'])) {
					$filter .= " and ENC.Org_id = :OrgWork_id";
					$queryParams['OrgWork_id'] = $data['OrgWork_id'];
				}

				break;
			case 'EvnNotifyTub': // Туберкулез
				$query .= '
					inner join v_EvnNotifyTub ENC with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) . '
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					outer apply (
						select top 1 Lpu_id
						from v_PersonCard with (nolock)
						where Person_id = PS.Person_id and LpuAttachType_id = 1
					) as PC
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyTub_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
					left join v_Diag DiagENC with (nolock) on DiagENC.Diag_id = ENC.Diag_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(PR.Diag_id, MO.Diag_id)
				';

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['TubDiagSop_id'])) {
					$filter .= ' and  
						exists(select top 1 1 from v_TubDiagSopLink tdsl with (nolock) where tdsl.EvnNotifyTub_id = ENC.EvnNotifyTub_id and tdsl.TubDiagSop_id = :TubDiagSop_id)
					 ';
					$queryParams['TubDiagSop_id'] = $data['TubDiagSop_id'];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and ENC.EvnNotifyTub_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and ENC.EvnNotifyTub_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if (isset($data['EvnNotifyTub_IsFirstDiag'])) {
					$filter .= ' and ENC.EvnNotifyTub_IsFirstDiag = :EvnNotifyTub_IsFirstDiag';
					$queryParams['EvnNotifyTub_IsFirstDiag'] = $data['EvnNotifyTub_IsFirstDiag'];
				}

				if (isset($data['PersonCategoryType_id'])) {
					$filter .= ' and ENC.PersonCategoryType_id = :PersonCategoryType_id';
					$queryParams['PersonCategoryType_id'] = $data['PersonCategoryType_id'];
				}

				if (isset($data['TubSurveyGroupType_id'])) {
					if ($data['TubSurveyGroupType_id'] == 2) {
						$filter .= ' and ENC.TubSurveyGroupType_id is not null';
					} else {
						$filter .= ' and ENC.TubSurveyGroupType_id is null';
					}
				}

				/* if ( isset($data['Lpu_sid']) ) {
				  $filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
				  $queryParams['Lpu_oid'] = $data['Lpu_sid'];
				  } */

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENC.EvnNotifyTub_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENC.EvnNotifyTub_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyHIV': // Извещения ВИЧ
				$queryParams['MorbusType_SysNick'] = 'hiv';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_EvnNotifyBase ENB with (nolock) on ENB.MorbusType_id = v_MorbusType.MorbusType_id 
						and ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENB.PersonEvn_id = PS.PersonEvn_id and ENB.Server_id = PS.Server_id' : 'ENB.Person_id = PS.Person_id' ) . '
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENB.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENB.EvnNotifyBase_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
					left join EvnClass with (nolock) on ENB.EvnClass_id = EvnClass.EvnClass_id
				';

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and ENB.EvnNotifyBase_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and ENB.EvnNotifyBase_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if (isset($data['HIVNotifyType_id'])) {
					$query .= '
					inner join v_HIVNotifyType HIVNotifyType with (nolock) on EvnClass.EvnClass_SysNick = HIVNotifyType.HIVNotifyType_SysNick
					';
					$filter .= ' and HIVNotifyType.HIVNotifyType_id = :HIVNotifyType_id ';
					$queryParams['HIVNotifyType_id'] = $data['HIVNotifyType_id'];
				}

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENB.EvnNotifyBase_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyVener': // Венер
				$query .= '
					inner join v_EvnNotifyVener ENC with (nolock) on ' . ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) . '
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyVener_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
				';

				if (isset($data['Diag_Code_From'])) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if (isset($data['EvnNotifyBase_setDT_Range'][0])) {
					$filter .= ' and ENC.EvnNotifyVener_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if (isset($data['EvnNotifyBase_setDT_Range'][1])) {
					$filter .= ' and ENC.EvnNotifyVener_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/* if ( isset($data['Lpu_sid']) ) {
				  $filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
				  $queryParams['Lpu_oid'] = $data['Lpu_sid'];
				  } */

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
					and ENC.EvnNotifyVener_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
					and (ENC.EvnNotifyVener_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'PalliatNotify':
				$query .= "
					inner join v_EvnNotifyBase ENB with(nolock) on ENB.Person_id = PS.Person_id
					inner join v_PalliatNotify PN with(nolock) on PN.EvnNotifyBase_id = ENB.EvnNotifyBase_id
					inner join v_Diag Diag with(nolock) on Diag.Diag_id = PN.Diag_id
					inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = ENB.Lpu_id
					left join v_Morbus M with(nolock) on M.Morbus_id = ENB.Morbus_id
					left join v_PersonRegister PR with(nolock) on PR.EvnNotifyBase_id = ENB.EvnNotifyBase_id
					left join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					outer apply (
						select top 1 PC.*
						from v_PersonCard PC with (nolock)
						where PC.Person_id = PS.Person_id
						and @getDT between PC.PersonCard_begDate and isnull(PC.PersonCard_endDate, @getDT)
						and PC.LpuAttachType_id = 1
						order by PC.PersonCard_id desc
					) PC
					left join v_Lpu AttachLpu with(nolock) on AttachLpu.Lpu_id = PC.Lpu_id
				";

				if (isset($data['Lpu_sid'])) {
					$filter .= ' and Lpu.Lpu_id = :Lpu_sid ';
					$queryParams['Lpu_sid'] = $data['Lpu_sid'];
				}

				if (isset($data['isNotifyProcessed'])) {
					if ($data['isNotifyProcessed'] == 1) {
						$filter .= '
							and ENB.EvnNotifyBase_niDate is null and PR.PersonRegister_id is null
						';
					} elseif ($data['isNotifyProcessed'] == 2) {
						$filter .= '
							and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'OrphanRegistry':
				$queryParams['MorbusType_SysNick'] = 'orphan';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					inner join v_Morbus M with (nolock) on M.Morbus_id = PR.Morbus_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyOrphan EN with (nolock) on EN.EvnNotifyOrphan_id = PR.EvnNotifyBase_id
					left join v_MorbusOrphan MO with (nolock) on MO.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = M.Diag_id
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'ACSRegistry':
				$queryParams['MorbusType_SysNick'] = 'acs';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					inner join v_Morbus M with (nolock) on M.Morbus_id = PR.Morbus_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyBase EN with (nolock) on EN.EvnNotifyBase_id = PR.EvnNotifyBase_id
					left join v_MorbusACS MA with (nolock) on MA.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Lpu LpuAdd with (nolock) on LpuAdd.Lpu_id = PR.Lpu_iid
					left join v_Diag Diag with (nolock) on Diag.Diag_id = M.Diag_id
				';

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				if (!empty($data['DiagACS_id'])) {
					$filter .= " and PR.Diag_id = :DiagACS_id";
					$queryParams['DiagACS_id'] = $data['DiagACS_id'];
				}

				if (!empty($data['Lpu_iid'])) {
					$filter .= " and PR.Lpu_iid = :Lpu_iid";
					$queryParams['Lpu_iid'] = $data['Lpu_iid'];
				}

				if (!empty($data['MorbusACS_IsST'])) {
					$filter .= " and MA.MorbusACS_IsST = :MorbusACS_IsST";
					$queryParams['MorbusACS_IsST'] = $data['MorbusACS_IsST'];
				}

				if (!empty($data['MorbusACS_IsCoronary'])) {
					$filter .= " and MA.MorbusACS_IsCoronary = :MorbusACS_IsCoronary";
					$queryParams['MorbusACS_IsCoronary'] = $data['MorbusACS_IsCoronary'];
				}

				if (!empty($data['MorbusACS_IsTransderm'])) {
					$filter .= " and MA.MorbusACS_IsTransderm = :MorbusACS_IsTransderm";
					$queryParams['MorbusACS_IsTransderm'] = $data['MorbusACS_IsTransderm'];
				}

				break;
			case 'CrazyRegistry':
				$queryParams['MorbusType_SysNick'] = 'crazy';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyCrazy EN with (nolock) on EN.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_MorbusCrazy MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_CrazyCauseEndSurveyType CCEST with(nolock) on CCEST.CrazyCauseEndSurveyType_id = MO.CrazyCauseEndSurveyType_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Lpu Lpu2 with (nolock) on Lpu2.Lpu_id = PR.Lpu_iid
					outer apply (select top 1 CD.Diag_id from v_MorbusCrazyDiag MCD with(nolock)
					left join v_CrazyDiag CD with(nolock) on CD.CrazyDiag_id=MCD.CrazyDiag_id 
					where MCD.MorbusCrazy_id=MO.MorbusCrazy_id
					order by MCD.MorbusCrazyDiag_setDT desc
					)CDiag
					left join v_Diag Diag with (nolock) on Diag.Diag_id = CDiag.Diag_id
					left join v_Diag PRDiag with (nolock) on PRDiag.Diag_id = PR.Diag_id
				';
				// ограничение по группе диагнозов по психиатрии
				//$filter .= ' and ( PRDiag.Diag_pid not in (705,706,707,708,709,710,711,712,713,714) ) ';
				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['RegLpu_id']) && !empty($data['RegLpu_id'])) {

					$filter .= " and Lpu2.Lpu_id = :RegLpu_id";
					$queryParams['RegLpu_id'] = $data['RegLpu_id'];
				}
				break;
			case 'NarkoRegistry':
				$queryParams['MorbusType_SysNick'] = 'narc';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyCrazy EC with (nolock) on EC.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_EvnNotifyNarco EN with (nolock) on EN.EvnNotifyNarco_id = PR.EvnNotifyBase_id
					left join v_MorbusCrazy MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_CrazyCauseEndSurveyType CCEST with(nolock) on CCEST.CrazyCauseEndSurveyType_id = MO.CrazyCauseEndSurveyType_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu2 with (nolock) on Lpu2.Lpu_id = PR.Lpu_iid
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					outer apply (select top 1 CD.Diag_id from v_MorbusCrazyDiag MCD with(nolock)
					left join v_CrazyDiag CD with(nolock) on CD.CrazyDiag_id=MCD.CrazyDiag_id 
					where MCD.MorbusCrazy_id=MO.MorbusCrazy_id
					order by MCD.MorbusCrazyDiag_setDT desc
					)CDiag
					left join v_Diag Diag with (nolock) on Diag.Diag_id = CDiag.Diag_id
					left join v_Diag PRDiag with (nolock) on PRDiag.Diag_id = PR.Diag_id
				';
				// ограничение по группе диагнозов по наркогии
				//$filter .= ' and ( PRDiag.Diag_pid in (705,706,707,708,709,710,711,712,713,714) ) ';
				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				if (isset($data['RegLpu_id']) && !empty($data['RegLpu_id'])) {

					$filter .= " and Lpu2.Lpu_id = :RegLpu_id";
					$queryParams['RegLpu_id'] = $data['RegLpu_id'];
				}
				break;
			case 'PersonRegisterBase':
				$this->load->library('swPersonRegister');

				if (empty($data['PersonRegisterType_SysNick']) || false == swPersonRegister::isAllow($data['PersonRegisterType_SysNick'])) {
					return false;
				}
				$queryParams['PersonRegisterType_SysNick'] = $data['PersonRegisterType_SysNick'];

				$query .= "
					inner join v_PersonRegisterType PRT with (nolock) on PRT.PersonRegisterType_SysNick like :PersonRegisterType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Lpu LpuIns with (nolock) on LpuIns.Lpu_id = pr.Lpu_iid
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					left join v_MorbusType MT with (nolock) on MT.MorbusType_id = PR.MorbusType_id
				";

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				if (false == empty($data['PersonRegister_Code'])) {
					$filter .= " and PR.PersonRegister_Code = :PersonRegister_Code";
					$queryParams['PersonRegister_Code'] = $data['PersonRegister_Code'];
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;

			case 'PalliatRegistry':
				$this->load->library('swPersonRegister');

				if (empty($data['PersonRegisterType_SysNick']) || false == swPersonRegister::isAllow($data['PersonRegisterType_SysNick'])) {
					return false;
				}
				$queryParams['PersonRegisterType_SysNick'] = $data['PersonRegisterType_SysNick'];

				$query .= "
					inner join v_PersonRegisterType PRT with (nolock) on PRT.PersonRegisterType_SysNick like :PersonRegisterType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
					left join v_MorbusPalliat MO with (nolock) on MO.Morbus_id = PR.Morbus_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Lpu LpuIns with (nolock) on LpuIns.Lpu_id = pr.Lpu_iid
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					left join v_MorbusType MT with (nolock) on MT.MorbusType_id = PR.MorbusType_id
				";

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}
				
				if (!empty($data['PersonRegisterOutCause_id'])) {
					$queryParams['PersonRegisterOutCause_id'] = $data['PersonRegisterOutCause_id'];
					$filter .= ' and PR.PersonRegisterOutCause_id = :PersonRegisterOutCause_id ';
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				if (false == empty($data['PersonRegister_Code'])) {
					$filter .= " and PR.PersonRegister_Code = :PersonRegister_Code";
					$queryParams['PersonRegister_Code'] = $data['PersonRegister_Code'];
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (!empty($data['MorbusPalliat_IsIVL'])) {
					$filter .= " and MO.MorbusPalliat_IsIVL = :MorbusPalliat_IsIVL";
					$queryParams['MorbusPalliat_IsIVL'] = $data['MorbusPalliat_IsIVL'];
				}

				if (!empty($data['AnesthesiaType_id'])) {
					if ($data['AnesthesiaType_id'] < 0) {
						$filter .= " and MO.MorbusPalliat_IsAnesthesia = 1";
					} else {
						$filter .= " and MO.AnesthesiaType_id = :AnesthesiaType_id";
						$queryParams['AnesthesiaType_id'] = $data['AnesthesiaType_id'];
					}
				}

				if (!empty($data['MorbusPalliat_IsZond'])) {
					$filter .= " and MO.MorbusPalliat_IsZond = :MorbusPalliat_IsZond";
					$queryParams['MorbusPalliat_IsZond'] = $data['MorbusPalliat_IsZond'];
				}

				if (!empty($data['ViolationsDegreeType_id'])) {
					$filter .= " and MO.ViolationsDegreeType_id = :ViolationsDegreeType_id";
					$queryParams['ViolationsDegreeType_id'] = $data['ViolationsDegreeType_id'];
				}

				if (!empty($data['Lpu_sid'])) {
					$filter .= " and MO.Lpu_sid = :Lpu_sid";
					$queryParams['Lpu_sid'] = $data['Lpu_sid'];
				}

				if (!empty($data['Lpu_aid'])) {
					$filter .= " and MO.Lpu_aid = :Lpu_aid";
					$queryParams['Lpu_aid'] = $data['Lpu_aid'];
				}

				if (!isSuperAdmin() && !haveARMType('spec_mz') && !havingGroup('RegistryPalliatCareAll')) {
					$filter .= " and (
						PC.Lpu_id = :Lpu_id
						or pr.Lpu_iid = :Lpu_id
						or MO.Lpu_sid = :Lpu_id
						or MO.Lpu_aid = :Lpu_id
					)";
					$queryParams['Lpu_id'] = $data['Lpu_id'];
				}

				break;
			case 'NephroRegistry':
				$queryParams['MorbusType_SysNick'] = 'nephro';
				//фильтр по сахарному диабету(1,2 тип)
				$queryParams['Diab_Diag_Code_Start'] = 'E10.0';
				$queryParams['Diab_Diag_Code_End'] = 'E11.9';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyNephro EN with (nolock) on EN.EvnNotifyNephro_id = PR.EvnNotifyBase_id
					left join v_MorbusNephro MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					outer apply (
						select top 1 v_evndiag.Diag_id
						from v_evndiag (nolock)
						where v_evndiag.Morbus_id = PR.Morbus_id
						and v_evndiag.evndiag_pid is null
						order by v_evndiag.evndiag_setdate desc
					) EvnDiagNephro
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(PR.Diag_id,EvnDiagNephro.Diag_id)

					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnDiagSpec EvnDiagSpec with (nolock) on EvnDiagSpec.Person_id = PS.Person_id and D.Diag_id = EvnDiagSpec.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnDiagSpec
					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnSection EvnSection with (nolock) on EvnSection.Person_id = PS.Person_id and D.Diag_id = EvnSection.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnSection
					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnVizitPL EvnVizitPL with (nolock) on EvnVizitPL.Person_id = PS.Person_id and D.Diag_id = EvnVizitPL.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnVizitPL
					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnDiagPLSop EvnDiagPLSop with (nolock) on EvnDiagPLSop.Person_id = PS.Person_id and D.Diag_id = EvnDiagPLSop.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnDiagPLSop
					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnDiagPS EvnDiagPS with (nolock) on EvnDiagPS.Person_id = PS.Person_id and D.Diag_id = EvnDiagPS.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnDiagPS
					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnUslugaDispDop EvnUslugaDispDop with (nolock) on EvnUslugaDispDop.Person_id = PS.Person_id and D.Diag_id = EvnUslugaDispDop.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnUslugaDispDop
					outer apply (
						select top 1 D.Diag_Code
						from v_Diag D (nolock)
						inner join v_EvnDiagDopDisp EvnDiagDopDisp with (nolock) on EvnDiagDopDisp.Person_id = PS.Person_id and D.Diag_id = EvnDiagDopDisp.Diag_id
						where D.Diag_Code >= :Diab_Diag_Code_Start and D.Diag_Code <= :Diab_Diag_Code_End and D.DiagLevel_id = 4
					) DiabEvnDiagDopDisp
					outer apply (
						select top 1 EVPL.EvnVizitPL_setDate
						from v_EvnVizitPL EVPL (nolock)
						left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = EVPL.MedStaffFact_id
						left join v_PostMed PM with (nolock) on PM.PostMed_id = MSF.Post_id
						where EVPL.Person_id = PS.Person_id and PM.PostMed_Code = 39
						order by EvnVizitPL_setDate desc
					) lastVizitDate
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if (getRegionNick() == 'ufa') {

						$query .= "
							left join v_NephroResultType NRT with (nolock) on NRT.NephroResultType_id = MO.NephroResultType_id
						";

						if (in_array($data['PersonRegisterType_id'], array(2, 3, 4)))
							$filter .= ' and PR.PersonRegister_disDate is null ';

						switch ($data['PersonRegisterType_id']) {

							case 2:
								$filter .= ' and NRT.NephroResultType_Code = 1 ';
								break;

							case 3:
								$filter .= ' and NRT.NephroResultType_Code in (2,3,5)';
								break;

							case 4:
								$filter .= ' and NRT.NephroResultType_Code = 4 ';
								break;

							case 5:
								$filter .= ' and NRT.NephroResultType_Code in (6,7,8) or PR.PersonRegister_disDate is not null';
							break;

						}
					} else {
						if ($data['PersonRegisterType_id'] == 2) {
							// Включенные в регистр
							$filter .= ' and PR.PersonRegister_disDate is null ';
						}
						if ($data['PersonRegisterType_id'] == 3) {
							// Исключенные из регистра
							$filter .= ' and PR.PersonRegister_disDate is not null ';
						}
					}
				}

				if(!empty($data['NephroPersonStatus_id'])){
					$queryParams['NephroPersonStatus_id'] = $data['NephroPersonStatus_id'];
					$filter .= ' and MO.NephroPersonStatus_id = :NephroPersonStatus_id';
				}

				if (!empty($data['PersonCountAtDate'])) {
					$queryParams['PersonCountAtDate'] = $data['PersonCountAtDate'];
					$filter .= " and :PersonCountAtDate >= cast(PR.PersonRegister_setDate as date) and :PersonCountAtDate < isnull(cast(PR.PersonRegister_disDate as date), cast('2999-01-01' as date))";
				}

				if (!empty($data['DialysisCenter_id'])) {
					$queryParams['DialysisCenter_id'] = $data['DialysisCenter_id'];
					if($data['DialysisCenter_id'] == -1)
						$filter .= ' and MO.Lpu_id is null';
					else
						$filter .= ' and MO.Lpu_id = :DialysisCenter_id';
				}

				// Фильтр "Стадия ХБП"
				if (!empty($data['NephroCRIType_id'])) {
					$filter .= " and MO.NephroCRIType_id = :NephroCRIType_id";
					$queryParams['NephroCRIType_id'] = $data['NephroCRIType_id'];
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				if (isset($data['MorbusNephro_DialDate_Range'][0]) && isset($data['MorbusNephro_DialDate_Range'][1])) {
					$queryParams['MorbusNephro_DialDate_Range_0'] = $data['MorbusNephro_DialDate_Range'][0];
					$queryParams['MorbusNephro_DialDate_Range_1'] = $data['MorbusNephro_DialDate_Range'][1];
					$filter .= ' and cast(MO.MorbusNephro_dialDate as date) between :MorbusNephro_DialDate_Range_0 and :MorbusNephro_DialDate_Range_1';
				}

				if (isset($data['MorbusNephro_DialEndDate_Range'][0]) && isset($data['MorbusNephro_DialEndDate_Range'][1])) {
					$queryParams['MorbusNephro_DialEndDate_Range_0'] = $data['MorbusNephro_DialEndDate_Range'][0];
					$queryParams['MorbusNephro_DialEndDate_Range_1'] = $data['MorbusNephro_DialEndDate_Range'][1];
					$filter .= ' and cast(MO.MorbusNephro_DialEndDate as date) between :MorbusNephro_DialEndDate_Range_0 and :MorbusNephro_DialEndDate_Range_1';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				// сахарный диабет
				if (isset($data['Diab_Diag_Code_From'])) {
					$filter .= " and (DiabEvnDiagSpec.Diag_Code >= :Diab_Diag_Code_From";
					$filter .= " or DiabEvnSection.Diag_Code >= :Diab_Diag_Code_From";
					$filter .= " or DiabEvnVizitPL.Diag_Code >= :Diab_Diag_Code_From";
					$filter .= " or DiabEvnDiagPLSop.Diag_Code >= :Diab_Diag_Code_From";
					$filter .= " or DiabEvnDiagPS.Diag_Code >= :Diab_Diag_Code_From";
					$filter .= " or DiabEvnUslugaDispDop.Diag_Code >= :Diab_Diag_Code_From";
					$filter .= " or DiabEvnDiagDopDisp.Diag_Code >= :Diab_Diag_Code_From)";
					$queryParams['Diab_Diag_Code_From'] = $data['Diab_Diag_Code_From'];
				}

				if (isset($data['Diab_Diag_Code_To'])) {
					$filter .= " and (DiabEvnDiagSpec.Diag_Code <= :Diab_Diag_Code_To";
					$filter .= " or DiabEvnSection.Diag_Code <= :Diab_Diag_Code_To";
					$filter .= " or DiabEvnVizitPL.Diag_Code <= :Diab_Diag_Code_To";
					$filter .= " or DiabEvnDiagPLSop.Diag_Code <= :Diab_Diag_Code_To";
					$filter .= " or DiabEvnDiagPS.Diag_Code <= :Diab_Diag_Code_To";
					$filter .= " or DiabEvnUslugaDispDop.Diag_Code <= :Diab_Diag_Code_To";
					$filter .= " or DiabEvnDiagDopDisp.Diag_Code <= :Diab_Diag_Code_To)";
					$queryParams['Diab_Diag_Code_To'] = $data['Diab_Diag_Code_To'];
				}

				//последнее посещения нефролога
				if (isset($data['PersonVisit_Date_Range'][0]) && isset($data['PersonVisit_Date_Range'][1])) {
					$queryParams['PersonVisit_Date_Range_0'] = $data['PersonVisit_Date_Range'][0];
					$queryParams['PersonVisit_Date_Range_1'] = $data['PersonVisit_Date_Range'][1];
					$filter .= ' and cast(lastVizitDate.EvnVizitPL_setDate as date) between :PersonVisit_Date_Range_0 and :PersonVisit_Date_Range_1 ';
				}

				if (!empty($data['MonthsWithoutNefroVisit'])) {
					switch ($data['MonthsWithoutNefroVisit']) {
						case 1:
							$filter .= ' and lastVizitDate.EvnVizitPL_setDate is null';
							break;
						case 2:
							$filter .= ' and DATEDIFF(month, lastVizitDate.EvnVizitPL_setDate, dbo.tzGetDate()) > 1';
							break;
						case 3:
							$filter .= ' and DATEDIFF(month, lastVizitDate.EvnVizitPL_setDate, dbo.tzGetDate()) > 3';
							break;
						case 4:
							$filter .= ' and DATEDIFF(month, lastVizitDate.EvnVizitPL_setDate, dbo.tzGetDate()) > 12';
							break;
					}
				}



				break;
			case 'EndoRegistry':
				$queryParams['MorbusType_SysNick'] = 'nephro';
				$query .= "
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id
					inner join v_PersonRegisterEndo PRE with (nolock) on PRE.PersonRegister_id = PR.PersonRegister_id
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PR.Lpu_iid
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					left join v_CategoryLifeDegreeType CLDT with (nolock) on CLDT.CategoryLifeDegreeType_id = PRE.CategoryLifeDegreeType_id
					left join v_ProsthesType PT with (nolock) on PT.ProsthesType_id = PRE.ProsthesType_id
					left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = PR.MedPersonal_iid
				";

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegisterEndo_hospDate_Range'][0]) && isset($data['PersonRegisterEndo_hospDate_Range'][1])) {
					$queryParams['PersonRegisterEndo_hospDate_Range_0'] = $data['PersonRegisterEndo_hospDate_Range'][0];
					$queryParams['PersonRegisterEndo_hospDate_Range_1'] = $data['PersonRegisterEndo_hospDate_Range'][1];
					$filter .= ' and cast(PRE.PersonRegisterEndo_hospDate as date) between :PersonRegisterEndo_hospDate_Range_0 and :PersonRegisterEndo_hospDate_Range_1 ';
				}

				if (!empty($data['PersonRegister_Code'])) {
					$filter .= " and PR.PersonRegister_Code = :PersonRegister_Code";
					$queryParams['PersonRegister_Code'] = $data['PersonRegister_Code'];
				}

				if (!empty($data['Lpu_iid'])) {
					$filter .= " and PR.Lpu_iid = :Lpu_iid";
					$queryParams['Lpu_iid'] = $data['Lpu_iid'];
				}

				if (!empty($data['MedPersonal_iid'])) {
					$filter .= " and PR.MedPersonal_iid = :MedPersonal_iid";
					$queryParams['MedPersonal_iid'] = $data['MedPersonal_iid'];
				}

				if (!empty($data['ProsthesType_id'])) {
					$filter .= " and PRE.ProsthesType_id = :ProsthesType_id";
					$queryParams['ProsthesType_id'] = $data['ProsthesType_id'];
				}

				break;
			case 'IBSRegistry':
				$queryParams['MorbusType_SysNick'] = 'ibs';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyBase EN with (nolock) on EN.EvnNotifyBase_id = PR.EvnNotifyBase_id
					left join v_MorbusIBS MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_nid,PR.Diag_id)
					left join v_IBSType IBSType with (nolock) on IBSType.IBSType_id = MO.IBSType_id
				';

				// регистр
				if (!empty($data['IBSType_id'])) {
					$queryParams['IBSType_id'] = $data['IBSType_id'];
					$filter .= ' and MO.IBSType_id = :IBSType_id ';
				}
				if (!empty($data['MorbusIBS_IsKGIndication'])) {
					$queryParams['MorbusIBS_IsKGIndication'] = $data['MorbusIBS_IsKGIndication'];
					$filter .= ' and MO.MorbusIBS_IsKGIndication = :MorbusIBS_IsKGIndication ';
				}
				if (!empty($data['MorbusIBS_IsKGFinished'])) {
					$queryParams['MorbusIBS_IsKGFinished'] = $data['MorbusIBS_IsKGFinished'];
					//$filter .= ' and isnull(MO.MorbusIBS_IsKGFinished,1) = :MorbusIBS_IsKGFinished ';
					$filter .= ' and MO.MorbusIBS_IsKGFinished = :MorbusIBS_IsKGFinished ';
				}
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'ProfRegistry':
				$queryParams['MorbusType_SysNick'] = 'prof';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyProf EN with (nolock) on EN.EvnNotifyProf_id = PR.EvnNotifyBase_id
					left join v_MorbusProf MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_MorbusProfDiag mpd (nolock) on mpd.MorbusProfDiag_id = MO.MorbusProfDiag_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					outer apply (
						select top 1 v_evndiag.Diag_id
						from v_evndiag (nolock)
						where v_evndiag.Morbus_id = PR.Morbus_id
						and v_evndiag.evndiag_pid is null
						order by v_evndiag.evndiag_setdate desc
					) EvnDiagProf
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(EvnDiagProf.Diag_id,PR.Diag_id)
					left join v_Job job with (nolock) ON ps.Job_id = job.Job_id
					left join v_Org o (nolock) on o.Org_id = job.Org_id
					left join v_PersonCardState pcs with (nolock) on pcs.Person_id = ps.Person_id and pcs.LpuAttachType_id = 1
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($data['MorbusProfDiag_id'])) {
					$filter .= " and mo.MorbusProfDiag_id = :MorbusProfDiag_id";
					$queryParams['MorbusProfDiag_id'] = $data['MorbusProfDiag_id'];
				}

				if (isset($data['OrgWork_id'])) {
					$filter .= " and job.Org_id = :OrgWork_id";
					$queryParams['OrgWork_id'] = $data['OrgWork_id'];
				}

				if ($data['Person_IsDead']) {
					$filter .= " and PS.Person_IsDead = 2";
				}

				if ($data['Person_DeRegister']) {
					$filter .= " and pcs.CardCloseCause_id = 5";
				}

				break;
			case 'TubRegistry':
				$queryParams['MorbusType_SysNick'] = 'tub';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyTub EN with (nolock) on EN.EvnNotifyTub_id = PR.EvnNotifyBase_id
					left join v_MorbusTub MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					outer apply (
						select top 1 Lpu_id
						from v_PersonCard with (nolock)
						where Person_id = PS.Person_id and LpuAttachType_id = 1
					) as PC
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				// Химиотерапия по IV режиму лечения (МЛУ)
				if (isset($data['isNeglected'])) {
					if ($data['isNeglected'] == 1) {
						$filter .= " and not exists (
							select mdr.MorbusTubMDR_id
							from v_MorbusTubMDR mdr with (nolock)
							where mdr.Morbus_id = PR.Morbus_id
						)";
					} else if ($data['isNeglected'] == 2) {
						$filter .= " and exists (
							select mdr.MorbusTubMDR_id
							from v_MorbusTubMDR mdr with (nolock)
							where mdr.Morbus_id = PR.Morbus_id
						)";
					}
				}
				if (!empty($data['isGeneralForm'])) {
					if ($data['isGeneralForm'] == 1) {
						$filter .= " and not exists (
							select tdgf.TubDiagGeneralForm_id
							from v_TubDiagGeneralForm tdgf with (nolock)
							where tdgf.MorbusTub_id = MO.MorbusTub_id
						)";
					} else if ($data['isGeneralForm'] == 2) {
						$filter .= " and exists (
							select tdgf.TubDiagGeneralForm_id
							from v_TubDiagGeneralForm tdgf with (nolock)
							where tdgf.MorbusTub_id = MO.MorbusTub_id
						)";
					}
				}
				break;
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
				$registers = array(
					'DiabetesRegistry' => 'diabetes',
					'LargeFamilyRegistry' => 'large family'
				);
				$queryParams['MorbusType_SysNick'] = $registers[$data['SearchFormType']];
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					outer apply (
						select count(*) Request
						from DrugRequestRow (nolock) 
						where Person_id = PC.Person_id
					) as Drug
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'FmbaRegistry':
				$query .= "
					inner join v_PersonRegisterType PRT with (nolock) on PRT.PersonRegisterType_SysNick = 'fmba'
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					outer apply (
						select count(*) Request
						from DrugRequestRow (nolock) 
						where Person_id = PC.Person_id
					) as Drug
				";

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'HIVRegistry':
				$queryParams['MorbusType_SysNick'] = 'hiv';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyHIV EN with (nolock) on EN.EvnNotifyHIV_id = PR.EvnNotifyBase_id
					inner join v_Morbus M with (nolock) on M.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					inner join v_MorbusHIV MH with (nolock) on MH.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				// № иммуноблота
				if (isset($data['MorbusHIV_NumImmun'])) {
					$filter .= " and MH.MorbusHIV_NumImmun = :MorbusHIV_NumImmun";
					$queryParams['MorbusHIV_NumImmun'] = $data['MorbusHIV_NumImmun'];
				}

				break;
			case 'VenerRegistry':
				$queryParams['MorbusType_SysNick'] = 'vener';
				$query .= '
					inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyVener EN with (nolock) on EN.EvnNotifyVener_id = PR.EvnNotifyBase_id
					left join v_MorbusVener MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';

				// регистр
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ($data['PersonRegisterType_id'] == 3) {
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if (isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1])) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;

			case 'PersonDopDispPlan':
				$query .= " left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id ";
				$query .= " outer apply (
					select top 1 
					epld.EvnPLDisp_id,
					epld.EvnPLDisp_setDate,
					epld.EvnPLDisp_disDate
					from v_EvnPLDisp epld with (nolock) 
					where epld.Person_id = PS.Person_id and YEAR(epld.EvnPLDisp_setDate) = :PersonDopDisp_Year and epld.DispClass_id = :DispClass_id
				) as IsPersonDopDispPassed ";

				// Дисп-ция детей-сирот стационарных 1-ый этап [3].
				if ($data['DispClass_id'] == 3) {
					$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.CategoryChildType_id IN (1,2,3,4) and DO.Lpu_id " . $this->getLpuIdFilter($data);
					$filter .= " and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') <= 18 ";
				}

				// Дисп-ция детей-сирот усыновленных 1-ый этап [7].
				if ($data['DispClass_id'] == 7) {
					$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.CategoryChildType_id IN (5,6,7) and DO.Lpu_id " . $this->getLpuIdFilter($data);
					$filter .= " and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') <= 18 ";
				}

				// Дисп-ция взр. населения 1-ый этап [1].
				if ($data['DispClass_id'] == 1) {
					$query .= " left join v_PersonPrivilege PP with (nolock) on PP.Person_id = PS.Person_id";
					// Подлежащие ежегодному прохождению ДВН (инвалиды ВОВ и блокадники)
					if ($data['Person_isYearlyDispDop']) {
						$filter .= " and PP.PersonPrivilege_id is not null and PP.PrivilegeType_id in (10,11,50) ";
					} else {
						// Исключаем из запроса для "Не проходившие в установленные сроки".
						if (!$data['Person_isNotDispDopOnTime']) {
							$add_filter = "";
							
							$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');
							$dateX = $this->EvnPLDispDop13_model->getNewDVNDate();
							if (!empty($dateX) && $dateX <= date('Y-m-d')) {
								$add_filter .= "
									or
									(dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') >= 40)
								";
							} else {
								if (!in_array($data['session']['region']['nick'], array('kz')) && $data['PersonDopDisp_Year'] >= 2018) {
									//#180113 исключение по ТЗ
									$add_filter .= " or dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') >= 40 ";
									$add_filter .= "
										or
										(PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') between 49 and 73 and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') % 2 = 1)
										or
										(PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') between 48 and 73)
									";
								}
							}
							$filter .= " and PS.Person_BirthDay <= :PersonAge_18 and (
								(PS.Person_BirthDay <= :PersonAge_21 and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') % 3 = 0) or
								(PP.PersonPrivilege_id is not null and PP.PrivilegeType_id in (10,11,50))
								{$add_filter}
							)";
						}
					}
					
					// Не проходившие в установленные сроки.
					// Примечание: Подлежащие ежегодному прохождению ДВН перекрывает запрос.
					if ($data['Person_isNotDispDopOnTime'] && !$data['Person_isYearlyDispDop']) {
						// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
						if (!in_array($this->getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])) {
							// 1. подлежат прохождению ДВН.
							// 2. не проходили ДВН согласно возраста диспансеризации (кратно трем до 40 лет).
							// 3. нет карты диспансеризации за два предыдущих года.
							$filter .= "
								AND (
									(
										-- ДВН проводится раз в 3 года.
										-- от 18 до 39 (младше 40 лет).
										(dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') >= 18)
										AND (dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') <= 39)
										-- Возраст кратен 3-м.
										AND (dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') % 3 = 0)
										-- нет карты диспансеризации в указанному году и за два предыдущих года.
										AND (not exists (
											SELECT top 1 EvnPLDispProf_id 
											FROM v_EvnPLDispProf (nolock) 
											WHERE 
												(YEAR(EvnPLDispProf_disDT) BETWEEN {$data['PersonDopDisp_Year']} - 2 AND {$data['PersonDopDisp_Year']}) 
												AND Person_id = PS.Person_id
											))
									)
									-- Подлежащие ежегодному прохождению ДВН (инвалиды ВОВ и блокадники)
									OR PP.PersonPrivilege_id is not null and PP.PrivilegeType_id in (10,11,50)
								)
							";
						}
					}
				}

				// Проф.осмотры взр. населения [5].
				if ($data['DispClass_id'] == 5) {
					$query .= " outer apply (
						select count(*) [count]
						from v_EvnPLDisp epld with (nolock) 
						where epld.Person_id = PS.Person_id and YEAR(epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev and epld.DispClass_id = 5
					) as EplDispProfLastYear ";
					$queryParams['PersonDopDisp_YearPrev'] = $data['PersonDopDisp_Year'] - 1;
					$filter .= "
						and PS.Person_BirthDay <= :PersonAge_18
						and dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') % 3 != 0
						and EplDispProfLastYear.count = 0
					";
				}

				// только не включенные в план
				$query .= " outer apply (
					select top 1 ppl.PlanPersonList_id
					from v_PlanPersonList ppl with (nolock) 
					inner join v_PersonDopDispPlan pddp with (nolock) on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
					left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
					where ppl.Person_id = PS.Person_id and pddp.PersonDopDispPlan_Year = :PersonDopDisp_Year and pddp.DispClass_id = :DispClass_id and ISNULL(PDDPS.PlanPersonListStatusType_id, 1) <> 4
				) as IsPersonDopDispPlanned ";
				$filter .= " and IsPersonDopDispPlanned.PlanPersonList_id is null ";

				// и не прошедшие в этом году ранее
				if (!$data['Person_isDopDispPassed']) {
					$filter .= " and IsPersonDopDispPassed.EvnPLDisp_id is null ";
				} else {
					if (isset($data['EvnPLDisp_setDate_Range'][0])) {
						$filter .= " and IsPersonDopDispPassed.EvnPLDisp_setDate >= :EvnPLDisp_setDate_Range_0";
						$queryParams['EvnPLDisp_setDate_Range_0'] = $data['EvnPLDisp_setDate_Range'][0];
					}
					if (isset($data['EvnPLDisp_setDate_Range'][1])) {
						$filter .= " and IsPersonDopDispPassed.EvnPLDisp_setDate <= :EvnPLDisp_setDate_Range_1";
						$queryParams['EvnPLDisp_setDate_Range_1'] = $data['EvnPLDisp_setDate_Range'][1];
					}
					if (isset($data['EvnPLDisp_disDate_Range'][0])) {
						$filter .= " and IsPersonDopDispPassed.EvnPLDisp_disDate >= :EvnPLDisp_disDate_Range_0";
						$queryParams['EvnPLDisp_disDate_Range_0'] = $data['EvnPLDisp_disDate_Range'][0];
					}
					if (isset($data['EvnPLDisp_disDate_Range'][1])) {
						$filter .= " and IsPersonDopDispPassed.EvnPLDisp_disDate <= :EvnPLDisp_disDate_Range_1";
						$queryParams['EvnPLDisp_disDate_Range_1'] = $data['EvnPLDisp_disDate_Range'][1];
					}
					/* if ($this->getRegionNick() == 'perm') {
					  if (in_array($data['PeriodCap_id'], array(3,4)) && date('md', strtotime($data['DispCheckPeriod_begDate'])) <= '0301') {
					  $filter .= " and (IsPersonDopDispPassed.EvnPLDisp_disDate between '{$data['PersonDopDisp_Year']}-01-01' and '{$data['PersonDopDisp_Year']}-03-31' or IsPersonDopDispPassed.EvnPLDisp_disDate is null) ";
					  }
					  else {
					  $filter .= " and IsPersonDopDispPassed.EvnPLDisp_disDate is null";
					  }
					  } */
				}

				$queryParams['PersonAge_18'] = date('Y-m-d', strtotime("-18 years", strtotime(date('Y-12-31'))));
				$queryParams['PersonAge_21'] = date('Y-m-d', strtotime("-21 years", strtotime(date('Y-12-31'))));
				$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				$queryParams['DispClass_id'] = $data['DispClass_id'];

				if ($data['Person_isOftenApplying'] || $data['Person_isNotApplyingLastYear']) {
					$query .= " outer apply (
						select count(*) [count]
						from v_EvnPL epl with (nolock) 
						where epl.Person_id = PS.Person_id and YEAR(epl.EvnPL_setDate) = :PersonDopDisp_YearPrev
					) as EplLastYear ";
					$queryParams['PersonDopDisp_YearPrev'] = $data['PersonDopDisp_Year'] - 1;
					if ($data['Person_isOftenApplying']) {
						$filter .= " and EplLastYear.count >= 4 ";
					}
					if ($data['Person_isNotApplyingLastYear']) {
						$filter .= " and EplLastYear.count = 0 ";
					}
				}

				// Не проходили ПОВН в прошлом году
				if ($data['Person_isNotDispProf']) {
					$query .= " outer apply (
						select count(*) [count]
						from v_EvnPLDisp epld with (nolock) 
						where epld.Person_id = PS.Person_id and YEAR(epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev and epld.DispClass_id = 5
					) as EplDispProfLastYear ";
					$queryParams['PersonDopDisp_YearPrev'] = $data['PersonDopDisp_Year'] - 1;
					$filter .= " and EplDispProfLastYear.count = 0 ";
				}

				// Не проходили ДВН в прошлом году
				if ($data['Person_isNotDispDop']) {
					$query .= " outer apply (
						select count(*) [count]
						from v_EvnPLDisp epld with (nolock) 
						where epld.Person_id = PS.Person_id and YEAR(epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev and epld.DispClass_id = 1
					) as EplDispDopLastYear ";
					$queryParams['PersonDopDisp_YearPrev'] = $data['PersonDopDisp_Year'] - 1;
					$filter .= " and EplDispDopLastYear.count = 0 ";
				}

				// исключим умерших
				$filter .= " and PS.Person_DeadDT is null";

				break;
			case 'RzhdRegistry':
				$filter .= ' and RR.RzhdRegistry_delDT is null';

				if (!empty($data['RzhdRegistry_id'])) {
					$queryParams['RzhdRegistry_id'] = $data['RzhdRegistry_id'];
					$filter .= ' and RR.RzhdRegistry_id = :RzhdRegistry_id';
				}
				if (!empty($data['RzhdWorkerCategory_id'])) {
					$queryParams['RzhdWorkerCategory_id'] = $data['RzhdWorkerCategory_id'];
					$filter .= ' and RR.RzhdWorkerCategory_id = :RzhdWorkerCategory_id';
				}
				if (!empty($data['RzhdWorkerGroup_id'])) {
					$queryParams['RzhdWorkerGroup_id'] = $data['RzhdWorkerGroup_id'];
					$filter .= ' and RR.RzhdWorkerGroup_id = :RzhdWorkerGroup_id';
				}
				if (!empty($data['RzhdWorkerSubgroup_id'])) {
					$queryParams['RzhdWorkerSubgroup_id'] = $data['RzhdWorkerSubgroup_id'];
					$filter .= ' and RR.RzhdWorkerSubgroup_id = :RzhdWorkerSubgroup_id';
				}
				if (!empty($data['RzhdRegistry_PensionBegDate_Range'][0]) && !empty($data['RzhdRegistry_PensionBegDate_Range'][1])) {
					$queryParams['RzhdRegistry_PensionBegDate_Range_0'] = $data['RzhdRegistry_PensionBegDate_Range'][0];
					$queryParams['RzhdRegistry_PensionBegDate_Range_1'] = $data['RzhdRegistry_PensionBegDate_Range'][1];
					$filter .= ' and RR.RzhdRegistry_PensionBegDate between cast(:RzhdRegistry_PensionBegDate_Range_0 as date) and cast(:RzhdRegistry_PensionBegDate_Range_1 as date)';
				}
				if (!empty($data['Register_setDate_Range'][0]) && !empty($data['Register_setDate_Range'][1])) {
					$queryParams['Register_setDate_Range_0'] = $data['Register_setDate_Range'][0];
					$queryParams['Register_setDate_Range_1'] = $data['Register_setDate_Range'][1];
					$filter .= ' and R.Register_setDate between cast(:Register_setDate_Range_0 as date) and cast(:Register_setDate_Range_1 as date)';
				}
				if (!empty($data['Register_disDate_Range'][0]) && !empty($data['Register_disDate_Range'][1])) {
					$queryParams['Register_disDate_Range_0'] = $data['Register_disDate_Range'][0];
					$queryParams['Register_disDate_Range_1'] = $data['Register_disDate_Range'][1];
					$filter .= ' and R.Register_disDate between cast(:Register_disDate_Range_0 as date) and cast(:Register_disDate_Range_1 as date)';
				}
				if (!empty($data['RzhdOrg_id'])) {
					$queryParams['RzhdOrg_id'] = $data['RzhdOrg_id'];
					$filter .= ' and RR.Org_id =:RzhdOrg_id';
				}
				if (!empty($data['RegisterDisCause_id'])) {
					$queryParams['RegisterDisCause_id'] = $data['RegisterDisCause_id'];
					$filter .= ' and R.RegisterDisCause_id =:RegisterDisCause_id';
				}

				$query .= "
					r2.v_Register R with(nolock)
					inner join r2.RegisterType RT with(nolock) on RT.RegisterType_id = R.RegisterType_id and RT.RegisterType_Code = 'RZHD'
					left join r2.v_RzhdRegistry RR WITH (NOLOCK) on RR.Register_id = R.Register_id
					left join r2.RzhdWorkerSubgroup RWS WITH (nolock) on RWS.RzhdWorkerSubgroup_id = RR.RzhdWorkerSubgroup_id
					left join r2.RzhdWorkerGroup RWG WITH (nolock) on RWG.RzhdWorkerGroup_id = RWS.RzhdWorkerGroup_id
					left join r2.RzhdWorkerCategory RWC WITH (nolock) on RWC.RzhdWorkerCategory_id = RWG.RzhdWorkerCategory_id
					left join r2.RegisterDisCause RDC with(nolock) on RDC.RegisterDisCause_id = R.RegisterDisCause_id
					left join dbo.v_PersonState PS WITH (NOLOCK) on PS.Person_id = R.Person_id
					left join dbo.v_PersonCardState PCS WITH (NOLOCK) on PCS.Person_id = R.Person_id and PCS.LpuAttachType_id = 1
					left join dbo.v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = PCS.Lpu_id
					left join dbo.v_Org Org WITH (NOLOCK) on Org.Org_id = Lpu.Org_id
				";
				break;
			case 'ONMKRegistry':

				$query .="
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id in (SELECT MorbusType_id
																											FROM dbo.MorbusType
																										   WHERE MorbusType_SysNick = 'onmk')
					inner join v_ONMKRegistry ONMKR with (nolock) on ONMKR.PersonRegister_id=PR.PersonRegister_id
					inner join v_Diag Dg with (nolock) on Dg.Diag_id=ONMKR.Diag_id
					inner join v_lpu Lp with (nolock) on Lp.Lpu_id=ONMKR.Lpu_id
					
					left join dbo.ConsciousType CT with (nolock) on CT.ConsciousType_id=ONMKR.ConsciousType_id
					left join v_RankinScale RS with (nolock) on RS.RankinScale_id=ONMKR.RankinScale_id
					left join v_LeaveType LT with (nolock) on LT.LeaveType_id=ONMKR.LeaveType_id
				";
				//left join v_ResultDesease RD with (nolock) on RD.ResultDesease_id=ONMKR.ResultDesease_id

				$filter .= " and ONMKR.ONMKRegistry_deleted = 1 ";

				// Тип записи регистра: Вкл Искл
				if (!empty($data['PersonRegisterType_id'])) {
					if ($data['PersonRegisterType_id'] == 2) {
						$filter .= " and PR.PersonRegister_disDate is null ";
					} elseif ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and PR.PersonRegister_disDate is not null ";
					}
				}

				// Статус записи ОНМК
				if (!empty($data['ONMKRegistry_Status'])) {
					if ($data['ONMKRegistry_Status'] == 1) {//Все активные случаи
						$filter .= " and ONMKR.ONMKRegistry_IsMonitor = 1 ";
					} elseif ($data['ONMKRegistry_Status'] == 3) { //Непросмотренные
						$filter .= " and ONMKR.ONMKRegistry_IsNew = 1 ";
					} elseif ($data['ONMKRegistry_Status'] == 4) { //Просмотренные
						$filter .= " and ONMKR.ONMKRegistry_IsNew = 2 ";
					} elseif ($data['ONMKRegistry_Status'] == 5) {
						$filter .= " and ONMKR.ONMKRegistry_IsConfirmed = 2 ";//Диагноз не подтвержден
					}
				}else{
					$filter .= " and ONMKR.ONMKRegistry_IsMonitor = 1 ";
				}

				//МО госпитализации
				if(isset($data['LPU_sid']) && $data['LPU_sid'] != ''){
					$filter  .= ' and ONMKR.Lpu_id = :LPU_sid';
					$queryParams['LPU_sid'] = $data['LPU_sid'];
				}

				//РСЦ/ПСО/МО госп-ии
				if(isset($data['LPU_id']) && $data['LPU_id'] != ''){
					if ($data['LPU_id'] == '20000'){
						$filter  .= ' and not ONMKR.Lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo)';
					}else{
						$filter  .= ' and ONMKR.Lpu_id in (select rcc.lpu_id from dbo.RoutingONMK rcc
									where rcc.lpu_id = :LPU_id
									union
									select pco.lpu_id from dbo.RoutingONMK rcc
									left join dbo.RoutingONMK pco on pco.lpu_pid=rcc.lpu_id
									where rcc.lpu_id = :LPU_id and pco.lpu_id is not null
									union
									select mo.lpu_id from dbo.RoutingONMK rcc
									left join dbo.RoutingONMK pco on pco.lpu_pid=rcc.lpu_id
									left join dbo.RoutingONMK mo on mo.lpu_pid=pco.lpu_id
									where rcc.lpu_id = :LPU_id and mo.lpu_id is not null)';
						$queryParams['LPU_id'] = $data['LPU_id'];
					}
				}

				//Тип МО госпитализации
				//data: [[1, 'Все'], [2, 'ПСО и РСЦ'], [3, 'ПСО'], [4, 'РСЦ'], [5, 'Прочие МО']]
				if(isset($data['ONMKRegistry_TypeMO']) && $data['ONMKRegistry_TypeMO'] != ''){
					if ($data['ONMKRegistry_TypeMO'] == 2){
						$filter  .= ' and Lp.lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo where mo.lpusectiondoptype_id in (1,2))';
					}else if ($data['ONMKRegistry_TypeMO'] == 3){
						$filter  .= ' and Lp.lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo where mo.lpusectiondoptype_id in (1))';
					}else if ($data['ONMKRegistry_TypeMO'] == 4){
						$filter  .= ' and Lp.lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo where mo.lpusectiondoptype_id in (2))';
					}else if ($data['ONMKRegistry_TypeMO'] == 5){
						$filter  .= ' and [dbo].[GetONMKMO](Lp.lpu_id) = 0';
					}
				}

				//Дата госпитализации
				if (isset($data['ONMKRegistry_Evn_DTDesease'][0]) && isset($data['ONMKRegistry_Evn_DTDesease'][1])) {
					$queryParams['ONMKRegistry_EvnDTDesease_0'] = $data['ONMKRegistry_Evn_DTDesease'][0];
					$queryParams['ONMKRegistry_EvnDTDesease_1'] = $data['ONMKRegistry_Evn_DTDesease'][1];
					$filter .= ' and cast(ONMKR.ONMKRegistry_EvnDT as date) between :ONMKRegistry_EvnDTDesease_0 and :ONMKRegistry_EvnDTDesease_1 ';
				}

				//Диагноз с
				if ( !empty($data['Diag_Code_From']) ) {
					$queryWithAdditionalWhere[] = "Dg.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				//Диагноз по
				if ( !empty($data['Diag_Code_To']) ) {
					$queryWithAdditionalWhere[] = "Dg.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (isset($queryWithAdditionalWhere)){
					$filter .= " and ". implode(' and ', $queryWithAdditionalWhere);
				}

				//ТЛТ
				//ONMKRegistry_ISTLT
				if (isset($data['ONMKRegistry_ISTLT'])) {
					if ($data['ONMKRegistry_ISTLT'] == 1){
						$filter .= ' and ONMKRegistry_TLTDT is not null ';
					}else if ($data['ONMKRegistry_ISTLT'] == 2){
						$filter .= ' and ONMKRegistry_TLTDT is null ';
					}
				}



				//Исход заболевания
				//ONMKRegistry_ResultDesease
				if (!empty($data['ONMKRegistry_ResultDesease'])) {
					if ($data['ONMKRegistry_ResultDesease'] == 1) {//Выписка
							$filter .= " and ONMKR.LeaveType_id = (select LT.LeaveType_id from [dbo].[LeaveType] LT where LT.region_id=".getRegionNumber()." and LT.LeaveType_Code=1) ";
					} elseif ($data['ONMKRegistry_ResultDesease'] == 2) { //Смерть
							$filter .= " and ONMKR.LeaveType_id = (select LT.LeaveType_id from [dbo].[LeaveType] LT where LT.region_id=".getRegionNumber()." and LT.LeaveType_Code=3) ";
						}
				}

				//Дата включения в регистр
				if (isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1])) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(ONMKR.ONMKRegistry_insDT as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}


				//$filter .= " and PR.PersonregisterOutCause_id is null  ";

				//маршрутизация МО, права доступа
				if (strpos($data['session']['groups'], 'ONMKRegistryCenter') === false && !empty($data['session']['lpu_id'])){
					$filter .= " and 
					(
					exists(select 1 from v_PersonCard_all VPC where VPC.Person_id=PS.Person_id and VPC.lpuattachtype_id=1 and VPC.PersonCard_endDate is null 
					and ','+dbo.GetRoutingONMKMO(" . $data['session']['lpu_id'] . ")+',' like '%,'+CONVERT(varchar, VPC.Lpu_id) +',%'
					)
					or 
					','+dbo.GetRoutingONMKMO(" . $data['session']['lpu_id'] . ")+',' like '%,'+CONVERT(varchar, ONMKR.lpu_id) +',%'
					) ";
				}
                break;
			case 'SportRegistry':
				$query .= '
					left join dbo.SportRegister SR with (nolock) on PS.Person_id = SR.Person_id
					left join dbo.PersonRegisterOutCause OC with (nolock) on OC.PersonRegisterOutCause_id = SR.PersonRegisterOutCause_id
					inner join dbo.SportRegisterUMO SRUMO with (nolock) on SR.SportRegister_id = SRUMO.SportRegister_id
					left join dbo.InvalidGroupType IGT with (nolock) on IGT.InvalidGroupType_id = SRUMO.InvalidGroupType_id
					left join dbo.SportParaGroup SPG with (nolock) on SPG.SportParaGroup_id = SRUMO.SportParaGroup_id
					-- Врач
					outer apply (select top 1 * from dbo.MedPersonalCache with (nolock) where MedPersonal_id = SRUMO.MedPersonal_pid) as MPp
					left join dbo.PersonState MSFPSp with (nolock) on MSFPSp.Person_id = MPp.Person_id
					-- }
					left join dbo.SportType ST with (nolock) on ST.SportType_id = SRUMO.SportType_id
					left join dbo.SportOrg SO with (nolock) on SO.SportOrg_id = SRUMO.SportOrg_id
					left join dbo.SportCategory SC with (nolock) on SC.SportCategory_id = SRUMO.SportCategory_id
					left join dbo.SportStage SS with (nolock) on SS.SportStage_id = SRUMO.SportStage_id
					-- Тренер {
					left join dbo.SportTrainer STr with (nolock) on STr.SportTrainer_id = SRUMO.SportTrainer_id
					left join dbo.PersonState PSTr with (nolock) on PSTr.Person_id = STr.Person_id
					-- }
					left join dbo.UMOResult UR with (nolock) on UR.UMOResult_id = SRUMO.UMOResult_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				';

				if (!empty($data['SportRegisterType_id'])) {
					if ($data['SportRegisterType_id'] == 3)
						$filter .= ' and SR.SportRegister_delDT is not null';
					else if ($data['SportRegisterType_id'] == 2)
						$filter .= ' and SR.SportRegister_delDT is null ';
		}

				if (!empty($data['SportType_id'])) {
					$queryParams['SportType_id'] = $data['SportType_id'];
					$filter .= ' and SRUMO.SportType_id = :SportType_id ';
				}

				if (!empty($data['SportStage_id'])) {
					$queryParams['SportStage_id'] = $data['SportStage_id'];
					$filter .= ' and SRUMO.SportStage_id = :SportStage_id ';
				}

				if (!empty($data['SportCategory_id'])) {
					$queryParams['SportCategory_id'] = $data['SportCategory_id'];
					$filter .= ' and SRUMO.SportCategory_id = :SportCategory_id ';
				}

				if (!empty($data['SportOrg_id'])) {
					$queryParams['SportOrg_id'] = $data['SportOrg_id'];
					$filter .= ' and SRUMO.SportOrg_id = :SportOrg_id ';
				}

				if (!empty($data['UMOResult_id'])) {
					$queryParams['UMOResult_id'] = $data['UMOResult_id'];
					$filter .= ' and SRUMO.UMOResult_id = :UMOResult_id ';
				}

				if (!empty($data['MedPersonal_pid'])) {
					$queryParams['MedPersonal_pid'] = $data['MedPersonal_pid'];
					$filter .= ' and SRUMO.MedPersonal_pid = :MedPersonal_pid ';
				}

				if (!empty($data['SportTrainer_id'])) {
					$queryParams['SportTrainer_id'] = $data['SportTrainer_id'];
					$filter .= ' and SRUMO.SportTrainer_id = :SportTrainer_id ';
				}

				if (!empty($data['IsTeamMember_id'])) {
					if ($data['IsTeamMember_id'] == true){}
					$queryParams['IsTeamMember_id'] = $data['IsTeamMember_id'];
					$filter .= ' and SRUMO.SportRegisterUMO_IsTeamMember = :IsTeamMember_id';
				}

				if (!empty($data['InvalidGroupType_id'])) {
					$queryParams['InvalidGroupType_id'] = $data['InvalidGroupType_id'];
					$filter .= ' and SRUMO.InvalidGroupType_id = :InvalidGroupType_id ';
				}

				if (!empty($data['SportParaGroup_id'])) {
					$queryParams['SportParaGroup_id'] = $data['SportParaGroup_id'];
					$filter .= ' and SRUMO.SportParaGroup_id = :SportParaGroup_id ';
				}

				if (!empty($data['SportStage_id'])) {
					$queryParams['SportStage_id'] = $data['SportStage_id'];
					$filter .= ' and SRUMO.SportStage_id = :SportStage_id ';
				}

				if (!empty($data['SportRegisterUMO_UMODate'][0]) && !empty($data['SportRegisterUMO_UMODate'][1])) {
					$queryParams['SportRegisterUMO_UMODate_0'] = $data['SportRegisterUMO_UMODate'][0];
					$queryParams['SportRegisterUMO_UMODate_1'] = $data['SportRegisterUMO_UMODate'][1];
					$filter .= ' and cast(SRUMO.SportRegisterUMO_UMODate as date) between :SportRegisterUMO_UMODate_0 and :SportRegisterUMO_UMODate_1 ';
				}

				if (!empty($data['SportRegisterUMO_AdmissionDtBeg'][0]) && !empty($data['SportRegisterUMO_AdmissionDtBeg'][1])) {
					$queryParams['SportRegisterUMO_AdmissionDtBeg_0'] = $data['SportRegisterUMO_AdmissionDtBeg'][0];
					$queryParams['SportRegisterUMO_AdmissionDtBeg_1'] = $data['SportRegisterUMO_AdmissionDtBeg'][1];
					$filter .= ' and cast(SRUMO.SportRegisterUMO_AdmissionDtBeg as date) between :SportRegisterUMO_AdmissionDtBeg_0 and :SportRegisterUMO_AdmissionDtBeg_1 ';
				}

				if (!empty($data['SportRegisterUMO_AdmissionDtEnd'][0]) && !empty($data['SportRegisterUMO_AdmissionDtEnd'][1])) {
					$queryParams['SportRegisterUMO_AdmissionDtEnd_0'] = $data['SportRegisterUMO_AdmissionDtEnd'][0];
					$queryParams['SportRegisterUMO_AdmissionDtEnd_1'] = $data['SportRegisterUMO_AdmissionDtEnd'][1];
					$filter .= ' and cast(SRUMO.SportRegisterUMO_AdmissionDtEnd as date) between :SportRegisterUMO_AdmissionDtEnd_0 and :SportRegisterUMO_AdmissionDtEnd_1 ';
				}

				$filter .= ' and SRUMO.SportRegisterUMO_delDT is null ';
				break;
			case 'HTMRegister':
				$query .= "
					r2.v_Register R with (nolock)
					inner join r2.v_HTMRegister HR  with (nolock) on R.Register_id = HR.Register_id
					left join v_PersonState PS      with (nolock) on PS.Person_id = R.Person_id
					left join v_PersonCardState PCS with (nolock) on PCS.Person_id = R.Person_id
					left join v_Lpu Lpu             with (nolock) on Lpu.Lpu_id = PCS.Lpu_id
					left join v_EvnDirectionHTM EDH with (nolock) on EDH.EvnDirectionHTM_id = HR.EvnDirectionHTM_id
					left join v_Lpu LpuEDH          with (nolock) on LpuEDH.Lpu_id = EDH.Lpu_sid
					left join v_LpuSectionProfile LSP       with (nolock) on LSP.LpuSectionProfile_id = EDH.LpuSectionProfile_id
				";

				if(!empty($data['RegisterType_id'])) {
					$queryParams['RegisterType_id'] = $data['RegisterType_id'];

					switch ($data['RegisterType_id']) {
						case 1:
							break;
						case 2:
							$filter .= ' and isNull(PS.Person_isDead,1) = 1';
							break;
						case 3:
							$filter .= ' and isNull(PS.Person_isDead,1) = 2';
							break;
		}

				}

				if(!empty($data['Register_setDate_Range'][0]) && !empty($data['Register_setDate_Range'][1])) {
					$queryParams['Register_setDate_Range_0'] = $data['Register_setDate_Range'][0];
					$queryParams['Register_setDate_Range_1'] = $data['Register_setDate_Range'][1];
					$filter .= ' and R.Register_setDate between :Register_setDate_Range_0 and :Register_setDate_Range_1';
				}

				if(!empty($data['Register_disDate_Range'][0]) && !empty($data['Register_disDate_Range'][1])) {
					$queryParams['Register_disDate_Range_0'] = $data['Register_disDate_Range'][0];
					$queryParams['Register_disDate_Range_1'] = $data['Register_disDate_Range'][1];
					$filter .= ' and PS.Person_DeadDT between :Register_disDate_Range_0 and :Register_disDate_Range_1';
				}

				if(!empty($data['HTMLpu_id'])) {
					$queryParams['Lpu_id'] = $data['HTMLpu_id'];
					$filter .= ' and EDH.Lpu_id = :Lpu_id';
				}

				if(!empty($data['HTMRegister_ApplicationDate_Range'][0]) && !empty($data['HTMRegister_ApplicationDate_Range'][1])) {
					$queryParams['HTMRegister_ApplicationDate_Range_0'] = $data['HTMRegister_ApplicationDate_Range'][0];
					$queryParams['HTMRegister_ApplicationDate_Range_1'] = $data['HTMRegister_ApplicationDate_Range'][1];
					$filter .= ' and HR.HTMRegister_ApplicationDate between :HTMRegister_ApplicationDate_Range_0 and :HTMRegister_ApplicationDate_Range_1';
				}

				if(!empty($data['HTMRegister_DisDate_Range'][0]) && !empty($data['HTMRegister_DisDate_Range'][1])) {
					$queryParams['HTMRegister_DisDate_Range_0'] = $data['HTMRegister_DisDate_Range'][0];
					$queryParams['HTMRegister_DisDate_Range_1'] = $data['HTMRegister_DisDate_Range'][1];
					$filter .= ' and HR.HTMRegister_DisDate between :HTMRegister_DisDate_Range_0 and :HTMRegister_DisDate_Range_1';
				}

				if(!empty($data['HTMedicalCareClass_id'])) {
					$queryParams['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
					$filter .= ' and HR.HTMedicalCareClass_id = :HTMedicalCareClass_id';
				}

				if(!empty($data['HTMRegister_Stage'])) {
					$queryParams['HTMRegister_Stage'] = $data['HTMRegister_Stage'];
					$filter .= ' and HR.HTMRegister_Stage = :HTMRegister_Stage';
				}

				if(!empty($data['Diag_id1'])) {
					$queryParams['Diag_id1'] = $data['Diag_id1'];
					$filter .= ' and HR.Diag_FirstId = :Diag_id1';
				}

				if(!empty($data['HTMQueueType_id'])) {
					$queryParams['HTMQueueType_id'] = $data['HTMQueueType_id'];
					$filter .= ' and HR.HTMQueueType_id = :HTMQueueType_id';
				}

				if(!empty($data['isSetPlannedHospDate'])) {
					switch ($data['isSetPlannedHospDate']) {
						case 2:
							$filter .= ' and HTMRegister_PlannedHospDate is not null';
							break;
						case 1:
							$filter .= ' and HTMRegister_PlannedHospDate is null';
							break;
						break;
					}
				}

				if(!empty($data['LpuSectionProfile_id'])) {
					$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
					$filter .= ' and EDH.LpuSectionProfile_id = :LpuSectionProfile_id';
				}

				if(!empty($data['HTMRegister_OperDate_Range'][0]) && !empty($data['HTMRegister_OperDate_Range'][1])){
					$queryParams['HTMRegister_OperDate_Range_0'] = $data['HTMRegister_OperDate_Range'][0];
					$queryParams['HTMRegister_OperDate_Range_1'] = $data['HTMRegister_OperDate_Range'][1];
					$filter .= ' and HR.HTMRegister_OperDate between :HTMRegister_OperDate_Range_0 and :HTMRegister_OperDate_Range_1';
				}

				if(!empty($data['HTMResult_id'])) {
					$queryParams['HTMResult_id'] = $data['HTMResult_id'];
					$filter .= ' and HR.HTMResult_id = :HTMResult_id';
				}

				if(!empty($data['HTMRegister_IsSigned'])) {
					$queryParams['HTMRegister_IsSigned'] = $data['HTMRegister_IsSigned'];
					$filter .= ' and HR.HTMRegister_IsSigned = :HTMRegister_IsSigned';
				}
				break;
			case 'GibtRegistry':
				$filter .= " and PR.PersonRegisterType_id = 70 ";
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id
					inner join v_Diag D with (nolock) on D.Diag_id = PR.Diag_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_Morbus M with (nolock) on M.Morbus_id = PR.Morbus_id
					left join v_MorbusGEBT MG with (nolock) on MG.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				';

				// Регистр
				if ( !empty($data['PersonRegisterType_id']) ) {
					if ( $data['PersonRegisterType_id'] == 2 ) {
						$filter .= " and PR.PersonRegister_disDate is null ";
					}
					else if ($data['PersonRegisterType_id'] == 3) {
						$filter .= " and PR.PersonRegister_disDate is not null ";
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) ) {
					$filter .= " and PR.PersonRegister_setDate >= cast(:PersonRegister_setDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
				}

				if ( isset($data['PersonRegister_setDate_Range'][1]) ) {
					$filter .= " and PR.PersonRegister_setDate <= cast(:PersonRegister_setDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) ) {
					$filter .= " and PR.PersonRegister_disDate >= cast(:PersonRegister_disDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
				}

				if ( isset($data['PersonRegister_disDate_Range'][1]) ) {
					$filter .= " and PR.PersonRegister_disDate <= cast(:PersonRegister_disDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
				}

				// диагноз
				if (isset($data['Diag_Code_From'])) {
					$filter .= " and D.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if (isset($data['Diag_Code_To'])) {
					$filter .= " and D.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if (!empty($data['MorbusGEBT_setDiagDT_Range'][0])) {
					$filter .= " and MG.Morbus_setDT >= cast(:MorbusGEBT_setDiagDT_Range_0 as datetime) ";
					$queryParams['MorbusGEBT_setDiagDT_Range_0'] = $data['MorbusGEBT_setDiagDT_Range'][0];
				}
				if (!empty($data['MorbusGEBT_setDiagDT_Range'][1])) {
					$filter .= " and MG.Morbus_setDT <= cast(:MorbusGEBT_setDiagDT_Range_1 as datetime) ";
					$queryParams['MorbusGEBT_setDiagDT_Range_1'] = $data['MorbusGEBT_setDiagDT_Range'][1];
				}

				break;
			case 'EvnERSBirthCertificate':
				$query .= '
					inner join v_EvnERSBirthCertificate ERS with (nolock) on ERS.Person_id = PS.Person_id
					left join v_ERSStatus ES (nolock) on ES.ERSStatus_id = ERS.ERSStatus_id
					left join v_ERSCloseCauseType CCT (nolock) on CCT.ERSCloseCauseType_id = ERS.ERSCloseCauseType_id
					outer apply (
						select top 1 *
						from v_ErsRequest ER with(nolock)
						where ER.EvnERS_id = ERS.EvnERSBirthCertificate_id
						order by ER.ERSRequest_insDT desc
					) as ER
					left join v_ErsRequestType ERT (nolock) on ERT.ErsRequestType_id = ER.ErsRequestType_id
					left join v_ErsRequestStatus ERSt (nolock) on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
					outer apply (
						Select (
							select ere.ERSRequestError_Descr + \', \' as \'data()\'
							from ERSRequestError ere with(nolock)
							where ere.ERSRequest_id = ER.ErsRequest_id
							for xml path(\'\')
						) as ERSRequestError
					) as ERE
					outer apply (
						select top 1 ES.ERSStatus_Name
						from v_EvnERSTicket ERT (nolock)
						inner join v_ERSStatus ES (nolock) on ES.ERSStatus_id = ERT.ERSStatus_id
						where ERT.ERSTicketType_id = 1 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
					) ticket1
					outer apply (
						select top 1 ES.ERSStatus_Name
						from v_EvnERSTicket ERT (nolock)
						inner join v_ERSStatus ES (nolock) on ES.ERSStatus_id = ERT.ERSStatus_id
						where ERT.ERSTicketType_id = 2 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
					) ticket2
					outer apply (
						select top 1 ES.ERSStatus_Name
						from v_EvnERSTicket ERT (nolock)
						inner join v_ERSStatus ES (nolock) on ES.ERSStatus_id = ERT.ERSStatus_id
						where ERT.ERSTicketType_id = 3 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
					) ticket31
					outer apply (
						select top 1 ES.ERSStatus_Name
						from v_EvnERSTicket ERT (nolock)
						inner join v_ERSStatus ES (nolock) on ES.ERSStatus_id = ERT.ERSStatus_id
						where ERT.ERSTicketType_id = 4 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
					) ticket32
				';
				// 147820
				// $filter .= " and ERS.Lpu_id = :Lpu_id ";
				// $queryParams['Lpu_id'] = $data['Lpu_id'];

				if (!empty($data['EvnERSBirthCertificate_Number'])) {
					$filter .= ' and ERS.EvnERSBirthCertificate_Number = :EvnERSBirthCertificate_Number ';
					$queryParams['EvnERSBirthCertificate_Number'] = $data['EvnERSBirthCertificate_Number'];
				}

				if (count($data['EvnERSBirthCertificate_CreateDate_Range']) == 2 && !empty($data['EvnERSBirthCertificate_CreateDate_Range'][0])) {
					$filter .= ' and ers.EvnErsBirthCertificate_setDate between :EvnERSBirthCertificate_CreateDate_RangeStart and :EvnERSBirthCertificate_CreateDate_RangeEnd ';
					$queryParams['EvnERSBirthCertificate_CreateDate_RangeStart'] = $data['EvnERSBirthCertificate_CreateDate_Range'][0];
					$queryParams['EvnERSBirthCertificate_CreateDate_RangeEnd'] = $data['EvnERSBirthCertificate_CreateDate_Range'][1];
				}
				if (!empty($data['ERSStatus_id'])) {
					$filter .= ' and ers.ERSStatus_id = :ERSStatus_id ';
					$queryParams['ERSStatus_id'] = $data['ERSStatus_id'];
				}
				if (!empty($data['ERSRequestType_id'])) {
					$filter .= ' and er.ERSRequestType_id = :ERSRequestType_id ';
					$queryParams['ERSRequestType_id'] = $data['ERSRequestType_id'];
				}
				if (!empty($data['ERSRequestStatus_id'])) {
					$filter .= ' and er.ERSRequestStatus_id = :ERSRequestStatus_id ';
					$queryParams['ERSRequestStatus_id'] = $data['ERSRequestStatus_id'];
				}

				break;
		}

		if( getRegionNick() == 'ufa' && in_array($data['SearchFormType'], array('EvnPLStom', 'EvnPL')) ){
			$evnDisDateField = $data ['SearchFormType'] == 'EvnPL' ? 'Evn.Evn_disDT' : 'EPLS.EvnPLStom_disDate';
			if ( !empty($data['RzhdOrg_id']) || !empty($data['RzhdWorkerCategory_id']) || !empty($data['RzhdWorkerGroup_id'])) {
				$query .= "outer apply(
					select
						R.Register_id,
						RR.RzhdRegistry_id,
						RR.Org_id,
						RR.RzhdWorkerCategory_id,
						RR.RzhdWorkerGroup_id
					from r2.v_Register R with(nolock)
					left join r2.RegisterType RT with(nolock) on RT.RegisterType_id = R.RegisterType_id
					left join r2.RzhdRegistry RR with (nolock) on RR.Register_id = R.Register_id
					where
						RT.RegisterType_Code = 'RZHD'
						and R.Person_id = PS.Person_id
						and isnull({$evnDisDateField}, @getDT) between R.Register_setDate and isnull(R.Register_disDate,@getDT)
				) RzhdReg";
			}

			if( !empty($data['RzhdOrg_id']) ) {
				$queryParams['RzhdOrg_id'] = $data['RzhdOrg_id'];
				$filter .= " and RzhdReg.Org_id = :RzhdOrg_id";
			}

			if( !empty($data['RzhdWorkerCategory_id'])) {
				$queryParams['RzhdWorkerCategory_id'] = $data['RzhdWorkerCategory_id'];
				$filter .= " and RzhdReg.RzhdWorkerCategory_id = :RzhdWorkerCategory_id";
			}

			if( !empty($data['RzhdWorkerGroup_id'])) {
				$queryParams['RzhdWorkerGroup_id'] = $data['RzhdWorkerGroup_id'];
				$filter .= " and RzhdReg.RzhdWorkerGroup_id = :RzhdWorkerGroup_id";
			}
		}

		if ($data['PersonPeriodicType_id'] == 3) {
			$filter .= " and exists(
				select top 1 1
				from v_Person_all PStmp (nolock)
				where PStmp.Person_id = PS.Person_id
			";

			$this->getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias, 'PStmp');

			$filter .= ") ";
		} else {
			$this->getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias);
		}

		// Подключаем PersonPrivilege, если поиск вызван с формы поиска льготников
		if (($data['SearchFormType'] == 'PersonPrivilege')) {
			$query .= " inner join PersonPrivilege PP with (nolock) on PP.Person_id = PS.Person_id";
			$query .= " left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id ";
			$query .= " inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id";
			$query .= " left join v_WhsDocumentCostItemType PT_WDCIT with (nolock) on PT_WDCIT.WhsDocumentCostItemType_id = PT.WhsDocumentCostItemType_id";
			$query .= " left join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = PT.ReceptFinance_id";
			$query .= " left join v_Address PUAdd with (nolock) on PUAdd.Address_id = isnull(PS.PAddress_id, PS.UAddress_id)";
			$query .= " left join v_PMUserCache UserDel with (nolock) on UserDel.pmUser_id = PP.pmUser_delID";
			$query .= " left join v_PrivilegeCloseType PrivCT with (nolock) on PrivCT.PrivilegeCloseType_id = PP.PrivilegeCloseType_id";
			$query .= "
				outer apply (
					select top 1
						(
							i_DocPriv.DocumentPrivilege_Ser+' '+
							i_DocPriv.DocumentPrivilege_Num+' '+
							convert(varchar(10), i_DocPriv.DocumentPrivilege_begDate, 104)+' '+
							coalesce(i_Org.Org_Nick, i_DocPriv.DocumentPrivilege_Org, '')	
						) as DocumentPrivilege_Data
					from
					 	v_DocumentPrivilege i_DocPriv with (nolock)
						left join v_Org i_Org with (nolock) on i_Org.Org_id = i_DocPriv.Org_id
					where
						i_DocPriv.PersonPrivilege_id = PP.PersonPrivilege_id
					order by
						i_DocPriv.DocumentPrivilege_id
				) as DocPriv
			";
			//$query .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = PP.PrivilegeType_id";
			//$query .= " inner join v_Lpu LPU with (nolock) on LPU.Lpu_id = PP.Lpu_id";
			//https://redmine.swan.perm.ru/issues/104566
			if ($data['session']['region']['nick'] == 'krym') {
				$query .= "
					outer apply(
						select count(PC.PersonCard_id) as cntPC
						from v_PersonCard PC (nolock)
						inner join v_LpuAttachType LAT (nolock) on LAT.LpuAttachType_id = PC.LpuAttachType_id
						where PC.Person_id = PS.Person_id
						and PC.Lpu_id = " . (!empty($data['session']['lpu_id']) ? $data['session']['lpu_id'] : "NULL") . "
						and LAT.LpuAttachType_SysNick in ('main','slug')
					) as PCardChecks
				";
			}

			if ($this->regionNick == 'kz') {
				$query .= " left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id";
				$query .= " left join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id";
			} else {
				$query .= " left join v_Diag Diag (nolock) on Diag.Diag_id = PP.Diag_id";
			}

			$this->getPrivilegeFilters($data, $filter, $queryParams);
			$this->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);
		}
		// Подключаем PersonPrivilege, если заданы фильтры на вкладке "Льгота"
		else if (( isset($data['RegisterSelector_id']) ) ||
				( isset($data['PrivilegeType_id']) ) || ( isset($data['Privilege_begDate']) ) || ( isset($data['Privilege_begDate_Range'][0]) ) ||
				( isset($data['Privilege_begDate_Range'][1]) ) || ( isset($data['Privilege_endDate']) ) ||
				( isset($data['Privilege_endDate_Range'][0]) ) || ( isset($data['Privilege_endDate_Range'][1]) ||
				( isset($data['Refuse_id']) ) || ( isset($data['RefuseNextYear_id'])) ) ||
				( $data['SearchFormType'] == 'PersonDisp' && isset($data['PrivilegeStateType_id']))
		) {
			$filter .= " and exists (select personprivilege_id from v_PersonPrivilege PP with (nolock) ";
			$filter .= " inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id";
			//$filter .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = PP.PrivilegeType_id";
			$filter .= " WHERE PP.Person_id = PS.Person_id";
			$this->getPrivilegeFilters($data, $filter, $queryParams);
			$this->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);

			$filter .= ") ";
		}

		if (in_array($data['SearchFormType'], array('EvnPS', 'EvnSection', 'EvnPL', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'PersonDisp', 'EvnRecept', 'EvnReceptGeneral', 'CmpCallCard', 'CmpCloseCard'))) {
			$diag_field_code = '';
			switch ($data['SearchFormType']) {
				case 'PersonDisp':
					$diag_field_code = 'dg1.Diag_Code';
					break;
				case 'EvnPL':
					$diag_field_code = ($dbf === true ? 'dbfdiag.Diag_Code' : 'EVPLD.Diag_Code');
					break;
				case 'EvnVizitPL':
				case 'EvnVizitPLStom':
					$diag_field_code = ($dbf === true ? 'dbfdiag.Diag_Code' : 'evpldiag.Diag_Code');
					break;
				case 'EvnPLStom':
					//$diag_field_code = 'EVPLSD.Diag_Code';
					$diag_field_code = ($dbf === true ? 'dbfdiag.Diag_Code' : 'EVPLSD.Diag_Code');
					break;
				case 'EvnPS':
					$diag_field_code = array('Dtmp.Diag_Code'/*, 'DP.Diag_Code'*/);
					break;
				case 'EvnSection':
					$diag_field_code = 'Dtmp.Diag_Code';
					break;
				case 'EvnRecept':
					if (!$isFarmacy)
						$diag_field_code = 'ERDiag.Diag_Code';
					else
						$diag_field_code = null;
					break;
				case 'CmpCallCard':
					if (in_array($data['session']['region']['nick'], array('kareliya', 'ufa'))) {
						$diag_field_code = 'CLD.Diag_Code';
					} else {
						$diag_field_code = 'CD.CmpDiag_Code';
					}
					break;
				case 'CmpCloseCard':
					$diag_field_code = 'CLD.Diag_Code';
					break;
				case 'EvnReceptGeneral':
					if (!$isFarmacy)
						$diag_field_code = 'ERDiag.Diag_Code';
					else
						$diag_field_code = null;
					break;
				default:
					$diag_field_code = 'D.Diag_Code';
			}
			$diagFilter = getAccessRightsDiagFilter($diag_field_code);
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
		}

		if (( isset($data['Refuse_id']) ) || ( isset($data['RefuseNextYear_id']) )) {
			// Отказ
			if (isset($data['Refuse_id'])) {
				$filter .= " and " . ($data['Refuse_id'] == 1 ? "not " : "") . "exists (
					select top 1 1
					from v_PersonPrivilege PP with (nolock)
						inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
						left join v_WhsDocumentCostItemType WDCIT with (nolock) on WDCIT.WhsDocumentCostItemType_id = PT.WhsDocumentCostItemType_id
					where
						PP.Person_id = PS.Person_id
						and WDCIT.WhsDocumentCostItemType_Nick = 'fl'
						and ISNULL(PS.Person_IsRefuse2, 1) = 2
						and PP.PersonPrivilege_begDate <= @getDT
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= cast(@getDT as date))
				";
				$filter .= ") ";
			}

			// Отказ
			if (isset($data['RefuseNextYear_id'])) {
				$filter .= " and " . ($data['RefuseNextYear_id'] == 1 ? "not " : "") . "exists (
					select top 1 1
					from v_PersonPrivilege PPN with (nolock)
						inner join v_PrivilegeType PTN (nolock) on PTN.PrivilegeType_id = PPN.PrivilegeType_id
							and PTN.ReceptFinance_id = 1
						outer apply (
							select top 1 ISNULL(PRN.PersonRefuse_IsRefuse, 1) as PersonRefuse_IsRefuse
							from v_PersonRefuse PRN with (nolock)
							where PRN.Person_id = PPN.Person_id
								and ISNULL(PRN.PersonRefuse_Year, YEAR(@getDT)) = YEAR(@getDT) + 1
						) PRefN
					where PPN.Person_id = PS.Person_id
						and ISNULL(PRefN.PersonRefuse_IsRefuse, 1) = 2
						and PPN.PersonPrivilege_begDate <= @getDT
						and (PPN.PersonPrivilege_endDate is null or PPN.PersonPrivilege_endDate >= cast(@getDT as date))
				";
				$filter .= ") ";
				/*
				  $filter .= " and (exists (
				  select top 1 PRN.PersonRefuse_id
				  from PersonPrivilege PPN with(nolock)
				  inner join PrivilegeType PTN with(nolock) on PTN.PrivilegeType_id = PPN.PrivilegeType_id
				  and PTN.PrivilegeType_Code <= 249
				  left join PersonEvn PEN with(nolock) on PEN.Person_id = PPN.Person_id
				  left join PersonEvnClass PECN with(nolock) on PECN.PersonEvnClass_id = PEN.PersonEvnClass_id
				  left join PersonRefuse PRN with(nolock) on PRN.Server_id = PEN.Server_id
				  and PRN.PersonRefuse_id = PEN.PersonEvn_id
				  where PPN.Person_id = PS.Person_id
				  and ISNULL(PRN.PersonRefuse_IsRefuse, 1) = :RefuseNextYear_id
				  and PPN.PersonPrivilege_begDate <= dbo.tzGetDate()
				  and (PPN.PersonPrivilege_endDate is null or PPN.PersonPrivilege_endDate > dbo.tzGetDate())
				  and ISNULL(PRN.PersonRefuse_Year, YEAR(dbo.tzGetDate()) + 1) = YEAR(dbo.tzGetDate()) + 1
				  ";
				  $queryParams['RefuseNextYear_id'] = $data['RefuseNextYear_id'];
				  $filter .= ") ";

				  if ($data['RefuseNextYear_id'] == 1 ) {
				  $filter .= " or
				  not exists (select PersonRefuse_id from v_PersonRefuse PR with(nolock) where PR.Person_id = PS.Person_id and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate()) + 1 )";
				  }
				 */
			}
		}
		/*
		  if ( $data['SearchFormType'] == 'PersonCard' ) {

		  if ( $data['PersonCard_IsDms'] > 0 ) {
		  $exists = "";
		  if ( $data['PersonCard_IsDms'] == 1 )
		  $exists = " not ";
		  $filter .= " and " . $exists . " exists(
		  select
		  PersonCard_id
		  from
		  v_PersonCard  (nolock)
		  where
		  Person_id = PC.Person_id
		  and LpuAttachType_id = 5
		  and PersonCard_endDate >= dbo.tzGetDate()
		  and CardCloseCause_id is null
		  ) ";

		  $exists = " = ";
		  if ( $data['PersonCard_IsDms'] == 1 )
		  $exists = " != ";
		  $filter .= " and pc.LpuAttachType_id " . $exists . " 5 ";
		  }

		  if ( $data['PersonCardStateType_id'] == 1 ) {
		  $query .= " inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
		  $filter .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > dbo.tzGetDate())";
		  }
		  else {
		  $query .= " inner join v_PersonCard_all PC with (nolock) on PC.Person_id = PS.Person_id";
		  }

		  $query .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
		  $query .= " left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id";
		  $query .= "
		  left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
		  left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
		  ";

		  $this->getPersonCardFilters($data, $filter, $queryParams, $orderby);

		  } */
		if ($data['SearchFormType'] == 'PersonCallCenter') {
			$personCardFilter = '';
			$this->getPersonCardFilters($data, $personCardFilter, $queryParams, $orderby, $pac_filter);

			if (!empty($personCardFilter)) {
				$filter .= " and PersonCard.PersonCard_id is not null";
			}

			$query .= "
				outer apply (
					select top 1
						 PersonCard_id
						,PersonCard_Code
						,PersonCard_begDate
						,PersonCard_endDate
						,LpuAttachType_Name
						,LpuRegionType_Name
						,PersonCard_IsAttachCondit
						,PersonCardAttach_id
						,LpuRegion_id
						,Lpu_id
						,LpuRegion_fapid
					from v_PersonCard PC with (nolock)
					where PC.Person_id = PS.Person_id
						" . (!empty($personCardFilter) ? $personCardFilter : '') . "
					order by LpuAttachType_id
				) PersonCard
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PersonCard.LpuRegion_id
				left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PersonCard.LpuRegion_fapid
				left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PersonCard.Lpu_id
				left join v_Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join v_Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
				left join v_PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR(@getDT)+1)
				left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_NewslatterAccept NA with (nolock) on NA.Person_id = PS.Person_id and NA.Lpu_id = :Lpu_id and NA.NewslatterAccept_endDate is null
				outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from v_PersonDisp with (nolock)
					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > @getDT)
					and Sickness_id IN (1,3,4,5,6,7,8)
				) as disp
			";
		} else if ($data['SearchFormType'] == 'PersonCard') {
			//var_dump($data);die;

			if ($data['PersonCard_IsDms'] > 0) {
				$exists = "";
				if ($data['PersonCard_IsDms'] == 1)
					$exists = " not ";
				$filter .= " and " . $exists . " exists(
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

				$exists = " = ";
				if ($data['PersonCard_IsDms'] == 1)
					$exists = " != ";
				$filter .= " and pc.LpuAttachType_id " . $exists . " 5 ";
			}

			if ($data['session']['region']['nick'] != 'kz') { //Добавлены фильтры - https://redmine.swan.perm.ru/issues/52901
				//БДЗ
				if (!empty($data['IsBDZ']) && ($data['IsBDZ'] <> 0)) {
					if ($data['IsBDZ'] == 2)
						$filter .= " and PS.Server_pid = 0";
					else
						$filter .= " and PS.Server_pid <> 0";
				}

				//Идентификатор с ТФОМС
				if (in_array($data['session']['region']['nick'], array('perm', 'ufa'))) {
					if (!empty($data['TFOMSIdent']) && ($data['TFOMSIdent'] <> 0)) {
						$query .= " left join v_Person Pers with (nolock) on Pers.Person_id = PS.Person_id";
						if ($data['TFOMSIdent'] == 2)
							$filter .= " and Pers.BDZ_Guid is not null";
						else
							$filter .= " and Pers.BDZ_Guid is null";
					}
				}

				//Наличие полиса (1 - отсутствует, 2 - в наличии)
				if (!empty($data['HasPolis_Code']) && ($data['HasPolis_Code'] <> 0)) {
					if ($data['HasPolis_Code'] == 2)
						$filter .= " and PS.Polis_id is not null";
					else
						$filter .= " and PS.Polis_id is null";
				}

				//Данные о закрытии полиса
				if (!empty($data['PolisClosed']) && $data['PolisClosed'] <> 0) {
					$query .= " left join v_Polis Pol with (nolock) on Pol.Polis_id = PS.Polis_id";
					if ($data['PolisClosed'] == 2) {
						$filter .= " and (PS.Polis_id is not null and Pol.Polis_endDate is not null)";

						//Диапазон дат закрытий полиса
						if (isset($data['PolisClosed_Date_Range'][0])) {
							$filter .= " and Pol.Polis_endDate >= cast(:PolisClosed_Date_Range_0 as datetime)";
							$queryParams['PolisClosed_Date_Range_0'] = $data['PolisClosed_Date_Range'][0];
						}
						if (isset($data['PolisClosed_Date_Range'][1])) {
							$filter .= " and Pol.Polis_endDate <= cast(:PolisClosed_Date_Range_1 as datetime)";
							$queryParams['PolisClosed_Date_Range_1'] = $data['PolisClosed_Date_Range'][1];
						}
					} else {
						$filter .= " and (PS.Polis_id is not null and Pol.Polis_endDate is null)";
					}
				}
			}

			if ($data['PersonCardStateType_id'] == 1) {
				if ($data['session']['region']['nick'] == 'khak') {
					$query .= " left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
				} else {
					$query .= " inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
				}
				//$query .= " inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
				$filter .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @getDT)";
			} else {
				$query .= " inner join v_PersonCard_all PC with (nolock) on PC.Person_id = PS.Person_id";
			}
			$query .= " outer apply(
				select top 1 ACLT.AmbulatCardLocatType_Name,PACL.PersonAmbulatCard_id from v_PersonAmbulatCardLocat PACL with(nolock)
				left join AmbulatCardLocatType ACLT with(nolock) on ACLT.AmbulatCardLocatType_id=PACL.AmbulatCardLocatType_id
				left join v_PersonAmbulatCardLink PACLink with(nolock) on PACLink.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
				where pc.PersonCard_id=PACLink.PersonCard_id
				order by PACL.PersonAmbulatCardLocat_begDate desc
			)PACLT ";
			$query .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
			$query .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid";
			$query .= " left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id";
			$query .= " 
				left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
			";
			$query .= "
							left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
						";
			$query .= " left join PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR(@getDT)+1)";
			//$query .= " left join PersonDisp PD with(nolock) on PD.Person_id = ps.Person_id";
			$query .= " outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from PersonDisp with (nolock)
					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > @getDT)
					and Sickness_id IN (1,3,4,5,6,7,8)
				) as disp";
			$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
		} else if ($data['SearchFormType'] == 'WorkPlacePolkaReg') {
			if ($data['PersonCardStateType_id'] == 1) {
				$query .= "
				outer apply (
					select top 1
					coalesce(PC_ALT.PersonCard_id,PC_SERV.PersonCard_id,PC_MAIN.PersonCard_id) as PersonCard_id
					from Person with (nolock)
					--left join v_PersonCard PC_MAIN with (nolock) on PC_MAIN.Person_id = PS.Person_id and PC_MAIN.LpuAttachType_id = 1
					outer apply (
						select top 1 PersonCard_id, Lpu_id
						from v_PersonCard with(nolock)
						where Person_id = PS.Person_id and LpuAttachType_id = 1 and PersonCard_endDate is null
						order by PersonCard_begDate
					) as PC_MAIN
					
					-- Если у пациента нет основного прикрепления, то показывать гинекологическое, стоматологическое прикрепление
					left join v_PersonCard PC_ALT with (nolock) on PC_ALT.Lpu_id = :Lpu_id and PC_ALT.Person_id = PS.Person_id and PC_ALT.LpuAttachType_id in (2,3)
					-- Служебное прикрепление к МО показывать только в той же МО и только если нет активного прикрепления другого типа к этой МО.
					left join v_PersonCard PC_SERV with (nolock) on PC_SERV.Person_id = PS.Person_id and PC_SERV.LpuAttachType_id = 4 and PC_SERV.Lpu_id = :Lpu_id and (coalesce(PC_MAIN.Lpu_id,PC_ALT.Lpu_id,0) != PC_SERV.Lpu_id)
					where Person.Person_id = PS.Person_id
				) as PersonCard
				left join v_PersonCard PC with (nolock) on PC.PersonCard_id = PersonCard.PersonCard_id";
			} else {
				$query .= "
				outer apply (
					select top 1
					coalesce(PC_SERV.PersonCard_id,PC_MAIN.PersonCard_id,PC_ALT.PersonCard_id) as PersonCard_id
					from Person with (nolock)
					--left join v_PersonCard_all PC_MAIN with (nolock) on PC_MAIN.Person_id = PS.Person_id and PC_MAIN.LpuAttachType_id = 1
					outer apply (
						select top 1 PersonCard_id, Lpu_id
						from v_PersonCard with(nolock)
						where Person_id = PS.Person_id and LpuAttachType_id = 1 and PersonCard_endDate is null
						order by PersonCard_begDate
					) as PC_MAIN
					-- Если у пациента нет основного прикрепления, то показывать гинекологическое, стоматологическое прикрепление
					left join v_PersonCard_all PC_ALT with (nolock) on PC_MAIN.PersonCard_id is null and PC_ALT.Person_id = PS.Person_id and PC_ALT.LpuAttachType_id in (2,3)
					-- Служебное прикрепление к МО показывать только в той же МО и только если нет активного прикрепления другого типа к этой МО.
					left join v_PersonCard_all PC_SERV with (nolock) on PC_SERV.Person_id = PS.Person_id and PC_SERV.LpuAttachType_id = 4 and PC_SERV.Lpu_id = :Lpu_id and (coalesce(PC_MAIN.Lpu_id,PC_ALT.Lpu_id,0) != PC_SERV.Lpu_id)
					where Person.Person_id = PS.Person_id
				) as PersonCard
				left join v_PersonCard_all PC with (nolock) on PC.PersonCard_id = PersonCard.PersonCard_id";
			}

			$query .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
			$query .= " left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id";
			$query .= " left join v_PersonState Inn with(nolock) on Inn.Person_id = ps.Person_id";
			$query .= " 
				left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
				left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
			";
			$query .= " left join PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR(@getDT)+1)";
			//$query .= " left join PersonDisp PD with(nolock) on PD.Person_id = ps.Person_id";
			$query .= " outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from PersonDisp with (nolock)
					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > @getDT)
					and Sickness_id IN (1,3,4,5,6,7,8)
				) as disp";
			$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
		} else {
			if (($data['AttachLpu_id'] > 0) || (strlen($data['PersonCard_Code']) > 0 && !in_array($data['SearchFormType'], array('EvnPL', 'EvnVizitPL'))) ||
					( isset($data['PersonCard_begDate']) ) || ( isset($data['PersonCard_begDate_Range'][0]) ) || ( isset($data['PersonCard_begDate_Range'][1]) ) ||
					( isset($data['PersonCard_endDate']) ) || ( isset($data['PersonCard_endDate_Range'][0]) ) || ( isset($data['PersonCard_endDate_Range'][1]) ) || ($data['LpuAttachType_id'] > 0) ||
					(isset($data['LpuRegion_id'])) || ($data['LpuRegionType_id'] > 0) || ($data['MedPersonal_id'] > 0) || isset($data['LpuRegion_Fapid']) ||
					($data['PersonCard_IsDms'] > 0) || $data['PersonCard_IsDms'] > 0 || !empty($data['PersonCard_IsAttachCondit'])
			) {
				$needWithoutAttach = false;
				if (getRegionNick() == 'ekb' && in_array($data['SearchFormType'], array('EvnPLDispDop13', 'EvnPLDispDop13Sec'))) {
					// проверяем наличие объёма "Без прикрепления"
					$resp_vol = $this->queryResult("
						declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'ДВН_Б_ПРИК'); -- ДВН без прикрепления
						declare @curDate datetime = dbo.tzGetDate();

						SELECT  TOP 1
							av.AttributeValue_id
						FROM
							v_AttributeVision avis (nolock)
							inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						WHERE
							avis.AttributeVision_TableName = 'dbo.VolumeType'
							and av.AttributeValue_ValueIdent = :Lpu_id
							and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
							and avis.AttributeVision_IsKeyValue = 2
							and ISNULL(av.AttributeValue_begDate, @curDate) <= @curDate
							and ISNULL(av.AttributeValue_endDate, @curDate) >= @curDate

					", array(
						'Lpu_id' => $data['Lpu_id']
					));
					if (!empty($resp_vol[0]['AttributeValue_id']) && $data['AttachLpu_id'] == 666666) {
						// Для отображения только тех леюдей, у кого нет прикреплений ни к одной МО. Работает, только если у МО есть открытый объем ДВН_Б_ПРИК
						$needWithoutAttach = true;
					}
				}
				if (!in_array($data['SearchFormType'], array('BskRegistry', 'IPRARegistry','AdminVIPPerson', 'ReabRegistry', 'ReanimatRegistry', 'ZNOSuspectRegistry', 'SportRegistry','HTMRegister'))) {

					$pc_fitler = $needWithoutAttach ? ' not' : ''; // not - где не существует прикрепления

					if (in_array($data['SearchFormType'], array('EvnPL', 'EvnVizitPL'))) {
						if ($data['PersonCardStateType_id'] == 1) {
							$pc_fitler .= " exists (select top 1 PC.personcard_id from v_PersonCard PC with (nolock) ";
							$pc_fitler .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
							$pc_fitler .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid"; //https://redmine.swan.perm.ru/issues/78988
							//$pc_fitler .= " left join v_PersonAmbulatCardLink PACL with(nolock) on PACL.PersonCard_id = PC.PersonCard_id"; //https://redmine.swan.perm.ru/issues/85161
							//$pc_fitler .= " left join v_PersonAmbulatCard PAC with(nolock) on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id"; //https://redmine.swan.perm.ru/issues/85161
							$pc_fitler .= " WHERE PC.Person_id = PS.Person_id";
							$pc_fitler .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @getDT)";
						} else {
							$pc_fitler .= " exists (select top 1 PC.personcard_id from v_PersonCard_all PC with (nolock) ";
							$pc_fitler .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
							$pc_fitler .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid"; //https://redmine.swan.perm.ru/issues/78988
							$pc_fitler .= " left join v_PersonAmbulatCardLink PACL with(nolock) on PACL.PersonCard_id = PC.PersonCard_id"; //https://redmine.swan.perm.ru/issues/85161
							$pc_fitler .= " left join v_PersonAmbulatCard PAC with(nolock) on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id"; //https://redmine.swan.perm.ru/issues/85161
							$pc_fitler .= " WHERE PC.Person_id = PS.Person_id";
						}
					} else {
						if ($needWithoutAttach === true) {
							$pc_fitler .= " exists (select top 1 personcard_id from v_PersonCard PC with (nolock) ";
							$pc_fitler .= " WHERE PC.Person_id = PS.Person_id";
						} elseif ($data['PersonCardStateType_id'] == 1) {
							$pc_fitler .= " exists (select top 1 personcard_id from v_PersonCard PC with (nolock) ";
							$pc_fitler .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
							$pc_fitler .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid"; //https://redmine.swan.perm.ru/issues/78988

							$pc_fitler .= " left join v_MedStaffRegion MedStaffRegion with (NOLOCK) on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
											left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
											left join persis.Post p with (nolock) on p.id = msf.Post_id
											"; //https://redmine.swan.perm.ru/issues/137508
							$pc_fitler .= " WHERE PC.Person_id = PS.Person_id";
							$pc_fitler .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @getDT)";
							// фильтр для Сигнальой информации
							if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
								$pc_fitler .= " and MedStaffRegion.MedStaffRegion_isMain = 2 -- основной врач на участке
												and p.code in (74,47,40,117,111)"; //Должность врача
							}
						} else {
							$pc_fitler .= " exists (select top 1 personcard_id from v_PersonCard_all PC with (nolock) ";
							$pc_fitler .= " left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
							$pc_fitler .= " left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid"; //https://redmine.swan.perm.ru/issues/78988
							$pc_fitler .= " WHERE PC.Person_id = PS.Person_id";
						}
					}

					$this->getPersonCardFilters($data, $pc_fitler, $queryParams, $orderby, $pac_filter);

					$filter .= ' and ' . $pc_fitler . ') ';
				} else if (in_array($data['SearchFormType'], array('BskRegistry', 'IPRARegistry','AdminVIPPerson', 'ReabRegistry', 'ReanimatRegistry', 'ZNOSuspectRegistry', 'SportRegistry'))) {
					$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
				}
			} else {
				$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
			}
		}
		// Пользователь
		if ($data['pmUser_insID'] > 0) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$filter .= " and " . $main_alias . ".pmUserBeg_insID = :pmUser_insID";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
				$filter .= " and case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.pmUser_insID else " . $main_alias . ".pmUser_insID end = :pmUser_insID";
			} else if ($data['SearchFormType'] == 'EvnPL') {
				$filter .= " and Evn.pmUser_insID = :pmUser_insID";
			} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
				$filter .= " and E.pmUser_insID = :pmUser_insID";
			} else {
				$filter .= " and " . $main_alias . ".pmUser_insID = :pmUser_insID";
			}
			$queryParams['pmUser_insID'] = $data['pmUser_insID'];
		}

		// Пользователь
		if ($data['pmUser_updID'] > 0) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$filter .= " and " . $main_alias . ".pmUserEnd_insID = :pmUser_updID";
			} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
				$filter .= " and case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.pmUser_updID else " . $main_alias . ".pmUser_updID end = :pmUser_updID";
			} else if ($data['SearchFormType'] == 'EvnPL') {
				$filter .= " and Evn.pmUser_updID = :pmUser_updID";
			} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
				$filter .= " and E.pmUser_updID = :pmUser_updID";
			} else {
				$filter .= " and " . $main_alias . ".pmUser_updID = :pmUser_updID";
			}
			$queryParams['pmUser_updID'] = $data['pmUser_updID'];
		}

		if (substr($data['SearchFormType'], 0, 3) == 'Kvs') { //для выгрузки квс в дбф
			$fld_name = substr($data['SearchFormType'], 3);
			switch ($data['SearchFormType']) {
				case 'KvsPerson':
					$fld_name = 'EvnPS';
					break;
				case 'KvsEvnDiag': $fld_name = 'EvnDiagPS';
					break;
				case 'KvsNarrowBed': $fld_name = 'EvnSectionNarrowBed';
					break;
			}
			// для Person фильтр по дате делаем по КВС
			$fld_name = ($data['SearchFormType'] != 'KvsPerson') ? $main_alias . "." . $fld_name : "EPS." . $fld_name;
			$is_pc = ($data['SearchFormType'] == 'KvsPersonCard' && $data['PersonCardStateType_id'] != 1);

			// Пользователь
			if (isset($data['InsDate'])) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (cast(convert(varchar(10), EOSectBP.EvnOtherSectionBedProfile_insDT, 112) as datetime) = cast(:InsDate as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), " . $fld_name . ($is_pc ? "Beg_insDT" : "_insDT") . ", 112) as datetime) = cast(:InsDate as datetime)";
				$queryParams['InsDate'] = $data['InsDate'];
			}
			// Пользователь
			if (isset($data['InsDate_Range'][0])) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (cast(convert(varchar(10), EOSectBP.EvnOtherSectionBedProfile_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), " . $fld_name . ($is_pc ? "Beg_insDT" : "_insDT") . ", 112) as datetime) >= cast(:InsDate_Range_0 as datetime)";
				$queryParams['InsDate_Range_0'] = $data['InsDate_Range'][0];
			}
			// Пользователь
			if (isset($data['InsDate_Range'][1])) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (cast(convert(varchar(10), EOSectBP.EvnOtherSectionBedProfile_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), " . $fld_name . ($is_pc ? "Beg_insDT" : "_insDT") . ", 112) as datetime) <= cast(:InsDate_Range_1 as datetime)";
				$queryParams['InsDate_Range_1'] = $data['InsDate_Range'][1];
			}

			// Пользователь
			if (isset($data['UpdDate'])) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (cast(convert(varchar(10), EOSectBP.EvnOtherSectionBedProfile_updDT, 112) as datetime) = cast(:UpdDate as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), " . $fld_name . ($is_pc ? "Beg_insDT" : "_updDT") . ", 112) as datetime) = cast(:UpdDate as datetime)";
				$queryParams['UpdDate'] = $data['UpdDate'];
			}
			// Пользователь
			if (isset($data['UpdDate_Range'][0])) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (cast(convert(varchar(10), EOSectBP.EvnOtherSectionBedProfile_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), " . $fld_name . ($is_pc ? "Beg_insDT" : "_updDT") . ", 112) as datetime) >= cast(:UpdDate_Range_0 as datetime)";
				$queryParams['UpdDate_Range_0'] = $data['UpdDate_Range'][0];
			}
			// Пользователь
			if (isset($data['UpdDate_Range'][1])) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (cast(convert(varchar(10), EOSectBP.EvnOtherSectionBedProfile_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), " . $fld_name . ($is_pc ? "Beg_insDT" : "_updDT") . ", 112) as datetime) <= cast(:UpdDate_Range_1 as datetime)";
				$queryParams['UpdDate_Range_1'] = $data['UpdDate_Range'][1];
			}
		}
		else {
			$fld_name = $data['SearchFormType'];

			switch ($data['SearchFormType']) {
				case 'EvnPLDispTeenInspectionPeriod':
				case 'EvnPLDispTeenInspectionPred':
				case 'EvnPLDispTeenInspectionProf':
					$fld_name = 'EvnPLDispTeenInspection';
					break;

				case 'PersonDispOrpPeriod':
				case 'PersonDispOrpPred':
				case 'PersonDispOrpProf':
					$fld_name = 'PersonDispOrp';
					break;

				case 'EvnPLDispDop13Sec':
					$fld_name = 'EvnPLDispDop13';
					break;

				case 'EvnPLDispOrpSec':
					$fld_name = 'EvnPLDispOrp';
					break;

				case 'CmpCloseCard':
					$fld_name = 'CmpCallCard';
					break;

				case 'EvnNotifyHIV':
					$fld_name = 'EvnNotifyBase';
					break;

				case 'EvnNotifyNarko':
					$fld_name = 'EvnNotifyNarco';
					break;

				case 'CrazyRegistry':
				case 'NarkoRegistry':
				case 'NephroRegistry':
				case 'OnkoRegistry':
				case 'PalliatRegistry':
				case 'TubRegistry':
				case 'IPRARegistry':
				case 'HepatitisRegistry':
					$fld_name = 'PersonRegister';
					break;
			}

			// Пользователь
			if (isset($data['InsDate'])) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(" . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) = cast(:InsDate as date)";
				} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
					$filter .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $fld_name . "_insDT else " . $main_alias . "." . $fld_name . "_insDT end as date) = cast(:InsDate as date)";
				} else if ($data['SearchFormType'] == 'EvnPL') {
					$filter .= " and cast(Evn.Evn_insDT as date) = cast(:InsDate as date)";
				} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
					$filter .= " and cast(E.Evn_insDT as date) = cast(:InsDate as date)";
				} else {
					$filter .= " and cast(" . $main_alias . "." . $fld_name . "_insDT as date) = cast(:InsDate as date)";
				}
				$queryParams['InsDate'] = $data['InsDate'];
			}

			// Пользователь
			if (isset($data['InsDate_Range'][0])) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(" . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) >= cast(:InsDate_Range_0 as date)";
				} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
					$filter .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $fld_name . "_insDT else " . $main_alias . "." . $fld_name . "_insDT end as date) >= cast(:InsDate_Range_0 as date)";
				} else if ($data['SearchFormType'] == 'EvnPL') {
					$filter .= " and cast(Evn.Evn_insDT as date) >= cast(:InsDate_Range_0 as date)";
				} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
					$filter .= " and cast(E.Evn_insDT as date) >= cast(:InsDate_Range_0 as date)";
				} else {
					$filter .= " and cast(" . $main_alias . "." . $fld_name . "_insDT as date) >= cast(:InsDate_Range_0 as date)";
				}
				$queryParams['InsDate_Range_0'] = $data['InsDate_Range'][0];
			}

			// Пользователь
			if (isset($data['InsDate_Range'][1])) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(" . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) <= cast(:InsDate_Range_1 as date)";
				} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
					$filter .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $fld_name . "_insDT else " . $main_alias . "." . $fld_name . "_insDT end as date) <= cast(:InsDate_Range_1 as date)";
				} else if ($data['SearchFormType'] == 'EvnPL') {
					$filter .= " and cast(Evn.Evn_insDT as date) <= cast(:InsDate_Range_1 as date)";
				} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
					$filter .= " and cast(E.Evn_insDT as date) <= cast(:InsDate_Range_1 as date)";
				} else {
					$filter .= " and cast(" . $main_alias . "." . $fld_name . "_insDT as date) <= cast(:InsDate_Range_1 as date)";
				}
				$queryParams['InsDate_Range_1'] = $data['InsDate_Range'][1];
			}

			// Пользователь
			if (isset($data['UpdDate'])) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(" . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) = cast(:UpdDate  as date)";
				} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
					$filter .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $fld_name . "_updDT else " . $main_alias . "." . $fld_name . "_updDT end as date) = cast(:UpdDate as date)";
				} else if ($data['SearchFormType'] == 'EvnPL') {
					$filter .= " and cast(Evn.Evn_updDT as date) = cast(:UpdDate as date)";
				} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
					$filter .= " and cast(E.Evn_updDT as date) = cast(:UpdDate as date)";
				} else {
					$filter .= " and cast(" . $main_alias . "." . $fld_name . "_updDT as date) = cast(:UpdDate as date)";
				}
				$queryParams['UpdDate'] = $data['UpdDate'];
			}

			// Пользователь
			if (isset($data['UpdDate_Range'][0])) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(" . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) >= cast(:UpdDate_Range_0 as date)";
				} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
					$filter .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $fld_name . "_updDT else " . $main_alias . "." . $fld_name . "_updDT end as date) >= cast(:UpdDate_Range_0 as date)";
				} else if ($data['SearchFormType'] == 'EvnPL') {
					$filter .= " and cast(Evn.Evn_updDT as date) >= cast(:UpdDate_Range_0 as date)";
				} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
					$filter .= " and cast(E.Evn_updDT as date) >= cast(:UpdDate_Range_0 as date)";
				} else {
					$filter .= " and cast(" . $main_alias . "." . $fld_name . "_updDT as date) >= cast(:UpdDate_Range_0 as date)";
				}
				$queryParams['UpdDate_Range_0'] = $data['UpdDate_Range'][0];
			}

			// Пользователь
			if (isset($data['UpdDate_Range'][1])) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(" . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT as date) <= cast(:UpdDate_Range_1 as date)";
				} else if ($data['SearchFormType'] == 'EvnPLDispDop13Sec') {
					$filter .= " and cast(case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond." . $fld_name . "_updDT else " . $main_alias . "." . $fld_name . "_updDT end as date) <= cast(:UpdDate_Range_1 as date)";
				} else if ($data['SearchFormType'] == 'EvnPL') {
					$filter .= " and cast(Evn.Evn_updDT as date) <= cast(:UpdDate_Range_1 as date)";
				} else if ($data['SearchFormType'] == 'EvnNotifyRegister') {// #132882 Ошибка БД при работе с журналом извещений ВЗН
					$filter .= " and cast(E.Evn_updDT as date) <= cast(:UpdDate_Range_1 as date)";
				} else {
					$filter .= " and cast(" . $main_alias . "." . $fld_name . "_updDT as date) <= cast(:UpdDate_Range_1 as date)";
				}
				$queryParams['UpdDate_Range_1'] = $data['UpdDate_Range'][1];
			}
		}
		/*
		  if ( count($queryParams) <= 2 ) {
		  return array('success' => false, 'Error_Msg' => 'Необходимо задать хотя бы 1 параметр');
		  }
		 */
		if ($data['SearchFormType'] == 'EvnPLDispDop13') {


			$joinEPLDD13 = "left";
			if (!empty($filterEPLDD13)) {
				$joinEPLDD13 = "inner";
			}
			$ddjoin = "";
			if ($data['PersonPeriodicType_id'] == 2) {
				$ddjoin = "
					{$joinEPLDD13} join [v_EvnPLDispDop13] [EvnPLDispDop13] with (nolock) on EvnPLDispDop13.Server_id = PS.Server_id and EvnPLDispDop13.PersonEvn_id = PS.PersonEvn_id and [EvnPLDispDop13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EvnPLDispDop13.DispClass_id,1) = 1 and YEAR(EvnPLDispDop13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
				";
			} else {
				$ddjoin = "
					{$joinEPLDD13} join [v_EvnPLDispDop13] [EvnPLDispDop13] with (nolock) on [PS].[Person_id] = [EvnPLDispDop13].[Person_id] and [EvnPLDispDop13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EvnPLDispDop13.DispClass_id,1) = 1 and YEAR(EvnPLDispDop13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
				";
			}

			if (allowPersonEncrypHIV($data['session'])) {
				$PersonFields = "
					case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname,
					case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname,
					case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname,
					case when PEH.PersonEncrypHIV_id is null then PS.Person_Birthday else null end as Person_Birthday
				";
				$ddjoin .= "
					left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
				";
			} else {
				$PersonFields = "
					RTRIM(PS.Person_Surname) as Person_Surname,
					RTRIM(PS.Person_Firname) as Person_Firname,
					RTRIM(PS.Person_Secname) as Person_Secname,
					PS.Person_Birthday
				";
			}

			$wherewith = array();
			$wherewith[] = $filter;
			$queryWithAdditionalJoin = array();
			//$queryWithAdditionalJoin[] = ' inner join v_PersonState PS with (nolock) on PS.Person_id = EvnPLDispDop13.Person_id';
			$queryWithAdditionalJoin[] = " {$joinDopDispSecond} apply(
						select top 1
							EPLDD13_SEC.EvnPLDispDop13_id,
							EPLDD13_SEC.EvnPLDispDop13_isPaid,
							EPLDD13_SEC.EvnPLDispDop13_IsTransit,
							EPLDD13_SEC.EvnPLDispDop13_setDate,
							EPLDD13_SEC.EvnPLDispDop13_disDate,
							EPLDD13_SEC.EvnPLDispDop13_consDT,
							EPLDD13_SEC.HealthKind_id,
							ISNULL(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
							HK_SEC.HealthKind_Name,
							EPLDD13_SEC.EvnPLDispDop13_insDT,
							EPLDD13_SEC.EvnPLDispDop13_updDT,
							EPLDD13_SEC.pmUser_insID,
							EPLDD13_SEC.pmUser_updID
						from
							v_EvnPLDispDop13 (nolock) EPLDD13_SEC
							left join v_HealthKind HK_SEC with (nolock) on HK_SEC.HealthKind_id = EPLDD13_SEC.HealthKind_id
						where
							EPLDD13_SEC.EvnPLDispDop13_fid = EvnPLDispDop13.EvnPLDispDop13_id
							{$filterDopDispSecond}
					) DopDispSecond -- данные по 2 этапу";
			//var_dump($queryWithAdditionalJoin);die;
			$queryWithArray[] = "
						EvnPLDispDop13Top as (
							select
									EvnPLDispDop13.EvnPLDispDop13_id,
									EvnPLDispDop13.EvnPLDispDop13_IsEndStage,
									EvnPLDispDop13.EvnPLDispDop13_isMobile,
									EvnPLDispDop13.EvnPLDispDop13_IsTransit,
									EvnPLDispDop13.HealthKind_id,
									EvnPLDispDop13.EvnPLDispDop13_IsArchive,
									PS.Person_id,
									PS.Server_id,
									EvnPLDispDop13.PersonEvn_id,
									EvnPLDispDop13.Lpu_id,
									EvnPLDispDop13.EvnPLDispDop13_consDT,
									EvnPLDispDop13.EvnPLDispDop13_disDate,
									EvnPLDispDop13.EvnPLDispDop13_IsRefusal,
									EvnPLDispDop13.EvnPLDispDop13_IsTwoStage,
									DopDispSecond.EvnPLDispDop13_id as DopDispSecond_EvnPLDispDop13_id,
									DopDispSecond.HealthKind_id as DopDispSecond_HealthKind_id,
									DopDispSecond.EvnPLDispDop13_IsEndStage as DopDispSecond_EvnPLDispDop13_IsEndStage,
									DopDispSecond.EvnPLDispDop13_consDT as DopDispSecond_EvnPLDispDop13_consDT,
									DopDispSecond.EvnPLDispDop13_disDate as DopDispSecond_EvnPLDispDop13_disDate,
									PS.UAddress_id,
									PS.PAddress_id,
									{$PersonFields}
							from v_PersonState PS with(nolock)
							" . $ddjoin . "
							--v_EvnPLDispDop13 EvnPLDispDop13

								" . implode(' ', $queryWithAdditionalJoin) . "
							where
								" . implode(' and ', $wherewith) . "
						)
					";

			$query .= "
				-- end from
			where
				-- where
					(1=1)
				-- end where
		";
		} else {
            if (!empty($data['Part_of_the_study']) && $data['Part_of_the_study'] && !empty($data['UslugaComplex_id'])) {
                $query .= "
					-- end from
				where
					-- where
					". $filter . $evnUC_filter . $pac_filter  . $uc_filter.  $filter .  "
					-- end where
			";
            }
            else {
                $set_evnUC_filter = $data['SearchFormType'] == 'EvnUslugaPar' ? $evnUC_filter : "";
                $query .= "
					-- end from
				where
					-- where
					". $filter . $set_evnUC_filter  .  $pac_filter . "
					-- end where
			";
            }

		}
		switch ($data['SearchFormType']) {
			case 'CmpCallCard':
			case 'CmpCloseCard':
				$query .= "
					order by
						-- order by
						PS.Person_Surname,
						CCC.Person_SurName,
						PS.Person_Firname,
						CCC.Person_FirName,
						PS.Person_Secname,
						CCC.Person_SecName
						-- end order by
				";
				break;

			case 'EvnPL':
				$query .= "
					order by
						-- order by
						EPL.EvnPL_id
						-- end order by
				";
				break;

			case 'EvnVizitPL':
				if ($dbf == true)
					$query .= "
						order by
							-- order by
							5
							-- end order by
					";
				else
					$query .= "
						order by
							-- order by
							EVizitPL.EvnVizitPL_id DESC
							-- end order by
					";
				break;

			case 'EvnPLStom':
				$query .= "
					order by
						-- order by
						EPLS.EvnPLStom_id
						-- end order by
				";
				break;

			case 'EvnVizitPLStom':
				$query .= "
					order by
						-- order by
						EVPLS.EvnVizitPLStom_setDate DESC
						-- end order by
				";
				break;

			case 'EvnPS':
				if ($print === true) {
					$query .= "
						order by
							-- order by
							PS.Person_SurName,
							PS.Person_FirName,
							PS.Person_SecName
							-- end order by
					";
				}
                else {
					$query .= "
						order by
							-- order by
							EPS.EvnPS_id
							-- end order by
					";
				}
				break;

			case 'EvnSection':
				if ($print === true) {
					$query .= "
						order by
							-- order by
							PS.Person_SurName,
							PS.Person_FirName,
							PS.Person_SecName
							-- end order by
					";
				} else {
					$query .= "
						order by
							-- order by
							EPS.EvnPS_id
							-- end order by
					";
				}
				break;

			case 'EvnDtpWound':
				$query .= "
					order by
						-- order by
						EDW.EvnDtpWound_id
						-- end order by
				";
				break;

			case 'EvnDtpDeath':
				$query .= "
					order by
						-- order by
						EDD.EvnDtpDeath_id
						-- end order by
				";
				break;

			case 'EvnUslugaPar':
				$query .= "
					order by
						-- order by
						EUP.EvnUslugaPar_id
						-- end order by
				";
				break;

			case 'EvnRecept':
				$query .= "
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						ER.EvnRecept_Num
						-- end order by
				";
				break;

			case 'EvnReceptGeneral':
				$query .= "
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						ERG.EvnReceptGeneral_Num
						-- end order by
				";
				break;

			case 'PersonDopDisp':
				$query .= "
					group by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						DD.PersonDopDisp_id,
						PS.Person_id,
						PS.Server_id,
						epldd.Server_id,
						PS.PersonEvn_id,
						epldd.PersonEvn_id,
						Person_Birthday,
						Sex.Sex_Name,
						PS.Polis_Ser,
						PS.Polis_Num,
						okved1.Okved_Name,
						org1.Org_OGRN,
						astat1.KLArea_Name,
						astat2.KLArea_Name,
						addr1.Address_Nick,
						otherddlpu.Lpu_Nick
						" . (allowPersonEncrypHIV($data['session']) ? ",PEH.PersonEncrypHIV_id, PEH.PersonEncrypHIV_Encryp" : "") . "
					-- end where
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;

			case 'PersonDopDispPlan':
				$query .= "
					group by
						PS.Person_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						Sex.Sex_Name,
						PS.Person_Birthday,
						IsPersonDopDispPassed.EvnPLDisp_setDate,
						IsPersonDopDispPassed.EvnPLDisp_disDate
					-- end where
					order by
						-- order by
						{$orderby}
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;

			case 'PersonDispOrp':
			case 'PersonDispOrpPeriod':
			case 'PersonDispOrpPred':
			case 'PersonDispOrpProf':
			case 'PersonDispOrpOld':
			case 'EvnPLDispDop':
			case 'EvnPLDispDop13Sec':
			case 'EvnPLDispProf':
			case 'EvnPLDispScreen':
			case 'EvnPLDispScreenChild':
			case 'EvnPLDispDopStream':
			case 'EvnPLDispTeen14':
			case 'EvnPLDispTeen14Stream':
			case 'EvnPLDispOrp':
			case 'EvnPLDispOrpOld':
			case 'EvnPLDispOrpSec':
			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
			case 'EvnPLDispOrpStream':
			case 'EvnPLDispMigrant':
			case 'EvnPLDispDriver':
			case 'PersonDisp':
			case 'PersonCardStateDetail':
			case 'WorkPlacePolkaReg':
			case 'PersonCallCenter':
			case 'PersonCard':
			case 'PersonPrivilegeWOW':
			case 'EvnPLWOW':
				$query .= "
					order by
						-- order by
						{$orderby}
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;
			case 'EvnPLDispDop13':
				$query .= "
					order by
						-- order by
							EPLDD13.Person_SurName,
							EPLDD13.Person_FirName,
							EPLDD13.Person_SecName
						-- end order by
				";
				break;
			case 'EvnNotifyHepatitis':
				$query .= "
					order by
						-- order by
						ENH.EvnNotifyHepatitis_setDT DESC
						-- end order by
				";
				break;
			case 'EvnOnkoNotify':
				$query .= "
					order by
						-- order by
						EON.EvnOnkoNotify_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyRegister':
				$query .= "
					order by
						-- order by
						E.Evn_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyOrphan':
				$query .= "
					order by
						-- order by
						ENO.EvnNotifyOrphan_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyCrazy':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyCrazy_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyNarko':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyNarco_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyTub':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyTub_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyNephro':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyNephro_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyProf':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyProf_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyHIV':
				$query .= "
					order by
						-- order by
						ENB.EvnNotifyBase_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyVener':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyVener_setDT DESC
						-- end order by
				";
				break;
			case 'PalliatNotify':
				$query .= "
					order by
						-- order by
						ENB.EvnNotifyBase_setDT DESC
						-- end order by
				";
				break;

			case 'OnkoRegistry':
				$query .= "
					group by
						PR.PersonRegister_id,
						PR.Lpu_iid,
						PR.MedPersonal_iid,
						PR.EvnNotifyBase_id,
						EONN.EvnOnkoNotifyNeglected_id,	
						MOV.MorbusOnkoVizitPLDop_id,
						MOL.MorbusOnkoLeave_id,
						MO.MorbusOnko_id,
						M.Morbus_id,
						PS.Person_id,
						PS.Server_id,
						PS.PersonEvn_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						PS.Person_Birthday,
						Lpu.Lpu_Nick,
						Diag.Diag_id,
						Diag.diag_FullName,
						PROUT.PersonRegisterOutCause_id,
						PROUT.PersonRegisterOutCause_Name,
						OnkoDiag.OnkoDiag_Code,
						OnkoDiag.OnkoDiag_Name,
						MO.MorbusOnko_IsMainTumor,
						TumorStage.TumorStage_Name,
						MO.MorbusOnko_setDiagDT,
						PR.PersonRegister_setDate,
						PR.PersonRegister_disDate,
						MP.Person_Fio
					-- end where
					order by
						-- order by
						PR.PersonRegister_setDate DESC,
						PR.PersonRegister_id
						-- end order by
				";
				break;

			case 'BskRegistry':
				$query .= '
					ORDER BY
					-- order by
						BSKRegistry_setDate DESC
					-- end order by';
				break;
			case 'IPRARegistry':
			case 'ECORegistry':
			case 'HepatitisRegistry':
			case 'OrphanRegistry':
			case 'ACSRegistry':
			case 'CrazyRegistry':
			case 'NarkoRegistry':
			case 'TubRegistry':
			case 'NephroRegistry':
			case 'EndoRegistry':
			case 'IBSRegistry':
			case 'ProfRegistry':
			case 'FmbaRegistry':
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
			case 'HIVRegistry':
			case 'VenerRegistry':
			case 'PalliatRegistry':
			case 'PersonRegisterBase':
			case 'GeriatricsRegistry':
			case 'GibtRegistry':
				$query .= "
					order by
						-- order by
						PR.PersonRegister_setDate DESC,
						PR.PersonRegister_id
						-- end order by
				";
				break;
			case 'ONMKRegistry':
				$query .= "
					order by
						-- order by
						ONMKR.ONMKRegistry_EvnDT DESC,
						PR.PersonRegister_id
						-- end order by
				";
				break;
			case 'ReabRegistry':
			case 'AdminVIPPerson':
			case 'ZNOSuspectRegistry':
				$query .= "
					order by
						-- order by
						Person_Surname asc,
						Person_Firname asc,
						Person_Secname asc
						-- end order by
				";
				break;

			case 'EvnERSBirthCertificate':
				$query .= "
					order by
						-- order by
						ers.EvnErsBirthCertificate_PregnancyRegDate desc
						-- end order by
				";
				break;

			case 'EvnInfectNotify':
				$query .= "
					group by
						EIN.EvnInfectNotify_id,
						EIN.EvnInfectNotify_insDT,
						PS.Person_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						PS.Person_Birthday,
						Lpu.Lpu_Nick,
						pc.lpu_id,
						Diag.diag_FullName,
						Diag1.diag_FullName
					-- end where
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;

			case 'PersonPrivilege':
				$query .= "
					order by
						-- order by
						Person_Surname,
						Person_Firname,
						Person_Secname,
						PP.PersonPrivilege_id
						-- end order by
				";
				break;
			case 'HTMRegister':
				$query .= "
					order by
						-- order by
							hr.HTMRegister_Number ASC --r.Register_setDate DESC
						-- end order by
				";
				break;
			case 'RzhdRegistry':
				$query .= "
					order by
						-- order by
							R.Register_setDate DESC
						-- end order by
				";
				break;

			case 'ReanimatRegistry':
				//BOB - 23.01.2018
				$query .= "
					order by
						-- order by
						RR.ReanimatRegister_IsPeriodNow  desc,
						ERP.selrow desc,
						RR.ReanimatRegister_setDate DESC,--PR.PersonRegister_setDate DESC,
						RR.ReanimatRegister_id   --PR.PersonRegister_id
						-- end order by
				";
				//BOB - 23.01.2018
				break;
			case 'RegisterSixtyPlus':
				$query .= "
					order by
						-- order by
						(case when (RPlus.RegisterSixtyPlus_IMTMeasure is null or cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 25.0 ) then 0
								 when (cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 25.0 and cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) < 30.0) then 1
								 when cast(RPlus.RegisterSixtyPlus_IMTMeasure as float) >= 30.0 then 2 end 
								 +
							case when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 5.1) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 5.1 and cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) < 7.1 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.') as float) >= 7.1 then 2 end
								 +
							case when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 6.2) then 0
								 when (cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 6.2 and cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) < 7.0 ) then 1 
								 when cast(replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.') as float) >= 7.0 then 2 end
								) desc,
						rg.LpuRegion_Name,
						LpuRegionFap.LpuRegion_Name
						-- end order by
				";
				break;
			case 'SportRegistry':
				$query .= "
					order by
						-- order by
						SRUMO.SportRegisterUMO_UMODate desc
						-- end order by";
				break;
		}
		$response = array();

		// Собираем все, что добавлено в $queryWithArray, и прицепляем в общий запрос
		if (count($queryWithArray) > 0) {
			$query = "
				-- addit with
				with " . implode(', ', $queryWithArray) . "
				-- end addit with
			" . $query;
		}

		$variables = "
			--variables
			" . implode(' ', $variablesArray) . "
			--end variables
		";

		if (strtolower($data['onlySQL']) == 'on' && isSuperAdmin()) {
			// Цепляем переменные
			$query = $variables . $query;
			//echo getDebugSQL($query, $queryParams); exit;\
			echo getDebugSQL($query, $queryParams);
			return false;
		}

		if ($getCount == true) {
			$get_count_query = getCountSQLPH($query);
			// приходится так делать из-за группировки
			if (in_array($data['SearchFormType'], array("EvnInfectNotify", "PersonDopDisp"))) {
				$get_count_query = getCountSQLPH($query, '*', '', '', true);
			}

			// Цепляем переменные
			$get_count_query = $variables . $get_count_query;

			$get_count_result = $this->db->query($get_count_query, $queryParams);
			$res = $get_count_result->result('array'); // #138061
			//т.к. в $get_count_result может быть много записей и выводятся по-разному, то:
			$cnt = (count($res) == 1) ? $res[0]['cnt'] : count($res); //если 1 запись, то брать из [0]['cnt'], если много, то выводить количество

			if (is_object($get_count_result)) {
				//$response['totalCount'] = $get_count_result->result('array');
				//$response['totalCount'] = $response['totalCount'][0]['cnt'];
				$response['totalCount'] = $cnt;
				$response['Error_Msg'] = '';
			} else {
				return false;
			}
		} else {

			// Цепляем переменные
			$query = $variables . $query;
			// echo getDebugSQL($query, $queryParams); exit;

			if ($print === true) {

				$result = $this->db->query($query, $queryParams);

				if (is_object($result)) {
					$response['data'] = $result->result('array');
				} else {
					return false;
				}
			} else if ($dbf === true) {
				if (
						$data['SearchFormType'] == "EvnDiag" ||
						substr($data['SearchFormType'], 0, 3) == 'Kvs' ||
						$data['SearchFormType'] == 'EPLPerson' ||
						$data['SearchFormType'] == 'EvnPL' ||
						$data['SearchFormType'] == 'EvnVizitPL' ||
						$data['SearchFormType'] == 'EvnUsluga' ||
						$data['SearchFormType'] == 'EvnAgg' ||
						$data['SearchFormType'] == 'EPLStomPerson' ||
						$data['SearchFormType'] == 'EvnPLStom' ||
						$data['SearchFormType'] == 'EvnVizitPLStom' ||
						$data['SearchFormType'] == 'EvnUslugaStom' ||
						$data['SearchFormType'] == 'EvnAggStom'
				) {
					$fld_name = 'EvnDiagPS_id';
					switch ($data['SearchFormType']) {
						case 'KvsPerson': $fld_name = $PS_prefix . ($data['kvs_date_type'] == 2 ? '.PersonEvn_id' : '.Person_id');
							break;
						case 'KvsPersonCard': $fld_name = 'PC.PersonCard_id';
							break;
						case 'KvsEvnDiag': $fld_name = 'EvnDiagPS_id';
							break;
						case 'KvsEvnPS': $fld_name = 'EPS.EvnPS_id';
							break;
						case 'KvsEvnSection': $fld_name = 'ESEC.EvnSection_id';
							break;
						case 'KvsNarrowBed': $fld_name = 'ESNB.EvnSectionNarrowBed_id';
							break;
						case 'KvsEvnUsluga': $fld_name = 'EU.EvnUsluga_id';
							break;
						case 'KvsEvnUslugaOB': $fld_name = 'EUOB.EvnUslugaOperBrig_id';
							break;
						case 'KvsEvnUslugaAn': $fld_name = 'EUOA.EvnUslugaOperAnest_id';
							break;
						case 'KvsEvnUslugaOsl': $fld_name = 'EA.EvnAgg_id';
							break;
						case 'KvsEvnDrug': $fld_name = 'ED.EvnDrug_id';
							break;
						case 'KvsEvnLeave': $fld_name = 'ESEC.EvnSection_id';
							break;
						case 'KvsEvnStick': $fld_name = 'EST.EvnStick_id';
							break;
						case 'EPLPerson': $fld_name = $PL_prefix . ($data['epl_date_type'] == 2 ? '.PersonEvn_id' : '.Person_id');
							break;
						case 'EPLStomPerson': $fld_name = $PLS_prefix . ($data['eplstom_date_type'] == 2 ? '.PersonEvn_id' : '.Person_id');
							break;
						case 'EvnPL': $fld_name = 'EPL.EvnPL_id';
							break;
						case 'EvnPLStom': $fld_name = 'EPLS.EvnPLStom_id';
							break;
						case 'EvnVizitPL': $fld_name = 'EVizitPL.EvnVizitPL_id';
							break;
						case 'EvnVizitPLStom': $fld_name = 'EVPLS.EvnVizitPLStom_id';
							break;
						case 'EvnUsluga': $fld_name = 'EvnUsluga.EvnUsluga_id';
							break;
						case 'EvnUslugaStom': $fld_name = 'EvnUsluga.EvnUslugaStom_id';
							break;
						case 'EvnAgg':
						case 'EvnAggStom':
							$fld_name = 'EvnAgg.EvnAgg_id';
							break;
						default: $fld_name = 'EPS.EvnPs_id';
					}
					$get_count_query = getCountSQLPH($query, '', 'distinct ' . $fld_name);
				} else {
					$get_count_query = getCountSQLPH($query);
					// приходится так делать из-за группировки
					if (in_array($data['SearchFormType'], array("EvnInfectNotify", "PersonDopDisp"))) {
						$get_count_query = getCountSQLPH($query, '*', '', '', true);
					}
				}

				$get_count_result = $this->db->query($get_count_query, $queryParams);
				if (!is_object($get_count_result)) {
					return false;
				}

				$records_count = $get_count_result->result('array');
				$cnt = $records_count[0]['cnt'];

				/* if ($data['SearchFormType'] == 'KvsEvnLeave') {
				  echo "<pre>".getDebugSQL($query, $queryParams);
				  die;
				  } */
				//echo "<pre>".getDebugSQL($get_count_query, $queryParams);


				$result = $this->db->query($query, $queryParams);

				if (is_object($result)) {
					$response = array('data' => $result, 'totalCount' => $cnt);
				} else {
					return false;
				}
			} else {
				if ($data['start'] >= 0 && $data['limit'] >= 0) {
					$distinct = in_array($data['SearchFormType'], array("EvnPL","EvnVizitPL"))
						? "DISTINCT"
						: "";
					$limit_query = getLimitSQLPH($query, $data['start'], $data['limit'], $distinct);
					//die(getDebugSQL($limit_query, $queryParams));

                    //сделано это из-за проблем архетектуры, при попытке написания запроса через union роисходило копирование частей запроса поэтому пришлось их вырезать вот таким способом. AMAZING одним словом.
                    if (!empty($data['Part_of_the_study']) && $data['Part_of_the_study'] && !empty($data['UslugaComplex_id'])) {
                        $start = stripos($limit_query, 'UNION ALL'); //первое вхождение UNION ALL
                        $end = strrpos ($limit_query, 'UNION ALL'); //последнее вхождение UNION ALL
                        $str_result = substr_replace ($limit_query, "", $start, $end - $start ); //результирующая строка
                        $limit_query = $str_result;
                    }
                    
					if ($this->isDebug) {
						$debug_sql = getDebugSql($limit_query, $queryParams);
					}
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
			}
		}
		//var_dump($response);
		//var_dump($data['limit']);

		if (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
			if (!empty($response['totalCount'])) {
				$response['totalCount'] = $response['totalCount'] + $data['archiveStart'];
			}
		}

		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));
		return $response;
	}

}

