<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonNewBorn_model extends swPgModel {
	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных о новорождённом. Метод для API.
	 */
	function getPersonNewbornForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['Person_id'])) {
			$filter .= " and pnb.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['EvnSection_mid'])) {
			$filter .= " and pnb.EvnSection_mid = :EvnSection_mid";
			$queryParams['EvnSection_mid'] = $data['EvnSection_mid'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				pnb.PersonNewborn_id as \"PersonNewborn_id\",
				pnb.Person_id as \"Person_id\",
				pnb.BirthSvid_id as \"BirthSvid_id\",
				pnb.EvnPS_id as \"EvnPS_id\",
				pnb.BirthSpecStac_id as \"BirthSpecStac_id\",
				pnb.EvnSection_mid as \"EvnSection_mid\",
				pnb.FeedingType_id as \"FeedingType_id\",
				pnb.ChildTermType_id as \"ChildTermType_id\",
				case when pnb.PersonNewborn_IsAidsMother = 2 then 1 else 0 end as \"PersonNewborn_IsAidsMother\",
				pnb.PersonNewborn_CountChild as \"PersonNewborn_CountChild\",
				pnb.ChildPositionType_id as \"ChildPositionType_id\",
				case when pnb.PersonNewborn_IsRejection = 2 then 1 else 0 end as \"PersonNewborn_IsRejection\",
				case when pnb.PersonNewborn_IsHighRisk = 2 then 1 else 0 end as \"PersonNewborn_IsHighRisk\",
				pnb.NewbornWardType_id as \"NewbornWardType_id\",
				case when pnb.PersonNewborn_IsBleeding = 2 then 1 else 0 end as \"PersonNewborn_IsBleeding\",
				case when pnb.PersonNewborn_IsNeonatal = 2 then 1 else 0 end as \"PersonNewborn_IsNeonatal\",
				case when pnb.PersonNewborn_IsAudio = 2 then 1 else 0 end as \"PersonNewborn_IsAudio\",
				case when pnb.PersonNewborn_IsBCG = 2 then 1 else 0 end as \"PersonNewborn_IsBCG\",
				to_char(pnb.PersonNewborn_BCGDate, 'yyyy-mm-dd HH24:MI:SS') as \"PersonNewborn_BCGDate\",
				pnb.PersonNewborn_BCGSer as \"PersonNewborn_BCGSer\",
				pnb.PersonNewborn_BCGNum as \"PersonNewborn_BCGNum\",
				case when pnb.PersonNewborn_IsHepatit = 2 then 1 else 0 end as \"PersonNewborn_IsHepatit\",
				to_char(pnb.PersonNewborn_HepatitDate, 'yyyy-mm-dd HH24:MI:SS') as \"PersonNewborn_HepatitDate\",
				pnb.PersonNewborn_HepatitSer as \"PersonNewborn_HepatitSer\",
				pnb.PersonNewborn_HepatitNum as \"PersonNewborn_HepatitNum\",
				pnb.PersonNewborn_Head as \"PersonNewborn_Head\",
				pnb.PersonNewborn_Breast as \"PersonNewborn_Breast\",
				pnb.PersonNewborn_Height as \"PersonNewborn_Height\",
				pnb.PersonNewborn_Weight as \"PersonNewborn_Weight\",
				case when pnb.PersonNewborn_IsBreath = 2 then 1 else 0 end as \"PersonNewborn_IsBreath\",
				case when pnb.PersonNewborn_IsHeart = 2 then 1 else 0 end as \"PersonNewborn_IsHeart\",
				case when pnb.PersonNewborn_IsPulsation = 2 then 1 else 0 end as \"PersonNewborn_IsPulsation\",
				case when pnb.PersonNewborn_IsMuscle = 2 then 1 else 0 end as \"PersonNewborn_IsMuscle\",
				pnb.PersonNewborn_BloodBili as \"PersonNewborn_BloodBili\",
				pnb.PersonNewborn_BloodHemoglo as \"PersonNewborn_BloodHemoglo\",
				pnb.PersonNewborn_BloodEryth as \"PersonNewborn_BloodEryth\",
				pnb.PersonNewborn_BloodHemato as \"PersonNewborn_BloodHemato\"
			from
				v_PersonNewborn pnb
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * @comment
	 */
	function chekPersonNewBorn($data){
		//Редактирование специфика новорожденного доступна, если специфика ещё не создана, либо создана в указанном КВС
		$query = "
			select
				case
				 	when specEPS.EvnPS_id is null or specEPS.EvnPS_id = :EvnPS_id then 1 else 0
				end as \"editPersonNewBorn\"
			from
				Person P
				left join v_PersonNewBorn PNB on PNB.Person_id = P.Person_id
				left join v_EvnPS specEPS on specEPS.EvnPS_id = PNB.EvnPS_id
			where
				P.Person_id = :Person_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			"EvnPS_id" => $data['EvnPS_id']
		));

		if ( is_object($result) ) {

			$res=$result->result('array');
			if(count($res)>0){
				return $res;
			}else{
				return array(array('editPersonNewBorn'=>1));
			}
		}
		else {
			return array(array('editPersonNewBorn'=>0));
		}
	}

    /**
     *
     * @param type $data
     * @return type
     */
    function loadMonitorBirthSpecGrid($data){
        $params =array();
        $where = '';
        if ( !empty($data['Person_FIO']) ) {
            $where .= " and (COALESCE(PS.Person_Surname, '') || ' ' || COALESCE(PS.Person_Firname, '') || ' ' || COALESCE(PS.Person_Secname, '')) ilike :Person_FIO";
            $params['Person_FIO'] = $data['Person_FIO'] . '%';
        }
        if(isset($data['PersonNewBorn_IsHighRisk'])&&$data['PersonNewBorn_IsHighRisk']==1){
            $where .= " and pch.PersonNewBorn_IsHighRisk = 2";
        }
        if(isset($data['PersonNewBorn_IsNeonatal'])&&$data['PersonNewBorn_IsNeonatal']==1){
            $where .= " and pch.PersonNewBorn_IsNeonatal = 2";
        }
        if ( isset($data['Period_DateRange'][0]) )
        {
            $where .= " and cast(ps.Person_BirthDay as date) >= cast(:Beg_Date as date)";
            $params['Beg_Date'] = $data['Period_DateRange'][0];
        }

        if ( isset($data['Period_DateRange'][1]) )
        {
            $where .= " and cast(ps.Person_BirthDay as date) <= cast(:End_Date as date)";
            $params['End_Date'] = $data['Period_DateRange'][1];
        }
        if ( isset($data['State_id']) )
        {
            switch($data['State_id']){
                case 1:
                    $where .= " and (eps.EvnPS_id is null or eps.EvnPS_disDT is not null) and (ps.Person_deadDT is null and coalesce(PntDeathSvid.PntDeathSvid_id,'0')='0')";
                    break;
                case 3:
                    $where .= " and eps.EvnPS_id is not null and eps.EvnPS_disDT is null and (ps.Person_deadDT is null and coalesce(PntDeathSvid.PntDeathSvid_id,'0')='0')";
                    break;

                case 2:
                    $where .= " and (ps.Person_deadDT is not null or coalesce(PntDeathSvid.PntDeathSvid_id,'0')!='0')";
                    break;
            }

            $params['State_id'] = $data['State_id'];
        }
        if ( !empty($data['Lpu_hid']) ) {
            $where .= " and Eps.Lpu_id=:Lpu_hid";
            $params['Lpu_hid'] = $data['Lpu_hid'];
        }
        if ( !empty($data['Lpu_bid']) ) {
            $where .= " and mLP.Lpu_id=:Lpu_bid";
            $params['Lpu_bid'] = $data['Lpu_bid'];
        }
        $query = "
			select
				-- select
				case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as \"isHighRisk\",
				ps.Person_SurName ||' '||LEFT(coalesce(ps.Person_FirName,''),1)||' '||LEFT(coalesce(ps.Person_SecName,''),1) as \"Person_FIO\",
				to_char (ps.Person_BirthDay,'dd.mm.yyyy') AS \"Person_BirthDay\" ,
				COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) as \"PersonNewBorn_Weight\",
				BirthSvid.BirthSvid_id as \"BirthSvid_id\",
				BirthSvid.BirthSvid_Num as BirthSvid,
				PntDeathSvid.PntDeathSvid_id as \"PntDeathSvid_id\",
				PntDeathSvid.PntDeathSvid_Num as \"DeathSvid\",
				ps.Server_id AS \"Server_id\",
				pch.Person_id AS \"Person_cid\",
				mPS.Person_id as \"Person_mid\",
				mLP.Lpu_Nick as \"LpuBirth\",
				lp.Lpu_Nick as \"LpuHosp\",
				pch.PersonNewborn_id as \"PersonNewborn_id\",
				cast(agp.NewbornApgarRate_Values as int) as \"NewbornApgarRate_Values\",
				case
					when ps.Person_deadDT is not null or coalesce(PntDeathSvid.PntDeathSvid_id,'0')!='0' then 'Умер'
					when (eps.EvnPS_id is not null and eps.EvnPS_disDT is not null) or eps.EvnPS_id is null then 'Выписан'
					else 'В стационаре'
				end as \"State\",
				case when coalesce(pch.PersonNewBorn_IsNeonatal,1)=2 then 'true' else '' end as \"PersonNewBorn_IsNeonatal\",
				case when coalesce(pch.PersonNewborn_IsAidsMother,1)=2 then 'true' else '' end as \"PersonNewborn_IsAidsMother\",
				case when coalesce(pch.PersonNewborn_IsRejection,1)=2 then 'true' else '' end as \"PersonNewborn_IsRejection\",
				Deputy.Deputy_FIO as \"Deputy_FIO\",
				Deputy.Deputy_Addres as \"Deputy_Addres\",
				Deputy.Deputy_Phone as \"Deputy_Phone\"
				-- end select
			from
				-- from
				v_PersonState ps
				inner join v_PersonNewBorn pch on ps.Person_id = pch.Person_id
				left join v_BirthSpecStac BSS on pch.birthspecstac_id = BSS.BirthSpecStac_id
				LEFT JOIN LATERAL(
					select
						COALESCE(psd.Person_SurName,'') || ' ' || COALESCE(psd.Person_FirName,'') || ' ' || COALESCE(psd.Person_SecName,'') as Deputy_FIO,
						COALESCE(Adr.Address_Address,'') as Deputy_Addres,
						COALESCE(psd.Person_Phone,'') as Deputy_Phone
					from v_PersonDeputy PD
					inner join v_PersonState psd on psd.Person_id = PD.Person_pid and PD.Person_id = ps.Person_id
					left join v_Address Adr on Adr.Address_id = psd.PAddress_id
					limit 1
				)Deputy ON TRUE
				LEFT JOIN LATERAL(
					select 
						1 as Trauma
					from
						v_PersonBirthTrauma PC
					where (1 = 1)
						and PC.PersonNewBorn_id = pch.PersonNewBorn_id
                    limit 1
				)tr ON TRUE
				LEFT JOIN LATERAL (
					select a.NewbornApgarRate_Values as NewbornApgarRate_Values 
					from v_NewbornApgarRate a 
					where a.PersonNewborn_id = pch.PersonNewborn_id
					order by NewbornApgarRate_Time desc, NewbornApgarRate_insDT desc
					limit 1
				)agp ON TRUE
				left join v_EvnSection mES on mES.EvnSection_id = BSS.EvnSection_id
				left join v_PersonRegister mPR on mPR.PersonRegister_id = BSS.PersonRegister_id
				left join v_PersonState mPS on mPS.Person_id = coalesce(mPR.Person_id, mES.Person_id)
				left join v_Lpu_all mLP on mLP.Lpu_id = coalesce(BSS.Lpu_id, mES.Lpu_id)
				left join v_EvnPS eps on eps.EvnPS_id = pch.EvnPS_id
				left join v_Lpu_all lp on lp.lpu_id=eps.Lpu_id
				LEFT JOIN LATERAL(SELECT pds.PntDeathSvid_id as PntDeathSvid_id, pds.PntDeathSvid_Num as PntDeathSvid_Num FROM dbo.v_PntDeathSvid pds WHERE pds.Person_rid = pch.Person_id LIMIT 1) as PntDeathSvid ON TRUE
				LEFT JOIN LATERAL(SELECT BirthSvid_id,BirthSvid_Num FROM dbo.v_BirthSvid bs WHERE bs.Person_cid = pch.Person_id LIMIT 1) AS BirthSvid ON TRUE
				LEFT JOIN LATERAL(select case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight, pw.PersonWeight_id as PersonWeight_id from v_personWeight pw where pw.person_id =pch.Person_id and pw.WeightMeasureType_id = 1 limit 1) as W ON TRUE
				-- end from
			where
				-- where
				(1=1)
				" . $where . "
				-- end where
			order by
				-- order by
				ps.Person_Surname,
				ps.Person_Firname,
				ps.Person_Secname,
				ps.Person_Birthday
				-- end order by
		";
        //echo getDebugSQL(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);

        if ( is_object($result_count) ) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        }
        else {
            $count = 0;
        }

        if ( is_object($result) ) {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        }
        else {
            return false;
        }
    }

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadPersonBirthTraumaGrid($data){
		$query = "
			select 
				D.Diag_Code as \"Diag_Code\",
				PC.PersonBirthTrauma_id as \"PersonBirthTrauma_id\",
				PC.PersonNewBorn_id as \"PersonNewBorn_id\",
				to_char(PersonBirthTrauma_setDate, 'dd.mm.yyyy') AS \"PersonBirthTrauma_setDate \",
				D.Diag_Name as \"Diag_Name\",
				PC.Diag_id as \"Diag_id\",
				PC.PersonBirthTrauma_Comment as \"PersonBirthTrauma_Comment\",
				PC.BirthTraumaType_id as \"BirthTraumaType_id\",
				1 as \"RecordStatus_Code\"
			from
				v_PersonBirthTrauma PC
				left join v_Diag D on D.Diag_id = PC.Diag_id
			where (1 = 1)
				and PC.PersonNewBorn_id = :PersonNewBorn_id
				and PC.BirthTraumaType_id = :BirthTraumaType_id
			
				
		";
		switch($data['BirthTraumaType_id']){
			case 1:
				$query .="union all
				
			select
				D.Diag_Code as \"Diag_Code\",
				null as \"PersonBirthTrauma_id\",
				null as \"PersonNewBorn_id\",
				to_char(ED.EvnDiag_setDate, 'dd.mm.yyyy') AS \"PersonBirthTrauma_setDate\",
				D.Diag_Name as \"Diag_Name\",
				D.Diag_id as \"Diag_id\",
				null as \"PersonBirthTrauma_Comment\",
				:BirthTraumaType_id as \"BirthTraumaType_id\",
				4 as \"RecordStatus_Code\"
			from v_EvnDiag ED
			inner join v_PersonNewBorn PNB on pnb.EvnPS_id = ED.EvnDiag_rid
			left join v_Diag D on D.Diag_id = ED.Diag_id
			where PNB.PersonNewBorn_id = :PersonNewBorn_id
			and d.Diag_Code >='P10' and d.Diag_Code<='P15' and ED.EvnClass_id=33
";
				break;
			case 2:
				$query .="union all
				
			select
				D.Diag_Code as \"Diag_Code\",
				null as \"PersonBirthTrauma_id\",
				null as \"PersonNewBorn_id\",
				to_char(ED.EvnDiag_setDate, 'dd.mm.yyyy') AS \"PersonBirthTrauma_setDate\",
				D.Diag_Name as \"Diag_Name\",
				D.Diag_id as \"Diag_id\",
				null as \"PersonBirthTrauma_Comment\",
				:BirthTraumaType_id as \"BirthTraumaType_id\",
				4 as \"RecordStatus_Code\"
			from v_EvnDiag ED
			inner join v_PersonNewBorn PNB on pnb.EvnPS_id = ED.EvnDiag_rid
			left join v_Diag D on D.Diag_id = ED.Diag_id
			where PNB.PersonNewBorn_id = :PersonNewBorn_id
			and d.Diag_Code >='P15' and d.Diag_Code<='P99' and ED.EvnClass_id=33";
				break;
			case 3:
				$query .="union all
				
			select
				D.Diag_Code as \"Diag_Code\",
				null as \"PersonBirthTrauma_id\",
				null as \"PersonNewBorn_id\",
				to_char(ED.EvnDiag_setDate, 'dd.mm.yyyy') AS \"PersonBirthTrauma_setDate\",
				D.Diag_Name as \"Diag_Name\",
				D.Diag_id as \"Diag_id\",
				null as \"PersonBirthTrauma_Comment\",
				:BirthTraumaType_id as \"BirthTraumaType_id\",
				4 as \"RecordStatus_Code\"
			from v_EvnDiag ED
			inner join v_PersonNewBorn PNB on pnb.EvnPS_id = ED.EvnDiag_rid
			left join v_Diag D on D.Diag_id = ED.Diag_id
			where PNB.PersonNewBorn_id = :PersonNewBorn_id
			and d.Diag_Code like 'Q%' and ED.EvnClass_id=33";
				break;
		}
		$result = $this->db->query($query, array(
			'PersonNewBorn_id' => $data['PersonNewBorn_id'],
			'BirthTraumaType_id' => $data['BirthTraumaType_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getPersonBirthTraumaEditWindow($data){
		$query = "
			select
				PC.Diag_id as \"Diag_id\",
				PC.PersonNewBorn_id as \"PersonNewBorn_id\",
				PC.PersonBirthTrauma_id as \"PersonBirthTrauma_id\",
				PC.BirthTraumaType_id as \"BirthTraumaType_id\",
				PC.Server_id as \"Server_id\",
				PC.PersonBirthTrauma_Comment as \"PersonBirthTrauma_Comment\",
				to_char(PersonBirthTrauma_setDate, 'dd.mm.yyyy') as \"PersonBirthTrauma_setDate\"
			from
				v_PersonBirthTrauma PC
			where (1 = 1)
				and PC.PersonBirthTrauma_id = :PersonBirthTrauma_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'PersonBirthTrauma_id' => $data['PersonBirthTrauma_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadPersonNewBornData($data) {
		$query = "
			select
				PC.PersonNewBorn_id as \"PersonNewBorn_id\",
				PC.ChildTermType_id as \"ChildTermType_id\",
				PC.FeedingType_id as \"FeedingType_id\",
				PC.NewBornWardType_id as \"NewBornWardType_id\",
				coalesce(PC.PersonNewBorn_BCGNum, '') as \"PersonNewBorn_BCGNum\",
				coalesce(PC.PersonNewBorn_BCGSer, '') as \"PersonNewBorn_BCGSer\",
				to_char(PC.PersonNewBorn_BCGDate, 'dd.mm.yyyy') as \"PersonNewBorn_BCGDate\",
				coalesce(PC.PersonNewBorn_HepatitNum, '') as \"PersonNewBorn_HepatitNum\",
				coalesce(PC.PersonNewBorn_HepatitSer, '') as \"PersonNewBorn_HepatitSer\",
				to_char(PC.PersonNewBorn_HepatitDate, 'dd.mm.yyyy') as \"PersonNewBorn_HepatitDate\",
				PC.PersonNewBorn_IsAidsMother as \"PersonNewBorn_IsAidsMother\",
				PC.PersonNewBorn_IsHepatit as \"PersonNewBorn_IsHepatit\",
				PC.PersonNewBorn_IsBCG as \"PersonNewBorn_IsBCG\",
				IsBreath.YesNo_Code as \"PersonNewBorn_IsBreath\",
				IsHeart.YesNo_Code as \"PersonNewBorn_IsHeart\",
				IsPulsation.YesNo_Code as \"PersonNewBorn_IsPulsation\",
				IsMuscle.YesNo_Code as \"PersonNewBorn_IsMuscle\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PC.Person_id as \"Person_id\",
				PC.Server_id as \"Server_id\",
				PC.EvnPS_id as \"EvnPS_id\",
				PC.EvnSection_mid as \"EvnSection_mid\",
				PC.PersonNewBorn_IsBleeding as \"PersonNewBorn_IsBleeding\",
				PC.PersonNewBorn_IsNeonatal as \"PersonNewBorn_IsNeonatal\",
				PC.PersonNewBorn_IsAudio as \"PersonNewBorn_IsAudio\",
				PersonNewBorn_CountChild as \"PersonNewBorn_CountChild\",
				ChildPositionType_id as \"ChildPositionType_id\",
				PersonNewBorn_IsRejection as \"PersonNewBorn_IsRejection\",
				H.PersonHeight_Height as \"PersonNewBorn_Height\",
				PC.PersonNewBorn_Head as \"PersonNewBorn_Head\",
				W.PersonWeight_Weight as \"PersonNewBorn_Weight\",
				PC.PersonNewBorn_Breast as \"PersonNewBorn_Breast\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				PC.PersonNewborn_BloodBili as \"PersonNewborn_BloodBili\",
				PC.PersonNewborn_BloodHemoglo as \"PersonNewborn_BloodHemoglo\",
				PC.PersonNewborn_BloodEryth as \"PersonNewborn_BloodEryth\",
				PC.PersonNewborn_BloodHemato as \"PersonNewborn_BloodHemato\",
				PC.RefuseType_pid as \"RefuseType_pid\",
				PC.RefuseType_aid as \"RefuseType_aid\",
				PC.RefuseType_bid as \"RefuseType_bid\",
				PC.RefuseType_gid as \"RefuseType_gid\"
			from
				v_PersonNewBorn PC
				left join v_PersonState ps on PC.person_id=ps.person_id
				left join lateral(
					select
						ph.PersonHeight_Height,ph.PersonHeight_id
					from v_personHeight ph
					where ph.person_id =PC.Person_id
						and ph.HeightMeasureType_id = 1
					limit 1
				) H on true
				left join lateral(
					select
						case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight
					end as PersonWeight_Weight,pw.PersonWeight_id
					from v_personWeight pw
					where pw.person_id =PC.Person_id
						and pw.WeightMeasureType_id = 1
					limit 1
				) W on true
				left join v_YesNo IsBreath on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
				left join v_YesNo IsHeart on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
				left join v_YesNo IsPulsation on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
				left join v_YesNo IsMuscle on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
			where (1 = 1)
				and PC.Person_id = :Person_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *
	 * @param array $data
	 * @return array
	 */
	function savePersonNewBorn($data) {
		$procedure = "p_PersonNewBorn_ins";
		//var_dump($data['ApgarData']);die;
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'BirthSpecStac_id' => (!empty($data['BirthSpecStac_id']) ? $data['BirthSpecStac_id'] : NULL),
			'PersonNewBorn_id' => (!empty($data['PersonNewBorn_id']) ? $data['PersonNewBorn_id'] : NULL),
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'EvnSection_mid'=>(!empty($data['EvnSection_mid']) ? $data['EvnSection_mid'] : NULL),
			'PersonNewBorn_IsHighRisk'=>(!empty($data['PersonNewBorn_IsHighRisk']) ? $data['PersonNewBorn_IsHighRisk'] : NULL),
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $data['Person_id']
		);
		$sql = "
			select
				PNB.PersonNewBorn_id as \"PersonNewBorn_id\",
				PNB.BirthSpecStac_id as \"BirthSpecStac_id\",
				PNB.EvnPS_id as \"EvnPS_id\",
				PNB.EvnSection_mid as \"EvnSection_mid\",
				ES.EvnSection_pid as \"EvnPS_mid\",
				PNB.PersonNewBorn_IsHighRisk as \"PersonNewBorn_IsHighRisk\"
			from v_PersonNewBorn PNB
			left join v_EvnSection ES on ES.EvnSection_id = PNB.EvnSection_mid
			where PNB.Person_id = :Person_id
			limit 1
		";
		$res =  $this->db->query($sql, array('Person_id'=>$data['Person_id']));
		$res = $res->result('array');
		if(count($res)>0){
			if (!empty($res[0]['EvnPS_id']) && !empty($res[0]['EvnPS_mid']) && $res[0]['EvnPS_id'] == $res[0]['EvnPS_mid']) {
				return $this->createError('','Совпадают КВС матери и КВС ребенка');
			}

			$queryParams['PersonNewBorn_id'] = (!empty($res[0]['PersonNewBorn_id'])&&$queryParams['PersonNewBorn_id']==null)?$res[0]['PersonNewBorn_id']:$queryParams['PersonNewBorn_id'];
			$data['PersonNewBorn_id'] =$queryParams['PersonNewBorn_id'];
			$queryParams['BirthSpecStac_id'] = (!empty($res[0]['BirthSpecStac_id'])&&$queryParams['BirthSpecStac_id']==null)?$res[0]['BirthSpecStac_id']:$queryParams['BirthSpecStac_id'];
			$queryParams['EvnPS_id'] = (!empty($res[0]['EvnPS_id'])&&$queryParams['EvnPS_id']==null)?$res[0]['EvnPS_id']:$queryParams['EvnPS_id'];
			$queryParams['EvnSection_mid'] = (!empty($res[0]['EvnSection_mid'])&&$queryParams['EvnSection_mid']==null)?$res[0]['EvnSection_mid']:$queryParams['EvnSection_mid'];
			$queryParams['PersonNewBorn_IsHighRisk'] = (!empty($res[0]['PersonNewBorn_IsHighRisk'])&&empty($queryParams['PersonNewBorn_IsHighRisk']))?$res[0]['PersonNewBorn_IsHighRisk']:$queryParams['PersonNewBorn_IsHighRisk'];
		}

		$personInfo = $this->getFirstRowFromQuery("
			select
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd HH24:MI:SS') as \"Person_BirthDay\"
			from v_PersonState PS
			where PS.Person_id = :Person_id
			limit 1
		", array('Person_id'=>$data['Person_id']));
		if (!is_array($personInfo)) {
			return $this->createError('','Ошибка при получении данных о ребёнке');
		}

		if ( !empty($data['PersonNewBorn_id']) ) {
			$procedure = "p_PersonNewBorn_upd";
		}
		$queryParams['NewBornWardType_id'] = (!empty($data['NewBornWardType_id']) ? $data['NewBornWardType_id'] : NULL);//
		$queryParams['FeedingType_id'] =(!empty($data['FeedingType_id']) ? $data['FeedingType_id'] : NULL);//
		$queryParams['ChildTermType_id'] = (!empty($data['ChildTermType_id']) ? $data['ChildTermType_id'] : NULL);//
		$queryParams['PersonNewBorn_IsAidsMother'] = (!empty($data['PersonNewBorn_IsAidsMother']) ? $data['PersonNewBorn_IsAidsMother'] : NULL);//
		$queryParams['PersonNewBorn_IsAudio'] = (!empty($data['PersonNewBorn_IsAudio']) ? $data['PersonNewBorn_IsAudio'] : NULL);
		$queryParams['PersonNewBorn_IsBleeding'] = (!empty($data['PersonNewBorn_IsBleeding']) ? $data['PersonNewBorn_IsBleeding'] : NULL);
		$queryParams['PersonNewBorn_IsNeonatal'] = (!empty($data['PersonNewBorn_IsNeonatal']) ? $data['PersonNewBorn_IsNeonatal'] : NULL);
		$queryParams['PersonNewBorn_IsBCG'] = (!empty($data['PersonNewBorn_IsBCG']) ? $data['PersonNewBorn_IsBCG'] : NULL);//
		$queryParams['PersonNewBorn_BCGSer'] = (!empty($data['PersonNewBorn_BCGSer']) ? $data['PersonNewBorn_BCGSer'] : NULL);//
		$queryParams['PersonNewBorn_BCGNum'] = (!empty($data['PersonNewBorn_BCGNum']) ? $data['PersonNewBorn_BCGNum'] : NULL);//
		$queryParams['PersonNewBorn_BCGDate'] = (!empty($data['PersonNewBorn_BCGDate']) ? $data['PersonNewBorn_BCGDate'] : NULL);//
		$queryParams['PersonNewBorn_IsHepatit'] = (!empty($data['PersonNewBorn_IsHepatit']) ? $data['PersonNewBorn_IsHepatit'] : NULL);
		$queryParams['PersonNewBorn_HepatitSer'] = (!empty($data['PersonNewBorn_HepatitSer']) ? $data['PersonNewBorn_HepatitSer'] : NULL);
		$queryParams['PersonNewBorn_HepatitNum'] = (!empty($data['PersonNewBorn_HepatitNum']) ? $data['PersonNewBorn_HepatitNum'] : NULL);
		$queryParams['PersonNewBorn_HepatitDate'] = (!empty($data['PersonNewBorn_HepatitDate']) ? $data['PersonNewBorn_HepatitDate'] : NULL);
		$queryParams['PersonNewBorn_CountChild'] = (!empty($data['PersonNewBorn_CountChild']) ? $data['PersonNewBorn_CountChild'] : NULL);
		$queryParams['ChildPositionType_id'] = (!empty($data['ChildPositionType_id']) ? $data['ChildPositionType_id'] : NULL);
		$queryParams['PersonNewBorn_IsRejection'] = (!empty($data['PersonNewBorn_IsRejection']) ? $data['PersonNewBorn_IsRejection'] : NULL);
		$queryParams['PersonNewBorn_Height'] = (!empty($data['PersonNewBorn_Height']) ? $data['PersonNewBorn_Height'] : NULL);
		$queryParams['PersonNewBorn_Weight'] = (!empty($data['PersonNewBorn_Weight']) ? $data['PersonNewBorn_Weight'] : NULL);
		$queryParams['PersonNewBorn_Head'] = (!empty($data['PersonNewBorn_Head']) ? $data['PersonNewBorn_Head'] : NULL);
		$queryParams['PersonNewBorn_Breast'] = (!empty($data['PersonNewBorn_Breast']) ? $data['PersonNewBorn_Breast'] : NULL);
		$queryParams['PersonNewBorn_IsBreath'] = (!empty($data['PersonNewBorn_IsBreath']) ? $data['PersonNewBorn_IsBreath'] : NULL);
		$queryParams['PersonNewBorn_IsHeart'] = (!empty($data['PersonNewBorn_IsHeart']) ? $data['PersonNewBorn_IsHeart'] : NULL);
		$queryParams['PersonNewBorn_IsPulsation'] = (!empty($data['PersonNewBorn_IsPulsation']) ? $data['PersonNewBorn_IsPulsation'] : NULL);
		$queryParams['PersonNewBorn_IsMuscle'] = (!empty($data['PersonNewBorn_IsMuscle']) ? $data['PersonNewBorn_IsMuscle'] : NULL);
		$queryParams['PersonNewborn_BloodBili'] = (!empty($data['PersonNewborn_BloodBili']) ? $data['PersonNewborn_BloodBili'] : NULL);
		$queryParams['PersonNewborn_BloodHemoglo'] = (!empty($data['PersonNewborn_BloodHemoglo']) ? $data['PersonNewborn_BloodHemoglo'] : NULL);
		$queryParams['PersonNewborn_BloodEryth'] = (!empty($data['PersonNewborn_BloodEryth']) ? $data['PersonNewborn_BloodEryth'] : NULL);
		$queryParams['PersonNewborn_BloodHemato'] = (!empty($data['PersonNewborn_BloodHemato']) ? $data['PersonNewborn_BloodHemato'] : NULL);
		$queryParams['RefuseType_pid'] = (!empty($data['RefuseType_pid']) ? $data['RefuseType_pid'] : NULL);
		$queryParams['RefuseType_aid'] = (!empty($data['RefuseType_aid']) ? $data['RefuseType_aid'] : NULL);
		$queryParams['RefuseType_bid'] = (!empty($data['RefuseType_bid']) ? $data['RefuseType_bid'] : NULL);
		$queryParams['RefuseType_gid'] = (!empty($data['RefuseType_gid']) ? $data['RefuseType_gid'] : NULL);

		$query = "
			select
				PersonNewBorn_id as \"PersonNewBorn_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				Server_id := :Server_id,
				PersonNewBorn_id := :PersonNewBorn_id,
				Person_id := :Person_id,
				FeedingType_id := :FeedingType_id,
				ChildTermType_id := :ChildTermType_id,
				EvnSection_mid := :EvnSection_mid,
				PersonNewBorn_Weight:=:PersonNewBorn_Weight,
				PersonNewBorn_Height:=:PersonNewBorn_Height,
				PersonNewBorn_Head:=:PersonNewBorn_Head,
				PersonNewBorn_Breast:=:PersonNewBorn_Breast,
				PersonNewBorn_IsAidsMother := :PersonNewBorn_IsAidsMother,
				PersonNewBorn_IsAudio := :PersonNewBorn_IsAudio,
				PersonNewBorn_IsBleeding := :PersonNewBorn_IsBleeding,
				PersonNewBorn_IsNeonatal:=:PersonNewBorn_IsNeonatal,
				PersonNewBorn_IsBCG := :PersonNewBorn_IsBCG,
				PersonNewBorn_BCGSer := :PersonNewBorn_BCGSer,
				PersonNewBorn_BCGNum := :PersonNewBorn_BCGNum,
				PersonNewBorn_BCGDate := :PersonNewBorn_BCGDate,
				PersonNewBorn_IsHepatit := :PersonNewBorn_IsHepatit,
				PersonNewBorn_HepatitSer := :PersonNewBorn_HepatitSer,
				PersonNewBorn_HepatitNum := :PersonNewBorn_HepatitNum,
				PersonNewBorn_HepatitDate := :PersonNewBorn_HepatitDate,
				PersonNewBorn_CountChild := :PersonNewBorn_CountChild,
				PersonNewBorn_IsBreath := :PersonNewBorn_IsBreath,
				PersonNewBorn_IsHeart := :PersonNewBorn_IsHeart,
				PersonNewBorn_IsPulsation := :PersonNewBorn_IsPulsation,
				PersonNewBorn_IsMuscle := :PersonNewBorn_IsMuscle,
				PersonNewBorn_IsHighRisk := :PersonNewBorn_IsHighRisk,
				PersonNewborn_BloodBili := :PersonNewborn_BloodBili,
				PersonNewborn_BloodHemoglo := :PersonNewborn_BloodHemoglo,
				PersonNewborn_BloodEryth := :PersonNewborn_BloodEryth,
				PersonNewborn_BloodHemato := :PersonNewborn_BloodHemato,
				ChildPositionType_id := :ChildPositionType_id,
				PersonNewBorn_IsRejection:=:PersonNewBorn_IsRejection,
				BirthSpecStac_id := :BirthSpecStac_id,
				EvnPS_id := :EvnPS_id,
				NewBornWardType_id := :NewBornWardType_id,
				RefuseType_pid := :RefuseType_pid,
				RefuseType_aid := :RefuseType_aid,
				RefuseType_bid := :RefuseType_bid,
				RefuseType_gid := :RefuseType_gid,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $queryParams);exit();
		$res = $this->queryResult($query, $queryParams);
		if (!is_array($res)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
		if (!$this->isSuccessful($res)) {
			return $res;
		}

		$this->updatePersonChild($data);

		if(!empty($data['PersonNewBorn_Height'])){
			$this->load->model('PersonHeight_model');
			$data['HeightMeasureType_id']=1;
			$data['PersonHeight_setDate']=$personInfo['Person_BirthDay'];
			$data['Okei_id']=2;
			$data['PersonHeight_Height'] = $data['PersonNewBorn_Height'];
			$this->PersonHeight_model->savePersonHeight($data);
		}
		if(!empty($data['PersonNewBorn_Weight'])){
			$this->load->model('PersonWeight_model');
			$data['WeightMeasureType_id']=1;
			$data['PersonWeight_setDate']=$personInfo['Person_BirthDay'];;
			$data['Okei_id']=36;
			$data['PersonWeight_Weight']=$data['PersonNewBorn_Weight'];
			$this->PersonWeight_model->savePersonWeight($data);
		}
		if (!empty($data['PersonBirthTraumaData']) && is_array($data['PersonBirthTraumaData'])) {
			$savePersonBirthTraumaResult = $this->savePersonBirthTrauma(array(
				"PersonNewBorn_id"=>$res[0]['PersonNewBorn_id'],
				"Server_id"=>$data['Server_id'],
				'pmUser_id'=>$data['pmUser_id'],
				"PersonBirthTraumaData"=>$data['PersonBirthTraumaData']
			));
		}
		if (!empty($data['ApgarData']) && is_array($data['ApgarData'])) {
			$SaveApgarResult = $this->saveApgarRate(array(
				"PersonNewBorn_id"=>$res[0]['PersonNewBorn_id'],
				"Server_id"=>$data['Server_id'],
				'pmUser_id'=>$data['pmUser_id'],
				"ApgarData"=>$data['ApgarData']
			));
		}
		$this->updatePersonNewbornIsHighRisk(array(
			'PersonNewBorn_id' => $res[0]['PersonNewBorn_id'],
		));

		return $res;
	}
	/**
	 *
	 * @param type $data
	 */
	function saveApgarRate($data){
		//print_r($data);
		foreach($data['ApgarData'] as $item){
			if(isset($item['RecordStatus_Code']))
			{
				switch($item['RecordStatus_Code']){
					case 0:
					case 2:
						$queryParams = array(
							'pmUser_id' => $data['pmUser_id'],
							'NewbornApgarRate_id'=>NULL,
							'PersonNewBorn_id' =>$data['PersonNewBorn_id'],
							'Server_id'=>$data['Server_id'],
							'NewbornApgarRate_Time'=>!empty($item['NewbornApgarRate_Time'])?$item['NewbornApgarRate_Time']:NULL,
							'NewbornApgarRate_Values'=>!empty($item['NewbornApgarRate_Values'])?$item['NewbornApgarRate_Values']:NULL,
							'NewbornApgarRate_Reflex'=>!empty($item['NewbornApgarRate_Reflex'])||$item['NewbornApgarRate_Reflex']===0?$item['NewbornApgarRate_Reflex']:NULL,
							'NewbornApgarRate_ToneMuscle'=>!empty($item['NewbornApgarRate_ToneMuscle'])||$item['NewbornApgarRate_ToneMuscle']===0?$item['NewbornApgarRate_ToneMuscle']:NULL,
							'NewbornApgarRate_SkinColor'=>!empty($item['NewbornApgarRate_SkinColor'])||$item['NewbornApgarRate_SkinColor']===0?$item['NewbornApgarRate_SkinColor']:NULL,
							'NewbornApgarRate_Breath'=>!empty($item['NewbornApgarRate_Breath'])||$item['NewbornApgarRate_Breath']===0?$item['NewbornApgarRate_Breath']:NULL,
							'NewbornApgarRate_Heartbeat'=>!empty($item['NewbornApgarRate_Heartbeat'])||$item['NewbornApgarRate_Heartbeat']===0?$item['NewbornApgarRate_Heartbeat']:NULL,
						);
						//print_r($queryParams);
						$proc = 'ins';
						if($item['RecordStatus_Code']==2){
							$queryParams['NewbornApgarRate_id']=$item['NewbornApgarRate_id'];
							$proc = 'upd';
						}
						$query = "
							select
								NewbornApgarRate_id as \"PersonNewBorn_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from p_NewbornApgarRate_".$proc."(
								NewbornApgarRate_id := :NewbornApgarRate_id,
								PersonNewBorn_id := :PersonNewBorn_id,
								NewbornApgarRate_Time:=:NewbornApgarRate_Time,
								NewbornApgarRate_Values :=:NewbornApgarRate_Values,
								NewbornApgarRate_Reflex := :NewbornApgarRate_Reflex,
								NewbornApgarRate_ToneMuscle :=:NewbornApgarRate_ToneMuscle,
								NewbornApgarRate_SkinColor :=:NewbornApgarRate_SkinColor,
								NewbornApgarRate_Breath:=:NewbornApgarRate_Breath,
								NewbornApgarRate_Heartbeat:=:NewbornApgarRate_Heartbeat,
								Server_id := :Server_id,
								pmUser_id := :pmUser_id
							)
						";
						//echo getDebugSQL($query, $queryParams);exit();
						$result = $this->db->query($query, $queryParams);
						break;

					case 3:
						$queryParams = array('NewbornApgarRate_id'=>$item['NewbornApgarRate_id']);
						$query = "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from p_NewbornApgarRate_del(
								NewbornApgarRate_id := :NewbornApgarRate_id
							)
						";
						/*echo getDebugSQL($query, $queryParams);exit();*/
						$result = $this->db->query($query, $queryParams);
						break;
				}
			}
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function deletePersonBirthTrauma($data){
		$queryParams = array(
			'PersonBirthTrauma_id' =>$data['id'],
		);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonBirthTrauma_del(
				PersonBirthTrauma_id := :PersonBirthTrauma_id
			)
		";
		/*echo getDebugSQL($query, $queryParams);exit();*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}
	/**
	 * @comment
	 */
	function deleteNewbornApgarRate($data){
		$queryParams = array(
			'NewbornApgarRate_id' =>$data['id'],
		);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_NewbornApgarRate_del(
				NewbornApgarRate_id := :NewbornApgarRate_id
			)
		";
		/*echo getDebugSQL($query, $queryParams);exit();*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function delLink($data) {
		$query = "
			update PersonNewBorn set BirthSpecStac_id = null where Person_id = :Person_id
		";

		$this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));
		$sql = "
			with mv as (select PersonDeputy_id as pd from PersonDeputy where Person_id = ? limit 1)

			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonDeputy_del(
				PersonDeputy_id := (select pd from mv)
			)
		";
		$res = $this->db->query($sql, array($data['Person_id']));
		return array(array('Error_Msg' => null,'Error_Code'=>null));
	}

	/**
	 * @comment
	 */
	function updateNewbornApgarRate($data){

		$queryParams = array(
			'pmUser_id' => $data['pmUser_id'],
			'NewbornApgarRate_id'=>$data['NewbornApgarRate_id'],
			'PersonNewBorn_id' =>$data['PersonNewBorn_id'],
			'Server_id'=>$data['Server_id'],
			'NewbornApgarRate_Time'=>isset($data['NewbornApgarRate_Time'])?$data['NewbornApgarRate_Time']:NULL,
			'NewbornApgarRate_Values'=>isset($data['NewbornApgarRate_Values'])?$data['NewbornApgarRate_Values']:NULL,
			'NewbornApgarRate_Reflex'=>isset($data['NewbornApgarRate_Reflex'])?$data['NewbornApgarRate_Reflex']:NULL,
			'NewbornApgarRate_ToneMuscle'=>isset($data['NewbornApgarRate_ToneMuscle'])?$data['NewbornApgarRate_ToneMuscle']:NULL,
			'NewbornApgarRate_SkinColor'=>isset($data['NewbornApgarRate_SkinColor'])?$data['NewbornApgarRate_SkinColor']:NULL,
			'NewbornApgarRate_Breath'=>isset($data['NewbornApgarRate_Breath'])?$data['NewbornApgarRate_Breath']:NULL,
			'NewbornApgarRate_Heartbeat'=>isset($data['NewbornApgarRate_Heartbeat'])?$data['NewbornApgarRate_Heartbeat']:NULL,
		);
		$empty = (
			$queryParams['NewbornApgarRate_Reflex']===NULL
			&&$queryParams['NewbornApgarRate_Heartbeat']===NULL
			&&$queryParams['NewbornApgarRate_Breath']===NULL
			&&$queryParams['NewbornApgarRate_SkinColor']===NULL
			&&$queryParams['NewbornApgarRate_ToneMuscle']===NULL
		);
		$sum = (int)$queryParams['NewbornApgarRate_Reflex']+(int)$queryParams['NewbornApgarRate_Heartbeat']+(int)$queryParams['NewbornApgarRate_Breath']+(int)$queryParams['NewbornApgarRate_SkinColor']+(int)$queryParams['NewbornApgarRate_ToneMuscle'];
		if(!$empty){
			$queryParams['NewbornApgarRate_Values'] = $sum;
		}
		//echo $s; exit();
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_NewbornApgarRate_upd(
				NewbornApgarRate_id := :NewbornApgarRate_id,
				PersonNewBorn_id := :PersonNewBorn_id,
				NewbornApgarRate_Time:=:NewbornApgarRate_Time,
				NewbornApgarRate_Values :=:NewbornApgarRate_Values,
				NewbornApgarRate_Reflex := :NewbornApgarRate_Reflex,
				NewbornApgarRate_ToneMuscle :=:NewbornApgarRate_ToneMuscle,
				NewbornApgarRate_SkinColor :=:NewbornApgarRate_SkinColor,
				NewbornApgarRate_Breath:=:NewbornApgarRate_Breath,
				NewbornApgarRate_Heartbeat:=:NewbornApgarRate_Heartbeat,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";
		/*echo getDebugSQL($query, $queryParams);exit();*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function addDeputy($data){

		$sql = "
			with mv as (select PersonDeputy_id as pd from PersonDeputy where Person_id = ? limit 1)

			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonDeputy_del(
				PersonDeputy_id := (select pd from mv)
			)
		";
		$res = $this->db->query($sql, array($data['Person_id']));

		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonDeputy_ins(
				Server_id := ?,
				Person_id := ?,
				Person_pid := ?,
				DeputyKind_id := ?,
				pmUser_id := ?
			)
		";
		$res = $this->db->query($sql, array($data['Server_id'], $data['Person_id'], $data['DeputyPerson_id'], 2, $data['pmUser_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}
	/**
	 * @comment
	 */
	function AddApgarRate($data){
		$queryParams = array(
			'pmUser_id' => $data['pmUser_id'],
			'PersonNewBorn_id' =>$data['PersonNewBorn_id'],
			'Server_id'=>$data['Server_id']
		);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_NewbornApgarRate_ins
			(
				PersonNewBorn_id := :PersonNewBorn_id,
				NewbornApgarRate_Values := 0,
				NewbornApgarRate_Time := 0,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";
		/*echo getDebugSQL($query, $queryParams);exit();*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}
	/**
	 * @comment
	 */
	function loadNewbornApgarRateGrid($data){
		$query = "
			select 
				cast(NewbornApgarRate_Values as int) as \"NewbornApgarRate_Values\",
				NewbornApgarRate_Reflex as \"NewbornApgarRate_Reflex\",
				NewbornApgarRate_ToneMuscle as \"NewbornApgarRate_ToneMuscle\",
				NewbornApgarRate_SkinColor as \"NewbornApgarRate_SkinColor\",
				NewbornApgarRate_Breath as \"NewbornApgarRate_Breath\",
				NewbornApgarRate_Heartbeat as \"NewbornApgarRate_Heartbeat\",
				NewbornApgarRate_Time as \"NewbornApgarRate_Time\",
				PersonNewBorn_id as \"PersonNewBorn_id\",
				NewbornApgarRate_id as \"NewbornApgarRate_id\",
				1 as \"RecordStatus_Code\"
			from
				v_NewbornApgarRate PC
			where (1 = 1)
				and PC.PersonNewBorn_id = :PersonNewBorn_id
			order by NewbornApgarRate_Time asc
		";

		$result = $this->db->query($query, array(
			'PersonNewBorn_id' => $data['PersonNewBorn_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function savePersonBirthTrauma($data) {

		foreach($data['PersonBirthTraumaData'] as $item){
			if(empty($item['Diag_id'])){
				continue;
			}
			switch($item['RecordStatus_Code']){
				case 0:
				case 2:
					$procedure = "p_PersonBirthTrauma_ins";

					$queryParams = array(
						'BirthTraumaType_id' => $item['BirthTraumaType_id'],
						'PersonNewBorn_id' =>$data['PersonNewBorn_id'],
						'PersonBirthTrauma_setDate'=>date('Y-m-d', strtotime($item['PersonBirthTrauma_setDate'])),
						'PersonBirthTrauma_id' => NULL,
						'PersonBirthTrauma_Comment' => (!empty($item['PersonBirthTrauma_Comment']) ? $item['PersonBirthTrauma_Comment'] : NULL),
						'pmUser_id' => $data['pmUser_id'],
						'Server_id'=>$data['Server_id'],
						'Diag_id' => $item['Diag_id']
					);
					if($item['RecordStatus_Code']==2){
						$queryParams['PersonBirthTrauma_id']=$item['PersonBirthTrauma_id'];
						$procedure = "p_PersonBirthTrauma_upd";
					}
					$query = "
						select
							PersonBirthTrauma_id as \"PersonBirthTrauma_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from " . $procedure . "(
							PersonNewBorn_id := :PersonNewBorn_id,
							PersonBirthTrauma_id := :PersonBirthTrauma_id,
							PersonBirthTrauma_setDate := :PersonBirthTrauma_setDate,
							BirthTraumaType_id := :BirthTraumaType_id,
							Diag_id := :Diag_id,
							PersonBirthTrauma_Comment := :PersonBirthTrauma_Comment,
							Server_id := :Server_id,
							pmUser_id := :pmUser_id
						)
					";
					/*echo getDebugSQL($query, $queryParams);exit();*/
					$result = $this->db->query($query, $queryParams);
					break;

				case 3:
					$queryParams = array('PersonBirthTrauma_id'=>$item['PersonBirthTrauma_id']);
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PersonBirthTrauma_del(
							PersonBirthTrauma_id := :PersonBirthTrauma_id
						)
					";
					/*echo getDebugSQL($query, $queryParams);exit();*/
					$result = $this->db->query($query, $queryParams);
					break;
			}
		}

	}

	/**
	 * Указание КВС ребенка в специфике новорожденного
	 */
	function setPersonNewBornEvnPS($data) {
		$queryParams = array(
			'PersonNewBorn_id' => $data['PersonNewBorn_id'],
			'EvnPS_id' => !empty($data['EvnPS_id'])?$data['EvnPS_id']:null,
		);
		$query = "
			update PersonNewBorn
			set EvnPS_id = :EvnPS_id
			where PersonNewBorn_id = :PersonNewBorn_id
		";
		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении КВС ребенка в специфике новорожденного');
		}
		return $resp;
	}

	/**
	 * Обвноеление PersonChild при редактировании PersonNewBorn
	 */
	function updatePersonChild($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'PersonChild_CountChild' => $data['PersonNewBorn_CountChild'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$params['PersonChild_id'] = $this->getFirstResultFromQuery("
			select PersonChild_id as \"PersonChild_id\" from v_PersonChild where Person_id = :Person_id
			limit 1
		", $params, true);
		if ($params['PersonChild_id'] === false) {
			return $this->createError('','Ошибка при получении специфики детства');
		}
		return array($this->saveObject('PersonChild', $params));
	}

	/**
	 * Обновление флага высокого риска у новорожденного
	 */
	function updatePersonNewbornIsHighRisk($data) {
		$params = array('PersonNewBorn_id' => $data['PersonNewBorn_id']);

		$query = "
			with ObservDataList as (
				select
					EOD.EvnObserv_id,
					EOD.EvnObservData_Value,
					EOD.ObservParamType_id,
					EONB.EvnObservNewBorn_setDate as EvnObserv_setDate,
					OTT.ObservTimeType_Code
				from v_EvnObservData EOD
				inner join v_EvnObservNewBorn EONB on EONB.EvnObservNewBorn_id = EOD.EvnObserv_id
				left join v_ObservTimeType OTT on OTT.ObservTimeType_id = EONB.ObservTimeType_id
				where EONB.PersonNewBorn_id = :PersonNewBorn_id
			)

			select
				PNB.PersonNewborn_Weight as \"PersonNewborn_Weight\",
				case when exists(
						select * from v_PersonBirthTrauma PBT
						where PBT.PersonNewBorn_id = PNB.PersonNewBorn_id and BirthTraumaType_id in (3,4)
				) then 2 else 1 end as \"PersonNewBorn_IsTrauma\",
				PNB.PersonNewborn_IsBleeding as \"PersonNewborn_IsBleeding\",
				PNB.PersonNewborn_BloodBili as \"PersonNewborn_BloodBili\",
				PNB.PersonNewborn_BloodHemoglo as \"PersonNewborn_BloodHemoglo\",
				PNB.PersonNewborn_BloodEryth as \"PersonNewborn_BloodEryth\",
				PNB.PersonNewborn_BloodHemato as \"PersonNewborn_BloodHemato\",
				LastUrineVolume.Value as \"EvnObserv_UrineVolume\",
				LastWeight.Value as \"EvnObserv_Weight\",
				LastCheckupReact.Value as \"EvnObserv_CheckupReact\",
				LastEyeReact.Value as \"EvnObserv_EyeReact\",
				LastRespiratoryRate.Value as \"EvnObserv_RespiratoryRate\",
				LastHeartRate.Value as \"EvnObserv_HeartRate\"
			from 
				v_PersonNewBorn PNB
				left join lateral(
					select EvnObservData_Value as Value
					from ObservDataList
					where ObservParamType_id = 8 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
					limit 1
				) LastUrineVolume on true
				left join lateral(
					select EvnObservData_Value as Value
					from ObservDataList
					where ObservParamType_id = 6 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
					limit 1
				) LastWeight on true
				left join lateral(
					select EvnObservData_Value as Value
					from ObservDataList
					where ObservParamType_id = 13 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
					limit 1
				) LastCheckupReact on true
				left join lateral(
					select EvnObservData_Value as Value
					from ObservDataList
					where ObservParamType_id = 13 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
					limit 1
				) LastEyeReact on true
				left join lateral(
					select EvnObservData_Value as Value
					from ObservDataList
					where ObservParamType_id = 5 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
					limit 1
				) LastRespiratoryRate on true
				left join lateral(
					select EvnObservData_Value as Value
					from ObservDataList
					where ObservParamType_id = 3 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
					limit 1
				) LastHeartRate on true
			where
				PNB.PersonNewBorn_id = :PersonNewBorn_id
		";
		$info = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($info)) {
			return $this->createError('','Ошибка при получении информации о новорожденном');
		}

		$IsHighRisk = false;
		if (!empty($info['PersonNewborn_Weight']) && $info['PersonNewborn_Weight'] < 2000) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewBorn_IsTrauma']) && $info['PersonNewBorn_IsTrauma'] == 2) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewborn_IsBleeding']) && $info['PersonNewborn_IsBleeding'] == 2) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewborn_BloodBili']) && $info['PersonNewborn_BloodBili'] > 250) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewborn_BloodHemoglo']) && $info['PersonNewborn_BloodHemoglo'] < 100) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewborn_BloodEryth']) && $info['PersonNewborn_BloodEryth'] > 6) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewborn_BloodHemato']) && $info['PersonNewborn_BloodHemato'] > 72) {
			$IsHighRisk = true;
		}
		if (!empty($info['EvnObserv_UrineVolume']) && !empty($info['EvnObserv_Weight'])) {
			$DailyDiuresis = $info['EvnObserv_UrineVolume']/$info['EvnObserv_Weight']/24;
			if ($DailyDiuresis < 2 || $DailyDiuresis > 5) {
				$IsHighRisk = true;
			}
		}
		if (!empty($info['EvnObserv_CheckupReact']) && $info['EvnObserv_CheckupReact'] == 2) {
			$IsHighRisk = true;
		}
		if (!empty($info['EvnObserv_EyeReact']) && $info['EvnObserv_EyeReact'] == 2) {
			$IsHighRisk = true;
		}
		if (!empty($info['EvnObserv_RespiratoryRate']) && $info['EvnObserv_RespiratoryRate'] > 60) {
			$IsHighRisk = true;
		}
		if (!empty($info['EvnObserv_RespiratoryRate']) && ($info['EvnObserv_HeartRate'] < 120 || $info['EvnObserv_HeartRate'] > 145)) {
			$IsHighRisk = true;
		}

		$params = array(
			'PersonNewBorn_id' => $data['PersonNewBorn_id'],
			'PersonNewBorn_IsHighRisk' => $IsHighRisk ? 2 : 1,
		);
		$query = "
			update PersonNewBorn
			set PersonNewBorn_IsHighRisk = :PersonNewBorn_IsHighRisk
			where PersonNewBorn_id = :PersonNewBorn_id
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении флага высого риска');
		}

		if (count($resp) > 0)
			return $resp[0]['ResultHighRisk'];

		return $resp;
	}

	function GetHighRisk($data){
		$query = "
			select Replace(gethighrisk(pcc.personnewborn_id, ps.person_id), 'ВИЧ-инфекция у матери<br>', '') as \"ResultHighRisk\"
			from v_personstate ps
				left join lateral(
					select
						pc.personnewborn_id as personnewborn_id
					from
						v_personnewborn pc
					where
							pc.person_id=ps.person_id
					order by pc.personnewborn_insdt desc
					limit 1
				) pcc on true
			where
				ps.person_id = :Person_id and
				extract(year from age(getdate(), ps.person_birthday)) = 0;
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении флага высого риска');
		}
		if (count($resp) > 0)
			return $resp[0]['ResultHighRisk'];

		return null;
	}

	function loadNewBornBlood($data) {
		$query = "
			select 
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				UslugaComplex_Code as \"UslugaComplex_Code\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				EvnXml_id as \"EvnXml_id\",
				UslugaTest_setDT as \"UslugaTest_setDT\",
				PersonNewborn_id as \"PersonNewborn_id\",
				EvnPS_id as \"EvnPS_id\"
			from (
				select 
					ROW_NUMBER() OVER(PARTITION BY case when UC.UslugaComplex_Code = 'A09.05.022.002' then 'A09.05.021' else UC.UslugaComplex_Code end ORDER BY COALESCE(EPS.EvnPS_id,0) desc, UT.UslugaTest_setDT desc, pnb.PersonNewborn_updDT desc) num,
					EUP.EvnUslugaPar_rid,
					case when UC.UslugaComplex_Code = 'A09.05.022.002' then 'A09.05.021' else UC.UslugaComplex_Code end as UslugaComplex_Code,
					coalesce(
						case when UC.UslugaComplex_Code in ('A09.05.021','A09.05.022.002') then pnb.personnewborn_bloodbili end,
						case when UC.UslugaComplex_Code in ('A09.05.003') then pnb.personnewborn_bloodhemoglo end,
						case when UC.UslugaComplex_Code in ('A08.05.003') then pnb.personnewborn_blooderyth end,
						case when UC.UslugaComplex_Code in ('A09.05.002') then pnb.personnewborn_bloodhemato end,
						case when UC.UslugaComplex_Code in ('A09.05.021','A09.05.022.002','A09.05.003') then CASE WHEN UT.UslugaTest_ResultValue~E'^\\\\d+,?\.?(\\\\d+)?$' and length(UT.UslugaTest_ResultValue)<6 THEN cast(to_number(ltrim(rtrim(replace(replace(UT.UslugaTest_ResultValue,'.',','),'>',''))), '99G999D999S') as int) end
							else CASE WHEN UT.UslugaTest_ResultValue~E'^\\\\d+,?\.?(\\\\d+)?$' and length(UT.UslugaTest_ResultValue)<6 THEN to_number(ltrim(rtrim(replace(replace(UT.UslugaTest_ResultValue,'.',','),'>',''))), '99G999D999S') end end) as UslugaTest_ResultValue,
					EvnXml_id,
					UT.UslugaTest_setDT,
					pnb.PersonNewborn_id,
					EPS.EvnPS_id
				from 
					v_PersonState PS inner join 
					v_EvnUslugaPar EUP on EUP.Person_id = PS.Person_id INNER JOIN
					v_UslugaTest UT ON UT.UslugaTest_pid = EUP.EvnUslugaPar_id AND UT.UslugaTest_ResultValue IS NOT NULL INNER JOIN
					UslugaComplex UC ON UC.UslugaComplex_id = UT.UslugaComplex_id and UC.UslugaComplex_Code in ('A09.05.021','A09.05.022.002','A09.05.003','A08.05.003','A09.05.002')
					inner join lateral (
						select EvnXml.EvnXml_id
						from 
							v_EvnXml EvnXml left join 
							XmlTemplateHtml xth on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
						where EvnXml.Evn_id = UT.UslugaTest_pid
						order by EvnXml_insDT desc
						limit 1
					) doc on true left join
					v_PersonNewborn pnb on pnb.Person_id = PS.Person_id left join
					v_EvnPS EPS on EPS.EvnPS_id = EUP.EvnUslugaPar_rid and EPS.EvnPS_id = pnb.EvnPS_id
				where (1 = 1)
					and PS.Person_id = :Person_id
					and (PS.Person_BirthDay >= dateadd('DAY',-365,GETDATE()) or EPS.EvnPS_id is not null)
			) as T
			where T.num = 1
		";

		return $this->queryResult($query, array(
			'Person_id' => $data['Person_id']
		));
	}
}
