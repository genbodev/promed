<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Выписка из медицинской карты стационарного больного злокачественным новообразованием
 *
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      12.2014
 */
class MorbusOnkoLeave_model extends swPgModel
{
    /*
    private $MorbusOnkoLeave_id; //
    private $EvnSection_id; //Движение в отделении
    private $Diag_id;//диагноз
    private $OnkoDiag_id; //Морфологический тип опухоли. (Гистология опухоли)
    private $OnkoT_id; //T
    private $OnkoN_id; //N
    private $OnkoM_id; //M
    private $TumorStage_id; //Стадия опухолевого процесса
    private $MorbusOnkoLeave_IsTumorDepoUnknown; //Локализация отдаленных метастазов: Неизвестна
    private $MorbusOnkoLeave_IsTumorDepoLympha; //Локализация отдаленных метастазов: Отдаленные лимфатические узлы
    private $MorbusOnkoLeave_IsTumorDepoBones; //Локализация отдаленных метастазов: Кости
    private $MorbusOnkoLeave_IsTumorDepoLiver; //Локализация отдаленных метастазов: Печень
    private $MorbusOnkoLeave_IsTumorDepoLungs; //Локализация отдаленных метастазов: Легкие и/или плевра
    private $MorbusOnkoLeave_IsTumorDepoBrain; //Локализация отдаленных метастазов: Головной мозг
    private $MorbusOnkoLeave_IsTumorDepoSkin; //Локализация отдаленных метастазов: Кожа
    private $MorbusOnkoLeave_IsTumorDepoKidney; //Локализация отдаленных метастазов: Почки
    private $MorbusOnkoLeave_IsTumorDepoOvary; //Локализация отдаленных метастазов: Яичники
    private $MorbusOnkoLeave_IsTumorDepoPerito; //Локализация отдаленных метастазов: Брюшина
    private $MorbusOnkoLeave_IsTumorDepoMarrow; //Локализация отдаленных метастазов: Костный мозг
    private $MorbusOnkoLeave_IsTumorDepoOther; //Локализация отдаленных метастазов: Другие органы
    private $MorbusOnkoLeave_IsTumorDepoMulti; //Локализация отдаленных метастазов: Множественные
    private $TumorPrimaryTreatType_id; //Проведенное лечение первичной опухоли
    private $TumorRadicalTreatIncomplType_id; //Причины незавершенности радикального лечения
    private $OnkoDiagConfType_id;//метод подтверждения диагноза
    private $OnkoStatusYearEndType_id;//состояние на конец отчетного года

    private $OnkoLateComplTreatType_id;//Поздние осложнения лечения
    private $OnkoTumorStatusType_id;//Состояние опухолевого процесса при осмотре
    */

