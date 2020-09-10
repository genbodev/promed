<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceFRMO_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      12.10.2017
 *
 */

class ServiceFRMO_model extends SwModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка МО для экспорта в сервис ФРМО
	 * @param array $data
	 * @return array|false
	 */
	function loadLpuListForExport($data) {
		$params = array();
		$query = "
			select
				L.Lpu_id,
				L.Lpu_Nick,
				isnull(L.Lpu_isFRMO, 1) as ExportToFRMO,
				PT.PassportToken_tid,
				convert(varchar(10), SLP.ServiceListPackage_insDT, 104) as ServiceListPackage_insDT
			from
				v_Lpu L with(nolock)
				inner join fed.v_PassportToken PT with(nolock) on PT.Lpu_id = L.Lpu_id
				outer apply(
					select top 1
						SLP.ServiceListPackage_insDT
					from
						stg.v_ServiceListLog SLL with(nolock)
						inner join stg.v_ServiceListPackage SLP with(nolock) on SLP.ServiceListLog_id = SLL.ServiceListLog_id
					where
						SLL.ServiceList_id = 11
						and ServiceListPackage_ObjectName = 'Lpu'
						and ServiceListPackage_ObjectID = L.Lpu_id
					order by
						SLP.ServiceListPackage_insDT desc
				) SLP
			where
				nullif(PT.PassportToken_tid, '-1') is not null
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО для импорта из сервиса ФРМО
	 * @param array $data
	 * @return array|false
	 */
	public function loadLpuListForImport($data) {
		return $this->queryResult("
			select
				L.Lpu_id,
				L.Lpu_Nick,
				isnull(L.Lpu_isFRMO, 1) as ImportFromFRMO,
				PT.PassportToken_tid,
				convert(varchar(10), FS.FRMOSession_endDT, 104) as FRMOSession_endDT
			from
				v_Lpu L with(nolock)
				inner join fed.v_PassportToken PT with(nolock) on PT.Lpu_id = L.Lpu_id
				outer apply(
					select top 1 FRMOSession_endDT
					from dbo.FRMOSession with (nolock)
					where Lpu_id = L.Lpu_id
						and FRMOSession_Name = 'FRMO import session'
						and FRMOSession_endDT is not null
						and FRMOSessionMode_id = 2
					order by
						FRMOSession_endDT desc
				) FS
			where
				nullif(PT.PassportToken_tid, '-1') is not null
			order by
				L.Lpu_isFRMO desc,
				L.Lpu_Nick
		", array());
	}

	/**
	 * Запуск экспорта данных в сервис ФРМО
	 * @param array $data
	 * @return array
	 */
	function runExport($data) {
		if (!empty($data['LpuList']) && is_array($data['LpuList'])) {
			foreach($data['LpuList'] as $Lpu_id) {
				$this->queryResult("
					declare
						@Error_Code int,
						@Error_Message varchar(4000)
					
					exec dbo.p_FRMOSession_run 
						@Lpu_id = :Lpu_id,
						@FRMOSession_Name = :FRMOSession_Name,
						@FRMOSession_begDT = :FRMOSession_begDT,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT
					
					select @Error_Code, @Error_Message 		
				", array(
					'Lpu_id' => $Lpu_id,
					'FRMOSession_Name' => 'Экспорт в ФРМО по МО: ' . $Lpu_id,
					'FRMOSession_begDT' => date('Y-m-d'),
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Запуск импорта данных из сервиса ФРМО
	 * @param array $data
	 * @return array
	 */
	public function runImport($data) {
		$response = array(array('success' => true, 'Error_Msg' => ''));

		try {
			$this->beginTransaction();

			if (empty($data['LpuList']) || !is_array($data['LpuList'])) {
				throw new Exception('Не указаны МО');
			}

			$data['ServiceList_id'] = 49;

			$query = "
				declare
					@today date = dbo.tzGetDate(); 
				select top 1 ServiceList_id 
				from stg.v_ServiceListLog with (nolock) 
				where 
					ServiceList_id = :ServiceList_id 
					and ServiceListResult_id = 2
					and ServiceListLog_begDT >= @today 
	
			";
			$isRunning = $this->getFirstResultFromQuery($query, $data);

			if ($isRunning !== false) {
				throw new Exception('Сервис уже выполняется');
			}

			foreach ( $data['LpuList'] as $Lpu_id ) {
				$resp = $this->getFirstRowFromQuery("
					declare
						@Err_Code int,
						@Err_Message varchar(4000);
					
					exec dbo.p_FRMOSession_run 
						@Lpu_id = :Lpu_id,
						@FRMOSession_Name = 'FRMO import session',
						@FRMOSession_begDT = :FRMOSession_begDT,
						@FRMOSessionMode_id = 2, 
						@pmUser_id = :pmUser_id,
						@Error_Code = @Err_Code output,
						@Error_Message = @Err_Message OUTPUT
					
					select @Err_Code as Error_Code, @Err_Message as Error_Msg;
				", array(
					'Lpu_id' => $Lpu_id,
					'FRMOSession_begDT' => date('Y-m-d'),
					'pmUser_id' => $data['pmUser_id'],
				));

				if ( !empty($resp['Error_Msg']) ) {
					throw new Exception($resp['Error_Msg']);
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Возобновление экспорта данных в сервис ФРМО
	 * @param array $data
	 * @return array
	 */
	function resumeFRMOSession($data) {
		$this->queryResult("
			declare
				@Lpu_id bigint = (select top 1 Lpu_id from FRMOSession where FRMOSession_id = :FRMOSession_id),
				@Error_Code int,
				@Error_Message varchar(4000)
			
			exec dbo.p_FRMOSession_run 
				@Lpu_id = @Lpu_id,
				@FRMOSession_Name = null,
				@FRMOSession_begDT = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT
			
			select @Error_Code, @Error_Message 		
		", array(
			'FRMOSession_id' => $data['FRMOSession_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array(array('success' => true));
	}

	/**
	 * Запуск сервиса обновления ФРМО
	 */
	function runUpdate($data = array()) {

		if(empty($data['ServiceList_id'])) {
			$data['ServiceList_id'] = 49;
		}

		$query = "
			declare
				@today date = dbo.tzGetDate(); 
			select top 1 ServiceList_id 
			from stg.v_ServiceListLog with (nolock) 
			where 
				ServiceList_id = :ServiceList_id 
				and ServiceListResult_id = 2
				and ServiceListLog_begDT >= @today 

		";
		$isRunning = $this->getFirstResultFromQuery($query, $data);
		if ( $isRunning !== false ) {
			throw new Exception('Сервис уже выполняется');
		}

		$params = array(
			'ServiceList_id' => $data['ServiceList_id'],
			'ServiceListResult_id' => 2,
			'pmUser_id' => $data['pmUser_id'],
		);

		$runResp = $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@ServiceListLog_id bigint,
				@date datetime = dbo.tzGetDate();

			exec stg.p_ServiceListLog_ins
				@ServiceListLog_id = @ServiceListLog_id output,
				@ServiceList_id = :ServiceList_id,
				@ServiceListLog_begDT = @date,
				@ServiceListLog_endDT = null,
				@ServiceListResult_id = :ServiceListResult_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output

			select @ServiceListLog_id as ServiceListLog_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		", $params);

		$query = "
			declare
				@nowDate date = dbo.tzGetDate();
			DELETE FROM 
				[dbo].[FRMOSession] with(rowlock)
			where 
				FRMOSession_Service = 'FRMO' 
				and FRMOSession_begDT > @nowDate 
				and [FRMOSession_Name] like 'Сессия чтения данных из ФРМО%'
		";
		$this->db->query($query, array());

		return 	$runResp;
	}

	/**
	 * Получение списка детального лога
	 */
	function loadFRMOSessionHistGrid($data) {
		$params = array(
			'FRMOSession_id' => $data['FRMOSession_id']
		);

		$query = "
			select
				-- select
				FSH.FRMOSessionHist_id,
				FSAT.FRMOSessionActionType_Name,
				convert(varchar(10), FSH.FRMOSessionHist_insDT, 104) +' '+ convert(varchar(5), FSH.FRMOSessionHist_insDT, 108) as FRMOSessionHist_insDT,
				convert(varchar(10), FSH.FRMOSessionHist_sendDT, 104) +' '+ convert(varchar(5), FSH.FRMOSessionHist_sendDT, 108) as FRMOSessionHist_sendDT,
				convert(varchar(10), FSH.FRMOSessionHist_getDT, 104) +' '+ convert(varchar(5), FSH.FRMOSessionHist_getDT, 108) as FRMOSessionHist_getDT,
				convert(varchar(10), FSH.FRMOSessionHist_doneDT, 104) +' '+ convert(varchar(5), FSH.FRMOSessionHist_doneDT, 108) as FRMOSessionHist_doneDT
				-- end select
			from
				-- from
				FRMOSessionHist FSH with (nolock)
				left join v_FRMOSessionActionType FSAT with (nolock) on FSAT.FRMOSessionActionType_id = FSH.FRMOSessionActionType_id
				-- end from
			where
				-- where
				FSH.FRMOSession_id = :FRMOSession_id
				-- end where
			order by
				-- order by
				FSH.FRMOSessionHist_insDT desc
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение списка детального лога
	 */
	function loadFRMOSessionErrorGrid($data) {
		$params = array(
			'FRMOSession_id' => $data['FRMOSession_id']
		);

		$query = "
			select
				-- select
				FSE.FRMOSessionError_id,
				ISNULL(FSET.FRMOSessionErrorType_Name, FSE.FRMOSessionError_Message) as FRMOSessionErrorType_Name,
			 	FSAT.FRMOSessionActionType_Descr,
				L.Lpu_Nick,
				LU.LpuUnit_Name,
				LBP.LpuBuildingPass_Name,
				LS.LpuSection_Name,
				FSE.LpuStaff_Num
				-- end select
			from
				-- from
				v_FRMOSession FS with (nolock)
				outer apply (
					select top 1
						h.FRMOSessionHist_id,
						h.FRMOSessionHist_insDT
					from
						v_FRMOSessionHist h with (nolock) 
						inner join v_FRMOSessionActionType a with (nolock) on a.FRMOSessionActionType_id = h.FRMOSessionActionType_id
					where
						FS.FRMOSession_id = h.FRMOSession_id
						and a.FRMOSessionActionType_code in (60, 64)
					order by
						h.FRMOSessionHist_id desc
				) ST        		
				inner join FRMOSessionError FSE with (nolock) on FSE.FRMOSession_id = FS.FRMOSession_id
				inner join FRMOSessionHist FSH with (nolock) on FSE.FRMOSessionHist_id = FSH.FRMOSessionHist_id 
        		inner join FRMOSessionActionType FSAT with (nolock) on FSAT.FRMOSessionActionType_id = FSH.FRMOSessionActionType_id        		
				left join FRMOSessionErrorType FSET with (nolock) on FSET.FRMOSessionErrorType_id = FSE.FRMOSessionErrorType_id
				left join v_Lpu L with (nolock) on L.Lpu_id = FSE.Lpu_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = FSE.LpuUnit_id
				left join v_LpuBuildingPass LBP with (nolock) on LBP.LpuBuildingPass_id = FSE.LpuBuildingPass_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = FSE.LpuSection_id
				-- end from
			where
				-- where
				FS.FRMOSession_id = :FRMOSession_id
				and FSH.FRMOSessionHist_insDT >= isnull(ST.FRMOSessionHist_insDT, FS.FRMOSession_insDT)
				-- end where
			order by
				-- order by
				FSE.FRMOSessionError_insDT desc
				-- end order by
		";

		return $this->queryResult($query, $params);
	}
}