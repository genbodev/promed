<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonNewBorn_model extends swModel {
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
				pnb.PersonNewborn_id,
				pnb.Person_id,
				pnb.BirthSvid_id,
				pnb.EvnPS_id,
				pnb.BirthSpecStac_id,
				pnb.EvnSection_mid,
				pnb.FeedingType_id,
				pnb.ChildTermType_id,
				case when pnb.PersonNewborn_IsAidsMother = 2 then 1 else 0 end as PersonNewborn_IsAidsMother,
				pnb.PersonNewborn_CountChild,
				pnb.ChildPositionType_id,
				case when pnb.PersonNewborn_IsRejection = 2 then 1 else 0 end as PersonNewborn_IsRejection,
				case when pnb.PersonNewborn_IsHighRisk = 2 then 1 else 0 end as PersonNewborn_IsHighRisk,
				pnb.NewbornWardType_id,
				case when pnb.PersonNewborn_IsBleeding = 2 then 1 else 0 end as PersonNewborn_IsBleeding,
				case when pnb.PersonNewborn_IsNeonatal = 2 then 1 else 0 end as PersonNewborn_IsNeonatal,
				case when pnb.PersonNewborn_IsAudio = 2 then 1 else 0 end as PersonNewborn_IsAudio,
				case when pnb.PersonNewborn_IsBCG = 2 then 1 else 0 end as PersonNewborn_IsBCG,
				convert(varchar(10), pnb.PersonNewborn_BCGDate, 120) as PersonNewborn_BCGDate,
				pnb.PersonNewborn_BCGSer,
				pnb.PersonNewborn_BCGNum,
				case when pnb.PersonNewborn_IsHepatit = 2 then 1 else 0 end as PersonNewborn_IsHepatit,
				convert(varchar(10), pnb.PersonNewborn_HepatitDate, 120) as PersonNewborn_HepatitDate,
				pnb.PersonNewborn_HepatitSer,
				pnb.PersonNewborn_HepatitNum,
				pnb.PersonNewborn_Head,
				pnb.PersonNewborn_Breast,
				pnb.PersonNewborn_Height,
				pnb.PersonNewborn_Weight,
				case when pnb.PersonNewborn_IsBreath = 2 then 1 else 0 end as PersonNewborn_IsBreath,
				case when pnb.PersonNewborn_IsHeart = 2 then 1 else 0 end as PersonNewborn_IsHeart,
				case when pnb.PersonNewborn_IsPulsation = 2 then 1 else 0 end as PersonNewborn_IsPulsation,
				case when pnb.PersonNewborn_IsMuscle = 2 then 1 else 0 end as PersonNewborn_IsMuscle,
				pnb.PersonNewborn_BloodBili,
				pnb.PersonNewborn_BloodHemoglo,
				pnb.PersonNewborn_BloodEryth,
				pnb.PersonNewborn_BloodHemato
			from
				v_PersonNewborn pnb with (nolock)
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
			select top 1
				case
				 	when specEPS.EvnPS_id is null or specEPS.EvnPS_id = :EvnPS_id then 1 else 0
				end as editPersonNewBorn
			from
				Person P with(nolock)
				left join v_PersonNewBorn PNB with(nolock) on PNB.Person_id = P.Person_id
				left join v_EvnPS specEPS with(nolock) on specEPS.EvnPS_id = PNB.EvnPS_id
			where
				P.Person_id = :Person_id
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
			$where .= " and (ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) like :Person_FIO";
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
					$where .= " and (eps.EvnPS_id is null or eps.EvnPS_disDT is not null) and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 3:
					$where .= " and eps.EvnPS_id is not null and eps.EvnPS_disDT is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				
				case 2:
					$where .= " and (ps.Person_deadDT is not null or isnull(PntDeathSvid.PntDeathSvid_id,'0')!='0')";
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
			-- variables
			declare @getDT date = dbo.tzGetDate();
			-- end variables

			select
				-- select
				case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as isHighRisk,
				ps.Person_SurName +' '+LEFT(isnull(ps.Person_FirName,''),1)+' '+LEFT(isnull(ps.Person_SecName,''),1) as Person_FIO,
				convert(varchar, ps.Person_BirthDay,104) AS Person_BirthDay ,
				ISNULL(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) as PersonNewBorn_Weight,
				BirthSvid.BirthSvid_id,
				BirthSvid.BirthSvid_Num as BirthSvid,
				PntDeathSvid.PntDeathSvid_id,
				PntDeathSvid.PntDeathSvid_Num as DeathSvid,
				ps.Server_id AS Server_id,
				pch.Person_id AS Person_cid,
				mPS.Person_id as Person_mid,
				mLP.Lpu_Nick as LpuBirth,
				lp.Lpu_Nick as LpuHosp,
				pch.PersonNewborn_id,
				cast(agp.NewbornApgarRate_Values as int) as NewbornApgarRate_Values,
				case
					when ps.Person_deadDT is not null or isnull(PntDeathSvid.PntDeathSvid_id,'0')!='0' then 'Умер'
					when (eps.EvnPS_id is not null and eps.EvnPS_disDT is not null) or eps.EvnPS_id is null then 'Выписан'
					else 'В стационаре'
				end as State,
				case when isnull(pch.PersonNewBorn_IsNeonatal,1)=2 then 'true' else '' end as PersonNewBorn_IsNeonatal,
				case when isnull(pch.PersonNewborn_IsAidsMother,1)=2 then 'true' else '' end as PersonNewborn_IsAidsMother,
				case when isnull(pch.PersonNewborn_IsRejection,1)=2 then 'true' else '' end as PersonNewborn_IsRejection,
				Deputy.Deputy_FIO,
				Deputy.Deputy_Addres,
				Deputy.Deputy_Phone
				-- end select
			from
				-- from
				v_PersonState ps with(nolock)
				inner join v_PersonNewBorn pch WITH (NOLOCK) on ps.Person_id = pch.Person_id
				left join v_BirthSpecStac BSS with(nolock) on pch.birthspecstac_id = BSS.BirthSpecStac_id
				outer apply(
					select  top 1
						ISNULL(psd.Person_SurName,'') + ' ' + ISNULL(psd.Person_FirName,'') + ' ' + ISNULL(psd.Person_SecName,'') as Deputy_FIO,
						ISNULL(Adr.Address_Address,'') as Deputy_Addres,
						ISNULL(psd.Person_Phone,'') as Deputy_Phone
					from v_PersonDeputy PD with(nolock)
					inner join v_PersonState psd with(nolock) on psd.Person_id = PD.Person_pid and PD.Person_id = ps.Person_id
					left join v_Address Adr with(nolock) on Adr.Address_id = psd.PAddress_id
				)Deputy
				outer apply(
					select 
						top 1 1 as Trauma
					from
						v_PersonBirthTrauma PC with (nolock)
					where (1 = 1)
						and PC.PersonNewBorn_id = pch.PersonNewBorn_id
				)tr
				outer apply (
					select top 1 a.NewbornApgarRate_Values 
					from v_NewbornApgarRate a 
					where a.PersonNewborn_id = pch.PersonNewborn_id and a.NewbornApgarRate_Values is not null
					order by NewbornApgarRate_Time desc, NewbornApgarRate_insDT desc
				)agp
				left join v_EvnSection mES with(nolock) on mES.EvnSection_id = BSS.EvnSection_id
				left join v_PersonRegister mPR with(nolock) on mPR.PersonRegister_id = BSS.PersonRegister_id
				left join v_PersonState mPS with(nolock) on mPS.Person_id = isnull(mPR.Person_id, mES.Person_id)
				left join v_Lpu_all mLP with(nolock) on mLP.Lpu_id = isnull(BSS.Lpu_id, mES.Lpu_id)
				left join v_EvnPS eps with(nolock) on eps.EvnPS_id = pch.EvnPS_id
				left join v_Lpu_all lp with(nolock) on lp.lpu_id=eps.Lpu_id
				outer apply(SELECT TOP 1 pds.PntDeathSvid_id,pds.PntDeathSvid_Num FROM dbo.v_PntDeathSvid pds WITH (NOLOCK) WHERE pds.Person_rid = pch.Person_id ) as PntDeathSvid
				outer apply(SELECT TOP 1 BirthSvid_id,BirthSvid_Num FROM dbo.v_BirthSvid bs WITH (NOLOCK) WHERE bs.Person_cid = pch.Person_id) AS BirthSvid
				outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =pch.Person_id and pw.WeightMeasureType_id = 1) W
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
		if ('ufa' == $this->regionNick) {
			$query = "
			select 
				D.Diag_Code,
				PC.PersonBirthTrauma_id,
				PC.PersonNewBorn_id,
				convert(varchar, PC.PersonBirthTrauma_setDate, 104) AS PersonBirthTrauma_setDate ,
				D.Diag_Name,
				PC.Diag_id,
				PC.PersonBirthTrauma_Comment,
				PC.BirthTraumaType_id,
				1 as RecordStatus_Code,
				(case when pbt.BirthTraumaType_id is null then 2 else 1 end) as PersonBirthTrauma_IsEdit
			from
				v_PersonBirthTrauma PC with (nolock)
				inner join v_PersonNewBorn pnb with(nolock) on pnb.PersonNewBorn_id = PC.PersonNewBorn_id		
				left join v_Diag D with(nolock) on D.Diag_id = PC.Diag_id
				left join v_PersonBirthTrauma pbt with(nolock) on pbt.PersonNewborn_Id = pnb.PersonNewborn_pid and 
					pbt.PersonBirthTrauma_setDate=PC.PersonBirthTrauma_setDate and pbt.Diag_id=PC.Diag_id
			where (1 = 1)
				and PC.PersonNewBorn_id = :PersonNewBorn_id
				and PC.BirthTraumaType_id = :BirthTraumaType_id			
			";
		}else {
			$query = "
			select 
				D.Diag_Code,
				PC.PersonBirthTrauma_id,
				PC.PersonNewBorn_id,
				convert(varchar, PersonBirthTrauma_setDate, 104) AS PersonBirthTrauma_setDate ,
				D.Diag_Name,
				PC.Diag_id,
				PC.PersonBirthTrauma_Comment,
				PC.BirthTraumaType_id,
				1 as RecordStatus_Code
			from
				v_PersonBirthTrauma PC with (nolock)
				left join v_Diag D with(nolock) on D.Diag_id = PC.Diag_id
			where (1 = 1)
				and PC.PersonNewBorn_id = :PersonNewBorn_id
				and PC.BirthTraumaType_id = :BirthTraumaType_id
			
				
			";
		}
		switch($data['BirthTraumaType_id']){
			case 1:
				$query .="union all
				
			select D.Diag_Code,
				null as PersonBirthTrauma_id,
				null as PersonNewBorn_id,
				convert(varchar, ED.EvnDiag_setDate, 104) AS PersonBirthTrauma_setDate,
				D.Diag_Name,
				D.Diag_id,
				null as PersonBirthTrauma_Comment,
				:BirthTraumaType_id as BirthTraumaType_id,
				4 as RecordStatus_Code,
				1 as PersonBirthTrauma_IsEdit
			from v_EvnDiag ED with(nolock)
			inner join v_PersonNewBorn PNB with(nolock) on pnb.EvnPS_id = ED.EvnDiag_rid
			left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
			where PNB.PersonNewBorn_id = :PersonNewBorn_id
			and d.Diag_Code >='P10' and d.Diag_Code<='P15' and ED.EvnClass_id=33
";
				break;
			case 2:
				$query .="union all
				
			select D.Diag_Code,
				null as PersonBirthTrauma_id,
				null as PersonNewBorn_id,
				convert(varchar, ED.EvnDiag_setDate, 104) AS PersonBirthTrauma_setDate,
				D.Diag_Name,
				D.Diag_id,
				null as PersonBirthTrauma_Comment,
				:BirthTraumaType_id as BirthTraumaType_id,
				4 as RecordStatus_Code,
				1 as PersonBirthTrauma_IsEdit
			from v_EvnDiag ED with(nolock)
			inner join v_PersonNewBorn PNB with(nolock) on pnb.EvnPS_id = ED.EvnDiag_rid
			left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
			where PNB.PersonNewBorn_id = :PersonNewBorn_id
			and d.Diag_Code >='P15' and d.Diag_Code<='P99' and ED.EvnClass_id=33";
				break;
			case 3:
				$query .="union all
				
			select D.Diag_Code,
				null as PersonBirthTrauma_id,
				null as PersonNewBorn_id,
				convert(varchar, ED.EvnDiag_setDate, 104) AS PersonBirthTrauma_setDate,
				D.Diag_Name,
				D.Diag_id,
				null as PersonBirthTrauma_Comment,
				:BirthTraumaType_id as BirthTraumaType_id,
				4 as RecordStatus_Code,
				1 as PersonBirthTrauma_IsEdit
			from v_EvnDiag ED with(nolock)
			inner join v_PersonNewBorn PNB with(nolock) on pnb.EvnPS_id = ED.EvnDiag_rid
			left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
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
			select top 1
				PC.Diag_id,
				PC.PersonNewBorn_id,
				PC.PersonBirthTrauma_id,
				PC.BirthTraumaType_id,
				PC.Server_id,
				PC.PersonBirthTrauma_Comment,
				convert(varchar(10),PersonBirthTrauma_setDate,104) as PersonBirthTrauma_setDate
			from
				v_PersonBirthTrauma PC with (nolock)
			where (1 = 1)
				and PC.PersonBirthTrauma_id = :PersonBirthTrauma_id
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
			select top 1
				PC.PersonNewBorn_id,
				PC.ChildTermType_id,
				PC.FeedingType_id,
				PC.NewBornWardType_id,
				ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
				ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
				convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
				ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
				ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
				convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
				PC.PersonNewBorn_IsAidsMother,
				PC.PersonNewBorn_IsHepatit,
				PC.PersonNewBorn_IsBCG,
				IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
				IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
				IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
				IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
				PS.PersonEvn_id,
				PC.Person_id,
				PC.Server_id,
				PC.EvnPS_id,
				PC.EvnSection_mid,
				PC.PersonNewBorn_IsBleeding,
				PC.PersonNewBorn_IsNeonatal,
				PC.PersonNewBorn_IsAudio,
				PersonNewBorn_CountChild,
				ChildPositionType_id,
				PersonNewBorn_IsRejection,
				PC.PersonNewBorn_Head,
				H.PersonHeight_Height as PersonNewBorn_Height, W.PersonWeight_Weight as PersonNewBorn_Weight,
				PC.PersonNewBorn_Breast,
				convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
				PC.PersonNewborn_BloodBili,
				PC.PersonNewborn_BloodHemoglo,
				PC.PersonNewborn_BloodEryth,
				PC.PersonNewborn_BloodHemato,
				PC.RefuseType_pid,
				PC.RefuseType_aid,
				PC.RefuseType_bid,
				PC.RefuseType_gid
			from
				v_PersonNewBorn PC with (nolock)
				left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
				outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
				outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
				left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
				left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
				left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
				left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
			where (1 = 1)
				and PC.Person_id = :Person_id
		";		
		
		if ('ufa' == $this->regionNick) {
			/*
			if (!empty($data['EvnPS_id'])) {
				$where = " and PC.EvnPS_id=:EvnPS_id ";
			}*/
			$query = "
			select top 1
				PC.PersonNewBorn_id,
				PC.ChildTermType_id,
				PC.FeedingType_id,
				PC.NewBornWardType_id,
				ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
				ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
				convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
				ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
				ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
				convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
				PC.PersonNewBorn_IsAidsMother,
				PC.PersonNewBorn_IsHepatit,
				PC.PersonNewBorn_IsBCG,
				IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
				IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
				IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
				IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
				PS.PersonEvn_id,
				PC.Person_id,
				PC.Server_id,
				PC.EvnPS_id,
				PC.EvnSection_mid,
				PC.PersonNewBorn_IsBleeding,
				PC.PersonNewBorn_IsNeonatal,
				PC.PersonNewBorn_IsAudio,
				PC.PersonNewBorn_CountChild,
				PC.ChildPositionType_id,
				PC.PersonNewBorn_IsRejection,
				PC.PersonNewBorn_Head,
				PC.PersonNewBorn_Height as PersonNewBorn_Height, PC.PersonNewBorn_Weight as PersonNewBorn_Weight,
				PC.PersonNewBorn_Breast,
				convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
				PC.PersonNewborn_BloodBili,
				PC.PersonNewborn_BloodHemoglo,
				PC.PersonNewborn_BloodEryth,
				PC.PersonNewborn_BloodHemato,

				(case when pnlast.ChildTermType_id is null then 2 else 1 end) as ChildTermType_id_IsEdit,
				(case when pnlast.FeedingType_id is null then 2 else 1 end) as FeedingType_id_IsEdit,
				(case when pnlast.NewBornWardType_id is null then 2 else 1 end) as NewBornWardType_id_IsEdit,
				(case when pnlast.PersonNewBorn_BCGNum is null then 2 else 1 end) as PersonNewBorn_BCGNum_IsEdit,
				(case when pnlast.PersonNewBorn_BCGSer is null then 2 else 1 end) as PersonNewBorn_BCGSer_IsEdit,
				(case when pnlast.PersonNewBorn_BCGDate is null then 2 else 1 end) as PersonNewBorn_BCGDate_IsEdit,
				(case when pnlast.PersonNewBorn_HepatitNum is null then 2 else 1 end) as PersonNewBorn_HepatitNum_IsEdit,
				(case when pnlast.PersonNewBorn_HepatitSer is null then 2 else 1 end) as PersonNewBorn_HepatitSer_IsEdit,
				(case when pnlast.PersonNewBorn_HepatitDate is null then 2 else 1 end) as PersonNewBorn_HepatitDate_IsEdit,
				(case when pnlast.PersonNewBorn_IsAidsMother is null then 2 else 1 end) as PersonNewBorn_IsAidsMother_IsEdit,
				(case when pnlast.PersonNewBorn_IsHepatit is null then 2 else 1 end) as PersonNewBorn_IsHepatit_IsEdit,
				(case when pnlast.PersonNewBorn_IsBCG is null then 2 else 1 end) as PersonNewBorn_IsBCG_IsEdit,
				(case when pnlast.PersonNewBorn_IsBreath is null then 2 else 1 end) as PersonNewBorn_IsBreath_IsEdit,
				(case when pnlast.PersonNewBorn_IsHeart is null then 2 else 1 end) as PersonNewBorn_IsHeart_IsEdit,
				(case when pnlast.PersonNewBorn_IsPulsation is null then 2 else 1 end) as PersonNewBorn_IsPulsation_IsEdit,
				(case when pnlast.PersonNewBorn_IsMuscle is null then 2 else 1 end) as PersonNewBorn_IsMuscle_IsEdit,
				(case when pnlast.PersonNewBorn_IsBleeding is null then 2 else 1 end) as PersonNewBorn_IsBleeding_IsEdit,
				(case when pnlast.PersonNewBorn_IsNeonatal is null then 2 else 1 end) as PersonNewBorn_IsNeonatal_IsEdit,
				(case when pnlast.PersonNewBorn_IsAudio is null then 2 else 1 end) as PersonNewBorn_IsAudio_IsEdit,
				(case when pnlast.PersonNewBorn_CountChild is null then 2 else 1 end) as PersonNewBorn_CountChild_IsEdit,
				(case when pnlast.ChildPositionType_id is null then 2 else 1 end) as ChildPositionType_id_IsEdit,
				(case when pnlast.PersonNewBorn_IsRejection is null then 2 else 1 end) as PersonNewBorn_IsRejection_IsEdit,
				(case when pnlast.PersonNewBorn_Head is null then 2 else 1 end) as PersonNewBorn_Head_IsEdit,
				(case when pnlast.PersonNewBorn_Weight is null then 2 else 1 end) as PersonNewBorn_Weight_IsEdit,
				(case when pnlast.PersonNewBorn_Breast is null then 2 else 1 end) as PersonNewBorn_Breast_IsEdit,
				(case when pnlast.PersonNewborn_BloodBili is null then 2 else 1 end) as PersonNewborn_BloodBili_IsEdit,
				(case when pnlast.PersonNewborn_BloodHemoglo is null then 2 else 1 end) as PersonNewborn_BloodHemoglo_IsEdit,
				(case when pnlast.PersonNewborn_BloodEryth is null then 2 else 1 end) as PersonNewborn_BloodEryth_IsEdit,
				(case when pnlast.PersonNewborn_BloodHemato is null then 2 else 1 end) as PersonNewborn_BloodHemato_IsEdit,
				pnlast.PersonNewborn_Id as PersonNewborn_Id_Last
			from
				v_PersonNewBorn PC with (nolock)
				left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
				outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
				outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
				left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
				left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
				left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
				left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
				outer apply(select top 1
					pnl.PersonNewborn_Id,
					pnl.ChildTermType_id,
					pnl.FeedingType_id,
					pnl.NewBornWardType_id,
					pnl.PersonNewBorn_BCGNum as PersonNewBorn_BCGNum,
					pnl.PersonNewBorn_BCGSer as PersonNewBorn_BCGSer,
					convert(varchar(10),pnl.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
					pnl.PersonNewBorn_HepatitNum as PersonNewBorn_HepatitNum,
					pnl.PersonNewBorn_HepatitSer as PersonNewBorn_HepatitSer,
					convert(varchar(10),pnl.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
					pnl.PersonNewBorn_IsAidsMother,
					pnl.PersonNewBorn_IsHepatit,
					pnl.PersonNewBorn_IsBCG,
					pnl.PersonNewBorn_IsBreath,
					pnl.PersonNewBorn_IsHeart,
					pnl.PersonNewBorn_IsPulsation,
					pnl.PersonNewBorn_IsMuscle,
					pnl.PersonNewBorn_IsBleeding,
					pnl.PersonNewBorn_IsNeonatal,
					pnl.PersonNewBorn_IsAudio,
					pnl.PersonNewBorn_CountChild,
					pnl.ChildPositionType_id,
					pnl.PersonNewBorn_IsRejection,
					pnl.PersonNewBorn_Head,
					pnl.PersonNewBorn_Height as PersonNewBorn_Height, pnl.PersonNewBorn_Weight as PersonNewBorn_Weight,
					pnl.PersonNewBorn_Breast,
					pnl.PersonNewborn_BloodBili,
					pnl.PersonNewborn_BloodHemoglo,
					pnl.PersonNewborn_BloodEryth,
					pnl.PersonNewborn_BloodHemato
				from v_PersonNewBorn pnl with(nolock) where pnl.person_id =PC.Person_id and not pnl.EvnPS_id=PC.EvnPS_id order by pnl.PersonNewborn_insDT desc) pnlast
			where (1 = 1)
				and PC.Person_id = :Person_id
				and PC.EvnPS_id=:EvnPS_id
			";
		}



		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'EvnPS_id' => $data['EvnPS_id']
		));
		
		if ( is_object($result) ) {

			$res = $result->result('array');

			if ('ufa' == $this->regionNick && count($res) == 0){
				$query = "
					select top 1
						PC.PersonNewBorn_id,
						PC.ChildTermType_id,
						PC.FeedingType_id,
						PC.NewBornWardType_id,
						ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
						ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
						convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
						ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
						ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
						convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
						PC.PersonNewBorn_IsAidsMother,
						PC.PersonNewBorn_IsHepatit,
						PC.PersonNewBorn_IsBCG,
						IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
						IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
						IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
						IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
						PS.PersonEvn_id,
						PC.Person_id,
						PC.Server_id,
						PC.EvnPS_id,
						PC.EvnSection_mid,
						PC.PersonNewBorn_IsBleeding,
						PC.PersonNewBorn_IsNeonatal,
						PC.PersonNewBorn_IsAudio,
						PersonNewBorn_CountChild,
						ChildPositionType_id,
						PersonNewBorn_IsRejection,
						PC.PersonNewBorn_Head,
						{$select}
						PC.PersonNewBorn_Breast,
						convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
						PC.PersonNewborn_BloodBili,
						PC.PersonNewborn_BloodHemoglo,
						PC.PersonNewborn_BloodEryth,
						PC.PersonNewborn_BloodHemato
					from
						v_PersonNewBorn PC with (nolock)
						left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
						outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
						left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
						left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
						left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
						left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
					where (1 = 1)
						and PC.Person_id = :Person_id
						order by PC.PersonNewborn_insDT desc
				";

				$result = $this->db->query($query, array(
					'Person_id' => $data['Person_id']
				));

				$res = $result->result('array');
				if (count($res) > 0) {
					
					//создание новой специфики со значениями равной первой
					
					$query = "		
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
			
						exec p_PersonNewborn_Copy
							@PersonNewBorn_id_new = @Res output,
							@PersonNewBorn_id = :PersonNewBorn_id,
							@EvnPS_id = :EvnPS_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @Res as PersonNewBorn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					
					$queryParams['PersonNewBorn_id'] = $res[0]['PersonNewBorn_id'];
					$queryParams['EvnPS_id'] = $data['EvnPS_id'];
					//$queryParams['EvnSection_id'] = $data['EvnSection_id'];

					//echo getDebugSQL($query, $queryParams);exit();
					$res = $this->queryResult($query, $queryParams);
					if (!is_array($res)) {
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о специфике)'));
					}
					if (!$this->isSuccessful($res)) {
						//$res[0]['PersonNewBorn_id'] = '1';

						$query = "
					select top 1
						PC.PersonNewBorn_id,
						PC.ChildTermType_id,
						PC.FeedingType_id,
						PC.NewBornWardType_id,
						ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
						ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
						convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
						ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
						ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
						convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
						PC.PersonNewBorn_IsAidsMother,
						PC.PersonNewBorn_IsHepatit,
						PC.PersonNewBorn_IsBCG,
						IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
						IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
						IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
						IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
						PS.PersonEvn_id,
						PC.Person_id,
						PC.Server_id,
						PC.EvnPS_id,
						PC.EvnSection_mid,
						PC.PersonNewBorn_IsBleeding,
						PC.PersonNewBorn_IsNeonatal,
						PC.PersonNewBorn_IsAudio,
						PersonNewBorn_CountChild,
						ChildPositionType_id,
						PersonNewBorn_IsRejection,
						PC.PersonNewBorn_Head,
						{$select}
						PC.PersonNewBorn_Breast,
						convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
						PC.PersonNewborn_BloodBili,
						PC.PersonNewborn_BloodHemoglo,
						PC.PersonNewborn_BloodEryth,
						PC.PersonNewborn_BloodHemato
					from
						v_PersonNewBorn PC with (nolock)
						left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
						outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
						left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
						left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
						left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
						left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
					where (1 = 1)
						and PC.PersonNewBorn_id = :PersonNewBorn_id
						order by PC.PersonNewborn_insDT asc
				";

						$result = $this->db->queryResult($query, array(
							'PersonNewBorn_id' => $res[0]['PersonNewBorn_id']
						));

						$res = $result->result('array');						
						
						//return $res;
					}					
					
				}
				
			}
			return $res;
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

		$whereEvnPS = '';
		if ('ufa' == $this->regionNick) {
			if (!empty($data['EvnPS_id'])) {
				$whereEvnPS = ' and PNB.EvnPS_id=:EvnPS_id ';
			}
		}

		$sql = "
			select top 1
				PNB.PersonNewBorn_id,
				PNB.BirthSpecStac_id,
				PNB.EvnPS_id,
				PNB.EvnSection_mid,
				ES.EvnSection_pid as EvnPS_mid,
				PNB.PersonNewBorn_IsHighRisk
			from v_PersonNewBorn PNB with(nolock)
			left join v_EvnSection ES with(nolock) on ES.EvnSection_id = PNB.EvnSection_mid
			where PNB.Person_id = :Person_id
			{$whereEvnPS}
		";
		$res =  $this->db->query($sql, array('Person_id'=>$data['Person_id'], 'EvnPS_id'=>$data['EvnPS_id']));
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
			select top 1
				convert(varchar(10), PS.Person_BirthDay, 120) as Person_BirthDay
			from v_PersonState PS with(nolock)
			where PS.Person_id = :Person_id
		", array('Person_id'=>$data['Person_id']));
		if (!is_array($personInfo)) {
			return $this->createError('','Ошибка при получении данных о ребёнке');
		}

		if ('ufa' == $this->regionNick && $data['PersonNewBorn_id'] == 1) {
			$data['PersonNewBorn_id'] = '';
			$queryParams['PersonNewBorn_id'] = '';
		}

		if ( !empty($data['PersonNewBorn_id'])) {
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonNewBorn_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonNewBorn_id = @Res output,
				@Person_id = :Person_id,
				@FeedingType_id = :FeedingType_id,
				@ChildTermType_id = :ChildTermType_id,
				@EvnSection_mid = :EvnSection_mid,
				@PersonNewBorn_Weight=:PersonNewBorn_Weight,
				@PersonNewBorn_Height=:PersonNewBorn_Height,
				@PersonNewBorn_Head=:PersonNewBorn_Head,
				@PersonNewBorn_Breast=:PersonNewBorn_Breast,
				@PersonNewBorn_IsAidsMother = :PersonNewBorn_IsAidsMother,
				@PersonNewBorn_IsAudio = :PersonNewBorn_IsAudio,
				@PersonNewBorn_IsBleeding = :PersonNewBorn_IsBleeding,
				@PersonNewBorn_IsNeonatal=:PersonNewBorn_IsNeonatal,
				@PersonNewBorn_IsBCG = :PersonNewBorn_IsBCG,
				@PersonNewBorn_BCGSer = :PersonNewBorn_BCGSer,
				@PersonNewBorn_BCGNum = :PersonNewBorn_BCGNum,
				@PersonNewBorn_BCGDate = :PersonNewBorn_BCGDate,
				@PersonNewBorn_IsHepatit = :PersonNewBorn_IsHepatit,
				@PersonNewBorn_HepatitSer = :PersonNewBorn_HepatitSer,
				@PersonNewBorn_HepatitNum = :PersonNewBorn_HepatitNum,
				@PersonNewBorn_HepatitDate = :PersonNewBorn_HepatitDate,
				@PersonNewBorn_CountChild = :PersonNewBorn_CountChild,
				@PersonNewBorn_IsBreath = :PersonNewBorn_IsBreath,
				@PersonNewBorn_IsHeart = :PersonNewBorn_IsHeart,
				@PersonNewBorn_IsPulsation = :PersonNewBorn_IsPulsation,
				@PersonNewBorn_IsMuscle = :PersonNewBorn_IsMuscle,
				@PersonNewBorn_IsHighRisk = :PersonNewBorn_IsHighRisk,
				@PersonNewborn_BloodBili = :PersonNewborn_BloodBili,
				@PersonNewborn_BloodHemoglo = :PersonNewborn_BloodHemoglo,
				@PersonNewborn_BloodEryth = :PersonNewborn_BloodEryth,
				@PersonNewborn_BloodHemato = :PersonNewborn_BloodHemato,
				@ChildPositionType_id = :ChildPositionType_id,
				@PersonNewBorn_IsRejection=:PersonNewBorn_IsRejection,
				@BirthSpecStac_id = :BirthSpecStac_id,
				@EvnPS_id = :EvnPS_id,
				@NewBornWardType_id = :NewBornWardType_id,
				@RefuseType_pid = :RefuseType_pid,
				@RefuseType_aid = :RefuseType_aid,
				@RefuseType_bid = :RefuseType_bid,
				@RefuseType_gid = :RefuseType_gid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonNewBorn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
							declare
								@ErrCode int,
								@ErrMessage varchar(4000),
								@Res bigint;
								set @Res = :NewbornApgarRate_id
							exec p_NewbornApgarRate_".$proc."
								@NewbornApgarRate_id = @Res,
								@PersonNewBorn_id = :PersonNewBorn_id,
								@NewbornApgarRate_Time=:NewbornApgarRate_Time,
								@NewbornApgarRate_Values =:NewbornApgarRate_Values,
								@NewbornApgarRate_Reflex = :NewbornApgarRate_Reflex,
								@NewbornApgarRate_ToneMuscle =:NewbornApgarRate_ToneMuscle,
								@NewbornApgarRate_SkinColor =:NewbornApgarRate_SkinColor,
								@NewbornApgarRate_Breath=:NewbornApgarRate_Breath,
								@NewbornApgarRate_Heartbeat=:NewbornApgarRate_Heartbeat,
								@Server_id = :Server_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;

							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						//echo getDebugSQL($query, $queryParams);exit();
						$result = $this->db->query($query, $queryParams);
					break;

					case 3:
						$queryParams = array('NewbornApgarRate_id'=>$item['NewbornApgarRate_id']);
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec p_NewbornApgarRate_del
								@NewbornApgarRate_id = :NewbornApgarRate_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;

							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonBirthTrauma_del
				@PersonBirthTrauma_id = :PersonBirthTrauma_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_NewbornApgarRate_del
				@NewbornApgarRate_id = :NewbornApgarRate_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			update PersonNewBorn with (rowlock) set BirthSpecStac_id = null where Person_id = :Person_id
		";

		$this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)
			declare @PD_id bigint

			set @PD_id = (select top 1 PersonDeputy_id from PersonDeputy with (nolock) where Person_id = ?)

			exec p_PersonDeputy_del
			@PersonDeputy_id = @PD_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_NewbornApgarRate_upd
				@NewbornApgarRate_id = :NewbornApgarRate_id,
				@PersonNewBorn_id = :PersonNewBorn_id,
				@NewbornApgarRate_Time=:NewbornApgarRate_Time,
				@NewbornApgarRate_Values =:NewbornApgarRate_Values,
				@NewbornApgarRate_Reflex = :NewbornApgarRate_Reflex,
				@NewbornApgarRate_ToneMuscle =:NewbornApgarRate_ToneMuscle,
				@NewbornApgarRate_SkinColor =:NewbornApgarRate_SkinColor,
				@NewbornApgarRate_Breath=:NewbornApgarRate_Breath,
				@NewbornApgarRate_Heartbeat=:NewbornApgarRate_Heartbeat,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare @ErrCode int
			declare @ErrMsg varchar(400)
			declare @PD_id bigint

			set @PD_id = (select top 1 PersonDeputy_id from PersonDeputy with (nolock) where Person_id = ?)

			exec p_PersonDeputy_del
			@PersonDeputy_id = @PD_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		$res = $this->db->query($sql, array($data['Person_id']));

		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonDeputy_ins
			@Server_id = ?,
			@Person_id = ?,
			@Person_pid = ?,
			@DeputyKind_id = ?,
			@pmUser_id = ?,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
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
		$procedure = "p_NewbornApgarRate_ins";

        $queryParams = array(
			'pmUser_id' => $data['pmUser_id'],
			'PersonNewBorn_id' =>$data['PersonNewBorn_id'],
			'Server_id'=>$data['Server_id']
        );
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_NewbornApgarRate_ins
				@NewbornApgarRate_id = null,
				@PersonNewBorn_id = :PersonNewBorn_id,
				@NewbornApgarRate_Values = 0,
				@NewbornApgarRate_Time=0,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				cast(NewbornApgarRate_Values as int) as NewbornApgarRate_Values,
				NewbornApgarRate_Reflex,
				NewbornApgarRate_ToneMuscle,
				NewbornApgarRate_SkinColor,
				NewbornApgarRate_Breath,
				NewbornApgarRate_Heartbeat,
				NewbornApgarRate_Time,
				PersonNewBorn_id,
				NewbornApgarRate_id,
				1 as RecordStatus_Code
			from
				v_NewbornApgarRate PC with (nolock)
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
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);

						set @Res = :PersonBirthTrauma_id;

						exec " . $procedure . "
							@PersonNewBorn_id = :PersonNewBorn_id,
							@PersonBirthTrauma_id = @Res output,
							@PersonBirthTrauma_setDate = :PersonBirthTrauma_setDate,
							@BirthTraumaType_id = :BirthTraumaType_id,
							@Diag_id = :Diag_id,
							@PersonBirthTrauma_Comment = :PersonBirthTrauma_Comment,
							@Server_id = :Server_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @Res as PersonBirthTrauma_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					/*echo getDebugSQL($query, $queryParams);exit();*/
					$result = $this->db->query($query, $queryParams);
				break;
			
				case 3:
					$queryParams = array('PersonBirthTrauma_id'=>$item['PersonBirthTrauma_id']);
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_PersonBirthTrauma_del
							@PersonBirthTrauma_id = :PersonBirthTrauma_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			set nocount on
			begin try

			update PersonNewBorn with (rowlock)
			set EvnPS_id = :EvnPS_id
			where PersonNewBorn_id = :PersonNewBorn_id

			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			select top 1 PersonChild_id from v_PersonChild with(nolock) where Person_id = :Person_id
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
			declare @PersonNewBorn_id bigint = :PersonNewBorn_id;
			with ObservDataList as (
				select
					EOD.EvnObserv_id,
					EOD.EvnObservData_Value,
					EOD.ObservParamType_id,
					EONB.EvnObservNewBorn_setDate as EvnObserv_setDate,
					OTT.ObservTimeType_Code
				from v_EvnObservData EOD with(nolock)
				inner join v_EvnObservNewBorn EONB with(nolock) on EONB.EvnObservNewBorn_id = EOD.EvnObserv_id
				left join v_ObservTimeType OTT with(nolock) on OTT.ObservTimeType_id = EONB.ObservTimeType_id
				where EONB.PersonNewBorn_id = @PersonNewBorn_id
			)
			select top 1
				PNB.PersonNewborn_Weight,
				case when exists(
						select * from v_PersonBirthTrauma PBT with(nolock)
						where PBT.PersonNewBorn_id = PNB.PersonNewBorn_id and BirthTraumaType_id in (3,4)
				) then 2 else 1 end as PersonNewBorn_IsTrauma,
				PNB.PersonNewborn_IsBleeding,
				PNB.PersonNewborn_BloodBili,
				PNB.PersonNewborn_BloodHemoglo,
				PNB.PersonNewborn_BloodEryth,
				PNB.PersonNewborn_BloodHemato,
				LastUrineVolume.Value as EvnObserv_UrineVolume,
				LastWeight.Value as EvnObserv_Weight,
				LastCheckupReact.Value as EvnObserv_CheckupReact,
				LastEyeReact.Value as EvnObserv_EyeReact,
				LastRespiratoryRate.Value as EvnObserv_RespiratoryRate,
				LastHeartRate.Value as EvnObserv_HeartRate
			from 
				v_PersonNewBorn PNB with(nolock)
				outer apply(
					select top 1 EvnObservData_Value as Value
					from ObservDataList with(nolock)
					where ObservParamType_id = 8 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
				) LastUrineVolume
				outer apply(
					select top 1 EvnObservData_Value as Value
					from ObservDataList with(nolock)
					where ObservParamType_id = 6 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
				) LastWeight
				outer apply(
					select top 1 EvnObservData_Value as Value
					from ObservDataList with(nolock)
					where ObservParamType_id = 13 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
				) LastCheckupReact
				outer apply(
					select top 1 EvnObservData_Value as Value
					from ObservDataList with(nolock)
					where ObservParamType_id = 13 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
				) LastEyeReact
				outer apply(
					select top 1 EvnObservData_Value as Value
					from ObservDataList with(nolock)
					where ObservParamType_id = 5 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
				) LastRespiratoryRate
				outer apply(
					select top 1 EvnObservData_Value as Value
					from ObservDataList with(nolock)
					where ObservParamType_id = 3 and isnumeric(EvnObservData_Value) = 1
					order by EvnObserv_setDate desc, ObservTimeType_Code desc
				) LastHeartRate
			where
				PNB.PersonNewBorn_id = @PersonNewBorn_id
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
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				update PersonNewBorn with(rowlock)
				set PersonNewBorn_IsHighRisk = :PersonNewBorn_IsHighRisk
				where PersonNewBorn_id = :PersonNewBorn_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении флага высого риска');
		}
		return $resp;
	}

	function GetHighRisk($data){
		$query = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			declare @PersonNewBorn_id bigint = null
			declare @Person_id bigint = :Person_id
			declare @ResultHighRisk varchar(4000) = ''
			declare @BirthDate Date = null
			set nocount on
			begin try
				select top 1 @BirthDate=PS.Person_BirthDay  from v_PersonState PS with (nolock) where PS.Person_id=@Person_id order by PersonState_insDT desc
				if (dateadd(year, 1, @BirthDate) > getdate())
				begin
					select top 1 @PersonNewBorn_id=PersonNewBorn_id from v_PersonNewBorn PC with (nolock) where Person_id=@Person_id order by PersonNewBorn_insDT desc
					select @ResultHighRisk=Replace([dbo].[GetHighRisk](@PersonNewBorn_id, @Person_id), 'ВИЧ-инфекция у матери<br>', '')
				end
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @ResultHighRisk as ResultHighRisk, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении флага высого риска');
		}

		return $resp[0]['ResultHighRisk'];
	}

	function loadNewBornBlood($data) {
		$query = "
			select 
				EvnUslugaPar_rid,
				UslugaComplex_Code,
				UslugaTest_ResultValue,
				EvnXml_id,
				UslugaTest_setDT,
				PersonNewborn_id,
				EvnPS_id
			from (
				select
					ROW_NUMBER() OVER(PARTITION BY case when UC.UslugaComplex_Code = 'A09.05.022.002' then 'A09.05.021' else UC.UslugaComplex_Code end ORDER BY isnull(EPS.EvnPS_id,0) desc, UT.UslugaTest_setDT desc, pnb.PersonNewborn_updDT desc) num,
					EUP.EvnUslugaPar_rid,
					case when UC.UslugaComplex_Code = 'A09.05.022.002' then 'A09.05.021' else UC.UslugaComplex_Code end as UslugaComplex_Code,
					coalesce(
						case when UC.UslugaComplex_Code in ('A09.05.021','A09.05.022.002') then pnb.personnewborn_bloodbili end,
						case when UC.UslugaComplex_Code in ('A09.05.003') then pnb.personnewborn_bloodhemoglo end,
						case when UC.UslugaComplex_Code in ('A08.05.003') then pnb.personnewborn_blooderyth end,
						case when UC.UslugaComplex_Code in ('A09.05.002') then pnb.personnewborn_bloodhemato end,
						case when UC.UslugaComplex_Code in ('A09.05.021','A09.05.022.002','A09.05.003') then cast(round(try_parse(ltrim(rtrim(replace(replace(UT.UslugaTest_ResultValue,',','.'),'>',''))) as float),0) as int) else try_parse(ltrim(rtrim(replace(replace(UT.UslugaTest_ResultValue,',','.'),'>',''))) as float) end) as UslugaTest_ResultValue,
					EvnXml_id,
					UT.UslugaTest_setDT,
					pnb.PersonNewborn_id,
					EPS.EvnPS_id
				from 
					v_PersonState PS with (nolock) inner join 
					v_EvnUslugaPar EUP with (nolock) on EUP.Person_id = PS.Person_id INNER JOIN
					v_UslugaTest UT with (nolock) ON UT.UslugaTest_pid = EUP.EvnUslugaPar_id AND UT.UslugaTest_ResultValue IS NOT NULL INNER JOIN
					UslugaComplex UC with (nolock) ON UC.UslugaComplex_id = UT.UslugaComplex_id and UC.UslugaComplex_Code in ('A09.05.021','A09.05.022.002','A09.05.003','A08.05.003','A09.05.002')
				cross apply (
					select top 1 EvnXml.EvnXml_id
					from 
						v_EvnXml EvnXml with (nolock) left join 
						XmlTemplateHtml xth with (nolock) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
					where EvnXml.Evn_id = UT.UslugaTest_pid
					order by EvnXml_insDT desc
				) doc
				left join v_PersonNewborn pnb with (nolock) on pnb.Person_id = PS.Person_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EUP.EvnUslugaPar_rid and EPS.EvnPS_id = pnb.EvnPS_id
				where (1 = 1)
					and PS.Person_id = :Person_id
					and (PS.Person_BirthDay >= dateadd(DAY,-365,GETDATE()) or EPS.EvnPS_id is not null)
			) as T
			where T.num = 1
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
}