	/**
	 * construct
	 */
	function __construct()
	{
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_VIEW_DATA,
		));
	}

	/**
	 * Cохранение выписки из стационарной карты онкобольного
	 * Должно происходить только после сохранения специфики из движения
	 * Принимает 3 обязательных параметра: EvnSection_id, MorbusOnko_id и pmUser_id
	 */
	function save($data) {
		$p = array(
			'EvnSection_id' => $data['Evn_pid'],
			'MorbusOnko_id' => $data['MorbusOnko_id'],
			'EvnDiag_id' => $data['EvnDiagPLSop_id'],
		);
		$q = '
			select 				
				T.MorbusOnkoLeave_id as "MorbusOnkoLeave_id" 
				,Evn.EvnSection_id "EvnSection_id"
				,coalesce(ED.Diag_id,Evn.Diag_id) as "Diag_id"
				,MO.TumorPrimaryTreatType_id as "TumorPrimaryTreatType_id"
				,MO.TumorRadicalTreatIncomplType_id as "TumorRadicalTreatIncomplType_id"
				,MO.OnkoLateComplTreatType_id as "OnkoLateComplTreatType_id"
				,MO.OnkoTumorStatusType_id as "OnkoTumorStatusType_id"
			from
				v_EvnSection Evn
				inner join v_MorbusOnko MO on MO.MorbusOnko_id = :MorbusOnko_id
				inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
				inner join v_MorbusOnkoBase MB on MB.MorbusBase_id = M.MorbusBase_id
				left join v_MorbusOnkoLeave T on T.EvnSection_id = Evn.EvnSection_id and coalesce(T.EvnDiag_id,0) = coalesce(cast(:EvnDiag_id as bigint),0)
				left join v_EvnDiag ED on ED.EvnDiag_id = :EvnDiag_id
			where
				Evn.EvnSection_id = :EvnSection_id
		';
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$ra = $r->result('array');
			if (empty($ra) || !is_array($ra[0]) || empty($ra[0]))
			{
				return array(array('Error_Msg' => 'Получение данных для выписки из стационарной карты онкобольного. Данные не получены'));
			}
			foreach($ra[0] as $key => $val) {
				$data[$key] = $val;
			}
		} else {
			return array(array('Error_Msg' => 'Получение данных для выписки из стационарной карты онкобольного. Ошибка запроса к БД'));
		}

		$action = (isset($data['MorbusOnkoLeave_id']))?'upd':'ins';
		$p = array(
			'MorbusOnkoLeave_id' => $data['MorbusOnkoLeave_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'Diag_id' => $data['Diag_id'],
			'OnkoDiag_id' => $data['OnkoDiag_mid'],
			'OnkoT_id' => $data['OnkoT_id'],
			'OnkoN_id' => $data['OnkoN_id'],
			'OnkoM_id' => $data['OnkoM_id'],
			'TumorStage_id' => $data['TumorStage_id'],
			'OnkoT_fid' => $data['OnkoT_fid'],
			'OnkoN_fid' => $data['OnkoN_fid'],
			'OnkoM_fid' => $data['OnkoM_fid'],
			'TumorStage_fid' => $data['TumorStage_fid'],
			'MorbusOnkoLeave_IsTumorDepoUnknown' => $data['MorbusOnko_IsTumorDepoUnknown'],
			'MorbusOnkoLeave_IsTumorDepoLympha' => $data['MorbusOnko_IsTumorDepoLympha'],
			'MorbusOnkoLeave_IsTumorDepoBones' => $data['MorbusOnko_IsTumorDepoBones'],
			'MorbusOnkoLeave_IsTumorDepoLiver' => $data['MorbusOnko_IsTumorDepoLiver'],
			'MorbusOnkoLeave_IsTumorDepoLungs' => $data['MorbusOnko_IsTumorDepoLungs'],
			'MorbusOnkoLeave_IsTumorDepoBrain' => $data['MorbusOnko_IsTumorDepoBrain'],
			'MorbusOnkoLeave_IsTumorDepoSkin' => $data['MorbusOnko_IsTumorDepoSkin'],
			'MorbusOnkoLeave_IsTumorDepoKidney' => $data['MorbusOnko_IsTumorDepoKidney'],
			'MorbusOnkoLeave_IsTumorDepoOvary' => $data['MorbusOnko_IsTumorDepoOvary'],
			'MorbusOnkoLeave_IsTumorDepoPerito' => $data['MorbusOnko_IsTumorDepoPerito'],
			'MorbusOnkoLeave_IsTumorDepoMarrow' => $data['MorbusOnko_IsTumorDepoMarrow'],
			'MorbusOnkoLeave_IsTumorDepoOther' => $data['MorbusOnko_IsTumorDepoOther'],
			'MorbusOnkoLeave_IsTumorDepoMulti' => $data['MorbusOnko_IsTumorDepoMulti'],
			'TumorPrimaryTreatType_id' => $data['TumorPrimaryTreatType_id'],
			'TumorRadicalTreatIncomplType_id' => $data['TumorRadicalTreatIncomplType_id'],
			'OnkoStatusYearEndType_id' => $data['OnkoStatusYearEndType_id'],
			'OnkoDiagConfType_id' => $data['OnkoDiagConfType_id'],
			'OnkoLateComplTreatType_id' => $data['OnkoLateComplTreatType_id'],
			'OnkoTumorStatusType_id' => $data['OnkoTumorStatusType_id'],
			'MorbusOnkoLeave_setDiagDT' => !empty($data['MorbusOnko_setDiagDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_setDiagDT'])) : null,
			'MorbusOnkoLeave_takeDT' => !empty($data['MorbusOnko_takeDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_takeDT'])) : null,
			'HistologicReasonType_id' => $data['HistologicReasonType_id'],
			'MorbusOnkoLeave_histDT' => !empty($data['MorbusOnko_histDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_histDT'])) : null,
			'DiagAttribType_id' => $data['DiagAttribType_id'],
			'OnkoLesionSide_id' => $data['OnkoLesionSide_id'],
			'MorbusOnkoLeave_NumHisto' => $data['MorbusOnko_NumHisto'],
			'MorbusOnkoLeave_NumTumor' => $data['MorbusOnko_NumTumor'],
			'TumorCircumIdentType_id' => $data['TumorCircumIdentType_id'],
			'OnkoLateDiagCause_id' => $data['OnkoLateDiagCause_id'],
			'TumorAutopsyResultType_id' => $data['TumorAutopsyResultType_id'],
			'MorbusOnkoLeave_IsMainTumor' => $data['MorbusOnko_IsMainTumor'],
			'OnkoDiag_mid' => $data['OnkoDiag_mid'],
			'OnkoPostType_id' => $data['OnkoPostType_id'],
			'OnkoTreatment_id' => $data['OnkoTreatment_id'],
			'MorbusOnkoLeave_FirstSignDT' => !empty($data['MorbusOnko_firstSignDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_firstSignDT'])) : null,
			'TumorPrimaryMultipleType_id' => $data['TumorPrimaryMultipleType_id'],
			'EvnDiag_id' => !empty($data['EvnDiagPLSop_id']) ? $data['EvnDiagPLSop_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		if ( $this->regionNick == 'ekb' || ($this->getRegionNick() == 'perm' && $data['DiagAttribType_id'] == 3) ) {
			$p['DiagAttribDict_id'] = $data['DiagAttribDict_id'];
			$p['DiagResult_id'] = $data['DiagResult_id'];
			$p['DiagAttribDict_fid'] = null;
			$p['DiagResult_fid'] = null;
		}
		else {
			$p['DiagAttribDict_fid'] = (!empty($data['DiagAttribDict_fid']) ? $data['DiagAttribDict_fid'] : $data['DiagAttribDict_id']);
			$p['DiagResult_fid'] = (!empty($data['DiagResult_fid']) ? $data['DiagResult_fid'] : $data['DiagResult_id']);
			$p['DiagAttribDict_id'] = null;
			$p['DiagResult_id'] = null;
		}

		$q = '
			select
				MorbusOnkoLeave_id as "MorbusOnkoLeave_id",
				Error_Code as "Error_Code",
				Error_MEssage as "Error_Msg"
			from dbo.p_MorbusOnkoLeave_'. $action .'(
				MorbusOnkoLeave_id := :MorbusOnkoLeave_id,
				EvnSection_id := :EvnSection_id,
				Diag_id := :Diag_id,
				OnkoDiag_id := :OnkoDiag_id,
				OnkoT_id := :OnkoT_id,
				OnkoN_id := :OnkoN_id,
				OnkoM_id := :OnkoM_id,
				TumorStage_id := :TumorStage_id,
				OnkoT_fid := :OnkoT_fid,
				OnkoN_fid := :OnkoN_fid,
				OnkoM_fid := :OnkoM_fid,
				TumorStage_fid := :TumorStage_fid,
				MorbusOnkoLeave_IsTumorDepoUnknown := :MorbusOnkoLeave_IsTumorDepoUnknown,
				MorbusOnkoLeave_IsTumorDepoLympha := :MorbusOnkoLeave_IsTumorDepoLympha,
				MorbusOnkoLeave_IsTumorDepoBones := :MorbusOnkoLeave_IsTumorDepoBones,
				MorbusOnkoLeave_IsTumorDepoLiver := :MorbusOnkoLeave_IsTumorDepoLiver,
				MorbusOnkoLeave_IsTumorDepoLungs := :MorbusOnkoLeave_IsTumorDepoLungs,
				MorbusOnkoLeave_IsTumorDepoBrain := :MorbusOnkoLeave_IsTumorDepoBrain,
				MorbusOnkoLeave_IsTumorDepoSkin := :MorbusOnkoLeave_IsTumorDepoSkin,
				MorbusOnkoLeave_IsTumorDepoKidney := :MorbusOnkoLeave_IsTumorDepoKidney,
				MorbusOnkoLeave_IsTumorDepoOvary := :MorbusOnkoLeave_IsTumorDepoOvary,
				MorbusOnkoLeave_IsTumorDepoPerito := :MorbusOnkoLeave_IsTumorDepoPerito,
				MorbusOnkoLeave_IsTumorDepoMarrow := :MorbusOnkoLeave_IsTumorDepoMarrow,
				MorbusOnkoLeave_IsTumorDepoOther := :MorbusOnkoLeave_IsTumorDepoOther,
				MorbusOnkoLeave_IsTumorDepoMulti := :MorbusOnkoLeave_IsTumorDepoMulti,
				TumorPrimaryTreatType_id := :TumorPrimaryTreatType_id,
				TumorRadicalTreatIncomplType_id := :TumorRadicalTreatIncomplType_id,
				OnkoStatusYearEndType_id := :OnkoStatusYearEndType_id,
				OnkoDiagConfType_id := :OnkoDiagConfType_id,
				OnkoLateComplTreatType_id := :OnkoLateComplTreatType_id,
				OnkoTumorStatusType_id := :OnkoTumorStatusType_id,
				MorbusOnkoLeave_setDiagDT := :MorbusOnkoLeave_setDiagDT,
				MorbusOnkoLeave_takeDT := :MorbusOnkoLeave_takeDT,
				DiagAttribType_id := :DiagAttribType_id,
				DiagAttribDict_id := :DiagAttribDict_id,
				DiagResult_id := :DiagResult_id,
				DiagAttribDict_fid := :DiagAttribDict_fid,
				DiagResult_fid := :DiagResult_fid,
				OnkoLesionSide_id := :OnkoLesionSide_id,
				MorbusOnkoLeave_NumHisto := :MorbusOnkoLeave_NumHisto,
			    MorbusOnkoLeave_NumTumor := :MorbusOnkoLeave_NumTumor,
				TumorCircumIdentType_id := :TumorCircumIdentType_id,
				OnkoLateDiagCause_id := :OnkoLateDiagCause_id,
				TumorAutopsyResultType_id := :TumorAutopsyResultType_id,
				MorbusOnkoLeave_IsMainTumor := :MorbusOnkoLeave_IsMainTumor,
				OnkoDiag_mid := :OnkoDiag_mid,
				OnkoPostType_id := :OnkoPostType_id,
				OnkoTreatment_id := :OnkoTreatment_id,
				MorbusOnkoLeave_FirstSignDT := :MorbusOnkoLeave_FirstSignDT,
				TumorPrimaryMultipleType_id := :TumorPrimaryMultipleType_id,
				HistologicReasonType_id := :HistologicReasonType_id,
				MorbusOnkoLeave_histDT := :MorbusOnkoLeave_histDT,
				EvnDiag_id := :EvnDiag_id,
				pmUser_id := :pmUser_id
			)
		';
		//echo getDebugSQL($q, $p); exit;
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			return $r->result('array');
		} else {
			//log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function delete($data) {
		$subsect_list = array('OnkoConsult', 'MorbusOnkoLink', 'MorbusOnkoSpecTreat', 'MorbusOnkoDrug', 'MorbusOnkoRefusal');
		$params = array(
			'MorbusOnkoLeave_id' => $data['MorbusOnkoLeave_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		foreach($subsect_list as $subsect) {
			$mol_list = $this->queryList("select {$subsect}_id as \"{$subsect}_id\" from {$subsect} where MorbusOnkoLeave_id = :MorbusOnkoLeave_id",
				array('MorbusOnkoLeave_id' => $data['MorbusOnkoLeave_id'])
			);
			foreach($mol_list as $ml) {
				$this->execCommonSP("dbo.p_{$subsect}_del", array("{$subsect}_id" => $ml));
			}
		}

		$tmp = $this->getFirstRowFromQuery("
			select
				mol.MorbusOnkoLeave_id as \"moid\",
				mol.Diag_id as \"Diag_id\",
				evn.Person_id as \"Person_id\",
				M.Morbus_id as \"Morbus_id\",
				evn.EvnSection_id as \"Evn_id\"
			from v_MorbusOnkoLeave mol
				inner join v_EvnSection evn on evn.EvnSection_id = mol.EvnSection_id
				left join lateral(
					select
						M.Morbus_id, 
						case when M.Morbus_id = evn.Morbus_id then 0 else 1 end as msort
					from v_Morbus M 
						inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
						inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
						inner join v_Diag MD on M.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
					where M.Person_id = evn.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
					limit 1
				) M on true
			where 
				mol.MorbusOnkoLeave_id = :MorbusOnkoLeave_id and 
				EvnDiag_id is null
			limit 1
		", array(
			'MorbusOnkoLeave_id' => $data['MorbusOnkoLeave_id']
		));

		if(!empty($tmp)) {
			$this->load->library('swMorbus');
			swMorbus::removeEvnUslugaOnko(array(
				'Evn_id' => $tmp['Evn_id'],
				'Morbus_id' => $tmp['Morbus_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_MEssage as \"Error_Msg\"
			from dbo.p_MorbusOnkoLeave_del (
				MorbusOnkoLeave_id := :MorbusOnkoLeave_id,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при удалении талона дополнений больного ЗНО');
		}
		return $resp;
	}

	/**
	 * Метод получения данных для просмотра в ЭМК
	 */
	function loadViewData($data)
	{
		$query = "
			select
				t.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				t.EvnSection_id as \"MorbusOnkoLeave_pid\",
				lpu.Lpu_Name as \"Lpu_Name\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				PS.Person_Phone as \"Person_Phone\",
				v_Ethnos.Ethnos_Name as \"Ethnos_Name\",
				OOC.OnkoOccupationClass_Name as \"OnkoOccupationClass_Name\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				v_Sex.Sex_Name as \"Sex_Name\",
				coalesce(rtrim(PSUAddress.Address_Address), '') as \"Person_UAddress\",
				coalesce(rtrim(PSPAddress.Address_Address), '') as \"Person_PAddress\",
				coalesce(rtrim(LpuAddress.Address_Address), '') as \"Lpu_Address\",
				mp.Person_Fin as \"MedPersonal_Fin\",
				diag.Diag_FullName as \"Diag_FullName\",
				IsTumorDepoLympha.YesNo_Name as \"IsTumorDepoLympha_Name\",
				IsTumorDepoBones.YesNo_Name as \"IsTumorDepoBones_Name\",
				IsTumorDepoLiver.YesNo_Name as \"IsTumorDepoLiver_Name\",
				IsTumorDepoLungs.YesNo_Name as \"IsTumorDepoLungs_Name\",
				IsTumorDepoBrain.YesNo_Name as \"IsTumorDepoBrain_Name\",
				IsTumorDepoKidney.YesNo_Name as \"IsTumorDepoKidney_Name\",
				IsTumorDepoOvary.YesNo_Name as \"IsTumorDepoOvary_Name\",
				IsTumorDepoPerito.YesNo_Name as \"IsTumorDepoPerito_Name\",
				IsTumorDepoMarrow.YesNo_Name as \"IsTumorDepoMarrow_Name\",
				IsTumorDepoOther.YesNo_Name as \"IsTumorDepoOther_Name\",
				IsTumorDepoMulti.YesNo_Name as \"IsTumorDepoMulti_Name\",
				IsTumorDepoSkin.YesNo_Name as \"IsTumorDepoSkin_Name\",
				IsTumorDepoUnknown.YesNo_Name as \"IsTumorDepoUnknown_Name\",
				v_OnkoCombiTreatType.OnkoCombiTreatType_Name as \"OnkoCombiTreatType_Name\",
				v_OnkoDiag.OnkoDiag_Name as \"OnkoDiag_Name\",
				v_OnkoDiagConfType.OnkoDiagConfType_Name as \"OnkoDiagConfType_Name\",
				v_OnkoDiagConfType.OnkoDiagConfType_Code as \"OnkoDiagConfType_Code\",
				v_OnkoM.OnkoM_Name as \"OnkoM_Name\",
				v_OnkoN.OnkoN_Name as \"OnkoN_Name\",
				v_OnkoT.OnkoT_Name as \"OnkoT_Name\",
				v_TumorPrimaryTreatType.TumorPrimaryTreatType_Name as \"TumorPrimaryTreatType_Name\",
				v_TumorRadicalTreatIncomplType.TumorRadicalTreatIncomplType_Name as \"TumorRadicalTreatIncomplType_Name\",
				v_TumorStage.TumorStage_Name as \"TumorStage_Name\",
				to_char(coalesce(MorbusOnkoBasePS_setDT, ev.EvnSection_setDT), 'dd.mm.yyyy') as \"EvnSection_setDate\",
				to_char(coalesce(MorbusOnkoBasePS_disDT, ev.EvnSection_disDT), 'dd.mm.yyyy') as \"EvnSection_disDate\",
				date_part('DAY', coalesce(MorbusOnkoBasePS_disDT, ev.EvnSection_disDT, dbo.tzGetDate()) - coalesce(MorbusOnkoBasePS_setDT, ev.EvnSection_setDT)) as \"EvnSection_Day\",
				v_OnkoPurposeHospType.OnkoPurposeHospType_Name as \"OnkoPurposeHospType_Name\",
				IsFirst.YesNo_Name as \"IsFirst_Name\",
				(SELECT
						string_agg(v_Diag.Diag_FullName, ', ') as Diag_FullName
					FROM
						v_EvnDiagPs
						inner join v_Diag on v_Diag.Diag_id = v_EvnDiagPs.Diag_id
					WHERE
						v_EvnDiagPs.EvnDiagPS_pid = ev.EvnSection_id
						and v_EvnDiagPs.DiagSetClass_id = 3 -- Сопутствующий
						and v_EvnDiagPs.DiagSetType_id = 3 -- Клинический
				) as \"EvnDiagPsSopList\",
				to_char(surg.EvnUslugaOnkoSurg_setDT, 'dd.mm.yyyy') as \"EvnUslugaOnkoSurg_setDate\",
				surg.Operation_Name as \"Operation_Name\",
				surg.SurgOslList as \"SurgOslList\",
				to_char(beem.EvnUslugaOnkoBeam_setDT, 'dd.mm.yyyy') as \"EvnUslugaOnkoBeam_setDate\",
				beem.BeamOslList as \"BeamOslList\",
				beem.EvnUslugaOnkoBeam_TotalDoseRegZone as \"EvnUslugaOnkoBeam_TotalDoseRegZone\",
				beem.TotalDoseRegZone_Unit as \"TotalDoseRegZone_Unit\",
				beem.EvnUslugaOnkoBeam_CountFractionRT as \"EvnUslugaOnkoBeam_CountFractionRT\",
				beem.EvnUslugaOnkoBeam_TotalDoseTumor as \"EvnUslugaOnkoBeam_TotalDoseTumor\",
				beem.TotalDoseTumor_Unit as \"TotalDoseTumor_Unit\",
				beem.OnkoUslugaBeamFocusType_Name as \"OnkoUslugaBeamFocusType_Name\",
				beem.OnkoUslugaBeamIrradiationType_Name as \"OnkoUslugaBeamIrradiationType_Name\",
				beem.OnkoUslugaBeamKindType_Name as \"OnkoUslugaBeamKindType_Name\",
				beem.OnkoUslugaBeamMethodType_Name as \"OnkoUslugaBeamMethodType_Name\",
				beem.OnkoUslugaBeamRadioModifType_Name as \"OnkoUslugaBeamRadioModifType_Name\",
				to_char(chem.EvnUslugaOnkoChem_setDT, 'dd.mm.yyyy') as \"EvnUslugaOnkoChem_setDate\",
				chem.OnkoUslugaChemKindType_Name as \"OnkoUslugaChemKindType_Name\",
				chem.OnkoDrugChemList as \"OnkoDrugChemList\",
				chem.ChemOslList as \"ChemOslList\",
				to_char(gormun.EvnUslugaOnkoChemBeam_setDT, 'dd.mm.yyyy') as \"EvnUslugaOnkoChemBeam_setDate\",
				gormun.OnkoDrugGormunList as \"OnkoDrugGormunList\",
				gormun.GormunOslList as \"GormunOslList\",
				gormun.EvnUslugaOnkoChemBeam_CountFractionRT as \"EvnUslugaOnkoChemBeam_CountFractionRT\",
				to_char(SpecTreat.MorbusOnkoSpecTreat_specSetDT, 'dd.mm.yyyy') as \"SpecTreatSetDT\",
				org.Org_Name as \"JobOrgName\",
				post.Post_Name as \"JobPostName\"
			from
				v_MorbusOnkoLeave t
				inner join v_EvnSection ev on t.EvnSection_id = ev.EvnSection_id
				inner join v_PersonState PS on ev.Person_id = PS.Person_id
				left join v_Sex on v_Sex.Sex_id = PS.Sex_id
				left join v_Address PSUAddress on PSUAddress.Address_id = PS.UAddress_id
				left join v_Address PSPAddress on PSPAddress.Address_id = PS.PAddress_id
				left join v_MorbusOnkoPerson MOP on MOP.Person_id = PS.Person_id
				left join v_Ethnos on v_Ethnos.Ethnos_id = MOP.Ethnos_id
				left join v_OnkoOccupationClass OOC on OOC.OnkoOccupationClass_id = MOP.OnkoOccupationClass_id
				left join v_Lpu lpu on ev.Lpu_id = lpu.Lpu_id
				left join v_Address LpuAddress on lpu.UAddress_id = LpuAddress.Address_id
				left join v_MedPersonal mp on ev.MedPersonal_id = mp.MedPersonal_id and ev.Lpu_id = mp.Lpu_id
				left join v_Diag diag on t.Diag_id = diag.Diag_id
				left join v_YesNo IsTumorDepoLympha on IsTumorDepoLympha.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoLympha
				left join v_YesNo IsTumorDepoBones on IsTumorDepoBones.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoBones
				left join v_YesNo IsTumorDepoLiver on IsTumorDepoLiver.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoLiver
				left join v_YesNo IsTumorDepoLungs on IsTumorDepoLungs.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoLungs
				left join v_YesNo IsTumorDepoBrain on IsTumorDepoBrain.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoBrain
				left join v_YesNo IsTumorDepoKidney on IsTumorDepoKidney.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoKidney
				left join v_YesNo IsTumorDepoOvary on IsTumorDepoOvary.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoOvary
				left join v_YesNo IsTumorDepoPerito on IsTumorDepoPerito.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoPerito
				left join v_YesNo IsTumorDepoMarrow on IsTumorDepoMarrow.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoMarrow
				left join v_YesNo IsTumorDepoOther on IsTumorDepoOther.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoOther
				left join v_YesNo IsTumorDepoMulti on IsTumorDepoMulti.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoMulti
				left join v_YesNo IsTumorDepoSkin on IsTumorDepoSkin.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoSkin
				left join v_YesNo IsTumorDepoUnknown on IsTumorDepoUnknown.YesNo_id = t.MorbusOnkoLeave_IsTumorDepoUnknown
				left join v_OnkoCombiTreatType on t.OnkoCombiTreatType_id = v_OnkoCombiTreatType.OnkoCombiTreatType_id
				left join v_OnkoDiag on t.OnkoDiag_id = v_OnkoDiag.OnkoDiag_id
				left join v_OnkoDiagConfType on t.OnkoDiagConfType_id = v_OnkoDiagConfType.OnkoDiagConfType_id
				left join v_OnkoM on t.OnkoM_id = v_OnkoM.OnkoM_id
				left join v_OnkoN on t.OnkoN_id = v_OnkoN.OnkoN_id
				left join v_OnkoT on t.OnkoT_id = v_OnkoT.OnkoT_id
				left join v_TumorPrimaryTreatType on t.TumorPrimaryTreatType_id = v_TumorPrimaryTreatType.TumorPrimaryTreatType_id
				left join v_TumorRadicalTreatIncomplType on t.TumorRadicalTreatIncomplType_id = v_TumorRadicalTreatIncomplType.TumorRadicalTreatIncomplType_id
				left join v_TumorStage on t.TumorStage_id = v_TumorStage.TumorStage_id

				left join v_MorbusOnkoBasePS on v_MorbusOnkoBasePS.Evn_id = t.EvnSection_id
				left join v_OnkoPurposeHospType on v_OnkoPurposeHospType.OnkoPurposeHospType_id = v_MorbusOnkoBasePS.OnkoPurposeHospType_id
				left join v_MorbusOnko on v_MorbusOnko.Morbus_id = ev.Morbus_id
				left join v_MorbusOnkoSpecTreat SpecTreat on SpecTreat.MorbusOnko_id = v_MorbusOnko.MorbusOnko_id
				left join v_Job job on job.Job_id = PS.Job_id
				left join v_Org org on org.Org_id = job.Org_id
				left join v_Post post on post.Post_id = job.Post_id

				left join v_YesNo IsFirst on IsFirst.YesNo_id = (
					case when exists (
						select Evn.Evn_id from v_Evn Evn
						left join v_EvnVizitPL PL on Evn.Evn_id = PL.EvnVizitPL_id
						left join v_EvnSection ST on Evn.Evn_id = ST.EvnSection_id
						inner join v_Diag on v_Diag.Diag_id = coalesce(PL.Diag_id,ST.Diag_id)
							and v_Diag.Diag_pid = diag.Diag_pid
						where
							Evn.Person_id = ev.Person_id
							and Evn.EvnClass_id in (11,13,32)
							and Evn.Evn_id != ev.EvnSection_id
							and Evn.Evn_setDT < ev.EvnSection_setDT
						limit 1
					) then 1 else 2 end
				)

				left join lateral(
					select
						s.EvnUslugaOnkoSurg_setDT,
						uc.UslugaComplex_Name as Operation_Name,
						(SELECT
								string_agg(v_AggType.AggType_Name, ', ') as AggType_Name
							FROM v_AggType
							WHERE
								v_AggType.AggType_id in (s.AggType_id,s.AggType_sid)
						) as SurgOslList
					from v_EvnUslugaOnkoSurg s
						left join v_UslugaComplex uc on uc.UslugaComplex_id = s.UslugaComplex_id
					where s.EvnUslugaOnkoSurg_pid = ev.EvnSection_id
					order by s.EvnUslugaOnkoSurg_setDT desc
					limit 1
				) surg on true

				left join lateral(
					select
						s.EvnUslugaOnkoBeam_setDT,
						s.EvnUslugaOnkoBeam_TotalDoseRegZone,
						ru.OnkoUslugaBeamUnitType_Name as TotalDoseRegZone_Unit,
						s.EvnUslugaOnkoBeam_CountFractionRT,
						s.EvnUslugaOnkoBeam_TotalDoseTumor,
						su.OnkoUslugaBeamUnitType_Name as TotalDoseTumor_Unit,
						v_OnkoUslugaBeamKindType.OnkoUslugaBeamKindType_Name,
						v_OnkoUslugaBeamIrradiationType.OnkoUslugaBeamIrradiationType_Name,
						v_OnkoUslugaBeamMethodType.OnkoUslugaBeamMethodType_Name,
						v_OnkoUslugaBeamRadioModifType.OnkoUslugaBeamRadioModifType_Name,
						v_OnkoUslugaBeamFocusType.OnkoUslugaBeamFocusType_Name,
						v_AggType.AggType_Name as BeamOslList
					from v_EvnUslugaOnkoBeam s
						left join v_AggType on v_AggType.AggType_id = s.AggType_id
						left join v_OnkoUslugaBeamKindType on v_OnkoUslugaBeamKindType.OnkoUslugaBeamKindType_id = s.OnkoUslugaBeamKindType_id
						left join v_OnkoUslugaBeamIrradiationType on v_OnkoUslugaBeamIrradiationType.OnkoUslugaBeamIrradiationType_id = s.OnkoUslugaBeamIrradiationType_id
						left join v_OnkoUslugaBeamMethodType on v_OnkoUslugaBeamMethodType.OnkoUslugaBeamMethodType_id = s.OnkoUslugaBeamMethodType_id
						left join v_OnkoUslugaBeamRadioModifType on v_OnkoUslugaBeamRadioModifType.OnkoUslugaBeamRadioModifType_id = s.OnkoUslugaBeamRadioModifType_id
						left join v_OnkoUslugaBeamFocusType on v_OnkoUslugaBeamFocusType.OnkoUslugaBeamFocusType_id = s.OnkoUslugaBeamFocusType_id
						left join v_OnkoUslugaBeamUnitType su on su.OnkoUslugaBeamUnitType_id = s.OnkoUslugaBeamUnitType_id
						left join v_OnkoUslugaBeamUnitType ru on ru.OnkoUslugaBeamUnitType_id = s.OnkoUslugaBeamUnitType_did
					where s.EvnUslugaOnkoBeam_pid = ev.EvnSection_id
					order by s.EvnUslugaOnkoBeam_setDT desc
					limit 1
				) beem on true

				left join lateral(
					select
						s.EvnUslugaOnkoChem_setDT,
						v_OnkoUslugaChemKindType.OnkoUslugaChemKindType_Name,
						(SELECT
								string_agg(v_OnkoDrug.OnkoDrug_Name||' - '||dr.MorbusOnkoDrug_SumDose, ', ')
							FROM v_MorbusOnkoDrug dr
							inner join v_OnkoDrug on v_OnkoDrug.OnkoDrug_id = dr.OnkoDrug_id
							WHERE
								dr.Evn_id = s.EvnUslugaOnkoChem_id
						) as OnkoDrugChemList,
						v_AggType.AggType_Name as ChemOslList
					from v_EvnUslugaOnkoChem s
						left join v_AggType on v_AggType.AggType_id = s.AggType_id
						left join v_OnkoUslugaChemKindType on v_OnkoUslugaChemKindType.OnkoUslugaChemKindType_id = s.OnkoUslugaChemKindType_id
					where s.EvnUslugaOnkoChem_pid = ev.EvnSection_id
					order by s.EvnUslugaOnkoChem_setDT desc
					limit 1
				) chem on true

				left join lateral(
					select
						s.EvnUslugaOnkoChemBeam_setDT,
						s.EvnUslugaOnkoChemBeam_CountFractionRT,
						(SELECT
								string_agg(v_OnkoDrug.OnkoDrug_Name||' - '||dr.MorbusOnkoDrug_SumDose, ', ')
							FROM v_MorbusOnkoDrug dr
							inner join v_OnkoDrug on v_OnkoDrug.OnkoDrug_id = dr.OnkoDrug_id
							WHERE
								dr.Evn_id = s.EvnUslugaOnkoChemBeam_id
						) as OnkoDrugGormunList,
						v_AggType.AggType_Name as GormunOslList
					from v_EvnUslugaOnkoChemBeam s
						left join v_AggType on v_AggType.AggType_id = s.AggType_id
					where s.EvnUslugaOnkoChemBeam_pid = ev.EvnSection_id
					order by s.EvnUslugaOnkoChemBeam_setDT desc
					limit 1
				) gormun on true
			where
				t.MorbusOnkoLeave_id = :MorbusOnkoLeave_id
			limit 1
		";
        /*
                 * непонятно где вводится KLAreaType_id
                        ,v_KLAreaType.KLAreaType_Name
                        left join v_KLAreaType with (nolock) on v_KLAreaType.KLAreaType_id = MOP.KLAreaType_id

                left join v_YesNo IsBeam with (NOLOCK) on IsBeam.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsBeam
                left join v_YesNo IsChem with (NOLOCK) on IsChem.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsChem
                left join v_YesNo IsGormun with (NOLOCK) on IsGormun.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsGormun
                left join v_YesNo IsImmun with (NOLOCK) on IsImmun.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsImmun
                left join v_YesNo IsIntraOper with (NOLOCK) on IsIntraOper.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsIntraOper
                left join v_YesNo IsNotTreat with (NOLOCK) on IsNotTreat.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsNotTreat
                left join v_YesNo IsOther with (NOLOCK) on IsOther.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsOther
                left join v_YesNo IsPostOper with (NOLOCK) on IsPostOper.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsPostOper
                left join v_YesNo IsPreOper with (NOLOCK) on IsPreOper.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsPreOper
                left join v_YesNo IsTreatDelay with (NOLOCK) on IsTreatDelay.YesNo_id = v_MorbusOnkoBasePS.MorbusOnkoBasePS_IsTreatDelay
                left join v_OnkoLeaveType with (NOLOCK) on v_OnkoLeaveType.OnkoLeaveType_id = v_MorbusOnkoBasePS.OnkoLeaveType_id
                left join v_OnkoHospType with (NOLOCK) on v_OnkoHospType.OnkoHospType_id = v_MorbusOnkoBasePS.OnkoHospType_id--повторная, но первичная в этом году
                */
        // echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
}