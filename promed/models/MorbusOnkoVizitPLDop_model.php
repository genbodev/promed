<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Талон дополнений на онкобольного
 *
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      12.2014
 */
class MorbusOnkoVizitPLDop_model extends swModel
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
	 * Принимает 3 обязательных параметра: EvnVizitPL_id, MorbusOnko_id и pmUser_id
	 */
	function save($data,$evnclass = null) {
		$p = array(
			'Evn_pid' => $data['Evn_pid'],
			'MorbusOnko_id' => $data['MorbusOnko_id'],
			'EvnDiagPLSop_id' => $data['EvnDiagPLSop_id'],
		);
		if(empty($evnclass)){
			$evnclass = $this->getFirstResultFromQuery('select EvnClass_SysNick from v_Evn (nolock) where Evn_id = :Evn_pid', $p);
		}
		// Перестраховка на всякий случай
		if(empty($evnclass) || !in_array($evnclass,array('EvnVizitPL','EvnVizitDispDop')))
			$evnclass = 'EvnVizitPL';
		$spdiag = $evnclass == 'EvnVizitDispDop' ? 'EPLDD13.Diag_spid' : 'Evn.Diag_spid';
		$q = '
			select 				
				T.MorbusOnkoVizitPLDop_id 
				,Evn.'.$evnclass.'_id 
				,coalesce(ED.Diag_id,'.$spdiag.',Evn.Diag_id) as Diag_id
				,substring(convert(varchar, Evn.'.$evnclass.'_setDT, 120),1,10) as MorbusOnkoVizitPLDop_setDT 
				,Evn.MedPersonal_id 
				,MOB.OnkoInvalidType_id
				,substring(convert(varchar, MOB.MorbusOnkoBase_deadDT, 120),1,10) as MorbusOnkoVizitPLDop_deadDT
				,MOB.MorbusOnkoBase_deathCause as MorbusOnkoVizitPLDop_deathCause
				,MOBPS.MorbusOnkoBasePersonState_id
				,MO.OnkoLateComplTreatType_id
				,MO.OnkoTumorStatusType_id
				,MOBLCT.MorbusOnkoBaseLateComplTreat_id
			from
				v_'.$evnclass.' Evn WITH (NOLOCK)
				inner join v_MorbusOnko MO WITH (NOLOCK) on MO.MorbusOnko_id = :MorbusOnko_id
				inner join v_Morbus M WITH (NOLOCK) on M.Morbus_id = MO.Morbus_id
				inner join v_MorbusOnkoBase MOB WITH (NOLOCK) on MOB.MorbusBase_id = M.MorbusBase_id
				left join v_MorbusOnkoVizitPLDop T WITH (NOLOCK) on T.EvnVizit_id = Evn.'.$evnclass.'_id and isnull(T.EvnDiagPLSop_id,0) = isnull(:EvnDiagPLSop_id,0)
				left join v_EvnDiag ED WITH (NOLOCK) on ED.EvnDiag_id = :EvnDiagPLSop_id
				left join v_EvnPLDispDop13 EPLDD13 with (nolock) on EPLDD13.EvnPLDispDop13_id = Evn.'.$evnclass.'_pid
				outer apply (
					select top 1 MorbusOnkoBasePersonState_id
					from v_MorbusOnkoBasePersonState S WITH (NOLOCK)
					where MOB.MorbusOnkoBase_id = S.MorbusOnkoBase_id
					ORDER BY S.MorbusOnkoBasePersonState_insDT DESC
				) MOBPS
				outer apply (
					select top 1 MorbusOnkoBaseLateComplTreat_id
					from v_MorbusOnkoBaseLateComplTreat Tr WITH (NOLOCK)
					where MOB.MorbusOnkoBase_id = Tr.MorbusOnkoBase_id
					ORDER BY Tr.MorbusOnkoBaseLateComplTreat_insDT DESC
				) MOBLCT
			where
				Evn.'.$evnclass.'_id = :Evn_pid
		';
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
		
		$action = (isset($data['MorbusOnkoVizitPLDop_id']))?'upd':'ins';
		$p = array(
			'MorbusOnkoVizitPLDop_id' => $data['MorbusOnkoVizitPLDop_id'],
			'EvnVizit_id' => $data['Evn_pid'],
			'Diag_id' => $data['Diag_id'],
			'MorbusOnkoVizitPLDop_setDT' => $data['MorbusOnkoVizitPLDop_setDT'],
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
			'MorbusOnkoVizitPLDop_IsTumorDepoUnknown' => $data['MorbusOnko_IsTumorDepoUnknown'],
			'MorbusOnkoVizitPLDop_IsTumorDepoLympha' => $data['MorbusOnko_IsTumorDepoLympha'],
			'MorbusOnkoVizitPLDop_IsTumorDepoBones' => $data['MorbusOnko_IsTumorDepoBones'],
			'MorbusOnkoVizitPLDop_IsTumorDepoLiver' => $data['MorbusOnko_IsTumorDepoLiver'],
			'MorbusOnkoVizitPLDop_IsTumorDepoLungs' => $data['MorbusOnko_IsTumorDepoLungs'],
			'MorbusOnkoVizitPLDop_IsTumorDepoBrain' => $data['MorbusOnko_IsTumorDepoBrain'],
			'MorbusOnkoVizitPLDop_IsTumorDepoSkin' => $data['MorbusOnko_IsTumorDepoSkin'],
			'MorbusOnkoVizitPLDop_IsTumorDepoKidney' => $data['MorbusOnko_IsTumorDepoKidney'],
			'MorbusOnkoVizitPLDop_IsTumorDepoOvary' => $data['MorbusOnko_IsTumorDepoOvary'],
			'MorbusOnkoVizitPLDop_IsTumorDepoPerito' => $data['MorbusOnko_IsTumorDepoPerito'],
			'MorbusOnkoVizitPLDop_IsTumorDepoMarrow' => $data['MorbusOnko_IsTumorDepoMarrow'],
			'MorbusOnkoVizitPLDop_IsTumorDepoOther' => $data['MorbusOnko_IsTumorDepoOther'],
			'MorbusOnkoVizitPLDop_IsTumorDepoMulti' => $data['MorbusOnko_IsTumorDepoMulti'],
			'MorbusOnkoVizitPLDop_deadDT' => $data['MorbusOnkoVizitPLDop_deadDT'],
			'Diag_did' => $data['Diag_did'],
			'MorbusOnkoVizitPLDop_deathCause' => $data['MorbusOnkoVizitPLDop_deathCause'],
			'AutopsyPerformType_id' => $data['AutopsyPerformType_id'],
			'TumorAutopsyResultType_id' => $data['TumorAutopsyResultType_id'],
			'MorbusOnkoBasePersonState_id' => $data['MorbusOnkoBasePersonState_id'],
			'MorbusOnkoBaseLateComplTreat_id' => $data['MorbusOnkoBaseLateComplTreat_id'],
			'OnkoStatusYearEndType_id' => $data['OnkoStatusYearEndType_id'],
			'OnkoDiagConfType_id' => $data['OnkoDiagConfType_id'],
			'OnkoLateComplTreatType_id' => $data['OnkoLateComplTreatType_id'],
			'OnkoTumorStatusType_id' => $data['OnkoTumorStatusType_id'],
			'MorbusOnkoVizitPLDop_setDiagDT' => !empty($data['MorbusOnko_setDiagDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_setDiagDT'])) : null,
			'MorbusOnkoVizitPLDop_takeDT' => !empty($data['MorbusOnko_takeDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_takeDT'])) : null,
			'HistologicReasonType_id' => $data['HistologicReasonType_id'],
			'MorbusOnkoVizitPLDop_histDT' => !empty($data['MorbusOnko_histDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_histDT'])) : null,
			'DiagAttribType_id' => $data['DiagAttribType_id'],
			'OnkoLesionSide_id' => $data['OnkoLesionSide_id'],
			'MorbusOnkoVizitPLDop_NumHisto' => $data['MorbusOnko_NumHisto'],
			'MorbusOnkoVizitPLDop_NumTumor' => $data['MorbusOnko_NumTumor'],
			'TumorCircumIdentType_id' => $data['TumorCircumIdentType_id'],
			'OnkoLateDiagCause_id' => $data['OnkoLateDiagCause_id'],
			'OnkoPostType_id' => $data['OnkoPostType_id'],
			'MorbusOnkoVizitPLDop_IsMainTumor' => $data['MorbusOnko_IsMainTumor'],
			'OnkoTreatment_id' => $data['OnkoTreatment_id'],
			'MorbusOnkoVizitPLDop_FirstSignDT' => !empty($data['MorbusOnko_firstSignDT']) ? date('Y-m-d', strtotime($data['MorbusOnko_firstSignDT'])) : null,
			'TumorPrimaryMultipleType_id' => $data['TumorPrimaryMultipleType_id'],
			'EvnDiagPLSop_id' => !empty($data['EvnDiagPLSop_id']) ? $data['EvnDiagPLSop_id'] : null,
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
			declare
				@MorbusOnkoVizitPLDop_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id;
			exec dbo.p_MorbusOnkoVizitPLDop_'. $action .'
				@MorbusOnkoVizitPLDop_id = @MorbusOnkoVizitPLDop_id output,
				@EvnVizit_id = :EvnVizit_id,
				@Diag_id = :Diag_id,
				@MorbusOnkoVizitPLDop_setDT = :MorbusOnkoVizitPLDop_setDT,
				@MedPersonal_id = :MedPersonal_id,
				@OnkoRegOutType_id = :OnkoRegOutType_id,
				@OnkoInvalidType_id = :OnkoInvalidType_id,
				@OnkoDiag_id = :OnkoDiag_id,
				@OnkoDiag_mid = :OnkoDiag_mid,
				@OnkoT_id = :OnkoT_id,
				@OnkoN_id = :OnkoN_id,
				@OnkoM_id = :OnkoM_id,
				@TumorStage_id = :TumorStage_id,
				@OnkoT_fid = :OnkoT_fid,
				@OnkoN_fid = :OnkoN_fid,
				@OnkoM_fid = :OnkoM_fid,
				@TumorStage_fid = :TumorStage_fid,
				@MorbusOnkoVizitPLDop_IsTumorDepoUnknown = :MorbusOnkoVizitPLDop_IsTumorDepoUnknown,
				@MorbusOnkoVizitPLDop_IsTumorDepoLympha = :MorbusOnkoVizitPLDop_IsTumorDepoLympha,
				@MorbusOnkoVizitPLDop_IsTumorDepoBones = :MorbusOnkoVizitPLDop_IsTumorDepoBones,
				@MorbusOnkoVizitPLDop_IsTumorDepoLiver = :MorbusOnkoVizitPLDop_IsTumorDepoLiver,
				@MorbusOnkoVizitPLDop_IsTumorDepoLungs = :MorbusOnkoVizitPLDop_IsTumorDepoLungs,
				@MorbusOnkoVizitPLDop_IsTumorDepoBrain = :MorbusOnkoVizitPLDop_IsTumorDepoBrain,
				@MorbusOnkoVizitPLDop_IsTumorDepoSkin = :MorbusOnkoVizitPLDop_IsTumorDepoSkin,
				@MorbusOnkoVizitPLDop_IsTumorDepoKidney = :MorbusOnkoVizitPLDop_IsTumorDepoKidney,
				@MorbusOnkoVizitPLDop_IsTumorDepoOvary = :MorbusOnkoVizitPLDop_IsTumorDepoOvary,
				@MorbusOnkoVizitPLDop_IsTumorDepoPerito = :MorbusOnkoVizitPLDop_IsTumorDepoPerito,
				@MorbusOnkoVizitPLDop_IsTumorDepoMarrow = :MorbusOnkoVizitPLDop_IsTumorDepoMarrow,
				@MorbusOnkoVizitPLDop_IsTumorDepoOther = :MorbusOnkoVizitPLDop_IsTumorDepoOther,
				@MorbusOnkoVizitPLDop_IsTumorDepoMulti = :MorbusOnkoVizitPLDop_IsTumorDepoMulti,
				@MorbusOnkoVizitPLDop_deadDT = :MorbusOnkoVizitPLDop_deadDT,
				@Diag_did = :Diag_did,
				@MorbusOnkoVizitPLDop_deathCause = :MorbusOnkoVizitPLDop_deathCause,
				@AutopsyPerformType_id = :AutopsyPerformType_id,
				@TumorAutopsyResultType_id = :TumorAutopsyResultType_id,
				@MorbusOnkoBasePersonState_id = :MorbusOnkoBasePersonState_id,
				@MorbusOnkoBaseLateComplTreat_id = :MorbusOnkoBaseLateComplTreat_id,
				@OnkoStatusYearEndType_id = :OnkoStatusYearEndType_id,
				@OnkoDiagConfType_id = :OnkoDiagConfType_id,
				@OnkoLateComplTreatType_id = :OnkoLateComplTreatType_id,
				@OnkoTumorStatusType_id = :OnkoTumorStatusType_id,
				@MorbusOnkoVizitPLDop_setDiagDT = :MorbusOnkoVizitPLDop_setDiagDT,
				@MorbusOnkoVizitPLDop_takeDT = :MorbusOnkoVizitPLDop_takeDT,
				@DiagAttribType_id = :DiagAttribType_id,
				@DiagAttribDict_id = :DiagAttribDict_id,
				@DiagResult_id = :DiagResult_id,
				@DiagAttribDict_fid = :DiagAttribDict_fid,
				@DiagResult_fid = :DiagResult_fid,
				@OnkoLesionSide_id = :OnkoLesionSide_id,
				@MorbusOnkoVizitPLDop_NumHisto = :MorbusOnkoVizitPLDop_NumHisto,
				@MorbusOnkoVizitPLDop_NumTumor = :MorbusOnkoVizitPLDop_NumTumor,
				@TumorCircumIdentType_id = :TumorCircumIdentType_id,
				@OnkoLateDiagCause_id = :OnkoLateDiagCause_id,
				@OnkoPostType_id = :OnkoPostType_id,
				@MorbusOnkoVizitPLDop_IsMainTumor = :MorbusOnkoVizitPLDop_IsMainTumor,
				@OnkoTreatment_id = :OnkoTreatment_id,
				@MorbusOnkoVizitPLDop_FirstSignDT = :MorbusOnkoVizitPLDop_FirstSignDT,
				@TumorPrimaryMultipleType_id = :TumorPrimaryMultipleType_id,
				@HistologicReasonType_id = :HistologicReasonType_id,
				@MorbusOnkoVizitPLDop_histDT = :MorbusOnkoVizitPLDop_histDT,
				@EvnDiagPLSop_id = :EvnDiagPLSop_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MorbusOnkoVizitPLDop_id as MorbusOnkoVizitPLDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		';
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
			'MorbusOnkoVizitPLDop_id' => $data['MorbusOnkoVizitPLDop_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		foreach($subsect_list as $subsect) {
			$mol_list = $this->queryList("select {$subsect}_id from {$subsect} (nolock) where MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id", 
				array('MorbusOnkoVizitPLDop_id' => $data['MorbusOnkoVizitPLDop_id'])
			);
			foreach($mol_list as $ml) {
				$this->execCommonSP("dbo.p_{$subsect}_del", array("{$subsect}_id" => $ml));
			}
		}

		$tmp = $this->getFirstRowFromQuery("
			select top 1 mol.MorbusOnkoVizitPLDop_id as moid, mol.Diag_id, evn.Person_id, M.Morbus_id, evn.EvnVizitPL_id as Evn_id
			from v_MorbusOnkoVizitPLDop mol (nolock)
			inner join v_EvnVizitPL evn (nolock) on evn.EvnVizitPL_id = mol.EvnVizit_id
			outer apply (
				select top 1 M.Morbus_id, 
				case when M.Morbus_id = evn.Morbus_id then 0 else 1 end as msort
				from v_Morbus M with (nolock) 
				inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
				inner join v_Diag MD with (nolock) on M.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
				where M.Person_id = evn.Person_id
				order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
			) M
			where 
				mol.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id and 
				EvnDiagPLSop_id is null
		", array(
			'MorbusOnkoVizitPLDop_id' => $data['MorbusOnkoVizitPLDop_id']
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_MorbusOnkoVizitPLDop_del
				@MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				t.MorbusOnkoVizitPLDop_id
				,t.EvnVizit_id as MorbusOnkoVizitPLDop_pid
				,convert(varchar(10),t.MorbusOnkoVizitPLDop_setDT,104) as MorbusOnkoVizitPLDop_setDate -- Дата осмотра или получения сведений 
				,lpu.Lpu_Name
				,pc.PersonCard_Code
				,PS.Person_SurName
				,PS.Person_FirName
				,PS.Person_SecName
				,isnull(rtrim(Address1.Address_Address), '') as Person_Address
				,diag.Diag_FullName
				,orot.OnkoRegOutType_Name
				,oit.OnkoInvalidType_Name
				,opst.OnkoPersonStateType_Name
				,otst.OnkoTumorStatusType_Name
				,osyet.OnkoStatusYearEndType_Name
				,convert(varchar(10),t.MorbusOnkoVizitPLDop_deadDT,104) as MorbusOnkoVizitPLDop_deadDate
				,t.MorbusOnkoVizitPLDop_deathCause
				,apt.AutopsyPerformType_Name
				,tart.TumorAutopsyResultType_Name
				,mp.Person_Fin as MedPersonal_Fin
				,olctt.OnkoLateComplTreatType_Name
			from
				v_MorbusOnkoVizitPLDop t with (NOLOCK)
				inner join v_EvnVizitPL ev with (NOLOCK) on t.EvnVizit_id = ev.EvnVizitPL_id
				inner join v_PersonState PS with (nolock) on ev.Person_id = PS.Person_id
				left join v_Lpu lpu with (NOLOCK) on ev.Lpu_id = lpu.Lpu_id
				left join v_MedPersonal mp with (NOLOCK) on t.MedPersonal_id = mp.MedPersonal_id and ev.Lpu_id = mp.Lpu_id
				left join v_PersonCard pc with (NOLOCK) on ev.Person_id = pc.Person_id and pc.LpuAttachType_id = 1
				left join v_Address Address1 with (nolock) on PS.UAddress_id = Address1.Address_id
				left join v_Diag diag with (NOLOCK) on t.Diag_id = diag.Diag_id
				left join v_OnkoRegOutType orot with (NOLOCK) on t.OnkoRegOutType_id = orot.OnkoRegOutType_id
				left join v_AutopsyPerformType apt with (NOLOCK) on t.AutopsyPerformType_id = apt.AutopsyPerformType_id
				left join v_TumorAutopsyResultType tart with (NOLOCK) on t.TumorAutopsyResultType_id = tart.TumorAutopsyResultType_id
				left join v_OnkoInvalidType oit with (NOLOCK) on t.OnkoInvalidType_id = oit.OnkoInvalidType_id
				left join v_MorbusOnkoBasePersonState mobps with (NOLOCK) on t.MorbusOnkoBasePersonState_id = mobps.MorbusOnkoBasePersonState_id
				left join v_OnkoPersonStateType opst with (NOLOCK) on mobps.OnkoPersonStateType_id = opst.OnkoPersonStateType_id
				left join v_OnkoTumorStatusType otst with (NOLOCK) on t.OnkoTumorStatusType_id = otst.OnkoTumorStatusType_id
				left join v_OnkoStatusYearEndType osyet with (NOLOCK) on t.OnkoStatusYearEndType_id = osyet.OnkoStatusYearEndType_id
				left join v_OnkoLateComplTreatType olctt with (NOLOCK) on olctt.OnkoLateComplTreatType_id = t.OnkoLateComplTreatType_id
			where
				t.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id
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