<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicInfomat_model - модель для работы со справочником инфоматов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class ElectronicInfomat_model extends SwPgModel {

    /**
     * Удаление инфомата
     */
    function delete($data) {

        $result = array();
        $error = array();

        $this->beginTransaction();

        $query = "
			select
				ElectronicInfomatLink_id as \"ElectronicInfomatLink_id\"
			from
				v_ElectronicInfomatLink
			where
				ElectronicInfomat_id = :ElectronicInfomat_id
		";

        $resp = $this->queryResult($query, $data);

        if (!empty($resp)) {

            foreach ($resp as $queueLink) {

                $response = $this->deleteElectronicInfomatLink(array(
                    'ElectronicInfomatLink_id' => $queueLink['ElectronicInfomatLink_id']
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
            p_ElectronicInfomat_del(
              ElectronicInfomat_id => :ElectronicInfomat_id
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

        $this->commitTransaction();
        return array($result);
    }

    /**
     * Возвращает список инфоматов
     */
    function loadList($data) {

        $filter = "";
        $queryParams = array();

        if (!empty($data['f_Lpu_id'])) {
            $filter .= " and ei.Lpu_id = :Lpu_id";
            $queryParams['Lpu_id'] = $data['f_Lpu_id'];
        }

        if (!empty($data['LpuBuilding_id'])) {
            $filter .= " and ei.LpuBuilding_id = :LpuBuilding_id";
            $queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
        }

        if (!empty($data['ElectronicInfomat_Code'])) {
            $filter .= " and ei.ElectronicInfomat_Code = :ElectronicInfomat_Code";
            $queryParams['ElectronicInfomat_Code'] = $data['ElectronicInfomat_Code'];
        }

        if (!empty($data['ElectronicInfomat_Name'])) {
            $filter .= " and ei.ElectronicInfomat_Name like '%' || :ElectronicInfomat_Name || '%'";
            $queryParams['ElectronicInfomat_Name'] = $data['ElectronicInfomat_Name'];
        }

        if (isset($data['ElectronicInfomat_WorkRange'])) {

            list($begDate, $endDate) = explode('-', $data['ElectronicInfomat_WorkRange']);

            if (!empty($begDate) && !empty($endDate)) {

                $filter .= " and ei.ElectronicInfomat_begDate >= :ElectronicInfomat_begDate
                            and (ei.ElectronicInfomat_endDate <= :ElectronicInfomat_endDate or ei.ElectronicInfomat_endDate IS NULL)
                ";

                $queryParams['ElectronicInfomat_begDate'] = date('Y-m-d', strtotime(trim($begDate)));
                $queryParams['ElectronicInfomat_endDate'] = date('Y-m-d', strtotime(trim($endDate)));
            }
        }

        $query = "
			select
				-- select
				ei.ElectronicInfomat_id as \"ElectronicInfomat_id\"
				,ei.Lpu_id as \"Lpu_id\"
				,ei.LpuBuilding_id as \"LpuBuilding_id\"
				,ei.ElectronicInfomat_Code as \"ElectronicInfomat_Code\"
				,ei.ElectronicInfomat_Name as \"ElectronicInfomat_Name\"
				,to_char(ei.ElectronicInfomat_begDate, 'DD.MM.YYYY') as \"ElectronicInfomat_begDate\"
				,to_char(ei.ElectronicInfomat_endDate, 'DD.MM.YYYY') as \"ElectronicInfomat_endDate\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
				,substring(CAST(eiqCodes.ElectronicQueueInfo_Codes AS varchar), 1, length(CAST(eiqCodes.ElectronicQueueInfo_Codes AS varchar))-1) as \"ElectronicQueues\"
				-- end select
			from
				-- from
				v_ElectronicInfomat ei
				left join v_Lpu l on l.Lpu_id = ei.Lpu_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ei.LpuBuilding_id
				LEFT JOIN LATERAL (				    
				    Select (select
                    string_agg(coalesce(CAST(eqi.ElectronicQueueInfo_Code as VARCHAR(10)),'') , ',') as \"data\"
                    from v_ElectronicQueueInfo eqi
                    inner join v_ElectronicInfomatLink eil on eil.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
                    where eil.ElectronicInfomat_id = ei.ElectronicInfomat_id) as ElectronicQueueInfo_Codes
				) eiqCodes on true
				-- end from
			where
				-- where
				(1=1)
				{$filter}
				-- end where
			order by
				-- order by
				ei.ElectronicInfomat_begDate desc
				-- end order by
		";
        $response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

        $infomatBase = $this->config->item('infomat_base');
        $infomatDomain = '';
        if(!empty($infomatBase)){
            $infomatDomain = preg_replace('/^(http.?:)?\/\//', '', $infomatBase);
        }

        foreach($response['data'] as $key => &$infomat){
            if(empty($infomatBase)){
                $infomat['Infomat_Addr'] = $infomat['ElectronicInfomat_id'];
            }else{
                $infomat['Infomat_Addr'] = '<a href="' . $infomatBase . '/infomt/' . $infomat['ElectronicInfomat_id'] . '" target="_blank">' . $infomatDomain . '/infomt/' . $infomat['ElectronicInfomat_id'] . '</a>';
            }
        }

        return $response;
    }

    /**
     * Возвращает инфомат
     */
    function load($data) {

        $query = "
			select
				ei.ElectronicInfomat_id as \"ElectronicInfomat_id\"
				,ei.Lpu_id as \"Lpu_id\"
				,ei.LpuBuilding_id as \"LpuBuilding_id\"
				,ei.ElectronicInfomat_Code as \"ElectronicInfomat_Code\"
				,ei.ElectronicInfomat_Name as \"ElectronicInfomat_Name\"
				,ei.ElectronicInfomat_StartPage as \"ElectronicInfomat_StartPage\"
                ,case when ei.ElectronicInfomat_IsAllSpec = 2 then 'true' else 'false' end as \"ElectronicInfomat_IsAllSpec\"
				,case when ei.ElectronicInfomat_isPrintOut = 2 then 'true' else 'false' end as \"ElectronicInfomat_isPrintOut\"
				,to_char(ei.ElectronicInfomat_begDate, 'DD.MM.YYYY') as \"ElectronicInfomat_begDate\"
				,to_char(ei.ElectronicInfomat_endDate, 'DD.MM.YYYY') as \"ElectronicInfomat_endDate\"
				,substring(ElectronicInfomatButtonLinkStr.ElectronicInfomatButtonLink_Items, 1, length(ElectronicInfomatButtonLinkStr.ElectronicInfomatButtonLink_Items) - 1) as \"ElectronicInfomatButton_List\"
				,ei.ElectronicInfomat_IsPrintService as \"ElectronicInfomat_IsPrintService\"
			from
				v_ElectronicInfomat ei
				LEFT JOIN LATERAL (
					select (
						select string_agg(CAST(ElectronicInfomatButton_id as varchar), ',') as \"data\"
						from v_ElectronicInfomatButtonLink
						where ElectronicInfomat_id = ei.ElectronicInfomat_id						
					) as ElectronicInfomatButtonLink_Items
				) ElectronicInfomatButtonLinkStr on true
			where
				ei.ElectronicInfomat_id = :ElectronicInfomat_id
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Возвращает список очередей для инфомата
     */
    function loadElectronicInfomatQueues($data)
    {
        $query = "
			select
				eil.ElectronicInfomatLink_id as \"ElectronicInfomatLink_id\"
				,eil.ElectronicInfomat_id as \"ElectronicInfomat_id\"
				,eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,eqi.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\"
				,eqi.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
				,ms.MedService_Name as \"MedService_Name\"
				,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
				,ls.LpuSection_Name as \"LpuSection_Name\"
			from
				v_ElectronicInfomatLink eil
				left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = eil.ElectronicQueueInfo_id
				left join v_MedService ms on ms.MedService_id = eqi.MedService_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = eqi.LpuBuilding_id
				left join v_LpuSection ls on ls.LpuSection_id = eqi.LpuSection_id
			where
				ElectronicInfomat_id = :ElectronicInfomat_id

		";

        return $this->queryResult($query, $data);
    }

    /**
     * Возвращает список профилей для инфомата
     */
    function loadElectronicInfomatProfiles($data) {
        $query = "
			select
				eip.ElectronicInfomatProfile_id as \"ElectronicInfomatProfile_id\",
                eip.ElectronicInfomat_id as \"ElectronicInfomat_id\",
                eip.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                eip.ElectronicInfomatProfile_Position as \"ElectronicInfomatProfile_Position\",
                lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
                mso.MedSpecOms_id as \"MedSpecOms_id\",
                mso.MedSpecOms_Name as \"MedSpecOms_Name\",
                ltrim(lsp.ProfileSpec_Name) as \"ProfileSpec_Name\"
			from
				v_ElectronicInfomatProfile eip
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = eip.LpuSectionProfile_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = eip.MedSpecOms_id
			where
				eip.ElectronicInfomat_id = :ElectronicInfomat_id
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Загружает данные о профиле инофомата
     */
    function loadElectronicInfomatProfileForm($data) {

        $query = "
			select
				eip.ElectronicInfomatProfile_id as \"ElectronicInfomatProfile_id\",
                eip.ElectronicInfomat_id as \"ElectronicInfomat_id\",
                eip.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                mso.MedSpecOms_id as \"MedSpecOms_id\",
                eip.ElectronicInfomatProfile_Position as \"ElectronicInfomatProfile_Position\"
			from
				v_ElectronicInfomatProfile eip
				left join v_MedSpecOms mso on mso.MedSpecOms_id = eip.MedSpecOms_id
			where
				eip.ElectronicInfomatProfile_id = :ElectronicInfomatProfile_id
		";

        return $this->queryResult($query, $data);
    }

    /**
     * Сохраняет профиль для инфомата
     */
    function saveElectronicInfomatProfile($data) {

        $procedure = empty($data['ElectronicInfomatProfile_id'])
            ? 'p_ElectronicInfomatProfile_ins'
            : 'p_ElectronicInfomatProfile_upd';

        $query = "
        SELECT
            ElectronicInfomatProfile_id as \"ElectronicInfomatProfile_id\",
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\"
        FROM {$procedure}
        (
            ElectronicInfomatProfile_id => :ElectronicInfomatProfile_id,
            LpuSectionProfile_id => :LpuSectionProfile_id,
            ElectronicInfomat_id => :ElectronicInfomat_id,
            ElectronicInfomatProfile_Position => :ElectronicInfomatProfile_Position,
            pmUser_id => :pmUser_id,
            MedSpecOms_id => :MedSpecOms_id
        )
        ";

        $resp = $this->queryResult($query, $data);
        return $resp;
    }

    /**
     * Сохраняет инфомат
     */
    function save($data) {

        $data['ElectronicInfomat_isPrintOut'] = (($data['ElectronicInfomat_isPrintOut']) ? 2 : 1);
        $data['ElectronicInfomat_IsAllSpec'] = (($data['ElectronicInfomat_IsAllSpec']) ? 2 : 1);

        $procedure = empty($data['ElectronicInfomat_id'])
            ? 'p_ElectronicInfomat_ins'
            : 'p_ElectronicInfomat_upd';

        $query = "
        SELECT
            ElectronicInfomat_id as \"ElectronicInfomat_id\",
            error_code as \"Error_Code\",
            error_message as \"Error_Msg\"
        FROM {$procedure}
        (
            ElectronicInfomat_id => :ElectronicInfomat_id,
            Lpu_id => :Lpu_id,
            LpuBuilding_id => :LpuBuilding_id,
            ElectronicInfomat_Code => :ElectronicInfomat_Code,
            ElectronicInfomat_Name => :ElectronicInfomat_Name,
            ElectronicInfomat_StartPage => :ElectronicInfomat_StartPage,
            ElectronicInfomat_begDate => :ElectronicInfomat_begDate,
            ElectronicInfomat_isPrintOut => :ElectronicInfomat_isPrintOut,
            ElectronicInfomat_IsAllSpec => :ElectronicInfomat_IsAllSpec,
            ElectronicInfomat_IsPrintService => :ElectronicInfomat_IsPrintService,
            pmUser_id => :pmUser_id
        )
        ";

        $resp = $this->queryResult($query, $data);
        return $resp;
    }

    /**
     * Сохраняет связь инфомат-очередь для всех записей
     */
    function updateElectronicInfomatLink($data) {

        $result = array();
        $error = array();

        if (!empty($data['jsonData']) && $data['ElectronicInfomat_id'] > 0) {
            ConvertFromWin1251ToUTF8($data['jsonData']);
            $records = (array) json_decode($data['jsonData']);

            // сохраняем\удаляем все записи из связанного грида по очереди
            foreach($records as $record) {

                if (count($error) == 0) {

                    switch($record->state) {

                        case 'add':
                        case 'edit':

                            $response = $this->saveObject('ElectronicInfomatLink', array(
                                'ElectronicInfomatLink_id' => $record->state == 'add' ? null : $record->ElectronicInfomatLink_id,
                                'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
                                'ElectronicQueueInfo_id' => $record->ElectronicQueueInfo_id,
                                'pmUser_id' => $data['pmUser_id']
                            ));
                            break;

                        case 'delete':

                            $response = $this->deleteElectronicInfomatLink(array(
                                'ElectronicInfomatLink_id' => $record->ElectronicInfomatLink_id
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
     * Сохраняет настройки кнопок стартового экрана для инфомата
     */
    public function updateElectronicInfomatButtonLink($data) {
        $result = array();

        try {
            if ( !empty($data['jsonData']) && !empty($data['ElectronicInfomat_id']) ) {
                $records = json_decode($data['jsonData'], true);

                $ElectronicInfomatButtonLink = array();

                // Получаем список существующих записей
                $response = $this->queryResult("
					select ElectronicInfomatButtonLink_id as \"ElectronicInfomatButtonLink_id\", ElectronicInfomatButton_id as \"ElectronicInfomatButton_id\"
					from v_ElectronicInfomatButtonLink
					where ElectronicInfomat_id = :ElectronicInfomat_id
				",
                    array('ElectronicInfomat_id' => $data['ElectronicInfomat_id']));

                if ( $response !== false && is_array($response) ) {
                    foreach ( $response as $rec ) {
                        $ElectronicInfomatButtonLink[$rec['ElectronicInfomatButton_id']] = $rec['ElectronicInfomatButtonLink_id'];
                    }
                }

                // Сохраняем/удаляем все записи из связанного грида по очереди
                foreach ( $records as $key => $value ) {
                    $key = str_replace('ElectronicInfomatButton', '', $key);

                    if ( $value == 1 && array_key_exists($key, $ElectronicInfomatButtonLink) ) {
                        // Удаляем
                        $response = $this->getFirstRowFromQuery("
							SELECT
							error_code as \"Error_Code\",
                            error_message as \"Error_Msg\"
                            FROM
                            dbo.p_ElectronicInfomatButtonLink_del(
                                ElectronicInfomatButtonLink_id => :ElectronicInfomatButtonLink_id
                            )
						", array(
                            'ElectronicInfomatButtonLink_id' => $ElectronicInfomatButtonLink[$key]
                        ));
                    }
                    else if ( $value == 2 && !array_key_exists($key, $ElectronicInfomatButtonLink) ) {
                        // Добавляем
                        $response = $this->getFirstRowFromQuery("							
							SELECT
							ElectronicInfomatButtonLink_id as \"ElectronicInfomatButtonLink_id\",
							error_code as \"Error_Code\",
                            error_message as \"Error_Msg\"
                            FROM
                            dbo.p_ElectronicInfomatButtonLink_ins(
                                ElectronicInfomatButtonLink_id => null,
								ElectronicInfomat_id => :ElectronicInfomat_id,
								ElectronicInfomatButton_id => :ElectronicInfomatButton_id,
								pmUser_id => :pmUser_id
                            )
						", array(
                            'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
                            'ElectronicInfomatButton_id' => $key,
                            'pmUser_id' => $data['pmUser_id'],
                        ));
                    }
                    else {
                        continue;
                    }

                    if ( $response === false || !is_array($response) || count($response) == 0 ) {
                        throw new Exception($response['Error_Msg']);
                    }
                    else if ( !empty($response['Error_Msg']) ) {
                        throw new Exception($response['Error_Msg']);
                    }
                }
            }

            $result['success'] = true;
        }
        catch ( Exception $e ) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return array($result);
    }

    /**
     * Удаление связи инфомат-очередь
     */
    function deleteElectronicInfomatLink($data) {

        $result = array();
        $error = array();

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        dbo.p_ElectronicInfomatLink_del(
          ElectronicInfomatLink_id => :ElectronicInfomatLink_id
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
     * Возвращает список инофматов для комбо
     */
    function loadElectronicInfomatCombo($data) {

        $params['Lpu_id'] = $data['Lpu_id'];

        $query = "
			select
				ei.ElectronicInfomat_id as \"ElectronicInfomat_id\"
				,ei.ElectronicInfomat_Name as \"ElectronicInfomat_Name\"
				,COALESCE(lb.LpuBuilding_Name, 'Не указано') as \"LpuBuilding_Name\"
				,case when kls.KLStreet_Name is not null
				    then kls.KLStreet_Name || ', ' || a.Address_House
				    else 'Не указано'
                end as \"LpuBuilding_Address\"
			from
				v_ElectronicInfomat ei
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ei.LpuBuilding_id
				left join v_Address a on a.Address_id = lb.Address_id
				left join KLStreet as kls on kls.KLStreet_id = a.KLStreet_id
			where ei.Lpu_id = :Lpu_id
            order by ei.ElectronicInfomat_id desc
		";

        $resp = $this->queryResult($query, $params);
        return $resp;
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
				EQI.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,EQI.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\"
				,EQI.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
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
     * Возвращает список всех связанных c инфоматами ЛПУ
     */
    function loadAllRelatedLpu() {

        $query = "
			select distinct
				lpu.Lpu_id as \"Lpu_id\"
				,lpu.Lpu_Name as \"Lpu_Name\"
				,lpu.Lpu_Nick as \"Lpu_Nick\"
			from
				v_ElectronicInfomat ei
				inner join v_Lpu lpu on lpu.Lpu_id = ei.Lpu_id
		";

        $resp = $this->queryResult($query);
        return $resp;
    }

}