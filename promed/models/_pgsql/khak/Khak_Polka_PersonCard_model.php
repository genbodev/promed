<?php
/**
 * Polka_PersonCard_model - модель, для работы с таблицей Personcard
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Valery Bondarev
 * @version      01.2020
 */
require_once(APPPATH . 'models/_pgsql/Polka_PersonCard_model.php');

class Khak_Polka_PersonCard_model extends Polka_PersonCard_model
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}


	/**
	 *    Список прикрепленного населения к указанной СМО на указанную дату
	 */
	function loadAttachedList($data)
	{
		$filterList = array();
		$queryParams = array();

		if (!empty($data['AttachLpu_id'])) {
			$filterList[] = 'PC.Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['AttachLpu_id'];
		}

		$query = "
			select
				L.Lpu_f003mcod as \"CODE_MO\", -- Реестровый код МО
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				PC.PersonCard_Code as \"ID_PAC\", -- Номер истории болезни
				rtrim(upper(PS.Person_SurName)) as \"FAM\",  -- Фамилия
				rtrim(upper(PS.Person_FirName)) as \"IM\", -- Имя
				COALESCE(rtrim(upper(case when replace(PS.Person_Secname, ' ', '') = '---' or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as \"OT\",	-- Отчество
				PS.Sex_id as \"W\", -- Пол застрахованного
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"DR\", -- Дата рождения застрахованного
				rtrim(PT.PolisType_CodeF008) as \"VPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as \"SPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as \"NPOLIS\",
				PS.Person_EdNum as \"ENP\",
				PC.PersonCard_id as \"NUM_REG\",
				to_char(PC.PersonCard_begDate, 'yyyy-mm-dd') as \"DATA_REG\",
				L.Lpu_f003mcod as \"MCOD\",
				case
					when LR.LpuRegionType_SysNick like 'ter' then 1
					when LR.LpuRegionType_SysNick like 'ped' then 2
					when LR.LpuRegionType_SysNick like 'vop' then 4
				end as \"TYPE_U\",
				LR.LpuRegion_Name as \"NUM_U\",
				case when MP.Person_Snils is null then '0'
					else SUBSTRING(MP.Person_Snils, 1, 3)||'-'||SUBSTRING(MP.Person_Snils, 4, 3)||'-'||SUBSTRING(MP.Person_Snils, 7, 3)||'-'||SUBSTRING(MP.Person_Snils, 10, 2)
				end as \"V_SNILS\",
				case when MP.MedPersonal_TabCode is null then '0' else MP.MedPersonal_TabCode end as \"VKOD\",
				LS.LpuSection_Code as \"PDRKOD\",
				1 as \"REASON\"
			from
				v_PersonState PS
				inner join v_PersonCard PC on PC.Person_id = PS.Person_id
				inner join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				inner join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
				inner join v_Lpu L on L.Lpu_id = PC.Lpu_id
				inner join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				inner join v_PolisType PT on PT.PolisType_id = PLS.PolisType_id
				inner join v_OMSSprTerr OST on OST.OMSSprTerr_id = PLS.OMSSprTerr_id
				left join lateral (
					select
						PS.Person_Snils,
						MSF.MedPersonal_TabCode
					from
						v_MedStaffRegion MSR
						inner join v_MedStaffFact MSF on MSF.MedPersonal_id = MSR.MedPersonal_id
						inner join v_PersonState PS on PS.Person_id = MSF.Person_id
					where
						MSR.LpuRegion_id = LR.LpuRegion_id and MSF.LpuSection_id = LS.LpuSection_id
						and (MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate <= dbo.tzGetDate())
						and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > dbo.tzGetDate())
					order by
						MSF.MedStaffFact_Stavka desc,
						COALESCE(MSF.MedPersonal_TabCode,0) desc
					limit 1
				) MP on true
			where
				-- https://redmine.swan.perm.ru/issues/27263
				PC.LpuAttachType_id = 1 -- 1. По типу прикрепления - Основное
				and (PC.CardCloseCause_id is null or PC.CardCloseCause_id <> 4)
				and (PLS.Polis_endDate is null or PLS.Polis_endDate > dbo.tzGetDate()) -- 3. Есть действующий полис
				and PS.Person_deadDT is null -- 3. .../живой;
				and OST.OMSSprTerr_Code = 1 -- 4. Застрахован на территории РХ
				and PT.PolisType_CodeF008 is not null
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
		";
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			return false;
		}

		return $result;
	}
}