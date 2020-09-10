<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Exp_Query_model - модель для работы с файлами информационного обмена с поставщиками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.11.2013
 *
 * @property Exp_Query_model dbmodel
 */

class Exp_Query_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список файлов
	 */
	function loadQueryGrid($data) {
		$params = array();

		$query = "
			select
				Q.Query_id,
				Q.Query_Nick,
				Q.Filename,
				Q.Name,
				Q.Ord
			from
				rls.exp_Query Q with(nolock)
			order by
			 	Q.Ord
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает список полей запроса
	 */
	function loadDbaseStructureGrid($data)
	{
		$params = array('Query_id' => $data['Query_id']);

		$query = "
			select
				DS.DbaseStructure_id,
				DS.Ord,
				DS.Query_ColumnName,
				DS.Dbase_ColumnName,
				DS.Dbase_ColumnType,
				DS.Dbase_ColumnLength,
				DS.Dbase_ColumnPrecision,
				DS.Description,
				1 as RecordStatus_Code
			from
				rls.exp_DbaseStructure DS with(nolock)
			where
				DS.Query_id = :Query_id
			order by
			 	DS.Ord
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает данные для формы редактирования файла запроса
	 */
	function loadQueryForm($data)
	{
		$params = array('Query_id' => $data['Query_id']);

		$query = "
			select
				Q.Query_id,
				Q.Query_Nick,
				Q.Filename,
				Q.Name,
				RTRIM(Q.Query) as Query,
				Q.Ord
			from
				rls.exp_Query Q with(nolock)
			where
				Q.Query_id = :Query_id
			order by
			 	Q.Ord
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохраняет данные файла запроса
	 */
	function saveQuery($data)
	{
		$response = array(
			'Query_id' => null,
			'Error_Code' => null,
			'Error_Msg' => null
		);


		$this->beginTransaction();

		if (empty($data['Query_id']) || $data['Query_id'] < 0 ) {
			$result = $this->createQuery($data);
		} else {
			$result =  $this->updateQuery($data);
		}

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение атрибутов)';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при сохранении атрибутов';
			return array($response);
		}
		else if ( !empty($queryResponse[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $queryResponse[0]['Error_Msg'];
			return array($response);
		}

		$response['Query_id'] = $queryResponse[0]['Query_id'];

		if ( !empty($data['DbaseStructureData']) ) {
			$DbaseStructureData = json_decode($data['DbaseStructureData'], true);

			if ( is_array($DbaseStructureData) ) {
				for ( $i = 0; $i < count($DbaseStructureData); $i++ ) {
					if ( empty($DbaseStructureData[$i]['DbaseStructure_id']) || !is_numeric($DbaseStructureData[$i]['DbaseStructure_id']) ) {
						continue;
					}

					if ( !isset($DbaseStructureData[$i]['RecordStatus_Code']) || !is_numeric($DbaseStructureData[$i]['RecordStatus_Code']) || !in_array($DbaseStructureData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$DbaseStructure = array(
						'Query_id' => $response['Query_id'],
						'DbaseStructure_id' => $DbaseStructureData[$i]['DbaseStructure_id'],
						'Query_ColumnName' => $DbaseStructureData[$i]['Query_ColumnName'],
						'Dbase_ColumnName' => $DbaseStructureData[$i]['Dbase_ColumnName'],
						'Dbase_ColumnType' => $DbaseStructureData[$i]['Dbase_ColumnType'],
						'Dbase_ColumnLength' => (is_numeric($DbaseStructureData[$i]['Dbase_ColumnLength'])?$DbaseStructureData[$i]['Dbase_ColumnLength']:null),
						'Dbase_ColumnPrecision' => (is_numeric($DbaseStructureData[$i]['Dbase_ColumnPrecision'])?$DbaseStructureData[$i]['Dbase_ColumnPrecision']:null),
						'Description' => $DbaseStructureData[$i]['Description'],
						'Ord' => $DbaseStructureData[$i]['Ord']
					);

					switch ( $DbaseStructureData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveDbaseStructure($DbaseStructure);
							break;

						case 3:
							$queryResponse = $this->deleteDbaseStructure($DbaseStructure);
							break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($DbaseStructureData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' поля';
						return array($response);
					} else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Добавление информации о файле запроса
	 */
	function createQuery($data) {
		$query = "
			declare
				@Query_id int,
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				insert into rls.exp_Query (Query_Nick, Filename, Name,	Query, Ord)
					values (:Query_Nick, :Filename, :Name,	:Query, :Ord)
				set nocount off;
				set @Query_id = SCOPE_IDENTITY();
			end try

			begin catch
				set @Error_Code = ERROR_NUMBER();
				set @Error_Message = ERROR_MESSAGE();
			end catch

			select @Query_id as Query_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$response = $this->db->query($query, $data);

		//echo getDebugSql($query, $data);exit;
		//print_r($response->result('array'));exit;

		return $response;
	}

	/**
	 * Обновление информации о файле запроса
	 */
	function updateQuery($data) {
		$query = "
			declare
				@Query_id int,
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				update
					rls.exp_Query with(rowlock)
				set
					Query_Nick = :Query_Nick,
					Filename = :Filename,
					Name = :Name,
					Query = :Query,
					Ord = :Ord
				where
					Query_id = :Query_id;
				set nocount off;
				set @Query_id = :Query_id;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @Query_id as Query_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$response = $this->db->query($query, $data);

		//echo getDebugSql($query, $data);exit;
		//print_r($response->result('array'));exit;

		return $response;
	}

	/**
	 * Сохраняет поле запроса
	 */
	function saveDbaseStructure($data)
	{
		if (empty($data['DbaseStructure_id']) || $data['DbaseStructure_id'] < 0 ) {
			$result = $this->createDbaseStructure($data);
		} else {
			$result =  $this->updateDbaseStructure($data);
		}

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Создает поле запроса
	 */
	function createDbaseStructure($data)
	{
		$query = "
			declare
				@DbaseStructure_id int,
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				insert into rls.exp_DbaseStructure
				(
					Query_id,
					Query_ColumnName,
					Dbase_ColumnName,
					Dbase_ColumnType,
					Dbase_ColumnLength,
					Dbase_ColumnPrecision,
					Description,
					Ord
				)
				values (
					:Query_id,
					:Query_ColumnName,
					:Dbase_ColumnName,
					:Dbase_ColumnType,
					:Dbase_ColumnLength,
					:Dbase_ColumnPrecision,
					:Description,
					:Ord
				)
				set nocount off;
				set @DbaseStructure_id = SCOPE_IDENTITY();
			end try

			begin catch
				set @Error_Code = ERROR_NUMBER();
				set @Error_Message = ERROR_MESSAGE();
			end catch

			select @DbaseStructure_id as DbaseStructure_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$response = $this->db->query($query, $data);

		return $response;
	}

	/**
	 * Обновляет поле запроса
	 */
	function updateDbaseStructure($data) {
		$query = "
			declare
				@DbaseStructure_id int,
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				update
					rls.exp_DbaseStructure with(rowlock)
				set
					Query_id = :Query_id,
					Query_ColumnName = :Query_ColumnName,
					Dbase_ColumnName = :Dbase_ColumnName,
					Dbase_ColumnType = :Dbase_ColumnType,
					Dbase_ColumnLength = :Dbase_ColumnLength,
					Dbase_ColumnPrecision = :Dbase_ColumnPrecision,
					Description = :Description,
					Ord = :Ord
				where
					DbaseStructure_id = :DbaseStructure_id;
				set nocount off;
				set @DbaseStructure_id = :DbaseStructure_id;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @DbaseStructure_id as DbaseStructure_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$response = $this->db->query($query, $data);

		//echo getDebugSql($query, $data);exit;
		//print_r($response->result('array'));exit;

		return $response;
	}

	/**
	 * Удаляет поле запроса
	 */
	function deleteDbaseStructure($data)
	{
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				delete
					from rls.exp_DbaseStructure with(rowlock)
				where
					DbaseStructure_id = :DbaseStructure_id
				set nocount off;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет запрос
	 */
	function deleteQuery($data)
	{
		$response = array(
			'Error_Code' => null,
			'Error_Msg' => null
		);

		$this->beginTransaction();

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				delete
					from rls.exp_Query with(rowlock)
				where
					Query_id = :Query_id
				set nocount off;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array('Query_id'=>$data['Query_id']));

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (удаление запроса)';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при удалении запроса';
			return array($response);
		}
		else if ( !empty($queryResponse[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $queryResponse[0]['Error_Msg'];
			return array($response);
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				delete
					from rls.exp_DbaseStructure with(rowlock)
				where
					Query_id = :Query_id
				set nocount off;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array('Query_id'=>$data['Query_id']));

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (удаление полей запроса)';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при удалении полей запроса';
			return array($response);
		}
		else if ( !empty($queryResponse[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $queryResponse[0]['Error_Msg'];
			return array($response);
		}

		$this->commitTransaction();

		return array($response);
	}
}