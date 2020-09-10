<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * PersonSnilsQueue_model - модель для работы с очередью на проверку СНИЛС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
class PersonSnilsQueue_model extends SwPgModel
{
    /**
     * Удаление из очереди
     *
     * @param array $data
     * @return array
     */
    public function deletePersonSnilsQueue(array $data): array
    {

        $query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonSnilsQueue_del
			(
				PersonSnilsQueue_id := :PersonSnilsQueue_id
			)
		";

        return $this->queryResult($query, [
            'PersonSnilsQueue_id' => $data['PersonSnilsQueue_id'],
            'pmUser_id' => $data['pmUser_id']
        ]);
    }

    /**
     * Сохранение очереди
     *
     * @param array $data
     * @return array
     */
    function savePersonSnilsQueue(array $data): array
    {
        $query = "
			select
			    PersonSnilsQueue_id as \"PersonSnilsQueue_id\",
			    Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PersonSnilsQueue_ins
			(
				Person_id := :Person_id,
				Person_SurName := :Person_SurName,
				Person_FirName := :Person_FirName,
				Person_SecName := :Person_SecName,
				Person_Sex := :Person_Sex,
				Person_BirthDay := :Person_BirthDay,
				Person_Snils := :Person_Snils,
				PersonSnilsResp_id := :PersonSnilsResp_id,
				pmUser_id := :pmUser_id
			)
		";

        return $this->queryResult($query, [
            'Person_id' => $data['Person_id'],
            'Person_SurName' => $data['Person_SurName'],
            'Person_FirName' => $data['Person_FirName'],
            'Person_SecName' => $data['Person_SecName'],
            'Person_Sex' => $data['Person_Sex'],
            'Person_BirthDay' => $data['Person_BirthDay'],
            'Person_Snils' => $data['Person_Snils'] ?? null,
            'PersonSnilsResp_id' => $data['PersonSnilsResp_id'] ?? null,
            'pmUser_id' => $data['pmUser_id']
        ]);
    }

    /**
     * Апдейт СНИЛС в очереди
     *
     * @param array $data
     * @return array
     */
    function updatePersonSnilsQueue(array $data): array
    {
        $filter = "";
        if ($data['Person_Snils'] === -1) {
            $filter .= "and Person_Snils is null";
        }
        $this->db->query("
			update
				PersonSnilsQueue
			set
				Person_Snils = :Person_Snils,
				PersonSnilsQueue_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				PersonSnilsQueue_id = :PersonSnilsQueue_id
				{$filter}
		", [
            'PersonSnilsQueue_id' => $data['PersonSnilsQueue_id'],
            'Person_Snils' => $data['Person_Snils'],
            'pmUser_id' => $data['pmUser_id']
        ]);

        return array('Error_Msg' => '');
    }

    /**
     * Обработка очереди
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    function runValidation(array $data): array
    {
        $url = $this->config->item('SnilsValidationServiceUrl');
        if (empty($url)) {
           throw new Exception('Не задан SnilsValidationServiceUrl');
        }

        $query = "
			select
				psq.PersonSnilsQueue_id as \"PersonSnilsQueue_id\",
				psq.Person_id as \"Person_id\",
				psq.Person_SurName as \"Person_SurName\",
				psq.Person_FirName as \"Person_FirName\",
				psq.Person_SecName as \"Person_SecName\",
				psq.Person_Sex as \"Person_Sex\",
				to_char(psq.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				ps.Person_Snils as \"Person_Snils\"
			from
				v_PersonSnilsQueue psq
				left join v_PersonState ps on ps.Person_id = psq.Person_id
			where
				psq.Person_Snils is null
			order by
				PersonSnilsQueue_insDT
		";
        $resp_psq = $this->queryResult($query);

        foreach ($resp_psq as $one_psq) {
            // проставляем Person_Snils = -1, это значит, что взяли в обработку
            // ставим статус "В процессе отправки".
            $this->updatePersonSnilsQueue([
                'PersonSnilsQueue_id' => $one_psq['PersonSnilsQueue_id'],
                'Person_Snils' => -1,
                'pmUser_id' => $data['pmUser_id']
            ]);

            $affected_rows = $this->db->affected_rows();
            if ($affected_rows < 1) {
                // пропускаем, если ничего не проапдейтили (значит уже обработан другим заданием)
                continue;
            }

            $POST = json_encode([
                'surname' => $one_psq['Person_SurName'],
                'firstname' => $one_psq['Person_FirName'],
                'patronymic' => $one_psq['Person_SecName'],
                'gender' => $one_psq['Person_Sex'] == 2 ? 'FEMALE' : 'MALE',
                'birthdate' => $one_psq['Person_BirthDay'],
            ]);

            // пытаемся отправить СНИЛС в сервис и получить ответ
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            if (!empty($port)) {
                curl_setopt($ch, CURLOPT_PORT, $port);
            }
            // curl_setopt($ch, CURLOPT_PROXY, "http://192.168.36.200:808");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Accept: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
            $response = curl_exec($ch);

            if ($response === false) {
                throw new Exception('Не удалось получить данные из сервиса валидации СНИЛС: ' . curl_error($ch));
            }

            $answer = json_decode($response, true);
            if (!empty($answer['errorText'])) {
                throw new Exception('Не удалось получить данные из сервиса валидации СНИЛС: ' . $answer['errorText']);
            }

            if (!array_key_exists('snils', $answer)) {
                throw new Exception('Не удалось получить данные из сервиса валидации СНИЛС: ' . $response);
            }

            $answer['snils'] = $answer['snils'] ?? '';
            $answer['snils'] = str_replace([' ', '-'], '', $answer['snils']);

            $this->updatePersonSnilsQueue([
                'PersonSnilsQueue_id' => $one_psq['PersonSnilsQueue_id'],
                'Person_Snils' => $answer['snils'],
                'pmUser_id' => $data['pmUser_id']
            ]);

            $query = "
                update
                    PersonState 
                set
                    PersonState_IsSnils = :PersonState_IsSnils
                where 
                    Person_id = :Person_id
            ";
            $this->db->query($query, [
                'Person_id' => $one_psq['Person_id'],
                'PersonState_IsSnils' => (!empty($answer['snils']) && $one_psq['Person_Snils'] == $answer['snils']) ? 2 : 1
            ]);
        }

        return ['Error_Msg' => ''];
    }
}
