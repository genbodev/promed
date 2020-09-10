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

class PersonPrivilegeWOW_model extends CI_Model {
    /**
     * PersonPrivilegeWOW_model constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    function savePersonPrivilegeWOW($data)
    {
        $proc = "p_PersonPrivilegeWOW_ins";

        $filter = "";

        if (empty($data['PersonPrivilegeWOW_id'])) {
            // проверка на присутствие человека в регистре
            $sql = "
				select
					count(Person_id) as count
				from
					v_PersonPrivilegeWOW with(nolock)
				where
					Person_id = :Person_id and
					PrivilegeTypeWOW_id = :PrivilegeTypeWOW_id
					{$filter}
			";
            $res = $this->db->query($sql, $data);
            if ( is_object($res) )
            {
                $sel = $res->result('array');
                if ($sel[0]['count'] > 0)
                {
                    $sel[0]['Error_Code'] = 666;
                    $sel[0]['Error_Msg'] = 'Данный пациент уже включён в регистр в вашем ЛПУ';
                    return $sel;
                }
            }
            else
            {
                $sel[0]['Error_Code'] = 1;
                $sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
                return $sel;
            }

        } else {
            $proc = "p_PersonPrivilegeWOW_upd";
        }

        $sql = "
			declare
				@PersonPrivilegeWOW_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonPrivilegeWOW_id = :PersonPrivilegeWOW_id;
			exec {$proc}
			    @PersonPrivilegeWOW_id = @PersonPrivilegeWOW_id output,
				@Server_id = :Server_id,
				@PrivilegeTypeWOW_id = :PrivilegeTypeWOW_id,
				@PersonPrivilegeWOW_begDate = :PersonPrivilegeWOW_begDate,
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @PersonPrivilegeWOW_id as PersonPrivilegeWOW_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        //echo getDebugSQL($sql, $data);
        $res = $this->db->query($sql, $data);
        if ( is_object($res) )
            return $res->result('array');
        else
            return false;
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function loadPersonPrivilegeWOWEditForm($data)
    {
        $query = "
			select
				Person_id,
				PersonPrivilegeWOW_id,
				PrivilegeTypeWOW_id,
				convert(varchar(10),PersonPrivilegeWOW_begDate, 104) as PersonPrivilegeWOW_begDate
			from
				v_PersonPrivilegeWOW (nolock)
			where
				PersonPrivilegeWOW_id = :PersonPrivilegeWOW_id
		";
        $res=$this->db->query($query, $data);

        if ( is_object($res) ) {
            return $res->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function getPersonData($data)
    {
        $sql = "
			SELECT
				pstate.Person_SurName,
				pstate.Person_FirName,
				pstate.Person_BirthDay,
				pstate.SocStatus_id,
				pstate.Sex_id,
				pstate.UAddress_id,
				polis.Polis_Ser,
				polis.Polis_Num,
				polis.OrgSmo_id,
				dbo.CheckINN(og.Org_INN) as Check_INN,
				og.Org_id,
				og.Org_INN,
				dbo.CheckOGRN(og.Org_OGRN) as Check_OGRN,
				og.Org_OGRN,
				og.Okved_id,
				og.UAddress_id as OrgUAddress_id
			FROM v_PersonState pstate with(nolock)
			LEFT JOIN
				Polis as polis with(nolock) on polis.Polis_id = pstate.Polis_id
			LEFT JOIN
				Job with(nolock) on Job.Job_id=pstate.Job_id
			LEFT JOIN
				v_Org as og with(nolock) on og.Org_id = Job.Org_id
			WHERE
				pstate.Person_id = :Person_id
		";
        $res = $this->db->query($sql, $data);
        if ( is_object($res) )
            return $res->result('array');
        else
            return false;

    }

    /**
     * Возвращает список регистра ВОВ введенных с заданной даты, для поточного ввода
     */
    function loadStreamPersonPrivilegeWOW($data) {
        $filter = '';
        $queryParams = array();

        $filter .= " and PPW.pmUser_insID = :pmUser_id";
        $queryParams['pmUser_id'] = $data['pmUser_id'];

        if ( (strlen($data['begDate']) > 0) && (strlen($data['begTime']) > 0) ) {
            $filter .= " and PPW.PersonPrivilegeWOW_insDT >= :begDateTime";
            $queryParams['begDateTime'] = $data['begDate']. " " . $data['begTime'];
        }

        $query = "
			select
				    PPW.PersonPrivilegeWOW_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					PTW.PrivilegeTypeWow_id,
					PTW.PrivilegeTypeWOW_Name,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday
					--isnull(rtrim(otherddlpu.Lpu_Nick), '') as OnDispInOtherLpu,
					--epldd1.EvnPLDispDop_id,
					--CASE WHEN epldd1.EvnPLDispDop_id is null THEN 'false' ELSE 'true' END as ExistsDDPL
			from
					v_PersonState PS with (nolock)
				    inner join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id
				    left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id
				    left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				    left join v_Address UAdd with(nolock) on UAdd.Address_id = ps.UAddress_id
				    left join v_Address PAdd with(nolock) on PAdd.Address_id = ps.PAddress_id

			where
				(1 = 1) " . $filter . "
			order by
                    PS.Person_SurName,
                    PS.Person_FirName,
                    PS.Person_SecName
		";
        //echo getDebugSQL($query, $queryParams); exit;
        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            //return $result->result('array');
            return $response = $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return array|mixed
	 */
    function deletePersonPrivilegeWOW($data)
    {

        $queryParams['PersonPrivilegeWOW_id'] = $data['PersonPrivilegeWOW_id'];

        $sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonPrivilegeWOW_del
				@PersonPrivilegeWOW_id = {$data['PersonPrivilegeWOW_id']},
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;";


        $result = $this->db->query($sql,$queryParams);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
    }

}