<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * EvnDiagDopDisp_model - модель для работы с записями в 'Ранее известные имеющиеся заболевания' / 'Впервые выявленные заболевания'
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      02.07.2013
 */
class EvnDiagDopDisp_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";

    /**
     *    Загрузка грида
     * @param $data
     * @return bool|array
     */
    function loadEvnDiagDopDispGrid($data)
    {
        $filter = " and EDDD.EvnDiagDopDisp_pid = :EvnPLDisp_id and EDDD.DeseaseDispType_id = :DeseaseDispType_id";

        if (!empty($data['EvnDiagDopDisp_id'])) {
            $filter = " and EDDD.EvnDiagDopDisp_id = :EvnDiagDopDisp_id";
        }

        $query = "
			select 
				EDDD.EvnDiagDopDisp_pid as \"EvnPLDisp_id\",
				EDDD.EvnDiagDopDisp_pid as \"EvnDiagDopDisp_pid\",
				to_char(EDDD.EvnDiagDopDisp_setDate, {$this->dateTimeForm104}) as \"EvnDiagDopDisp_setDate\",
				EDDD.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				EDDD.PersonEvn_id as \"PersonEvn_id\",
				EDDD.Server_id as \"Server_id\",
				EDDD.DiagSetClass_id as \"DiagSetClass_id\",
				EDDD.EvnDiagDopDisp_IsSystemDataAdd as \"EvnDiagDopDisp_IsSystemDataAdd\",
				DSC.DiagSetClass_Name as \"DiagSetClass_Name\",
				EDDD.Diag_id as \"Diag_id\",
				D.Diag_Name as \"Diag_Name\",
				EDDD.Lpu_id as \"Lpu_id\"
			from
				v_EvnDiagDopDisp EDDD
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_Diag D on D.Diag_id = EDDD.Diag_id
			where
				(1=1)
				{$filter}
			order by
				EDDD.EvnDiagDopDisp_id
		";
        $result = $this->db->query($query, $data);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     *    Загрузка грида
     * @param $data
     * @return bool|array
     */
    public function loadEvnDiagDopDispSoputGrid($data)
    {
        $query = "
			select
				EDDD.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				EDDD.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EDDD.DeseaseDispType_id as \"DeseaseDispType_id\",
				DDT.DeseaseDispType_Name as \"DeseaseDispType_Name\",
				1 as \"Record_Status\"
			from
				v_EvnDiagDopDisp EDDD
				left join v_DeseaseDispType DDT on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
				left join v_Diag D on D.Diag_id = EDDD.Diag_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
            and
                EDDD.DiagSetClass_id = 3
			order by
				EDDD.EvnDiagDopDisp_id
		";

        $result = $this->db->query($query, $data);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *    Загрузка грида
     * @param $data
     * @return bool|array
     */
    public function loadEvnDiagDopDispAndRecomendationGrid($data)
    {
        $filter = " and EDDD.EvnDiagDopDisp_pid = :EvnPLDisp_id";

        if (!empty($data['EvnDiagDopDisp_id'])) {
            $filter = " and EDDD.EvnDiagDopDisp_id = :EvnDiagDopDisp_id";
        }

        $query = "
			select
				EDDD.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				EDDD.PersonEvn_id as \"PersonEvn_id\",
				EDDD.Server_id as \"Server_id\",
				EDDD.EvnDiagDopDisp_pid as \"EvnDiagDopDisp_pid\",
				EDDD.HTMRecomType_id as \"HTMRecomType_id\",
				EDDD.EvnDiagDopDisp_IsVMP as \"EvnDiagDopDisp_IsVMP\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Code || '. ' || D.Diag_Name as \"Diag_Name\",
				EDDD.DispSurveilType_id as \"DispSurveilType_id\",
				EDDD.DeseaseDispType_id as \"DeseaseDispType_id\",
				DST.DispSurveilType_Name as \"DispSurveilType_Name\",
				DDT.YesNo_Name as \"DeseaseDispType_Name\",
				coalesce (MC1.ConditMedCareType_nid, 1) as \"ConditMedCareType1_nid\",
				MC1.PlaceMedCareType_nid as \"PlaceMedCareType1_nid\",
				MC1.ConditMedCareType_id as \"ConditMedCareType1_id\",
				MC1.PlaceMedCareType_id as \"PlaceMedCareType1_id\",
				MC1.LackMedCareType_id as \"LackMedCareType1_id\",
				coalesce (MC2.ConditMedCareType_nid, 1) as \"ConditMedCareType2_nid\",
				MC2.PlaceMedCareType_nid as \"PlaceMedCareType2_nid\",
				MC2.ConditMedCareType_id as \"ConditMedCareType2_id\",
				MC2.PlaceMedCareType_id as \"PlaceMedCareType2_id\",
				MC2.LackMedCareType_id as \"LackMedCareType2_id\",
				coalesce (MC3.ConditMedCareType_nid, 1) as \"ConditMedCareType3_nid\",
				MC3.PlaceMedCareType_nid as \"PlaceMedCareType3_nid\",
				MC3.ConditMedCareType_id as \"ConditMedCareType3_id\",
				MC3.PlaceMedCareType_id as \"PlaceMedCareType3_id\",
				MC3.LackMedCareType_id as \"LackMedCareType3_id\"
			from
			    v_EvnDiagDopDisp EDDD
				left join v_Diag D on D.Diag_id = EDDD.Diag_id
				left join v_DispSurveilType DST on DST.DispSurveilType_id = EDDD.DispSurveilType_id
				left join v_YesNo DDT on DDT.YesNo_id = EDDD.DeseaseDispType_id
				left join lateral (
					select * from v_MedCare MC where MC.MedCareType_id = 1 and MC.EvnDiagDopDisp_id = EDDD.EvnDiagDopDisp_id limit 1
				) MC1 on true
				-- лечение
				left join lateral (
					select * from v_MedCare MC where MC.MedCareType_id = 2 and MC.EvnDiagDopDisp_id = EDDD.EvnDiagDopDisp_id limit 1
				) MC2 on true
				-- медицинская реабилитация / санаторно-курортное лечение
				left join lateral (
					select * from v_MedCare MC where MC.MedCareType_id = 3 and MC.EvnDiagDopDisp_id = EDDD.EvnDiagDopDisp_id limit 1
				) MC3 on true
			where
				(1=1)
				{$filter}
            order by
				EDDD.EvnDiagDopDisp_id
		";
        $result = $this->db->query($query, $data);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     *    Проверка существования
     */
    function checkEvnDiagDopDispExists($data)
    {
        $query = "
			select
				EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\"
			from
				v_EvnDiagDopDisp
			where
				EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
            and
                Diag_id = :Diag_id
            and
                DeseaseDispType_id = :DeseaseDispType_id
            and
                EvnDiagDopDisp_id <> coalesce(:EvnDiagDopDisp_id::bigint, 0)
            limit 1
		";

        $result = $this->db->query($query, $data);

        if (is_object($result)) {
            $resp = $result->result('array');
            if (count($resp) > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверка существования
     *
     * @param $data
     * @return bool|array
     */
    public function loadEvnDiagDopDispEditForm($data)
    {
        $query = "
			select
				EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				EvnDiagDopDisp_pid as \"EvnDiagDopDisp_pid\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				DiagSetClass_id as \"DiagSetClass_id\",
				Diag_id as \"Diag_id\",
				DeseaseDispType_id as \"DeseaseDispType_id\"
			from
				v_EvnDiagDopDisp
			where
				EvnDiagDopDisp_id = :EvnDiagDopDisp_id
		";

        $result = $this->db->query($query, $data);

        if (!is_object($result))
            return true;


        return $result->result('array');
    }

    /**
     *	Проверка наличия карты диспансеризации при определенной группе диагноза
     */
    function checkDiagDisp($data) {
        if (empty($data['Person_id']) && !empty($data['PersonEvn_id'])) {
            $query_person = "
			select
				Person_id as \"Person_id\"
			from
				v_PersonEvn
			where
				PersonEvn_id = :PersonEvn_id
				limit 1
		";
            $response_person = $this->db->query($query_person, $data);
            if (is_object($response_person)) {
                $response_person = $response_person->result('array');
                $data['Person_id'] = $response_person[0]['Person_id'];
            }
        }
        if (empty($data['Person_id'])) {
            throw new Exception('Не заданы идентификатор пациента и идентификатор события');
        }

        // ищем прикрепление
        $query_attach = "
				select 
					PersonCard_id as \"PersonCard_id\"
				from
					v_PersonCard
				where
					Lpu_id = :Lpu_id and Person_id = :Person_id
					limit 1
			";

        $response_attach = $this->db->query($query_attach, $data);
        if (is_object($response_attach) && !empty($response_attach->result('array'))) {
            // если прикрепление есть, проверяем диагноз
            $query_diag = "
					select
						DispSickDiag_id as \"DispSickDiag_id\"
					from
						v_DispSickDiag
					where
						Diag_id = :Diag_id
						limit 1
				";

            $response_diag = $this->db->query($query_diag, $data);

            if (is_object($response_diag) && !empty($response_diag->result('array'))) {
                // если диагноз входит в список, проверяем карту диспансерного наблюдения
                $query_disp_card = "						
						select
							 PersonDisp_id as \"PersonDisp_id\"
						from
							v_PersonDisp
						where
							Person_id = :Person_id
							and Lpu_id = :Lpu_id
							and COALESCE(:Date, dbo.tzGetDate()) between PersonDisp_begDate and COALESCE(PersonDisp_endDate, dbo.tzGetDate())
							and Diag_id = :Diag_id
							limit 1
					";

                $response_disp_card = $this->db->query($query_disp_card, $data);

                if (is_object($response_disp_card) && empty($response_disp_card->result('array'))) {
                    return array('success' => true, 'result' => false, 'Person_id' => $data['Person_id']);
                }
            }
        }

        return array('success' => true, 'result' => true, 'Person_id' => $data['Person_id']);
    }

    /**
     * Удаление
     * @param $data
     * @return bool|array
     */
    function delEvnDiagDopDisp($data)
    {
        $sql = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_EvnDiagDopDisp_del
			(
				EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
				pmUser_id := :pmUser_id
			)
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Сохранение
     *
     * @param $data
     * @return bool|array
     */
    public function saveEvnDiagDopDisp($data)
    {
        $proc = "p_EvnDiagDopDisp_ins";
        if (!empty($data['EvnDiagDopDisp_id']) && $data['EvnDiagDopDisp_id'] > 0) {
            $proc = "p_EvnDiagDopDisp_upd";
        }

        if (!isset($data['EvnDiagDopDisp_IsSystemDataAdd'])) {
            $data['EvnDiagDopDisp_IsSystemDataAdd'] = 1;
        }

        $sql = "
			select 
			    EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$proc}
			(
				EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
				EvnDiagDopDisp_setDT := :EvnDiagDopDisp_setDate,
				EvnDiagDopDisp_pid := :EvnDiagDopDisp_pid,
				Diag_id := :Diag_id,
				DiagSetClass_id := :DiagSetClass_id,
				DeseaseDispType_id := :DeseaseDispType_id,
				EvnDiagDopDisp_IsSystemDataAdd := :EvnDiagDopDisp_IsSystemDataAdd,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				pmUser_id := :pmUser_id
            )
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Получение идентификатора медицинской помощи
     *
     * @param $EvnDiagDopDisp_id
     * @param $MedCareType_id
     * @return int|null
     */
    public function getMedCareForEvnDiagDopDisp($EvnDiagDopDisp_id, $MedCareType_id)
    {
        $query = "
			select
				MedCare_id as \"MedCare_id\"
			from
				v_MedCare
			where
				EvnDiagDopDisp_id = :EvnDiagDopDisp_id
            and
                MedCareType_id = :MedCareType_id
            limit 1
		";

        $result = $this->db->query($query, array(
            'EvnDiagDopDisp_id' => $EvnDiagDopDisp_id,
            'MedCareType_id' => $MedCareType_id
        ));

        if (is_object($result)) {
            $resp = $result->result('array');
            if (count($resp) > 0) {
                return $resp[0]['MedCare_id'];
            }
        }

        return null;
    }

    /**
     * Сохранение оказания медицинской помощи
     * @param $data
     * @return bool|array
     */
    public function saveMedCare($data)
    {
        if (!empty($data['MedCare_id']) && $data['MedCare_id'] > 0) {
            $proc = 'p_MedCare_upd';
        } else {
            $proc = 'p_MedCare_ins';
            $data['MedCare_id'] = null;
        }

        $query = "
			select
			    MedCare_id as \"MedCare_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$proc}
			(
				MedCare_id := :MedCare_id, 
				EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
				LackMedCareType_id := :LackMedCareType_id,
				ConditMedCareType_nid := :ConditMedCareType_nid,
				PlaceMedCareType_nid := :PlaceMedCareType_nid,
				ConditMedCareType_id := :ConditMedCareType_id,
				PlaceMedCareType_id := :PlaceMedCareType_id,
				MedCareType_id := :MedCareType_id,
				pmUser_id := :pmUser_id
			)
		";

        $result = $this->db->query($query, $data);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * Сохранение
     * @param $data
     * @return bool|array
     */
    public function saveEvnDiagDopDispAndRecomendation($data)
    {
        $proc = "p_EvnDiagDopDisp_ins";
        if (!empty($data['EvnDiagDopDisp_id']) && $data['EvnDiagDopDisp_id'] > 0) {
            $proc = "p_EvnDiagDopDisp_upd";
        }

        $sql = "
			select 
			    EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$proc}
			(
				EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
				EvnDiagDopDisp_setDT := dbo.tzGetDate(),
				EvnDiagDopDisp_pid := :EvnDiagDopDisp_pid,
				Diag_id := :Diag_id,
				DiagSetClass_id := 1,
				DeseaseDispType_id := :DeseaseDispType_id,
				DispSurveilType_id := :DispSurveilType_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				HTMRecomType_id := :HTMRecomType_id,
				pmUser_id := :pmUser_id
			)
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        $resp = $res->result('array');
        if (!empty($resp[0]['EvnDiagDopDisp_id'])) {
            $MedCare_id = $this->getMedCareForEvnDiagDopDisp($resp[0]['EvnDiagDopDisp_id'], 1);
            $this->saveMedCare([
                'MedCare_id' => $MedCare_id,
                'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
                'ConditMedCareType_nid' => empty($data['ConditMedCareType1_nid']) ? null : $data['ConditMedCareType1_nid'],
                'PlaceMedCareType_nid' => empty($data['PlaceMedCareType1_nid']) ? null : $data['PlaceMedCareType1_nid'],
                'ConditMedCareType_id' => empty($data['ConditMedCareType1_id']) ? null : $data['ConditMedCareType1_id'],
                'PlaceMedCareType_id' => empty($data['PlaceMedCareType1_id']) ? null : $data['PlaceMedCareType1_id'],
                'LackMedCareType_id' => empty($data['LackMedCareType1_id']) ? null : $data['LackMedCareType1_id'],
                'MedCareType_id' => 1,
                'pmUser_id' => $data['pmUser_id']
            ]);

            // получаем MedCare_id для MedCareType_id = 2
            $MedCare_id = $this->getMedCareForEvnDiagDopDisp($resp[0]['EvnDiagDopDisp_id'], 2);
            $this->saveMedCare([
                'MedCare_id' => $MedCare_id,
                'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
                'ConditMedCareType_nid' => empty($data['ConditMedCareType2_nid']) ? null : $data['ConditMedCareType2_nid'],
                'PlaceMedCareType_nid' => empty($data['PlaceMedCareType2_nid']) ? null : $data['PlaceMedCareType2_nid'],
                'ConditMedCareType_id' => empty($data['ConditMedCareType2_id']) ? null : $data['ConditMedCareType2_id'],
                'PlaceMedCareType_id' => empty($data['PlaceMedCareType2_id']) ? null : $data['PlaceMedCareType2_id'],
                'LackMedCareType_id' => empty($data['LackMedCareType2_id']) ? null : $data['LackMedCareType2_id'],
                'MedCareType_id' => 2,
                'pmUser_id' => $data['pmUser_id']
            ]);

            // получаем MedCare_id для MedCareType_id = 3
            $MedCare_id = $this->getMedCareForEvnDiagDopDisp($resp[0]['EvnDiagDopDisp_id'], 3);
            $this->saveMedCare([
                'MedCare_id' => $MedCare_id,
                'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
                'ConditMedCareType_nid' => empty($data['ConditMedCareType3_nid']) ? null : $data['ConditMedCareType3_nid'],
                'PlaceMedCareType_nid' => empty($data['PlaceMedCareType3_nid']) ? null : $data['PlaceMedCareType3_nid'],
                'ConditMedCareType_id' => empty($data['ConditMedCareType3_id']) ? null : $data['ConditMedCareType3_id'],
                'PlaceMedCareType_id' => empty($data['PlaceMedCareType3_id']) ? null : $data['PlaceMedCareType3_id'],
                'LackMedCareType_id' => empty($data['LackMedCareType3_id']) ? null : $data['LackMedCareType3_id'],
                'MedCareType_id' => 3,
                'pmUser_id' => $data['pmUser_id']
            ]);
        }

        return $resp;
    }

    /**
     * Добавление впервые выявленного
     * @param $data
     * @param $diag_id
     * @return bool|void
     */
    public function addEvnDiagDopDispFirst($data, $diag_id)
    {
        return $this->doSaveEvnDiagDopDisp($data, $diag_id, 2);
    }

    /**
     * Добавление выявленного до
     * @param $data
     * @param $diag_id
     * @return array|bool
     */
    public function addEvnDiagDopDispBefore($data, $diag_id)
    {
        return $this->doSaveEvnDiagDopDisp($data, $diag_id, 1);
    }

    protected function doSaveEvnDiagDopDisp($data, $diag_id, $type_id)
    {
        // проверяем есть ли такой диагноз уже, если нет, то добавляем
        if (empty($diag_id))
            return false;

        $data['Diag_id'] = $diag_id;
        $query = "
            select
                EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\"
            from
                v_EvnDiagDopDisp
            where
                EvnDiagDopDisp_pid = :EvnPLDisp_id
            and
                Diag_id = :Diag_id
            and
                DeseaseDispType_id = :Type_id
            limit 1
        ";

        $result = $this->db->query($query, array_merge($data, ['Type_id' => $type_id]));

        if (is_object($result)) {
            $resp = $result->result('array');
            if (count($resp) > 0) {
                return false;
            }
        }

        $params = [
            'EvnDiagDopDisp_id' => null,
            'EvnDiagDopDisp_setDate' => $data['EvnDiagDopDisp_setDate'],
            'EvnDiagDopDisp_pid' => $data['EvnPLDisp_id'],
            'Diag_id' => $data['Diag_id'],
            'DiagSetClass_id' => 1,
            'DeseaseDispType_id' => $type_id,
            'EvnDiagDopDisp_IsSystemDataAdd' => 2,
            'Lpu_id' => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'pmUser_id' => $data['pmUser_id']
        ];
        return $this->saveEvnDiagDopDisp($params);
    }

    /**
     * Удаление впервые выявленного
     * @param $data
     * @param $diag_id
     * @return array|bool
     */
    function delEvnDiagDopDispFirst($data, $diag_id)
    {
        return $this->doDelEvnDiagDopDisp($data, $diag_id, 2);
    }

    /**
     * Удаление выявленного до
     * @param $data
     * @param $diag_id
     * @return array|bool
     */
    function delEvnDiagDopDispBefore($data, $diag_id)
    {
       return $this->doDelEvnDiagDopDisp($data, $diag_id, 1);
    }

    /**
     * @param $data
     * @param $diag_id
     * @param $type_id
     * @return array|bool
     */
    protected function doDelEvnDiagDopDisp($data, $diag_id, $type_id)
    {
        // проверяем есть ли такой диагноз уже, если есть, то удаляем
        if (empty($diag_id))
            return false;

        $data['Diag_id'] = $diag_id;
        $query = "
            select
                EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\"
            from
                v_EvnDiagDopDisp
            where
                EvnDiagDopDisp_pid = :EvnPLDisp_id
            and
                Diag_id = :Diag_id
            and
                DeseaseDispType_id = :Type_id
            limit 1
        ";

        $result = $this->db->query($query, array_merge($data, ['Type_id' => $type_id]));

        if (is_object($result)) {
            $resp = $result->result('array');
            if (count($resp) > 0 && !empty($resp[0]['EvnDiagDopDisp_id'])) {
                $params = [
                    'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
                    'pmUser_id' => $data['pmUser_id']
                ];
                return $this->delEvnDiagDopDisp($params);
            }
        }

        return false;
    }

    /**
     * Получение данных об установленных диагнозах во время диспанцеризации/мед.осмотра
     * взрослого населения для панели просмотра ЭМК
     * @param $data
     * @return array|bool
     */
    public function getEvnDiagDopDispViewData($data)
    {
        if (empty($data['EvnDiagDopDisp_pid'])) {
            return [];
        }

        $filter = "";
        $queryParams = [
            'EvnDiagDopDisp_pid' => $data['EvnDiagDopDisp_pid']
        ];

        if (!empty($data['DeseaseDispType_id'])) {
            $filter .= " and EDDD.DeseaseDispType_id = :DeseaseDispType_id";
            $queryParams['DeseaseDispType_id'] = $data['DeseaseDispType_id'];
        }

        $query = "
			select
				EDDD.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				EDDD.EvnDiagDopDisp_pid as \"EvnDiagDopDisp_pid\",
				to_char(EDDD.EvnDiagDopDisp_setDate, {$this->dateTimeForm104}) as \"EvnDiagDopDisp_setDate\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				DSC.DiagSetClass_Name as \"DiagSetClass_Name\",
				DDT.DeseaseDispType_Name as \"DeseaseDispType_Name\",
				DST.DispSurveilType_Name as \"DispSurveilType_Name\"
			from
				v_EvnDiagDopDisp EDDD
				inner join v_Diag D on D.Diag_id = EDDD.Diag_id
				left join v_DeseaseDispType DDT on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
				left join v_DispSurveilType DST on DST.DispSurveilType_id = EDDD.DispSurveilType_id
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
				{$filter}
		";

        $result = $this->db->query($query, $queryParams);

        if (!is_object($result))
            return false;

        return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
    }
}