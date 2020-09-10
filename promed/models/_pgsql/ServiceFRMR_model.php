<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceFRMR_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Stanislav Bykov (savage@swan-it.ru)
 * @version      21.12.2018
 *
 */

class ServiceFRMR_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Запуск экспопрта данных в сервис ФРМР
	 * @param array $data
	 * @return array
	 */
   function runExport($data) {
		try {
			$this->beginTransaction();

			$data['ServiceList_id'] = $this->getFirstResultFromQuery("select ServiceList_id from stg.v_ServiceList  where ServiceList_SysNick = 'TransfDateFRMR' limit 1", array());

			if ( $data['ServiceList_id'] === false ) {
				throw new Exception('Ошибка при получении идентификатор сервиса');
			}

			$params = array(
				'ServiceList_id' => $data['ServiceList_id'],
				'ServiceListResult_id' => 2,
				'pmUser_id' => $data['pmUser_id'],
			);

			$runLogResp = $this->getFirstRowFromQuery("
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\",
                    ServiceListLog_id as \"ServiceListLog_id\"
                from stg.p_ServiceListLog_ins
                    (
					        ServiceList_id := :ServiceList_id,
					        ServiceListLog_begDT := dbo.tzGetDate(),
					        ServiceListLog_endDT := null,
					        ServiceListResult_id := :ServiceListResult_id,
					        pmUser_id := :pmUser_id
                    )", $params);

			if ( $runLogResp === false ) {
				throw new Exception('Ошибка при добавлении записи в лог');
			}
			else if ( !empty($runLogResp['Error_Msg']) ) {
				throw new Exception($runLogResp['Error_Msg']);
			}

			$params = array(
				'FRMOSession_comment' => 'ServiceList_id = ' . $data['ServiceList_id'] . '; ServiceListLog_id = ' . $runLogResp['ServiceListLog_id'],
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
			);

			$runResp = $this->getFirstRowFromQuery("
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\",
                    FRMOSession_id as \"FRMOSession_id\"
                from dbo.p_FRMOSession_ins
                    (
 					        FRMOSession_Guid := newid(),
					        FRMOSession_Name := 'FRMR',
					        FRMOSession_begDT := dbo.tzGetDate(),
					        FRMOSession_endDT := dbo.tzGetDate(),
					        Lpu_id := :Lpu_id,
					        FRMOSession_success := cast(2 as bool),
					        FRMOSession_comment := :FRMOSession_comment,
					        FRMOSession_Service := 'FRMR',
					        pmUser_id := :pmUser_id
                    )", $params);

			if ( $runResp === false ) {
				throw new Exception('Ошибка при добавлении сессии сервиса (FRMOSession)');
			}
			else if ( !empty($runResp['Error_Msg']) ) {
				throw new Exception($runResp['Error_Msg']);
			}

			$params = array(
				'FRMOSession_id' => $runResp['FRMOSession_id'],
				'ServiceListLog_id' => $runLogResp['ServiceListLog_id'],
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
			);

			$runResp = $this->getFirstRowFromQuery("
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from dbo.p_FRMRIntegrationSession_ins
                    (
 					        FRMOSession_id := :FRMOSession_id,
					        FRMRIntegrationSession_isSuccess := null,
					        Lpu_id := :Lpu_id,
					        Lpu_oid := (select PassportToken_tid from fed.v_PassportToken  where Lpu_id = :Lpu_id limit 1),
					        FRMRIntegrationSession_isUsed := null,
					        FRMOSessionActionType_id := 10053,
					        ServiceListLog_id := :ServiceListLog_id,
					        pmUser_id := :pmUser_id
                    )", $params);

			if ( $runResp === false ) {
				throw new Exception('Ошибка при добавлении сессии сервиса (FRMRIntegrationSession)');
			}
			else if ( !empty($runResp['Error_Msg']) ) {
				throw new Exception($runResp['Error_Msg']);
			}

			$this->commitTransaction();

			$response = array(array('success' => true));
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response = array(array('Error_Msg' => $e->getMessage()));
		}

		return $response;
	}

	/**
	 * Запуск импорта данных из сервиса ФРМР
	 * @param array $data
	 * @return array
	 */
   public function runImport($data) {
		try {
			$this->beginTransaction();

			$data['ServiceList_id'] = $this->getFirstResultFromQuery("select  ServiceList_id as \"ServiceList_id\" from stg.v_ServiceList where ServiceList_SysNick = 'ServiceUpdFRMR' limit 1", array());

			if ( $data['ServiceList_id'] === false ) {
				throw new Exception('Ошибка при получении идентификатор сервиса');
			}

			$isRunning = $this->getFirstResultFromQuery("select  ServiceList_id as \"ServiceList_id\" from stg.v_ServiceListLog  where ServiceList_id = :ServiceList_id and ServiceListResult_id = 2 limit 1", $data);;

			if ( $isRunning !== false ) {
				throw new Exception('Сервис уже выполняется');
			}

			$params = array(
				'ServiceList_id' => $data['ServiceList_id'],
				'ServiceListResult_id' => 2,
				'pmUser_id' => $data['pmUser_id'],
			);

			$runResp = $this->getFirstRowFromQuery("
                    select
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\",
                       ServiceListLog_id as \"ServiceListLog_id\"
                    from stg.p_ServiceListLog_ins
                        (
					            ServiceList_id := :ServiceList_id,
					            ServiceListLog_begDT := dbo.tzGetDate(),
					            ServiceListLog_endDT := null,
					            ServiceListResult_id := :ServiceListResult_id,
					            pmUser_id := :pmUser_id
                        )", $params);

			if ( $runResp === false ) {
				throw new Exception('Ошибка при добавлении записи в лог');
			}
			else if ( !empty($runResp['Error_Msg']) ) {
				throw new Exception($runResp['Error_Msg']);
			}

			$data['ServiceListLog_id'] = $runResp['ServiceListLog_id'];

			$params = array(
				'FRMOSession_comment' => 'ServiceList_id = ' . $data['ServiceList_id'] . '; ServiceListLog_id = ' . $runResp['ServiceListLog_id'],
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
			);

			$runResp = $this->getFirstRowFromQuery("
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            FRMOSession_id as \"FRMOSession_id\"
        from dbo.p_FRMOSession_ins
            (
					FRMOSession_Guid := newid(),
					FRMOSession_Name := 'FRMR',
					FRMOSession_begDT := dbo.tzGetDate(),
					FRMOSession_endDT := dbo.tzGetDate(),
					Lpu_id := :Lpu_id,
					FRMOSession_success := cast(2 as bool),
					FRMOSession_comment := :FRMOSession_comment,
					FRMOSession_Service := 'FRMR',
					pmUser_id := :pmUser_id
            )", $params);

			if ( $runResp === false ) {
				throw new Exception('Ошибка при добавлении сессии сервиса (FRMOSession)');
			}
			else if ( !empty($runResp['Error_Msg']) ) {
				throw new Exception($runResp['Error_Msg']);
			}

			$params = array(
				'FRMOSession_id' => $runResp['FRMOSession_id'],
				'ServiceListLog_id' => $data['ServiceListLog_id'],
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
			);

			$runResp = $this->getFirstRowFromQuery("
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from dbo.p_FRMRIntegrationSession_ins
                (
					    FRMOSession_id := :FRMOSession_id,
					    FRMRIntegrationSession_isSuccess := 2,
					    Lpu_id := :Lpu_id,
					    FRMRIntegrationSession_isUsed := null,
					    FRMOSessionActionType_id := 10055,
					    ServiceListLog_id := :ServiceListLog_id,
					    pmUser_id := :pmUser_id
                )", $params);

			if ( $runResp === false ) {
				throw new Exception('Ошибка при добавлении сессии сервиса (FRMRIntegrationSession)');
			}
			else if ( !empty($runResp['Error_Msg']) ) {
				throw new Exception($runResp['Error_Msg']);
			}

			$this->commitTransaction();

			$response = array(array('success' => true));
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response = array(array('Error_Msg' => $e->getMessage()));
		}

		return $response;
	}
	
}