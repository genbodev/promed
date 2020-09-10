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

class WorkGraphMiddle_model extends swModel
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

        $query = "SELECT
      -- select
       wgm.WorkGraphMiddle_id,
       wgm.MedStaffFact_id,
       msf.Person_SurName,
       msf.Person_FirName,
       msf.Person_SecName,
       CONVERT(VARCHAR, wgm.WorkGraphMiddle_begDate, 126) WorkGraphMiddle_begDate,
       CONVERT(VARCHAR, wgm.WorkGraphMiddle_endDate, 126) WorkGraphMiddle_endDate
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
      -- end order by";

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
        $query = "DECLARE @dtNow DATETIME = dbo.tzGetDate(),
              @Error_Code BIGINT = NULL,
              @Error_Msg VARCHAR(4000) = NULL;

      SET NOCOUNT ON;

      BEGIN TRY
       INSERT INTO WorkGraphMiddle (MedStaffFact_id, WorkGraphMiddle_begDate, WorkGraphMiddle_endDate,
                                    WorkGraphMiddle_updDT, pmUser_insID, pmUser_updID, WorkGraphMiddle_insDT)
        VALUES (:MedStaffFact_id, :WorkGraphMiddle_begDate, :WorkGraphMiddle_endDate,
                @dtNow, :pmUser_id, :pmUser_id, @dtNow);
      END TRY

      BEGIN CATCH
       SET @Error_Code = ERROR_NUMBER();
       SET @Error_Msg = ERROR_MESSAGE();
      END CATCH

      SELECT @@IDENTITY WorkGraphMiddle_id, @Error_Code Error_Code, @Error_Msg Error_Msg";

        $res = $this->queryResult($query, $data);

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

        $query = "DECLARE @Error_Code BIGINT = NULL,
              @Error_Msg VARCHAR(4000) = NULL;

      SET NOCOUNT ON;

      BEGIN TRY
       UPDATE WorkGraphMiddle
        SET WorkGraphMiddle_updDT = dbo.tzGetDate(),
            pmUser_updID = :pmUser_id
            {$fields}
        WHERE WorkGraphMiddle_id = :WorkGraphMiddle_id
      END TRY

      BEGIN CATCH
       SET @Error_Code = ERROR_NUMBER();
       SET @Error_Msg = ERROR_MESSAGE();
      END CATCH

      SELECT @Error_Code Error_Code, @Error_Msg Error_Msg";

        $res = $this->queryResult($query, $data);

        if (is_array($res)) return $res;

        return false;
    }

    /**
     * Удаление дежурства
     */
    function delWorkGraphMiddle($data)
    {
        $query = "DECLARE @Error_Code BIGINT = NULL,
              @Error_Msg VARCHAR(4000) = NULL;

      SET NOCOUNT ON;

      BEGIN TRY
       DELETE FROM WorkGraphMiddle
        WHERE WorkGraphMiddle_id = :WorkGraphMiddle_id
      END TRY

      BEGIN CATCH
       SET @Error_Code = ERROR_NUMBER();
       SET @Error_Msg = ERROR_MESSAGE();
      END CATCH

      SELECT @Error_Code Error_Code, @Error_Msg Error_Msg";

        $res = $this->queryResult($query, $data);

        if (is_array($res)) return $res;

        return false;
    }
};

