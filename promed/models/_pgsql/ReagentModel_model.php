<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов привязки реагента к модели анализатора
 *
 * @package      common
 * @access       public
 * @author       Arslanov Azat
 */
class ReagentModel_model extends SwPgModel
{
    private $ReagentModel_id;//
    private $AnalyzerModel_id;//Модель анализатора
    private $DrugNomen_id;//Реагент
    private $pmUser_id;//Идентификатор пользователя системы Промед

    /**
     * id
     */
    public function getReagentModel_id()
    {
        return $this->ReagentModel_id;
    }

    /**
     * id
     */
    public function setReagentModel_id($value)
    {
        $this->ReagentModel_id = $value;
    }

    /**
     * модель анализатора
     */
    public function getAnalyzerModel_id()
    {
        return $this->AnalyzerModel_id;
    }

    /**
     * модель анализатора
     */
    public function setAnalyzerModel_id($value)
    {
        $this->AnalyzerModel_id = $value;
    }

    /**
     * id лек-ва номенклатурного справочника
     */
    public function getDrugNomen_id()
    {
        return $this->DrugNomen_id;
    }

    /**
     * id лек-ва номенклатурного справочника
     */
    public function setDrugNomen_id($value)
    {
        $this->DrugNomen_id = $value;
    }

    /**
     * кто виноват
     */
    public function getpmUser_id()
    {
        return $this->pmUser_id;
    }

    /**
     * кто виноват
     */
    public function setpmUser_id($value)
    {
        $this->pmUser_id = $value;
    }

    /**
     * TO-DO: описать
     */
    function __construct()
    {
        if (isset($_SESSION['pmuser_id'])) {
            $this->setpmUser_id($_SESSION['pmuser_id']);
        } else {
            throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
        }
    }

