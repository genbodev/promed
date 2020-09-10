<?php

/**
 * Class PersonAllergicReaction_model
 *
 * @property CI_DB_driver $db
 */
class PersonAllergicReaction_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function deletePersonAllergicReaction($data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonAllergicReaction_del(
				PersonAllergicReaction_id := :PersonAllergicReaction_id
			)
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["PersonAllergicReaction_id" => $data["PersonAllergicReaction_id"]]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение данных о виде аллергической реакции человека
	 * @param $data
	 * @return array|bool
	 */
	function getPersonAllergicReactionViewData($data)
	{
		$query = "
			select
				PAR.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PAR.PersonAllergicReaction_id as \"PersonAllergicReaction_id\",
				PAR.PersonAllergicReaction_id as \"AllergHistory_id\",
				to_char(PAR.PersonAllergicReaction_setDT, 'dd.mm.yyyy') as \"PersonAllergicReaction_setDate\",
				coalesce(ART.AllergicReactionType_Name, '') as \"AllergicReactionType_Name\",
				coalesce(ARL.AllergicReactionLevel_Name, '') as \"AllergicReactionLevel_Name\",
				COALESCE(DM.DrugMnn_Name, PAR.PersonAllergicReaction_Kind, a.RUSNAME, t.NAME, '') as \"PersonAllergicReaction_Kind\",
				PAR.pmUser_insID as \"pmUser_insID\"
			from
				v_PersonAllergicReaction PAR
				inner join v_AllergicReactionType ART on ART.AllergicReactionType_id = PAR.AllergicReactionType_id
				inner join v_AllergicReactionLevel ARL on ARL.AllergicReactionLevel_id = PAR.AllergicReactionLevel_id
				left join v_DrugMnn DM on DM.DrugMnn_id = PAR.DrugMnn_id
				left join rls.actmatters a on a.actmatters_id = PAR.ACTMATTERS_ID
				left join rls.tradenames t on t.TRADENAMES_ID = PAR.TRADENAMES_ID				
				
			where PAR.Person_id = :Person_id
			order by PAR.PersonAllergicReaction_setDT
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["Person_id" => $data["Person_id"]]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonAllergicReactionEditForm($data)
	{
		$query = "
			select
				PAR.PersonAllergicReaction_id as \"PersonAllergicReaction_id\",
				PAR.Person_id as \"Person_id\",
				PAR.Server_id as \"Server_id\",
				PAR.AllergicReactionType_id as \"AllergicReactionType_id\",
				PAR.AllergicReactionLevel_id as \"AllergicReactionLevel_id\",
				PAR.DrugMnn_id as \"DrugMnn_id\",
				PAR.ACTMATTERS_ID as \"RlsActmatters_id\",
				PAR.TRADENAMES_ID as \"TRADENAMES_ID\",
				CASE
				    WHEN TRADENAMES_ID IS NOT NULL THEN 2
					WHEN ACTMATTERS_ID IS NOT NULL THEN 1
					ELSE 1
				END AS \"PersonAllergicReactionType_value\",
				coalesce(PAR.PersonAllergicReaction_Kind, '') as \"PersonAllergicReaction_Kind\",
				to_char(PAR.PersonAllergicReaction_setDT, 'dd.mm.yyyy') as \"PersonAllergicReaction_setDate\",
				PAR.pmUser_insID as \"pmUser_insID\"
			from v_PersonAllergicReaction PAR
			where PAR.PersonAllergicReaction_id = :PersonAllergicReaction_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["PersonAllergicReaction_id" => $data["PersonAllergicReaction_id"]]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function savePersonAllergicReaction($data)
	{
		$procedure = ((!isset($data["PersonAllergicReaction_id"])) || ($data["PersonAllergicReaction_id"] <= 0)) ? "p_PersonAllergicReaction_ins" : "p_PersonAllergicReaction_upd";
		$query = "
			select
				PersonAllergicReaction_id as \"PersonAllergicReaction_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				Server_id := :Server_id,
				PersonAllergicReaction_id := :PersonAllergicReaction_id,
				Person_id := :Person_id,
				AllergicReactionLevel_id := :AllergicReactionLevel_id,
				AllergicReactionType_id := :AllergicReactionType_id,
				PersonAllergicReaction_setDT := :PersonAllergicReaction_setDate,
				PersonAllergicReaction_Kind := :PersonAllergicReaction_Kind,
				DrugMnn_id := :DrugMnn_id,
				ACTMATTERS_ID := :RlsActmatters_id,
				TRADENAMES_ID := :TRADENAMES_ID,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"Server_id" => $data["Server_id"],
			"PersonAllergicReaction_id" => $data["PersonAllergicReaction_id"],
			"Person_id" => $data["Person_id"],
			"AllergicReactionLevel_id" => $data["AllergicReactionLevel_id"],
			"AllergicReactionType_id" => $data["AllergicReactionType_id"],
			"PersonAllergicReaction_setDate" => $data["PersonAllergicReaction_setDate"],
			"PersonAllergicReaction_Kind" => $data["PersonAllergicReaction_Kind"],
			"DrugMnn_id" => $data["DrugMnn_id"],
			"RlsActmatters_id" => $data["RlsActmatters_id"],
			"TRADENAMES_ID" => $data["TRADENAMES_ID"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка аллергических реакций пациента для ЭМК
	 * @param $data
	 * @return array|false
	 */
	function loadPersonAllergicReaction($data)
	{
		// для оффлайн режима
		$filter = (!empty($data["person_in"])) ? " PAR.Person_id in ({$data["person_in"]}) " : " PAR.Person_id = :Person_id ";
		$select = (!empty($data["person_in"])) ? " ,PAR.Person_id as \"Person_id\"" : "";
		return $this->queryResult("
			select
				PAR.PersonAllergicReaction_id as \"PersonAllergicReaction_id\",
				COALESCE(DM.DrugMnn_Name, ACT.RUSNAME, TD.NAME, PAR.PersonAllergicReaction_Kind, '') as \"PersonAllergicReaction_Kind\",
				ART.AllergicReactionType_Name as \"AllergicReactionType_Name\",
				ARL.AllergicReactionLevel_Name as \"AllergicReactionLevel_Name\",
				to_char(PAR.PersonAllergicReaction_setDT, 'dd.mm.yyyy') as \"PersonAllergicReaction_setDate\"
				{$select}
			from 
				v_PersonAllergicReaction PAR
				left join v_AllergicReactionType ART on ART.AllergicReactionType_id = PAR.AllergicReactionType_id
				left join v_AllergicReactionLevel ARL on ARL.AllergicReactionLevel_id = PAR.AllergicReactionLevel_id
				left join v_DrugMnn DM on DM.DrugMnn_id = PAR.DrugMnn_id
				left join rls.v_ACTMATTERS ACT on ACT.ACTMATTERS_ID = PAR.ACTMATTERS_ID
				left join rls.v_TRADENAMES TD on TD.TRADENAMES_ID = PAR.TRADENAMES_ID
			where {$filter}
    	", ["Person_id" => $data["Person_id"]]);
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
			from PersonAllergicReaction
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
			    left join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = ac.ACTMATTERS_ID
			    left join dbo.v_CureStandartTreatmentDrug cstd on cstd.ACTMATTERS_ID = MnnName.ACTMATTERS_id
			    left join dbo.v_CureStandartTreatment cst on cst.CureStandartTreatment_id = cstd.CureStandartTreatment_id
			where MnnName.DrugComplexMnnName_id = (
			    select T2.DrugComplexMnnName_id
			    from
			        rls.DrugComplexMnn T1,
			        rls.DrugComplexMnnName T2
			    where T1.DrugComplexMnnName_id = T2.DrugComplexMnnName_id
			      and T1.DrugComplexMnn_id = :DrugComplexMnn_id
			)
			
			UNION 
			select
			    ACTMATTERS_ID as \"ACTMATTERS_ID\",
			    TRADENAMES_ID as \"TRADENAMES_ID\"
			from
			    rls.v_DrugComplexMnnName MnnName
			where MnnName.DrugComplexMnnName_id = (
			    select T2.DrugComplexMnnName_id
			    from
			        rls.DrugComplexMnn T1,
			        rls.DrugComplexMnnName T2
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
			select
				ll.LS_LINK_ID as \"LS_LINK_ID\",
				le.DESCRIPTION as \"LS_EFFECT_NAME\",
				dcm1.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				dcm2.DrugComplexMnn_RusName AS \"DrugComplexMnn_RusName2\",
				to_char(EPT.EvnPrescrTreat_setDT::date, 'DD.MM.YYYY') AS \"EvnPrescrTreat_setDT\",
				EP.EvnPL_NumCard AS \"EvnPL_NumCard\"
			from
				v_EvnPrescrTreat EPT
				inner join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				inner join rls.v_DrugComplexMnn dcm1 on dcm1.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				inner join rls.v_LS_LINK ll ON (
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
				left join rls.v_LS_EFFECT le on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
				left join v_EvnPL EP on EP.EvnPL_id = (select Evn_rid from v_Evn where Evn_id = :Evn_id limit 1)
			where
				EvnPrescrTreat_pid = :Evn_id
            limit 1
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
			select
				dcm.DrugComplexMnn_RusName as \"Drug_Name\",
				( 
					select 
						string_agg(ad.AntagonistDrug_Names,', ') 
					from (
						select 
						dcm1.DrugComplexMnn_RusName || 
						case 
							when E.Evn_pid = :Evn_id then ', назначенным ранее, ' 
							else
								case 
									when EA.EvnClass_SysNick = 'EvnPS' then ' (КВС №' || ES.EvnPS_NumCard || ' от ' || to_char(EA.Evn_setDate::date, 'DD.MM.YYYY') || '), '
									when EA.EvnClass_SysNick = 'EvnPL' then ' (ТАП №' || EP.EvnPL_NumCard || ' от ' || to_char(EA.Evn_setDate::date, 'DD.MM.YYYY') || '), '
								end
						end as AntagonistDrug_Names
						from v_Evn E 
							inner join v_EvnPrescrTreat EPT on EPT.EvnPrescrTreat_id = E.Evn_id
							inner join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
							inner join rls.v_DrugComplexMnn dcm1 on dcm1.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
							inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
							inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
							left join v_Evn EA on ea.Evn_id = E.Evn_rid
							left join v_EvnPL EP on EP.EvnPL_id = ea.Evn_id
							left join v_EvnPS ES on ES.EvnPS_id = ea.Evn_id
							inner join rls.v_LS_LINK ll on (
								(
									( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
								) or (
									( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
										and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
								)
							) and ll.LS_EFFECT_ID in (3, 4)
							left join rls.v_LS_EFFECT le on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
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
							dcm1.DrugComplexMnn_RusName || ', назначенным ранее, ' as AntagonistDrug_Names
						from rls.v_DrugComplexMnn dcm1
							inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
							inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
							inner join rls.v_LS_LINK ll on (
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
							dcm1.DrugComplexMnn_RusName ||  ', назначенным ранее, ' as AntagonistDrug_Names
						from dbo.v_PacketPrescr pp
							inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
							inner join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
							left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
							left join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
							left join rls.v_Drug Drug on Drug.Drug_id = pptd.Drug_id
							inner join rls.v_DrugComplexMnn dcm1 on dcm1.DrugComplexMnn_id = coalesce(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
							inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
							inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
							inner join rls.v_LS_LINK ll on (
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
							and ppl.PrescriptionType_id = 5
						) as ad
				) as \"AntagonistDrug_Names\"
				from rls.v_DrugComplexMnn dcm
				inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				where 
					dcm.DrugComplexMnn_id = :DrugComplexMnn_id
				limit 1
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
				dcm2.DrugComplexMnn_id as \"DrugComplexMnn_sort\",
				dcm1.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				to_char(min(E.Evn_setDate)::date, 'DD.MM.YYYY') as \"AntagonistDrug_AppDate\", 
				case 
					when EA.EvnClass_SysNick = 'EvnPS' then ' КВС №'
					when EA.EvnClass_SysNick = 'EvnPL' then ' ТАП №'
				end as \"Evn_Name\",
				case 
					when EA.EvnClass_SysNick = 'EvnPS' then ES.EvnPS_NumCard
					when EA.EvnClass_SysNick = 'EvnPL' then EP.EvnPL_NumCard
				end as \"Evn_NumCard\",
				date_part ('day' , max(E.Evn_setDate) - cast(:EvnCourseTreat_setDate as date) ) as \"AntagonistDrug_RemainingDay\",
				dcmn1.DrugComplexMnnName_Name as \"AntagonistDrug_Name\", 
				case when E.Evn_pid = :Evn_id then 1 else 0 end as \"thisEvn\",
				ll.DESCRIPTION as \"AntagonistDrug_Description\",
				ll.RECOMMENDATION as \"AntagonistDrug_Recommendation\",
				ll.BREAKTIME as \"AntagonistDrug_BreakTime\"
			from v_Evn E
				inner join v_EvnPrescrTreat EPT on EPT.EvnPrescrTreat_id = E.Evn_id
				inner join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				inner join rls.v_DrugComplexMnn dcm1 on dcm1.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				left join v_Evn EA on ea.Evn_id = E.Evn_rid
				left join v_EvnPL EP on EP.EvnPL_id = ea.Evn_id
				left join v_EvnPS ES on ES.EvnPS_id = ea.Evn_id
				inner join rls.v_LS_LINK ll on (
					(
						( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
							and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
					) or (
						( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
							and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
					)
				) and ll.LS_EFFECT_ID in (3, 4)
				left join rls.v_LS_EFFECT le on le.LS_EFFECT_ID = ll.LS_EFFECT_ID
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
				dcm2.DrugComplexMnn_id as \"DrugComplexMnn_sort\",
				dcm1.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				'' as \"AntagonistDrug_AppDate\", 
				'' as \"Evn_Name\",
				'' as \"Evn_NumCard\",
				1 as \"AntagonistDrug_RemainingDay\",
				dcmn1.DrugComplexMnnName_Name as \"AntagonistDrug_Name\", 
				3 as \"thisEvn\",
				ll.DESCRIPTION as \"AntagonistDrug_Description\",
				ll.RECOMMENDATION as \"AntagonistDrug_Recommendation\",
				ll.BREAKTIME as \"AntagonistDrug_BreakTime\"
			from rls.v_DrugComplexMnn dcm1
				inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				inner join rls.v_LS_LINK ll on (
					(
						( ll.ACTMATTERS_G1ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn1.TRADENAMES_ID ) 
						and ( ll.ACTMATTERS_G2ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn2.TRADENAMES_ID )
					) or (
						( ll.ACTMATTERS_G1ID = dcmn2.ACTMATTERS_ID or ll.TRADENAMES_G1ID = dcmn2.TRADENAMES_ID ) 
						and ( ll.ACTMATTERS_G2ID = dcmn1.ACTMATTERS_ID or ll.TRADENAMES_G2ID = dcmn1.TRADENAMES_ID )
					)
				) and ll.LS_EFFECT_ID in (3, 4)
			where dcm1.DrugComplexMnn_id in('" . implode('\',\'',$DrugComplexMnn_ids). "')
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
			from dbo.v_PacketPrescr pp
				inner join dbo.v_PacketPrescrList ppl on ppl.PacketPrescr_id = pp.PacketPrescr_id
				inner join dbo.v_PacketPrescrTreat ppt on ppt.PacketPrescrList_id = ppl.PacketPrescrList_id
				left join dbo.v_PacketPrescrTreatDrug pptd on pptd.PacketPrescrTreat_id = ppt.PacketPrescrTreat_id
				left join rls.v_DrugComplexMnnName MnnName on MnnName.ACTMATTERS_id = pptd.ACTMATTERS_ID
				left join rls.v_Drug Drug on Drug.Drug_id = pptd.Drug_id
				inner join rls.v_DrugComplexMnn dcm1 on dcm1.DrugComplexMnn_id = coalesce(pptd.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				inner join rls.v_DrugComplexMnn dcm2 on dcm2.DrugComplexMnn_id = :DrugComplexMnn_id
				inner join rls.v_DrugComplexMnnName dcmn1 on dcmn1.DrugComplexMnnName_id = dcm1.DrugComplexMnnName_id
				inner join rls.v_DrugComplexMnnName dcmn2 on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
				inner join rls.v_LS_LINK ll on (
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
				and ppl.PrescriptionType_id = 5
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