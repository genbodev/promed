<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicService_model - модель для работы с Пунктами обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class ElectronicService_model extends SwPgModel {

    /**
     * Удаление пункта
     */
    function delete($data) {

        $res = $this->getFirstResultFromQuery("
			select count(*)
			from v_MedServiceElectronicQueue
			where ElectronicService_id = :ElectronicService_id
		", $data);

        if ($res > 0) {
            return $this->createError('', 'Пункт обслуживания связан с сотрудником на службе. Удаление невозможно.');
        }

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_ElectronicService_del(
          ElectronicService_id => :ElectronicService_id
        )
        ";

        return $this->queryResult($query, $data);
    }

    /**
     * Возвращает список пунктов
     */
    function loadList($data) {

        $query = "
			select
				-- select
				ElectronicService_id as \"ElectronicService_id\"
				,ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,ElectronicService_Code as \"ElectronicService_Code\"
				,ElectronicService_Num as \"ElectronicService_Num\"
				,ElectronicService_Name as \"ElectronicService_Name\"
				,ElectronicService_Nick as \"ElectronicService_Nick\"
				,ElectronicService_tid as \"ElectronicService_tid\"
			   	,SurveyType_id as \"SurveyType_id\"
				,case when ElectronicService_isShownET = 2 then 'true' else 'false' end as \"ElectronicService_isShownET\"
				-- end select
			from
				-- from
				v_ElectronicService
				-- end from
			where
				-- where
				ElectronicQueueInfo_id = :ElectronicQueueInfo_id
				-- end where
			order by
				-- order by
				ElectronicService_Num
				-- end order by
		";

        return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
    }

    /**
     * Возвращает список пунктов (фильтрация по службам, подразделениям или отделениям)
     */
    function loadElectronicServicesList($data) {

        $filter = '';

        if (!empty($data['MedService_id'])) {
            $filter = 'eq.MedService_id = :MedService_id';
        }

        if (!empty($data['LpuBuilding_id'])) {
            $filter = 'eq.LpuBuilding_id = :LpuBuilding_id';
        }

        if (!empty($data['LpuSection_id'])) {
            $filter = 'eq.LpuSection_id = :LpuSection_id';
        }

        $query = "
			select
				-- select
				es.ElectronicService_id as \"ElectronicService_id\"
				,es.ElectronicService_Code as \"ElectronicService_Code\"
				,es.ElectronicService_Num as \"ElectronicService_Num\"
				,es.ElectronicService_Name as \"ElectronicService_Name\"
				,es.ElectronicService_Nick as \"ElectronicService_Nick\"
				,eq.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
				-- end select
			from
				-- from
				v_ElectronicService es
				inner join v_ElectronicQueueInfo eq on eq.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				es.ElectronicService_Code
				-- end order by
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Возвращает список осмотров / исследований с фильтрацией по возрастной группе
     */
    function loadSurveyTypeList($data) {
        $queryParams = [];
        $filter = "";
        $join = "";
        if(!empty($data['AgeGroupDisp_id']) || !empty($data['DispClass_id'])) {
            $join .= " inner join v_SurveyTypeLink STL on STL.SurveyType_id = ST.SurveyType_id";
        }
        if (!empty($data['AgeGroupDisp_id'])) {
            $join .= "
				LEFT JOIN LATERAL (
					select *
					from AgeGroupDisp
					where AgeGroupDisp_id = :AgeGroupDisp_id
					limit 1
				) as AGD on true
			";
            $filter .= "				
				and (
					( -- если границы не указаны выводим для всех групп
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is null				
					)
					or ( -- если указана только нижняя граница (оба поля)
						STL.SurveyTypeLink_From is not null
						and STL.SurveyTypeLink_monthFrom is not null
						and STL.SurveyTypeLink_To is null
						and STL.SurveyTypeLink_monthTo is null

						and (
							AGD.AgeGroupDisp_From >= STL.SurveyTypeLink_From
							or AGD.AgeGroupDisp_From is null
						)
						and (
							AGD.AgeGroupDisp_monthFrom >= STL.SurveyTypeLink_monthFrom
							or AGD.AgeGroupDisp_monthFrom is null
						)
					)
					or (  --указана нижняя граница(SurveyTypeLink_From)
						STL.SurveyTypeLink_From is not null
						and STL.SurveyTypeLink_To is null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is null

						and (
							AGD.AgeGroupDisp_From >= STL.SurveyTypeLink_From
							or AGD.AgeGroupDisp_From is null
						)
					)
					or (  --указана нижняя граница(SurveyTypeLink_monthFrom)
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is null
						and STL.SurveyTypeLink_monthFrom is not null
						and STL.SurveyTypeLink_monthTo is null

						and (
							AGD.AgeGroupDisp_monthFrom >= STL.SurveyTypeLink_monthFrom
							or AGD.AgeGroupDisp_monthFrom is null
						)
					)
					or ( --указана верхняя граница(оба поля)
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is not null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is not null

						and (
							AGD.AgeGroupDisp_monthTo <= STL.SurveyTypeLink_monthTo
							or AGD.AgeGroupDisp_monthTo is null
						)
						and (
							AGD.AgeGroupDisp_To <= STL.SurveyTypeLink_To
							or AGD.AgeGroupDisp_To is null
						)
					)
					or ( --указана верхняя граница(SurveyTypeLink_To)
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is not null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is null

						and( 
							AGD.AgeGroupDisp_To <= STL.SurveyTypeLink_To
							or AGD.AgeGroupDisp_To is null
						)
					)
					or ( --указана верхняя граница(SurveyTypeLink_To)
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is not null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is null

						and (
							AGD.AgeGroupDisp_To <= STL.SurveyTypeLink_To
							or AGD.AgeGroupDisp_To is null
						)
					)
					or ( --указана верхняя граница(SurveyTypeLink_monthTo)
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is not null

						and ( 
							AGD.AgeGroupDisp_monthTo <= STL.SurveyTypeLink_monthTo
							or AGD.AgeGroupDisp_monthTo is null
						)
					)
					or ( -- указаны все границы
						STL.SurveyTypeLink_From is not null
						and STL.SurveyTypeLink_To is not null
						and STL.SurveyTypeLink_monthFrom is not null
						and STL.SurveyTypeLink_monthTo is not null

						and (
							AGD.AgeGroupDisp_From >= STL.SurveyTypeLink_From
							or AGD.AgeGroupDisp_From is null
						)
						and (
							AGD.AgeGroupDisp_monthFrom >= STL.SurveyTypeLink_monthFrom
							or AGD.AgeGroupDisp_monthFrom is null
						)
						and (
							AGD.AgeGroupDisp_monthTo <= STL.SurveyTypeLink_monthTo
							or AGD.AgeGroupDisp_monthTo is null
						)
						and (
							AGD.AgeGroupDisp_To <= STL.SurveyTypeLink_To
							or AGD.AgeGroupDisp_To is null
						)
					)
					or ( -- указаны нижняя и верхняя границы(SurveyTypeLink_(From/To))
						STL.SurveyTypeLink_From is not null
						and STL.SurveyTypeLink_To is not null
						and STL.SurveyTypeLink_monthFrom is null
						and STL.SurveyTypeLink_monthTo is null

						and (
							AGD.AgeGroupDisp_From >= STL.SurveyTypeLink_From
							or AGD.AgeGroupDisp_From is null
						)
						and (
							AGD.AgeGroupDisp_To <= STL.SurveyTypeLink_To
							or AGD.AgeGroupDisp_To is null
						)
					)
					or ( --нижняя и верхняя(SurveyTypeLink_(monthFrom/monthTo)
						STL.SurveyTypeLink_From is null
						and STL.SurveyTypeLink_To is null
						and STL.SurveyTypeLink_monthFrom is not null
						and STL.SurveyTypeLink_monthTo is not null

						and (
							AGD.AgeGroupDisp_monthFrom >= STL.SurveyTypeLink_monthFrom
							or AGD.AgeGroupDisp_monthFrom is null
						)
						and (
							AGD.AgeGroupDisp_monthTo <= STL.SurveyTypeLink_monthTo
							or AGD.AgeGroupDisp_monthTo is null
						)
					)
				)
			";
            $queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
        }
        if(!empty($data['DispClass_id'])) {
            $filter .= " and STL.DispClass_id = :DispClass_id";
            $queryParams['DispClass_id'] = $data['DispClass_id'];
        }

        if(!empty($data['ElectronicQueueInfo_id'])) {
            $join .= " inner join v_ElectronicService ES on ES.SurveyType_id = ST.SurveyType_id";
            $filter .= " and ES.ElectronicQueueInfo_id = :ElectronicQueueInfo_id";
            $queryParams['ElectronicQueueInfo_id'] = $data['ElectronicQueueInfo_id'];
        }

        $query = "
			select distinct
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_code as \"SurveyType_code\",
				ST.SurveyType_name as \"SurveyType_name\"
			from
				v_SurveyType ST
				{$join}
			where
				(1=1)
				{$filter}
		";
        //echo getDebugSql($query,$queryParams);die;
        return $this->queryResult($query, $queryParams);
    }

    /**
     * Возвращает пункт
     */
    function load($data) {

        $query = "
			select
				ElectronicService_id as \"ElectronicService_id\"
				,ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,ElectronicService_Code as \"ElectronicService_Code\"
				,ElectronicService_Num as \"ElectronicService_Num\"
				,ElectronicService_Name as \"ElectronicService_Name\"
				,ElectronicService_Nick as \"ElectronicService_Nick\"
				,case when ElectronicService_isShownET = 2 then 'true' else 'false' end as \"ElectronicService_isShownET\"
			from
				v_ElectronicService
			where
				ElectronicService_id = :ElectronicService_id
		";

        return $this->queryResult($query, $data);
    }

    /**
     *	Возвращает порядок прохождения пунктов обслуживания(для проф. осмотра)
     */
    function loadElectronicServiceOrder($data) {
        $queryParams = array(
            'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id']
        );

        $filters = "";
        if (!empty($data['AgeGroupDisp_id'])) {
            $filters .= " and ESO.AgeGroupDisp_id = :AgeGroupDisp_id";
            $queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
        }
        if (!empty($data['SurveyType_id'])) {
            $filters .= " and ESO.SurveyType_id = :SurveyType_id";
            $queryParams['SurveyType_id'] = $data['SurveyType_id'];
        }
        if (!empty($data['ElectronicServiceOrder_Num'])) {
            $filters .= " and ESO.ElectronicServiceOrder_Num = :ElectronicServiceOrder_Num";
            $queryParams['ElectronicServiceOrder_Num'] = $data['ElectronicServiceOrder_Num'];
        }

        $query = "
			select
				-- select 
				ESO.ElectronicServiceOrder_id as \"ElectronicServiceOrder_id\",
				AGD.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				AGD.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_name as \"SurveyType_name\",
				ESO.DispClass_id as \"DispClass_id\",
				ESO.ElectronicServiceOrder_Num as \"ElectronicServiceOrder_Num\"
				-- end select
			from 
				-- from
				ElectronicServiceOrder ESO
				inner join v_AgeGroupDisp AGD on AGD.AgeGroupDisp_id = ESO.AgeGroupDisp_id
				inner join SurveyType ST on ST.SurveyType_id = ESO.SurveyType_id
				-- end from
			where
				-- where
				ESO.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
				and COALESCE(ESO.ElectronicServiceOrder_deleted, 0) != 2
				{$filters}
				-- end where
			ORDER BY 
				-- order by
				AgeGroupDisp_Name,
				ESO.ElectronicServiceOrder_Num
				-- end order by
		";

        return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
    }

    /**
     *	Проверяет существование списка порядка осмотров / исследований для возрастной группы
     */
    function checkExistAgeGroupOrderList($data)
    {
        if(!empty($data['ElectronicQueueInfo_id']) && !empty($data['AgeGroupDisp_id'])) {
            $queryParams = array(
                'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
                'AgeGroupDisp_id' => $data['AgeGroupDisp_id']
            );
        } else {
            return false;
        }

        $query = "
			SELECT *
			from ElectronicServiceOrder
			where 
				ElectronicQueueInfo_id = :ElectronicQueueInfo_id
				and AgeGroupDisp_id = :AgeGroupDisp_id
				and COALESCE(ElectronicServiceOrder_deleted, 0) != 2
				limit 1
		";
        $response = $this->queryResult($query, $queryParams);
        return !empty($response);
    }

    /**
     * Возвращает список возрастных групп
     */
    function loadAgeGroupDispList($data) {
        $join = '';
        $filters = '';
        if( !empty($data['DispClass_id']) ) {
            $join .= "inner join v_DispClassDispTypeLink DCL on DCL.DispType_id = AGD.DispType_id";

            $filters .= "and DCL.DispClass_id = :DispClass_id";
        }

        $query = "
			select 
				AGD.AgeGroupDisp_id as \"AgeGroupDisp_id\", 
				AGD.AgeGroupDisp_id as \"AgeGroupDisp_Code\",
				AGD.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",
				AGD.AgeGroupDisp_From as \"AgeGroupDisp_From\",
				AgeGroupDisp_To as \"AgeGroupDisp_To\",
				AGD.AgeGroupDisp_monthFrom as \"AgeGroupDisp_monthFrom\",
				AGD.AgeGroupDisp_monthTo as \"AgeGroupDisp_monthTo\",
				AGD.Sex_id as \"Sex_id\",
				AGD.DispType_id as \"DispType_id\",
				to_char(AGD.AgeGroupDisp_begDate, 'dd.mm.yyyy') as \"AgeGroupDisp_begDate\",
				to_char(AGD.AgeGroupDisp_endDate, 'dd.mm.yyyy') as \"AgeGroupDisp_endDate\"
				from 
					v_AgeGroupDisp AGD
					{$join}
				where
					(1=1)
					{$filters}
				order by 
					AGD.AgeGroupDisp_From, 
					AGD.AgeGroupDisp_monthFrom, 
					AGD.AgeGroupDisp_To, 
					AGD.AgeGroupDisp_monthTo
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Сохранение порядка прохождения осмотров / исследований
     */
    function saveOrderServicePoints($data) {
        $ServicePoints = (array)json_decode($data['ServicePoints']);

        $proc = 'p_ElectronicServiceOrder_ins';
        // if($data['action'] == 'add') {
        // 	$proc .= 'ins';
        // } else {
        // 	$proc .= 'upd';
        // }

        for($i = 1; $i <= $data['ServicePointsCount']; $i++) {
            $queryParams = array(
                'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
                'ElectronicServiceOrder_Num' => $i,
                'AgeGroupDisp_id' => $data['AgeGroupDisp_id'],
                'DispClass_id' => $data['DispClass_id'],
                'SurveyType_id' => $ServicePoints['SurveyType_id_' . $i],
                'pmUser_id' => $data['pmUser_id']
            );
            $query = "
            SELECT
            :ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
            ElectronicServiceOrder_id as \"ElectronicServiceOrder_id\",
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\"
            FROM
            {$proc}(
                    ElectronicServiceOrder_Num => :ElectronicServiceOrder_Num
					,AgeGroupDisp_id => :AgeGroupDisp_id
					,SurveyType_id => :SurveyType_id
					,ElectronicQueueInfo_id => :ElectronicQueueInfo_id
					,DispClass_id => :DispClass_id
					,pmUser_id => :pmUser_id
            )
            ";
            $result = $this->queryResult($query, $queryParams);
            if(!empty($result['Error_Code'])) {
                return $result;
            }
        }
        return $result;
    }

    /**
     * Удаляет пункты обслуживания из очереди, соответствующие указанной возрастной группе
     */
    function deleteOrderServicePoints($data) {
        if(!empty($data['ElectronicQueueInfo_id']) && !empty($data['AgeGroupDisp_id'])) {
            $queryParams = array(
                'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
                'AgeGroupDisp_id' => $data['AgeGroupDisp_id']
            );
        } else {
            return false;
        }

        $query = "
			SELECT ElectronicServiceOrder_id as \"ElectronicServiceOrder_id\"
			from ElectronicServiceOrder
			where 
				ElectronicQueueInfo_id = :ElectronicQueueInfo_id
				and AgeGroupDisp_id = :AgeGroupDisp_id
				and COALESCE(ElectronicServiceOrder_deleted, 0) != 2
		";
        $response = $this->queryResult($query, $queryParams);

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_ElectronicServiceOrder_del(
          ElectronicServiceOrder_id => :ElectronicServiceOrder_id
        )
        ";
        if(!empty($response[0])) {
            foreach ($response as $value) {
                $queryParams['ElectronicServiceOrder_id'] = $value['ElectronicServiceOrder_id'];
                $result = $this->queryResult($query, $queryParams);
                if(!empty($result['Error_Code'])) {
                    return $result;
                }
            }
            $response = $result;
        }
        return $response;
    }

    /**
     * Сохраняет пункт
     */
    function save($data) {

        // Проверка дубликатов номеров
        if (true !== ($response = $this->checkElectronicServiceDoubles($data))) {
            return $this->createError('', 'Порядковый номер должен быть уникален в рамках очереди');
        }

        $data['ElectronicService_isShownET'] = (($data['ElectronicService_isShownET']) ? 2 : 1);

        $procedure = empty($data['ElectronicService_id']) ? 'p_ElectronicService_ins' : 'p_ElectronicService_upd';

        $query = "
        SELECT
        ElectronicService_id as \"ElectronicService_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                ElectronicService_id => :ElectronicService_id,
				ElectronicQueueInfo_id => :ElectronicQueueInfo_id,
				ElectronicService_Code => :ElectronicService_Code,
				ElectronicService_Num => :ElectronicService_Num,
				ElectronicService_Name => :ElectronicService_Name,
				ElectronicService_Nick => :ElectronicService_Nick,
				ElectronicService_isShownET => :ElectronicService_isShownET,
				ElectronicService_tid => :ElectronicService_tid
				pmUser_id => :pmUser_id
        )
        ";

        return $this->queryResult($query, $data);
    }

    /**
     * Проверка дубликатов номеров
     */
    function checkElectronicServiceDoubles($data) {

        $query = "
			select count(*)
			from v_ElectronicService
			where 
				ElectronicService_Num = :ElectronicService_Num and
				ElectronicQueueInfo_id = :ElectronicQueueInfo_id
		";

        if (!empty($data['ElectronicService_id'])) {
            $query .= " and ElectronicService_id != :ElectronicService_id";
        }

        $res = $this->getFirstResultFromQuery($query, $data);

        if ($res > 0) {
            return false;
        }

        return true;
    }
}
