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
class Penza_Registry_model extends Registry_model {
	var $region = "penza";
	var $scheme = "r58";
	var $MaxEvnField = "Evn_id";

	private $dbregANSI = null;

	private $_unionRegistryData = array();

	// Хранение связок RegistryErrorType_Code => array('RegistryErrorType_id' => <значение>, 'RegistryErrorClass_id' => <значение>)
	private $_registryErrorTypeList = array();

	private $_IDCASE = 0;
	private $_IDSERV = 0;
	private $_N_ZAP = 0;
	private $_SL_ID = 0;
	private $_ZSL = 0;

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
		";
	}

	/**
	 * Получение дополнительных полей
	 */
	function getReformErrRegistryAdditionalFields() {
		return ",DispClass_id";
	}

	/**
	 *	Установка статуса реестра
	 */
	function setRegistryStatus($data) {
		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
		}

		// Предварительно получаем тип реестра
		$RegistryType_id = 0;
		$RegistryStatus_id = 0;

		$query = "
			select RegistryType_id, RegistryStatus_id
			from {$this->scheme}.v_Registry Registry with (NOLOCK)
			where Registry_id = :Registry_id
		";
		$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if (is_object($r) ) {
			$res = $r->result('array');

			if ( is_array($res) && count($res) > 0 ) {
				$RegistryType_id = $res[0]['RegistryType_id'];
				$RegistryStatus_id = $res[0]['RegistryStatus_id'];

				$data['RegistryType_id'] = $RegistryType_id;
			}
		}

		$this->setRegistryParamsByType($data);

		$fields = "";

		if ( $data['RegistryStatus_id'] == 3 ) { // если перевели в работу, то снимаем признак формирования
			//#11018 2. При перемещении реестра в других статусах в состояние "В работу " дополнительно сбрасывать Registry_xmlExpDT
			$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
		}

		if ($data['is_manual']!=1) {
			if ($data['RegistryStatus_id']==4) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000)
					exec {$this->scheme}.p_Registry_setPaid
						@Registry_id = :Registry_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select 4 as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				$result = $this->db->query($query, $data);
				if (!is_object($result))
				{
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
				}
			}
			else if ($data['RegistryStatus_id']==2 && $RegistryStatus_id==4) { // если переводим из "Оплаченный" в "К оплате" p_Registry_setUnPaid
				$check154914 = $this->checkRegistryDataIsInOtherRegistry($data);

				if ( !empty($check154914) ) {
					return array(array('success' => false, 'Error_Msg' => $check154914));
				}

				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000)
					exec {$this->scheme}.p_Registry_setUnPaid
						@Registry_id = :Registry_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select 2 as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				$result = $this->db->query($query, $data);

				if (!is_object($result)) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке к оплате'));
				}
			}
		}

		$query = "
			Declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@RegistryStatus_id bigint =  :RegistryStatus_id

			set nocount on

			begin try
				update {$this->scheme}.Registry set
					RegistryStatus_id = @RegistryStatus_id,
					Registry_updDT = dbo.tzGetDate(),
					{$fields}
					pmUser_updID = :pmUser_id
				where
					Registry_id = :Registry_id
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off

			select @RegistryStatus_id as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		}

	/**
	 *	Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	function SetXmlPackNum($data) {
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
								and SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) = :Registry_endMonth
								and Registry_FileNum is not null
								and RegistryGroupType_id = :RegistryGroupType_id
						);

						set @packNum = ISNULL(@packNum, 0) + 1;

						update {$this->scheme}.Registry with (rowlock)
						set Registry_FileNum = @packNum
						where Registry_id = :Registry_id
					end

				/*if ( @packNum > 9 )
					begin
						set @packNum = 9;
					end*/
			end try

			begin catch
				set @Err_Msg = error_message();
				set @packNum = null;
			end catch

			set nocount off;

			select @packNum as packNum, @Err_Msg as Error_Msg;
		";
		$result = $this->db->query($query, $data);

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
	 * Получение номера пакета для выгружаемых файлов
	 */
	public function getRegistryFileNum($data) {
		// Из YYYY-MM-DD выделяем YYMM
		$data['Registry_endMonth'] = substr(str_replace('-', '', $data['Registry_endDate']), 2, 4);

		$Registry_FileNum = $this->getFirstResultFromQuery("
			select max(Registry_FileNum) + 1 as Registry_FileNum
			from {$this->scheme}.v_Registry with (nolock)
			where Lpu_id = :Lpu_id
				and SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) = :Registry_endMonth
				and Registry_FileNum is not null
				and RegistryGroupType_id = :RegistryGroupType_id
		", $data);

		if ( !is_numeric($Registry_FileNum) || $Registry_FileNum === false ) {
			$Registry_FileNum = 1;
		}

		return $Registry_FileNum;
	}

	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		$this->setRegistryParamsByType($data);

		// Закомментировал условие выбора пути до файла
		// @task https://redmine.swan.perm.ru/issues/60634
		/*$xmlExportPath = 'case when ( Registry_xmlExpDT is null or datediff(mi, Registry_xmlExpDT, dbo.tzGetDate()) < 5 ) then RTrim(Registry_xmlExportPath) else NULL end as Registry_xmlExportPath,';

		if ( isSuperadmin() ) {
			$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';
		}*/

		$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';

		$query = "
			select
				{$xmlExportPath}
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.RegistryGroupType_id,
				R.Registry_FileNum,
				kn.KatNasel_SysNick,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
				RDSum.RegistryData_Count as RegistryData_Count,
				IsNull(R.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
				IsNull(rcs.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
				rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name,
				R.DispClass_id,
				SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
				,convert(varchar(10), Registry_begDate, 112) as Registry_begDate
			from {$this->scheme}.Registry R with (nolock)
				left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
				outer apply(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
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
	function loadRegistryErrorTFOMS($data)
	{
		$this->setRegistryParamsByType($data);

		$filterAddQueryTemp = null;
		$returnQueryOnly = isset($data['returnQueryOnly']);

		if(isset($data['Filter'])){
			$filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

			if(is_array($filterData)){

				foreach($filterData as $column=>$value){

					if(is_array($value)){
						$r = null;

						foreach($value as $d){
							$r .= "'".trim(toAnsi($d))."',";
						}

						if($column == 'Evn_id')
							$column = 'RE.'.$column;
						elseif($column == 'Person_FIO')
							$column = "rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))";//'RE.'.$column;
						elseif($column == 'LpuSection_Name')
							$column = 'LS.'.$column;
						elseif($column = 'RegistryErrorType_Code')
							$column = 'ret.'.$column;

						$r = rtrim($r, ',');

						$filterAddQueryTemp[] = $column.' IN ('.$r.')';
					}
				}

			}

			if(is_array($filterAddQueryTemp)){
				$filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
			}
			else
				$filterAddQuery = "and (1=1)";
		}

		$filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;

		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and ps.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}
		if (!empty($data['RegistryErrorStageType_id']))
		{
			$filter .= " and ret.RegistryErrorStageType_id = :RegistryErrorStageType_id ";
			$params['RegistryErrorStageType_id'] = $data['RegistryErrorStageType_id'];
		}

		$addToSelect = "";
		$leftjoin = "";

		if ( in_array($this->RegistryType_id, array(7, 9, 12)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = Evn.Evn_rid ";
			$addToSelect .= " , epd.DispClass_id";
		}

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		if ($this->RegistryType_id == 6) {
			$query = "
			Select
				-- select
				RegistryErrorTFOMS_id,
				RE.Registry_id,
				null as Evn_rid,
				RE.Evn_id,
				null as EvnClass_id,
				ret.RegistryErrorType_Code,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				--MP.Person_Fio as MedPersonal_Fio,
				RTRIM(RD.MedPersonal_Fio) as MedPersonal_Fio,
				LB.LpuBuilding_Name,
				null as LpuSection_Name,
				1 as RegistryData_deleted,
				1 as RegistryData_notexist
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
				left join {$this->scheme}.v_RegistryDataCmp RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.CmpCloseCard_id = RE.Evn_id
				left join v_CmpCloseCard CmpCloseCard with(nolock) on CmpCloseCard.CmpCloseCard_id = RD.CmpCloseCard_id
				left join v_CmpCallCard CmpCallCard with(nolock) on CmpCallCard.CmpCallCard_id = CmpCloseCard.CmpCallCard_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CmpCallCard.LpuBuilding_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = CmpCloseCard.MedPersonal_id
				) as MP
				left join v_PersonState ps with (nolock) on ps.Person_id = CmpCallCard.Person_id
				left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				{$leftjoin}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by";
		} else {
			$query = "
			Select
				-- select
				RegistryErrorTFOMS_id,
				RE.Registry_id,
				Evn.Evn_rid,
				RE.Evn_id,
				Evn.EvnClass_id,
				ret.RegistryErrorType_Code,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				--MP.Person_Fio as MedPersonal_Fio,
				RTRIM(RD.MedPersonal_Fio) as MedPersonal_Fio,
				LB.LpuBuilding_Name,
				LS.LpuSection_Name,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
				left join v_EvnPL evpl (nolock) on evpl.EvnPL_id = RE.Evn_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = ISNULL(ES.LpuSection_id, evpl.LpuSection_id)
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
				) as MP
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				{$leftjoin}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by";
		}

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
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
		$this->setRegistryParamsByType($data);

		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
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
			$errors_join = " inner join {$this->scheme}.v_{$this->RegistryErrorObject} re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
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
				{$this->scheme}.v_{$this->RegistryDataObject} rd
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
		$this->setRegistryParamsByType($data);

		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
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
			$errors_join = " inner join {$this->scheme}.v_{$this->RegistryErrorObject} re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
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
				{$this->scheme}.v_{$this->RegistryDataObject} rd
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
		$this->setRegistryParamsByType($data);

		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Registry_id = :Registry_id
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
			$errors_join = " inner join {$this->scheme}.v_{$this->RegistryErrorObject} re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
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
				{$this->scheme}.v_{$this->RegistryDataObject} rd
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
		
		// заодно получаем код ЛПУ
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
	 *	Получение данных для выгрузки реестров в XML
	 */
	function loadRegistrySCHETForXmlUsingCommonUnion($data)
	{
		if ( empty($this->dbregANSI) ) {
			$this->dbregANSI = $this->load->database('registry', true); // получаем коннект к БД
			$this->dbregANSI->close(); // коннект должен быть закрыт
			$this->dbregANSI->char_set = "windows-1251"; // ставим правильную кодировку (файл выгружается в windows-1251)
		}

		switch ( $data['RegistryGroupType_id'] ) {
			case 1: $object = 'Untd'; break;
			case 2: $object = 'EvnHTM'; break;
			case 3: case 4: $object = 'EvnPLDD13'; break;
			case 5: case 6: $object = 'EvnPLOrp13'; break;
			case 7: case 8: case 9: $object = 'EvnPLProfTeen'; break;
			case 10: $object = 'EvnPLProf'; break;
			case 26: $object = 'UntdMVD'; break;
			default: $object = 'Untd'; break;
		}

		$p_schet = $this->scheme.".p_Registry_{$object}_expScet";

		// шапка
		$query = "
			exec {$p_schet} @Registry_id = ?
		";

		$result = $this->dbregANSI->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');
			if (!empty($header[0])) {
				return array($header[0]);
			}
		}

		return false;
	}

	/**
	 * Разбивка SLUCH на услуги.
	 * @param $ZAP
	 * @return mixed
	 */
	function exportByUsl($vizit) {
		$newVizits = array();
		$gemVizit = null;
		$groupVizit = array();
		$groupUsl = array();

		if (!empty($vizit['USL'])) {
			foreach($vizit['USL'] as $oneUSL) {
				$newOneVizit = $vizit;
				if (mb_substr($oneUSL['CODE_USL'], 0, 3) == '02G' || (!empty($oneUSL['IsHemo']) && $oneUSL['IsHemo'] == 2)) {
					// гемодиализ не разбивается на отдельные услуги, выгружается в 1 SLUCH
					if (!empty($gemVizit)) {
						$gemVizit['USL'][] = $oneUSL;
					} else {
						$newOneVizit['USL'] = array($oneUSL);
						$gemVizit = $newOneVizit;
						$vizit['IDCASE']++;
					}
				} else if (!empty($oneUSL['EvnUslugaGroup_id'])) {
					// с одинаковым EvnUslugaGroup_id должны группироваться в 1 SLUCH
					$groupUsl[$oneUSL['EvnUslugaGroup_id']][] = $oneUSL;
					if (!empty($oneUSL['IsVizit']) && $oneUSL['IsVizit'] == 2) {
						// а это главная услуга с которой надо будет собрать SLUCH
						$newOneVizit['LPU'] = $oneUSL['USL_LPU'];
						$newOneVizit['LPU_1'] = $oneUSL['USL_LPU_1'];
						$newOneVizit['DATE_1'] = $oneUSL['DATE_IN'];
						$newOneVizit['DATE_2'] = $oneUSL['DATE_OUT'];
						$newOneVizit['SUMV'] = $oneUSL['SUMV_USL'];
						$newOneVizit['ED_COL'] = $oneUSL['KOL_USL'];
						if (!isset($groupVizit[$oneUSL['EvnUslugaGroup_id']])) {
							$groupVizit[$oneUSL['EvnUslugaGroup_id']] = $newOneVizit;
							$vizit['IDCASE']++;
						}
					}
				} else {
					// иначе разбиваем на отдельные SLUCH
					$newOneVizit['USL'] = array($oneUSL);
					$newOneVizit['LPU'] = $oneUSL['USL_LPU'];
					$newOneVizit['LPU_1'] = $oneUSL['USL_LPU_1'];
					$newOneVizit['DATE_1'] = $oneUSL['DATE_IN'];
					$newOneVizit['DATE_2'] = $oneUSL['DATE_OUT'];
					$newOneVizit['SUMV'] = $oneUSL['SUMV_USL'];
					$newOneVizit['ED_COL'] = $oneUSL['KOL_USL'];
					$newVizits[] = $newOneVizit;
					$vizit['IDCASE']++;
				}
			}
		}

		if (!empty($gemVizit)) {
			$newVizits[] = $gemVizit;
		}

		foreach($groupVizit as $key => $value) {
			$value['USL'] = $groupUsl[$key];
			$newVizits[] = $value;
		}

		foreach($groupUsl as $key => $value) {
			if (empty($groupVizit[$key])) { // если IsVizit не было, а EvnUslugaGroup_id есть
				$newOneVizit = $vizit;
				$newOneVizit['USL'] = $value;
				$newOneVizit['LPU'] = $value[0]['USL_LPU'];
				$newOneVizit['LPU_1'] = $value[0]['USL_LPU_1'];
				$newOneVizit['DATE_1'] = $value[0]['DATE_IN'];
				$newOneVizit['DATE_2'] = $value[0]['DATE_OUT'];
				$newOneVizit['SUMV'] = $value[0]['SUMV_USL'];
				$newOneVizit['ED_COL'] = $value[0]['KOL_USL'];
				$newVizits[] = $newOneVizit;
				$vizit['IDCASE']++;
			}
		}

		if (!empty($newVizits)) {
			return $newVizits;
		} else {
			return array($vizit);
		}
	}

	/**
	 * Разбивка SL на услуги.
	 * @param $ZAP
	 * @return mixed
	 */
	protected function _exportByUsl2018($sl) {
		$newSLs = array();
		$gemSL = null;
		$groupSL = array();
		$groupUsl = array();

		if (!empty($sl['USL'])) {
			foreach($sl['USL'] as $oneUSL) {
				$newOneSL = $sl;
				if (mb_substr($oneUSL['CODE_USL'], 0, 3) == '02G' || (!empty($oneUSL['IsHemo']) && $oneUSL['IsHemo'] == 2)) {
					// гемодиализ не разбивается на отдельные услуги, выгружается в 1 SLUCH
					if (!empty($gemSL)) {
						$gemSL['USL'][] = $oneUSL;
					} else {
						$newOneSL['USL'] = array($oneUSL);
						$gemSL = $newOneSL;
					}
				} else if (!empty($oneUSL['EvnUslugaGroup_id'])) {
					// с одинаковым EvnUslugaGroup_id должны группироваться в 1 SLUCH
					$groupUsl[$oneUSL['EvnUslugaGroup_id']][] = $oneUSL;
					if (!empty($oneUSL['IsVizit']) && $oneUSL['IsVizit'] == 2) {
						// а это главная услуга с которой надо будет собрать SLUCH
						$newOneSL['LPU'] = $oneUSL['USL_LPU'];
						$newOneSL['LPU_1'] = $oneUSL['USL_LPU_1'];
						$newOneSL['DATE_1'] = $oneUSL['DATE_IN'];
						$newOneSL['DATE_2'] = $oneUSL['DATE_OUT'];
						$newOneSL['SUMV'] = $oneUSL['SUMV_USL'];
						$newOneSL['ED_COL'] = $oneUSL['KOL_USL'];
						if (!isset($groupSL[$oneUSL['EvnUslugaGroup_id']])) {
							$groupSL[$oneUSL['EvnUslugaGroup_id']] = $newOneSL;
						}
					}
				} else {
					// иначе разбиваем на отдельные SLUCH
					$newOneSL['USL'] = array($oneUSL);
					$newOneSL['LPU'] = $oneUSL['USL_LPU'];
					$newOneSL['LPU_1'] = $oneUSL['USL_LPU_1'];
					$newOneSL['DATE_1'] = $oneUSL['DATE_IN'];
					$newOneSL['DATE_2'] = $oneUSL['DATE_OUT'];
					$newOneSL['SUMV'] = $oneUSL['SUMV_USL'];
					$newOneSL['ED_COL'] = $oneUSL['KOL_USL'];
					$newSLs[] = $newOneSL;
				}
			}
		}

		if (!empty($gemSL)) {
			$newSLs[] = $gemSL;
		}

		foreach($groupSL as $key => $value) {
			$value['USL'] = $groupUsl[$key];
			$newSLs[] = $value;
		}

		foreach($groupUsl as $key => $value) {
			if (empty($groupSL[$key])) { // если IsVizit не было, а EvnUslugaGroup_id есть
				$newOneSL = $sl;
				$newOneSL['USL'] = $value;
				$newOneSL['LPU'] = $value[0]['USL_LPU'];
				$newOneSL['LPU_1'] = $value[0]['USL_LPU_1'];
				$newOneSL['DATE_1'] = $value[0]['DATE_IN'];
				$newOneSL['DATE_2'] = $value[0]['DATE_OUT'];
				$newOneSL['SUMV'] = $value[0]['SUMV_USL'];
				$newOneSL['ED_COL'] = $value[0]['KOL_USL'];
				$newSLs[] = $newOneSL;
			}
		}

		if (!empty($newSLs)) {
			// пробиваем правильные SL_ID
			$first = true;
			foreach($newSLs as $key => $value) {
				if (!$first) {
					$this->_SL_ID++;
				} else {
					$first = false;
				}
				$newSLs[$key]['SL_ID'] = $this->_SL_ID;
			}
			return $newSLs;
		} else {
			return array($sl);
		}
	}

	/**
	 * Размножение услуг
	 */
	function increaseUslByKol($usl) {
		if (!empty($usl['EvnUsluga_Kolvo']) && $usl['EvnUsluga_Kolvo'] > 1) {
			$newUsls = array();

			$count = $usl['EvnUsluga_Kolvo'];
			$usl['KOL_USL'] = number_format($usl['KOL_USL'] / $count, 2, '.', '');
			$usl['SUMV_USL'] = number_format($usl['SUMV_USL'] / $count, 2, '.', '');

			for ($i = 0; $i < $count; $i++) { // размножаем услуги по количеству услуг
				$usl['EvnUslugaGroup_id'] = null; // не должны сгруппироваться в 1 группу
				$newUsls[] = $usl;
				$usl['IDSERV']++;
			}

			return $newUsls;
		}

		return array($usl);
	}

	/**
	 * Размножение услуг
	 */
	protected function _increaseUslByKol2018($usl) {
		if (!empty($usl['EvnUsluga_Kolvo']) && $usl['EvnUsluga_Kolvo'] > 1) {
			$newUsls = array();

			$count = $usl['EvnUsluga_Kolvo'];
			$usl['KOL_USL'] = number_format($usl['KOL_USL'] / $count, 2, '.', '');
			$usl['SUMV_USL'] = number_format($usl['SUMV_USL'] / $count, 2, '.', '');

			for ($i = 0; $i < $count; $i++) { // размножаем услуги по количеству услуг
				$this->_IDSERV++;

				$usl['EvnUslugaGroup_id'] = null; // не должны сгруппироваться в 1 группу
				$usl['IDSERV'] = $this->_IDSERV;
				$newUsls[] = $usl;
			}

			return $newUsls;
		}

		return array($usl);
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	public function loadRegistryDataForXmlUsing($data, &$Registry_EvnNum, $xml_file, $file_re_data_name, $file_re_pers_data_name, $error_log = '') {
		if ( empty($this->dbregANSI) ) {
			$this->dbregANSI = $this->load->database('registry', true); // получаем коннект к БД
			$this->dbregANSI->close(); // коннект должен быть закрыт
			$this->dbregANSI->char_set = "windows-1251"; // ставим правильную кодировку (файл выгружается в windows-1251)
		}

		/**
		 * Функция записи ошибки в лог
		 */
		function writeToErrorLog($log, $string) {
			if ( empty($log) ) {
				return false;
			}

			$f = fopen($log, 'a');
			fputs($f, $string . PHP_EOL);
			fclose($f);

			return true;
		}

		$rsp = $this->queryResult("
			select
				r.Registry_id,
				r.Registry_Num,
				rt.RegistryType_Name
			from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
				inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
				inner join v_RegistryType rt with (nolock) on rt.RegistryType_id = r.RegistryType_id
			where rgl.Registry_pid = :Registry_id
		", $data);

		if ( $rsp === false || !is_array($rsp) || count($rsp) == 0 ) {
			return false;
		}

		foreach ( $rsp as $row ) {
			$this->_unionRegistryData[$row['Registry_id']] = $row;
		}

		$IDCASE = 1;
		$IDSERV = 1;
		$N_ZAP = 1;
		$SLUCH_COUNT = 0;

		switch ( $data['RegistryGroupType_id'] ) {
			case 1: $object = 'Untd'; break;
			case 2: $object = 'EvnHTM'; break;
			case 3: case 4: $object = 'EvnPLDD13'; break;
			case 5: case 6: $object = 'EvnPLOrp13'; break;
			case 7: case 8: case 9: $object = 'EvnPLProfTeen'; break;
			case 10: $object = 'EvnPLProf'; break;
			case 26: $object = 'UntdMVD'; break;
			default: $object = 'Untd'; break;
		}

		$exportByUsl = false;
		if (in_array($data['RegistryGroupType_id'], array(1,4,5,6,8,9,26))) {
			$exportByUsl = true;
		}
		$increaseUslByKol = false;
		if (in_array($data['RegistryGroupType_id'], array(1))) {
			$increaseUslByKol = true;
		}

		$p_pers = $this->scheme.".p_Registry_{$object}_expPac";
		$p_usl = $this->scheme.".p_Registry_{$object}_expUsl";
		$p_vizit = $this->scheme.".p_Registry_{$object}_expVizit";

		if ( in_array($data['RegistryGroupType_id'], array(1, 2, 3, 4, 7, 8, 9, 10)) ) {
			$p_ds2 = $this->scheme.".p_Registry_{$object}_expDS2";
		}

		if ( in_array($data['RegistryGroupType_id'], array(1, 2)) ) {
			$p_ds3 = $this->scheme.".p_Registry_{$object}_expDS3";
		}

		if ( in_array($data['RegistryGroupType_id'], array(3, 4, 5, 6, 7, 8, 9, 10)) ) {
			$p_naz = $this->scheme.".p_Registry_{$object}_expNAZ";
		}

		$this->textlog->add('Занимаемая память до выполнения ХП: ' . (memory_get_usage()/1024/1024));

		// 1. Выгружаем пациентов
		$query = "exec {$p_pers} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_pers = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_pers) ) {
			return false;
		}

		// 2. Выгружаем посещения
		$query = "exec {$p_vizit} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_vizit = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_vizit) ) {
			return false;
		}

		// 3. Выгружаем услуги
		$USL = array();
		$query = "exec {$p_usl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_usluga = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		//echo getDebugSQL($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_usluga) ) {
			return false;
		}

		// Формируем массив услуг
		while ( $usluga = $result_usluga->_fetch_assoc() ) {
			if ( !isset($USL[$usluga['MaxEvn_id']]) ) {
				$USL[$usluga['MaxEvn_id']] = array();
			}

			$USL[$usluga['MaxEvn_id']][] = $usluga;
		}

		// 4. Выгружаем диагнозы DS2
		$DS2 = array();
		if ( !empty($p_ds2) ) {
			$query = "exec {$p_ds2} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds2 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_ds2) ) {
				return false;
			}

			// Формируем массив DS2
			while ( $one_ds2 = $result_ds2->_fetch_assoc() ) {
				if ( !isset($DS2[$one_ds2['MaxEvn_id']]) ) {
					$DS2[$one_ds2['MaxEvn_id']] = array();
				}

				$DS2[$one_ds2['MaxEvn_id']][] = $one_ds2;
			}
		}

		// 5. Выгружаем диагнозы DS3
		$DS3 = array();
		if ( !empty($p_ds3) ) {
			$query = "exec {$p_ds3} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds3 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_ds3) ) {
				return false;
			}

			// Формируем массив DS3
			while ( $one_ds3 = $result_ds3->_fetch_assoc() ) {
				if ( !isset($DS3[$one_ds3['MaxEvn_id']]) ) {
					$DS3[$one_ds3['MaxEvn_id']] = array();
				}

				$DS3[$one_ds3['MaxEvn_id']][] = $one_ds3;
			}
		}

		// 6. Выгружаем назначения NAZ
		$NAZ = array();
		if ( !empty($p_naz) ) {
			$query = "exec {$p_naz} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_naz = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_naz) ) {
				return false;
			}

			// Формируем массив NAZ
			while ( $one_naz = $result_naz->_fetch_assoc() ) {
				if ( !isset($NAZ[$one_naz['MaxEvn_id']]) ) {
					$NAZ[$one_naz['MaxEvn_id']] = array();
				}

				$NAZ[$one_naz['MaxEvn_id']][] = $one_naz;
			}
		}

		$this->textlog->add('Занимаемая память после выполнения ХП: ' . (memory_get_usage()/1024/1024));

		$altKeys = array(
			 'USL_LPU' => 'LPU'
			,'USL_LPU_1' => 'LPU_1'
			,'LPU_1_USL' => 'LPU_1'
			,'USL_P_OTK' => 'P_OTK'
			,'USL_PODR' => 'PODR'
			,'PODR_USL' => 'PODR'
			,'USL_PROFIL' => 'PROFIL'
			,'USL_DET' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'USL_PRVS' => 'PRVS'
		);

		// идём по людям, цепляем к ним случаи и услуги и пишем в файл
		$ZAP = array();
		$CurrentPersonEvn_id = null;
		$CurrentVizit = null;
		$CurrentUsluga = null;
		$netValue = toAnsi('НЕТ', true);

		while ($pers = $result_pers->_fetch_assoc()) {
			if ( empty($pers['ID_PERS']) ) {
				continue;
			}
			$PersonEvn_id = $pers['ID_PERS'] . '_' . intval($pers['NOVOR']);

			if ($CurrentPersonEvn_id == $PersonEvn_id) {
				continue; // если уже был такой, пропускаем
			}

			$CurrentPersonEvn_id = $PersonEvn_id;

			// некоторая обработка пациента
			$pers['DOST'] = array();
			$pers['DOST_P'] = array();
			$pers['NPOLIS'] = isset($pers['NPOLIS']) ? $pers['NPOLIS'] : null;

			if($data['RegistryGroupType_id'] != 26){

				if ( empty($pers['NOVOR']) || $pers['NOVOR'] == '0' ) {
					$i = 0;

					if ( empty($pers['FAM']) ) {
						$i++;
						$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 2);
					}

					if ( empty($pers['IM']) ) {
						$i++;
						$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 3);
					}

					if ( empty($pers['OT']) || mb_strtoupper($pers['OT']) == $netValue ) {
						$i++;
						$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 1);
					}

				if ($i == 0) {
					$i++;
					$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 3);
				}
			}
				else {
					$i = 0;

					if ( empty($pers['FAM_P']) ) {
						$i++;
						$pers['DOST_P'][] = array('DOST_P_N' => $i, 'DOST_P_CODE' => 2);
					}

					if ( empty($pers['IM_P']) ) {
						$i++;
						$pers['DOST_P'][] = array('DOST_P_N' => $i, 'DOST_P_CODE' => 3);
					}

					if ( empty($pers['OT_P']) || mb_strtoupper($pers['OT_P']) == $netValue ) {
						$i++;
						$pers['DOST_P'][] = array('DOST_P_N' => $i, 'DOST_P_CODE' => 1);
					}
				}
			}


			try {
				// ищем случаи для пациента
				if (!empty($CurrentVizit) && $CurrentVizit['PersonEvn_id'] . '_' . intval($CurrentVizit['NOVOR']) == $CurrentPersonEvn_id) {
					$key = $CurrentPersonEvn_id.'_'.intval($CurrentVizit['PR_NOV']);
					if (!array_key_exists($key, $ZAP)) {
						$pers['N_ZAP'] = $N_ZAP;
						$N_ZAP++;
						$ZAP[$key] = $pers;
						$ZAP[$key]['PR_NOV'] = $CurrentVizit['PR_NOV'];
					}

					if (in_array($object, array('Untd', 'EvnHTM', 'UntdMVD'))) {
						if (!empty($pers['OS_SLUCH'])) {
							$CurrentVizit['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH']);
						}
						if (!empty($pers['OS_SLUCH1'])) {
							$CurrentVizit['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH1']);
						}
					}

					$ZAP[$key]['ID_PAC'] = $key;

					if ($exportByUsl) {
						// постобработка
						$vizits = $this->exportByUsl($CurrentVizit);
						$SLUCH_COUNT += count($vizits) - 1;
						foreach ($vizits as $onevizit) {
							$IDCASE = $onevizit['IDCASE'];
							$Registry_EvnNum[$onevizit['IDCASE']] = array(
								'e' => $onevizit['MaxEvn_id'],
								'r' => $onevizit['Registry_id'],
								'n' => $ZAP[$key]['N_ZAP']
							);
							$ZAP[$key]['SLUCH'][] = $onevizit;
						}
						$IDCASE++;
					} else {
						$Registry_EvnNum[$CurrentVizit['IDCASE']] = array(
							'e' => $CurrentVizit['MaxEvn_id'],
							'r' => $CurrentVizit['Registry_id'],
							'n' => $ZAP[$key]['N_ZAP']
						);
						$ZAP[$key]['SLUCH'][] = $CurrentVizit;
					}
					$CurrentVizit = null;
				}

				if (empty($CurrentVizit)) {
					// ищем ещё случаи для пациента
					while ($vizit = $result_vizit->_fetch_assoc()) {

						$SLUCH_COUNT++;
						$CurrentVizit = $vizit;
						$CurrentVizit['USL'] = array();
						$CurrentVizit['DS2_DATA'] = array();
						$CurrentVizit['DS3_DATA'] = array();
						$CurrentVizit['NAZ_DATA'] = array();
						if (in_array($object, array('Untd', 'EvnHTM', 'UntdMVD'))) {
							$CurrentVizit['OS_SLUCH'] = array();
						}

						// Привязываем услуги
						if ( isset($USL[$vizit['MaxEvn_id']]) ) {
							foreach ( $USL[$vizit['MaxEvn_id']] as $k => $usluga ) {
								$usluga['IDSERV'] = $IDSERV;
								$IDSERV++;

								if ( $increaseUslByKol ) {
									// постобработка
									$usls = $this->increaseUslByKol($usluga);

									foreach ( $usls as $oneusl ) {
										$IDSERV = $oneusl['IDSERV'];
										$CurrentVizit['USL'][] = $oneusl;
									}

									$IDSERV++;
								}
								else {
									$CurrentVizit['USL'][] = $usluga;
								}
							}

							unset($USL[$vizit['MaxEvn_id']]);
						}

						// Привязываем DS2
						if ( isset($DS2[$vizit['MaxEvn_id']]) ) {
							foreach ( $DS2[$vizit['MaxEvn_id']] as $k => $one_rec ) {
								$CurrentVizit['DS2_DATA'][] = $one_rec;
							}
							unset($DS2[$vizit['MaxEvn_id']]);
						}

						// Привязываем DS3
						if ( isset($DS3[$vizit['MaxEvn_id']]) ) {
							foreach ( $DS3[$vizit['MaxEvn_id']] as $k => $one_rec ) {
								$CurrentVizit['DS3_DATA'][] = $one_rec;
							}
							unset($DS3[$vizit['MaxEvn_id']]);
						}

						// Привязываем NAZ
						if ( isset($NAZ[$vizit['MaxEvn_id']]) ) {
							foreach ( $NAZ[$vizit['MaxEvn_id']] as $k => $one_rec ) {
								$CurrentVizit['NAZ_DATA'][] = $one_rec;
							}
							unset($NAZ[$vizit['MaxEvn_id']]);
						}

						$CurrentVizit['IDCASE'] = $IDCASE;
						$IDCASE++;

						if ($CurrentVizit['PersonEvn_id'] . '_' . intval($CurrentVizit['NOVOR']) == $CurrentPersonEvn_id) {
							$key = $CurrentPersonEvn_id . '_' . $CurrentVizit['PR_NOV'];
							if (!array_key_exists($key, $ZAP)) {
								$pers['N_ZAP'] = $N_ZAP;
								$N_ZAP++;
								$ZAP[$key] = $pers;
								$ZAP[$key]['PR_NOV'] = $CurrentVizit['PR_NOV'];
							}

							if (in_array($object, array('Untd', 'EvnHTM', 'UntdMVD'))) {
								if (!empty($pers['OS_SLUCH'])) {
									$CurrentVizit['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH']);
								}
								if (!empty($pers['OS_SLUCH1'])) {
									$CurrentVizit['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH1']);
								}
							}

							$ZAP[$key]['ID_PAC'] = $key;

							if ($exportByUsl) {
								// постобработка
								$vizits = $this->exportByUsl($CurrentVizit);
								$SLUCH_COUNT += count($vizits) - 1;
								foreach ($vizits as $onevizit) {
									$IDCASE = $onevizit['IDCASE'];
									$Registry_EvnNum[$onevizit['IDCASE']] = array(
										'e' => $onevizit['MaxEvn_id'],
										'r' => $onevizit['Registry_id'],
										'n' => $ZAP[$key]['N_ZAP']
									);
									$ZAP[$key]['SLUCH'][] = $onevizit;
								}
								$IDCASE++;
							} else {
								$Registry_EvnNum[$CurrentVizit['IDCASE']] = array(
									'e' => $CurrentVizit['MaxEvn_id'],
									'r' => $CurrentVizit['Registry_id'],
									'n' => $ZAP[$key]['N_ZAP']
								);
								$ZAP[$key]['SLUCH'][] = $CurrentVizit;
							}
						} else {
							break;
						}
					}
				}
			} catch (Exception $e) {
				// вышли за пределы случаев
			}

			if (count($ZAP) >= 1000) {
				$cntZAP = count($ZAP);

				foreach ( $ZAP as $k => $oneZAP ) {
					if (in_array($object, array('Untd', 'EvnHTM', 'UntdMVD'))) {
						unset($ZAP[$k]['OS_SLUCH']);
						unset($ZAP[$k]['OS_SLUCH1']);
					}

					if ( !array_key_exists('SLUCH', $oneZAP) || count($oneZAP['SLUCH']) == 0 ) {
						array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
						writeToErrorLog($error_log, "Запись без SLUCH" . PHP_EOL
							. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
							. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
							. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
							//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
						);
						unset($ZAP[$k]);
					}
					else {
						$oneZAPConverted = false;

						foreach ( $oneZAP['SLUCH'] as $j => $oneSLUCH ) {
							if ( !array_key_exists('USL', $oneSLUCH) || count($oneSLUCH['USL']) == 0 ) {
								$SLUCH_COUNT--;
								if ( $oneZAPConverted === false ) {
									array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
									$oneZAPConverted = true;
								}
								//array_walk_recursive($oneSLUCH, 'ConvertFromWin1251ToUTF8', true);
								writeToErrorLog($error_log, "SLUCH без USL" . PHP_EOL
									. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
									. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
									. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
									. "IDCASE: " . $oneSLUCH['IDCASE'] . PHP_EOL
									. "ID случая: " . $oneSLUCH['MaxEvn_id'] . PHP_EOL
									. "Дата начала случая: " . $oneSLUCH['DATE_1'] . PHP_EOL
									. "Дата окончания случая: " . $oneSLUCH['DATE_2'] . PHP_EOL
									//. "Информация о случае: " . json_encode($oneSLUCH) . PHP_EOL
								);
								unset($ZAP[$k]['SLUCH'][$j]);
							}
						}

						if ( count($ZAP[$k]['SLUCH']) == 0 ) {
							if ( $oneZAPConverted === false ) {
								array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
							}
							writeToErrorLog($error_log, "Запись без SLUCH" . PHP_EOL
								. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
								. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
								. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
								//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
							);
							unset($ZAP[$k]);
						}
					}
				}

				// пишем в файл
				// array_walk_recursive($ZAP, 'ConvertFromUTF8ToWin1251', true);
				$this->textlog->add('Сформирован пакет на ' . $cntZAP . ' записей, памяти задействовано: ' . (memory_get_usage()/1024/1024));
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $xml_file, array('ZAP' => $ZAP), true, false, $altKeys);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили ' . $cntZAP . ' записей ZAP за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/registry_penza_person_body', array('PACIENT' => $ZAP), true);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили ' . $cntZAP . ' записей PACIENT за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_pers_data_name, $xml, FILE_APPEND);
				unset($xml);

				unset($ZAP);
				$ZAP = array();

				$this->textlog->add('Обнулили переменные, памяти задействовано: ' . (memory_get_usage()/1024/1024));
			}
		}

		if (count($ZAP) > 0) {
			$cntZAP = count($ZAP);

			foreach ( $ZAP as $k => $oneZAP ) {
				if (in_array($object, array('Untd', 'EvnHTM', 'UntdMVD'))) {
					unset($ZAP[$k]['OS_SLUCH']);
					unset($ZAP[$k]['OS_SLUCH1']);
				}

				if ( !array_key_exists('SLUCH', $oneZAP) || count($oneZAP['SLUCH']) == 0 ) {
					array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
					writeToErrorLog($error_log, "Запись без SLUCH" . PHP_EOL
						. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
						. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
						. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
						//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
					);
					unset($ZAP[$k]);
				}
				else {
					$oneZAPConverted = false;

					foreach ( $oneZAP['SLUCH'] as $j => $oneSLUCH ) {
						if ( !array_key_exists('USL', $oneSLUCH) || count($oneSLUCH['USL']) == 0 ) {
							$SLUCH_COUNT--;
							if ( $oneZAPConverted === false ) {
								array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
								$oneZAPConverted = true;
							}
							//array_walk_recursive($oneSLUCH, 'ConvertFromWin1251ToUTF8', true);
							writeToErrorLog($error_log, "SLUCH без USL" . PHP_EOL
								. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
								. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
								. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
								. "IDCASE: " . $oneSLUCH['IDCASE'] . PHP_EOL
								. "ID случая: " . $oneSLUCH['MaxEvn_id'] . PHP_EOL
								. "Дата начала случая: " . $oneSLUCH['DATE_1'] . PHP_EOL
								. "Дата окончания случая: " . $oneSLUCH['DATE_2'] . PHP_EOL
								//. "Информация о случае: " . json_encode($oneSLUCH) . PHP_EOL
							);
							unset($ZAP[$k]['SLUCH'][$j]);
						}
					}

					if ( count($ZAP[$k]['SLUCH']) == 0 ) {
						if ( $oneZAPConverted === false ) {
							array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
						}
						writeToErrorLog($error_log, "Запись без SLUCH" . PHP_EOL
							. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
							. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
							. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
							//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
						);
						unset($ZAP[$k]);
					}
				}
			}

			// пишем в файл
			// array_walk_recursive($ZAP, 'ConvertFromUTF8ToWin1251', true);
			$this->textlog->add('Сформирован пакет на ' . $cntZAP . ' записей, памяти задействовано: ' . (memory_get_usage()/1024/1024));
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $xml_file, array('ZAP' => $ZAP), true, false, $altKeys);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . $cntZAP . ' записей ZAP за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/registry_penza_person_body', array('PACIENT' => $ZAP), true);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . $cntZAP . ' записей PACIENT за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_pers_data_name, $xml, FILE_APPEND);
			unset($xml);

			unset($ZAP);

			$this->textlog->add('Обнулили переменные, памяти задействовано: ' . (memory_get_usage()/1024/1024));
		}

		return $SLUCH_COUNT;
	}
	
	/**
	 * Получение данных для выгрузки реестров в XML
	 * Версия с 01.05.2018
	 */
	public function loadRegistryDataForXmlUsing2018($data, &$Registry_EvnNum, $xml_file, $file_re_data_name, $file_re_pers_data_name, $error_log = '') {
		if ( empty($this->dbregANSI) ) {
			$this->dbregANSI = $this->load->database('registry', true); // получаем коннект к БД
			$this->dbregANSI->close(); // коннект должен быть закрыт
			$this->dbregANSI->char_set = "windows-1251"; // ставим правильную кодировку (файл выгружается в windows-1251)
		}

		/**
		 * Функция записи ошибки в лог
		 */
		function writeToErrorLog($log, $string) {
			if ( empty($log) ) {
				return false;
			}

			$f = fopen($log, 'a');
			fputs($f, $string . PHP_EOL);
			fclose($f);

			return true;
		}

		$rsp = $this->queryResult("
			select
				r.Registry_id,
				r.Registry_Num,
				rt.RegistryType_Name
			from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
				inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
				inner join v_RegistryType rt with (nolock) on rt.RegistryType_id = r.RegistryType_id
			where rgl.Registry_pid = :Registry_id
		", $data);

		if ( $rsp === false || !is_array($rsp) || count($rsp) == 0 ) {
			return false;
		}

		foreach ( $rsp as $row ) {
			$this->_unionRegistryData[$row['Registry_id']] = $row;
		}

		switch ( $data['RegistryGroupType_id'] ) {
			case 1: $object = 'Untd'; break;
			case 2: $object = 'EvnHTM'; break;
			case 3: case 4: $object = 'EvnPLDD13'; break;
			case 5: case 6: $object = 'EvnPLOrp13'; break;
			case 7: case 8: case 9: $object = 'EvnPLProfTeen'; break;
			case 10: $object = 'EvnPLProf'; break;
			default: $object = 'Untd'; break;
		}

		$exportByUsl = false;
		if (in_array($data['RegistryGroupType_id'], array(1, 4, 5, 6, 8, 9))) {
			$exportByUsl = true;
		}
		$increaseUslByKol = false;
		if (in_array($data['RegistryGroupType_id'], array(1))) {
			$increaseUslByKol = true;
		}

		$p_pers = $this->scheme.".p_Registry_{$object}_expPac_2018";
		$p_sl = $this->scheme.".p_Registry_{$object}_expVizit_2018";
		$p_usl = $this->scheme.".p_Registry_{$object}_expUsl_2018";
		$p_zsl = $this->scheme.".p_Registry_{$object}_expSL_2018";

		if ( in_array($data['RegistryGroupType_id'], array(1, 2, 3, 4, 7, 8, 9, 10)) ) {
			$p_ds2 = $this->scheme.".p_Registry_{$object}_expDS2";
		}

		if ( in_array($data['RegistryGroupType_id'], array(1, 2)) ) {
			$p_bdiag = $this->scheme.".p_Registry_{$object}_expBDIAG_2018";
			$p_bprot = $this->scheme.".p_Registry_{$object}_expBPROT_2018";
			$p_ds3 = $this->scheme.".p_Registry_{$object}_expDS3";
			$p_napr = $this->scheme . ".p_Registry_{$object}_expNAPR_2018";
			$p_onkousl = $this->scheme.".p_Registry_{$object}_expONKOUSL_2018";
		}

		if ( in_array($data['RegistryGroupType_id'], array(1)) ) {
			$p_kslp = $this->scheme.".p_Registry_{$object}_expKSLP_2018";
		}

		if ( in_array($data['RegistryGroupType_id'], array(3, 4, 5, 6, 7, 8, 9, 10)) ) {
			$p_naz = $this->scheme.".p_Registry_{$object}_expNAZ_2018";
		}

		$this->textlog->add('Занимаемая память до выполнения ХП: ' . (memory_get_usage()/1024/1024));

		// 1. Выгружаем пациентов
		$query = "exec {$p_pers} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_pers = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_pers) ) {
			return false;
		}

		// 2. Выгружаем законченные случаи
		$query = "exec {$p_zsl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_zsl = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_zsl) ) {
			return false;
		}

		// 3. Выгружаем посещения
		$query = "exec {$p_sl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_sl = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_sl) ) {
			return false;
		}

		// 4. Выгружаем услуги
		$USL = array();
		$query = "exec {$p_usl} @Registry_id = :Registry_id";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_usluga = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');

		if ( !is_object($result_usluga) ) {
			return false;
		}

		// Формируем массив услуг
		while ( $usluga = $result_usluga->_fetch_assoc() ) {
			if ( !isset($USL[$usluga['MaxEvn_id']]) ) {
				$USL[$usluga['MaxEvn_id']] = array();
			}

			$usluga['NAPR_DATA'] = array();
			$usluga['ONK_USL_DATA'] = array();

			$USL[$usluga['MaxEvn_id']][] = $usluga;
		}

		// 5. Выгружаем диагнозы DS2
		$DS2 = array();
		if ( !empty($p_ds2) ) {
			$query = "exec {$p_ds2} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds2 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_ds2) ) {
				return false;
			}

			// Формируем массив DS2
			while ( $one_ds2 = $result_ds2->_fetch_assoc() ) {
				if ( !isset($DS2[$one_ds2['MaxEvn_id']]) ) {
					$DS2[$one_ds2['MaxEvn_id']] = array();
				}

				$DS2[$one_ds2['MaxEvn_id']][] = $one_ds2;
			}
		}

		// 6. Выгружаем диагнозы DS3
		$DS3 = array();
		if ( !empty($p_ds3) ) {
			$query = "exec {$p_ds3} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds3 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_ds3) ) {
				return false;
			}

			// Формируем массив DS3
			while ( $one_ds3 = $result_ds3->_fetch_assoc() ) {
				if ( !isset($DS3[$one_ds3['MaxEvn_id']]) ) {
					$DS3[$one_ds3['MaxEvn_id']] = array();
				}

				$DS3[$one_ds3['MaxEvn_id']][] = $one_ds3;
			}
		}

		// 7. Выгружаем назначения NAZR
		$NAZR = array();
		if ( !empty($p_naz) ) {
			$query = "exec {$p_naz} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_naz = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_naz) ) {
				return false;
			}

			// Формируем массив NAZR
			while ( $one_naz = $result_naz->_fetch_assoc() ) {
				if ( !isset($NAZR[$one_naz['MaxEvn_id']]) ) {
					$NAZR[$one_naz['MaxEvn_id']] = array();
				}

				$NAZR[$one_naz['MaxEvn_id']][] = $one_naz;
			}
		}

		// 8. Выгружаем КСЛП
		$KSLP = array();
		if ( !empty($p_kslp) ) {
			$query = "exec {$p_kslp} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_kslp = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($result_kslp) ) {
				return false;
			}

			// Формируем массив KSLP
			while ( $one_kslp = $result_kslp->_fetch_assoc() ) {
				if ( !isset($KSLP[$one_kslp['MaxEvn_id']]) ) {
					$KSLP[$one_kslp['MaxEvn_id']] = array();
				}

				$KSLP[$one_kslp['MaxEvn_id']][] = $one_kslp;
			}
		}

		// 9. Выгружаем направления
		$NAPR = array();
		if ( !empty($p_napr) ) {
			$query = "exec {$p_napr} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($query_result) ) {
				return false;
			}

			// Формируем массив NAPR
			while ( $row = $query_result->_fetch_assoc() ) {
				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = array();
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		// 10. Выгружаем информацию по онкологическим услугам
		$ONK_USL = array();
		if ( !empty($p_onkousl) ) {
			$query = "exec {$p_onkousl} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($query_result) ) {
				return false;
			}

			// Формируем массив ONK_USL
			while ( $row = $query_result->_fetch_assoc() ) {
				if ( !isset($ONK_USL[$row['Evn_id']]) ) {
					$ONK_USL[$row['Evn_id']] = array();
				}

				$ONK_USL[$row['Evn_id']][] = $row;
			}
		}

		// 11. Выгружаем данные диагностического блока
		$BDIAG = array();
		if ( !empty($p_bdiag) ) {
			$query = "exec {$p_bdiag} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($query_result) ) {
				return false;
			}

			// Формируем массив BDIAG
			while ( $row = $query_result->_fetch_assoc() ) {
				if ( !isset($BDIAG[$row['MaxEvn_id']]) ) {
					$BDIAG[$row['MaxEvn_id']] = array();
				}

				$BDIAG[$row['MaxEvn_id']][] = $row;
			}
		}

		// 12. Выгружаем сведения об имеющихся противопоказаниях и отказах
		$BPROT = array();
		if ( !empty($p_bprot) ) {
			$query = "exec {$p_bprot} @Registry_id = :Registry_id";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if ( !is_object($query_result) ) {
				return false;
			}

			// Формируем массив BPROT
			while ( $row = $query_result->_fetch_assoc() ) {
				if ( !isset($BPROT[$row['MaxEvn_id']]) ) {
					$BPROT[$row['MaxEvn_id']] = array();
				}

				$BPROT[$row['MaxEvn_id']][] = $row;
			}
		}

		$this->textlog->add('Занимаемая память после выполнения ХП: ' . (memory_get_usage()/1024/1024));

		$altKeys = array(
			 'SL_SANK_IT' => 'SANK_IT'
			,'USL_LPU' => 'LPU'
			,'USL_LPU_1' => 'LPU_1'
			,'USL_P_OTK' => 'P_OTK'
			,'USL_PODR' => 'PODR'
			,'USL_PROFIL' => 'PROFIL'
			,'USL_DET' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'USL_PRVS' => 'PRVS'
		);

		$KSG_KPG_DATA_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL');
		$NAZR_DATA_FIELDS = array('NAZ_N', 'NAZ_R', 'NAZ_SP', 'NAZ_V', 'NAZ_PMP', 'NAZ_PK');
		$ONK_SL_DATA_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD');

		// идём по людям, цепляем к ним случаи и услуги и пишем в файл
		$ZAP = array();
		$CurrentEvn_rid = null;
		$CurrentPersonEvn_id = null;
		$CurrentSL = null;
		$CurrentZSL = null;
		$netValue = toAnsi('НЕТ', true);

		while ($pers = $result_pers->_fetch_assoc()) {
			if ( empty($pers['ID_PERS']) ) {
				continue;
			}
			$PersonEvn_id = $pers['ID_PERS'] . '_' . $pers['NOVOR'];

			if ($CurrentPersonEvn_id == $PersonEvn_id) {
				continue; // если уже был такой, пропускаем
			}

			$CurrentPersonEvn_id = $PersonEvn_id;

			// некоторая обработка пациента
			$pers['DOST'] = array();
			$pers['DOST_P'] = array();

			if ( empty($pers['NOVOR']) || $pers['NOVOR'] == '0' ) {
				$i = 0;

				if ( empty($pers['FAM']) ) {
					$i++;
					$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 2);
				}

				if ( empty($pers['IM']) ) {
					$i++;
					$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 3);
				}

				if ( empty($pers['OT']) || mb_strtoupper($pers['OT']) == $netValue ) {
					$i++;
					$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 1);
				}

				if ($i == 0) {
					$i++;
					$pers['DOST'][] = array('DOST_N' => $i, 'DOST_CODE' => 3);
			}
			}
			else {
				$i = 0;

				if ( empty($pers['FAM_P']) ) {
					$i++;
					$pers['DOST_P'][] = array('DOST_P_N' => $i, 'DOST_P_CODE' => 2);
				}

				if ( empty($pers['IM_P']) ) {
					$i++;
					$pers['DOST_P'][] = array('DOST_P_N' => $i, 'DOST_P_CODE' => 3);
				}

				if ( empty($pers['OT_P']) || mb_strtoupper($pers['OT_P']) == $netValue ) {
					$i++;
					$pers['DOST_P'][] = array('DOST_P_N' => $i, 'DOST_P_CODE' => 1);
				}
			}

			try {
				if (!empty($CurrentZSL) && $CurrentZSL['PersonEvn_id'] . '_' . $CurrentZSL['NOVOR'] == $CurrentPersonEvn_id) {
					$key = $CurrentPersonEvn_id . '_' . $CurrentZSL['PR_NOV'];
					if (!array_key_exists($key, $ZAP)) {
						$this->_N_ZAP++;

						$pers['N_ZAP'] = $this->_N_ZAP;
						$ZAP[$key] = $pers;
						$ZAP[$key]['PR_NOV'] = $CurrentZSL['PR_NOV'];
					}

					if (!empty($pers['OS_SLUCH'])) {
						$CurrentZSL['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH']);
					}
					if (!empty($pers['OS_SLUCH1'])) {
						$CurrentZSL['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH1']);
					}

					// обработка SL
					if (!empty($CurrentSL) && $CurrentSL['Evn_rid'] == $CurrentEvn_rid && $CurrentSL['NOVOR'] == $CurrentZSL['NOVOR']) {
						if ($exportByUsl) {
							// постобработка
							$sls = $this->_exportByUsl2018($CurrentSL);

							foreach ($sls as $onesl) {
								$Registry_EvnNum[$onesl['SL_ID']] = array(
									'e' => $onesl['MaxEvn_id'],
									'r' => $onesl['Registry_id'],
									'n' => $ZAP[$key]['N_ZAP'],
								);
								$CurrentZSL['SL'][] = $onesl;
							}
						}
						else {
							$Registry_EvnNum[$CurrentSL['SL_ID']] = array(
								'e' => $CurrentSL['MaxEvn_id'],
								'r' => $CurrentSL['Registry_id'],
								'n' => $ZAP[$key]['N_ZAP'],
							);
							$CurrentZSL['SL'][] = $CurrentSL;
						}

						$CurrentSL = null;
					}

					if ( empty($CurrentSL) ) {
						// ищем случаи для пациента
						while ( $sluch = $result_sl->_fetch_assoc() ) {
							$this->_SL_ID++;

							$CurrentSL = $sluch;
							$CurrentSL['SL_ID'] = $this->_SL_ID;
							$CurrentSL['USL'] = array();
							$CurrentSL['DS2_DATA'] = array();
							$CurrentSL['DS3_DATA'] = array();
							$CurrentSL['KSG_KPG_DATA'] = array();
							$CurrentSL['NAZR_DATA'] = array();
							$CurrentSL['ONK_SL_DATA'] = array();
							$CurrentSL['SANK'] = array();

							$hasOnkoDS2 = false;

							// Привязываем DS2
							if ( isset($DS2[$sluch['MaxEvn_id']]) ) {
								foreach ( $DS2[$sluch['MaxEvn_id']] as $k => $one_rec ) {
									$CurrentSL['DS2_DATA'][] = $one_rec;

									if ( !empty($one_rec['DS2_CODE']) && substr($one_rec['DS2_CODE'], 0, 1) == 'C' ) {
										$hasOnkoDS2 = true;
									}
								}
								unset($DS2[$sluch['MaxEvn_id']]);
							}

							// Привязываем DS3
							if ( isset($DS3[$sluch['MaxEvn_id']]) ) {
								foreach ( $DS3[$sluch['MaxEvn_id']] as $k => $one_rec ) {
									$CurrentSL['DS3_DATA'][] = $one_rec;
								}
								unset($DS3[$sluch['MaxEvn_id']]);
							}

							// Привязываем NAZR
							if ( isset($NAZR[$sluch['MaxEvn_id']]) ) {
								foreach ( $NAZR[$sluch['MaxEvn_id']] as $k => $one_rec ) {
									$CurrentSL['NAZR_DATA'][] = $one_rec;
								}
								unset($NAZR[$sluch['MaxEvn_id']]);
							}

							// KSG_KPG
							$KSG_KPG_DATA = array();

							foreach ( $KSG_KPG_DATA_FIELDS as $field ) {
								if ( isset($CurrentSL[$field]) ) {
									$KSG_KPG_DATA[$field] = $CurrentSL[$field];
								}
							}

							if ( count($KSG_KPG_DATA) > 0 ) {
								$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

								if ( isset($KSLP[$sluch['MaxEvn_id']]) ) {
									foreach ( $KSLP[$sluch['MaxEvn_id']] as $k => $one_rec ) {
										$KSG_KPG_DATA['SL_KOEF_DATA'][] = $one_rec;
									}
									unset($KSLP[$sluch['MaxEvn_id']]);
								}

								$CurrentSL['KSG_KPG_DATA'][] = $KSG_KPG_DATA;
							}

							// ONK_SL
							if (
								(empty($CurrentSL['DS_ONK']) || $CurrentSL['DS_ONK'] != 1)
								&& (
									(!empty($CurrentSL['DS1']) && substr($CurrentSL['DS1'], 0, 1) == 'C')
									|| (empty($CurrentSL['DS1']) && $hasOnkoDS2 === true)
								)
							) {
								$ONK_SL_DATA = array();

								foreach ( $ONK_SL_DATA_FIELDS as $field ) {
									if ( isset($CurrentSL[$field]) ) {
										$ONK_SL_DATA[$field] = $CurrentSL[$field];
									}
								}

								if ( count($ONK_SL_DATA) > 0 ) {
									$ONK_SL_DATA['B_DIAG_DATA'] = array();
									$ONK_SL_DATA['B_PROT_DATA'] = array();

									if ( isset($BDIAG[$sluch['MaxEvn_id']]) ) {
										foreach ( $BDIAG[$sluch['MaxEvn_id']] as $k => $one_rec ) {
											$ONK_SL_DATA['B_DIAG_DATA'][] = $one_rec;
										}
										unset($BDIAG[$sluch['MaxEvn_id']]);
									}

									if ( isset($BPROT[$sluch['MaxEvn_id']]) ) {
										foreach ( $BPROT[$sluch['MaxEvn_id']] as $k => $one_rec ) {
											$ONK_SL_DATA['B_PROT_DATA'][] = $one_rec;
										}
										unset($BPROT[$sluch['MaxEvn_id']]);
									}

									$CurrentSL['ONK_SL_DATA'][] = $ONK_SL_DATA;
								}
							}

							// Привязываем услуги
							if ( isset($USL[$sluch['MaxEvn_id']]) ) {
								foreach ( $USL[$sluch['MaxEvn_id']] as $k => $usluga ) {
									$this->_IDSERV++;
									$usluga['IDSERV'] = $this->_IDSERV;

									if ( !empty($usluga['Evn_id']) ) {
										// Привязываем NAPR
										if ( !empty($sluch['DS_ONK']) && $sluch['DS_ONK'] == 1 && isset($NAPR[$usluga['Evn_id']]) ) {
											$usluga['NAPR_DATA'] = $NAPR[$usluga['Evn_id']];
											unset($NAPR[$usluga['Evn_id']]);
										}

										// Привязываем ONK_USL
										if ( (empty($sluch['DS_ONK']) || $sluch['DS_ONK'] != 1) && count($CurrentSL['ONK_SL_DATA']) > 0 && isset($ONK_USL[$usluga['Evn_id']]) ) {
											$usluga['ONK_USL_DATA'] = $ONK_USL[$usluga['Evn_id']];
											unset($ONK_USL[$usluga['Evn_id']]);
										}
									}

									if ( $increaseUslByKol ) {
										// постобработка
										$usls = $this->_increaseUslByKol2018($usluga);

										foreach ( $usls as $oneusl ) {
											$CurrentSL['USL'][] = $oneusl;
										}
									}
									else {
										$CurrentSL['USL'][] = $usluga;
									}
								}

								unset($USL[$sluch['MaxEvn_id']]);
							}

							if ($CurrentSL['Evn_rid'] == $CurrentEvn_rid && $CurrentSL['NOVOR'] == $CurrentZSL['NOVOR']) {
								if ($exportByUsl) {
									// постобработка
									$sls = $this->_exportByUsl2018($CurrentSL);

									foreach ($sls as $onesl) {
										$Registry_EvnNum[$onesl['SL_ID']] = array(
											'e' => $onesl['MaxEvn_id'],
											'r' => $onesl['Registry_id'],
											'n' => $ZAP[$key]['N_ZAP'],
										);
										$CurrentZSL['SL'][] = $onesl;
									}
								}
								else {
									$Registry_EvnNum[$CurrentSL['SL_ID']] = array(
										'e' => $CurrentSL['MaxEvn_id'],
										'r' => $CurrentSL['Registry_id'],
										'n' => $ZAP[$key]['N_ZAP'],
									);
									$CurrentZSL['SL'][] = $CurrentSL;
								}
							}
							else {
								break;
							}
						}
					}

					$ZAP[$key]['ID_PAC'] = $key;
					$ZAP[$key]['Z_SL'][] = $CurrentZSL;

					$CurrentZSL = null;
				}

				if ( empty($CurrentZSL) ) {
					// ищем законченные случаи для пациента
					while ( $zak_sluch = $result_zsl->_fetch_assoc() ) {
						$this->_IDCASE++;
						$this->_ZSL++;

						$CurrentZSL = $zak_sluch;
						$CurrentZSL['IDCASE'] = $this->_IDCASE;
						$CurrentZSL['OS_SLUCH'] = array();
						$CurrentZSL['SL'] = array();

						$CurrentEvn_rid = $zak_sluch['Evn_rid'];

						if ( $CurrentZSL['PersonEvn_id'] . '_' . $CurrentZSL['NOVOR'] == $CurrentPersonEvn_id) {
							$key = $CurrentPersonEvn_id . '_' . $CurrentZSL['PR_NOV'];

							if ( !array_key_exists($key, $ZAP) ) {
								$this->_N_ZAP++;

								$pers['N_ZAP'] = $this->_N_ZAP;
								$ZAP[$key] = $pers;
								$ZAP[$key]['PR_NOV'] = $CurrentZSL['PR_NOV'];
							}

							if ( !empty($pers['OS_SLUCH']) ) {
								$CurrentZSL['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH']);
							}

							if ( !empty($pers['OS_SLUCH1']) ) {
								$CurrentZSL['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $pers['OS_SLUCH1']);
							}

							// обработка SL
							if (!empty($CurrentSL) && $CurrentSL['Evn_rid'] == $CurrentEvn_rid && $CurrentSL['NOVOR'] == $CurrentZSL['NOVOR']) {
								if ($exportByUsl) {
									// постобработка
									$sls = $this->_exportByUsl2018($CurrentSL);

									foreach ($sls as $onesl) {
										$Registry_EvnNum[$onesl['SL_ID']] = array(
											'e' => $onesl['MaxEvn_id'],
											'r' => $onesl['Registry_id'],
											'n' => $ZAP[$key]['N_ZAP'],
										);
										$CurrentZSL['SL'][] = $onesl;
									}
								}
								else {
									$Registry_EvnNum[$CurrentSL['SL_ID']] = array(
										'e' => $CurrentSL['MaxEvn_id'],
										'r' => $CurrentSL['Registry_id'],
										'n' => $ZAP[$key]['N_ZAP'],
									);
									$CurrentZSL['SL'][] = $CurrentSL;
								}

								$CurrentSL = null;
							}

							if ( empty($CurrentSL) ) {
								// ищем случаи для пациента
								while ( $sluch = $result_sl->_fetch_assoc() ) {
									$this->_SL_ID++;

									$CurrentSL = $sluch;
									$CurrentSL['SL_ID'] = $this->_SL_ID;
									$CurrentSL['USL'] = array();
									$CurrentSL['DS2_DATA'] = array();
									$CurrentSL['DS3_DATA'] = array();
									$CurrentSL['KSG_KPG_DATA'] = array();
									$CurrentSL['NAZR_DATA'] = array();
									$CurrentSL['ONK_SL_DATA'] = array();
									$CurrentSL['SANK'] = array();

									$hasOnkoDS2 = false;

									// Привязываем DS2
									if ( isset($DS2[$sluch['MaxEvn_id']]) ) {
										foreach ( $DS2[$sluch['MaxEvn_id']] as $k => $one_rec ) {
											$CurrentSL['DS2_DATA'][] = $one_rec;

											if ( !empty($one_rec['DS2_CODE']) && substr($one_rec['DS2_CODE'], 0, 1) == 'C' ) {
												$hasOnkoDS2 = true;
											}
										}
										unset($DS2[$sluch['MaxEvn_id']]);
									}

									// Привязываем DS3
									if ( isset($DS3[$sluch['MaxEvn_id']]) ) {
										foreach ( $DS3[$sluch['MaxEvn_id']] as $k => $one_rec ) {
											$CurrentSL['DS3_DATA'][] = $one_rec;
										}
										unset($DS3[$sluch['MaxEvn_id']]);
									}

									// Привязываем NAZR
									if ( isset($NAZR[$sluch['MaxEvn_id']]) ) {
										foreach ( $NAZR[$sluch['MaxEvn_id']] as $k => $one_rec ) {
											$CurrentSL['NAZR_DATA'][] = $one_rec;
										}
										unset($NAZR[$sluch['MaxEvn_id']]);
									}

									// KSG_KPG
									$KSG_KPG_DATA = array();

									foreach ( $KSG_KPG_DATA_FIELDS as $field ) {
										if ( isset($CurrentSL[$field]) ) {
											$KSG_KPG_DATA[$field] = $CurrentSL[$field];
										}
									}

									if ( count($KSG_KPG_DATA) > 0 ) {
										$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

										if ( isset($KSLP[$sluch['MaxEvn_id']]) ) {
											foreach ( $KSLP[$sluch['MaxEvn_id']] as $k => $one_rec ) {
												$KSG_KPG_DATA['SL_KOEF_DATA'][] = $one_rec;
											}
											unset($KSLP[$sluch['MaxEvn_id']]);
										}

										$CurrentSL['KSG_KPG_DATA'][] = $KSG_KPG_DATA;
									}

									// ONK_SL
									if (
										(empty($CurrentSL['DS_ONK']) || $CurrentSL['DS_ONK'] != 1)
										&& (
											(!empty($CurrentSL['DS1']) && substr($CurrentSL['DS1'], 0, 1) == 'C')
											|| (empty($CurrentSL['DS1']) && $hasOnkoDS2 === true)
										)
									) {
										$ONK_SL_DATA = array();

										foreach ( $ONK_SL_DATA_FIELDS as $field ) {
											if ( isset($CurrentSL[$field]) ) {
												$ONK_SL_DATA[$field] = $CurrentSL[$field];
											}
										}

										if ( count($ONK_SL_DATA) > 0 ) {
											$ONK_SL_DATA['B_DIAG_DATA'] = array();
											$ONK_SL_DATA['B_PROT_DATA'] = array();

											if ( isset($BDIAG[$sluch['MaxEvn_id']]) ) {
												foreach ( $BDIAG[$sluch['MaxEvn_id']] as $k => $one_rec ) {
													$ONK_SL_DATA['B_DIAG_DATA'][] = $one_rec;
												}
												unset($BDIAG[$sluch['MaxEvn_id']]);
											}

											if ( isset($BPROT[$sluch['MaxEvn_id']]) ) {
												foreach ( $BPROT[$sluch['MaxEvn_id']] as $k => $one_rec ) {
													$ONK_SL_DATA['B_PROT_DATA'][] = $one_rec;
												}
												unset($BPROT[$sluch['MaxEvn_id']]);
											}

											$CurrentSL['ONK_SL_DATA'][] = $ONK_SL_DATA;
										}
									}

									// Привязываем услуги
									if ( isset($USL[$sluch['MaxEvn_id']]) ) {
										foreach ( $USL[$sluch['MaxEvn_id']] as $k => $usluga ) {
											$this->_IDSERV++;
											$usluga['IDSERV'] = $this->_IDSERV;

											if ( !empty($usluga['Evn_id']) ) {
												// Привязываем NAPR
												if ( !empty($sluch['DS_ONK']) && $sluch['DS_ONK'] == 1 && isset($NAPR[$usluga['Evn_id']]) ) {
													$usluga['NAPR_DATA'] = $NAPR[$usluga['Evn_id']];
													unset($NAPR[$usluga['Evn_id']]);
												}

												// Привязываем ONK_USL
												if ( (empty($sluch['DS_ONK']) || $sluch['DS_ONK'] != 1) && count($CurrentSL['ONK_SL_DATA']) > 0 && isset($ONK_USL[$usluga['Evn_id']]) ) {
													$usluga['ONK_USL_DATA'] = $ONK_USL[$usluga['Evn_id']];
													unset($ONK_USL[$usluga['Evn_id']]);
												}
											}

											if ( $increaseUslByKol ) {
												// постобработка
												$usls = $this->_increaseUslByKol2018($usluga);

												foreach ( $usls as $oneusl ) {
													$CurrentSL['USL'][] = $oneusl;
												}
											}
											else {
												$CurrentSL['USL'][] = $usluga;
											}
										}

										unset($USL[$sluch['MaxEvn_id']]);
									}

									if ($CurrentSL['Evn_rid'] == $CurrentEvn_rid  && $CurrentSL['NOVOR'] == $CurrentZSL['NOVOR']) {
										if ($exportByUsl) {
											// постобработка
											$sls = $this->_exportByUsl2018($CurrentSL);

											foreach ($sls as $onesl) {
												$Registry_EvnNum[$onesl['SL_ID']] = array(
													'e' => $onesl['MaxEvn_id'],
													'r' => $onesl['Registry_id'],
													'n' => $ZAP[$key]['N_ZAP'],
												);
												$CurrentZSL['SL'][] = $onesl;
											}
										}
										else {
											$Registry_EvnNum[$CurrentSL['SL_ID']] = array(
												'e' => $CurrentSL['MaxEvn_id'],
												'r' => $CurrentSL['Registry_id'],
												'n' => $ZAP[$key]['N_ZAP'],
											);
											$CurrentZSL['SL'][] = $CurrentSL;
										}
									}
									else {
										break;
									}
								}
							}

							$ZAP[$key]['ID_PAC'] = $key;
							$ZAP[$key]['Z_SL'][] = $CurrentZSL;
						}
						else {
							break;
						}
					}
				}
			}
			catch ( Exception $e ) {
				// вышли за пределы законченных случаев
			}

			if ( count($ZAP) >= 100 ) {
				$cntZAP = count($ZAP);

				foreach ( $ZAP as $k => $oneZAP ) {
					if ( array_key_exists('OS_SLUCH', $ZAP[$k]) ) {
						unset($ZAP[$k]['OS_SLUCH']);
					}

					if ( array_key_exists('OS_SLUCH1', $ZAP[$k]) ) {
						unset($ZAP[$k]['OS_SLUCH1']);
					}

					if ( !array_key_exists('Z_SL', $oneZAP) || count($oneZAP['Z_SL']) == 0 ) {
						array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
						writeToErrorLog($error_log, "Запись без Z_SL" . PHP_EOL
							. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
							. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
							. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
							//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
						);
						unset($ZAP[$k]);
					}
					else {
						$oneZAPConverted = false;

						foreach ( $oneZAP['Z_SL'] as $j => $oneZSL ) {
							if ( !array_key_exists('SL', $oneZSL) || count($oneZSL['SL']) == 0 ) {
								$this->_ZSL--;
								if ( $oneZAPConverted === false ) {
									array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
									$oneZAPConverted = true;
								}
								//array_walk_recursive($oneZSL, 'ConvertFromWin1251ToUTF8', true);
								writeToErrorLog($error_log, "Z_SL без SL" . PHP_EOL
									. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
									. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
									. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
									. "IDCASE: " . $oneZSL['IDCASE'] . PHP_EOL
									. "ID случая: " . $oneZSL['MaxEvn_id'] . PHP_EOL
									. "Дата начала случая: " . $oneZSL['DATE_Z_1'] . PHP_EOL
									. "Дата окончания случая: " . $oneZSL['DATE_Z_2'] . PHP_EOL
									//. "Информация о случае: " . json_encode($oneZSL) . PHP_EOL
								);
								unset($ZAP[$k]['Z_SL'][$j]);
							}
						}

						if ( count($ZAP[$k]['Z_SL']) == 0 ) {
							if ( $oneZAPConverted === false ) {
								array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
							}
							writeToErrorLog($error_log, "Запись без Z_SL" . PHP_EOL
								. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
								. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
								. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
								//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
							);
							unset($ZAP[$k]);
						}
					}
				}

				// пишем в файл
				// array_walk_recursive($ZAP, 'ConvertFromUTF8ToWin1251', true);
				$this->textlog->add('Сформирован пакет на ' . $cntZAP . ' записей, памяти задействовано: ' . (memory_get_usage()/1024/1024));
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $xml_file, array('ZAP' => $ZAP), true, false, $altKeys);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили ' . $cntZAP . ' записей ZAP за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/registry_penza_person_body', array('PACIENT' => $ZAP), true);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили ' . $cntZAP . ' записей PACIENT за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_pers_data_name, $xml, FILE_APPEND);
				unset($xml);

				unset($ZAP);
				$ZAP = array();

				$this->textlog->add('Обнулили переменные, памяти задействовано: ' . (memory_get_usage()/1024/1024));
			}
		}

		if ( count($ZAP) > 0 ) {
			$cntZAP = count($ZAP);

			foreach ( $ZAP as $k => $oneZAP ) {
				if ( array_key_exists('OS_SLUCH', $ZAP[$k]) ) {
					unset($ZAP[$k]['OS_SLUCH']);
				}

				if ( array_key_exists('OS_SLUCH1', $ZAP[$k]) ) {
					unset($ZAP[$k]['OS_SLUCH1']);
				}

				if ( !array_key_exists('Z_SL', $oneZAP) || count($oneZAP['Z_SL']) == 0 ) {
					array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
					writeToErrorLog($error_log, "Запись без Z_SL" . PHP_EOL
						. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
						. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
						. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
						//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
					);
					unset($ZAP[$k]);
				}
				else {
					$oneZAPConverted = false;

					foreach ( $oneZAP['Z_SL'] as $j => $oneZSL ) {
						if ( !array_key_exists('SL', $oneZSL) || count($oneZSL['SL']) == 0 ) {
							$this->_ZSL--;
							if ( $oneZAPConverted === false ) {
								array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
								$oneZAPConverted = true;
							}
							//array_walk_recursive($oneZSL, 'ConvertFromWin1251ToUTF8', true);
							writeToErrorLog($error_log, "Z_SL без SL" . PHP_EOL
								. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
								. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
								. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
								. "IDCASE: " . $oneZSL['IDCASE'] . PHP_EOL
								. "ID случая: " . $oneZSL['MaxEvn_id'] . PHP_EOL
								. "Дата начала случая: " . $oneZSL['DATE_Z_1'] . PHP_EOL
								. "Дата окончания случая: " . $oneZSL['DATE_Z_2'] . PHP_EOL
								//. "Информация о случае: " . json_encode($oneZSL) . PHP_EOL
							);
							unset($ZAP[$k]['Z_SL'][$j]);
						}
					}

					if ( count($ZAP[$k]['Z_SL']) == 0 ) {
						if ( $oneZAPConverted === false ) {
							array_walk_recursive($oneZAP, 'ConvertFromWin1251ToUTF8', true);
						}
						writeToErrorLog($error_log, "Запись без Z_SL" . PHP_EOL
							. "Реестр № " . $this->_unionRegistryData[$oneZAP['Registry_id']]['Registry_Num'] . ", Registry_id = " . $oneZAP['Registry_id'] . ", тип реестра: " . $this->_unionRegistryData[$oneZAP['Registry_id']]['RegistryType_Name'] . PHP_EOL
							. "ФИО пациента: " . $oneZAP['FAM'] . ' ' . $oneZAP['IM'] . ' ' . $oneZAP['OT'] . ", ДР: " . $oneZAP['DR'] . PHP_EOL
							. "Номер полиса: " . $oneZAP['NPOLIS'] . PHP_EOL
							//. "Информация о случае: " . json_encode($oneZAP) . PHP_EOL
						);
						unset($ZAP[$k]);
					}
				}
			}

			// пишем в файл
			// array_walk_recursive($ZAP, 'ConvertFromUTF8ToWin1251', true);
			$this->textlog->add('Сформирован пакет на ' . $cntZAP . ' записей, памяти задействовано: ' . (memory_get_usage()/1024/1024));
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $xml_file, array('ZAP' => $ZAP), true, false, $altKeys);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . $cntZAP . ' записей ZAP за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/registry_penza_person_body', array('PACIENT' => $ZAP), true);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . $cntZAP . ' записей PACIENT за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_pers_data_name, $xml, FILE_APPEND);
			unset($xml);

			unset($ZAP);

			$this->textlog->add('Обнулили переменные, памяти задействовано: ' . (memory_get_usage()/1024/1024));
		}

		return array('ZSL' => $this->_ZSL, 'N_ZAP' => $this->_N_ZAP, 'SL_ID' => $this->_SL_ID);
	}
	
	/**
	 *	Получение данных для выгрузки реестра в XML с использованием контроля по объему (?)
	 */
	function loadRegistryDataForXmlCheckVolumeUsing($type, $data)
	{
		$query = "
			exec [{$this->scheme}].[p_RegistryPL_expRE] @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');
			foreach ($header as $key=>$value)
			{
				$header[$key]['SUM'] = number_format($header[$key]['SUM'], 2, '.', '');
			}
		}
		else {
			return false;
		}
		
		// код ЛПУ
		$query = "
			select
				top 1 lpu_f003mcod
			from
				Lpu with (nolock)
			where
				Lpu_id = ?
		";		
		$result = $this->db->query($query, array($data['Lpu_id']));

		if ( is_object($result) ) {
			$result = $result->result('array');
			$lpu_code = $result[0]['lpu_f003mcod'];
		}
		else {
			return false;
		}		
		
		return array('registry_data' => $header, 'lpu_code' => $lpu_code);
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

		$this->setRegistryParamsByType($data);

		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id']==5))
		{
			$query = "
			Select 
				R.RegistryQueue_id as Registry_id,
				R.OrgSmo_id,
				R.KatNasel_id,
				R.DispClass_id,
				R.Registry_IsAddAcc,
				R.Registry_IsOnceInTwoYears,
				DispClass.DispClass_Name,
				kn.KatNasel_Name,
				R.RegistryType_id,
				5 as RegistryStatus_id,
				2 as Registry_IsActive,
				RTrim(R.Registry_Num)+' / в очереди: '+LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
				OrgSmo.OrgSmo_Nick as OrgSmo_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				0 as Registry_Count,
				0 as Registry_ErrorCount,
				0 as Registry_Sum,
				1 as Registry_IsProgress,
				1 as Registry_IsNeedReform,
				'' as Registry_updDate,
				PayType.PayType_Name,
				PayType.PayType_id
			from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
				left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = R.OrgSmo_id
				left join v_KatNasel kn with (NOLOCK) on kn.KatNasel_id = r.KatNasel_id
				left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
				left join v_PayType PayType (nolock) on PayType.PayType_id = R.PayType_id
			where {$filter}";
		}
		else 
		{
			if (isset($data['RegistryStatus_id']))
			{
				$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
				$params['RegistryStatus_id'] = $data['RegistryStatus_id'];

				// только если оплаченные!!!
				if ( 4 == (int)$data['RegistryStatus_id'] ) {
					if ( $data['Registry_accYear'] > 0 ) {
						$filter .= ' and YEAR(R.Registry_begDate) <= :Registry_accYear';
						$filter .= ' and YEAR(R.Registry_endDate) >= :Registry_accYear';
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}
			$query = "
			Select 
				R.Registry_id,
				R.OrgSmo_id,
				R.KatNasel_id,
				R.DispClass_id,
				DispClass.DispClass_Name,
				kn.KatNasel_Name,
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.Registry_IsActive,
				R.Registry_IsAddAcc,
				R.Registry_IsOnceInTwoYears,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				RTrim(R.Registry_Num) as Registry_Num,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
				--R.Registry_Sum,
				R.OrgSmo_Name as OrgSmo_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				isnull(R.Registry_RecordCount, 0) as Registry_Count,
				isnull(R.Registry_ErrorCount, 0) as Registry_ErrorCount,
				isnull(R.Registry_Sum, 0.00) as Registry_Sum,
				isnull(R.Registry_xmlExportPath,'') as Registry_xmlExportPath,
				case when (RQ.RegistryQueueHistory_id is not null) and (RQ.RegistryQueueHistory_endDT is null) then 1 else 0 end as Registry_IsProgress,
				convert(varchar(10), R.Registry_updDT, 104) + ' ' + convert(varchar(8), R.Registry_updDT, 108) as Registry_updDate,
				-- количество записей в таблицах RegistryError, RegistryPerson, RegistryNoPolis, RegistryErrorTFOMS
				0 as RegistryErrorCom_IsData,
				RegistryError.RegistryError_IsData,
				RegistryPerson.RegistryPerson_IsData,
				RegistryNoPolis.RegistryNoPolis_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMSType_id,
				0 as RegistryNoPay_IsData,
				0 as RegistryNoPay_Count,
				0 as RegistryNoPay_UKLSum,
				0 as RegistryNoPaid_Count,
				null as ReformTime,
				null as RegistryCheckStatus_id,
				null as RegistryCheckStatus_Code,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				PayType.PayType_Name,
				PayType.PayType_id
			from {$this->scheme}.v_Registry R with (NOLOCK)
				left join v_KatNasel kn with (NOLOCK) on kn.KatNasel_id = r.KatNasel_id
				left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
				left join v_PayType PayType (nolock) on PayType.PayType_id = R.PayType_id
				outer apply(
					select top 1
						RegistryQueueHistory_id,
						RegistryQueueHistory_endDT,
						RegistryQueueHistory.Registry_id
					from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
					where RegistryQueueHistory.Registry_id = R.Registry_id
					order by RegistryQueueHistory_id desc
				) RQ
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id and ISNULL(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1) RegistryPerson
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_{$this->RegistryNoPolisObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
				outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorTFOMS
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
	 *	Установка реестра в очередь на формирование 
	 *	Возвращает номер в очереди 
	 */
	function saveRegistryQueue($data) 
	{
		// Сохранение нового реестра
		if (0 == $data['Registry_id']) 
		{
			$data['Registry_IsActive']=2;
			$operation = 'insert';
		}
		else
		{
			$operation = 'update';
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

		$this->deleteRegistryErrorTFOMS($data);
		
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
			'DispClass_id' => $data['DispClass_id'],
			'KatNasel_id' => $data['KatNasel_id'],
			'PayType_id' => $data['PayType_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'Registry_IsAddAcc' => $data['Registry_IsAddAcc'],
			'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
			'pmUser_id' => $data['pmUser_id']
		);
		$fields = "";

		if ( in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
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
					@OrgRSchet_id = :OrgRSchet_id,
					@DispClass_id = :DispClass_id,
					@KatNasel_id = :KatNasel_id,
					@PayType_id = :PayType_id,
					{$fields}
					@Registry_Num = :Registry_Num,
					@Registry_accDate = @curdate, 
					@RegistryStatus_id = :RegistryStatus_id,
					@Registry_IsAddAcc = :Registry_IsAddAcc,
					@Registry_IsOnceInTwoYears = :Registry_IsOnceInTwoYears,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @RegistryQueue_id as RegistryQueue_id, @RegistryQueue_Position as RegistryQueue_Position, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				Lpu_id,
				RegistryType_id,
				RegistryStatus_id,
				convert(varchar(10), Registry_begDate, 120) as Registry_begDate,
				convert(varchar(10), Registry_endDate, 120) as Registry_endDate,
				KatNasel_id,
				DispClass_id,
				PayType_id,
				Registry_Num,
				Registry_IsActive,
				Registry_IsAddAcc,
				Registry_IsOnceInTwoYears,
				OrgRSchet_id,
				convert(varchar(10), Registry_accDate, 120) as Registry_accDate
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
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['OrgRSchet_id'] = $row[0]['OrgRSchet_id'];
				$data['KatNasel_id'] = $row[0]['KatNasel_id'];
				$data['DispClass_id'] = $row[0]['DispClass_id'];
				$data['PayType_id'] = $row[0]['PayType_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];
				$data['Registry_IsAddAcc'] = $row[0]['Registry_IsAddAcc'];
				$data['Registry_IsOnceInTwoYears'] = $row[0]['Registry_IsOnceInTwoYears'];

				// Постановка реестра в очередь 
				return  $this->saveRegistryQueue($data);
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
		$this->setRegistryParamsByType($data);

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
			Delete from {$this->scheme}.v_{$this->RegistryErrorObject}
			where {$filter};
		";
		$result = $this->db->query($query, $params);
		return true;
	}
	
	/**
	 *	Проверка
	 */
	function checkErrorDataInRegistry($data) {
		$this->setRegistryParamsByType($data);

		$query = "Select rd.Registry_id from {$this->scheme}.v_{$this->RegistryDataObject} rd	where rd.Registry_id = :Registry_id  and (rd.Evn_id = :IDCASE OR :IDCASE IS NULL) and (rd.Person_id = :ID_PERS OR :ID_PERS IS NULL)";

		$params['Registry_id'] = $data['Registry_id'];
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
	 * Загрузка необходимых для импорта данных по реестру
	 */
	function loadRegistryForImport($data) {
		$query = "
			select
				Registry_id,
				Registry_EvnNum,
				Registry_xmlExportPath,
				convert(varchar(10), Registry_begDate, 120) as Registry_begDate
			from
				{$this->scheme}.v_Registry (nolock)
			where
				Registry_id = :Registry_id
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id']
		));
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 * Получаем список всех случаев в реестре, для импорта
	 */
	function getRegistryDataForImport($data) {
		$resp = $this->queryResult("
			select
				rd.Evn_id,
				rd.Registry_id,
				convert(varchar(10), rd.Evn_setDate, 120) as Evn_setDate,
				convert(varchar(10), rd.Evn_disDate, 120) as Evn_disDate,
				d.Diag_Code,
				rd.PersonEvn_id,
				rd.Person_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
				left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
			where
				rgl.Registry_pid = :Registry_id
				and ISNULL(rd.RegistryData_deleted, 1) = 1
				
			union all
			
			select
				rd.Evn_id,
				rd.Registry_id,
				convert(varchar(10), rd.Evn_setDate, 120) as Evn_setDate,
				convert(varchar(10), rd.Evn_disDate, 120) as Evn_disDate,
				d.Diag_Code,
				rd.PersonEvn_id,
				rd.Person_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_RegistryDataEvnPS rd (nolock) on rd.Registry_id = rgl.Registry_id
				left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
			where
				rgl.Registry_pid = :Registry_id
				and ISNULL(rd.RegistryData_deleted, 1) = 1
				
			union all
			
			select
				rd.Evn_id,
				rd.Registry_id,
				convert(varchar(10), rd.Evn_setDate, 120) as Evn_setDate,
				convert(varchar(10), rd.Evn_disDate, 120) as Evn_disDate,
				d.Diag_Code,
				rd.PersonEvn_id,
				rd.Person_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_RegistryDataDisp rd (nolock) on rd.Registry_id = rgl.Registry_id
				left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
			where
				rgl.Registry_pid = :Registry_id
				and ISNULL(rd.RegistryData_deleted, 1) = 1
				
			union all
			
			select
				rd.Evn_id,
				rd.Registry_id,
				convert(varchar(10), rd.Evn_setDate, 120) as Evn_setDate,
				convert(varchar(10), rd.Evn_disDate, 120) as Evn_disDate,
				d.Diag_Code,
				rd.PersonEvn_id,
				rd.Person_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_RegistryDataProf rd (nolock) on rd.Registry_id = rgl.Registry_id
				left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
			where
				rgl.Registry_pid = :Registry_id
				and ISNULL(rd.RegistryData_deleted, 1) = 1
				
			union all
			
			select
				rd.Evn_id,
				rd.Registry_id,
				convert(varchar(10), rd.Evn_setDate, 120) as Evn_setDate,
				convert(varchar(10), rd.Evn_disDate, 120) as Evn_disDate,
				d.Diag_Code,
				rd.PersonEvn_id,
				rd.Person_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.v_RegistryDataPar rd (nolock) on rd.Registry_id = rgl.Registry_id
				left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
			where
				rgl.Registry_pid = :Registry_id
				and ISNULL(rd.RegistryData_deleted, 1) = 1
				
			union all
			
			select
				rd.CmpCloseCard_id as Evn_id,
				rd.Registry_id,
				convert(varchar(10), rd.Evn_setDate, 120) as Evn_setDate,
				convert(varchar(10), rd.Evn_disDate, 120) as Evn_disDate,
				d.Diag_Code,
				null as PersonEvn_id, -- не хранится, будем определять по Person_id для СМП тогда
				rd.Person_id
			from
				{$this->scheme}.v_RegistryGroupLink rgl (nolock)
				inner join {$this->scheme}.RegistryDataCmp rd (nolock) on rd.Registry_id = rgl.Registry_id
				left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
			where
				rgl.Registry_pid = :Registry_id
				and ISNULL(rd.RegistryDataCmp_deleted, 1) = 1
		", array(
			'Registry_id' => $data['Registry_id']
		));

		$response = array();

		foreach($resp as $key => $value) {
			if (!empty($value['PersonEvn_id'])) {
				$response['pe_' . $value['PersonEvn_id'] . '_' . $value['Evn_setDate'] . '_' . $value['Evn_disDate'] . '_' . $value['Diag_Code']] = array(
					'Registry_id' => $value['Registry_id'],
					'Evn_id' => $value['Evn_id'],
					'cnt' => 0,
				);
			} else {
				$response['p_' . $value['Person_id'] . '_' . $value['Evn_setDate'] . '_' . $value['Evn_disDate'] . '_' . $value['Diag_Code']] = array(
					'Registry_id' => $value['Registry_id'],
					'Evn_id' => $value['Evn_id'],
					'cnt' => 0,
				);
			}
			unset($resp[$key]);
		}

		return $response;
	}

	/**
	 *	Проверка
	 */
	function checkTFOMSErrorDataInRegistry($data, $Registry_EvnNum = array()) {
		if (!empty($data['IDCASE'])) {
			if (!empty($Registry_EvnNum[$data['IDCASE']])) {
				if (is_array($Registry_EvnNum[$data['IDCASE']])) {
					if (isset($Registry_EvnNum[$data['IDCASE']]['e'])) {
						return array(
							'Evn_id' => $Registry_EvnNum[$data['IDCASE']]['e'],
							'Registry_id' => $Registry_EvnNum[$data['IDCASE']]['r']
						);
					} else {
						return array(
							'Evn_id' => $Registry_EvnNum[$data['IDCASE']]['Evn_id'],
							'Registry_id' => $Registry_EvnNum[$data['IDCASE']]['Registry_id']
						);
					}
				}
			}
		} else if (!empty($data['PersonEvn_id']) && !empty($data['Registry_id']) && !empty($data['DATE_1'])) {
			// более сложная схема, определяем случай по PersonEvn_id/DATE_1/DATE_2/DS1.
			$filters = "";
			if (!empty($data['DATE_2'])) {
				$filters .= " and rd.Evn_disDate = :Evn_disDate";
			}
			if (!empty($data['DS1'])) {
				$filters .= " and d.Diag_Code = :Diag_Code";
			}

			$resp = $this->queryResult("
				select top 1
					rd.Evn_id,
					rd.Registry_id
				from
					{$this->scheme}.v_RegistryGroupLink rgl (nolock)
					inner join {$this->scheme}.RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
					left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
				where
					rgl.Registry_pid = :Registry_id
					and rd.PersonEvn_id = :PersonEvn_id
					and rd.Evn_setDate = :Evn_setDate
					and ISNULL(rd.RegistryData_deleted, 1) = 1
					{$filters}
					
				union all
				
				select top 1
					rd.CmpCloseCard_id as Evn_id,
					rd.Registry_id
				from
					{$this->scheme}.v_RegistryGroupLink rgl (nolock)
					inner join {$this->scheme}.RegistryDataCmp rd (nolock) on rd.Registry_id = rgl.Registry_id
					inner join v_PersonEvn pe (nolock) on pe.Person_id = rd.Person_id
					left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
				where
					rgl.Registry_pid = :Registry_id
					and pe.PersonEvn_id = :PersonEvn_id
					and rd.Evn_setDate = :Evn_setDate
					and ISNULL(rd.RegistryDataCmp_deleted, 1) = 1
					{$filters}
			", array(
				'Registry_id' => $data['Registry_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Evn_setDate' => $data['DATE_1'],
				'Evn_disDate' => !empty($data['DATE_2'])?$data['DATE_2']:null,
				'Diag_Code' => !empty($data['DS1'])?$data['DS1']:null
			));

			if (!empty($resp[0])) {
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 *	Проверка
	 */
	public function checkTFOMSErrorDataInRegistry2018($data, $Registry_EvnNum = array()) {
		if (!empty($data['SL_ID'])) {
			if (!empty($Registry_EvnNum[$data['SL_ID']])) {
				if (is_array($Registry_EvnNum[$data['SL_ID']])) {
					if (isset($Registry_EvnNum[$data['SL_ID']]['e'])) {
						return array(
							'Evn_id' => $Registry_EvnNum[$data['SL_ID']]['e'],
							'Registry_id' => $Registry_EvnNum[$data['SL_ID']]['r']
						);
					} else {
						return array(
							'Evn_id' => $Registry_EvnNum[$data['SL_ID']]['Evn_id'],
							'Registry_id' => $Registry_EvnNum[$data['SL_ID']]['Registry_id']
						);
					}
				}
			}
		} else if (!empty($data['PersonEvn_id']) && !empty($data['Registry_id']) && !empty($data['DATE_1'])) {
			// более сложная схема, определяем случай по PersonEvn_id/DATE_1/DATE_2/DS1.
			$filters = "";
			if (!empty($data['DATE_2'])) {
				$filters .= " and rd.Evn_disDate = :Evn_disDate";
			}
			if (!empty($data['DS1'])) {
				$filters .= " and d.Diag_Code = :Diag_Code";
			}

			$resp = $this->queryResult("
				select top 1
					rd.Evn_id,
					rd.Registry_id
				from
					{$this->scheme}.v_RegistryGroupLink rgl (nolock)
					inner join {$this->scheme}.RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
					left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
				where
					rgl.Registry_pid = :Registry_id
					and rd.PersonEvn_id = :PersonEvn_id
					and rd.Evn_setDate = :Evn_setDate
					and ISNULL(rd.RegistryData_deleted, 1) = 1
					{$filters}
					
				union all
				
				select top 1
					rd.CmpCloseCard_id as Evn_id,
					rd.Registry_id
				from
					{$this->scheme}.v_RegistryGroupLink rgl (nolock)
					inner join {$this->scheme}.RegistryDataCmp rd (nolock) on rd.Registry_id = rgl.Registry_id
					inner join v_PersonEvn pe (nolock) on pe.Person_id = rd.Person_id
					left join v_Diag d (nolock) on d.Diag_id = rd.Diag_id
				where
					rgl.Registry_pid = :Registry_id
					and pe.PersonEvn_id = :PersonEvn_id
					and rd.Evn_setDate = :Evn_setDate
					and ISNULL(rd.RegistryDataCmp_deleted, 1) = 1
					{$filters}
			", array(
				'Registry_id' => $data['Registry_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Evn_setDate' => $data['DATE_1'],
				'Evn_disDate' => !empty($data['DATE_2'])?$data['DATE_2']:null,
				'Diag_Code' => !empty($data['DS1'])?$data['DS1']:null
			));

			if (!empty($resp[0])) {
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 *	Проверка
	 */
	function checkTFOMSErrorDataInRegistryByNZAP($data, $Registry_EvnNum = array()) {
		if (!empty($data['N_ZAP'])) {
			$arr = array();

			// все случаи данного N_ZAP
			foreach ($Registry_EvnNum as $Evn_Num) {
				if (is_array($Evn_Num)) {
					if (!empty($Evn_Num['n']) && $Evn_Num['n'] == $data['N_ZAP']) {
						$arr[] = array(
							'Evn_id' => $Evn_Num['e'],
							'Registry_id' => $Evn_Num['r']
						);
					} else if (!empty($Evn_Num['N_ZAP']) && $Evn_Num['N_ZAP'] == $data['N_ZAP']) {
						$arr[] = array(
							'Evn_id' => $Evn_Num['Evn_id'],
							'Registry_id' => $Evn_Num['Registry_id']
						);
					}
				}
			}

			return $arr;
		}

		return false;
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
		return ',DispClass_id';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return ',DispClass_id';
	}
	
	/**
	 *	Снятие с посещения признака вхождения в реестр
	 */
	function setVizitNotInReg($Registry_id) 
	{
		// Проставляем ошибочные посещения обратно
		
		$params = array();
		$params['Registry_id'] = $Registry_id;

		$this->setRegistryParamsByType($params);

		if ($Registry_id>0)
		{
			$query = "
				update EvnVizit with (PAGLOCK)
				set EvnVizit_IsInReg = 1
				from {$this->scheme}.v_{$this->RegistryDataObject} rd with (NOLOCK)
					inner join {$this->scheme}.v_{$this->RegistryErrorObject} re on re.Evn_id = rd.Evn_id
						and re.Registry_id = rd.Registry_id
				where EvnVizit.EvnVizit_id = rd.Evn_id
					and rd.Registry_id = :Registry_id
				
				update EvnSection with (PAGLOCK)
				set EvnSection_IsInReg = 1
				from {$this->scheme}.v_{$this->RegistryDataObject} rd with (NOLOCK)
					inner join {$this->scheme}.v_{$this->RegistryErrorObject} re on re.Evn_id = rd.Evn_id and re.Registry_id = rd.Registry_id
				where EvnSection.EvnSection_id = rd.Evn_id
					and rd.Registry_id = :Registry_id
				
				update {$this->scheme}.Registry
				set Registry_ErrorCount = (
					select count(distinct rd.Evn_id)
					from {$this->scheme}.v_{$this->RegistryDataObject} rd with (NOLOCK)
						inner join {$this->scheme}.v_{$this->RegistryErrorObject} re with (NOLOCK) on re.Evn_id = rd.Evn_id
							and re.Registry_id = rd.Registry_id
					where rd.Registry_id = :Registry_id
				)
				where Registry_id = :Registry_id
				
				update {$this->scheme}.v_{$this->RegistryDataObject}
				set Paid_id = (case when re.RegistryError_Count > 0 then 1 else 2 end)
				from {$this->scheme}.v_{$this->RegistryDataObject} rd
					outer apply (
						select count(*) as RegistryError_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} re with (nolock)
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
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
		}
	}

	/**
	 * Установка ошибок
	 */
	function setErrorFromImportRegistry($d, $data) 
	{
		// Сохранение загружаемого реестра, точнее его ошибок 
		$this->setRegistryParamsByType($data);

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
							Insert {$this->scheme}.v_{$this->RegistryErrorObject} (Registry_id, Evn_id, RegistryErrorType_id, LpuSection_id, pmUser_insID, pmUser_updID, RegistryError_insDT, RegistryError_updDT)
							Select 
							rd.Registry_id, rd.Evn_id, :FLAG as RegistryErrorType_id, rd.LpuSection_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT 
							from {$this->scheme}.v_{$this->RegistryDataObject} rd
							where rd.Registry_id = :Registry_id  and rd.Evn_id = :IDCASE";
							
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
				return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
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
					from {$this->scheme}.v_{$this->RegistryDataObject} rd
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
				return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
			}
		
		}
	}

	/**
	 * Добавление ошибки в справочник
	 */
	function addRegistryErrorType($data) {
		$params = array(
			'RegistryErrorType_Code' => $data['RegistryErrorType_Code'],
			'RegistryErrorType_Name' => $data['RegistryErrorType_Name'],
			'RegistryErrorType_Descr' => !empty($data['RegistryErrorType_Descr']) ? $data['RegistryErrorType_Descr'] : '',
			'RegistryErrorClass_id' => !empty($data['RegistryErrorClass_id']) ? $data['RegistryErrorClass_id'] : 2,
			'RegistryErrorStageType_id' => !empty($data['RegistryErrorStageType_id']) ? $data['RegistryErrorStageType_id'] : 1,
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@RegistryErrorType_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryErrorType_ins
				@RegistryErrorType_id = @RegistryErrorType_id output,
				@RegistryErrorType_Code = :RegistryErrorType_Code,
				@RegistryErrorType_Name = :RegistryErrorType_Name,
				@RegistryErrorType_Descr = :RegistryErrorType_Descr,
				@RegistryErrorClass_id = :RegistryErrorClass_id,
				@RegistryErrorStageType_id = :RegistryErrorStageType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @RegistryErrorType_id as RegistryErrorType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Удаление ошибок ТФОМС
	 */
	function deleteRegistryErrorTFOMS($data)
	{
		$query = "
			select top 1 RegistryType_id
			from {$this->scheme}.v_Registry with (nolock)
			where Registry_id = :Registry_id
		";
		$RegistryType_id = $this->getFirstResultFromQuery($query,$data);
		if (!$RegistryType_id) {
			return false;
		}

		$registry_list = array();

		if ($RegistryType_id == 13) {
			$query = "
				select RGL.Registry_id
				from {$this->scheme}.v_RegistryGroupLink RGL with(nolock)
				where RGL.Registry_pid = :Registry_id
			";
			$result = $this->queryResult($query, $data);
			if (!$this->isSuccessful($result)) {
				return false;
			}
			foreach($result as $item) {
				$registry_list[] = $item['Registry_id'];
			}
		} else {
			$registry_list[] = $data['Registry_id'];
		}

		$query = "
			delete from {$this->scheme}.RegistryErrorTFOMS with (rowlock)
			where Registry_id in (".implode(',',$registry_list).")
		";
		$result = $this->db->query($query, $data);
		return true;
	}

	/**
	 * Сохранение ошибок ФЛК
	 */
	function setErrorFromTFOMSImportRegistry($data, $addErrorType = false)
	{
		if (empty($data['Registry_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
		}

		$this->setRegistryParamsByType($data);

		$query = "
			SELECT TOP 1 RegistryErrorType_id, RegistryErrorClass_id
			FROM {$this->scheme}.v_RegistryErrorType with (nolock)
			WHERE RegistryErrorType_Code = :OSHIB
		";
		$resp = $this->db->query($query, $data);
		if (!is_object($resp)) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки '.$data['OSHIB']));
		}
		$ret = $resp->result('array');
		if (is_array($ret) && (count($ret) == 0)) {
			if ($addErrorType) {
				$RegistryErrorClass_id = 1;
				$ret = $this->addRegistryErrorType(array(
					'RegistryErrorType_Code' => $data['OSHIB'],
					'RegistryErrorType_Name' => $data['COMMENT'],
					'RegistryErrorStageType_id' => 1,
					'RegistryErrorClass_id' => $RegistryErrorClass_id,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$ret) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении ошибки с кодом '.$data['OSHIB'].' в справочник'));
				}
				if (!empty($ret[0]['Error_Msg'])) {
					return $ret;
				}
				$ret[0]['RegistryErrorClass_id'] = $RegistryErrorClass_id;
			} else {
				return array(array('success' => false, 'Error_Msg' => 'Код ошибки '.$data['OSHIB']. ' не найден в бд'));
			}
		}
		$data['OSHIB_ID'] = $ret[0]['RegistryErrorType_id'];
		$data['RegistryErrorClass_id'] = $ret[0]['RegistryErrorClass_id'];

		if (empty($data['ROWNUM'])) {$data['ROWNUM'] = null;}

		$query = "
		Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_BaseElement, RegistryErrorTFOMS_Comment, RegistryErrorTFOMS_RowNum, RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
		Select
		rd.Registry_id, rd.Evn_id, :OSHIB_ID as RegistryErrorType_id, :OSHIB, :IM_POL, :BAS_EL, :COMMENT, :ROWNUM, :RegistryErrorClass_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT
		from {$this->scheme}.v_{$this->RegistryDataObject} rd
		where rd.Registry_id = :Registry_id  and rd.Evn_id = :Evn_id";

		$result = $this->db->query($query, $data);
		if ($result === true) {
			return array(array('success' => true, 'Error_Msg' => ''));
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Сохранение ошибок ПТК
	 */
	public function setErrorImportRegistry($data) {
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['COMMENT'] = $data['COMMENT'];
		$params['REFREASON'] = $data['REFREASON'];
		$params['Evn_id'] = $data['Evn_id'];

		$this->setRegistryParamsByType($data, true);

		if ( isset($this->_registryErrorTypeList[$params['REFREASON']]) && is_array($this->_registryErrorTypeList[$params['REFREASON']]) ) {
			$params['RegistryErrorType_id'] = $this->_registryErrorTypeList[$params['REFREASON']]['RegistryErrorType_id'];
			$params['RegistryErrorType_Code'] = $params['REFREASON'];
			$params['RegistryErrorClass_id'] = $this->_registryErrorTypeList[$params['REFREASON']]['RegistryErrorClass_id'];
		}
		else {
			// Можно и нужно кэшировать результат этого запроса
			$query = "
				select top 1
					RegistryErrorType_id,
					RegistryErrorType_Code,
					RegistryErrorClass_id
				from
					{$this->scheme}.RegistryErrorType with(nolock)
				where
					RegistryErrorType_Code = :REFREASON
					and RegistryErrorStageType_id = 3
			";
			$resp = $this->queryResult($query, $params);

			if ($resp === false) {
				return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
			}
			if (count($resp) > 0) {
				$params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
				$params['RegistryErrorType_Code'] = $resp[0]['RegistryErrorType_Code'];
				$params['RegistryErrorClass_id'] = $resp[0]['RegistryErrorClass_id'];
			} else {
				$RegistryErrorClass_id = 1;
				$resp = $this->addRegistryErrorType(array(
					'RegistryErrorType_Code' => $params['REFREASON'],
					'RegistryErrorType_Name' => $params['REFREASON'],
					'RegistryErrorType_Descr' => $params['REFREASON'],
					'RegistryErrorStageType_id' => 3,
					'RegistryErrorClass_id' => $RegistryErrorClass_id,
					'pmUser_id' => $params['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					return array(array('success' => false, 'Error_Msg' => 'Не удалось добавить ошибку в справочник!'));
				}
				$params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
				$params['RegistryErrorClass_id'] = $RegistryErrorClass_id;
			}

			// Кэшируем значения
			$this->_registryErrorTypeList[$params['REFREASON']] = array(
				'RegistryErrorType_id' => $params['RegistryErrorType_id'],
				'RegistryErrorClass_id' => $params['RegistryErrorClass_id'],
			);
		}

		$query = "
			Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_Comment, RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
			Select
				rd.Registry_id, rd.Evn_id, :RegistryErrorType_id as RegistryErrorType_id, :REFREASON, :COMMENT, :RegistryErrorClass_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT
			from {$this->scheme}.v_{$this->RegistryDataObject} rd with (nolock)
			where rd.Registry_id = :Registry_id
				and rd.Evn_id = :Evn_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ($result === true)
		{
			return array(array('success' => true, 'Error_Msg' => ''));
		}
		else
		{
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Сохранение ошибки ФЛК, связанной с отсутствием USL в SLUCH
	 */
	public function setManualFLKError($data) {

		$this->setRegistryParamsByType($data);

		$params = array();

		$params['Evn_id'] = $data['Evn_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['Registry_id'] = $data['Registry_id'];
		$params['COMMENT'] = 'Случай отсутствует в ответе от фонда';
		$params['REFREASON'] = 'ФЛК';

		$query = "
			select top 1 RegistryErrorType_id
			from {$this->scheme}.RegistryErrorType with(nolock)
			where RegistryErrorType_Code = :REFREASON
		";
		$resp = $this->queryResult($query, $params);

		if ( $resp === false ) {
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
		}

		if ( count($resp) > 0 ) {
			$params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
		}
		else {
			$resp = $this->addRegistryErrorType(array(
				'RegistryErrorType_Code' => $params['REFREASON'],
				'RegistryErrorType_Name' => 'Ошибка ФЛК',
				'RegistryErrorType_Descr' => $params['COMMENT'],
				'RegistryErrorClass_id' => 1,
				'pmUser_id' => $params['pmUser_id'],
			));

			if ( !$this->isSuccessful($resp) ) {
				return array(array('success' => false, 'Error_Msg' => 'Не удалось добавить ошибку в справочник!'));
			}

			$params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
		}

		$query = "
			insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_Comment,
				RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
			select
				rd.Registry_id, rd.Evn_id, :RegistryErrorType_id as RegistryErrorType_id, :REFREASON, :COMMENT, 1, :pmUser_id, :pmUser_id,
				dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT
			from {$this->scheme}.v_{$this->RegistryDataObject} rd with (nolock)
			where rd.Registry_id = :Registry_id
				and rd.Evn_id = :Evn_id
		";
		$result = $this->db->query($query, $params);

		if ( $result === true ) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}
		else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 *	Установка признака оплаченности.
	 */
	function setRegistryPaid($data)
	{
		$registry_list = array();
		$query = "
			select top 1
				RT.RegistryType_SysNick
			from
				{$this->scheme}.v_Registry R with(nolock)
				inner join v_RegistryType RT with(nolock) on RT.RegistryType_id = R.RegistryType_id
			where
				R.Registry_id = :Registry_id
		";
		$RegistryType_SysNick = $this->getFirstResultFromQuery($query, $data);

		if ($RegistryType_SysNick == 'group') {
			$query = "
				select RGL.Registry_id
				from {$this->scheme}.v_RegistryGroupLink RGL with(nolock)
				where RGL.Registry_pid = :Registry_id
			";
			$result = $this->db->query($query, $data);
			$registry_list = $result->result('array');
		} else {
			$registry_list[] = array('Registry_id' => $data['Registry_id']);
		}

		foreach($registry_list as $registry) {
			$params = array(
				'Registry_id' => $registry['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				declare
					@ErrCode int,
					@ErrMsg varchar(400)
				exec {$this->scheme}.p_Registry_setPaid
					@Registry_id = :Registry_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @ErrMsg as ErrMsg
			";
			$resp = $this->getFirstRowFromQuery($query, $params);
			if (!$resp || !empty($resp['Error_Msg'])) {
				return false;
			}
		}
		return true;
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
		$result = array(
		array('RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'),
		array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
		array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
		array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
		//array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Удаленные')
		);
		return $result;
	}
	
	/**
	 * Проверяет находится ли карта вызова в реестре?
	 * 
	 * @param array $data Набор параметров
	 * @return bool|array on error
	 */
	function checkCmpCallCardInRegistry( $data ){
		
		if ( !array_key_exists( 'CmpCallCard_id', $data ) || !$data['CmpCallCard_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор карты вызова' ) );
		}

		$sql = "
		select top 1
			c.CmpCloseCard_id
		from 
			v_CmpCloseCard c with (nolock)
			inner join v_CmpCallCard cc with (nolock) on cc.cmpcallcard_id = c.CmpCallCard_id
			inner join {$this->scheme}.RegistryDataCmp rd with (nolock) on rd.cmpclosecard_id = c.cmpclosecard_id
			inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rd.Registry_id
		where 
			cc.CmpCallCard_id = :CmpCallCard_id
			and ((rd.RegistryDataCmp_IsPaid = 2 and r.RegistryStatus_id = 4) or r.RegistryStatus_id in (2,3))			
		";
		$query = $this->db->query( $sql, $data );
		if ( is_object( $query ) ) {
			
			$result = $query->result('array');			
			if ( sizeof( $result ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 *	Проверка вхождения случая в реестр
	 */
	function checkEvnInRegistry($data, $action = 'delete')
	{
		$this->setRegistryParamsByType($data);

		$filter = "(1=1)";

		if(isset($data['EvnPL_id'])) {
			$filter .= " and Evn_rid = :EvnPL_id";
		}
		if(isset($data['EvnPS_id'])) {
			$filter .= " and Evn_rid = :EvnPS_id";
		}
		if(isset($data['EvnPLStom_id'])) {
			$filter .= " and Evn_rid = :EvnPLStom_id";
		}
		if(isset($data['EvnVizitPL_id'])) {
			$filter .= " and Evn_id = :EvnVizitPL_id";
		}
		if(isset($data['EvnSection_id'])) {
			$filter .= " and Evn_id = :EvnSection_id";
		}
		if(isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and Evn_id = :EvnVizitPLStom_id";
		}
		if(isset($data['EvnPLDispDop13_id'])) {
			$filter .= " and Evn_id = :EvnPLDispDop13_id";
		}
		if(isset($data['EvnPLDispProf_id'])) {
			$filter .= " and Evn_id = :EvnPLDispProf_id";
		}
		if(isset($data['EvnPLDispOrp_id'])) {
			$filter .= " and Evn_id = :EvnPLDispOrp_id";
		}
		if(isset($data['EvnPLDispTeenInspection_id'])) {
			$filter .= " and Evn_id = :EvnPLDispTeenInspection_id";
		}
		
		if (empty($filter)) {
			return false;
		}
		
		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
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
	 * Функция возвращает набор данных для дерева реестра 3-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data)
	{
		$result = array(
			array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
			array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
			array('RegistryType_id' => 16, 'RegistryType_Name' => 'Стоматология'),
			array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
			array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения'),
			array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот'),
			array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
			array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'),
			array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
			array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги'),
		);

		return $result;
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
	public function saveUnionRegistry($data)
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

		$registryFileNumCheck = $this->getFirstResultFromQuery("
			select top 1 Registry_id
			from {$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and RegistryGroupType_id = :RegistryGroupType_id
				and Registry_FileNum = :Registry_FileNum
				and year(Registry_endDate) = year(:Registry_endDate)
				and month(Registry_endDate) = month(:Registry_endDate)
				and Registry_id != ISNULL(:Registry_id, 0)
		", $data, true);

		if ( !empty($registryFileNumCheck) ) {
			return array('Error_Msg' => 'Указанный номер пакета уже используется для данного отчётного периода и типа реестров. Необходимо указать неиспользуемый номер.');
		}
		
		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "
			declare
				@Error_Code bigint,
				@KatNasel_Code bigint = (select top 1 KatNasel_Code from v_KatNasel (nolock) where KatNasel_id = :KatNasel_id),
				@Error_Message varchar(4000),
				@Registry_id bigint = :Registry_id,
				@curdate datetime = dbo.tzGetDate();
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
				@Registry_FileNum = :Registry_FileNum,
				@KatNasel_id = :KatNasel_id,
				@RegistryGroupType_id = :RegistryGroupType_id,
				@Registry_IsAddAcc = :Registry_IsAddAcc,
				@Registry_IsOnceInTwoYears = :Registry_IsOnceInTwoYears,
				@Lpu_id = :Lpu_id,
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

				$paytypefilter = " and ISNULL(PT.PayType_SysNick, 'oms') = 'oms'";

				$dateX20180501 = '2018-05-01';
				$registrytypefilter = "";

				switch ($data['RegistryGroupType_id']) {
					case 1:
						if ( $data['Registry_begDate'] >= $dateX20180501 ) {
							$registrytypefilter = "
								and R.RegistryType_id IN (1, 2, 6, 15, 16)
							";
						}
						else {
							$registrytypefilter = " and (R.RegistryType_id IN (1, 2, 6, 15, 16) or (R.RegistryType_id = 7 and R.Registry_IsOnceInTwoYears = 2))";
						}
					break;
					case 2:
						$registrytypefilter = " and R.RegistryType_id = 14";
					break;
					case 3:
						if ( $data['Registry_begDate'] >= $dateX20180501 ) {
							$registrytypefilter = "
								and R.RegistryType_id IN (7) and R.DispClass_id = 1
								and ISNULL(R.Registry_IsOnceInTwoYears, 1) = ISNULL(:Registry_IsOnceInTwoYears, 1)
							";
						}
						else {
							$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and ISNULL(R.Registry_IsOnceInTwoYears, 1) = 1";
						}
					break;
					case 4:
						if ( $data['Registry_begDate'] >= $dateX20180501 ) {
							$registrytypefilter = "
								and R.RegistryType_id IN (7) and R.DispClass_id = 2
								and ISNULL(R.Registry_IsOnceInTwoYears, 1) = ISNULL(:Registry_IsOnceInTwoYears, 1)
							";
						}
						else {
							$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 2 and ISNULL(R.Registry_IsOnceInTwoYears, 1) = 1";
						}
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
					case 26:
						$paytypefilter = " and PT.PayType_SysNick = 'osobkatgr'";
					break;

				}
				
				// 3. выполняем поиск реестров которые войдут в объединённый
				$query = "
					select
						R.Registry_id,
						ISNULL(R.Registry_Sum, 0) as Registry_Sum
					from
						{$this->scheme}.v_Registry R (nolock)
						left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
						left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
					where
						R.RegistryType_id <> 13
						and R.RegistryStatus_id = 2 -- к оплате
						and R.Lpu_id = :Lpu_id
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and ISNULL(R.Registry_IsAddAcc, 1) = :Registry_IsAddAcc
						and not exists(select top 1 RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink (nolock) where Registry_id = R.Registry_id)
						{$paytypefilter}
						{$registrytypefilter}
				";
				//		and R.KatNasel_id = :KatNasel_id

				$result_reg = $this->db->query($query, array(
					'KatNasel_id' => $data['KatNasel_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate'],
					'Registry_IsAddAcc' => $data['Registry_IsAddAcc'],
					'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
				));
				
				if (is_object($result_reg)) 
				{
					$UnionRegistrySumma = 0;

					$resp_reg = $result_reg->result('array');
					// 4. сохраняем новые связи
					foreach($resp_reg as $one_reg) {
						$UnionRegistrySumma += $one_reg['Registry_Sum'];
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

					$query = "
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000) = '';

						set nocount on;

						begin try
							update {$this->scheme}.Registry with (updlock)
							set Registry_Sum = :Registry_Sum
							where Registry_id = :Registry_id
						end try

						begin catch
							set @Error_Code = error_number()
							set @Error_Message = error_message()
						end catch

						set nocount off;

						select @Error_Code as Error_Code, @Error_Message as Error_Msg
					";

					$result = $this->db->query($query, array(
						'Registry_id' => $resp[0]['Registry_id'],
						'Registry_Sum' => $UnionRegistrySumma
					));
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
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm($data)
	{
		$query = "
			select
				R.Registry_id,
				R.Registry_Num,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
				R.KatNasel_id,
				R.RegistryGroupType_id,
				R.Registry_FileNum,
				R.Registry_IsAddAcc,
				R.Registry_IsOnceInTwoYears,
				R.Lpu_id
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
				R.Registry_FileNum,
				R.Registry_xmlExportPath,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
				R.KatNasel_id,
				KN.KatNasel_Name,
				KN.KatNasel_SysNick,
				RGT.RegistryGroupType_Name,
				ISNULL(RS.Registry_SumPaid, 0.00) as Registry_SumPaid
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry R (nolock) -- объединённый реестр
				left join v_RegistryGroupType RGT (nolock) on RGT.RegistryGroupType_id = R.RegistryGroupType_id
				left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
				outer apply(
					select
						SUM(ISNULL(R2.Registry_SumPaid,0)) as Registry_SumPaid
					from {$this->scheme}.v_Registry R2 (nolock)
						inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on R2.Registry_id = RGL.Registry_id
					where
						RGL.Registry_pid = R.Registry_id
				) RS
				-- end from
			where
				-- where
				R.Lpu_id = :Lpu_id
				and R.RegistryType_id = 13
				-- end where
			order by
				-- order by
				R.Registry_id desc
				-- end order by
		";
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
			LB.LpuBuilding_Name,
			convert(varchar,R.Registry_updDT,104) as Registry_updDate
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryGroupLink RGL (nolock)
			inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id -- обычный реестр
			left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
			left join v_RegistryType RT (nolock) on RT.RegistryType_id = R.RegistryType_id
			left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
			left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = R.LpuBuilding_id
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
	 *	Установка статуса импорта реестра в XML
	 */
	function SetXmlExportStatus($data) 
	{
		if (empty($data['Registry_EvnNum']))
		{
			$data['Registry_EvnNum'] = null;
		}

		$data['Registry_FileNameCase'] = !empty($data['Registry_FileNameCase']) ? $data['Registry_FileNameCase'] : null;
		$data['Registry_FileNamePersonalData'] = !empty($data['Registry_FileNamePersonalData']) ? $data['Registry_FileNamePersonalData'] : null;
		$data['Registry_CaseCount'] = !empty($data['Registry_CaseCount']) ? $data['Registry_CaseCount'] : null;
		$data['Registry_PersonalDataCount'] = !empty($data['Registry_PersonalDataCount']) ? $data['Registry_PersonalDataCount'] : null;

		if (!empty($data['Registry_id']))
		{
			$query = "
				update
					{$this->scheme}.Registry with (rowlock)
				set
					Registry_xmlExportPath = :Status,
					Registry_EvnNum = :Registry_EvnNum,
					Registry_xmlExpDT = dbo.tzGetDate(),
					Registry_FileNameCase = :Registry_FileNameCase,
					Registry_FileNamePersonalData = :Registry_FileNamePersonalData,
					Registry_CaseCount = :Registry_CaseCount,
					Registry_PersonalDataCount = :Registry_PersonalDataCount

				where
					Registry_id = :Registry_id
			";
			
			$result = $this->db->query($query,
                array(
					'Registry_id' => $data['Registry_id'],
					'Registry_EvnNum' => $data['Registry_EvnNum'],
					'Status' => $data['Status'],
					'Registry_FileNameCase' => $data['Registry_FileNameCase'],
					'Registry_FileNamePersonalData' => $data['Registry_FileNamePersonalData'],
					'Registry_CaseCount' => $data['Registry_CaseCount'],
					'Registry_PersonalDataCount' => $data['Registry_PersonalDataCount']
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
	 * Получение группы случаев из реестров по стационару
	 */
	function getRegistryDataGroupForDelete($data)
	{
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id']
		);

		$this->setRegistryParamsByType($data);

		$query = "
			select top 1 Evn_rid, EvnMax_id
			from {$this->scheme}.v_{$this->RegistryDataObject} RD with(nolock)
			where RD.Registry_id = :Registry_id and RD.Evn_id = :Evn_id
		";
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp) || count($resp) == 0) {
			return  $resp;
		}
		$params = array_merge($params, $resp[0]);

		$filter = "";
		if ($data['RegistryType_id'] == 16) {
			$filter .= " and RD.EvnMax_id = :EvnMax_id";
		} else {
			$filter .= " and RD.Evn_rid = :Evn_rid";
		}

		$query = "
			select
				RD.Evn_id
			from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
			where
				RD.Registry_id = :Registry_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 *	Помечаем запись реестра на удаление
	 */
	function deleteRegistryData($data)
	{
		$evn_list = $data['EvnIds'];

		//Случаи в стационаре и стоматологии группируются
		//При удалении одного случая из группы нужно удалить всю группу
		if (in_array($data['RegistryType_id'], array(1, 16))) {
			$new_evn_list = array();

			foreach ($evn_list as $EvnId) {
				$resp = $this->getRegistryDataGroupForDelete(array(
					'Registry_id' => $data['Registry_id'],
					'RegistryType_id' => $data['RegistryType_id'],
					'Evn_id' => $EvnId
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				foreach($resp as $item) {
					$new_evn_list[] = $item['Evn_id'];
				}

				if (!in_array($EvnId, $new_evn_list)) {
					$new_evn_list[] = $EvnId;
				}
			}
			$evn_list = array_unique($new_evn_list);
		}

		foreach ($evn_list as $EvnId) {
			$data['Evn_id'] = $EvnId;

			$query = "
				Declare @Error_Code bigint;
				Declare @Error_Message varchar(4000);
				exec {$this->scheme}.p_RegistryData_del
					@Evn_id = :Evn_id,
					@Registry_id = :Registry_id,
					@RegistryType_id = :RegistryType_id,
					@RegistryData_deleted = :RegistryData_deleted,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
			";
			$res = $this->db->query($query, $data);
		}

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 2:
				$this->MaxEvnField = 'Evn_rid';
				break;
			case 1:
				$this->MaxEvnField = 'Evn_rid';
			case 14:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryDataSLObject = 'RegistryDataEvnPSSL';
				$this->RegistryErrorComObject = 'RegistryErrorComEvnPS';
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryNoPolisObject = 'RegistryEvnPSNoPolis';
			break;

			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryErrorComObject = 'RegistryErrorComDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
				$this->RegistryNoPolisObject = 'RegistryDispNoPolis';
			break;

			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryErrorComObject = 'RegistryErrorComProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
				$this->RegistryNoPolisObject = 'RegistryProfNoPolis';
			break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryErrorComObject = 'RegistryErrorComPar';
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryNoPolisObject = 'RegistryParNoPolis';
			break;

			case 6:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataObjectTable = 'RegistryDataCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
			break;
		}
	}

	/**
	 * Установка признака необходимости переформирования реестра
	 */
	function setNeedReform($data)
	{
		$data['Registry_IsNeedReform'] = 2;
		return $this->setRegistryIsNeedReform($data);
	}
	
	/**
	 * Возвращает список настроек ФЛК
	 */
	public function loadRegistryEntiesSettings($data)
	{
		if(!$data['RegistryGroupType_id']) return false;
		
		$where = ' AND RegistryGroupType_id = '.$data['RegistryGroupType_id'];
		
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
				AND FLKSettings_EvnData LIKE '%penza%'
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
}