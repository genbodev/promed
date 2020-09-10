<?php
require_once('Abstract_model.php');
/**
 * @property EvnDiagPS_model $EvnDiagPS_model
 */
class EvnDiag_model extends Abstract_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return string
	 */
	protected function getTableName()
	{
		return 'EvnDiag';
	}

	/**
	 * @return Abstract_model|bool
	 */
	public function validate(){
		return true;
	}

	/**
	 * @return Abstract_model|bool
	 */
	protected function canDelete()
	{
		return true;
	}

	/**
	 *	Получение данных для отображения в ЭМК
	 */
	function getDiagListViewData($data) {
		$where = '';
		$params = array();
		$isKz=(isset($data['session']['region']) && $data['session']['region']['nick'] == 'kz');
		
		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter)
		{
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$where.=" and {$diagFilter}";
			}
			$lpuFilter = getAccessRightsLpuFilter('ED.Lpu_id');
			if (!empty($lpuFilter)) {
				$where.=" and {$lpuFilter}";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$where.=" and {$lpuBuildingFilter}";
			}
		}
		$query = "
			with EvnDiag(
				EvnClass_SysNick,
				spec_id,
				Person_id,
				Diag_id,
				Diag_setDate,
				Lpu_id
				,MedPersonal_id
				,LpuSection_id
			) as (
				select 
					'EvnSection',
					0,
					Person_id,
					Diag_id,
					EvnSection_setDate,
					Lpu_id
					,MedPersonal_id
					,LpuSection_id
				from v_EvnSection with (nolock)
				where
					Person_id = :Person_id
					and Diag_id is not null
				union all
				select 
					'EvnVizitPL',
					0,
					Person_id,
					Diag_id,
					EvnVizitPL_setDate,
					Lpu_id
					,MedPersonal_id
					,LpuSection_id
				from v_EvnVizitPL EVPL with (nolock)
				where
					Person_id = :Person_id
					and Diag_id is not null
				union all
				select
					'EvnDiagPLSop',
					0,
					EDL.Person_id,
					EDL.Diag_id,
					EDL.EvnDiagPLSop_setDate,
					EDL.Lpu_id
					,ev.MedPersonal_id
					,LpuSection_id
				from v_EvnDiagPLSop EDL with (nolock)
				left join v_EvnVizit ev with (nolock) on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
				where 
					EDL.Person_id = :Person_id
					and EDL.Diag_id is not null
				union all
				select
					'EvnDiagPS',
					0,
					eds.Person_id,
					eds.Diag_id,
					EDS.EvnDiagPS_setDate,
					eds.Lpu_id
					,es.MedPersonal_id
					,LpuSection_id
				from v_EvnDiagPS EDS with (nolock)
				left join v_EvnSection es with (nolock) on EDS.EvnDiagPS_pid=es.EvnSection_id
				where 
					eds.Person_id = :Person_id
					and eds.Diag_id is not null
				union all
				select
					'EvnDiagSpec',
					eds.EvnDiagSpec_id,
					eds.Person_id,
					eds.Diag_id,
					EDS.EvnDiagSpec_didDT,
					eds.Lpu_id
					,0
					,0
				from v_EvnDiagSpec EDS with (nolock)
				where 
					eds.Person_id = :Person_id
					and eds.Diag_id is not null
				union all
				select
				 'EvnVizitDispDop',
					0,
					EVDD.Person_id,
					EVDD.Diag_id,
					EVDD.EvnVizitDispDop_setDate,
					EVDD.Lpu_id
					,EVDD.MedPersonal_id
					,EVDD.LpuSection_id
				from v_EvnUslugaDispDop EVNU with(nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
				inner join v_Diag diag with(nolock) on diag.Diag_id=EVDD.Diag_id
				left join v_DopDispInfoConsent DDIC with(nolock) on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
				left join v_SurveyTypeLink STL with(nolock) on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
				where EVNU.Person_id=:Person_id and STL.SurveyType_id=19 and EVDD.DopDispDiagType_id=2 and diag.Diag_Code not like 'Z%'
				union all
				select
					'EvnDiagDopDisp',
					0,
					EDDD.Person_id,
					EDDD.Diag_id,
					EDDD.EvnDiagDopDisp_setDate,
					EDDD.Lpu_id
					,0
					,0
				from
				v_EvnDiagDopDisp EDDD (nolock)
			where
				(1=1) and EDDD.Person_id = :Person_id and EDDD.DeseaseDispType_id = '2'
			)

			select
				ED.EvnClass_SysNick,
				ED.Person_id,
				Ed.spec_id,
				ED.Person_id as pid,
				0 as Children_Count,
				ED.Lpu_id,
				--ED.Evn_id as Diag_pid,
				ED.Diag_id,
				ED.Diag_id as DiagList_id,
				CONVERT(varchar(10), ED.Diag_setDate, 104) as Diag_setDate,
				RTRIM(ISNULL(Diag.Diag_Code, '')) as Diag_Code,
				RTRIM(ISNULL(Diag.Diag_Name, '')) as Diag_Name,
				case ED.spec_id when 0 then RTRIM(ISNULL(Lpu.Lpu_Nick, ''))else EDS.EvnDiagSpec_Lpu end as Lpu_Nick
				,case ED.spec_id when 0 then RTRIM(ISNULL(MP.Person_Fio, ''))else ISNULL(EDS.EvnDiagSpec_MedWorker, MSF.Person_Fio) end as MedPersonal_Fio
				,case ED.spec_id when 0 then ISNULL(LS.LpuSectionProfile_Name, '')else EDS.EvnDiagSpec_LpuSectionProfile end as LpuSectionProfile_Name
			from EvnDiag ED with (nolock)
				left join v_Diag as Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_id
				left join v_EvnDiagSpec EDS with(nolock) on ED.spec_id = EDS.EvnDiagSpec_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = EDS.MedStaffFact_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
			where (1=1) ".$where."
			order by
				ED.Diag_setDate";


		/*echo getDebugSQL($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id'=>$data['Lpu_id']
		));die();*/
		
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id'=>$data['Lpu_id']
		));
		
		if ( is_object($result) ){
			$resp = $result->result('array');
			$diagArr = array();
			$respfiltered = array();
			foreach($resp as $respone){
				// фильтруем одинаковые диагнозы в посещениях
				if (!in_array($respone['Diag_id'], $diagArr)) {
					$diagArr[] = $respone['Diag_id'];
					$respfiltered[] = $respone;
				}
			}
			if(!$isKz){
				return swFilterResponse::filterNotViewDiag($respfiltered, $data);
			}
			$diagArray=Array();
			$res=Array();
			foreach($respfiltered as $val){
				if(!in_array($val['Diag_id'],$diagArray)){
					if($val['spec_id']>0){
						if($val['MedPersonal_Fio']!=''){
							$val['LpuSectionProfile_Name']='<a id="DiagList_'.$val["Diag_id"].'_'.$val["spec_id"].'_viewDiag">'.$val['MedPersonal_Fio'].'</a>';
						}else{
							$val['LpuSectionProfile_Name']='<a id="DiagList_'.$val["Diag_id"].'_'.$val["spec_id"].'_viewDiag">'.'Просмотр'.'</a>';
						}
					}
					$res[]=$val;
					$diagArray[]=$val['Diag_id'];
				}
			}
			return $res;
		}
		else
			return false;
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function delEvnDiagSpec($data){
		
		if ( $data['EvnDiagSpec_id'] > 0 ) {
			$params = array('EvnDiagSpec_id'=>$data['EvnDiagSpec_id'],'pmUser_id'=>$data['pmUser_id'] );
		}else{
			return false;
		}
		$sql = "declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDiagSpec_del
				@EvnDiagSpec_id = :EvnDiagSpec_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDiagSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
		//echo getDebugSQL($sql, $params);exit();
		$result = $this->db->query($sql, $params);
		
		if ( is_object($result) ) {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function saveDiagSpecEditWindow($data){

		$filter = '';
		if ( (isset($data['EvnDiagSpec_id'])) && ($data['EvnDiagSpec_id'] > 0) ) {
			$filter = ' and EDS.EvnDiagSpec_id!=:EvnDiagSpec_id';
		}
		$sql = 'with EvnDiag(
				Diag_id
			) as (
				select
					Diag_id
					from v_EvnSection with (nolock)
				where
					Person_id = :Person_id
					and Diag_id =:Diag_id
				union all
				select
					Diag_id
					from v_EvnVizitPL EVPL with (nolock)
				where
					Person_id = :Person_id
					and Diag_id =:Diag_id
				union all
				select
					EDL.Diag_id
				from v_EvnDiagPLSop EDL with (nolock)
				left join v_EvnVizit ev with (nolock) on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
				where 
					EDL.Person_id = :Person_id
					and EDL.Diag_id =:Diag_id
				union all
				select
					EDS.Diag_id
				from v_EvnDiagPS EDS with (nolock)
				left join v_EvnSection es with (nolock) on EDS.EvnDiagPS_pid=es.EvnSection_id
				where 
					eds.Person_id = :Person_id
					and eds.Diag_id =:Diag_id
				union all
				select
					EDS.Diag_id
				from v_EvnDiagSpec EDS with (nolock)
				where 
					eds.Person_id =:Person_id
					and eds.Diag_id =:Diag_id
					'.$filter.'
			)

			select
				COUNT(*) as cnt
			from EvnDiag ED with (nolock)';
		$params=array('Diag_id'=>$data['Diag_id'],'Person_id'=>$data['Person_id'],'EvnDiagSpec_id'=>$data['EvnDiagSpec_id']);
		//echo getDebugSQL($sql, $params);exit();
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if ( $result[0]['cnt'] > 0 ) {
				return array(0 => array('Error_Msg' => 'Данный диагноз уже числится в списке.'));
			}
		}
		if ( (!isset($data['EvnDiagSpec_id'])) || ($data['EvnDiagSpec_id'] <= 0) ) {
			
			$procedure = 'p_EvnDiagSpec_ins';
		}
		else {
			$procedure = 'p_EvnDiagSpec_upd';
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDiagSpec_id;
			exec " . $procedure . "
				@EvnDiagSpec_id = @Res output,
				@Org_id = :Org_id,
				@Lpu_id = :Lpu_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDiagSpec_LpuSectionProfile = :EvnDiagSpec_LpuSectionProfile,
				@EvnDiagSpec_Lpu = :EvnDiagSpec_Lpu,
				@EvnDiagSpec_MedWorker = :EvnDiagSpec_MedWorker,
				@Server_id = :Server_id,
				@EvnDiagSpec_setDT = :EvnDiagSpec_setDT,
				@EvnDiagSpec_didDT = :EvnDiagSpec_didDT,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 3,
				@pmUser_id = :pmUser_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDiagSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnDiagSpec_id' => ((!isset($data['EvnDiagSpec_id'])) || ($data['EvnDiagSpec_id'] <= 0) ? NULL : $data['EvnDiagSpec_id']),
			'Org_id' => $data['Org_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'MedStaffFact_id'=>!empty($data['EvnDiagSpec_MedWorker'])?null:$data['MedStaffFact_id'],
			'EvnDiagSpec_MedWorker'=> $data['EvnDiagSpec_MedWorker'],
			'EvnDiagSpec_LpuSectionProfile'=>$data['EvnDiagSpec_LpuSectionProfile'],
			'EvnDiagSpec_Lpu'=>$data['EvnDiagSpec_Lpu'],
			'EvnDiagSpec_setDT' => $data['EvnDiagSpec_setDate'],
			'EvnDiagSpec_didDT' => $data['EvnDiagSpec_setDT'],
			'Diag_id' => $data['Diag_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getDiagSpecEditWindow($data) {
		$queryParams = array(
			'EvnDiagSpec_id' => $data['EvnDiagSpec_id']
		);
		$query = "
			select
				Diag_id,
				Server_id,
				EvnDiagSpec_id,
				convert(varchar(10), EvnDiagSpec_didDT, 104) as EvnDiagSpec_setDT,
				convert(varchar(10), EvnDiagSpec_setDT, 104) as EvnDiagSpec_setDate,
				Org_id,
				MedStaffFact_id,
				EvnDiagSpec_MedWorker,
				EvnDiagSpec_LpuSectionProfile,
				EvnDiagSpec_Post,
				PersonEvn_id,
				Person_id
			from v_EvnDiagSpec with (nolock)
			where EvnDiagSpec_id = :EvnDiagSpec_id
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных для отображения в ЭМК
	 */
	function getEvnDiagPLViewData($data) {
		$where = '1=1';
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (isset($data['EvnDiagPL_pid'])) {
			$filter = 'and EVPL.EvnVizitPL_id = :EvnVizitPL_id';
			$queryParams['EvnVizitPL_id'] = $data['EvnDiagPL_pid'];
		} else {
			$filter = 'and EDPLS.EvnDiagPLSop_id = :EvnDiagPL_id';
			$queryParams['EvnDiagPL_id'] = $data['EvnDiagPL_id'];
		}

		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$where .= " and {$diagFilter}";
		}

		$query = "
			select
				EVPL.Lpu_id as Lpu_id, -- + EDPLS.Diag_id для filterNotViewDiag
				case when EVPL.Lpu_id = :Lpu_id and ISNULL(EVPL.EvnVizitPL_IsInReg, 1) = 1 then 'edit' else 'view' end as accessType,
				EDPLS.EvnDiagPLSop_id as EvnDiagPL_id,
				EDPLS.EvnDiagPLSop_pid as EvnVizitPL_id,
				EDPLS.Person_id,
				EDPLS.PersonEvn_id,
				EDPLS.Server_id,
				DT.DeseaseType_id,
				RTrim(DT.DeseaseType_Name) as DeseaseType_Name,
				EDPLS.Diag_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				convert(varchar(10), EDPLS.EvnDiagPLSop_setDate, 104) as EvnDiagPL_setDate,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fin) as MedPersonal_Fin,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(Diag.Diag_Code) as Diag_Code,
				RTrim(Diag.Diag_Name) as Diag_Name,
				0 as Children_Count
			from v_EvnDiagPLSop EDPLS with (nolock)
				inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EDPLS.EvnDiagPLSop_pid
					{$filter}
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = EVPL.Lpu_id
				left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
			where
				{$where}
		";
		// alexpm, убрал это where EVPL.Lpu_id = :Lpu_id
		// соп. диагнозы должны отображаться, если они установлены в др. ЛПУ и тут тоже нужен фильтр
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			//return $result->result('array');
			//этот фильтр пока не нужен, т.к. в запросе where EVPL.Lpu_id = :Lpu_id
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных для отображения в ЭМК
	 */
	function getEvnDiagPLStomViewData($data) {
		$queryParams = array(
			'EvnDiagPLStom_id' => !empty($data['EvnDiagPLStom_id']) ? $data['EvnDiagPLStom_id'] : null,
			'EvnDiagPLStom_pid' => !empty($data['EvnDiagPLStom_pid']) ? $data['EvnDiagPLStom_pid'] : null,
			'Lpu_id' => $data['Lpu_id']
		);

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$join = array();
		$select = array();

		if ( $this->regionNick == 'penza' ) {
			$join[] = "
				outer apply (
					select top 1 1 as hasUslugaType03
					from v_EvnUslugaStom eus with (nolock)
						inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where eus.EvnDiagPLStom_id = EDPLS.EvnDiagPLStom_id
						and uca.UslugaComplexAttribute_Value = '03'
				) uc03
			";
			$select[] = "uc03.hasUslugaType03";
		}

		$query = "
			select
				 case when EDPLS.Lpu_id = :Lpu_id
					" . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and exists (select top 1 EvnVizitPLStom_id from v_EvnVizitPLStom with (nolock) where EvnVizitPLStom_rid = EDPLS.EvnDiagPLStom_rid and MedPersonal_id in (".implode(',',$med_personal_list)."))" : "") . "
				 then 'edit' else 'view' end as accessType
				,EDPLS.EvnDiagPLStom_id
				,EDPLS.EvnDiagPLStom_pid
				,EDPLS.EvnDiagPLStom_rid
				,:EvnDiagPLStom_pid as EvnDiagPLStom_vid
				,EDPLS.Person_id
				,EDPLS.PersonEvn_id
				,EDPLS.Server_id
				,EDPLS.Diag_id -- для filterNotViewDiag
				,EDPLS.Lpu_id -- для filterNotViewDiag
				,RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
				,IsNull(convert(varchar,EvnPL.EvnPLStom_setDT,104),'') as EvnPLStom_setDate
				,convert(varchar(10), EDPLS.EvnDiagPLStom_setDate, 104) as EvnDiagPLStom_setDate
				,case
					when EDPLS.EvnDiagPLStom_IsClosed = 2 then convert(varchar(10), EDPLS.EvnDiagPLStom_disDate, 104)
					else null
				end as EvnDiagPLStom_disDate
				,ISNULL(EDPLS.EvnDiagPLStom_IsClosed, 1) as EvnDiagPLStom_IsClosed
				,ISNULL(EDPLS.EvnDiagPLStom_HalfTooth, 1) as EvnDiagPLStom_HalfTooth
				,RTrim(Diag.Diag_Code) as Diag_Code
				,RTrim(Diag.Diag_Name) as Diag_Name
				,Tooth.Tooth_id
				,Tooth.Tooth_Code
				,mes.Mes_id
				,mes.Mes_Code
				,mes.Mes_Name
				,0 as Children_Count
				" . (count($select) > 0 ? "," . implode(",", $select) : "") . "
			from v_EvnDiagPLStom EDPLS with (nolock)
				inner join v_EvnPLStom EvnPL with (nolock) on EvnPL.EvnPLStom_id = EDPLS.EvnDiagPLStom_rid
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
				left join v_Tooth Tooth with (nolock) on Tooth.Tooth_id = EDPLS.Tooth_id
				left join v_MesOld mes with (nolock) on mes.Mes_id = EDPLS.Mes_id
				" . implode(" ", $join) . "
		";
		if ( !empty($data['EvnDiagPLStom_id']) ) {
			$query .= "
				where EDPLS.EvnDiagPLStom_id = :EvnDiagPLStom_id
			";
		}
		else {
			$query .= "
				where EDPLS.EvnDiagPLStom_rid = (select top 1 Evn_rid from v_Evn with (nolock) where Evn_id = :EvnDiagPLStom_pid)
			";
		}
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных для отображения в ЭМК
	 */
	function getEvnDiagPLStomSopViewData($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['EvnDiagPLStomSop_pid']))
		{
			$filter = 'and EDPLSS.EvnDiagPLStomSop_pid = :EvnDiagPLStomSop_pid';
			$queryParams['EvnDiagPLStomSop_pid'] = $data['EvnDiagPLStomSop_pid'];
		}
		else
		{
			$filter = 'and EDPLSS.EvnDiagPLStomSop_id = :EvnDiagPLStomSop_id';
			$queryParams['EvnDiagPLStomSop_id'] = $data['EvnDiagPLStomSop_id'];
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select
				 case when EDPLSS.Lpu_id = :Lpu_id " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EVPLS.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType
				,EDPLSS.EvnDiagPLStomSop_id
				,EDPLSS.EvnDiagPLStomSop_pid
				,EDPLSS.Person_id
				,EDPLSS.PersonEvn_id
				,EDPLSS.Server_id
				,EDPLSS.Diag_id -- для filterNotViewDiag
				,EDPLSS.Lpu_id -- для filterNotViewDiag
				,RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
				,convert(varchar(10), EDPLSS.EvnDiagPLStomSop_setDate, 104) as EvnDiagPLStomSop_setDate
				,RTrim(Diag.Diag_Code) as Diag_Code
				,RTrim(Diag.Diag_Name) as Diag_Name
				,0 as Children_Count
			from v_EvnDiagPLStomSop EDPLSS with (nolock)
				inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EDPLSS.EvnDiagPLStomSop_pid
					{$filter}
				left join Diag with (nolock) on Diag.Diag_id = EDPLSS.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLSS.DeseaseType_id
			where EDPLSS.EvnDiagPLStomSop_pid = :EvnDiagPLStomSop_pid
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

	/**
	 *	Удаление диагноза
	 */
	function deleteEvnDiag($data) {
		if ( $data['class'] == 'EvnDiagPLStom' ) {
			// Проверяем наличие услуг, в которых указано удаляемое заболевание
			$query = "
				select top 1 EvnUslugaStom_id
				from v_EvnUslugaStom with (nolock)
				where EvnDiagPLStom_id = :id
			";

			$queryParams = array(
				'id' => $data['id'],
				'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка возможности удаления заболевания/диагноза)'));
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnUslugaStom_id']) ) {
				return array(array('Error_Msg' => 'Удаления заболевания невозможно, т.к. имеется связанная услуга.'));
			}
		}

		switch ( $data['class'] ) {
			case 'EvnDiagPLSop':
			case 'EvnDiagPLStom':
			case 'EvnDiagPLStomSop':
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_" . $data['class'] . "_del
						@" . $data['class'] . "_id = :id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$queryParams = array(
					'id' => $data['id'],
					'pmUser_id' => $data['pmUser_id']
				);
			break;

			case 'EvnDiagPS':
				$this->load->model('EvnDiagPS_model');
				$data['EvnDiagPS_id'] = $data['id'];
				$result = array($this->EvnDiagPS_model->doDelete($data, true));
				$this->deleteMorbusOnko($data);

				return $result;
				break;

			default:
				return false;
			break;
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$this->deleteMorbusOnko($data);
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление диагноза)'));
		}
	}


	/**
	 *	Загрузка списка диагнозов
	 */
	function loadEvnDiagPLGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$accessType = '
			case
				when EDPLS.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EDPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EDPLS.EvnDiagPLSop_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$query = "
			select
				case
					when {$accessType} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and exists (select top 1 MedStaffFact_id from v_MedStaffFact with (nolock) where MedPersonal_id in (".implode(',',$med_personal_list).") and LpuSection_id = EVPL.LpuSection_id and WorkData_begDate <= EVPL.EvnVizitPL_setDate and (WorkData_endDate is null or WorkData_endDate >= EVPL.EvnVizitPL_setDate))" : "") . " then 'edit'
					else 'view'
				end as accessType,
				EDPLS.EvnDiagPLSop_id as EvnDiagPL_id,
				EDPLS.EvnDiagPLSop_pid as EvnVizitPL_id,
				EDPLS.Person_id,
				EDPLS.PersonEvn_id,
				EDPLS.Server_id,
				DT.DeseaseType_id,
				RTrim(DT.DeseaseType_Name) as DeseaseType_Name,
				EDPLS.Diag_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				convert(varchar(10), EDPLS.EvnDiagPLSop_setDate, 104) as EvnDiagPL_setDate,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(Diag.Diag_Code) as Diag_Code,
				RTrim(Diag.Diag_Name) as Diag_Name
			from v_EvnDiagPLSop EDPLS with (nolock)
				inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EDPLS.EvnDiagPLSop_pid
					and EVPL.EvnVizitPL_id = :EvnVizitPL_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = EVPL.Lpu_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
			where EDPLS.Lpu_id " . getLpuIdFilter($data) . "
		";
		$result = $this->db->query($query, array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Загрузка списка диагнозов
	 */
	function loadEvnDiagPSGrid($data) {
		if ( empty($data['EvnDiagPS_pid']) && empty($data['EvnDiagPS_rid']) ) {
			return false;
		}
		$filter = "(1 = 1)";
		$queryParams = array(
			'EvnDiagPS_pid' => !empty($data['EvnDiagPS_pid']) ? $data['EvnDiagPS_pid'] : null,
			'EvnDiagPS_rid' => !empty($data['EvnDiagPS_rid']) ? $data['EvnDiagPS_rid'] : null,
			'Lpu_id' => $data['Lpu_id']
		);
		
		if (isset($data["DiagSetClass"])) {
		    $filter.=" and EDPS.DiagSetClass_id = 1";
		} else {
			$filter.=" and EDPS.DiagSetClass_id != 1";
		}

		switch ( $data['class'] ) {
			case 'EvnDiagPSDie':
				$filter .= " and EDPS.DiagSetType_id = 5";
			break;

			case 'EvnDiagPSHosp':
				$filter .= " and EDPS.DiagSetType_id = 1";
			break;

			case 'EvnDiagPSRecep':
				$filter .= " and EDPS.DiagSetType_id = 2";
			break;

			case 'EvnDiagPSSect':
				$filter .= " and EDPS.DiagSetType_id = 3";
			break;

			default:
				return false;
			break;
		}

		if (in_array($data['class'], array('EvnDiagPSSect'))) {
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) $filter .= " and {$diagFilter}";
		}

		$query = "
			select
				EDPS.EvnDiagPS_id,
				EDPS.EvnDiagPS_pid,
				EDPS.Person_id,
				EDPS.PersonEvn_id,
				EDPS.Server_id,
				Diag.Diag_id,
				EDPS.DiagSetPhase_id,
				RTRIM(EDPS.EvnDiagPS_PhaseDescr) as EvnDiagPS_PhaseDescr,
				DSC.DiagSetClass_id,
				EDPS.DiagSetType_id,
				convert(varchar(10), EDPS.EvnDiagPS_setDate, 104) as EvnDiagPS_setDate,
				EDPS.EvnDiagPS_setTime,
				RTRIM(DSC.DiagSetClass_Name) as DiagSetClass_Name,
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(Diag.Diag_Name) as Diag_Name,
				1 as RecordStatus_Code
			from v_EvnDiagPS EDPS with (nolock)
				left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
				left join DiagSetClass DSC with (nolock) on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
			where " . $filter . "
				" . (!empty($data['EvnDiagPS_rid']) ? "and EDPS.EvnDiagPS_rid = :EvnDiagPS_rid" : "and EDPS.EvnDiagPS_pid = :EvnDiagPS_pid" ) . "
		";

		//echo getDebugSQL($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}


	/**
	 *	Сохранение диагноза
	 */
	function saveEvnDiagPL($data) {
		$osn_diag_id = $this->getFirstResultFromQuery("
			select Diag_id from v_EvnVizitPL (nolock) where EvnVizitPL_id = ?
		", [$data['EvnVizitPL_id']]);
		
		if ($osn_diag_id == $data['Diag_id']) {
			throw new Exception('Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов');
		}
		
		$procedure = '';

		if ( (!isset($data['EvnDiagPL_id'])) || ($data['EvnDiagPL_id'] <= 0) ) {
			$procedure = 'p_EvnDiagPLSop_ins';
		}
		else {
			$procedure = 'p_EvnDiagPLSop_upd';
			//проверяем изменился ли диагноз
			$query = "
				SELECT top 1
					EvnDiagPLSop_id
				from
					v_EvnDiagPLSop with (nolock)
				where
					EvnDiagPLSop_id = :EvnDiagPLSop_id
					and Diag_id != :Diag_id
			";
			$queryParams = array(
				'EvnDiagPLSop_id' => $data['EvnDiagPL_id'],
				'Diag_id' => $data['Diag_id']
			);
			$chkDiag = $this->getFirstRowFromQuery($query, $queryParams);
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDiagPL_id;
			exec " . $procedure . "
				@EvnDiagPLSop_id = @Res output,
				@EvnDiagPLSop_pid = :EvnVizitPL_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDiagPLSop_setDT = :EvnDiagPL_setDate,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 3,
				@DeseaseType_id = :DeseaseType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDiagPL_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnDiagPL_id' => ((!isset($data['EvnDiagPL_id'])) || ($data['EvnDiagPL_id'] <= 0) ? NULL : $data['EvnDiagPL_id']),
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnDiagPL_setDate' => $data['EvnDiagPL_setDate'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseType_id' => $data['DeseaseType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			if(!empty($chkDiag)) {
				$data['class'] = 'EvnDiagPLSop';
				$data['id'] = $data['EvnDiagPL_id'];
				$this->deleteMorbusOnko($data);
			}


			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Сохранение диагноза в КВС
	 */
	function saveEvnDiagPS($data) {
		$this->load->model('EvnDiagPS_model', 'EvnDiagPS_model');
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_DO_SAVE;
		}

		if(!empty($data['EvnDiagPS_id'])) {
			$query = "
				SELECT top 1 EvnDiagPS_id
				from v_EvnDiagPS
				where 
					EvnDiagPS_id = :EvnDiagPS_id
					and Diag_id != :Diag_id
			";
			$queryParams = array(
				'EvnDiagPS_id' => $data['EvnDiagPS_id'],
				'Diag_id' => $data['Diag_id']
			);
			$checkDiag = $this->getFirstRowFromQuery($query, $queryParams);
		}

		$result = array($this->EvnDiagPS_model->doSave($data, true));
		if(!empty($checkDiag)) {
			$data['class'] = 'EvnDiagPS';
			$data['id'] = $data['EvnDiagPS_id'];
			$this->deleteMorbusOnko($data);
		}
		return $result;
	}

	/**
	 *	Сохранение диагноза в КВС
	 */
	function mSaveEvnDiagPS($data) {
		$this->load->model('EvnDiagPS_model', 'EvnDiagPS_model');
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_DO_SAVE;
		}

		if(!empty($data['EvnDiagPS_id'])) {
			$query = "
				SELECT top 1 EvnDiagPS_id
				from v_EvnDiagPS
				where 
					EvnDiagPS_id = :EvnDiagPS_id
					and Diag_id != :Diag_id
			";
			$queryParams = array(
				'EvnDiagPS_id' => $data['EvnDiagPS_id'],
				'Diag_id' => $data['Diag_id']
			);
			$checkDiag = $this->getFirstRowFromQuery($query, $queryParams);
		}

		$result = array($this->EvnDiagPS_model->doSave($data, true));
		if(!empty($checkDiag)) {
			$data['class'] = 'EvnDiagPS';
			$data['id'] = $data['EvnDiagPS_id'];
			$this->deleteMorbusOnko($data);
		}
		return $result;
	}

	/**
	 * Загрузка списка доступных для копирования диагнозов
	 */
	function loadEvnDiagForCopy($data) {
		$filter = "(1 = 1)";
		$queryParams = array(
			'EvnDiagPS_rid' => $data['EvnDiagPS_rid'],
			'EvnDiagPS_pid' => $data['EvnDiagPS_pid']
		);

		$query = "
			select
				ED.EvnDiagPS_id, D.Diag_id, D.Diag_FullName,
				ED.EvnDiagPS_pid
			from v_EvnDiagPS ED with(nolock)
				left join v_DiagSetClass DSC with (nolock) on DSC.DiagSetClass_id = ED.DiagSetClass_id
				left join v_DiagSetType DST with (nolock) on DST.DiagSetType_id = ED.DiagSetType_id
				inner join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
				left join v_EvnSection ES with(nolock) on ES.EvnSection_id = ED.EvnDiagPS_pid
			where
				ED.EvnDiagPS_rid = :EvnDiagPS_rid
				and DSC.DiagSetClass_SysNick like 'sop'
				and DST.DiagSetType_SysNick in ('priem', 'klin')
				and not exists(
					select * from v_EvnDiagPS t with(nolock)
					where
					t.EvnDiagPS_pid = :EvnDiagPS_pid
					and t.DiagSetType_id = 3
					and t.DiagSetClass_id != 1
					and t.Diag_id=D.Diag_id
				)
				and (ES.EvnSection_Index < (select top 1 t.EvnSection_Index from v_EvnSection t with (nolock) where t.EvnSection_id = :EvnDiagPS_pid)
				 or ES.EvnSection_Index is null or :EvnDiagPS_pid is null)
			order by ED.EvnDiagPS_updDT desc
		";

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = array();
			$diag_ids = array();
			$list = $result->result('array');
			foreach($list as $diag) {
				if (!in_array($diag['Diag_id'], $diag_ids)) {
					$diag_ids[] = $diag['Diag_id'];
					$response[] = $diag;
				}
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Копирование диагноза
	 */
	function copyEvnDiagPS($data) {
		$queryParams = $data;

		switch ( $data['class'] ) {
			case 'EvnDiagPSDie':
				$queryParams['DiagSetType_id'] = 5;
				break;

			case 'EvnDiagPSHosp':
				$queryParams['DiagSetType_id'] = 1;
				break;

			case 'EvnDiagPSRecep':
				$queryParams['DiagSetType_id'] = 2;
				break;

			case 'EvnDiagPSSect':
				$queryParams['DiagSetType_id'] = 3;
				break;

			default:
				return false;
				break;
		}

		$query = "
			select top 1
				ED.Lpu_id,
				ED.Server_id,
				ED.PersonEvn_id,
				ED.Person_id,
				case when ED.EvnDiagPS_setDT >= ES.EvnSection_setDT and (ED.EvnDiagPS_setDT <= ES.EvnSection_disDT or ES.EvnSection_disDT is null) then
					convert(varchar(10), ED.EvnDiagPS_setDT, 120)
				else
					convert(varchar(10), ES.EvnSection_setDT, 120)
				end as EvnDiagPS_setDate,
				case when ED.EvnDiagPS_setDT >= ES.EvnSection_setDT and (ED.EvnDiagPS_setDT <= ES.EvnSection_disDT or ES.EvnSection_disDT is null) then
					convert(varchar(5), ED.EvnDiagPS_setDT, 108)
				else
					convert(varchar(5), ES.EvnSection_setDT, 108)
				end as EvnDiagPS_setTime,
				ED.Diag_id,
				ED.DiagSetPhase_id,
				ED.EvnDiagPS_PhaseDescr,
				ED.DiagSetClass_id,
				ES.EvnSection_id as EvnDiagPS_pid,
				:DiagSetType_id as DiagSetType_id,
				:pmUser_id as pmUser_id
			from v_EvnDiagPS ED with (nolock)
				inner join v_EvnSection ES with(nolock) on ES.EvnSection_id = :EvnDiagPS_pid
			where
				ED.EvnDiagPS_id = :EvnDiagPS_id
		";
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при получении данных для копирования'));
		}

		$params = $result->result('array');
		$params[0]['session']=$data['session'];
		$response = $this->saveEvnDiagPS($params[0]);

		return $response;
	}

	/**
	 * Получение списка уточненных диагнозов пациента для ЭМК
	 */
	function loadPersonDiagPanel($data) {
		$this->load->library('swFilterResponse');

		$where = '';
		$isKz=(isset($data['session']['region']) && $data['session']['region']['nick'] == 'kz');

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter)
		{
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$where.=" and {$diagFilter}";
			}
			$lpuFilter = getAccessRightsLpuFilter('ED.Lpu_id');
			if (!empty($lpuFilter)) {
				$where.=" and {$lpuFilter}";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$where.=" and {$lpuBuildingFilter}";
			}
		}

		$person_filter = " Person_id = :Person_id "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$person_filter = " Person_id in ({$data['person_in']}) ";
			$select = " ,ED.Person_id ";
		}

		$query = "
			with EvnDiag(
				spec_id,
				Diag_id,
				Diag_setDate,
				Lpu_id,
				MedPersonal_id,
				LpuSection_id,
				Person_id
			) as (
				-- диагнозы из движений
				select
					0,
					Diag_id,
					EvnSection_setDate,
					Lpu_id,
					MedPersonal_id,
					LpuSection_id,
					Person_id
				from
					v_EvnSection with (nolock)
				where
					{$person_filter}
					and Diag_id is not null
					
				union all
				
				-- диагнозы из посещений
				select
					0,
					Diag_id,
					EvnVizitPL_setDate,
					Lpu_id,
					MedPersonal_id,
					LpuSection_id,
					Person_id
				from
					v_EvnVizitPL EVPL with (nolock)
				where
					{$person_filter}
					and Diag_id is not null
					
				union all
				
				-- сопутствующие диагнозы
				select
					0,
					EDL.Diag_id,
					EDL.EvnDiagPLSop_setDate,
					EDL.Lpu_id,
					ev.MedPersonal_id,
					LpuSection_id,
					EDL.Person_id
				from
					v_EvnDiagPLSop EDL with (nolock)
					left join v_EvnVizit ev with (nolock) on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
				where
				 	".preg_replace('/Person_id/','EDL.Person_id',$person_filter, 1)."
					and EDL.Diag_id is not null
					
				union all
				
				-- диагнозы из КВС
				select
					0,
					eds.Diag_id,
					EDS.EvnDiagPS_setDate,
					eds.Lpu_id,
					es.MedPersonal_id,
					LpuSection_id,
					EDS.Person_id
				from
					v_EvnDiagPS EDS with (nolock)
					left join v_EvnSection es with (nolock) on EDS.EvnDiagPS_pid=es.EvnSection_id
				where 
					".preg_replace('/Person_id/','EDS.Person_id',$person_filter, 1)."
					and eds.Diag_id is not null
					
				union all
				
				select
					eds.EvnDiagSpec_id,
					eds.Diag_id,
					EDS.EvnDiagSpec_didDT,
					eds.Lpu_id,
					0,
					0,
					EDS.Person_id
				from
					v_EvnDiagSpec EDS with (nolock)
				where 
					{$person_filter}
					and eds.Diag_id is not null
					
				union all
				
				select
					0,
					EVDD.Diag_id,
					EVDD.EvnVizitDispDop_setDate,
					EVDD.Lpu_id,
					EVDD.MedPersonal_id,
					EVDD.LpuSection_id,
					EVNU.Person_id
				from
					v_EvnUslugaDispDop EVNU with(nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
					inner join v_Diag diag with(nolock) on diag.Diag_id=EVDD.Diag_id
					left join v_DopDispInfoConsent DDIC with(nolock) on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
					left join v_SurveyTypeLink STL with(nolock) on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
				where
					".preg_replace('/Person_id/','EVNU.Person_id',$person_filter, 1)."
					and STL.SurveyType_id = 19
					and EVDD.DopDispDiagType_id = 2
					and diag.Diag_Code not like 'Z%'
					
				union all
				
				select
					0,
					EDDD.Diag_id,
					EDDD.EvnDiagDopDisp_setDate,
					EDDD.Lpu_id,
					0,
					0,
					Person_id
				from
					v_EvnDiagDopDisp EDDD (nolock)
				where
					{$person_filter}
					and EDDD.DeseaseDispType_id = '2'
			)

			select
				Ed.spec_id,
				ED.Lpu_id,
				ED.Diag_id,
				CONVERT(varchar(10), ED.Diag_setDate, 104) as Diag_setDate,
				RTRIM(ISNULL(Diag.Diag_Code, '')) as Diag_Code,
				RTRIM(ISNULL(Diag.Diag_Name, '')) as Diag_Name,
				case ED.spec_id when 0 then RTRIM(ISNULL(Lpu.Lpu_Nick, ''))else EDS.EvnDiagSpec_Lpu end as Lpu_Nick,
				case ED.spec_id when 0 then RTRIM(ISNULL(MP.Person_Fio, ''))else ISNULL(EDS.EvnDiagSpec_MedWorker, MSF.Person_Fio) end as MedPersonal_Fio,
				case ED.spec_id when 0 then ISNULL(LS.LpuSectionProfile_Name, '')else EDS.EvnDiagSpec_LpuSectionProfile end as LpuSectionProfile_Name
				{$select}
			from EvnDiag ED with (nolock)
				left join v_Diag as Diag with (nolock) on Diag.Diag_id = ED.Diag_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_id
				left join v_EvnDiagSpec EDS with(nolock) on ED.spec_id = EDS.EvnDiagSpec_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = EDS.MedStaffFact_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
			where
				(1=1)
				{$where}
			order by
				ED.Diag_setDate
		";


		/*echo getDebugSQL($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id'=>$data['Lpu_id']
		));die();*/

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id'=>$data['Lpu_id']
		));

		if ( is_object($result) ){
			$resp = $result->result('array');
			$diagArr = array();
			$respfiltered = array();
			foreach($resp as $respone){
				// фильтруем одинаковые диагнозы в посещениях
				if (!in_array($respone['Diag_id'], $diagArr)) {
					$diagArr[] = $respone['Diag_id'];
					$respfiltered[] = $respone;
				}
			}
			if(!$isKz){
				return swFilterResponse::filterNotViewDiag($respfiltered, $data);
			}
			$diagArray=Array();
			$res=Array();
			foreach($respfiltered as $val){
				if(!in_array($val['Diag_id'],$diagArray)){
					if($val['spec_id']>0){
						if($val['MedPersonal_Fio']!=''){
							$val['LpuSectionProfile_Name']='<a id="DiagList_'.$val["Diag_id"].'_'.$val["spec_id"].'_viewDiag">'.$val['MedPersonal_Fio'].'</a>';
						}else{
							$val['LpuSectionProfile_Name']='<a id="DiagList_'.$val["Diag_id"].'_'.$val["spec_id"].'_viewDiag">'.'Просмотр'.'</a>';
						}
					}
					$res[]=$val;
					$diagArray[]=$val['Diag_id'];
				}
			}
			return $res;
		}
		else
			return false;
	}

	/**
	 * Получение счетчика уточненных диагнозов пациента для ЭМК (мобильное приложение)
	 */
	function getPersonDiagCount($data) {

		$this->load->library('swFilterResponse');

		$where = '';
		$isKz = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'kz');

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if (isset($data['from_MZ']) && $data['from_MZ'] == 2) $needAccessFilter = false;

		if ($needAccessFilter) {

			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) $where.=" and {$diagFilter}";

			$lpuFilter = getAccessRightsLpuFilter('ED.Lpu_id');
			if (!empty($lpuFilter)) $where.=" and {$lpuFilter}";

			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) $where.=" and {$lpuBuildingFilter}";
		}

		$person_filter = " Person_id = :Person_id "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$person_filter = " Person_id in ({$data['person_in']}) ";
			$select = " ,ED.Person_id ";
		}

		$query = "
			with EvnDiag(
				Diag_id,
				Lpu_id,
				LpuSection_id,
				Person_id
			) as (
				-- диагнозы из движений
				select Diag_id, Lpu_id, LpuSection_id, Person_id from v_EvnSection with (nolock)
				where {$person_filter} and Diag_id is not null

				union all

				-- диагнозы из посещений
				select Diag_id, Lpu_id, LpuSection_id, Person_id from v_EvnVizitPL EVPL with (nolock)
				where {$person_filter} and Diag_id is not null

				union all

				-- сопутствующие диагнозы
				select EDL.Diag_id, EDL.Lpu_id, LpuSection_id, EDL.Person_id from v_EvnDiagPLSop EDL with (nolock)
					left join v_EvnVizit ev with (nolock) on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
				where ".preg_replace('/Person_id/','EDL.Person_id',$person_filter, 1)."
					and EDL.Diag_id is not null

				union all

				-- диагнозы из КВС
				select eds.Diag_id, eds.Lpu_id, LpuSection_id, EDS.Person_id from v_EvnDiagPS EDS with (nolock)
					left join v_EvnSection es with (nolock) on EDS.EvnDiagPS_pid=es.EvnSection_id
				where ".preg_replace('/Person_id/','EDS.Person_id',$person_filter, 1)."
					and eds.Diag_id is not null

				union all

				select eds.Diag_id, eds.Lpu_id, 0 as LpuSection_id, eds.Person_id from v_EvnDiagSpec EDS with (nolock)
				where {$person_filter} and eds.Diag_id is not null

				union all

				select EVDD.Diag_id, EVDD.Lpu_id, EVDD.LpuSection_id, EVDD.Person_id from v_EvnUslugaDispDop EVNU with(nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
					inner join v_Diag diag with(nolock) on diag.Diag_id=EVDD.Diag_id
					left join v_DopDispInfoConsent DDIC with(nolock) on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
					left join v_SurveyTypeLink STL with(nolock) on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
				where
					".preg_replace('/Person_id/','EVNU.Person_id',$person_filter, 1)."
					and STL.SurveyType_id = 19
					and EVDD.DopDispDiagType_id = 2
					and diag.Diag_Code not like 'Z%'

				union all

				select EDDD.Diag_id, EDDD.Lpu_id, 0 as LpuSection_id, Person_id from v_EvnDiagDopDisp EDDD (nolock)
				where {$person_filter} and EDDD.DeseaseDispType_id = '2'
			)

			select
				ED.Diag_id,
				ED.Lpu_id
				{$select}
			from EvnDiag ED (nolock)
				left join v_Diag as Diag (nolock) on Diag.Diag_id = ED.Diag_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = ED.Lpu_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = ED.LpuSection_id
			where (1=1) {$where}
		";

		$result = $this->db->query($query, array('Person_id' => $data['Person_id'],'Lpu_id'=>$data['Lpu_id']));

		if (is_object($result)){

			$diags = $result->result('array');
			$diag_list = array();

			// фильтруем одинаковые диагнозы в посещениях
			foreach($diags as $d) {
				if (!isset($diag_list[$d['Diag_id']]))
					$diag_list[$d['Diag_id']] = $d;
			}

			$count = array('EvnDiagCount' => 0);
			if (!$isKz) $diag_list = swFilterResponse::filterNotViewDiag($diag_list, $data);

			if (!empty($diag_list)) {
				$diag_list = array_values($diag_list);
				$count['EvnDiagCount'] = count($diag_list);
			}

			return $count;

		} else return false;
	}

	/**
	 * удаление специфики
	 */
	private function deleteMorbusOnko($data)
	{
		$table = false;
		$subsect_list = array('OnkoConsult', 'MorbusOnkoLink', 'MorbusOnkoSpecTreat', 'MorbusOnkoDrug', 'MorbusOnkoRefusal');
				
		switch ( $data['class'] ) {
			case 'EvnDiagPLSop':
				$table = 'MorbusOnkoVizitPLDop';
				$evntable = 'EvnVizitPL';
				$evnfield = 'EvnVizit';
				$sopid = 'EvnDiagPLSop_id';
			break;

			case 'EvnDiagPS':
				$table = 'MorbusOnkoLeave';
				$evntable = 'EvnSection';
				$evnfield = 'EvnSection';
				$sopid = 'EvnDiag_id';
				break;
		}
		
		if (!$table) return false;
		
		$tmp = $this->getFirstRowFromQuery("
			select top 1 mol.{$table}_id as moid, mol.Diag_id, evn.Person_id, M.Morbus_id, evn.{$evntable}_id as Evn_id
			from v_{$table} mol (nolock)
			inner join v_{$evntable} evn (nolock) on evn.{$evntable}_id = mol.{$evnfield}_id
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
			where mol.{$sopid} = :EvnDiag_id
		", array(
			'EvnDiag_id' => $data['id']
		));
		
		if(!empty($tmp)) {
			foreach($subsect_list as $subsect) {
				$mol_list = $this->queryList("select {$subsect}_id from {$subsect} (nolock) where {$table}_id = :moid", 
					array('moid' => $tmp['moid'])
				);
				foreach($mol_list as $ml) {
					$this->execCommonSP("dbo.p_{$subsect}_del", array("{$subsect}_id" => $ml));
				}
			}
			// удаляем все связанные услуги
			if (!empty($tmp['Morbus_id'])) {
				$this->load->library('swMorbus');
				swMorbus::removeEvnUslugaOnko(array(
					'Evn_id' => $tmp['Evn_id'],
					'Morbus_id' => $tmp['Morbus_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
			// И удаляем саму специфику
			$delParams = array("{$table}_id" => $tmp['moid']);
			$res = $this->execCommonSP("dbo.p_{$table}_del", $delParams);
			// сносим неактуальные морбусы
			$this->load->model('MorbusOnkoSpecifics_model');
			$this->MorbusOnkoSpecifics_model->clearMorbusOnkoSpecifics(array(
				'Person_id' => $tmp['Person_id'],
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session'],
			));
		}		
	}
	function getDiagData($data) {
		$result =  $this->queryResult("
			select top 1
				DiagSetType_id,
				EvnDiagPS_pid,
				EvnDiagPS_rid
			from v_EvnDiagPS
			where 
				Diag_id = :Diag_id and 
				Person_id = :Person_id
			order by EvnDiagPS_id desc
		", array('Diag_id' => $data['Diag_id'], 'Person_id' => $data['Person_id']));
		return $result[0];
	}
}
