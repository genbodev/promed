<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Vologda_Registry_model - модель для работы с таблицей Registry
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @region       Vologda
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Stanislav Bykov
 * @version      06.11.2018
 */
require_once(APPPATH.'models/_pgsql/Registry_model.php');

class Vologda_Registry_model extends Registry_model {
	public $scheme = "r35";

	public $RegistryEvnClass;

	public $orgSmoList = [];
	public $registryEvnNum = [];
	public $registryEvnNumByNZAP = [];

	protected $ID_PAC_list = [];
	protected $persCnt = 0;
	protected $zapCnt = 0;
	protected $_IDSERV = 0;

	protected $exportPersonDataFile = '';
	protected $exportPersonDataBodyTemplate = 'registry_vologda_2_body';
	protected $exportPersonDataFooterTemplate = 'registry_vologda_2_footer';
	protected $exportPersonDataHeaderTemplate = 'registry_vologda_2_header';
	protected $exportSluchDataFile = '';
	protected $exportSluchDataFileTmp = '';
	protected $exportSluchDataBodyTemplate = 'registry_vologda_1_body';
	protected $exportSluchDataFooterTemplate = 'registry_vologda_1_footer';
	protected $exportSluchDataHeaderTemplate = 'registry_vologda_1_header';

	private $_registryTypeList = array(
		1 => array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар', 'SP_Object' => 'EvnPS', 'IsNew' => 2),
		2 => array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника', 'SP_Object' => 'EvnPL'/*, 'IsNew' => 1*/),
		6 => array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь', 'SP_Object' => 'SMP', 'IsNew' => 2),
		7 => array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения', 'SP_Object' => 'EvnPLDD13'/*, 'IsNew' => 1*/),
		9 => array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот', 'SP_Object' => 'EvnPLOrp13'/*, 'IsNew' => 1*/),
		11 => array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения', 'SP_Object' => 'EvnPLProf'/*, 'IsNew' => 1*/),
		12 => array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних', 'SP_Object' => 'EvnPLProfTeen'/*, 'IsNew' => 1*/),
		14 => array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь', 'SP_Object' => 'EvnHTM', 'IsNew' => 2),
		15 => array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги', 'SP_Object' => 'EvnUslugaPar', 'IsNew' => 2),
		20 => array('RegistryType_id' => 20, 'RegistryType_Name' => 'Взаиморасчёты', 'SP_Object' => 'EvnPLUslugaPar'/*, 'IsNew' => 1*/),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * comment
	 */
	public function loadRegistry($data) {
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';

		$this->setRegistryParamsByType($data);

		$IsZNOField = "case when R.Registry_IsZNO = 2 then 'true' else 'false' end as \"Registry_IsZNO\",";
		$IsRepeatedField = "case when R.Registry_IsRepeated = 2 then 'true' else 'false' end as \"Registry_IsRepeated\",";
		$IsPersFinField = "case when R.Registry_isPersFin = 2 then 'true' else 'false' end as \"Registry_isPersFin\",";

		if ( !empty($data['Registry_id']) ) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
			$IsZNOField = "COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",";

			$IsRepeatedField = "COALESCE(R.Registry_IsRepeated, 1) as \"Registry_IsRepeated\",";

			$IsPersFinField = "COALESCE(R.Registry_isPersFin, 1) as \"Registry_isPersFin\",";

		}

		if (!empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2) {
			$filter .= ' and R.Registry_IsNew = 2';
		}
		else {
			$filter .= ' and COALESCE(R.Registry_IsNew, 1) = 1';

		}

		if (isset($data['RegistryType_id'])) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		if (isset($data['RegistrySubType_id']))
		{
			$filter .= ' and COALESCE(R.RegistrySubType_id, 1) = :RegistrySubType_id';

			$params['RegistrySubType_id'] = $data['RegistrySubType_id'];
		} else {
			$filter .= ' and COALESCE(R.RegistrySubType_id, 1) = 1';

		}

		if ( !empty($data['RegistryStatus_id']) ) {
			// только если оплаченные!!!
			if( 4 == (int)$data['RegistryStatus_id'] ) {
				if( $data['Registry_accYear'] > 0 ) {
					$filter .= ' and to_char(cast(R.Registry_accDate as date),\'YYYY\') = :Registry_accYear';
					$params['Registry_accYear'] = $data['Registry_accYear'];
				}
			}
		}

		if ( !empty($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 5 ) {
			$query = "
				Select 
					R.RegistryQueue_id as \"Registry_id\",
					R.OrgSmo_id as \"OrgSmo_id\",
					R.Lpu_oid as \"Lpu_oid\",
					R.Org_did as \"Org_did\",
					R.LpuUnitSet_id as \"LpuUnitSet_id\",
					R.RegistryType_id as \"RegistryType_id\",
					5 as \"RegistryStatus_id\",
					2 as \"Registry_IsActive\",
					RTrim(R.Registry_Num)||' / в очереди: '||LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
					{$IsZNOField}
					COALESCE(R.Registry_IsOnceInTwoYears, 1) as \"Registry_IsOnceInTwoYears\",
					{$IsRepeatedField}
					to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
					OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
					DC.DispClass_id as \"DispClass_id\",
					DC.DispClass_Name as \"DispClass_Name\",
					R.Lpu_cid as \"Lpu_cid\",
					R.PayType_id as \"PayType_id\",
					PT.PayType_Name as \"PayType_Name\",
					PT.PayType_SysNick as \"PayType_SysNick\",
					R.KatNasel_id as \"KatNasel_id\",
					KN.KatNasel_Name as \"KatNasel_Name\",
					KN.KatNasel_SysNick as \"KatNasel_SysNick\",
					R.RegistrySubType_id as \"RegistrySubType_id\",
					{$IsPersFinField}
					0 as \"Registry_Count\",
					0 as \"Registry_ErrorCount\",
					0 as \"RegistryErrorCom_IsData\",
					0 as \"RegistryError_IsData\",
					0 as \"RegistryNoPolis_IsData\",
					0 as \"RegistryErrorTFOMS_IsData\",
					0 as \"RegistryErrorBDZ_IsData\",
					0 as \"RegistryDouble_IsData\",
					0 as \"Registry_Sum\",
					1 as \"Registry_IsProgress\",
					1 as \"Registry_IsNeedReform\",
					'' as \"Registry_updDate\"
				from {$this->scheme}.v_RegistryQueue R 
					left join v_OrgSmo OrgSmo  on OrgSmo.OrgSmo_id = R.OrgSmo_id
					left join v_DispClass DC  on DC.DispClass_id = R.DispClass_id 
					left join v_PayType PT  on PT.PayType_id = R.PayType_id
					left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				where {$filter}
			";
		}
		else {
			if ( !empty($data['RegistryStatus_id']) ) {
				$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
				$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
			}

			$query = "
				Select 
					R.Registry_id as \"Registry_id\",
					R.OrgSmo_id as \"OrgSmo_id\",
					R.Org_did as \"Org_did\",
					R.RegistryType_id as \"RegistryType_id\",
					R.RegistryStatus_id as \"RegistryStatus_id\",
					R.Registry_IsActive as \"Registry_IsActive\",
					COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					RTrim(R.Registry_Num) as \"Registry_Num\",
					{$IsZNOField}
					COALESCE(R.Registry_IsOnceInTwoYears, 1) as \"Registry_IsOnceInTwoYears\",
					{$IsRepeatedField}
					to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
					to_char(R.Registry_insDT, 'DD.MM.YYYY') as \"Registry_insDT\",
					to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
					OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
					DC.DispClass_id as \"DispClass_id\",
					DC.DispClass_Name as \"DispClass_Name\",
					R.Lpu_cid as \"Lpu_cid\",
					R.PayType_id as \"PayType_id\",
					PT.PayType_Name as \"PayType_Name\",
					PT.PayType_SysNick as \"PayType_SysNick\",
					R.KatNasel_id as \"KatNasel_id\",
					KN.KatNasel_Name as \"KatNasel_Name\",
					KN.KatNasel_SysNick as \"KatNasel_SysNick\",
					R.RegistrySubType_id as \"RegistrySubType_id\",
					{$IsPersFinField}
					COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
					COALESCE(R.Registry_ErrorCount, 0) as \"Registry_ErrorCount\",
					RegistryErrorCom.RegistryErrorCom_IsData as \"RegistryErrorCom_IsData\",
					RegistryError.RegistryError_IsData as \"RegistryError_IsData\",
					RegistryNoPolis.RegistryNoPolis_IsData as \"RegistryNoPolis_IsData\",
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\",
					RegistryErrorBDZ.RegistryErrorBDZ_IsData as \"RegistryErrorBDZ_IsData\",
					RegistryDouble.RegistryDouble_IsData as \"RegistryDouble_IsData\",
					COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					to_char(R.Registry_updDT, 'DD.MM.YYYY') || ' ' || to_char(R.Registry_updDT, 'HH24:MI:SS') as \"Registry_updDate\",
					to_char(RQH.RegistryQueueHistory_endDT, 'DD.MM.YYYY') || ' ' || to_char(RQH.RegistryQueueHistory_endDT, 'HH24:MI:SS') as \"ReformTime\",
					CASE WHEN R.Registry_IsNotInsur = 2 THEN 'true' ELSE 'false' END as \"Registry_IsNotInsurC\"
				from {$this->scheme}.v_Registry R 
					left join v_DispClass DC  on DC.DispClass_id = R.DispClass_id
					left join v_PayType PT  on PT.PayType_id = R.PayType_id
					left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
					left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id
					LEFT JOIN LATERAL(
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue 
						where Registry_id = R.Registry_id
                        limit 1
					) RQ ON true
					LEFT JOIN LATERAL(
						select RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory 
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
                        limit 1
					) RQH ON true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from dbo.v_{$this->RegistryErrorComObject} RE  where RE.Registry_id = R.Registry_id limit 1) RegistryErrorCom ON true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE  where RE.Registry_id = R.Registry_id limit 1) RegistryError ON true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_{$this->RegistryNoPolisObject} RE  where RE.Registry_id = R.Registry_id limit 1) RegistryNoPolis ON true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData from {$this->scheme}.v_RegistryErrorTFOMS RE  where RE.Registry_id = R.Registry_id and RE.RegistryErrorTFOMSType_id in (1, 3) limit 1) RegistryErrorTFOMS ON true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorBDZ_IsData from {$this->scheme}.v_RegistryErrorTFOMS RE  where RE.Registry_id = R.Registry_id and RE.RegistryErrorTFOMSType_id = 2 limit 1) RegistryErrorBDZ ON true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryDouble_IsData from {$this->scheme}.v_RegistryDouble RE  where RE.Registry_id = R.Registry_id limit 1) RegistryDouble ON true
				where 
					{$filter}
				order by
					R.Registry_endDate DESC,
					RQH.RegistryQueueHistory_endDT DESC
			";
		}

		// Если регистры относятся к СМП, тянем данные из базы СМП

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Список ошибок ТФОМС
	 */
	public function loadUnionRegistryErrorTFOMS($data) {
		if ( $data['Registry_id'] <= 0 ) {
			return false;
		}

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		$filter="(1=1)";

		if (isset($data['Person_SurName']))
		{
			$filter .= " and ps.Person_SurName iLIKE :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName iLIKE :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName iLIKE :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) iLIKE :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$regData = $this->queryResult("select 
					r.Org_did as \"Org_did\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					kn.KatNasel_SysNick as \"KatNasel_SysNick\"
				from {$this->scheme}.v_Registry r 
					left join v_KatNasel kn  on kn.KatNasel_id = r.KatNasel_id
					left join v_PayType pt  on pt.PayType_id = r.PayType_id
				where Registry_id = :Registry_id
				limit 1",
			array('Registry_id' => $data['Registry_id']));

		if (empty($regData[0])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		}

		$Org_did = $regData[0]['Org_did'];

		if ( $regData[0]['PayType_SysNick'] == 'oms' ) {
			$filter .= " and ost.OmsSprTerr_id is not null";

			if ( $regData[0]['KatNasel_SysNick'] == 'oblast' ) {
				$filter .= " and ost.KLRgn_id = 35";
			}
			else {
				$filter .= " and COALESCE(ost.KLRgn_id, 0) <> 35";
			}
		}

		if ( $this->RegistryType_id == 6 ) {
			$evnFields = "
				null as \"Evn_rid\",
				111 as \"EvnClass_id\",
			";
			$joinByEvnClass = "";
		}
		else {
			$evnFields = "
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
			";
			$joinByEvnClass = "
				left join v_Evn Evn  on Evn.Evn_id = RE.Evn_id
			";
		}

		if ( !empty($Org_did) ) {
			$filter .= " and RD.Org_did = :Org_did ";
			$params['Org_did'] = $Org_did;
		}

		$query = "
			select
				-- select
				RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				RE.Registry_id as \"Registry_id\",
				R.RegistryType_id as \"RegistryType_id\",
				RE.Evn_id as \"Evn_id\",
				{$evnFields}
				ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				ret.RegistryErrorType_Name as \"RegistryErrorType_Name\",
				RegistryErrorType_Descr || ' (' ||RETF.RegistryErrorTFOMSField_Name || ')' as \"RegistryError_Comment\",
				rtrim(COALESCE(ps.Person_SurName,'')) || ' ' || rtrim(COALESCE(ps.Person_FirName,'')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"Person_FIO\",
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				re.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
				re.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
				re.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RE.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\",
				null as \"IsGroupEvn\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				COALESCE(cast(msc.MedSpecClass_Code as varchar) || '. ', '') || msc.MedSpecClass_Name as \"MedSpecOms_Name\",
				RD.MedPersonal_Fio as \"MedPersonal_Fio\",
				to_char(RD.Evn_setDate, 'DD.MM.YYYY') as \"Evn_setDate\",
				to_char(RD.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryGroupLink RGL 
				inner join {$this->scheme}.v_Registry RF  on RF.Registry_id = RGL.Registry_pid
				inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id
				inner join {$this->scheme}.v_RegistryErrorTFOMS RE  on RE.Registry_id = R.Registry_id
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join fed.v_MedSpecClass msc  on msc.MedSpecClass_id = rd.MedSpec_id
				left join v_OrgSmo os  on os.OrgSmo_id = rd.OrgSmo_id
				left join v_LpuSection ls  on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit lu  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb  on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join RegistryErrorTFOMSField RETF  on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
				left join v_Person_bdz ps  on ps.PersonEvn_id = RD.PersonEvn_id and ps.Server_id = RD.Server_id
				{$joinByEvnClass}
				left join {$this->scheme}.RegistryErrorType ret  on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_RegistryErrorTFOMSLevel retl  on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
				left join v_OmsSprTerr ost  on ost.OmsSprTerr_id = RD.OmsSprTerr_id
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_id
				and COALESCE(RE.RegistryErrorTFOMSType_id, 0) != 2
				and COALESCE(RD.RegistryData_deleted, 1) = 1
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/

		$this->setRegistryParamsByType($data);

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
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return array|bool
	 * @description Удаление финального реестра
	 */
	public function deleteUnionRegistry($data = []) {
		$registryData = $this->getFirstRowFromQuery("
			select
				r.Registry_id as \"Registry_id\",
				r.Registry_IsNew as \"Registry_IsNew\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.RegistryStatus_id as \"RegistryStatus_id\"
			from
				{$this->scheme}.v_Registry r 
			where
				r.Registry_id = :Registry_id
		", [
			'Registry_id' => $data['id']
		]);

		// @task https://redmine.swan-it.ru/issues/163820
		if ( $registryData == false || !is_array($registryData) || count($registryData) == 0 || empty($registryData['Registry_IsNew']) || $registryData['Registry_IsNew'] != 2 ) {
			return false;
		}

		// 1. удаляем все связи
		$this->db->query("
			delete from {$this->scheme}.RegistryGroupLink where Registry_pid = :Registry_id;
			select '' as \"Error_Code\", '' as \"Error_Msg\";
		", [
			'Registry_id' => $registryData['Registry_id']
		]);

		// 2. удаляем сам реестр
		return $this->queryResult("
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_del(
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id);
		", [
			'Registry_id' => $registryData['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		]);
	}

	/**
	 * Удаление финального реестра (с удалением случаев из предварительных реестров).
	 */
	public function deleteUnionRegistryWithData($data) {
		$resp = $this->getFirstRowFromQuery("
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.RegistryStatus_id as \"RegistryStatus_id\",
				r.RegistryType_id as \"RegistryType_id\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.OrgSmo_id as \"OrgSmo_id\",
				r.Org_did as \"Org_did\",
				KN.KatNasel_Code as \"KatNasel_Code\",
				PT.PayType_SysNick as \"PayType_SysNick\",
				to_char(r.Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
			from {$this->scheme}.v_Registry r 
				left join v_KatNasel KN  on KN.KatNasel_id = r.KatNasel_id
				left join v_PayType PT  on PT.PayType_id = r.PayType_id
			where
				r.Registry_id = :Registry_id
            limit 1
		", [
			'Registry_id' => $data['Registry_id']
		]);

		if (!is_array($resp) && !empty($resp['Registry_id'])) {
			return [ 'Error_Msg' => 'Не найден реестр для удаления' ];
		}

		$data['Registry_id'] = $resp['Registry_id'];

		if ($resp['RegistrySubType_id'] != '2') {
			return [ 'Error_Msg' => 'Указанный реестр не является реестром по СМО' ];
		}

		if ($resp['RegistryStatus_id'] != 3) { // если не "В работе"
			return [ 'Error_Msg' => 'Действие доступно для реестров в статусе «В работе»' ];
		}

		$where = '';

		if ($resp['PayType_SysNick'] == 'oms') {
			$where .= " and ost.OmsSprTerr_id is not null";

			if ($resp['KatNasel_Code'] == 2) {
				// Категория населения "Иногородние"
				$where .= ' AND ost.KLRgn_id <> 35';
			}
			else if ($resp['KatNasel_Code'] == 1) {
				// Категория населения "Жители области"
				$where .= ' AND ost.KLRgn_id = 35';
			}
		}

		if ( !empty($resp['Org_did']) ) {
			$where .= ' AND rd.Org_did = :Org_did';
		}

		// достаём случаи по данному реестру
		$this->setRegistryParamsByType($data);

		$query = "
			select
				rd.Registry_id as \"Registry_id\",
				rd.{$this->RegistryDataEvnField} as \"Evn_id\"
			from
				{$this->scheme}.v_RegistryGroupLink rgl 
				inner join {$this->scheme}.v_Registry rf  on rf.Registry_id = rgl.Registry_pid
				inner join {$this->scheme}.{$this->RegistryDataObject} rd  on rgl.Registry_id = rd.Registry_id
				inner join v_OmsSprTerr ost  on ost.OmsSprTerr_id = rd.OmsSprTerr_id
				left join v_OrgSmo os  on os.OrgSmo_id = rd.OrgSmo_id
			where
				rgl.Registry_pid = :Registry_id
				{$where}
		";

		$resp_rd = $this->queryResult($query, [
			'Registry_id' => $data['Registry_id'],
			'Org_did' => !empty($resp['Org_did']) ? $resp['Org_did'] : null,
		]);

		$Registrys = [];

		foreach($resp_rd as $resp_rdone) {
			// Удаляем случаи
			$this->deleteRegistryData([
				'EvnIds' => [ $resp_rdone['Evn_id'] ],
				'Registry_id' => $resp_rdone['Registry_id'],
				'RegistryType_id' => $resp['RegistryType_id'],
				'RegistryData_deleted' => 2
			]);

			if (!in_array($resp_rdone['Registry_id'], $Registrys)) {
				$Registrys[] = $resp_rdone['Registry_id'];
			}
		}

		// произвести пересчёт обычных реестров
		foreach($Registrys as $Registry_id) {
			$this->refreshRegistry([
				'Registry_id' => $Registry_id,
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink
			where Registry_pid = :Registry_id;
		";
		$this->db->query($query, [
			'Registry_id' => $data['Registry_id']
		]);

		// 2. удаляем сам реестр
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from {$this->scheme}.p_Registry_del(
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id);
		";

		$result = $this->db->query($query, [
			'Registry_id' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		]);

		if (!is_object($result)) {
			return [ 'Error_Msg' => 'Ошибка при удалении реестра' ];
		}

		return $result->result('array');
	}


	/**
	 * Удаление случая из реестра по СМО (с удалением случаев из предварительного реестра)
	 */
	public function deleteUnionRegistryData($data) {

		$query = "select
						rgl.Registry_pid as \"Registry_pid\"
					from
						{$this->scheme}.v_RegistryGroupLink rgl 
					where
						rgl.Registry_id = :Registry_id
					limit 1";

		$resp_rd = $this->queryResult($query, array(
			'Registry_id' => $data['Registry_id']
		));

		$resp = $this->deleteRegistryData(array(
			'EvnIds' => array($data['Evn_id']),
			'Registry_id' => $data['Registry_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryData_deleted' => 2
		));
		//Обновление предварительного
		$this->refreshRegistry(array(
			'Registry_id' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		//Пересчет СМО
		$this->recountSumKolUnionRegistry(array(
			'Registry_id' => $resp_rd[0]['Registry_pid'],
		));

		return $resp;
	}


	/**
	 * comment
	 */
	public function loadUnionRegistry($data) {
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';

		if ( !empty($data['Registry_id']) ) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		if ( isset($data['RegistryType_id']) ) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}
		if (isset($data['RegistryStatus_id']))
		{
			// только если оплаченные!!!
			if( 4 == (int)$data['RegistryStatus_id'] ) {
				if( $data['Registry_accYear'] > 0 ) {
					$filter .= ' and date_part(\'year\', R.Registry_begDate) <= :Registry_accYear';
					$filter .= ' and date_part(\'year\', R.Registry_endDate) >= :Registry_accYear';
					$params['Registry_accYear'] = $data['Registry_accYear'];
				}
			}
		}

		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id']==5)) {
			$query = "
				Select
					R.RegistryQueue_id as \"Registry_id\",
					R.OrgSmo_id as \"OrgSmo_id\",
					R.RegistryType_id as \"RegistryType_id\",
					5 as \"RegistryStatus_id\",
					2 as \"Registry_IsActive\",
					RTrim(R.Registry_Num)||' / в очереди: '||LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
					to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
					OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
					COALESCE(R.Registry_isPersFin, 1) as \"Registry_isPersFin\",
					COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",
					COALESCE(R.Registry_IsOnceInTwoYears, 1) as \"Registry_IsOnceInTwoYears\",
					COALESCE(R.Registry_IsRepeated, 1) as \"Registry_IsRepeated\",
					R.PayType_id as \"PayType_id\",
					R.Org_did as \"Org_did\",
					R.KatNasel_id as \"KatNasel_id\",
					DC.DispClass_id as \"DispClass_id\",
					DC.DispClass_Name as \"DispClass_Name\",
					0 as \"Registry_Count\",
					0 as \"Registry_ErrorCount\",
					0 as \"Registry_Sum\",
					1 as \"Registry_IsProgress\",
					1 as \"Registry_IsNeedReform\",
					'' as \"Registry_updDate\"
				from {$this->scheme}.v_RegistryQueue R 
					left join v_OrgSmo OrgSmo  on OrgSmo.OrgSmo_id = R.OrgSmo_id
					left join v_DispClass DC  on DC.DispClass_id = R.DispClass_id 
				where {$filter}
			";
		}
		else {
			if (isset($data['RegistryStatus_id'])) {
				$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
				$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
			}

			$query = "
				Select
					R.Registry_id as \"Registry_id\",
					R.OrgSmo_id as \"OrgSmo_id\",
					R.RegistryType_id as \"RegistryType_id\",
					R.RegistryStatus_id as \"RegistryStatus_id\",
					R.Registry_IsActive as \"Registry_IsActive\",
					COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					RTrim(R.Registry_Num) as \"Registry_Num\",
					to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
					to_char(R.Registry_insDT, 'DD.MM.YYYY') as \"Registry_insDT\",
					to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
					OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
					COALESCE(R.Registry_isPersFin, 1) as \"Registry_isPersFin\",
					COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",
					COALESCE(R.Registry_IsOnceInTwoYears, 1) as \"Registry_IsOnceInTwoYears\",
					COALESCE(R.Registry_IsRepeated, 1) as \"Registry_IsRepeated\",
					R.PayType_id as \"PayType_id\",
					R.Org_did as \"Org_did\",
					R.KatNasel_id as \"KatNasel_id\",
					COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
					COALESCE(R.Registry_ErrorCount, 0) as \"Registry_ErrorCount\",
					COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					to_char(R.Registry_updDT, 'DD.MM.YYYY') || ' ' || to_char(R.Registry_updDT, 'HH24:MI:SS') as \"Registry_updDate\"
					,DC.DispClass_id as \"DispClass_id\"
					,DC.DispClass_Name as \"DispClass_Name\"
					,CASE WHEN R.Registry_IsNotInsur = 2 THEN 'true' ELSE 'false' END as \"Registry_IsNotInsurC\"
				from {$this->scheme}.v_Registry R 
					left join v_DispClass DC  on DC.DispClass_id = R.DispClass_id 
					left join v_OrgSmo OrgSmo  on OrgSmo.OrgSmo_id = R.OrgSmo_id
					LEFT JOIN LATERAL(
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue 
						where Registry_id = R.Registry_id
                        limit 1
					) RQ ON true
				where
					{$filter}
			";
		}

		//echo getDebugSQL($query, $params);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Установка реестра в очередь на формирование
	 * Возвращает номер в очереди
	 */
	public function saveRegistryQueue($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_saveRegistryQueueNew($data);
		}

		// Сохранение нового реестра
		if ( empty($data['Registry_id']) ) {
			$data['Registry_IsActive'] = 2;
			$operation = 'insert';
		}
		else {
			$operation = 'update';

			$registryData = $this->getFirstRowFromQuery("
				select 
					r.OrgSmo_id as \"OrgSmo_id\",
					rg.Registry_pid as \"Registry_pid\",
					r.RegistrySubType_id as \"RegistrySubType_id\",
					r.Registry_IsZNO as \"Registry_IsZNO\"
				from
					{$this->scheme}.v_Registry r 
					LEFT JOIN LATERAL (
						select 
							rgl.Registry_pid
						from
							{$this->scheme}.v_RegistryGroupLink rgl 
						where
							rgl.Registry_id = r.Registry_id
						limit 1
					) rg ON true
				where
					r.Registry_id = :Registry_id
				limit 1
			", $data);

			if ( $registryData == false ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка получения данных по реестру'));
			}

			if (!empty($data['OrgSmo_id']) && empty($registryData['RegistrySubType_id'])) {
				// Переформирование доступно только для предварительных реестров без указания СМО (если в шапке реестра указана СМО, то переформировывать/ изменять данный реестр нельзя, действие должно быть не активно).
				return array('Error_Msg' => 'Действие доступно только для предварительных реестров без СМО');
			}
			else if (!empty($registryData['Registry_pid'])) {
				// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один реестр по СМО.
				return array('Error_Msg' => 'Предварительный реестр входит в реестр по СМО, переформирование невозможно');
			}

			$data['Registry_IsZNO'] = $registryData['Registry_IsZNO'];
			$data['RegistrySubType_id'] = $registryData['RegistrySubType_id'];
		}

		if ( $operation == 'update' ) {
			$re = $this->loadRegistryQueue($data);

			if ( is_array($re) && (count($re) > 0) && $re[0]['RegistryQueue_Position'] > 0 ) {
				return array(array('success' => false, 'Error_Msg' => '<b>Запрос МО по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
			}
		}

		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'Registry_IsNotInsur' => $data['Registry_IsNotInsur'],
			'Registry_isPersFin' => $data['Registry_isPersFin'],
			'Org_did' => $data['Org_did'],
			'OrgSmo_id' => $data['OrgSmo_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'pmUser_id' => $data['pmUser_id'],
			'RegistrySubType_id' => $data['RegistrySubType_id'],
			'DispClass_id' => $data['DispClass_id'],
			'Registry_IsZNO' => $data['Registry_IsZNO'],
			'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
			'Registry_IsRepeated' => $data['Registry_IsRepeated'],
			'PayType_id' => $data['PayType_id'],
			'KatNasel_id' => $data['KatNasel_id'],
			'Lpu_cid' => $data['Lpu_cid']
		);

		if ( in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) ) {
			$query = "
				select 
					RegistryQueue_id as \"RegistryQueue_id\"
				from
					{$this->scheme}.v_RegistryQueue 
				where
					COALESCE(Registry_id,0) = COALESCE(CAST(:Registry_id as bigint),0)
					and COALESCE(RegistryType_id,0) = COALESCE(CAST(:RegistryType_id as bigint),0)
					and COALESCE(RegistrySubType_id,0) = COALESCE(CAST(:RegistrySubType_id as bigint),0)
					and COALESCE(DispClass_id,0) = COALESCE(CAST(:DispClass_id as bigint),0)
					and COALESCE(Lpu_id,0) = COALESCE(CAST(:Lpu_id as bigint),0)
					and COALESCE(Registry_IsNotInsur,0) = COALESCE(CAST(:Registry_IsNotInsur as bigint),0)	
					and COALESCE(Registry_isPersFin,0) = COALESCE(CAST(:Registry_isPersFin as bigint),0)	
					and COALESCE(Registry_begDate,CAST('1900.01.01' as date)) = COALESCE(CAST(:Registry_begDate as date),CAST('1900.01.01' as date))
					and COALESCE(Registry_endDate,CAST('1900.01.01' as date)) = COALESCE(CAST(:Registry_endDate as date),CAST('1900.01.01' as date))
					and COALESCE(OrgSmo_id,0) = COALESCE(CAST(:OrgSmo_id as bigint),0)
					and COALESCE(Registry_Num,0) = COALESCE(CAST(:Registry_Num as bigint),0)
                limit 1
			";

			$result = $this->db->query($query, $params);


			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( count($resp) > 0 ) {
					return array(array('success' => false, 'Error_Msg' => '<b>Запрос МО по данному реестру уже находится в очереди на формирование.</b>'));
				}
			}

			$query = "
				select RegistryQueue_id as \"RegistryQueue_id\", RegistryQueue_Position as \"RegistryQueue_Position\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"         
				from {$this->scheme}.p_RegistryQueue_ins(
					RegistryQueue_id := null,
					RegistryQueue_Position := null,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					Lpu_id := :Lpu_id,
					Registry_begDate := :Registry_begDate,
					Registry_endDate := :Registry_endDate,
					OrgSmo_id := :OrgSmo_id,
					Registry_Num := :Registry_Num,
					Registry_accDate := dbo.tzGetDate(), 
					RegistryStatus_id := :RegistryStatus_id,
					Registry_IsNotInsur := :Registry_IsNotInsur,
					Registry_isPersFin := :Registry_isPersFin,
					RegistrySubType_id := :RegistrySubType_id,
					DispClass_id := :DispClass_id,
					Registry_IsZNO := :Registry_IsZNO,
					Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
					Registry_IsRepeated := :Registry_IsRepeated,
					PayType_id := :PayType_id,
					Lpu_cid := :Lpu_cid,
					Org_did := :Org_did,
					Registry_Comments := null,
					pmUser_id := :pmUser_id);
			";

			//echo getDebugSql($query, $params);
			//exit;

			$result = $this->db->query($query, $params);

			if (is_object($result)) {
				return $result->result('array');
			}
			else {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}
		}
		else {
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}
	}

	/**
	 * comment
	 */
	public function reformRegistry($data) {
		$row = $this->getFirstRowFromQuery("
			select
				r.Registry_id as \"Registry_id\",
				r.Lpu_id as \"Lpu_id\",
				r.RegistryType_id as \"RegistryType_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.RegistryStatus_id as \"RegistryStatus_id\",
				r.DispClass_id as \"DispClass_id\",
				to_char(r.Registry_begDate,'YYYYMMDD') as \"Registry_begDate\",
				to_char(r.Registry_endDate,'YYYYMMDD') as \"Registry_endDate\",
				r.OrgSmo_id as \"OrgSmo_id\",
				r.Registry_Num as \"Registry_Num\",
				r.Registry_IsActive as \"Registry_IsActive\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.Registry_IsZNO as \"Registry_IsZNO\",
				r.Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
				r.Registry_IsRepeated as \"Registry_IsRepeated\",
				r.Registry_IsNew as \"Registry_IsNew\",
				r.PayType_id as \"PayType_id\",
				r.KatNasel_id as \"KatNasel_id\",
				r.Lpu_cid as \"Lpu_cid\",
				r.Org_did as \"Org_did\",
				r.Registry_isPersFin as \"Registry_isPersFin\",
				to_char(r.Registry_accDate,'YYYYMMDD') as \"Registry_accDate\",
				rg.Registry_pid as \"Registry_pid\"
			from
				{$this->scheme}.v_Registry r 
				LEFT JOIN LATERAL (
					select 
						rgl.Registry_pid
					from
						{$this->scheme}.v_RegistryGroupLink rgl 
					where
						rgl.Registry_id = r.Registry_id
                    limit 1
				) rg ON true
			where
				r.Registry_id = :Registry_id
				and COALESCE(r.RegistrySubType_id, 1) <> 2
		", $data);

		if ( $row === false || !is_array($row) || count($row) == 0 ) {
			return [ 'success' => false, 'Error_Msg' => 'Реестр не найден в базе. Возможно, он был удален.' ];
		}

		if ( (empty($row['Registry_IsNew']) || $row['Registry_IsNew'] != 2) && !empty($row['OrgSmo_id'])) {
			// Переформирование доступно только для предварительных реестров без указания СМО (если в шапке реестра указана СМО, то переформировывать/ изменять данный реестр нельзя, действие должно быть не активно).
			return [ 'Error_Msg' => 'Действие доступно только для предварительных реестров без СМО' ];
		}
		else if ( !empty($row['Registry_pid']) ) {
			if ( !empty($row['Registry_IsNew']) && $row['Registry_IsNew'] == 2) {
				// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один объединенный реестр.
				return [ 'Error_Msg' => 'Предварительный реестр входит в объединенный реестр, переформирование невозможно' ];
			}
			else {
				// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один реестр по СМО.
				return [ 'Error_Msg' => 'Предварительный реестр входит в реестр по СМО, переформирование невозможно' ];
			}
		}

		$data = array_merge($data, $row);

		return $this->saveRegistryQueue($data);
	}

	/**
	 * comment
	 */
	public function deleteRegistryError($data) {
		$filter = "";
		$params = array();
		$join = "";

		if ( $data['Registry_id'] > 0 ) {
			$params['Registry_id'] = $data['Registry_id'];
		}
		else {
			return false;
		}

		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryError_del(
				Registry_id := :Registry_id,
				Evn_id := NULL,
				RegistryErrorType_id := NULL);
		";
		// echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);
		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function deleteRegistryErrorTFOMS($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_deleteRegistryErrorTFOMSNew($data);
		}

		if ( empty($data['Registry_id']) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id'],
			'RegistryErrorTFOMSType_id' => $data['RegistryErrorTFOMSType_id'],
		);

		if ( !empty($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2 ) {
			$regData = $this->queryResult("select
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.OrgSmo_id as \"OrgSmo_id\",
				kn.KatNasel_SysNick as \"KatNasel_SysNick\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				to_char(r.Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
			from {$this->scheme}.v_Registry r 
				left join v_KatNasel kn  on kn.KatNasel_id = r.KatNasel_id
				left join v_PayType pt  on pt.PayType_id = r.PayType_id
			where
				Registry_id = :Registry_id",
				array('Registry_id' => $data['Registry_id']));

			if (empty($regData[0])) {
				return array('Error_Msg' => 'Ошибка получения данных по реестру');
			}

			$filter = "";
			if ($regData[0]['PayType_SysNick'] == 'oms') {
				$filter .= " and ost.OmsSprTerr_id is not null";

				if ($regData[0]['KatNasel_SysNick'] == 'oblast') {
					$filter .= " and ost.KLRgn_id = 35";
				}
				else {
					$filter .= " and COALESCE(ost.KLRgn_id, 0) <> 35";

				}
			}

			$mainQuery = "
				delete from {$this->scheme}.RegistryErrorTFOMS where RegistryErrorTFOMS_id in (
					select RE.RegistryErrorTFOMS_id
					from  
						{$this->scheme}.v_RegistryGroupLink RGL 
						inner join {$this->scheme}.v_Registry RF  on RF.Registry_id = RGL.Registry_pid
						inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id
						inner join {$this->scheme}.v_RegistryErrorTFOMS RE  on RE.Registry_id = R.Registry_id
						inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RE.Registry_id
							and RD.Evn_id = RE.Evn_id
						left join v_OmsSprTerr ost  on ost.OmsSprTerr_id = RD.OmsSprTerr_id
					where 
						RGL.Registry_pid = :Registry_id
						{$filter}
				) and RegistryErrorTFOMSType_id = :RegistryErrorTFOMSType_id;
			";
		}
		else {
			$mainQuery = "
				delete from {$this->scheme}.RegistryErrorTFOMS where Registry_id = :Registry_id and RegistryErrorTFOMSType_id = :RegistryErrorTFOMSType_id;
			";
		}

		$query = "
				{$mainQuery}
			select '' as \"Error_Code\", '' as \"Error_Msg\";
		";

		$result = $this->db->query($query, $params);

		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	protected function _deleteRegistryErrorTFOMSNew($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		$params = [
			'Registry_id' => $data['Registry_id'],
			'RegistryErrorTFOMSType_id' => $data['RegistryErrorTFOMSType_id'],
		];

		return $this->getFirstRowFromQuery("
				delete from {$this->scheme}.RegistryErrorTFOMS where RegistryErrorTFOMS_id in (
					select RE.RegistryErrorTFOMS_id
					from  
						{$this->scheme}.v_RegistryGroupLink RGL 
						inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id
						inner join {$this->scheme}.v_RegistryErrorTFOMS RE  on RE.Registry_id = R.Registry_id
					where 
						RGL.Registry_pid = :Registry_id
				) and RegistryErrorTFOMSType_id = :RegistryErrorTFOMSType_id;
			select '' as \"Error_Code\", '' as \"Error_Msg\";
		", $params);
	}

	/**
	 * @param $d
	 * @param $data
	 * @return array
	 */
	public function setErrorFromSMOImportRegistry($d, $data) {
		// Сохранение загружаемого реестра, точнее его ошибок
		$params = $d;
		$params['Evn_id'] = $data['Evn_id'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
		$params['Registry_id'] = $data['Registry_id'];
		$params['RegistryErrorTFOMSType_id'] = $data['RegistryErrorTFOMSType_id'];
		$params['RegistryType_id'] = $data['RegistryType_id'];

		$this->setRegistryParamsByType([ 'RegistryType_id' => $params['RegistryType_id'] ], true);

		$RegistryErrorType_id = $this->getFirstResultFromQuery("SELECT RegistryErrorType_id  as \"RegistryErrorType_id\" FROM {$this->scheme}.RegistryErrorType WHERE RegistryErrorType_Code = :S_OSN LIMIT 1", [
			'S_OSN' => $params['S_OSN']
		]);

		if ( $RegistryErrorType_id === false ) {
			$resp = $this->getFirstRowFromQuery("
				select
					RegistryErrorType_id as \"RegistryErrorType_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_RegistryErrorType_ins(
					RegistryErrorType_id := null,
					RegistryErrorType_Code := :S_OSN,
					RegistryErrorType_Name := :S_COM,
					RegistryErrorType_Descr := :S_COM,
					RegistryErrorClass_id := 1,
					pmUser_id := :pmUser_id);
			", $params);

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				return [ 'Error_Msg' => 'Ошибка при выполнении запроса к БД (добавление нового типа ошибки)' ];
			}
			else if ( !empty($resp['Error_Msg']) ) {
				return [ 'Error_Msg' => $resp['Error_Msg'] ];
			}

			$params['RegistryErrorType_id'] = $resp['RegistryErrorType_id'];
		}
		else {
			$params['RegistryErrorType_id'] = $RegistryErrorType_id;
		}

		return $this->getFirstRowFromQuery("
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryErrorTFOMS_ins(
				Registry_id := null,
				RegistryErrorTFOMSType_id := :RegistryErrorTFOMSType_id,
				RegistryErrorClass_id := 1,
				RegistryErrorSource_id := 2, -- СМО
				Evn_id := Evn_id,
				RegistryErrorType_id := :RegistryErrorType_id,
				RegistryErrorTFOMS_Comment := :S_COM,
				pmUser_id := :pmUser_id);
		", $params);
	}

	/**
	 * Получение списка ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}

		$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and ps.Person_SurName iLIKE :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName iLIKE :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName iLIKE :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) iLIKE :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$addToSelect = "";
		$leftjoin = "";

		if ( in_array($data['RegistryType_id'], array(7, 9)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd  on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id";
		}

		if ($data['RegistryType_id'] == 6) {
			$evn_object = 'CmpCallCard';
			$evn_fields = "
				null as \"Evn_rid\",
				null as \"EvnClass_id\",
			";
		} else {
			$evn_object = 'Evn';
			$evn_fields = "
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
			";
		}

		$query = "
			Select
				-- select
				RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				RE.Registry_id as \"Registry_id\",
				RE.Evn_id as \"Evn_id\",
				{$evn_fields}
				ret.RegistryErrorType_Name as \"RegistryError_FieldName\",
				rtrim(COALESCE(ps.Person_SurName,'')) || ' ' || rtrim(COALESCE(ps.Person_FirName,'')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"Person_FIO\",
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				LTRIM(RTRIM(COALESCE(RD.Polis_Ser, '') || ' ' || COALESCE(RD.Polis_Num, ''))) as \"Person_Polis\",
				RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
				RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
				RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				to_char(cast(RD.Evn_setDate as timestamp), 'DD.MM.YYYY') as \"Evn_setDate\",
				to_char(cast(RD.Evn_disDate as timestamp), 'DD.MM.YYYY') as \"Evn_disDate\",
				rd.MedPersonal_Fio as \"MedPersonal_Fio\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				lu.LpuBuilding_Name as \"LpuBuilding_Name\",
				COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				COALESCE(cast(msc.MedSpecClass_Code as varchar) || '. ', '') || msc.MedSpecClass_Name as \"MedSpecOms_Name\",
				ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				ret.RegistryErrorType_Name as \"RegistryErrorType_Name\"
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE 
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join fed.v_MedSpecClass msc  on msc.MedSpecClass_id = rd.MedSpec_id
				left join v_{$evn_object} Evn  on Evn.{$evn_object}_id = RE.Evn_id
				left join v_Person_bdz ps  on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
				left join {$this->scheme}.RegistryErrorType ret  on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_LpuSection ls  on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit lu  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb  on LB.LpuBuilding_id = LU.LpuBuilding_id
				{$leftjoin}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and COALESCE(re.RegistryErrorTFOMSType_id, 0) != 2
				and
				{$filter}
				-- end where
			order by
				-- order by
				RET.RegistryErrorType_Code
				-- end order by";

		//echo getDebugSql($query, $params);exit;

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
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка ошибок перс. данных
	 */
	public function loadRegistryErrorBDZ($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		$query = "
			select
				-- select
				re.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				COALESCE(rd.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when rd.Evn_id is not null then 1 else 2 end as \"RegistryData_notexist\",
				ps.Person_id as \"Person_id\",
				ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				ret.RegistryErrorType_Name as \"RegistryErrorType_Name\",
				re.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS re 
				left join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Registry_id = re.Registry_id
					and rd.Evn_id = re.Evn_id
				left join v_Person_bdz ps  on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
				left join {$this->scheme}.v_RegistryErrorType ret  on ret.RegistryErrorType_id = re.RegistryErrorType_id
				-- end from
			where
				-- where
				re.Registry_id = :Registry_id
				and re.RegistryErrorTFOMSType_id = 2
				-- end where
			order by
				-- order by
				ret.RegistryErrorType_Code
				-- end order by
		";

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
	 * @param $data
	 * @return bool
	 */
	public function checkErrorDataInRegistry($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_checkErrorDataInRegistryNew($data);
		}

		$this->setRegistryParamsByType($data);

		$from = "
			{$this->scheme}.v_Registry r 
		";
		$filter = "r.Registry_id = :Registry_id";

		if (!empty($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) {
			// для финального ищем случай в предварительных
			$from = "
				{$this->scheme}.v_RegistryGroupLink rgl 
				inner join {$this->scheme}.v_Registry r  on r.Registry_id = rgl.Registry_id
			";
			$filter = "rgl.Registry_pid = :Registry_id";
		}

		$emptyFilter = true;

		if (!empty($data['SL_ID'])) {
			$filter .= " and rd.Evn_id = :SL_ID";
			$params['SL_ID'] = $data['SL_ID'];
			$emptyFilter = false;
		}

		if ($emptyFilter) {
			return false;
		}

		$query = "
			select
				rd.Evn_id as \"Evn_id\",
				rd.Registry_id as \"Registry_id\",
				rd.RegistryType_id as \"RegistryType_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				rd.Person_id as \"Person_id\"
			from
				{$from}
				inner join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Registry_id = r.Registry_id
			where
				{$filter}
		";

		$params['Registry_id'] = $data['Registry_id'];

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0];
			}
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	protected function _checkErrorDataInRegistryNew($data) {
		if ( empty($data['SL_ID']) || !is_array($this->registryEvnNum) || !isset($this->registryEvnNum[$data['SL_ID']]) ) {
			return false;
		}

		$recInfo = $this->registryEvnNum[$data['SL_ID']][0];

		$this->setRegistryParamsByType([ 'RegistryType_id' => $recInfo['t'] ], true);

		return $this->getFirstRowFromQuery("
			select
				rd.Evn_id as \"Evn_id\",
				rd.Registry_id as \"Registry_id\",
				rd.RegistryType_id as \"RegistryType_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				rd.Person_id as \"Person_id\"
			from
				{$this->scheme}.v_{$this->RegistryDataObject} rd 
				inner join {$this->scheme}.v_Registry r  on r.Registry_id = rd.Registry_id
			where
				rd.Registry_id = :Registry_id
				and rd.Evn_id = :Evn_id  
		", [
			'Registry_id' => $recInfo['r'],
			'Evn_id' => $recInfo['e'],
		]);
	}

	/**
	 * comment
	 */
	function setVizitNotInReg($data)
	{
		// Проставляем ошибочные посещения обратно

		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		if ($data['Registry_id']>0)
		{
			// @task https://redmine.swan.perm.ru/issues/85241
			// Добавил в название вызываемой хранимки условие на тип реестра - финальный/предварительный
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_" . ($data['RegistrySubType_id'] == 2 ? "SMO_" : "") . "setPaid(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id);
			";

			$result = $this->db->query($query, $params);
			if (is_object($result))
			{
				return $result->result('array');
			}
			else
			{
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}
		}
		else
		{
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
		}
	}

	/**
	 * @param $d
	 * @param $data
	 * @return array
	 */
	public function setErrorFromImportRegistry($d, $data) {
		// Сохранение загружаемого реестра, точнее его ошибок
		$params = $d;
		$params['pmUser_id'] = $data['session']['pmuser_id'];

		$this->setRegistryParamsByType([ 'RegistryType_id' => $params['RegistryType_id'] ], true);

		$RegistryErrorType_id = $this->getFirstResultFromQuery("SELECT RegistryErrorType_id  as \"RegistryErrorType_id\" FROM {$this->scheme}.RegistryErrorType  WHERE RegistryErrorType_Code = :OSHIB LIMIT 1", [
			'OSHIB' => $params['OSHIB']
		]);

		if ( $RegistryErrorType_id === false ) {
			$resp = $this->getFirstRowFromQuery("
				select
					RegistryErrorType_id as \"RegistryErrorType_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_RegistryErrorType_ins(
					RegistryErrorType_id := null,
					RegistryErrorType_Code := :OSHIB,
					RegistryErrorType_Name := :OSHIB,
					RegistryErrorType_Descr := :OSHIB,
					RegistryErrorClass_id := :RegistryErrorClass_id,
					pmUser_id := :pmUser_id);
			", $params);

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				return [ 'Error_Msg' => 'Ошибка при выполнении запроса к БД (добавление нового типа ошибки)' ];
			}
			else if ( !empty($resp['Error_Msg']) ) {
				return [ 'Error_Msg' => $resp['Error_Msg'] ];
			}

			$params['RegistryErrorType_id'] = $resp['RegistryErrorType_id'];
		}
		else {
			$params['RegistryErrorType_id'] = $RegistryErrorType_id;
		}

		return $this->getFirstRowFromQuery("
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"			
			from {$this->scheme}.p_RegistryErrorTFOMS_ins(
				Registry_id := :Registry_id,
				RegistryErrorTFOMSType_id := :RegistryErrorTFOMSType_id,
				RegistryErrorClass_id := :RegistryErrorClass_id,
				RegistryErrorSource_id := 1, -- ТФОМС
				Evn_id := :Evn_id,
				RegistryErrorType_id := :RegistryErrorType_id,
				RegistryErrorTFOMS_FieldName := :IM_POL,
				RegistryErrorTFOMS_BaseElement := :BAS_EL,
				RegistryErrorTFOMS_Comment := :COMMENT,
				pmUser_id := :pmUser_id);
		", $params);
	}

	/**
	 * Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	public function loadRegistryTypeNode($data) {
		$result = [];

		foreach ( $this->_registryTypeList as $row ) {
			if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
				if ( !isset($row['IsNew']) || $row['IsNew'] == 2 ) {
					$result[] = $row;
				}
			}
			else {
				if ( !isset($row['IsNew']) || $row['IsNew'] == 1 ) {
					$result[] = $row;
				}
			}
		}

		return $result;
	}

	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data) {
		$this->setRegistryParamsByType($data);

		$xmlExportPath = '';

		$query = "
			select
				RTrim(R.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
				R.RegistryType_id as \"RegistryType_id\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.RegistryStatus_id as \"RegistryStatus_id\",
				COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
				R.OrgSmo_id as \"OrgSmo_id\",
				R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				R.Registry_IsRepeated as \"Registry_IsRepeated\",
				R.Registry_IsZNO as \"Registry_IsZNO\",
				R.DispClass_id as \"DispClass_id\",
				R.Registry_pack as \"Registry_pack\",
				COALESCE(R.RegistrySubType_id, 1) as \"RegistrySubType_id\",
				COALESCE(kn.KatNasel_SysNick, 'all') as \"KatNasel_SysNick\",
				COALESCE(pt.PayType_SysNick, 'oms') as \"PayType_SysNick\",
				SUBSTRING(to_char(Registry_endDate, 'YYYYMMDD'), 3, 4) as \"Registry_endMonth\" -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
			from
				{$this->scheme}.Registry R 
				left join v_KatNasel kn  on kn.KatNasel_id = R.KatNasel_id
				left join v_PayType pt  on pt.PayType_id = R.PayType_id
			where
				R.Registry_id = :Registry_id
		";

		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result) ) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * comment
	 */
	public function loadRegistryStatusNode($data) {
		$result = [
			['RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'],
			['RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'],
			['RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'],
			['RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные']
		];

		if (!empty($data['RegistrySubType_id'])) {
			switch ($data['RegistrySubType_id']) {
				case 2:
					$result = [
						['RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'],
						['RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'],
						['RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные']
					];
					break;
			}
		}

		return $result;
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1:
			case 14:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryDataSLObject = 'RegistryDataEvnPSSL';
				$this->RegistryDataTempObject = 'RegistryDataTempEvnPS';
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryNoPolisObject = 'RegistryEvnPSNoPolis';
				$this->RegistryEvnClass = 'EvnSection';
				break;

			case 2:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataSLObject = 'RegistryDataSL';
				$this->RegistryDataTempObject = 'RegistryData';
				$this->RegistryEvnClass = 'EvnVizit';
				break;

			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTempCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryNoPolisObject = 'RegistryCmpNoPolis';
				$this->RegistryEvnClass = 'CmpCloseCard';
				break;

			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTempDisp';
				$this->RegistryNoPolisObject = 'RegistryDispNoPolis';
				$this->RegistryEvnClass = 'EvnPLDisp';
				break;

			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTempProf';
				$this->RegistryNoPolisObject = 'RegistryProfNoPolis';
				$this->RegistryEvnClass = 'EvnPLDisp';
				break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTemp';
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryNoPolisObject = 'RegistryNoPolisPar';
				$this->RegistryEvnClass = 'EvnUslugaPar';
				break;

			case 20:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryDataSLObject = 'RegistryDataParSL';
				$this->RegistryDataTempObject = null;
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryEvnClass = 'EvnUsluga';
				break;

			default:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataSLObject = 'RegistryDataSL';
				$this->RegistryDataTempObject = 'RegistryDataTemp';
				$this->RegistryNoPolisObject = 'v_RegistryNoPolis';
				$this->RegistryEvnClass = 'EvnVizit';
				break;
		}
	}

	/**
	 *	Проверка вхождения случаев, в которых указано отделение, в реестр
	 */
	public function checkLpuSectionInRegistry($data) {
		$filterList = array(
			'R.RegistryStatus_id = 4',
			'COALESCE(RD.RegistryData_deleted, 1) = 1'
		);

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = "LS.LpuUnit_id = :LpuUnit_id";
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = "RD.LpuSection_id = :LpuSection_id";
		}

		$query = "
			-- Стационар
			(
			select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataEvnPS RD 
				left join {$this->scheme}.v_Registry R  on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS  on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
            limit 1
			)
			union all

			-- Поликлиника
			(            
			select 
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryData RD 
				left join {$this->scheme}.v_Registry R  on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS  on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . " 
            limit 1
			)
			union all

			-- СМП
			(
			select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataCmp RD 
				left join {$this->scheme}.v_Registry R  on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS  on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . " 
            limit 1
			)
			union all

			-- ДВН, ДДС, МОН
			(            
			select 
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataDisp RD 
				left join {$this->scheme}.v_Registry R  on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS  on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . " 
            limit 1
			)
		";
		$res = $this->db->query($query, $data);

		if ( !is_object($res) ) {
			return array(array('Error_Msg' => 'Ошибка БД!'));
		}

		$resp = $res->result('array');

		if ( count($resp) > 0 ) {
			if ( !empty($data['LpuSection_id']) ) {
				return "Изменение профиля отделения невозможно, для отделения существуют оплаченные реестры.";
			}
			else {
				return "Изменение типа группы отделений невозможно, для некоторых отделений существуют оплаченные реестры.";
			}
		}
		else {
			return "";
		}
	}

	/**
	 * Получение данных о счете для выгрузки объединенного реестра в XML
	 */
	protected function _loadRegistrySCHETForXmlUsing($type, $data, $isFinal = false) {
		$queryModificator = ($isFinal === true ? ", OrgSmo_id := :OrgSmo_id, Registry_IsNotInsur := :Registry_IsNotInsur" : "");
		$queryParams = array(
			'Registry_id' => $data['Registry_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null),
			'Registry_IsNotInsur' => (!empty($data['Registry_IsNotInsur']) ? $data['Registry_IsNotInsur'] : null),
		);
		$smoRegistryModificator = ($isFinal === true ? "_SMO" : "");

		$object = $this->_getRegistryObjectName($type);

		$p_schet = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expScet";

		$query = "SELECT {$p_schet} (Registry_id := :Registry_id{$queryModificator})";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		if ( is_object($result) ) {
			$header = $result->result('array');
			if (!empty($header[0])) {
				array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);
				return array($header[0]);
			}
		}

		return false;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML с помощью функций БД
	 */
	protected function _loadRegistryDataForXmlByFunc($type, $data, $sluchDataFile, $personDataFile, $sluchDataTemplate, $personDataTemplate, $isFinal = false) {
		$fnQueryModificator = ($isFinal === true ? ", :OrgSmo_id, :Registry_IsNotInsur" : "");
		$queryModificator = ($isFinal === true ? ", OrgSmo_id := :OrgSmo_id, Registry_IsNotInsur = :Registry_IsNotInsur" : "");
		$smoRegistryModificator = ($isFinal === true ? "_SMO" : "");

		$object = $this->_getRegistryObjectName($type);

		$fn_pers = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expPac_2018_f";
		$fn_sl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expVizit_2018_f";
		$fn_usl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expUsl_2018_f";
		$fn_zsl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expSL_2018_f";

		if ( in_array($type, array(1, 2, 7, 9, 11, 14, 17)) ) {
			$p_ds2 = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expDS2_2018";
		}

		if ( in_array($type, array(1, 14)) ) {
			$p_ds3 = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expDS3";
		}

		if ( in_array($type, array(1)) ) {
			$p_kslp = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expKSLP_2018";
		}

		if ( in_array($type, array(7, 9, 11, 12)) ) {
			$p_naz = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expNAZ_2018";
		}

		if ( in_array($type, array(1, 2, 14)) && ($data['Registry_IsZNO'] == 2 || $data['PayType_SysNick'] != 'oms') ) {
			$p_bdiag = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expBDIAG_2018";
			$p_bprot = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expBPROT_2018";
			$p_napr = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expNAPR_2018";
			//$p_onkousl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expONKOUSL_2018";

			if ( in_array($type, array(2)) ) {
				$p_cons = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expCONS_2018";
				//$p_lek_pr = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expLEK_PR_2018";
			}
		}

		$queryParams = array(
			'Registry_id' => $data['Registry_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null),
			'Registry_IsNotInsur' => (!empty($data['Registry_IsNotInsur']) ? $data['Registry_IsNotInsur'] : null),
		);

		$BDIAG = array();
		$BPROT = array();
		$CONS = array();
		$DS2 = array();
		$DS3 = array();
		$LEK_PR = array();
		$NAPR = array();
		$NAZ = array();
		$ONKOUSL = array();
		$SL_KOEF = array();
		$USL = array();

		$KSG_KPG_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'SL_K', 'IT_SL');
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');

		$altKeys = array(
			'SL_PODR' => 'PODR'
		,'LPU_USL' => 'LPU'
		,'LPU_1_USL' => 'LPU_1'
		,'P_OTK_USL' => 'P_OTK'
		,'PODR_USL' => 'PODR'
		,'PROFIL_USL' => 'PROFIL'
		,'DET_USL' => 'DET'
		,'TARIF_USL' => 'TARIF'
		,'PRVS_USL' => 'PRVS'
		,'VNOV_M_VAL' => 'VNOV_M'
		);

		$indexDS2 = 'DS2_DATA';
		$netValue = toAnsi('НЕТ', true);

		if ( in_array($type, array(7, 9, 11, 12)) ) {
			$indexDS2 = 'DS2_N_DATA';
		}

		// Сведения о проведении консилиума (CONS)
		if ( !empty($p_cons) ) {
			$query = "SELECT {$p_cons} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $CONS
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = array();
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS2)
		if ( !empty($p_ds2) ) {
			$query = "SELECT {$p_ds2} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS2 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS2) ) {
				return false;
			}

			// Массив $DS2
			while ( $row = $resultDS2->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = array();
				}

				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS3)
		if ( !empty($p_ds3) ) {
			$query = "SELECT {$p_ds3} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS3 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS3) ) {
				return false;
			}

			// Массив $DS3
			while ( $row = $resultDS3->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = array();
				}

				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "SELECT {$p_kslp} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultKSLP = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultKSLP) ) {
				return false;
			}

			// Массив $SL_KOEF
			while ( $row = $resultKSLP->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($SL_KOEF[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = array();
				}

				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введённом противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($p_lek_pr) ) {
			$query = "SELECT {$p_lek_pr} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $LEK_PR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$LEK_PR[$row['EvnUsluga_id']] = array();
				}

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Назначения (NAZ)
		if ( !empty($p_naz) ) {
			$query = "SELECT {$p_naz} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultNAZ = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultNAZ) ) {
				return false;
			}

			// Массив $NAZ
			while ( $row = $resultNAZ->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = array();
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// Диагностический блок (BDIAG)
		if ( !empty($p_bdiag) ) {
			$query = "SELECT {$p_bdiag} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BDIAG
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BDIAG[$row['Evn_id']]) ) {
					$BDIAG[$row['Evn_id']] = array();
				}

				$BDIAG[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об имеющихся противопоказаниях и отказах (BPROT)
		if ( !empty($p_bprot) ) {
			$query = "SELECT {$p_bprot} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BPROT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = array();
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// Направления (NAPR)
		if ( !empty($p_napr) ) {
			$query = "SELECT {$p_napr} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $NAPR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = array();
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if ( !empty($p_onkousl) ) {
			$query = "SELECT {$p_onkousl} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $ONKOUSL
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($ONKOUSL[$row['Evn_id']]) ) {
					$ONKOUSL[$row['Evn_id']] = array();
				}

				$row['LEK_PR_DATA'] = array();

				if ( isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$row['LEK_PR_DATA'] = $LEK_PR[$row['EvnUsluga_id']];
					unset($LEK_PR[$row['EvnUsluga_id']]);
				}

				$ONKOUSL[$row['Evn_id']][] = $row;
			}
		}

		// Услуги (USL)
		$query = "select * from {$fn_usl}(:Registry_id{$fnQueryModificator})";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultUSL = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultUSL) ) {
			return false;
		}

		// Массив $USL
		while ( $row = $resultUSL->_fetch_assoc() ) {
			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
			if ( !isset($USL[$row['Evn_id']]) ) {
				$USL[$row['Evn_id']] = array();
			}

			if (!empty($row['EvnUsluga_kolvo'])) {
				$row['KOL_USL'] = $row['KOL_USL'] / $row['EvnUsluga_kolvo'];
				for($i = 0; $i < $row['EvnUsluga_kolvo']; $i++) {
					$this->_IDSERV++;
					$row['IDSERV'] = $this->_IDSERV;
					$USL[$row['Evn_id']][] = $row;
				}
			} else {
				$this->_IDSERV++;
				$row['IDSERV'] = $this->_IDSERV;
				$USL[$row['Evn_id']][] = $row;
			}
		}

		// 2. джойним сразу посещения + услуги + пациенты и гребем постепенно то что получилось, сразу записывая в файл
		$result = $this->db->query("
			with zsl as (
				select * from {$fn_zsl} (:Registry_id{$fnQueryModificator})
			),
			sl as (
				select * from {$fn_sl} (:Registry_id{$fnQueryModificator})
			),
			pers as (
				select * from {$fn_pers} (:Registry_id{$fnQueryModificator})
			)
			select
				null as fields_part_1,
				z.*,
				z.MaxEvn_id as MaxEvn_zid,
				null as fields_part_2,
				s.*,
				s.Evn_id as Evn_sid,
				null as fields_part_3,
				p.*
			from
				zsl z 
				inner join sl s  on s.MaxEvn_id = z.MaxEvn_id
				inner join pers p  on p.MaxEvn_id = z.MaxEvn_id
			order by
				p.FAM, p.IM, p.OT, p.ID_PAC, s.MaxEvn_id, s.Evn_id
		", $queryParams, true);

		if ( !is_object($result) ) {
			return false;
		}

		$ZAP_ARRAY = array();
		$PACIENT_ARRAY = array();

		$recKeys = array(); // ключи для данных

		$prevID_PAC = null;

		while ( $one_rec = $result->_fetch_assoc() ) {
			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);

			if ( count($recKeys) == 0 ) {
				$recKeys = $this->_getKeysForRec($one_rec);

				if ( count($recKeys) < 3 ) {
					$this->textlog->add("Ошибка, неверное количество частей в запросе");
					return false;
				}
			}

			$zsl_key = $one_rec['MaxEvn_zid'];
			$sl_key = $one_rec['Evn_sid'];

			$ZSL = array_intersect_key($one_rec, $recKeys[1]);
			$SL = array_intersect_key($one_rec, $recKeys[2]);
			$PACIENT = array_intersect_key($one_rec, $recKeys[3]);

			$SL['Evn_id'] = $one_rec['Evn_sid'];

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PACIENT['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys, array('TARIF', 'TARIF_USL', 'SUMV_USL'));
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\n", $xml);
				file_put_contents($sluchDataFile, $xml, FILE_APPEND);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = array();
				unset($xml);

				// пишем в файл пациентов
				$xml_pers = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $PACIENT_ARRAY), true, false, array(), false);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\n", $xml_pers);
				file_put_contents($personDataFile, $xml_pers, FILE_APPEND);
				unset($PACIENT_ARRAY);
				$PACIENT_ARRAY = array();
				unset($xml_pers);
			}

			$prevID_PAC = $PACIENT['ID_PAC'];

			if ( isset($ZAP_ARRAY[$zsl_key]) ) {
				// если уже есть законченный случай, значит добавляем в него SL
				$SL['CONS_DATA'] = array();
				$SL['DS2_DATA'] = array();
				$SL['DS2_N_DATA'] = array();
				$SL['DS3_DATA'] = array();
				$SL['KSG_KPG_DATA'] = array();
				$SL['NAPR_DATA'] = array();
				$SL['NAZ_DATA'] = array();
				$SL['ONK_SL_DATA'] = array();
				$SL['SANK'] = array();
				$SL['USL'] = array();

				$KSG_KPG_DATA = array();
				$ONK_SL_DATA = array();

				if ( isset($USL[$sl_key]) ) {
					$SL['USL'] = $USL[$sl_key];
					unset($USL[$sl_key]);
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL[$indexDS2] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}
				else if ( !empty($SL['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$SL[$indexDS2] = array(array('DS2' => $SL['DS2']));
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( array_key_exists('DS2_PR', $SL) ) {
					unset($SL['DS2_PR']);
				}

				if ( array_key_exists('PR_DS2_N', $SL) ) {
					unset($SL['PR_DS2_N']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}
				else if ( !empty($SL['DS3']) ) {
					$SL['DS3_DATA'] = array(array('DS3' => $SL['DS3']));
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ( !empty($SL['VER_KSG']) ) {
					foreach ( $KSG_KPG_FIELDS as $index ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}

					$KSG_KPG_DATA['CRIT_DATA'] = array();
					$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

					$SL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
				}

				if ( isset($SL_KOEF[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				if ($data['Registry_IsZNO'] == 2 || $data['PayType_SysNick'] != 'oms') {
					// Цепляем CONS
					if ( isset($CONS[$sl_key]) ) {
						$SL['CONS_DATA'] = $CONS[$sl_key];
						unset($CONS[$sl_key]);
					}

					// Цепляем NAPR
					if ( isset($NAPR[$sl_key]) ) {
						$SL['NAPR_DATA'] = $NAPR[$sl_key];
						unset($NAPR[$sl_key]);
					}

					// Цепляем ONK_SL
					$hasONKOSLData = false;
					$ONK_SL_DATA['B_DIAG_DATA'] = array();
					$ONK_SL_DATA['B_PROT_DATA'] = array();
					$ONK_SL_DATA['ONK_USL_DATA'] = array();

					foreach ( $ONK_SL_FIELDS as $field ) {
						if ( isset($SL[$field]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA[$field] = $SL[$field];
						}
						else {
							$ONK_SL_DATA[$field] = null;
						}

						if ( array_key_exists($field, $SL) ) {
							unset($SL[$field]);
						}
					}

					if ( isset($BDIAG[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
						unset($BDIAG[$sl_key]);
					}

					if ( isset($BPROT[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
						unset($BPROT[$sl_key]);
					}

					if ( isset($ONKOUSL[$sl_key]) ) {
						$hasONKOSLData = true;

						// Цепляем LEK_PR
						foreach ( $ONKOUSL[$sl_key] as $recKey => $recData ) {
							if ( isset($LEK_PR[$recData['EvnUsluga_id']]) && $recData['USL_TIP'] == 2 ) {
								$ONKOUSL[$sl_key][$recKey]['LEK_PR_DATA'] = $LEK_PR[$recData['EvnUsluga_id']];
								unset($LEK_PR[$recData['EvnUsluga_id']]);
							}
						}

						$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
						unset($ONKOUSL[$sl_key]);
					}

					if ( $hasONKOSLData == false ) {
						$ONK_SL_DATA = array();
					}
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZAP_ARRAY[$zsl_key]['SL'][$sl_key] = $SL;
			}
			else {
				// иначе создаём новый ZAP
				$this->persCnt++;
				$this->zapCnt++;

				$SL['CONS_DATA'] = array();
				$SL['DS2_DATA'] = array();
				$SL['DS2_N_DATA'] = array();
				$SL['DS3_DATA'] = array();
				$SL['KSG_KPG_DATA'] = array();
				$SL['NAPR_DATA'] = array();
				$SL['NAZ_DATA'] = array();
				$SL['ONK_SL_DATA'] = array();
				$SL['SANK'] = array();
				$SL['USL'] = array();

				$ZSL['SL'] = array();

				$KSG_KPG_DATA = array();
				$ONK_SL_DATA = array();
				$OS_SLUCH = array();
				$VNOV_M = array();

				if ( !empty($PACIENT['OS_SLUCH']) ) {
					$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT['OS_SLUCH']);
				}

				if ( !empty($PACIENT['OS_SLUCH1']) ) {
					$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT['OS_SLUCH1']);
				}

				if ( array_key_exists('OS_SLUCH', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH']);
				}
				if ( array_key_exists('OS_SLUCH1', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH1']);
				}

				if ( !empty($ZSL['VNOV_M1']) ) {
					$VNOV_M[] = array('VNOV_M_VAL' => $ZSL['VNOV_M1']);
				}

				if ( !empty($ZSL['VNOV_M2']) ) {
					$VNOV_M[] = array('VNOV_M_VAL' => $ZSL['VNOV_M2']);
				}

				$ZSL['OS_SLUCH_DATA'] = $OS_SLUCH;
				$ZSL['VNOV_M_DATA'] = $VNOV_M;

				$PACIENT['DOST'] = array();
				$PACIENT['DOST_P'] = array();

				if ( $PACIENT['NOVOR'] == '0' ) {
					if ( empty($PACIENT['FAM']) ) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 2);
					}

					if ( empty($PACIENT['IM']) ) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 3);
					}

					if ( empty($PACIENT['OT']) || mb_strtoupper($PACIENT['OT'], 'windows-1251') == $netValue ) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 1);
					}
				}
				else {
					if ( empty($PACIENT['FAM_P']) ) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 2);
					}

					if ( empty($PACIENT['IM_P']) ) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 3);
					}

					if ( empty($PACIENT['OT_P']) || mb_strtoupper($PACIENT['OT_P'], 'windows-1251') == $netValue ) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 1);
					}
				}

				if ( isset($USL[$sl_key]) ) {
					$SL['USL'] = $USL[$sl_key];
					unset($USL[$sl_key]);
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL[$indexDS2] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}
				else if ( !empty($SL['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$SL[$indexDS2] = array(array('DS2' => $SL['DS2']));
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( array_key_exists('DS2_PR', $SL) ) {
					unset($SL['DS2_PR']);
				}

				if ( array_key_exists('PR_DS2_N', $SL) ) {
					unset($SL['PR_DS2_N']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}
				else if ( !empty($SL['DS3']) ) {
					$SL['DS3_DATA'] = array(array('DS3' => $SL['DS3']));
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ( !empty($SL['VER_KSG']) ) {
					foreach ( $KSG_KPG_FIELDS as $index ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}

					$KSG_KPG_DATA['CRIT_DATA'] = array();
					$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

					$SL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
				}

				if ( isset($SL_KOEF[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				if ($data['Registry_IsZNO'] == 2 || $data['PayType_SysNick'] != 'oms') {
					// Цепляем CONS
					if ( isset($CONS[$sl_key]) ) {
						$SL['CONS_DATA'] = $CONS[$sl_key];
						unset($CONS[$sl_key]);
					}

					// Цепляем NAPR
					if ( isset($NAPR[$sl_key]) ) {
						$SL['NAPR_DATA'] = $NAPR[$sl_key];
						unset($NAPR[$sl_key]);
					}

					// Цепляем ONK_SL
					$hasONKOSLData = false;
					$ONK_SL_DATA['B_DIAG_DATA'] = array();
					$ONK_SL_DATA['B_PROT_DATA'] = array();
					$ONK_SL_DATA['ONK_USL_DATA'] = array();

					foreach ( $ONK_SL_FIELDS as $field ) {
						if ( isset($SL[$field]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA[$field] = $SL[$field];
						}
						else {
							$ONK_SL_DATA[$field] = null;
						}

						if ( array_key_exists($field, $SL) ) {
							unset($SL[$field]);
						}
					}

					if ( isset($BDIAG[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
						unset($BDIAG[$sl_key]);
					}

					if ( isset($BPROT[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
						unset($BPROT[$sl_key]);
					}

					if ( isset($ONKOUSL[$sl_key]) ) {
						$hasONKOSLData = true;

						// Цепляем LEK_PR
						foreach ( $ONKOUSL[$sl_key] as $recKey => $recData ) {
							if ( isset($LEK_PR[$recData['EvnUsluga_id']]) && $recData['USL_TIP'] == 2 ) {
								$ONKOUSL[$sl_key][$recKey]['LEK_PR_DATA'] = $LEK_PR[$recData['EvnUsluga_id']];
								unset($LEK_PR[$recData['EvnUsluga_id']]);
							}
						}

						$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
						unset($ONKOUSL[$sl_key]);
					}

					if ( $hasONKOSLData == false ) {
						$ONK_SL_DATA = array();
					}
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZSL['N_ZAP'] = $this->zapCnt;
				$ZSL['PACIENT'] = array($PACIENT);
				$ZSL['SL'][$sl_key] = $SL;

				if (!in_array($PACIENT['ID_PAC'], $this->ID_PAC_list)) {
					$this->ID_PAC_list[] = $PACIENT['ID_PAC'];
					$PACIENT_ARRAY[$PACIENT['ID_PAC']] = $PACIENT;
				}

				$ZAP_ARRAY[$zsl_key] = $ZSL;
			}

			if ( !isset($this->registryEvnNum[$this->zapCnt]) ) {
				$this->registryEvnNum[$this->zapCnt] = array();
			}

			$this->registryEvnNum[$this->zapCnt][] = $sl_key;
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			// пишем в файл случаи
			$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys, array('TARIF', 'TARIF_USL', 'SUMV_USL'));
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\n", $xml);
			file_put_contents($sluchDataFile, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);

			// пишем в файл пациентов
			$xml_pers = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $PACIENT_ARRAY), true, false, array(), false);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\n", $xml_pers);
			file_put_contents($personDataFile, $xml_pers, FILE_APPEND);
			unset($PACIENT_ARRAY);
			unset($xml_pers);
		}

		$response = array();
		$response['persCnt'] = $this->persCnt;
		$response['zapCnt'] = $this->zapCnt;

		return $response;
	}

	/**
	 *	Установка статуса реестра
	 */
	public function setRegistryStatus($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_setRegistryStatusNew($data);
		}

		$response = [
			'success' => true,
			'Error_Msg' => '',
			'RegistryStatus_id' => 0,
		];

		try {
			$this->beginTransaction();

			if ( empty($data['Registry_ids']) || empty($data['RegistryStatus_id']) ) {
				throw new Exception('Пустые значения входных параметров');
			}

			$registryStatusList = $this->getAllowedRegistryStatuses();

			if ( !in_array($data['RegistryStatus_id'], $registryStatusList) ) {
				throw new Exception('Недопустимый статус реестра');
			}

			foreach ( $data['Registry_ids'] as $Registry_id ) {
				$data['Registry_id'] = $Registry_id;

				if ($this->checkRegistryInArchive($data)) {
					throw new Exception('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
				}

				if (!in_array($data['RegistryStatus_id'], [2, 4])) {
					if ($this->checkRegistryInGroupLink($data)) {
						throw new Exception('Реестр включён в реестр по СМО, все действия над реестром запрещены.');
					}
				}

				if ($data['RegistryStatus_id'] == 3 && $this->checkRegistryIsBlocked($data)) {
					throw new Exception('Реестр заблокирован, запрещено менять статус на "В работе".');
				}


				$r = $this->getFirstRowFromQuery("
					select 
						RegistryType_id as \"RegistryType_id\",
						RegistrySubType_id as \"RegistrySubType_id\",
						RegistryStatus_id as \"RegistryStatus_id\"
					from {$this->scheme}.v_Registry 
					where Registry_id = :Registry_id
					limit 1
				", array('Registry_id' => $data['Registry_id']));

				if ($r === false) {
					throw new Exception('Ошибка при получении данных реестра');
				}

				$RegistryType_id = $r['RegistryType_id'];
				$RegistryStatus_id = $r['RegistryStatus_id'];
				$RegistrySubType_id = $r['RegistrySubType_id'];

				$data['RegistryType_id'] = $RegistryType_id;

				$this->setRegistryParamsByType($data);

				$fields = "";

				// если перевели в работу, то снимаем признак формирования
				if ($data['RegistryStatus_id'] == 3) {
					$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, ";
				}

				if ($RegistryStatus_id == 3 && $data['RegistryStatus_id'] == 2 && (in_array($RegistryType_id, $this->getAllowedRegistryTypes($data))) && (isset($data['session']['setting']['server']['check_registry_exists_errors']) && $data['session']['setting']['server']['check_registry_exists_errors'] == 1) && (!isSuperadmin())) // если переводим "к оплате" и проверка установлена, и это не суперадмин то проверяем на ошибки
				{
					$tempscheme = 'dbo';

					$errCnt = $this->getFirstResultFromQuery("
						select (
							select count(*) as err
							from {$this->scheme}.v_{$this->RegistryErrorObject} RegistryError 
								left join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Evn_id = RegistryError.Evn_id
								left join RegistryErrorType  on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
							where RegistryError.Registry_id = :Registry_id
								and RegistryErrorType.RegistryErrorClass_id = 1
								and RegistryError.RegistryErrorClass_id = 1
								and COALESCE(rd.RegistryData_deleted,1)=1
								and rd.Evn_id is not null
						) + (
							select count(*) as err
							from {$tempscheme}.v_{$this->RegistryErrorComObject} RegistryErrorCom 
								left join RegistryErrorType  on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
							where Registry_id = :Registry_id
								and RegistryErrorType.RegistryErrorClass_id = 1
						) as \"errCnt\"
					", ['Registry_id' => $data['Registry_id']]);

					if ($errCnt !== false && !empty($errCnt)) {
						throw new Exception('Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.');
					}
				} else if ($RegistrySubType_id == 1 && $RegistryStatus_id == 2 && $data['RegistryStatus_id'] == 4) {

					$result = $this->getFirstRowFromQuery("
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from {$this->scheme}.p_Registry_setPaid(
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					", $data);

					if ($result === false) {
						throw new Exception('Ошибка при отметке оплаты реестра');
					}
					else if (!empty($result['Error_Msg'])) {
						throw new Exception($result['Error_Msg']);
					}

					$res = $this->getFirstRowFromQuery("
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from {$this->scheme}.p_RegistryData_Refresh(
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					", $data);

					if ($res === false) {
						throw new Exception('Ошибка при пересчете реестра');
					}
					else if (!empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}
				}else if($RegistrySubType_id == 1 && $RegistryStatus_id == 4 && $data['RegistryStatus_id'] == 2) {

					$result = $this->getFirstRowFromQuery("
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from {$this->scheme}.p_Registry_setUnPaid(
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					", $data);

					if ($result === false) {
						throw new Exception('Ошибка при отмене оплаты реестра');
					}
					else if (!empty($result['Error_Msg'])) {
						throw new Exception($result['Error_Msg']);
					}
				}

				// пересчитываем количество записей в реестре
				$resp_sum = $this->queryResult("
					select
						SUM(RD.RegistryData_ItogSum) as \"Sum\",
						SUM(1) as \"Count\"
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd 
					where
						RD.Registry_id = :Registry_id
				", array(
					'Registry_id' => $data['Registry_id']
				));

				$query = "
						update {$this->scheme}.Registry
						set
							RegistryStatus_id = :RegistryStatus_id,
							Registry_updDT = dbo.tzGetDate(),
							Registry_SumR = :Registry_SumR,
							Registry_Sum = :Registry_Sum,
							Registry_RecordCount = :Registry_RecordCount,
							{$fields}
							pmUser_updID = :pmUser_id
						where
							Registry_id = :Registry_id;
					select :RegistryStatus_id as \"RegistryStatus_id\", '' as \"Error_Code\", '' as \"Error_Msg\"
				";

				$result = $this->db->query($query, [
					'Registry_id' => $data['Registry_id'],
					'RegistryStatus_id' => $data['RegistryStatus_id'],
					'Registry_SumR' => !empty($resp_sum[0]['Sum']) ? $resp_sum[0]['Sum'] : 0,
					'Registry_Sum' => !empty($resp_sum[0]['Sum']) ? $resp_sum[0]['Sum'] : 0,
					'Registry_RecordCount' => !empty($resp_sum[0]['Count']) ? $resp_sum[0]['Count'] : 0,
					'pmUser_id' => $data['pmUser_id']
				]);

				if (!is_object($result)) {
					throw new Exception('Ошибка при выполнении запроса к базе данных');
				}

				if ( $data['RegistryStatus_id'] == 4 ) {
					// пишем информацию о смене статуса в историю
					$res = $this->dumpRegistryInformation([ 'Registry_id' => $data['Registry_id'] ], 4);

					if ($res === false) {
						throw new Exception('Ошибка при добавлении информации о смене статуса реестра');
					}
					else if (is_array($res) && !empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}
				}
			}

			$response['RegistryStatus_id'] = $data['RegistryStatus_id'];

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Сохранение финального реестра
	 */
	public function saveUnionRegistry($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_saveUnionRegistryNew($data);
		}

		if (!empty($data['Registry_id'])) {
			$registryData = $this->getFirstRowFromQuery("
				select
					r.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					r.RegistrySubType_id as \"RegistrySubType_id\",
					r.Registry_IsZNO as \"Registry_IsZNO\"
				from
					{$this->scheme}.v_Registry r 
				where
					r.Registry_id = :Registry_id
			", $data);

			if ( $registryData === false ) {
				return array('Error_Msg' => 'Ошибка при получении данных реестра');
			}

			if ( $registryData['RegistrySubType_id'] != 2 ) {
				return array('Error_Msg' => 'Указанный реестр не является реестром по СМО');
			}

			// @task https://redmine.swan-it.ru/issues/152091
			$data['Registry_IsZNO'] = $registryData['Registry_IsZNO'];
		}

		if (!empty($data['KatNasel_id'])) {
			/**
			 * @task https://redmine.swan-it.ru/issues/162672
			 * @comment Добавил проверку заполнения поля СМО
			 */
			$data['KatNasel_Code'] = $this->getFirstResultFromQuery("select KatNasel_Code  as \"KatNasel_Code\" from v_KatNasel  where KatNasel_id = :KatNasel_id limit 1", $data);


			if ($data['KatNasel_Code'] === false || empty($data['KatNasel_Code'])) {
				return array('Error_Msg' => 'Ошибка при получения кода категории населения');
			} else if ($data['KatNasel_Code'] == 1 && empty($data['OrgSmo_id'])) {
				return array('Error_Msg' => 'Поле СМО обязательно для заполнения');
			}
		} else {
			$data['KatNasel_Code'] = null;
		}

		// 1. сохраняем реестр по СМО
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "
			select Registry_id as \"Registry_id\", KatNasel_Code as \"KatNasel_Code\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from {$this->scheme}.{$proc}(
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				RegistryStatus_id := 3,
				Registry_Sum := NULL,
				Registry_IsActive := 2,
				Registry_Num := :Registry_Num,
				Registry_accDate := :Registry_accDate,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				OrgSmo_id := :OrgSmo_id,
				Lpu_id := :Lpu_id,
				Registry_IsNotInsur := :Registry_IsNotInsur,
				Registry_isPersFin := :Registry_isPersFin,
				RegistrySubType_id := 2,
				DispClass_id := :DispClass_id,
				Registry_IsZNO := :Registry_IsZNO,
				Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
				Registry_IsRepeated := :Registry_IsRepeated,
				PayType_id := :PayType_id,
				Org_did := :Org_did,
				KatNasel_id := :KatNasel_id,
				Registry_Comments := null,
				pmUser_id := :pmUser_id);
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		$resp = $result->result('array');

		if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
			// 2. удаляем все связи
			$query = "
				delete {$this->scheme}.RegistryGroupLink
				where Registry_pid = :Registry_id
			";
			$this->db->query($query, array(
				'Registry_id' => $resp[0]['Registry_id']
			));

			$isPersFin = "";
			if($resp[0]['KatNasel_Code'] != 2){
				$isPersFin = " and COALESCE(R.Registry_isPersFin, 1) = COALESCE(CAST(:Registry_isPersFin as bigint), 1)";

			}

			$query = "
				select
					R.Registry_id as \"Registry_id\",
					R.Registry_Num as \"Registry_Num\",
					to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
					RT.RegistryType_Name as \"RegistryType_Name\",
					RETF.FLKCount as \"FLKCount\"
				from
					{$this->scheme}.v_Registry R 
					left join v_RegistryType RT  on RT.RegistryType_id = R.RegistryType_id
					LEFT JOIN LATERAL(
						select count(RegistryErrorTFOMS_id) as FLKCount from dbo.v_RegistryErrorTFOMS  where RegistryErrorTFOMSLevel_id = 1 and Registry_id = R.Registry_id
					) RETF ON true
				where
					R.RegistrySubType_id = 1
					and R.RegistryStatus_id = 2 -- К оплате
					and R.Lpu_id = :Lpu_id
					and R.RegistryType_id = :RegistryType_id
					and R.Registry_begDate >= :Registry_begDate
					and R.Registry_endDate <= :Registry_endDate
					and COALESCE(R.DispClass_id, 0) = :DispClass_id
					and COALESCE(R.Registry_IsZNO, 1) = COALESCE(CAST(:Registry_IsZNO as bigint), 1)
					and COALESCE(R.Registry_IsOnceInTwoYears, 1) = COALESCE(CAST(:Registry_IsOnceInTwoYears as bigint), 1)
					and COALESCE(R.Registry_IsRepeated, 1) = COALESCE(CAST(:Registry_IsRepeated as bigint), 1)
--					{$isPersFin}
					and COALESCE(R.PayType_id, 1) = COALESCE(CAST(:PayType_id as bigint), 1)
					and not exists( -- и не входит в другой реестр
						select
							rgl.RegistryGroupLink_id
						from
							{$this->scheme}.v_RegistryGroupLink rgl 
							inner join {$this->scheme}.v_Registry rf  on rf.Registry_id = rgl.Registry_pid -- финальный реестр
						where
							rgl.Registry_id = R.Registry_id
							and rf.KatNasel_id = :KatNasel_id
					)
			";
			$result_reg = $this->db->query($query, array(
				'Lpu_id' => $data['Lpu_id'],
				'RegistryType_id' => $data['RegistryType_id'],
				'Registry_begDate' => $data['Registry_begDate'],
				'Registry_endDate' => $data['Registry_endDate'],
				'DispClass_id' => !empty($data['DispClass_id']) ? $data['DispClass_id'] : 0,
				'Registry_IsZNO' => $data['Registry_IsZNO'],
				'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
				'Registry_IsRepeated' => $data['Registry_IsRepeated'],
				'Registry_isPersFin' => $data['Registry_isPersFin'],
				'PayType_id' => $data['PayType_id'],
				'KatNasel_id' => $data['KatNasel_id']
			));

			if (is_object($result_reg))
			{
				$resp_reg = $result_reg->result('array');
				// 4. сохраняем новые связи
				foreach($resp_reg as $one_reg) {
					$query = "
						select RegistryGroupLink_id as \"RegistryGroupLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from {$this->scheme}.p_RegistryGroupLink_ins(
							RegistryGroupLink_id := null,
							Registry_pid := :Registry_pid,
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					";

					$this->db->query($query, array(
						'Registry_pid' => $resp[0]['Registry_id'],
						'Registry_id' => $one_reg['Registry_id'],
						'pmUser_id' => $data['pmUser_id']
					));

				}
			}

			// Обновляем Registry_sid
			// Получаем данные по реестру
			$regData = $this->getFirstRowFromQuery("
				select
					kn.KatNasel_SysNick as \"KatNasel_SysNick\",
					pt.PayType_SysNick as \"PayType_SysNick\"
				from
					{$this->scheme}.v_Registry r 
					left join v_KatNasel kn  on kn.KatNasel_id = r.KatNasel_id
					left join v_PayType pt  on pt.PayType_id = r.PayType_id
				where
					r.Registry_id = :Registry_id
			", array(
				'Registry_id' => $resp[0]['Registry_id']
			));

			if ( $regData === false || !is_array($regData) || count($regData) == 0 ) {
				return false; // Вывод ошибки не нужен, реестр находится на формировании
			}

			$KatNasel_SysNick = $regData['KatNasel_SysNick'];
			$PayType_SysNick = $regData['PayType_SysNick'];

			$this->setRegistryParamsByType($data);

			$filterList = array();
			$params = array(
				'Registry_id' => $resp[0]['Registry_id'],
			);

			if ( $PayType_SysNick == 'oms' ) {
				$filterList[] = "ost.OmsSprTerr_id is not null";

				if ( $KatNasel_SysNick == 'oblast' ) {
					$filterList[] = "ost.KLRgn_id = 35";
				}
				else {
					$filterList[] = "COALESCE(ost.KLRgn_id, 0) <> 35";
				}
			}

			if ( !empty($data['Org_did']) ) {
				$filterList[] = "RD.Org_did = :Org_did";
				$params['Org_did'] = $data['Org_did'];
			}

			$updResult = $this->getFirstRowFromQuery("
					-- Сперва обнуляем
					update {$this->RegistryEvnClass}
					set Registry_sid = null
					where Registry_sid = :Registry_id;

					-- Потом заполняем
					update {$this->RegistryEvnClass}
					set Registry_sid = :Registry_id
					from
						{$this->scheme}.v_Registry R 
						inner join {$this->scheme}.v_RegistryGroupLink RGL  on RGL.Registry_pid = R.Registry_id
						inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RGL.Registry_id
						left join v_OmsSprTerr ost  on ost.OmsSprTerr_id = rd.OmsSprTerr_id
					where
						R.Registry_id = :Registry_id
						and {$this->RegistryEvnClass}.{$this->RegistryEvnClass}_id = RD.Evn_id
						" . (count($filterList) > 0 ? "and " . implode(PHP_EOL . 'and  ', $filterList) : "") . ";
				select '' as \"Error_Code\", '' as \"Error_Msg\";
			", $params);

			if ( $updResult === false || !is_array($updResult) || count($updResult) == 0 || !empty($updResult['Error_Msg']) ) {
				return false;
			}

			$this->recountSumKolUnionRegistry(array(
				'Registry_id' => $resp[0]['Registry_id']
			));

			// пишем информацию о формировании реестра в историю
			$this->dumpRegistryInformation(array(
				'Registry_id' => $resp[0]['Registry_id']
			), 1);
		}

		return $resp;
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	public function loadUnionRegistryGrid($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_loadUnionRegistryGridNew($data);
		}

		$this->setRegistryParamsByType($data);

		$query = "
			Select
				-- select
				R.Registry_id as \"Registry_id\",
				R.RegistryType_id as \"RegistryType_id\",
				R.DispClass_id as \"DispClass_id\",
				OS.OrgSmo_Name as \"OrgSmo_Name\",
				R.Registry_Num as \"Registry_Num\",
				COALESCE(R.Registry_IsNotInsur, 1) as \"Registry_IsNotInsur\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
				COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
				R.RegistryStatus_id as \"RegistryStatus_id\",
				COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
				to_char(R.Registry_updDT, 'DD.MM.YYYY') || ' ' || to_char(R.Registry_updDT, 'HH24:MI:SS') as \"Registry_updDT\",
				DC.DispClass_Name as \"DispClass_Name\",
				case when R.Registry_IsZNO = 2 then 'true' else 'false' end as \"Registry_IsZNO\",
				case when R.Registry_IsRepeated = 2 then 'true' else 'false' end as \"Registry_IsRepeated\",
				case when R.Registry_isPersFin = 2 then 'true' else 'false' end as \"Registry_isPersFin\",
				pt.PayType_Name as \"PayType_Name\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				kn.KatNasel_Name as \"KatNasel_Name\",
				RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\"
				-- end select
			from
				-- from
				{$this->scheme}.v_Registry R  -- объединённый реестр
				left join v_OrgSmo os  on os.OrgSmo_id = r.OrgSmo_id
				left join v_DispClass DC  on DC.DispClass_id = R.DispClass_id
				left join v_PayType PT  on PT.PayType_id = R.PayType_id
				left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				LEFT JOIN LATERAL (
					select 1 as RegistryErrorTFOMS_IsData
					from {$this->scheme}.v_RegistryGroupLink RGL 
						inner join {$this->scheme}.v_RegistryErrorTFOMS RE  on RE.Registry_id = RGL.Registry_id
					where RGL.Registry_pid = R.Registry_id
						and COALESCE(RE.RegistryErrorTFOMSType_id, 0) != 2
                    limit 1

				) RegistryErrorTFOMS ON true
				-- end from
			where
				-- where
				R.Lpu_id = :Lpu_id
				and R.RegistryType_id = :RegistryType_id
				and R.RegistryStatus_id = :RegistryStatus_id
				and R.RegistrySubType_id = 2
				-- end where
			order by
				-- order by
				R.Registry_endDate DESC,
				R.Registry_updDT DESC
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	public function loadUnionRegistryChildGrid($data) {
		$query = "
			select
				-- select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
				RT.RegistryType_SysNick as \"RegistryType_SysNick\",
				RT.RegistryType_Name as \"RegistryType_Name\",
				COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
				case when R.Registry_IsRepeated = 2 then 'true' else 'false' end as \"Registry_IsRepeated\",
				case when R.Registry_isPersFin = 2 then 'true' else 'false' end as \"Registry_isPersFin\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				PT.PayType_Name as \"PayType_Name\",
				PT.PayType_SysNick as \"PayType_SysNick\",
				COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
				to_char(R.Registry_updDT, 'DD.MM.YYYY') as \"Registry_updDate\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryGroupLink RGL 
				inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id -- обычный реестр
				left join v_RegistryType RT  on RT.RegistryType_id = R.RegistryType_id
				left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				left join v_PayType PT  on PT.PayType_id = R.PayType_id
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_id
				-- end where
			order by
				-- order by
				R.Registry_id
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	}

	/**
	 * Пересчёт суммы и количества в предварительном реестре
	 */
	function recountSumKolRegistry($data) {
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.OrgSmo_id as \"OrgSmo_id\"
			from
				{$this->scheme}.v_Registry r 
			where
				r.Registry_id = :Registry_id
		";

		$resp = $this->queryResult($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Registry_id'])) {
			$data['Registry_id'] = $resp[0]['Registry_id'];
			if ($resp[0]['RegistrySubType_id'] == '2') {
				return array('Error_Msg' => 'Указанный реестр не является предварительным');
			}

			$this->setRegistryParamsByType($data);

			// считаем сумму и количество
			$resp_sum = $this->queryResult("
				select
					SUM(RD.RegistryData_ItogSum) as \"Sum\",
					SUM(1) as \"Count\"
				from
					{$this->scheme}.v_{$this->RegistryDataObject} rd 
				where
					rd.Registry_id = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id']
			));

			$this->db->query("
				update {$this->scheme}.Registry set Registry_SumR = :Registry_SumR, Registry_Sum = :Registry_Sum, Registry_RecordCount = :Registry_RecordCount where Registry_id = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id'],
				'Registry_SumR' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
				'Registry_Sum' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
				'Registry_RecordCount' => !empty($resp_sum[0]['Count'])?$resp_sum[0]['Count']:0,
			));
		}
	}

	/**
	 * Пересчёт суммы и количества в реестре по СМО
	 */
	function recountSumKolUnionRegistry($data) {
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.Org_did as \"Org_did\",
				kn.KatNasel_SysNick as \"KatNasel_SysNick\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				to_char(r.Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
			from
				{$this->scheme}.v_Registry r 
				left join v_KatNasel kn  on kn.KatNasel_id = r.KatNasel_id
				left join v_PayType pt  on pt.PayType_id = r.PayType_id
			where
				r.Registry_id = :Registry_id
		";

		$resp = $this->queryResult($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Registry_id'])) {
			$data['Registry_id'] = $resp[0]['Registry_id'];
			$data['Org_did'] = $resp[0]['Org_did'];
			if ($resp[0]['RegistrySubType_id'] != '2') {
				return array('Error_Msg' => 'Указанный реестр не является реестром по СМО');
			}

			$this->setRegistryParamsByType($data);

			$filter = "";
			if ($resp[0]['PayType_SysNick'] == 'oms') {
				$filter .= " and ost.OmsSprTerr_id is not null";

				if ($resp[0]['KatNasel_SysNick'] == 'oblast') {
					$filter .= " and ost.KLRgn_id = 35";
				}
				else {
					$filter .= " and COALESCE(ost.KLRgn_id, 0) <> 35";
				}
			}

			if ( !empty($data['Org_did']) ) {
				$filter .= " and rd.Org_did = :Org_did";
			}

			// считаем сумму и количество
			$resp_sum = $this->queryResult("
				select
					SUM(RD.RegistryData_ItogSum) as \"Sum\",
					SUM(1) as \"Count\"
				from
					{$this->scheme}.v_RegistryGroupLink RGL 
					inner join {$this->scheme}.v_Registry RF  on RF.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Registry_id = r.Registry_id
					left join v_OmsSprTerr ost  on ost.OmsSprTerr_id = rd.OmsSprTerr_id
					left join v_OrgSmo os  on os.OrgSmo_id = rd.OrgSmo_id
				where
					RGL.Registry_pid = :Registry_id
					{$filter}
			", array(
				'Registry_id' => $data['Registry_id'],
				'Org_did' => $data['Org_did'],
			));

			$this->db->query("
				update {$this->scheme}.Registry set Registry_SumR = :Registry_SumR, Registry_Sum = :Registry_Sum, Registry_RecordCount = :Registry_RecordCount where Registry_id = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id'],
				'Registry_SumR' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
				'Registry_Sum' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
				'Registry_RecordCount' => !empty($resp_sum[0]['Count'])?$resp_sum[0]['Count']:0,
			));
		}
	}

	/**
	 * Переформирование реестра
	 */
	public function reformUnionRegistry($data) {
		// получаем данные по реестру и сохраняем
		$resp = $this->queryResult("
			select
				pmUser_downID as \"pmUser_downID\",
				Registry_IsNeedReform as \"Registry_IsNeedReform\",
				Registry_orderDate as \"Registry_orderDate\",
				Registry_pack as \"Registry_pack\",
				PayType_id as \"PayType_id\",
				Org_mid as \"Org_mid\",
				OrgRSchet_mid as \"OrgRSchet_mid\",
				Registry_IsNotInsur as \"Registry_IsNotInsur\",
				Registry_IsNew as \"Registry_IsNew\",
				Registry_Comments as \"Registry_Comments\",
				Registry_IsFinal as \"Registry_IsFinal\",
				Registry_CountIsBadVol as \"Registry_CountIsBadVol\",
				RegistrySubType_id as \"RegistrySubType_id\",
				Lpu_oid as \"Lpu_oid\",
				DispClass_id as \"DispClass_id\",
				Registry_isPersFin as \"Registry_isPersFin\",
				Registry_IsZNO as \"Registry_IsZNO\",
				Registry_IsRepeated as \"Registry_IsRepeated\",
				Lpu_cid as \"Lpu_cid\",
				Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
				KatNasel_id as \"KatNasel_id\",
				Org_did as \"Org_did\",
				RegistryGroupType_id as \"RegistryGroupType_id\",
				Registry_id as \"Registry_id\",
				RegistryType_id as \"RegistryType_id\",
				Lpu_id as \"Lpu_id\",
				Registry_begDate as \"Registry_begDate\",
				Registry_endDate as \"Registry_endDate\",
				OrgSmo_id as \"OrgSmo_id\",
				LpuUnitSet_id as \"LpuUnitSet_id\",
				Registry_Num as \"Registry_Num\",
				Registry_accDate as \"Registry_accDate\",
				RegistryStatus_id as \"RegistryStatus_id\",
				Registry_Sum as \"Registry_Sum\",
				Registry_IsActive as \"Registry_IsActive\",
				Registry_ErrorCount as \"Registry_ErrorCount\",
				Registry_ErrorCommonCount as \"Registry_ErrorCommonCount\",
				Registry_RecordCount as \"Registry_RecordCount\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Registry_insDT as \"Registry_insDT\",
				Registry_updDT as \"Registry_updDT\",
				Registry_ExportPath as \"Registry_ExportPath\",
				Registry_expDT as \"Registry_expDT\",
				Registry_xmlExportPath as \"Registry_xmlExportPath\",
				Registry_xmlExpDT as \"Registry_xmlExpDT\",
				Registry_SumR as \"Registry_SumR\",
				Registry_SumF as \"Registry_SumF\",
				Registry_downDT as \"Registry_downDT\"
			from
				{$this->scheme}.v_Registry 
			where
				Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));
		if (empty($resp[0]['Registry_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		}

		// добавляем недостающие поля
		$resp[0]['pmUser_id'] = $data['pmUser_id'];

		// даты преобразуем в нормальные даты
		foreach($resp[0] as &$field) {
			if ($field instanceof DateTime) {
				$field = $field->format('Y-m-d H:i:s');
			}
		}

		return $this->saveUnionRegistry($resp[0]);
	}

	/**
	 * Установка статуса финального реестра
	 */
	public function setUnionRegistryStatus($data) {
		$registryStatusList = $this->getAllowedRegistryStatuses(array('RegistrySubType_id' => 2));

		if ( !in_array($data['RegistryStatus_id'], $registryStatusList) ) {
			return array(array('success' => false, 'Error_Msg' => "Недопустимый статус реестра"));
		}

		$response = array();

		foreach ( $data['Registry_ids'] as $Registry_id ) {
			$data['Registry_id'] = $Registry_id;

			if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id'])) {
				return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
			}

			// пересчитать все предварительные реестры
			$resp_reg = $this->queryResult("
				select
					rgl.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					r.RegistryStatus_id as \"RegistryStatus_id\"
				from
					{$this->scheme}.v_RegistryGroupLink rgl 
					inner join {$this->scheme}.v_Registry r  on r.Registry_id = rgl.Registry_pid
				where
					rgl.Registry_pid = :Registry_id
			", $data);

			if ( !empty($resp_reg[0]['RegistryType_id']) ) {
				$data['RegistryType_id'] = $resp_reg[0]['RegistryType_id'];
				$this->setRegistryParamsByType($data, true);
			}

			if ( $data['RegistryStatus_id'] == 4 ) { // если переводим в оплаченные, то вызываем p_Registry_SMO_setPaid
				$result = $this->getFirstRowFromQuery("
					select 4 as \"RegistryStatus_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_Registry_SMO_setPaid(
						Registry_id := :Registry_id,
						pmUser_id := :pmUser_id);
				", $data);

				if ( $result === false || !is_array($result) || count($result) == 0 ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке реестра как оплаченного'));
				}
				else if ( !empty($result['Error_Msg']) ) {
					return array(array('success' => false, 'Error_Msg' => $result['Error_Msg']));
				}
			}
			else if ( !empty($resp_reg[0]['RegistryStatus_id']) && $resp_reg[0]['RegistryStatus_id'] == 4 && $data['RegistryStatus_id'] == 2 ) { // если переводим из "Оплаченный" в "К оплате" - p_Registry_SMO_setUnPaid

				$resp_data = $this->queryResult("
				select
					r.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					r.RegistrySubType_id as \"RegistrySubType_id\",
					kn.KatNasel_SysNick as \"KatNasel_SysNick\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					r.RegistryStatus_id as \"RegistryStatus_id\"
				from
					{$this->scheme}.v_Registry r 
					left join v_KatNasel kn  on kn.KatNasel_id = r.KatNasel_id
					left join v_PayType pt  on pt.PayType_id = r.PayType_id
				where
					r.Registry_id = :Registry_id
			", $data);

				$check154914 = $this->checkRegistryDataIsInOtherRegistry($resp_data[0]);

				if ( !empty($check154914) ) {
					return array(array('success' => false, 'Error_Msg' => $check154914));
				}

				$result = $this->getFirstRowFromQuery("
					select 2 as \"RegistryStatus_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_Registry_SMO_setUnPaid(
						Registry_id := :Registry_id,
						pmUser_id := :pmUser_id);
				", $data);

				if ( $result === false || !is_array($result) || count($result) == 0 ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке к оплате'));
				}
				else if ( !empty($result['Error_Msg']) ) {
					return array(array('success' => false, 'Error_Msg' => $result['Error_Msg']));
				}
			}

			foreach ( $resp_reg as $one_reg ) {
				$this->recountSumKolRegistry(array(
					'Registry_id' => $one_reg['Registry_id']
				));
			}

			$this->recountSumKolUnionRegistry(array(
				'Registry_id' => $data['Registry_id']
			));

			$query = "
					update {$this->scheme}.Registry 
					set
						RegistryStatus_id = :RegistryStatus_id,
						Registry_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						Registry_id = :Registry_id;
				Select :RegistryStatus_id as \"RegistryStatus_id\", '' as \"Error_Code\", '' as \"Error_Msg\"
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
				'RegistryStatus_id' => $data['RegistryStatus_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}

			$response = $result->result('array');

			if ( !empty($response[0]['Error_Msg']) ) {
				return $response;
			}

			if ( $data['RegistryStatus_id'] == 4 ) {
				// пишем информацию о смене статуса в историю
				$this->dumpRegistryInformation(array('Registry_id' => $data['Registry_id']), 4);
			}
		}

		return $response;
	}

	/**
	 * Проверка включен ли реестр в объединённый
	 */
	function checkRegistryInGroupLink($data) {
		$data['Registry_id'] = $this->getFirstResultFromQuery("
			SELECT rgl.Registry_pid as \"Registry_pid\"
			FROM {$this->scheme}.v_RegistryGroupLink rgl 
				inner join {$this->scheme}.v_Registry rf  on rf.Registry_id = rgl.Registry_pid
			WHERE rgl.Registry_id = :Registry_id
			limit 1
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if ( !empty($data['Registry_id']) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegistryForImportXml($data) {
		return $this->getFirstRowFromQuery("
			select
				r.Registry_id as \"Registry_id\",
				r.RegistryType_id as \"RegistryType_id\",
				r.Registry_Num as \"Registry_Num\",
				to_char(r.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
				r.OrgSmo_id as \"OrgSmo_id\",
				r.RegistryGroupType_id as \"RegistryGroupType_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.Registry_IsZNO as \"Registry_IsZNO\",
				r.Registry_xmlExportPath as \"Registry_xmlExportPath\",
				kn.KatNasel_SysNick as \"KatNasel_SysNick\"
			from
				{$this->scheme}.v_Registry r 
				left join KatNasel kn on kn.KatNasel_id = r.KatNasel_id  
			where
				r.Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml($data) {
		if ( !empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2 ) {
			return $this->_exportRegistryToXmlNew($data);
		}

		$this->load->library('parser');

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');

		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$res = $this->GetRegistryXmlExport($data);

		//Временная заглушка - чтобы каждый раз происходило формирование архивов
		//$res[0]['Registry_xmlExportPath'] = null;

		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return array('Error_Msg' => 'Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
		}

		$data['DispClass_id'] = $res[0]['DispClass_id'];
		$data['OrgSmo_id'] = $res[0]['OrgSmo_id'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_IsNotInsur'] = $res[0]['Registry_IsNotInsur'];
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$data['Registry_pack'] = $res[0]['Registry_pack'];
		$data['RegistryType_id'] = $res[0]['RegistryType_id'];
		$data['RegistrySubType_id'] = $res[0]['RegistrySubType_id'];
		$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
		$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];

		if ( empty($data['Registry_pack']) ) {
			$data['Registry_pack'] = $this->_setXmlPackNum($data);
		}

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return array('Error_Msg' => 'Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
		}
		else if ((strlen($res[0]['Registry_xmlExportPath'])>0) && $data['OverrideExportOneMoreOrUseExist'] == 1) // если уже выгружен реестр
		{
			$link = $res[0]['Registry_xmlExportPath'];

			$this->textlog->add('exportRegistryToXml: вернули ссылку ' . $link);
			return array('success' => true, 'Link' => $link);
		}

		$reg_endmonth = $res[0]['Registry_endMonth'];
		$type = $res[0]['RegistryType_id'];
		$this->textlog->add('exportRegistryToXml: Тип реестра '.$res[0]['RegistryType_id']);

		if ( !in_array($type, $this->getAllowedRegistryTypes($data)) ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Данный тип реестров не обрабатывается.');
			return array('Error_Msg' => 'Данный тип реестров не обрабатывается.');
		}

		// Формирование XML в зависимости от типа.
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->SetXmlExportStatus($data);

			$SCHET = $this->_loadRegistrySCHETForXmlUsing($type, $data, $data['RegistrySubType_id'] == 2);

			switch ( $type ) {
				case 2:
				case 20:
					if ( $data['Registry_IsZNO'] == 2 ) {
						$pcode = 'LC';
						$scode = 'C';
					}
					else {
						$pcode = 'L';
						$scode = 'H';
					}
					break;

				case 7:
					if ( $data['DispClass_id'] == 2 ) {
						$pcode = 'LV';
						$scode = 'DV';
					}
					else {
						$pcode = 'LP';
						$scode = 'DP';
					}
					break;

				case 9:
					if ( $data['DispClass_id'] == 7 ) {
						$pcode = 'LU';
						$scode = 'DU';
					}
					else {
						$pcode = 'LS';
						$scode = 'DS';
					}
					break;

				case 11:
					$pcode = 'LO';
					$scode = 'DO';
					break;

				case 12:
					$pcode = 'LF';
					$scode = 'DF';
					break;

				case 14:
					$pcode = 'LT';
					$scode = 'HT';
					break;
			}

			$xml_file_body = "registry_vologda_1_body";
			$xml_file_header = "registry_vologda_1_header";
			$xml_file_footer = "registry_vologda_1_footer";

			$xml_file_person_body = "registry_vologda_2_body";
			$xml_file_person_header = "registry_vologda_2_header";
			$xml_file_person_footer = "registry_vologda_2_footer";

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];

			//Проверка на наличие созданной ранее директории
			if ( !file_exists(EXPORTPATH_REGISTRY.$out_dir) ) {
				mkdir( EXPORTPATH_REGISTRY.$out_dir );
			}
			$this->textlog->add('exportRegistryToXml: создали каталог ' . EXPORTPATH_REGISTRY . $out_dir);

			$Liter = ((($data['RegistrySubType_id'] == 2) && ($data['KatNasel_SysNick'] != "oblast")) ? "T" : "S");
			$Plat = ((($data['RegistrySubType_id'] == 2) && $data['KatNasel_SysNick'] != "oblast") ? "35" : $SCHET[0]['PLAT'] );

			// случаи
			$file_re_data_sign = $scode . "M" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";
			// временный файл для случаев
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";
			// перс. данные
			$file_re_pers_data_sign = $pcode . "M" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$this->textlog->add('exportRegistryToXml: Определили наименования файлов: ' . $file_re_data_name . ' и ' . $file_re_pers_data_name);
			$this->textlog->add('exportRegistryToXml: Создаем XML файлы на диске');

			// Заголовок для файла с перс. данными
			$ZGLV = array(
				array(
					'VERSION' => '3.1',
					'FILENAME' => $file_re_pers_data_sign,
					'FILENAME1' => $file_re_data_sign
				)
			);

			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\n" . $this->parser->parse_ext('export_xml/'.$xml_file_person_header, $ZGLV[0], true);
			$xml_pers = preg_replace("/\R\s*\R/", "\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			// Получаем данные
			$this->_resetPersCnt();
			$this->_resetZapCnt();
			$response = $this->_loadRegistryDataForXmlByFunc($type, $data, $file_re_data_name_tmp, $file_re_pers_data_name, $xml_file_body, $xml_file_person_body, $data['RegistrySubType_id'] == 2);
			$this->textlog->add('exportRegistryToXml: _loadRegistryDataForXmlByFunc: Выгрузили данные');

			if ( $response === false ) {
				throw new Exception($this->error_deadlock);
			}

			$this->textlog->add('exportRegistryToXml: Получили все данные из БД');
			$this->textlog->add('exportRegistryToXml: Количество записей реестра = ' . $response['zapCnt']);
			$this->textlog->add('exportRegistryToXml: Количество людей в реестре = ' . $response['persCnt']);

			$SCHET[0]['VERSION'] = '3.1';
			$SCHET[0]['SD_Z'] = $response['zapCnt'];
			$SCHET[0]['FILENAME'] = $file_re_data_sign;

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\n" . $this->parser->parse_ext('export_xml/' . $xml_file_header, $SCHET[0], true, false);
			$xml = preg_replace("/\R\s*\R/", "\n", $xml);
			file_put_contents($file_re_data_name, $xml);
			unset($xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($file_re_data_name_tmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($file_re_data_name_tmp, "rb");

				if ( $fh === false ) {
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($file_re_data_name_tmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл');

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/'.$xml_file_footer, array(), true, false);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/'.$xml_file_person_footer, array(), true);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
			$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);

			$H_registryValidate = true;
			$L_registryValidate = true;

			if(array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
				$settingsFLK = $this->loadRegistryEntiesSettings($res[0]);
				if(count($settingsFLK) > 0){
					$upload_path = 'RgistryFields/';
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;

					if($tplEvnDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplEvnDataXSD;

						//Проверяем валидность 1го реестра
						//Путь до шаблона
						$H_xsd_tpl = $fileEvnDataXSD;
						//Файл с ошибками, если понадобится
						$H_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_data_sign.'.html';
						//Проверка
						$H_registryValidate = $this->Reconciliation($file_re_data_name, $H_xsd_tpl, 'file', $H_validate_err_file);
					}

					if($tplPersonDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$tplPersonDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplPersonDataXSD;

						//Проверяем 2й реестр
						//Путь до шаблона
						$L_xsd_tpl = $tplPersonDataXSD;
						//Файл с ошибками, если понадобится
						$L_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_pers_data_sign.'.html';
						//Проверка
						$L_registryValidate = $this->Reconciliation($file_re_pers_data_name, $L_xsd_tpl, 'file', $L_validate_err_file);
					}
				}
			}

			$file_zip_sign = $file_re_data_sign;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$file_evn_num_name = EXPORTPATH_REGISTRY . $out_dir . "/evnnum.txt";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP ' . $file_zip_name);

			$data['Status'] = $file_zip_name;
			$this->SetXmlExportStatus($data);

			/**/
			if(!$H_registryValidate  && !$L_registryValidate){
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				return array(
					'success' => false,
					'Error_Msg' => 'Реестр не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>,
						<a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>, 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 20
				);

			}
			elseif(!$H_registryValidate){
				//Скинули статус
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

				return array(
					'success' => false,
					'Error_Msg' => 'Файл H реестра не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						(<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>),
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 21
				);
			}
			elseif(!$L_registryValidate){
				//Скинули статус
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($file_re_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

				return array(
					'success' => false,
					'Error_Msg' => 'Файл L реестра не прошёл ФЛК: <a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						(<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>), 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 22
				);
			}
			else {
				unlink($file_re_data_name);
				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

				$this->_saveRegistryEvnNum([
					'Registry_EvnNum' => $this->registryEvnNum,
					'FileName' => $file_evn_num_name,
				]);

				// Пишем информацию о выгрузке в историю
				$this->dumpRegistryInformation($data, 2);

				$this->textlog->add('exportRegistryToXml: вернули ссылку ' . $file_zip_name);
				return array('success' => true, 'Link' => $file_zip_name);
			}
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->SetXmlExportStatus($data);
			$this->textlog->add("exportRegistryToXml:".toUtf($e->getMessage()));
			return array('success' => false, 'Error_Msg' => toUtf($e->getMessage()));
		}
	}

	/**
	 *  ФЛК контроль
	 */
	function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
	{
		libxml_use_internal_errors(true);
		$xml = new DOMDocument();

		if($type == 'file'){
			$xml->load($xml_data);
		}
		elseif($type == 'string'){
			$xml->loadXML($xml_data);
		}

		if (!$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();

			file_put_contents($output_file_name, $res_errors);

			return false;
		}
		else
			return true;
	}

	/**
	 * ФЛК контроль
	 * Метод для формирования листа ошибок при сверке xml по шаблоны xsd
	 * Task#18694
	 * @return (string)
	 */
	function libxml_display_errors()
	{
		$errors = libxml_get_errors();

		foreach ($errors as $error)
		{
			$return = "<br/>\n";

			switch($error->level)
			{
				case LIBXML_ERR_WARNING:
					$return .= "<b>Warning $error->code</b>: ";
					break;
				case LIBXML_ERR_ERROR:
					$return .= "<b>Error $error->code</b>: ";
					break;
				case LIBXML_ERR_FATAL:
					$return .= "<b>Fatal Error $error->code</b>: ";
					break;
			}

			$return .= trim($error->message);

			if($error->file)
			{
				$return .=    " in <b>$error->file</b>";
			}

			$return .= " on line <b>$error->line</b>\n";
			print $return;
		}
		libxml_clear_errors();
	}

	/**
	 *	Получение списка случаев реестра
	 */
	public function loadRegistryData($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( isset($data['start']) && isset($data['limit']) && !($data['start'] >= 0 && $data['limit'] >= 0) )  {
			return false;
		}

		// Получаем данные по реестру
		$regData = $this->getFirstRowFromQuery("
			select
				kn.KatNasel_SysNick as \"KatNasel_SysNick\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				r.RegistryType_id as \"RegistryType_id\",
				COALESCE(r.Registry_IsNew, 1) as \"Registry_IsNew\",
				r.Org_did as \"Org_did\"
			from
				{$this->scheme}.v_Registry r 
				left join v_KatNasel kn  on kn.KatNasel_id = r.KatNasel_id
				left join v_PayType pt  on pt.PayType_id = r.PayType_id
			where
				r.Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);

		if ( $regData === false || !is_array($regData) || count($regData) == 0 ) {
			return false; // Вывод ошибки не нужен, реестр находится на формировании
		}

		$data['RegistryType_id'] = $regData['RegistryType_id'];

		$KatNasel_SysNick = $regData['KatNasel_SysNick'];
		$PayType_SysNick = $regData['PayType_SysNick'];
		$Org_did = $regData['Org_did'];
		$Registry_IsNew = $regData['Registry_IsNew'];

		$this->setRegistryParamsByType($data);

		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		];

		$fieldsList = [];
		$filterList = [];
		$joinList = [];

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "RD.Person_SurName iLIKE :Person_SurName";
			$params['Person_SurName'] = trim($data['Person_SurName'])."%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "RD.Person_FirName iLIKE :Person_FirName";
			$params['Person_FirName'] = trim($data['Person_FirName'])."%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "RD.Person_SecName iLIKE :Person_SecName";
			$params['Person_SecName'] = trim($data['Person_SecName'])."%";
		}

		if ( !empty($data['Polis_Num']) ) {
			$filterList[] = "RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = "RD.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filterList[] = "LS.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['NumCard']) ) {
			$filterList[] = "RD.NumCard = :NumCard";
			$params['NumCard'] = $data['NumCard'];
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( !empty($data['LpuBuilding_id']) ) {
			$filterList[] = "LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ( !empty($data['Evn_disDate']) ) {
			$filterList[] = "to_char(RD.Evn_disDate, 'DD.MM.YYYY') = :Evn_disDate";

			$params['Evn_disDate'] = date('d.m.Y', strtotime($data['Evn_disDate']));
		}

		if ( $this->RegistryType_id == 1 ) {
			$fieldsList[] = "m.Mes_Code as \"Mes_Code\"";
			$joinList[] = "left join v_EvnSection es  on ES.EvnSection_id = RD.Evn_id";
			$joinList[] = "left join v_MesOld m  on m.Mes_id = ES.Mes_id";
		}

		$joinList[] = "left join v_UslugaComplex U  on U.UslugaComplex_id = RD.UslugaComplex_id";
		$joinList[] = "left join v_Diag D  on RD.Diag_id =  D.Diag_id";
		$joinList[] = "left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id";
		$joinList[] = "left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id";

		$fieldsList[] = "U.UslugaComplex_Code as \"Usluga_Code\"";
		$fieldsList[] = "D.Diag_Code as \"Diag_Code\"";
		$fieldsList[] = "case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as \"Paid\"";
		$fieldsList[] = "LB.LpuBuilding_Name as \"LpuBuilding_Name\"";

		if ( $this->RegistryDataObject == 'RegistryDataEvnPS' ) {
			$joinList[] = "left join v_HTMedicalCareClass htm  on htm.HTMedicalCareClass_id = rd.HTMedicalCareClass_id";

			$fieldsList[] = "htm.HTMedicalCareClass_GroupCode as \"HTMedicalCareClass_GroupCode\"";
			$fieldsList[] = "htm.HTMedicalCareClass_Name as \"HTMedicalCareClass_Name\"";
		}

		if ( $data['filterRecords'] == 2 ) {
			$filterList[] = "COALESCE(RD.Paid_id, 1) = 2";
		}
		else if ( $data['filterRecords'] == 3 ) {
			$filterList[] = "COALESCE(RD.Paid_id, 1) = 1";
		}

		$select_uet = "RD.RegistryData_KdFact as \"RegistryData_Uet\"";
		if ( in_array($this->RegistryType_id, array(2, 16)) ) {
			$select_uet = "RD.EvnVizit_UetOMS as \"RegistryData_Uet\"";
		}
		$fieldsList[] = $select_uet;

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$joinList[] = "left join v_EvnPLDisp epd  on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id as \"DispClass_id\"";
		}

		if ( !empty($data['OrgSmo_id']) ) {
			if ( $data['OrgSmo_id'] == 8 ) {
				$filterList[] = "COALESCE(os.OrgSMO_RegNomC, '') = '' and RD.Polis_id IS NOT NULL";
			}
			else {
				$filterList[] = "RD.OrgSmo_id = :OrgSmo_id";
				$params['OrgSmo_id'] = $data['OrgSmo_id'];
			}
		}

		$fieldsList[] = "COALESCE(RD.RegistryData_Sum_R, 0) as \"RegistryData_Sum_R\"";
		$fieldsList[] = "COALESCE(RD.Paid_id, 1) as \"RegistryData_IsPaid\"";


		$evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');
		$from = "
			{$this->scheme}.v_Registry R 
			inner join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = R.Registry_id
		";

		if (
			!empty($data['RegistrySubType_id'])
			&& $data['RegistrySubType_id'] == 2
		) {
			// для финального берём по другому
			if ( $PayType_SysNick == 'oms' ) {
				$filterList[] = "ost.OmsSprTerr_id is not null";
				if ( $KatNasel_SysNick == 'oblast' ) {
					$filterList[] = "ost.KLRgn_id = 35";
				}
				else {
					$filterList[] = "COALESCE(ost.KLRgn_id, 0) <> 35";
				}
			}

			if ( !empty($Org_did) ) {
				$filterList[] = "RD.Org_did = :Org_did";
				$params['Org_did'] = $Org_did;
			}

			$from = "
				{$this->scheme}.v_Registry R 
				inner join {$this->scheme}.v_RegistryGroupLink RGL  on RGL.Registry_pid = R.Registry_id
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RGL.Registry_id
				left join v_OmsSprTerr ost  on ost.OmsSprTerr_id = rd.OmsSprTerr_id
			";

			if ( $data['filterRecords'] == 2 ) {
				$filterList[] = "COALESCE(RD.Paid_id, 1) = 2";
			}
			else if ( $data['filterRecords'] == 3 ) {
				$filterList[] = "COALESCE(RD.Paid_id, 1) = 1";
			}

			$filterList[] = "COALESCE(RD.RegistryData_deleted, 1) = 1";

			$fieldsList[] = "RegistryErrorTFOMS.ErrTfoms_Count as \"ErrTfoms_Count\"";

			$joinList[] = "
				LEFT JOIN LATERAL (
					select count(*) as ErrTfoms_Count
					from {$this->scheme}.v_RegistryErrorTFOMS RET 
					where RD.Evn_id = RET.Evn_id
						and RD.Registry_id = RET.Registry_id
				) RegistryErrorTFOMS ON true
			";
		}
		else {
			$fieldsList[] = "RegistryError.Err_Count as \"Err_Count\"";

			$joinList[] = "
				LEFT JOIN LATERAL (
					select count(*) as Err_Count
					from {$this->scheme}.v_{$this->RegistryErrorObject} RE 
					where RD.Evn_id = RE.Evn_id
						and RD.Registry_id = RE.Registry_id
				) RegistryError ON true
			";
		}

		$query = "
			Select
				-- select
				RD.Evn_id as \"Evn_id\",
				RD.Evn_rid as \"Evn_rid\",
				RD.EvnClass_id as \"EvnClass_id\",
				RD.Registry_id as \"Registry_id\",
				RD.RegistryType_id as \"RegistryType_id\",
				RD.Person_id as \"Person_id\",
				RD.Server_id as \"Server_id\",
				PersonEvn.PersonEvn_id as \"PersonEvn_id\",
				" . implode(', ', $fieldsList) . ",
				RD.RegistryData_deleted as \"RegistryData_deleted\",
				RD.Polis_Num as \"Polis_Num\",
				RTrim(RD.NumCard) as \"EvnPL_NumCard\",
				RTrim(RD.Person_FIO) as \"Person_FIO\",
				to_char(RD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				LTRIM(RTRIM(COALESCE(RD.Polis_Ser, '') || ' ' || COALESCE(RD.Polis_Num, ''))) as \"Person_Polis\",
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
				RD.LpuSection_id as \"LpuSection_id\",
				RTrim(RD.LpuSection_Name) as \"LpuSection_Name\",
				COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				COALESCE(cast(msc.MedSpecClass_Code as varchar) || '. ', '') || msc.MedSpecClass_Name as \"MedSpecOms_Name\",
				RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
				to_char(RD.{$evnVizitPLSetDateField}, 'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
				to_char(RD.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\",
				RD.RegistryData_Tariff as \"RegistryData_Tariff\",
				RD.RegistryData_KdPay as \"RegistryData_KdPay\",
				RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
				RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
				case when COALESCE(e.Evn_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"
				-- end select
			from
				-- from
				{$from}
				left join v_Evn e  on e.Evn_id = rd.Evn_id
				left join v_LpuSection LS  on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join fed.v_MedSpecClass msc  on msc.MedSpecClass_id = rd.MedSpec_id
				" . implode(PHP_EOL, $joinList) . "
				LEFT JOIN LATERAL (
					select PersonEvn_id
					from v_PersonEvn PE 
					where RD.Person_id = PE.Person_id
						and PE.PersonEvn_insDT <= COALESCE(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
                    limit 1
				) PersonEvn ON true
			-- end from
			where
				-- where
				R.Registry_id = :Registry_id
				" . (count($filterList) > 0 ? "and " . implode(PHP_EOL . 'and  ', $filterList) : "") . "
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";

		if ( !empty($data['nopaging']) ) {
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}

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

		if ( !is_object($result) ) {
			return false;
		}

		$response = array();
		$response['data'] = $result->result('array');
		$response['totalCount'] = $count;

		return $response;
	}

	/**
	 * @param array $data
	 * @return bool
	 * @description Возвращает список настроек ФЛК
	 */
	public function loadRegistryEntiesSettings($data = []) {
		if ( empty($data['RegistryGroupType_id']) && empty($data['RegistryType_id']) ) {
			return false;
		}

		$params = [];

		if ( !empty($data['RegistryGroupType_id']) ) {
			$where = ' AND RegistryGroupType_id = :RegistryGroupType_id';
			$params['RegistryGroupType_id'] = $data['RegistryGroupType_id'];
		}
		else {
			$where = ' AND RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		return $this->queryResult("
			SELECT 
				 FLKSettings_id as \"FLKSettings_id\"
				,RegistryType_id as \"RegistryType_id\"
				,FLKSettings_EvnData as \"FLKSettings_EvnData\"
				,FLKSettings_PersonData as \"FLKSettings_PersonData\"
			FROM v_FLKSettings 
			WHERE 
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then '2030-01-01'
						else FLKSettings_endDate
					end
				AND FLKSettings_EvnData iLIKE '%vologda%'
				{$where}
			LIMIT 1
		", $params);
	}

	/**
	 * Возвращает наименование объекта для хранимых процедур в зависимости от типа реестра
	 */
	private function _getRegistryObjectName($type) {
		$result = '';

		if ( array_key_exists($type, $this->_registryTypeList) ) {
			$result = $this->_registryTypeList[$type]['SP_Object'];
		}

		return $result;
	}

	/**
	 * Получает ключи
	 */
	private function _getKeysForRec($rec) {
		$recKeys = array();
		$part = 1;

		foreach($rec as $key => $value) {
			if (strpos($key, 'fields_part_') !== false) {
				$part = intval(str_replace('fields_part_', '', $key));
				continue;
			}
			if (!isset($recKeys[$part])) {
				$recKeys[$part] = array();
			}
			$recKeys[$part][$key] = null;
		}

		return $recKeys;
	}

	/**
	 * Обнуление счетчика записей
	 */
	private function _resetPersCnt() {
		$this->persCnt = 0;
		return true;
	}

	/**
	 * Обнуление счетчика записей
	 */
	private function _resetZapCnt() {
		$this->zapCnt = 0;
		return true;
	}

	/**
	 * @param $data
	 * @return int
	 * @description Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	protected function _setXmlPackNum($data) {

		//для новых реестров нет RegistrySubType_id
		$filter="";
		if ( empty($data['Registry_IsNew']) ) {
			$filter='and COALESCE(RegistrySubType_id, 0) = COALESCE(CAST(:RegistrySubType_id as bigint), 0)';
		}
		$query = "
				with cte1 as (
					select Registry_pack as packNum
					from {$this->scheme}.v_Registry 
					where Registry_id = :Registry_id
					limit 1
				),
				cte2 as (
					select case when (select packNum from cte1) is null then 
					(
							select max(Registry_pack)
							from {$this->scheme}.v_Registry 
							where Lpu_id = :Lpu_id
								and SUBSTRING(to_char(Registry_endDate, 'YYYYMMDD'), 4, 4) = :Registry_endMonth
								and Registry_pack is not null
								and RegistryType_id = :RegistryType_id
								{$filter}
								and COALESCE(DispClass_id, 0) = COALESCE(CAST(:DispClass_id as bigint), 0)
								and COALESCE(Registry_IsNew, 0) = COALESCE(CAST(:Registry_IsNew as bigint), 0)
					) else (select packNum from cte1) end as packNum
				),
				cte3 as (
					select case when (select packNum from cte2) is null then
					(COALESCE((
							select max(Registry_pack)
							from {$this->scheme}.v_Registry 
							where Lpu_id = :Lpu_id
								and SUBSTRING(to_char(Registry_endDate, 'YYYYMMDD'), 4, 4) = :Registry_endMonth
								and Registry_pack is not null
								and RegistryType_id = :RegistryType_id
								{$filter}
								and COALESCE(DispClass_id, 0) = COALESCE(CAST(:DispClass_id as bigint), 0)
								and COALESCE(Registry_IsNew, 0) = COALESCE(CAST(:Registry_IsNew as bigint), 0)
						), 0) + 1) else (select packNum from cte2) end as packNum
				)
				update {$this->scheme}.Registry 
					set Registry_pack = (select packNum from cte3)
				where (select packNum from cte2) is null and Registry_id = :Registry_id;
			select (select packNum from cte3) as \"packNum\", '' as \"Error_Msg\";
		";
		$result = $this->db->query($query, $data);

		// echo getDebugSQL($query, $data);

		$packNum = 0;

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['packNum']) ) {
				$packNum = $response[0]['packNum'];
			}
		}

		return $packNum;
	}

	/**
	 *	Список случаев по пациентам без документов ОМС
	 */
	function loadRegistryNoPolis($data)
	{
		$this->setRegistryParamsByType($data);

		$join = "";
		$filter = "(1=1)";

		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array('Registry_id' => $data['Registry_id']);
		if ($data['Registry_id'] == 6) {
			$evn_join = "";
			$set_date_time = " null as \"Evn_setDT\"";
		} else {
			$evn_join = " left join v_Evn Evn  on Evn.Evn_id = RNP.Evn_id";
			$set_date_time = " to_char(Evn.Evn_setDT, 'DD.MM.YYYY')||' '||to_char(Evn.Evn_setDT, 'HH24:MI:SS') as \"Evn_setDT\"";
		}

		$query = "
			Select
				RNP.Registry_id as \"Registry_id\",
				RNP.Evn_id as \"Evn_id\",
				Evn.Evn_rid as \"Evn_rid\",
				RNP.Person_id as \"Person_id\",
				Evn.Server_id as \"Server_id\",
				Evn.PersonEvn_id as \"PersonEvn_id\",
				rtrim(COALESCE(RNP.Person_SurName,'')) || ' ' || rtrim(COALESCE(RNP.Person_FirName,'')) || ' ' || rtrim(COALESCE(RNP.Person_SecName, '')) as \"Person_FIO\",
				to_char(RNP.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				{$set_date_time}
			from
				{$this->scheme}.v_{$this->RegistryNoPolisObject} RNP 
				{$evn_join}
				{$join}
			where
				RNP.Registry_id=:Registry_id
				and {$filter}
			order by
				RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName
		";

		$result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);

		$result_count = $this->db->query(getCountSQL($query), $params);

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
	}

	/**
	 * @param $PersonEvn_id
	 * @param $Registry_id
	 * @param $RegistrySubType_id
	 * @param bool $Registry_IsNew
	 * @return array|bool
	 * @description Получение данных пациента по идентификатору события
	 */
	public function getPersonDataByPersonEvnId($PersonEvn_id, $Registry_id, $RegistrySubType_id, $Registry_IsNew = false) {
		if ( $Registry_IsNew == true ) {
			return $this->_getPersonDataByPersonEvnIdNew($PersonEvn_id, $Registry_id);
		}

		$joinList = array();

		if ( 2 == $RegistrySubType_id ) {
			$joinList[] = "inner join {$this->scheme}.v_RegistryGroupLink rgl  on rgl.Registry_id = rd.Registry_id";
			$RegistryField = 'rgl.Registry_pid';
		}
		else {
			$RegistryField = 'rd.Registry_id';
		}

		return $this->getFirstRowFromQuery("
			select 
				p.Person_id as \"Person_id\",
				p.Person_Surname as \"FAM\",
				p.Person_Firname as \"IM\",
				p.Person_Secname as \"OT\",
				to_char(p.Person_Birthday, 'YYYY-MM-DD') as \"DR\",
				to_char(e.Evn_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\"
			from {$this->scheme}.v_{$this->RegistryDataObject} rd 
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "") . "
				inner join Evn e  on e.Evn_id = rd.Evn_id
				inner join v_Person_all p on p.PersonEvn_id = e.PersonEvn_id
					and p.Server_id = e.Server_id
			where rd.PersonEvn_id = :PersonEvn_id
				and {$RegistryField} = :Registry_id
			order by e.Evn_setDT
			limit 1
		", array(
			'PersonEvn_id' => $PersonEvn_id,
			'Registry_id' => $Registry_id,
		));
	}

	/**
	 * @param $PersonEvn_id
	 * @param $Registry_pid
	 * @return array|bool
	 * @description Получение данных пациента по идентификатору события (новые реестры)
	 */
	protected function _getPersonDataByPersonEvnIdNew($PersonEvn_id, $Registry_pid) {
		$unionRegistryTypes = $this->getUnionRegistryTypes($Registry_pid);

		foreach ( $unionRegistryTypes as $RegistryType_id ) {
			$this->setRegistryParamsByType([ 'RegistryType_id' => $RegistryType_id ], true);

			$resp = $this->getFirstRowFromQuery("
				select 
					p.Person_id as \"Person_id\",
					p.Person_Surname as \"FAM\",
					p.Person_Firname as \"IM\",
					p.Person_Secname as \"OT\",
					to_char(p.Person_Birthday, 'YYYY-MM-DD') as \"DR\",
					to_char(e.Evn_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"Evn_setDT\"
				from {$this->scheme}.v_{$this->RegistryDataObject} rd 
					inner join {$this->scheme}.v_RegistryGroupLink rgl  on rgl.Registry_id = rd.Registry_id
					inner join Evn e  on e.Evn_id = rd.Evn_id
					inner join v_Person_all p on p.PersonEvn_id = e.PersonEvn_id
						and p.Server_id = e.Server_id
				where rd.PersonEvn_id = :PersonEvn_id
					and rgl.Registry_pid = :Registry_id
				order by e.Evn_setDT
				limit 1
			", [
				'PersonEvn_id' => $PersonEvn_id,
				'Registry_id' => $Registry_pid,
			]);

			if ( is_array($resp) && count($resp) > 0 ) {
				return $resp;
			}
		}

		return false;
	}

	/**
	 *	Обновление данных полиса
	 */
	public function updatePersonPolis($data) {
		$dbmain = $this->load->database('default', true);

		try {
			if ( !array_key_exists(intval($data['SMO']), $this->orgSmoList) ) {
				$this->orgSmoList[intval($data['SMO'])] = $this->getFirstResultFromQuery("
					select OrgSmo_id  as \"OrgSmo_id\" from v_OrgSMO  where Orgsmo_f002smocod = :SMO and KLRgn_id = 35 limit 1
				", array(
					'SMO' => intval($data['SMO'])
				));
			}

			$data['OrgSmo_id'] = $this->orgSmoList[intval($data['SMO'])];

			if ( empty($data['OrgSmo_id']) ) {
				$data['OrgSmo_id'] = null;
			}

			$Federal_Num = NULL; // ???
			$Person_id = $data['Person_id'];
			$Polis_begDate = empty($data['BEGDT']) ? NULL : date('Y-m-d', strtotime($data['BEGDT']));
			$Polis_endDate = empty($data['ENDDT']) ? null : date('Y-m-d', strtotime($data['ENDDT']));
			$Polis_closeDate = empty($data['BEGDT']) ? NULL : date('Y-m-d', strtotime($data['BEGDT'] . "-1 days"));
			$Polis_nextDate = empty($Polis_endDate) ? NULL : date('Y-m-d', strtotime($Polis_endDate . "+1 days"));
			$Polis_Num = $data['NPOLIS'];
			$Polis_Ser = $data['SPOLIS'];
			$PolisFormType_id = $data['PolisFormType_id'];
			$PolisType_id = $data['VPOLIS'];
			$OmsSprTerr_id = $data['OmsSprTerr_id']; // ???
			$OrgSmo_id = $data['OrgSmo_id'];


			if ( $PolisType_id == 4 ) {
				$Federal_Num = $Polis_Num;
			}

			$hasPersonEvnChanges = false;

			// 1) Нужно вытащить предыдущий документ ОМС и правильно закрыть его при необходимости
			$query = "
				select 
					pa.Server_id as \"Server_id\",
					pa.PersonEvn_id as \"PersonEvn_id\",
					pol.Polis_id as \"Polis_id\",
					pol.OmsSprTerr_id as \"OmsSprTerr_id\",
					pol.OrgSmo_id as \"OrgSmo_id\",
					pol.PolisType_id as \"PolisType_id\",
					pol.PolisFormType_id as \"PolisFormType_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pa.Person_EdNum as \"Person_EdNum\",
					to_char(pol.Polis_begDate, 'YYYY-MM-DD') as \"Polis_begDate\",
					to_char(pol.Polis_endDate, 'YYYY-MM-DD') as \"Polis_endDate\"
				from v_Person_all pa  
					inner join v_Polis pol  ON pa.Polis_id = pol.Polis_id
				where
					pa.Person_id = :Person_id
					and pa.PersonEvnClass_id = 8
					and cast(pol.Polis_begDate as date) < cast(:Polis_begDate as date)
				order by pol.Polis_begDate desc
				limit 1
			";
			$queryParams = array(
				'Person_id' => $Person_id,
				'Polis_begDate' => $Polis_begDate,
			);
			$result = $dbmain->query($query, $queryParams);

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса (получение полисных данных) (Person_id=' . $Person_id . ')');
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 && ($Polis_closeDate != $resp[0]['Polis_endDate'])) {
				if ( !(empty($Polis_closeDate) || $Polis_closeDate >= $resp[0]['Polis_begDate']) ) {
					throw new Exception('Ошибка при попытке выполнении запроса (обновление полисных данных) (Person_id=' . $Person_id . ') - дата закрытия полиса не может быть меньше даты открытия');
				}

				$query = "
					select error_message as \"ErrMsg\"
					from p_PersonPolis_upd(
						PersonPolis_id := :PersonEvn_id,
						Server_id := :Server_id,
						Person_id := :Person_id,
						OmsSprTerr_id := :OmsSprTerr_id,
						PolisType_id := :PolisType_id,
						PolisFormType_id := :PolisFormType_id,
						OrgSmo_id := :OrgSmo_id,
						Polis_Ser := :Polis_Ser,
						Polis_Num := :Polis_Num,
						Polis_begDate := :Polis_begDate,
						Polis_endDate := :Polis_endDate,
						pmUser_id := :pmUser_id);
				";

				$result = $dbmain->query($query, array(
					'PersonEvn_id' => $resp[0]['PersonEvn_id'],
					'Server_id' => $resp[0]['Server_id'],
					'Person_id' => $Person_id,
					'OmsSprTerr_id' => $resp[0]['OmsSprTerr_id'],
					'PolisType_id' => $resp[0]['PolisType_id'],
					'PolisFormType_id' => $resp[0]['PolisFormType_id'],
					'OrgSmo_id' => $resp[0]['OrgSmo_id'],
					'Polis_Ser' => $resp[0]['Polis_Ser'],
					'Polis_Num' => $resp[0]['Polis_Num'],
					'Polis_begDate' => $resp[0]['Polis_begDate'],
					'Polis_endDate' => $Polis_closeDate,
					'pmUser_id' => $data['pmUser_id'],
				));

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при выполнении запроса (обновление полисных данных) (Person_id=' . $Person_id . ')');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					throw new Exception('Ошибка при обновлении полисных данных (Person_id=' . $Person_id . ')');
				}
				else if ( !empty($resp[0]['ErrMsg']) ) {
					throw new Exception($resp[0]['ErrMsg']);
				}

				$hasPersonEvnChanges = true;
			}

			// 2. Ищем и брабатываем документы ОМС, которые начали действовать после даты начала действия документа ОМС из загруженного файла
			$query = "
				select
					pa.Server_id as \"Server_id\",
					pa.PersonEvn_id as \"PersonEvn_id\",
					pol.Polis_id as \"Polis_id\",
					pol.OmsSprTerr_id as \"OmsSprTerr_id\",
					pol.OrgSmo_id as \"OrgSmo_id\",
					pol.PolisType_id as \"PolisType_id\",
					pol.PolisFormType_id as \"PolisFormType_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pa.Person_EdNum as \"Person_EdNum\",
					to_char(pol.Polis_begDate, 'YYYY-MM-DD') as \"Polis_begDate\",
					to_char(pol.Polis_endDate, 'YYYY-MM-DD') as \"Polis_endDate\"
				from v_Person_all pa  
					inner join v_Polis pol  ON pa.Polis_id = pol.Polis_id
				where
					pa.Person_id = :Person_id
					and pa.PersonEvnClass_id = 8
					and cast(pol.Polis_begDate as date) >= cast(:Polis_begDate as date)
			";
			$queryParams = array(
				'Person_id' => $Person_id,
				'Polis_begDate' => $Polis_begDate
			);
			$result = $dbmain->query($query, $queryParams);

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса (получение полисных данных) (Person_id=' . $Person_id . ')');
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 ) {
				foreach ( $resp as $row )  {
					if (
						$row['Server_id'] == 0
						&& $OrgSmo_id == $row['OrgSmo_id']
						&& $PolisType_id == $row['PolisType_id']
						&& $Polis_begDate == $row['Polis_begDate']
						&& $Polis_Num == $row['Polis_Num']
					) {
						$recordToUpdate = $row;
						continue;
					}
					else if ( empty($Polis_endDate) || (!empty($row['Polis_endDate']) && $Polis_endDate >= $row['Polis_endDate']) ) {
						$query = "
							select error_message as \"ErrMsg\"
							from dbo.xp_PersonRemovePersonEvn(
								Server_id := :Server_id,
								PersonEvn_id := :PersonEvn_id,
								Person_id := :Person_id
							);
						";
						$result = $dbmain->query($query, array(
							'Server_id' => $row['Server_id'],
							'PersonEvn_id' => $row['PersonEvn_id'],
							'Person_id' => $Person_id,
						));

						if ( !is_object($result) ) {
							throw new Exception('Ошибка при выполнении запроса (удаление документа ОМС) (Person_id=' . $Person_id . ')');
						}

						$resp = $result->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							throw new Exception('Ошибка при удалении документа ОМС (Person_id=' . $Person_id . ')');
						}
						else if ( !empty($resp[0]['ErrMsg']) ) {
							throw new Exception($resp[0]['ErrMsg']);
						}

						$hasPersonEvnChanges = true;
					}
					else {
						// Сдвигаем дату начала действия следующих ДОМС на 1 день после даты окончания ДОМС из ответа ТФОМС
						if ($row['Polis_begDate'] != $Polis_nextDate) {
							$query = "
								select error_message as \"ErrMsg\"	
								from p_PersonPolis_upd(
									PersonPolis_id := :PersonEvn_id,
									Server_id := :Server_id,
									Person_id := :Person_id,
									OmsSprTerr_id := :OmsSprTerr_id,
									PolisType_id := :PolisType_id,
									PolisFormType_id := :PolisFormType_id,
									OrgSmo_id := :OrgSmo_id,
									Polis_Ser := :Polis_Ser,
									Polis_Num := :Polis_Num,
									Polis_begDate := :Polis_begDate,
									Polis_endDate := :Polis_endDate,
									pmUser_id := :pmUser_id);
							";

							if (!(empty($row['Polis_endDate']) || $row['Polis_endDate'] >= $Polis_nextDate)) {
								throw new Exception('Ошибка при попытке выполнении запроса (сдвиг даты начала действия более поздних документов ОМС) (Person_id=' . $Person_id . ') - дата закрытия полиса не может быть меньше даты открытия');
							}

							$result = $dbmain->query($query, array(
								'PersonEvn_id' => $row['PersonEvn_id'],
								'Server_id' => $row['Server_id'],
								'Person_id' => $Person_id,
								'OmsSprTerr_id' => $row['OmsSprTerr_id'],
								'PolisType_id' => $row['PolisType_id'],
								'PolisFormType_id' => $row['PolisFormType_id'],
								'OrgSmo_id' => $row['OrgSmo_id'],
								'Polis_Ser' => $row['Polis_Ser'],
								'Polis_Num' => $row['Polis_Num'],
								'Polis_begDate' => $Polis_nextDate,
								'Polis_endDate' => $row['Polis_endDate'],
								'pmUser_id' => $data['pmUser_id'],
							));

							if (!is_object($result)) {
								throw new Exception('Ошибка при выполнении запроса (сдвиг даты начала действия более поздних документов ОМС) (Person_id=' . $Person_id . ')');
							}

							$resp = $result->result('array');

							if (!is_array($resp) || count($resp) == 0) {
								throw new Exception('Ошибка при сдвиге даты начала действия более поздних документов ОМС (Person_id=' . $Person_id . ')');
							}
							else if (!empty($resp[0]['ErrMsg'])) {
								throw new Exception($resp[0]['ErrMsg']);
							}

							$hasPersonEvnChanges = true;
						}
					}
				}
			}

			if ( isset($recordToUpdate) && is_array($recordToUpdate) && count($recordToUpdate) > 0 ) {
				$proc = 'upd';
			}
			else {
				$proc = 'ins';
			}

			$updated = false;

			if (
				$proc == 'ins'
				// если обновление, то только если что-то изменяется.
				|| $recordToUpdate['Polis_endDate'] != $Polis_endDate
				|| $recordToUpdate['OmsSprTerr_id'] != $OmsSprTerr_id
				//|| $recordToUpdate['PolisFormType_id'] != $PolisFormType_id
				|| $recordToUpdate['Server_id'] != 0
			) {
				$query = "
					select PersonPolis_id as \"PersonPolis_id\", error_message as \"ErrMsg\"
					from p_PersonPolis_{$proc}(
						PersonPolis_id := :PersonEvn_id,
						Server_id := :Server_id,
						Person_id := :Person_id,
						OmsSprTerr_id := :OmsSprTerr_id,
						PolisType_id := :PolisType_id,
						PolisFormType_id := :PolisFormType_id,
						OrgSmo_id := :OrgSmo_id,
						Polis_Ser := :Polis_Ser,
						Polis_Num := :Polis_Num,
						Polis_begDate := :Polis_begDate,
						Polis_endDate := :Polis_endDate,
						PersonPolis_insDT := :Polis_begDate,
						pmUser_id := :pmUser_id);
				";

				if (!(empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate)) {
					throw new Exception('Ошибка при попытке выполнении запроса (внесение информации об актуальном документе ОМС) (Person_id=' . $Person_id . ') - дата закрытия полиса не может быть меньше даты открытия');
				}

				$result = $dbmain->query($query, array(
					'PersonEvn_id' => ($proc == "upd" ? $recordToUpdate['PersonEvn_id'] : null),
					'Server_id' => 0,
					'Person_id' => $Person_id,
					'OmsSprTerr_id' => $OmsSprTerr_id,
					'PolisType_id' => $PolisType_id,
					'PolisFormType_id' => $PolisFormType_id,
					'OrgSmo_id' => $OrgSmo_id,
					'Polis_Ser' => $Polis_Ser,
					'Polis_Num' => $Polis_Num,
					'Polis_begDate' => $Polis_begDate,
					'Polis_endDate' => $Polis_endDate,
					'pmUser_id' => $data['pmUser_id'],
				));

				if (!is_object($result)) {
					throw new Exception('Ошибка при выполнении запроса (внесение информации об актуальном документе ОМС) (Person_id=' . $Person_id . ')');
				}

				$resp = $result->result('array');

				if (!is_array($resp) || count($resp) == 0) {
					throw new Exception('Ошибка при внесении информации об актуальном документе ОМС (Person_id=' . $Person_id . ')');
				}
				else if (!empty($resp[0]['ErrMsg'])) {
					throw new Exception($resp[0]['ErrMsg']);
				}

				$updated = true;
				$hasPersonEvnChanges = true;
			}

			// Проверяем необходимость добавления/обновления ЕНП
			if ( !empty($Federal_Num) ) {
				$enpAddFlag = false;

				$query = "
					select 
						Server_id as \"Server_id\",
						PersonPolisEdNum_id as \"PersonPolisEdNum_id\",
						PersonPolisEdNum_EdNum as \"PersonPolisEdNum_EdNum\",
						to_char(PersonPolisEdNum_insDT, 'YYYY-MM-DD') as \"PersonPolisEdNum_insDate\"
					from v_PersonPolisEdNum 
					where Person_id = :Person_id
						and PersonPolisEdNum_insDate <= :Polis_begDate
					order by PersonPolisEdNum_insDT desc
                    limit 2
				";
				$result = $dbmain->query($query, array(
					'Person_id' => $Person_id,
					'Polis_begDate' => $Polis_begDate,
				));

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при выполнении запроса (получение информации о ЕНП) (Person_id=' . $Person_id . ')');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					$enpAddFlag = true;
				}
				else if ( $resp[0]['PersonPolisEdNum_EdNum'] != $Federal_Num ) {
					if ( empty($resp[1]['PersonPolisEdNum_EdNum']) || $resp[1]['PersonPolisEdNum_EdNum'] != $Federal_Num ) {
						$enpAddFlag = true;
					}

					// Удаляем существующую периодику, если дата начала совпадает с датой начала действия полиса
					if ( $resp[0]['PersonPolisEdNum_insDate'] == $Polis_begDate ) {
						$query = "
							select error_message as \"ErrMsg\"
							from dbo.xp_PersonRemovePersonEvn(
								Server_id := :Server_id,
								PersonEvn_id := :PersonEvn_id,
								Person_id := :Person_id
							);
						";
						$result = $dbmain->query($query, array(
							'Server_id' => $resp[0]['Server_id'],
							'PersonEvn_id' => $resp[0]['PersonPolisEdNum_id'],
							'Person_id' => $Person_id,
						));

						if ( !is_object($result) ) {
							throw new Exception('Ошибка при выполнении запроса (удаление ЕНП) (Person_id=' . $Person_id . ')');
						}

						$resp = $result->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							throw new Exception('Ошибка при удалении ЕНП (Person_id=' . $Person_id . ')');
						}
						else if ( !empty($resp[0]['ErrMsg']) ) {
							throw new Exception($resp[0]['ErrMsg']);
						}

						$hasPersonEvnChanges = true;
					}
				}

				if ( $enpAddFlag == true ) {
					$query = "
						select 
							Server_id as \"Server_id\",
							PersonPolisEdNum_id as \"PersonPolisEdNum_id\",
							PersonPolisEdNum_EdNum as \"PersonPolisEdNum_EdNum\",
							to_char(PersonPolisEdNum_insDT, 'YYYY-MM-DD') as \"PersonPolisEdNum_insDate\"
						from v_PersonPolisEdNum 
						where Person_id = :Person_id
							and PersonPolisEdNum_insDate > :Polis_begDate
						order by PersonPolisEdNum_insDT
                        limit 1
					";
					$result = $dbmain->query($query, array(
						'Person_id' => $Person_id,
						'Polis_begDate' => $Polis_begDate,
					));

					if ( !is_object($result) ) {
						throw new Exception('Ошибка при выполнении запроса (получение информации о ЕНП) (Person_id=' . $Person_id . ')');
					}

					$resp = $result->result('array');

					if ( is_array($resp) && count($resp) > 0 && $resp[0]['PersonPolisEdNum_EdNum'] == $Federal_Num ) {
						$query = "
							select error_message as \"ErrMsg\"
							from dbo.xp_PersonRemovePersonEvn(
								Server_id := :Server_id,
								PersonEvn_id := :PersonEvn_id,
								Person_id := :Person_id
							);
						";
						$result = $dbmain->query($query, array(
							'Server_id' => $resp[0]['Server_id'],
							'PersonEvn_id' => $resp[0]['PersonPolisEdNum_id'],
							'Person_id' => $Person_id,
						));

						if ( !is_object($result) ) {
							throw new Exception('Ошибка при выполнении запроса (удаление ЕНП) (Person_id=' . $Person_id . ')');
						}

						$resp = $result->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							throw new Exception('Ошибка при удалении ЕНП (Person_id=' . $Person_id . ')');
						}
						else if ( !empty($resp[0]['ErrMsg']) ) {
							throw new Exception($resp[0]['ErrMsg']);
						}

						$hasPersonEvnChanges = true;
					}
				}

				if ( $enpAddFlag == true ) {
					$query = "
						select error_message as \"ErrMsg\"
						from p_PersonPolisEdNum_ins(
							Server_id := 0,
							Person_id := :Person_id,
							PersonPolisEdNum_insDT := :Polis_begDate,
							PersonPolisEdNum_EdNum := :Polis_Num,
							pmUser_id := :pmUser_id);
					";
					$result = $dbmain->query($query, array(
						'Person_id' => $Person_id,
						'Polis_begDate' => $Polis_begDate,
						'Polis_Num' => $Federal_Num,
						'pmUser_id' => $data['pmUser_id'],
					));

					if ( !is_object($result) ) {
						throw new Exception('Ошибка при выполнении запроса (добавление ЕНП) (Person_id=' . $Person_id . ')');
					}

					$resp = $result->result('array');

					if ( !is_array($resp) || count($resp) == 0 ) {
						throw new Exception('Ошибка при добавлении ЕНП (Person_id=' . $Person_id . ')');
					}
					else if ( !empty($resp[0]['ErrMsg']) ) {
						throw new Exception($resp[0]['ErrMsg']);
					}

					$hasPersonEvnChanges = true;
				}
			}

			if ( $proc == 'upd' && $updated ) {
				$sql = "
					select error_message as \"ErrMsg\"
					from xp_PersonTransferDate(
						Server_id := 0,
						PersonEvn_id := :PersonEvn_id,
						PersonEvn_begDT := :Polis_begDate,
						pmUser_id := :pmUser_id);
				";
				$result = $dbmain->query($sql, array(
					'PersonEvn_id' => $recordToUpdate['PersonEvn_id'],
					'Polis_begDate' => $Polis_begDate,
					'pmUser_id' => $data['pmUser_id'],
				));

				$resp = $result->result('array');
			}

			if ( $hasPersonEvnChanges ) {
				$dbmain->query('select dbo.xp_PersonTransferEvn(Person_id := :Person_id)', array('Person_id' => $Person_id));
			}

			/*if ( empty($Polis_endDate) ) {
				// Проставляем человеку признак "из БДЗ"
				$sql = "
					declare
						@guid uniqueidentifier,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @guid = (select top 1 BDZ_Guid from v_Person  where Person_id = :Person_id);


					exec p_Person_server
						@Person_id = :Person_id,
						@Server_id = 0,
						@BDZ_Guid = @guid,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Error_Code as Error_Code, @Error_Message as Error_Msg;


				";
				$result = $dbmain->query($sql, array(
					'Person_id' => $Person_id,
					'pmUser_id' => $data['pmUser_id'],
				));

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при выполнении запроса (изменение признака "из БДЗ") (Person_id=' . $Person_id . ')');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					throw new Exception('Ошибка при изменении признака "из БДЗ" (Person_id=' . $Person_id . ')');
				}
				else if ( !empty($resp[0]['ErrMsg']) ) {
					throw new Exception($resp[0]['ErrMsg']);
				}
			}*/

			$response = '';
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Обновление фамилии
	 */
	public function updatePersonSurname($data) {
		$dbmain = $this->load->database('default', true);

		try {
			// 1) Нужно вытащить предыдущую фамилию и правильно закрыть периодику при необходимости
			$resultLast = $dbmain->query("
				select 
					Server_id as \"Server_id\",
					PersonSurName_id as \"PersonSurName_id\",
					PersonSurName_SurName as \"PersonSurName_SurName\",
					to_char(PersonSurName_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"PersonSurName_insDT\"
				from v_PersonSurName  
				where
					Person_id = :Person_id
					and cast(PersonSurName_insDT as date) <= cast(:Evn_setDT as date)
				order by PersonSurName_insDT desc
                limit 1
			", $data, true);

			if (!is_object($resultLast)) {
				throw new Exception('Ошибка при выполнении запроса (получение фамилии) (Person_id=' . $data['Person_id'] . ')');
			}

			$respLast = $resultLast->result('array');

			if (
				count($respLast) == 0
				|| trim($respLast[0]['PersonSurName_SurName']) != $data['FAM']
			) {
				if (!empty($respLast[0]['PersonSurName_insDT']) && trim($respLast[0]['PersonSurName_SurName']) != $data['FAM'] && $respLast[0]['PersonSurName_insDT'] >= $data['Evn_setDT']) {
					$data['PersonSurName_insDT'] = date('Y-m-d H:i:s', strtotime($respLast[0]['PersonSurName_insDT'] . "+1 second"));
				} else {
					$data['PersonSurName_insDT'] = $data['Evn_setDT'];
				}

				$resultTmp = $dbmain->query("
					select error_message as \"Error_Msg\"
					from p_PersonSurName_ins(
						Server_id := :Server_id,
						Person_id := :Person_id,
						PersonSurName_SurName := :FAM,
						PersonSurName_insDT := :PersonSurName_insDT,
						pmUser_id := :pmUser_id);
				", $data);

				if (!is_object($resultTmp)) {
					throw new Exception('Ошибка при выполнении запроса (добавление периодики по фамилии) (Person_id=' . $data['Person_id'] . ')');
				}
				$respTmp = $resultTmp->result('array');
				if (!empty($respTmp[0]['Error_Msg'])) {
					throw new Exception($respTmp[0]['Error_Msg']);
				}
			}

			$response = '';
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Обновление имени
	 */
	public function updatePersonFirname($data) {
		$dbmain = $this->load->database('default', true);

		try {
			// 1) Нужно вытащить предыдущее имя и правильно закрыть периодику при необходимости
			$resultLast = $dbmain->query("
				select 
					Server_id as \"Server_id\",
					PersonFirName_id as \"PersonFirName_id\",
					PersonFirName_FirName as \"PersonFirName_FirName\",
					to_char(PersonFirName_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"PersonFirName_insDT\"
				from v_PersonFirName  
				where
					Person_id = :Person_id
					and cast(PersonFirName_insDT as date) <= cast(:Evn_setDT as date)
				order by PersonFirName_insDT desc
				limit 1
			", $data, true);

			if (!is_object($resultLast)) {
				throw new Exception('Ошибка при выполнении запроса (получение имени) (Person_id=' . $data['Person_id'] . ')');
			}

			$respLast = $resultLast->result('array');

			if (
				count($respLast) == 0
				|| trim($respLast[0]['PersonFirName_FirName']) != $data['IM']
			) {
				if (!empty($respLast[0]['PersonFirName_insDT']) && trim($respLast[0]['PersonFirName_FirName']) != $data['IM'] && $respLast[0]['PersonFirName_insDT'] >= $data['Evn_setDT']) {
					$data['PersonFirName_insDT'] = date('Y-m-d H:i:s', strtotime($respLast[0]['PersonFirName_insDT'] . "+1 second"));
				} else {
					$data['PersonFirName_insDT'] = $data['Evn_setDT'];
				}

				$resultTmp = $dbmain->query("
					select error_message as \"Error_Msg\"
					from p_PersonFirName_ins(
						Server_id := :Server_id,
						Person_id := :Person_id,
						PersonFirName_FirName := :IM,
						PersonFirName_insDT := :PersonFirName_insDT,
						pmUser_id := :pmUser_id);
				", $data);

				if (!is_object($resultTmp)) {
					throw new Exception('Ошибка при выполнении запроса (добавление периодики по имени) (Person_id=' . $data['Person_id'] . ')');
				}
				$respTmp = $resultTmp->result('array');
				if (!empty($respTmp[0]['Error_Msg'])) {
					throw new Exception($respTmp[0]['Error_Msg']);
				}
			}

			$response = '';
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Обновление отчества
	 */
	public function updatePersonSecname($data) {
		$dbmain = $this->load->database('default', true);

		try {
			// 1) Нужно вытащить предыдущее отчество и правильно закрыть периодику при необходимости
			$resultLast = $dbmain->query("
				select
					Server_id as \"Server_id\",
					PersonSecName_id as \"PersonSecName_id\",
					PersonSecName_SecName as \"PersonSecName_SecName\",
					to_char(PersonSecName_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"PersonSecName_insDT\"
				from v_PersonSecName  
				where
					Person_id = :Person_id
					and cast(PersonSecName_insDT as date) <= cast(:Evn_setDT as date)
				order by PersonSecName_insDT desc
				limit 1
			", $data, true);

			if (!is_object($resultLast)) {
				throw new Exception('Ошибка при выполнении запроса (получение отчества) (Person_id=' . $data['Person_id'] . ')');
			}

			$respLast = $resultLast->result('array');

			if (
				count($respLast) == 0
				|| trim($respLast[0]['PersonSecName_SecName']) != $data['OT']
			) {
				if (!empty($respLast[0]['PersonSecName_insDT']) && trim($respLast[0]['PersonSecName_SecName']) != $data['OT'] && $respLast[0]['PersonSecName_insDT'] >= $data['Evn_setDT']) {
					$data['PersonSecName_insDT'] = date('Y-m-d H:i:s', strtotime($respLast[0]['PersonSecName_insDT'] . "+1 second"));
				} else {
					$data['PersonSecName_insDT'] = $data['Evn_setDT'];
				}

				$resultTmp = $dbmain->query("
					select error_message as \"Error_Msg\"
					from p_PersonSecName_ins(
						Server_id := :Server_id,
						Person_id := :Person_id,
						PersonSecName_SecName := :OT,
						PersonSecName_insDT := :PersonSecName_insDT,
						pmUser_id := :pmUser_id);
				", $data);

				if (!is_object($resultTmp)) {
					throw new Exception('Ошибка при выполнении запроса (добавление периодики по отчеству) (Person_id=' . $data['Person_id'] . ')');
				}
				$respTmp = $resultTmp->result('array');
				if (!empty($respTmp[0]['Error_Msg'])) {
					throw new Exception($respTmp[0]['Error_Msg']);
				}
			}

			$response = '';
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Обновление даты рождения
	 */
	public function updatePersonBirthday($data) {
		$dbmain = $this->load->database('default', true);

		try {
			// 1) Нужно вытащить предыдущую дату рождения и правильно закрыть периодику при необходимости
			$resultLast = $dbmain->query("
				select 
					Server_id as \"Server_id\",
					PersonBirthday_id as \"PersonBirthday_id\",
					to_char(PersonBirthday_Birthday, 'YYYY-MM-DD') as \"PersonBirthday_Birthday\",
					to_char(PersonBirthday_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"PersonBirthday_insDT\"
				from v_PersonBirthday  
				where
					Person_id = :Person_id
					and cast(PersonBirthday_insDT as date) <= cast(:Evn_setDT as date)
				order by PersonBirthday_insDT desc
				limit 1
			", $data, true);

			if (!is_object($resultLast)) {
				throw new Exception('Ошибка при выполнении запроса (получение даты рождения) (Person_id=' . $data['Person_id'] . ')');
			}

			$respLast = $resultLast->result('array');

			if (
				count($respLast) == 0
				|| $respLast[0]['PersonBirthday_Birthday'] != $data['DR']
			) {
				if (!empty($respLast[0]['PersonBirthday_insDT']) && $respLast[0]['PersonBirthday_Birthday'] != $data['DR'] && $respLast[0]['PersonBirthday_insDT'] >= $data['Evn_setDT']) {
					$data['PersonBirthday_insDT'] = date('Y-m-d H:i:s', strtotime($respLast[0]['PersonBirthday_insDT'] . "+1 second"));
				} else {
					$data['PersonBirthday_insDT'] = $data['Evn_setDT'];
				}

				$resultTmp = $dbmain->query("
					select error_message as \"Error_Msg\"
					from p_PersonBirthday_ins(
						Server_id := :Server_id,
						Person_id := :Person_id,
						PersonBirthday_Birthday := :DR,
						PersonBirthday_insDT := :PersonBirthday_insDT,
						pmUser_id := :pmUser_id);
				", $data);

				if (!is_object($resultTmp)) {
					throw new Exception('Ошибка при выполнении запроса (добавление периодики по дате рождения) (Person_id=' . $data['Person_id'] . ')');
				}
				$respTmp = $resultTmp->result('array');
				if (!empty($respTmp[0]['Error_Msg'])) {
					throw new Exception($respTmp[0]['Error_Msg']);
				}
			}

			$response = '';
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Сохранение Registry_EvnNum
	 */
	protected function _saveRegistryEvnNum($data) {
		$toWrite = array();

		foreach ( $data['Registry_EvnNum'] as $key => $record ) {
			$toWrite[$key] = $record;

			if ( count($toWrite) >= 1000 ) {
				$str = json_encode($toWrite) . PHP_EOL;
				@file_put_contents($data['FileName'], $str, FILE_APPEND);
				$toWrite = array();
			}
		}

		if ( count($toWrite) > 0 ) {
			$str = json_encode($toWrite) . PHP_EOL;
			file_put_contents($data['FileName'], $str, FILE_APPEND);
		}

		return true;
	}

	/**
	 * Получение Registry_EvnNum
	 */
	public function setRegistryEvnNum($data) {
		if ( !empty($data['Registry_EvnNum']) ) {
			$this->registryEvnNum = json_decode($data['Registry_EvnNum'], true);
		}
		else if ( !empty($data['Registry_xmlExportPath']) ) {
			$filename = basename($data['Registry_xmlExportPath']);
			$evnNumPath = str_replace('/' . $filename, '/evnnum.txt', $data['Registry_xmlExportPath']);

			if ( file_exists($evnNumPath) ) {
				$fileContents = file_get_contents($evnNumPath);
				$exploded = explode(PHP_EOL, $fileContents);
				$this->registryEvnNum = [];

				foreach ( $exploded as $one ) {
					if ( !empty($one) ) {
						$unjsoned = json_decode($one, true);

						if ( is_array($unjsoned) ) {
							foreach ( $unjsoned as $key => $value ) {
								$this->registryEvnNum[$key] = $value;
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Получение массива Evn_id, сгруппированного по N_ZAP
	 */
	public function setRegistryEvnNumByNZAP() {
		if ( is_array($this->registryEvnNum) && count($this->registryEvnNum) > 0 ) {
			$this->registryEvnNumByNZAP = [];

			foreach ( $this->registryEvnNum as $key => $record ) {
				if ( is_array($record) && !empty($record[0]['z']) ) {
					if ( !isset($this->registryEvnNumByNZAP[$record[0]['z']]) ) {
						$this->registryEvnNumByNZAP[$record[0]['z']] = [];
					}

					$this->registryEvnNumByNZAP[$record[0]['z']][] = $key;
				}
			}
		}

		return true;
	}

	/**
	 * Проверка вхождения случая в другой реестр при снятии с реестра отметки "Оплачен"
	 * @task https://redmine.swan-it.ru/issues/154914
	 */
	public function checkRegistryDataIsInOtherRegistry($data) {
		$response = '';

		try {
			switch ( $this->RegistryType_id ) {
				case 1:
				case 14:
					$EvnClass_SysNick = 'EvnSection';
					break;

				case 2:
				case 16:
					$EvnClass_SysNick = 'EvnVizit';
					break;

				case 6:
					$EvnClass_SysNick = 'CmpCallCard';
					break;

				case 7:
				case 9:
				case 11:
				case 12:
					$EvnClass_SysNick = 'EvnPLDisp';
					break;

				case 15:
				case 20:
					$EvnClass_SysNick = 'EvnUsluga';
					break;

				default:
					throw new Exception('Ошибка при определении класса событий реестра.');
					break;
			}

			$filter = "";
			$join = "";

			if ( $this->RegistryType_id != 6 ) {
				$join = "inner join Evn on Evn.Evn_id = e.{$EvnClass_SysNick}_id";
				$filter = "and COALESCE(Evn.Evn_deleted, 1) = 1";
			}


			if (
				!empty($data['RegistrySubType_id'])
				&& $data['RegistrySubType_id'] == 2
			) {

				$join .= " left join v_OmsSprTerr ost  on ost.OmsSprTerr_id = rd.OmsSprTerr_id";
				// для финального берём по другому
				if ( $data['PayType_SysNick'] == 'oms' ) {
					$filter .= " and ost.OmsSprTerr_id is not null";
					if ( $data['KatNasel_SysNick'] == 'oblast' ) {
						$filter .= " and ost.KLRgn_id = 35";
					}
					else {
						$filter .= " and COALESCE(ost.KLRgn_id, 0) <> 35";
					}
				}

			}

			$checkResult = $this->getFirstRowFromQuery("
				select ro.Registry_Num as \"Registry_Num\", uro.Registry_Num as \"UnionRegistry_Num\"  
				from {$this->scheme}.v_{$this->RegistryDataObject} rd 
					inner join {$this->scheme}.v_Registry r  on r.Registry_id = rd.Registry_id
					inner join {$this->scheme}.v_RegistryGroupLink rgl  on rgl.Registry_id = rd.Registry_id
					inner join {$EvnClass_SysNick} e  on e.{$EvnClass_SysNick}_id = rd.Evn_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rdo  on rdo.Evn_id = e.{$EvnClass_SysNick}_id
						and rdo.Registry_id != rd.Registry_id
					inner join {$this->scheme}.v_Registry ro  on ro.Registry_id = rdo.Registry_id
					left join {$this->scheme}.v_RegistryGroupLink rglo  on rglo.Registry_id = rdo.Registry_id
					left join {$this->scheme}.v_Registry uro on uro.Registry_id = rglo.Registry_pid
						and uro.RegistryStatus_id != 4
					{$join}
				where rgl.Registry_pid = :Registry_id
					and COALESCE(e.{$EvnClass_SysNick}_IsInReg, 1) = 2
					and COALESCE(rd.Paid_id, 1) = 1
					and (
						ro.Registry_accDate >= r.Registry_accDate
						or uro.Registry_id is not null
					)
					{$filter}
				limit 1
			", $data, true);

			if ( $checkResult === false ) {
				throw new Exception('Ошибка при определении вхождения случаев в реестр.');
			}
			else if ( is_array($checkResult) && count($checkResult) > 0 ) {
				throw new Exception('Снятие отметки «Оплачен» невозможно, так как реестр содержит случаи, включенные в другие неоплаченные реестры: ' . (!empty($checkResult['UnionRegistry_Num']) ? 'реестр по СМО № ' . $checkResult['UnionRegistry_Num'] : 'предварительный реестр № ' . $checkResult['Registry_Num']) . '. Для снятия отметки нужно исключить такие случаи из других неоплаченных реестров.');
			}
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	private function _loadUnionRegistryGridNew($data) {
		$query = "
			select 
				-- select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				R.Registry_xmlExportPath as \"Registry_xmlExportPath\",
				R.Registry_Sum as \"Registry_Sum\",  
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
				R.KatNasel_id as \"KatNasel_id\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				R.PayType_id as \"PayType_id\",
				PT.PayType_Name as \"PayType_Name\",
				PT.PayType_SysNick as \"PayType_SysNick\",
				RGT.RegistryGroupType_id as \"RegistryGroupType_id\",
				RGT.RegistryGroupType_Name as \"RegistryGroupType_Name\",
				case when R.Registry_IsZNO = 2 then 'true' else 'false' end as \"Registry_IsZNO\",
				case when R.Registry_IsRepeated = 2 then 'true' else 'false' end as \"Registry_IsRepeated\",
				case when R.Registry_isPersFin = 2 then 'true' else 'false' end as \"Registry_isPersFin\",
				OS.OrgSMO_Name as \"OrgSMO_Name\"
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry R  -- объединённый реестр
				left join v_RegistryGroupType RGT  on RGT.RegistryGroupType_id = R.RegistryGroupType_id
				left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				left join v_PayType PT  on PT.PayType_id = R.PayType_id
				left join v_OrgSmo OS  on OS.OrgSmo_id = R.OrgSmo_id
				-- end from
			where
				-- where
				R.Lpu_id = :Lpu_id
				and R.RegistryType_id = 13
				-- end where
			order by
				-- order by
				R.Registry_endDate DESC,
				R.Registry_updDT DESC
				-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Сохранение объединённого реестра (нового)
	 */
	private function _saveUnionRegistryNew($data) {
		try {
			$this->beginTransaction();

			// Проверка уникальности номера реестра по МО в одном году
			$checkResult = $this->getFirstResultFromQuery("
				select 
					Registry_id as \"Registry_id\"
				from
					{$this->scheme}.v_Registry 
				where
					RegistryType_id = 13
					and Lpu_id = :Lpu_id
					and Registry_Num = :Registry_Num
					and date_part('year', Registry_accDate) = date_part('year', CAST(:Registry_accDate as date))
					and (Registry_id <> :Registry_id OR :Registry_id IS NULL)
				limit 1
			", $data);

			if ( $checkResult !== false && !empty($checkResult) ) {
				throw new Exception('Номер счета не должен повторяться в течение года');
			}

			// 1. сохраняем объединённый реестр
			$proc = 'p_Registry_ins';

			if ( !empty($data['Registry_id']) ) {
				$proc = 'p_Registry_upd';
			}

			$resp = $this->getFirstRowFromQuery("
				select Registry_id as \"Registry_id\", (select KatNasel_Code from v_KatNasel  where KatNasel_id := :KatNasel_id limit 1) as \"KatNasel_Code\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"	
				from {$this->scheme}.{$proc}(
					Registry_id := :Registry_id,
					RegistryType_id := 13,
					RegistryStatus_id := 1,
					Registry_Sum := NULL,
					Registry_IsActive := 2,
					Registry_Num := :Registry_Num,
					Registry_accDate := :Registry_accDate,
					Registry_begDate := :Registry_begDate,
					Registry_endDate := :Registry_endDate,
					KatNasel_id := :KatNasel_id,
					PayType_id := :PayType_id,
					Registry_IsNew := 2,
					RegistryGroupType_id := :RegistryGroupType_id,
					Registry_IsZNO := :Registry_IsZNO,
					OrgSmo_id := :OrgSmo_id,
					Lpu_id := :Lpu_id,
					Org_did := :Org_did,
					Lpu_cid := :Lpu_cid,
					Registry_Comments := null,
					Registry_IsRepeated := :Registry_IsRepeated,
					Registry_isPersFin := :Registry_isPersFin,
					pmUser_id := :pmUser_id);
			", $data);

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				throw new Exception('Ошибка при сохранении объединенного реестра');
			}

			// 2. удаляем все связи
			$query = "
				delete {$this->scheme}.RegistryGroupLink
				where Registry_pid = :Registry_id
			";
			$this->db->query($query, [
				'Registry_id' => $resp['Registry_id']
			]);

			$registrytypefilter = "";

			switch ( $data['RegistryGroupType_id'] ) {
				case 1:
					$registrytypefilter = " and R.RegistryType_id in (1, 2, 15) and COALESCE(R.Registry_IsZNO, 1) = COALESCE( CAST(:Registry_IsZNO as bigint), 1) and COALESCE(R.Registry_isPersFin, 1) = COALESCE( CAST(:Registry_isPersFin as bigint), 1)";

					break;

				case 2:
					$registrytypefilter = " and R.RegistryType_id = 14";
					break;

				case 3:
					$registrytypefilter = " and R.RegistryType_id = 7 and DispClass_id = 1";
					break;

				case 4:
					$registrytypefilter = " and R.RegistryType_id = 7 and DispClass_id = 2";
					break;

				case 10:
					$registrytypefilter = " and R.RegistryType_id = 11";
					break;

				case 21:
					$registrytypefilter = " and R.RegistryType_id = 6 and COALESCE(R.Registry_isPersFin, 1) = COALESCE( CAST(:Registry_isPersFin as bigint), 1)";

					break;

				case 27:
					$registrytypefilter = " and R.RegistryType_id = 9 and DispClass_id = 3";
					break;

				case 29:
					$registrytypefilter = " and R.RegistryType_id = 9 and DispClass_id = 7";
					break;

				case 33:
					$registrytypefilter = " and R.RegistryType_id = 12";
					break;

				case 34:
					$registrytypefilter = " and R.RegistryType_id = 20 and COALESCE(R.Registry_IsZNO, 1) = COALESCE( CAST(:Registry_IsZNO as bigint), 1)";

					break;
			}

			// 3. выполняем поиск реестров которые войдут в объединённый
			$resp_reg = $this->queryResult("
				select
					R.Registry_id as \"Registry_id\",
					COALESCE(R.Registry_Sum, 0) as \"Registry_Sum\"
				from
					{$this->scheme}.v_Registry R 
				where
					R.RegistryType_id <> 13
					and R.Registry_IsNew = 2
					and R.RegistryStatus_id = 2 -- к оплате
					and R.Lpu_id = :Lpu_id
					and R.Registry_begDate >= :Registry_begDate
					and R.Registry_endDate <= :Registry_endDate
					and COALESCE(R.Registry_IsRepeated, 1) = COALESCE(CAST(:Registry_IsRepeated as bigint), 1)
					and COALESCE(R.KatNasel_id, 0) = COALESCE(CAST(:KatNasel_id as bigint), 0)
					and R.PayType_id = :PayType_id
					and COALESCE(R.Org_did, 0) = COALESCE(CAST(:Org_did as bigint), 0)
					and COALESCE(R.OrgSmo_id, 0) = COALESCE(CAST(:OrgSmo_id as bigint), 0)
					and COALESCE(R.Lpu_cid, 0) = COALESCE(CAST(:Lpu_cid as bigint), 0)
					and not exists(select RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink  where Registry_id = R.Registry_id)
					{$registrytypefilter}
			", [
				'KatNasel_id' => $data['KatNasel_id'],
				'Lpu_cid' => $data['Lpu_cid'],
				'Lpu_id' => $data['Lpu_id'],
				'Org_did' => $data['Org_did'],
				'OrgSmo_id' => $data['OrgSmo_id'],
				'PayType_id' => $data['PayType_id'],
				'Registry_begDate' => $data['Registry_begDate'],
				'Registry_endDate' => $data['Registry_endDate'],
				'Registry_isPersFin' => $data['Registry_isPersFin'],
				'Registry_IsRepeated' => $data['Registry_IsRepeated'],
				'Registry_IsZNO' => $data['Registry_IsZNO'],
			]);

			if ( $resp_reg !== false && is_array($resp_reg) && count($resp_reg) > 0 ) {
				$UnionRegistry_Sum = 0;

				// 4. сохраняем новые связи
				foreach ( $resp_reg as $one_reg ) {
					$query = "
					select RegistryGroupLink_id as \"RegistryGroupLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_RegistryGroupLink_ins(
						RegistryGroupLink_id := null,
						Registry_pid := :Registry_pid,
						Registry_id := :Registry_id,
						pmUser_id := :pmUser_id);
				";

					$this->db->query($query, [
						'Registry_pid' => $resp['Registry_id'],
						'Registry_id' => $one_reg['Registry_id'],
						'pmUser_id' => $data['pmUser_id'],
					]);

					$UnionRegistry_Sum += $one_reg['Registry_Sum'];
				}

				$sumSaveResponse = $this->getFirstRowFromQuery("
						update {$this->scheme}.Registry
						set
							Registry_Sum = :Registry_Sum,
							pmUser_updID = :pmUser_id
						where
							Registry_id = :Registry_id;
					select '' as \"Error_Code\", '' as \"Error_Msg\"
				", [
					'Registry_id' => $resp['Registry_id'],
					'Registry_Sum' => $UnionRegistry_Sum,
					'pmUser_id' => $data['pmUser_id'],
				]);

				if ( $sumSaveResponse === false || !is_array($sumSaveResponse) ) {
					throw new Exception('Ошибка при обновлении суммы объединенного реестра');
				}
				else if ( !empty($sumSaveResponse['Error_Msg']) ) {
					throw new Exception($sumSaveResponse['Error_Msg']);
				}
			}

			// пишем информацию о формировании реестра в историю
			$dumpResponse = $this->dumpRegistryInformation([
				'Registry_id' => $resp['Registry_id']
			], 1);

			if ( $dumpResponse === false ) {
				throw new Exception('Ошибка при добавлении информации в историю работы с реестрами');
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$resp = [
				'Error_Msg' => $e->getMessage()
			];
		}

		return $resp;
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	public function loadUnionRegistryEditForm($data) {
		return $this->queryResult("
			select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
				R.Lpu_id as \"Lpu_id\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.Registry_IsRepeated as \"Registry_IsRepeated\",
				R.Registry_IsZNO as \"Registry_IsZNO\",
				R.Registry_isPersFin as \"Registry_isPersFin\",
				R.KatNasel_id as \"KatNasel_id\",
				R.PayType_id as \"PayType_id\",
				R.OrgSmo_id as \"OrgSmo_id\",
				R.Org_did as \"Org_did\",
				R.Lpu_cid as \"Lpu_cid\"
			from
				{$this->scheme}.v_Registry R 
			where
				R.Registry_id = :Registry_id
		", $data);
	}

	/**
	 * Постановка реестра в очередь на формирование
	 * Возвращает номер в очереди
	 */
	private function _saveRegistryQueueNew($data) {
		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes($data)) ) {
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}

		// Сохранение нового реестра
		if ( empty($data['Registry_id']) ) {
			$data['Registry_IsActive'] = 2;
			$operation = 'insert';
		}
		else {
			$operation = 'update';

			$registryData = $this->getFirstRowFromQuery("
				select 
					rg.Registry_pid as \"Registry_pid\",
					r.Registry_IsZNO as \"Registry_IsZNO\"
				from
					{$this->scheme}.v_Registry r 
					LEFT JOIN LATERAL (
						select 
							rgl.Registry_pid
						from
							{$this->scheme}.v_RegistryGroupLink rgl 
						where
							rgl.Registry_id = r.Registry_id
						limit 1
					) rg ON true
				where
					r.Registry_id = :Registry_id
				limit 1
			", $data);

			if ( $registryData == false ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка получения данных по реестру'));
			}

			if (!empty($registryData['Registry_pid'])) {
				// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один реестр по СМО.
				return array('Error_Msg' => 'Предварительный реестр входит в реестр по СМО, переформирование невозможно');
			}

			$data['Registry_IsZNO'] = $registryData['Registry_IsZNO'];
		}

		if ( $operation == 'update' ) {
			$rq = $this->loadRegistryQueue($data);

			if ( is_array($rq) && count($rq) > 0 && $rq[0]['RegistryQueue_Position'] > 0 ) {
				return array(array('success' => false, 'Error_Msg' => '<b>Запрос МО по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
			}
		}

		// проверка, чтобы не могли сохранить реестр без СМО, иначе он не попадает в объединенный
		if (
			$data['PayType_id'] == 190 // ОМС
			&& $data['KatNasel_id'] == 38 // Жители области
			&& empty($data['OrgSmo_id'])
		) {
			return array(array('success' => false, 'Error_Msg' => 'Не указана СМО, сохранение не возможно'));
		}

		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'Registry_IsNotInsur' => $data['Registry_IsNotInsur'],
			'Registry_isPersFin' => $data['Registry_isPersFin'],
			'OrgSmo_id' => $data['OrgSmo_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'pmUser_id' => $data['pmUser_id'],
			'RegistrySubType_id' => $data['RegistrySubType_id'],
			'DispClass_id' => $data['DispClass_id'],
			'Registry_IsZNO' => $data['Registry_IsZNO'],
			'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
			'Registry_IsRepeated' => $data['Registry_IsRepeated'],
			'PayType_id' => $data['PayType_id'],
			'KatNasel_id' => $data['KatNasel_id'],
			'Registry_IsNew' => $data['Registry_IsNew'],
			'Lpu_cid' => $data['Lpu_cid'],
			'Org_did' => $data['Org_did'],
		];

		$rq = $this->getFirstResultFromQuery("
			select
				RegistryQueue_id as \"RegistryQueue_id\" 
			from
				{$this->scheme}.v_RegistryQueue 
			where
				COALESCE(Registry_id, 0) = COALESCE(CAST(:Registry_id as bigint), 0)
				and COALESCE(RegistryType_id, 0) = COALESCE(CAST(:RegistryType_id as bigint), 0)
				and COALESCE(RegistrySubType_id, 0) = COALESCE(CAST(:RegistrySubType_id as bigint), 0)
				and COALESCE(DispClass_id, 0) = COALESCE(CAST(:DispClass_id as bigint), 0)
				and COALESCE(KatNasel_id, 0) = COALESCE(CAST(:KatNasel_id as bigint), 0)
				and COALESCE(Lpu_id, 0) = COALESCE(CAST(:Lpu_id as bigint), 0)
				and COALESCE(Registry_IsNotInsur, 0) = COALESCE(CAST(:Registry_IsNotInsur as bigint), 0)	
				and COALESCE(Registry_isPersFin, 0) = COALESCE(CAST(:Registry_isPersFin as bigint), 0)	
				and COALESCE(Registry_begDate, 0) = COALESCE(CAST(:Registry_begDate as bigint), 0)
				and COALESCE(Registry_endDate, 0) = COALESCE(CAST(:Registry_endDate as bigint), 0)
				and COALESCE(OrgSmo_id, 0) = COALESCE(CAST(:OrgSmo_id as bigint), 0)
				and COALESCE(Registry_Num,0) = COALESCE(CAST(:Registry_Num as bigint),0)
				and COALESCE(Registry_IsNew, 0) = COALESCE(CAST(:Registry_IsNew as bigint), 0)
				and COALESCE(Org_did, 0) = COALESCE(CAST(:Org_did as bigint), 0)
		", $params);

		if ( $rq !== false && !empty($rq) ) {
			return array(array('success' => false, 'Error_Msg' => '<b>Запрос МО по данному реестру уже находится в очереди на формирование.</b>'));
		}

		return $this->getFirstRowFromQuery("
			select RegistryQueue_id as \"RegistryQueue_id\", RegistryQueue_Position as \"RegistryQueue_Position\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryQueue_ins(
				RegistryQueue_id := null,
				RegistryQueue_Position := null,
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				Lpu_id := :Lpu_id,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				OrgSmo_id := :OrgSmo_id,
				Registry_Num := :Registry_Num,
				Registry_accDate := :Registry_accDate, 
				RegistryStatus_id := :RegistryStatus_id,
				Registry_IsNotInsur := :Registry_IsNotInsur,
				Registry_isPersFin := :Registry_isPersFin,
				RegistrySubType_id := :RegistrySubType_id,
				DispClass_id := :DispClass_id,
				Registry_IsZNO := :Registry_IsZNO,
				Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
				Registry_IsRepeated := :Registry_IsRepeated,
				PayType_id := :PayType_id,
				KatNasel_id := :KatNasel_id,
				Lpu_cid := :Lpu_cid,
				Org_did := :Org_did,
				Registry_Comments := null,
				Registry_IsNew := :Registry_IsNew,
				pmUser_id := :pmUser_id);
		", $params);
	}

	/**
	 * Установка статуса реестра (нового)
	 */
	private function _setRegistryStatusNew($data) {
		$response = [
			'success' => true,
			'Error_Msg' => '',
			'RegistryStatus_id' => 0,
		];

		try {
			$this->beginTransaction();

			if ( empty($data['Registry_ids']) || empty($data['RegistryStatus_id']) ) {
				throw new Exception('Пустые значения входных параметров');
			}

			$registryStatusList = $this->getAllowedRegistryStatuses();

			if ( !in_array($data['RegistryStatus_id'], $registryStatusList) ) {
				throw new Exception('Недопустимый статус реестра');
			}

			foreach ( $data['Registry_ids'] as $Registry_id ) {
				$data['Registry_id'] = $Registry_id;

				if ($this->checkRegistryInArchive($data)) {
					throw new Exception('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
				}

				if (!in_array($data['RegistryStatus_id'], [2, 4])) {
					if ($this->checkRegistryInGroupLink($data)) {
						throw new Exception('Реестр включён в объединенный реестр, все действия над реестром запрещены.');
					}
				}

				if ($data['RegistryStatus_id'] == 3 && $this->checkRegistryIsBlocked($data)) {
					throw new Exception('Реестр заблокирован, запрещено менять статус на "В работе".');
				}


				$r = $this->getFirstRowFromQuery("
					select 
						RegistryType_id as \"RegistryType_id\",
						RegistryStatus_id as \"RegistryStatus_id\"
					from {$this->scheme}.v_Registry 
					where Registry_id = :Registry_id
					limit 1
				", array('Registry_id' => $data['Registry_id']));

				if ($r === false) {
					throw new Exception('Ошибка при получении данных реестра');
				}

				$RegistryType_id = $r['RegistryType_id'];
				$RegistryStatus_id = $r['RegistryStatus_id'];

				$data['RegistryType_id'] = $RegistryType_id;

				$this->setRegistryParamsByType($data);

				$fields = "";

				// если перевели в работу, то снимаем признак формирования
				if ($data['RegistryStatus_id'] == 3) {
					$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, ";
				}

				if ($RegistryStatus_id == 3 && $data['RegistryStatus_id'] == 2 && (in_array($RegistryType_id, $this->getAllowedRegistryTypes($data))) && (isset($data['session']['setting']['server']['check_registry_exists_errors']) && $data['session']['setting']['server']['check_registry_exists_errors'] == 1) && (!isSuperadmin())) // если переводим "к оплате" и проверка установлена, и это не суперадмин то проверяем на ошибки
				{
					$tempscheme = 'dbo';

					$errCnt = $this->getFirstResultFromQuery("
						select (
							select count(*) 
							from {$this->scheme}.v_{$this->RegistryErrorObject} RegistryError 
								left join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Evn_id = RegistryError.Evn_id
								left join RegistryErrorType  on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
							where RegistryError.Registry_id = :Registry_id
								and RegistryErrorType.RegistryErrorClass_id = 1
								and RegistryError.RegistryErrorClass_id = 1
								and COALESCE(rd.RegistryData_deleted,1)=1
								and rd.Evn_id is not null
						) + (
							select count(*) 
							from {$tempscheme}.v_{$this->RegistryErrorComObject} RegistryErrorCom 
								left join RegistryErrorType  on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
							where Registry_id = :Registry_id
								and RegistryErrorType.RegistryErrorClass_id = 1
						) as \"errCnt\"
					", [ 'Registry_id' => $data['Registry_id'] ]);

					if ($errCnt !== false && !empty($errCnt)) {
						throw new Exception('Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.');
					}
				} else if ($RegistryStatus_id == 2 && $data['RegistryStatus_id'] == 4) {

					$result = $this->getFirstRowFromQuery("
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from {$this->scheme}.p_Registry_setPaid(
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					", $data);

					if ($result === false) {
						throw new Exception('Ошибка при отметке оплаты реестра');
					}
					else if (!empty($result['Error_Msg'])) {
						throw new Exception($result['Error_Msg']);
					}

					$res = $this->getFirstRowFromQuery("
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"		
						from {$this->scheme}.p_RegistryData_Refresh(
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					", $data);

					if ($res === false) {
						throw new Exception('Ошибка при пересчете реестра');
					}
					else if (!empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}
				}else if ($RegistryStatus_id == 4 && $data['RegistryStatus_id'] == 2) {
					$result = $this->getFirstRowFromQuery("
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from {$this->scheme}.p_Registry_setUnPaid(
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id)
					", $data);

					if ($result === false) {
						throw new Exception('Ошибка при отмене оплаты реестра');
					}
					else if (!empty($result['Error_Msg'])) {
						throw new Exception($result['Error_Msg']);
					}
				}

				// пересчитываем количество записей в реестре
				$resp_sum = $this->queryResult("
					select
						SUM(RD.RegistryData_ItogSum) as \"Sum\",
						SUM(1) as \"Count\"
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd 
					where
						RD.Registry_id = :Registry_id
				", array(
					'Registry_id' => $data['Registry_id']
				));

				$updateResponse = $this->getFirstRowFromQuery("
						update {$this->scheme}.Registry
						set
							RegistryStatus_id = :RegistryStatus_id,
							Registry_updDT = dbo.tzGetDate(),
							{$fields}
							pmUser_updID = :pmUser_id
						where
							Registry_id = :Registry_id;
					select :RegistryStatus_id as \"RegistryStatus_id\", '' as \"Error_Code\", '' as \"Error_Msg\";
				", [
					'Registry_id' => $data['Registry_id'],
					'RegistryStatus_id' => $data['RegistryStatus_id'],
					'pmUser_id' => $data['pmUser_id']
				]);

				if ( $updateResponse === false || !is_array($updateResponse) ) {
					throw new Exception('Ошибка при выполнении запроса к базе данных');
				}
				else if ( !empty($updateResponse['Error_Msg']) ) {
					throw new Exception($updateResponse['Error_Msg']);
				}

				if ( $data['RegistryStatus_id'] == 4 ) {
					// пишем информацию о смене статуса в историю
					$res = $this->dumpRegistryInformation([ 'Registry_id' => $data['Registry_id'] ], 4);

					if ($res === false) {
						throw new Exception('Ошибка при добавлении информации о смене статуса реестра');
					}
					else if (is_array($res) && !empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}
				}
			}

			$response['RegistryStatus_id'] = $data['RegistryStatus_id'];

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных (новые реестры)
	 * @param array $data
	 * @return array
	 */
	private function _exportRegistryToXmlNew($data = []) {
		//return [ 'Error_Msg' => 'Функция экспорта в стадии разработки.' ];

		$this->load->library('parser');

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->library('textlog', [ 'file'=>'exportRegistryToXmlNew_' . date('Y_m_d') . '.log' ]);
		$this->textlog->add('');
		$this->textlog->add('Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');

		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$res = $this->GetRegistryXmlExport($data);

		//Временная заглушка - чтобы каждый раз происходило формирование архивов
		//$res[0]['Registry_xmlExportPath'] = null;

		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return [ 'Error_Msg' => 'Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.' ];
		}

		$data['DispClass_id'] = $res[0]['DispClass_id'];
		$data['OrgSmo_id'] = $res[0]['OrgSmo_id'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_IsNotInsur'] = $res[0]['Registry_IsNotInsur'];
		$data['Registry_IsRepeated'] = $res[0]['Registry_IsRepeated'];
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$data['Registry_pack'] = $res[0]['Registry_pack'];
		$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'];
		$data['RegistrySubType_id'] = $res[0]['RegistrySubType_id'];
		$data['RegistryType_id'] = $res[0]['RegistryType_id'];
		$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
		$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];

		if ( empty($data['Registry_pack']) ) {
			$data['Registry_pack'] = $this->_setXmlPackNum($data);
		}

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return [ 'Error_Msg' => 'Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).' ];
		}
		else if ( !empty($res[0]['Registry_xmlExportPath']) && $data['OverrideExportOneMoreOrUseExist'] == 1 ) // если уже выгружен реестр
		{
			$link = $res[0]['Registry_xmlExportPath'];
			$this->textlog->add('Вернули ссылку ' . $link);
			return [ 'success' => true, 'Link' => $link ];
		}

		$reg_endmonth = $res[0]['Registry_endMonth'];
		$type = $res[0]['RegistryType_id'];
		$this->textlog->add('Тип реестра '.$res[0]['RegistryType_id']);

		$registryIsUnion = ($type == 13);

		if ( $registryIsUnion === false && !in_array($type, $this->getAllowedRegistryTypes($data)) ) {
			$this->textlog->add('Ошибка: Данный тип реестров не обрабатывается.');
			return [ 'Error_Msg' => 'Данный тип реестров не обрабатывается.' ];
		}

		// Формирование XML в зависимости от типа.
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->SetXmlExportStatus($data);

			$SCHET = $this->_loadRegistrySCHETForXmlUsingNew($data);

			switch ( true ) {
				case ($registryIsUnion == false && $type == 1):
				case ($registryIsUnion == false && $type == 2):
				case ($registryIsUnion == false && $type == 15):
				case ($registryIsUnion == false && $type == 20):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 34):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 1):
					if ( $data['Registry_IsZNO'] == 2 ) {
						$pcode = 'LC';
						$scode = 'C';
					}
					else {
						$pcode = 'L';
						$scode = 'H';
					}
					break;

				case ($registryIsUnion == false && $type == 6):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 21):
					$pcode = 'L';
					$scode = 'H';
					break;

				case ($registryIsUnion == false && $type == 7 && $data['DispClass_id'] == 1):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 3):
					$pcode = 'LP';
					$scode = 'DP';
					break;

				case ($registryIsUnion == false && $type == 7 && $data['DispClass_id'] == 2):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 4):
					$pcode = 'LV';
					$scode = 'DV';
					break;

				case ($registryIsUnion == false && $type == 9 && $data['DispClass_id'] == 3):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 27):
					$pcode = 'LS';
					$scode = 'DS';
					break;

				case ($registryIsUnion == false && $type == 9 && $data['DispClass_id'] == 7):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 29):
					$pcode = 'LU';
					$scode = 'DU';
					break;

				case ($registryIsUnion == false && $type == 11):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 10):
					$pcode = 'LO';
					$scode = 'DO';
					break;

				case ($registryIsUnion == false && $type == 12):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 33):
					$pcode = 'LF';
					$scode = 'DF';
					break;

				case ($registryIsUnion == false && $type == 14):
				case ($registryIsUnion == true && $data['RegistryGroupType_id'] == 2):
					$pcode = 'LT';
					$scode = 'T';
					break;
			}

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];

			//Проверка на наличие созданной ранее директории
			if ( !file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				mkdir( EXPORTPATH_REGISTRY . $out_dir );
			}
			$this->textlog->add('создали каталог ' . EXPORTPATH_REGISTRY . $out_dir);

			$Liter = ($data['KatNasel_SysNick'] == "oblast" ? "S" : "T");
			$Plat = ($data['KatNasel_SysNick'] == "oblast" ? $SCHET[0]['PLAT'] : "35");

			// случаи
			$file_re_data_sign = $scode . "M" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			$this->exportSluchDataFile = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";
			// временный файл для случаев
			$this->exportSluchDataFileTmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";

			// перс. данные
			$file_re_pers_data_sign = $pcode . "M" . $SCHET[0]['CODE_MO'] . $Liter . $Plat . "_" . $reg_endmonth . $data['Registry_pack'];
			$this->exportPersonDataFile = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$this->textlog->add('Определили наименования файлов: ' . $this->exportSluchDataFile . ' и ' . $this->exportPersonDataFile);
			$this->textlog->add('Создаем XML файлы на диске');

			// Заголовок для файла с перс. данными
			$ZGLV = [
				[
					'VERSION' => '3.2',
					'FILENAME' => $file_re_pers_data_sign,
					'FILENAME1' => $file_re_data_sign
				]
			];

			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\n" . $this->parser->parse_ext('export_xml/' . $this->exportPersonDataHeaderTemplate, $ZGLV[0], true);
			$xml_pers = preg_replace("/\R\s*\R/", "\n", $xml_pers);
			file_put_contents($this->exportPersonDataFile, $xml_pers);

			// Получаем данные
			$this->_resetPersCnt();
			$this->_resetZapCnt();

			// Объединенные реестры могут содержать данные любого типа
			// Получаем список типов реестров, входящих в объединенный реестр
			if ( $registryIsUnion ) {
				$registrytypes = $this->getUnionRegistryTypes($data['Registry_id']);

				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					// выход с ошибкой
					$this->textlog->add('getUnionRegistryTypes: При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}
			}
			else {
				$registrytypes = [ $type ];
			}

			foreach ( $registrytypes as $type ) {
				$rsp = $this->_loadRegistryDataForXmlByFuncNew($type, $data);

				if ( $rsp === false ) {
					// выход с ошибкой
					$this->textlog->add('loadRegistryDataForXmlByFuncNew: Ошибка при выгрузке реестра.');
					throw new Exception('Ошибка при выгрузке реестра.');
				}
			}

			$this->textlog->add('Получили все данные из БД');
			$this->textlog->add('Количество записей реестра = ' . $this->zapCnt);
			$this->textlog->add('Количество людей в реестре = ' . $this->persCnt);

			$SCHET[0]['VERSION'] = '3.1';
			$SCHET[0]['SD_Z'] = $this->zapCnt;
			$SCHET[0]['FILENAME'] = $file_re_data_sign;

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\n" . $this->parser->parse_ext('export_xml/' . $this->exportSluchDataHeaderTemplate, $SCHET[0], true, false);
			$xml = preg_replace("/\R\s*\R/", "\n", $xml);
			file_put_contents($this->exportSluchDataFile, $xml);
			unset($xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($this->exportSluchDataFileTmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($this->exportSluchDataFileTmp, "rb");

				if ( $fh === false ) {
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($this->exportSluchDataFile, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($this->exportSluchDataFileTmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл');

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/' . $this->exportSluchDataFooterTemplate, [], true, false);
			file_put_contents($this->exportSluchDataFile, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/' . $this->exportPersonDataFooterTemplate, [], true);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('Создан ' . $this->exportSluchDataFile);
			$this->textlog->add('Создан ' . $this->exportPersonDataFile);

			$H_registryValidate = true;
			$L_registryValidate = true;

			if ( array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK']) {
				$settingsFLK = $this->loadRegistryEntiesSettings($res[0]);

				if ( count($settingsFLK) > 0 ) {
					$upload_path = 'RgistryFields/';
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;

					if ( $tplEvnDataXSD ) {
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD);
						$dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT . $upload_path . $dirTpl . "/" . $tplEvnDataXSD;

						//Проверяем валидность 1го реестра
						//Путь до шаблона
						$H_xsd_tpl = $fileEvnDataXSD;
						//Файл с ошибками, если понадобится
						$H_validate_err_file = EXPORTPATH_REGISTRY . $out_dir . "/err_" . $file_re_data_sign . '.html';
						//Проверка
						$H_registryValidate = $this->Reconciliation($this->exportSluchDataFile, $H_xsd_tpl, 'file', $H_validate_err_file);
					}

					if ( $tplPersonDataXSD ) {
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD);
						$dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$tplPersonDataXSD = IMPORTPATH_ROOT . $upload_path . $dirTpl . "/" . $tplPersonDataXSD;

						//Проверяем 2й реестр
						//Путь до шаблона
						$L_xsd_tpl = $tplPersonDataXSD;
						//Файл с ошибками, если понадобится
						$L_validate_err_file = EXPORTPATH_REGISTRY . $out_dir . "/err_" . $file_re_pers_data_sign . '.html';
						//Проверка
						$L_registryValidate = $this->Reconciliation($this->exportPersonDataFile, $L_xsd_tpl, 'file', $L_validate_err_file);
					}
				}
			}

			$file_zip_sign = $file_re_data_sign;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$file_evn_num_name = EXPORTPATH_REGISTRY . $out_dir . "/evnnum.txt";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $this->exportSluchDataFile, $file_re_data_sign . ".xml" );
			$zip->AddFile( $this->exportPersonDataFile, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name);

			$data['Status'] = $file_zip_name;
			$this->SetXmlExportStatus($data);

			if ( !$H_registryValidate  && !$L_registryValidate ) {
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				return [
					'success' => false,
					'Error_Msg' => 'Реестр не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						<a target="_blank" href="'.$this->exportSluchDataFile.'">H файл реестра</a>,
						<a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						<a target="_blank" href="'.$this->exportPersonDataFile.'">L файл реестра</a>, 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 20
				];

			}
			elseif ( !$H_registryValidate ) {
				//Скинули статус
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($this->exportPersonDataFile);
				$this->textlog->add('Почистили папку за собой');

				return [
					'success' => false,
					'Error_Msg' => 'Файл H реестра не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						(<a target="_blank" href="'.$this->exportSluchDataFile.'">H файл реестра</a>),
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 21
				];
			}
			elseif ( !$L_registryValidate ) {
				//Скинули статус
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($this->exportSluchDataFile);
				$this->textlog->add('Почистили папку за собой');

				return [
					'success' => false,
					'Error_Msg' => 'Файл L реестра не прошёл ФЛК: <a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						(<a target="_blank" href="'.$this->exportPersonDataFile.'">L файл реестра</a>), 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 22
				];
			}
			else {
				unlink($this->exportSluchDataFile);
				unlink($this->exportPersonDataFile);
				$this->textlog->add('Почистили папку за собой');

				$this->_saveRegistryEvnNum([
					'Registry_EvnNum' => $this->registryEvnNum,
					'FileName' => $file_evn_num_name,
				]);

				// Пишем информацию о выгрузке в историю
				$this->dumpRegistryInformation($data, 2);

				$this->textlog->add('Вернули ссылку ' . $file_zip_name);

				return [ 'success' => true, 'Link' => $file_zip_name ];
			}
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->SetXmlExportStatus($data);
			$this->textlog->add($e->getMessage());
			return [ 'success' => false, 'Error_Msg' => $e->getMessage() ];
		}
	}

	/**
	 * @param $type
	 * @param $data
	 * @return bool
	 * @description Получение и запись в файлы данных для выгрузки реестров в XML с помощью функций БД (новые реестры)
	 */
	protected function _loadRegistryDataForXmlByFuncNew($type, $data) {
		$object = $this->_getRegistryObjectName($type);

		$spVersion = '2019';

		$fn_pers = $this->scheme.".p_Registry_{$object}_expPac_{$spVersion}_f";
		$fn_sl = $this->scheme.".p_Registry_{$object}_expVizit_{$spVersion}_f";
		$fn_usl = $this->scheme.".p_Registry_{$object}_expUsl_{$spVersion}_f";
		$fn_zsl = $this->scheme.".p_Registry_{$object}_expSL_{$spVersion}_f";

		if ( in_array($type, [ 1, 2, 7, 9, 11, 12, 14 ]) ) {
			$p_ds2 = $this->scheme.".p_Registry_{$object}_expDS2_{$spVersion}";
		}

		if ( in_array($type, [ 1, 14 ]) ) {
			//$p_ds3 = $this->scheme.".p_Registry_{$object}_expDS3_{$spVersion}";
		}

		if ( in_array($type, [ 1 ]) ) {
			$p_crit = $this->scheme.".p_Registry_{$object}_expCRIT_{$spVersion}";
			$p_kslp = $this->scheme.".p_Registry_{$object}_expKSLP_{$spVersion}";
		}

		if ( in_array($type, [ 7, 9, 11, 12 ]) ) {
			$p_naz = $this->scheme.".p_Registry_{$object}_expNAZ_{$spVersion}";
		}

		if ( (in_array($type, [ 1, 2 ]) && ($data['Registry_IsZNO'] == 2 || $data['PayType_SysNick'] != 'oms')) || $type == 14 ) {
			$p_bdiag = $this->scheme.".p_Registry_{$object}_expBDIAG_{$spVersion}";
			$p_bprot = $this->scheme.".p_Registry_{$object}_expBPROT_{$spVersion}";
			$p_cons = $this->scheme.".p_Registry_{$object}_expCONS_{$spVersion}";
			$p_lek_pr = $this->scheme.".p_Registry_{$object}_expLEK_PR_{$spVersion}";
			$p_napr = $this->scheme.".p_Registry_{$object}_expNAPR_{$spVersion}";
			$p_onkousl = $this->scheme.".p_Registry_{$object}_expONKOUSL_{$spVersion}";
		}

		$queryParams = [
			'Registry_id' => $data['Registry_id'],
		];

		$BDIAG = [];
		$BPROT = [];
		$CONS = [];
		$CRIT = [];
		$DS2 = [];
		$DS3 = [];
		$LEK_PR = [];
		$NAPR = [];
		$NAZ = [];
		$ONKOUSL = [];
		$SL_KOEF = [];
		$USL = [];

		$KSG_KPG_FIELDS = [ 'N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'SL_K', 'IT_SL' ];
		$ONK_SL_FIELDS = [ 'DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA' ];

		$altKeys = [
			'SL_PODR' => 'PODR'
			,'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'P_OTK_USL' => 'P_OTK'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
			,'VNOV_M_VAL' => 'VNOV_M'
		];

		$indexDS2 = 'DS2_DATA';
		$netValue = toAnsi('НЕТ', true);

		if ( in_array($type, array(7, 9, 11, 12)) ) {
			$indexDS2 = 'DS2_N_DATA';
		}

		// Сведения о проведении консилиума (CONS)
		if ( !empty($p_cons) ) {
			$query = "SELECT {$p_cons} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $CONS
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = [];
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о классификационных критериях (CRIT)
		if ( !empty($p_crit) ) {
			$query = "SELECT {$p_crit} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $CRIT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CRIT[$row['Evn_id']]) ) {
					$CRIT[$row['Evn_id']] = [];
				}

				$CRIT[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS2)
		if ( !empty($p_ds2) ) {
			$query = "SELECT {$p_ds2} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS2 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS2) ) {
				return false;
			}

			// Массив $DS2
			while ( $row = $resultDS2->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = [];
				}

				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS3)
		if ( !empty($p_ds3) ) {
			$query = "SELECT {$p_ds3} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS3 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS3) ) {
				return false;
			}

			// Массив $DS3
			while ( $row = $resultDS3->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = [];
				}

				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "SELECT {$p_kslp} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultKSLP = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultKSLP) ) {
				return false;
			}

			// Массив $SL_KOEF
			while ( $row = $resultKSLP->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($SL_KOEF[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = [];
				}

				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введённом противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($p_lek_pr) ) {
			$query = "SELECT {$p_lek_pr} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $LEK_PR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$LEK_PR[$row['EvnUsluga_id']] = [];
				}

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Назначения (NAZ)
		if ( !empty($p_naz) ) {
			$query = "SELECT {$p_naz} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultNAZ = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultNAZ) ) {
				return false;
			}

			// Массив $NAZ
			while ( $row = $resultNAZ->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = [];
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// Диагностический блок (BDIAG)
		if ( !empty($p_bdiag) ) {
			$query = "SELECT {$p_bdiag} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BDIAG
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BDIAG[$row['Evn_id']]) ) {
					$BDIAG[$row['Evn_id']] = [];
				}

				$BDIAG[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об имеющихся противопоказаниях и отказах (BPROT)
		if ( !empty($p_bprot) ) {
			$query = "SELECT {$p_bprot} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BPROT
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = [];
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// Направления (NAPR)
		if ( !empty($p_napr) ) {
			$query = "SELECT {$p_napr} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $NAPR
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = [];
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if ( !empty($p_onkousl) ) {
			$query = "SELECT {$p_onkousl} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $ONKOUSL
			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($ONKOUSL[$row['Evn_id']]) ) {
					$ONKOUSL[$row['Evn_id']] = [];
				}

				$row['LEK_PR_DATA'] = [];

				if ( isset($LEK_PR[$row['EvnUsluga_id']]) && $row['USL_TIP'] == 2 ) {
					$LEK_PR_DATA = [];

					foreach ( $LEK_PR[$row['EvnUsluga_id']] as $rowTmp ) {
						if ( !isset($LEK_PR_DATA[$rowTmp['REGNUM']]) ) {
							$LEK_PR_DATA[$rowTmp['REGNUM']] = [
								'REGNUM' => $rowTmp['REGNUM'],
								'CODE_SH' => (!empty($rowTmp['CODE_SH']) ? $rowTmp['CODE_SH'] : null),
								'DATE_INJ_DATA' => [],
							];
						}

						$LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ_DATA'][] = ['DATE_INJ' => $rowTmp['DATE_INJ']];
					}

					$row['LEK_PR_DATA'] = $LEK_PR_DATA;
					unset($LEK_PR[$row['EvnUsluga_id']]);
				}

				$ONKOUSL[$row['Evn_id']][] = $row;
			}
		}

		// Услуги (USL)
		$query = "select * from {$fn_usl}(:Registry_id)";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultUSL = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultUSL) ) {
			return false;
		}

		// Массив $USL
		while ( $row = $resultUSL->_fetch_assoc() ) {
			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
			if ( !isset($USL[$row['Evn_id']]) ) {
				$USL[$row['Evn_id']] = [];
			}

			if (!empty($row['EvnUsluga_kolvo'])) {
				$row['KOL_USL'] = number_format(($row['KOL_USL'] / $row['EvnUsluga_kolvo']), 2, '.', '');
				for($i = 0; $i < $row['EvnUsluga_kolvo']; $i++) {
					$this->_IDSERV++;
					$row['IDSERV'] = $this->_IDSERV;
					$USL[$row['Evn_id']][] = $row;
				}
			} else {
				$this->_IDSERV++;
				$row['IDSERV'] = $this->_IDSERV;
				$USL[$row['Evn_id']][] = $row;
			}
		}

		// 2. джойним сразу посещения + пациенты и гребем постепенно то что получилось, сразу записывая в файл
		$result = $this->db->query("
			with zsl as (
				select * from {$fn_zsl} (:Registry_id)
			),
			sl as (
				select * from {$fn_sl} (:Registry_id)
			),
			pers as (
			select * from {$fn_pers} (:Registry_id)
			)
			
			select
				null as fields_part_1,
				z.*,
				z.MaxEvn_id as \"MaxEvn_zid\",
				null as fields_part_2,
				s.*,
				s.Evn_id as \"Evn_sid\",
				s.Registry_id as \"Registry_sid\",
				null as fields_part_3,
				p.*
			from
				zsl z 
				inner join sl s  on s.MaxEvn_id = z.MaxEvn_id
				inner join pers p  on p.MaxEvn_id = z.MaxEvn_id
			order by
				p.FAM, p.IM, p.OT, p.ID_PAC, s.MaxEvn_id, s.Evn_id
		", $queryParams, true);

		if ( !is_object($result) ) {
			return false;
		}

		$ZAP_ARRAY = [];
		$PACIENT_ARRAY = [];

		$recKeys = []; // ключи для данных

		$prevID_PAC = null;

		while ( $one_rec = $result->_fetch_assoc() ) {
			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);

			if ( count($recKeys) == 0 ) {
				$recKeys = $this->_getKeysForRec($one_rec);

				if ( count($recKeys) < 3 ) {
					$this->textlog->add("Ошибка, неверное количество частей в запросе");
					return false;
				}
			}

			$zsl_key = $one_rec['MaxEvn_zid'];
			$sl_key = $one_rec['Evn_sid'];

			$ZSL = array_intersect_key($one_rec, $recKeys[1]);
			$SL = array_intersect_key($one_rec, $recKeys[2]);
			$PACIENT = array_intersect_key($one_rec, $recKeys[3]);

			$SL['Evn_id'] = $one_rec['Evn_sid'];
			$SL['Registry_id'] = $one_rec['Registry_sid'];

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PACIENT['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				$xml = $this->parser->parse_ext(
					'export_xml/' . $this->exportSluchDataBodyTemplate,
					[ 'ZAP' => $ZAP_ARRAY ],
					true,
					false,
					$altKeys,
					[$type == 2 ? 'TARIF' : '', 'TARIF_USL', 'SUMV_USL']
				);

				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\n", $xml);
				file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = [];
				unset($xml);

				// пишем в файл пациентов
				$xml_pers = $this->parser->parse_ext(
					'export_xml/' . $this->exportPersonDataBodyTemplate,
					[ 'PACIENT' => $PACIENT_ARRAY ],
					true,
					false,
					[],
					false
				);

				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\n", $xml_pers);
				file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
				unset($PACIENT_ARRAY);
				$PACIENT_ARRAY = [];
				unset($xml_pers);
			}

			$prevID_PAC = $PACIENT['ID_PAC'];

			if ( isset($ZAP_ARRAY[$zsl_key]) ) {
				// если уже есть законченный случай, значит добавляем в него SL
				$SL['CONS_DATA'] = [];
				$SL['DS2_DATA'] = [];
				$SL['DS2_N_DATA'] = [];
				$SL['DS3_DATA'] = [];
				$SL['KSG_KPG_DATA'] = [];
				$SL['NAPR_DATA'] = [];
				$SL['NAZ_DATA'] = [];
				$SL['ONK_SL_DATA'] = [];
				$SL['SANK'] = [];
				$SL['USL'] = [];

				$KSG_KPG_DATA = [];
				$ONK_SL_DATA = [];

				if ( isset($USL[$sl_key]) ) {
					$SL['USL'] = $USL[$sl_key];
					unset($USL[$sl_key]);
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL[$indexDS2] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}
				else if ( !empty($SL['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$SL[$indexDS2] = [['DS2' => $SL['DS2']]];
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( array_key_exists('DS2_PR', $SL) ) {
					unset($SL['DS2_PR']);
				}

				if ( array_key_exists('PR_DS2_N', $SL) ) {
					unset($SL['PR_DS2_N']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}
				else if ( !empty($SL['DS3']) ) {
					$SL['DS3_DATA'] = [['DS3' => $SL['DS3']]];
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ( !empty($SL['VER_KSG']) ) {
					foreach ( $KSG_KPG_FIELDS as $index ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}

					$KSG_KPG_DATA['CRIT_DATA'] = [];
					$KSG_KPG_DATA['SL_KOEF_DATA'] = [];

					$SL['KSG_KPG_DATA'] = [ $KSG_KPG_DATA ];
				}

				if ( isset($CRIT[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['CRIT_DATA'] = $CRIT[$sl_key];
					unset($CRIT[$sl_key]);
				}

				if ( isset($SL_KOEF[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				$onkDS2 = false;

				if ( count($SL['DS2_DATA']) > 0 ) {
					foreach ( $SL['DS2_DATA'] as $ds2 ) {
						if ( empty($ds2['DS2']) ) {
							continue;
						}

						$code = substr($ds2['DS2'], 0, 3);

						if ( ($code >= 'C00' && $code <= 'C80') || $code == 'C97' ) {
							$onkDS2 = true;
						}
					}
				}

				if (
					(
						in_array($type, [ 1, 2 ])
						&& ($data['Registry_IsZNO'] == 2 || $data['PayType_SysNick'] != 'oms')
					)
					|| (
						$type == 14
						&& !empty($SL['DS1'])
						&& (
							substr($SL['DS1'], 0, 1) == 'C'
							|| (substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
							|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
						)
					)
				) {
					// Цепляем CONS
					if ( isset($CONS[$sl_key]) ) {
						$SL['CONS_DATA'] = $CONS[$sl_key];
						unset($CONS[$sl_key]);
					}

					// Цепляем NAPR
					if ( isset($NAPR[$sl_key]) ) {
						$SL['NAPR_DATA'] = $NAPR[$sl_key];
						unset($NAPR[$sl_key]);
					}

					// Цепляем ONK_SL
					$hasONKOSLData = false;
					$ONK_SL_DATA['B_DIAG_DATA'] = [];
					$ONK_SL_DATA['B_PROT_DATA'] = [];
					$ONK_SL_DATA['ONK_USL_DATA'] = [];

					foreach ( $ONK_SL_FIELDS as $field ) {
						if ( isset($SL[$field]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA[$field] = $SL[$field];
						}
						else {
							$ONK_SL_DATA[$field] = null;
						}

						if ( array_key_exists($field, $SL) ) {
							unset($SL[$field]);
						}
					}

					if ( isset($BDIAG[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
						unset($BDIAG[$sl_key]);
					}

					if ( isset($BPROT[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
						unset($BPROT[$sl_key]);
					}

					if ( isset($ONKOUSL[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
						unset($ONKOUSL[$sl_key]);
					}

					if ( $hasONKOSLData == false ) {
						$ONK_SL_DATA = [];
					}
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZAP_ARRAY[$zsl_key]['SL'][$sl_key] = $SL;
			}
			else {
				// иначе создаём новый ZAP
				$this->persCnt++;
				$this->zapCnt++;

				$SL['CONS_DATA'] = [];
				$SL['DS2_DATA'] = [];
				$SL['DS2_N_DATA'] = [];
				$SL['DS3_DATA'] = [];
				$SL['KSG_KPG_DATA'] = [];
				$SL['NAPR_DATA'] = [];
				$SL['NAZ_DATA'] = [];
				$SL['ONK_SL_DATA'] = [];
				$SL['SANK'] = [];
				$SL['USL'] = [];

				$ZSL['SL'] = [];

				$KSG_KPG_DATA = [];
				$ONK_SL_DATA = [];
				$OS_SLUCH = [];
				$VNOV_M = [];

				if ( !empty($PACIENT['OS_SLUCH']) ) {
					$OS_SLUCH[] = ['OS_SLUCH' => $PACIENT['OS_SLUCH']];
				}

				if ( !empty($PACIENT['OS_SLUCH1']) ) {
					$OS_SLUCH[] = ['OS_SLUCH' => $PACIENT['OS_SLUCH1']];
				}

				if ( array_key_exists('OS_SLUCH', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH']);
				}
				if ( array_key_exists('OS_SLUCH1', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH1']);
				}

				if ( !empty($ZSL['VNOV_M1']) ) {
					$VNOV_M[] = ['VNOV_M_VAL' => $ZSL['VNOV_M1']];
				}

				if ( !empty($ZSL['VNOV_M2']) ) {
					$VNOV_M[] = ['VNOV_M_VAL' => $ZSL['VNOV_M2']];
				}

				$ZSL['OS_SLUCH_DATA'] = $OS_SLUCH;
				$ZSL['VNOV_M_DATA'] = $VNOV_M;

				$PACIENT['DOST'] = [];
				$PACIENT['DOST_P'] = [];

				if ( $PACIENT['NOVOR'] == '0' ) {
					if ( empty($PACIENT['FAM']) ) {
						$PACIENT['DOST'][] = ['DOST_VAL' => 2];
					}

					if ( empty($PACIENT['IM']) ) {
						$PACIENT['DOST'][] = ['DOST_VAL' => 3];
					}

					if ( empty($PACIENT['OT']) || mb_strtoupper($PACIENT['OT'], 'windows-1251') == $netValue ) {
						$PACIENT['DOST'][] = ['DOST_VAL' => 1];
					}
				}
				else {
					if ( empty($PACIENT['FAM_P']) ) {
						$PACIENT['DOST_P'][] = ['DOST_P_VAL' => 2];
					}

					if ( empty($PACIENT['IM_P']) ) {
						$PACIENT['DOST_P'][] = ['DOST_P_VAL' => 3];
					}

					if ( empty($PACIENT['OT_P']) || mb_strtoupper($PACIENT['OT_P'], 'windows-1251') == $netValue ) {
						$PACIENT['DOST_P'][] = ['DOST_P_VAL' => 1];
					}
					$PACIENT['FAM'] = null;
					$PACIENT['IM'] = null;
					$PACIENT['OT'] = null;
				}

				if ( isset($USL[$sl_key]) ) {
					$SL['USL'] = $USL[$sl_key];
					unset($USL[$sl_key]);
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL[$indexDS2] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}
				else if ( !empty($SL['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$SL[$indexDS2] = [['DS2' => $SL['DS2']]];
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( array_key_exists('DS2_PR', $SL) ) {
					unset($SL['DS2_PR']);
				}

				if ( array_key_exists('PR_DS2_N', $SL) ) {
					unset($SL['PR_DS2_N']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}
				else if ( !empty($SL['DS3']) ) {
					$SL['DS3_DATA'] = [['DS3' => $SL['DS3']]];
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ( !empty($SL['VER_KSG']) ) {
					foreach ( $KSG_KPG_FIELDS as $index ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}

					$KSG_KPG_DATA['CRIT_DATA'] = [];
					$KSG_KPG_DATA['SL_KOEF_DATA'] = [];

					$SL['KSG_KPG_DATA'] = [ $KSG_KPG_DATA ];
				}

				if ( isset($CRIT[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['CRIT_DATA'] = $CRIT[$sl_key];
					unset($CRIT[$sl_key]);
				}

				if ( isset($SL_KOEF[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				$onkDS2 = false;

				if ( count($SL['DS2_DATA']) > 0 ) {
					foreach ( $SL['DS2_DATA'] as $ds2 ) {
						if ( empty($ds2['DS2']) ) {
							continue;
						}

						$code = substr($ds2['DS2'], 0, 3);

						if ( ($code >= 'C00' && $code <= 'C80') || $code == 'C97' ) {
							$onkDS2 = true;
						}
					}
				}

				if (
					(
						in_array($type, [ 1, 2 ])
						&& ($data['Registry_IsZNO'] == 2 || $data['PayType_SysNick'] != 'oms')
					)
					|| (
						$type == 14
						&& !empty($SL['DS1'])
						&& (
							substr($SL['DS1'], 0, 1) == 'C'
							|| (substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
							|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
						)
					)
				) {
					// Цепляем CONS
					if ( isset($CONS[$sl_key]) ) {
						$SL['CONS_DATA'] = $CONS[$sl_key];
						unset($CONS[$sl_key]);
					}

					// Цепляем NAPR
					if ( isset($NAPR[$sl_key]) ) {
						$SL['NAPR_DATA'] = $NAPR[$sl_key];
						unset($NAPR[$sl_key]);
					}

					// Цепляем ONK_SL
					$hasONKOSLData = false;
					$ONK_SL_DATA['B_DIAG_DATA'] = [];
					$ONK_SL_DATA['B_PROT_DATA'] = [];
					$ONK_SL_DATA['ONK_USL_DATA'] = [];

					foreach ( $ONK_SL_FIELDS as $field ) {
						if ( isset($SL[$field]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA[$field] = $SL[$field];
						}
						else {
							$ONK_SL_DATA[$field] = null;
						}

						if ( array_key_exists($field, $SL) ) {
							unset($SL[$field]);
						}
					}

					if ( isset($BDIAG[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
						unset($BDIAG[$sl_key]);
					}

					if ( isset($BPROT[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
						unset($BPROT[$sl_key]);
					}

					if ( isset($ONKOUSL[$sl_key]) ) {
						$hasONKOSLData = true;
						$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
						unset($ONKOUSL[$sl_key]);
					}

					if ( $hasONKOSLData == false ) {
						$ONK_SL_DATA = [];
					}
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZSL['N_ZAP'] = $this->zapCnt;
				$ZSL['IDCASE'] = $this->zapCnt;
				$ZSL['PR_NOV'] = ($data['Registry_IsRepeated'] == 2 ? 1 : 0);
				$ZSL['PACIENT'] = [ $PACIENT ];
				$ZSL['SL'][$sl_key] = $SL;

				if (!in_array($PACIENT['ID_PAC'], $this->ID_PAC_list)) {
					$this->ID_PAC_list[] = $PACIENT['ID_PAC'];
					$PACIENT_ARRAY[$PACIENT['ID_PAC']] = $PACIENT;
				}

				$ZAP_ARRAY[$zsl_key] = $ZSL;
			}

			if ( !isset($this->registryEvnNum[$sl_key]) ) {
				$this->registryEvnNum[$sl_key] = [];
			}

			$this->registryEvnNum[$sl_key][] = [
				'e' => $sl_key,
				'r' => $SL['Registry_id'],
				't' => $type,
				'z' => $this->zapCnt,
			];
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			// пишем в файл случаи
			$xml = $this->parser->parse_ext(
				'export_xml/' . $this->exportSluchDataBodyTemplate,
				[ 'ZAP' => $ZAP_ARRAY ],
				true,
				false,
				$altKeys,
				[$type == 2 ? 'TARIF' : '', 'TARIF_USL', 'SUMV_USL']
			);

			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\n", $xml);
			file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);

			// пишем в файл пациентов
			$xml_pers = $this->parser->parse_ext(
				'export_xml/' . $this->exportPersonDataBodyTemplate,
				[ 'PACIENT' => $PACIENT_ARRAY ],
				true,
				false,
				[],
				false
			);

			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\n", $xml_pers);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($PACIENT_ARRAY);
			unset($xml_pers);
		}

		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @description Получение данных о счете для выгрузки объединенного реестра в XML (новые реестры)
	 */
	protected function _loadRegistrySCHETForXmlUsingNew($data) {
		$queryParams = [
			'Registry_id' => $data['Registry_id'],
		];

		$p_schet = $this->scheme . ".p_Registry_expScet";

		$query = "select {$p_schet} (Registry_id := :Registry_id)";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$header = $result->result('array');

		if ( !is_array($header) || count($header) == 0 ) {
			return false;
		}

		array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);

		return [ $header[0] ];
	}

	/**
	 * @param array $data
	 * @return array|bool
	 * @description Получение списка ошибок данных
	 */
	public function loadRegistryError($data = []) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( empty($data['nopaging']) ) {
			if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
				return false;
			}
		}

		$this->setRegistryParamsByType($data);

		$fieldsList = [];
		$filterList = [ "RE.Registry_id = :Registry_id" ];
		$joinList = [];
		$params = [ 'Registry_id' => $data['Registry_id'] ];

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "RE.Person_SurName iLIKE :Person_SurName";

			$params['Person_SurName'] = $data['Person_SurName'] . "%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "RE.Person_FirName iLIKE :Person_FirName";

			$params['Person_FirName'] = $data['Person_FirName'] . "%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "RE.Person_SecName iLIKE :Person_SecName";

			$params['Person_SecName'] = $data['Person_SecName'] . "%";
		}

		if ( !empty($data['RegistryErrorType_id']) ) {
			$filterList[] = "RE.RegistryErrorType_id = :RegistryErrorType_id";
			$params['RegistryErrorType_id'] = $data['RegistryErrorType_id'];
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RE.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		switch ( $this->RegistryType_id ) {
			case 1:
			case 14:
				$joinList[] = "
					left join v_EvnSection es  on ES.EvnSection_id = RE.Evn_id
					left join v_MesOld m  on m.Mes_id = ES.Mes_id
					LEFT JOIN LATERAL(
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = ES.MedPersonal_id
						limit 1
					) as MP ON true
				";

				$fieldsList[] = "m.Mes_Code as \"Mes_Code\"";
				$fieldsList[] = "COALESCE(MP.Person_Fio, RD.MedPersonal_Fio) as \"MedPersonal_Fio\"";

				if ( !empty($data['MedPersonal_id']) ) {
					$filterList[] = "COALESCE(ES.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
					$params['MedPersonal_id'] = $data['MedPersonal_id'];
				}
				break;

			case 2:
				$joinList[] = "
					left join v_EvnVizitPL evpl  on evpl.EvnVizitPL_id = RE.Evn_id
					LEFT JOIN LATERAL (
						select 
							t1.EvnUslugaCommon_id,
							t1.UslugaComplex_id as UslugaComplex_uid
						from
							v_EvnUslugaCommon t1 
							left join v_UslugaComplex t2  on t2.UslugaComplex_id = t1.UslugaComplex_id
							left join v_UslugaCategory t3  on t3.UslugaCategory_id = t2.UslugaCategory_id
						where
							t1.EvnUslugaCommon_pid = evpl.EvnVizitPL_id
							and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
						order by
							t1.EvnUslugaCommon_setDT desc
                        limit 1
					) EU ON true
					left join v_UslugaComplex U  on U.UslugaComplex_id = EU.UslugaComplex_uid
					LEFT JOIN LATERAL(
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = evpl.MedPersonal_id
                        limit 1
					) as MP ON true
				";

				$fieldsList[] = "U.UslugaComplex_Code as \"Usluga_Code\"";
				$fieldsList[] = "COALESCE(MP.Person_Fio, RD.MedPersonal_Fio) as \"MedPersonal_Fio\"";

				if ( !empty($data['MedPersonal_id']) ) {
					$filterList[] = "COALESCE(evpl.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
					$params['MedPersonal_id'] = $data['MedPersonal_id'];
				}
				break;

			case 4:
			case 7:
				$joinList[] = "
					left join v_EvnVizitDispDop evdd  on evdd.EvnVizitDispDop_id = RE.Evn_id
					left join v_EvnUslugaDispDop eudd  on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
					left join v_UslugaComplex U  on U.UslugaComplex_id = eudd.UslugaComplex_id
					LEFT JOIN LATERAL(
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = eudd.MedPersonal_id
						limit 1
					) as MP ON true
				";

				$fieldsList[] = "U.UslugaComplex_Code as \"Usluga_Code\"";
				$fieldsList[] = "COALESCE(MP.Person_Fio, RD.MedPersonal_Fio) as \"MedPersonal_Fio\"";


				if ( !empty($data['MedPersonal_id']) ) {
					$filterList[] = "COALESCE(eudd.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
					$params['MedPersonal_id'] = $data['MedPersonal_id'];
				}
				break;

			case 5:
			case 9:
				$joinList[] = "
					left join v_EvnVizitDispOrp evdo  on evdo.EvnVizitDispOrp_id = RE.Evn_id
					left join v_EvnUslugaDispOrp eudo  on eudo.EvnUslugaDispOrp_pid = evdo.EvnVizitDispOrp_id
					left join v_UslugaComplex U  on U.UslugaComplex_id = eudo.UslugaComplex_id
					LEFT JOIN LATERAL(
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = evdo.MedPersonal_id
					) as MP ON true
				";

				$fieldsList[] = "U.UslugaComplex_Code as \"Usluga_Code\"";
				$fieldsList[] = "COALESCE(MP.Person_Fio, RD.MedPersonal_Fio) as \"MedPersonal_Fio\"";

				if (!empty($data['MedPersonal_id'])) {
					$filterList[] = "COALESCE(evdo.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
					$params['MedPersonal_id'] = $data['MedPersonal_id'];
				}
				break;

			default:
				$fieldsList[] = "RD.MedPersonal_Fio as \"MedPersonal_Fio\"";

				if (!empty($data['MedPersonal_id'])) {
					$filterList[] = "RD.MedPersonal_id = :MedPersonal_id";
					$params['MedPersonal_id'] = $data['MedPersonal_id'];
				}
				break;
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 12, 17)) ) {
			$joinList[] = "
				left join v_EvnPLDisp epd  on epd.EvnPLDisp_id = COALESCE(RD.Evn_rid, RE.Evn_rid, RE.Evn_id)
			";
			$fieldsList[] = "epd.DispClass_id as \"DispClass_id\"";
		}

		$query = "
			select
				-- select
				ROW_NUMBER() OVER (ORDER by RE.Registry_id, RE.RegistryErrorType_id, RE.Evn_id) as \"RegistryError_id\",
				RE.Registry_id as \"Registry_id\",
				RE.Evn_id as \"Evn_id\",
				RE.Evn_id as \"Evn_ident\",
				RE.Evn_rid as \"Evn_rid\",
				RE.EvnClass_id as \"EvnClass_id\",
				RE.RegistryErrorType_id as \"RegistryErrorType_id\",
				RE.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				RTrim(RE.RegistryErrorType_Name) as \"RegistryErrorType_Name\",
				RE.RegistryError_Desc as \"RegistryError_Desc\",
				RE.RegistryErrorType_Descr as \"RegistryErrorType_Descr\",
				RE.Person_id as \"Person_id\",
				RE.Server_id as \"Server_id\",
				RE.PersonEvn_id as \"PersonEvn_id\",
				RTrim(RE.Person_FIO) as \"Person_FIO\",
				to_char(RE.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				LTRIM(RTRIM(COALESCE(RD.Polis_Ser, '') || ' ' || COALESCE(RD.Polis_Num, ''))) as \"Person_Polis\",
				CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
				RE.LpuSection_id as \"LpuSection_id\",
				RTrim(RE.LpuSection_name) as \"LpuSection_name\",
				to_char(RE.Evn_setDate, 'DD.MM.YYYY') as \"Evn_setDate\",
				to_char(RE.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\",
				COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				COALESCE(MSO.MedSpecOms_Code || '. ', '') || MSO.MedSpecOms_Name as \"MedSpecOms_Name\",
				R.RegistryType_id as \"RegistryType_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				RE.RegistryErrorClass_id as \"RegistryErrorClass_id\",
				RTrim(RE.RegistryErrorClass_Name) as \"RegistryErrorClass_Name\",
				COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
				" . (count($fieldsList) > 0 ? "," . implode(',', $fieldsList) : "") . "
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryErrorObject} RE 
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso  on mso.MedSpecOms_id = rd.MedSpec_id
				left join {$this->scheme}.v_Registry R  on R.Registry_id = RE.Registry_id
				left join v_LpuSection LS  on LS.LpuSection_id = RE.LpuSection_id
				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
				" . implode(' ', $joinList) . "
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code,
				RE.Registry_id,
				RE.RegistryErrorType_id,
				RE.Evn_id
				-- end order by
		";

		if (!empty($data['nopaging'])) {

			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}
		//echo getDebugSQL($query, $params);exit;

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение id Lpu участника взаиморасчтов для печати листа согласования
	 */

		public function getLpuSidList($data){
			if ( empty($data['Registry_id']) ) {
				return false;
			}

			return $this->queryResult("
			select distinct Lpu_sid  as \"Lpu_sid\" from {$this->scheme}.v_RegistryDataPar  where Registry_id = :Registry_id
		", $data);
		}

}

