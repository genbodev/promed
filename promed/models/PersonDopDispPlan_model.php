<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonDopDispPlan_model - модель для работы с планом диспансеризации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class PersonDopDispPlan_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление плана
	 */
	function delete($data) {

		set_time_limit(0);
		
		$resp = $this->queryResult("
			select top 1
				PPL.PlanPersonList_id
			from
				v_PlanPersonList PPL with (nolock)
				left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
			where
				PPL.PersonDopDispPlan_id = :PersonDopDispPlan_id
				and PDDPS.PlanPersonListStatusType_id IN (2, 3)		
		", array(
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id']
		));

		if (!empty($resp[0]['PlanPersonList_id'])) {
			return array('Error_Msg' => 'Нельзя удалить план, т.к. он содержит записи со статусом "Принято ТФОМС" или "Отправлено в ТФОМС".');
		}

		/*$resp = $this->queryResult("
			select PlanPersonList_id from PlanPersonList with(nolock) where PersonDopDispPlan_id = :PersonDopDispPlan_id
		", $data);
		
		foreach($resp as $item) {
			$this->deletePlanPersonList(array(
				'PlanPersonList_id' => $item['PlanPersonList_id']
			));
		}*/

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec dbo.p_PersonDopDispPlan_delAll
				@PersonDopDispPlan_id = :PersonDopDispPlan_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список планов
	 */
	function loadList($data) {
	
		$filter = '(1 = 1)';
		$params = array();

		if (!empty($data['PersonDopDispPlan_Year'])) {
			$params['PersonDopDispPlan_Year'] = $data['PersonDopDispPlan_Year'];
			$filter .= ' and PDDP.PersonDopDispPlan_Year = :PersonDopDispPlan_Year';
		}
		
		if (!empty($data['DispClass_id'])) {
			$params['DispClass_id'] = $data['DispClass_id'];
			$filter .= ' and PDDP.DispClass_id = :DispClass_id';
		}
		
		if (isset($_SESSION['lpu_id']) && !empty($_SESSION['lpu_id'])) {
			$params['Lpu_id'] = $_SESSION['lpu_id'];
			$filter .= ' and PDDP.Lpu_id = :Lpu_id';
		}

		if (!empty($data['PersonDopDispPlanExport_expDateRange'][0]) || !empty($data['PersonDopDispPlanExport_expDateRange'][1])) {
			$filter_pddpe = "";
			if (!empty($data['PersonDopDispPlanExport_expDateRange'][0])) {
				$params['PersonDopDispPlanExport_expDate_From'] = $data['PersonDopDispPlanExport_expDateRange'][0];
				$filter_pddpe .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) >= :PersonDopDispPlanExport_expDate_From';
			}
			if (!empty($data['PersonDopDispPlanExport_expDateRange'][1])) {
				$params['PersonDopDispPlanExport_expDate_To'] = $data['PersonDopDispPlanExport_expDateRange'][1];
				$filter_pddpe .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) <= :PersonDopDispPlanExport_expDate_To';
			}
			$filter .= " and exists(
				select top 1
					pddpe.PersonDopDispPlanExport_id
				from
					v_PersonDopDispPlanLink pddpl (nolock)
					inner join v_PersonDopDispPlanExport pddpe (nolock) on pddpe.PersonDopDispPlanExport_id = pddpl.PersonDopDispPlanExport_id
				where
					pddpl.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
					{$filter_pddpe}
			)";
		}

		$sendToTfomsLpus = array();
		$ExchInspectPlan = $this->config->item('ExchInspectPlan');
		if (isset($ExchInspectPlan) && isset($ExchInspectPlan['allowed_lpus'])) {
			$sendToTfomsLpus = $ExchInspectPlan['allowed_lpus'];
		}
		if ( count($sendToTfomsLpus) == 0 ) {
			$sendToTfomsLpus[] = 'PDDP.Lpu_id';
		}
		$sendToTfomsLpus = implode(',', $sendToTfomsLpus);
		
		$countFilter = " and coalesce(PDDPS.PlanPersonListStatusType_id, 1) <> 4";
		if (in_array($this->getRegionNick(), ['penza', 'kz'])) {
			$countFilter = " and coalesce(PDDPS.PlanPersonListStatusType_id, 1) not in (4,5)";
		}
		
		$query = "
			select
				-- select
				PDDP.PersonDopDispPlan_id,
				convert(varchar(10), PDDP.PersonDopDispPlan_insDT, 104) as PersonDopDispPlan_insDT,
				DC.DispClass_id,
				DC.DispClass_Name,
				DCP.DispCheckPeriod_Name,
				PPL.count as PersonDopDispPlan_Count,
				PPL_Ready.count as PersonDopDispPlan_CountReady,
				PPL_Error.count as PersonDopDispPlan_CountError,
				PPL_ACC.count as PersonDopDispPlan_CountAccepted,
				convert(varchar(10), pddpe.PersonDopDispPlanExport_impDate, 104) as PersonDopDispPlanExport_impDate,
				case when exists (
					select top 1
						PPL.PlanPersonList_id
					from
						v_PlanPersonList PPL with (nolock)
						left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
					where
						PPL.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
						and PDDPS.PlanPersonListStatusType_id IN (2, 3)
				) then 1 else 2 end as deleteAccess,
				case when PDDP.Lpu_id not in ({$sendToTfomsLpus})
				then 1 else 2 end as exportToTfomsAccess
				-- end select
			from
				-- from
				PersonDopDispPlan PDDP with (nolock)
				inner join v_DispClass DC with (nolock) on DC.DispClass_id = PDDP.DispClass_id
				inner join v_DispCheckPeriod DCP with (nolock) on DCP.DispCheckPeriod_id = PDDP.DispCheckPeriod_id
				outer apply (
					select count(*) [count]
					from
						v_PlanPersonList PPL with (nolock)
						left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
					where
						PPL.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
						{$countFilter}
				) as PPL
				outer apply (
					select count(*) [count]
					from
						v_PlanPersonList PPL with (nolock)
						left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
					where
						PPL.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
						and PDDPS.PlanPersonListStatusType_id = 1
				) as PPL_Ready
				outer apply (
					select count(*) [count]
					from
						v_PlanPersonList PPL with (nolock)
						left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
					where
						PPL.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
						and PDDPS.PlanPersonListStatusType_id = 4
				) as PPL_Error
				outer apply (
					select count(*) [count]
					from
						v_PlanPersonList PPL with (nolock)
						left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
					where
						PPL.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
						and PDDPS.PlanPersonListStatusType_id = 3
				) as PPL_ACC
				outer apply (
					select top 1
						pddpe.PersonDopDispPlanExport_impDate
					from
						v_PersonDopDispPlanLink pddpl (nolock)
						inner join v_PersonDopDispPlanExport pddpe (nolock) on pddpe.PersonDopDispPlanExport_id = pddpl.PersonDopDispPlanExport_id
					where
						pddpl.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
					order by
						PersonDopDispPlanExport_impDate desc
				) pddpe
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				PDDP.PersonDopDispPlan_insDT desc
				-- end order by
		";
		
		if ($this->isDebug) {
			$debug_sql = getDebugSQL($query, $params);
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Удаление файла экспорта
	 */
	function deletePersonDopDispPlanExport($data)
	{
		// Только для записей сущности «файл экспорта», по которым не производился импорт ошибок (дата импорта пустая)
		$params = array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id']
		);

		$query = "
			select top 1
				pddpe.PersonDopDispPlanExport_id,
				pddpe.PersonDopDispPlanExport_FileName,
				case when pddpe.PersonDopDispPlanExport_impDate is not null then 1 else 0 end as imported,
				pddpl.cnt as count,
				pddpe.PersonDopDispPlanExport_IsUsed,
				pddpe.PersonDopDispPlanExport_IsCreatedTFOMS,
				pplst.PlanPersonListStatusType_Code
			from
				v_PersonDopDispPlanExport pddpe (nolock)
				outer apply (
					select
						count(*) as cnt
					from
						v_PersonDopDispPlanLink pddpl (nolock)
					where
						pddpl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id
				) pddpl
				outer apply (
					select top 1 case 
						when pddpl.cnt > 0 and exists(
							select * from v_PlanPersonList ppl with(nolock)
							inner join v_PlanPersonListStatus ppls with(nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
							where ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id and ppls.PlanPersonListStatusType_id = 2
						) then 2
						when pddpl.cnt > 0 and not exists(
							select * from v_PlanPersonList ppl with(nolock)
							inner join v_PlanPersonListStatus ppls with(nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
							where ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id and ppls.PlanPersonListStatusType_id <> 3
						) then 3
					end as PlanPersonListStatusType_id
				) pddpes
				left join v_PlanPersonListStatusType pplst (nolock) on pplst.PlanPersonListStatusType_id = pddpes.PlanPersonListStatusType_id
			where
				pddpe.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
		";

		$resp = $this->queryResult($query, $params);

		if (!empty($resp[0]['PersonDopDispPlanExport_id'])) {

			if ($resp[0]['PersonDopDispPlanExport_IsCreatedTFOMS'] == 2 && $resp[0]['PlanPersonListStatusType_Code'] == 2) {
				return array('Error_Msg' => 'Файл отправлен в ТФОМС, удаление недоступно. Повторите попытку позднее.');
			}
			if ($resp[0]['PersonDopDispPlanExport_IsUsed'] == 1) {
				return array('Error_Msg' => 'Файл формируется, удаление недоступно. Повторите попытку позднее.');
			}

			if (empty($data['ignoreMultiplePlans']) && !empty($resp[0]['count']) && $resp[0]['count'] > 1) {
				// Если найдено более 1 связанного плана, то показывать пользователю предупреждение «При удалении информации об экспорте, признак отправки в ТФОМС будет снят со всех записей планов, включенных в файл %Имя файла%. Продолжить удаление? Ок. Отмена»
				return array(
					'Error_Msg' => '',
					'Alert_Msg' => 'При удалении информации об экспорте, признак отправки в ТФОМС будет снят со всех записей планов, включенных в файл ' . $resp[0]['PersonDopDispPlanExport_FileName'] . '. Продолжить удаление?'
				);
			}

			$this->beginTransaction();
			// Удалить запись и у всех записей сущности «Человек в плане», связанных с выбранным файлом изменить статус на «Новый».
			$resp_ppl = $this->queryResult("
				select
					ppl.PlanPersonList_id, ppls.PlanPersonListStatusType_id
				from
					v_PlanPersonList ppl (nolock)
					left join v_PlanPersonListStatus ppls (nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
				where
					ppl.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id				
			", array(
				'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id']
			));

			if (is_array($resp_ppl)) {
				foreach ($resp_ppl as $one_ppl) {
					// неошибочные переводим в новые
					if ($one_ppl['PlanPersonList_id'] != 4) {
						$PlanPersonListStatusType_id = 1; // Новая
						$resp_set = $this->setPlanPersonListStatus(array(
							'PlanPersonList_id' => $one_ppl['PlanPersonList_id'],
							'PlanPersonListStatusType_id' => $PlanPersonListStatusType_id,
							'pmUser_id' => $data['pmUser_id']
						));

						if (!empty($resp_set[0]['Error_Msg'])) {
							$this->rollbackTransaction();
							return $resp_set;
						}
					} else {
						// у ошибочных только зануляем PersonDopDispPlanExport_id
						$this->db->query("
							update PlanPersonList with (rowlock) set PersonDopDispPlanExport_id = null where PlanPersonList_id = :PlanPersonList_id
						", $queryParams);
					}
				}
			}
			
			// Удаление PersonDopDispPlanLink и зануление ExportErrorPlanDD теперь в p_PersonDopDispPlanExport_del

			// Удаляем файл экспорта
			$query = "
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_PersonDopDispPlanExport_del
					@PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$resp_del = $this->queryResult($query, array(
				'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id']
			));

			if (!empty($resp_del[0]['Error_Msg'])) {
				$this->rollbackTransaction();
			} else {
				$this->commitTransaction();
			}

			return $resp_del;
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по файлу экспорта.');
		}
	}

	/**
	 * Возвращает список экспортов планов
	 * NB: Для Вологды переопределено в региональной модели
	 */
	function loadPersonDopDispPlanExportList($data) {

		$filter = '(1 = 1)';
		$params = array();
		
		if (empty($data['PersonDopDispPlan_id']) && empty($data['PersonDopDispPlan_ids'])) {
			return array('Error_Msg' => 'Не указан идентификатор плана');
		}

		if (!empty($data['PersonDopDispPlan_id'])) {
			$params['PersonDopDispPlan_id'] = $data['PersonDopDispPlan_id'];
			$filter .= ' and pddpl.PersonDopDispPlan_id = :PersonDopDispPlan_id';
		}
		elseif (!empty($data['PersonDopDispPlan_ids'])) {
			$filter .= ' and pddpl.PersonDopDispPlan_id in ('.join(',',$data['PersonDopDispPlan_ids']).')';
		}

		if (!empty($data['PersonDopDispPlanExport_expDateRange'][0])) {
			$params['PersonDopDispPlanExport_expDate_From'] = $data['PersonDopDispPlanExport_expDateRange'][0];
			$filter .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) >= :PersonDopDispPlanExport_expDate_From';
		}
		if (!empty($data['PersonDopDispPlanExport_expDateRange'][1])) {
			$params['PersonDopDispPlanExport_expDate_To'] = $data['PersonDopDispPlanExport_expDateRange'][1];
			$filter .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) <= :PersonDopDispPlanExport_expDate_To';
		}

		$query = "
			select
				-- select
				pddpe.PersonDopDispPlanExport_id,
				pddpe.PersonDopDispPlanExport_FileName,
				convert(varchar(10), pddpe.PersonDopDispPlanExport_expDate, 104) as PersonDopDispPlanExport_expDate,
				ppl.cnt as PersonDopDispPlanExport_Count,
				convert(varchar(10), pddpe.PersonDopDispPlanExport_impDate, 104) as PersonDopDispPlanExport_impDate,
				pddpe.PersonDopDispPlanExport_Year,
				pddpe.PersonDopDispPlanExport_Month,
				cast(pddpe.PersonDopDispPlanExport_Year as varchar(4)) + RIGHT('00' + cast(pddpe.PersonDopDispPlanExport_Month as varchar(2)), 2) as PersonDopDispPlanExport_Period, /* чтобы сортировка отрабатывала */
				eepdd.cnt as PersonDopDispPlanExport_CountErr,
				pddpe.PersonDopDispPlanExport_isUsed,
				pddpe.PersonDopDispPlanExport_DownloadLink,
				pddpe.PersonDopDispPlanExport_PackNum,
				pplst.PlanPersonListStatusType_id as PersonDopDispPlanExportStatus_id,
				pplst.PlanPersonListStatusType_Code as PersonDopDispPlanExportStatus_Code,
				pplst.PlanPersonListStatusType_Name as PersonDopDispPlanExportStatus_Name
				-- end select
			from
				-- from
				v_PersonDopDispPlanLink pddpl (nolock)
				inner join v_PersonDopDispPlanExport pddpe (nolock) on pddpe.PersonDopDispPlanExport_id = pddpl.PersonDopDispPlanExport_id
				outer apply (
					select
						count(*) as cnt
					from
						v_PlanPersonList ppl (nolock)
						inner join v_PlanPersonListStatus ppls with(nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
					where
						ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id
						and ppl.PersonDopDispPlan_id = pddpl.PersonDopDispPlan_id
						and ppls.PlanPersonListStatusType_id in (2,3)
				) ppl
				outer apply (
					select
						count(*) as cnt
					from
						v_ExportErrorPlanDD eepdd (nolock)
					where
						eepdd.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id
				) eepdd
				outer apply (
					select top 1 case 
						when ppl.cnt > 0 and exists(
							select * from v_PlanPersonList ppl with(nolock)
							inner join v_PlanPersonListStatus ppls with(nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
							where ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id and ppls.PlanPersonListStatusType_id = 2
						) then 2
						when ppl.cnt > 0 and not exists(
							select * from v_PlanPersonList ppl with(nolock)
							inner join v_PlanPersonListStatus ppls with(nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
							where ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id and ppls.PlanPersonListStatusType_id <> 3
						) then 3
					end as PlanPersonListStatusType_id
				) pddpes
				left join v_PlanPersonListStatusType pplst with(nolock) on pplst.PlanPersonListStatusType_id = pddpes.PlanPersonListStatusType_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				pddpe.PersonDopDispPlanExport_expDate desc
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Возвращает список ошибок экспортов планов
	 * NB: Для Вологды переопределено в региональной модели
	 */
	function loadExportErrorPlanDDList($data) {

		$filter = '(1 = 1)';
		$params = array();

		if (!empty($data['PersonDopDispPlanExport_id'])) {
			$params['PersonDopDispPlanExport_id'] = $data['PersonDopDispPlanExport_id'];
			$filter .= ' and eepdd.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id';
		}

		$query = "
			select
				-- select
				eepdd.ExportErrorPlanDD_id,
				ppl.PlanPersonList_ExportNum,
				ISNULL(PS.Person_SurName, '') + ISNULL(' ' + PS.Person_FirName, '') + ISNULL(' ' + PS.Person_SecName, '') as Person_Fio,
				eepddt.ExportErrorPlanDDType_Code,
				ISNULL(eepddt.ExportErrorPlanDDType_Name, eepdd.ExportErrorPlanDD_Description) as ExportErrorPlanDDType_Name
				-- end select
			from
				-- from
				v_ExportErrorPlanDD eepdd with (nolock)
				left join v_ExportErrorPlanDDType eepddt with (nolock) on eepddt.ExportErrorPlanDDType_id = eepdd.ExportErrorPlanDDType_id
				left join v_PlanPersonList ppl with (nolock) on ppl.PlanPersonList_id = eepdd.PlanPersonList_id
				left join v_PersonState ps with (nolock) on ps.Person_id = ppl.Person_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				eepdd.ExportErrorPlanDD_insDT desc
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Импорт данных плана
	 */
	function importPersonDopDispPlan($data)
	{
		$upload_path = './'.IMPORTPATH_ROOT.$data['Lpu_id'].'/';
		$allowed_types = explode('|','zip|xml');

		set_time_limit(0);

		if ( !isset($_FILES['File'])) {
			return array('Error_Msg' => 'Не выбран файл!');
		}

		if ( !is_uploaded_file($_FILES['File']['tmp_name']) ) {
			$error = (!isset($_FILES['File']['error'])) ? 4 : $_FILES['File']['error'];

			switch ( $error ) {
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

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['File']['name']);
		$file_data['file_ext'] = end($x);
		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			return array('Error_Msg' => 'Данный тип файла не разрешен.');
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			return array('Error_Msg' => 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			return array('Error_Msg' => 'Загрузка файла невозможна из-за прав пользователя.');
		}

		$fileList = array();

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$fileList[] = $_FILES['File']['name'];

			if ( !move_uploaded_file($_FILES["File"]["tmp_name"], $upload_path.$_FILES['File']['name']) ) {
				return array('Error_Msg' => 'Не удаётся переместить файл.');
			}
		}
		else {
			$zip = new ZipArchive;

			if ( $zip->open($_FILES["File"]["tmp_name"]) === TRUE ) {
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$fileList[] = $zip->getNameIndex($i);
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}

			unlink($_FILES["File"]["tmp_name"]);
		}

		$xmlfile = '';

		libxml_use_internal_errors(true);

		foreach ( $fileList as $filename ) {
			$xmlfile = $filename;
		}

		if ( empty($xmlfile) ) {
			return array('Error_Msg' => 'Файл не является файлом для импорта ошибок плана проф. мероприятий.');
		}

		if (mb_strpos($xmlfile, "ERR") === false) {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Имя файла не соответствует установленному формату. Выберите другой файл.');
		}

		$xml_string = file_get_contents($upload_path . $xmlfile);

		// Структура должна соответствовать xsd схеме для файла-ошибок.
		$xml = new DOMDocument();
		$xml->loadXML($xml_string);
		$xsd_tpl = $_SERVER['DOCUMENT_ROOT'].'/documents/xsd/pddp_err.xsd';
		if (!$xml->schemaValidate($xsd_tpl)) {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Структура файла не соответствует установленному формату. Выберите другой файл.');
		}
		unset($xml);

		$xml = new SimpleXMLElement($xml_string);
		$fname = $xml->FNAME_I->__toString();

		// o Поиск в БД записи сущности «Файл экспорта». по тегу FNAME_I. FNAME_I  передается в текстовом формате PROF_PiNiPpNpSN_YYMMN.  Поиск записи по следующим параметрам
		if (preg_match('/PROF\_M([0-9]*)T59.*?\_([0-9]{2})([0-9]{2})([0-9]*)/ui', $fname, $match)) {
			//  YY  поиск соответствующих значений по «отчетный год»
			//  ММ – поиск соответствующих значений по «отчетный месяц»
			//  N - поиск соответствующих значений по порядковому номеру.
			//  Ni – поиск по реестровому номеру МО

			$Lpu_f003mcod = $match[1];
			$PersonDopDispPlanExport_Year = '20'.$match[2];
			$PersonDopDispPlanExport_Month = intval($match[3]);
			$PersonDopDispPlanExport_PackNum = $match[4];

			$resp_pddpe = $this->queryResult("
				select top 1
					pddpe.PersonDopDispPlanExport_id
				from
					v_PersonDopDispPlanExport pddpe (nolock)
					inner join v_Lpu l (nolock) on l.Lpu_id = pddpe.Lpu_id 
				where
					l.Lpu_f003mcod = :Lpu_f003mcod
					and l.Lpu_id = :Lpu_id
					and pddpe.PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year
					and pddpe.PersonDopDispPlanExport_Month = :PersonDopDispPlanExport_Month
					and pddpe.PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum
			", array(
				'Lpu_f003mcod' => $Lpu_f003mcod,
				'Lpu_id' => $data['Lpu_id'],
				'PersonDopDispPlanExport_Year' => $PersonDopDispPlanExport_Year,
				'PersonDopDispPlanExport_Month' => $PersonDopDispPlanExport_Month,
				'PersonDopDispPlanExport_PackNum' => $PersonDopDispPlanExport_PackNum
			));

			if (!empty($resp_pddpe[0]['PersonDopDispPlanExport_id'])) {
				// o Если запись сущности найдена, то устанавливается дата импорта=текущая дата.
				$this->db->query("update PersonDopDispPlanExport with (rowlock) set PersonDopDispPlanExport_impDate = dbo.tzGetDate() where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id", array(
					'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id']
				));

				foreach ( $xml->ZAP_OSHIB as $oneoshib ) {
					// Для записей из плана (поиск по порядковому номеру по записям сущности «Человек в плане» значения тега NOMER_Z)
					$NOMER_Z = $oneoshib->NOMER_Z->__toString();
					$OSHIB = $oneoshib->OSHIB->__toString();

					// ищем запись
					$resp_ppl = $this->queryResult("
						select
							PlanPersonList_id
						from
							v_PlanPersonList (nolock)
						where
							PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
							and PlanPersonList_ExportNum = :PlanPersonList_ExportNum
					", array(
						'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
						'PlanPersonList_ExportNum' => $NOMER_Z
					));

					if (!empty($resp_ppl[0]['PlanPersonList_id'])) {
						// Сохранить ошибки
						$ExportErrorPlanDDType_id = $this->getFirstResultFromQuery("
							select
								ExportErrorPlanDDType_id
							from
								v_ExportErrorPlanDDType (nolock)
							where
								ExportErrorPlanDDType_Code = :ExportErrorPlanDDType_Code
						", array(
							'ExportErrorPlanDDType_Code' => $OSHIB
						));

						$this->saveExportErrorPlanDD(array(
							'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
							'ExportErrorPlanDDType_id' => $ExportErrorPlanDDType_id,
							'PlanPersonList_id' => $resp_ppl[0]['PlanPersonList_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					} else {
						return array('Error_Msg' => 'Запись с NOMER_Z = ' . $NOMER_Z . ' не найдена');
					}
				}

				// изменить статус на «Ошибки» если вернулись ошибки, для всех остальных установить статус «Принят ТФОМС»)
				$resp_ppl = $this->queryResult("
					select
						ppl.PlanPersonList_id,
						eepdd.ExportErrorPlanDD_id
					from
						v_PlanPersonList ppl (nolock)
						outer apply (
							select top 1
								ExportErrorPlanDD_id
							from
								v_ExportErrorPlanDD eepdd (nolock)
							where
								eepdd.PersonDopDispPlanExport_id = ppl.PersonDopDispPlanExport_id
								and eepdd.PlanPersonList_id = ppl.PlanPersonList_id
						) eepdd
					where
						ppl.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id						
				", array(
					'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id']
				));

				if (is_array($resp_ppl)) {
					foreach ($resp_ppl as $one_ppl) {
						$PlanPersonListStatusType_id = 3; // Принята ТФОМС
						if (!empty($one_ppl['ExportErrorPlanDD_id'])) {
							$PlanPersonListStatusType_id = 4; // Ошибки
						}
						$this->setPlanPersonListStatus(array(
							'PlanPersonList_id' => $one_ppl['PlanPersonList_id'],
							'PlanPersonListStatusType_id' => $PlanPersonListStatusType_id,
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}
			} else {
				// Иначе показать сообщение об ошибке «Файл экспорта не найден или удален»
				return array('Error_Msg' => 'Файл экспорта не найден или удален');
			}
		} else {
			return array('Error_Msg' => 'FNAME_I имеет не корректное значение');
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Возвращает план
	 */
	function load($data) {

		$query = "
			select
				-- select
				PDDP.PersonDopDispPlan_id,
				PDDP.DispClass_id,
				PDDP.DispCheckPeriod_id
				-- end select
			from
				-- from
				v_PersonDopDispPlan PDDP with(nolock)
				-- end from
			where
				-- where
				PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id
				-- end where
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список периодов
	 */
	function getDispCheckPeriod($data) {
		
		$filter = "(1=1)";
		$data['Lpu_id'] = $_SESSION['lpu_id'];
		
		// первое добавление, исключаем занятые периоды
		if (empty($data['PersonDopDispPlan_id'])) {
			$filter = "PDDP.PersonDopDispPlan_id is null";
		}
		// редактирование
		else {
			$filter = "(PDDP.PersonDopDispPlan_id is null or PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id) ";
			$filter .= " and YEAR(DCP.DispCheckPeriod_begDate) = (select PersonDopDispPlan_Year from v_PersonDopDispPlan with (nolock) where PersonDopDispPlan_id = :PersonDopDispPlan_id) ";
		}
		
		// по видам перидов
		$filter .= " and (DCP.PeriodCap_id = pc.PeriodCap_id or pc.PeriodCap_id is null)";
		
		// конкретный период
		if (!empty($data['DispCheckPeriod_id'])) {
			$filter = " DCP.DispCheckPeriod_id = :DispCheckPeriod_id";
		}
		
		// особые условия для переноса
		if ($data['isForTransfer'] == 1) {
			$filter = "
				YEAR(DCP.DispCheckPeriod_begDate) = (select PersonDopDispPlan_Year from v_PersonDopDispPlan with (nolock) where PersonDopDispPlan_id = :PersonDopDispPlan_id) and 
				PDDP.PersonDopDispPlan_id is not null and 
				PDDP.PersonDopDispPlan_id != :PersonDopDispPlan_id
			";
		} else if ($data['isForRetryInclude'] == 1) {
			$filter = "
				YEAR(DCP.DispCheckPeriod_begDate) = (select PersonDopDispPlan_Year from v_PersonDopDispPlan with (nolock) where PersonDopDispPlan_id = :PersonDopDispPlan_id) and 
				PDDP.PersonDopDispPlan_id is not null
			";
		}
		
		if ($this->regionNick == 'vologda') {
			if (!empty($data['DispClass_id'])) {
				$filter .= " and DCP.PeriodCap_id = 4";
			}
		} else {
			$filter .= " and DCP.PeriodCap_id != 1";
		}

		if ($data['DispClass_id'] == 1) {
			if ($this->regionNick == 'pskov') {
				$filter .= " and DCP.PeriodCap_id in (3, 4)";
			} 
		}
		
		// особые условия для экспорта
		if (!empty($data['PersonDopDispPlan_ids']) && count($data['PersonDopDispPlan_ids'])) {
			if (count($data['PersonDopDispPlan_ids']) == 1) {
				// При экспорте одного плана по умолчанию в поле устанавливается значение периода плана
				$filter = "PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id";
				$data['PersonDopDispPlan_id'] = $data['PersonDopDispPlan_ids'][0];
			}
			else {
				$resp_dc = $this->queryResult("select distinct pddp.DispClass_id from v_PersonDopDispPlan pddp (nolock) where pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')");
				if (count($resp_dc) > 1 && $this->regionNick != 'astra') {
					// Если экспортируются планы с типами ДВН и ПОВН одновременно и с разными периодами. Тогда поле оставлять пустым.
					return array();
				} else {
					// Если экспортируется несколько планов одного типа (ДВН или ПОВН) одновременно с разными периодами, тогда в поле устанавливается значение год отчетного периода.
					$filter = "DCP.PeriodCap_id = 1 and year(DCP.DispCheckPeriod_begDate) = (select PersonDopDispPlan_Year from v_PersonDopDispPlan with (nolock) where PersonDopDispPlan_id = :PersonDopDispPlan_id)";
					$data['PersonDopDispPlan_id'] = $data['PersonDopDispPlan_ids'][0];
				}
			}
		}

		$query = "
			select
				-- select
				DCP.DispCheckPeriod_id,
				DCP.PeriodCap_id,
				DCP.DispCheckPeriod_Name,
				PDDP.PersonDopDispPlan_id,
				YEAR(DCP.DispCheckPeriod_begDate) as DispCheckPeriod_Year,
				convert(varchar(10), DCP.DispCheckPeriod_begDate, 120) as DispCheckPeriod_begDate,
				convert(varchar(10), DCP.DispCheckPeriod_endDate, 120) as DispCheckPeriod_endDate
				-- end select
			from
				-- from
				v_DispCheckPeriod DCP with(nolock)
				left join v_PersonDopDispPlan PDDP with (nolock) on PDDP.DispCheckPeriod_id = DCP.DispCheckPeriod_id and PDDP.DispClass_id = :DispClass_id and PDDP.Lpu_id = :Lpu_id
				outer apply (
					select top 1 t1.PeriodCap_id, YEAR(t1.DispCheckPeriod_begDate) as DispCheckPeriod_Year
					from v_DispCheckPeriod t1 with(nolock)
					inner join v_PersonDopDispPlan t2 with (nolock) on t2.DispCheckPeriod_id = t1.DispCheckPeriod_id 
					where t2.DispClass_id = :DispClass_id and t2.Lpu_id = :Lpu_id and YEAR(t1.DispCheckPeriod_begDate) = YEAR(DCP.DispCheckPeriod_begDate)
				) pc
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by 
				DCP.DispCheckPeriod_begDate, DCP.PeriodCap_id
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет план
	 */
	function save($data) {
	
		// Проверка дубликатов
		$response = $this->checkPersonDopDispPlanDoubles($data);
		if ( $response ) {
			throw new Exception('Сохранение плана невозможно т.к. план с таким типом и периодом уже существует');
		}

		$params = array(
			'PersonDopDispPlan_id' => empty($data['PersonDopDispPlan_id']) ? null : $data['PersonDopDispPlan_id'],
			'DispClass_id' => $data['DispClass_id'],
			'DispCheckPeriod_id' => $data['DispCheckPeriod_id'],
			'PersonDopDispPlan_Year' => $data['PersonDopDispPlan_Year'],
			'Lpu_id' => $_SESSION['lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($params['PersonDopDispPlan_id']) ? 'p_PersonDopDispPlan_ins' : 'p_PersonDopDispPlan_upd';

		$query = "
			declare
				@PersonDopDispPlan_id bigint = :PersonDopDispPlan_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@PersonDopDispPlan_id = @PersonDopDispPlan_id output,
				@DispClass_id = :DispClass_id,
				@DispCheckPeriod_id = :DispCheckPeriod_id,
				@PersonDopDispPlan_Year = :PersonDopDispPlan_Year,
				@Lpu_id = :Lpu_id,
				@DispDopClass_id = null,
				@PersonDopDispPlan_Plan = 0,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PersonDopDispPlan_id as PersonDopDispPlan_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка на дубли
	 */
	function checkPersonDopDispPlanDoubles ($data) {
		
		$query = "
			select top 1 PersonDopDispPlan_id
			from v_PersonDopDispPlan with(nolock)
			where 
				DispClass_id = :DispClass_id and 
				DispCheckPeriod_id = :DispCheckPeriod_id and 
				Lpu_id = :Lpu_id
		";
		
		if (!empty($data['PersonDopDispPlan_id'])) $query .= "and PersonDopDispPlan_id != :PersonDopDispPlan_id";
		
		$PersonDopDispPlan = $this->getFirstRowFromQuery($query, 
			array(
				'DispClass_id' => $data['DispClass_id'],
				'DispCheckPeriod_id' => $data['DispCheckPeriod_id'],
				'Lpu_id' => $data['Lpu_id'],
				'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id']
			)
		);
		
		return ($PersonDopDispPlan !== false);
	}

	/**
	 * Возвращает список людей в плане
	 */
	function loadPlanPersonList($data) {
	
		$filter = '';
		$params = array();
		$params['PersonDopDispPlan_id'] = $data['PersonDopDispPlan_id'];
		
		// ФИО
		if (!empty($data['Person_FIO'])) {
			$filter .= " and PS.Person_SurName like :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'].'%';
		}
		
		// Пол
		if (!empty($data['Sex_id'])) {
			$filter .= " and PS.Sex_id = :Sex_id";
			$params['Sex_id'] = $data['Sex_id'];
		}

		// Статус записи
		if (!empty($data['PlanPersonListStatusType_id'])) {
			$filter .= " and ISNULL(PDDPS.PlanPersonListStatusType_id, 1) = :PlanPersonListStatusType_id";
			$params['PlanPersonListStatusType_id'] = $data['PlanPersonListStatusType_id'];
		}
		
		// ДР
		if (!empty($data['Person_Birthday'])) {
			$filter .= " and PS.Person_BirthDay = :Person_Birthday";
			$params['Person_Birthday'] = $data['Person_Birthday'];
		}
		
		// Возраст пациента (с)
		if (!empty($data['PersonAge_Max'])) {
			$filter .= " and dbo.Age2(PS.Person_BirthDay, @getDT) <= :PersonAge_Max";
			$params['PersonAge_Max'] = $data['PersonAge_Max'];
		}

		// Возраст пациента (по)
		if (!empty($data['PersonAge_Min'])) {
			$filter .= " and dbo.Age2(PS.Person_BirthDay, @getDT) >= :PersonAge_Min";
			$params['PersonAge_Min'] = $data['PersonAge_Min'];
		}

		// Факт
		if (!empty($data['Fact_id'])) {
			if ($data['Fact_id'] == 1) { // прошел
				$filter .= " and epld.EvnPLDisp_id is not null";
			}
			if ($data['Fact_id'] == 2) { // не прошел
				$filter .= " and epld.EvnPLDisp_id is null";
			}
		}

		// Номер пакета
		if (!empty($data['PacketNumber'])) {
			$filter .= " and exists(
					select top 1 pddpl.PersonDopDispPlanLink_id
					from v_PersonDopDispPlanLink pddpl with (nolock)
						inner join v_PersonDopDispPlanExport pddpe (nolock) on pddpe.PersonDopDispPlanExport_id = pddpl.PersonDopDispPlanExport_id
					where pddpl.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
						and pddpe.PersonDopDispPlanExport_PackNum = :PacketNumber
				)
			";
			$params['PacketNumber'] = $data['PacketNumber'];
		}
		
		// Год плана и тип осмотра.
		$resp_vol = $this->queryResult("
			SELECT TOP 1
				PDDP.PersonDopDispPlan_Year as persondopdispplan_year,
				PDDP.DispClass_id as dispclass_id
			FROM
				v_PersonDopDispPlan PDDP with (nolock)
			WHERE
				PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id
		", [
			'PersonDopDispPlan_id' => $params['PersonDopDispPlan_id']
		]);
		
		$params['PersonDopDispPlan_Year'] = date('Y');
		$params['PersonDopDispPlan_YearEndDate'] = date('Y') . '-12-31';
		// Тип осмотра.
		$DispClass_id = NULL;
		
		if (!empty($resp_vol[0]['persondopdispplan_year'])) {
			$params['PersonDopDispPlan_Year'] = $resp_vol[0]['persondopdispplan_year'];
			$params['PersonDopDispPlan_YearEndDate'] = $params['PersonDopDispPlan_Year'] . '-12-31';
			$DispClass_id = $resp_vol[0]['dispclass_id'];
		}
		
		// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
		if (!in_array($this->getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])) {
			// Тип осмотра: "1. Дисп-ция взр. населения 1-ый этап" [1].
			if ($DispClass_id == 1) {
				$filter .= "
					-- от 18 до 39 лет.
					AND dbo.Age2(PS.Person_BirthDay, :PersonDopDispPlan_YearEndDate) BETWEEN 18 and 39
					-- нет карты диспансеризации в указанному году и за два предыдущих года.
					AND (not exists (
						SELECT top 1 EvnPLDispProf_id
						FROM v_EvnPLDispProf (nolock)
						WHERE
							(YEAR(EvnPLDispProf_disDT) BETWEEN :PersonDopDispPlan_Year - 2 AND :PersonDopDispPlan_Year)
							AND Person_id = PS.Person_id))
				";
			}
		}
		
		$query = "
			--variables
			declare @getDT datetime = dbo.tzGetDate();
			--end variables
			select
				-- select
				PPL.PlanPersonList_id,
				PS.Person_id,
				RTRIM(PS.Person_SurName) + ' ' + isnull(PS.Person_FirName, '') + ' ' + isnull(PS.Person_SecName, '') as Person_FIO,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				dbo.Age2(PS.Person_BirthDay, @getDT) as Person_Age,
				case when epld.EvnPLDisp_id is not null then 'true' else 'false' end as IsDisp,
				2 as IsChecked,
				ISNULL(PDDPS.PlanPersonListStatusType_id, 1) as PlanPersonListStatusType_id,
				PDDPST.PlanPersonListStatusType_Name,
				STUFF(
					(SELECT DISTINCT
						', '+rtrim(isnull(eepddt.ExportErrorPlanDDType_Code,''))
					FROM
						v_ExportErrorPlanDD eepdd with (nolock)
						left join v_ExportErrorPlanDDType eepddt with (nolock) on eepddt.ExportErrorPlanDDType_id = eepdd.ExportErrorPlanDDType_id
					WHERE
						eepdd.PlanPersonList_id = PPL.PlanPersonList_id
					FOR XML PATH ('')
					), 1, 2, ''
				) as ExportErrorPlanDDType_Code,
				eepdd.ExportErrorPlanDD_Description,
				SLP.ExportResult,
				SLP.ExportData,
				SLP.ImportData,
				convert(varchar(10), PlanPersonListStatus_setDate, 104) as PlanPersonListStatus_setDate
				-- end select
			from
				-- from
				v_PersonDopDispPlan PDDP with (nolock)
				inner join v_DispCheckPeriod DCP with (nolock) on DCP.DispCheckPeriod_id = PDDP.DispCheckPeriod_id
				inner join v_PlanPersonList PPL with (nolock) on PPL.PersonDopDispPlan_id = PDDP.PersonDopDispPlan_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = PPL.Person_id
				outer apply (
					select top 1 epld.EvnPLDisp_id
					from v_EvnPLDisp epld with (nolock)
					where 
						epld.DispClass_id = PDDP.DispClass_id 
						and epld.Person_id = PPL.Person_id
						and epld.EvnPLDisp_setDate between DCP.DispCheckPeriod_begDate and DCP.DispCheckPeriod_endDate
				) as epld
				outer apply (
					select top 1 eepdd.ExportErrorPlanDD_Description
					from v_ExportErrorPlanDD eepdd with (nolock)
					where eepdd.PlanPersonList_id = PPL.PlanPersonList_id
				) as eepdd
				left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
				left join v_PlanPersonListStatusType pddpst (nolock) on pddpst.PlanPersonListStatusType_id = ISNULL(PDDPS.PlanPersonListStatusType_id, 1)
				outer apply (
					select top 1
						SLDL.ServiceListDetailLog_Message as ExportResult,
						SP_Export.ServicePackage_Data as ExportData,
						SP_Import.ServicePackage_Data as ImportData
					from
						stg.v_ServiceList SL with(nolock)
						inner join stg.v_ServiceListLog SLL with(nolock) on SLL.ServiceList_id = SL.ServiceList_id
						inner join stg.v_ServiceListPackage SLP with(nolock) on SLP.ServiceListLog_id = SLL.ServiceListLog_id
						outer apply (
							select top 1 SLDL.*
							from stg.v_ServiceListDetailLog SLDL with(nolock) 
							where SLDL.ServiceListLog_id = SLL.ServiceListLog_id
							and SLDL.ServiceListPackage_id = SLP.ServiceListPackage_id
						) SLDL
						outer apply (
						    select top 1 SP.ServicePackage_Data
                            from stg.v_ServicePackage SP with(nolock)
                            where SP.ServiceListPackage_id = SLP.ServiceListPackage_id
                            and isnull(SP.ServicePackage_IsResp, 1) = 1
						) SP_Export
						outer apply (
						    select top 1 SP.ServicePackage_Data
                            from stg.v_ServicePackage SP with(nolock)
                            where SP.ServiceListPackage_id = SLP.ServiceListPackage_id
                            and SP.ServicePackage_IsResp = 2
						) SP_Import
					where
						SL.ServiceList_SysNick = 'ExchInspectPlan'
						and SLP.ServiceListPackage_ObjectName = 'PlanPersonList'
						and SLP.ServiceListPackage_ObjectID = PPL.PlanPersonList_id
					order by
						SLP.ServiceListPackage_id desc
				) SLP
				-- end from
			where
				-- where
				PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id
				{$filter}
				-- end where
			order by
				-- order by
				PPL.PlanPersonList_insDT desc
				-- end order by
		";

		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		if (!is_array($response)) {
		    return $response;
        }

		$process = function($data, $result = '') use(&$process) {
		    foreach($data as $key => $value) {
                if (is_array($value)) {
                    $result = $process($value, $result);
                } else {
                    $result .= "{$key}: {$value}<br/>";
                }
            }
		    return $result;
        };

		foreach($response['data'] as &$item) {
		    if (!empty($item['ExportData'])) {
		        $item['ExportData'] = $process(json_decode($item['ExportData'], true));
            }
		    if (!empty($item['ImportData'])) {
                $item['ImportData'] = $process(json_decode($item['ImportData'], true));
            }
        }

		return $response;
	}
	
	/**
	 * Добавляет людей в план
	 */
	function savePlanPersonList($data) {
		
		$data['Person_ids'] = array_unique($data['Person_ids']);
		foreach ($data['Person_ids'] as $Person_id) {
			$this->saveNewPlanPersonList(array(
				'PlanPersonList_id' => null,
				'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
				'Person_id' => $Person_id,
				'Lpu_id' => $_SESSION['lpu_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array('success' => true);
	}

	/**
	 * Сохранение людей в план
	 */
	function saveNewPlanPersonList($data) {
		if (empty($data['PlanPersonListStatusType_id'])) {
			$data['PlanPersonListStatusType_id'] = 1;
		}
		
		$query = "
			declare
				@PlanPersonList_id bigint = :PlanPersonList_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PlanPersonList_ins
				@PlanPersonList_id = @PlanPersonList_id output,
				@PersonDopDispPlan_id = :PersonDopDispPlan_id,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PlanPersonList_id as PlanPersonList_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->queryResult($query, array(
			'PlanPersonList_id' => $data['PlanPersonList_id'],
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp[0]['PlanPersonList_id'])) {
			$this->setPlanPersonListStatus(array(
				'PlanPersonList_id' => $resp[0]['PlanPersonList_id'],
				'PlanPersonListStatusType_id' => $data['PlanPersonListStatusType_id'], // Новая
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return $resp;
	}

	/**
	 * Сохранение ошибок
	 */
	function saveExportErrorPlanDD($data) {
		$query = "
			declare
				@ExportErrorPlanDD_id bigint = :ExportErrorPlanDD_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_ExportErrorPlanDD_ins
				@ExportErrorPlanDD_id = @ExportErrorPlanDD_id output,
				@PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id,
				@ExportErrorPlanDDType_id = :ExportErrorPlanDDType_id,
				@ExportErrorPlanDD_Description = :ExportErrorPlanDD_Description,
				@PlanPersonList_id = :PlanPersonList_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @ExportErrorPlanDD_id as ExportErrorPlanDD_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->queryResult($query, array(
			'ExportErrorPlanDD_id' => null,
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'ExportErrorPlanDDType_id' => $data['ExportErrorPlanDDType_id'],
			'ExportErrorPlanDD_Description' => isset($data['ExportErrorPlanDD_Description']) ? $data['ExportErrorPlanDD_Description'] : null,
			'PlanPersonList_id' => $data['PlanPersonList_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return $resp;
	}

	/**
	 * Установка статуса записи человека в плане
	 */
	function setPlanPersonListStatus($data) {
		$query = "
			declare @curDate datetime = dbo.tzGetDate();
			
			declare
				@PlanPersonListStatus_id bigint = :PlanPersonListStatus_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PlanPersonListStatus_ins
				@PlanPersonListStatus_id = @PlanPersonListStatus_id output,
				@PlanPersonListStatusType_id = :PlanPersonListStatusType_id,
				@PlanPersonList_id = :PlanPersonList_id,
				@PlanPersonListStatus_setDate = @curDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PlanPersonListStatus_id as PlanPersonListStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, array(
			'PlanPersonListStatus_id' => null,
			'PlanPersonListStatusType_id' => $data['PlanPersonListStatusType_id'],
			'PlanPersonList_id' => $data['PlanPersonList_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp[0]['PlanPersonListStatus_id'])) {
			$queryParams = array(
				'PlanPersonList_id' => $data['PlanPersonList_id'],
				'PlanPersonListStatus_id' => $resp[0]['PlanPersonListStatus_id']
			);

			$addQuery = "";
			if ($data['PlanPersonListStatusType_id'] == 1) {
				// для новых поля PersonDopDispPlanExport_id и PlanPersonList_ExportNum должны стать пустыми
				$queryParams['PersonDopDispPlanExport_id'] = null;
				$queryParams['PlanPersonList_ExportNum'] = null;
				$addQuery .= ", PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id, PlanPersonList_ExportNum = :PlanPersonList_ExportNum";
			} else {
				// перенёс из Perm_PersonDopDispPlan_model чтоб два раза не апдейтить
				if (isset($data['PersonDopDispPlanExport_id'])) {
					$queryParams['PersonDopDispPlanExport_id'] = $data['PersonDopDispPlanExport_id'];
					$addQuery .= ", PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id";
				}
				if (isset($data['PlanPersonList_ExportNum'])) {
					$queryParams['PlanPersonList_ExportNum'] = $data['PlanPersonList_ExportNum'];
					$addQuery .= ", PlanPersonList_ExportNum = :PlanPersonList_ExportNum";
				}
			}

			$this->db->query("
				update PlanPersonList with (rowlock) set PlanPersonListStatus_id = :PlanPersonListStatus_id{$addQuery} where PlanPersonList_id = :PlanPersonList_id
			", $queryParams);
		}

		return $resp;
	}

	/**
	 * Сохранение файла экспорта
	 */
	function savePersonDopDispPlanExport($data) {
		$query = "
			declare @curDate datetime = :PersonDopDispPlanExport_expDate;

			if ( @curDate is null )
				set @curDate = dbo.tzGetDate();
			
			declare
				@PersonDopDispPlanExport_id bigint = :PersonDopDispPlanExport_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PersonDopDispPlanExport_ins
				@PersonDopDispPlanExport_id = @PersonDopDispPlanExport_id output,
				@PersonDopDispPlanExport_FileName = :PersonDopDispPlanExport_FileName,
				@PersonDopDispPlanExport_expDate = @curDate,
				@PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum,
				@OrgSmo_id = :OrgSmo_id,
				@PersonDopDispPlanExport_impDate = null,
				@Lpu_id = :Lpu_id,
				@PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year,
				@PersonDopDispPlanExport_Month = :PersonDopDispPlanExport_Month,
				@PersonDopDispPlanExport_DownloadQuarter = :PersonDopDispPlanExport_DownloadQuarter,
				@PersonDopDispPlanExport_IsUsed = :PersonDopDispPlanExport_IsUsed,
				@PersonDopDispPlanExport_IsCreatedTFOMS = :PersonDopDispPlanExport_IsCreatedTFOMS,
				@DispCheckPeriod_id = :DispCheckPeriod_id,
				@PersonDopDispPlanExport_IsExportPeriod = :PersonDopDispPlanExport_IsExportPeriod,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PersonDopDispPlanExport_id as PersonDopDispPlanExport_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, array(
			'PersonDopDispPlanExport_id' => null,
			'PersonDopDispPlanExport_FileName' => $data['PersonDopDispPlanExport_FileName'],
			'PersonDopDispPlanExport_PackNum' => !empty($data['PersonDopDispPlanExport_PackNum']) ? $data['PersonDopDispPlanExport_PackNum'] : null,
			'OrgSmo_id' => !empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_expDate' => !empty($data['PersonDopDispPlanExport_expDate']) ? $data['PersonDopDispPlanExport_expDate'] : null,
			'PersonDopDispPlanExport_Year' => !empty($data['PersonDopDispPlanExport_Year']) ? $data['PersonDopDispPlanExport_Year'] : null,
			'PersonDopDispPlanExport_Month' => !empty($data['PersonDopDispPlanExport_Month']) ? $data['PersonDopDispPlanExport_Month'] : null,
			'PersonDopDispPlanExport_DownloadQuarter' => !empty($data['PersonDopDispPlanExport_DownloadQuarter']) ? $data['PersonDopDispPlanExport_DownloadQuarter'] : null,
			'PersonDopDispPlanExport_IsUsed' => !empty($data['PersonDopDispPlanExport_IsUsed']) ? $data['PersonDopDispPlanExport_IsUsed'] : null,
			'PersonDopDispPlanExport_IsCreatedTFOMS' => !empty($data['PersonDopDispPlanExport_IsCreatedTFOMS']) ? $data['PersonDopDispPlanExport_IsCreatedTFOMS'] : null,
			'DispCheckPeriod_id' => $data['DispCheckPeriod_id'] ?? null,
			'PersonDopDispPlanExport_IsExportPeriod' => $data['PersonDopDispPlanExport_IsExportPeriod'] ?? null,
			'pmUser_id' => $data['pmUser_id'],
		));
	}

	/**
	 * Сохранение линка файла экспорта
	 */
	function savePersonDopDispPlanLink($data) {
		$query = "
			declare
				@PersonDopDispPlanLink_id bigint = :PersonDopDispPlanLink_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PersonDopDispPlanLink_ins
				@PersonDopDispPlanLink_id = @PersonDopDispPlanLink_id output,
				@PersonDopDispPlan_id = :PersonDopDispPlan_id,
				@PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PersonDopDispPlanLink_id as PersonDopDispPlanLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, array(
			'PersonDopDispPlanLink_id' => null,
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}
	
	/**
	 * Установка статуса файла экспорта
	 */
	function setPersonDopDispPlanExportIsUsed($data) {
		$query = "
			update PersonDopDispPlanExport with(rowlock) 
			set PersonDopDispPlanExport_IsUsed = :PersonDopDispPlanExport_IsUsed 
			where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
		";
		$this->db->query($query, array(
			'PersonDopDispPlanExport_id' => $data ['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => $data['PersonDopDispPlanExport_IsUsed']
		));
	}

	/**
	 * Удаляет людей из плана
	 */
	function deletePlanPersonLists($data) {
		
		foreach ($data['PlanPersonList_ids'] as $PlanPersonList_id) {
			$this->deletePlanPersonList(array(
				'PlanPersonList_id' => $PlanPersonList_id
			));
		}
		
		return array('success' => true);
	}

	/**
	 * Удаляет людей из плана
	 */
	function deletePlanPersonList($data) {
		$this->beginTransaction();

		// сначала удаляем все статусы
		$this->db->query("update PlanPersonList with (rowlock) set PlanPersonListStatus_id = null where PlanPersonList_id = :PlanPersonList_id", array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		$resp_ppls = $this->queryResult("
			select
				PlanPersonListStatus_id
			from
				v_PlanPersonListStatus (nolock)
			where
				PlanPersonList_id = :PlanPersonList_id
		", array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		if (is_array($resp_ppls)) {
			foreach ($resp_ppls as $one_ppls) {
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_PlanPersonListStatus_del
						@PlanPersonListStatus_id = :PlanPersonListStatus_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";

				$resp_del = $this->queryResult($query, array(
					'PlanPersonListStatus_id' => $one_ppls['PlanPersonListStatus_id']
				));

				if (!empty($resp_del[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					return $resp_del;
				}
			}
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PlanPersonList_del
				@PlanPersonList_id = :PlanPersonList_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp_del = $this->queryResult($query, array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		if (!empty($resp_del[0]['Error_Msg'])) {
			$this->rollbackTransaction();
		} else {
			$this->commitTransaction();
		}

		return $resp_del;
	}

	/**
	 * Перенос
	 */
	function transferPlanPersonList($data) {
		
		// Проверка по периодам
		$response = $this->checkDispCheckPeriod($data);
		if ( !is_array($response) ) {
			throw new Exception('Ошибка при проверке периодов');
		}
		if( ( empty($data['ignore_period_check']) || $data['ignore_period_check'] == 0 ) && count($response) > 0 ) {
			return array(
				'Error_Msg' => 'YesNo',
				'Error_Code' => 112,
				'Alert_Msg' => 'Период нового плана уже закрыт. Вы хотите перенести?',
				'success' => true
			);
		}
		
		foreach ($data['PlanPersonList_ids'] as $PlanPersonList_id) {
			$query = "update PlanPersonList set PersonDopDispPlan_id = :PersonDopDispPlan_id where PlanPersonList_id = :PlanPersonList_id";
			$result = $this->db->query($query, array(
				'PlanPersonList_id' => $PlanPersonList_id,
				'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id']
			));

			$this->setPlanPersonListStatus(array(
				'PlanPersonList_id' => $PlanPersonList_id,
				'PlanPersonListStatusType_id' => 1, // Новая
				'pmUser_id' => $data['pmUser_id']
			));
		}
		
		return array('success' => true);
	}

	/**
	 * Повторное включение
	 */
	function retryIncludePlanPersonList($data) {

		// Проверка по периодам
		$response = $this->checkDispCheckPeriod($data);
		if ( !is_array($response) ) {
			throw new Exception('Ошибка при проверке периодов');
		}
		if( ( empty($data['ignore_period_check']) || $data['ignore_period_check'] == 0 ) && count($response) > 0 ) {
			return array(
				'Error_Msg' => 'YesNo',
				'Error_Code' => 112,
				'Alert_Msg' => 'Период нового плана уже закрыт. Вы хотите повторно включить?',
				'success' => true
			);
		}
		
		// сохраняем список пэрсонов, чтобы избежать дублей
		$person_ids = array();
		
		// Изначально предполагалось массивом, но решили, что достаточно количества
		$already_incl = 0;

		foreach ($data['PlanPersonList_ids'] as $PlanPersonList_id) {
			// получаем данные, можно нагребать сразу по нескольким...
			$resp = $this->queryResult("
				select
					Person_id,
					Lpu_id
				from
					v_PlanPersonList (nolock)
				where
					PlanPersonList_id = :PlanPersonList_id
			", array(
				'PlanPersonList_id' => $PlanPersonList_id
			));

			if (!empty($resp[0]['Person_id']) && !in_array($resp[0]['Person_id'], $person_ids)) {
				$chk = $this->queryResult("
					select
						Person_id
					from
						v_PlanPersonList ppl (nolock)
						inner join v_PlanPersonListStatus ppls (nolock) on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
					where
						ppl.PersonDopDispPlan_id = :PersonDopDispPlan_id and 
						ppl.Person_id = :Person_id and 
						ppls.PlanPersonListStatusType_id not in (4,5)
				", array(
					'Person_id' => $resp[0]['Person_id'],
					'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id']
				));
				if (count($chk)) {
					$already_incl++;
				} else {
					$person_ids[] = $resp[0]['Person_id'];
					// сохраняем
					$this->saveNewPlanPersonList(array(
						'PlanPersonList_id' => null,
						'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
						'PlanPersonListStatusType_id' => 5,		//Включена повторно
						'Person_id' => $resp[0]['Person_id'],
						'Lpu_id' => $resp[0]['Lpu_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}
		
		if ($already_incl) {
			return array('Error_Msg' => 'Невозможно включить в план '.$already_incl.' записей. Пациент уже найден в текущем плане');
		}

		return array('success' => true);
	}
	
	/**
	 * Проверка периода
	 */
	function checkDispCheckPeriod($data) {
		
		return $this->queryResult("
				select top 1 PDDP.PersonDopDispPlan_id
				from v_PersonDopDispPlan PDDP with (nolock)
				inner join v_DispCheckPeriod DCP with (nolock) on DCP.DispCheckPeriod_id = PDDP.DispCheckPeriod_id
				where PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id and DCP.DispCheckPeriod_endDate < dbo.tzGetDate()
			", array(
				'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id']
			)
		);
	}

	/**
	 * Экспорт планов
	 */
	function exportPersonDopDispPlan($data) {
		
		
	}

	/**
	 * Получение номера пакета для экспорта
	 */
	function getPersonDopDispPlanExportPackNum($data) {
		$resp = $this->queryResult("
			select
				MAX(PersonDopDispPlanExport_PackNum) + 1 as PacketNumber
			from
				v_PersonDopDispPlanExport (nolock)
			where
				PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year
		", array(
			'PersonDopDispPlanExport_Year' => $data['PersonDopDispPlanExport_Year']
		));

		if (!empty($resp[0]['PacketNumber'])) {
			return array('Error_Msg' => '', 'PacketNumber' => $resp[0]['PacketNumber']);
		}

		return array('Error_Msg' => '', 'PacketNumber' => 1);
	}
}