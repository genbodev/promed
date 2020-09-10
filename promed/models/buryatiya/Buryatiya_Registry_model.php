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
class Buryatiya_Registry_model extends Registry_model {
	var $region = "buryatiya";
	var $scheme = "r3";
	var $Registry_EvnNum = array();
	var $MaxEvnField = "Evn_id";

	private $dbregANSI = null;

	private $_IDCASE = 0;
	private $_ID_PAC = 0;
	private $_IDSERV = 0;
	private $_N_ZAP = 0;
	private $_SL_ID = 0;
	private $_ZSL = 0;

	protected $_unionRegistryContentByType = [];
	protected $_unionRegistryTypes = [];

	/**
	 * @var array Типы реестров, входящих в объединенный
	 * @comment Необходим при импорте ответа по объединенному реестру и вызове setRegistryParamsByType без обращения к БД
	 */
	private $_registryTypes = array();

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

		if ( $data['RegistryStatus_id'] == 4 ) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
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
		} elseif ($data['RegistryStatus_id']==2 && $RegistryStatus_id==4) { // если переводим из "Оплаченный" в "К оплате" p_Registry_setUnPaid
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

			if (!is_object($result))
			{
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке к оплате'));
			}

			$res = $result->result('array');

			if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
				return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
			}

			$query = "
				declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '',
					@RegistryErrorType_id bigint;

				set nocount on

				begin try
					set @RegistryErrorType_id = (select top 1 RegistryErrorType_id from r3.v_RegistryErrorType with (nolock) where RegistryErrorType_Code = '1016');

					delete from r3.RegistryErrorTFOMS with (rowlock)
					where Registry_id = :Registry_id and RegistryErrorType_id = @RegistryErrorType_id
				end try

				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch

				set nocount off

				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id']
			));

			if ( !is_object($result) ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление ошибок 1016)'));
			}

			$res = $result->result('array');

			if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
				return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
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

		if ( !is_object($result) ) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		if ( $data['RegistryStatus_id'] == 4 ) {
			// пишем информацию о смене статуса в историю
			$this->dumpRegistryInformation(array('Registry_id' => $data['Registry_id']), 4);
		}

		return $result->result('array');
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
						);

						set @packNum = ISNULL(@packNum, 0) + 1;

						update {$this->scheme}.Registry
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

		$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';

		$query = "
			select
				{$xmlExportPath}
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.RegistryGroupType_id,
				R.Registry_IsZNO,
				kn.KatNasel_SysNick,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
				RDSum.RegistryData_Count as RegistryData_Count,
				IsNull(R.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
				IsNull(rcs.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
				rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name,
				R.DispClass_id,
				SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
			from {$this->scheme}.Registry R with (nolock)
				left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
				outer apply(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_RegistryData RD with (nolock)
						inner join {$this->scheme}.v_RegistryGroupLink RGL with (nolock) on RGL.Registry_id = RD.Registry_id
					where RGL.Registry_pid = R.Registry_id
				) RDSum
				left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
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
		if(!empty($data['MedPersonal_id']))
		{
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		$addToSelect = "";
		$leftjoin = "";

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 12)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = Evn.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id";
		}

		if($this->RegistryType_id == 15){
			$leftjoin .= "left join v_EvnUslugaPar EUP_ELSE (nolock) on EUP_ELSE.EvnUslugaPar_id = RE.Evn_id ";
			$leftjoin .= "left join v_EvnFuncRequest efr (nolock) on efr.EvnFuncRequest_pid = EUP_ELSE.EvnDirection_id ";
			$leftjoin .= "left join v_EvnLabRequest  elr (nolock) on elr.EvnDirection_id = EUP_ELSE.EvnDirection_id ";
			$addToSelect .= ",case when efr.EvnFuncRequest_id is not null then 'true' else 'false' end as isEvnFuncRequest ";
			$addToSelect .= ",case when elr.EvnLabRequest_id is not null then 'true' else 'false' end as isEvnLabRequest ";
			$addToSelect .= ",elr.MedService_id ";
			$addToSelect .= ",EUP.EvnDirection_id ";
		}

		if($this->RegistryType_id != 15) {
			$orderby="";
			if ($this->RegistryType_id == 6) {
				$E_connection = "{$this->RegistryObject}_id = RE.Evn_id";
				$E_numCard = 'Ngod';
			} else if (in_array($this->RegistryType_id, [7, 9, 11, 12])) {
				$E_connection = "
						Person_id = ps.Person_id
						and LpuAttachType_id = 1
						and isnull(PersonCard_begDate, RD.Evn_setDate) <= RD.Evn_setDate
						and isnull(PersonCard_endDate, RD.Evn_setDate) >= RD.Evn_setDate
					";
				$E_numCard = 'Code';
				$orderby="order by PersonCard_begDate desc";
			} else {
				$E_connection = "{$this->RegistryObject}_id = Evn.Evn_rid";
				$E_numCard = 'NumCard';
			}

			if (in_array($this->RegistryType_id, [2,16])) {
				$addToSelect .= ", RTrim(evpl.EvnPL_NumCard) as NumCard ";
			}else {
				$addToSelect .= ", NC.NumCard as NumCard ";
				$leftjoin .= "
					outer apply (
						select top 1 
							RTrim({$this->RegistryObject}_{$E_numCard}) as NumCard
						from
						 	v_{$this->RegistryObject} with (nolock)
						where
							{$E_connection}
							{$orderby} 
					) NC
				";
			}
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
				RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				RegistryErrorType_Descr,
				--MP.Person_Fio as MedPersonal_Fio,
				RTRIM(RD.MedPersonal_Fio) as MedPersonal_Fio,
				LB.LpuBuilding_Name,
				null as LpuSection_Name,
				1 as RegistryData_deleted,
				1 as RegistryData_notexist,
				null as Evn_setDate,
				null as Evn_disDate
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
				inner join {$this->scheme}.v_RegistryDataCmp RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_CmpCloseCard CmpCloseCard with(nolock) on CmpCloseCard.CmpCloseCard_id = RD.Evn_id
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
				RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				RegistryErrorType_Descr,
				--MP.Person_Fio as MedPersonal_Fio,
				RTRIM(RD.MedPersonal_Fio) as MedPersonal_Fio,
				LB.LpuBuilding_Name,
				LS.LpuSection_Name,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				convert(varchar(10), Evn.Evn_setDT, 104)+' '+convert(varchar(5), Evn.Evn_setDT, 108) as Evn_setDate,
				convert(varchar(10), Evn.Evn_disDT, 104)+' '+convert(varchar(5), Evn.Evn_disDT, 108) as Evn_disDate
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
				left join v_EvnPL evpl (nolock) on evpl.EvnPL_id = Evn.Evn_rid
				left join v_EvnUslugaPar eup (nolock) on eup.EvnUslugaPar_id = RE.Evn_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = ISNULL(ES.LpuSection_id, evpl.LpuSection_id)
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = coalesce(ES.MedPersonal_id, evpl.MedPersonal_id, eup.MedPersonal_id)
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
	function loadRegistrySCHETForXmlUsingCommonUnion($data, $isNewExport = false)
	{
		if ( empty($this->dbregANSI) ) {
			$this->dbregANSI = $this->load->database('registry1251', true); // получаем коннект к БД с кодировкой windows-1251
		}

		if ($isNewExport) {
			$p_schet = $this->scheme . ".p_Registry_expScet";
		} else {
			switch ($data['RegistryGroupType_id']) {
				case 11:
					$p_schet = $this->scheme . ".p_Registry_UntdDD_expScet";
					break;

				default:
					$p_schet = $this->scheme . ".p_Registry_Untd_expScet";
					break;
			}
		}

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
	 *	Получение данных для выгрузки реестров в XML
	 */
	function loadRegistryDataForXmlUsing($data, &$Registry_EvnNum, $xml_file, $file_re_data_name, $file_re_pers_data_name) {
		if ( empty($this->dbregANSI) ) {
			$this->dbregANSI = $this->load->database('registry1251', true); // получаем коннект к БД с кодировкой windows-1251
		}

		$IDCASE = 1;
		$IDSERV = 1;
		$N_ZAP = 1;
		$SD_Z = 0;

		switch ( $data['RegistryGroupType_id'] ) {
			case 11:
				$object = "UntdDD";
				break;

			default:
				$object = "Untd";
				break;
		}

		$netValue = toAnsi('НЕТ', true);

		$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2";
		$p_pers = $this->scheme . ".p_Registry_" . $object . "_expPac";
		$p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl";
		$p_vizit = $this->scheme . ".p_Registry_" . $object . "_expVizit";

		if ( $object == "UntdDD" ) {
			$p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ";
		}

		// 1. Выгружаем пациентов
		$query = "exec {$p_pers} @Registry_id = :Registry_id";
		$result_pers = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result_pers) ) {
			return false;
		}

		// 2. Выгружаем посещения
		$query = "exec {$p_vizit} @Registry_id = :Registry_id";
		$result_vizit = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result_vizit) ) {
			return false;
		}

		// 3. Выгружаем услуги
		$query = "exec {$p_usl} @Registry_id = :Registry_id";
		$result_usluga = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result_usluga) ) {
			return false;
		}

		// 4. Выгружаем диагнозы
		$query = "exec {$p_ds2} @Registry_id = :Registry_id";
		$result_ds2 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result_usluga) ) {
			return false;
		}

		// назначения (NAZ)
		$NAZ = array();
		if (!empty($p_naz)) {
			$query = "
				exec {$p_naz} @Registry_id = :Registry_id
			";
			$result_naz = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			if (!is_object($result_naz)) {
				return false;
			}
			while ($row = $result_naz->_fetch_assoc()) {
				$NAZ[$row['MaxEvn_id']][] = $row;
			}
		}

		$altKeys = array(
			 'USL_LPU' => 'LPU'
			,'USL_LPU_1' => 'LPU_1'
			,'USL_P_OTK' => 'P_OTK'
			,'USL_PODR' => 'PODR'
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

		// @task https://redmine.swan.perm.ru/issues/97198
		$emptyTagsList = array();
		if ( $data['KatNasel_SysNick'] == 'inog' ) {
			// Как оказалось, выгружать пустое значение не надо
			// @task https://redmine.swan.perm.ru/issues/94817
			// $emptyTagsList[] = 'ENP';
		}

		/*$result_pers->_data_seek(0);
		$result_vizit->_data_seek(0);
		$result_usluga->_data_seek(0);*/
		while ($pers = $result_pers->_fetch_assoc()) {
			if ( empty($pers['ID_PERS']) ) {
				continue;
			}
			$PersonEvn_id = $pers['ID_PERS'];

			if ($CurrentPersonEvn_id == $PersonEvn_id) {
				continue; // если уже был такой, пропускаем
			}

			$CurrentPersonEvn_id = $PersonEvn_id;

			// некоторая обработка пациента
			$pers['DOST'] = array();
			$pers['DOST_P'] = array();

			if ( $pers['NOVOR'] == '0' ) {
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
			else {
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

			try {
				// ищем случаи для пациента
				if (!empty($CurrentVizit) && $CurrentVizit['PersonEvn_id'] == $pers['ID_PERS']) {
					$key = $CurrentVizit['PersonEvn_id'].'_'.$CurrentVizit['PR_NOV'];
					if (!array_key_exists($key, $ZAP)) {
						$pers['N_ZAP'] = $N_ZAP;
						$N_ZAP++;
						$ZAP[$key] = $pers;
						$ZAP[$key]['PR_NOV'] = $CurrentVizit['PR_NOV'];
					}
					$Registry_EvnNum[$CurrentVizit['IDCASE']] = array(
						'Evn_id' => $CurrentVizit['MaxEvn_id'],
						'Registry_id' => $CurrentVizit['Registry_id'],
						'N_ZAP' => $ZAP[$key]['N_ZAP']
					);
					$ZAP[$key]['ID_PAC'] = $key;
					$ZAP[$key]['SLUCH'][] = $CurrentVizit;
					$SD_Z++;
					$CurrentVizit = null;
				}

				if (empty($CurrentVizit)) {
					// ищем ещё случаи для пациента
					while ($vizit = $result_vizit->_fetch_assoc()) {
						if ( !empty($vizit['flag']) && in_array($vizit['flag'], array('EvnPLDD13', 'EvnPLOrp13', 'EvnPLProf', 'EvnPLProfTeen')) ) {
							if ( array_key_exists('DS2', $vizit) ) {
								unset($vizit['DS2']);
							}
							if ( array_key_exists('DS2_PR', $vizit) ) {
								unset($vizit['DS2_PR']);
							}
						}
						$CurrentVizit = $vizit;
						$CurrentVizit['USL'] = array();
						$CurrentVizit['DS2_DATA'] = array();
						$Flag061057 = false;

						try {
							// ищем услуги для случая
							if (!empty($CurrentUsluga) && $CurrentUsluga['MaxEvn_id'] == $vizit['MaxEvn_id']) {
								$CurrentVizit['USL'][] = $CurrentUsluga;

								if (
									in_array($CurrentUsluga['flag'], array('EvnPLProf'))
									&& in_array($CurrentUsluga['CODE_USL'], array('063411', '063421', '063412', '063422', '063413', '063423', '063414', '063424', '063438'))
									&& $Flag061057 === false
								) {
									$CurrentUsluga['IDSERV'] = $IDSERV;
									$CurrentUsluga['CODE_USL'] = '061057';
									$CurrentUsluga['TARIF_USL'] = '0';
									$CurrentUsluga['SUMV_USL'] = '0';

									$IDSERV++;

									$CurrentVizit['USL'][] = $CurrentUsluga;

									$Flag061057 = true;
								}

								$CurrentUsluga = null;
							}

							if (empty($CurrentUsluga)) {
								// ищем ещё услуги для случая
								while ($usluga = $result_usluga->_fetch_assoc()) {
									$CurrentUsluga = $usluga;
									$CurrentUsluga['IDSERV'] = $IDSERV;
									$IDSERV++;
									if ($CurrentUsluga['MaxEvn_id'] == $vizit['MaxEvn_id']) {
										$CurrentVizit['USL'][] = $CurrentUsluga;

										if (
											in_array($CurrentUsluga['flag'], array('EvnPLProf'))
											&& in_array($CurrentUsluga['CODE_USL'], array('063411', '063421', '063412', '063422', '063413', '063423', '063414', '063424', '063438'))
											&& $Flag061057 === false
										) {
											$CurrentUsluga['IDSERV'] = $IDSERV;
											$CurrentUsluga['CODE_USL'] = '061057';
											$CurrentUsluga['TARIF_USL'] = '0';
											$CurrentUsluga['SUMV_USL'] = '0';

											$IDSERV++;

											$CurrentVizit['USL'][] = $CurrentUsluga;

											$Flag061057 = true;
										}
									} else {
										break;
									}
								}
							}
						} catch (Exception $e) {
							// вышли за пределы услуг
						}

						try {
							// ищем диагнозы для случая
							if (!empty($CurrentDS2) && $CurrentDS2['MaxEvn_id'] == $vizit['MaxEvn_id']) {
								$CurrentVizit['DS2_DATA'][] = $CurrentDS2;
								$CurrentDS2 = null;
							}

							if (empty($CurrentDS2)) {
								// ищем еще диагнозы для случая
								while ($ds2 = $result_ds2->_fetch_assoc()) {
									$CurrentDS2 = $ds2;
									if ($CurrentDS2['MaxEvn_id'] == $vizit['MaxEvn_id']) {
										$CurrentVizit['DS2_DATA'][] = $CurrentDS2;
									} else {
										break;
									}
								}
							}
						} catch (Exception $e) {
							// вышли за пределы диагнозов
						}

						$CurrentVizit['NAZR_DATA'] = array();
						$CurrentVizit['NAZ_SP_DATA'] = array();
						$CurrentVizit['NAZ_V_DATA'] = array();
						$CurrentVizit['NAZ_PMP_DATA'] = array();
						$CurrentVizit['NAZ_PK_DATA'] = array();

						if ( isset($NAZ[$vizit['MaxEvn_id']]) && is_array($NAZ[$vizit['MaxEvn_id']]) ) {
							foreach ( $NAZ[$vizit['MaxEvn_id']] as $item ) {
								foreach ( $item as $k => $v ) {
									if ( in_array($k, array('NAZR', 'NAZ_SP', 'NAZ_V', 'NAZ_PMP', 'NAZ_PK')) ) {
										$CurrentVizit[$k . '_DATA'][] = array($k => $v);
									}
								}
							}
						}

						if (!empty($p_naz)) {
							unset($CurrentVizit['NAZR']);
							unset($CurrentVizit['NAZ_SP']);
							unset($CurrentVizit['NAZ_V']);
							unset($CurrentVizit['NAZ_PMP']);
							unset($CurrentVizit['NAZ_PK']);
						}

						$CurrentVizit['IDCASE'] = $IDCASE;
						$IDCASE++;
						if ($CurrentVizit['PersonEvn_id'] == $pers['ID_PERS']) {
							$key = $CurrentVizit['PersonEvn_id'] . '_' . $CurrentVizit['PR_NOV'];
							if (!array_key_exists($key, $ZAP)) {
								$pers['N_ZAP'] = $N_ZAP;
								$N_ZAP++;
								$ZAP[$key] = $pers;
								$ZAP[$key]['PR_NOV'] = $CurrentVizit['PR_NOV'];
							}
							$Registry_EvnNum[$CurrentVizit['IDCASE']] = array(
								'Evn_id' => $CurrentVizit['MaxEvn_id'],
								'Registry_id' => $CurrentVizit['Registry_id'],
								'N_ZAP' => $ZAP[$key]['N_ZAP']
							);
							$ZAP[$key]['ID_PAC'] = $key;
							$ZAP[$key]['SLUCH'][] = $CurrentVizit;
							$SD_Z++;
						} else {
							break;
						}
					}
				}
			} catch (Exception $e) {
				// вышли за пределы случаев
			}

			if (count($ZAP) > 100) {
				// пишем в файл
				$xml = $this->parser->parse_ext('export_xml/' . $xml_file, array('ZAP' => $ZAP), true, false, $altKeys, false, $emptyTagsList);
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				$xml = $this->parser->parse_ext('export_xml/registry_buryatiya_person_body', array('PACIENT' => $ZAP), true);
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($file_re_pers_data_name, $xml, FILE_APPEND);
				unset($xml);

				unset($ZAP);
				$ZAP = array();
			}
		}

		if (count($ZAP) > 0) {
			// пишем в файл
			$xml = $this->parser->parse_ext('export_xml/' . $xml_file, array('ZAP' => $ZAP), true, false, $altKeys, false, $emptyTagsList);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			$xml = $this->parser->parse_ext('export_xml/registry_buryatiya_person_body', array('PACIENT' => $ZAP), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_pers_data_name, $xml, FILE_APPEND);
			unset($xml);

			unset($ZAP);
		}

		return $SD_Z;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	function loadRegistryDataForXmlUsing2018($type, $data, &$Registry_EvnNum, $registry_data_template_body, $file_re_data_name, $file_re_pers_data_name) {
		if ( empty($this->dbregANSI) ) {
			$this->dbregANSI = $this->load->database('registry1251', true); // получаем коннект к БД с кодировкой windows-1251
		}

		switch ($type)
		{
			case 1: //stac
				$object = 'EvnPS';
				break;
			case 2: //polka
				$object = 'EvnPL';
				break;
			case 4: //dd
				$object = 'EvnPLDD';
				break;
			case 5: //orp
				$object = 'EvnPLOrp';
				break;
			case 6: //smp
				$object = 'SMP';
				break;
			case 7: //dd
			case 8: //dd
				$object = 'EvnPLDD13';
				break;
			case 9: //orp
			case 10: //orp
				$object = 'EvnPLOrp13';
				break;
			case 11: //orp
				$object = 'EvnPLProf';
				break;
			case 12: //teen inspection
				$object = 'EvnPLProfTeen';
				break;
			case 14: // ВМП
				$object = 'EvnHTM';
				break;
			case 15: //parka
				$object = 'EvnUslugaPar';
				break;
			case 16: //stom
				$object = 'EvnPLStom';
				break;
			default:
				return false;
				break;
		}

		$netValue = toAnsi('НЕТ', true);

		$fn_pers = $this->scheme . ".p_Registry_" . $object . "_expPac_2018_f";
		$fn_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl_2018_f";
		$fn_sl = $this->scheme . ".p_Registry_" . $object . "_expVizit_2018_f";
		$fn_zsl = $this->scheme . ".p_Registry_" . $object . "_expSL_2018_f";

		if ( in_array($type, array(1,2,7,9,11,12,14,16)) ) {
			$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2_2018";
		}

		if ( in_array($type, array(1,14)) ) {
			$p_ds3 = $this->scheme . ".p_Registry_" . $object . "_expDS3_2018";
		}

		if ( in_array($type, array(7,9,11,12)) ) {
			$p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ_2018";
		}

		if ( in_array($type, array(1)) ) {
			$p_kslp = $this->scheme . ".p_Registry_" . $object . "_expKSLP_2018";
			$p_crit = $this->scheme . ".p_Registry_" . $object . "_expCRIT_2018";
		}

		if (in_array($type, array(1, 2, 14, 16))) {
			$p_bdiag = $this->scheme . ".p_Registry_" . $object . "_expBDIAG_2018";
			$p_bprot = $this->scheme . ".p_Registry_" . $object . "_expBPROT_2018";
			$p_onkousl = $this->scheme . ".p_Registry_" . $object . "_expONKOUSL_2018";
		}

		if (
			in_array($type, array(14))
			|| (in_array($type, array(1, 2, 6, 16)) && $data['Registry_IsZNO'] == 2)
		) {
			$p_cons = $this->scheme . ".p_Registry_{$object}_expCONS_2018";

			if ( $type != 6 ) {
				$p_lek_pr = $this->scheme . ".p_Registry_{$object}_expLEK_PR_2018";
				$p_napr = $this->scheme . ".p_Registry_" . $object . "_expNAPR_2018";
			}
		}
		
		if (
			in_array($type, array(15))
		) {
			$p_cons_f = $this->scheme . ".p_Registry_{$object}_expCONS_2018_f";
			$p_bdiag_f = $this->scheme . ".p_Registry_" . $object . "_expBDIAG_2018_f";
		}

		$DS2 = array();
		$DS3 = array();
		$NAZ = array();
		$NAPR = array();
		$CRIT = array();
		$BDIAG = array();
		$LEK_PR = array();
		$CONS = array();
		$ONKOUSL = array();
		$BPROT = array();
		$SL_KOEF = array();

		// диагнозы (DS2)
		if (!empty($p_ds2)) {
			$query = "
				exec {$p_ds2} @Registry_id = :Registry_id
			";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds2 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_ds2)) {
				return false;
			}
			while ($row = $result_ds2->_fetch_assoc()) {
				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = array();
				}

				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// диагнозы (DS3)
		if (!empty($p_ds3)) {
			$query = "
				exec {$p_ds3} @Registry_id = :Registry_id
			";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds3 = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_ds3)) {
				return false;
			}
			while ($row = $result_ds3->_fetch_assoc()) {
				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = array();
				}

				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// назначения (NAZ)
		if (!empty($p_naz)) {
			$query = "
				exec {$p_naz} @Registry_id = :Registry_id
			";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_naz = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_naz)) {
				return false;
			}
			while ($row = $result_naz->_fetch_assoc()) {
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = array();
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// Направления (NAPR)
		if (!empty($p_napr)) {
			$query = "
				exec {$p_napr} @Registry_id = ?
			";
			$result_napr = $this->dbregANSI->query($query, array($data['Registry_id']));
			if (!is_object($result_napr)) {
				return false;
			}
			while ($row = $result_napr->_fetch_assoc()) {
				if (!isset($NAPR[$row['Evn_id']])) {
					$NAPR[$row['Evn_id']] = array();
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		// Критерии
		if ( !empty($p_crit) ) {
			$query = "exec {$p_crit} @Registry_id = :Registry_id";
			$result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			// Формируем массив CRIT
			foreach ( $resp as $row ) {
				if ( !isset($CRIT[$row['Evn_id']]) ) {
					$CRIT[$row['Evn_id']] = array();
				}

				$CRIT[$row['Evn_id']][] = array(
					'CRIT' => $row['CRIT'],
				);
			}
		}

		if (!empty($p_bdiag) || !empty($p_bdiag_f)) {
			if(!empty($p_bdiag)){
				$query = "exec {$p_bdiag} @Registry_id = ?";
			}else{
				$query = "select * from {$p_bdiag_f} (?)";
			}
			
			$result_bdiag = $this->dbregANSI->query($query, array($data['Registry_id']));
			if (!is_object($result_bdiag)) {
				return false;
			}
			while ($row = $result_bdiag->_fetch_assoc()) {
				if(!empty($row['DIAG_TIP']) || !empty($row['DIAG_CODE']) || !empty($row['DIAG_RSLT']) || !empty($row['DIAG_DATE'])){
					if ( !isset($BDIAG[$row['Evn_id']]) ) {
						$BDIAG[$row['Evn_id']] = array();
					}

					$BDIAG[$row['Evn_id']][] = $row;
				}

			}
		}

		// Сведения о проведении консилиума (CONS)
		if ( !empty($p_cons) || !empty($p_cons_f)) {
			
			if (!empty($p_cons)) {
				$query = "exec {$p_cons} @Registry_id = :Registry_id";
			} else {
				$query = "select * from {$p_cons_f} (:Registry_id)";
			}
			
			$result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			while ( $row = $result->_fetch_assoc() ) {
				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = array();
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($p_lek_pr) ) {
			$query = "exec {$p_lek_pr} @Registry_id = :Registry_id";
			$result = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			while ( $row = $result->_fetch_assoc() ) {
				if ( !isset($LEK_PR[$row['EvnUslugaLEK_id']]) ) {
					$LEK_PR[$row['EvnUslugaLEK_id']] = array();
				}

				$LEK_PR[$row['EvnUslugaLEK_id']][] = $row;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if (!empty($p_onkousl)) {
			$query = "
				exec {$p_onkousl} @Registry_id = ?
			";
			$result_onkousl = $this->dbregANSI->query($query, array($data['Registry_id']));
			if (!is_object($result_onkousl)) {
				return false;
			}
			while ($row = $result_onkousl->_fetch_assoc()) {
				if (!isset($ONKOUSL[$row['Evn_id']])) {
					$ONKOUSL[$row['Evn_id']] = array();
				}

				$row['LEK_PR_DATA'] = array();

				if (isset($LEK_PR[$row['EvnUslugaLEK_id']]) && in_array($row['USL_TIP'], array(2, 4))) {
					$LEK_PR_DATA = array();

					foreach ($LEK_PR[$row['EvnUslugaLEK_id']] as $rowTmp) {
						if (!isset($LEK_PR_DATA[$rowTmp['REGNUM']])) {
							$LEK_PR_DATA[$rowTmp['REGNUM']] = array(
								'REGNUM' => $rowTmp['REGNUM'],
								'CODE_SH' => $rowTmp['CODE_SH'],
								'DATE_INJ_DATA' => array(),
							);
						}

						$LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $rowTmp['DATE_INJ']);
					}

					$row['LEK_PR_DATA'] = $LEK_PR_DATA;
					unset($LEK_PR[$row['EvnUslugaLEK_id']]);
				}

				$ONKOUSL[$row['Evn_id']][] = $row;
			}
		}

		if (!empty($p_bprot)) {
			$query = "
				exec {$p_bprot} @Registry_id = ?
			";
			$result_bprot = $this->dbregANSI->query($query, array($data['Registry_id']));
			if (!is_object($result_bprot)) {
				return false;
			}
			while ($row = $result_bprot->_fetch_assoc()) {
				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = array();
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "
				exec {$p_kslp} @Registry_id = :Registry_id
			";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_kslp = $this->dbregANSI->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_kslp)) {
				return false;
			}
			while ($row = $result_kslp->_fetch_assoc()) {
				if ( !isset($kslp[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = array();
				}

				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
		$altKeys = array(
			 'USL_LPU' => 'LPU'
			,'USL_LPU_1' => 'LPU_1'
			,'USL_P_OTK' => 'P_OTK'
			,'USL_PODR' => 'PODR'
			,'USL_PROFIL' => 'PROFIL'
			,'USL_DET' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'USL_PRVS' => 'PRVS'
		);

		$SD_Z = 0;

		$KSG_KPG_fields = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL');
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'PROT', 'D_PROT', 'K_FR', 'WEI', 'HEI', 'BSA');

		$person_data_template_body = 'registry_buryatiya_person_body';

		$result = $this->db->query("
			set nocount on;
			select * into #zsl from {$fn_zsl} (:Registry_id);
			select * into #sl from {$fn_sl} (:Registry_id);
			select * into #pers from {$fn_pers} (:Registry_id);
			select * into #usl from {$fn_usl} (:Registry_id);
			set nocount off;
			
			select
				null as 'fields_part_1',
				z.*,
				z.MaxEvn_id as MaxEvn_zid,
				z.registry_id as Registry_zid,
				null as 'fields_part_2',
				s.*,
				s.Evn_id as Evn_sid,
				null as 'fields_part_3',
				p.*,
				null as 'fields_part_4',
				u.*
			from
				#zsl z (nolock)
				inner join #sl s (nolock) on s.MaxEvn_id = z.MaxEvn_id
				inner join #pers p (nolock) on p.MaxEvn_id = z.MaxEvn_id
				left join #usl u (nolock) on u.Evn_id = s.Evn_id
			order by
				s.MaxEvn_id, s.Evn_id
		", $data, true);

		if ( !is_object($result) ) {
			return false;
		}

		$ZAP_ARRAY = array();
		$PACIENT_ARRAY = array();
		$netValue = toAnsi('НЕТ', true);

		$recKeys = array(); // ключи для данных

		$prevID_PAC = null;

		// Идём по случаям, как набираем 1000 записей -> пишем сразу в файл.
		$this->textlog->add('Начинаем обработку случаев');

		while ( $one_rec = $result->_fetch_assoc() ) {
			if ( count($recKeys) == 0 ) {
				$recKeys = $this->_getKeysForRec($one_rec);

				if ( count($recKeys) < 3 ) {
					$this->textlog->add("Ошибка, неверное количество частей в запросе");
					return false;
				}
			}

			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);

			$zsl_key = $one_rec['MaxEvn_zid'];
			$sl_key = $one_rec['Evn_sid'];

			$ZSL = array_intersect_key($one_rec, $recKeys[1]);
			$SL = array_intersect_key($one_rec, $recKeys[2]);
			$PACIENT = array_intersect_key($one_rec, $recKeys[3]);
			$USL = array_intersect_key($one_rec, $recKeys[4]);

			$SL['Evn_id'] = $one_rec['Evn_sid'];

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PACIENT['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				$SD_Z += count($ZAP_ARRAY);
				// пишем в файл
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys, false);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили ' . count($ZAP_ARRAY) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = array();

				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $PACIENT_ARRAY), true, false, array(), false);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($PACIENT_ARRAY);
				$PACIENT_ARRAY = array();
			}

			$prevID_PAC = $PACIENT['ID_PAC'];

			if ( isset($ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][$zsl_key]['SL'][$sl_key]) ) {
				$this->_IDSERV++;
				$USL['IDSERV'] = $this->_IDSERV;

				$ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][$zsl_key]['SL'][$sl_key]['USL'][] = $USL;
			}
			else if ( isset($ZAP_ARRAY[$zsl_key]) ) {
				$SL['SL_ID'] = $SL['Evn_id'];

				// если уже есть законченный случай, значит добавляем в него SL
				$SL['CONS_DATA'] = array();
				$SL['DS2_DATA'] = array();
				$SL['DS3_DATA'] = array();
				$SL['KSG_KPG_DATA'] = array();
				$SL['NAPR_DATA'] = array();
				$SL['NAZ_DATA'] = array();
				$SL['ONK_SL_DATA'] = array();
				$SL['USL'] = array();

				$KSG_KPG_DATA = array();
				$ONK_SL_DATA = array();

				if ( !empty($USL['DATE_IN']) ) {
					$this->_IDSERV++;
					$USL['IDSERV'] = $this->_IDSERV;
					$SL['USL'][] = $USL;
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL['DS2_DATA'] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if (in_array($type, array(14,15)) || $data['Registry_IsZNO'] == 2) {
					if ( isset($CONS[$sl_key]) ) {
						$SL['CONS_DATA'] = $CONS[$sl_key];
					}

					if ( isset($NAPR[$sl_key]) ) {
						$SL['NAPR_DATA'] = $NAPR[$sl_key];
					}
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				foreach ( $KSG_KPG_fields as $index ) {
					if ( isset($SL[$index]) ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}
				}

				if ( !empty($KSG_KPG_DATA) ) {
					if (isset($SL_KOEF[$sl_key])) {
						$KSG_KPG_DATA['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
						unset($SL_KOEF[$sl_key]);
					}
					else {
						$KSG_KPG_DATA['SL_KOEF_DATA'] = array();
					}

					if ( isset($CRIT[$sl_key])) {
						$KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$sl_key];
						unset($CRIT[$sl_key]);
					}
					else {
						$KSG_KPG_DATA['CRIT_DATA'] = array();
					}

					$SL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
				}

				foreach ( $ONK_SL_FIELDS as $onkslfield ) {
					if ( isset($SL[$onkslfield]) ) {
						if ( !in_array($onkslfield, array('PROT', 'D_PROT')) ) {
							$ONK_SL_DATA[$onkslfield] = $SL[$onkslfield];
						}
						unset($SL[$onkslfield]);
					}
				}

				if ( isset($BPROT[$sl_key]) ) {
					$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
				}

				if ( isset($BDIAG[$sl_key]) ) {
					$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
				}

				if (isset($ONKOUSL[$sl_key])) {
					$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					foreach ( $ONK_SL_FIELDS as $onkslfield ) {
						if ( !in_array($onkslfield, array('PROT', 'D_PROT')) && !isset($ONK_SL_DATA[$onkslfield]) ) {
							$ONK_SL_DATA[$onkslfield] = null; // заполняем недостающие поля null
						}
					}

					if ( !isset($ONK_SL_DATA['B_PROT_DATA']) ) {
						$ONK_SL_DATA['B_PROT_DATA'] = array();
					}

					if ( !isset($ONK_SL_DATA['B_DIAG_DATA']) ) {
						$ONK_SL_DATA['B_DIAG_DATA'] = array();
					}

					if ( !isset($ONK_SL_DATA['ONK_USL_DATA']) ) {
						$ONK_SL_DATA['ONK_USL_DATA'] = array();
					}

					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][$zsl_key]['SL'][$sl_key] = $SL;

				$Registry_EvnNum[$SL['SL_ID']] = array(
					'MaxEvn_id' => $zsl_key,
					'Evn_id' => $sl_key,
					'Registry_id' => $ZSL['Registry_zid'],
					'IDCASE' => $this->_IDCASE,
					'N_ZAP' => $this->_N_ZAP,
				);
			}
			else {
				// иначе создаём новый ZAP
				$this->_IDCASE++;
				$this->_N_ZAP++;
				$this->_ID_PAC++;

				$SL['SL_ID'] = $SL['Evn_id'];
				$ZSL['IDCASE'] = $this->_IDCASE;
				$ZSL['N_ZAP'] = $this->_N_ZAP;

				$SL['CONS_DATA'] = array();
				$SL['DS2_DATA'] = array();
				$SL['DS3_DATA'] = array();
				$SL['KSG_KPG_DATA'] = array();
				$SL['NAPR_DATA'] = array();
				$SL['NAZ_DATA'] = array();
				$SL['ONK_SL_DATA'] = array();
				$SL['USL'] = array();

				$ZSL['SL'] = array();

				$KSG_KPG_DATA = array();
				$ONK_SL_DATA = array();

				$PACIENT['DOST'] = array();
				$PACIENT['DOST_P'] = array();
				$PACIENT['ID_PAC'] = $this->_ID_PAC;
				$OS_SLUCH = array();

				if ( isset($PACIENT['OS_SLUCH']) ) {
					$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT['OS_SLUCH']);
				}

				if ( isset($PACIENT['OS_SLUCH1']) ) {
					$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT['OS_SLUCH1']);
				}

				if ( array_key_exists('OS_SLUCH', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH']);
				}
				if ( array_key_exists('OS_SLUCH1', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH1']);
				}

				$ZSL['OS_SLUCH_DATA'] = $OS_SLUCH;

				if ( !empty($PACIENT['NOVOR']) && $PACIENT['NOVOR'] != '0' ) {
					if ( empty($PACIENT['FAM_P']) ) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 2);
					}

					if ( empty($PACIENT['IM_P']) ) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 3);
					}

					if ( empty($PACIENT['OT_P']) || strtoupper($PACIENT['OT_P']) == $netValue ) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 1);
					}
				}
				else {
					if ( empty($PACIENT['FAM']) ) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 2);
					}

					if ( empty($PACIENT['IM']) ) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 3);
					}

					if ( empty($PACIENT['OT']) || strtoupper($PACIENT['OT']) == $netValue ) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 1);
					}
				}

				if ( !empty($USL['DATE_IN']) ) {
					$this->_IDSERV++;
					$USL['IDSERV'] = $this->_IDSERV;
					$SL['USL'][] = $USL;
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL['DS2_DATA'] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ($type == 14 || $data['Registry_IsZNO'] == 2) {
					if ( isset($CONS[$sl_key]) ) {
						$SL['CONS_DATA'] = $CONS[$sl_key];
					}

					if ( isset($NAPR[$sl_key]) ) {
						$SL['NAPR_DATA'] = $NAPR[$sl_key];
					}
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				foreach ( $KSG_KPG_fields as $index ) {
					if ( isset($SL[$index]) ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}
				}

				if ( !empty($KSG_KPG_DATA) ) {
					if (isset($SL_KOEF[$sl_key])) {
						$KSG_KPG_DATA['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
						unset($SL_KOEF[$sl_key]);
					}
					else {
						$KSG_KPG_DATA['SL_KOEF_DATA'] = array();
					}

					if ( isset($CRIT[$sl_key])) {
						$KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$sl_key];
						unset($CRIT[$sl_key]);
					}
					else {
						$KSG_KPG_DATA['CRIT_DATA'] = array();
					}

					$SL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
				}

				foreach ( $ONK_SL_FIELDS as $onkslfield ) {
					if ( isset($SL[$onkslfield]) ) {
						if ( !in_array($onkslfield, array('PROT', 'D_PROT')) ) {
							$ONK_SL_DATA[$onkslfield] = $SL[$onkslfield];
						}
						unset($SL[$onkslfield]);
					}
				}

				if ( isset($BPROT[$sl_key]) ) {
					$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
				}

				if ( isset($BDIAG[$sl_key]) ) {
					$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
				}

				if (isset($ONKOUSL[$sl_key])) {
					$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					foreach ( $ONK_SL_FIELDS as $onkslfield ) {
						if ( !in_array($onkslfield, array('PROT', 'D_PROT')) && !isset($ONK_SL_DATA[$onkslfield]) ) {
							$ONK_SL_DATA[$onkslfield] = null; // заполняем недостающие поля null
						}
					}

					if ( !isset($ONK_SL_DATA['B_PROT_DATA']) ) {
						$ONK_SL_DATA['B_PROT_DATA'] = array();
					}

					if ( !isset($ONK_SL_DATA['B_DIAG_DATA']) ) {
						$ONK_SL_DATA['B_DIAG_DATA'] = array();
					}

					if ( !isset($ONK_SL_DATA['ONK_USL_DATA']) ) {
						$ONK_SL_DATA['ONK_USL_DATA'] = array();
					}

					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZSL['SL'][$sl_key] = $SL;

				$PACIENT_ARRAY[$zsl_key] = $PACIENT;

				$ZAP_ARRAY[$zsl_key] = array(
					'N_ZAP' => $this->_N_ZAP,
					'PR_NOV' => (!empty($ZSL['PR_NOV']) ? $ZSL['PR_NOV'] : 0),
					'PACIENT' => array($PACIENT),
					'Z_SL_DATA' => array($zsl_key => $ZSL)
				);

				$Registry_EvnNum[$SL['SL_ID']] = array(
					'MaxEvn_id' => $zsl_key,
					'Evn_id' => $sl_key,
					'Registry_id' => $ZSL['Registry_zid'],
					'IDCASE' => $this->_IDCASE,
					'N_ZAP' => $this->_N_ZAP,
				);

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
					'Evn_id' => $zsl_key,
					'RegistryData_RowNum' => $this->_N_ZAP,
				));
			}
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			$SD_Z += count($ZAP_ARRAY);
			// пишем в файл случаи
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys, false);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . count($ZAP_ARRAY) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP_ARRAY);

			// пишем в файл
			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $PACIENT_ARRAY), true, false, array(), false);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($PACIENT_ARRAY);
		}

		return $SD_Z;
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

		$IsZNOField = "case when R.Registry_IsZNO = 2 then 'true' else 'false' end as Registry_IsZNO,";
		
		if ( !empty($data['Registry_id']) ) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
			$IsZNOField = "ISNULL(R.Registry_IsZNO, 1) as Registry_IsZNO,";
		}
		if (isset($data['RegistryType_id']))
		{
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}
		if ($data['Registry_accYear'] > 0) {
			$filter .= ' and convert(varchar(4),cast(R.Registry_accDate as date),112) = :Registry_accYear';
			$params['Registry_accYear'] = $data['Registry_accYear'];
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
					DispClass.DispClass_Name,
					kn.KatNasel_Name,
					R.RegistryType_id,
					5 as RegistryStatus_id,
					2 as Registry_IsActive,
					RTrim(R.Registry_Num)+' / в очереди: '+LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
					{$IsZNOField}
					convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
					convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
					convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
					OrgSmo.OrgSmo_Nick as OrgSmo_Name,
					R.Lpu_id,
					R.OrgRSchet_id,
					R.Registry_IsRepeated,
					0 as Registry_Count,
					0 as Registry_ErrorCount,
					0 as Registry_Sum,
					1 as Registry_IsProgress,
					1 as Registry_IsNeedReform,
					'' as Registry_updDate
				from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
					left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = R.OrgSmo_id
					left join v_KatNasel kn with (NOLOCK) on kn.KatNasel_id = r.KatNasel_id
					left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
				where {$filter}
				order by R.Registry_endDate DESC
			";
		}
		else 
		{
			if (isset($data['RegistryStatus_id']))
			{
				$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
				$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
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
					R.Registry_IsRepeated,
					ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
					RTrim(R.Registry_Num) as Registry_Num,
					{$IsZNOField}
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
					RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),''))+' '+
					RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),108),'')) as Registry_updDate,
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
					NoPaid.RegistryNoPaid_Count,
					convert(varchar, RQH.RegistryQueueHistory_endDT, 104) + ' ' + convert(varchar, RQH.RegistryQueueHistory_endDT, 108) as ReformTime,
					rcs.RegistryCheckStatus_id,
					rcs.RegistryCheckStatus_Code,
					rcs.RegistryCheckStatus_Name,
					ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform
				from {$this->scheme}.v_Registry R with (NOLOCK)
					left join v_KatNasel kn with (NOLOCK) on kn.KatNasel_id = r.KatNasel_id
					left join v_DispClass DispClass (nolock) on DispClass.DispClass_id = R.DispClass_id
					left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					outer apply (
						select
							top 1 count(*) as RegistryNoPaid_Count
						from
							r3.v_RegistryData with (nolock)
						where
							Registry_id = R.Registry_id
							and ISNULL(RegistryData_isPaid, 1) = 1
					) NoPaid
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
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id and ISNULL(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1) RegistryPerson
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_{$this->RegistryNoPolisObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
					outer apply(
					    select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id 
					    from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) 
					    inner join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
					    where RE.Registry_id = R.Registry_id
					) RegistryErrorTFOMS
				where 
					{$filter}
				order by R.Registry_endDate DESC, R.Registry_updDT DESC
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
			'Registry_accDate' => $data['Registry_accDate'],
			'Registry_IsRepeated' => (!empty($data['Registry_IsRepeated']) ? $data['Registry_IsRepeated'] : null),
			'Registry_IsZNO' => (!empty($data['Registry_IsZNO']) ? $data['Registry_IsZNO'] : 1),
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
					{$fields}
					@Registry_Num = :Registry_Num,
					@Registry_accDate = @curdate, 
					@RegistryStatus_id = :RegistryStatus_id,
					@Registry_IsRepeated = :Registry_IsRepeated,
					@Registry_IsZNO = :Registry_IsZNO,
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
				convert(varchar,cast(Registry_begDate as datetime),112) as Registry_begDate,
				convert(varchar,cast(Registry_endDate as datetime),112) as Registry_endDate,
				KatNasel_id,
				DispClass_id,
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
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['Registry_IsRepeated'] = $row[0]['Registry_IsRepeated'];
				$data['OrgRSchet_id'] = $row[0]['OrgRSchet_id'];
				$data['KatNasel_id'] = $row[0]['KatNasel_id'];
				$data['DispClass_id'] = $row[0]['DispClass_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];
				$data['Registry_IsZNO'] = $row[0]['Registry_IsZNO'];

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
	 *	Проверка
	 */
	function checkErrorDataInRegistry($data) {
		$query = "Select rd.Registry_id from {$this->scheme}.v_RegistryData rd	where rd.Registry_id = :Registry_id  and (rd.Evn_id = :IDCASE OR :IDCASE IS NULL) and (rd.Person_id = :ID_PERS OR :ID_PERS IS NULL)";

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
				r.Registry_id,
				r.Registry_EvnNum,
				r.Registry_xmlExportPath,
				case when rgl.Registry_id is not null then 2 else 1 end as Registry_HasPaid
			from
				{$this->scheme}.v_Registry r (nolock)
				outer apply (
					select top 1 t1.Registry_id
					from {$this->scheme}.v_RegistryGroupLink t1 with (nolock)
						inner join {$this->scheme}.v_Registry t2 with (nolock) on t2.Registry_id = t1.Registry_id
					where t1.Registry_pid = r.Registry_id
						and t2.RegistryStatus_id = 4 -- Оплаченные
				) rgl
			where
				r.Registry_id = :Registry_id
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
	 *	Проверка
	 */
	function checkTFOMSErrorDataInRegistry($data) {
		if (!empty($data['Registry_EvnNum']) && count($this->Registry_EvnNum) == 0) {
			$this->Registry_EvnNum = json_decode($data['Registry_EvnNum'], true);
		}

		if (!empty($data['SL_ID'])) {
			if (!empty($this->Registry_EvnNum[$data['SL_ID']])) {
				if (is_array($this->Registry_EvnNum[$data['SL_ID']])) {
					return array(
						'MaxEvn_id' => $this->Registry_EvnNum[$data['SL_ID']]['MaxEvn_id'],
						'Evn_id' => $this->Registry_EvnNum[$data['SL_ID']]['Evn_id'],
						'Registry_id' => $this->Registry_EvnNum[$data['SL_ID']]['Registry_id'],
						'N_ZAP' => $this->Registry_EvnNum[$data['SL_ID']]['N_ZAP'],
					);
				}
			}
			else {
				$unionRegistryTypes = $this->getUnionRegistryTypes($data['Registry_id']);

				foreach ($unionRegistryTypes as $RegistryType_id) {
					$this->setRegistryParamsByType(['RegistryType_id' => $RegistryType_id], true);

					$check = $this->getFirstRowFromQuery("
						select top 1 Evn_id, Registry_id, RegistryData_RowNum as N_ZAP
						from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
						where Registry_id in (" . implode(',', $this->_unionRegistryContentByType[$RegistryType_id]) . ")
							and Evn_id = :Evn_id
					", [
						'Evn_id' => $data['SL_ID'],
					]);

					if ($check !== false && is_array($check) && count($check) > 0) {
						return $check;
					}
				}
			}
		}
		else if (!empty($data['IDCASE'])) {
			if (!empty($this->Registry_EvnNum[$data['IDCASE']])) {
				if (is_array($this->Registry_EvnNum[$data['IDCASE']])) {
					return array(
						'Evn_id' => $this->Registry_EvnNum[$data['IDCASE']]['Evn_id'],
						'Registry_id' => $this->Registry_EvnNum[$data['IDCASE']]['Registry_id'],
						'N_ZAP' => $this->Registry_EvnNum[$data['IDCASE']]['N_ZAP'],
					);
				}
			}
		}

		return false;
	}

	/**
	 *	Проверка
	 */
	function checkTFOMSErrorDataInRegistryByIDCASE($data) {
		if (!empty($data['Registry_EvnNum']) && count($this->Registry_EvnNum) == 0) {
			$this->Registry_EvnNum = json_decode($data['Registry_EvnNum'], true);
		}

		if (!empty($data['IDCASE'])) {
			$arr = array();

			// все случаи данного IDCASE
			foreach($this->Registry_EvnNum as $key => $Evn_Num) {
				if (is_array($Evn_Num) && !empty($Evn_Num['IDCASE']) && $Evn_Num['IDCASE'] == $data['IDCASE']) {
					$arr[] = array(
						'Evn_id' => $Evn_Num['Evn_id'],
						'Registry_id' => $Evn_Num['Registry_id'],
						'SL_ID' => $key,
						'N_ZAP' => $Evn_Num['N_ZAP'],
					);
				}
			}

			return $arr;
		}

		return false;
	}

	/**
	 *	Проверка
	 */
	function checkTFOMSErrorDataInRegistryByNZAP($data) {
		if (!empty($data['Registry_EvnNum']) && count($this->Registry_EvnNum) == 0) {
			$this->Registry_EvnNum = json_decode($data['Registry_EvnNum'], true);
		}

		if (!empty($data['N_ZAP'])) {
			$arr = array();

			// все случаи данного N_ZAP
			foreach($this->Registry_EvnNum as $key => $Evn_Num) {
				if (is_array($Evn_Num) && !empty($Evn_Num['N_ZAP']) && $Evn_Num['N_ZAP'] == $data['N_ZAP']) {
					$arr[] = array(
						'Evn_id' => $Evn_Num['Evn_id'],
						'Registry_id' => $Evn_Num['Registry_id'],
						'SL_ID' => $key,
					);
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
		}
	}

	/**
	 * Установка ошибок
	 */
	function setErrorFromImportRegistry($d, $data) 
	{
		// Сохранение загружаемого реестра, точнее его ошибок 
		
		$params = $d;
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
		$params['FLAG'] = $d['FLAG'];

		if (isset($this->_registryTypes[$data['Registry_id']])) {
			$data['RegistryType_id'] = $this->_registryTypes[$data['Registry_id']];
		}

		$this->setRegistryParamsByType($data, true);

		if (!isset($this->_registryTypes[$data['Registry_id']])) {
			$this->_registryTypes[$data['Registry_id']] = $this->RegistryType_id;
		}

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
							Insert {$this->scheme}.{$this->RegistryErrorObject} (Registry_id, {$this->RegistryDataEvnField}, RegistryErrorType_id, LpuSection_id, pmUser_insID, pmUser_updID, RegistryError_insDT, RegistryError_updDT)
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
					Insert {$this->scheme}.{$this->RegistryErrorObject} (Registry_id, {$this->RegistryDataEvnField}, RegistryErrorType_id, LpuSection_id, pmUser_insID, pmUser_updID, RegistryError_insDT, RegistryError_updDT)
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
				return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
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
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
		}

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

		if (isset($this->_registryTypes[$data['Registry_id']])) {
			$data['RegistryType_id'] = $this->_registryTypes[$data['Registry_id']];
		}

		$this->setRegistryParamsByType($data, true);

		if (!isset($this->_registryTypes[$data['Registry_id']])) {
			$this->_registryTypes[$data['Registry_id']] = $this->RegistryType_id;
		}

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
	 * Сохранение ошибок при импорте файл с результатами ПТК. 
	 *
	 * @param array $data
	 * @return array
	 */
	function setRegistryErrorTfoms($data) {
		$params['Evn_id'] = $data['Evn_id'];
		$params['Registry_id'] = $data['CaseRegistry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['COMMENT'] = 'Err_OutOfRegistry';

		$query = "
			select top 1
				RegistryErrorType_id,
				RegistryErrorType_Code,
				RegistryErrorType_Descr
			from 
				{$this->scheme}.RegistryErrorType with(nolock)
			where 
				RegistryErrorType_SysNick = :COMMENT
		";

		$resp = $this->queryResult($query, $params);

		if (count($resp) > 0) {
			$params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
			$params['RegistryErrorType_Code'] = $resp[0]['RegistryErrorType_Code'];
			$params['RegistryErrorType_Descr'] = $resp[0]['RegistryErrorType_Descr'];
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении кода ошибки!'));
		}

		$query = "
			insert into {$this->scheme}.RegistryErrorTFOMS with (rowlock) (
				Evn_id, 
				pmUser_insID, 
				pmUser_updID, 
				Registry_id, 
				RegistryErrorType_id, 
				RegistryErrorType_Code, 
				RegistryErrorTFOMS_Comment, 
				RegistryErrorTFOMS_insDT,
				RegistryErrorTFOMS_updDT
				)
			values(
				:Evn_id,
				:pmUser_id,
				:pmUser_id,
				:Registry_id,
				:RegistryErrorType_id,
				:RegistryErrorType_Code,
				:RegistryErrorType_Descr,
				dbo.tzGetDate(),
				dbo.tzGetDate()
			)
		";
		//exit(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		if ($result === true)
		{
			return array(array('success' => true, 'Error_Msg' => ''));
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Сохранение ошибок ПТК
	 */
	function setErrorImportRegistry($data) {
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['COMMENT'] = $data['COMMENT'];
		$params['Evn_id'] = $data['Evn_id'];

		$query = "
			select top 1
				RegistryErrorType_id,
				RegistryErrorType_Code,
				RegistryErrorClass_id
			from {$this->scheme}.RegistryErrorType with(nolock)
			where RegistryErrorType_Descr = :COMMENT
		";
		$resp = $this->queryResult($query, $params);

		if (count($resp) > 0) {
			$params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
			$params['RegistryErrorType_Code'] = $resp[0]['RegistryErrorType_Code'];
			$params['RegistryErrorClass_id'] = $resp[0]['RegistryErrorClass_id'];
		} else {
			$RegistryErrorClass_id = 1;
			$query = "
				select top 1
					isnull(max(RegistryErrorType_Code),0)+1 as RegistryErrorType_Code
				from {$this->scheme}.RegistryErrorType with(nolock)
				where RegistryErrorType_Code like '1[0-9][0-9][0-9]'
			";
			$code = $this->getFirstResultFromQuery($query, $params);
			if (!$code) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении кода новой ошибки!'));
			}
			$params['RegistryErrorType_Code'] = $code;
			$resp = $this->addRegistryErrorType(array(
				'RegistryErrorType_Code' => $params['RegistryErrorType_Code'],
				'RegistryErrorType_Name' => $params['COMMENT'],
				'RegistryErrorType_Descr' => $params['COMMENT'],
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

		if (isset($this->_registryTypes[$data['Registry_id']])) {
			$data['RegistryType_id'] = $this->_registryTypes[$data['Registry_id']];
		}

		$this->setRegistryParamsByType($data, true);

		if (!isset($this->_registryTypes[$data['Registry_id']])) {
			$this->_registryTypes[$data['Registry_id']] = $this->RegistryType_id;
		}

		$query = "
			Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_Comment, RegistryErrorClass_id, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
			Select
				rd.Registry_id, rd.Evn_id, :RegistryErrorType_id as RegistryErrorType_id, :RegistryErrorType_Code, :COMMENT, :RegistryErrorClass_id, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT
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
		$filter = "";

		if (isset($data['EvnPL_id'])) {
			$filter .= " and Evn_rid = :EvnPL_id";
			$data['RegistryType_id'] = 2;
		}
		if (isset($data['EvnPS_id'])) {
			$filter .= " and Evn_rid = :EvnPS_id";
			$data['RegistryType_id'] = 1;
		}
		if (isset($data['EvnPLStom_id'])) {
			$filter .= " and Evn_rid = :EvnPLStom_id";
			$data['RegistryType_id'] = 16;
		}
		if (isset($data['EvnVizitPL_id'])) {
			$filter .= " and Evn_id = :EvnVizitPL_id";
			$data['RegistryType_id'] = 2;
		}
		if (isset($data['EvnSection_id'])) {
			$filter .= " and Evn_id = :EvnSection_id";
			$data['RegistryType_id'] = 1;
		}
		if (isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and Evn_id = :EvnVizitPLStom_id";
			$data['RegistryType_id'] = 16;
		}
		if (isset($data['EvnPLDispDop13_id'])) {
			$filter .= " and Evn_id = :EvnPLDispDop13_id";
			$data['RegistryType_id'] = 7;
		}
		if (isset($data['EvnPLDispProf_id'])) {
			$filter .= " and Evn_id = :EvnPLDispProf_id";
			$data['RegistryType_id'] = 11;
		}
		if (isset($data['EvnPLDispOrp_id'])) {
			$filter .= " and Evn_id = :EvnPLDispOrp_id";
			$data['RegistryType_id'] = 9;
		}
		if (isset($data['EvnPLDispTeenInspection_id'])) {
			$filter .= " and Evn_id = :EvnPLDispTeenInspection_id";
			$data['RegistryType_id'] = 12;
		}
		if (isset($data['CmpCloseCard_id'])) {
			$filter .= " and Evn_id = :CmpCloseCard_id";
			$data['RegistryType_id'] = 6;
		}
		if (isset($data['EvnUslugaPar_id'])) {
			$filter .= " and Evn_id = :EvnUslugaPar_id";
			$data['RegistryType_id'] = 15;
		}

		if (empty($filter)) {
			return false;
		}

		$this->setRegistryParamsByType($data);

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
				(ISNULL(RD.RegistryData_isPaid, 1) = 2 or R.RegistryStatus_id not in (3, 4))
				and R.Lpu_id = :Lpu_id
				{$filter}
			
			union
			
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.{$this->RegistryDataTempObject} RD with (nolock) -- в процессе формирования
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
			where
				R.Lpu_id = :Lpu_id
				{$filter}
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
	 * Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
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
		// Проверяем возможность удалить объединенный реестр
		$query = "
			select
				rcs.RegistryCheckStatus_Code
			from
				{$this->scheme}.v_Registry r with (nolock)
				left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				r.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array(
			'Registry_id' => $data['id']
		));

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка статуса реестра)');
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) > 0 && $resp[0]['RegistryCheckStatus_Code'] == 1 ) {
			return array('Error_Msg' => 'Реестр заблокирован, удаление недопустимо');
		}

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

		// проверка статуса реестра
		if ( !empty($data['Registry_id']) ) {
			$query = "
				select
					rcs.RegistryCheckStatus_Code
				from
					{$this->scheme}.v_Registry r with (nolock)
					left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
				where
					r.Registry_id = :Registry_id
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка статуса реестра)');
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 && $resp[0]['RegistryCheckStatus_Code'] == 1 ) {
				return array('Error_Msg' => 'Реестр заблокирован, изменение недопустимо');
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
				@KatNasel_id = :KatNasel_id,
				@RegistryGroupType_id = :RegistryGroupType_id,
				@Lpu_id = :Lpu_id,
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
						$registrytypefilter = " and (R.RegistryType_id IN (1,2,6,15,16) OR (R.RegistryType_id IN (7,9,11,12) and kn.KatNasel_SysNick = 'inog')) and ISNULL(R.Registry_IsZNO, 1) = ISNULL(:Registry_IsZNO, 1)";
						break;
					case 2:
						$registrytypefilter = " and R.RegistryType_id = 14";
						break;
					case 11:
						$registrytypefilter = " and R.RegistryType_id IN (7,9,11,12) and kn.KatNasel_SysNick != 'inog'";
						break;
				}
				
				// 3. выполняем поиск реестров которые войдут в объединённый
				$query = "
					select
						R.Registry_id
					from
						{$this->scheme}.v_Registry R (nolock)
						left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
					where
						R.RegistryType_id <> 13
						and R.RegistryStatus_id = 2 -- к оплате
						and R.KatNasel_id = :KatNasel_id
						and R.Lpu_id = :Lpu_id
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and not exists(select top 1 RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink (nolock) where Registry_id = R.Registry_id)
						{$registrytypefilter}
				";
				$result_reg = $this->db->query($query, array(
					'KatNasel_id' => $data['KatNasel_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate'],
					'Registry_IsZNO' => $data['Registry_IsZNO'],
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
				ISNULL(R.Registry_IsZNO, 1) as Registry_IsZNO,
				convert(varchar,R.Registry_accDate,104) as Registry_accDate,
				convert(varchar,R.Registry_begDate,104) as Registry_begDate,
				convert(varchar,R.Registry_endDate,104) as Registry_endDate,
				R.KatNasel_id,
				R.RegistryGroupType_id,
				R.Lpu_id,
				RCS.RegistryCheckStatus_id,
				RCS.RegistryCheckStatus_Code
			from
				{$this->scheme}.v_Registry R (nolock)
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
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
				rtrim(isnull(convert(varchar,cast(R.Registry_updDT as datetime),104),''))+' '+
					rtrim(isnull(convert(varchar,cast(R.Registry_updDT as datetime),108),'')) as Registry_updDate,
				convert(varchar,R.Registry_endDate,104) as Registry_endDate,
				R.KatNasel_id,
				KN.KatNasel_Name,
				KN.KatNasel_SysNick,
				RGT.RegistryGroupType_Name,
				ISNULL(RS.Registry_SumPaid, 0.00) as Registry_SumPaid,
				RCS.RegistryCheckStatus_id,
				RCS.RegistryCheckStatus_Code,
				RCS.RegistryCheckStatus_Name
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry R (nolock) -- объединённый реестр
				left join v_RegistryGroupType RGT (nolock) on RGT.RegistryGroupType_id = R.RegistryGroupType_id
				left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
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
				R.Registry_endDate DESC,
				R.Registry_updDT DESC,
				R.Registry_id
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
		if (count($this->_unionRegistryTypes) > 0) {
			return $this->_unionRegistryTypes;
		}

		$resp = $this->queryResult("
			select r.RegistryType_id, r.Registry_id
			from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
				inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_pid
		", [
			'Registry_pid' => $Registry_pid
		]);

		if ($resp !== false) {
			foreach ($resp as $rec) {
				if (!in_array($rec['RegistryType_id'], $this->_unionRegistryTypes)) {
					$this->_unionRegistryTypes[] = $rec['RegistryType_id'];
					$this->_unionRegistryContentByType[$rec['RegistryType_id']] = [];
				}

				$this->_unionRegistryContentByType[$rec['RegistryType_id']][] = $rec['Registry_id'];
			}
		}

		return $this->_unionRegistryTypes;
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
		
		if (!empty($data['Registry_id']))
		{
			$query = "
				update
					{$this->scheme}.Registry with (rowlock)
				set
					Registry_xmlExportPath = :Status,
					Registry_EvnNum = :Registry_EvnNum,
					Registry_xmlExpDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
			";
			
			$result = $this->db->query($query,
                array(
					'Registry_id' => $data['Registry_id'],
					'Registry_EvnNum' => $data['Registry_EvnNum'],
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
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1:
			case 14:
				$this->RegistryObject = 'EvnPS';
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryErrorComObject = 'RegistryErrorComEvnPS';
				$this->RegistryDataTempObject = 'RegistryDataTempEvnPS';
				$this->RegistryNoPolisObject = 'RegistryEvnPSNoPolis';
				break;

			case 2:
			case 16:
				$this->MaxEvnField = 'Evn_rid';

				$this->RegistryObject = 'EvnPL';
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryErrorObject = 'RegistryError';
				$this->RegistryErrorComObject = 'RegistryErrorCom';
				$this->RegistryDataTempObject = 'RegistryDataTemp';
				$this->RegistryNoPolisObject = 'RegistryNoPolis';
				break;

			case 6:
				$this->RegistryObject = 'CmpCallCard';
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataObjectTable = 'RegistryDataCmp';
				$this->RegistryDataTempObject = 'RegistryDataTempCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryNoPolisObject = 'RegistryCmpNoPolis';
				break;

			case 7:
			case 9:
				$this->RegistryObject = 'PersonCard';
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
				$this->RegistryErrorComObject = 'RegistryErrorComDisp';
				$this->RegistryDataTempObject = 'RegistryDataTempDisp';
				$this->RegistryNoPolisObject = 'RegistryDispNoPolis';
				break;

			case 11:
			case 12:
				$this->RegistryObject = 'PersonCard';
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
				$this->RegistryErrorComObject = 'RegistryErrorComProf';
				$this->RegistryDataTempObject = 'RegistryDataTempProf';
				$this->RegistryNoPolisObject = 'RegistryProfNoPolis';
				break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryErrorComObject = 'RegistryErrorComPar';
				$this->RegistryDataTempObject = 'RegistryDataTemp';
				$this->RegistryNoPolisObject = 'RegistryParNoPolis';
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
	 * Простановка статуса реестра
	 */
	function setRegistryCheckStatus($data) {
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@curdate datetime = dbo.tzGetDate(),
				@rcs_id bigint = null;

			" . (!empty($data['RegistryCheckStatus_Code']) ? "
			set @rcs_id = (select top 1 RegistryCheckStatus_id from v_RegistryCheckStatus with (nolock) where RegistryCheckStatus_Code = :RegistryCheckStatus_Code);
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
	 * Проверка статуса реестра
	 * Возващает true, если статус "Заблокирован"
	 * @task https://redmine.swan.perm.ru/issues/70754
	 */
	function checkRegistryIsBlocked($data) {
		$query = "
			select
				rcs.RegistryCheckStatus_Code
			from
				{$this->scheme}.v_Registry r with (nolock)
				left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				r.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if ( !is_object($result) ) {
			return true;
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) > 0 && $resp[0]['RegistryCheckStatus_Code'] == 1 ) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Отметки об оплате случаев
	 */
	function loadRegistryDataPaid($data) {
		$this->setRegistryParamsByType($data);

		// В зависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);
		$join = "";
		$fields = "";

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id, ";
		}

		$query = "
			-- addit with 
			with PE (
				Person_id,
				PersonEvn_id,
				PersonEvn_insDT
			) as (
				select
					t1.Person_id,
					t1.PersonEvn_id,
					t1.PersonEvn_insDT
				from v_PersonEvn t1 with (nolock)
					inner join {$this->scheme}.v_{$this->RegistryDataObject} t2 with (nolock) on t2.Person_id = t1.Person_id
				where t2.Registry_id = :Registry_id
			)
			-- end addit with 

			Select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				{$fields}
				RD.needReform,
				case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit,
				RD.RegistryData_deleted,
				RTrim(RD.NumCard) as EvnPL_NumCard,
				RD.Person_FirName,
				RD.Person_SurName,
				RD.Person_SecName,
				RD.Polis_Num,
				RTrim(RD.Person_FIO) as Person_FIO,
				convert(varchar(10),RD.Person_BirthDay,104) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_id,
				RTrim(RD.LpuSection_Name) as LpuSection_Name,
				RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
				convert(varchar(10),RD.Evn_setDate,104) as EvnVizitPL_setDate,
				convert(varchar(10),RD.Evn_disDate,104) as Evn_disDate,
				RD.RegistryData_Tariff RegistryData_Tariff,
				RD.RegistryData_KdFact as RegistryData_Uet,
				RD.RegistryData_KdPay as RegistryData_KdPay,
				RD.RegistryData_KdPlan as RegistryData_KdPlan,
				RD.RegistryData_ItogSum as RegistryData_ItogSum,
				RD.RegistryData_IsPaid as RegistryData_IsPaid,
				RET.RegistryErrorType_id,
				RET.RegistryErrorClass_id,
				RET.RegistryErrorType_Code,
				RET.RegistryErrorTFOMS_id,
				null as Registry_xmlExportFile,
				RET.RegistryErrorTFOMS_RowNum as RegistryData_EvnNum,
				1 as RecordStatus_Code
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK)
				{$join}
				left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
				outer apply (
					select top 1 t2.RegistryErrorType_id, ISNULL(t1.RegistryErrorClass_id, t2.RegistryErrorClass_id) as RegistryErrorClass_id, t2.RegistryErrorType_Code, t1.RegistryErrorTFOMS_id, t1.RegistryErrorTFOMS_Comment, t1.RegistryErrorTFOMS_RowNum
					from {$this->scheme}.v_RegistryErrorTFOMS t1 with(nolock)
						left join {$this->scheme}.v_RegistryErrorType t2 with(nolock) on t2.RegistryErrorType_id = t1.RegistryErrorType_id
					where t1.Registry_id = RD.Registry_id and t1.Evn_id = RD.Evn_id and ISNULL(t1.RegistryErrorClass_id, t2.RegistryErrorClass_id) = 1
				) as RET
				outer apply (
					select top 1 PersonEvn_id
					from PE with (NOLOCK)
					where Person_id = RD.Person_id
						and PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
				) PersonEvn
			-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";

		$this->load->library('textlog', array('file'=>'loadRegistryDataPaid_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add(getDebugSQL($query, $params));

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$resp = $result->result('array');

			$exp = $this->getRegistryExportInfo($data);
			$evn_num_arr = json_decode($exp['Registry_EvnNum'], true);
			if (is_array($evn_num_arr)) {
				foreach ( $evn_num_arr as $key => $value ) {
					if ( is_array($value) ) {
						$evn_num_arr[$value['Evn_id']] = $value['N_ZAP'];
					}
					else {
						$evn_num_arr = array_flip($evn_num_arr);
					}
				}
			}
			$file_name = null;
			if (!empty($exp['Registry_xmlExportFile']) && $exp['Registry_xmlExportFile'] != 1) {
				$arr = explode('/',$exp['Registry_xmlExportFile']);
				$file_name = $arr[count($arr)-1];
				$file_name_array = explode('.', $file_name);
				if ( count($file_name_array) > 1 ) {
					unset($file_name_array[count($file_name_array) - 1]);
					$file_name = implode('.', $file_name_array);
				}
			}
			foreach($resp as &$registry_data) {
				$key = $registry_data['Evn_id'];
				if(isset($evn_num_arr[$key]) && empty($registry_data['RegistryData_EvnNum'])) {
					$registry_data['RegistryData_EvnNum'] = $evn_num_arr[$key];
				}
				if (empty($registry_data['Registry_xmlExportFile'])) {
					$registry_data['Registry_xmlExportFile'] = $file_name;
				}
			}

			return $resp;
		}

		return false;
	}

	/**
	 * Данные об экспорте реестра
	 */
	function getRegistryExportInfo($data) {
		$params = array('Registry_id' => $data['Registry_id']);

		$query = "
			select top 1 R.Registry_EvnNum, R.Registry_xmlExportPath as Registry_xmlExportFile
			from {$this->scheme}.v_RegistryGroupLink RGL with(nolock)
				inner join {$this->scheme}.v_Registry R with(nolock) on R.Registry_id = RGL.Registry_pid
			where RGL.Registry_id = :Registry_id
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		return $resp;
	}

	/**
	 * Загрузка справочника типов ошибок
	 */
	function loadRegistryErrorType($data) {
		$query = "
			select
				RET.RegistryErrorType_id,
				RET.RegistryErrorType_Code,
				RET.RegistryErrorType_Name,
				RET.RegistryType_id,
				RET.RegistryErrorClass_id
			from {$this->scheme}.v_RegistryErrorType RET with (nolock)
		";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Изменение отметки об оплате случаев
	 */
	function setRegistryDataPaidFromJSON($data) {
		//$RegistryErrorType_id1016 = $this->getFirstResultFromQuery("select top 1 RegistryErrorType_id from {$this->scheme}.RegistryErrorType with (nolock) where RegistryErrorType_Code = '1016'", array());

		if (!empty($data['RegistryDataPaid'])) {
			$RegistryDataPaid = json_decode($data['RegistryDataPaid'],true);

			foreach($RegistryDataPaid as $record) {
				$record['Registry_id'] = $data['Registry_id'];
				$record['pmUser_id'] = $data['pmUser_id'];

				$response = $this->deleteRegistryDataErrorTFOMS($record);
				if (is_array($response) && count($response) > 0 && !empty($response[0]['Error_Msg'])) {
					return $response;
				}

				if ( empty($record['RegistryErrorType_Code']) && $record['RegistryData_IsPaid'] == 1 ) {
					$record['RegistryErrorType_Code'] = '1016';
					//return array(array('success' => false, 'Error_Msg' => 'Обнаружен случай, отмеченный как неоплаченный, у которого не указан код ошибки ТФОМС'));
				}
				else if ( !empty($record['RegistryErrorType_Code']) && $record['RegistryData_IsPaid'] == 2 ) {
					$record['RegistryErrorType_Code'] = null;
				}

				if(!empty($record['RegistryErrorType_Code'])) {
					$params = $record;
					$params['OSHIB'] = $record['RegistryErrorType_Code'];
					$params['IM_POL'] = null;
					$params['BAS_EL'] = null;
					$params['COMMENT'] = $record['Registry_xmlExportFile'];
					$params['ROWNUM'] = $record['RegistryData_EvnNum'];
					$params['FATALITY'] = 1;

					$response = $this->setErrorFromTFOMSImportRegistry($params);
					if (!empty($response[0]['Error_Msg'])) {
						return $response;
					}
				}
				/*$response = $this->setRegistryDataPaid($record);
				if (!empty($response[0]['Error_Msg'])) {
					return $response;
				}*/
			}

			$params = array();
			$params['Registry_id'] = $data['Registry_id'];
			$params['pmUser_id'] = $data['pmUser_id'];
			$response = $this->setRegistryPaid($params);
			if (!empty($response[0]['Error_Msg'])) {
				return $response;
			}
		}
		return array(array('Registry_id' => $data['Registry_id'], 'success' => true));
	}

	/**
	 * Удаление ошибок
	 */
	function deleteRegistryDataErrorTFOMS($data) {
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id']
		);
		$query = "
			delete from {$this->scheme}.RegistryErrorTFOMS with (rowlock)
			where Registry_id = :Registry_id and Evn_id = :Evn_id;
		";
		$result = $this->db->query($query, $params);
		return true;
	}

	/**
	 * Проверка включен ли реестр в объединённый
	 */
	function checkDeleteRegistryInGroupLink($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
		$query = "
			select top 1
				RGL.Registry_pid
			from
				{$this->scheme}.v_RegistryGroupLink RGL with (nolock)
			where
				RGL.Registry_id = :Registry_id
		";

		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id']
			)
		);

		if (is_object($result)) {
			$r = $result->result('array');

			if ( is_array($r) && count($r) > 0 ) {
				return array('success' => true, 'Alert_Msg' => 'Реестр входит в объединенный реестр ' . $r[0]['Registry_pid'] . '. Вы уверены что хотите удалить реестр?');
			} else {
				return false;
			}
		} else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных реестра)');
		}
	}
	/**
	 * Удаление связи реестра с объединенным
	 */
	function deleteRegistryInGroupLink($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
		
		$query = "
			Delete from {$this->scheme}.v_RegistryGroupLink
			where Registry_id = :Registry_id;
		";

		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id']
			)
		);
		return true;
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
				AND FLKSettings_EvnData LIKE '%buryatiya%'
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
	 * Получение списка событий
	 * @task https://redmine.swan.perm.ru//issues/119221
	 */
	public function getEvnIdList($data) {
		return $this->_getEvnDiagPLStomList($data);
	}

	/**
	 * Получение списка заболеваний в рамках стомат. ТАП
	 * @task https://redmine.swan.perm.ru//issues/119221
	 */
	protected function _getEvnDiagPLStomList($data) {
		if ( empty($data['Evn_id']) || empty($data['Evn_rid']) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$resposne = array();

		$queryResult = $this->queryResult("
			declare @EvnClass_id bigint = (select top 1 EvnClass_id from v_Evn with (nolock) where Evn_id = :Evn_id);

			select Evn_id
			from {$this->scheme}.v_RegistryData with (nolock)
			where EvnClass_id = @EvnClass_id
				and Evn_rid = :Evn_rid
				and Registry_id = :Registry_id
		", array(
			'Evn_id' => $data['Evn_id'],
			'Evn_rid' => $data['Evn_rid'],
			'Registry_id' => $data['Registry_id'],
		));

		if ( is_array($queryResult) && count($queryResult) > 0 ) {
			foreach ( $queryResult as $row ) {
				$response[] = $row['Evn_id'];
			}
		}

		return $response;
	}

	/**
	 *	Список случаев по пациентам без документов ОМС
	 */
	public function loadRegistryNoPolis($data) {
		$this->setRegistryParamsByType($data);

		$filter = "(1=1)";
		$filterAddQueryTemp = null;
		$returnQueryOnly = isset($data['returnQueryOnly']);

		if ( isset($data['Filter']) ) {
			$filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

			if ( is_array($filterData) ) {
				foreach ( $filterData as $column => $value ) {
					if ( is_array($value) ) {
						$r = null;

						foreach ( $value as $d ) {
							$r .= "'".trim(toAnsi($d))."',";
						}

						if ( $column == 'Evn_id' )
							$column = 'RNP.'.$column;
						elseif ( $column == 'Person_FIO' )
							$column = "rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, ''))";//'RE.'.$column;
						elseif ( $column == 'LpuSection_Name' )
							$column = "(rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name)";//'RD.'.$column;

						$r = rtrim($r, ',');

						$filterAddQueryTemp[] = $column.' IN ('.$r.')';
					}
				}

			}

			if ( is_array($filterAddQueryTemp) ) {
				$filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
			}
			else {
				$filterAddQuery = "and (1=1)";
			}
		}

		$filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;

		if ( $data['Registry_id'] <= 0 ) {
			return false;
		}

		if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
			return false;
		}

		$params = array('Registry_id' => $data['Registry_id']);

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		$orderBy = "
			RNP.Person_SurName,
			RNP.Person_FirName,
			RNP.Person_SecName,
			LpuSection_Name
		";

		if (!empty($data['sort'])) {
			$sortDir = ('DESC' == $data['dir']) ? 'DESC' : 'ASC';
			switch($data['sort']) {
				case 'LpuSection_Name':
					$data['sort'] = "cast(LpuSection.LpuSection_Code as BIGINT)";
					break;
				case 'Person_BirthDay':
					$data['sort'] = "RNP.".$data['sort'];
					break;
				case 'Person_OrgSmo':
					$data['sort'] = "OrgSMO.Orgsmo_f002smocod";
					break;
				case 'Person_PolisDate':
					$data['sort'] = "pol.Polis_begDate {$sortDir}, pol.Polis_endDate";
					break;
				case 'Evn_disDate':
				case 'Evn_setDate':
					$data['sort'] = "Evn.".str_replace('Date', "DT", $data['sort']);
					break;
			}
			$orderBy = " {$data['sort']} {$sortDir}";
			$params['sort'] = $data['sort'];
		}

		if ( $this->RegistryType_id == 6 ) {
			$query = "
				select
					-- select
					RNP.Registry_id,
					RNP.CmpCloseCard_id as Evn_id,
					null as Evn_rid,
					null as Evn_setDT,
					RNP.Person_id,
					ps.Server_id,
					ps.PersonEvn_id,
					rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
					convert(varchar(10), RNP.Person_BirthDay, 104) as Person_BirthDay,
					rtrim(CP.CmpProfile_Code) + '. ' + CP.CmpProfile_Name as LpuSection_Name,
					rtrim(IsNull(pol.Polis_Ser, '')) +rtrim(IsNull(' №'+pol.Polis_Num,'')) as Person_Polis,
					IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
					MP.Person_Fio as MedPersonal_Fio,
					IsNull(OrgSMO.Orgsmo_f002smocod,'') + ' ' + IsNull(OrgSMO.OrgSMO_Nick,'') as Person_OrgSmo,
					convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), CClC.AcceptTime, 108) as Evn_setDate,
					convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), CClC.BackTime, 108) as Evn_disDate
					-- end select
				from
					-- from
					{$this->scheme}.v_{$this->RegistryNoPolisObject} RNP with (NOLOCK)
					left join v_CmpCloseCard CClC with (NOLOCK) on CClC.CmpCloseCard_id = RNP.CmpCloseCard_id
					left join v_CmpEmergencyTeam CET with (NOLOCK) on CET.CMPEmergencyTeam_id = CClC.EmergencyTeam_id
					left join v_CmpProfile CP with (NOLOCK) on CP.CmpProfile_id = CET.CmpProfile_id
					left join v_PersonState ps with (nolock) on ps.Person_id = RNP.Person_id
					left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
					left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = pol.OrgSmo_id
					outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = CClC.MedPersonal_id
					) as MP
					-- end from
				where
					-- where
					RNP.Registry_id=:Registry_id
					and {$filter}
					{$filterAddQuery}
					-- end where
				order by
					-- order by
					{$orderBy}
					-- end order by
			";
		}
		else {
			$query = "
				select
					-- select
					RNP.Registry_id,
					RNP.Evn_id,
					Evn.Evn_rid,
					RNP.Person_id,
					Evn.Server_id,
					Evn.PersonEvn_id,
					rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
					convert(varchar(10), RNP.Person_BirthDay, 104) as Person_BirthDay,
					rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name,
					convert(varchar(10), Evn.Evn_setDT, 104)+' '+convert(varchar(5), Evn.Evn_setDT, 108) as Evn_setDT,
					rtrim(IsNull(pol.Polis_Ser, '')) +rtrim(IsNull(' №'+pol.Polis_Num,'')) as Person_Polis,
					IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
					RTRIM(ISNULL(RNP.Person_Fio, '')) as MedPersonal_Fio,
					IsNull(OrgSMO.Orgsmo_f002smocod,'') + ' ' + IsNull(OrgSMO.OrgSMO_Nick,'') as Person_OrgSmo,
					convert(varchar(10), Evn.Evn_setDT, 104)+' '+convert(varchar(5), Evn.Evn_setDT, 108) as Evn_setDate,
					convert(varchar(10), Evn.Evn_disDT, 104)+' '+convert(varchar(5), Evn.Evn_disDT, 108) as Evn_disDate
					-- end select
				from
					-- from
					{$this->scheme}.v_{$this->RegistryNoPolisObject} RNP with (NOLOCK)
					left join v_LpuSection LpuSection with (NOLOCK) on LpuSection.LpuSection_id = RNP.LpuSection_id
					left join v_Evn Evn with(nolock) on Evn.Evn_id = RNP.Evn_id
					left join v_PersonState ps with (nolock) on ps.Person_id = RNP.Person_id
					left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
					left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = pol.OrgSmo_id
					left join v_EvnSection es (nolock) on ES.EvnSection_id = RNP.Evn_id
					-- end from
				where
					-- where
					RNP.Registry_id=:Registry_id
					and {$filter}
					{$filterAddQuery}
					-- end where
				order by
					-- order by
					{$orderBy}
					-- end order by
			";
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
}