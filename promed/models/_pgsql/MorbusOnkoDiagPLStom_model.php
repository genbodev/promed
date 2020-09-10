<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Талон дополнений на онкобольного
 *
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @package      MorbusOnko
 */
class MorbusOnkoDiagPLStom_model extends swPgModel
{

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
	 * Cохранение талона дополнений
	 * Должно происходить только после сохранения специфики из посещения
	 * Принимает 3 обязательных параметра: EvnDiagPLStom_id, MorbusOnko_id и pmUser_id
	 */
	function save($data) {
		$p = array(
			'EvnDiagPLStom_id' => $data['Evn_pid'],
			'MorbusOnko_id' => $data['MorbusOnko_id'],
			'EvnDiagPLStomSop_id' => $data['EvnDiagPLStomSop_id'],
		);
		$q = "
			select 				
				 T.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\"
				,Evn.EvnDiagPLStom_id as \"EvnDiagPLStom_id\"
				,coalesce(EDPLS.Diag_id,Evn.Diag_id) as \"Diag_id\"
				,substring(to_char(Evn.EvnDiagPLStom_setDT, 'yyyy-mm-dd') ,1, 10) as \"MorbusOnkoDiagPLStom_setDT\"
				,EVPLS.MedPersonal_id as \"MedPersonal_id\"
				,MOB.OnkoInvalidType_id as \"OnkoInvalidType_id\"
				,substring(to_char(MOB.MorbusOnkoBase_deadDT, 'yyyy-mm-dd'), 1, 10) as \"MorbusOnkoDiagPLStom_deadDT\"
				,MOB.MorbusOnkoBase_deathCause as \"MorbusOnkoDiagPLStom_deathCause\"
				,MOBPS.MorbusOnkoBasePersonState_id as \"MorbusOnkoBasePersonState_id\"
				,MO.OnkoLateComplTreatType_id as \"OnkoLateComplTreatType_id\"
				,MO.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\"
				,MOBLCT.MorbusOnkoBaseLateComplTreat_id as \"MorbusOnkoBaseLateComplTreat_id\"
			from
				v_EvnDiagPLStom Evn
				inner join v_EvnVizitPLStom EVPLS on EVPLS.EvnVizitPLStom_id = Evn.EvnDiagPLStom_pid
				inner join v_MorbusOnko MO on MO.MorbusOnko_id = :MorbusOnko_id
				inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
				inner join v_MorbusOnkoBase MOB on MOB.MorbusBase_id = M.MorbusBase_id
				left join v_MorbusOnkoDiagPLStom T on T.EvnDiagPLStom_id = Evn.EvnDiagPLStom_id
					and coalesce(T.EvnDiagPLStomSop_id, 0) = coalesce(:EvnDiagPLStomSop_id, 0)
				left join v_EvnDiagPLStomSop EDPLS on EDPLS.EvnDiagPLStomSop_id = :EvnDiagPLStomSop_id
				left join lateral(
					select
						MorbusOnkoBasePersonState_id
					from v_MorbusOnkoBasePersonState S
					where MOB.MorbusOnkoBase_id = S.MorbusOnkoBase_id
					ORDER BY S.MorbusOnkoBasePersonState_insDT DESC
					limit 1
				) MOBPS on true
				left join lateral(
					select
						MorbusOnkoBaseLateComplTreat_id
					from v_MorbusOnkoBaseLateComplTreat Tr
					where MOB.MorbusOnkoBase_id = Tr.MorbusOnkoBase_id
					ORDER BY Tr.MorbusOnkoBaseLateComplTreat_insDT DESC
					limit 1
				) MOBLCT on true
			where
				Evn.EvnDiagPLStom_id = :EvnDiagPLStom_id
		";
		// echo getDebugSQL($q, $p); exit;
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$ra = $r->result('array');
			if (empty($ra) || !is_array($ra[0]) || empty($ra[0]))
			{
				return array(array('Error_Msg' => 'Получение данных для талона дополнений. Данные не получены'));	
			}
			foreach($ra[0] as $key => $val) {
				$data[$key] = $val;
			}
		} else {
			return array(array('Error_Msg' => 'Получение данных для талона дополнений. Ошибка запроса к БД'));	
		}
		
