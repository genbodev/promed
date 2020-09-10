<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry_model - модель для работы с таблицей Registry
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require(APPPATH.'models/Registry_model.php');
class Pskov_Registry_model extends Registry_model {
	public $scheme = "r60";
	public $region = "pskov";
	public $MaxEvnField = "Evn_id";

	private $_IDCASE = 0;
	private $_PersonIds = array();

	private $_registryTypeList = array(
		1 => array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар', 'SP_Object' => 'EvnPS'),
		2 => array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника', 'SP_Object' => 'EvnPL'),
		6 => array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь', 'SP_Object' => 'SMP'),
		7 => array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года', 'SP_Object' => 'EvnPLDD13'),
		9 => array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года', 'SP_Object' => 'EvnPLOrp13'),
		11 => array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф. осмотры взр. населения', 'SP_Object' => 'EvnPLProf'),
		12 => array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних', 'SP_Object' => 'EvnPLProfTeen'),
		14 => array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь', 'SP_Object' => 'EvnHTM'),
		15 => array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги', 'SP_Object' => 'EvnUslugaPar'),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение дополнительных полей для сохранения реестра
	 */
	function getSaveRegistryAdditionalFields() {
		return "
			@DispClass_id = :DispClass_id,
			@Registry_IsRepeated = :Registry_IsRepeated,
			@Registry_IsZNO = :Registry_IsZNO,
			@Registry_rid = :Registry_rid,
		";
	}

	/**
	 * Получение дополнительных полей
	 */
	function getReformErrRegistryAdditionalFields() {
		return ",DispClass_id";
	}
	

	/**
	 *	Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	public function SetXmlPackNum($data) {
		$query = "
			declare
				 @packNum int
				,@Err_Msg varchar(400);

			set nocount on;

			begin try
				set @packNum = (select top 1 Registry_FileNum from {$this->scheme}.v_Registry with (nolock) where Registry_id = :Registry_id);

				if ( @packNum is null )
					begin
						set @packNum = (
							select max(Registry_FileNum)
							from {$this->scheme}.v_Registry with (nolock)
							where Lpu_id = :Lpu_id
								and ISNULL(PayType_id, 0) = ISNULL(:PayType_id, 0)
								and ISNULL(RegistryGroupType_id, 0) = ISNULL(:RegistryGroupType_id, 0)
								and SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) = :Registry_endMonth
								and Registry_FileNum is not null
						);

						set @packNum = ISNULL(@packNum, 0) + 1;

						update {$this->scheme}.Registry with (rowlock)
						set Registry_FileNum = @packNum
						where Registry_id = :Registry_id
					end

				if ( @packNum > 9 )
					begin
						set @packNum = 9;
					end
			end try

			begin catch
				set @Err_Msg = error_message();
				set @packNum = null;
			end catch

			set nocount off;

			select @packNum as packNum, @Err_Msg as Error_Msg;
		";
		// echo getDebugSQL($query, $data);die();
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
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		// Закомментировал условие выбора пути до файла
		// @task https://redmine.swan.perm.ru/issues/60634
		/*$xmlExportPath = 'case when ( Registry_xmlExpDT is null or datediff(mi, Registry_xmlExpDT, dbo.tzGetDate()) < 5 ) then RTrim(Registry_xmlExportPath) else NULL end as Registry_xmlExportPath,';

		if ( isSuperadmin() ) {
			$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';
		}*/

		$query = "
			select
				RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,
				RTrim(Registry_xmlExpPathErr) as Registry_xmlExpPathErr,
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.RegistryGroupType_id,
				pt.PayType_id,
				ISNULL(pt.PayType_SysNick, 'oms') as PayType_SysNick,
				kn.KatNasel_SysNick,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
				RDSum.RegistryData_Count as RegistryData_Count,
				IsNull(R.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
				IsNull(rcs.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
				rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name,
				R.DispClass_id,
				R.Registry_IsRepeated,
				R.Registry_IsZNO,
				CONVERT(varchar(10), Registry_begDate, 120) as Registry_begDate,
				CONVERT(varchar(10), Registry_endDate, 120) as Registry_endDate,
				SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
			from {$this->scheme}.Registry R with (nolock)
				left join v_PayType pt (nolock) on pt.PayType_id = R.PayType_id
				left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
				outer apply(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_RegistryData RD with (nolock)
					where RD.Registry_id = R.Registry_id
				) RDSum
				left join RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				R.Registry_id = :Registry_id
		";

		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id']
			)
		);

		if (is_object($result)) {
			$r = $result->result('array');

			if ( is_array($r) && count($r) > 0 ) {
				return $r;
			}
			else {
				return array('success' => false, 'Error_Msg' => 'Ошибка при получении данных реестра');
			}
		}
		else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных реестра)');
		}
	}

	/**
	 *	Комментарий
	 */
	function loadRegistryErrorTFOMS($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		$this->setRegistryParamsByType($data);

		$filterList = array();

		if ( !empty($data['Person_SurName']) ) {
			if ( $this->RegistryType_id == 6 ) {
				$filterList[] = "isnull(ccc.Person_SurName, ps.Person_SurName) like :Person_SurName";
			}
			else {
				$filterList[] = "ps.Person_SurName like :Person_SurName ";
			}

			$params['Person_SurName'] = $data['Person_SurName'] . "%";
		}

		if ( !empty($data['Person_FirName']) ) {
			if ( $this->RegistryType_id == 6 ) {
				$filterList[] = "isnull(ccc.Person_FirName, ps.Person_FirName) like :Person_FirName";
			}
			else {
				$filterList[] = "ps.Person_FirName like :Person_FirName ";
			}

			$params['Person_FirName'] = $data['Person_FirName'] . "%";
		}

		if ( !empty($data['Person_SecName']) ) {
			if ( $this->RegistryType_id == 6 ) {
				$filterList[] = "isnull(ccc.Person_SecName, ps.Person_SecName) like :Person_SecName";
			}
			else {
				$filterList[] = "ps.Person_SecName like :Person_SecName ";
			}

			$params['Person_SecName'] = $data['Person_SecName'] . "%";
		}

		if ( isset($data['RegistryErrorType_Code']) ) {
			$filterList[] = "RE.RegistryErrorType_Code = :RegistryErrorType_Code";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}

		if ( !empty($data['Person_FIO']) ) {
			if ( $this->RegistryType_id == 6 ) {
				$filterList[] = "
					case
						when ps.Person_id is not null then rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
						else rtrim(isnull(ccc.Person_SurName, '')) + ' ' + rtrim(isnull(ccc.Person_FirName, '')) + ' ' + rtrim(isnull(ccc.Person_SecName, ''))
					end like :Person_FIO
				";
			}
			else {
				$filterList[] = "rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO";
			}

			$params['Person_FIO'] = $data['Person_FIO'] . "%";
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( $this->RegistryType_id == 6 ) {
			$query = "
				select
					-- select
					RE.RegistryErrorTFOMS_id,
					RE.Registry_id,
					null as Evn_rid,
					RE.CmpCloseCard_id as Evn_id,
					111 as EvnClass_id,
					ret.RegistryErrorType_Code,
					case
						when ps.Person_id is not null then rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
						else rtrim(isnull(ccc.Person_SurName,'')) + ' ' + rtrim(isnull(ccc.Person_FirName,'')) + ' ' + rtrim(isnull(ccc.Person_SecName, ''))
					end as Person_FIO,
					ps.Person_id,
					ps.PersonEvn_id,
					ps.Server_id,
					convert(varchar(10), case when ps.Person_id is not null then ps.Person_BirthDay else ccc.Person_BirthDay end, 104) as Person_BirthDay,
					RE.RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment,
					ISNULL(msf.Person_Fio, mp.Person_Fio) as MedPersonal_Fio,
					lb.LpuBuilding_Name,
					ls.LpuSection_Name as LpuSection_name,
					ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
					case when RD.Evn_id is not null then 1 else 2 end as RegistryData_notexist,
					RCS.RegistryCheckStatus_Code
					-- end select
				from
					-- from
					{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
					left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
					left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
					left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join v_CmpCloseCard cclc with (nolock) on cclc.CmpCloseCard_id = RD.Evn_id
					left join v_CmpCallCard ccc with (nolock) on ccc.CmpCallCard_id = cclc.CmpCallCard_id
					left join v_PersonState ps with (nolock) on ps.Person_id = ccc.Person_id
					left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
					left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = cclc.MedStaffFact_id
					outer apply (
						select top 1 Person_Fio
						from v_MedPersonal with (nolock)
						where MedPersonal_id = cclc.MedPersonal_id
					) mp
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = isnull(msf.LpuBuilding_id, cclc.LpuBuilding_id)
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = ISNULL(msf.LpuSection_id, cclc.LpuSection_id)
					-- end from
				where
					-- where
					RE.Registry_id = :Registry_id
					" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
					-- end where
				order by
					-- order by
					RE.RegistryErrorType_Code
					-- end order by
			";
		}
		else {
			$query = "
				select
					-- select
					RE.RegistryErrorTFOMS_id,
					RE.Registry_id,
					Evn.Evn_rid,
					RE.Evn_id,
					Evn.EvnClass_id,
					ret.RegistryErrorType_Code,
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
					ps.Person_id,
					ps.PersonEvn_id,
					ps.Server_id,
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
					RegistryErrorTFOMS_FieldName,
					RegistryErrorTFOMS_BaseElement,
					RegistryErrorTFOMS_Comment,
					MP.Person_Fio as MedPersonal_Fio,
					LB.LpuBuilding_Name,
					LS.LpuSection_Name as LpuSection_name,
					ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
					case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
					RCS.RegistryCheckStatus_Code
					-- end select
				from
					-- from
					{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
					left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
					left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
					left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
					left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
					left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id
					left join v_LpuSection LS (nolock) on LS.LpuSection_id = ISNULL(ES.LpuSection_id, evpl.LpuSection_id)
					left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					outer apply(
						select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
					) as MP
					left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id
						and ps.Server_id = Evn.Server_id
					left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
					-- end from
				where
					-- where
					RE.Registry_id=:Registry_id
					" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
					-- end where
				order by
					-- order by
					RE.RegistryErrorType_Code
					-- end order by
			";
		}
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
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
	 *	Данные по реестру
	 */
	function loadRegData($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id'], 'Registry_id' => $data['Registry_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';
		$filter .= ' and R.Registry_id = :Registry_id';
		
		$query = "
			select 
				R.Registry_id,
				R.RegistryType_id,
				R.Registry_Num,
				Lpu.Lpu_Email,
				Lpu.Lpu_Nick
			from {$this->scheme}.v_Registry R
			left join v_Lpu Lpu on Lpu.Lpu_id = R.Lpu_id
			where 
				{$filter}
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 *	Загрузка ошибок по посещениям
	 */
	function loadEvnVizitErrorData($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
				and (OrgSmo_id is null
				or LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		if ( is_object($result) ) {
			$sel = $result->result('array');
			if ( $sel[0]['cnt'] > 0 )
				$load_errors_only = false;
		}
		else {
			return false;
		}
		
		$errors_join = "";
		if ( $load_errors_only === true )
			$errors_join = " inner join {$this->scheme}.v_RegistryError re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
		$query = "
			select
				rd.Evn_id as ID_POS,
				rd.Person_id as ID,
				convert(varchar(8), ev.EvnVizit_setDate, 112) as DATE_POS,
				'' as SMO,
				'' as POL_NUM,
				'' as ID_STATUS,
				'' as NAM,
				'' as FNAM,
				'' as SEX,
				'' as SNILS,
				null as DATE_BORN,
				null as DATE_SV,
				null as FLAG
			from
				{$this->scheme}.v_RegistryData rd
				". $errors_join ."
				inner join v_EvnVizit ev on ev.EvnVizit_id = rd.Evn_id
			where
				rd.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Загрузка ошибок по движениям в стационаре
	 */
	function loadEvnSectionErrorData($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
				and (OrgSmo_id is null
				or LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		if ( is_object($result) ) {
			$sel = $result->result('array');
			if ( $sel[0]['cnt'] > 0 )
				$load_errors_only = false;
		}
		else {
			return false;
		}
		
		$errors_join = "";
		if ( $load_errors_only === true )
			$errors_join = " inner join {$this->scheme}.v_RegistryError re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
		$query = "
			select
				rd.Evn_id as ID_POS,
				rd.Person_id as ID,
				convert(varchar(8), es.EvnSection_setDT, 112) as DATE_POS,
				'' as SMO,
				'' as POL_NUM,
				'' as ID_STATUS,
				'' as NAM,
				'' as FNAM,
				'' as SEX,
				'' as SNILS,
				null as DATE_BORN,
				null as DATE_SV,
				null as FLAG
			from
				{$this->scheme}.v_RegistryData rd
				". $errors_join ."
				inner join v_EvnSection es on es.EvnSection_id = rd.Evn_id
			where
				rd.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение перс. данных из ошибочного реестра
	 */
	function loadPersonInfoFromErrorRegistry($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
				and (OrgSmo_id is null
				or LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		if ( is_object($result) ) {
			$sel = $result->result('array');
			if ( $sel[0]['cnt'] > 0 )
				$load_errors_only = false;
		}
		else {
			return false;
		}
		
		$errors_join = "";
		if ( $load_errors_only === true )
			$errors_join = " inner join {$this->scheme}.v_RegistryError re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
		$query = "
			select distinct
				rd.Person_id as ID,
				dbo.UcWord(ps.Person_SurName) as FAM,	
				dbo.UcWord(ps.Person_FirName) as NAM,
				dbo.UcWord(ps.Person_SecName) as FNAM,
				convert(varchar(8), ps.Person_BirthDay, 112) as DATE_BORN,
				ISNULL(Sex.Sex_Code, 0) as SEX,
				doct.DocumentType_Code as DOC_TYPE,
				rtrim(isnull(doc.Document_Ser, '')) as DOC_SER,
				rtrim(isnull(doc.Document_Num, '')) as DOC_NUM,
				rtrim(isnull(ps.Person_Inn, '')) as INN,
				coalesce(addr.KLStreet_Code, addr.KLTown_Code, addr.KLCity_Code, addr.KLSubRGN_Code, addr.KLRGN_Code) as KLADR,
				rtrim(isnull(addr.Address_House, '')) as HOUSE,
				rtrim(isnull(addr.Address_Flat, '')) as ROOM,
				smoorg.Org_Code as SMO,
				rtrim(isnull(PS.Polis_Num, '')) as POL_NUM,
				case
					when ss.SocStatus_Code = 1 then 4
					when ss.SocStatus_Code = 2 then 7
					when ss.SocStatus_Code = 3 then 1
					when ss.SocStatus_Code = 4 then 3
					when ss.SocStatus_Code = 5 then 5
				else
					''
				end as [STATUS]
			from
				{$this->scheme}.v_RegistryData rd
				". $errors_join ."
				inner join v_PersonState ps on ps.Person_id = rd.Person_id
				left join Sex on Sex.Sex_id = ps.Sex_id
				left join Document doc on ps.Document_id = doc.Document_id
				left join DocumentType doct on doc.DocumentType_id = doct.DocumentType_id
				left join v_Address_all addr on addr.Address_id = ps.UAddress_id
				left join SocStatus ss on ss.SocStatus_id = ps.SocStatus_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join OrgSmo osmo on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Org smoorg on smoorg.Org_id = osmo.Org_id
			where
				rd.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		/*$query = "
			select
				rtrim(isnull(LpuUnitSet_Code, '')) as LpuUnitSet_Code
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
		";
		$result_lpu = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		if ( is_object($result_lpu) ) {
			$sel_lpu = $result_lpu->result('array');
			if ( count($sel_lpu) > 0 )
				$lpu_code = $sel_lpu[0]['LpuUnitSet_Code'];
		}
		else {
			return false;
		}*/
		// заодно получаем код ЛПУ, но здесь он будет LpuUnitSet
		$query = "
			select
				rtrim(isnull(Org_Code, '')) as Lpu_Code
			from
				v_Lpu with (nolock)
			where
				Lpu_id = ?
		";
		$result_lpu = $this->db->query($query, array($data['Lpu_id']));
		if ( is_object($result_lpu) ) {
			$sel_lpu = $result_lpu->result('array');
			if ( count($sel_lpu) > 0 )
				$lpu_code = $sel_lpu[0]['Lpu_Code'];
		}
		else {
			return false;
		}

		if ( is_object($result) ) {
			$sel_data = $result->result('array');
			return array('data' => $sel_data, 'lpu_code' => $lpu_code);
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данные для экспорта в DBF
	 */
	/*function loadRegistryDataForDbfExport($data, $type)
	{
		$procedures = array();
		$response = array();

		switch ($type)
		{
			case 1: //stac
				$procedures['SCHET'] = $this->scheme.".p_Registry_EvnPS_expScet";
				$procedures['VIZIT'] = $this->scheme.".p_Registry_EvnPS_expVizit";
				$procedures['USL'] = $this->scheme.".p_Registry_EvnPS_expUsl";
				$procedures['PERS'] = $this->scheme.".p_Registry_EvnPS_expPac";
				break;
			case 2: //polka
				$procedures['SCHET'] = $this->scheme.".p_Registry_EvnPL_expScet";
				$procedures['VIZIT'] = $this->scheme.".p_Registry_EvnPL_expVizit";
				$procedures['USL'] = $this->scheme.".p_Registry_EvnPL_expUsl";
				$procedures['PERS'] = $this->scheme.".p_Registry_EvnPL_expPac";
				break;
			case 6: //smp
				$procedures['SCHET'] = $this->scheme.".p_Registry_SMP_expScet";
				$procedures['VIZIT'] = $this->scheme.".p_Registry_SMP_expVizit";
				$procedures['USL'] = $this->scheme.".p_Registry_SMP_expUsl";
				$procedures['PERS'] = $this->scheme.".p_Registry_SMP_expPac";
				break;
		}

		foreach ($procedures as $key=>$value) {
			// выполняем каждую процедуру
			$query = "
				exec {$value} @Registry_id = ?
			";

			$result = $this->db->query($query, array($data['Registry_id']));

			if ( is_object($result) ) {
				$response[$key] = $result->result('array');
			}
		}

		return $response;
	}*/

	/**
	 *	Получение данных заголовка для выгрузки реестров в XML
	 */
	function loadRegistrySCHETForXmlUsing($data, $type = 13) {
		$bud = (in_array($data['PayType_SysNick'], array('bud', 'fbud')) ? '_bud' : '');

		$query = "exec {$this->scheme}.p_Registry_expScet{$bud} @Registry_id = :Registry_id";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	public function loadRegistryDataForXmlUsing($type, $data, &$Registry_EvnNum, &$errors, $file_re_data_name, $file_re_pers_data_name, $registry_data_template_body, $person_data_template_body) {
		$this->setRegistryParamsByType(array(
			'RegistryType_id' => $type
		));

		if ( $type == 13 ) {
			$object = 'EvnPL';
		}
		else {
			$object = $this->_getRegistryObjectName($type);
		}

		$bud = (in_array($data['PayType_SysNick'], array('bud', 'fbud')) ? '_bud' : '');

		if ( empty($object) ) {
			return false;
		}

		$p_zsl = $this->scheme . ".p_Registry_" . $object . "_expSL" . $bud;
		$p_sl = $this->scheme . ".p_Registry_" . $object . "_expVizit" . $bud;
		$p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl" . $bud;
		$p_pers = $this->scheme . ".p_Registry_" . $object . "_expPac" . $bud;

		if ( in_array($type, array(7, 9, 11, 12)) ) {
			$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2";
		}

		if ( in_array($type, array(7, 9, 11, 12)) ) {
			$p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ";
		}

		if ( in_array($type, array(1)) ) {
			$p_kslp = $this->scheme . ".p_Registry_" . $object . "_expKSLP";
			$p_crit = $this->scheme . ".p_Registry_" . $object . "_expCRIT_2018";
		}

		if ( $data['PayType_SysNick'] == 'oms' && ($type == 14 || (in_array($type, array(1, 2)) && $data['Registry_IsZNO'] == 2)) && $data['RegistryIsAfter20180901'] == true ) {
			$p_bdiag = $this->scheme . ".p_Registry_" . $object . "_expBDIAG";
			$p_bprot = $this->scheme . ".p_Registry_" . $object . "_expBPROT";
			$p_napr = $this->scheme . ".p_Registry_" . $object . "_expNAPR";
			$p_onkousl = $this->scheme . ".p_Registry_" . $object . "_expONKOUSL";
			$p_cons = $this->scheme.".p_Registry_{$object}_expCONS";
			$p_lek_pr = $this->scheme.".p_Registry_{$object}_expLEK_PR";
		}

		// люди
		$query = "exec {$p_pers} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_pac = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_pac)) {
			return false;
		}

		// посещения
		$query = "exec {$p_sl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_sluch = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_sluch)) {
			return false;
		}

		// услуги
		$query = "exec {$p_usl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_usl = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_usl)) {
			return false;
		}

		$BDIAG = array();
		$BPROT = array();
		$CONS = array();
		$CRIT = array();
		$DS2 = array();
		$NAPR = array();
		$NAZ = array();
		$ONKOUSL = array();
		$PACIENT = array();
		$SL = array();
		$SL_KOEF = array();
		$USL = array();
		$ZAP = array();
		$ZSL = array();

		$netValue = toAnsi('НЕТ', true);

		// диагнозы (DS2)
		if (!empty($p_ds2)) {
			$query = "exec {$p_ds2} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds2 = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_ds2)) {
				return false;
			}
			while ($row = $result_ds2->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = array();
				}

				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// назначения (NAZ)
		if (!empty($p_naz)) {
			$query = "exec {$p_naz} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_naz = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_naz)) {
				return false;
			}
			while ($row = $result_naz->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = array();
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "exec {$p_kslp} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_kslp = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_kslp)) {
				return false;
			}
			while ($row = $result_kslp->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($SL_KOEF[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = array();
				}

				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Диагностический блок (BDIAG)
		if ( !empty($p_bdiag) ) {
			$query = "exec {$p_bdiag} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$queryResult = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}
			while ($row = $queryResult->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BDIAG[$row['Evn_id']]) ) {
					$BDIAG[$row['Evn_id']] = array();
				}

				$BDIAG[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об имеющихся противопоказаниях и отказах (BPROT)
		if ( !empty($p_bprot) ) {
			$query = "exec {$p_bprot} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$queryResult = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}
			while ($row = $queryResult->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = array();
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// Направления (NAPR)
		if ( !empty($p_napr) ) {
			$query = "exec {$p_napr} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$queryResult = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}
			while ($row = $queryResult->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = array();
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		// Критерии
		if ( !empty($p_crit) ) {
			$query = "exec {$p_crit} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CRIT[$row['Evn_id']]) ) {
					$CRIT[$row['Evn_id']] = array();
				}

				$CRIT[$row['Evn_id']][] = array(
					'CRIT' => $row['CRIT'],
				);
			}
		}

		// Сведения о проведении консилиума (CONS)
		if ( !empty($p_cons) ) {
			$query = "exec {$p_cons} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = array();
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($p_lek_pr) ) {
			$query = "exec {$p_lek_pr} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$LEK_PR[$row['EvnUsluga_id']] = array();
				}

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Направления (ONKOUSL)
		if ( !empty($p_onkousl) ) {
			$query = "exec {$p_onkousl} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$queryResult = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($queryResult)) {
				return false;
			}
			while ($row = $queryResult->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($ONKOUSL[$row['Evn_id']]) ) {
					$ONKOUSL[$row['Evn_id']] = array();
				}

				$row['LEK_PR_DATA'] = array();
				if (isset($row['EvnUsluga_id']) && isset($LEK_PR[$row['EvnUsluga_id']]) && in_array($row['USL_TIP'], array(2, 4))) {
					$LEK_PR_DATA = array();

					foreach ($LEK_PR[$row['EvnUsluga_id']] as $rowTmp) {
						if (!isset($LEK_PR_DATA[$rowTmp['REGNUM']])) {
							$LEK_PR_DATA[$rowTmp['REGNUM']] = array(
								'REGNUM' => $rowTmp['REGNUM'],
								'CODE_SH' => (!empty($rowTmp['CODE_SH']) ? $rowTmp['CODE_SH'] : null),
								'DATE_INJ_DATA' => array(),
							);
						}

						$LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $rowTmp['DATE_INJ']);
					}

					$row['LEK_PR_DATA'] = $LEK_PR_DATA;
					unset($LEK_PR[$row['EvnUsluga_id']]);
				}

				$ONKOUSL[$row['Evn_id']][] = $row;
			}
		}

		// ЗСЛ (ZSL)
		$query = "exec {$p_zsl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_sl = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_sl)) {
			return false;
		}

		while ($row = $result_sl->_fetch_assoc()) {
			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
			$ZSL[$row['MaxEvn_id']] = $row;
		}

		// Формируем массив случаев
		while ($record = $result_sluch->_fetch_assoc()) {
			array_walk_recursive($record, 'ConvertFromUTF8ToWin1251', true);

			if ( !isset($SL[$record['MaxEvn_id']]) ) {
				$SL[$record['MaxEvn_id']] = array();
			}

			// привязываем случаи к законченному случаю
			$SL[$record['MaxEvn_id']][] = $record;
		}

		// Формируем массив пациентов
		while ($pers = $result_pac->_fetch_assoc()) {
			array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);

			if ( !empty($pers['Person_id']) ) {
				$pers['ID_PAC'] = $pers['Person_id'];
				$pers['DOST'] = array();
				$pers['DOST_P'] = array();

				if ( $data['PayType_SysNick'] == 'oms' ) {
					if ( $pers['NOVOR'] != '0' ) {
						if ( empty($pers['FAM_P']) ) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => 2);
						}

						if ( empty($pers['IM_P']) ) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => 3);
						}

						if ( empty($pers['OT_P']) || strtoupper($pers['OT_P']) == $netValue ) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => 1);
						}
					}
					else {
						if ( empty($pers['FAM']) ) {
							$pers['DOST'][] = array('DOST_VAL' => 2);
						}

						if ( empty($pers['IM']) ) {
							$pers['DOST'][] = array('DOST_VAL' => 3);
						}

						if ( empty($pers['OT']) || strtoupper($pers['OT']) == $netValue ) {
							$pers['DOST'][] = array('DOST_VAL' => 1);
						}
					}
				}

				$PACIENT[$pers['Person_id']] = $pers;
			}
		}

		// Формируем массив услуг
		while ($usluga = $result_usl->_fetch_assoc()) {
			array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);

			if ( !isset($USL[$usluga['Evn_id']]) ) {
				$USL[$usluga['Evn_id']] = array();
			}

			// привязываем услуги к случаю
			$USL[$usluga['Evn_id']][] = $usluga;
		}

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
		$altKeys = array(
			 'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'P_OTK_USL' => 'P_OTK'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
		);

		$SD_Z = 0;

		$KSG_KPG_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL');
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');

		// Идём по случаям, как набираем 1000 записей -> пишем сразу в файл.
		$this->textlog->add('Начинаем обработку случаев');
		foreach($ZSL as $key => $oneZSL) {
			if ( empty($oneZSL['MaxEvn_id']) ) {
				continue;
			}

			$key = $oneZSL['MaxEvn_id'];

			// привязывем случаи к законченному случаю
			$oneZSL['SL'] = array();
			if ( isset($SL[$key]) ) {
				foreach($SL[$key] as $oneSL) {
					$slKey = $oneSL['Evn_id'];

					$oneSL['CONS_DATA'] = array();
					$oneSL['DS2_DATA'] = array();
					$oneSL['NAPR_DATA'] = array();
					$oneSL['NAZ_DATA'] = array();
					$oneSL['ONK_SL_DATA'] = array();

					if (isset($DS2[$slKey])) {
						$oneSL['DS2_DATA'] = $DS2[$slKey];
						unset($DS2[$slKey]);
					}
					else if ( !empty($oneSL['DS2']) ) {
						$oneSL['DS2_DATA'] = array(array('DS2' => $oneSL['DS2']));
					}

					if (isset($NAZ[$slKey])) {
						$oneSL['NAZ_DATA'] = $NAZ[$slKey];
						unset($NAZ[$slKey]);
					}

					if ( array_key_exists('DS2', $oneSL) ) {
						unset($oneSL['DS2']);
					}

					$onkDS2 = false;
					$ONK_SL_DATA = array();

					if ( count($oneSL['DS2_DATA']) > 0 ) {
						foreach ( $oneSL['DS2_DATA'] as $ds2 ) {
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
						($data['Registry_IsZNO'] == 2 || $type == 14)
						&& (
							$oneSL['DS_ONK'] == 1
							|| (
								!empty($oneSL['DS1'])
								&& (
									substr($oneSL['DS1'], 0, 1) == 'C'
									|| (substr($oneSL['DS1'], 0, 3) >= 'D00' && substr($oneSL['DS1'], 0, 3) <= 'D09')
									|| ($oneSL['DS1'] == 'D70' && $onkDS2 == true)
								)
							)
						)
					) {
						if ( isset($CONS[$slKey]) ) {
							$oneSL['CONS_DATA'] = $CONS[$slKey];
							unset($CONS[$slKey]);
						}

						if ( isset($NAPR[$slKey]) ) {
							$oneSL['NAPR_DATA'] = $NAPR[$slKey];
							unset($NAPR[$slKey]);
						}
					}

					if (
						(empty($oneSL['DS_ONK']) || $oneSL['DS_ONK'] != 1)
						//&& (empty($oneSL['P_CEL']) || $oneSL['P_CEL'] != '1.3')
						//&& (empty($visit['REAB']) || $visit['P_CEL'] != '1')
						&& !empty($oneSL['DS1'])
						&& (
							substr($oneSL['DS1'], 0, 1) == 'C'
							|| (substr($oneSL['DS1'], 0, 3) >= 'D00' && substr($oneSL['DS1'], 0, 3) <= 'D09')
							|| ($oneSL['DS1'] == 'D70' && $onkDS2 == true)
						)
					) {
						$hasONKOSLData = false;
						$ONK_SL_DATA['B_DIAG_DATA'] = array();
						$ONK_SL_DATA['B_PROT_DATA'] = array();
						$ONK_SL_DATA['ONK_USL_DATA'] = array();

						foreach ( $ONK_SL_FIELDS as $field ) {
							if ( isset($oneSL[$field]) && strlen((string)$oneSL[$field]) > 0 ) {
								$hasONKOSLData = true;
								$ONK_SL_DATA[$field] = $oneSL[$field];
							}
							else {
								$ONK_SL_DATA[$field] = null;
							}

							if ( array_key_exists($field, $oneSL) ) {
								unset($oneSL[$field]);
							}
						}

						if ( isset($BDIAG[$slKey]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$slKey];
							unset($BDIAG[$slKey]);
						}

						if ( isset($BPROT[$slKey]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$slKey];
							unset($BPROT[$slKey]);
						}

						if ( isset($ONKOUSL[$slKey]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$slKey];
							unset($ONKOUSL[$slKey]);
						}

						if ( $hasONKOSLData == false ) {
							$ONK_SL_DATA = array();
						}
					}

					if ( count($ONK_SL_DATA) > 0 ) {
						$oneSL['ONK_SL_DATA'][] = $ONK_SL_DATA;
					}

					$KSG_KPG_DATA = array();

					foreach ( $KSG_KPG_FIELDS as $index ) {
						if (isset($oneSL[$index])) {
							$KSG_KPG_DATA[$index] = $oneSL[$index];
							unset($oneSL[$index]);
						}
					}

					if ( count($KSG_KPG_DATA) > 0 ) {
						$KSG_KPG_DATA['CRIT_DATA'] = array();
						$KSG_KPG_DATA['SL_KOEF'] = array();

						if ( isset($SL_KOEF[$slKey]) ) {
							$KSG_KPG_DATA['SL_KOEF'] = $SL_KOEF[$slKey];
							unset($SL_KOEF[$slKey]);
						}

						if ( isset($CRIT[$slKey]) ) {
							$KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$slKey];
							unset($CRIT[$slKey]);
						}

						$oneSL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
					}
					else {
						$oneSL['KSG_KPG_DATA'] = array();
					}

					// привязываем услуги к случаю
					if ( isset($USL[$slKey]) ) {
						$oneSL['USL'] = $USL[$slKey];
						unset($USL[$slKey]);
					}
					/*else if (
						// только для стационара
						$type == 1
						// при наличии данных по онкоспецифике
						&& (
							count($oneSL['ONK_SL_DATA']) > 0
							|| (!empty($oneSL['DS_ONK']) && $oneSL['DS_ONK'] == 1 && isset($NAPR[$slKey]))
						)
					) {
						$oneSL['USL'] = array(
							array(
								'IDSERV' => isset($oneSL['SL_ID']) ? $oneSL['SL_ID'] : null,
								'LPU_USL' => isset($oneZSL['LPU']) ? $oneZSL['LPU'] : null, // тут тянем из ZSL, т.к. в SL поля нет
								'LPU_1_USL' => isset($oneSL['LPU_1']) ? $oneSL['LPU_1'] : null,
								'PODR_USL' => isset($oneSL['PODR']) ? $oneSL['PODR'] : null,
								'PROFIL_USL' => isset($oneSL['PROFIL']) ? $oneSL['PROFIL'] : null,
								'VID_VME' => null,
								'DET_USL' => isset($oneSL['DET']) ? $oneSL['DET'] : null,
								'DATE_IN' => isset($oneSL['DATE_1']) ? $oneSL['DATE_1'] : null,
								'DATE_OUT' => isset($oneSL['DATE_2']) ? $oneSL['DATE_2'] : null,
								'DS' => isset($oneSL['DS1']) ? $oneSL['DS1'] : null,
								'CODE_USL' => isset($oneSL['KSG_KPG_DATA'][0]['N_KSG']) ? $oneSL['KSG_KPG_DATA'][0]['N_KSG'] : null,
								'KOL_USL' => 1,
								'TARIF_USL' => isset($oneSL['TARIF']) ? $oneSL['TARIF'] : null,
								'SUMV_USL' => 0,
								'PRVS_USL' => isset($oneSL['PRVS']) ? $oneSL['PRVS'] : null,
								'CODE_MD' => isset($oneSL['IDDOKT']) ? $oneSL['IDDOKT'] : null,
								'COMENTU' => null,
								'NPL' => null,
							)
						);
					}*/
					else {
						$oneSL['USL'] = array();
					}

					$SD_Z++;
					$oneZSL['SL'][] = $oneSL;
				}

				unset($SL[$key]);
			}

			if ($type == 6) {
				$prevKey = 'ccc_' . $oneZSL['MaxEvn_id'];
			} else {
				$prevKey = 'e_' . $oneZSL['MaxEvn_id'];
			}
			if (!empty($data['prevRegistryData'][$prevKey])) {
				// если есть номер из предыдущего реестра, то берём его
				$oneZSL['IDCASE'] = $data['prevRegistryData'][$prevKey];
			} else {
				// иначе порядковый
				$this->_IDCASE++;
				$oneZSL['IDCASE'] = $this->_IDCASE;
			}

			$pacKey = $oneZSL['Person_id'];

			$ZAP[$key] = array(
				'N_ZAP' => $oneZSL['IDCASE'],
				'PACIENT' => array($PACIENT[$pacKey]),
				'SLUCH' => array($oneZSL)
			);

			if ( $data['PayType_SysNick'] == 'oms' ) {
				$ZAP[$key]['PR_NOV'] = (!empty($oneZSL['PR_NOV']) ? $oneZSL['PR_NOV'] : 0);
			}
			else {
				$ZAP[$key]['PR_NOV'] = null;
			}

			$Registry_EvnNum[$this->_IDCASE] = $key;

			// проапдейтить поле RegistryData_RowNum
			$this->db->query("
				update
					rd with (rowlock)
				set
					rd.{$this->RegistryDataObject}_RowNum = :RegistryData_RowNum
				from
					{$this->scheme}.{$this->RegistryDataObject} rd
					inner join {$this->scheme}.v_RegistryGroupLink rgl (nolock) on rgl.Registry_id = rd.Registry_id
				where
					rgl.Registry_pid = :Registry_id
					and rd.{$this->RegistryDataEvnField} = :Evn_id
			", array(
				'Registry_id' => $data['Registry_id'],
				'Evn_id' => $oneZSL['MaxEvn_id'],
				'RegistryData_RowNum' => $this->_IDCASE
			));

			unset($ZSL[$key]);

			if (count($ZAP) >= 1000) {
				// пишем в файл
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $ZAP), true, false, $altKeys, false);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили 1000 записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP);
				$ZAP = array();
			}
		}

		if (count($ZAP) > 0) {
			// пишем в файл
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $ZAP), true, false, $altKeys, false);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . count($ZAP) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP);
		}

		unset($DS2);
		unset($NAZ);
		unset($SL_KOEF);
		unset($ZSL);
		unset($SL);
		unset($USL);

		$toFile = array();
		foreach($PACIENT as $onepac) {
			if (!in_array($onepac['Person_id'], $this->_PersonIds)) {
				$toFile[] = $onepac;
				$this->_PersonIds[] = $onepac['Person_id'];
			}
			if (count($toFile) >= 1000) {
				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $toFile), true, false, array(), false);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($toFile);
				$toFile = array();
			}
		}
		if (count($toFile) > 0) {
			// пишем в файл
			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $toFile), true, false, array(), false);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($toFile);
		}

		unset($toFile);
		unset($PACIENT);

		return $SD_Z;
	}
	
	/**
	 *	Получение данных для выгрузки реестра в DBF
	 */
	function loadRegistryForDbfUsing($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';
		
		if ( isset($data['Registry_id']) )
		{
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		$query = "
			select 
				os.Org_Code as ID_SMO,
				--R.LpuUnitSet_Code as ID_SUBLPU, 
				null as ID_SUBLPU, 
				--rt.RegistryType_Code as ID_SUBLPU, -- тип
				1 as [TYPE],
				YEAR(R.Registry_endDate) as [YEAR],
				MONTH(R.Registry_endDate) as [MONTH],
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as DATE_BEG,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as DATE_END
				
			from {$this->scheme}.v_Registry R with (NOLOCK)
			left join RegistryType rt on rt.RegistryType_id = R.RegistryType_id
			inner join v_Lpu Lpu on Lpu.Lpu_id = R.Lpu_id
			left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id
			left join Org os on os.Org_id = OrgSmo.Org_id
			--left join LpuUnitSet on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id
			where 
				{$filter}
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных по предыдущим реестрам
	 */
	function getPrevRegistryData($data) {
		$resp = $this->queryResult("
			select 
				rd.RegistryData_RowNum as RowNum,
				'e_' + cast(rd.Evn_id as varchar) as RowId
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_Registry r (nolock) on r.Registry_id = rgl.Registry_id
				inner join {$this->scheme}.RegistryData rd (nolock) on rd.Registry_id = r.Registry_rid
			where
				rgl.Registry_pid = :Registry_id

			union all

			select 
				rdc.RegistryDataCmp_RowNum as RowNum,
				'ccc_' + cast(rdc.CmpCloseCard_id as varchar) as RowId
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_Registry r (nolock) on r.Registry_id = rgl.Registry_id
				inner join {$this->scheme}.RegistryDataCmp rdc (nolock) on rdc.Registry_id = r.Registry_rid
			where
				rgl.Registry_pid = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		$maxNumber = 0;
		$prevRegistryData = array();
		foreach($resp as $respone) {
			$prevRegistryData[$respone['RowId']] = $respone['RowNum'];
			if ($respone['RowNum'] > $maxNumber) {
				$maxNumber = $respone['RowNum'];
			}
		}

		return array(
			'prevRegistryData' => $prevRegistryData,
			'maxNumber' => $maxNumber
		);
	}

	/**
	 *	Установка статуса экспорта реестра в XML
	 */
	function SetXmlExportStatus($data)
	{
		if ($this->scheme=='dbo') {
			$this->setRegistryCheckStatus($data);
		}
		if ((0 != $data['Registry_id']))
		{
			$query = "
				update {$this->scheme}.Registry with (rowlock)
				set
					Registry_xmlExportPath = :Status,
					Registry_xmlExpPathErr = :Registry_xmlExpPathErr,
					Registry_xmlExpDT = dbo.tzGetDate()
				where Registry_id = :Registry_id
			";
			/*die (getDebugSQL($query, array(
			 'Registry_id' => $data['Registry_id'],
			 'Status' => $data['Status']
				)));*/

			$result = $this->db->query($query,
				array(
					'Registry_id' => $data['Registry_id'],
					'Registry_xmlExpPathErr' => !empty($data['Registry_xmlExpPathErr'])?$data['Registry_xmlExpPathErr']:null,
					'Status' => $data['Status']
				)
			);
			if (is_object($result))
			{
				return true;
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}
	
	/**
	 *	Получение списка реестров
	 */
	function loadRegistry($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';
		
		if (isset($data['Registry_id']))
		{
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		if (isset($data['RegistryType_id']))
		{
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		if (empty($data['Registry_id'])) {
			if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
				// реесты по бюджету
				$filter .= " and pt.PayType_SysNick in ('bud','fbud')";
			} else {
				$filter .= " and ISNULL(pt.PayType_SysNick, '') not in ('bud','fbud')";
			}
		}

		$loadDeleted = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 12);
		$loadQueue = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 11);

		if ($loadQueue)
		{
			$query = "
			Select 
				R.RegistryQueue_id as Registry_id,
				R.OrgSmo_id,
				R.KatNasel_id,
				R.DispClass_id,
				r.PayType_id,
				pt.PayType_SysNick,
				DispClass.DispClass_Name,
				kn.KatNasel_SysNick,
				kn.KatNasel_Name,
				--R.LpuUnitSet_id,
				R.RegistryType_id,
				11 as RegistryStatus_id,
				2 as Registry_IsActive,
				RTrim(R.Registry_Num)+' / в очереди: '+LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--LpuUnitSet.LpuUnitSet_Code,
				OrgSmo.OrgSmo_Nick as OrgSmo_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				0 as Registry_Count,
				0 as Registry_ErrorCount,
				0 as Registry_CountErr,
				0 as Registry_Sum,
				1 as Registry_IsProgress,
				1 as Registry_IsNeedReform,
				0 as RegistryErrorCom_IsData,
				0 as RegistryError_IsData,
				0 as RegistryNoPolis_IsData,
				0 as RegistryErrorTFOMS_IsData,
				'' as Registry_updDate,
				R.Registry_IsRepeated,
				R.Registry_IsZNO,
				R.Registry_rid,
				'' as Registry_xmlExportPath,
				null as RegistryCheckStatus_id,
				null as RegistryCheckStatus_SysNick,
				null as RegistryCheckStatus_Name,
				0 as RegistryHealDepCheckJournal_AccRecCount,
				0 as RegistryHealDepCheckJournal_DecRecCount,
				0 as RegistryHealDepCheckJournal_UncRecCount
			from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
				left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = R.OrgSmo_id
				left join v_KatNasel kn with (NOLOCK) on kn.KatNasel_id = r.KatNasel_id
				left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
				left join v_PayType pt with (nolock) on pt.PayType_id = R.PayType_id
			where {$filter}";
		}
		else 
		{
			$source_table = 'v_Registry';
			if (isset($data['RegistryStatus_id']))
			{
				if ($loadDeleted) {
					// если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
				}
				else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
			}
			//todo: во вьюхе на Пскове нет Registry_xmlExportPath
			$query = "
			Select 
				R.Registry_id,
				R.OrgSmo_id,
				R.KatNasel_id,
				R.DispClass_id,
				r.PayType_id,
				pt.PayType_SysNick,
				DispClass.DispClass_Name,
				kn.KatNasel_SysNick,
				kn.KatNasel_Name,
				--R.LpuUnitSet_id,
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.Registry_IsActive,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				RTrim(R.Registry_Num) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--R.Registry_Sum,
				--R.LpuUnitSet_Code,
				R.OrgSmo_Name as OrgSmo_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				isnull(R.Registry_RecordCount, 0) as Registry_Count,
				isnull(R.Registry_ErrorCount, 0) as Registry_ErrorCount,
				isnull(R.Registry_CountErr, 0) as Registry_CountErr,
				isnull(R.Registry_Sum, 0.00) as Registry_Sum,
				case when (RQ.RegistryQueueHistory_id is not null) and (RQ.RegistryQueueHistory_endDT is null) then 1 else 0 end as Registry_IsProgress,
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104), ''))+' '+
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),108), '')) as Registry_updDate,
				R.Registry_IsRepeated,
				R.Registry_IsZNO,
				R.Registry_rid,
				RTrim(R1.Registry_xmlExportPath) as Registry_xmlExportPath,
				RCS.RegistryCheckStatus_id,
				RCS.RegistryCheckStatus_SysNick,
				RCS.RegistryCheckStatus_Name,
				RegistryError.RegistryError_IsData,
				RegistryNoPolis.RegistryNoPolis_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
				RegistryErrorCom.RegistryErrorCom_IsData,
				case when exists(select top 1 Evn_id from RegistryDouble with(nolock) where Registry_id = R.Registry_id) then 1 else 0 end as issetDouble,
				convert(varchar, RQH.RegistryQueueHistory_endDT, 104) + ' ' + convert(varchar, RQH.RegistryQueueHistory_endDT, 108) as ReformTime,
				rhdcj.RegistryHealDepCheckJournal_AccRecCount,
				rhdcj.RegistryHealDepCheckJournal_DecRecCount,
				rhdcj.RegistryHealDepCheckJournal_UncRecCount
			from {$this->scheme}.{$source_table} R with (NOLOCK)
				left join {$this->scheme}.Registry R1 with(nolock) on R1.Registry_id = R.Registry_id
				left join v_KatNasel kn with (NOLOCK) on kn.KatNasel_id = r.KatNasel_id
				left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				left join v_PayType pt with (nolock) on pt.PayType_id = R.PayType_id
				outer apply(
					select top 1
						RegistryQueueHistory_id,
						RegistryQueueHistory_endDT,
						RegistryQueueHistory.Registry_id
					from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
					where RegistryQueueHistory.Registry_id = R.Registry_id
					order by RegistryQueueHistory_id desc
				) RQ
				outer apply(
						select top 1 RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
				) RQH
				outer apply (
					select top 1
						rhdcj.RegistryHealDepCheckJournal_AccRecCount,
						rhdcj.RegistryHealDepCheckJournal_DecRecCount,
						rhdcj.RegistryHealDepCheckJournal_UncRecCount
					from
						v_RegistryHealDepCheckJournal rhdcj with (nolock)
					where
						rhdcj.Registry_id = r.Registry_id
					order by
						rhdcj.RegistryHealDepCheckJournal_Count desc,
						rhdcj.RegistryHealDepCheckJournal_id desc
				) rhdcj
				outer apply (
					select top 1
						case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData,
						RE.RegistryErrorTFOMSType_id
					from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK)
					where RE.Registry_id = R.Registry_id
		  		) RegistryErrorTFOMS
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from dbo.v_{$this->RegistryErrorComObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorCom
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_RegistryNoPolis RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
		  		
			where 
				{$filter}
			order by
				R.Registry_endDate DESC,
				RQH.RegistryQueueHistory_endDT DESC
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
	 *	Установка реестра в очередь на формирование 
	 *	Возвращает номер в очереди 
	 */
	function saveRegistryQueue($data) 
	{
		// Сохранение нового реестра
		if (empty($data['Registry_id'])) 
		{
			$data['Registry_IsActive']=2;
			$operation = 'insert';
		}
		else
		{
			$operation = 'update';
			$data['Registry_IsZNO'] = $this->getFirstResultFromQuery("select top 1 Registry_IsZNO from {$this->scheme}.v_Registry with (nolock) where Registry_id = :Registry_id", $data);
		}
		
		$re = $this->loadRegistryQueue($data);
		if (is_array($re) && (count($re) > 0))
		{
			if ($operation=='update')
			{
				if ($re[0]['RegistryQueue_Position']>0)
				{
					return array(array('success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
				}
			}
		}
		
		$params = array
		(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'LpuUnitSet_id' => null, //$data['LpuUnitSet_id'],
			'DispClass_id' => $data['DispClass_id'],
			'PayType_id' => $data['PayType_id'],
			'KatNasel_id' => $data['KatNasel_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'pmUser_id' => $data['pmUser_id'],
			'Registry_IsRepeated' => $data['Registry_IsRepeated'],
			'Registry_IsZNO' => $data['Registry_IsZNO'],
			'Registry_rid' => $data['Registry_rid']
		);
		$fields = "";
		switch ($data['RegistryType_id'])
		{
			case 1: 
				//$params['KatNasel_id'] = $data['KatNasel_id'];
				//$fields .= "@KatNasel_id = :KatNasel_id,";
				break;
			case 2: 
				//$params['KatNasel_id'] = $data['KatNasel_id'];
				//$fields .= "@KatNasel_id = :KatNasel_id,";
				break;
			default:
				break;
		}
		if ( in_array($data['RegistryType_id'], array(1,2,4,5,6,7,8,9,10,11,12,14,15)) )
		{
			$query = "
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000),
					@RegistryQueue_id bigint = null,
					@RegistryQueue_Position bigint = null,
					@curdate datetime = dbo.tzGetDate();
				exec {$this->scheme}.p_RegistryQueue_ins
					@RegistryQueue_id = @RegistryQueue_id output,
					@RegistryQueue_Position = @RegistryQueue_Position output,
					@Registry_id = :Registry_id,
					@RegistryType_id = :RegistryType_id,
					@Lpu_id = :Lpu_id,
					@Registry_begDate = :Registry_begDate,
					@Registry_endDate = :Registry_endDate,
					@Registry_IsRepeated = :Registry_IsRepeated,
					@Registry_IsZNO = :Registry_IsZNO,
					@Registry_rid = :Registry_rid,
					@OrgRSchet_id = :OrgRSchet_id,
					@DispClass_id = :DispClass_id,
					@PayType_id = :PayType_id,
					@KatNasel_id = :KatNasel_id,
					{$fields}
					@Registry_Num = :Registry_Num,
					@Registry_accDate = @curdate, 
					@RegistryStatus_id = :RegistryStatus_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @RegistryQueue_id as RegistryQueue_id, @RegistryQueue_Position as RegistryQueue_Position, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			//		--@LpuUnitSet_id = :LpuUnitSet_id,
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
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}
		}
		else 
		{
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}
	}
	
	/**
	 *	Переформирование реестра
	 */
	function reformRegistry($data) 
	{
		$query = "
			select
				Registry_id,
				Registry_rid,
				Lpu_id,
				RegistryType_id,
				RegistryStatus_id,
				convert(varchar,cast(Registry_begDate as datetime),112) as Registry_begDate,
				convert(varchar,cast(Registry_endDate as datetime),112) as Registry_endDate,
				KatNasel_id,
				DispClass_id,
				PayType_id,
				--LpuUnitSet_id,
				Registry_Num,
				Registry_IsActive,
				Registry_IsRepeated,
				Registry_IsZNO,
				OrgRSchet_id,
				convert(varchar,cast(Registry_accDate as datetime),112) as Registry_accDate
			from
				{$this->scheme}.v_Registry with (NOLOCK)
			where
				Registry_id = ?
		";
		
		$result = $this->db->query($query, array($data['Registry_id']));
		
		if (is_object($result)) 
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				$data['Registry_rid'] = $row[0]['Registry_rid'];
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['Registry_IsRepeated'] = $row[0]['Registry_IsRepeated'];
				$data['Registry_IsZNO'] = $row[0]['Registry_IsZNO'];
				$data['OrgRSchet_id'] = $row[0]['OrgRSchet_id'];
				$data['KatNasel_id'] = $row[0]['KatNasel_id'];
				$data['DispClass_id'] = $row[0]['DispClass_id'];
				$data['PayType_id'] = $row[0]['PayType_id'];
				$data['LpuUnitSet_id'] = null; //$row[0]['LpuUnitSet_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];

				// Постановка реестра в очередь 
				return $this->saveRegistryQueue($data);
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Реестр не найден в базе');
			}
		}
		else 
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}
	
	/**
	 *	Удаление ошибки из реестра
	 */
	function deleteRegistryError($data) 
	{
		$filter = "";
		$params = array();
		$join = "";
		if ($data['Registry_id']>0)
		{
			$filter ="RegistryError.Registry_id = :Registry_id ";
			$params['Registry_id'] = $data['Registry_id'];
		}
		else 
			return false;
		$query = "
			Delete from {$this->scheme}.RegistryError
			where {$filter};
		";
		$result = $this->db->query($query, $params);
		return true;
	}

	/**
	 *	Удаление реестра
	 */
	function deleteRegistry($data)
	{
		$this->load->model('Utils_model', 'umodel');
		$response = $this->umodel->ObjectRecordDelete($data, "Registry", true, $data['id'], $this->scheme);
		return $response;
	}
	
	/**
	 *	Проверка
	 */
	function checkErrorDataInRegistry($data) {
		$query = "
			select top 1 
				rd.Registry_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
			where
				rgl.Registry_pid = :Registry_id
				and (rd.RegistryData_RowNum = :N_ZAP OR :N_ZAP IS NULL) and (rd.Evn_id = :IDCASE OR :IDCASE IS NULL)
				and (rd.Person_id = :ID_PERS OR :ID_PERS IS NULL)

			union all

			select top 1 
				rdc.Registry_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.RegistryDataCmp rdc (nolock) on rdc.Registry_id = rgl.Registry_id
			where
				rgl.Registry_pid = :Registry_id
				and (rdc.RegistryDataCmp_RowNum = :N_ZAP OR :N_ZAP IS NULL) and (rdc.CmpCloseCard_id = :IDCASE OR :IDCASE IS NULL)
				and (rdc.Person_id = :ID_PERS OR :ID_PERS IS NULL)
		";

		$params['Registry_id'] = $data['Registry_id'];
		$params['N_ZAP'] = (isset($data['N_ZAP']) && is_numeric($data['N_ZAP']) && $data['N_ZAP'] > 0 ? $data['N_ZAP'] : NULL);
		$params['IDCASE'] = (isset($data['IDCASE']) && is_numeric($data['IDCASE']) && $data['IDCASE'] > 0 ? $data['IDCASE'] : NULL);
		$params['ID_PERS'] = (isset($data['ID_PERS']) && is_numeric($data['ID_PERS']) && $data['ID_PERS'] > 0 ? $data['ID_PERS'] : NULL);

		$result = $this->db->query($query, $params);
		
		if (is_object($result)) 
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Получение идентификатора события по номеру строки в объедененном реестре
	 */
	function getEvnIdByRowNum($registry_id, $row_num) {
		$params = array(
			'Registry_id' => $registry_id,
			'RegistryData_RowNum' => $row_num
		);
		$query = "
			select top 1 rd.Evn_id
			from {$this->scheme}.v_RegistryGroupLink rgl (nolock)
			inner join {$this->scheme}.RegistryData rd with(nolock) on rd.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_id and rd.RegistryData_RowNum = :RegistryData_RowNum
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryAdditionalFields() {
		return ',DispClass.DispClass_id,DispClass.DispClass_Name';
	}

	/**
	 *	Получение списка дополнительных джойнов для запроса
	 */
	function getLoadRegistryAdditionalJoin() {
		return '
			left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
		';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryQueueAdditionalFields() {
		return ',DispClass_id,Registry_IsRepeated,Registry_IsZNO,Registry_rid';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return ',DispClass_id,PayType_id,Registry_IsRepeated,Registry_IsZNO,Registry_rid';
	}
	
	/**
	 *	Снятие с посещения признака вхождения в реестр
	 */
	function setVizitNotInReg($Registry_id) 
	{
		// Проставляем ошибочные посещения обратно
		
		/*$params = array();
		$params['Registry_id'] = $Registry_id;
		if ($Registry_id>0)
		{
			$query = "
				update EvnVizit with (PAGLOCK)
				set EvnVizit_IsInReg = 1
				from {$this->scheme}.v_RegistryData rd with (NOLOCK)
					inner join {$this->scheme}.v_RegistryError re on re.Evn_id = rd.Evn_id
						and re.Registry_id = rd.Registry_id
				where EvnVizit.EvnVizit_id = rd.Evn_id
					and rd.Registry_id = :Registry_id
				
				update EvnSection with (PAGLOCK)
				set EvnSection_IsInReg = 1
				from {$this->scheme}.v_RegistryData rd with (NOLOCK)
					inner join {$this->scheme}.v_RegistryError re on re.Evn_id = rd.Evn_id and re.Registry_id = rd.Registry_id
				where EvnSection.EvnSection_id = rd.Evn_id
					and rd.Registry_id = :Registry_id
				
				update {$this->scheme}.Registry
				set Registry_ErrorCount = (
					select count(distinct rd.Evn_id)
					from {$this->scheme}.v_RegistryData rd with (NOLOCK) 
						inner join {$this->scheme}.v_RegistryError re with (NOLOCK) on re.Evn_id = rd.Evn_id
							and re.Registry_id = rd.Registry_id
					where rd.Registry_id = :Registry_id
				)
				where Registry_id = :Registry_id
				
				update {$this->scheme}.RegistryData
				set Paid_id = (case when re.RegistryError_Count > 0 then 1 else 2 end)
				from {$this->scheme}.v_RegistryData rd
					outer apply (
						select count(*) as RegistryError_Count
						from {$this->scheme}.v_RegistryError re with (nolock)
						where re.Evn_id = rd.Evn_id
							and re.Registry_id = rd.Registry_id
					) re
				where RegistryData.Registry_id = :Registry_id
					and RegistryData.Evn_id = rd.Evn_id
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
		}*/
	}


		
	/**
	 *	Установка ошибок
	 */
	function setErrorFromImportRegistry($d, $data) 
	{
		// Сохранение загружаемого реестра, точнее его ошибок 
		
		$params = $d;
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
		$params['FLAG'] = $d['FLAG'];
	
		// если задан IDCASE значит идёт разбор из xml, иначе из dbf
		if (!empty($d['IDCASE'])) {
		
			$params['IDCASE'] = $d['IDCASE'];
			if ($data['Registry_id']>0)
			{
				$query = "SELECT TOP 1 RegistryErrorType_id FROM {$this->scheme}.RegistryErrorType with (nolock) WHERE RegistryErrorType_Code = :FLAG";
				$resp = $this->db->query($query, $params);
				if (is_object($resp))
				{
					$ret = $resp->result('array');
					if (is_array($ret) && (count($ret) > 0)) {
					
						$params['FLAG'] = $ret[0]['RegistryErrorType_id'];
						$query = "
							Insert {$this->scheme}.RegistryError (Registry_id, Evn_id, RegistryErrorType_id, LpuSection_id, pmUser_insID, pmUser_updID, RegistryError_insDT, RegistryError_updDT)
							Select 
							rd.Registry_id, rd.Evn_id, :FLAG as RegistryErrorType_id, rd.LpuSection_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT 
							from {$this->scheme}.v_RegistryGroupLink rgl (nolock)
							inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
							where rgl.Registry_pid = :Registry_id  and rd.Evn_id = :IDCASE";
							
							//echo getDebugSql($query, $params);
							//exit;
							
						$result = $this->db->query($query, $params);
						// если выполнилось, возвращаем пустой Error_Msg
						if ($result === true) 
						{
							return array(array('success' => true, 'Error_Msg' => ''));
						}
						else 
						{
							return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
						}
					} else {
						return array(array('success' => false, 'Error_Msg' => 'Код ошибки '.$d['FLAG']. ' не найден в бд'));
					}
				} else {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки '.$d['FLAG']));
				}
			}
			else 
			{
				return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
			}
		
		} else {
			$params['ID'] = $d['ID'];
			/* -- сохранение всех
			foreach($params as $k=>&$v)
			{
				$query .= "@".$key."='".$v."', ";
			}	
			*/
			if ($data['Registry_id']>0)
			{
				$query = "
					Insert {$this->scheme}.RegistryError (Registry_id, Evn_id, RegistryErrorType_id, LpuSection_id, pmUser_insID, pmUser_updID, RegistryError_insDT, RegistryError_updDT)
					Select 
					rd.Registry_id, rd.Evn_id, :FLAG as RegistryErrorType_id, rd.LpuSection_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT 
					from {$this->scheme}.v_RegistryData rd
					where rd.Registry_id = :Registry_id  and rd.Guid = :ID";
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
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
				}
			}
			else 
			{
				return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
			}
		
		}
	}

	/**
	 * Удаление ошибок ТФОМС
	 */
	function deleteRegistryErrorTFOMS($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		}

		$query = "
			delete
				retf with (rowlock)
			from
				{$this->scheme}.RegistryErrorTFOMS retf
				inner join {$this->scheme}.v_RegistryGroupLink rgl (nolock) on rgl.Registry_id = retf.Registry_id
			where
				rgl.Registry_pid = :Registry_id
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id']
		));

		return true;
	}

	/**
	 * Сохранение ошибок ТФОМС
	 */
	function setErrorFromTFOMSImportRegistry($data)
	{
		// Сохранение загружаемого реестра, точнее его ошибок

		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
		$params['S_OSN'] = $data['S_OSN'];
		$params['IDCASE'] = $data['IDCASE'];
		$params['COMMENT'] = $data['COMMENT'];
		$params['RegistryErrorTFOMSType_id'] = 1;
		$params['RegistryErrorClass_id'] = 1;

		if ($data['Registry_id']>0)
		{
			$query = "SELECT TOP 1 RegistryErrorType_id, RegistryErrorType_Descr FROM {$this->scheme}.RegistryErrorType with (nolock) WHERE RegistryErrorType_Code = :S_OSN";
			$resp = $this->db->query($query, $params);
			if (is_object($resp))
			{
				$ret = $resp->result('array');
				if (is_array($ret) && (count($ret) > 0)) {

					$params['S_OSN_ID'] = $ret[0]['RegistryErrorType_id'];
					if (empty($params['COMMENT'])) {
						$params['COMMENT'] = $ret[0]['RegistryErrorType_Descr'];
					}
					if (!empty($params['IDCASE'])) {
						// ошибка на уровне случая
						$query = "
							declare @Err_Msg varchar(400);

							set nocount on;

							begin try
								insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_Comment, RegistryErrorTFOMSType_id, RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
								select
									rd.Registry_id, rd.Evn_id, :S_OSN_ID as RegistryErrorType_id, :S_OSN as RegistryErrorType_Code, '' as RegistryErrorTFOMS_FieldName, :COMMENT as RegistryErrorTFOMS_Comment, :RegistryErrorTFOMSType_id as RegistryErrorTFOMSType_id, :RegistryErrorClass_id as RegistryErrorClass_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryErrorTFOMS_insDT, dbo.tzGetDate() as RegistryErrorTFOMS_updDT
								from {$this->scheme}.v_RegistryGroupLink rgl (nolock)
									inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
									inner join v_Evn E with (nolock) on E.Evn_id = rd.Evn_id
								where rgl.Registry_pid = :Registry_id
									and rd.Evn_id = :IDCASE

								insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, CmpCloseCard_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_Comment, RegistryErrorTFOMSType_id, RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
								select
									rd.Registry_id, rd.Evn_id, :S_OSN_ID as RegistryErrorType_id, :S_OSN as RegistryErrorType_Code, '' as RegistryErrorTFOMS_FieldName, :COMMENT as RegistryErrorTFOMS_Comment, :RegistryErrorTFOMSType_id as RegistryErrorTFOMSType_id, :RegistryErrorClass_id as RegistryErrorClass_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryErrorTFOMS_insDT, dbo.tzGetDate() as RegistryErrorTFOMS_updDT
								from {$this->scheme}.v_RegistryGroupLink rgl (nolock)
									inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
									inner join v_CmpCloseCard CCC with (nolock) on CCC.CmpCloseCard_id = rd.Evn_id
								where rgl.Registry_pid = :Registry_id
									and rd.Evn_id = :IDCASE
							end try

							begin catch
								set @Err_Msg = error_message();
							end catch

							set nocount off;

							select @Err_Msg as Error_Msg;
						";
					}
					else {
						// ошибка на уровне счёта
						$query = "
							declare @Err_Msg varchar(400);

							set nocount on;

							begin try
								insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_Comment, RegistryErrorTFOMSType_id, RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
								values (:Registry_id, :S_OSN_ID, :S_OSN, '', :COMMENT, :RegistryErrorTFOMSType_id, :RegistryErrorClass_id, :pmUser_id, :pmUser_id, dbo.tzGetDate(), dbo.tzGetDate())
							end try

							begin catch
								set @Err_Msg = error_message();
							end catch

							set nocount off;

							select @Err_Msg as Error_Msg;
						";
					}
					$result = $this->db->query($query, $params);

					if ( !is_object($result) ) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
					}

					$res = $result->result('array');

					if ( empty($res[0]['Error_Msg']) ) {
						return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
					}

					// если выполнилось, возвращаем пустой Error_Msg
					return array(array('success' => true, 'Error_Msg' => ''));
				}
				else {
					return array(array('success' => false, 'Error_Msg' => 'Код ошибки '.$data['S_OSN']. ' не найден в бд'));
				}
			}
			else {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки '.$data['S_OSN']));
			}
		}
		else {
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
		}
	}
	
	/**
	 * Получение данных по реестру для печати
	 */
	function getRegistryFields($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		$filter .= " and Registry.Registry_id = :Registry_id";
		$queryParams['Registry_id'] = $data['Registry_id'];

		if ( !isMinZdrav() ) {
			$filter .= " and Registry.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select 
				RTRIM(Registry.Registry_Num) as Registry_Num,
				ISNULL(convert(varchar(10), cast(Registry.Registry_accDate as datetime), 104), '') as Registry_accDate,
				RTRIM(ISNULL(Org.Org_Name, '')) as Lpu_Name,
				ISNULL(Lpu.Lpu_RegNomC, '') as Lpu_RegNomC,
				ISNULL(Lpu.Lpu_RegNomN, '') as Lpu_RegNomN,
				RTRIM(LpuAddr.Address_Address) as Lpu_Address,
				RTRIM(Org.Org_Phone) as Lpu_Phone,
				ORS.OrgRSchet_RSchet as Lpu_Account,
				OB.OrgBank_Name as LpuBank_Name,
				OB.OrgBank_BIK as LpuBank_BIK,
				Org.Org_INN as Lpu_INN,
				Org.Org_KPP as Lpu_KPP,
				Okved.Okved_Code as Lpu_OKVED,
				Org.Org_OKPO as Lpu_OKPO,
				month(Registry.Registry_begDate) as Registry_Month,
				year(Registry.Registry_begDate) as Registry_Year,
				cast(isnull(Registry.Registry_Sum, 0.00) as float) as Registry_Sum,
				OHDirector.OrgHeadPerson_Fio as Lpu_Director,
				OHGlavBuh.OrgHeadPerson_Fio as Lpu_GlavBuh,
				RT.RegistryType_id,
				RT.RegistryType_Code
			from {$this->scheme}.v_Registry Registry with (NOLOCK)
				inner join Lpu on Lpu.Lpu_id = Registry.Lpu_id
				inner join Org on Org.Org_id = Lpu.Org_id
				inner join RegistryType RT on RT.RegistryType_id = Registry.RegistryType_id
				left join Okved on Okved.Okved_id = Org.Okved_id
				left join [Address] LpuAddr on LpuAddr.Address_id = Org.UAddress_id
				left join OrgRSchet ORS on Registry.OrgRSchet_id = ORS.OrgRSchet_id
				left join v_OrgBank OB on OB.OrgBank_id = ORS.OrgBank_id
				outer apply (
					select 
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH
						inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where 
						OH.Lpu_id = Lpu.Lpu_id
						and OH.OrgHeadPost_id = 1
				) as OHDirector
				outer apply (
					select 
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH
						inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where 
						OH.Lpu_id = Lpu.Lpu_id
						and OH.OrgHeadPost_id = 2
				) as OHGlavBuh
			where " . $filter . "
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response[0];
		}
		else {
			return false;
		}
	}


	/**
	 * Сохранение перс. данных
	 */
	function savePersonData($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;

			exec {$this->scheme}.p_RegErrorPerson_ins
				@RegErrorPerson_id = @Res output,
				@Registry_id = :Registry_id,
				@Person_id = :Person_id,
				@OrgSmo_Code = :OrgSmo_Code,
				@Polis_Num = :Polis_Num,
				@SocStatus_Code = :SocStatus_Code,
				@Person_FirName = :Person_FirName,
				@Person_SecName = :Person_SecName,
				@Person_BirthDay = :Person_BirthDay,
				@Sex_id = :Sex_id,
				@Person_Snils = :Person_Snils,
				@Person_IsFlag = :Person_IsFlag,
				@Evn_setDT = :Evn_setDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as RegErrorPerson_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Registry_id' => (!empty($data['Registry_id']) && is_numeric($data['Registry_id']) && $data['Registry_id'] > 0 ? $data['Registry_id'] : NULL),
			'Person_id' => (isset($data['ID']) && is_numeric($data['ID']) && $data['ID'] > 0 ? $data['ID'] : NULL),
			'OrgSmo_Code' => (isset($data['SMO']) && is_numeric($data['SMO']) && $data['SMO'] > 0 ? $data['SMO'] : NULL),
			'Polis_Num' => (isset($data['POL_NUM']) && strlen($data['POL_NUM']) > 0 ? $data['POL_NUM'] : NULL),
			'SocStatus_Code' => (isset($data['ID_STATUS']) && is_numeric($data['ID_STATUS']) && $data['ID_STATUS'] > 0 ? $data['ID_STATUS'] : NULL),
			'Person_FirName' => (!empty($data['NAM']) ? $data['NAM'] : NULL),
			'Person_SecName' => (!empty($data['FNAM']) ? $data['FNAM'] : NULL),
			'Person_BirthDay' => (!empty($data['DATE_BORN']) ? $data['DATE_BORN'] : NULL),
			'Sex_id' => (!empty($data['SEX']) ? $data['SEX'] : NULL),
			'Person_Snils' => (!empty($data['SNILS']) ? $data['SNILS'] : NULL),
			'Person_IsFlag' => (in_array($data['FLAG'], array(0, 1)) ? $data['FLAG'] : NULL),
			'Evn_setDT' => (!empty($data['DATE_POS']) ? $data['DATE_POS'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);
		/*
		$f = fopen('tmp.txt', 'a');
		fputs($f, getDebugSQL($query, $queryParams));
		fclose($f);
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
	 * Обновление чего-то
	 */
	function updatePersonErrorData($data) {
		$query = "
			declare @Res bigint;

			exec {$this->scheme}.xp_RegistryErrorPerson_process
				@Registry_id = :Registry_id,
				@CountUpd = @Res output,
				@pmUser_id = :pmUser_id

			select @Res as CountUpd;
		";
		$queryParams = array('Registry_id' => $data['Registry_id'], 'pmUser_id' => $data['pmUser_id']);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Загрузка списка статусов реестра
	 */
	function loadRegistryStatusNode($data)
	{
		if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
			$result = array(
				array('RegistryStatus_id' => 11, 'RegistryStatus_Name' => 'В очереди'),
				array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
				array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
				array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Проверенные МЗ'),
				array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
				array('RegistryStatus_id' => 12, 'RegistryStatus_Name' => 'Удаленные')
			);
		} else {
			$result = array(
				array('RegistryStatus_id' => 11, 'RegistryStatus_Name' => 'В очереди'),
				array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
				array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
				array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
				//array('RegistryStatus_id' => 12, 'RegistryStatus_Name' => 'Удаленные')
			);
		}
		return $result;
	}
	
	/**
	 *	Проверка вхождения случая в реестр
	 */
	function checkEvnInRegistry($data, $action = 'delete')
	{
		$filter = "(1=1)";

		if(isset($data['EvnPL_id'])) {
			$filter .= " and Evn_rid = :EvnPL_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnPS_id'])) {
			$filter .= " and Evn_rid = :EvnPS_id";
			$data['RegistryType_id'] = 1;
		}
		if(isset($data['EvnPLStom_id'])) {
			$filter .= " and Evn_rid = :EvnPLStom_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnVizitPL_id'])) {
			$filter .= " and Evn_id = :EvnVizitPL_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnSection_id'])) {
			$filter .= " and Evn_id = :EvnSection_id";
			$data['RegistryType_id'] = 1;
		}
		if(isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and Evn_id = :EvnVizitPLStom_id";
			$data['RegistryType_id'] = 2;
		}

		if(isset($data['EvnPLDispDop13_id'])) {
			$filter .= " and Evn_id = :EvnPLDispDop13_id";
			$data['RegistryType_id'] = 7;
		}

		if(isset($data['EvnPLDispProf_id'])) {
			$filter .= " and Evn_id = :EvnPLDispProf_id";
			$data['RegistryType_id'] = 11;
		}

		if(isset($data['EvnPLDispOrp_id'])) {
			$filter .= " and Evn_id = :EvnPLDispOrp_id";
			$data['RegistryType_id'] = 9;
		}

		if(isset($data['EvnPLDispTeenInspection_id'])) {
			$filter .= " and Evn_id = :EvnPLDispTeenInspection_id";
			$data['RegistryType_id'] = 12;
		}

		if (empty($filter)) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		//#51767
		if (in_array($data['RegistryType_id'], array(7,9,11,12))) {
			if ($action == 'edit') {
				return false;
			}

			$query = "
				select top 1 DC.DispClass_Code
				from v_Evn E with(nolock)
				inner join v_EvnPLDisp EPLD with(nolock) on EPLD.EvnPLDisp_id = E.Evn_id
				inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
				where {$filter}
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return $this->createError('', 'Ошибка при определении класса диспансеризации');
			}

			$resp = $result->result('array');

			if (is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4,8,11,12))) {
				if (isset($data['EvnPLDispTeenInspection_id'])) {
					$data['Evn_id'] = $data['EvnPLDispTeenInspection_id'];
				} else {
					$data['Evn_id'] = $data['EvnPLDispOrp_id'];
				}


				$query = "
					select
						RD.Evn_id,
						R.Registry_Num,
						RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
					from
						{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_EvnVizitDisp EVD with(nolock) on EVD.EvnVizitDisp_id = RD.Evn_id
					where
						EVD.EvnVizitDisp_pid = :Evn_id
						and R.RegistryStatus_id = 4 and isnull(RD.Paid_id,1) = 2
				";

				$resp = $this->queryResult($query, $data);
				if (!$this->isSuccessful($resp)) {
					return $this->createError('', 'Ошибка БД!');
				}

				$actiontxt = 'Удаление';
				switch($action) {
					case 'delete':
						$actiontxt = 'Удаление';
						break;
					case 'edit':
						$actiontxt = 'Редактирование';
						break;
				}

				if( count($resp) > 0 ) {
					return array(
						array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
					);
				} else {
					return false;
				}
			}
		}

		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.v_RegistryData RD with (nolock)
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				{$filter}
				and (ISNULL(RD.Paid_id, 1) = 2 or R.RegistryStatus_id != 4)
				and R.Lpu_id = :Lpu_id
			
			union
			
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.RegistryDataTemp RD with (nolock) -- в процессе формирования
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
			where
				{$filter}
				and R.Lpu_id = :Lpu_id
		";
		//echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		
		$actiontxt = 'Удаление';
		switch($action) {
			case 'delete':
				$actiontxt = 'Удаление';
			break;
			case 'edit':
				$actiontxt = 'Редактирование';
			break;
		}
		
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
			);
		} else {
			return false;
		}
	}

	/**
	 *	Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	public function loadRegistryTypeNode($data) {
		if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
			return array(
				$this->_registryTypeList[1],
				$this->_registryTypeList[2],
				$this->_registryTypeList[6],
				$this->_registryTypeList[14],
				$this->_registryTypeList[15]
			);
		} else {
			return $this->_registryTypeList;
		}
	}

	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry($data)
	{
		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink with (ROWLOCK)
			where Registry_pid = :Registry_id
		";
		$this->db->query($query, array(
			'Registry_id' => $data['id']
		));
		
		// 2. удаляем сам реестр
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$this->scheme}.p_Registry_del
				@Registry_id = :Registry_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		$result = $this->db->query($query, array(
			 'Registry_id' => $data['id']
			,'pmUser_id' => $data['pmUser_id']
		));
		
		if (is_object($result)) 
		{
			return $result->result('array');
		}
		
		return false;
	}

	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry($data)
	{
		// проверка уникальности номера реестра по лпу в одном году
		$query = "
			select top 1
				Registry_id
			from
				{$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and Registry_Num = :Registry_Num
				and year(Registry_accDate) = year(:Registry_accDate)
				and (Registry_id <> :Registry_id OR :Registry_id IS NULL)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				return array('Error_Msg' => 'Номер счета не должен повторяться в году');
			}
		}
		
		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
			$data['Registry_IsZNO'] = $this->getFirstResultFromQuery("select top 1 Registry_IsZNO from {$this->scheme}.v_Registry with (nolock) where Registry_id = :Registry_id", $data);
		}
		$query = "
			declare
				@Error_Code bigint,
				@KatNasel_Code bigint = (select top 1 KatNasel_Code from v_KatNasel (nolock) where KatNasel_id = :KatNasel_id),
				@Registry_FileNum int,
				@Error_Message varchar(4000),
				@Registry_id bigint = :Registry_id,
				@curdate datetime = dbo.tzGetDate();

			if ( @Registry_id is not null )
				set @Registry_FileNum = (select top 1 Registry_FileNum from {$this->scheme}.v_Registry with (nolock) where Registry_id = @Registry_id);

			exec {$this->scheme}.{$proc}
				@Registry_id = @Registry_id output,
				@RegistryType_id = 13,
				@RegistryStatus_id = 1,
				@Registry_Sum = NULL,
				@Registry_IsActive = 2,
				@Registry_Num = :Registry_Num,
				@Registry_accDate = :Registry_accDate,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@KatNasel_id = :KatNasel_id,
				@RegistryGroupType_id = :RegistryGroupType_id,
				@Registry_FileNum = @Registry_FileNum,
				@Lpu_id = :Lpu_id,
				@Registry_IsRepeated = :Registry_IsRepeated,
				@Registry_IsZNO = :Registry_IsZNO,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Registry_id as Registry_id, @KatNasel_Code as KatNasel_Code, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) 
		{
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
				// 2. удаляем все связи
				$query = "
					delete {$this->scheme}.RegistryGroupLink with (ROWLOCK)
					where Registry_pid = :Registry_id
				";
				$this->db->query($query, array(
					'Registry_id' => $resp[0]['Registry_id']
				));
				
				$registrytypefilter = "";
				switch ($data['RegistryGroupType_id']) {
					case 1:
						$registrytypefilter = " and R.RegistryType_id in (1, 2, 6, 15) and ISNULL(R.Registry_IsZNO, 1) = ISNULL(:Registry_IsZNO, 1)";
						break;
					case 2:
						$registrytypefilter = " and R.RegistryType_id = 14";
						break;
					case 3:
						$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1";
						break;
					case 4:
						$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 2";
						break;
					case 5:
						$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 3";
						break;
					case 6:
						$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 7";
						break;
					case 7:
						$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 6";
						break;
					case 8:
						$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 9";
						break;
					case 9:
						$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 10";
						break;
					case 10:
						$registrytypefilter = " and R.RegistryType_id IN (11)";
						break;
				}
				
				// 3. выполняем поиск реестров которые войдут в объединённый
				$query = "
					declare @PayType_id bigint = (Select top 1 PayType_id from v_PayType pt (nolock) where pt.PayType_SysNick = 'oms');
					  
					select
						R.Registry_id
					from
						{$this->scheme}.v_Registry R (nolock)
					where
						R.RegistryType_id <> 13
						and R.RegistryStatus_id = 2 -- к оплате
						and R.KatNasel_id = :KatNasel_id
						and R.Lpu_id = :Lpu_id
						and R.Registry_IsRepeated = :Registry_IsRepeated
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and ISNULL(r.PayType_id, @PayType_id) = @PayType_id
						and not exists(select top 1 RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink (nolock) where Registry_id = R.Registry_id)
						{$registrytypefilter}
				";
				$result_reg = $this->db->query($query, array(
					'KatNasel_id' => $data['KatNasel_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Registry_IsRepeated' => $data['Registry_IsRepeated'],
					'Registry_IsZNO' => $data['Registry_IsZNO'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate']
				));
				
				if (is_object($result_reg)) 
				{
					$resp_reg = $result_reg->result('array');
					// 4. сохраняем новые связи
					foreach($resp_reg as $one_reg) {
						$query = "
							declare
								@Error_Code bigint,
								@Error_Message varchar(4000),
								@RegistryGroupLink_id bigint = null;
							exec {$this->scheme}.p_RegistryGroupLink_ins
								@RegistryGroupLink_id = @RegistryGroupLink_id output,
								@Registry_pid = :Registry_pid,
								@Registry_id = :Registry_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							select @RegistryGroupLink_id as RegistryGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";
						
						$this->db->query($query, array(
							'Registry_pid' => $resp[0]['Registry_id'],
							'Registry_id' => $one_reg['Registry_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				// пишем информацию о формировании реестра в историю
				$this->dumpRegistryInformation(array(
					'Registry_id' => $resp[0]['Registry_id']
				), 1);
			}
			
			return $resp;
		}
		
		return false;
	}

	/**
	 * Получение номера объединённого реестра
	 */
	function getUnionRegistryNumber($data)
	{
		$query = "
			select
				ISNULL(MAX(cast(Registry_Num as bigint)),0) + 1 as Registry_Num
			from
				{$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and ISNUMERIC(Registry_Num) = 1
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_Num'])) {
				return $resp[0]['Registry_Num'];
			}
		}
		
		return 1;
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm($data)
	{
		$query = "
			select
				R.Registry_id,
				R.Registry_Num,
				convert(varchar,R.Registry_accDate,104) as Registry_accDate,
				convert(varchar,R.Registry_begDate,104) as Registry_begDate,
				convert(varchar,R.Registry_endDate,104) as Registry_endDate,
				R.KatNasel_id,
				R.RegistryGroupType_id,
				R.Lpu_id,
				R.Registry_IsRepeated,
				R.Registry_IsZNO
			from
				{$this->scheme}.v_Registry R (nolock)
			where
				R.Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) 
		{
			return $result->result('array');
		}
		
		return false;
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid($data)
	{
		$query = "
			Select 
				-- select
				R.Registry_id,
				R.Registry_Num,
				convert(varchar,R.Registry_accDate,104) as Registry_accDate,
				convert(varchar,R.Registry_begDate,104) as Registry_begDate,
				convert(varchar,R.Registry_endDate,104) as Registry_endDate,
				R.KatNasel_id,
				KN.KatNasel_Name,
				KN.KatNasel_SysNick,
				RGT.RegistryGroupType_Name,
				RGT.RegistryGroupType_id,
				cast(RSum.Registry_Sum as decimal(20, 2)) as Registry_Sum,
				RSum.Registry_SumPaid as Registry_SumPaid,
				RCS.RegistryCheckStatus_id,
				RCS.RegistryCheckStatus_SysNick,
				RCS.RegistryCheckStatus_Name
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry R (nolock) -- объединённый реестр
				cross apply (
					select
						SUM(t1.Registry_Sum) as Registry_Sum,
						SUM(t1.Registry_SumPaid) as Registry_SumPaid
					from {$this->scheme}.Registry t1 with (nolock)
						inner join {$this->scheme}.v_RegistryGroupLink t2 with (nolock) on t2.Registry_id = t1.Registry_id
					where t2.Registry_pid = R.Registry_id
				) RSum
				left join v_RegistryGroupType RGT (nolock) on RGT.RegistryGroupType_id = R.RegistryGroupType_id
				left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				outer apply (
					select top 1 t1.Registry_id
					from {$this->scheme}.v_Registry t1 with (nolock)
						inner join {$this->scheme}.v_RegistryGroupLink t2 with (nolock) on t2.Registry_id = t1.Registry_id
					where t2.Registry_pid = R.Registry_id
						and t1.RegistryType_id = 2
				) Polka
				outer apply (
					select top 1 t1.Registry_id
					from {$this->scheme}.v_Registry t1 with (nolock)
						inner join {$this->scheme}.v_RegistryGroupLink t2 with (nolock) on t2.Registry_id = t1.Registry_id
					where t2.Registry_pid = R.Registry_id
						and t1.RegistryType_id = 6
				) SMP
				left join fed.PasportMO PasportMO with (nolock) on PasportMO.Lpu_id = R.Lpu_id
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

		//echo getDebugSql($query, $data); exit;

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
	function loadUnionRegistryChildGrid($data)
	{
		$query = "
		Select 
			-- select
			R.Registry_id,
			R.Registry_Num,
			convert(varchar,R.Registry_accDate,104) as Registry_accDate,
			convert(varchar,R.Registry_begDate,104) as Registry_begDate,
			convert(varchar,R.Registry_endDate,104) as Registry_endDate,
			KN.KatNasel_Name,
			RT.RegistryType_Name,
			ISNULL(R.Registry_Sum, 0.00) as Registry_Sum,
			ISNULL(R.Registry_SumPaid, 0.00) as Registry_SumPaid,
			PT.PayType_Name,
			convert(varchar,R.Registry_updDT,104) as Registry_updDate,
			R.Registry_IsRepeated,
			R.Registry_rid
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryGroupLink RGL (nolock)
			inner join {$this->scheme}.Registry R (nolock) on R.Registry_id = RGL.Registry_id -- обычный реестр
			left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
			left join v_RegistryType RT (nolock) on RT.RegistryType_id = R.RegistryType_id
			left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
			-- end from
		where
			-- where
			RGL.Registry_pid = :Registry_pid
			-- end where
		order by
			-- order by
			R.Registry_id
			-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
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
	 * Получение списка типов реестров, входящих в объединенный реестр
	 */
	function getUnionRegistryTypes($Registry_pid = 0) {
		$query = "
			select distinct r.RegistryType_id
			from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
				inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_pid
		";
		$result = $this->db->query($query, array('Registry_pid' => $Registry_pid));

		if ( !is_object($result) ) {
			return false;
		}

		$registryTypes = array();
		$resp = $result->result('array');

		foreach ( $resp as $rec ) {
			$registryTypes[] = $rec['RegistryType_id'];
		}

		return $registryTypes;
	}

	/**
	 * Простановка статуса реестра
	 */
	function setRegistryCheckStatus($data) {
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@curdate datetime = dbo.tzGetDate(),
				@rcs_id bigint = null;

			" . (!empty($data['RegistryCheckStatus_SysNick']) ? "
			set @rcs_id = (select top 1 RegistryCheckStatus_id from v_RegistryCheckStatus with (nolock) where RegistryCheckStatus_SysNick = :RegistryCheckStatus_SysNick);
			" : "") . "

			exec {$this->scheme}.p_Registry_setRegistryCheckStatus
				@Registry_id = :Registry_id,
				@RegistryCheckStatus_id = @rcs_id,
				@Registry_RegistryCheckStatusDate = @curdate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при изменении статуса реестра');
		}
	}

	/**
	 * Получение списка случаев в реестре
	 */
	function loadRegistryData($data) {
		if ( empty($data['Registry_id']) || empty($data['RegistryType_id']) ) {
			return false;
		}
		else if ( (isset($data['start']) && (isset($data['limit'])))&&(!(($data['start'] >= 0) && ($data['limit'] >= 0))) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);
		if ( $this->RegistryType_id == 1 ) {
			$this->MaxEvnField = 'MaxEvn_id';
		}

		// В зависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);

		$fieldsList = array();
		$filterList = array("RD.Registry_id = :Registry_id");
		$joinList = array();

		// Общие фильтры
		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( !empty($data['Polis_Num']) ) {
			$filterList[] = "RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		// Фильтры в зависимости от типа реестра
		if ( $this->RegistryType_id == 6 ) {
			if ( !empty($data['Person_SurName']) ) {
				$filterList[] = "isnull(ccc.Person_SurName, ps.Person_SurName) like :Person_SurName";
				$params['Person_SurName'] = $data['Person_SurName'] . "%";
			}

			if ( !empty($data['Person_FirName']) ) {
				$filterList[] = "isnull(ccc.Person_FirName, ps.Person_FirName) like :Person_FirName";
				$params['Person_FirName'] = $data['Person_FirName'] . "%";
			}

			if ( !empty($data['Person_SecName']) ) {
				$filterList[] = "isnull(ccc.Person_SecName, ps.Person_SecName) like :Person_SecName";
				$params['Person_SecName'] = $data['Person_SecName'] . "%";
			}

			if ( !empty($data['LpuBuilding_id']) ) {
				$filterList[] = "isnull(msf.LpuBuilding_id, cclc.LpuBuilding_id) = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}

			if ( !empty($data['MedPersonal_id']) ) {
				$filterList[] = "cclc.MedPersonal_id = :MedPersonal_id";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}
		}
		else {
			if ( !empty($data['Person_SurName']) ) {
				$filterList[] = "RD.Person_SurName like :Person_SurName ";
				$params['Person_SurName'] = $data['Person_SurName'] . "%";
			}

			if ( !empty($data['Person_FirName']) ) {
				$filterList[] = "RD.Person_FirName like :Person_FirName ";
				$params['Person_FirName'] = $data['Person_FirName'] . "%";
			}

			if ( !empty($data['Person_SecName']) ) {
				$filterList[] = "RD.Person_SecName like :Person_SecName ";
				$params['Person_SecName'] = $data['Person_SecName'] . "%";
			}

			if ( !empty($data['LpuBuilding_id']) ) {
				$filterList[] = "LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}

			if ( !empty($data['MedPersonal_id']) ) {
				$filterList[] = "RD.MedPersonal_id = :MedPersonal_id";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}
		}

		$select_uet = "RD.RegistryData_KdFact as RegistryData_Uet";

		// Определение УЕТ (для поликлиники)
		if ( in_array($this->RegistryType_id, array(2, 16)) ) {
			$select_uet = "
				case when (RD.LpuSectionProfile_Code in ('529', '530', '629', '630', '829', '830') or Usluga.UslugaComplex_id is not null)
				then EVPL.EvnVizitPL_UetOMS else 1
				end as RegistryData_Uet,
				EVPL.EvnVizitPL_Count
			";
			$joinList[] = "
				left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id
				outer apply (
					select top 1
						UslugaComplex.UslugaComplex_id,
						UslugaComplex.UslugaComplex_Code
					from
						v_EvnUsluga EvnUsluga with (nolock)
						left join UslugaComplex on UslugaComplex.UslugaComplex_id = EvnUsluga.UslugaComplex_id
					where
						EvnUsluga.EvnUsluga_pid = RD.Evn_id
						and LEFT(UslugaComplex.UslugaComplex_Code,4) = 'A.07'
						and rd.LpuSectionProfile_Code in ('577','677','877')
					order by EvnUsluga_id
				) as Usluga
			";
		}

		$fieldsList[] = $select_uet;

		if ( in_array($this->RegistryType_id, array(7, 12)) ) {
			$joinList[] = "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldsList[] = "epd.DispClass_id";
		}

		if ( $this->RegistryType_id == 6 ) {
			$query = "
				select
					-- select
					cast(RD.Evn_id as varchar) + '_' + cast(ISNULL(RD.CmpCloseCard_sid, 0) as varchar) as Evn_ident,
					RD.Evn_id,
					null as Evn_rid,
					RD.CmpCloseCard_sid as MaxEvn_id,
					111 as EvnClass_id,
					RD.Registry_id,
					RD.RegistryType_id,
					RD.Person_id,
					PersonEvn.Server_id,
					PersonEvn.PersonEvn_id,
					RD.RegistryData_deleted,
					RTrim(RD.NumCard) as EvnPL_NumCard,
					case
						when ps.Person_id is not null then rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
						else rtrim(isnull(ccc.Person_SurName,'')) + ' ' + rtrim(isnull(ccc.Person_FirName,'')) + ' ' + rtrim(isnull(ccc.Person_SecName, ''))
					end as Person_FIO,
					convert(varchar(10), case when ps.Person_id is not null then ps.Person_BirthDay else ccc.Person_BirthDay end, 104) as Person_BirthDay,
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					convert(varchar(10), RD.Evn_setDate, 104) as EvnVizitPL_setDate,
					convert(varchar(10), RD.Evn_disDate, 104) as Evn_disDate,
					0 as RegistryData_Sum_R,
					RD.RegistryData_Tariff RegistryData_Tariff,
					RD.RegistryData_KdPay as RegistryData_KdPay,
					RD.RegistryData_KdPlan as RegistryData_KdPlan,
					RD.RegistryData_ItogSum as RegistryData_ItogSum,
					RegistryError.Err_Count as Err_Count,
					RegistryErrorTFOMS.ErrTfoms_Count as ErrTfoms_Count,
					case when ISNULL(cclc.CmpCloseCard_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord,
					ISNULL(msf.Person_Fio, mp.Person_Fio) as MedPersonal_Fio,
					lb.LpuBuilding_Name,
					ls.LpuSection_id,
					ls.LpuSection_Name as LpuSection_name,
					U.UslugaComplex_Code as Usluga_Code,
					D.Diag_Code,
					RHDCR.RegistryHealDepResType_id
					" . (count($fieldsList) > 0 ? "," . implode(",", $fieldsList) : "")  . "
					-- end select
				from
					-- from
					{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
					left join v_CmpCloseCard cclc with (nolock) on cclc.CmpCloseCard_id = RD.Evn_id
					left join v_CmpCallCard ccc with (nolock) on ccc.CmpCallCard_id = cclc.CmpCallCard_id
					left join v_PersonState ps with (nolock) on ps.Person_id = ccc.Person_id
					left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = cclc.MedStaffFact_id
					outer apply (
						select top 1 Person_Fio
						from v_MedPersonal with (nolock)
						where MedPersonal_id = cclc.MedPersonal_id
					) mp
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = isnull(msf.LpuBuilding_id, cclc.LpuBuilding_id)
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = isnull(msf.LpuSection_id, cclc.LpuSection_id)
					left join v_UslugaComplex U with (nolock) on RD.UslugaComplex_id = U.UslugaComplex_id
					left join v_Diag D with (nolock) on RD.Diag_id =  D.Diag_id
					left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
					" . (count($joinList) > 0 ? implode(" ", $joinList) : "")  . "
					outer apply (
						select count(RE.Evn_id) as Err_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (nolock)
						where RE.Evn_id = RD.Evn_id
							and RE.Registry_id = RD.Registry_id
					) RegistryError
					outer apply (
						select count(RET.RegistryErrorTFOMS_id) as ErrTfoms_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET with (nolock)
						where RET.CmpCloseCard_id = RD.Evn_id
							and RET.Registry_id = RD.Registry_id
					) RegistryErrorTFOMS
					outer apply (
						select top 1 PersonEvn_id, Server_id
						from v_PersonEvn PE with (NOLOCK)
						where ccc.Person_id = PE.Person_id
							and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
					) PersonEvn
				-- end from
				where
					-- where
					" . implode(" and ", $filterList) . "
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}
		else {
			if ( $this->RegistryType_id == 1 || $this->RegistryType_id == 14 ) {
				$fieldsList[] = 'm.MesOldUslugaComplexLink_Number as Usluga_Code';
				$joinList[] = '
					left join v_EvnSection es (nolock) on ES.EvnSection_id = RD.Evn_id
					outer apply (
						select top 1 MesOldUslugaComplexLink_Number
						from r60.v_MesOldUslugaComplexLink with (nolock)
						where MesOldUslugaComplex_id = ES.MesOldUslugaComplex_id
							and (MesOldUslugaComplexLink_begDT is null or MesOldUslugaComplexLink_begDT <= ES.EvnSection_disDate)
							and (MesOldUslugaComplexLink_endDT is null or MesOldUslugaComplexLink_endDT >= ES.EvnSection_disDate)
					) m
				';
			}
			else {
				$fieldsList[] = 'U.UslugaComplex_Code as Usluga_Code';
				$joinList[] = 'left join v_UslugaComplex U with (nolock) on RD.UslugaComplex_id = U.UslugaComplex_id';
			}

			$query = "
				select
					-- select
					RD.Evn_id as Evn_ident,
					RD.Evn_id,
					RD.Evn_rid,
					RD.{$this->MaxEvnField} as MaxEvn_id,
					RD.EvnClass_id,
					RD.Registry_id,
					RD.RegistryType_id,
					RD.Person_id,
					RD.Server_id,
					PersonEvn.PersonEvn_id,
					0 as RegistryData_Sum_R,
					RD.RegistryData_deleted,
					RTrim(RD.NumCard) as EvnPL_NumCard,
					RTrim(RD.Person_FIO) as Person_FIO,
					RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					RD.LpuSection_id,
					RTrim(RD.LpuSection_name) as LpuSection_name,
					RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
					RTrim(IsNull(convert(varchar,cast(RD.Evn_setDate as datetime),104),'')) as EvnVizitPL_setDate,
					RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
					RD.RegistryData_Tariff RegistryData_Tariff,
					RD.RegistryData_KdPay as RegistryData_KdPay,
					RD.RegistryData_KdPlan as RegistryData_KdPlan,
					RD.RegistryData_ItogSum as RegistryData_ItogSum,
					RegistryError.Err_Count as Err_Count,
					RegistryErrorTFOMS.ErrTfoms_Count as ErrTfoms_Count,
					case when ISNULL(e.Evn_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord,
					D.Diag_Code,
					case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as Paid,
					LB.LpuBuilding_Name,
					RHDCR.RegistryHealDepResType_id
					" . (count($fieldsList) > 0 ? "," . implode(",", $fieldsList) : "")  . "
					-- end select
				from
					-- from
					{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
					left join v_Evn e (nolock) on e.Evn_id = rd.Evn_id
					" . (count($joinList) > 0 ? implode(" ", $joinList) : "")  . "
					left join v_Diag D with (nolock) on RD.Diag_id =  D.Diag_id
					left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
					left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
					outer apply (
						select count(*) as Err_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK)
						where RD.Evn_id = RE.Evn_id
							and RD.Registry_id = RE.Registry_id
					) RegistryError
					outer apply (
						select count(*) as ErrTfoms_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET with (NOLOCK)
						where RD.Evn_id = RET.Evn_id
							and RD.Registry_id = RET.Registry_id
					) RegistryErrorTFOMS
					outer apply (
						select top 1 PersonEvn_id
						from v_PersonEvn PE with (NOLOCK)
						where RD.Person_id = PE.Person_id
							and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
					) PersonEvn
				-- end from
				where
					-- where
					" . implode(" and ", $filterList) . "
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}

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
	 * Данные для комбобокса "Первичный реестр"
	 */
	function getRegistryPrimaryCombo($data) {
		$params = array();
		$filter = "";

		$params['RegistryType_id'] = $data['RegistryType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		if (isset($data['Registry_begDate']) && !empty($data['Registry_begDate'])) {
			$filter .= " and R.Registry_begDate = :Registry_begDate";
			$params['Registry_begDate'] = $data['Registry_begDate'];
		}

		if (isset($data['Registry_endDate']) && !empty($data['Registry_endDate'])) {
			$filter .= " and R.Registry_endDate = :Registry_endDate";
			$params['Registry_endDate'] = $data['Registry_endDate'];
		}

		$query = "
			SELECT 
				R.Registry_id,
				CONVERT(VARCHAR(10), R.Registry_begDate, 104) as Registry_begDate,
				CONVERT(VARCHAR(10), R.Registry_endDate, 104) as Registry_endDate,
				('№' +  CAST(R.Registry_Num as varchar) + ' от ' + CONVERT(VARCHAR(10), R.Registry_accDate, 104)) as displayField
			FROM
				{$this->scheme}.v_Registry R with (nolock)
			WHERE
				DATEDIFF(d, R.Registry_accDate, GETDATE()) < 366
				and R.RegistryStatus_id = 4
				and R.RegistryType_id = :RegistryType_id
				and R.Lpu_id = :Lpu_id
				and EXISTS(
					select TOP 1 1
					from {$this->scheme}.v_RegistryErrorTFOMS RET with (NOLOCK)
					inner join {$this->scheme}.v_RegistryErrorType REC with (NOLOCK) on RET.RegistryErrorType_id=REC.RegistryErrorType_id
					where R.Registry_id = RET.Registry_id
						and REC.RegistryErrorClass_id = 1
				)
				{$filter}";

		if (isset($data['Registry_id']) && !empty($data['Registry_id'])) {
			$params['Registry_id'] = $data['Registry_id'];
			$query.= "
			UNION

			SELECT 
				R.Registry_id,
				CONVERT(VARCHAR(10), R.Registry_begDate, 104) as Registry_begDate,
				CONVERT(VARCHAR(10), R.Registry_endDate, 104) as Registry_endDate,
				('№' +  CAST(R.Registry_Num as varchar) + ' от ' + CONVERT(VARCHAR(10), R.Registry_accDate, 104)) as displayField
			FROM
				{$this->scheme}.v_Registry R with (nolock)
			WHERE
				R.Registry_id=:Registry_id
			";
		}

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка наличия посещения в реестре (нельзя убирать в очередь такие ТАП)
	 * @task https://redmine.swan.perm.ru/issues/93958
	 * @task https://redmine.swan.perm.ru/issues/94355
	 */
	function getRegistryIdForEvnVizit($data) {
		$dbreg = $this->load->database('registry', true);

		$query = "
			select top 1
				Reg.Registry_id
			from
				{$this->scheme}.v_RegistryData RD with(nolock)
				inner join {$this->scheme}.v_Registry Reg with(nolock) on RD.Registry_id = Reg.Registry_id
			where
				RD.Evn_id = :Evn_id
				and (Reg.RegistryStatus_id!=4 or (Reg.RegistryStatus_id=4 and RD.Paid_id = 2))
		";
		$result = $dbreg->query($query, array(
			'Evn_id' => $data['Evn_id']
		));
		if (is_object($result)) {
			$rresp = $result->result('array');
			if (!empty($rresp[0]['Registry_id'])) {
				return $rresp[0]['Registry_id'];
			}
		} else {
			throw new Exception('Ошибка проверки оплаченности посещений.', 400);
		}

		return null;
	}
	
	/**
	 * Возвращает список настроек ФЛК
	 */
	public function loadRegistryEntiesSettings($data)
	{
		if(!$data['RegistryGroupType_id'] && !$data['RegistryType_id']) return false;
		
		if( $data['RegistryType_id'] == 13 && isset($data['RegistryGroupType_id']) ){
			$where = ' AND RegistryGroupType_id = '.$data['RegistryGroupType_id'];
		}else{
			$where = ' AND RegistryType_id = '.$data['RegistryType_id'];
		}
		
		$params = array();
		$query = "
			SELECT top 1
				FLKSettings_id
				,RegistryType_id
				,RegistryGroupType_id
				,FLKSettings_EvnData
				,FLKSettings_PersonData
			FROM v_FLKSettings
			WHERE 
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then '2030-01-01'
						else FLKSettings_endDate
					end
				AND FLKSettings_EvnData LIKE '%pskov%'
		".$where;
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
	 *  ФЛК контроль 
	 */
	function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
    {
		if( !file_exists($xsd_tpl) || !$xml_data) return false;
		
		libxml_use_internal_errors(true);  
		$xml = new DOMDocument();
	
		if($type == 'file'){
			$xml->load($xml_data); 
		}
		elseif($type == 'string'){
			$xml->loadXML($xml_data);   
		}
	
		if (!@$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();
	
			file_put_contents($output_file_name, $res_errors);
			return false;
		}
		else{
			return true;
		}
	}
	
	/**
	* ФЛК контроль
	* Метод для формирования листа ошибок при сверке xml по шаблону xsd
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
	 * Устанавливает стартовое значение $this->_IDCASE
	 */
	public function setIDCASE($value) {
		$this->_IDCASE = $value;
		return true;
	}

	/**
	 *	Список случаев по пациентам без документов ОМС
	 */
	public function loadRegistryNoPolis($data) {
		$this->setRegistryParamsByType($data);

		if ( $data['Registry_id'] <= 0 ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$params = array('Registry_id' => $data['Registry_id']);

		if ( $this->RegistryType_id == 6 ) {
			$evn_join = "";
			$set_date_time = " null as Evn_setDT";
		}
		else {
			$evn_join = " left join v_Evn Evn with(nolock) on Evn.Evn_id = RNP.Evn_id";
			$set_date_time = " convert(varchar(10), Evn.Evn_setDT, 104) + ' ' + convert(varchar(5), Evn.Evn_setDT, 108) as Evn_setDT";
		}

		$query = "
			Select
				RNP.Registry_id,
				RNP.Evn_id,
				RNP.Evn_rid,
				RNP.Person_id,
				RNP.Server_id,
				RNP.PersonEvn_id,
				rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
				convert(varchar(10), RNP.Person_BirthDay, 104) as Person_BirthDay,
				LS.LpuSection_FullName as LpuSection_Name,
				{$set_date_time}
			from {$this->scheme}.v_{$this->RegistryNoPolisObject} RNP with (NOLOCK)
				left join v_LpuSection LS with (NOLOCK) on LS.LpuSection_id = RNP.LpuSection_id
				{$evn_join}
			where
				RNP.Registry_id = :Registry_id
			order by
				RNP.Person_SurName,
				RNP.Person_FirName,
				RNP.Person_SecName,
				LS.LpuSection_Name
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
}