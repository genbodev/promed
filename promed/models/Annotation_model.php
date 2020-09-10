<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Annotation_model - модель для работы с примечаниями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			12.11.2015
 */

class Annotation_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление примечания
	 */
	function delete($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_Annotation_del
				@Annotation_id = :Annotation_id,
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
	 * Возвращает список примечаний
	 */
	function loadList($data) {
	
		$filter = '';

		if (!empty($data['MedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$filter = 'A.MedStaffFact_id = :MedStaffFact_id';
		} elseif (!empty($data['MedService_id'])) {
			$params['MedService_id'] = $data['MedService_id'];
			$filter = 'A.MedService_id = :MedService_id';
		} elseif (!empty($data['Resource_id'])) {
			$params['Resource_id'] = $data['Resource_id'];
			$filter = 'A.Resource_id = :Resource_id';
		} else {
			return false;
		}
		
		if (!empty($data['AnnotationType_id'])) {
			$filter .= ' and A.AnnotationType_id = :AnnotationType_id ';
			$params['AnnotationType_id'] = $data['AnnotationType_id'];
		}
		if (!empty($data['AnnotationVison_id'])) {
			$filter .= ' and A.AnnotationVison_id = :AnnotationVison_id ';
			$params['AnnotationVison_id'] = $data['AnnotationVison_id'];
		}
		if (!empty($data['Annotation_Comment'])) {
			$filter .= ' and A.Annotation_Comment like :Annotation_Comment ';
			$params['Annotation_Comment'] = "%{$data['Annotation_Comment']}%";
		}
		
		$date_select = "convert(varchar(10), A.Annotation_begDate, 104) + ' - ' + isnull(convert(varchar(10), A.Annotation_endDate, 104), '')";
		
		// это костыль для копирования расписания
		if (count($data['AnnotationDateRange']) == 2 && !empty($data['AnnotationDateRange'][0])) {
			$filter .= ' and (
				cast(A.Annotation_begDate as date) <= :AnnotationDateRangeEnd and 
				cast(A.Annotation_endDate as date) >= :AnnotationDateRangeStart
			)';
			$params['AnnotationDateRangeStart'] = $data['AnnotationDateRange'][0];
			$params['AnnotationDateRangeEnd'] = $data['AnnotationDateRange'][1];
			$date_select = "
				convert(varchar(10), (case when A.Annotation_begDate > :AnnotationDateRangeStart then A.Annotation_begDate else cast(:AnnotationDateRangeStart as date) end), 104) +
				' - ' + 
				convert(varchar(10), (case when A.Annotation_endDate is not null and A.Annotation_endDate < :AnnotationDateRangeEnd then A.Annotation_endDate else cast(:AnnotationDateRangeEnd as date) end), 104)
			";
		}
		
		// а это нормальная фильтрация
		if (count($data['Annotation_DateRange']) == 2 && !empty($data['Annotation_DateRange'][0])) {
			$filter .= ' and (
				(cast(A.Annotation_begDate as date) <= :AnnotationDateRangeEnd or A.Annotation_begDate is null) and 
				(cast(A.Annotation_endDate as date) >= :AnnotationDateRangeStart or A.Annotation_endDate is null)
			)';
			$params['AnnotationDateRangeStart'] = $data['Annotation_DateRange'][0];
			$params['AnnotationDateRangeEnd'] = $data['Annotation_DateRange'][1];
		}

		$query = "
			select
				-- select
				A.Annotation_id
				,AT.AnnotationType_Name
				,AC.AnnotationClass_Name
				,AV.AnnotationVison_Name
				,convert(varchar(10), A.Annotation_begDate, 104) as Annotation_Beg_Date
				,{$date_select} as Annotation_Date
				,convert(varchar(5), A.Annotation_begTime, 108) + ' - ' + isnull(convert(varchar(5), A.Annotation_endTime, 108), '') as Annotation_Time
				,A.Annotation_Comment
				-- end select
			from
				-- from
				v_Annotation A with(nolock)
				inner join v_AnnotationType AT with(nolock) on AT.AnnotationType_id = A.AnnotationType_id
				inner join v_AnnotationClass AC with(nolock) on AC.AnnotationClass_id = AT.AnnotationClass_id
				inner join v_AnnotationVison AV with(nolock) on AV.AnnotationVison_id = A.AnnotationVison_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				A.Annotation_begTime
				-- end order by
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает примечание
	 */
	function load($data) {

		$query = "
			select
				-- select
				A.Annotation_id
				,A.MedStaffFact_id
				,A.MedService_id
				,A.Resource_id
				,A.AnnotationType_id
				,A.AnnotationVison_id
				,convert(varchar(10), A.Annotation_begDate, 104) as Annotation_begDate
				,convert(varchar(10), A.Annotation_endDate, 104) as Annotation_endDate
				,convert(varchar(5), A.Annotation_begTime, 108) as Annotation_begTime
				,convert(varchar(5), A.Annotation_endTime, 108) as Annotation_endTime
				,A.Annotation_Comment
				-- end select
			from
				-- from
				v_Annotation A with(nolock)
				-- end from
			where
				-- where
				A.Annotation_id = :Annotation_id
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
	 * Сохраняет примечание
	 */
	function save($data) {
	
		// Проверка дубликатов управляющих примечаний
		$response = $this->checkAnnotationDoubles($data);
		if ( !is_array($response) ) {
			throw new Exception('Ошибка при проверке дублей управляющих примечаний');
		}
		if( ( empty($data['ignore_doubles']) || $data['ignore_doubles'] == 0 ) && count($response) > 0 ) {
			return array(
				'Error_Msg' => 'YesNo',
				'Error_Code' => 112,
				'Alert_Msg' => 'Примечание имеет пересечение периодов с уже введенными примечаниями. Продолжить сохранение?',
				'success' => true
			);
		}

		if (empty($data['MedStaffFact_id']) && !empty($data['Annotation_id'])) {
			$data['MedStaffFact_id'] = $this->getFirstResultFromQuery("select MedStaffFact_id from v_Annotation (nolock) where Annotation_id = :Annotation_id", array(
				'Annotation_id' => $data['Annotation_id']
			));
		}

		$params = array(
			'Annotation_id' => empty($data['Annotation_id']) ? null : $data['Annotation_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedService_id' => $data['MedService_id'],
			'Resource_id' => $data['Resource_id'],
			'AnnotationType_id' => $data['AnnotationType_id'],
			'AnnotationVison_id' => $data['AnnotationVison_id'],
			'Annotation_Comment' => $data['Annotation_Comment'],
			'Annotation_begDate' => $data['Annotation_begDate'] ?: null,
			'Annotation_endDate' => $data['Annotation_endDate'] ?: null,
			'Annotation_begTime' => $data['Annotation_begTime'] ?: null,
			'Annotation_endTime' => $data['Annotation_endTime'] ?: null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($params['Annotation_id']) ? 'p_Annotation_ins' : 'p_Annotation_upd';

		$query = "
			declare
				@Annotation_id bigint = :Annotation_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@Annotation_id = @Annotation_id output,
				@AnnotationType_id = :AnnotationType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedService_id = :MedService_id,
				@Resource_id = :Resource_id,
				@AnnotationVison_id = :AnnotationVison_id,
				@Annotation_Comment = :Annotation_Comment,
				@Annotation_begDate = :Annotation_begDate,
				@Annotation_begTime = :Annotation_begTime,
				@Annotation_endDate = :Annotation_endDate,
				@Annotation_endTime = :Annotation_endTime,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Annotation_id as Annotation_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка управляющих примечений на дубли
	 */
	function checkAnnotationDoubles ($data) {

		$AnnotationClass = $this->getFirstRowFromQuery("
				select top 1 AnnotationClass_id
				from v_AnnotationType with(nolock)
				where AnnotationType_id = :AnnotationType_id
			", array(
				'AnnotationType_id' => $data['AnnotationType_id']
			)
		);

		if ( $AnnotationClass['AnnotationClass_id'] == '2' ) return array();

		$params = array(
			'Annotation_id' => empty($data['Annotation_id']) ? null : $data['Annotation_id'],
			'Annotation_begDate' => $data['Annotation_begDate'] ?: null,
			'Annotation_endDate' => $data['Annotation_endDate'] ?: null,
		);

		if (isset($data['MedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$filter = 'A.MedStaffFact_id = :MedStaffFact_id';
		} elseif (isset($data['MedService_id'])) {
			$params['MedService_id'] = $data['MedService_id'];
			$filter = 'A.MedService_id = :MedService_id';
		} elseif (isset($data['Resource_id'])) {
			$params['Resource_id'] = $data['Resource_id'];
			$filter = 'A.Resource_id = :Resource_id';
		} else {
			return false;
		}

		$query = "
			select
				-- select
				A.Annotation_id
				-- end select
			from
				-- from
				v_Annotation A with(nolock)
				inner join v_AnnotationType AT with(nolock) on AT.AnnotationType_id = A.AnnotationType_id
				-- end from
			where
				-- where
				{$filter} and
				A.Annotation_id != isnull(:Annotation_id, 0) and
				AT.AnnotationClass_id = 1 and
				(
					(Annotation_begDate <= :Annotation_begDate AND
					(Annotation_endDate > :Annotation_endDate OR Annotation_endDate IS NULL))
				OR
					(:Annotation_begDate BETWEEN Annotation_begDate AND Annotation_endDate)
				OR
					(Annotation_begDate > :Annotation_begDate AND :Annotation_endDate is null)
				)
				-- end where
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение примечаний для регистратуры
	 */
	function getRegAnnotation($data)
	{
		$params = array(
			'nulltime' => '00:00:00',
			'Lpu_id' => $data['Lpu_id']
		);

		if (isset($data['MedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$filter = 'An.MedStaffFact_id = :MedStaffFact_id';
		} elseif (isset($data['Resource_id'])) {
			$params['Resource_id'] = $data['Resource_id'];
			$filter = 'An.Resource_id = :Resource_id';
		} else {
			return false;
		}

		$sql = "
			select
				rtrim(An.Annotation_Comment) as Annotation_Comment,
				An.Annotation_updDT,
				rtrim(u.pmUser_Name) as pmUser_Name
			from v_Annotation An with (nolock)
			left join v_pmUser u with (nolock) on u.pmUser_id = An.pmUser_updID
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = An.MedStaffFact_id
			left join v_Resource r with (nolock) on r.Resource_id = An.Resource_id
			left join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
			where
				{$filter} AND
				An.Annotation_begDate is null AND
				An.Annotation_endDate is null AND
				(An.Annotation_begTime is null or An.Annotation_begTime = :nulltime) AND
				(An.Annotation_endTime is null or An.Annotation_endTime = :nulltime) AND
				(An.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id or ms.Lpu_id = :Lpu_id)
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение примечаний для регистратуры по врачам
	 */
	function getMSFAnnotations($data, $MedStaffFactIds)
	{
		$params = array(
			'nulltime' => '00:00:00',
			'Lpu_id' => $data['Lpu_id']
		);

		$sql = "
			select
				An.MedStaffFact_id,
				rtrim(An.Annotation_Comment) as Annotation_Comment,
				An.Annotation_updDT,
				rtrim(u.pmUser_Name) as pmUser_Name
			from v_Annotation An with (nolock)
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = An.MedStaffFact_id
				left join v_pmUser u with (nolock) on u.pmUser_id = An.pmUser_updID
			where
				An.MedStaffFact_id IN ('" . implode("','", $MedStaffFactIds) . "') AND
				An.Annotation_begDate is null AND
				An.Annotation_endDate is null AND
				(An.Annotation_begTime is null or An.Annotation_begTime = :nulltime) AND
				(An.Annotation_endTime is null or An.Annotation_endTime = :nulltime) AND
				(An.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)
		";

		$annotations = array();

		$resp = $this->queryResult($sql, $params);
		if (!empty($resp)) {
			foreach($resp as $respone) {
				if (!isset($annotations[$respone['MedStaffFact_id']])) {
					$annotations[$respone['MedStaffFact_id']] = array();
				}
				$annotations[$respone['MedStaffFact_id']][] = $respone;
			}
		}

		return $annotations;
	}

	/**
	 * Проверка наличия блокирующего примечания для бирки
	 */
	function checkBlockAnnotation($data) {

		$params = array(
			'nulltime' => '00:00:00'
		);

		if (isset($data['TimetableGraf_id'])) {
			$params['TimetableGraf_id'] = $data['TimetableGraf_id'];
			$filter = 'TT.TimetableGraf_id = :TimetableGraf_id';
			$join = 'A.MedStaffFact_id = TT.MedStaffFact_id';
			$object = 'TimetableGraf';

		} elseif (isset($data['Resource_id'])) {
			$params['TimetableResource_id'] = $data['TimetableResource_id'];
			$filter = 'TT.TimetableResource_id = :TimetableResource_id';
			$join = 'A.Resource_id = TT.Resource_id';
			$object = 'TimetableResource';

		} else {
			return false;
		}

		$query = "
			select
				-- select
				A.Annotation_id,
				coalesce(A.Annotation_Comment, AT.AnnotationType_Name) as Annotation_Comment
				-- end select
			from
				-- from
				v_Annotation A with(nolock)
				inner join v_AnnotationType AT with(nolock) on AT.AnnotationType_id = A.AnnotationType_id
				inner join v_{$object} TT with(nolock) on
					{$join} AND
					(
						(cast(TT.{$object}_begTime as DATE) >= A.Annotation_begDate OR A.Annotation_begDate IS NULL) AND
						(cast(TT.{$object}_begTime as DATE) <= A.Annotation_endDate OR A.Annotation_endDate IS NULL) AND
						(cast(TT.{$object}_begTime as time) >= A.Annotation_begTime OR A.Annotation_begTime IS NULL OR A.Annotation_begTime = :nulltime) AND
						(cast(TT.{$object}_begTime as time) <= A.Annotation_endTime OR A.Annotation_endTime IS NULL OR A.Annotation_begTime = :nulltime)
					)
				-- end from
			where
				-- where
				AT.AnnotationClass_id = 1 and
				{$filter}
				-- end where
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}

	/**
	 * Проверка, работает ли сотрудник в указанные дни
	 */
	function checkMsfIsWork( $data ) {
		
		// для других ограничений пока нет
		if (!isset($data['MedStaffFact_id'])) {
			return true;
		}
		
		$filter = '';
		$params = array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		);
		
		if (!empty($data['Annotation_begDate'])) {
			$params['StartDate'] = $data['Annotation_begDate'];
			$filter .= " and WorkData_begDate <= :StartDate ";
			// если не заполнена дата окончания, на всякий случай проверим, входит ли дата начала в интервал работы
			if (empty($data['Annotation_endDate'])) {
				$filter .= " and isnull(cast(WorkData_endDate as date), '2030-01-01') >= :StartDate ";
			}
		}
		
		if (!empty($data['Annotation_endDate'])) {
			$params['EndDate'] = $data['Annotation_endDate'];
			$filter .= " and isnull(cast(WorkData_endDate as date), '2030-01-01') >= :EndDate ";
		}
		
		$sql = "
			select count(*) as cnt
			from v_MedStaffFact with (nolock)
			where MedStaffFact_id = :MedStaffFact_id
			{$filter}
		";
		
		$res = $this->db->query($sql,$params);
		
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}
		
		if ( $res[0]['cnt'] == 0 ) {
			return false;
		}
		
		return true;
	}
	
	/**
	* Копирование примечаний
	* принимает Annotation_id исходного примечания и новый период дат
	*/
	function copy($data) {
		if ( empty($data['Annotation_id']) || empty($data['Annotation_begDate']) || empty($data['Annotation_endDate']) ) {
			return false;
		}
		
		$annotation_data = $this->load(array('Annotation_id' => $data['Annotation_id']));
		
		if (!$annotation_data || !count($annotation_data)) return false;
		
		$annotation_data = $annotation_data[0];
		$annotation_data['Annotation_id'] = null;
		
		// Дата начала копируемого примечания + Дата начала целевого диапазона – Дата начала копируемого диапазона
		$beg_date = strtotime($annotation_data['Annotation_begDate']) + strtotime($data['Annotation_begDate']) - strtotime($data['Annotation_copyFromDate']);
		$beg_date = max($beg_date, strtotime($data['Annotation_begDate']));
		
		// Дата окончания копируемого примечания + Дата начала целевого диапазона – Дата начала копируемого диапазона, но не позже Даты окончания целевого диапазона.
		$end_date = strtotime($annotation_data['Annotation_endDate']) + strtotime($data['Annotation_begDate']) - strtotime($data['Annotation_copyFromDate']);
		$end_date = min($end_date, strtotime($data['Annotation_endDate']));
		
		$annotation_data['Annotation_begDate'] = date('Y-m-d', $beg_date);
		$annotation_data['Annotation_endDate'] = date('Y-m-d', $end_date);
		
		$annotation_data['pmUser_id'] = $data['pmUser_id'];
		
		return $this->save($annotation_data);
	}

	/**
	 * Получение списка всех комментариев по доктору и биркам за период
	 * @param object $data
	 */
	public function getDoctorAnnotationsByPeriod($data) {

		if (empty($data)) return false;

		// включить постоянные примечания в запрос ?
		$showRegularAnnotations = true;

		// конвертировать дату в день?
		$convertDateToSqlDay = true;
		if (!empty($data->dontConvertDateToSqlDay)) { $convertDateToSqlDay = false; }
		$this->load->helper("Reg");
		$params = array(
			'doctor_id' => $data['doctor_id'],
			'TimeTableGraf_Day_Start' => ($convertDateToSqlDay ? (TimeToDay($data['start_date']) - 1) : $data['start_date']-1) ,
			'TimeTableGraf_Day_End'  => ($convertDateToSqlDay ? TimeToDay($data['end_date']) : $data['end_date'] )
		);

		$query = "
            select
                Annotation_Comment,
                Annotation_begDate,
                Annotation_endDate,
                Annotation_begTime,
                Annotation_endTime,
                (d.day_id+1) as day_id
            from v_Annotation A with (nolock)
            left join v_Day d with (nolock) on 
				-- было так
				--(cast(D.day_date as date) >= Annotation_begDate AND cast(D.day_date as date) <= Annotation_endDate)
				
				(cast(A.Annotation_begDate as date) <= cast(D.day_date as date) OR A.Annotation_begDate is null) AND
				(cast(A.Annotation_endDate as date) >= cast(D.day_date as date) OR A.Annotation_endDate is null) AND
				A.AnnotationVison_id = 1 
				
            where
                A.MedStaffFact_id = :doctor_id
                and d.Day_id >= :TimeTableGraf_Day_Start
                and d.Day_id < :TimeTableGraf_Day_End
                and (A.AnnotationVison_id = 1 OR A.AnnotationType_id = 4)
            -- если все комменты не нужны
            -- and (A.Annotation_begTime is not null or A.Annotation_endTime is not null)
        ";

		if ($showRegularAnnotations) {

			$query .= "

            union
            select
                Annotation_Comment,
                Annotation_begDate,
                Annotation_endDate,
                Annotation_begTime,
                Annotation_endTime,
                0 as day_id
            from v_Annotation A with (nolock)
            where
                A.MedStaffFact_id = :doctor_id
                and Annotation_begDate is null
                and (A.AnnotationVison_id = 1 OR A.AnnotationType_id = 4)
            ";
		}

		$result = $this->queryResult($query,$params);

		return $result;
	}

}