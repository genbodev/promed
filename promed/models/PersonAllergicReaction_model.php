<?php
class PersonAllergicReaction_model extends swModel {
	/**
	 * PersonAllergicReaction_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function deletePersonAllergicReaction($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonAllergicReaction_del
				@PersonAllergicReaction_id = :PersonAllergicReaction_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'PersonAllergicReaction_id' => $data['PersonAllergicReaction_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных о виде аллергической реакции человека
	 */
	function getPersonAllergicReactionViewData($data) {
		$query = "
			select
				PAR.Person_id,
				0 as Children_Count,
				PAR.PersonAllergicReaction_id,
				PAR.PersonAllergicReaction_id as AllergHistory_id,
				convert(varchar(10), PAR.PersonAllergicReaction_setDT, 104) as PersonAllergicReaction_setDate,
				ISNULL(ART.AllergicReactionType_Name, '') as AllergicReactionType_Name,
				ISNULL(ARL.AllergicReactionLevel_Name, '') as AllergicReactionLevel_Name,
				COALESCE(DM.DrugMnn_Name, PAR.PersonAllergicReaction_Kind, a.RUSNAME, t.NAME, '') as PersonAllergicReaction_Kind,
				--ISNULL(PU.pmUser_Name, '') as pmUser_Name,
				PAR.pmUser_insID
			from
				v_PersonAllergicReaction PAR with (nolock)
				inner join v_AllergicReactionType ART with (nolock) on ART.AllergicReactionType_id = PAR.AllergicReactionType_id
				inner join v_AllergicReactionLevel ARL with (nolock) on ARL.AllergicReactionLevel_id = PAR.AllergicReactionLevel_id
				left join v_DrugMnn DM with (nolock) on DM.DrugMnn_id = PAR.DrugMnn_id
				left join rls.actmatters a with (nolock) on a.actmatters_id = PAR.ACTMATTERS_ID
				left join rls.tradenames t with (nolock) on t.TRADENAMES_ID = PAR.TRADENAMES_ID				
				--left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(PAR.pmUser_updID, PAR.pmUser_insID)
			where
				PAR.Person_id = :Person_id
			order by
				PAR.PersonAllergicReaction_setDT
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
	 * @param $data
	 * @return bool|mixed
	 */
	function loadPersonAllergicReactionEditForm($data) {
		$query = "
			select top 1
				PAR.PersonAllergicReaction_id,
				PAR.Person_id,
				PAR.Server_id,
				PAR.AllergicReactionType_id,
				PAR.AllergicReactionLevel_id,
				PAR.DrugMnn_id,
				PAR.ACTMATTERS_ID as RlsActmatters_id,
				PAR.TRADENAMES_ID as TRADENAMES_ID,
				CASE
				    WHEN TRADENAMES_ID IS NOT NULL THEN 2
					WHEN ACTMATTERS_ID IS NOT NULL THEN 1
					ELSE 1
				END AS PersonAllergicReactionType_value,
				ISNULL(PAR.PersonAllergicReaction_Kind, '') as PersonAllergicReaction_Kind,
				convert(varchar(10), PAR.PersonAllergicReaction_setDT, 104) as PersonAllergicReaction_setDate,
				PAR.pmUser_insID
			from
				v_PersonAllergicReaction PAR with (nolock)
			where (1 = 1)
				and PAR.PersonAllergicReaction_id = :PersonAllergicReaction_id
		";
		$result = $this->db->query($query, array(
			'PersonAllergicReaction_id' => $data['PersonAllergicReaction_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function savePersonAllergicReaction($data) {
		$procedure = '';

		if ( (!isset($data['PersonAllergicReaction_id'])) || ($data['PersonAllergicReaction_id'] <= 0) ) {
			$procedure = 'p_PersonAllergicReaction_ins';
		}
		else {
			$procedure = 'p_PersonAllergicReaction_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonAllergicReaction_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonAllergicReaction_id = @Res output,
				@Person_id = :Person_id,
				@AllergicReactionLevel_id = :AllergicReactionLevel_id,
				@AllergicReactionType_id = :AllergicReactionType_id,
				@PersonAllergicReaction_setDT = :PersonAllergicReaction_setDate,
				@PersonAllergicReaction_Kind = :PersonAllergicReaction_Kind,
				@DrugMnn_id = :DrugMnn_id,
				@ACTMATTERS_ID = :RlsActmatters_id,
				@TRADENAMES_ID = :TRADENAMES_ID,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonAllergicReaction_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'PersonAllergicReaction_id' => $data['PersonAllergicReaction_id'],
			'Person_id' => $data['Person_id'],
			'AllergicReactionLevel_id' => $data['AllergicReactionLevel_id'],
			'AllergicReactionType_id' => $data['AllergicReactionType_id'],
			'PersonAllergicReaction_setDate' => $data['PersonAllergicReaction_setDate'],
			'PersonAllergicReaction_Kind' => $data['PersonAllergicReaction_Kind'],
			'DrugMnn_id' => $data['DrugMnn_id'],
			'RlsActmatters_id' => $data['RlsActmatters_id'],
			'TRADENAMES_ID' => $data['TRADENAMES_ID'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка аллергических реакций пациента для ЭМК
	 */
	function loadPersonAllergicReaction($data) {

		$filter = " PAR.Person_id = :Person_id "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " PAR.Person_id in ({$data['person_in']}) ";
			$select = " ,PAR.Person_id ";
		}

		return $this->queryResult("
			select
				PAR.PersonAllergicReaction_id,
				COALESCE(DM.DrugMnn_Name, ACT.RUSNAME, TD.NAME, PAR.PersonAllergicReaction_Kind, '') as PersonAllergicReaction_Kind,
				ART.AllergicReactionType_Name,
				ARL.AllergicReactionLevel_Name,
				convert(varchar(10), PAR.PersonAllergicReaction_setDT, 104) as PersonAllergicReaction_setDate
				{$select}
			from 
				v_PersonAllergicReaction PAR with (nolock)
				left join v_AllergicReactionType ART with (nolock) on ART.AllergicReactionType_id = PAR.AllergicReactionType_id
				left join v_AllergicReactionLevel ARL with (nolock) on ARL.AllergicReactionLevel_id = PAR.AllergicReactionLevel_id
				left join v_DrugMnn DM with (nolock) on DM.DrugMnn_id = PAR.DrugMnn_id
				left join rls.v_ACTMATTERS ACT with (nolock) on ACT.ACTMATTERS_ID = PAR.ACTMATTERS_ID
				left join rls.v_TRADENAMES TD with (nolock) on TD.TRADENAMES_ID = PAR.TRADENAMES_ID
			where {$filter}
    	", array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	 * Получение массива аллергических препаратов и компонентов пациента
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function getPersonAllergicReaction($data)
	{
		if(empty($data["Person_id"])) {
			throw new Exception("Не указан обязательный параметр Person_id");
		}
		$query = "
			select
			    ACTMATTERS_ID as \"ACTMATTERS_ID\",
			    TRADENAMES_ID as \"TRADENAMES_ID\"
			from PersonAllergicReaction with (nolock)
			where Person_id = :Person_id
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Проверка аллергических реакций на связку Person_id и DrugComplexMnn_id
	 * @param $data
	 * @return false
	 * @throws Exception
	 */
	function checkPersonAllergicReaction($data)
	{
		if (empty($data["Person_id"]) || empty($data["DrugComplexMnn_id"])) {
			throw new Exception("Не указаны обязательные параметры");
		}
		$returnResult = false;
		$getPersonAllergicReactionData = $this->getPersonAllergicReaction(["Person_id" => $data["Person_id"]]);
		$query = "
			select distinct
			    ac.ACTMATTERS_ID as \"ACTMATTERS_ID\",
			    tr.TRADENAMES_ID as \"TRADENAMES_ID\"
			from
			    rls.CLSATC atc
			    left join rls.PREP_ATC pa on atc.CLSATC_ID = pa.UNIQID
			    left join rls.PREP pr on pa.PREPID = pr.Prep_id
			    left join rls.TRADENAMES tr on pr.TRADENAMEID = tr.TRADENAMES_ID
			    left join rls.PREP_ACTMATTERS pac on pr.Prep_id = pac.PREPID
			    left join rls.ACTMATTERS ac on pac.MATTERID = ac.ACTMATTERS_ID
			    left join rls.v_DrugComplexMnnName MnnName with (nolock) on MnnName.ACTMATTERS_id = ac.ACTMATTERS_ID
			    left join dbo.v_CureStandartTreatmentDrug cstd (nolock) on cstd.ACTMATTERS_ID = MnnName.ACTMATTERS_id
			    left join dbo.v_CureStandartTreatment cst (nolock) on cst.CureStandartTreatment_id = cstd.CureStandartTreatment_id
			where MnnName.DrugComplexMnnName_id = (
			    select T2.DrugComplexMnnName_id
			    from
			        rls.DrugComplexMnn T1 with (nolock),
			        rls.DrugComplexMnnName T2 with (nolock)
			    where T1.DrugComplexMnnName_id = T2.DrugComplexMnnName_id
			      and T1.DrugComplexMnn_id = :DrugComplexMnn_id
			)
			
			UNION --yl:простой контроль по торговому наименованию 
			select
			    ACTMATTERS_ID,
			    TRADENAMES_ID
			from
			    rls.v_DrugComplexMnnName MnnName with (nolock)
			where MnnName.DrugComplexMnnName_id = (
			    select T2.DrugComplexMnnName_id
			    from
			        rls.DrugComplexMnn T1 with (nolock),
			        rls.DrugComplexMnnName T2 with (nolock)
			    where T1.DrugComplexMnnName_id = T2.DrugComplexMnnName_id
			      and T1.DrugComplexMnn_id = :DrugComplexMnn_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["DrugComplexMnn_id" => $data["DrugComplexMnn_id"]]);
		if (!is_object($result)) {
			throw new Exception("Ошибка выполнения запроса к базе данных");
		}
		$result = $result->result_array();
		$ACTMATTERS_Data = [];
		$TRADENAMES_Data = [];
		foreach ($result as $resultItem) {
			$ACTMATTERS_Data[] = $resultItem["ACTMATTERS_ID"];
			$TRADENAMES_Data[] = $resultItem["TRADENAMES_ID"];
		}
		$ACTMATTERS_Data = array_unique($ACTMATTERS_Data);
		$TRADENAMES_Data = array_unique($TRADENAMES_Data);
		foreach ($getPersonAllergicReactionData as $getPersonAllergicReactionDataItem) {
			if (!empty($getPersonAllergicReactionDataItem["ACTMATTERS_ID"])) {
				if (in_array($getPersonAllergicReactionDataItem["ACTMATTERS_ID"], $ACTMATTERS_Data)) {
					$returnResult = true;
					break;
				}
			}
			if (!empty($getPersonAllergicReactionDataItem["TRADENAMES_ID"])) {
				if (in_array($getPersonAllergicReactionDataItem["TRADENAMES_ID"], $TRADENAMES_Data)) {
					$returnResult = true;
					break;
				}
			}
		}
		return $returnResult;
	}

	/**
	 * Проверка лекарственного взаимодейтсвия переданного DrugComplexMnn_id с другими назначениями лек. средств в случае
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkPersonDrugReaction($data)
	{
		$response = ['Error_Msg' => ''];

		// надо проверить связь указанного медикамента через rls.v_LS_LINK с любым другим сохраненным в случае
		$resp = $this->queryResult("
			select top 1
				ll.LS_LINK_ID,
				le.DESCRIPTION as LS_EFFECT_NAME,
				dcm1.DrugComplexMnn_RusName,
				dcm2.DrugComplexMnn_RusName AS DrugComplexMnn_RusName2,
				CONVERT(VARCHAR(19), EPT.EvnPrescrTreat_setDT, 104) AS EvnPrescrTreat_setDT,
				EP.EvnPL_NumCard
			from
				v_EvnPrescrTreat EPT with(nolock)
				inner join v_EvnPrescrTreatDrug EPTD (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				inner join rls.v_DrugComplexMnn dcm1 (nolock) on dcm1.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				inner join rls.v_DrugComplexMnn dcm2 (nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 with (NOLOCK) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 with (NOLOCK) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				inner join rls.v_LS_LINK ll with (nolock) ON (
					(
						(
							ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID
							or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID
						) and (
							ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID
							or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID
						)
					) or (
						(
							ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID
							or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID
						) and (
							ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID
							or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID
						)
					)
				) and ll.LS_EFFECT_ID IN (3, 4)
				left join rls.v_LS_EFFECT le with (nolock) on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
				left join v_EvnPL EP with (nolock) on EP.EvnPL_id = (select top 1 Evn_rid from v_Evn with (nolock) where Evn_id = :Evn_id)
			where
				EvnPrescrTreat_pid = :Evn_id
		", [
			'Evn_id' => $data['Evn_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
		]);

		if (!empty($resp[0]['LS_LINK_ID'])) {
			$response = array_merge($response, $resp[0]);
		}

		return $response;
	}


	/**
	 * Проверка лекарственного взаимодейтсвия переданного DrugComplexMnn_id с назначениями в других случаях
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkPersonDrugReactionInEvn($data)
	{
		$response = ['Error_Msg' => ''];
		$data['DrugComplexMnn_ids'] = $data['DrugComplexMnn_ids'] != '' ? $data['DrugComplexMnn_ids'] : 0;
		$data['PacketPrescr_id'] = $data['PacketPrescr_id'] != '' ? $data['PacketPrescr_id'] : 0;
		$DrugComplexMnn_ids = explode(',',$data['DrugComplexMnn_ids']);
		$resp = $this->queryResult("
			select top 1
				dcm.DrugComplexMnn_RusName as Drug_Name,
				( 
					select 
						ad.AntagonistDrug_Names AS '*' 
					from (
						select 
						dcm1.DrugComplexMnn_RusName + 
						case 
							when E.Evn_pid = :Evn_id then ', назначенным ранее, ' 
							else
								case 
									when EA.EvnClass_SysNick = 'EvnPS' then ' (КВС №' + ES.EvnPS_NumCard + ' от ' + convert(varchar(10), EA.Evn_setDate, 104) + '), '
									when EA.EvnClass_SysNick = 'EvnPL' then ' (ТАП №' + EP.EvnPL_NumCard + ' от ' + convert(varchar(10), EA.Evn_setDate, 104) + '), '
								end
						end as AntagonistDrug_Names
						from v_Evn E with(nolock) 
							inner join v_EvnPrescrTreat EPT with(nolock) on EPT.EvnPrescrTreat_id = E.Evn_id
							inner join v_EvnPrescrTreatDrug EPTD with(nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
							inner join rls.v_DrugComplexMnn dcm1 with(nolock) on dcm1.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
							inner join rls.v_DrugComplexMnn dcm2 with(nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn1 with(nolock) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
							inner join rls.v_DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
							left join v_Evn EA with(nolock) on ea.Evn_id = E.Evn_rid
							left join v_EvnPL EP with(nolock) on EP.EvnPL_id = ea.Evn_id
							left join v_EvnPS ES with(nolock) on ES.EvnPS_id = ea.Evn_id
							inner join rls.v_LS_LINK ll with(nolock) on (
								(
									( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
								) or (
									( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
								)
							) and ll.LS_EFFECT_ID IN (3, 4)
							left join rls.v_LS_EFFECT le with(nolock) on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
						where 
							E.Person_id = :Person_id
							and E.EvnClass_SysNick = 'EvnPrescrTreat'
							and cast(E.Evn_setDate as date) = cast(:EvnCourseTreat_setDate as date)
						group by 
							EA.EvnClass_SysNick,
							E.Evn_rid,
							EA.Evn_setDate,
							dcm1.DrugComplexMnn_RusName,
							dcm2.DrugComplexMnn_RusName,
							EP.EvnPL_NumCard,
							ES.EvnPS_NumCard,
							E.Evn_pid
							
						union all
						
						select 
							dcm1.DrugComplexMnn_RusName +  ', назначенным ранее, ' as AntagonistDrug_Names
						from rls.v_DrugComplexMnn dcm1 with(nolock)
							inner join rls.v_DrugComplexMnn dcm2 with(nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn1 with(nolock) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
							inner join rls.v_DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
							inner join rls.v_LS_LINK ll with(nolock) on (
								(
									( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
								) or (
									( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
								)
							) and ll.LS_EFFECT_ID in (3, 4)
						where 
							dcm1.DrugComplexMnn_id in('" . implode('\',\'',$DrugComplexMnn_ids). "')
							
						union all
						
						select 
							dcm1.DrugComplexMnn_RusName +  ', назначенным ранее, ' as AntagonistDrug_Names
						from dbo.v_PacketPrescr pp with(nolock)
							inner join dbo.v_PacketPrescrList ppl with(nolock) on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrTreat ppt with(nolock) on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
							left join dbo.v_PacketPrescrTreatDrug pptd with(nolock) on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
							left join rls.v_DrugComplexMnnName MnnName with(nolock) on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
							left join rls.v_Drug Drug with(nolock) on Drug.Drug_id = pptd.Drug_id
							inner join rls.v_DrugComplexMnn dcm1 with(nolock) on dcm1.DrugComplexMnn_id = isnull(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
							inner join rls.v_DrugComplexMnn dcm2 with(nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn1 with(nolock) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
							inner join rls.v_DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
							inner join rls.v_LS_LINK ll with(nolock) on (
								(
									( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
								) or (
									( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
								)
							) and ll.LS_EFFECT_ID in (3, 4)
						where 
							pp.PacketPrescr_id = :PacketPrescr_id
							and ppl.PrescriptionType_id = '5'
						) as ad
					for XML path('')
				) as AntagonistDrug_Names
				from rls.v_DrugComplexMnn dcm with(nolock)
				inner join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				where 
					dcm.DrugComplexMnn_id = :DrugComplexMnn_id
		", [
			'EvnCourseTreat_setDate' => $data['EvnCourseTreat_setDate'],
			'Evn_id' => $data['Evn_id'],
			'Person_id' => $data['Person_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
			'DrugComplexMnn_ids' => $data['DrugComplexMnn_ids'],
			'PacketPrescr_id' => $data['PacketPrescr_id']
		]);

		if (!empty($resp[0]['AntagonistDrug_Names'])) {
			$response = array_merge($response, $resp[0]);
		}

		return $response;
	}
	/**
	 * Получение описания лекарственного взаимодейтсвия переданного DrugComplexMnn_id с назначениями в других случаях
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getDrugInteractionsDescription($data)
	{
		$data['DrugComplexMnn_ids'] = $data['DrugComplexMnn_ids'] != '' ? $data['DrugComplexMnn_ids'] : 0;
		$data['PacketPrescr_id'] = $data['PacketPrescr_id'] != '' ? $data['PacketPrescr_id'] : 0;
		$DrugComplexMnn_ids = explode(',',$data['DrugComplexMnn_ids']);
		$resp = $this->queryResult("
			select 
				dcm2.DrugComplexMnn_id as DrugComplexMnn_sort,
				dcm1.DrugComplexMnn_id,
				convert(varchar(10), min(E.Evn_setDate), 104) as AntagonistDrug_AppDate, 
				case 
					when EA.EvnClass_SysNick = 'EvnPS' then ' КВС №'
					when EA.EvnClass_SysNick = 'EvnPL' then ' ТАП №'
				end as Evn_Name,
				case 
					when EA.EvnClass_SysNick = 'EvnPS' then ES.EvnPS_NumCard
					when EA.EvnClass_SysNick = 'EvnPL' then EP.EvnPL_NumCard
				end as Evn_NumCard,
				datediff (d , cast(:EvnCourseTreat_setDate as date) , max(E.Evn_setDate) ) as AntagonistDrug_RemainingDay,
				dcmn1.DrugComplexMnnName_Name as AntagonistDrug_Name, 
				case when E.Evn_pid = :Evn_id then 1 else 0 end as thisEvn,
				ll.DESCRIPTION as AntagonistDrug_Description,
				ll.RECOMMENDATION as AntagonistDrug_Recommendation,
				ll.BREAKTIME as AntagonistDrug_BreakTime
			from v_Evn E with(nolock) 
				inner join v_EvnPrescrTreat EPT with(nolock) on EPT.EvnPrescrTreat_id = E.Evn_id
				inner join v_EvnPrescrTreatDrug EPTD with(nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				inner join rls.v_DrugComplexMnn dcm1 with(nolock) on dcm1.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				inner join rls.v_DrugComplexMnn dcm2 with(nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 with(nolock) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				left join v_Evn EA with(nolock) on ea.Evn_id = E.Evn_rid
				left join v_EvnPL EP with(nolock) on EP.EvnPL_id = ea.Evn_id
				left join v_EvnPS ES with(nolock) on ES.EvnPS_id = ea.Evn_id
				inner join rls.v_LS_LINK ll with(nolock) on (
					(
						( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
							and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
					) or (
						( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
							and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
					)
				) and ll.LS_EFFECT_ID in (3, 4)
				left join rls.v_LS_EFFECT le with(nolock) on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
			where 
				E.Person_id = :Person_id
				and E.EvnClass_SysNick = 'EvnPrescrTreat'
			group by 
				dcm2.DrugComplexMnn_id,
				dcm1.DrugComplexMnn_id,
				EA.EvnClass_SysNick,
				E.Evn_rid,
				EA.Evn_setDate,
				dcm1.DrugComplexMnn_RusName,
				dcm2.DrugComplexMnn_RusName,
				EP.EvnPL_NumCard,
				ES.EvnPS_NumCard,
				E.Evn_pid,
				ll.DESCRIPTION,
				ll.RECOMMENDATION,
				ll.BREAKTIME,
				dcmn1.DrugComplexMnnName_Name
				
			union all

			select 
				dcm2.DrugComplexMnn_id as DrugComplexMnn_sort,
				dcm1.DrugComplexMnn_id,
				'' as AntagonistDrug_AppDate, 
				'' as Evn_Name,
				'' as Evn_NumCard,
				1 as AntagonistDrug_RemainingDay,
				dcmn1.DrugComplexMnnName_Name as AntagonistDrug_Name, 
				3 as thisEvn,
				ll.DESCRIPTION as AntagonistDrug_Description,
				ll.RECOMMENDATION as AntagonistDrug_Recommendation,
				ll.BREAKTIME as AntagonistDrug_BreakTime
			from rls.v_DrugComplexMnn dcm1 with(nolock)
				inner join rls.v_DrugComplexMnn dcm2 with(nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 with(nolock) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				inner join rls.v_LS_LINK ll with(nolock) on (
					(
						( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
						and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
					) or (
						( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
						and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
					)
				) and ll.LS_EFFECT_ID in (3, 4)
			where 
				dcm1.DrugComplexMnn_id in('" . implode('\',\'',$DrugComplexMnn_ids). "')
			group by 
				dcm2.DrugComplexMnn_id,
				dcm1.DrugComplexMnn_id,
				dcm1.DrugComplexMnn_RusName,
				dcm2.DrugComplexMnn_RusName,
				ll.DESCRIPTION,
				ll.RECOMMENDATION,
				ll.BREAKTIME,
				dcmn1.DrugComplexMnnName_Name
				
			union all

			select 
				dcm2.DrugComplexMnn_id as DrugComplexMnn_sort,
				dcm1.DrugComplexMnn_id,
				'' as AntagonistDrug_AppDate, 
				'' as Evn_Name,
				'' as Evn_NumCard,
				1 as AntagonistDrug_RemainingDay,
				dcmn1.DrugComplexMnnName_Name as AntagonistDrug_Name, 
				3 as thisEvn,
				ll.DESCRIPTION as AntagonistDrug_Description,
				ll.RECOMMENDATION as AntagonistDrug_Recommendation,
				ll.BREAKTIME as AntagonistDrug_BreakTime
			from dbo.v_PacketPrescr pp with(nolock)
				inner join dbo.v_PacketPrescrList ppl with(nolock) on ppl.PacketPrescr_id = pp.PacketPrescr_id
				inner join dbo.v_PacketPrescrTreat ppt with(nolock) on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
				left join dbo.v_PacketPrescrTreatDrug pptd with(nolock) on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
				left join rls.v_DrugComplexMnnName MnnName with(nolock) on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
				left join rls.v_Drug Drug with(nolock) on Drug.Drug_id = pptd.Drug_id
				inner join rls.v_DrugComplexMnn dcm1 with(nolock) on dcm1.DrugComplexMnn_id = isnull(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				inner join rls.v_DrugComplexMnn dcm2 with(nolock) on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 with(nolock) on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				inner join rls.v_LS_LINK ll with(nolock) on (
					(
						( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
							and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
					) or (
						( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
							and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
					)
				) and ll.LS_EFFECT_ID in (3, 4)
			where 
				pp.PacketPrescr_id = :PacketPrescr_id
				and ppl.PrescriptionType_id = '5'
			group by 
				dcm2.DrugComplexMnn_id,
				dcm1.DrugComplexMnn_id,
				dcm1.DrugComplexMnn_RusName,
				dcm2.DrugComplexMnn_RusName,
				ll.DESCRIPTION,
				ll.RECOMMENDATION,
				ll.BREAKTIME,
				dcmn1.DrugComplexMnnName_Name
		", [
			'EvnCourseTreat_setDate' => $data['EvnCourseTreat_setDate'],
			'Evn_id' => $data['Evn_id'],
			'Person_id' => $data['Person_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
			'DrugComplexMnn_ids' => $data['DrugComplexMnn_ids'],
			'PacketPrescr_id' => $data['PacketPrescr_id']
		]);

		if (!empty($resp[0]['AntagonistDrug_Name'])) {
			$tpl = '';
			foreach($resp as $value){
				if($value['AntagonistDrug_RemainingDay'] > 0){
					$tpl .= '
						<table style="padding-top:7px;table-layout: fixed; width:100%" width="100%" cellspacing="0" cellpadding="0">
							<tr> 
   								<td style="padding-right:7px; width:20px;vertical-align: top;" class="leftcol">
   									<img src="../img/icons/warn_red.png" width="15" class="" alt="">
   								</td>
   								<td style="word-wrap:break-word;width:100%;" valign="top">';
					if($value['thisEvn'] != 3){
					$tpl .= '
									<div style="padding-bottom:7px;" class="AntagonistDrugHeader"><b>Назначен с:</b> ' .  $value['AntagonistDrug_AppDate'] . ' <b>в ' . $value['Evn_Name'] . 
						'</b>' . $value['Evn_NumCard'] . '; <b>Оставшееся время приема: </b>' .$value['AntagonistDrug_RemainingDay']. ' дней</div>';
					}
					$tpl .= '<div style="max-width:720px;padding-bottom:7px;white-space:break-spaces;" class="AntagonistDrugHeader">Влияет на <b>' . $value['AntagonistDrug_Name'] . ':</b> ' . $value['AntagonistDrug_Description'] . '<br>' .
						 '<b>Рекомендации:</b> ' .  $value['AntagonistDrug_Recommendation'] . '<br>' .
						 '<b>Временной перерыв между приемами:</b> ' .  $value['AntagonistDrug_BreakTime'] . '</div>
								</td>
   							</tr>
  						</table>';
				}
			}
			$response = $tpl;
		}
		return isset($response) ? $response : '';
	}

	/**
	 * Получение описания лекарственного взаимодейтсвия переданного DrugComplexMnn_id с назначениями в других случаях
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function loadDrugInteractionsDescription($data)
	{
		$response = ['Error_Msg' => ''];

		$DrugComplexMnn_ids = explode(',',$data['DrugComplexMnn_ids']);
		foreach($DrugComplexMnn_ids as $DrugComplexMnn_id){
			$notification = $this->getDrugInteractionsDescription([
				'EvnCourseTreat_setDate' => $data['EvnCourseTreat_setDate'],
				'Evn_id' => $data['Evn_id'],
				'Person_id' => $data['Person_id'],
				'DrugComplexMnn_id' => $DrugComplexMnn_id,
				'DrugComplexMnn_ids' => $data['DrugComplexMnn_ids'],
				'PacketPrescr_id' => $data['PacketPrescr_id']
			]);
			$response['tpl'][$DrugComplexMnn_id . '_description'] = $notification;
		}
		return $response;
	}
}