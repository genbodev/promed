<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * WorkGraphMiddle_model - модель для работы с графиком дежурств среднего медперсонала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access    public
 * @copyright Copyright (c) 2019
 * @author
 * @version
 */

class WorkGraphMiddle_model extends SwPgModel
{
    /**
     * Получение списка дежурств
     */
    function selWorkGraphMiddle($data)
    {
        $where = '';
        $params = array();

        if (empty($data['start'])) $params['start'] = 0;
        else $params['start'] = $data['start'];

        if (empty($data['limit'])) $params['limit'] = 100;
        else $params['limit'] = $data['limit'];

        if (!empty($data['MedStaffFact_id']))
        {
            $where .= ' AND wgm.MedStaffFact_id = :MedStaffFact_id';
            $params['MedStaffFact_id'] = $data['MedStaffFact_id'];
        }

        if (!empty($data['fromDate']))
        {
            $where .= ' AND wgm.WorkGraphMiddle_endDate >= :fromDate';
            $params['fromDate'] = $data['fromDate'];
        }

        if (!empty($data['toDate']))
        {
            $where .= ' AND wgm.WorkGraphMiddle_begDate <= :toDate';
            $params['toDate'] = $data['toDate'] . 'T23:59:59.999';
        }

		$query = "
			SELECT
				-- select
				wgm.WorkGraphMiddle_id as \"WorkGraphMiddle_id\",
				wgm.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Person_SurName as \"Person_SurName\",
				msf.Person_FirName as \"Person_FirName\",
				msf.Person_SecName as \"Person_SecName\",
				to_char(wgm.WorkGraphMiddle_begDate, 'YYYY-MM-DD\"T\"HH24:MI:SS') as \"WorkGraphMiddle_begDate\",
				to_char(wgm.WorkGraphMiddle_endDate, 'YYYY-MM-DD\"T\"HH24:MI:SS') as \"WorkGraphMiddle_endDate\"
				-- end select
			FROM
				-- from
				v_WorkGraphMiddle wgm
				INNER JOIN v_MedStaffFact msf
				 ON msf.MedStaffFact_id = wgm.MedStaffFact_id
				-- end from
			WHERE
				-- where
				1 = 1 {$where}
				-- end where
			ORDER BY
				-- order by
				1, 2, 3, 4, 5
				-- end order by
		";

        //     $result = $this->db->query("insert into WorkGraphMiddle (MedStaffFact_id, WorkGraphMiddle_begDate, WorkGraphMiddle_endDate, WorkGraphMiddle_updDT, pmUser_insID, pmUser_updID, WorkGraphMiddle_insDT) values (99560114168, '2019-12-19T22:00:00', '2019-12-30T08:00:00', GETDATE(), 333697355564, 333697355564, GETDATE())", $params);
        $result = $this
            ->db
            ->query(getLimitSQLPH($query, $params['start'], $params['limit']) , $params);
        $result_count = $this
            ->db
            ->query(getCountSQLPH($query) , $params);

        if (is_object($result) && is_object($result_count)) return (array(
            'data' => $result->result('array') ,
            'totalCount' => $result_count->result('array') [0]['cnt']
        ));

        return false;
    }

    /**
     * Добавление дежурства
     */
    function addWorkGraphMiddle($data)
    {
		$query = "
			INSERT INTO WorkGraphMiddle (
				MedStaffFact_id,
				WorkGraphMiddle_begDate,
				WorkGraphMiddle_endDate,
				WorkGraphMiddle_updDT,
				pmUser_insID,
				pmUser_updID,
				WorkGraphMiddle_insDT
			)
			VALUES (
				:MedStaffFact_id,
				:WorkGraphMiddle_begDate,
				:WorkGraphMiddle_endDate,
				dbo.tzGetDate(),
				:pmUser_id,
				:pmUser_id,
				dbo.tzGetDate()
			)
			RETURNING WorkGraphMiddle_id as \"WorkGraphMiddle_id\", null as \"Error_Code\", null as \"Error_Msg\";
		";
		
		try {
			$res = $this->queryResult($query, $data);
		} catch (Exception $err) {
			$res = $this->createError(666, $err->getMessage());
		}
        //     if ($res[0]['Error_Code'] == null)
        //      $res[0]['Error_Code'] = "";
        if (is_array($res)) return $res;

        return false;
    }

    /**
     * Изменение дежурства
     */
    function updWorkGraphMiddle($data)
    {
        $fields = "";

        if (!empty($data['MedStaffFact_id'])) $fields .= ',MedStaffFact_id = :MedStaffFact_id';

        if (!empty($data['WorkGraphMiddle_begDate'])) $fields .= ',WorkGraphMiddle_begDate = :WorkGraphMiddle_begDate';

        if (!empty($data['WorkGraphMiddle_endDate'])) $fields .= ',WorkGraphMiddle_endDate = :WorkGraphMiddle_endDate';

		$query = "
			UPDATE WorkGraphMiddle
			SET WorkGraphMiddle_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
				{$fields}
			WHERE WorkGraphMiddle_id = :WorkGraphMiddle_id
			RETURNING null as \"Error_Code\", null as \"Error_Msg\"
		";

        try {
            $res = $this->queryResult($query, $data);
        }
        catch (Exception $err) {
            $res = $this->createError(666, $err->getMessage());
        }

        if (is_array($res)) return $res;

        return false;
    }

    /**
     * Удаление дежурства
     */
    function delWorkGraphMiddle($data)
    {
		$query = "
			DELETE FROM WorkGraphMiddle
			WHERE WorkGraphMiddle_id = :WorkGraphMiddle_id
			RETURNING null as \"Error_Code\", null as \"Error_Msg\"
		";

        try {
            $res = $this->queryResult($query, $data);
        }
        catch(Exception $err) {
            $res = $this->createError(666, $err->getMessage());
        }

        if (is_array($res)) return $res;

        return false;
    }
};

