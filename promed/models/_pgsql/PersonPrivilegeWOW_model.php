<?php
/**
 * PersonDispOrp13_model - модель, для работы с таблицей PersonPrivilegeWOW
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Абахри Самир
 * @version      август 2013
 */

class PersonPrivilegeWOW_model extends SwPgModel
{

    /**
     * @param $data
     * @return bool|mixed
     */
    public function savePersonPrivilegeWOW($data)
    {
        $proc = "p_PersonPrivilegeWOW_ins";

        if (empty($data['PersonPrivilegeWOW_id'])) {
            // проверка на присутствие человека в регистре
            $sql = "
				select
					count(Person_id) as count
				from
					v_PersonPrivilegeWOW
				where
					Person_id = :Person_id
				and
					PrivilegeTypeWOW_id = :PrivilegeTypeWOW_id
			";
            $res = $this->db->query($sql, $data);
            if ( !is_object($res) ) {
                $sel[0]['Error_Code'] = 1;
                $sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
                return $sel;
            }

            $sel = $res->result('array');
            if ($sel[0]['count'] > 0) {
                $sel[0]['Error_Code'] = 666;
                $sel[0]['Error_Msg'] = 'Данный пациент уже включён в регистр в вашем ЛПУ';
                return $sel;
            }

        } else {
            $proc = "p_PersonPrivilegeWOW_upd";
        }

        $sql = "
			select
			    PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$proc}
			(
			    PersonPrivilegeWOW_id := :PersonPrivilegeWOW_id,
				Server_id := :Server_id,
				PrivilegeTypeWOW_id := :PrivilegeTypeWOW_id,
				PersonPrivilegeWOW_begDate := :PersonPrivilegeWOW_begDate,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
            )
		";

        $res = $this->db->query($sql, $data);
        if (! is_object($res) )
            return false;

        return $res->result('array');
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    public function loadPersonPrivilegeWOWEditForm($data)
    {
        $query = "
			select
				Person_id as \"Person_id\",
				PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\",
				PrivilegeTypeWOW_id as \"PrivilegeTypeWOW_id\",
				to_char(PersonPrivilegeWOW_begDate, 'dd.mm.yyyy') as \"PersonPrivilegeWOW_begDate\"
			from
				v_PersonPrivilegeWOW
			where
				PersonPrivilegeWOW_id = :PersonPrivilegeWOW_id
		";
        $res = $this->db->query($query, $data);

        if (!is_object($res)) {
            return false;
        }

        return $res->result('array');
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    public function getPersonData($data)
    {
        $sql = "
			SELECT
				pstate.Person_SurName as \"Person_SurName\",
				pstate.Person_FirName as \"Person_FirName\",
				pstate.Person_BirthDay as \"Person_BirthDay\",
				pstate.SocStatus_id as \"SocStatus_id\",
				pstate.Sex_id as \"Sex_id\",
				pstate.UAddress_id as \"UAddress_id\",
				polis.Polis_Ser as \"Polis_Ser\",
				polis.Polis_Num as \"Polis_Num\",
				polis.OrgSmo_id as \"OrgSmo_id\",
				dbo.CheckINN(og.Org_INN) as \"Check_INN\",
				og.Org_id as \"Org_id\",
				og.Org_INN as \"Org_INN\",
				dbo.CheckOGRN(og.Org_OGRN) as \"Check_OGRN\",
				og.Org_OGRN as \"Org_OGRN\",
				og.Okved_id as \"Okved_id\",
				og.UAddress_id as \"OrgUAddress_id\"
			from
			    v_PersonState pstate
                left join Polis as polis on polis.Polis_id = pstate.Polis_id
                left join Job on Job.Job_id=pstate.Job_id
                left join v_Org as og on og.Org_id = Job.Org_id
			WHERE
				pstate.Person_id = :Person_id
		";
        $res = $this->db->query($sql, $data);
        if ( !is_object($res) )
            return false;

        return $res->result('array');
    }

    /**
     * Возвращает список регистра ВОВ введенных с заданной даты, для поточного ввода
     * @param $data
     * @return bool
     */
    public function loadStreamPersonPrivilegeWOW($data)
    {
        $filter = " and PPW.pmUser_insID = :pmUser_id";
        $queryParams['pmUser_id'] = $data['pmUser_id'];

        if ( (strlen($data['begDate']) > 0) && (strlen($data['begTime']) > 0) ) {
            $filter .= " and PPW.PersonPrivilegeWOW_insDT >= :begDateTime";
            $queryParams['begDateTime'] = $data['begDate']. " " . $data['begTime'];
        }

        $query = "
			select
				    PPW.PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\",
					PS.Person_id as \"Person_id\",
					PS.Server_id as \"Server_id\",
					PS.PersonEvn_id as \"PersonEvn_id\",
					rtrim(PS.Person_SurName) as \"Person_Surname\",
					rtrim(PS.Person_FirName) as \"Person_Firname\",
					rtrim(PS.Person_SecName) as \"Person_Secname\",
					UAdd.Address_Address as \"ua_name\",
					PAdd.Address_Address as \"pa_name\",
					Sex.Sex_Name as \"Sex_Name\",
					PS.Polis_Ser as \"Polis_Ser\",
					PS.Polis_Num as \"Polis_Num\",
					PTW.PrivilegeTypeWow_id as \"PrivilegeTypeWow_id\",
					PTW.PrivilegeTypeWOW_Name as \"PrivilegeTypeWOW_Name\",
					to_char(PS.Person_BirthDay::timestamp, 'dd.mm.yyyy') as \"Person_Birthday\"
			from
					v_PersonState PS
				    inner join PersonPrivilegeWOW PPW on PPW.Person_id = PS.Person_id
				    left join PrivilegeTypeWOW PTW on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id
				    left join Sex on Sex.Sex_id = PS.Sex_id
				    left join v_Address UAdd on UAdd.Address_id = ps.UAddress_id
				    left join v_Address PAdd on PAdd.Address_id = ps.PAddress_id

			where
				(1 = 1) " . $filter . "
			order by
                    PS.Person_SurName,
                    PS.Person_FirName,
                    PS.Person_SecName
		";

        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) )
            return false;

        return $result->result('array');
    }

	/**
	 * @param $data
	 * @return array|mixed
	 */
    public function deletePersonPrivilegeWOW($data)
    {

        $queryParams['PersonPrivilegeWOW_id'] = $data['PersonPrivilegeWOW_id'];

        $sql = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonPrivilegeWOW_del
			(
				PersonPrivilegeWOW_id := :PersonPrivilegeWOW_id
			)
		";


        $result = $this->db->query($sql,$queryParams);

        if ( !is_object($result) )
            return [['Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];

        return $result->result('array');
    }
}