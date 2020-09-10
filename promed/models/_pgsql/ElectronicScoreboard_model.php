<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicScoreboard_model - модель для работы со справочником  электронных табло
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class ElectronicScoreboard_model extends SwPgModel {

    /**
     * Удаление табло
     */
    function delete($data) {

        $result = array();
        $error = array();

        //$this->beginTransaction();

        $query = "
			select
				ElectronicScoreboardQueueLink_id as \"ElectronicScoreboardQueueLink_id\"
			from
				v_ElectronicScoreboardQueueLink
			where
				ElectronicScoreboard_id = :ElectronicScoreboard_id
		";

        $resp = $this->queryResult($query, $data);

        if (!empty($resp)) {

            foreach ($resp as $queueLink) {

                $response = $this->deleteElectronicScoreboardQueueLink(array(
                    'ElectronicScoreboardQueueLink_id' => $queueLink['ElectronicScoreboardQueueLink_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }
            }
        }

        if (count($error) > 0) {
            $result['success'] = false;
            $result['Error_Msg'] = $error[0];
        } else {

            $query = "
            SELECT
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\"
            FROM
            p_ElectronicScoreboard_del(
              ElectronicScoreboard_id => :ElectronicScoreboard_id
            )
            ";

            $resp = $this->queryResult($query, $data);
            if (!empty($resp['Error_Msg'])) {
                $error[] = $resp['Error_Msg'];
            }

            if (count($error) > 0) {
                $result['success'] = false;
                $result['Error_Msg'] = $error[0];
            } else {
                $result['success'] = true;
            }
        }

        //$this->commitTransaction();
        return array($result);
    }

    /**
     * Возвращает список табло
     */
    function loadList($data) {

        $filter = "";
        $queryParams = array();

        if (!empty($data['f_Lpu_id'])) {
            $filter .= " and es.Lpu_id = :Lpu_id";
            $queryParams['Lpu_id'] = $data['f_Lpu_id'];
        }

        if (!empty($data['LpuBuilding_id'])) {
            $filter .= " and es.LpuBuilding_id = :LpuBuilding_id";
            $queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
        }

        if (!empty($data['ElectronicScoreboard_Code'])) {
            $filter .= " and es.ElectronicScoreboard_Code = :ElectronicScoreboard_Code";
            $queryParams['ElectronicScoreboard_Code'] = $data['ElectronicScoreboard_Code'];
        }

        if (!empty($data['ElectronicScoreboard_Name'])) {
            $filter .= " and es.ElectronicScoreboard_Name like '%' || :ElectronicScoreboard_Name || '%'";
            $queryParams['ElectronicScoreboard_Name'] = $data['ElectronicScoreboard_Name'];
        }

        if (isset($data['ElectronicScoreboard_WorkRange'])) {

            list($begDate, $endDate) = explode('-', $data['ElectronicScoreboard_WorkRange']);

            if (!empty($begDate) && !empty($endDate)) {

                $filter .= " and es.ElectronicScoreboard_begDate >= :ElectronicScoreboard_begDate
                            and (es.ElectronicScoreboard_endDate <= :ElectronicScoreboard_endDate or es.ElectronicScoreboard_endDate IS NULL)
                ";

                $queryParams['ElectronicScoreboard_begDate'] = date('Y-m-d', strtotime(trim($begDate)));
                $queryParams['ElectronicScoreboard_endDate'] = date('Y-m-d', strtotime(trim($endDate)));
            }
        }

        $query = "
			select
				-- select
				es.ElectronicScoreboard_id as \"ElectronicScoreboard_id\"
				,es.Lpu_id as \"Lpu_id\"
				,es.LpuBuilding_id as \"LpuBuilding_id\"
				,es.ElectronicScoreboard_Code as \"ElectronicScoreboard_Code\"
				,es.ElectronicScoreboard_Name as \"ElectronicScoreboard_Name\"
				,to_char(es.ElectronicScoreboard_begDate, 'DD.MM.YYYY') as \"ElectronicScoreboard_begDate\"
				,to_char(es.ElectronicScoreboard_endDate, 'DD.MM.YYYY') as \"ElectronicScoreboard_endDate\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
				,substring(CAST(esqCodes.ElectronicQueueInfo_Codes AS varchar), 1, length(CAST(esqCodes.ElectronicQueueInfo_Codes AS varchar))-1) as \"ElectronicQueues\"
				,ElectronicScoreboard_IsLED as \"ElectronicScoreboard_IsLED\"
				,case 
					when ElectronicScoreboard_IsLED = 2 then 'Табло' 
					when ElectronicScoreboard_IsShownTimetable = 2 then 'ТВ-расписание' 
					else 'ТВ-ЭО' 
				end as \"ElectronicScoreboard_Type\"
				,ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\"
				,ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\"
				-- end select
			from
				-- from
				v_ElectronicScoreboard es
				left join v_Lpu l on l.Lpu_id = es.Lpu_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = es.LpuBuilding_id
				LEFT JOIN LATERAL (
					Select (select
                    string_agg(coalesce(CAST(eqi.ElectronicQueueInfo_Code as VARCHAR(10)),'') , ',') as \"data\"
                    from v_ElectronicQueueInfo eqi
                    inner join v_ElectronicScoreboardQueueLink esql on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
                    where esql.ElectronicScoreboard_id = es.ElectronicScoreboard_id) as ElectronicQueueInfo_Codes
				) esqCodes on true
				-- end from
			where
				-- where
				(1=1)
				{$filter}
				-- end where
			order by
				-- order by
				es.ElectronicScoreboard_begDate desc
				-- end order by
		";

        $response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

        $infomatBase = $this->config->item('infomat_base');
        $infomatDomain = '';
        if(!empty($infomatBase)){
            $infomatDomain = preg_replace('/^(http.?:)?\/\//', '', $infomatBase);
        }

        foreach($response['data'] as $key => &$board){
            if(2 === (int)$board['ElectronicScoreboard_IsLED']){
                $board['Scoreboard_Addr'] = '';
            }elseif(empty($infomatBase)){
                $board['Scoreboard_Addr'] = $board['ElectronicScoreboard_id'];
            }else{
                $board['Scoreboard_Addr'] = '<a href="' . $infomatBase . '/scoreboard/' . $board['ElectronicScoreboard_id'] . '" target="_blank">' . $infomatDomain . '/scoreboard/' . $board['ElectronicScoreboard_id'] . '</a>';
            }
        }

        return $response;
    }

    /**
     * Возвращает табло
     */
    function load($data) {

        $query = "
			select
				ElectronicScoreboard_id as \"ElectronicScoreboard_id\"
				,Lpu_id as \"Lpu_id\"
				,LpuBuilding_id as \"LpuBuilding_id\"
				,LpuSection_id as \"LpuSection_id\"
				,ElectronicScoreboard_Code as \"ElectronicScoreboard_Code\"
				,ElectronicScoreboard_Name as \"ElectronicScoreboard_Name\"
                ,case when ElectronicScoreboard_IsLED = 2 then 2 else 1 end as \"ElectronicScoreboard_IsLED\"
                ,case when ElectronicScoreboard_IsShownTimetable = 2 then 2 else 1 end as \"ElectronicScoreboard_IsShownTimetable\"
				,ElectronicScoreboard_RefreshInSeconds as \"ElectronicScoreboard_RefreshInSeconds\"
                ,ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\"
                ,ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\"
				,to_char(ElectronicScoreboard_begDate, 'DD.MM.YYYY') as \"ElectronicScoreboard_begDate\"
				,to_char(ElectronicScoreboard_endDate, 'DD.MM.YYYY') as \"ElectronicScoreboard_endDate\"
				,case when ElectronicScoreboard_IsCalled = 2 then 1 else 0 end as \"ElectronicScoreboard_IsCalledCheckbox\"
				,case when ElectronicScoreboard_IsShownForEachDoctor = 2 then 1 else 0 end as \"ElectronicScoreboard_IsShownForEachDoctor\"
			from
				v_ElectronicScoreboard
			where
				ElectronicScoreboard_id = :ElectronicScoreboard_id

		";

        return $this->queryResult($query, $data);
    }

    /**
     * Возвращает список очередей для табло
     */
    function loadElectronicScoreboardQueues($data)
    {
        $query = "
			select
				esql.ElectronicScoreboardQueueLink_id as \"ElectronicScoreboardQueueLink_id\"
				,esql.ElectronicScoreboard_id as \"ElectronicScoreboard_id\"
				,eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,eqi.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\"
				,eqi.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
				,es.ElectronicService_id as \"ElectronicService_id\"
				,es.ElectronicService_Name as \"ElectronicService_Name\"
				,ms.MedService_Name as \"MedService_Name\"
				,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
				,ls.LpuSection_Name as \"LpuSection_Name\"
			from
				v_ElectronicScoreboardQueueLink esql
				left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = esql.ElectronicQueueInfo_id
				left join v_ElectronicService es on es.ElectronicService_id = esql.ElectronicService_id
				left join v_MedService ms on ms.MedService_id = eqi.MedService_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = eqi.LpuBuilding_id
				left join v_LpuSection ls on ls.LpuSection_id = eqi.LpuSection_id
			where
				ElectronicScoreboard_id = :ElectronicScoreboard_id

		";

        return $this->queryResult($query, $data);
    }

    /**
     * Сохраняет табло
     */
    function save($data) {

        $procedure = empty($data['ElectronicScoreboard_id'])
            ? 'p_ElectronicScoreboard_ins'
            : 'p_ElectronicScoreboard_upd';

        $data['ElectronicScoreboard_IsLED'] = (!empty($data['ElectronicScoreboard_IsLED']) ? $data['ElectronicScoreboard_IsLED'] : 1);

        $query = "
        SELECT
        ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM {$procedure}(
                ElectronicScoreboard_id => :ElectronicScoreboard_id,
				Lpu_id => :Lpu_id,
				LpuBuilding_id => :LpuBuilding_id,
				ElectronicScoreboard_Code => :ElectronicScoreboard_Code,
				ElectronicScoreboard_Name => :ElectronicScoreboard_Name,
				ElectronicScoreboard_IsLED => :ElectronicScoreboard_IsLED,
                ElectronicScoreboard_IPaddress => :ElectronicScoreboard_IPaddress,
                ElectronicScoreboard_Port => :ElectronicScoreboard_Port,
				ElectronicScoreboard_begDate => :ElectronicScoreboard_begDate,
				ElectronicScoreboard_endDate => :ElectronicScoreboard_endDate,
				LpuSection_id => :LpuSection_id,
				ElectronicScoreboard_IsShownTimetable => :ElectronicScoreboard_IsShownTimetable,
				ElectronicScoreboard_RefreshInSeconds => :ElectronicScoreboard_RefreshInSeconds,
				ElectronicScoreboard_IsCalled => :ElectronicScoreboard_IsCalled,
				ElectronicScoreboard_IsShownForEachDoctor => :ElectronicScoreboard_IsShownForEachDoctor,
				pmUser_id => :pmUser_id
        )
        ";

        $resp = $this->queryResult($query, $data);
        return $resp;
    }

    /**
     * Сохраняет связь табло-очередь для всех записей
     */
    function updateElectronicScoreboardQueueLink($data) {

        $result = array();
        $error = array();

        if (!empty($data['jsonData']) && $data['ElectronicScoreboard_id'] > 0) {
            ConvertFromWin1251ToUTF8($data['jsonData']);
            $records = (array) json_decode($data['jsonData']);

            // сохраняем\удаляем все записи из связанного грида по очереди
            foreach($records as $record) {

                if (count($error) == 0) {

                    switch($record->state) {

                        case 'add':
                        case 'edit':

                            $response = $this->saveObject('ElectronicScoreboardQueueLink', array(
                                'ElectronicScoreboardQueueLink_id' => $record->state == 'add' ? null : $record->ElectronicScoreboardQueueLink_id,
                                'ElectronicScoreboard_id' => $data['ElectronicScoreboard_id'],
                                'ElectronicQueueInfo_id' => $record->ElectronicQueueInfo_id,
                                'ElectronicService_id' => (!empty($record->ElectronicService_id) ? $record->ElectronicService_id : null ),
                                'pmUser_id' => $data['pmUser_id']
                            ));
                            break;

                        case 'delete':

                            $response = $this->deleteElectronicScoreboardQueueLink(array(
                                'ElectronicScoreboardQueueLink_id' => $record->ElectronicScoreboardQueueLink_id
                            ));
                            if (!empty($response['Error_Msg'])) {
                                $error[] = $response['Error_Msg'];
                            }
                            break;
                    }
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                    }
                }

                if (count($error) > 0) {
                    break;
                }
            }
        }

        if (count($error) > 0) {
            $result['success'] = false;
            $result['Error_Msg'] = $error[0];
        } else {
            $result['success'] = true;
        }

        return array($result);
    }

    /**
     * Удаление связи табло-очередь
     */
    function deleteElectronicScoreboardQueueLink($data) {

        $result = array();
        $error = array();

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        dbo.p_ElectronicScoreboardQueueLink_del(
          ElectronicScoreboardQueueLink_id => :ElectronicScoreboardQueueLink_id
        )
        ";

        $result = $this->getFirstRowFromQuery($query, $data);

        if ($result && is_array($result)) {
            if(empty($result['Error_Msg'])) {
                $result['success'] = true;
            }
            $response = $result;
        } else {
            $response = array('Error_Msg' => 'При удалении произошла ошибка');
        }

        if (!empty($response['Error_Msg'])) {
            $error[] = $response['Error_Msg'];
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Подгрузка комбо
     */
	function loadElectronicQueueInfoCombo($data) {

		$filter = '';
		$params['Lpu_id'] = $data['Lpu_id'];

		if (!empty($data['LpuBuilding_id'])) {
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filter .= " and (EQI.LpuBuilding_id = :LpuBuilding_id or EQI.LpuBuilding_id is null) ";
		}

		$query = "
			select
				EQI.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				EQI.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\",
				EQI.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
			from v_ElectronicQueueInfo EQI
			where (1=1)
				and EQI.Lpu_id = :Lpu_id
				{$filter}
            order by EQI.ElectronicQueueInfo_begDate desc
		";

		$resp = $this->queryResult($query, $params);
		return $resp;
	}


    /**
     * Подгрузка комбо (ПО)
     */
    function loadElectronicServiceCombo($data) {

        $params['ElectronicQueueInfo_id'] = $data['ElectronicQueueInfo_id'];

        $query = "
			select
				ElectronicService_id as \"ElectronicService_id\",
				ElectronicService_Name as \"ElectronicService_Name\",
				ElectronicService_Nick as \"ElectronicService_Nick\",
				ElectronicService_Code as \"ElectronicService_Code\"
			from
				v_ElectronicService
			where
				ElectronicQueueInfo_id = :ElectronicQueueInfo_id
		";

        $resp = $this->queryResult($query, $params);
        return $resp;
    }

    /**
     * Возвращает список всех связанных c табло ЛПУ
     */
    function loadAllRelatedLpu() {

        $query = "
			select distinct
				lpu.Lpu_id as \"Lpu_id\"
				,lpu.Lpu_Name as \"Lpu_Name\"
				,lpu.Lpu_Nick as \"Lpu_Nick\"
			from
				v_ElectronicScoreboard es
				inner join v_Lpu lpu on lpu.Lpu_id = es.Lpu_id
		";

        $resp = $this->queryResult($query);
        return $resp;
    }

    /**
     * Обновляет страницу браузера на ТВ через socket.io и NodeJS
     */
    function refreshScoreboardBrowserPage($data){

        // нагребаем параметры для НОДА
        $nodeParams = array(
            'message' => 'RefreshScoreboardBrowserPage',
            'ElectronicScoreboard_id' => $data['ElectronicScoreboard_id'],
        );

        $this->load->model('ElectronicTalon_model');

        // отправляем сообщение в нод-серверу
        $this->ElectronicTalon_model->sendElectronicQueueNodeMessage($nodeParams);
        return array(array('success'=> true));
    }
}