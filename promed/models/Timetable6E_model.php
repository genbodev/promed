<?php

/**
* Timetable6E_model - модель с базовыми методами для работы с расписанием
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.03.2012
*/
class Timetable6E_model extends swModel {
	/**
	 * Timetable_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}
	
	function loadLpuStructureTree($data) {
		$params = [];
		
		switch($data['parentNodeType']) {
			case 'Lpu':
				$params['Lpu_id'] = $data['Lpu_id'];
				$query = "
					select
						'LpuUnitType_' + cast(LUT.LpuUnitType_id as varchar) as id,
						rtrim(LUT.LpuUnitType_Name) as text,
						'LpuUnitType' as nodeType,
						'mo' as iconCls,
						0 as leaf,
						:Lpu_id as Lpu_id,
						LUT.LpuUnitType_id as LpuUnitType_id
					from
						v_LpuUnitType LUT with(nolock)
					where
						LUT.LpuUnitType_SysNick in ('polka'/*,'parka','stac','dstac','hstac','pstac','priem'*/)
				";
				break;
			case 'LpuUnitType':
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
				$query = "
					select
						'LpuUnit_' + cast(LU.LpuUnit_id as varchar) as id,
						rtrim(LU.LpuUnit_Name) as text,
						'LpuUnit' as nodeType,
						'mo-section' as iconCls,
						0 as leaf,
						LU.Lpu_id,
						LU.LpuUnit_id
					from
						v_LpuUnit LU with(nolock)
					where
						LU.Lpu_id = :Lpu_id
						and LU.LpuUnitType_id = :LpuUnitType_id
				";
				break;
			case 'LpuUnit':
				$params['LpuUnit_id'] = $data['LpuUnit_id'];
				$query = "
					select
						'LpuSection_' + cast(LS.LpuSection_id as varchar) as id,
						rtrim(LS.LpuSection_Name) as text,
						'LpuSection' as nodeType,
						'mini-section' as iconCls,
						1 as leaf,
						LS.Lpu_id,
						LS.LpuSection_id
					from
						v_LpuSection LS with(nolock)
					where
						LS.LpuUnit_id = :LpuUnit_id
				";
				break;
			default:
				return false;
		}
		
		$response = $this->queryResult($query, $params);
		
		return $response;
	}
	
	function loadSubjectList($data) {
		$params = [];
		$filters = [];
		
		if (!empty($data['Lpu_id'])) {
			$filters[] = "L.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['LpuUnitType_id'])) {
			$filters[] = "LU.LpuUnitType_id = :LpuUnitType_id";
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		if (!empty($data['LpuUnit_id'])) {
			$filters[] = "LU.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filters[] = "LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['MedStaffFact_id'])) {
			$filters[] = "MSF.MedStaffFact_id = :MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		if (!empty($data['query'])) {
			$filters[] = "(
				MSF.Person_Fio like :query+'%' or
				LS.LpuSection_Name like :query+'%'
			)";
			$params['query'] = $data['query'];
		}
		
		$filters_str = implode("\nand ", $filters);
		
		$query = "
			declare @date date = dbo.tzGetDate();
			select
				MSF.MedStaffFact_id as id,
				coalesce(MSF.Person_Fio, 'Не заполнено') as name,
				LS.LpuSection_Name as place,
				TT.DaysCount as count
			from
				v_MedStaffFact MSF with(nolock)
				inner join v_Lpu L with(nolock) on L.Lpu_id = MSF.Lpu_id
				inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = MSF.LpuSection_id
				inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = MSF.LpuUnit_id
				outer apply (
					select top 1 count(D.Day_id) as DaysCount
					from v_Day D with(nolock)
					where D.Day_Date between @date and dateadd(day, 13, @date)
					and exists(
						select * from v_TimetableGraf_lite with(nolock)
						where TimetableGraf_Day = D.Day_id and MedStaffFact_id = MSF.MedStaffFact_id 
						and (TimetableGraf_IsDop is null or TimetableGraf_IsDop = 0)
					)
				) TT
			where
				{$filters_str}
		";
		
		return $this->queryResult($query, $params);
	}
	
	/**
	 * @param string $objectName
	 * @param string $typeAlias
	 * @return string
	 */
	function getTimetableTypeClsField($objectName, $typeAlias, $needDop = false) {
		$dopType = "";
		if ($needDop && in_array($objectName, ['TimetableGraf', 'TimetableMedService', 'TimetableResource'])) {
			$dopType = "when {$objectName}_IsDop = 1 then 'extra'";
		}
		$field = "case
			{$dopType}
			when {$typeAlias}.TimetableType_Code = 1 then 'normal'
			when {$typeAlias}.TimetableType_Code = 2 then 'reserve'
			when {$typeAlias}.TimetableType_Code = 3 then 'paid'
			when {$typeAlias}.TimetableType_Code = 4 then 'call-center'
			when {$typeAlias}.TimetableType_Code = 5 then 'on-direction'
			when {$typeAlias}.TimetableType_Code = 6 then 'emergency-bed'
			when {$typeAlias}.TimetableType_Code = 7 then 'stationary'
			when {$typeAlias}.TimetableType_Code = 8 then 'only-this-mo'
			when {$typeAlias}.TimetableType_Code = 9 then 'infomat-reg'
			when {$typeAlias}.TimetableType_Code = 10 then 'in-mo-reg'
			when {$typeAlias}.TimetableType_Code = 11 then 'for-internet'
			when {$typeAlias}.TimetableType_Code = 12 then 'live-queue'
			when {$typeAlias}.TimetableType_Code = 13 then 'video-call'
			when {$typeAlias}.TimetableType_Code = 14 then 'group-reception'
		end";
		return $field;
	}
	
	function loadTimetableTypeList($data) {
		$params = [];
		
		$typeClsField = $this->getTimetableTypeClsField('TimetableGraf', 'TTT');
		
		$query = "
			select
				TTT.TimetableType_id as id,
				TTT.TimetableType_Name as name,
				TTT.TimetableType_SysNick as nick,
				{$typeClsField} as cls
			from 
				v_TimetableType TTT with(nolock)
		";
		
		return $this->queryResult($query, $params);
	}
	
	function loadTimetableSchedule($data) {
		$params = [
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Date' => $data['Date'],
		];
		$response = [
			'success' => true,
			'Annotations' => [],
			'Timetables' => []
		];
		
		$query = "
			select
				A.Annotation_id as id,
				coalesce(A.Annotation_Comment, AT.AnnotationType_Name) as text,
				convert(varchar(10), Annotation_insDT, 104) as insDate,
				convert(varchar(10), Annotation_begDate, 104) as begDate,
				convert(varchar(5), Annotation_begTime, 108) as begTime,
				convert(varchar(10), Annotation_endDate, 104) as endDate,
				convert(varchar(5), Annotation_endTime, 108) as endTime,
				AV.AnnotationVison_id as visionId,
				AV.AnnotationVison_Name as visionName
			from 
				v_Annotation A with(nolock)
				left join v_AnnotationVison AV with(nolock) on AV.AnnotationVison_id = A.AnnotationVison_id
				left join v_AnnotationType AT with(nolock) on AT.AnnotationType_id = A.AnnotationType_id
			where
				A.MedStafffact_id = :MedStaffFact_id
				and A.Annotation_begDate <= dateadd(day, 13, cast(:Date as date))
				and A.Annotation_endDate >= cast(:Date as date)
		";
		$response['Annotations'] = $this->queryResult($query, $params);
		if (!is_array($response['Annotations'])) {
			return false;
		}
		
		$typeClsField = $this->getTimetableTypeClsField('TimetableGraf', 'TTT', true);
		
		$query = "
			select
				TTG.TimetableGraf_id as id,
				convert(varchar(10), coalesce(TTG.TimetableGraf_begTime, TTG.TimetableGraf_factTime), 104) as date,
				convert(varchar(5), coalesce(TTG.TimetableGraf_begTime, TTG.TimetableGraf_factTime), 108) as time,
				TTG.TimetableGraf_Time as duration,
				case when TTG.Person_id is null then 1 else 0 end as isFree,
				case
					when TTG.TimetableGraf_IsDop = 1 then 'Дополнительная'
					else TTT.TimetableType_Name 
				end as typeName,
				case
					when TTG.TimetableGraf_IsDop = 1 then 'extra'
					else TTT.TimetableType_SysNick
				end as typeNick,
				{$typeClsField} as typeCls,
				TimetableExtend_Descr as descr
			from
				v_TimetableGraf TTG with(nolock)
				inner join v_Day D with(nolock) on D.Day_id = TTG.TimetableGraf_Day
				left join v_TimetableType TTT with(nolock) on TTT.TimetableType_id = TTG.TimetableType_id
			where
				TTG.MedStaffFact_id = :MedStaffFact_id
				--and D.Day_Date between cast(:Date as date) and dateadd(day, 13, cast(:Date as date))
				and cast(TTG.TimetableGraf_begTime as date) between cast(:Date as date) and dateadd(day, 13, cast(:Date as date))
			order by
				D.Day_Date,
				coalesce(TTG.TimetableGraf_begTime, TTG.TimetableGraf_factTime)
		";
		
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}
		
		$response['Timetables'] = $resp;
		
		return $response;
	}
	
	function saveTimetableSchedule($data) {
		$this->load->helper('Reg');
		
		$begDay = TimeToDay(strtotime($data['Range'][0]));
		$endDay = TimeToDay(strtotime($data['Range'][1]));
		
		$this->beginTransaction();
		
		for ($day = $begDay; $day <= $endDay; $day++) {
			$params = [
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'TimetableGraf_Day' => $day,
				'TimetableGraf_Time' => $data['Duration'],
				'TimetableType_id' => $data['TimetableType_id'],		
				'StartTime' => $data['BegTime'],		
				'EndTime' => $data['EndTime'],		
				'pmUser_id' => $data['pmUser_id'],		
			];
			
			$query = "
				declare
					@Error_Code bigint,
					@Error_Msg varchar(4000);
				exec p_TimeTableGraf_fill 
					@MedStaffFact_id = :MedStaffFact_id,
					@TimetableGraf_Day = :TimetableGraf_Day,
					@TimetableGraf_Time = :TimetableGraf_Time,
					@TimetableType_id = :TimetableType_id,
					@StartTime = :StartTime,
					@EndTime = :EndTime,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;
				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			";
			
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при заполнении расписания');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		
		$this->commitTransaction();
		
		return [['success' => true]];
	}
	
	function deleteTimetableSchedule($data) {
		$this->beginTransaction();
		
		foreach($data['ids'] as $id) {
			$params = [
				'TimetableGraf_id' => $id,			
				'pmUser_id' => $data['pmUser_id'],			
			];
			
			$query = "
				declare
					@Error_Code bigint,
					@Error_Msg varchar(4000);
				exec p_TimeTableGraf_del
					@TimetableGraf_id = :TimetableGraf_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;
				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			";
			
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при удалении бирки');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		
		$this->commitTransaction();
		
		return [['success' => true]];
	}
	
	function copyTimetableSchedule($data) {
		$this->beginTransaction();
		
		$this->load->helper('Reg');
		
		$ids = implode(',', $data['ids']);
		
		$query = "
			select
				TTG.TimetableGraf_id,
				TTG.MedStaffFact_id,
				TTG.TimetableGraf_Day,
				convert(varchar(10), TTG.TimetableGraf_begTime, 120) as TimetableGraf_begDate,
				convert(varchar(5), TTG.TimetableGraf_begTime, 108) as TimetableGraf_begTime,
				TTG.TimetableGraf_Time,
				TTG.TimetableGraf_IsDop,
				TTG.TimetableType_id,
				TTG.TimetableExtend_Descr,
				TTG.TimetableGraf_countRec
			from
				v_TimetableGraf TTG with(nolock)
			where
				TTG.TimetableGraf_id in ({$ids})
				and TTG.TimetableGraf_begTime is not null 
			order by
				TTG.TimetableGraf_Day
		";
		
		$TimetableList = $this->queryResult($query);
		if (!is_array($TimetableList)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении данных бирок');
		}
		if (count($TimetableList) == 0) {
			$this->rollbackTransaction();
			return $this->createError('','Отсутствуют бирки для копирования');
		}
		
		$query = "
			select
				TTG.TimetableGraf_id
			from
				v_TimetableGraf TTG with(nolock)
			where
				TTG.MedStaffFact_id = :MedStaffFact_id
				and cast(TTG.TimetableGraf_begTime as date) between :begDate and :endDate
		";
		$idsForDelete = $this->queryList($query, [
			'MedStaffFact_id' => $TimetableList[0]['MedStaffFact_id'],
			'begDate' => $data['toRange'][0],
			'endDate' => $data['toRange'][1],
		]);
		if (!is_array($idsForDelete)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении поиске бирок для удаления');
		}
		if (count($idsForDelete) > 0) {
			$this->isAllowTransaction = false;
			$resp = $this->deleteTimetableSchedule([
				'ids' => $idsForDelete,
				'pmUser_id' => $data['pmUser_id']
			]);
			$this->isAllowTransaction = true;
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		
		
		$timetableByDate = [];
		foreach($TimetableList as $Timetable) {
			$key = $Timetable['TimetableGraf_begDate'];
			unset($Timetable['TimetableGraf_begDate']);
			$timetableByDate[$key][] = $Timetable;
		}
		
		$fromBegDate = date_create($data['fromRange'][0]);
		$fromEndDate = date_create($data['fromRange'][1]);
		
		$toBegDate = date_create($data['toRange'][0]);
		$toEndDate = date_create($data['toRange'][1]);
		
		for (
			$fromDate = date_create($fromBegDate->format('Y-m-d')), $toDate = date_create($toBegDate->format('Y-m-d')); 
			$toDate <= $toEndDate; 
			$fromDate->modify('+1 day'), $toDate->modify('+1 day')
		) {
			if ($fromDate > $fromEndDate) {
				if ($data['repeatable']) {
					$fromDate = date_create($fromBegDate->format('Y-m-d'));
				} else {
					break;
				}
			}
			
			$timetables = $timetableByDate[$fromDate->format('Y-m-d')] ?? [];
			
			foreach($timetables as $Timetable) {
				$params = array_merge($Timetable, [
					'TimetableGraf_id' => null,
					'TimetableGraf_Day' => TimeToDay($toDate->getTimestamp()),
					'TimetableGraf_begTime' => $toDate->format('Y-m-d') . ' ' . $Timetable['TimetableGraf_begTime'],
					'pmUser_id' => $data['pmUser_id'],
				]);
				
				$query = "
					declare
						@TimetableGraf_id bigint,
						@Error_Code bigint,
						@Error_Msg varchar(4000);
					exec p_TimeTableGraf_ins
						@TimetableGraf_id = @TimetableGraf_id output,
						@MedStaffFact_id = :MedStaffFact_id,
						@TimetableGraf_Day = :TimetableGraf_Day,
						@TimetableGraf_begTime = :TimetableGraf_begTime,
						@TimetableGraf_Time = :TimetableGraf_Time,
						@TimetableGraf_IsDop = :TimetableGraf_IsDop,
						@TimetableType_id = :TimetableType_id,
						@TimetableExtend_Descr = :TimetableExtend_Descr,
						@TimetableGraf_countRec = :TimetableGraf_countRec,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Msg output;
					select @TimetableGraf_id as TimetableGraf_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
				";
				
				$resp = $this->queryResult($query, $params);
				if (!is_array($resp)) {
					$this->rollbackTransaction();
					return $this->createError('','Ошибка при копировании бирки');
				}
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}
		
		$this->commitTransaction();
		
		return [['success' => true]];
	}
	
	function setTimetableType($data) {
		$this->beginTransaction();
		
		$ids = implode(',', $data['ids']);
		
		$params = [
			'TimetableType_id' => $data['typeId']	
		];
		
		$query = "
			update TimetableGraf with(rowlock)
			set TimetableType_id = :TimetableType_id
			where TimetableGraf_id in ({$ids})
		";
		
		$this->db->query($query, $params);
		
		$this->commitTransaction();
		
		return [['success' => true]];
	}
	
	function loadAnnotationTypeList($data) {
		$params = [];
		
		$query = "
			select
				AT.AnnotationType_id as id,
				AT.AnnotationType_Name as name,
				case
					when AT.AnnotationClass_id = 2 and AT.pmUser_insID > 1 
					then 1 else 0 
				end as isCustom
			from
				v_AnnotationType AT with(nolock)
			where
				(AT.AnnotationClass_id = 1 or AT.pmUser_insID > 1)
		";
		
		return $this->queryResult($query, $params);
	}
	
	function loadAnnotationEditForm($data) {
		$params = [
			'Annotation_id' => $data['Annotation_id']		
		];
		
		$query = "
			select top 1
				A.Annotation_id,
				A.MedStaffFact_id,
				convert(varchar(10), A.Annotation_begDate, 104) as Annotation_begDate,
				convert(varchar(5), A.Annotation_begTime, 108) as Annotation_begTime,
				convert(varchar(10), A.Annotation_endDate, 104) as Annotation_endDate,
				convert(varchar(5), A.Annotation_endTime, 108) as Annotation_endTime,
				A.AnnotationType_id,
				A.AnnotationVison_id
			from
				v_Annotation A with(nolock)
			where
				A.Annotation_id = :Annotation_id
		";
		
		return $this->queryResult($query, $params);
	}
	
	/**
	 * @param array $data
	 * @return array|false
	 */
	function saveAnnotation($data) {
		$params = [
			'Annotation_id' => !empty($data['Annotation_id'])?$data['Annotation_id']:null,
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Annotation_begDate' => $data['Annotation_begDate'],
			'Annotation_begTime' => !empty($data['Annotation_begTime'])?$data['Annotation_begTime']:null,
			'Annotation_endDate' => $data['Annotation_endDate'],
			'Annotation_endTime' => !empty($data['Annotation_endTime'])?$data['Annotation_endTime']:null,
			'AnnotationType_id' => $data['AnnotationType_id'],
			'AnnotationVison_id' => $data['AnnotationVison_id'],
			'pmUser_id' => $data['AnnotationVison_id'],
		];
		
		if (empty($params['Annotation_id'])) {
			$procedure = 'p_Annotation_ins';
		} else {
			$procedure = 'p_Annotation_upd';
		}
		
		$query = "
			declare
				@Annotation_id bigint,
				@Error_Code bigint,
				@Error_Msg varchar(4000);
			set @Annotation_id = :Annotation_id;
			exec {$procedure}
				@Annotation_id = @Annotation_id output,
				@MedStaffFact_id = :MedStaffFact_id,
				@Annotation_begDate = :Annotation_begDate,
				@Annotation_begTime = :Annotation_begTime,
				@Annotation_endDate = :Annotation_endDate,
				@Annotation_endTime = :Annotation_endTime,
				@AnnotationType_id = :AnnotationType_id,
				@AnnotationVison_id = :AnnotationVison_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Annotation_id as Annotation_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении примечания');
		}
		
		return $response;
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	function setAnnotationRange($data) {
		$params = [
			'Annotation_id' => $data['Annotation_id'],
			'Annotation_begDate' => $data['Annotation_begDate'],		
			'Annotation_endDate' => !empty($data['Annotation_endDate'])?$data['Annotation_endDate']:$data['Annotation_begDate'],		
		];
		
		$query = "
			update 
				Annotation with(rowlock)
			set
				Annotation_begDate = :Annotation_begDate,
				Annotation_endDate = :Annotation_endDate
			where
				Annotation_id = :Annotation_id
		";
		
		$this->db->query($query, $params);
		
		return [['success' => true]];
	}
	
	/**
	 * @param array $data
	 * @return array|false
	 */
	function deleteAnnotation($data) {
		$params = [
			'Annotation_id' => $data['Annotation_id'],
		];
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Msg varchar(4000);
			exec p_Annotation_del
				@Annotation_id = :Annotation_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении примечания');
		}
		
		return $response;
	}
	
	function addAnnotationTypeCustom($data) {
		$params = [
			//'AnnotationType_id' => null,
			//'AnnotationType_Code' => null,
			'AnnotationType_Name' => $data['name'],
			'AnnotationClass_id' => 2,
			'pmUser_id' => $data['pmUser_id']		
		];
		
		$query = "
			select count(*) as cnt
			from v_AnnotationType with(nolock)
			where AnnotationType_Name = :AnnotationType_Name
			and pmUser_insID = :pmUser_id
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке существования типа примечания');
		}
		if ($count > 0) {
			return $this->createError('','Уже существует тип примечения с таким наименованием');
		}
	
		$query = "
			declare
				@AnnotationType_id bigint,
				@AnnotationType_Code int,
				@Error_Code bigint,
				@Error_Msg varchar(4000);
			set @AnnotationType_Code = (
				select coalesce(max(AnnotationType_Code), 0) + 1
				from v_AnnotationType with(nolock)
			);
			exec p_AnnotationType_ins
				@AnnotationType_id = @AnnotationType_id output,
				@AnnotationType_Code = @AnnotationType_Code,
				@AnnotationType_Name = :AnnotationType_Name,
				@AnnotationClass_id = :AnnotationClass_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @AnnotationType_id as AnnotationType_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении типа примечания');
		}
		
		return $response;
	}
	
	function deleteAnnotationTypeCustom($data) {
		$params = [
			'AnnotationType_id' => $data['id']
		];
		
		$query = "
			select
				AnnotationClass_id, 
				pmUser_insID
			from v_AnnotationType with(nolock)
			where AnnotationType_id = :AnnotationType_id
		";
		
		$AnnotationType = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($AnnotationType)) {
			return $this->createError('','Ошибка при получении информации о типе примечания');
		}
		if ($AnnotationType['AnnotationClass_id'] != 2 || $AnnotationType['pmUser_insID'] != $data['pmUser_id']) {
			return $this->createError('','Не доступно удаление типа примечания');
		}
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Msg varchar(4000);
			exec p_AnnotationType_del
				@AnnotationType_id = :AnnotationType_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении типа примечания');
		}
		
		return $response;
	}
}