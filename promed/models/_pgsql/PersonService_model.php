<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonService_model - модель для работы с данными
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      ?
*/


class PersonService_model extends SwPgModel
{
	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPerson($data)
    {
	    if(empty($data['Person_id'])) {
	        $data['Person_id'] = null;
        }

		$query = "
            select
                Person_id as \"Person_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_Person_ins
			(
				Server_id := :Server_id,
				BDZ_id := :BDZ_id,
				pmUser_id := 1
			)
		";


		$queryParams = [
			'Server_id' => $data['Server_id'],
			'BDZ_id' => $data['bdzID']
		];

		$result = $this->db->query($query, $queryParams);

		if (! is_object($result) )
			return false;

        $resp = $result->result('array')[0];


        if ( $resp['Person_id'] ) {
            $query = "select 1 from PersonState where Person_id = :Person_id";
            $r = $this->db->query($query, $resp);

            if (is_object($r) && !count($r->result('array'))) {
                $query = "
                        insert into PersonState (Person_id, PersonState_insDT)
                        values (:Person_id, dbo.tzGetDate())
				    ";
                $this->db->query($query, $resp);
            }

        }

        return $resp;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonSurname($data)
    {

        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Person_Surname' => $data['surName']
        ];

	    $query = "
            select 1
            from
                PersonState ps
                inner join Person p on ps.Person_id = p.Person_id
                        and p.Person_id = :Person_id
                        and p.Server_id = :Server_id
            where
                (ps.PersonSurName_SurName is null or ps.PersonSurName_SurName != :Person_Surname)
                and ps.Person_id not in (
                    select
                        e1.Person_id
                    from
                        v_PersonSurName e1
                        inner join v_PersonSurName e2 on e1.Person_id = e2.Person_id
                                and e1.Server_id = 0
                                and e2.Server_id = 1
                                and e1.PersonSurName_insDT < e2.PersonSurName_insDT
                )
            limit 1
	    ";

	    $res = $this->getFirstResultFromQuery($query, $queryParams);
	    if($res) {
            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_PersonSurName_ins
                (
                    Person_id := :Person_id,
                    Server_id := :Server_id,
                    PersonSurName_SurName := :Person_Surname,
                    pmUser_id := 1
                )   
            ";

            $result = $this->db->query($query, $queryParams);

            if (!is_object($result) )
                return false;
            return $result->result('array');
        }
	    return  [['Error_Msg' => '']];
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonFirname($data)
    {
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Person_Firname' => $data['firName']
        ];
        $query = "
            select
                1
            from
                PersonState ps
                inner join Person p on ps.Person_id = p.Person_id
                    and p.Person_id = :Person_id
                    and p.Server_id = :Server_id
            where
                (ps.PersonFirName_FirName is null or ps.PersonFirName_FirName != :Person_Firname)
                and ps.Person_id not in (
                    select e1.Person_id
                    from v_PersonFirName e1
                        inner join v_PersonFirName e2 on e1.Person_id = e2.Person_id
                            and e1.Server_id = 0
                            and e2.Server_id = 1
                            and e1.PersonFirName_insDT < e2.PersonFirName_insDT
                )
            limit 1
        ";
        $res = $this->getFirstResultFromQuery($query, $queryParams);

        if($res) {
            $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from p_PersonFirName_ins
            (
                Person_id := :Person_id,
                Server_id := :Server_id,
                PersonFirName_FirName := :Person_Firname,
                pmUser_id := 1
			)";
            $result = $this->db->query($query, $queryParams);

            if ( !is_object($result) ) {
                return false;
            }

            if (!is_object($result) )
                return false;
            return $result->result('array');
        }

        return  [['Error_Msg' => '']];
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonSecname($data)
    {


        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Person_Secname' => ( strlen($data['secName']) > 0 ? $data['secName'] : NULL )
        ];
	    $query = "
                select
                    1
                from
                    PersonState ps
                    inner join Person p on ps.Person_id = p.Person_id 
                        and p.Person_id = :Person_id
                        and p.Server_id = :Server_id
                where
                    coalesce (ps.PersonSecName_SecName, '') != coalesce (:Person_Secname, '')
				and
				    ps.Person_id not in (
                        select
                            e1.Person_id
                        from
                            v_PersonSecName e1
                            inner join v_PersonSecName e2 on e1.Person_id = e2.Person_id
                                and e1.Server_id = 0
                                and e2.Server_id = 1
                                and e1.PersonSecName_insDT < e2.PersonSecName_insDT
					)
				limit 1
		";
        $res = $this->getFirstResultFromQuery($query, $queryParams);
	    if($res) {
	        $query = "
	            select
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
                from p_PersonSecName_ins
                (
                    Person_id := :Person_id,
                    Server_id := :Server_id,
                    PersonSecName_SecName := :Person_Secname,
                    pmUser_id := 1
                )
		    ";
            $result = $this->db->query($query, $queryParams);

            if (!is_object($result) )
                return false;
            return $result->result('array');
        }

        return [['Error_Msg' => '']];
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonBirthday($data)
    {
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Person_Birthday' => $data['birthDay']
        ];
        $query = "
                select 1
                from
                    PersonState ps
                    inner join Person p on ps.Person_id = p.Person_id
                        and p.Person_id = :Person_id
                        and p.Server_id = :Server_id
                where
                    (ps.PersonBirthDay_BirthDay is null or ps.PersonBirthDay_BirthDay != cast(:Person_Birthday as timestamp))
                and
                    ps.Person_id not in (
                        select e1.Person_id
                        from
                            v_PersonBirthDay e1
                            inner join v_PersonBirthDay e2 on e1.Person_id = e2.Person_id
                                and e1.Server_id = 0
                                and e2.Server_id = 1
                                and e1.PersonBirthDay_insDT < e2.PersonBirthDay_insDT
                    )
                limit 1
		";

        $res = $this->getFirstResultFromQuery($query, $queryParams);
        if($res) {
            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_PersonBirthDay_ins
                (
                    Person_id := :Person_id,
                    Server_id := :Server_id,
                    PersonBirthDay_BirthDay := :Person_Birthday,
                    pmUser_id := 1
                )
            ";
            $result = $this->db->query($query, $queryParams);
            if ( is_object($result) ) {
                return $result->result('array');
            } else {
                return false;
            }
        }

        return [['Error_Msg' => '']];
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonPolisEdNum($data)
    {
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Person_EdNum' => ( strlen($data['edNum']) > 0 ? $data['edNum'] : NULL )
        ];
	    $query = "
	        select
	            1
            from PersonState ps
                inner join Person p on ps.Person_id = p.Person_id
                    and p.Person_id = :Person_id
                    and p.Server_id = :Server_id
            where coalesce (ps.PersonPolisEdNum_EdNum, '') != coalesce (:Person_EdNum, '')
	    ";
	    $resp = $this->getFirstResultFromQuery($query, $queryParams);
	    if ($resp) {
	        $query = "
	            select 
	                Error_Code as \"Error_Code\",
	                Error_Message as \"Error_Msg\"
	            from p_PersonPolisEdNum_ins
	            (
                    Person_id := :Person_id,
                    Server_id := :Server_id,
                    PersonPolisEdNum_EdNum := :Person_EdNum,
                    pmUser_id := 1
                )
			";

            $result = $this->db->query($query, $queryParams);

            if (! is_object($result) )
                return false;

            return $result->result('array');
        }
	    return [['Error_Msg' => '']];
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonSex($data)
    {
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Sex_Code' => $data['sex']
        ];
        $Sex_id = $this->getFirstResultFromQuery("select Sex_id from Sex where Sex_Code = :Sex_Code limit 1", $queryParams);

        if($Sex_id) {
            $query = "
                select
                    1
                from PersonState ps 
                    inner join Person p on ps.Person_id = p.Person_id
                        and p.Person_id = :Person_id
                        and p.Server_id = :Server_id
                where coalesce(ps.Sex_id, 0) <> coalesce(:Sex_id, 0)
                    and ps.Person_id not in (
                        select e1.Person_id
                        from v_PersonSex e1
                            inner join v_PersonSex e2 on e1.Person_id = e2.Person_id
                                and e1.Server_id = 0
                                and e2.Server_id = 1
                                and e1.PersonSex_insDT < e2.PersonSex_insDT
                )
            ";
            $res = $this->getFirstResultFromQuery($query, array_merge($queryParams, ['Sex_id' => $Sex_id]));
            if($res) {
                $query = "
                    select
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    from p_PersonSex_ins
                    (
                        Person_id := :Person_id,
                        Server_id := :Server_id,
                        Sex_id := :Sex_id,
                        pmUser_id := 1
                    )
                ";
                $result = $this->db->query($query, $queryParams);

                if ( !is_object($result) ) {
                    return false;
                }
                return $result->result('array');
            }
        }

        return [['Error_Msg' => '']];
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonUAddress($data)
    {
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'Kladr_Code' => $data['uaddressKladr'],
            'Address_House' => $data['uaddressHome'],
            'Address_Flat' => $data['uaddressFlat']
        ];
        $query = "
            select
                KLR.KLArea_id as \"KLRgn_id\",
                KLS.KLArea_id as \"KLSubRgn_id\",
                KLC.KLArea_id as \"KLCity_id\",
                KLT.KLArea_id as \"KLTown_id\"
            from 
                (
                    select
                        KLArea_id
                    from
                        KLArea
                    where
                        Kladr_Code = LEFT(:Kladr_Code, 2) || repeat('0', 11)
                    and
                        KLAreaLevel_id = 1
                    limit 1
                ) as KLR
                left join lateral (
                    select
                        KLArea_id
                    from
                        KLArea
                    where
                        Kladr_Code = LEFT(:Kladr_Code, 5) || repeat('0', 8)
                    and
                        KLAreaLevel_id = 2
                    limit 1
                ) KLS on true
                left join lateral (
                    select
                        KLArea_id
                    from
                        KLArea
                    where
                        Kladr_Code = LEFT(:Kladr_Code, 8) || repeat('0', 5)
                    and
                        KLAreaLevel_id = 3
                    limit 1
                ) KLC on true
                left join lateral (
                    select
                        KLArea_id
                    from
                        KLArea
                    where
                        Kladr_Code = LEFT(:Kladr_Code, 11) || repeat('0', 2)
                    and
                        KLAreaLevel_id = 4 limit 1
                ) KLT on true
            limit 1
        ";

        $vars = $this->queryResult($query, $queryParams)[0];
        $queryParams = array_merge($vars, $queryParams);

        $queryParams['KLStreet_id'] = NULL;
        if(mb_strlen($queryParams['Kladr_Code']) == 17) {
            $query = "
                select
                    KLStreet_id
                from
                    KLStreet
                where
                    Kladr_Code = LEFT(:Kladr_Code, 15) || repeat('0', 2)
                limit 1
            ";
            $queryParams['KLStreet_id'] = $this->getFirstResultFromQuery($query,$queryParams);
        }


        $query = "
            select
                1
            from PersonState ps
                inner join Person p on ps.Person_id = p.Person_id
                    and p.Person_id = :Person_id
                    and p.Server_id = :Server_id
                left join Address a on ps.UAddress_id = a.Address_id
            where
                (
                    coalesce (a.KLRgn_id, 0) <> coalesce (:KLRgn_id::bigint, 0)
                or
                    coalesce (a.KLSubRgn_id, 0) <> coalesce (:KLSubRgn_id::bigint, 0)
                or
                    coalesce (a.KLCity_id, 0) <> coalesce (:KLCity_id::bigint, 0)
                or
                    coalesce (a.KLTown_id, 0) <> coalesce (:KLTown_id::bigint, 0)
                or
                    coalesce (a.KLStreet_id, 0) <> coalesce (:KLStreet_id::bigint, 0)
                or
                    coalesce (a.Address_House, '') <> coalesce (:Address_House, '')
                or
                    coalesce (a.Address_Flat, '') <> coalesce (:Address_Flat, '')
                )
            and
                ps.Person_id not in (
                    select
                        e1.Person_id
                    from v_PersonUAddress e1 
                        inner join v_PersonUAddress e2 on e1.Person_id = e2.Person_id
                            and e1.Server_id = 0
                            and e2.Server_id = 1
                            and e1.PersonUAddress_insDT < e2.PersonUAddress_insDT
                )
		";
        $res = $this->getFirstResultFromQuery($query, $queryParams);
        if($res) {
            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_PersonUAddress_ins
                (
                    Person_id := :Person_id,
                    PersonUAddress_id := null,
                    Server_id := :Server_id,
                    PersonUAddress_Index := null,
                    PersonUAddress_Count := null,
                    PersonUAddress_insDT := null,
                    Address_id := null,
                    KLAreaType_id := null,
                    KLCountry_id := 643,
                    KLRgn_id := :KLRgn_id,
                    KLSubRgn_id := :KLSubRgn_id,
                    KLCity_id := :KLCity_id,
                    KLTown_id := :KLTown_id,
                    KLStreet_id := :KLStreet_id,
                    Address_Zip := null,
                    Address_House := :Address_House,
                    Address_Corpus := null,
                    Address_Flat := :Address_Flat,
                    Address_Address := null,
                    pmUser_id := 1
                )
            ";
            $result = $this->db->query($query, $queryParams);

            if (! is_object($result) )
                return false;

            return $result->result('array');
        }

        return [['Error_Msg' => '']];
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function addPersonPolis($data)
    {
        $queryParams = [
            'Person_id' => $data['Person_id'],
            'Server_id' => $data['Server_id'],
            'OrgSmo_RegNomC' => $data['regNomC'],
            'OrgSmo_RegNomN' => $data['regNomN'],
            'OMSSprTerr_Code' => $data['sprTerr'],
            'Polis_Ser' => $data['polisSer'],
            'Polis_Num' => $data['polisNum'],
            'Polis_begDate' => $data['polisBegDate']
        ];
        $query = "
            select
                OMS.OMSSprTerr_id as \"OMSSprTerr_id\",
                Org.OrgSmo_id as \"OrgSmo_id\"
            from
                (
                    select
                        OMSSprTerr_id
                    from
                        OMSSprTerr where coalesce (OMSSprTerr_Code, 0) = coalesce (:OMSSprTerr_Code, 0)
                    or
                        (
                         OMSSprTerr_Code = 1 and coalesce (:OMSSprTerr_Code, 0) between 1 and 8
                        )
                ) OMS
                left join lateral (
                    select
                        OrgSmo_id
                    from
                        OrgSmo
                    where
                        coalesce (OrgSmo_RegNomC, 0) = coalesce (:OrgSmo_RegNomC, 0)
                    and
                        coalesce (OrgSmo_RegNomN, 0) = coalesce (:OrgSmo_RegNomN, 0)
                    limit 1
                ) Org on true
            ";

        $vars = $this->queryResult($query, $queryParams)[0];

        $queryParams = array_merge($queryParams, $vars);


        $query = "
            select
                1
            from
                PersonState ps
                inner join Person p on ps.Person_id = p.Person_id
                    and p.Person_id = :Person_id
                    and p.Server_id = :Server_id
                left join Polis pol on pol.Polis_id = ps.Polis_id
                left join OrgSmo os on os.OrgSmo_id = pol.OrgSmo_id
            where
                coalesce (os.OrgSmo_id, 0) <> coalesce (:OrgSmo_id::bigint, 0)
            or
                coalesce (pol.OMSSprTerr_id, 0) <> coalesce (:OMSSprTerr_id::bigint, 0)
            or
                coalesce(pol.Polis_Ser, '') <> coalesce (:Polis_Ser, '')
            or
                coalesce(pol.Polis_Num, 0) <> coalesce (:Polis_Num, 0)
            or
                pol.Polis_begDate::timestamp <> cast(:Polis_begDate as timestamp)
            limit 1
		";
        $res = $this->getFirstResultFromQuery($query, $queryParams);
        if($res) {
            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_PersonPolis_ins
                (
                    Server_id := :Server_id,
                    PersonPolis_id := null,
                    Person_id := :Person_id,
                    PersonPolis_Index := null,
                    PersonPolis_Count := null,
                    PersonPolis_insDT := null,
                    Polis_id := null,
                    PolisType_id := 1,
                    OrgSMO_id := :OrgSmo_id,
                    OmsSprTerr_id := :OMSSprTerr_id,
                    Polis_Ser := :Polis_Ser,
                    Polis_Num := :Polis_Num,
                    Polis_begDate := :Polis_begDate,
                    Polis_endDate := null,
                    pmUser_id := 1
				)
			";
            $result = $this->db->query($query, $queryParams);

            if (! is_object($result) )
                return false;
            return $result->result('array');
        }
        return [['Error_Msg' => '']];
	}

	/**
	 * @param $bdzId
	 * @return array
	 */
	public function checkPersonExists($bdzId)
    {
		$checkResult = [
			'Error_Msg' => '',
			'Person_id' => 0
		];

		$query = "
			SELECT
			    Person_id
			FROM
			    Person
			WHERE
			    BDZ_id = :BDZ_id
			ORDER BY
			    Person_insDT desc
		    limit 1
		";


		$result = $this->db->query($query, ['BDZ_id' => $bdzId]);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) ) {
				if ( $response[0]['Person_id'] > 0 ) {
					$checkResult['Person_id'] = $response[0]['Person_id'];
				}
			} else {
				$checkResult['Error_Msg'] = 'Ошибка при проверке наличия человека в БД';
			}
		} else {
			$checkResult['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка наличия человека в БД)';
		}

		return $checkResult;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function attachPersonToLpu($data)
    {
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonCard_ins
			(
				Server_id := :Server_id,
				Person_id := :Person_id,
				Lpu_id := :Lpu_id,
				LpuAttachType_id := 1,
				PersonCard_begDate := :PersonCard_begDate,
				PersonCard_IsAttachCondit := 2,
				pmUser_id := 1
			)
		";

		$queryParams = [
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['AttachLpu_id'],
			'PersonCard_begDate' => date('Y-m-d')
		];

		$result = $this->db->query($query, $queryParams);


		if ( !is_object($result) )
            return false;

        return $result->result('array');
	}

	/**
	 * @param $personId
	 * @return bool|mixed
	 */
	public function getAttachLpu($personId)
    {
		$query = "
			select
			    Lpu_id as \"AttachLpu_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from xp_PersonAttach
			(
				Person_id := :Person_id,
				LpuAttachType_id := 1
            )   
		";

		$result = $this->db->query($query, ['Person_id' => $personId]);


		if (! is_object($result) )
			return false;

        return $result->result('array');
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function putPersonCardState($data) {
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonCardQueue_status
			(
				pmUser_id := 1,
				PersonCardQueue_Status := :PersonCardQueue_Status,
				PersonCardQueue_id := :PersonCardQueue_id
			)
		";

		$queryParams = [
			'PersonCardQueue_id' => $data['transactCode'],
			'PersonCardQueue_Status' => $data['status']
		];

		$result = $this->db->query($query, $queryParams);


		if (! is_object($result) )
			return false;

        return $result->result('array');
	}
}