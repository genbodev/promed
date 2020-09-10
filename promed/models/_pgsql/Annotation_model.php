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

class Annotation_model extends SwPgModel {

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
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_Annotation_del(
          Annotation_id => :Annotation_id
        )
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
            $filter .= ' and A.Annotation_Comment ilike :Annotation_Comment ';
            $params['Annotation_Comment'] = "%{$data['Annotation_Comment']}%";
        }

        $date_select = "to_char(A.Annotation_begDate, 'DD.MM.YYYY') || ' - ' || COALESCE(to_char(A.Annotation_endDate, 'DD.MM.YYYY'), '')";



        // это костыль для копирования расписания
        if (count($data['AnnotationDateRange']) == 2 && !empty($data['AnnotationDateRange'][0])) {
            $filter .= ' and (
				cast(A.Annotation_begDate as date) <= cast(:AnnotationDateRangeEnd as date) and 
				cast(A.Annotation_endDate as date) >= cast(:AnnotationDateRangeStart as date)
			)';
            $params['AnnotationDateRangeStart'] = $data['AnnotationDateRange'][0];
            $params['AnnotationDateRangeEnd'] = $data['AnnotationDateRange'][1];
            $date_select = "
				to_char((case when A.Annotation_begDate > :AnnotationDateRangeStart then A.Annotation_begDate else cast(:AnnotationDateRangeStart as date) end), 'DD.MM.YYYY') ||

				' - ' || 
				to_char((case when A.Annotation_endDate is not null and A.Annotation_endDate < :AnnotationDateRangeEnd then A.Annotation_endDate else cast(:AnnotationDateRangeEnd as date) end), 'DD.MM.YYYY')

			";
        }

        // а это нормальная фильтрация
        if (count($data['Annotation_DateRange']) == 2 && !empty($data['Annotation_DateRange'][0])) {
            $filter .= ' and (
				(cast(A.Annotation_begDate as date) <= cast(:AnnotationDateRangeEnd as date) or A.Annotation_begDate is null) and 
				(cast(A.Annotation_endDate as date) >= cast(:AnnotationDateRangeStart as date) or A.Annotation_endDate is null)
			)';
            $params['AnnotationDateRangeStart'] = $data['Annotation_DateRange'][0];
            $params['AnnotationDateRangeEnd'] = $data['Annotation_DateRange'][1];
        }

        $query = "
			select
				-- select
				A.Annotation_id as \"Annotation_id\"
				,AT.AnnotationType_Name as \"AnnotationType_Name\"
				,AC.AnnotationClass_Name as \"AnnotationClass_Name\"
				,AV.AnnotationVison_Name as \"AnnotationVison_Name\"
				,to_char(A.Annotation_begDate, 'DD.MM.YYYY') as \"Annotation_Beg_Date\"
				,{$date_select} as \"Annotation_Date\"
				,to_char(A.Annotation_begTime, 'HH24:MI') || ' - ' || COALESCE(to_char(A.Annotation_endTime, 'HH24:MI'), '') as \"Annotation_Time\"


				,A.Annotation_Comment as \"Annotation_Comment\"
				-- end select
			from
				-- from
				v_Annotation A 

				inner join v_AnnotationType AT  on AT.AnnotationType_id = A.AnnotationType_id

				inner join v_AnnotationClass AC  on AC.AnnotationClass_id = AT.AnnotationClass_id

				inner join v_AnnotationVison AV  on AV.AnnotationVison_id = A.AnnotationVison_id

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
				A.Annotation_id as \"Annotation_id\"
				,A.MedStaffFact_id as \"MedStaffFact_id\"
				,A.MedService_id as \"MedService_id\"
				,A.Resource_id as \"Resource_id\"
				,A.AnnotationType_id as \"AnnotationType_id\"
				,A.AnnotationVison_id as \"AnnotationVison_id\"
				,to_char(A.Annotation_begDate, 'DD.MM.YYYY') as \"Annotation_begDate\"

				,to_char(A.Annotation_endDate, 'DD.MM.YYYY') as \"Annotation_endDate\"

				,to_char(A.Annotation_begTime, 'HH24:MI:SS') as \"Annotation_begTime\"

				,to_char(A.Annotation_endTime, 'HH24:MI:SS') as \"Annotation_endTime\"

				,A.Annotation_Comment as \"Annotation_Comment\"
				-- end select
			from
				-- from
				v_Annotation A 

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
            $data['MedStaffFact_id'] = $this->getFirstResultFromQuery("select MedStaffFact_id from v_Annotation  where Annotation_id = :Annotation_id", array(

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
        SELECT
        Annotation_id as \"Annotation_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                Annotation_id => :Annotation_id,
				AnnotationType_id => :AnnotationType_id,
				MedStaffFact_id => :MedStaffFact_id,
				MedService_id => :MedService_id,
				Resource_id => :Resource_id,
				AnnotationVison_id => :AnnotationVison_id,
				Annotation_Comment => :Annotation_Comment,
				Annotation_begDate => :Annotation_begDate,
				Annotation_begTime => :Annotation_begTime,
				Annotation_endDate => :Annotation_endDate,
				Annotation_endTime => :Annotation_endTime,
				pmUser_id => :pmUser_id
        )
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
				select AnnotationClass_id as \"AnnotationClass_id\"
				from v_AnnotationType 
				where AnnotationType_id = :AnnotationType_id
				limit 1
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
				A.Annotation_id as \"Annotation_id\"
				-- end select
			from
				-- from
				v_Annotation A 

				inner join v_AnnotationType AT  on AT.AnnotationType_id = A.AnnotationType_id

				-- end from
			where
				-- where
				{$filter} and
				A.Annotation_id != COALESCE(:Annotation_id, 0::bigint) and

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
	 * @param $data
	 * @return array|bool
	 */
    function getRegAnnotation($data)
    {
	    $params = [
		    "nulltime" => "00:00:00",
		    "Lpu_id" => $data["Lpu_id"]
	    ];
	    if (isset($data["MedStaffFact_id"])) {
		    $params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		    $filter = "An.MedStaffFact_id = :MedStaffFact_id";
	    } elseif (isset($data["Resource_id"])) {
		    $params["Resource_id"] = $data["Resource_id"];
		    $filter = "An.Resource_id = :Resource_id";
	    } else {
		    return false;
	    }
	    $sql = "
			select
				rtrim(An.Annotation_Comment) as \"Annotation_Comment\",
				to_char(An.Annotation_updDT, 'HH24:MI dd.mm.yyyy') as \"Annotation_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\"
			from
				v_Annotation An 
				left join v_pmUser u  on u.pmUser_id = An.pmUser_updID
				left join v_MedStaffFact msf  on msf.MedStaffFact_id = An.MedStaffFact_id
				left join v_Resource r  on r.Resource_id = An.Resource_id
				left join v_MedService ms  on ms.MedService_id = r.MedService_id
			where {$filter}
			  and An.Annotation_begDate is null
			  and An.Annotation_endDate is null
			  and (An.Annotation_begTime is null or An.Annotation_begTime = :nulltime)
			  and (An.Annotation_endTime is null or An.Annotation_endTime = :nulltime)
			  and (An.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id or ms.Lpu_id = :Lpu_id)
		";
	    /**@var CI_DB_result $result */
	    $result = $this->db->query($sql, $params);
	    return (is_object($result)) ? $result->result("array") : false;
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
				An.MedStaffFact_id as \"MedStaffFact_id\",
				rtrim(An.Annotation_Comment) as \"Annotation_Comment\",
				to_char(An.Annotation_updDT, 'dd.mm.yyyy HH24:mi:ss') as \"Annotation_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\"
			from v_Annotation An 

				left join v_MedStaffFact msf  on msf.MedStaffFact_id = An.MedStaffFact_id

				left join v_pmUser u  on u.pmUser_id = An.pmUser_updID

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
				A.Annotation_id as \"Annotation_id\"
				-- end select
			from
				-- from
				v_Annotation A 

				inner join v_AnnotationType AT  on AT.AnnotationType_id = A.AnnotationType_id

				inner join v_{$object} TT  on

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
            $filter .= " and WorkData_begDate <= cast(:StartDate as date) ";
            // если не заполнена дата окончания, на всякий случай проверим, входит ли дата начала в интервал работы
            if (empty($data['Annotation_endDate'])) {
                $filter .= " and COALESCE(cast(WorkData_endDate as date), '2030-01-01') >= cast(:StartDate as date) ";

            }
        }

        if (!empty($data['Annotation_endDate'])) {
            $params['EndDate'] = $data['Annotation_endDate'];
            $filter .= " and COALESCE(cast(WorkData_endDate as date), '2030-01-01') >= cast(:EndDate as date) ";

        }

        $sql = "
			select count(*) as cnt
			from v_MedStaffFact 

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
                Annotation_Comment as \"Annotation_Comment\",
                Annotation_begDate as \"Annotation_begDate\",
                Annotation_endDate as \"Annotation_endDate\",
                Annotation_begTime as \"Annotation_begTime\",
                Annotation_endTime as \"Annotation_endTime\",
                (d.day_id+1) as \"day_id\"
            from v_Annotation A
            left join v_Day d on 
				(cast(A.Annotation_begDate as date) <= cast(D.day_date as date) OR A.Annotation_begDate is null) AND
				(cast(A.Annotation_endDate as date) >= cast(D.day_date as date) OR A.Annotation_endDate is null) AND
				A.AnnotationVison_id = 1 
				
            where
                A.MedStaffFact_id = :doctor_id
                and d.Day_id >= :TimeTableGraf_Day_Start
                and d.Day_id < :TimeTableGraf_Day_End
                and (A.AnnotationVison_id = 1 OR A.AnnotationType_id = 4)
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
            from v_Annotation A
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