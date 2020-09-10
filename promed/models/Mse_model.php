<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Mse
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      11.10.2011
*/

class Mse_model extends swModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 *	Method description
	 */
	function searchData($data)
	{
		$filter = "1=1";
		$params = array();
		
		if( !empty($data['Person_SurName']) ) {
			$params['Person_SurName'] = $data['Person_SurName'];
			$filter .= " and PS.Person_SurName like :Person_SurName + '%'";
		}
		
		if( !empty($data['Person_FirName']) ) {
			$params['Person_FirName'] = $data['Person_FirName'];
			$filter .= " and PS.Person_FirName like :Person_FirName + '%'";
		}
		
		if( !empty($data['Person_SecName']) ) {
			$params['Person_SecName'] = $data['Person_SecName'];
			$filter .= " and PS.Person_SecName like :Person_SecName + '%'";
		}
		
		if( !empty($data['Person_BirthDay']) ) {
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
			$filter .= " and PS.Person_BirthDay = :Person_BirthDay";
		}
		
		if( !empty($data['Lpu_id']) ) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and PS.Lpu_id = :Lpu_id";
		}
		
		if( !empty($data['MedService_id']) ) {
			$params['MedService_id'] = $data['MedService_id'];
			$filter .= " and EM.MedService_id = :MedService_id";
		}
		
		if( !empty($data['EvnMse_setDT'][0]) ) {
			$params['EvnMse_setDT_beg'] = $data['EvnMse_setDT'][0];
			$filter .= " and EM.EvnMse_setDT >= :EvnMse_setDT_beg";
		}
		
		if( !empty($data['EvnMse_setDT'][1]) ) {
			$params['EvnMse_setDT_end'] = $data['EvnMse_setDT'][1];
			$filter .= " and EM.EvnMse_setDT <= :EvnMse_setDT_end";
		}
		
		if( !empty($data['Diag_id']) ) {
			$params['Diag_id'] = $data['Diag_id'];
			$filter .= " and EM.Diag_id = :Diag_id";
		}
		
		$query = "
			select
				EM.EvnMse_id,
				EM.EvnMse_NumAct,
				PS.Person_id,
				PS.Server_id,
				case when PS.Person_id is not null
					then PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + isnull(PS.Person_SecName, '') 
					else ''
				end as Person_Fio,
				convert(varchar(10), cast(PS.Person_BirthDay as datetime), 104) as Person_BirthDay,
				L.Lpu_Nick,
				convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104) as EvnMse_setDT,
				D.diag_FullName as DiagMse_Name,
				IGT.InvalidGroupType_Name,
				convert(varchar(10), cast(EM.EvnMse_ReExamDate as datetime), 104) as EvnMse_ReExamDate,
				case when EM.Signatures_id is not null then
					isnull(convert(varchar(10), S.Signatures_updDT, 104) + ' ', '') + 
					isnull(convert(varchar(5), S.Signatures_updDT, 108) + '. ', '') +
					isnull(PUS.PMUser_Name, '')
					else ''
				end as EvnMse_Sign
			from
				v_EvnMse EM with(nolock)
				left join v_Signatures S with (nolock) on EM.Signatures_id = S.Signatures_id
				left join v_pmUser PUS with (nolock) on PUS.pmUser_id = S.pmUser_updID
				left join v_PersonState PS with(nolock) on PS.Person_id = EM.Person_id
					and PS.PersonEvn_id = PS.PersonEvn_id
				left join v_Diag D with(nolock) on D.Diag_id = EM.Diag_id
				left join v_InvalidGroupType IGT with(nolock) on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
				left join v_Lpu L with(nolock) on L.Lpu_id = PS.Lpu_id
			where
				{$filter}
				and EM.MedService_id is not null
		";
		//echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Method description
	 */
	function saveEvnMse($data)
	{
		if(!empty($data['EvnMse_id']))
			$action = 'upd';
		else
			$action = 'ins';
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@EvnMse_insDT datetime,
				@EvnMse_updDT datetime,
				@EvnMse_Index bigint = :EvnMse_Index,
				@EvnMse_Count bigint = :EvnMse_Count,
				@ErrMessage varchar(4000);
			set @Res = :EvnMse_id;
			exec p_EvnMse_".$action."
				@EvnMse_id = @Res output,
				@MedService_id = :MedService_id,
				@EvnMse_pid = :EvnMse_pid,
				@EvnMse_rid = :EvnMse_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnMse_setDT = :EvnMse_setDT,
				@EvnMse_disDT = :EvnMse_disDT,
				@EvnMse_didDT = :EvnMse_didDT,
				@EvnMse_insDT = @EvnMse_insDT output,
				@EvnMse_updDT = @EvnMse_updDT output,
				@EvnMse_Index = @EvnMse_Index output,
				@EvnMse_Count = @EvnMse_Count output,
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@EvnVK_id = :EvnVK_id,
				@EvnMse_NumAct = :EvnMse_NumAct,
				@Diag_id = :Diag_id,
				@Diag_sid = :Diag_sid,
				@Diag_aid = :Diag_aid,
				@HealthAbnorm_id = :HealthAbnorm_id,
				@HealthAbnormDegree_id = :HealthAbnormDegree_id,
				@CategoryLifeType_id = :CategoryLifeType_id,
				@CategoryLifeDegreeType_id = :CategoryLifeDegreeType_id,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@InvalidCouseType_id = :InvalidCouseType_id,
				@EvnMse_InvalidPercent = :EvnMse_InvalidPercent,
				@ProfDisabilityPeriod_id = :ProfDisabilityPeriod_id,
				@EvnMse_ProfDisabilityStartDate = :EvnMse_ProfDisabilityStartDate,
				@EvnMse_ProfDisabilityEndDate = :EvnMse_ProfDisabilityEndDate,
				@EvnMse_ReExamDate = :EvnMse_ReExamDate,
				@InvalidRefuseType_id = :InvalidRefuseType_id,
				@EvnMse_SendStickDate = :EvnMse_SendStickDate,
				@EvnMse_HeadStaffMse = :EvnMse_HeadStaffMse,
				@MedServiceMedPersonal_id = :MedServiceMedPersonal_id,
				@EvnMse_MedRecomm = :EvnMse_MedRecomm,
				@EvnMse_ProfRecomm = :EvnMse_ProfRecomm,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output,
				@EvnMse_DiagDetail = :EvnMse_DiagDetail,
				@EvnMse_DiagSDetail = :EvnMse_DiagSDetail,
				@EvnMse_DiagADetail = :EvnMse_DiagADetail,
				@Diag_bid = :Diag_bid,
				@EvnMse_DiagBDetail = :EvnMse_DiagBDetail,
				@EvnMse_SendStickDetail = :EvnMse_SendStickDetail;
			select @Res as EvnMse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$this->beginTransaction();
		
		$this->load->model('Evn_model', 'Evn_model');
		$this->Evn_model->updateEvnStatus(array(
			'Evn_id' => $data['EvnPrescrMse_id'],
			'EvnStatus_id' => 31,
			'EvnClass_SysNick' => 'EvnPrescrMse',
			'pmUser_id' => $data['pmUser_id']
		));
		
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$response = $result->result('array');
			$data['EvnMse_id'] = $response[0]['EvnMse_id'];
			$this->saveEvnMseDiag($data);
			$this->commitTransaction();
			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Сохранение диагнозов
	 */
	function saveEvnMseDiag($data)
	{
		
		$SopDiagList = array();
		$SopDiagArr = array();
		foreach($data['SopDiagList'] as $diag_id) {
			if (in_array($diag_id[0], $SopDiagList)) {
				throw new Exception('Ввод одинаковых сопутствующих заболеваний не допускается', 500);
			}
			$SopDiagArr[] = $diag_id[0];
			$SopDiagList[] = $diag_id;
			
			$SopDiagOslArr = array();
			foreach($diag_id[2] as $diag_oid) {
				if (in_array($diag_oid, $SopDiagOslArr)) {
					throw new Exception('Ввод одинаковых осложнений сопутствующих заболеваний не допускается', 500);
				}
				$SopDiagOslArr[] = $diag_oid;
			}
		}
		
		$OslDiagList = array(); 
		$OslDiagArr = array(); 
		foreach($data['OslDiagList'] as $diag_id) {
			if (in_array($diag_id[0], $OslDiagArr)) {
				throw new Exception('Ввод одинаковых осложнений основного заболевания не допускается', 500);
			}
			$OslDiagArr[] = $diag_id[0];
			$OslDiagList[] = $diag_id;
		}
		
		$this->deleteEvnMseDiag($data);
		
		foreach($data['SopDiagList'] as $diag_id) {
			$resp = $this->queryResult("
				declare
					@Res bigint,
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_EvnMseDiagLink_ins
					@EvnMseDiagLink_id = @Res output,
					@EvnMse_id = :EvnMse_id,
					@Diag_id = :Diag_id,
					@Diag_oid = :Diag_oid,
					@EvnMseDiagLink_DescriptDiag = :DescriptDiag,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Res as EvnMseDiagLink_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'EvnMse_id' => $data['EvnMse_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' => $diag_id[0],
				'DescriptDiag' => $diag_id[1],
				'Diag_oid' => null
			));
			if ($resp && isset($resp[0])) {
				$EvnMseDiagLink_id = $resp[0]['EvnMseDiagLink_id'];
				foreach($diag_id[2] as $diag_oid) {
					$this->queryResult("
						declare
							@Res bigint,
							@Error_Code int,
							@Error_Msg varchar(4000);

						exec p_EvnMseDiagMkb10Link_ins
							@EvnMseDiagMkb10Link_id = @Res output,
							@EvnMseDiagLink_id = :EvnMseDiagLink_id,
							@Diag_id = :Diag_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Msg output;

						select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
					", array(
						'EvnMseDiagLink_id' => $EvnMseDiagLink_id,
						'pmUser_id' => $data['pmUser_id'],
						'Diag_id' => $diag_oid
					));
				}
			} 
		}
		
		foreach($data['OslDiagList'] as $diag_id) {
			$this->queryResult("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_EvnMseDiagLink_ins
					@EvnMseDiagLink_id = null,
					@EvnMse_id = :EvnMse_id,
					@Diag_id = :Diag_id,
					@Diag_oid = :Diag_oid,
					@EvnMseDiagLink_DescriptDiag = :DescriptDiag,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'EvnMse_id' => $data['EvnMse_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' => null,
				'DescriptDiag' => $diag_id[1],
				'Diag_oid' => $diag_id[0]
			));
		}
	}
	
	/**
	 *	Удаление диагнозов
	 */
	function deleteEvnMseDiag($data)
	{
		$resp = $this->queryResult("
			select EvnMseDiagLink_id from EvnMseDiagLink with(nolock) where EvnMse_id = :EvnMse_id
		", $data);
		
		foreach($resp as $item) {
			$resp2 = $this->queryResult("
				select EvnMseDiagMkb10Link_id from EvnMseDiagMkb10Link with(nolock) where EvnMseDiagLink_id = ?
			", array($item['EvnMseDiagLink_id']));
			foreach($resp2 as $item2) {
				$this->queryResult("
					declare
						@Error_Code int,
						@Error_Msg varchar(4000);

					exec p_EvnMseDiagMkb10Link_del
						@EvnMseDiagMkb10Link_id = :EvnMseDiagMkb10Link_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Msg output;

					select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
				", array(
					'EvnMseDiagMkb10Link_id' => $item2['EvnMseDiagMkb10Link_id']
				));
			}
			$this->queryResult("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_EvnMseDiagLink_del
					@EvnMseDiagLink_id = :EvnMseDiagLink_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'EvnMseDiagLink_id' => $item['EvnMseDiagLink_id']
			));
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnMse($data)
	{
		// Если протокол еще не создан (условие не выполняется), то при чтении подставляем в него данные из направления
		if( !empty($data['EvnMse_id']) ) {
			$query = "
				select
					EM.EvnMse_id,
					convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104) as EvnMse_setDT,
					EM.EvnMse_NumAct,
					EM.EvnPrescrMse_id,
					EM.EvnVK_id,
					EM.Diag_id,
					EM.Diag_sid,
					EM.Diag_aid,
					EM.HealthAbnorm_id,
					EM.HealthAbnormDegree_id,
					EM.CategoryLifeType_id,
					EM.CategoryLifeDegreeType_id,
					EM.InvalidGroupType_id,
					EM.InvalidCouseType_id,
					EM.EvnMse_InvalidPercent,
					EM.ProfDisabilityPeriod_id,
					convert(varchar(10), cast(EM.EvnMse_ProfDisabilityStartDate as datetime), 104) as EvnMse_ProfDisabilityStartDate,
					convert(varchar(10), cast(EM.EvnMse_ProfDisabilityEndDate as datetime), 104) as EvnMse_ProfDisabilityEndDate,
					convert(varchar(10), cast(EM.EvnMse_ReExamDate as datetime), 104) as EvnMse_ReExamDate,
					EM.InvalidRefuseType_id,
					convert(varchar(10), cast(EM.EvnMse_SendStickDate as datetime), 104) as EvnMse_SendStickDate,
					EM.EvnMse_HeadStaffMse,
					EM.MedServiceMedPersonal_id,
					EM.EvnMse_MedRecomm,
					EM.EvnMse_ProfRecomm,
					EM.MedService_id,
					EPM.Diag_id as EPMDiag_id,
					EPM.Diag_sid as EPMDiag_sid,
					EPM.Diag_aid as EPMDiag_aid,
					EM.EvnMse_DiagDetail, 
					EM.EvnMse_DiagSDetail, 
					EM.EvnMse_DiagADetail, 
					EM.Diag_bid, 
					EM.EvnMse_DiagBDetail,
					EM.EvnMse_SendStickDetail
				from
					v_EvnMse EM with(nolock)
					left join v_EvnPrescrMse EPM (nolock) on EPM.EvnPrescrMse_id = EM.EvnPrescrMse_id
				where
					EM.EvnMse_id = :EvnMse_id
			";
		} else if ( !empty($data['EvnPrescrMse_id']) ) {
			$query = "
				select
					(select isnull(max(cast(EvnMse_NumAct as bigint)), 0)+1 from v_EvnMse with(nolock) where isnumeric(EvnMse_NumAct) = 1) as EvnMse_NumAct,
					EvnPrescrMse_id,
					EvnVK_id,
					Diag_id,
					Diag_sid,
					Diag_aid,
					(select top 1 MedServiceMedPersonal_id from v_EvnMse with(nolock) where MedService_id = MedService_id order by EvnMse_setDate desc) as MedServiceMedPersonal_id,
					InvalidGroupType_id,
					EvnPrescrMse_InvalidPercent as EvnMse_InvalidPercent,
					MedService_id,
					Diag_id as EPMDiag_id,
					Diag_sid as EPMDiag_sid,
					Diag_aid as EPMDiag_aid
				from
					v_EvnPrescrMse with(nolock)
				where
					EvnPrescrMse_id = :EvnPrescrMse_id
			";
		} else if ( !empty($data['EvnVK_id']) ) {
			$query = "
				select
					(select isnull(max(cast(EvnMse_NumAct as bigint)), 0)+1 from v_EvnMse with(nolock) where isnumeric(EvnMse_NumAct) = 1) as EvnMse_NumAct,
					EvnVK_id,
					Diag_id,
					Diag_sid,
					(select top 1 MedServiceMedPersonal_id from v_EvnMse with(nolock) where MedService_id = MedService_id order by EvnMse_setDate desc) as MedServiceMedPersonal_id,
					MedService_id
				from
					v_EvnVK with(nolock)
				where
					EvnVK_id = :EvnVK_id
			";
		} else if ( !empty($data['EvnPL_id']) ) {
			$query = "
				select top 1 
					convert(varchar(10), EPM.EvnPrescrMse_issueDT, 104) as EvnPrescrMse_issueDT
				from 
					v_EvnPL EPL with(nolock)
					inner join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
					inner join v_EvnPrescrMse EPM with(nolock) on EPM.EvnPrescrMse_pid = EVPL.EvnVizitPL_id
					inner join v_EvnDirection_all ED with(nolock) on ED.EvnQueue_id = EPM.EvnQueue_id
					inner join v_EvnStatus ES with(nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				where 
					EPL.EvnPL_id = :EvnPL_id
					and ES.EvnStatus_SysNick not in('Canceled', 'Declined')
				order by
					ED.EvnDirection_setDT desc

			";
		} else {
			$query = "
				select
					(select top 1 MedServiceMedPersonal_id from v_EvnMse with(nolock) where MedService_id = MedService_id order by EvnMse_setDate desc) as MedServiceMedPersonal_id,
					isnull(max(cast(EvnMse_NumAct as bigint)), 0)+1 as EvnMse_NumAct
				from
					v_EvnMse with(nolock)
				where 
					isnumeric(EvnMse_NumAct) = 1
			";
		}
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			if( !empty($data['EvnMse_id']) ) {
				$result[0]['SopDiagList'] = $this->getMseDiagList($data, 1);
				$result[0]['OslDiagList'] = $this->getMseDiagList($data, 2);
			} else if ( !empty($data['EvnPrescrMse_id']) ) {
				$result[0]['SopDiagList'] = $this->getDiagList($data, 1);
				$result[0]['OslDiagList'] = $this->getDiagList($data, 2);
			}
			return $result;
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getMseDiagList($data, $type)
	{
		$field = ($type == 1) ? 'Diag_id' : 'Diag_oid';
		$filter = ($type == 1) ? 'Diag_id is not null' : 'Diag_id is null';
		$query = "
			select {$field} as Diag_id, EvnMseDiagLink_DescriptDiag as DescriptDiag, EvnMseDiagLink_id
			from EvnMseDiagLink with(nolock)
			where EvnMse_id = :EvnMse_id and {$filter}
		";
		$result = $this->queryResult($query, $data);
		if(is_array($result))
		{
			if($type == 1) {
				foreach($result as &$res) {
					$res['OslDiag'] = $this->queryList("select Diag_id from EvnMseDiagMkb10Link (nolock) where EvnMseDiagLink_id = ?", array($res['EvnMseDiagLink_id']));
				}
			}
			return $result;
		}
		return false;
	}
	
	/**
	 *	Method description
	 */
	function getEvnStickOfYear($data)
	{
		$person_ids = array();
		if (!empty($data['Person_id'])) {
			$person_ids[] = $data['Person_id'];
		} else if (!empty($data['Person_ids']) && is_array($data['Person_ids'])) {
			$person_ids = $data['Person_ids'];
		}
		if (count($person_ids) == 0) {
			return false;
		}
		$person_ids_str = implode(",", $person_ids);

		$query = "
			select
				ES.EvnStick_id as EvnStick_id,
				ES.Person_id as Person_id,
				convert(varchar(10), cast(ESWR.EvnStickWorkRelease_begDT as datetime), 104) as EvnStick_setDate,
				convert(varchar(10), cast(ESWR.EvnStickWorkRelease_endDT as datetime), 104) as EvnStick_disDate,
				DATEDIFF(day, ESWR.EvnStickWorkRelease_begDT, (case when ESWR.EvnStickWorkRelease_endDT is not null then ESWR.EvnStickWorkRelease_endDT else dbo.tzGetDate() end)) as DayCount,
				D.Diag_id,
				D.Diag_Code,
				D.Diag_FullName as Diag_Name,
				ES.EvnStick_Num as EvnMseStick_StickNum,
				ress.EvnMseStick_IsStick,
				IsStick.YesNo_Name EvnMseStick_IsStickName,
				EvnStickClass = 'EvnStick'
			from
				v_EvnStick ES with(nolock)
				left join v_EvnStickWorkRelease ESWR with(nolock) on ESWR.EvnStickBase_id = ES.EvnStick_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = ES.EvnStick_mid
					and EPL.Person_id = ES.Person_id and EPL.PersonEvn_id = ES.PersonEvn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnStick_mid
					and EPS.Person_id = ES.Person_id and EPS.PersonEvn_id = ES.PersonEvn_id
				left join v_Diag D with(nolock) on D.Diag_id = isnull(EPL.Diag_id, EPS.Diag_id)
				outer apply (
					select top 1
						case when RegistryESStorage_id is not null then 2 else 1 end as EvnMseStick_IsStick
					from
						v_EvnStickBase esb (nolock)
						left join v_RegistryESStorage ress with (nolock) on esb.EvnStickBase_id = ress.EvnStickBase_id
					where
						esb.EvnStickBase_id = ES.EvnStick_id
				) ress
				left join v_YesNo IsStick (nolock) on IsStick.YesNo_id = ress.EvnMseStick_IsStick
			where
				ES.Person_id in ({$person_ids_str})
				and ES.EvnStick_setDT is not null
				and datediff(month, (case when (ESWR.EvnStickWorkRelease_endDT is not null) then ESWR.EvnStickWorkRelease_endDT else dbo.tzGetDate() end), dbo.tzGetDate()) <=12
			union All
			select
				EMS.EvnMseStick_id as EvnStick_id,
				EMS.Person_id,
				convert(varchar(10), cast(EMS.EvnMseStick_begDT as datetime), 104) as EvnStick_setDate,
				convert(varchar(10), cast(EMS.EvnMseStick_endDT as datetime), 104) as EvnStick_disDate,
				DATEDIFF(day, EMS.EvnMseStick_begDT, (case when EMS.EvnMseStick_endDT is not null then EMS.EvnMseStick_endDT else dbo.tzGetDate() end)) as DayCount,
				D.Diag_id,
				D.Diag_Code,
				D.Diag_FullName as Diag_Name,
				EMS.EvnMseStick_StickNum,
				EMS.EvnMseStick_IsStick,
				IsStick.YesNo_Name EvnMseStick_IsStickName,
				EvnStickClass = 'EvnMseStick'
			from
				v_EvnMseStick EMS with(nolock)
				left join v_Diag D with(nolock) on D.Diag_id = EMS.Diag_id
				left join v_YesNo IsStick (nolock) on IsStick.YesNo_id = EMS.EvnMseStick_IsStick
			where
				EMS.Person_id in ({$person_ids_str})
				and EMS.EvnMseStick_begDT is not null
				and datediff(month, (case when (EMS.EvnMseStick_endDT is not null) then EMS.EvnMseStick_endDT else dbo.tzGetDate() end), dbo.tzGetDate()) <=12
		";

		//echo getDebugSql($query, $data);
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Method description
	 */
	function saveEvnMseStick($data)
	{
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMseStick_id;
			exec p_EvnMseStick_".$data['action']."
				@EvnMseStick_id = @Res output,
				@Person_id = :Person_id,
				@EvnMseStick_begDT = :EvnMseStick_begDT,
				@EvnMseStick_endDT = :EvnMseStick_endDT,
				@Diag_id = :Diag_id,
				@EvnMseStick_StickNum = :EvnMseStick_StickNum,
				@EvnMseStick_IsStick = :EvnMseStick_IsStick,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnMseStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function saveEvnStick($data)
	{
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnStick_id;
			exec p_EvnStick_upd
				@EvnStick_id = @Res output,
				@EvnStick_pid = :EvnStick_pid,
				@EvnStick_rid = :EvnStick_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnStick_setDT = :EvnStick_setDT,
				@EvnStick_disDT = :EvnStick_disDT,
				@EvnStick_didDT = :EvnStick_didDT,
				@EvnStick_insDT = :EvnStick_insDT,
				@EvnStick_updDT = :EvnStick_updDT,
				@EvnStick_Index = :EvnStick_Index,
				@EvnStick_Count = :EvnStick_Count,
				@EvnStick_Ser = :EvnStick_Ser,
				@EvnStick_Num = :EvnStick_Num,
				@EvnStick_Age = :EvnStick_Age,
				@StickCause_id = :StickCause_id,
				@MedPersonal_id = :MedPersonal_id,
				@Org_id = :Org_id,
				@StickType_id = :StickType_id,
				@StickWorkType_id = :StickWorkType_id,
				@StickOrder_id = :StickOrder_id,
				@EvnStick_SerCont = :EvnStick_SerCont,
				@EvnStick_NumCont = :EvnStick_NumCont,
				@Post_Name = :Post_Name,
				@StickCauseDopType_id = :StickCauseDopType_id,
				@EvnStick_IsDisability = :EvnStick_IsDisability,
				@StickCause_did = :StickCause_did,
				@Org_did = :Org_did,
				@EvnStick_IsRegPregnancy = :EvnStick_IsRegPregnancy,
				@EvnStick_mid = :EvnStick_mid,
				@EvnStick_begDate = :EvnStick_begDate,
				@EvnStick_endDate = :EvnStick_endDate,
				@Sex_id = :Sex_id,
				@EvnStick_prid = :EvnStick_prid,
				@EvnStick_SerOsn = :EvnStick_SerOsn,
				@EvnStick_NumOsn = :EvnStick_NumOsn,
				@EvnStick_BirthDate = :EvnStick_BirthDate,
				@Person_sid = :Person_sid,
				@EvnStick_sstBegDate = :EvnStick_sstBegDate,
				@EvnStick_sstEndDate = :EvnStick_sstEndDate,
				@EvnStick_sstNum = :EvnStick_sstNum,
				@EvnStick_sstPlace = :EvnStick_sstPlace,
				@EvnStick_mseDT = :EvnStick_mseDT,
				@MedPersonal_vkid = :MedPersonal_vkid,
				@EvnStick_mseRegDT = :EvnStick_mseRegDT,
				@EvnStick_mseExamDT = :EvnStick_mseExamDT,
				@MedPersonal_mseid = :MedPersonal_mseid,
				@EvnStick_mseConcl = :EvnStick_mseConcl,
				@Diag_pid = :Diag_pid,
				@StickIrregularity_id = :StickIrregularity_id,
				@EvnStick_irrDT = :EvnStick_irrDT,
				@EvnStick_workDT = :EvnStick_workDT,
				@StickLeaveType_id = :StickLeaveType_id,
				@EvnStick_stacBegDate = :EvnStick_stacBegDate,
				@EvnStick_stacEndDate = :EvnStick_stacEndDate,
				@EvnStick_regBegDate = :EvnStick_regBegDate,
				@EvnStick_regEndDate = :EvnStick_regEndDate,
				@MedPersonal_rid = :MedPersonal_rid,
				@EvnStick_alienBegDate = :EvnStick_alienBegDate,
				@EvnStick_alienEndDate = :EvnStick_alienEndDate,
				@StickRegime_id = :StickRegime_id,
				@Lpu_oid = :Lpu_oid,
				@EvnStick_isRegService = :EvnStick_isRegService,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
	
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnStick($data)
	{
		$query = "
			select * from v_EvnStick with(nolock) where EvnStick_id = :EvnStick_id and Person_id = :Person_id
		";
	
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function saveEvnPrescrVK($data)
	{
		$proc = 'p_EvnPrescrVK_ins';
		if (!empty($data['EvnPrescrVK_id'])) {
			$proc = 'p_EvnPrescrVK_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnPrescrVK_id;
			exec {$proc}
				@EvnPrescrVK_id = @Res output,
				@EvnPrescrVK_pid = :EvnPrescrVK_pid,
				@EvnPrescrVK_rid = :EvnPrescrVK_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPrescrVK_setDT = :EvnPrescrVK_setDT,
				@EvnPrescrVK_disDT = :EvnPrescrVK_disDT,
				@EvnPrescrVK_didDT = :EvnPrescrVK_didDT,
				@EvnPrescrVK_Index = :EvnPrescrVK_Index,
				@EvnPrescrVK_Count = :EvnPrescrVK_Count,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@EvnPrescrVK_Descr = :EvnPrescrVK_Descr,
				@EvnPrescrVK_IsExec = :EvnPrescrVK_IsExec,
				@TimetableGraf_id = :TimetableGraf_id,
				@TimetableMedService_id = :TimetableMedService_id,
				@MedService_id = :MedService_id,
				@CauseTreatmentType_id = :CauseTreatmentType_id,
				@Diag_id = :Diag_id,
				@EvnStick_id = :EvnStick_id,
				@MedPersonal_sid = :MedPersonal_sid,
				@LpuSection_sid = :LpuSection_sid,
				@MedPersonal_cid = :MedPersonal_cid,
				@LpuSection_cid = :LpuSection_cid,
				@EvnPrescrVK_Note = :EvnPrescrVK_Note,
				@EvnPrescrVK_LVN = :EvnPrescrVK_LVN,
				@Lpu_gid = :Lpu_gid,
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@EvnDirectionHTM_id = :EvnDirectionHTM_id,
				@EvnXml_id = :EvnXml_id,
				@PalliatQuestion_id = :PalliatQuestion_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPrescrVK_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSql($query, $data); return false;
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Возвращает массив данных для грида АРМ Врачебной комиссии
	 *
	 * @param array $data
	 * @return array
	 */
	function loadEvnPrescrVKGrid($data)
	{
		$filter = '';
		if(!empty($data['Person_SurName']))
		{
			$data['Person_SurName'] = rtrim($data['Person_SurName']);
			$filter .= ' and PA.Person_SurName like :Person_SurName+\'%\'';
		}
		
		if(!empty($data['Person_FirName']))
		{
			$data['Person_FirName'] = rtrim($data['Person_FirName']);
			$filter .= ' and PA.Person_FirName like :Person_FirName+\'%\'';
		}
			
		if(!empty($data['Person_SecName']))
		{
			$data['Person_SecName'] = rtrim($data['Person_SecName']);
			$filter .= ' and PA.Person_SecName like :Person_SecName+\'%\'';
		}
			
		if(!empty($data['Person_BirthDay']))
			$filter .= ' and PA.Person_BirthDay = :Person_BirthDay';
		
		if(!empty($data['isEvnVK'])){
			switch($data['isEvnVK']){
				case 1:
					$filter .= ' and EVK.EvnVK_id is not null';
				break;
				case 2:
					$filter .= ' and EVK.EvnVK_id is null';
				break;
			}
		}
		
		if(!empty($data['isEvnPrescrMse'])){
			switch($data['isEvnPrescrMse']){
				case 1:
					$filter .= ' and EPM.EvnPrescrMse_id is not null';
				break;
				case 2:
					$filter .= ' and EPM.EvnPrescrMse_id is null';
				break;
			}
		}
		
		if(!empty($data['EvnStatus_id'])){
			$filter .= ' and EPM.EvnStatus_id = :EvnStatus_id';
		}

		if(!empty($data['isEvnMse'])){
			switch($data['isEvnMse']){
				case 1:
					$filter .= ' and EM.EvnMse_id is not null';
				break;
				case 2:
					$filter .= ' and EM.EvnMse_id is null';
				break;
			}
		}
		
		$data['MedPersonal_id'] = $data['session']['medpersonal_id'];

		$data['MedPersonal_id'] = $data['session']['medpersonal_id'];

		$response = array();
		$keys = array();

		// в целях оптимизации запроса разделил его на несколько разных запросов
		$resp1 = $this->queryResult("
			select
				null as EvnPrescrVK_id,
				null as EvnPrescrVK_pid,
				null as EvnVK_id,
				null as EvnVK_IsSigned,
				null as EvnVK_signDT,
				null as pmUser_signName,
				TTMS.TimetableMedService_id,
				isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'без записи') as EvnPrescrVK_setDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з')  as EvnPrescrVK_setTime,
				null as CauseTreatmentType_id,
				null as CauseTreatmentType_Name,
				null as Diag_id,
				null as  EvnVK_NumCard,
				null as  Diag_Name,
				null as Person_id,
				null as PersonEvn_id,
				null as Server_id,
				null as  Person_Fio,
				null as  Person_BirthDay,
				null as LpuSection_id,
				null as LpuSection_FullName,
				null as MedPersonal_id,
				null as  MedPerson_Fio,
				null as  EvnVK_setDT,
				null as  EvnVK_NumProtocol,
				null as ExpertiseNameType_Name,
				null as  ExpertiseEventType_Name,
				null as  ExpertiseEventType_Code,
				null as  EvnPrescrMse_setDT,
				null as EvnPrescrMse_id,
				null as EvnPrescrMse_IsSigned,
				null as EvnPrescrMse_signDT,
				'' as  EvnMse,
				null as  EvnStickBase_id,
				null as  EvnStick_all,
				null as  EvnVK_LVN,
				null as EvnVK_Note,
				null as EvnDirectionHTM_id,
				null as EvnDirectionHTM_IsSigned,
				null as EvnDirectionHTM_setDT,
				null as EvnStatusVK_id,
				null as VoteListVK_id,
				null as VoteListVK_isFinished,
				null as VoteExpertVK_id,
				null as EvnVK_isAccepted,
				null as EvnStatus_id,
				null as EvnStatus_Name,
				null as EvnStatus_SysNick,
				null as EvnPrescrMse_appointDT,
				null as EvnVK_IsFail,
				null as ExpertiseNameType_id,
				null as ExpertiseEventType_id,
				null as EvnDirection_From,
				null as EvnMse_id,
				null as EPM_MedService_id,
				null as EPM_MedService_Name,
				null as EvnVK_DecisionVK
			from
				v_TimetableMedService_lite TTMS with(nolock)
			where
				cast(ISNULL(TTMS.TimetableMedService_begTime, :begDate) as date) >= :begDate
				and cast(ISNULL(TTMS.TimetableMedService_begTime, :endDate) as date) <= :endDate
				and TTMS.MedService_id = :MedService_id
		", $data);
		foreach($resp1 as $one) {
			$response[] = $one;
		}

		$resp2 = $this->queryResult("
			select
				EPVK.EvnPrescrVK_id,
				EPVK.EvnPrescrVK_pid,
				EVK.EvnVK_id,
				EVK.EvnVK_IsSigned,
				convert(varchar(10), EVK.EvnVK_signDT, 104) as EvnVK_signDT,
				pu.pmUser_Name as pmUser_signName,
				EVK.EvnVK_DecisionVK,
				TTMS.TimetableMedService_id,
				isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'без записи') as EvnPrescrVK_setDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з')  as EvnPrescrVK_setTime,
				CTT.CauseTreatmentType_id,
				CTT.CauseTreatmentType_Name,
				EPVK.Diag_id,
				case when EPL.EvnPL_id is not null
					then EPL.EvnPL_NumCard
					else EPS.EvnPS_NumCard
				end as EvnVK_NumCard,
				D.Diag_FullName as Diag_Name,
				PA.Person_id,
				EPVK.PersonEvn_id,
				PA.Server_id,
				PA.Person_Fio as Person_Fio,
				convert(varchar(10), PA.Person_BirthDay, 104) as Person_BirthDay,
				LS.LpuSection_id,
				LS.LpuSection_FullName,
				MP.MedPersonal_id,
				MP.Person_Fio as MedPerson_Fio,
				convert(varchar(10), EVK.EvnVK_setDT, 104) as EvnVK_setDT,
				EVK.EvnVK_NumProtocol,
				ENT.ExpertiseNameType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Name,'') as ExpertiseEventType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Code,-1) as ExpertiseEventType_Code,
				convert(varchar(10), isnull(EPM.EvnPrescrMse_issueDT,EPM.EvnPrescrMse_setDT), 104) as EvnPrescrMse_setDT,
				EPM.EvnPrescrMse_id,
				EPM.EvnPrescrMse_IsSigned,
				convert(varchar(10), EPM.EvnPrescrMse_signDT, 104) as EvnPrescrMse_signDT,
				case when EM.EvnMse_id is not null
					then '№' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), EM.EvnMse_setDT, 104)
					else ''
				end as EvnMse,
				ES.EvnStick_id as EvnStickBase_id,
				(
					isnull(ES.EvnStick_Ser, '')+' '+isnull(ES.EvnStick_Num, '')+
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '+convert(varchar(10), ES.EvnStick_begDate, 104) else '' end)+
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '+convert(varchar(10), ES.EvnStick_endDate, 104) else '' end)
				) as EvnStick_all,
				EPVK.EvnPrescrVK_LVN as EvnVK_LVN,
				EPVK.EvnPrescrVK_Note as EvnVK_Note,
				EDH.EvnDirectionHTM_id,
				EDH.EvnDirectionHTM_IsSigned,
				convert(varchar(10), EDH.EvnDirectionHTM_setDT, 104) as EvnDirectionHTM_setDT,
				EPVK.EvnStatus_id as EvnStatusVK_id,
				VLVK.VoteListVK_id,
				VLVK.VoteListVK_isFinished,
				vek.VoteExpertVK_id,
				case 
					when 
						vek.VoteExpertVK_id is not null or 
						evke.EvnVKExpert_id is not null 
					then 2 
					else null 
				end as isExpert,
				EVK.EvnVK_isAccepted,
				EPMS.EvnStatus_id,
				EPMS.EvnStatus_Name,
				EPMS.EvnStatus_SysNick,
				convert(varchar(10), EPM.EvnPrescrMse_appointDT, 104) + ' ' + convert(varchar(5), EPM.EvnPrescrMse_appointDT, 108) as EvnPrescrMse_appointDT,
				EVK.EvnVK_IsFail,
				EVK.ExpertiseNameType_id,
				EVK.ExpertiseEventType_id,
				EPMP.EvnDirection_From,
				EM.EvnMse_id,
				MS.MedService_id as EPM_MedService_id,
				MS.MedService_Name as EPM_MedService_Name
			from
				v_EvnPrescrVK EPVK with(nolock)
				outer apply (
					select top 1
						l.Lpu_Nick + ISNULL(' / ' + ls.LpuSection_Name, '') + ISNULL(' / ' + MSF.Person_Fio, '') as EvnDirection_From
					from
						v_MedStaffFact MSF with (nolock)
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPVK.LpuSection_sid
						inner join v_Lpu l with (nolock) on l.Lpu_id = coalesce(EPVK.Lpu_gid,ls.Lpu_id,msf.Lpu_id)
					where
						MSF.MedPersonal_id = EPVK.MedPersonal_sid
				) EPMP
				left join v_EvnVK EVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = EVK.pmUser_signID
				LEFT JOIN v_ExpertiseEventType ExpertiseEventType with (nolock) on ExpertiseEventType.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_Person_all PA with(nolock) on PA.PersonEvn_id = EPVK.PersonEvn_id and PA.Server_id = EPVK.Server_id
				left join v_Diag D with(nolock) on D.Diag_id = EPVK.Diag_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPVK.LpuSection_sid
				outer apply (
					select top 1
						MedPersonal_id,
						Person_Fio
					from v_MedPersonal with(nolock)
					where MedPersonal_id = EPVK.MedPersonal_sid
						and Lpu_id = EPVK.Lpu_id
				) MP
				left join v_EvnPrescrMse EPMVK with(nolock) on EPMVK.EvnVK_id = EVK.EvnVK_id
				left join v_EvnPrescrMse EPM with(nolock) on EPM.EvnPrescrMse_id = ISNULL(EPVK.EvnPrescrMse_id, EPMVK.EvnPrescrMse_id)
				left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_EvnStatus EPMS with(nolock) on EPMS.EvnStatus_id = EPM.EvnStatus_id
				left Join v_CauseTreatmentType CTT with(nolock) on CTT.CauseTreatmentType_id = EPVK.CauseTreatmentType_id
				left join v_ExpertiseNameType ENT with(nolock) on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join v_TimetableMedService_lite TTMS with(nolock) on TTMS.TimetableMedService_id = EPVK.TimetableMedService_id
					--and TTMS.MedService_id = EPVK.MedService_id
				--пока этот джойн не нужен left join v_EvnQueue EQ with(nolock) on EQ.EvnQueue_pid = EPVK.EvnPrescrVK_id and EQ.MedService_id = EPVK.MedService_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = EPVK.EvnPrescrVK_pid
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = EPVK.EvnPrescrVK_pid
				left join v_EvnStick ES with(nolock) on ES.EvnStick_id = EPVK.EvnStick_id
				left join v_EvnDirectionHTM EDH with(nolock) on 
					EDH.EvnDirectionHTM_pid = EVK.EvnVK_id or 
					EDH.EvnDirectionHTM_id = EPVK.EvnDirectionHTM_id
				left join v_MedService MS with(nolock) on MS.MedService_id = EPM.MedService_id
				left join v_EvnQueue EQ with (nolock) on EQ.EvnQueue_id = EPVK.EvnQueue_id
				left join v_VoteListVK VLVK (nolock) on VLVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				outer apply (
					select top 1 VoteExpertVK_id
					from v_VoteExpertVK vek (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = vek.MedServiceMedPersonal_id
					where
						vek.VoteListVK_id = VLVK.VoteListVK_id and 
						msmp.MedPersonal_id = :MedPersonal_id
				) vek
				outer apply (
					select top 1 EvnVKExpert_id
					from v_EvnVKExpert evke (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
					where
						evke.EvnVK_id = EVK.EvnVK_id and 
						msmp.MedPersonal_id = :MedPersonal_id
				) evke
			where
				(
					(
						EVK.EvnVK_setDT is null 
						and cast(ISNULL(TTMS.TimetableMedService_begTime, :begDate) as date) >= :begDate
						and cast(ISNULL(TTMS.TimetableMedService_begTime, :endDate) as date) <= :endDate
						and cast(EQ.EvnQueue_setDate as date)  <= :endDate
					)
					or (
						cast(EVK.EvnVK_setDT as date) >= :begDate
						and cast(EVK.EvnVK_setDT as date) <= :endDate
					)
				)
				and EPVK.MedService_id = :MedService_id
				and (
					:CauseTreatmentType_id is null
					or EPVK.CauseTreatmentType_id = :CauseTreatmentType_id
				)
				{$filter}
		", $data);
		foreach($resp2 as $one) {
			if (!in_array($one['EvnPrescrVK_id'], $keys)) {
				$keys[] = $one['EvnPrescrVK_id'];
				$response[] = $one;
			}
		}

		$resp3 = $this->queryResult("
			select
				EPVK.EvnPrescrVK_id,
				EPVK.EvnPrescrVK_pid,
				EVK.EvnVK_id,
				EVK.EvnVK_IsSigned,
				convert(varchar(10), EVK.EvnVK_signDT, 104) as EvnVK_signDT,
				pu.pmUser_Name as pmUser_signName,
				EVK.EvnVK_DecisionVK,
				TTMS.TimetableMedService_id,
				isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'без записи') as EvnPrescrVK_setDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з')  as EvnPrescrVK_setTime,
				CTT.CauseTreatmentType_id,
				CTT.CauseTreatmentType_Name,
				EPVK.Diag_id,
				case when EPL.EvnPL_id is not null
					then EPL.EvnPL_NumCard
					else EPS.EvnPS_NumCard
				end as EvnVK_NumCard,
				D.Diag_FullName as Diag_Name,
				PA.Person_id,
				EPVK.PersonEvn_id,
				PA.Server_id,
				PA.Person_Fio as Person_Fio,
				convert(varchar(10), PA.Person_BirthDay, 104) as Person_BirthDay,
				LS.LpuSection_id,
				LS.LpuSection_FullName,
				MP.MedPersonal_id,
				MP.Person_Fio as MedPerson_Fio,
				convert(varchar(10), EVK.EvnVK_setDT, 104) as EvnVK_setDT,
				EVK.EvnVK_NumProtocol,
				ENT.ExpertiseNameType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Name,'') as ExpertiseEventType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Code,-1) as ExpertiseEventType_Code,
				convert(varchar(10), isnull(EPM.EvnPrescrMse_issueDT,EPM.EvnPrescrMse_setDT), 104) as EvnPrescrMse_setDT,
				EPM.EvnPrescrMse_id,
				EPM.EvnPrescrMse_IsSigned,
				convert(varchar(10), EPM.EvnPrescrMse_signDT, 104) as EvnPrescrMse_signDT,
				case when EM.EvnMse_id is not null
					then '№' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), EM.EvnMse_setDT, 104)
					else ''
				end as EvnMse,
				ES.EvnStick_id as EvnStickBase_id,
				(
					isnull(ES.EvnStick_Ser, '')+' '+isnull(ES.EvnStick_Num, '')+
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '+convert(varchar(10), ES.EvnStick_begDate, 104) else '' end)+
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '+convert(varchar(10), ES.EvnStick_endDate, 104) else '' end)
				) as EvnStick_all,
				EPVK.EvnPrescrVK_LVN as EvnVK_LVN,
				EPVK.EvnPrescrVK_Note as EvnVK_Note,
				EDH.EvnDirectionHTM_id,
				EDH.EvnDirectionHTM_IsSigned,
				convert(varchar(10), EDH.EvnDirectionHTM_setDT, 104) as EvnDirectionHTM_setDT,
				EPVK.EvnStatus_id as EvnStatusVK_id,
				VLVK.VoteListVK_id,
				VLVK.VoteListVK_isFinished,
				vek.VoteExpertVK_id,
				case 
					when 
						vek.VoteExpertVK_id is not null or 
						evke.EvnVKExpert_id is not null 
					then 2 
					else null 
				end as isExpert,
				EVK.EvnVK_isAccepted,
				EPMS.EvnStatus_id,
				EPMS.EvnStatus_Name,
				EPMS.EvnStatus_SysNick,
				convert(varchar(10), EPM.EvnPrescrMse_appointDT, 104) + ' ' + convert(varchar(5), EPM.EvnPrescrMse_appointDT, 108) as EvnPrescrMse_appointDT,
				EVK.EvnVK_IsFail,
				EVK.ExpertiseNameType_id,
				EVK.ExpertiseEventType_id,
				EPMP.EvnDirection_From,
				EM.EvnMse_id,
				MS.MedService_id as EPM_MedService_id,
				MS.MedService_Name as EPM_MedService_Name
			from
				v_EvnPrescrVK EPVK with(nolock)
				outer apply (
					select top 1
						l.Lpu_Nick + ISNULL(' / ' + ls.LpuSection_Name, '') + ISNULL(' / ' + MSF.Person_Fio, '') as EvnDirection_From
					from
						v_MedStaffFact MSF with (nolock)
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPVK.LpuSection_sid
						inner join v_Lpu l with (nolock) on l.Lpu_id = coalesce(EPVK.Lpu_gid,ls.Lpu_id,msf.Lpu_id)
					where
						MSF.MedPersonal_id = EPVK.MedPersonal_sid
				) EPMP
				left join v_EvnVK EVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = EVK.pmUser_signID
				LEFT JOIN v_ExpertiseEventType ExpertiseEventType with (nolock) on ExpertiseEventType.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_Person_all PA with(nolock) on PA.PersonEvn_id = EPVK.PersonEvn_id and PA.Server_id = EPVK.Server_id
				left join v_Diag D with(nolock) on D.Diag_id = EPVK.Diag_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPVK.LpuSection_sid
				outer apply (
					select top 1
						MedPersonal_id,
						Person_Fio
					from v_MedPersonal with(nolock)
					where MedPersonal_id = EPVK.MedPersonal_sid
						and Lpu_id = EPVK.Lpu_id
				) MP
				left join v_EvnPrescrMse EPMVK with(nolock) on EPMVK.EvnVK_id = EVK.EvnVK_id
				left join v_EvnPrescrMse EPM with(nolock) on EPM.EvnPrescrMse_id = ISNULL(EPVK.EvnPrescrMse_id, EPMVK.EvnPrescrMse_id)
				left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_EvnStatus EPMS with(nolock) on EPMS.EvnStatus_id = EPM.EvnStatus_id
				left Join v_CauseTreatmentType CTT with(nolock) on CTT.CauseTreatmentType_id = EPVK.CauseTreatmentType_id
				left join v_ExpertiseNameType ENT with(nolock) on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join v_TimetableMedService_lite TTMS with(nolock) on TTMS.TimetableMedService_id = EPVK.TimetableMedService_id and TTMS.Person_id = EPVK.Person_id
					--and TTMS.MedService_id = EPVK.MedService_id
				--пока этот джойн не нужен left join v_EvnQueue EQ with(nolock) on EQ.EvnQueue_pid = EPVK.EvnPrescrVK_id and EQ.MedService_id = EPVK.MedService_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = EPVK.EvnPrescrVK_pid
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = EPVK.EvnPrescrVK_pid
				left join v_EvnStick ES with(nolock) on ES.EvnStick_id = EPVK.EvnStick_id
				left join v_EvnDirectionHTM EDH with(nolock) on 
					EDH.EvnDirectionHTM_pid = EVK.EvnVK_id or 
					EDH.EvnDirectionHTM_id = EPVK.EvnDirectionHTM_id
				left join v_MedService MS with(nolock) on MS.MedService_id = EPM.MedService_id
				left join v_VoteListVK VLVK (nolock) on VLVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				outer apply (
					select top 1 VoteExpertVK_id
					from v_VoteExpertVK vek (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = vek.MedServiceMedPersonal_id
					where
						vek.VoteListVK_id = VLVK.VoteListVK_id and 
						msmp.MedPersonal_id = :MedPersonal_id
				) vek
				outer apply (
					select top 1 EvnVKExpert_id
					from v_EvnVKExpert evke (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
					where
						evke.EvnVK_id = EVK.EvnVK_id and 
						msmp.MedPersonal_id = :MedPersonal_id
				) evke
			where
				cast(EVK.EvnVK_setDT as date) >= :begDate
				and cast(EVK.EvnVK_setDT as date) <= :endDate
				and EPVK.MedService_id = :MedService_id
				and (
					:CauseTreatmentType_id is null
					or EPVK.CauseTreatmentType_id = :CauseTreatmentType_id
				)
				{$filter}
		", $data);
		foreach($resp3 as $one) {
			if (!in_array($one['EvnPrescrVK_id'], $keys)) {
				$keys[] = $one['EvnPrescrVK_id'];
				$response[] = $one;
			}
		}

		$resp4 = $this->queryResult("
			select
				EPVK.EvnPrescrVK_id,
				EPVK.EvnPrescrVK_pid,
				EVK.EvnVK_id,
				EVK.EvnVK_IsSigned,
				convert(varchar(10), EVK.EvnVK_signDT, 104) as EvnVK_signDT,
				pu.pmUser_Name as pmUser_signName,
				EVK.EvnVK_DecisionVK,
				TTMS.TimetableMedService_id,
				isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'без записи') as EvnPrescrVK_setDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з')  as EvnPrescrVK_setTime,
				CTT.CauseTreatmentType_id,
				CTT.CauseTreatmentType_Name,
				EPVK.Diag_id,
				case when EPL.EvnPL_id is not null
					then EPL.EvnPL_NumCard
					else EPS.EvnPS_NumCard
				end as EvnVK_NumCard,
				D.Diag_FullName as Diag_Name,
				PA.Person_id,
				EPVK.PersonEvn_id,
				PA.Server_id,
				PA.Person_Fio as Person_Fio,
				convert(varchar(10), PA.Person_BirthDay, 104) as Person_BirthDay,
				LS.LpuSection_id,
				LS.LpuSection_FullName,
				MP.MedPersonal_id,
				MP.Person_Fio as MedPerson_Fio,
				convert(varchar(10), EVK.EvnVK_setDT, 104) as EvnVK_setDT,
				EVK.EvnVK_NumProtocol,
				ENT.ExpertiseNameType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Name,'') as ExpertiseEventType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Code,-1) as ExpertiseEventType_Code,
				convert(varchar(10), isnull(EPM.EvnPrescrMse_issueDT,EPM.EvnPrescrMse_setDT), 104) as EvnPrescrMse_setDT,
				EPM.EvnPrescrMse_id,
				EPM.EvnPrescrMse_IsSigned,
				convert(varchar(10), EPM.EvnPrescrMse_signDT, 104) as EvnPrescrMse_signDT,
				case when EM.EvnMse_id is not null
					then '№' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), EM.EvnMse_setDT, 104)
					else ''
				end as EvnMse,
				ES.EvnStick_id as EvnStickBase_id,
				(
					isnull(ES.EvnStick_Ser, '')+' '+isnull(ES.EvnStick_Num, '')+
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '+convert(varchar(10), ES.EvnStick_begDate, 104) else '' end)+
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '+convert(varchar(10), ES.EvnStick_endDate, 104) else '' end)
				) as EvnStick_all,
				EPVK.EvnPrescrVK_LVN as EvnVK_LVN,
				EPVK.EvnPrescrVK_Note as EvnVK_Note,
				EDH.EvnDirectionHTM_id,
				EDH.EvnDirectionHTM_IsSigned,
				convert(varchar(10), EDH.EvnDirectionHTM_setDT, 104) as EvnDirectionHTM_setDT,
				EPVK.EvnStatus_id as EvnStatusVK_id,
				VLVK.VoteListVK_id,
				VLVK.VoteListVK_isFinished,
				vek.VoteExpertVK_id,
				case 
					when 
						vek.VoteExpertVK_id is not null or 
						evke.EvnVKExpert_id is not null 
					then 2 
					else null 
				end as isExpert,
				EVK.EvnVK_isAccepted,
				EPMS.EvnStatus_id,
				EPMS.EvnStatus_Name,
				EPMS.EvnStatus_SysNick,
				convert(varchar(10), EPM.EvnPrescrMse_appointDT, 104) + ' ' + convert(varchar(5), EPM.EvnPrescrMse_appointDT, 108) as EvnPrescrMse_appointDT,
				EVK.EvnVK_IsFail,
				EVK.ExpertiseNameType_id,
				EVK.ExpertiseEventType_id,
				EPMP.EvnDirection_From,
				EM.EvnMse_id,
				MS.MedService_id as EPM_MedService_id,
				MS.MedService_Name as EPM_MedService_Name
			from
				v_EvnPrescrVK EPVK with(nolock)
				outer apply (
					select top 1
						l.Lpu_Nick + ISNULL(' / ' + ls.LpuSection_Name, '') + ISNULL(' / ' + MSF.Person_Fio, '') as EvnDirection_From
					from
						v_MedStaffFact MSF with (nolock)
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPVK.LpuSection_sid
						inner join v_Lpu l with (nolock) on l.Lpu_id = coalesce(EPVK.Lpu_gid,ls.Lpu_id,msf.Lpu_id)
					where
						MSF.MedPersonal_id = EPVK.MedPersonal_sid
				) EPMP
				left join v_TimetableMedService_lite TTMS with(nolock) on TTMS.TimetableMedService_id = EPVK.TimetableMedService_id and TTMS.Person_id = EPVK.Person_id
				left join v_EvnVK EVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = EVK.pmUser_signID
				LEFT JOIN v_ExpertiseEventType ExpertiseEventType with (nolock) on ExpertiseEventType.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_Person_all PA with(nolock) on PA.PersonEvn_id = EPVK.PersonEvn_id and PA.Server_id = EPVK.Server_id
				left join v_Diag D with(nolock) on D.Diag_id = EPVK.Diag_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPVK.LpuSection_sid
				outer apply (
					select top 1
						MedPersonal_id,
						Person_Fio
					from v_MedPersonal with(nolock)
					where MedPersonal_id = EPVK.MedPersonal_sid
						and Lpu_id = EPVK.Lpu_id
				) MP
				left join v_EvnPrescrMse EPMVK with(nolock) on EPMVK.EvnVK_id = EVK.EvnVK_id
				left join v_EvnPrescrMse EPM with(nolock) on EPM.EvnPrescrMse_id = ISNULL(EPVK.EvnPrescrMse_id, EPMVK.EvnPrescrMse_id)
				left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_EvnStatus EPMS with(nolock) on EPMS.EvnStatus_id = EPM.EvnStatus_id
				left Join v_CauseTreatmentType CTT with(nolock) on CTT.CauseTreatmentType_id = EPVK.CauseTreatmentType_id
				left join v_ExpertiseNameType ENT with(nolock) on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				--пока этот джойн не нужен left join v_EvnQueue EQ with(nolock) on EQ.EvnQueue_pid = EPVK.EvnPrescrVK_id and EQ.MedService_id = EPVK.MedService_id
				/* на назначение должно быть неотмененное направление */
				inner join v_EvnDirection_all ED with(nolock) on ED.EvnDirection_id = TTMS.EvnDirection_id and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = EPVK.EvnPrescrVK_pid
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = EPVK.EvnPrescrVK_pid
				left join v_EvnStick ES with(nolock) on ES.EvnStick_id = EPVK.EvnStick_id
				left join v_EvnDirectionHTM EDH with(nolock) on 
					EDH.EvnDirectionHTM_pid = EVK.EvnVK_id or 
					EDH.EvnDirectionHTM_id = EPVK.EvnDirectionHTM_id
				left join v_MedService MS with(nolock) on MS.MedService_id = EPM.MedService_id
				left join v_VoteListVK VLVK (nolock) on VLVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				outer apply (
					select top 1 VoteExpertVK_id
					from v_VoteExpertVK vek (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = vek.MedServiceMedPersonal_id
					where
						vek.VoteListVK_id = VLVK.VoteListVK_id and 
						msmp.MedPersonal_id = :MedPersonal_id
				) vek
				outer apply (
					select top 1 EvnVKExpert_id
					from v_EvnVKExpert evke (nolock)
					inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
					where
						evke.EvnVK_id = EVK.EvnVK_id and 
						msmp.MedPersonal_id = :MedPersonal_id
				) evke
			where
				EVK.EvnVK_setDT is null
				and cast(ISNULL(TTMS.TimetableMedService_begTime, EPVK.EvnPrescrVK_insDT) as date) >= :begDate
				and cast(ISNULL(TTMS.TimetableMedService_begTime, EPVK.EvnPrescrVK_insDT) as date) <= :endDate
				and EPVK.MedService_id = :MedService_id
				and (
					:CauseTreatmentType_id is null
					or EPVK.CauseTreatmentType_id = :CauseTreatmentType_id
				)
				{$filter}
		", $data);
		foreach($resp4 as $one) {
			if (!in_array($one['EvnPrescrVK_id'], $keys)) {
				$keys[] = $one['EvnPrescrVK_id'];
				$response[] = $one;
			}
		}

		$resp5 = $this->queryResult("
			select
				EVK.EvnPrescrVK_id,
				null as EvnPrescrVK_pid,
				EVK.EvnVK_id,
				EVK.EvnVK_IsSigned,
				convert(varchar(10), EVK.EvnVK_signDT, 104) as EvnVK_signDT,
				pu.pmUser_Name as pmUser_signName,
				EVK.EvnVK_DecisionVK,
				null as TimetableMedService_id,
				'без записи' as EvnPrescrVK_setDate,
				'б/з' as EvnPrescrVK_setTime,
				CTT.CauseTreatmentType_id as CauseTreatmentType_id,
				CTT.CauseTreatmentType_Name as CauseTreatmentType_Name,
				EVK.Diag_id,
				EVK.EvnVK_NumCard,
				D.Diag_FullName as Diag_Name,
				PA.Person_id,
				EVK.PersonEvn_id,
				PA.Server_id,
				PA.Person_Fio as Person_Fio,
				convert(varchar(10), PA.Person_BirthDay, 104) as Person_BirthDay,
				null as LpuSection_id,
				null as LpuSection_FullName,
				null as MedPersonal_id,
				null as MedPerson_Fio,
				convert(varchar(10), EVK.EvnVK_setDT, 104) as EvnVK_setDT,
				EVK.EvnVK_NumProtocol,
				ENT.ExpertiseNameType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Name,'') as ExpertiseEventType_Name,
				ISNULL(ExpertiseEventType.ExpertiseEventType_Code,-1) as ExpertiseEventType_Code,
				convert(varchar(10), isnull(EPM.EvnPrescrMse_issueDT,EPM.EvnPrescrMse_setDT), 104) as EvnPrescrMse_setDT,
				EPM.EvnPrescrMse_id,
				EPM.EvnPrescrMse_IsSigned,
				convert(varchar(10), EPM.EvnPrescrMse_signDT, 104) as EvnPrescrMse_signDT,
				case when EM.EvnMse_id is not null
					then '№' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), EM.EvnMse_setDT, 104)
					else ''
				end as EvnMse,
				EVK.EvnStickBase_id,
				(
					isnull(ES.EvnStick_Ser, '')+' '+isnull(ES.EvnStick_Num, '')+
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '+convert(varchar(10), ES.EvnStick_begDate, 104) else '' end)+
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '+convert(varchar(10), ES.EvnStick_endDate, 104) else '' end)
				) as EvnStick_all,
				EVK.EvnVK_LVN,
				case when (EVK.EvnVK_Note is not null) then EVK.EvnVK_Note else '' end as EvnVK_Note,
				EDH.EvnDirectionHTM_id,
				EDH.EvnDirectionHTM_IsSigned,
				convert(varchar(10), EDH.EvnDirectionHTM_setDT, 104) as EvnDirectionHTM_setDT,
				null as EvnStatusVK_id,
				null as VoteListVK_id,
				null as VoteListVK_isFinished,
				null as VoteExpertVK_id,
				null as EvnVK_isAccepted,
				EPMS.EvnStatus_id,
				EPMS.EvnStatus_Name,
				EPMS.EvnStatus_SysNick,
				convert(varchar(10), EPM.EvnPrescrMse_appointDT, 104) + ' ' + convert(varchar(5), EPM.EvnPrescrMse_appointDT, 108) as EvnPrescrMse_appointDT,
				EVK.EvnVK_IsFail,
				EVK.ExpertiseNameType_id,
				EVK.ExpertiseEventType_id,
				null as EvnDirection_From,
				EM.EvnMse_id,
				MS.MedService_id as EPM_MedService_id,
				MS.MedService_Name as EPM_MedService_Name
			from
				v_EvnVK EVK with(nolock)
				left join v_pmUser pu (nolock) on pu.pmUser_id = EVK.pmUser_signID
				left join v_Person_all PA with(nolock) on PA.PersonEvn_id = EVK.PersonEvn_id and PA.Server_id = EVK.Server_id
				LEFT JOIN v_ExpertiseEventType ExpertiseEventType with (nolock) on ExpertiseEventType.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_Diag D with(nolock) on D.Diag_id = EVK.Diag_id
				left join v_EvnPrescrMse EPM with(nolock) on EPM.EvnVK_id = EVK.EvnVK_id
				left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id or EM.EvnVK_id = EVK.EvnVK_id
				left join v_EvnStatus EPMS with(nolock) on EPMS.EvnStatus_id = EPM.EvnStatus_id
				left join v_CauseTreatmentType CTT with(nolock) on CTT.CauseTreatmentType_id = EVK.CauseTreatmentType_id
				left join v_ExpertiseNameType ENT with(nolock) on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join v_EvnStick ES with(nolock) on ES.EvnStick_id = EVK.EvnStickBase_id
				left join v_EvnDirectionHTM EDH with(nolock) on EDH.EvnDirectionHTM_pid = EVK.EvnVK_id
				left join v_MedService MS with(nolock) on MS.MedService_id = EPM.MedService_id
				
			where
				-- EVK.EvnPrescrVK_id is null and 
				EVK.MedService_id = :MedService_id
				and cast(EVK.EvnVK_setDT as date) >= :begDate
				and cast(EVK.EvnVK_setDT as date) <= :endDate
				and (
					:CauseTreatmentType_id is null
					or EVK.CauseTreatmentType_id = :CauseTreatmentType_id
				)
				{$filter}
		", $data);
		foreach($resp5 as $one) {
			if (empty($one['EvnPrescrVK_id'])){
				$response[] = $one;
			} else if (!in_array($one['EvnPrescrVK_id'], $keys)) {
				$keys[] = $one['EvnPrescrVK_id'];
				$response[] = $one;
			}
		}

		$EvnPrescrMseIds = [];
		foreach($response as $one) {
			if (!empty($one['EvnPrescrMse_id']) && $one['EvnPrescrMse_IsSigned'] == 2 && !in_array($one['EvnPrescrMse_id'], $EvnPrescrMseIds)) {
				$EvnPrescrMseIds[] = $one['EvnPrescrMse_id'];
			}
		}
		
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($EvnPrescrMseIds) && !empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$MedStaffFact_id = $data['session']['CurMedStaffFact_id'] ?? null;
			if (
				empty($MedStaffFact_id)
				&& !empty($data['session']['CurMedService_id'])
				&& !empty($data['session']['medpersonal_id'])
			) {
				// получаем данные по мед. работнику службы
				$resp_ms = $this->queryResult("
					select top 1
						msf.MedStaffFact_id
					from v_MedService ms (nolock)
					inner join v_MedStaffFact msf (nolock) on msf.LpuSection_id = ms.LpuSection_id
					where 
						ms.MedService_id = :MedService_id
						and msf.MedPersonal_id = :MedPersonal_id
				", array(
					'MedService_id' => $data['session']['CurMedService_id'],
					'MedPersonal_id' => $data['session']['medpersonal_id']
				));

				if (!empty($resp_ms[0]['MedStaffFact_id'])) {
					$MedStaffFact_id = $resp_ms[0]['MedStaffFact_id'];
				}
			}

			$signStatus = $this->EMD_model->getSignStatus([
				'EMDRegistry_ObjectName' => 'EvnPrescrMse',
				'EMDRegistry_ObjectIDs' => $EvnPrescrMseIds,
				'MedStaffFact_id' => $MedStaffFact_id
			]);

			foreach($response as $key => $one) {
				if (!empty($one['EvnPrescrMse_id']) && $one['EvnPrescrMse_IsSigned'] == 2) {
					if (isset($signStatus[$one['EvnPrescrMse_id']])) {
						$response[$key]['EvnPrescrMse_SignCount'] = $signStatus[$one['EvnPrescrMse_id']]['signcount'];
						$response[$key]['EvnPrescrMse_MinSignCount'] = $signStatus[$one['EvnPrescrMse_id']]['minsigncount'];
						$response[$key]['EvnPrescrMse_IsSigned'] = $signStatus[$one['EvnPrescrMse_id']]['signed'];
					} else {
						$response[$key]['EvnPrescrMse_SignCount'] = 0;
						$response[$key]['EvnPrescrMse_MinSignCount'] = 0;
					}
				}
			}
		}

		// order by EvnPrescrVK_setTime -- сортировку можно (или нужно) сделать на клиенте.

		return $response;
	}
	
	/**
	 *	Method description
	 */
	function loadEvnPrescrMseGrid($data)
	{
		$filter = '';
		if(!empty($data['Person_SurName']))
		{
			$data['Person_SurName'] = rtrim($data['Person_SurName']);
			$filter .= ' and PA.Person_SurName like :Person_SurName+\'%\'';
		}
		
		if(!empty($data['Person_FirName']))
		{
			$data['Person_FirName'] = rtrim($data['Person_FirName']);
			$filter .= ' and PA.Person_FirName like :Person_FirName+\'%\'';
		}
			
		if(!empty($data['Person_SecName']))
		{
			$data['Person_SecName'] = rtrim($data['Person_SecName']);
			$filter .= ' and PA.Person_SecName like :Person_SecName+\'%\'';
		}
			
		if(!empty($data['Person_BirthDay']))
			$filter .= ' and PA.Person_BirthDay = :Person_BirthDay';
		
		if(!empty($data['Lpu_id']))
			$filter .= ' and LPU.Lpu_id = :Lpu_id';
		
		if(!empty($data['isEvnMse'])) {
			switch($data['isEvnMse']){
				case 1:
					$filter .= ' and EM.EvnMse_id is not null';
				break;
				case 2:
					$filter .= ' and EM.EvnMse_id is null';
				break;
			}
		}
								
		if(!empty($data['Person_Snils']))
		{
			$data['Person_Snils'] = str_replace(array('-', ' '), '', $data['Person_Snils']);
			$filter .= ' and PA.Person_Snils like :Person_Snils+\'%\'';
		}		

		$dateFilter1 = "
			and (((TTMS.TimetableMedService_begTime is null or (cast(TTMS.TimetableMedService_begTime as date) >= :begDate and cast(TTMS.TimetableMedService_begTime as date) <= :endDate)))
				or (cast(EM.EvnMse_setDT as date) >= :begDate and cast(EM.EvnMse_setDT as date) <= :endDate))
		";
		$dateFilter2 = "
			and cast(EM.EvnMse_setDT as date) >= :begDate
			and cast(EM.EvnMse_setDT as date) <= :endDate
		";
		if (getRegionNick() == 'perm') {
			// фильтровать по значению поля «Дата выдачи» направления на МСЭ, а не даты записи. (за исключением направлений на статусе «Новое»)
			$dateFilter1 = "
				and (
					(cast(EPM.EvnPrescrMse_issueDT as date) >= :begDate and cast(EPM.EvnPrescrMse_issueDT as date) <= :endDate)
					or ISNULL(EPM.EvnStatus_id, 27) = 27
				)
			";
			$dateFilter2 = "
				and (
					(cast(EPM.EvnPrescrMse_issueDT as date) >= :begDate and cast(EPM.EvnPrescrMse_issueDT as date) <= :endDate)
					or ISNULL(EPM.EvnStatus_id, 27) = 27
				)
			";

			if (!empty($data['MSEDirStatus_id']) && getRegionNick() == 'perm') {
				switch($data['MSEDirStatus_id']) {
					case 2: // Отправлено
						$filter .= " and ES.EvnStatus_SysNick = 'Sended'";
						break;
					case 3: // Доработка в МО
						$filter .= " and ES.EvnStatus_SysNick = 'Rework'";
						break;
					case 4: // Принято
						$filter .= " and ES.EvnStatus_SysNick = 'Accept'";
						break;
					case 5: // Выполнено
						$filter .= " and ES.EvnStatus_SysNick = 'Done'";
						break;
					default:
						$filter .= "
							and EPM.EvnStatus_id is not null 
							and ES.EvnStatus_SysNick <> 'New'
						";
						break;
				}
			}
		}
		
		$response = array();

		// в целях оптимизации запроса разделил его на несколько разных запросов
		
		// с направлением
		$resp = $this->queryResult("
			with EvnPrescrMse(
				EvnPrescrMse_id,
				TimetableMedService_begTime,
				EvnPrescrMse_issueDT,
				EvnPrescrMse_IsFirstTime,
				EvnPrescrMse_appointDT,
				EvnPrescrMse_setDT,
				Person_id,
				Server_id,
				PersonEvn_id,
				EvnVK_id,
				LpuSection_sid,
				MseDirectionAimType_id,
				EvnStatus_id,
				InvalidGroupType_id,
				Lpu_gid,
				MedPersonal_sid
			) as (
				select 
					EPM.EvnPrescrMse_id,
					TTMS.TimetableMedService_begTime,
					EPM.EvnPrescrMse_issueDT,
					EPM.EvnPrescrMse_IsFirstTime,
					EPM.EvnPrescrMse_appointDT,
					EPM.EvnPrescrMse_setDT,
					EPM.Person_id,
					EPM.Server_id,
					EPM.PersonEvn_id,
					EPM.EvnVK_id,
					EPM.LpuSection_sid,
					EPM.MseDirectionAimType_id,
					EPM.EvnStatus_id,
					EPM.InvalidGroupType_id,
					EPM.Lpu_gid,
					EPM.MedPersonal_sid
				from
					v_EvnPrescrMse EPM (nolock)
					left join v_EvnMse EM (nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
					left join v_TimetableMedService_lite TTMS (nolock) on TTMS.TimetableMedService_id = EPM.TimetableMedService_id and TTMS.MedService_id = EPM.MedService_id
				where
					(EPM.MedService_id = :MedService_id or EM.MedService_id = :MedService_id)
					{$dateFilter1}
			)
			
			select
				EPM.EvnPrescrMse_id,
				isnull(convert(varchar(10), cast(EPM.TimetableMedService_begTime as datetime), 104), 'без записи') as EvnPrescrMse_setDT,
				convert(varchar(10), cast(EPM.EvnPrescrMse_setDT as datetime), 104) as EvnPrescrMse_setDate,
				convert(varchar(10), EPM.EvnPrescrMse_issueDT, 104) as EvnPrescrMse_issueDT,
				isnull(convert(varchar(5), cast(EPM.TimetableMedService_begTime as datetime), 108), 'б/з') as EvnPrescrMse_setTime,
				case
					when EPM.EvnPrescrMse_IsFirstTime = 1 then 'Первично'
					when EPM.EvnPrescrMse_IsFirstTime = 2 then 'Повторно'
					else ''
				end as EvnPrescrMse_IsFirstTime,
				MDAT.MseDirectionAimType_Name,
				EPM.Person_id,
				EPM.Server_id,
				PA.Person_Fio,
				convert(varchar(10), cast(PA.Person_BirthDay as datetime), 104) as Person_BirthDay,
				EVK.EvnVK_id,
				D.diag_FullName as Diag_Name,
				LPU.Lpu_Nick,
				EM.EvnMse_id,
				case when EM.EvnMse_id is not null
					then '№ ' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104)
					else ''
				end as EvnMse,
				case when EM.Signatures_id is not null then
					isnull(convert(varchar(10), S.Signatures_updDT, 104) + ' ', '') + 
					isnull(convert(varchar(5), S.Signatures_updDT, 108) + '. ', '') +
					isnull(PUS.PMUser_Name, '')
					else ''
				end as EvnMse_Sign,
				S.Signatures_id,
				convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104) as EvnMse_setDT,
				(select diag_FullName from v_Diag with(nolock) where Diag_id = EM.Diag_id) as DiagMse_Name,
				(select InvalidGroupType_Name from v_InvalidGroupType with(nolock) where InvalidGroupType_id = EPM.InvalidGroupType_id) as InvalidGroupType_Name,
				convert(varchar(10), cast(EM.EvnMse_ReExamDate as datetime), 104) as EvnMse_ReExamDate,
				MP.EvnDirection_From,
				es.EvnStatus_Name,
				es.EvnStatus_SysNick,
				convert(varchar(10), EPM.EvnPrescrMse_appointDT, 104) + ' ' + convert(varchar(5), EPM.EvnPrescrMse_appointDT, 108) as EvnPrescrMse_appointDT
			from
				EvnPrescrMse EPM (nolock)
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = ISNULL(EPM.EvnStatus_id, 27)
				left join v_EvnMse EM (nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_Signatures S with (nolock) on EM.Signatures_id = S.Signatures_id
				left join v_pmUser PUS with (nolock) on PUS.pmUser_id = S.pmUser_updID
				outer apply (
					select top 1
						l.Lpu_Nick + ISNULL(' / ' + ls.LpuSection_Name, '') + ISNULL(' / ' + MSF.Person_Fio, '') as EvnDirection_From
					from
						v_MedStaffFact MSF with (nolock)
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPM.LpuSection_sid
						inner join v_Lpu l with (nolock) on l.Lpu_id = coalesce(EPM.Lpu_gid,ls.Lpu_id,msf.Lpu_id)
					where
						MSF.MedPersonal_id = EPM.MedPersonal_sid
				) MP
				left join v_Person_all PA (nolock) on PA.Person_id = EPM.Person_id
					and PA.PersonEvn_id = EPM.PersonEvn_id
				left join v_EvnVK EVK (nolock) on EVK.EvnVK_id = EPM.EvnVK_id
				left join v_MseDirectionAimType MDAT (nolock) on MDAT.MseDirectionAimType_id = EPM.MseDirectionAimType_id
				left join v_Diag D (nolock) on D.Diag_id = EM.Diag_id
				outer apply(
					select top 1 Lpu_id from v_PersonCard (nolock) where Person_id = EPM.Person_id and LpuAttachType_id = 1
				) as PC
				left join v_Lpu LPU (nolock) on LPU.Lpu_id = PC.Lpu_id
			where 
				(1 = 1)
				{$filter}
		", $data);
		foreach($resp as $one) {
			$response[] = $one;
		}
		
		// без направления
		$resp = $this->queryResult("
			select
				null as EvnPrescrMse_id,
				'без записи' as EvnPrescrMse_setDT,
				'' as EvnPrescrMse_setDate,
				convert(varchar(10), EPM.EvnPrescrMse_issueDT, 104) as EvnPrescrMse_issueDT,
				'б/з' as EvnPrescrMse_setTime,
				'' as EvnPrescrMse_IsFirstTime,
				'' as MseDirectionAimType_Name,
				EM.Person_id,
				EM.Server_id,
				PA.Person_Fio,
				convert(varchar(10), cast(PA.Person_BirthDay as datetime), 104) as Person_BirthDay,
				null as EvnVK_id,
				null as Diag_Name,
				LPU.Lpu_Nick,
				EM.EvnMse_id,
				'№ ' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104) as EvnMse,
				case when EM.Signatures_id is not null then
					isnull(convert(varchar(10), S.Signatures_updDT, 104) + ' ', '') + 
					isnull(convert(varchar(5), S.Signatures_updDT, 108) + '. ', '') +
					isnull(PUS.PMUser_Name, '')
					else ''
				end as EvnMse_Sign,
				S.Signatures_id,
				convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104) as EvnMse_setDT,
				D.diag_FullName as DiagMse_Name,
				IGT.InvalidGroupType_Name,
				convert(varchar(10), cast(EM.EvnMse_ReExamDate as datetime), 104) as EvnMse_ReExamDate,
				null as EvnDirection_From,
				es.EvnStatus_Name,
				es.EvnStatus_SysNick,
				null as EvnPrescrMse_appointDT
			from
				v_EvnMse EM (nolock)
				left join v_Signatures S with (nolock) on EM.Signatures_id = S.Signatures_id
				left join v_pmUser PUS with (nolock) on PUS.pmUser_id = S.pmUser_updID
				left join v_EvnPrescrMse EPM (nolock) on EPM.EvnPrescrMse_id = EM.EvnPrescrMse_id
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = EPM.EvnStatus_id
				left join v_Person_all PA (nolock) on PA.Person_id = EM.Person_id
					and PA.PersonEvn_id = EM.PersonEvn_id
				left join v_Diag D (nolock) on D.Diag_id = EM.Diag_id
				outer apply(
					select top 1 Lpu_id from v_PersonCard (nolock) where Person_id = EM.Person_id and LpuAttachType_id = 1
				) as PC
				left join v_Lpu LPU (nolock) on LPU.Lpu_id = PC.Lpu_id
				left join v_InvalidGroupType IGT (nolock) on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
			where
				EM.MedService_id = :MedService_id
				and EPM.EvnPrescrMse_id is null
				{$dateFilter2}
				{$filter}
		", $data);
		foreach($resp as $one) {
			$response[] = $one;
		}
		
		return $response;
	}

	/**
	 *	Method description
	 */
	function loadEvnVKRejectGrid($data)
	{
		$filter = '';
		if (!empty($data['Person_SurName'])) {
			$data['Person_SurName'] = rtrim($data['Person_SurName']);
			$filter .= ' and PS.Person_SurName like :Person_SurName+\'%\'';
		}

		if (!empty($data['Person_FirName'])) {
			$data['Person_FirName'] = rtrim($data['Person_FirName']);
			$filter .= ' and PS.Person_FirName like :Person_FirName+\'%\'';
		}

		if (!empty($data['Person_SecName'])) {
			$data['Person_SecName'] = rtrim($data['Person_SecName']);
			$filter .= ' and PS.Person_SecName like :Person_SecName+\'%\'';
		}

		if (!empty($data['Person_BirthDay'])) {
			$filter .= ' and PS.Person_BirthDay = :Person_BirthDay';
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= ' and l.Lpu_id = :Lpu_id';
		}

		if (!empty($data['isEvnMse'])) {
			switch ($data['isEvnMse']) {
				case 1:
					$filter .= ' and EM.EvnMse_id is not null';
					break;
				case 2:
					$filter .= ' and EM.EvnMse_id is null';
					break;
			}
		}

		if ($data['onlyOwnLpu']) {
			$filter .= ' and (EVK.Lpu_id in (select LML.Lpu_bid from v_LpuMseLink LML (nolock) where LML.Lpu_id = :myLpu_id) or EVK.Lpu_id = :myLpu_id)';
		}

		$query = "
			select
				EVK.EvnVK_id,
				EVK.Person_id,
				EVK.Server_id,
				EM.EvnMse_id,
				convert(varchar(10), EVK.EvnVK_setDT, 104) as EvnVK_setDT,
				D.Diag_Code + '. ' + D.Diag_Name as Diag_Name,
				ps.Person_SurName + isnull(' ' + ps.Person_FirName,'') + isnull(' ' + ps.Person_SecName,'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				L.Lpu_Nick,
				case when EM.EvnMse_id is not null
					then '№ ' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104)
					else ''
				end as EvnMse,
				EMD.Diag_Code + '. ' + EMD.Diag_Name as DiagMse_Name,
				IGT.InvalidGroupType_Name,
				convert(varchar(10), cast(EM.EvnMse_ReExamDate as datetime), 104) as EvnMse_ReExamDate
			from
				v_EvnVK EVK with (nolock)
				left join v_EvnMse EM (nolock) on EM.EvnVK_id = EVK.EvnVK_id
				left join v_Diag D (nolock) on D.Diag_id = EVK.Diag_id
				left join v_PersonState PS (nolock) on PS.Person_id = EVK.Person_id
				left join v_Lpu l (nolock) on l.Lpu_id = PS.Lpu_id
				left join v_Diag EMD (nolock) on EMD.Diag_id = EM.Diag_id
				left join v_InvalidGroupType IGT (nolock) on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
			where
				cast(EVK.EvnVK_setDT as date) >= :begDate
				and cast(EVK.EvnVK_setDT as date) <= :endDate
				and EVK.EvnVK_IsFail = 2
				{$filter}
		";

		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getOrgAddress($data)
	{
		$query = "
			select top 1
				AD.Address_id,
				AD.Address_Zip,
				AD.KLCountry_id,
				AD.KLRGN_id,
				AD.KLSubRGN_id,
				AD.KLCity_id,
				AD.KLTown_id,
				AD.KLStreet_id,
				AD.Address_House,
				AD.Address_Corpus,
				AD.Address_Flat,
				AD.Address_Address as Address_AddressText,
				AD.Address_Address
			from
				v_Address AD with(nolock)
				left join v_Org O with(nolock) on ISNULL(O.UAddress_id, O.PAddress_id) = AD.Address_id
			where
				O.Org_id = :Org_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getPersonBodyData($data)
	{
		$query = "
			select top 1
				PW.PersonWeight_id,
				case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonWeight_Weight,
				PW.PersonWeight_IsAbnorm,
				PW.WeightAbnormType_id,
				PH.PersonHeight_id,
				PH.PersonHeight_Height,
				PH.PersonHeight_IsAbnorm,
				PH.HeightAbnormType_id
			from
				v_PersonWeight PW with(nolock)
				outer apply(
					select top 1
						PersonHeight_id,
						PersonHeight_Height,
						PersonHeight_IsAbnorm,
						HeightAbnormType_id
					from
						v_PersonHeight with(nolock)
					where
						Person_id = PW.Person_id
					order by
						" . (!empty($data['PersonHeight_id']) ? "case when PersonHeight_id = :PersonHeight_id then 1 else 2 end," : "") . "
						PersonHeight_id desc
				) as PH
			where
				PW.Person_id = :Person_id
			order by
				" . (!empty($data['PersonWeight_id']) ? "case when PW.PersonWeight_id = :PersonWeight_id then 1 else 2 end," : "") . "
				PW.PersonWeight_id desc
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getPersonJobData($data)
	{
		$query = "
			select top 1
				J.Job_id,
				J.Org_id,
				O.Org_Name,
				J.Post_id
			from
				v_PersonState PS with(nolock)
				inner join v_Job J with(nolock) on J.Job_id = PS.Job_id
				left join v_Org o with(nolock) on O.Org_id = J.Org_id
			where
				PS.Person_id = :Person_id
				and PS.Server_id = :Server_id
		";
	
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function defineActionForEvnPrescrMse($data)
	{
		if (!empty($data['EvnVK_id'])) {
			$filter = 'EPM.EvnVK_id = :EvnVK_id';
		} elseif(!empty($data['EvnPrescrMse_id'])) {
			$filter = 'EPM.EvnPrescrMse_id = :EvnPrescrMse_id';
		} else {
			return array();
		}
			
		$query = "
			select
				EPM.EvnPrescrMse_id,
				isnull(EPM.EvnStatus_id, 27) as EvnStatus_id,
				EM.EvnMse_id
			from
				v_EvnPrescrMse EPM with(nolock)
				left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
			where
				{$filter}
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Установка даты проведения
	 */
	function setMseAppointDT($data)
	{
		$query = "
			update EvnPrescrMse with (rowlock) set EvnPrescrMse_appointDT = :EvnPrescrMse_appointDT where EvnPrescrMse_id = :EvnPrescrMse_id
		";

		$this->db->query($query, array(
			'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
			'EvnPrescrMse_appointDT' => $data['EvnPrescrMse_appointDate'] . ' ' . $data['EvnPrescrMse_appointTime']
		));

		// При сохранении введенных данных статус направления на МСЭ изменяется на «Принято».
		$this->load->model('Evn_model');
		$this->Evn_model->updateEvnStatus(array(
			'Evn_id' => $data['EvnPrescrMse_id'],
			'EvnStatus_SysNick' => 'Accept',
			'EvnClass_SysNick' => 'EvnPrescrMse',
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 *	Установка службы
	 */
	function setEpmMedService($data)
	{
		$query = "
			update EvnPrescrMse with (rowlock) set MedService_id = :MedService_id where EvnPrescrMse_id = :EvnPrescrMse_id
		";

		$this->db->query($query, array(
			'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
			'MedService_id' => $data['MedService_id']
		));

		return array('Error_Msg' => '');
	}
	
	/**
	 *	Method description
	 */
	function saveEvnPrescrMse($data)
	{
		if (!empty($data['withCreateDirection'])) {
			$data['LpuUnitType_SysNick'] = 'parka';
			$data['DirType_id'] = 23;
			$data['EvnDirection_IsAuto'] = 2;
			$data['EvnDirection_IsReceive'] = 1;
			$data['Lpu_sid'] = $data['Lpu_id'];
			$data['EvnDirection_Num'] = '0';
			$data['EvnStatus_id'] = empty($data['EvnStatus_id']) ? 27 : $data['EvnStatus_id'];
			$data['EvnDirection_setDate'] = empty($data['EvnPrescrMse_setDT']) ? date('Y-m-d') : $data['EvnPrescrMse_setDT'];
			$data['RemoteConsultCause_id'] = null;
			$data['EvnDirection_pid'] = $data['EvnPrescrMse_pid'];
			$data['EvnDirection_Descr'] = null;
			$data['MedPersonal_zid'] = null;
			$data['MedStaffFact_id'] = null;
			$data['From_MedStaffFact_id'] = null;
			$data['Person_id'] = null;

			if (!empty($data['MedService_id'])) {
				// Получаем данные для направления из EvnVK по EvnVK_id
				$query = "
					declare @today DATE = dbo.tzGetDate();
					select top 1
						v_EvnVK.Person_id,
						MSMP.MedService_id as MedService_sid,
						MSMP.MedPersonal_id,
						MS.LpuSection_id as LpuSection_sid,
						MS.Lpu_id as Lpu_sid,
						MSD.Lpu_id as Lpu_did,
						MSD.LpuUnit_id as LpuUnit_did,
						MSD.LpuSection_id as LpuSection_did,
						MSF.MedStaffFact_id
					from v_EvnVK (nolock)
					inner join v_EvnVKExpert (nolock) on v_EvnVK.EvnVK_id = v_EvnVKExpert.EvnVK_id  and v_EvnVKExpert.ExpertMedStaffType_id = 1
					inner join v_MedService MS (nolock) on MS.MedService_id = v_EvnVK.MedService_id
					inner join v_MedService MSD (nolock) on MSD.MedService_id = :MedService_id
					inner join v_MedServiceMedPersonal MSMP (nolock) on MSMP.MedServiceMedPersonal_id = v_EvnVKExpert.MedServiceMedPersonal_id
					outer apply (
						select top 1 msf.MedStaffFact_id, msf.LpuSection_id
						from v_MedStaffFact msf (nolock)
						where msf.MedPersonal_id = MSMP.MedPersonal_id
							and msf.Lpu_id = MS.Lpu_id
							and cast(msf.WorkData_begDate as DATE) <= @today
							and (msf.WorkData_endDate is null OR cast(msf.WorkData_endDate as DATE) >= @today)
						order by 
							case when MS.LpuBuilding_id is not null and MS.LpuBuilding_id = msf.LpuBuilding_id then 1 else 2 end,
							case when MS.LpuUnit_id is not null and MS.LpuUnit_id = msf.LpuUnit_id then 1 else 2 end,
							case when MS.LpuSection_id is not null and MS.LpuSection_id = msf.LpuSection_id then 1 else 2 end
					) MSF
					where MSF.MedStaffFact_id is not null
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$response = $result->result('array');
					if (!is_array($response) || count($response) == 0) {
						//return array(array('Error_Code'=> 500, 'Error_Msg'=> 'Не удалось получить данные председателя ВК', ));
					} else if (empty($response[0]['MedStaffFact_id'])) {
						return array(array('Error_Code' => 500, 'Error_Msg' => 'У председателя ВК нет ни одного места работы',));
					}

					$data['Person_id'] = $response[0]['Person_id'];
					$data['MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
					$data['From_MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
					$data['MedPersonal_id'] = $response[0]['MedPersonal_id'];
					$data['LpuSection_id'] = $response[0]['LpuSection_sid'];
					//$data['Diag_id'] = $response[0]['Diag_id'];
					//$data['Lpu_id'] = $response[0]['Lpu_sid'];
					$data['Lpu_sid'] = $response[0]['Lpu_sid'];
					$data['LpuSection_did'] = $response[0]['LpuSection_did'];
					$data['Lpu_did'] = $response[0]['Lpu_did'];
					$data['LpuUnit_did'] = $response[0]['LpuUnit_did'];
				} else {
					return false;
				}
			} else if (!empty($data['EvnPrescrMse_pid'])) {
				// создание из ТАП
				$query = "
					select top 1
						epl.Person_id,
						null as MedService_sid,
						epl.MedPersonal_id,
						epl.LpuSection_id as LpuSection_sid,
						epl.Lpu_id as Lpu_sid,
						epl.Lpu_id as Lpu_did,
						ls.LpuUnit_id as LpuUnit_did,
						epl.LpuSection_id as LpuSection_did,
						epl.MedStaffFact_id
					from v_EvnVizitPL epl (nolock)
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = epl.LpuSection_id
					where epl.EvnVizitPL_id = :EvnPrescrMse_pid or epl.EvnVizitPL_pid = :EvnPrescrMse_pid
					
					union all 
					
					select top 1
						es.Person_id,
						null as MedService_sid,
						es.MedPersonal_id,
						es.LpuSection_id as LpuSection_sid,
						es.Lpu_id as Lpu_sid,
						es.Lpu_id as Lpu_did,
						ls.LpuUnit_id as LpuUnit_did,
						es.LpuSection_id as LpuSection_did,
						es.MedStaffFact_id
					from v_EvnSection es (nolock)
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					where es.EvnSection_id = :EvnPrescrMse_pid or es.EvnSection_pid = :EvnPrescrMse_pid
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$response = $result->result('array');
					if ( !is_array($response) || count($response) == 0 ) {
						return array(array('Error_Code'=> 500, 'Error_Msg'=> 'Не удалось получить данные посещения', ));
					}

					$data['Person_id'] = $response[0]['Person_id'];
					$data['MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
					$data['From_MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
					$data['MedPersonal_id'] = $response[0]['MedPersonal_id'];
					$data['LpuSection_id'] = $response[0]['LpuSection_sid'];
					$data['Lpu_sid'] = $response[0]['Lpu_sid'];
					$data['LpuSection_did'] = $response[0]['LpuSection_did'];
					$data['Lpu_did'] = $response[0]['Lpu_did'];
					$data['LpuUnit_did'] = $response[0]['LpuUnit_did'];
				} else {
					return false;
				}
			} else {
				// создание из формы журнала направлений на МСЭ
				$query = "
					select top 1
						pe.Person_id,
						null as MedService_sid,
						msf.MedPersonal_id,
						msf.LpuSection_id as LpuSection_sid,
						msf.Lpu_id as Lpu_sid,
						msf.Lpu_id as Lpu_did,
						ls.LpuUnit_id as LpuUnit_did,
						msf.LpuSection_id as LpuSection_did,
						msf.MedStaffFact_id
					from v_MedStaffFact msf (nolock)
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_PersonEvn pe (nolock) on pe.PersonEvn_id = :PersonEvn_id
					where msf.MedStaffFact_id = :MedStaffFact_id
				";
				$result = $this->db->query($query, array(
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'],
					'PersonEvn_id' => $data['PersonEvn_id']
				));
				if (is_object($result)) {
					$response = $result->result('array');
					if ( !is_array($response) || count($response) == 0 ) {
						return array(array('Error_Code'=> 500, 'Error_Msg'=> 'Не удалось получить данные рабочего места врача', ));
					}

					$data['Person_id'] = $response[0]['Person_id'];
					$data['MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
					$data['From_MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
					$data['MedPersonal_id'] = $response[0]['MedPersonal_id'];
					$data['LpuSection_id'] = $response[0]['LpuSection_sid'];
					$data['Lpu_sid'] = $response[0]['Lpu_sid'];
					$data['LpuSection_did'] = $response[0]['LpuSection_did'];
					$data['Lpu_did'] = $response[0]['Lpu_did'];
					$data['LpuUnit_did'] = $response[0]['LpuUnit_did'];
				} else {
					return false;
				}
			}
		}
		
		if (getRegionNick() == 'perm' && !empty($data['EvnVK_id']) && $data['ARMType'] != 'mse') {
			$query = "
				select top 1
					convert(varchar(10), EvnVK_setDT, 120) as EvnVK_setDT
				from v_EvnVK (nolock)
				where EvnVK_id = :EvnVK_id
			";
			$result = $this->queryResult($query, $data);
			if (count($result)) {
				if ($data['EvnPrescrMse_issueDT'] < $result[0]['EvnVK_setDT']) {
					return array(array('Error_Code'=> 500, 'Error_Msg'=> 'Дата выдачи направления на МСЭ не может быть ранее даты экспертизы ВК'));
				}
				if ($data['EvnPrescrMse_issueDT'] > date('Y-m-d')) {
					return array(array('Error_Code'=> 500, 'Error_Msg'=> 'Дата выдачи направления на МСЭ не может быть больше текущей даты'));
				}
			}
		}

		if (getRegionNick() != 'kz' && empty($data['ignoreRequiredSetOfStudiesCheck']) && (empty($data['EvnStatus_id']) || in_array($data['EvnStatus_id'], array(32,30,27))) ){
			//Определяется необходимый набор исследований 
			$requiredSetOfStudies = $this->getRequiredSetOfStudiesCheck($data);
			if(is_array($requiredSetOfStudies) && count($requiredSetOfStudies)>0){
				$str = 'Список исследований: ';
				$strArr = array();
				foreach ($requiredSetOfStudies as $value) {
					$strArr[] = $value['UslugaComplex_Name'];
				}
				$str .= implode(",", $strArr);
				return array(array('Error_Code'=> 700, 'Error_Str'=> $str));
			}
		}

		if (getRegionNick() != 'kz' && empty($data['ignorePersonDocumentCheck']) && (empty($data['EvnStatus_id']) || in_array($data['EvnStatus_id'], array(32,30,27))) ){
			$query = "SELECT TOP 1 Document_id FROM v_PersonState_all (nolock) WHERE PersonEvn_id = :PersonEvn_id";
			$Document_id = $this->getFirstResultFromQuery($query, $data);
			if(empty($Document_id)) {
				return array(array('Error_Code'=> 101, 'Error_Str' => "Для корректной отправки направления в Бюро МСЭ должны быть указаны тип и номер документа, удостоверяющего личность пациента. Внесите данные на форме \"Человек\""));
			}
		}
		
		$this->beginTransaction();

		if ( !empty($data['PostNew']) ) {
			$post_new = $data['PostNew'];

			if ( is_numeric($post_new) ) {
				$numPostID = 1;

				$sql = "
					select top 1 Post_id
					from v_Post with (nolock)
					where Post_id = :Post_id
				";
				$result = $this->db->query($sql, array('Post_id' => $post_new));
			}
			else {
				$sql = "
					select top 1 Post_id
					from v_Post with (nolock)
					where Post_Name = :Post_Name
						and Server_id = :Server_id
				";
				$result = $this->db->query($sql, array('Post_Name' => $post_new, 'Server_id' => $data['Server_id']));
			}

			if ( is_object($result) ) {
				$sel = $result->result('array');

				if ( count($sel) > 0 ) {
					if ( $sel[0]['Post_id'] > 0 ) {
						$data['Post_id'] = $sel[0]['Post_id'];
					}
				}
				else if ( !empty($numPostID) ) {
					$data['Post_id'] = null;
				}
				else {
					$sql = "
						declare @Psto_id bigint

						exec p_Post_ins
							@Post_Name = :Post_Name,
							@pmUser_id = :pmUser_id,
							@Server_id = :Server_id,
							@Post_id = @Psto_id output;

						select @Psto_id as Post_id;
					";
					$result = $this->db->query($sql, array(
						'Post_Name' => $post_new,
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $data['Server_id']
					));

					if ( is_object($result) ) {
						$sel = $result->result('array');

						if ( is_array($sel) && count($sel) > 0 && !empty($sel[0]['Post_id']) ) {
							$data['Post_id'] = $sel[0]['Post_id'];
						}
					}
				}
			}
		}

		$data['EvnPrescrMse_OrgMedDate'] = null;
		if (!empty($data['EvnPrescrMse_OrgMedDateYear'])) {
			$month = '01';
			if (!empty($data['EvnPrescrMse_OrgMedDateMonth'])) {
				$month = $data['EvnPrescrMse_OrgMedDateMonth'];
			}
			$data['EvnPrescrMse_OrgMedDate'] = $data['EvnPrescrMse_OrgMedDateYear'] . '-' . $month . '-01';
		}

		//начинаем считать вес файлов
		$filesSize = 0;

		$filesVK = array();
		if( !empty($data['filesVK']) ) {
			$files = explode("|", $data['filesVK']);

			foreach($files as $file) {
				$f = explode("::", $file);
				$filesVK[] = array(
					'name' => $f[0],
					'url' => $f[1],
					'size' => !empty($f[2]) ? $f[2] : 0
				);
				$filesSize += !empty($f[2]) ? $f[2] : 0;
			}
		}
		
		$filesMSE = array();
		if( !empty($data['filesMSE']) ) {
			$files = explode("|", $data['filesMSE']);

			foreach($files as $file) {
				$f = explode("::", $file);
				$filesMSE[] = array(
					'name' => $f[0],
					'url' => $f[1],
					'size' => !empty($f[2]) ? $f[2] : 0
				);
				$filesSize += !empty($f[2]) ? $f[2] : 0;
			}
		}
		
		//знаем вес всех файлов, проверяем, чтобы было не больше 10 МБ
		if (getRegionNick() != 'kz' && ($filesSize > 10485760)) {
			throw new Exception('Направление с прикрепленными файлами превышает допустимые 10 МБ. Удалите лишние файлы.');
		}

		$data['EvnPrescrMse_FilePath'] = json_encode(array(
			'vk' => $filesVK,
			'mse' => $filesMSE
		));
		
		$addr = $this->SaveAddress($data, 'O');
		$data['Address_oid'] = ($addr > 0) ? $addr : null;
		
		$addr = $this->SaveAddress($data, 'E');
		$data['Address_eid'] = ($addr > 0) ? $addr : null;

		if(empty($data['EvnPrescrMse_id']))
			$action = 'ins';
		else
			$action = 'upd';

		$query = "
			declare
				@Res bigint,
				@EvnPrescrMse_IsSigned bigint,
				@pmUser_signID bigint,
				@EvnPrescrMse_signDT datetime,
				@ErrCode int,
				@EvnPrescrMse_insDT datetime,
				@EvnPrescrMse_updDT datetime,
				@EvnPrescrMse_Index bigint = :EvnPrescrMse_Index,
				@EvnPrescrMse_Count bigint = :EvnPrescrMse_Count,
				@ErrMessage varchar(4000);
			set @Res = :EvnPrescrMse_id;
			
			if @Res is not null
			  	begin
			  		select
			  			@EvnPrescrMse_IsSigned = case when EvnPrescrMse_IsSigned = 2 then 1 else EvnPrescrMse_IsSigned end,
						@pmUser_signID = pmUser_signID,
						@EvnPrescrMse_signDT = EvnPrescrMse_signDT
					from
						v_EvnPrescrMse (nolock)
					where
						EvnPrescrMse_id = @Res
				end
			
			exec p_EvnPrescrMse_".$action."
				@EvnPrescrMse_id = @Res output,
				@EvnStatus_id = :EvnStatus_id,
				@TimetableMedService_id = :TimetableMedService_id,
				@MedService_id = :MedService_id,
				@EvnPrescrMse_pid = :EvnPrescrMse_pid,
				@EvnPrescrMse_rid = :EvnPrescrMse_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPrescrMse_issueDT = :EvnPrescrMse_issueDT,
				@EvnPrescrMse_setDT = :EvnPrescrMse_setDT,
				@EvnPrescrMse_disDT = :EvnPrescrMse_disDT,
				@EvnPrescrMse_didDT = :EvnPrescrMse_didDT,
				@EvnPrescrMse_insDT = @EvnPrescrMse_insDT output,
				@EvnPrescrMse_updDT = @EvnPrescrMse_updDT output,
				@EvnPrescrMse_Index = @EvnPrescrMse_Index output,
				@EvnPrescrMse_Count = @EvnPrescrMse_Count output,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@EvnPrescrMse_Descr = :EvnPrescrMse_Descr,
				@EvnPrescrMse_IsExec = :EvnPrescrMse_IsExec,
				@TimetableGraf_id = :TimetableGraf_id,
				@EvnVK_id = :EvnVK_id,
				@EvnPrescrMse_IsFirstTime = :EvnPrescrMse_IsFirstTime,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@EvnPrescrMse_InvalidPercent = :EvnPrescrMse_InvalidPercent,
				@EvnPrescrMse_IsWork = :EvnPrescrMse_IsWork,
				@Post_id = :Post_id,
				@EvnPrescrMse_ExpPost = :EvnPrescrMse_ExpPost,
				@EvnPrescrMse_Prof = :EvnPrescrMse_Prof,
				@EvnPrescrMse_ExpProf = :EvnPrescrMse_ExpProf,
				@EvnPrescrMse_Spec = :EvnPrescrMse_Spec,
				@EvnPrescrMse_ExpSpec = :EvnPrescrMse_ExpSpec,
				@EvnPrescrMse_Skill = :EvnPrescrMse_Skill,
				@EvnPrescrMse_ExpSkill = :EvnPrescrMse_ExpSkill,
				@Org_id = :Org_id,
				@EvnPrescrMse_CondWork = :EvnPrescrMse_CondWork,
				@EvnPrescrMse_MainProf = :EvnPrescrMse_MainProf,
				@EvnPrescrMse_MainProfSkill = :EvnPrescrMse_MainProfSkill,
				@Org_did = :Org_did,
				@EvnPrescrMse_Dop = :EvnPrescrMse_Dop,
				@LearnGroupType_id = :LearnGroupType_id,
				@EvnPrescrMse_ProfTraining = :EvnPrescrMse_ProfTraining,
				@EvnPrescrMse_OrgMedDate = :EvnPrescrMse_OrgMedDate,
				@EvnPrescrMse_OrgMedDateMonth = :EvnPrescrMse_OrgMedDateMonth,
				@EvnPrescrMse_DiseaseHist = :EvnPrescrMse_DiseaseHist,
				@EvnPrescrMse_LifeHist = :EvnPrescrMse_LifeHist,
				@EvnPrescrMse_MedRes = :EvnPrescrMse_MedRes,
				@EvnPrescrMse_State = :EvnPrescrMse_State,
				@EvnPrescrMse_DopRes = :EvnPrescrMse_DopRes,
				@PersonWeight_id = :PersonWeight_id,
				@PersonHeight_id = :PersonHeight_id,
				@StateNormType_id = :StateNormType_id,
				@StateNormType_did = :StateNormType_did,
				@Diag_id = :Diag_id,
				@Diag_sid = :Diag_sid,
				@Diag_aid = :Diag_aid,
				@MseDirectionAimType_id = :MseDirectionAimType_id,
				@EvnPrescrMse_AimMseOver = :EvnPrescrMse_AimMseOver,
				@ClinicalForecastType_id = :ClinicalForecastType_id,
				@ClinicalPotentialType_id = :ClinicalPotentialType_id,
				@ClinicalForecastType_did = :ClinicalForecastType_did,
				@EvnPrescrMse_Recomm = :EvnPrescrMse_Recomm,
				@EvnPrescrMse_MeasureSurgery = :EvnPrescrMse_MeasureSurgery,
				@EvnPrescrMse_MeasureProstheticsOrthotics = :EvnPrescrMse_MeasureProstheticsOrthotics,
				@EvnPrescrMse_HealthResortTreatment = :EvnPrescrMse_HealthResortTreatment,
				@PhysiqueType_id = :PhysiqueType_id,
				@EvnPrescrMse_DailyPhysicDepartures = :EvnPrescrMse_DailyPhysicDepartures,
				@EvnPrescrMse_Waist = :EvnPrescrMse_Waist,
				@EvnPrescrMse_Hips = :EvnPrescrMse_Hips,
				@EvnPrescrMse_WeightBirth = :EvnPrescrMse_WeightBirth,
				@EvnPrescrMse_PhysicalDevelopment = :EvnPrescrMse_PhysicalDevelopment,
				@MedPersonal_sid = :MedPersonal_sid,
				@LpuSection_sid = :LpuSection_sid,
				@MedPersonal_cid = :MedPersonal_cid,
				@LpuSection_cid = :LpuSection_cid,
				@EvnPrescrMse_MainDisease = :EvnPrescrMse_MainDisease,
				@Person_sid = :Person_sid,
				@EvnPrescrMse_IsCanAppear = :EvnPrescrMse_IsCanAppear,
				@Org_sid = :Org_sid,
				@Org_gid = :Org_gid,
				@Address_eid = :Address_eid,
				@Address_oid = :Address_oid,
				@EvnPrescrMse_IsPersonInhabitation = :EvnPrescrMse_IsPersonInhabitation,
				@EvnPrescrMse_IsPalliative = :EvnPrescrMse_IsPalliative,
				@EvnMse_id = :EvnMse_id,
				@EvnPrescrMse_InvalidDate = :EvnPrescrMse_InvalidDate,
				@InvalidPeriodType_id = :InvalidPeriodType_id,
				@EvnPrescrMse_InvalidEndDate = :EvnPrescrMse_InvalidEndDate,
				@EvnPrescrMse_InvalidPeriod = :EvnPrescrMse_InvalidPeriod,
				@InvalidCouseType_id = :InvalidCouseType_id,
				@EvnPrescrMse_InvalidCouseAnother = :EvnPrescrMse_InvalidCouseAnother,
				@EvnPrescrMse_InvalidCouseAnotherLaw = :EvnPrescrMse_InvalidCouseAnotherLaw,
				@ProfDisabilityPeriod_id = :ProfDisabilityPeriod_id,
				@EvnPrescrMse_ProfDisabilityEndDate = :EvnPrescrMse_ProfDisabilityEndDate,
				@EvnPrescrMse_ProfDisabilityAgainPercent = :EvnPrescrMse_ProfDisabilityAgainPercent,
				@EvnPrescrMse_FilePath = :EvnPrescrMse_FilePath,
				@Lpu_gid = :Lpu_gid,
				@DocumentAuthority_id = :DocumentAuthority_id,
				@EvnPrescrMse_DocumentSer = :EvnPrescrMse_DocumentSer,
				@EvnPrescrMse_DocumentNum = :EvnPrescrMse_DocumentNum,
				@EvnPrescrMse_DocumentIssue = :EvnPrescrMse_DocumentIssue,
				@EvnPrescrMse_DocumentDate = :EvnPrescrMse_DocumentDate,
				@MilitaryKind_id = :MilitaryKind_id,
				@EvnQueue_id = :EvnQueue_id,
				@EvnPrescrMse_IsSigned = @EvnPrescrMse_IsSigned,
				@pmUser_signID = @pmUser_signID,
				@EvnPrescrMse_signDT = @EvnPrescrMse_signDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPrescrMse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		
		//Костылина!!! Из пустого swhtmleditor приходит какбы пустой символ , который на самом деле '​', который в sql распознается как '?'
		$data['EvnPrescrMse_State'] = mb_convert_encoding( $data['EvnPrescrMse_State'], 'Windows-1251', 'UTF-8');
		$data['EvnPrescrMse_State'] = mb_convert_encoding( $data['EvnPrescrMse_State'], 'UTF-8', 'Windows-1251');
		
		$data['EvnPrescrMse_DiseaseHist'] = mb_convert_encoding( $data['EvnPrescrMse_DiseaseHist'], 'Windows-1251', 'UTF-8');
		$data['EvnPrescrMse_DiseaseHist'] = mb_convert_encoding( $data['EvnPrescrMse_DiseaseHist'], 'UTF-8', 'Windows-1251');
		
		$data['EvnPrescrMse_LifeHist'] = mb_convert_encoding( $data['EvnPrescrMse_LifeHist'], 'Windows-1251', 'UTF-8');
		$data['EvnPrescrMse_LifeHist'] = mb_convert_encoding( $data['EvnPrescrMse_LifeHist'], 'UTF-8', 'Windows-1251');
		
		$data['EvnPrescrMse_MedRes'] = mb_convert_encoding( $data['EvnPrescrMse_MedRes'], 'Windows-1251', 'UTF-8');
		$data['EvnPrescrMse_MedRes'] = mb_convert_encoding( $data['EvnPrescrMse_MedRes'], 'UTF-8', 'Windows-1251');
		
		$data['EvnPrescrMse_DopRes'] = mb_convert_encoding( $data['EvnPrescrMse_DopRes'], 'Windows-1251', 'UTF-8');
		$data['EvnPrescrMse_DopRes'] = mb_convert_encoding( $data['EvnPrescrMse_DopRes'], 'UTF-8', 'Windows-1251');
		
		/*echo getDebugSql($query, $data);
		$this->rollbackTransaction();
		die;*/
		
		// Костылина намбер ту. В АРМ МСЭ только обновить файлы
		if ($data['ARMType'] == 'mse') {
			$query = "update EvnPrescrMse with (rowlock) set EvnPrescrMse_FilePath = :EvnPrescrMse_FilePath where EvnPrescrMse_id = :EvnPrescrMse_id";
			$result = $this->db->query($query, $data);
			$response = array(array(
				'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
				'Error_Code' => null,
				'Error_Code' => null,
				'success' => true
			));
			
		} else {
			
			$result = $this->db->query($query, $data);
			
			if ( is_object($result) ) {
				$response = $result->result('array');
			} else {
				return false;
			}
			
			if (!empty($response[0]['Error_Msg']) || empty($response[0]['EvnPrescrMse_id'])) {
				$this->rollbackTransaction();
				return $response;
			}
			
			$data['EvnPrescrMse_id'] = $response[0]['EvnPrescrMse_id'];
			$this->saveEvnDirectionMSEDiag($data);
			$this->saveMeasuresRehabEffect($data);
			
		}

		$query = "
			update ES with (rowlock)
			set EvnStick_mseDT = :EvnPrescrMse_issueDT
			from
				v_EvnVizitPL EVPL
				inner join v_EvnStickBase ESB on ESB.EvnStickBase_pid = EVPL.EvnVizitPL_pid
				inner join EvnStick ES on ES.EvnStick_id = ESB.EvnStickBase_id
			where
				EVPL.EvnVizitPL_id = :EvnPrescrMse_pid
				and isnull(ESB.EvnStickBase_IsInReg, 1) = 1
				and isnull(ESB.EvnStickBase_isPaid, 1) = 1
				and ES.EvnStick_mseDT is null
				and ES.Signatures_id is null
		";
		$queryParams = array(
			'EvnPrescrMse_pid' => $data['EvnPrescrMse_pid'],
			'EvnPrescrMse_issueDT' => $data['EvnPrescrMse_issueDT']
		);
		$this->db->query($query, $queryParams);

		//добавление множественных целей
		$codes = array();
		foreach($data['Aims'] as $i) {
			$codes[] = $i;
		}

		if (!empty($codes))
			$this->saveMultipleAims($data, $codes);

		
		if (!empty($data['withCreateDirection']) && !empty($data['TimetableMedService_id'])) {
			// Записываем на бирку
			$data['Evn_id'] = $response[0]['EvnPrescrMse_id'];
			$data['object'] = 'TimetableMedService';
			$data['Post_id'] = null;
			$this->load->helper('Reg');
			$this->load->model("TimetableMedService_model");
			$resp = $this->TimetableMedService_model->Apply($data);
			if ( $resp['success'] ) {
				$response[0]['EvnDirection_id'] = $resp['EvnDirection_id'];
			} else {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		if (!empty($data['withCreateDirection']) && empty($data['TimetableMedService_id'])) {
			// Ставим в очередь
			$data['Evn_id'] = $response[0]['EvnPrescrMse_id'];
			$data['toQueue'] = 1;
			$data['LpuSectionProfile_did'] = $data['LpuSectionProfile_id'];
			$data['MedService_did'] = $data['MedService_id'];
			$data['MedPersonal_did'] = null;
			$data['Post_id'] = null;
			$this->load->model("EvnDirection_model");
			$resp = $this->EvnDirection_model->saveEvnDirection($data);
			if ( is_array($resp) && count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				$response[0]['EvnDirection_id'] = $resp[0]['EvnDirection_id'];
			} else {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		if (!empty($data['EvnPrescrMse_id']) && !empty($response[0]['EvnDirection_id'])) {
			// создаём запись в EvnLink
			$this->queryResult("
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = null;
				exec p_EvnLink_ins
					@EvnLink_id = @Res output,
					@Evn_id = :Evn_id,
					@Evn_lid = :Evn_lid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", array(
				'Evn_id' => $data['EvnPrescrMse_id'],
				'Evn_lid' => $response[0]['EvnDirection_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		if ($data['EvnPrescrMse_id'] ) {
			// сохраняем Результаты проведенных мероприятий по медицинской реабилитации
			$this->load->model('MeasuresRehab_model', 'MeasuresRehab_model');
			$this->MeasuresRehab_model->saveMeasuresForMedicalRehabilitation($data);
			// сохраняем Обследования и исследования
			$this->saveUslugaComplexMSEList($data);
		}
		$this->commitTransaction();

		if (!empty($data['EvnPrescrMse_id'])) {
			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->saveApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnPrescrMse',
				'ApprovalList_ObjectId' => $data['EvnPrescrMse_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return $response;
	}
	
	/**
	 *	Сохранение диагнозов
	 */
	function saveEvnDirectionMSEDiag($data)
	{
		
		$SopDiagList = array();
		$SopDiagArr = array();
		foreach($data['SopDiagList'] as $diag_id) {
			if (in_array($diag_id[0], $SopDiagArr)) {
				throw new Exception('Ввод одинаковых сопутствующих заболеваний не допускается', 500);
			}
			$SopDiagArr[] = $diag_id[0];
			$SopDiagList[] = $diag_id;
			
			$SopDiagOslArr = array();
			foreach($diag_id[2] as $diag_oid) {
				if (in_array($diag_oid, $SopDiagOslArr)) {
					throw new Exception('Ввод одинаковых осложнений сопутствующих заболеваний не допускается', 500);
				}
				$SopDiagOslArr[] = $diag_oid;
			}
		}
		
		$OslDiagList = array(); 
		$OslDiagArr = array(); 
		foreach($data['OslDiagList'] as $diag_id) {
			if (in_array($diag_id[0], $OslDiagArr)) {
				throw new Exception('Ввод одинаковых осложнений основного заболевания не допускается', 500);
			}
			$OslDiagArr[] = $diag_id[0];
			$OslDiagList[] = $diag_id;
		}
		
		$this->deleteEvnDirectionMSEDiag($data);
		
		foreach($data['SopDiagList'] as $diag_id) {
			$resp = $this->queryResult("
				declare
					@Res bigint,
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_EvnPrescrMseDiagLink_ins
					@EvnPrescrMseDiagLink_id = @Res output,
					@EvnPrescrMse_id = :EvnPrescrMse_id,
					@Diag_id = :Diag_id,
					@EvnPrescrMseDiagLink_DescriptDiag = :DescriptDiag,
					@Diag_oid = :Diag_oid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Res as EvnPrescrMseDiagLink_id,@Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' => $diag_id[0],
				'DescriptDiag' => $diag_id[1],
				'Diag_oid' => null
			));
			if ($resp && isset($resp[0])) {
				$EvnPrescrMseDiagLink_id = $resp[0]['EvnPrescrMseDiagLink_id'];
				foreach($diag_id[2] as $diag_oid) {
					$this->queryResult("
						declare
							@Res bigint,
							@Error_Code int,
							@Error_Msg varchar(4000);

						exec p_EvnPrescrMseDiagMkb10Link_ins
							@EvnPrescrMseDiagMkb10Link_id = @Res output,
							@EvnPrescrMseDiagLink_id = :EvnPrescrMseDiagLink_id,
							@Diag_id = :Diag_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Msg output;

						select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
					", array(
						'EvnPrescrMseDiagLink_id' => $EvnPrescrMseDiagLink_id,
						'pmUser_id' => $data['pmUser_id'],
						'Diag_id' => $diag_oid
					));
				}
			} 
		}
		
		foreach($data['OslDiagList'] as $diag_id) {
			$this->queryResult("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_EvnPrescrMseDiagLink_ins
					@EvnPrescrMseDiagLink_id = null,
					@EvnPrescrMse_id = :EvnPrescrMse_id,
					@Diag_id = :Diag_id,
					@Diag_oid = :Diag_oid,
					@EvnPrescrMseDiagLink_DescriptDiag = :DescriptDiag,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' => null,
				'DescriptDiag' => $diag_id[1],
				'Diag_oid' => $diag_id[0]
			));
		}
	}
	
	/**
	 *	Удаление диагнозов
	 */
	function deleteEvnDirectionMSEDiag($data)
	{
		$resp = $this->queryResult("
			select EvnPrescrMseDiagLink_id from EvnPrescrMseDiagLink with(nolock) where EvnPrescrMse_id = :EvnPrescrMse_id
		", $data);
		
		foreach($resp as $item) {
			$resp2 = $this->queryResult("
				select EvnPrescrMseDiagMkb10Link_id from EvnPrescrMseDiagMkb10Link with(nolock) where EvnPrescrMseDiagLink_id = ?
			", array($item['EvnPrescrMseDiagLink_id']));
			foreach($resp2 as $item2) {
				$this->queryResult("
					declare
						@Error_Code int,
						@Error_Msg varchar(4000);

					exec p_EvnPrescrMseDiagMkb10Link_del
						@EvnPrescrMseDiagMkb10Link_id = :EvnPrescrMseDiagMkb10Link_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Msg output;

					select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
				", array(
					'EvnPrescrMseDiagMkb10Link_id' => $item2['EvnPrescrMseDiagMkb10Link_id']
				));
			}
			
			$this->queryResult("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_EvnPrescrMseDiagLink_del
					@EvnPrescrMseDiagLink_id = :EvnPrescrMseDiagLink_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'EvnPrescrMseDiagLink_id' => $item['EvnPrescrMseDiagLink_id']
			));
		}
	}
	
	/**
	 *	Сохраняем реабилитацию
	 */
	function saveMeasuresRehabEffect($data)
	{
		$proc = empty($data['MeasuresRehabEffect_id']) ? 'p_MeasuresRehabEffect_ins' : 'p_MeasuresRehabEffect_upd';
		
		$data['MeasuresRehabEffect_IsRecovery'] = $data['MeasuresRehabEffect_IsRecovery'] + 1;
		$data['MeasuresRehabEffect_IsCompensation'] = $data['MeasuresRehabEffect_IsCompensation'] + 1;
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MeasuresRehabEffect_id;
			exec {$proc}
				@MeasuresRehabEffect_id = @Res output,
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@IPRARegistry_id = :IPRARegistry_id,
				@MeasuresRehabEffect_IsRecovery = :MeasuresRehabEffect_IsRecovery,
				@IPRAResult_rid = :IPRAResult_rid,
				@MeasuresRehabEffect_IsCompensation = :MeasuresRehabEffect_IsCompensation,
				@IPRAResult_cid = :IPRAResult_cid,
				@MeasuresRehabEffect_Comment = :MeasuresRehabEffect_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MeasuresRehabEffect_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Сохраняем Обследования и исследования
	 */
	function saveUslugaComplexMSEList($data)
	{
		if (is_array($data['UslugaComplexMSEData'])) {
			foreach ($data['UslugaComplexMSEData'] as $ucmdata) {
				$ucmdata = (array)$ucmdata;
				$ucmdata['EvnPrescrMse_id'] = $data['EvnPrescrMse_id'];
				$ucmdata['pmUser_id'] = $data['pmUser_id'];
				switch ($ucmdata['RecordStatus_Code']) {
					case 0:
					case 2:
						$queryResponse = $this->saveUslugaComplexMSE($ucmdata);
						break;

					case 3:
						$queryResponse = $this->deleteUslugaComplexMSE($ucmdata);
						break;
				}
			}
		}
	}
	
	/**
	 *	Сохраняем Обследования и исследования
	 */
	function saveUslugaComplexMSE($data)
	{
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnPrescrMseLink_id;
			exec p_EvnPrescrMseLink_ins
				@EvnPrescrMseLink_id = :EvnPrescrMseLink_id,
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@EvnUsluga_id = :EvnUsluga_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPrescrMseLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Удаляем Обследования и исследования
	 */
	function deleteUslugaComplexMSE($data)
	{		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPrescrMseLink_del
				@EvnPrescrMseLink_id = :EvnPrescrMseLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Method description
	 */
	function getEvnPrescrMse($data)
	{
		if (!empty($data['EvnVK_id'])) {
			$filter = 'EPM.EvnVK_id = :EvnVK_id';
		} elseif(!empty($data['EvnPrescrMse_id'])) {
			$filter = 'EPM.EvnPrescrMse_id = :EvnPrescrMse_id';
		} else {
			return array();
		}
		
		$query = "
			select top 1
				case when ES.EvnStatus_SysNick in ('New', 'Rework') then 'edit' else 'view' end as accessType,
				EPM.EvnPrescrMse_id,
				EPM.EvnPrescrMse_pid,
				convert(varchar(10), cast(EPM.EvnPrescrMse_issueDT as datetime), 104) as EvnPrescrMse_issueDT,
				convert(varchar(10), cast(EPM.EvnPrescrMse_setDT as datetime), 104) as EvnPrescrMse_setDT,
				EPM.EvnStatus_id,
				EPM.EvnPrescrMse_Descr,
				EPM.EvnPrescrMse_IsExec,
				EPM.EvnPrescrMse_IsFirstTime,
				EPM.Person_sid,
				PA.Person_Fio,
				EPM.InvalidGroupType_id,
				EPM.EvnPrescrMse_InvalidPercent,
				EPM.EvnPrescrMse_IsWork,
				EPM.Post_id,
				EPM.EvnPrescrMse_ExpPost,
				--EPM.Okved_id,
				EPM.EvnPrescrMse_Prof,
				EPM.EvnPrescrMse_ExpProf,
				EPM.EvnPrescrMse_Spec,
				EPM.EvnPrescrMse_ExpSpec,
				EPM.EvnPrescrMse_Skill,
				EPM.EvnPrescrMse_ExpSkill,
				EPM.Org_id,
				EPM.EvnPrescrMse_CondWork,
				EPM.EvnPrescrMse_MainProf,
				EPM.EvnPrescrMse_MainProfSkill,
				EPM.Org_did,
				(select rtrim(Org_Name) from v_Org with(nolock) where Org_id = EPM.Org_id) as Org_Name1,
				(select rtrim(Org_Name) from v_Org with(nolock) where Org_id = EPM.Org_did) as Org_Name2,
				(select rtrim(Org_Name) from v_Org with(nolock) where Org_id = EPM.Org_sid) as Org_NameSid,
				(select rtrim(Org_Name) from v_Org with(nolock) where Org_id = EPM.Org_gid) as Org_NameGid,
				EPM.EvnPrescrMse_Dop,
				EPM.LearnGroupType_id,
				--EPM.Okved_did,
				EPM.EvnPrescrMse_ProfTraining,
				convert(varchar(10), cast(EPM.EvnPrescrMse_OrgMedDate as datetime), 104) as EvnPrescrMse_OrgMedDate,
				YEAR(EPM.EvnPrescrMse_OrgMedDate) as EvnPrescrMse_OrgMedDateYear,
				EPM.EvnPrescrMse_OrgMedDateMonth,
				EPM.EvnPrescrMse_DiseaseHist,
				EPM.EvnPrescrMse_LifeHist,
				EPM.EvnPrescrMse_MedRes,
				EPM.EvnPrescrMse_State,
				EPM.EvnPrescrMse_DopRes,
				EPM.StateNormType_id,
				EPM.StateNormType_did,
				EPM.Diag_id,
				EPM.Diag_sid,
				EPM.Diag_aid,
				EPM.MseDirectionAimType_id,
				EPM.EvnPrescrMse_AimMseOver,
				EPM.ClinicalForecastType_id,
				EPM.ClinicalPotentialType_id,
				EPM.ClinicalForecastType_did,
				EPM.EvnPrescrMse_Recomm,
				EPM.EvnPrescrMse_MeasureSurgery,
				EPM.EvnPrescrMse_MeasureProstheticsOrthotics,
				EPM.EvnPrescrMse_HealthResortTreatment,
				EPM.PhysiqueType_id,
				EPM.EvnPrescrMse_DailyPhysicDepartures,
				EPM.EvnPrescrMse_Waist,
				EPM.EvnPrescrMse_Hips,
				EPM.EvnPrescrMse_WeightBirth,
				EPM.EvnPrescrMse_PhysicalDevelopment,
				EPM.TimetableMedService_id,
				EPM.MedService_id,
				EPM.EvnPrescrMse_MainDisease,
				EPM.EvnPrescrMse_FilePath,
				EPM.Lpu_gid,
				EPM.EvnQueue_id,
				EPM.MilitaryKind_id,
				EPM.PersonHeight_id,
				EPM.PersonWeight_id,
				MRE.MeasuresRehabEffect_id,
				IR.IPRARegistry_id,
				IR.IPRARegistry_Number,
				IR.IPRARegistry_Number,
				convert(varchar(10), IR.IPRARegistry_ProtocolDate, 104) as IPRARegistry_ProtocolDate,
                case when MRE.MeasuresRehabEffect_IsRecovery = 2 then 1 else 0 end as MeasuresRehabEffect_IsRecovery,
				MRE.IPRAResult_rid,
                case when MRE.MeasuresRehabEffect_IsCompensation = 2 then 1 else 0 end as MeasuresRehabEffect_IsCompensation,
				MRE.IPRAResult_cid,
				MRE.MeasuresRehabEffect_Comment,
				EPM.EvnPrescrMse_IsCanAppear,
				EPM.Org_sid,
				EPM.Org_gid,
				EPM.Address_oid as OAddress_id,
				AO.Address_Zip as OAddress_Zip,
				AO.KLCountry_id as OKLCountry_id,
				AO.KLRGN_id as OKLRGN_id,
				AO.KLSubRGN_id as OKLSubRGN_id,
				AO.KLCity_id as OKLCity_id,
				AO.KLTown_id as OKLTown_id,
				AO.KLStreet_id as OKLStreet_id,
				AO.Address_House as OAddress_House,
				AO.Address_Corpus as OAddress_Corpus,
				AO.Address_Flat as OAddress_Flat,
				AO.Address_Address as OAddress_Address,
				AO.Address_Address as OAddress_AddressText,
				EPM.Address_eid as EAddress_id,
				AE.Address_Zip as EAddress_Zip,
				AE.KLCountry_id as EKLCountry_id,
				AE.KLRGN_id as EKLRGN_id,
				AE.KLSubRGN_id as EKLSubRGN_id,
				AE.KLCity_id as EKLCity_id,
				AE.KLTown_id as EKLTown_id,
				AE.KLStreet_id as EKLStreet_id,
				AE.Address_House as EAddress_House,
				AE.Address_Corpus as EAddress_Corpus,
				AE.Address_Flat as EAddress_Flat,
				AE.Address_Address as EAddress_Address,
				AE.Address_Address as EAddress_AddressText,
				EPM.EvnPrescrMse_IsPersonInhabitation,
				EPM.EvnPrescrMse_IsPalliative,
				EPM.EvnMse_id,
				convert(varchar(10), cast(EPM.EvnPrescrMse_InvalidDate as datetime), 104) as EvnPrescrMse_InvalidDate,
				EPM.InvalidPeriodType_id,
				convert(varchar(10), cast(EPM.EvnPrescrMse_InvalidEndDate as datetime), 104) as EvnPrescrMse_InvalidEndDate,
				EPM.EvnPrescrMse_InvalidPeriod,
				EPM.InvalidCouseType_id,
				EPM.EvnPrescrMse_InvalidCouseAnother,
				EPM.EvnPrescrMse_InvalidCouseAnotherLaw,
				EPM.ProfDisabilityPeriod_id,
				convert(varchar(10), cast(EPM.EvnPrescrMse_ProfDisabilityEndDate as datetime), 104) as EvnPrescrMse_ProfDisabilityEndDate,
				EPM.EvnPrescrMse_ProfDisabilityAgainPercent,
				EPM.DocumentAuthority_id,
				EPM.EvnPrescrMse_DocumentSer,
				EPM.EvnPrescrMse_DocumentNum,
				EPM.EvnPrescrMse_DocumentIssue,
				convert(varchar(10), cast(EPM.EvnPrescrMse_DocumentDate as datetime), 104) as EvnPrescrMse_DocumentDate,
				EPM.EvnVK_id
			from
				v_EvnPrescrMse EPM with(nolock)
				left join v_Person_all PA with(nolock) on PA.Person_id = EPM.Person_sid
				left join v_MeasuresRehabEffect MRE with(nolock) on MRE.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_IPRARegistry IR with(nolock) on IR.IPRARegistry_id = MRE.IPRARegistry_id
				left join v_EvnStatus ES with (nolock) on ES.EvnStatus_id = EPM.EvnStatus_id
				left join v_Address AO with(nolock) on AO.Address_id = EPM.Address_oid
				left join v_Address AE with(nolock) on AE.Address_id = EPM.Address_eid
			where
				{$filter}
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {

			$result = $result->result('array');
			if(!empty($result)){
				$data['EvnPrescrMse_id'] = $result[0]['EvnPrescrMse_id'];
				$result[0]['SopDiagList'] = $this->getDiagList($data, 1);
				$result[0]['OslDiagList'] = $this->getDiagList($data, 2);
				$result[0]['EvnPrescrMse_FilePath'] = json_decode($result[0]['EvnPrescrMse_FilePath']);
			}
			return $result;
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getDiagList($data, $type)
	{
		$field = ($type == 1) ? 'Diag_id' : 'Diag_oid';
		$query = "
			select {$field} as Diag_id, ISNULL(EvnPrescrMseDiagLink_DescriptDiag,'') as DescriptDiag, EvnPrescrMseDiagLink_id
			from EvnPrescrMseDiagLink with(nolock)
			where EvnPrescrMse_id = :EvnPrescrMse_id and {$field} is not null
		";
		$result = $this->queryResult($query, $data);
		if(is_array($result))
		{
			if($type == 1) {
				foreach($result as &$res) {
					$res['OslDiag'] = $this->queryList("select Diag_id from EvnPrescrMseDiagMkb10Link (nolock) where EvnPrescrMseDiagLink_id = ?", array($res['EvnPrescrMseDiagLink_id']));
				}
			}
			return $result;
		}
		return false;
	}
	
	/**
	 *	Method description
	 */
	function defineEvnMseFormParams($data)
	{
		if( !empty($data['EvnMse_id']) ) 
			$filter = 'EvnMse_id = :EvnMse_id';
		else if ( !empty($data['EvnPrescrMse_id']) )
			$filter = 'EvnPrescrMse_id = :EvnPrescrMse_id';
		else
			$filter = '0=1';
		
		$query = "
			select
				isnull(max(cast(EvnMse_NumAct as bigint)), 0)+1 as EvnMse_NumAct,
				(select
					EvnMse_ImportedCouponGUID
				from
					v_EvnMse with(nolock)
				where
					{$filter}
				) as EvnMse_ImportedCouponGUID,
				(select
					EvnMse_id
				from
					v_EvnMse with(nolock)
				where
					{$filter}
				) as EvnMse_id
			from
				v_EvnMse with(nolock)
			where 
				isnumeric(EvnMse_NumAct) = 1
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Method description
	 */
	function getEvnMseOnEvnVK($data)
	{
		$query = "
			select
				em.EvnMse_id
			from
				v_EvnPrescrMse epm with(nolock)
				left join v_EvnMse em with(nolock) on em.EvnPrescrMse_id = epm.EvnPrescrMse_id
			where
				epm.EvnVK_id = :EvnVK_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnPrescrMseOnEvnVK($data)
	{
		$query = "
			select
				EvnPrescrMse_id,
				TimetableMedService_id
			from
				v_EvnPrescrMse with(nolock)
			where
				EvnVK_id = :EvnVK_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Method description
	 */
	function getEvnDirectionHTMOnEvnVK($data)
	{
		$query = "
			select
				EvnDirectionHTM_id,
				TimetableMedService_id
			from
				v_EvnDirectionHTM with(nolock)
			where
				EvnDirectionHTM_pid = :EvnVK_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function deleteEvnPrescrMse($data)
	{
		$EvnDirection_id = $this->getFirstResultFromQuery("
			select EvnDirection_id 
			from v_EvnDirection_all with(nolock) 
			where EvnQueue_id = (select EvnQueue_id from v_EvnPrescrMse with(nolock) where EvnPrescrMse_id = :EvnPrescrMse_id)
		");
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPrescrMse_del
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, $data);
		
		if (!empty($EvnDirection_id)) {
			$this->load->model('EvnDirection_model', 'EvnDirection_model');
			$this->EvnDirection_model->deleteEvnDirection(array(
				'EvnDirection_id' => $EvnDirection_id,
				'pmUser_id' => $data['pmUser_id']
			));

			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->deleteApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnPrescrMse',
				'ApprovalList_ObjectId' => $data['EvnPrescrMse_id']
			));
		}

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Method description
	 */
	function deleteEvnDirectionHTM($data)
	{
		if(getRegionNick() != 'kz'){
			$this->load->model('EvnDirectionHTM_model');
			$resDel = $this->EvnDirectionHTM_model->deleteEvnLink($data);
		}
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirectionHTM_del
				@EvnDirectionHTM_id = :EvnDirectionHTM_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnPLXmlData($data)
	{
		$query = "
			select top 1
				EX.EvnXml_Data
			from
				v_EvnXml EX with(nolock)
				left join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = EX.Evn_id
			where
				EVPL.EvnVizitPL_pid = :EvnPL_id
			order by	
				EVPL.EvnVizitPL_setDT desc
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnMseForPrint($data)
	{
		$query = "
			select
				LPU.Lpu_Name + 
					(case when LPU.PAddress_Address is not null then '<br />'+LPU.PAddress_Address else '' end) as Lpu,
				PA.Person_Fio,
				isnull(convert(varchar(10), EM.EvnMse_setDate, 104), '') as EvnMse_setDate,
				EM.EvnMse_NumAct,
				D1.Diag_Code as Diag_Code1, -- код осн. забол.
				D1.diag_FullName as diag_FullName1, -- осн. забол.
				substring(Diag2.Diag2_Name, 1, len(Diag2.Diag2_Name)-1) as diag_FullName2, -- сопутст.
				substring(Diag3.Diag3_Name, 1, len(Diag3.Diag3_Name)-1) as diag_FullName3, -- ослож
				HA.HealthAbnorm_Name, -- Виды нарушений функций организма
				HAD.HealthAbnormDegree_Name, -- степень их выраженности
				CLT.CategoryLifeType, -- Ограничения основных категорий жизнедеятельности
				ICT.InvalidCouseType_Name, -- причина инвалидности
				EM.EvnMse_InvalidPercent, -- степень утраты профессиональной трудоспособности в процентах
				isnull(convert(varchar(10), EM.EvnMse_ReExamDate, 104), '') as EvnMse_ReExamDate, -- дата переосвидетельствования
				EM.EvnMse_MedRecomm, -- рекомендации по медицинской реабилитации
				EM.EvnMse_ProfRecomm, -- рекомендации по профессиональной, социальной, психолого-педагогической реабилитации
				IRT.InvalidRefuseType_Name, -- Причины отказа в установлении инвалидности
				isnull(convert(varchar(10), EM.EvnMse_SendStickDate, 104), '') as EvnMse_SendStickDate, -- Дата отправки обратного талона
				MP.Person_Fio as Person_Fio2,
				IGT.InvalidGroupType_Code -- Код группы инвалидности 
			from
				v_EvnMse EM with(nolock)
				left join v_Person_all PA with(nolock) on PA.Person_id = EM.Person_id
					and PA.PersonEvn_id = EM.PersonEvn_id
				left join v_MedServiceMedPersonal MSMP with(nolock) on MSMP.MedServiceMedPersonal_id = EM.MedServiceMedPersonal_id
				outer apply (
					select top 1 Person_Fio from v_MedPersonal with(nolock) where MedPersonal_id = MSMP.MedPersonal_id
				) as MP
				left join v_Diag D1 with(nolock) on D1.Diag_id = EM.Diag_id -- осн.
				left join v_HealthAbnorm HA with(nolock) on HA.HealthAbnorm_id = EM.HealthAbnorm_id
				left join v_HealthAbnormDegree HAD on HAD.HealthAbnormDegree_id = EM.HealthAbnormDegree_id
				left join v_Lpu LPU with(nolock) on LPU.Lpu_id = EM.Lpu_id
				left join v_InvalidGroupType IGT with (nolock) on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
				left join v_InvalidCouseType ICT with (nolock) on ICT.InvalidCouseType_id = EM.InvalidCouseType_id
				left join v_InvalidRefuseType IRT with (nolock) on IRT.InvalidRefuseType_id = EM.InvalidRefuseType_id
				outer apply (
					Select (
						select d.Diag_Name + ', ' as 'data()'
						from EvnMseDiagLink dl with(nolock)
						inner join v_Diag d with(nolock) on d.Diag_id = dl.Diag_id
						where dl.EvnMse_id = EM.EvnMse_id and dl.Diag_id is not null
						for xml path('')
					) as Diag2_Name
				) as Diag2
				outer apply (
					Select (
						select d.Diag_Name + ', ' as 'data()'
						from EvnMseDiagLink dl with(nolock)
						inner join v_Diag d with(nolock) on d.Diag_id = dl.Diag_oid
						where dl.EvnMse_id = EM.EvnMse_id and dl.Diag_oid is not null
						for xml path('')
					) as Diag3_Name
				) as Diag3
				outer apply (
					Select (
						select '[br]' + clt.CategoryLifeType_Name + ', ' + cldt.CategoryLifeDegreeType_Name
						from v_EvnMseCategoryLifeTypeLink emcltl with(nolock) 
						inner join v_CategoryLifeTypeLink cltl with(nolock) on cltl.CategoryLifeTypeLink_id = emcltl.CategoryLifeTypeLink_id
						inner join v_CategoryLifeType clt with(nolock) on clt.CategoryLifeType_id = cltl.CategoryLifeType_id
						inner join v_CategoryLifeDegreeType cldt with(nolock) on cldt.CategoryLifeDegreeType_id = cltl.CategoryLifeDegreeType_id
						where emcltl.EvnMse_id = EM.EvnMse_id
						for xml path('')
					) as CategoryLifeType
				) as CLT
			where
				EM.EvnMse_id = :EvnMse_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnPrescrMseForPrint($data)
	{
		$query = "
			select
				LPU.Lpu_Name + 
					(case when LPU.PAddress_Address is not null then '<br />'+LPU.PAddress_Address else '' end) as Lpu,
				isnull(convert(varchar(10), EPM.EvnPrescrMse_setDate, 104), '') as EvnPrescrMse_setDate,
				isnull(convert(varchar(10), EPM.EvnPrescrMse_issueDT, 104), '') as EvnPrescrMse_issueDT,
				P.Person_id,
				P.Person_Fio,
				isnull(convert(varchar(10), P.Person_BirthDay, 104), '') as Person_BirthDay,
				case
					when P.Sex_id = 1 then 'М'
					when P.Sex_id = 2 then 'Ж'
					else ''
				end as PersonSex,
				P2.Person_Fio as Person_Fio2,
				PersonAddr.Address_Address,
				EPM.InvalidGroupType_id,
				EPM.EvnPrescrMse_InvalidPercent,
				EPM.EvnPrescrMse_IsFirstTime,
				-- Работа (должность, профессия, специальность, квалификация) + стаж
				(POST.Post_Name + case when EPM.EvnPrescrMse_ExpPost is not null then ' ('+cast(EPM.EvnPrescrMse_ExpPost as varchar(10))+')' else '' end) as post,
				(EPM.EvnPrescrMse_Prof + case when EPM.EvnPrescrMse_ExpProf is not null then ' ('+cast(EPM.EvnPrescrMse_ExpProf as varchar(10))+')' else '' end) as prof,
				(EPM.EvnPrescrMse_Spec + case when EPM.EvnPrescrMse_ExpSpec is not null then ' ('+cast(EPM.EvnPrescrMse_ExpSpec as varchar(10))+')' else '' end) as spec,
				(EPM.EvnPrescrMse_Skill + case when EPM.EvnPrescrMse_ExpSkill is not null then ' ('+cast(EPM.EvnPrescrMse_ExpSkill as varchar(10))+')' else '' end) as skill,
				--
				(ORG1.Org_Name + case when Org1Addr.Address_Address is not null then '<br />'+Org1Addr.Address_Address else '' end) as Org1,
				EPM.EvnPrescrMse_CondWork,
				EPM.EvnPrescrMse_MainProf,
				EPM.EvnPrescrMse_MainProfSkill,
				(ORG2.Org_Name + case when Org2Addr.Address_Address is not null then '<br />'+Org2Addr.Address_Address else '' end) as Org2,
				isnull(EPM.LearnGroupType_id, 0) as LearnGroupType_id,
				EPM.EvnPrescrMse_Dop,
				EPM.EvnPrescrMse_ProfTraining,
				isnull(convert(varchar(10), EPM.EvnPrescrMse_OrgMedDate, 104), '') as EvnPrescrMse_OrgMedDate,
				EPM.EvnPrescrMse_DiseaseHist,
				EPM.EvnPrescrMse_LifeHist,
				EPM.EvnPrescrMse_MedRes,
				EPM.EvnPrescrMse_State,
				EPM.EvnPrescrMse_DopRes,
				case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonWeight_Weight,
				cast(PH.PersonHeight_Height as float) as PersonHeight_Height,
				case when pw.Okei_id = 36 then
					round(cast((PW.PersonWeight_Weight/1000)/(PH.PersonHeight_Height*PH.PersonHeight_Height/10000) as float), 3) 
				else
					round(cast((PW.PersonWeight_Weight)/(PH.PersonHeight_Height*PH.PersonHeight_Height/10000) as float), 3) 
				end as idxWeight,
				PW.WeightAbnormType_id,
				PH.HeightAbnormType_id,
				case
					when EPM.StateNormType_id = 1 then '\"норма\"'
					when EPM.StateNormType_id = 2 then '\"отклонение\"'
				end as StateNormType_id,
				case
					when EPM.StateNormType_did = 1 then '\"норма\"'
					when EPM.StateNormType_did = 2 then '\"отклонение\"'
				end as StateNormType_did,
				D1.Diag_Code as Diag1_Code,
				ISNULL(EPM.EvnPrescrMse_MainDisease, D1.Diag_Name) as diag1_FullName,
				D2.diag_FullName as diag2_FullName,
				D3.diag_FullName as diag3_FullName,
				EPM.ClinicalForecastType_id,
				EPM.ClinicalPotentialType_id,
				EPM.ClinicalForecastType_did,
				EPM.EvnPrescrMse_AimMseOver,
				EPM.MseDirectionAimType_id,
				EPM.EvnPrescrMse_Recomm,
				EPM.EvnVK_id,
				EPM.EvnPrescrMse_MainDisease,
				substring(Diag2.Diag2_Name, 1, len(Diag2.Diag2_Name)-1) as diag2_FullName,
				substring(Diag3.Diag3_Name, 1, len(Diag3.Diag3_Name)-1) as diag3_FullName
			from
				v_EvnPrescrMse EPM
				left join v_Lpu LPU with(nolock) on LPU.Lpu_id = EPM.Lpu_id
				left join v_Person_all P with(nolock) on P.Person_id = EPM.Person_id
					and P.PersonEvn_id = EPM.PersonEvn_id
				outer apply(
					select top 1 Person_id, Person_Fio from v_Person_all with(nolock) where Person_id = EPM.Person_sid
				) as P2
				left join v_Address PersonAddr with(nolock) on PersonAddr.Address_id = P.PAddress_id
				left join v_Post POST with(nolock) on POST.Post_id = EPM.Post_id
				left join v_Org ORG1 with(nolock) on ORG1.Org_id = EPM.Org_id
				left join v_Address Org1Addr with(nolock) on Org1Addr.Address_id = ORG1.PAddress_id
				left join v_Org ORG2 with(nolock) on ORG2.Org_id = EPM.Org_did
				left join v_Address Org2Addr with(nolock) on Org2Addr.Address_id = ORG2.PAddress_id
				left join v_PersonWeight PW with(nolock) on PW.PersonWeight_id = EPM.PersonWeight_id
					and PW.Person_id = EPM.Person_id
				left join v_PersonHeight PH with(nolock) on PH.PersonHeight_id = EPM.PersonHeight_id
					and PH.Person_id = EPM.Person_id
				left join v_Diag D1 with(nolock) on D1.Diag_id = EPM.Diag_id
				left join v_Diag D2 with(nolock) on D2.Diag_id = EPM.Diag_sid
				left join v_Diag D3 with(nolock) on D3.Diag_id = EPM.Diag_aid
				outer apply (
					Select (
						select d.Diag_Name + ', '  as 'data()'
						from EvnPrescrMseDiagLink dl with(nolock)
						inner join v_Diag d with(nolock) on d.Diag_id = dl.Diag_id
						where dl.EvnPrescrMse_id = EPM.EvnPrescrMse_id and dl.Diag_id is not null
						for xml path('')
					) as Diag2_Name
				) as Diag2
				outer apply (
					Select (
						select d.Diag_Name + ', ' as 'data()'
						from EvnPrescrMseDiagLink dl with(nolock)
						inner join v_Diag d with(nolock) on d.Diag_id = dl.Diag_oid
						where dl.EvnPrescrMse_id = EPM.EvnPrescrMse_id and dl.Diag_oid is not null
						for xml path('')
					) as Diag3_Name
				) as Diag3
			where
				EPM.EvnPrescrMse_id = :EvnPrescrMse_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			$aims = $this->loadMultiplePrescrAims($data);
			$codes = [];
			foreach($aims as $aim)
				$codes[] = $aim['Code'];
			$query = "
				select 
					MseDirectionAimType_Code as Code,
					MseDirectionAimType_Name as Name
				from v_MseDirectionAimType
				order by Code
				";
			$allAims = $this->db->query($query);
			$allAims = $allAims->result('array');
			$res = [];
			foreach ($allAims as $aim) {
				if (in_array($aim['Code'], $codes))
					$res[] = '<u>' . $aim['Name'] . '</u>';
				else $res[] = $aim['Name'];
			}
			$result[0]['MseDirectionAimType_id'] = implode(', ', $res);
			$result[0]['MseDirectionAimType_id'] .= ' (указать): ';

			if(getRegionNick() == 'perm')
			{
				$result[0]['diag2_FullName'] = '';
				$query_get_oslDiag = "
					select 
						(d.Diag_Code + ' ' + d.Diag_Name) as Diag_Name, 
						ISNULL(dl.EvnPrescrMseDiagLink_DescriptDiag,'') as DescriptDiag
					from EvnPrescrMseDiagLink dl with(nolock)
					inner join v_Diag d with(nolock) on d.Diag_id = dl.Diag_id
					where 
						dl.EvnPrescrMse_id = :EvnPrescrMse_id 
					and 
						dl.Diag_id is not null
				";
				$result_get_oslDiag = $this->db->query($query_get_oslDiag, $data);
				if(is_object($result_get_oslDiag))
				{
					$result_get_oslDiag = $result_get_oslDiag->result('array');
					if(count($result_get_oslDiag) > 0)
					{
						for($i=0; $i<count($result_get_oslDiag); $i++)
						{
							$result[0]['diag2_FullName'] .= $result_get_oslDiag[$i]['Diag_Name'] . ' (' . $result_get_oslDiag[$i]['DescriptDiag'] . ')<br>';
						}
					}
				}

				$result[0]['MeasuresRehabMSE'] = $this->queryResult("
					select
						MeasuresRehabMSE_Name,
						MeasuresRehabMSE_Result,
						convert(varchar(10), MeasuresRehabMSE_BegDate, 104) as MeasuresRehabMSE_BegDate,
						convert(varchar(10), MeasuresRehabMSE_EndDate, 104) as MeasuresRehabMSE_EndDate
					from
						v_MeasuresRehabMSE mrmse (nolock)
					where
						mrmse.EvnPrescrMse_id = :EvnPrescrMse_id
				", $data);
			}

			return $result;
			//return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function deleteEvnPrescrVK($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPrescrVK_del
				@EvnPrescrVK_id = :EvnPrescrVK_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function clearTimeMSOnEvnPrescrVK($data)
	{
		if(empty($data['EvnPrescrVK_id'])) {
			$query = "
				select
					EvnPrescrVK_id
				from
					v_EvnPrescrVK with(nolock)
				where
					TimetableMedService_id = :TimetableMedService_id
			";
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			if(count($result) == 0)
				return false;
			
			$data['EvnPrescrVK_id'] = $result[0]['EvnPrescrVK_id'];
		}
		
		return $this->deleteEvnPrescrVK($data);
	}
	
	/**
	 *	Method description
	 */
	function clearTimeMSOnEvnPrescrMse($data)
	{
		if(empty($data['EvnPrescrMse_id'])) {
			$query = "
				select
					EvnPrescrMse_id
				from
					v_EvnPrescrMse with(nolock)
				where
					TimetableMedService_id = :TimetableMedService_id
			";
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			if(count($result) == 0)
				return false;
			
			$data['EvnPrescrMse_id'] = $result[0]['EvnPrescrMse_id'];
		}
		
		return $this->deleteEvnPrescrMse($data);
	}
	
	/**
	 *	Method description
	 */
	function getDeputyKind($data)
	{
		$query = "
			select top 1
				PA.Person_id,
				PA.Person_Fio,
				PD.PersonDeputy_id,
				PD.DeputyKind_id
			from
				v_PersonDeputy PD with (nolock)
				left join v_Person_all PA with(nolock) on PA.Person_id = PD.Person_pid
			where
				PD.Person_id = :Person_id
			order by PA.PersonEvn_insDT desc
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *	Метод получения данных о представителе для Карелии
	 */
	function getDeputyKindKareliya($data)
	{
		$query = "
			select top 1
				PA.Person_id as Person_id,
				PA.Person_Fio as Person_Fio,
				PD.PersonDeputy_id as PersonDeputy_id,
				PD.DeputyKind_id as DeputyKind_id,
				DK.DeputyKind_Name as DeputyKind_Name,
				PA.Sex_id as Sex_id,	
				PS.PersonPhone_Phone as PersonPhone_Phone,
				PS.Document_Num as Document_Num,
				PS.Document_Ser as Document_Ser,
				O.Org_Name as Org_Name,
				convert(char, Doc.Document_begDate, 104) as Document_begDate
			from
				v_PersonDeputy PD with (nolock)
				left join v_Person_all PA with(nolock) on PA.Person_id = PD.Person_pid
				left join DeputyKind DK with(nolock) on PD.DeputyKind_id = DK.DeputyKind_id
				left join PersonState PS with(nolock) on PD.Person_pid = PS.Person_id
				left join Document Doc with(nolock) on PS.Document_id = Doc.Document_id
				left join OrgDep OD with(nolock) on Doc.OrgDep_id = OD.OrgDep_id
				left join Org O with(nolock) on OD.Org_id = O.Org_id
			where
				PD.Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *	Method description
	 */
	function saveDeputyKind($data)
	{
		$sdata = $this->getDeputyKind($data);
		if(!is_array($sdata)) return false;
		
		if(count($sdata) > 0) {
			$action = 'upd';
			$data['PersonDeputy_id'] = $sdata[0]['PersonDeputy_id'];
		} else {
			$action = 'ins';			
		}
		
		if( empty($data['DeputyKind_id']) ) {
			$data['DeputyKind_id'] = 1;
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonDeputy_id;
			exec p_PersonDeputy_{$action}
				@PersonDeputy_id = :PersonDeputy_id,
				@DeputyKind_id = :DeputyKind_id,
				@PersonDeputy_begDT = null,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@Person_pid = :Person_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonDeputy_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function deleteDeputyKind($data)
	{
		$sdata = $this->getDeputyKind($data);
		if(!is_array($sdata)) return false;
		
		//print_r($sdata); exit();
		if(count($sdata) == 0) {
			return false;
		}
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonDeputy_del
				@PersonDeputy_id = :PersonDeputy_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, $sdata[0]);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function loadEvnPrescrJournalGrid($data)
	{
		$obj = strtoupper($data['ARMType']);
		$filter = '1=1';
		$select = '';
		$join = '';
		
		//$filter .= ' and EPO.MedPersonal_cid = :MedPersonal_id';
		
		if( !empty($data['Person_SurName']) ) {
			$filter .= ' and PA.Person_SurName like :Person_SurName + \'%\'';
		}
		
		if( !empty($data['Person_FirName']) ) {
			$filter .= ' and PA.Person_FirName like :Person_FirName + \'%\'';
		}
		
		if( !empty($data['Person_SecName']) ) {
			$filter .= ' and PA.Person_SecName like :Person_SecName + \'%\'';
		}
		
		if( !empty($data['Person_BirthDay']) ) {
			$filter .= ' and PA.Person_BirthDay = :Person_BirthDay';
		}
		
		if( !empty($data['isEvn'.$obj]) && $data['isEvn'.$obj] != 3 ) {
			$filter .= ' and EO.Evn'.$obj.'_id is '.( $data['isEvn'.$obj] == 1 ? 'not ' : '' ).'null';
		}
		
		switch($obj) {
			case 'VK':
				$numField = 'EvnVK_NumProtocol';
				$select .= ',CTT.CauseTreatmentType_Name as CauseDirection';
				$join .= 'left join v_CauseTreatmentType CTT with(nolock) on CTT.CauseTreatmentType_id = EPO.CauseTreatmentType_id';
				break;
			
			case 'MSE':
				$numField = 'EvnMse_NumAct';
				$select .= ',MDAT.MseDirectionAimType_Name as CauseDirection';
				$join .= 'left join v_MseDirectionAimType MDAT with(nolock) on MDAT.MseDirectionAimType_id = EPO.MseDirectionAimType_id';
				break;
		}
		
		$query = "
			select
				EPO.EvnPrescr{$obj}_id as EvnPrescrObj_id,
				convert(varchar(10), cast(EvnPrescr{$obj}_insDT as datetime), 104) as EvnPrescrObj_setDate,
				isnull(convert(varchar(10), cast(TTMS.TimetableMedService_begTime as datetime), 104),'-') as EvnPrescrObj_begDate,
				isnull(convert(varchar(5), cast(TTMS.TimetableMedService_begTime as datetime), 108),'-') as EvnPrescrObj_begTime,
				EO.Evn{$obj}_id as EvnObj_id,
				case when EO.Evn{$obj}_id is not null
					then '№' + cast(EO.{$numField} as varchar) + ' от ' + convert(varchar(10), cast(EO.Evn{$obj}_setDate as datetime), 104)
					else '-'
				end as EvnObj,
				PA.Person_id,
				PA.Person_Fio,
				convert(varchar(10), cast(PA.Person_BirthDay as datetime), 104) as Person_BirthDay,
				EPO.Diag_id,
				D.diag_FullName as Diag_Name,
				MS.MedService_Name
				{$select}
			from
				v_EvnPrescr{$obj} EPO with(nolock)
				left join v_Evn{$obj} EO (nolock) on EO.EvnPrescr{$obj}_id = EPO.EvnPrescr{$obj}_id
				left join v_Person_all PA (nolock) on PA.Person_id = EPO.Person_id
					and PA.PersonEvn_id = EPO.PersonEvn_id
				left join v_Diag D (nolock) on D.Diag_id = EPO.Diag_id
				left join v_TimetableMedService_lite TTMS (nolock) on TTMS.TimetableMedService_id = EPO.TimetableMedService_id
				left join v_MedService MS (nolock) on MS.MedService_id = EPO.MedService_id
				{$join}
			where
				{$filter}
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			$res = array(
				'data' => $result,
				'totalCount' => count($result)
			);
			return $res;
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function cancelEvnPrescr($data)
	{
		$query = "
			
		";
	}
	
	/**
	 *	Method description
	 */
	function cancelEvnPrescrbyRecord($data)
	{		
		if (isset($data['TimetableMedService_id']) && !empty($data['TimetableMedService_id'])) {
			// если отменяется направление на МСЭ, то надо удалить лист согласования
			$resp_epm = $this->queryResult("
				select top 1
					EvnPrescrMse_id
				from
					v_EvnPrescrMse (nolock)
				where
					TimeTableMedService_id = :TimetableMedService_id
			", array(
				'TimetableMedService_id' => $data['TimetableMedService_id']
			));
			if (!empty($resp_epm[0]['EvnPrescrMse_id'])) {
				$this->load->model('ApprovalList_model');
				$this->ApprovalList_model->deleteApprovalList(array(
					'ApprovalList_ObjectName' => 'EvnPrescrMse',
					'ApprovalList_ObjectId' => $resp_epm[0]['EvnPrescrMse_id']
				));
			}

			$res = $this->db->query(" 
				select
					EvnPrescrVK_id
				from v_EvnPrescrVK with (nolock)
				where
					TimetableMedService_id = :TimetableMedService_id
				", 
				array(
					'TimetableMedService_id' => $data['TimetableMedService_id']
				)
			);
		}
		else if (isset($data['EvnQueue_id']) && !empty($data['EvnQueue_id'])) {
			$resp_epm = $this->queryResult("
				select top 1
					EvnPrescrMse_id
				from
					v_EvnPrescrMse (nolock)
				where
					EvnQueue_id = :EvnQueue_id
			", array(
				'EvnQueue_id' => $data['EvnQueue_id']
			));
			if (!empty($resp_epm[0]['EvnPrescrMse_id'])) {
				$this->load->model('ApprovalList_model');
				$this->ApprovalList_model->deleteApprovalList(array(
					'ApprovalList_ObjectName' => 'EvnPrescrMse',
					'ApprovalList_ObjectId' => $resp_epm[0]['EvnPrescrMse_id']
				));
			}

			$res = $this->db->query("
				select
					epvk.EvnPrescrVK_id
				from v_EvnQueue eq with (nolock)
				inner join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = eq.EvnDirection_id
				inner join v_EvnPrescrVK epvk with (nolock) on 
					epvk.EvnPrescrVK_rid = ed.EvnDirection_rid and 
					ed.MedService_id = epvk.MedService_id and
					cast(epvk.EvnPrescrVK_insDT as DATE) = cast(ed.EvnDirection_insDT as DATE)
				where
					eq.EvnQueue_id = :EvnQueue_id
				", 
				array(
					'EvnQueue_id' => $data['EvnQueue_id']
				)
			);
		}
		else {
			return false;
		}
		
		if (is_object($res)) {
			$res = $res->result('array');
			if (isset($res[0])) {
				$evnprescrvk_id = $res[0]['EvnPrescrVK_id'];
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}

		$queryDel = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnPrescrVK_del
				@EvnPrescrVK_id = :EvnPrescr_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select :EvnPrescr_id as EvnPrescrVK_id, :EvnPrescr_id as EvnPrescr_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$queryParams = array(
			'EvnPrescr_id' => $evnprescrvk_id,
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($queryDel, $queryParams);
		return false;
	}
	
	/**
	 *	Поиск всех врачей, которые должны получить уведомление 
	 */
	function getMedPersonalForNotice($data)
	{
		$medpersons = array();
		$queryParams = array(
			$data['object'].'_id' => $data['object_id']
		);
		$queryParams['EvnClass'] = $data['object'];
		
		switch ( $data['object'] ) {
		
			//Направление на ВК (уведомление получают все врачи, указанные на службе ВК)
			case 'EvnPrescrVK':
				$query = "
					select
						MSMP.MedPersonal_id
					from
						v_MedServiceMedPersonal MSMP with(nolock)
						left join v_EvnPrescrVK EPVK with(nolock) on EPVK.MedService_id = MSMP.MedService_id
					where
						 EPVK.EvnPrescrVK_id = :EvnPrescrVK_id
				";
				
				$sql = "
					select Person_id from v_EvnPrescrVK EPVK with(nolock) where EPVK.EvnPrescrVK_id = :EvnPrescrVK_id and EPVK.CauseTreatmentType_id=5
				";
				$Person_id = $this->getFirstResultFromQuery($sql, $queryParams);
				break;
			
			//Протокол ВК (уведомление получает врач, выписавший направление на ВК)
			case 'EvnVK':
				$query = "
					select
						EPVK.MedPersonal_sid as MedPersonal_id
					from
						v_EvnPrescrVK EPVK with(nolock)
						left join v_EvnVK EVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
					where
						EVK.EvnVK_id = :EvnVK_id
				";
				
				$sql = "
					select Person_id from v_EvnVK EVK with(nolock) where EVK.EvnVK_id = :EvnVK_id and EVK.CauseTreatmentType_id=5
				";
				$Person_id = $this->getFirstResultFromQuery($sql, $queryParams);
				$queryParams['EvnClass'] = 'EvnPrescrVK';
				break;
			
			//Направление на МСЭ (уведомление получают все врачи, указанные на службе МСЭ)
			case 'EvnPrescrMse':
				$query = "
					select
						MSMP.MedPersonal_id
					from
						v_MedServiceMedPersonal MSMP with(nolock)
						left join v_EvnPrescrMse EPM with(nolock) on EPM.MedService_id = MSMP.MedService_id
					where
						 EPM.EvnPrescrMse_id = :EvnPrescrMse_id
				";
				
				$sql = "
					select Person_id from v_EvnPrescrMse EPM with(nolock) where EPM.EvnPrescrMse_id = :EvnPrescrMse_id
				";
				$Person_id = $this->getFirstResultFromQuery($sql, $queryParams);
				break;
			
			//Протокол МСЭ (уведомление получает врач, выписавший направление на ВК и врач ВК, выписавший направление на МСЭ)
			case 'EvnMse':
				$query = "
					select top 1
						EPM.MedPersonal_sid as MedPersonal_id
					from
						v_EvnPrescrMse EPM with(nolock)
						left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id	
					where
						 EM.EvnMse_id = :EvnMse_id
					union
					select top 1
						EPVK.MedPersonal_sid as MedPersonal_id
					from
						v_EvnPrescrVK EPVK with(nolock)
						left join v_EvnVK EVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
						left join v_EvnPrescrMse EPM with(nolock) on EPM.EvnVK_id = EVK.EvnVK_id
						left join v_EvnMse EM with(nolock) on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
					where
						EM.EvnMse_id = :EvnMse_id
				";
				
				$sql = "
					select Person_id from v_EvnMse EM with(nolock) where EM.EvnMse_id = :EvnMse_id
				";
				$Person_id = $this->getFirstResultFromQuery($sql, $queryParams);
				
				$queryParams['EvnClass'] = 'EvnPrescrMse';
				break;
		}
		
		if(!empty($Person_id)) {
			$queryParams['Person_id'] = $Person_id;
			
			$query .= " union
				select distinct MSF.MedPersonal_id
				from
					v_MedStaffFact MSF with(nolock)
					inner join v_pmUserCache UC with(nolock) on UC.MedPersonal_id = MSF.MedPersonal_id
					inner join v_MedStaffRegion MSR with(nolock) on MSR.MedStaffFact_id = MSF.MedStaffFact_id
					inner join v_PersonCardState PCS with(nolock) on PCS.LpuRegion_id = MSR.LpuRegion_id
					left join v_EvnVizitPL EVPL with(nolock) on EVPL.MedPersonal_id = MSF.MedPersonal_id and EVPL.Person_id = :Person_id
					left join v_EvnPL EPL with(nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
				where (
					UC.pmUser_IsMessage = 1
						or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
						or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct = 1 and UC.PMUser_Phone is not null)
					)
					and ISNULL(UC.pmUser_deleted,1) = 1
					and not exists(
						select top 1 t.PersonNotice_id from v_PersonNotice t with(nolock)
						where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
						and t.PersonNotice_IsSend = 1
					) and UC.pmUser_EvnClass LIKE '%'+:EvnClass+'%'
					and 
					UC.pmUser_PolkaGroupType=1 and PCS.Person_id = :Person_id
				union 
				select distinct EVPL.MedPersonal_id
				from
					v_EvnVizitPL EVPL with(nolock)
					inner join v_EvnPL EPL with(nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
					inner join v_pmUserCache UC with(nolock) on UC.MedPersonal_id = EVPL.MedPersonal_id
				where (
					UC.pmUser_IsMessage = 1
						or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
						or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct = 1 and UC.PMUser_Phone is not null)
					)
					and ISNULL(UC.pmUser_deleted,1) = 1
					and not exists(
						select top 1 t.PersonNotice_id from v_PersonNotice t with(nolock)
						where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
						and t.PersonNotice_IsSend = 1
					) and UC.pmUser_EvnClass LIKE '%'+:EvnClass+'%'
					and ISNULL(UC.pmUser_PolkaGroupType,2)=2
					and EPL.EvnPL_IsFinish != 2
					and EVPL.Person_id = :Person_id
			";
			
		}
		//~ echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		
		foreach( $result as $r ) 
			$medpersons[] = $r['MedPersonal_id'];
		
		return $medpersons;
	}
	
	/**
	 *	Поиск данных для уведомления в направлении на ВК / протоколе ВК / Направлении на МСЭ / Протоколе МСЭ (в зависимости от параметра $data['object'])
	 */
	function getDataForNotice($data)
	{
		$select = '';
		$join = '';
		$OBJ = $data['object'];
		if($OBJ == 'EvnPrescrMse') {
			$select .= 'OBJ.EvnVK_id,';
			
			$join .= " left join EvnStatus ES with (nolock) on ES.EvnStatus_id = OBJ.EvnStatus_id";
			$select .= 'ES.EvnStatus_Name,';
		}
		if($OBJ == 'EvnMse') {
			$select .= "EPVK.Lpu_id as VkLpu_id,";
			$join .= " left join v_EvnPrescrMse EPM with(nolock) on OBJ.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_EvnVK EVK with(nolock) on EPM.EvnVK_id = EVK.EvnVK_id
				left join v_EvnPrescrVK EPVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id";
		}
		if($OBJ == 'EvnVK') {
			$select .= 'OBJ.EvnVK_DecisionVK,';
		}
		$query = "
			select top 1
				OBJ.{$OBJ}_id,
				{$select}
				OBJ.Person_id,
				OBJ.Server_id,
				PA.Person_Fio,
				isnull(convert(varchar(10), PA.Person_BirthDay, 104), '') as Person_BirthDay,
				MS.MedService_Name
			from
				v_Person_all PA with (nolock)
				left join v_{$OBJ} OBJ with (nolock) on OBJ.Person_id = PA.Person_id
					and OBJ.PersonEvn_id = PA.PersonEvn_id
				left join v_MedService MS with (nolock) on MS.MedService_id = OBJ.MedService_id
				{$join}
			where
				OBJ.{$OBJ}_id = :obj_id
		";
		$queryParams = array(
			'obj_id' => $data['object_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return $result[0];
	}

	/**
	 *	Method description
	 */
	function deleteEvnMse($data)
	{
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnMse_del
				@EvnMse_id = :EvnMse_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$evnprescrmse_id = $this->getFirstResultFromQuery("select EvnPrescrMse_id from EvnMse with (nolock) where EvnMse_id = :EvnMse_id", $data);
			if(!empty($evnprescrmse_id)) {
				$this->load->model('Evn_model', 'Evn_model');
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $evnprescrmse_id,
					'EvnStatus_id' => 29,
					'EvnClass_SysNick' => 'EvnPrescrMse',
					'pmUser_id' => $data['pmUser_id']
				));
			}
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnPrescrMseStatusHistory($data)
	{
		$query = "
			select top 1
				esh.EvnStatusHistory_id,
				convert(varchar(10), esh.EvnStatusHistory_begDate, 104) + ' ' + convert(varchar(5), esh.EvnStatusHistory_begDate, 108) as EvnStatusHistory_begDate,
				esh.EvnStatusHistory_Cause,
				MS.MedService_id,
				MS.MedService_Name
			from v_EvnStatusHistory esh with(nolock)
			left join v_EvnStatus es WITH (NOLOCK) on es.EvnStatus_id = esh.EvnStatus_id
			left join v_MedServiceMedPersonal MSMP with (nolock) on MSMP.MedServiceMedPersonal_id = esh.MedServiceMedPersonal_id
			left join v_MedService MS with (nolock) on MS.MedService_id = MSMP.MedService_id
			where 
				esh.Evn_id = :EvnPrescrMse_id 
				--EvnStatus_id = 30 and
				and es.EvnStatus_SysNick IN ('Rework','RefusalVK','RefusalDir','ReworkMSE')
				and esh.EvnStatusHistory_Cause is not null
			ORDER BY esh.EvnStatusHistory_begDate DESC
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Method description
	 */
	function setEvnVKIsFail($data) {
		
		$query = "update EvnVK with (rowlock) set EvnVK_IsFail = :EvnVK_IsFail where EvnVK_id = :EvnVK_id";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function loadCategoryLifeTypeLinkList($data) {
		
		$filter = '(1 = 1) ';
		if(!empty($data['CategoryLifeType_id'])) {
			$filter .= ' and CategoryLifeType_id = :CategoryLifeType_id ';
		}
		
		$query = "
			select cltl.CategoryLifeTypeLink_id, cldt.CategoryLifeDegreeType_Name, cltl.CategoryLifeTypeLink_Name
			from v_CategoryLifeTypeLink cltl with(nolock) 
			inner join v_CategoryLifeDegreeType cldt with(nolock) on cltl.CategoryLifeDegreeType_id = cldt.CategoryLifeDegreeType_id
			where {$filter}";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Method description
	 */
	function loadEvnMseCategoryLifeTypeLink($data) {
		
		$query = "
			select 
				emcltl.EvnMseCategoryLifeTypeLink_id,
				emcltl.EvnMse_id,
				emcltl.CategoryLifeTypeLink_id,
				cltl.CategoryLifeType_id,
				clt.CategoryLifeType_Name,
				cldt.CategoryLifeDegreeType_Name, 
				cltl.CategoryLifeTypeLink_Name
			from v_EvnMseCategoryLifeTypeLink emcltl with(nolock) 
			inner join v_CategoryLifeTypeLink cltl with(nolock) on cltl.CategoryLifeTypeLink_id = emcltl.CategoryLifeTypeLink_id
			inner join v_CategoryLifeType clt with(nolock) on clt.CategoryLifeType_id = cltl.CategoryLifeType_id
			inner join v_CategoryLifeDegreeType cldt with(nolock) on cldt.CategoryLifeDegreeType_id = cltl.CategoryLifeDegreeType_id
			where EvnMse_id = :EvnMse_id";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Method description
	 */
	function saveEvnMseCategoryLifeType($data) {
		
		if(!empty($data['EvnMseCategoryLifeTypeLink_id'])) {
			$action = 'upd';
			$checkfilter = ' and EvnMseCategoryLifeTypeLink_id != :EvnMseCategoryLifeTypeLink_id ';
		} else {
			$action = 'ins';
			$checkfilter = '';
		}
		
		$query = "
			select emcltl.EvnMseCategoryLifeTypeLink_id 
			from v_EvnMseCategoryLifeTypeLink emcltl with(nolock) 
			inner join v_CategoryLifeTypeLink cltl with(nolock) on cltl.CategoryLifeTypeLink_id = emcltl.CategoryLifeTypeLink_id
			where 
				emcltl.EvnMse_id = :EvnMse_id and 
				cltl.CategoryLifeType_id = :CategoryLifeType_id 
				{$checkfilter}
		";
		
		$check = $this->queryResult($query, $data);
		if (count($check)) {
			throw new Exception("Ограничение с такой категорией жизнедеятельности уже существует. Выберите другую категорию жизнедеятельности", 500);
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMseCategoryLifeTypeLink_id;
			exec p_EvnMseCategoryLifeTypeLink_{$action}
				@EvnMseCategoryLifeTypeLink_id = @Res output,
				@EvnMse_id = :EvnMse_id,
				@CategoryLifeTypeLink_id = :CategoryLifeTypeLink_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnMseCategoryLifeTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	public function searchEvnPrescrMse(array $data) {
		$filterList = [
			'EVK.EvnVK_id is null',
			'EPM.Lpu_id = :Lpu_id',
		];
		$queryParams = [
			'Lpu_id' => $data['Lpu_id'],
		];
		
		if (!empty($data['Person_SurName'])) {
			$filterList[] = 'PA.Person_SurName like :Person_SurName+\'%\'';
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}
		
		if (!empty($data['Person_FirName'])) {
			$filterList[] = 'PA.Person_FirName like :Person_FirName+\'%\'';
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}
		
		if (!empty($data['Person_SecName'])) {
			$filterList[] = 'PA.Person_SecName like :Person_SecName+\'%\'';
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}
		
		if (!empty($data['Person_BirthDay'])) {
			$filterList[] = 'PA.Person_BirthDay = :Person_BirthDay';
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		
		if (!empty($data['EvnPrescrVK_Status'])) {
			switch ($data['EvnPrescrVK_Status']) {
				case 1:
					$filterList[] = 'EPVK.EvnPrescrVK_id is not null';
					break;

				case 2:
					$filterList[] = 'EPVK.EvnPrescrVK_id is null';
				break;
			}
		}
		
		if (!empty($data['Diag_id'])) {
			$filterList[] = 'EPM.Diag_id = :Diag_id';
			$queryParams['Diag_id'] = $data['Diag_id'];
		}
		
		if (!empty($data['EvnPrescrMse_issueDT'][0])) {
			$filterList[] = 'EPM.EvnPrescrMse_issueDT >= :EvnPrescrMse_issueDT_beg';
			$queryParams['EvnPrescrMse_issueDT_beg'] = $data['EvnPrescrMse_issueDT'][0];
		}
		
		if (!empty($data['EvnPrescrMse_issueDT'][1])) {
			$filterList[] = 'EPM.EvnPrescrMse_issueDT <= :EvnPrescrMse_issueDT_end';
			$queryParams['EvnPrescrMse_issueDT_end'] = $data['EvnPrescrMse_issueDT'][1];
		}
			
		if (!empty($data['EvnStatus_id'])) {
			$filterList[] = 'EPM.EvnStatus_id = :EvnStatus_id';
			$queryParams['EvnStatus_id'] = $data['EvnStatus_id'];
		}
		
		if (!empty($data['EvnDirection_Num'])) {
			$filterList[] = 'ED.EvnDirection_Num = :EvnDirection_Num';
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}
		
		$query = "
			-- Журнал направлений на МСЭ (MS)

			select
				-- select
				EPM.EvnPrescrMse_id,
				case
					when
						ES.EvnStatus_SysNick in ('New', 'Rework')
						and ISNULL(EPM.EvnPrescrMse_IsSigned, 1) = 1
				then 'edit' else 'view' end as signAccess,
				EPM.MedPersonal_sid,
				EPM.EvnStatus_id,
				EPVK.EvnPrescrVK_id,
				ED.EvnDirection_Num,
				PA.Person_id,
				PA.Server_id,
				isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104), '') as EvnPrescrVK_setDate,
				convert(varchar(10), EPM.EvnPrescrMse_issueDT, 104) as EvnPrescrMse_issueDT,
				es.EvnStatus_Name,
				case
					when EPM.EvnPrescrMse_IsFirstTime = 1 then 'Первично'
					when EPM.EvnPrescrMse_IsFirstTime = 2 then 'Повторно'
					else ''
				end as EvnPrescrMse_IsFirstTime,
				MDAT.MseDirectionAimType_Name,
				D.diag_FullName as Diag_Name,
				PA.Person_Fio,
				convert(varchar(10), PA.Person_BirthDay, 104) as Person_BirthDay,
				EPM.EvnPrescrMse_IsSigned,
				convert(varchar(10), EPM.EvnPrescrMse_signDT, 104) as EvnPrescrMse_signDT
				-- end select
			from
				-- from
				v_EvnPrescrMse EPM (nolock)
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = EPM.EvnStatus_id
				outer apply (
					select top 1 EPVK.*
					from v_EvnPrescrVK EPVK with(nolock)
					where EPVK.EvnPrescrMse_id = EPM.EvnPrescrMse_id
						and EPVK.Person_id = EPM.Person_id
					order by EPVK.EvnPrescrVK_id desc
				) EPVK
				outer apply (
					SELECT TOP 1 EVK.EvnVK_id
					FROM dbo.v_EvnVK EVK (nolock)
					WHERE EVK.Person_id = EPM.Person_id
						and (EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id or EVK.EvnVK_id = EPM.EvnVK_id)
				) EVK
				left join v_TimetableMedService_lite TTMS with(nolock) on TTMS.TimetableMedService_id = EPVK.TimetableMedService_id
				outer apply (
					select top 1 ED.EvnDirection_id, ED.EvnDirection_Num
					from v_EvnDirection_all ED with(nolock)
					where (ED.EvnDirection_rid = EPVK.EvnPrescrVK_rid or ED.EvnDirection_pid is null)
						and ED.Person_id = EPM.Person_id -- фильтр нужен, т.к. теоретически предыдущее условие может выдать направление на другого пациента
						and ED.MedService_id = EPVK.MedService_id
						and ED.EvnDirection_insDT between dateadd(minute,-1, EPVK.EvnPrescrVK_insDT) and dateadd(minute,1, EPVK.EvnPrescrVK_insDT)
				) ED
				left join v_MseDirectionAimType MDAT (nolock) on MDAT.MseDirectionAimType_id = EPM.MseDirectionAimType_id
				left join v_Diag D (nolock) on D.Diag_id = EPM.Diag_id
				left join v_Person_all PA (nolock) on PA.Server_id = EPM.Server_id
					and PA.PersonEvn_id = EPM.PersonEvn_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				EPM.EvnPrescrMse_id
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}
	
	/**
	 *	Method description
	 */
	function checkEvnPrescrMseExists($data)
	{
		$query = "
			select
				EvnPrescrMse_id
			from
				v_EvnPrescrMse (nolock)
			where
				Person_id = :Person_id and 
				Lpu_gid = :Lpu_id
		";
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Загрузка списка направлений на МСЭ
	 */
	function getEvnPrescrMseList($data)
	{
		$filter = '';
		if ($this->regionNick == 'ufa') {
			$filter .= ' and epm.EvnStatus_id = 27 ';
		}
		if (in_array($this->regionNick, ['perm', 'vologda'])) {
			$filter .= " and not exists(
				select top 1
					epvk.EvnPrescrVK_id
				from
					v_EvnPrescrVK epvk (nolock)
					inner join v_EvnStatus es (nolock) on es.EvnStatus_id = epvk.EvnStatus_id
				where
					epvk.EvnPrescrMse_id = epm.EvnPrescrMse_id
					and es.EvnStatus_SysNick in ('Agreement','RequestReception','SubmittedVK','GeneratedVK')
			)";
		}
		
		$orFilter = '';
		if (!empty($data['EvnPrescrMse_id'])) {
			$orFilter = ' or epm.EvnPrescrMse_id = :EvnPrescrMse_id';
		}

		$query = "
			select
				epm.EvnPrescrMse_id,
				'Создано ' + convert(varchar(10), epm.EvnPrescrMse_setDT, 104) as EvnPrescrMse_Name,
				epm.Diag_id,
				ISNULL(es.EvnStatus_SysNick, 'New') as EvnStatus_SysNick
			from
				v_EvnPrescrMse epm (nolock)
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = epm.EvnStatus_id
			where
				epm.Person_id = :Person_id 
				and ((
					epm.Lpu_gid = :Lpu_id
					{$filter}
				) {$orFilter})
		";
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Загрузка списка направлений на ВМП
	 */
	function getEvnDirectionHTMList($data)
	{
		$orFilter = '';
		if (!empty($data['EvnDirectionHTM_id'])) {
			$orFilter = ' or edh.EvnDirectionHTM_id = :EvnDirectionHTM_id';
		}
		
		$query = "
			select
				edh.EvnDirectionHTM_id,
				'Создано ' + convert(varchar(10), edh.EvnDirectionHTM_setDT, 104) + ISNULL(' (' + hmcc.HTMedicalCareClass_Name + ')', '') as EvnDirectionHTM_Name,
				edh.Diag_id,
				ISNULL(es.EvnStatus_SysNick, 'New') as EvnStatus_SysNick
			from
				v_EvnDirectionHTM edh (nolock)
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = edh.EvnStatus_id
				left join v_HTMedicalCareClass hmcc (nolock) on hmcc.HTMedicalCareClass_id = edh.HTMedicalCareClass_id
			where
				edh.Person_id = :Person_id 
				and ((
					edh.Lpu_sid = :Lpu_id
					and not exists(
						select top 1
							epvk.EvnPrescrVK_id
						from
							v_EvnPrescrVK epvk (nolock)
							inner join v_EvnStatus es (nolock) on es.EvnStatus_id = epvk.EvnStatus_id
						where
							epvk.EvnDirectionHTM_id = edh.EvnDirectionHTM_id
							and es.EvnStatus_SysNick in ('Agreement','RequestReception','SubmittedVK','GeneratedVK')
					)
				) {$orFilter})
		";
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Method description
	 */
	function getEvnPrescrMseData($data)
	{
		$query = "
			select
				EPM.EvnPrescrMse_id,
				isnull(EPM.Diag_id,EPVK.Diag_id) as Diag_id,
				EPM.EvnPrescrMse_MainDisease,
				EPVK.MedPersonal_sid,
				EPVK.PalliatQuestion_id,
				EPVK.CauseTreatmentType_id
			from
				v_EvnPrescrVK EPVK (nolock)
				left join v_EvnPrescrMse EPM (nolock) on EPM.EvnPrescrMse_id = EPVK.EvnPrescrMse_id
			where
				EPVK.EvnPrescrVK_id = :EvnPrescrVK_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if ( count($result) && !empty($result[0]['EvnPrescrMse_id']) ) {
				$data['EvnPrescrMse_id'] = $result[0]['EvnPrescrMse_id'];
				$result[0]['SopDiagList'] = $this->getDiagList($data, 1);
				$result[0]['OslDiagList'] = $this->getDiagList($data, 2);
				foreach($result[0]['OslDiagList'] as &$val) {
					$val = $val['Diag_id'];
				}
			}
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * Обновление статуса направления на МСЭ
	 * @param array $data
	 * @return array
	 */
	function updateEvnPrescrMseStatus($data) {
		$params = array(
			'Evn_id' => $data['Evn_id'],
			'EvnStatus_SysNick' => $data['EvnStatus_SysNick'],
			'EvnClass_SysNick' => 'EvnPrescrMse',
			'pmUser_id' => $data['pmUser_id'],
			'EvnStatusHistory_Cause' => $data['EvnStatusHistory_Cause']
		);
		$query = "	
				select top 1
					MSMP.MedServiceMedPersonal_id
				from 
					v_MedServiceMedPersonal MSMP with (NOLOCK)
				where 
					MSMP.MedService_id = :MedService_id";
		if(!empty($data['MedPersonal_id']) && !empty($data['MedService_id'])){
			$f =  " and MedPersonal_id = :MedPersonal_id";
			$MedServiceMedPersonalData = $this->getFirstRowFromQuery($query.$f, array(
				'MedService_id' => $data['MedService_id'],
				'MedPersonal_id' => $data['MedPersonal_id']
			));
		}
		if(empty($MedServiceMedPersonalData['MedServiceMedPersonal_id']) && !empty($data['MedService_id'])){
			$MedServiceMedPersonalData = $this->getFirstRowFromQuery($query, array('MedService_id' => $data['MedService_id']));
		}

		$params['MedServiceMedPersonal_id'] = (!empty($MedServiceMedPersonalData['MedServiceMedPersonal_id']))?$MedServiceMedPersonalData['MedServiceMedPersonal_id']:null;
		try {
			$this->beginTransaction();

			if ($params['EvnStatus_SysNick'] == 'Sended') {
				$EvnPrescrMseData = $this->getFirstRowFromQuery("
					select top 1
						EvnStatus_id,
						EvnPrescrMse_EaviiasGUID
					from v_EvnPrescrMse with(nolock) 
					where EvnPrescrMse_id = :Evn_id
				", $params);
				if (!is_array($EvnPrescrMseData)) {
					throw new Exception('Ошибка при получении предыдущего статуса нарпавления на МСЭ');
				}

				if ($EvnPrescrMseData['EvnStatus_id'] == 30 && !empty($EvnPrescrMseData['EvnPrescrMse_EaviiasGUID'])) {
					$resp = $this->updateEvnPrescrMseEaviiasGUID(array(
						'EvnPrescrMse_id' => $params['Evn_id'],
						'EvnPrescrMse_EaviiasGUID' => null,
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
				}
			}

			$this->load->model('Evn_model', 'Evn_model');
			$resp = $this->Evn_model->updateEvnStatus($params);
			if (!$resp) {
				throw new Exception('Ошибка при обновлении статуса направления на МСЭ');
			}

			$this->commitTransaction();
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError('', $e->getMessage());
		}

		return array(array('success' => true));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function updateEvnPrescrMseEaviiasGUID($data) {
		$params = array(
			'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
			'EvnPrescrMse_EaviiasGUID' => $data['EvnPrescrMse_EaviiasGUID'],
		);
		$query = "					
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				update EvnPrescrMse with(rowlock)
				set EvnPrescrMse_EaviiasGUID = :EvnPrescrMse_EaviiasGUID
				where EvnPrescrMse_id = :EvnPrescrMse_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->queryResult('Ошибка при обновлении GUID ');
		}
		return $resp;
	}

	/**
	 * Экспорт данных направлений на МСЭ
	 * @param array $data
	 * @return array
	 */
	function exportEvnPrescrMse($data, $autoExport = false) {
		$ARMType = !empty($data['ARMType'])?$data['ARMType']:null;
		$filters = array();
		$params = array();

		$filters[] = "EPM.EvnStatus_id = :EvnStatus_id";
		$params['EvnStatus_id'] = $data['EvnStatus_id'];
		$params['Region_id'] = getRegionNumber();

		$filters[] = "cast(isnull(EPM.EvnPrescrMse_statusDate, EPM.EvnPrescrMse_setDate) as date) between :begDate and :endDate";
		$params['begDate'] = $data['ExportDateRange'][0];
		$params['endDate'] = $data['ExportDateRange'][1];

		// $filters[] = "EPM.MseDirectionAimType_id in (1,2,3,4)"; // убрали по задаче #165874

		if (!empty($data['Lpu_oid'])) {
			if ($ARMType == 'vk') {
				$filters[] = "MS.Lpu_id = :Lpu_oid";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			} elseif ($ARMType == 'mse') {
				$filters[] = "dMS.Lpu_id = :Lpu_oid";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			} else {
				$filters[] = "EPM.Lpu_id = :Lpu_oid";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			}
		} else {
			if ($ARMType == 'spec_mz') {
				$filters[] = "L.Org_tid = :Org_id";
				$params['Org_id'] = $data['session']['org_id'];
			}
			elseif ($ARMType != 'superadmin' && !empty($data['Lpu_id'])) {
				$filters[] = "L.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}
		}

		if (!empty($data['MedService_id'])) {
			if ($ARMType == 'vk') {
				$filters[] = "MS.MedService_id = :MedService_id";
				$params['MedService_id'] = $data['MedService_id'];
			} elseif ($ARMType == 'mse') {
				$filters[] = "dMS.MedService_id = :MedService_id";
				$params['MedService_id'] = $data['MedService_id'];
			}
		}

		if (empty($data['ExportAllRecords'])) {
			$filters[] = "EPM.EvnPrescrMse_EaviiasGUID is null";
		}

		if (!empty($data['G_CODE'])) {
			$filters[] = "EPM.EvnPrescrMse_EaviiasGUID = :G_CODE";
			$params['G_CODE'] = $data['G_CODE'];
		}

		$filters_str = implode("\nand ", $filters);
		
		if ($autoExport) {
			$filters_str = "
				( {$filters_str} and not exists (
						select top 1 slp.ServiceListPackage_id
						from stg.ServiceListPackage slp (nolock)
						where 
							slp.ServiceListPackage_ObjectName = 'EvnPrescrMse' and
							slp.ServiceListPackage_ObjectID = EPM.EvnPrescrMse_id
					)
				)
			";
		}

		$queryBase = "
			declare @curDT datetime = dbo.tzGetDate();
			
			select
				EPM.EvnPrescrMse_id,
				EPM.Person_id,
				EPM.EvnVK_id,
				EPM.EvnPrescrMse_EaviiasGUID as G_Code,
				MHO.MseHeadOffice_Code as 'TargetMseOrg/ID',
				MHO.MseHeadOffice_Name as 'TargetMseOrg/Value',
				L.Lpu_Nick as 'MedOrgName',
				ISNULL(ua.Address_Address, pa.Address_Address) as 'MedOrgAddress',
				klr.KLRgn_id as 'MedOrgTerritorySubject/ID',
				klr.KLRgn_FullName as 'MedOrgTerritorySubject/Value',
				L.Lpu_OGRN as 'MedOrgOgrn',
				EVK.EvnVK_NumProtocol as 'ProtocolNum',
				convert(varchar(10), EVK.EvnVK_setDT, 120) as 'ProtocolDate',
				case when EPM.EvnPrescrMse_IsCanAppear = 2 then 'true' else 'false' end as 'MseMustBeAtHome',
				case when EPM.EvnPrescrMse_IsPalliative = 2 then 'true' else 'false' end as 'PalliativeHelpNeeded',
				convert(varchar(10), ESH_SENT.EvnStatusHistory_begDate, 120) as 'IssueDate',
				nullif(EPM.EvnPrescrMse_AimMseOver, '') as 'ExaminationPurposesComment',
				P.Person_SurName as 'LastName',
				P.Person_FirName as 'FirstName',
				P.Person_SecName as 'SecondName',
				convert(varchar(10), P.Person_BirthDay, 120) as 'BirthDate',
				dbo.Age2(P.Person_BirthDay, @curDT) as 'Age/Years',
				case when datediff(month, P.Person_BirthDay, @curDT) < 12 then cast(datediff(month, P.Person_BirthDay, @curDT) as varchar) else '###' end as 'Age/Months',
				Sex.Sex_fedid as 'Gender',
				ns.KLCountry_id,
				EPM.MilitaryKind_id,
				p.UAddress_id,
				p.PAddress_id,
				case when sc.SocStatus_SysNick = 'bomzh' then 'true' else 'false' end as 'HasNoLivingAddress',
				EPM.EvnPrescrMse_IsPersonInhabitation,
				ogt.OrgType_SysNick as PlaceType_SysNick,
				ISNULL(og.PAddress_id, og.UAddress_id) as PlaceAddress_id,
				ISNULL(og.Org_OGRN, '###') as 'PersonPlace/PlaceOgrn',
				nullif(PP.PersonPhone_Phone, '') as 'Phone',
				PersInfo.PersonInfo_Email as 'Email',
				case
					when p.Person_Snils is not null and len(p.Person_Snils) = 11 then LEFT(p.Person_Snils, 3) + '-' + SUBSTRING(p.Person_Snils, 4, 3) + '-' + SUBSTRING(p.Person_Snils, 7, 3) + ' ' + RIGHT(p.Person_Snils, 2)
					else '###'
				end as 'Snils',
				DTML.DocumentTypeMinLab_Code as 'IdentityDoc/IdentityCardTypeId/ID',
				DTML.DocumentTypeMinLab_Name as 'IdentityDoc/IdentityCardTypeId/Value',
				nullif(D.Document_Ser,'') as 'IdentityDoc/Series',
				nullif(D.Document_Num,'') as 'IdentityDoc/Number',
				OD.Org_Name as 'IdentityDoc/IssueOrgName',
				convert(varchar(10), D.Document_begDate, 120) as 'IdentityDoc/IssueDate',
				EPM.Person_sid,
				DA.DocumentAuthority_Code as 'Representer/AuthorityDoc/IdentityCardTypeId/ID',
				DA.DocumentAuthority_Name as 'Representer/AuthorityDoc/IdentityCardTypeId/Value',
				EPM.EvnPrescrMse_DocumentSer as 'Representer/AuthorityDoc/Series',
				EPM.EvnPrescrMse_DocumentNum as 'Representer/AuthorityDoc/Number',
				EPM.EvnPrescrMse_DocumentIssue as 'Representer/AuthorityDoc/IssueOrgName',
				convert(varchar(10), EPM.EvnPrescrMse_DocumentDate, 120) as 'Representer/AuthorityDoc/IssueDate',
				OS.Org_Nick as 'RepresentativeOrg/Name',
				OS.Org_Ogrn as 'RepresentativeOrg/Ogrn',
				ISNULL(OS.PAddress_id, OS.UAddress_id) as ReprAddress_id,
				EPM.EvnPrescrMse_IsFirstTime,
				EPM.InvalidGroupType_id,
				ISNULL(convert(varchar(10), EPM.EvnPrescrMse_InvalidEndDate, 120), '###') as 'PrevExamInfo/DisabilityEndDate',
				EPM.EvnPrescrMse_InvalidPeriod,
				ict.InvalidCouseType_Code as 'PrevExamInfo/DisabilityReason/ID',
				ict.InvalidCouseType_Name as 'PrevExamInfo/DisabilityReason/Value',
				EPM.EvnPrescrMse_InvalidCouseAnother as 'PrevExamInfo/DisabilityReasonOther',
				EPM.EvnPrescrMse_InvalidCouseAnotherLaw as 'PrevExamInfo/DisabilityReasonOutdated',
				ISNULL(cast(EPM.EvnPrescrMse_InvalidPercent as varchar), '###') as 'PrevExamInfo/ProfLossDegree',
				PDP.ProfDisabilityPeriod_Code as 'PrevExamInfo/ProfLossPeriod/ID',
				PDP.ProfDisabilityPeriod_Name as 'PrevExamInfo/ProfLossPeriod/Value',
				ISNULL(convert(varchar(10), EPM.EvnPrescrMse_ProfDisabilityEndDate, 120), '###') as 'PrevExamInfo/ProfLossEndDate',
				EPM.EvnPrescrMse_ProfDisabilityAgainPercent as 'PrevExamInfo/ProfLossPreviousCases',
				ODI.Org_Nick as 'EducationInfo/OrgName',
				coalesce(EPM.Address_eid, ODI.PAddress_id, ODI.UAddress_id) as EducationAddress_id,
				EPM.LearnGroupType_id,
				EPM.EvnPrescrMse_Dop as 'EducationInfo/LevelValue',
				EPM.EvnPrescrMse_ProfTraining as 'EducationInfo/Profession',
				EPM.EvnPrescrMse_MainProf as 'ProfInfo/MainProfession',
				EPM.EvnPrescrMse_Skill as 'ProfInfo/Qualification',
				EPM.EvnPrescrMse_ExpPost as 'ProfInfo/JobExperience',
				EPM.EvnPrescrMse_Prof as 'ProfInfo/CurrentJob/Profession',
				EPM.EvnPrescrMse_Spec as 'ProfInfo/CurrentJob/Speciality',
				PO.Post_Name as 'ProfInfo/CurrentJob/Position',
				EPM.EvnPrescrMse_CondWork as 'ProfInfo/LaborConditions',
				OJ.Org_Nick as 'ProfInfo/JobPlace',
				coalesce(EPM.Address_oid, OJ.PAddress_id, OJ.UAddress_id) as JobAddress_id,
				YEAR(EPM.EvnPrescrMse_OrgMedDate) as 'MedOrgSupervisionStartYear',
				nullif(EPM.EvnPrescrMse_DiseaseHist, '') as 'DeseaseAnamnesis',
				nullif(EPM.EvnPrescrMse_LifeHist, '') as 'LifeAnamnesis',
				case when esb.EvnStickBase_id is not null then 'true' else 'false' end as 'HasEln',
				ISNULL(cast(esb.EvnStickBase_Num as varchar), '###') as 'ElnNum',
				IR.IPRARegistry_Number as 'RehabEventsResult/IpraNum',
				IR.IPRARegistry_Protocol as 'RehabEventsResult/ProtocolNum',
				convert(varchar(10), IR.IPRARegistry_ProtocolDate, 120) as 'RehabEventsResult/ProtocolDate',
				IRR.IPRAResult_Code as 'RehabEventsResult/ImpairedFunctionsRecovery/ID',
				IRR.IPRAResult_Name as 'RehabEventsResult/ImpairedFunctionsRecovery/Value',
				IRC.IPRAResult_Code as 'RehabEventsResult/LostFunctionsCompensation/ID',
				IRC.IPRAResult_Name as 'RehabEventsResult/LostFunctionsCompensation/Value',
				MRE.MeasuresRehabEffect_Comment as 'RehabEventsResult/Comment',
				ISNULL(cast(PH.PersonHeight_Height as varchar), '###') as 'AnthropometricData/Height',
				ISNULL(cast(PW.PersonWeight_Weight as varchar), '###') as 'AnthropometricData/Weight',
				PT.PhysiqueType_Name as 'AnthropometricData/Constitution',
				ISNULL(cast(EPM.EvnPrescrMse_DailyPhysicDepartures as varchar), '###') as 'AnthropometricData/PhysiologicalFunctionsDailyAmmount',
				ISNULL(cast(EPM.EvnPrescrMse_Waist as varchar), '###') as 'AnthropometricData/WaistSize',
				ISNULL(cast(EPM.EvnPrescrMse_Hips as varchar), '###') as 'AnthropometricData/HipsVolume',
				ISNULL(cast(EPM.EvnPrescrMse_WeightBirth as varchar), '###') as 'AnthropometricData/BirthWeight',
				ISNULL(cast(EPM.EvnPrescrMse_PhysicalDevelopment as varchar), '###') as 'AnthropometricData/PhysicalDevelopment',
				nullif(EPM.EvnPrescrMse_State, '') as 'HealthCondition',
				DI.Diag_Name as 'Diagnosis/MainDesease',
				DI.Diag_Code as 'Diagnosis/MainDeseaseCode',
				cft.ClinicalForecastType_Code as 'ClinicalPrognosis/ID',
				cft.ClinicalForecastType_Name as 'ClinicalPrognosis/Value',
				cpt.ClinicalPotentialType_Code as 'RehabPotential/ID',
				cpt.ClinicalPotentialType_Name as 'RehabPotential/Value',
				cftd.ClinicalForecastType_Code as 'RehabPrognosis/ID',
				cftd.ClinicalForecastType_Name as 'RehabPrognosis/Value',
				isnull(EPM.EvnPrescrMse_Recomm, 'Рекомендации отсутствуют') as 'RecommendedMedEvents',
				isnull(EPM.EvnPrescrMse_MeasureSurgery, 'Рекомендации отсутствуют') as 'RecommendedReconstructiveSurgeryEvents',
				isnull(EPM.EvnPrescrMse_MeasureProstheticsOrthotics, 'Рекомендации отсутствуют') as 'RecommendedProstheticsEvents',
				isnull(EPM.EvnPrescrMse_HealthResortTreatment, 'Рекомендации отсутствуют') as 'SpaTreatment',
				EPM.EvnPrescrMse_FilePath as Attachments
			from
				v_EvnPrescrMse EPM with (nolock)
				inner join MseHeadOffice MHO with (nolock) on MHO.Region_id = :Region_id
				inner join v_Lpu L with (nolock) on L.Lpu_id = EPM.Lpu_id
				inner join v_Person_all P with (nolock) on P.PersonEvn_id = EPM.PersonEvn_id and P.Server_id = EPM.Server_id
				inner join v_EvnVK EVK with (nolock) on EVK.EvnVK_id = EPM.EvnVK_id
				left join v_PhysiqueType PT with (nolock) on PT.PhysiqueType_id = EPM.PhysiqueType_id
				left join v_KLRgn klr with (nolock) on klr.KLRgn_id = MHO.Region_id
				left join v_Sex Sex with (nolock) on Sex.Sex_id = P.Sex_id
				left join v_NationalityStatus ns with (nolock) on ns.NationalityStatus_id = p.NationalityStatus_id
				left join v_PersonInfo PersInfo with(nolock) on PersInfo.Person_id = P.Person_id
				left join v_Address_all ua with (nolock) on ua.Address_id = l.UAddress_id
				left join v_Address_all pa with (nolock) on pa.Address_id = l.PAddress_id
				left join v_SocStatus sc with (nolock) on sc.SocStatus_id = p.SocStatus_id
				outer apply (
					select top 1 PersonPhone_Phone
					from v_PersonPhone with(nolock)
					where Person_id = P.Person_id
					order by PersonPhone_id desc
				) PP
				outer apply (
					select top 1
						ESH.EvnStatusHistory_begDate
					from
						v_EvnStatusHistory ESH (nolock)
					where
						ESH.Evn_id = EPM.EvnPrescrMse_id
						and ESH.EvnStatus_id = 28 -- отправлено 
				) ESH_SENT
				outer apply (
					select top 1
						ESB.EvnStickBase_id,
						ESB.EvnStickBase_Num
					from
						v_EvnStickBase ESB (nolock)
					where
						ESB.Person_id = EPM.Person_id
						and ESB.EvnStickBase_setDT <= EPM.EvnPrescrMse_setDT
						and ISNULL(ESB.EvnStickBase_disDT, EPM.EvnPrescrMse_setDT) <= EPM.EvnPrescrMse_setDT
				) ESB
				left join v_Document D with (nolock) on D.Document_id = P.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join DocumentTypeMinLab DTML with (nolock) on DTML.DocumentType_id = D.DocumentType_id
				left join DocumentAuthority DA with (nolock) on DA.DocumentAuthority_id = EPM.DocumentAuthority_id
				left join v_OrgDep OD with (nolock) on OD.OrgDep_id = D.OrgDep_id
				left join v_EvnPrescrVK EPVK with(nolock) on EPVK.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_MedService MS with(nolock) on MS.MedService_id = isnull(EVK.MedService_id, EPVK.MedService_id)
				left join v_MedService dMS with(nolock) on dMS.MedService_id = EPM.MedService_id
				left join v_Org OS with (nolock) on OS.Org_id = EPM.Org_sid
				left join v_MeasuresRehabEffect MRE with (nolock) on MRE.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_IPRARegistry IR with (nolock) on IR.IPRARegistry_id = MRE.IPRARegistry_id
				left join v_IPRAResult IRR with (nolock) on IRR.IPRAResult_id = MRE.IPRAResult_rid
				left join v_IPRAResult IRC with (nolock) on IRC.IPRAResult_id = MRE.IPRAResult_cid
				left join v_InvalidCouseTypeLink ictl (nolock) on ictl.InvalidCouseType_id = EPM.InvalidCouseType_id
				left join nsi.v_InvalidCouseType ict (nolock) on ict.InvalidCouseType_id = ictl.InvalidCouseType_nid
				left join v_ProfDisabilityPeriod PDP (nolock) on PDP.ProfDisabilityPeriod_id = EPM.ProfDisabilityPeriod_id
				left join v_PersonHeight PH with (nolock) on PH.PersonHeight_id = EPM.PersonHeight_id
				left join v_PersonWeight PW with (nolock) on PW.PersonWeight_id = EPM.PersonWeight_id
				left join v_Diag DI with (nolock) on DI.Diag_id = EPM.Diag_id
				left join v_ClinicalForecastType cft with (nolock) on cft.ClinicalForecastType_id = EPM.ClinicalForecastType_id
				left join v_ClinicalPotentialType cpt with (nolock) on cpt.ClinicalPotentialType_id = EPM.ClinicalPotentialType_id
				left join v_ClinicalForecastType cftd with (nolock) on cftd.ClinicalForecastType_id = EPM.ClinicalForecastType_did
				left join v_Org oj with (nolock) on oj.Org_id = EPM.Org_id 
				left join v_Post po with (nolock) on po.Post_id = EPM.Post_id
				left join v_Org odi with (nolock) on odi.Org_id = EPM.Org_did 
				left join v_Org og with (nolock) on og.Org_id = EPM.Org_gid 
				left join v_OrgType ogt with (nolock) on ogt.OrgType_id = og.OrgType_id 
		";
		$query = "{$queryBase}
			where
				{$filters_str}
		";
		//echo getDebugSQL($query, $params);exit;
		$EvnPrescrMseList = $this->queryResult($query, $params);
		if ($autoExport) {
			$query2 = "{$queryBase}
				where exists (
					select top 1 slp.ServiceListPackage_id
					from stg.ServiceListPackage slp (nolock)
					inner join stg.ServiceListDetailLog sldl (nolock) on sldl.ServiceListPackage_id = slp.ServiceListPackage_id
					where 
						slp.ServiceListPackage_ObjectName = 'EvnPrescrMse' and
						slp.ServiceListPackage_ObjectID = EPM.EvnPrescrMse_id and 
						sldl.ServiceListLogType_id = 2 and 
						cast(slp.ServiceListPackage_insDT as date) = cast(dateadd(day, -1, @curDT) as date)
				)
			";
			$EvnPrescrMseList = array_merge($EvnPrescrMseList, $this->queryResult($query2, $params));
		}
		if (!is_array($EvnPrescrMseList)) {
			return $this->createError('','Ошибка при получении данных направлений на МСЭ для экспорта');
		}
		if (count($EvnPrescrMseList) == 0) {
			if ($autoExport)
				return [];
			else
				return $this->createError('','Не найдены направления на МСЭ для экспорта');
		}

		// тянем дополнительную информацию
		$personIds = array();
		$representerIds = array();
		$addressIds = array();
		$evnvkIds = array();
		$evnPrescrMseIds = array();
		foreach($EvnPrescrMseList as $item) {
			if (!empty($item['Person_id']) && !in_array($item['Person_id'], $personIds)) {
				$personIds[] = $item['Person_id'];
			}

			if (!empty($item['Person_sid']) && !in_array($item['Person_sid'], $representerIds)) {
				$representerIds[] = $item['Person_sid'];
			}

			if (!empty($item['PAddress_id'])) {
				if (!in_array($item['PAddress_id'], $addressIds)) {
					$addressIds[] = $item['PAddress_id'];
				}
			} else if (!empty($item['UAddress_id'])) {
				if (!in_array($item['UAddress_id'], $addressIds)) {
					$addressIds[] = $item['UAddress_id'];
				}
			}

			if (!empty($item['PlaceAddress_id']) && !in_array($item['PlaceAddress_id'], $addressIds)) {
				$addressIds[] = $item['PlaceAddress_id'];
			}

			if (!empty($item['EducationAddress_id']) && !in_array($item['EducationAddress_id'], $addressIds)) {
				$addressIds[] = $item['EducationAddress_id'];
			}

			if (!empty($item['JobAddress_id']) && !in_array($item['JobAddress_id'], $addressIds)) {
				$addressIds[] = $item['JobAddress_id'];
			}

			if (!empty($item['ReprAddress_id']) && !in_array($item['ReprAddress_id'], $addressIds)) {
				$addressIds[] = $item['ReprAddress_id'];
			}

			if (!empty($item['EvnVK_id']) && !in_array($item['EvnVK_id'], $evnvkIds)) {
				$evnvkIds[] = $item['EvnVK_id'];
			}

			if (!empty($item['EvnPrescrMse_id']) && !in_array($item['EvnPrescrMse_id'], $evnPrescrMseIds)) {
				$evnPrescrMseIds[] = $item['EvnPrescrMse_id'];
			}
		}

		$representerData = array();
		$emptyRepresenter = array(
			'Representer/LastName' => null,
			'Representer/FirstName' => null,
			'Representer/SecondName' => null,
			'Representer/IdentityDoc/IdentityCardTypeId/ID' => null,
			'Representer/IdentityDoc/IdentityCardTypeId/Value' => null,
			'Representer/IdentityDoc/Series' => null,
			'Representer/IdentityDoc/Number' => null,
			'Representer/IdentityDoc/IssueOrgName' => null,
			'Representer/IdentityDoc/IssueDate' => null,
			'Representer/Phone' => null,
			'Representer/Email' => null,
			'Representer/Snils' => '###'
		);
		if (!empty($representerIds)) {
			$resp_rep = $this->queryResult("
				select
					PS.Person_id,
					PS.Person_SurName as 'Representer/LastName',
					PS.Person_FirName as 'Representer/FirstName',
					PS.Person_SecName as 'Representer/SecondName',
					DTML.DocumentTypeMinLab_Code as 'Representer/IdentityDoc/IdentityCardTypeId/ID',
					DTML.DocumentTypeMinLab_Name as 'Representer/IdentityDoc/IdentityCardTypeId/Value',
					nullif(D.Document_Ser,'') as 'Representer/IdentityDoc/Series',
					nullif(D.Document_Num,'') as 'Representer/IdentityDoc/Number',
					OD.Org_Name as 'Representer/IdentityDoc/IssueOrgName',
					convert(varchar(10), D.Document_begDate, 120) as 'Representer/IdentityDoc/IssueDate',
					nullif(PP.PersonPhone_Phone, '') as 'Representer/Phone',
					PersInfo.PersonInfo_Email as 'Representer/Email',
					case
						when ps.Person_Snils is not null and len(ps.Person_Snils) = 11 then LEFT(ps.Person_Snils, 3) + '-' + SUBSTRING(ps.Person_Snils, 4, 3) + '-' + SUBSTRING(ps.Person_Snils, 7, 3) + ' ' + RIGHT(ps.Person_Snils, 2)
						else '###'
					end as 'Representer/Snils'
				from
					v_PersonState ps (nolock)
					left join v_PersonInfo PersInfo with(nolock) on PersInfo.Person_id = PS.Person_id
					left join v_Document D with (nolock) on D.Document_id = PS.Document_id
					left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
					left join DocumentTypeMinLab DTML with (nolock) on DTML.DocumentType_id = D.DocumentType_id
					left join v_OrgDep OD with (nolock) on OD.OrgDep_id = D.OrgDep_id
					outer apply (
						select top 1 PersonPhone_Phone
						from v_PersonPhone with(nolock)
						where Person_id = PS.Person_id
						order by PersonPhone_id desc
					) PP
				where
					ps.Person_id in ('" . implode("','", $representerIds) . "')
			");
			if (!is_array($resp_rep)) {
				return $this->createError('', 'Ошибка при получении данных представителей');
			}
			foreach($resp_rep as $one_rep) {
				$representerData[$one_rep['Person_id']] = $one_rep;
			}
			unset($resp_rep);
		}

		$addressData = array();
		if (!empty($addressIds)) {
			$resp_rep = $this->queryResult("
				select
					A.Address_id,
					ISNULL(NULLIF(A.Address_Zip, ''), '###') as 'ZipCode',
					Rgn.KLRgn_id as 'TerritorySubject/ID',
					Rgn.KLRgn_FullName as 'TerritorySubject/Value',
					null as 'TerritorySubjectOther',
					'###' as 'District',
					isnull(City.KLCity_FullName, Town.KLTown_FullName) as 'Place',
					Street.KLStreet_FullName as 'Street',
					ISNULL(A.Address_House + isnull(', '+nullif(A.Address_Corpus, ''), ''), '###') as 'Building',
					ISNULL(nullif(A.Address_Flat, ''), '###') as 'Flat'
				from
					v_Address_all a with (nolock)
					inner join v_KLRgn Rgn with(nolock) on Rgn.KLRgn_id = A.KLRgn_id
					left join v_KLSubRgn SubRgn with(nolock) on SubRgn.KLSubRgn_id = A.KLSubRgn_id
					left join v_KLCity City with(nolock) on City.KLCity_id = A.KLCity_id
					left join v_KLTown Town with(nolock) on Town.KLTown_id = A.KLTown_id
					left join v_KLStreet Street with(nolock) on Street.KLStreet_id = A.KLStreet_id
				where
					a.Address_id in ('" . implode("','", $addressIds) . "')
			");
			if (!is_array($resp_rep)) {
				return $this->createError('', 'Ошибка при получении данных адресов');
			}
			foreach($resp_rep as $one_rep) {
				$addressData[$one_rep['Address_id']] = $one_rep;
			}
			unset($resp_rep);
		}

		$evnStickData = array();
		if (!empty($personIds)) {
			$resp_rep = $this->getEvnStickOfYear(array(
				'Person_ids' => $personIds
			));
			if (!is_array($resp_rep)) {
				return $this->createError('', 'Ошибка при получении данных ЛВН');
			}
			foreach ($resp_rep as $one_rep) {
				$evnStickData[$one_rep['Person_id']][] = array(
					'StartDate' => ConvertDateEx($one_rep['EvnStick_setDate']),
					'EndDate' => ConvertDateEx($one_rep['EvnStick_disDate']),
					'DaysCount' => $one_rep['DayCount'],
					'Diagnosis' => $one_rep['Diag_Name']
				);
			}
			unset($resp_rep);
		}

		$evnVKExpertData = array();
		if (!empty($evnvkIds)) {
			$query = "
				select
					EVKE.EvnVK_id,
					EVKE.ExpertMedStaffType_id as Type,
					MP.Person_Fio as Person
				from
					v_EvnVKExpert EVKE with(nolock)
					inner join v_MedServiceMedPersonal MSMP with(nolock) on MSMP.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
					cross apply (
						select top 1 MP.*
						from v_MedPersonal MP with(nolock)
						where MP.MedPersonal_id = MSMP.MedPersonal_id
					) MP
				where
					EVKE.EvnVK_id in ('" . implode("','", $evnvkIds) . "')
				order by 
					EVKE.EvnVK_id
			";
			$resp_rep = $this->queryResult($query);
			if (!is_array($resp_rep)) {
				return $this->createError('','Ошибка при получении данных экспертов врачебной комиссии');
			}
			foreach($resp_rep as $one_rep) {
				if ($one_rep['Type'] == 1) {
					$evnVKExpertData[$one_rep['EvnVK_id']]['MedCommissionChairman'] = $one_rep['Person'];
				} else {
					$evnVKExpertData[$one_rep['EvnVK_id']]['MedCommissionMembers'][] = $one_rep;
				}
			}
		}

		$requiredMedExamsData = array();
		$diagData = array();
		$aimTypeData = array();
		if (!empty($evnPrescrMseIds)) {
			$query = "
				select
					epml.EvnPrescrMse_id,
					convert(varchar(10), eu.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
					uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name as UslugaComplex_Name,
					eu.EvnClass_SysNick,
					eup.EvnUslugaPar_Comment,
					msf.Person_Fio,
					ps.PostMed_Name,
					sr.StudyResult_Name,
					eup.EvnLabSample_id,
					eu.EvnUsluga_id
				from
					EvnPrescrMseLink epml (nolock)
					inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_id = epml.EvnUsluga_id
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					left join v_EvnUslugaPar eup (nolock) on eup.EvnUslugaPar_id = eu.EvnUsluga_id
					left join v_StudyResult sr (nolock) on sr.StudyResult_id = eup.StudyResult_id
					left join v_MedStaffFact msf (nolock) on eup.MedStaffFact_id = msf.MedStaffFact_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id 
				where
					epml.EvnPrescrMse_id in ('" . implode("','", $evnPrescrMseIds) . "')
			";
			$resp_rep = $this->queryResult($query);
			if (!is_array($resp_rep)) {
				return $this->createError('','Ошибка при получении данных экспертов врачебной комиссии');
			}
			foreach($resp_rep as $one_rep) {
				if (empty($requiredMedExamsData[$one_rep['EvnPrescrMse_id']])) {
					$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] = '';
				} else {
					$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ', ';
				}

				$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= $one_rep['EvnUsluga_setDate'] . ' ' . $one_rep['UslugaComplex_Name'];

				if (!empty($one_rep['StudyResult_Name'])) {
					$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' ' . $one_rep['StudyResult_Name'];
				}

				if (!empty($one_rep['EvnUslugaPar_Comment'])) {
					$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' ' . $one_rep['EvnUslugaPar_Comment'];
				}

				if (!empty($one_rep['EvnLabSample_id'])) {
					// запрашиваем результаты тестов
					$resp_ut = $this->queryResult("
						select
							ut.UslugaTest_id,
							uc.UslugaComplex_Code,
							ut.UslugaTest_ResultValue,
							ut.UslugaTest_ResultLower,
							ut.UslugaTest_ResultUpper,
							ut.UslugaTest_ResultUnit
						from
							v_UslugaTest ut (nolock)
							inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ut.UslugaComplex_id
						where
							ut.UslugaTest_pid = :EvnUsluga_id
							and ut.UslugaTest_ResultApproved = 2
					", array(
						'EvnUsluga_id' => $one_rep['EvnUsluga_id']
					));

					foreach($resp_ut as $one_ut) {
						$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' ' . $one_ut['UslugaComplex_Code'];
						$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' ' . $one_ut['UslugaTest_ResultValue'];
						$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' (' . $one_ut['UslugaTest_ResultLower'];
						$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' - ' . $one_ut['UslugaTest_ResultUpper'];
						$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ') ' . $one_ut['UslugaTest_ResultUnit'];
					}
				}

				if (!empty($one_rep['PostMed_Name'])) {
					$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' ' . $one_rep['PostMed_Name'];
				}

				if (!empty($one_rep['Person_Fio'])) {
					$requiredMedExamsData[$one_rep['EvnPrescrMse_id']] .= ' ' . $one_rep['Person_Fio'];
				}
			}

			$query = "
				select
					EPMDL.EvnPrescrMse_id,
					EPMDL.Diag_id,
					EPMDL.Diag_oid,
					D.Diag_Code as Diag_Code,
					D.Diag_Name as Diag_Name,
					D2.Diag_Code as DiagOsl_Code,
					D2.Diag_Name as DiagOsl_Name
				from
					v_EvnPrescrMseDiagLink EPMDL with (nolock)
					inner join v_Diag D with (nolock) on D.Diag_id = ISNULL(EPMDL.Diag_id, EPMDL.Diag_oid)
					left join v_EvnPrescrMseDiagMkb10Link EPMDML with (nolock) on EPMDML.EvnPrescrMseDiagLink_id = EPMDL.EvnPrescrMseDiagLink_id
					left join v_Diag D2 with (nolock) on D2.Diag_id = EPMDML.Diag_id
				where
					EPMDL.EvnPrescrMse_id in ('" . implode("','", $evnPrescrMseIds) . "')
			";
			$resp_rep = $this->queryResult($query);
			if (!is_array($resp_rep)) {
				return $this->createError('','Ошибка при получении данных экспертов врачебной комиссии');
			}
			foreach($resp_rep as $one_rep) {
				if (!empty($one_rep['Diag_id'])) {
					$diagData[$one_rep['EvnPrescrMse_id']]['sopcodes'][$one_rep['Diag_id']] = array(
						'Code' => $one_rep['Diag_Code']
					);

					$diagData[$one_rep['EvnPrescrMse_id']]['soptext'][$one_rep['Diag_id']] = $one_rep['Diag_Code'] . ' ' . $one_rep['Diag_Name'];
				} else {
					$diagData[$one_rep['EvnPrescrMse_id']]['osntext'][$one_rep['Diag_id']] = $one_rep['Diag_Code'] . ' ' . $one_rep['Diag_Name'];
				}

				if (!empty($one_rep['DiagOsl_Code'])) {
					$diagData[$one_rep['EvnPrescrMse_id']]['oslsoptext'][$one_rep['DiagOsl_Code']] = $one_rep['DiagOsl_Code'] . ' ' . $one_rep['DiagOsl_Name'];
				}
			}

			$query = "
				select
					epm.EvnPrescrMse_id,
					mdatml.MseDirectionAimTypeMinLab_Code,
					mdatml.MseDirectionAimTypeMinLab_Name
				from
					v_EvnPrescrMse epm with (nolock)
					inner join MseDirectionAimTypeMinLab mdatml with (nolock) on mdatml.MseDirectionAimType_id = epm.MseDirectionAimType_id
				where
					epm.EvnPrescrMse_id in ('" . implode("','", $evnPrescrMseIds) . "')
				
				union
					
				select
					mdatl.EvnPrescrMse_id,
					mdatml.MseDirectionAimTypeMinLab_Code,
					mdatml.MseDirectionAimTypeMinLab_Name
				from
					v_MseDirectionAimTypeLink mdatl with (nolock)
					inner join MseDirectionAimTypeMinLab mdatml with (nolock) on mdatml.MseDirectionAimType_id = mdatl.MseDirectionAimType_id
				where
					mdatl.EvnPrescrMse_id in ('" . implode("','", $evnPrescrMseIds) . "')
			";
			$resp_rep = $this->queryResult($query);
			if (!is_array($resp_rep)) {
				return $this->createError('','Ошибка при получении данных экспертов врачебной комиссии');
			}
			foreach($resp_rep as $one_rep) {
				if (!isset($aimTypeData[$one_rep['EvnPrescrMse_id']])) {
					$aimTypeData[$one_rep['EvnPrescrMse_id']] = array();
				}
				$aimTypeData[$one_rep['EvnPrescrMse_id']][] = array(
					'ExaminationPurposes/ID' => $one_rep['MseDirectionAimTypeMinLab_Code'],
					'ExaminationPurposes/Value' => $one_rep['MseDirectionAimTypeMinLab_Name']
				);
			}
		}

		// заполняем то, что не вошло в основной запрос
		foreach($EvnPrescrMseList as &$item) {
			if (!empty($item['KLCountry_id']) && $item['KLCountry_id'] == 643) {
				$item['Citizenship/ID'] = 1;
				$item['Citizenship/Value'] = 'Гражданин Российской Федерации';
			} else if (!empty($item['KLCountry_id']) && $item['KLCountry_id'] != 643) {
				$item['Citizenship/ID'] = 2;
				$item['Citizenship/Value'] = 'Гражданин иностранного государства, находящийся на территории Российской Федерации';
			} else {
				$item['Citizenship/ID'] = 3;
				$item['Citizenship/Value'] = 'Лицо без гражданства, находящееся на территории Российской Федерации';
			}

			switch($item['MilitaryKind_id']) {
				case 1:
					$item['MilitaryDuty/ID'] = 1;
					$item['MilitaryDuty/Value'] = 'гражданин, состоящий на воинском учете';
					break;
				case 2:
					$item['MilitaryDuty/ID'] = 4;
					$item['MilitaryDuty/Value'] = 'гражданин, не состоящий на воинском учете';
					break;
				case 3:
					$item['MilitaryDuty/ID'] = 2;
					$item['MilitaryDuty/Value'] = 'гражданин, не состоящий на воинском учете, но обязанный состоять на воинском учете';
					break;
				case 4:
					$item['MilitaryDuty/ID'] = 3;
					$item['MilitaryDuty/Value'] = 'гражданин, поступающий на воинской учет';
					break;
				default:
					$item['MilitaryDuty/ID'] = null;
					$item['MilitaryDuty/Value'] = null;
					break;
			}

			if ($item['EvnPrescrMse_IsFirstTime'] == 2) {
				$item['RepetitionKind/ID'] = 1;
				$item['RepetitionKind/Value'] = 'Первично';
			} else {
				$item['RepetitionKind/ID'] = 2;
				$item['RepetitionKind/Value'] = 'Повторно';
			}

			switch ($item['InvalidGroupType_id']) {
				case 2:
					$item['PrevExamInfo/DisabilityGroup/ID'] = 1;
					$item['PrevExamInfo/DisabilityGroup/Value'] = 'Первая группа';
					break;
				case 3:
					$item['PrevExamInfo/DisabilityGroup/ID'] = 2;
					$item['PrevExamInfo/DisabilityGroup/Value'] = 'Вторая группа';
					break;
				case 4:
					$item['PrevExamInfo/DisabilityGroup/ID'] = 3;
					$item['PrevExamInfo/DisabilityGroup/Value'] = 'Третья группа';
					break;
				case 5:
					$item['PrevExamInfo/DisabilityGroup/ID'] = 4;
					$item['PrevExamInfo/DisabilityGroup/Value'] = 'Категория «ребенок-инвалид»';
					break;
				default:
					$item['PrevExamInfo/DisabilityGroup/ID'] = null;
					$item['PrevExamInfo/DisabilityGroup/Value'] = null;
					break;
			}

			switch ($item['EvnPrescrMse_InvalidPeriod']) {
				case 1:
					$item['PrevExamInfo/DisabilityPeriod/ID'] = 1;
					$item['PrevExamInfo/DisabilityPeriod/Value'] = 'Один год';
					break;
				case 2:
					$item['PrevExamInfo/DisabilityPeriod/ID'] = 2;
					$item['PrevExamInfo/DisabilityPeriod/Value'] = 'Два года';
					break;
				case 3:
					$item['PrevExamInfo/DisabilityPeriod/ID'] = 3;
					$item['PrevExamInfo/DisabilityPeriod/Value'] = 'Три года';
					break;
				default:
					if ($item['EvnPrescrMse_InvalidPeriod'] >= 4) {
						$item['PrevExamInfo/DisabilityPeriod/ID'] = 4;
						$item['PrevExamInfo/DisabilityPeriod/Value'] = 'Четыре и более лет';
					} else {
						$item['PrevExamInfo/DisabilityPeriod/ID'] = null;
						$item['PrevExamInfo/DisabilityPeriod/Value'] = null;
					}
					break;
			}

			switch ($item['LearnGroupType_id']) {
				case 1:
					$item['EducationInfo/LevelType/ID'] = 3;
					$item['EducationInfo/LevelType/Value'] = 'возрастная группа детского дошкольного учреждения';
					break;
				case 2:
					$item['EducationInfo/LevelType/ID'] = 2;
					$item['EducationInfo/LevelType/Value'] = 'класс';
					break;
				case 3:
					$item['EducationInfo/LevelType/ID'] = 1;
					$item['EducationInfo/LevelType/Value'] = 'курс';
					break;
				default:
					$item['EducationInfo/LevelType/ID'] = null;
					$item['EducationInfo/LevelType/Value'] = null;
					break;
			}

			if (!empty($item['AnthropometricData/Height']) && !empty($item['AnthropometricData/Weight']) && $item['AnthropometricData/Height'] !== '###' && $item['AnthropometricData/Weight'] !== '###') {
				$item['AnthropometricData/BMI'] = $item['AnthropometricData/Weight'] / ($item['AnthropometricData/Height'] * $item['AnthropometricData/Height'] / 10000);
			} else {
				$item['AnthropometricData/BMI'] = '###';
			}

			if (!empty($item['PAddress_id'])) {
				$item['AddressType/ID'] = 1;
				$item['AddressType/Value'] = 'Адрес места жительства';
				if (!empty($addressData[$item['PAddress_id']])) {
					$item['Address'] = array($addressData[$item['PAddress_id']]);
				} else {
					$item['Address'] = array(array(
						'ZipCode' => '###',
						'TerritorySubject/ID' => '',
						'TerritorySubject/Value' => '',
						'TerritorySubjectOther' => '',
						'District' => '###',
						'Place' => '',
						'Street' => '',
						'Building' => '###',
						'Flat' => '###'
					));
				}
			} else if (!empty($item['UAddress_id'])) {
				$item['AddressType/ID'] = 5;
				$item['AddressType/Value'] = 'Адрес места постоянной регистрации';
				if (!empty($addressData[$item['UAddress_id']])) {
					$item['Address'] = array($addressData[$item['UAddress_id']]);
				} else {
					$item['Address'] = array(array(
						'ZipCode' => '###',
						'TerritorySubject/ID' => '',
						'TerritorySubject/Value' => '',
						'TerritorySubjectOther' => '',
						'District' => '###',
						'Place' => '',
						'Street' => '',
						'Building' => '###',
						'Flat' => '###'
					));
				}
			} else {
				$item['AddressType/ID'] = null;
				$item['AddressType/Value'] = null;
				$item['Address'] = array(array(
					'ZipCode' => '###',
					'TerritorySubject/ID' => '',
					'TerritorySubject/Value' => '',
					'TerritorySubjectOther' => '',
					'District' => '###',
					'Place' => '',
					'Street' => '',
					'Building' => '###',
					'Flat' => '###'
				));
			}

			switch ($item['EvnPrescrMse_IsPersonInhabitation']) {
				case 1:
					$item['PersonPlace/PlaceType/ID'] = 5;
					$item['PersonPlace/PlaceType/Value'] = 'По месту жительства (по месту пребывания, фактического проживания на территории Российской Федерации)';
					break;
				case 2:
					switch ($item['PlaceType_SysNick']) {
						case 'lpu':
							$item['PersonPlace/PlaceType/ID'] = 1;
							$item['PersonPlace/PlaceType/Value'] = 'В медицинской организация, оказывающая медицинскую помощь в стационарных условиях';
							break;
						case 'socservice':
							$item['PersonPlace/PlaceType/ID'] = 2;
							$item['PersonPlace/PlaceType/Value'] = 'В организации социального обслуживания, оказывающая социальные услуги в стационарной форме социального обслуживания';
							break;
						case 'penitentia':
							$item['PersonPlace/PlaceType/ID'] = 3;
							$item['PersonPlace/PlaceType/Value'] = 'В исправительном учреждении';
							break;
						default:
							$item['PersonPlace/PlaceType/ID'] = 4;
							$item['PersonPlace/PlaceType/Value'] = 'В иной организации';
							break;
					}
					break;
				default:
					$item['PersonPlace/PlaceType/ID'] = null;
					$item['PersonPlace/PlaceType/Value'] = null;
					break;
			}

			if (!empty($item['PlaceAddress_id']) && !empty($addressData[$item['PlaceAddress_id']])) {
				$item['PersonPlace/PlaceAddress'] = array($addressData[$item['PlaceAddress_id']]);
			} else {
				$item['PersonPlace/PlaceAddress'] = array(array(
					'ZipCode' => '###',
					'TerritorySubject/ID' => '',
					'TerritorySubject/Value' => '',
					'TerritorySubjectOther' => '',
					'District' => '###',
					'Place' => '',
					'Street' => '',
					'Building' => '###',
					'Flat' => '###'
				));
			}

			if (!empty($item['EducationAddress_id']) && !empty($addressData[$item['EducationAddress_id']])) {
				$item['EducationInfo/OrgAddress'] = array($addressData[$item['EducationAddress_id']]);
			} else {
				$item['EducationInfo/OrgAddress'] = array(array(
					'ZipCode' => '###',
					'TerritorySubject/ID' => '',
					'TerritorySubject/Value' => '',
					'TerritorySubjectOther' => '',
					'District' => '###',
					'Place' => '',
					'Street' => '',
					'Building' => '###',
					'Flat' => '###'
				));
			}

			if (!empty($item['JobAddress_id']) && !empty($addressData[$item['JobAddress_id']])) {
				$item['ProfInfo/JobAddress'] = array($addressData[$item['JobAddress_id']]);
			} else {
				$item['ProfInfo/JobAddress'] = array();
			}

			if (!empty($item['ReprAddress_id']) && !empty($addressData[$item['ReprAddress_id']])) {
				$item['RepresentativeOrg/Address'] = array($addressData[$item['ReprAddress_id']]);
			} else {
				$item['RepresentativeOrg/Address'] = array(array(
					'ZipCode' => '###',
					'TerritorySubject/ID' => '',
					'TerritorySubject/Value' => '',
					'TerritorySubjectOther' => '',
					'District' => '###',
					'Place' => '',
					'Street' => '',
					'Building' => '###',
					'Flat' => '###'
				));
			}

			if (!empty($item['Person_sid']) && !empty($representerData[$item['Person_sid']])) {
				$item = array_merge($item, $representerData[$item['Person_sid']]);
			} else {
				$item = array_merge($item, $emptyRepresenter);
			}

			if (!empty($item['Person_id']) && !empty($evnStickData[$item['Person_id']])) {
				$item['TempWorkDisabilityItems/Item'] = $evnStickData[$item['Person_id']];
			} else {
				$item['TempWorkDisabilityItems/Item'] = array();
			}

			if (!empty($item['EvnVK_id']) && !empty($evnVKExpertData[$item['EvnVK_id']]['MedCommissionChairman'])) {
				$item['MedCommissionChairman'] = $evnVKExpertData[$item['EvnVK_id']]['MedCommissionChairman'];
			} else {
				$item['MedCommissionChairman'] = '';
			}

			if (!empty($item['EvnVK_id']) && !empty($evnVKExpertData[$item['EvnVK_id']]['MedCommissionMembers'])) {
				$item['MedCommissionMembers'] = $evnVKExpertData[$item['EvnVK_id']]['MedCommissionMembers'];
			} else {
				$item['MedCommissionMembers'] = array();
			}

			if (!empty($item['EvnPrescrMse_id']) && !empty($diagData[$item['EvnPrescrMse_id']]['osntext'])) {
				$item['Diagnosis/MainDeseaseComplications'] = implode(',', $diagData[$item['EvnPrescrMse_id']]['osntext']);
			} else {
				$item['Diagnosis/MainDeseaseComplications'] = 'Осложнения основного заболевания отсутствуют';
			}

			if (!empty($item['EvnPrescrMse_id']) && !empty($diagData[$item['EvnPrescrMse_id']]['soptext'])) {
				$item['Diagnosis/AccompanyingDiseases'] = implode(',', $diagData[$item['EvnPrescrMse_id']]['soptext']);
			} else {
				$item['Diagnosis/AccompanyingDiseases'] = 'Сопутствующие заболевания отсутствуют';
			}

			if (!empty($item['EvnPrescrMse_id']) && !empty($diagData[$item['EvnPrescrMse_id']]['sopcodes'])) {
				$item['Diagnosis/AccompanyingDiseasesCodes'] = $diagData[$item['EvnPrescrMse_id']]['sopcodes'];
			} else {
				$item['Diagnosis/AccompanyingDiseasesCodes'] = array();
			}

			if (!empty($item['EvnPrescrMse_id']) && !empty($diagData[$item['EvnPrescrMse_id']]['oslsoptext'])) {
				$item['Diagnosis/AccompanyingDiseasesComplications'] = implode(',', $diagData[$item['EvnPrescrMse_id']]['oslsoptext']);
			} else {
				$item['Diagnosis/AccompanyingDiseasesComplications'] = 'Осложнения сопутствующих заболеваний отсутствуют';
			}

			if (!empty($item['EvnPrescrMse_id']) && !empty($aimTypeData[$item['EvnPrescrMse_id']])) {
				$item['ExaminationPurposes'] = $aimTypeData[$item['EvnPrescrMse_id']];
			} else {
				$item['ExaminationPurposes'] = array();
			}

			if (!empty($item['EvnPrescrMse_id']) && !empty($requiredMedExamsData[$item['EvnPrescrMse_id']])) {
				$item['RequiredMedExams'] = $requiredMedExamsData[$item['EvnPrescrMse_id']];
			} else {
				$item['RequiredMedExams'] = 'Обследования и исследования не проводились';
			}
		}
		unset($item);

		//Заполнение архива xml-файлами
		$this->load->library('parser');

		$time = time();

		$tpl = 'export_evn_prescr_mse';
		$out_dir = EXPORTPATH_ROOT."evn_prescr_mse";
		$file_zip_path = "{$out_dir}/{$time}.zml";
		$file_zip_errors_path = "{$out_dir}/{$time}_errors.zml";
		$file_error_path = "{$out_dir}/{$time}_errors.txt";

		if (!file_exists($out_dir) && !mkdir($out_dir)) {
			return $this->createError('','Не удалось создать папку для экспортируемых файлов');
		}

		if (!$autoExport) {
			$zip = new ZipArchive();
			$zip->open($file_zip_path, ZIPARCHIVE::CREATE);

			$zip_err = new ZipArchive();
			$zip_err->open($file_zip_errors_path, ZIPARCHIVE::CREATE);
		} else {
			$file_list = [];
		}

		$this->beginTransaction();

		$hasExport = false;
		$hasErrors = false;
		libxml_use_internal_errors(true);
		foreach($EvnPrescrMseList as $EvnPrescrMse) {
			if (empty($EvnPrescrMse['G_Code'])) {
				$EvnPrescrMse['G_Code'] = GUID();

				$resp = $this->updateEvnPrescrMseEaviiasGUID(array(
					'EvnPrescrMse_id' => $EvnPrescrMse['EvnPrescrMse_id'],
					'EvnPrescrMse_EaviiasGUID' => $EvnPrescrMse['G_Code'],
				));
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}

			$file_name = "{$EvnPrescrMse['G_Code']}.xml";
			$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>";

			$EvnPrescrMse['DeseaseAnamnesis'] = html_entity_decode(strip_tags($EvnPrescrMse['DeseaseAnamnesis']));
			$EvnPrescrMse['LifeAnamnesis'] = html_entity_decode(strip_tags($EvnPrescrMse['LifeAnamnesis']));
			$EvnPrescrMse['HealthCondition'] = html_entity_decode(strip_tags($EvnPrescrMse['HealthCondition']));

			array_walk_recursive($EvnPrescrMse, array($this, 'wrapCDATA'));

			$xml .= $this->parser->parse('export_xml/'.$tpl, $EvnPrescrMse, true);
			// header('Content-Type: text/xml; charset=utf-8'); echo $xml;

			// тэги которые могут быть пустыми, но должны присутствовать
			$xml = preg_replace('/<TerritorySubject>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/TerritorySubject>/uis', '<TerritorySubject xsi:nil="true" />', $xml);
			$xml = preg_replace('/<ProfLossPeriod>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/ProfLossPeriod>/uis', '<ProfLossPeriod xsi:nil="true" />', $xml);
			$xml = preg_replace('/<DisabilityReason>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/DisabilityReason>/uis', '<DisabilityReason xsi:nil="true" />', $xml);
			$xml = preg_replace('/<DisabilityPeriod>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/DisabilityPeriod>/uis', '<DisabilityPeriod xsi:nil="true" />', $xml);
			$xml = preg_replace('/<DisabilityGroup>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/DisabilityGroup>/uis', '<DisabilityGroup xsi:nil="true" />', $xml);
			$xml = preg_replace('/<ClinicalPrognosis>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/ClinicalPrognosis>/uis', '<ClinicalPrognosis xsi:nil="true" />', $xml);
			$xml = preg_replace('/<RehabPotential>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/RehabPotential>/uis', '<RehabPotential xsi:nil="true" />', $xml);
			$xml = preg_replace('/<RehabPrognosis>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/RehabPrognosis>/uis', '<RehabPrognosis xsi:nil="true" />', $xml);
			$xml = preg_replace('/<ImpairedFunctionsRecovery>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/ImpairedFunctionsRecovery>/uis', '<ImpairedFunctionsRecovery xsi:nil="true" />', $xml);
			$xml = preg_replace('/<LostFunctionsCompensation>\s*<Id><\/Id>\s*<Value><\/Value>\s*<\/LostFunctionsCompensation>/uis', '<LostFunctionsCompensation xsi:nil="true" />', $xml);
			$xml = preg_replace('/<(\w*)>###<\/\w*>/u', '<$1 xsi:nil="true" />', $xml);
			if (empty($EvnPrescrMse['Diagnosis/AccompanyingDiseasesCodes'])) {
				$xml = preg_replace('/<AccompanyingDiseasesCodes>.*<\/AccompanyingDiseasesCodes>/uis', '<AccompanyingDiseasesCodes xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['TempWorkDisabilityItems/Item'])) {
				$xml = preg_replace('/<TempWorkDisabilityItems>.*<\/TempWorkDisabilityItems>/uis', '<TempWorkDisabilityItems xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['RehabEventsResult/IpraNum'])) {
				$xml = preg_replace('/<RehabEventsResult>.*<\/RehabEventsResult>/uis', '<RehabEventsResult xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['RepresentativeOrg/Ogrn'])) {
				$xml = preg_replace('/<RepresentativeOrg>.*<\/RepresentativeOrg>/uis', '<RepresentativeOrg xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['Representer/LastName'])) {
				$xml = preg_replace('/<Representer>.*<\/Representer>/uis', '<Representer xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['EducationInfo/OrgName'])) {
				$xml = preg_replace('/<EducationInfo>.*<\/EducationInfo>/uis', '<EducationInfo xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['AddressType/ID'])) {
				$xml = preg_replace('/<Address>.*<\/Address>/uis', '<Address xsi:nil="true" />', $xml);
				$xml = preg_replace('/<AddressType>.*<\/AddressType>/uis', '<AddressType xsi:nil="true" />', $xml);
			}
			if (empty($EvnPrescrMse['ProfInfo/JobAddress'])) {
				$xml = preg_replace('/<JobAddress>.*<\/JobAddress>/uis', '<JobAddress xsi:nil="true" />', $xml);
			}

			$filelist = '';
			$attachments = json_decode($EvnPrescrMse['Attachments']);
			if (is_object($attachments) && property_exists($attachments, 'vk')) {
				foreach ($attachments->vk as $vk) {
					$type = substr($vk->name, strripos($vk->name, '.') + 1);
					$filelist .= "\r\n\t\t<Item>\r\n\t\t\t<Name>{$vk->name}</Name>\r\n\t\t\t<Type>{$type}</Type>\r\n\t\t\t<Size>$vk->size</Size>\r\n\t\t</Item>";
				}
			}
			if (is_object($attachments) && property_exists($attachments, 'mse')) {
				foreach ($attachments->mse as $mse) {
					$type = substr($mse->name, strripos($mse->name, '.') + 1);
					$filelist .= "\r\n\t\t<Item>\r\n\t\t\t<Name>{$mse->name}</Name>\r\n\t\t\t<Type>{$type}</Type>\r\n\t\t\t<Size>$mse->size</Size>\r\n\t\t</Item>";
				}
			}
			if (!empty($filelist)) {
				$xml = substr_replace($xml, "\t<Attachments>{$filelist}\r\n\t</Attachments>\r\n</Document>", strripos($xml, "</Document>"));
			}

			// проверим XML по XSD-схеме
			$xmlObject = new DOMDocument();
			$xmlObject->loadXML($xml);
			if (!$xmlObject->schemaValidate($_SERVER['DOCUMENT_ROOT'] . '/documents/xsd/export_mse.xsd')) {
				$errors = libxml_get_errors();

				file_put_contents($file_error_path, $file_name . ', EvnPrescrMse_id: ' . $EvnPrescrMse['EvnPrescrMse_id'] . ', LastName: ' . $EvnPrescrMse['LastName'] . ', FirstName: ' . $EvnPrescrMse['FirstName'] . ', SecondName: ' . $EvnPrescrMse['SecondName'] . ', ошибки валидации по XSD-схеме: ' . PHP_EOL, FILE_APPEND);
				foreach ($errors as $error)
				{
					// Переводим английские ошибки на русский
					$comment = $error->message;
					$comment = str_replace('The value \'\' is not accepted by the pattern \'.*[^\s].*\'', 'Элемент не заполнен, данный элемент обязателен для заполнения', $comment);
					$comment = str_replace('is not accepted by the pattern', 'не удовлетворяет шаблону', $comment);
					$comment = str_replace('error parsing attribute name', 'Ошибка синтаксического анализа названия атрибута', $comment);
					$comment = str_replace('This element is not expected. Expected is one of', 'Указан не верный элемент. Ожидается один из', $comment);
					$comment = str_replace('The value has a length of', 'Значение имеет длинну', $comment);
					$comment = str_replace('Missing child element(s)', 'Пропущен дочерний элемент', $comment);
					$comment = str_replace('Expected is one of', 'Ожидается один из ', $comment);
					$comment = str_replace('has more digits than are allowed', 'Состоит из большего числа знаков чем допустимо', $comment);
					$comment = str_replace('this exceeds the allowed maximum length of', 'Максимальное количество символов', $comment);
					$comment = str_replace('this underruns the allowed minimum length of', 'Минимальное количество символов', $comment);
					$comment = str_replace('is greater than the maximum value allowed', 'больше чем максимально допустимое значение', $comment);
					$comment = str_replace('is not a valid value of the local atomic type', 'тип данных не соответствует определённому в схеме', $comment);
					$comment = str_replace('is not a valid value of the atomic type', 'тип данных не соответствует определённому в схеме', $comment);
					$comment = str_replace('This element is not expected. Expected is one of', 'Указан не верный элемент. Ожидается один из следующих элементов', $comment);
					$comment = str_replace('This element is not expected. Expected is', 'Указан не верный элемент. Ожидается элемент', $comment);
					$comment = str_replace('The value', 'Значение', $comment);
					$comment = str_replace('facet \'pattern\'', 'ограничение схемы', $comment);
					$comment = str_replace('maxLength', 'Максимальная длинна', $comment);
					$comment = str_replace('minLength', 'Минимальная длинна', $comment);
					$comment = str_replace('facet', 'ограничение', $comment);
					$comment = str_replace('Element', 'Элемент', $comment);
					$comment = str_replace('{http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl}', '', $comment);
					$comment = preg_replace('/\bSNILS\b/', 'СНИЛС', $comment);
					$comment = preg_replace('/\bSURNAME\b/', 'Фамилия', $comment);
					$comment = preg_replace('/\bNAME\b/', 'Имя', $comment);
					$comment = preg_replace('/\bLN_CODE\b/', 'Номер ЛН', $comment);
					$comment = preg_replace('/\bLPU_NAME\b/', 'Наименование МО', $comment);
					$comment = preg_replace('/\bREASON1\b/', 'Причина нетрудоспособности', $comment);
					$comment = preg_replace('/\bBIRTHDAY\b/', 'Дата рождения', $comment);
					$comment = preg_replace('/\bGENDER\b/', 'Пол', $comment);
					$comment = preg_replace('/\n/', '', $comment);

					$comment .= " (строка " . $error->line . ")";
					$comment .= PHP_EOL;

					file_put_contents($file_error_path, $comment, FILE_APPEND);
				}

				file_put_contents($file_error_path, PHP_EOL, FILE_APPEND);

				libxml_clear_errors();

				if (!$autoExport) {
					$zip_err->addFromString($file_name, $xml);
				} else {
					$file_path = "{$out_dir}/{$file_name}";
					file_put_contents($file_path, $xml);
					$file_list[] = [
						'EvnPrescrMse_id' => $EvnPrescrMse['EvnPrescrMse_id'],
						'G_Code' => $EvnPrescrMse['G_Code'],
						'path' => $file_path,
						'isError' => true
					];
				}

				$hasErrors = true;
			} else {
				if (!$autoExport) {
					$zip->addFromString($file_name, $xml);
				} else {
					$file_path = "{$out_dir}/{$file_name}";
					file_put_contents($file_path, $xml);
					$file_list[] = [
						'EvnPrescrMse_id' => $EvnPrescrMse['EvnPrescrMse_id'],
						'G_Code' => $EvnPrescrMse['G_Code'],
						'path' => $file_path,
						'isError' => false
					];
				}

				$hasExport = true;
			}
		}
		// die();

		if (!$autoExport) {
			$zip->close();
			$zip_err->close();
		}

		$this->commitTransaction();
		
		if ($autoExport) {
			return $file_list;
		}

		return array(array(
			'success' => true,
			'link' => $hasExport ? $file_zip_path : '',
			'errorlink' => $hasErrors ? $file_zip_errors_path : '',
			'commentlink' => $hasErrors ? $file_error_path : ''
		));
	}

	/**
	 * Оборачиваем в CDATA при необходимости
	 */
	function wrapCDATA(&$var) {
		if (strpos($var, '>') > 0 || strpos($var, '<') > 0) {
			$var = "<![CDATA[" . $var . "]]>";
		}
	}

	/**
	 * Печать обратного талон
	 */
	function printEvnMse($data) {
		$this->load->library('parser');
		$view = 'evn_mse_blank';
		$response = $this->getEvnMseForPrint($data);
		if (is_array($response) && count($response) == 1) {
			$response[0] = str_replace('[br]', '<br>', $response[0]);
			$response[0]['isMseDepers'] = (isset($data['isMseDepers']) && $data['isMseDepers'] == 1);
			$html = $this->parser->parse($view, $response[0], !empty($data['returnString']));
			if (!empty($data['returnString'])) {
				return array('html' => $html);
			} else {
				return array('Error_Msg' => '');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных обратного талона');
		}
	}

	/**
	 * Проверка прав на подписание направления на МСЭ
	 */
	function checkSignAccess($data) {
		// #193894 Выполняется проверка наличия у пациента минимум трёх заполненных витальных параметров из перечисленных: «Масса тела», «Длина тела», «Индекс массы тела», «Суточный объём физиологических отправлений», «Окружность талии», «Окружность бёдер».
		// Если параметров меньше трёх, то выводится ошибка: «У пациента {ФИО} недостаточно данных в разделе «Антропометрические данные и физиологические параметры». Необходимо заполнить минимум три из перечисленных параметров: «Масса тела», «Длина тела», «Индекс массы тела», «Суточный объём физиологических отправлений», «Окружность талии», «Окружность бёдер». Документ не может быть подписан, отправка в РЭМД невозможна.» ОК. При нажатии на кнопку «ОК» или закрытии сообщения дальнейшие действия не выполняются.
		$resp_epm = $this->queryResult("
			select
				epm.EvnPrescrMse_id,
				ps.Person_SurName + isnull(' ' + ps.Person_FirName,'') + isnull(' ' + ps.Person_SecName,'') as Person_Fio,
				pw.PersonWeight_Weight,
				ph.PersonHeight_Height,
				epm.EvnPrescrMse_DailyPhysicDepartures,
				epm.EvnPrescrMse_Waist,
				epm.EvnPrescrMse_Hips,
				epm.EvnPrescrMse_IsFirstTime,
				epm.EvnMse_id
			from
				v_EvnPrescrMse epm (nolock)
				left join v_PersonState ps (nolock) on ps.Person_id = epm.Person_id
				left join v_PersonWeight pw with (nolock) on pw.PersonWeight_id = epm.PersonWeight_id
				left join v_PersonHeight ph with (nolock) on ph.PersonHeight_id = epm.PersonHeight_id 
			where
				epm.EvnPrescrMse_id = :EvnPrescrMse_id
		", [
			'EvnPrescrMse_id' => $data['EvnPrescrMse_id']
		]);

		if (empty($resp_epm[0]['EvnPrescrMse_id'])) {
			throw new Exception('Подписание невозможно, т.к. не найдено подписываемое направление на МСЭ');
		}

		$vitalCount = 0;
		if (!empty($resp_epm[0]['PersonWeight_Weight'])) {
			$vitalCount++; // вес
		}
		if (!empty($resp_epm[0]['PersonHeight_Height'])) {
			if (!empty($resp_epm[0]['PersonWeight_Weight'])) {
				$vitalCount++; // можно посчитать и ИМТ
			}
			$vitalCount++; // рост
		}
		if (!empty($resp_epm[0]['EvnPrescrMse_DailyPhysicDepartures'])) {
			$vitalCount++; // суточный объём физиологических отправлений
		}
		if (!empty($resp_epm[0]['EvnPrescrMse_Waist'])) {
			$vitalCount++; // окружность талии
		}
		if (!empty($resp_epm[0]['EvnPrescrMse_Hips'])) {
			$vitalCount++; // окружность бёдер
		}
		if ($vitalCount < 3) {
			throw new Exception('У пациента ' . $resp_epm[0]['Person_Fio'] . ' недостаточно данных в разделе «Антропометрические данные и физиологические параметры». Необходимо заполнить минимум три из перечисленных параметров: «Масса тела», «Длина тела», «Индекс массы тела», «Суточный объём физиологических отправлений», «Окружность талии», «Окружность бёдер». Документ не может быть подписан, отправка в РЭМД невозможна.');
		}

		if ($resp_epm[0]['EvnPrescrMse_IsFirstTime'] == 2 && empty($resp_epm[0]['EvnMse_id'])) {
			throw new Exception('В повторном направлении на МСЭ должно быть заполнено поле «Обратный талон МСЭ». Документ не может быть подписан, отправка в РЭМД невозможна.');
		}

		return true;
	}

	/**
	 * Печать направления на МСЭ
	 */
	function printEvnPrescrMse($data) {
		$val = array();
		$this->load->library('parser');
		$view = 'evn_prescr_mse_blank';

		if (getRegionNick() == 'perm')
			$view = $view.'_perm';
		$response = $this->getEvnPrescrMseForPrint($data);
		if(is_array($response) && count($response) == 1){
			// ЛВН-ки
			$val = $response[0];
			$val['sticks'] = array();
			$sticks = $this->getEvnStickOfYear($response[0]);
			if(is_array($sticks) && count($sticks) > 0) {
				for($i=0; $i<count($sticks); $i++){
					$sticks[$i]['number'] = $i+1;
				}
				$val['sticks'] = $sticks;
			}
			$val['vkchairman'] = '';
			$val['vkexperts'] = array();
			if( !empty($val['EvnVK_id']) ) {
				$this->load->model('ClinExWork_model', 'cew_model');
				$val['Lpu_id'] = $data['session']['lpu_id'];
				$medpersonals = $this->cew_model->getEvnVKExpert($val);
				unset($val['Lpu_id']);
				if(is_array($medpersonals) && count($medpersonals) > 0) {
					foreach( $medpersonals as $m ) {
						if( $m['ExpertMedStaffType_id'] == 1 ) { // Председатель ВК
							$val['vkchairman'] = $m['MF_Person_FIO'];
						} else {
							$val['vkexperts'][] = array('MF_Person_FIO' => $m['MF_Person_FIO']);
						}
					}
				}
			}
			$html = $this->parser->parse($view, $val, !empty($data['returnString']));
			if (!empty($data['returnString'])) {
				return array('html' => $html);
			} else {
				return array('Error_Msg' => '');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных направления на МСЭ');
		}
	}
	
	/**
	 *	Method description
	 */
	function searchUslugaComplexMSE($data)
	{
		$filter = '';
		$join = '';
		$params = array();
		$params['Person_id'] = $data['Person_id'];
		$params['Diag_id'] = $data['Diag_id'];
		$params['EvnPrescrMse_IsFirstTime'] = $data['EvnPrescrMse_IsFirstTime'];
		
		if (is_array($data['EvnUsluga_DateRange']) && count($data['EvnUsluga_DateRange']) == 2 && !empty($data['EvnUsluga_DateRange'][0])) {
			$filter .= ' and cast(eu.EvnUsluga_setDate as date) between :EvnUsluga_DateRangeStart and :EvnUsluga_DateRangeEnd ';
			$params['EvnUsluga_DateRangeStart'] = $data['EvnUsluga_DateRange'][0];
			$params['EvnUsluga_DateRangeEnd'] = $data['EvnUsluga_DateRange'][1];
		}
		
		$join .=  'outer apply (
			select top 1 
				ucmdl.UslugaComplexMSEDiagLink_id,
				ucmdl.UslugaComplexMSEDiagLink_PeriodF,
				ucmdl.UslugaComplexMSEDiagLink_PeriodS,
				ucmdl.UslugaComplex_id
			from UslugaComplexMSEDiagLink ucmdl (nolock)
			left join UslugaComplexReplacementLink ucrl (nolock) on ucrl.UslugaComplexMSEDiagLink_id = ucmdl.UslugaComplexMSEDiagLink_id
			where ucmdl.Diag_id = :Diag_id and (
				ucmdl.UslugaComplex_id = eu.UslugaComplex_id or
				ucrl.UslugaComplex_id = eu.UslugaComplex_id
			)
		) ucmdl ';
		
		if ($data['RecommendedOnly'] && !empty($data['Diag_id'])) {
			$filter .= ' and ucmdl.UslugaComplexMSEDiagLink_id is not null ';
		}
		
		if (!$data['AllDiag'] && !empty($data['Diag_id'])) {
			$filter .= ' and coalesce(eu.Diag_id, evpl.Diag_id, es.Diag_id) = :Diag_id ';
		}
		
		if (!empty($data['UslugaComplex_id'])) {
			$filter .= ' and eu.UslugaComplex_id = :UslugaComplex_id ';
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		
		$query = "
			declare @curDT datetime = dbo.tzGetDate();
			
			with t as (
				select
					eu.EvnUsluga_id,
					eu.EvnClass_SysNick,
					evn.EvnClass_SysNick as ParentClass_SysNick,
					eu.Person_id,
					convert(varchar(10), eu.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
					uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name as UslugaComplex_Name,
					case 
						when 
							:EvnPrescrMse_IsFirstTime = 1 and 
							ucmdl.UslugaComplexMSEDiagLink_PeriodF is not null and 
							dateadd(month, cast(ucmdl.UslugaComplexMSEDiagLink_PeriodF as int), eu.EvnUsluga_setDate) < @curDT
							then 'false'
						when 
							:EvnPrescrMse_IsFirstTime = 2 and 
							ucmdl.UslugaComplexMSEDiagLink_PeriodS is not null and 
							dateadd(month, cast(ucmdl.UslugaComplexMSEDiagLink_PeriodS as int), eu.EvnUsluga_setDate) < @curDT
							then 'false'
						else 'true'
					end as EvnUsluga_isActual,
					ucmdl.UslugaComplexMSEDiagLink_id,
					case when uc.UslugaComplex_id = ucmdl.UslugaComplex_id then 1 else 0 end as isMain
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on eu.UslugaComplex_id = uc.UslugaComplex_id
					left join v_Evn evn (nolock) on evn.Evn_id = eu.EvnUsluga_pid
					left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = eu.EvnUsluga_pid
					left join v_EvnSection es (nolock) on es.EvnSection_id = eu.EvnUsluga_pid
					{$join}
				where
					eu.Person_id = :Person_id and
					eu.EvnUsluga_setDate is not null
					{$filter}
			)

			select * from t 
		";
		
		if ($data['RecommendedOnly'] && !empty($data['Diag_id'])) {
			$query .= ' where isMain = 1 or not exists (
				select top 1 tmp.EvnUsluga_id from t tmp where t.UslugaComplexMSEDiagLink_id = tmp.UslugaComplexMSEDiagLink_id and tmp.isMain = 1
			) ';
		}
		
		//echo getDebugSql($query, $params); die;
		return $this->queryResult($query, $params);
	}
	
	/**
	 *	Method description
	 */
	function getUslugaComplexMSERecommended($data)
	{		
		$query = "
			declare @curDT datetime = dbo.tzGetDate();
			
			with t as (
				select
					eu.EvnUsluga_id,
					eu.EvnClass_SysNick,
					evn.EvnClass_SysNick as ParentClass_SysNick,
					eu.Person_id,
					convert(varchar(10), eu.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name as UslugaComplex_Name,
					case 
						when 
							:EvnPrescrMse_IsFirstTime = 1 and 
							ucmdl.UslugaComplexMSEDiagLink_PeriodF is not null and 
							dateadd(month, cast(ucmdl.UslugaComplexMSEDiagLink_PeriodF as int), eu.EvnUsluga_setDate) < @curDT
							then 'false'
						when 
							:EvnPrescrMse_IsFirstTime = 2 and 
							ucmdl.UslugaComplexMSEDiagLink_PeriodS is not null and 
							dateadd(month, cast(ucmdl.UslugaComplexMSEDiagLink_PeriodS as int), eu.EvnUsluga_setDate) < @curDT
							then 'false'
						else 'true'
					end as EvnUsluga_isActual,
					ucmdl.UslugaComplexMSEDiagLink_id,
					case when uc.UslugaComplex_id = ucmdl.UslugaComplex_id then 1 else 0 end as isMain
				from
					UslugaComplexMSEDiagLink ucmdl (nolock)
					left join UslugaComplexReplacementLink ucrl (nolock) on ucrl.UslugaComplexMSEDiagLink_id = ucmdl.UslugaComplexMSEDiagLink_id
					cross apply (
						select top 1 eu.* 
						from v_EvnUsluga eu (nolock)
						where 
							eu.Person_id = :Person_id and
							eu.EvnUsluga_setDate is not null and
							(eu.UslugaComplex_id = ucmdl.UslugaComplex_id or eu.UslugaComplex_id = ucrl.UslugaComplex_id)
						order by 
							case when ucrl.UslugaComplexReplacementLink_id is null then 1 else 2 end,
							eu.EvnUsluga_setDate desc
					) eu
					inner join v_UslugaComplex uc (nolock) on eu.UslugaComplex_id = uc.UslugaComplex_id
					left join v_Evn evn (nolock) on evn.Evn_id = eu.EvnUsluga_pid
				where
					ucmdl.Diag_id = :Diag_id
			)

			select 
				EvnUsluga_id,
				EvnClass_SysNick,
				ParentClass_SysNick,
				Person_id,
				EvnUsluga_setDate,
				UslugaComplex_id,
				UslugaComplex_Name,
				EvnUsluga_isActual
			from t where isMain = 1 or not exists (
				select top 1 tmp.EvnUsluga_id from t tmp where t.UslugaComplexMSEDiagLink_id = tmp.UslugaComplexMSEDiagLink_id and tmp.isMain = 1
			) group by 
				EvnUsluga_id,
				EvnClass_SysNick,
				ParentClass_SysNick,
				Person_id,
				EvnUsluga_setDate,
				UslugaComplex_id,
				UslugaComplex_Name,
				EvnUsluga_isActual
		";
		
		//echo getDebugSql($query, $data); die;
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Method description
	 */
	function loadUslugaComplexMSEList($data)
	{		
		$query = "
			select
				epml.EvnPrescrMseLink_id,
				1 as RecordStatus_Code,
				eu.EvnUsluga_id,
				eu.EvnClass_SysNick,
				evn.EvnClass_SysNick as ParentClass_SysNick,
				eu.Person_id,
				convert(varchar(10), eu.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
				eu.UslugaComplex_id,
				uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name as UslugaComplex_Name,
				eua.EvnUsluga_isActual
			from
				EvnPrescrMseLink epml (nolock)
				inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_id = epml.EvnUsluga_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
				left join v_Evn evn (nolock) on evn.Evn_id = eu.EvnUsluga_pid
				outer apply (
					select top 1 case 
						when 
							epm.EvnPrescrMse_IsFirstTime = 1 and 
							ucmdl.UslugaComplexMSEDiagLink_PeriodF is not null and 
							dateadd(month, cast(ucmdl.UslugaComplexMSEDiagLink_PeriodF as int), eu.EvnUsluga_setDate) < epm.EvnPrescrMse_setDate 
							then 'false'
						when 
							epm.EvnPrescrMse_IsFirstTime = 2 and 
							ucmdl.UslugaComplexMSEDiagLink_PeriodS is not null and 
							dateadd(month, cast(ucmdl.UslugaComplexMSEDiagLink_PeriodS as int), eu.EvnUsluga_setDate) < epm.EvnPrescrMse_setDate 
							then 'false'
						else 'true'
					end as EvnUsluga_isActual
					from v_EvnPrescrMse epm (nolock)
					inner join UslugaComplexMSEDiagLink ucmdl (nolock) on ucmdl.UslugaComplex_id = eu.UslugaComplex_id and ucmdl.Diag_id = epm.Diag_id
					where epm.EvnPrescrMse_id = :EvnPrescrMse_id
				) eua
			where
				epml.EvnPrescrMse_id = :EvnPrescrMse_id
		";
		
		//echo getDebugSql($query, $data); die;
		return $this->queryResult($query, $data);
	}

	/**
	 * Получение целей МСЭ
	*/
	function loadMultiplePrescrAims($data)
	{
		$query = "
			select
				mdat.MseDirectionAimType_Code as Code,
				MDAT.MseDirectionAimType_Name as Name,
				case when mdat.MseDirectionAimType_Code = 14 then EPM.EvnPrescrMse_AimMseOver else null end as AimText
			from
				v_MseDirectionAimTypeLink mse with(nolock)
				left join v_MseDirectionAimType MDAT with(nolock) on mse.MseDirectionAimType_id = MDAT.MseDirectionAimType_id
				left join v_EvnPrescrMse EPM with(nolock) on mse.EvnPrescrMse_id = EPM.EvnPrescrMse_id
			where
				mse.EvnPrescrMse_id = :EvnPrescrMse_id
			order by mse.MseDirectionAimType_id";

		$result = $this->db->query($query, $data);
		return $result->result('array');
	}

	/**
	 * добавление множественных целей
	*/
	function saveMultipleAims($data, $codes)
	{
			$query = "
				select
    				MseDirectionAimType_id as id,
    				MseDirectionAimType_Code as code
				from
   					v_MseDirectionAimType MDAT
				where
        			MDAT.MseDirectionAimType_Code in (".implode(',',$codes).")
			";
		$codes=$this->db->query($query)->result_array();
		//если есть цель "для другого", то установить её
		foreach ($codes as $code){
			if ($code['code']==14) {
				$query = "
					update v_EvnPrescrMse
					set MseDirectionAimType_id = :MseDirectionAimType_id
					where EvnPrescrMse_id = :EvnPrescrMse_id
				";
				$this->db->query($query, array(
					'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
					'MseDirectionAimType_id' => $code['id']
				));
			}
		}

		$query = "
			select
				MseDirectionAimTypeLink_id
		  	from 
				v_MseDirectionAimTypeLink with (nolock)
			where
				EvnPrescrMse_id = :EvnPrescrMse_id
			order by MseDirectionAimType_id
			";
		$aims = $this->db->query($query, $data);
		$aims = $aims->result('array');
		//удаление существующих целей вместо перезаписи
		if (!empty($aims[0]['MseDirectionAimTypeLink_id'])) {
			$query = "
				delete from v_MseDirectionAimTypeLink with (rowlock)
				where EvnPrescrMse_id = :EvnPrescrMse_id";
			$this->db->query($query, $data);
		}

		$values = [];
		foreach ($codes as $code) {
			$values[] = "({$data['EvnPrescrMse_id']}, {$code['id']}, {$data['pmUser_id']}, {$data['pmUser_id']}, dbo.tzGetDate(), dbo.tzGetDate())";
		}

		$query = "
			insert into v_MseDirectionAimTypeLink with (rowlock)
				(EvnPrescrMse_id, MseDirectionAimType_id, pmUser_insID, pmUser_updID, MseDirectionAimTypeLink_insDT, MseDirectionAimTypeLink_updDT)
				values" . implode(',
				', $values);

		$this->db->query($query);
	}

	/**
	 * Получение данных по регистру ИПРА
	*/
	function getIPRAData($data)
	{
		$query = "
			select top 1
				IR.IPRARegistry_id,
				IR.IPRARegistry_Number,
				IR.IPRARegistry_Protocol,
				convert(varchar(10), IR.IPRARegistry_ProtocolDate, 104) as IPRARegistry_ProtocolDate
			from v_IPRARegistry IR (nolock)
			/*inner join v_PersonRegister PR with (nolock) on PR.Person_id = IR.Person_id and PR.MorbusType_id = 90*/ -- непонятно, нужно ли это
			where IR.Person_id = :Person_id
			order by 
				IR.IPRARegistry_issueDate desc
		";
		return $this->queryResult($query, $data);		
	}

	/**
	 * Список обратных талонов по человеку
	*/
	function getPrevEvnMseList($data)
	{
		$query = "
			select 
				EM.EvnMse_id
				,EM.EvnMse_NumAct
				,cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), EM.EvnMse_SendStickDate, 104) as EvnMse_Name
				,convert(varchar(10), EM.EvnMse_SendStickDate, 104) as EvnMse_SendStickDate
				,convert(varchar(10), EM.EvnMse_ReExamDate, 104) as EvnMse_ReExamDate
				,EM.InvalidGroupType_id
				,EM.InvalidCouseType_id
				,PEVK.PalliatEvnVK_IsPMP
				,EM.ProfDisabilityPeriod_id
				,convert(varchar(10), EM.EvnMse_ProfDisabilityEndDate, 104) as EvnMse_ProfDisabilityEndDate
				,HA.HealthAbnorm_Name
				,EM.EvnMse_InvalidPercent
			from v_EvnMse EM (nolock)
			left join v_EvnVK EVK (nolock) on EVK.EvnVK_id = EM.EvnVK_id
			left join PalliatEvnVK PEVK (nolock) on PEVK.EvnVK_id = EVK.EvnVK_id	
			left join v_HealthAbnorm HA (nolock) on HA.HealthAbnorm_id = EM.HealthAbnorm_id	
			where EM.Person_id = :Person_id
			order by 
				EM.EvnMse_SendStickDate desc
		";
		return $this->queryResult($query, $data);		
	}

	/**
	 * Проверка наличия документа у человека
	*/
	function checkPersonDocument($data)
	{
		$query = "
			select top 1 d.Document_id
			from v_PersonState ps (nolock)
			inner join v_Document d (nolock) on d.Document_id = ps.Document_id
			inner join DocumentTypeMinLab dtml (nolock) on dtml.DocumentType_id = d.DocumentType_id
			where ps.Person_id = :Person_id
		";
		return $this->queryResult($query, $data);
	}
	
	/**
	 *	Сохранение адреса
	 *	возможные варианты: 
	 *	1. Удаление адреса   - если Address_id not null and другие поля пустые 
	 *	2. Добавление адреса - если Address_id null and другие поля заполнены		
	 */
	function SaveAddress($data, $prefix) {
		$Address_id = isset($data[$prefix.'Address_id']) && $data[$prefix.'Address_id'] > 0 ? $data[$prefix.'Address_id'] : 0;
		
		// создаем или редактируем адрес
		// Если строка адреса не пустая
		if (isset($data[$prefix.'Address_Address']) && $data[$prefix.'Address_Address'] != '') {
			// не было адреса
			if ($Address_id <= 0) {
				$sql = "
					declare
						@Address_id bigint = null,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Address_ins
						@Server_id = :Server_id,
						@Address_id = @Address_id output,
						@KLAreaType_id = Null, -- опреляется логикой в хранимке
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRGN_id,
						@KLSubRgn_id = :KLSubRGN_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Message
				";
				$res = $this->db->query($sql, array(
					'Server_id' => $data['Server_id'],
					'KLCountry_id' => $data[$prefix.'KLCountry_id'],
					'KLRGN_id' => $data[$prefix.'KLRGN_id'],
					'KLSubRGN_id' => $data[$prefix.'KLSubRGN_id'],
					'KLCity_id' => $data[$prefix.'KLCity_id'],
					'KLTown_id' => $data[$prefix.'KLTown_id'],
					'KLStreet_id' => $data[$prefix.'KLStreet_id'],
					'Address_Zip' => $data[$prefix.'Address_Zip'],
					'Address_House' => $data[$prefix.'Address_House'],
					'Address_Corpus' => $data[$prefix.'Address_Corpus'],
					'Address_Flat' => $data[$prefix.'Address_Flat'],
					'Address_Address' => $data[$prefix.'Address_Address'],
					'pmUser_id' => $data['pmUser_id']
				));
				
				if (is_object($res)) {
					$sel = $res->result('array');
					if ( $sel[0]['Error_Code'] == '' ) {
						$Address_id = $sel[0]['Address_id'];
					} else
						return 0;
				} else
					return 0;
			} else { // обновляем адрес
				$sql = "
					declare
						@Address_id bigint = :Address_id,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Address_upd
						@Server_id = :Server_id,
						@Address_id = @Address_id output,
						@KLAreaType_id = NULL, -- опреляется логикой в хранимке
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRGN_id,
						@KLSubRgn_id = :KLSubRGN_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Message
				";
				$res = $this->db->query($sql, array(
					'Server_id' => $data['Server_id'],
					'Address_id' => $data[$prefix.'Address_id'],
					'KLCountry_id' => $data[$prefix.'KLCountry_id'],
					'KLRGN_id' => $data[$prefix.'KLRGN_id'],
					'KLSubRGN_id' => $data[$prefix.'KLSubRGN_id'],
					'KLCity_id' => $data[$prefix.'KLCity_id'],
					'KLTown_id' => $data[$prefix.'KLTown_id'],
					'KLStreet_id' => $data[$prefix.'KLStreet_id'],
					'Address_Zip' => $data[$prefix.'Address_Zip'],
					'Address_House' => $data[$prefix.'Address_House'],
					'Address_Corpus' => $data[$prefix.'Address_Corpus'],
					'Address_Flat' => $data[$prefix.'Address_Flat'],
					'Address_Address' => $data[$prefix.'Address_Address'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (is_object($res)) {
					$sel = $res->result('array');
					if ( $sel[0]['Error_Code'] == '' ) {
						$Address_id = $sel[0]['Address_id'];
					} else
						return 0;
				} else
					return 0;
			}
		}
		
		return $Address_id;
	}

	/**
	 * Импорт обратных талонов
	 */
	function importEvnMse($data)
	{
		$upload_path = './'.IMPORTPATH_ROOT.'importEvnMse/';
		
		if (!isset($_FILES['EvnMseFile'])) {
			return array('Error_Msg' => 'Не выбран файл');
		}

		if (!is_uploaded_file($_FILES['EvnMseFile']['tmp_name'])) {
			$error = (!isset($_FILES['EvnMseFile']['error'])) ? 4 : $_FILES['EvnMseFile']['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			return array('Error_Msg' => $message);
		}

		$x = explode('.', $_FILES['EvnMseFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), array('xml'))) {
			return array('Error_Msg' => 'Данный тип файла не разрешен');
		}
		
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			if ($folders[$i] == '') {continue;}
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		
		if (!@is_dir($upload_path)) {
			return array('Error_Msg' => 'Путь для загрузки файлов некорректен.');
		}

		if (!is_writable($upload_path)) {
			return array('Error_Msg' => 'Загрузка файла не возможна из-за прав пользователя.');
		}
		
		$xmlfile = time().$_FILES['EvnMseFile']['name'];
		
		if (!move_uploaded_file($_FILES["EvnMseFile"]["tmp_name"], $upload_path.$xmlfile)){
			return array('Error_Msg' => 'Не удаётся переместить файл.');
		}
		
		return $this->_importEvnMse($upload_path.$xmlfile);
	}
	
	/**
	 * Импорт обратных талонов
	 */
	function _importEvnMse($filepath)
	{
		$xml = simplexml_load_file($filepath);
		$tLpuTicket = $xml->DocContent->tLpuTicket;

		if (!preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", (string)$xml->Meta->Id)) {
			return array('Error_Msg' => 'Неверный формат GUID');
		}
		
		if (!empty($data['Lpu_oid'])) {
			$lpu_data = $this->getFirstRowFromQuery("select Lpu_Name, Lpu_Nick, Lpu_OGRN from v_Lpu (nolock) where Lpu_id = ?", [$data['Lpu_oid']]);
			if (
				$lpu_data['Lpu_Name'] != trim((string)$tLpuTicket->SentOrgName) &&
				$lpu_data['Lpu_Nick'] != trim((string)$tLpuTicket->SentOrgName) &&
				$lpu_data['Lpu_OGRN'] != trim((string)$tLpuTicket->SentOrgOgrn)
			) {
				return array('Error_Msg' => 'Талон не относится к МО загрузки');
			}
		}

		$ms_data = $this->getFirstRowFromQuery("
			select top 1 ms.MedService_id, ms.Lpu_id 
			from v_MedService ms (nolock)
			inner join v_MseOffice mo (nolock) on mo.MseOffice_id = ms.MseOffice_id
			where mo.MseOffice_Code = :BuroId
		", [
			'BuroId' => trim((string)$xml->Meta->BuroId)
		]);
		if (!$ms_data) {
			return array('Error_Msg' => 'В Системе отсутствует служба МСЭ с Кодом ЕАВИИАС «'.trim((string)$xml->Meta->BuroId).'», указанным в Обратном талоне');
		}

		$personData = $this->identificatePerson($tLpuTicket);
		
		if ($personData === false) {
			return array('Error_Msg' => 'Пациент не идентифицирован в Промед');
		}
		
		$evnmse_data = $this->getFirstResultFromQuery("select EvnMse_id from v_EvnMse (nolock) where EvnMse_ImportedCouponGUID = ?", [(string)$xml->Meta->Id]);
		if ($evnmse_data != false) {
			return array('Error_Msg' => 'Обратный талон импортирован ранее');
		}

		$evnmse_data = $this->getFirstResultFromQuery("select EvnMse_id from v_EvnMse (nolock) where EvnMse_setDT = ? and Person_id = ?", [(string)$tLpuTicket->ExamDate, $personData['Person_id']]);
		if ($evnmse_data != false) {
			return array('Error_Msg' => 'Обратный талон импортирован ранее');
		}

		$EvnPrescrMse_id = $this->getFirstResultFromQuery("select top 1 EvnPrescrMse_id from v_EvnPrescrMse (nolock) where Person_id = ? and EvnStatus_id != 31 order by EvnPrescrMse_setDate desc", [$personData['Person_id']]);

		$LifeDysfunctions = $this->identificateLifeDysfunctions($tLpuTicket->LifeDysfunctions);
		
		$LifeRestrictions = $this->identificateLifeRestrictions($tLpuTicket->LifeRestrictions);
		
		$res = array(
			'EvnMse_id' => null,
			'EvnMse_ImportedCouponGUID' => $xml->Meta->Id,
			'EvnMse_NumAct' => $tLpuTicket->ActNum,
			'EvnPrescrMse_id' => $EvnPrescrMse_id,
			'Server_id' => $personData['Server_id'],
			'Person_id' => $personData['Person_id'],
			'PersonEvn_id' => $personData['PersonEvn_id'],
			'Diag_id' => $this->identificateDiag($tLpuTicket->MainDeseaseMKBCode),
			'EvnMse_DiagDetail' => $tLpuTicket->MainDeseaseDesc,
			'EvnMse_InvalidCause' => $tLpuTicket->DisabilityCauseOther,
			'EvnMse_setDT' => $tLpuTicket->ExamDate,
			'EvnMse_ReExamDate' => $tLpuTicket->ReExamDate,
			'EvnMse_MedRecomm' => $tLpuTicket->MedRecommendations,
			'EvnMse_ProfRecomm' => $tLpuTicket->OtherRecommendations,
			'InvalidRefuseType_id' => $this->identificateInvalidRefuseType($tLpuTicket->NoDisabilityReasons),
			'EvnMse_SendStickDate' => $tLpuTicket->IssueDate,
			'InvalidGroupType_id' => $tLpuTicket->DisabilityGroup->Id,
			'InvalidCouseType_id' => $tLpuTicket->DisabilityCause->Id,
			'EvnMse_HeadStaffMse' => $tLpuTicket->FIOHead->LastName .' '. $tLpuTicket->FIOHead->FirstName .' '. $tLpuTicket->FIOHead->SecondName,
			'EvnMse_InvalidPercent' => $tLpuTicket->UptDegrees->Degree->Value,
			'ProfDisabilityPeriod_id' => $tLpuTicket->UptDegrees->Degree->IsPermanent == true ? 4 : null,
			'EvnMse_ProfDisabilityStartDate' => $tLpuTicket->UptDegrees->Degree->StartDate,
			'EvnMse_ProfDisabilityEndDate' => $tLpuTicket->UptDegrees->Degree->EndDate,
			'HealthAbnorm_id' => $LifeDysfunctions['HealthAbnorm_id'],
			'HealthAbnormDegree_id' => $LifeDysfunctions['HealthAbnormDegree_id'],
			'Lpu_id' => $ms_data['Lpu_id'],
			'MedService_id' => $ms_data['MedService_id'],
			'SatelliteDeseaseDesc' => $tLpuTicket->SatelliteDeseaseDesc,
			'MainDeseaseComplications' => $tLpuTicket->MainDeseaseComplications,
			'EvnMse_DiagBDetail' => $tLpuTicket->SatelliteDeseaseComplications,
			'pmUser_id' => $data['pmUser_id']
		);
		
		foreach($res as &$r) {
			if ($r === false) $r = null;
			elseif (!empty($r)) $r = ((string)$r);
			elseif (is_object($r) && empty($r)) $r = null;
		}
		
		$res['SatelliteDesease'] = $this->identificateSatelliteDesease($tLpuTicket->SatelliteDeseaseMKBCodes);
		$res['LifeRestrictions'] = $LifeRestrictions;

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_EvnMse_ins
				@EvnMse_id = @Res output,
				@EvnMse_ImportedCouponGUID = :EvnMse_ImportedCouponGUID,
				@MedService_id = :MedService_id,
				@EvnMse_pid = null,
				@EvnMse_rid = null,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnMse_setDT = :EvnMse_setDT,
				@EvnMse_disDT = null,
				@EvnMse_didDT = null,
				@EvnPrescrMse_id = :EvnPrescrMse_id,
				@EvnVK_id = null,
				@EvnMse_NumAct = :EvnMse_NumAct,
				@Diag_id = :Diag_id,
				@Diag_sid = null,
				@Diag_aid = null,
				@HealthAbnorm_id = :HealthAbnorm_id,
				@HealthAbnormDegree_id = :HealthAbnormDegree_id,
				@CategoryLifeType_id = null,
				@CategoryLifeDegreeType_id = null,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@InvalidCouseType_id = :InvalidCouseType_id,
				@EvnMse_InvalidPercent = :EvnMse_InvalidPercent,
				@ProfDisabilityPeriod_id = :ProfDisabilityPeriod_id,
				@EvnMse_ProfDisabilityStartDate = :EvnMse_ProfDisabilityStartDate,
				@EvnMse_ProfDisabilityEndDate = :EvnMse_ProfDisabilityEndDate,
				@EvnMse_ReExamDate = :EvnMse_ReExamDate,
				@InvalidRefuseType_id = :InvalidRefuseType_id,
				@EvnMse_SendStickDate = :EvnMse_SendStickDate,
				@EvnMse_HeadStaffMse = :EvnMse_HeadStaffMse,
				@MedServiceMedPersonal_id = null,
				@EvnMse_MedRecomm = :EvnMse_MedRecomm,
				@EvnMse_ProfRecomm = :EvnMse_ProfRecomm,
				@EvnMse_DiagDetail = :EvnMse_DiagDetail,
				@EvnMse_DiagSDetail = null,
				@EvnMse_DiagADetail = null,
				@Diag_bid = null,
				@EvnMse_DiagBDetail = :EvnMse_DiagBDetail,
				@EvnMse_SendStickDetail = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnMse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->queryResult($query, $res);

		if(empty($result[0]['EvnMse_id'])) {
			return $result;
		}

		$data['EvnMse_id'] = $result[0]['EvnMse_id'];

		if ($EvnPrescrMse_id != false) {
			$this->load->model('Evn_model', 'Evn_model');
			$this->Evn_model->updateEvnStatus([
				'Evn_id' => $EvnPrescrMse_id,
				'EvnStatus_id' => 31,
				'EvnClass_SysNick' => 'EvnPrescrMse',
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		foreach($res['LifeRestrictions'] as $ls) {
			$this->saveImportEvnMseCategoryLifeType([
				'EvnMseCategoryLifeTypeLink_id' => null,
				'EvnMse_id' => $data['EvnMse_id'],
				'CategoryLifeTypeLink_id' => $ls,
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		foreach($res['SatelliteDesease'] as $k => $sd) {
			$this->saveSatelliteDesease([
				'EvnMseDiagLink_id' => null,
				'EvnMse_id' => $data['EvnMse_id'],
				'Diag_id' => $sd,
				'Diag_oid' => null,
				'DescriptDiag' => $k == 0 ? $res['SatelliteDeseaseDesc'] : '',
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		if(!empty($res['MainDeseaseComplications'])) {
			$this->saveSatelliteDesease([
				'EvnMseDiagLink_id' => null,
				'EvnMse_id' => $data['EvnMse_id'],
				'Diag_id' => null,
				'Diag_oid' => null,
				'DescriptDiag' => $res['MainDeseaseComplications'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		return array(array(
			'success' => true, 
			'EvnMse_id' => $data['EvnMse_id'], 
			'EvnMse_ImportedCouponGUID' => $res['EvnMse_ImportedCouponGUID'], 
			'Message' => 'Обратный талон успешно импортирован'
		));
	}

	/**
	 * Импорт обратных талонов: Идентификация сопутствующих заболеваний
	 */
	function identificateSatelliteDesease($SatelliteDeseaseMKBCodes)
	{
		$MKBCodes = explode(',', trim((string)$SatelliteDeseaseMKBCodes));
		$diag_ids = [];

		foreach($MKBCodes as $diag_code) {
			$diag_ids[] = $this->identificateDiag($diag_code);
		}

		return $diag_ids;
	}

	/**
	 * Импорт обратных талонов: Идентификация человека
	 */
	function identificatePerson($tLpuTicket)
	{
		$filters = '
			Person_SurName = :Person_SurName and
			Person_FirName = :Person_FirName
		';
		
		$params = array(
			'Person_SurName' => trim((string)$tLpuTicket->LastName),
			'Person_FirName' => trim((string)$tLpuTicket->FirstName)
		);
		
		if (!empty($tLpuTicket->SecondName)) {
			$filters .= ' and Person_SecName = :Person_SecName ';
			$params['Person_SecName'] = trim((string)$tLpuTicket->SecondName);
		}
		
		if (!empty($tLpuTicket->SNILS)) {
			$filters .= ' and Person_Snils = :Person_Snils ';
			$params['Person_Snils'] = str_replace(array(' ','-'), '', trim((string)$tLpuTicket->SNILS));
		}
		
		$sql = "select Server_id, Person_id, PersonEvn_id from v_PersonState (nolock) where $filters";
		
		$result = $this->queryResult($sql, $params);
		
		if (count($result) == 1) {
			return $result[0];
		}
		
		return false;
	}

	/**
	 * Импорт обратных талонов: Идентификация диагноза
	 */
	function identificateDiag($diag_code)
	{
		return $this->getFirstResultFromQuery("select Diag_id from v_Diag (nolock) where Diag_Code = ?", array(trim((string)$diag_code)));
	}

	/**
	 * Импорт обратных талонов: Идентификация причины отказа в установлении инвалидности
	 */
	function identificateInvalidRefuseType($InvalidRefuseType_Name)
	{
		return $this->getFirstResultFromQuery("select InvalidRefuseType_id from v_InvalidRefuseType (nolock) where InvalidRefuseType_Name = ?", array(trim((string)$InvalidRefuseType_Name)));
	}

	/**
	 * Импорт обратных талонов: Обработка LifeDysfunctions
	 */
	function identificateLifeDysfunctions($LifeDysfunctions)
	{
		$LifeDysfunctions = trim((string)$LifeDysfunctions);
		if (empty($LifeDysfunctions)) return array('HealthAbnorm_id' => null, 'HealthAbnormDegree_id' => null);
		
		$LifeDysfunctions = explode(':', $LifeDysfunctions);
		if (count($LifeDysfunctions) != 2) return array('HealthAbnorm_id' => null, 'HealthAbnormDegree_id' => null);
		
		return $this->getFirstRowFromQuery("
			select 
				(select HealthAbnorm_id from v_HealthAbnorm (nolock) where HealthAbnorm_Name = :HealthAbnorm_Name) as HealthAbnorm_id,
				(select HealthAbnormDegree_id from v_HealthAbnormDegree (nolock) where HealthAbnormDegree_Name like :HealthAbnormDegree_Name) as HealthAbnormDegree_id
		", array(
			'HealthAbnorm_Name' => trim($LifeDysfunctions[0]),
			'HealthAbnormDegree_Name' => '%'.trim($LifeDysfunctions[1])
		));
	}

	/**
	 * Импорт обратных талонов: Обработка LifeRestrictions
	 */
	function identificateLifeRestrictions($LifeRestrictions)
	{
		$res = [];

		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 1 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->SelfCare)));
		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 2 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->Moving)));
		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 3 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->Orientation)));
		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 4 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->Communication)));
		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 6 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->Learn)));
		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 7 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->Work)));
		$res[] = $this->getFirstResultFromQuery("select CategoryLifeTypeLink_id from v_CategoryLifeTypeLink (nolock) where CategoryLifeType_id = 5 and CategoryLifeDegreeType_id = ?", array(trim((string)$LifeRestrictions->BehaviorControl)));

		foreach($res as $k => $r) {
			if (empty($r)) unset($res[$k]);
		}

		return $res;
	}

	/**
	 * Сохранение сопутствующих заболеваний
	 */
	function saveSatelliteDesease($data) {
		return $this->queryResult("
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_EvnMseDiagLink_ins
				@EvnMseDiagLink_id = @Res output,
				@EvnMse_id = :EvnMse_id,
				@Diag_id = :Diag_id,
				@Diag_oid = :Diag_oid,
				@EvnMseDiagLink_DescriptDiag = :DescriptDiag,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Res as EvnMseDiagLink_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		", $data);
	}


	/**
	 *	Method description
	 */
	function saveImportEvnMseCategoryLifeType($data) {
		return $this->queryResult("
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMseCategoryLifeTypeLink_id;
			exec p_EvnMseCategoryLifeTypeLink_ins
				@EvnMseCategoryLifeTypeLink_id = @Res output,
				@EvnMse_id = :EvnMse_id,
				@CategoryLifeTypeLink_id = :CategoryLifeTypeLink_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnMseCategoryLifeTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}
	
	/**
	 * Проверка наличия необходимого набора исследований
	 */
	function getRequiredSetOfStudiesCheck($data){
		$tmpArr = array();
		$requiredSetOfStudies = $this->getRequiredSetOfStudies($data);
		if($requiredSetOfStudies && count($requiredSetOfStudies)>0){
			$tmpArr = $requiredSetOfStudies;
			//$ss = $this->getUslugaComplex($data);
			if (is_array($data['UslugaComplexMSEData']) && count($data['UslugaComplexMSEData'])>0){
				foreach ($data['UslugaComplexMSEData'] as $ucmdata) {
					$UslugaComplex_id = (is_array($ucmdata)) ? $ucmdata['UslugaComplex_id'] : $ucmdata->UslugaComplex_id;
					if($UslugaComplex_id){
						foreach ($requiredSetOfStudies as $key=>$requiredData) {
							$arr = ($requiredData['UslugaComplexIdList']) ? explode(",", $requiredData['UslugaComplexIdList']) : null;
							
							if(is_array($arr) && count($arr)>0){
								if(in_array($UslugaComplex_id, $arr)){
									$flag = $key;		
									unset($tmpArr[$key]);
								}
							}
						}
					}
				}
			}
		}
		
		return $tmpArr;
	}
		
	/**
	 * Получение необходимого набора исследований
	 */
	function getRequiredSetOfStudies($data){
		if(empty($data['Diag_id'])) {
			return false;
		}
		$regardingDate = (empty($data['EvnPrescrMse_issueDT'])) ? 'dbo.tzGetDate()' : ':EvnPrescrMse_issueDT';

		$query = "declare 
				@curDT date = {$regardingDate}, 
				@Diag_id INT = :Diag_id;

			SELECT
				ucmdl.UslugaComplexMSEDiagLink_id,
				ucmdl.UslugaComplex_id,
				uc.UslugaComplex_Code + ' - ' + uc.UslugaComplex_Name AS UslugaComplex_Name,
				ISNULL(STUFF(		
					(select
						',' + cast(UslugaComplex_id as varchar)
					FROM
						v_UslugaComplexReplacementLink with (nolock) -- замещающие услуги
					WHERE
						UslugaComplexMSEDiagLink_id = ucmdl.UslugaComplexMSEDiagLink_id
						AND @curDT BETWEEN ISNULL(UslugaComplexReplacementLink_begDT, @curDT) AND ISNULL(UslugaComplexReplacementLink_endDT, @curDT)
					FOR XML PATH ('')
					), 1, 1, ''
				) + ',', '') + cast(ucmdl.UslugaComplex_id as varchar) as UslugaComplexIdList
			from
				v_UslugaComplexMSEDiagLink ucmdl (nolock)
				inner join v_UslugaComplex uc (nolock) on ucmdl.UslugaComplex_id = uc.UslugaComplex_id
			WHERE 1=1
				AND ucmdl.Diag_id = @Diag_id

				AND ucmdl.UslugaComplexMSEDiagLink_IsNeed = 2
				AND @curDT BETWEEN ISNULL(UslugaComplexMSEDiagLink_begDT, @curDT) AND ISNULL(UslugaComplexMSEDiagLink_endDT, @curDT)";
				
		//echo getDebugSQL($query, $data);die();
		return $this->queryResult($query, $data);
	}
	
	/**
	 * проверка на полноту исследований МСЭ
	 */
	function completenessTestMSE($data){
		if(empty($data['EvnPrescrMse_id'])) return false;
		//получим направление
		$query = "
			SELECT TOP 1
				EPM.EvnPrescrMse_id,
				EPM.EvnPrescrMse_IsFirstTime,
				EPM.Diag_id,
				EPM.Person_id,
				EPM.EvnPrescrMse_issueDT
			FROM v_EvnPrescrMse EPM (nolock)	
			WHERE EvnPrescrMse_id = :EvnPrescrMse_id
		";
		$resEvnPrescrMse = $this->getFirstRowFromQuery($query, $data);
		
		//получим иследования из направления
		$resEvnPrescrMse['UslugaComplexMSEData'] = $this->loadUslugaComplexMSEList($data);
		$res = $this->getRequiredSetOfStudiesCheck($resEvnPrescrMse);
		
		if($res && is_array($res) && count($res)>0){
			$str = 'Внимание! Отсутствуют исследования с актуальным сроком давности из перечня исследований для данного диагноза. Список исследований:  ';
			$strArr = array();
			foreach ($res as $value) {
				$strArr[] = $value['UslugaComplex_Name'];
			}
			$str .= implode(",", $strArr);
			$res = $str."<br>Проверьте данные, указанные в полях Диагноз, Услуга";
			return array(array('code' => 100, 'msg' => $res));
		}
		
		$query = "SELECT TOP 1 Document_id FROM v_PersonState (nolock) WHERE Person_id = :Person_id";
		$Document_id = $this->getFirstResultFromQuery($query, $resEvnPrescrMse);
		if(empty($Document_id)) {
			return array(array('code' => 101, 'msg' => "Для корректной отправки направления в Бюро МСЭ должны быть указаны тип и номер документа, удостоверяющего личность пациента. Внесите данные на форме \"Человек\""));
		}
		
		return $res;
	}

	/**
	 * Загрузка журнала запросов ВК
	 */
	function loadVKJournalGrid($data) {
		$filter = "";
		$queryParams = [
			'Lpu_id' => $data['Lpu_id']
		];

		if (!empty($data['begDate'])) {
			$filter .= " and cast(EPV.EvnPrescrVK_insDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}
		if (!empty($data['endDate'])) {
			$filter .= " and cast(EPV.EvnPrescrVK_insDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}
		if (!empty($data['Person_SurName'])) {
			$filter .= " and ps.Person_SurName like :Person_SurName + '%'";
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}
		if (!empty($data['Person_FirName'])) {
			$filter .= " and ps.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}
		if (!empty($data['Person_SecName'])) {
			$filter .= " and ps.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}
		if (!empty($data['Person_BirthDay_From'])) {
			$filter .= " and ps.Person_BirthDay >= :Person_BirthDay_From";
			$queryParams['Person_BirthDay_From'] = $data['Person_BirthDay_From'];
		}
		if (!empty($data['Person_BirthDay_To'])) {
			$filter .= " and ps.Person_BirthDay <= :Person_BirthDay_To";
			$queryParams['Person_BirthDay_To'] = $data['Person_BirthDay_To'];
		}
		if (!empty($data['CauseTreatmentType_id'])) {
			$filter .= " and EPV.CauseTreatmentType_id = :CauseTreatmentType_id";
			$queryParams['CauseTreatmentType_id'] = $data['CauseTreatmentType_id'];
		}
		if (!empty($data['EvnStatus_id'])) {
			$filter .= " and EPV.EvnStatus_id = :EvnStatus_id";
			$queryParams['EvnStatus_id'] = $data['EvnStatus_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filter .= " and msf.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['MedStaffFact_id'])) {
			$filter .= " and msf.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		$query = "
			select
				-- select
				EPV.EvnPrescrVK_id,
				EPV.Person_id,
			    PS.Server_id,
			    PS.PersonEvn_id,
			    PS.Person_IsDead,
			    PS.Person_Firname,
			    PS.Person_Secname,
			    PS.Person_Surname,
				EPV.EvnPrescrMse_id,
				EPV.EvnDirectionHTM_id,
				convert(varchar(10), EPV.EvnPrescrVK_insDT, 104) as EvnPrescrVK_setDT,
				isnull(ps.Person_SurName,'') + isnull(' ' + ps.Person_FirName,'') + isnull(' ' + ps.Person_SecName, '') as Person_Fio,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				ctt.CauseTreatmentType_Name,
				es.EvnStatus_Name,
				es.EvnStatus_SysNick,
				--msf.Person_Fio as MedPersonal_Fio,
				MP.Person_Fio as MedPersonal_Fio,
				ev.EvnVK_id,
				convert(varchar(10), ev.EvnVK_setDT, 104) as EvnVK_setDT
				-- end select
			from
				-- from
				v_EvnPrescrVK epv (nolock)
				left join v_TimetableMedService ttms (nolock) on ttms.TimetableMedService_id = epv.TimetableMedService_id 
				left join v_EvnQueue eq (nolock) on eq.EvnQueue_id = epv.EvnQueue_id
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = COALESCE(ttms.EvnDirection_id, eq.EvnDirection_id)
				left join v_PersonState ps (nolock) on ps.Person_id = epv.Person_id
				left join v_CauseTreatmentType ctt (nolock) on ctt.CauseTreatmentType_id = epv.CauseTreatmentType_id
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = ISNULL(epv.EvnStatus_id, 43)
				LEFT JOIN v_MedPersonal MP (nolock) ON MP.MedPersonal_id = ed.MedPersonal_id
				outer apply (
					select top 1
						msf.*
					from
						v_MedStaffFact msf (nolock)
					where
						msf.MedPersonal_id = epv.MedPersonal_sid
						and msf.LpuSection_id = epv.LpuSection_sid
				) msf
				outer apply (
					select top 1
						ev.EvnVK_id,
						ev.EvnVK_setDT
					from
						v_EvnVK ev (nolock)
					where
						ev.EvnPrescrVK_id = epv.EvnPrescrVK_id
						and ev.Lpu_id = :Lpu_id
				) ev
				-- end from
			where
				-- where
				epv.Lpu_id = :Lpu_id
				and ISNULL(ed.EvnStatus_id, 16) not in (12,13)
				{$filter}
				-- end where
			order by
				-- order by
				EPV.EvnPrescrVK_id
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 * Загрузка формы направления на ВК
	 */
	function loadEvnPrescrVKWindow($data) {
		return $this->queryResult("
			select
				EvnPrescrVK_id,
				EvnPrescrVK_pid,
				Person_id,
				PersonEvn_id,
				Server_id,
				MedService_id,
				Lpu_id,
				Lpu_gid,
				TimetableMedService_id,
			    CauseTreatmentType_id,
			    Diag_id,
				EvnPrescrMse_id,
				EvnDirectionHTM_id,
			    EvnStick_id,
			    EvnPrescrVK_LVN,
			    EvnPrescrVK_Note,
			    EvnXml_id,
			    Person_id,
			    PalliatQuestion_id
			from
				v_EvnPrescrVK (nolock)
			where
				EvnPrescrVK_id = :EvnPrescrVK_id
		", [
			'EvnPrescrVK_id' => $data['EvnPrescrVK_id']
		]);
	}

	/**
	 * Загрузка списка статусов направления на ВК
	 */
	function loadEvnPrescrVKStatusGrid($data) {
		return $this->queryResult("
			select
				esh.EvnStatusHistory_id,
				convert(varchar(10), esh.EvnStatusHistory_begDate, 104) as EvnStatusHistory_begDate,
			    es.EvnStatus_Name,
			    esh.EvnStatusHistory_Cause,
				pu.pmUser_Name
			from
				v_EvnStatusHistory esh (nolock)
				left join v_pmUser pu (nolock) on pu.pmUser_id = esh.pmUser_insID
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = esh.EvnStatus_id
			where
				esh.Evn_id = :EvnPrescrVK_id
				and es.EvnStatus_SysNick in ('Rework','RequestReception')
		", [
			'EvnPrescrVK_id' => $data['EvnPrescrVK_id']
		]);
	}

	/**
	 * Отправка оповещения о смене статуса направления на ВК
	 */
	function notifyEvnPrescrVKStatusChange($data) {
		if (
			in_array(getRegionNick(), ['perm', 'vologda'])
			&& !empty($data['EvnStatus_SysNick'])
			&& in_array($data['EvnStatus_SysNick'], ['Rework', 'RequestReception', 'SubmittedVK', 'GeneratedVK'])
		) {
			$resp = $this->queryResult("
				select top 1
					epvk.EvnPrescrVK_id,
					isnull(ps.Person_SurName,'') + isnull(' ' + ps.Person_FirName,'') + isnull(' ' + ps.Person_SecName, '') as Person_Fio,
					ed.EvnDirection_Num,
					es.EvnStatus_Name,
					puc.MedPersonal_id,
				    puc.Lpu_id,
				    convert(varchar(10), epvk.EvnPrescrVK_insDT, 104) as EvnPrescrVK_insDT
				from
					v_EvnPrescrVK epvk (nolock)
					left join v_TimetableMedService ttms (nolock) on ttms.TimetableMedService_id = epvk.TimetableMedService_id 
					left join v_EvnQueue eq (nolock) on eq.EvnQueue_id = epvk.EvnQueue_id
					left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = isnull(ttms.EvnDirection_id, eq.EvnDirection_id)
					left join v_PersonState ps (nolock) on ps.Person_id = epvk.Person_id 
					left join v_EvnStatus es (nolock) on es.EvnStatus_SysNick = :EvnStatus_SysNick and es.EvnClass_id = epvk.EvnClass_id
					left join v_pmUserCache puc (nolock) on puc.pmUser_id = epvk.pmUser_insID
				where
					epvk.EvnPrescrVK_id = :EvnPrescrVK_id
			", [
				'EvnPrescrVK_id' => $data['EvnPrescrVK_id'],
				'EvnStatus_SysNick' => $data['EvnStatus_SysNick']
			]);

			if (!empty($resp[0]['MedPersonal_id'])) {
				$text = "Статус направления на ВК №{$resp[0]['EvnDirection_Num']} от {$resp[0]['EvnPrescrVK_insDT']} пациента {$resp[0]['Person_Fio']} изменился на {$resp[0]['EvnStatus_Name']}.";
				if (!empty($data['EvnStatusHistory_Cause'])) {
					$text .= " Причина: {$data['EvnStatusHistory_Cause']}.";
				}
				$noticeData = [
					'autotype' => 1,
					'Lpu_rid' => $resp[0]['Lpu_id'],
					'MedPersonal_rid' => $resp[0]['MedPersonal_id'],
					'pmUser_id' => $data['pmUser_id'],
					'type' => 1,
					'title' => 'Автоматическое уведомление',
					'text' => $text
				];
				$this->load->model('Messages_model');
				$this->Messages_model->autoMessage($noticeData);
			}
		}
	}
}


