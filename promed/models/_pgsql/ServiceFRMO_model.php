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

class ServiceFRMO_model extends swPgModel {
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
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				COALESCE(L.Lpu_isFRMO, 1) as \"ExportToFRMO\",

				PT.PassportToken_tid as \"PassportToken_tid\",
				to_char(SLP.ServiceListPackage_insDT, 'DD.MM.YYYY') as \"ServiceListPackage_insDT\"

			from
				v_Lpu L 

				inner join fed.v_PassportToken PT  on PT.Lpu_id = L.Lpu_id

				LEFT JOIN LATERAL(

					select 
						SLP.ServiceListPackage_insDT
					from
						stg.v_ServiceListLog SLL 

						inner join stg.v_ServiceListPackage SLP  on SLP.ServiceListLog_id = SLL.ServiceListLog_id

					where
						SLL.ServiceList_id = 11
						and ServiceListPackage_ObjectName = 'Lpu'
						and ServiceListPackage_ObjectID = L.Lpu_id
					order by
						SLP.ServiceListPackage_insDT desc
                    limit 1
				) SLP ON true
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
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				coalesce(L.Lpu_isFRMO, 1) as \"ImportFromFRMO\",
				PT.PassportToken_tid as \"PassportToken_tid\",
				to_char(FS.FRMOSession_endDT, 'DD.MM.YYYY') as \"FRMOSession_endDT\"
			from
				v_Lpu L
				inner join fed.v_PassportToken PT on PT.Lpu_id = L.Lpu_id
				left join lateral (
					select FRMOSession_endDT
					from dbo.FRMOSession
					where Lpu_id = L.Lpu_id
						and FRMOSession_Name = 'FRMO import session'
						and FRMOSession_endDT is not null
						and FRMOSessionMode_id = 2
					order by
						FRMOSession_endDT desc
					limit 1
				) FS
			where
				nullif(PT.PassportToken_tid, '-1') is not null
			order by
				L.Lpu_isFRMO desc,
				L.Lpu_Nick
		", array());
	}

	/**
	 * Запуск экспопрта данных в сервис ФРМО
	 * @param array $data
	 * @return array
	 */
	function runExport($data) {
		if (!empty($data['LpuList']) && is_array($data['LpuList'])) {
			foreach($data['LpuList'] as $Lpu_id) {
				$this->queryResult("
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
					from dbo.p_FRMOSession_run(
						Lpu_id := :Lpu_id,
						FRMOSession_Name := :FRMOSession_Name,
						FRMOSession_begDT := :FRMOSession_begDT,
						pmUser_id := :pmUser_id);	
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
				select ServiceList_id as \"ServiceList_id\"
				from stg.v_ServiceListLog 
				where 
					ServiceList_id = :ServiceList_id 
					and ServiceListResult_id = 2
					and ServiceListLog_begDT >= cast(dbo.tzGetDate() as date)
				limit 1
			";
			$isRunning = $this->getFirstResultFromQuery($query, $data);

			if ($isRunning !== false) {
				throw new Exception('Сервис уже выполняется');
			}

			foreach ( $data['LpuList'] as $Lpu_id ) {
				$resp = $this->getFirstRowFromQuery("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from dbo.p_FRMOSession_run (
						Lpu_id := :Lpu_id,
						FRMOSession_Name := 'FRMO import session',
						FRMOSession_begDT := :FRMOSession_begDT,
						FRMOSessionMode_id := 2, 
						pmUser_id := :pmUser_id
					)
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from dbo.p_FRMOSession_run 
				Lpu_id := (select Lpu_id from FRMOSession where FRMOSession_id := :FRMOSession_id limit 1),
				FRMOSession_Name := null,
				FRMOSession_begDT := null,
				pmUser_id := :pmUser_id);	
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
			select ServiceList_id  as \"ServiceList_id \"
			from stg.v_ServiceListLog  

			where 
				ServiceList_id = :ServiceList_id 
				and ServiceListResult_id = 2
				and ServiceListLog_begDT >= dbo.tzGetDate()
            limit 1

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
			select ServiceListLog_id as \"ServiceListLog_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from stg.p_ServiceListLog_ins(
				ServiceListLog_id := ServiceListLog_id output,
				ServiceList_id := :ServiceList_id,
				ServiceListLog_begDT := dbo.tzGetDate(),
				ServiceListLog_endDT := null,
				ServiceListResult_id := :ServiceListResult_id,
				pmUser_id := :pmUser_id);
		", $params);

		$query = "
			DELETE FROM 
				dbo.FRMOSession
			where 
				FRMOSession_Service = 'FRMO' 
				and FRMOSession_begDT > dbo.tzGetDate() 
				and FRMOSession_Name iLIKE 'Сессия чтения данных из ФРМО%'

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
				FSH.FRMOSessionHist_id as \"FRMOSessionHist_id\",
				FSAT.FRMOSessionActionType_Name as \"FRMOSessionActionType_Name\",
				to_char(FSH.FRMOSessionHist_insDT, 'DD.MM.YYYY') ||' '|| to_char(FSH.FRMOSessionHist_insDT, 'HH24:MI:SS') as \"FRMOSessionHist_insDT\",


				to_char(FSH.FRMOSessionHist_sendDT, 'DD.MM.YYYY') ||' '|| to_char(FSH.FRMOSessionHist_sendDT, 'HH24:MI:SS') as \"FRMOSessionHist_sendDT\",


				to_char(FSH.FRMOSessionHist_getDT, 'DD.MM.YYYY') ||' '|| to_char(FSH.FRMOSessionHist_getDT, 'HH24:MI:SS') as \"FRMOSessionHist_getDT\",


				to_char(FSH.FRMOSessionHist_doneDT, 'DD.MM.YYYY') ||' '|| to_char(FSH.FRMOSessionHist_doneDT, 'HH24:MI:SS') as \"FRMOSessionHist_doneDT\"


				-- end select
			from
				-- from
				FRMOSessionHist FSH 

				left join v_FRMOSessionActionType FSAT  on FSAT.FRMOSessionActionType_id = FSH.FRMOSessionActionType_id

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
				FSE.FRMOSessionError_id as \"FRMOSessionError_id\",
				COALESCE(FSET.FRMOSessionErrorType_Name, FSE.FRMOSessionError_Message) as \"FRMOSessionErrorType_Name\",

			 	FSAT.FRMOSessionActionType_Descr as \"FRMOSessionActionType_Descr\",
				L.Lpu_Nick as \"Lpu_Nick\",
				LU.LpuUnit_Name as \"LpuUnit_Name\",
				LBP.LpuBuildingPass_Name as \"LpuBuildingPass_Name\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				FSE.LpuStaff_Num as \"LpuStaff_Num\"
				-- end select
			from
				-- from
				v_FRMOSession FS 

				LEFT JOIN LATERAL (

					select 
						h.FRMOSessionHist_id,
						h.FRMOSessionHist_insDT
					from
						v_FRMOSessionHist h  

						inner join v_FRMOSessionActionType a  on a.FRMOSessionActionType_id = h.FRMOSessionActionType_id

					where
						FS.FRMOSession_id = h.FRMOSession_id
						and a.FRMOSessionActionType_code in (60, 64)
					order by
						h.FRMOSessionHist_id desc
                    limit 1
				) ST ON true
				inner join FRMOSessionError FSE  on FSE.FRMOSession_id = FS.FRMOSession_id

				inner join FRMOSessionHist FSH  on FSE.FRMOSessionHist_id = FSH.FRMOSessionHist_id 

        		inner join FRMOSessionActionType FSAT  on FSAT.FRMOSessionActionType_id = FSH.FRMOSessionActionType_id        		

				left join FRMOSessionErrorType FSET  on FSET.FRMOSessionErrorType_id = FSE.FRMOSessionErrorType_id

				left join v_Lpu L  on L.Lpu_id = FSE.Lpu_id

				left join v_LpuUnit LU  on LU.LpuUnit_id = FSE.LpuUnit_id

				left join v_LpuBuildingPass LBP  on LBP.LpuBuildingPass_id = FSE.LpuBuildingPass_id

				left join v_LpuSection LS  on LS.LpuSection_id = FSE.LpuSection_id

				-- end from
			where
				-- where
				FS.FRMOSession_id = :FRMOSession_id
				and FSH.FRMOSessionHist_insDT >= COALESCE(ST.FRMOSessionHist_insDT, FS.FRMOSession_insDT)

				-- end where
			order by
				-- order by
				FSE.FRMOSessionError_insDT desc
				-- end order by
		";

		return $this->queryResult($query, $params);
	}
}