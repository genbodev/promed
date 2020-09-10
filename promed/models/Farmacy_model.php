<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс модели для общих операций используемых во всех модулях
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage@swan.perm.ru)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

class Farmacy_model extends swModel {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 * Удаление строки учетного документа
	 */
	function checkEvnReceptProcessAbilty($data) {
		$query = "
			select count(ER.EvnRecept_id) as Drug_Count
			from EvnRecept ER with(nolock)
				inner join v_DrugFed DF with(nolock) on DF.Drug_id = ER.Drug_id
				inner join v_Drug7Noz D7N with(nolock) on D7N.Drug_id = ER.Drug_id
			where ER.EvnRecept_id = :EvnRecept_id
		";
		$result = $this->db->query($query, array('EvnRecept_id' => $data['EvnRecept_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление строки учетного документа)'));
		}
	}

	/**
	 * Удаление учетного документа
	 */
	function deleteDocumentUc($data) {
		$errors = array();
		
		//проверка на наличие дочерних элементов
		$query = " 
			select (
				(
					select
						count(DocumentUc_id)
					from
						DocumentUc with(nolock)
					where
						DocumentUc_pid = :DocumentUc_id
				) + (
					select
						count(dus1.DocumentUcStr_id)
					from
						DocumentUcStr dus1 with(nolock)
					where dus1.DocumentUcStr_oid in (
						select
							dus2.DocumentUcStr_id
						from
							DocumentUcStr dus2 with(nolock)
						where
							dus2.DocumentUc_id = :DocumentUc_id
					)
				)
			) as cnt
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ($res[0]['cnt'] > 0)
				$errors[] = 'Удаление данного документа невозможно. Существует связь с другими документами.';
		} else {
			$errors[] = 'Ошибка при выполнении запроса к базе данных (удаление учетного документа)';
		}

		//$errors[] = 'qew';
		if (count($errors) == 0) {
			$query = "
				delete from
					DocumentUcStr
				where
					DocumentUc_id = :DocumentUc_id;				
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_DocumentUc_del
					@DocumentUc_id = :DocumentUc_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				$errors[] = 'Ошибка при выполнении запроса к базе данных (удаление учетного документа)';
			}
		}
			
		if (count($errors) > 0) {
			return array('Error_Msg' => $errors[0]);
		}		
	}
	
	/**
	 * Удаление строки учетного документа
	 */
	function deleteDocumentUcStr($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_DocumentUcStr_del
				@DocumentUcStr_id = :DocumentUcStr_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'DocumentUcStr_id' => $data['DocumentUcStr_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление строки учетного документа)');
		}
	}


	/**
	 * Поиск рецепта для отоваривания или постановки на отсрочку
	 */
	function searchEvnRecept($data) {
		$query = "
			select
				EvnRecept_id
			from v_EvnRecept with (nolock)
			where (1 = 1)
				and EvnRecept_Num = :EvnRecept_Num
				and EvnRecept_Ser = :EvnRecept_Ser
				and (ReceptDelayType_id is null or ReceptDelayType_id in (1, 2))
		";

		$queryParams = array(
			'EvnRecept_Num' => $data['EvnRecept_Num'],
			'EvnRecept_Ser' => $data['EvnRecept_Ser']
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
	 * Загрузка информации о найденном рецепте
	 */
	function loadEvnReceptData($data) {
		$query = "
			SELECT
				ER.EvnRecept_Num,
				ER.EvnRecept_Ser,
				convert(varchar(10), ER.EvnRecept_setDate, 104) as EvnRecept_setDate,
				convert(varchar(10), DATEADD(DAY, (case when ER.ReceptValid_id = 1 then 14 else 30 end), ER.EvnRecept_setDate), 104) as EvnRecept_expDate,
				PT.PrivilegeType_Code,
				RTRIM(PT.PrivilegeType_Name) as PrivilegeType_Name,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,
				RTRIM(MP.Person_FIO) as MedPersonal_Fio,
				RTRIM(ISNULL(PS.Person_SurName, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_FirName, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_SecName, '')) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				ISNULL(RTRIM(PS.Person_Snils), '-') as Person_Snils,
				ISNULL(RTRIM(Sex.Sex_Name), '-') as Sex_Name,
				ISNULL(Drug.Drug_id, 0) as Drug_id,
				ISNULL(DrugMnn.DrugMnn_id, 0) as DrugMnn_id,
				RlsDrug.Drug_id as Drug_rlsid,
				DrugComplexMnn.DrugComplexMnn_id as DrugComplexMnn_id,
				ISNULL(DrugTorg.DrugTorg_id, 0) as DrugTorg_id,
				COALESCE(RlsDrug.Drug_Code, Drug.Drug_Code, '-') as Drug_Code,
				COALESCE(RlsDrug.Drug_Name, Drug.Drug_Name, '-') as Drug_Name,
				COALESCE(DrugComplexMnn.DrugComplexMnn_RusName, DrugMnn.DrugMnn_Name, '-') as DrugMnn_Name,
				COALESCE(DrugComplexMnn.DrugComplexMnn_LatName, DrugMnn.DrugMnn_NameLat, '-') as DrugMnn_NameLat,
				COALESCE(RlsDrug.DrugTorg_Name, DrugTorg.DrugTorg_Name, '-') as DrugTorg_Name,
				ISNULL(RTRIM(DrugTorg.DrugTorg_NameLat), '-') as DrugTorg_NameLat,
				RD.ReceptDiscount_Code,
				RF.ReceptFinance_Code,
				RF.ReceptFinance_Name,
				DrugIsMnn.YesNo_Code as Drug_IsMnn_Code,
				ISNULL(DrugIsKek.YesNo_Name, '') as Drug_IsKEK_Name,
				cast(ER.EvnRecept_Kolvo as float) as EvnRecept_Kolvo,
				isnull(RDT.ReceptDelayType_id, 0) as ReceptDelayType_id,
				isnull(RDT.ReceptDelayType_Name, 'Выписан') as ReceptDelayType_Name,
				isnull(ER.OrgFarmacy_oid, 0) as OrgFarmacy_oid,
				isnull(OrgFarmacy.OrgFarmacy_Name, '-') as OrgFarmacy_Name
			FROM v_EvnRecept ER with (nolock)
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ER.Lpu_id
				outer apply (
					select top 1 Person_FIO
					from v_MedPersonal with (nolock)
					where MedPersonal_id = ER.MedPersonal_id
						and Lpu_id = ER.Lpu_id
				) MP
				left join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = ER.PrivilegeType_id
				left join v_ReceptDiscount RD with (nolock) on RD.ReceptDiscount_id = ER.ReceptDiscount_id
				left join v_ReceptFinance RF with (nolock) on RF.ReceptFinance_id = ER.ReceptFinance_id
				left join v_Person_All PS with (nolock) on PS.Server_id = ER.Server_id
					and PS.PersonEvn_id = ER.PersonEvn_id
				left join v_YesNo DrugIsMnn with (nolock) on DrugIsMnn.YesNo_id = ISNULL(ER.EvnRecept_IsMnn, 1)
				left join v_YesNo DrugIsKek with (nolock) on DrugIsKek.YesNo_id = ISNULL(ER.EvnRecept_IsKek, 1)
				left join v_Drug Drug with (nolock) on Drug.Drug_id = ER.Drug_id
				left join rls.v_Drug RlsDrug with (nolock) on RlsDrug.Drug_id = ER.Drug_rlsid
				left join v_DrugMnn DrugMnn with (nolock) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
				left join rls.v_DrugComplexMnn DrugComplexMnn with (nolock) on DrugComplexMnn.DrugComplexMnn_id = RlsDrug.DrugComplexMnn_id
				left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join v_DrugTorg DrugTorg with (nolock) on DrugTorg.DrugTorg_id = Drug.DrugTorg_id
					and DrugIsMnn.YesNo_Code = 0
				left join v_ReceptDelayType RDT with (nolock) on RDT.ReceptDelayType_id = ER.ReceptDelayType_id
				left join v_OrgFarmacy OrgFarmacy with (nolock) on OrgFarmacy.OrgFarmacy_id = ER.OrgFarmacy_oid
			WHERE ER.EvnRecept_id = :EvnRecept_id
				and (RDT.ReceptDelayType_id is null or RDT.ReceptDelayType_id in (1, 2))
				and ER.ReceptValid_id is not null
		";
		$queryParams = array(
			'EvnRecept_id' => $data['EvnRecept_id']
		);
		
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			//var_dump($result->result('array'));
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Функция
	 */
	function loadDocumentUcView($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}

		$params = array();
		$filter = "(1=1)";
		$ssl_filter = ""; //фильтры по структуре
		$joinUcStr = "";
		$joinDrug = "";
		$joinUser = "";
		$joinCourse = "";
		$joinPatient = "";
		
		// Выбираем только документы для этой аптеки/контрагента
		if ((isset($data['Contragent_id'])) && ($data['Contragent_id']>0))
		{
			$filter = $filter." and (
				DocUc.Contragent_id = :Contragent_id
				or (DocUc.Contragent_id is null and DocUc.Lpu_id = :Lpu_id)
				or DocUc.Contragent_id in (select Contragent_id from v_Contragent with(nolock) where Lpu_id = :Lpu_id)
			)";
			$params['Contragent_id'] = $data['Contragent_id'];
            $params['Lpu_id'] = $data['Lpu_id'];
		}
		else 
		{
			$filter = $filter." and DocUc.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		/*
		// И/или берем только один какой-то документ
		if ((isset($data['DocumentUc_id'])) && ($data['DocumentUc_id']>0))
		{
			$filter = $filter." and DocUc.DocumentUc_id = :DocumentUc_id";
			$params['DocumentUc_id'] = $data['DocumentUc_id'];
		}
		*/

		if (isset($data['session']) && !empty($data['filterByOrgUser']) && $data['filterByOrgUser']) {
			$OrgStructList = $this->queryResult("
				select PW.OrgStruct_id
				from v_pmUserCacheOrg PUO with(nolock)
				inner join v_PersonWork PW with(nolock) on PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
				where PUO.pmUserCache_id = :pmUser_id and PUO.Org_id = :Org_id
			", array(
				'pmUser_id' => $data['session']['pmuser_id'],
				'Org_id' => $data['session']['org_id'],
			), true);
			if (!is_array($OrgStructList)) return false;
			$get_ids = function($item){return !empty($item['OrgStruct_id'])?$item['OrgStruct_id']:0;};
			$ids_str = implode(",", array_map($get_ids, $OrgStructList));

			$ssl_filter .= " and (
				(sssl.Org_id = :Org_id_ses and isnull(sssl.OrgStruct_id,0) in ({$ids_str})) or
				(tssl.Org_id = :Org_id_ses and isnull(tssl.OrgStruct_id,0) in ({$ids_str}))
			)";
			$params['Org_id_ses'] = $data['session']['org_id'];
		}
		
		// Кроме того, выбираем документы только определенного типа 
		if ((isset($data['DrugDocumentType_id'])) && ($data['DrugDocumentType_id']>0))
		{
			$filter = $filter." and DocUc.DrugDocumentType_id = :DrugDocumentType_id";
			$params['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
		} else {
            $filter = $filter." and DDT.DrugDocumentType_Code <> '35'";
        }
		
		// Фильтры
		if ((isset($data['Contragent_tid'])) && ($data['Contragent_tid']>0)) {
			$filter .= " and DocUc.Contragent_tid = :Contragent_tid";
			$params['Contragent_tid'] = $data['Contragent_tid'];
		}
		if ((isset($data['Contragent_sid'])) && ($data['Contragent_sid']>0)) {
			$filter .= " and DocUc.Contragent_sid = :Contragent_sid";
			$params['Contragent_sid'] = $data['Contragent_sid'];
		}
		if ((isset($data['Storage_tid'])) && ($data['Storage_tid']>0)) {
			$filter .= " and DocUc.Storage_tid = :Storage_tid";
			$params['Storage_tid'] = $data['Storage_tid'];
		}
		if ((isset($data['Storage_sid'])) && ($data['Storage_sid']>0)) {
			$filter .= " and DocUc.Storage_sid = :Storage_sid";
			$params['Storage_sid'] = $data['Storage_sid'];
		}
		if ((isset($data['Mol_tid'])) && ($data['Mol_tid']>0)) {
			$filter .= " and DocUc.Mol_tid = :Mol_tid";
			$params['Mol_tid'] = $data['Mol_tid'];
		}
		if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0)) {
			$ssl_filter .= " and (sssl.LpuSection_id = :LpuSection_id or tssl.LpuSection_id = :LpuSection_id)";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if ((isset($data['LpuBuilding_id'])) && ($data['LpuBuilding_id']>0)) {
			$ssl_filter .= " and (sssl.LpuBuilding_id = :LpuBuilding_id or tssl.LpuBuilding_id = :LpuBuilding_id)";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if ((isset($data['Storage_id'])) && ($data['Storage_id']>0)) {
			$filter .= " and (DocUc.Storage_tid = :Storage_id or DocUc.Storage_sid = :Storage_id)";
			$params['Storage_id'] = $data['Storage_id'];
		}
		if ((isset($data['DocumentUc_Num'])) && !empty($data['DocumentUc_Num'])) {
			$filter .= " and DocUc.DocumentUc_Num like :DocumentUc_Num";
			$params['DocumentUc_Num'] = $data['DocumentUc_Num'].'%';
		}
		if (isset($data['DocumentUc_setDate']) && !empty($data['DocumentUc_setDate'])) {
			$filter .= " and DocUc.DocumentUc_setDate = :DocumentUc_setDate";
			$params['DocumentUc_setDate'] = $data['DocumentUc_setDate'];
		}
		if (!empty($data['DocumentUc_setDate_range'][0]) && !empty($data['DocumentUc_setDate_range'][1])) {
			$filter .= " and DocUc.DocumentUc_setDate between :begDate and :endDate";
			$params['begDate'] = $data['DocumentUc_setDate_range'][0];
			$params['endDate'] = $data['DocumentUc_setDate_range'][1];
		}
		if (!empty($data['DocumentUc_date_range'][0]) && !empty($data['DocumentUc_date_range'][1])) {
			$filter .= " and convert(varchar(10), cast(DocUc.DocumentUc_updDT as datetime),120) between :updbegDate and :updendDate";
			$params['updbegDate'] = $data['DocumentUc_date_range'][0];
			$params['updendDate'] = $data['DocumentUc_date_range'][1];
		} else if (!empty($data['DocumentUc_date_range'][0])){
			$filter .= " and convert(varchar(10), cast(DocUc.DocumentUc_updDT as datetime),120) >= :updbegDate";
			$params['updbegDate'] = $data['DocumentUc_date_range'][0];
		} else if (!empty($data['DocumentUc_date_range'][1])){
			$filter .= " and convert(varchar(10), cast(DocUc.DocumentUc_updDT as datetime),120) <= :updendDate";
			$params['updendDate'] = $data['DocumentUc_date_range'][1];
		}
		if (isset($data['begDate']) && !empty($data['begDate']) && isset($data['endDate']) && !empty($data['endDate'])) {
			$filter .= " and DocUc.DocumentUc_setDate between :begDate and :endDate";
			$params['begDate'] = $data['begDate'];
			$params['endDate'] = $data['endDate'];
		}
		if ((isset($data['DrugFinance_id'])) && !empty($data['DrugFinance_id'])) {
			$filter .= " and DocUc.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if ((isset($data['WhsDocumentCostItemType_id'])) && !empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and DocUc.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		if (!empty($data['DrugDocumentClass_id'])) {
			$filter .= " and DocUc.DrugDocumentClass_id = :DrugDocumentClass_id";
			$params['DrugDocumentClass_id'] = $data['DrugDocumentClass_id'];
		}
		if (!empty($data['DrugDocumentStatus_id'])) {
			$filter .= " and DocUc.DrugDocumentStatus_id = :DrugDocumentStatus_id";
			$params['DrugDocumentStatus_id'] = $data['DrugDocumentStatus_id'];
		}
		if ((isset($data['WhsDocumentUc_Num'])) && !empty($data['WhsDocumentUc_Num'])) {
			$filter .= " and WDU.WhsDocumentUc_Num = :WhsDocumentUc_Num";
			$params['WhsDocumentUc_Num'] = $data['WhsDocumentUc_Num'];
		}
		if ((isset($data['Org_id'])) && !empty($data['Org_id'])) {
			$ssl_filter .= " and (
			    (
			        DocUc.Org_id = :Org_id and
			        not exists (select Lpu_id from v_Lpu where Org_id = :Org_id)
			    ) or (
			        sssl.Org_id = :Org_id or
			        sssl.Lpu_id in (select Lpu_id from v_Lpu with (nolock) where Org_id = :Org_id)
			    ) or (
			        tssl.Org_id = :Org_id or
			        tssl.Lpu_id in (select Lpu_id from v_Lpu with (nolock) where Org_id = :Org_id)
			    )
			)";
			$params['Org_id'] = $data['Org_id'];
		}
		if ((isset($data['Mol_sid'])) && !empty($data['Mol_sid'])) {
			$filter .= " and DocUc.Mol_sid = :Mol_sid";
			$params['Mol_sid'] = $data['Mol_sid'];
		}
		if ((isset($data['Mol_tid'])) && !empty($data['Mol_tid'])) {
			$filter .= " and DocUc.Mol_tid = :Mol_tid";
			$params['Mol_tid'] = $data['Mol_tid'];
		}
		if ((isset($data['DocumentUcStr_Reason'])) && !empty($data['DocumentUcStr_Reason'])) {
			$joinUcStr = "left join v_DocumentUcStr DocUcStr with (nolock) on DocUcStr.DocumentUc_id = DocUc.DocumentUc_id";
			$filter .= " and DocUcStr.DocumentUcStr_Reason LIKE :DocumentUcStr_Reason";
			$params['DocumentUcStr_Reason'] = '%'.$data['DocumentUcStr_Reason'].'%';
		}
		if (((isset($data['DrugMnn_Name'])) && !empty($data['DrugMnn_Name'])) || ((isset($data['DrugTorg_Name'])) && !empty($data['DrugTorg_Name']))) {
			if(empty($joinUcStr))
				$joinUcStr = "left join v_DocumentUcStr DocUcStr with (nolock) on DocUcStr.DocumentUc_id = DocUc.DocumentUc_id";
			$joinDrug = "left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = DocUcStr.Drug_id";
			if((isset($data['DrugMnn_Name'])) && !empty($data['DrugMnn_Name'])){
				//$joinDrug .= " left join v_DrugMnn DrugMnn with (nolock) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id";
				//$filter .= " and DrugMnn.DrugMnn_Name LIKE :DrugMnn_Name";
				$filter .= " and Drug.Drug_Name LIKE :DrugMnn_Name";
				$params['DrugMnn_Name'] = '%'.$data['DrugMnn_Name'].'%';
			}
			if((isset($data['DrugTorg_Name'])) && !empty($data['DrugTorg_Name'])){
				//$joinDrug .= " left join v_DrugTorg DrugTorg with (nolock) on DrugTorg.DrugTorg_id = Drug.DrugTorg_id";
				//$filter .= " and DrugTorg.DrugTorg_Name LIKE :DrugTorg_Name";
				$filter .= " and Drug.Drug_Name LIKE :DrugTorg_Name";
				$params['DrugTorg_Name'] = '%'.$data['DrugTorg_Name'].'%';
			}
			
		}
		if ((isset($data['pmUser'])) && !empty($data['pmUser'])) {
			$joinUser = "left join v_pmUser pUsi with (nolock) on pUsi.PMUser_id = DocUc.pmUser_insID";
			$joinUser .= " left join v_pmUser pUsu with (nolock) on pUsu.PMUser_id = DocUc.pmUser_updID";
			$filter .= " and pUsi.PMUser_Name LIKE :pmUser and pUsu.PMUser_Name LIKE :pmUser";
			$params['pmUser'] = '%'.$data['pmUser'].'%';
		}
		if ((isset($data['Postms'])) && !empty($data['Postms'])) {
			if(empty($joinUcStr))
				$joinUcStr = "left join v_DocumentUcStr DocUcStr with (nolock) on DocUcStr.DocumentUc_id = DocUc.DocumentUc_id";
			$joinCourse = "left join v_EvnDrug EvnDR with (nolock) on EvnDR.EvnDrug_id = DocUcStr.EvnDrug_id";
			$joinCourse .= " left join v_EvnPrescrTreatDrug ECTD with (nolock) on ECTD.EvnPrescrTreatDrug_id = EvnDR.EvnPrescrTreatDrug_id";
			$joinCourse .= " left join v_EvnPrescrTreat ECT with (nolock) on ECT.EvnPrescrTreat_id = ECTD.EvnPrescrTreat_id";
			$joinCourse .= " left join v_pmUserCache MP with (nolock) on MP.PMUser_id = ECT.pmUser_updID";
			//$joinCourse .= " left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ECT.MedPersonal_id";
			$filter .= " and MP.PMUser_Name LIKE :Postms";
			$params['Postms'] = '%'.$data['Postms'].'%';
		}
		if ((isset($data['Patient'])) && !empty($data['Patient'])) {
			if(empty($joinUcStr))
				$joinUcStr = "left join v_DocumentUcStr DocUcStr with (nolock) on DocUcStr.DocumentUc_id = DocUc.DocumentUc_id";
			$joinPatient = " outer apply (
								select top 1 
								LTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio
								from v_Person_fio PS with (nolock)
								where PS.Person_id = DocUcStr.Person_id
							) PSfio
							left join ReceptOtov RO with (nolock) on RO.ReceptOtov_id = DocUcStr.ReceptOtov_id
							left join v_Person_all RO_pers with (nolock) on RO_pers.Person_id = RO.Person_id";
			$filter .= " and (PSfio.Person_Fio LIKE :Patient OR RO_pers.Person_Fio LIKE :Patient)";
			$params['Patient'] = '%'.$data['Patient'].'%';
		}
		if (!empty($data['MedService_Storage_id'])) {
			//получение списка дочерних складов склада службы
			$query = "
				with storage_tree (Storage_id, Storage_pid) as (
					select
						s.Storage_id,
						s.Storage_pid
					from
						v_Storage s with (nolock)
					where
						s.Storage_pid = :Storage_id
					union all
					select
						s.Storage_id,
						s.Storage_pid
					from
						v_Storage s with (nolock)
						inner join storage_tree tr on s.Storage_pid = tr.Storage_id
				)
				select
					Storage_id
				from
					storage_tree
			";
			$storage_list = $this->queryList($query, array(
				'Storage_id' => $data['MedService_Storage_id']
			));
			if (is_array($storage_list) && count($storage_list) > 0) {
				$storage_list_str = join(',', $storage_list);
				if (!empty($ssl_filter)) {
					//убираем " and " в начале
					$ssl_filter = substr($ssl_filter, 5);

					$ssl_filter = " and (
						DocUc.Storage_sid in ({$storage_list_str}) or
						DocUc.Storage_tid in ({$storage_list_str}) or
						(
							{$ssl_filter}
						)
					)";
				} else {
					$ssl_filter = " and (
						DocUc.Storage_sid in ({$storage_list_str}) or
						DocUc.Storage_tid in ({$storage_list_str})
					)";
				}
			}
		}
		
		if (!empty($data['Org_sINN'])) {
			$filter .= " and sOrg.Org_INN = :Org_sINN";
			$params['Org_sINN'] = $data['Org_sINN'];
		}

		if (!empty($data['Org_tINN'])) {
			$filter .= " and tOrg.Org_INN = :Org_tINN";
			$params['Org_tINN'] = $data['Org_tINN'];
		}

		$fields1 = "";
		$fields4 = "";
		$fields9 = "";

		Switch ($data['DrugDocumentType_id'])
		{
			case 1:
				$fields1 = "RTrim(DocUc.DocumentUc_DogNum) as DocumentUc_DogNum, convert(varchar(10), DocUc.DocumentUc_DogDate, 104) as DocumentUc_DogDate,";
				break;
			case 4:
				$fields4 = "RTrim(DocUc.DocumentUc_InvNum) as DocumentUc_InvNum, convert(varchar(10), DocUc.DocumentUc_InvDate, 104) as DocumentUc_InvDate,";
				break;
			case 9:
				$fields9 = "convert(varchar(10), DocUc.DocumentUc_planDT, 104) as DocumentUc_planDate,";
				break;
			default: 
				$order = "";
				break;
		}
		
		// Выбираем DrugFinance_id - все документы отображаются только в "своих" отделах 
		if ((isset($data['FarmacyOtdel_id'])) && ($data['FarmacyOtdel_id']>0))
		{
			$filter = $filter." and (DocUc.DrugFinance_id = :DrugFinance_id or DocUc.DrugFinance_id is null)";
			$params['DrugFinance_id'] = $data['FarmacyOtdel_id'];
		}

		$query = "
			Select 
				-- select
				DocUc.DocumentUc_id,
				{$fields1}
				{$fields4}
				{$fields9}
				DocUc.DrugDocumentStatus_id,
				DDS.DrugDocumentStatus_Code,
				RTrim(case
					when DrugDocumentStatus_Name is null then 'Нет статуса'
					else DrugDocumentStatus_Name
				end) as DrugDocumentStatus_Name,
				DocUc.DrugDocumentType_id,
				RTrim(DrugDocumentType_Code) as DrugDocumentType_Code,
				RTrim(DrugDocumentType_Name) as DrugDocumentType_Name,
				RTrim(DocUc.DocumentUc_Num) as DocumentUc_Num,
				convert(varchar(10), DocUc.DocumentUc_setDate, 104) as DocumentUc_setDate,
				DocUc.DocumentUc_didDate,
				convert(varchar(10), DocUc.DocumentUc_didDate, 104) as DocumentUc_txtdidDate, -- постраничный вывод не понимает alias-ов
				DocUc.DrugFinance_id,
				DocUc.Contragent_tid,
				DocUc.Mol_tid,
				DocUc.Storage_tid,
				RTrim(case
					when DrugDocumentType_Code = 21 then 'Пациент'
					else T.Contragent_Name
				end) as Contragent_tName,
				TOrg.Org_INN as Org_tINN,
				RTrim(tStorage.Storage_Name) as Storage_tName,
				DocUc.Contragent_sid,
				DocUc.Mol_sid,
				DocUc.Storage_sid,
				RTrim(S.Contragent_Name) as Contragent_sName,
				SOrg.Org_INN as Org_sINN,
				RTrim(sStorage.Storage_Name) as Storage_sName,
				DocumentUc_Sum,
				(
					select
						sum(
							(case
								when
									isnull(isnds.YesNo_Code, 0) = 1
								then
									isnull(dus.DocumentUcStr_Sum, 0)
								else
									isnull(dus.DocumentUcStr_Sum, 0)+isnull(dus.DocumentUcStr_SumNds, 0)
							end)
						)
					from
						v_DocumentUcStr dus with (nolock)
						left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
						left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
					where
						dus.DocumentUc_id = DocUc.DocumentUc_id
				) as DocumentUcStr_NdsSum,
				DocumentUc_SumR,
				DF.DrugFinance_Name,
				WDCIT.WhsDocumentCostItemType_Name,
				DocUc.WhsDocumentCostItemType_id,
				DocUc.WhsDocumentUc_id,
				(
				    case
				        when e_doknak.DrugDocumentStatus_Code is not null and e_doknak.DrugDocumentStatus_Code <> 4 then 'В пути'
				        when e_doknak.DrugDocumentStatus_Code = 4 then 'Поставлено '+e_doknak.Update_Date
				        else ''
				    end
				) as Supply_State,
				s_work.StorageWork_State
				-- end select
			from 
				-- from
				v_DocumentUc DocUc with (nolock)
				left join Contragent T with (nolock) on T.Contragent_id = DocUc.Contragent_tid --потребитель
				left join Org TOrg  with (nolock) on TOrg.Org_id = T.Org_id
				left join Contragent S with (nolock) on S.Contragent_id = DocUc.Contragent_sid --поставщик
				left join Org SOrg  with (nolock) on SOrg.Org_id = S.Org_id
				left join v_DrugFinance DF with (nolock) on DF.DrugFinance_id = DocUc.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT with (nolock) on WDCIT.WhsDocumentCostItemType_id = DocUc.WhsDocumentCostItemType_id
				left join v_DrugDocumentStatus DDS with (nolock) on DDS.DrugDocumentStatus_id = DocUc.DrugDocumentStatus_id
				left join v_DrugDocumentType DDT with (nolock) on DDT.DrugDocumentType_id = DocUc.DrugDocumentType_id
				left join v_WhsDocumentUc WDU with (nolock) on WDU.WhsDocumentUc_id = DocUc.WhsDocumentUc_id
				left join v_Storage tStorage with (nolock) on tStorage.Storage_id = DocUc.Storage_tid
				left join v_Storage sStorage with (nolock) on sStorage.Storage_id = DocUc.Storage_sid
				left join v_StorageStructLevel sssl with (nolock) on sssl.Storage_id = DocUc.Storage_sid
				left join v_StorageStructLevel tssl with (nolock) on tssl.Storage_id = DocUc.Storage_tid
				outer apply (
                    select top 1
                        i_dds.DrugDocumentStatus_Code,
                        convert(varchar(10), i_du.DocumentUc_updDT, 104) as Update_Date
                    from
                        v_DocumentUc i_du with (nolock)
                        left join v_DrugDocumentStatus i_dds with (nolock) on i_dds.DrugDocumentStatus_id = i_du.DrugDocumentStatus_id
				        left join v_DrugDocumentType i_ddt with (nolock) on i_ddt.DrugDocumentType_id = i_du.DrugDocumentType_id
                    where
                        DDT.DrugDocumentType_Code = 10 and -- расходная наклкдная
                        i_du.DocumentUc_pid = DocUc.DocumentUc_id and
                        i_ddt.DrugDocumentType_id = 6 -- приходная накладная
                ) e_doknak
                outer apply (
                    select top 1
                        (
                            isnull(i_dutw.DocumentUcTypeWork_Name, '')+
                            isnull(' '+i_ps.Person_SurName, '')+
                            isnull(' '+i_ps.Person_FirName, '')+
                            isnull(' '+i_ps.Person_SecName, '')
                        ) as StorageWork_State
                    from
                        v_DocumentUcStr i_dus with (nolock)
                        inner join v_DocumentUcStorageWork i_dusw with (nolock) on i_dusw.DocumentUcStr_id = i_dus.DocumentUcStr_id
                        left join v_DocumentUcTypeWork i_dutw with (nolock) on i_dutw.DocumentUcTypeWork_id = i_dusw.DocumentUcTypeWork_id
                        left join v_PersonState i_ps with (nolock) on i_ps.Person_id = i_dusw.Person_eid
                    where
                        i_dus.DocumentUc_id = DocUc.DocumentUc_id and
                        i_dusw.DocumentUcStorageWork_endDate is null
                    order by
                        i_dusw.DocumentUcStorageWork_id
                ) s_work
				{$joinUcStr}
				{$joinDrug}
				{$joinUser}
				{$joinCourse}
				{$joinPatient}
				-- end from
			where
				-- where
				{$filter}
				{$ssl_filter}
				-- end where
			order by 
				-- order by
				DocumentUc_didDate desc
				-- end order by
		";
		//echo getDebugSql(getLimitSQLPH($query, 0, 100), $params);exit;
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		}

	/**
	 *  Функция
	 */
	function DrugProducerAdd($data)
	{
		// Добавление производителя
		$DrugProducer_id = $data['DrugProducer_id'];
		if ((isset($data['DrugProducer_New']) && !empty($data['DrugProducer_New'])))
		{
			$sql = "
				select
					DrugProducer_id
				from
					v_DrugProducer with(nolock)
				where
					DrugProducer_Name = :DrugProducer_New --and Server_id=:Server_id
				";
			$result = $this->db->query($sql, array(
						'DrugProducer_New' => $data['DrugProducer_New'],
						'Server_id' => $data['Server_id']
					));
			if (is_object($result)) 
			{
				if (isset($sel[0])) 
				{
					$sel = $result->result('array');
					if ( $sel[0]['DrugProducer_id'] > 0 )
						$DrugProducer_id = $sel[0]['DrugProducer_id'];
				}
				else 
				{
					$sql = "
						declare @DrugProducer_id bigint
						exec p_DrugProducer_ins
							@DrugProducer_Name = :DrugProducer_Name,
							@DrugProducer_Code = :DrugProducer_Code,
							@DrugProducer_Country = :DrugProducer_Country,
							@pmUser_id = :pmUser_id,
							@Server_id = :Server_id,
							@DrugProducer_id=@DrugProducer_id output
						select @DrugProducer_id as DrugProducer_id;
					";
					$result = $this->db->query($sql, array(
						'DrugProducer_Name' => $data['DrugProducer_New'],
						'Server_id' => $data['Server_id'],
						'DrugProducer_Code' => null,
						'DrugProducer_Country' => null,
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_object($result)) 
					{
						$sel = $result->result('array');
						if ( $sel[0]['DrugProducer_id'] > 0 )
							$DrugProducer_id = $sel[0]['DrugProducer_id'];
					}
				}
			}
		}
		return $DrugProducer_id;
	}

	/**
	 *  Функция для получения идентификатора серии по самой серии. Если серии еще нет в справочнике, она добавляется туда.
	 */
	function PrepSeriesAdd($data) {
		$PrepSeries_id = isset($data['PrepSeries_id']) ? $data['PrepSeries_id'] : null;

		if ((isset($data['PrepSeries_Ser']) && !empty($data['PrepSeries_Ser']))) {
			$sql = "
				select top 1
					ps.PrepSeries_id
				from
					rls.v_PrepSeries ps with(nolock)
					left join rls.v_Drug d with(nolock) on d.DrugPrep_id = ps.Prep_id
				where
					d.Drug_id = :Drug_id and
					ps.PrepSeries_Ser = :PrepSeries_Ser;
			";
			$result = $this->db->query($sql, array(
				'Drug_id' => $data['Drug_id'],
				'PrepSeries_Ser' => $data['PrepSeries_Ser']
			));

			if (is_object($result)) {
				$sel = $result->result('array');
				if (isset($sel[0]) && $sel[0]['PrepSeries_id'] > 0) {
					$PrepSeries_id = $sel[0]['PrepSeries_id'];
				} else {
					$sql = "
						declare
							@PrepSeries_id bigint,
							@Prep_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000);

						set @Prep_id = (select top 1 DrugPrep_id from rls.Drug with(nolock) where Drug_id = :Drug_id);

						execute rls.p_PrepSeries_ins
							@PrepSeries_id = @PrepSeries_id output,
							@Prep_id = @Prep_id,
							@PrepSeries_Ser = :PrepSeries_Ser,
							@PrepSeries_GodnDate = :PrepSeries_GodnDate,
							@PackNx_Code = null,
							@PrepSeries_IsDefect = null,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @PrepSeries_id as PrepSeries_id;
					";
					$result = $this->db->query($sql, array(
						'Drug_id' => $data['Drug_id'],
						'PrepSeries_Ser' => $data['PrepSeries_Ser'],
						'PrepSeries_GodnDate' => isset($data['PrepSeries_GodnDate']) ? $data['PrepSeries_GodnDate'] : null,
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_object($result)) {
						$sel = $result->result('array');
						if ( $sel[0]['PrepSeries_id'] > 0 )
							$PrepSeries_id = $sel[0]['PrepSeries_id'];
					}
				}
			}
		}
		return $PrepSeries_id;
	}

	/**
	 *  Функция
	 */
	function loadDocumentUcEdit($data) {
		
		$params = array();
		$filter = "(1=1)";
		
		// Выбираем только документы для этой аптеки/контрагента
		/*if ((isset($data['Contragent_id'])) && ($data['Contragent_id']>0))
		{
			$filter = $filter." and DocUc.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		}
		else 
		{
			return false;
		}
		*/
		// И/или берем только один какой-то документ
		if ((isset($data['DocumentUc_id'])) && ($data['DocumentUc_id']>0))
		{
			$filter = $filter." and DocUc.DocumentUc_id = :DocumentUc_id";
			$params['DocumentUc_id'] = $data['DocumentUc_id'];
		}
		else 
		{
			return false;
		}
		
		$fields4 = "";
		$fields6 = "";
		$join4 = "";
		$join6 = "";

		if ($data['DrugDocumentType_id'] == 4) {
			$fields4 = "DocUc.Contragent_sid as Contragent_id, DocUc.Mol_sid as Mol_id, DocUc.DocumentUc_InvNum, convert(varchar(10), DocUc.DocumentUc_InvDate, 104) as DocumentUc_InvDate,";
			$join4 = "";
		}
		
		if ($data['DrugDocumentType_id'] == 6) {
			$fields6 = "RTrim(DrugDocumentStatus_Name) as DrugDocumentStatus_Name,";
			//$fields6 = $fields6." DocUc.DocumentUc_NZU,";
			$join6 = "";
		}
		
		$query = "
			Select 
				DocUc.DocumentUc_id,
				DocUc.DrugDocumentStatus_id,
				RTrim(DocUc.DocumentUc_Num) as DocumentUc_Num,
				convert(varchar(10), DocUc.DocumentUc_setDate, 104) as DocumentUc_setDate,
				convert(varchar(10), DocUc.DocumentUc_didDate, 104) as DocumentUc_didDate,
				DocUc.DrugDocumentType_id,
				DDT.DrugDocumentType_Code,
				DDS.DrugDocumentStatus_Code,
				DocUc.Contragent_tid,
				DocUc.Storage_tid,
				DocUc.Mol_tid,
				--RTrim(T.Contragent_Name) as Contragent_tName,
				DocUc.Contragent_sid,
				DocUc.Storage_sid,
				DocUc.Mol_sid,
				--RTrim(S.Contragent_Name) as Contragent_sName,
				RTrim(DocUc.DocumentUc_DogNum) as DocumentUc_DogNum,
				convert(varchar(10), DocUc.DocumentUc_DogDate, 104) as DocumentUc_DogDate,
				convert(varchar(10), DocUc.DocumentUc_begDT, 104) as DocumentUc_begDate,
				convert(varchar(10), DocUc.DocumentUc_endDT, 104) as DocumentUc_endDate,
				convert(varchar(10), DocUc.DocumentUc_planDT, 104) as DocumentUc_planDate,
				DocUc.DrugDocumentClass_id,
				{$fields4}
				{$fields6}				
				DocUc.DrugFinance_id,
				DocUc.WhsDocumentCostItemType_id,
				DocUc.WhsDocumentUc_id
			from v_DocumentUc DocUc with (nolock)
				--left join Contragent T with (nolock) on T.Contragent_id = DocUc.Contragent_tid --потребитель
				--left join Contragent S with (nolock) on S.Contragent_id = DocUc.Contragent_sid --поставщик
				left join v_DrugDocumentType DDT with (nolock) on DDT.DrugDocumentType_id = DocUc.DrugDocumentType_id
				left join v_DrugDocumentStatus DDS with (nolock) on DDS.DrugDocumentStatus_id = DocUc.DrugDocumentStatus_id
				{$join4}
				{$join6}
			where 
				{$filter}
		";

		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Функция
	 */
	function loadDocumentUcStrView($data) {
		$params = array();
		$filter = "(1=1)";
		$join = "";
		
		// Выбираем только строки по одному документу
		if ((isset($data['DocumentUc_id'])) && ($data['DocumentUc_id']>0))
		{
			$filter = $filter." and DocUcStr.DocumentUc_id = :DocumentUc_id";
			$params['DocumentUc_id'] = $data['DocumentUc_id'];
		}
		
		// Выбираем одну строку 
		if ((isset($data['DocumentUcStr_id'])) && ($data['DocumentUcStr_id']>0))
		{
			$filter = $filter." and DocUcStr.DocumentUcStr_id = :DocumentUcStr_id";
			$params['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
		}
		
		if ((!isset($data['DocumentUc_id'])) && (!isset($data['DocumentUcStr_id'])))
		{
			return false;
		}

		// Фильтры
		if (!empty($data['Drug_id'])) {
			$filter = $filter." and DocUcStr.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}
		if (!empty($data['Person_id'])) {
			$filter = $filter." and DocUcStr.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		$query = "
			Select 
				DocUcStr.DocumentUcStr_id,
				DocUcStr.Drug_id,
				Drug.DrugPrepFas_id,
				Drug.Drug_Name,
				Drug.Drug_Code,
				case when isnull(Drug.Drug_deleted, 1) = 2 then 1 else 0 end as DrugDeleted,
				DocUcStr.DrugFinance_id,
				DocUc.WhsDocumentCostItemType_id,
				DocUcStr.DrugNds_id,
				RTrim(DrugNds_Name) as DocumentUcStr_Nds,
				DocumentUcStr_Price, 
				cast(DocumentUcStr_Price*(1+(isnull(DrugNds.DrugNds_Code, 0)/100.0)) as decimal(12,2)) as DocumentUcStr_NdsPrice,
				DocumentUcStr_PriceR,
				-- Rash - это то, что в расход, просто Count - это соответственно приход
				-- а когда делаем вывод, то количество выводим то, которое есть 
				ROUND(IsNull(DocumentUcStr_Count, DocumentUcStr_RashCount),4) as DocumentUcStr_Count, 
				DocumentUcStr_EdCount,
				DocumentUcStr_Ser,
				ROUND(DocumentUcStr_RashCount,4) as DocumentUcStr_RashCount,
				DocumentUcStr_Sum,
				cast(DocumentUcStr_Price*(1+(isnull(DrugNds.DrugNds_Code, 0)/100.0)) as decimal(12,2)) * DocumentUcStr_Count as DocumentUcStr_NdsSum,
				DocumentUcStr_SumR, 
				DocumentUcStr_SumNds,
				DocumentUcStr_SumNdsR,
				DocumentUcStr_NZU,
				convert(varchar(10), DocUcStr.DocumentUcStr_godnDate, 104) as DocumentUcStr_godnDate,
				DocUcStr.DrugProducer_id,
				DrugProducer.DrugProducer_Code,
				DrugProducer.DrugProducer_Name,
				DrugProducer.DrugProducer_Country,
				DocumentUcStr_CertNum,
				convert(varchar(10), DocUcStr.DocumentUcStr_CertDate, 104) as DocumentUcStr_CertDate,
				convert(varchar(10), DocUcStr.DocumentUcStr_CertGodnDate, 104) as DocumentUcStr_CertGodnDate,
				DocumentUcStr_CertOrg,
				DocumentUcStr_Decl,
				DocumentUcStr_Barcod,
				convert(varchar(10), DocUcStr.DocumentUcStr_RegDate, 104) as DocumentUcStr_RegDate,
				DocumentUcStr_RegPrice,
				DocumentUcStr_CertNM,
				convert(varchar(10), DocUcStr.DocumentUcStr_CertDM, 104) as DocumentUcStr_CertDM,
				DocumentUcStr_NTU,
				DrugLabResult_Name,
				DocumentUcStr_IsLab,
				DocumentUcStr_oid, -- для партии
				isnull(isDefect.YesNo_Code, 0) as PrepSeries_isDefect, -- фальсификат
				Okei_id,
				DocumentUcStr_PlanPrice,
				DocumentUcStr_PlanKolvo,
				DocumentUcStr_PlanSum,
				DocUcStr.Person_id,
				isnull(rtrim(PS.Person_Surname)+' ','') + isnull(rtrim(PS.Person_Firname)+' ','') + isnull(rtrim(PS.Person_Secname),'') as Person_Fio,
				RecordStatus_Code = 1
			from v_DocumentUcStr DocUcStr with(nolock)
			left join v_DocumentUc DocUc with (nolock) on DocUc.DocumentUc_id = DocUcStr.DocumentUc_id
			left join rls.Drug Drug with (nolock) on Drug.Drug_id = DocUcStr.Drug_id
			left join DrugNds with (nolock) on DrugNds.DrugNds_id = DocUcStr.DrugNds_id
			left join DrugProducer with (nolock) on DrugProducer.DrugProducer_id = DocUcStr.DrugProducer_id
			left join rls.v_PrepSeries PrepSeries with (nolock) on PrepSeries.PrepSeries_id = DocUcStr.PrepSeries_id
			left join v_YesNo isDefect with (nolock) on isDefect.YesNo_id = PrepSeries.PrepSeries_isDefect
			left join v_PersonState PS with (nolock) on PS.Person_id = DocUcStr.Person_id
			where 
				{$filter}
			order by Drug_Name
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);
		
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
	 *  Функция
	 */
	function loadDocumentInvStrView($data) {
		$params = array();
		$filter = "(1=1)";
		
		// Выбираем только строки по одному документу
		if ((isset($data['DocumentUc_id'])) && ($data['DocumentUc_id']>0)) {
			$filter = $filter." and DUS.DocumentUc_id = :DocumentUc_id";
			$params['DocumentUc_id'] = $data['DocumentUc_id'];
		}
		
		// Выбираем одну строку 
		if ((isset($data['DocumentUcStr_id'])) && ($data['DocumentUcStr_id']>0)) {
			$filter = $filter." and DUS.DocumentUcStr_id = :DocumentUcStr_id";
			$params['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
		}
		
		if ((!isset($data['DocumentUc_id'])) && (!isset($data['DocumentUcStr_id']))) {
			return false;
		}
		$query = "
			Select 
				DU.DocumentUc_InvNum,
				convert(varchar(10), DU.DocumentUc_InvDate, 104) as DocumentUc_InvDate,
				DUS.Drug_id,
				DUS.DocumentUcStr_id,
				C.Contragent_Name,
				(M.Person_FirName + ' ' + M.Person_SecName + ' ' + M.Person_SurName) as Mol_Name,
				Ost.DrugFinance_Name,
				Ost.WhsDocumentCostItemType_Name,
				D.Drug_Code,
				D.Drug_Name,
				D.Drug_Fas,
				ROUND(IsNull(Ost.DocumentUcStr_Ost,0), 3) as DocumentUcStr_OstCount,
				ROUND((
					case 
						when (Ost.DocumentUcStr_Ost is not null and D.Drug_Fas is not null)
						then (Ost.DocumentUcStr_Ost * D.Drug_Fas)
						else 0
					end
				), 3) as DocumentUcStr_OstEdCount,
				ROUND(IsNull(DUS.DocumentUcStr_Count,0), 3) as DocumentUcStr_Count,
				ROUND(IsNull(DUS.DocumentUcStr_EdCount,0), 3) as DocumentUcStr_EdCount,
				(
					case 
						when (IsNull(Ost.DocumentUcStr_Ost,0) < IsNull(DUS.DocumentUcStr_Count,0)) then 'Избыток'
						when (IsNull(Ost.DocumentUcStr_Ost,0) > IsNull(DUS.DocumentUcStr_Count,0)) then 'Недостача'
						else 'Норма'
					end
				) as balance,
				GU.GoodsUnit_Name as unit,
				(
					case
						when ISNULL(DUS.DocumentUcStr_Sum, 0) > 0 then DUS.DocumentUcStr_Sum
						else ISNULL(DUS.DocumentUcStr_SumR, 0)
					end
				) as DocumentUcStr_Sum,
				ROUND(((
					case
						when ISNULL(Ost.DocumentUcStr_Price, 0) > 0 then Ost.DocumentUcStr_Price
						else ISNULL(Ost.DocumentUcStr_PriceR, 0)
					end
				) * Ost.DocumentUcStr_Count),3) as ostsum,
				Ost.DocumentUcStr_Ser,
				IsNull(DUS.DocumentUcStr_Price,DUS.DocumentUcStr_PriceR) as price, --необходимо уточнить, как будет правильно
				(ISNULL(cast(D.Drug_Code as varchar) + '. ', '') + D.Drug_Name) as Drug_CodeName
			from v_DocumentUcStr DUS with(nolock)
			left join v_DocumentUc DU with (nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
			left join v_Contragent C with (nolock) on C.Contragent_id = DU.Contragent_sid
			left join v_Mol M with (nolock) on M.Mol_id = DU.Mol_sid
			left join rls.v_Drug D with (nolock) on D.Drug_id = DUS.Drug_id
			--left join DrugNds with (nolock) on DrugNds.DrugNds_id = DUS.DrugNds_id
			--left join DrugProducer with (nolock) on DrugProducer.DrugProducer_id = DUS.DrugProducer_id
			left join DrugFinance DF with (nolock) on DF.DrugFinance_id = DUS.DrugFinance_id
			left join v_DocumentUcOst_Lite Ost with (nolock) on Ost.DocumentUcStr_id = DUS.DocumentUcStr_oid
			left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = isnull(DUS.GoodsUnit_id, '57')
			where 
				{$filter}
			order by D.Drug_Name
		";

		//echo getDebugSql($query, $params);	exit;

		$result = $this->db->query($query, $params);
		
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
	 * Загрузка списка доступных партий
	 */
	function loadDocumentUcStrList($data) {
		$filter = "(1 = 1)";
		$queryParams = array();
		$join = "";
		if (!empty($data['Contragent_id'])) {
			$filter .= " and DUS.Contragent_tid = :Contragent_tid";
			$queryParams['Contragent_tid'] = $data['Contragent_id'];
		}
		if (!empty($data['LpuSection_id'])) { // Если списываем с отделения в персучете (то есть по отделению получаем контрагента)
			// получаем контрагента по отделению ЛПУ 
			$join = "inner join v_Contragent C with (nolock) on  C.Contragent_id = DUS.Contragent_tid and C.LpuSection_id = :LpuSection_id ";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}
		if ((empty($data['LpuSection_id'])) && (empty($data['Contragent_id']))) { // если нет контрагента, то передаем сообщение об ошибке (его надо обрабатывать на клиенте)
			return array(array('Error_Msg'=>'Не определен контрагент для списания медикамента!'));
		}
		
		if ( isset($data['EvnRecept_Kolvo']) && $data['EvnRecept_Kolvo'] > 0 ) {
			$filter .= " and DUS.DocumentUcStr_Ost >= :EvnRecept_Kolvo";
			$queryParams['EvnRecept_Kolvo'] = $data['EvnRecept_Kolvo'];
		}

		if ( isset($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0 ) {
			$filter .= " and (DUS.DrugFinance_id is null or DUS.DrugFinance_id = :DrugFinance_id)";
			$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
		}

		if ( isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id'] > 0 ) {
			$filter .= " and (DUS.WhsDocumentCostItemType_id is null or DUS.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)";
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		
		switch ( $data['mode'] ) {
			case 'default':
				$filter .= ' and DUS.Drug_id = :Drug_id';
				$filter .= " and (DocumentUc_didDate <= :date or :date Is Null) ";
				if (isset($data['is_personal']) && $data['is_personal'] == 1 && $_SESSION['region']['nick'] != 'perm') { //отключено для Перми по задаче #85080
					$filter .= " and (:DocumentUcStr_id is not null or :date is null or DUS.DocumentUcStr_godnDate is null or DUS.DocumentUcStr_godnDate >= :date) ";
				}
				$queryParams['date'] = $data['date'];
			break;

			case 'recept': // все что в этом кейсе жутко устарело, имхо (с) Night, 2011-01-10
				$filter .= ' and DrugMnn.DrugMnn_id = :DrugMnn_id';
				$filter .= ' and ((DrugMnn.DrugMnn_id = 1 and (DrugTorg.DrugTorg_id = :DrugTorg_id or Drug.Drug_id = :Drug_id)) or (DrugTorg.DrugTorg_id = :DrugTorg_id or :DrugTorg_id is null))';
				$filter .= ' and EvnRecept_Drug.DrugFormGroup_id = ISNULL(DrugForm.DrugFormGroup_id, 0)';
				$filter .= " and DUS.DrugFinance_id = :DrugFinance_id";
				$filter .= " and ISNULL(DUS.DocumentUc_setDate, cast(:EvnRecept_otpDate as datetime)) = cast(:EvnRecept_otpDate as datetime)";
				$join = "
				inner join rls.v_Drug Drug with (nolock) on Drug.Drug_id = DUS.Drug_id
				inner join DrugForm with (nolock) on DrugForm.DrugForm_id = Drug.DrugForm_id
				left join DrugMnn with (nolock) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id 
				left join DrugTorg with (nolock) on DrugTorg.DrugTorg_id = Drug.DrugTorg_id
				";
				if ( (!isset($data['FarmacyOtdel_id'])) || ($data['FarmacyOtdel_id'] <= 0) ) {
					return array(array('Error_Msg' => 'Невозможно определить отдел аптеки'));
				}

				if ( !isset($data['EvnRecept_otpDate']) ) {
					return array(array('Error_Msg' => 'Не задана дата отпуска рецепта'));
				}

				$queryParams['DrugFinance_id'] = $data['FarmacyOtdel_id'];
				$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
				$queryParams['DrugTorg_id'] = $data['DrugTorg_id'];
				$queryParams['EvnRecept_otpDate'] = $data['EvnRecept_otpDate'];
			break;

			default:
				return false;
			break;
		}


		//кстати! для ЛПУ источник финансирования берется из строки документа, а для аптеки - из самого медикамента 
		// и это реализовано в sql-функции
		
		$query = "
			select --distinct
				1 as [query],
				DUS.DocumentUcStr_id as DocumentUcStr_id,
				DUS.DocumentUc_id,
				DUS.DrugNds_id as DrugNds_id,
				DUS.DrugFinance_id as DrugFinance_id,
				DUS.WhsDocumentCostItemType_id as WhsDocumentCostItemType_id,
				'годн. '+IsNull(convert(varchar(10), DUS.DocumentUcStr_godnDate, 104),'отсут.')+', цена '+cast(ROUND(IsNull(DUS.DocumentUcStr_PriceR,0), 2) as varchar(20))+', ост. '+
				cast(cast(Round(IsNull(DUS.DocumentUcStr_Ost,0),4) as numeric(16,4)) as varchar(20))+', фин. '+RTRIM(RTRIM(ISNULL(DUS.DrugFinance_Name, 'отсут.')))+', серия '+RTRIM(ISNULL(DUS.DocumentUcStr_Ser, ''))
				 as DocumentUcStr_Name,
				ROUND(IsNull(DUS.DocumentUcStr_Ost,0), 4) as DocumentUcStr_Ost,
				ROUND(IsNull(DUS.DocumentUcStr_Ost,0), 4) as DocumentUcStr_Count,
				RTRIM(ISNULL(DUS.DrugFinance_Name, '')) as DrugFinance_Name,
				RTRIM(ISNULL(DUS.WhsDocumentCostItemType_Name, '')) as WhsDocumentCostItemType_Name,
				ROUND(DUS.DocumentUcStr_Price, 2) as DocumentUcStr_Price,
				ROUND(DUS.DocumentUcStr_PriceR, 2) as DocumentUcStr_PriceR,
				ROUND(DUS.DocumentUcStr_PriceR, 2) as EvnDrug_Price,
				DUS.DocumentUcStr_CertNum,
				DUS.DocumentUcStr_IsLab,
				IsNull(convert(varchar(10), DUS.DocumentUcStr_godnDate, 104),'') as DocumentUcStr_godnDate,
				RTRIM(ISNULL(DUS.DocumentUcStr_Ser, '')) as DocumentUcStr_Ser,
				RTRIM(ISNULL(DUS.DocumentUcStr_NZU, '')) as DocumentUcStr_NZU,
				isnull(DUS.PrepSeries_IsDefect,1) as PrepSeries_IsDefect
			from dbo.DocumentUcOst_Lite(:DocumentUcStr_id) DUS
				{$join}
				outer apply (
					select top 1
						ISNULL(D.Drug_Fas, 0) as Drug_Fas,
						ISNULL(D.Drug_Dose, 0) as Drug_DoseQ
					from rls.v_Drug D with (nolock)
						--inner join DrugForm DF on DF.DrugForm_id = D.DrugForm_id
					where D.Drug_id = :Drug_id
				) EvnRecept_Drug
			where " . $filter . "
				-- нулевые остатки
				-- and ROUND(IsNull(DUS.DocumentUcStr_Ost,0), 4)>0
		";

        //если передается идентификатор конкретной строки остатков, добавляем её к результатам запроса
        if (!empty($data['Ost_DocumentUcStr_id'])) {
            //секция select идентична аналогичной секции в основном запросе
            $query .= "
                union select
					2 as [query],
                    DUS.DocumentUcStr_id as DocumentUcStr_id,
                    DUS.DocumentUc_id,
                    DUS.DrugNds_id as DrugNds_id,
                    DUS.DrugFinance_id as DrugFinance_id,
                    DUS.WhsDocumentCostItemType_id as WhsDocumentCostItemType_id,
                    'годн. '+IsNull(convert(varchar(10), DUS.DocumentUcStr_godnDate, 104),'отсут.')+', цена '+cast(ROUND(IsNull(DUS.DocumentUcStr_PriceR,0), 2) as varchar(20))+', ост. '+
                    cast(cast(Round(IsNull(DUS.DocumentUcStr_Ost,0),4) as numeric(16,4)) as varchar(20))+', фин. '+RTRIM(RTRIM(ISNULL(DUS.DrugFinance_Name, 'отсут.')))+', серия '+RTRIM(ISNULL(DUS.DocumentUcStr_Ser, ''))
                     as DocumentUcStr_Name,
                    ROUND(IsNull(DUS.DocumentUcStr_Ost,0), 4) as DocumentUcStr_Ost,
                    ROUND(IsNull(DUS.DocumentUcStr_Ost,0), 4) as DocumentUcStr_Count,
                    RTRIM(ISNULL(DUS.DrugFinance_Name, '')) as DrugFinance_Name,
                    RTRIM(ISNULL(DUS.WhsDocumentCostItemType_Name, '')) as WhsDocumentCostItemType_Name,
                    ROUND(DUS.DocumentUcStr_Price, 2) as DocumentUcStr_Price,
                    ROUND(DUS.DocumentUcStr_PriceR, 2) as DocumentUcStr_PriceR,
                    ROUND(DUS.DocumentUcStr_PriceR, 2) as EvnDrug_Price,
                    DUS.DocumentUcStr_CertNum,
                    DUS.DocumentUcStr_IsLab,
                    IsNull(convert(varchar(10), DUS.DocumentUcStr_godnDate, 104),'') as DocumentUcStr_godnDate,
                    RTRIM(ISNULL(DUS.DocumentUcStr_Ser, '')) as DocumentUcStr_Ser,
                    RTRIM(ISNULL(DUS.DocumentUcStr_NZU, '')) as DocumentUcStr_NZU,
                    isnull(DUS.PrepSeries_IsDefect,1) as PrepSeries_IsDefect
                from
                    dbo.DocumentUcOst_Lite(:DocumentUcStr_id) DUS
                where
                    DUS.DocumentUcStr_id = :Ost_DocumentUcStr_id
            ";
            $queryParams['Ost_DocumentUcStr_id'] = $data['Ost_DocumentUcStr_id'];
        }

		$queryParams['Drug_id'] = $data['Drug_id'];
		$queryParams['DocumentUc_id'] = $data['DocumentUc_id'];
		$queryParams['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
		// везде, где он поставшик 
		$queryParams['Contragent_id'] = $data['Contragent_id'];
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$res = $result->result('array');
			foreach ($res as $k => $row) {
				if ($row['DocumentUcStr_Ost'] <= 0 && $row['query'] == 1) {
					unset($res[$k]);
				}
			}
			$res = array_values($res);
			return $res;
		} else {
			return false;
		}
	}


	/**
	 *  Функция
	 */
	function evnReceptProcess($data) {
		// Проверка наличия медикамента
		// ...

		$this->db->trans_begin();

		switch ( $data['ProcessingType_Name'] ) {
			// Отоваривание
			case 'release':
				$document_uc_id = NULL;

				if ( (!isset($data['FarmacyOtdel_id'])) || ($data['FarmacyOtdel_id'] <= 0) ) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Невозможно определить отдел аптеки'));
				}

				// Проверяем наличие документа расхода на текущую дату
				$query = "
					select
						top 1 DocumentUc_id
					from DocumentUc with (nolock)
					where (1 = 1)
						and Contragent_id = :Contragent_id
						and Contragent_sid = :Contragent_id
						and Contragent_tid = 1
						and DrugDocumentType_id = 1
						and DrugFinance_id = :DrugFinance_id
						and DocumentUc_setDate = cast(:EvnRecept_otpDate as datetime)
				";
				//		-- and Mol_sid = :Mol_id

				$queryParams = array(
					'Contragent_id' => $data['Contragent_id'],
					'DrugFinance_id' => $data['FarmacyOtdel_id'],
					'EvnRecept_otpDate' => $data['EvnRecept_otpDate']
				);

				$result = $this->db->query($query, $queryParams);

				if ( !is_object($result) ) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
				}

				$response = $result->result('array');

				if ( is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id']) ) {
					// Документ существует
					$document_uc_id = $response[0]['DocumentUc_id'];
				}
				else {
					// Документ не существует
					// Добавляем новый документ учета
					$query = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = NULL;

						exec p_DocumentUc_ins
							@DocumentUc_id = @Res output,
							@DocumentUc_Num = :DocumentUc_Num,
							@DocumentUc_setDate = :EvnRecept_otpDate,
							@DocumentUc_didDate = :EvnRecept_otpDate,
							@Contragent_id = :Contragent_id,
							@Contragent_sid = :Contragent_id,
							@Contragent_tid = 1,
							@DrugFinance_id = :DrugFinance_id,
							@DrugDocumentType_id = 1,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Res as DocumentUc_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
					//		-- @Mol_sid = :Mol_id,

					$queryParams = array(
						'Contragent_id' => $data['Contragent_id'],
						'DocumentUc_Num' => str_replace('-', '', $data['EvnRecept_otpDate']) . $data['FarmacyOtdel_id'],
						'DrugFinance_id' => $data['FarmacyOtdel_id'],
						'EvnRecept_otpDate' => $data['EvnRecept_otpDate'],
						/*'Mol_id' => $data['Mol_id'],*/
						'pmUser_id' => $data['pmUser_id']
					);

					$result = $this->db->query($query, $queryParams);

					if ( !is_object($result) ) {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (добавление нового документа учета)'));
					}

					$response = $result->result('array');

					if ( is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id']) ) {
						$document_uc_id = $response[0]['DocumentUc_id'];
					}
					else {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Ошибка при добавлении нового документа учета'));
					}
				}

				for ( $i = 0; $i < count($data['documentUcStrData']); $i++ ) {
					// Добавляем новую расходную строку в документ учета
					$query = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = NULL;

						exec p_DocumentUcStr_ins
							@DocumentUcStr_id = @Res output,
							@DocumentUcStr_oid = :DocumentUcStr_oid,
							@DocumentUc_id = :DocumentUc_id,
							@DocumentUcStr_RashCount = :DocumentUcStr_RashCount,
							@DocumentUcStr_Sum = :DocumentUcStr_Sum,
							@DocumentUcStr_SumNdsR = :DocumentUcStr_SumNdsR,
							@DocumentUcStr_setDate = :EvnRecept_otpDate,
							@EvnRecept_id = :EvnRecept_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Res as DocumentUcStr_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";

					$queryParams = array(
						'DocumentUcStr_oid' => $data['documentUcStrData'][$i]['DocumentUcStr_oid'],
						'DocumentUc_id' => $document_uc_id,
						'DocumentUcStr_RashCount' => $data['documentUcStrData'][$i]['DocumentUcStr_RashCount'],
						'DocumentUcStr_Sum' => $data['documentUcStrData'][$i]['DocumentUcStr_Sum'],
						'DocumentUcStr_SumNdsR' => $data['documentUcStrData'][$i]['DocumentUcStr_SumNdsR'],
						'EvnRecept_otpDate' => $data['EvnRecept_otpDate'],
						'EvnRecept_id' => $data['EvnRecept_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					$result = $this->db->query($query, $queryParams);

					if ( !is_object($result) ) {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (добавление строки расхода)'));
					}

					$response = $result->result('array');

					if ( is_array($response) && count($response) > 0 && isset($response[0]['DocumentUcStr_id']) ) {
						$document_uc_str_id = $response[0]['DocumentUcStr_id'];
					}
					else {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Ошибка при добавлении строки расхода'));
					}
				}

				$query = "
					declare
						@EvnRecept_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000),
						@ReceptDelayType_id bigint;
					set @EvnRecept_id = :EvnRecept_id;

					set nocount on;

					begin try
						set @ReceptDelayType_id = (select top 1 ReceptDelayType_id from EvnRecept with(nolock) where EvnRecept_id = @EvnRecept_id);

						if ( @ReceptDelayType_id = 2 )
							update
								EvnRecept WITH (ROWLOCK)
							set
								OrgFarmacy_oid = :OrgFarmacy_oid,
								EvnRecept_otpDT = :EvnRecept_otpDate,
								ReceptDelayType_id = 1
							where
								EvnRecept_id = @EvnRecept_id;
						else
							update
								EvnRecept WITH (ROWLOCK)
							set
								OrgFarmacy_oid = :OrgFarmacy_oid,
								EvnRecept_obrDT = :EvnRecept_obrDate,
								EvnRecept_otpDT = :EvnRecept_otpDate,
								ReceptDelayType_id = 1
							where
								EvnRecept_id = @EvnRecept_id;
					end try
					begin catch
						set @Error_Code = error_number();
						set @Error_Message = error_message();
					end catch

					select @EvnRecept_id as EvnRecept_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;

					set nocount off;
				";

				$queryParams = array(
					'EvnRecept_id' => $data['EvnRecept_id'],
					'EvnRecept_obrDate' => $data['EvnRecept_obrDate'],
					'EvnRecept_otpDate' => $data['EvnRecept_otpDate'],
					'OrgFarmacy_oid' => $data['OrgFarmacy_id']
				);

				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$response = $result->result('array');
					$this->db->trans_commit();
					return $response;
				}
				else {
					$this->db->trans_rollback();
					return false;
				}
			break;

			case 'reserve':
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Функционал по резервированию рецепта находится в разработке'));
			break;
		}
	}


	/**
	 * Загрузка списка доступных партий
	 */
	function loadEvnReceptTrafficBook($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		if ( isset($data['FarmacyOtdel_id']) ) {
			$filter .= " and (RDT.ReceptDelayType_id <> 1 or DocUcStr.DrugFinance_id = :DrugFinance_id)";
			$queryParams['DrugFinance_id'] = $data['FarmacyOtdel_id'];
		}

		if ( isset($data['EvnRecept_obrDate'][0]) ) {
			$filter .= " and ER.EvnRecept_obrDT >= cast(:EvnRecept_obrDate_0 as datetime)";
			$queryParams['EvnRecept_obrDate_0'] = $data['EvnRecept_obrDate'][0];
		}

		if ( isset($data['EvnRecept_obrDate'][1]) ) {
			$filter .= " and ER.EvnRecept_obrDT <= cast(:EvnRecept_obrDate_1 as datetime)";
			$queryParams['EvnRecept_obrDate_1'] = $data['EvnRecept_obrDate'][1];
		}

		if ( isset($data['EvnRecept_otpDate'][0]) ) {
			$filter .= " and ER.EvnRecept_otpDT >= cast(:EvnRecept_otpDate_0 as datetime)";
			$queryParams['EvnRecept_otpDate_0'] = $data['EvnRecept_otpDate'][0];
		}

		if ( isset($data['EvnRecept_otpDate'][1]) ) {
			$filter .= " and ER.EvnRecept_otpDT <= cast(:EvnRecept_otpDate_1 as datetime)";
			$queryParams['EvnRecept_otpDate_1'] = $data['EvnRecept_otpDate'][1];
		}

		if ( isset($data['EvnRecept_setDate'][0]) ) {
			$filter .= " and ER.EvnRecept_setDT >= cast(:EvnRecept_setDate_0 as datetime)";
			$queryParams['EvnRecept_setDate_0'] = $data['EvnRecept_setDate'][0];
		}

		if ( isset($data['EvnRecept_setDate'][1]) ) {
			$filter .= " and ER.EvnRecept_setDT <= cast(:EvnRecept_setDate_1 as datetime)";
			$queryParams['EvnRecept_setDate_1'] = $data['EvnRecept_setDate'][1];
		}

		if ( isset($data['Person_Birthday'][0]) ) {
			$filter .= " and PS.Person_BirthDay >= cast(:Person_Birthday_0 as datetime)";
			$queryParams['Person_Birthday_0'] = $data['Person_Birthday'][0];
		}

		if ( isset($data['Person_Firname']) ) {
			$filter .= " and PS.Person_FirName like :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . "%";
		}

		if ( isset($data['Person_Secname']) ) {
			$filter .= " and PS.Person_SecName like :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . "%";
		}

		if ( isset($data['Person_Surname']) ) {
			$filter .= " and PS.Person_SurName like :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . "%";
		}

		if ( isset($data['Person_Birthday'][1]) ) {
			$filter .= " and PS.Person_BirthDay <= cast(:Person_Birthday_1 as datetime)";
			$queryParams['Person_Birthday_1'] = $data['Person_Birthday'][1];
		}

		if ( isset($data['ReceptDelayType_id']) ) {
			$filter .= " and RDT.ReceptDelayType_id = :ReceptDelayType_id";
			$queryParams['ReceptDelayType_id'] = $data['ReceptDelayType_id'];
		}

		$query = "
			SELECT
				-- select
				ER.EvnRecept_id as EvnRecept_id,
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				RTRIM(ISNULL(DocUcStr.Drug_Name, '')) as Drug_Name,
				convert(varchar(10), ER.EvnRecept_setDate, 104) as EvnRecept_setDate,
				RTRIM(ISNULL(ER.EvnRecept_Ser, '')) as EvnRecept_Ser,
				RTRIM(ISNULL(ER.EvnRecept_Num, '')) as EvnRecept_Num,
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				RTRIM(ERMP.Person_Fio) as MedPersonal_Fio,
				convert(varchar(10), ER.EvnRecept_obrDT, 104) as EvnRecept_obrDate,
				convert(varchar(10), ER.EvnRecept_otpDT, 104) as EvnRecept_otpDate,
				RDT.ReceptDelayType_id,
				RTRIM(ISNULL(RDT.ReceptDelayType_Name, 'Выписан')) as ReceptDelayType_Name,
				DocUcStr.DrugFinance_id,
				ISNULL(ERSum.DocumentUcStr_SumNdsR, 0) as DocumentUcStr_SumNdsR,
				ERSum.DocumentUcStr_Count
				-- end select
			FROM
				-- from
				v_EvnRecept ER with (nolock)
				inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = ER.Lpu_id
				inner join v_PersonState PS with(nolock) on PS.PersonEvn_id = ER.PersonEvn_id
					and PS.Server_id = ER.Server_id
				inner join ReceptDelayType RDT with(nolock) on RDT.ReceptDelayType_id = ER.ReceptDelayType_id
				left join v_MedPersonal ERMP with(nolock) on ERMP.MedPersonal_id = ER.MedPersonal_id
					and ERMP.Lpu_id = ER.Lpu_id
				outer apply (
					select top 1 DR.Drug_Name, DU.DrugFinance_id
					from DocumentUcStr DUS1 with(nolock)
						inner join DocumentUcStr DUS2 with(nolock) on DUS2.DocumentUcStr_id = DUS1.DocumentUcStr_oid
						inner join DocumentUc DU with(nolock) on DU.DocumentUc_id = DUS2.DocumentUc_id
						inner join Drug DR with(nolock) on DR.Drug_id = DUS2.Drug_id
					where DUS1.EvnRecept_id = ER.EvnRecept_id
				) DocUcStr
				outer apply (
					select
						ROUND(SUM(DUS1.DocumentUcStr_SumNdsR), 2) as DocumentUcStr_SumNdsR,
						count(DUS1.DocumentUcStr_id) as DocumentUcStr_Count
					from DocumentUcStr DUS1 with(nolock)
						inner join DocumentUc DU with(nolock) on DU.DocumentUc_id = DUS1.DocumentUc_id
						inner join DrugFinance DF with(nolock) on DF.DrugFinance_id = DU.DrugFinance_id
					where DUS1.EvnRecept_id = ER.EvnRecept_id
				) ERSum
				-- end from
			WHERE
				-- where
				" . $filter . "
				and ER.OrgFarmacy_oid = :OrgFarmacy_oid
				-- end where
			ORDER BY
				-- order by
				ER.EvnRecept_obrDT,
				ER.EvnRecept_otpDT
				-- end order by
		";

		$queryParams['OrgFarmacy_oid'] = $data['OrgFarmacy_id'];

		$response = array();

		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		}
		else {
			return false;
		}

		return $response;
	}


	/**
	 *  Функция получаения упаковок
	 * Допилена 04.10.2013 в рамках задачи https://redmine.swan.perm.ru/issues/25631 - добавлена проверка на тип поставщика: если это организация, то DocumentUcStr_Ost не подключаем
	 */
	function loadDrugList($data) {
		$allowMainQuery = false;
		$ContragentType_is_1 = false; //Тип поставщика - НЕ организация
		$filterList = array();
		$idFilter = "";
		$joinList = array();
		$queryList = array();
		$queryParams = array();

		$result_contragenttype = $this->getFirstResultFromQuery("
			select top 1
				Con.Contragent_id
			from
				v_Contragent Con (nolock)
				left join v_ContragentType ConT (nolock) on ConT.ContragentType_id = Con.ContragentType_id
			where
				Con.Contragent_id = :Contragent_id
				and ConT.ContragentType_SysNick = 'org'
		", array(
			'Contragent_id' => $data['Contragent_id']
		), true);

		if ( !empty($result_contragenttype) ) {
			$ContragentType_is_1 = true;
		}

		switch ( $data['mode'] ) {
			case 'expenditure':
				if(!$ContragentType_is_1){ //Если это НЕ организация, то цепляем DocumentUcStr_Ost
					$uc_ost = "v_DocumentUcOst_Lite DUOL (nolock)";

					if (!empty($data['DocumentUcStr_id'])) {
						$uc_ost = "dbo.DocumentUcOst_Lite(:DocumentUcStr_id) DUOL";
						$queryParams['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
					}

					// @task https://redmine.swan.perm.ru//issues/106903
					// Оптимизация запроса
					$duolFilterList = array('DUOL.Drug_id = Drug.Drug_id', 'DUOL.DocumentUcStr_Ost > 0');
					$duolJoinList = array();

					if ( !empty($data['Contragent_id']) ) {
						$duolFilterList[] = "DUOL.Contragent_tid = :Contragent_id";
						$queryParams['Contragent_id'] = $data['Contragent_id'];
					}

					if ( !empty($data['LpuSection_id']) ) {
						$duolJoinList[] = "
							inner join Contragent C (nolock) on C.Contragent_id = DUOL.Contragent_tid and C.LpuSection_id = :LpuSection_id
						";
						$queryParams['LpuSection_id'] = $data['LpuSection_id'];

						// По дате
						// Почему фильтр по дате действует только при указанном LpuSection_id?
						if ( !empty($data['date']) ) {
							$duolFilterList[] = "DUOL.DocumentUc_didDate <= :date";
							$queryParams['date'] = $data['date'];
						}
					}

					if ( !empty($data['WhsDocumentCostItemType_id']) ) {
						$duolFilterList[] = "(DUOL.WhsDocumentCostItemType_id is null or DUOL.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)";
						$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
					}

					if ( !empty($data['DrugFinance_id']) ) {
						$duolFilterList[] = "(DUOL.DrugFinance_id is null or DUOL.DrugFinance_id = :DrugFinance_id)";
						$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
					}

					$joinList[] = "
						cross apply (
						 	select top 1 DUOL.Drug_id
						 	from {$uc_ost}
						 		" . implode(' and ', $duolJoinList) . "
						 	where
						 		" . implode(' and ', $duolFilterList) . "
						 ) DUOL
					";
				}
			break;
		}

		if ( !empty($data['Drug_id']) ) {
			$idFilter = "Drug.Drug_id = :Drug_id";
			$queryParams['Drug_id'] = $data['Drug_id'];
		}
		
		// Фильтрация по первому кобмбобоксу
		if ( !empty($data['DrugPrepFas_id']) ) {
			$filterList[] = "Drug.DrugPrepFas_id = :DrugPrepFas_id";
			$allowMainQuery = true;
			$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
		}

		if ( !empty($data['query']) ) {
			$filterList[] = "Drug.Drug_Name like :Drug_Name";
			$allowMainQuery = true;
			$queryParams['Drug_Name'] = $data['query'] . "%";
		}
		else if ( !empty($data['Drug_Name']) ) {
			$filterList[] = "Drug.Drug_Name like :Drug_Name";
			$allowMainQuery = true;
			$queryParams['Drug_Name'] = "%" . $data['Drug_Name'] . "%";
		}
		
		if ( !empty($data['Drug_Code']) ) {
			$filterList[] = "Drug.Drug_Code like :Drug_Code";
			$allowMainQuery = true;
			$queryParams['Drug_Code'] = "%" . $data['Drug_Code'] . "%";
		}

		$baseQuery = "
			select distinct top 500
				RTRIM(ISNULL(Drug.Drug_Nomen, '')) as Drug_Name,
				RTRIM(ISNULL(Drug.Drug_Name, '')) as Drug_FullName,
				Drug.Drug_id as Drug_id,
				Drug.Drug_Code as Drug_Code,
				Drug.Drug_Fas as Drug_Fas,
				RTRIM(ISNULL(Drug.DrugForm_Name, '')) as DrugForm_Name,
				RTRIM(ISNULL(Drug.Drug_PackName, '')) as DrugUnit_Name,
				gu.GoodsUnit_id
			from
				rls.v_Drug Drug with (nolock)
				left join rls.v_DrugComplexMnn dMnn (nolock) on dMnn.DrugComplexMnn_id = Drug.DrugComplexMnn_id
				outer apply (
					select top 1 isnull(gpc.GoodsUnit_id, 57) as GoodsUnit_id
					from v_GoodsPackCount gpc with (nolock)
					where gpc.DrugComplexMnn_id = dMnn.DrugComplexMnn_id
				) gu
		";

		if ( $allowMainQuery === true ) {
			$queryList[] = $baseQuery . implode(PHP_EOL, $joinList) . "
				where " . implode(' and ', $filterList) . "
			";
		}

		if ( !empty($idFilter) ) { // если задан фильтр по идентификатору медикамента
			$queryList[] = $baseQuery . "
				where {$idFilter}
			";
		}

		if ( count($queryList) == 0 ) {
			return false;
		}

		// echo getDebugSQL(implode(' union ', $queryList), $queryParams);exit;
		return $this->queryResult(implode(' union ', $queryList), $queryParams);
	}

	/**
	 *  Функция возвращает список медикаментов для первого комбобокса (для аптеки)
	 */
	function loadDrugPrepList($data) {
		$queryParams = array();
		$filter = "(1=1) ";
        $distinct = "";
		$exists_filter = "and exists (Select top 1 1 from rls.v_Drug D with (nolock) where D.DrugPrepFas_id = DrugPrep.DrugPrepFas_id)";
		$id_filter = "";
		$join = "";

		if (($data['load']=='torg') && (($data['Drug_id'] > 0) || ($data['DrugPrepFas_id'] > 0))) {
			// Если выбор при нажатии клавиши распахнуть или F2 (стрелка вниз)
			// Читаем по торговому 
			if ($data['DrugPrepFas_id'] > 0) {
				$join .= "
                    outer apply (
                        Select DrugTorg_Name from rls.v_DrugPrep D with (nolock)
                        where D.DrugPrepFas_id = :DrugPrepFas_id
                    ) DrugTorg
				";
				$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
				$filter .= " and DrugPrep.DrugTorg_Name like ('%'+DrugTorg.DrugTorg_Name)";
			} elseif ($data['Drug_id'] > 0) {
				$join .= "
                    outer apply
                    (
                        Select DrugTorg_Name from rls.v_Drug D with (nolock)
                        where D.Drug_id = :Drug_id
                    ) DrugTorg
				";
				$queryParams['Drug_id'] = $data['Drug_id'];
				$filter .= " and DrugPrep.DrugTorg_Name like ('%'+DrugTorg.DrugTorg_Name)";
			}
		} else {
			// Если передается конкретное значение, то включаем фильтрацию сразу по этому значению
			if ($data['DrugPrepFas_id'] > 0) {
                $id_filter = "DrugPrep.DrugPrepFas_id = :DrugPrepFas_id";
				$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
			} elseif ($data['Drug_id'] > 0) {
				$id_filter = "exists (Select top 1 1 from rls.v_Drug D with (nolock) where D.DrugPrepFas_id = DrugPrep.DrugPrepFas_id and D.Drug_id = :Drug_id)";
				$queryParams['Drug_id'] = $data['Drug_id'];
			} else {
				// Если выполняется поиск 
				if ((isset($data['query'])) && (strlen($data['query'])>=3)) {
					$filter .= " and DrugPrep.DrugPrep_Name like :query";
					$queryParams['query'] = "".$data['query'] . "%";
				} elseif ( isset($data['DrugPrep_Name']) ) {
					$filter .= " and DrugPrep.DrugPrep_Name like :DrugPrep_Name";
					$queryParams['DrugPrep_Name'] = "%".$data['DrugPrep_Name'] . "%";
				} elseif (strlen($data['query']) < 1) {
					//return false;
				}
			}
		}
		// другие фильтры 
		
		switch ( $data['mode'] ) {
			// если режим внутри ЛПУ, то учитываем контагентов
			case 'expenditure':
                $distinct = "distinct";
				$uc_ost = "v_DocumentUcOst_Lite DUO (nolock)";

				if (isset($data['DocumentUcStr_id']) && $data['DocumentUcStr_id'] != '') {
					$uc_ost = "dbo.DocumentUcOst_Lite(:DocumentUcStr_id) DUO";
					$queryParams['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
				}

				$join .= "inner join rls.v_Drug Drug (nolock) on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id ";
				$join .= "inner join ".$uc_ost." on Drug.Drug_id = DUO.Drug_id ";
				$join .= "left join v_DocumentUcStr DUS on DUS.DocumentUcStr_id = DUO.DocumentUcStr_id ";

				if ($data['Contragent_id'] > 0) {
					$filter .= " and DUO.Contragent_tid = :Contragent_id ";
					$queryParams['Contragent_id'] = $data['Contragent_id'];
				}

				if ($data['LpuSection_id'] > 0) {
					$filter .= " and C.LpuSection_id = :LpuSection_id ";
					$filter .= " and DUO.DocumentUcStr_Ost > :Drug_Kolvo";

					$queryParams['Drug_Kolvo'] = (!empty($data['Drug_Kolvo']) ? $data['Drug_Kolvo'] : 0);

					// По дате 
					//$filter .= " and (DocumentUc_didDate <= :date or :date Is Null) ";
					//$queryParams['date'] = $data['date'];

					$join .= "inner join Contragent C (nolock) on C.Contragent_id = DUO.Contragent_tid ";
					/*
					$join .= "outer apply (
						select isnull(sum(DocumentUcStr_Ost),0) as cnt 
						from ".$uc_ost." DUOL left join Contragent TC with (nolock) on TC.Contragent_id = DUOL.Contragent_tid
						where Drug_id = Drug.Drug_id and TC.LpuSection_id = :LpuSection_id
					) as ost";
					*/
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				} else {
					$filter .= " and DUO.DocumentUcStr_Ost > 0";
				}

				if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id'] > 0) {
					$filter .= " and (DUO.WhsDocumentCostItemType_id is null or DUO.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)";
					$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
				}

				if (isset($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0) {
					$filter .= " and (isnull(DUO.DrugFinance_id,DUS.DrugFinance_id) is null or isnull(DUO.DrugFinance_id,DUS.DrugFinance_id) = :DrugFinance_id)";
					$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
				}
				break;
			case 'income':
				if ($data['Drug_id']>0) {
					$join .= "inner join rls.v_Drug Drug (nolock) on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id ";
				}
				break;
		}

		if(!empty($data['DrugPrepFasCode_Code'])){
			$join .= " left join rls.v_DrugPrepFasCode DrugPrepFC (nolock) on DrugPrepFC.DrugPrepFas_id = DrugPrep.DrugPrepFas_id ";
			$filter .= " and DrugPrepFC.DrugPrepFasCode_Code = :DrugPrepFasCode_Code ";
			$queryParams['DrugPrepFasCode_Code'] = $data['DrugPrepFasCode_Code'];
		}

        $base_query = "
            select {$distinct} top 500 -- я какбэ сомневаюсь, но чтожжж...
				RTRIM(ISNULL(DrugPrep.DrugPrep_Name, '')) as DrugPrep_Name,
				DrugPrep.DrugPrep_id as DrugPrep_id,
				DrugPrep.DrugPrepFas_id as DrugPrepFas_id
			from
			    rls.v_DrugPrep DrugPrep with (nolock)
				--inner join rls.v_Drug D with (nolock) on D.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
        ";

		$query = "
			{$base_query}
			    {$join}
			where
			    {$filter}
				{$exists_filter}
		";

        if (!empty($id_filter)) { //если задан фильтр по идентификатору медикамента
            $query2 = "
                {$base_query}
                where
                    {$id_filter}
            ";
            $result = $this->db->query($query2, $queryParams);
			if ( is_object($result) ) {
				$result = $result->result('array');
				if(count($result) > 0){
					return $result;
				}
			} else {
				return false;
			}
        }

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if(count($result) == 1){
				$data['DrugPrepFas_id'] = $result[0]['DrugPrepFas_id'];
				$res = $this->loadDrugList($data);
				if(is_array($res) && count($res) == 1) {
					$data['mode'] = 'default';
					$data['Drug_id'] = $res[0]['Drug_id'];
					$data['is_personal'] = 1;
					$data['DocumentUc_id'] = null;
					$data['DocumentUcStr_id'] = null;
					$data['Contragent_id'] = null;
					$res2 = $this->loadDocumentUcStrList($data);
					if(is_array($res2) && count($res2) == 1) {
						$result[1] = $res[0];
						$result[2] = $res2[0];
						return $result;
					} else {
						return $result;
					}
				} else {
					return $result;
				}
			} else {
				return $result;
			}
			
		} else {
			return false;
		}
	}
	
	/**
	 *  Функция возвращает список медикаментов с постраничным выводом
	 *  Используется на форме поиска медикаментов
	 */
	function loadDrugMultiList($data) {
		$queryParams = array();
		$filter = "(1=1) ";
		$filter_all = "(1=1) ";
		$join = "left join rls.v_DrugComplexMnn dMnn (nolock) on dMnn.DrugComplexMnn_id=Drug.DrugComplexMnn_id 
				outer apply (select top 1 isnull(gpc.GoodsUnit_id,57) as GoodsUnit_id from v_GoodsPackCount gpc with (nolock) where gpc.DrugComplexMnn_id = dMnn.DrugComplexMnn_id ) gu
			";
		

		// Фильтрация по первому кобмбобоксу
		if ($data['DrugPrepFas_id']>0)
		{
			$filter_all .= "and Drug.DrugPrepFas_id = :DrugPrepFas_id ";
			$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
		}
		
		
		switch ( $data['mode'] ) {
			case 'ostat':
				$join .= "inner join v_DrugOstatRegistry DOR (nolock) on Drug.Drug_id = DOR.Drug_id
					";
				if (!empty($data['LpuSection_id'])) {
					$filter .= " and DOR.Storage_id in (
						select Storage_id
						from v_StorageStructLevel with(nolock)
						where LpuSection_id = :LpuSection_id
					)";
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				if (!empty($data['Storage_id'])) {
					$filter .= "and DOR.Storage_id = :Storage_id ";
					$queryParams['Storage_id'] = $data['Storage_id'];
				}

				$query = "
					select
						-- select
						RTRIM(ISNULL(Drug.DrugTorg_Name, '')) as DrugTorg_Name,
						Drug.Drug_id as Drug_id,
						Drug.DrugPrepFas_id as DrugPrepFas_id,
						Drug.Drug_Nomen,
						RTRIM(ISNULL(Drug.Drug_Nomen, '')) as Drug_Name,
						RTRIM(ISNULL(Drug.DrugForm_Name, '')) as DrugForm_Name,
						Drug.Drug_Dose as Drug_Dose,
						Drug.Drug_Fas as Drug_Fas,
						Drug.Drug_PackName as Drug_PackName,
						Drug.Drug_Firm as Drug_Firm,
						Drug.Drug_Ean as Drug_Ean,
						Drug.Drug_RegNum as Drug_RegNum,
						dMnn.DrugComplexMnn_RusName as DrugMnn,
						gu.GoodsUnit_id
						-- end select
					from
						-- from
						rls.v_Drug Drug with (nolock)
						{$join}
						-- end from
					where
						-- where
						{$filter} and {$filter_all} --and DUO.DocumentUcStr_Ost > 0
				";
				break;

			case 'expenditure':
				$join .= "inner join v_DocumentUcOst_Lite DUO (nolock) on Drug.Drug_id = DUO.Drug_id 
					";
				if ($data['Contragent_id']>0)
				{
					$filter .= "and DUO.Contragent_tid = :Contragent_id ";
					$queryParams['Contragent_id'] = $data['Contragent_id'];
				}
				
				if ($data['LpuSection_id']>0)
				{
					$filter .= "and C.LpuSection_id = :LpuSection_id ";
					$filter .= " and ost.cnt > 0 ";
					$join .= "inner join Contragent C with(nolock) on C.Contragent_id = DUO.Contragent_tid ";
					$join .= " outer apply (
						select isnull(sum(DocumentUcStr_Ost),0) as cnt 
						from v_DocumentUcOst_Lite DUOL (nolock)
						left join Contragent TC with (nolock) on TC.Contragent_id = DUOL.Contragent_tid
						where Drug_id = Drug.Drug_id and TC.LpuSection_id = :LpuSection_id
					) as ost";
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				$query = "
					select 
						-- select
						RTRIM(ISNULL(Drug.DrugTorg_Name, '')) as DrugTorg_Name,
						Drug.Drug_id as Drug_id,
						Drug.DrugPrepFas_id as DrugPrepFas_id,
						Drug.Drug_Nomen,
						RTRIM(ISNULL(Drug.Drug_Nomen, '')) as Drug_Name,
						RTRIM(ISNULL(Drug.DrugForm_Name, '')) as DrugForm_Name,
						Drug.Drug_Dose as Drug_Dose,
						Drug.Drug_Fas as Drug_Fas,
						Drug.Drug_PackName as Drug_PackName,
						Drug.Drug_Firm as Drug_Firm,
						Drug.Drug_Ean as Drug_Ean,
						Drug.Drug_RegNum as Drug_RegNum,
						dMnn.DrugComplexMnn_RusName as DrugMnn,
						gu.GoodsUnit_id
						-- end select
					from 
						-- from 
						rls.v_Drug Drug with (nolock)
						{$join}
						-- end from
					where 
						-- where
						{$filter} and {$filter_all} --and DUO.DocumentUcStr_Ost > 0
				";
			break;

			case 'income':
				$query = "
					select -- здесь был top 50, но я его убрал, согласно задаче # 
						-- select
						RTRIM(ISNULL(Drug.DrugTorg_Name, '')) as DrugTorg_Name,
						Drug.Drug_id as Drug_id,
						Drug.DrugPrepFas_id as DrugPrepFas_id,
						Drug.Drug_Nomen,
						RTRIM(ISNULL(Drug.Drug_Nomen, '')) as Drug_Name,
						RTRIM(ISNULL(Drug.DrugForm_Name, '')) as DrugForm_Name,
						Drug.Drug_Dose as Drug_Dose,
						Drug.Drug_Fas as Drug_Fas,
						Drug.Drug_PackName as Drug_PackName,
						Drug.Drug_Firm as Drug_Firm,
						Drug.Drug_Ean as Drug_Ean,
						Drug.Drug_RegNum as Drug_RegNum,
						dMnn.DrugComplexMnn_RusName as DrugMnn,
						gu.GoodsUnit_id
						-- end select
					from 
						-- from 
						rls.v_Drug Drug with (nolock)
						{$join}
						-- end from
					where 
						-- where 
						{$filter_all}
				";
			break;
		}
		
		/*
		if ( isset($data['Drug_id']) ) {
			$query .= " and Drug.Drug_id = :Drug_id";
			$queryParams['Drug_id'] = $data['Drug_id'];
		}
		*/
		// todo: Условие я поправил чтобы оно быстрее работало, но оно неправильное изначально, поскольку при переходе по страницам и наличии distinct будет выходить разная ерунда, его нужно переделывать когда придет время
		// А пока за почти три года никто не обратил внимание что она не правильно возвращает данные постранично
		if ( isset($data['DrugTorg_Name']) ) {
			$query .= " and (Drug.DrugTorg_Name like :DrugTorg_Name or 
			exists(Select top 1 1 from rls.v_DrugComplexMnn dMnn (nolock) where dMnn.DrugComplexMnn_id=Drug.DrugComplexMnn_id and dMnn.DrugComplexMnn_RusName like :DrugTorg_Name)
			)";
			$queryParams['DrugTorg_Name'] = "%".$data['DrugTorg_Name'] . "%";
		}
		
		if ( isset($data['DrugForm_Name']) ) {
			$query .= " and Drug.DrugForm_Name like :DrugForm_Name";
			$queryParams['DrugForm_Name'] = "%".$data['DrugForm_Name'] . "%";
		}
		
		if ( isset($data['Drug_PackName']) ) {
			$query .= " and Drug.Drug_PackName like :Drug_PackName";
			$queryParams['Drug_PackName'] = "%".$data['Drug_PackName'] . "%";
		}
		
		if ( isset($data['Drug_Dose']) ) {
			$query .= " and Drug.Drug_Dose like :Drug_Dose";
			$queryParams['Drug_Dose'] = "%".$data['Drug_Dose'] . "%";
		}
		
		if ( isset($data['Drug_Firm']) ) {
			$query .= " and Drug.Drug_Firm like :Drug_Firm";
			$queryParams['Drug_Firm'] = "%".$data['Drug_Firm'] . "%";
		}
		
		$query .= "
						-- end where
					order by 
					-- order by 
					Drug.Drug_Nomen
					-- end order by 	
		";
		/*
					order by 
					-- order by 
					Drug.Drug_Name
					-- end order by 
		*/
		//echo getDebugSql($query, $queryParams);die;

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct'), $queryParams);
		$result_count = $this->db->query(getCountSQLPH($query, 'Drug.Drug_id', 'distinct'), $queryParams);

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
			return $response;
		}
		else
		{
			return false;
		}
		return $response;
	}

	/**
	 *  Функция
	 */
	function loadContragentView($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}
		$mode = isset($data['mode']) && !empty($data['mode']) ? $data['mode'] : null;
		$params = array();
		$filter = "(1=1)";
		$filter .= " and (OrgFarmacy.Region_id is null or OrgFarmacy.Region_id = dbo.GetRegion())";

		if (!empty($data['ContragentOrg_Org_id'])) {
			$filter .= " and ContragentOrg.Org_id = :ContragentOrg_Org_id";

			if ($data['ContragentOrg_Org_id'] == 'minzdrav') {
				$mzorg_id = $this->getFirstResultFromQuery("select dbo.GetMinzdravDloOrgId() as Org_id");
				$params['ContragentOrg_Org_id'] = $mzorg_id;
			} else {
				$params['ContragentOrg_Org_id'] = $data['ContragentOrg_Org_id'];
			}
		} else {
			if (!empty($data['Lpu_id']) && $data['Lpu_id'] > 0) {
				//Связка контрагентов и организаций создается сейчас только в МО
				$filter .= " and ContragentOrg.Org_id = :Org_id";
				$params['Org_id'] = isset($data['session']['org_id']) ? $data['session']['org_id'] : null;
			}
		}

		$wm_org_filter = "";
		if ($mode == "all_without_lpu") {
			//$wm_org_filter = " or (Contragent.Org_pid is null and (Contragent.Lpu_id is null or Contragent.Lpu_id = 0))";
		}

		if ($mode == "all_without_lpu") {
			$filter .= " and (isnull(Contragent.Lpu_id, 0) = 0)";
		} else {
			// Все свои и также РАС и пациент
			if ( isset($data['session']['isFarmacyNetAdmin']) && $data['session']['isFarmacyNetAdmin'] === true ) {
				$filter = $filter." and (Contragent.Org_pid = :Org_pid or Contragent.Org_id = :OrgNet_id or IsNull(Contragent.Org_pid, 200)=200)";
				$params['OrgNet_id'] = $data['session']['OrgNet_id'];
			} else {
				if  (isset($data['session']['OrgFarmacy_id'])) {
					$filter = $filter." and Contragent.ContragentType_id <> 2"; //для аптек не выводим отделения в контрагентах
					if (isset($data['session']['Org_pid']))
					{
						$filter = $filter." and (Contragent.Org_pid = :Org_pid or IsNull(Contragent.Org_pid, 200)=200)";
						$params['Org_pid'] = $data['session']['Org_pid'];
					}
					else if (isset($data['session']['org_id'])) //добавил условие чтоыб при отсутсвии в сесии org_id не дохло все
					{
						$filter = $filter." and (Contragent.Org_pid = :Org_id)";
						$params['Org_id'] = $data['session']['org_id'];
					}
				} /*elseif (isset($data['session']['lpu_id']) && $data['session']['lpu_id'] > 0) {
					$filter = $filter." and (Contragent.Lpu_id = :Lpu_id)";
					$params['Lpu_id'] = $data['session']['lpu_id'];
				} else {
					$filter = $filter." and (Contragent.Lpu_id is null or Contragent.Lpu_id = 0)";
				}*/
			}
		}

		if (ArrayVal($data,'ContragentType_id') > 0) {
			$filter .= " and Contragent.ContragentType_id = :ContragentType_id";
			$params['ContragentType_id'] = $data['ContragentType_id'];
		}

		if (ArrayVal($data,'Contragent_aid') > 0) {
			$filter .= " and Contragent.Contragent_id = :Contragent_aid";
			$params['Contragent_aid'] = $data['Contragent_aid'];
		}

		$query = "
			Select 
				Contragent.Contragent_id,
				Contragent.ContragentType_id,
				RTrim(ContragentType.ContragentType_Name) as ContragentType_Name,
				Contragent.Lpu_id,
				Contragent.Org_id,
				OrgType.OrgType_SysNick,
				Org.Server_id as OrgServer_id,
				Contragent.Org_pid,
				Contragent.OrgFarmacy_id,
				Contragent.LpuSection_id,
				Contragent.Contragent_Code,
				RTrim(Contragent.Contragent_Name) as Contragent_Name,
				convert(varchar(10), isnull(ContragentExpDates.BegDate, Contragent.Contragent_insDT), 104) BegDate,
				convert(varchar(10), ContragentExpDates.EndDate, 104) EndDate
			from v_Contragent Contragent with(nolock)
			inner join v_ContragentOrg ContragentOrg with(nolock) on ContragentOrg.Contragent_id = Contragent.Contragent_id
			left join v_ContragentType ContragentType with(nolock) on ContragentType.ContragentType_id = Contragent.ContragentType_id
			left join v_Org Org with(nolock) on Org.Org_id = Contragent.Org_id
			left join v_OrgType OrgType with(nolock) on OrgType.OrgType_id = Org.OrgType_id
			left join OrgFarmacy OrgFarmacy with(nolock) on OrgFarmacy.OrgFarmacy_id = Contragent.OrgFarmacy_id
			left join v_LpuSection LpuSection with(nolock) on LpuSection.LpuSection_id = Contragent.LpuSection_id
			outer apply dbo.GetContragentExpDates(Contragent.Contragent_id,Contragent.ContragentType_id) ContragentExpDates
			where
				{$filter}
			order by Contragent_Code, Contragent_Name
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit']);
	}

	/**
	 *  Функция
	 */
	function loadContragentEdit($data) {
		$isFarmacy = (isset($data['session']['OrgFarmacy_id']));
		$params = array();
		$filter = "(1=1)";
		if (!$isFarmacy) {
			$params['Org_id'] = isset($data['session']['org_id'])?$data['session']['org_id']:null;
			if ( !empty($data['Lpu_id']) && $data['Lpu_id'] > 0 ) {
				//Связка контрагентов и организаций создается сейчас только в МО
				$filter .= " and ContragentOrg.Org_id = :Org_id";
			}
		
			//$filter = $filter." and (Contragent.Lpu_id = isnull(:Lpu_id, 0) or Contragent.Lpu_id is null)";
			//$params['Lpu_id'] = $data['session']['lpu_id'];
		}

		if ((isset($data['Contragent_id'])) && ($data['Contragent_id']>0)) {
			$filter = $filter." and Contragent.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		}
		
		$query = "
			Select 
				Contragent.Contragent_id,
				Contragent.ContragentType_id,
				Org.Org_id,
				Contragent.OrgFarmacy_id,
				Contragent.LpuSection_id,
				Contragent.MedService_id,
				Contragent.Contragent_Code,
				RTrim(Contragent.Contragent_Name) as Contragent_Name
			from Contragent Contragent with(nolock)
			left join v_ContragentOrg ContragentOrg with(nolock) on ContragentOrg.Contragent_id = Contragent.Contragent_id
			left join v_Lpu_all Lpu with(nolock) on Lpu.Lpu_id = Contragent.Lpu_id and Contragent.ContragentType_id = 5
			left join v_Org Org with(nolock) on Org.Org_id = isnull(Contragent.Org_id,Lpu.Org_id)
			left join v_OrgFarmacy OrgFarmacy with(nolock) on OrgFarmacy.OrgFarmacy_id = Contragent.OrgFarmacy_id
			where 
				{$filter}
			order by Contragent.Lpu_id, Contragent_Name
		";
		$result = $this->db->query($query, $params);
		
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
	 *  Функция
	 */
	function loadContragentList($data) {
		$params = array();
		$filter = "(1=1)";
		$params['Org_id'] = isset($data['session']['org_id'])?$data['session']['org_id']:null;

		$Contragent_Name = 'RTrim(Contragent.Contragent_Name)';

		if (!empty($data['mode']) && $data['mode'] == 'punktotp') {
			$filter .= " and Contragent.Contragent_id in (
				select
					wdrr.Contragent_id
				from
					v_WhsDocumentRightRecipient wdrr (nolock) -- контрагенты
					inner join v_WhsDocumentTitle wdt (nolock) on wdt.WhsDocumentTitle_id = wdrr.WhsDocumentTitle_id -- документ приложение к ГК
					inner join v_WhsDocumentSupply wds (nolock) on wds.WhsDocumentUc_id = wdt.WhsDocumentUc_id -- контракт
				where
					WhsDocumentTitleType_id = 3 -- Приложение к ГК: список пунктов отпуска
					and wds.Org_sid = :Org_id -- Поставщик в контракте
					and wds.WhsDocumentType_id = 6 -- Контракт на поставку и отпуск
			)";
			$Contragent_Name = 'Org.Org_Name';
		} else if ( !empty($data['Lpu_id']) && $data['Lpu_id'] > 0 ) {
			//Связка контрагентов и организаций создается сейчас только в МО
			$filter .= " and (ContragentOrg.Org_id = :Org_id or Contragent.ContragentType_id = 4)";
		}
		if( !empty($data['ContragentType_id']) ) {
			$filter .= " and Contragent.ContragentType_id = :ContragentType_id";
			$params['ContragentType_id'] = $data['ContragentType_id'];
		}
		if( !empty($data['ContragentType_CodeList']) ) {
			$filter .= " and ContragentType.ContragentType_Code in ({$data['ContragentType_CodeList']})";
		}
		if ( !empty($data['query']) ) {
			$filter .= " and (Contragent.Contragent_Name like '%'+:query+'%' or Contragent.Contragent_Code like :query+'%')";
			$params['query'] = $data['query'];
		}
		
		if (isset($data['session']['OrgFarmacy_id'])) { //для аптек
			$filter .= " and Contragent.ContragentType_id != 2 "; //аптека не должна видеть отделений
			$params['Lpu_id'] = $data['Lpu_id'];
			
			switch ($data['mode']) {
				case 'receiver': // Потребитель
					// $filter .= " and Contragent.ContragentType_id not in (1,3)";
					break;
				case 'sender': // Поставщик
					$filter = $filter." and Contragent.ContragentType_id != 4 ";
					break;
				case 'med_ost': //режим своего лпу, для списания/передачи остатков медикаментов
					$filter .= " and Contragent.ContragentType_id <> 1";
					break;
				default:
					break;
			}
		} else { //для ЛПУ
			
			
			$params['Lpu_id'] = $data['Lpu_id'];
			
			// если указан определенный контрагент, то его и выбираем 
			if ((isset($data['Contragent_id'])) && ($data['Contragent_id']>0)) {
				//$filter .= " and Contragent.Contragent_id = :Contragent_id";
				$params['Contragent_id'] = $data['Contragent_id'];
			}
			
			switch ($data['mode']) {
				case 'receiver': // Потребитель
					if ($data['Contragent_id']>0) {
						$filter .= " and Contragent.ContragentType_id not in (1,3)"; // не орг и не аптека
					} else {
						$filter .= " and Contragent.ContragentType_id not in (1,3,4)"; // и не пациент
					}
				break;
				case 'sender': // Поставщик
					$filter .= " and Contragent.ContragentType_id != 4 "; // и точно не больной
					break;
				case 'self_lpu': //режим своего лпу, для документов списания и ввода остатков
					//$filter .= " and (Contragent.Lpu_id = :Lpu_id)"; // для поставщиков ЛПУ - только свои контрагенты
					break;
				case 'med_ost': //режим своего лпу, для списания/передачи остатков медикаментов
					$filter .= " and Contragent.ContragentType_id <> 1";
					break;	
				default:
					break;
			}
		}

		//строгая фильтрация по идентификатору
		if (isset($data['FilterContragent_id']) && $data['FilterContragent_id'] > 0) {
			$filter = "Contragent.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['FilterContragent_id'];
		}
		if (isset($data['FilterOrg_id']) && $data['FilterOrg_id'] > 0) {
			$filter = "Contragent.Org_id = :FilterOrg_id";
			$params['FilterOrg_id'] = $data['FilterOrg_id'];
		}

		$query = "
			select distinct
				Contragent.Contragent_id,
				Contragent.ContragentType_id,
				ContragentType.ContragentType_Code,
				Contragent.Contragent_Code,
				{$Contragent_Name} as Contragent_Name,
				Contragent.Org_id,
				Contragent.OrgFarmacy_id,
				Contragent.Lpu_id,
				Contragent.LpuSection_id,
				Contragent.MedService_id,
				OrgType.OrgType_SysNick
			from
				v_Contragent Contragent with (nolock)
				left join v_ContragentOrg ContragentOrg with(nolock) on ContragentOrg.Contragent_id = Contragent.Contragent_id
				left join v_ContragentType ContragentType with (nolock) on ContragentType.ContragentType_id = Contragent.ContragentType_id
				left join v_Org Org with (nolock) on Org.Org_id = Contragent.Org_id
				left join v_OrgType OrgType with (nolock) on OrgType.OrgType_id = Org.OrgType_id
			where 
				{$filter}
			order by Contragent.ContragentType_id, Contragent_Name; --Contragent.Contragent_Code
		";

		// echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Функция loadDrugFinanceList
	 */
	function loadDrugFinanceListOld($data) {
		// todo: Поскольку в текущем виде и при текущей реализации получения остатков через v_DocumentUcOst_Lite
		// этот функционал нормально работать не будет, поэтому закрываю до лучших времен
		return false;
		
        $params = array();
        $filter = "(1=1)";
        //var_dump($data);
        if ((isset($data['Contragent_id'])) && ($data['Contragent_id']>0)) {
            $filter .= " and Contragent_tid = :Contragent_id and DocumentUcStr_Ost > 0";
            $params['Contragent_id'] = $data['Contragent_id'];
        }

        $query = "
			select distinct
                DrugFinance_id
            from
                v_DocumentUcOst_Lite with (nolock)
            where
                {$filter}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
	/**
	 *  Функция
	 */
	function generateMolCode($data) {

		$params = array();
		$filter = "(1=1)";
		
		if (isset($data['Lpu_id']))
		{
			$filter = $filter." and C.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!isset($data['Lpu_id']))
		{
			$filter = $filter." and C.Lpu_id is null";
		}
		$query = "
			Select 
				IsNull(Max(Mol_Code),10)+1 as Mol_Code
			from v_Mol Mol with(nolock)
			left join v_Contragent  C with(nolock) on C.Contragent_id = Mol.Contragent_id
			where 
				{$filter}
		";
		
		$result = $this->db->query($query, $params);
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
	 *  Функция
	 */
	function generateContragentCode($data) {
		$params = array();

		$query = "
			select
				IsNull(Max(Contragent_Code),10)+1 as Contragent_Code
			from
				v_Contragent with (nolock)
		";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Функция
	 */
	function loadMolView($data) {

		$params = array();
		$filter = "(1=1)";
		
		if (isset($data['Mol_id']))
		{
			$filter = $filter." and Mol.Mol_id = :Mol_id";
			$params['Mol_id'] = $data['Mol_id'];
		} elseif (isset($data['Contragent_id']))
		{
			$filter = $filter." and Mol.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		}
		
		if ((!isset($data['Mol_id'])) && (!isset($data['Contragent_id'])))
		{
			return false;
		}
		$query = "
			Select 
				Mol.Mol_id,
				Mol.Person_id,
				Mol.MedPersonal_id,
				Mol.MedStaffFact_id,
				RTrim(Mol.Person_SurName)+' '+RTrim(Mol.Person_FirName)+' '+IsNull(RTrim(Mol.Person_SecName)+' ', '') as Person_FIO,
				RTrim(Mol.Person_SurName) as Person_SurName,
				RTrim(Mol.Person_FirName) as Person_FirName,
				RTrim(Mol.Person_SecName) as Person_SecName,
				convert(varchar(10), Mol.Mol_begDT, 104) Mol_begDT,
				convert(varchar(10), Mol.Mol_endDT, 104) Mol_endDT,
				Mol.Storage_id,
				Mol.Contragent_id,
				Mol.Mol_Code
			from v_Mol Mol with (nolock)
			where 
				{$filter}
			order by Mol_Code desc
		";
		
		$result = $this->db->query($query, $params);
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
	 *  Функция
	 */
	function loadDrugLabResult($data) {
		$params = array();
		$filter = "(1=1) and DocUcStr.DrugLabResult_Name is not null";
		
		if (isset($data['Contragent_id']))
		{
			$filter = $filter." and D.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		}
		
		$query = "
			Select 
				RTrim(DrugLabResult_Name) as DrugLabResult_Name
			from v_DocumentUcStr DocUcStr with (nolock)
			inner join DocumentUc D with (nolock) on D.DocumentUc_id = DocUcStr.DocumentUc_id
			where 
				{$filter}
			group by DrugLabResult_Name
			order by DrugLabResult_Name
		";
		
		$result = $this->db->query($query, $params);
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
	 * Сохранение записи о контрагенте
	 */
	function saveContragentData($data) {
		if (!isset($data['Contragent_id'])) {
			$proc = 'p_Contragent_ins';
		} else {
			$proc = 'p_Contragent_upd';
		}

		$query = "
			Declare @Contragent_id bigint = :Contragent_id;
			Declare @Error_Code bigint = 0;
			Declare @Error_Message varchar(4000) = '';
			exec {$proc}
			@Server_id = :Server_id,
			@Contragent_id = @Contragent_id output,
			@Lpu_id = :Lpu_id,
			@ContragentType_id = :ContragentType_id,
			@Contragent_Code = :Contragent_Code,
			@Contragent_Name = :Contragent_Name,
			@Org_id = :Org_id,
			@OrgFarmacy_id = :OrgFarmacy_id,
			@LpuSection_id = :LpuSection_id,
			@MedService_id = :MedService_id,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
			select @Contragent_id as Contragent_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		$params = array(
			'Contragent_id' => $data['Contragent_id'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:null,
			'ContragentType_id' => $data['ContragentType_id'],
			'Contragent_Code' => $data['Contragent_Code'],
			'Contragent_Name' => $data['Contragent_Name'],
			'Org_id' => $data['Org_id'],
			'OrgFarmacy_id' => !empty($data['OrgFarmacy_id'])?$data['OrgFarmacy_id']:null,
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'MedService_id' => !empty($data['MedService_id'])?$data['MedService_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 *  Сохранение записи о контрагенте и связи контрагента с организацией
	 */
	function saveContragent($data) {
		if (isset($data['session']['OrgFarmacy_id'])) {
			$data['LpuSection_id'] = Null;
		}

		$ContragentType_Code = $this->getFirstResultFromQuery("
			select top 1 ContragentType_Code
			from v_ContragentType with(nolock)
			where ContragentType_id = :ContragentType_id
		", $data);
		if (!$ContragentType_Code) {
			$this->rollbackTransaction();
			return $this->createError(400,'Ошибка при получении кода типа контрагента');
		}

		if (empty($data['Org_id']) && !empty($data['Contragent_id'])) {
			$query = "
				select top 1 c.Org_id
				from v_Contragent c with (nolock)
				where c.Contragent_id = :Contragent_id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'Contragent_id' => $data['Contragent_id']
			));

			if ($result && !empty($result['Org_id'])) {
				$data['Org_id'] = $result['Org_id'];
			}
		}

		if (empty($data['Org_id']) && (!empty($data['session']['lpu_id']) || !empty($data['LpuSection_id']))) {
			$query = "
				select
					l.Org_id
				from
					Lpu l with (nolock)
				where
					l.Lpu_id = :Lpu_id or
					(
						:LpuSection_id is not null and
						l.lpu_id in (
							select
								ls.Lpu_id
							from
								v_LpuSection ls with (nolock)
							where
								ls.LpuSection_id = :LpuSection_id
						)
					);
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'Lpu_id' => $data['session']['lpu_id'] > 0 ? $data['session']['lpu_id'] : null,
				'LpuSection_id' => $data['LpuSection_id']
			));

			if ($result && !empty($result['Org_id'])) {
				$data['Org_id'] = $result['Org_id'];
			}
		}

		$data['Lpu_id'] = null;
		if (($ContragentType_Code == 5 || $ContragentType_Code == 2) && !empty($data['Org_id'])) {
			$query = "
				select top 1 Lpu_id
				from v_Lpu_all with (nolock)
				where Org_id = :Org_id
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'Org_id' => $data['Org_id']
			));
			if ($result && !empty($result['Lpu_id'])) {
				$data['Lpu_id'] = $result['Lpu_id'];
			}
		}

		if (!empty($data['Contragent_id']) || empty($data['session']['lpu_id'])) {
			//Если редактируется контрагент или организация не МО
			return $this->saveContragentData(array(
				'Contragent_id' => $data['Contragent_id'],
				'Lpu_id' => $data['Lpu_id'],
				'ContragentType_id' => $data['ContragentType_id'],
				'Contragent_Code' => $data['Contragent_Code'],
				'Contragent_Name' => $data['Contragent_Name'],
				'Org_id' => $data['Org_id'],
				'OrgFarmacy_id' => $data['OrgFarmacy_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'MedService_id' => $data['MedService_id'],
				'Server_id' => $data['session']['server_id'],
				'pmUser_id' => $data['session']['pmuser_id']
			));
		} else {
			$this->beginTransaction();

			if ($ContragentType_Code != 2) {
				//На организации должен быть только 1 контрагент с указанным типом, за исключением отделений
				$result = $this->getFirstRowFromQuery("
					select top 1 Contragent_id
					from v_Contragent C with(nolock)
					where ContragentType_id = :ContragentType_id and Org_id = :Org_id
				", $data);
				if ($result && !empty($result['Contragent_id'])) {
					$data['Contragent_id'] = $result['Contragent_id'];
				}
			}

			$response = $this->saveContragentData(array(
				'Contragent_id' => $data['Contragent_id'],
				'Lpu_id' => $data['Lpu_id'],
				'ContragentType_id' => $data['ContragentType_id'],
				'Contragent_Code' => $data['Contragent_Code'],
				'Contragent_Name' => $data['Contragent_Name'],
				'Org_id' => $data['Org_id'],
				'OrgFarmacy_id' => $data['OrgFarmacy_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'MedService_id' => $data['MedService_id'],
				'Server_id' => $data['session']['server_id'],
				'pmUser_id' => $data['session']['pmuser_id']
			));

			if (is_array($response) && !empty($response[0]['Contragent_id'])) {
				$data['Contragent_id'] = $response[0]['Contragent_id'];
			} else {
				$this->rollbackTransaction();
				return $response;
			}

			$count = $this->getFirstResultFromQuery("
				select top 1 count(MS.MedService_id) as Count
				from v_MedService MS with(nolock)
				inner join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
				where MS.Lpu_id = :Lpu_oid or MS.Org_id = :Org_oid and MST.MedServiceType_SysNick like 'adminllo'
			", array(
				'Lpu_oid' => isset($data['session']['lpu_id'])?$data['session']['lpu_id']:0,
				'Org_oid' => isset($data['session']['org_id'])?$data['session']['org_id']:0,
			));
			if ($count === false) {
				$this->rollbackTransaction();
				return $this->createError(400,'Ошибка при проверке службы администратора ЛЛО');
			}
			$isAdminLLO = ($count > 0);

			if (/*!isMinZdrav() && !$isAdminLLO &&*/ isset($data['session']['org_id'])) {
				//Добавляеися связь контрагента с организацией пользователя, если ещё нет
				$params = array(
					'Contragent_id' => $data['Contragent_id'],
					'Org_oid' => $data['session']['org_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				);
				$count = $this->getFirstResultFromQuery("
					select top 1 count(ContragentOrg_id) as Count
					from v_ContragentOrg with(nolock)
					where Contragent_id = :Contragent_id and Org_id = :Org_oid
				", $params);
				if ($count === false) {
					$this->rollbackTransaction();
					return $this->createError(400,'Ошибка при поиске связи контрагента и организации');
				}
				if ($count == 0) {
					$query = "
						declare
							@ContragentOrg_id bigint,
							@Error_Code bigint,
							@Error_Message varchar(4000);
						exec p_ContragentOrg_ins
							@ContragentOrg_id = @ContragentOrg_id output,
							@Contragent_id = :Contragent_id,
							@Org_id = :Org_oid,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						select @ContragentOrg_id as ContragentOrg_id, @Error_Code as Error_Code, @Error_Message as Error_Message
					";
					$result = $this->queryResult($query, $params);
					if (!is_array($result)) {
						$this->rollbackTransaction();
						return $this->createError(400,'Ошибка при сохранении связи контрагента и организации');
					}
					if (!empty($result[0]['Error_Message'])) {
						$this->rollbackTransaction();
						return $result;
					}
				}
			}

			$this->commitTransaction();
			return $response;
		}
	}

	/**
	 * Удаление контрагента
	 */
	function deleteContragent($data) {
		$params = array('Contragent_id' => $data['Contragent_id']);

		if (!empty($data['session']['lpu_id'])) {
			$result = $this->queryResult("
				select top 1 ContragentOrg_id
				from v_ContragentOrg with(nolock)
				where Contragent_id = :Contragent_id and Org_id = :Org_id
			", array(
				'Contragent_id' => $params['Contragent_id'],
				'Org_id' => $data['session']['org_id']
			));
			if (!is_array($result)) {
				return $this->createError('', 'Ошибка при поиске связи контрагента и организации');
			}
			if (count($result) == 0) {
				return $this->createError('', 'Не найдена связь контрагента и текущей организации');
			}

			$data['ContragentOrg_id'] = $result[0]['ContragentOrg_id'];
			$response = $this->deleteContragentOrg($data);
			if(!empty($response[0]['Error_Msg']) || !empty($response['Error_Msg'])){
				return $response;
			}

			$result = $this->queryResult("
				select top 1 Contragent_id
				from v_Contragent with(nolock)
				where Contragent_id = :Contragent_id and Lpu_id = :Lpu_id
			", array(
				'Contragent_id' => $params['Contragent_id'],
				'Lpu_id' => $data['session']['lpu_id']
			));
			if (!is_array($result)) {
				return $this->createError('', 'Ошибка при получении данных о контрагенте');
			}
			if (!empty($result[0]['Contragent_id'])) {
				$result = $this->queryResult("
					select top 1 ContragentOrg_id
					from v_ContragentOrg with(nolock)
					where Contragent_id = :Contragent_id
				", array(
					'Contragent_id' => $params['Contragent_id']
				));
				if (!is_array($result)) {
					return $this->createError('', 'Ошибка при поиске связи контрагента и организации');
				}
				if (count($result) == 0) {
					$query = "
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000);
						exec p_Contragent_del
							@Contragent_id = :Contragent_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						select @Error_Code as Error_Code, @Error_Message as Error_Message
					";

					$result = $this->queryResult($query, $params);
					if ($result && !empty($result[0]['Error_Code']) && $result[0]['Error_Code'] == 547) {
						return $this->createError('', 'Удаление невозможно, т.к. существуют объекты в БД, ссылающиеся на удаляемую запись');
					}
					return $result;
				} else {
					return $response;
				}
			} else {
				return $response;
			}

		} else {
			return $this->createError('', 'Операция доступна только в организациях с типом МО');
		}
	}

	/**
	 * Удаление связи контрагента с организацией
	 */
	function deleteContragentOrg($data) {
		$params = array('ContragentOrg_id' => $data['ContragentOrg_id']);

		$ContragentOrg = $this->getFirstRowFromQuery("
			select top 1
				CO.Contragent_id,	--Контрагент, связь с которым удаляется
				CO.Org_id			--Организация пользователя
			from v_ContragentOrg CO with(nolock)
			where CO.ContragentOrg_id = :ContragentOrg_id
		", $params);
		if ($ContragentOrg === false) {
			return $this->createError('', 'Ошибка при поиске связи контрагента и организации');
		}
		if ($ContragentOrg['Org_id'] != $data['session']['org_id']) {
			return $this->createError('', 'Разрешено удалать связь только собственной организации с контрагентом');
		}

		$count = $this->getFirstResultFromQuery("
			declare
				@Org_id bigint = :Org_id,
				@Contragent_id bigint = :Contragent_id
			select top 1
				Doc.Count+WhsDoc.Count as Count
			from (
				select top 1 count(DU.DocumentUc_id) as Count
				from v_DocumentUc DU with(nolock)
				where DU.Org_id = @Org_id and @Contragent_id in (DU.Contragent_sid, DU.Contragent_tid)
			) as Doc, (
				select top 1 count(WDS.WhsDocumentSupply_id) as Count
				from v_WhsDocumentSupply WDS with(nolock)
				where WDS.Org_aid = @Org_id and WDS.Org_sid = (select top 1 Org_id from v_Contragent with(nolock) where Contragent_id = @Contragent_id)
			) as WhsDoc
		", $ContragentOrg);
		if ($count === false) {
			return $this->createError('', 'Ошибка при поиске докуметов, в которых указан контрагент');
		}
		if ($count > 0) {
			return $this->createError('', 'Удаление невозможно. Существуют документы, в которых указан данный контрагент');
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_ContragentOrg_del
				@ContragentOrg_id = :ContragentOrg_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Проверка наличия контрагента в документах
	 */
	function checkContragentOrgInDocs($data) {
		$params = array('Contragent_id' => $data['Contragent_id'],'Org_id' => $data['session']['org_id']);

		$count = $this->getFirstResultFromQuery("
			declare
				@Org_id bigint = :Org_id,
				@Contragent_id bigint = :Contragent_id
			select top 1
				Doc.Count+WhsDoc.Count as Count
			from (
				select top 1 count(DU.DocumentUc_id) as Count
				from v_DocumentUc DU with(nolock)
				where DU.Org_id = @Org_id and @Contragent_id in (DU.Contragent_sid, DU.Contragent_tid)
			) as Doc, (
				select top 1 count(WDS.WhsDocumentSupply_id) as Count
				from v_WhsDocumentSupply WDS with(nolock)
				where WDS.Org_aid = @Org_id and WDS.Org_sid = (select top 1 Org_id from v_Contragent with(nolock) where Contragent_id = @Contragent_id)
			) as WhsDoc
		", $params);
		if ($count === false) {
			return $this->createError('', 'Ошибка при поиске докуметов, в которых указан контрагент');
		}
		if ($count > 0) {
			return $this->createError('', 'Удаление невозможно. Существуют документы, в которых указан данный контрагент');
		}

		return array();
	}

	/**
	 *  Функция
	 */
	function saveMol($data)	{

		if (!isset($data['Mol_id']))
		{
			$proc = 'p_Mol_ins';
		}
		else
		{
			$proc = 'p_Mol_upd';
		}

		$query = "
		Declare @Mol_id bigint = :Mol_id;
		Declare @Error_Code bigint = 0;
		Declare @Error_Message varchar(4000) = '';
		exec {$proc}
		@Server_id = :Server_id,
		@Mol_id = @Mol_id output,
		@Contragent_id = :Contragent_id,
		@MedPersonal_id = :MedPersonal_id,
		@MedStaffFact_id = :MedStaffFact_id,
		@Storage_id = :Storage_id,
		@Person_id = :Person_id,
		@Mol_Code = :Mol_Code,
		@Mol_begDT = :Mol_begDT,
		@Mol_endDT = :Mol_endDT,
		@pmUser_id = :pmUser_id,
		@Error_Code = @Error_Code output,
		@Error_Message = @Error_Message output;
		select @Mol_id as Mol_id, @Error_Code as Error_Code, @Error_Message as Error_Message;"; 
		
		$result = $this->db->query($query, array(
			'Mol_id' => $data['Mol_id'],
			'Server_id' => $data['Server_id'],
			'Contragent_id' => $data['Contragent_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Storage_id' => $data['Storage_id'],
			'Person_id' => $data['Person_id'],
			'Mol_Code' => $data['Mol_Code'],
			'Mol_begDT' => $data['Mol_begDT'],
			'Mol_endDT' => $data['Mol_endDT'],
			'pmUser_id' => $data['pmUser_id']
		));
		
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
	 *  Функция
	 */
	function saveDokNak($data) {
		if ($data['action']=='take') {
			$data['DrugDocumentType_id'] = 1;
			$data['DrugDocumentStatus_id'] = 2;
		} else {
			$data['DrugDocumentStatus_id'] = 3;
		}
		// Все делаем одним запросом 
		$query = "
			begin tran
			Declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@DocumentUc_id bigint =  :DocumentUc_id
			begin try
				set nocount on
				Update DocumentUc 
				set 
					DrugDocumentStatus_id = :DrugDocumentStatus_id,
					DocumentUc_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
					from DocumentUc DocUc
				where 
					DocUc.DocumentUc_id = @DocumentUc_id
				set nocount off;
				if (:action!='take')
					begin
						Select @DocumentUc_id as DocumentUc_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
					end
				else
					begin
						Declare @DocumentUc_pid bigint = :DocumentUc_pid;
						Set @DocumentUc_id = null;
						exec p_DocumentUc_cp
							@DocumentUc_id = @DocumentUc_id output,
							@DocumentUc_pid = @DocumentUc_pid,
							@DrugDocumentType_id = :DrugDocumentType_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						Select @DocumentUc_id as DocumentUc_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
					end;
				commit tran;
			end try
				begin catch
					rollback tran;
					set @Error_Code = error_number();
					set @Error_Message = error_message();
				end catch
			";

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'DocumentUc_pid' => $data['DocumentUc_id'],
			'DrugDocumentType_id' => $data['DrugDocumentType_id'],
			'DrugDocumentStatus_id' => $data['DrugDocumentStatus_id'],
			'action' => $data['action'],
			//'DrugFinance_id' => $data['DrugFinance_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_object($result))
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
		$r = $result->result('array');
		return $r;
		/* Так выглядело бы удаление , но его не надо :) 
			//data['DrugDocumentType_id'] = 4; // это пока .. а вообще здесь удаление должно быть 
			$this->load->database();
			$utils =& get_instance();
			$utils->load->model("Utils_model", "umodel", true);
			$umodel =& $utils->umodel;
			return true;
			
			//Пока закрыл
			$response = $umodel->ObjectRecordDelete($data, 'DocumentUc',$data['DocumentUc_id']);
			return $response;
			*/
	}

	/**
	 * Сохранение внутренней заявки
	 */
	function saveDocZayav($data)
	{
		$this->beginTransaction();

		$response = $this->saveDocumentUc($data);
		if (empty($response) || empty($response[0]) || !empty($response[0]['Error_Message'])) {
			$this->rollbackTransaction();
			if (!empty($response[0]['Error_Message'])) {
				return $response;
			}
			return false;
		}
		$data['DocumentUc_id'] = $response[0]['DocumentUc_id'];

		if ($data['changeStatus'] == 1) {
			$StatusHistory = $this->saveDrugDocumentStatusHistory($data);
			if (empty($StatusHistory) || empty($StatusHistory[0]) || !empty($StatusHistory[0]['Error_Message'])) {
				$this->rollbackTransaction();
				if (!empty($StatusHistory[0]['Error_Message'])) {
					return $StatusHistory;
				}
				return false;
			}
		}

		if ( !empty($data['DocumentUcStrData']) ) {
			$DocumentUcStrData = json_decode($data['DocumentUcStrData'], true);
			if ( is_array($DocumentUcStrData) ) {
				for ( $i = 0; $i < count($DocumentUcStrData); $i++ ) {
					if ( !isset($DocumentUcStrData[$i]['RecordStatus_Code']) || !in_array($DocumentUcStrData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$DocumentUcStr = array(
						'DocumentUc_id' => $data['DocumentUc_id'],
						'DocumentUcStr_id' => ($DocumentUcStrData[$i]['DocumentUcStr_id'] > 0) ? $DocumentUcStrData[$i]['DocumentUcStr_id'] : null,
						'Drug_id' => $DocumentUcStrData[$i]['Drug_id'],
						'Okei_id' => $DocumentUcStrData[$i]['Okei_id'],
						'DocumentUcStr_Count' => $DocumentUcStrData[$i]['DocumentUcStr_Count'],
						'DocumentUcStr_Price' => !empty($DocumentUcStrData[$i]['DocumentUcStr_Price']) ? $DocumentUcStrData[$i]['DocumentUcStr_Price'] : 0,
						'DocumentUcStr_Sum' => $DocumentUcStrData[$i]['DocumentUcStr_Sum'],
						'DocumentUcStr_PlanKolvo' => $DocumentUcStrData[$i]['DocumentUcStr_PlanKolvo'],
						'DocumentUcStr_PlanPrice' => !empty($DocumentUcStrData[$i]['DocumentUcStr_PlanPrice']) ? $DocumentUcStrData[$i]['DocumentUcStr_PlanPrice'] : 0,
						'DocumentUcStr_PlanSum' => $DocumentUcStrData[$i]['DocumentUcStr_PlanSum'],
						'Person_id' => $DocumentUcStrData[$i]['Person_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					switch ( $DocumentUcStrData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							//print_r($DocumentUcStr);exit;
							$queryResponse = $this->saveDocumentUcStr($DocumentUcStr);
							break;

						case 3:
							$queryResponse = $this->deleteDocumentUcStr($DocumentUcStr);
							break;
					}
					if (empty($queryResponse) || empty($queryResponse[0]) || !empty($queryResponse[0]['Error_Msg'])) {
						$this->rollbackTransaction();
						if (!empty($queryResponse[0]['Error_Msg'])) {
							return $queryResponse;
						}
						return false;
					}
				}
			}
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 *  Функция
	 */
	function saveDocumentUc($data) {
		if (!isset($data['DocumentUc_id'])) {
			$proc = 'p_DocumentUc_ins';
		} else {
			$proc = 'p_DocumentUc_upd';
		}
		if ((isset($data['FarmacyOtdel_id'])) && (empty($data['DrugFinance_id']))) {
			$data['DrugFinance_id'] = $data['FarmacyOtdel_id'];
		}
		if (!isset($data['session']['OrgFarmacy_id']) && isset($data['Lpu_id'])) {
			$data['Lpu_id'] = $data['Lpu_id'];
		} else {
			$data['Lpu_id'] = Null;
		}
		
		if (!isset($data['DrugDocumentStatus_id'])) {
			$data['DrugDocumentStatus_id'] = 1;
		}

		//если не задан id типа документа, вычисляем его через ник (при возможности)
		if ((!isset($data['DrugDocumentType_id']) || $data['DrugDocumentType_id'] <= 0) && isset($data['DrugDocumentType_SysNick'])) {
			$query = "
				select top 1
					DrugDocumentType_id
				from
					v_DrugDocumentType with(nolock)
				where
					DrugDocumentType_SysNick = :DrugDocumentType_SysNick;
			";
			$result = $this->db->query($query, array('DrugDocumentType_SysNick' => $data['DrugDocumentType_SysNick']));
			if (is_object($result)) {
				$res = $result->result('array');
				if (isset($res[0]) && isset($res[0]['DrugDocumentType_id']))
					$data['DrugDocumentType_id'] = $res[0]['DrugDocumentType_id'];
			}
		}

		//если не задан номер документа, вычисляем следующий номер в бд
		if (!isset($data['DocumentUc_id']) && (!isset($data['DocumentUc_Num']) || $data['DocumentUc_Num'] <= 0)) {
			$data['DocumentUc_Num'] = 0;
			$query = "
				select
					isnull(max(cast(DocumentUc_Num as bigint)),0) + 1 as num
				from
					DocumentUc with(nolock)
				where
					DocumentUc_Num not like '%.%' and
					DocumentUc_Num not like '%,%' and
					isnumeric(DocumentUc_Num) = 1 and
					len(DocumentUc_Num) <= 18;
			";
			$doc_num = $this->getFirstResultFromQuery($query);
			if ($doc_num !== false) {
				$data['DocumentUc_Num'] = $doc_num;
			}
		}
			
		//проверка актуальности МОЛ для приходных документов учета
		if ($data['DrugDocumentType_id'] == 1 && $data['Mol_tid'] > 0 && !empty($data['DocumentUc_setDate'])) { // DrugDocumentType_id: 1 - Документ прихода/расхода медикаментов В ЛПУ
			$query = "
				select
					Mol_endDT
				from
					Mol with(nolock)
				where
					Mol_id = :Mol_id and
					Mol_endDT <= :DocumentUc_setDate
			";
			$result = $this->db->query($query, array('Mol_id' => $data['Mol_tid'], 'DocumentUc_setDate' => $data['DocumentUc_setDate']));
			if (is_object($result)) {			
				$res = $result->result('array');
				if (isset($res[0]) && isset($res[0]['Mol_endDT']))
					return array(array('Error_Code' => '1', 'Error_Message' => 'Срок действия МОЛ истек. Сохранение документа учета невозможно.'));
			}
		}

		//проверка актуальности источника финансирования для приходных документов учета
		if (!empty($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0 && !empty($data['DocumentUc_setDate']) && (!isset($data['Mol_sid']) || $data['Mol_sid'] <= 0)) {
			$query = "
				select
					DrugFinance_endDate,
					DrugFinance_begDate
				from
					DrugFinance
				where
					DrugFinance_id = :DrugFinance_id and
					(DrugFinance_endDate <= :DocumentUc_setDate or
					DrugFinance_begDate >= :DocumentUc_setDate)

			";
            $result = $this->db->query($query, array('DrugFinance_id' => $data['DrugFinance_id'], 'DocumentUc_setDate' => $data['DocumentUc_setDate']));
            if (is_object($result)) {
                $res = $result->result('array');
                if (isset($res[0]) && (isset($res[0]['DrugFinance_endDate']) || isset($res[0]['DrugFinance_begDate'])))
                    return array(array('Error_Code' => '1', 'Error_Message' => 'Срок действия источника финансирования не соответствует выбранной дате подписания. Сохранение документа учета невозможно.'));
            }
        }
		
		$params = array(
			'DocumentUc_id' => isset($data['DocumentUc_id']) &&  $data['DocumentUc_id'] > 0 ? $data['DocumentUc_id'] : null,
			'DocumentUc_Num' => $data['DocumentUc_Num'],
			'DocumentUc_setDate' => isset($data['DocumentUc_setDate']) && !empty($data['DocumentUc_setDate']) ? $data['DocumentUc_setDate'] : null,
			'DocumentUc_didDate' => isset($data['DocumentUc_didDate']) && !empty($data['DocumentUc_didDate']) ? $data['DocumentUc_didDate'] : null,
			'DocumentUc_DogNum' =>  isset($data['DocumentUc_DogNum']) ? $data['DocumentUc_DogNum'] : 0,
			'DocumentUc_DogDate' => isset($data['DocumentUc_DogDate']) ? $data['DocumentUc_DogDate'] : null,
			'DocumentUc_InvNum' => isset($data['DocumentUc_InvNum']) && !empty($data['DocumentUc_InvNum']) ? $data['DocumentUc_InvNum'] : null,
			'DocumentUc_InvDate' => isset($data['DocumentUc_InvDate']) && !empty($data['DocumentUc_InvDate']) ? $data['DocumentUc_InvDate'] : null,
			'DocumentUc_InvoiceNum' => isset($data['DocumentUc_InvoiceNum']) && !empty($data['DocumentUc_InvoiceNum']) ? $data['DocumentUc_InvoiceNum'] : null,
			'DocumentUc_InvoiceDate' => isset($data['DocumentUc_InvoiceDate']) && !empty($data['DocumentUc_InvoiceDate']) ? $data['DocumentUc_InvoiceDate'] : null,
			'WhsDocumentUc_id' => isset($data['WhsDocumentUc_id']) && !empty($data['WhsDocumentUc_id']) ? $data['WhsDocumentUc_id'] : null,
			'Lpu_id' => isset($data['Lpu_id']) && !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'Org_id' => isset($data['Org_id']) && !empty($data['Org_id']) ? $data['Org_id'] : null,
			'Contragent_id' => !empty($data['Contragent_id']) ? $data['Contragent_id'] : null,
			'Contragent_sid' => $data['Contragent_sid'],
			'Mol_sid' => isset($data['Mol_sid']) && !empty($data['Mol_sid']) ? $data['Mol_sid'] : null,
			'Contragent_tid' => $data['Contragent_tid'],
			'Mol_tid' => isset($data['Mol_tid']) && !empty($data['Mol_tid']) ? $data['Mol_tid'] : null,
			'Storage_sid' => isset($data['Storage_sid']) && !empty($data['Storage_sid']) ? $data['Storage_sid'] : null,
			'SubAccountType_sid' => isset($data['SubAccountType_sid']) && !empty($data['SubAccountType_sid']) ? $data['SubAccountType_sid'] : null,
			'Storage_tid' => isset($data['Storage_tid']) && !empty($data['Storage_tid']) ? $data['Storage_tid'] : null,
			'SubAccountType_tid' => isset($data['SubAccountType_tid']) && !empty($data['SubAccountType_tid']) ? $data['SubAccountType_tid'] : null,
			'DrugFinance_id' => isset($data['DrugFinance_id']) && !empty($data['DrugFinance_id']) ? $data['DrugFinance_id'] : null,
			'DrugDocumentType_id' => $data['DrugDocumentType_id'],
			'DrugDocumentStatus_id' => !empty($data['DrugDocumentStatus_id']) ? $data['DrugDocumentStatus_id'] : null,
			'pmUser_id' => $data['pmUser_id'],
			'WhsDocumentCostItemType_id' => isset($data['WhsDocumentCostItemType_id']) && !empty($data['WhsDocumentCostItemType_id']) ? $data['WhsDocumentCostItemType_id'] : null,
			'DrugDocumentClass_id' => !empty($data['DrugDocumentClass_id']) ? $data['DrugDocumentClass_id'] : null,
			'DocumentUc_planDT' => !empty($data['DocumentUc_planDate']) ? $data['DocumentUc_planDate'] : null,
			'DocumentUc_begDT' => !empty($data['DocumentUc_begDate']) ? $data['DocumentUc_begDate'] : null,
			'DocumentUc_endDT' => !empty($data['DocumentUc_endDate']) ? $data['DocumentUc_endDate'] : null,
		);

		$query = "
			Declare @DocumentUc_id bigint = :DocumentUc_id;
			Declare @Error_Code bigint = 0;
			Declare @Error_Message varchar(4000) = '';

			exec {$proc}
				@DocumentUc_id = @DocumentUc_id output,
				@DocumentUc_Num = :DocumentUc_Num,
				@DocumentUc_setDate = :DocumentUc_setDate,
				@DocumentUc_didDate = :DocumentUc_didDate,
				@DocumentUc_DogNum = :DocumentUc_DogNum,
				@DocumentUc_DogDate = :DocumentUc_DogDate,
				@DocumentUc_InvNum = :DocumentUc_InvNum,
				@DocumentUc_InvDate = :DocumentUc_InvDate,
				@DocumentUc_InvoiceNum = :DocumentUc_InvoiceNum,
				@DocumentUc_InvoiceDate = :DocumentUc_InvoiceDate,
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				--@DocumentUc_Sum = null,
				--@DocumentUc_SumR = null,
				--@DocumentUc_SumNds = null,
				--@DocumentUc_SumNdsR = null,
				@Lpu_id = :Lpu_id,
				@Org_id = :Org_id,
				@Contragent_id = :Contragent_id,
				@Contragent_sid = :Contragent_sid,
				@Mol_sid = :Mol_sid,
				@Contragent_tid = :Contragent_tid,
				@Mol_tid = :Mol_tid,
				@Storage_sid = :Storage_sid,
				@SubAccountType_sid = :SubAccountType_sid,
				@Storage_tid = :Storage_tid,
				@SubAccountType_tid = :SubAccountType_tid,
				@DrugFinance_id = :DrugFinance_id,
				@DrugDocumentType_id = :DrugDocumentType_id,
				@DrugDocumentStatus_id = :DrugDocumentStatus_id,
				@DrugDocumentClass_id = :DrugDocumentClass_id,
				@DocumentUc_planDT = :DocumentUc_planDT,
				@DocumentUc_begDT = :DocumentUc_begDT,
				@DocumentUc_endDT = :DocumentUc_endDT,
				@pmUser_id = :pmUser_id,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @DocumentUc_id as DocumentUc_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		//print getDebugSQL($query, $params);exit;
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Функция
	 */
	function saveDocumentUcStr($data) {
		$procedure = "";

		if ( !isset($data['DocumentUcStr_id']) ) {
			$procedure = "p_DocumentUcStr_ins";
		}
		else {
			$procedure = "p_DocumentUcStr_upd";
			// предварительно проверить есть ли связанный введенный учет по текущей партии 
			if ($this->isDocumentUcStrExistChanges($data)) {
				// и если есть - не разрешать изменять документ 
				return array(0 => array('Error_Msg' => 'Редактирование строки документа невозможно, поскольку<br/>данный медикамент используется в других документах учета!'));
			}
		}

		if (empty($data['DrugProducer_id']) && (!empty($data['DrugProducer_New']))) {
			$data['DrugProducer_id'] = $this->DrugProducerAdd($data);
		}

		if ((!isset($data['PrepSeries_id']) || $data['PrepSeries_id'] <= 0) && isset($data['DocumentUcStr_Ser']) && !empty($data['DocumentUcStr_Ser'])) {
			$data['PrepSeries_Ser'] = $data['DocumentUcStr_Ser'];
			$data['PrepSeries_GodnDate'] = $data['DocumentUcStr_godnDate'];
			$data['PrepSeries_id'] = $this->PrepSeriesAdd($data);
		}

        /*if (!empty($data['DocumentUcStr_oid'])) {
            $cnt = $this->getOstLiteCount($data);
            if ($cnt < $data['DocumentUcStr_Count']) {
                return array(array('Error_Msg' => 'На остатках недостаточно медикаментов.'));
            }
        }*/

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :DocumentUcStr_id;

			exec " . $procedure . "
				@DocumentUcStr_id = @Res output,
				@DocumentUcStr_oid = :DocumentUcStr_oid,
				@DocumentUc_id = :DocumentUc_id,
				@Drug_id = :Drug_id,
				@DrugFinance_id = :DrugFinance_id,
				@DocumentUcStr_Price = :DocumentUcStr_Price,
				@DocumentUcStr_PriceR = :DocumentUcStr_PriceR,
				@DocumentUcStr_Count = :DocumentUcStr_Count,
				@DrugNds_id = :DrugNds_id,
				@DocumentUcStr_EdCount = :DocumentUcStr_EdCount,
				@DocumentUcStr_SumR = :DocumentUcStr_SumR,
				@DocumentUcStr_Sum = :DocumentUcStr_Sum,
				@DocumentUcStr_SumNds = :DocumentUcStr_SumNds,
				@DocumentUcStr_SumNdsR = :DocumentUcStr_SumNdsR,
				@DocumentUcStr_godnDate = :DocumentUcStr_godnDate,
				@DocumentUcStr_NZU = :DocumentUcStr_NZU,
				@DocumentUcStr_IsLab = :DocumentUcStr_IsLab,
				@PrepSeries_id = :PrepSeries_id,
				@DrugProducer_id = :DrugProducer_id,
				@DrugLabResult_Name = :DrugLabResult_Name,
				@DocumentUcStr_Ser = :DocumentUcStr_Ser,
				@DocumentUcStr_CertNum = :DocumentUcStr_CertNum,
				@DocumentUcStr_CertDate = :DocumentUcStr_CertDate,
				@DocumentUcStr_CertGodnDate = :DocumentUcStr_CertGodnDate,
				@DocumentUcStr_CertOrg = :DocumentUcStr_CertOrg,
				@ReceptOtov_id = :ReceptOtov_id,
				@EvnRecept_id = :EvnRecept_id,
				@DocumentUcStr_PlanPrice = :DocumentUcStr_PlanPrice,
				@DocumentUcStr_PlanKolvo = :DocumentUcStr_PlanKolvo,
				@DocumentUcStr_PlanSum = :DocumentUcStr_PlanSum,
				@Person_id = :Person_id,
				@Okei_id = :Okei_id,
				@DocumentUcStr_IsNDS = :DocumentUcStr_IsNDS,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as DocumentUcStr_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		//@DocumentUcStr_RashCount = :DocumentUcStr_RashCount,
		$queryParams = array(
			'DocumentUcStr_id' => isset($data['DocumentUcStr_id']) && !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
			'DocumentUcStr_oid' => isset($data['DocumentUcStr_oid']) && !empty($data['DocumentUcStr_oid']) ? $data['DocumentUcStr_oid'] : null,
			'DocumentUc_id' => $data['DocumentUc_id'],
			'Drug_id' => $data['Drug_id'],
			'DrugFinance_id' => !empty($data['DrugFinance_id']) ? $data['DrugFinance_id'] : null,
			'DocumentUcStr_Price' => $data['DocumentUcStr_Price'],
			'DocumentUcStr_PriceR' => isset($data['DocumentUcStr_PriceR']) && !empty($data['DocumentUcStr_PriceR']) ? $data['DocumentUcStr_PriceR'] : null,
			'DrugNds_id' => !empty($data['DrugNds_id']) ? $data['DrugNds_id'] : null,
			'DocumentUcStr_Count' => $data['DocumentUcStr_Count'],
			'DocumentUcStr_EdCount' => isset($data['DocumentUcStr_EdCount']) && !empty($data['DocumentUcStr_EdCount']) ? $data['DocumentUcStr_EdCount'] : null,
			'DocumentUcStr_Sum' => $data['DocumentUcStr_Sum'],
			'DocumentUcStr_SumR' => isset($data['DocumentUcStr_SumR']) && !empty($data['DocumentUcStr_SumR']) ? $data['DocumentUcStr_SumR'] : null,
			'DocumentUcStr_SumNds' => isset($data['DocumentUcStr_SumNds']) && !empty($data['DocumentUcStr_SumNds']) ? $data['DocumentUcStr_SumNds'] : null,
			'DocumentUcStr_SumNdsR' => isset($data['DocumentUcStr_SumNdsR']) && !empty($data['DocumentUcStr_SumNdsR']) ? $data['DocumentUcStr_SumNdsR'] : null,
			'DocumentUcStr_godnDate' => isset($data['DocumentUcStr_godnDate']) && !empty($data['DocumentUcStr_godnDate']) ? $data['DocumentUcStr_godnDate'] : null,
			'DocumentUcStr_NZU' => isset($data['DocumentUcStr_NZU']) && !empty($data['DocumentUcStr_NZU']) ? $data['DocumentUcStr_NZU'] : null,
			'DocumentUcStr_IsLab' => isset($data['DocumentUcStr_IsLab']) && !empty($data['DocumentUcStr_IsLab']) ? $data['DocumentUcStr_IsLab'] : null,
			//'DocumentUcStr_RashCount' => $data['DocumentUcStr_RashCount'],
			'PrepSeries_id' => isset($data['PrepSeries_id']) && !empty($data['PrepSeries_id']) ? $data['PrepSeries_id'] : null,
			'DrugProducer_id' => isset($data['DrugProducer_id']) && !empty($data['DrugProducer_id']) ? $data['DrugProducer_id'] : null,
			'DrugLabResult_Name' => isset($data['DrugLabResult_Name']) && !empty($data['DrugLabResult_Name']) ? $data['DrugLabResult_Name'] : null,
			'DocumentUcStr_Ser' => isset($data['DocumentUcStr_Ser']) && !empty($data['DocumentUcStr_Ser']) ? $data['DocumentUcStr_Ser'] : null,
			'DocumentUcStr_CertNum' => isset($data['DocumentUcStr_CertNum']) && !empty($data['DocumentUcStr_CertNum']) ? $data['DocumentUcStr_CertNum'] : null,
			'DocumentUcStr_CertDate' => isset($data['DocumentUcStr_CertDate']) && !empty($data['DocumentUcStr_CertDate']) ? $data['DocumentUcStr_CertDate'] : null,
			'DocumentUcStr_CertGodnDate' => isset($data['DocumentUcStr_CertGodnDate']) && !empty($data['DocumentUcStr_CertGodnDate']) ? $data['DocumentUcStr_CertGodnDate'] : null,
			'DocumentUcStr_CertOrg' => isset($data['DocumentUcStr_CertOrg']) && !empty($data['DocumentUcStr_CertOrg']) ? $data['DocumentUcStr_CertOrg'] : null,
			'ReceptOtov_id' => isset($data['ReceptOtov_id']) && !empty($data['ReceptOtov_id']) ? $data['ReceptOtov_id'] : null,
			'EvnRecept_id' => isset($data['EvnRecept_id']) && !empty($data['EvnRecept_id']) ? $data['EvnRecept_id'] : null,
			'DocumentUcStr_PlanKolvo' => !empty($data['DocumentUcStr_PlanKolvo']) ? $data['DocumentUcStr_PlanKolvo'] : null,
			'DocumentUcStr_PlanPrice' => (!empty($data['DocumentUcStr_PlanPrice']) ? $data['DocumentUcStr_PlanPrice'] : null),
			'DocumentUcStr_PlanSum' => !empty($data['DocumentUcStr_PlanSum']) ? $data['DocumentUcStr_PlanSum'] : null,
			'Person_id' => !empty($data['Person_id']) ? $data['Person_id'] : null,
			'Okei_id' => !empty($data['Okei_id']) ? $data['Okei_id'] : null,
			'DocumentUcStr_IsNDS' => !empty($data['DocumentUcStr_IsNDS']) ? $data['DocumentUcStr_IsNDS'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		/*
		echo getDebugSql($query, $queryParams);
		exit;
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
	 *  Функция
	 */
	function saveDocumentInvUcStr($data) { // сохранение строки документа для инвентаризационной ведеомости, переделать потом на хранимку
		if (!isset($data['DocumentUcStr_id'])) {
			return array('Error_Msg' => 'Ошибка. Не определен идентификатор строки документа.');
		}		
		$query = "
			update DocumentUcStr
			set
				DocumentUcStr_Count = :DocumentUcStr_Count,
				DocumentUcStr_EdCount = :DocumentUcStr_EdCount,
				DocumentUcStr_Sum = :DocumentUcStr_Sum,
				DocumentUcStr_updDT = dbo.tzGetDate(),
				pmUser_updId = :pmUser_id
			where
				DocumentUcStr_id = :DocumentUcStr_id
		";
		$queryParams = array(
			'DocumentUcStr_Count' => $data['DocumentUcStr_Count'],
			'DocumentUcStr_EdCount' => $data['DocumentUcStr_EdCount'],
			'DocumentUcStr_Sum' => $data['DocumentUcStr_Sum'],
			'pmUser_id' => $data['pmUser_id'],
			'DocumentUcStr_id' => $data['DocumentUcStr_id']			
		);
		$res = $this->db->query($query, $queryParams);
		if ( $res > 0 ) {
			return array('Error_Msg' => '');
		} else {
			return array('Error_Msg' => 'При сохранении произошла ошибка');
		}
	}

	/**
	 * Проверка на наличие расхода по текущей партии 
	 */
	function isDocumentUcStrExistChanges($data) {
		$query = "
			select count(*) as _Count
			from DocumentUcStr with (nolock)
			where DocumentUcStr_oid = :DocumentUcStr_id
		";
		/*
		echo getDebugSql($query, array('DocumentUcStr_id' => $data['DocumentUcStr_id']));
		exit;
		*/
		$result = $this->db->query($query, array('DocumentUcStr_id' => $data['DocumentUcStr_id']));

		if ( is_object($result) ) {
			$r = $result->result('array');
			if ($r[0]['_Count']>0) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	* Постановка рецепта на отсрочку
	*/
	function putEvnReceptOnDelay($data) {
		$session_data = getSessionParams();
		$this->beginTransaction();

		$query = "
			declare
				@EvnRecept_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @EvnRecept_id = :EvnRecept_id;
			
			declare
			    @EvnRecept_obrDate datetime;
			    
			set @EvnRecept_obrDate = :EvnRecept_obrDate;
			if @EvnRecept_obrDate = convert(date, '01.01.1900', 104)
			    set @EvnRecept_obrDate = GetDate();

			set nocount on;

			begin try
				update
					EvnRecept WITH (ROWLOCK)
				set
					OrgFarmacy_oid = :OrgFarmacy_oid,
					EvnRecept_obrDT = @EvnRecept_obrDate,
					ReceptDelayType_id = 2
				where EvnRecept_id = @EvnRecept_id;
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			select @EvnRecept_id as EvnRecept_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;

			set nocount off;
		";

		$queryParams = array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'EvnRecept_obrDate' => $data['EvnRecept_obrDate'],
			'OrgFarmacy_oid' => !empty($data['OrgFarmacy_id']) ? $data['OrgFarmacy_id'] : null
		);
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->getFirstRowFromQuery($query, $queryParams);

		$evn_recept_id = 0;
		if ($result !== false) {
			if (!empty($result['Error_Msg'])) {
				$this->rollbackTransaction();
				return array($result);
			} else if ($result['EvnRecept_id'] > 0) {
				$evn_recept_id = $result['EvnRecept_id'];
			}
		}
		if ($session_data['session']['region']['nick'] == 'ufa' && $evn_recept_id != 0) {
		    //  Для Уфы  создаем запись для оповещения
		    $result = $this->saveReceptNotification($data);
		    
		    /*
					if (is_array($result) && count($result) > 0 && isset($result[0]['Error_Msg']) && !empty($result[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					*/
		    
		    /*
		    $query = "
			Declare 
			    @evnRecept_id bigint = :EvnRecept_id,
			    @receptNotification_id bigint = null,
			    @receptNotification_phone varchar(50) = :receptNotification_phone,
			    @receptNotification_setDate date = null,
			    @pmUser_id bigint = :pmUser_id,
			    @Error_Code int = null,
			    @Error_Message varchar(4000) = null;
			    
			Select top 1 @receptNotification_id = receptNotification_id
			    ,@receptNotification_setDate = receptNotification_setDate
			    from receptNotification with (nolock)
				    where evnRecept_id = @evnRecept_id
			    
			if @receptNotification_id is not null
			    exec p_receptNotification_upd
				    @receptNotification_id = @receptNotification_id output,
				    @evnRecept_id = @evnRecept_id,
				    @receptNotification_phone = @receptNotification_phone,
				    @receptNotification_setDate = @receptNotification_setDate,
				    @pmUser_id = @pmUser_id,
				    @Error_Code = @Error_Code output,
				    @Error_Message = @Error_Message output;
			else
			    exec p_receptNotification_ins
				    @receptNotification_id = @receptNotification_id output,
				    @evnRecept_id = @evnRecept_id,
				    @receptNotification_phone = @receptNotification_phone,
				    @receptNotification_setDate = @receptNotification_setDate,
				    @pmUser_id = @pmUser_id,
				    @Error_Code = @Error_Code output,
				    @Error_Message = @Error_Message output;

			    Select  @receptNotification_id as receptNotification_id, @Error_Code as Error_Code, @Error_Message as Error_Mes;

		    ";
		    $result = $this->db->query($query, array(
			    'EvnRecept_id' => $data['EvnRecept_id'],
			    'receptNotification_phone' => $data['receptNotification_phone'],
			    'pmUser_id' => $data['pmUser_id']
		    ));
		    */
		}
		if ($evn_recept_id == 0) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при постановке рецепта на отсрочку.'));
		}

		//проверяем наличие рецепта в ReceptOtov
		$query = "
			select top 1
				ro.ReceptOtov_id,
				' '+farm.OrgFarmacy_Name+isnull(' т.'+org.Org_Phone, '') as OrgFarmacy_Name
			from
				v_ReceptOtovUnSub ro with (nolock)
				left join v_OrgFarmacy farm with (nolock) on farm.OrgFarmacy_id = ro.OrgFarmacy_id
				left join v_Org org with (nolock) on org.Org_id = farm.Org_id
			where
				EvnRecept_id = :EvnRecept_id;
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
					er.EvnRecept_id,
					er.EvnRecept_Guid,
					er.Person_id,
					ps.Person_Snils,
					er.PersonPrivilege_id,
					er.PrivilegeType_id,
					er.Lpu_id,
					l.Lpu_Ogrn,
					er.MedPersonal_id,
					er.Diag_id,
					er.EvnRecept_Ser,
					er.EvnRecept_Num,
					convert(varchar(10), er.EvnRecept_setDT, 120) as EvnRecept_setDT,
					convert(varchar(10), er.EvnRecept_obrDT, 120)+' '+convert(varchar(10), er.EvnRecept_obrDT, 108) as EvnRecept_obrDT,
					convert(varchar(10), er.EvnRecept_otpDT, 120)+' '+convert(varchar(10), er.EvnRecept_otpDT, 108) as EvnRecept_otpDT,
					er.ReceptFinance_id,
					er.OrgFarmacy_oid,
					er.Drug_rlsid as Drug_id,
					dn.DrugNomen_Code as Drug_Code,
					er.EvnRecept_Kolvo,
					er.ReceptDelayType_id,
					er.EvnRecept_Is7Noz,
					er.DrugFinance_id,
					er.WhsDocumentCostItemType_id,
					er.EvnRecept_Kolvo,
					er.WhsDocumentUc_id,
					wr.ReceptWrong_id
				from
					v_EvnRecept er with(nolock)
					left join v_PersonState ps with(nolock) on ps.Person_id = er.Person_id
					left join v_Lpu l with(nolock) on l.Lpu_id = er.Lpu_id
					left join ReceptWrong wr with(nolock) on wr.EvnRecept_id = er.EvnRecept_id
					outer apply (
						select top 1
							DrugNomen_Code
						from
							rls.v_DrugNomen dn with(nolock)
						where
							dn.Drug_id = er.Drug_rlsid
					) dn
				where
					er.EvnRecept_id = :EvnRecept_id;
			";
			$params = array(
				'EvnRecept_id' => $data['EvnRecept_id']
			);
			$recept_data = $this->getFirstRowFromQuery($query, $params);
			if ($recept_data === false) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Не удалось получить данные о рецепте.'));
			}
			if (!empty($recept_data['ReceptWrong_id'])) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Рецепт признан неправильно выписанным.'));
			}

			//создаем запись в ReceptOtov
			$query = "
				declare
					@ReceptOtov_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_ReceptOtov_ins
					@ReceptOtov_id = @ReceptOtov_id output,
					@EvnRecept_Guid = :EvnRecept_Guid,
					@Person_id = :Person_id,
					@Person_Snils = :Person_Snils,
					@PersonPrivilege_id = :PersonPrivilege_id,
					@PrivilegeType_id = :PrivilegeType_id,
					@Lpu_id = :Lpu_id,
					@Lpu_Ogrn = :Lpu_Ogrn,
					@MedPersonalRec_id = :MedPersonalRec_id,
					@Diag_id = :Diag_id,
					@EvnRecept_Ser = :EvnRecept_Ser,
					@EvnRecept_Num = :EvnRecept_Num,
					@EvnRecept_setDT = :EvnRecept_setDT,
					@ReceptFinance_id = :ReceptFinance_id,
					@ReceptValid_id = :ReceptValid_id,
					@OrgFarmacy_id = :OrgFarmacy_id,
					@Drug_cid = :Drug_cid,
					@Drug_Code = :Drug_Code,
					@EvnRecept_Kolvo = :EvnRecept_Kolvo,
					@EvnRecept_obrDate = :EvnRecept_obrDate,
					@EvnRecept_otpDate = :EvnRecept_otpDate,
					@EvnRecept_Price = :EvnRecept_Price,
					@ReceptDelayType_id = :ReceptDelayType_id,
					@ReceptOtdel_id = :ReceptOtdel_id,
					@EvnRecept_id = :EvnRecept_id,
					@EvnRecept_Is7Noz = :EvnRecept_Is7Noz,
					@DrugFinance_id = :DrugFinance_id,
					@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
					@ReceptStatusType_id = :ReceptStatusType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ReceptOtov_id as ReceptOtov_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'EvnRecept_Guid' => $recept_data['EvnRecept_Guid'],
				'Person_id' => $recept_data['Person_id'],
				'Person_Snils' => $recept_data['Person_Snils'],
				'PersonPrivilege_id' => $recept_data['PersonPrivilege_id'],
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
				'OrgFarmacy_id' => $recept_data['OrgFarmacy_oid'],
				'Drug_cid' => $recept_data['Drug_id'],
				'Drug_Code' => $recept_data['Drug_Code'],
				'EvnRecept_Kolvo' => $recept_data['EvnRecept_Kolvo'],
				'EvnRecept_obrDate' => $recept_data['EvnRecept_obrDT'],
				'EvnRecept_otpDate' => $recept_data['EvnRecept_otpDT'],
				'EvnRecept_Price' => 0,
				'ReceptDelayType_id' => $recept_data['ReceptDelayType_id'],
				'ReceptOtdel_id' => null,
				'EvnRecept_id' => $recept_data['EvnRecept_id'],
				'EvnRecept_Is7Noz' => $recept_data['EvnRecept_Is7Noz'],
				'DrugFinance_id' => $recept_data['DrugFinance_id'],
				'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id'],
				'ReceptStatusType_id' => null,
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->getFirstRowFromQuery($query, $params);
			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					$this->rollbackTransaction();
					return array($result);
				} else if ($result['ReceptOtov_id'] > 0) {
					$receptotov_id = $result['ReceptOtov_id'];
				}
			}
			if ($receptotov_id == 0) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Сохранение данных в списке отоваренных рецептов не удалось.'));
			}
		}

		$this->commitTransaction();
		return array(array('success' => true));
	}

	/**
	 *  Функция
	 */
	function evnReceptReleaseRollback($data) {
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000),
				@EvnRecept_id bigint,
				@OrgFarmacy_id bigint,
				@ReceptDelayType_id bigint;
			set @EvnRecept_id = :EvnRecept_id;

			set nocount on;

			begin tran

			begin try
				set @ReceptDelayType_id = (
					select
						top 1 case when EvnRecept_obrDT <> EvnRecept_otpDT then 2 else null end as ReceptDelayType_id
					from
						v_EvnRecept with (nolock)
					where
						EvnRecept_id = :EvnRecept_id
						and OrgFarmacy_oid = :OrgFarmacy_id
				);

				set @OrgFarmacy_id = (
					select
						top 1 case when EvnRecept_obrDT <> EvnRecept_otpDT then OrgFarmacy_oid else null end as OrgFarmacy_id
					from
						v_EvnRecept with (nolock)
					where
						EvnRecept_id = :EvnRecept_id
						and OrgFarmacy_oid = :OrgFarmacy_id
				);

				update
					EvnRecept WITH (ROWLOCK)
				set
					EvnRecept_otpDT = null,
					ReceptDelayType_id = @ReceptDelayType_id,
					OrgFarmacy_oid = @OrgFarmacy_id
				where
					EvnRecept_id = :EvnRecept_id
					and OrgFarmacy_oid = :OrgFarmacy_id;

				delete from DocumentUcStr where EvnRecept_id = :EvnRecept_id
			end try
			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			if ( @Error_Message is null and @Error_Code is null )
				commit tran
			else
				rollback tran

			select @EvnRecept_id as EvnRecept_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;

			set nocount off;
		";

		$queryParams = array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'OrgFarmacy_id' => $data['OrgFarmacy_id']
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
	 * 	Проверка на уникальность контрагента
	 */
	function checkDoubleContragent($data) {
		$this->load->helper('Options');
		$params = array();
		$filter = "(1=1)";
		$join = "";

		if (!empty($data['session']['lpu_id']) && $data['session']['lpu_id'] > 0) {
			$join .= " inner join v_ContragentOrg  CO with(nolock) on CO.Contragent_id = C.Contragent_id";
			$filter .= " and CO.Org_id = :Org_oid";
			$params['Org_oid'] = isset($data['session']['org_id']) ? $data['session']['org_id'] : 0;
		}

		// проверка выполняется в случае если пришло LpuSection_id или Org_id или OrgFarmacy_id
		if (($data['LpuSection_id']>0) || ($data['Org_id']>0) || ($data['OrgFarmacy_id']>0))
		{
			if ($data['LpuSection_id']>0) {
				$filter .= " and C.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			if ($data['Org_id']>0)
			{
				$filter .= " and C.Org_id = :Org_id";
				$params['Org_id'] = $data['Org_id'];
			}
			if ($data['OrgFarmacy_id']>0)
			{
				$filter .= " and C.OrgFarmacy_id = :OrgFarmacy_id";
				$params['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			}
			if ($data['Contragent_id']>0)
			{
				$filter .= " and C.Contragent_id != :Contragent_id";
				$params['Contragent_id'] = $data['Contragent_id'];
			}
			if ($data['ContragentType_id']>0)
			{
				$filter .= " and C.ContragentType_id = :ContragentType_id";
				$params['ContragentType_id'] = $data['ContragentType_id'];
			}
			/*if ($data['Lpu_id']>0)
			{
				$filter .= " and C.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}*/
			/*else
			{
				return false;
			}*/
			$sql = "
				Select 
					count(*) as checkCount
					from Contragent C with (nolock)
						{$join}
					where
						{$filter}
			";
			/*
			echo getDebugSql($sql, $params);
			exit;
			*/
			$res = $this->db->query($sql, $params);
			if (is_object($res))
				return $res->result('array');
			else
				return false;
		}
		else 
		{
			return array(array('checkCount'=>0));
		}
	}

	/**
	 *  Функция
	 */
	function getDokSpisAktFields($data) {
		$doc_id = $data['DocumentUc_id'];

		$query = "
			SELECT TOP 1
				du.DocumentUc_SumR,
				isnull(convert(varchar(10), du.DocumentUc_didDate, 104),'') as Act_Date,
				(case when ct.ContragentType_id <> 2 then Contragent_Name else l.Org_Name end) as Org1,
				(case when ct.ContragentType_id <> 2 then '' else Contragent_Name end) as Org2,
				(isnull(m.Person_SurName,'')+isnull(' '+m.Person_FirName,'')+isnull(' '+m.Person_SecName,'')) as Mol_Name
			FROM
				v_DocumentUc du with(nolock)
				left join v_Contragent ct with(nolock) on ct.Contragent_id = du.Contragent_sid
				left join v_Lpu l with(nolock) on l.Lpu_id = ct.Lpu_id
				left join v_Mol m with(nolock) on m.Mol_id = du.Mol_sid
			WHERE (1 = 1)
				AND DocumentUc_id = :doc_id";
				
		$result = $this->db->query($query, array('doc_id' => $doc_id));

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res) && count($res) > 0) {
				$dt = $res[0];
				
				//получаем данные по строкам документа
				$dt['docuc_str_data'] = array();				
				$query = "
					SELECT
						d.DrugTorg_Name+ISNULL(', '+d.DrugForm_Name,'')+ISNULL(', '+d.Drug_Dose,'')+ISNULL(', №'+cast(d.Drug_Fas as varchar),'') as Drug_Name,
						d.Drug_Code,
						d.Drug_PackName,
						dus.DocumentUcStr_Count,
						dus.DocumentUcStr_PriceR,
						dus.DocumentUcStr_SumR
					FROM
						v_DocumentUcStr dus with(nolock)						
						left join rls.v_Drug d with(nolock) on d.Drug_id = dus.Drug_id
					WHERE (1 = 1)
						AND dus.DocumentUc_id = :doc_id";				
				$result = $this->db->query($query, array('doc_id' => $doc_id));
				if (is_object($result)) {
					$res = $result->result('array');
					if (is_array($res)) {
						foreach($res as $row)
							$dt['docuc_str_data'][] = $row;
					}
				}
				return $dt;
			} else			
				return false;
		} else {
			return false;
		}		
	}

	/**
	 *  Функция
	 */
	function loadDocumentListByDay($data) {
		$begTime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$endTime = mktime(0, 0, 0, date("m"), date("d")+15, date("Y"));
		if (!empty($data['begDate'])) {
			$begTime = strtotime($data['begDate']);
			$endTime = strtotime($data['endDate']);
		}		
		$filter = "(1 = 1)";
		$params = array();
		
		//TODO: добавить зависимость от выбранного временного диапазона
		
		// Выбираем только документы для этой аптеки/контрагента
		if ((isset($data['Contragent_id'])) && ($data['Contragent_id']>0)) {
			$filter = $filter." and DocUc.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		} else {
			$filter = $filter." and DocUc.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if ((isset($data['DrugFinance_id'])) && ($data['DrugFinance_id']>0)) {
			$filter = $filter." and DocUc.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if ((isset($data['DrugDocumentType_id'])) && ($data['DrugDocumentType_id']>0)) {
			$filter = $filter." and DocUc.DrugDocumentType_id = :DrugDocumentType_id";
			$params['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
		}
		if ((isset($data['DrugDocumentStatus_id'])) && ($data['DrugDocumentStatus_id']>0)) {
			$filter = $filter." and DocUc.DrugDocumentStatus_id = :DrugDocumentStatus_id";
			$params['DrugDocumentStatus_id'] = $data['DrugDocumentStatus_id'];
		}
		if ((isset($data['DocumentUc_DogNum'])) && ($data['DocumentUc_DogNum']>0)) {
			$filter = $filter." and DocUc.DocumentUc_DogNum = :DocumentUc_DogNum";
			$params['DocumentUc_DogNum'] = $data['DocumentUc_DogNum'];
		}
		if ((isset($data['DocumentUc_Num'])) && ($data['DocumentUc_Num']>0)) {
			$filter = $filter." and DocUc.DocumentUc_Num = :DocumentUc_Num";
			$params['DocumentUc_Num'] = $data['DocumentUc_Num'];
		}
		if ((isset($data['Contragent_sid'])) && ($data['Contragent_sid']>0)) {
			$filter = $filter." and DocUc.Contragent_sid = :Contragent_sid";
			$params['Contragent_sid'] = $data['Contragent_sid'];
		}
		if ((isset($data['Contragent_tid'])) && ($data['Contragent_tid']>0)) {
			$filter = $filter." and DocUc.Contragent_tid = :Contragent_tid";
			$params['Contragent_tid'] = $data['Contragent_tid'];
		}
		if ((isset($data['DocumentUc_Date'])) && !empty($data['DocumentUc_Date'])) {
			$filter = $filter." and convert(varchar(10), DocUc.DocumentUc_updDT, 120) = :DocumentUc_Date";
			$params['DocumentUc_Date'] = $data['DocumentUc_Date'];
		}
		if ((isset($data['DrugMnn_id'])) && ($data['DrugMnn_id']>0)) {
			$filter = $filter."
				and DocUc.DocumentUc_id in (
					select
						DocumentUc.DocumentUc_id
					from
						DocumentUc with(nolock)
						left join DocumentUcStr with(nolock) on DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id
						left join rls.Drug with(nolock) on Drug.Drug_id = DocumentUcStr.Drug_id
					where
						Drug.DrugMnn_id = :DrugMnn_id
				)";
			$params['DrugMnn_id'] = $data['DrugMnn_id'];
		}
		if ((isset($data['Drug_id'])) && ($data['Drug_id']>0)) {
			$filter = $filter."
				and DocUc.DocumentUc_id in (
					select
						DocumentUc.DocumentUc_id
					from
						DocumentUc with(nolock)
						left join DocumentUcStr with(nolock) on DocumentUcStr.DocumentUc_id = DocumentUc.DocumentUc_id						
					where
						DocumentUcStr.Drug_id = :Drug_id
				)";
			$params['Drug_id'] = $data['Drug_id'];
		}
		if ($begTime > 0 && $endTime > 0) {
			$begTime = getdate($begTime);
			$endTime = getdate($endTime);			
			$filter .= "
				and cast(DocUc.DocumentUc_updDT as date) between :begDate and :endDate
			";
			$params['begDate'] = $begTime['year'].'-'.$begTime['mon'].'-'.$begTime['mday'];
			$params['endDate'] = $endTime['year'].'-'.$endTime['mon'].'-'.$endTime['mday'];
		}
		
		$query = "
			select
				convert(varchar(10), DocUc.DocumentUc_updDT, 104) as DocumentUc_Date,
				DDT.DrugDocumentType_Name as DocType,
				DocUc.DrugDocumentStatus_id,
				DocUc.DrugDocumentType_id,
				DocUc.DocumentUc_id,
				RTrim(DocUc.DocumentUc_Num) as DocumentUc_Num,
				RTrim(DocUc.DocumentUc_DogNum) as DocumentUc_DogNum,
				convert(varchar(10), DocUc.DocumentUc_setDate, 104) as DocumentUc_setDate,
				DocUc.DocumentUc_didDate,
				convert(varchar(10), DocUc.DocumentUc_didDate, 104) as DocumentUc_txtdidDate,
				DF.DrugFinance_Name,
				RTrim(T.Contragent_Name) as Contragent_tName,
				RTrim(S.Contragent_Name) as Contragent_sName,
				DDS.DrugDocumentStatus_Name
			from 
				v_DocumentUc DocUc with (nolock)
				left join Contragent T with (nolock) on T.Contragent_id = DocUc.Contragent_tid --потребитель
				left join Contragent S with (nolock) on S.Contragent_id = DocUc.Contragent_sid --поставщик
				left join DrugFinance DF with (nolock) on DF.DrugFinance_id = DocUc.DrugFinance_id
				left join DrugDocumentType DDT with (nolock) on DDT.DrugDocumentType_id = DocUc.DrugDocumentType_id
				left join DrugDocumentStatus DDS with (nolock) on DDS.DrugDocumentStatus_id = DocUc.DrugDocumentStatus_id
			where
				{$filter}
				and (DocUc.DrugDocumentType_id = 1 or DocUc.DrugDocumentType_id = 8)
			order by 
				DocUc.DocumentUc_updDT desc
		";
		//echo getDebugSql($query, $params); exit;
		$res = $this->db->query($query, $params);
		if (is_object($res)) {			
			return $res->result('array');
		} else
			return false;
	}

	/**
	 *  Функция
	 */
	function executeDocumentUc($data) {
		//старт транзакции
		$this->beginTransaction();

		$error = array(); //для сбора ошибок при "исполнении" документа
		$result = array();
		$new_id = 0;
		$doc = array(
			'status' => 0,
			'type' => 0,
			'incom' => true
		);
		
		//получаем информацию о документе
		$query = "
			select
				DU.Contragent_sid,
				DU.DrugDocumentType_id,
				DU.DrugDocumentStatus_id,
				DDS.DrugDocumentStatus_Name
			from
				v_DocumentUc DU with(nolock)
				left join v_DrugDocumentStatus DDS with(nolock) on DDS.DrugDocumentStatus_id = DU.DrugDocumentStatus_id
			where
				DU.DocumentUc_id = :DocumentUc_id;
		";
		$res = $this->db->query($query, array('DocumentUc_id' => $data['DocumentUc_id']));
		if (is_object($res)) {
			$self_contragent_id = isset($data['session']['Contragent_id']) ? $data['session']['Contragent_id'] : 0;
			$res = $res->result('array');
			$res = $res[0];
			$doc['status'] = $res['DrugDocumentStatus_id'];
			$doc['status_name'] = $res['DrugDocumentStatus_Name'];
			$doc['type'] = $res['DrugDocumentType_id'];
			$doc['incom'] = !($res['Contragent_sid'] == $self_contragent_id); //определяем документ расхода или документ прихода
		}

		//Проверки
		//Недопустимый статус
		if (!in_array($doc['status'], array(1/*,2*/))) { //1 - Новый
			$error[] = "Исполнение документа невозможно. Недопустимый статус документа: ".$doc['status_name'];
		}

		//В списке медикаментов есть позиции с пустой серией или сроком годности
		$query = "
			select
				count(DocumentUcStr_id) as cnt
			from
				DocumentUcStr with(nolock)
			where
				DocumentUc_id = :DocumentUc_id and
				PrepSeries_id is null
		";
		$res = $this->getFirstResultFromQuery($query, array('DocumentUc_id' => $data['DocumentUc_id']));
		if ($res > 0) {
			$error[] = "Исполнение документа невозможно, так как в списке медикаментов есть строки без серии.";
		}

		switch($doc['type']) {
			case 1: //документы прихода/расхода медикаментов				
				//Документ прихода
				if ($doc['incom'])
					$error[] = "Для создания накладной необходим документ расхода";
				//Список медикаментов пуст
				if (count($error) < 1) {
					$query = "
						select count(DocumentUcStr_id) as cnt
						from DocumentUcStr with(nolock)
						where DocumentUc_id = :DocumentUc_id
					";
					$res = $this->db->query($query, array('DocumentUc_id' => $data['DocumentUc_id']));
					if (is_object($res)) {
						$res = $res->result('array');
						if ($res[0]['cnt'] < 1)
							$error[] = "Список медикаментов пуст";
					}
				}
				//Накладная уже создана
				if (count($error) < 1) {
					$query = "
						select count(DocumentUc_id) as cnt
						from DocumentUc with(nolock)
						where DrugDocumentType_id = 6 and DocumentUc_pid = :DocumentUc_id
					";
					$res = $this->db->query($query, array('DocumentUc_id' => $data['DocumentUc_id']));
					if (is_object($res)) {			
						$res = $res->result('array');
						if ($res[0]['cnt'] > 0)
							$error[] = "Накладная для данного документа уже создана";
					}
				}
			break;
		}

		//Непосредственное исполнение
		if (count($error) < 1) {
			switch($doc['type']) {
				case 1: //документы прихода/расхода медикаментов
				case 10: //расходная накладная
					//копируем документ, превращая в накладную
					$result = $this->createDokNakByDocumentUc($data);

					if ($doc['type'] == 10) {
						$response = $this->updateDrugOstatRegistryByDocumentUc($data);
						if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
							$error[] = $response[0]['Error_Msg'];
						} else {
							$result = array('DocumentUc_id' => $data['DocumentUc_id']);
						}
					}
				break;
				case 2: //Документ ввода остатков
					$response = $this->updateDrugOstatRegistryForDokSpis($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					} else {
						$result = array('DocumentUc_id' => $data['DocumentUc_id']);
					}
				break;
				case 3: //Документ ввода остатков
				case 6: //Приходная накладаная
				case 16: //Возвратная накладаная
					$response = $this->createDrugOstatRegistryByDocumentUc($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					} else {
						$result = array('DocumentUc_id' => $data['DocumentUc_id']);
					}
				break;

				default:
					$error[] = "Для данного типа документов не предусмотрен механизм \"исполнения\"";
				break;
			}
			
		}

		//смена статуса документа
		if (count($error) < 1) {
			//проставляем статус "исполнен" для изначльного документа
			$query = "
				update
					DocumentUc
				set
					DrugDocumentStatus_id = 2,
					pmUser_updID = :pmUser_id,
					DocumentUc_updDT = GetDate()--dbo.tzGetDate()
				where
					DocumentUc_id = :DocumentUc_id;
			";
			$res = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
			$this->rollbackTransaction();
			return $result;
		}

		//коммит транзакции
		$this->commitTransaction();

		return $result;
	}
	
	/**
	 *  Перемещение определенного количества медикаментов (quantity) из конкретной строки (DocumentUcStr_id) в строку конкретного документа учета (DocumentUc_id)
	 */
	function displacementDrugs($data) {
		if (!isset($data['DocumentUc_id']) || !isset($data['DocumentUcStr_id']) || !isset($data['quantity'])) {			
			return false;
		}
		$query = "
			insert into
				DocumentUcStr (
					DocumentUcStr_oid,
					DocumentUc_id,
					Drug_id,
					DrugFinance_id,
					DrugNds_id,
					DrugProducer_id,
					DocumentUcStr_Price,
					DocumentUcStr_PriceR,
					DocumentUcStr_EdCount,
					DocumentUcStr_Count,
					DocumentUcStr_Sum,
					DocumentUcStr_SumR,
					DocumentUcStr_SumNds,
					DocumentUcStr_SumNdsR,
					DocumentUcStr_Ser,
					DocumentUcStr_CertNum,
					DocumentUcStr_CertDate,
					DocumentUcStr_CertGodnDate,
					DocumentUcStr_CertOrg,
					DocumentUcStr_IsLab,
					DrugLabResult_Name,
					DocumentUcStr_RashCount,
					DocumentUcStr_RegDate,
					DocumentUcStr_RegPrice,
					DocumentUcStr_godnDate,
					DocumentUcStr_setDate,
					DocumentUcStr_Decl,
					DocumentUcStr_Barcod,
					DocumentUcStr_CertNM,
					DocumentUcStr_CertDM,
					DocumentUcStr_NTU,
					DocumentUcStr_NZU,
					DocumentUcStr_Reason,
					EvnRecept_id,
					pmUser_insID,
					pmUser_updID,
					DocumentUcStr_insDT,
					DocumentUcStr_updDT,
					EvnDrug_id,
					ReceptOtov_id,
					PrepSeries_id,
					DocumentUcStr_PlanKolvo,
					Okei_id,
					DocumentUcStr_PlanPrice,
					DocumentUcStr_PlanSum,
					Person_id
				)
			select
				DocumentUcStr_id,
				:DocumentUc_id,
				Drug_id,
				DrugFinance_id,
				DrugNds_id,
				DrugProducer_id,
				DocumentUcStr_Price,
				DocumentUcStr_PriceR,
				((DocumentUcStr_EdCount*:quantity)/DocumentUcStr_Count),
				:quantity,
				((DocumentUcStr_Sum*:quantity)/DocumentUcStr_Count),	
				((DocumentUcStr_SumR*:quantity)/DocumentUcStr_Count),
				((DocumentUcStr_SumNds*:quantity)/DocumentUcStr_Count),
				((DocumentUcStr_SumNdsR*:quantity)/DocumentUcStr_Count),
				DocumentUcStr_Ser,
				DocumentUcStr_CertNum,
				DocumentUcStr_CertDate,
				DocumentUcStr_CertGodnDate,
				DocumentUcStr_CertOrg,
				DocumentUcStr_IsLab,
				DrugLabResult_Name,
				DocumentUcStr_RashCount,
				DocumentUcStr_RegDate,
				DocumentUcStr_RegPrice,
				DocumentUcStr_godnDate,
				DocumentUcStr_setDate,
				DocumentUcStr_Decl,
				DocumentUcStr_Barcod,
				DocumentUcStr_CertNM,
				DocumentUcStr_CertDM,
				DocumentUcStr_NTU,
				DocumentUcStr_NZU,
				DocumentUcStr_Reason,
				:EvnRecept_id,
				:pmUser_id,
				:pmUser_id,
				dbo.tzGetDate(),
				dbo.tzGetDate(),
				EvnDrug_id,
				ReceptOtov_id,
				PrepSeries_id,
				DocumentUcStr_PlanKolvo,
				Okei_id,
				DocumentUcStr_PlanPrice,
				DocumentUcStr_PlanSum,
				Person_id
			from
				DocumentUcStr with(nolock)
			where
				DocumentUcStr_id = :DocumentUcStr_id
				and DocumentUcStr_Count > 0
		";
		$res = $this->db->query($query, $data);
		return true;
	}

	/**
	 *  Функция
	 */
	function EvnReceptSetDelayType($data, $delaytype_id) {
		$query = "
			update
				EvnRecept
			set
				ReceptDelayType_id = :delaytype_id,
				OrgFarmacy_oid = :OrgFarmacy_oid
			where
				EvnRecept_id = :evnrecept_id
		";
		$res = $this->db->query($query, array(
			'delaytype_id' => $delaytype_id,
			'evnrecept_id' => $data['EvnRecept_id'],
			'OrgFarmacy_oid' => isset($data['session']['OrgFarmacy_id']) ? $data['session']['OrgFarmacy_id'] : null
		));
		return true;
	}

	/**
	 *  Функция
	 */
	function loadContragentDocumentsList($filter) {
		$q = "
			with allow_org_list as (
				select :Org_oid as Org_id						--Собственная МО
				union
				select id as Org_id from MinZdravList()			--Минздрав
				union
				select isnull(MS.Org_id, L.Org_id) as Org_id	--Администратор ЛЛО
				from v_MedService MS with(nolock)
				inner join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
				left join v_Lpu L with(nolock) on L.Lpu_id = MS.Lpu_id
				where MST.MedServiceType_SysNick like 'adminllo'
			)
			select * from (
				select
					'WhsDocumentTitle_' + cast(WhsDocumentTitle_id as varchar) Doc_id,
					WhsDocumentTitle_id Document_id,
					'WhsDocumentTitle' DocumentType,
					null Document_Num,
					WhsDocumentTitle_Name Document_Name,
					convert(varchar(10), WhsDocumentTitle_begDate, 104) Document_begDate,
					convert(varchar(10), WhsDocumentTitle_endDate, 104) Document_endDate
				from
					v_WhsDocumentTitle with(nolock)
				where
					WhsDocumentTitle_id in (select WhsDocumentTitle_id from WhsDocumentRightRecipient with(nolock) where Contragent_id = :Contragent_id)
				union
				select
					'WhsDocumentSupply_' + cast(WhsDocumentSupply_id as varchar) Doc_id,
					WhsDocumentSupply_id Document_id,
					'WhsDocumentSupply' DocumentType,
					WhsDocumentUc_Num Document_Num,
					WhsDocumentUc_Name Document_Name,
					convert(varchar(10), WhsDocumentUc_Date, 104) Document_begDate,
					convert(varchar(10), WhsDocumentSupply_ExecDate, 104) Document_endDate
				from
					v_WhsDocumentSupply WDS with(nolock)
					left join v_pmUserCache pmUser with(nolock) on pmUser.pmUser_id = WDS.pmUser_insID
				where
					Org_sid in (select Org_id from Contragent with(nolock) where Contragent_id = :Contragent_id)
					and Org_aid in (select Org_id from allow_org_list with(nolock))
			) p order by Document_endDate desc, Document_begDate desc
		";
		$filter['Org_oid'] = isset($filter['session']['org_id'])?$filter['session']['org_id']:0;
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Импорт документов учет с типом "Приходная накладная" (DokNak)
	 */
	function importDokNak($data) {
		require_once("promed/libraries/Spreadsheet_Excel_Reader/Spreadsheet_Excel_Reader.php");

		$f_res = array(array('Error_Msg' => null));

		$xls_data = new Spreadsheet_Excel_Reader();
		$xls_data->setOutputEncoding('UTF-8');
		$xls_data->read($data['FileFullName']);

		$cur_date = new DateTime();

		$doc_arr = array();
		$nds_arr = array();
		$err_arr = array();
		
		$fld_arr = array(
			'NOMERDOK' =>	array('cell_num' => '1', 'required' => true, 'type' => 'doc'),
			'DATADOK' => 	array('cell_num' => '2', 'required' => true, 'type' => 'doc'),
			'CODKONTR' => 	array('cell_num' => '4', 'required' => true, 'type' => 'doc'),
			'NAMKONTR' => 	array('cell_num' => '5', 'required' => true, 'type' => 'doc'),
			'NOMK_LS' => 	array('cell_num' => '6', 'required' => true, 'type' => 'drug'),
			'MADE' => 	array('cell_num' => '9', 'required' => false, 'type' => 'drug'),
			'SERIA' => 		array('cell_num' => '11', 'required' => true, 'type' => 'drug'),
			'NOM_S_D' => 	array('cell_num' => '14', 'required' => true, 'type' => 'drug'),
			'SROK_S_D' => 	array('cell_num' => '15', 'required' => true, 'type' => 'drug'),
			'VID_S_D' => 	array('cell_num' => '16', 'required' => true, 'type' => 'drug'),
			'SROKGOD' => 	array('cell_num' => '17', 'required' => true, 'type' => 'drug'),
			'REGISTR' => 	array('cell_num' => '18', 'required' => true, 'type' => 'drug'),
			'KOLVO' => 		array('cell_num' => '20', 'required' => true, 'type' => 'drug'),
			'CENA' => 		array('cell_num' => '21', 'required' => true, 'type' => 'drug'),
			'CENASNDS' => 	array('cell_num' => '22', 'required' => true, 'type' => 'drug'),
			'PR_NDS' => 	array('cell_num' => '23', 'required' => true, 'type' => 'drug'),
			'NDS' => 		array('cell_num' => '24', 'required' => true, 'type' => 'drug'),
			'SUMMA' => 		array('cell_num' => '25', 'required' => true, 'type' => 'drug'),
			'SUMSNDS' => 	array('cell_num' => '26', 'required' => true, 'type' => 'drug')
		);

		if (isset($xls_data->sheets[0])) {
			if (isset($xls_data->sheets[0]['cells'][1])) {
				foreach($fld_arr as $key => $value) {
					if (!isset($xls_data->sheets[0]['cells'][1][$value['cell_num']]) || strtoupper($xls_data->sheets[0]['cells'][1][$value['cell_num']]) != $key) {
						$err_arr[] = $this->getImportDokNakError(2, array('field_name' => $key));
					}
				}
			} else {
				$err_arr[] = $this->getImportDokNakError(1);
			}

			if (count($err_arr) < 1) {
				for ($i = 2; $i <= $xls_data->sheets[0]['numRows']; $i++) {
					if (isset($xls_data->sheets[0]['cells'][$i])) {
						$doc_num = isset($xls_data->sheets[0]['cells'][$i][1]) ? $xls_data->sheets[0]['cells'][$i][1] : null;
						if (!empty($doc_num)) {
							if (!isset($doc_arr[$doc_num])) {
								$doc_arr[$doc_num] = array(
									'drugs' => array(),
									'error' => false
								);

								foreach($fld_arr as $key => $value) {
									if ($value['type'] == 'doc') {
										if (isset($xls_data->sheets[0]['cells'][$i][$value['cell_num']]) && !empty($xls_data->sheets[0]['cells'][$i][$value['cell_num']])) {
											$doc_arr[$doc_num][$key] = $xls_data->sheets[0]['cells'][$i][$value['cell_num']];
										} else {
											if ($value['required']) {
												$doc_arr[$doc_num]['error'] = true;
												$err_arr[] = $this->getImportDokNakError(3, array('field_name' => $key, 'row_num' => $i));
											} else {
												$doc_arr[$doc_num][$key] = null;
											}
										}
									}
								}
							}

							$tmp_arr = array(
								'error' => false
							);
							foreach($fld_arr as $key => $value) {
								if ($value['type'] == 'drug') {
									if (isset($xls_data->sheets[0]['cells'][$i][$value['cell_num']]) && !empty($xls_data->sheets[0]['cells'][$i][$value['cell_num']])) {
										$tmp_arr[$key] = $xls_data->sheets[0]['cells'][$i][$value['cell_num']];
									} else {
										if ($value['required']) {
											$tmp_arr['error'] = true;
											$err_arr[] = $this->getImportDokNakError(3, array('field_name' => $key, 'row_num' => $i));
										} else {
											$tmp_arr[$key] = null;
										}
									}
								}
							}
							if (!$tmp_arr['error']) {
								$doc_arr[$doc_num]['drugs'][] = $tmp_arr;
							}
						} else {
							$err_arr[] = $this->getImportDokNakError(3, array('field_name' => 'NOMERDOK', 'row_num' => $i));
						}
					}
				}

				foreach($doc_arr as $key => $value) {
					if ($value['error']) {
						unset($doc_arr[$key]);
					}
				}
			}
		} else {
			$err_arr[] = $this->getImportDokNakError(1);
		}

		//получаем массив НДС
		$q = "
			select
				DrugNds_id,
				DrugNds_Code
			from
				DrugNds;
		";
		$result = $this->db->query($q, array());
		if (is_object($result)) {
			$res = $result->result('array');
			foreach($res as $value) {
				$nds_arr[$value['DrugNds_Code']] = $value['DrugNds_id'];
			}
		}

		$doc_cnt = 0;
		foreach($doc_arr as $doc) {
			//получаем данные ГК
			$q = "
				select
					wds.WhsDocumentSupply_id,
					wds.WhsDocumentUc_Num,
					convert(varchar(10), wds.WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
					wds.DrugFinance_id,
					wds.WhsDocumentCostItemType_id,
					c_sid.Contragent_id as Contragent_sid,
					c_tid.Contragent_id as Contragent_tid,
					wds.Org_sid,
					wds.Org_rid,
					ds.DrugShipment_id
				from
					v_WhsDocumentSupply wds with (nolock)
					cross apply (
						select top 1
							Contragent_id
						from
							v_Contragent with(nolock)
						where
							Org_id = wds.Org_sid
						order by
							Lpu_id asc
					) c_sid
					outer apply (
						select top 1
							Contragent_id
						from
							v_Contragent with(nolock)
						where
							Org_id = wds.Org_rid
						order by
							Lpu_id asc
					) c_tid
					outer apply (
						select top 1
							DrugShipment_id
						from
							v_DrugShipment with(nolock)
						where
							v_DrugShipment.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
					) ds
				where
					wds.WhsDocumentUc_Num =  :WhsDocumentUc_Num;
			";
			$result = $this->db->query($q, array(
				'WhsDocumentUc_Num' => $doc['CODKONTR']
			));
			if ( is_object($result) ) {
				$sup_data = $result->result('array');
				if (count($sup_data) > 0 && isset($sup_data[0]['DrugShipment_id']) && $sup_data[0]['DrugShipment_id'] > 0) {
					$sup_data = $sup_data[0];
					$doc_id = 0;

					$contragent_sid = null;
					$org_sid = null;
					$contragent_tid = null;
					$org_tid = null;

					//получаем информацию о поставщике
					$contragent_sid = $data['Contragent_id'];
					$q = "
						select
							Org_id
						from
							v_Contragent with(nolock)
						where
							Contragent_id = :Contragent_id;
					";
					$result = $this->db->query($q, array(
						'Contragent_id' => $contragent_sid
					));
					if (is_object($result)) {
						$res = $result->result('array');
						if (isset($res[0]) && isset($res[0]['Org_id']) && $res[0]['Org_id'] > 0) {
							$org_sid = $res[0]['Org_id'];
						}
					}


					//получаем получателя из информации о текущем пользователе
					if (isset($data['session']['org_id']) && $data['session']['org_id'] > 0) {
						$org_tid = $data['session']['org_id'];
					}
					if ($org_tid > 0) {
						$q = "
							select top 1
								Contragent_id
							from
								v_Contragent with(nolock)
							where
								Org_id = :Org_id
							order by
								Lpu_id asc;
						";
						$result = $this->db->query($q, array(
							'Org_id' => $org_tid
						));
						if (is_object($result)) {
							$res = $result->result('array');
							if (isset($res[0]) && isset($res[0]['Contragent_id']) && $res[0]['Contragent_id'] > 0) {
								$contragent_tid = $res[0]['Contragent_id'];
							}
						}
					}

					//формирование документа учета
					$response = $this->saveDocumentUc(array(
						'DocumentUc_Num' => $doc['NOMERDOK'],
						'DocumentUc_didDate' => $cur_date->format('Y-m-d'),
						'DocumentUc_setDate' => $this->formatDate($doc['DATADOK']),
						'DocumentUc_DogNum' => $sup_data['WhsDocumentUc_Num'],
						'DocumentUc_DogDate' => $this->formatDate($sup_data['WhsDocumentUc_Date']),
						'Org_id' => $data['DrugDocumentType_id'] == 6 || $data['DrugDocumentType_id'] == 16 ? $org_tid : $org_sid,
						'Contragent_id' => $data['DrugDocumentType_id'] == 6 || $data['DrugDocumentType_id'] == 16 ? $contragent_tid : $contragent_sid,
						'Contragent_sid' => $contragent_sid,
						'Contragent_tid' => $contragent_tid,
						'DrugFinance_id' => $sup_data['DrugFinance_id'],
						'DrugDocumentType_id' => $data['DrugDocumentType_id'],
						'pmUser_id' => $data['pmUser_id'],
						'WhsDocumentCostItemType_id' => $sup_data['WhsDocumentCostItemType_id']
					));
					if (is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id']) && $response[0]['DocumentUc_id'] > 0) {
						$doc_id = $response[0]['DocumentUc_id'];
					}
					//формирование спецификации документа учета
					if ($doc_id > 0) {
						foreach($doc['drugs'] as $drug) {
							$drug_id = 0;

							//получаем медикамент по номенклатурному коду
							$q = "
								select
									dn.Drug_id
								from
									rls.v_DrugNomen dn with (nolock)
								where
									DrugNomen_Code = :DrugNomen_Code;
							";
							$result = $this->db->query($q, array(
								'DrugNomen_Code' => $drug['NOMK_LS']
							));
							if (is_object($result)) {
								$res = $result->result('array');
								if (isset($res[0]) && isset($res[0]['Drug_id']) && $res[0]['Drug_id'] > 0) {
									$drug_id = $res[0]['Drug_id'];
								}
							}

							//если медикамент идентифицирован, добавляем строку документа учета
							if ($drug_id > 0) {
								$this->saveDocumentUcStr(array(
									'DocumentUc_id' => $doc_id,
									'Drug_id' => $drug_id,
									'DrugFinance_id' => $sup_data['DrugFinance_id'],
									'DocumentUcStr_Price' => $drug['CENA'],
									'DocumentUcStr_PriceR' => $drug['CENA'],
									'DrugNds_id' => isset($nds_arr[$drug['PR_NDS']]) ? $nds_arr[$drug['PR_NDS']] : null,
									'DocumentUcStr_Count' => $drug['KOLVO'],
									'DocumentUcStr_Sum' => $drug['SUMMA'],
									'DocumentUcStr_SumR' => $drug['SUMMA'],
									'DocumentUcStr_SumNds' => $drug['SUMSNDS'],
									'DocumentUcStr_SumNdsR' => $drug['SUMSNDS'],
									'DocumentUcStr_godnDate' => $this->formatDate($drug['SROKGOD']),
									'DocumentUcStr_NZU' => 1,
									'DocumentUcStr_Ser' => $drug['SERIA'],
									'DrugProducer_id' => null,
									'DrugProducer_New' => $drug['MADE'],
									'DocumentUcStr_CertNum' => $drug['NOM_S_D'],
									'DocumentUcStr_CertDate' => $this->formatDate($drug['VID_S_D']),
									'DocumentUcStr_CertGodnDate' => $this->formatDate($drug['SROK_S_D']),
									'DocumentUcStr_CertOrg' => $drug['REGISTR'],
									'Server_id' => $data['Server_id'],
									'pmUser_id' => $data['pmUser_id']
								));
							} else {
								$err_arr[] = $this->getImportDokNakError(4, $drug);
							}
						}
					}
				} else {
					if (count($sup_data) <= 0) {
						$err_arr[] = $this->getImportDokNakError(5, $doc);
					} else {
						$err_arr[] = $this->getImportDokNakError(6, $doc);
					}

				}
			}
		}

		if (count($err_arr) > 0) {
			$f_res[0]['success'] = false;
			$f_res[0]['ErrorProtocol_Link'] = $this->getImportDokNakErrorProtocol($err_arr);
		}

		return $f_res;
	}

	/**
	 * Импорт приходной накладной из файла dbf
	 */
	function importDokNakFromDbf($data) {
		$f_res = array(array('Error_Msg' => null));
		$this->load->model('DocumentUc_model', 'dumodel');

		$this->beginTransaction();

		$doc_arr = array();
		$nds_arr = array();
		$log_arr = array();
		$doc_id = null;
		$cur_date = new DateTime();

		$fld_arr = array(
			'DCODE' => 		array('required' => true, 'type' => 'doc'),
			'DATE_DOC' => 	array('required' => true, 'type' => 'doc'),
			'ID_APTEKA' => 	array('required' => true, 'type' => 'doc'),
			'ID_FILIAL' => 	array('required' => false, 'type' => 'doc'),
			'XCONCEPT' => 	array('required' => true, 'type' => 'doc'),
			'CODEPOST' => 	array('required' => true, 'type' => 'doc'),
			'TYPEPOST' => 	array('required' => true, 'type' => 'doc'),
			'TYPEFIN' => 	array('required' => true, 'type' => 'doc'),
			'K_AGENT' => 	array('required' => false, 'type' => 'doc'),

			'CODE' =>		array('required' => true, 'type' => 'drug'),
			'KOLVO' =>		array('required' => true, 'type' => 'drug'),
			'PRICE_OPL' =>	array('required' => true, 'type' => 'drug'),
			'SUM_BASE' => 	array('required' => false, 'type' => 'drug'),
			'NDS_PR' => 	array('required' => true, 'type' => 'drug'),
			'SUM_OPL' => 	array('required' => false, 'type' => 'drug'),
			'PRO' => 		array('required' => false, 'type' => 'drug'),
			'DATE_REES' => 	array('required' => false, 'type' => 'drug'),
			'GTD' => 		array('required' => false, 'type' => 'drug'),
			'SERIES' => 	array('required' => true, 'type' => 'drug'),
			'SERT_N' => 	array('required' => false, 'type' => 'drug'),
			'PRODUCT' => 	array('required' => true, 'type' => 'drug'),
			'PRODUCER' => 	array('required' => false, 'type' => 'drug'),
			'SROK_S' => 	array('required' => false, 'type' => 'drug'),
			'EAN13' => 		array('required' => false, 'type' => 'drug'),
			'EXPIR_VSS' => 	array('required' => false, 'type' => 'drug'),
		);

		$handler = dbase_open($data['FileFullName'], 0);
		if (!$handler) {
			return $this->createError('', 'Ошибка чтения файла');
		}

		$record_count = dbase_numrecords($handler);

		$structErr = false;
		$record = dbase_get_record_with_names($handler, 0);
		foreach($fld_arr as $key=>$opt) {
			if (!isset($record[$key])) {
				$structErr = true;
				$log_arr[] = $this->getImportFromDbfError(2, array('field_name' => $key));
			}
		}

		if (!$structErr) {
			$date_format = 'y-m-d';

			for($i=1; $i<=$record_count; $i++) {
				$record = dbase_get_record_with_names($handler, $i);
				$ar[]=$record;
				$inputParams = array();

				array_walk($record, 'ConvertFromWin866ToUtf8');

				foreach($record as $key=>$val) {
					$val = trim($val);
					if (in_array($key, array('DATE_DOC','SROK_S','DATE_REES','EXPIR_VSS'))) {
						$date = $val;

						if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $val, $matches)) {
							$date = strtolower($date_format);
							$date = str_replace('d', $matches[1], $date);
							$date = str_replace('m', $matches[2], $date);
							$date = str_replace('y', $matches[3], $date);
						} else if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $val, $matches)) {
							$date = strtolower($date_format);
							$date = str_replace('d', $matches[3], $date);
							$date = str_replace('m', $matches[2], $date);
							$date = str_replace('y', $matches[1], $date);
						}

						$val = $date;
					}

					$inputParams[$key] = $val;
				}

				if (!empty($inputParams['DCODE'])) {
					$doc_num = $inputParams['DCODE'];

					$error = false;
					$doc = array();
					$drug = array('row_num' => $i);

					//если данные накладной ещё не заполнены, то заполняем
					if (!isset($doc_arr[$doc_num])) {
						foreach($fld_arr as $key => $opt) {
							if ($opt['type'] == 'doc') {
								if ($opt['required'] && $inputParams[$key] !== '0' && empty($inputParams[$key])) {
									$log_arr[] = $this->getImportFromDbfError(3, array('field_name' => $key, 'row_num' => $i));
									$error = true;
								} else {
									$doc[$key] = $inputParams[$key];
								}
							} else {
								continue;
							}
						}
						if (!$error) {
							$doc_arr[$doc_num]['data'] = $doc;
						}
					}

					//заполняем строку накладной
					if (isset($doc_arr[$doc_num])) {
						$error = false;

						foreach($fld_arr as $key => $opt) {
							if ($opt['type'] == 'drug') {
								if ($opt['required'] && $inputParams[$key] !== '0' && empty($inputParams[$key])) {
									$log_arr[] = $this->getImportFromDbfError(3, array('field_name' => $key, 'row_num' => $i));
									$error = true;
								} else {
									$drug[$key] = $inputParams[$key];
								}
							} else {
								continue;
							}
						}
						if (!$error) {
							$doc_arr[$doc_num]['drugs'][] = $drug;
						}
					}
				} else {
					foreach($fld_arr as $key => $opt) {
						if ($opt['required'] && $inputParams[$key] !== '0' && empty($inputParams[$key])) {
							$log_arr[][$key] = $this->getImportFromDbfError(3, array('field_name' => $key, 'row_num' => $i));
						}
					}
				}
			}

			foreach($doc_arr as $doc_num => $doc) {
				if (!isset($doc['drugs']) || count($doc['drugs']) == 0) {
					unset($doc_arr[$doc_num]);
				}
			}
		}
		dbase_close($handler);

		//получаем массив НДС
		$query = "
			select
				DrugNds_id,
				DrugNds_Code
			from v_DrugNds;
		";
		$result = $this->db->query($query, array());
		if (is_object($result)) {
			$res = $result->result('array');
			foreach($res as $value) {
				$nds_arr[$value['DrugNds_Code']] = $value['DrugNds_id'];
			}
		}

		//получаем данные из контракта
		$query = "
			select top 1
				WDS.WhsDocumentUc_id,
				WDS.WhsDocumentSupply_id,
				WDS.WhsDocumentUc_Num,
				convert(varchar(10), WDS.WhsDocumentUc_Date, 120) as WhsDocumentUc_Date,
				WDS.Org_sid,
				WDS.Org_rid,
				WDF.DrugFinance_id,
				WDF.DrugFinance_Name,
				case
					when WDF.DrugFinance_SysNick = 'fed' then 0
					when WDF.DrugFinance_SysNick = 'reg' then 1
				end as DrugFinance,
				WDCIT.WhsDocumentCostItemType_id,
				WDCIT.WhsDocumentCostItemType_Name
			from
				v_WhsDocumentSupply WDS with(nolock)
				left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = WDS.WhsDocumentCostItemType_id
				left join v_DrugFinance WDF with(nolock) on WDF.DrugFinance_id = WDS.DrugFinance_id
			where
				WDS.WhsDocumentUc_id = :WhsDocumentUc_id
		";
		$params = array('WhsDocumentUc_id' => $data['WhsDocumentUc_id']);
		$sup_data = $this->getFirstRowFromQuery($query, $params);

		if (!is_array($sup_data) || empty($sup_data['WhsDocumentSupply_id'])) {
			$log_arr[] = $this->getImportFromDbfError(4);
		} else if (count($doc_arr) > 0) {
			$response = array();
			$is_error = false;
			$doc = array();
			//Ожидается, что в файле для импорта будет одна накладная
			foreach($doc_arr as $key=>$item) {
				$doc = $item; break;
			}

			$contragent_name = null;
			$contragent_sid = null;
			$org_sid = $sup_data['Org_sid'];
			$contragent_tid = null;
			$org_tid = $data['session']['org_id'];

			if (!empty($sup_data['Org_rid']) && $org_tid != $sup_data['Org_rid']) {
				$log_arr[] = $this->getImportFromDbfError(6);
				$is_error = true;
			}

			if (!$is_error) {
				//получаем информацию о поставщике
				$q = "
					select top 1
						Contragent_id,
						Contragent_Name
					from v_Contragent with(nolock)
					where Org_id = :Org_id and Contragent_Code = :Contragent_Code;
				";

				$contragent = $this->getFirstRowFromQuery($q, array(
					'Org_id' => $org_sid,
					'Contragent_Code' => $doc['data']['CODEPOST']
				));
				$contragent_sid = $contragent['Contragent_id'];
				$contragent_name = $contragent['Contragent_Name'];

				if (!empty($data['Contragent_id'])) {
					$contragent_tid = $data['Contragent_id'];
				} else {
					//получаем получателя из информации о текущем пользователе
					$q = "
						select top 1 Contragent_id
						from v_Contragent with(nolock)
						where Org_id = :Org_id
						order by Lpu_id asc;
					";
					$contragent_tid = $this->getFirstResultFromQuery($q, array('Org_id' => $org_tid));
				}

				if (empty($contragent_tid)) {
					$log_arr[] = $this->getImportFromDbfError(7);
					$is_error = true;
				} else {
					try {
						$params = array(
							'DocumentUc_Num' => $doc['data']['DCODE'],
							'DocumentUc_setDate' => !empty($data['DocumentUc_setDate'])?$data['DocumentUc_setDate']:date('Y-m-d'),
							'DocumentUc_didDate' => !empty($data['DocumentUc_didDate'])?$data['DocumentUc_didDate']:null,
							'DocumentUc_InvoiceDate' => !empty($data['DocumentUc_InvoiceDate'])?$data['DocumentUc_InvoiceDate']:null,
							'DocumentUc_InvoiceNum' => !empty($data['DocumentUc_InvoiceNum'])?$data['DocumentUc_InvoiceNum']:null,
							'Storage_tid' => !empty($data['Storage_tid'])?$data['Storage_tid']:null,
							'Mol_tid' => !empty($data['Mol_tid'])?$data['Mol_tid']:null,
							'DocumentUc_DogNum' => $sup_data['WhsDocumentUc_Num'],
							'DocumentUc_DogDate' => $sup_data['WhsDocumentUc_Date'],
							'WhsDocumentUc_id' => $sup_data['WhsDocumentUc_id'],
							'Lpu_id' => (!empty($data['Lpu_id']) && $data['Lpu_id'] > 0) ? $data['Lpu_id'] : null,
							'Org_id' => $org_tid,
							'Contragent_id' => $contragent_tid,
							'Contragent_sid' => $contragent_sid,
							'Contragent_tid' => $contragent_tid,
							'DrugFinance_id' => $sup_data['DrugFinance_id'],
							'DrugDocumentType_id' => 6,
							'DrugDocumentStatus_id' => 1,
							'SubAccountType_sid' => 1,
							'pmUser_id' => $data['pmUser_id'],
							'WhsDocumentCostItemType_id' => $sup_data['WhsDocumentCostItemType_id']
						);
						if (empty($params['DocumentUc_InvoiceNum'])) {
							$params['DocumentUc_InvoiceNum'] = $params['DocumentUc_Num'];
						}
						if (empty($params['DocumentUc_InvoiceDate'])) {
							$params['DocumentUc_InvoiceDate'] = $params['DocumentUc_setDate'];
						}

						//формирование документа учета
						$response = $this->saveDocumentUc($params);
					} catch(Exception $e) {
						$log_arr[] = $this->getImportFromDbfError(5);
						$is_error = true;
					}
				}
			}

			if (!$is_error && isset($response[0]) && !empty($response[0]['DocumentUc_id'])) {
				$doc_id = $response[0]['DocumentUc_id'];

				if (!empty($data['Note_Text'])) {
					$this->dumodel->saveNote(array(
						'DocumentUc_id' => $doc_id,
						'Note_id' => !empty($data['Note_id'])?$data['Note_id']:null,
						'Note_Text' => $data['Note_Text'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				foreach($doc['drugs'] as $drug) {
					$query = "
						select top 1
							d.Drug_id,
							d.Drug_Ean,
							d.DrugPrep_id,
							dn.DrugNomen_Code,
							ps.PrepSeries_id,
							ps.PrepSeries_Ser,
							convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
							wdss.WhsDocumentSupplySpec_id,
							wdss.WhsDocumentSupplySpec_PriceNDS,
							wdss.Okei_id,
							dor.DrugOstatRegistry_id,
							dor.DrugOstatRegistry_Kolvo
						from
							rls.v_DrugNomen dn with(nolock)
							inner join rls.v_Drug d with(nolock) on d.Drug_id = dn.Drug_id
							outer apply (
								select top 1
									PrepSeries_id,
									PrepSeries_Ser,
									PrepSeries_GodnDate
								from rls.v_PrepSeries with(nolock)
								where Prep_id = d.DrugPrep_id and PrepSeries_Ser = :PrepSeries_Ser
							) ps
							outer apply (
								select top 1
									WhsDocumentSupplySpec_id,
									WhsDocumentSupplySpec_PriceNDS,
									Okei_id
								from v_WhsDocumentSupplySpec with(nolock)
								where
									WhsDocumentSupply_id = :WhsDocumentSupply_id
									and Drug_id = d.Drug_id
							) wdss
							outer apply (
								select top 1
									t.DrugOstatRegistry_id,
									t.DrugOstatRegistry_Kolvo
								from
									v_DrugOstatRegistry t with(nolock)
									inner join v_DrugShipment ds with(nolock) on ds.DrugShipment_id = t.DrugShipment_id
								where
									t.SubAccountType_id = 1
									and t.Drug_id = d.Drug_id
									and ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
									and t.DrugOstatRegistry_Cost = wdss.WhsDocumentSupplySpec_PriceNDS
							) dor
						where
							dn.DrugNomen_Code = :DrugNomen_Code
					";
					$params = array(
						'DrugNomen_Code' => $drug['CODE'],
						'PrepSeries_Ser' => $drug['SERIES'],
						'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id'],
					);
					//echo getDebugSQL($query, $params);exit;
					$drug_resp = $this->getFirstRowFromQuery($query, $params);
					if (is_array($drug_resp) && $drug_resp['Drug_id'] > 0) {
						$log_data = array(
							'doc' => array(
								'DocumentUc_Num' => $doc['data']['DCODE'],
								'WhsDocumentCostItemType_Name' => $sup_data['WhsDocumentCostItemType_Name'],
								'DrugFinance_Name' => $sup_data['DrugFinance_Name'],
								'Contragent_Name' => $contragent_name
							),
							'drug' => $drug_resp,
							'row_num' => $drug['row_num']
						);

						if ($doc['data']['TYPEFIN'] != $sup_data['DrugFinance']) {
							$log_arr[] = $this->getImportFromDbfLog(1, $log_data);
							continue;	// Прервать обработку строки
						}
						if (!empty($drug['EAN13']) && $drug['EAN13'] != $drug_resp['Drug_Ean']) {
							$log_arr[] = $this->getImportFromDbfLog(2, $log_data);
						}
						if (empty($drug_resp['WhsDocumentSupplySpec_id'])) {
							$log_arr[] = $this->getImportFromDbfLog(3, $log_data);
							continue;	// Прервать обработку строки
						}
						if ($drug['PRICE_OPL'] != $drug_resp['WhsDocumentSupplySpec_PriceNDS']) {
							$log_arr[] = $this->getImportFromDbfLog(4, $log_data);
							continue;	// Прервать обработку строки
						}
						if ($drug_resp['DrugOstatRegistry_Kolvo'] == 0) {
							$log_arr[] = $this->getImportFromDbfLog(5, $log_data);
							continue;
						}
						if ($drug['KOLVO'] > $drug_resp['DrugOstatRegistry_Kolvo']) {
							$log_arr[] = $this->getImportFromDbfLog(6, $log_data);
							$drug['KOLVO'] = $drug_resp['DrugOstatRegistry_Kolvo'];
						}

						if (empty($drug_resp['PrepSeries_id'])) {
							$query = "
								declare
									@PrepSeries_id bigint,
									@Error_Code int,
									@Error_Message varchar(4000);
								execute rls.p_PrepSeries_ins
									@PrepSeries_id = @PrepSeries_id output,
									@Prep_id = :Prep_id,
									@PrepSeries_Ser = :PrepSeries_Ser,
									@PrepSeries_GodnDate = :PrepSeries_GodnDate,
									@pmUser_id = :pmUser_id,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
								select @PrepSeries_id as PrepSeries_id, @Error_Code as @Error_Code, @Error_Message as Error_Msg;
							";
							$params = array(
								'Prep_id' => $drug_resp['DrugPrep_id'],
								'PrepSeries_Ser' => $drug['SERIES'],
								'PrepSeries_GodnDate' => $drug['SROK_S']
							);
							$series_resp = $this->getFirstRowFromQuery($query, $params);
							if (is_array($series_resp) && $series_resp['PrepSeries_id'] > 0) {
								$drug_resp['PrepSeries_id'] = $series_resp['PrepSeries_id'];
							}
						}

						$nds_id = isset($nds_arr[$drug['NDS_PR']]) ? $nds_arr[$drug['NDS_PR']] : null;
						$sum_nds = round(($drug['SUM_BASE']/100)*$drug['NDS_PR'], 2);
						$saved_str = $this->saveDocumentUcStr(array(
							'DocumentUc_id' => $doc_id,
							'DrugFinance_id' => $sup_data['DrugFinance_id'],
							'Drug_id' => $drug_resp['Drug_id'],
							'PrepSeries_id' => $drug_resp['PrepSeries_id'],
							'DocumentUcStr_Ser' => $drug['SERIES'],
							'DocumentUcStr_godnDate' => $drug['SROK_S'],
							'DocumentUcStr_Barcod' => $drug['EAN13'],
							'Okei_id' => $drug_resp['Okei_id'],
							'DocumentUcStr_RegDate' => $drug['DATE_REES'],
							'DocumentUcStr_RegPrice' => $drug['PRO'],
							'DocumentUcStr_Price' => $drug['PRICE_OPL'],
							'DocumentUcStr_PriceR' => $drug['PRICE_OPL'],
							'DocumentUcStr_PlanKolvo' => $drug['KOLVO'],
							'DocumentUcStr_Count' => $drug['KOLVO'],
							'DocumentUcStr_Sum' => $drug['SUM_OPL'],
							'DocumentUcStr_SumR' => $drug['SUM_OPL'],
							'DocumentUcStr_IsNDS' => $doc['data']['TYPEPOST'] == 0 ? 2 : 1,
							'DrugNds_id' => $nds_id,
							'DocumentUcStr_SumNds' => $sum_nds,
							'DocumentUcStr_SumNdsR' => $sum_nds,
							'DocumentUcStr_CertNum' => $drug['SERT_N'],
							'DocumentUcStr_CertDate' => $drug['SROK_S'],
							'DocumentUcStr_CertGodnDate' => $drug['EXPIR_VSS'],
							'DocumentUcStr_Decl' => $drug['GTD'],
							'DocumentUcStr_NZU' => 1,
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						$sh_query = "
							declare
								@Res bigint,
								@ErrCode int,
								@ErrMessage varchar(4000),
								@name varchar(30);
							set @name = isnull((
								select max(cast(DrugShipment_Name as bigint))+1
								from v_DrugShipment with(nolock)
								where ISNUMERIC(DrugShipment_Name)=1 and DrugShipment_Name not like '%.%' and DrugShipment_Name not like '%,%'
							), 1);
							exec p_DrugShipment_ins
								@DrugShipment_id = @Res output,
								@DrugShipment_setDT = :DrugShipment_setDT,
								@DrugShipment_Name = @name,
								@WhsDocumentSupply_id = :WhsDocumentSupply_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$shipment = $this->getFirstRowFromQuery($sh_query, array(
							'DrugShipment_setDT' => $doc['data']['DATE_DOC'],
							'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						$shl_query = "
							declare
								@Res bigint,
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec p_DrugShipmentLink_ins
								@DrugShipmentLink_id = @Res output,
								@DrugShipment_id = :DrugShipment_id,
								@DocumentUcStr_id = :DocumentUcStr_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$shipment_link = $this->getFirstRowFromQuery($shl_query, array(
							'DocumentUcStr_id' => $saved_str[0]['DocumentUcStr_id'],
							'DrugShipment_id' => $shipment['DrugShipment_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						$log_arr[] = $this->getImportFromDbfLog(0, $log_data);
					} else {
						$log_data = array(
							'doc' => array(
								'DocumentUc_Num' => $doc['data']['DCODE'],
								'WhsDocumentCostItemType_Name' => $sup_data['WhsDocumentCostItemType_Name'],
								'DrugFinance_Name' => $sup_data['DrugFinance_Name'],
								'Contragent_Name' => $contragent_name
							),
							'drug' => array('DrugNomen_Code' => $drug['CODE']),
							'row_num' => $drug['row_num']
						);
						$log_arr[] = $this->getImportFromDbfLog(7, $log_data);
					}
				}
			}
		}

		if (!empty($doc_id)) {
			$f_res[0]['DocumentUc_id'] = $doc_id;
		}
		if (count($log_arr) > 0) {
			$f_res[0]['success'] = false;
			$f_res[0]['Protocol_Link'] = $this->getImportDokNakFromDbfProtocol($log_arr);
		}

		$this->commitTransaction();

		return $f_res;
	}

	/**
	 * Сообщение об ошибках при импорте накладных
	 */
	function getImportDokNakError($err_code, $data = array()) {
		$err_msg = '';

		switch($err_code) {
			case 1:
				$err_msg = "Структура файла не соответствует установленной.";
				break;
			case 2:
				$err_msg = "Структура файла не соответствует установленной. Поле {$data['field_name']} не найдено.";
				break;
			case 3:
				$err_msg = "Обязательное поле {$data['field_name']} не заполнено.";
				break;
			case 4:
				$err_msg = "ЛП с кодом {$data['NOMK_LS']} не найден в номенклатурном справочнике.";
				break;
			case 5:
				$err_msg = "Госконтракт с номером {$data['CODKONTR']} не найден.";
				break;
			case 6:
				$err_msg = "Партия для госконтракта с номером {$data['CODKONTR']} не найдена.";
				break;
		}

		if (isset($data['row_num']) && !empty($data['row_num'])) {
			$err_msg = "Строка {$data['row_num']}: {$err_msg}";
		}

		return $err_msg;
	}

	/**
	 * Формирование записи для протокола импорта из dbf
	 */
	function getImportFromDbfLog($code, $data = array()) {
		$msg = '';

		if (isset($data['doc']) && is_array($data['doc'])) {
			$d = $data['doc'];
			$msg .= "№ {$d['DocumentUc_Num']}, {$d['WhsDocumentCostItemType_Name']}, {$d['DrugFinance_Name']}, {$d['Contragent_Name']}\n";
		}
		if (isset($data['row_num']) && !empty($data['row_num'])) {
			$msg .= "Строка {$data['row_num']}\n";
		}
		if (isset($data['drug']) && is_array($data['drug'])) {
			$d = $data['drug'];
			$msg .= "Код ЛС: {$d['DrugNomen_Code']}\n";
		}

		if ($code == 0) {
			$msg .= "Результат: Ok";
		} else {
			$msg .= "Результат: ";
			switch($code) {
				case 1:
					$msg .= "Ошибка: Источники финансирования ГК и строки накладной расходятся.";
					break;
				case 2:
					$msg .= "Предупреждение: Штрих-коды в накладной и справочнике ЛС расходятся.";
					break;
				case 3:
					$msg .= "Ошибка: ЛС не найдено в спецификации ГК.";
					break;
				case 4:
					$msg .= "Ошибка: Цена не соответствует ГК.";
					break;
				case 5:
					$msg .= "Ошибка: ЛС отсутсвует в учетных остатках поставщика.";
					break;
				case 6:
					$msg .= "Предупреждение: Кол-во в накладной не соответствует количеству учетных остатков поставщика.";
					break;
				case 7:
					$msg .= "Ошибка: ЛС не найдено в справочнике.";
					break;
			}
		}

		return $msg;
	}

	/**
	 * Сообщение об ошибках при импорте накладных
	 */
	function getImportFromDbfError($err_code = 0, $data = array()) {
		$err_msg = "Ошибка: ";

		switch($err_code) {
			case 1:
				$err_msg .= "Структура файла не соответствует установленной.";
				break;
			case 2:
				$err_msg .= "Структура файла не соответствует установленной. Поле {$data['field_name']} не найдено.";
				break;
			case 3:
				$err_msg .= "Обязательное поле {$data['field_name']} не заполнено.";
				break;
			case 4:
				$err_msg .= "Ошибка при получении данных контракта.";
				break;
			case 5:
				$err_msg .= "Ошибка при сохранении документа.";
				break;
			case 6:
				$err_msg .= "Организация получателя по контракту не совпадает с текущей организацией.";
				break;
			case 7:
				$err_msg .= "Не найдена запись о контрагенте получателя.";
				break;
		}

		if (isset($data['row_num']) && !empty($data['row_num'])) {
			$err_msg = "Строка {$data['row_num']}:\n{$err_msg}";
		}

		return $err_msg;
	}

	/**
	 * Запись протокола импорта накладных в файл
	 */
	function getImportDokNakFromDbfProtocol($log_array) {
		$link = '';

		$out_dir = "import_doknak_".time();
		mkdir(EXPORTPATH_REGISTRY.$out_dir);

		$msg_count = 0;
		$link = EXPORTPATH_REGISTRY.$out_dir."/protocol.txt";
		$fprot = fopen($link, 'w');

		foreach($log_array as $log_msg) {
			$msg = $log_msg;
			$msg .= "\r\n\r\n";
			fwrite($fprot, $msg);
		}

		fclose($fprot);

		return $link;
	}

	/**
	 * Запись протокола импорта накладных в файл
	 */
	function getImportDokNakErrorProtocol($err_array) {
		$link = '';

		$out_dir = "import_doknak_".time();
		mkdir(EXPORTPATH_REGISTRY.$out_dir);

		$msg_count = 0;
		$link = EXPORTPATH_REGISTRY.$out_dir."/protocol.txt";
		$fprot = fopen($link, 'w');

		foreach($err_array as $err_msg) {
			$msg = (++$msg_count).". Ошибка";
			$msg .= "\r\n".str_repeat(' ', strlen($msg_count)+2);
			$msg .= $err_msg;
			$msg .= "\r\n";
			fwrite($fprot, $msg);
		}

		fclose($fprot);

		return $link;
	}

	/**
	 * Вспомогательная функция преобразования формата даты
	 * Получает cnhjre c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
	 */
	function formatDate($date) {
		$d_str = null;
		if (!empty($date)) {
			$date = preg_replace('/\//', '.', $date);
			$d_arr = explode('.', $date);
			if (is_array($d_arr)) {
				$d_arr = array_reverse($d_arr);
			}
			if (count($d_arr) == 3) {
				$d_str = join('-', $d_arr);
			}
		}
		return $d_str;
	}

	/**
	 * Создание записей в регистре остатков для конкретного документа учета
	 */
	function createDrugOstatRegistryByDocumentUc($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				ddt.DrugDocumentType_Code,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				c_sid.Org_id as Org_sid,
				du.Storage_tid,
				du.Storage_sid
			from
				v_DocumentUc du with(nolock)
				inner join v_Contragent c_tid with(nolock) on c_tid.Contragent_id = du.Contragent_tid
				inner join v_Contragent c_sid with(nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt with(nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$doc_data = $res[0];
			}
		}
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем данные о гк
		$sup_data = array();
		$query = "
			select
				WhsDocumentSupply_id,
				WhsDocumentUc_Num,
				convert(varchar(10), WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
				Org_sid, --Поставщик
				Org_rid --Получатель
			from
				v_WhsDocumentSupply with(nolock)
			where
				WhsDocumentUc_id = :WhsDocumentUc_id or
				(:WhsDocumentUc_id is null and WhsDocumentUc_Num = :WhsDocumentUc_Num);
		";
		$result = $this->db->query($query, array(
			'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
			'WhsDocumentUc_Num' => $doc_data['DocumentUc_DogNum']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$sup_data = $res[0];
			}
		}
		if (count($sup_data) == 0) {
			return array(array('Error_Msg' => 'Договор поставки не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id,
				dus.PrepSeries_id,
				isnull(sup_spec.Okei_id, 120) as Okei_id, -- 120 - Упаковка
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(dus.DocumentUcStr_Price, 0)
						else
							cast(isnull(dus.DocumentUcStr_Price, 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id
			from
				v_DocumentUcStr dus
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				outer apply (
					select top 1
						wdss.Okei_id
					from
						v_WhsDocumentSupplySpec wdss with(nolock)
					where
						wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
						and
						wdss.Drug_id = dus.Drug_id
				) sup_spec
				outer apply (
					select top 1
						dsl.DrugShipment_id
					from
						v_DrugShipmentLink dsl with (nolock)
					where
						dsl.DocumentUcStr_id = dus.DocumentUcStr_id
				) ds
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//для приходной накладной DrugDocumentType_Code = 6
		$title_doc_cnt = 0;
		if ($doc_data['DrugDocumentType_Code'] == 6) {
			//проверяем наличие получателя по документу в списке пунктов отпуска
			$query = "
				select
					count(wdt.WhsDocumentTitle_id) as cnt
				from
					v_WhsDocumentTitle wdt with (nolock)
					left join v_WhsDocumentTitleType wdtt with (nolock) on wdtt.WhsDocumentTitleType_id = wdt.WhsDocumentTitleType_id
					left join v_WhsDocumentRightRecipient wdrr with (nolock) on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
				where
					wdt.WhsDocumentUc_id = :WhsDocumentSupply_id and
					wdtt.WhsDocumentTitleType_Code = 3 and --Приложение к ГК: список пунктов отпуска
					Org_id = :Org_id;
			";
			$title_doc_cnt = $this->getFirstResultFromQuery($query, array(
				'Org_id' => $doc_data['Org_tid'],
				'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id']
			));
		}


		if ($doc_data['DrugDocumentType_Code'] == 16 || ($doc_data['DrugDocumentType_Code'] == 6 && ($title_doc_cnt > 0 || $doc_data['Org_tid'] == $sup_data['Org_rid']))) { //для приходных накладных также проверяем является ли получатель по документу - грузополучателем по ГК
			//списание остатков со счета поставщика
			foreach ($drug_arr as $drug) {
				//ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
				$kolvo = $drug['DocumentUcStr_Count'];

				$query = "
					select
						dor.Contragent_id,
						dor.Org_id,
						dor.Storage_id,
						dor.DrugShipment_id,
						dor.Drug_id,
						dor.PrepSeries_id,
						dor.Okei_id,
						dor.DrugOstatRegistry_Kolvo,
						dor.DrugOstatRegistry_Sum,
						dor.DrugOstatRegistry_Cost
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
					where
						dor.Org_id = :Org_id and
						dor.Drug_id = :Drug_id and
						sat.SubAccountType_Code = 1 and
						dor.DrugOstatRegistry_Kolvo > 0 and
						ds.WhsDocumentSupply_id = :WhsDocumentSupply_id and
						(:PrepSeries_id is null or dor.PrepSeries_id = :PrepSeries_id) and
						(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price);
				";

				$result = $this->db->query($query, array(
					'Org_id' => $doc_data['DrugDocumentType_Code'] == 6 ? $sup_data['Org_sid'] : $doc_data['Org_sid'],
					'Drug_id' => $drug['Drug_id'],
					'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id'],
					'PrepSeries_id' => $doc_data['DrugDocumentType_Code'] != 6 || $doc_data['Org_sid'] != $sup_data['Org_sid'] ? $drug['PrepSeries_id'] : null, //для приходных накладных серия при списании учитывается только если поставщик из документа учета не является поставщиком по госконтракту
					'DocumentUcStr_Price' => $drug['DocumentUcStr_Price']
				));

				if ( is_object($result) ) {
					$res = $result->result('array');
					if (!empty($res[0]['Error_Msg'])) {
						return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
					}

					foreach ($res as $ostat) {
						if ($kolvo > 0) {
							//списание
							$kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
							$sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost']*$kol : ($ostat['DrugOstatRegistry_Sum']/$ostat['DrugOstatRegistry_Kolvo'])*$kol;

							$kolvo -= $kol;

							$query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
								exec xp_DrugOstatRegistry_count
									@Contragent_id = :Contragent_id,
									@Org_id = :Org_id,
									@Storage_id = :Storage_id,
									@DrugShipment_id = :DrugShipment_id,
									@Drug_id = :Drug_id,
									@PrepSeries_id = :PrepSeries_id,
									@SubAccountType_id = 1, -- субсчёт доступно
									@Okei_id = :Okei_id,
									@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
									@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
									@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

							$q_params = array(
								'Contragent_id' => $ostat['Contragent_id'],
								'Org_id' => $doc_data['DrugDocumentType_Code'] == 6 ? $sup_data['Org_sid'] : $doc_data['Org_sid'],
								'Storage_id' => $ostat['Storage_id'],
								'DrugShipment_id' => $ostat['DrugShipment_id'],
								'Drug_id' => $ostat['Drug_id'],
								'PrepSeries_id' => $ostat['PrepSeries_id'],
								'Okei_id' => $ostat['Okei_id'],
								'DrugOstatRegistry_Kolvo' => $kol*(-1),
								'DrugOstatRegistry_Sum' => $sum*(-1),
								'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
								'pmUser_id' => $data['pmUser_id']
							);

							$result = $this->db->query($query, $q_params);
							if ( is_object($result) ) {
								$res = $result->result('array');
								if (!empty($res[0]['Error_Msg'])) {
									return array(0 => array('Error_Msg' => 'Ошибка списания остатков'));
								}
							} else {
								return array(0 => array('Error_Msg' => 'Ошибка запроса списания остатков'));
							}

							//зачисление
							$q_params['Contragent_id'] = $doc_data['Contragent_tid'];
							$q_params['PrepSeries_id'] = $drug['PrepSeries_id'];
							$q_params['Org_id'] = $doc_data['Org_tid'];
							$q_params['Storage_id'] = $doc_data['Storage_tid'];
							$q_params['DrugOstatRegistry_Kolvo'] = $kol;
							$q_params['DrugOstatRegistry_Sum'] = $sum;

							$result = $this->db->query($query, $q_params);
							if ( is_object($result) ) {
								$res = $result->result('array');
								if (!empty($res[0]['Error_Msg'])) {
									return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
								}
							} else {
								return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
							}
						}
					}
				}

				if ($kolvo > 0) {
					return array(0 => array('Error_Msg' => 'На остатках поставщика недостаточно медикаментов для списания.'));
				}
			}
		} else {
			$shipment_id = null;

			if ($doc_data['DrugDocumentType_Code'] == 3) { //Документ ввода остатков
				//ищем патрию
				$query = "
					select top 1
						DrugShipment_id
					from
						v_DrugShipment with(nolock)
					where
						WhsDocumentSupply_id = :WhsDocumentSupply_id
					order by
						DrugShipment_id	desc;
				";
				$result = $this->db->query($query, array(
					'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id']
				));
				if (is_object($result)) {
					$res = $result->result('array');
					if (count($res) > 0) {
						$shipment_id = $res[0]['DrugShipment_id'];
					}
				}

				//если партия не найдена, создаем её
				if ($shipment_id == 0) {
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000),
							@datetime datetime;
						set @datetime = dbo.tzGetDate();
						exec p_DrugShipment_ins
							@DrugShipment_id = @Res output,
							@DrugShipment_setDT = @datetime,
							@DrugShipment_Name = :DrugShipment_Name,
							@WhsDocumentSupply_id = :WhsDocumentSupply_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, array(
						'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id'],
						'DrugShipment_Name' => $sup_data['WhsDocumentUc_Num'] + ' от ' + $sup_data['WhsDocumentUc_Date'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_object($result)) {
						$res = $result->result('array');
						if (count($res) > 0) {
							if ($res[0]['DrugShipment_id'] > 0) {
								$shipment_id = $res[0]['DrugShipment_id'];
							} else {
								//$this->db->trans_rollback();
								return $res;
							}
						}
					}
				}

				//если партия по прежнему не определена, выдаем ошибку
				if ($shipment_id == 0) {
					return array(array('Error_Msg' => 'Не удалось создать партию для договора поставки'));
				}
			}

			//создаем записи в регистре
			foreach ($drug_arr as $drug) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@Storage_id = :Storage_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 1, -- субсчёт доступно
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$result = $this->db->query($query, array(
					'Contragent_id' => $doc_data['Contragent_tid'],
					'Org_id' => $doc_data['Org_tid'],
					'Storage_id' => $doc_data['Storage_tid'],
					'DrugShipment_id' => $doc_data['DrugDocumentType_Code'] == 3 ? $shipment_id : $drug['DrugShipment_id'], //Для документов ввода остатков (код 3) используем партию из ГК ($shipment_id). Для приходных накладных (код 6) используем партии связанные со строками документа учета ($drug['DrugShipment_id']).
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ( is_object($result) ) {
					$res = $result->result('array');
					if (!empty($res[0]['Error_Msg'])) {
						return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
					}
				} else {
					return array(0 => array('Error_Msg' => 'Ошибка запроса создания регистра остатков'));
				}
			}
		}
		return array(array());
	}


	/**
	 * Редактирование записей в регистре остатков для конкретного документа учета
	 */
	function updateDrugOstatRegistryByDocumentUc($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				du.Storage_tid,
				du.Contragent_sid,
				c_sid.Org_id as Org_sid,
				du.Storage_sid,
				ddt.DrugDocumentType_Code
			from
				v_DocumentUc du with(nolock)
				inner join v_Contragent c_tid with(nolock) on c_tid.Contragent_id = du.Contragent_tid
				inner join v_Contragent c_sid with(nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt with(nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$doc_data = $res[0];
			}
		}
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем данные о гк
		$sup_data = array();
		$query = "
			select
				WhsDocumentSupply_id,
				WhsDocumentUc_Num,
				convert(varchar(10), WhsDocumentUc_Date, 104) as WhsDocumentUc_Date
			from
				v_WhsDocumentSupply with(nolock)
			where
				WhsDocumentUc_id = :WhsDocumentUc_id or
				(:WhsDocumentUc_id is null and WhsDocumentUc_Num = :WhsDocumentUc_Num);
		";
		$result = $this->db->query($query, array(
			'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
			'WhsDocumentUc_Num' => $doc_data['DocumentUc_DogNum']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$sup_data = $res[0];
			}
		}
		if (count($sup_data) == 0) {
			return array(array('Error_Msg' => 'Договор поставки не найден'));
		}

		//ищем патрию для документов прихода/расхода
		$shipment_id = 0;
		if ($doc_data['DrugDocumentType_Code'] == 1) {
			$query = "
				select top 1
					DrugShipment_id
				from
					v_DrugShipment with(nolock)
				where
					WhsDocumentSupply_id = :WhsDocumentSupply_id
				order by
					DrugShipment_id	desc;
			";
			$result = $this->db->query($query, array(
				'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id']
			));
			if (is_object($result)) {
				$res = $result->result('array');
				if (count($res) > 0) {
					$shipment_id = $res[0]['DrugShipment_id'];
				}
			}
			//если партия не определена, выдаем ошибку
			if ($shipment_id == 0) {
				//$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Не удалось найти партию для договора поставки'));
			}
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id,
				dus.PrepSeries_id,
				isnull(sup_spec.Okei_id, 120) as Okei_id, -- 120 - Упаковка
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				isnull(dus.DocumentUcStr_Price, 0) as DocumentUcStr_Price,
				ds.DrugShipment_id
			from
				v_DocumentUcStr dus
				outer apply (
					select top 1
						wdss.Okei_id
					from
						v_WhsDocumentSupplySpec wdss with(nolock)
					where
						wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id
						and
						wdss.Drug_id = dus.Drug_id
				) sup_spec
				outer apply (
					select top 1
						dsl.DrugShipment_id
					from
						v_DrugShipmentLink dsl with (nolock)
					where
						dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				) ds
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'WhsDocumentSupply_id' => $sup_data['WhsDocumentSupply_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			//$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//редактируем записи в регистре
		foreach ($drug_arr as $drug) {
			$query = "
				select
					isnull(sum(DrugOstatRegistry_Kolvo), 0) as DrugOstatRegistry_Kolvo
				from
					v_DrugOstatRegistry
				where
					Contragent_id = :Contragent_id and
					Org_id = :Org_id and
					(:Storage_id is null or Storage_id = :Storage_id) and
					DrugShipment_id = :DrugShipment_id and
					Drug_id = :Drug_id and
					PrepSeries_id = :PrepSeries_id and
					DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost;
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_sid'],
				'Org_id' => $doc_data['Org_sid'],
				'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
				'DrugShipment_id' => $doc_data['DrugDocumentType_Code'] == 1 ? $shipment_id : $drug['DrugShipment_id'], //Для документов прихода/расхода (код 1) используем партию из ГК ($shipment_id). Для расходных накладных (код 10) используем партии связанные со строками документа учета ($drug['DrugShipment_id']).
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
			);
			$result = $this->getFirstResultFromQuery($query, $params);
			if ($result === false) {
				return array(array('Error_Msg' => 'Ошибка при получении данных регистра остатков'));
			} else if($result <= 0 || $result < $drug['DocumentUcStr_Count']*1) {
				return array(array('Error_Msg' => 'В регистре остатков недостаточно медикаментов для списания'));
			}

			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec xp_DrugOstatRegistry_count
					@Contragent_id = :Contragent_id,
					@Org_id = :Org_id,
					@Storage_id = :Storage_id,
					@DrugShipment_id = :DrugShipment_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = :PrepSeries_id,
					@SubAccountType_id = 1, -- субсчёт доступно
					@Okei_id = :Okei_id,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
					@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_sid'],
				'Org_id' => $doc_data['Org_sid'],
				'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
				'DrugShipment_id' => $doc_data['DrugDocumentType_Code'] == 1 ? $shipment_id : $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'Okei_id' => $drug['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count']*(-1),
				'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count']*(-1),
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
				}
			} else {
				return array(0 => array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
			}
		}

		return array(array());
	}

	/**
	 * Списание медикаментов c регистра остатков по сериям в спецификации документа.
	 */
	function updateDrugOstatRegistryForDokSpis($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum,
				du.DrugDocumentType_id,
				isnull(du.Org_id,l.Org_id) as Org_id,
				du.DrugFinance_id,
				du.WhsDocumentCostItemType_id
			from
				v_DocumentUc du with(nolock)
				left join v_Lpu l with(nolock) on l.Lpu_id = du.Lpu_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$doc_data = $res[0];
			}
		}
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id,
				dus.PrepSeries_id,
				isnull(dus.DocumentUcStr_Count, 0) as Kolvo,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(dus.DocumentUcStr_Price, 0)
						else
							cast(isnull(dus.DocumentUcStr_Price, 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price
			from
				v_DocumentUcStr dus
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			//$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//редактируем записи в регистре
		foreach ($drug_arr as &$drug) {
			$query = "
				declare
					@SubAccountType_id bigint;

				set @SubAccountType_id = (select SubAccountType_id from v_SubAccountType with(nolock) where SubAccountType_Code = 1); --Доступно

				select
					Contragent_id,
					DrugShipment_id,
					SubAccountType_id,
					Okei_id,
					ltrim(rtrim(str(DrugOstatRegistry_Sum/DrugOstatRegistry_Kolvo,12,2))) as Price,
					isnull(DrugOstatRegistry_Kolvo, 0) as Available_Kolvo
				from
					v_DrugOstatRegistry
				where
					SubAccountType_id = @SubAccountType_id and
					DrugOstatRegistry_Kolvo > 0 and
					Org_id = :Org_id and
					Drug_id = :Drug_id and
					DrugFinance_id = :DrugFinance_id and
					WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
					PrepSeries_id = :PrepSeries_id;
			";
			$params = array(
				'Org_id' => $doc_data['Org_id'],
				'Drug_id' => $drug['Drug_id'],
				'DrugFinance_id' => $doc_data['DrugFinance_id'],
				'WhsDocumentCostItemType_id' => $doc_data['WhsDocumentCostItemType_id'],
				'PrepSeries_id' => $drug['PrepSeries_id']
			);
			$ost_data = array();
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$ost_data = $result->result('array');
			} else {
				return array(array('Error_Msg' => 'Ошибка при получении данных регистра остатков'));
			}

			foreach($ost_data as $ost) {
				$kolvo = $ost['Available_Kolvo'] > $drug['Kolvo'] ? $drug['Kolvo'] : $ost['Available_Kolvo'];

				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = :SubAccountType_id,
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$params = array(
					'Contragent_id' => $ost['Contragent_id'],
					'Org_id' => $doc_data['Org_id'],
					'DrugShipment_id' => $ost['DrugShipment_id'],
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'SubAccountType_id' => $ost['SubAccountType_id'],
					'Okei_id' => $ost['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $kolvo*(-1),
					'DrugOstatRegistry_Sum' => $ost['Price']*$kolvo*(-1),
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($query, $params);
				if ( is_object($result) ) {
					$res = $result->result('array');
					if (!empty($res[0]['Error_Msg'])) {
						return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
					}
				} else {
					return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
				}

				$drug['Kolvo'] -= $kolvo;
			}

			if ($drug['Kolvo'] > 0) {
				return array(array('Error_Msg' => 'Недостаточно медикамента для списания'));
			}
		}

		return array(array());
	}

	/**
	 * Обеспечение рецепта
	 */
	function provideEvnRecept($data) {
		//старт транзакции
		$this->beginTransaction();

		if (empty($data['session']['Contragent_id'])) {
			return array(array('Error_Msg' => 'Отсутствуют данные контрагента.'));
		}

		//получаем данные текущего пользователя
		$org_id = $data['session']['org_id'];
		$contragent_id = $data['session']['Contragent_id'];
		$cur_date = new DateTime();

		//получаем данные общего характера
		$query = "
			declare
				@SubAccountType_id bigint,
				@DrugDocumentType_id bigint,
				@DrugDocumentStatus_id bigint,
				@PacientContragent_id bigint,
				@Storage_id bigint,
				@Yes_id bigint;

			set @SubAccountType_id = (select top 1 SubAccountType_id from v_SubAccountType with(nolock) where SubAccountType_Code = 1); --Доступно
			set @DrugDocumentType_id = (select top 1 DrugDocumentType_id from v_DrugDocumentType with(nolock) where DrugDocumentType_SysNick = 'DocReal'); --Реализация
			set @DrugDocumentStatus_id = (select top 1 DrugDocumentStatus_id from v_DrugDocumentStatus with(nolock) where DrugDocumentStatus_Code = 4); --Исполнен
			set @PacientContragent_id = (select top 1 Contragent_id from v_Contragent with(nolock) where Contragent_Code = 1); --Пациент
			set @Storage_id = (select top 1 Storage_id from StorageStructLevel with(nolock) where MedService_id = :MedService_id);
			set @Yes_id = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = 1);

			select
				@SubAccountType_id as SubAccountType_id,
				@DrugDocumentType_id as DrugDocumentType_id,
				@DrugDocumentStatus_id as DrugDocumentStatus_id,
				@PacientContragent_id as PacientContragent_id,
				@Storage_id as Storage_id,
				@Yes_id as Yes_id;
		";
		$common_data = $this->getFirstRowFromQuery($query, array(
			'MedService_id' => $data['MedService_id']
		));
		if ($common_data === false) {
			return array(array('Error_Msg' => 'Не удалось получить данные.'));
		}

		//проверка текущего статуса рецепта
		$query = "
			select
				count(EvnRecept_id) as cnt
			from
				EvnRecept er with (nolock)
				left join v_ReceptDelayType rdt with (nolock) on rdt.ReceptDelayType_id = er.ReceptDelayType_id
			where
				EvnRecept_id = :EvnRecept_id and
				ReceptDelayType_Code = 0;
		";
		$params = array(
			'EvnRecept_id' => $data['EvnRecept_id']
		);
		$result = $this->getFirstResultFromQuery($query, $params);
		if ($result !== false) {
			if ($result > 0) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Рецепт уже обеспечен.'));
			}
		} else {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при обращении к базе данных.'));
		}

		//изменение статуса рецепта
		$query = "
			declare
				@ReceptDelayType_id bigint,
				@OrgFarmacy_id bigint,
				@EvnRecept_obrDT datetime;

			set @ReceptDelayType_id = (select top 1 ReceptDelayType_id from v_ReceptDelayType with(nolock) where ReceptDelayType_Code = 0);
			set @OrgFarmacy_id = (select top 1 OrgFarmacy_id from v_OrgFarmacy with(nolock) where Org_id = :Org_id);
			set @EvnRecept_obrDT = (select top 1 isnull(EvnRecept_obrDT, dbo.tzGetDate()) from v_EvnRecept with(nolock) where EvnRecept_id = :EvnRecept_id);

			update
				Evn
			set
				Evn_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				Evn_id = :EvnRecept_id;

			update
				EvnRecept
			set
				ReceptDelayType_id = @ReceptDelayType_id,
				OrgFarmacy_oid = @OrgFarmacy_id,
				EvnRecept_obrDT = @EvnRecept_obrDT,
				EvnRecept_otpDT = dbo.tzGetDate()
			where
				EvnRecept_id = :EvnRecept_id;
		";
		$result = $this->db->query($query, array(
			'Org_id' => $data['session']['org_id'],
			'EvnRecept_id' => $data['EvnRecept_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		//получаем данные рецепта
		$query = "
			select
				er.EvnRecept_id,
				er.EvnRecept_Guid,
				er.Person_id,
				ps.Person_Snils,
				er.PersonPrivilege_id,
				er.PrivilegeType_id,
				er.Lpu_id,
				l.Lpu_Ogrn,
				er.MedPersonal_id,
				er.Diag_id,
				er.EvnRecept_Ser,
				er.EvnRecept_Num,
				convert(varchar(10), er.EvnRecept_setDT, 120) as EvnRecept_setDT,
				convert(varchar(10), er.EvnRecept_obrDT, 120)+' '+convert(varchar(10), er.EvnRecept_obrDT, 108) as EvnRecept_obrDT,
				convert(varchar(10), er.EvnRecept_otpDT, 120)+' '+convert(varchar(10), er.EvnRecept_otpDT, 108) as EvnRecept_otpDT,
				er.ReceptFinance_id,
				er.OrgFarmacy_oid,
				er.Drug_rlsid as Drug_id,
				dn.DrugNomen_Code as Drug_Code,
				er.EvnRecept_Kolvo,
				er.ReceptDelayType_id,
				er.EvnRecept_Is7Noz,
				er.DrugFinance_id,
				er.WhsDocumentCostItemType_id,
				er.EvnRecept_Kolvo,
				er.WhsDocumentUc_id
			from
				v_EvnRecept er with (nolock)
				left join v_PersonState ps with(nolock) on ps.Person_id = er.Person_id
				left join v_Lpu l with(nolock) on l.Lpu_id = er.Lpu_id
				outer apply (
					select top 1
						DrugNomen_Code
					from
						rls.v_DrugNomen dn with(nolock)
					where
						dn.Drug_id = er.Drug_rlsid
				) dn
			where
				er.EvnRecept_id = :EvnRecept_id;
		";
		$params = array(
			'EvnRecept_id' => $data['EvnRecept_id']
		);
		$recept_data = $this->getFirstRowFromQuery($query, $params);
		if ($recept_data === false) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить данные о рецепте.'));
		}


		//получаем данные о выбранных сериях и строках регистра остатков
		$series_data = array();
		if (!empty($data['DrugOstatDataJSON'])) {
			$series_data = (array) json_decode($data['DrugOstatDataJSON']);
			$ost_pack_kolvo = 0;
			foreach($series_data as &$s_data) {
                $coeff = !empty($s_data->WhsDocumentSupplySpecDrug_Coeff) && $s_data->WhsDocumentSupplySpecDrug_Coeff > 0 ? $s_data->WhsDocumentSupplySpecDrug_Coeff : 1; //коэфицент пересчета количества для синонимов
                $ost_pack_kolvo += $s_data->PackKolvo/$coeff;
			}
			if ($recept_data['EvnRecept_Kolvo'] != $ost_pack_kolvo) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Суммарное количество медикамента для выбранных серий, не соответствует количеству в рецепте.'));
			}
		}
		if (count($series_data) < 1) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить данные о выбранных сериях.'));
		}

		//списываем медикамент с остатков и суммируем количество по ценам и медикаментам
		$price_array = array();
		foreach($series_data as &$s_data) {
			$query = "
				declare
					@Contragent_id bigint,
					@Org_id bigint,
					@DrugShipment_id bigint,
					@Drug_id bigint,
					@PrepSeries_id bigint,
					@SubAccountType_id bigint,
					@Okei_id bigint,
					@DrugOstatRegistry_Kolvo float,
					@DrugOstatRegistry_Sum float,
					@Storage_id bigint,
					@DrugOstatRegistry_Cost numeric(18,2),
					@GoodsUnit_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				select
					@Contragent_id = Contragent_id,
					@Org_id = Org_id,
					@DrugShipment_id = DrugShipment_id,
					@Drug_id = Drug_id,
					@PrepSeries_id = PrepSeries_id,
					@SubAccountType_id = SubAccountType_id,
					@Okei_id = Okei_id,
					@DrugOstatRegistry_Kolvo = DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = DrugOstatRegistry_Sum,
					@Storage_id = Storage_id,
					@DrugOstatRegistry_Cost = DrugOstatRegistry_Cost,
					@GoodsUnit_id = GoodsUnit_id
				from
					v_DrugOstatRegistry with (nolock)
				where
					DrugOstatRegistry_id = :DrugOstatRegistry_id;

				if (@DrugOstatRegistry_Kolvo > 0  and @DrugOstatRegistry_Kolvo >= :DrugOstatRegistry_Kolvo)
				begin
					set @DrugOstatRegistry_Sum = @DrugOstatRegistry_Cost*:DrugOstatRegistry_Kolvo*(-1);
					set @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo*(-1);

					exec xp_DrugOstatRegistry_count
						@Contragent_id = @Contragent_id,
						@Org_id = @Org_id,
						@DrugShipment_id = @DrugShipment_id,
						@Drug_id = @Drug_id,
						@PrepSeries_id = @PrepSeries_id,
						@SubAccountType_id = @SubAccountType_id,
						@Okei_id = @Okei_id,
						@DrugOstatRegistry_Kolvo = @DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = @DrugOstatRegistry_Sum,
						@Storage_id = @Storage_id,
						@DrugOstatRegistry_Cost = @DrugOstatRegistry_Cost,
						@GoodsUnit_id = @GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end;
				else
				begin
					set @ErrMessage = (
						select top 1
							isnull(
								d.Drug_Name+', '+
								ps.PrepSeries_Ser+', '+
								convert(varchar(10), ps.PrepSeries_GodnDate, 104)+', '+
								'№ '+ds.DrugShipment_Name+
								' – '+cast(dor.DrugOstatRegistry_Kolvo as varchar)+' шт. '+
								'недостаточно ЛП на остатках аптеки.   Рецепт не обеспечен. Выполните обеспечение рецепта с другой серией.',
								'Для обеспечения рецепта недостаточно медикаментов'
							) as msg
						from
							v_DrugOstatRegistry dor with (nolock)
							left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
							left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
							left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
						where
							dor.DrugOstatRegistry_id = :DrugOstatRegistry_id
					)
				end;

				select @Drug_id as Drug_id, @DrugOstatRegistry_Cost as DrugOstatRegistry_Cost, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'DrugOstatRegistry_id' => $s_data->DrugOstatRegistry_id,
				'DrugOstatRegistry_Kolvo' => $s_data->Kolvo,
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->getFirstRowFromQuery($query, $params);
			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					$this->rollbackTransaction();
					return array($result);
				} else {
					$s_data->Drug_id = $result['Drug_id'];
					$s_data->DrugOstatRegistry_Cost = $result['DrugOstatRegistry_Cost'];

                    if ($s_data->Drug_id > 0) {
                        if(!isset($price_array[$s_data->Drug_id])) {
                            $price_array[$s_data->Drug_id] = array();
                        }
                        if ($s_data->DrugOstatRegistry_PackCost > 0) {
                            if(!isset($price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_PackCost])) {
                                $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_PackCost] = array(
                                    'pack_kolvo' => 0,
                                    'barcode_list' => array()
                                );
                            }
                            $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_PackCost]['pack_kolvo'] += $s_data->PackKolvo;
                            /*if (!empty($s_data->BarCode_Data)) {
                                //разбираем массив со штрих кодами на отдельные элементы и сохраняем в массиве цен в виде списка
                                $bc_arr = explode(',', $s_data->BarCode_Data);
                                foreach($bc_arr as $bc_item) {
                                    $bc_data = preg_split('/\|/', $bc_item);
                                    if (is_array($bc_data) && count($bc_data) == 2 && !empty($bc_data[0]) && !empty($bc_data[1])) {
                                        $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_PackCost]['barcode_list'][] = array(
                                            'id' => $bc_data[0],
                                            'code' => $bc_data[1]
                                        );
                                    }
                                }
                            }*/
                        }
                    }
				}
			} else {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Не удалось списать медикаменты с регистра остатков.'));
			}
		}

		//создаем запись в ReceptOtov
        foreach($price_array as $drug_id => $full_price_data) {
            foreach($full_price_data as $pack_price => $price_data) {
                //ищем подходящую записи в ReceptOtov
                $query = "
                    select top 1
                        ro.ReceptOtov_id
                    from
                        ReceptOtov ro with (nolock)
                        left join v_ReceptDelayType rdt with(nolock) on rdt.ReceptDelayType_id = ro.ReceptDelayType_id
                    where
                        EvnRecept_id = :EvnRecept_id and
                        rdt.ReceptDelayType_Code = 1 --Отложен
                    order by
                        ro.ReceptOtov_id;
                ";
                $receptotov_id = $this->getFirstResultFromQuery($query, array(
                    'EvnRecept_id' => $recept_data['EvnRecept_id']
                ));

                $proc = 'p_ReceptOtov_ins';
                if ($receptotov_id > 0) {
                    $proc = 'p_ReceptOtov_upd';
                } else {
                    $receptotov_id = null;
                }

                $query = "
                    declare
                        @ReceptOtov_id bigint = :ReceptOtov_id,
                        @ErrCode int,
                        @ErrMessage varchar(4000);

                    exec {$proc}
                        @ReceptOtov_id = @ReceptOtov_id output,
                        @EvnRecept_Guid = :EvnRecept_Guid,
                        @Person_id = :Person_id,
                        @Person_Snils = :Person_Snils,
                        @PersonPrivilege_id = :PersonPrivilege_id,
                        @PrivilegeType_id = :PrivilegeType_id,
                        @Lpu_id = :Lpu_id,
                        @Lpu_Ogrn = :Lpu_Ogrn,
                        @MedPersonalRec_id = :MedPersonalRec_id,
                        @Diag_id = :Diag_id,
                        @EvnRecept_Ser = :EvnRecept_Ser,
                        @EvnRecept_Num = :EvnRecept_Num,
                        @EvnRecept_setDT = :EvnRecept_setDT,
                        @ReceptFinance_id = :ReceptFinance_id,
                        @ReceptValid_id = :ReceptValid_id,
                        @OrgFarmacy_id = :OrgFarmacy_id,
                        @Drug_cid = :Drug_cid,
                        @Drug_Code = :Drug_Code,
                        @EvnRecept_Kolvo = :EvnRecept_Kolvo,
                        @EvnRecept_obrDate = :EvnRecept_obrDate,
                        @EvnRecept_otpDate = :EvnRecept_otpDate,
                        @EvnRecept_Price = :EvnRecept_Price,
                        @ReceptDelayType_id = :ReceptDelayType_id,
                        @ReceptOtdel_id = :ReceptOtdel_id,
                        @EvnRecept_id = :EvnRecept_id,
                        @EvnRecept_Is7Noz = :EvnRecept_Is7Noz,
                        @DrugFinance_id = :DrugFinance_id,
                        @WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
                        @ReceptStatusType_id = :ReceptStatusType_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;
                    select @ReceptOtov_id as ReceptOtov_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                ";
                $params = array(
                    'ReceptOtov_id' => $receptotov_id,
                    'EvnRecept_Guid' => $recept_data['EvnRecept_Guid'],
                    'Person_id' => $recept_data['Person_id'],
                    'Person_Snils' => $recept_data['Person_Snils'],
                    'PersonPrivilege_id' => $recept_data['PersonPrivilege_id'],
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
                    'OrgFarmacy_id' => $recept_data['OrgFarmacy_oid'],
                    'Drug_cid' => $drug_id,
                    'Drug_Code' => $recept_data['Drug_Code'],
                    'EvnRecept_Kolvo' => $price_data['pack_kolvo'],
                    'EvnRecept_obrDate' => $recept_data['EvnRecept_obrDT'],
                    'EvnRecept_otpDate' => $recept_data['EvnRecept_otpDT'],
                    'EvnRecept_Price' => $pack_price,
                    'ReceptDelayType_id' => $recept_data['ReceptDelayType_id'],
                    'ReceptOtdel_id' => null,
                    'EvnRecept_id' => $recept_data['EvnRecept_id'],
                    'EvnRecept_Is7Noz' => $recept_data['EvnRecept_Is7Noz'],
                    'DrugFinance_id' => $recept_data['DrugFinance_id'],
                    'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id'],
                    'ReceptStatusType_id' => null,
                    'pmUser_id' => $data['pmUser_id']
                );
                $result = $this->getFirstRowFromQuery($query, $params);
                if ($result !== false) {
                    if (!empty($result['Error_Msg'])) {
                        $this->rollbackTransaction();
                        return array($result);
                    } else if ($result['ReceptOtov_id'] > 0) {
                        $receptotov_id = $result['ReceptOtov_id'];
                        $price_array[$drug_id][$pack_price]['receptotov_id'] = $receptotov_id;
                    }
                }
                if ($receptotov_id <= 0) {
                    $this->rollbackTransaction();
                    return array(array('Error_Msg' => 'Сохранение данных в списке отоваренных рецептов не удалось.'));
                }
            }
        }


		//создаем документ реализации
		$doc_id = 0;
		$response = $this->saveDocumentUc(array(
			'DocumentUc_Num' => $recept_data['EvnRecept_Num'],
			'DocumentUc_didDate' => $cur_date->format('Y-m-d'),
			'DocumentUc_setDate' => $cur_date->format('Y-m-d'),
			'DocumentUc_DogNum' => null,
			'DocumentUc_DogDate' => null,
			'Org_id' => $org_id,
			'Contragent_id' => $contragent_id,
			'Contragent_sid' => $contragent_id,
			'Contragent_tid' => $common_data['PacientContragent_id'],
			'DrugFinance_id' => $recept_data['DrugFinance_id'],
			'DrugDocumentType_id' => $common_data['DrugDocumentType_id'],
			'DrugDocumentStatus_id' => $common_data['DrugDocumentStatus_id'],
			'SubAccountType_sid' => $common_data['SubAccountType_id'],
			'Storage_sid' => $common_data['Storage_id'],
			'pmUser_id' => $data['pmUser_id'],
			'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id']
		));
		if (is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id']) && $response[0]['DocumentUc_id'] > 0) {
			$doc_id = $response[0]['DocumentUc_id'];
		}
		if ($doc_id <= 0) {
			$this->rollbackTransaction();
			return array(0 => array('Error_Msg' => 'Не удалось создать документ реализации'));
		}

		//получение коэфицентов для рассчета суммы НДС
		$nds_koef = array();
		$query = "
			select
				DrugNds_id,
				1-(100/(100.0+DrugNds_Code)) as koef
			from
				v_DrugNds with (nolock)
		";
		$result = $this->db->query($query);
		if (is_object($result)) {
			$result = $result->result('array');
			foreach($result as $nds_data) {
				$nds_koef[$nds_data['DrugNds_id']] = $nds_data['koef'];
			}
		}

		//формирование спецификации документа реализации
		foreach($series_data as &$s_data) {
			//обрабатываем дату
			$godn_date = !empty($s_data->PrepSeries_GodnDate) ? join(array_reverse(preg_split('/[.]/',$s_data->PrepSeries_GodnDate)),'-') : null;

			//рассчитываем суммы
			$sum = $s_data->DrugOstatRegistry_Cost > 0 ? $s_data->DrugOstatRegistry_Cost*$s_data->Kolvo : null;

			$dus_data = $this->saveDocumentUcStr(array(
				'DocumentUc_id' => $doc_id,
				'DocumentUcStr_oid' => $s_data->DocumentUcStr_id,
				'Drug_id' => $s_data->Drug_id,
				'DrugFinance_id' => $recept_data['DrugFinance_id'],
				'DocumentUcStr_Price' => $s_data->DrugOstatRegistry_Cost,
				'DocumentUcStr_PriceR' => $s_data->DrugOstatRegistry_Cost,
				'DrugNds_id' => $s_data->DrugNds_id,
				'DocumentUcStr_Count' => $s_data->Kolvo,
				'DocumentUcStr_Sum' => $sum,
				'DocumentUcStr_SumR' => $sum,
				'DocumentUcStr_SumNds' => isset($nds_koef[$s_data->DrugNds_id]) ? round($sum*$nds_koef[$s_data->DrugNds_id], 2) : 0,
				'DocumentUcStr_SumNdsR' => isset($nds_koef[$s_data->DrugNds_id]) ? round($sum*$nds_koef[$s_data->DrugNds_id], 2) : 0,
				'DocumentUcStr_godnDate' => $godn_date,
				'DocumentUcStr_NZU' => 1,
				'DocumentUcStr_Ser' => $s_data->PrepSeries_Ser,
				'PrepSeries_id' => $s_data->PrepSeries_id,
				'EvnRecept_id' => $recept_data['EvnRecept_id'],
				'ReceptOtov_id' => $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_PackCost]['receptotov_id'],
				'DocumentUcStr_IsNDS' => $common_data['Yes_id'],
                'GoodsUnit_bid' => !empty($s_data->GoodsUnit_id) ? $s_data->GoodsUnit_id : null,
				'pmUser_id' => $data['pmUser_id']
			));

            //копирование списка штрих кодов в строки документа учета
            if (is_array($dus_data) && !empty($dus_data[0]) && !empty($dus_data[0]['DocumentUcStr_id'])) {
                /*$barcode_list  = $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_PackCost]['barcode_list'];
                foreach($barcode_list as $barcode_data) {
                    $bc_data = $this->copyObject('DrugPackageBarCode', array(
                        'DrugPackageBarCode_id' => $barcode_data['id'],
                        'DocumentUcStr_id' => $dus_data[0]['DocumentUcStr_id']
                    ));
                }*/

                if (!empty($s_data->BarCode_Data)) {
                    //разбираем массив со штрих кодами на отдельные элементы и копируем в новую строку документа учета
                    $bc_arr = explode(',', $s_data->BarCode_Data); //коды передаются в виде списка конструкций вида "идентификатор|код" перечисленных через запятую
                    foreach($bc_arr as $bc_item) {
                        $bc_data = preg_split('/\|/', $bc_item);
                        if (is_array($bc_data) && count($bc_data) == 2 && !empty($bc_data[0]) && !empty($bc_data[1])) {
                            $bc_data = $this->copyObject('DrugPackageBarCode', array(
                                'DrugPackageBarCode_id' => $bc_data[0],
                                'DocumentUcStr_id' => $dus_data[0]['DocumentUcStr_id']
                            ));
                        }
                    }
                }
            }

		}

        //коммит транзакции
		$this->commitTransaction();

		return array(array('Error_Msg' => null));
	}

	/**
	 * Обеспечение общего (не льготного) рецепта
	 */
	function provideEvnReceptGeneral($data) {
		//старт транзакции
		$this->beginTransaction();

		if (empty($data['session']['Contragent_id'])) {
			return array(array('Error_Msg' => 'Отсутствуют данные контрагента.'));
		}

		//получаем данные текущего пользователя
		$org_id = $data['session']['org_id'];
		$contragent_id = $data['session']['Contragent_id'];
		$cur_date = new DateTime();

		//получаем данные общего характера
		$query = "
			declare
				@SubAccountType_id bigint,
				@DrugDocumentType_id bigint,
				@DrugDocumentStatus_id bigint,
				@PacientContragent_id bigint,
				@Storage_id bigint,
				@Yes_id bigint;

			set @SubAccountType_id = (select top 1 SubAccountType_id from v_SubAccountType with(nolock) where SubAccountType_Code = 1); --Доступно
			set @DrugDocumentType_id = (select top 1 DrugDocumentType_id from v_DrugDocumentType with(nolock) where DrugDocumentType_SysNick = 'DocReal'); --Реализация
			set @DrugDocumentStatus_id = (select top 1 DrugDocumentStatus_id from v_DrugDocumentStatus with(nolock) where DrugDocumentStatus_Code = 4); --Исполнен
			set @PacientContragent_id = (select top 1 Contragent_id from v_Contragent with(nolock) where Contragent_Code = 1); --Пациент
			set @Storage_id = (select top 1 Storage_id from StorageStructLevel with(nolock) where MedService_id = :MedService_id);
			set @Yes_id = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = 1);

			select
				@SubAccountType_id as SubAccountType_id,
				@DrugDocumentType_id as DrugDocumentType_id,
				@DrugDocumentStatus_id as DrugDocumentStatus_id,
				@PacientContragent_id as PacientContragent_id,
				@Storage_id as Storage_id,
				@Yes_id as Yes_id;
		";
		$common_data = $this->getFirstRowFromQuery($query, array(
			'MedService_id' => $data['MedService_id']
		));
		if ($common_data === false) {
			return array(array('Error_Msg' => 'Не удалось получить данные.'));
		}

		//проверка текущего статуса рецепта
		$query = "
			select
				count(erg.EvnReceptGeneral_id) as cnt
			from
				EvnReceptGeneral erg with(nolock)
				left join v_ReceptDelayType rdt with(nolock) on rdt.ReceptDelayType_id = erg.ReceptDelayType_id
			where
				erg.EvnReceptGeneral_id = :EvnReceptGeneral_id and
				rdt.ReceptDelayType_Code = 0;
		";
		$params = array(
			'EvnReceptGeneral_id' => $data['EvnReceptGeneral_id']
		);
		$result = $this->getFirstResultFromQuery($query, $params);
		if ($result !== false) {
			if ($result > 0) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Рецепт уже обеспечен.'));
			}
		} else {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при обращении к базе данных.'));
		}

		//изменение статуса рецепта
		$query = "
			declare
				@ReceptDelayType_id bigint,
				@OrgFarmacy_id bigint,
				@EvnReceptGeneral_obrDT datetime;

			set @ReceptDelayType_id = (select top 1 ReceptDelayType_id from v_ReceptDelayType with(nolock) where ReceptDelayType_Code = 0);
			set @OrgFarmacy_id = (select top 1 OrgFarmacy_id from v_OrgFarmacy with(nolock) where Org_id = :Org_id);
			set @EvnReceptGeneral_obrDT = (select top 1 isnull(EvnReceptGeneral_obrDT, dbo.tzGetDate()) from v_EvnReceptGeneral with(nolock) where EvnReceptGeneral_id = :EvnReceptGeneral_id);

			update
				Evn
			set
				Evn_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				Evn_id = :EvnReceptGeneral_id;

			update
				EvnReceptGeneral
			set
				ReceptDelayType_id = @ReceptDelayType_id,
				OrgFarmacy_oid = @OrgFarmacy_id,
				EvnReceptGeneral_obrDT = @EvnReceptGeneral_obrDT,
				EvnReceptGeneral_otpDT = dbo.tzGetDate()
			where
				EvnReceptGeneral_id = :EvnReceptGeneral_id;
		";
		$result = $this->db->query($query, array(
			'Org_id' => $data['session']['org_id'],
			'EvnReceptGeneral_id' => $data['EvnReceptGeneral_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		//получаем данные рецепта
		$query = "
			select
				erg.EvnReceptGeneral_id,
				erg.EvnReceptGeneral_Guid,
				erg.Person_id,
				ps.Person_Snils,
				erg.PrivilegeType_id,
				erg.Lpu_id,
				l.Lpu_Ogrn,
				erg.MedPersonal_id,
				erg.Diag_id,
				erg.EvnReceptGeneral_Ser,
				erg.EvnReceptGeneral_Num,
				convert(varchar(10), erg.EvnReceptGeneral_setDT, 120) as EvnReceptGeneral_setDT,
				convert(varchar(10), erg.EvnReceptGeneral_obrDT, 120)+' '+convert(varchar(10), erg.EvnReceptGeneral_obrDT, 108) as EvnReceptGeneral_obrDT,
				convert(varchar(10), erg.EvnReceptGeneral_otpDT, 120)+' '+convert(varchar(10), erg.EvnReceptGeneral_otpDT, 108) as EvnReceptGeneral_otpDT,
				erg.ReceptFinance_id,
				erg.OrgFarmacy_oid,
				erg.Drug_rlsid as Drug_id,
				dn.DrugNomen_Code as Drug_Code,
				erg.EvnReceptGeneral_Kolvo,
				erg.ReceptDelayType_id,
				erg.EvnReceptGeneral_Is7Noz,
				erg.DrugFinance_id,
				erg.WhsDocumentCostItemType_id,
				erg.EvnReceptGeneral_Kolvo,
				erg.WhsDocumentUc_id
			from
				v_EvnReceptGeneral erg with(nolock)
				left join v_PersonState ps with(nolock) on ps.Person_id = erg.Person_id
				left join v_Lpu l with(nolock) on l.Lpu_id = erg.Lpu_id
				outer apply (
					select top 1
						DrugNomen_Code
					from
						rls.v_DrugNomen dn with(nolock)
					where
						dn.Drug_id = erg.Drug_rlsid
				) dn
			where
				erg.EvnReceptGeneral_id = :EvnReceptGeneral_id;
		";
		$params = array(
			'EvnReceptGeneral_id' => $data['EvnReceptGeneral_id']
		);
		$recept_data = $this->getFirstRowFromQuery($query, $params);
		if ($recept_data === false) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить данные о рецепте.'));
		}


		//получаем данные о выбранных сериях и строках регистра остатков
		$series_data = array();
		if (!empty($data['DrugOstatDataJSON'])) {
			$series_data = (array) json_decode($data['DrugOstatDataJSON']);
			$ost_kolvo = 0;
			foreach($series_data as &$s_data) {
                $coeff = !empty($s_data->WhsDocumentSupplySpecDrug_Coeff) && $s_data->WhsDocumentSupplySpecDrug_Coeff > 0 ? $s_data->WhsDocumentSupplySpecDrug_Coeff : 1; //коэфицент пересчета количества для синонимов
                $ost_kolvo += $s_data->Kolvo/$coeff;
			}
			if ($recept_data['EvnReceptGeneral_Kolvo'] != $ost_kolvo) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Суммарное количество медикамента для выбранных серий, не соответствует количеству в рецепте.'));
			}
		}
		if (count($series_data) < 1) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить данные о выбранных сериях.'));
		}

		//списываем медикамент с остатков и суммируем количество по ценам
		//$price_array = array();
		foreach($series_data as &$s_data) {
			$query = "
				declare
					@Contragent_id bigint,
					@Org_id bigint,
					@DrugShipment_id bigint,
					@Drug_id bigint,
					@PrepSeries_id bigint,
					@SubAccountType_id bigint,
					@Okei_id bigint,
					@DrugOstatRegistry_Kolvo float,
					@DrugOstatRegistry_Sum float,
					@Storage_id bigint,
					@DrugOstatRegistry_Cost numeric(18,2),
					@ErrCode int,
					@ErrMessage varchar(4000);

				select
					@Contragent_id = Contragent_id,
					@Org_id = Org_id,
					@DrugShipment_id = DrugShipment_id,
					@Drug_id = Drug_id,
					@PrepSeries_id = PrepSeries_id,
					@SubAccountType_id = SubAccountType_id,
					@Okei_id = Okei_id,
					@DrugOstatRegistry_Kolvo = DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = DrugOstatRegistry_Sum,
					@Storage_id = Storage_id,
					@DrugOstatRegistry_Cost = DrugOstatRegistry_Cost
				from
					v_DrugOstatRegistry
				where
					DrugOstatRegistry_id = :DrugOstatRegistry_id;

				if (@DrugOstatRegistry_Kolvo > 0  and @DrugOstatRegistry_Kolvo >= :DrugOstatRegistry_Kolvo)
				begin
					set @DrugOstatRegistry_Sum = @DrugOstatRegistry_Cost*:DrugOstatRegistry_Kolvo*(-1);
					set @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo*(-1);

					exec xp_DrugOstatRegistry_count
						@Contragent_id = @Contragent_id,
						@Org_id = @Org_id,
						@DrugShipment_id = @DrugShipment_id,
						@Drug_id = @Drug_id,
						@PrepSeries_id = @PrepSeries_id,
						@SubAccountType_id = @SubAccountType_id,
						@Okei_id = @Okei_id,
						@DrugOstatRegistry_Kolvo = @DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = @DrugOstatRegistry_Sum,
						@Storage_id = @Storage_id,
						@DrugOstatRegistry_Cost = @DrugOstatRegistry_Cost,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end;
				else
				begin
					set @ErrMessage = (
						select top 1
							isnull(
								d.Drug_Name+', '+
								ps.PrepSeries_Ser+', '+
								convert(varchar(10), ps.PrepSeries_GodnDate, 104)+', '+
								'№ '+ds.DrugShipment_Name+
								' – '+cast(dor.DrugOstatRegistry_Kolvo as varchar)+' шт. '+
								'недостаточно ЛП на остатках аптеки.   Рецепт не обеспечен. Выполните обеспечение рецепта с другой серией.',
								'Для обеспечения рецепта недостаточно медикаментов'
							) as msg
						from
							v_DrugOstatRegistry dor with (nolock)
							left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
							left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
							left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
						where
							dor.DrugOstatRegistry_id = :DrugOstatRegistry_id
					)
				end;

				select @Drug_id as Drug_id, @DrugOstatRegistry_Cost as DrugOstatRegistry_Cost, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'DrugOstatRegistry_id' => $s_data->DrugOstatRegistry_id,
				'DrugOstatRegistry_Kolvo' => $s_data->Kolvo,
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->getFirstRowFromQuery($query, $params);
			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					$this->rollbackTransaction();
					return array($result);
				} else {
					$s_data->Drug_id = $result['Drug_id'];
					$s_data->DrugOstatRegistry_Cost = $result['DrugOstatRegistry_Cost'];

                    /*if ($s_data->Drug_id > 0) {
                        if(!isset($price_array[$s_data->Drug_id])) {
                            $price_array[$s_data->Drug_id] = array();
                        }
                        if ($s_data->DrugOstatRegistry_Cost > 0) {
                            if(!isset($price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_Cost])) {
                                $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_Cost] = array('kolvo' => 0);
                            }
                            $price_array[$s_data->Drug_id][$s_data->DrugOstatRegistry_Cost]['kolvo'] += $s_data->Kolvo;
                        }
                    }*/
				}
			} else {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Не удалось списать медикаменты с регистра остатков.'));
			}
		}

		//создаем документ реализации
		$doc_id = 0;
		$response = $this->saveDocumentUc(array(
			'DocumentUc_Num' => $recept_data['EvnReceptGeneral_Num'],
			'DocumentUc_didDate' => $cur_date->format('Y-m-d'),
			'DocumentUc_setDate' => $cur_date->format('Y-m-d'),
			'DocumentUc_DogNum' => null,
			'DocumentUc_DogDate' => null,
			'Org_id' => $org_id,
			'Contragent_id' => $contragent_id,
			'Contragent_sid' => $contragent_id,
			'Contragent_tid' => $common_data['PacientContragent_id'],
			'DrugFinance_id' => $recept_data['DrugFinance_id'],
			'DrugDocumentType_id' => $common_data['DrugDocumentType_id'],
			'DrugDocumentStatus_id' => $common_data['DrugDocumentStatus_id'],
			'SubAccountType_sid' => $common_data['SubAccountType_id'],
			'Storage_sid' => $common_data['Storage_id'],
			'pmUser_id' => $data['pmUser_id'],
			'WhsDocumentCostItemType_id' => $recept_data['WhsDocumentCostItemType_id']
		));
		if (is_array($response) && count($response) > 0 && isset($response[0]['DocumentUc_id']) && $response[0]['DocumentUc_id'] > 0) {
			$doc_id = $response[0]['DocumentUc_id'];
		}
		if ($doc_id <= 0) {
			$this->rollbackTransaction();
			return array(0 => array('Error_Msg' => 'Не удалось создать документ реализации'));
		}

		//получение коэфицентов для рассчета суммы НДС
		$nds_koef = array();
		$query = "
			select
				DrugNds_id,
				1-(100/(100.0+DrugNds_Code)) as koef
			from
				v_DrugNds
		";
		$result = $this->db->query($query);
		if (is_object($result)) {
			$result = $result->result('array');
			foreach($result as $nds_data) {
				$nds_koef[$nds_data['DrugNds_id']] = $nds_data['koef'];
			}
		}

		//формирование спецификации документа реализации
		foreach($series_data as &$s_data) {
			//обрабатываем дату
			$godn_date = !empty($s_data->PrepSeries_GodnDate) ? join(array_reverse(preg_split('/[.]/',$s_data->PrepSeries_GodnDate)),'-') : null;

			//рассчитываем суммы
			$sum = $s_data->DrugOstatRegistry_Cost > 0 ? $s_data->DrugOstatRegistry_Cost*$s_data->Kolvo : null;

			$this->saveDocumentUcStr(array(
				'DocumentUc_id' => $doc_id,
				'DocumentUcStr_oid' => $s_data->DocumentUcStr_id,
				'Drug_id' => $s_data->Drug_id,
				'DrugFinance_id' => $recept_data['DrugFinance_id'],
				'DocumentUcStr_Price' => $s_data->DrugOstatRegistry_Cost,
				'DocumentUcStr_PriceR' => $s_data->DrugOstatRegistry_Cost,
				'DrugNds_id' => $s_data->DrugNds_id,
				'DocumentUcStr_Count' => $s_data->Kolvo,
				'DocumentUcStr_Sum' => $sum,
				'DocumentUcStr_SumR' => $sum,
				'DocumentUcStr_SumNds' => isset($nds_koef[$s_data->DrugNds_id]) ? round($sum*$nds_koef[$s_data->DrugNds_id], 2) : 0,
				'DocumentUcStr_SumNdsR' => isset($nds_koef[$s_data->DrugNds_id]) ? round($sum*$nds_koef[$s_data->DrugNds_id], 2) : 0,
				'DocumentUcStr_godnDate' => $godn_date,
				'DocumentUcStr_NZU' => 1,
				'DocumentUcStr_Ser' => $s_data->PrepSeries_Ser,
				'PrepSeries_id' => $s_data->PrepSeries_id,
				'EvnRecept_id' => null,
				'ReceptOtov_id' => null,
				'DocumentUcStr_IsNDS' => $common_data['Yes_id'],
                'GoodsUnit_bid' => !empty($s_data->GoodsUnit_id) ? $s_data->GoodsUnit_id : null,
				'pmUser_id' => $data['pmUser_id']
			));
		}

		//коммит транзакции
		$this->commitTransaction();

		return array(array('Error_Msg' => null));
	}

	/**
	 * Получение списка остатков для обеспечения рецепта
	 */
	function getDrugOstatForProvide($data) {
		$where = array();

		if (!empty($data['DrugOstatRegistry_id']) && $data['DrugOstatRegistry_id'] > 0) {
			$where[] = 'dus.DrugOstatRegistry_id = :DrugOstatRegistry_id';
		} else {
			if (!empty($data['EvnRecept_id']) && $data['EvnRecept_id'] > 0) {
				$result = array();

				if (!empty($data['EvnReceptGeneral_id']) && $data['EvnReceptGeneral_id'] > 0) {
					$q = "
						select
							erg.Drug_rlsid,
							erg.DrugComplexMnn_id,
							wds.WhsDocumentSupply_id,
							erg.DrugFinance_id,
							erg.WhsDocumentCostItemType_id
						from
							v_EvnReceptGeneral erg with (nolock)
							left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = erg.WhsDocumentUc_id
						where
							erg.EvnReceptGeneral_id = :EvnReceptGeneral_id;
					";
					$result = $this->getFirstRowFromQuery($q, array('EvnReceptGeneral_id' => $data['EvnReceptGeneral_id']));
				} else {
					$q = "
						select
							er.Drug_rlsid,
							er.DrugComplexMnn_id,
							wds.WhsDocumentSupply_id,
							er.DrugFinance_id,
							er.WhsDocumentCostItemType_id
						from
							v_EvnRecept er with (nolock)
							left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = er.WhsDocumentUc_id
						where
							er.EvnRecept_id = :EvnRecept_id;
					";
					$result = $this->getFirstRowFromQuery($q, array('EvnRecept_id' => $data['EvnRecept_id']));
				}

				if (is_array($result) && count($result) > 0) {
					if ($result['Drug_rlsid'] > 0) {
						$where[] = 'dor.Drug_id = :Drug_id';
						$data['Drug_id'] = $result['Drug_rlsid'];
					} else {
						$where[] = 'd.DrugComplexMnn_id = :DrugComplexMnn_id';
						$data['DrugComplexMnn_id'] = $result['DrugComplexMnn_id'];
					}
					if ($result['WhsDocumentSupply_id'] > 0) {
						$where[] = 'dor.DrugShipment_id in (select DrugShipment_id from v_DrugShipment with (nolock) where WhsDocumentSupply_id = :WhsDocumentSupply_id)';
						$data['WhsDocumentSupply_id'] = $result['WhsDocumentSupply_id'];
					} else {
						$where[] = 'dor.DrugFinance_id = :DrugFinance_id';
						$where[] = 'dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
						$data['DrugFinance_id'] = $result['DrugFinance_id'];
						$data['WhsDocumentCostItemType_id'] = $result['WhsDocumentCostItemType_id'];
					}
				}
			}

			if (!empty($data['MedService_id']) && $data['MedService_id'] > 0) {
				$where[] = 'dor.Storage_id in (
					select Storage_id from StorageStructLevel with(nolock) where MedService_id = :MedService_id
				)';
			}

			$where[] = 'dor.DrugOstatRegistry_Kolvo > 0';
		}

		$q = "
			select
				dor.DrugOstatRegistry_id,
				dor.DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Cost,
				dus.DocumentUcStr_id,
				ds.DrugShipment_Name,
				substring(d.Drug_Name, 0, 15)+'...' as Drug_ShortName,
				d.Drug_Name,
				ps.PrepSeries_id,
				ps.PrepSeries_Ser,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
				'упак' as Okei_NationSymbol,
				dus.DocumentUcStr_Price,
				isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				dus.DocumentUcStr_Sum,
				dn.DrugNds_id,
				dn.DrugNds_Code,
				dus.DocumentUcStr_SumNds,
				isnull(dus.DocumentUcStr_Sum, 0) + isnull(dus.DocumentUcStr_SumNds, 0) as DocumentUcStr_NdsSum,
				isnull(df.DrugFinance_Name, '') as DrugFinance_Name,
				isnull(wdcit.WhsDocumentCostItemType_Name, '') as WhsDocumentCostItemType_Name
			from
				v_DrugOstatRegistry dor with (nolock)
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = dor.DrugShipment_id
				left join rls.v_Drug d with(nolock) on d.Drug_id = dor.Drug_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = du.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
		";

		if (count($where) > 0) {
			$q .= " where ".join($where, " and ");
		}

		//print getDebugSQL($q, $data);
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

    /**
     * Получение списка остатков для обеспечения рецепта
     */
    function getDrugOstatForProvideFromBarcode($data) {
        $this->load->model('DocumentUc_model', 'DocumentUc_model');
        $default_goods_unit_id = $this->DocumentUc_model->getDefaultGoodsUnitId();
        $and = '';
        $and_drug = '';
        $select = '';
        $join = '';
        $nomen_join = '';

        $data['DefaultGoodsUnit_id'] = $default_goods_unit_id;

        //определяем по идентификатору рецепта оригинальный медикамент
        $data['Drug_rlsid'] = null;
        if (!empty($data['EvnRecept_id'])) {
            $query = "
                select
                    Drug_rlsid
                from
                    v_EvnRecept with (nolock)
                where
                    EvnRecept_id = :EvnRecept_id
            ";
            $data['Drug_rlsid'] = $this->getFirstResultFromQuery($query, array(
                'EvnRecept_id' => $data['EvnRecept_id']
            ));
        }

        if (!empty($data['MedService_id']) && $data['MedService_id'] > 0) {
            $and = ' and dor.Storage_id in (
					select Storage_id from StorageStructLevel with(nolock) where MedService_id = :MedService_id
				)';
        }
        if(isset($data['Sin_check']) && $data['Sin_check'] == '1') {
            $and_drug .= '';//' and d.DrugComplexMnn_id = :DrugComplexMnn_id';
        }
        else
        {
            $and_drug .= ' and d.DrugComplexMnn_id = :DrugComplexMnn_id';
        }
        if(isset($data['Drug_ean']) && $data['Drug_ean'] != '') {
            $and_drug .= ' and d.drug_ean = :Drug_ean';
        }

        if(isset($data['Drug_id']) && $data['Drug_id'] != '') {
            $and_drug .= ' and d.Drug_id = :Drug_id';
        }

        if(isset($data['DrugFinance_id']) && $data['DrugFinance_id'] != '') {
            $and .= ' and dor.DrugFinance_id = :DrugFinance_id';
        }

        if(isset($data['WhsDocumentCostItemType']) && $data['WhsDocumentCostItemType'] != '') {
            $and .= ' and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType';
        }

        if(!empty($data['DrugPackageBarCode_BarCode'])) {
            //проуерка не выбыл ли из системы данный штрих-код
            $query = "
                select
                    count(dpbc.DrugPackageBarCode_id) as cnt
                from
                    v_DrugPackageBarCode dpbc with (nolock)
                    left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dpbc.DocumentUcStr_id
                    left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                    left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                where
                    dpbc.DrugPackageBarCode_BarCode = :DrugPackageBarCode_BarCode and
                    ddt.DrugDocumentType_Code in (2, 11) -- 2 - Документ списания медикаментов; 11 - Реализация
            ";
            $bc_cnt = $this->getFirstResultFromQuery($query, array(
                'DrugPackageBarCode_BarCode' => $data['DrugPackageBarCode_BarCode']
            ));

            if (empty($bc_cnt)) { //если штрих-код еще не выбыл
                $select .= ', dpbc.DrugPackageBarCode_id';
                $join .= '
                    outer apply (
                        select top 1
                            i_dpbc.DrugPackageBarCode_id
                        from
                            v_DrugPackageBarCode i_dpbc with (nolock)
                        where
                            i_dpbc.DocumentUcStr_id = dus.DocumentUcStr_id and
                            i_dpbc.DrugPackageBarCode_BarCode = :DrugPackageBarCode_BarCode
                    ) dpbc
                ';
                //$and .= ' and dpbc.DrugPackageBarCode_id is not null';
                $and .= ' and gu.GoodsUnit_id = :DefaultGoodsUnit_id'; //при поиске по штрих-коду берем строки регистра только с упаковкой в качестве ед. учета
                $and .= ' and dus.DocumentUcStr_id in (
                    select
                        i_dpbc.DocumentUcStr_id
                    from
                        v_DrugPackageBarCode i_dpbc with (nolock)
                    where
                        i_dpbc.DrugPackageBarCode_BarCode = :DrugPackageBarCode_BarCode
                )';
            } else {
                return array();
            }
        }

		/*if(isset($data['DrugComplexMnn_id']) && $data['DrugComplexMnn_id'] != '') {
			$and .= ' and d.DrugComplexMnn_id = :DrugComplexMnn_id';
		}*/

        if(isset($data['query'])) {
            $data['query'] = "%".$data['query']."%";
            $and_drug .= " and ps.PrepSeries_Ser like :query";
        }

        if(isset($data['Drugnomen_Code']) && $data['Drugnomen_Code'] != '') {
            $and_drug .= ' and DNom.DrugNomen_Code = :Drugnomen_Code';
            $nomen_join = 'left join rls.DrugNomen DNom with (nolock) on DNom.Drug_id = D.Drug_id';
        }

		if(isset($data['SubAccountTypeIsReserve'])) {
			$and .= ' and dor.SubAccountType_id != 2';
		}

        $sql = "
            select
                d.Drug_id,
				dor.DrugOstatRegistry_id,
				dor.DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Cost,
				dus.DocumentUcStr_id,
				isnull(ds.DrugShipment_Name, '') as DrugShipment_Name,
				substring(d.Drug_Name, 0, 15)+'...' as Drug_ShortName,
				d.Drug_Name,
				ps.PrepSeries_id,
				isnull(ps.PrepSeries_Ser, '') as PrepSeries_Ser,
				isnull(convert(varchar(10), ps.PrepSeries_GodnDate, 104), '') as PrepSeries_GodnDate,
				isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
				'упак' as Okei_NationSymbol,
				cast(isnull(dus.DocumentUcStr_Price, 0) as decimal(14,2)) as DocumentUcStr_Price,
				isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				dus.DocumentUcStr_Sum,
				dn.DrugNds_id,
				dn.DrugNds_Code,
				dus.DocumentUcStr_SumNds,
				isnull(dus.DocumentUcStr_Sum, 0) + isnull(dus.DocumentUcStr_SumNds, 0) as DocumentUcStr_NdsSum,
				isnull(df.DrugFinance_Name, '') as DrugFinance_Name,
				isnull(wdcit.WhsDocumentCostItemType_Name, '') as WhsDocumentCostItemType_Name,
				df.DrugFinance_Name + ' / ' + wdcit.WhsDocumentCostItemType_Name as Finance_and_CostItem,
				isnull(wdssd.WhsDocumentSupplySpecDrug_Coeff, 1) as WhsDocumentSupplySpecDrug_Coeff,
				gu.GoodsUnit_id,
				gu.GoodsUnit_Nick,
				(case
				    when gu.GoodsUnit_id = :DefaultGoodsUnit_id then 1
				    else isnull(gpc.GoodsPackCount_Count, 1)
				end) as GoodsPackCount_Count
				{$select}
			from
				v_DrugOstatRegistry dor with (nolock)
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = dor.DrugShipment_id
				left join rls.v_Drug d with(nolock) on d.Drug_id = dor.Drug_id
				{$nomen_join}
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = du.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id)
				outer apply (
                    select top 1
                        i_wdssd.Drug_sid,
                        i_wdssd.WhsDocumentSupplySpecDrug_Coeff
                    from
                        v_WhsDocumentSupplySpec i_wdss with (nolock)
                        left join v_WhsDocumentSupplySpecDrug i_wdssd with (nolock) on i_wdssd.WhsDocumentSupplySpec_id = i_wdss.WhsDocumentSupplySpec_id
                    where
                        i_wdssd.Drug_id = :Drug_rlsid and
                        i_wdssd.Drug_sid = d.Drug_id and
                        i_wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    order by
                        i_wdssd.WhsDocumentSupplySpecDrug_id
                ) wdssd
                outer apply (
                    select top 1
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = dor.GoodsUnit_id and
                        i_gpc.DrugComplexMnn_id = d.DrugComplexMnn_id and
                        (
                            d.DrugTorg_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = d.DrugTorg_id
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc, i_gpc.Org_id
                ) gpc
                {$join}
			where
			    (1=1)
			    and dor.DrugOstatRegistry_Kolvo > 0
			    and isnull(isdef.YesNo_Code, 0) = 0
                {$and}
                {$and_drug}
            order by
                ps.PrepSeries_GodnDate desc
        ";
        $result = $this->db->query($sql, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        }

        return false;
    }

	/**
	 * Получение списка медикаментов для отпуска ЛС
	 */
	function getDrugRlsListForProvide($data) {
		$params = array();
		$and = "";
		$params['EvnRecept_id'] = $data['EvnRecept_id'];
		$and_drug = '';
		if(isset($data['query'])) {
			$params['query'] = $data['query'] . "%";
			$and_drug .= " and d.Drug_Name like :query";
		}
		$join = "
			inner join DrugOstatRegistry dor on dor.Drug_id=d.Drug_id
			inner join DrugShipment dsh on dsh.DrugShipment_id=dor.DrugShipment_id
			left join StorageStructLevel ssle on ssle.Storage_id = dor.Storage_id
		";
		$and_where = "
			and dor.WhsdocumentCostItemtype_id=er.WhsdocumentCostItemtype_id
			and COALESCE(er.WhsDocumentUc_id,dsh.WhsDocumentSupply_id,0)=ISNULL(dsh.WhsDocumentSupply_id,0)
		";
		if(isset($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0)
		{
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
			$and_where .= " and dor.DrugFinance_id=:DrugFinance_id";
		}
		if(isset($data['Contragent_id']) && $data['Contragent_id'] > 0)
		{
			$params['Contragent_id'] = $data['Contragent_id'];
			$and_where .= " and dor.Contragent_id=:Contragent_id";
		}
		if(isset($data['MedService_id']) && $data['MedService_id'] > 0)
		{
			$params['MedService_id'] = $data['MedService_id'];
			$and_where .= " and ssle.MedService_id=:MedService_id";
		}
		if(isset($data['Sin_check']) && $data['Sin_check'] == '1') {
			$query = "
				select distinct
				    d.Drug_id,
				    d.Drug_Name,
				    ISNULL(d.Drug_Code,'') as Drug_Code,
				    ISNULL(D.Drug_Ean,'') as Drug_Ean
				from
				    v_EvnRecept ER
					left join rls.v_DrugComplexMnn dcm_t1 on dcm_t1.DrugComplexMnn_id = ER.DrugComplexMnn_id
					left join rls.v_Drug d_t on d_t.Drug_id = er.Drug_rlsid
					left join rls.v_DrugComplexMnn dcm_t2 on dcm_t2.DrugComplexMnn_id = ER.DrugComplexMnn_id
					inner join rls.v_DrugComplexMnnName dcmn_t on dcmn_t.DrugComplexMnnName_id = COALESCE(dcm_t2.DrugComplexMnnName_id, dcm_t1.DrugComplexMnnName_id)
					outer apply (
						select distinct
						    DrugComplexMnnName_id
						from
						    rls.v_DrugComplexMnnName
						where
						    ACTMATTERS_id = dcmn_t.ACTMATTERS_id or
						    TRADENAMES_ID = dcmn_t.TRADENAMES_ID
					) dcmn
					inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
					inner join rls.v_Drug d on d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					{$join}
				where
				    ER.EvnRecept_id = :EvnRecept_id
					{$and_where}
					{$and_drug}
			";
		}
		else
		{
			$query = "
				select
				    d.Drug_id,
				    d.Drug_Name,
				    ISNULL(d.Drug_Code,'') as Drug_Code,
				    ISNULL(D.Drug_Ean,'') as Drug_Ean
				from
				    v_EvnRecept er (nolock)
				    --inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = ER.DrugComplexMnn_id
				    --inner join rls.v_Drug D on D.DrugComplexMnn_id = dcm.DrugComplexMnn_id
				    inner join rls.v_Drug D on (D.DrugComplexMnn_id = ER.DrugComplexMnn_id or D.Drug_id = ER.Drug_rlsid)
    				{$join}
				where
				    er.EvnRecept_id = :EvnRecept_id
				    {$and_where}
				    {$and_drug}
			";
		}

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Получение данных контрагента по отделению ЛПУ
	 */
	function getLpuSectionContragent($data) {
		$query = "
			select top 1
				C.Contragent_id,
				C.Contragent_Code,
				CT.ContragentType_Code
			from
				v_Contragent C with(nolock)
				inner join v_ContragentType CT with(nolock) on CT.ContragentType_id=C.ContragentType_id
				inner join ContragentOrg CO with (nolock) on CO.Contragent_id = C.Contragent_id
			where
				CT.ContragentType_SysNick = 'lpu'
				and C.Lpu_id = :Lpu_id
				and C.LpuSection_id = :LpuSection_id
		";
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response)!=0) {
				return array('data'=>$response[0]);
			}
		}
		return false;
	}

	/**
	 * Создание документа учета для списания реактивов.
	 */
	function createDocumentForReagentConsumption($data) {
		$doc_id = 0;
		$usluga_list = (array) json_decode($data['UslugaListJSON']);

		//получаем данные общего характера
		$query = "
			select
				ls.Lpu_id,
				l.Org_id,
				c.Contragent_id
			from
				v_LpuSection ls with (nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = ls.Lpu_id
				inner join v_Contragent c with (nolock) on c.Lpu_id = ls.Lpu_id and c.LpuSection_id = ls.LpuSection_id
			where
				ls.LpuSection_id = :LpuSection_id;
		";
		$lpu_data = $this->getFirstRowFromQuery($query, $data);
		if ($lpu_data === false) {
			return array(array('Error_Msg' => 'Не удалось получить данные о ЛПУ.'));
		}

		$date = date("Y-m-d");

		//старт транзакции
		$this->beginTransaction();

		$save_data = array(
			'DocumentUc_setDate' => isset($data['Date']) && !empty($data['Date']) ? $data['Date'] : $date,
			'DocumentUc_didDate' => $date,
			'Lpu_id' => $lpu_data['Lpu_id'],
			'Org_id' => $lpu_data['Org_id'], // id аптеки
			'SubAccountType_sid' => 1, // доступно
			'SubAccountType_tid' => 1, // доступно
			'Contragent_id' => $lpu_data['Contragent_id'], //аптека
			'Contragent_sid' => $lpu_data['Contragent_id'], //поставщик
			'Mol_sid' => null,
			'Contragent_tid' => null, //аптека
			'Mol_tid' => null,
			'DrugDocumentType_SysNick' => 'DokSpis', //Приходная накладная
			'DrugFinance_id' => $data['DrugFinance_id'],
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'DrugDocumentStatus_id' => 1, //Новый
			'pmUser_id' => $data['pmUser_id']
		);
		$result = $this->saveDocumentUc($save_data);
		if (is_array($result)) {
			if (!empty($result[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => $result[0]['Error_Msg']));
			} else {
				if (isset($result[0]['DocumentUc_id']) && $result[0]['DocumentUc_id'] > 0) {
					$doc_id = $result[0]['DocumentUc_id'];
				}
			}
		}
		if ($doc_id <= 0) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось создать документ учета.'));
		}

		$drugs = array();
		foreach($usluga_list as $usluga) {
			//Получаем список реактивов и количество для каждой конкретной услуги
			$query = "
				select
					dn.Drug_id,
					NormCostItem_Kolvo as Kolvo
				from
					dbo.v_NormCostItem nci
					inner join rls.v_DrugNomen dn with(nolock) on dn.DrugNomen_id = nci.DrugNomen_id
				where
					nci.UslugaComplex_id = :UslugaComplex_id;
			";
			$params = array(
				'UslugaComplex_id' => $usluga->UslugaComplex_id
			);
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				$drugs_arr = $result->result('array');
				foreach($drugs_arr as $drug) {
					if (!isset($drugs[$drug['Drug_id']])) {
						$drugs[$drug['Drug_id']] = array(
							'kolvo' => 0
						);
					}
					//Суммируем количество по конкретному медикаменту
					$drugs[$drug['Drug_id']]['kolvo'] += $usluga->Kolvo*$drug['Kolvo'];
				}
			}
		}
		if (count($drugs) == 0) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Не удалось получить список медикаментов для списания.'));
		}

		//Получаем доступные остатки для каждого медикамента
		foreach($drugs as $key=>$value) {
			$ost_data = array();
			$query = "
				declare
					@SubAccountType_id bigint,
					@No bigint;

				set @SubAccountType_id = (select SubAccountType_id from v_SubAccountType with(nolock) where SubAccountType_Code = 1); --Доступно
				set @No = (select YesNo_id from v_YesNo with(nolock) where YesNo_Code = 0); --Нет

				select
					ps.PrepSeries_id,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 120) as PrepSeries_GodnDate,
					dor.DrugOstatRegistry_Kolvo as Available_Kolvo,
					ltrim(rtrim(str(dor.DrugOstatRegistry_Sum/dor.DrugOstatRegistry_Kolvo, 10, 2))) as Price
				from
					v_DrugOstatRegistry dor with (nolock)
					inner join rls.v_PrepSeries ps with (nolock)on ps.PrepSeries_id = dor.PrepSeries_id
				where
					dor.SubAccountType_id = @SubAccountType_id and
					dor.DrugOstatRegistry_Kolvo > 0 and
					isnull(ps.PrepSeries_IsDefect, @No) = @No and
					ps.PrepSeries_GodnDate >= dbo.tzGetDate()
					and dor.Org_id = :Org_id
					and dor.Drug_id = :Drug_id
					and dor.DrugFinance_id = :DrugFinance_id
					and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
				order by
					ps.PrepSeries_GodnDate;
			";
			$params = array(
				'Org_id' => $lpu_data['Org_id'],
				'Drug_id' => $key,
				'DrugFinance_id' => $data['DrugFinance_id'],
				'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
			);
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				$ost_data = $result->result('array');
			}
			$drugs[$key]['ost_data'] = $ost_data;
		}

		//Генерируем спецификацию по всем медикаментам и нужным сериям
		foreach($drugs as $drug_id=>$drug_data) {
			foreach($drug_data['ost_data'] as $ost) {
				$kolvo = $ost['Available_Kolvo'] < $drugs[$drug_id]['kolvo'] ? $ost['Available_Kolvo'] : $drugs[$drug_id]['kolvo'];
				$sum = $kolvo*$ost['Price'];

				$drugs[$drug_id]['kolvo'] -= $kolvo;

				//print "<br>Списание. Drug_id:{$drug_id};  Seria:{$ost['PrepSeries_Ser']}; Kolvo:{$kolvo} из {$ost['Available_Kolvo']}; Sum:{$sum}; Осталось списать:{$drugs[$drug_id]['kolvo']};";
				$save_data = array(
					'DocumentUcStr_id' => null,
					'DocumentUcStr_oid' => null,
					'DocumentUc_id' => $doc_id,
					'Drug_id' => $drug_id,
					'DrugFinance_id' => $data['DrugFinance_id'],
					'DocumentUcStr_Price' => $ost['Price'],
					'DocumentUcStr_PriceR' => $ost['Price'],
					'DocumentUcStr_Count' => $kolvo,
					'DrugNds_id' => null,
					'DocumentUcStr_EdCount' => null,
					'DocumentUcStr_SumR' => $sum,
					'DocumentUcStr_Sum' => $sum,
					'DocumentUcStr_SumNds' => $sum,
					'DocumentUcStr_SumNdsR' => $sum,
					'DocumentUcStr_godnDate' => $ost['PrepSeries_GodnDate'],
					'DocumentUcStr_Ser' => $ost['PrepSeries_Ser'],
					'DocumentUcStr_NZU' => null,
					'DocumentUcStr_IsLab' => null,
					'DrugProducer_id' => null,
					'DrugLabResult_Name' => null,
					'DocumentUcStr_CertNum' => null,
					'PrepSeries_id' => $ost['PrepSeries_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->saveDocumentUcStr($save_data);
				if (is_array($result) && !empty($result[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $result[0]['Error_Msg']));
				}

				if ($drugs[$drug_id]['kolvo'] <= 0) {
					break;
				}
			}
			if ($drugs[$drug_id]['kolvo'] > 0) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Недостаточно медикаментов для списания.'));
			}
		}

		//коммит транзакции
		$this->commitTransaction();

		return array(array('DocumentUc_id' => $doc_id));
	}

	/**
	 * Получение аптеки по идентификатору организации
	 */
	function getOrgFarmacyByOrgId($org_id) {
		if (!empty($org)) {
			$query = "
				select
					OrgFaramacy_id
				from
					OrgFaramacy with (nolock)
				where
					Org_id = :Org_id;
			";
			$r = $this->db->query($query, array(
				'Org_id' => $org_id
			));
			if (is_object($r)) {
				$r = $r->result('array');
				return $r;
			}
		}
		return false;
	}

	/**
	 * Сохранение изменеиная статуса документы в истории
	 */
	function saveDrugDocumentStatusHistory($data) {
		$params = array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'DrugDocumentStatus_id' => !empty($data['DrugDocumentStatus_id']) ? $data['DrugDocumentStatus_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@DrugDocumentStatusHistory_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000),
				@setDate datetime;
			set @setDate = dbo.tzGetDate();
			exec p_DrugDocumentStatusHistory_ins
				@DrugDocumentStatusHistory_id output,
				@DocumentUc_id = :DocumentUc_id,
				@DrugDocumentStatus_id = :DrugDocumentStatus_id,
				@DrugDocumentStatusHistory_setDate = @setDate,
				@pmUser_userID = :pmUser_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @DrugDocumentStatusHistory_id as DrugDocumentStatusHistory_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Изменение статуса документа
	 */
	function setDocumentUcStatus($data) {
		$query = "
			declare
				@DocumentUc_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				update
					DocumentUc with(rowlock)
				set
					DrugDocumentStatus_id = :DrugDocumentStatus_id
				where
					DocumentUc_id = :DocumentUc_id;
				set nocount off;
				set @DocumentUc_id = :DocumentUc_id;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @DocumentUc_id as DocumentUc_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Получение конфига для меню изменения статуса документа
	 */
	function getAllowedDrugDocumentStatusConfig($data) {
		$where = '';

		if ($data['DrugDocumentType_id'] == 9) {
			$allowedStatusMap = array(
				'c_0' => array(6),
				'c_6' => array(0, 7, 10),
				'c_7' => array(6, 8, 9, 10),
				'c_8' => array(),
				'c_9' => array(7),
				'c_10' => array(
					'prev' => array(
						'c_6' => array(6),
						'c_7' => array(7)
					)
				)
			);
		} else {
			return array('success' => false);
		}

		$currStatus = 'c_0';
		$prevStatus = null;
		if (!empty($data['DocumentUc_id'])) {
			$query = "
				select top 2
					(case
						when DDS.DrugDocumentStatus_Code is null then 0
						else DDS.DrugDocumentStatus_Code
					end) as DrugDocumentStatus_Code
				from
					v_DrugDocumentStatusHistory DDSH with(nolock)
					left join v_DrugDocumentStatus DDS with(nolock) on DDS.DrugDocumentStatus_id = DDSH.DrugDocumentStatus_id
				where
					DDSH.DocumentUc_id = :DocumentUc_id
				order by DDSH.DrugDocumentStatusHistory_setDate desc
			";
			$params = array('DocumentUc_id' => $data['DocumentUc_id']);
			$result = $this->db->query($query, $params);

			if (!is_object($result)) {
				return array('Error_Msg' => 'Ошибка при запросе истории изменения статусов');
			}
			$statusHistory = $result->result('array');

			if (!is_array($statusHistory) || count($statusHistory) == 0) {
				$statusHistory[0] = array('DrugDocumentStatus_Code' => 0);
			}
			$currStatus = 'c_'.$statusHistory[0]['DrugDocumentStatus_Code'];
			$prevStatus = !isset($statusHistory[1]) ? null : 'c_'.$statusHistory[1]['DrugDocumentStatus_Code'];
		}

		$statusList = $allowedStatusMap['c_0'];
		if (!isset($allowedStatusMap[$currStatus]['prev'])) {
			$statusList = $allowedStatusMap[$currStatus];
		}
		else {
			if ($prevStatus && isset($allowedStatusMap[$currStatus]['prev'][$prevStatus])) {
				$statusList = $allowedStatusMap[$currStatus]['prev'][$prevStatus];
			}
		}
		$statusList[] = (int) substr($currStatus, 2);

		$response = array(
			'data' => array(),
			'allowBlank' => true,
			'disabled' => false,
			'success' => true
		);

		if (empty($statusList)) {
			$response['disabled'] = true;
		}
		else {
			if (count($statusList) == 1) {
				$response['disabled'] = true;
			}

			if (in_array(0, $statusList)) {
				$response['allowBlank'] = true;
			} else {
				$response['allowBlank'] = false;
			}

			$params = array('DrugDocumentType_id' => $data['DrugDocumentType_id']);
			$statusList_str = implode(',', $statusList);
			$query = "
				select
					DDS.DrugDocumentStatus_id,
					DDS.DrugDocumentStatus_Code,
					DDS.DrugDocumentStatus_Name
				from
					v_DrugDocumentStatus DDS with(nolock)
				where
					DDS.DrugDocumentStatus_Code in ({$statusList_str})
					and DDS.DrugDocumentType_id = :DrugDocumentType_id
			";
			$result = $this->db->query($query, $params);

			if (!is_object($result)) {
				return array('Error_Msg' => 'Ошибка при запросе списка статусов', 'success' => false);
			}
			$resp_arr = $result->result('array');

			foreach($resp_arr as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$response['data'][] = $row;
			}
		}

		return $response;
	}

	/**
	 * Получение истории изменения статуса документа
	 */
	function loadDrugDocumentStatusHistoryGrid($data) {
		$params = array('DocumentUc_id' => $data['DocumentUc_id']);

		$query = "
			select
				DDSH.DrugDocumentStatusHistory_id,
				DDSH.DocumentUc_id,
				DDS.DrugDocumentStatus_id,
				DDS.DrugDocumentStatus_Code,
				(case
					when DDS.DrugDocumentStatus_Name is null then 'Нет статуса'
					else DDS.DrugDocumentStatus_Name
				end) as DrugDocumentStatus_Name,
				(
					convert(varchar(10), DDSH.DrugDocumentStatusHistory_setDate, 104)
					+ ' ' + convert(varchar(8), DDSH.DrugDocumentStatusHistory_setDate, 114)
				)as DrugDocumentStatusHistory_setDate,
				UC.pmUser_id as pmUser_userID,
				rtrim(UC.pmUser_Name) as pmUser_Fio
			from
				v_DrugDocumentStatusHistory DDSH with(nolock)
				left join v_DrugDocumentStatus DDS with(nolock) on DDS.DrugDocumentStatus_id = DDSH.DrugDocumentStatus_id
				left join v_pmUserCache UC with(nolock) on UC.pmUser_id = DDSH.pmUser_userID
			where
				DDSH.DocumentUc_id = :DocumentUc_id
			order by
				DDSH.DrugDocumentStatusHistory_setDate
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$response = $result->result('array');
			return array('data' => $response);
		}
		return false;
	}

	/**
	 *  Создание спецификации для документа учета медикаментов на основе ГК.
	 */
	function createDocumentUcStrListByWhsDocumentSupply($data) {
		//старт транзакции
		$this->beginTransaction();

		$error = array(); //для сбора ошибок
		$result = array('DocumentUc_id' => $data['DocumentUc_id']);
		$drug_data = array();

		//получение данных о документе учета
		$query = "
			select
				DrugFinance_id
			from
				v_DocumentUc
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (!is_array($doc_data) || count($doc_data) < 1) {
			$error[] = "Не удалось получить данные о документе учета";
		}

		//получение данных о медикаментах
		$query = "
			select
				ds.WhsDocumentSupply_id,
				dor.Drug_id,
				d.Drug_Fas,
				dor.DrugOstatRegistry_Kolvo as Count,
				ps.PrepSeries_id,
				ps.PrepSeries_Ser,
				cast((dor.DrugOstatRegistry_Sum*100)/(dor.DrugOstatRegistry_Kolvo*(100+isnull(dn.DrugNds_Code, 0))) as decimal (12,2)) as Price,
				dn.DrugNds_id,
				dn.DrugNds_Code
			from
				v_WhsDocumentSupply wds with (nolock)
				left join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				left join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = ds.DrugShipment_id
				left join v_SubAccountType sat with(nolock) on sat.SubAccountType_id = dor.SubAccountType_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
                outer apply (
                    select top 1
                        i_dn.DrugNds_id,
                        i_dn.DrugNds_Code
                    from
                        v_WhsDocumentSupplySpec i_wdss with (nolock)
                        left join v_DrugNds i_dn with (nolock) on i_dn.DrugNds_Code = i_wdss.WhsDocumentSupplySpec_NDS
                    where
                        i_wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id and
                        i_wdss.Drug_id = dor.Drug_id
                ) dn
			where
				dor.DrugOstatRegistry_Kolvo > 0 and
				sat.SubAccountType_Code = 1 and
				dor.Org_id = wds.Org_sid and
				wds.WhsDocumentSupply_id = :WhsDocumentSupply_id;
		";
		$res = $this->db->query($query, array(
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']
		));

		if (is_object($res)) {
			$drug_data = $res->result('array');
		}

		//запись строк спецификации
		foreach($drug_data as $drug) {
			if (count($error) < 1) {
				$r_koef = 1.22; //розничный коэфицент
				$nds_koef = (100+$drug['DrugNds_Code'])/100;
				$price = round($drug['Price']/$nds_koef,2);
				$price_r = round($price*$r_koef*$nds_koef,2);

				$query = "
					declare
						@DocumentUcStr_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000);

					execute dbo.p_DocumentUcStr_ins
						@DocumentUcStr_id = @DocumentUcStr_id output,
						@DocumentUc_id = :DocumentUc_id,
						@Drug_id = :Drug_id,
						@DrugFinance_id = :DrugFinance_id,
						@DrugNds_id = :DrugNds_id,
						@DocumentUcStr_Price = :Price,
						@DocumentUcStr_PriceR = :PriceR,
						@DocumentUcStr_Count = :Count,
						@DocumentUcStr_EdCount = :EdCount,
						@DocumentUcStr_Sum = :Sum,
						@DocumentUcStr_SumR = :SumR,
						@DocumentUcStr_Ser = :DocumentUcStr_Ser,
						@PrepSeries_id = :PrepSeries_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @DocumentUcStr_id as DocumentUcStr_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'Drug_id' => $drug['Drug_id'],
					'DrugFinance_id' => $doc_data['DrugFinance_id'],
					'DrugNds_id' => $drug['DrugNds_id'],
					'Price' => $price,
					'PriceR' => $price_r,
					'Count' => $drug['Count'],
					'EdCount' => $drug['Drug_Fas'] > 0 ? $drug['Count']*$drug['Drug_Fas'] : null,
					'Sum' => $price*$drug['Count'],
					'SumR' => $price_r*$drug['Count'],
					'DocumentUcStr_Ser' => $drug['PrepSeries_Ser'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'DocumentUc_id' => $data['DocumentUc_id'],
					'pmUser_id' => $data['session']['pmuser_id'],
				));
				if ($res && is_array($res)) {
					if (!empty($res['@Error_Message'])) {
						$error[] = $res['@Error_Message'];
					}
				} else {
					$error[] = "При записи позиции в спецификацию возникла ошибка";
				}
			}
		}

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
			$this->rollbackTransaction();
			return $result;
		}

		//коммит транзакции
		$this->commitTransaction();

		return $result;
	}

	/**
	 * Загрузка списка для окна выбора ГК (используется в форме редактирования документов учета)
	 */
	function loadWhsDocumentSupplyList($filter) {
		$where = array();
		$join = "";
		$p = array();
		if (isset($filter['WhsDocumentUc_id']) && $filter['WhsDocumentUc_id'] > 0) {
			$where[] = 'wds.WhsDocumentUc_id = :WhsDocumentUc_id';
			$p['WhsDocumentUc_id'] = $filter['WhsDocumentUc_id'];
		} else {
			if (!empty($filter['WhsDocumentUc_Num'])) {
				$where[] = 'wds.WhsDocumentUc_Num like :WhsDocumentUc_Num';
				$p['WhsDocumentUc_Num'] = $filter['WhsDocumentUc_Num'].'%';
			}
			if (isset($filter['WhsDocumentUc_DateRange']) && count($filter['WhsDocumentUc_DateRange']) == 2 && !empty($filter['WhsDocumentUc_DateRange'][0])) {
				$where[] = 'wds.WhsDocumentUc_Date between :WhsDocumentUc_Date1 and :WhsDocumentUc_Date2';
				$p['WhsDocumentUc_Date1'] = $filter['WhsDocumentUc_DateRange'][0];
				$p['WhsDocumentUc_Date2'] = $filter['WhsDocumentUc_DateRange'][1];
			}
			if (isset($filter['Contragent_sid']) && $filter['Contragent_sid']) {
				$where[] = 'wds.Org_sid = (select Org_id from v_Contragent with(nolock) where Contragent_id = :Contragent_sid)';
				$p['Contragent_sid'] = $filter['Contragent_sid'];
			}
			if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
				$where[] = 'wds.DrugFinance_id = :DrugFinance_id';
				$p['DrugFinance_id'] = $filter['DrugFinance_id'];
			}
			if (!empty($filter['DrugFinance_Name'])) {
				$where[] = 'df.DrugFinance_Name like :DrugFinance_Name';
				$p['DrugFinance_Name'] = $filter['DrugFinance_Name'].'%';
			}
			if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
				$where[] = 'wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
				$p['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
			}
			if (!empty($filter['WhsDocumentCostItemType_Name'])) {
				$where[] = 'wdcit.WhsDocumentCostItemType_Name like :WhsDocumentCostItemType_Name';
				$p['WhsDocumentCostItemType_Name'] = $filter['WhsDocumentCostItemType_Name'].'%';
			}
			if (!empty($filter['WhsDocumentType_Code'])) {
				$where[] = 'wdt.WhsDocumentType_Code = :WhsDocumentType_Code';
				$p['WhsDocumentType_Code'] = $filter['WhsDocumentType_Code'];
			}
			if (!empty($filter['WhsDocumentType_CodeList'])) {
				$where[] = "wdt.WhsDocumentType_Code in ({$filter['WhsDocumentType_CodeList']})";
			}
			if (!empty($filter['WhsDocumentStatusType_Code'])) {
				$where[] = 'wdst.WhsDocumentStatusType_Code = :WhsDocumentStatusType_Code';
				$p['WhsDocumentStatusType_Code'] = $filter['WhsDocumentStatusType_Code'];
			}
			if (isset($filter['query']) && strlen($filter['query'])>=1 && empty($filter['WhsDocumentUc_Num'])) {
				$where[] = ' wds.WhsDocumentUc_Num like :query';
				$p['query'] = "".$filter['query']."%";
			}
			if (!empty($filter['Org_cid']) && empty($ilter['OrgFilter_Org_cid'])) {
				$where[] = ' org_c.Org_id = :Org_cid';
				$p['Org_cid'] = $filter['Org_cid'];
			}
			if (!empty($filter['OrgCid_Name'])) {
				$where[] = ' org_c.Org_Name like :OrgCid_Name';
				$p['OrgCid_Name'] = "%".$filter['OrgCid_Name']."%";
			}
			if (!empty($filter['OrgSid_Name'])) {
				$where[] = ' org_s.Org_Name like :OrgSid_Name';
				$p['OrgSid_Name'] = "%".$filter['OrgSid_Name']."%";
			}
			if (!empty($filter['WhsDocumentRightRecipientOrg_id'])) {
				$where[] = ' exists (
					select
						i_wdt.WhsDocumentTitle_id
					from
						v_WhsDocumentTitle i_wdt with (nolock)
						left join v_WhsDocumentTitleType i_wdtt with (nolock) on i_wdtt.WhsDocumentTitleType_id = i_wdt.WhsDocumentTitleType_id
						left join v_WhsDocumentRightRecipient i_wdrr with (nolock) on i_wdrr.WhsDocumentTitle_id = i_wdt.WhsDocumentTitle_id
					where
						i_wdt.WhsDocumentUc_id = wds.WhsDocumentUc_id and
						i_wdtt.WhsDocumentTitleType_Code = 3 and --Приложение к ГК: список пунктов отпуска
						i_wdrr.Org_id = :WhsDocumentRightRecipientOrg_id
				)';
				$p['WhsDocumentRightRecipientOrg_id'] = $filter['WhsDocumentRightRecipientOrg_id'];
			}
			if (!empty($filter['OrgSidOstatExists'])) {
				$where[] = 'ostat.cnt > 0'; //есть остатки на поставщике
				$join .= "
                    outer apply (
                        select
                            sum(dor.DrugOstatRegistry_Kolvo) as cnt
                        from
                            v_DrugOstatRegistry dor with (nolock)
                            left join v_SubAccountType sat with(nolock) on sat.SubAccountType_id = dor.SubAccountType_id
                            left join v_DrugShipment ds with(nolock) on ds.DrugShipment_id = dor.DrugShipment_id
                        where
                            ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id and
                            dor.Org_id = wds.Org_sid and
                            SubAccountType_Code = 1
                    ) ostat
                ";
			}

            //фильтр по организации
            $org_filter_fields = array(
                'OrgFilter_Org_cid',
                'OrgFilter_Org_pid'
            );
            $org_filter_exists = false;
            foreach($org_filter_fields as $of_field) {
                if (!empty($filter[$of_field])) {
                    $org_filter_exists = true;
                    break;
                }
            }
            if ($org_filter_exists) {
                $of_type = $filter['OrgFilter_Type'] == 'or' ? 'or' : 'and';
                $of_filter = array();
                foreach($org_filter_fields as $of_field) { //сборка условия по конкретному фильтру
                    $of_id_array = explode(',', $filter[$of_field]); //если переденно енсколько идентификаторов через запятую, то разбиваем строку на массив идентификаторов
                    $of_sub_filter = array();
                    foreach($of_id_array as $of_id) {
                        if (!empty($of_id) && $of_id > 0) { //если идентификатор не пустой, то собираем фрагмент условия
                            $of_sub_filter[] = 'wds.'.preg_replace('/OrgFilter_/', '', $of_field).' = '.$of_id;
                        }
                    }
                    //собираем условия по одному фильтру (всегда собираем через 'или')
                    if (count($of_sub_filter) > 0) {
                        $of_filter[] = count($of_sub_filter) > 1 ? '('.join(' or ', $of_sub_filter).')' : join(' or ', $of_sub_filter);
                    }
                }
                //собираем условие по всем фильтрам организации
                $where[] = '('.join(' '.$of_type.' ', $of_filter).')';
            }

			$where[] = 'wdt.WhsDocumentType_Code <> 13'; //исключаем доп соглашения из списка
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					-- where
					{$where_clause}
					-- end where
			";
		}
		$q = "
			select top 1000
				-- select
				wds.WhsDocumentUc_id,
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
				wds.WhsDocumentType_id,
				wds.Org_pid,
				wdt.WhsDocumentType_Code,
				convert(varchar(10), wds.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
				convert(varchar(4), wds.WhsDocumentUc_Date, 120) WhsDocumentUc_Year,
				contragent_s.Contragent_id as Contragent_sid,
				isnull(org_s.Org_Nick, org_s.Org_Name) Org_sid_Nick,
				wdst.WhsDocumentStatusType_Name,
				cast(wds.WhsDocumentUc_Sum as decimal(16,2)) as WhsDocumentUc_Sum,
				wds.DrugFinance_id,
				df.DrugFinance_Name,
				wds.WhsDocumentCostItemType_id,
				wdcit.WhsDocumentCostItemType_Name,
				null as DrugNds_Code,
				wdpr.WhsDocumentProcurementRequest_id,
				rtrim(
					isnull(dreq.DrugRequest_Name+'; ','')
					+ isnull(
						wdpr.WhsDocumentUc_Name + ' ' + convert(varchar(10), wdpr.WhsDocumentUc_Date, 104),
						wds.WhsDocumentUc_Num + ' ' + convert(varchar(10), wds.WhsDocumentUc_Date, 104)
					)
				) as DrugRequestPurchaseSpec_string,
				(convert(varchar(10), wds.WhsDocumentUc_Date, 104) + ' - ' + isnull(convert(varchar(10), wds.WhsDocumentSupply_ExecDate, 104), '')) as WhsDocumentUc_DateRange
				-- end select
			from
				-- from
				dbo.v_WhsDocumentSupply wds with (nolock)
				left join dbo.v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
				left join dbo.v_DrugFinance df with (nolock) on df.DrugFinance_id = wds.DrugFinance_id
				left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id
				left join dbo.v_Org org_s with (nolock) on org_s.Org_id = wds.Org_sid
				left join dbo.v_Org org_c with (nolock) on org_c.Org_id = wds.Org_cid
				left join dbo.v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = ISNULL(wds.WhsDocumentStatusType_id, 1)
				left join dbo.v_WhsDocumentProcurementRequest wdpr with (nolock) on wdpr.WhsDocumentUc_id = wds.WhsDocumentUc_pid
				outer apply (
					select top 1
						dr.DrugRequest_Name
					from 
						dbo.v_DrugRequest dr with (nolock)
						left join dbo.v_DrugRequestPurchaseSpec drps with (nolock) on drps.DrugRequest_id = dr.DrugRequest_id
						left join dbo.v_WhsDocumentProcurementRequestSpec wdprs with (nolock) on wdprs.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
					where 
						wdprs.WhsDocumentProcurementRequest_id = wdpr.WhsDocumentProcurementRequest_id
				) dreq
				outer apply (
					select top 1
						Contragent_id
					from
						v_Contragent with (nolock)
					where
						v_Contragent.Org_id = wds.Org_sid
				) contragent_s
				{$join}
				-- end from
			{$where_clause}
			order by
				-- order by
				wds.WhsDocumentUc_Num
				-- end order by
		";

		if (!empty($filter['limit'])) {
			$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $p);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($q), $p);
			if (is_object($result) && $count !== false) {
				return array(
					'data' => $result->result('array'),
					'totalCount' => $count
				);
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($q, $p);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 *  Копирование строки документа. Возвращет id новой строки.
	 */
	function copyDocumentUcStr($data) {
		$query = "
			declare
				@DocumentUcStr_id bigint,
				@DocumentUcStr_oid bigint,
				@DocumentUc_id bigint,
				@Drug_id bigint,
				@DrugFinance_id bigint,
				@DrugNds_id bigint,
				@DrugProducer_id bigint,
				@DocumentUcStr_Price money,
				@DocumentUcStr_PriceR money,
				@DocumentUcStr_EdCount numeric(16,2),
				@DocumentUcStr_Count numeric(16,6),
				@DocumentUcStr_Sum money,
				@DocumentUcStr_SumR money,
				@DocumentUcStr_SumNds money,
				@DocumentUcStr_SumNdsR money,
				@DocumentUcStr_Ser varchar(20),
				@DocumentUcStr_CertNum varchar(20),
				@DocumentUcStr_CertDate datetime,
				@DocumentUcStr_CertGodnDate datetime,
				@DocumentUcStr_CertOrg varchar(30),
				@DocumentUcStr_IsLab bigint,
				@DrugLabResult_Name varchar(200),
				@DocumentUcStr_RashCount numeric(16,4),
				@DocumentUcStr_RegDate datetime,
				@DocumentUcStr_RegPrice money,
				@DocumentUcStr_godnDate datetime,
				@DocumentUcStr_setDate datetime,
				@DocumentUcStr_Decl varchar(30),
				@DocumentUcStr_Barcod varchar(30),
				@DocumentUcStr_CertNM int,
				@DocumentUcStr_CertDM datetime,
				@DocumentUcStr_NTU int,
				@DocumentUcStr_NZU int,
				@DocumentUcStr_Reason varchar(100),
				@EvnRecept_id bigint,
				@EvnDrug_id bigint,
				@ReceptOtov_id bigint,
				@PrepSeries_id bigint,
				@DocumentUcStr_PlanKolvo numeric(16,2),
				@Okei_id bigint,
				@DocumentUcStr_PlanPrice money,
				@DocumentUcStr_PlanSum money,
				@Person_id bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			select
				@DocumentUcStr_oid = DocumentUcStr_oid,
				@DocumentUc_id = DocumentUc_id,
				@Drug_id = Drug_id,
				@DrugFinance_id = DrugFinance_id,
				@DrugNds_id = DrugNds_id,
				@DrugProducer_id = DrugProducer_id,
				@DocumentUcStr_Price = DocumentUcStr_Price,
				@DocumentUcStr_PriceR = DocumentUcStr_PriceR,
				@DocumentUcStr_EdCount = DocumentUcStr_EdCount,
				@DocumentUcStr_Count = DocumentUcStr_Count,
				@DocumentUcStr_Sum = DocumentUcStr_Sum,
				@DocumentUcStr_SumR = DocumentUcStr_SumR,
				@DocumentUcStr_SumNds = DocumentUcStr_SumNds,
				@DocumentUcStr_SumNdsR = DocumentUcStr_SumNdsR,
				@DocumentUcStr_Ser = DocumentUcStr_Ser,
				@DocumentUcStr_CertNum = DocumentUcStr_CertNum,
				@DocumentUcStr_CertDate = DocumentUcStr_CertDate,
				@DocumentUcStr_CertGodnDate = DocumentUcStr_CertGodnDate,
				@DocumentUcStr_CertOrg = DocumentUcStr_CertOrg,
				@DocumentUcStr_IsLab = DocumentUcStr_IsLab,
				@DrugLabResult_Name = DrugLabResult_Name,
				@DocumentUcStr_RashCount = DocumentUcStr_RashCount,
				@DocumentUcStr_RegDate = DocumentUcStr_RegDate,
				@DocumentUcStr_RegPrice = DocumentUcStr_RegPrice,
				@DocumentUcStr_godnDate = DocumentUcStr_godnDate,
				@DocumentUcStr_setDate = DocumentUcStr_setDate,
				@DocumentUcStr_Decl = DocumentUcStr_Decl,
				@DocumentUcStr_Barcod = DocumentUcStr_Barcod,
				@DocumentUcStr_CertNM = DocumentUcStr_CertNM,
				@DocumentUcStr_CertDM = DocumentUcStr_CertDM,
				@DocumentUcStr_NTU = DocumentUcStr_NTU,
				@DocumentUcStr_NZU = DocumentUcStr_NZU,
				@DocumentUcStr_Reason = DocumentUcStr_Reason,
				@EvnRecept_id = EvnRecept_id,
				@EvnDrug_id = EvnDrug_id,
				@ReceptOtov_id = ReceptOtov_id,
				@PrepSeries_id = PrepSeries_id,
				@DocumentUcStr_PlanKolvo = DocumentUcStr_PlanKolvo,
				@Okei_id = Okei_id,
				@DocumentUcStr_PlanPrice = DocumentUcStr_PlanPrice,
				@DocumentUcStr_PlanSum = DocumentUcStr_PlanSum,
				@Person_id = Person_id
			from
				v_DocumentUcStr
			where
				DocumentUcStr_id = :DocumentUcStr_id;

			exec p_DocumentUcStr_ins
				@DocumentUcStr_id = @DocumentUcStr_id output,
				@DocumentUcStr_oid = @DocumentUcStr_oid,
				@DocumentUc_id = @DocumentUc_id,
				@Drug_id = @Drug_id,
				@DrugFinance_id = @DrugFinance_id,
				@DocumentUcStr_Price = @DocumentUcStr_Price,
				@DocumentUcStr_PriceR = @DocumentUcStr_PriceR,
				@DocumentUcStr_Count = @DocumentUcStr_Count,
				@DrugNds_id = @DrugNds_id,
				@DocumentUcStr_EdCount = @DocumentUcStr_EdCount,
				@DocumentUcStr_SumR = @DocumentUcStr_SumR,
				@DocumentUcStr_Sum = @DocumentUcStr_Sum,
				@DocumentUcStr_SumNds = @DocumentUcStr_SumNds,
				@DocumentUcStr_SumNdsR = @DocumentUcStr_SumNdsR,
				@DocumentUcStr_godnDate = @DocumentUcStr_godnDate,
				@DocumentUcStr_NZU = @DocumentUcStr_NZU,
				@DocumentUcStr_IsLab = @DocumentUcStr_IsLab,
				@PrepSeries_id = @PrepSeries_id,
				@DrugProducer_id = @DrugProducer_id,
				@DrugLabResult_Name = @DrugLabResult_Name,
				@DocumentUcStr_Ser = @DocumentUcStr_Ser,
				@DocumentUcStr_CertNum = @DocumentUcStr_CertNum,
				@DocumentUcStr_CertDate = @DocumentUcStr_CertDate,
				@DocumentUcStr_CertGodnDate = @DocumentUcStr_CertGodnDate,
				@DocumentUcStr_CertOrg = @DocumentUcStr_CertOrg,
				@ReceptOtov_id = @ReceptOtov_id,
				@EvnRecept_id = @EvnRecept_id,
				@DocumentUcStr_PlanPrice = @DocumentUcStr_PlanPrice,
				@DocumentUcStr_PlanKolvo = @DocumentUcStr_PlanKolvo,
				@DocumentUcStr_PlanSum = @DocumentUcStr_PlanSum,
				@Person_id = @Person_id,
				@Okei_id = @Okei_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @DocumentUcStr_id as DocumentUcStr_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'DocumentUcStr_id' => $data['DocumentUcStr_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Создание электронной накладной
	 */
	function createDokNakByDocumentUc($data) {
		$result = array();

		$query = "
			insert into
				DocumentUc (
					DocumentUc_pid,
					DocumentUc_Num,
					DocumentUc_setDate,
					DocumentUc_didDate,
					DocumentUc_DogNum,
					DocumentUc_DogDate,
					DocumentUc_InvNum,
					DocumentUc_InvDate,
					DocumentUc_Sum,
					DocumentUc_SumR,
					DocumentUc_SumNds,
					DocumentUc_SumNdsR,
					Lpu_id,
					Contragent_id,
					Contragent_sid,
					Mol_sid,
					Contragent_tid,
					Mol_tid,
					DrugFinance_id,
					DrugDocumentType_id,
					DrugDocumentStatus_id,
					pmUser_insID,
					pmUser_updID,
					DocumentUc_insDT,
					DocumentUc_updDT,
					Org_id,
					Storage_sid,
					SubAccountType_sid,
					Storage_tid,
					SubAccountType_tid,
					WhsDocumentCostItemType_id,
					WhsDocumentUc_id
				)
			select
				DocumentUc_id,
				DocumentUc_Num,
				DocumentUc_setDate,
				DocumentUc_didDate,
				DocumentUc_DogNum,
				DocumentUc_DogDate,
				DocumentUc_InvNum,
				DocumentUc_InvDate,
				DocumentUc_Sum,
				DocumentUc_SumR,
				DocumentUc_SumNds,
				DocumentUc_SumNdsR,
				Lpu_id,
				Contragent_tid,
				Contragent_sid,
				Mol_sid,
				Contragent_tid,
				Mol_tid,
				DrugFinance_id,
				6,
				1,
				:pmuser_id,
				:pmuser_id,
				dbo.tzGetDate(),
				dbo.tzGetDate(),
				(select top 1 Org_id from v_Contragent with(nolock) where Contragent_id = Contragent_tid) as Org_id,
				Storage_sid,
				SubAccountType_sid,
				Storage_tid,
				SubAccountType_tid,
				WhsDocumentCostItemType_id,
				WhsDocumentUc_id
			from
				DocumentUc
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$res = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'pmuser_id' => $data['session']['pmuser_id']
		));
		$new_id = $this->db->insert_id();

		if ($new_id > 0) {
			//копируем строки документа
			$query = "
				insert into
					DocumentUcStr (
						DocumentUcStr_oid,
						DocumentUc_id,
						Drug_id,
						DrugFinance_id,
						DrugNds_id,
						DrugProducer_id,
						DocumentUcStr_Price,
						DocumentUcStr_PriceR,
						DocumentUcStr_EdCount,
						DocumentUcStr_Count,
						DocumentUcStr_Sum,
						DocumentUcStr_SumR,
						DocumentUcStr_SumNds,
						DocumentUcStr_SumNdsR,
						DocumentUcStr_Ser,
						DocumentUcStr_CertNum,
						DocumentUcStr_CertDate,
						DocumentUcStr_CertGodnDate,
						DocumentUcStr_CertOrg,
						DocumentUcStr_IsLab,
						DrugLabResult_Name,
						DocumentUcStr_RashCount,
						DocumentUcStr_RegDate,
						DocumentUcStr_RegPrice,
						DocumentUcStr_godnDate,
						DocumentUcStr_setDate,
						DocumentUcStr_Decl,
						DocumentUcStr_Barcod,
						DocumentUcStr_CertNM,
						DocumentUcStr_CertDM,
						DocumentUcStr_NTU,
						DocumentUcStr_NZU,
						DocumentUcStr_Reason,
						EvnRecept_id,
						pmUser_insID,
						pmUser_updID,
						DocumentUcStr_insDT,
						DocumentUcStr_updDT,
						EvnDrug_id,
						ReceptOtov_id,
						PrepSeries_id,
						DocumentUcStr_PlanKolvo,
						Okei_id,
						DocumentUcStr_PlanPrice,
						DocumentUcStr_PlanSum,
						Person_id,
						DocumentUcStr_IsNDS
					)
				select
					DocumentUcStr_oid,
					:new_id,
					Drug_id,
					DrugFinance_id,
					DrugNds_id,
					DrugProducer_id,
					DocumentUcStr_Price,
					DocumentUcStr_PriceR,
					DocumentUcStr_EdCount,
					DocumentUcStr_Count,
					DocumentUcStr_Sum,
					DocumentUcStr_SumR,
					DocumentUcStr_SumNds,
					DocumentUcStr_SumNdsR,
					DocumentUcStr_Ser,
					DocumentUcStr_CertNum,
					DocumentUcStr_CertDate,
					DocumentUcStr_CertGodnDate,
					DocumentUcStr_CertOrg,
					DocumentUcStr_IsLab,
					DrugLabResult_Name,
					DocumentUcStr_RashCount,
					DocumentUcStr_RegDate,
					DocumentUcStr_RegPrice,
					DocumentUcStr_godnDate,
					DocumentUcStr_setDate,
					DocumentUcStr_Decl,
					DocumentUcStr_Barcod,
					DocumentUcStr_CertNM,
					DocumentUcStr_CertDM,
					DocumentUcStr_NTU,
					DocumentUcStr_NZU,
					DocumentUcStr_Reason,
					EvnRecept_id,
					:pmuser_id,
					:pmuser_id,
					dbo.tzGetDate(),
					dbo.tzGetDate(),
					EvnDrug_id,
					ReceptOtov_id,
					PrepSeries_id,
					DocumentUcStr_PlanKolvo,
					Okei_id,
					DocumentUcStr_PlanPrice,
					DocumentUcStr_PlanSum,
					Person_id,
					DocumentUcStr_IsNDS
				from
					DocumentUcStr
				where
					DocumentUc_id = :DocumentUc_id;
			";
			$res = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'pmuser_id' => $data['session']['pmuser_id'],
				'new_id' => $new_id
			));
			$result = array('DocumentUc_id' => $new_id);

			//создаем партии для новых документов учета

			//получаем стартовый номер партии
			$query = "
				select
					isnull(max(cast(DrugShipment_Name as bigint)),0) + 1 as DrugShipment_Name
				from
					v_DrugShipment with(nolock)
				where
					DrugShipment_Name not like '%.%' and
					DrugShipment_Name not like '%,%' and
					len(DrugShipment_Name) <= 18 and
					isnumeric(DrugShipment_Name + 'e0') = 1
			";
			$sh_num = $this->getFirstResultFromQuery($query);

			//получаем список строк медикаментов новой накладной
			$query = "
				select
					DocumentUcStr_id
				from
					v_DocumentUcStr with (nolock)
				where
					DocumentUc_id = :DocumentUc_id
			";
			$res = $this->db->query($query, array(
				'DocumentUc_id' => $new_id
			));

			if (is_object($res)) {
				$str_arr = $res->result('array');
			}

			$sh_query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@datetime datetime;

				set @datetime = dbo.tzGetDate();

				exec p_DrugShipment_ins
					@DrugShipment_id = @Res output,
					@DrugShipment_setDT = @datetime,
					@DrugShipment_Name = :DrugShipment_Name,
					@WhsDocumentSupply_id = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$shl_query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_DrugShipmentLink_ins
					@DrugShipmentLink_id = @Res output,
					@DrugShipment_id = :DrugShipment_id,
					@DocumentUcStr_id = :DocumentUcStr_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			foreach($str_arr as $str) {
				$str_id = $str['DocumentUcStr_id'];

				//создание партии
				$sh_id = $this->getFirstResultFromQuery($sh_query, array(
					'DrugShipment_Name' => $sh_num++,
					'pmUser_id' => $data['session']['pmuser_id'],
				));

				//связь партии со строкой документа учета
				$shl_id = $this->getFirstResultFromQuery($shl_query, array(
					'DrugShipment_id' => $sh_id,
					'DocumentUcStr_id' => $str_id,
					'pmUser_id' => $data['session']['pmuser_id'],
				));
			}
		}

		return $result;
	}

	/**
	 *  Получение идентификатора организации соответствующей Минздраву.
	 */
	function getMinzdravDloOrgId() {
		$query = "select dbo.GetMinzdravDloOrgId() as Org_id;";
		$result = $this->getFirstRowFromQuery($query);
		if ($result && !empty($result['Org_id'])) {
			return $result;
		} else {
			return false;
		}
	}

    /**
     * Получение данных о медикаментых на основе назначений
     */
    function loadDocumentUcStrListByEvnCourseTreatDrug($data) {
        $query = "
			select
                p.Drug_id,
                d.Drug_Name,
                sum(p.Drug_Kolvo) as Drug_Kolvo
            from (
                select
                    isnull(ectd.Drug_id, alt_d.Drug_id) as Drug_id,
                    ectd.EvnCourseTreatDrug_Kolvo as Drug_Kolvo
                from
                    EvnCourseTreatDrug ectd with(nolock)
                    left join EvnCourseTreat ect on ectd.EvnCourseTreat_id=ect.EvnCourseTreat_id
                    left join EvnCourse ec on ec.EvnCourse_id=ect.EvnCourse_id
                    left join EvnDrug ed on ec.EvnCourse_id=ed.EvnCourse_id
                    left join DrugOstatRegistry dor on dor.Drug_id=ectd.Drug_id
                    outer apply (
                        select top 1
                            i_d.Drug_id
                        from
                            rls.v_Drug i_d with(nolock)
                        where
                            ectd.DrugComplexMnn_id is not null and
                            i_d.DrugComplexMnn_id = ectd.DrugComplexMnn_id
                        order by
                            Drug_id
                    ) alt_d
                where
                    ed.EvnDrug_id is null and
                    ectd.EvnCourseTreatDrug_Kolvo is not null and
                    ec.LpuSection_id = :LpuSection_id
            ) p
                inner join rls.v_Drug d on d.Drug_id = p.Drug_id
            where
                p.Drug_Kolvo is not null
            group by
                p.Drug_id, d.Drug_Name
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
	 * Загрузка списка для вкладки заявки в АРМ товароведа
	 */
	function loadWhsDocumentRequestList($data) {
		$where = array();
		$join = "";
		$p = array();

        $p['UserMedPersonal_id'] = $data['UserMedPersonal_id'];
        $p['MedService_id'] = $data['MedService_id'];

		if (!empty($data['WhsDocumentUc_Num'])) {
			$where[] = 'wdu.WhsDocumentUc_Num like :WhsDocumentUc_Num';
			$p['WhsDocumentUc_Num'] = '%'.$data['WhsDocumentUc_Num'].'%';
		}
		if ((isset($data['DocumentUc_Num'])) && !empty($data['DocumentUc_Num'])) {
			$where[] = "wdu.WhsDocumentUc_Num like :DocumentUc_Num";
			$p['DocumentUc_Num'] = $data['DocumentUc_Num'].'%';
		}
		if (!empty($data['begDate']) || !empty($data['endDate'])) {
			$where[] = "wdu.WhsDocumentUc_Date between :begDate and :endDate";
			$p['begDate'] = $data['begDate'];
			$p['endDate'] = $data['endDate'];
		}
		if (!empty($data['DocumentUc_date_range'][0]) && !empty($data['DocumentUc_date_range'][1])) {
			$where[] = "wdu.WhsDocumentUc_updDT between :updbegDate and :updendDate";
			$p['updbegDate'] = $data['DocumentUc_date_range'][0];
			$p['updendDate'] = $data['DocumentUc_date_range'][1];
		} else if (!empty($data['DocumentUc_date_range'][0])){
			$where[] = "wdu.WhsDocumentUc_updDT >= :updbegDate";
			$p['updbegDate'] = $data['DocumentUc_date_range'][0];
		} else if (!empty($data['DocumentUc_date_range'][1])){
			$where[] = "wdu.WhsDocumentUc_updDT <= :updendDate";
			$p['updendDate'] = $data['DocumentUc_date_range'][1];
		}
		if (isset($data['Contragent_sid']) && $data['Contragent_sid']) {
			$where[] = 'wds.Org_sid = (select Org_id from v_Contragent with(nolock) where Contragent_id = :Contragent_sid)';
			$p['Contragent_sid'] = $data['Contragent_sid'];
		}
		if (isset($data['DrugFinance_id']) && $data['DrugFinance_id']) {
			$where[] = 'wds.DrugFinance_id = :DrugFinance_id';
			$p['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id']) {
			$where[] = 'wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$p['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		/*if (!empty($data['WhsDocumentType_Code'])) {
			$where[] = 'wdt.WhsDocumentType_Code = :WhsDocumentType_Code';
			$p['WhsDocumentType_Code'] = $data['WhsDocumentType_Code'];
		}*/
		/*if (!empty($data['WhsDocumentType_CodeList'])) {
			$where[] = "wdt.WhsDocumentType_Code in ({$data['WhsDocumentType_CodeList']})";
		}*/
		if (!empty($data['WhsDocumentStatusType_Code'])) {
			$where[] = 'wdst.WhsDocumentStatusType_Code = :WhsDocumentStatusType_Code';
			$p['WhsDocumentStatusType_Code'] = $data['WhsDocumentStatusType_Code'];
		}
		if (isset($data['query']) && strlen($data['query'])>=1 && empty($data['WhsDocumentUc_Num'])) {
			$where[] = ' wdu.WhsDocumentUc_Num like :query';
			$p['query'] = "".$data['query']."%";
		}
		if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0)) {
			$where[] = "(sssl.LpuSection_id = :LpuSection_id or tssl.LpuSection_id = :LpuSection_id)";
			$p['LpuSection_id'] = $data['LpuSection_id'];
		}
		if ((isset($data['LpuBuilding_id'])) && ($data['LpuBuilding_id']>0)) {
			$where[] = "(sssl.LpuBuilding_id = :LpuBuilding_id or tssl.LpuBuilding_id = :LpuBuilding_id)";
			$p['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if ((isset($data['Storage_id'])) && ($data['Storage_id']>0)) {
			$where[] = "(wds.Storage_tid = :Storage_id or wds.Storage_sid = :Storage_id)";
			$p['Storage_id'] = $data['Storage_id'];
		}

		if (!empty($data['Org_id']) && !empty($data['Lpu_id'])) {
            $org_filter = "";
            $p['Org_id'] = $data['Org_id'];

            if (!empty($data['Lpu_id'])) {
                $org_filter .= "sssl.Lpu_id = :Lpu_id or tssl.Lpu_id = :Lpu_id or ";
            }
            $org_filter .= "sssl.Org_id = :Org_id or tssl.Org_id = :Org_id or ";
            $org_filter .= "wds.Org_sid = :Org_id or wds.Org_tid = :Org_id or ";
            $org_filter .= "wdu.Org_aid = :Org_id";

			$p['Lpu_id'] = $data['Lpu_id'];
            $where[] = "({$org_filter})";
		} else {
            $p['Org_id'] = null;
        }

		/*if (!empty($data['Org_cid'])) {
			$where[] = ' org_c.Org_id like :Org_cid';
			$p['Org_cid'] = $data['Org_cid'];
		}
		if (!empty($data['OrgCid_Name'])) {
			$where[] = ' org_c.Org_Name like :OrgCid_Name';
			$p['OrgCid_Name'] = "%".$data['OrgCid_Name']."%";
		}*/
		if (!empty($data['WhsDocumentRightRecipientOrg_id'])) {
			$where[] = ' exists (
				select
					i_wdt.WhsDocumentTitle_id
				from
					v_WhsDocumentTitle i_wdt with (nolock)
					left join v_WhsDocumentTitleType i_wdtt with (nolock) on i_wdtt.WhsDocumentTitleType_id = i_wdt.WhsDocumentTitleType_id
					left join v_WhsDocumentRightRecipient i_wdrr with (nolock) on i_wdrr.WhsDocumentTitle_id = i_wdt.WhsDocumentTitle_id
				where
					i_wdt.WhsDocumentUc_id = wds.WhsDocumentSupply_id and
					i_wdtt.WhsDocumentTitleType_Code = 3 and --Приложение к ГК: список пунктов отпуска
					i_wdrr.Org_id = :WhsDocumentRightRecipientOrg_id
			)';
			$p['WhsDocumentRightRecipientOrg_id'] = $data['WhsDocumentRightRecipientOrg_id'];
		}
		if (!empty($data['OrgTidOstatExists'])) {
			$where[] = 'ostat.cnt > 0'; //есть остатки на поставщике
			$join .= "
			outer apply (
				select
					sum(dor.DrugOstatRegistry_Kolvo) as cnt
				from
					v_DrugOstatRegistry dor with (nolock)
					left join v_SubAccountType sat with(nolock) on sat.SubAccountType_id = dor.SubAccountType_id
					left join v_DrugShipment ds with(nolock) on ds.DrugShipment_id = dor.DrugShipment_id
				where
					ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id and
					dor.Org_id = wds.Org_tid and
					SubAccountType_Code = 1
			) ostat
		";
		}
		$where[] = 'wdu.WhsDocumentType_id = 22'; //запрос только на документы с типом Заявка


		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					-- where
					{$where_clause}
					-- end where
			";
		}
		$q = "
			-- addit with
			with
			RequestDirectionType as (
				select
				1 as RequestDirectionType_id,
				'Входящие' as RequestDirectionType_Name
				union
				select
				2 as RequestDirectionType_id,
				'Исходящие' as RequestDirectionType_Name
			),
			MedServiceStorageList as (
				select Storage_id
				from v_StorageStructLevel with(nolock)
				where MedService_id = :MedService_id
			)
			-- end addit with
			select top 1000
				-- select
				wdu.WhsDocumentUc_id,
				wdu.WhsDocumentUc_Num,
				wdu.WhsDocumentUc_Name,
				wdu.WhsDocumentUc_Sum,
				convert(varchar(10), wdu.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
				wdc.WhsDocumentClass_id,
				wdc.WhsDocumentClass_Code,
				wdc.WhsDocumentClass_Name,
				wdst.WhsDocumentStatusType_id,
				wdst.WhsDocumentStatusType_Code,
				wdst.WhsDocumentStatusType_Name,
				sOrg.Org_id as Org_sid,
				sOrg.Org_Nick as Org_sid_Nick,
				tOrg.Org_id as Org_tid,
				tOrg.Org_Nick as Org_tid_Nick,
				sStorage.Storage_id as Storage_sid,
				sStorage.Storage_Name as Storage_sName,
				tStorage.Storage_id as Storage_tid,
				tStorage.Storage_Name as Storage_tName,
				case when(
					wdc.WhsDocumentClass_Code = 1 -- 1 - Заявка на поставку
					or s_m.Mol_id is not null -- Мол получателя соответствует врачу пользователя
				) then 1 else 0 end as allow_delete, -- 1 - Удаление разрешено; 0 - Удаление запрещено.
				case when (
					wds.Storage_tid is null
					or tStorage.Storage_id in (select Storage_id from MedServiceStorageList)
				) then 1 else 0 end as allow_execute,
				RDT.RequestDirectionType_id,
				RDT.RequestDirectionType_Name
				-- end select
			from
				-- from
				v_WhsDocumentUc wdu with (nolock)
				inner join v_WhsDocumentSpecificity wds with(nolock) on wds.WhsDocumentUc_id = wdu.WhsDocumentUc_id
				left join v_WhsDocumentClass wdc with(nolock) on wdc.WhsDocumentClass_id = wdu.WhsDocumentClass_id
				left join v_WhsDocumentStatusType wdst with(nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
				left join Org sOrg with(nolock) on sOrg.Org_id = wds.Org_sid
				left join Org tOrg with(nolock) on tOrg.Org_id = wds.Org_tid
				left join v_Storage sStorage with(nolock) on sStorage.Storage_id = wds.Storage_sid
				left join v_Storage tStorage with(nolock) on tStorage.Storage_id = wds.Storage_tid
				left join v_StorageStructLevel sssl with (nolock) on sssl.Storage_id = wds.Storage_sid
				left join v_StorageStructLevel tssl with (nolock) on tssl.Storage_id = wds.Storage_tid
				outer apply (
                    select top 1
                        i_m.Mol_id
                    from
                        v_Contragent i_c with (nolock)
                        left join v_Mol i_m with (nolock) on i_m.Contragent_id = i_c.Contragent_id
                    where
                        i_c.Org_id = wds.Org_sid and
                        i_m.MedPersonal_id = :UserMedPersonal_id
                ) s_m
                outer apply(
                	select top 1
                		RequestDirectionType_id,
                		RequestDirectionType_Name
					from RequestDirectionType
					where
						RequestDirectionType_id = (case
							when
								wdst.WhsDocumentStatusType_Code in (5,6,7)
								and (
									wdu.Org_aid != isnull(:Org_id, 0)
									or tStorage.Storage_id in (select Storage_id from MedServiceStorageList)
								)
							then 1
							when (
								sOrg.Org_id != isnull(:Org_id, 0)
								or sStorage.Storage_id in (select Storage_id from MedServiceStorageList)
							) then 2
						end)
                ) RDT
				{$join}
				-- end from

			{$where_clause}

			order by
				-- order by
				wdu.WhsDocumentUc_Num
				-- end order by
		";
		//echo getDebugSQL($q, $p);exit;
		if (!empty($data['limit'])) {
			$result = $this->db->query(getLimitSQLPH($q, $data['start'], $data['limit']), $p);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($q), $p);
			if (is_object($result) && $count !== false) {
				return array(
					'data' => $result->result('array'),
					'totalCount' => $count
				);
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($q, $p);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка списка источников финансирования
	 */
	function loadDrugFinanceGrid($data) {
		$params = array();
		$query = "
			select
				DF.DrugFinance_id,
				DF.DrugFinance_Code,
				DF.DrugFinance_Name,
				DF.DrugFinance_SysNick,
				convert(varchar(10), DF.DrugFinance_begDate, 104) as DrugFinance_begDate,
				convert(varchar(10), DF.DrugFinance_endDate, 104) as DrugFinance_endDate
			from
				v_DrugFinance DF with(nolock)
			order by
				cast(DF.DrugFinance_Code as int)
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка источников финансировния
	 */
	function loadDrugFinanceList($data) {
		$filters = "1=1";
		$params = array();

		if (!empty($data['DrugFinance_id'])) {
			$filters .= " and DF.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}

		$query = "
			select
				DF.DrugFinance_id,
				DF.DrugFinance_Code,
				DF.DrugFinance_Name,
				DF.DrugFinance_SysNick,
				convert(varchar(10), DF.DrugFinance_begDate, 104) as DrugFinance_begDate,
				convert(varchar(10), DF.DrugFinance_endDate, 104) as DrugFinance_endDate
			from
				v_DrugFinance DF with(nolock)
			where
				{$filters}
			order by
				cast(DF.DrugFinance_Code as int)
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для формы редактировния источника финансировния
	 */
	function loadDrugFinanceForm($data) {
		$params = array('DrugFinance_id' => $data['DrugFinance_id']);
		$query = "
			select top 1
				DF.DrugFinance_id,
				DF.DrugFinance_Code,
				DF.DrugFinance_Name,
				DF.DrugFinance_SysNick,
				convert(varchar(10), DF.DrugFinance_begDate, 104) as DrugFinance_begDate,
				convert(varchar(10), DF.DrugFinance_endDate, 104) as DrugFinance_endDate,
				DF.Region_id
			from
				v_DrugFinance DF with(nolock)
			where
				DF.DrugFinance_id = :DrugFinance_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение источника финансировния
	 */
	function saveDrugFinance($data) {
		$params = array(
			'DrugFinance_id' => !empty($data['DrugFinance_id'])?$data['DrugFinance_id']:null,
			'DrugFinance_Code' => $data['DrugFinance_Code'],
			'DrugFinance_Name' => $data['DrugFinance_Name'],
			'DrugFinance_SysNick' => $data['DrugFinance_SysNick'],
			'DrugFinance_begDate' => !empty($data['DrugFinance_begDate'])?$data['DrugFinance_begDate']:null,
			'DrugFinance_endDate' => !empty($data['DrugFinance_endDate'])?$data['DrugFinance_endDate']:null,
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		$uniqFields = array(
			'DrugFinance_Code' => 'Код',
			'DrugFinance_Name' => 'Наименование',
			'DrugFinance_SysNick' => 'Ник'
		);
		foreach($uniqFields as $fieldName => $fieldLabel) {
			$count = $this->getFirstResultFromQuery("
				select top 1 count(*) as cnt
				from v_DrugFinance with(nolock)
				where DrugFinance_id <> isnull(:DrugFinance_id,0) and lower({$fieldName}) = lower(:{$fieldName})
			", $params);
			if ($count === false) {
				return $this->createError('',"Ошибка при проверке уникальности поля \"{$fieldLabel}\"");
			}
			if ($count > 0) {
				return $this->createError('',"Значение в поле \"{$fieldLabel}\" не уникально");
			}
		}

		if (empty($data['DrugFinance_id'])) {
			$procedure = "p_DrugFinance_ins";
		} else {
			$procedure = "p_DrugFinance_upd";
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Res bigint;
			set @Res = :DrugFinance_id;
			exec {$procedure}
				@DrugFinance_id = @Res output,
				@DrugFinance_Code = :DrugFinance_Code,
				@DrugFinance_Name = :DrugFinance_Name,
				@DrugFinance_SysNick = :DrugFinance_SysNick,
				@DrugFinance_begDate = :DrugFinance_begDate,
				@DrugFinance_endDate = :DrugFinance_endDate,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as DrugFinance_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении источника финансировании');
		}
		return $response;
	}

	/**
	 * Получение списка целевых статей
	 */
	function loadBudgetFormTypeGrid($data) {
		$params = array();
		$query = "
			select
				BFT.BudgetFormType_id,
				BFT.BudgetFormType_Code,
				BFT.BudgetFormType_Name,
				convert(varchar(10), BFT.BudgetFormType_begDate, 104) as BudgetFormType_begDate,
				convert(varchar(10), BFT.BudgetFormType_endDate, 104) as BudgetFormType_endDate
			from
				v_BudgetFormType BFT with(nolock)
			order by
				BFT.BudgetFormType_Code
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка целевых статей
	 */
	function loadBudgetFormTypeList($data) {
		$params = array();
		$query = "
			select
				BFT.BudgetFormType_id,
				BFT.BudgetFormType_Code,
				BFT.BudgetFormType_Name,
				convert(varchar(10), BFT.BudgetFormType_begDate, 104) as BudgetFormType_begDate,
				convert(varchar(10), BFT.BudgetFormType_endDate, 104) as BudgetFormType_endDate
			from
				v_BudgetFormType BFT with(nolock)
			order by
				BFT.BudgetFormType_Code
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для редактирования целевой статьи
	 */
	function loadBudgetFormTypeForm($data) {
		$params = array('BudgetFormType_id' => $data['BudgetFormType_id']);
		$query = "
			select top 1
				BFT.BudgetFormType_id,
				BFT.BudgetFormType_Code,
				BFT.BudgetFormType_Name,
				BFT.BudgetFormType_NameGen,
				convert(varchar(10), BFT.BudgetFormType_begDate, 104) as BudgetFormType_begDate,
				convert(varchar(10), BFT.BudgetFormType_endDate, 104) as BudgetFormType_endDate,
				BFT.Region_id
			from
				v_BudgetFormType BFT with(nolock)
			where
				BFT.BudgetFormType_id = :BudgetFormType_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение кода для целевой статьи
	 */
	function generateBudgetFormTypeCode($data) {
		$query = "
			declare @code bigint = (
				select top 1 max(cast(BudgetFormType_Code as bigint))
				from v_BudgetFormType with(nolock)
				where isNumeric(BudgetFormType_Code) = 1
			)
			select isnull(@code,0)+1 as BudgetFormType_Code
		";
		$result = $this->queryResult($query);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при получении кода');
		}
		$result[0]['success'] = true;
		return $result;
	}

	/**
	 * Сохранение целевой статьи
	 */
	function saveBudgetFormType($data) {
		$params = array(
			'BudgetFormType_id' => !empty($data['BudgetFormType_id'])?$data['BudgetFormType_id']:null,
			'BudgetFormType_Code' => $data['BudgetFormType_Code'],
			'BudgetFormType_Name' => $data['BudgetFormType_Name'],
			'BudgetFormType_NameGen' => $data['BudgetFormType_NameGen'],
			'BudgetFormType_begDate' => !empty($data['BudgetFormType_begDate'])?$data['BudgetFormType_begDate']:null,
			'BudgetFormType_endDate' => !empty($data['BudgetFormType_endDate'])?$data['BudgetFormType_endDate']:null,
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		$uniqFields = array(
			'BudgetFormType_Code' => 'Код',
		);
		foreach($uniqFields as $fieldName => $fieldLabel) {
			$count = $this->getFirstResultFromQuery("
				select top 1 count(*) as cnt
				from v_BudgetFormType with(nolock)
				where BudgetFormType_id <> isnull(:BudgetFormType_id,0) and lower({$fieldName}) = lower(:{$fieldName})
			", $params);
			if ($count === false) {
				return $this->createError('',"Ошибка при проверке уникальности поля \"{$fieldLabel}\"");
			}
			if ($count > 0) {
				return $this->createError('',"Значение в поле \"{$fieldLabel}\" не уникально");
			}
		}

		if (empty($data['BudgetFormType_id'])) {
			$procedure = "p_BudgetFormType_ins";
		} else {
			$procedure = "p_BudgetFormType_upd";
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Res bigint;
			set @Res = :BudgetFormType_id;
			exec {$procedure}
				@BudgetFormType_id = @Res output,
				@BudgetFormType_Code = :BudgetFormType_Code,
				@BudgetFormType_Name = :BudgetFormType_Name,
				@BudgetFormType_NameGen = :BudgetFormType_NameGen,
				@BudgetFormType_begDate = :BudgetFormType_begDate,
				@BudgetFormType_endDate = :BudgetFormType_endDate,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as BudgetFormType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении источника финансировании');
		}
		return $response;
	}

	/**
	 * Получение списка статьи расхода
	 */
	function loadWhsDocumentCostItemTypeGrid($data) {
		$params = array();
		$query = "
			select
				WDCIT.WhsDocumentCostItemType_id,
				WDCIT.WhsDocumentCostItemType_Code,
				WDCIT.WhsDocumentCostItemType_Name,
				convert(varchar(10), WDCIT.WhsDocumentCostItemType_begDate, 104) as WhsDocumentCostItemType_begDate,
				convert(varchar(10), WDCIT.WhsDocumentCostItemType_endDate, 104) as WhsDocumentCostItemType_endDate,
				WDCIT.WhsDocumentCostItemType_isDLO,
				WDCIT.WhsDocumentCostItemType_isPersonAllocation,
				WDCIT.WhsDocumentCostItemType_isDrugRequest,
				DF.DrugFinance_id,
				DF.DrugFinance_Name,
				PRT.PersonRegisterType_id,
				PRT.PersonRegisterType_Name
			from
				v_WhsDocumentCostItemType WDCIT with(nolock)
				left join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = WDCIT.DrugFinance_id
				left join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = WDCIT.PersonRegisterType_id
			order by
				WDCIT.WhsDocumentCostItemType_Code
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка статьи расхода
	 */
	function loadWhsDocumentCostItemTypeList($data) {
		$filters = "1=1";
		$params = array();

		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filters .= " and WDCIT.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}

		$query = "
			select
				WDCIT.WhsDocumentCostItemType_id,
				WDCIT.WhsDocumentCostItemType_Code,
				WDCIT.WhsDocumentCostItemType_Name
			from
				v_WhsDocumentCostItemType WDCIT with(nolock)
			where
				{$filters}
			order by
				WDCIT.WhsDocumentCostItemType_Code
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для редактирования статьи расхода
	 */
	function loadWhsDocumentCostItemTypeForm($data) {
		$params = array('WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']);
		$query = "
			select
				WDCIT.WhsDocumentCostItemType_id,
				WDCIT.WhsDocumentCostItemType_Code,
				WDCIT.WhsDocumentCostItemType_Nick,
				WDCIT.WhsDocumentCostItemType_Name,
				WDCIT.WhsDocumentCostItemType_FullName,
				convert(varchar(10), WDCIT.WhsDocumentCostItemType_begDate, 104) as WhsDocumentCostItemType_begDate,
				convert(varchar(10), WDCIT.WhsDocumentCostItemType_endDate, 104) as WhsDocumentCostItemType_endDate,
				WDCIT.WhsDocumentCostItemType_isDLO,
				WDCIT.WhsDocumentCostItemType_isPersonAllocation,
				WDCIT.WhsDocumentCostItemType_isPrivilegeAllowed,
				WDCIT.WhsDocumentCostItemType_isDrugRequest,
				WDCIT.DrugFinance_id,
				WDCIT.PersonRegisterType_id,
				WDCIT.DocNormative_id,
				WDCIT.Region_id
			from
				v_WhsDocumentCostItemType WDCIT with(nolock)
			where
				WDCIT.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение кода статьи расхода
	 */
	function generateWhsDocumentCostItemTypeCode($data) {
		$query = "
			declare @code bigint = (
				select top 1 max(cast(WhsDocumentCostItemType_Code as bigint))
				from v_WhsDocumentCostItemType with(nolock)
				where isNumeric(WhsDocumentCostItemType_Code) = 1
			)
			select isnull(@code,0)+1 as WhsDocumentCostItemType_Code
		";
		$result = $this->queryResult($query);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при получении кода');
		}
		$result[0]['success'] = true;
		return $result;
	}

	/**
	 * Сохранение статьи расхода
	 */
	function saveWhsDocumentCostItemType($data) {
		$params = array(
			'WhsDocumentCostItemType_id' => !empty($data['WhsDocumentCostItemType_id'])?$data['WhsDocumentCostItemType_id']:null,
			'WhsDocumentCostItemType_Code' => $data['WhsDocumentCostItemType_Code'],
			'WhsDocumentCostItemType_Nick' => $data['WhsDocumentCostItemType_Nick'],
			'WhsDocumentCostItemType_Name' => $data['WhsDocumentCostItemType_Name'],
			'WhsDocumentCostItemType_FullName' => $data['WhsDocumentCostItemType_FullName'],
			'PersonRegisterType_id' => !empty($data['PersonRegisterType_id'])?$data['PersonRegisterType_id']:null,
			'DrugFinance_id' => $data['DrugFinance_id'],
			'DocNormative_id' => !empty($data['DocNormative_id'])?$data['DocNormative_id']:null,
			'WhsDocumentCostItemType_isDLO' => !empty($data['WhsDocumentCostItemType_isDLO'])?$data['WhsDocumentCostItemType_isDLO']:null,
			'WhsDocumentCostItemType_isPersonAllocation' => !empty($data['WhsDocumentCostItemType_isPersonAllocation'])?$data['WhsDocumentCostItemType_isPersonAllocation']:null,
			'WhsDocumentCostItemType_isPrivilegeAllowed' => !empty($data['WhsDocumentCostItemType_isPrivilegeAllowed'])?$data['WhsDocumentCostItemType_isPrivilegeAllowed']:null,
			'WhsDocumentCostItemType_isDrugRequest' => !empty($data['WhsDocumentCostItemType_isDrugRequest'])?$data['WhsDocumentCostItemType_isDrugRequest']:null,
			'WhsDocumentCostItemType_begDate' => !empty($data['WhsDocumentCostItemType_begDate'])?$data['WhsDocumentCostItemType_begDate']:null,
			'WhsDocumentCostItemType_endDate' => !empty($data['WhsDocumentCostItemType_endDate'])?$data['WhsDocumentCostItemType_endDate']:null,
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['WhsDocumentCostItemType_id'])) {
			$savedParams = $this->getFirstRowFromQuery("
				select top 1 * from v_WhsDocumentCostItemType with(nolock)
				where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
			", $params);
			if (!is_array($savedParams)) {
				return $this->createError('','Ошибка при получении данных статьи расхода из БД');
			}
			$ignoreParams = array('pmUser_insID','pmUser_updID','WhsDocumentCostItemType_insDT','WhsDocumentCostItemType_updDT');
			$paramsNameList = array_keys(array_change_key_case($params));
			foreach($savedParams as $name => $value) {
				if (!in_array(mb_strtolower($name), $paramsNameList) && !in_array($name, $ignoreParams)) {
					if ($value instanceof DateTime) {
						$params[$name] = $value->format('Y-m-d H:i:s');
					} else {
						$params[$name] = $value;
					}
				}
			}
		}

		$uniqFields = array(
			'WhsDocumentCostItemType_Code' => 'Код',
			'WhsDocumentCostItemType_Nick' => 'Ник',
		);
		foreach($uniqFields as $fieldName => $fieldLabel) {
			$count = $this->getFirstResultFromQuery("
				select top 1 count(*) as cnt
				from v_WhsDocumentCostItemType with(nolock)
				where WhsDocumentCostItemType_id <> isnull(:WhsDocumentCostItemType_id,0) and lower({$fieldName}) = lower(:{$fieldName})
			", $params);
			if ($count === false) {
				return $this->createError('',"Ошибка при проверке уникальности поля \"{$fieldLabel}\"");
			}
			if ($count > 0) {
				return $this->createError('',"Значение в поле \"{$fieldLabel}\" не уникально");
			}
		}

		if (empty($data['WhsDocumentCostItemType_id'])) {
			$procedure = "p_WhsDocumentCostItemType_ins";
		} else {
			$procedure = "p_WhsDocumentCostItemType_upd";
		}


		$execPartParams = array();
		foreach($params as $name => $value) {
			if ($name != 'WhsDocumentCostItemType_id') {
				$execPartParams[] = "@{$name} = :{$name}";
			}
		}
		$execPartParamsStr = implode(",\n", $execPartParams);
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Res bigint;
			set @Res = :WhsDocumentCostItemType_id;
			exec {$procedure}
				@WhsDocumentCostItemType_id = @Res output,
				{$execPartParamsStr},
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as WhsDocumentCostItemType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении источника финансировании');
		}
		return $response;
	}

	/**
	 * Получение списка финансирований контрактов
	 */
	function loadFinanceSourceGrid($data) {
		$params = array();
		$query = "
			select
				FS.FinanceSource_id,
				FS.FinanceSource_Code,
				FS.FinanceSource_Name,
				convert(varchar(10), FS.FinanceSource_begDate, 104) as FinanceSource_begDate,
				convert(varchar(10), FS.FinanceSource_endDate, 104) as FinanceSource_endDate,
				BFT.BudgetFormType_Name,
				DF.DrugFinance_Name,
				WDCIT.WhsDocumentCostItemType_Name
			from
				v_FinanceSource FS with(nolock)
				left join v_BudgetFormType BFT with(nolock) on BFT.BudgetFormType_id = FS.BudgetFormType_id
				left join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = FS.DrugFinance_id
				left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = FS.WhsDocumentCostItemType_id
			order by
				FS.FinanceSource_Code
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для редактирования финансирования контрактов
	 */
	function loadFinanceSourceForm($data) {
		$params = array('FinanceSource_id' => $data['FinanceSource_id']);
		$query = "
			select
				FS.FinanceSource_id,
				FS.FinanceSource_Code,
				FS.FinanceSource_Name,
				FS.FinanceSource_SuppName,
				convert(varchar(10), FS.FinanceSource_begDate, 104) as FinanceSource_begDate,
				convert(varchar(10), FS.FinanceSource_endDate, 104) as FinanceSource_endDate,
				FS.BudgetFormType_id,
				FS.DrugFinance_id,
				FS.WhsDocumentCostItemType_id,
				FS.Region_id
			from
				v_FinanceSource FS with(nolock)
			where
				FS.FinanceSource_id = :FinanceSource_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение кода для финансирования контрактов
	 */
	function generateFinanceSourceCode($data) {
		$query = "
			declare @code bigint = (
				select top 1 max(cast(FinanceSource_Code as bigint))
				from v_FinanceSource with(nolock)
				where isNumeric(FinanceSource_Code) = 1
			)
			select isnull(@code,0)+1 as FinanceSource_Code
		";
		$result = $this->queryResult($query);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при получении кода');
		}
		$result[0]['success'] = true;
		return $result;
	}

	/**
	 * Сохранение финансирования контрактов
	 */
	function saveFinanceSource($data) {
		$params = array(
			'FinanceSource_id' => !empty($data['FinanceSource_id'])?$data['FinanceSource_id']:null,
			'FinanceSource_Code' => $data['FinanceSource_Code'],
			'FinanceSource_Name' => $data['FinanceSource_Name'],
			'FinanceSource_SuppName' => $data['FinanceSource_SuppName'],
			'FinanceSource_begDate' => $data['FinanceSource_begDate'],
			'FinanceSource_endDate' => !empty($data['FinanceSource_endDate'])?$data['FinanceSource_endDate']:null,
			'DrugFinance_id' => $data['DrugFinance_id'],
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'BudgetFormType_id' => $data['BudgetFormType_id'],
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		$uniqFields = array(
			'FinanceSource_Code' => 'Код',
		);
		foreach($uniqFields as $fieldName => $fieldLabel) {
			$count = $this->getFirstResultFromQuery("
				select top 1 count(*) as cnt
				from v_FinanceSource with(nolock)
				where FinanceSource_id <> isnull(:FinanceSource_id,0) and lower({$fieldName}) = lower(:{$fieldName})
			", $params);
			if ($count === false) {
				return $this->createError('',"Ошибка при проверке уникальности поля \"{$fieldLabel}\"");
			}
			if ($count > 0) {
				return $this->createError('',"Значение в поле \"{$fieldLabel}\" не уникально");
			}
		}

		if (empty($data['FinanceSource_id'])) {
			$procedure = "p_FinanceSource_ins";
		} else {
			$procedure = "p_FinanceSource_upd";
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Res bigint;
			set @Res = :FinanceSource_id;
			exec {$procedure}
				@FinanceSource_id = @Res output,
				@FinanceSource_Code = :FinanceSource_Code,
				@FinanceSource_Name = :FinanceSource_Name,
				@FinanceSource_SuppName = :FinanceSource_SuppName,
				@FinanceSource_begDate = :FinanceSource_begDate,
				@FinanceSource_endDate = :FinanceSource_endDate,
				@DrugFinance_id = :DrugFinance_id,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@BudgetFormType_id = :BudgetFormType_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as FinanceSource_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении источника финансировании');
		}
		return $response;
	}

	/**
	 * Загрузка списка медикаментов из регистра остатков
	 */
	function loadDrugOstatRegistryGrid($data) {
        $this->load->model('DocumentUc_model', 'DocumentUc_model');
        $default_goods_unit_id = $this->DocumentUc_model->getDefaultGoodsUnitId();

		$params = array('Org_id' => $data['Org_id']);
        $join = array();
        $order = array();
		$filters = "";
        $join_clause = "";
        $order_clause = "";

        //фильтр по привязке к документам учета
        if (!empty($data['only_doc_str_linked'])) {
            $filters .= " and DSL.DocumentUcStr_id is not null";
        } else {
            $filters .= " and (
                DSL.DocumentUcStr_id is not null or
                (
                    DSL.DocumentUcStr_id is null and
                    (
                        isnull(WDCIT.WhsDocumentCostItemType_isDLO, 1) <> 2 or
                        OT.OrgType_SysNick not in ('lpu', 'touz')
                    )
                )
            )";
        }

		if ($data['Storage_id']) {
			$filters .= " and S.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}
		if ($data['WhsDocumentSupply_id']) {
			$filters .= " and WDS.WhsDocumentSupply_id = :WhsDocumentSupply_id";
			$params['WhsDocumentSupply_id'] = $data['WhsDocumentSupply_id'];
		}
		if ($data['DrugFinance_id']) {
			$filters .= " and DF.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if ($data['WhsDocumentCostItemType_id']) {
			$filters .= " and DOR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		if ($data['Actmatters_id']) {
			$filters .= " and DCMN.Actmatters_id = :Actmatters_id";
			$params['Actmatters_id'] = $data['Actmatters_id'];
		}
		if ($data['DrugComplexMnn_id']) {
			$filters .= " and DCM.DrugComplexMnn_id = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}
		if ($data['Tradenames_id']) {
			$filters .= " and D.DrugTorg_id = :Tradenames_id";
			$params['Tradenames_id'] = $data['Tradenames_id'];
		}
		if ($data['DrugPrep_id']) {
			$filters .= " and D.DrugPrep_id = :DrugPrep_id";
			$params['DrugPrep_id'] = $data['DrugPrep_id'];
		}
		if (!empty($data['DrugComplexMnnName_Name'])) {
			$filters .= " and DCMN.DrugComplexMnnName_Name like :DrugComplexMnnName_Name";
			$params['DrugComplexMnnName_Name'] = $data['DrugComplexMnnName_Name'].'%';
		}
		if (!empty($data['DrugTorg_Name'])) {
			$filters .= " and D.DrugTorg_Name like :DrugTorg_Name";
			$params['DrugTorg_Name'] = $data['DrugTorg_Name'].'%';
		}
		if (!empty($data['SubAccountType_id'])) {
			$filters .= " and DOR.SubAccountType_id = :SubAccountType_id";
			$params['SubAccountType_id'] = $data['SubAccountType_id'];
		}
		if (!empty($data['PrepSeries_IsDefect'])) {
			$filters .= " and isnull(PS.PrepSeries_IsDefect, 1) = :PrepSeries_IsDefect";
			$params['PrepSeries_IsDefect'] = $data['PrepSeries_IsDefect'];
		}
		if (!empty($data['PrepSeries_MonthCount_Max'])) {
			$filters .= " and PSMC.PrepSeries_MonthCount <= :PrepSeries_MonthCount_Max";
			$params['PrepSeries_MonthCount_Max'] = $data['PrepSeries_MonthCount_Max'];
		}

        if (!empty($data['Sort_Type']) &&  $data['Sort_Type'] == 'defect_less6') {
            $join[] = "outer apply (
                select
                    (case
                        when PSMC.PrepSeries_MonthCount < 6 then PSMC.PrepSeries_MonthCount
                        when isnull(isDefect.YesNo_Code, 0) = 1 then 10
                        else 100
                    end) as Sort_Points
            ) SRT";
            $order[] = "SRT.Sort_Points";
        }

        $params['DefaultGoodsUnit_id'] = $default_goods_unit_id;

        $order[] = "DCMN.DrugComplexMnnName_Name";
        $order[] = "DOR.DrugOstatRegistry_id";

        if (count($join) > 0) {
            $join_clause = implode(" ", $join);
        }
        
        if (count($order) > 0) {
            $order_clause = implode(", ", $order);
            $order_clause = "
                order by
                    -- order by
                    {$order_clause} 
                    -- end order by
            ";
        }

		$query = "
		    -- variables
		    declare
	            @current_date datetime = dbo.tzGetDate();
	        -- end variables

			select
			    -- select
				DOR.DrugOstatRegistry_id,
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name,
				O.Org_Nick,
				DCMN.DrugComplexMnnName_Name,
				D.Drug_Code,
				D.DrugTorg_Name,
				D.DrugForm_Name,
				D.Drug_Dose,
				Fas.Value as Drug_Fas,
				D.Drug_Firm,
				D.Drug_RegNum,
				cast(DOR.DrugOstatRegistry_Kolvo as varchar) as DrugOstatRegistry_Kolvo,
				DOR.DrugOstatRegistry_Cost,
				WDS.WhsDocumentUc_Num,
				DF.DrugFinance_Name,
				WDCIT.WhsDocumentCostItemType_Name,
				S.Storage_Name,
				SAT.SubAccountType_Name,
				PS.PrepSeries_Ser,
				convert(varchar(10), PS.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				(
				    case
				        when isnull(isDefect.YesNo_Code, 0) = 1 then 'true'
				        else ''
				    end
				) as PrepSeries_isDefect_CK,
				PSMC.PrepSeries_MonthCount
				-- end select
			from
			    -- from
				v_DrugOstatRegistry DOR with(nolock)
				left join Org O with(nolock) on O.Org_id = DOR.Org_id
				left join v_OrgType OT with(nolock) on OT.OrgType_id = O.OrgType_id
				left join v_Storage S with(nolock) on S.Storage_id = DOR.Storage_id
				left join rls.v_Drug D with(nolock) on D.Drug_id = DOR.Drug_id
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName DCMN with(nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join v_DrugShipment DS with(nolock) on DS.DrugShipment_id = DOR.DrugShipment_id
				left join v_DrugShipmentLink DSL with(nolock) on DSL.DrugShipment_id = DS.DrugShipment_id
				left join v_WhsDocumentSupply WDS with(nolock) on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id
				left join DrugFinance DF with(nolock) on DF.DrugFinance_id = DOR.DrugFinance_id
				left join WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id
				left join v_SubAccountType SAT with (nolock) on SAT.SubAccountType_id = DOR.SubAccountType_id
				left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
				left join v_YesNo isDefect with (nolock) on isDefect.YesNo_id = PS.PrepSeries_isDefect
				left join rls.v_Nomen N with (nolock) on N.NOMEN_ID = DOR.Drug_id
				left join rls.v_DrugPack dp with (nolock) on dp.DRUGPACK_ID = N.PPACKID
				left join rls.MASSUNITS MU with (nolock) on MU.MASSUNITS_ID = N.PPACKMASSUNID
				left join rls.CUBICUNITS CU with (nolock) on CU.CUBICUNITS_ID = N.PPACKCUBUNID
				left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = isnull(DOR.GoodsUnit_id, :DefaultGoodsUnit_id)
				outer apply (
				    select
				        datediff(month, @current_date, PS.PrepSeries_GodnDate) as PrepSeries_MonthCount
				) PSMC
				outer apply(
					select (
						(case when D.Drug_Fas is not null then cast(D.Drug_Fas as varchar) else '' end)+
						(case when D.Drug_Fas is not null and coalesce(D.Drug_Volume,D.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(D.Drug_Volume,D.Drug_Mass) is not null then coalesce(D.Drug_Volume,D.Drug_Mass) else '' end)+
						(case when coalesce(N.PPACKVOLUME,N.PPACKMASS) is not null then ' (' + dp.Name + ' ' + cast(cast(coalesce(N.PPACKVOLUME,N.PPACKMASS) as decimal(10,2)) as varchar) + ' ' + coalesce(CU.SHORTNAME,MU.SHORTNAME) + ')' else '' end)
					) as Value
				) Fas
				{$join_clause}
				-- end from
			where
			    -- where
				DOR.Org_id = :Org_id
				and DOR.SubAccountType_id = 1
				and DOR.DrugOstatRegistry_Kolvo > 0
				{$filters}
				-- end where
			{$order_clause}
		";

        if (!empty($data['limit'])) {
            $result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
            $count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
            if (is_array($result) && $count !== false) {
                return array(
                    'data' => $result,
                    'totalCount' => $count
                );
            } else {
                return false;
            }
        } else {
            return $this->queryResult($query, $params);
        }
	}

	/**
	 * Получение количества медикамента в упаковке
	 */
	function getGoodsPackCount($data) {
		
		$join = '';
		$declare = '';
		
		if (empty($data['Drug_id']) || empty($data['GoodsUnit_id'])) {
			return false;
		} else {
			$params = array('Drug_id' => $data['Drug_id'], 'GoodsUnit_id' => $data['GoodsUnit_id']);
		}
		
		if ($_SESSION['region']['nick'] == 'ufa') {
			
			$declare = "Declare 
						@DrugComplexMnn_id bigint;

						Select @DrugComplexMnn_id = DrugComplexMnn_id from rls.v_Drug drug with(nolock)
							where Drug_id = :Drug_id;";
			
			$join = "left join r2.fn_GoodsPackCount ( @DrugComplexMnn_id) gpc  on gpc.DrugComplexMnn_id = drug.DrugComplexMnn_id
					";
		}
		else {
			$join = "left join v_GoodsPackCount gpc with(nolock) on gpc.DrugComplexMnn_id = drug.DrugComplexMnn_id
						";
		}

		$query = "
			{$declare}
			select top 1
				gpc.GoodsPackCount_Count
			from
				rls.v_Drug drug with(nolock)
				--left join v_GoodsPackCount gpc with(nolock) on gpc.DrugComplexMnn_id = drug.DrugComplexMnn_id
				{$join}
			where
				drug.Drug_id = :Drug_id
				and gpc.GoodsUnit_id = :GoodsUnit_id
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение единиц измерения
	 */
	function checkGoodsPackCount($data) {
		
		if (empty($data['Drug_id'])) {
			return false;
		} else {
			$params = array('Drug_id' => $data['Drug_id']);
		}

		$query = "
			select
				gpc.GoodsUnit_id
			from
				rls.v_Drug drug with(nolock)
				left join v_GoodsPackCount gpc with(nolock) on gpc.DrugComplexMnn_id = drug.DrugComplexMnn_id
			where
				drug.Drug_id = :Drug_id
		";
		//echo getDebugSQL($query, $params);exit;
		$res = $this->queryResult($query, $params);
		$resl = array();
		foreach ($res as $value) {
			array_push($resl, $value['GoodsUnit_id']);
		}
		array_push($resl, 57);
		return $resl;
	}
	
	/**
	 * Cоздание записи  оповещения для осроченных рецептов
	*/
	function saveReceptNotification($data) {
	    //$params = array();
	    $query = "
			Declare 
			    @evnRecept_id bigint = :EvnRecept_id,
			    @receptNotification_id bigint = null,
			    @receptNotification_phone varchar(50) = :receptNotification_phone,
			    @receptNotification_setDate date = :receptNotification_setDate,
			    @pmUser_id bigint = :pmUser_id,
			    @Error_Code int = null,
			    @Error_Message varchar(4000) = null;
			set nocount on;
			
			Select top 1 @receptNotification_id = receptNotification_id
			    ,@receptNotification_setDate = receptNotification_setDate
			    from receptNotification with (nolock)
				    where evnRecept_id = @evnRecept_id
			    
			if @receptNotification_id is not null
			    exec p_receptNotification_upd
				    @receptNotification_id = @receptNotification_id output,
				    @evnRecept_id = @evnRecept_id,
				    @receptNotification_phone = @receptNotification_phone,
				    @receptNotification_setDate = @receptNotification_setDate,
				    @pmUser_id = @pmUser_id,
				    @Error_Code = @Error_Code output,
				    @Error_Message = @Error_Message output;
			else
			    exec p_receptNotification_ins
				    @receptNotification_id = @receptNotification_id output,
				    @evnRecept_id = @evnRecept_id,
				    @receptNotification_phone = @receptNotification_phone,
				    @receptNotification_setDate = @receptNotification_setDate,
				    @pmUser_id = @pmUser_id,
				    @Error_Code = @Error_Code output,
				    @Error_Message = @Error_Message output;

			    Select  @receptNotification_id as receptNotification_id, @Error_Code as Error_Code, @Error_Message as Error_Mes;
			    set nocount off;

		    ";
	    
		    $params = array(
			'EvnRecept_id' => $data['EvnRecept_id'],
			'receptNotification_phone' => isset($data['receptNotification_phone']) ? $data['receptNotification_phone']: null,
			'receptNotification_setDate' => isset($data['receptNotification_setDate date']) ? isset($data['receptNotification_setDate date']): null,
			'pmUser_id' => $data['pmUser_id'],
		    );
		    
		  // echo getDebugSql($query, $params); exit();

		    $result = $this->db->query($query, $params);
		    $result = $result->result('array');
		    $result['success'] = true;
		    return $result; 
	    
	}

    /**
     * Получение остатков (аналогично вьюхе DocumentUcOst_Lite) по идентифкатору партии и текущей строки документа учета
     */
    function getOstLiteCount($data) {
        $query = "
            select
                sum(dus.DocumentUcStr_Count) as cnt
            from
                dbo.v_DocumentUcStr dus with (nolock)
                left outer join dbo.DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
            where
                dus.DocumentUcStr_oid = :DocumentUcStr_oid and
                dus.DocumentUcStr_id <> :DocumentUcStr_oid and
                dus.DocumentUcStr_id <> isnull(:DocumentUcStr_id, 0) and
                du.DrugDocumentType_id <> 4 AND
                du.DrugDocumentType_id < 6
        ";
        $cnt = $this->getFirstResultFromQuery($query, array(
            'DocumentUcStr_oid' => $data['DocumentUcStr_oid'],
            'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null
        ));

        return $cnt;
    }
}
