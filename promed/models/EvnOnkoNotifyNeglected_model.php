<?php
/**
* EvnOnkoNotifyNeglected_model - модель для работы с таблицей EvnOnkoNotifyNeglected
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
*/

class EvnOnkoNotifyNeglected_model extends CI_Model {

	/**
	 * construct
	 */
	function __construct ()
	{
		parent::__construct();
	}

	/**
	 * save
	 */
	function save($data)
	{

		if ( empty($data['EvnOnkoNotifyNeglected_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnOnkoNotifyNeglected_id;
			exec p_EvnOnkoNotifyNeglected_' . $procedure_action . '
				@EvnOnkoNotifyNeglected_id = @Res output,
				@EvnOnkoNotify_id = :EvnOnkoNotify_id,
				@Lpu_id = :Lpu_id,
				@Lpu_sid = :Lpu_sid,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@Lpu_cid = :Lpu_cid,
				@Morbus_id = :Morbus_id,
				@EvnOnkoNotifyNeglected_ClinicalData = :EvnOnkoNotifyNeglected_ClinicalData,
				@EvnOnkoNotifyNeglected_OrgDescr = :EvnOnkoNotifyNeglected_OrgDescr,
				@EvnOnkoNotifyNeglected_setConfDT = :EvnOnkoNotifyNeglected_setConfDT,
				@EvnOnkoNotifyNeglected_setNotifyDT = :EvnOnkoNotifyNeglected_setNotifyDT,
				@EvnOnkoNotifyNeglected_setDT = :EvnOnkoNotifyNeglected_setDT,
				@OnkoLateDiagCause_id = :OnkoLateDiagCause_id,
				@EvnOnkoNotifyNeglected_setFirstDT = :EvnOnkoNotifyNeglected_setFirstDT,
				@EvnOnkoNotifyNeglected_setFirstTreatmentDT = :EvnOnkoNotifyNeglected_setFirstTreatmentDT,
				@Lpu_fid = :Lpu_fid,
				@EvnOnkoNotifyNeglected_setFirstZODT = :EvnOnkoNotifyNeglected_setFirstZODT,
				@Lpu_zid = :Lpu_zid,
				@NeglectLpuType_id = :NeglectLpuType_id,
				@NeglectLpuTime_id = :NeglectLpuTime_id,
				@EvnOnkoNotifyNeglected_TreatFirstDate = :EvnOnkoNotifyNeglected_TreatFirstDate,
				@NeglectOnkoType_id = :NeglectOnkoType_id,
				@NeglectOnkoTime_id = :NeglectOnkoTime_id,
				@EvnOnkoNotifyNeglected_ConfirmDate = :EvnOnkoNotifyNeglected_ConfirmDate,
				@EvnOnkoNotifyNeglected_ExceptionDate = :EvnOnkoNotifyNeglected_ExceptionDate,
				@NeglectScreenLpuType_id = :NeglectScreenLpuType_id,
				@EvnOnkoNotifyNeglected_begScreenDate = :EvnOnkoNotifyNeglected_begScreenDate,
				@EvnOnkoNotifyNeglected_endScreenDate = :EvnOnkoNotifyNeglected_endScreenDate,
				@NeglectScreenOnkoType_id = :NeglectScreenOnkoType_id,
				@NeglectScreenOnkoTime_id = :NeglectScreenOnkoTime_id,
				@NeglectHiddenType_id = :NeglectHiddenType_id,
				@NeglectDiagnosticErrType_id = :NeglectDiagnosticErrType_id,
				@NeglectDiagnosticErrType_SecondComment = :NeglectDiagnosticErrType_SecondComment,
				@MedPersonal_id = :MedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnOnkoNotifyNeglected_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnOnkoNotifyNeglected_id' => $data['EvnOnkoNotifyNeglected_id'],
			'EvnOnkoNotify_id' => $data['EvnOnkoNotify_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_sid' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Lpu_cid' => $data['Lpu_cid'],
			'Morbus_id' => $data['Morbus_id'],
			'EvnOnkoNotifyNeglected_ClinicalData' => $data['EvnOnkoNotifyNeglected_ClinicalData'],
			'EvnOnkoNotifyNeglected_OrgDescr' => $data['EvnOnkoNotifyNeglected_OrgDescr'],
			'EvnOnkoNotifyNeglected_setConfDT' => $data['EvnOnkoNotifyNeglected_setConfDT'],
			'EvnOnkoNotifyNeglected_setNotifyDT' => $data['EvnOnkoNotifyNeglected_setNotifyDT'],
			'EvnOnkoNotifyNeglected_setDT' => $data['EvnOnkoNotifyNeglected_setDT'],
			'OnkoLateDiagCause_id' => $data['OnkoLateDiagCause_id'],
			'EvnOnkoNotifyNeglected_setFirstDT' => $data['EvnOnkoNotifyNeglected_setFirstDT'],
			'EvnOnkoNotifyNeglected_setFirstTreatmentDT' => $data['EvnOnkoNotifyNeglected_setFirstTreatmentDT'],
			'Lpu_fid' => $data['Lpu_fid'],
			'EvnOnkoNotifyNeglected_setFirstZODT' => $data['EvnOnkoNotifyNeglected_setFirstZODT'],
			'Lpu_zid' => $data['Lpu_zid'],
			'NeglectLpuType_id' => $data['NeglectLpuType_id'],
			'NeglectLpuTime_id' => $data['NeglectLpuTime_id'],
			'EvnOnkoNotifyNeglected_TreatFirstDate' => $data['EvnOnkoNotifyNeglected_TreatFirstDate'],
			'NeglectOnkoType_id' => $data['NeglectOnkoType_id'],
			'NeglectOnkoTime_id' => $data['NeglectOnkoTime_id'],
			'EvnOnkoNotifyNeglected_ConfirmDate' => $data['EvnOnkoNotifyNeglected_ConfirmDate'],
			'EvnOnkoNotifyNeglected_ExceptionDate' => $data['EvnOnkoNotifyNeglected_ExceptionDate'],
			'NeglectScreenLpuType_id' => $data['NeglectScreenLpuType_id'],
			'EvnOnkoNotifyNeglected_begScreenDate' => $data['EvnOnkoNotifyNeglected_begScreenDate'],
			'EvnOnkoNotifyNeglected_endScreenDate' => $data['EvnOnkoNotifyNeglected_endScreenDate'],
			'NeglectScreenOnkoType_id' => $data['NeglectScreenOnkoType_id'],
			'NeglectScreenOnkoTime_id' => $data['NeglectScreenOnkoTime_id'],
			'NeglectHiddenType_id' => $data['NeglectHiddenType_id'],
			'NeglectDiagnosticErrType_id' => $data['NeglectDiagnosticErrType_id'],
			'NeglectDiagnosticErrType_SecondComment' => $data['NeglectDiagnosticErrType_SecondComment'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		// echo getDebugSQL($query, $queryParams); exit();
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getCurDataForPrint($data)
	{
		$query = '
			select
				Lpu.Lpu_Nick as CurData_Lpu
				,Diag.Diag_FullName as CurData_Diag
				,convert(varchar,Evn.Evn_SetDT,104) as CurData_Date
				,RTRIM(EU.EvnClass_SysNick) as EvnUslugaClass_SysNick
				,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
				,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
				,OnkoDrug.OnkoDrug_Name
				,null as OnkoDrug_Schema
				,null as OnkoDrug_Dose
				,OnkoBeamKindType.OnkoUslugaBeamKindType_Name
				,Evn.Evn_id
			from
				(
					select
						Lpu_id
						,Diag_id
						,EvnVizitPL_setDT as Evn_SetDT
						,EvnVizitPL_id as Evn_id
					from v_EvnVizitPL with (nolock)
					where Diag_id = :Diag_id and Person_id = :Person_id
					union all
					select
						Lpu_id
						,Diag_id
						,EvnSection_setDT as Evn_SetDT
						,EvnSection_id as Evn_id
					from v_EvnSection with (nolock)
					where Diag_id = :Diag_id and Person_id = :Person_id
				) Evn
				left join v_Lpu Lpu with (nolock) on Evn.Lpu_id = Lpu.Lpu_id
				left join v_Diag Diag with (nolock) on Evn.Diag_id = Diag.Diag_id
				left join v_EvnUsluga_all EU with (nolock) on Evn.Evn_id = EU.EvnUsluga_pid
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				
				left join v_EvnUslugaOnkoChem OnkoChem with (nolock) on OnkoChem.EvnUslugaOnkoChem_id = EU.EvnUsluga_id
				left join v_EvnUslugaOnkoGormun OnkoGormun with (nolock) on OnkoGormun.EvnUslugaOnkoGormun_id = EU.EvnUsluga_id
				left join v_OnkoDrug OnkoDrug with (nolock) on OnkoDrug.OnkoDrug_id = null
				
				left join v_EvnUslugaOnkoBeam OnkoBeam with (nolock) on OnkoBeam.EvnUslugaOnkoBeam_id = EU.EvnUsluga_id
				left join v_OnkoUslugaBeamKindType OnkoBeamKindType with (nolock) on OnkoBeamKindType.OnkoUslugaBeamKindType_id = OnkoBeam.OnkoUslugaBeamKindType_id
			order by
				Evn.Evn_SetDT
		';
		
		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id']
		);
		// echo getDebugSQL($query, $queryParams); exit();
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$response = $res->result('array');
			$result = array();
			foreach($response as $row) {
				$id = $row['Evn_id'];
				if( empty($result[$id]) ) 
				{
					$result[$id] = array(
						'CurData_Lpu' => $row['CurData_Lpu']
						,'CurData_Date' => $row['CurData_Date']
						,'CurData_Diag' => $row['CurData_Diag']
						,'CurData_Res' => ''
						,'CurData_Treat' => ''
					);
				}
				if( in_array($row['EvnUslugaClass_SysNick'],array('EvnUslugaCommon','EvnUslugaPar')) ) 
				{
					$result[$id]['CurData_Res'] .= $row['Usluga_Code'].'. '.$row['Usluga_Name'].'<br />';
				}
				if( $row['EvnUslugaClass_SysNick'] == 'EvnUslugaOper' ) 
				{
					$result[$id]['CurData_Treat'] .= $row['Usluga_Code'].'. '.$row['Usluga_Name'].'<br />';
				}
				if( $row['EvnUslugaClass_SysNick'] == 'EvnUslugaOnkoBeam' ) 
				{
					$result[$id]['CurData_Treat'] .= $row['OnkoUslugaBeamKindType_Name'].'<br />';
				}
				if( in_array($row['EvnUslugaClass_SysNick'],array('EvnUslugaOnkoChem','EvnUslugaOnkoGormun')) ) 
				{
					$result[$id]['CurData_Treat'] .= $row['OnkoDrug_Name'].' (в дозе '.$row['OnkoDrug_Dose'].' по схеме '.$row['OnkoDrug_Schema'].')<br />';
				}
			}
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getDataForPrint($data)
	{
		$query = '
			select
				EONN.EvnOnkoNotifyNeglected_id
				,PS.Person_id
				,diag.Diag_id
				,EONN.EvnOnkoNotifyNeglected_ClinicalData
				,EONN.EvnOnkoNotifyNeglected_OrgDescr
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setConfDT, 104) as EvnOnkoNotifyNeglected_setConfDT
				,lpu_c.Lpu_Nick as LpuC_Name
				,isnull(EvnPL.EvnPL_NumCard,EvnPS.EvnPS_NumCard) as Num_Card
				,lpu_s.Lpu_Name as Lpu_Name
				,lpu_s.Lpu_Nick as LpuS_Name
				,rtrim(LpuSAddress.Address_Address) as LpuS_Address
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setNotifyDT, 104) as EvnOnkoNotifyNeglected_setNotifyDT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setDT, 104) as EvnOnkoNotifyNeglected_setDT
				,PS.Person_SurName
				,PS.Person_FirName
				,PS.Person_SecName
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay
				,rtrim(PersonAddress.Address_Address) as Person_Address
				,sex.Sex_Name
				,diag.Diag_FullName
				,od.OnkoDiag_Code + \'. \' + od.OnkoDiag_Name as OnkoDiag_FullName
				,OnkoT.OnkoT_Name
				,OnkoN.OnkoN_Name
				,OnkoM.OnkoM_Name
				,ts.TumorStage_Name
				,convert(varchar(10), MO.MorbusOnko_firstSignDT, 104) as MorbusOnko_firstSignDT
				,convert(varchar(10), MO.MorbusOnko_firstVizitDT, 104) as MorbusOnko_firstVizitDT
				,lpu_f.Lpu_Nick as LpuF_Name
				,rtrim(LpuFAddress.Address_Address) as LpuF_Address
				,oldc.OnkoLateDiagCause_Name
				,convert(varchar(10), MO.MorbusOnko_setDiagDT, 104) as MorbusOnko_setDiagDT
				,lpu_d.Lpu_Nick as LpuD_Name
				,rtrim(LpuDAddress.Address_Address) as LpuD_Address
				,odct.OnkoDiagConfType_Name
				,MO.MorbusOnko_IsTumorDepoUnknown as IsTumorDepoUnknown
				,MO.MorbusOnko_IsTumorDepoBones as IsTumorDepoBones
				,MO.MorbusOnko_IsTumorDepoLiver as IsTumorDepoLiver
				,MO.MorbusOnko_IsTumorDepoSkin as IsTumorDepoSkin
				,MO.MorbusOnko_IsTumorDepoKidney as IsTumorDepoKidney
				,MO.MorbusOnko_IsTumorDepoOvary as IsTumorDepoOvary
				,MO.MorbusOnko_IsTumorDepoPerito as IsTumorDepoPerito
				,MO.MorbusOnko_IsTumorDepoLympha as IsTumorDepoLympha
				,MO.MorbusOnko_IsTumorDepoLungs as IsTumorDepoLungs
				,MO.MorbusOnko_IsTumorDepoBrain as IsTumorDepoBrain
				,MO.MorbusOnko_IsTumorDepoMarrow as IsTumorDepoMarrow
				,MO.MorbusOnko_IsTumorDepoOther as IsTumorDepoOther
				,MO.MorbusOnko_IsTumorDepoMulti as IsTumorDepoMulti
			from
				v_EvnOnkoNotifyNeglected EONN with (nolock)
				left join v_MorbusOnkoPerson MOP with (nolock) on MOP.Person_id = EONN.Person_id
				left join v_MorbusOnko MO with (nolock) on MO.Morbus_id = EONN.Morbus_id
				left join v_Morbus M with (nolock) on M.Morbus_id = EONN.Morbus_id
				left join v_Diag Diag with (nolock) on M.Diag_id = Diag.Diag_id
				left join v_OnkoDiag od with (nolock) on MO.OnkoDiag_mid = od.OnkoDiag_id
				left join v_PersonState PS with (nolock) on EONN.Person_id = PS.Person_id
				left join v_Sex sex with (nolock) on PS.Sex_id = sex.Sex_id
				left join v_Address PersonAddress with (nolock) on PS.UAddress_id = PersonAddress.Address_id
				left join dbo.v_TumorStage ts with (nolock) on MO.TumorStage_id = ts.TumorStage_id
				left join dbo.v_OnkoM OnkoM with (nolock) on MO.OnkoM_id = OnkoM.OnkoM_id
				left join dbo.v_OnkoN OnkoN with (nolock) on MO.OnkoN_id = OnkoN.OnkoN_id
				left join dbo.v_OnkoT OnkoT with (nolock) on MO.OnkoT_id = OnkoT.OnkoT_id
				left join v_OnkoDiagConfType odct with (nolock) on MO.OnkoDiagConfType_id = odct.OnkoDiagConfType_id
				left join v_OnkoLateDiagCause oldc with (nolock) on MO.OnkoLateDiagCause_id = oldc.OnkoLateDiagCause_id
				left join Evn EvnP with (nolock) on M.Evn_pid = EvnP.Evn_id
				left join v_EvnPL EvnPL with (nolock) on EvnP.Evn_rid = EvnPL.EvnPL_id
				left join v_EvnPS EvnPS with (nolock) on EvnP.Evn_rid = EvnPS.EvnPS_id
				left join v_Lpu lpu_d with (nolock) on isnull(EvnPL.Lpu_id,EvnPS.Lpu_id) = lpu_d.Lpu_id
				left join v_Address LpuDAddress with (nolock) on lpu_d.UAddress_id = LpuDAddress.Address_id
				left join v_Lpu lpu_f with (nolock) on MO.Lpu_foid = lpu_f.Lpu_id
				left join v_Address LpuFAddress with (nolock) on lpu_f.UAddress_id = LpuFAddress.Address_id
				left join v_Lpu lpu_s with (nolock) on EONN.Lpu_sid = lpu_s.Lpu_id
				left join v_Address LpuSAddress with (nolock) on lpu_s.UAddress_id = LpuSAddress.Address_id
				left join v_Lpu lpu_c with (nolock) on EONN.Lpu_cid = lpu_c.Lpu_id
			where
				EONN.EvnOnkoNotifyNeglected_id = ?
		';
		$queryParams = array($data['EvnOnkoNotifyNeglected_id']);
		// echo getDebugSQL($query, $queryParams); exit();
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Загрузка формы редактирования
	 * @param $data
	 * @return mixed
	 */
	function load($data)
	{
		$query = '
			select
				EONN.EvnOnkoNotifyNeglected_id
				,EONN.EvnOnkoNotify_id
				,EONN.Morbus_id
				,EONN.Server_id
				,EONN.Person_id
				,EONN.PersonEvn_id
				,EONN.EvnOnkoNotifyNeglected_ClinicalData
				,EONN.EvnOnkoNotifyNeglected_OrgDescr
				,EONN.Lpu_cid
				,EONN.Lpu_sid
				,EONN.Lpu_fid
				,EONN.Lpu_zid
				,EONN.Lpu_id
				,EONN.OnkoLateDiagCause_id
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setConfDT, 104) as EvnOnkoNotifyNeglected_setConfDT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setNotifyDT, 104) as EvnOnkoNotifyNeglected_setNotifyDT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setDT, 104) as EvnOnkoNotifyNeglected_setDT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setFirstDT, 104) as EvnOnkoNotifyNeglected_setFirstDT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setFirstTreatmentDT, 104) as EvnOnkoNotifyNeglected_setFirstTreatmentDT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_setFirstZODT, 104) as EvnOnkoNotifyNeglected_setFirstZODT
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_TreatFirstDate, 104) as EvnOnkoNotifyNeglected_TreatFirstDate
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_ConfirmDate, 104) as EvnOnkoNotifyNeglected_ConfirmDate
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_ExceptionDate, 104) as EvnOnkoNotifyNeglected_ExceptionDate
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_begScreenDate, 104) as EvnOnkoNotifyNeglected_begScreenDate
				,convert(varchar(10), EONN.EvnOnkoNotifyNeglected_endScreenDate, 104) as EvnOnkoNotifyNeglected_endScreenDate
				,EONN.NeglectLpuType_id
				,EONN.NeglectLpuTime_id
				,EONN.NeglectOnkoType_id
				,EONN.NeglectOnkoTime_id
				,EONN.NeglectScreenLpuType_id
				,EONN.NeglectScreenOnkoType_id
				,EONN.NeglectScreenOnkoTime_id
				,EONN.NeglectHiddenType_id
				,EONN.NeglectDiagnosticErrType_id
				,EONN.NeglectDiagnosticErrType_SecondComment
				,EONN.MedPersonal_id
				,MO.TumorStage_id
			from
				v_EvnOnkoNotifyNeglected EONN with (nolock)
				left join v_MorbusOnko MO with (nolock) on MO.Morbus_id = EONN.Morbus_id 
			where
				EONN.EvnOnkoNotifyNeglected_id = ?
		';
		$queryParams = array($data['EvnOnkoNotifyNeglected_id']);
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	
}