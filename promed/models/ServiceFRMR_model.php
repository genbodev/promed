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

class ServiceFRMR_model extends SwModel {
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

			$data['ServiceList_id'] = $this->getFirstResultFromQuery("select top 1 ServiceList_id from stg.v_ServiceList with (nolock) where ServiceList_SysNick = 'TransfDateFRMR'", array());

			if ( $data['ServiceList_id'] === false ) {
				throw new Exception('Ошибка при получении идентификатор сервиса');
			}

			$params = array(
				'ServiceList_id' => $data['ServiceList_id'],
				'ServiceListResult_id' => 2,
				'pmUser_id' => $data['pmUser_id'],
			);

			$runLogResp = $this->getFirstRowFromQuery("
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
				declare
					@FRMOSession_id bigint,
					@FRMOSession_Guid uniqueidentifier = newid(),
					@Error_Code bigint,
					@Error_Message varchar(4000),
					@date datetime = dbo.tzGetDate();

				exec dbo.p_FRMOSession_ins
					@FRMOSession_id = @FRMOSession_id output,
					@FRMOSession_Guid = @FRMOSession_Guid,
					@FRMOSession_Name = 'FRMR',
					@FRMOSession_begDT = @date,
					@FRMOSession_endDT = @date,
					@Lpu_id = :Lpu_id,
					@FRMOSession_success = 2,
					@FRMOSession_comment = :FRMOSession_comment,
					@FRMOSession_Service = 'FRMR',
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output

				select @FRMOSession_id as FRMOSession_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			", $params);

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
				declare
					@Lpu_oid varchar(256),
					@Error_Code bigint,
					@Error_Message varchar(4000);

				set @Lpu_oid = (select top 1 PassportToken_tid from fed.v_PassportToken (nolock) where Lpu_id = :Lpu_id); 
				
				exec dbo.p_FRMRIntegrationSession_ins
					@FRMOSession_id = :FRMOSession_id,
					@FRMRIntegrationSession_isSuccess = null,
					@Lpu_id = :Lpu_id,
					@Lpu_oid = @Lpu_oid,
					@FRMRIntegrationSession_isUsed = null,
					@FRMOSessionActionType_id = 10053,
					@ServiceListLog_id = :ServiceListLog_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output

				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			", $params);

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

			$data['ServiceList_id'] = $this->getFirstResultFromQuery("select top 1 ServiceList_id from stg.v_ServiceList with (nolock) where ServiceList_SysNick = 'ServiceUpdFRMR'", array());

			if ( $data['ServiceList_id'] === false ) {
				throw new Exception('Ошибка при получении идентификатор сервиса');
			}

			$isRunning = $this->getFirstResultFromQuery("select top 1 ServiceList_id from stg.v_ServiceListLog with (nolock) where ServiceList_id = :ServiceList_id and ServiceListResult_id = 2", $data);;

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
				declare
					@FRMOSession_id bigint,
					@FRMOSession_Guid uniqueidentifier = newid(),
					@Error_Code bigint,
					@Error_Message varchar(4000),
					@date datetime = dbo.tzGetDate();

				exec dbo.p_FRMOSession_ins
					@FRMOSession_id = @FRMOSession_id output,
					@FRMOSession_Guid = @FRMOSession_Guid,
					@FRMOSession_Name = 'FRMR',
					@FRMOSession_begDT = @date,
					@FRMOSession_endDT = @date,
					@Lpu_id = :Lpu_id,
					@FRMOSession_success = 2,
					@FRMOSession_comment = :FRMOSession_comment,
					@FRMOSession_Service = 'FRMR',
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output

				select @FRMOSession_id as FRMOSession_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			", $params);

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
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec dbo.p_FRMRIntegrationSession_ins
					@FRMOSession_id = :FRMOSession_id,
					@FRMRIntegrationSession_isSuccess = 2,
					@Lpu_id = :Lpu_id,
					@FRMRIntegrationSession_isUsed = null,
					@FRMOSessionActionType_id = 10055,
					@ServiceListLog_id = :ServiceListLog_id, 
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output

				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			", $params);

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