		$action = (isset($data['MorbusOnkoDiagPLStom_id']))?'upd':'ins';
		$p = array(
			'MorbusOnkoDiagPLStom_id' => $data['MorbusOnkoDiagPLStom_id'],
			'EvnDiagPLStom_id' => $data['Evn_pid'],
			'Diag_id' => $data['Diag_id'],
			'MorbusOnkoDiagPLStom_setDT' => $data['MorbusOnkoDiagPLStom_setDT'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'OnkoRegOutType_id' => $data['OnkoRegOutType_id'],
			'OnkoInvalidType_id' => $data['OnkoInvalidType_id'],
			'OnkoDiag_id' => $data['OnkoDiag_mid'],
			'OnkoDiag_mid' => $data['OnkoDiag_mid'],
			'OnkoT_id' => $data['OnkoT_id'],
			'OnkoN_id' => $data['OnkoN_id'],
			'OnkoM_id' => $data['OnkoM_id'],
			'TumorStage_id' => $data['TumorStage_id'],
			'OnkoT_fid' => $data['OnkoT_fid'],
			'OnkoN_fid' => $data['OnkoN_fid'],
			'OnkoM_fid' => $data['OnkoM_fid'],
			'TumorStage_fid' => $data['TumorStage_fid'],
			'MorbusOnkoDiagPLStom_IsTumorDepoUnknown' => $data['MorbusOnko_IsTumorDepoUnknown'],
			'MorbusOnkoDiagPLStom_IsTumorDepoLympha' => $data['MorbusOnko_IsTumorDepoLympha'],
			'MorbusOnkoDiagPLStom_IsTumorDepoBones' => $data['MorbusOnko_IsTumorDepoBones'],
			'MorbusOnkoDiagPLStom_IsTumorDepoLiver' => $data['MorbusOnko_IsTumorDepoLiver'],
			'MorbusOnkoDiagPLStom_IsTumorDepoLungs' => $data['MorbusOnko_IsTumorDepoLungs'],
			'MorbusOnkoDiagPLStom_IsTumorDepoBrain' => $data['MorbusOnko_IsTumorDepoBrain'],
			'MorbusOnkoDiagPLStom_IsTumorDepoSkin' => $data['MorbusOnko_IsTumorDepoSkin'],
			'MorbusOnkoDiagPLStom_IsTumorDepoKidney' => $data['MorbusOnko_IsTumorDepoKidney'],
			'MorbusOnkoDiagPLStom_IsTumorDepoOvary' => $data['MorbusOnko_IsTumorDepoOvary'],
			'MorbusOnkoDiagPLStom_IsTumorDepoPerito' => $data['MorbusOnko_IsTumorDepoPerito'],
			'MorbusOnkoDiagPLStom_IsTumorDepoMarrow' => $data['MorbusOnko_IsTumorDepoMarrow'],
			'MorbusOnkoDiagPLStom_IsTumorDepoOther' => $data['MorbusOnko_IsTumorDepoOther'],
			'MorbusOnkoDiagPLStom_IsTumorDepoMulti' => $data['MorbusOnko_IsTumorDepoMulti'],
			'MorbusOnkoDiagPLStom_deadDT' => $data['MorbusOnkoDiagPLStom_deadDT'],
			'Diag_did' => $data['Diag_did'],
			'MorbusOnkoDiagPLStom_deathCause' => $data['MorbusOnkoDiagPLStom_deathCause'],
			'AutopsyPerformType_id' => $data['AutopsyPerformType_id'],
			'TumorAutopsyResultType_id' => $data['TumorAutopsyResultType_id'],
			'MorbusOnkoBasePersonState_id' => $data['MorbusOnkoBasePersonState_id'],
			'MorbusOnkoBaseLateComplTreat_id' => $data['MorbusOnkoBaseLateComplTreat_id'],
			'OnkoStatusYearEndType_id' => $data['OnkoStatusYearEndType_id'],
			'OnkoDiagConfType_id' => $data['OnkoDiagConfType_id'],
			'OnkoLateComplTreatType_id' => $data['OnkoLateComplTreatType_id'],
			'OnkoTumorStatusType_id' => $data['OnkoTumorStatusType_id'],
			'MorbusOnkoDiagPLStom_takeDT' => !empty($data['MorbusOnko_takeDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_takeDT'])) : null,
			'MorbusOnkoDiagPLStom_setDiagDT' => !empty($data['MorbusOnko_setDiagDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_setDiagDT'])) : null,
			'HistologicReasonType_id' => $data['HistologicReasonType_id'],
			'MorbusOnkoDiagPLStom_histDT' => !empty($data['MorbusOnko_histDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_histDT'])) : null,
			'DiagAttribType_id' => $data['DiagAttribType_id'],
			'OnkoLesionSide_id' => $data['OnkoLesionSide_id'],
			'MorbusOnkoDiagPLStom_NumHisto' => $data['MorbusOnko_NumHisto'],
			'TumorCircumIdentType_id' => $data['TumorCircumIdentType_id'],
			'OnkoLateDiagCause_id' => $data['OnkoLateDiagCause_id'],
			'OnkoPostType_id' => $data['OnkoPostType_id'],
			'MorbusOnkoDiagPLStom_IsMainTumor' => $data['MorbusOnko_IsMainTumor'],
			'OnkoTreatment_id' => $data['OnkoTreatment_id'],
			'EvnDiagPLStomSop_id' => !empty($data['EvnDiagPLStomSop_id']) ? $data['EvnDiagPLStomSop_id'] : null,
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
				MorbusOnkoDiagPLStom_id as "MorbusOnkoDiagPLStom_id",
				Error_Code as "Error_Code",
				Error_Message as "Error_Msg"
			from dbo.p_MorbusOnkoDiagPLStom_'. $action .'(
				MorbusOnkoDiagPLStom_id := :MorbusOnkoDiagPLStom_id,
				EvnDiagPLStom_id := :EvnDiagPLStom_id,
				Diag_id := :Diag_id,
				OnkoDiag_id := :OnkoDiag_id,
				OnkoDiag_mid := :OnkoDiag_mid,
				OnkoT_id := :OnkoT_id,
				OnkoN_id := :OnkoN_id,
				OnkoM_id := :OnkoM_id,
				TumorStage_id := :TumorStage_id,
				OnkoT_fid := :OnkoT_fid,
				OnkoN_fid := :OnkoN_fid,
				OnkoM_fid := :OnkoM_fid,
				TumorStage_fid := :TumorStage_fid,
				MorbusOnkoDiagPLStom_IsTumorDepoUnknown := :MorbusOnkoDiagPLStom_IsTumorDepoUnknown,
				MorbusOnkoDiagPLStom_IsTumorDepoLympha := :MorbusOnkoDiagPLStom_IsTumorDepoLympha,
				MorbusOnkoDiagPLStom_IsTumorDepoBones := :MorbusOnkoDiagPLStom_IsTumorDepoBones,
				MorbusOnkoDiagPLStom_IsTumorDepoLiver := :MorbusOnkoDiagPLStom_IsTumorDepoLiver,
				MorbusOnkoDiagPLStom_IsTumorDepoLungs := :MorbusOnkoDiagPLStom_IsTumorDepoLungs,
				MorbusOnkoDiagPLStom_IsTumorDepoBrain := :MorbusOnkoDiagPLStom_IsTumorDepoBrain,
				MorbusOnkoDiagPLStom_IsTumorDepoSkin := :MorbusOnkoDiagPLStom_IsTumorDepoSkin,
				MorbusOnkoDiagPLStom_IsTumorDepoKidney := :MorbusOnkoDiagPLStom_IsTumorDepoKidney,
				MorbusOnkoDiagPLStom_IsTumorDepoOvary := :MorbusOnkoDiagPLStom_IsTumorDepoOvary,
				MorbusOnkoDiagPLStom_IsTumorDepoPerito := :MorbusOnkoDiagPLStom_IsTumorDepoPerito,
				MorbusOnkoDiagPLStom_IsTumorDepoMarrow := :MorbusOnkoDiagPLStom_IsTumorDepoMarrow,
				MorbusOnkoDiagPLStom_IsTumorDepoOther := :MorbusOnkoDiagPLStom_IsTumorDepoOther,
				MorbusOnkoDiagPLStom_IsTumorDepoMulti := :MorbusOnkoDiagPLStom_IsTumorDepoMulti,
				TumorAutopsyResultType_id := :TumorAutopsyResultType_id,
				OnkoStatusYearEndType_id := :OnkoStatusYearEndType_id,
				OnkoDiagConfType_id := :OnkoDiagConfType_id,
				OnkoLateComplTreatType_id := :OnkoLateComplTreatType_id,
				OnkoTumorStatusType_id := :OnkoTumorStatusType_id,
				MorbusOnkoDiagPLStom_setDiagDT := :MorbusOnkoDiagPLStom_setDiagDT,
				MorbusOnkoDiagPLStom_takeDT := :MorbusOnkoDiagPLStom_takeDT,
				DiagAttribType_id := :DiagAttribType_id,
				DiagAttribDict_id := :DiagAttribDict_id,
				DiagResult_id := :DiagResult_id,
				DiagAttribDict_fid := :DiagAttribDict_fid,
				DiagResult_fid := :DiagResult_fid,
				OnkoLesionSide_id := :OnkoLesionSide_id,
				MorbusOnkoDiagPLStom_NumHisto := :MorbusOnkoDiagPLStom_NumHisto,
				TumorCircumIdentType_id := :TumorCircumIdentType_id,
				OnkoLateDiagCause_id := :OnkoLateDiagCause_id,
				OnkoPostType_id := :OnkoPostType_id,
				MorbusOnkoDiagPLStom_IsMainTumor := :MorbusOnkoDiagPLStom_IsMainTumor,
				OnkoTreatment_id := :OnkoTreatment_id,
				EvnDiagPLStomSop_id := :EvnDiagPLStomSop_id,
				HistologicReasonType_id := :HistologicReasonType_id,
				MorbusOnkoDiagPLStom_histDT := :MorbusOnkoDiagPLStom_histDT,
				pmUser_id := :pmUser_id
			)
		';
		// echo getDebugSQL($q, $p); exit;
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			return $r->result('array');
		} else {
			//log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}
	
	/**
	 * Метод получения данных для просмотра в ЭМК
	 */
	function loadViewData($data)
	{
		$query = "
			select
				 t.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\"
				,t.EvnDiagPLStom_id as \"MorbusOnkoDiagPLStom_pid\"
				,to_char(t.MorbusOnkoDiagPLStom_setDT, 'dd.mm.yyyy') as \"MorbusOnkoDiagPLStom_setDate\" -- Дата осмотра или получения сведений 
				,lpu.Lpu_Name as \"Lpu_Name\"
				,pc.PersonCard_Code as \"PersonCard_Code\"
				,PS.Person_SurName as \"Person_SurName\"
				,PS.Person_FirName as \"Person_FirName\"
				,PS.Person_SecName as \"Person_SecName\"
				,coalesce(rtrim(Address1.Address_Address), '') as \"Person_Address\"
				,diag.Diag_FullName as \"Diag_FullName\"
				,orot.OnkoRegOutType_Name as \"OnkoRegOutType_Name\"
				,oit.OnkoInvalidType_Name as \"OnkoInvalidType_Name\"
				,opst.OnkoPersonStateType_Name as \"OnkoPersonStateType_Name\"
				,otst.OnkoTumorStatusType_Name as \"OnkoTumorStatusType_Name\"
				,osyet.OnkoStatusYearEndType_Name as \"OnkoStatusYearEndType_Name\"
				,to_char(t.MorbusOnkoDiagPLStom_deadDT, 'dd.mm.yyyy') as \"MorbusOnkoDiagPLStom_deadDate\"
				,t.MorbusOnkoDiagPLStom_deathCause as \"MorbusOnkoDiagPLStom_deathCause\"
				,apt.AutopsyPerformType_Name as \"AutopsyPerformType_Name\"
				,tart.TumorAutopsyResultType_Name as \"TumorAutopsyResultType_Name\"
				,mp.Person_Fin as \"MedPersonal_Fin\"
				,olctt.OnkoLateComplTreatType_Name as \"OnkoLateComplTreatType_Name\"
			from
				v_MorbusOnkoDiagPLStom t
				inner join v_EvnDiagPLStom ev on t.EvnDiagPLStom_id = ev.EvnDiagPLStom_id
				inner join v_PersonState PS on ev.Person_id = PS.Person_id
				left join v_Lpu lpu on ev.Lpu_id = lpu.Lpu_id
				left join v_MedPersonal mp on t.MedPersonal_id = mp.MedPersonal_id
					and ev.Lpu_id = mp.Lpu_id
				left join v_PersonCard pc on ev.Person_id = pc.Person_id
					and pc.LpuAttachType_id = 1
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
			where
				t.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id
			limit 1
		";
		// echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}