<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedStaffFactReplace_model - модель для работы с замещениями врачей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Polka
 * @access      public
 * @copyright   Copyright (c) 2017 Swan Ltd.
 * @author		Dmitrii Vlasenko
 * @version     10.09.2017
 */

class MedStaffFactReplace_model extends SwPgModel
{
    /**
     *    Конструктор
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Получение списка
     * @param $data
     * @return array|bool
     */
    function loadMedStaffFactReplaceGrid($data)
    {
        $params = array();
        $filters = "(1=1)";

        if (!empty($data['Lpu_id'])) {
            $filters .= " and MSF.Lpu_id = :Lpu_id";
            $params['Lpu_id'] = $data['Lpu_id'];
        }

        if (!empty($data['MedStaffFact_id'])) {
            $filters .= " and MSFR.MedStaffFact_id = :MedStaffFact_id";
            $params['MedStaffFact_id'] = $data['MedStaffFact_id'];
        }

        if (!empty($data['MedStaffFact_rid'])) {
            $filters .= " and MSFR.MedStaffFact_rid = :MedStaffFact_rid";
            $params['MedStaffFact_rid'] = $data['MedStaffFact_rid'];
        }

        if (!empty($data['MedStaffFactReplace_DateRange'][0]) && !empty($data['MedStaffFactReplace_DateRange'][1])) {
            $filters .= "
				and MSFR.MedStaffFactReplace_BegDate <= :MedStaffFactReplace_Date2
				and MSFR.MedStaffFactReplace_EndDate >= :MedStaffFactReplace_Date1
			";
            $params['MedStaffFactReplace_Date1'] = $data['MedStaffFactReplace_DateRange'][0];
            $params['MedStaffFactReplace_Date2'] = $data['MedStaffFactReplace_DateRange'][1];
        }

        $query = "
			select
				MSFR.MedStaffFactReplace_id as \"MedStaffFactReplace_id\",
				MSFR.MedStaffFact_rid as \"MedStaffFact_rid\",
				MSFR.MedStaffFact_id as \"MedStaffFact_id\",
				to_char (MSFR.MedStaffFactReplace_BegDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_BegDate\",
				to_char (MSFR.MedStaffFactReplace_EndDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_EndDate\",
				MSF2.Person_Fin || COALESCE(' (' || LS2.LpuSection_Name || ')', '') as \"MedStaffFact_rDesc\",
				MSF.Person_Fin || COALESCE(' (' || LS.LpuSection_Name || ')', '') as \"MedStaffFact_Desc\",
				pu.pmUser_Name as \"pmUser_Name\"
			from
				v_MedStaffFactReplace MSFR
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
				left join v_MedStaffFact MSF2 on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
				left join v_LpuSection LS2 on LS2.LpuSection_id = MSF2.LpuSection_id
				left join v_pmUser pu on pu.pmUser_id = MSFR.pmUser_insID
			where
				{$filters}
		";

        return $this->queryResult($query, $params);
    }

    /**
     * Сохранение
     * @param $data
     * @return bool
     */
    function saveMedStaffFactReplace($data)
    {
        if (empty($data['MedStaffFactReplace_id'])) {
            $data['MedStaffFactReplace_id'] = null;
            $procedure = 'p_MedStaffFactReplace_ins';
        } else {
            $procedure = 'p_MedStaffFactReplace_upd';
        }

        $params = array(
            'MedStaffFactReplace_id' => $data['MedStaffFactReplace_id'],
            'MedStaffFact_rid' => $data['MedStaffFact_rid'],
            'MedStaffFact_id' => $data['MedStaffFact_id'],
            'MedStaffFactReplace_BegDate' => $data['MedStaffFactReplace_BegDate'],
            'MedStaffFactReplace_EndDate' => $data['MedStaffFactReplace_EndDate'],
            'pmUser_id' => $data['pmUser_id']
        );

        // Исключить добавление дублирующих значений. Если найдено дублирующее значение, тогда выдается сообщение
        $resp = $this->queryResult("
			select
				MSFR.MedStaffFactReplace_id as \"MedStaffFactReplace_id\",
				MSF2.Person_Fin as \"Person_rFin\",
				MSF.Person_Fin as \"Person_Fin\",
				to_char (MSFR.MedStaffFactReplace_BegDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_BegDate\",
				to_char (MSFR.MedStaffFactReplace_EndDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_EndDate\"
			from
				v_MedStaffFactReplace MSFR
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_MedStaffFact MSF2 on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
			where
				MSFR.MedStaffFact_rid = :MedStaffFact_rid
				and MSFR.MedStaffFact_id = :MedStaffFact_id
				and MSFR.MedStaffFactReplace_BegDate = :MedStaffFactReplace_BegDate
				and MSFR.MedStaffFactReplace_EndDate = :MedStaffFactReplace_EndDate
				and MSFR.MedStaffFactReplace_id <> COALESCE(:MedStaffFactReplace_id, 0.0)
            limit 1
		", $params);
        if (!empty($resp[0]['MedStaffFactReplace_id'])) {
            return array('Error_Msg' => 'В период c ' . $resp[0]['MedStaffFactReplace_BegDate'] . ' по  ' . $resp[0]['MedStaffFactReplace_EndDate'] . ' ' . $resp[0]['Person_rFin'] . ' уже замещает ' . $resp[0]['Person_Fin']);
        }

        // Два врача не могут замещать одного врача в одно и тоже время. При пересечении периода дат замещения одного врача несколькими другими врачами, выдается сообщение
        $resp = $this->queryResult("
            select
				MSFR.MedStaffFactReplace_id as \"MedStaffFactReplace_id\",
				MSF2.Person_Fin as \"Person_rFin\",
				MSF.Person_Fin as \"Person_Fin\",
				to_char (MSFR.MedStaffFactReplace_BegDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_BegDate\",
				to_char (MSFR.MedStaffFactReplace_EndDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_EndDate\"
			from
				v_MedStaffFactReplace MSFR
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_MedStaffFact MSF2 on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
			where
				MSFR.MedStaffFact_id = :MedStaffFact_id
				and MSFR.MedStaffFactReplace_BegDate >= :MedStaffFactReplace_EndDate
				and MSFR.MedStaffFactReplace_EndDate <= :MedStaffFactReplace_BegDate
				and MSFR.MedStaffFactReplace_id <> COALESCE(:MedStaffFactReplace_id, 0.0)
            limit 1
		", $params);
        if (!empty($resp[0]['MedStaffFactReplace_id'])) {
            return array('Error_Msg' => $resp[0]['Person_Fin'] . ' в период c ' . $resp[0]['MedStaffFactReplace_BegDate'] . ' по  ' . $resp[0]['MedStaffFactReplace_EndDate'] . ' замещает врач ' . $resp[0]['Person_rFin']);
        }

        // Замещаемого врача нельзя выбрать как замещающего врача. Если в качестве замещающего врача выбирают врача, который в этом периоде является в каком-либо графике замещения замещающим, тогда выдается сообщение
        $resp = $this->queryResult("
			select
				MSFR.MedStaffFactReplace_id as \"MedStaffFactReplace_id\",
				MSF2.Person_Fin as \"Person_rFin\",
				MSF.Person_Fin as \"Person_Fin\",
				to_char (MSFR.MedStaffFactReplace_BegDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_BegDate\",
				to_char (MSFR.MedStaffFactReplace_EndDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_EndDate\"
			from
				v_MedStaffFactReplace MSFR
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = MSFR.MedStaffFact_id
				left join v_MedStaffFact MSF2 on MSF2.MedStaffFact_id = MSFR.MedStaffFact_rid
			where
				MSFR.MedStaffFact_id = :MedStaffFact_rid
				and MSFR.MedStaffFactReplace_BegDate >= :MedStaffFactReplace_EndDate
				and MSFR.MedStaffFactReplace_EndDate <= :MedStaffFactReplace_BegDate
				and MSFR.MedStaffFactReplace_id <> COALESCE(:MedStaffFactReplace_id, 0.0)
            limit 1
		", $params);
        if (!empty($resp[0]['MedStaffFactReplace_id'])) {
            return array('Error_Msg' => 'Врач ' . $resp[0]['Person_Fin'] . ' в период c ' . $resp[0]['MedStaffFactReplace_BegDate'] . ' по  ' . $resp[0]['MedStaffFactReplace_EndDate'] . ' уже определён в качестве замещаемого сотрудника. Выберите другого сотрудника.');
        }

        $query = "
            select
                MedStaffFactReplace_id as \"MedStaffFactReplace_id\", 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				MedStaffFactReplace_id := :MedStaffFactReplace_id,
				MedStaffFact_rid := :MedStaffFact_rid,
				MedStaffFact_id := :MedStaffFact_id,
				MedStaffFactReplace_BegDate := :MedStaffFactReplace_BegDate,
				MedStaffFactReplace_EndDate := :MedStaffFactReplace_EndDate,
				pmUser_id := :pmUser_id
			)
		";
        //echo getDebugSQL($query, $params);exit;
        return $this->queryResult($query, $params);
    }

    /**
     * Удаление
     */
    function deleteMedStaffFactReplace($data)
    {
        $params = array(
            'MedStaffFactReplace_id' => $data['id']
        );

        $query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from p_MedStaffFactReplace_del (
				MedStaffFactReplace_id := :MedStaffFactReplace_id
			)
		";

        return $this->queryResult($query, $params);
    }

    /**
     * Проверка
     */
    function checkExist($data)
    {
        $params = array(
            'MedStaffFact_id' => $data['MedStaffFact_id'],
            'begDate' => $data['begDate'],
            'endDate' => $data['endDate']
        );

        $query = "
			select
				MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFactReplace
			where
				MedStaffFact_rid = :MedStaffFact_id
				and MedStaffFactReplace_BegDate <= :endDate
				and MedStaffFactReplace_EndDate >= :begDate
            limit 1
		";

        $resp = $this->queryResult($query, $params);
        if (!empty($resp[0]['MedStaffFact_id'])) {
            return array('Error_Msg' => '', 'exist' => true);
        } else {
            return array('Error_Msg' => '');
        }
    }

    /**
     * Получение данных для формы
     * @param $data
     * @return bool
     */
    function loadMedStaffFactReplaceForm($data) {
        $params = array(
            'MedStaffFactReplace_id' => $data['MedStaffFactReplace_id']
        );

        $query = "
			select
				MSFR.MedStaffFactReplace_id as \"MedStaffFactReplace_id\",
				MSFR.MedStaffFact_rid as \"MedStaffFact_rid\",
				MSFR.MedStaffFact_id as \"MedStaffFact_id\",
				to_char (MSFR.MedStaffFactReplace_BegDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_BegDate\",
				to_char (MSFR.MedStaffFactReplace_EndDate, 'dd.mm.yyyy') as \"MedStaffFactReplace_EndDate\"
			from
				v_MedStaffFactReplace MSFR
			where
				MSFR.MedStaffFactReplace_id = :MedStaffFactReplace_id
            limit 1
		";

        return $this->queryResult($query, $params);
    }
}