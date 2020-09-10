<?php

require_once(APPPATH.'models/_pgsql/Person_model.php');

class Samara_Person_model extends Person_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}  
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function getPostColoredList($data) {
        
        $filter = "";
        $queryParams = array();
       
        if( isset($data['Post_id'])){
            $filter .= 'and Post_id = :Post_id';
            $queryParams['Post_id'] = $data['Post_id'];
        }
        else{
            if( isset($data['query'])){
                $filter .= ' and Post_Name ILIKE :query';
                $queryParams['query'] = '%'.$data['query'].'%';
            };
        }
              
        $query = "
            SELECT
                Post_id as \"Post_id\",
                RTRIM(Post_Name) as \"Post_Name\"
            FROM
                Post
            WHERE (1 = 1)
                " . $filter . "
        ";
        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            return $res->result('array');
        }

        return false;
        
	}     
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function getPersonEditWindow($data) {
		$sql = "
			SELECT
				vper.Person_SurName as \"Person_SurName\",
				vper.Person_SecName as \"Person_SecName\",
				vper.Person_FirName as \"Person_FirName\",
				-- если человек имеет федеральную льготу, то устанавливаем Server_pid = 1
				-- убрать, когда будет сделан адекватный механизм сбора атрибутов человека
				--CASE WHEN ( (fedl.Person_id is not null) ) THEN 1 ELSE -1 END as Server_pid,
				-- федеральный льготник
				case 
					when PersonPrivilegeFed.Person_id is not null then 1 
					else 0 
				end as \"Person_IsFedLgot\",
				vper.Server_pid as \"Server_pid\",
				vper.Person_id as \"Person_id\",
				to_char(cast(vper.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				vper.Sex_id as \"PersonSex_id\",
				vper.Person_Snils as \"Person_SNILS\",
				vper.SocStatus_id as \"ocStatus_id\",
				vper.FamilyStatus_id as \"amilyStatus_id\",
				vper.PersonFamilyStatus_IsMarried as \"PersonFamilyStatus_IsMarried\",
				vper.Person_edNum as \"Federal_Num\",
				vper.UAddress_id as \"UAddress_id\",
				uaddr.Address_Zip as \"UAddress_Zip\",
				uaddr.KLCountry_id as \"UKLCountry_id\",
				uaddr.KLRGN_id as \"UKLRGN_id\",
				uaddr.KLSubRGN_id as \"UKLSubRGN_id\",
				uaddr.KLCity_id as \"UKLCity_id\",
				pi.UPersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				uaddr.KLTown_id as \"UKLTown_id\",
				uaddr.KLStreet_id as \"UKLStreet_id\",
				uaddr.Address_House as \"UAddress_House\",
				uaddr.Address_Corpus as \"UAddress_Corpus\",
				uaddr.Address_Flat as \"UAddress_Flat\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				uaddr.Address_Address as \"UAddress_Address\",
				baddr.Address_id as \"Address_id\",
				baddr.KLCountry_id as \"BKLCountry_id\",
				baddr.KLRGN_id as \"BKLRGN_id\",
				baddr.KLSubRGN_id as \"BKLSubRGN_id\",
				baddr.KLCity_id as \"BKLCity_id\",
				baddr.KLTown_id as \"BKLTown_id\",
				baddr.KLStreet_id as \"BKLStreet_id\",
				baddr.Address_House as \"BAddress_House\",
				baddr.Address_Corpus as \"BAddress_Corpus\",
				baddr.Address_Flat as \"BAddress_Flat\",
				baddr.Address_Address as \"BAddress_AddressText\",
				baddr.Address_Address as \"BAddress_Address\",
				pcc.PolisCloseCause_Code as \"polisCloseCause\",
				vper.PAddress_id as \"PAddress_id\",
				paddr.Address_Zip as \"PAddress_Zip\",
				paddr.KLCountry_id as \"PKLCountry_id\",
				paddr.KLRGN_id as \"PKLRGN_id\",
				paddr.KLSubRGN_id as \"PKLSubRGN_id\",
				paddr.KLCity_id as \"PKLCity_id\",
				pi.PPersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
				paddr.KLTown_id as \"PKLTown_id\",
				paddr.KLStreet_id as \"PKLStreet_id\",
				paddr.Address_House as \"PAddress_House\",
				paddr.Address_Corpus as \"PAddress_Corpus\",
				paddr.Address_Flat as \"PAddress_Flat\",
				paddr.Address_Address as \"PAddress_AddressText\",
				paddr.Address_Address as \"PAddress_Address\",
				pi.Nationality_id as \"PersonNationality_id\",
				pol.OmsSprTerr_id as \"OMSSprTerr_id\",
				pol.PolisType_id as \"PolisType_id\",
				pol.Polis_Ser as \"Polis_Ser\",
				pol.Polis_Num as \"Polis_Num\",
				pol.OrgSmo_id as \"OrgSMO_id\",
				to_char(cast(pol.Polis_begDate as timestamp), 'dd.mm.yyyy') as \"Polis_begDate\",
				to_char(cast(pol.Polis_endDate as timestamp), 'dd.mm.yyyy') as \"Polis_endDate\",
				doc.DocumentType_id as \"DocumentType_id\",
				doc.Document_Ser as \"Document_Ser\",
				doc.Document_Num as \"Document_Num\",
				doc.OrgDep_id as \"OrgDep_id\",
				ns.KLCountry_id as \"KLCountry_id\",
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				pjob.Org_id as \"Org_id\",
				pjob.OrgUnion_id as \"OrgUnion_id\",
				pjob.Post_id as \"Post_id\",
				to_char(cast(doc.Document_begDate as timestamp), 'dd.mm.yyyy') as \"Document_begDate\",
				PDEP.DeputyKind_id as \"DeputyKind_id\",
				PDEP.Person_pid as \"DeputyPerson_id\",
				case when PDEPSTATE.Person_id is not null
					THEN PDEPSTATE.Person_SurName
						|| ' ' || PDEPSTATE.Person_FirName
						|| ' ' || coalesce(PDEPSTATE.Person_SecName, '')
					ELSE ''
				END as \"DeputyPerson_Fio\",
				ResidPlace_id as \"ResidPlace_id\",
				PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
				PersonChild_IsBad as \"PersonChild_IsBad\",
				PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
				PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
				PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
				PersonChild_IsTutor as \"PersonChild_IsTutor\",
				PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
				HealthKind_id as \"HealthKind_id\",
				ph.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				ph.HeightAbnormType_id as \"HeightAbnormType_id\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				pw.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PCh.PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				FeedingType_id as \"FeedingType_id\",
				InvalidKind_id as \"InvalidKind_id\",
				to_char(cast(PersonChild_invDate as timestamp), 'dd.mm.yyyy') as \"PersonChild_invDate\",
				HealthAbnorm_id as \"HealthAbnorm_id\",
				HealthAbnormVital_id as \"HealthAbnormVital_id\",
				Diag_id as \"Diag_id\",
				to_char(cast(vper.Person_deadDT as timestamp), 'dd.mm.yyyy') as \"Person_deadDT\",
				to_char(cast(vper.Person_closeDT as timestamp), 'dd.mm.yyyy') as \"Person_closeDT\",
				rtrim(vper.Person_Phone) as \"PersonPhone_Phone\",
				rtrim(pi.PersonInfo_InternetPhone) as \"PersonInfo_InternetPhone\",
				rtrim(vper.Person_Inn) as \"PersonInn_Inn\",
				rtrim(vper.Person_SocCardNum) as \"PersonSocCardNum_SocCardNum\",
				rtrim(Ref.PersonRefuse_IsRefuse) as \"PersonRefuse_IsRefuse\",
				rtrim(pce.PersonCarExist_IsCar) as \"PersonCarExist_IsCar\",
				rtrim(pche.PersonChildExist_IsChild) as \"PersonChildExist_IsChild\",
				ph.PersonHeight_Height as \"PersonHeight_Height\",
				coalesce(pw.Okei_id, 37) as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				-- признак того, что человек БДЗшный и у него закончился полис и можно дать ввести иногородний
				CASE WHEN vper.Server_pid = 0
					and pol.Polis_endDate is not null
					and pol.Polis_endDate < dbo.tzgetdate()
					THEN 1
					ELSE 0
				END as \"Polis_CanAdded\",
				pi.Ethnos_id as \"Ethnos_id\",
				mop.OnkoOccupationClass_id as \"OnkoOccupationClass_id\",
				vper.Lpu_id as \"AttachLpu_id\"
			from v_PersonState vper
				left join v_Address uaddr on vper.UAddress_id = uaddr.Address_id
				left join v_Address paddr on vper.PAddress_id = paddr.Address_id
				-- Адрес рождения
				left join PersonBirthPlace pbp on vper.Person_id = pbp.Person_id
				left join v_Address baddr on pbp.Address_id = baddr.Address_id
				-- end. Адрес рождения
	
				left join Polis pol on pol.Polis_id=vper.Polis_id
				left join v_PolisCloseCause pcc on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
				left join Document doc on doc.Document_id=vper.Document_id
				left join NationalityStatus ns on ns.NationalityStatus_id = vper.NationalityStatus_id
				left join PersonInfo pi on pi.Person_id = vper.Person_id
				left join Job pjob on vper.Job_id = pjob.Job_id
				left join PersonDeputy PDEP on PDEP.Person_id = vper.Person_id
				left join v_PersonState PDEPSTATE on PDEPSTATE.Person_id = PDEP.Person_pid
				left join PersonChild PCh on PCh.Person_id = vper.Person_id
				left join lateral(
					select
						pp.Person_id
					from
						v_PersonPrivilege pp
						inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					where
						pt.ReceptFinance_id = 1
						and pp.PersonPrivilege_begDate <= dbo.tzgetdate()
						and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= cast(dbo.tzgetdate() as date))
						and pp.Person_id = vper.Person_id
					limit 1
				) PersonPrivilegeFed on true
				left join lateral(
					select
						OnkoOccupationClass_id
					from
						v_MorbusOnkoPerson
					where
						Person_id = :Person_id
					order by
						MorbusOnkoPerson_insDT desc
					limit 1
				) as mop on true
				left join lateral(
					select
						PersonRefuse_IsRefuse
					from
						v_PersonRefuse
					where
						Person_id = :Person_id
						and PersonRefuse_Year = date_part('year', dbo.tzgetdate())
					order by
						PersonRefuse_insDT desc
					limit 1
				) as Ref on true
				left join lateral(
					select
						PersonCarExist_IsCar
					from
						PersonCarExist
					where
						Person_id = :Person_id
					order by
						PersonCarExist_setDT desc
					limit 1
				) as pce on true
				left join lateral(
					select
						PersonChildExist_IsChild
					from
						PersonChildExist
					where
						Person_id = :Person_id
					order by
						PersonChildExist_setDT desc
					limit 1
				) as pche on true
				left join lateral(
					select
						PersonHeight_Height,
						PersonHeight_IsAbnorm,
						HeightAbnormType_id
					from
						PersonHeight
					where
						Person_id = :Person_id
					order by
						PersonHeight_setDT desc
					limit 1
				) as ph on true
				left join lateral(
					select
						PersonWeight_Weight,
						WeightAbnormType_id,
						PersonWeight_IsAbnorm,
						Okei_id
					from
						PersonWeight
					where
						Person_id = :Person_id
					order by
						PersonWeight_setDT desc
					limit 1
				) as pw on true
			where vper.Person_id= :Person_id
			limit 1
		";
		/*echo getDebugSQL($sql, array(
						'Person_id' => $data['person_id']
					));exit();*/
		$res = $this->db->query(
			$sql,
			array(
				'Person_id' => $data['person_id']
			)
		);
		if ( is_object($res) )
		{
			
			$return = $res->result('array');
			if (count($return)>0)
			{
				// если порожден электронной регистратурой, то отправляем сразу его с открытым на редактирование
				if ( $return[0]['Server_pid'] == 3 )
				{
					$return[0]["Servers_ids"] = "[3]";
					return $return;
				}
				$sql = "
					SELECT distinct
						Server_id as \"Server_id\"
					FROM
						v_Person_all
					WHERE
						Person_id = :Person_id
					union all
					select case when exists(
						SELECT
							PersonPrivilege_id
						FROM
							v_PersonPrivilege reg
							inner join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
						WHERE
							reg.person_id = :Person_id
							and pt.ReceptFinance_id = 1
							and reg.personprivilege_begdate <= dbo.tzgetdate()
							and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(to_char(dbo.tzgetdate(), 'yyyyddmm') as timestamp))
						limit 1
					)
					then 1 end
				";
				
				$res = $this->db->query(
					$sql,
					array(
						'Person_id' => $data['person_id']
					)
				);
				if ( is_object($res) )
				{
					$servers = $res->result('array');
					$servers_arr = array();
					$sys_server_flag = false;
					foreach ( $servers as $row )
					{
						if ( $row['Server_id'] != '' )
						{
							if ( $return[0]['Server_pid'] > 0 && $row['Server_id'] == 0 )
								continue;
							$servers_arr[] = $row['Server_id'];
							if ( $row['Server_id'] == 1 || $row['Server_id'] == 0 )
								$sys_server_flag = true;
						}
					}
					if ( $sys_server_flag === true )
					{
						$servers_new_arr = array();
						foreach ( $servers_arr as $value )
						{
							if ( $return[0]['Server_pid'] > 0 && $value == 0 )
								continue;
							if ( $value == 1 || $value == 0 )
							{
								$servers_new_arr[] = $value;
							}
						}
						$servers_arr = $servers_new_arr;
					}
					// если суперадмин, то отсылаем его для предоставления возможности редактирования недоступных полей
					if ( preg_match("/SuperAdmin/", $data['session']['groups']) )
						$servers_str = "['SuperAdmin']";
					else
						$servers_str = "[" . implode(", ", $servers_arr) . "]";
					$return[0]["Servers_ids"] = $servers_str;
					return $return;
				}
				else 
					return false;
			}
			else 
			{
				// Ошибкама - по этому Person_id человечка не нашли
				return false;
			}
		}
 	    else
 	    	return false;
    }
    
	/**
	 * Проверка на дублирование СНИЛС-а
	 * Возвращает true если дублей не найдено
	 */
	function checkPersonDoublesSamara($data) {
		$query = "
			select
				 ps.Person_id as \"Person_id\"
			from
				v_PersonState ps
			where
				ps.Person_BirthDay = :Person_BirthDay 
				and ps.Person_SurName =  :Person_SurName
				and ps.Person_FirName =  :Person_FirName
				and (ps.Person_SecName =  :Person_SecName or (:Person_SecName = '' and ps.Person_SecName is null))
		";
		
		$result = $this->db->query($query, $data);
		return $result->result('array');
	}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function savePerson(&$person){
		/**
		 * Некая абстрактная функция TODO: описать
		 */
		function makeQuery($procname, $field, $key){
			return "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from " . $procname . "(
					Server_id := :Server_id,
					Person_id := :Person_id,
					" . $field . " := :" . $key . ",
					pmUser_id := :pmUser_id
				)
			";
		}
	
		if (!$person['Person_id']){
			$sql="
				select
					Person_id as \"Person_id\",
					Error_Code as \"Error_Code\",
					Error_Msg as \"Error_Msg\"
				from p_Person_ins(
					Server_id := :Server_id,
					Person_id := :Person_id,
					pmUser_id := :pmUser_id
				)
			";
	
			$res = $this->db->query($sql, $person);
			$res = $res->result('array');
			$person['Person_id'] = $res[0]['Person_id']; // вернуть
		}
	
		$inserts = array(
				array('proc' => 'p_PersonSurName_ins',	'field' => 'PersonSurName_SurName',		'key' => 'Person_SurName'),
				array('proc' => 'p_PersonFirName_ins',	'field' => 'PersonFirName_FirName',		'key' => 'Person_FirName'),
				array('proc' => 'p_PersonSecName_ins',	'field' => 'PersonSecName_SecName',		'key' => 'Person_SecName'),
				array('proc' => 'p_PersonSex_ins',		'field' => 'Sex_id',					'key' => 'PersonSex_id'),
				array('proc' => 'p_PersonBirthDay_ins',	'field' => 'PersonBirthDay_BirthDay',	'key' => 'Person_BirthDay'),
				array('proc' => 'p_PersonPolisEdNum_ins',	'field' => 'PersonPolisEdNum_EdNum','key' => 'Federal_Num')
		);
	

		// вызов функций вставки полей о человеке 
		foreach($inserts as $insert){
			$sql = makeQuery($insert['proc'], $insert['field'], $insert['key']);
			$res = $this->db->query($sql, $person);
		}
	
		// лпу прикрепления
		$sql = "UPDATE PersonState
			  	SET Lpu_id = :Lpu_id
				WHERE Person_id = :Person_id";
		
		$res = $this->db->query($sql, $person);
		
		$sql = "select PersonEvn_id as \"PersonEvn_id\"
				from PersonState
				where Person_id = :Person_id
				limit 1";
	
		$res = $this->db->query($sql,$person);
	
		$res = $res->result('array');
		$person['PersonEvn_id'] = $res[0]['PersonEvn_id']; // вернуть
	
		return $person;
	}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function savePersonPolis(&$person){
	
		$sql = "select OrgSmo_id as \"OrgSmo_id\"
				from OrgSmo
				where Orgsmo_f002smocod = :Orgsmo_f002smocod
				limit 1";
	
		$res = $this->db->query($sql,$person);
		$res = $res->result('array');
		$res = $res[0];
		$person['OrgSmo_id'] = $res['OrgSmo_id'];
		
		
		$sql = "select PolisType_id as \"PolisType_id\"
				from PolisType
				where PolisType_CodeF008 = :PolisType_Code
				limit 1";
		
		$res = $this->db->query($sql,$person);
		$res = $res->result('array');
		$res = $res[0];
		
		$person['PolisType_id'] = $res['PolisType_id'];
		
		$person['Polis_Ser'] = empty($person['Polis_Ser']) ? '' : $person['Polis_Ser'];
		$person['Polis_Num'] = empty($person['Polis_Num']) ? ($person['PolistType_Code'] = 3 ? $person['Federal_Num'] : '') : $person['Polis_Num'];
				
		$sql="
			select
				Error_Message as \"ErrMsg\",
				Error_Code as \"Error_Code\"
			from p_PersonPolis_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				OmsSprTerr_id := :OmsSprTerr_id,
				PolisType_id := :PolisType_id,
				OrgSmo_id := :OrgSmo_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Polis_begDate := :Polis_begDate,
				Polis_endDate := :Polis_endDate,
				pmUser_id := :pmUser_id
			)
		";
			
		$res = $this->db->query($sql,$person);
	}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function savePersonDocument(&$person){
	
		$sql = "select DocumentType_id as \"DocumentType_id\"
				from DocumentType
				where DocumentType_Code = :DocumentType_Code
				limit 1";
	
		$res = $this->db->query($sql, $person);
		$res = $res->result('array');
		$res = $res[0];
	
		$person['DocumentType_id'] = $res['DocumentType_id'];
		$person['PersonDocument_insDT'] = date('Y-m-d H:i:s', time());
		$person['OrgDep_id'] = NULL;
		$person['Document_begDate'] = NULL;
	
		$sql="
			select
				Error_Message as \"ErrMsg\",
				Error_Code as \"Error_Code\"
			from p_PersonDocument_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonDocument_insDT := :PersonDocument_insDT,
				DocumentType_id := :DocumentType_id,
				OrgDep_id := :OrgDep_id,
				Document_Ser := :Document_Ser,
				Document_Num := :Document_Num,
				Document_begDate := :Document_begDate,
				pmUser_id := :pmUser_id
			)
		";
	
		$res = $this->db->query($sql, $person);
	}
	
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function savePersonUAddress(&$person){
	
		$sqlFind = "
			select
				KLCountry_id as \"KLCountry_id\",
				KLRgn_id as \"KLRgn_id\",
				KLSubRgn_id as \"KLSubRgn_id\",
				KLCity_id as \"KLCity_id\",
				KLTown_id as \"KLTown_id\",
				KLStreet_id as \"KLStreet_id\"
			from r63.TransformTFOMSAddress(:Ocatd,:Streetdbf_id)";
	
		$res = $this->db->query($sqlFind, $person);
	
		$kl = $res->result('array');
		$kl = $kl[0];
	
		$person['KLCountry_id'] 	= $kl['KLCountry_id'];
		$person['KLRgn_id'] 		= $kl['KLRgn_id'];
		$person['KLSubRgn_id'] 		= $kl['KLSubRgn_id'];
		$person['KLCity_id'] 		= $kl['KLCity_id'];
		$person['KLTown_id'] 		= $kl['KLTown_id'];
		$person['KLStreet_id'] 		= $kl['KLStreet_id'];
		$person['Address_Zip'] 		= NULL;
		$person['Address_Address'] 	= NULL;
		//$person['Address_begDate'] 	= NULL;
		$person['PersonUAddress_insDT'] =  date('Y-m-d H:i:s', time());

		$sql="
			select
				Error_Message as \"ErrMsg\",
				Error_Code as \"Error_Code\"
			from p_PersonUAddress_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonUAddress_insDT := :PersonUAddress_insDT,
				KLCountry_id := :KLCountry_id,
				KLRgn_id := :KLRgn_id,
				KLSubRgn_id := :KLSubRgn_id,
				KLCity_id := :KLCity_id,
				KLTown_id := :KLTown_id,
				KLStreet_id := :KLStreet_id,
				Address_Zip := :Address_Zip,
				Address_House := :Address_House,
				Address_Corpus := :Address_Corpus,
				Address_Flat := :Address_Flat,
				Address_Address := :Address_Address,
				pmUser_id := :pmUser_id
			)
		";
	
		$res = $this->db->query($sql, $person);
	
	}
	
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function getPersonSearchGrid($data)
	{
		$join = "";
		$queryParams = array();
		$filters = array();
		$orderFirst = '';
		$extraSelect = '';
		$queryParams['Lpu_id'] = $data['Lpu_id'];
	
		// проверяем не идет ли поиск по типу регистра
		if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] > 0 && isset($data['DrugRequestPeriod_id']) && $data['DrugRequestPeriod_id'] > 0) {
			$queryParams['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
			$queryParams['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
	
			$query = "
				select
					PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\"
				from
					v_PersonRegisterType
				where
					PersonRegisterType_id = :PersonRegisterType_id;
			";
			$person_register_nick = $this->getFirstResultFromQuery($query, $queryParams);
	
			if (strpos($person_register_nick, 'common') === false) { //если морбус не является общетерапевтическим
				$filters[] = "
					ps.Person_id in (
						select
							Person_id
						from
							v_PersonRegister
						where
							(
								PersonRegister_disDate is null or
								PersonRegister_disDate > (select DrugRequestPeriod_begDate from v_DrugRequestPeriod where DrugRequestPeriod_id = :DrugRequestPeriod_id)
							)
							and PersonRegisterType_id = :PersonRegisterType_id
					)
				";
			} else {
				if ($person_register_nick == 'common_fl') { //ОНЛС: общетерапевтическая группа
					$filters[] = "fedl.Person_id is not null"; //только федеральные льготники
				}
	
				if ($person_register_nick == 'common_rl') { //РЛО: общетерапевтическая группа
					$filters[] = "regl.OwnLpu is not null"; //только региональные льготники
				}
			}
		}
	
		// если ищем по ДД, то добавляем еще один inner join c PersonDopDisp
		if (strtolower($data['searchMode'])=='dd') {
			$queryParams['Year'] = ( isset($data['Year']) && (int)$data['Year'] > 1970 ) ? (int)$data['Year'] : date('Y');
			$join .= " inner join PersonDopDisp pdd on pdd.Person_id = ps.Person_id
				and pdd.Lpu_id = :Lpu_id
				and pdd.PersonDopDisp_Year = :Year ";
			$data['searchMode']='all';
		}
	
		// только прикреплённые
		if (strtolower($data['searchMode'])=='att') {
			$filters[] = "
				pcard.Lpu_id = :Lpu_id
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$data['searchMode']='all';
		}
	
		// дети младше 6 лет
		if (strtolower($data['searchMode'])=='dt6') {
			$filters[] = "
				(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) < 6)
				and pcard.Lpu_id = :Lpu_id
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$data['searchMode']='all';
		}
	
		// если ищем по ДД 14, то добавляем фильтр по дате рождения
		if (strtolower($data['searchMode'])=='dt14') {
			$filters[] = "
				(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) between 13 and 15)
			";
			$data['searchMode']='all';
		}
	
		// если ищем по регистру детей-сирот, то добавляем еще один inner join c PersonDopDisp
		if (strtolower($data['searchMode'])=='ddorp') {
			$queryParams['Year'] = ( isset($data['Year']) && (int)$data['Year'] > 1970 ) ? (int)$data['Year'] : date('Y') ;
			$join .= " inner join v_persondisporp ddorp on ddorp.Person_id=ps.Person_id and ddorp.Lpu_id = :Lpu_id and ddorp.PersonDispOrp_Year = :Year and ddorp.CategoryChildType_id <= 7";
			$data['searchMode']='all';
		}
	
		// если ищем по картам первого этапа детей-сирот, то добавляем еще один inner join c EvnPLDispOrp
		if (strtolower($data['searchMode'])=='ddorpsec') {
			$queryParams['Year'] = ( isset($data['Year']) && (int)$data['Year'] > 1970 ) ? (int)$data['Year'] : date('Y') ;
			$join .= " inner join v_EvnPLDispOrp epldorp on epldorp.Person_id=ps.Person_id and epldorp.Lpu_id = :Lpu_id and epldorp.EvnPLDispOrp_IsTwoStage = 2 and epldorp.EvnPLDispOrp_IsFinish = 2 and epldorp.DispClass_id IN (3,7) and not exists(
				select EvnPLDispOrp_id from v_EvnPLDispOrp epldorpsec where epldorpsec.EvnPLDispOrp_fid = epldorp.EvnPLDispOrp_id limit 1
			)";
			$data['searchMode']='all';
		}
	
		// периодический осмотр
		if (strtolower($data['searchMode'])=='ddorpperiod') {
			$queryParams['Year'] = ( isset($data['Year']) && (int)$data['Year'] > 1970 ) ? (int)$data['Year'] : date('Y') ;
			$join .= " inner join v_persondisporp ddorp on ddorp.Person_id=ps.Person_id and ddorp.Lpu_id = :Lpu_id and ddorp.PersonDispOrp_Year = :Year and ddorp.CategoryChildType_id = 8";
			$data['searchMode']='all';
		}
	
		// если ищем по картам первого этапа профосмотра несовершеннолетних, то добавляем еще один inner join c EvnPLDispTeenInspection
		if (strtolower($data['searchMode'])=='evnpldtipro') {
			$queryParams['Year'] = ( isset($data['Year']) && (int)$data['Year'] > 1970 ) ? (int)$data['Year'] : date('Y') ;
			$join .= " inner join v_EvnPLDispTeenInspection epldti on epldti.Person_id=ps.Person_id and epldti.Lpu_id = :Lpu_id and epldti.EvnPLDispTeenInspection_IsTwoStage = 2 and epldti.EvnPLDispTeenInspection_IsFinish = 2 and epldti.DispClass_id = 10 and not exists(
				select EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection epldtisec where epldtisec.EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id limit 1
			)";
			$data['searchMode']='all';
		}
	
		// если ищем по картам первого этапа предвосмотра несовершеннолетних, то добавляем еще один inner join c EvnPLDispTeenInspection
		if (strtolower($data['searchMode'])=='evnpldtipre') {
			$queryParams['Year'] = ( isset($data['Year']) && (int)$data['Year'] > 1970 ) ? (int)$data['Year'] : date('Y') ;
			$join .= " inner join v_EvnPLDispTeenInspection epldti on epldti.Person_id=ps.Person_id and epldti.Lpu_id = :Lpu_id and epldti.EvnPLDispTeenInspection_IsTwoStage = 2 and epldti.EvnPLDispTeenInspection_IsFinish = 2 and epldti.DispClass_id = 9 and not exists(
				select EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection epldtisec where epldtisec.EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id limit 1
			)";
			$data['searchMode']='all';
		}
	
		if (strtolower($data['searchMode'])=='wow') {
			$join .= " inner join PersonPrivilegeWOW PPW on PPW.Person_id = ps.Person_id";
			$data['searchMode']='all';
		}
	
		if ((strtolower($data['searchMode'])=='attachrecipients') &&
		(!isMinZdrav()) &&
		(!isOnko()) &&
		(!isRA()) &&
		(!isPsih()) &&
		(!isOnkoGem()) &&
		(!isGuvd())
		)
		{
			// только по льготникам и прикрепленным
			$data['searchMode']='all';
			$filters[] = "Lpu.Lpu_id = :Lpu_id";
			$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
			$filterscard = "pc.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$filterscard = "(1=1)";
		}
	
		if ((strtolower($data['searchMode'])=='withlgotonly') &&
		(!isMinZdrav()) &&
		(!isOnko()) &&
		(!isRA()) &&
		(!isPsih()) &&
		(!isOnkoGem()) &&
		(!isGuvd())
		)
		{
			// только по льготникам
			$data['searchMode']='all';
			$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
		}
	
		if ( isset( $data['ParentARM'] )
		&& ( $data['ParentARM'] == 'smpdispatchcall' || $data['ParentARM'] == 'smpadmin' || $data['ParentARM'] == 'smpdispatchdirect' )
		&& !empty( $data['PersonAge_AgeFrom'] ) && empty( $data['PersonAge_AgeTo'] )
		) {
			$filters[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) between :PersonAge_AgeFrom - 5 and :PersonAge_AgeFrom + 5)";
			$extraSelect .= ',ABS(:PersonAge_AgeFrom - (datediff(\'year\',ps.Person_BirthDay,dbo.tzGetDate())
				+ case when date_part(\'month\', ps.Person_BirthDay)>date_part(\'month\', dbo.tzGetDate())
				or (date_part(\'month\', ps.Person_BirthDay)=date_part(\'month\', dbo.tzGetDate()) and date_part(\'day\', ps.Person_BirthDay)>date_part(\'day\', dbo.tzGetDate()))
				then -1 else 0 end)) as "YearDifference"';
			$orderFirst .= 'YearDifference ASC,';
		} else {
			if ( !empty($data['PersonAge_AgeFrom']) ) {
				$filters[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= :PersonAge_AgeFrom)";
			}
	
			if ( !empty($data['PersonAge_AgeTo']) ) {
				$filters[] = "(dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) <= :PersonAge_AgeTo)";
			}
		}
	
		if ( !empty($data['PersonBirthYearFrom']) ) {
			$filters[] = "(date_part('year', ps.Person_BirthDay) >= :PersonBirthYearFrom)";
			$queryParams['PersonBirthYearFrom'] = $data['PersonBirthYearFrom'];
		}
	
		if ( !empty($data['PersonBirthYearTo']) ) {
			$filters[] = "(date_part('year', ps.Person_BirthDay) <= :PersonBirthYearTo)";
			$queryParams['PersonBirthYearTo'] = $data['PersonBirthYearTo'];
		}
	
		$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		$queryParams['PersonAge_AgeTo'] = $data['PersonAge_AgeTo'];
	
		if ( !empty($data['EvnUdost_Ser']) || !empty($data['EvnUdost_Num']) )
		{
			$join .= " inner join v_EvnUdost eu on eu.Person_id = ps.Person_id and EvnUdost_disDate is null";
			if ( !empty($data['EvnUdost_Ser']) )
			{
				$join .= " and eu.EvnUdost_Ser = :EvnUdost_Ser ";
				$queryParams['EvnUdost_Ser'] = $data['EvnUdost_Ser'];
			}
			if ( !empty($data['EvnUdost_Num']) )
			{
				$join .= " and eu.EvnUdost_Num = :EvnUdost_Num ";
				$queryParams['EvnUdost_Num'] = $data['EvnUdost_Num'];
			}
		}
	
		if ( !empty($data['PersonCard_Code']) )
		{
			$join .= " inner join PersonCardState pcard1 on pcard1.Person_id=ps.Person_id and pcard1.PersonCardState_Code = :PersonCard_Code and pcard1.Lpu_id = :Lpu_id ";
			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		}
	
		if ( !empty($data['EvnPS_NumCard']) && $data['EvnPS_NumCard'] != '' )
		{
			$join .= " inner join v_EvnPS eps1 on eps1.Person_id=ps.Person_id and rtrim(eps1.EvnPS_NumCard) = :EvnPS_NumCard and eps1.Lpu_id = :Lpu_id ";
			$queryParams['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
		}
	
		if (!empty($data['Person_id'])) {
			$filters[] = "ps.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['PersonSurName_SurName'])) {
			$filters[] = "ps.Person_SurName ILIKE :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['PersonSurName_SurName'])."%";
		}
		if (!empty($data['PersonFirName_FirName'])) {
			$filters[] = "ps.Person_FirName ILIKE :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['PersonFirName_FirName'])."%";
		}
		if (!empty($data['PersonSecName_SecName'])) {
			$filters[] = "ps.Person_SecName ILIKE :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['PersonSecName_SecName'])."%";
		}
		if (!empty($data['PersonBirthDay_BirthDay'])) {
			$filters[] = "ps.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['PersonBirthDay_BirthDay'];
		}
	
		if (!empty($data['Person_Snils'])) {
			$filters[] = "ps.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}
	
		if (!empty($data['Polis_Ser'])) {
			$filters[] = "ps.Polis_Ser = :Polis_Ser";
			$queryParams['Polis_Ser'] = $data['Polis_Ser'];
		}
	
		if (!empty($data['Polis_Num'])) {
			$filters[] = "ps.Polis_Num = :Polis_Num";
			$queryParams['Polis_Num'] = $data['Polis_Num'];
		}
		if (!empty($data['Polis_EdNum'])) {
			$filters[] = "ps.Person_edNum = :Polis_edNum";
			$queryParams['Polis_edNum'] = $data['Polis_EdNum'];
		}
	
		if( !empty($data['Sex_id']) ) {
			$filters[] = "ps.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}
	
		/**
			*  2009-04-27 [savage]
			*  Изменил определение признака "льготник" с учетом принадлежности пользователя к ЛПУ
			*/
		/**
			*  2009-07-08 [ivp]
			*  Изменил определение признака "льготник" - федеральный, региональный, региональный не своего ЛПУ
			*  Добавил определение признака 7ми нозологий
			*/
		// исходный запрос
	
		// если ищем по соц. карте, то задаем еще один жесткий джоин
		if ( isset($data['soc_card_id']) && strlen($data['soc_card_id']) >= 25  )
		{
			$queryParams['SocCardNum'] = substr($data['soc_card_id'], 0, 19);
			$join = " inner join PersonSocCardNum pscn on ps.PersonSocCardNum_id = pscn.PersonSocCardNum_id and LEFT(pscn.PersonSocCardNum_SocCardNum, 19) = :SocCardNum ";
		}
	
		$sql = "
		select
		-- select
			ps.Person_id as \"Person_id\",
			ps.Server_id as \"Server_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Person_SurName as \"PersonSurName_SurName\",
			ps.Person_FirName as \"PersonFirName_FirName\",
			ps.Person_SecName as \"PersonSecName_SecName\",
			ps.Polis_Ser as \"Polis_Ser\",
			ps.Polis_Num as \"Polis_Num\",
			ps.Person_edNum as \"Polis_EdNum\",
			dbo.Age(ps.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
			to_char(cast(ps.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"PersonBirthDay_BirthDay\",
			to_char(cast(ps.Person_deadDT as timestamp), 'dd.mm.yyyy') as \"Person_deadDT\",
			ps.Sex_id as \"Sex_id\",
			nlpu.Lpu_Nick as \"Lpu_Nick\", -- changed petrov pavel
			nlpu.Lpu_id as \"CmpLpu_id\",
			CASE WHEN coalesce(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as \"Person_isOftenCaller\",
			CASE WHEN PersonRefuse_IsRefuse = 2	THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
			CASE WHEN (Person_deadDT is not null) or (Person_IsDead = 2) THEN 'true' ELSE 'false' END as \"Person_IsDead\",
			CASE WHEN fedl.Person_id is not null then 'true' else 'false' end as \"Person_IsFedLgot\",
			CASE WHEN regl.OwnLpu = 1 THEN 'true' ELSE CASE WHEN regl.OwnLpu is not null THEN 'gray' ELSE 'false' END END as \"Person_IsRegLgot\",
			CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END END as \"Person_Is7Noz\",
			CASE WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(to_char(dbo.tzGetDate(), 'yyyyddmm') as timestamp)
				THEN 'yellow'
				ELSE CASE WHEN ps.PersonCloseCause_id = 2 and Person_closeDT is not null
					THEN 'red'
					ELSE CASE WHEN ps.Server_pid = 0
						THEN 'true'
						ELSE 'false'
					END
				END
			END as \"Person_IsBDZ\",
			CASE WHEN exists (
				select
					PersonCard_id
				from
					v_PersonCard
				where
					Person_id = ps.Person_id
					and LpuAttachType_id = 5
					and PersonCard_endDate >= dbo.tzGetDate()
					and CardCloseCause_id is null
			)
				THEN 'true'
				ELSE 'false'
			END as \"PersonCard_IsDms\"
			{$extraSelect}
		-- end select
		from
		-- from
			v_PersonState ps
			{$join}
			left join Polis pls on pls.Polis_id = ps.Polis_id
			left join lateral(
				select
					pc.Person_id as PersonCard_Person_id,
					pc.Lpu_id
				from v_PersonCard pc
				where
					pc.Person_id = ps.Person_id
					and LpuAttachType_id = 1
					and {$filterscard}
				order by PersonCard_begDate desc
				limit 1
			) as pcard on true
			LEFT JOIN v_Lpu lpu on pcard.Lpu_id=lpu.Lpu_id
			LEFT JOIN v_Lpu nlpu on ps.Lpu_id = nlpu.Lpu_id -- Petrov	Pavel
			LEFT JOIN v_PersonRefuse ON v_PersonRefuse.Person_id = ps.Person_id and PersonRefuse_IsRefuse = 2 and PersonRefuse_Year = date_part('year', dbo.tzGetDate())
			left join lateral(
				select Person_id
				from personprivilege pp
					left join PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pp.person_id = ps.person_id
					--and pp.privilegetype_id <= 249
					and pt.ReceptFinance_id = 1
					and pp.personprivilege_begdate <= dbo.tzGetDate()
					and (pp.personprivilege_enddate is null or pp.personprivilege_enddate >= cast(to_char(dbo.tzGetDate(), 'yyyyddmm') as timestamp))
			limit 1
			) as fedl on true
			left join lateral(
				select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
				from personprivilege pp
					left join PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pp.person_id = ps.person_id
					and pt.ReceptFinance_id = 2
					and pp.personprivilege_begdate <= dbo.tzGetDate()
					and (pp.personprivilege_enddate is null or pp.personprivilege_enddate >= cast(to_char(dbo.tzGetDate(), 'yyyyddmm') as timestamp))
			) as regl on true
			left join lateral(
				select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
				from v_PersonDisp
				where
					Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > dbo.tzGetDate())
					and Sickness_id IN (1,3,4,5,6,7,8)
			) as disp on true
			LEFT JOIN v_OftenCallers OC on ps.Person_id = OC.Person_id
		-- end from
		".ImplodeWherePH($filters)."
		order by
		-- order by
			{$orderFirst}
			ps.Person_SurName ASC,
			ps.Person_FirName ASC,
			Person_SecName ASC
		-- end order by
		limit 1000
		";
	
		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 )
		{
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
	}
		$res=$this->db->query($sql, $queryParams);
		// определение общего количества записей
			$count_res=$this->db->query($count_sql, $queryParams);
	
		if ( is_object($count_res) )
			{
			$cnt_arr = $count_res->result('array');
					$count = $cnt_arr[0]['cnt'];
	}
					else
					return false;
	
		if ( is_object($res) )
				{
					$response = $res->result('array');
					$response[] = array('__countOfAllRows' => $count);
				return $response;
	}
	else
		return false;
	}
	/**
	 * Некая абстрактная функция TODO: описать
	 */	
	function savePersonEditWindow($data)
    {
    	$ret = $this->savePersonEditWindowSamara($data);
    	
    	//print_r($ret);

    	if (!$ret[0]['Error_Msg']){
    		$sql="
				UPDATE PersonState
				SET Lpu_id = :AttachLpu_id
				WHERE Person_id = :Person_id
			";
    		$res = $this->db->query($sql, array( 'AttachLpu_id' => $data['AttachLpu_id'], 'Person_id' => $ret[0]['Person_id']));
	   	}
    	
    	return $ret;
    }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
    function checkPolisIntersection($data, $attr=false)
	{
		return;
	}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function savePersonEditWindowSamara($data)
	{
		$is_superadmin = false;
		$is_ufa = false;
		$is_samara = false;
		$is_kareliya = false;
		$is_saratov = false;
		$is_astra = false;
	
		$person_is_identified = false;
	
		$this->load->library('textlog', array('file'=>'Person_save.log'));
	
		if ( preg_match("/SuperAdmin/", $data['session']['groups']) ) {
			$is_superadmin = true;
		}
	
		if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa' ) {
			$is_ufa = true;
		}
	
		if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'samara' ) {
			$is_samara = true;
		}
	
		if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'astra' ) {
			$is_astra = true;
		}
	
		if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'kareliya' ) {
			$is_kareliya = true;
		}
	
		if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'saratov' ) {
			$is_saratov = true;
		}
	
		if ( empty($data['PersonIdentState_id']) || !in_array(intval($data['PersonIdentState_id']), array(1, 2, 3)) ) {
			$data['PersonIdentState_id'] = 0;
		}
	
		if ( ($is_ufa === true || $is_kareliya === true) && $data['PersonIdentState_id'] != 0 && !empty($data['Person_identDT']) ) {
			$person_is_identified = true;
		}
	
		if ( $data['mode'] != 'add' )
		{
			// оставим только изменившиеся поля
			$oldValues = explode('&', urldecode($data['oldValues']));
			$newFields = array();
			foreach ($oldValues as $oldValue)
			{
				$val = explode('=', $oldValue);
				$fieldVal = "";
				$flag = false;
				foreach ($val as $item)
				{
					// первый пропускаем
					if (!$flag)
						$flag = true;
					else
						$fieldVal .= $item;
				}
				$item = iconv('utf-8', 'windows-1251', $item) ;
				if ($val[0] == 'Person_BirthDay' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}
				if ($val[0] == 'Document_begDate' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}
				/*if ($val[0] == 'PAddress_begDate' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}
				if ($val[0] == 'UAddress_begDate' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}*/
				if ($val[0] == 'Polis_begDate' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}
				if ($val[0] == 'Polis_endDate' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}
				if ($val[0] == 'PersonChild_invDate' && $item != '') {
					$item = date('Y-m-d', strtotime($item));
				}
				if ( array_key_exists($val[0], $data) ) {
					if (in_array($val[0],array('Person_SurName', 'Person_FirName', 'Person_SecName'))) {
						if ( strtoupper(trim((string)$data[$val[0]])) !== strtoupper(trim((string)$item)) ) {
							$newFields[$val[0]] = $item;
						}
					} else {
						if ( trim((string)$data[$val[0]]) !== trim((string)$item) ) {
							$newFields[$val[0]] = $item;
						}
					}
				}
			}
			unset($data['oldValues']);
			$pid = $data['Person_id'];
				
			// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан,
			// чтобы лишний раз в сессию не писать, экономим на спичках
			if (session_id() == '') session_start();
			if ( isset($data['session']['person']) && isset($data['session']['person']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person']['Person_id'] )
				unset($_SESSION['person']);
	
			if ( isset($data['session']['person_short']) && isset($data['session']['person_short']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person_short']['Person_id'] )
				unset($_SESSION['person_short']);
			session_write_close();
		}
		//foreach ($data as $key=>$value)
		//if ( !empty($data[$key]) )
		//$data[$key] = iconv('utf-8', 'windows-1251', trim($data[$key]));
	
		if ( $data['mode'] == 'add' )
		{
			$newFields = $data;
			foreach ($newFields as $key => $value)
			if ( empty($value) )
				unset($newFields[$key]);
		}
		$server_id = $data['Server_id'];
	
		$flBDZ=false;
		$arr=array(
				'Person_SurName',
				'Person_FirName',
				'Person_SecName',
				'Person_BirthDay',
				"PolisType",
				"OMSSprTerr_id",
				"Polis_Ser",
				"Polis_num",
				"Federal_Num",
				"OrgSMO_id",
				"Polis_begDate",
				"Polis_endDate"
		);
		foreach ($arr as $value) {
			if(array_key_exists($value,$newFields)){
				$flBDZ=true;
				break;
			}
		}
		// новая хранимка p_PersonAll_ins вызывается только при добавлении людей или обновления как минимум ФИО и ДР, иначе вызываются поштучные хранимки, так же вызывается  если регион Карелия изменено одно из полей ФИО ИЛИ ДР ИЛИ ПОЛИС.
		if ( ($data['mode'] == 'add') || ($flBDZ == true && ($is_kareliya==true||$is_ufa==true)) || (array_key_exists('Person_SurName', $newFields) && array_key_exists('Person_FirName', $newFields) && array_key_exists('Person_SecName', $newFields) && array_key_exists('Person_BirthDay', $newFields)) ) {
				
			$queryParams = array(
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $person_is_identified?0:$data['Server_id'],
					'Person_id' => $data['Person_id']
			);
			$query = "
				select
					Person_id as \"Pid\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PersonAll_ins(
					Person_id := :Person_id
					,Server_id := :Server_id
				)
			";
			if ( $person_is_identified ) {
				$query .= "
					,Person_identDT := :Person_identDT
					,PersonIdentState_id := :PersonIdentState_id
				";
				$queryParams['Person_identDT'] = date('Y-m-d', $data['Person_identDT']);
				$queryParams['PersonIdentState_id'] = $data['PersonIdentState_id'];
			}
			if ( array_key_exists('Person_SurName', $newFields) )
			{
				$query .= "
					,PersonSurName_SurName := :PersonSurName_SurName
				";
				$queryParams['PersonSurName_SurName'] = $data['Person_SurName'];
			}
			if ( array_key_exists('Person_FirName', $newFields) )
			{
				$query .= "
					,PersonFirName_FirName := :PersonFirName_FirName
				";
				$queryParams['PersonFirName_FirName'] = $data['Person_FirName'];
			}
			if ( array_key_exists('Person_SecName', $newFields) )
			{
				$query .= "
					,PersonSecName_SecName := :PersonSecName_SecName
				";
				$queryParams['PersonSecName_SecName'] = $data['Person_SecName'];
			}
			if ( array_key_exists('Person_BirthDay', $newFields) )
			{
				$query .= "
					,PersonBirthDay_BirthDay := :PersonBirthDay_BirthDay
				";
				$queryParams['PersonBirthDay_BirthDay'] = $data['Person_BirthDay'];
			}
			if ( array_key_exists('PersonSex_id', $newFields) )
			{
				$query .= "
					,Sex_id := :Sex_id
				";
				$queryParams['Sex_id'] = $data['PersonSex_id'];
			}
			if ( array_key_exists('Person_SNILS', $newFields) )
			{
				$query .= "
					,PersonSnils_Snils := :PersonSnils_Snils
				";
				$queryParams['PersonSnils_Snils'] = preg_replace("/[ \-]+/", "", $data['Person_SNILS']);
			}
			if ( array_key_exists('SocStatus_id', $newFields) )
			{
				$query .= "
					,SocStatus_id := :SocStatus_id
				";
				$queryParams['SocStatus_id'] = $data['SocStatus_id'];
			}
			if ( array_key_exists('Federal_Num', $newFields) )
			{
				$query .= "
					,PersonPolisEdNum_EdNum := :PersonPolisEdNum_EdNum
				";
				$queryParams['PersonPolisEdNum_EdNum'] = $data['Federal_Num'];
			}
			if ( array_key_exists('PersonPhone_Phone', $newFields) )
			{
				$query .= "
					,PersonPhone_Phone := :PersonPhone_Phone
				";
				$queryParams['PersonPhone_Phone'] = $data['PersonPhone_Phone'];
			}
			if ( array_key_exists('PersonInn_Inn', $newFields) )
			{
				$query .= "
					,PersonInn_Inn := :PersonInn_Inn
				";
				$queryParams['PersonInn_Inn'] = $data['PersonInn_Inn'];
			}
			if ( (isSuperadmin() || $person_is_identified) && array_key_exists('PersonSocCardNum_SocCardNum', $newFields) )
			{
				$query .= "
					,PersonSocCardNum_SocCardNum := :PersonSocCardNum_SocCardNum
				";
				$queryParams['PersonSocCardNum_SocCardNum'] = $data['PersonSocCardNum_SocCardNum'];
			}
			if ( array_key_exists('FamilyStatus_id', $newFields) ) {
				$query .= "
					,FamilyStatus_id := :FamilyStatus_id
				";
				$queryParams['FamilyStatus_id'] = $data['FamilyStatus_id'];
			}
			if ( array_key_exists('PersonFamilyStatus_IsMarried', $newFields) )
			{
				$query .= "
					,PersonFamilyStatus_IsMarried := :PersonFamilyStatus_IsMarried
				";
				$queryParams['PersonFamilyStatus_IsMarried'] = $data['PersonFamilyStatus_IsMarried'];
			}
				
			$query .= "
					,pmUser_id := :pmUser_id
				)
			";
				
			// echo getDebugSql($query, $queryParams); die();
			$res = $this->db->query($query, $queryParams);
			if ( !is_object($res) )
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
			$rows = $res->result('array');
				
			if ( !is_array($rows) || count($rows) == 0 ) {
				return array('success' => false, 'Error_Msg' => 'Ошибки сохранения человека');
			}
			else if ( !empty($rows[0]['Error_Msg']) ) {
				return array('success' => false, 'Error_Msg' => $rows[0]['Error_Msg']);
			}
			$pid = $rows[0]['Pid'];
				
			if ( $data['mode'] == 'add' )
			{
				// проверяем, есть ли человеку 3 года
				if ( !empty($data['Person_BirthDay']) )
				{
					//$birthday = strtotime(ConvertDateFormat(trim($data['Person_BirthDay'])));
					$birthday = strtotime($data['Person_BirthDay']);
					if ( $is_kareliya === false && strtotime("+3 year", $birthday) > time() )
					{
						// добавляем льготу
						$this->load->database();
						$CI =& get_instance();
						$CI->load->model('Privilege_model','ppmodel',true);
						$model =& $CI->ppmodel;
	
						$priv_data = array();
	
						$priv_data['PrivilegeType_id'] = $model->getPrivilegeTypeIdBySysNick('child_und_three_year', date('Y-m-d'));
	
						if ( $priv_data['PrivilegeType_id'] === false ) {
							return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении льготы (получение идентификатора категории льготы)'));
						}
	
						$priv_data['Lpu_id'] = $data['Lpu_id'];
						$priv_data['pmUser_id'] = $data['pmUser_id'];
						$priv_data['PersonPrivilege_id'] = 0;
						$priv_data['Person_id'] = $pid;
						$priv_data['Server_id'] = ($person_is_identified === true ? 0 : $data['Server_id']);
						$priv_data['Privilege_begDate'] = date("Y-m-d", $birthday);
						$priv_data['Privilege_endDate'] = date("Y-m-d",strtotime("+3 year", $birthday) - 60*60*24);
						$priv_data['session'] = $data['session'];
						$res = $model->savePrivilege($priv_data);
						if ( count($res) > 0 )
						{
							if ( isset($res[0]) )
							{
								if ( isset($res[0]['success']) && $res[0]['success'] == false )
								{
									//$this->db->trans_rollback();
									return $res;
								}
							}
							else
							{
								//$this->db->trans_rollback();
								return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при добавлении льготы'));
							}
						}
						else
						{
							//$this->db->trans_rollback();
							return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при добавлении льготы'));
						}
					}
				}
			}
		} else {
			// если не поменялось фио и др и это не добавление человека, то старый код..
			if ( $person_is_identified ) {
				// Проставляем человеку признак "из БДЗ"
				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_Person_server(
						Person_id := :Person_id,
						Server_id := :Server_id,
						pmUser_id := :pmUser_id
					)
				";
				$res = $this->db->query($sql, array(
						'Person_id' => $pid,
						'Server_id' => 0,
						'pmUser_id' => $data['pmUser_id']
				));
	
				if ( !is_object($res) ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)'));
				}
	
				$response = $res->result('array');
	
				if ( !is_array($response) || count($response) == 0 ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при проставлении признака идентификации по сводной базе застрахованных'));
				}
	
				if ( !empty($response[0]['Error_Msg']) ) {
					return array(array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']));
				}
	
				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_Person_ident(
						Person_id := :Person_id,
						Person_identDT := :Person_identDT,
						PersonIdentState_id := :PersonIdentState_id,
						pmUser_id := :pmUser_id
					)
				";
				$res = $this->db->query($sql, array(
						'Person_id' => $pid,
						'Person_identDT' => date('Y-m-d', $data['Person_identDT']),
						'PersonIdentState_id' => $data['PersonIdentState_id'],
						'pmUser_id' => $data['pmUser_id']
				));
	
				if ( !is_object($res) ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (обновление данных об идентификации по сводной базе застрахованных)'));
				}
	
				$response = $res->result('array');
	
				if ( !is_array($response) || count($response) == 0 ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при обновлении данных об идентификации по сводной базе застрахованных'));
				}
	
				if ( !empty($response[0]['Error_Msg']) ) {
					return array(array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']));
				}
			}
				
			//Изменилась фамилия
			if ( array_key_exists('Person_SurName', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonSurName_ins(
						Server_id := ?,
						Person_id := ?,
						PersonSurName_SurName := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_SurName'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
			//Изменилось имя
			if ( array_key_exists('Person_FirName', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonFirName_ins(
						Server_id := ?,
						Person_id := ?,
						PersonFirName_FirName := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_FirName'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
	
			//Изменилось отчество
			if ( array_key_exists('Person_SecName', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonSecName_ins(
						Server_id := ?,
						Person_id := ?,
						PersonSecName_SecName := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_SecName'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
				
			// Изменился номер соц. карты
			if ( (isSuperadmin() || $person_is_identified) && array_key_exists('PersonSocCardNum_SocCardNum', $newFields) )
			{
				// для прав суперадмина
				$serv_id = 0;
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonSocCardNum_ins(
						Server_id := ?,
						Person_id := ?,
						PersonSocCardNum_SocCardNum := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonSocCardNum_SocCardNum'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
				
			// Изменился номер телефона
			if ( array_key_exists('PersonPhone_Phone', $newFields) )
			{
				// для прав суперадмина
				$serv_id = 0;
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonPhone_ins(
						Server_id := ?,
						Person_id := ?,
						PersonPhone_Phone := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonPhone_Phone'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
				
			// Изменился INN
			if ( array_key_exists('PersonInn_Inn', $newFields) )
			{
				// для прав суперадмина
				$serv_id = 0;
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonInn_ins(
						Server_id := ?,
						Person_id := ?,
						PersonInn_Inn := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonInn_Inn'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
				
			//Изменилась дата рождения
			if ( array_key_exists('Person_BirthDay', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$date = empty($data['Person_BirthDay'])?NULL:$data['Person_BirthDay'];
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonBirthDay_ins(
						Server_id := ?,
						Person_id := ?,
						PersonBirthDay_BirthDay := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
						array($serv_id, $pid, $date, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
	
			//Изменился СНИЛС
			if ( array_key_exists('Person_SNILS', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 1;
				}
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonSnils_ins(
						Server_id := ?,
						Person_id := ?,
						PersonSnils_Snils := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
						array($serv_id, $pid, preg_replace("/[ \-]+/", "", $data['Person_SNILS']), $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
	
			//Изменился пол. o_O фигасе!
			if ( array_key_exists('PersonSex_id', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$Sex_id = (!isset($data['PersonSex_id'])||!is_numeric($data['PersonSex_id'])?NULL:$data['PersonSex_id']);
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonSex_ins(
						Server_id := ?,
						Person_id := ?,
						Sex_id := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
						array($serv_id, $pid, $Sex_id, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
	
			//Изменился социальный статус
			if ( array_key_exists('SocStatus_id', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$SocStatus_id = (empty($data['SocStatus_id'])?NULL:$data['SocStatus_id']);
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonSocStatus_ins(
						Server_id := ?,
						Person_id := ?,
						SocStatus_id := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
						array($serv_id, $pid, $SocStatus_id, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
				
			// изменилось семейное положение
			if ( array_key_exists('FamilyStatus_id', $newFields) || array_key_exists('PersonFamilyStatus_IsMarried', $newFields) )
			{
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ( $is_superadmin || $person_is_identified )
				{
					$serv_id = 0;
				}
	
				$FamilyStatus_id = (empty($data['FamilyStatus_id'])?NULL:$data['FamilyStatus_id']);
				$PersonFamilyStatus_IsMarried = (empty($data['PersonFamilyStatus_IsMarried'])?NULL:$data['PersonFamilyStatus_IsMarried']);
	
				if ( empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id) ) {
					return array(array('success' => false, 'Error_Msg' => 'Хотя бы одно из полей "Семейное положение" или "Состоит в зарегистрированном браке" должно быть заполнено'));
				}
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonFamilyStatus_ins(
						Server_id := ?,
						Person_id := ?,
						FamilyStatus_id := ?,
						PersonFamilyStatus_IsMarried := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
						array($serv_id, $pid, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
	
			//Изменился единый номер
			if ( array_key_exists('Federal_Num', $newFields) )
			{
				$Federal_Num = (empty($data['Federal_Num'])?'':$data['Federal_Num']);
	
				$serv_id = $server_id;
				// для прав суперадмина
				if ( $is_superadmin )
				{
					$serv_id = 0;
				}
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonPolisEdNum_ins(
						Server_id := ?,
						Person_id := ?,
						PersonPolisEdNum_EdNum := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
						array($serv_id, $pid, $Federal_Num, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
				
		}
	
		// изменился родитель
		if ( array_key_exists('DeputyKind_id', $newFields) || array_key_exists('DeputyPerson_id', $newFields) )
		{
			if ( isset($data['DeputyKind_id']) && isset($data['DeputyPerson_id']) )
			{
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonDeputy_del(
						PersonDeputy_id := (select PersonDeputy_id from PersonDeputy where Person_id = ? limit 1)
					)
				";
				$res = $this->db->query($sql,
						array($pid));
	
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonDeputy_ins(
						Server_id := ?,
						Person_id := ?,
						Person_pid := ?,
						DeputyKind_id := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($server_id, $pid, $data['DeputyPerson_id'], $data['DeputyKind_id'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
			else
			{
				// если ни один не задан, то удаляем
				if ( !isset($data['DeputyKind_id']) && !isset($data['DeputyPerson_id']) )
				{
					$sql="
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"ErrMsg\"
						from p_PersonDeputy_del(
							PersonDeputy_id := (select PersonDeputy_id from PersonDeputy where Person_id = ? limit 1)
						)
					";
					$res = $this->db->query($sql,
							array($pid));
				}
			}
		}
	
		// Изменилась национальность, этническая группа, территория адреса проживания или адреса регистрации для Уфы
		// http://redmine.swan.perm.ru/issues/22988
		if (
		array_key_exists('PersonNationality_id', $newFields)
		|| array_key_exists('Ethnos_id', $newFields)
		|| (($is_ufa === true || $is_samara === true) && array_key_exists('PPersonSprTerrDop_id', $newFields))
		|| (($is_ufa === true || $is_samara === true) && array_key_exists('UPersonSprTerrDop_id', $newFields))
		) {
			//проверяем, есть ли уже запись на этого персона в этой таблице
			$sql = "
				select
					 PersonInfo_id as \"PersonInfo_id\"
					,PersonInfo_InternetPhone as \"PersonInfo_InternetPhone\"
				from
					PersonInfo
				where
					Person_id = :Person_id
				order by PersonInfo_updDT desc
				limit 1
			";
			$res = $this->db->query($sql, array('Person_id' => $pid));
	
			if ( is_object($res) ) {
				$rows = $res->result('array');
	
				if ( !is_array($rows) || count($rows) == 0 ) {
					$rows = array(array(
							'PersonInfo_id' => NULL
							,'PersonInfo_InternetPhone' => NULL
					));
	
					$procedure = 'p_PersonInfo_ins';
				}
				else {
					$procedure = 'p_PersonInfo_upd';
				}
	
				// выполняем хранимку
				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from " . $procedure . "(
						Server_id := :Server_id,
						PersonInfo_id := :PersonInfo_id,
						Person_id := :Person_id,
						UPersonSprTerrDop_id := :UPersonSprTerrDop_id,
						PPersonSprTerrDop_id := :PPersonSprTerrDop_id,
						PersonInfo_InternetPhone := :PersonInfo_InternetPhone,
						Nationality_id := :Nationality_id,
						Ethnos_id := :Ethnos_id,
						pmUser_id := :pmUser_id
					)
				";
				$res = $this->db->query($sql, array(
						'Server_id' => $server_id,
						'PersonInfo_id' => $rows[0]['PersonInfo_id'],
						'Person_id' => $pid,
						'UPersonSprTerrDop_id' => (!empty($data['UPersonSprTerrDop_id']) ? $data['UPersonSprTerrDop_id'] : NULL),
						'PPersonSprTerrDop_id' => (!empty($data['PPersonSprTerrDop_id']) ? $data['PPersonSprTerrDop_id'] : NULL),
						'PersonInfo_InternetPhone' => $rows[0]['PersonInfo_InternetPhone'],
						'Nationality_id' => (!empty($data['PersonNationality_id']) ? $data['PersonNationality_id'] : NULL),
						'Ethnos_id' => (!empty($data['Ethnos_id']) ? $data['Ethnos_id'] : NULL),
						'pmUser_id' => $data['pmUser_id']
				));
				$this->ValidateInsertQuery($res);
			}
		}
	
		// Изменилось поле "Отказ от льготы"
		if ( isSuperadmin() && array_key_exists('PersonRefuse_IsRefuse', $newFields) )
		{
			if ( isset($data['PersonRefuse_IsRefuse']) )
			{
				// для прав суперадмина
				$serv_id = 0;
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonRefuse_ins(
						Person_id := ?,
						PersonRefuse_IsRefuse := ?,
						PersonRefuse_Year := date_part('year', dbo.tzGetDate()),
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql, array($pid, $data['PersonRefuse_IsRefuse'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
		}
	
		// Изменился рост
		if ( array_key_exists('PersonHeight_Height', $newFields) || array_key_exists('HeightAbnormType_id', $newFields) || array_key_exists('PersonHeight_IsAbnorm', $newFields) )
		{
			$serv_id = $server_id;
			// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
			$serv_id = 0;
			$ins_dt = date('Y-m-d H:i:s', time());
				
			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonHeight_ins(
					Server_id := ?,
					Person_id := ?,
					PersonHeight_setDT := ?,
					PersonHeight_Height := ?,
					HeightAbnormType_id := ?,
					PersonHeight_IsAbnorm := ?,
					Okei_id := 2,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonHeight_Height'], $data['HeightAbnormType_id'], $data['PersonHeight_IsAbnorm'], $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
	
		// Изменился вес
		if ( array_key_exists('PersonWeight_Weight', $newFields) || (array_key_exists('Okei_id', $newFields) && !empty($data['PersonWeight_Weight'])) || array_key_exists('WeightAbnormType_id', $newFields) || array_key_exists('PersonWeight_IsAbnorm', $newFields) )
		{
			$serv_id = $server_id;
			// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
			$serv_id = 0;
			$ins_dt = date('Y-m-d H:i:s', time());
				
			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonWeight_ins(
					Server_id := ?,
					Person_id := ?,
					PersonWeight_setDT := ?,
					PersonWeight_Weight := ?,
					WeightAbnormType_id := ?,
					PersonWeight_IsAbnorm := ?,
					WeightMeasureType_id := 3,
					Okei_id := ?,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonWeight_Weight'], $data['WeightAbnormType_id'], $data['PersonWeight_IsAbnorm'], $data['Okei_id'], $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
	
		// Изменилось поле "Есть дети до 16-ти"
		if ( array_key_exists('PersonChildExist_IsChild', $newFields) )
		{
			$PersonChildExist_IsChild = (empty($data['PersonChildExist_IsChild'])?NULL:$data['PersonChildExist_IsChild']);
			$serv_id = $server_id;
			// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
			$serv_id = 0;
			$ins_dt = date('Y-m-d H:i:s', time());
				
			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonChildExist_ins(
					Server_id := ?,
					Person_id := ?,
					PersonChildExist_setDT := ?,
					PersonChildExist_IsChild := ?,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $PersonChildExist_IsChild, $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
	
		// Изменилось поле "Есть автомобиль"
		if ( array_key_exists('PersonCarExist_IsCar', $newFields) )
		{
			$PersonCarExist_IsCar = (empty($data['PersonCarExist_IsCar'])?NULL:$data['PersonCarExist_IsCar']);
	
			$serv_id = $server_id;
			// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
			$serv_id = 0;
			$ins_dt = date('Y-m-d H:i:s', time());
				
			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonCarExist_ins(
					Server_id := ?,
					Person_id := ?,
					PersonCarExist_setDT := ?,
					PersonCarExist_IsCar := ?,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $PersonCarExist_IsCar, $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
	
		}
	
		// Изменились поля "Социально-профессиональная группа" или "Этническая группа"
		if ( array_key_exists('Ethnos_id', $newFields) || array_key_exists('OnkoOccupationClass_id', $newFields) )
		{
			$Ethnos_id = (empty($data['Ethnos_id']) ? NULL : $data['Ethnos_id']);
			$OnkoOccupationClass_id = (empty($data['OnkoOccupationClass_id']) ? NULL : $data['OnkoOccupationClass_id']);
			$MorbusOnkoPerson_id = NULL;
			$proc = "p_MorbusOnkoPerson_ins";
				
			// получить предыдущий MorbusOnkoPerson_id, если есть его обновить, иначе добавить
			$sql="
				select
					MorbusOnkoPerson_id as \"MorbusOnkoPerson_id\"
				from
					v_MorbusOnkoPerson
				where
					Person_id = ?
				order by
					MorbusOnkoPerson_insDT desc
				limit 1
			";
			$res = $this->db->query($sql, array($pid));
			if (is_object($res))
			{
				$resp = $res->result('array');
				if (count($resp) > 0) {
					$MorbusOnkoPerson_id = $resp[0]['MorbusOnkoPerson_id'];
					$proc = "p_MorbusOnkoPerson_upd";
				}
			}
				
			$sql="
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"ErrMsg\"
			from {$proc}(
				Person_id := ?,
				Ethnos_id := ?,
				OnkoOccupationClass_id := ?,
				pmUser_id := ?,
				MorbusOnkoPerson_id := ?
			)
			";
			$res = $this->db->query($sql, array($pid, $Ethnos_id, $OnkoOccupationClass_id, $data['pmUser_id'], $MorbusOnkoPerson_id));
					$this->ValidateInsertQuery($res);
	
		}
	
		if ( array_key_exists('Post_id', $newFields) || (isset($data['PostNew']) && !empty($data['PostNew'])) || array_key_exists('Org_id', $newFields) || array_key_exists('OrgUnion_id', $newFields) || (!empty($data['OrgUnionNew'])) )
		{
			$Post_id = (empty($data['Post_id'])?NULL:$data['Post_id']);
			$Org_id = (empty($data['Org_id'])?NULL:$data['Org_id']);
			$OrgUnion_id = (empty($data['OrgUnion_id'])?NULL:$data['OrgUnion_id']);
	
	
			// POST может быть добавлен
			if ( isset($data['PostNew']) && !empty($data['PostNew']))
				{
	
				$post_new = $data['PostNew'];
	
				if (is_numeric($post_new)){
	
						$numPostID = 1;
	
						$sql = "
						select
							Post_id as \"Post_id\"
						from v_Post
						where
                            Post_id = ?
                    ";
                    $result = $this->db->query($sql, array($post_new));
	
                } else {
	
                    $sql = "
                        select
							Post_id as \"Post_id\"
						from
							v_Post
						where
							Post_Name ILIKE ?
							and Server_id = ?
					";
					$result = $this->db->query($sql, array($post_new, $server_id));
	
                }
	
				if (is_object($result))
				{
					$sel = $result->result('array');
					if ( isset( $sel[0] ) )
					{
						if ( $sel[0]['Post_id'] > 0 )
							$Post_id = $sel[0]['Post_id'];
	
					} else if (isset($numPostID)){
										$Post_id = null;
					} else {
						$sql = "
							select
								Post_id as \"Post_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"ErrMsg\"
							from p_Post_ins(
								Post_Name := ?,
								pmUser_id := ?,
								Server_id := ?
							)
						";
						$result = $this->db->query($sql, array($post_new, $data['pmUser_id'], $server_id));
						if (is_object($result))
						{
							$sel = $result->result('array');
							if ( $sel[0]['Post_id'] > 0 )
								$Post_id = $sel[0]['Post_id'];
						}
					}
				}
			}
	
		
			// OrgUnion может быть добавлен
			if (isset($data['OrgUnionNew']) && !empty($data['OrgUnionNew']) && !empty($data['Org_id']) && is_numeric($data['Org_id'])) {
				$org_union_new = $data['OrgUnionNew'];
				if (is_numeric($org_union_new)) {
					$numOrgUnionID = 1;
					$sql = "
						select
							OrgUnion_id as \"OrgUnion_id\"
						from
						v_OrgUnion
						where
						OrgUnion_id = ?
					";
					$result = $this->db->query($sql, array($org_union_new));
				} else {
					$sql = "
                        select
                            OrgUnion_id as \"OrgUnion_id\"
                        from
                            v_OrgUnion
                        where
							OrgUnion_Name ILIKE ?
							and Server_id = ?
							and Org_id = ?
					";
					$result = $this->db->query($sql, array($org_union_new, $server_id, $data['Org_id']));

				}

				if (is_object($result)) {
					$sel = $result->result('array');
					if (isset($sel[0])) {
						if ($sel[0]['OrgUnion_id'] > 0)
							$OrgUnion_id = $sel[0]['OrgUnion_id'];

					} else if (isset($numOrgUnionID)) {
						$OrgUnion_id = null;
					} else {
						$sql = "
							select
								OrgUnion_id as \"OrgUnion_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"ErrMsg\"
							from p_OrgUnion_ins(
								OrgUnion_Name := ?,
								Org_id := ?,
								pmUser_id := ?,
								Server_id := ?
							)
						";
						$result = $this->db->query($sql, array($org_union_new, $data['Org_id'], $data['pmUser_id'], $server_id));
						if (is_object($result)) {
							$sel = $result->result('array');
							if ($sel[0]['OrgUnion_id'] > 0)
								$OrgUnion_id = $sel[0]['OrgUnion_id'];
						}
					}
				}
			}

			if (!isset($Org_id)) {
				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonJob_del(
						Server_id := ?,
						Person_id := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
					array($server_id, $pid, $data['pmUser_id']));
			} else {


				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonJob_ins(
						Server_id := ?,
						Person_id := ?,
						Org_id := ?,
						OrgUnion_id := ?,
						Post_id := ?,
						pmUser_id := ?
					)
				";

				$res = $this->db->query($sql,
					array($server_id, $pid, $Org_id, $OrgUnion_id, $Post_id, $data['pmUser_id']));
			}

			$this->ValidateInsertQuery($res);

		}
	
		//Изменились атрибуты полиса
		if (array_key_exists('OMSSprTerr_id', $newFields) || array_key_exists('PolisType_id', $newFields) ||
	array_key_exists('Polis_Ser', $newFields) ||
	array_key_exists('Polis_Num', $newFields) || array_key_exists('OrgSMO_id', $newFields) ||
	array_key_exists('Polis_begDate', $newFields) || array_key_exists('Polis_endDate', $newFields))
	{
			$OmsSprTerr_id = (empty($data['OMSSprTerr_id'])?NULL:$data['OMSSprTerr_id']);
			$PolisType_id = (empty($data['PolisType_id'])?NULL:$data['PolisType_id']);
			$OrgSmo_id = (empty($data['OrgSMO_id'])?NULL:$data['OrgSMO_id']);
			$Polis_Ser = (empty($data['Polis_Ser'])?'':$data['Polis_Ser']);
			$Polis_Num = (empty($data['Polis_Num'])?'':$data['Polis_Num']);
			$Polis_begDate = empty($data['Polis_begDate'])?NULL:$data['Polis_begDate'];
			$Polis_endDate = empty($data['Polis_endDate'])?NULL:$data['Polis_endDate'];
		
			// если не указана территория, но указаны другие данные о полисе, очищаем другие данные и делаем пометку в логе. (refs #16940)
			if ($is_ufa && empty($OmsSprTerr_id) && (!empty($PolisType_id) || !empty($OrgSmo_id) || !empty($Polis_Ser) || !empty($Polis_Num) || !empty($Polis_begDate) || !empty($Polis_endDate))) {
				$this->textlog->add("Соханение человека с полисными данными без указанной территории страхования. Person_id: {$pid}, PolisType_id: {$PolisType_id}, OrgSmo_id: {$OrgSmo_id}, Polis_Ser: {$Polis_Ser}, Polis_Num: {$PolisType_id}, Polis_Num: {$PolisType_id}, Polis_begDate: {$Polis_begDate}, Polis_endDate: {$Polis_endDate}" );
		
				$PolisType_id = NULL;
				$OrgSmo_id = NULL;
				$Polis_Ser = '';
				$Polis_Num = '';
				$Polis_begDate = NULL;
				$Polis_endDate = NULL;
		}
			
			$serv_id = $server_id;
			// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
			if ( $is_superadmin || $person_is_identified )
			{
				$serv_id = 0;
		}
	
			// если человек из БДЗ и можем добавлять иннотериториальный полис
			if ( !$is_astra && !$is_superadmin && !$person_is_identified  && isset($data['Polis_CanAdded']) && $data['Polis_CanAdded'] == 1 )
		{
				// проверяем, иная ли территория
				$sql = "
					select
						KLRgn_id as \"KLRgn_id\"
					from
						OMSSprTerr
					where
						OMSSprTerr_id = ?
				";
				$res = $this->db->query($sql,
					array($OmsSprTerr_id));
				$sel = $res->result('array');
				if ( count($sel) >= 1 )
			{
					$region = $data['session']['region'];
					if ( isset($region) && isset($region['number']) && $region['number'] > 0 && isset($sel[0]['KLRgn_id']) && $sel[0]['KLRgn_id'] > 0 && $sel[0]['KLRgn_id'] != $region['number'] )
				{
						// сохраняем
						$sql= "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"ErrMsg\"
							from p_PersonPolis_ins(
								Server_id := ?,
								Person_id := ?,
								OmsSprTerr_id := ?,
								PolisType_id := ?,
								OrgSmo_id := ?,
								Polis_Ser := ?,
								Polis_Num := ?,
								Polis_begDate := ?,
								Polis_endDate := ?,
								pmUser_id := ?
							)
						";
						$res = $this->db->query($sql,
							array($server_id, $pid, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser, $Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
	
						// выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
						$sql= "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"ErrMsg\"
							from p_Person_server(
								Server_id := ?,
								Person_id := ?,
								pmUser_id := ?
							)
						";
						$res = $this->db->query($sql,
							array($server_id, $pid, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
				}
			}
		}
			// сохраняем как обычно
			else
		{
				if ( isset($OmsSprTerr_id) )
			{
					// если изменились какие то поля кроме дат, то создаём новую периодику
					if (array_key_exists('OMSSprTerr_id', $newFields) || array_key_exists('PolisType_id', $newFields) || array_key_exists('Polis_Ser', $newFields) || array_key_exists('Polis_Num', $newFields) || array_key_exists('OrgSMO_id', $newFields)) {
						// проверка есть ли предыдущий не закрытый полис и его закрытие (только если сохранение после идентификации).
						$check = $this->checkPolisIntersection($data);
						if ($check === false) {
							return array(array('success' => false, 'Error_Msg' => 'Периоды полисов не могут пересекаться.'));
					}
	
						$sql="
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"ErrMsg\"
							from p_PersonPolis_ins(
								Server_id := :Server_id,
								Person_id := :Person_id,
								OmsSprTerr_id := :OmsSprTerr_id,
								PolisType_id := :PolisType_id,
								OrgSmo_id := :OrgSmo_id,
								Polis_Ser := :Polis_Ser,
								Polis_Num := :Polis_Num,
								Polis_begDate := :Polis_begDate,
								Polis_endDate := :Polis_endDate,
								pmUser_id := :pmUser_id
							)
						";
						$res = $this->db->query($sql,
							array(
									'Server_id' => $serv_id,
									'Person_id' => $pid,
									'OmsSprTerr_id' => $OmsSprTerr_id,
									'PolisType_id' => $PolisType_id,
									'OrgSmo_id' => $OrgSmo_id,
									'Polis_Ser' => $Polis_Ser,
									'Polis_Num' => $Polis_Num,
									'Polis_begDate' => $Polis_begDate,
									'Polis_endDate' => $Polis_endDate,
									'pmUser_id' => $data['pmUser_id']
							)
						);
						$this->ValidateInsertQuery($res);
				}
					// если изменились только даты полиса то обновляем периодику
					else
				{
						// получаем последнюю периодику по полису
						$sql = "
							select
								PersonEvn_id as \"PersonEvn_id\",
								Server_id as \"Server_id\",
								Polis_id as \"Polis_id\"
							from
								v_Person_all
							where
								PersonEvnClass_id = 8 and
								Person_id = :Person_id
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
							limit 1
						";
						$res = $this->db->query($sql, array('Person_id' => $pid));
						if ( is_object($res) ) {
							$sel = $res->result('array');
							if (count($sel) > 0) {
								$data['PersonEvn_id'] = $sel[0]['PersonEvn_id'];
								$check = $this->checkPolisIntersection($data);
								if ($check === false) {
									return array(array('success' => false, 'Error_Msg' => 'Периоды полисов не могут пересекаться.'));
							}
								$sql="
									select
										Error_Code as \"Error_Code\",
										Error_Message as \"ErrMsg\"
									from p_PersonPolis_upd(
										PersonPolis_id := :PersonPolis_id,
										Server_id := :Server_id,
										Person_id := :Person_id,
										OmsSprTerr_id := :OmsSprTerr_id,
										PolisType_id := :PolisType_id,
										OrgSmo_id := :OrgSmo_id,
										Polis_Ser := :Polis_Ser,
										Polis_Num := :Polis_Num,
										Polis_begDate := :Polis_begDate,
										Polis_endDate := :Polis_endDate,
										pmUser_id := :pmUser_id
									)
								";
								$res = $this->db->query($sql,
									array(
											'PersonPolis_id' => $sel[0]['PersonEvn_id'],
											'Server_id' => $sel[0]['Server_id'],
											'Person_id' => $pid,
											'OmsSprTerr_id' => $OmsSprTerr_id,
											'PolisType_id' => $PolisType_id,
											'OrgSmo_id' => $OrgSmo_id,
											'Polis_Ser' => $Polis_Ser,
											'Polis_Num' => $Polis_Num,
											'Polis_begDate' => $Polis_begDate,
											'Polis_endDate' => $Polis_endDate,
											'pmUser_id' => $data['pmUser_id']
									)
								);
								$this->ValidateInsertQuery($res);
						}
					}
				}
			}
		}
	}

		//Изменились атрибуты гражданства
		if ( array_key_exists('KLCountry_id', $newFields) || array_key_exists('NationalityStatus_IsTwoNation', $newFields) ){
			$serv_id = $server_id;

			$KLCountry_id = empty($data['KLCountry_id']) ? NULL : $data['KLCountry_id'];
			$NationalityStatus_IsTwoNation = $data['NationalityStatus_IsTwoNation'] ? 2 : 1;

			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonDocument_ins(
					Server_id := ?,
					Person_id := ?,
					KLCountry_id := ?,
					NationalityStatus_IsTwoNation := ?,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql, array($serv_id, $pid, $KLCountry_id, $NationalityStatus_IsTwoNation, $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
		//Изменились атрибуты документа
		if ( array_key_exists('DocumentType_id', $newFields) || array_key_exists('Document_Ser', $newFields) ||
			array_key_exists('Document_Num', $newFields) ||
			array_key_exists('OrgDep_id', $newFields) || array_key_exists('Document_begDate', $newFields)
		){
			$serv_id = $server_id;
			// в случае идентицикации человека по сводному регистру застрахованных в Уфе
			if ( $person_is_identified )
			{
					$serv_id = 0;
			}

			$DocumentType_id = (empty($data['DocumentType_id'])?NULL:$data['DocumentType_id']);
			$OrgDep_id = (empty($data['OrgDep_id'])?NULL:$data['OrgDep_id']);
			$Document_Ser = (empty($data['Document_Ser'])?'':$data['Document_Ser']);
			$Document_Num = (empty($data['Document_Num'])?'':$data['Document_Num']);
			$Document_begDate = empty($data['Document_begDate'])?NULL:$data['Document_begDate'];
			if ( isset($DocumentType_id ) )

			{
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonDocument_ins(
						Server_id := ?,
						Person_id := ?,
						DocumentType_id := ?,
						OrgDep_id := ?,
						Document_Ser := ?,
						Document_Num := ?,
						Document_begDate := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
					array($serv_id, $pid, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
		}
	
		//Изменились атрибуты адреса регистрации
		if ( array_key_exists('UKLCountry_id', $newFields) || array_key_exists('UKLRGN_id', $newFields) ||
	array_key_exists('UKLSubRGN_id', $newFields) ||
	array_key_exists('UKLCity_id', $newFields) || array_key_exists('UKLTown_id', $newFields) ||
	array_key_exists('UPersonSprTerrDop_id', $newFields) ||
	array_key_exists('UKLStreet_id', $newFields) ||
	array_key_exists('UAddress_House', $newFields) || array_key_exists('UAddress_Corpus', $newFields) ||
	array_key_exists('UAddress_Flat', $newFields) ||
	array_key_exists('UAddress_Zip', $newFields) ||
	array_key_exists('UAddress_Address', $newFields) ||
	/*array_key_exists('UAddress_begDate', $newFields) ||*/( isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa' && array_key_exists('UPersonSprTerrDop_id', $newFields) && $data['UPersonSprTerrDop_id'] > 0 ))
	{
			$KLCountry_id = (empty($data['UKLCountry_id'])?NULL:$data['UKLCountry_id']);
			$KLRgn_id = (empty($data['UKLRGN_id'])?NULL:$data['UKLRGN_id']);
			$KLRgnSocr_id = (empty($data['UKLRGNSocr_id'])?NULL:$data['UKLRGNSocr_id']);
			$KLSubRgn_id = (empty($data['UKLSubRGN_id'])?NULL:$data['UKLSubRGN_id']);
			$KLSubRgnSocr_id = (empty($data['UKLSubRGNSocr_id'])?NULL:$data['UKLSubRGNSocr_id']);
			$KLCity_id = (empty($data['UKLCity_id'])?NULL:$data['UKLCity_id']);
			$KLCitySocr_id = (empty($data['UKLCitySocr_id'])?NULL:$data['UKLCitySocr_id']);
			$KLTown_id = (empty($data['UKLTown_id'])?NULL:$data['UKLTown_id']);
			$District_id = (empty($data['UPersonSprTerrDop_id'])?NULL:$data['UPersonSprTerrDop_id']);
			$KLTownSocr_id = (empty($data['UKLTownSocr_id'])?NULL:$data['UKLTownSocr_id']);
			$KLStreet_id = (empty($data['UKLStreet_id'])?NULL:$data['UKLStreet_id']);
			$KLStreetSocr_id = (empty($data['UKLStreetSocr_id'])?NULL:$data['UKLStreetSocr_id']);
			$Address_Zip = (empty($data['UAddress_Zip'])?'':$data['UAddress_Zip']);
			$Address_House = (empty($data['UAddress_House'])?'':$data['UAddress_House']);
			$Address_Corpus = (empty($data['UAddress_Corpus'])?'':$data['UAddress_Corpus']);
			$Address_Flat = (empty($data['UAddress_Flat'])?'':$data['UAddress_Flat']);
			//$Address_begDate = (empty($data['UAddress_begDate'])?NULL:$data['UAddress_begDate']);
			
			
			if ( ($is_ufa === true || $is_samara === true) && $data['UPersonSprTerrDop_id'] > 0 )
		{
				$Address_Address = (empty($data['UAddress_Address'])?null:$data['UAddress_Address']);
		}
			else
		{
				$Address_Address = null;
		}
	
			$serv_id = $server_id;
			// в случае идентицикации человека по сводному регистру застрахованных в Уфе
			if ( $person_is_identified )
		{
				$serv_id = 0;
		}
	
			// Сохранение данных стран кроме РФ, которые ранее отсутствовали
			list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
			$this->saveAddressAll($serv_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id,
				$KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
	
			// Сохранение непосредственно адреса (ИДов)
			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonUAddress_ins(
					Server_id := ?,
					Person_id := ?,
					KLCountry_id := ?,
					KLRgn_id := ?,
					KLSubRgn_id := ?,
					KLCity_id := ?,
					KLTown_id := ?,
					District_id := ?,
					KLStreet_id := ?,
					Address_Zip := ?,
					Address_House := ?,
					Address_Corpus := ?,
					Address_Flat := ?,
					Address_Address := ?,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql,
				array($serv_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $District_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $Address_Address, $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
	}
	
	
	
		//TODO:Изменились атрибуты адреса рождения
	
		if ( array_key_exists('BKLCountry_id', $newFields) || array_key_exists('BKLRGN_id', $newFields) ||
	array_key_exists('BKLSubRGN_id', $newFields) ||
	array_key_exists('BKLCity_id', $newFields) || array_key_exists('BKLTown_id', $newFields) ||
	array_key_exists('BKLStreet_id', $newFields) ||
	array_key_exists('BAddress_House', $newFields) || array_key_exists('BAddress_Corpus', $newFields) ||
	array_key_exists('BAddress_Flat', $newFields) ||
	array_key_exists('BAddress_Zip', $newFields) || array_key_exists('BAddress_Address', $newFields) )
	{
			$Address_Address = trim(empty($data['BAddress_Address'])?null:$data['BAddress_Address']);
		
			$KLCountry_id = (empty($data['BKLCountry_id'])?NULL:$data['BKLCountry_id']);
			$KLRgn_id = (empty($data['BKLRGN_id'])?NULL:$data['BKLRGN_id']);
			$KLRgnSocr_id = (empty($data['BKLRGNSocr_id'])?NULL:$data['BKLRGNSocr_id']);
			$KLSubRgn_id = (empty($data['BKLSubRGN_id'])?NULL:$data['BKLSubRGN_id']);
			$KLSubRgnSocr_id = (empty($data['BKLSubRGNSocr_id'])?NULL:$data['BKLSubRGNSocr_id']);
			$KLCity_id = (empty($data['BKLCity_id'])?NULL:$data['BKLCity_id']);
			$KLCitySocr_id = (empty($data['BKLCitySocr_id'])?NULL:$data['BKLCitySocr_id']);
			$KLTown_id = (empty($data['BKLTown_id'])?NULL:$data['BKLTown_id']);
			$KLTownSocr_id = (empty($data['BKLTownSocr_id'])?NULL:$data['BKLTownSocr_id']);
			$KLStreet_id = (empty($data['BKLStreet_id'])?NULL:$data['BKLStreet_id']);
			$KLStreetSocr_id = (empty($data['BKLStreetSocr_id'])?NULL:$data['BKLStreetSocr_id']);
			$Address_Zip = (empty($data['BAddress_Zip'])?'':$data['BAddress_Zip']);
			$Address_House = (empty($data['BAddress_House'])?'':$data['BAddress_House']);
			$Address_Corpus = (empty($data['BAddress_Corpus'])?'':$data['BAddress_Corpus']);
			$Address_Flat = (empty($data['BAddress_Flat'])?'':$data['BAddress_Flat']);
	
	
	
			$serv_id = $server_id;
			// в случае идентицикации человека по сводному регистру застрахованных в Уфе
			if ( $person_is_identified )
		{
				$serv_id = 0;
		}
	
			// Сохранение данных стран кроме РФ, которые ранее отсутствовали
			list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
			$this->saveAddressAll($serv_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id,
				$KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
			$sql = "
				select
					Address_id as \"Address_id\"
				from
					PersonBirthPlace
				where
					Person_id = ?
			";
	
			$res = $this->db->query($sql, array($pid));
			$sel = $res->result('array');
	
			if ( count($sel) == 0 ) {
	
				$sql="
					select
						Address_id as \"Address_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_Address_ins(
						Server_id := ?,
						KLCountry_id := ?,
						KLRgn_id := ?,
						KLSubRgn_id := ?,
						KLCity_id := ?,
						KLTown_id := ?,
						KLStreet_id := ?,
						Address_Zip := ?,
						Address_House := ?,
						Address_Corpus := ?,
						Address_Flat := ?,
						Address_Address := ?,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
					array($serv_id, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $Address_Address, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
	
				$address_id = $res->result('array');
	
	
	
				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_PersonBirthPlace_ins(
						Person_id := ?,
						Address_id := ?,
						pmUser_id := ?
					)
				";
	
				$res = $this->db->query($sql, array($pid, $address_id[0]['Address_id'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			} else {
				$sql="
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"ErrMsg\"
					from p_Address_upd(
						Server_id := ?,
						Address_id := ?,
						KLAreaType_id := null,
						KLCountry_id := ?,
						KLRgn_id := ?,
						KLSubRgn_id := ?,
						KLCity_id := ?,
						KLTown_id := ?,
						KLStreet_id := ?,
						Address_Zip := ?,
						Address_House := ?,
						Address_Corpus := ?,
						Address_Flat := ?,
						Address_Address := ?,
						KLAreaStat_id := null,
						pmUser_id := ?
					)
				";
				$res = $this->db->query($sql,
					array($serv_id, $sel[0]['Address_id'],$KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $Address_Address, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
		}
	}
	
		//Изменились атрибуты адреса проживания
		if ( array_key_exists('PKLCountry_id', $newFields) || array_key_exists('PKLRGN_id', $newFields) ||
	array_key_exists('PKLSubRGN_id', $newFields) ||
	array_key_exists('PKLCity_id', $newFields) || array_key_exists('PKLTown_id', $newFields) ||
	array_key_exists('PKLStreet_id', $newFields) ||
	array_key_exists('PAddress_House', $newFields) || array_key_exists('PAddress_Corpus', $newFields) ||
	array_key_exists('PAddress_Flat', $newFields) ||
	array_key_exists('PAddress_Zip', $newFields) ||
	array_key_exists('PAddress_Address', $newFields) ||
	/*array_key_exists('PAddress_begDate', $newFields) ||*/( isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa' && array_key_exists('PPersonSprTerrDop_id', $newFields) && $data['PPersonSprTerrDop_id'] > 0 ))
	{
			$KLCountry_id = (empty($data['PKLCountry_id'])?NULL:$data['PKLCountry_id']);
			$KLRgn_id = (empty($data['PKLRGN_id'])?NULL:$data['PKLRGN_id']);
			$KLRgnSocr_id = (empty($data['PKLRGNSocr_id'])?NULL:$data['PKLRGNSocr_id']);
			$KLSubRgn_id = (empty($data['PKLSubRGN_id'])?NULL:$data['PKLSubRGN_id']);
			$KLSubRgnSocr_id = (empty($data['PKLSubRGNSocr_id'])?NULL:$data['PKLSubRGNSocr_id']);
			$KLCity_id = (empty($data['PKLCity_id'])?NULL:$data['PKLCity_id']);
			$KLCitySocr_id = (empty($data['PKLCitySocr_id'])?NULL:$data['PKLCitySocr_id']);
			$KLTown_id = (empty($data['PKLTown_id'])?NULL:$data['PKLTown_id']);
			$District_id = (empty($data['PPersonSprTerrDop_id'])?NULL:$data['PPersonSprTerrDop_id']);
			$KLTownSocr_id = (empty($data['PKLTownSocr_id'])?NULL:$data['PKLTownSocr_id']);
			$KLStreet_id = (empty($data['PKLStreet_id'])?NULL:$data['PKLStreet_id']);
			$KLStreetSocr_id = (empty($data['PKLStreetSocr_id'])?NULL:$data['PKLStreetSocr_id']);
			$Address_Zip = (empty($data['PAddress_Zip'])?'':$data['PAddress_Zip']);
			$Address_House = (empty($data['PAddress_House'])?'':$data['PAddress_House']);
			$Address_Corpus = (empty($data['PAddress_Corpus'])?'':$data['PAddress_Corpus']);
			$Address_Flat = (empty($data['PAddress_Flat'])?'':$data['PAddress_Flat']);
			//$Address_begDate = (empty($data['PAddress_begDate'])?NULL:$data['PAddress_begDate']);
				
			
			if ( ($is_ufa === true || $is_samara === true) && $data['PPersonSprTerrDop_id'] > 0 )
		{
				$Address_Address = (empty($data['PAddress_Address'])?null:$data['PAddress_Address']);
		}
		else
			$Address_Address = null;
	
			// Сохранение данных стран кроме РФ, которые ранее отсутствовали
			list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
			$this->saveAddressAll($server_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id,
				$KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
	
			$sql="
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"ErrMsg\"
				from p_PersonPAddress_ins(
					Server_id := ?,
					Person_id := ?,
					KLCountry_id := ?,
					KLRgn_id := ?,
					KLSubRgn_id := ?,
					KLCity_id := ?,
					KLTown_id := ?,
					District_id := ?,
					KLStreet_id := ?,
					Address_Zip := ?,
					Address_House := ?,
					Address_Corpus := ?,
					Address_Flat := ?,
					Address_Address := ?,
					pmUser_id := ?
				)
			";
			$res = $this->db->query($sql,
				array($server_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $District_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $Address_Address, $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
	}
	
		//Изменились атрибуты специфики детства
		if ( array_key_exists('ResidPlace_id', $newFields)
	|| array_key_exists('PersonChild_IsManyChild', $newFields)
	|| array_key_exists('PersonChild_IsBad', $newFields)
	|| array_key_exists('PersonChild_IsYoungMother', $newFields)
	|| array_key_exists('PersonChild_IsIncomplete', $newFields)
	|| array_key_exists('PersonChild_IsTutor', $newFields)
	|| array_key_exists('PersonChild_IsMigrant', $newFields)
	|| array_key_exists('HealthKind_id', $newFields)
	|| array_key_exists('FeedingType_id', $newFields)
	|| array_key_exists('PersonChild_IsInvalid', $newFields)
	|| array_key_exists('InvalidKind_id', $newFields)
	|| array_key_exists('PersonChild_invDate', $newFields)
	|| array_key_exists('HealthAbnorm_id', $newFields)
	|| array_key_exists('HealthAbnormVital_id', $newFields)
	|| array_key_exists('Diag_id', $newFields)
	|| array_key_exists('PersonSprTerrDop_id', $newFields) )
	{
			// проверяем наличие записи о PersonChild
			$sql = "
				select
					PersonChild_id as \"PersonChild_id\"
				from
					PersonChild
				where
					Person_id = ?
				limit 1
			";
			$res = $this->db->query($sql, array($pid));
			if ( is_object($res) )
		{
				$sel = $res->result('array');
				if ( count($sel) > 0 )
			{
					$is_pc_upd = true;
					$data['PersonChild_id'] = $sel[0]['PersonChild_id'];
			}
				else
			{
					$is_pc_upd = false;
					$data['PersonChild_id'] = NULL;
			}
		}

			$queryParams = array();
			$queryParams['Server_id'] = $data['Server_id'];
			$queryParams['Person_id'] = $pid;
			$queryParams['PersonChild_id'] = $data['PersonChild_id'];
			$queryParams['ResidPlace_id'] = $data['ResidPlace_id'];
			$queryParams['PersonChild_IsManyChild'] = $data['PersonChild_IsManyChild'];
			$queryParams['PersonChild_IsBad'] = $data['PersonChild_IsBad'];
			$queryParams['PersonChild_IsYoungMother'] = $data['PersonChild_IsYoungMother'];
			$queryParams['PersonChild_IsIncomplete'] = $data['PersonChild_IsIncomplete'];
			$queryParams['PersonChild_IsTutor'] = $data['PersonChild_IsTutor'];
			$queryParams['PersonChild_IsMigrant'] = $data['PersonChild_IsMigrant'];
			$queryParams['HealthKind_id'] = $data['HealthKind_id'];
			$queryParams['FeedingType_id'] = $data['FeedingType_id'];
			$queryParams['InvalidKind_id'] = $data['InvalidKind_id'];
			$queryParams['PersonChild_IsInvalid'] = $data['PersonChild_IsInvalid'];
			$queryParams['PersonChild_invDate'] = $data['PersonChild_invDate'];
			$queryParams['HealthAbnorm_id'] = $data['HealthAbnorm_id'];
			$queryParams['HealthAbnormVital_id'] = $data['HealthAbnormVital_id'];
			$queryParams['Diag_id'] = $data['Diag_id'];
			$queryParams['PersonSprTerrDop_id'] = $data['PersonSprTerrDop_id'];
			$queryParams['pmUser_id'] = $data['pmUser_id'];
			$queryParams['ChildTermType_id'] = null;
			$queryParams['PersonChild_IsAidsMother'] = null;
			$queryParams['PersonChild_IsBCG'] = null;
			$queryParams['PersonChild_BCGSer'] = null;
			$queryParams['PersonChild_BCGNum'] = null;
			$queryParams['BirthSvid_id'] = null;
			$queryParams['PersonChild_CountChild'] = null;
			$queryParams['ChildPositionType_id'] = null;
			$queryParams['PersonChild_IsRejection'] = null;
			$queryParams['BirthSpecStac_id'] = null;
	
			$procedure = 'p_PersonChild_ins';
			if ( $is_pc_upd ) {
				$procedure = 'p_PersonChild_upd';
				//чтобы значения атрибутов, редактируемых из других форм, не затирались при сохранении этой формы
				//  .. перед апдейтом записи достаем их и передаем в процедуру в неизменном виде
				$row = $this->getFirstRowFromQuery('
					select
						Server_id as \"Server_id\",
						PersonChild_id as \"PersonChild_id\",
						Person_id as \"Person_id\",
						ResidPlace_id as \"ResidPlace_id\",
						PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
						PersonChild_IsBad as \"PersonChild_IsBad\",
						PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
						PersonChild_IsTutor as \"PersonChild_IsTutor\",
						PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
						HealthKind_id as \"HealthKind_id\",
						PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
						FeedingType_id as \"FeedingType_id\",
						InvalidKind_id as \"InvalidKind_id\",
						PersonChild_invDate as \"PersonChild_invDate\",
						HealthAbnorm_id as \"HealthAbnorm_id\",
						HealthAbnormVital_id as \"HealthAbnormVital_id\",
						Diag_id as \"Diag_id\",
						PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
						PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
						pmUser_insID as \"pmUser_insID\",
						pmUser_updID as \"pmUser_updID\",
						PersonChild_insDT as \"PersonChild_insDT\",
						PersonChild_updDT as \"PersonChild_updDT\",
						ChildTermType_id as \"ChildTermType_id\",
						PersonChild_IsAidsMother as \"PersonChild_IsAidsMother\",
						PersonChild_IsBCG as \"PersonChild_IsBCG\",
						PersonChild_BCGSer as \"PersonChild_BCGSer\",
						PersonChild_BCGNum as \"PersonChild_BCGNum\",
						BirthSvid_id as \"BirthSvid_id\",
						PersonChild_CountChild as \"PersonChild_CountChild\",
						ChildPositionType_id as \"ChildPositionType_id\",
						PersonChild_IsRejection as \"PersonChild_IsRejection\",
						BirthSpecStac_id as \"BirthSpecStac_id\",
						EvnPS_id as \"EvnPS_id\",
						PersonChild_IsBreath as \"PersonChild_IsBreath\",
						PersonChild_IsHeart as \"PersonChild_IsHeart\",
						PersonChild_IsPulsation as \"PersonChild_IsPulsation\",
						PersonChild_IsMuscle as \"PersonChild_IsMuscle\"
					from v_PersonChild
					where personChild_id = :PersonChild_id
				', array('PersonChild_id' => $data['PersonChild_id']));
				if ($row) {
					foreach($queryParams as $key => &$value) {
						if (empty($value) && !empty($row[$key])) {
							$value = $row[$key];
						}
					}
				}
			}
			$sql="
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"ErrMsg\"
			from ". $procedure ."(
				Server_id := :Server_id,
				PersonChild_id := :PersonChild_id,
				Person_id := :Person_id,
				ResidPlace_id := :ResidPlace_id,
				BirthSpecStac_id := :BirthSpecStac_id,
				PersonChild_IsManyChild := :PersonChild_IsManyChild,
				PersonChild_IsBad := :PersonChild_IsBad,
				PersonChild_IsYoungMother := :PersonChild_IsYoungMother,
				PersonChild_IsIncomplete := :PersonChild_IsIncomplete,
				PersonChild_IsTutor := :PersonChild_IsTutor,
				PersonChild_IsMigrant := :PersonChild_IsMigrant,
				HealthKind_id := :HealthKind_id,
				FeedingType_id := :FeedingType_id,
				PersonChild_IsInvalid := :PersonChild_IsInvalid,
				InvalidKind_id := :InvalidKind_id,
				PersonChild_invDate := :PersonChild_invDate,
				HealthAbnorm_id := :HealthAbnorm_id,
				HealthAbnormVital_id := :HealthAbnormVital_id,
				Diag_id := :Diag_id,
				PersonSprTerrDop_id := :PersonSprTerrDop_id,
				pmUser_id := :pmUser_id,
				ChildTermType_id         := :ChildTermType_id        ,
				PersonChild_IsAidsMother := :PersonChild_IsAidsMother,
				PersonChild_IsBCG        := :PersonChild_IsBCG       ,
				PersonChild_BCGSer       := :PersonChild_BCGSer      ,
				PersonChild_BCGNum       := :PersonChild_BCGNum      ,
				BirthSvid_id             := :BirthSvid_id            ,
				PersonChild_CountChild   := :PersonChild_CountChild  ,
				ChildPositionType_id     := :ChildPositionType_id    ,
				PersonChild_IsRejection  := :PersonChild_IsRejection
			)
			";
	
			$res = $this->db->query($sql, $queryParams);
			$this->ValidateInsertQuery($res);
	}
		//$this->db->trans_commit();
		// Выбираем запись либо с Server_id больницы, либо если ее нет, с Server_id = 0
		$sql="
			select
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from PersonState
			where Person_id = ?
			order by Server_id desc
			limit 1
		";
		$res = $this->db->query($sql, array($pid));
		if ( is_object($res) )
	{
			$sel = $res->result('array');
	
			assert(!empty($sel));
			/* не должно случаться
			 если мы редактируем человека то периодика либо с server_id = 0 либо с server_id больницы должна быть,
			если добавляем, то должна создаваться.
			Пустое выражение говорит о том, что мы пытались редактировать чужого человека или что типа того
			*/
			$peid = $sel[0]['PersonEvn_id'];
			$server_id = $sel[0]['Server_id']; // берем server_id из выборки, ибо он может быть и 0
	}
	else
		$peid = 'NULL';
		$ret = array(array('Person_id'=>$pid, 'PersonEvn_id'=>$peid, 'Server_id'=>$server_id, 'Error_Msg' => ''));
		return $ret;
	}
}
