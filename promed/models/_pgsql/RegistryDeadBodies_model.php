<?php defined('BASEPATH') or die ('No direct script access allowed');

class RegistryDeadBodies_model extends swModel {

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *  Получение списка записей поступления и выдачи тел умерших за определенный период
	 * @param $data
	 * @return array
	 *  Используется: журнал регистрации поступления и выдачи тел умерших
	 */
	function loadRegistryDeadBodiesListGrid($data)
	{
		$queryParams = array();
		$filter = "";

		if (empty($data['ReportPeriod'])) {
			return $this->createError('','Поле ввода периода дат обязательно для заполнения.');
		}

		$filter .= " AND (MHCR.MorfoHistologicCorpseReciept_setDT >= :begDate";
		$filter .= " OR MHP.EvnMorfoHistologicProto_autopsyDT >= :begDate";
		$filter .= " OR MHCG.MorfoHistologicCorpseGiveaway_setDT >= :begDate)";
		$queryParams['begDate'] = $data['ReportPeriod'][0];

		$filter .= " AND (MHCR.MorfoHistologicCorpseReciept_setDT <= :endDate";
		$filter .= " OR MHP.EvnMorfoHistologicProto_autopsyDT <= :endDate";
		$filter .= " OR MHCG.MorfoHistologicCorpseGiveaway_setDT <= :endDate)";
		$queryParams['endDate'] = $data['ReportPeriod'][1];

		if (!empty($data['Lpu_sid'])) {
			$filter .= " and EDMH.Lpu_sid = :Lpu_sid";
			$queryParams['Lpu_sid'] = $data['Lpu_sid'];
		}

		if (!empty($data['Refuse_Exists'])) {
			$filter .= " and MHR.MorfoHistologicRefuse_id IS NOT NULL";
		}

		if (!empty($data['MorfoHistologicCorpse_recieptDate'])) {
			$filter .= " and MHCR.MorfoHistologicCorpseReciept_setDT >= :CorpseReciept_begDate and MHCR.MorfoHistologicCorpseReciept_setDT <= :CorpseReciept_endDate";
			$queryParams['CorpseReciept_begDate'] = $data['MorfoHistologicCorpse_recieptDate'][0];
			$queryParams['CorpseReciept_endDate'] = $data['MorfoHistologicCorpse_recieptDate'][1];
		}

		if (!empty($data['PersonDead_FIO'])) {
			$filter .= " and PersonDead.FIO ilike '%'||:PersonDead_FIO||'%'";
			$queryParams['PersonDead_FIO'] = $data['PersonDead_FIO'];
		}

		if (!empty($data['EvnPS_NumCard'])) {
			$filter .= " and EPS.EvnPS_NumCard like :EvnPS_NumCard";
			$queryParams['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
		}

		if (!empty($data['EvnMorfoHistologicProto_autopsyDate'])) {
			$filter .= " and MHP.EvnMorfoHistologicProto_autopsyDT >= :Autopsy_begDate and MHP.EvnMorfoHistologicProto_autopsyDT <= :Autopsy_endDate";
			$queryParams['Autopsy_begDate'] = $data['EvnMorfoHistologicProto_autopsyDate'][0];
			$queryParams['Autopsy_endDate'] = $data['EvnMorfoHistologicProto_autopsyDate'][1];
		}

		if (!empty($data['MorfoHistologicCorpse_giveawayDate'])) {
			$filter .= " and MHCG.MorfoHistologicCorpseGiveaway_setDT >= :CorpseGiveaway_begDate and MHCG.MorfoHistologicCorpseGiveaway_setDT <= :CorpseGiveaway_endDate";
			$queryParams['CorpseGiveaway_begDate'] = $data['MorfoHistologicCorpse_giveawayDate'][0];
			$queryParams['CorpseGiveaway_endDate'] = $data['MorfoHistologicCorpse_giveawayDate'][1];
		}

		$query = "
				SELECT
    				EDMH.EvnDirectionMorfoHistologic_id as \"EvnDirectionMorfoHistologic_id\",
    				MHCR.MorfoHistologicCorpseReciept_id as \"MorfoHistologicCorpseReciept_id\",
    				to_char(MHCR.MorfoHistologicCorpseReciept_setDT, 'DD.MM.YYYY') as \"MorfoHistologicCorpse_recieptDate\",
    				MHCG.MorfoHistologicCorpseGiveaway_id as \"MorfoHistologicCorpseGiveaway_id\",
    				to_char(MHCG.MorfoHistologicCorpseGiveaway_setDT, 'DD.MM.YYYY') as \"MorfoHistologicCorpse_giveawayDate\",
    				MHCG.Person_id as \"CorpseRecipient_id\",
    				COALESCE(PR.Person_SurName, '') || ' ' || COALESCE(PR.Person_FirName, '') || ' ' || COALESCE(PR.Person_SecName, '') as \"PersonRecipient_FIO\",
    				MHP.EvnMorfoHistologicProto_id as \"EvnMorfoHistologicProto_id\",
    				to_char(MHP.EvnMorfoHistologicProto_autopsyDT, 'DD.MM.YYYY') as \"EvnMorfoHistologicProto_autopsyDate\",
    				MHR.MorfoHistologicRefuse_id as \"MorfoHistologicRefuse_id\",
    				case when MHR.MorfoHistologicRefuse_id is not null then 'true' else 'false' end as \"Refuse_Exists\",
    				EDMH.Person_id as \"DeadPerson_id\",
				    PersonDead.FIO as \"PersonDead_FIO\",
    				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
    				MHRT.MorfoHistologicRefuseType_name as \"MorfoHistologicRefuseType_name\",
    				COALESCE(D.Document_Ser, '') || ' ' || COALESCE(D.Document_Num, '') || ' ' || COALESCE(DT.DocumentType_Name, '') || ' ' || COALESCE(to_char(D.Document_begDate, 'DD.MM.YYYY'), '') as \"Document\",
    				EDMH.Lpu_did as \"Lpu_did\",
    				L.Lpu_Name as \"Lpu_Name\"
				       
    			FROM v_EvnDirectionMorfoHistologic EDMH
				
    				LEFT JOIN v_MorfoHistologicCorpseReciept MHCR ON EDMH.EvnDirectionMorfoHistologic_id = MHCR.EvnDirectionMorfoHistologic_id
    				LEFT JOIN v_MorfoHistologicCorpseGiveaway MHCG ON EDMH.EvnDirectionMorfoHistologic_id = MHCG.EvnDirectionMorfoHistologic_id
    				LEFT JOIN v_EvnMorfoHistologicProto MHP ON EDMH.EvnDirectionMorfoHistologic_id = MHP.EvnDirectionMorfoHistologic_id
    				LEFT JOIN v_MorfoHistologicRefuse MHR ON EDMH.EvnDirectionMorfoHistologic_id = MHR.EvnDirectionMorfoHistologic_id
    				LEFT JOIN v_EvnPS EPS ON EPS.EvnPS_id = EDMH.EvnPS_id
    				INNER JOIN v_PersonState PR ON PR.Person_id = MHCG.Person_id
    				INNER JOIN v_PersonState PD ON PD.Person_id = EDMH.Person_id
    				LEFT JOIN v_MorfoHistologicRefuseType MHRT ON MHR.MorfoHistologicRefuseType_id = MHRT.MorfoHistologicRefuseType_id
    				LEFT JOIN v_Document D ON PR.Document_id = D.Document_id
    				LEFT JOIN v_DocumentType DT ON D.DocumentType_id = DT.DocumentType_id
   					LEFT JOIN v_Lpu L ON L.Lpu_id = EDMH.Lpu_did
					LEFT JOIN lateral(
						SELECT
							COALESCE(rtrim(pd.Person_SurName),'') ||
							COALESCE(' ' || rtrim(pd.Person_FirName),'') ||
							COALESCE(' ' || rtrim(pd.Person_SecName),'') as FIO
						FROM v_PersonState PD
						WHERE
							pd.Person_id = EDMH.Person_id
						LIMIT 1
					) PersonDead on true   	
									
				WHERE 
					(1 = 1) 
					{$filter}
				ORDER BY
					MHCR.MorfoHistologicCorpseReciept_id ASC
			";

//		$result = $this->db->query($query, $queryParams);

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], false);

	}
}
