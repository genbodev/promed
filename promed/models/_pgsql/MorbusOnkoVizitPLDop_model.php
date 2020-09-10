<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Талон дополнений на онкобольного
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      12.2014
 *
 * @property CI_DB_driver $db
 */
class MorbusOnkoVizitPLDop_model extends swPgModel
{
	/*
	private $MorbusOnkoVizitPLDop_id;//идентификатор
	private $EvnVizit_id;//лечение в поликлинике
	private $Diag_id;//диагноз
	private $OnkoRegOutType_id;//причина снятия с учета
	private $OnkoStatusYearEndType_id;//состояние на конец отчетного года
	private $OnkoInvalidType_id;//инвалидность по основному заболеванию
	private $OnkoDiag_id;//морфологический тип опухоли. (Гистология опухоли)
	private $OnkoT_id;//T
	private $OnkoN_id;//N
	private $OnkoM_id;//M
	private $TumorStage_id;//Стадия опухолевого процесса
	private $MorbusOnkoVizitPLDop_IsTumorDepoUnknown;//локализация отдаленных метастазов: Неизвестна
	private $MorbusOnkoVizitPLDop_IsTumorDepoLympha;//локализация отдаленных метастазов: Отдаленные лимфатические узлы
	private $MorbusOnkoVizitPLDop_IsTumorDepoBones;//локализация отдаленных метастазов: Кости
	private $MorbusOnkoVizitPLDop_IsTumorDepoLiver;//локализация отдаленных метастазов: Печень
	private $MorbusOnkoVizitPLDop_IsTumorDepoLungs;//локализация отдаленных метастазов: Легкие и/или плевра
	private $MorbusOnkoVizitPLDop_IsTumorDepoBrain;//локализация отдаленных метастазов: Головной мозг
	private $MorbusOnkoVizitPLDop_IsTumorDepoSkin;//локализация отдаленных метастазов: Кожа
	private $MorbusOnkoVizitPLDop_IsTumorDepoKidney;//локализация отдаленных метастазов: Почки
	private $MorbusOnkoVizitPLDop_IsTumorDepoOvary;//локализация отдаленных метастазов: Яичники
	private $MorbusOnkoVizitPLDop_IsTumorDepoPerito;//локализация отдаленных метастазов: Брюшина
	private $MorbusOnkoVizitPLDop_IsTumorDepoMarrow;//локализация отдаленных метастазов: Костный мозг
	private $MorbusOnkoVizitPLDop_IsTumorDepoOther;//локализация отдаленных метастазов: Другие органы
	private $MorbusOnkoVizitPLDop_IsTumorDepoMulti;//локализация отдаленных метастазов: Множественные
	private $MorbusOnkoVizitPLDop_deadDT;//дата смерти
	private $Diag_did;//диагноз причины смерти
	private $MorbusOnkoVizitPLDop_deathCause;//причина смерти
	private $AutopsyPerformType_id;//аутопсия
	private $TumorAutopsyResultType_id;//результат аутопсии
	private $MorbusOnkoVizitPLDop_setDT;//дата заполнения
	private $MedPersonal_id;//врач
	private $MorbusOnkoBasePersonState_id;//состояние пациента
	private $OnkoLateComplTreatType_id;//Поздние осложнения лечения
	private $OnkoTumorStatusType_id;//Состояние опухолевого процесса при осмотре
	private $OnkoDiagConfType_id;//метод подтверждения диагноза
	*/

	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_VIEW_DATA,
		]);
	}

	/**
	 * Cохранение талона дополнений
	 * Должно происходить только после сохранения специфики из посещения
	 * Принимает 3 обязательных параметра: EvnVizitPL_id, MorbusOnko_id и pmUser_id
	 * @param $data
	 * @param null $evnclass
	 * @return array
	 * @throws Exception
	 */
	function save($data, $evnclass = null)
	{
		$queryParams = [
			"Evn_pid" => $data["Evn_pid"],
			"MorbusOnko_id" => $data["MorbusOnko_id"],
			"EvnDiagPLSop_id" => $data["EvnDiagPLSop_id"],
		];
		if (empty($evnclass)) {
			$query = "
				select EvnClass_SysNick
				from v_Evn
				where Evn_id = :Evn_pid
			";
			$evnclass = $this->getFirstResultFromQuery($query, $queryParams);
		}
		// Перестраховка на всякий случай
		if (empty($evnclass) || !in_array($evnclass, ["EvnVizitPL", "EvnVizitDispDop"])) {
			$evnclass = "EvnVizitPL";
		}
        $spdiag = $evnclass == 'EvnVizitDispDop' ? 'EPLDD13.Diag_spid' : 'Evn.Diag_spid';
		$selectString = "
			T.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
			Evn.{$evnclass}_id as \"{$evnclass}_id\",
			coalesce(ED.Diag_id, {$spdiag}, Evn.Diag_id) as \"Diag_id\",
			to_char(Evn.{$evnclass}_setDT, '{$this->dateTimeForm120}') as \"MorbusOnkoVizitPLDop_setDT\",
			Evn.MedPersonal_id as \"MedPersonal_id\",
			MOB.OnkoInvalidType_id as \"OnkoInvalidType_id\",
			to_char(MOB.MorbusOnkoBase_deadDT, '{$this->dateTimeForm120}') as \"MorbusOnkoVizitPLDop_deadDT\",
			MOB.MorbusOnkoBase_deathCause as \"MorbusOnkoVizitPLDop_deathCause\",
			MOBPS.MorbusOnkoBasePersonState_id as \"MorbusOnkoBasePersonState_id\",
			MO.OnkoLateComplTreatType_id as \"OnkoLateComplTreatType_id\",
			MO.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\",
			MOBLCT.MorbusOnkoBaseLateComplTreat_id as \"MorbusOnkoBaseLateComplTreat_id\"
		";
		$fromString = "
			v_{$evnclass} Evn
			inner join v_MorbusOnko MO on MO.MorbusOnko_id = :MorbusOnko_id
			inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
			inner join v_MorbusOnkoBase MOB on MOB.MorbusBase_id = M.MorbusBase_id
			left join v_MorbusOnkoVizitPLDop T on T.EvnVizit_id = Evn.{$evnclass}_id and coalesce(T.EvnDiagPLSop_id, 0) = coalesce(CAST(:EvnDiagPLSop_id as bigint), 0)
			left join v_EvnDiag ED on ED.EvnDiag_id = :EvnDiagPLSop_id
			left join v_EvnPLDispDop13 EPLDD13 on EPLDD13.EvnPLDispDop13_id = Evn.{$evnclass}_pid
			left join lateral (
				select MorbusOnkoBasePersonState_id
				from v_MorbusOnkoBasePersonState S
				where MOB.MorbusOnkoBase_id = S.MorbusOnkoBase_id
				order by S.MorbusOnkoBasePersonState_insDT desc
			    limit 1
			) as MOBPS on true
			left join lateral (
				select MorbusOnkoBaseLateComplTreat_id
				from v_MorbusOnkoBaseLateComplTreat Tr
				where MOB.MorbusOnkoBase_id = Tr.MorbusOnkoBase_id
				order by Tr.MorbusOnkoBaseLateComplTreat_insDT desc
			    limit 1
			) as MOBLCT on true
		";
		$whereString = "Evn.{$evnclass}_id = :Evn_pid";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		/**@var CI_DB_result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Получение данных для талона дополнений. Ошибка запроса к БД");
		}
		$ra = $result->result("array");
		if (empty($ra) || !is_array($ra[0]) || empty($ra[0])) {
			throw new Exception("Получение данных для талона дополнений. Данные не получены");
		}
		foreach ($ra[0] as $key => $val) {
			$data[$key] = $val;
		}
		$procedure = (isset($data["MorbusOnkoVizitPLDop_id"])) ? "p_MorbusOnkoVizitPLDop_upd" : "p_MorbusOnkoVizitPLDop_ins";
		$queryParams = [
			"MorbusOnkoVizitPLDop_id" => $data["MorbusOnkoVizitPLDop_id"],
			"EvnVizit_id" => $data["Evn_pid"],
			"Diag_id" => $data["Diag_id"],
			"MorbusOnkoVizitPLDop_setDT" => $data["MorbusOnkoVizitPLDop_setDT"],
			"MedPersonal_id" => $data["MedPersonal_id"],
			"OnkoRegOutType_id" => $data["OnkoRegOutType_id"],
			"OnkoInvalidType_id" => $data["OnkoInvalidType_id"],
			"OnkoDiag_id" => $data["OnkoDiag_mid"],
			"OnkoDiag_mid" => $data["OnkoDiag_mid"],
			"OnkoT_id" => $data["OnkoT_id"],
			"OnkoN_id" => $data["OnkoN_id"],
			"OnkoM_id" => $data["OnkoM_id"],
			"TumorStage_id" => $data["TumorStage_id"],
			"OnkoT_fid" => $data["OnkoT_fid"],
			"OnkoN_fid" => $data["OnkoN_fid"],
			"OnkoM_fid" => $data["OnkoM_fid"],
			"TumorStage_fid" => $data["TumorStage_fid"],
			"MorbusOnkoVizitPLDop_IsTumorDepoUnknown" => $data["MorbusOnko_IsTumorDepoUnknown"],
			"MorbusOnkoVizitPLDop_IsTumorDepoLympha" => $data["MorbusOnko_IsTumorDepoLympha"],
			"MorbusOnkoVizitPLDop_IsTumorDepoBones" => $data["MorbusOnko_IsTumorDepoBones"],
			"MorbusOnkoVizitPLDop_IsTumorDepoLiver" => $data["MorbusOnko_IsTumorDepoLiver"],
			"MorbusOnkoVizitPLDop_IsTumorDepoLungs" => $data["MorbusOnko_IsTumorDepoLungs"],
			"MorbusOnkoVizitPLDop_IsTumorDepoBrain" => $data["MorbusOnko_IsTumorDepoBrain"],
			"MorbusOnkoVizitPLDop_IsTumorDepoSkin" => $data["MorbusOnko_IsTumorDepoSkin"],
			"MorbusOnkoVizitPLDop_IsTumorDepoKidney" => $data["MorbusOnko_IsTumorDepoKidney"],
			"MorbusOnkoVizitPLDop_IsTumorDepoOvary" => $data["MorbusOnko_IsTumorDepoOvary"],
			"MorbusOnkoVizitPLDop_IsTumorDepoPerito" => $data["MorbusOnko_IsTumorDepoPerito"],
			"MorbusOnkoVizitPLDop_IsTumorDepoMarrow" => $data["MorbusOnko_IsTumorDepoMarrow"],
			"MorbusOnkoVizitPLDop_IsTumorDepoOther" => $data["MorbusOnko_IsTumorDepoOther"],
			"MorbusOnkoVizitPLDop_IsTumorDepoMulti" => $data["MorbusOnko_IsTumorDepoMulti"],
			"MorbusOnkoVizitPLDop_deadDT" => $data["MorbusOnkoVizitPLDop_deadDT"],
			"Diag_did" => $data["Diag_did"],
			"MorbusOnkoVizitPLDop_deathCause" => $data["MorbusOnkoVizitPLDop_deathCause"],
			"AutopsyPerformType_id" => $data["AutopsyPerformType_id"],
			"TumorAutopsyResultType_id" => $data["TumorAutopsyResultType_id"],
			"MorbusOnkoBasePersonState_id" => $data["MorbusOnkoBasePersonState_id"],
			"MorbusOnkoBaseLateComplTreat_id" => $data["MorbusOnkoBaseLateComplTreat_id"],
			"OnkoStatusYearEndType_id" => $data["OnkoStatusYearEndType_id"],
			"OnkoDiagConfType_id" => $data["OnkoDiagConfType_id"],
			"OnkoLateComplTreatType_id" => $data["OnkoLateComplTreatType_id"],
			"OnkoTumorStatusType_id" => $data["OnkoTumorStatusType_id"],
			"MorbusOnkoVizitPLDop_setDiagDT" => !empty($data["MorbusOnko_setDiagDT"]) ? date("Y-m-d", strtotime($data["MorbusOnko_setDiagDT"])) : null,
			"MorbusOnkoVizitPLDop_takeDT" => !empty($data["MorbusOnko_takeDT"]) ? date("Y-m-d", strtotime($data["MorbusOnko_takeDT"])) : null,
			"HistologicReasonType_id" => $data["HistologicReasonType_id"],
			"MorbusOnkoVizitPLDop_histDT" => !empty($data["MorbusOnko_histDT"]) ? date("Y-m-d", strtotime($data["MorbusOnko_histDT"])) : null,
			"DiagAttribType_id" => $data["DiagAttribType_id"],
			"OnkoLesionSide_id" => $data["OnkoLesionSide_id"],
			"MorbusOnkoVizitPLDop_NumHisto" => $data["MorbusOnko_NumHisto"],
			"TumorCircumIdentType_id" => $data["TumorCircumIdentType_id"],
			"OnkoLateDiagCause_id" => $data["OnkoLateDiagCause_id"],
			"OnkoPostType_id" => $data["OnkoPostType_id"],
			"MorbusOnkoVizitPLDop_IsMainTumor" => $data["MorbusOnko_IsMainTumor"],
			'MorbusOnkoVizitPLDop_NumTumor' => $data['MorbusOnko_NumTumor'],
			"OnkoTreatment_id" => $data["OnkoTreatment_id"],
			"MorbusOnkoVizitPLDop_FirstSignDT" => !empty($data["MorbusOnko_firstSignDT"]) ? date("Y-m-d", strtotime($data["MorbusOnko_firstSignDT"])) : null,
			"TumorPrimaryMultipleType_id" => $data["TumorPrimaryMultipleType_id"],
			"EvnDiagPLSop_id" => !empty($data["EvnDiagPLSop_id"]) ? $data["EvnDiagPLSop_id"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		if ($this->regionNick == "ekb" || ($this->getRegionNick() == "perm" && $data["DiagAttribType_id"] == 3)) {
			$queryParams["DiagAttribDict_id"] = $data["DiagAttribDict_id"];
			$queryParams["DiagResult_id"] = $data["DiagResult_id"];
			$queryParams["DiagAttribDict_fid"] = null;
			$queryParams["DiagResult_fid"] = null;
		} else {
			$queryParams["DiagAttribDict_fid"] = (!empty($data["DiagAttribDict_fid"]) ? $data["DiagAttribDict_fid"] : $data["DiagAttribDict_id"]);
			$queryParams["DiagResult_fid"] = (!empty($data["DiagResult_fid"]) ? $data["DiagResult_fid"] : $data["DiagResult_id"]);
			$queryParams["DiagAttribDict_id"] = null;
			$queryParams["DiagResult_id"] = null;
		}
		$selectString = "
			morbusonkovizitpldop_id as \"MorbusOnkoVizitPLDop_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    morbusonkovizitpldop_id := :MorbusOnkoVizitPLDop_id,
			    evnvizit_id := :EvnVizit_id,
			    diag_id := :Diag_id,
			    onkoregouttype_id := :OnkoRegOutType_id,
			    onkostatusyearendtype_id := :OnkoStatusYearEndType_id,
			    onkoinvalidtype_id := :OnkoInvalidType_id,
			    onkodiag_id := :OnkoDiag_id,
			    onkot_id := :OnkoT_id,
			    onkon_id := :OnkoN_id,
			    onkom_id := :OnkoM_id,
			    tumorstage_id := :TumorStage_id,
			    morbusonkovizitpldop_istumordepounknown := :MorbusOnkoVizitPLDop_IsTumorDepoUnknown,
			    morbusonkovizitpldop_istumordepolympha := :MorbusOnkoVizitPLDop_IsTumorDepoLympha,
			    morbusonkovizitpldop_istumordepobones := :MorbusOnkoVizitPLDop_IsTumorDepoBones,
			    morbusonkovizitpldop_istumordepoliver := :MorbusOnkoVizitPLDop_IsTumorDepoLiver,
			    morbusonkovizitpldop_istumordepolungs := :MorbusOnkoVizitPLDop_IsTumorDepoLungs,
			    morbusonkovizitpldop_istumordepobrain := :MorbusOnkoVizitPLDop_IsTumorDepoBrain,
			    morbusonkovizitpldop_istumordeposkin := :MorbusOnkoVizitPLDop_IsTumorDepoSkin,
			    morbusonkovizitpldop_istumordepokidney := :MorbusOnkoVizitPLDop_IsTumorDepoKidney,
			    morbusonkovizitpldop_istumordepoovary := :MorbusOnkoVizitPLDop_IsTumorDepoOvary,
			    morbusonkovizitpldop_istumordepoperito := :MorbusOnkoVizitPLDop_IsTumorDepoPerito,
			    morbusonkovizitpldop_istumordepomarrow := :MorbusOnkoVizitPLDop_IsTumorDepoMarrow,
			    morbusonkovizitpldop_istumordepoother := :MorbusOnkoVizitPLDop_IsTumorDepoOther,
			    morbusonkovizitpldop_istumordepomulti := :MorbusOnkoVizitPLDop_IsTumorDepoMulti,
			    morbusonkovizitpldop_deaddt := :MorbusOnkoVizitPLDop_deadDT,
			    diag_did := :Diag_did,
			    morbusonkovizitpldop_deathcause := :MorbusOnkoVizitPLDop_deathCause,
			    autopsyperformtype_id := :AutopsyPerformType_id,
			    tumorautopsyresulttype_id := :TumorAutopsyResultType_id,
			    morbusonkovizitpldop_setdt := :MorbusOnkoVizitPLDop_setDT,
			    medpersonal_id := :MedPersonal_id,
			    morbusonkobasepersonstate_id := :MorbusOnkoBasePersonState_id,
			    morbusonkobaselatecompltreat_id := :MorbusOnkoBaseLateComplTreat_id,
			    onkotumorstatustype_id := :OnkoTumorStatusType_id,
			    onkodiagconftype_id := :OnkoDiagConfType_id,
			    onkolatecompltreattype_id := :OnkoLateComplTreatType_id,
			    onkolesionside_id := :OnkoLesionSide_id,
			    morbusonkovizitpldop_numhisto := :MorbusOnkoVizitPLDop_NumHisto,
			    tumorcircumidenttype_id := :TumorCircumIdentType_id,
			    onkolatediagcause_id := :OnkoLateDiagCause_id,
			    onkodiag_mid := :OnkoDiag_mid,
			    onkoposttype_id := :OnkoPostType_id,
			    diagattribtype_id := :DiagAttribType_id,
			    diagattribdict_id := :DiagAttribDict_id,
			    diagresult_id := :DiagResult_id,
			    morbusonkovizitpldop_setdiagdt := :MorbusOnkoVizitPLDop_setDiagDT,
			    morbusonkovizitpldop_ismaintumor := :MorbusOnkoVizitPLDop_IsMainTumor,
			    MorbusOnkoVizitPLDop_NumTumor := :MorbusOnkoVizitPLDop_NumTumor,
			    diagattribdict_fid := :DiagAttribDict_fid,
			    diagresult_fid := :DiagResult_fid,
			    onkotreatment_id := :OnkoTreatment_id,
			    evndiagplsop_id := :EvnDiagPLSop_id,
			    morbusonkovizitpldop_firstsigndt := :MorbusOnkoVizitPLDop_FirstSignDT,
			    tumorprimarymultipletype_id := :TumorPrimaryMultipleType_id,
			    morbusonkovizitpldop_takedt := :MorbusOnkoVizitPLDop_takeDT,
			    histologicreasontype_id := :HistologicReasonType_id,
			    morbusonkovizitpldop_histdt := :MorbusOnkoVizitPLDop_histDT,
			    onkot_fid := :OnkoT_fid,
			    onkon_fid := :OnkoN_fid,
			    onkom_fid := :OnkoM_fid,
			    tumorstage_fid := :TumorStage_fid,
			    pmuser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function delete($data)
	{
		$subsect_list = ["OnkoConsult", "MorbusOnkoLink", "MorbusOnkoSpecTreat", "MorbusOnkoDrug", "MorbusOnkoRefusal"];
		$params = ["MorbusOnkoVizitPLDop_id" => $data["MorbusOnkoVizitPLDop_id"],'pmUser_id' => $data['pmUser_id']];
		foreach ($subsect_list as $subsect) {
			$selectString = "{$subsect}_id";
			$whereString = "MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id";
			$mol_list = $this->queryList("select {$selectString} from {$subsect} where {$whereString}", ["MorbusOnkoVizitPLDop_id" => $data["MorbusOnkoVizitPLDop_id"]]);
			foreach ($mol_list as $ml) {
				$this->execCommonSP("dbo.p_{$subsect}_del", array("{$subsect}_id" => $ml));
			}
		}
		$query = "
			select
				mol.MorbusOnkoVizitPLDop_id as \"moid\",
			    mol.Diag_id as \"Diag_id\",
			    evn.Person_id as \"Person_id\",
			    M.Morbus_id as \"Morbus_id\",
			    evn.EvnVizitPL_id as \"Evn_id\"
			from
				v_MorbusOnkoVizitPLDop mol
				inner join v_EvnVizitPL evn on evn.EvnVizitPL_id = mol.EvnVizit_id
				left join lateral (
					select
					    M1.Morbus_id as Morbus_id,
						case when M1.Morbus_id = evn.Morbus_id then 0 else 1 end msort
					from
						v_Morbus M1
						inner join v_MorbusBase MB on M1.MorbusBase_id = MB.MorbusBase_id
						inner join v_MorbusOnko MO on MO.Morbus_id = M1.Morbus_id
						inner join v_Diag MD on M1.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
					where M1.Person_id = evn.Person_id
					order by
						msort,
					    M1.Morbus_disDT,
					    M1.Morbus_setDT DESC
					limit 1
				) as M on true
			where mol.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id
			  and EvnDiagPLSop_id is null
			limit 1
		";
		$tmp = $this->getFirstRowFromQuery($query, ["MorbusOnkoVizitPLDop_id" => $data["MorbusOnkoVizitPLDop_id"]]);

		if (!empty($tmp)) {
			$this->load->library("swMorbus");
			$funcParams = [
				"Evn_id" => $tmp["Evn_id"],
				"Morbus_id" => $tmp["Morbus_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			swMorbus::removeEvnUslugaOnko($funcParams);
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_morbusonkovizitpldop_del(morbusonkovizitpldop_id => :MorbusOnkoVizitPLDop_id, pmUser_id => :pmUser_id);
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при удалении талона дополнений больного ЗНО");
		}
		return $resp;
	}

	/**
	 * Метод получения данных для просмотра в ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function loadViewData($data)
	{
		$query = "
			select
				t.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				t.EvnVizit_id as \"MorbusOnkoVizitPLDop_pid\",
				to_char(t.MorbusOnkoVizitPLDop_setDT, '{$this->dateTimeForm104}') as \"MorbusOnkoVizitPLDop_setDate\",
				lpu.Lpu_Name as \"Lpu_Name\",
				pc.PersonCard_Code as \"PersonCard_Code\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				coalesce(rtrim(Address1.Address_Address), '') as \"Person_Address\",
				diag.Diag_FullName as \"Diag_FullName\",
				orot.OnkoRegOutType_Name as \"OnkoRegOutType_Name\",
				oit.OnkoInvalidType_Name as \"OnkoInvalidType_Name\",
				opst.OnkoPersonStateType_Name as \"OnkoPersonStateType_Name\",
				otst.OnkoTumorStatusType_Name as \"OnkoTumorStatusType_Name\",
				osyet.OnkoStatusYearEndType_Name as \"OnkoStatusYearEndType_Name\",
				to_char(t.MorbusOnkoVizitPLDop_deadDT, '{$this->dateTimeForm104}') as \"MorbusOnkoVizitPLDop_deadDate\",
				t.MorbusOnkoVizitPLDop_deathCause as \"MorbusOnkoVizitPLDop_deathCause\",
				apt.AutopsyPerformType_Name as \"AutopsyPerformType_Name\",
				tart.TumorAutopsyResultType_Name as \"TumorAutopsyResultType_Name\",
				mp.Person_Fin as \"MedPersonal_Fin\",
				olctt.OnkoLateComplTreatType_Name as \"OnkoLateComplTreatType_Name\"
			from
				v_MorbusOnkoVizitPLDop t
				inner join v_EvnVizitPL ev on t.EvnVizit_id = ev.EvnVizitPL_id
				inner join v_PersonState PS on ev.Person_id = PS.Person_id
				left join v_Lpu lpu on ev.Lpu_id = lpu.Lpu_id
				left join v_MedPersonal mp on t.MedPersonal_id = mp.MedPersonal_id and ev.Lpu_id = mp.Lpu_id
				left join v_PersonCard pc on ev.Person_id = pc.Person_id and pc.LpuAttachType_id = 1
				left join v_Address Address1 on PS.UAddress_id = Address1.Address_id
				left join v_Diag diag on t.Diag_id = diag.Diag_id
				left join v_OnkoRegOutType orot on t.OnkoRegOutType_id = orot.OnkoRegOutType_id
				left join v_AutopsyPerformType apt on t.AutopsyPerformType_id = apt.AutopsyPerformType_id
				left join v_TumorAutopsyResultType tart on t.TumorAutopsyResultType_id = tart.TumorAutopsyResultType_id
				left join v_OnkoInvalidType oit on t.OnkoInvalidType_id = oit.OnkoInvalidType_id
				left join v_MorbusOnkoBasePersonState mobps on t.MorbusOnkoBasePersonState_id = mobps.MorbusOnkoBasePersonState_id
				left join v_OnkoPersonStateType opst on mobps.OnkoPersonStateType_id = opst.OnkoPersonStateType_id
				left join v_OnkoTumorStatusType otst on t.OnkoTumorStatusType_id = otst.OnkoTumorStatusType_id
				left join v_OnkoStatusYearEndType osyet on t.OnkoStatusYearEndType_id = osyet.OnkoStatusYearEndType_id
				left join v_OnkoLateComplTreatType olctt on olctt.OnkoLateComplTreatType_id = t.OnkoLateComplTreatType_id
			where t.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}
}