    /**
     *  получение списка всех реактивов из номенклатурного справочника
     */
    public function loadReagentList($data)
    {
        $queryParams = [];
        $where = '';

        if (strlen($data['query']) > 0) {
            $queryParams['query'] = "%" . $data['query'] . "%";
            $where .= " AND (DN.DrugNomen_Code || ' ' || DN.DrugNomen_Nick) ilike replace(trim(:query),' ', '%') || '%'";
        }

        $q = "
		select 
			-- select
			DN.DrugNomen_id as \"Drug_id\",
			DN.DrugNomen_Code as \"Drug_Code\",
			DN.DrugNomen_Code || ' ' || DN.DrugNomen_Nick AS \"Drug_Name\"
			-- end select
		from
			-- from
			rls.v_DrugNomen DN
			inner join rls.v_Drug D on D.Drug_id = DN.Drug_id
			-- end from
		where
			-- where
			DN.PrepClass_id = 10
			" . $where . "
			-- end where
		order by
			-- order by
			DN.DrugNomen_Nick, DN.DrugNomen_Code
			-- end order by
		limit 100
		";
        $result = $this->db->query($q, $queryParams);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     *  получение реактива модели анализатора
     *
     * @param array $filter
     * @return bool
     */
    public function loadReagentModel($filter)
    {
        $params['ReagentModel_id'] = $filter['ReagentModel_id'];

        //todo снять не нужные селекты тут было *
        $q = "
			SELECT 
				-- select
				rm.ReagentModel_id as \"ReagentModel_id\",
				rm.pmUser_insID as \"pmUser_insID\",
				rm.pmUser_updID as \"pmUser_updID\",
				rm.ReagentModel_insDT as \"ReagentModel_insDT\",
				rm.ReagentModel_updDT as \"ReagentModel_updDT\",
				rm.DrugNomen_id as \"DrugNomen_id\",
				rm.AnalyzerModel_id as \"AnalyzerModel_id\",
				rm.ReagentModel_deleted as \"ReagentModel_deleted\",
				rm.pmUser_delID as \"pmUser_delID\",
				rm.ReagentModel_delDT as \"ReagentModel_delDT\",
				rm.ReagentModel_rowVersion as \"ReagentModel_rowVersion\",
				DN.DrugNomen_Name as \"DrugNomen_Name\"
				-- end select
			FROM 
				-- from
				lis.ReagentModel rm
				LEFT JOIN rls.v_DrugNomen DN on DN.DrugNomen_id = rm.DrugNomen_id
				-- end from
			WHERE 
				-- where
				rm.ReagentModel_id = :ReagentModel_id
				-- end where
		";
        $result = $this->db->query($q, $params);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     *  Получение списка реактивов модели анализатора
     *
     * @param array $filter
     * @return array|bool
     */
    public function loadReagentModelGrid($filter)
    {
        $params['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];

        //todo снять не нужные селекты тут было *
        $q = "
			SELECT 
				-- select
				rm.ReagentModel_id as \"ReagentModel_id\",
				rm.pmUser_insID as \"pmUser_insID\",
				rm.pmUser_updID as \"pmUser_updID\",
				rm.ReagentModel_insDT as \"ReagentModel_insDT\",
				rm.ReagentModel_updDT as \"ReagentModel_updDT\",
				rm.DrugNomen_id as \"DrugNomen_id\",
				rm.AnalyzerModel_id as \"AnalyzerModel_id\",
				rm.ReagentModel_deleted as \"ReagentModel_deleted\",
				rm.pmUser_delID as \"pmUser_delID\",
				rm.ReagentModel_delDT as \"ReagentModel_delDT\",
				rm.ReagentModel_rowVersion as \"ReagentModel_rowVersion\",
				DN.DrugNomen_Name as \"DrugNomen_Name\"
				-- end select
			FROM 
				-- from
				lis.ReagentModel rm
				LEFT JOIN rls.v_DrugNomen DN on DN.DrugNomen_id = rm.DrugNomen_id
				-- end from
			WHERE 
				-- where
				rm.AnalyzerModel_id = :AnalyzerModel_id
            AND
                (rm.ReagentModel_Deleted is null OR rm.ReagentModel_Deleted != 2) -- неудаленные
				-- end where
			order by
					-- order by
					DN.DrugNomen_Name
					-- end order by
		";

        $result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
        $result_count = $this->db->query(getCountSQLPH($q), $filter);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }

        if (!is_object($result))
            return false;

        $response = array();
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
    }

    /**
     * Сохранение реагента для модели анализатора
     *
     * @return array
     * @throws Exception
     */
    function saveReagentModel()
    {
        $procedure = 'p_ReagentModel_ins';
        if ($this->ReagentModel_id > 0) {
            $procedure = 'p_ReagentModel_upd';
        }
        $q = "
			select 
			    ReagentModel_id as \"ReagentModel_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from lis." . $procedure . "
			(
				ReagentModel_id := :ReagentModel_id,
				AnalyzerModel_id := :AnalyzerModel_id,
				DrugNomen_id := :DrugNomen_id,
				pmUser_id := :pmUser_id
			)
		";
        $p = [
            'ReagentModel_id' => $this->ReagentModel_id,
            'AnalyzerModel_id' => $this->AnalyzerModel_id,
            'DrugNomen_id' => $this->DrugNomen_id,
            'pmUser_id' => $this->pmUser_id,
        ];
        $r = $this->db->query($q, $p);
        if (!is_object($r)) {
            log_message('error', var_export(['q' => $q, 'p' => $p, 'e' => pg_last_error($this->getDb()->conn_id)], true));
            throw new Exception('Ошибка при выполнении запроса к базе данных');
        }

        $result = $r->result('array');
        $this->ReagentModel_id = $result[0]['ReagentModel_id'];
        return $result;
    }

    /**
     * Удаление реагента модели анализатора
     *
     * @return array|bool
     * @throws Exception
     */
    public function delete()
    {
        $query = "
            SELECT
                COUNT(1)
            FROM
                lis.v_AnalyzerTest at
                LEFT JOIN lis.ReagentNormRate rnr ON at.AnalyzerTest_id = rnr.AnalyzerTest_id
                LEFT JOIN lis.ReagentModel rm ON rnr.ReagentModel_id = rm.ReagentModel_id
            WHERE
                rm.ReagentModel_id = :ReagentModel_id
		";
        $tests_count = $this->getFirstResultFromQuery($query, [
            'ReagentModel_id' => $this->ReagentModel_id
        ]);
        if ($tests_count > 0) {
            throw new Exception("Нельзя удалить данный реактив, так как на него уже заведены тесты");
        }

        $q = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from lis.p_ReagentModel_del
			(
				ReagentModel_id := :ReagentModel_id
			)
		";
        $r = $this->db->query($q, array(
            'ReagentModel_id' => $this->ReagentModel_id
        ));

        if (!is_object($r))
            return false;

        return $r->result('array');
    }
}
