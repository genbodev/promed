<?php
/**
 * EvnNotifyPalliat_model - модель для работы с извешениями по палиативной помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Kirill Sabirov
 * @version      12.2018
 */

require_once('EvnNotifyAbstract_model.php');

class EvnNotifyPalliat_model extends SwPgModel {
    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @param array $data
     * @return array
     */
    function checkAllowCreate($data) {
        $params = array(
            'Person_id' => $data['Person_id'],
        );

        $query = "			
			select sum(t.cnt) as cnt
			from (
				(select count(*) as cnt
				from v_PalliatNotify PN
				inner join v_EvnNotifyBase ENB on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
				where ENB.Person_id = :Person_id 
				and ENB.EvnNotifyBase_niDate is null
				limit 1)
				union all
				(select count(*) as cnt
				from v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
				where PR.Person_id = :Person_id
				and dbo.tzGetDate() between PR.PersonRegister_setDate and COALESCE(PR.PersonRegister_disDate, dbo.tzGetDate())
				and PRT.PersonRegisterType_SysNick ilike 'palliat'
				limit 1)
			) t
		";

        $count = $this->getFirstResultFromQuery($query, $params);
        if ($count === false) {
            return $this->createError('','Ошибка при проверке возможности создания извещения');
        }

        return array(array(
            'success' => true,
            'isAllowCreate' => $count == 0
        ));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function saveEvnNotifyBase($data) {
        $params = array(
            'EvnNotifyBase_id' => !empty($data['EvnNotifyBase_id'])?$data['EvnNotifyBase_id']:null,
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'EvnNotifyBase_setDate' => $data['EvnNotifyBase_setDate'],
            'MedPersonal_id' => $data['MedPersonal_id'],
            'Lpu_did' => $data['Lpu_did'],
            'pmUser_id' => $data['pmUser_id'],
        );

        if (empty($params['EvnNotifyBase_id'])) {
            $procedure = "p_EvnNotifyBase_ins";
        } else {
            $procedure = "p_EvnNotifyBase_upd";
        }

        $query = "
        SELECT
        EvnNotifyBase_id as \"EvnNotifyBase_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                EvnNotifyBase_id => :EvnNotifyBase_id,
				Server_id => :Server_id,
				PersonEvn_id => :PersonEvn_id,
				EvnNotifyBase_setDT => :EvnNotifyBase_setDate,
				MedPersonal_id => :MedPersonal_id,
				Lpu_id => :Lpu_did,
				pmUser_id => :pmUser_id
        )
        ";

        $response = $this->queryResult($query, $params);
        if (!is_array($response)) {
            return $this->createError('','Ошибка при сохранении базового извещения');
        }
        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function savePalliatNotify($data) {
        $params = array(
            'PalliatNotify_id' => !empty($data['PalliatNotify_id'])?$data['PalliatNotify_id']:null,
            'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
            'Diag_id' => $data['Diag_id'],
            'pmUser_id' => $data['pmUser_id'],
        );

        if (empty($params['PalliatNotify_id'])) {
            $procedure = "p_PalliatNotify_ins";
        } else {
            $procedure = "p_PalliatNotify_upd";
        }

        $query = "
        SELECT
        PalliatNotify_id as \"PalliatNotify_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                PalliatNotify_id => :PalliatNotify_id,
				EvnNotifyBase_id => :EvnNotifyBase_id,
				Diag_id => :Diag_id,
				pmUser_id => :pmUser_id
        )
        ";

        $response = $this->queryResult($query, $params);
        if (!is_array($response)) {
            return $this->createError('','Ошибка при сохранении извещения по палиативной помощи');
        }
        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    function doSave($data = array(), $isAllowTransaction = true) {
        $params = array(
            'PalliatNotify_id' => !empty($data['PalliatNotify_id'])?$data['PalliatNotify_id']:null,
            'EvnNotifyBase_id' => !empty($data['EvnNotifyBase_id'])?$data['EvnNotifyBase_id']:null,
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'Person_id' => $data['Person_id'],
            'EvnNotifyBase_setDate' => $data['EvnNotifyBase_setDate'],
            'Diag_id' => $data['Diag_id'],
            'MedPersonal_id' => $data['MedPersonal_id'],
            'Lpu_did' => $data['Lpu_did'],
            'pmUser_id' => $data['pmUser_id'],
        );

        if (empty($params['PalliatNotify_id'])) {
            $resp = $this->checkAllowCreate($params);
            if (!$this->isSuccessful($resp)) {
                return $resp;
            }
            if (!$resp[0]['isAllowCreate']) {
                return $this->createError('', 'Пациент уже состоит в регистре ПМП или у него есть необработанное извещение ПМП по выбранному диагнозу. Проверьте корректность введенных данных.');
            }
        }

        $response = array(
            'success' => true,
            'EvnNotifyBase_id' => null,
            'PalliatNotify_id' => null,
        );

        $this->beginTransaction();

        $resp = $this->saveEvnNotifyBase($data);
        if (!$this->isSuccessful($resp)) {
            $this->rollbackTransaction();
            return $resp;
        }
        $data['EvnNotifyBase_id'] = $resp[0]['EvnNotifyBase_id'];
        $response['EvnNotifyBase_id'] = $resp[0]['EvnNotifyBase_id'];

        $resp = $this->savePalliatNotify($data);
        if (!$this->isSuccessful($resp)) {
            $this->rollbackTransaction();
            return $resp;
        }
        $response['PalliatNotify_id'] = $resp[0]['PalliatNotify_id'];

        $this->commitTransaction();

        return array($response);
    }

    /**
     * @param array $data
     * @param bool $isAllowTransaction
     * @return array|false
     */
    function doDelete($data = array(), $isAllowTransaction = true) {
        $params = array(
            'PalliatNotify_id' => $data['PalliatNotify_id'],
            'EvnNotifyBase_id' => null,
            'pmUser_id' => $data['pmUser_id'],
        );

        $params['EvnNotifyBase_id'] = $this->getFirstResultFromQuery("
			select PN.EvnNotifyBase_id
			from v_PalliatNotify PN
			where PN.PalliatNotify_id = :PalliatNotify_id
			limit 1
		", $params);
        if (empty($params['EvnNotifyBase_id'])) {
            return $this->createError('','Ошибка при получении идентификатора базового извещения');
        }

        $this->beginTransaction();

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_PalliatNotify_del(
          PalliatNotify_id => :PalliatNotify_id
        )
        ";
        $resp = $this->queryResult($query, $params);
        if (!is_array($resp)) {
            $this->rollbackTransaction();
            return $this->createError('','Ошибка при удалении извещения по палиативной помощи');
        }
        if (!$this->isSuccessful($resp)) {
            $this->rollbackTransaction();
            return $resp;
        }

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_EvnNotifyBase_del(
            EvnNotifyBase_id => :EvnNotifyBase_id,
			pmUser_id => :pmUser_id
        )
        ";

        $resp = $this->queryResult($query, $params);
        if (!is_array($resp)) {
            $this->rollbackTransaction();
            return $this->createError('','Ошибка при удалении базового извещения');
        }
        if (!$this->isSuccessful($resp)) {
            $this->rollbackTransaction();
            return $resp;
        }

        $this->commitTransaction();

        return array(array(
            'success' => true
        ));
    }

    /**
     * @param array $data
     * @return array|false
     */
    function loadEditForm($data) {
        $params = array(
            'PalliatNotify_id' => $data['PalliatNotify_id'],
        );

        $query = "
			select
				PN.PalliatNotify_id as \"PalliatNotify_id\",
				ENB.EvnNotifyBase_id as \"EvnNotifyBase_id\",
				ENB.Server_id as \"Server_id\",
				ENB.PersonEvn_id as \"PersonEvn_id\",
				ENB.Person_id as \"Person_id\",
				to_char(ENB.EvnNotifyBase_setDate, 'DD.MM.YYYY') as \"EvnNotifyBase_setDate\",
				PN.Diag_id as \"Diag_id\",
				ENB.MedPersonal_id as \"MedPersonal_id\",
				ENB.Lpu_id as \"Lpu_did\"
			from
				v_PalliatNotify PN
				inner join v_EvnNotifyBase ENB on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
			where
				PN.PalliatNotify_id = :PalliatNotify_id
				limit 1
		";

        return $this->queryResult($query, $params);
    }

    /**
     * @param array $data
     * @return array|false
     */
    function getViewProperties($data) {
        $params = array(
            'Person_id' => $data['Person_id']
        );

        $query = "			
			select
			case
				when (
					exists(
						select * from v_PalliatQuestion
						where Person_id = :Person_id and PalliatQuestion_CountYes >= 3
					) or exists(
						select * from v_PalliatNotify PN
						inner join v_EvnNotifyBase ENB on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
						where ENB.Person_id = :Person_id
					)
				) then 1 else 0
			end as \"showPalliatNotifyList\",
			/*case
				when not exists (
					select * from v_PersonRegister PR with(nolock)
					inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					where PR.Person_id = :Person_id and PRT.PersonRegisterType_SysNick like 'palliat'
					and @date between PR.PersonRegister_setDate and isnull(PR.PersonRegister_disDate, @date)
				) and not exists (
					select * from v_PalliatNotify PN with(nolock)
					inner join v_EvnNotifyBase ENB with(nolock) on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
					where ENB.Person_id = :Person_id and ENB.EvnNotifyBase_niDate is not null
				) then 1 else 0
			end*/1 as \"allowAddPalliatNotifyButton\"
			limit 1
		";

        return $this->getFirstRowFromQuery($query, $params);
    }

    /**
     * @param $data
     * @return array|false
     */
    function getViewListData($data) {
        $params = array(
            'Person_id' => $data['Person_id']
        );

        $query = "
			select
				PN.PalliatNotify_id as \"PalliatNotify_id\",
				to_char(ENB.EvnNotifyBase_setDate, 'DD.MM.YYYY') as \"EvnNotifyBase_setDate\",
				D.Diag_FullName as \"Diag_FullName\",
				L.Lpu_Nick as \"Lpu_Nick\",
				MP.Person_Fio as \"MedPersonal_Fio\",
				to_char(COALESCE(PR.PersonRegister_setDate, ENB.EvnNotifyBase_niDate), 'DD.MM.YYYY') as \"procDate\",
				case 
					when PR.PersonRegister_id is not null then 'Да'
					when ENB.EvnNotifyBase_niDate is not null then 'Нет'
				end as \"isInRegister\",
				'none' as \"displayEditBtn\",
				'none' as \"displayDelBtn\"
			from
				v_PalliatNotify PN
				inner join v_EvnNotifyBase ENB on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
				left join v_Diag D on D.Diag_id = PN.Diag_id
				left join v_Lpu L on L.Lpu_id = ENB.Lpu_id
				LEFT JOIN LATERAL (
					select MP.Person_Fio
					from v_MedPersonal MP
					where MP.MedPersonal_id = ENB.MedPersonal_id
					limit 1
				) MP on true
				LEFT JOIN LATERAL (
					select PR.*
					from v_PersonRegister PR
					inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					inner join v_MorbusPalliat MO on MO.Morbus_id = PR.Morbus_id
					where PR.EvnNotifyBase_id = ENB.EvnNotifyBase_id and PRT.PersonRegisterType_SysNick ilike 'palliat'
					limit 1
				) PR on true
			where
				ENB.Person_id = :Person_id
		";

        $list = $this->queryResult($query, $params);
        if (!is_array($list)) {
            return false;
        }

        $properties = $this->EvnNotifyPalliat_model->getViewProperties($data);
        if (!is_array($properties)) {
            return false;
        }

        $list[] = array_merge($properties, array(
            'PalliatNotify_id' => -1,
        ));

        return $list;
    }
}