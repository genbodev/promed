<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
*               Bykov Stas aka Savage (savage1981@gmail.com)
* @version      14.05.2009
*/

class Dlo_EvnRecept_model extends swPgModel {

	public $log_file = 'receptsearch.log';
	public $log_file_access_type = 'a';

    //swPgModel устанавливает schema
	//private $schema = "dbo";

    /**
     * Конструктор
     */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		//$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 * Получение условия по просроченным рецептам для запроса
	 */
function getReceptValidCondition() {
		return "
			dbo.tzGetDate() >= case
				when RV.ReceptValid_Code = 1 then dateadd('month', 1, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 2 then dateadd('month', 3, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 3 then dateadd('day', 14, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 4 then dateadd('day', 5, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 5 then dateadd('month', 2, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 7 then dateadd('day', 10, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 8 then dateadd('day', 60, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 9 then dateadd('day', 30, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 10 then dateadd('day', 90, evnRecept_all.EvnRecept_setDate)
				when RV.ReceptValid_Code = 11 then dateadd('day', 15, evnRecept_all.EvnRecept_setDate)
			end
			and EvnRecept.EvnRecept_otpDT is null
		";
	}

	/**
	 *  Сохранение признака печати рецепта
	 */
	function saveEvnReceptIsPrinted($data) {
		$result = array(
			'Error_Msg' => '',
			'reprinting' => false //признак того, что рецепт уже был напечатан ранее
		);

		//проверяем текущее значение поля EvnRecept_IsPrinted
		$query = "
			select
				coalesce(er.EvnRecept_IsPrinted, 1) as \"EvnRecept_IsPrinted\"
			from
				EvnRecept er
			where
				er.Evn_id = :EvnRecept_id;
		";
		$rec_data = $this->getFirstRowFromQuery($query, array(
			'EvnRecept_id' => $data['EvnRecept_id']
		));

		if (!empty($rec_data['EvnRecept_IsPrinted']) && $rec_data['EvnRecept_IsPrinted'] == 2) { //если рецепт уже отмечен ка напечатаный, ставим отметку в соответствующемй переменной
			$result['reprinting'] = true;
		} else { //иначе фиксимруем фвкт печати в БД
			$query = "
				update EvnRecept set EvnRecept_IsPrinted = 2 where Evn_id = :EvnRecept_id
			";
			$this->db->query($query, array(
				'EvnRecept_id' => $data['EvnRecept_id']
			));
		}

		return $result;
	}

	/**
	 *  Сохранение признака печати общего рецепта
	 */
	function saveEvnReceptGeneralIsPrinted($data) {
		$query = "
			update EvnReceptGeneral set EvnReceptGeneral_IsPrinted = 2 where evn_id = :EvnReceptGeneral_id
		";

		$this->db->query($query, array(
			'EvnReceptGeneral_id' => $data['EvnReceptGeneral_id']
		));

		return array('Error_Msg' => '');
	}

    /**
     * Возвращает рецепт
     */
	function getEvnReceptView($data) {
		/*,'Lpu_id'=>$data['Lpu_id']
		$accessType = 'R.Lpu_id = :Lpu_id';
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= ' and R.MedPersonal_id = MSF.MedPersonal_id and R.LpuSection_id = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = :user_MedStaffFact_id';

			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}
				case when {$accessType} then 'edit' else 'view' end as accessType,
		*/

		$fields = "";
		$join = "";

		if (getRegionNick() == 'kz') {
			$fields .= ",SCPT.SubCategoryPrivType_Code as \"SubCategoryPrivType_Code\"\n";
			$fields .= ",SCPT.SubCategoryPrivType_Name as \"SubCategoryPrivType_Name\"";
			$join .= " left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT  on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id";

			$join .= " left join r101.v_SubCategoryPrivType SCPT  on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id";

		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("R.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$privilegeFilter = " and $privilegeFilter";
		}

		if (isset($data['EvnRecept_id'])) {
			$params = array('EvnRecept_id'=>$data['EvnRecept_id'], 'Lpu_id'=>$data['Lpu_id']);
			$join_msf = '';
			$query = "
				SELECT
					R.EvnRecept_id as \"EvnRecept_id\",
					to_char(R.EvnRecept_setDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\",
					R.EvnRecept_Ser as \"EvnRecept_Ser\",
					R.EvnRecept_Num as \"EvnRecept_Num\",
					cast(R.EvnRecept_Kolvo as double precision) as \"EvnRecept_Kolvo\",
					COALESCE(R.EvnRecept_Signa,'') as \"EvnRecept_Signa\",		
					case when (R.EvnRecept_Is7Noz is not null AND R.EvnRecept_Is7Noz = 2) then 'да' else 'нет' end as \"EvnRecept_Is7Noz\",
					COALESCE(PT.PrivilegeType_VCode, cast(PT.PrivilegeType_Code as varchar)) as \"PrivilegeType_VCode\",
					COALESCE(PT.PrivilegeType_Name,'') as \"PrivilegeType_Name\",
					CASE
						when R.Drug_rlsid is not null
						then COALESCE(DF.DrugFinance_Name,'')
						else COALESCE(RF.ReceptFinance_Name,'')
					END as \"ReceptFinance_Name\",
					COALESCE(RD.ReceptDiscount_Name,'') as \"ReceptDiscount_Name\",		
					COALESCE(RV.ReceptValid_Name,'') as \"ReceptValid_Name\",		
					COALESCE(LS.LpuSection_Name,'') as \"LpuSection_Name\",
					COALESCE(M.Person_Fin,'') as \"MedPersonal_Fin\",
					R.Diag_id as \"Diag_id\",
					CASE
						WHEN R.EvnRecept_deleted = 2 THEN 'Удалённый МО'
						WHEN dbo.tzGetDate() >= case
							when RV.ReceptValid_Code = 1 then interval '1 month' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 2 then interval '3 month' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 3 then interval '14 day' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 4 then interval '5 day' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 5 then interval '2 month' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 7 then interval '10 day' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 8 then interval '60 day' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 9 then interval '30 day' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 10 then interval '90 day' + R.EvnRecept_setDate
							when RV.ReceptValid_Code = 11 then interval '15 day' + R.EvnRecept_setDate
						end
						and R.EvnRecept_otpDT is null
							THEN 'Просрочен'
						WHEN R.EvnRecept_otpDT is not null THEN 'Отоварен'
						WHEN R.EvnRecept_otpDT is null and R.EvnRecept_obrDT is not null THEN 'Отсрочен'
						WHEN R.ReceptDelayType_id = 3 THEN 'Отказ'
						WHEN R.ReceptDelayType_id is not  null THEN 'Выписан'
					END as \"ReceptDelayType_Name\",
					COALESCE(D.Diag_Code,'') as \"Diag_Code\",
					COALESCE(D.Diag_Name,'') as \"Diag_Name\",
					COALESCE(P.Person_SurName,'') as \"Person_Surname\",
					COALESCE(P.Person_FirName,'') as \"Person_Firname\",
					COALESCE(P.Person_SecName,'') as \"Person_Secname\",
					to_char(P.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",
					CASE
						when R.Drug_rlsid IS Not null
						then COALESCE(rlsDrug.Drug_Name,'')
						else COALESCE(Drug.Drug_Name,'')
					END as \"Drug_Name\",
					CASE
						when COALESCE(R.Drug_rlsid, R.DrugComplexMnn_id) is not null
						then coalesce(rlsDrugComplexMnn.DrugComplexMnn_RusName,rlsActmatters.RUSNAME,'')
						else DrugMnn.DrugMnn_Name
					END as \"DrugMnn_Name\",
					case when (R.EvnRecept_IsKEK is not null AND R.EvnRecept_IsKEK = 2) then '<span id=\"EvnReceptView_'|| cast(R.EvnRecept_id as varchar(1)) ||'_showProtocolVK\" class=\"link\" title=\"Показать экспертный анамнез и льготы пациента\">да</span>' else 'нет' end as \"ProtocolVK\",
					cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
					A.OrgFarmacy_Nick as \"OrgFarmacy_Name\",
					COALESCE(OrgFarmacyOtp.OrgFarmacy_Name,'') as \"OrgFarmacyOtp_Name\",
					to_char(R.EvnRecept_otpDT, 'DD.MM.YYYY') as \"EvnRecept_otpDate\",
					DrugOtp.Drug_Name as \"DrugOtp_Name\",
					null as \"DrugOtp_List\",
                    WhsDocumentCostItemType.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
                    R.Drug_rlsid as \"Drug_rlsid\",
					COALESCE(Drug.RLS_id,rlsDrug.Drug_id) as \"RLS_id\",
					COALESCE(L.Lpu_Name,'') as \"Lpu_Name\",
					to_char(RO.ReceptOtov_insDT, 'DD.MM.YYYY') as \"ReceptOtov_insDT\",
					to_char(RO.EvnRecept_obrDate, 'DD.MM.YYYY') as \"ReceptOtov_obrDate\",
					to_char(RO.EvnRecept_otpDate, 'DD.MM.YYYY') as \"ReceptOtov_otpDate\",
					'' as \"ReceptResult\",
					R.EvnRecept_IsOtvSigned as \"EvnRecept_IsOtvSigned\",
					R.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
					to_char(R.EvnRecept_signotvDT, 'DD.MM.YYYY') ||' '|| to_char(R.EvnRecept_signotvDT, 'HH24:MI') as \"signDT\",
					rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) ||' '|| rtrim(COALESCE(pucsign.PMUser_firName,'')) ||' '|| rtrim(COALESCE(pucsign.PMUser_secName,'')) as \"sign_Name\",
					(case
						when COALESCE(RECF.ReceptForm_Code, '') = '148 (к)'
						then 1 else 0
					end) as \"isKardio\"
					{$fields}
				FROM
					v_EvnRecept_all R 

					left join EvnRecept ER  on ER.Evn_id = R.EvnRecept_id

					left join v_Drug Drug  on Drug.Drug_id = R.Drug_id

					left join rls.v_Drug rlsDrug  on rlsDrug.Drug_id = R.Drug_rlsid

					left join rls.v_DrugComplexMnn rlsDrugComplexMnn  on rlsDrugComplexMnn.DrugComplexMnn_id = COALESCE(rlsDrug.DrugComplexMnn_id, R.DrugComplexMnn_id)

					left join rls.DrugComplexMnnName MnnName  on MnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id

					left join rls.v_Actmatters rlsActmatters  on rlsActmatters.Actmatters_id = MnnName.Actmatters_id

					left join v_DrugMnn DrugMnn  on DrugMnn.DrugMnn_id = Drug.DrugMnn_id

					left join v_Diag D  on D.Diag_id = R.Diag_id

					left join v_Lpu L  on L.Lpu_id = R.Lpu_id

					left join v_LpuSection LS  on LS.LpuSection_id = R.LpuSection_id

					left join v_MedPersonal M  on M.MedPersonal_id = R.MedPersonal_id

					{$join_msf}
					left join v_PersonPrivilege PP  on PP.PersonPrivilege_id = ER.PersonPrivilege_id

					left join v_PrivilegeType PT  on PT.PrivilegeType_id = R.PrivilegeType_id

					left join ReceptFinance RF  on RF.ReceptFinance_id = R.ReceptFinance_id

					left join DrugFinance DF  on DF.DrugFinance_id = R.DrugFinance_id

					left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = R.ReceptDiscount_id

					left join dbo.v_ReceptValid RV on RV.ReceptValid_id = R.ReceptValid_id

					left join v_ReceptForm RECF  on RECF.ReceptForm_id = R.ReceptForm_id

					left join v_OrgFarmacy A  on A.OrgFarmacy_id = R.OrgFarmacy_id

					left join v_OrgFarmacy OrgFarmacyOtp  on OrgFarmacyOtp.OrgFarmacy_id = R.OrgFarmacy_oid

					left join dbo.v_Drug as DrugOtp  on DrugOtp.Drug_id = R.Drug_oid

					left join v_DrugPrice DrugPrice  on DrugPrice.Drug_id = Drug.Drug_id

						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from v_DrugPrice DP 

								inner join v_ReceptFinance ReceptFinance  on ReceptFinance.ReceptFinance_id = DP.ReceptFinance_id

									and ReceptFinance.ReceptFinance_id = RF.ReceptFinance_id
							where DP.Drug_id = Drug.Drug_id
								and DP.DrugProto_begDate <= R.EvnRecept_setDate
								and (DP.DrugProto_EndDate is null or DP.DrugProto_EndDate >= R.EvnRecept_setDate)
						)
					left join v_Person_all P  on P.Person_id = R.Person_id AND P.PersonEvn_id = R.PersonEvn_id AND P.Server_id = R.Server_id

					left join WhsDocumentCostItemType  on WhsDocumentCostItemType.WhsDocumentCostItemType_id = R.WhsDocumentCostItemType_id

					left join v_ReceptOtov RO  on RO.EvnRecept_id = R.EvnRecept_id

					left join v_pmUserCache pucsign  on R.pmUser_signotvID = pucsign.PMUser_id

					{$join}
				WHERE
					R.EvnRecept_id = :EvnRecept_id
					{$privilegeFilter}
                limit 1
			";
			// echo getDebugSQL($query, $params); exit();
			$result = $this->db->query($query, $params);

			if (false == is_object($result)) {
				return false;
			}
			$response = $result->result('array');
			if(!empty($response))
			{
				$query = "
					select
						ro.ReceptOtov_id as \"ReceptOtov_id\"
					from
						ReceptOtov ro 

					where
						ro.EvnRecept_id = :EvnRecept_id;
				";
				$ro_data = $this->getFirstRowFromQuery($query, $params);
				if(empty($ro_data['ReceptOtov_id']))
				{
					$response[0]['ReceptResult'] = 'Не было обращения';
				}
				else
				{
					if(empty($response[0]['ReceptOtov_obrDate']) && empty($response[0]['ReceptOtov_otpDate'])) //Дата обращения и дата отпуска пустые
					{
						$response[0]['ReceptResult'] = 'Обращение в аптеку '.$response[0]['ReceptOtov_insDT'];
					}
					if(!empty($response[0]['ReceptOtov_obrDate']) && empty($response[0]['ReceptOtov_otpDate'])) //Дата обращения есть, дата отпуска пустая
					{
						$response[0]['ReceptResult'] = 'На отсроченном обслуживании '.$response[0]['ReceptOtov_obrDate'];
					}
					if(!empty($response[0]['ReceptOtov_otpDate']))
					{
						$response[0]['ReceptResult'] = 'Обеспечен '.$response[0]['ReceptOtov_otpDate'];
					}

				}
			}
			if (!empty($response) && !empty($response[0]['EvnRecept_otpDate'])) {
				// предварительно получим идентификаторы ReceptOtov
				$query = "
					Select ReceptOtov_id  as \"ReceptOtov_id\" from ReceptOtov  where EvnRecept_id = ?

				";
				$qr = $this->db->query($query, array($response[0]['EvnRecept_id']));
				if (!is_object($qr)) {
					return false;
				} else {
					$rows = $qr->result('array');
					$ro = array();
					if (count($rows)>0) {
						// собираем идентификаторы в массив
						foreach ($rows as $v) {
							$ro[] = $v['ReceptOtov_id'];
						}
						$query = "
							SELECT
								case 
									when rlsDrugOtp.Drug_id is not null then rlsDrugOtp.Drug_Name
									when DrugOtp.Drug_id is not null then DrugOtp.Drug_Name
									else DrugOtpStr.Drug_Name
								end as \"DrugOtp_Name\",
								cast(dus.DocumentUcStr_Count as double precision) as \"EvnRecept_Kolvo\",
								dus.DocumentUcStr_Price as \"DocumentUcStr_Price\"
							FROM
								ReceptOtov 

								left join rls.v_Drug as rlsDrugOtp  on rlsDrugOtp.Drug_id = ReceptOtov.Drug_cid

								left join v_Drug as DrugOtp  on DrugOtp.Drug_id = ReceptOtov.Drug_id

								LEFT JOIN LATERAL (

									select
										DocumentUcStr_Price,
										sum(DocumentUcStr_Count) as DocumentUcStr_Count,
										MIN(Drug_id) as Drug_id
									from
										DocumentUcStr 

									where
										DocumentUcStr.ReceptOtov_id = ReceptOtov.ReceptOtov_id
									group by
										DocumentUcStr_Price
								) dus ON true
								left join v_Drug as DrugOtpStr  on DrugOtpStr.Drug_id = dus.Drug_id

							WHERE
								ReceptOtov.ReceptOtov_id in (".implode(',', $ro).")
						";
						$result = $this->db->query($query, array());
						if (false == is_object($result)) {
							return false;
						}
						$response[0]['DrugOtp_List'] = $result->result('array');
						foreach ($response[0]['DrugOtp_List'] as $i => $row) {
							if (empty($row['DrugOtp_Name'])) {
								$response[0]['DrugOtp_List'][$i]['DrugOtp_Name'] = $response[0]['DrugOtp_Name'];
							}
						}
					} else {
						return false;
					}
				}
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
     * Возвращает рецепт
     */
	function getEvnReceptGeneralView($data) {
		/*,'Lpu_id'=>$data['Lpu_id']
		$accessType = 'R.Lpu_id = :Lpu_id';
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= ' and R.MedPersonal_id = MSF.MedPersonal_id and R.LpuSection_id = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = :user_MedStaffFact_id';

			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}
				case when {$accessType} then 'edit' else 'view' end as accessType,
		*/

		if (isset($data['EvnReceptGeneral_id'])) {
			$params = array('EvnReceptGeneral_id'=>$data['EvnReceptGeneral_id'], 'Lpu_id'=>$data['Lpu_id']);
			$join_msf = '';
			$query = "
				SELECT 
					R.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
					to_char(R.EvnReceptGeneral_begDate, 'DD.MM.YYYY') as \"EvnReceptGeneral_setDate\",

					R.EvnReceptGeneral_Ser as \"EvnReceptGeneral_Ser\",
					R.EvnReceptGeneral_Num as \"EvnReceptGeneral_Num\",
					cast(R.EvnReceptGeneral_Kolvo as double precision) as \"EvnReceptGeneral_Kolvo\",
					COALESCE(R.EvnReceptGeneral_Signa,'') as \"EvnReceptGeneral_Signa\",		

					case when (R.EvnReceptGeneral_Is7Noz is not null AND R.EvnReceptGeneral_Is7Noz = 2) then 'да' else 'нет' end as \"EvnReceptGeneral_Is7Noz\",
					CASE
						when R.Drug_rlsid is not null
						then COALESCE(DF.DrugFinance_Name,'')

						else COALESCE(RF.ReceptFinance_Name,'')

					END as \"ReceptFinance_Name\",
					COALESCE(RD.ReceptDiscount_Name,'') as \"ReceptDiscount_Name\",		

					COALESCE(RV.ReceptValid_Name,'') as \"ReceptValid_Name\",		

					COALESCE(LS.LpuSection_Name,'') as \"LpuSection_Name\",

					COALESCE(M.Person_Fin,'') as \"MedPersonal_Fin\",

					R.Diag_id as \"Diag_id\",
					COALESCE(D.Diag_Code,'') as \"Diag_Code\",

					COALESCE(D.Diag_Name,'') as \"Diag_Name\",

					CASE
						WHEN dbo.tzGetDate() >= case
							when RV.ReceptValid_Code = 1 then interval '1 month' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 2 then interval '3 month' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 3 then interval '14 day' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 4 then interval '5 day' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 5 then interval '2 month' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 7 then interval '10 day' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 8 then interval '60 day' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 9 then interval '30 day' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 10 then interval '90 day' + R.EvnReceptGeneral_setDate
							when RV.ReceptValid_Code = 11 then interval '15 day' + R.EvnReceptGeneral_setDate
						end
						and R.EvnReceptGeneral_otpDT is null
							THEN 'Просрочен'
						WHEN R.EvnReceptGeneral_otpDT is not null THEN 'Отоварен'
						WHEN R.EvnReceptGeneral_otpDT is null and R.EvnReceptGeneral_obrDT is not null THEN 'Отсрочен'
						WHEN R.ReceptDelayType_id = 3 THEN 'Отказ'
						WHEN R.ReceptDelayType_id is not  null THEN 'Выписан'
						else 'Выписан'
					END as \"ReceptDelayType_Name\",
					
					
					case when 
						R.EvnReceptGeneral_endDate is not null then 'До ' || to_char(R.EvnReceptGeneral_endDate, 'DD.MM.YYYY') 

						else	RV.ReceptValid_Name
					end as \"EvnReceptGeneral_Valid\",
					
					COALESCE(P.Person_SurName,'') as \"Person_Surname\",

					COALESCE(P.Person_FirName,'') as \"Person_Firname\",

					COALESCE(P.Person_SecName,'') as \"Person_Secname\",

					to_char(P.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

					CASE
						when R.Drug_rlsid IS Not null
						then COALESCE(rlsDrug.Drug_Name,'')

						else COALESCE(Drug.Drug_Name,'')

					END as \"Drug_Name\",
					CASE
						when COALESCE(R.Drug_rlsid, R.DrugComplexMnn_id) is not null

						then coalesce(rlsDrugComplexMnn.DrugComplexMnn_RusName,rlsActmatters.RUSNAME,'')
						else DrugMnn.DrugMnn_Name
					END as \"DrugMnn_Name\",
					cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
					A.OrgFarmacy_Nick as \"OrgFarmacy_Name\",
					to_char(R.EvnReceptGeneral_otpDT, 'DD.MM.YYYY') as \"EvnReceptGeneral_otpDate\",

                    WhsDocumentCostItemType.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
                    R.Drug_rlsid as \"Drug_rlsid\",
					COALESCE(Drug.RLS_id,rlsDrug.Drug_id) as \"RLS_id\",

					COALESCE(L.Lpu_Name,'') as \"Lpu_Name\",

					recf.ReceptForm_Name as \"ReceptForm_Name\",
					case when RU.ReceptUrgency_id is not null then ' Срочность: ' || RU.ReceptUrgency_Name else '' end as \"ReceptUrgency\",
					case
						when R.EvnReceptGeneral_IsChronicDisease is not null and R.EvnReceptGeneral_IsChronicDisease = 2 then ' Пациенту с хроническими заболеваниями '
						when R.EvnReceptGeneral_IsSpecNaz is not null and R.EvnReceptGeneral_IsSpecNaz = 2 then ' По специальному назначению '
						else ''						
					end as \"AddInfo\",
					COALESCE(R.EvnReceptGeneral_Period,'') as \"ReceptPeriod\"

				FROM
					v_EvnReceptGeneral R 

					left join v_Drug Drug  on Drug.Drug_id = R.Drug_id

					left join rls.v_Drug rlsDrug  on rlsDrug.Drug_id = R.Drug_rlsid

					left join rls.v_DrugComplexMnn rlsDrugComplexMnn  on rlsDrugComplexMnn.DrugComplexMnn_id = COALESCE(rlsDrug.DrugComplexMnn_id, R.DrugComplexMnn_id)


					left join rls.DrugComplexMnnName MnnName  on MnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id

					left join rls.v_Actmatters rlsActmatters  on rlsActmatters.Actmatters_id = MnnName.Actmatters_id

					left join v_DrugMnn DrugMnn  on DrugMnn.DrugMnn_id = Drug.DrugMnn_id

					left join v_Diag D  on D.Diag_id = R.Diag_id

					left join v_Lpu L  on L.Lpu_id = R.Lpu_id

					left join v_LpuSection LS  on LS.LpuSection_id = R.LpuSection_id

					left join v_MedPersonal M  on M.MedPersonal_id = R.MedPersonal_id

					{$join_msf}
					left join ReceptFinance RF  on RF.ReceptFinance_id = R.ReceptFinance_id

					left join DrugFinance DF  on DF.DrugFinance_id = R.DrugFinance_id

					left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = R.ReceptDiscount_id

					left join dbo.v_ReceptValid RV on RV.ReceptValid_id = R.ReceptValid_id

					left join v_OrgFarmacy A  on A.OrgFarmacy_id = R.OrgFarmacy_id

					left join v_OrgFarmacy OrgFarmacyOtp  on OrgFarmacyOtp.OrgFarmacy_id = R.OrgFarmacy_oid

					left join dbo.v_Drug as DrugOtp  on DrugOtp.Drug_id = R.Drug_oid

					left join v_DrugPrice DrugPrice  on DrugPrice.Drug_id = Drug.Drug_id

						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from v_DrugPrice DP 

								inner join v_ReceptFinance ReceptFinance  on ReceptFinance.ReceptFinance_id = DP.ReceptFinance_id

									and ReceptFinance.ReceptFinance_id = RF.ReceptFinance_id
							where DP.Drug_id = Drug.Drug_id
								and DP.DrugProto_begDate <= R.EvnReceptGeneral_setDate
								and (DP.DrugProto_EndDate is null or DP.DrugProto_EndDate >= R.EvnReceptGeneral_setDate)
						)
					left join v_Person_all P  on P.Person_id = R.Person_id AND P.PersonEvn_id = R.PersonEvn_id AND P.Server_id = R.Server_id

					left join WhsDocumentCostItemType  on WhsDocumentCostItemType.WhsDocumentCostItemType_id = R.WhsDocumentCostItemType_id

					left join v_ReceptForm recf  on recf.ReceptForm_id = R.ReceptForm_id

					left join v_ReceptUrgency RU on RU.ReceptUrgency_id = R.ReceptUrgency_id
				WHERE
					R.EvnReceptGeneral_id = :EvnReceptGeneral_id
				limit 1
			";
			//echo getDebugSQL($query, $params); exit();
			$result = $this->db->query($query, $params);

			if (false == is_object($result)) {
				return false;
			}
			$response = $result->result('array');
			//Получим медикаменты рецепта:
			$query_drugs = "
				select 
					ERGDL.EvnReceptGeneralDrugLink_id as \"EvnReceptGeneralDrugLink_id\",
					ERGDL.EvnReceptGeneralDrugLink_Kolvo::text as \"EvnReceptGeneral_Kolvo\",
					ERGDL.EvnReceptGeneralDrugLink_Signa as \"EvnReceptGeneral_Signa\",
					case when ECTD.Drug_id is null then DCM.DrugComplexMnn_RusName else D.Drug_ShortName end as \"DrugMnn_Name\"
				from v_EvnReceptGeneralDrugLink ERGDL 

				inner join v_EvnCourseTreatDrug ECTD  on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id

				left join rls.v_DrugComplexMnn DCM2  on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid

				left join rls.v_Drug D  on D.Drug_id = ECTD.Drug_id

				where ERGDL.EvnReceptGeneral_id = :EvnReceptGeneral_id
			";
			$result_drugs = $this->db->query($query_drugs,$params);
			if(is_object($result_drugs))
			{
				$result_drugs = $result_drugs->result('array');
				for($i=0;$i<count($result_drugs);$i++)
				{
					$response[0]['EvnReceptGeneralDrugLink_id'.$i] = $result_drugs[$i]['EvnReceptGeneralDrugLink_id'];
					$response[0]['DrugMnn_Name'.$i] = $result_drugs[$i]['DrugMnn_Name'];
					$response[0]['EvnReceptGeneral_Kolvo'.$i] = $result_drugs[$i]['EvnReceptGeneral_Kolvo'] . ' ' . $response[0]['ReceptPeriod'];
					$response[0]['EvnReceptGeneral_Signa'.$i] = $result_drugs[$i]['EvnReceptGeneral_Signa'];
					
					//Получим данные по обеспечению
					$response[0]['GeneralReceptSupply'.$i] = null;
					$query_supp = "
						select
							GRS.GeneralReceptSupply_id as \"GeneralReceptSupply_id\",
							to_char(GRS.GeneralReceptSupply_SupplyDate, 'DD.MM.YYYY') as \"SuppDate\",

							COALESCE(O.Org_Nick,'') as \"SuppFarm\",

							case when D.Drug_id is null then DCM.DrugComplexMnn_RusName else D.Drug_ShortName end as \"SuppDrug\",
							GRS.GeneralReceptSupply_DrugPrice as \"SuppPrice\",
							GRS.GeneralReceptSupply_DrugVolume as \"SuppKolvo\"
						from v_GeneralReceptSupply GRS 

						left join v_EvnReceptGeneralDrugLink ERGDL  on ERGDL.EvnReceptGeneralDrugLink_id = GRS.EvnReceptGeneralDrugLink_id

						inner join v_EvnCourseTreatDrug ECTD  on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id

						left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id

						left join rls.v_DrugComplexMnn DCM2  on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid

						left join rls.v_Drug D  on D.Drug_id = COALESCE(GRS.Drug_id,ECTD.Drug_id)



						left join OrgFarmacy OFarm  on OFarm.OrgFarmacy_id = GRS.OrgFarmacy_id

						left join Org O  on O.Org_id = OFarm.Org_id

						where GRS.EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id
					";
					$result_supp = $this->db->query($query_supp,array('EvnReceptGeneralDrugLink_id' => $response[0]['EvnReceptGeneralDrugLink_id'.$i]));
					if(is_object($result_supp))
					{
						$result_supp = $result_supp->result('array');
						if(is_array($result_supp) && count($result_supp)>0)
						{
							$response[0]['SuppInfo'.$i] = '';
							for($s=0;$s<count($result_supp); $s++)
							{
								$response[0]['GeneralReceptSupply'.$i] = $result_supp[$s]['GeneralReceptSupply_id'];
								$response[0]['SuppInfo'.$i] .= '<b>Дата обеспечения, аптека: </b>'.$result_supp[$s]['SuppDate'].' ' .$result_supp[$s]['SuppFarm'].'<br>';
								$response[0]['SuppInfo'.$i] .= 'Медикамент, выданный по рецепту: '.$result_supp[$s]['SuppDrug'].'<br>';
								if(!empty($result_supp[0]['SuppPrice']))
									$response[0]['SuppInfo'.$i] .= 'Цена медикамента, отпущенного по рецепту: '.$result_supp[$s]['SuppPrice'].'<br>';
								if(!empty($result_supp[0]['SuppKolvo']))
									$response[0]['SuppInfo'.$i] .= 'Количество выданных упаковок: '.$result_supp[$s]['SuppKolvo'].'<br>';
								$response[0]['SuppInfo'.$i] .= '<br>';
							}
						}
					}
				}
			}
			//var_dump($response);die;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для шаблона print_evnrecept_list
	 * Используется библиотекой swMarker
	 */
	function getEvnReceptPrintData($data) {
		$query = "
			select
				COALESCE(

					case
						when ER.EvnRecept_IsExtemp = 2 then ER.EvnRecept_ExtempContents
						when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then COALESCE(DrugMnn_Fed.DrugMnn_NameLat, DrugMnn_Fed.DrugMnn_Name, '')

						when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then COALESCE(DrugMnn_Reg.DrugMnn_NameLat, DrugMnn_Reg.DrugMnn_Name, '')

						when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 1 then COALESCE(DrugMnn_Noz.DrugMnn_NameLat, DrugMnn_Noz.DrugMnn_Name, '')

						when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then COALESCE(DrugTorg_Fed.DrugTorg_NameLat, DrugTorg_Fed.DrugTorg_Name, '')

						when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then COALESCE(DrugTorg_Reg.DrugTorg_NameLat, DrugTorg_Reg.DrugTorg_Name, '')

						when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 1 then COALESCE(DrugTorg_Noz.DrugTorg_NameLat, DrugTorg_Noz.DrugTorg_Name, '')

					end
				, '') as \"Drug_Name\",
				COALESCE(RTRIM(ER.EvnRecept_Ser), '') as \"EvnRecept_Ser\",

				COALESCE(RTRIM(ER.EvnRecept_Num), '') as \"EvnRecept_Num\",

				COALESCE(RTRIM(ER.EvnRecept_Signa), '') as \"EvnRecept_Signa\",

				to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\"

			from v_EvnRecept ER 

				inner join ReceptFinance  on ReceptFinance.ReceptFinance_id = ER.ReceptFinance_id

				left outer join YesNo MnnYesNo  on MnnYesNo.YesNo_id = ER.EvnRecept_IsMnn

				left outer join YesNo Is7Noz  on Is7Noz.YesNo_id = ER.EvnRecept_Is7Noz

				left join v_DrugFed Drug_Fed  on Drug_Fed.Drug_id = ER.Drug_id

				left join v_DrugReg Drug_Reg  on Drug_Reg.Drug_id = ER.Drug_id

				left join v_Drug7noz Drug_Noz  on Drug_Noz.Drug_id = ER.Drug_id

				left join v_DrugMnn DrugMnn_Fed  on DrugMnn_Fed.DrugMnn_id = Drug_Fed.DrugMnn_id

				left join v_DrugMnn DrugMnn_Reg  on DrugMnn_Reg.DrugMnn_id = Drug_Reg.DrugMnn_id

				left join v_DrugMnn DrugMnn_Noz  on DrugMnn_Noz.DrugMnn_id = Drug_Noz.DrugMnn_id

				left join v_DrugTorg DrugTorg_Fed  on DrugTorg_Fed.DrugTorg_id = Drug_Fed.DrugTorg_id

				left join v_DrugTorg DrugTorg_Reg  on DrugTorg_Reg.DrugTorg_id = Drug_Reg.DrugTorg_id

				left join v_DrugTorg DrugTorg_Noz  on DrugTorg_Noz.DrugTorg_id = Drug_Noz.DrugTorg_id

			where ER.EvnRecept_pid = :Evn_pid
		";
		
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Возвращает данные рецепта
     */
	function getEvnReceptViewData($data) {

		$filter = '';
		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("er.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter = " and $privilegeFilter";
		}


		$needAccessFilter = true;
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
		{
			$needAccessFilter = false;
		}

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('er.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}


		$query = "
			SELECT
				er.Lpu_id as \"Lpu_id\",
				er.Diag_id as \"Diag_id\",-- для filterNotViewDiag
				er.EvnRecept_id as \"EvnRecept_id\",
				er.EvnRecept_Ser as \"EvnRecept_Ser\",
				er.EvnRecept_Num as \"EvnRecept_Num\",
				cast(er.EvnRecept_Kolvo as double precision) as \"EvnRecept_Kolvo\",
				(case
					when COALESCE(er.Drug_rlsid, er.DrugComplexMnn_id) is not null
					then coalesce(dcm.DrugComplexMnn_RusName, am.RUSNAME,'')
					else COALESCE(dm.DrugMnn_Name, d.Drug_Name)
				end) as \"Drug_Name\",
				0 as \"Children_Count\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				er.ReceptForm_id as \"ReceptForm_id\",
				er.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
				er.EvnRecept_IsPrinted as \"EvnRecept_IsPrinted\",
				to_char(ER.EvnRecept_obrDT, 'DD.MM.YYYY') as \"EvnRecept_obrDate\",
				to_char(ER.EvnRecept_otpDT, 'DD.MM.YYYY') as \"EvnRecept_otpDate\",
				case when 2 = coalesce(ER.EvnRecept_IsDelivery, 1)
					then 'Выдан уполномоченному лицу'
					else ''
				end as \"EvnRecept_IsDelivery\",
				er.Signatures_id as \"Signatures_id\"
			FROM
				v_EvnRecept er 
				left join v_Diag Diag  on Diag.Diag_id = er.Diag_id
				left join v_Drug d  on d.Drug_id = er.Drug_id
				left join v_DrugMnn dm  on dm.DrugMnn_id = d.DrugMnn_id
				left join rls.v_Drug rd  on rd.Drug_id = er.Drug_rlsid
				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = er.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_Actmatters am  on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = er.ReceptDelayType_id
				left join v_ReceptType RT  on RT.ReceptType_id = er.ReceptType_id
				left join v_ReceptForm RF  on RF.ReceptForm_id = er.ReceptForm_id
				left join v_LpuSection LS  on LS.LpuSection_id = er.LpuSection_id
			WHERE
				er.EvnRecept_pid = :EvnRecept_pid
				and COALESCE(RDT.ReceptDelayType_Code,-1) <> 4
				and COALESCE(RF.ReceptForm_Code, '') <> '148 (к)'
				{$filter}
		";
		$params = array('EvnRecept_pid' => $data['EvnRecept_pid'], 'Lpu_id' => $data['Lpu_id']);
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			$resp = $result->result('array');

			$EvnReceptIds = [];
			foreach($resp as $one) {
				if (!empty($one['EvnRecept_id']) && $one['EvnRecept_IsSigned'] == 2 && !in_array($one['EvnRecept_id'], $EvnReceptIds)) {
					$EvnReceptIds[] = $one['EvnRecept_id'];
				}
			}

			$isEMDEnabled = $this->config->item('EMD_ENABLE');
			if (!empty($EvnReceptIds) && !empty($isEMDEnabled)) {
				$this->load->model('EMD_model');
				$signStatus = $this->EMD_model->getSignStatus([
					'EMDRegistry_ObjectName' => 'EvnRecept',
					'EMDRegistry_ObjectIDs' => $EvnReceptIds,
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
				]);

				foreach($resp as $key => $one) {
					$resp[$key]['EvnRecept_SignCount'] = 0;
					$resp[$key]['EvnRecept_MinSignCount'] = 0;
					if (!empty($one['EvnRecept_id']) && $one['EvnRecept_IsSigned'] == 2 && isset($signStatus[$one['EvnRecept_id']])) {
						$resp[$key]['EvnRecept_SignCount'] = $signStatus[$one['EvnRecept_id']]['signcount'];
						$resp[$key]['EvnRecept_MinSignCount'] = $signStatus[$one['EvnRecept_id']]['minsigncount'];
						$resp[$key]['EvnRecept_IsSigned'] = $signStatus[$one['EvnRecept_id']]['signed'];
					}
				}
			}

			return swFilterResponse::filterNotViewDiag($resp, $data);
		}
		else
		{
			return false;
		}
	}

	/**
     * Возвращает данные рецепта
     */
	function getEvnReceptGeneralViewData($data) {

		$filter = '';
		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("er.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter = " and $privilegeFilter";
		}


		$needAccessFilter = true;
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
		{
			$needAccessFilter = false;
		}

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('er.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}

		$query = "
			SELECT
				er.Lpu_id as \"Lpu_id\",
				er.Diag_id as \"Diag_id\",-- для filterNotViewDiag
				er.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
				er.EvnReceptGeneral_Ser as \"EvnReceptGeneral_Ser\",
				er.EvnReceptGeneral_Num as \"EvnReceptGeneral_Num\",
				cast(er.EvnReceptGeneral_Kolvo as double precision) as \"EvnReceptGeneral_Kolvo\",
				COALESCE(rd.Drug_Name, d.Drug_Name, dcm.DrugComplexMnn_RusName) as \"Drug_Name\",
				0 as \"Children_Count\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				er.ReceptForm_id as \"ReceptForm_id\",
				er.EvnReceptGeneral_IsSigned as \"EvnReceptGeneral_IsSigned\",
				case when 2 = coalesce(er.EvnReceptGeneral_IsDelivery, 1) then 'Выдан уполномоченному лицу' else '' end as \"EvnReceptGeneral_IsDelivery\"
			FROM
				v_EvnReceptGeneral er 
				left join v_Diag Diag  on Diag.Diag_id = er.Diag_id
				left join v_Drug d  on d.Drug_id = er.Drug_id
				left join rls.v_Drug rd  on rd.Drug_id = er.Drug_rlsid
				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = er.DrugComplexMnn_id
				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = er.ReceptDelayType_id
				left join v_ReceptType RT  on RT.ReceptType_id = er.ReceptType_id
				left join v_LpuSection LS  on LS.LpuSection_id = er.LpuSection_id
			WHERE
				er.EvnReceptGeneral_pid = :EvnReceptGeneral_pid
				{$filter}
				
		";
		$params = array('EvnReceptGeneral_pid' => $data['EvnReceptGeneral_pid'], 'Lpu_id' => $data['Lpu_id']);
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			//return $result->result('array');
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает данные рецепта
	 */
	function getEvnReceptKardioViewData($data) {

		$filter = '';
		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("er.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter = " and $privilegeFilter";
		}


		$needAccessFilter = true;
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
		{
			$needAccessFilter = false;
		}

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('er.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}


		$query = "
			SELECT
				er.Lpu_id as \"Lpu_id\",
				er.Diag_id as \"Diag_id\",-- для filterNotViewDiag
				er.EvnRecept_id as \"EvnRecept_id\",
				er.EvnRecept_Ser as \"EvnRecept_Ser\",
				er.EvnRecept_Num as \"EvnRecept_Num\",
				cast(er.EvnRecept_Kolvo as decimal) as \"EvnRecept_Kolvo\",
				(case
					when COALESCE(er.Drug_rlsid, er.DrugComplexMnn_id) is not null

					then coalesce(dcm.DrugComplexMnn_RusName, am.RUSNAME,'')
					else d.Drug_Name
				end) as \"Drug_Name\",
				0 as \"Children_Count\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				er.ReceptForm_id as \"ReceptForm_id\",
				er.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
				er.EvnRecept_IsPrinted as \"EvnRecept_IsPrinted\",
				to_char(ER.EvnRecept_obrDT, 'DD.MM.YYYY') as \"EvnRecept_obrDate\",
				to_char(ER.EvnRecept_otpDT, 'DD.MM.YYYY') as \"EvnRecept_otpDate\",
				case when 2 = coalesce(er.EvnRecept_IsDelivery, 1) then 'Выдан уполномоченному лицу' else '' end as \"EvnRecept_IsDelivery\"
			FROM
				v_EvnRecept er 

				left join v_Diag Diag  on Diag.Diag_id = er.Diag_id

				left join v_Drug d  on d.Drug_id = er.Drug_id

				left join rls.v_Drug rd  on rd.Drug_id = er.Drug_rlsid

				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = er.DrugComplexMnn_id

				left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				left join rls.v_Actmatters am  on am.ACTMATTERS_ID = dcmn.Actmatters_id

				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = er.ReceptDelayType_id

				left join v_ReceptType RT  on RT.ReceptType_id = er.ReceptType_id

				left join v_ReceptForm RF  on RF.ReceptForm_id = er.ReceptForm_id

				left join v_LpuSection LS  on LS.LpuSection_id = er.LpuSection_id

			WHERE
				er.EvnRecept_pid = :EvnRecept_pid
				and COALESCE(RDT.ReceptDelayType_Code,-1) <> 4

				and RF.ReceptForm_Code = '148 (к)' 
				{$filter}
		";
		$params = array('EvnRecept_pid' => $data['EvnReceptKardio_pid'], 'Lpu_id' => $data['Lpu_id']);
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			//return $result->result('array');
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает данные о видимости раздела рецепотов по программе ДЛО Кардио
	 */
	function getEvnReceptKardioVisibleData($data) {
		$result = array(
			'is_visible' => false,
			'success' => true
		);

		$query_params = array();
		$object_data = array();

		if ($data['parent_object'] == 'EvnPL') { //лечение
			$query = "
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact msf 

					left join v_PostMed ps  on ps.PostMed_id = msf.Post_id

				where
					msf.MedStaffFact_id = :MedStaffFact_id and
					ps.PostMed_Name iLIKE '%кардиолог%'
			    limit 1

			";
			$query_params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$check_data = $this->getFirstRowFromQuery($query, $query_params);

			$result['is_visible'] = !empty($check_data['MedStaffFact_id']);
		}

		if ($data['parent_object'] == 'EvnPS') { //КВС
			//получение данных родительского события
			$query = "
				select
					e.Lpu_id as \"Lpu_id\",
					e.Person_id as \"Person_id\"
				from
					v_{$data['parent_object']} e 

				where
					e.{$data['parent_object']}_id = :Evn_id;
			";
			$query_params['Evn_id'] = $data['parent_object_value'];
			$object_data = $this->getFirstRowFromQuery($query, $query_params);

			if (!empty($object_data['Lpu_id']) && !empty($object_data['Person_id'])) {
				$query = "
					with mv1 as (
						select VolumeType_id as v
						from v_VolumeType
						where VolumeType_Code = '2019_kardio'
						limit 1
					), mv2 as (
						-- поверка наличия объемов
						select
							av_value.AttributeValue_id as a
						from
							dbo.AttributeValue av_value
							inner join dbo.AttributeValue av_lpu on av_lpu.Attribute_id = (
									select a.Attribute_id
									from v_AttributeVision av
										left join v_Attribute a on a.Attribute_id = av.Attribute_id
									where av.AttributeVision_TablePKey = (select v from mv1)
										and a.Attribute_SysNick = 'Lpu'
									order by
										a.Attribute_id
									limit 1
								) and av_lpu.AttributeValue_rid = av_value.AttributeValue_id
						where av_value.AttributeValue_TablePKey = (select v from mv1)
							and	av_value.Attribute_id = (
								select
									a.Attribute_id
								from v_AttributeVision av
									left join v_Attribute a on a.Attribute_id = av.Attribute_id
								where av.AttributeVision_TablePKey = (select v from mv1)
									and a.Attribute_SysNick = 'Value'
								order by
									a.Attribute_id
								limit 1
							)
							and	av_lpu.AttributeValue_ValueIdent = :Lpu_id
						limit 1
					), mv3 as (
						-- проверка наличия рецептов
						select
							er.EvnRecept_id as e
						from v_EvnRecept er
						where
							er.Person_id = :Person_id and
							er.ReceptForm_id = (
								select ReceptForm_id
								from v_ReceptForm
								where ReceptForm_Code = '148 (к)'
								limit 1
							)
						limit 1
					), mv4 as (
						-- проверка наличия дествующей льготы по программе ДЛО Кардио
						select
							pp.PersonPrivilege_id as p
						from
							v_PersonPrivilege pp
						where pp.Person_id = :Person_id
							and pp.PrivilegeType_id = (
								select PrivilegeType_id
								from v_PrivilegeType
								where PrivilegeType_SysNick = 'kardio'
								limit 1
							)
							and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
							and (
								pp.PersonPrivilege_endDate is null or
								pp.PersonPrivilege_endDate >= dbo.tzGetDate()
							)
						limit 1
					)
					
					select
						(select a from mv2) as \"AttributeValue_id\",
						(select e from mv3) as \"EvnRecept_id\",
						(select p from mv4) as \"PersonPrivilege_id\"
				";
				$check_data = $this->getFirstRowFromQuery($query, $object_data);
				$result['is_visible'] = (!empty($check_data['AttributeValue_id']) && (!empty($check_data['EvnRecept_id'])  || !empty($check_data['PersonPrivilege_id'])));
			}
		}

		if ($data['parent_object'] == 'EvnUslugaTelemed') { //телемедицинская услуга
			if (!empty($data['parent_object_value'])) {
				//получение данных родительского события
				$query = "
					select
						:Evn_id as \"Evn_id\",
						e.Lpu_id as \"Lpu_id\"
					from
						v_{$data['parent_object']} e
					where
						e.{$data['parent_object']}_id = :Evn_id;
				";
				$query_params['Evn_id'] = $data['parent_object_value'];
				$object_data = $this->getFirstRowFromQuery($query, $query_params);
			} else {
				$object_data = array(
					'Evn_id' => null,
					'Lpu_id' => $data['Lpu_id']
				);
			}

			if (!empty($object_data['Lpu_id'])) {
				$query = "
					--без этого комментария codeigniter всегда возвращает пустой запрос (в SQL Manager работает нормально)
					CREATE OR REPLACE FUNCTION pg_temp.isVisibleEvnUslugaTelemed_Query(
						in Lpu_id bigint default null::bigint,
						in Evn_id bigint default null::bigint,
						out AttributeValue_id bigint,
						out EvnRecept_id bigint
					)
					RETURNS record
					AS $$
					DECLARE
						p_Lpu_id bigint;
						p_Evn_id bigint;
						p_AttributeValue_id bigint;
						p_EvnRecept_id bigint;
						p_VolumeType_id bigint;
						p_Value_Attribute_id bigint;
						p_Lpu_Attribute_id bigint;
						p_ReceptForm_id bigint;
						p_current_date date;
					BEGIN
						p_Lpu_id = Lpu_id;
						p_Evn_id = Evn_id;
					
						p_VolumeType_id = (select VolumeType_id from v_VolumeType where VolumeType_Code = '2019_kardio' limit 1);
						p_ReceptForm_id = (select ReceptForm_id from v_ReceptForm where ReceptForm_Code = '148 (к)' limit 1);
						p_current_date = dbo.tzGetDate();
							
						p_Value_Attribute_id = (
							select
								min(a.Attribute_id)
							from
								v_AttributeVision av
								left join v_Attribute a on a.Attribute_id = av.Attribute_id
							where
								av.AttributeVision_TablePKey = p_VolumeType_id and
								a.Attribute_SysNick = 'Value'
							limit 1	
						);
						
						p_Lpu_Attribute_id = (
							select
								min(a.Attribute_id)
							from
								v_AttributeVision av
								left join v_Attribute a on a.Attribute_id = av.Attribute_id
							where
								av.AttributeVision_TablePKey = p_VolumeType_id and
								a.Attribute_SysNick = 'Lpu'
							limit 1	
						);
						
						-- поверка наличия объемов
						AttributeValue_id = (
							select
								av_value.AttributeValue_id 
							from
								dbo.AttributeValue av_value
								inner join dbo.AttributeValue av_lpu on av_lpu.Attribute_id = p_Lpu_Attribute_id and av_lpu.AttributeValue_rid = av_value.AttributeValue_id
							WHERE
								av_value.AttributeValue_TablePKey = p_VolumeType_id and
								av_value.Attribute_id = p_Value_Attribute_id and
								av_lpu.AttributeValue_ValueIdent = p_Lpu_id
							limit 1
						);
						
						-- проверка наличия рецептов
						if (p_Evn_id is not null) then
							EvnRecept_id = (
								select
									er.EvnRecept_id
								from
									v_EvnRecept er
								where
									er.EvnRecept_pid = p_Evn_id and
									er.ReceptForm_id = p_ReceptForm_id
								limit 1
							);
						end if;
					END;
					$$ LANGUAGE 'plpgsql';
					
					select
						AttributeValue_id as \"AttributeValue_id\",
						EvnRecept_id as \"EvnRecept_id\"
					from pg_temp.isVisibleEvnUslugaTelemed_Query(
						Lpu_id := :Lpu_id,
						Evn_id := :Evn_id
					);
				";
				$check_data = $this->getFirstRowFromQuery($query, $object_data);
				$result['is_visible'] = (!empty($check_data['AttributeValue_id']) || !empty($check_data['EvnRecept_id']));
			}
		}
		return $result;
	}

    /**
     * Запись в лог
     */
	function writeToLog($string) {
		$f = fopen($this->log_file, $this->log_file_access_type);
		fputs($f, $string);
		fclose($f);
	}

    /**
     * Проверка рецепта пациента
     */
	function checkEvnRecept($data) {
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkEvnRecept\n\r");

		$checkResult = 'false';
		$queryParams = array();

		$query = "
			SELECT
				count(*) as \"EvnRecept_Count\"
			FROM
				v_EvnRecept 

			WHERE (1 = 1)
				and Drug_id = :Drug_id
				and EvnRecept_setDate = cast(:EvnRecept_setDate as timestamp)
				and EvnRecept_id = COALESCE(CAST(:EvnRecept_id as bigint), 0)

				and Person_id = :Person_id
		;";

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		$queryParams['EvnRecept_setDate'] = $data['EvnRecept_setDate'];
		// $queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['Person_id'] = $data['Person_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) ) {
				if ( $response[0]['EvnRecept_Count'] == 0 ) {
					$checkResult = 'true';
				}
			}
			else {
				$checkResult = 'error';
			}
		}
		else {
			$checkResult = 'error';
		}

		return $checkResult;
	}

    /**
     * Проверка лекарственного вещества на соответствие рецепту
     */
	function checkEvnMatterRecept($data) {

		//$checkResult = 'false';
        $res = Array();
        $queryParams = array(
            'Diag_id' => $data['Diag_id'],
            'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
        );

		$query = "
            select distinct
                left(CLSIIC.NAME,position(' ' in CLSIIC.NAME)-1) as \"Name\", -- выделяем код диагноза действующего вещества
                DC.Diag_Code as \"Diag_Code\", -- код выбранного диагноза
                case
                    when LENGTH(left(CLSIIC.NAME,position(' ' in CLSIIC.NAME)-1)) = 3 then
                        case
                            when  left(CLSIIC.NAME, position(' ' in CLSIIC.NAME)-1) = left(DC.Diag_code, position('.' in DC.Diag_code )-1) then '1' else '0'
                        end
                    else
                        case
                            when  left(CLSIIC.NAME, position(' ' in CLSIIC.NAME)-1) = DC.Diag_code then '1' else '0'
                        end
                end as \"Matter_in_diag\" -- совпадает ли код выбранного диагноза и код диагноза для дейтвующего вещ-ва
            from rls.v_CLSIIC CLSIIC 

				LEFT JOIN rls.v_PREP_IIC PREP_IIC  on PREP_IIC.UNIQID = CLSIIC.CLSIIC_ID

				LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS  on PREP_ACTMATTERS.PREPID = PREP_IIC.PREPID

				LEFT JOIN rls.v_ACTMATTERS ACTMATTERS  on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID

                LEFT JOIN LATERAL (

                    select Diag_Code
                    from v_Diag D 

                    where Diag_id = :Diag_id
                ) as DC ON true
                LEFT JOIN LATERAL (

                    select COALESCE(DCM.ActMatters_id, DCMN.ActMatters_id) as ActMatters_id

                    from rls.v_DrugComplexMnn DCM 

                    left join rls.v_DrugComplexMnnName DCMN  on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id

                    where DCM.DrugComplexMnn_id = 
                    :DrugComplexMnn_id
                    and COALESCE(DCM.ActMatters_id,DCMN.ACTMATTERS_id) = PREP_ACTMATTERS.MATTERID

                ) as DM ON true
            where DM.ActMatters_id = PREP_ACTMATTERS.MATTERID
		";

        //echo getDebugSQL($query, $queryParams); exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

            return $response;
		}
		else {
            return false;
		}
	}

    /**
     * Проверка рецепта
     */
	function checkEvnReceptValues($data) {
		// upd [2012-05-14 13:27]: добавил проверку на код диагноза при выписке рецепта по 7 нозологиям
		// https://redmine.swan.perm.ru/issues/8253
		if ( $data['EvnRecept_Is7Noz'] == 2 ) {
			$query = "
				select 
					prd.PersonRegisterDiag_id as \"PersonRegisterDiag_id\"
				from
					v_PersonRegisterDiag prd 

					inner join v_PersonRegisterType prt  on prt.PersonRegisterType_id = prd.PersonRegisterType_id

				where
					prt.PersonRegisterType_SysNick = 'nolos'
					and prd.Diag_id = :Diag_id
				limit 1
			";

			$queryParams = array(
				'Diag_id' => $data['Diag_id']
			);

			$resp = $this->queryResult($query, $queryParams);

			if (empty($resp[0]['PersonRegisterDiag_id'])) {
				return array(array('Error_Msg' => 'Указанный диагноз недопустим при выписке рецепта по 7 нозологиям', 'success' => false));
			}
		}

		// Проверяем дату включения Lpu в ЛЛО
		$query = "
			SELECT
				count(*) as \"LpuPeriodDLO_Count\"
			FROM v_LpuPeriodDLO 

			WHERE LpuPeriodDLO_begDate <= dbo.tzGetDate() AND (LpuPeriodDLO_endDate >= dbo.tzGetDate() OR LpuPeriodDLO_endDate IS NULL) AND Lpu_id = :Lpu_id
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);
		
		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных', 'success' => false));
		}

		$response = $result->result('array');

		if ( !is_array($response) || !isset($response[0])) {
			return array(array('Error_Msg' => 'Ошибка при проверке актуальности включения ЛПУ в ЛЛО', 'success' => false));
		}

		if ( $response[0]['LpuPeriodDLO_Count'] <= 0 ) {
			return array(array('Error_Msg' => 'У ЛПУ отсутсвует активная дата включения в ЛЛО, выписка льготных рецептов невозможна', 'success' => false));
		}
		
		// Проверяем отделение
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkEvnReceptValues -> LpuSection_id\n\r");
		$query = "
			SELECT
				count(*) as \"LpuSection_Count\"
			FROM LpuSection 

				left join LpuUnit on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
				left join LpuBuilding on LpuBuilding.LpuBuilding_id = LpuUnit.LpuBuilding_id
				left join LpuSectionProfile on LpuSectionProfile.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id
				left join LpuUnitType on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id
			WHERE LpuSectionProfile.LpuSectionProfile_id <> 75
				and LpuUnitType.LpuUnitType_id in (2,10,12)
				and LpuSection.LpuSection_setDate is not null
				and LpuSection.LpuSection_setDate <= :EvnRecept_setDate
				and (LpuSection.LpuSection_disDate is null or LpuSection.LpuSection_disDate > :EvnRecept_setDate)
				and LpuBuilding.Lpu_id = :Lpu_id
				and LpuSection.LpuSection_id = :LpuSection_id
		";

		$queryParams = array(
			'EvnRecept_setDate' => $data['EvnRecept_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных', 'success' => false));
		}

		$response = $result->result('array');

		if ( !is_array($response) || !isset($response[0])) {
			return array(array('Error_Msg' => 'Ошибка при проверке актуальности выбранного отделения', 'success' => false));
		}

		if ( $response[0]['LpuSection_Count'] <= 0 ) {
			return array(array('Error_Msg' => 'Выбранное отделение не действовало на дату выписки рецепта', 'success' => false));
		}

		// Проверяем врача
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkEvnReceptValues -> MedPersonal_id\n\r");
		$query = "
			SELECT
				count(*) as \"MedStaffFact_Count\"
			FROM v_MedStaffFact MSF 

				left join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id

				left join LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id

				INNER JOIN LATERAL (

					select t.MedStaffFact_id 
					from v_MedStaffFact t  

					where t.MedPersonal_id = 
                    :MedPersonal_id
					and t.LpuSection_id = 
                    :LpuSection_id
					and t.Lpu_id = 
                    :Lpu_id
					order by COALESCE(WorkData_endDate, dbo.tzGetDate()) desc
					limit 1
                ) as t ON true
			WHERE
				LS.LpuSectionProfile_id <> 75
				and LU.LpuUnitType_id in (2,10,12)
				and COALESCE(MSF.MedPersonal_Code, '') not in ('', '0')

				and MSF.Lpu_id = :Lpu_id
				and MSF.LpuSection_id = :LpuSection_id
				and MSF.MedPersonal_id = :MedPersonal_id
				and MSF.WorkData_begDate is not null
				and cast(MSF.WorkData_begDate as date) <= cast(:EvnRecept_setDate as date)
				and (MSF.WorkData_endDate is null or cast(MSF.WorkData_endDate as date) >= cast(:EvnRecept_setDate as date))
		";

		/*
			https://redmine.swan.perm.ru/issues/11333
			Для сравниваемых дат в запросе добавлено приведение к типу date, чтобы отсечь возможные часы-минуты-секунды
			Плюс в сравнение даты увольнения с датой выписки рецепта добавлено равенство
		*/

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'EvnRecept_setDate' => $data['EvnRecept_setDate']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных', 'success' => false));
		}

		$response = $result->result('array');

		if ( !is_array($response) || !isset($response[0])) {
			return array(array('Error_Msg' => 'Ошибка при проверке врача', 'success' => false));
		}

		if ( $response[0]['MedStaffFact_Count'] <= 0 ) {
			return array(array('Error_Msg' => 'Указанный врач не работал в выбранном отделении на дату выписки рецепта', 'success' => false));
		}

		// Проверка льготы
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkEvnReceptValues -> PrivilegeType_id\n\r");
		$PersonPrivilege_id = $this->getFirstResultFromQuery("
			select PP.PersonPrivilege_id as \"PersonPrivilege_id\"
			from v_PersonPrivilege PP 

				inner join PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id

					--and PT.PrivilegeType_Code between 1 and 500
					and PT.PrivilegeType_id = 
                    :PrivilegeType_id
				left join v_PersonRefuse PR  ON PR.Person_id = PP.Person_id

					and PR.PersonRefuse_IsRefuse = 2
					and PR.PersonRefuse_Year = date_part('YEAR',cast(
                    :EvnRecept_setDate 
                    as timestamp))
					and PT.ReceptFinance_id = 1
					--and PT.PrivilegeType_Code < 500
			where PP.Person_id = 
            :Person_id
				and (
                :EvnRecept_Is7Noz 
                = 2 or (
					COALESCE(PR.PersonRefuse_IsRefuse, 1) = 1

					and PP.PersonPrivilege_begDate is not null
					and PP.PersonPrivilege_begDate <= 
                    :EvnRecept_setDate
					and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= 
                    :EvnRecept_setDate)
				))
			limit 1
		", array(
			'EvnRecept_Is7Noz' => $data['EvnRecept_Is7Noz'],
			'EvnRecept_setDate' => $data['EvnRecept_setDate'],
			'Person_id' => $data['Person_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'ReceptFinance_id' => $data['ReceptFinance_id']
		), true);

		if ( $result === false ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных', 'success' => false));
		}

		if ( empty($PersonPrivilege_id) ) {
			return array(array('Error_Msg' => 'Указанная льгота не действует на дату выписки рецепта', 'success' => false));
		}

		return array(array('Error_Msg' => '', 'success' => true));
	}

    /**
     * Проверка на соответствие диагноза выбранной льготе
     */
    function checkPrivDiag($data) {
        $params = array();
        $params['Diag_id'] = $data['Diag_id'];
        $params['PrivilegeType_id'] = $data['PrivilegeType_id'];
		$query_check_priv = "
			select
				D.Diag_Code as \"Diag_Code\",
				case when D.Diag_id = 
                :Diag_id 
                then 1 else 0 end as \"Diag_exists\"
			from PrivilegeDiagLink PDL 

			inner join dbo.Diag D  on D.Diag_id = PDL.Diag_id

			where PrivilegeType_id = :PrivilegeType_id
			order by D.Diag_Code
		";
		$result = $this->db->query($query_check_priv,$params);
		if(is_object($result))
		{
			$response = $result->result('array');
			return $response;
		}
		else
			return false;
		/*
        $query = "
            select PrivilegeDiagLink_id
            from dbo.PrivilegeDiagLink 

            where Diag_id = :Diag_id
            and PrivilegeType_id = :PrivilegeType_id
        ";
        //Проверим, есть ли для указанной льготы записи в справочнике "Соответствие диагнозов и льготных категорий"
        $query_check_priv = "
            select count (PrivilegeDiagLink_id) as LinkCount
            from dbo.PrivilegeDiagLink 

            where PrivilegeType_id = :PrivilegeType_id
        ";
        $result_check_priv = $this->db->query($query_check_priv,$params);
        if(is_object($result_check_priv)){
            $response_check_priv = $result_check_priv->result('array');

            if(is_array($response_check_priv) && isset($response_check_priv[0]) && $response_check_priv[0]['LinkCount'] > 0){
                //Проверка на соответствие диагноза выбранной льготе
                $result = $this->db->query($query,$params);

                if(is_object($result)){
                    $response = $result->result('array');

                    if(is_array($response) && isset($response[0]['PrivilegeDiagLink_id']))
                        return array(array('Error_Msg' => '', 'success' => true));
                    else
                        return array(array('Error_Msg' => 'Сохранение рецепта невозможно: указанный диагноз не соответствует выбранной льготе', 'success' => false));
                }
                else
                    return array(array('Error_Msg' => 'Ошибка при проверке соответствия диагноза и льготы', 'success' => false));
            }
            else
                return array(array('Error_Msg' => '', 'success' => true));
        }
        else
            return array(array('Error_Msg' => 'Ошибка при проверке соответствия диагноза и льготы', 'success' => false));
		*/
    }


    /**
     * Проверка медикомента
     */
	function checkDrugIsActual($data) {
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkDrugIsActual\n\r");

		$check_drug_is_actual = -1;
		$queryParams = array();

		$query = "
			SELECT
				count(*) as \"kolvo\"
			FROM v_DrugActive 

			WHERE DrugActive_begDate <= dbo.tzGetDate()
				and (DrugActive_endDate is null or DrugActive_endDate > dbo.tzGetDate())
				and Drug_id = :Drug_id
				and ReceptFinance_id = :ReceptFinance_id
		;";

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['ReceptFinance_id'] = $data['ReceptFinance_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( (is_array($response)) && (isset($response[0]['kolvo'])) ) {
				$check_drug_is_actual = $response[0]['kolvo'];
			}
		}

		return $check_drug_is_actual;
	}

    /**
     * Проверка остатков медикоментов
     */
	function checkDrugOstat($data) {
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkDrugOstat\n\r");

		$checkDrugOstat = 0;
		$queryParams = array();

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		// $queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		$queryParams['ReceptFinance_id'] = $data['ReceptFinance_id'];

		$query = "
			SELECT
				CASE WHEN coalesce(SUM(D.DrugOstat_Kolvo), 0) > 0
					THEN 1
					ELSE 2
				END as \"EvnRecept_IsNotOstat\"
			FROM
				v_DrugOstat_all D
			WHERE (1 = 1)
				and D.Drug_id = :Drug_id
				and D.Lpu_id = :Lpu_id
				and D.ReceptFinance_id = :ReceptFinance_id
		;";
		// and DO.OrgFarmacy_id = :OrgFarmacy_id
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( (is_array($response)) && (isset($response[0]['EvnRecept_IsNotOstat'])) ) {
				$checkDrugOstat = $response[0]['EvnRecept_IsNotOstat'];
			}
		}

		return $checkDrugOstat;
	}

    /**
     * Проверка на отказ от льгот
     */
	function checkPersonIsRefuse($data) {
		$queryParams = array();
		$response = array();

		$query = "
			SELECT
				count(*) as \"Records_Count\"
			FROM
				v_PersonRefuse 
			WHERE Person_id = :Person_id
				and :PrivilegeType_id in (select PrivilegeType_id from PrivilegeType where ReceptFinance_id = 1 and isnumeric(PrivilegeType_Code) = 1)
				and PersonRefuse_Year = date_part('YEAR',dbo.tzGetDate())
		;";

		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$temp = $result->result('array');

			if ( (is_array($temp)) && (isset($temp[0]['Records_Count'])) ) {
				if ( $temp[0]['Records_Count'] > 0 ) {
					$response = array(
						'Error_Msg' => 'Пациент является отказником. Выписка рецепта не возможна.',
						'success' => false
					);
				}
				else {
					$response = array(
						'success' => true
					);
				}
			}
			else {
				$response = array(
					'Error_Msg' => 'Ошибка при поиске человека в списке отказников',
					'success' => false
				);
			}
		}
		else {
			$response = array(
				'Error_Msg' => 'Ошибка при выполнении запроса к БД (поиск человека в списке отказников)',
				'success' => false
			);
		}

		return $response;
	}

    /**
     * Проверка остатков медикоментов
     */
	function checkReceptDrugOstat($data) {
		$check_recept_drug_ostat = -1;
		$queryParams = array();

		$query = "
			SELECT
				COALESCE(sum(DrugOstat_Kolvo), 0) as \"kolvo\"

			FROM
				v_DrugOstat DrugOstat

			WHERE (1 = 1)
				and to_char (COALESCE(DrugOstat_insDT, DrugOstat_updDT), 'YYYYMMDD') = (

					select max(to_char (COALESCE(DrugOstat_insDT, DrugOstat_updDT), 'YYYYMMDD'))

					from DrugOstat d 

					where COALESCE(DrugOstat_insDT, DrugOstat_updDT) < to_char (dbo.tzGetDate(), 'YYYYMMDD')::timestamp

						and DrugOstat.Drug_id = d.Drug_id
				)
				and DrugOstat.Drug_id = :Drug_id
				and DrugOstat.Lpu_id = :Lpu_id
		;";
		
		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( (is_array($response)) && (isset($response[0]['kolvo'])) ) {
				$check_recept_drug_ostat = $response[0]['kolvo'];
			}
		}

		return $check_recept_drug_ostat;
	}

	/**
	 * Проверка на повторную выписку рецепта ЛКО Кардио
	 */
	function checkReceptKardioReissue($data) {
		$result = array();
		$is_question = false;

		try {
			//определяем действующее вещество по переденному медикаменту
			$query = "
				select
					am.ACTMATTERS_ID as \"Actmatters_id\",
					am.RUSNAME as \"Actmatters_Name\"
				from
					rls.v_Drug d
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					inner join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
				where
					d.Drug_id = :Drug_id or
					d.DrugComplexMnn_id = :DrugComplexMnn_id
				limit 1
			";
			$actmatters_data = $this->getFirstRowFromQuery($query, array(
				'Drug_id' => $data['Drug_id'],
				'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
			));

			//проверка имеет смысл только если у медикамента в рецепте есть действующее вещество
			if (!is_array($actmatters_data) || empty($actmatters_data['Actmatters_id'])) {
				throw new Exception("");
			}

			//поиск ранее выписанных рецептов
			$query = "
				select
					count(er.EvnRecept_id) as cnt
				from v_EvnRecept er
					left join rls.v_Drug d on d.Drug_id = er.Drug_rlsid
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(d.DrugComplexMnn_id, er.DrugComplexMnn_id)
					inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id 
				where
					er.Person_id = :Person_id
					and er.EvnRecept_setDate > dateadd('day', -60, dbo.tzGetDate())
					and dcmn.ACTMATTERS_id = :Actmatters_id
			";
			$recept_cnt = $this->getFirstResultFromQuery($query, array(
				'Person_id' => $data['Person_id'],
				'Actmatters_id' => $actmatters_data['Actmatters_id'],
			));

			if ($recept_cnt > 0) {
				$is_question = true;
				throw new Exception("Внимание! В рецептах программы ЛКО Кардио выписывается количество ЛС рассчитанное на 90 дней терапии и предыдущий рецепт на ЛС {$actmatters_data['Actmatters_Name']} выписан менее 60 дней назад. Проверьте, не дублируется ли рецепт. Выписать рецепт?");
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result[$is_question ? 'Question_Msg' : 'Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = (empty($result['Error_Msg']) && empty($result['Question_Msg']));
		$result['success'] = true;

		return $result;
	}

	/**
	 * Проверка на выписку ЛП Тикагрелор в стационаре и поликлинике (только для рецептов по рограмме "ЛЛО Кардио")
	 */
	function checkReceptKardioTicagrelor($data) {
		$result = array();
		$is_question = false;

		try {
			//определяем действующее вещество по переденному медикаменту
			$query = "
				select
					am.ACTMATTERS_ID as \"Actmatters_id\",
					am.RUSNAME as \"Actmatters_Name\"
				from rls.v_Drug d
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					inner join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
				where
					d.Drug_id = :Drug_id or
					d.DrugComplexMnn_id = :DrugComplexMnn_id
				limit 1
			";
			$actmatters_data = $this->getFirstRowFromQuery($query, array(
				'Drug_id' => $data['Drug_id'],
				'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
			));

			//проверка осуществляется только для Тикагрелора
			if (!is_array($actmatters_data) || $actmatters_data['Actmatters_Name'] != 'Тикагрелор') {
				throw new Exception("");
			}

			$query = "
				with mv1 as (
					select VolumeType_id from v_VolumeType where VolumeType_Code = 'RVC kardio' limit 1
				)
				select
					count(av_value.AttributeValue_id) as \"val_cnt\"
				from
					dbo.AttributeValue av_lpu
					inner join dbo.AttributeValue av_value on
						av_value.AttributeValue_id = av_lpu.AttributeValue_rid and
						av_value.Attribute_id = (
							select a.Attribute_id
							from v_AttributeVision av
								left join v_Attribute a on a.Attribute_id = av.Attribute_id
							where a.Attribute_SysNick = 'Value'
								and av.AttributeVision_TablePKey = (select VolumeType_id from mv1)
							order by a.Attribute_id
							limit 1
						) and
						av_value.AttributeValue_TablePKey = (select VolumeType_id from mv1)
				where
					av_lpu.Attribute_id = (
						select a.Attribute_id
						from v_AttributeVision av
							left join v_Attribute a on a.Attribute_id = av.Attribute_id
						where a.Attribute_SysNick = 'Lpu'
							and av.AttributeVision_TablePKey = (select VolumeType_id from mv1)
						order by a.Attribute_id
						limit 1
					) and
					av_lpu.AttributeValue_ValueIdent = :Lpu_id and
					(
						av_value.AttributeValue_endDate is null or
						av_value.AttributeValue_endDate >= :EvnRecept_setDate
					);
			";
			$val_data = $this->getFirstRowFromQuery($query, array(
				'Lpu_id' => $data['Lpu_id'],
				'EvnRecept_setDate' => !empty($data['EvnRecept_setDate']) ? $data['EvnRecept_setDate'] : null
			));

			//проверка наличия объема для МО
			if (!is_array($val_data) || empty($val_data['val_cnt'])) {
				$error_msg = "Выписка ЛП Тикагрелор невозможна, так как этот ЛП выписывается только по решению врачебной комиссии ";
				$error_msg .= "в медицинских организациях, являющихся Региональными сосудистыми центрами (РСЦ). Направьте пациента в РСЦ.";
				throw new Exception($error_msg);
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result[$is_question ? 'Question_Msg' : 'Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = (empty($result['Error_Msg']) && empty($result['Question_Msg']));
		$result['success'] = true;

		return $result;
	}

	/**
	 * Проверка даты ваписки рецепта
	 */
	function checkReceptKardioSetDate($data) {
		$result = array();
		$is_question = false;

		try {
			//определяем откуда осуществляется выписка из стационара или из поликлиники по родительскому событию
			$evnclass_nick = null;
			if (!empty($data['EvnRecept_pid'])) {
				$query = "
					select
						ec.EvnClass_SysNick as \"EvnClass_SysNick\"
					from
						v_Evn e
						left join v_EvnClass ec on ec.EvnClass_id = e.EvnClass_id
					where
						e.Evn_id = :EvnRecept_pid;
				";
				$evnclass_nick = $this->getFirstResultFromQuery($query, array(
					'EvnRecept_pid' => $data['EvnRecept_pid']
				));
			}

			switch ($evnclass_nick) {
				case 'EvnSection': //движение - выписка в стационаре
					//поиск данных текущей КВС
					$query = "
						with mv1 as (
							select
								eps.EvnPS_id
							from v_EvnSection es
								left join v_EvnPS eps on eps.EvnPS_id = es.EvnSection_rid or eps.EvnPS_id = es.EvnSection_pid
							where es.EvnSection_id = :EvnRecept_pid
							order by
								eps.EvnPS_id desc
							limit 1
						), mv2 as (
							select
								es.EvnSection_disDate
							from
								v_EvnSection es
							where
								es.EvnSection_pid = (select EvnPS_id from mv1) or
								es.EvnSection_rid = (select EvnPS_id from mv1)
							order by
								es.EvnSection_id desc
							limit 1
						)
						
						select
							(select EvnPS_id from mv1) as \"EvnPS_id\",
							to_char((select EvnSection_disDate from mv2), 'yyyy.mm.dd') as \"EvnSection_disDate\"
					";
					$evnps_data = $this->getFirstRowFromQuery($query, array(
						'EvnRecept_pid' => $data['EvnRecept_pid']
					));

					if (!is_array($evnps_data) || empty($evnps_data['EvnPS_id'])) {
						throw new Exception("Не удалось получить информацию о текущей КВС пациента.");
					}

					$recept_set_date = !empty($data['EvnRecept_setDate']) ? DateTime::createFromFormat('Y-m-d', $data['EvnRecept_setDate']) : null;
					$section_dis_date = !empty($evnps_data['EvnSection_disDate']) ? DateTime::createFromFormat('Y.m.d', $evnps_data['EvnSection_disDate']) : null;

					if ($section_dis_date != $recept_set_date) {
						throw new Exception("Сохранение невозможно. Дата выписки рецепта должна быть равна дате выписки из стационара. Измените дату рецепта и повторите сохранение рецепта.");
					}
					break;
				case 'EvnVizitPL': //посещение - выписка в поликлинике
					//определение даты включения пациента в программу ЛЛО Кардио
					$query = "
						select
							to_char(evpl.EvnVizitPL_setDate, 'yyyy.mm.dd') as \"EvnVizitPL_setDate\"
						from
							v_EvnVizitPL evpl
						where
							evpl.EvnVizitPL_id = :EvnRecept_pid;
					";
					$visit_set_date = $this->getFirstResultFromQuery($query, array(
						'EvnRecept_pid' => $data['EvnRecept_pid']
					));
					$visit_set_date = !empty($visit_set_date) ? DateTime::createFromFormat('Y.m.d', $visit_set_date) : null;
					$recept_set_date = !empty($data['EvnRecept_setDate']) ? DateTime::createFromFormat('Y-m-d', $data['EvnRecept_setDate']) : null;

					if ($visit_set_date != $recept_set_date) {
						throw new Exception("Сохранение невозможно. Дата выписки рецепта должна быть равна дате посещения. Измените дату рецепта и повторите сохранение рецепта.");
					}
					break;
				default: //игнорируем рецепты выписанные не в рамках движения или посещения
					throw new Exception("");
					break;
			}
		} catch (Exception $e) {
			if (!empty($e->getMessage())) {
				$result[$is_question ? 'Question_Msg' : 'Error_Msg'] = $e->getMessage();
			}
		}

		$result['check_result'] = (empty($result['Error_Msg']) && empty($result['Question_Msg']));
		$result['success'] = true;

		return $result;
	}

	/**
     * Сверяет дату выписки рецепта с датой рождения пациента
     */
	function checkReceptPersonBirthday($data) {
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkReceptPersonBirthday\n\r");

		$check_recept_person_birthday = -1;
		$queryParams = array();

		$query = "
			select
				count(*) as \"Person_Count\"
			from v_PersonState 

			where (1 = 1)
				and Person_id = :Person_id
				and Person_BirthDay < :EvnRecept_setDate
		";

		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['EvnRecept_setDate'] = $data['EvnRecept_setDate'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( (is_array($response)) && (isset($response[0]['Person_Count'])) ) {
				$check_recept_person_birthday = $response[0]['Person_Count'];
			}
		}

		return $check_recept_person_birthday;
	}

    /**
     * Проверяет наличие полиса ОМС у пациента
     */
    function checkReceptPersonPolis($data) {
        $check_result = false;
        $query = "
			select
			    PS.Polis_id as \"Polis_id\", SC.SocStatus_SysNick as \"SocStatus_SysNick\"
            from v_PersonState PS 

                left join SocStatus SC  on SC.SocStatus_id = PS.SocStatus_id

            where Person_id = :Person_id
		";

        $queryParams['Person_id'] = $data['Person_id'];

        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            $response = $result->result('array');
            if ( (is_array($response)) && count($response)>0 ) {
                $socStatusFilter = in_array($response[0]['SocStatus_SysNick'],array(
                    'mvd','gtk','dnp','ra','fapsi','fsb','fps','mcs','fire','warother'
                ));
                if ($socStatusFilter == true){
                    $check_result = true;
                }else if(!empty($response[0]['Polis_id'])){
                    $check_result = true;
                }
            }
        }

        return $check_result;
    }

	/**
	 * Проверяет наличие карты у пациента
	 */
	function checkReceptPersonCard($data)
	{
		$query = "
			select 
			    PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\"
            from v_PersonAmbulatCard PAC 

            where PAC.Person_id = :Person_id
			limit 1
		";

		$resp = $this->queryResult($query, array(
			'Person_id' => $data['Person_id']
		));

		if (!empty($resp[0]['PersonAmbulatCard_id'])) {
			return true;
		}

		return false;
	}

    /**
     * Проверка действующих льгот у пациента
     */
	function checkReceptPrivilegeDate($data) {
		$check_recept_privilege_date = -1;
		$queryParams = array();

		$query = "
			select
				count(*) as \"Privilege_Count\"
			from v_PersonPrivilege 

			where (1 = 1)
				and Person_id = :Person_id
				and PrivilegeType_id = :PrivilegeType_id
				and PrivilegeType_Code between 1 and 500
				and PersonPrivilege_begDate <= cast(:EvnRecept_setDate as date)
				and (PersonPrivilege_endDate is null or PersonPrivilege_endDate > ?)
		";

		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
		$queryParams['EvnRecept_setDate'] = $data['EvnRecept_setDate'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( (is_array($response)) && (isset($response[0]['Privilege_Count'])) ) {
				$check_recept_privilege_date = $response[0]['Privilege_Count'];
			}
		}

		return $check_recept_privilege_date;
	}

    /**
     * Проверка рецепта по серийному номеру
     */
	function checkReceptSerNum($data) {
		// $this->writeToLog("[" . date('Y-m-d H:i:s') . "] checkReceptSerNum\n\r");

		$check_recept_ser_num = -1;
		$queryParams = array();
		$query = "
			select EvnRecept_id as \"EvnRecept_id\"
			from v_EvnRecept 

			where (1 = 1)
				and Lpu_id = :Lpu_id
				and EvnRecept_Ser = :EvnRecept_Ser
				and EvnRecept_Num = cast(:EvnRecept_Num as varchar)
		";

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
		$queryParams['EvnRecept_Num'] = $data['EvnRecept_Num'];

		if ( $data['EvnRecept_id'] > 0 ) {
			$query .= " and EvnRecept_id <> :EvnRecept_id";
			$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		}
		$query .= "
			limit 1";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnRecept_id']) ) {
				$check_recept_ser_num = 1;
			}
			else {
				$check_recept_ser_num = 0;
			}
		}

		return $check_recept_ser_num;
	}

	/**
	 * Проверка наличия у пациента необеспеченных рецептов с таким же действующим веществом
	 */
	function checkReceptPersonActmatters($data) {
		$result = array();

		try {
			if (empty($data['Person_id'])) {
				throw new Exception("Не удалось определить пациента");
			}
			if (empty($data['EvnRecept_id'])) {
				$data['EvnRecept_id'] = null;
			}

			//определение действующего вещества
			if (empty($data['Actmatters_id']) && (!empty($data['Drug_rlsid']) || !empty($data['DrugComplexMnn_id']))) {
				if (!empty($data['Drug_rlsid'])) {
					$query = "
						select
							coalesce(dcmn.ACTMATTERS_id, dcm.ActMatters_id) as \"Actmatters_id\"
						from
							rls.v_Drug d
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
						where
							d.Drug_id = :Drug_id;
					";
				} else {
					$query = "
						select
							coalesce(dcmn.ACTMATTERS_id, dcm.ActMatters_id) as \"Actmatters_id\"
						from
							rls.v_DrugComplexMnn dcm
							left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
						where
							dcm.DrugComplexMnn_id = :DrugComplexMnn_id;
					";
				}

				$am_data = $this->getFirstRowFromQuery($query, $data);
				if (!empty($am_data['Actmatters_id'])) {
					$data['Actmatters_id'] = $am_data['Actmatters_id'];
				}
			}

			if (empty($data['Actmatters_id'])) {
				throw new Exception("Не удалось определить действующее вещество");
			}

			$query = "
				select
					er.EvnRecept_id as \"EvnRecept_id\"
				from
					v_EvnRecept er
					left join rls.v_Drug d on d.Drug_id = er.Drug_rlsid
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(d.DrugComplexMnn_id, er.DrugComplexMnn_id)
					left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				where
					er.Person_id = :Person_id and
					er.ReceptRemoveCauseType_id is null and
					er.EvnRecept_obrDT is null and
					coalesce(dcmn.ACTMATTERS_id, dcm.ActMatters_id) = :Actmatters_id and
					(:EvnRecept_id is null or er.EvnRecept_id <> :EvnRecept_id)
				order by
					er.EvnRecept_id
				limit 1
			";
			$check_data = $this->getFirstRowFromQuery($query, $data);
			if (!empty($check_data['EvnRecept_id'])) {
				$result['EvnRecept_id'] = $check_data['EvnRecept_id'];
			}
		} catch (Exception $e) {
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}

	/**
	 * Проверка состояния текущих запроов по льготе
	 */
	function checkReceptCurrentPersonPrivilgeReqState($data) {
		$result = array(
			'reject_cnt' => 0, //количество отклоненных запросов (берется всегда самый последний запрос по льготе, так что тут будет либо 0 либо 1)
			'new_cnt' => 0 //количество новых или еще не рассмотренных запросов созданных до даты выписки рецепта
		);

		try {
			if (empty($data['Person_id']) || empty($data['PrivilegeType_id']) || empty($data['EvnRecept_setDate'])) {
				throw new Exception("Не указаны обязательные параметры");
			}

			//проверка последнего добавленного запроса на предмет отказа
			$query = "
				select
					(case
						when ppra.PersonPrivilegeReqStatus_id = 3 and ppra.PersonPrivilegeReqAns_IsInReg = 1 then 1 -- ответ получен, но пациент не включен в регистр
						else 0
					end) as \"reject_cnt\"
				from
					v_PersonPrivilegeReq ppr
					left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
				where
					ppr.Person_id = :Person_id and
					ppr.PrivilegeType_id = :PrivilegeType_id
				order by
					ppr.PersonPrivilegeReq_id desc
				limit 1;
			";
			$check_data = $this->getFirstRowFromQuery($query, $data);
			if (!empty($check_data['reject_cnt'])) {
				$result['reject_cnt'] = $check_data['reject_cnt'];
			}

			//подсчет количества нерассмотренных запросов по льготе
			$query = "
				select
					count(ppr.PersonPrivilegeReq_id) as \"new_cnt\"
				from
					v_PersonPrivilegeReq ppr
					left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
				where
					ppr.Person_id = :Person_id and
					ppr.PrivilegeType_id = :PrivilegeType_id and
					ppr.PersonPrivilegeReq_insDT <= :EvnRecept_setDate and
					ppra.PersonPrivilegeReqStatus_id in (1, 2) -- сатус Новый или На рассмотрении
			";
			$check_data = $this->getFirstRowFromQuery($query, $data);
			if (!empty($check_data['new_cnt'])) {
				$result['new_cnt'] = $check_data['new_cnt'];
			}
		} catch (Exception $e) {
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}

	/**
	* Проверка серии-номера общего рецепта
	*/
	function checkReceptGeneralSerNum($data)
	{
		$check_recept_ser_num = -1;
		$queryParams = array();
		$query = "
			select EvnReceptGeneral_id as \"EvnReceptGeneral_id\"
			from v_EvnReceptGeneral 

			where (1 = 1)
				and Lpu_id = :Lpu_id
				and EvnReceptGeneral_Ser = :EvnReceptGeneral_Ser
				and EvnReceptGeneral_Num = cast(:EvnReceptGeneral_Num as varchar)
		";

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['EvnReceptGeneral_Ser'] = $data['EvnReceptGeneral_Ser'];
		$queryParams['EvnReceptGeneral_Num'] = $data['EvnReceptGeneral_Num'];

		if ( $data['EvnReceptGeneral_id'] > 0 ) {
			$query .= " and EvnReceptGeneral_id <> :EvnReceptGeneral_id";
			$queryParams['EvnReceptGeneral_id'] = $data['EvnReceptGeneral_id'];
		}

		$query .= "
		limit 1";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnRecept_id']) ) {
				$check_recept_ser_num = 1;
			}
			else {
				$check_recept_ser_num = 0;
			}
		}

		return $check_recept_ser_num;
	}
	
	/**
	 * Отмена списания медикамента из разнарядки
	 */
	function deleteEvnReceptDrugOstReg($data) {
		$this->beginTransaction();
		$queryParams = array('EvnRecept_id' => $data['EvnRecept_id']);

		$query = "
			select 
				ER.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
				ERDOR.EvnReceptDrugOstReg_id as \"EvnReceptDrugOstReg_id\",
				ERDOR.DrugOstatRegistry_id as \"DrugOstatRegistry_id\"
			from
				v_EvnRecept ER 

				left join v_EvnReceptDrugOstReg ERDOR  on ERDOR.EvnRecept_id = ER.EvnRecept_id

			where ER.EvnRecept_id = :EvnRecept_id
            limit 1
		";
		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp) || count($resp) == 0) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при запросе информации о рецепте');
		}
		$EvnReceptInfo = $resp[0];

		if (!empty($EvnReceptInfo['EvnReceptDrugOstReg_id'])) {
			$query = "
				WITH cte AS (
                    select
                         Contragent_id,
                         Org_id,
                         DrugShipment_id,
                         Drug_id,
                         SubAccountType_id,
                         Okei_id,
                         DrugOstatRegistry_Kolvo,
                         DrugOstatRegistry_Sum,
                         DrugOstatRegistry_Cost,
                         Storage_id,
                         PrepSeries_id,
                         case when DrugOstatRegistry_Kolvo = 0 then 0 else (:EvnRecept_Kolvo/DrugOstatRegistry_Kolvo)*DrugOstatRegistry_Sum end as DrugOstatRegistry_Sum2,
                         :EvnRecept_Kolvo as DrugOstatRegistry_Kolvo2
                    from
                        v_DrugOstatRegistry 
    
                    where
                        DrugOstatRegistry_id = :DrugOstatRegistry_id
                    limit 1
                )
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from xp_DrugOstatRegistry_count(
					Contragent_id := (SELECT Contragent_id FROM cte),
					Org_id := (SELECT Org_id FROM cte),
					DrugShipment_id := (SELECT DrugShipment_id FROM cte),
					Drug_id := (SELECT Drug_id FROM cte),
					PrepSeries_id := (SELECT PrepSeries_id FROM cte),
					SubAccountType_id := (SELECT SubAccountType_id FROM cte), -- субсчёт доступно
					Okei_id := (SELECT Okei_id FROM cte),
					DrugOstatRegistry_Kolvo :=(SELECT DrugOstatRegistry_Kolvo2 FROM cte),
					DrugOstatRegistry_Sum := (SELECT DrugOstatRegistry_Sum2 FROM cte),
					DrugOstatRegistry_Cost := (SELECT DrugOstatRegistry_Cost FROM cte),
					Storage_id := Storage_id,
					pmUser_id := :pmUser_id)
			";
			$queryParams = array(
				'EvnRecept_Kolvo' => $EvnReceptInfo['EvnRecept_Kolvo'],
				'DrugOstatRegistry_id' => $EvnReceptInfo['DrugOstatRegistry_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$resp = $this->queryResult($query, $queryParams);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('', 'Ошибка при возврате ЛС, в строку разнарядки');
			}
			if (!empty($resp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}

			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_EvnReceptDrugOstReg_del(
					EvnReceptDrugOstReg_id := :EvnReceptDrugOstReg_id);
			";
			$queryParams = array('EvnReceptDrugOstReg_id' => $EvnReceptInfo['EvnReceptDrugOstReg_id']);
			$this->queryResult($query, $queryParams);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('', 'Ошибка при удалении связи рецепта и строки разнарядки');
			}
			if (!empty($resp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return array(array('success' => true, 'Error_Msg' => ''));
	}

    /**
     * Удаление рецепта
     */
	function deleteEvnRecept($data) {
		//запоминаем значение параметра на момент начала выполнения функции
		$isAllowTransactionSavedValue = $this->isAllowTransaction;

		$this->load->model("Options_model", "opmodel");
		$o = $this->opmodel->getOptionsGlobals($data);
		$g_options = $o['globals'];
		
		//получаем сведения о состояниях рецепта, которые влияют на возможность удаления
		$resp_er = $this->queryResult("
					select
						er.EvnRecept_id as \"EvnRecept_id\",
						er.EvnRecept_IsPrinted as \"EvnRecept_IsPrinted\",
						er.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
						rt.ReceptType_Code as \"ReceptType_Code\",
						er.Lpu_id as \"Lpu_id\"
					from
						v_EvnRecept er 

						left join v_ReceptType rt  on rt.ReceptType_id = er.ReceptType_id

					where
						EvnRecept_id = :EvnRecept_id
				", array(
			'EvnRecept_id' => $data['EvnRecept_id']
		));

		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$this->EMD_model->deleteEMDRegistryByEvn(array(
				'EMDRegistry_ObjectID' => $data['EvnRecept_id'],
				'EMDRegistry_ObjectName' => 'EvnRecept',
				'pmUser_id' => $data['pmUser_id'],
			));
		}

		//Проверим, можно ли вообще удалить этот рецепт (задача https://redmine.swan.perm.ru/issues/43889)
		//Для Уфы раньше не было, сейчас добавил и для нее - https://redmine.swan.perm.ru/issues/82882
		$may_delete = true;
		$query_check = "
			select COUNT(*) as \"ctn\"
			from ReceptOtov 

			where EvnRecept_id = :EvnRecept_id
		";
		//echo getDebugSQL($query_check, array('EvnRecept_id' => $data['EvnRecept_id']));die;
		$result_check = $this->db->query($query_check, array('EvnRecept_id' => $data['EvnRecept_id']));
		if(is_object($result_check)){
			$response_check = $result_check->result('array');
			if( (is_array($response_check)) && (isset($response_check[0]['ctn']))){
				if($response_check[0]['ctn'] > 0)
					$may_delete = false;
			}
		}
		if(!$may_delete)
			return array(array('Error_Msg' => 'Рецепт находится на обслуживании в аптеке. Удаление рецепта невозможно'));
		if ($data['DeleteType'] == 1) {
			// Для Московской области
			if ( $this->regionNick == 'msk' && !havingGroup('SuperAdmin') && $resp_er[0]['Lpu_id'] != $data['Lpu_id'] ) {
				return [[ 'Error_Msg' => 'Аннулирование рецепта недоступно' ]];
			}
			// если рецепт распечатан или подписан и на бланке, то удалить может только Администратор ЦОД / Администратор МО / Руководитель ЛЛО МО
			else if (
				$this->regionNick != 'msk' && !havingGroup('SuperAdmin') && !havingGroup('LpuAdmin') && !havingGroup('ChiefLLO')
			) {
				if (!empty($resp_er[0]['EvnRecept_id']) && $resp_er[0]['ReceptType_Code'] == 2 && ($resp_er[0]['EvnRecept_IsPrinted'] == 2 || $resp_er[0]['EvnRecept_IsSigned'] == 2)) {
					if ($resp_er[0]['EvnRecept_IsPrinted'] == 2) {
						return array(array('Error_Msg' => 'Рецепт распечатан. Удаление рецепта невозможно'));
					} else {
						return array(array('Error_Msg' => 'Рецепт подписан. Удаление рецепта невозможно'));
					}
				}
			}

			$this->beginTransaction();

			$this->isAllowTransaction = false;
			$resp = $this->deleteEvnReceptDrugOstReg($data);
			$this->isAllowTransaction = $isAllowTransactionSavedValue;

			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}

			if (
				/*!$is_kardio &&*/ !empty($g_options['select_drug_from_list']) && in_array($g_options['select_drug_from_list'], array('allocation', 'request'/*, 'request_and_allocation'*/))
			) { //корректировка данных о количестве выписанных медикаментов в разнарядке заявки
				$resp = $this->updateDrugRequestPersonOrder('delete', array(
					'EvnRecept_id' => $data['EvnRecept_id']
				));
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}

			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_EvnRecept_del(
					EvnRecept_id := :EvnRecept_id,
					ReceptRemoveCauseType_id := :ReceptRemoveCauseType_id,
					pmUser_id := :pmUser_id);
			";

			$response = $this->queryResult($query, array(
				'EvnRecept_id' => $data['EvnRecept_id'],
				'ReceptRemoveCauseType_id' => $data['ReceptRemoveCauseType_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_array($response) ) {
				$this->rollbackTransaction();
				return $this->createError('', 'Ошибка при выполнении запроса к базе данных (' . ($this->regionNick == 'msk' ? 'аннулирование' : 'удаление') . ' рецепта)');
			}
			if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}

			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->deleteApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnRecept',
				'ApprovalList_ObjectId' => $data['EvnRecept_id']
			));

			$this->load->model("Options_model", "opmodel");
			$options = $this->opmodel->getOptionsGlobals($data);
			if (getRegionNick() == 'msk' && $options['globals']['use_external_service_for_recept_num'] == 1) {
				//получим данные по серии и номеру
				$query = "
					select
						ReceptFreeNum_id as \"ReceptFreeNum_id\",
						ReceptFreeNum_Num as \"ReceptFreeNum_Num\",
						ReceptFreeNum_Ser as \"ReceptFreeNum_Ser\"
					from r50.ReceptFreeNum
					where 1=1
						and EvnRecept_id = :EvnRecept_id
				";
				$res = $this->getFirstRowFromQuery($query, $data);
				
				if (!empty($res['ReceptFreeNum_Num']) && !empty($res['ReceptFreeNum_Ser'])) {
					//удаление из списка номеров
					$res['pmUser_id'] = $data['pmUser_id'];
					$response = $this->queryResult("
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from r50.p_ReceptFreeNum_del (
							ReceptFreeNum_id := :ReceptFreeNum_id,
							pmUser_id := :pmUser_id
						)
					", $res);
					
					if ( !is_array($response) ) {
						$this->rollbackTransaction();
						return $this->createError('', 'Ошибка при выполнении запроса к базе данных (удаление ссылки на рецепт в таблице свободных номеров)');
					}
					if ( !empty($response[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $response;
					}
				}
				else {
					$this->rollbackTransaction();
					return $this->createError('','Ошибка при выполнении запроса к базе данных (удаление ссылки на рецепт в таблице свободных номеров)');
				}
			}

			$this->commitTransaction();
		}
		else
		{
			$query = "
				update EvnRecept
				set
					ReceptRemoveCauseType_id = :ReceptRemoveCauseType_id,
					ReceptDelayType_id = 4
				where Evn_id = :EvnRecept_id
			";
			$result = $this->db->query($query,array(
				'EvnRecept_id' => $data['EvnRecept_id'],
				'ReceptRemoveCauseType_id' => $data['ReceptRemoveCauseType_id']
			));
			$query = "
				update Evn
					set pmUser_updID = :pmUser_id
				where Evn_id = :EvnRecept_id
			";
			$result = $this->db->query($query,array(
				'EvnRecept_id' => $data['EvnRecept_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			$response = array(
				'Error_Code' => null,
				'Error_Msg' => null,
				'success'	=> true
			);
		}

		return $response;
	}


    /**
     * Удаление рецепта
     */
	function deleteEvnReceptGeneral($data) {
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_EvnReceptGeneral_del(
				EvnReceptGeneral_id := :EvnReceptGeneral_id,
				pmUser_id := :pmUser_id);


		";

		$response = $this->queryResult($query, array(
			'EvnReceptGeneral_id' => $data['EvnReceptGeneral_id'],
			'pmUser_id' => $data['pmUser_id']
		));
			
		return $response;
	}

	/**
     * Проверки перед созданием общего рецепта
     */
	function checkBeforeCreateEvnReceptGeneral($data) {

		if(empty($data['EvnCourseTreatDrug_id'])){
			return false;
		}

		// Проверка является ли медикамент медицинским изделием
		$query = "
			select 1
			from 
				v_EvnCourseTreatDrug ectd 

				inner join rls.Drug RD  on RD.Drug_id = ectd.Drug_id

				inner join rls.Prep P  on P.Prep_id = RD.DrugPrep_id

				inner join rls.CLSNTFR NTFR  on NTFR.CLSNTFR_ID = P.NTFRID

			where ectd.EvnCourseTreatDrug_id = 
            :EvnCourseTreatDrug_id
			-- Условия проверки взяты из существующего метода loadDrugList
			and NTFR.CLSNTFR_ID not in (1,176,137,138,139,140,141,142,144)
			and NTFR.PARENTID not in (1,176)
			limit 1
		";

		$response = $this->queryResult($query, array('EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id']));

		if(is_array($response) && count($response) == 1){
			return array(array('mi'=>1));
		}

		// Проверка - входит ли медикамент в группу наркотических
		$query = "
			select 
				am.NARCOGROUPID as \"NARCOGROUPID\",
				am.STRONGGROUPID as \"STRONGGROUPID\"
			from 
				v_EvnCourseTreatDrug ectd 

				left join rls.Drug RD  on RD.Drug_id = ectd.Drug_id

				inner join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = COALESCE(RD.DrugComplexMnn_id,ectd.DrugComplexMnn_id)


				inner join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				inner join rls.v_ACTMATTERS am  on am.ACTMATTERS_ID = dcmn.Actmatters_id

				left join rls.NARCOGROUPS ng  on ng.NARCOGROUPS_ID = am.NARCOGROUPID

			where ectd.EvnCourseTreatDrug_id = 
            :EvnCourseTreatDrug_id
			and (am.NARCOGROUPID in (2,3,4,5) or am.STRONGGROUPID=1)
			limit 1
		";
		
		$response = $this->queryResult($query, array('EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id']));
		//var_dump($response);die;
		if(is_array($response) && !empty($response[0]['NARCOGROUPID']) ){
			//#152001 форма рецепта – 107/у-НП, если медикамент входит в группу «Список II. Список наркотических средств и психотропных…» Иначе форма рецепта – 148-1/у-88
			return array(array('narco'=>$response[0]['NARCOGROUPID']));
		}
		
		if(is_array($response) && !empty($response[0]['STRONGGROUPID']) ){
			//#152001 сильнодействующее ЛС - форма рецепта – 148-1/у-88
			return array(array('stronggroup' => $response[0]['STRONGGROUPID']));
		}
		
		if (getRegionNick() != 'kz') {
			// Проверка, входит ли медикамент в группу ATX
			$query = "
				select 1
				from
					v_EvnCourseTreatDrug ectd
					inner join rls.Drug RD on (RD.Drug_id = ectd.Drug_id or RD.DrugComplexMnn_id = ectd.DrugComplexMnn_id)
					inner join rls.Prep P on P.Prep_id = RD.DrugPrep_id
					inner join rls.PREP_ATC PA on PA.PREPID = P.Prep_id
					inner join rls.CLSATC C on C.CLSATC_ID = PA.UNIQID
				where ectd.EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id
				and (
					C.CODE like 'N05A%' or
					C.CODE like 'N05B%' or
					C.CODE like 'N05C%' or
					C.CODE like 'N06A%'
				)
				limit 1
			";
			
			$response = $this->queryResult($query, array('EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id']));
			
			if (is_array($response) && count($response) == 1) {
				return array(array('atxgroup' => 1));
			}
		}

		// Загрузка списка рецептов (Переделал cnt в соответствие с задачей https://redmine.swan.perm.ru/issues/108295)
		$list_where = array();

		if (getRegionNick() != 'kz') {
			//формы рецептов: 107 - 107-1/у "Рецептурный бланк"; 103 - 130/у; тип рецепта: 3 - Электронный документ
			$list_where[] = "(
				(rf.ReceptForm_Code = '107' and rt.ReceptType_Code <> 3) or
				rf.ReceptForm_Code = '103'
			)";
		} else {
			$list_where[] = "rf.ReceptForm_Code = '103'";
		}

		$list_where_clause = "";
		if (count($list_where) > 0) {
			$list_where_clause = " and ".implode(" and ", $list_where);
		}

		$query = "
			select distinct
				rtrim(COALESCE(erg.EvnReceptGeneral_Ser,'') ||' '|| COALESCE(erg.EvnReceptGeneral_Num,'')) as \"EvnReceptGeneral_SerNum\",
				erg.EvnReceptGeneral_id as \"EvnReceptGeneral_id\"
			from 
				v_EvnCourseTreatDrug ectd 
				inner join v_EvnCourseTreat ect  on ect.EvnCourseTreat_id = ectd.EvnCourseTreat_id
				inner join v_EvnVizitPL evp  on evp.EvnVizitPL_id = ect.EvnCourseTreat_pid
				inner join v_EvnReceptGeneral erg  on erg.EvnReceptGeneral_pid = ect.EvnCourseTreat_pid
				inner join v_ReceptForm rf  on rf.ReceptForm_id = erg.ReceptForm_id			  
				inner join v_ReceptType rt  on rt.ReceptType_id = erg.ReceptType_id
				LEFT JOIN LATERAL (
					select COUNT(EGDL.EvnReceptGeneralDrugLink_id) as cnt
					from v_EvnReceptGeneralDrugLink EGDL 
					where EGDL.EvnReceptGeneral_id = ERG.EvnReceptGeneral_id
				) cnt ON true
			where ectd.EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id and COALESCE(cnt.cnt,0) < 3
				--#152001 рецепты по наркотику (107/у-НП) могут быть отредактированы до момента обеспечения рецепта
				and not exists(
					select ergc.EvnReceptGeneral_id
					from v_EvnReceptGeneral ergc 
					left join v_ReceptDelayType rdt  on rdt.ReceptDelayType_id = ergc.ReceptDelayType_id
					WHERE ergc.EvnReceptGeneral_id = erg.EvnReceptGeneral_id and ergc.ReceptForm_id = 8 and rdt.ReceptDelayType_Code = 0
				)
				--#188358 рецепты, содержащие медикаменты группы ATX не должны быть доступны
				and not exists(
					select distinct ergx.EvnReceptGeneral_id as \"EvnReceptGeneral_id\"
					from
						v_EvnReceptGeneralDrugLink ERGDL
						inner join v_EvnReceptGeneral ergx on ergx.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id
						inner join v_EvnCourseTreatDrug ectdx on ectdx.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id
						inner join rls.Drug RD on (RD.Drug_id = ectdx.Drug_id or RD.DrugComplexMnn_id = ectdx.DrugComplexMnn_id)
						inner join rls.Prep P on P.Prep_id = RD.DrugPrep_id
						inner join rls.PREP_ATC PA on PA.PREPID = P.Prep_id
						inner join rls.CLSATC C on C.CLSATC_ID = PA.UNIQID
					where ergx.EvnReceptGeneral_id = erg.EvnReceptGeneral_id and ergx.ReceptForm_id = 3 and (
							C.CODE like 'N05A%' or
							C.CODE like 'N05B%' or
							C.CODE like 'N05C%' or
							C.CODE like 'N06A%'
						)
				)
				{$list_where_clause}
		";

		$response = $this->queryResult($query, array('EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id']));

		if(is_array($response) && count($response) > 0){
			return $response;
		}

		return array();
	}
	/**
	 * Проверка на вид формы платного рецепта в ЭМК ExtJs6
	 */
	function checkFormEvnReceptGeneral($data) {

		if(empty($data['Drug_id']) && empty($data['DrugComplexMnn_id'])){
			return false;
		}

		// Проверка является ли медикамент медицинским изделием
		$query = "
			select 1
			from 
				rls.Drug RD 

				inner join rls.Prep P  on P.Prep_id = RD.DrugPrep_id

				inner join rls.CLSNTFR NTFR  on NTFR.CLSNTFR_ID = P.NTFRID

			where RD.Drug_id = 
            :Drug_id
			-- Условия проверки взяты из существующего метода loadDrugList
			and NTFR.CLSNTFR_ID not in (1,176,137,138,139,140,141,142,144)
			and NTFR.PARENTID not in (1,176)
			limit 1
		";

		$response = $this->queryResult($query, array('Drug_id' => $data['Drug_id']));

		if(is_array($response) && count($response) == 1){
			return array(array('mi'=>1));
		}

		// Проверка - входит ли медикамент в группу наркотических
		if(!empty($data['Drug_id'])){
			$from = 'rls.Drug RD 

					inner join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = COALESCE(RD.DrugComplexMnn_id,:DrugComplexMnn_id)';


			$where = 'RD.Drug_id = :Drug_id';
		}
		else{
			$from = 'rls.v_DrugComplexMnn dcm ';

			$where = 'dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
		}
		$query = "
			select 1
			from 
			  	{$from}
				inner join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				inner join rls.v_ACTMATTERS am  on am.ACTMATTERS_ID = dcmn.Actmatters_id

				left join rls.NARCOGROUPS ng  on ng.NARCOGROUPS_ID = am.NARCOGROUPID

			where {$where}
			and (am.NARCOGROUPID in (2,3,4,5) or am.STRONGGROUPID=1)
			limit 1
		";

		$response = $this->queryResult($query, array(
			'Drug_id' => $data['Drug_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
		));
		//var_dump($response);die;
		if(is_array($response) && count($response) == 1){
			return array(array('narco'=>1));
		}

		return array();
	}

	/**
	 * Снятие отметки к удалению
	 */
	function UndoDeleteEvnRecept($data){
		$query = "
			update EvnRecept
			set
				ReceptRemoveCauseType_id = null,
				ReceptDelayType_id = null
			where Evn_id = :EvnRecept_id
		";
		$result = $this->db->query($query,array(
			'EvnRecept_id' => $data['EvnRecept_id']
		));
		$query = "
				update Evn
					set pmUser_updID = :pmUser_id
				where Evn_id = :EvnRecept_id
		";
		$result = $this->db->query($query,array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		$response = array(
			'Error_Code' => null,
			'Error_Msg' => null,
			'success'	=> true
		);
		return $response;
	}

    /**
     * Возвращает список медикоментов
     */
	function loadDrugList($data) {
		$filter = "(1 = 1)";
		$drug_table = "v_Drug";
		$query = "";
		$queryParams = array();

        if(isset($data['DopRequest']) && $data['DopRequest'] == 2){
            $query = "
                select distinct
                    D.Drug_id as \"Drug_id\",
                    D.Drug_Name as \"Drug_Name\",
                    COALESCE(D.Drug_CodeG,'') as \"Drug_CodeG\"

                from v_Drug D 

                left join v_DrugOstat_all D2  on D2.Drug_id = D.Drug_id and D2.Lpu_id = ".$data['Lpu_id']."

                where D.DrugMnn_id = :DrugMnn_id
                and COALESCE(D2.DrugOstat_Kolvo,0)=0

                order by D.Drug_Name
            ";
            $queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
            $result = $this->db->query($query, $queryParams);

            if ( is_object($result) ) {
                return $result->result('array');
            }
            else {
                return false;
            }
        }

		$queryParams['Lpu_id'] = $data['Lpu_id'];
        $mi_1_join = "";
        $mi_1_where = "";
        if(isset($data['is_mi_1']) && ($data['is_mi_1'] == 'true') && !isset($data['Drug_id'])){
            $mi_1_join = "  inner join rls.DrugNomen DN  on DN.DrugNomen_Code = cast(Drug.Drug_CodeG as varchar(20))

							left join rls.Drug RD  on RD.Drug_id = DN.Drug_id

							left join rls.Prep P  on P.Prep_id = RD.DrugPrep_id

							left join rls.CLSNTFR NTFR  on NTFR.CLSNTFR_ID = P.NTFRID

			";
            $mi_1_where = " and NTFR.CLSNTFR_ID <> 1 and NTFR.PARENTID <> 1 and NTFR.CLSNTFR_ID <> 176 and NTFR.PARENTID <> 176 and NTFR.CLSNTFR_ID <> 137 and NTFR.CLSNTFR_ID <> 138 and NTFR.CLSNTFR_ID <> 139 and NTFR.CLSNTFR_ID <> 140 and NTFR.CLSNTFR_ID <> 141 and NTFR.CLSNTFR_ID <> 142 and NTFR.CLSNTFR_ID <> 144";
        }
		switch ( $data['mode'] ) {
            case 'only_search_form_filters':
                $filter = "(1=1)";

                if ( isset($data['query']) ) {
                    // Поиск через доп. форму
                    $filter .= " and Drug.Drug_Name iLIKE :Drug_Name";

                    $queryParams['Drug_Name'] = "%" . $data['query'] . "%";
                }
                if (isset($data['Drug_CodeG']) && strlen($data['Drug_CodeG']) > 0) {
                    $filter .= " and Drug.Drug_CodeG iLIKE :Drug_CodeG";

                    $queryParams['Drug_CodeG'] = $data['Drug_CodeG'] . "%";
                }

                $queryParams['Date'] = $data['Date'];
                $queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
                $queryParams['ReceptFinance_id'] = $this->getFirstResultFromQuery(
                    "Select ReceptFinance_id  from ReceptFinance RF  where RF.ReceptFinance_Code = :ReceptFinance_Code limit 1",

                    array('ReceptFinance_Code' => $data['ReceptFinance_Code'])
                );

                $query = "
                    SELECT 
                        Drug.Drug_id as \"Drug_id\",
                        null as \"DrugRequestRow_id\",
                        Drug.DrugMnn_id as \"DrugMnn_id\",
                        null as \"DrugFormGroup_id\",
                        Drug.Drug_IsKek as \"Drug_IsKEK\",
                        Drug.Drug_Name as \"Drug_Name\",
                        null as \"Drug_DoseCount\",
                        null as \"Drug_Dose\",
                        null as \"Drug_Fas\",
                        cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
                        COALESCE(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
                        0 as \"DrugOstat_Flag\"
                    FROM v_Drug Drug 

                        LEFT JOIN LATERAL (

                            select DP.DrugState_Price, DP.ReceptFinance_id
                            from v_DrugPrice DP 

                            inner join ReceptFinance RF  on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = 
                            :ReceptFinance_Code

                            where DP.Drug_id = Drug.Drug_id and DP.DrugProto_begDate <= 
                            :Date
                            order by DP.DrugProto_id desc
                            limit 1
                        ) DrugPrice ON true
                        left join YesNo Drug_IsKEK  on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek

                        left join ReceptFinance  on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id

                    WHERE
                        {$filter}
                    ORDER BY
                        Drug_Name
                    LIMIT 250
                ";
                break;
			case 'all':
				if ( $data['Drug_id'] > 0 ) {
					// Загрузка на редактирование

					$query = "
						SELECT
							Drug.Drug_id as \"Drug_id\",
							null as \"DrugRequestRow_id\",
							Drug.DrugMnn_id as \"DrugMnn_id\",
							null as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as \"Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							null as \"Drug_DoseCount\",
							null as \"Drug_Dose\",
							null as \"Drug_Fas\",
							cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							COALESCE(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",

							0 as \"DrugOstat_Flag\"
						FROM v_Drug Drug 

							LEFT JOIN LATERAL (

								select DP.DrugState_Price, DP.ReceptFinance_id
								from v_DrugPrice DP 

								inner join ReceptFinance RF  on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = 
                                :ReceptFinance_Code

								where DP.Drug_id = Drug.Drug_id and DP.DrugProto_begDate <= 
                                :Date
								order by DP.DrugProto_id desc
                                limit 1
							) DrugPrice ON true
							left join YesNo Drug_IsKEK  on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek

							left join ReceptFinance  on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id

						WHERE (1 = 1)
							and Drug.Drug_id = :Drug_id
						ORDER BY Drug_Name
						LIMIT 1
					";

					$queryParams['Drug_id'] = $data['Drug_id'];
				}
				else {
					$farmacy_filter = "";
					$ostat_filter = "";

					//$filter .= " and (Drug.Drug_endDate is null or Drug.Drug_endDate > cast(:Date as datetime))";
                    //and Drug.Drug_begDate < '2014-10-21' and (Drug.Drug_endDate is null or Drug.Drug_endDate > '2014-10-21')
                    $filter .= "and (Drug.Drug_begDate is null or cast(Drug.Drug_begDate as timestamp) < cast(:Date as timestamp)) and (Drug.Drug_endDate is null or cast(Drug.Drug_endDate as timestamp) > cast(:Date as timestamp))";

					if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
						$drug_table = "v_Drug7Noz";
						$farmacy_filter = " and COALESCE(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2";

					}
					else {
						if (getRegionNick() == 'perm') {
							$filter .= " and exists(
								select
									ds.DrugState_id as \"DrugState_id\"
								from
									v_DrugState  ds

									inner join v_DrugProto dp  on dp.DrugProto_id = ds.DrugProto_id

									inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = dp.DrugRequestPeriod_id and CAST(
                                    :Date 
                                    as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate

									inner join ReceptFinance RF  on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = 
                                    :ReceptFinance_Code

								where
									ds.Drug_id = Drug.Drug_id
								limit 1
							) ";
						}

						switch ( $data['ReceptFinance_Code'] ) {
							case 1:
								if ( $data['ReceptType_Code'] == 2 ) {
									if ( !in_array(getRegionNick(), array('perm', 'ufa', 'saratov')) ) {
										$ostat_filter .= " and COALESCE(Ostat.Farm_Ostat, 0) + COALESCE(Ostat.RAS_Ostat, 0) > 0";

									}
									$drug_table = "v_Drug";
								}
								else {
									$drug_table = "v_DrugFed";
								}
							break;

							case 2:
								if ( $data['ReceptType_Code'] == 2 ) {
									if ( !in_array(getRegionNick(), array('perm', 'ufa', 'saratov')) ) {
										$ostat_filter .= " and COALESCE(Ostat.Farm_Ostat, 0) + COALESCE(Ostat.RAS_Ostat, 0) > 0";

									}
									$drug_table = "v_Drug";
								}
								else {
									$drug_table = "v_DrugReg";
								}
							break;

							default:
								$drug_table = "v_Drug";
							break;
						}

						if (getRegionNick() == 'perm' && !empty($data['WhsDocumentCostItemType_id'])) {
							$wdcit_data = $this->getFirstRowFromQuery("
								select
									coalesce(WhsDocumentCostItemType_IsDrugRequest, 1) as \"WhsDocumentCostItemType_IsDrugRequest\"
								from
									v_WhsDocumentCostItemType
								where
									WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
							", array(
								'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
							));
							if ($wdcit_data['WhsDocumentCostItemType_IsDrugRequest'] != 2) { //если в программе ЛЛО нет признака формирования заявки
								$drug_table = "dbo.fn_DrugFromDrugNormativeList({$data['WhsDocumentCostItemType_id']})";
							}
						}
					}
					
					if(!$data['ignoreCheck'] && !isset($data['DrugMnn_id']) && (!isset($data['query']) || strlen($data['query']) < 3) && (!isset($data['Drug_CodeG']) || strlen($data['Drug_CodeG']) < 3)){
						return array('Error_Msg' => 'Для поиска введите не менее 3 символов');
					}
					if ( isset($data['DrugMnn_id']) ) {
						// Выбрана запись из комбо "МНН"
						$filter .= " and Drug.DrugMnn_id = :DrugMnn_id";
						$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
					}
					if ( isset($data['query']) ) {
						// Поиск через доп. форму
						$filter .= " and Drug.Drug_Name iLIKE :Drug_Name";

						$queryParams['Drug_Name'] = "%" . $data['query'] . "%";
					}
					if (isset($data['Drug_CodeG']) && strlen($data['Drug_CodeG']) > 0) {
						$filter .= " and Drug.Drug_CodeG::varchar iLIKE :Drug_CodeG";

						$queryParams['Drug_CodeG'] = $data['Drug_CodeG'] . "%";
					}
					if ( isset($data['PrivilegeType_id']) ) {
						// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
						$filter .= "
							and COALESCE(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

							and COALESCE(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

						";
						$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
					}
					$ufa_farm_join = "";
					if($data['session']['region']['nick'] == 'ufa')
					{
						$ufa_farm_join = "
							inner join v_OrgFarmacyIndex OrgFarmacyIndex  on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id

								and OrgFarmacyIndex.Lpu_id = :Lpu_id
						";
					}

					$queryParams['ReceptFinance_id'] = $this->getFirstResultFromQuery(
						"Select ReceptFinance_id   as \"ReceptFinance_id\" from ReceptFinance RF  where RF.ReceptFinance_Code = :ReceptFinance_Code limit 1",

						array('ReceptFinance_Code' => $data['ReceptFinance_Code'])
					);

					if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
						$queryParams['ReceptFinanceOstat_id'] = $this->getFirstResultFromQuery(
							"select ReceptFinance_id  as \"ReceptFinance_id\" from ReceptFinance  where ReceptFinance_Code = 3 limit 1"

						);
					}
					else {
						$queryParams['ReceptFinanceOstat_id'] = $queryParams['ReceptFinance_id'];
					}

					$query = "
						WITH cte AS (	
						select
							distinct
							Drug.Drug_id,
							null as DrugRequestRow_id,
							Drug.DrugMnn_id,
							null as DrugFormGroup_id,
							Drug.Drug_IsKek as Drug_IsKEK,
							Drug.Drug_Name,
							null as Drug_DoseCount,
							null as Drug_Dose,
							null as Drug_Fas,
							COALESCE(cast(Drug.Drug_CodeG as varchar),'') as Drug_CodeG
						from
							" . $drug_table . " Drug 

							left join v_DrugState DS  ON DS.Drug_id = Drug.Drug_id

							left join PrivilegeDrug PD  on Drug.Drug_id = PD.Drug_id

							left join PrivilegeDrug PD2  on PD.Drug_id IS NULL AND DS.DrugProtoMnn_id = PD2.DrugProtoMnn_id

							".$mi_1_join."
						where (1 = 1) and " . $filter . $mi_1_where . "
						),
						
						Balance as (
							select
								Drug.Drug_id as Drug_id,
								SUM(COALESCE(case when DOA.OrgFarmacy_id != 1 and COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2 then DOA.DrugOstat_Kolvo end,0)) as Farm_Ostat,

								SUM(COALESCE(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end,0)) as RAS_Ostat

							from cte Drug 
                            inner join dbo.v_DrugOstat DOA on Drug.Drug_id = DOA.Drug_id
							inner join v_OrgFarmacy OrgFarmacy  on OrgFarmacy.OrgFarmacy_id = DOA.OrgFarmacy_id

							".$ufa_farm_join."

							where DOA.ReceptFinance_id = :ReceptFinanceOstat_id
							group by Drug.Drug_id
						)
                        
						select
							Drug.Drug_id as \"Drug_id\",
							null as \"DrugRequestRow_id\",
							Drug.DrugMnn_id as \"DrugMnn_id\",
							null as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as \"Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							null as \"Drug_DoseCount\",
							null as \"Drug_Dose\",
							null as \"Drug_Fas\",
							cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							COALESCE(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
							case when COALESCE(Ostat.Farm_Ostat, 0) <= 0 then case when COALESCE(Ostat.RAS_Ostat, 0) <= 0 then 2 else 1 end else 0 end as \"DrugOstat_Flag\",
							COALESCE(Drug.Drug_CodeG,'') as \"Drug_CodeG\"

						from
							cte Drug 

							LEFT JOIN LATERAL (

								select DP.DrugState_Price
								from v_DrugPrice DP 

								where DP.Drug_id = Drug.Drug_id and DP.DrugProto_begDate <= cast(:Date as timestamp) 
								and DP.ReceptFinance_id = :ReceptFinance_id
								order by DP.DrugProto_id desc
                                limit 1
							) DrugPrice ON true
							left join YesNo Drug_IsKEK  on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek

							left join Balance Ostat  on Ostat.Drug_id = drug.drug_id

						where (1=1)" . (!empty($ostat_filter) ? $ostat_filter : "") . "
						order by
							\"DrugOstat_Flag\", Drug.Drug_Name
					";
				}
				$queryParams['Date'] = $data['Date'];
				$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
			break;

			case 'request':

				if ( $data['ReceptType_Code'] == 2 ) {
					// На листе
					// Только медикаменты из заявки, имеющиеся на остатках
					if ( !in_array(getRegionNick(), array('perm', 'ufa', 'saratov')) ) {
						$filter .= " and COALESCE(Ostat.Farm_Ostat, 0) + COALESCE(Ostat.RAS_Ostat, 0) > 0";

					}
				}

				if (!empty($data['DrugRequestRow_id'])) {
					$filter .= " and DRR.DrugRequestRow_id = :DrugRequestRow_id";
					$queryParams['DrugRequestRow_id'] = $data['DrugRequestRow_id'];
				}

				switch ( $data['DrugRequestRow_IsReserve'] ) {
					case 1:
						// Не из резерва
						$filter .= " and DRR.Person_id = :Person_id";
					break;

					case 2:
						// Из резерва
						$filter .= " and DRR.Person_id is null";
						if (!empty($data['MedPersonal_id'])) {
							$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
							$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
						}

						if (!empty($data['DrugProtoMnn_id'])) {
							$filter .= " and DRR.DrugProtoMnn_id = :DrugProtoMnn_id";
							$queryParams['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
						}
					break;

					default:
						return false;
					break;
				}

				switch ( $data['ReceptFinance_Code'] ) {
					case 1:
						$data['ReceptFinance_id'] = 1;
						// $drug_table = "v_DrugFed";
						$filter .= " and DRR.DrugRequestType_id = 1";
					break;

					case 2:
						$data['ReceptFinance_id'] = 2;
						// $drug_table = "v_DrugReg";
						$filter .= " and DRR.DrugRequestType_id = 2";
					break;
				}

				if ( isset($data['RequestDrug_id']) ) {
				
					if ( isset($data['PrivilegeType_id']) ) {
						// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
						$filter .= "
							and COALESCE(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

							and COALESCE(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

						";
						$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
					}
					// Выбрана запись из комбо "Заявка" с заявкой по Drug_id
					$filter .= " and Drug.Drug_id = :RequestDrug_id";
					$filter .= " and (Drug.Drug_begDate is null or cast(Drug.Drug_begDate as timestamp) < cast(:Date as timestamp))";
					$filter .= " and (Drug.Drug_endDate is null or cast(Drug.Drug_endDate as timestamp) > cast(:Date as timestamp))";

					$query = "
						select
							DISTINCT 
							Drug.Drug_id as \"Drug_id\",
							DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
							Drug.DrugMnn_id as \"DrugMnn_id\",
							DF.DrugFormGroup_id as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as \"Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							Drug.Drug_DoseCount as \"Drug_DoseCount\",
							COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') as \"Drug_Dose\",

							Drug.Drug_Fas as \"Drug_Fas\",
							cast(DrugPrice.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							COALESCE(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",

							case when COALESCE(Ostat.Farm_Ostat, 0) <= 0 then case when COALESCE(Ostat.RAS_Ostat, 0) <= 0 then 2 else 1 end else 0 end as \"DrugOstat_Flag\"

						from
							v_DrugRequestRow DRR 

							inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

							inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

								and CAST(
                                :Date 
                                as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
							inner join v_Drug Drug  on Drug.Drug_id = DRR.Drug_id

							left join PrivilegeDrug PD  on PD.Drug_id = DRR.Drug_id

							left join PrivilegeDrug PD2  on PD.Drug_id IS NULL AND DRR.DrugProtoMnn_id = PD2.DrugProtoMnn_id

							LEFT JOIN LATERAL (

								select DP.DrugState_Price
								from v_DrugPrice DP 

								inner join ReceptFinance RF  on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = 
                                :ReceptFinance_Code

								where DP.Drug_id = Drug.Drug_id and DP.DrugProto_begDate <= 
                                :Date
								order by DP.DrugProto_id desc
                                limit 1
							) DrugPrice ON true
							inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

							left join YesNo Drug_IsKEK  on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek

							LEFT JOIN LATERAL (

								select
									SUM(case when DOA.OrgFarmacy_id != 1 then DOA.DrugOstat_Kolvo end) as Farm_Ostat,
									SUM(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end) as RAS_Ostat
								from v_DrugOstat DOA 

									inner join v_ReceptFinance RF  on RF.ReceptFinance_id = DOA.ReceptFinance_id

										and RF.ReceptFinance_Code = null
                                        :ReceptFinance_Code
								where DOA.Drug_id = Drug.Drug_id
							) Ostat ON true
							".$mi_1_join."
						where " . $filter . $mi_1_where .  "
						order by
							Drug.Drug_Name
					";

					$queryParams['RequestDrug_id'] = $data['RequestDrug_id'];
				}
				else {
					if ( isset($data['DrugMnn_id']) ) {
						// Выбрана запись из комбо "Заявка"
						$filter .= " and DPM.DrugMnn_id = :DrugMnn_id";
						$filter .= " and COALESCE(Drug.Drug_DoseCount, 0) = COALESCE(CAST(:Drug_DoseCount as bigint), 0)";

						// $filter .= " and COALESCE(DrugO.Drug_Dose, '') = COALESCE(:Drug_Dose, '')";

						// $filter .= " and COALESCE(DrugO.Drug_Fas, 0) = COALESCE(:Drug_Fas, 0)";


						$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
						$queryParams['Drug_DoseCount'] = $data['Drug_DoseCount'];
						// $queryParams['Drug_DoseQ'] = $data['Drug_DoseQ'];
						// $queryParams['Drug_Fas'] = $data['Drug_Fas'];

						if ( isset($data['DrugFormGroup_id']) ) {
							$filter .= " and COALESCE(DF.DrugFormGroup_id, 0) = COALESCE(CAST(:DrugFormGroup_id as bigint), 0)";

							$queryParams['DrugFormGroup_id'] = $data['DrugFormGroup_id'];
						}
					}
					else if ( isset($data['query']) && strlen($data['query']) >= 2 ) {
						$filter .= " and Drug.Drug_Name iLIKE :Drug_Name";

						$queryParams['Drug_Name'] = $data['query'] . "%";
					}
					else {
						return false;
					}

					$filter .= " and (Drug.Drug_begDate is null or cast(Drug.Drug_begDate as timestamp) < cast(:Date as timestamp))";
					$filter .= " and (Drug.Drug_endDate is null or cast(Drug.Drug_endDate as timestamp) > cast(:Date as timestamp))";

					if ( isset($data['PrivilegeType_id']) ) {
						// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
						$filter .= "
							and COALESCE(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

							and COALESCE(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

						";
						$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
					}
						
					$query = "
						select
							DISTINCT 
							Drug.Drug_id as \"Drug_id\",
							DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
							DPM.DrugMnn_id as \"DrugMnn_id\",
							DPM.DrugFormGroup_id as \"DrugFormGroup_id\",
							Drug.Drug_IsKek as \"Drug_IsKEK\",
							Drug.Drug_Name as \"Drug_Name\",
							DPM.Drug_DoseCount as \"Drug_DoseCount\",
							DPM.Drug_Dose as \"Drug_Dose\",
							DPM.Drug_Fas as \"Drug_Fas\",
							cast(DS.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
							COALESCE(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",

							case when COALESCE(Ostat.Farm_Ostat, 0) <= 0 then case when COALESCE(Ostat.RAS_Ostat, 0) <= 0 then 2 else 1 end else 0 end as \"DrugOstat_Flag\"

						from
							v_DrugRequestRow DRR 

							inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

							inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

								and CAST(
                                :Date 
                                as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
							inner join v_DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id

							inner join v_DrugState DS  on DS.DrugProtoMnn_id = DPM.DrugProtoMnn_id

							inner join v_DrugProto DP  on DP.DrugProto_id = DS.DrugProto_id

								and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							inner join v_Drug Drug  on Drug.Drug_id = DS.Drug_id

							inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

								and COALESCE(DF.DrugFormGroup_id, 0) = COALESCE(DPM.DrugFormGroup_id, 0)

							left join PrivilegeDrug PD  on Drug.Drug_id = PD.Drug_id

							left join PrivilegeDrug PD2  on PD.Drug_id IS NULL AND DPM.DrugProtoMnn_id = PD2.DrugProtoMnn_id

							left join YesNo Drug_IsKEK  on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek

							LEFT JOIN LATERAL (

								select
									SUM(case when DOA.OrgFarmacy_id != 1 then DOA.DrugOstat_Kolvo end) as Farm_Ostat,
									SUM(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end) as RAS_Ostat
								from v_DrugOstat DOA 

									inner join ReceptFinance RF  on RF.ReceptFinance_id = DOA.ReceptFinance_id

										and RF.ReceptFinance_Code = 
                                        :ReceptFinance_Code
								where DOA.Drug_id = Drug.Drug_id
							) Ostat ON true
							".$mi_1_join."
						where " . $filter . $mi_1_where . "
						order by
							Drug.Drug_Name
					";
					//		inner join " . $drug_table . " DrugTmp on DrugTmp.DrugMnn_id = DrugMnn.DrugMnn_id
				}

				$queryParams['Date'] = $data['Date'];
				$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
				$queryParams['Person_id'] = $data['Person_id'];
				$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
			break;

			case 'byrequest':
				switch ( $data['DrugRequestRow_IsReserve'] ) {
					case 1:
						// Не из резерва
						$filter .= " and DRR.Person_id = :Person_id";
					break;

					case 2:
						// Из резерва
						$filter .= " and DRR.Person_id is null";
						if (!empty($data['MedPersonal_id'])) {
							$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
							$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
						}

						if (!empty($data['DrugProtoMnn_id'])) {
							$filter .= " and DRR.DrugProtoMnn_id = :DrugProtoMnn_id";
							$queryParams['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
						}
					break;

					default:
						return false;
					break;
				}

				switch ( $data['ReceptFinance_Code'] ) {
					case 1:
						$data['ReceptFinance_id'] = 1;
						// $drug_table = "v_DrugFed";
						$filter .= " and DRR.DrugRequestType_id = 1";
					break;

					case 2:
						$data['ReceptFinance_id'] = 2;
						// $drug_table = "v_DrugReg";
						$filter .= " and DRR.DrugRequestType_id = 2";
					break;
				}

				if ( isset($data['query']) && strlen($data['query']) >= 2 ) {
					$filter .= " and Drug.Drug_Name iLIKE :Drug_Name";

					$queryParams['Drug_Name'] = $data['query'] . "%";
				}
				else {
					return false;
				}

				$filter .= " and (Drug.Drug_begDate is null or cast(Drug.Drug_begDate as timestamp) < cast(:Date as timestamp))";
				$filter .= " and (Drug.Drug_endDate is null or cast(Drug.Drug_endDate as timestamp) > cast(:Date as timestamp))";

				if ( isset($data['PrivilegeType_id']) ) {
					// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
					$filter .= "
						and COALESCE(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

						and COALESCE(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id

					";
					$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
				}

				$query = "
					select
						DISTINCT 
						Drug.Drug_id as \"Drug_id\",
						DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
						DPM.DrugMnn_id as \"DrugMnn_id\",
						DPM.DrugFormGroup_id as \"DrugFormGroup_id\",
						Drug.Drug_IsKek as \"Drug_IsKEK\",
						Drug.Drug_Name as \"Drug_Name\",
						DPM.Drug_DoseCount as \"Drug_DoseCount\",
						DPM.Drug_Dose as \"Drug_Dose\",
						DPM.Drug_Fas as \"Drug_Fas\",
						cast(DS.DrugState_Price as numeric(18, 2)) as \"Drug_Price\",
						COALESCE(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",

						case when COALESCE(Ostat.Farm_Ostat, 0) <= 0 then case when COALESCE(Ostat.RAS_Ostat, 0) <= 0 then 2 else 1 end else 0 end as \"DrugOstat_Flag\"

					from
						v_DrugRequestRow DRR 

						inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

						inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

							and CAST(
                            :Date 
                            as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
						inner join v_DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id

						inner join v_DrugState DS  on DS.DrugProtoMnn_id = DPM.DrugProtoMnn_id

						inner join v_DrugProto DP  on DP.DrugProto_id = DS.DrugProto_id

							and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
						inner join v_Drug Drug  on Drug.Drug_id = DS.Drug_id

						inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

							and COALESCE(DF.DrugFormGroup_id, 0) = COALESCE(DPM.DrugFormGroup_id, 0)

						left join PrivilegeDrug PD  on Drug.Drug_id = PD.Drug_id

						left join PrivilegeDrug PD2  on PD.Drug_id IS NULL AND DPM.DrugProtoMnn_id = PD2.DrugProtoMnn_id

						left join YesNo Drug_IsKEK  on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek

						LEFT JOIN LATERAL (

							select
								SUM(case when DOA.OrgFarmacy_id != 1 then DOA.DrugOstat_Kolvo end) as Farm_Ostat,
								SUM(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end) as RAS_Ostat
							from v_DrugOstat DOA 

								inner join ReceptFinance RF  on RF.ReceptFinance_id = DOA.ReceptFinance_id

									and RF.ReceptFinance_Code = 
                                    :ReceptFinance_Code
							where DOA.Drug_id = Drug.Drug_id
						) Ostat ON true
						".$mi_1_join."
					where " . $filter . $mi_1_where . "
					order by
						Drug.Drug_Name
				";

				$queryParams['Date'] = $data['Date'];
				$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
				$queryParams['Person_id'] = $data['Person_id'];
				$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
			break;

			default:
				return false;
			break;
		}

		if ( strlen($query) == 0 ) {
			return false;
		}
        /*
		echo getDebugSQL($query, $queryParams);
		exit();
        */
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Возвращает список наименований медикоментов
     */
	function loadDrugRequestMnnList($data) {
		$filter = "(1 = 1)";
		$filterCommon = "(1 = 1)";

		if ( isset($data['Drug_id']) ) {
			$query = "
				select 
					1 as \"id\",
					NULL as \"DrugRequestRow_id\",
					DrugMnn.DrugMnn_id as \"DrugMnn_id\",
					DF.DrugFormGroup_id as \"DrugFormGroup_id\",
					Drug.Drug_DoseCount as \"Drug_DoseCount\",
					COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') as \"Drug_Dose\",

					Drug.Drug_Fas as \"Drug_Fas\",
					DrugMnn.DrugMnn_Name || ' ' ||
						DF.DrugForm_Name || ' ' ||
						COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') || ' ' ||

						COALESCE(cast(nullif(Drug.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~') || ' ', '') ||

                        COALESCE(cast(nullif(Drug.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~') || ' ', '') ||

						case
							when COALESCE(Drug.Drug_Fas, 0) > 0 then 'n' || cast(Drug.Drug_Fas as varchar)

							when COALESCE(Drug.Drug_DoseCount, 0) > 0 then COALESCE(Drug.Drug_DoseUEEi || cast(Drug.Drug_DoseCount as varchar), '')

							else ''
						end
					as \"DrugRequestRow_Name\",
					'-' as \"MedPersonal_Name\"
				from
					v_Drug Drug 

					inner join v_DrugMnn DrugMnn  on DrugMnn.DrugMnn_id = Drug.DrugMnn_id

					inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

					left join DrugEdVol DEV  on DEV.DrugEdVol_id = Drug.DrugEdVol_id

	                left join DrugEdMass DEM  on DEM.DrugEdMass_id = Drug.DrugEdMass_id

				where Drug.Drug_id = :Drug_id
					and (Drug.Drug_endDate is null or Drug.Drug_endDate > cast(:Date as timestamp))
				limit 1
			";

			$queryParams = array(
				'Date' => $data['Date'],
				'Drug_id' => $data['Drug_id']
			);
		}
		else if ( !empty($data['DrugRequestRow_id']) ) {
			$query = "
				select 
					1 as \"id\",
					DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
					DPM.DrugMnn_id as \"DrugMnn_id\",
					DPM.DrugFormGroup_id as \"DrugFormGroup_id\",
					DPM.Drug_DoseCount as \"Drug_DoseCount\",
					DPM.Drug_Dose as \"Drug_Dose\",
					DPM.Drug_Fas as \"Drug_Fas\",
					COALESCE(DPM.DrugProtoMnn_Name, D.Drug_Name, '') as  \"DrugRequestRow_Name\",
					null as \"MedPersonal_Name\"
				from
					v_DrugRequestRow DRR 

					left join v_DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id

					left join v_Drug D  on D.Drug_id = DRR.Drug_id

				where
					DRR.DrugRequestRow_id = :DrugRequestRow_id
                limit 1
			";

			$queryParams = array(
				'DrugRequestRow_id' => $data['DrugRequestRow_id']
			);
		}
		else if ( isset($data['mode']) && $data['mode'] == 'all' ) {
			if (getRegionNick() == 'perm') {
				$filter .= " and exists(
					select 
						ds.DrugState_id
					from
						v_DrugState  ds

						inner join v_DrugProto dp  on dp.DrugProto_id = ds.DrugProto_id

						inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = dp.DrugRequestPeriod_id and CAST(:Date as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate

						inner join ReceptFinance RF  on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code

					where
						ds.Drug_id = Drug.Drug_id
				) ";
			}

			switch ( $data['ReceptFinance_Code'] ) {
				case 1:
					if ( $data['ReceptType_Code'] == 2 ) {
						if( !in_array(getRegionNick(), array('saratov', 'perm')) ) {
							$filter .= " and COALESCE(Ostat.DrugOstat_Kolvo, 0) > 0";

						}

						$query_from = "Drug ";

					}
					else {
						$query_from = " v_DrugFed DrugTmp  inner join v_Drug Drug  on Drug.Drug_id = DrugTmp.Drug_id ";

					}
				break;

				case 2:
					if ( $data['ReceptType_Code'] == 2 ) {
						if( !in_array(getRegionNick(), array('saratov', 'perm')) ) {
							$filter .= " and COALESCE(Ostat.DrugOstat_Kolvo, 0) > 0";

						}

						$query_from = "Drug ";

					}
					else {
						$query_from = " v_DrugReg DrugTmp  inner join v_Drug Drug  on Drug.Drug_id = DrugTmp.Drug_id ";

					}
				break;

				case 3:
					$data['ReceptFinance_Code'] = 1;
					$query_from = " v_Drug7Noz DrugTmp  inner join v_Drug Drug  on Drug.Drug_id = DrugTmp.Drug_id ";

				break;

				default:
					$query_from = "Drug ";
				break;
			}

			if ( isset($data['DrugMnn_id']) ) {
				// Выбрана запись из комбо "МНН"
				$filter .= " and DrugMnn.DrugMnn_id = :DrugMnn_id";
				$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
			}
			else if ( isset($data['query']) && strlen($data['query']) >= 2 ) {
				// Поиск через доп. форму
				$filter .= " and DrugMnn.DrugMnn_Name iLIKE :DrugMnn_Name";

				$queryParams['DrugMnn_Name'] = "%" . $data['query'] . "%";
			}
			else {
				return false;
			}
			
			if ( isset($data['PrivilegeType_id']) ) {
				// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
				$filter .= " and (PD.PrivilegeType_id = :PrivilegeType_id OR PD.PrivilegeType_id IS NULL)";
				$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
			}
			
			$query = "
				select
					ROW_NUMBER() OVER (ORDER BY DrugMnn.DrugMnn_Name, DF.DrugForm_Name, Drug.Drug_DoseQ, Drug.Drug_Fas) as \"id\",
					null as \"DrugRequestRow_id\",
					DrugMnn.DrugMnn_id as \"DrugMnn_id\",
					DF.DrugFormGroup_id as \"DrugFormGroup_id\",
					Drug.Drug_DoseCount as \"Drug_DoseCount\",
					COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') as \"Drug_Dose\",

					Drug.Drug_Fas as \"Drug_Fas\",
					DrugMnn.DrugMnn_Name || ' ' ||
						DF.DrugForm_Name || ' ' ||
						COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') || ' ' ||

						COALESCE(cast(nullif(Drug.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~') || ' ', '') ||

                        COALESCE(cast(nullif(Drug.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~') || ' ', '') ||

						case
							when COALESCE(Drug.Drug_Fas, 0) > 0 then 'n' || cast(Drug.Drug_Fas as varchar)

							when COALESCE(Drug.Drug_DoseCount, 0) > 0 then COALESCE(Drug.Drug_DoseUEEi || cast(Drug.Drug_DoseCount as varchar), '')

							else ''
						end
					as \"DrugRequestRow_Name\",
					'' as \"MedPersonal_Name\"
				from
					" . $query_from . "
					left join PrivilegeDrug PD  on Drug.Drug_id = PD.Drug_id

					inner join v_DrugMnn DrugMnn  on DrugMnn.DrugMnn_id = Drug.DrugMnn_id

					inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

					left join DrugEdVol DEV  on DEV.DrugEdVol_id = Drug.DrugEdVol_id

	                left join DrugEdMass DEM  on DEM.DrugEdMass_id = Drug.DrugEdMass_id

					LEFT JOIN LATERAL (

						select DOA.DrugOstat_Kolvo as DrugOstat_Kolvo
						from v_DrugOstat2 DOA 

						where DOA.OrgFarmacy_id != 2
							and DOA.Drug_id = Drug.Drug_id
							and ReceptFinance_id = 
                            :ReceptFinance_Code
                        limit 1
					) Ostat ON true
				where " . $filter . "
					and (Drug.Drug_endDate is null or Drug.Drug_endDate > cast(:Date as timestamp))
				group by
					DrugMnn.DrugMnn_id,
					DF.DrugFormGroup_id,
					Drug.Drug_DoseCount,
					Drug.Drug_DoseQ,
					Drug.Drug_DoseUEEi,
					Drug.Drug_DoseEi,
					Drug.Drug_Fas,
					DrugMnn.DrugMnn_Name,
					DF.DrugForm_Name,
					Drug.Drug_DoseEi,
					Drug.Drug_Vol,
                    Drug.Drug_Mass,
                    DEV.DrugEdVol_Name,
                    DEM.DrugEdMass_Name
				order by
					DrugMnn.DrugMnn_Name
			";

			$queryParams['Date'] = $data['Date'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else if ( isset($data['mode']) && $data['mode'] == 'only_search_form_filters' ) {
            $queryParams = array(
                'Date' => $data['Date'],
                'Drug_id' => $data['Drug_id']
            );

            if ( isset($data['query']) && strlen($data['query']) >= 2 ) {
                $filter .= " and DrugMnn.DrugMnn_Name iLIKE :DrugMnn_Name";

                $queryParams['DrugMnn_Name'] = "%" . $data['query'] . "%";
            }

            $query = "
				select 
					Drug_id as \"id\",
					NULL as \"DrugRequestRow_id\",
					DrugMnn.DrugMnn_id as \"DrugMnn_id\",
					DF.DrugFormGroup_id as \"DrugFormGroup_id\",
					Drug.Drug_DoseCount as \"Drug_DoseCount\",
					COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') as \"Drug_Dose\",

					Drug.Drug_Fas as \"Drug_Fas\",
					DrugMnn.DrugMnn_Name || ' ' ||
						DF.DrugForm_Name || ' ' ||
						COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') || ' ' ||

						COALESCE(cast(nullif(Drug.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~') || ' ', '') ||

                        COALESCE(cast(nullif(Drug.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~') || ' ', '') ||

						case
							when COALESCE(Drug.Drug_Fas, 0) > 0 then 'n' || cast(Drug.Drug_Fas as varchar)

							when COALESCE(Drug.Drug_DoseCount, 0) > 0 then COALESCE(Drug.Drug_DoseUEEi || cast(Drug.Drug_DoseCount as varchar), '')

							else ''
						end
					as \"DrugRequestRow_Name\",
					'-' as \"MedPersonal_Name\"
				from
					v_Drug Drug 

					inner join v_DrugMnn DrugMnn  on DrugMnn.DrugMnn_id = Drug.DrugMnn_id

					inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

					left join DrugEdVol DEV  on DEV.DrugEdVol_id = Drug.DrugEdVol_id

	                left join DrugEdMass DEM  on DEM.DrugEdMass_id = Drug.DrugEdMass_id

				where
				    {$filter}

				limit 250
			";
        } else {
			$queryParams = array(
				'Date' => $data['Date'],
				'Lpu_id' => $data['Lpu_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'Person_id' => $data['Person_id'],
				'ReceptFinance_Code' => $data['ReceptFinance_Code']
			);

			switch ( $data['DrugRequestRow_IsReserve'] ) {
				case 1:
					// Не из резерва
					$filter .= " and DRR.Person_id = :Person_id";
				break;

				case 2:
					// Из резерва
					$filter .= " and DRR.Person_id is null";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
						$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
					}

					if (!empty($data['DrugProtoMnn_id'])) {
						$filter .= " and DRR.DrugProtoMnn_id = :DrugProtoMnn_id";
						$queryParams['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
					}

                    if (empty($data['MedPersonal_id']) && empty($data['DrugProtoMnn_id'])) { //если ни врач ни медикамент не переданы - прерываем выполнение функции
                        return false;
                    }
				break;

				default:
					return false;
				break;
			}

			switch ( $data['ReceptFinance_Code'] ) {
				case 1:
					// $drug_table = "v_DrugFed";
					$filter .= " and DRR.DrugRequestType_id = 1";
				break;

				case 2:
					// $drug_table = "v_DrugReg";
					$filter .= " and DRR.DrugRequestType_id = 2";
				break;
			}

			switch ( $data['ReceptType_Code'] ) {
				case 1:
					// Без учета остатков
				break;

				case 2:
					// С учетом остатков (кроме Саратова)
					if ( !in_array(getRegionNick(), array('perm', 'saratov')) ) {
						$filterCommon .= " and COALESCE(Ostat.Farm_Ostat, 0) + COALESCE(Ostat.RAS_Ostat, 0) > 0";

					}
				break;

				default:
					return false;
				break;
			}

			if ( isset($data['PrivilegeType_id']) ) {
				// исключаем препараты которые не для этой категории (PrivilegeType_id в соотв. таблце не задан или равен выбранной категории)
				$filter .= " and COALESCE(PD.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id and COALESCE(PD2.PrivilegeType_id, :PrivilegeType_id) = :PrivilegeType_id";

				$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];					
			}
					
			$query = "
				select
					ROW_NUMBER() OVER (ORDER BY DrugRequestMnn.DrugRequestRow_Name, DrugRequestMnn.Drug_Dose, DrugRequestMnn.Drug_Fas) as \"id\",
					DrugRequestMnn.DrugRequestRow_id as \"DrugRequestRow_id\",
					DrugRequestMnn.RequestDrug_id as \"RequestDrug_id\",
					DrugRequestMnn.DrugMnn_id as \"DrugMnn_id\",
					DrugRequestMnn.DrugFormGroup_id as \"DrugFormGroup_id\",
					DrugRequestMnn.Drug_DoseCount as \"Drug_DoseCount\",
					DrugRequestMnn.Drug_Dose as \"Drug_Dose\",
					DrugRequestMnn.Drug_Fas as \"Drug_Fas\",
					DrugRequestMnn.DrugRequestRow_Name as \"DrugRequestRow_Name\",
					DrugRequestMnn.MedPersonal_Name as \"MedPersonal_Name\"
				from (
					select
						DRR.DrugRequestRow_id,
						0 as RequestDrug_id,
						DPM.DrugMnn_id,
						DPM.DrugFormGroup_id,
						DPM.Drug_DoseCount,
						DPM.Drug_Dose,
						DPM.Drug_Fas,
						DPM.DrugProtoMnn_Name as DrugRequestRow_Name,
						case
							when DR.Lpu_id =
                            :Lpu_id 
                            then RTRIM(MP.Person_Fio)
							when DR.Lpu_id in (select id from MinZdravList()) then 'МЗ'
							when DR.Lpu_id in (select id from OnkoList()) then 'Онкодиспансер'
							else ''
						end as MedPersonal_Name,
						null as Drug_id
					from
						v_DrugRequestRow DRR 

						left join PrivilegeDrug PD  on PD.Drug_id = DRR.Drug_id

						left join PrivilegeDrug PD2  on PD.Drug_id IS NULL AND DRR.DrugProtoMnn_id = PD2.DrugProtoMnn_id

						inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

						inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

							and cast(
                            :Date 
                            as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
						inner join v_DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id

						INNER JOIN LATERAL (

							select Drug_id
							from v_Drug 

							where DrugMnn_id = DPM.DrugMnn_id
								and COALESCE(Drug_endDate, '2030-01-01') > cast(
                                :Date 
                                as timestamp)
                            limit 1

						) Drug ON true
						left join v_MedPersonal MP  on MP.MedPersonal_id = DR.MedPersonal_id

							and MP.Lpu_id = DR.Lpu_id
					where " . $filter . "
			";

			if ( $data['DrugRequestRow_IsReserve'] != 2 ) {
				$query .= "

					union

					select
						DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
						Drug.Drug_id as \"RequestDrug_id\",
						Drug.DrugMnn_id as \"DrugMnn_id\",
						DF.DrugFormGroup_id as \"DrugFormGroup_id\",
						Drug.Drug_DoseCount as \"Drug_DoseCount\",
						COALESCE(Drug.Drug_DoseQ, '') || COALESCE(Drug.Drug_DoseEi, '') as \"Drug_Dose\",

						Drug.Drug_Fas as \"Drug_Fas\",
						Drug.Drug_Name as \"DrugRequestRow_Name\",
						'МЗ' as \"MedPersonal_Name\",
						Drug.Drug_id as \"Drug_id\"
					from
						v_DrugRequestRow DRR 

						left join PrivilegeDrug PD  on PD.Drug_id = DRR.Drug_id

						left join PrivilegeDrug PD2  on PD.Drug_id IS NULL AND DRR.DrugProtoMnn_id = PD2.DrugProtoMnn_id

						inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

						inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

							and cast(
                            :Date 
                            as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
						inner join v_Drug Drug  on Drug.Drug_id = DRR.Drug_id

						inner join DrugForm DF  on DF.DrugForm_id = Drug.DrugForm_id

						left join v_MedPersonal MP  on MP.MedPersonal_id = DR.MedPersonal_id

							and MP.Lpu_id = DR.Lpu_id
					where " . $filter . "
						and (Drug.Drug_endDate is null or Drug.Drug_endDate > cast(:Date as timestamp))
				";
			}

			$query .= "
				) DrugRequestMnn
				LEFT JOIN LATERAL (

					select
						SUM(case when DOA.OrgFarmacy_id != 1 then DOA.DrugOstat_Kolvo end) as Farm_Ostat,
						SUM(case when DOA.OrgFarmacy_id = 1 then DOA.DrugOstat_Kolvo end) as RAS_Ostat
					from v_DrugOstat DOA 

						inner join v_ReceptFinance RF  on RF.ReceptFinance_id = DOA.ReceptFinance_id

							and RF.ReceptFinance_Code = :ReceptFinance_Code
					where DOA.Drug_id = CAST(DrugRequestMnn.Drug_id as bigint)
				) Ostat ON true
				where " . $filterCommon . "
			   group by
					DrugRequestMnn.DrugRequestRow_id,
					DrugRequestMnn.RequestDrug_id,
					DrugRequestMnn.DrugMnn_id,
					DrugRequestMnn.DrugFormGroup_id,
					DrugRequestMnn.Drug_DoseCount,
					DrugRequestMnn.Drug_Dose,
					DrugRequestMnn.Drug_Fas,
					DrugRequestMnn.DrugRequestRow_Name,
					DrugRequestMnn.MedPersonal_Name
			";
		}
		/*
		echo getDebugSQL($query, $queryParams);
		exit();
		*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Возвращает список соответствий выписок и заявок на медикоменты
     */
	function loadDrugRequestOtovGrid($data) {
		$filter = "(1 = 1)";

        $and_period = "";
        $year = substr($data['Date'],0,4);

        if($year < '2016' && $data['session']['region']['nick'] == 'perm') //https://redmine.swan.perm.ru/issues/74758
            $and_period = " and DRP.DrugRequestPeriod_Name iLIKE '%квартал%'";


		$query = "
			select
				DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
				RTRIM(Lpu.Lpu_Nick) as \"Lpu_Nick\",
				COALESCE(MP.Person_Fio, '') as \"MedPersonal_Fio\",

				COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as \"DrugRequestRow_Name\",

				DRR.DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
				COALESCE(ERLpu.Lpu_Nick, '') as \"ER_Lpu_Nick\",

				ERMP.MedPersonal_id as \"ER_MedPersonal_id\",
				COALESCE(ERMP.Person_Fio, '') as \"ER_MedPersonal_Fio\",

				case
					when COALESCE(CAST(DrugData.Drug_DoseUEQ as double precision), CAST(Drug.Drug_DoseUEQ as double precision), 0) > 0
						and COALESCE(DrugData.DrugFormGroup_id, DrugForm.DrugFormGroup_id, 0) = COALESCE(ERDF.DrugFormGroup_id, 0)

						then COALESCE(CAST(ERD.Drug_DoseUEQ as double precision), 0) / COALESCE(CAST(DrugData.Drug_DoseUEQ as double precision), CAST(Drug.Drug_DoseUEQ as double precision)) * COALESCE(ER.EvnRecept_Kolvo, 0)

					else NULL
				end as \"EvnRecept_Kolvo\"
			from v_DrugRequestRow DRR 

				inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

				inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

					and CAST(
                    :Date 
                    as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
				inner join v_Lpu Lpu  on Lpu.Lpu_id = DR.Lpu_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = DR.MedPersonal_id

					and MP.Lpu_id = DR.Lpu_id
				left join v_DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id

				left join v_Drug Drug  on Drug.Drug_id = DRR.Drug_id

				left join DrugForm  on DrugForm.DrugForm_id = Drug.DrugForm_id

				LEFT JOIN LATERAL (

					select 
						DF.DrugFormGroup_id,
						D.Drug_DoseUEQ,
						D.Drug_DoseUEEi
					from
						v_DrugState DS 

						inner join v_DrugProto DP  on DP.DrugProto_id = DS.DrugProto_id

							and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
						inner join v_Drug D  on D.Drug_id = DS.Drug_id

						inner join DrugForm DF  on DF.DrugForm_id = D.DrugForm_id

					where DS.DrugProtoMnn_id = DPM.DrugProtoMnn_id
                    limit 1
				) DrugData ON true
				left join v_EvnRecept ER  on ER.DrugRequestRow_id = DRR.DrugRequestRow_id

				left join v_Drug ERD  on ERD.Drug_id = ER.Drug_id

					and COALESCE(ERD.Drug_DoseUEEi, '') = COALESCE(DrugData.Drug_DoseUEEi, '')

					and COALESCE(ERD.Drug_DoseUEQ, 0) = COALESCE(DrugData.Drug_DoseUEQ, 0)

				left join DrugForm ERDF  on ERDF.DrugForm_id = ERD.DrugForm_id

					and COALESCE(ERDF.DrugFormGroup_id, 0) = COALESCE(DrugData.DrugFormGroup_id, 0)

				left join v_Lpu ERLpu  on ERLpu.Lpu_id = ER.Lpu_id

				left join v_MedPersonal ERMP  on ERMP.MedPersonal_id = ER.MedPersonal_id

					and ERMP.Lpu_id = ER.Lpu_id
			where (1 = 1)
				and DRR.Person_id = :Person_id
			union all
			select distinct
				0 as \"DrugRequestRow_id\",
				'=== ВНЕ ЗАЯВКИ ===' as \"Lpu_Nick\",
				'' as \"MedPersonal_Fio\",
				COALESCE(ERD.Drug_Name, '') as \"DrugRequestRow_Name\",

				0 as \"DrugRequestRow_Kolvo\",
				COALESCE(ERLpu.Lpu_Nick, '') as \"ER_Lpu_Nick\",

				ERMP.MedPersonal_id as \"ER_MedPersonal_id\",
				COALESCE(ERMP.Person_Fio, '') as \"ER_MedPersonal_Fio\",

				COALESCE(ER.EvnRecept_Kolvo, 0) as \"EvnRecept_Kolvo\"

			from v_EvnRecept ER 

				inner join v_DrugRequestPeriod DRP  on CAST(
                :Date 
                as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate

					and ER.EvnRecept_setDT between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
					{$and_period}
				inner join v_Drug ERD  on ERD.Drug_id = ER.Drug_id

				inner join v_Lpu ERLpu  on ERLpu.Lpu_id = ER.Lpu_id

				inner join v_MedPersonal ERMP  on ERMP.MedPersonal_id = ER.MedPersonal_id

					and ERMP.Lpu_id = ER.Lpu_id
			where (1 = 1)
				and ER.DrugRequestRow_id is null
				and ER.Person_id = :Person_id
			
			order by \"DrugRequestRow_id\" desc, \"Lpu_Nick\"	";

		$queryParams = array(
			'Date' => $data['Date'],
			'Person_id' => $data['Person_id']
		);
        /*
		echo getDebugSQL($query, $queryParams);
		exit();
        */
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Возращает список заявок на медикоменты
     */
	function loadDrugRequestRowGrid($data) {
		$filter = "(1 = 1)";

		$queryParams = array(
			'Date' => $data['Date'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'MedPersonal_id' => null
		);

		switch ( $data['DrugRequestRow_IsReserve'] ) {
			case 1:
				// Не из резерва
				//if($data['session']['region']['nick'] != 'perm') //убираем для Перми по задаче https://redmine.swan.perm.ru/issues/79194
				$filter .= " and DRR.Person_id = :Person_id";
			break;

			case 2:
				// Из резерва
				$filter .= " and DRR.Person_id is null";
				if (!empty($data['MedPersonal_id'])) {
					$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
					$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
				}

				if (!empty($data['DrugProtoMnn_id'])) {
					$filter .= " and DRR.DrugProtoMnn_id = :DrugProtoMnn_id";
					$queryParams['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
				}
				// $filter .= " and DR.Lpu_id = :Lpu_id";
			break;

			default:
				return false;
			break;
		}
		if (isset($data['ReceptFinance_id']))
		{
			if($data['ReceptFinance_id']==1)
				$filter .= " and DRT.DrugRequestType_id = 1";
			else if($data['ReceptFinance_id']==2)
				$filter .= " and DRT.DrugRequestType_id = 2";
			else
				return false;
		}
		$apply = '';
		if(isset($data['CurrentLpu_id']))
		{
			if($data['DrugRequestRow_IsReserve'] == 2) //https://redmine.swan.perm.ru/issues/80447 - доп фильтр на заявки - только из выбранного МО или его предшественников
			{
				$filter .= "
					and DR.Lpu_id in (select Lpu_id from v_Lpu  where Lpu_pid = :CurrentLpu_id or Lpu_id = :CurrentLpu_id)

				";
				$queryParams['CurrentLpu_id'] = $data['CurrentLpu_id'];
			}
		}
		$query = "
			select
				DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
				COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as \"DrugRequestRow_Name\",

				DRR.DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
				DRV.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
				case when COALESCE(DRR.DrugRequestRow_Kolvo, 0) > 0 then ROUND((DRR.DrugRequestRow_Summa / DRR.DrugRequestRow_Kolvo)::numeric, 2) else null end as \"DrugRequestRow_Price\",

				DRR.DrugRequestRow_Summa as \"DrugRequestRow_Summa\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				RTRIM(Lpu.Lpu_Nick) as \"Lpu_Nick\",
				RTRIM(DRT.DrugRequestType_Name) as \"DrugRequestType_Name\",
				case
					when 
                    ((cast(:Lpu_id as bigint) not in (select id from OnkoList())) or 
                    (DR.MedPersonal_id != COALESCE( :MedPersonal_id ,0)))
                    and (DR.Lpu_id in (select id from OnkoList()))
                    	then 'ОНКО'
					when DR.Lpu_id in (select id from MinZdravList())
						then 'МЗ'
						else COALESCE(MP.Person_Fio, '-')
				end as \"MedPersonal_Fio\",
				to_char(DRR.DrugRequestRow_insDT, 'DD.MM.YYYY') as \"DrugRequestRow_insDate\",

				to_char(DRR.DrugRequestRow_updDT, 'DD.MM.YYYY') as \"DrugRequestRow_updDate\",

				to_char(DRR.DrugRequestRow_delDT, 'DD.MM.YYYY') as \"DrugRequestRow_delDate\",

				'<font color=\"green\">'||DRP.DrugRequestPeriod_Name||'<font>' as \"DrugRequestPeriod_Name\"
			from
				DrugRequestRow DRR 

				inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

				inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

					and cast(
                    :Date 
                    as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
				inner join v_DrugRequestType DRT  on DRT.DrugRequestType_id = DRR.DrugRequestType_id

				inner join v_Lpu Lpu  on Lpu.Lpu_id = DR.Lpu_id

				left join v_DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = DR.MedPersonal_id

					and MP.Lpu_id = DR.Lpu_id
				left join v_Drug Drug  on Drug.Drug_id = DRR.Drug_id

				LEFT JOIN LATERAL (

					select
						SUM(ER.EvnRecept_Kolvo) as EvnRecept_Kolvo
					from
						v_EvnRecept ER 

						inner join v_Drug Drug2   on Drug2.Drug_id = ER.Drug_id

						inner join v_DrugRequestRow DRR2  on DRR2.DrugRequestRow_id = ER.DrugRequestRow_id

							and DRR2.DrugRequestRow_id = DRR.DrugRequestRow_id
				) DRV ON true
			where " . $filter . "
			order by
				\"DrugRequestRow_Name\"
		";
        /*
		echo getDebugSQL($query, $queryParams);
		exit();
        */
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Возвращает список заявок на медикоменты по человеку
     */
	function loadDrugRequestMedPersonalList($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		if (!empty($data['Lpu_rid'])) {
			$filter .= " and MP.Lpu_id = :Lpu_rid";
			$queryParams['Lpu_rid'] = $data['Lpu_rid'];
		} else if (!empty($data['MedPersonal_rid'])) {
			$filter .= " and MP.MedPersonal_id = :MedPersonal_rid";
			$queryParams['MedPersonal_rid'] = $data['MedPersonal_rid'];
		} else {
			return array();
		}

		$queryParams['Date'] = $data['Date'];
		$queryParams['Person_id'] = $data['Person_id'];

		$query = "
			select distinct
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Lpu_id as \"Lpu_id\",
				MP.MedPersonal_Code as \"MedPersonal_DloCode\",
				MP.MedPersonal_TabCode as \"MedPersonal_TabCode\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				2 as \"MedPersonal_IsRequest\",
				1 as \"MedPersonal_ReserveEnable\",
				case when PC.PersonCard_id is null then 1 else 2 end \"MedPersonal_IsMain\"
			from
				v_MedPersonal mp 

				LEFT JOIN LATERAL(

					SELECT 
						pcs.PersonCard_id
					FROM
						v_PersonCardState pcs 

						INNER JOIN v_MedStaffRegion msr  ON msr.MedPersonal_id = mp.MedPersonal_id AND msr.LpuRegion_id = pcs.LpuRegion_id AND msr.MedStaffRegion_isMain = 2

					WHERE
						pcs.Person_id = :Person_id
						AND pcs.LpuAttachType_id = 1
                    limit 1
				) PC ON true
            where
            	{$filter}
		";
		//echo getDebugSql($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;

		/*$mp = array();
		$mpReserve = array();

		// Получаем список врачей у которых есть заявка на выбранного персона
		// where DRR.Person_id = :Person_id - этот кусок убрал в рамках задачи https://redmine.swan.perm.ru/issues/79194
		$query = "
			select distinct
				MP.MedPersonal_id,
				MP.Lpu_id,
				MP.MedPersonal_Code as MedPersonal_DloCode,
				MP.MedPersonal_TabCode,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				2 as MedPersonal_IsRequest,
				1 as MedPersonal_ReserveEnable
			from v_DrugRequestRow DRR 

                inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

                inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

					and cast(:Date as datetime) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
				inner join dbo.v_MedPersonal mp  on DR.Lpu_id = MP.Lpu_id and DR.MedPersonal_id = MP.MedPersonal_id

            where (1=1)
				and {$filter}
		";
		//echo getDebugSql($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$mp = $result->result('array');
		}

		// Получаем список врачей у которых есть резерв на персонов, и где этот врач связан с участком, на котором прикреплен пациент
		$query = "
			Declare @LpuRegion_id bigint;
			-- Предполагаем у одного пациента одно активное прикрепление
			Set @LpuRegion_id = (
				select top 1 LpuRegion_id
				from v_PersonCard PC 

				where PC.Person_id = :Person_id
                    and PC.LpuAttachType_id = 1
					and cast(cast(PC.PersonCard_begDate as date) as datetime) <= cast(:Date as datetime)
					and (PC.PersonCard_endDate is null or cast(cast(PC.PersonCard_endDate as date) as datetime) > cast(:Date as datetime)));

			select distinct
				MP.MedPersonal_id,
				MP.Lpu_id,
				MP.MedPersonal_Code as MedPersonal_DloCode,
				MP.MedPersonal_TabCode,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				2 as MedPersonal_IsRequest,
				2 as MedPersonal_ReserveEnable
			from v_DrugRequestRow DRR 

                inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id

                inner join v_DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

					and cast(:Date as datetime) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
				inner join dbo.v_MedPersonal mp  on DR.Lpu_id = MP.Lpu_id and DR.MedPersonal_id = MP.MedPersonal_id

            where DRR.Person_id is null
                and {$filter}
				and EXISTS(select top 1 1 from v_MedStaffRegion MSR where MSR.MedPersonal_id = MP.MedPersonal_id and MSR.LpuRegion_id = @LpuRegion_id)

		";
		//echo getDebugSql($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$mpReserve = $result->result('array');
		}

		if (count($mp)>0 && count($mpReserve)>0) {
			// надо объединять результаты хитрым способом: по MedPersonal_id и Lpu_id
			foreach ($mp as $key=>$row) {
				foreach ($mpReserve as $keyR => $rowR) {
					if ($row['MedPersonal_id'] == $rowR['MedPersonal_id'] && $row['Lpu_id'] == $rowR['Lpu_id']) {
						// переносим только признак резерва
						$mp[$key]['MedPersonal_ReserveEnable'] = $mpReserve[$keyR]['MedPersonal_ReserveEnable'];
						// и прибъем учтенную запись
						unset($mpReserve[$keyR]);
						break;
					}
				}
			}
			// Оставшиеся массивы можно объединить: все "неучтенные" записи по резерву нужно загнать в общий массив
			$mp = array_merge($mp, $mpReserve);
			return $mp;
		} elseif (count($mp)>0 || count($mpReserve)>0) {
			// нужно вернуть тот результат, где количество больше нуля
			return (count($mp)>0)?$mp:$mpReserve;
		} else {
			return false;
		}*/
	}


	/**
	 * Получение предшественников МО
	 */
	function getLpuPrev($data) {
		$queryParams = array();
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$query = "
			select
				L.Lpu_id as \"Lpu_id\"
			from
				v_Lpu L 

			where
				L.Lpu_pid = :Lpu_id
		";
		$result = $this->db->query($query, $queryParams);
		if(is_object($result)){
			return $result->result('array');
		}
		else
			return false;
	}

    /**
     * Возвращает данные для формы редактирования рецепта
     */
	function loadEvnReceptEditForm($data) {

		$queryParams = array();
		//if ( !isMinZdrav() && !isFarmacy() ) {
		if ( !isMinZdravOrNotLpu() && !isFarmacy() ) {
			$lpu_filter = "and ER.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
            $query_check = "
                select Lpu_id as \"Lpu_id\"
                from v_EvnRecept_all 

                where EvnRecept_id = :EvnRecept_id
            ";
            $res_check = $this->db->query($query_check, array('EvnRecept_id' => $data['EvnRecept_id']));
            if(is_object($res_check)){
                $result_check = $res_check->result('array');
                if(is_array($result_check) && count($result_check)>0)
                {
                    if($result_check[0]['Lpu_id'] <> $data['Lpu_id'])
                    {
						//return array(array('success' => true, 'Error_Code'=>'error_cause_lpu'));
						$lpu_filter = "";
                    }
                }
                else
                    return array(array('success' => false, 'Error_Code'=>'error_query_lpu'));
            }
            else
                return array(array('success' => false, 'Error_Code'=>'error_query_lpu'));
		}
		else {
			$lpu_filter = "";
		}
        //var_dump('dfdf');die;
		$query = "
			SELECT 
				er.EvnRecept_id as \"EvnRecept_id\",
				er.EvnRecept_pid as \"EvnRecept_pid\",
				er.Lpu_id as \"Lpu_id\",
				er.ReceptType_id as \"ReceptType_id\",
				ERec.ReceptForm_id as \"ReceptForm_id\",
				er.PrivilegeType_id as \"PrivilegeType_id\",
				pp.PersonPrivilege_id as \"PersonPrivilege_id\",
				to_char(er.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\",
				er.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\", 
				er.ReceptFinance_id as \"ReceptFinance_id\",
				er.DrugFinance_id as \"DrugFinance_id\",
				er.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				er.ReceptDiscount_id as \"ReceptDiscount_id\",
				RTRIM(er.EvnRecept_Ser) as \"EvnRecept_Ser\",
				RTRIM(er.EvnRecept_Num) as \"EvnRecept_Num\",
				er.EvnRecept_IsMnn as \"Drug_IsMnn\",
				er.EvnRecept_IsMnn as \"EvnRecept_IsMnn\",
				er.Drug_rlsid as \"Drug_rlsid\",
				er.Drug_id as \"Drug_id\",
				COALESCE(dg.Drug_Name, drls.Drug_Name, dcmnn.DrugComplexMnn_RusName) as \"Drug_Name\",
				dtorg.DrugTorg_Name as \"DrugTorg_Name\",
				case when DRR.DrugRequestRow_id is null then dg.DrugMnn_id else null end as \"DrugMnn_id\",
				RTRIM(er.EvnRecept_Signa) as \"EvnRecept_Signa\",
				case when 2 = COALESCE(ERec.EvnRecept_IsDelivery, 1)
					then 'true'
					else 'false'
				end as \"EvnRecept_IsDelivery\",
				er.LpuSection_id as \"LpuSection_id\",
				er.MedPersonal_id as \"MedPersonal_id\",
				er.OrgFarmacy_id as \"OrgFarmacy_id\",
				er.ReceptValid_id as \"ReceptValid_id\",
				DR.Lpu_id as \"Lpu_rid\",
				MP.MedPersonal_id as \"MedPersonal_rid\",
				DRR.DrugRequestRow_id as \"DrugRequestMnn_id\",
				round(er.EvnRecept_Kolvo, 2) as \"EvnRecept_Kolvo\",
				COALESCE(er.EvnRecept_IsExtemp, 1) as \"EvnRecept_IsExtemp\",
				RTRIM(er.EvnRecept_ExtempContents) as \"EvnRecept_ExtempContents\",
				COALESCE(er.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\",
				er.EvnRecept_IsKEK as \"EvnRecept_IsKEK\",
				er.EvnRecept_IsKEK as \"Drug_IsKEK\",
				er.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				er.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
				ERec.ReceptDelayType_id as \"ReceptWrongDelayType_id\",
				COALESCE(RDT.ReceptDelayType_Code,-1) as \"Recept_Result_Code\",
				'' as \"Recept_Result\",
				'' as \"Recept_Delay_Info\",
				'' as \"EvnRecept_Drugs\",
				to_char(wr.ReceptWrong_insDT, 'DD.MM.YYYY') as \"ReceptWrong_DT\",
				DATEDIFF('day',ERec.EvnRecept_obrDT,dbo.tzGetDate()) as \"ReceptDelay_1_days\",
				wr.ReceptWrong_Decr as \"ReceptWrong_Decr\",
				er.Person_id as \"Person_id\",
				--RO.ReceptOtov_insDT,
				to_char(RO.ReceptOtov_insDT, 'DD.MM.YYYY') as \"ReceptOtov_insDT\",
				to_char(RO.EvnRecept_obrDate, 'DD.MM.YYYY') as \"ReceptOtov_obrDate\",
				to_char(RO.EvnRecept_otpDate, 'DD.MM.YYYY') as \"ReceptOtov_otpDate\",
				COALESCE(OrgF.Org_Name,'') as \"ReceptOtov_Farmacy\",
				COALESCE(OrgF.Org_Name,OFBefore.Org_Name, '') as \"Recept_Farmacy\",
				COALESCE(er.EvnRecept_deleted,1) as \"EvnRecept_deleted\",
				--COALESCE(er.EvnRecept_oPrice,ERec.EvnRecept_Price) as \"Drug_Price\"
				CAST(coalesce(er.EvnRecept_oPrice,ERec.EvnRecept_Price) as decimal(12,2)) as \"Drug_Price\",				
				er.EvnRecept_VKProtocolNum as \"EvnRecept_VKProtocolNum\",
				to_char(er.EvnRecept_VKProtocolDT, 'DD.MM.YYYY') as \"EvnRecept_VKProtocolDT\",
				er.CauseVK_id as \"CauseVK_id\",
				ERec.EvnRecept_IsPrinted as \"EvnRecept_IsPrinted\",
				ERec.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				ERec.EvnCourseTreatDrug_id as \"EvnCourseTreatDrug_id\",
				cast(ectd.EvnCourseTreatDrug_KolvoEd as decimal) as  \"EvnCourseTreatDrug_KolvoEd\",
				cast(ectd.EvnCourseTreatDrug_Kolvo as decimal) as \"EvnCourseTreatDrug_Kolvo\",
				ectd.GoodsUnit_sid as \"GoodsUnit_sid\",
				ectd.GoodsUnit_id as \"GoodsUnit_id\",
				ect.EvnCourseTreat_MaxCountDay as \"EvnCourseTreat_CountDay\",
				ect.PrescriptionIntroType_id as \"PrescriptionIntroType_id\",
				to_char(ect.EvnCourseTreat_setDate, 'DD.MM.YYYY') as \"EvnCourseTreat_setDate\",
				ect.EvnCourseTreat_Duration as \"EvnCourseTreat_Duration\",
				er.PrescrSpecCause_id as \"PrescrSpecCause_id\",
				er.ReceptUrgency_id as \"ReceptUrgency_id\",
				er.EvnRecept_IsExcessDose as \"EvnRecept_IsExcessDose\"
			FROM v_EvnRecept_all er 
				left join v_Drug dg  on er.Drug_id = dg.Drug_id
				left join v_DrugRequestRow DRR  on DRR.DrugRequestRow_id = er.DrugRequestRow_id
				left join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id
				left join v_EvnRecept ERec  on ERec.EvnRecept_id = er.EvnRecept_id
				left join v_MedPersonal MP  on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				left join ReceptWrong wr  on wr.EvnRecept_id = er.EvnRecept_id
				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = ERec.ReceptDelayType_id
				left join ReceptOtov RO  on RO.EvnRecept_id = ERec.EvnRecept_id
				left join v_OrgFarmacy OrgF  on OrgF.OrgFarmacy_id = RO.OrgFarmacy_id
				left join v_Diag d  on d.Diag_id = er.Diag_id
				left join v_DrugTorg dtorg  on dtorg.DrugTorg_id = dg.DrugTorg_id
				left join v_Drug drls  on drls.Drug_id = er.Drug_rlsid
				left join rls.v_DrugComplexMnn dcmnn  on dcmnn.DrugComplexMnn_id = er.DrugComplexMnn_id
				left join v_OrgFarmacy OFBefore  on OFBefore.OrgFarmacy_id = er.OrgFarmacy_id
				left join v_EvnCourseTreatDrug ectd on ectd.EvnCourseTreatDrug_id = ERec.EvnCourseTreatDrug_id
				left join v_EvnCourseTreat ect on ect.EvnCourseTreat_id = ectd.EvnCourseTreat_id
				left join v_PersonPrivilege pp on pp.PrivilegeType_id = er.PrivilegeType_id and pp.Person_id = er.Person_id
			WHERE (1 = 1)
				and er.EvnRecept_id = :EvnRecept_id
				" . $lpu_filter . "
			LIMIT 1
		";

		$queryParams['EvnRecept_id'] = $data['EvnRecept_id'];
		//echo getDebugSQL($query,$queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result = $result->result('array');
			$result[0]['ReceptOtov_Date'] = '';
			if($result[0]['EvnRecept_deleted'] == 2)
				$result[0]['Recept_Result_Code'] = 4;
			if(count($result) > 0){
				switch ($result[0]['Recept_Result_Code'])
				{
					case 0:
						$result[0]['Recept_Result'] = 'Обслужен';
						$query_drug = "
							select distinct COALESCE(D.Drug_Name,'') as \"Drug_Name\"

							from ReceptOtov RO 

							left join rls.v_Drug D  on D.Drug_id = RO.Drug_cid

							where RO.EvnRecept_id = :EvnRecept_id
						";
						$result_drug = $this->db->query($query_drug,$queryParams);
						if(is_object($result_drug)){
							$result_drug = $result_drug->result('array');
							for ($i=0; $i < count($result_drug); $i++){
								$result[0]['EvnRecept_Drugs'] .= $result_drug[$i]['Drug_Name'].PHP_EOL;
							}
						}
						$result[0]['ReceptOtov_Date'] = $result[0]['ReceptOtov_otpDate'];
						break;
					case 1:
						$result[0]['Recept_Result'] = 'На отсроченном обеспечении';
						$result[0]['Recept_Delay_Info'] = 'Рецепт на отсроченном обеспечении '.$result[0]['ReceptDelay_1_days'].' дн.';
						$result[0]['ReceptOtov_Date'] = $result[0]['ReceptOtov_obrDate'];
						break;
					case 2:
						$result[0]['Recept_Result'] = 'Признан неправильно выписанным';
						$query_info = "
							select 
								to_char(RW.ReceptWrong_insDT, 'DD.MM.YYYY') as \"Wrong_Date\",

								COALESCE(RW.ReceptWrong_Decr,'') as \"Wrong_Cause\"

							from v_ReceptWrong RW 

							where RW.EvnRecept_id = :EvnRecept_id
                            limit 1
						";
						$result_info = $this->db->query($query_info,$queryParams);
						if(is_object($result_info)){
							$result_info = $result_info->result('array');
							if(count($result_info) > 0){
								$result[0]['Recept_Delay_Info'] = 'От '.$result_info[0]['Wrong_Date'].'. Причина: '.$result_info[0]['Wrong_Cause'];
							}
						}
						break;
					case 4:
						$result[0]['Recept_Result'] = $this->regionNick == 'msk' ? 'Аннулирован' : 'Удален';
						$query_info = "
							select
								RRCT.ReceptRemoveCauseType_Name as \"Del_Cause\",
								RTRIM(PUC.PMUser_Name) as \"Del_User\",
								to_char(ER.EvnRecept_updDT, 'DD.MM.YYYY') as \"Del_Date\"

							from v_EvnRecept_all ER 

							left join v_pmUserCache PUC  on PUC.pmUser_id = ER.pmUser_updID

							left join v_ReceptRemoveCauseType RRCT  on RRCT.ReceptRemoveCauseType_id = ER.ReceptRemoveCauseType_id

							where ER.EvnRecept_id = :EvnRecept_id
						";
						$result_info = $this->db->query($query_info, $queryParams);
						if(is_object($result_info)){
							$result_info = $result_info->result('array');
							if(count($result_info) > 0){
								$result[0]['Recept_Delay_Info'] = 'Дата ' . ($this->regionNick == 'msk' ? 'аннулирования' : 'удаления') .': '.$result_info[0]['Del_Date'].PHP_EOL.'Пользователь: '.$result_info[0]['Del_User'].PHP_EOL.'Причина:'.$result_info[0]['Del_Cause'];
							};
						}
						break;
					case 5:
						$result[0]['Recept_Result'] = 'Снят с отсроченного обеспечения';
						$query_info = "
							select 
								COALESCE(wdu.WhsDocumentUc_Num,'') as \"Act_Num\",

								to_char(wduaro.WhsDocumentUcActReceptOut_setDT, 'DD.MM.YYYY') as \"Act_Date\",

								COALESCE(wduarl.WhsDocumentUcActReceptList_outCause,'') as \"Act_Cause\"

							from v_WhsDocumentUcActReceptList wduarl 

							inner join v_WhsDocumentUcActReceptOut wduaro  on wduaro.WhsDocumentUcActReceptOut_id = wduarl.WhsDocumentUcActReceptOut_id

							inner join v_WhsDocumentUc wdu  on wdu.WhsDocumentUc_id = wdu.WhsDocumentUc_id

							where wduarl.EvnRecept_id = :EvnRecept_id
							and wdu.WhsDocumentType_id = 24
							limit 1
						";
						$result[0]['ReceptOtov_Date'] = $result[0]['ReceptOtov_obrDate'];
						$result_info = $this->db->query($query_info,$queryParams);
						if(is_object($result_info)){
							$result_info = $result_info->result('array');
							if(count($result_info) > 0){
								$result[0]['Recept_Delay_Info'] = 'Акт №'.$result_info[0]['Act_Num'].' от '.$result_info[0]['Act_Date'].'. Причина: '.$result_info[0]['Act_Cause'];
							}
						}
						break;
				}
			}
			return $result;
			//return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
     *  Получение списка остатков по медикаменту в аптеках
     */
	function loadOrgFarmacyList($data) {
		$queryParams = array();

		if ( isset($data['OrgFarmacy_id']) ) {
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when COALESCE(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else COALESCE(DrugOstat.DrugOstat_Kolvo, 0) end as \"DrugOstat_Kolvo\"

				from v_OrgFarmacy OrgFarmacy 

					inner join v_Org Org  on Org.Org_id = OrgFarmacy.Org_id

					inner join YesNo  on YesNo.YesNo_id = COALESCE(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)


					LEFT JOIN LATERAL (

						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA 

							inner join v_ReceptFinance ReceptFinance  on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id

								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat ON true
				where (1 = 1)
					and OrgFarmacy.OrgFarmacy_id = :OrgFarmacy_id
					and COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2

			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else if ( $data['EvnRecept_IsExtemp'] == 2 ) {
			// Экстемпоральный рецепт. Загрузка списка включенных аптек
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					0 as \"DrugOstat_Kolvo\"
				from v_OrgFarmacy OrgFarmacy 

					inner join v_OrgFarmacyIndex OrgFarmacyIndex  on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id

						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org  on Org.Org_id = OrgFarmacy.Org_id

					inner join YesNo  on YesNo.YesNo_id = COALESCE(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)


				where COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2

					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
				order by \"OrgFarmacy_Name\"
			";

			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		else if ( $data['EvnRecept_Is7Noz_Code'] == 1 ) {
			// 7 нозологий
			//Для Уфы добавлена привязка к ЛПУ и условие на OrgFarmacy_IsNozLgot=2 - https://redmine.swan.perm.ru/issues/63752
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when COALESCE(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else COALESCE(DrugOstat.DrugOstat_Kolvo, 0) end as \"DrugOstat_Kolvo\"

				from v_OrgFarmacy OrgFarmacy 

					inner join v_Org Org  on Org.Org_id = OrgFarmacy.Org_id

					inner join YesNo  on YesNo.YesNo_id = COALESCE(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)


					".($data['session']['region']['nick'] == 'ufa' ? "
					 inner join v_OrgFarmacyIndex OrgFarmacyIndex  on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id

						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					":" ")."
					LEFT JOIN LATERAL (

						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA 

							inner join v_ReceptFinance ReceptFinance  on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id

								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat ON true
				where (1 = 1)
					and COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2

					and COALESCE(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2

					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
					".($data['session']['region']['nick'] == 'ufa'? " and OrgFarmacy.OrgFarmacy_IsNozLgot = 2":"")."
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else if ( $data['ReceptType_Code'] == 1 ) {
			// Тип рецепта "На бланке"
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					RTRIM(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when COALESCE(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else COALESCE(DrugOstat.DrugOstat_Kolvo, 0) end as \"DrugOstat_Kolvo\"

				from v_OrgFarmacy OrgFarmacy 

					inner join v_OrgFarmacyIndex OrgFarmacyIndex  on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id

						and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org  on Org.Org_id = OrgFarmacy.Org_id

					inner join YesNo  on YesNo.YesNo_id = COALESCE(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)


					LEFT JOIN LATERAL (

						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA 

							inner join v_ReceptFinance ReceptFinance  on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id

								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat ON true
				where (1 = 1)
					and COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2

					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		else {
			$fields = "";
			$filter_pers = "";
			if (!empty($data['Person_id'])) {
				// выводить любимую аптеку человека, даже если она не прикреплена к МО
				$filter_pers = " or exists(select ofp.OrgFarmacyPerson_id from v_OrgFarmacyPerson ofp  where ofp.Person_id = :Person_id and ofp.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id)";

				$queryParams['Person_id'] = $data['Person_id'];

				// отдадим признак является ли аптека любимой для пациента
				$fields .= ", case when exists(select ofp.OrgFarmacyPerson_id from v_OrgFarmacyPerson ofp  where ofp.Person_id = :Person_id and ofp.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id) then 2 else 1 end as OrgFarmacy_IsFavorite";

				$queryParams['Person_id'] = $data['Person_id'];
			}
			$filter = " and (OrgFarmacyIndex.OrgFarmacyIndex_id is not null{$filter_pers})";

			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					RTRIM(Org.Org_Name) as \"OrgFarmacy_Name\",
					COALESCE(RTRIM(OrgFarmacy.OrgFarmacy_HowGo),'Адрес аптеки не указан') as \"OrgFarmacy_HowGo\",

					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when COALESCE(DrugOstat.DrugOstat_Kolvo, 0) <= 0 then 0 else COALESCE(DrugOstat.DrugOstat_Kolvo, 0) end as \"DrugOstat_Kolvo\",

					0 as \"sort\"
					{$fields}
				from v_OrgFarmacy OrgFarmacy 

					inner join v_Org Org  on Org.Org_id = OrgFarmacy.Org_id

					inner join YesNo  on YesNo.YesNo_id = COALESCE(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)


					left join v_OrgFarmacyIndex OrgFarmacyIndex  on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = 
                    :Lpu_id

					LEFT JOIN LATERAL (

						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from v_DrugOstat DOA 

							inner join v_ReceptFinance ReceptFinance  on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id

								and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							and DOA.Drug_id = :Drug_id
					) DrugOstat ON true
				where COALESCE(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2

					" . (!in_array(getRegionNick(), array('saratov', 'ufa', 'perm')) ? "and COALESCE(DrugOstat.DrugOstat_Kolvo, 0) > 0" : "") . "

					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
					{$filter}

				union all

					select
						OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
						'Остатки на аптечном складе' as \"OrgFarmacy_Name\",
						'' as \"OrgFarmacy_HowGo\",
						YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
						(substring(COALESCE(DrugOstat.DrugOstat_Kolvo::text, ''),18,2))::numeric as \"DrugOstat_Kolvo\",

						1 as \"sort\"
						{$fields}
					from v_OrgFarmacy OrgFarmacy 

						inner join v_Org Org  on Org.Org_id = OrgFarmacy.Org_id

						LEFT JOIN LATERAL (

							select
								SUM(D2.DrugOstat_Kolvo) as DrugOstat_Kolvo
							from v_DrugOstat D2 

								inner join v_ReceptFinance RF  on RF.ReceptFinance_id = D2.ReceptFinance_id

									and RF.ReceptFinance_Code = :ReceptFinance_Code
							where D2.Drug_id = 
                            :Drug_id
								and D2.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
							group by D2.OrgFarmacy_id
						) DrugOstat ON true
						left join OrgFarmacyIndex  on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id

							and OrgFarmacyIndex.Lpu_id = :Lpu_id
						left join YesNo  on YesNo.YesNo_id = COALESCE(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)


						left join Address PAddr on PAddr.Address_id = Org.PAddress_id
					where
						OrgFarmacy.OrgFarmacy_id = 1
						" . ($data['session']['region']['nick'] != 'saratov' ? "and COALESCE(DrugOstat.DrugOstat_Kolvo, 0) > 0" : "") . "


					order by
						\"sort\", \"DrugOstat_Kolvo\" desc, \"OrgFarmacy_Name\"
			";

			$queryParams['Drug_id'] = $data['Drug_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$queryParams['ReceptFinance_Code'] = $data['ReceptFinance_Code'];
		}
		//echo getDebugSQL($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Возвращяет список рецептов
     */
	function loadEvnReceptList($data) {
		$filter = '';

		$filter .= ' and ER.Lpu_id = :Lpu_id';
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if ( !empty($data['EvnRecept_pid']) ) {
			$filter .= ' and ER.EvnRecept_pid = :EvnRecept_pid';
			$queryParams['EvnRecept_pid'] = $data['EvnRecept_pid'];
		}

		if ( !empty($data['Person_id']) ) {
			$filter .= ' and ER.Person_id = :Person_id';
			$queryParams['Person_id'] = $data['Person_id'];
		}

		$query = "
			select
				ER.EvnRecept_id as \"EvnRecept_id\",
				ER.EvnRecept_pid as \"EvnRecept_pid\",
				ER.LpuSection_id as \"LpuSection_id\",
				ER.MedPersonal_id as \"MedPersonal_id\",
				ER.Person_id as \"Person_id\",
				ER.PersonEvn_id as \"PersonEvn_id\",
				ER.Server_id as \"Server_id\",
				ER.ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\",
				to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(COALESCE(Drug.Drug_Name, ER.EvnRecept_ExtempContents)) as \"Drug_Name\",

				RTRIM(ER.EvnRecept_Ser) as \"EvnRecept_Ser\",
				RTRIM(ER.EvnRecept_Num) as \"EvnRecept_Num\"
			from v_EvnRecept ER 

				left join v_Drug Drug  on Drug.Drug_id = ER.Drug_id

				inner join v_MedPersonal MP  on MP.MedPersonal_id = ER.MedPersonal_id

					and MP.Lpu_id = ER.Lpu_id
			where (1 = 1)
				" . $filter . "
			order by ER.EvnRecept_setDT
		";
		$result = $this->db->query(
			$query,
			$queryParams
		);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Сохранение рецепта
     */
	function saveEvnRecept($data) {
		// Сохранение нового рецепта
		if ( empty($data['EvnRecept_id']) ) {
			$action = 'ins';
		}
		else if ( 0 < $data['EvnRecept_id'] ) {
			$action = 'upd';
		}
		else {
			return array(array('success' => false, 'Error_Msg' => 'Неверное значение идентификатора рецепта'));
		}

		if (getRegionNick() == 'kz' && empty($data['EvnRecept_Ser'])) {
			$data['EvnRecept_Ser'] = '101';
		}

		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsAll($data);

		// Проверка на уникальность серии и номера рецепта
		$unique_ser_num = ($this->getRegionNick() == 'ufa' || $options['recepts']['unique_ser_num'] === true || $options['recepts']['unique_ser_num'] == '1' || $options['recepts']['unique_ser_num'] == 'true');
		if ($this->getRegionNick() != 'kz' && $unique_ser_num) {
			$check_recept_ser_num = $this->checkReceptSerNum($data);

			if ( $check_recept_ser_num == -1 ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта'));
			}
			else if ( $check_recept_ser_num > 0 ) {
				return array(array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее'));
			}
		}

		$this->db->trans_begin();

		if (getRegionNick() == 'perm' && !empty($data['OrgFarmacy_id'])) {
			// сохраним данные о любимой аптеке
			$OrgFarmacyPerson_id = $this->getFirstResultFromQuery("
				select 
					OrgFarmacyPerson_id as \"OrgFarmacyPerson_id\"
				from
					v_OrgFarmacyPerson 

				where
					Person_id = :Person_id
    			limit 1
			", array(
				'Person_id' => $data['Person_id']
			), true);

			$proc = "p_OrgFarmacyPerson_upd";
			if (empty($OrgFarmacyPerson_id)) {
				// если ещё нет, то добавим
				$proc = "p_OrgFarmacyPerson_ins";
			}

			$this->queryResult("
				select OrgFarmacyPerson_id as \"OrgFarmacyPerson_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from {$proc}(
					OrgFarmacyPerson_id := :OrgFarmacyPerson_id,
					Person_id := :Person_id,
					OrgFarmacy_id := :OrgFarmacy_id,
					Server_id := :Lpu_id,
					pmUser_id := :pmUser_id);


			", array(
				'OrgFarmacyPerson_id' => $OrgFarmacyPerson_id,
				'Lpu_id' => $data['Lpu_id'],
				'OrgFarmacy_id' => $data['OrgFarmacy_id'],
				'Person_id' => $data['Person_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$DrugComplexMnn_id = null;
		$WhsDocumentCostItemType_id = null;
		$DrugFinance_id = null;
		if ($this->getRegionNick() == 'perm') {
			$query = "
				select 
					rlsDrug.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				from
					v_Drug Drug 

					inner join rls.v_DrugNomen DN  on DN.DrugNomen_Code = cast(Drug.Drug_CodeG as varchar(20))

					inner join rls.v_Drug rlsDrug  on rlsDrug.Drug_id = DN.Drug_id

				where
					Drug.Drug_id = :Drug_id
				limit 1
			";
			$params = array('Drug_id' => $data['Drug_id']);
			$resp = $this->queryResult($query, $params);
			if (!$this->isSuccessful($resp)) {
				//$this->db->trans_rollback();
				//return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении комплексного МНН'));
			} else if (count($resp) == 1) {
				$DrugComplexMnn_id = $resp[0]['DrugComplexMnn_id'];
			}
		}

		$data['EvnRecept_IsPrinted'] = null;
		// если на бланке, считаем что распечатан
		if ($data['ReceptType_id'] == 1) {
			$data['EvnRecept_IsPrinted'] = 2;
		}

		$query = "
			select EvnRecept_id as \"EvnRecept_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_EvnRecept_" . $action . "(
				EvnRecept_id := :EvnRecept_id,
				EvnRecept_pid := :EvnRecept_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnRecept_setDT := :EvnRecept_setDate,
				EvnRecept_Num := :EvnRecept_Num,
				EvnRecept_Ser := :EvnRecept_Ser,
				EvnRecept_Price := :Drug_Price,
				Diag_id := :Diag_id,
				ReceptDiscount_id := :ReceptDiscount_id,
				ReceptFinance_id := :ReceptFinance_id,
				ReceptValid_id := :ReceptValid_id,
				PersonPrivilege_id := :PersonPrivilege_id,
				PrivilegeType_id := :PrivilegeType_id,
				EvnRecept_IsKEK := :Drug_IsKEK,
				EvnRecept_Kolvo := :EvnRecept_Kolvo,
				MedPersonal_id := :MedPersonal_id,
				LpuSection_id := :LpuSection_id,
				Drug_id := :Drug_id,
				DrugComplexMnn_id := :DrugComplexMnn_id,
				WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
				DrugFinance_id := :DrugFinance_id,
				ReceptForm_id := :ReceptForm_id,
				ReceptType_id := :ReceptType_id,
				EvnRecept_IsMnn := :Drug_IsMnn,
				EvnRecept_Signa := :EvnRecept_Signa,
				EvnRecept_IsDelivery := :EvnRecept_IsDelivery,
				OrgFarmacy_id := :OrgFarmacy_id,
				EvnRecept_IsNotOstat := :EvnRecept_IsNotOstat,
				DrugRequestRow_id := :DrugRequestRow_id,
				EvnRecept_ExtempContents := :EvnRecept_ExtempContents,
				EvnRecept_IsExtemp := :EvnRecept_IsExtemp,
				EvnRecept_Is7Noz := :EvnRecept_Is7Noz,
				EvnRecept_IsPrinted := :EvnRecept_IsPrinted,
				EvnRecept_VKProtocolNum := :EvnRecept_VKProtocolNum,
				EvnRecept_VKProtocolDT := :EvnRecept_VKProtocolDT,
				CauseVK_id := :CauseVK_id,
				PrescrSpecCause_id := :PrescrSpecCause_id,
				ReceptUrgency_id := :ReceptUrgency_id,
				EvnRecept_IsExcessDose := :EvnRecept_IsExcessDose,
				pmUser_id := :pmUser_id);

		";

		$params = array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'EvnRecept_pid' => $data['EvnRecept_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnRecept_setDate' => $data['EvnRecept_setDate'],
			'EvnRecept_Num' => $data['EvnRecept_Num'],
			'EvnRecept_Ser' => $data['EvnRecept_Ser'],
			'Drug_Price'	=> $data['Drug_Price'],
			'Diag_id' => $data['Diag_id'],
			'ReceptDiscount_id' => $data['ReceptDiscount_id'],
			'ReceptFinance_id' => $data['ReceptFinance_id'],
			'ReceptValid_id' => $data['ReceptValid_id'],
			'PersonPrivilege_id' => $data['PersonPrivilege_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Drug_IsKEK' => $data['Drug_IsKEK'],
			'EvnRecept_Kolvo' => $data['EvnRecept_Kolvo'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Drug_id' => $data['Drug_id'],
			'DrugComplexMnn_id' => $DrugComplexMnn_id,
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'DrugFinance_id' => $DrugFinance_id,
			'EvnRecept_Signa' => $data['EvnRecept_Signa'],
			'EvnRecept_IsDelivery' => ($data['EvnRecept_IsDelivery']=='on') ? '2' : '1',
			'ReceptForm_id' => $data['ReceptForm_id'],
			'ReceptType_id' => $data['ReceptType_id'],
			'Drug_IsMnn' => $data['Drug_IsMnn'],
			'OrgFarmacy_id' => $data['OrgFarmacy_id'],
			'EvnRecept_IsNotOstat' => $data['EvnRecept_IsNotOstat'],
			'DrugRequestRow_id' => $data['DrugRequestRow_id'],
			'EvnRecept_ExtempContents' => $data['EvnRecept_ExtempContents'],
			'EvnRecept_IsExtemp' => $data['EvnRecept_IsExtemp'],
			'EvnRecept_Is7Noz' => $data['EvnRecept_Is7Noz'],
			'EvnRecept_IsPrinted' => $data['EvnRecept_IsPrinted'],
			'EvnRecept_VKProtocolNum' => $data['EvnRecept_VKProtocolNum'],
			'EvnRecept_VKProtocolDT' => $data['EvnRecept_VKProtocolDT'],
			'CauseVK_id' => $data['CauseVK_id'],
			'PrescrSpecCause_id' => !empty($data['PrescrSpecCause_id']) ? $data['PrescrSpecCause_id'] : null,
			'ReceptUrgency_id' => !empty($data['ReceptUrgency_id']) ? $data['ReceptUrgency_id'] : null,
			'EvnRecept_IsExcessDose' => !empty($data['EvnRecept_IsExcessDose']) ? 2 : 1,
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 || empty($response[0]['EvnRecept_id']) ) {
			$this->db->trans_rollback();
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$data['EvnRecept_id'] = $response[0]['EvnRecept_id'];
		
		if (getRegionNick() == 'msk' && $options['globals']['use_external_service_for_recept_num'] == 1) {
			//получим данные по серии и номеру
			$query = "
				select
					ReceptFreeNum_id as \"ReceptFreeNum_id\",
					Lpu_id as \"Lpu_id\",
					LpuUnit_id as \"LpuUnit_id\",
					ReceptFreeNum_Ser as \"ReceptFreeNum_Ser\",
					ReceptFreeNum_Num as \"ReceptFreeNum_Num\",
					EvnRecept_id as \"EvnRecept_id\"
				from r50.ReceptFreeNum
				where 1=1
					and ReceptFreeNum_Num = :EvnRecept_Num
					and ReceptFreeNum_Ser = :EvnRecept_Ser
			";
			$res = $this->getFirstRowFromQuery($query, $data);
			
			if (!empty($res['ReceptFreeNum_Num']) && !empty($res['ReceptFreeNum_Ser'])) {
				//серия и номер уже сохранены, надо только добавить ссылку на рецепт
				$res['pmUser_id'] = $data['pmUser_id'];
				$res['EvnRecept_id'] = $data['EvnRecept_id'];
				$this->queryResult("
					select
						ReceptFreeNum_id as \"ReceptFreeNum_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from r50.p_ReceptFreeNum_upd (
						ReceptFreeNum_id := :ReceptFreeNum_id,
						Lpu_id := :Lpu_id,
						LpuUnit_id := :LpuUnit_id,
						ReceptFreeNum_Ser := :ReceptFreeNum_Ser,
						ReceptFreeNum_Num := :ReceptFreeNum_Num,
						EvnRecept_id := :EvnRecept_id,
						pmUser_id := :pmUser_id
					)
				", $res);
			}
			else {
				$this->db->trans_rollback();
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении связи рецепта и свободного номера из таблицы свободных номеров'));
			}
		}
		
		// Повторно проверяем на уникальность серии и номера рецепта
		// https://redmine.swan.perm.ru/issues/25626
		if ( $this->getRegionNick() == 'ufa' || $options['recepts']['unique_ser_num'] === true || $options['recepts']['unique_ser_num'] == '1' || $options['recepts']['unique_ser_num'] == 'true' ) {
			$check_recept_ser_num = $this->checkReceptSerNum($data);

			if ( $check_recept_ser_num == -1 ) {
				$this->db->trans_rollback();
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта'));
			}
			else if ( $check_recept_ser_num > 0 ) {
				$this->db->trans_rollback();
				return array(array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее'));
			}
		}

		$this->db->trans_commit();

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->saveApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnRecept',
			'ApprovalList_ObjectId' => $data['EvnRecept_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return $response;
	}

    /**
     * Сохранение дополнительной заявки
     */
    function saveDrugRequestDop($data){

        $query = "
			select DrugRequestDop_id as \"DrugRequestDop_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_DrugRequestDop_ins(
				DrugRequestDop_id          := 0,
				DrugRequestDop_setDT       := :DrugRequestDop_setDT,
				DrugRequestPeriod_id       := :DrugRequestPeriod_id,
				MedStaffFact_id            := :MedStaffFact_id,
				Diag_id                    := :Diag_id,
				DrugRequestDop_IsMedical   := :DrugRequestDop_IsMedical,
				Person_id                  := :Person_id,
                PrivilegeType_id           := :PrivilegeType_id,
				DrugProtoMnn_id            := :DrugProtoMnn_id,
				Drug_id                    := :Drug_id,
				DrugRequestDop_PackCount   := :DrugRequestDop_PackCount,
				DrugFinance_id             := :DrugFinance_id,
				pmUser_id                  := :pmUser_id);

		";
        $this->db->trans_begin();
        $result = $this->db->query($query, array(
            'DrugRequestDop_setDT' => $data['DrugRequestDop_setDT'],
            'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
            'MedStaffFact_id' => $data['MedStaffFact_id'],
            'Diag_id' => $data['Diag_id'],
            'DrugRequestDop_IsMedical' => $data['DrugRequestDop_IsMedical'],
            'Person_id' => $data['Person_id'],
            'PrivilegeType_id' => $data['PrivilegeType_id'],
            'DrugProtoMnn_id' => $data['DrugProtoMnn_id'],
            'Drug_id' => $data['Drug_id'],
            'DrugRequestDop_PackCount' => $data['DrugRequestDop_PackCount'],
            'DrugFinance_id' => $data['DrugFinance_id'],
            'pmUser_id' => $data['pmUser_id']
        ));

        if ( !is_object($result) ) {
            $this->db->trans_rollback();
            return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }

        $response = $result->result('array');

        if ( !is_array($response) || count($response) == 0 || empty($response[0]['DrugRequestDop_id']) ) {
            $this->db->trans_rollback();
            return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        $this->db->trans_commit();

        return $response;
    }

	/**
	 * Генерирует по переданным данным фильтр по полю "7 нозологий". Фильтр имеет региональные различия.
	 */
	function genEvnReceptListIs7NozFilter($data, &$filters, &$queryParams, &$join) {
		If (ArrayVal($data,'EvnRecept_Is7Noz')!='') {
			$filters[] = "COALESCE(EvnRecept.EvnRecept_Is7Noz, 1) = :EvnRecept_Is7Noz";

			$queryParams['EvnRecept_Is7Noz'] = $data['EvnRecept_Is7Noz'];
		}
	}

	/**
	 * Генерирует по переданным данным набор фильтров и джойнов
	 */
  	function genEvnReceptListFilters($data, &$filters, &$queryParams, &$join) {

		// для минздрава и если не задан ЛПУ не добавляем фильтр по ЛПУ
		if ( !isMinZdravOrNotLpu() || (isMinZdravOrNotLpu() && $data['SearchedLpu_id'] != '') )
		{
			$filters[] = "EvnRecept.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		// 1. Основной фильтр
		If (ArrayVal($data,'Person_Surname')!='') {
			$filters[] = "Person.Person_Surname iLIKE :Person_Surname";

			$queryParams['Person_Surname'] = $data['Person_Surname'].'%';
		}
		If (ArrayVal($data,'Person_Firname')!='') {
			$filters[] = "Person.Person_Firname iLIKE :Person_Firname";

			$queryParams['Person_Firname'] = $data['Person_Firname'].'%';
		}
		If (ArrayVal($data,'Person_Secname')!='') {
			$filters[] = "Person.Person_Secname iLIKE :Person_Secname";

			$queryParams['Person_Secname'] = $data['Person_Secname'].'%';
		}
		If ( isset($data['Person_BirthDay'][0]) ) {
			$filters[] = "Person.Person_BirthDay >= :Person_BirthdayStart";
			$queryParams['Person_BirthdayStart'] = $data['Person_BirthDay'][0];
		}
		If ( isset($data['Person_BirthDay'][1]) ) {
			$filters[] = "Person.Person_BirthDay <= :Person_BirthdayEnd";
			$queryParams['Person_BirthdayEnd'] = $data['Person_BirthDay'][1];
		}
		If (ArrayVal($data,'PersonCard_NumCard')!='') {
			$join .= "
			inner join v_PersonCard_all PersonCard  on

			(PersonCard.Person_id = Person.Person_id)
			and PersonCard.PersonCard_Code = :PersonCard_NumCard
			";
			$queryParams['PersonCard_NumCard'] = $data['PersonCard_NumCard'];
		}
		If ( isset($data['EvnRecept_setDate'][0]) ) {
			$filters[] = "evnRecept_all.EvnRecept_setDate >= :EvnRecept_setDateStart";
			$queryParams['EvnRecept_setDateStart'] = $data['EvnRecept_setDate'][0];
		}
		If ( isset($data['EvnRecept_setDate'][1]) ) {
			$filters[] = "evnRecept_all.EvnRecept_setDate <= :EvnRecept_setDateEnd";
			$queryParams['EvnRecept_setDateEnd'] = $data['EvnRecept_setDate'][1];
		}
		If (ArrayVal($data,'PrivilegeType_id')!='') {
			$filters[] = "evnRecept_all.PrivilegeType_id = :PrivilegeType_id";
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
		}
		If (ArrayVal($data,'SubCategoryPrivType_id')!='' && getRegionNick() == 'kz') {
			$filters[] = "exists(
				select *
				from r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT

				inner join v_PersonPrivilege PP  on PP.PersonPrivilege_id = PPSCPT.PersonPrivilege_id

				where PP.Person_id = evnRecept_all.Person_id
				and PP.PrivilegeType_id = evnRecept_all.PrivilegeType_id
				and PPSCPT.SubCategoryPrivType_id = :SubCategoryPrivType_id
			)";
			$queryParams['SubCategoryPrivType_id'] = $data['SubCategoryPrivType_id'];
		}
		If (ArrayVal($data,'Person_SNILS')!='') {
			$filters['Person_SNILS_Person'] = "Person.Person_Snils = :Person_SNILS";
			$filters['Person_SNILS_Recept'] = "EvnRecept.Person_Snils = :Person_SNILS";
			$queryParams['Person_SNILS'] = $data['Person_SNILS'];
		}
		If (ArrayVal($data,'Person_Inn')!='') {
			$filters['Person_Inn'] = "Person.Person_Inn = :Person_Inn";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}
		/*If (ArrayVal($data,'Person_IsRefuse')!='')
        $filters[]="Person_IsRefuse = '{$data['Person_IsRefuse']}'";*/
		If (ArrayVal($data,'ER_MedPersonal_id')!='') {
			$filters['MedPersonal_id'] = "EvnRecept.MedPersonal_id = :ER_MedPersonal_id";
			$filters['MedPersonalRec_id'] = "EvnRecept.MedPersonalRec_id = :ER_MedPersonal_id";
			$queryParams['ER_MedPersonal_id'] = $data['ER_MedPersonal_id'];
		}
		If (ArrayVal($data,'ER_Diag_Code_From')!='' || ArrayVal($data,'ER_Diag_Code_To')!='') {
			/*$join .= "
			inner join v_Diag Diag  on EvnRecept.Diag_id = Diag.Diag_id

			";*/
			If (ArrayVal($data,'ER_Diag_Code_From')!='')
				$filters[] = "Diag.Diag_Code >= :ER_Diag_Code_From";
			$queryParams['ER_Diag_Code_From'] = $data['ER_Diag_Code_From'];
			If (ArrayVal($data,'ER_Diag_Code_To')!='')
				$filters[] = "Diag.Diag_Code <= :ER_Diag_Code_To";
			$queryParams['ER_Diag_Code_To'] = $data['ER_Diag_Code_To'];
		}

		// 2. Пациент
		If (ArrayVal($data,'PersonSex_id')!='') {
			$filters[] = "Person.Sex_id = :PersonSex_id";
			$queryParams['PersonSex_id'] = $data['PersonSex_id'];
		}
		If (ArrayVal($data,'SocStatus_id')!='') {
			$filters[] = "Person.SocStatus_id = :SocStatus_id";
			$queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}
		If (ArrayVal($data,'PersonPrivilegeType_id')!='') {
			$join .= "
			inner join v_PersonPrivilege PersonPrivilege  on

			(Person.Person_id = PersonPrivilege.Person_id and PersonPrivilege.PrivilegeType_id = :PersonPrivilegeType_id)
			";
			$queryParams['PersonPrivilegeType_id'] = $data['PersonPrivilegeType_id'];
		}
		// Документ
		If (ArrayVal($data,'DocumentType_id')!='' || ArrayVal($data,'OrgDep_id')!='') {
			$join .= "
			inner join Document  on

				Person.Document_id = Document.Document_id
			";
			If (ArrayVal($data,'DocumentType_id')!='') {
				$join .= " and Document.DocumentType_id = :DocumentType_id ";
				$queryParams['DocumentType_id'] = $data['DocumentType_id'];
			}
			If (ArrayVal($data,'OrgDep_id')!='') {
				$join .= " and Document.OrgDep_id = :OrgDep_id ";
				$queryParams['OrgDep_id'] = $data['OrgDep_id'];
			}
		}
		// Полис
		If (ArrayVal($data,'OMSSprTerr_id')!='' || ArrayVal($data,'PolisType_id')!='' || ArrayVal($data,'OrgSMO_id')!='') {
			$join .= "
			inner join v_Polis Polis  on

				Person.Polis_id = Polis.Polis_id
			";
			If (ArrayVal($data,'OMSSprTerr_id')!='') {
				$join .= " and Polis.OMSSprTerr_id = :OMSSprTerr_id ";
				$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
			}
			If (ArrayVal($data,'PolisType_id')!='') {
				$join .= " and Polis.PolisType_id = :PolisType_id ";
				$queryParams['PolisType_id'] = $data['PolisType_id'];
			}
			If (ArrayVal($data,'OrgSMO_id')!='') {
				$join .= " and Polis.OrgSMO_id = :OrgSMO_id ";
				$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];
			}
		}
		// Место работы, учебы
		If (ArrayVal($data,'Org_id')!='' || ArrayVal($data,'Post_id')!='') {
			$join .= "
			inner join v_Job Job  on

				Person.Job_id = Job.Job_id
			";
			If (ArrayVal($data,'Org_id')!='') {
				$join .= " and Job.Org_id = :Org_id ";
				$queryParams['Org_id'] = $data['Org_id'];
			}
			If (ArrayVal($data,'Post_id')!='') {
				$join .= " and Job.Post_id = :Post_id ";
				$queryParams['Post_id'] = $data['Post_id'];
			}
		}

		// 3. Адрес
		if (($data['KLRgn_id'] > 0) || ($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) || ($data['KLStreet_id'] > 0) || (strlen($data['Address_House']) > 0))
		{
			$join .= " inner join Address  on Address.Address_id = Person.UAddress_id";


			if ($data['KLRgn_id'] > 0) {
				$filters[] = "Address.KLRgn_id = :KLRgn_id";
				$queryParams['KLRgn_id'] = $data['KLRgn_id'];
			}

			if ($data['KLSubRgn_id'] > 0) {
				$filters[] = "Address.KLSubRgn_id = :KLSubRgn_id";
				$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
			}

			if ($data['KLCity_id'] > 0) {
				$filters[] = "Address.KLCity_id = :KLCity_id";
				$queryParams['KLCity_id'] = $data['KLCity_id'];
			}

			if ($data['KLTown_id'] > 0) {
				$filters[] = "Address.KLTown_id = :KLTown_id";
				$queryParams['KLTown_id'] = $data['KLTown_id'];
			}

			if ($data['KLStreet_id'] > 0) {
				$filters[] = "Address.KLStreet_id = :KLStreet_id";
				$queryParams['KLStreet_id'] = $data['KLStreet_id'];
			}

			if (strlen($data['Address_House']) > 0) {
				$filters[] = "Address.Address_House = :Address_House";
				$queryParams['Address_House'] = $data['Address_House'];
			}
		}

		// 4. Рецепт
		If (ArrayVal($data,'ReceptFinance_id')!='') {
			$filters[] = "EvnRecept.ReceptFinance_id = :ReceptFinance_id";
			$queryParams['ReceptFinance_id'] = $data['ReceptFinance_id'];
		}
		If (ArrayVal($data,'WhsDocumentCostItemType_id')!='') {
			$filters[] = "EvnRecept.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		If (ArrayVal($data,'ReceptDiscount_id')!='') {
			$filters[] = "evnRecept_all.ReceptDiscount_id = :ReceptDiscount_id";
			$queryParams['ReceptDiscount_id'] = $data['ReceptDiscount_id'];
		}
		If (ArrayVal($data,'ReceptValid_id')!='') {
			$filters[] = "evnRecept_all.ReceptValid_id = :ReceptValid_id";
			$queryParams['ReceptValid_id'] = $data['ReceptValid_id'];
		}
		$this->genEvnReceptListIs7NozFilter($data, $filters, $queryParams, $join);

		// Рецепт
		If (ArrayVal($data,'EvnRecept_Num')!='') {
			$filters[] = "evnRecept_all.EvnRecept_Num = :EvnRecept_Num";
			$queryParams['EvnRecept_Num'] = $data['EvnRecept_Num'];
		}
		If (ArrayVal($data,'EvnRecept_Ser')!='') {
			$filters[] = "evnRecept_all.EvnRecept_Ser = :EvnRecept_Ser";
			$queryParams['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
		}

		/*
		// Удостоверение
		If (ArrayVal($data,'EvnUdost_Num')!='')
        $filters[]="EvnRecept_UdostNum = '{$data['EvnUdost_Num']}'";
		If (ArrayVal($data,'EvnUdost_Ser')!='')
        $filters[]="EvnRecept_UdostSer = '{$data['EvnUdost_Ser']}'";
         */

		if(!empty($data['receptLpuId'])){
			$filters[] = "evnRecept_all.Lpu_id = :receptLpuId";
			$queryParams['receptLpuId'] = $data['receptLpuId'];
		}
		if(!empty($data['LpuBuilding_id'])){
			$filters[] = "
				exists(select  1
					from v_MedStaffFact msf

					where msf.MedPersonal_id = evnRecept_all.MedPersonal_id and msf.LpuBuilding_id = :LpuBuilding_id
				)
			";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if(!empty($data['LpuSection_id'])){
			$filters[] = "
				exists(select 1
					from v_MedStaffFact msf

					where msf.MedPersonal_id = evnRecept_all.MedPersonal_id and msf.LpuSection_id = :LpuSection_id
				)
			";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		// Медикамент
		If (ArrayVal($data,'DrugMnn_id')!='') {
			$filters[] = "Drug.DrugMnn_id = :DrugMnn_id";
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
		}
		If (ArrayVal($data,'Drug_id')!='') {
			$filters[] = "Drug.Drug_id = :Drug_id";
			$queryParams['Drug_id'] = $data['Drug_id'];
		}

		If (ArrayVal($data,'EvnRecept_IsNotOstat')!='') {
			$filters[] = "COALESCE(EvnRecept.EvnRecept_IsNotOstat, 1) = :EvnRecept_IsNotOstat";

			$queryParams['EvnRecept_IsNotOstat'] = $data['EvnRecept_IsNotOstat'];
		}


		// 5. Пользователь
		If ( isset($data['EvnRecept_insDT'][0]) ) {
			$filters[] = "evnRecept_all.EvnRecept_insDT >= :EvnRecept_insDTStart";
			$queryParams['EvnRecept_insDTStart'] = $data['EvnRecept_insDT'][0];
		}
		If ( isset($data['EvnRecept_insDT'][1]) ) {
			$filters[] = "evnRecept_all.EvnRecept_insDT <= :EvnRecept_insDTEnd";
			$queryParams['EvnRecept_insDTEnd'] = $data['EvnRecept_insDT'][1];
		}
		If ( isset($data['EvnRecept_updDT'][0]) ) {
			$filters[] = "evnRecept_all.EvnRecept_updDT >= :EvnRecept_updDT";
			$queryParams['EvnRecept_updDT'] = $data['EvnRecept_updDT'][0];
		}
		If ( isset($data['EvnRecept_updDT'][1]) ) {
			$filters[] = "evnRecept_all.EvnRecept_updDT <= :EvnRecept_updDTEnd";
			$queryParams['EvnRecept_updDTEnd'] = $data['EvnRecept_updDT'][1];
		}
		If (ArrayVal($data,'pmUser_insID')!='') {
			$filters[] = "EvnRecept.pmUser_insID = :pmUser_insID";
			$queryParams['pmUser_insID'] = $data['pmUser_insID'];
		}
		If (ArrayVal($data,'pmUser_updID')!='') {
			$filters[] = "EvnRecept.pmUser_updID = :pmUser_updID";
			$queryParams['pmUser_updID'] = $data['pmUser_updID'];
		}

		// Тип рецепта
		If (ArrayVal($data,'ReceptType_id')!='') {
			$filters[] = "EvnRecept.ReceptType_id = :ReceptType_id";
			$queryParams['ReceptType_id'] = ArrayVal($data,'ReceptType_id');
		}

		// Аптека
		If (ArrayVal($data,'OrgFarmacy_id')!='') {
			if (ArrayVal($data,'OrgFarmacy_id') == -1) {
				$filters[] = "EvnRecept.OrgFarmacy_id is null";
			}
			else {
				$filters[] = "EvnRecept.OrgFarmacy_id = :OrgFarmacy_id";
				$queryParams['OrgFarmacy_id'] = ArrayVal($data,'OrgFarmacy_id');
			}
		}

		// Результат
		If (ArrayVal($data,'ReceptResult_id')!='') {
			$filters['EvnRecept_deleted'] = "COALESCE(evnRecept_all.EvnRecept_deleted,1) <> 2";

			switch (ArrayVal($data,'ReceptResult_id')) {
				case 1:
					//1. Было обращение - дата обращения определена
					$filters[] = "EvnRecept.EvnRecept_obrDT is not null";
                    break;
				case 2:
					//2. Не было обращения - дата обращения не определена
					$filters[] = "EvnRecept.EvnRecept_obrDT is null";
                    break;
				case 3:
					//3. Рецепт не отоварен - дата отоваривания не определена
					$filters[] = "EvnRecept.EvnRecept_otpDT is null";
                    break;
				case 4:
					//4. Рецепт отоварен - дата отоваривания определена
					$filters[] = "EvnRecept.EvnRecept_otpDT is not null";
                    break;
				case 5:
					//5. Рецепт отоварен без отсрочки - (имеются даты отоваривания и обращения и дата отоваривания = дате обращения) или (имеется дата отоваривания и нет даты обращения)
					$filters[] = "EvnRecept.EvnRecept_otpDT is not null and ((EvnRecept.EvnRecept_obrDT = EvnRecept.EvnRecept_otpDT) or EvnRecept.EvnRecept_obrDT is null)";
                    break;
				case 6:
					if (ArrayVal($data,'EvnRecept_otsDate') == '') { // не задана дата отсрочки
						//6. Рецепт отоварен после отсрочки - имеются даты отоваривания и обращения и дата отоваривания > даты обращения.
						$filters[] = "EvnRecept.EvnRecept_otpDT is not null and EvnRecept.EvnRecept_obrDT is not null and EvnRecept.EvnRecept_otpDT > EvnRecept.EvnRecept_obrDT";
					} else { // задана дата отсрочки
						/*Есть Дата обращения
						и
						Есть Дата отоваривания
						и
						Дата обращения <= Дата актуальности отсрочки
						и
						Дата актуальности отсрочки < Дата отоваривания.*/
						$filters[] = "
							EvnRecept.EvnRecept_obrDT is not null
							and EvnRecept.EvnRecept_otpDT is not null
							and EvnRecept.EvnRecept_obrDT <= cast(:EvnRecept_otsDate as date)
							and cast(:EvnRecept_otsDate as date) < EvnRecept.EvnRecept_otpDT";
						$queryParams['EvnRecept_otsDate'] = ArrayVal($data,'EvnRecept_otsDate');
					}
                    break;
				case 7:
					if (ArrayVal($data,'EvnRecept_otsDate') == '') { // не задана дата отсрочки
						//7. Рецепт отсрочен - имеется дата обращения, нет даты отоваривания, рецепт не просрочен и нет отказа.
						/*$filters[] = "
                        EvnRecept.EvnRecept_otpDT is null
                        and EvnRecept.EvnRecept_obrDT is not null
                        and dbo.tzGetDate() <= case
                        when RV.ReceptValid_Code = 1 then dateadd(month, 1, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 2 then dateadd(month, 3, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 3 then dateadd(day, 14, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 4 then dateadd(day, 5, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 5 then dateadd(month, 2, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 7 then dateadd(day, 10, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 8 then dateadd(day, 60, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 9 then dateadd(day, 30, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 10 then dateadd(day, 90, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 11 then dateadd(day, 15, evnRecept_all.EvnRecept_setDate)
                        end
                        and (EvnRecept.ReceptDelayType_id != 3 or EvnRecept.ReceptDelayType_id is null)";*/
						// Выводятся рецепты, у которых статус рецепта Отсрочен, указана дата обращения в аптеку, а дата обеспечения не указана.
						// @task https://redmine.swan.perm.ru//issues/112202
						$filters[] = "
							EvnRecept.ReceptDelayType_id = 2
							and EvnRecept.EvnRecept_otpDT is null
							and EvnRecept.EvnRecept_obrDT is not null
						";
					} else { // задана дата отсрочки
						/*Есть Дата обращения
                        и
                        (если есть Дата отоваривания, то
                        Дата актуальности отсрочки < Дата отоваривания)
                        и
                        Дата обращения <= Дата актуальности отсрочки
                        и
                        Дата актуальности отсрочки  <= Дата выписки + Срок действия рецепта
                        и
                        нет отказа.
                         */
						/*$filters[] = "
                        EvnRecept.EvnRecept_obrDT is not null
                        and ((EvnRecept.EvnRecept_otpDT is not null and :EvnRecept_otsDate < EvnRecept.EvnRecept_otpDT) or EvnRecept.EvnRecept_otpDT is null)
                        and EvnRecept.EvnRecept_obrDT < :EvnRecept_otsDate
                        and :EvnRecept_otsDate <= case
                        when RV.ReceptValid_Code = 1 then dateadd(month, 1, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 2 then dateadd(month, 3, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 3 then dateadd(day, 14, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 4 then dateadd(day, 5, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 5 then dateadd(month, 2, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 7 then dateadd(day, 10, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 8 then dateadd(day, 60, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 9 then dateadd(day, 30, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 10 then dateadd(day, 90, evnRecept_all.EvnRecept_setDate)
                        when RV.ReceptValid_Code = 11 then dateadd(day, 15, evnRecept_all.EvnRecept_setDate)
                        end
                        and (EvnRecept.ReceptDelayType_id != 3 or EvnRecept.ReceptDelayType_id is null)";*/
						/*
                        @task https://redmine.swan.perm.ru//issues/112202
                        - Дата обращения в аптеку больше или равна дате актуальности отсрочки.
                        - И статус рецепта:
                        - Отсрочен И дата обеспечения не указана.
                        - Или Обеспечен И дата обеспечения больше даты актуальности отсрочки.
                        - Или Снят с обслуживания И дата акта снятия с отсрочки больше даты актуальности отсрочки.
                         */
						$filters[] = "
							EvnRecept.EvnRecept_obrDT >= cast(:EvnRecept_otsDate as date)
							and (
								(EvnRecept.ReceptDelayType_id = 2 and EvnRecept.EvnRecept_otpDT is null)
								or (EvnRecept.ReceptDelayType_id = 1 and EvnRecept.EvnRecept_otpDT > cast(:EvnRecept_otsDate as date))
								or (EvnRecept.ReceptDelayType_id = 5 and WDUARL.WhsDocumentUc_Date > cast(:EvnRecept_otsDate as date))
							)
						";
						$join .= "
							LEFT JOIN LATERAL (

								select t3.WhsDocumentUc_Date
								from v_WhsDocumentUcActReceptList t1

									inner join v_WhsDocumentUcActReceptOut t2  on t2.WhsDocumentUcActReceptOut_id = t1.WhsDocumentUcActReceptOut_id

									inner join v_WhsDocumentUc t3  on t3.WhsDocumentUc_id = t2.WhsDocumentUc_id

								where t1.EvnRecept_id = EvnRecept.EvnRecept_id
								limit 1
							) WDUARL ON true
						";
						$queryParams['EvnRecept_otsDate'] = ArrayVal($data,'EvnRecept_otsDate');
					}
                    break;
				case 8:
					//8. Рецепт просрочен - Текущая дата > Даты выписки + Срок действия рецепта и нет даты отоваривания
					$filters[] = "
						dbo.tzGetDate() >= case
							when RV.ReceptValid_Code = 1 then dateadd('month', 1, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 2 then dateadd('month', 3, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 3 then dateadd('day', 14, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 4 then dateadd('day', 5, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 5 then dateadd('month', 2, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 7 then dateadd('day', 10, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 8 then dateadd('day', 60, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 9 then dateadd('day', 30, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 10 then dateadd('day', 90, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 11 then dateadd('day', 15, evnRecept_all.EvnRecept_setDate)
						end
						and EvnRecept.EvnRecept_otpDT is null
					";
                    break;
				case 9:
					//9. Рецепт просрочен без обращения - Текущая дата > Даты выписки + Срок действия рецепта и нет даты отоваривания и нет даты обращения.
					$filters[] = "
						dbo.tzGetDate() >= case
							when RV.ReceptValid_Code = 1 then dateadd('month', 1, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 2 then dateadd('month', 3, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 3 then dateadd('day', 14, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 4 then dateadd('day', 5, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 5 then dateadd('month', 2, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 7 then dateadd('day', 10, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 8 then dateadd('day', 60, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 9 then dateadd('day', 30, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 10 then dateadd('day', 90, evnRecept_all.EvnRecept_setDate)
							when RV.ReceptValid_Code = 11 then dateadd('day', 15, evnRecept_all.EvnRecept_setDate)
						end
						and EvnRecept.EvnRecept_otpDT is null
						and EvnRecept.EvnRecept_obrDT is null
					";
                    break;
				case 10:
					if (ArrayVal($data,'EvnRecept_otsDate') == '') { // не задана дата отсрочки
						//10. Рецепт просрочен после отсрочки - Текущая дата > Даты выписки + Срок действия рецепта, нет даты отоваривания, есть дата обращения и нет отказа.
						$filters[] = "
							dbo.tzGetDate() >= case
								when RV.ReceptValid_Code = 1 then dateadd('month', 1, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 2 then dateadd('month', 3, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 3 then dateadd('day', 14, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 4 then dateadd('day', 5, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 5 then dateadd('month', 2, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 7 then dateadd('day', 10, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 8 then dateadd('day', 60, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 9 then dateadd('day', 30, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 10 then dateadd('day', 90, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 11 then dateadd('day', 15, evnRecept_all.EvnRecept_setDate)
							end
							and EvnRecept.EvnRecept_otpDT is null
							and EvnRecept.EvnRecept_obrDT is not null
							and EvnRecept.ReceptDelayType_id != 3
						";
					} else { // задана дата отсрочки
						/*Есть Дата обращения
						и
						Нет Даты отоваривания
						и
						Текущая дата > Дата выписки + Срок действия рецепта
						и
						Дата обращения <= Дата актуальности отсрочки
						и
						Дата актуальности отсрочки  <= Дата выписки + Срок действия рецепта
						и
						нет отказа.*/
						$filters[] = "
							EvnRecept.EvnRecept_obrDT is not null
							and EvnRecept.EvnRecept_otpDT is null
							and dbo.tzGetDate() >= case
								when RV.ReceptValid_Code = 1 then dateadd('month', 1, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 2 then dateadd('month', 3, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 3 then dateadd('day', 14, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 4 then dateadd('day', 5, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 5 then dateadd('month', 2, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 7 then dateadd('day', 10, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 8 then dateadd('day', 60, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 9 then dateadd('day', 30, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 10 then dateadd('day', 90, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 11 then dateadd('day', 15, evnRecept_all.EvnRecept_setDate)
							end
							and EvnRecept.EvnRecept_obrDT <= cast(:EvnRecept_otsDate as date)
							and :EvnRecept_otsDate <= case
								when RV.ReceptValid_Code = 1 then dateadd('month', 1, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 2 then dateadd('month', 3, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 3 then dateadd('day', 14, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 4 then dateadd('day', 5, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 5 then dateadd('month', 2, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 7 then dateadd('day', 10, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 8 then dateadd('day', 60, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 9 then dateadd('day', 30, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 10 then dateadd('day', 90, evnRecept_all.EvnRecept_setDate)
								when RV.ReceptValid_Code = 11 then dateadd('day', 15, evnRecept_all.EvnRecept_setDate)
							end
							and EvnRecept.ReceptDelayType_id != 3";
					}
                    break;
				case 11:
					//11. Отказ
					$filters[] = "EvnRecept.ReceptDelayType_id = 3";
                    break;
				case 12:
					//12. Удалённый МО
					$filters['EvnRecept_deleted'] = "evnRecept_all.EvnRecept_deleted = 2";
                    break;
				case 13:
					$filters[] = "EvnRecept.ReceptDelayType_id = 5";
                    break;
			}
		}

		// Несовпадения в рецептах
		If (ArrayVal($data,'ReceptMismatch_id')!='') {
			switch (ArrayVal($data,'ReceptMismatch_id')) {
				case 1:
					//1. Различные медикаменты
					$filters[] = "EvnRecept.Drug_oid is not null and EvnRecept.Drug_id != EvnRecept.Drug_oid";
                    break;
				case 2:
					//2. Различные количества
					$filters[''] = "EvnRecept_Okolvo is not null and EvnRecept_kolvo != EvnRecept_Okolvo";
                    break;
				case 3:
					//3. Различные аптеки
					$filters[] = "EvnRecept.OrgFarmacy_oid is not null and EvnRecept.OrgFarmacy_id != EvnRecept.OrgFarmacy_oid";
                    break;
				case 4:
					//4. Дата выписки больше даты обращения
					$filters[] = "EvnRecept.EvnRecept_obrDT is not null and evnRecept_all.EvnRecept_setDate is not null and evnRecept_all.EvnRecept_setDate > EvnRecept.EvnRecept_obrDT";
                    break;
			}
		}

		//Время обращения в аптеку с момента выписки от
		If (ArrayVal($data,'EvnRecept_obrTimeFrom')!='') {
			$filters[] = "datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_obrDT) >= :EvnRecept_obrTimeFrom";
			$queryParams['EvnRecept_obrTimeFrom'] = $data['EvnRecept_obrTimeFrom'];
		}
		//Время обращения в аптеку с момента выписки до
		If (ArrayVal($data,'EvnRecept_obrTimeTo')!='') {
			$filters[] = "datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_obrDT) <= :EvnRecept_obrTimeTo";
			$queryParams['EvnRecept_obrTimeTo'] = $data['EvnRecept_obrTimeTo'];
		}

		//Время отсрочки отоваривания рецепта от
		If (ArrayVal($data,'EvnRecept_otsTimeFrom')!='') {
			$filters[] = "datediff('day', EvnRecept.EvnRecept_obrDT, COALESCE(EvnRecept.EvnRecept_otpDT, dbo.tzGetDate())) >= :EvnRecept_otsTimeFrom";

			$queryParams['EvnRecept_otsTimeFrom'] = $data['EvnRecept_otsTimeFrom'];
		}
		//Время отсрочки отоваривания рецепта до
		If (ArrayVal($data,'EvnRecept_otsTimeTo')!='') {
			$filters[] = "datediff('day', EvnRecept.EvnRecept_obrDT, COALESCE(EvnRecept.EvnRecept_otpDT, dbo.tzGetDate())) <= :EvnRecept_otsTimeTo";

			$queryParams['EvnRecept_otsTimeTo'] = $data['EvnRecept_otsTimeTo'];
		}

		//Время отоваривания рецепта с момента выписки  от
		If (ArrayVal($data,'EvnRecept_otovTimeFrom')!='') {
			$filters[] = "datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_otpDT) >= :EvnRecept_otovTimeFrom";
			$queryParams['EvnRecept_otovTimeFrom'] = $data['EvnRecept_otovTimeFrom'];
		}
		//Время отоваривания рецепта с момента выписки  до
		If (ArrayVal($data,'EvnRecept_otovTimeTo')!='') {
			$filters[] = "datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_otpDT) <= :EvnRecept_otovTimeTo";
			$queryParams['EvnRecept_otovTimeTo'] = $data['EvnRecept_otovTimeTo'];
		}


		//Дата обращения
		If ( isset($data['EvnRecept_obrDate'][0]) ) {
			$filters[] = "EvnRecept.EvnRecept_obrDT >= cast(:EvnRecept_obrDateStart as timestamp)";
			$queryParams['EvnRecept_obrDateStart'] = $data['EvnRecept_obrDate'][0];
		}
		If ( isset($data['EvnRecept_obrDate'][1]) ) {
			$filters[] = "EvnRecept.EvnRecept_obrDT <= cast(:EvnRecept_obrDateEnd as timestamp)";
			$queryParams['EvnRecept_obrDateEnd'] = $data['EvnRecept_obrDate'][1];
		}
		//Дата отпуска
		If ( isset($data['EvnRecept_otpDate'][0]) ) {
			$filters[] = "EvnRecept.EvnRecept_otpDT >= cast(:EvnRecept_otpDateStart as timestamp)";
			$queryParams['EvnRecept_otpDateStart'] = $data['EvnRecept_otpDate'][0];
		}
		If ( isset($data['EvnRecept_otpDate'][1]) ) {
			$filters[] = "EvnRecept.EvnRecept_otpDT <= cast(:EvnRecept_otpDateEnd as timestamp)";
			$queryParams['EvnRecept_otpDateEnd'] = $data['EvnRecept_otpDate'][1];
		}

		//Протокол ВК
		If ( !empty($data['EvnRecept_IsKEK']) ) {
			$filters[] = "coalesce(evnRecept_all.EvnRecept_IsKEK, 1) = :EvnRecept_IsKEK";
			$queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
		}
		If ( !empty($data['EvnRecept_VKProtocolNum']) ) {
			$filters[] = "evnRecept_all.EvnRecept_VKProtocolNum = :EvnRecept_VKProtocolNum";
			$queryParams['EvnRecept_VKProtocolNum'] = $data['EvnRecept_VKProtocolNum'];
		}
		If ( !empty($data['EvnRecept_VKProtocolDT']) ) {
			$filters[] = "evnRecept_all.EvnRecept_VKProtocolDT = :EvnRecept_VKProtocolDT";
			$queryParams['EvnRecept_VKProtocolDT'] = $data['EvnRecept_VKProtocolDT'];
		}

		if ( $data['SearchedLpu_id'] > 0 && isMinZdravOrNotLpu() )
		{
			$queryParams['Lpu_id'] = $data['SearchedLpu_id'];
		}

		/*if ( isMinZdravOrNotLpu() )
		{
        $join .= "
        LEFT JOIN
        v_Lpu as Lpu  on Lpu.Lpu_id = EvnRecept.Lpu_id";

		}*/

		if ( $data['SearchedOMSSprTerr_Code'] > 0 && isMinZdravOrNotLpu() && $data['SearchedLpu_id'] == "" )
		{
			$filters[] = "((Lpu.Lpu_RegNomC2 = :OmsSprTerr_Code) or (1 = :OmsSprTerr_Code and Lpu.Lpu_RegNomC2 <= 7 ))";
			$queryParams['OmsSprTerr_Code'] = $data['SearchedOMSSprTerr_Code'];
		}

		if ( $data['Lpu_IsOblast_id'] > 0 && isMinZdravOrNotLpu() )
		{
			$filters[] = "Lpu.Lpu_IsOblast = :Lpu_IsOblast_id";
			$queryParams['Lpu_IsOblast_id'] = $data['Lpu_IsOblast_id'];
		}
	}

	/**
	 * Генерирует по переданным данным набор фильтров и джойнов экспертизы
	 */
	function genEvnReceptListExpertiseFilters($data, &$filters, &$queryParams, &$join) {
		//7. Экспертиза (Саратов, Псков)
		//Подключение RegistryRecept (RR)
		if ( ArrayVal($data,'ReceptStatusFLKMEK_id') > 0 || ArrayVal($data,'RegistryReceptErrorType_id') > 0 || ArrayVal($data,'AllowRegistryDataRecept') > 0
			|| ArrayVal($data,'RegistryDataRecept_IsReceived') > 0 || ArrayVal($data,'RegistryDataRecept_IsPaid') > 0 )
		{
			$join .= " left join {$this->schema}.v_RegistryRecept RR  on RR.ReceptOtov_id = EvnRecept.ReceptOtov_id";

		}

		if ( ArrayVal($data,'ReceptStatusType_id') > 0 ) {
			$join .= " left join v_ReceptStatusType RST  on RST.ReceptStatusType_id = EvnRecept.ReceptStatusType_id";

			if ( ArrayVal($data,'ReceptStatusType_id') == 2 ) {
				$filters[] = "RST.ReceptStatusType_Code = 2";
			} else {
				$filters[] = "RST.ReceptStatusType_Code <> 2";
			}
			$queryParams['ReceptStatusType_id'] = $data['ReceptStatusType_id'];
		}

		if ( ArrayVal($data,'ReceptStatusFLKMEK_id') > 0 ) {
			$filters[] = "RR.ReceptStatusFLKMEK_id = :ReceptStatusFLKMEK_id";
			$queryParams['ReceptStatusFLKMEK_id'] = $data['ReceptStatusFLKMEK_id'];
		}

		if ( ArrayVal($data,'RegistryReceptErrorType_id') > 0 ) {
			$join .= " left join {$this->schema}.v_RegistryReceptError RRE  on RRE.RegistryRecept_id = RR.RegistryRecept_id";

			$filters[] = "RRE.RegistryReceptErrorType_id = :RegistryReceptErrorType_id";
			$queryParams['RegistryReceptErrorType_id'] = $data['RegistryReceptErrorType_id'];
		}

		//Подключение RegistryDataRecept (RDR)
		if ( ArrayVal($data,'AllowRegistryDataRecept') == 2 || ArrayVal($data,'RegistryDataRecept_IsReceived') > 0 || ArrayVal($data,'RegistryDataRecept_IsPaid') > 0 ) {
			$join .= " left join {$this->schema}.v_RegistryDataRecept RDR  on RDR.RegistryRecept_id = RR.RegistryRecept_id";

			if ( ArrayVal($data,'AllowRegistryDataRecept') == 1 ) {
				$filters[] = "RDR.RegistryDataRecept_id is null";
			} else {
				$filters[] = "RDR.RegistryDataRecept_id is not null";
			}
		}

		if ( ArrayVal($data,'RegistryDataRecept_IsReceived') > 0 ) {
			if ( ArrayVal($data,'RegistryDataRecept_IsReceived') == 2 ) {
				$filters[] = "RDR.RegistryDataRecept_IsReceived = :RegistryDataRecept_IsReceived";
			} else {
				$filters[] = "RDR.RegistryDataRecept_IsReceived = :RegistryDataRecept_IsReceived or RDR.RegistryDataRecept_IsReceived is null";
			}
			$queryParams['RegistryDataRecept_IsReceived'] = $data['RegistryDataRecept_IsReceived'];
		}

		if ( ArrayVal($data,'RegistryDataRecept_IsPaid') > 0 ) {
			$join .= " left join {$this->schema}.v_Registry R  on R.Registry_id = RDR.Registry_id";

			$join .= " left join v_RegistryStatus RS  on RS.RegistryStatus_id = R.RegistryStatus_id";

			if (ArrayVal($data,'RegistryDataRecept_IsPaid') == 2) {
				$filters[] = "RS.RegistryStatus_SysNick iLIKE 'paid'";

			} else {
				$filters[] = "RS.RegistryStatus_SysNick not iLIKE 'paid'";

			}
		}
	}
	
	
	/**
     * Возвращает список рецептов по заданным фильтрам
     */
	function getEvnReceptList($data) {
		$this->writeToLog(ImplodeAssoc('=', '|', $data)."\r\n");
		$filters = array();
		$queryParams = array();
		$join='';
		
		$this->genEvnReceptListFilters($data, $filters, $queryParams, $join);

		if (ArrayVal($data,'PersonSex_id')!='' ||
			ArrayVal($data,'SocStatus_id')!='' ||
			ArrayVal($data,'DocumentType_id')!='' ||
			ArrayVal($data,'OrgDep_id')!='' ||
			ArrayVal($data,'OMSSprTerr_id')!='' ||
			ArrayVal($data,'PolisType_id')!='' ||
			ArrayVal($data,'OrgSMO_id')!='' ||
			ArrayVal($data,'Org_id')!='' ||
			ArrayVal($data,'Post_id')!='' ||
			($data['KLRgn_id'] > 0) ||
			($data['KLSubRgn_id'] > 0) ||
			($data['KLCity_id'] > 0) ||
			($data['KLTown_id'] > 0) ||
			($data['KLStreet_id'] > 0) ||
			(strlen($data['Address_House']) > 0)
		) {
			$tableName = 'v_Person_pfr';
		} else {
			$tableName = 'v_Person_FIO';
		}
		
		
		$sql = "
		SELECT
			EvnRecept.EvnRecept_id as \"EvnRecept_id\",
			EvnRecept.Person_id as \"Person_id\",
			EvnRecept.PersonEvn_id as \"PersonEvn_id\",
			EvnRecept.Server_id as \"Server_id\",
			rtrim(Person.Person_Surname) as \"Person_Surname\",
			rtrim(Person.Person_Firname) as \"Person_Firname\",
			rtrim(Person.Person_Secname) as \"Person_Secname\",
			to_char(Person.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

			to_char(EvnRecept_setDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

			rtrim(EvnRecept.EvnRecept_Ser) as \"EvnRecept_Ser\",
			rtrim(EvnRecept.EvnRecept_Num) as \"EvnRecept_Num\",
			ROUND(EvnRecept.EvnRecept_Kolvo, 3) as \"EvnRecept_Kolvo\",
			rtrim(MedPersonal.Person_Surname)||' '||rtrim(MedPersonal.Person_Firname)||' '||rtrim(MedPersonal.Person_Secname) as \"MedPersonal_Fio\",
			rtrim(Drug_Name) as \"Drug_Name\"
		FROM
			v_EvnRecept EvnRecept 

			LEFT JOIN {$tableName} Person  on Person.PersonEvn_id=EvnRecept.PersonEvn_id and Person.Server_id=EvnRecept.Server_id

			LEFT JOIN LATERAL (

				select Person_Surname, Person_Firname, Person_Secname
				from v_MedPersonal 

				where
					MedPersonal_id=EvnRecept.MedPersonal_id
					and Lpu_id = :Lpu_id
					and MedPersonal_Code is not null
                limit 1
			) MedPersonal ON true
			LEFT JOIN v_Drug Drug  on Drug.Drug_id=EvnRecept.Drug_id

			LEFT JOIN v_Diag Diag  on Diag.Diag_id=EvnRecept.Diag_id

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = EvnRecept.ReceptValid_id

			".$join."
		".ImplodeWhere($filters)." 
		ORDER BY Person.Person_Surname, Person.Person_Firname, Person.Person_Secname
		LIMIT 10000";

		$res=$this->db->query(
			$sql,
			$queryParams
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Возвращает общее количество рецептов по заданным фильтрам
	 */
	function getEvnReceptListCount($data) {
		$this->writeToLog(ImplodeAssoc('=', '|', $data)."\r\n");
		$filters = array();
		$queryParams = array();
		$join='';
		
		$this->genEvnReceptListFilters($data, $filters, $queryParams, $join);

		if (ArrayVal($data,'PersonSex_id')!='' ||
			ArrayVal($data,'SocStatus_id')!='' ||
			ArrayVal($data,'DocumentType_id')!='' ||
			ArrayVal($data,'OrgDep_id')!='' ||
			ArrayVal($data,'OMSSprTerr_id')!='' ||
			ArrayVal($data,'PolisType_id')!='' ||
			ArrayVal($data,'OrgSMO_id')!='' ||
			ArrayVal($data,'Org_id')!='' ||
			ArrayVal($data,'Post_id')!='' ||
			($data['KLRgn_id'] > 0) ||
			($data['KLSubRgn_id'] > 0) ||
			($data['KLCity_id'] > 0) ||
			($data['KLTown_id'] > 0) ||
			($data['KLStreet_id'] > 0) ||
			(strlen($data['Address_House']) > 0)
		) {
			$tableName = 'v_Person_pfr';
		} else {
			$tableName = 'v_Person_FIO';
		}
		
		
		$sql = "
		SELECT
			count(*) as \"cnt\"
		FROM
			v_EvnRecept EvnRecept 

			LEFT JOIN {$tableName} Person  on Person.PersonEvn_id=EvnRecept.PersonEvn_id and Person.Server_id=EvnRecept.Server_id

			LEFT JOIN v_Drug Drug  on Drug.Drug_id=EvnRecept.Drug_id

			LEFT JOIN v_Diag Diag  on Diag.Diag_id=EvnRecept.Diag_id

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = EvnRecept.ReceptValid_id

			".$join."
		".ImplodeWhere($filters);

		$res=$this->db->query(
			$sql,
			$queryParams
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	
	/**
	 * Возвращает список невалидных рецептов по заданным фильтрам
	 */
	function getEvnReceptInCorrectList($data)
	{
		//$this->writeToLog(ImplodeAssoc('=', '|', $data)."\r\n");
		$basefilters=array();
		$join='';
		$queryParams = array();

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		if ($data['Person_Surname']) $data['Person_Surname'] = rtrim($data['Person_Surname']);
		if ($data['Person_Secname']) $data['Person_Secname'] = rtrim($data['Person_Secname']);
		if ($data['Person_Firname']) $data['Person_Firname'] = rtrim($data['Person_Firname']);

		$this->genEvnReceptListFilters($data, $basefilters, $queryParams, $join);
		if (ArrayVal($data,'PersonSex_id')!='' ||
			ArrayVal($data,'SocStatus_id')!='' ||
			ArrayVal($data,'DocumentType_id')!='' ||
			ArrayVal($data,'OrgDep_id')!='' ||
			ArrayVal($data,'OMSSprTerr_id')!='' ||
			ArrayVal($data,'PolisType_id')!='' ||
			ArrayVal($data,'OrgSMO_id')!='' ||
			ArrayVal($data,'Org_id')!='' ||
			ArrayVal($data,'Post_id')!='' ||
			($data['KLRgn_id'] > 0) ||
			($data['KLSubRgn_id'] > 0) ||
			($data['KLCity_id'] > 0) ||
			($data['KLTown_id'] > 0) ||
			($data['KLStreet_id'] > 0) ||
			(strlen($data['Address_House']) > 0)
		) {
			$tableName = 'v_Person_pfr';
		} else {
			$tableName = 'v_Person_FIO';
		}

		// фильтр по ЛПУ в джойне медперсонала если под минздравом и не выбрано ЛПУ, то показывает по всем ЛПУ
		$med_personal_lpu_filter = " and Lpu_id = :Lpu_id ";

		if ( (($data['SearchedLpu_id'] == 0) || ($data['SearchedLpu_id'] == '')) && isMinZdravOrNotLpu() )
		{
			$med_personal_lpu_filter = " and Lpu_id = EvnRecept.Lpu_id ";
		}

		$resArray = array();

		//Поле Lpu_Nick отображается во всех случаях по #25293
		/*$get_lpu_nick = "";
		if ( isMinZdravOrNotLpu() )
        $get_lpu_nick = " rtrim(Lpu.Lpu_Nick) as Lpu_Nick, ";*/

		$sql = null;

		// если не суперадмин и у пользователя есть арм ТОУЗ и нет армов МЭК ЛЛО и специалист ЛЛО, то фильтруем по ЛПУ со схожей территорией обслуживания
		if (!isSuperAdmin() && isset($data['session']['ARMList']) && in_array('touz',$data['session']['ARMList']) && !in_array('mekllo',$data['session']['ARMList']) && !in_array('minzdravdlo',$data['session']['ARMList'])) {
			if (!empty($data['session']['org_id'])) {
				// получаем список лпу с такой же территорией обслуживания, как и у организации пользователя
				// если у организации задана страна + регион то по их равенству, если задан ещё и город то и по городу тоже
				$basefilters[] = "
					EvnRecept.Lpu_id IN (
						select
							l.Lpu_id
						from
							v_OrgServiceTerr ost

							left join v_OrgServiceTerr ost2  on

								COALESCE(ost2.KLCountry_id, 0) = COALESCE(ost.KLCountry_id, 0)

								and COALESCE(ost2.KLRGN_id, 0) = COALESCE(ost.KLRGN_id, 0)

								and COALESCE(ost2.KLSubRgn_id, 0) = COALESCE(ost.KLSubRgn_id, 0)

								and (COALESCE(ost2.KLCity_id, 0) = COALESCE(ost.KLCity_id, 0) or ost.KLCity_id is NULL)

							inner join v_Lpu l  on l.Org_id = ost2.Org_id

						where
							ost.Org_id = :Org_id
					)
				";

				$queryParams['Org_id'] = $data['session']['org_id'];
			} else {
				return false;
			}
		}

		$isExpertise = (
			ArrayVal($data,'ReceptStatusType_id') > 0 || ArrayVal($data,'ReceptStatusFLKMEK_id')
			|| ArrayVal($data,'RegistryReceptErrorType_id') || ArrayVal($data,'AllowRegistryDataRecept')
			|| ArrayVal($data,'RegistryDataRecept_IsReceived') || ArrayVal($data,'RegistryDataRecept_IsPaid')
		);

		$basefilters = array_merge($basefilters, getAccessRightsDiagFilter('Diag.Diag_Code', true));

		$ReceptValidCond = $this->getReceptValidCondition();

		// Поиск в таблице EvnRecept
		If ((ArrayVal($data,'ReceptYes_id') == 2 || ArrayVal($data,'ReceptYes_id') == '') && !$isExpertise ) {
			$filters = $basefilters;
			$med_personal_lpu_filter = preg_replace('/EvnRecept\./','evnRecept_all.',$med_personal_lpu_filter);
			$ReceptValidCondFirst = preg_replace('/EvnRecept\./','evnRecept_all.',$ReceptValidCond);
			$join = preg_replace('/EvnRecept\./','evnRecept_all.',$join);
			foreach($filters as $key=>$filter) {
				$filters[$key] = preg_replace('/EvnRecept\./','evnRecept_all.',$filters[$key]);
			}
            if(isset($data['ReceptForm_id'])){
                $filters[] = "RecF.ReceptForm_id = :ReceptForm_id";
                $queryParams['ReceptForm_id'] = $data['ReceptForm_id'];
            }
			unset($filters['MedPersonalRec_id']);
			unset($filters['Person_SNILS_Recept']);
			$sql = "
			SELECT
				evnRecept_all.ReceptDelayType_id as \"ReceptDelayType_id\",
				CASE
					WHEN
						(evnRecept_all.EvnRecept_deleted = 2/* or evnRecept_all.ReceptDelayType_id = 4*/)
					THEN
						'Удалённый МО'
					WHEN
						{$ReceptValidCondFirst}
					THEN
						'Просрочен'
					WHEN
						evnRecept_all.EvnRecept_otpDT is null AND evnRecept_all.EvnRecept_obrDT is null
					THEN
						'Выписан'
					WHEN
						evnRecept_all.EvnRecept_otpDT is not null
					THEN
						'Отоварен'
					WHEN
						evnRecept_all.EvnRecept_otpDT is null and evnRecept_all.EvnRecept_obrDT is not null
					THEN
						'Отсрочен'
					WHEN
						evnRecept_all.ReceptDelayType_id = 3
					THEN
						'Отказ'
					WHEN
						evnRecept_all.ReceptDelayType_id is not  null
					THEN
						'Выписан'
					WHEN
						evnRecept_all.ReceptDelayType_id = 5
					THEN
						'Снят с обслуживания'
				END
					as \"ReceptDelayType_Name\",
				CASE WHEN evnRecept_all.EvnRecept_IsSigned = 2 THEN 'ДА' ELSE 'НЕТ' END as \"EvnRecept_IsSigned\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				CASE WHEN RT.ReceptType_Code = 3 THEN 'ЭД' ELSE RT.ReceptType_Name END as \"ReceptType_Name\",
				evnRecept_all.ReceptForm_id as \"ReceptForm_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				evnRecept_all.EvnRecept_id as \"EvnRecept_id\",
				evnRecept_all.Person_id as \"Person_id\",
				evnRecept_all.PersonEvn_id as \"PersonEvn_id\",
				evnRecept_all.Server_id as \"Server_id\",
				evnRecept_all.OrgFarmacy_id as \"OrgFarmacy_id\",
				evnRecept_all.OrgFarmacy_oid as \"OrgFarmacy_oid\",
                CAST(Summ.Summa as numeric(19,2)) as \"EvnRecept_Suma\",
				Person.Person_Surname as \"Person_Surname\",
				Person.Person_Firname as \"Person_Firname\",
				Person.Person_Secname as \"Person_Secname\",
				to_char(Person.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

				Person.Person_Snils as \"Person_Snils\",
				evnRecept_all.EvnRecept_Ser as \"EvnRecept_Ser\",
				evnRecept_all.EvnRecept_Num as \"EvnRecept_Num\",
				case when evnRecept_all.Drug_id is not null then
					ReceptFinance.ReceptFinance_Name
					else
					COALESCE(DF.DrugFinance_Name,ReceptFinance.ReceptFinance_Name)

					end as \"ReceptFinance_Name\",
				/*ReceptFinance.ReceptFinance_Name as ReceptFinance_Name,*/
				ReceptFinance.ReceptFinance_id as \"ReceptFinance_id\",
				MedPersonal.Person_Surname||' '||MedPersonal.Person_Firname||' '||MedPersonal.Person_Secname as \"MedPersonal_Fio\",
				case
					when
						COALESCE(evnRecept_all.Drug_rlsid, evnRecept_all.DrugComplexMnn_id) IS Not null

					then
						coalesce(rlsActmatters.RUSNAME,rlsDrugComplexMnnName.DrugComplexMnnName_Name,'')
					else
						DrugMnn.DrugMnn_Name || case when evnRecept_all.EvnRecept_otpDT is not null then '' || COALESCE(DrugMnnOtp.DrugMnn_Name,'') else '' end

				end
				as \"DrugMnn_Name\",
				case
					when
						evnRecept_all.Drug_rlsid IS Not null
					then
						COALESCE(rlsDrug.Drug_Name,'')

					else
						COALESCE(Drug.Drug_Name,'') || case when evnRecept_all.EvnRecept_otpDT is not null then ' ' || COALESCE(DrugOtp.Drug_Name,'') else '' end

				end
				as \"Drug_Name\",
				evnRecept_all.Drug_id as \"Drug_id\",
				evnRecept_all.Drug_rlsid as \"Drug_rlsid\",
				evnRecept_all.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				cast(ROUND(cast(evnRecept_all.EvnRecept_Kolvo as numeric), 3)as varchar) as \"EvnRecept_firKolvo\",
				cast(ROUND(cast(ro.otvEvnRecept_Kolvo as numeric), 3) as varchar) as \"EvnRecept_secKolvo\",
				cast(ROUND(cast(evnRecept_all.EvnRecept_Kolvo as numeric), 3) as varchar) || '<br/>' ||
					case when ro.otvEvnRecept_Kolvo is not null then cast(ROUND(cast(ro.otvEvnRecept_Kolvo as numeric), 3) as varchar) else '&nbsp;' end as \"EvnRecept_Kolvo\",
				COALESCE(OrgFarmacy.OrgFarmacy_Name,'') || '<br/>' ||

					COALESCE(OrgFarmacyOtp.OrgFarmacy_Name,'') as \"OrgFarmacy_Name\",


				to_char(evnRecept_all.EvnRecept_setDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				to_char(interval '-1 day' +case
					when RV.ReceptValid_Code = 1 then interval '1 month' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 2 then interval '3 month' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 3 then interval '14 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 4 then interval '5 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 5 then interval '2 month' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 7 then interval '10 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 8 then interval '60 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 9 then interval '30 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 10 then interval '90 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 11 then interval '15 day' + evnRecept_all.EvnRecept_setDate
				end,'dd.mm.yyyy') as \"EvnRecept_Godn\",
				to_char(evnRecept_all.EvnRecept_obrDT, 'DD.MM.YYYY') as \"EvnRecept_obrDate\",

				to_char(evnRecept_all.EvnRecept_otpDT, 'DD.MM.YYYY') as \"EvnRecept_otpDate\",

				datediff('day', evnRecept_all.EvnRecept_setDT, evnRecept_all.EvnRecept_obrDT) as \"EvnRecept_obrDay\",
				datediff('day', evnRecept_all.EvnRecept_obrDT, COALESCE(evnRecept_all.EvnRecept_otpDT,dbo.tzGetDate())) as \"EvnRecept_otsDay\",

				datediff('day', evnRecept_all.EvnRecept_setDT, evnRecept_all.EvnRecept_otpDT) as \"EvnRecept_otovDay\",
				case when evnRecept_all.DrugRequestRow_id is null then 'НЕТ' else 'ДА' end as \"EvnRecept_InRequest\",
				COALESCE(OrgFarmacyOtp.OrgFarmacy_Name,'') as \"OrgFarmacyOtp_Name\",

				RecF.ReceptForm_Code as \"ReceptForm_Code\",
				COALESCE(wdcit.WhsDocumentCostItemType_Name,'') as \"WhsDocumentCostItemType_Name\"

			FROM
				v_EvnRecept_all evnRecept_all

				left join WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = evnRecept_all.WhsDocumentCostItemType_id

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = evnRecept_all.ReceptValid_id

			left join v_ReceptForm RecF  on RecF.ReceptForm_id = evnRecept_all.ReceptForm_id

			left join v_ReceptType RT  on RT.ReceptType_id = evnRecept_all.ReceptType_id

			inner join
				v_PersonState Person  on Person.Person_id = evnRecept_all.Person_id

            LEFT JOIN LATERAL

                (select
                    otv.EvnRecept_Kolvo as otvEvnRecept_Kolvo
                from ReceptOtov otv

                where otv.EvnRecept_otpDate is not null and
                    otv.EvnRecept_id = evnRecept_all.EvnRecept_id
                limit 1
                ) ro ON true
            LEFT JOIN LATERAL

                (select
                     WDSS.WhsDocumentSupplySpec_PriceNDS*EVN.EvnRecept_Kolvo  as Summa -- для неотоваренных
                from v_EvnRecept EVN

                    left join WhsDocumentSupply WDS  on WDS.WhsDocumentUc_id = EVN.WhsDocumentUc_id

                    left join WhsDocumentSupplySpec WDSS  on WDSS.WhsDocumentSupply_id = WDS.WhsDocumentSupply_id

	            where
					(Evn.Drug_rlsid=WDSS.Drug_id or Evn.DrugComplexMnn_id=WDSS.DrugComplexMnn_id) and
                    EVN.WhsDocumentUc_id is not null and
                    --EVN.EvnRecept_otpDT is null and
                    EVN.EvnRecept_id = evnRecept_all.EvnRecept_id
                ) Summ ON true
				LEFT JOIN LATERAL (

					select Person_Surname, Person_Firname, Person_Secname
					from v_MedPersonal

					where
						MedPersonal_id = evnRecept_all.MedPersonal_id
						{$med_personal_lpu_filter}
						and MedPersonal_Code is not null
                    limit 1
				) MedPersonal ON true
			LEFT JOIN
				v_Drug as Drug  on Drug.Drug_id = evnRecept_all.Drug_id

			LEFT JOIN
				v_Drug as DrugOtp  on DrugOtp.Drug_id = evnRecept_all.Drug_oid

			LEFT JOIN
				v_DrugMnn as DrugMnn  on DrugMnn.DrugMnn_id = Drug.DrugMnn_id

			LEFT JOIN
				v_DrugMnn as DrugMnnOtp  on DrugMnnOtp.DrugMnn_id = DrugOtp.DrugMnn_id

			LEFT JOIN
				rls.v_Drug as rlsDrug  on rlsDrug.Drug_id = evnRecept_all.Drug_rlsid

			LEFT JOIN
				rls.v_DrugComplexMnn as rlsDrugComplexMnn  on rlsDrugComplexMnn.DrugComplexMnn_id = COALESCE(rlsDrug.DrugComplexMnn_id, evnRecept_all.DrugComplexMnn_id)


			LEFT JOIN
				rls.v_DrugComplexMnnName as rlsDrugComplexMnnName  on rlsDrugComplexMnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id

			LEFT JOIN
				rls.v_Actmatters as rlsActmatters  on rlsActmatters.Actmatters_id = rlsDrugComplexMnnName.Actmatters_id

			LEFT JOIN
				v_OrgFarmacy as OrgFarmacy  on OrgFarmacy.OrgFarmacy_id = evnRecept_all.OrgFarmacy_id

			LEFT JOIN
				v_OrgFarmacy as OrgFarmacyOtp  on OrgFarmacyOtp.OrgFarmacy_id = evnRecept_all.OrgFarmacy_oid

			LEFT JOIN
				v_ReceptFinance as ReceptFinance  on ReceptFinance.ReceptFinance_id = evnRecept_all.ReceptFinance_id

			LEFT JOIN
				v_DrugFinance as DF  on DF.DrugFinance_id = evnRecept_all.DrugFinance_id

			LEFT JOIN
				v_ReceptDelayType as ReceptDelayType  on ReceptDelayType.ReceptDelayType_id = evnRecept_all.ReceptDelayType_id

			LEFT JOIN
				v_Lpu as Lpu  on Lpu.Lpu_id = evnRecept_all.Lpu_id

			LEFT JOIN
				v_Diag as Diag  on Diag.Diag_id = evnRecept_all.Diag_id

			".$join." ".ImplodeWhere($filters);

		}
		// Поиск в таблице ReceptOtov
		If (((ArrayVal($data,'ReceptYes_id') == 1 || ArrayVal($data,'ReceptYes_id') == '') &&
			ArrayVal($data,'ReceptType_id') == '' &&//поля тип рецепта нет в отоваренных, просто не делаем запрос
			ArrayVal($data,'ReceptMismatch_id') == '' && ArrayVal($data,'ReceptResult_id') != 12) ||
			$isExpertise
		) {
			$this->genEvnReceptListExpertiseFilters($data, $basefilters, $queryParams, $join);
			$filters = $basefilters;
			unset($filters['MedPersonal_id']);
			unset($filters['Person_SNILS_Person']);
			unset($filters['EvnRecept_deleted']);
			if ( (($data['SearchedLpu_id'] == 0) || ($data['SearchedLpu_id'] == '')) && isMinZdravOrNotLpu() )
				$filters[] = "COALESCE(EvnRecept.EvnRecept_id, 0) not in (select EvnRecept.Evn_id from EvnRecept  inner join Evn  on Evn.Evn_id = EvnRecept.Evn_id and COALESCE(Evn.Evn_deleted, 1) = 1)";


			else
				$filters[] = "COALESCE(EvnRecept.EvnRecept_id, 0) not in (select EvnRecept.Evn_id from EvnRecept  inner join Evn  on Evn.Evn_id = EvnRecept.Evn_id and COALESCE(Evn.Evn_deleted, 1) = 1 where Evn.Lpu_id = :Lpu_id)";



			// <!-- start костылина убогая, не ну а куле?
			$ptrs = array('/EvnRecept.EvnRecept_IsNotOstat/', '/\(EvnRecept_Is7Noz/','/EvnRecept_setDate/','/:EvnRecept.EvnRecept_setDateStart/','/:EvnRecept.EvnRecept_setDateEnd/','/EvnRecept.EvnRecept.EvnRecept_setDate/','/EvnRecept_Num/','/:EvnRecept.EvnRecept_Num/','/EvnRecept_Ser/','/:EvnRecept.EvnRecept_Ser/'); // Поля, наименование которых надо заменить
			$repls = array('ER.EvnRecept_IsNotOstat', '(ER.EvnRecept_Is7Noz', 'EvnRecept_setDate',':EvnRecept_setDateStart',':EvnRecept_setDateEnd','EvnRecept_setDate','EvnRecept_Num',':EvnRecept_Num','EvnRecept_Ser',':EvnRecept_Ser'); // Поля на которые надо заменить
			foreach($filters as $k=>$f) {
				$filters[$k] = preg_replace($ptrs, $repls, $f);
			}
			// --> end костылина
			$filters_otov = $filters; //https://redmine.swan.perm.ru/issues/81039
			foreach($filters_otov as $key=>$filter) {
				if(strpos($filter,':LpuBuilding_id') > 0 || strpos($filter,':LpuSection_id') > 0 || strpos($filter,':EvnRecept_IsKEK') > 0 || strpos($filter,':EvnRecept_VKProtocol') > 0){
					continue;
				}
				if(
					strpos($filter,':EvnRecept_updDT') > 0 ||
					strpos($filter,':EvnRecept_updDTEnd') > 0 ||
					strpos($filter,':EvnRecept_insDTStart') > 0 ||
					strpos($filter,':EvnRecept_insDTEnd') > 0
				) {
					$filters_otov[$key] = preg_replace('/evnRecept_all\./','ER.',$filters_otov[$key]);
				}
				else
					$filters_otov[$key] = preg_replace('/evnRecept_all\./','EvnRecept.',$filters_otov[$key]);
			}
			$sql_recept_otov = "
			SELECT
				EvnRecept.ReceptDelayType_id as \"ReceptDelayType_id\",
				CASE
					WHEN
						{$ReceptValidCond}
					THEN
						'Просрочен'
					WHEN
						EvnRecept.ReceptDelayType_id is null
					THEN
						'Выписан'
					WHEN
						EvnRecept.EvnRecept_otpDT is not null
					THEN
						'Отоварен'
					WHEN
						EvnRecept.EvnRecept_otpDT is null and EvnRecept.EvnRecept_obrDT is not null
					THEN
						'Отсрочен'
					WHEN
						EvnRecept.ReceptDelayType_id = 3
					THEN
						'Отказ'
				END
					as \"ReceptDelayType_Name\",
				CASE WHEN evnRecept_all.EvnRecept_IsSigned = 2 THEN 'ДА' ELSE 'НЕТ' END as \"EvnRecept_IsSigned\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				RT.ReceptType_Name as \"ReceptType_Name\",
				evnRecept_all.ReceptForm_id as \"ReceptForm_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				null as \"EvnRecept_id\",
				EvnRecept.Person_id as \"Person_id\",
				evnRecept_all.OrgFarmacy_id as \"OrgFarmacy_id\",
				evnRecept_all.OrgFarmacy_oid as \"OrgFarmacy_oid\",
				null as \"PersonEvn_id\",
				null as \"Server_id\",
				cast(Summ.DocumentUc_SumNdsR as numeric(19,2)) as \"EvnRecept_Suma\",
				Person.Person_Surname as \"Person_Surname\",
				Person.Person_Firname as \"Person_Firname\",
				Person.Person_Secname as \"Person_Secname\",
				to_char(Person.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

				EvnRecept.Person_Snils as \"Person_Snils\",
				EvnRecept.EvnRecept_Ser as \"EvnRecept_Ser\",
				EvnRecept.EvnRecept_Num as \"EvnRecept_Num\",
				case when evnRecept_all.Drug_id is not null then
					ReceptFinance.ReceptFinance_Name
					else
					COALESCE(DF.DrugFinance_Name,ReceptFinance.ReceptFinance_Name)

					end as \"ReceptFinance_Name\",
				/*ReceptFinance.ReceptFinance_Name as ReceptFinance_Name,*/
				ReceptFinance.ReceptFinance_id as \"ReceptFinance_id\",
				MedPersonal.Person_Surname||' '||MedPersonal.Person_Firname||' '||MedPersonal.Person_Secname as \"MedPersonal_Fio\",
				'&nbsp;<br/>'||DrugMnn.DrugMnn_Name as \"DrugMnn_Name\",
				'&nbsp;<br/>'||COALESCE(Drug.Drug_Name,'') as \"Drug_Name\",

				EvnRecept.Drug_id as \"Drug_id\",
				evnRecept_all.Drug_rlsid as \"Drug_rlsid\",
				evnRecept_all.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				cast(ROUND(cast(EvnRecept.EvnRecept_Kolvo as numeric), 3)as varchar) as \"EvnRecept_firKolvo\",
				'' as \"EvnRecept_secKolvo\",
				cast(ROUND(cast(EvnRecept.EvnRecept_Kolvo as numeric), 3)as varchar) as \"EvnRecept_Kolvo\",
				'&nbsp;<br/>'||COALESCE(OrgFarmacy.OrgFarmacy_Name,'') as \"OrgFarmacy_Name\",

				to_char(EvnRecept.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				to_char(interval '-1 day' +case
					when RV.ReceptValid_Code = 1 then interval '1 month' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 2 then interval '3 month' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 3 then interval '14 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 4 then interval '5 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 5 then interval '2 month' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 7 then interval '10 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 8 then interval '60 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 9 then interval '30 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 10 then interval '90 day' + evnRecept_all.EvnRecept_setDate
					when RV.ReceptValid_Code = 11 then interval '15 day' + evnRecept_all.EvnRecept_setDate
				end,'dd.mm.yyyy') as \"EvnRecept_Godn\",
				to_char(EvnRecept.EvnRecept_obrDT, 'DD.MM.YYYY') as \"EvnRecept_obrDate\",

				to_char(EvnRecept.EvnRecept_otpDT, 'DD.MM.YYYY') as \"EvnRecept_otpDate\",

				datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_obrDT) as \"EvnRecept_obrDay\",
				datediff('day', EvnRecept.EvnRecept_obrDT, COALESCE(EvnRecept.EvnRecept_otpDT,dbo.tzGetDate())) as \"EvnRecept_otsDay\",

				datediff('day', EvnRecept.EvnRecept_setDT, EvnRecept.EvnRecept_otpDT) as \"EvnRecept_otovDay\",
				'НЕТ' as \"EvnRecept_InRequest\",
				'' as \"OrgFarmacyOtp_Name\",
				null as \"ReceptForm_Code\",
				COALESCE(wdcit.WhsDocumentCostItemType_Name,'') as \"WhsDocumentCostItemType_Name\"

			FROM
				v_ReceptOtovUnSub EvnRecept

			left join v_evnrecept_all  evnRecept_all  on evnRecept_all.EvnRecept_id=EvnRecept.EvnRecept_id

			left join WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = evnRecept_all.WhsDocumentCostItemType_id

			left join v_ReceptType RT  on RT.ReceptType_id = evnRecept_all.ReceptType_id

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = EvnRecept.ReceptValid_id

			inner join
				v_PersonState Person  on Person.Person_id = EvnRecept.Person_id

            LEFT JOIN LATERAL

                (select
                    du.DocumentUc_SumNdsR-- для отоваренных
                from ReceptOtov otv

                    left join DocumentUcStr DUS  on otv.receptotov_id=DUS.ReceptOtov_id

                    left join DocumentUc du  on DUS.DocumentUc_id=du.DocumentUc_id

                where otv.EvnRecept_otpDate is not null and
                    otv.ReceptOtov_id = EvnRecept.ReceptOtov_id
                ) Summ ON true
				LEFT JOIN LATERAL (

					select Person_Surname, Person_Firname, Person_Secname
					from v_MedPersonal

					where
						MedPersonal_id = EvnRecept.MedPersonalRec_id
						{$med_personal_lpu_filter}
						and MedPersonal_Code is not null
                    limit 1
				) MedPersonal ON true
			LEFT JOIN
				v_Drug Drug  on Drug.Drug_id=EvnRecept.Drug_id

			LEFT JOIN
				v_DrugMnn DrugMnn  on DrugMnn.DrugMnn_id=Drug.DrugMnn_id

			LEFT JOIN
				v_OrgFarmacy as OrgFarmacy  on OrgFarmacy.OrgFarmacy_id=EvnRecept.OrgFarmacy_id

			LEFT JOIN
				ReceptFinance  on ReceptFinance.ReceptFinance_id = EvnRecept.ReceptFinance_id

			LEFT JOIN
				v_DrugFinance as DF  on DF.DrugFinance_id = EvnRecept.DrugFinance_id

			left join
				v_EvnRecept ER  on ER.EvnRecept_id = EvnRecept.EvnRecept_id

			LEFT JOIN
				v_Lpu as Lpu  on Lpu.Lpu_id = EvnRecept.Lpu_id

			LEFT JOIN
				v_Diag as Diag  on Diag.Diag_id = EvnRecept.Diag_id

			".$join." ".ImplodeWhere($filters_otov);

			if ($sql!=null) {
				$sql = "($sql
					UNION
				$sql_recept_otov
				) as \"Recept\"";
			} else {
				$sql = "($sql_recept_otov) as \"Recept\"";
			}

		} elseif ($sql!=null) {
			$sql = "($sql) as \"Recept\"";
		}

		if ($sql!=null) {
			$sql = "
			SELECT
			-- select
				*
			-- end select
			FROM
			-- from
				$sql
			-- end from
			ORDER BY
			-- order by
				\"Person_Surname\", \"Person_Firname\", \"Person_Secname\"
			-- end order by";
		} else {
			return false;
		}

		//echo getDebugSql($sql, $queryParams);exit;
		//echo getDebugSql(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);exit;
		if (!empty($data['print']) && ($data['print'] == true)) { // если список для печати, то надо печатать весь список, а не только 100 записей
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result))
			{
				//return $result->result('array');
				$response = array();
				$response['data'] =  $result->result('array');
				return $response;
			}

			return false;
		}

		$count = 0;
		// Отдельно для количества
		if (!empty($data['onlyCount']) && ($data['onlyCount'] == true)) {
			$result_count = $this->db->query(getCountSQLPH($sql), $queryParams);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			return $count;
		}

		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);

		if (is_object($result)) {
			$res = $result->result('array');
			// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
			if (count($res)==$data['limit']) {
				// определение общего количества записей
				$result_count = $this->db->query(getCountSQLPH($sql), $queryParams);
				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			} else { // Иначе считаем каунт по реальному количеству + start
				$count = $data['start'] + count($res);
			}
			$response = array();
			$response['totalCount'] = $count;
			$response['data'] =  $res;
			return $response;
		} else {
			return false;
		}
	}
	/**
	 * Возвращает все поля по рецепту
	 */
	function getReceptFields($data) {
		$lpu_filter = "";

		if ( !isMinZdrav() && (!isset($data['session']['medpersonal_id']) || !($data['session']['medpersonal_id'] > 0)) ) {
			$lpu_filter = " and ER.Lpu_id = :Lpu_id";
		}

		// [2013-01-24] http://redmine.swan.perm.ru/issues/15177
		// Для Уфы берется табельный номер врача, выписавшего рецепт
		$query = "
			select 
				dbo.GetRegio() as \"Region_Code\",
				COALESCE(L.Lpu_OGRN, '') as \"Lpu_Ogrn\"

				,COALESCE(cast(L.Lpu_Ouz as varchar(7)), '') as \"Lpu_Ouz\"

				,right('0000000' || COALESCE(cast(L.Lpu_Ouz as varchar(7)), ''), 7) as \"Lpu_Code\"

				,COALESCE(LUS.LpuUnitSet_Code, 0) as \"LpuUnitSet_Code\"

				,COALESCE(L.Lpu_Name,'') as \"Lpu_Name\"

				,CAST(COALESCE(ER.EvnRecept_Ser, '') as varchar(14)) as \"EvnRecept_Ser\"

				,CAST(COALESCE(ER.EvnRecept_Num, '') as varchar(20)) as \"EvnRecept_Num\"

				,CAST(RTRIM(COALESCE(DG.Diag_Code, '')) as varchar(7)) as \"Diag_Code\"

				,COALESCE(RD.ReceptDiscount_Code, 0) as \"ReceptDiscount_Code\"

				,COALESCE(RF.ReceptFinance_Code, 0) as \"ReceptFinance_Code\"

				,COALESCE(RT.ReceptType_Code, 0) as \"ReceptType_Code\"

				,COALESCE(RV.ReceptValid_Code, 0) as \"ReceptValid_Code\"

				,COALESCE(MnnYesNo.YesNo_Code, -1) as \"Drug_IsMnn\"

				,COALESCE(PT.PrivilegeType_Code, 0) as \"PrivilegeType_Code\"

				,to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\"

				,date_part('DAY',ER.EvnRecept_setDT) as \"EvnRecept_setDay\"
				,date_part('MONTH',ER.EvnRecept_setDT) as \"EvnRecept_setMonth\"
				,date_part('YEAR',ER.EvnRecept_setDT) as \"EvnRecept_setYear\"
                ,cast(ER.EvnRecept_Kolvo as double precision) as \"EvnRecept_Kolvo\"
                ,RForm.ReceptForm_Code as \"ReceptForm_Code\"
				,RTRIM(COALESCE(ER.EvnRecept_Signa, '')) as \"EvnRecept_Signa\"
				,case when 2 = COALESCE(ER.EvnRecept_IsDelivery, 1) then 'true' else 'false' end as \"EvnRecept_IsDelivery\"

				,CAST(COALESCE(MSF." . $this->getMedPersonalCodeField() .", '') as varchar(6)) as \"MedPersonal_Code\"

				,COALESCE(RTRIM(MSF.Person_FIO), '') as \"MedPersonal_Fio\"

				,COALESCE(PS.Person_Snils, '') as \"Person_Snils\"

				,PS.Person_id as \"Person_id\"
				,RTRIM(COALESCE(PS.Person_SurName, '')) || ' ' || RTRIM(COALESCE(PS.Person_FirName, '')) || ' ' || RTRIM(COALESCE(PS.Person_SecName, '')) as \"Person_Fio\"

				,to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\"

				,date_part('DAY',PS.Person_BirthDay) as \"Person_Birthday_Day\"
				,date_part('MONTH',PS.Person_BirthDay) as \"Person_Birthday_Month\"
				,date_part('YEAR',PS.Person_BirthDay) as \"Person_Birthday_Year\"
				,COALESCE(RTRIM(case when PLST.PolisType_CodeF008 = 3 then PS.Person_EdNum else PLS.Polis_Num end), '') as \"Polis_Num\"

				,COALESCE(RTRIM(PLS.Polis_Ser), '') as \"Polis_Ser\"

				,RTRIM(COALESCE(OS.OrgSmo_Nick, OS.OrgSmo_Name)) as \"OrgSmo_Name\"

				,COALESCE(PC.PersonCard_Code, COALESCE(PC4.PersonCard_Code, '')) as \"PersonCard_Code \"--для Уфы нужно учитывать еще и служебное прикрепление (http://redmine.swan.perm.ru/issues/24983)

				,CAST(
					COALESCE(RTRIM(PA.KLSubRGN_Name) || ' ', '') ||

					COALESCE(RTRIM(PA.KLSubRGN_Socr) || ', ', '') ||

					COALESCE(RTRIM(PA.KLCity_Socr) || ' ', '') ||

					COALESCE(RTRIM(PA.KLCity_Name) || ', ', '') ||

					COALESCE(RTRIM(PA.KLTown_Name) || ' ', '') ||

					COALESCE(RTRIM(PA.KLTown_Socr) || ', ', '')

					as varchar(100)
				 ) as \"Person_Address_1\"
				,CAST(
					COALESCE(RTRIM(PA.KLStreet_Socr) || ' ', '') ||

					COALESCE(RTRIM(PA.KLStreet_Name) || ', ', '') ||

					COALESCE(NULLIF('Д ' || RTRIM(PA.Address_House) || ', ', 'Д , '), '') ||

					COALESCE(NULLIF('КОРПУС ' || RTRIM(PA.Address_Corpus) || ', ', 'КОРПУС , '), '') ||

					COALESCE(NULLIF('КВ ' || RTRIM(PA.Address_Flat), 'КВ '), '')

					as varchar(100)
				 ) as \"Person_Address_2\"
				,RTRIM(COALESCE(OFarm.OrgFarmacy_Name, '')) as \"OrgFarmacy_Name\"

				,--'' as OrgFarmacy_Phone --,RTRIM(COALESCE(OFarm.OrgFarmacy_Phone, '')) as \"OrgFarmacy_Phone\"

				RTRIM(COALESCE(O.Org_Phone,'')) as \"OrgFarmacy_Phone\"

				,RTRIM(COALESCE(OFarm.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\"

				,OFarm.OrgFarmacy_id as \"OrgFarmacy_id\"

				,COALESCE(

					case
						when MnnYesNo.YesNo_Code = 1 then COALESCE(DMC.DrugMnnCode_Code::text, DM.DrugMnn_Code::text, '0')
						when MnnYesNo.YesNo_Code = 0 then COALESCE(DTC.DrugTorgCode_Code::text, D.Drug_Code::text, '0')
					end, '0'
				 ) as \"DrugMnnTorg_Code\"
				,COALESCE(
					DRls.Drug_Dose,
					D.Drug_DoseQ,
					COALESCE(cast(nullif(D.Drug_Vol, 0) as varchar(10)), '') || COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)), '')

				 ) as \"Drug_Dose\"
				,COALESCE(DRls.Drug_Fas, D.Drug_Fas) as \"Drug_Fas\"

				,'' as \"Drug_Fas_Ed\"
				,COALESCE(ProtoYesNo.YesNo_Code, 0) as \"Drug_IsKEK\"

				,COALESCE(DRls.Drug_Code, D.Drug_Code, 0) as \"Drug_Code\"
				,DM.DrugMnn_Name as \"DrugMnn_Name\"
				,COALESCE(case

					when ER.EvnRecept_IsExtemp = 2 then ER.EvnRecept_ExtempContents
					when MnnYesNo.YesNo_Code = 1 then COALESCE(DM.DrugMnn_NameLat, DM.DrugMnn_Name, DMRls.DrugComplexMnn_LatName, DMRls.DrugComplexMnn_RusName, LatRls.NAME, '')
					when MnnYesNo.YesNo_Code = 0 then COALESCE(DT.DrugTorg_NameLat, DT.DrugTorg_Name, D.Drug_Name,LatRls.LATINNAMES_NameGen, LatRls.NAME, DRls.DrugTorg_Name, DRls.Drug_Name,'')
				 end, '') as \"Drug_Name\"
				 --,D.Drug_Name as Drug_Name_mi1
				 --,TR_MI.NAME as Drug_Name_mi1

                /*Drug_Name_mi1 - после долгих правок пришли к такому варианту печати*/
				,COALESCE(DT.DrugTorg_Name,'')||' '||COALESCE(DRls.DrugForm_Name, DF.DrugForm_Name)||' '||

                COALESCE(
                DRls.Drug_Dose,
                nullif(COALESCE(D.Drug_DoseQ, '') ||

                COALESCE(D.Drug_DoseEi, '') ||

                COALESCE(' ' || cast(nullif(D.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~'), '') ||

                COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~'), '') ||

                COALESCE(' ' || cast(nullif(D.Drug_DoseCount, 0) as varchar(10)) || 'доз', '')

                , ''),
                ''
                ) as \"Drug_Name_mi1\"

				,COALESCE(DF.DrugForm_Name,DRls.DrugForm_Name,CLSDRUGFORMS_NameLatinSocr,CLSDRUGFORMS_NameLatin,'') as \"DrugForm_Name\"
				,COALESCE(
					--DRls.Drug_Dose,
					nullif(
						COALESCE(cast(ROUND(cast(PrepRls.DFMASS as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(MU.MassUnits_NameLatin,''),'') ||

						COALESCE(cast(ROUND(cast(PrepRls.DFCONC as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(CU.CONCENUNITS_NameLatin,''),'') ||

						COALESCE(cast(ROUND(cast(PrepRls.DFACT as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(AU.ACTUNITS_NameLatin, ''),'')

					,''),
					nullif(COALESCE(D.Drug_DoseQ, '') ||

						COALESCE(D.Drug_DoseEi, '') ||

						COALESCE(' ' || cast(nullif(D.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~'), '') || 

						COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~'), '') ||

						COALESCE(' ' || cast(nullif(D.Drug_DoseCount, 0) as varchar(10)) || 'доз', '')

					, ''),
					PrepRls.DFSIZE,
					''
				 ) as \"Drug_DoseFull\",
				COALESCE(wdcit.WhsDocumentCostItemType_Code::text, '') as \"WhsDocumentCostItemType_Code\",

				COALESCE(ER.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\"

			from v_EvnRecept ER 

			    left join v_ReceptForm RForm  on RForm.ReceptForm_id = ER.ReceptForm_id

				left join v_Person_reg PS  on PS.Server_id = ER.Server_id

					and PS.PersonEvn_id = ER.PersonEvn_id
				left join v_PrivilegeType PT  on PT.PrivilegeType_id = ER.PrivilegeType_id

				left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = ER.ReceptDiscount_id

				left join v_ReceptFinance RF  on RF.ReceptFinance_id = ER.ReceptFinance_id

				left join v_ReceptType RT  on RT.ReceptType_id = ER.ReceptType_id

				left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ER.ReceptValid_id

				left join WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id

				LEFT JOIN LATERAL (

					select 
						 MedPersonal_Code
						,MedPersonal_TabCode
						,Person_FIO
					from v_MedStaffFact 

					where MedPersonal_id = ER.MedPersonal_id
						and LpuSection_id = ER.LpuSection_id
						and COALESCE(WorkData_begDate, '1970-01-01') <= ER.EvnRecept_setDate

						and COALESCE(WorkData_endDate, '2030-12-31') >= ER.EvnRecept_setDate

						and COALESCE(WorkData_dlobegDate, '1970-01-01') <= ER.EvnRecept_setDate

						and COALESCE(WorkData_dloendDate, '2030-12-31') >= ER.EvnRecept_setDate

					order by MedPersonal_Code desc
                    limit 1
				) MSF ON true
				left join v_LpuSection LS  on LS.LpuSection_id = ER.LpuSection_id

				left join v_Diag DG  on DG.Diag_id = ER.Diag_id

				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id

				left join v_LpuUnitSet LUS  on LUS.LpuUnitSet_id = LU.LpuUnitSet_id

				left join v_Drug D  on D.Drug_id = ER.Drug_id

				left join v_DrugTorg DT  on DT.DrugTorg_id = D.DrugTorg_id

				left join v_DrugMnn DM  on DM.DrugMnn_id = D.DrugMnn_id

				left join rls.v_Drug DRls  on DRls.Drug_id = ER.Drug_rlsid

				left join rls.PREP PrepRls  on PrepRls.Prep_id = DRls.DrugPrep_id


				left join rls.MASSUNITS MU  on MU.MASSUNITS_ID = PrepRls.DFMASSID

				left join rls.CONCENUNITS CU  on CU.CONCENUNITS_ID = PrepRls.DFCONCID

				left join rls.ACTUNITS AU  on AU.ACTUNITS_ID = PrepRls.DFACTID

				left join rls.SIZEUNITS SU  on SU.SIZEUNITS_ID = PrepRls.DFSIZEID


				left join rls.LATINNAMES LatRls  on LatRls.LATINNAMES_id = PrepRls.LATINNAMEID

				left join rls.v_DrugComplexMnn DMRls  on DMRls.DrugComplexMnn_id = COALESCE(DRls.DrugComplexMnn_id,ER.DrugComplexMnn_id)


				left join rls.CLSDRUGFORMS CLSDR  on CLSDR.CLSDRUGFORMS_ID = DMRLs.CLSDRUGFORMS_ID

				left join rls.v_ACTMATTERS ActMat  on ActMat.ACTMATTERS_ID = DMRls.ActMatters_id

				left join rls.v_DrugNomen DN  on DN.Drug_id = DRls.Drug_id

				left join rls.v_DrugMnnCode DMC  on DMC.DrugMnnCode_id = DN.DrugMnnCode_id

				left join rls.v_DrugTorgCode DTC  on DTC.DrugTorgCode_id = DN.DrugTorgCode_id

				left join DrugForm DF  on DF.DrugForm_id = D.DrugForm_id

				left join DrugEdVol DEV  on DEV.DrugEdVol_id = D.DrugEdVol_id

				left join DrugEdMass DEM  on DEM.DrugEdMass_id = D.DrugEdMass_id

				left join v_Polis PLS  on PLS.Polis_id = PS.Polis_id

				left join v_PolisType PLST  on PLST.PolisType_id = PLS.PolisType_id

				left join v_OrgSMO OS  on OS.OrgSMO_id = PLS.OrgSmo_id

				left join v_PersonCard_all PC  on PC.Person_id = PS.Person_id

					and PC.Lpu_id = ER.Lpu_id
					and (
						(PC.PersonCard_begDate <= ER.EvnRecept_setDT and PC.PersonCard_endDate > ER.EvnRecept_setDT)
						or
						PC.PersonCard_endDate is null
					)
					and PC.LpuAttachType_id=1
				left join v_PersonCard_all PC4  on PC4.Person_id = PS.Person_id --для Уфы нужно учитывать еще и служебное прикрепление (http://redmine.swan.perm.ru/issues/24983)

					and PC4.Lpu_id = ER.Lpu_id
					and (
						(PC4.PersonCard_begDate <= ER.EvnRecept_setDT and PC4.PersonCard_endDate > ER.EvnRecept_setDT)
						or
						PC4.PersonCard_endDate is null
					)
					and PC4.LpuAttachType_id=4
				left join v_YesNo MnnYesNo  on MnnYesNo.YesNo_id = ER.EvnRecept_IsMnn

				left join v_YesNo ProtoYesNo  on ProtoYesNo.YesNo_id = ER.EvnRecept_IsKek

				left join v_Lpu L  on L.Lpu_id = ER.Lpu_id

				left join v_Address_all PA  on PA.Address_id = COALESCE(PS.PAddress_id, PS.UAddress_id)


				left join v_OrgFarmacy OFarm  on OFarm.OrgFarmacy_id = ER.OrgFarmacy_id

					and OFarm.OrgFarmacy_IsEnabled = 2
				left join Org O  on O.Org_id = OFarm.Org_id

				left join v_YesNo Is7Noz  on Is7Noz.YesNo_id = ER.EvnRecept_Is7Noz

			where ER.EvnRecept_id = :EvnRecept_id
				" . $lpu_filter . "
			limit 1
		";
        /*
		echo getDebugSQL($query, array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'Lpu_id' => $data['Lpu_id']
		));
        */
		$result = $this->db->query($query, array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'Lpu_id' => $data['Lpu_id']
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
	 * @return bool
	 * Получение данных для печати рецепта на Саратове
	 */
	function GetReceptFieldsSaratov($data){
		$lpu_filter = "";

		if ( !isMinZdrav() && (!isset($data['session']['medpersonal_id']) || !($data['session']['medpersonal_id'] > 0)) ) {
			$lpu_filter = " and ER.Lpu_id = :Lpu_id";
		}

		if($data['session']['region']['nick'] != 'ufa') //https://redmine.swan.perm.ru/issues/62600
		{
			$COALESCE = 'null';

		}
		else{
			$COALESCE = "D.Drug_Name";

		}
		$query = "
			select 
				dbo.GetRegio() as \"Region_Code\",
				COALESCE(L.Lpu_OGRN, '') as \"Lpu_Ogrn\"

				,COALESCE(cast(L.Lpu_Ouz as varchar(7)), '') as \"Lpu_Ouz\"

				,right('0000000' || COALESCE(cast(L.Lpu_Ouz as varchar(7)), ''), 7) as \"Lpu_Code\"

				,COALESCE(LUS.LpuUnitSet_Code, 0) as \"LpuUnitSet_Code\"

				,COALESCE(L.Lpu_Name,'') as \"Lpu_Name\"

				,CAST(COALESCE(ER.EvnRecept_Ser, '') as varchar(14)) as \"EvnRecept_Ser\"

				,CAST(COALESCE(ER.EvnRecept_Num, '') as varchar(20)) as \"EvnRecept_Num\"

				,CAST(RTRIM(COALESCE(DG.Diag_Code, '')) as varchar(7)) as \"Diag_Code\"

				,COALESCE(RD.ReceptDiscount_Code, 0) as \"ReceptDiscount_Code\"

				,COALESCE(RF.ReceptFinance_Code, 0) as \"ReceptFinance_Code\"

				,COALESCE(RT.ReceptType_Code, 0) as \"ReceptType_Code\"

				,COALESCE(RV.ReceptValid_Code, 0) as \"ReceptValid_Code\"

				,COALESCE(MnnYesNo.YesNo_Code, -1) as \"Drug_IsMnn\"

				,COALESCE(PT.PrivilegeType_Code, 0) as \"PrivilegeType_Code\"

				,to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\"

				,date_part('DAY',ER.EvnRecept_setDT) as \"EvnRecept_setDay\"
				,RForm.ReceptForm_Code
				,date_part('MONTH',ER.EvnRecept_setDT) as \"EvnRecept_setMonth\"
				,date_part('YEAR',ER.EvnRecept_setDT) as \"EvnRecept_setYear\"
				,cast(ER.EvnRecept_Kolvo as numeric(10, 2)) as \"EvnRecept_Kolvo\"
				,RTRIM(COALESCE(ER.EvnRecept_Signa, '')) as \"EvnRecept_Signa\"
				,case when 2 = coalesce(ER.EvnRecept_IsDelivery, 1) then 'true' else 'false' end as \"EvnRecept_IsDelivery\"

				,CAST(COALESCE(MSF." . $this->getMedPersonalCodeField() .", '') as varchar(6)) as \"MedPersonal_Code\"

				,COALESCE(RTRIM(MSF.Person_FIO), '') as \"MedPersonal_Fio\"

				,COALESCE(PS.Person_Snils, '') as \"Person_Snils\"

				,PS.Person_id as \"Person_id\"
				,RTRIM(COALESCE(PS.Person_SurName, '')) || ' ' || RTRIM(COALESCE(PS.Person_FirName, '')) || ' ' || RTRIM(COALESCE(PS.Person_SecName, '')) as \"Person_Fio\"

				,to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\"

				,date_part('DAY',PS.Person_BirthDay) as \"Person_Birthday_Day\"
				,date_part('MONTH',PS.Person_BirthDay) as \"Person_Birthday_Month\"
				,date_part('YEAR',PS.Person_BirthDay) as \"Person_Birthday_Year\"
				,COALESCE(RTRIM(case when PLST.PolisType_CodeF008 = 3 then PS.Person_EdNum else PLS.Polis_Num end), '') as \"Polis_Num\"

				,COALESCE(RTRIM(PLS.Polis_Ser), '') as \"Polis_Ser\"

				,RTRIM(COALESCE(OS.OrgSmo_Nick, OS.OrgSmo_Name)) as \"OrgSmo_Name\"

				,COALESCE(PC.PersonCard_Code, COALESCE(PC4.PersonCard_Code, '')) as \"PersonCard_Code \"--для Уфы нужно учитывать еще и служебное прикрепление (http://redmine.swan.perm.ru/issues/24983)

				,CAST(
					COALESCE(RTRIM(PA.KLSubRGN_Name) || ' ', '') ||

					COALESCE(RTRIM(PA.KLSubRGN_Socr) || ', ', '') ||

					COALESCE(RTRIM(PA.KLCity_Socr) || ' ', '') ||

					COALESCE(RTRIM(PA.KLCity_Name) || ', ', '') ||

					COALESCE(RTRIM(PA.KLTown_Name) || ' ', '') ||

					COALESCE(RTRIM(PA.KLTown_Socr) || ', ', '')

					as varchar(100)
				 ) as \"Person_Address_1\"
				,CAST(
					COALESCE(RTRIM(PA.KLStreet_Socr) || ' ', '') ||

					COALESCE(RTRIM(PA.KLStreet_Name) || ', ', '') ||

					COALESCE(NULLIF('Д ' || RTRIM(PA.Address_House) || ', ', 'Д , '), '') ||

					COALESCE(NULLIF('КОРПУС ' || RTRIM(PA.Address_Corpus) || ', ', 'КОРПУС , '), '') ||

					COALESCE(NULLIF('КВ ' || RTRIM(PA.Address_Flat), 'КВ '), '')

					as varchar(100)
				 ) as \"Person_Address_2\"
				,RTRIM(COALESCE(OFarm.OrgFarmacy_Name, '')) as \"OrgFarmacy_Name\"

				,--'' as OrgFarmacy_Phone --,RTRIM(COALESCE(OFarm.OrgFarmacy_Phone, '')) as \"OrgFarmacy_Phone\"

				RTRIM(COALESCE(O.Org_Phone,'')) as \"OrgFarmacy_Phone\"

				,RTRIM(COALESCE(OFarm.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\"

				,OFarm.OrgFarmacy_id as \"OrgFarmacy_id\"
				,COALESCE(

					case
						when MnnYesNo.YesNo_Code = 1 then COALESCE(DMC.DrugMnnCode_Code::text, DM.DrugMnn_Code::text, '0')
						when MnnYesNo.YesNo_Code = 0 then COALESCE(DTC.DrugTorgCode_Code::text, D.Drug_Code::text, '0')
					end, '0'
				 ) as \"DrugMnnTorg_Code\"
				 ,DRls.DrugTorg_Name as \"DrugTorg_Name_mi1\"
				 ,DM.DrugMnn_Name as \"DrugMnn_Name\"
				,COALESCE(
					DRls.Drug_Dose,
					D.Drug_DoseQ,
					COALESCE(cast(nullif(D.Drug_Vol, 0) as varchar(10)), '') || COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)), '')

				 ) as \"Drug_Dose\"
				,COALESCE(DRls.Drug_Fas, D.Drug_Fas) as \"Drug_Fas\"

				,case when (COALESCE(Nomen.PPACKVOLUME,0)<>0 or COALESCE(Nomen.PPACKMASS,0)<>0) then COALESCE(DPACK.FULLNAMELATIN,'') else '' end as \"Drug_Fas_Ed\"

				--,COALESCE(DPACK.FULLNAMELATIN,'') as Drug_Fas_Ed

				,COALESCE(ProtoYesNo.YesNo_Code, 0) as \"Drug_IsKEK\"

				,COALESCE(DRls.Drug_Code, D.Drug_Code, 0) as \"Drug_Code\"
				,REPLACE(COALESCE(COALESCE(CLSDRUGFORMS_NameLatinSocr,CLSDRUGFORMS_NameLatin),'') || ' ' ||COALESCE(case

					when ER.EvnRecept_IsExtemp = 2 then ER.EvnRecept_ExtempContents
					when MnnYesNo.YesNo_Code = 1 then COALESCE(ActMat.ACTMATTERS_LatNameGen, ActMat.LATNAME, ActMat.RUSNAME,DM.DrugMnn_NameLat, DM.DrugMnn_Name, LatRls.NAME, '')
					when MnnYesNo.YesNo_Code = 0 then COALESCE(LatRls.LATINNAMES_NameGen, LatRls.NAME, DRls.DrugTorg_Name, DRls.Drug_Name, DT.DrugTorg_NameLat, DT.DrugTorg_Name, D.Drug_Name, '')
				 end, ''),'*','') as \"Drug_Name\"

				,COALESCE(CLSDRUGFORMS_NameLatinSocr,CLSDRUGFORMS_NameLatin,DRls.DrugForm_Name,DF.DrugForm_Name,'') as \"DrugForm_Name\"
				,COALESCE(
					--DRls.Drug_Dose,
					nullif(
						COALESCE(cast(ROUND(cast(PrepRls.DFMASS as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(MU.MassUnits_NameLatin,''),'') || ' ' ||

						COALESCE(cast(ROUND(cast(PrepRls.DFCONC as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(CU.CONCENUNITS_NameLatin,''),'') || ' ' ||

						COALESCE(cast(ROUND(cast(PrepRls.DFACT as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(AU.ACTUNITS_NameLatin, ''),'')

					,''),
					nullif(COALESCE(D.Drug_DoseQ, '') || ' ' ||

						COALESCE(D.Drug_DoseEi, '') || ' ' ||

						COALESCE(' ' || cast(nullif(D.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~'), '') || ' ' ||

						COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~'), '') || ' ' ||

						COALESCE(' ' || cast(nullif(D.Drug_DoseCount, 0) as varchar(10)) || 'доз', '')

					, ''),
					nullif(
						COALESCE(COALESCE(PrepRls.DFSIZELATIN,PrepRls.DFSIZE),'') || ' ' ||

						COALESCE(COALESCE(SU.SHORTNAMELATIN,SU.FULLNAMELATIN),'')

					, ''),
					nullif(
						case when ((Nomen.PPACKVOLUME is not null or Nomen.PPACKMASS is not null) and Nomen.DRUGSINPPACK <> 0) then
							COALESCE(cast(ROUND(cast(Nomen.DRUGSINPPACK as varchar(10))::numeric,2) as varchar(10)),'') || COALESCE(COALESCE(DPACK.FULLNAMELATIN,''),'')

						else ''
					end
					,''),
					''
				 ) || ' ' ||
				 coalesce(
					nullif(COALESCE(cast(ROUND(cast(Nomen.PPACKVOLUME as varchar(10))::numeric,2) as varchar(10)),'') || ' ' || COALESCE(COALESCE(CUBUN.SHORTNAMELATIN,' '),''),''),

					nullif(COALESCE(cast(ROUND(cast(Nomen.PPACKMASS as varchar(10))::numeric,2) as varchar(10)),'') || ' ' ||  COALESCE(COALESCE(MUN.MassUnits_NameLatin,' '),''),''),''

				 ) || ' ' ||
				 case when COALESCE(cast(ROUND(cast(PrepRls.DRUGDOSE as varchar(10))::numeric,2) as varchar(10)),'')::numeric=0 then '' else COALESCE(cast(ROUND(cast(PrepRls.DRUGDOSE as varchar(10))::numeric,2) as varchar(10)),'') end

				 as \"Drug_DoseFull\",
				(
					COALESCE(
                    ".$COALESCE."
                    ,

						coalesce(DRls.DrugTorg_Name||' ', DT.DrugTorg_Name||' ', '')||
						coalesce(DRls.DrugForm_Name||' ', DF.DrugForm_Name||' ', '')||
						coalesce(
							DRls.Drug_Dose,
							nullif(
								COALESCE(D.Drug_DoseQ, '') ||

								COALESCE(D.Drug_DoseEi, '') ||

								COALESCE(' ' || cast(nullif(D.Drug_Vol, 0) as varchar(10)) || nullif(DEV.DrugEdVol_Name, '~'), '') ||

								COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)) || nullif(DEM.DrugEdMass_Name, '~'), '') ||

								COALESCE(' ' || cast(nullif(D.Drug_DoseCount, 0) as varchar(10)) || 'доз', ''),

								''
							),
							''
						)
					)
				) as \"Drug_Name_mi1\",
				COALESCE(wdcit.WhsDocumentCostItemType_Code::text, '') as \"WhsDocumentCostItemType_Code\",

				COALESCE(ER.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\"

			from v_EvnRecept ER 

				--left join v_PersonState PS  on PS.Server_id = ER.Server_id

				--	and PS.PersonEvn_id = ER.PersonEvn_id
				left join v_PersonState PS  on PS.Person_id = ER.Person_id

				left join v_ReceptForm RForm  on RForm.ReceptForm_id = ER.ReceptForm_id

				left join v_PrivilegeType PT  on PT.PrivilegeType_id = ER.PrivilegeType_id

				left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = ER.ReceptDiscount_id

				left join v_ReceptFinance RF  on RF.ReceptFinance_id = ER.ReceptFinance_id

				left join v_ReceptType RT  on RT.ReceptType_id = ER.ReceptType_id

				left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ER.ReceptValid_id

				left join WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id

				LEFT JOIN LATERAL (

					select 
						 MedPersonal_Code
						,MedPersonal_TabCode
						,Person_FIO
					from v_MedStaffFact 

					where MedPersonal_id = ER.MedPersonal_id
						and LpuSection_id = ER.LpuSection_id
						and COALESCE(WorkData_begDate, '1970-01-01') <= ER.EvnRecept_setDate

						and COALESCE(WorkData_endDate, '2030-12-31') >= ER.EvnRecept_setDate

						and COALESCE(WorkData_dlobegDate, '1970-01-01') <= ER.EvnRecept_setDate

						and COALESCE(WorkData_dloendDate, '2030-12-31') >= ER.EvnRecept_setDate

					order by MedPersonal_Code desc
                    limit 1
				) MSF ON true
				left join v_LpuSection LS  on LS.LpuSection_id = ER.LpuSection_id

				left join v_Diag DG  on DG.Diag_id = ER.Diag_id

				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id

				left join v_LpuUnitSet LUS  on LUS.LpuUnitSet_id = LU.LpuUnitSet_id

				left join v_Drug D  on D.Drug_id = ER.Drug_id

				left join v_DrugTorg DT  on DT.DrugTorg_id = D.DrugTorg_id

				left join v_DrugMnn DM  on DM.DrugMnn_id = D.DrugMnn_id

				left join rls.v_DrugComplexMnn DMRls  on DMRls.DrugComplexMnn_id = ER.DrugComplexMnn_id

				--left join rls.v_Drug DRls  on DRls.Drug_id = COALESCE(ER.Drug_rlsid,ER.DrugComplexMnn_id)


				LEFT JOIN LATERAL(

					select Drug_id,DrugPrep_id, DrugTorg_Name, DrugForm_Name, Drug_Dose, Drug_Fas, Drug_Code, Drug_Name
					from rls.v_Drug 

					where Drug_id = ER.Drug_rlsid or DrugComplexMnn_id = ER.DrugComplexMnn_id
                    limit 1
				) DRls ON true
				left join rls.PREP PrepRls  on PrepRls.Prep_id = DRls.DrugPrep_id


				left join rls.MASSUNITS MU  on MU.MASSUNITS_ID = PrepRls.DFMASSID

				left join rls.CONCENUNITS CU  on CU.CONCENUNITS_ID = PrepRls.DFCONCID

				left join rls.ACTUNITS AU  on AU.ACTUNITS_ID = PrepRls.DFACTID

				left join rls.SIZEUNITS SU  on SU.SIZEUNITS_ID = PrepRls.DFSIZEID


				--left join rls.v_Nomen Nomen  on Nomen.PREPID = PrepRls.Prep_id

				left join rls.v_Nomen Nomen  on Nomen.Nomen_id = Drls.Drug_id

				left join rls.v_CUBICUNITS CUBUN  on CUBUN.CUBICUNITS_ID = Nomen.PPACKCUBUNID

				left join rls.v_MassUnits MUN  on MUN.MASSUNITS_ID = Nomen.PPACKMASSUNID

				left join rls.v_DRUGPACK DPACK  on DPACK.DRUGPACK_ID = Nomen.PPACKID


				left join rls.LATINNAMES LatRls  on LatRls.LATINNAMES_id = PrepRls.LATINNAMEID

				--left join rls.v_DrugComplexMnn DMRls  on DMRls.DrugComplexMnn_id = COALESCE(DRls.DrugComplexMnn_id, ER.DrugComplexMnn_id)


				left join rls.CLSDRUGFORMS CLSDR  on CLSDR.CLSDRUGFORMS_ID = DMRLs.CLSDRUGFORMS_ID

				left join rls.PREP_ACTMATTERS PACM  on PACM.PREPID=PrepRls.Prep_id

				left join rls.drugcomplexmnnname dcomn  on dcomn.DrugComplexMnnName_id = DMRls.DrugComplexMnnName_id

				left join rls.v_ACTMATTERS ActMat  on ActMat.ACTMATTERS_ID = COALESCE(PACM.MATTERID,dcomn.ACTMATTERS_id)


				--left join rls.v_ACTMATTERS ActMat  on ActMat.ACTMATTERS_ID = DMRls.ActMatters_id

				left join rls.v_DrugNomen DN  on DN.Drug_id = DRls.Drug_id

				left join rls.v_DrugMnnCode DMC  on DMC.DrugMnnCode_id = DN.DrugMnnCode_id

				left join rls.v_DrugTorgCode DTC  on DTC.DrugTorgCode_id = DN.DrugTorgCode_id

				left join DrugForm DF  on DF.DrugForm_id = D.DrugForm_id

				left join DrugEdVol DEV  on DEV.DrugEdVol_id = D.DrugEdVol_id

				left join DrugEdMass DEM  on DEM.DrugEdMass_id = D.DrugEdMass_id

				left join v_Polis PLS  on PLS.Polis_id = PS.Polis_id

				left join v_PolisType PLST  on PLST.PolisType_id = PLS.PolisType_id

				left join v_OrgSMO OS  on OS.OrgSMO_id = PLS.OrgSmo_id

				left join v_PersonCard_all PC  on PC.Person_id = PS.Person_id

					and PC.Lpu_id = ER.Lpu_id
					and (
						(PC.PersonCard_begDate <= ER.EvnRecept_setDT and PC.PersonCard_endDate > ER.EvnRecept_setDT)
						or
						PC.PersonCard_endDate is null
					)
					and PC.LpuAttachType_id=1
				left join v_PersonCard_all PC4  on PC4.Person_id = PS.Person_id --для Уфы нужно учитывать еще и служебное прикрепление (http://redmine.swan.perm.ru/issues/24983)

					and PC4.Lpu_id = ER.Lpu_id
					and (
						(PC4.PersonCard_begDate <= ER.EvnRecept_setDT and PC4.PersonCard_endDate > ER.EvnRecept_setDT)
						or
						PC4.PersonCard_endDate is null
					)
					and PC4.LpuAttachType_id=4
				left join v_YesNo MnnYesNo  on MnnYesNo.YesNo_id = ER.EvnRecept_IsMnn

				left join v_YesNo ProtoYesNo  on ProtoYesNo.YesNo_id = ER.EvnRecept_IsKek

				left join v_Lpu L  on L.Lpu_id = ER.Lpu_id

				left join v_Address_all PA  on PA.Address_id = COALESCE(PS.PAddress_id, PS.UAddress_id)


				left join v_OrgFarmacy OFarm  on OFarm.OrgFarmacy_id = ER.OrgFarmacy_id

					and OFarm.OrgFarmacy_IsEnabled = 2
				left join Org O  on O.Org_id = OFarm.Org_id

				left join v_YesNo Is7Noz  on Is7Noz.YesNo_id = ER.EvnRecept_Is7Noz

			where ER.EvnRecept_id = :EvnRecept_id
				" . $lpu_filter . "

			limit 1
		";

		/*echo getDebugSQL($query, array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'Lpu_id' => $data['Lpu_id']
		));*/

		$result = $this->db->query($query, array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * Возвращает номер для нового рецепта (атонумерация)
	 */
	function getReceptNumber($data) {
		$query = "select objectid as \"rnumber\" 
				from xp_GenpmID(
					ObjectName := 'EvnRecept',
					Lpu_id := :Lpu_id);
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает номер для рецепта (моб)
	 */
	function getReceptSerAndNumberForApi($data) {

		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsGlobals($data);

		if ($options['globals']['use_numerator_for_recept'] == 2 || !empty($data['isGeneral'])) { //для нельготных рецептов нумераторы используются по умолчанию
			$this->load->model('Numerator_model');

			$name = 'Выписка рецепта';
			$query = null;

			if (!empty($data['isGeneral'])) { //общие рецепты
				$sysname = 'EvnReceptGeneral';

				switch($data['ReceptForm_id']) {
					case 2:
						$query = 'ReceptForm_id=2';
						$name = 'Выписка простого рецепта 1-МИ';
						break;
					case 3:
						$query = 'ReceptForm_id=3';
						$name = 'Выписка простого рецепта по форме  107-1/у';
						break;
					case 5:
						$query = 'ReceptForm_id=5';
						$name = 'Выписка простого рецепта 148-1/у-88';
						break;
					case 8:
						$query = 'ReceptForm_code =’107/у-НП’';
						$name = 'Выписка рецепта на НС и ПВ по форме  107/у-НП';
						break;
				}
			} else { //льготные рецепты
				$sysname = 'EvnRecept';

				if (!empty($data['WhsDocumentCostItemType_id'])) {
					$obj_query_data = $this->dbmodel->getNumeratorObjectQueryByWhsDocumentCostItemTypeId($data['WhsDocumentCostItemType_id']);
					$query = $obj_query_data['query'];
					$name = $obj_query_data['name'];
				}
			}

			$params = array(
				'NumeratorObject_SysName' => $sysname,
				'NumeratorObject_Query' => $query,
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
				'onDate' => $data['EvnRecept_setDate']
			);

			$resp = $this->Numerator_model->getNumeratorNum($params, null);
			$val = array();

			if (!empty($resp['Numerator_Num'])) {
				$val['EvnRecept_Ser'] = $resp['Numerator_Ser'];
				$val['EvnRecept_Num'] = $resp['Numerator_Num'];
				$val['SerNum_Source'] = 'Numerator';

				return $val;

			} else {
				if (!empty($resp['Error_Msg'])) return $resp['Error_Msg'];
				else return array('Error_Msg' => 'Не задан активный нумератор для "'.$name.'". Обратитесь к администратору системы.');
			}
		} else {
			// по классической схеме
			$prefix = '1';
			$punkt  = '0';
			$val    = array();

			if (getRegionNick() == 'buryatiya' && !empty($data['isRLS']) && $data['isRLS']) {
				$result = $this->dbmodel->getReceptNumberRls($data);
			} else {
				$result = $this->dbmodel->getReceptNumber($data);
			}

			if ( is_array($result) && count($result) > 0 ) {
				if (getRegionNick() == 'khak') {
					$val['EvnRecept_Num'] = $result[0]['rnumber'];
				} else {
					$val['EvnRecept_Num'] = $prefix . $punkt . sprintf('%06d', $result[0]['rnumber']);
				}
			}

			return $val;
		}
	}

	/**
	 * Проверка резервирования номеров
	 */
	function checkNumInRezerv($data) {
		$this->load->model('Numerator_model');
		$query = null;

		if (!empty($data['isGeneral'])) { //общие рецепты
			$sysname = 'EvnReceptGeneral';

			switch($data['ReceptForm_id']) {
				case 2:
					$query = 'ReceptForm_id=2';
					break;
				case 3:
					$query = 'ReceptForm_id=3';
					break;
				case 5:
					$query = 'ReceptForm_id=5';
					break;
				case 8:
					$query = 'ReceptForm_code =’107/у-НП’';
					break;
			}
		} else { //льготные рецепты
			$sysname = 'EvnRecept';

			if (!empty($data['WhsDocumentCostItemType_id'])) {
				$obj_query_data = $this->dbmodel->getNumeratorObjectQueryByWhsDocumentCostItemTypeId($data['WhsDocumentCostItemType_id']);
				$query = $obj_query_data['query'];
			}
		}

		$data['Numerator_Num'] = $data['EvnRecept_Num'];
		$data['NumeratorObject_SysName'] = $sysname;
		$data['NumeratorObject_Query'] = $query;

		return $this->Numerator_model->checkNumInRezerv($data);
	}

    /**
     * Возвращет количество рецептов
     */
	function getReceptCount($data) {
		$query = "
			select
				count(*) as \"Recept_Count\"
			from v_EvnRecept 

			where (1 = 1)
				and Lpu_id = :Lpu_id
				and EvnRecept_Ser = :EvnRecept_Ser
				and EvnRecept_Num >= :MinValue and (EvnRecept_Num <= :MaxValue OR :MaxValue IS NULL)
		";

		$params = array(
			 'Lpu_id' => $data['Lpu_id']
			,'EvnRecept_Ser' => $data['EvnRecept_Ser']
			,'MinValue' => $data['MinValue']
			,'MaxValue' => $data['MaxValue']
		);
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Возвращает список рецептов введенных с заданной даты, для арма ЛЛО
	 */
	function loadReceptList($data) {
		$filter = "1=1";
		$queryParams = array();
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$filter .= " and ER.Lpu_id = :Lpu_id";

		if( !empty($data['EvnRecept_pid']) ) {
			$filter .= " and ER.EvnRecept_pid = :EvnRecept_pid";
			$queryParams['EvnRecept_pid'] = $data['EvnRecept_pid'];
		}
		
		if( !empty($data['begDate']) ) {
			$filter .= " and ER.EvnRecept_setDT >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if( !empty($data['endDate']) ) {
			$filter .= " and ER.EvnRecept_setDT <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}
		
		if( !empty($data['Search_SurName']) ) {
			$filter .= " and PS.Person_SurName iLIKE :Person_SurName || '%'";

			$queryParams['Person_SurName'] = rtrim($data['Search_SurName']);
		}
		
		if( !empty($data['Search_FirName']) ) {
			$filter .= " and PS.Person_FirName iLIKE :Person_FirName || '%'";

			$queryParams['Person_FirName'] = rtrim($data['Search_FirName']);
		}
		
		if( !empty($data['Search_SecName']) ) {
			$filter .= " and PS.Person_SecName iLIKE :Person_SecName || '%'";

			$queryParams['Person_SecName'] = rtrim($data['Search_SecName']);
		}
		
		if( !empty($data['Search_BirthDay']) ) {
			$filter .= " and PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}
		
		if( !empty($data['Person_Snils']) ) {
			$filter .= " and PS.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}
		
		$query = "
			select
				-- select
				ER.EvnRecept_id as \"EvnRecept_id\"
				,ER.EvnRecept_pid as \"EvnRecept_pid\"
				,ER.ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\"
				,ER.Person_id as \"Person_id\"
				,ER.PersonEvn_id as \"PersonEvn_id\"
				,ER.Server_id as \"Server_id\"
				,ER.Drug_rlsid as \"Drug_rlsid\"
				,ER.Drug_id as \"Drug_id\"
				,ER.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				,RTrim(PS.Person_SurName) as \"Person_Surname\"
				,RTrim(PS.Person_FirName) as \"Person_Firname\"
				,RTrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\"

				,to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\"

				,RTrim(COALESCE(DRls.Drug_Name, Drug.Drug_Name, DCM.DrugComplexMnn_RusName, ER.EvnRecept_ExtempContents)) as \"Drug_Name\"
				,RTrim(ER.EvnRecept_Ser) as \"EvnRecept_Ser\"
				,RTrim(ER.EvnRecept_Num) as \"EvnRecept_Num\"
				,RTrim(MP.Person_FIO) as \"MedPersonal_Fio\"
				,mt.MorbusType_SysNick as \"MorbusType_SysNick\"
				,mt.MorbusType_id as \"MorbusType_id\"
				,CASE WHEN ER.EvnRecept_IsSigned = 2 THEN 'true' ELSE 'false' END as \"EvnRecept_IsSigned\"
				,CASE WHEN ER.EvnRecept_IsPrinted = 2 THEN 'true' ELSE 'false' END as \"EvnRecept_IsPrinted\"
				,RT.ReceptType_Code as \"ReceptType_Code\"
				,RT.ReceptType_Name as \"ReceptType_Name\"
				,CASE WHEN RDT.ReceptDelayType_Code = 4 THEN 'true' ELSE 'false' END as \"Recept_MarkDeleted\"
				,RF.ReceptForm_id as \"ReceptForm_id\"
				,RF.ReceptForm_Code as \"ReceptForm_Code\"
				,RF.ReceptForm_Name as \"ReceptForm_Name\"
				,cast(ER.EvnRecept_Kolvo as numeric(10, 2)) as \"EvnRecept_Kolvo\"
				-- end select
			from
				-- from
				v_EvnRecept ER 

				inner join v_Person_FIO PS  on PS.Server_id = ER.Server_id

					and PS.PersonEvn_id = ER.PersonEvn_id
				INNER JOIN LATERAL (

					select Person_FIO
					from v_MedPersonal 

					where MedPersonal_id = ER.MedPersonal_id
						and Lpu_id = :Lpu_id
                    limit 1
				) MP ON true
				left join v_ReceptType RT  on RT.ReceptType_id = ER.ReceptType_id

				left join v_ReceptForm RF  on RF.ReceptForm_id = ER.ReceptForm_id

				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = ER.ReceptDelayType_id

				left join v_Drug Drug  on Drug.Drug_id = ER.Drug_id

				left join rls.v_Drug DRls  on DRls.Drug_id = ER.Drug_rlsid

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id

				left join dbo.v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id

				left join dbo.v_MorbusType mt  on mt.MorbusType_id = COALESCE(wdcit.MorbusType_id, 1)


				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				ER.EvnRecept_setDT desc
				-- end order by
		";
		$result = $this->db->query($query, $queryParams);

		//var_dump($query); die();
		$countQuery = getCountSQLPH($query);
		
		// определение общего количества записей
		$countResult = $this->db->query($countQuery, $queryParams);
		
		if ( !is_object($countResult) ) {
			return false;
		}

		$cnt_arr = $countResult->result('array');
		
		$count = $cnt_arr[0]['cnt'];

		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 ) {
			$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		}

		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);
		
		if ( !is_object($result) ) {
			return false;
		}
		
		$val = $result->result('array');

		return array(
			 'data' => $val
			,'totalCount' => $count
		);
	}

	/**
	 * Возвращает список рецептов введенных с заданной даты, для поточного ввода
	 */
	function loadStreamReceptList($data) {
		$filter = '';
		$queryParams = array();

		$filter .= " and ER.pmUser_insID = :pmUser_id";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( (strlen($data['begDate']) > 0) && (strlen($data['begTime']) > 0) ) {
			$filter .= " and ER.EvnRecept_insDT >= cast(:begDateTime as timestamp)";
			$queryParams['begDateTime'] = $data['begDate']. " " . $data['begTime'];
		}

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and ER.Lpu_id = :Lpu_id ";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT distinct 
				ER.EvnRecept_id as \"EvnRecept_id\",
				ER.EvnRecept_pid as \"EvnRecept_pid\",
				ER.ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\",
				ER.Person_id as \"Person_id\",
				ER.PersonEvn_id as \"PersonEvn_id\",
				ER.Server_id as \"Server_id\",
				ER.Drug_id as \"Drug_id\",
				ER.Drug_rlsid as \"Drug_rlsid\",
				ER.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				RTrim(PS.Person_SurName) as \"Person_Surname\",
				RTrim(PS.Person_FirName) as \"Person_Firname\",
				RTrim(PS.Person_SecName) as \"Person_Secname\",
				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

				to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				RTrim(COALESCE(DrugRls.Drug_Name, Drug.Drug_Name, ER.EvnRecept_ExtempContents)) as \"Drug_Name\",
				RTrim(ER.EvnRecept_Ser) as \"EvnRecept_Ser\",
				RTrim(ER.EvnRecept_Num) as \"EvnRecept_Num\",
				RTrim(MP.Person_FIO) as \"MedPersonal_Fio\",
				CASE WHEN ER.EvnRecept_IsSigned = 2 THEN 'true' ELSE 'false' END as \"EvnRecept_IsSigned\",
				CASE WHEN ER.EvnRecept_IsPrinted = 2 THEN 'true' ELSE 'false' END as \"EvnRecept_IsPrinted\",
				mt.MorbusType_SysNick as \"MorbusType_SysNick\",
				mt.MorbusType_id as \"MorbusType_id\"
			FROM v_EvnRecept ER 

				inner join v_Person_FIO PS  on PS.Server_id = ER.Server_id

					and PS.PersonEvn_id = ER.PersonEvn_id
				INNER JOIN LATERAL (

					select Person_FIO
					from v_MedPersonal 

					where MedPersonal_id = ER.MedPersonal_id
						and Lpu_id = :Lpu_id
                    limit 1
				) MP ON true
				left join rls.v_Drug DrugRls  on DrugRls.Drug_id = ER.Drug_rlsid

				left join dbo.v_Drug Drug  on Drug.Drug_id = ER.Drug_id

				left join dbo.v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id

				left join dbo.v_MorbusType mt  on mt.MorbusType_id = COALESCE(wdcit.MorbusType_id, 1)


			WHERE (1 = 1)
				and ER.ReceptRemoveCauseType_id is null
				" . $filter . "
			ORDER BY ER.EvnRecept_id desc
			limit 100
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение списка заболеваний пациента
	 */
	function loadPersonRegisterList($data) {
		$query = "
			select
				 PersonRegister_id as \"PersonRegister_id\"
				,MorbusType_id as \"MorbusType_id\"
				,PersonRegisterType_id as \"PersonRegisterType_id\"
				,Diag_id as \"Diag_id\"
				,to_char(PersonRegister_setDate, 'DD.MM.YYYY') as \"PersonRegister_setDate\"

				,to_char(PersonRegister_disDate, 'DD.MM.YYYY') as \"PersonRegister_disDate\"

			from
				v_PersonRegister 

			where
				Person_id = COALESCE(CAST(:Person_id as bigint), 0)

		";

		$queryParams = array(
			'Person_id' => $data['Person_id']
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
     * Сохранение рецепта
     */
	function saveEvnReceptRls($data) {
		if ( !empty($data['EvnRecept_id']) && empty($data['fromAPI']) ) {
			return array(array('success' => false, 'Error_Msg' => 'Редактирование рецепта запрещено'));
		}

		//для московского региона при необходимости производится смена источника финансирования
		if ($this->getRegionNick() == 'msk') {
			$query = "
				with mv1 as (
					select
						df.DrugFinance_SysNick
					from
						v_PrivilegeType pt
						left join v_DrugFinance df on df.DrugFinance_id = pt.DrugFinance_id
					where
						pt.PrivilegeType_id = :PrivilegeType_id
					limit 1
				), mv2 as (
					select
						wdcit.WhsDocumentCostItemType_Nick
					from
						v_WhsDocumentCostItemType wdcit
					where
						wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
					limit 1
				), mv3 as ( -- получение ид регионального финансирования
					select
						df.DrugFinance_id
					from
						v_DrugFinance df
					where
						df.DrugFinance_SysNick = 'reg' and
						(
							df.DrugFinance_begDate is null or
							df.DrugFinance_begDate <= dbo.tzGetDate()
						) and
						(
							df.DrugFinance_endDate is null or
							df.DrugFinance_endDate >= dbo.tzGetDate()
						)
					order by
						df.DrugFinance_id asc
					limit 1
				), mv4 as ( -- проверка наличия у медикамента в справочнике СПО УЛО признака выписки по федеральному бюджету
					select
						count(d.Drug_id) as cnt
					from
						rls.Drug d
						inner join rls.v_DrugNomen dn on dn.Drug_id = d.Drug_id
						inner join r50.SPOULODrug sud on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code	
					where
						(
							(
								:Drug_id is not null and
								d.Drug_id = :Drug_id
							) or
							(
								:Drug_id is null and
								d.DrugComplexMnn_id = :DrugComplexMnn_id
							)
						) and
						coalesce(sud.fed, 0) = 1
				);
				
				-- если федеральная льгота, програма ОНЛС и нет признака выписки медикамента по федеральному справочнику, то возвращаем региональный источник финансирования
				select
					(case when (select DrugFinance_SysNick from mv1) = 'fed'
							and (select WhsDocumentCostItemType_Nick from mv2) = 'fl'
							and (select cnt from mv4) = 0
						then (select DrugFinance_id from mv3)
						else null end) as \"NewDrugFinance_id\"
			";
			$NewDrugFinance_id = $this->getFirstResultFromQuery($query, array(
				'Drug_id' => !empty($data['Drug_rlsid']) ? $data['Drug_rlsid'] : null,
				'DrugComplexMnn_id' => !empty($data['DrugComplexMnn_id']) ? $data['DrugComplexMnn_id'] : null,
				'PrivilegeType_id' => !empty($data['PrivilegeType_id']) ? $data['PrivilegeType_id'] : null,
				'WhsDocumentCostItemType_id' => !empty($data['WhsDocumentCostItemType_id']) ? $data['WhsDocumentCostItemType_id'] : null
			));

			if (!empty($NewDrugFinance_id)) {
				$data['DrugFinance_id'] = $NewDrugFinance_id;
			}
		}

		// Проверка на уникальность серии и номера рецепта
		$trans_good = true;
		$is_kardio = ($data['isKardio'] == 1);
		$this->db->trans_begin();

		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsAll($data);

		$o = $this->opmodel->getOptionsGlobals($data);
		$g_options = $o['globals'];

		if (getRegionNick() == 'kz' && empty($data['EvnRecept_Ser'])) {
			$data['EvnRecept_Ser'] = '101';
		}

		// Проверка на уникальность серии и номера рецепта
		$unique_ser_num = ($this->getRegionNick() == 'ufa' || $options['recepts']['unique_ser_num'] === true || $options['recepts']['unique_ser_num'] == '1' || $options['recepts']['unique_ser_num'] == 'true');
		if ($this->getRegionNick() != 'kz' && $unique_ser_num) {
			$check_recept_ser_num = $this->checkReceptSerNum($data);

			if ( $check_recept_ser_num == -1 ) {
				$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта'));
				$trans_good = false;
			}
			else if ( $check_recept_ser_num > 0 ) {
				$response = array(array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее'));
				$trans_good = false;
			}
		}

		// Проверка на наличие необеспеченных рецептов
		/*if ( $trans_good === true ) {
			$check_recept_person_actmatters = $this->checkReceptPersonActmatters($data);
			if (!empty($check_recept_person_actmatters['Error_Msg'])) {
				$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке на наличие необеспеченных рецептов'));
				$trans_good = false;
			} else if (!empty($check_recept_person_actmatters['EvnRecept_id'])) {
				$response = array(array('success' => false, 'Error_Msg' => 'Выписка рецепта невозможна, так как у пациента на руках есть рецепты на лекарственные средства с таким же действующим веществом, и пациент в аптеку не обращался'));
				$trans_good = false;
			}
		}*/

		// Проверка состояния запросов на прохождение модерации по льготе (только для режима постмодерации)
		if ( $trans_good === true && $g_options['person_privilege_add_request_postmoderation'] == 1) { //активен режим постмодерации
			$check_recept_ppr_state = $this->checkReceptCurrentPersonPrivilgeReqState($data);

			if (!empty($check_recept_ppr_state['Error_Msg'])) {
				$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке запросов на включение в льготные регистры'));
				$trans_good = false;
			} else if ($check_recept_ppr_state['reject_cnt'] > 0) {
				$response = array(array('success' => false, 'Error_Msg' => 'Выписка рецепта невозможна, так как последний запрос на включение в регистр по указанной льготной категории не прошел модерацию'));
				$trans_good = false;
			} else if ($check_recept_ppr_state['new_cnt'] > 0) {
				$response = array(array('success' => false, 'Error_Msg' => 'Выписка рецепта невозможна, так как запрос на включение в регистр по указанной льготной категории еще не прошел модерацию'));
				$trans_good = false;
			}
		}

		// Проверяем признак подписания существующего рецепта
		if ( $trans_good === true && !empty($data['EvnRecept_id']) ) {
			$query = "
				select COALESCE(EvnRecept_IsSigned, 1) as \"EvnRecept_IsSigned\"
				from v_EvnRecept 
				where EvnRecept_id = :EvnRecept_id
				limit 1
			";

			$queryParams = array(
				'EvnRecept_id' => $data['EvnRecept_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении проверки признака подписания рецепта'));
					$trans_good = false;
				}
				else if ( $response[0]['EvnRecept_IsSigned'] == 2 ) {
					$response = array(array('success' => false, 'Error_Msg' => 'Рецепт уже подписан, сохранение невозможно'));
					$trans_good = false;
				}
			}
			else {
				$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка признака подписания рецепта)'));
				$trans_good = false;
			}
		}

		//сохранение данных назначения
		if ( $trans_good === true && !empty($data['EvnCourseTreatDrug_KolvoEd']) && !empty($data['EvnRecept_pid']) && empty($data['EvnUslugaTelemed']) ) {
			$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');

			$course_data = $data;
			$course_data['EvnCourseTreat_pid'] = $data['EvnRecept_pid']; //устанавливаем в качестве родителя курса, родителя рецепта
			$course_data['parentEvnClass_SysNick'] = null;
			$course_data['EvnCourseTreat_ContReception'] = $course_data['EvnCourseTreat_Duration']; //считаем что прием непрерывный
			$course_data['EvnCourseTreat_Interval'] = 0; //перерыв
			$course_data['DurationType_id'] = 1; //1 - Дни
			$course_data['DurationType_recid'] = $course_data['DurationType_id'];
			$course_data['DurationType_intid'] = $course_data['DurationType_id'];
			$course_data['EvnPrescrTreat_Descr'] = null;

			//определение системного ника родительского события
			if (!empty($course_data['EvnCourseTreat_pid'])) {
				$query = "
					select
						e.EvnClass_SysNick as \"EvnClass_SysNick\"
					from
						v_Evn e
					where
						e.Evn_id = :Evn_id
					limit 1;
				";
				$course_data['parentEvnClass_SysNick'] = $this->getFirstResultFromQuery($query, array(
					'Evn_id' => $course_data['EvnCourseTreat_pid']
				));
			}

			$course_drug_data = array(
				'id' => null,
				'status' => 'new',
				'MethodInputDrug_id' => !empty($data['Drug_rlsid']) ? 2 : 1,
				'DrugComplexMnn_id' => empty($data['Drug_rlsid']) ? $data['DrugComplexMnn_id'] : null,
				'Drug_id' => $data['Drug_rlsid'],
				'KolvoEd' => $data['EvnCourseTreatDrug_KolvoEd'],
				'Kolvo' => $data['EvnCourseTreatDrug_Kolvo'],
				//'EdUnits_id' => $data['EdUnits_id'],
				'DrugComplexMnnDose_Mass' => null/*$data['DrugComplexMnnDose_Mass']*/,
				'DoseDay' => null,
				'PrescrDose' => null,
				'GoodsUnit_id' => $data['GoodsUnit_id'],
				'GoodsUnit_sid' => $data['GoodsUnit_sid']
			);

			//определение ед. изм. дозы и расчет курсовой и дневной доз (ВНИМАНИЕ! расчет верен лишь для длительности в днях)
			if (!empty($course_drug_data['GoodsUnit_id'])) {
				$query = "
					select
						gu.GoodsUnit_Nick
					from
						v_GoodsUnit gu
					where
						gu.GoodsUnit_id = :GoodsUnit_id
					limit 1;
				";
				$gu_nick = $this->getFirstResultFromQuery($query, array(
					'GoodsUnit_id' => $course_drug_data['GoodsUnit_id']
				));
				if (!empty($gu_nick)) {
					$dd = (int)$course_data['EvnCourseTreat_CountDay'] * (int)$course_drug_data['KolvoEd'];
					$pd = $dd * (int)$course_data['EvnCourseTreat_Duration'];

					$course_drug_data['DoseDay'] = $dd.' '.$gu_nick;
					$course_drug_data['PrescrDose'] = $pd.' '.$gu_nick;
				}
			}

			$course_data['DrugListData'] = array('1' => $course_drug_data);

			$course_save_result = $this->EvnPrescrTreat_model->doSaveEvnCourseTreat($course_data);
			$course_save_error = null;
			if (is_array($course_save_result) && count($course_save_result) > 0) {
				if (!empty($course_save_result[0]['EvnCourseTreatDrug_id1_saved'])) {
					$data['EvnCourseTreatDrug_id'] = $course_save_result[0]['EvnCourseTreatDrug_id1_saved'];
				} else {
					$course_save_error = !empty($course_save_result[0]['Error_Msg']) ? $course_save_result[0]['Error_Msg'] : 'Ошибка при сохранении назначения';
				}
			} else {
				$course_save_error = 'Ошибка при сохранении назначения';
			}

			if (!empty($course_save_error)) {
				$response = array(array('success' => false, 'Error_Msg' => $course_save_error));
				$trans_good = false;
			}
		}

		if ( $trans_good === true ) {
			$data['EvnRecept_IsPrinted'] = null;
			// если на бланке, считаем что распечатан
			if ($data['ReceptType_id'] == 1) {
				$data['EvnRecept_IsPrinted'] = 2;
			}

			$query = "
				select EvnRecept_id as \"EvnRecept_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_EvnRecept_" . (!empty($data['EvnRecept_id']) ? "upd" : "ins") . "(
					EvnRecept_id := :EvnRecept_id,
					EvnRecept_pid := :EvnRecept_pid,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					EvnRecept_setDT := :EvnRecept_setDate,
					EvnRecept_Num := :EvnRecept_Num,
					EvnRecept_Ser := :EvnRecept_Ser,
					EvnRecept_Price := :Drug_Price,
					EvnRecept_IsOtherDiag := :EvnRecept_IsOtherDiag,
					Diag_id := :Diag_id,
					ReceptDiscount_id := :ReceptDiscount_id,
					ReceptFinance_id := :ReceptFinance_id,
					DrugFinance_id := :DrugFinance_id,
					ReceptValid_id := :ReceptValid_id,
					PersonPrivilege_id := :PersonPrivilege_id,
					PrivilegeType_id := :PrivilegeType_id,
					EvnRecept_IsKEK := :EvnRecept_IsKEK,
					EvnRecept_Kolvo := :EvnRecept_Kolvo,
					MedPersonal_id := :MedPersonal_id,
					LpuSection_id := :LpuSection_id,
					Drug_rlsid := :Drug_rlsid,
					DrugComplexMnn_id := :DrugComplexMnn_id,
					DrugRequestRow_id := :DrugRequestRow_id,
					ReceptForm_id := :ReceptForm_id,
					ReceptType_id := :ReceptType_id,
					EvnRecept_IsMnn := :EvnRecept_IsMnn,
					EvnRecept_Is7Noz := :EvnRecept_Is7Noz,
					EvnRecept_IsPrinted := :EvnRecept_IsPrinted,
					EvnRecept_Signa := :EvnRecept_Signa,
					EvnRecept_IsDelivery := :EvnRecept_IsDelivery,
					OrgFarmacy_id := :OrgFarmacy_id,
					ReceptDelayType_id := :ReceptDelayType_id,
					EvnRecept_IsNotOstat := :EvnRecept_IsNotOstat,
					WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
					EvnRecept_IsSigned := :EvnRecept_IsSigned,
					WhsDocumentUc_id := :WhsDocumentUc_id,
					EvnRecept_ExtempContents := :EvnRecept_ExtempContents,
					EvnRecept_IsExtemp := :EvnRecept_IsExtemp,
					Storage_id := :Storage_id,
					EvnRecept_VKProtocolNum := :EvnRecept_VKProtocolNum,
					EvnRecept_VKProtocolDT := :EvnRecept_VKProtocolDT,
					CauseVK_id := :CauseVK_id,
					PersonAmbulatCard_id := :PersonAmbulatCard_id,
					EvnCourseTreatDrug_id := :EvnCourseTreatDrug_id,
					PrescrSpecCause_id := :PrescrSpecCause_id,
					ReceptUrgency_id := :ReceptUrgency_id,
					EvnRecept_IsExcessDose := :EvnRecept_IsExcessDose,
					pmUser_id := :pmUser_id);

			";

			$queryParams = array(
				'EvnRecept_id' => (!empty($data['EvnRecept_id']) ? $data['EvnRecept_id'] : NULL),
				'EvnRecept_pid' => (!empty($data['EvnRecept_pid']) ? $data['EvnRecept_pid'] : NULL),
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnRecept_setDate' => $data['EvnRecept_setDate'],
				'EvnRecept_Num' => $data['EvnRecept_Num'],
				'EvnRecept_Ser' => $data['EvnRecept_Ser'],
				'Drug_Price' => $data['Drug_Price'],
				'EvnRecept_IsOtherDiag' => (!empty($data['EvnRecept_IsOtherDiag']) ? $data['EvnRecept_IsOtherDiag'] : NULL),
				'Diag_id' => $data['Diag_id'],
				'ReceptDiscount_id' => $data['ReceptDiscount_id'],
				'ReceptFinance_id' => $data['ReceptFinance_id'],
				'DrugFinance_id' => $data['DrugFinance_id'],
				'ReceptValid_id' => $data['ReceptValid_id'],
				'PersonPrivilege_id' => $data['PersonPrivilege_id'],
				'PrivilegeType_id' => $data['PrivilegeType_id'],
				'EvnRecept_IsKEK' => (!empty($data['EvnRecept_IsKEK']) ? $data['EvnRecept_IsKEK'] : NULL),
				'EvnRecept_Kolvo' => $data['EvnRecept_Kolvo'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'Drug_rlsid' => (!empty($data['Drug_rlsid']) ? $data['Drug_rlsid'] : NULL),
				'DrugComplexMnn_id' => (!empty($data['DrugComplexMnn_id']) ? $data['DrugComplexMnn_id'] : NULL),
				'DrugRequestRow_id' => (!empty($data['DrugRequestRow_id']) ? $data['DrugRequestRow_id'] : NULL),
				'EvnRecept_Signa' => (!empty($data['EvnRecept_Signa']) ? $data['EvnRecept_Signa'] : NULL),
				'EvnRecept_IsDelivery' => ($data['EvnRecept_IsDelivery']=='on') ? '2' : '1',
				'ReceptForm_id' => $data['ReceptForm_id'],
				'ReceptType_id' => $data['ReceptType_id'],
				'EvnRecept_IsMnn' => (!empty($data['EvnRecept_IsMnn']) ? $data['EvnRecept_IsMnn'] : NULL),
				'EvnRecept_Is7Noz' => $data['EvnRecept_Is7Noz'],
				'EvnRecept_IsPrinted' => $data['EvnRecept_IsPrinted'],
				'OrgFarmacy_id' => (!empty($data['OrgFarmacy_id']) ? $data['OrgFarmacy_id'] : NULL),
				'ReceptDelayType_id' => (!empty($data['ReceptDelayType_id']) ? $data['ReceptDelayType_id'] : NULL),
				'EvnRecept_IsNotOstat' => (!empty($data['EvnRecept_IsNotOstat']) ? $data['EvnRecept_IsNotOstat'] : NULL),
				'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
				'EvnRecept_IsSigned' => (!empty($data['EvnRecept_IsSigned']) ? $data['EvnRecept_IsSigned'] : NULL),
				'WhsDocumentUc_id' => (!empty($data['WhsDocumentUc_id']) && $data['WhsDocumentUc_id'] > 0) ? $data['WhsDocumentUc_id'] : NULL,
				'EvnRecept_ExtempContents' => !empty($data['EvnRecept_ExtempContents'])?$data['EvnRecept_ExtempContents']:null,
				'EvnRecept_IsExtemp' => !empty($data['EvnRecept_IsExtemp'])?$data['EvnRecept_IsExtemp']:null,
				'Storage_id' => !empty($data['Storage_id']) ? $data['Storage_id'] : null,
				'EvnRecept_VKProtocolNum' => !empty($data['EvnRecept_VKProtocolNum']) ? $data['EvnRecept_VKProtocolNum'] : null,
				'EvnRecept_VKProtocolDT' => !empty($data['EvnRecept_VKProtocolDT']) ? $data['EvnRecept_VKProtocolDT'] : null,
				'CauseVK_id' => !empty($data['CauseVK_id']) ? $data['CauseVK_id'] : null,
				'PersonAmbulatCard_id' => !empty($data['PersonAmbulatCard_id']) ? $data['PersonAmbulatCard_id'] : null,
				'EvnCourseTreatDrug_id' => !empty($data['EvnCourseTreatDrug_id']) ? $data['EvnCourseTreatDrug_id'] : null,
				'PrescrSpecCause_id' => !empty($data['PrescrSpecCause_id']) ? $data['PrescrSpecCause_id'] : null,
				'ReceptUrgency_id' => !empty($data['ReceptUrgency_id']) ? $data['ReceptUrgency_id'] : null,
				'EvnRecept_IsExcessDose' => !empty($data['EvnRecept_IsExcessDose']) ? 2 : 1,
				'pmUser_id' => $data['pmUser_id']
			);
			//die(getDebugSQL($query, $queryParams));
			$result = $this->db->query($query, $queryParams);
			//print_r($result->result('array'));exit;
			if ( is_object($result) ) {
				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 || empty($response[0]['EvnRecept_id']) ) {
					$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
					$trans_good = false;
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					$response = array(array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']));
					$trans_good = false;
				}
				else {
					$data['EvnRecept_id'] = $response[0]['EvnRecept_id'];
					
					if (getRegionNick() == 'msk' && $options['globals']['use_external_service_for_recept_num'] == 1) {
						//получим данные по серии и номеру
						$query = "
							select
								ReceptFreeNum_id as \"ReceptFreeNum_id\",
								Lpu_id as \"Lpu_id\",
								LpuUnit_id as \"LpuUnit_id\",
								ReceptFreeNum_Ser as \"ReceptFreeNum_Ser\",
								ReceptFreeNum_Num as \"ReceptFreeNum_Num\",
								EvnRecept_id as \"EvnRecept_id\"
							from r50.ReceptFreeNum
							where 1=1
								and ReceptFreeNum_Num = :EvnRecept_Num
								and ReceptFreeNum_Ser = :EvnRecept_Ser
						";
						$res = $this->getFirstRowFromQuery($query, $data);
						
						if (!empty($res['ReceptFreeNum_Num']) && !empty($res['ReceptFreeNum_Ser'])) {
							//серия и номер уже сохранены, надо только добавить ссылку на рецепт
							$res['pmUser_id'] = $data['pmUser_id'];
							$res['EvnRecept_id'] = $data['EvnRecept_id'];
							$this->queryResult("								
								select
									ReceptFreeNum_id as \"ReceptFreeNum_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from r50.p_ReceptFreeNum_upd (
									ReceptFreeNum_id := :ReceptFreeNum_id,
									Lpu_id := :Lpu_id,
									LpuUnit_id := :LpuUnit_id,
									ReceptFreeNum_Ser := :ReceptFreeNum_Ser,
									ReceptFreeNum_Num := :ReceptFreeNum_Num,
									EvnRecept_id := :EvnRecept_id,
									pmUser_id := :pmUser_id
								)
							", $res);
						}
						else {
							$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении связи рецепта и свободного номера из таблицы свободных номеров'));
							$trans_good = false;
						}
					}
				}
			}
			else {
				$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
				$trans_good = false;
			}
		}

		if (
			$trans_good === true && !$is_kardio && !empty($g_options['select_drug_from_list']) && in_array($g_options['select_drug_from_list'], array('allocation'/*, 'request_and_allocation'*/))
		) { //cписываем медикамент с остатков ЛПУ

			if (empty($data['DrugOstatRegistry_id'])) { //Такое бывает, если не указали "наименование", однако DrugOstatRegistry_id можно получить, имея Drug_rlsid; что и сделаем:
				if(!empty($data['Drug_rlsid'])){
					$dor_params = array();
					$dor_params['Drug_rlsid'] = $data['Drug_rlsid'];
					$dor_params['Lpu_id'] = $data['Lpu_id'];
					$dor_params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
					if (!empty($data['EvnRecept_IsMnn'])) {
						if ($data['EvnRecept_IsMnn'] == 2) {
							$and_dor = " and DCMN.ActMatters_id is not null";
						} else {
							$and_dor = " and DCMN.ActMatters_id is null";
						}
					}
					$dor_query = "
						select 
							DOR.DrugOstatRegistry_id as \"DrugOstatRegistry_id\"
						from v_DrugOstatRegistry DOR 

						inner join rls.v_Drug D  on D.Drug_id = DOR.Drug_id

						left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id

						left join rls.v_DrugComplexMnnName DCMN  on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id

						inner join Lpu L  on L.Org_id = DOR.Org_id

						inner join v_SubAccountType SAT  on SAT.SubAccountType_id = DOR.SubAccountType_id

						left join rls.v_PrepSeries PS  on PS.PrepSeries_id = DOR.PrepSeries_id

						left join v_YesNo YN  on YN.YesNo_id = PS.PrepSeries_IsDefect

						where 
							D.Drug_id = :Drug_rlsid
							and L.Lpu_id = :Lpu_id
							and DOR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
							and DOR.DrugOstatRegistry_Kolvo > 0 --На остатках должны быть медикаменты
							and SAT.SubAccountType_Code = 1 --Доступно
							and COALESCE(YN.YesNo_Code,0) = 0 --Исключение забракованных серий

							{$and_dor}
					";
					//echo getDebugSQL($dor_query,$dor_params);die;
					$dor_result = $this->db->query($dor_query,$dor_params);
					if(is_object($dor_result)){
						$dor_result = $dor_result->result('array');
						if(count($dor_result) > 0)
							{
								$data['DrugOstatRegistry_id'] = $dor_result[0]['DrugOstatRegistry_id'];
							}
					}
				}
				//var_dump($data);die;
				if(empty($data['DrugOstatRegistry_id']))
				{
					$response = array(array('success' => false, 'Error_Msg' => 'Для списания из разнарядки МО должен быть передан идентифактор разнарядки'));
					$trans_good = false;
				}
			}

			if ($trans_good) {
				$query = "
					select
						COALESCE(DrugOstatRegistry_Kolvo, 0) as \"DrugOstatRegistry_Kolvo\"

					from
						v_DrugOstatRegistry 

					where
						DrugOstatRegistry_id = :DrugOstatRegistry_id;
				";

				$result = $this->db->query($query, array(
					'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id']
				));

				if ( is_object($result) ) {
					$res = $result->result('array');
					if ( !is_array($res) || count($res) == 0 ) {
						$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
						$trans_good = false;
					} else if ($res[0]['DrugOstatRegistry_Kolvo'] < $data['EvnRecept_Kolvo']) {
						$response = array(array('success' => false, 'Error_Msg' => 'На остатках ЛПУ недостаточно медикамента для списания'));
						$trans_good = false;
					}
				} else {
					$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
					$trans_good = false;
				}
			}

			if ($trans_good) {
				$query = "
                WITH cte AS (
					select
					 Contragent_id,
					 Org_id,
					 DrugShipment_id,
					 Drug_id,
					 SubAccountType_id,
					 Okei_id,
					 DrugOstatRegistry_Kolvo,
					 DrugOstatRegistry_Sum,
					 DrugOstatRegistry_Cost,
					 Storage_id,
					 PrepSeries_id,
                     (:EvnRecept_Kolvo/DrugOstatRegistry_Kolvo)*DrugOstatRegistry_Sum * (-1) as DrugOstatRegistry_Sum2,
                     :EvnRecept_Kolvo * (-1) AS DrugOstatRegistry_Kolvo2
					from
						v_DrugOstatRegistry 
					where
						DrugOstatRegistry_id = :DrugOstatRegistry_id
                    limit 1
                   )
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from xp_DrugOstatRegistry_count(
						Contragent_id := (SELECT Contragent_id FROM cte),
						Org_id := (SELECT Org_id FROM cte),
						DrugShipment_id := (SELECT DrugShipment_id FROM cte),
						Drug_id := (SELECT Drug_id FROM cte),
						PrepSeries_id := (SELECT PrepSeries_id FROM cte),
						SubAccountType_id := (SELECT SubAccountType_id FROM cte), -- субсчёт доступно
						Okei_id := (SELECT Okei_id FROM cte),
						DrugOstatRegistry_Kolvo := (SELECT DrugOstatRegistry_Kolvo2 FROM cte),
						DrugOstatRegistry_Sum := (SELECT DrugOstatRegistry_Sum2 FROM cte),
						DrugOstatRegistry_Cost := (SELECT DrugOstatRegistry_Cost FROM cte),
						Storage_id := (SELECT Storage_id FROM cte),
						pmUser_id := :pmUser_id);
				";

				$queryParams = array(
					'EvnRecept_Kolvo' => $data['EvnRecept_Kolvo'],
					'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$res = $result->result('array');
					if ( !is_array($res) || count($res) == 0 ) {
						$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
						$trans_good = false;
					} else if ( !empty($res[0]['Error_Msg']) ) {
						$response = array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
						$trans_good = false;
					}
				} else {
					$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
					$trans_good = false;
				}
			}

			if ($trans_good) {
				$resp = $this->queryResult("
					select EvnReceptDrugOstReg_id as \"EvnReceptDrugOstReg_id\"
					from v_EvnReceptDrugOstReg 

					where EvnRecept_id = :EvnRecept_id
					limit 1
				", array(
					'EvnRecept_id' => $data['EvnRecept_id']
				));

				if (!is_array($resp)) {
					$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при запросе связи рецепта и строки разнарядки'));
					$trans_good = false;
				}

				if (isset($resp[0]) && !empty($resp[0]['EvnReceptDrugOstReg_id'])) {
					$procedure = "p_EvnReceptDrugOstReg_upd";
				} else {
					$procedure = "p_EvnReceptDrugOstReg_ins";
				}

				$query = "
					select EvnReceptDrugOstReg_id as \"EvnReceptDrugOstatReg_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from {$procedure}(
						EvnReceptDrugOstReg_id := :EvnReceptDrugOstReg_id,
						EvnRecept_id := :EvnRecept_id,
						DrugOstatRegistry_id := :DrugOstatRegistry_id,
						pmUser_id := :pmUser_id);
				";
				$queryParams = array(
					'EvnReceptDrugOstReg_id' => !empty($resp[0]['EvnReceptDrugOstReg_id']) ? $resp[0]['EvnReceptDrugOstReg_id'] : NULL,
					'EvnRecept_id' => $data['EvnRecept_id'],
					'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				$res = $this->queryResult($query, $queryParams);

				if ( !is_array($res) || count($res) == 0 ) {
					$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении связи рецепта и строки разнарядки'));
					$trans_good = false;
				} else if ( !empty($res[0]['Error_Msg']) ) {
					$response = array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
					$trans_good = false;
				}
			}
		}

		if (
			$trans_good === true && !$is_kardio && !empty($g_options['select_drug_from_list']) && in_array($g_options['select_drug_from_list'], array('allocation', 'request'/*, 'request_and_allocation'*/))
		) { //корректировка данных о количестве выписанных медикаментов в разнарядке заявки
			$resp = $this->updateDrugRequestPersonOrder('add', array(
				'EvnRecept_id' => $data['EvnRecept_id']
			));
			if (!$this->isSuccessful($resp)) {
				$response = array(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
				$trans_good = false;
			}
		}

		if ( $trans_good === true && ($this->getRegionNick() == 'ufa' || $options['recepts']['unique_ser_num'] === true || $options['recepts']['unique_ser_num'] == '1' || $options['recepts']['unique_ser_num'] == 'true') ) {
			$check_recept_ser_num = $this->checkReceptSerNum($data);

			if ( $check_recept_ser_num == -1 ) {
				$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта'));
				$trans_good = false;
			}
			else if ( $check_recept_ser_num > 0 ) {
				$response = array(array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее'));
				$trans_good = false;
			}
		}

		if ( $trans_good === true ) {
			$this->db->trans_commit();

			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->saveApprovalList(array(
				'ApprovalList_ObjectName' => 'EvnRecept',
				'ApprovalList_ObjectId' => $data['EvnRecept_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		else {
			$this->db->trans_rollback();
		}

		//если в процессе сохранения рецепта, было сохранено назначение то добавляем его идентификатор к ответу
		if (!empty($response[0]['EvnRecept_id']) && !empty($data['EvnCourseTreatDrug_id'])) {
			$response[0]['EvnCourseTreatDrug_id'] = $data['EvnCourseTreatDrug_id'];
		}

		return $response;
	}

    /**
     * Получение наименование поля для обозначения кода мед. персонала
     */
	function getMedPersonalCodeField() {
		return 'MedPersonal_Code';
	}

    /**
     * @return bool
     */
    function getReceptFormList(){
        $query = "
			select
				ReceptForm_id as \"ReceptForm_id\",
				ReceptForm_Code as \"ReceptForm_Code\",
				ReceptForm_Name as \"ReceptForm_Name\",
				to_char(ReceptForm_begDate, 'DD.MM.YYYY') as \"ReceptForm_begDate\",

				to_char(ReceptForm_endDate, 'DD.MM.YYYY') as \"ReceptForm_endDate\"

			from
				v_ReceptForm 

			where
				/*ReceptForm_endDate is null
				and*/ ReceptForm_IsPrivilege = 2
				and (
					(:Region_id = 101 and Region_id = 101)
					or (:Region_id <> 101)
				)
        ";
		$params = array(
			'Region_id' => $this->getRegionNumber()
		);
		//echo getDebugSQL($query, $params);exit;
        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }
	
	/**
     * @return bool
     */
	function getReceptGenFormList($data){
		$ReceptFormIDS = "5,2,3,8";
		if(!empty($data['group']) && $data['group'] == 'narco'){
			$ReceptFormIDS = "5,8";
		}
		$query = "
			select
				ReceptForm_id as \"ReceptForm_id\",
				ReceptForm_Code as \"ReceptForm_Code\",
				ReceptForm_Name as \"ReceptForm_Name\"
			from
				v_ReceptForm 

			where
				ReceptForm_id in ({$ReceptFormIDS})
        ";
        $result = $this->db->query($query, array());
        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
	}
	
	/**
     * @return bool
	*/
	function getReceptUrgencyList(){
		$query = "
			select
				ReceptUrgency_id as \"ReceptUrgency_id\",
				ReceptUrgency_Code as \"ReceptUrgency_Code\",
				ReceptUrgency_Name as \"ReceptUrgency_Name\"
			from
				v_ReceptUrgency 

        ";
        $result = $this->db->query($query, array());
        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
	}
	
    /**
     * Получение формы рецепта
     */
    function getReceptForm($data){
        $query = "
           select
              R.ReceptForm_id as \"ReceptForm_id\",
              R.ReceptForm_Code as \"ReceptForm_Code\",
              to_char(ER.EvnRecept_setDate, 'YYYY-MM-DD HH24:MI:SS') as \"EvnRecept_setDate\"

            from
              v_EvnRecept ER 

            left join ReceptForm R  on R.ReceptForm_id = ER.ReceptForm_id

            where
              ER.EvnRecept_id = :EvnRecept_id
        ";
        $result = $this->db->query($query, array('EvnRecept_id' => $data["EvnRecept_id"]));
        if ( is_object($result) ) {

            return $result->result('array');
        }
        else {
            return false;
        }
    }
	
	/**
     * Получение формы рецепта
     */
	function getReceptGeneralForm($data)
	{
		$query = "
            select
              R.ReceptForm_Code as \"ReceptForm_Code\",
			  R.ReceptForm_id as \"ReceptForm_id\",
              to_char(coalesce(ER.EvnReceptGeneral_setDate, ER.EvnReceptGeneral_begDate), 'YYYY-MM-DD') as \"EvnReceptGeneral_setDate\"
            from
              v_EvnReceptGeneral ER 

            left join ReceptForm R  on R.ReceptForm_id = ER.ReceptForm_id

            where
              ER.EvnReceptGeneral_id = :EvnReceptGeneral_id
        ";
        $result = $this->db->query($query, array('EvnReceptGeneral_id' => $data["EvnReceptGeneral_id"]));
        if ( is_object($result) ) {

            return $result->result('array');
        }
        else {
            return false;
        }
	}

	/**
	 * Получить cписок ЛС, заявленных в рамках ЛЛО
	 */
	function loadPersonDrugRequestPanel($data)
	{
		$query = "
			select
				dr.DrugRequest_id as \"DrugRequest_id\",
				coalesce(mnn.\"DrugComplexMnn_RusName\",drug.\"Drug_Name\") as \"ls\",
				status.DrugRequestStatus_Name as \"DrugRequestStatus_Name\",
				to_char(period.DrugRequestPeriod_begDate, 'dd.mm.yyyy') as \"DrugRequestPeriod_begDate\",
				to_char(period.DrugRequestPeriod_endDate, 'dd.mm.yyyy') as \"DrugRequestPeriod_endDate\",
				med.\"FIO\" as \"FIO\",
				coalesce(drpo.DrugRequestPersonOrder_OrdKolvo, 0) as \"DrugRequestPersonOrder_OrdKolvo\",
				coalesce(drpo.DrugRequestPersonOrder_Kolvo, 0) as \"DrugRequestPersonOrder_Kolvo\",
				(coalesce(drpo.DrugRequestPersonOrder_OrdKolvo, 0) - coalesce(drpo.DrugRequestPersonOrder_Kolvo, 0))  as \"ostatok\"
			from
				v_DrugRequest dr
				inner join v_DrugRequestPersonOrder drpo on drpo.DrugRequest_id = dr.DrugRequest_id
				left join v_DrugRequestPeriod period on period.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join v_DrugRequestStatus status on status.DrugRequestStatus_id = dr.DrugRequestStatus_id
				left join lateral (
					select DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\" from rls.DrugComplexMnn where DrugComplexMnn_id = drpo.DrugComplexMnn_id limit 1
				) mnn
				on true
				left join lateral (
					select Drug_Name as \"Drug_Name\" from v_Drug where Drug_id = drpo.Drug_id limit 1
				) drug
				on true
				left join lateral (
					select (Person_SurName || ' ' || Person_FirName || ' ' || Person_SecName) as \"FIO\" from v_MedPersonal where MedPersonal_id = drpo.MedPersonal_id limit 1
				) med
				on true
			where
				cast(drpo.DrugRequestPersonOrder_insDT as date) = dbo.tzGetDate()
				and drpo.Person_id = :Person_id
				and drpo.DrugRequestExceptionType_id is null
				and coalesce(drpo.DrugComplexMnn_id, drpo.Drug_id) is not null
        ";
		$result = $this->db->query($query, array('Person_id' => $data["Person_id"]));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Проверка срока годности рецепта на определенную дату
	 */
	function checkReceptValidByDate($data) {
		$checkResult = 'false';
		$result = null;

		if (!empty($data['EvnReceptGeneral_id'])) {
			$query = "
				select
					datediff('day', cast(:Date as date), end_date.dt) as \"RemainedDays\",
					rdt.ReceptDelayType_Code as \"ReceptDelayType_Code\"
				from
					v_EvnReceptGeneral erg 
					left join ReceptDelayType rdt  on rdt.ReceptDelayType_id = erg.ReceptDelayType_id
					left join dbo.ReceptValid rv on rv.ReceptValid_id = erg.ReceptValid_id
					LEFT JOIN LATERAL (
						select
							(case
								when ReceptValid_Name = '5 дней' then interval '5 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = '10 дней' then interval '10 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = '14 дней' then interval '14 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = '60 дней' then interval '60 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = '30 дней' then interval '30 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = '90 дней' then interval '90 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = '15 дней' then interval '15 day' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = 'Месяц' then interval '1 month' + erg.EvnReceptGeneral_setDate
								when ReceptValid_Name = 'Три месяца' then interval '3 month' + erg.EvnReceptGeneral_setDate
								else erg.EvnReceptGeneral_setDate
							end) as dt
					) end_date ON true
				where
					erg.EvnReceptGeneral_id = :EvnReceptGeneral_id;
			";
			$result = $this->db->query($query, $data);
		} else {
			$query = "
				select
					datediff('day', 
                    cast(:Date as date)
                    , end_date.dt) as \"RemainedDays\",
					rdt.ReceptDelayType_Code as \"ReceptDelayType_Code\"
				from
					v_EvnRecept er 
					left join ReceptDelayType rdt  on rdt.ReceptDelayType_id = er.ReceptDelayType_id
					left join dbo.ReceptValid rv on rv.ReceptValid_id = er.ReceptValid_id
					LEFT JOIN LATERAL (
						select
							(case
								when ReceptValid_Name = '5 дней' then interval '5 day' + er.EvnRecept_setDate
								when ReceptValid_Name = '10 дней' then interval '10 day' + er.EvnRecept_setDate
								when ReceptValid_Name = '14 дней' then interval '14 day' + er.EvnRecept_setDate
								when ReceptValid_Name = '60 дней' then interval '60 day' + er.EvnRecept_setDate
								when ReceptValid_Name = '30 дней' then interval '30 day' + er.EvnRecept_setDate
								when ReceptValid_Name = '90 дней' then interval '90 day' + er.EvnRecept_setDate
								when ReceptValid_Name = '15 дней' then interval '15 day' + er.EvnRecept_setDate
								when ReceptValid_Name = 'Месяц' then interval '1 month' + er.EvnRecept_setDate
								when ReceptValid_Name = 'Три месяца' then interval '3 month' + er.EvnRecept_setDate
								else er.EvnRecept_setDate
							end) as dt
					) end_date ON true
				where
					er.EvnRecept_id = :EvnRecept_id;
			";
			$result = $this->db->query($query, $data);
		}

		if ( is_object($result) ) {
			$result = $result->result('array');
			if (count($result) > 0 && isset($result[0]['RemainedDays'])) {
				$checkResult = $result[0]['ReceptDelayType_Code'] == 1 || $result[0]['RemainedDays'] >= 0 ? 'true' : 'false';
			} else {
				$checkResult = 'error';
			}
		} else {
			$checkResult = 'error';
		}

		return $checkResult;
	}

	/**
	 * Получение остатков медикамента по заявке
	 */
	function getDrugRequestRowOstat($data) {
		if (empty($data['DrugRequestRow_id'])) {
			return $this->createError('', 'Не была передана заявка врача');
		}
		$params = array('DrugRequestRow_id' => $data['DrugRequestRow_id']);

		$query = "
			select 
				drr.DrugRequestRow_Kolvo - ER.EvnRecept_SumKolvo as \"DrugRequestRowOstat_Kolvo\"
			from
				v_DrugRequestRow drr 

				LEFT JOIN LATERAL(

					select COALESCE(sum(EvnRecept_Kolvo),0) as EvnRecept_SumKolvo

					from v_EvnRecept 

					where DrugRequestRow_id = drr.DrugRequestRow_id
				) ER ON true
			where drr.DrugRequestRow_id = :DrugRequestRow_id
			limit 1
		";

		$response = $this->queryResult($query, $params);
		if (!$response) {
			return $this->createError('', 'Ошибка при получении остатков по заявке на медикаменты');
		}
		return $response;
	}

	/**
	 * Получение системного наименования
	 */
	function getDrugFinanceSysNick($data) {
		$params = array('DrugFinance_id' => $data['DrugFinance_id']);
		$query = "
			select DrugFinance_SysNick as \"DrugFinance_SysNick\"
			from v_DrugFinance 

			where DrugFinance_id = :DrugFinance_id
            limit 1
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Поиск рецепта по данным со штрих-кода
	 */
	function SearchReceptFromBarcode($data) {
        $params = array();

        if(isset($data['EvnRecept_id'])) {
            $where = "where ER.EvnRecept_id = :EvnRecept_id";
            $params['EvnRecept_id'] = $data['EvnRecept_id'];
        } else {
            $where = "where ER.EvnRecept_Ser = :EvnRecept_Ser and cast(ER.EvnRecept_Num as bigint) = :EvnRecept_Num";
            $params['EvnRecept_Ser'] = !empty($data['EvnRecept_Ser']) ? $data['EvnRecept_Ser'] : null;
            $params['EvnRecept_Num'] = !empty($data['EvnRecept_Num']) ? $data['EvnRecept_Num'] : null;
        }

		$query = "
			select
			    ER.EvnRecept_id as \"EvnRecept_id\",
			    D.Drug_id as \"Drug_id\",
				COALESCE(ProtoYesNo.YesNo_Code, -1) as \"drug_is_kek\",

				to_char(ER.EvnRecept_setDT, 'DD.MM.YYYY') as \"evn_recept_set_date\",

				COALESCE(RV.ReceptValid_Code, 0) as \"recept_valid_code\",

				COALESCE(PT.PrivilegeType_Code, 0) as \"privilege_type_code\",

				--COALESCE(DRls.Drug_Fas, D.Drug_Fas) as \"Drug_Fas\",

				COALESCE(DRls.Drug_Fas, D.Drug_Fas,0) as \"Drug_Fas\",
				COALESCE(DRls.Drug_Code::text,'') as \"Drug_Code\",

				cast(ER.EvnRecept_Kolvo as numeric(10, 2)) as \"EvnRecept_Kolvo\",
				COALESCE(
					DRls.Drug_Dose,
					D.Drug_DoseQ,
					COALESCE(cast(nullif(D.Drug_Vol, 0) as varchar(10)), '') || COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)), '')

				 ) as \"drug_dose\",
				COALESCE(cast(cast(PS.Person_Snils as bigint) as varchar(13)),'') as \"person_snils\",

				--COALESCE(PS.Person_Snils, '') as person_snils,

				COALESCE(

					case
						when MnnYesNo.YesNo_Code = 1 then COALESCE(DMC.DrugMnnCode_Code::text, DM.DrugMnn_Code::text, '0')
						when MnnYesNo.YesNo_Code = 0 then COALESCE(DTC.DrugTorgCode_Code::text, D.Drug_Code::text, '0')
					end, '0'
				 ) as \"drug_mnn_torg_code\",
				COALESCE(MnnYesNo.YesNo_Code, -1) as \"drug_is_mnn\",

				COALESCE(RD.ReceptDiscount_Code, 0) as \"recept_discount_code\",

				COALESCE(RF.ReceptFinance_Code, 0) as \"recept_finance_code\",

				CAST(RTRIM(COALESCE(DG.Diag_Code, '')) as varchar(7)) as \"diag_code\",

				CAST(COALESCE(ER.EvnRecept_Ser, '') as varchar(14)) as \"evn_recept_ser\",

				CAST(COALESCE(replace(ltrim(replace(ER.EvnRecept_Num, '0', ' ')), ' ', '0'), '') as varchar(20)) as \"evn_recept_num\",

				right('0000000' || COALESCE(cast(L.Lpu_Ouz as varchar(7)), ''), 7) as \"lpu_code\",

				COALESCE(L.Lpu_OGRN, '0') as \"lpu_ogrn\",

				CAST(COALESCE(MSF." . $this->getMedPersonalCodeField() .", '') as varchar(6)) as \"medpersonal_code\",

				--L.Lpu_Name as EvnRecept_LpuName,
				O.Org_Name as \"EvnRecept_LpuName\",

				COALESCE(DRls.DrugTorg_Name, D.Drug_Name) as \"EvnRecept_DrugTorgName\",

				MSF.Person_Fio as \"EvnRecept_MedPersonal\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				WDCIT.WhsDocumentCostItemType_Name as \"EvnRecept_WhsDocumentCostItemType\",
				WDCIT.WhsDocumentCostItemType_id as \"EvnRecept_WhsDocumentCostItemType_id\",
				PT.PrivilegeType_Name as \"EvnRecept_Privilege\",
				PT.PrivilegeType_Code as \"EvnRecept_PrivilegeC\",
				RD.ReceptDiscount_Name as \"EvnRecept_Discount\",
				RF.ReceptFinance_id as \"EvnRecept_Finance_id\",
				ER.DrugFinance_id as \"EvnRecept_DrugFinance_id\",
				DF.DrugFinance_Name \"EvnRecept_DrugFinance\",
				RF.ReceptFinance_Name as \"EvnRecept_Finance\",
				ER.DrugComplexMnn_id as \"DrugComplexMnn_id\"
			from v_EvnRecept ER 


			left join v_YesNo ProtoYesNo  on ProtoYesNo.YesNo_id = ER.EvnRecept_IsKek

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ER.ReceptValid_id

			left join v_PrivilegeType PT  on PT.PrivilegeType_id = ER.PrivilegeType_id

			left join v_PersonState PS  on PS.Person_id = ER.Person_id

			left join v_YesNo MnnYesNo  on MnnYesNo.YesNo_id = ER.EvnRecept_IsMnn

			left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = ER.ReceptDiscount_id

			left join DrugFinance DF  on DF.DrugFinance_id = ER.DrugFinance_id

			left join ReceptFinance RF  on RF.ReceptFinance_id = ER.ReceptFinance_id

			left join v_Diag DG  on DG.Diag_id = ER.Diag_id

			left join v_Lpu L  on L.Lpu_id = ER.Lpu_id

			left join Lpu L2  on L2.Lpu_id = ER.Lpu_id

			left join Org O  on O.Org_id = L2.Org_id

			left join WhsDocumentCostItemType WDCIT  on WDCIT.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id

			LEFT JOIN LATERAL (

					select 
						 MedPersonal_Code
						,MedPersonal_TabCode
						,Person_FIO
					from v_MedStaffFact 

					where MedPersonal_id = ER.MedPersonal_id
						and LpuSection_id = ER.LpuSection_id
						and COALESCE(WorkData_begDate, '1970-01-01') <= ER.EvnRecept_setDate

						and COALESCE(WorkData_endDate, '2030-12-31') >= ER.EvnRecept_setDate

						and COALESCE(WorkData_dlobegDate, '1970-01-01') <= ER.EvnRecept_setDate

						and COALESCE(WorkData_dloendDate, '2030-12-31') >= ER.EvnRecept_setDate

					order by MedPersonal_Code desc
                    limit 1
			) MSF ON true

			left join rls.v_Drug DRls  on DRls.Drug_id = ER.Drug_rlsid

			left join v_Drug D  on D.Drug_id = ER.Drug_id

			left join rls.v_DrugNomen DN  on DN.Drug_id = DRls.Drug_id

			left join rls.v_DrugMnnCode DMC  on DMC.DrugMnnCode_id = DN.DrugMnnCode_id

			left join rls.v_DrugTorgCode DTC  on DTC.DrugTorgCode_id = DN.DrugTorgCode_id

			left join v_DrugMnn DM  on DM.DrugMnn_id = D.DrugMnn_id

            left join rls.Drug D2  on D2.Drug_id = ER.DrugComplexMnn_id

			left join rls.DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = COALESCE(ER.DrugComplexMnn_id, DRls.DrugComplexMnn_id)


			{$where}
		";
		$result = $this->db->query($query,$params);
		//echo getDebugSQL($query,$params);die;


		if(is_object($result)) {
			$result = $result->result('array');
			if (is_array($result) && count($result) > 0) {
				//var_dump($result);die;
				if ($result[0]['drug_is_mnn'] == 0)
					$result[0]['drug_is_mnn'] = 1;
				else if ($result[0]['drug_is_mnn'] == 1)
					$result[0]['drug_is_mnn'] = 0;
				$result[0]['medpersonal_code'] = str_pad($result[0]['medpersonal_code'], 6, '0', STR_PAD_LEFT);
				//var_dump($result[0]);die;
				if ($result[0]['Drug_Fas'] == 0) {
					$result[0]['Drug_Fas'] = 1;
				}
				$result[0]['drug_dose_count'] = $result[0]['Drug_Fas'] * $result[0]['EvnRecept_Kolvo'] * 1000;

				if (($result[0]['recept_discount_code'] >= 1) && ($result[0]['recept_discount_code'] <= 2)) {
					$result[0]['recept_discount_code'] = $result[0]['recept_discount_code'] - 1;
				}

				$result[0]['EvnRecept_SerNumDate'] = $result[0]['evn_recept_ser'] . ' №' . $result[0]['evn_recept_num'] . ' от ' . $result[0]['evn_recept_set_date'];

				return $result;
			}
		}

		return array('Error_Msg' => 'Рецепт не найден', 'success' => false);
	}

	/**
	*	Получение данных по рецепту для обеспечения
	*/
	function searchReceptForProvide($data){
		$from = '';
		$params = array();
		//return $this->SearchReceptFromBarcode($data);
		if(isset($data['EvnRecept_id']) && $data['EvnRecept_id'] > 0)
		{
			//$from = 'v_EvnRecept ER';
			//$params['EvnRecept_id'] = $data['EvnRecept_id'];
			return $this->SearchReceptFromBarcode($data);
		}
		else if(isset($data['EvnReceptGeneral_id']) && $data['EvnReceptGeneral_id'] > 0)
		{
			$params['EvnReceptGeneral_id'] = $data['EvnReceptGeneral_id'];
			$query = "
				select
				    ER.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
				    D.Drug_id as \"Drug_id\",
					COALESCE(ProtoYesNo.YesNo_Code, -1) as \"drug_is_kek\",

					to_char(ER.EvnReceptGeneral_setDT, 'DD.MM.YYYY') as \"evn_recept_set_date\",

					COALESCE(RV.ReceptValid_Code, 0) as \"recept_valid_code\",

					COALESCE(PT.PrivilegeType_Code, 0) as \"privilege_type_code\",

					--COALESCE(DRls.Drug_Fas, D.Drug_Fas) as \"Drug_Fas\",

					COALESCE(DRls.Drug_Fas, D.Drug_Fas,0) as \"Drug_Fas\",
					COALESCE(DRls.Drug_Code,'') as \"Drug_Code\",

					cast(ER.EvnReceptGeneral_Kolvo as numeric(10, 2)) as \"EvnRecept_Kolvo\",
					COALESCE(
						DRls.Drug_Dose,
						D.Drug_DoseQ,
						COALESCE(cast(nullif(D.Drug_Vol, 0) as varchar(10)), '') || COALESCE(' ' || cast(nullif(D.Drug_Mass, 0) as varchar(10)), '')

					 ) as \"drug_dose\",
					COALESCE(cast(cast(PS.Person_Snils as bigint) as varchar(13)),'') as \"person_snils\",

					--COALESCE(PS.Person_Snils, '') as \"person_snils\",

					COALESCE(

						case
							when MnnYesNo.YesNo_Code = 1 then COALESCE(DMC.DrugMnnCode_Code::text, DM.DrugMnn_Code::text, '0')
							when MnnYesNo.YesNo_Code = 0 then COALESCE(DTC.DrugTorgCode_Code::text, D.Drug_Code::text, '0')
						end, 0
					 ) as \"drug_mnn_torg_code\",
					COALESCE(MnnYesNo.YesNo_Code, -1) as \"drug_is_mnn\",

					COALESCE(RD.ReceptDiscount_Code, 0) as \"recept_discount_code\",

					COALESCE(RF.ReceptFinance_Code, 0) as \"recept_finance_code\",

					CAST(RTRIM(COALESCE(DG.Diag_Code, '')) as varchar(7)) as \"diag_code\",

					CAST(COALESCE(ER.EvnReceptGeneral_Ser, '') as varchar(14)) as \"evn_recept_ser\",

					CAST(COALESCE(replace(ltrim(replace(ER.EvnReceptGeneral_Num, '0', ' ')), ' ', '0'), '') as varchar(20)) as \"evn_recept_num\",

					right('0000000' || COALESCE(cast(L.Lpu_Ouz as varchar(7)), ''), 7) as \"lpu_code\",

					COALESCE(L.Lpu_OGRN, '0') as \"lpu_ogrn\",

					CAST(COALESCE(MSF." . $this->getMedPersonalCodeField() .", '') as varchar(6)) as \"medpersonal_code\",

					--L.Lpu_Name as EvnRecept_LpuName,
					O.Org_Name as \"EvnRecept_LpuName\",

					COALESCE(DRls.DrugTorg_Name, D.Drug_Name) as \"EvnRecept_DrugTorgName\",

					MSF.Person_Fio as \"EvnRecept_MedPersonal\",
					DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
					WDCIT.WhsDocumentCostItemType_Name as \"EvnRecept_WhsDocumentCostItemType\",
					WDCIT.WhsDocumentCostItemType_id as \"EvnRecept_WhsDocumentCostItemType_id\",
					PT.PrivilegeType_Name as \"EvnRecept_Privilege\",
					PT.PrivilegeType_Code as \"EvnRecept_PrivilegeC\",
					RD.ReceptDiscount_Name as \"EvnRecept_Discount\",
					RF.ReceptFinance_id as \"EvnRecept_Finance_id\",
					ER.DrugFinance_id as \"EvnRecept_DrugFinance_id\",
					DF.DrugFinance_Name \"EvnRecept_DrugFinance\",
					RF.ReceptFinance_Name as \"EvnRecept_Finance\",
					ER.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				from v_EvnReceptGeneral ER 


				left join v_YesNo ProtoYesNo  on ProtoYesNo.YesNo_id = ER.EvnReceptGeneral_IsKek

				left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ER.ReceptValid_id

				left join v_PrivilegeType PT  on PT.PrivilegeType_id = ER.PrivilegeType_id

				left join v_PersonState PS  on PS.Person_id = ER.Person_id

				left join v_YesNo MnnYesNo  on MnnYesNo.YesNo_id = ER.EvnReceptGeneral_IsMnn

				left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = ER.ReceptDiscount_id

				left join DrugFinance DF  on DF.DrugFinance_id = ER.DrugFinance_id

				left join ReceptFinance RF  on RF.ReceptFinance_id = ER.ReceptFinance_id

				left join v_Diag DG  on DG.Diag_id = ER.Diag_id

				left join v_Lpu L  on L.Lpu_id = ER.Lpu_id

				left join Lpu L2  on L2.Lpu_id = ER.Lpu_id

				left join Org O  on O.Org_id = L2.Org_id

				left join WhsDocumentCostItemType WDCIT  on WDCIT.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id

				LEFT JOIN LATERAL (

						select 
							 MedPersonal_Code
							,MedPersonal_TabCode
							,Person_FIO
						from v_MedStaffFact 

						where MedPersonal_id = ER.MedPersonal_id
							and LpuSection_id = ER.LpuSection_id
							and COALESCE(WorkData_begDate, '1970-01-01') <= ER.EvnReceptGeneral_setDate

							and COALESCE(WorkData_endDate, '2030-12-31') >= ER.EvnReceptGeneral_setDate

							and COALESCE(WorkData_dlobegDate, '1970-01-01') <= ER.EvnReceptGeneral_setDate

							and COALESCE(WorkData_dloendDate, '2030-12-31') >= ER.EvnReceptGeneral_setDate

						order by MedPersonal_Code desc
                        limit 1
				) MSF ON true

				left join rls.v_Drug DRls  on DRls.Drug_id = ER.Drug_rlsid

				left join v_Drug D  on D.Drug_id = ER.Drug_id

				left join rls.v_DrugNomen DN  on DN.Drug_id = DRls.Drug_id

				left join rls.v_DrugMnnCode DMC  on DMC.DrugMnnCode_id = DN.DrugMnnCode_id

				left join rls.v_DrugTorgCode DTC  on DTC.DrugTorgCode_id = DN.DrugTorgCode_id

				left join v_DrugMnn DM  on DM.DrugMnn_id = D.DrugMnn_id

	            left join rls.Drug D2  on D2.Drug_id = ER.DrugComplexMnn_id

				left join rls.DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = COALESCE(ER.DrugComplexMnn_id, DRls.DrugComplexMnn_id)


				where ER.EvnReceptGeneral_id = :EvnReceptGeneral_id
			";
			$result = $this->db->query($query,$params);
			if(is_object($result)) {
				$result = $result->result('array');
				if (is_array($result) && count($result) > 0) {
					//var_dump($result);die;
					if ($result[0]['drug_is_mnn'] == 0)
						$result[0]['drug_is_mnn'] = 1;
					else if ($result[0]['drug_is_mnn'] == 1)
						$result[0]['drug_is_mnn'] = 0;
					$result[0]['medpersonal_code'] = str_pad($result[0]['medpersonal_code'], 6, '0', STR_PAD_LEFT);
					//var_dump($result[0]);die;
					if ($result[0]['Drug_Fas'] == 0) {
						$result[0]['Drug_Fas'] = 1;
					}
					$result[0]['drug_dose_count'] = $result[0]['Drug_Fas'] * $result[0]['EvnRecept_Kolvo'] * 1000;

					if (($result[0]['recept_discount_code'] >= 1) && ($result[0]['recept_discount_code'] <= 2)) {
						$result[0]['recept_discount_code'] = $result[0]['recept_discount_code'] - 1;
					}

					$result[0]['EvnRecept_SerNumDate'] = $result[0]['evn_recept_ser'] . ' №' . $result[0]['evn_recept_num'] . ' от ' . $result[0]['evn_recept_set_date'];

					return $result;
				}
			}

			return array('Error_Msg' => 'Рецепт не найден', 'success' => false);
		}
		else
			return array(array('Error_Msg' => 'Отсутствует идентификатор рецепта', 'success' => false));


	}

	/**
	 * Снять рецепт с обслуживания
	 */
	function pullOffServiceRecept($data) {

		$this->beginTransaction();
		try {
           
           	// Проверяем наличие акта списания
			$query = "
				select
					wdu.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					wduaro.WhsDocumentUcActReceptOut_id as \"WhsDocumentUcActReceptOut_id\",
					wduarl.WhsDocumentUcActReceptList_id as \"WhsDocumentUcActReceptList_id\"
				from
					v_WhsDocumentUc wdu 

					inner join v_WhsDocumentUcActReceptOut wduaro  on wduaro.WhsDocumentUc_id = wdu.WhsDocumentUc_id

					inner join v_WhsDocumentUcActReceptList wduarl  on wduarl.WhsDocumentUcActReceptOut_id = wduaro.WhsDocumentUcActReceptOut_id

					inner join v_EvnRecept er  on er.EvnRecept_id = wduarl.EvnRecept_id

				where
					wdu.WhsDocumentType_id = 24 -- Тип документа Акт о снятии рецепта с обслуживания 
					and wdu.WhsDocumentStatusType_id = 1 -- Статус документа Новый
					and er.Lpu_id = :Lpu_id
					and wduaro.Org_nid = :Org_id
			";
			$result = $this->db->query($query, array('Lpu_id'=>$data['Lpu_id'], 'Org_id'=>$data['Org_id']));

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при проверке существующего акта', 500);
			}

			$result = $result->result('array');
			if (!(count($result) > 0)) {

				// Получаем номер для создания документа с типом акт снятия
				$query = "
					select
						max(wdu.WhsDocumentUc_Num) as \"WhsDocumentUc_maxNum\"
					from
						v_WhsDocumentUc wdu 

					where
						wdu.WhsDocumentType_id = 24 -- Тип документа Акт о снятии рецепта с обслуживания 
				";
				$resMaxNum = $this->db->query($query, $data);
				if ( !is_object($resMaxNum) ) {
					throw new Exception('Ошибка при получении номера акта', 500);
				} else {
					$resMaxNum = $resMaxNum->result('array');
					if (count($resMaxNum) > 0 && !empty($resMaxNum[0]['WhsDocumentUc_maxNum'])) {
						$WhsDocumentUc_Num = $resMaxNum[0]['WhsDocumentUc_maxNum'];
					} else {
						$WhsDocumentUc_Num = 1;
					}
				}

				$query = "
					select WhsDocumentUc_id as \"WhsDocumentUc_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_WhsDocumentUc_ins(
						WhsDocumentUc_id := null,
						WhsDocumentUc_pid := null,
						WhsDocumentUc_Name := :WhsDocumentUc_Name,
						WhsDocumentUc_Num := :WhsDocumentUc_Num,
						WhsDocumentType_id := :WhsDocumentType_id,
						WhsDocumentUc_Date := dbo.tzGetDate(),
						WhsDocumentUc_Sum := null,
						WhsDocumentStatusType_id := :WhsDocumentStatusType_id,
						Org_aid := :Org_aid,
						WhsDocumentClass_id := null,
						pmUser_id := :pmUser_id);


				";
				$queryParams = array(
					'WhsDocumentUc_Name' => 'Акт о снятии рецепта с обслуживания от '.date("m.d.y"),
					'WhsDocumentUc_Num' => $WhsDocumentUc_Num,
					'WhsDocumentType_id' => 24,
					'WhsDocumentStatusType_id' => 1,
					'Org_aid' => $data['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				//echo getDebugSQL($query,$queryParams);die;
				$res = $this->queryResult($query, $queryParams);

				if ( !is_array($res) || count($res) == 0 ) {
					throw new Exception('Ошибка при создании акта', 500);
				} else if ( !empty($res[0]['Error_Msg']) ) {
					throw new Exception('Ошибка при создании акта - '.$res[0]['Error_Msg'], 500);
				} else if ( !($res[0]['WhsDocumentUc_id'] > 0) ) {
					throw new Exception('Ошибка при создании акта', 500);
				}

				$data['WhsDocumentUc_id'] = $res[0]['WhsDocumentUc_id'];
				$result[0] = array('WhsDocumentUcActReceptOut_id'=>null,'WhsDocumentUcActReceptList_id'=>null);
				$proctype = 'ins';
			} else {
				$proctype = 'upd';
				$data['WhsDocumentUc_id'] = $result[0]['WhsDocumentUc_id'];
			}

			$query = "
				select WhsDocumentUcActReceptOut_id as \"WhsDocumentUcActReceptOut_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                   from dbo.p_WhsDocumentUcActReceptOut_{$proctype}(
					WhsDocumentUcActReceptOut_id := :WhsDocumentUcActReceptOut_id,
					WhsDocumentUc_id := :WhsDocumentUc_id,
					Org_nid := :Org_nid,
					pmUser_createID := :pmUser_id,
					Org_mid := null,
					pmUser_signID := :pmUser_signID,
					WhsDocumentUcActReceptOut_setDT := :WhsDocumentUcActReceptOut_setDT,
					pmUser_id := :pmUser_id);
			";
			$queryParams = array(
				'WhsDocumentUcActReceptOut_id' => (((count($result) > 0)&&isset($result[0]['WhsDocumentUcActReceptOut_id']))?$result[0]['WhsDocumentUcActReceptOut_id']:null),
				'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
				'Org_nid' => $data['Org_id'],
				'WhsDocumentUcActReceptOut_setDT' => $data['WhsDocumentUcActReceptOut_setDT'],
				'pmUser_signID' => $data['pmUser_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$resl = $this->queryResult($query, $queryParams);

			if ( !is_array($resl) || count($resl) == 0 ) {
				throw new Exception('Ошибка при создании акта', 500);
			} else if ( !empty($resl[0]['Error_Msg']) ) {
				throw new Exception('Ошибка при создании акта - '.$resl[0]['Error_Msg'], 500);
			} else if ( !($resl[0]['WhsDocumentUcActReceptOut_id'] > 0) ) {
				throw new Exception('Ошибка при создании акта', 500);
			}

			$data['WhsDocumentUcActReceptOut_id'] = $resl[0]['WhsDocumentUcActReceptOut_id'];

			$query = "
				select WhsDocumentUcActReceptList_id as \"WhsDocumentUcActReceptList_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_WhsDocumentUcActReceptList_{$proctype}(
					WhsDocumentUcActReceptList_id := :WhsDocumentUcActReceptList_id,
					WhsDocumentUcActReceptOut_id := :WhsDocumentUcActReceptOut_id,
					EvnRecept_id := :EvnRecept_id,
					WhsDocumentUcActReceptList_outCause := :WhsDocumentUcActReceptList_outCause,
					Evn_id := null,
					pmUser_id := :pmUser_id);


			";
			$queryParams = array(
				'WhsDocumentUcActReceptList_id' => (((count($result) > 0)&&isset($result[0]['WhsDocumentUcActReceptList_id']))?$result[0]['WhsDocumentUcActReceptList_id']:null),
				'WhsDocumentUcActReceptOut_id' => $data['WhsDocumentUcActReceptOut_id'],
				'EvnRecept_id' => $data['EvnRecept_id'],
				'WhsDocumentUcActReceptList_outCause' => $data['WhsDocumentUcActReceptList_outCause'],
				'pmUser_id' => $data['pmUser_id']
			);
			$reslt = $this->queryResult($query, $queryParams);

			if ( !is_array($reslt) || count($reslt) == 0 ) {
				throw new Exception('Ошибка при создании акта', 500);
			} else if ( !empty($reslt[0]['Error_Msg']) ) {
				throw new Exception('Ошибка при создании акта - '.$reslt[0]['Error_Msg'], 500);
			} else if ( !($reslt[0]['WhsDocumentUcActReceptList_id'] > 0) ) {
				throw new Exception('Ошибка при создании акта', 500);
			}

			$data['WhsDocumentUcActReceptList_id'] = $reslt[0]['WhsDocumentUcActReceptList_id'];

			//проверяем наличие рецепта в ReceptOtov
			$query = "
				select 
                    ReceptOtov_id as \"ReceptOtov_id\",
                    Person_id as \"Person_id\",
                    PrivilegeType_id as \"PrivilegeType_id\",
                    Lpu_id as \"Lpu_id\",
                    MedPersonalRec_id as \"MedPersonalRec_id\",
                    Diag_id as \"Diag_id\",
                    EvnRecept_Ser as \"EvnRecept_Ser\",
                    EvnRecept_Num as \"EvnRecept_Num\",
                    EvnRecept_setDT as \"EvnRecept_setDT\",
                    EvnRecept_setDate as \"EvnRecept_setDate\",
                    ReceptFinance_id as \"ReceptFinance_id\",
                    ReceptValid_id as \"ReceptValid_id\",
                    OrgFarmacy_id as \"OrgFarmacy_id\",
                    Drug_id as \"Drug_id\",
                    EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
                    EvnRecept_obrDT as \"EvnRecept_obrDT\",
                    EvnRecept_otpDT as \"EvnRecept_otpDT\",
                    EvnRecept_Price as \"EvnRecept_Price\",
                    EvnRecept_id as \"EvnRecept_id\",
                    pmUser_insID as \"pmUser_insID\",
                    pmUser_updID as \"pmUser_updID\",
                    ReceptOtov_insDT as \"ReceptOtov_insDT\",
                    ReceptOtov_updDT as \"ReceptOtov_updDT\",
                    EvnRecept_Is7Noz as \"EvnRecept_Is7Noz\",
                    Person_Snils as \"Person_Snils\",
                    ReceptDelayType_id as \"ReceptDelayType_id\",
                    DrugFinance_id as \"DrugFinance_id\",
                    WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                    ReceptStatusType_id as \"ReceptStatusType_id\",
                    Drug_cid as \"Drug_cid\",
                    DrugComplexMNN_id as \"DrugComplexMNN_id\",
                    Drug_rlsid as \"Drug_rlsid\"
				from
					v_ReceptOtovUnSub 

				where
					EvnRecept_id = :EvnRecept_id
				limit 1
			";
			$queryParams = array(
				'EvnRecept_id' => $data['EvnRecept_id']
			);
			$result = $this->getFirstRowFromQuery($query, $queryParams);
			$receptotov_id = $result !== false && !empty($result['ReceptOtov_id']) ? $result['ReceptOtov_id'] : 0;

			//если записи о рецепте нет в ReceptOtov, добавляем её туда
			if ($receptotov_id == 0) {
				//получаем данные рецепта
				$query = "
					select
						er.EvnRecept_id as \"EvnRecept_id\",
						er.EvnRecept_Guid as \"EvnRecept_Guid\",
						er.Person_id as \"Person_id\",
						ps.Person_Snils as \"Person_Snils\",
						er.PrivilegeType_id as \"PrivilegeType_id\",
						er.Lpu_id as \"Lpu_id\",
						l.Lpu_Ogrn as \"Lpu_Ogrn\",
						er.MedPersonal_id as \"MedPersonal_id\",
						er.Diag_id as \"Diag_id\",
						er.EvnRecept_Ser as \"EvnRecept_Ser\",
						er.EvnRecept_Num as \"EvnRecept_Num\",
						to_char(er.EvnRecept_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnRecept_setDT\",

						to_char(er.EvnRecept_obrDT, 'YYYY-MM-DD HH24:MI:SS')||' '||to_char(er.EvnRecept_obrDT, 'HH24:MI:SS') as \"EvnRecept_obrDT\",


						to_char(er.EvnRecept_otpDT, 'YYYY-MM-DD HH24:MI:SS')||' '||to_char(er.EvnRecept_otpDT, 'HH24:MI:SS') as \"EvnRecept_otpDT\",


						er.ReceptFinance_id as \"ReceptFinance_id\",
						er.OrgFarmacy_oid as \"OrgFarmacy_oid\",
						er.Drug_rlsid  as \"Drug_id\",
						dn.DrugNomen_Code as \"Drug_Code\",
						er.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
						er.ReceptDelayType_id as \"ReceptDelayType_id\",
						er.EvnRecept_Is7Noz as \"EvnRecept_Is7Noz\",
						er.DrugFinance_id as \"DrugFinance_id\",
						er.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
						er.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
						er.WhsDocumentUc_id as \"WhsDocumentUc_id\",
						wr.ReceptWrong_id as \"ReceptWrong_id\"
					from
						v_EvnRecept er 

						left join v_PersonState ps  on ps.Person_id = er.Person_id

						left join v_Lpu l  on l.Lpu_id = er.Lpu_id

						left join ReceptWrong wr  on wr.EvnRecept_id = er.EvnRecept_id

						LEFT JOIN LATERAL (

							select 
								DrugNomen_Code
							from
								rls.v_DrugNomen dn 

							where
								dn.Drug_id = er.Drug_rlsid
                            limit 1
						) dn ON true
					where
						er.EvnRecept_id = :EvnRecept_id;
				";
				$params = array(
					'EvnRecept_id' => $data['EvnRecept_id']
				);
				$recept_data = $this->getFirstRowFromQuery($query, $params);
				if ($recept_data === false) {
					throw new Exception('Не удалось получить данные о рецепте', 500);
				}
				
				$params = array(
					'EvnRecept_Guid' => $recept_data['EvnRecept_Guid'],
					'Person_id' => $recept_data['Person_id'],
					'Person_Snils' => $recept_data['Person_Snils'],
					'PrivilegeType_id' => $recept_data['PrivilegeType_id'],
					'Lpu_id' => $recept_data['Lpu_id'],
					'Lpu_Ogrn' => $recept_data['Lpu_Ogrn'],
					'MedPersonalRec_id' => $recept_data['MedPersonal_id'],
					'Diag_id' => $recept_data['Diag_id'],
					'EvnRecept_Ser' => $recept_data['EvnRecept_Ser'],
					'EvnRecept_Num' => $recept_data['EvnRecept_Num'],
					'EvnRecept_setDT' => $recept_data['EvnRecept_setDT'],
					'ReceptFinance_id' => $recept_data['ReceptFinance_id'],
					'ReceptValid_id' => null,
					'OrgFarmacy_id' => $data['OrgFarmacy_id'],
					'Drug_cid' => $recept_data['Drug_id'],
					'Drug_Code' => $recept_data['Drug_Code'],
					'EvnRecept_Kolvo' => $recept_data['EvnRecept_Kolvo'],
					'EvnRecept_obrDate' => @getDT,
					'EvnRecept_otpDate' => @getDT,
					'EvnRecept_Price' => 0,
					'ReceptDelayType_id' => $recept_data['ReceptDelayType_id'],
					'ReceptOtdel_id' => null,
					'EvnRecept_id' => $recept_data['EvnRecept_id'],
					'EvnRecept_Is7Noz' => $recept_data['EvnRecept_Is7Noz'],
					'DrugFinance_id' => $recept_data['DrugFinance_id'],
					'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id'],
					'ReceptStatusType_id' => null,
					'ReceptOtov_isKEK' => null,
					'Polis_Ser' => null,
					'Polis_Num' => null,
					'pmUser_id' => $data['pmUser_id']
				);
				$proc_mode = 'ins';
				$var_mode = ':';
				
			} else {

				//получаем доп данные рецепта, которых нет в v_ReceptOtovUnSub
				$query = "
					select
						EvnRecept_Guid as \"EvnRecept_Guid\",
						Lpu_Ogrn as \"Lpu_Ogrn\",
						ReceptOtov_isKEK as \"ReceptOtov_isKEK\",
						Polis_Ser as \"Polis_Ser\",
						Polis_Num as \"Polis_Num\",
						Drug_Code as \"Drug_Code\",
						ReceptOtdel_id as \"ReceptOtdel_id\"
					from
						ReceptOtov 

					where
						ReceptOtov_id = :ReceptOtov_id	
				";
				$dop_params = array(
					'ReceptOtov_id' => $receptotov_id
				);
				$dop_recept_data = $this->getFirstRowFromQuery($query, $dop_params);
				if ($dop_recept_data === false) {
					throw new Exception('Не удалось получить данные о рецепте', 500);
				}

				$recept_data = $result;

				$params = array(
					'ReceptOtov_id' => $receptotov_id,
					'EvnRecept_Guid' => $dop_recept_data['EvnRecept_Guid'],
					'Person_id' => $recept_data['Person_id'],
					'Person_Snils' => $recept_data['Person_Snils'],
					'PrivilegeType_id' => $recept_data['PrivilegeType_id'],
					'Lpu_id' => $recept_data['Lpu_id'],
					'Lpu_Ogrn' => $dop_recept_data['Lpu_Ogrn'],
					'MedPersonalRec_id' => $recept_data['MedPersonalRec_id'],
					'Diag_id' => $recept_data['Diag_id'],
					'EvnRecept_Ser' => $recept_data['EvnRecept_Ser'],
					'EvnRecept_Num' => $recept_data['EvnRecept_Num'],
					'EvnRecept_setDT' => $recept_data['EvnRecept_setDT'],
					'ReceptFinance_id' => $recept_data['ReceptFinance_id'],
					'ReceptValid_id' => $recept_data['ReceptValid_id'],
					'OrgFarmacy_id' => $recept_data['OrgFarmacy_id'],
					'Drug_cid' => $recept_data['Drug_id'],
					'Drug_Code' => $dop_recept_data['Drug_Code'],
					'EvnRecept_Kolvo' => $recept_data['EvnRecept_Kolvo'],
					'EvnRecept_obrDate' => $recept_data['EvnRecept_obrDT'],
					'EvnRecept_otpDate' => $recept_data['EvnRecept_otpDT'],
					'EvnRecept_Price' => $recept_data['EvnRecept_Price'],
					'ReceptDelayType_id' => 5, // Статус рецепта по обслуживанию – «Снят с обслуживания»
					'ReceptOtdel_id' => $dop_recept_data['ReceptOtdel_id'],
					'EvnRecept_id' => $recept_data['EvnRecept_id'],
					'EvnRecept_Is7Noz' => $recept_data['EvnRecept_Is7Noz'],
					'DrugFinance_id' => $recept_data['DrugFinance_id'],
					'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id'],
					'ReceptStatusType_id' => $recept_data['ReceptStatusType_id'],
					'ReceptOtov_isKEK' => $dop_recept_data['ReceptOtov_isKEK'],
					'Polis_Ser' => $dop_recept_data['Polis_Ser'],
					'Polis_Num' => $dop_recept_data['Polis_Num'],
					'pmUser_id' => $data['pmUser_id']
				);
				$proc_mode = 'upd';
				$var_mode = ':';

			}

			//создаем/обновляем запись в ReceptOtov
			$query = "
				select ReceptOtov_id as \"ReceptOtov_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_ReceptOtov_{$proc_mode}(
					ReceptOtov_id := {$var_mode}ReceptOtov_id,
					EvnRecept_Guid := :EvnRecept_Guid,
					Person_id := :Person_id,
					Person_Snils := :Person_Snils,
					PrivilegeType_id := :PrivilegeType_id,
					Lpu_id := :Lpu_id,
					Lpu_Ogrn := :Lpu_Ogrn,
					MedPersonalRec_id := :MedPersonalRec_id,
					Diag_id := :Diag_id,
					EvnRecept_Ser := :EvnRecept_Ser,
					EvnRecept_Num := :EvnRecept_Num,
					EvnRecept_setDT := :EvnRecept_setDT,
					ReceptFinance_id := :ReceptFinance_id,
					ReceptValid_id := :ReceptValid_id,
					OrgFarmacy_id := :OrgFarmacy_id,
					Drug_cid := :Drug_cid,
					Drug_Code := :Drug_Code,
					EvnRecept_Kolvo := :EvnRecept_Kolvo,
					EvnRecept_obrDate := :EvnRecept_obrDate,
					EvnRecept_otpDate := :EvnRecept_otpDate,
					EvnRecept_Price := :EvnRecept_Price,
					ReceptDelayType_id := :ReceptDelayType_id,
					ReceptOtdel_id := :ReceptOtdel_id,
					EvnRecept_id := :EvnRecept_id,
					EvnRecept_Is7Noz := :EvnRecept_Is7Noz,
					DrugFinance_id := :DrugFinance_id,
					WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
					ReceptStatusType_id := :ReceptStatusType_id,
					ReceptOtov_isKEK := :ReceptOtov_isKEK,
					Polis_Ser := :Polis_Ser,
					Polis_Num := :Polis_Num,
					pmUser_id := :pmUser_id);


			";
			$result = $this->getFirstRowFromQuery($query, $params);
			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					throw new Exception($result['Error_Msg'], 500);
				} else if ($result['ReceptOtov_id'] > 0) {
					$receptotov_id = $result['ReceptOtov_id'];
				}
			}
			if ($receptotov_id == 0) {
				throw new Exception('Сохранение данных в списке отоваренных рецептов не удалось.', 500);
			}

			$query = "
				select
                    EvnClass_id as \"EvnClass_id\",
                    EvnRecept_UdostNum as \"EvnRecept_UdostNum\",
                    LpuSection_id as \"LpuSection_id\",
                    Drug_id as \"Drug_id\",
                    ReceptType_id as \"ReceptType_id\",
                    EvnClass_Name as \"EvnClass_Name\",
                    EvnRecept_IsMnn as \"EvnRecept_IsMnn\",
                    EvnRecept_IsInReg as \"EvnRecept_IsInReg\",
                    OrgFarmacy_id as \"OrgFarmacy_id\",
                    EvnRecept_Signa as \"EvnRecept_Signa\",
                    EvnRecept_id as \"EvnRecept_id\",
                    EvnRecept_IsNotOstat as \"EvnRecept_IsNotOstat\",
                    EvnRecept_obrDT as \"EvnRecept_obrDT\",
                    EvnRecept_setDate as \"EvnRecept_setDate\",
                    EvnRecept_otpDT as \"EvnRecept_otpDT\",
                    ReceptDelayType_id as \"ReceptDelayType_id\",
                    OrgFarmacy_oid as \"OrgFarmacy_oid\",
                    Drug_oid as \"Drug_oid\",
                    EvnRecept_oKolvo as \"EvnRecept_oKolvo\",
                    EvnRecept_oPrice as \"EvnRecept_oPrice\",
                    DrugRequestRow_id as \"DrugRequestRow_id\",
                    EvnRecept_ExtempContents as \"EvnRecept_ExtempContents\",
                    EvnRecept_IsExtemp as \"EvnRecept_IsExtemp\",
                    Person_Age as \"Person_Age\",
                    EvnRecept_setTime as \"EvnRecept_setTime\",
                    EvnRecept_Is7Noz as \"EvnRecept_Is7Noz\",
                    ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\",
                    DrugComplexMnn_id as \"DrugComplexMnn_id\",
                    PrepSeries_id as \"PrepSeries_id\",
                    EvnRecept_didDate as \"EvnRecept_didDate\",
                    Drug_rlsid as \"Drug_rlsid\",
                    DrugFinance_id as \"DrugFinance_id\",
                    WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                    EvnRecept_IsPaid as \"EvnRecept_IsPaid\",
                    EvnRecept_didTime as \"EvnRecept_didTime\",
                    WhsDocumentUc_id as \"WhsDocumentUc_id\",
                    EvnRecept_IsOtherDiag as \"EvnRecept_IsOtherDiag\",
                    EvnRecept_disDate as \"EvnRecept_disDate\",
                    ReceptForm_id as \"ReceptForm_id\",
                    EvnRecept_IsOtvSigned as \"EvnRecept_IsOtvSigned\",
                    EvnRecept_disTime as \"EvnRecept_disTime\",
                    pmUser_signotvID as \"pmUser_signotvID\",
                    EvnRecept_signotvDT as \"EvnRecept_signotvDT\",
                    EvnRecept_pid as \"EvnRecept_pid\",
                    EvnRecept_IsPrinted as \"EvnRecept_IsPrinted\",
                    EvnRecept_rid as \"EvnRecept_rid\",
                    Lpu_id as \"Lpu_id\",
                    EvnRecept_Price as \"EvnRecept_Price\",
                    PersonPrivilege_id as \"PersonPrivilege_id\",
                    Storage_id as \"Storage_id\",
                    Signatures_id as \"Signatures_id\",
                    Server_id as \"Server_id\",
                    PersonEvn_id as \"PersonEvn_id\",
                    EvnRecept_setDT as \"EvnRecept_setDT\",
                    EvnRecept_disDT as \"EvnRecept_disDT\",
                    EvnRecept_didDT as \"EvnRecept_didDT\",
                    EvnRecept_insDT as \"EvnRecept_insDT\",
                    EvnRecept_updDT as \"EvnRecept_updDT\",
                    EvnRecept_Index as \"EvnRecept_Index\",
                    EvnRecept_Count as \"EvnRecept_Count\",
                    pmUser_insID as \"pmUser_insID\",
                    pmUser_updID as \"pmUser_updID\",
                    Person_id as \"Person_id\",
                    Morbus_id as \"Morbus_id\",
                    EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
                    pmUser_signID as \"pmUser_signID\",
                    EvnRecept_signDT as \"EvnRecept_signDT\",
                    EvnRecept_IsArchive as \"EvnRecept_IsArchive\",
                    EvnRecept_Guid as \"EvnRecept_Guid\",
                    EvnRecept_IndexMinusOne as \"EvnRecept_IndexMinusOne\",
                    EvnStatus_id as \"EvnStatus_id\",
                    EvnRecept_statusDate as \"EvnRecept_statusDate\",
                    EvnRecept_IsTransit as \"EvnRecept_IsTransit\",
                    EvnRecept_Num as \"EvnRecept_Num\",
                    EvnRecept_Ser as \"EvnRecept_Ser\",
                    Diag_id as \"Diag_id\",
                    ReceptDiscount_id as \"ReceptDiscount_id\",
                    ReceptFinance_id as \"ReceptFinance_id\",
                    ReceptValid_id as \"ReceptValid_id\",
                    PrivilegeType_id as \"PrivilegeType_id\",
                    EvnRecept_IsKEK as \"EvnRecept_IsKEK\",
                    EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
                    MedPersonal_id as \"MedPersonal_id\",
                    EvnRecept_UdostSer as \"EvnRecept_UdostSer\"
				from
					v_EvnRecept er 

				where
					er.EvnRecept_id = :EvnRecept_id;
			";
			$params = array(
				'EvnRecept_id' => $data['EvnRecept_id']
			);
			$recept_data = $this->getFirstRowFromQuery($query, $params);
			if ($recept_data === false) {
				throw new Exception('Не удалось получить данные о рецепте', 500);
			}
			if(empty($recept_data['ReceptDelayType_id']) || $recept_data['ReceptDelayType_id'] != 5){
				$query = "
					select 
                        substring(field.name, 1, length(field.name)) as \"name\",
                        t.typname AS \"type\",
                        CASE WHEN field.mode_id = 'b' THEN 1 ELSE 0 END AS \"is_output\"
                    from (
                        select unnest(p.proargnames) as name,
                               unnest(p.proargtypes) as type_id,
                               unnest(p.proargmodes) as mode_id
                        from pg_catalog.pg_proc p
                            INNER JOIN pg_namespace n ON p.pronamespace = n.oid	
                        where
                            p.proname = 'p_evnrecept_upd' 
                            AND n.nspname = 'dbo'
                    ) field
            	    LEFT JOIN pg_type t ON t.oid = field.type_id
				";

				$reslt = $this->db->query($query, $data);
				if ( is_object($reslt) ) {
					$reslt = $reslt->result('array');
				} else {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				}

				$proc_params = $reslt;
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_EvnRecept_upd(\n";
				
				foreach($proc_params as $k=>$param) {
					if($param['name'] == 'EvnRecept_updDT'){
						$query .= "\t\t\t\tdbo.tzGetDate()";
					} else {
						$query .= "\t\t\t\t" . $param['name'] . " := :" . ( (in_array($param['name'],array('Error_Code','Error_Message'))) ? "" : ":".$param['name'] );
					}
					$query .= ( count($proc_params) == ++$k ? ");" : "," ) . "\n";
				}

				$recept_data['ReceptDelayType_id'] = 5;
				$recept_data['pmUser_id'] = $data['pmUser_id'];
				
				//echo getDebugSQL($query,$recept_data);die;
				$result = $this->db->query($query, $recept_data);

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				}
				$result = $result->result('array');

				if ( !is_array($result) || count($result) == 0 ) {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				} else if ( !empty($result[0]['Error_Message']) ) {
					throw new Exception('Ошибка при обновлении данных о рецепте - '.$result[0]['Error_Message'], 500);
				}
			}

		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
			return false;
		}
		$this->commitTransaction();
		return array(array('Error_Msg'=>null, 'Error_Code'=>null));
	}

	/**
	 * Получение даты и причины снятия с обслуживания
	 */
	function getReceptOutDateAndCause($data) {
		$query = "
			select
				wduarl.WhsDocumentUcActReceptList_outCause as \"WhsDocumentUcActReceptList_outCause\",
				to_char(wduaro.WhsDocumentUcActReceptOut_setDT, 'DD.MM.YYYY') as \"WhsDocumentUcActReceptOut_setDT\"

			from
				v_WhsDocumentUcActReceptList wduarl 

				left join v_WhsDocumentUcActReceptOut wduaro  on wduaro.WhsDocumentUcActReceptOut_id = wduarl.WhsDocumentUcActReceptOut_id

			where
				wduarl.EvnRecept_id = :EvnRecept_id
		";
		$result = $this->db->query($query, array('EvnRecept_id'=>$data['EvnRecept_id']));

		if ( !is_object($result) ) {
			return array(array('Error_Msg'=>'Ошибка при получении данных по рецепту'));
		}
		$result = $result->result('array');
		return $result;
	}

	/**
	 * Проверка статуса документа снятия с обслуживания
	 */
	function checkOutDocumentStatus($data) {
		$query = "
			select 
				wdu.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\"
			from
				v_WhsDocumentUcActReceptList wduarl 

				inner join v_WhsDocumentUcActReceptOut wduaro  on wduaro.WhsDocumentUcActReceptOut_id = wduarl.WhsDocumentUcActReceptOut_id

				inner join v_WhsDocumentUc wdu  on wdu.WhsDocumentUc_id = wdu.WhsDocumentUc_id

			where
				wdu.WhsDocumentType_id = 24
				and wduarl.EvnRecept_id = :EvnRecept_id
            limit 1
		";
		//echo getDebugSql($query, array('EvnRecept_id'=>$data['EvnRecept_id'])); die();
		$result = $this->db->query($query, array('EvnRecept_id'=>$data['EvnRecept_id']));

		if ( !is_object($result) ) {
			return array(array('Error_Msg'=>'Ошибка при получении данных по рецепту'));
		}
		$result = $result->result('array');
		return $result;
	}

	/**
	 * Удаление данных о снятии рецепта с обслуживания
	 */
	function deletePullOfServiceRecord($data) {
           
        $this->beginTransaction();
		try {
			$query = "
				select
					wduaro.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					wduarl.WhsDocumentUcActReceptOut_id as \"WhsDocumentUcActReceptOut_id\",
					wduarl.WhsDocumentUcActReceptList_id as \"WhsDocumentUcActReceptList_id\"
				from
					v_WhsDocumentUcActReceptList wduarl 

					left join v_WhsDocumentUcActReceptOut wduaro  on wduaro.WhsDocumentUcActReceptOut_id = wduarl.WhsDocumentUcActReceptOut_id

				where
					wduarl.EvnRecept_id = :EvnRecept_id
			";
			$result = $this->db->query($query, array('EvnRecept_id'=>$data['EvnRecept_id']));

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при получении данных о рецепте', 500);
			}
			$result = $result->result('array');
			if ( count($result) == 0 ) {
				throw new Exception('Ошибка при получении данных о рецепте', 500);
			}
			
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_WhsDocumentUcActReceptList_del(
					WhsDocumentUcActReceptList_id := :WhsDocumentUcActReceptList_id);
			";
			//echo getDebugSql($query, $data); die();
			$res = $this->db->query($query, array('WhsDocumentUcActReceptList_id'=>$result[0]['WhsDocumentUcActReceptList_id']));
			
			if ( !is_object($res) ) {
				throw new Exception('Ошибка при удалении данных о рецепте', 500);
			}
			$res = $res->result('array');
			if ( !empty($res[0]['Error_Msg']) ) {
				throw new Exception('Ошибка при удалении данных о рецепте '.$res[0]['Error_Msg'], 500);
			}

			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_WhsDocumentUcActReceptOut_del(
					WhsDocumentUcActReceptOut_id := :WhsDocumentUcActReceptOut_id);
			";
			//echo getDebugSql($query, $data); die();
			$res = $this->db->query($query, array('WhsDocumentUcActReceptOut_id'=>$result[0]['WhsDocumentUcActReceptOut_id']));
			
			if ( !is_object($res) ) {
				throw new Exception('Ошибка при удалении данных о рецепте', 500);
			}
			$res = $res->result('array');
			if ( !empty($res[0]['Error_Msg']) ) {
				throw new Exception('Ошибка при удалении данных о рецепте '.$res[0]['Error_Msg'], 500);
			}

			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_WhsDocumentUc_del(
					WhsDocumentUc_id := :WhsDocumentUc_id);
			";
			//echo getDebugSql($query, $data); die();
			$res = $this->db->query($query, array('WhsDocumentUc_id'=>$result[0]['WhsDocumentUc_id']));
			
			if ( !is_object($res) ) {
				throw new Exception('Ошибка при удалении данных о рецепте', 500);
			}
			$res = $res->result('array');
			if ( !empty($res[0]['Error_Msg']) ) {
				throw new Exception('Ошибка при удалении данных о рецепте '.$res[0]['Error_Msg'], 500);
			}

			$query = "
				select
					EvnClass_id as \"EvnClass_id\",
					EvnClass_Name as \"EvnClass_Name\",
					EvnRequest_id as \"EvnRequest_id\",
					EvnRequest_setDate as \"EvnRequest_setDate\",
					EvnRequest_setTime as \"EvnRequest_setTime\",
					EvnRequest_didDate as \"EvnRequest_didDate\",
					EvnRequest_didTime as \"EvnRequest_didTime\",
					EvnRequest_disDate as \"EvnRequest_disDate\",
					EvnRequest_disTime as \"EvnRequest_disTime\",
					EvnRequest_pid as \"EvnRequest_pid\",
					EvnRequest_rid as \"EvnRequest_rid\",
					Lpu_id as \"Lpu_id\",
					Server_id as \"Server_id\",
					PersonEvn_id as \"PersonEvn_id\",
					EvnRequest_setDT as \"EvnRequest_setDT\",
					EvnRequest_disDT as \"EvnRequest_disDT\",
					EvnRequest_didDT as \"EvnRequest_didDT\",
					EvnRequest_insDT as \"EvnRequest_insDT\",
					EvnRequest_updDT as \"EvnRequest_updDT\",
					EvnRequest_Index as \"EvnRequest_Index\",
					EvnRequest_Count as \"EvnRequest_Count\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					Person_id as \"Person_id\",
					Morbus_id as \"Morbus_id\",
					EvnRequest_IsSigned as \"EvnRequest_IsSigned\",
					pmUser_signID as \"pmUser_signID\",
					EvnRequest_signDT as \"EvnRequest_signDT\",
					EvnRequest_IsArchive as \"EvnRequest_IsArchive\",
					EvnRequest_Guid as \"EvnRequest_Guid\",
					EvnRequest_IndexMinusOne as \"EvnRequest_IndexMinusOne\",
					EvnStatus_id as \"EvnStatus_id\",
					EvnRequest_statusDate as \"EvnRequest_statusDate\",
					EvnRequest_IsTransit as \"EvnRequest_IsTransit\",
					EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnRecept er 

				where
					er.EvnRecept_id = :EvnRecept_id;
			";
			$params = array(
				'EvnRecept_id' => $data['EvnRecept_id']
			);
			$recept_data = $this->getFirstRowFromQuery($query, $params);
			if ($recept_data === false) {
				throw new Exception('Не удалось получить данные о рецепте', 500);
			}
			if(empty($recept_data['ReceptDelayType_id']) || $recept_data['ReceptDelayType_id'] == 5){
				$query = "
					select 
                        substring(field.name, 1, length(field.name)) as \"name\",
                        t.typname AS \"type\",
                        CASE WHEN field.mode_id = 'b' THEN 1 ELSE 0 END AS \"is_output\"
                    from (
                        select unnest(p.proargnames) as name,
                               unnest(p.proargtypes) as type_id,
                               unnest(p.proargmodes) as mode_id
                        from pg_catalog.pg_proc p
                            INNER JOIN pg_namespace n ON p.pronamespace = n.oid	
                        where
                            p.proname = 'p_evnrecept_upd' 
                            AND n.nspname = 'dbo'
                    ) field
            	    LEFT JOIN pg_type t ON t.oid = field.type_id
				";

				$reslt = $this->db->query($query, $data);
				if ( is_object($reslt) ) {
					$reslt = $reslt->result('array');
				} else {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				}

				$proc_params = $reslt;
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_EvnRecept_upd(\n";
				
				foreach($proc_params as $k=>$param) {
					if($param['name'] == 'EvnRecept_updDT'){
						$query .= "\t\t\t\tEvnRecept_updDT := dbo.tzGetDate()";
					} else {
						$query .= "\t\t\t\t" . $param['name'] . " := " . ( (in_array($param['name'],array('Error_Code','Error_Message'))) ? "" : ":".$param['name'] );
					}
					$query .= ( count($proc_params) == ++$k ? ");" : "," ) . "\n";
				}


				$recept_data['ReceptDelayType_id'] = 2;
				$recept_data['pmUser_id'] = $data['pmUser_id'];
				
				//echo getDebugSQL($query,$recept_data);die;
				$result = $this->db->query($query, $recept_data);

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				}
				$result = $result->result('array');

				if ( !is_array($result) || count($result) == 0 ) {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				} else if ( !empty($result[0]['Error_Message']) ) {
					throw new Exception('Ошибка при обновлении данных о рецепте - '.$result[0]['Error_Message'], 500);
				}
			}

        } catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		$this->commitTransaction();
		return array(array('Error_Msg'=>null, 'Error_Code'=>null));
	}

	/**
	 * Удаление данных об отказе по рецепту
	 */
	function deleteReceptWrongRecord($data) {
           
        $this->beginTransaction();
		try {
			$query = "
				select 
					Wr.ReceptWrong_id as \"ReceptWrong_id\",
					ro.ReceptOtov_id as \"ReceptOtov_id\"
				from 
					v_EvnRecept er 

					inner join ReceptWrong Wr  on Wr.EvnRecept_id = er.EvnRecept_id

					left join ReceptOtov ro  on ro.EvnRecept_id = er.EvnRecept_id

				where	
					er.EvnRecept_id = :EvnRecept_id
			";
			//echo getDebugSql($query, $data); die();
			$result = $this->db->query($query, array('EvnRecept_id'=>$data['EvnRecept_id']));

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при получении данных о рецепте', 500);
			}
			$result = $result->result('array');
			if ( count($result) == 0 ) {
				throw new Exception('Ошибка при получении данных о рецепте', 500);
			}
			
			if(!empty($result[0]['ReceptOtov_id'])){
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_ReceptOtov_del(
						ReceptOtov_id := :ReceptOtov_id);
				";
				//echo getDebugSql($query, $data); die();
				$res = $this->db->query($query, array('ReceptOtov_id'=>$result[0]['ReceptOtov_id']));
				
				if ( !is_object($res) ) {
					throw new Exception('Ошибка при удалении данных о рецепте', 500);
				}
				$res = $res->result('array');
				if ( !empty($res[0]['Error_Msg']) ) {
					throw new Exception('Ошибка при удалении данных о рецепте '.$res[0]['Error_Msg'], 500);
				}
			}
			
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_ReceptWrong_del(
					ReceptWrong_id := :ReceptWrong_id);
			";
			//echo getDebugSql($query, $data); die();
			$res = $this->db->query($query, array('ReceptWrong_id'=>$result[0]['ReceptWrong_id']));
			
			if ( !is_object($res) ) {
				throw new Exception('Ошибка при удалении данных о рецепте', 500);
			}
			$res = $res->result('array');
			if ( !empty($res[0]['Error_Msg']) ) {
				throw new Exception('Ошибка при удалении данных о рецепте '.$res[0]['Error_Msg'], 500);
			}

			$query = "
				select
					EvnClass_id as \"EvnClass_id\",
					EvnClass_Name as \"EvnClass_Name\",
					EvnRequest_id as \"EvnRequest_id\",
					EvnRequest_setDate as \"EvnRequest_setDate\",
					EvnRequest_setTime as \"EvnRequest_setTime\",
					EvnRequest_didDate as \"EvnRequest_didDate\",
					EvnRequest_didTime as \"EvnRequest_didTime\",
					EvnRequest_disDate as \"EvnRequest_disDate\",
					EvnRequest_disTime as \"EvnRequest_disTime\",
					EvnRequest_pid as \"EvnRequest_pid\",
					EvnRequest_rid as \"EvnRequest_rid\",
					Lpu_id as \"Lpu_id\",
					Server_id as \"Server_id\",
					PersonEvn_id as \"PersonEvn_id\",
					EvnRequest_setDT as \"EvnRequest_setDT\",
					EvnRequest_disDT as \"EvnRequest_disDT\",
					EvnRequest_didDT as \"EvnRequest_didDT\",
					EvnRequest_insDT as \"EvnRequest_insDT\",
					EvnRequest_updDT as \"EvnRequest_updDT\",
					EvnRequest_Index as \"EvnRequest_Index\",
					EvnRequest_Count as \"EvnRequest_Count\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					Person_id as \"Person_id\",
					Morbus_id as \"Morbus_id\",
					EvnRequest_IsSigned as \"EvnRequest_IsSigned\",
					pmUser_signID as \"pmUser_signID\",
					EvnRequest_signDT as \"EvnRequest_signDT\",
					EvnRequest_IsArchive as \"EvnRequest_IsArchive\",
					EvnRequest_Guid as \"EvnRequest_Guid\",
					EvnRequest_IndexMinusOne as \"EvnRequest_IndexMinusOne\",
					EvnStatus_id as \"EvnStatus_id\",
					EvnRequest_statusDate as \"EvnRequest_statusDate\",
					EvnRequest_IsTransit as \"EvnRequest_IsTransit\",
					EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnRecept er 

				where
					er.EvnRecept_id = :EvnRecept_id;
			";
			$params = array(
				'EvnRecept_id' => $data['EvnRecept_id']
			);
			$recept_data = $this->getFirstRowFromQuery($query, $params);
			if ($recept_data === false) {
				throw new Exception('Не удалось получить данные о рецепте', 500);
			}
			if($recept_data['ReceptDelayType_id'] == 3){
				$query = "
					select 
                        substring(field.name, 1, length(field.name)) as \"name\",
                        t.typname AS \"type\",
                        CASE WHEN field.mode_id = 'b' THEN 1 ELSE 0 END AS \"is_output\"
                    from (
                        select unnest(p.proargnames) as name,
                               unnest(p.proargtypes) as type_id,
                               unnest(p.proargmodes) as mode_id
                        from pg_catalog.pg_proc p
                            INNER JOIN pg_namespace n ON p.pronamespace = n.oid	
                        where
                            p.proname = 'p_evnrecept_upd' 
                            AND n.nspname = 'dbo'
                    ) field
            	    LEFT JOIN pg_type t ON t.oid = field.type_id
				";

				$reslt = $this->db->query($query, $data);
				if ( is_object($reslt) ) {
					$reslt = $reslt->result('array');
				} else {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				}

				$proc_params = $reslt;
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_EvnRecept_upd(\n";
				
				foreach($proc_params as $k=>$param) {
					if($param['name'] == 'EvnRecept_updDT'){
						$query .= "\t\t\t\tEvnRecept_updDT := dbo.tzGetDate()";
					} else {
						$query .= "\t\t\t\t" . $param['name'] . " := " . ( (in_array($param['name'],array('Error_Code','Error_Message'))) ? "" : ":".$param['name'] );
					}
					$query .= ( count($proc_params) == ++$k ? ");" : "," ) . "\n";
				}

				$recept_data['ReceptDelayType_id'] = null;
				$recept_data['EvnRecept_obrDT'] = null;
				$recept_data['EvnRecept_otpDT'] = null;
				$recept_data['pmUser_id'] = $data['pmUser_id'];
				
				//echo getDebugSQL($query,$recept_data);die;
				$result = $this->db->query($query, $recept_data);

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				}
				$result = $result->result('array');

				if ( !is_array($result) || count($result) == 0 ) {
					throw new Exception('Ошибка при обновлении данных о рецепте', 500);
				} else if ( !empty($result[0]['Error_Message']) ) {
					throw new Exception('Ошибка при обновлении данных о рецепте - '.$result[0]['Error_Message'], 500);
				}
			}

        } catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		$this->commitTransaction();
		return array(array('Error_Msg'=>null, 'Error_Code'=>null));
	}

	/**
	 * Поиск разнарядки для списания медикамента
	 */
	function getDrugOstatRegistry($data) {
		$query = "
			select 
				DOR.DrugOstatRegistry_id as \"DrugOstatRegistry_id\"
			from v_DrugOstatRegistry DOR 

			inner join rls.v_Drug D  on D.Drug_id = DOR.Drug_id

			left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id

			left join rls.v_DrugComplexMnnName DCMN  on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id

			inner join Lpu L  on L.Org_id = DOR.Org_id

			inner join v_SubAccountType SAT  on SAT.SubAccountType_id = DOR.SubAccountType_id

			left join rls.v_PrepSeries PS  on PS.PrepSeries_id = DOR.PrepSeries_id

			left join v_YesNo YN  on YN.YesNo_id = PS.PrepSeries_IsDefect

			where 
				DCM.DrugComplexMnn_id = :DrugComplexMnn_id
				and L.Lpu_id = :Lpu_id
				and DOR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
				and DOR.DrugOstatRegistry_Kolvo - :EvnRecept_Kolvo > 0
				and SAT.SubAccountType_Code = 1 --Доступно
				and COALESCE(YN.YesNo_Code,0) = 0 --Исключение забракованных серий
			limit 1 

		";
		return $this->dbmodel->getFirstResultFromQuery($query, $data, true);
	}

	/**
	 * Получение списка рецептов. Метод для API
	 */
	function getEvnReceptListForAPI($data) {
		$params = array('Evn_pid' => $data['Evn_pid']);
		$query = "
			select
				ER.EvnRecept_id as \"Evn_id\",
				to_char(ER.EvnRecept_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\",

				ER.EvnRecept_Num as \"EvnRecept_Num\",
				ER.EvnRecept_Ser as \"EvnRecept_Ser\",
				ER.PrivilegeType_id as \"PrivilegeType_id\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\"
			from 
				v_EvnRecept ER 

				left join rls.v_Drug D  on D.Drug_id = ER.Drug_id

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = COALESCE(D.DrugComplexMnn_id, ER.DrugComplexMnn_id)


			where
				ER.EvnRecept_pid = :Evn_pid
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных рецепта. Метод для API
	 */
	function getEvnReceptForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['Evn_id'])) {
			$filters[] = "ER.EvnRecept_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filters[] = "ER.EvnRecept_pid = :Evn_pid";
			$params['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['Evn_setDT'])) {
			$filters[] = "cast(ER.EvnRecept_setDT as date) = cast(:Evn_setDT as date)";
			$params['Evn_setDT'] = $data['Evn_setDT'];
		}
		if (!empty($data['EvnRecept_Num'])) {
			$filters[] = "ER.EvnRecept_Num = :EvnRecept_Num";
			$params['EvnRecept_Num'] = $data['EvnRecept_Num'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не было передано ни одного параметра');
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				ER.EvnRecept_id as \"Evn_id\",
				ER.EvnRecept_pid as \"Evn_pid\",
				ER.ReceptForm_id as \"ReceptForm_id\",
				ER.ReceptType_id as \"ReceptType_id\",
				to_char(ER.EvnRecept_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\",

				ER.EvnRecept_Num as \"EvnRecept_Num\",
				ER.EvnRecept_Ser as \"EvnRecept_Ser\",
				ER.ReceptValid_id as \"ReceptValid_id\",
				ER.LpuSection_id as \"LpuSection_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				ER.Diag_id as \"Diag_id\",
				ER.ReceptFinance_id as \"ReceptFinance_id\",
				ER.ReceptDiscount_id as \"ReceptDiscount_id\",
				ER.EvnRecept_is7Noz as \"EvnRecept_is7Noz\",
				ER.PrivilegeType_id as \"PrivilegeType_id\",
				--ER.Drug_id,
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DP.DrugState_Price as \"Drug_Price\",
				ER.EvnRecept_ExtempContents as \"EvnRecept_ExtempContents\",
				ER.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
				ER.EvnRecept_Signa as \"EvnRecept_Signa\",
				Is7Noz.YesNo_Code as \"EvnRecept_is7Noz\"
			from 
				v_EvnRecept ER 

				left join rls.v_Drug D  on D.Drug_id = ER.Drug_id

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = COALESCE(D.DrugComplexMnn_id, ER.DrugComplexMnn_id)


				LEFT JOIN LATERAL(

					select 
						MSF.MedStaffFact_id
					from v_MedStaffFact MSF 

					where MSF.MedPersonal_id = ER.MedPersonal_id
					and MSF.LpuSection_id = ER.LpuSection_id
					and MSF.WorkData_begDate <= ER.EvnRecept_setDate
					and COALESCE(MSF.WorkData_endDate, ER.EvnRecept_setDate) >= ER.EvnRecept_setDate
					limit 1
				) MSF ON true
				LEFT JOIN LATERAL (

					select  DP.DrugState_Price
					from v_DrugPrice DP 

					inner join ReceptFinance RF  on RF.ReceptFinance_id = DP.ReceptFinance_id

					where DP.Drug_id = ER.Drug_id and DP.DrugProto_begDate <= ER.EvnRecept_setDate
					order by DP.DrugProto_id desc
                    limit 1
				) DP ON true
				left join v_YesNo Is7Noz  on Is7Noz.YesNo_id = ER.EvnRecept_is7Noz

			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}
	
	/**
	*	Получение данных по общему рецепту
	*/
	function loadEvnReceptGeneralEditForm($data) {
		$params = array(
			'EvnReceptGeneral_id'	=> $data['EvnReceptGeneral_id']
		);
		$query = "
			select
				ERG.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
				ERG.EvnReceptGeneral_pid as \"EvnReceptGeneral_pid\",
				ERG.Person_id as \"Person_id\",
				ERG.Lpu_id as \"Lpu_id\",
				ERG.ReceptForm_id as \"ReceptForm_id\",
				ERG.ReceptType_id as \"ReceptType_id\",
				ERG.ReceptValid_id as \"ReceptValid_id\",
				/*to_char(ERG.EvnReceptGeneral_setDT, 'DD.MM.YYYY') as EvnReceptGeneral_setDate,*/
				to_char(ERG.EvnReceptGeneral_begDate, 'DD.MM.YYYY') as \"EvnReceptGeneral_setDate\",
				COALESCE(ERG.EvnReceptGeneral_Ser,'') as \"EvnReceptGeneral_Ser\",
				COALESCE(ERG.EvnReceptGeneral_Num,'') as \"EvnReceptGeneral_Num\",
				case when ERG.EvnReceptGeneral_IsChronicDisease = 2 then 1 else 0 end as \"EvnReceptGeneral_IsChronicDisease\",
				case when ERG.EvnReceptGeneral_IsSpecNaz = 2 then 1 else 0 end as \"EvnReceptGeneral_IsSpecNaz\",
				ERG.ReceptUrgency_id as \"ReceptUrgency_id\",
				COALESCE(ERG.EvnReceptGeneral_Validity,'') as \"EvnReceptGeneral_Validity\",
				to_char(ERG.EvnReceptGeneral_endDate, 'DD.MM.YYYY') as \"EvnReceptGeneral_endDate\",
				COALESCE(EvnReceptGeneral_Period,'') as \"EvnReceptGeneral_Period\",
				case when ERG.CauseVK_id is not null then 2 else 1 end as \"EvnReceptGeneral_hasVK\",
				ERG.EvnReceptGeneral_VKProtocolNum as \"EvnReceptGeneral_VKProtocolNum\",
				to_char(ERG.EvnReceptGeneral_VKProtocolDT, 'dd.mm.yyyy') as \"EvnReceptGeneral_VKProtocolDT\",
				ERG.CauseVK_id as \"CauseVK_id\",
				COALESCE(L.Lpu_Name, '') as \"Lpu_Name\",
				COALESCE(MP.MedPersonal_id,0) as \"MedPersonal_id\",
				COALESCE(LS.LpuSection_id,0) as \"LpuSection_id\",
				COALESCE(MP.Person_Fio,'') as \"MedPersonal_Name\",
				COALESCE(LS.LpuSection_FullName,'') as \"LpuSection_Name\",
				ERG.Diag_id as \"Diag_id\",
				ERG.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				ERG.Drug_rlsid as \"Drug_rlsid\",
				ERG.EvnReceptGeneral_Kolvo as \"EvnReceptGeneral_Kolvo\",
				ERG.EvnReceptGeneral_NumDose as \"EvnReceptGeneral_NumDose\",
				ERG.EvnReceptGeneral_Signa as \"EvnReceptGeneral_Signa\",
				case when 2 = coalesce(ERG.EvnReceptGeneral_IsDelivery, 1)
					then 'true'
					else 'false'
				end as \"EvnReceptGeneral_IsDelivery\",
				ES.MedPersonal_id as \"MedPersonal_id\",
				EPL.MedPersonal_id as \"MedPersonal_id\",
				rdt.ReceptDelayType_Code as \"ReceptDelayType_Code\" 
			from v_EvnReceptGeneral ERG 
			left join v_Lpu L  on L.Lpu_id = ERG.Lpu_id
			left join v_EvnSection ES  on ES.EvnSection_id = ERG.EvnReceptGeneral_pid
			left join v_EvnVizitPL EPL  on EPL.EvnVizitPL_id = ERG.EvnReceptGeneral_pid
			left join v_ReceptDelayType rdt  on rdt.ReceptDelayType_id = ERG.ReceptDelayType_id
			LEFT JOIN LATERAL(
				select 
					MedPers.MedPersonal_id,
					MedPers.Person_Fio
				from v_MedPersonal MedPers
				where MedPers.MedPersonal_id = COALESCE(ES.MedPersonal_id,EPL.MedPersonal_id)
				limit 1
			) as MP ON true
			LEFT JOIN LATERAL(
				select 
					LSect.LpuSection_id,
					LSect.LpuSection_FullName
				from v_LpuSection LSect
				where LSect.LpuSection_id = COALESCE(ES.LpuSection_id,EPL.LpuSection_id)
				limit 1
			) as LS ON true
			where ERG.EvnReceptGeneral_id = :EvnReceptGeneral_id
		";
		$result = $this->db->query($query, $params);
		if(is_object($result))
		{
			$result = $result->result('array');
			//Получим данные о медикаментах в рецепте
			$query_get_drugs = "
				select 
					ERGDL.EvnReceptGeneralDrugLink_id as \"EvnReceptGeneralDrugLink_id\",
					ERGDL.EvnReceptGeneralDrugLink_Kolvo as \"Drug_Kolvo_Pack\",
					ERGDL.EvnReceptGeneralDrugLink_NumDose as \"Drug_Fas\",
					(
						COALESCE(DCMF.DrugComplexMnnFas_Kol,1) * 

						COALESCE(DCMF.DrugComplexMnnFas_KolPrim,1) * 

						COALESCE(DCMF.DrugComplexMnnFas_KolSec,1) * 

						COALESCE(DCMF.DrugComplexMnnFas_Tert,1)

					) as \"Drug_Fas_\",
					ERGDL.EvnReceptGeneralDrugLink_Signa as \"Drug_Signa\",
					case when ECTD.Drug_id is null then DCM.DrugComplexMnn_RusName else D.Drug_ShortName end as \"Drug_Name\"
				from v_EvnReceptGeneralDrugLink ERGDL 

				inner join v_EvnCourseTreatDrug ECTD  on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id

				left join rls.v_DrugComplexMnn DCM2  on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid

				left join rls.v_DrugComplexMnnFas DCMF  on DCMF.DrugComplexMnnFas_id = COALESCE(DCM.DrugComplexMnnFas_id,DCM2.DrugComplexMnnFas_id)


				left join rls.v_Drug D  on D.Drug_id = ECTD.Drug_id

				where ERGDL.EvnReceptGeneral_id = :EvnReceptGeneral_id
			";
			$result_get_drugs = $this->db->query($query_get_drugs,$params);
			if(is_object($result_get_drugs))
			{
				$result_get_drugs = $result_get_drugs->result('array');
				for($i=0;$i<count($result_get_drugs);$i++)
				{
					if (isset($data['fromExt6']) && $data['fromExt6'] > 0
						&& (!empty($data['EvnReceptGeneralDrugLink_id']) && $data['EvnReceptGeneralDrugLink_id'] == $result_get_drugs[$i]['EvnReceptGeneralDrugLink_id']))
					{
						$result[0]['EvnReceptGeneralDrugLink_id'] = $result_get_drugs[$i]['EvnReceptGeneralDrugLink_id'];
						$result[0]['Drug_Kolvo_Pack'] = $result_get_drugs[$i]['Drug_Kolvo_Pack'];
						$result[0]['Drug_Fas'] = $result_get_drugs[$i]['Drug_Fas'];
						$result[0]['Drug_Fas_'] = $result_get_drugs[$i]['Drug_Fas_'];
						$result[0]['Drug_Signa'] = $result_get_drugs[$i]['Drug_Signa'];
						$result[0]['Drug_Name'] = $result_get_drugs[$i]['Drug_Name'];
					}
					$result[0]['EvnReceptGeneralDrugLink_id'.$i] = $result_get_drugs[$i]['EvnReceptGeneralDrugLink_id'];
					$result[0]['Drug_Kolvo_Pack'.$i] = $result_get_drugs[$i]['Drug_Kolvo_Pack'];
					$result[0]['Drug_Fas'.$i] = $result_get_drugs[$i]['Drug_Fas'];
					$result[0]['Drug_Fas_'.$i] = $result_get_drugs[$i]['Drug_Fas_'];
					$result[0]['Drug_Signa'.$i] = $result_get_drugs[$i]['Drug_Signa'];
					$result[0]['Drug_Name'.$i] = $result_get_drugs[$i]['Drug_Name'];
				}
			}
			//var_dump($result);die;
			return $result;
		}
		else
			return false;
	}
	
	/**
	*	Получение медикаментов из строки лекарственного лечения
	*/
	function getEvnCourseTreatDrugDetail($data)
	{
		$params = array(
			'EvnCourseTreatDrug_id'	=> $data['EvnCourseTreatDrug_id']
		);
		$query = "
						select
							ECTD.EvnCourseTreatDrug_id as \"EvnCourseTreatDrug_id\",
							case when ECTD.Drug_id is null then DCM.DrugComplexMnn_RusName else D.Drug_ShortName end as \"Drug_Name\",
							/*CONVERT(float,COALESCE(ECTD.EvnCourseTreatDrug_Kolvo,0)) as Drug_Kolvo_Pack,*/

							/*1 as Drug_Kolvo_Pack,*/
							CEIL(COALESCE(ectd.EvnCourseTreatDrug_Kolvo*EvnCourse_PrescrCount/gpc.GoodsPackCount_Count, 1)) as \"Drug_Kolvo_Pack\",

							(
								COALESCE(DCMF.DrugComplexMnnFas_Kol,1) * 

								COALESCE(DCMF.DrugComplexMnnFas_KolPrim,1) * 

								COALESCE(DCMF.DrugComplexMnnFas_KolSec,1) * 

								COALESCE(DCMF.DrugComplexMnnFas_Tert,1)

							) as \"Drug_Fas_\",						
							(
								RTRIM(CAST(ECTD.EvnCourseTreatDrug_KolvoEd as decimal)::text) || ' ' || GU.GoodsUnit_Nick || ' на прием, ' ||
								'Приемов в день: ' || ECT.EvnCourseTreat_MaxCountDay::text || 
								', Продолжительность: ' || ECT.EvnCourseTreat_Duration::text || ' ' || DT.DurationType_Nick ||
								(case when EvnCourseComm.EvnPrescrTreat_Descr is null then '' else (', Комментарий: ' || EvnCourseComm.EvnPrescrTreat_Descr) end)
								--(case when EvnCourseComm.EvnPrescrTreat_Descr IS null then '' else (' ,Комментарий: ' + EvnCourseComm.EvnPrescrTreat_Descr)) end
							) as \"Drug_Signa\"
						
					from v_EvnCourseTreatDrug ECTD 

					left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id

					left join rls.v_DrugComplexMnn DCM2  on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid

					left join rls.v_DrugComplexMnnFas DCMF  on DCMF.DrugComplexMnnFas_id = COALESCE(DCM.DrugComplexMnnFas_id,DCM2.DrugComplexMnnFas_id)


					left join rls.v_Drug D  on D.Drug_id = ECTD.Drug_id

					left join v_GoodsUnit GU  on GU.GoodsUnit_id = ECTD.GoodsUnit_sid

					left join v_EvnCourseTreat ECT  on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id

					left join EvnCourse ec on ec.Evn_id=ECT.EvnCourseTreat_id
					left join GoodsPackCount gpc on 1=2 and gpc.GoodsUnit_id=ectd.GoodsUnit_id and (D.DrugComplexMnn_id=gpc.DrugComplexMnn_id or ectd.DrugComplexMnn_id=gpc.DrugComplexMnn_id)
					left join v_DurationType DT  on DT.DurationType_id = ECT.DurationType_id

					LEFT JOIN LATERAL (

						select EPT.EvnPrescrTreat_Descr
						from v_EvnPrescrTreat EPT 

						where EPT.EvnCourse_id = ECT.EvnCourseTreat_id
						and EPT.EvnPrescrTreat_Descr is not null
                        limit 1
					) as EvnCourseComm ON true
					where ECTD.EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id
		";
		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			return $result;
		}
		return false;
	}

	/*
	 * Проверка лекарства на вхождение в группу сильнодействующих
	 */
	function checkDrugByLinkIsStrong($data) {
		// Проверка - входит ли медикамент в группу наркотических
		$query = "
				select
					am.NARCOGROUPID  as \"NARCOGROUPID\",
					am.STRONGGROUPID as \"STRONGGROUPID\"
				from
					v_EvnReceptGeneralDrugLink ergdl
					inner join v_EvnCourseTreatDrug ectd on ectd.EvnCourseTreatDrug_id = ergdl.EvnCourseTreatDrug_id
					left join rls.Drug RD on RD.Drug_id = ectd.Drug_id
					inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = COALESCE(RD.DrugComplexMnn_id,ectd.DrugComplexMnn_id)
					inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					inner join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.Actmatters_id
					left join rls.NARCOGROUPS ng on ng.NARCOGROUPS_ID = am.NARCOGROUPID
				where ergdl.EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id
					and (am.NARCOGROUPID in (2,3,4,5) or am.STRONGGROUPID=1)
				limit 1
			";

		$response = $this->queryResult($query, $data);
		$result_drug = array();
		if(is_array($response) && !empty($response[0]['NARCOGROUPID']) ){
			$result_drug[0]['narco']= $response[0]['NARCOGROUPID'];
		}
		if( is_array($response) && !empty($response[0]['STRONGGROUPID']) ) {
			$result_drug[0]['stronggroup'] = $response[0]['STRONGGROUPID'];
		}
		return $result_drug;
	}
	
	/**
	*	Получение дефолтных данных и данных по медикаменту для добавления общего рецепта
	*/	
	function getReceptGeneralAddDetails($data)
	{
		$params = array(
			'EvnCourseTreatDrug_id'	=> $data['EvnCourseTreatDrug_id']
		);
		$query = "
			select
				to_char (COALESCE(ES.EvnSection_setDate,EVPL.EvnVizitPL_setDate) , 'dd.mm.yyyy') as \"EvnReceptGeneral_setDate\",

				--COALESCE(ES.EvnSection_setDate,EVPL.EvnVizitPL_setDate) as EvnReceptGeneral_setDate,

				COALESCE(L.Lpu_id, 0) as \"Lpu_id\",

				COALESCE(MP.MedPersonal_id,0) as \"MedPersonal_id\",

				COALESCE(L.Lpu_Name, '') as \"Lpu_Name\",

				COALESCE(MP.Person_Fio,'') as \"MedPersonal_Name\",

				COALESCE(LS.LpuSection_id,0) as \"LpuSection_id\",

				COALESCE(LS.LpuSection_FullName,'') as \"LpuSection_Name\",

				COALESCE(ES.Diag_id,EVPL.Diag_id) as \"Diag_id\",

				COALESCE(L.Lpu_Ouz::text,'') as \"EvnReceptGeneral_Ser\",

				RecNum.EvnReceptGeneral_Num as \"EvnReceptGeneral_Num\"
			from v_EvnCourseTreat ECT 

			INNER JOIN LATERAL (

				select EvnCourseTreat_id
				from v_EvnCourseTreatDrug
				where EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id
                limit 1
			) as EvnCourseTreatDrug ON true
			left join v_EvnSection ES  on ES.EvnSection_id = ECT.EvnCourseTreat_pid

			left join v_EvnVizitPL EVPL  on EVPL.EvnVizitPL_id = ECT.EvnCourseTreat_pid

			left join v_Lpu L  on L.Lpu_id = COALESCE(ES.Lpu_id,EVPL.Lpu_id)


			LEFT JOIN LATERAL(

				select 
					MedPers.MedPersonal_id,
					MedPers.Person_Fio
				from v_MedPersonal MedPers
				where MedPers.MedPersonal_id = COALESCE(ES.MedPersonal_id,EVPL.MedPersonal_id)
				limit 1
			) as MP ON true
			LEFT JOIN LATERAL(

				select 
					LSect.LpuSection_id,
					LSect.LpuSection_FullName
				from v_LpuSection LSect
				where LSect.LpuSection_id = COALESCE(ES.LpuSection_id,EVPL.LpuSection_id)
				limit 1
			) as LS ON true
			LEFT JOIN LATERAL(

				select COALESCE(max(cast(ERG.EvnReceptGeneral_Num as bigint)),0) + 1 as EvnReceptGeneral_Num

				from v_EvnReceptGeneral ERG 

				where
					-- избежим недоразумения с текстом в этих полях
					ISNUMERIC(ERG.EvnReceptGeneral_Num) = 1 and
					ISNUMERIC(ERG.evnreceptgeneral_Ser || 'e0') = 1 and
					length(ERG.EvnReceptGeneral_Num) <= 18 and
					ERG.EvnReceptGeneral_Ser = cast(L.Lpu_Ouz as varchar)
			) as RecNum ON true
			where ECT.EvnCourseTreat_id = EvnCourseTreatDrug.EvnCourseTreat_id
		";
		$result = $this->db->query($query, $params);
		if(is_object($result))
		{
			$result = $result->result('array');
			$result_drug = $this->getEvnCourseTreatDrugDetail($data);
			
			// Проверка - входит ли медикамент в группу наркотических
			$query = "
				select 
					am.NARCOGROUPID as \"NARCOGROUPID\",
					am.STRONGGROUPID as \"STRONGGROUPID\"
				from 
					v_EvnCourseTreatDrug ectd 

					left join rls.Drug RD  on RD.Drug_id = ectd.Drug_id

					inner join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = COALESCE(RD.DrugComplexMnn_id,ectd.DrugComplexMnn_id)


					inner join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

					inner join rls.v_ACTMATTERS am  on am.ACTMATTERS_ID = dcmn.Actmatters_id

					left join rls.NARCOGROUPS ng  on ng.NARCOGROUPS_ID = am.NARCOGROUPID

				where ectd.EvnCourseTreatDrug_id = 
                :EvnCourseTreatDrug_id
				and (am.NARCOGROUPID in (2,3,4,5) or am.STRONGGROUPID=1)
				limit 1
			";
			
			$response = $this->queryResult($query, array('EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id']));
			if(is_array($response) && !empty($response[0]['NARCOGROUPID']) ){
				$result_drug[0]['narco']= $response[0]['NARCOGROUPID'];
			}
			if( is_array($response) && !empty($response[0]['STRONGGROUPID']) ) {
				$result_drug[0]['stronggroup'] = $response[0]['STRONGGROUPID'];
			}
			
			//var_dump($result_drug);die;
			//var_dump(array_merge($result,$result_drug));die;
			$result = array_merge($result,$result_drug);
			//var_dump($result);die;
			return $result;
		}
		else
			return false;
	}
	
	/**
	*	Сохранение общего рецепта
	*/
	function saveEvnReceptGeneral($data)
	{
		$procedure = 'p_EvnReceptGeneral_ins';
		$params = $data;
		if(isset($params['EvnReceptGeneral_id']))
		{
			$procedure = 'p_EvnReceptGeneral_upd';
		}
		if($params['EvnReceptGeneral_IsChronicDisease'] == 'on')
			$params['EvnReceptGeneral_IsChronicDisease'] = 2;
		else
			$params['EvnReceptGeneral_IsChronicDisease'] = null;
			
		if(!empty($params['EvnReceptGeneral_IsSpecNaz']) && $params['EvnReceptGeneral_IsSpecNaz'] == 'on')
			$params['EvnReceptGeneral_IsSpecNaz'] = 2;
		else
			$params['EvnReceptGeneral_IsSpecNaz'] = null;

		if(!empty($params['EvnReceptGeneral_IsDelivery']) && $params['EvnReceptGeneral_IsDelivery'] == 'on')
			$params['EvnReceptGeneral_IsDelivery'] = 2;
		else
			$params['EvnReceptGeneral_IsDelivery'] = null;
		//Дополняем номер рецепта нулями, если знаков менее 6

		if(isset($params['EvnReceptGeneral_Num']) && intval($params['EvnReceptGeneral_Num'])>0 && strlen($params['EvnReceptGeneral_Num'])<6){
			str_pad($params['EvnReceptGeneral_Num'], 6, "0", STR_PAD_LEFT);
		}
		$this->beginTransaction();
		$response = array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении рецепта'));
		try{
		
			//Получим Server_id и PersonEvn_id (почему-то иногда из ЭМК он передается кривой) из pid-а
			$query_get_Server_id = "
				select Server_id as \"Server_id\", PersonEvn_id as \"PersonEvn_id\"
				from v_Evn
				where Evn_id = :EvnReceptGeneral_pid
			";
			$result_get_Server_id = $this->db->query($query_get_Server_id,$params);
			if (is_object($result_get_Server_id))
			{
				$result_get_Server_id = $result_get_Server_id->result('array');
				if(is_array($result_get_Server_id) && count($result_get_Server_id) > 0)
				{
					$params['Server_id'] = $result_get_Server_id[0]['Server_id'];
					$params['PersonEvn_id'] = $result_get_Server_id[0]['PersonEvn_id'];
				}
			}
			//Добавляем/изменяем рецепт
			$query_add_recept = "
				select EvnReceptGeneral_id as \"EvnReceptGeneral_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from {$procedure}(
					EvnReceptGeneral_id 					:= :EvnReceptGeneral_id,
					EvnReceptGeneral_pid					:= :EvnReceptGeneral_pid,
					Lpu_id									:= :Lpu_id,
					MedPersonal_id							:= :MedPersonal_id,
					LpuSection_id							:= :LpuSection_id,
					Diag_id								:= :Diag_id,
					PersonEvn_id							:= :PersonEvn_id,
					ReceptForm_id							:= :ReceptForm_id,
					ReceptType_id							:= :ReceptType_id,
					EvnReceptGeneral_begDate				:= :EvnReceptGeneral_setDate,
					EvnReceptGeneral_Ser					:= :EvnReceptGeneral_Ser,
					EvnReceptGeneral_Num					:= :EvnReceptGeneral_Num,
					EvnReceptGeneral_IsChronicDisease		:= :EvnReceptGeneral_IsChronicDisease,
					EvnReceptGeneral_IsSpecNaz				:= :EvnReceptGeneral_IsSpecNaz,
					EvnReceptGeneral_IsDelivery				:= :EvnReceptGeneral_IsDelivery,
					ReceptUrgency_id						:= :ReceptUrgency_id,
					ReceptValid_id							:= :ReceptValid_id,
					EvnReceptGeneral_Validity				:= :EvnReceptGeneral_Validity,
					EvnReceptGeneral_endDate				:= :EvnReceptGeneral_endDate,
					EvnReceptGeneral_Period				:= :EvnReceptGeneral_Period,
					EvnReceptGeneral_VKProtocolNum			:= :EvnReceptGeneral_VKProtocolNum,
					EvnReceptGeneral_VKProtocolDT			:= :EvnReceptGeneral_VKProtocolDT,
					CauseVK_id								:= :CauseVK_id,
					DrugFinance_id							:= 26, --Собственные средства
					WhsDocumentCostItemType_id				:= 33, --Основная деятельность
					Server_id								:= :Server_id,
					pmUser_id 								:= :pmUser_id);


			";
			//echo getDebugSQL($query_add_recept,$params);die;
			$result_add_recept = $this->db->query($query_add_recept,$params);
			//var_dump($result_add_recept);die;
			if(is_object($result_add_recept))
			{
				$result_add_recept = $result_add_recept->result('array');
				$response = $result_add_recept;
				if(is_array($result_add_recept) && count($result_add_recept) > 0)
				{
					$EvnReceptGeneral_id = $result_add_recept[0]['EvnReceptGeneral_id'];
					//Проверяем, были ли уже записи в EvnReceptGeneralDrugLink. Если были, то обновляем их
					$index = 0;
					for($i=0; $i<3; $i++)
					{
						if(isset($params['EvnReceptGeneralDrugLink_id'.$i]) && $params['EvnReceptGeneralDrugLink_id'.$i] > 0)
						{
							$EvnCourseTreatDrug_id = null;
							$params_get_EvnCourseTreatDrug = array(
								'EvnReceptGeneralDrugLink_id'	=> $params['EvnReceptGeneralDrugLink_id'.$i]
							);
							$query_get_EvnCourseTreatDrug = "
								select EvnCourseTreatDrug_id as \"EvnCourseTreatDrug_id\"
								from v_EvnReceptGeneralDrugLink
								where EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id
							";
							$result_get_EvnCourseTreatDrug = $this->db->query($query_get_EvnCourseTreatDrug,$params_get_EvnCourseTreatDrug);
							if(is_object($result_get_EvnCourseTreatDrug))
							{
								$result_get_EvnCourseTreatDrug = $result_get_EvnCourseTreatDrug->result('array');
								if(count($result_get_EvnCourseTreatDrug) > 0)
									$EvnCourseTreatDrug_id = $result_get_EvnCourseTreatDrug[0]['EvnCourseTreatDrug_id'];
							}
							$params_upd_EvnReceptGeneralDrugLink = array(
								'EvnReceptGeneralDrugLink_id' 		=> $params['EvnReceptGeneralDrugLink_id'.$i],
								'EvnReceptGeneral_id'				=> $EvnReceptGeneral_id,
								'EvnCourseTreatDrug_id'				=> $EvnCourseTreatDrug_id,
								'EvnReceptGeneralDrugLink_Kolvo'	=> $params['Drug_Kolvo_Pack'.$i],
								'EvnReceptGeneralDrugLink_NumDose'	=> $params['Drug_Fas'.$i],
								'EvnReceptGeneralDrugLink_Signa'	=> $params['Drug_Signa'.$i],
								'pmUser_id'							=> $params['pmUser_id']
							);
							$query_upd_EvnReceptGeneralDrugLink = "
								select EvnReceptGeneralDrugLink_id as \"EvnReceptGeneralDrugLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
								from p_EvnReceptGeneralDrugLink_upd(
									EvnReceptGeneralDrugLink_id 		:= :EvnReceptGeneralDrugLink_id,
									EvnReceptGeneral_id				:= :EvnReceptGeneral_id,
									EvnCourseTreatDrug_id				:= :EvnCourseTreatDrug_id,
									EvnReceptGeneralDrugLink_Kolvo		:= CAST(:EvnReceptGeneralDrugLink_Kolvo as numeric),
									EvnReceptGeneralDrugLink_NumDose	:= CAST(:EvnReceptGeneralDrugLink_NumDose as integer),
									EvnReceptGeneralDrugLink_Signa		:= :EvnReceptGeneralDrugLink_Signa,
									pmUser_id							:= :pmUser_id);


							";
							//echo getDebugSQL($query_upd_EvnReceptGeneralDrugLink,$params_upd_EvnReceptGeneralDrugLink);die;
							$result_upd_EvnReceptGeneralDrugLink = $this->db->query($query_upd_EvnReceptGeneralDrugLink,$params_upd_EvnReceptGeneralDrugLink);
							if(is_object($result_upd_EvnReceptGeneralDrugLink))
							{
								$result_upd_EvnReceptGeneralDrugLink = $result_upd_EvnReceptGeneralDrugLink->result('array');
								if(is_array($result_upd_EvnReceptGeneralDrugLink) && count($result_upd_EvnReceptGeneralDrugLink) > 0 && $result_upd_EvnReceptGeneralDrugLink[0]['EvnReceptGeneralDrugLink_id'] > 0)
									$index = $i+1;
								else
								{
									throw new Exception('При сохранении добавлении медикамента в рецепт произошла ошибка[2]', 500);
								}
							}
							else
							{
								throw new Exception('При сохранении добавлении медикамента в рецепт произошла ошибка[1]', 500);
							}
						}
					}
					//Если $index остался равен нулю, значит в рецепте еще нет ни одного медикамента, 
					//значит берем EvnCourseTreatDrug_id (если он есть) и связываем с Drug_Kolvo_Pack0, Drug_Fas0 и Drug_Signa0
					//Если $index больше нуля, значит, он показывает количество медикаментов с рецептом, 
					//поэтому EvnCourseTreatDrug_id (если он есть) связываем с Drug_Kolvo_Pack.$index, Drug_Fas.$index и Drug_Signa.$index
					if(isset($params['EvnCourseTreatDrug_id']) && $params['EvnCourseTreatDrug_id'] > 0)
					{
						//Добавляем новый медикамент (из назначения) в рецепт
						//var_dump($params);die;
						$params_add_EvnReceptGeneralDrugLink = array(
							'EvnReceptGeneral_id'	=> $EvnReceptGeneral_id,
							'EvnCourseTreatDrug_id'	=> $params['EvnCourseTreatDrug_id'],
							'EvnReceptGeneralDrugLink_Kolvo'	=> $params['Drug_Kolvo_Pack'.$index],
							'EvnReceptGeneralDrugLink_NumDose'	=> $params['Drug_Fas'.$index],
							'EvnReceptGeneralDrugLink_Signa'	=> $params['Drug_Signa'.$index],
							'pmUser_id'							=> $params['pmUser_id']
						);
						$query_add_EvnReceptGeneralDrugLink = "
							select 
							    EvnReceptGeneralDrugLink_id as \"EvnReceptGeneralDrugLink_id\",
							    Error_Code as \"Error_Code\",
							    Error_Message as \"Error_Msg\"
							from p_EvnReceptGeneralDrugLink_ins
							(
								EvnReceptGeneralDrugLink_id 		:=null,
								EvnReceptGeneral_id				    := :EvnReceptGeneral_id,
								EvnCourseTreatDrug_id				:= :EvnCourseTreatDrug_id,
								EvnReceptGeneralDrugLink_Kolvo		:= CAST(:EvnReceptGeneralDrugLink_Kolvo as numeric),
								EvnReceptGeneralDrugLink_NumDose	:= CAST(:EvnReceptGeneralDrugLink_NumDose as integer),
								EvnReceptGeneralDrugLink_Signa		:= :EvnReceptGeneralDrugLink_Signa,
								pmUser_id							:= :pmUser_id);


						";
						//echo getDebugSQL($query_add_EvnReceptGeneralDrugLink,$params_add_EvnReceptGeneralDrugLink);die;
						$result_add_EvnReceptGeneralDrugLink = $this->db->query($query_add_EvnReceptGeneralDrugLink,$params_add_EvnReceptGeneralDrugLink);
						if(is_object($result_add_EvnReceptGeneralDrugLink))
						{
							$result_add_EvnReceptGeneralDrugLink = $result_add_EvnReceptGeneralDrugLink->result('array');
							if(count($result_add_EvnReceptGeneralDrugLink) > 0 && isset($result_add_EvnReceptGeneralDrugLink[0]['EvnReceptGeneralDrugLink_id']) && $result_add_EvnReceptGeneralDrugLink[0]['EvnReceptGeneralDrugLink_id'] > 0)
							{
							}
							else
							{
								throw new Exception('При добавлении медикамента в рецепт произошла ошибка[2]', 500);
							}
						}
						else
						{
							throw new Exception('При добавлении медикамента в рецепт произошла ошибка[1]', 500);
						}
					}
				}
				else
				{
					throw new Exception('При сохранении рецепта произошла ошибка[2]', 500);
				}
			}
			else
			{
				throw new Exception('При сохранении рецепта произошла ошибка[1]', 500);
			}
		}
		catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		//$this->rollbackTransaction();
		//	return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		
		$this->commitTransaction();
		
		return $response;
	}
	
	/**
	*	Удаление медикамента из рецепта
	*/
	function deleteEvnReceptGeneralDrugLink($data)
	{
		$params = array(
			'EvnReceptGeneralDrugLink_id'	=> $data['EvnReceptGeneralDrugLink_id'],
		);
		//Проверим, не последний ли это медикамент в рецепте?
		$EvnReceptGeneral_id = 0;
		$query_check_recept_drugs = "
			select 
				ERGDL.EvnReceptGeneral_id as \"EvnReceptGeneral_id\", 
				ReceptDrugs.drugs_count as \"drugs_count\"
			from v_EvnReceptGeneralDrugLink ERGDL 

			--inner join v_EvnReceptGeneral ERG  on ERG.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id

			LEFT JOIN LATERAL

			(
				select count(ERGDL_t.EvnReceptGeneralDrugLink_id) as drugs_count
				from v_EvnReceptGeneralDrugLink ERGDL_t 

				where ERGDL_t.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id
			) as ReceptDrugs ON true
			where ERGDL.EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id
		";
		$result_check_recept_drugs = $this->db->query($query_check_recept_drugs,$params);
		if(is_object($result_check_recept_drugs))
		{
			$result_check_recept_drugs = $result_check_recept_drugs->result('array');
			if(count($result_check_recept_drugs) > 0)
			{
				if($result_check_recept_drugs[0]['drugs_count'] == 1)
					$EvnReceptGeneral_id = $result_check_recept_drugs[0]['EvnReceptGeneral_id'];
			}
		}
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_EvnReceptGeneralDrugLink_del(
				EvnReceptGeneralDrugLink_id := :EvnReceptGeneralDrugLink_id);


		";		
		$response = $this->queryResult($query,$params);
		
		//Если это был последний медикамент, то удаляем рецепт
		if($EvnReceptGeneral_id > 0)
		{
			$params['EvnReceptGeneral_id'] = $EvnReceptGeneral_id;
			$params['pmUser_id'] = $data['pmUser_id'];
			$query_delete_recept = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_EvnReceptGeneral_del(
					EvnReceptGeneral_id := :EvnReceptGeneral_id,
					pmUser_id := :pmUser_id);


			";
			$result_delete_recept = $this->db->query($query_delete_recept,$params);
		}
		return $response;
	}

	/**
	*	Поиск рецептов для АРМа провизора общего отдела
	*/
	function searchEvnReceptGeneralList($data)
	{
		$params = $data;
		//var_dump($params['start']);die;
		$select = '';
		$from = '';
		$join = '';
		$where = '';
		//$join .= ' inner join v_EvnReceptGeneral ERG  on ERG.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id';
		if($params['EvnReceptSearchDateType'] == 'vypis') //Поиск по дате выписки рецепта. Отталкиваемся от EvnReceptGeneral
		{
			//EvnReceptGeneral
			$from = ' v_EvnReceptGeneral ERG ';
			$join .= ' left join v_ReceptType RTP on RTP.ReceptType_id = ERG.ReceptType_id';
			$join .= ' inner join v_EvnReceptGeneralDrugLink ERGDL  on ERGDL.EvnReceptGeneral_id = ERG.EvnReceptGeneral_id';
			$join .= ' left join v_GeneralReceptSupply GRS  on GRS.EvnReceptGeneralDrugLink_id = ERGDL.EvnReceptGeneralDrugLink_id';

			//$join .= ' left join v_OrgFarmacy OrgF  on OrgF.OrgFarmacy_id = GRS.OrgFarmacy_id';
			//$join .= ' left join OrgFarmacy OrgF_  on OrgF_.OrgFarmacy_id = GRS.OrgFarmacy_id';
			//$join .= ' left join Org OrgF  on OrgF.Org_id = OrgF_.Org_id';


			if(!empty($params['EvnRecept_setDate_Range'][0]))
			{
				$where .= ' and ERG.EvnReceptGeneral_begDate >= cast(:EvnRecept_setDate_Range_0 as timestamp)';
				$params['EvnRecept_setDate_Range_0'] = $params['EvnRecept_setDate_Range'][0];
			}
			if(!empty($params['EvnRecept_setDate_Range'][1]))
			{
				$where .= ' and ERG.EvnReceptGeneral_begDate <= cast(:EvnRecept_setDate_Range_1 as timestamp)';
				$params['EvnRecept_setDate_Range_1'] = $params['EvnRecept_setDate_Range'][1];
			}
			/*$select .= ' RDT.ReceptDelayType_Name as Stat_and_Farm,';
			$select .= " '' as Supp_Date";
			*/
			//$select .= " RDT.ReceptDelayType_Name + ', ' + COALESCE(OrgF.OrgFarmacy_Nick,'') as Stat_and_Farm,";

			//$select .= ' to_char(GRS.GeneralReceptSupply_SupplyDate, 'DD.MM.YYYY') as Supp_Date';

		}
		else //Поиск по дате обращения в аптеку или по дате выписки рецепта. Отталкиваемся от GeneralReceptSupply
		{
			$from = ' v_GeneralReceptSupply GRS ';
			$join .= ' inner join v_EvnReceptGeneralDrugLink ERGDL  on ERGDL.EvnReceptGeneral_id = GRS.EvnReceptGeneralDrugLink_id';
			$join .= ' inner join v_EvnReceptGeneral ERG  on ERG.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id';
			$join .= ' left join v_ReceptType RTP on RTP.ReceptType_id = ERG.ReceptType_id';
			//$join .= ' left join v_OrgFarmacy OrgF  on OrgF.OrgFarmacy_id = GRS.OrgFarmacy_id';
			//$join .= ' left join OrgFarmacy OrgF_  on OrgF_.OrgFarmacy_id = GRS.OrgFarmacy_id';
			//$join .= ' left join Org OrgF  on OrgF.Org_id = OrgF_.Org_id';
			//$select .= " RDT.ReceptDelayType_Name + ', ' + COALESCE(OrgF.OrgFarmacy_Nick,'') as Stat_and_Farm,";
			//$select .= ' to_char(GRS.GeneralReceptSupply_SupplyDate, 'DD.MM.YYYY') as Supp_Date';
			if(!empty($params['EvnRecept_setDate_Range'][0]))
			{
				if($params['EvnReceptSearchDateType'] == 'obr')
				{
					$where .= ' and GRS.GeneralReceptSupply_MessageDate >= cast(:EvnRecept_setDate_Range_0 as timestamp)';
					$params['EvnRecept_setDate_Range_0'] = $params['EvnRecept_setDate_Range'][0];
				}
				if($params['EvnReceptSearchDateType'] == 'obesp')
				{
					$where .= ' and GRS.GeneralReceptSupply_SupplyDate >= cast(:EvnRecept_setDate_Range_0 as timestamp)';
					$params['EvnRecept_setDate_Range_0'] = $params['EvnRecept_setDate_Range'][0];
				}
			}
			if(!empty($params['EvnRecept_setDate_Range'][1]))
			{
				if($params['EvnReceptSearchDateType'] == 'obr')
				{
					$where .= ' and GRS.GeneralReceptSupply_MessageDate <= cast(:EvnRecept_setDate_Range_1 as timestamp)';
					$params['EvnRecept_setDate_Range_1'] = $params['EvnRecept_setDate_Range'][1];
				}
				if($params['EvnReceptSearchDateType'] == 'obesp')
				{
					$where .= ' and GRS.GeneralReceptSupply_SupplyDate <= cast(:EvnRecept_setDate_Range_1 as timestamp)';
					$params['EvnRecept_setDate_Range_1'] = $params['EvnRecept_setDate_Range'][1];
				}
			}
		}

		if(!empty($params['ReceptDelayType_id']) && $params['ReceptDelayType_id'] != 8)
		{
			if($params['ReceptDelayType_id'] == 7)
				$where .= " and ERG.ReceptDelayType_id is null";
			else
				$where .= " and ERG.ReceptDelayType_id = :ReceptDelayType_id";
		}

		//var_dump($params['inValidRecept']);die;
		if(empty($params['inValidRecept']) || $params['inValidRecept'] != 1)
		{
			$where .= " 
				and (ReceptStatus.ReceptStatus is null or ReceptStatus.ReceptStatus != 'prosr')
			";
		}
	
		
		if(!empty($params['Person_Snils']))
		{
			$params['Person_Snils'] = str_replace(' ', '',str_replace('-','',$params['Person_Snils']));
			$where .= " and PS.person_Snils iLIKE (:Person_Snils||'%')";

		}

		if(!empty($params['Polis_Ser']))
			$where .= " and (POMS.Polis_Ser iLIKE (:Polis_Ser||'%') or PDMS.Polis_Ser iLIKE (:Polis_Ser||'%') or PS.Polis_Ser iLIKE (:Polis_Ser||'%'))";


		if(!empty($params['Polis_Num']))
			$where .= " and (POMS.Polis_Num iLIKE (:Polis_Num||'%') or PDMS.Polis_Num iLIKE (:Polis_Num||'%') or PS.Polis_Num iLIKE (:Polis_Num||'%'))";


		if(!empty($params['Polis_EdNum']))
			$where .= " and PS.Person_EdNum iLIKE (:Polis_EdNum||'%')";


		if(!empty($params['Person_Surname']))
			$where .= " and PS.Person_SurName iLIKE (:Person_Surname||'%')";


		if(!empty($params['Person_Firname']))
			$where .= " and PS.Person_FirName iLIKE (:Person_Firname||'%')";


		if(!empty($params['Person_Secname']))
			$where .= " and PS.Person_SecName iLIKE (:Person_Secname||'%')";


		if(!empty($data['Person_Birthday']) )
			$where .= " and PS.Person_BirthDay = :Person_Birthday";

		if(!empty($data['EvnRecept_Ser'])) 
			$where .= " and ERG.EvnReceptGeneral_Ser iLIKE (:EvnRecept_Ser||'%')";


		if(!empty($data['EvnRecept_Num'])) 
			$where .= " and ERG.EvnReceptGeneral_Num iLIKE (:EvnRecept_Num||'%')";


		if(!empty($params['MedPersonal_Name']))
			$where .= " and MP.Person_Fio iLIKE ('%'||:MedPersonal_Name||'%')";


		if(!empty($params['Drug_Name']))
		{
			$where .= "
				and (
					DCM.DrugComplexMnn_RusName iLIKE (:Drug_Name || '%') or

					D.DrugTorg_Name iLIKE (:Drug_Name || '%')

				)
			";
		}

		$query = "
		SELECT * FROM (
            select distinct
			-- select
				ERGDL.EvnReceptGeneralDrugLink_id as \"EvnReceptGeneralDrugLink_id\",
				ERG.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
				GRS.GeneralReceptSupply_id as \"GeneralReceptSupply_id\",
				ERG.Server_id as \"Server_id\",
				ERG.Lpu_id as \"Lpu_id\",
				ERG.Person_id as \"Person_id\",
				ERG.PersonEvn_id as \"PersonEvn_id\",
				RF.ReceptForm_Code || '<br>' || '<p style=\"color: red\">'||COALESCE(RU.ReceptUrgency_Name,'')||'</p>' as \"ReceptForm_Code_Full\",

				RF.ReceptForm_Code as \"ReceptForm_Code_Short\",
				RDT.ReceptDelayType_id as \"ReceptDelayType_id\",
				case when ERG.ReceptDelayType_id is null then 'Выписан' else RDT.ReceptDelayType_Name end as \"ReceptDelayType_Name\",
				COALESCE(RU.ReceptUrgency_id,0) as \"ReceptUrgency_id\",

				RU.ReceptUrgency_Name as \"ReceptUrgency_Name\",
				to_char(ERG.EvnReceptGeneral_begDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				ReceptStatus.ReceptStatus as \"ReceptStatus\",
				ERG.EvnReceptGeneral_Ser as \"EvnRecept_Ser\",
				ERG.EvnReceptGeneral_Num as \"EvnRecept_Num\",
				L.Lpu_Nick as \"ReceptLpu\",
				COALESCE(PS.person_Snils, '') as \"P_Snils\",

				(COALESCE(PS.Person_FirName,'') || ' ' || COALESCE(PS.Person_SecName,'') || ' ' || COALESCE(PS.Person_SurName,'')) as \"P_FIO_Full\",

				(COALESCE(PS.Person_FirName,'') || ' ' || COALESCE(PS.Person_SecName,'') || ' ' || SUBSTRING(COALESCE(PS.Person_SurName,''),1,1)) as \"P_FIO_Short\",

				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

				(
					case when PS.Person_EdNum is not null then PS.Person_EdNum else

						COALESCE(POMS.Polis_Ser,'') || ' ' || COALESCE(POMS.Polis_Num,'') ||

						case when PDMS.Polis_Num is null then ''
						else
							'</br>' || 'ДМС: '|| COALESCE(PDMS.Polis_Ser,'') || ', '|| PDMS.Polis_Num

						end
					end
				) as \"P_POlis\",
				ERGDL.EvnReceptGeneralDrugLink_Kolvo as \"Drug_Kolvo\",
				case when ECTD.Drug_id is null then DCM.DrugComplexMnn_RusName else D.Drug_ShortName end as \"Drug_Name\",
				case when GRS.GeneralReceptSupply_SupplyDate is null then '' else ('Выписан, ' || COALESCE(OrgF.Org_Nick,'')) end as \"Stat_and_Farm\",
				to_char(GRS.GeneralReceptSupply_SupplyDate, 'DD.MM.YYYY') as \"Supp_Date\",
				RTP.ReceptType_Code as \"ReceptType_Code\"
			-- end select
			from
			-- from
			{$from}
			{$join}
			left join OrgFarmacy OrgF_  on OrgF_.OrgFarmacy_id = GRS.OrgFarmacy_id

 			left join Org OrgF  on OrgF.Org_id = OrgF_.Org_id

			inner join v_PersonState PS  on PS.Person_id = ERG.Person_id

			LEFT JOIN LATERAL(

				select MP_t.MedPersonal_id, MP_t.Person_Fio
				from v_MedPersonal MP_t 

				where MP_t.MedPersonal_id = ERG.MedPersonal_id
                limit 1
			) as MP ON true
			inner join v_ReceptForm RF  on RF.ReceptForm_id = ERG.ReceptForm_id

			left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = ERG.ReceptDelayType_id

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ERG.ReceptValid_id

			left join v_ReceptUrgency RU  on RU.ReceptUrgency_id = ERG.ReceptUrgency_id

			inner join v_Lpu L  on L.Lpu_id = ERG.Lpu_id



			LEFT JOIN LATERAL (

				select  PPol.Polis_Ser, PPol.Polis_Num
				from v_PersonPolis PPol
				where PPol.Person_id = PS.Person_id
				and PPol.PolisType_id in (1,4)
				and (PPol.Polis_endDate is null or PPol.Polis_endDate <= dbo.tzGetDate())
				order by PPol.Polis_endDate desc
                limit 1
			) POMS ON true
			LEFT JOIN LATERAL (

				select PPol2.Polis_Ser, PPol2.Polis_Num
				from v_PersonPolis PPol2
				where PPol2.Person_id = PS.Person_id
				and PPol2.PolisType_id = 2
				and (PPol2.Polis_endDate is null or PPol2.Polis_endDate <= dbo.tzGetDate())
				order by PPol2.Polis_endDate desc
                limit 1
			) PDMS ON true

			LEFT JOIN LATERAL

			(
				select case when GRS.GeneralReceptSupply_SupplyDate is not null then 'vypis'
				else
					case when (ERG.EvnReceptGeneral_endDate is not null and ERG.EvnReceptGeneral_endDate <= dbo.tzGetDate()) then 'prosr'
					else
						case when
							dbo.tzGetDate() >=
							case 
								when RV.ReceptValid_Code = 1 then interval '1 month' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 2 then interval '3 month' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 3 then interval '14 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 4 then interval '5 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 5 then interval '2 month' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 7 then interval '10 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 8 then interval '60 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 9 then interval '30 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 10 then interval '90 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 11 then interval '15 day' + ERG.EvnReceptGeneral_begDate
								when RV.ReceptValid_Code = 12 then interval '1 year' + ERG.EvnReceptGeneral_begDate
							end
						then 'prosr' end
					end
				end as ReceptStatus
			) ReceptStatus ON true

			inner join v_EvnCourseTreatDrug ECTD  on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id

			left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id

			left join rls.v_Drug D  on D.Drug_id = ECTD.Drug_id

			-- end from
			where
			-- where
			(1=1)
			{$where}
			-- end where
        ) t
        ORDER BY \"EvnRecept_setDate\"
		";		
		//echo getDebugSQL($query,$params);die;
		//$result = $this->db->query($query,$params);
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}

		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			for($i=0; $i<count($response['data']); $i++)
			{
				if($response['data'][$i]['ReceptUrgency_id'] != '0' && !($response['data'][$i]['ReceptStatus'] == 'prosr' || $response['data'][$i]['ReceptStatus'] == 'vypis'))
				{
					$response['data'][$i]['ReceptForm_Code'] = $response['data'][$i]['ReceptForm_Code_Full'];
				}
				else
				{
					$response['data'][$i]['ReceptForm_Code'] = $response['data'][$i]['ReceptForm_Code_Short'];
				}
				//Получим данные обеспечения:
				$response['data'][$i]['Supp_Info'] = '';
				$query_supp_info = "
					select 

						GRS.GeneralReceptSupply_id as \"GeneralReceptSupply_id\",
						COALESCE(OrgF.Org_Nick,'') as \"Supp_Org\",

						to_char(GRS.GeneralReceptSupply_SupplyDate, 'DD.MM.YYYY') as \"Supp_Date\"

					from v_GeneralReceptSupply GRS 

					left join OrgFarmacy OrgF_  on OrgF_.OrgFarmacy_id = GRS.OrgFarmacy_id

					left join Org OrgF  on OrgF.Org_id = OrgF_.Org_id

					where GRS.EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id
					order by GRS.GeneralReceptSupply_SupplyDate desc
				";
				$params_supp_info = array(
					'EvnReceptGeneralDrugLink_id' => $response['data'][$i]['EvnReceptGeneralDrugLink_id']
				);
				$result_supp_info = $this->db->query($query_supp_info ,$params_supp_info);
				if(is_object($result_supp_info))
				{
					$result_supp_info = $result_supp_info->result('array');
					for($j=0;$j < count($result_supp_info); $j++)
					{
						$response['data'][$i]['Supp_Info'] .= $result_supp_info[$j]['Supp_Date'].' '.$result_supp_info[$j]['Supp_Org'].'<br>';
					}
				}
			}
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	* provideEvnReceptGeneralDrugLink
	*/
	function provideEvnReceptGeneralDrugLink($data)
	{
		$OrgFarmacy_id = null;
		//Получим OrgFarmacy_id
		$query_get_OrgFarmacy = "
			select OrgFarmacy_id as \"OrgFarmacy_id\"
			from OrgFarmacy 

			where Org_id = :Org_id
			limit 1
		";
		$result_get_OrgFarmacy = $this->db->query($query_get_OrgFarmacy,$data);
		if(is_object($result_get_OrgFarmacy))
		{
			$result_get_OrgFarmacy = $result_get_OrgFarmacy->result('array');
			if (is_array($result_get_OrgFarmacy) && count($result_get_OrgFarmacy) > 0)
				$OrgFarmacy_id = $result_get_OrgFarmacy[0]['OrgFarmacy_id'];
		}

		$query_provide = "
			select GeneralReceptSupply_id as \"GeneralReceptSupply_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_GeneralReceptSupply_ins(
				GeneralReceptSupply_id          := 0,
				EvnReceptGeneralDrugLink_id	:= :EvnReceptGeneralDrugLink_id,
				OrgFarmacy_id 					:= {$OrgFarmacy_id},
				GeneralReceptSupply_SupplyDate	:= dbo.tzGetDate(),
				pmUser_id                  	:= :pmUser_id);

		";
		//echo getDebugSQL($query_provide,$data);die;
		$response = $this->queryResult($query_provide,$data);
		//Изменим статус рецепта на "Обслужен"
		$query_upd_status = "
			update EvnReceptGeneral
			set ReceptDelayType_id = 1
			where EvnReceptGeneral_id = (select EvnReceptGeneral_id from EvnReceptGeneralDrugLink where EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id limit 1)
		";
		//echo getDebugSQL($query_upd_status,$data);die;
		$result_upd_status = $this->db->query($query_upd_status,$data);
		return $response;
	}

	/**
	* undo_provideEvnReceptGeneralDrugLink
	*/
	function undo_provideEvnReceptGeneralDrugLink($data)
	{
		$EvnReceptGeneral_id = 0;
		$GeneralReceptSupply_id = 0;
		$response = array();
		$query_get_Recept = "
			select Rec.EvnReceptGeneral_id as \"EvnReceptGeneral_id\"
			from v_GeneralReceptSupply GRS
			LEFT JOIN LATERAL(

				select EvnReceptGeneral_id
				from v_EvnReceptGeneralDrugLink ERGDL
				where ERGDL.EvnReceptGeneralDrugLink_id = GRS.EvnReceptGeneralDrugLink_id
                limit 1
			) as Rec ON true
			where GRS.GeneralReceptSupply_id = :GeneralReceptSupply_id
		";
		$result_get_Recept = $this->db->query($query_get_Recept,$data);
		if(is_object($result_get_Recept))
		{
			$result_get_Recept = $result_get_Recept->result('array');
			if(is_array($result_get_Recept) && count($result_get_Recept)>0)
				$EvnReceptGeneral_id = $result_get_Recept[0]['EvnReceptGeneral_id'];
		}
		
		//В одной строке может быть несколько записей об обеспечении, поэтому делаем хитрый финт ушами и берем последнюю запись (отталкиваясь от EvnReceptGeneralDrugLink_id)
		//EvnReceptGeneralDrugLink_id
		$query_get_Supply_item = "
			select GeneralReceptSupply_id as \"GeneralReceptSupply_id\"
			from v_GeneralReceptSupply 

			where EvnReceptGeneralDrugLink_id = :EvnReceptGeneralDrugLink_id
			order by GeneralReceptSupply_id desc
			limit 1
		";
		$result_get_Supply_item = $this->db->query($query_get_Supply_item,$data);
		if(is_object($result_get_Supply_item))
		{
			$result_get_Supply_item = $result_get_Supply_item->result('array');
			if(is_array($result_get_Supply_item) && count($result_get_Supply_item) > 0)
			{
				$GeneralReceptSupply_id = $result_get_Supply_item[0]['GeneralReceptSupply_id'];
			}
		}
		
		if($GeneralReceptSupply_id > 0)
		{
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_GeneralReceptSupply_del(
					GeneralReceptSupply_id := :GeneralReceptSupply_id);


			";		
			$response = $this->queryResult($query,array('GeneralReceptSupply_id' => $GeneralReceptSupply_id));
		}
		//Если это последнее отмененное обеспечение в рецепте, то очищаем статус рецепта (по дефолту он будет "Выписан")
		if($EvnReceptGeneral_id > 0)
		{
			$query_check_supply = "
				select Supp_Count.SupCtn as \"SupCtn\"
				from v_EvnReceptGeneral ERG

				LEFT JOIN LATERAL(

					select COUNT(GRS.GeneralReceptSupply_id) as SupCtn
					from v_EvnReceptGeneralDrugLink ERGDL 

					inner join v_GeneralReceptSupply GRS on GRS.EvnReceptGeneralDrugLink_id = ERGDL.EvnReceptGeneralDrugLink_id

					where ERGDL.EvnReceptGeneral_id = ERG.EvnReceptGeneral_id
				) as Supp_Count ON true
				where ERG.EvnReceptGeneral_id = :EvnReceptGeneral_id		--730023881012452
			";
			$params_check_supply = array(
				'EvnReceptGeneral_id'	=> $EvnReceptGeneral_id
			);
			$result_check_supply = $this->db->query($query_check_supply,$params_check_supply);
			$need_change_status = true;
			if(is_object($result_check_supply))
			{
				$result_check_supply = $result_check_supply->result('array');
				if(is_array($result_check_supply) && count($result_check_supply)>0)
				{
					if($result_check_supply[0]['SupCtn'] > 0)
						$need_change_status = false;
				}
			}
			if($need_change_status )
			{
				$query_upd_status = "
					update EvnReceptGeneral
					set ReceptDelayType_id = null
					where EvnReceptGeneral_id = :EvnReceptGeneral_id
				";
				$result_upd_status = $this->db->query($query_upd_status,$params_check_supply);
			}
		}
		
		return $response;
	}
	
	/**
	* Получение информации об общем рецепте для панели
	*/
	function getReceptGeneralInfo($data)
	{
		$params = array(
			'EvnReceptGeneral_id'	=> $data['EvnReceptGeneral_id']
		);
		$query = "
		    select 
				ERG.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
				RF.ReceptForm_Name as \"ReceptForm_Name\",
				ERG.EvnReceptGeneral_Ser as \"EvnReceptGeneral_Ser\",
				ERG.EvnReceptGeneral_Num as \"EvnReceptGeneral_Num\",
				to_char(ERG.EvnReceptGeneral_begDate, 'DD.MM.YYYY') as \"EvnReceptGeneral_begDate\",

				to_char(case when ERG.EvnReceptGeneral_endDate is not null then ERG.EvnReceptGeneral_endDate
				else
					case 
						when RV.ReceptValid_Code = 1 then interval '1 month' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 2 then interval '3 month' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 3 then interval '14 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 4 then interval '5 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 5 then interval '2 month' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 7 then interval '10 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 8 then interval '60 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 9 then interval '30 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 10 then interval '90 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 11 then interval '15 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 12 then interval '1 year' + ERG.EvnReceptGeneral_begDate
					end
				end,'dd.mm.yyyy') as \"EvnReceptGeneral_endDate\",
							to_char(ERG.EvnReceptGeneral_begDate, 'DD.MM.YYYY') || '-' ||

				to_char(case when ERG.EvnReceptGeneral_endDate is not null then ERG.EvnReceptGeneral_endDate
				else
					case 
						when RV.ReceptValid_Code = 1 then interval '1 month' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 2 then interval '3 month' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 3 then interval '14 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 4 then interval '5 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 5 then interval '2 month' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 7 then interval '10 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 8 then interval '60 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 9 then interval '30 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 10 then interval '90 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 11 then interval '15 day' + ERG.EvnReceptGeneral_begDate
						when RV.ReceptValid_Code = 12 then interval '1 year' + ERG.EvnReceptGeneral_begDate
					end
				end,'dd.mm.yyyy') as \"EvnReceptGeneral_Period\",
				RV.ReceptValid_Name as \"ReceptValid_Name\",
				MP.Person_Fio as \"MedPersonal_FIO\",
				L.Lpu_Name as \"Lpu_Name\",
				COALESCE(PS.Person_SurName,'') || ' ' || COALESCE(PS.Person_FirName,'') || ' ' || COALESCE(PS.Person_SecName,'') as \"Person_FIO\",

				(
					datediff('year', PS.Person_Birthday, dbo.tzGetDate())
					+ case 
						when date_part('month',PS.Person_Birthday) > date_part('month',dbo.tzGetDate())	or 
						(
							date_part('month',PS.Person_Birthday) = date_part('month',dbo.tzGetDate()) 
							and date_part('day',PS.Person_Birthday) > date_part('day',dbo.tzGetDate())
						)
						then -1 
						else 0 
					end
				)	as \"Person_Age\",
				case when COALESCE(ERG.EvnReceptGeneral_IsSpecNaz,1) = 2 then 'По специальному назначению'

				else case when COALESCE(ERG.EvnReceptGeneral_IsChronicDisease,1) = 2 then 'Хроническому больному'

				else ''
				end end as \"Recept_Attr\",
				case when ERG.EvnReceptGeneral_Period is not null then 'Периодичность: '||ERG.EvnReceptGeneral_Period
				else '' end as \"Recept_Periodicity\",
				COALESCE(RU.ReceptUrgency_Name,'') as \"ReceptUrgency\"

			from v_EvnReceptGeneral ERG 

			inner join v_ReceptForm RF  on RF.ReceptForm_id = ERG.ReceptForm_id

			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ERG.ReceptValid_id

			INNER JOIN LATERAL(

				select MPers.Person_Fio
				from v_MedPersonal MPers  

				where MPers.MedPersonal_id = ERG.MedPersonal_id
                limit 1
			) as MP ON true
			inner join v_Lpu L  on L.Lpu_id = ERG.Lpu_id

			inner join v_PersonState PS  on PS.Person_id = ERG.Person_id

			left join v_ReceptUrgency RU  on RU.ReceptUrgency_id = ERG.ReceptUrgency_id

			where ERG.EvnReceptGeneral_id = :EvnReceptGeneral_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
			return $result->result('array');
		return false;
	}

	/**
	 * Загрузка списка рецептов для ЭМК
	 */
	function loadEvnReceptPanel($data)
	{
		$resp = $this->queryResult("
			select
				'er' || cast(ER.EvnRecept_id as varchar) as \"keyId\",
				ER.EvnRecept_id as \"EvnRecept_id\",
				'EvnRecept' as \"EMDRegistry_ObjectName\",
				case when coalesce(RF.ReceptForm_Code, '') = '148 (к)'
					then 'Рецепты ЛКО'
					else 'Льготные рецепты'
				end as \"groupTitle\",
				ER.Person_id as \"Person_id\",
				ER.Server_id as \"Server_id\",
				ER.PersonEvn_id as \"PersonEvn_id\",
				ER.EvnRecept_Ser as \"EvnRecept_Ser\",
				ER.EvnRecept_Num as \"EvnRecept_Num\",
				ER.Drug_id as \"Drug_id\",
				ER.Drug_rlsid as \"Drug_rlsid\",
				ER.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				(case
					when coalesce(er.Drug_rlsid, er.DrugComplexMnn_id) is not null
					then coalesce(dcm.DrugComplexMnn_RusName, am.RUSNAME,'')
					else coalesce(dm.DrugMnn_Name, d.Drug_Name)
				end) as \"Drug_Name\", -- стало #170933
				to_char(ER.EvnRecept_setDT, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
				ER.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
				ER.EvnRecept_IsPrinted as \"EvnRecept_IsPrinted\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				cast(er.EvnRecept_Kolvo as decimal) as \"EvnRecept_Kolvo\",
				case when 2 = coalesce(ER.EvnRecept_IsDelivery, 1) then 'Выдан уполномоченному лицу' else '' end as \"EvnRecept_IsDelivery\",
				(case
				when coalesce(RF.ReceptForm_Code, '') = '148 (к)'
				then 1 else 0
				end) as \"isKardio\"
			from
				v_EvnRecept ER 
				left join v_ReceptType RT on RT.ReceptType_id = ER.ReceptType_id
				inner join v_ReceptForm RF  on RF.ReceptForm_id = ER.ReceptForm_id
				left join v_Drug D  on D.Drug_id = ER.Drug_id
				left join v_DrugMnn dm  on dm.DrugMnn_id = D.DrugMnn_id
				left join rls.v_Drug DRls  on DRls.Drug_id = ER.Drug_rlsid
				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join rls.v_Actmatters am  on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join v_ReceptDelayType RDT on RDT.ReceptDelayType_id = ER.ReceptDelayType_id
			where
				ER.EvnRecept_pid = :EvnRecept_pid
				and coalesce(RDT.ReceptDelayType_Code,-1) <> 4
				
			union all
				
			select
				'erg' || cast(ERG.EvnReceptGeneral_id as varchar) as \"keyId\",
				ERG.EvnReceptGeneral_id as \"EvnRecept_id\",
				'EvnReceptGeneral' as \"EMDRegistry_ObjectName\",
				'Рецепты за полную стоимость' as \"groupTitle\",
				ERG.Person_id as \"Person_id\",
				ERG.Server_id as \"Server_id\",
				ERG.PersonEvn_id as \"PersonEvn_id\",
				ERG.EvnReceptGeneral_Ser as \"EvnRecept_Ser\",
				ERG.EvnReceptGeneral_Num as \"EvnRecept_Num\",
				ERG.Drug_id as \"Drug_id\",
				ERG.Drug_rlsid as \"Drug_rlsid\",
				ERG.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				--coalesce(D.Drug_Name, DRls.Drug_Name, DCM.DrugComplexMnn_RusName) as Drug_Name, -- было
				(case
					when coalesce(ERG.Drug_rlsid, ERG.DrugComplexMnn_id) is not null
					then coalesce(dcm.DrugComplexMnn_RusName, am.RUSNAME,'')
					else coalesce(dm.DrugMnn_Name, d.Drug_Name)
				end) as \"Drug_Name\", -- стало #170933
				to_char(ERG.EvnReceptGeneral_setDT, 'DD.MM.YYYY') as \"EvnRecept_setDate\",
				ERG.EvnReceptGeneral_IsSigned as \"EvnRecept_IsSigned\",
				ERG.EvnReceptGeneral_IsPrinted as \"EvnRecept_IsPrinted\",
				RT.ReceptType_Code as \"ReceptType_Code\",
				cast(ERG.EvnReceptGeneral_Kolvo as numeric) as \"EvnRecept_Kolvo\",
				'' as \"EvnRecept_IsDelivery\",
				(case
				when coalesce(RF.ReceptForm_Code, '') = '148 (к)'
				then 1 else 0
				end) as \"isKardio\"
			from v_EvnReceptGeneral ERG
				left join v_ReceptType RT on RT.ReceptType_id = ERG.ReceptType_id
				inner join v_ReceptForm RF on RF.ReceptForm_id = ERG.ReceptForm_id
				left join v_Drug D on D.Drug_id = ERG.Drug_id
				left join v_DrugMnn dm on dm.DrugMnn_id = D.DrugMnn_id
				left join rls.v_Drug DRls on DRls.Drug_id = ERG.Drug_rlsid
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = ERG.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join rls.v_Actmatters am on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join v_ReceptDelayType RDT on RDT.ReceptDelayType_id = ERG.ReceptDelayType_id
			where
				ERG.EvnReceptGeneral_pid = :EvnRecept_pid
		", array(
			'EvnRecept_pid' => $data['EvnRecept_pid']
		));

		$EvnReceptIds = [];
		foreach($resp as $one) {
			if (!empty($one['EvnRecept_id']) && $one['EMDRegistry_ObjectName'] == 'EvnRecept' && $one['EvnRecept_IsSigned'] == 2 && !in_array($one['EvnRecept_id'], $EvnReceptIds)) {
				$EvnReceptIds[] = $one['EvnRecept_id'];
			}
		}

		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($EvnReceptIds) && !empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$signStatus = $this->EMD_model->getSignStatus([
				'EMDRegistry_ObjectName' => 'EvnRecept',
				'EMDRegistry_ObjectIDs' => $EvnReceptIds,
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
			]);

			foreach($resp as $key => $one) {
				$resp[$key]['EvnRecept_SignCount'] = 0;
				$resp[$key]['EvnRecept_MinSignCount'] = 0;
				if (!empty($one['EvnRecept_id']) && $one['EMDRegistry_ObjectName'] == 'EvnRecept' && $one['EvnRecept_IsSigned'] == 2 && isset($signStatus[$one['EvnRecept_id']])) {
					$resp[$key]['EvnRecept_SignCount'] = $signStatus[$one['EvnRecept_id']]['signcount'];
					$resp[$key]['EvnRecept_MinSignCount'] = $signStatus[$one['EvnRecept_id']]['minsigncount'];
					$resp[$key]['EvnRecept_IsSigned'] = $signStatus[$one['EvnRecept_id']]['signed'];
				}
			}
		}

		return $resp;
	}

	/**
	 * Загрузка списка рецептов пациента для ЭМК
	 */
	function loadPersonEvnReceptPanel($data)
	{
		$filter = "";

		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and {$diagFilter}";
		}
		$lpuFilter = getAccessRightsLpuFilter('ER.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and {$lpuFilter}";
		}

		$sql = "
			select
				-- select
				recepts.*,
				to_char(recepts.\"EvnRecept_setDate\", 'DD.MM.YYYY') as \"EvnRecept_setDate\"
				-- end select
			FROM
			-- from
			(
				select
					ER.EvnReceptGeneral_id as \"EvnRecept_id\",
					ER.EvnReceptGeneral_rid as \"EvnRecept_rid\",
					'general' as \"isGeneral\",
					ER.Person_id as \"Person_id\",
					ER.Server_id as \"Server_id\",
					ER.PersonEvn_id as \"PersonEvn_id\",
					ER.EvnReceptGeneral_Ser as \"EvnRecept_Ser\",
					ER.EvnReceptGeneral_Num as \"EvnRecept_Num\",
					--id тянем из назначений, которые всегда заносятся из схемы RLS
					null as \"Drug_id\", 
					ECTD.Drug_id as \"Drug_rlsid\",
					ECTD.DrugComplexMnn_id as \"DrugComplexMnn_id\",
					VLpu.Lpu_Name as \"Lpu_Name\",
					ER.Diag_id as \"Diag_id\",
					ER.Lpu_id as \"Lpu_id\",
					coalesce(DRls.Drug_Name, DCM.DrugComplexMnn_RusName) as \"Drug_Name\",
					ERGDL.EvnReceptGeneralDrugLink_updDT as \"EvnRecept_setDate\",
					ER.EvnReceptGeneral_IsSigned as \"EvnRecept_IsSigned\",
					cast(ECTD.EvnCourseTreatDrug_Kolvo as numeric) as \"EvnRecept_Kolvo\",
					case when 2 = COALESCE(ER.EvnReceptGeneral_IsDelivery, 1) then 'Уполномоченному лицу' else '' end as \"EvnRecept_IsDelivery\",
					DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\"
				from
					v_EvnReceptGeneral ER	
					inner join v_EvnReceptGeneralDrugLink ERGDL on ERGDL.EvnReceptGeneral_id = ER.EvnReceptGeneral_id
					inner join v_EvnCourseTreatDrug ECTD on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id
					--left join v_Drug D on D.Drug_id = ECTD.Drug_id --Назначения всегда заносятся из схемы RLS
					left join v_Diag Diag on Diag.Diag_id = ER.Diag_id
					left join rls.v_Drug DRls on DRls.Drug_id = ECTD.Drug_id
					left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id
					left join v_Lpu VLpu on ER.Lpu_id = VLpu.Lpu_id
				where
					ER.Person_id = :Person_id
					{$filter}
	
				union all
	
				select
					ER.EvnRecept_id as \"EvnRecept_id\",
					ER.EvnRecept_rid as \"EvnRecept_rid\",
					'privilege' as \"isGeneral\",
					ER.Person_id as \"Person_id\",
					ER.Server_id as \"Server_id\",
					ER.PersonEvn_id as \"PersonEvn_id\",
					ER.EvnRecept_Ser as \"EvnRecept_Ser\",
					ER.EvnRecept_Num as \"EvnRecept_Num\",
					ER.Drug_id as \"Drug_id\",
					ER.Drug_rlsid as \"Drug_rlsid\",
					ER.DrugComplexMnn_id as \"DrugComplexMnn_id\",
					VLpu.Lpu_Name as \"Lpu_Name\",
					ER.Diag_id as \"Diag_id\",
					ER.Lpu_id as \"Lpu_id\",
					coalesce(D.Drug_Name, DRls.Drug_Name, DCM.DrugComplexMnn_RusName) as \"Drug_Name\",
					ER.EvnRecept_setDT as \"EvnRecept_setDate\",
					ER.EvnRecept_IsSigned as \"EvnRecept_IsSigned\",
					cast(er.EvnRecept_Kolvo as numeric) as \"EvnRecept_Kolvo\",
					case when 2 = COALESCE(ER.EvnRecept_IsDelivery, 1) then 'Уполномоченному лицу' else '' end as \"EvnRecept_IsDelivery\",
					DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\"
				from
					v_EvnRecept ER
					left join v_Diag Diag on Diag.Diag_id = ER.Diag_id
					left join v_Drug D on D.Drug_id = ER.Drug_id
					left join rls.v_Drug DRls on DRls.Drug_id = ER.Drug_rlsid
					left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id
					left join v_Lpu VLpu on ER.Lpu_id = VLpu.Lpu_id
				where
					ER.Person_id = :Person_id
					{$filter}
			) as recepts
			-- end from
			order by
				-- order by
				recepts.\"EvnRecept_setDate\" DESC
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $data);

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = count($response['data']) + intval($data['start']);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Функция для корректировки информации о выписанном количестве в разнарядках
	 */
	function updateDrugRequestPersonOrder($action, $recept_data) {
		$result = array(
			'Error_Msg' => null
		);
		$update_record_cnt = 0; //количество обновленных записей в разнарядке
		$recept_kolvo = 0; //количество медикамента в рецепте
		$move_kolvo = 0; //количество медикамента которое было добавлено или удалено в разнарядку

		if (!empty($recept_data['EvnRecept_id'])) {
			$query = "
				select
					er.Drug_rlsid as \"Drug_rlsid\",
					er.DrugFinance_id as \"DrugFinance_id\",
					er.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
					er.Person_id as \"Person_id\",
					er.MedPersonal_id as \"MedPersonal_id\",
					cast(er.EvnRecept_setDate as date) as \"EvnRecept_setDate\",					
				  	er.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
				  	COALESCE(yn.YesNo_Code, 0) as \"isPersonAllocation\"

				from
					v_EvnRecept er 

					left join v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id 

					left join v_YesNo yn  on yn.YesNo_id = WhsDocumentCostItemType_isPersonAllocation

				where
					er.EvnRecept_id = :EvnRecept_id;
			";
			$recept_data = $this->getFirstRowFromQuery($query, array(
				'EvnRecept_id' => $recept_data['EvnRecept_id']
			));
		}

		if (!empty($recept_data['Drug_rlsid']) && !empty($recept_data['EvnRecept_Kolvo'])) {
			$recept_kolvo = $recept_data['EvnRecept_Kolvo'];

			try {
				//поиск подходящих строк в разнарядке
				$query = "
					WITH cte AS (
        			select
					 DrugComplexMnn_id AS DrugComplexMnn_id,
					 DrugTorg_id AS Tradenames_id
					from
						rls.v_Drug 

					where
						Drug_id = :Drug_rlsid
                    limit 1
                    )
	
					select
						drpo.DrugRequestPersonOrder_id as \"DrugRequestPersonOrder_id\",
						drpo.Drug_id as \"Drug_id\",
						drpo.DrugRequestPersonOrder_OrdKolvo as \"DrugRequestPersonOrder_OrdKolvo\",
						drpo.DrugRequestPersonOrder_Kolvo as \"DrugRequestPersonOrder_Kolvo\",
						(case
							when COALESCE(drr.DrugRequestRow_Kolvo, 0) > COALESCE(drpo_summ.DrugRequestPersonOrder_OrdKolvo, 0) then  COALESCE(drr.DrugRequestRow_Kolvo, 0) - COALESCE(drpo_summ.DrugRequestPersonOrder_OrdKolvo, 0)

							else 0
						end) as \"Reserve_Kolvo\" -- количество нераспределенных медикаментов
					from
						v_DrugRequestPersonOrder drpo 

						left join v_DrugRequest dr  on dr.DrugRequest_id = drpo.DrugRequest_id

						left join v_DrugRequestPeriod drp  on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id

						LEFT JOIN LATERAL (

							select 
								i_df.DrugFinance_id,
								i_drr.DrugRequestRow_Kolvo
							from 
								v_DrugRequestRow i_drr 

								left join v_DrugFinance i_df  on i_df.DrugFinance_id = i_drr.DrugFinance_id 

							where
								i_drr.DrugRequest_id = drpo.DrugRequest_id and
								i_drr.DrugComplexMnn_id = drpo.DrugComplexMnn_id and
								COALESCE(i_drr.TRADENAMES_id, 0) = COALESCE(drpo.Tradenames_id, 0)
							order by
								i_drr.DrugRequestRow_id
							limit 1
						) drr ON true
						LEFT JOIN LATERAL (

							select
								sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as DrugRequestPersonOrder_OrdKolvo
							from
								v_DrugRequestPersonOrder i_drpo 

							where
								i_drpo.Person_id is not null and
								i_drpo.DrugComplexMnn_id = drpo.DrugComplexMnn_id and
								COALESCE(i_drpo.Tradenames_id, 0) = COALESCE(drpo.Tradenames_id, 0)

						) drpo_summ ON true 
						LEFT JOIN LATERAL (

							select 
								i_wdcit.WhsDocumentCostItemType_id
							from
								v_WhsDocumentCostItemType i_wdcit 

							where
								i_wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
								i_wdcit.DrugFinance_id = :DrugFinance_id and
								COALESCE(i_wdcit.PersonRegisterType_id, 0) = COALESCE(dr.PersonRegisterType_id, 0)					
                            limit 1
 
						) wdcit ON true
						LEFT JOIN LATERAL (

							select
								(case
									when drpo.Tradenames_id = (SELECT Tradenames_id FROM cte) then 1
									else 0
								end) as val
						) ord ON true
					where
						drpo.Person_id = :Person_id	and
						drpo.DrugComplexMnn_id = (SELECT DrugComplexMnn_id FROM cte)	and
						drpo.DrugRequestPersonOrder_OrdKolvo > 0 and
						drr.DrugFinance_id = :DrugFinance_id and
						wdcit.WhsDocumentCostItemType_id is not null 
                        and
						drp.DrugRequestPeriod_begDate <= cast(:EvnRecept_setDate as date) and
						drp.DrugRequestPeriod_endDate >= cast(:EvnRecept_setDate as date)
					order by
						ord.val desc, drpo.DrugRequestPersonOrder_id
				";
				$drpo_array = $this->queryResult($query, $recept_data);

				//данная часть функция рассчитана на возможность того что будет найдено несколько подходящих строк в разнарядках

				if (count($drpo_array) > 0) { //если персональная разнарядка выдана
					//Этап 1
					//базовая корректировка данных в разнарядках
					foreach($drpo_array as $drpo_key => $drpo_data) {
						if ($recept_kolvo > $move_kolvo) {
							$drpo_ord_kolvo = $drpo_data['DrugRequestPersonOrder_OrdKolvo']*1 > 0 ? $drpo_data['DrugRequestPersonOrder_OrdKolvo']*1 : 0;
							$drpo_kolvo = $drpo_data['DrugRequestPersonOrder_Kolvo']*1 > 0 ? $drpo_data['DrugRequestPersonOrder_Kolvo']*1 : 0;
							$kolvo = 0;

							switch($action) {
								case 'add':
									if ($drpo_ord_kolvo > $drpo_kolvo) { //если еще не все медикаменты из строки разнарядки выписанны
										$kolvo = $recept_kolvo - $move_kolvo;
										if ($kolvo > $drpo_ord_kolvo - $drpo_kolvo) {
											$kolvo = $drpo_ord_kolvo - $drpo_kolvo;
										}
									}
									break;
								case 'delete':
									if ($drpo_kolvo > 0) { //если в разнарядке есть выписанные медикаменты
										$kolvo = $recept_kolvo - $move_kolvo;
										if ($kolvo > $drpo_kolvo) {
											$kolvo = $drpo_kolvo;
										}
									}

									//при удалении рецепта остатки в разнарядке нужно уменьшать, поэтому делаем количество отрицательным
									$kolvo = $kolvo*(-1);
									break;
							}

							if ($kolvo != 0) {
								$save_response = $this->saveObject('DrugRequestPersonOrder', array(
									'DrugRequestPersonOrder_id' => $drpo_data['DrugRequestPersonOrder_id'],
									'DrugRequestPersonOrder_Kolvo' => $drpo_kolvo+$kolvo
								));
								if (empty($save_response['DrugRequestPersonOrder_id'])) {
									throw new Exception(!empty($save_response['Error_Msg']) ? $save_response['Error_Msg'] : 'При сохранении даннаых в разнарядке произошла ошибка');
								}
								$move_kolvo += abs($kolvo);
								$update_record_cnt++;

								//фиксируем актуальное количества в массиве данных разнарядки (для второго этапа обработки данных)
								$drpo_array[$drpo_key]['DrugRequestPersonOrder_Kolvo'] = $drpo_kolvo+$kolvo;
							}
						}
					}

					//Этап 2
					//если производится увеличение количества медикамента в разнарядках, а первый этап не позволил полностью добавить все количество из рецепта,
					//то пробуем увеличить количество разнаряженых медикаментов за счет резерва
					if ($action == 'add' && $move_kolvo < $recept_kolvo) {
						foreach($drpo_array as $drpo_key => $drpo_data) {
							$drpo_reserve = $drpo_data['Reserve_Kolvo']*1 > 0 ? $drpo_data['Reserve_Kolvo']*1 : 0;

							if ($recept_kolvo > $move_kolvo && $drpo_reserve > 0) {
								$drpo_ord_kolvo = $drpo_data['DrugRequestPersonOrder_OrdKolvo']*1 > 0 ? $drpo_data['DrugRequestPersonOrder_OrdKolvo']*1 : 0;
								$drpo_kolvo = $drpo_data['DrugRequestPersonOrder_Kolvo']*1 > 0 ? $drpo_data['DrugRequestPersonOrder_Kolvo']*1 : 0;

								$kolvo = $recept_kolvo - $move_kolvo;
								if ($kolvo > $drpo_reserve) {
									$kolvo = $drpo_reserve;
								}

								$save_response = $this->saveObject('DrugRequestPersonOrder', array(
									'DrugRequestPersonOrder_id' => $drpo_data['DrugRequestPersonOrder_id'],
									'DrugRequestPersonOrder_OrdKolvo' => $drpo_ord_kolvo+$kolvo,
									'DrugRequestPersonOrder_Kolvo' => $drpo_kolvo+$kolvo
								));
								if (empty($save_response['DrugRequestPersonOrder_id'])) {
									throw new Exception(!empty($save_response['Error_Msg']) ? $save_response['Error_Msg'] : 'При сохранении даннаых в разнарядке произошла ошибка');
								}
								$move_kolvo += $kolvo;
								$update_record_cnt++;
							}
						}
					}

					if ($recept_data['isPersonAllocation'] == '1' && $move_kolvo < $recept_kolvo) { //если в программе ЛЛО есть признак выписки по персональной разнарядке и не для всего объема медикаментов удалочь найти подходящие разнарядки
						throw new Exception('Количество ЛС, указанное в персональной разнарядке пациента недостаточно для выписки рецепта');
					}
				} else { //персональная разнарядка не выдана
					if ($recept_data['isPersonAllocation'] == '1') { //если в программе ЛЛО есть признак выписки по персональной разнарядке
						throw new Exception('Персональная разнарядка на пациента не сформирована и '.($action == 'add' ? 'выписка рецепта не возможна' : 'удаление рецепта не возможно'));
					}
					if ($action == 'add') { //если изначально не удалось найти подходящих строк разнарядок, то пытаемся сделать это за счет заявок врача из рецепта или участка пациента, актуально только в режиме добавления рецепта
						//вычисление идентификаторов участков для пациента
						$query = "
							select
								pc.LpuRegion_id as \"LpuRegion_id\"
							from
								v_PersonCard_all pc 

							where
								pc.Person_id = :Person_id and
								pc.LpuAttachType_id in (1, 4) and -- основное или служебное прикрепление
								(pc.PersonCard_begDate is null or pc.PersonCard_begDate <= cast(:EvnRecept_setDate as date)) and
								(pc.PersonCard_endDate is null or pc.PersonCard_endDate >= cast(:EvnRecept_setDate as date))
						";
						$lpuregion_list = $this->queryList($query, $recept_data);
						$lpuregion_str = is_array($lpuregion_list) && count($lpuregion_list) > 0 ? join(',', $lpuregion_list) : '0';

						//получение данных заявок врача из рецепта и участка пациента, в которых нет ранее обработанных строк разнарядок
						$query = "
							WITH cte AS (								
							select
							 DrugComplexMnn_id AS DrugComplexMnn_id,
							 DrugTorg_id AS Tradenames_id
							from
								rls.v_Drug 

							where
								Drug_id = :Drug_rlsid
                            limit 1
							)
                            
							select
								dr.DrugRequest_id as \"DrugRequest_id\",
								drpo.DrugRequestPersonOrder_id as \"DrugRequestPersonOrder_id\",
								drr_res.Reserve_Kolvo as \"Reserve_Kolvo\", -- количество нераспределенных медикаментов
								drr.DrugComplexMnn_id as \"DrugComplexMnn_id\",
								drr.TRADENAMES_id as \"Tradenames_id\"
							from
								v_DrugRequest dr 

								left join v_DrugRequestPeriod drp  on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id

								left join v_DrugRequestRow drr  on drr.DrugRequest_id = dr.DrugRequest_id

								LEFT JOIN LATERAL (

									select
										sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as DrugRequestPersonOrder_OrdKolvo
									from
										v_DrugRequestPersonOrder i_drpo 

									where
										i_drpo.Person_id is not null and
										i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id and
										COALESCE(i_drpo.Tradenames_id, 0) = COALESCE(drr.TRADENAMES_id, 0)

								) drpo_summ ON true
								LEFT JOIN LATERAL ( -- Блок для перестраховки, в нормальных условиях в нем всегда должно быть пустой результат выборки. Если понадобится, то для оптимизации можно его удалить.

									select 
										i_drpo.DrugRequestPersonOrder_id
									from
										v_DrugRequestPersonOrder i_drpo 

									where
										i_drpo.DrugRequest_id = dr.DrugRequest_id and
										i_drpo.Person_id = :Person_id and
										i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id and
										i_drpo.DrugRequestPersonOrder_OrdKolvo > 0
                                    limit 1
								) drpo ON true
								LEFT JOIN LATERAL (

									select
										(case
											when COALESCE(drr.DrugRequestRow_Kolvo, 0) > COALESCE(drpo_summ.DrugRequestPersonOrder_OrdKolvo, 0) then  COALESCE(drr.DrugRequestRow_Kolvo, 0) - COALESCE(drpo_summ.DrugRequestPersonOrder_OrdKolvo, 0)

											else 0
										end) as Reserve_Kolvo
								) drr_res ON true
								LEFT JOIN LATERAL (

									select 
										i_wdcit.WhsDocumentCostItemType_id
									from
										v_WhsDocumentCostItemType i_wdcit 

									where
										i_wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
										i_wdcit.DrugFinance_id = :DrugFinance_id and
										COALESCE(i_wdcit.PersonRegisterType_id, 0) = COALESCE(dr.PersonRegisterType_id, 0)					
									limit 1
								) wdcit ON true
								LEFT JOIN LATERAL (

									select
										(case
											when drr.Tradenames_id = (SELECT Tradenames_id FROM cte) then 1
											else 0
										end) as val
								) ord ON true
							where
								(
									dr.MedPersonal_id = :MedPersonal_id or
									dr.LpuRegion_id in ({$lpuregion_str})
								) and
								drr.DrugComplexMnn_id = (SELECT DrugComplexMnn_id FROM cte)	and
								drr.DrugFinance_id = :DrugFinance_id and
								wdcit.WhsDocumentCostItemType_id is not null and
								drp.DrugRequestPeriod_begDate <= cast(:EvnRecept_setDate as date) and
								drp.DrugRequestPeriod_endDate >= cast(:EvnRecept_setDate as date) and
								drpo.DrugRequestPersonOrder_id is null -- в заявке не должно быть разнарядок по заданному сочетанию пациент+медикамент
							order by
								ord.val desc, drr.DrugRequestRow_id
						";
						$drpo_array = $this->queryResult($query, $recept_data);

						if (count($drpo_array) > 0) {
							foreach($drpo_array as $drpo_key => $drpo_data) {
								$reserve_kolvo = $drpo_data['Reserve_Kolvo']*1 > 0 ? $drpo_data['Reserve_Kolvo']*1 : 0;

								if ($recept_kolvo > $move_kolvo && $reserve_kolvo > 0) {
									$kolvo = $recept_kolvo - $move_kolvo;
									if ($kolvo > $reserve_kolvo) {
										$kolvo = $reserve_kolvo;
									}

									//добавление новой строки в разнарядку
									$save_response = $this->saveObject('DrugRequestPersonOrder', array(
										'DrugRequestPersonOrder_id' => null,
										'DrugRequest_id' => $drpo_data['DrugRequest_id'],
										'Person_id' => $recept_data['Person_id'],
										'MedPersonal_id' => $recept_data['MedPersonal_id'],
										'DrugComplexMnn_id' => $drpo_data['DrugComplexMnn_id'],
										'Tradenames_id' => $drpo_data['DrugComplexMnn_id'],
										'DrugRequestPersonOrder_OrdKolvo' => $kolvo,
										'DrugRequestPersonOrder_Kolvo' => $kolvo
									));
									if (empty($save_response['DrugRequestPersonOrder_id'])) {
										throw new Exception(!empty($save_response['Error_Msg']) ? $save_response['Error_Msg'] : 'При сохранении даннаых в разнарядке произошла ошибка');
									}
									$move_kolvo += $kolvo;
									$update_record_cnt++;
								}
							}
							if ($move_kolvo < $recept_kolvo) { //если не для всего объема медикаментов удалось найти подходящие строки заявок
								throw new Exception('Количество ЛС в заявках недостаточно для выписки рецепта');
							}
						} else {
							throw new Exception('Рецепт не может быть выписан, т.к. количество медикамента в рецепте превышает количество, доступное для выписки в соответствии с заявкой врача или участка.');
						}
					}
				}
			} catch (Exception $e) {
				$result['Error_Msg'] = $e->getMessage();
			}
		}

		$result['update_record_cnt'] = $update_record_cnt;
		$result['recept_kolvo'] = $recept_kolvo;
		$result['move_kolvo'] = $move_kolvo;

		return array($result);
	}

	/**
	 * Возвращает значение NumeratorObject_Query и наименование по идентификатору программы ЛЛО
	 */
	function getNumeratorObjectQueryByWhsDocumentCostItemTypeId($wdcit_id) {
		$result = array(
			'query' => "",
			'name' => ""
		);

		$query = "
			select
				WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				WhsDocumentCostItemType_Nick as \"WhsDocumentCostItemType_Nick\"
			from
				v_WhsDocumentCostItemType 

			where
				WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
		";
		$wdcit_data = $this->getFirstRowFromQuery($query, array(
			'WhsDocumentCostItemType_id' => $wdcit_id
		));

		if (!empty($wdcit_data['WhsDocumentCostItemType_id'])) {
			$result['query'] = "WhsDocumentCostItemType_Nick='{$wdcit_data['WhsDocumentCostItemType_Nick']}'";
			$result['name'] = "Выписка льготного рецепта {$wdcit_data['WhsDocumentCostItemType_Name']}";
		}

		return $result;
	}

	/**
	 * Возвращает дату выписки пациенту последнего рецепта по программе "ДЛО Кардио"
	 */
	function getLastDLOKardioReceptDate($data) {
		if ( !empty($data['Person_id']) ) {
			$queryParams['Person_id'] = $data['Person_id'];
		} else {
			return false;
		}

		$query = "
			select
				ER.EvnRecept_id as \"EvnRecept_id\",
				ER.EvnRecept_setDT as \"EvnRecept_setDT\"
			from v_EvnRecept ER
			left join v_PrivilegeType PT on PT.PrivilegeType_id = ER.PrivilegeType_id
			where ER.Person_id = :Person_id and 
				  PT.PrivilegeType_SysNick = 'kardio'						--ДЛО Кардио
			order by ER.EvnRecept_id DESC
			limit 1
		";
		$result = $this->db->query(
			$query,
			$queryParams
		);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение даных о последней модерации по льготе
	 */
	function getLastPersonPrivilegeModerationData($data) {
		//параметры модерации
		$moderation_period = 12; //переодичность првоедения модерации в количестве месяцев
		$attention_period = 3; //количество месяцев до окончания периода, когда следует напомнить о предстоящей повторной модерации

		$result = array(
			'PersonPrivilege_begDate' => null, //дата начала действующей льготы
			'LastRequest_Date' => null, //дата последнего принятого запроса на включение в регистр,
			'LastRequest_Status' => null, //статус последнего запроса по льготе
			'LastAcceptedRequest_Date' => null, //дата принятого запроса на включение в регистр,
			'BeforeCreatedRecept_Cnt' => 0, //количество рецептов созданных по льготе до даты текущего рецепта
			'NextModeration_Date' => null, //дата следующей модерации
			'NextAttention_Date' => null, //дата предупреждения о следующей модерации
			'MonthsBeforeNextModeration' => 0 //целое количество месяцев, оставшееся до следующей модерации
		);

		try {
			if (empty($data['Person_id']) || empty($data['PrivilegeType_id']) || empty($data['EvnRecept_setDate'])) {
				throw new Exception("Не указаны обязательные параметры");
			}

			//получение данных действующей льготы
			$query = "
				select
					to_char(pp.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"PersonPrivilege_begDate\"
				from
					v_PersonPrivilege pp
				where
					pp.PrivilegeType_id = :PrivilegeType_id and
					pp.Person_id = :Person_id and
					(pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate > :EvnRecept_setDate) and 
					pp.PersonPrivilege_begDate <= :EvnRecept_setDate
				order by
					pp.PersonPrivilege_id desc
				limit 1;
			";
			$priv_data = $this->getFirstRowFromQuery($query, $data);
			if ($priv_data && count($priv_data) > 0) {
				$result['PersonPrivilege_begDate'] = $priv_data['PersonPrivilege_begDate'];
			}

			//получение данных последнего запроса по льготе
			$query = "
				select
					to_char(ppr.PersonPrivilegeReq_setDT, 'DD.MM.YYYY') as \"LastRequest_Date\",
					ppra.PersonPrivilegeReqStatus_id as \"LastRequest_Status\"
				from
					v_PersonPrivilegeReq ppr
					left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
				where
					ppr.Person_id = :Person_id and
					ppr.PrivilegeType_id = :PrivilegeType_id
				order by
					ppr.PersonPrivilegeReq_id desc
				limit 1;
			";
			$req_data = $this->getFirstRowFromQuery($query, $data);
			if ($req_data && count($req_data) > 0) {
				$result['LastRequest_Date'] = $req_data['LastRequest_Date'];
				$result['LastRequest_Status'] = $req_data['LastRequest_Status'];
			}

			//список рецептов выписанных ранее по льготе
			$query = "
				select
					count(er.EvnRecept_id) as \"cnt\"
				from
					v_EvnRecept er
				where
					er.Person_id = :Person_id and
					er.PrivilegeType_id = :PrivilegeType_id and 
					er.EvnRecept_setDate < :EvnRecept_setDate
				limit 1;
			";
			$rec_data = $this->getFirstRowFromQuery($query, $data);
			if ($rec_data && count($rec_data) > 0) {
				$result['BeforeCreatedRecept_Cnt'] = $rec_data['cnt'];
			}

			//расчет параметров следующей модерации
			$query = "				
				with cte1 as (
					select
						ppr.PersonPrivilegeReq_setDT as LastAcceptedRequest_Date
					from
						v_PersonPrivilegeReq ppr
						left join v_PersonPrivilegeReqAns ppra on ppra.PersonPrivilegeReq_id = ppr.PersonPrivilegeReq_id
					where
						ppr.Person_id = :Person_id and
						ppr.PrivilegeType_id = :PrivilegeType_id and
						ppra.PersonPrivilegeReqStatus_id = 3 and -- Ответ получен
						ppra.PersonPrivilegeReqAns_IsInReg = 2 -- Пациент включен в регистр
					order by
						ppr.PersonPrivilegeReq_id desc
					limit 1
				),
				cte2 as (
					select
						dbo.tzgetdate() as CurrentDate,
						date_add('month', :moderation_period_month, (select LastAcceptedRequest_Date from cte1)) as NextModeration_Date,
						date_add('month', :attention_period_month, (select LastAcceptedRequest_Date from cte1)) as NextAttention_Date
				)
				select
					to_char((select LastAcceptedRequest_Date from cte1), 'DD.MM.YYYY') as \"LastAcceptedRequest_Date\",
					to_char((select NextModeration_Date from cte2), 'DD.MM.YYYY') as \"NextModeration_Date\",
					to_char((select NextAttention_Date from cte2), 'DD.MM.YYYY') as \"NextAttention_Date\",
					(case -- расчет количества целых месяцев до даты следующей модерации
						when (select NextModeration_Date from cte2) is not null then (
							(date_part('month', (select NextModeration_Date from cte2))+(date_part('year', (select NextModeration_Date from cte2))*12))-
							(date_part('month', (select CurrentDate from cte2))+(date_part('year', (select CurrentDate from cte2))*12))-
							(case when date_part('day', (select NextModeration_Date from cte2)) < date_part('day', (select CurrentDate from cte2)) then 1 else 0 end)
						)
						else null
					end) as \"MonthsBeforeNextModeration\";
			";
			$req_data = $this->getFirstRowFromQuery($query, array(
				'moderation_period_month' => $moderation_period,
				'attention_period_month' => ($moderation_period - $attention_period),
				'Person_id' => $data['Person_id'],
				'PrivilegeType_id' => $data['PrivilegeType_id']
			));

			if ($req_data) {
				$result['LastAcceptedRequest_Date'] = $req_data['LastAcceptedRequest_Date'];
				$result['NextModeration_Date'] = $req_data['NextModeration_Date'];
				$result['NextAttention_Date'] = $req_data['NextAttention_Date'];
				$result['MonthsBeforeNextModeration'] = $req_data['MonthsBeforeNextModeration'];
			}

			$result['success'] = true;
		} catch (Exception $e) {
			$result['success'] = false;
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}
	
	/**
	 *	Возвращает данные об адресе и документе пациента
	 */
	function getPersonAddressAndDocData($data) {
		$query = "
			select
				ua.Address_id as \"Address_id\",
				ua.Address_Address as \"Address_Address\",
				ua.KLCity_id as \"KLCity_id\",
				ua.KLSubRgn_id as \"KLSubRgn_id\",
				ua.KLStreet_id as \"KLStreet_id\",
				d.Document_Num as \"Document_Num\",
				d.OrgDep_id as \"OrgDep_id\",
				to_char(d.Document_begDate, 'YYYY-MM-DD') as \"Document_begDate\"
			from
				v_PersonState ps
				inner join v_Address_all ua on ua.Address_id = coalesce(ps.UAddress_id, ps.PAddress_id)
				left join v_Document d on d.Document_id = ps.Document_id
			where
				ps.Person_id = :Person_id;
		";
		$person_data = $this->getFirstRowFromQuery($query, array(
			'Person_id' => $data['Person_id']
		));

		return $person_data;
	}

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadPersonAmbulatCardCombo($data) {
		$where = array();
		$params = array();

		if (!empty($data['PersonAmbulatCard_id'])) {
			$where[] = "pac.PersonAmbulatCard_id = :PersonAmbulatCard_id";
			$params['PersonAmbulatCard_id'] = $data['PersonAmbulatCard_id'];
		} else {
			if (!empty($data['Lpu_id'])) {
				$where[] = "pac.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}
			if (!empty($data['Person_id'])) {
				$where[] = "pac.Person_id = :Person_id";
				$params['Person_id'] = $data['Person_id'];
			}
			if (!empty($data['LpuAttachType_Code'])) {
				$where[] = "lat.LpuAttachType_Code = :LpuAttachType_Code";
				$params['LpuAttachType_Code'] = $data['LpuAttachType_Code'];
			}
			if (!empty($data['Date'])) {
				$where[] = "(pac.PersonAmbulatCard_begDate is null or cast(pac.PersonAmbulatCard_begDate as date) <= :Date)";
				$where[] = "(pac.PersonAmbulatCard_endDate is null or cast(pac.PersonAmbulatCard_endDate as date) >= :Date)";
				$params['Date'] = $data['Date'];
			}
			if (!empty($data['query'])) {
				$where[] = "pac.PersonAmbulatCard_Num ilike :query";
				$params['query'] = "%".$data['query']."%";
			}
			$where[] = "lat.LpuAttachType_id is not null";
		}
		
		$name_field = "(coalesce(pac.PersonAmbulatCard_Num, '')||coalesce(' ('||lat.LpuAttachType_Name||')', ''))";
		if (getRegionNick() == 'msk') {
			$name_field = "coalesce(pac.PersonAmbulatCard_Num, '')";
		}

		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
            select
				pac.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				{$name_field} as \"PersonAmbulatCard_Name\"
			from
				v_PersonAmbulatCard pac
				left join lateral (
					select
						i_lat.LpuAttachType_id,
						i_lat.LpuAttachType_Code,
						i_lat.LpuAttachType_Name
					from
						v_PersonAmbulatCardLink i_pacl
						left join v_PersonCard i_pc on i_pc.PersonCard_id = i_pacl.PersonCard_id
						left join v_LpuAttachType i_lat on i_lat.LpuAttachType_id = i_pc.LpuAttachType_id
					where
						i_pacl.PersonAmbulatCard_id = pac.PersonAmbulatCard_id and
						i_lat.LpuAttachType_Code in ('1', '4') -- основное или служебное прикрепление
					order by
						i_lat.LpuAttachType_id
					limit 1
				) lat on true
			{$where_clause}
			order by
				lat.LpuAttachType_id, pac.PersonAmbulatCard_id desc
			limit 500
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Проверка, является ли лекарственный препарат сильнодействующим или наркотическим
	 */
	function isNarcoOrStrongDrug($data)
	{
		$params = array();

		if (!empty($data['DrugComplexMnn_id'])) {
			$query = "
			select count (*) as \"NarcoOrStrongDrugCount\"
			from rls.DrugComplexMnn dcm
				left join rls.ACTMATTERS ac on ac.ACTMATTERS_ID = dcm.ACTMATTERS_ID
				left join rls.STRONGGROUPS sr on sr.STRONGGROUPS_ID = ac.STRONGGROUPID
				left join rls.NARCOGROUPS nr on nr.NARCOGROUPS_ID = ac.NARCOGROUPID
			where 
				dcm.DrugComplexMnn_id = :DrugComplexMnn_id
				and (ac.STRONGGROUPID is not null and ac.STRONGGROUPID>0) 
				or (ac.NARCOGROUPID is not null and ac.NARCOGROUPID>0)
		";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		} else {
			$query = "
			select count (*) as \"NarcoOrStrongDrugCount\"
			from dbo.Drug dg			
			where 
				dg.Drug_id = :Drug_id and
				dg.DrugClass_id in (8, 9)          -- 8-наркотические, 9-сильнодействующие лекарственные средства
			";
			$params['Drug_id'] = $data['Drug_id'];
		}

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем максимальное количество упаковок лекарственного препарата на 1 месяц
	 */
	function getDosKurs($data) {
		if ( !empty($data['DrugComplexMnn_id']) ) {
			$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			$queryParams['Region_id'] = $this->getRegionNumber();
		} else {
			return false;
		}

		$query = "
			select
				DCMC.DrugComplexMnnCode_id as \"DrugComplexMnnCode_id\",
				DCMC.DrugComplexMnnCode_DosKurs as \"DrugComplexMnnCode_DosKurs\"
			from rls.v_DrugComplexMnnCode DCMC
			where DCMC.DrugComplexMnn_id = :DrugComplexMnn_id
			  and DCMC.Region_id = :Region_id
			order by DrugComplexMnnCode_id
			limit 1
		";
		$result = $this->db->query(
			$query,
			$queryParams
		);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для подписания рецепта в виде JSON
	 */
	function getEvnReceptJSON($data) {
		$resp = $this->queryResult("
			select
				er.EvnRecept_id as \"EvnRecept_id\",
				kla.KLAdr_Ocatd as \"KLAdr_Ocatd\",
				l.Lpu_Name as \"Lpu_Name\",
				l.Lpu_Phone as \"Lpu_Phone\",
				l.UAddress_Address as \"UAddress_Address\",
				l.Lpu_OGRN as \"Lpu_OGRN\",
				date_part('day', er.EvnRecept_setDate) as \"EvnRecept_setDateDay\",
				date_part('month', er.EvnRecept_setDate) as \"EvnRecept_setDateMonth\",
				date_part('year', er.EvnRecept_setDate) as \"EvnRecept_setDateYear\",
				date_part('day', case
					when rv.ReceptValidType_id = 1 then er.EvnRecept_setDT + cast(rv.ReceptValid_Value || ' day' as interval)
					when rv.ReceptValidType_id = 2 then er.EvnRecept_setDT + cast(rv.ReceptValid_Value || ' month' as interval)
				end) as \"EvnRecept_disDateDay\",
				date_part('month', case
					when rv.ReceptValidType_id = 1 then er.EvnRecept_setDT + cast(rv.ReceptValid_Value || ' day' as interval)
					when rv.ReceptValidType_id = 2 then er.EvnRecept_setDT + cast(rv.ReceptValid_Value || ' month' as interval)
				end) as \"EvnRecept_disDateMonth\",
				date_part('year', case
					when rv.ReceptValidType_id = 1 then er.EvnRecept_setDT + cast(rv.ReceptValid_Value || ' day' as interval)
					when rv.ReceptValidType_id = 2 then er.EvnRecept_setDT + cast(rv.ReceptValid_Value || ' month' as interval)
				end) as \"EvnRecept_disDateYear\",
				er.EvnRecept_Ser as \"EvnRecept_Ser\",
				er.EvnRecept_Num as \"EvnRecept_Num\",
				ru.ReceptUrgency_Name as \"ReceptUrgency_Name\",
				ps.Person_id as \"Person_id\",
				ps.Person_SNILS as \"Person_SNILS\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				date_part('day', ps.Person_BirthDay) as Person_BirthDayDay,
				date_part('month', ps.Person_BirthDay) as Person_BirthDayMonth,
				date_part('year', ps.Person_BirthDay) as Person_BirthDayYear,
				ps.Polis_Num as \"Polis_Num\",
				pa.Address_Address as \"PAddress_Address\",
				mp.MedPersonal_Code as \"MedPersonal_DloCode\",
				mp.Person_SurName as \"MedPersonal_SurName\",
				mp.Person_FirName as \"MedPersonal_FirName\",
				mp.Person_SecName as \"MedPersonal_SecName\",
				er.EvnRecept_Signa as \"EvnRecept_Signa\",
				er.EvnRecept_IsKEK as \"EvnRecept_IsKEK\",
				er.CauseVK_id as \"CauseVK_id\",
				er.PrescrSpecCause_id as \"PrescrSpecCause_id\",
				pt.PrivilegeType_Code as \"PrivilegeType_Code\",
				pt.PrivilegeType_Name as \"PrivilegeType_Name\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				df.DrugFinance_SysNick as \"DrugFinance_SysNick\",
				rd.ReceptDiscount_Code as \"ReceptDiscount_Code\",
				case when dcm.DrugComplexMnn_id is not null then am.Actmatters_id else dm.DrugMnn_id end as \"DrugMnn_id\",
				case when dcm.DrugComplexMnn_id is not null then dmc.DrugMnnCode_Code else dm.DrugMnn_Code end as \"DrugMnn_Code\",
				case when dcm.DrugComplexMnn_id is not null then am.rusname else dm.DrugMnn_Name end as \"DrugMnn_Name\",
				case when dcm.DrugComplexMnn_id is not null then am.latname else dm.DrugMnn_NameLat end as \"DrugMnn_NameLat\",
				dt.DrugTorg_id as \"DrugTorg_id\",
				dt.DrugTorg_Code as \"DrugTorg_Code\",
				dt.DrugTorg_Name as \"DrugTorg_Name\",
				dt.DrugTorg_NameLat as \"DrugTorg_NameLat\",
				case when dcm.DrugComplexMnn_id is not null then cdf.CLSDRUGFORMS_id else dfo.DrugForm_id end as \"DrugForm_id\",
				case when dcm.DrugComplexMnn_id is not null then cdf.NAME else dfo.DrugForm_Name end as \"DrugForm_Name\",
				case when dcm.DrugComplexMnn_id is not null then dcmd.DrugComplexMnnDose_Name else dr.Drug_DoseQ end as \"Drug_DoseQ\",
				case when dcm.DrugComplexMnn_id is not null then '' else dr.Drug_DoseEi end as \"Drug_DoseEi\",
				case when dcm.DrugComplexMnn_id is not null then dp.NAME else dfv.DrugFormVip_Name end as \"DrugFormVip_Name\",
				case when dcm.DrugComplexMnn_id is not null then n.PPACKMASS else dr.Drug_Mass end as \"Drug_Mass\",
				case when dcm.DrugComplexMnn_id is not null then n.PPACKMASSUNID else dem.DrugEdMass_Name end as \"DrugEdMass_Name\",
				case when dcm.DrugComplexMnn_id is not null then n.PPACKVOLUME else dr.Drug_Vol end as \"Drug_Vol\",
				case when dcm.DrugComplexMnn_id is not null then n.PPACKCUBUNID else dev.DrugEdVol_Name end as \"DrugEdVol_Name\",
				case when dcm.DrugComplexMnn_id is not null then dcmd.DrugComplexMnnDose_Kol else dr.Drug_DoseCount end as \"Drug_DoseCount,\" 
				case when dcm.DrugComplexMnn_id is not null then dcm.DrugComplexMnn_Fas else dr.Drug_Fas end as \"Drug_Fas\",
			    er.EvnRecept_IsExcessDose as \"EvnRecept_IsExcessDose\",
			    rf.ReceptForm_Name as \"ReceptForm_Name\"
			from
				v_EvnRecept er
				left join v_ReceptForm rf on rf.ReceptForm_id = ER.ReceptForm_id
				left join v_Lpu l on l.Lpu_id = er.Lpu_id
				left join v_Address a on a.Address_id = l.UAddress_id
				left join v_KLArea kla on kla.KLArea_id = a.KLRgn_id
				left join v_ReceptValid rv on rv.ReceptValid_id = er.ReceptValid_id
				left join v_ReceptUrgency ru on ru.ReceptUrgency_id = er.ReceptUrgency_id
				left join v_PersonState ps on ps.Person_id = er.Person_id
				left join v_Address pa on pa.Address_id = ps.PAddress_id
				left join v_MedPersonal mp on mp.MedPersonal_id = er.MedPersonal_id and mp.Lpu_id = er.Lpu_id
				left join v_PrivilegeType pt on pt.PrivilegeType_id = er.PrivilegeType_id
				left join v_Diag d on d.Diag_id = er.Diag_id
				left join v_DrugFinance df on df.DrugFinance_id = er.DrugFinance_id
				left join v_ReceptDiscount rd on rd.ReceptDiscount_id = er.ReceptDiscount_id
				left join v_Drug dr on dr.Drug_id = er.Drug_id
				left join v_DrugMnn dm on dm.DrugMnn_id = dr.DrugMnn_id
				left join v_DrugForm dfo on dfo.DrugForm_id = dr.DrugForm_id
				left join DrugFormVip dfv on dfv.DrugFormVip_id = dr.DrugFormVip_id
				left join v_DrugEdMass dem on dem.DrugEdMass_id = dr.DrugEdMass_id
				left join v_DrugEdVol dev on dev.DrugEdVol_id = dr.DrugEdVol_id
				left join rls.v_Drug rdr on rdr.Drug_id = er.Drug_rlsid
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(rdr.DrugComplexMnn_id, er.DrugComplexMnn_id)
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_Actmatters am on am.Actmatters_id = dcmn.Actmatters_id
				left join rls.v_DrugMnnCode dmc on dmc.Actmatters_id = dcmn.Actmatters_id
				left join rls.v_DrugComplexMnnDose dcmd on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join v_DrugTorg dt on dt.DrugTorg_id = coalesce(dr.DrugTorg_id, rdr.DrugTorg_id)
				left join rls.CLSDRUGFORMS cdf on cdf.CLSDRUGFORMS_id = dcm.CLSDRUGFORMS_id
				left join rls.Nomen n on n.NOMEN_ID = rdr.Drug_id
				left join rls.DRUGPACK dp on dp.DRUGPACK_id = n.PPACKID
			where
				er.EvnRecept_id = :EvnRecept_id
		", [
			'EvnRecept_id' => $data['EvnRecept_id']
		]);

		if (empty($resp[0]['EvnRecept_id'])) {
			return ['Error_Msg' => 'Ошибка получения данных по рецепту'];
		}

		$WhsDocumentCostItemType_Code = null;
		$WhsDocumentCostItemType_Name = null;
		switch($resp[0]['DrugFinance_SysNick']) {
			case 'fed':
				$WhsDocumentCostItemType_Code = 1;
				$WhsDocumentCostItemType_Name = 'федеральный бюджет';
				break;
			case 'reg':
				$WhsDocumentCostItemType_Code = 2;
				$WhsDocumentCostItemType_Name = 'бюджет субъекта Российской Федерации';
				break;
			case 'mest':
				$WhsDocumentCostItemType_Code = 3;
				$WhsDocumentCostItemType_Name = 'муниципальный бюджет';
				break;
		}

		$ReceptDiscount_Name = null;
		switch($resp[0]['ReceptDiscount_Code']) {
			case 1:
				$ReceptDiscount_Name = 'бесплатно';
				break;
			case 2:
				$ReceptDiscount_Name = '50%';
				break;
		}

		$arr = [
			"okato" => $resp[0]['KLAdr_Ocatd'],
			"lpu" => [
				"name" => $resp[0]['Lpu_Name'],
				"phone" => $resp[0]['Lpu_Phone'],
				"adress" => $resp[0]['UAddress_Address'],
				"orgn" => $resp[0]['Lpu_OGRN'],
			],
			"begDate" => [
				"day" => $resp[0]['EvnRecept_setDateDay'],
				"month" => $resp[0]['EvnRecept_setDateMonth'],
				"year" => $resp[0]['EvnRecept_setDateYear']
			],
			"endDate" => [
				"day" => $resp[0]['EvnRecept_disDateDay'],
				"month" => $resp[0]['EvnRecept_disDateMonth'],
				"year" => $resp[0]['EvnRecept_disDateYear']
			],
			"receptform" => $resp[0]['ReceptForm_Name'],
			"series" => $resp[0]['EvnRecept_Ser'],
			"number" => $resp[0]['EvnRecept_Num'],
			"urgency" => $resp[0]['ReceptUrgency_Name'],
			"person" => [
				"id" => $resp[0]['Person_id'],
				"snils" => $resp[0]['Person_SNILS'],
				"surname " => $resp[0]['Person_SurName'],
				"firname" => $resp[0]['Person_FirName'],
				"secname " => $resp[0]['Person_SecName'],
				"birthday" => [
					"day" => $resp[0]['Person_BirthDayDay'],
					"month" => $resp[0]['Person_BirthDayMonth'],
					"year" => $resp[0]['Person_BirthDayYear']
				],
				"polis" => $resp[0]['Polis_Num'],
				"adress" => $resp[0]['PAddress_Address']
			],
			"medPersonal " => [
				"code" => $resp[0]['MedPersonal_DloCode'],
				"fio " => $resp[0]['MedPersonal_SurName'] . ' ' . $resp[0]['MedPersonal_FirName'] . ' ' . $resp[0]['MedPersonal_SecName']
			],
			"drug" => [
				"drugMnn" => [
					"id" => $resp[0]['DrugMnn_id'],
					"code" => $resp[0]['DrugMnn_Code'],
					"name" => $resp[0]['DrugMnn_Name'],
					"latname" => $resp[0]['DrugMnn_NameLat']
				],
				"drugTorg" => [
					"id" => $resp[0]['DrugTorg_id'],
					"code" => $resp[0]['DrugTorg_Code'],
					"name" => $resp[0]['DrugTorg_Name'],
					"latname" => $resp[0]['DrugTorg_NameLat']
				],
				"drugForm" => [
					"id" => $resp[0]['DrugForm_id'],
					"name" => $resp[0]['DrugForm_Name'],
				],
				"drugDoseQ" => $resp[0]['Drug_DoseQ'] . ($resp[0]['EvnRecept_IsExcessDose'] == 2 ? '!' : ''),
				"drugDoseQEi" => $resp[0]['Drug_DoseEi'],
				"drugPackName" => $resp[0]['DrugFormVip_Name'],
				"drugPackQMass" => $resp[0]['Drug_Mass'],
				"drugPackQMassEi" => $resp[0]['DrugEdMass_Name'],
				"drugPackQVol" => $resp[0]['Drug_Vol'],
				"drugPackQVolEi" => $resp[0]['DrugEdVol_Name'],
				"drugDoseCount" => $resp[0]['Drug_DoseCount'],
				"drugFas" => $resp[0]['Drug_Fas']
			],
			"signa" => $resp[0]['EvnRecept_Signa'],
			"isVk" => $resp[0]['EvnRecept_IsKEK'] == 2,
			"isVkSpec" => $resp[0]['CauseVK_id'] == 1,
			"isKol" => $resp[0]['PrescrSpecCause_id'] == 1,
			"isCourse" => in_array($resp[0]['PrescrSpecCause_id'], [2,3]),
			"privilegeType" => [
				"code" => $resp[0]['PrivilegeType_Code'],
				"name" => $resp[0]['PrivilegeType_Name']
			],
			"diag" => [
				"code" => $resp[0]['Diag_Code'],
				"name" => $resp[0]['Diag_Name']
			],
			"drugFinance" => [
				"code" => $WhsDocumentCostItemType_Code,
				"name" => $WhsDocumentCostItemType_Name
			],
			"whsDocumentCostItemType" => [
				"code" => $WhsDocumentCostItemType_Code,
				"name" => $WhsDocumentCostItemType_Name
			],
			"receptDiscount" => [
				"code" => $resp[0]['ReceptDiscount_Code'],
				"name" => $ReceptDiscount_Name
			]
		];

		return ['json' => json_encode($arr)];
	}

	/**
	 * Получение данных для подписания рецепта в виде JSON
	 */
	function getEvnReceptGeneralJSON($data) {
		$resp = $this->queryResult("
			select
				er.EvnReceptGeneral_id as \"EvnReceptGeneral_id\",
				kla.KLAdr_Ocatd as \"KLAdr_Ocatd\",
				l.Lpu_Name as \"Lpu_Name\",
				l.Lpu_Phone as \"Lpu_Phone\",
				l.UAddress_Address as \"UAddress_Address\",
				l.Lpu_OGRN as \"Lpu_OGRN\",
				date_part('day', er.EvnReceptGeneral_setDate) as \"EvnReceptGeneral_setDateDay\",
				date_part('month', er.EvnReceptGeneral_setDate) as \"EvnReceptGeneral_setDateMonth\",
				date_part('year', er.EvnReceptGeneral_setDate) as \"EvnReceptGeneral_setDateYear\",
				date_part('day', case
					when rv.ReceptValidType_id = 1 then dateadd('day', rv.ReceptValid_Value, er.EvnReceptGeneral_setDT)
					when rv.ReceptValidType_id = 2 then dateadd('month', rv.ReceptValid_Value, er.EvnReceptGeneral_setDT)
				end) as \"EvnReceptGeneral_disDateDay\",
				date_part('month', case
					when rv.ReceptValidType_id = 1 then dateadd('day', rv.ReceptValid_Value, er.EvnReceptGeneral_setDT)
					when rv.ReceptValidType_id = 2 then dateadd('month', rv.ReceptValid_Value, er.EvnReceptGeneral_setDT)
				end) as \"EvnReceptGeneral_disDateMonth\",
				date_part(case
					when rv.ReceptValidType_id = 1 then dateadd('day', rv.ReceptValid_Value, er.EvnReceptGeneral_setDT)
					when rv.ReceptValidType_id = 2 then dateadd('month', rv.ReceptValid_Value, er.EvnReceptGeneral_setDT)
				end) as \"EvnReceptGeneral_disDateYear\",
				er.EvnReceptGeneral_Ser as \"EvnReceptGeneral_Ser\",
				er.EvnReceptGeneral_Num as \"EvnReceptGeneral_Num\",
				ru.ReceptUrgency_Name as \"ReceptUrgency_Name\",
				ps.Person_id as \"Person_id\",
				ps.Person_SNILS as \"Person_SNILS\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				date_part('day', ps.Person_BirthDay) as \"Person_BirthDayDay\",
				date_part('month', ps.Person_BirthDay) as \"Person_BirthDayMonth\",
				date_part('year', ps.Person_BirthDay) as \"Person_BirthDayYear\",
				ps.Polis_Num as \"Polis_Num\",
				pa.Address_Address as \"PAddress_Address\",
				mp.Person_SurName as \"MedPersonal_SurName\",
				mp.Person_FirName as \"MedPersonal_FirName\",
				mp.Person_SecName as \"MedPersonal_SecName\",
				er.EvnReceptGeneral_Signa as \"EvnReceptGeneral_Signa\",
				er.EvnReceptGeneral_IsKEK as \"EvnReceptGeneral_IsKEK\",
				er.CauseVK_id as \"CauseVK_id\",
				er.PrescrSpecCause_id as \"PrescrSpecCause_id\",
				pt.PrivilegeType_Code as \"PrivilegeType_Code\",
				pt.PrivilegeType_Name as \"PrivilegeType_Name\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				case when dcm.DrugComplexMnn_id is not null then am.Actmatters_id else dm.DrugMnn_id end as \"DrugMnn_id\",
				case when dcm.DrugComplexMnn_id is not null then dmc.DrugMnnCode_Code else dm.DrugMnn_Code end as \"DrugMnn_Code\",
				case when dcm.DrugComplexMnn_id is not null then am.rusname else dm.DrugMnn_Name end as \"DrugMnn_Name\",
				case when dcm.DrugComplexMnn_id is not null then am.latname else dm.DrugMnn_NameLat end as \"DrugMnn_NameLat\",
				dt.DrugTorg_id as \"DrugTorg_id\",
				dt.DrugTorg_Code as \"DrugTorg_Code\",
				dt.DrugTorg_Name as DrugTorg_Name\"\",
				dt.DrugTorg_NameLat as \"DrugTorg_NameLat\",
				case when dcm.DrugComplexMnn_id is not null then cdf.CLSDRUGFORMS_id else dfo.DrugForm_id end as \"DrugForm_id\",
				case when dcm.DrugComplexMnn_id is not null then cdf.NAME else dfo.DrugForm_Name end as \"DrugForm_Name\",
				case when dcm.DrugComplexMnn_id is not null then dcmd.DrugComplexMnnDose_Name else dr.Drug_DoseQ end as \"Drug_DoseQ\",
				case when dcm.DrugComplexMnn_id is not null then '' else dr.Drug_DoseEi end as \"Drug_DoseEi\",
				case when dcm.DrugComplexMnn_id is not null then dp.NAME else dfv.DrugFormVip_Name end as \"DrugFormVip_Name\",
				case when dcm.DrugComplexMnn_id is not null then n.PPACKMASS else dr.Drug_Mass end as \"Drug_Mass\",
				case when dcm.DrugComplexMnn_id is not null then mu.SHORTNAME else dem.DrugEdMass_Name end as \"DrugEdMass_Name\",
				case when dcm.DrugComplexMnn_id is not null then n.PPACKVOLUME else dr.Drug_Vol end as \"Drug_Vol\",
				case when dcm.DrugComplexMnn_id is not null then cu.SHORTNAME else dev.DrugEdVol_Name end as \"DrugEdVol_Name\",
				case when dcm.DrugComplexMnn_id is not null then dcmd.DrugComplexMnnDose_Kol else dr.Drug_DoseCount end as \"Drug_DoseCount\", 
				case when dcm.DrugComplexMnn_id is not null then dcm.DrugComplexMnn_Fas else dr.Drug_Fas end as \"Drug_Fas\",
			    er.EvnReceptGeneral_IsExcessDose as \"EvnReceptGeneral_IsExcessDose\",
			    rf.ReceptForm_Name as \"ReceptForm_Name\",
			    er.EvnReceptGeneral_Period as \"EvnReceptGeneral_Period\",
			    er.EvnReceptGeneral_Validity as \"EvnReceptGeneral_Validity\"
			from
				v_EvnReceptGeneral er
				left join v_ReceptForm rf on rf.ReceptForm_id = ER.ReceptForm_id
				left join v_Lpu l on l.Lpu_id = er.Lpu_id
				left join v_Address a on a.Address_id = l.UAddress_id
				left join v_KLArea kla on kla.KLArea_id = a.KLRgn_id
				left join v_ReceptValid rv on rv.ReceptValid_id = er.ReceptValid_id
				left join v_ReceptUrgency ru on ru.ReceptUrgency_id = er.ReceptUrgency_id
				left join v_PersonState ps on ps.Person_id = er.Person_id
				left join v_Address pa on pa.Address_id = ps.PAddress_id
				left join v_MedPersonal mp on mp.MedPersonal_id = er.MedPersonal_id and mp.Lpu_id = er.Lpu_id
				left join v_PrivilegeType pt on pt.PrivilegeType_id = er.PrivilegeType_id
				left join v_Diag d on d.Diag_id = er.Diag_id
				left join v_Drug dr on dr.Drug_id = er.Drug_id
				left join v_DrugMnn dm on dm.DrugMnn_id = dr.DrugMnn_id
				left join v_DrugForm dfo on dfo.DrugForm_id = dr.DrugForm_id
				left join DrugFormVip dfv on dfv.DrugFormVip_id = dr.DrugFormVip_id
				left join v_DrugEdMass dem on dem.DrugEdMass_id = dr.DrugEdMass_id
				left join v_DrugEdVol dev on dev.DrugEdVol_id = dr.DrugEdVol_id
				left join rls.v_Drug rdr on rdr.Drug_id = er.Drug_rlsid
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(rdr.DrugComplexMnn_id, er.DrugComplexMnn_id)
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_Actmatters am on am.Actmatters_id = dcmn.Actmatters_id
				left join rls.v_DrugMnnCode dmc on dmc.Actmatters_id = dcmn.Actmatters_id
				left join rls.v_DrugComplexMnnDose dcmd on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join v_DrugTorg dt on dt.DrugTorg_id = coalesce(dr.DrugTorg_id, rdr.DrugTorg_id)
				left join rls.CLSDRUGFORMS cdf on cdf.CLSDRUGFORMS_id = dcm.CLSDRUGFORMS_id
				left join rls.Nomen n on n.NOMEN_ID = rdr.Drug_id
				left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.DRUGPACK dp on dp.DRUGPACK_id = n.PPACKID
			where
				er.EvnReceptGeneral_id = :EvnReceptGeneral_id
		", [
			'EvnReceptGeneral_id' => $data['EvnReceptGeneral_id']
		]);

		if (empty($resp[0]['EvnReceptGeneral_id'])) {
			return ['Error_Msg' => 'Ошибка получения данных по рецепту'];
		}

		$arr = [
			"okato" => $resp[0]['KLAdr_Ocatd'],
			"lpu" => [
				"name" => $resp[0]['Lpu_Name'],
				"phone" => $resp[0]['Lpu_Phone'],
				"adress" => $resp[0]['UAddress_Address'],
				"orgn" => $resp[0]['Lpu_OGRN'],
			],
			"begDate" => [
				"day" => $resp[0]['EvnReceptGeneral_setDateDay'],
				"month" => $resp[0]['EvnReceptGeneral_setDateMonth'],
				"year" => $resp[0]['EvnReceptGeneral_setDateYear']
			],
			"endDate" => [
				"day" => $resp[0]['EvnReceptGeneral_disDateDay'],
				"month" => $resp[0]['EvnReceptGeneral_disDateMonth'],
				"year" => $resp[0]['EvnReceptGeneral_disDateYear']
			],
			"receptform" => $resp[0]['ReceptForm_Name'],
			"series" => $resp[0]['EvnReceptGeneral_Ser'],
			"number" => $resp[0]['EvnReceptGeneral_Num'],
			"urgency" => $resp[0]['ReceptUrgency_Name'],
			"person" => [
				"id" => $resp[0]['Person_id'],
				"snils" => $resp[0]['Person_SNILS'],
				"surname " => $resp[0]['Person_SurName'],
				"firname" => $resp[0]['Person_FirName'],
				"secname " => $resp[0]['Person_SecName'],
				"birthday" => [
					"day" => $resp[0]['Person_BirthDayDay'],
					"month" => $resp[0]['Person_BirthDayMonth'],
					"year" => $resp[0]['Person_BirthDayYear']
				],
				"polis" => $resp[0]['Polis_Num'],
				"adress" => $resp[0]['PAddress_Address']
			],
			"medPersonal " => [
				"fio " => $resp[0]['MedPersonal_SurName'] . ' ' . $resp[0]['MedPersonal_FirName'] . ' ' . $resp[0]['MedPersonal_SecName']
			],
			"drug" => [
				"drugMnn" => [
					"id" => $resp[0]['DrugMnn_id'],
					"code" => $resp[0]['DrugMnn_Code'],
					"name" => $resp[0]['DrugMnn_Name'],
					"latname" => $resp[0]['DrugMnn_NameLat']
				],
				"drugTorg" => [
					"id" => $resp[0]['DrugTorg_id'],
					"code" => $resp[0]['DrugTorg_Code'],
					"name" => $resp[0]['DrugTorg_Name'],
					"latname" => $resp[0]['DrugTorg_NameLat']
				],
				"drugForm" => [
					"id" => $resp[0]['DrugForm_id'],
					"name" => $resp[0]['DrugForm_Name'],
				],
				"drugDoseQ" => $resp[0]['Drug_DoseQ'] . ($resp[0]['EvnReceptGeneral_IsExcessDose'] == 2 ? '!' : ''),
				"drugDoseQEi" => $resp[0]['Drug_DoseEi'],
				"drugPackName" => $resp[0]['DrugFormVip_Name'],
				"drugPackQMass" => $resp[0]['Drug_Mass'],
				"drugPackQMassEi" => $resp[0]['DrugEdMass_Name'],
				"drugPackQVol" => $resp[0]['Drug_Vol'],
				"drugPackQVolEi" => $resp[0]['DrugEdVol_Name'],
				"drugDoseCount" => $resp[0]['Drug_DoseCount'],
				"drugFas" => $resp[0]['Drug_Fas']
			],
			"signa" => $resp[0]['EvnReceptGeneral_Signa'],
			"isVk" => $resp[0]['EvnReceptGeneral_IsKEK'] == 2,
			"isVkSpec" => $resp[0]['CauseVK_id'] == 1,
			"isKol" => $resp[0]['PrescrSpecCause_id'] == 1,
			"isCourse" => in_array($resp[0]['PrescrSpecCause_id'], [2,3]),
			'CoursePeriodic' => $resp[0]['EvnReceptGeneral_Period'],
			'CourseDescr' => $resp[0]['EvnReceptGeneral_Validity'],
			"diag" => [
				"code" => $resp[0]['Diag_Code'],
				"name" => $resp[0]['Diag_Name']
			]
		];

		return ['json' => json_encode($arr)];
	}
}
