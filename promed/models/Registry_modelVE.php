<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry_model - модель для работы с таблицей Registry
 *
 * Task#18011
 * модификация оригинального Registry_model.php для групповой постановке реестров на очередь формирования Task#18011 
 */

class Registry_modelVE extends swModel {
	var $scheme = "r2";
	var $isufa = false;
	var $RegistryType_id = null;
	var $RegistryDataObject = 'RegistryData';
	var $RegistryErrorComObject = 'RegistryErrorCom';
	var $RegistryErrorObject = 'RegistryError';
	var $RegistryDataEvnField = 'Evn_id';
	var $RegistryPersonObject = 'RegistryPerson';
	var $RegistryDoubleObject = 'RegistryDouble';

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 *	Получение данных по реестру
	 */
	function loadRegistryData($data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		if ($data['RegistryType_id']==0)
		{
			return false;
		}

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and RD.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and RD.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and RD.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}
		if(!empty($data['Polis_Num'])) {
			$filter .= " and RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}
		
		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		$join = "";
		$fields = "";
		
		if (empty($this->isufa))
		{
			$join = "
				outer apply (
					select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
				) RDL
			";
			$join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
			$fields = "case when RDL.Person_id is null then 0 else 1 end as IsRDL, ";
			$fields .= "RD.needReform, RD.checkReform, RD.timeReform, ";
			$fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
		}
		else
		{
		
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			
			$join .= "
				left join v_UslugaComplex U with (NOLOCK) on RD.Usluga_id =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_Diag D with (NOLOCK) on RD.Diag_id =  D.Diag_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RD.Evn_id
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields .= "
				case when RD.RegistryType_id = 1 then
					m.Mes_Code
				else
					U.UslugaComplex_Code
				end as Usluga_Code, 
			";
			$fields .= "D.Diag_Code, case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as Paid, ";
			$fields .= "LB.LpuBuilding_Name, ";
		}
		
		if ($data['session']['region']['nick'] == 'perm') {
			if ($data['filterRecords'] == 2) {
				$filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 1";
			}
			
			// в реестрах со статусом частично принят помечаем оплаченные случаи
			$join .= "left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id ";
			$join .= "left join v_RegistryCheckStatus RCS with (nolock) on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id ";
			$fields .= "case when RCS.RegistryCheckStatus_Code = 3 then ISNULL(RD.RegistryData_IsPaid,1) else 0 end as RegistryData_IsPaid, ";
		}
		
		// Полка
		if (($data['RegistryType_id'] == 1) || ($data['RegistryType_id'] == 2) || ($data['RegistryType_id'] == 4) || ($data['RegistryType_id'] == 5) || ($data['RegistryType_id'] == 6))
		{
			if (isset($data['RegistryStatus_id']) && (6==$data['RegistryStatus_id'])) {
                $source_table = 'v_RegistryDeleted_Data';
            } else {
                $source_table = 'v_RegistryData';
            }
            $query = "
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
					RD.RegistryData_KdFact as RegistryData_Uet,
					RD.RegistryData_KdPay as RegistryData_KdPay,
					RD.RegistryData_KdPlan as RegistryData_KdPlan,
					RD.RegistryData_ItogSum as RegistryData_ItogSum,
					RegistryError.Err_Count as Err_Count
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD with (NOLOCK)
					{$join}
					outer apply
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryError RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
					) RegistryError
					outer apply
					(
						Select top 1 PersonEvn_id
						from v_PersonEvn PE with (NOLOCK)
						where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
					) PersonEvn
				-- end from
				where
					-- where
					RD.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}
		/*
		 echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		 echo getDebugSql(getCountSQLPH($query), $params);
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
	 *	Список общих ошибок
	 */
	function loadRegistryErrorCom($data)
	{

		if ($data['Registry_id']==0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$params = array('Registry_id' => $data['Registry_id']);
		if (empty($this->isufa)) { $tempscheme = $this->scheme; } else { $tempscheme = 'dbo'; }
		$query = "
		Select
			RE.Registry_id,
			RE.RegistryErrorType_id,
			RE.RegistryErrorType_Code,
			RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
			RE.RegistryErrorType_Descr,
			RE.RegistryErrorClass_id,
			RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name
		from {$tempscheme}.v_RegistryErrorCom RE with (NOLOCK)
		where
			RE.Registry_id=:Registry_id
		order by RE.RegistryErrorType_Code";
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
	 *	Список ошибок
	 */
	function loadRegistryError($data)
	{
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
			$filter .= " and RE.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and RE.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and RE.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryError_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryError_Code ";
			$params['RegistryError_Code'] = $data['RegistryError_Code'];
		}
		if (isset($data['RegistryErrorType_id']))
		{
			$filter .= " and RE.RegistryErrorType_id = :RegistryErrorType_id ";
			$params['RegistryErrorType_id'] = $data['RegistryErrorType_id'];
		}
		if (!empty($data['MedPersonal_id'])) {
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$join = "";
		$fields = "";
		if (empty($this->isufa))
		{
			$join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
			$fields .= "RD.needReform, RE.RegistryErrorType_Form, RE.MedStaffFact_id,"; // , RD.checkReform, RD.timeReform
			$fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
			$fields .= "RE.LpuUnit_id, RE.MedPersonal_id, ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted, ";
		}
		else
		{
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			
			$join .= "
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
				left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id
				outer apply (
					select top 1
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1 with (nolock)
						left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = evpl.EvnVizitPL_id
						and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
					order by
						t1.EvnUslugaCommon_setDT desc
				) EU
				left join v_UslugaComplex U (nolock) on EU.UslugaComplex_uid =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RE.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RE.Registry_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
				) as MP
			";
			$fields .= "
				MP.Person_Fio as MedPersonal_Fio, 
				case when R.RegistryType_id = 1 then
					m.Mes_Code
				else
					U.UslugaComplex_Code
				end as Usluga_Code, 
				LB.LpuBuilding_Name, 
			";
		}

		$query = "
			Select
				-- select
				RTrim(cast(RE.Registry_id as char))+RTrim(cast(IsNull(RE.Evn_id,0) as char))+RTrim(cast(RE.RegistryErrorType_id as char)) as RegistryError_id,
				RE.Registry_id,
				RE.Evn_id,
				RE.Evn_rid,
				RE.EvnClass_id,
				RE.RegistryErrorType_id,
				RE.RegistryErrorType_Code,
				{$fields}
				RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
				RE.RegistryErrorType_Descr,
				RE.Person_id,
				RE.Server_id,
				RE.PersonEvn_id,
				RTrim(RE.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RE.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RE.LpuSection_id,
				RTrim(RE.LpuSection_name) as LpuSection_name,
				RTrim(IsNull(convert(varchar,cast(RE.Evn_setDate as datetime),104),'')) as Evn_setDate,
				RTrim(IsNull(convert(varchar,cast(RE.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RE.RegistryErrorClass_id,
				RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryError RE with (NOLOCK)
				left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				{$join}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";

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
	 *	Непонятная хрень
	 */
	function doRegistryPersonIsDifferent($data)
	{
		$query = "
			declare
				 @Error_Code bigint
				,@Person1_id bigint
				,@Person2_id bigint
				,@Error_Message varchar(4000);
			
			select
				@Person1_id = Person_id,
				@Person2_id = Person2_id
			from 
				{$this->scheme}.v_RegistryPerson with (nolock)
			where
				Registry_id = :Registry_id
				and MaxEvnPerson_id = :MaxEvnPerson_id;
				
			exec pd.p_PersonNotDoubles_ins 
				@Person_id = @Person1_id,
				@Person_did = @Person2_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (empty($resp[0]['Error_Message'])) {
				$query = "
					update {$this->scheme}.RegistryPerson with (rowlock)
					set
						RegistryPerson_IsDifferent = 2
					where
						Registry_id = :Registry_id
						and MaxEvnPerson_id = :MaxEvnPerson_id
				";
					
				$result = $this->db->query($query, $data);
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка типов ошибок в реестре
	 */
	function loadRegistryErrorType($data)
	{
		if ($data['Registry_id']<=0) {
			return false;
		}
		$params = array('Registry_id'=>$data['Registry_id']);
		$query = "

		Select distinct
			RegistryErrorType.RegistryErrorType_id,
			RegistryErrorType_Code,
			RegistryErrorType_Name,
			--RegistryErrorType_Descr,
			RegistryType_id
		from RegistryErrorType with (nolock)
		cross apply
		(
			Select top 1 Evn_id from {$this->scheme}.v_RegistryError RE with (nolock) where RE.Registry_id = :Registry_id and RegistryErrorType.RegistryErrorType_id = RE.RegistryErrorType_id
		) as Registry
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
	 *	Опять непонятная загрузка каких-то данных
	 */
	function loadRegistryNoPolis($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array('Registry_id' => $data['Registry_id']);
		$query = "
		Select
			RNP.Registry_id,
			RNP.Evn_id,
			RNP.Evn_rid as Evn_rid,
			RNP.Person_id,
			RNP.Server_id,
			RNP.PersonEvn_id,
			rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
			RTrim(IsNull(convert(varchar,cast(RNP.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name
		from {$this->scheme}.v_RegistryNoPolis RNP with (NOLOCK)
		left join v_LpuSection LpuSection with (NOLOCK) on LpuSection.LpuSection_id = RNP.LpuSection_id
		where
			RNP.Registry_id=:Registry_id
		order by RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName, LpuSection.LpuSection_Name";
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
	 *	И снова что-то непонятное
	 */
	function loadRegistryPerson($data)
	{
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
		/*$filter="(1=1)";
		 if (isset($data['Person_SurName']))
		 {
			$filter .= " and Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
			}
			if (isset($data['Person_FirName']))
			{
			$filter .= " and Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
			}
			if (isset($data['Person_SecName']))
			{
			$filter .= " and Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
			}
			*/
		$query = "
			Select
				-- select
				RP.MaxEvnPerson_id,
				RP.MaxEvnPerson_id as PersonEvn_id,
				RP.Registry_id,
				RP.Person_id,
				RP.Person2_id,
				case when ISNULL(RP.Person2_SurName,'')!='' and RP.Person_SurName != RP.Person2_SurName then
					rtrim(IsNull(RP.Person_SurName,'')) + IsNull('<br/><font color=\"red\">'+RP.Person2_SurName+'<font>','')
				else
					rtrim(IsNull(RP.Person_SurName,''))
				end as Person_SurName,
				case when ISNULL(RP.Person2_FirName,'')!='' and RP.Person_FirName != RP.Person2_FirName then
					rtrim(IsNull(RP.Person_FirName,'')) + IsNull('<br/><font color=\"red\">'+RP.Person2_FirName+'<font>','')
				else
					rtrim(IsNull(RP.Person_FirName,''))
				end as Person_FirName,
				case when ISNULL(RP.Person2_SecName,'')!='' and RP.Person_SecName != RP.Person2_SecName then
					rtrim(IsNull(RP.Person_SecName,'')) + IsNull('<br/><font color=\"red\">'+RP.Person2_SecName+'<font>','')
				else
					rtrim(IsNull(RP.Person_SecName,''))
				end as Person_SecName,
				case when RP.Person2_BirthDay is not null and RP.Person_BirthDay != RP.Person2_BirthDay then
					rtrim(convert(varchar,cast(RP.Person_BirthDay as datetime),104)) + IsNull('<br/><font color=\"red\">'+rtrim(convert(varchar,cast(RP.Person2_BirthDay as datetime),104))+'<font>','')
				else
					rtrim(convert(varchar,cast(RP.Person_BirthDay as datetime),104))
				end as Person_BirthDay,

				RD.needReform,
				case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit,
				rtrim(IsNull(RP.Polis_Ser, '')) +' №'+rtrim(RP.Polis_Num)  + IsNull('<br/><font color=\"red\">'+rtrim(IsNull(RP.Polis2_Ser,'')) +' №'+rtrim(RP.Polis2_Num)+'<font>','') as Person_Polis,

				IsNull(convert(varchar,cast(RP.Polis2_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(RP.Polis2_endDate as datetime),104),'...') as Person_PolisDate,

				IsNull(convert(varchar,cast(Evn.Evn_setDT as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'...') as Person_EvnDate,

				IsNull(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') + IsNull('<br/><font color=\"red\">'+IsNull(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')+' '+RTrim(OrgSMO2.OrgSMO_Nick)+'<font>','') as Person_OrgSmo
			-- end select
			from
				-- from
				{$this->scheme}.RegistryPerson RP with (NOLOCK)
			left join {$this->scheme}.v_RegistryData RD with (NOLOCK) on RD.Registry_id = RP.Registry_id and RD.Evn_id = RP.MaxEvnPerson_id
			left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
			left join Evn with (NOLOCK) on Evn.Evn_id = RD.Evn_id
			left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = RP.OrgSmo_id
			left join v_OrgSmo OrgSmo2 with (NOLOCK) on OrgSmo2.OrgSmo_id = RP.OrgSmo2_id
			-- end from
			where
				-- where
				RP.Registry_id = :Registry_id
				and ISNULL(RP.RegistryPerson_IsDifferent, 1) = 1
				-- end where
			order by
				-- order by
				RP.Person_SurName, RP.Person_FirName
				-- end order by
		";

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
	 *	Список ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{
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
		
		$addToSelect = "";
		$leftjoin = "";
		
		if ($data['session']['region']['nick'] == 'perm') {
			$addToSelect = ", retl.RegistryErrorTFOMSLevel_Name";
			$leftjoin = "left join v_RegistryErrorTFOMSLevel retl with (nolock) on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id";
		}
		
		$query = "
		Select 
			-- select
			RegistryErrorTFOMS_id,
			RE.Registry_id,
			Evn.Evn_rid,
			RE.Evn_id,
			Evn.EvnClass_id,
			ret.RegistryErrorType_Code,
			RegistryErrorType_Name as RegistryError_FieldName,
			RegistryErrorType_Descr + ' (' +RETF.RegistryErrorTFOMSField_Name + ')' as RegistryError_Comment,
			rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
			ps.Person_id, 
			ps.PersonEvn_id, 
			ps.Server_id, 
			RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			RegistryErrorTFOMS_FieldName,
			RegistryErrorTFOMS_BaseElement,
			RegistryErrorTFOMS_Comment,
			ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
			{$addToSelect}
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
			left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
			left join RegistryErrorTFOMSField RETF with (nolock) on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
			left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
			left join RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
			{$leftjoin}
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
		order by
			-- order by
			RE.RegistryErrorType_Code
			-- end order by";
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
	 *	Список ошибок БДЗ
	 */
	function loadRegistryErrorBDZ($data)
	{
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
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, '')) like :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		
		if (!empty($data['RegistryType_id']) && $data['RegistryType_id'] == 6) {
			$query = "
			Select 
				-- select
				RegistryErrorBDZ_id,
				RE.Registry_id,
				CCC.CmpCallCard_id as Evn_rid,
				RE.Evn_id,
				null as EvnClass_id,
				case when (rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) <> rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))) then
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) + '<br/><font color=\"red\">'+rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))+'</font>'
				else
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
				end as Person_FIO,
				ps.Person_id, 
				re.Person_id as Person2_id,
				ps.PersonEvn_id, 
				ps.Server_id, 
				case when RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) != RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),'')) then
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) + '<br/><font color=\"red\">'+RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),''))+'</font>'
				else
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),''))
				end as Person_BirthDay,
				case when rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) <> rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) then
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) + '<br/><font color=\"red\">'+rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) +'</font>'
				else 
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,''))
				end
				as Person_Polis,
				-- IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
				IsNull(convert(varchar,cast(CCC.CmpCallCard_prmDT as datetime),104),'...') as Person_EvnDate,
				IsNull(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') + IsNull('<br/><font color=\"red\">'+IsNull(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')+' '+RTrim(OrgSMO2.OrgSMO_Nick)+'<font>','') as Person_OrgSmo,
				RegistryErrorBDZ_Comment as RegistryError_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				RE.RegistryErrorBDZ_Comment
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryErrorBDZ RE with (nolock)
				left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_CmpCallCard ccc with (nolock) on ccc.CmpCallCard_id = RE.Evn_id
				outer apply(
					select top 1
						 pa.PersonEvn_id
						,pa.Server_id
						,pa.Person_id
						,ISNULL(pa.Person_SurName, '') as Person_Surname
						,ISNULL(pa.Person_FirName, '') as Person_Firname
						,ISNULL(pa.Person_SecName, '') as Person_Secname
						,pa.Person_BirthDay as Person_Birthday
						,ISNULL(pa.Sex_id, 0) as Sex_id
						,pa.Person_EdNum
						,pa.Polis_id
					from
						v_Person_all pa with (nolock)
					where
						Person_id = CCC.Person_id
						and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
					order by
						PersonEvn_insDT desc
				) PS
				left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id = RE.OrgSmo_id
				left join v_OrgSmo OrgSmo2 with (nolock) on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorBDZ_id
				-- end order by";
		} else {
			$query = "
			Select 
				-- select
				RegistryErrorBDZ_id,
				RE.Registry_id,
				Evn.Evn_rid,
				RE.Evn_id,
				Evn.EvnClass_id,
				case when (rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) <> rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))) then
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) + '<br/><font color=\"red\">'+rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))+'</font>'
				else
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
				end as Person_FIO,
				ps.Person_id, 
				re.Person_id as Person2_id,
				ps.PersonEvn_id, 
				ps.Server_id, 
				case when RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) != RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),'')) then
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) + '<br/><font color=\"red\">'+RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),''))+'</font>'
				else
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),''))
				end as Person_BirthDay,
				case when rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) <> rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) then
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) + '<br/><font color=\"red\">'+rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) +'</font>'
				else 
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,''))
				end
				as Person_Polis,
				-- IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
				IsNull(convert(varchar,cast(Evn.Evn_setDT as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(Evn.Evn_disDate as datetime),104),'...') as Person_EvnDate,
				IsNull(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') + IsNull('<br/><font color=\"red\">'+IsNull(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')+' '+RTrim(OrgSMO2.OrgSMO_Nick)+'<font>','') as Person_OrgSmo,
				RegistryErrorBDZ_Comment as RegistryError_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				RE.RegistryErrorBDZ_Comment
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryErrorBDZ RE with (nolock)
				left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id = RE.OrgSmo_id
				left join v_OrgSmo OrgSmo2 with (nolock) on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorBDZ_id
				-- end order by";
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
				R.KatNasel_id,
				R.Registry_Num,
				Lpu.Lpu_Email,
				Lpu.Lpu_Nick
			from {$this->scheme}.v_Registry R (nolock)
			left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = R.Lpu_id
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
				LP.Lpu_Ouz as HC,
				RTrim(R.Registry_Num) as NSH,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as DSH,
				isnull(Lpu_RegNomC, '') as RG,
				isnull(Lpu_RegNomN, '') as RNL,
				cast(isnull(R.Registry_Sum, 0.00) as float) as SUMS,
				0 as TS,
				1 as VR,
				1 as RE,
				'За выполненные услуги с TX1 по TX2' as TX,
				OB.OrgBank_Code as K_BANR,
				OB.OrgBank_KSchet as KS_BAN,
				case
					when RegistryType_id=4 then 9
					when RegistryType_id=5 then 7
					else '' end as MFO_BAN,
				ORS.OrgRSchet_RSchet as SCHET,
				1 as RC_P,
				368 as RN_P,
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),'')) as DISMEN,
				OG.Org_INN as INN,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as DNP,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as DKP,
				OG.Org_Phone as TEL,
				OHGlav.OrgHeadPerson_Fio as GL,
				OHIspoln.OrgHeadPerson_Fio as ISP
			from {$this->scheme}.v_Registry R with (NOLOCK)
			inner join v_Lpu LP (nolock) on LP.Lpu_id = R.Lpu_id
			inner join Org OG (nolock) on LP.Org_id = OG.Org_id
			left join OrgRSchet ORS (nolock) on R.OrgRSchet_id = ORS.OrgRSchet_id
			left join v_OrgBank OB (nolock) on OB.OrgBank_id = ORS.OrgBank_id
			outer apply (
				select
					top 1 rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHeadPerson_Fio
				from OrgHead OH (nolock)
				inner join v_PersonState PS (nolock) on PS.Person_id = OH.Person_id
				where
					OH.Lpu_id=LP.Lpu_id and OH.OrgHeadPost_id = 1
			) as OHGlav
			outer apply (
				select
					top 1 rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHeadPerson_Fio
				from OrgHead OH (nolock)
				inner join v_PersonState PS (nolock) on PS.Person_id = OH.Person_id
				where
					OH.Lpu_id=LP.Lpu_id and OH.OrgHeadPost_id = 3
			) as OHIspoln
			where
			{$filter}
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных по услугам для выгрузки реестра в DBF
	 */
	function loadRegistryUslDataForDbfUsing($type, $data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		$params = array();
		$params['Registry_id'] = $data['Registry_id'];

		switch ($type)
		{
			case 1: //stac
				$query = "exec {$this->scheme}.p_RegistryPS_expRU @Registry_id = :Registry_id";
				break;
			case 2: //polka
				$query = "exec {$this->scheme}.p_RegistryPL_expRU @Registry_id = :Registry_id";
				break;
			case 3: //receipt
				$query = "";
				break;
			case 4: //dd
				$query = "exec {$this->scheme}.p_RegistryDD_expRU @Registry_id = :Registry_id";
				break;
			case 5: //orp
				$query = "exec {$this->scheme}.p_RegistryOrp_expRU @Registry_id = :Registry_id";
				break;
			case 6: //smp
				$query = "exec {$this->scheme}.p_RegistrySmp_expRU @Registry_id = :Registry_id";
				break;
		}

		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Получение данных реестра для выгрузки в DBF
	 */
	function loadRegistryDataForDbfUsing($type, $data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		$params = array();
		$params['Registry_id'] = $data['Registry_id'];

		switch ($type)
		{
			case 1: //stac
				$query = "exec {$this->scheme}.p_RegistryPS_exp @Registry_id = :Registry_id";
				break;
			case 2: //polka
				$query = "exec {$this->scheme}.p_RegistryPL_exp @Registry_id = :Registry_id";
				break;
			case 3: //receipt
				$query = "";
				break;
			case 4: //dd
				$query = "exec {$this->scheme}.p_RegistryDD_exp @Registry_id = :Registry_id";
				break;
			case 5: //orp
				$query = "exec {$this->scheme}.p_RegistryOrp_exp @Registry_id = :Registry_id";
				break;
			case 6: //smp
				$query = "exec {$this->scheme}.p_RegistrySmp_exp @Registry_id = :Registry_id";
				break;
		}
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			//Вместо сгенерированных данных результата возвращаем сам объект результата
			//данные из него будем получать по строкам. Память то не резиновая.
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *	Получение данных для выгрузки реестра в XML
	 */
	function loadRegistryDataForXmlUsingCommon($type, $data)
	{
		$person_field = "ID_PAC";
		$paytype = '';
		if (isset($data['PayType_SysNick']) && ($data['PayType_SysNick']=='ovd')) {
			$paytype = 'OVD';
		}
		switch ($type)
		{
			case 1: //stac
				$p_schet = $this->scheme.".p_Registry_EvnPS".$paytype."_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPS".$paytype."_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPS".$paytype."_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPS".$paytype."_expPac";
				break;
			case 2: //polka
				$p_schet = $this->scheme.".p_Registry_EvnPL".$paytype."_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPL".$paytype."_expVizit";
				switch ($this->scheme)
				{
					case "r2": 
						$p_usl = $this->scheme.".p_Registry_EvnPL".$paytype."_expUsl";
						$person_field = "ID_PERS";
						break;
					default: 
						$p_usl = $this->scheme.".p_Registry_EvnPL".$paytype."_expUsl";
						$person_field = "ID_PAC";
						break;
				}
				$p_pers = $this->scheme.".p_Registry_EvnPL".$paytype."_expPac";
				break;			
			case 4: //dd
				$p_schet = $this->scheme.".p_Registry_EvnPLDD_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLDD_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLDD_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLDD_expPac";
				break;
			case 5: //orp
				$p_schet = $this->scheme.".p_Registry_EvnPLOrp_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLOrp_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLOrp_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLOrp_expPac";
				break;
			case 6: //smp
				$p_schet = $this->scheme.".p_Registry_SMP_expScet";
				$p_vizit = $this->scheme.".p_Registry_SMP_expVizit";
				$p_usl = $this->scheme.".p_Registry_SMP_expUsl";
				$p_pers = $this->scheme.".p_Registry_SMP_expPac";
				break;
			default:
				return false;
		}
		// шапка		
		$query = "
			exec {$p_schet} @Registry_id = ?
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');
		}
		else {
			return false;
		}		
		// посещения
		$query = "
			exec {$p_vizit} @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$visits = $result->result('array');
			$SLUCH = array();
			// привязываем услуги к случаю
			foreach( $visits as $visit )
			{
				if ( !empty($visit['IDCASE']) ) {
					if ( !isset($SLUCH[$visit['IDCASE']]) ) {
						$SLUCH[$visit['IDCASE']] = array();
					}

					$SLUCH[$visit['IDCASE']][] = $visit;
				}
			}
			unset($visits);
		}
		else {
			return false;
		}
				
		// услуги
		if (strlen($p_usl)>0) {
			$query = "
				exec {$p_usl} @Registry_id = ?
			";		
			$result = $this->db->query($query, array($data['Registry_id']));

			if ( is_object($result) ) {
				$uslugi = $result->result('array');
				$USL = array();
				// привязываем услуги к случаю			
				$i = 1;
				foreach( $uslugi as $usluga )
				{
					$usluga['IDSERV'] = $i;
					if ( !isset($USL[$usluga['MaxEvn_id']]) )
						$USL[$usluga['MaxEvn_id']] = array();
					$USL[$usluga['MaxEvn_id']][] = $usluga;
					$i++;
				}
				unset($uslugi);
			}
			else {
				return false;
			}
		}
		// люди
		$query = "
			exec {$p_pers} @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$person = $result->result('array');
			$PACIENT = array();
			// привязываем персона к случаю
			foreach( $person as $pers ) {
				if ( !empty($pers[$person_field]) ) {
					$PACIENT[$pers[$person_field]] = $pers;
				}
			}
			unset($person);
		}
		else {
			return false;
		}
		// собираем массив для выгрузки
		$data = array();
		$data['SCHET'] = array($header[0]);
		// массив с записями
		$data['ZAP'] = array();
		foreach ( $PACIENT as $key => $value )
			$data['ZAP'][$key]['PACIENT'] = array($value);
		/*
		echo "<pre>";
		print_r($SLUCH);
		die();
		*/
		foreach($SLUCH as $key => $value )
		{
			foreach($value as $k => $val)
				if ( isset($USL[$key]) )
					$value[$k]['USL'] = $USL[$key];
				else
					$value[$k]['USL'] = array();
			$data['ZAP'][$key]['SLUCH'] = $value;
		}
		$i = 1;
		foreach ( $data['ZAP'] as $key => $value )
		{
			$data['ZAP'][$key]['N_ZAP'] = $i;
			$i++;
			if ( !isset($data['ZAP'][$key]['SLUCH']) )
				unset($data['ZAP'][$key]);
		}
		$data['PACIENT'] = $PACIENT;
		return $data;
	}

	/**
	 *	Загрузка данных по очереди реестров
	 */
	function loadRegistryQueue($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id']);
		$filter .= ' and RQ.Lpu_id = :Lpu_id';


		if (isset($data['Registry_id']))
		{
			$filter .= ' and RQ.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}

		$query = "
		Select
			RegistryQueue_id,
			RegistryQueue_Position
		from {$this->scheme}.v_RegistryQueue RQ with (NOLOCK)
		where
		{$filter}
			";
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			$r = $result->result('array');
			if (count($r)==0)
			{
				// Сформировался реестр или ничего не было
				$r[0]['RegistryQueue_id'] = 0;
				$r[0]['RegistryQueue_Position'] = 0;
			}
			return $r;
		}
		else {
			return false;
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
		
		$addToSelect = "";
		$leftjoin = "";
		
		if ($data['session']['region']['nick'] == 'perm') {
			$addToSelect .= ", R.PayType_id as PayType_id, pt.PayType_Name as PayType_Name";
			$leftjoin = "left join v_PayType pt with (nolock) on pt.PayType_id = R.PayType_id";
		}
		
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
		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id']==5))
		{//запрос для реестров в очереди
			
			if ($data['session']['region']['nick'] == 'perm') {
				$addToSelect .= ", 0 as MekErrors_IsData, 0 as FlkErrors_IsData, 0 as BdzErrors_IsData, 0 as Registry_SumPaid, '' as Registry_sendDate";
			}
			
			$query = "
			Select
				R.RegistryQueue_id as Registry_id,
				R.KatNasel_id,
				R.RegistryType_id,
				5 as RegistryStatus_id,
				R.RegistryStacType_id,
				2 as Registry_IsActive,
				RTrim(R.Registry_Num)+' / в очереди: '+LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--R.Registry_Sum,
				KatNasel.KatNasel_Name,
				KatNasel.KatNasel_SysNick,
				RTrim(RegistryStacType.RegistryStacType_Name) as RegistryStacType_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				R.LpuBuilding_id,
				LpuBuilding.LpuBuilding_Name,
				0 as Registry_Count,
				0 as Registry_RecordPaidCount,
				0 as Registry_KdCount,
				0 as Registry_KdPaidCount,
				0 as Registry_Sum,
				1 as Registry_IsProgress,
				1 as Registry_IsNeedReform,
				'' as Registry_updDate,
				-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
				0 as RegistryErrorCom_IsData,
				0 as RegistryError_IsData,
				0 as RegistryPerson_IsData,
				0 as RegistryNoPolis_IsData,
				0 as RegistryNoPay_IsData,
				0 as RegistryErrorTFOMS_IsData,
				0 as RegistryErrorTFOMSType_id,
				0 as RegistryNoPay_Count,
				0 as RegistryNoPay_UKLSum,
				0 as RegistryNoPaid_Count, 
				null as RegistryCheckStatus_id,
				-1 as RegistryCheckStatus_Code,
				'' as RegistryCheckStatus_Name,
				1 as Registry_IsNeedReform
				{$addToSelect}
			from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
			left join KatNasel with (nolock) on KatNasel.KatNasel_id = R.KatNasel_id
			left join LpuBuilding with (nolock) on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
			left join RegistryStacType with (nolock) on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
			{$leftjoin}
			where {$filter}";
		}
		else
		{//для всех реестров, кроме тех что в очереди
			$source_table = 'v_Registry';
			if (isset($data['RegistryStatus_id']))
			{
				if (6 == (int)$data['RegistryStatus_id']) {
					//6 - если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
					//т.к. для удаленных реестров статус не важен - не накладываем никаких условий на статус реестра.
				} else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
				// только если оплаченные!!!
				if( 4 == (int)$data['RegistryStatus_id'] ) {
					if( $data['Registry_accYear'] > 0 ) {
						$filter .= ' and convert(varchar(4),cast(R.Registry_begDate as date),112) <= :Registry_accYear';
						$filter .= ' and convert(varchar(4),cast(R.Registry_endDate as date),112) >= :Registry_accYear';
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}
			if (empty($this->isufa)) { $tempscheme = $this->scheme; } else { $tempscheme = 'dbo'; }
			
			if ($data['session']['region']['nick'] == 'perm') {
				$addToSelect .= ", RegistryErrorMEK.MekErrors_IsData, RegistryErrorFLK.FlkErrors_IsData, RegistryErrorBDZ.BdzErrors_IsData,
				RTrim(IsNull(convert(varchar,cast(R.Registry_sendDT as datetime),104),''))+' '+RTrim(IsNull(convert(varchar,cast(R.Registry_sendDT as datetime),108),'')) as Registry_sendDate,
				isnull(R.Registry_SumPaid, 0.00) as Registry_SumPaid
				";
				$leftjoin .= " 
					outer apply(
						select top 1 case when RE.Registry_id is not null then 1 else 0 end as MekErrors_IsData 
						from 
							{$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) 
							left join RegistryErrorTFOMSType RET with (nolock) on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
						where RE.Registry_id = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_MEK'
					) RegistryErrorMEK
					outer apply(
						select top 1 case when RE.Registry_id is not null then 1 else 0 end as FlkErrors_IsData 
						from 
							{$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) 
							left join RegistryErrorTFOMSType RET with (nolock) on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
						where RE.Registry_id = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_FLK'
					) RegistryErrorFLK
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as BdzErrors_IsData from RegistryErrorBDZ RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorBDZ
				
				";
				$addToSelect .= ", ISNULL('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:' + CAST(R.Registry_id as varchar) + '});''>'+RegistryCheckStatus.RegistryCheckStatus_Name+'</a>','') as RegistryCheckStatus_Name";
			} else {
				$addToSelect .= ", RegistryCheckStatus.RegistryCheckStatus_Name";
			}
			
			$query = "
			Select
				R.Registry_id,
				R.KatNasel_id,
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.RegistryStacType_id,
				R.Registry_IsActive,
				RTrim(R.Registry_Num) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--R.Registry_Sum,
				KatNasel.KatNasel_Name,
				KatNasel.KatNasel_SysNick,
				R.LpuBuilding_id,
				LpuBuilding.LpuBuilding_Name,
				RTrim(RegistryStacType.RegistryStacType_Name) as RegistryStacType_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				isnull(R.Registry_RecordCount, 0) as Registry_Count,
				isnull(R.Registry_RecordPaidCount, 0) as Registry_RecordPaidCount,
				isnull(R.Registry_KdCount, 0) as Registry_KdCount,
				isnull(R.Registry_KdPaidCount, 0) as Registry_KdPaidCount,
				isnull(R.Registry_Sum, 0.00) as Registry_Sum,
				--  and RQ.Registry_id is null) or (RQ.RegistryQueueHistory_endDT is null and RQ.Registry_id = R.Registry_id)
				case when (RQ.RegistryQueueHistory_id is not null) and (RQ.RegistryQueueHistory_endDT is null) then 1 else 0 end as Registry_IsProgress,
				isnull(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				--isnull(RData.Registry_Count, 0) as Registry_Count,
				--isnull(RData.Registry_Sum, 0.00) as Registry_Sum,
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),''))+' '+
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),108),'')) as Registry_updDate,

				-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
				RegistryErrorCom.RegistryErrorCom_IsData,
				RegistryError.RegistryError_IsData,
				RegistryPerson.RegistryPerson_IsData,
				RegistryNoPolis.RegistryNoPolis_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMSType_id,
				case when RegistryNoPay_Count>0 then 1 else 0 end as RegistryNoPay_IsData,
				RegistryNoPay.RegistryNoPay_Count as RegistryNoPay_Count,
				RegistryNoPay.RegistryNoPay_UKLSum as RegistryNoPay_UKLSum,
				RegistryNoPaid.RegistryNoPaid_Count as RegistryNoPaid_Count,
				R.RegistryCheckStatus_id,
				ISNULL(RegistryCheckStatus.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				case when (select count(*) from {$tempscheme}.RegistryDouble with(nolock) where Registry_id = R.Registry_id) > 0 then 1 else 0 end as issetDouble
				{$addToSelect}
			from {$this->scheme}.{$source_table} R with (NOLOCK)
			left join KatNasel with (nolock) on KatNasel.KatNasel_id = R.KatNasel_id
			left join RegistryStacType with (NOLOCK) on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
			left join LpuBuilding with (nolock) on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
			left join RegistryCheckStatus with (NOLOCK) on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			{$leftjoin}
			outer apply(
				select top 1
					RegistryQueueHistory_id,
					RegistryQueueHistory_endDT,
					RegistryQueueHistory.Registry_id
				from RegistryQueueHistory with (NOLOCK)
				where RegistryQueueHistory.Registry_id = R.Registry_id
				order by RegistryQueueHistory_id desc
			) RQ
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$tempscheme}.v_RegistryErrorCom RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorCom
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_RegistryError RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_RegistryPerson RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryPerson
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_RegistryNoPolis RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
			outer apply(
				select

					count(RegistryNoPay.Evn_id) as RegistryNoPay_Count,
					sum(RegistryNoPay.RegistryNoPay_UKLSum) as RegistryNoPay_UKLSum
				from {$this->scheme}.v_RegistryNoPay RegistryNoPay with (NOLOCK)
				where RegistryNoPay.Registry_id = R.Registry_id
			) RegistryNoPay
			outer apply(
				select
					count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
				from {$this->scheme}.v_RegistryData RDnoPaid with (NOLOCK)
				where RDnoPaid.Registry_id = R.Registry_id and ISNULL(RDnoPaid.RegistryData_isPaid, 1) = 1
			) RegistryNoPaid
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
	 *	Функция возрвращает массив годов, в которых есть реестры
	 */
	function getYearsList($data)
	{
		$query = "
			select distinct
				convert(varchar(4),cast(Registry_begDate as date),112) as reg_year
			from
				{$this->scheme}.Registry with(nolock)
			where
				Lpu_id = :Lpu_id
				and RegistryStatus_id = :RegistryStatus_id
				and RegistryType_id = :RegistryType_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Функция возрвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data)
	{
		$result = array(
		array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
		array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
		/*array('RegistryType_id' => 3, 'RegistryType_Name' => 'Рецепты'),*/
		/*array('RegistryType_id' => 4, 'RegistryType_Name' => 'Дополнительная диспансеризация'),*/
		/*array('RegistryType_id' => 5, 'RegistryType_Name' => 'Диспансеризация детей-сирот'),*/
		array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь')
		);
		return $result;
	}

	/**
	 *	Функция возрвращает набор данных для дерева реестра 2-го уровня (статус реестра)
	 */
	function loadRegistryStatusNode($data)
	{
		$result = array(
		array('RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'),
		array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
		array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
		array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
		array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Удаленные')
		);
		return $result;
	}

	/**
	 *	Сохранение реестра
	 */
	function saveRegistry($data)
	{
		$addToQuery = "";
		if ($data['session']['region']['nick'] == 'perm') {
			$addToQuery = "@PayType_id = :PayType_id,";
		}

        
		if (0 == $data['Registry_id'])
		{
			$data['Registry_IsActive']=2;
			$proc = 'p_Registry_ins';
		}
		else
		{
			$proc = 'p_Registry_upd';
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
			'Registry_accDate' => $data['Registry_accDate'],
			'KatNasel_id' => $data['KatNasel_id'],
			'PayType_id' => $data['PayType_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'pmUser_id' => $data['pmUser_id'],
			'reform' => $data['reform'],
		);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Registry_id bigint = :Registry_id,
				@curdate datetime = dbo.tzGetDate();
			exec ".$this->scheme.".".$proc. "
				@Registry_id = @Registry_id output,
				@Lpu_id = :Lpu_id,
				@RegistryType_id = :RegistryType_id,
				@RegistryStatus_id = :RegistryStatus_id,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@KatNasel_id = :KatNasel_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@Registry_Num = :Registry_Num,
				@Registry_Sum = 0,
				@Registry_IsActive = :Registry_IsActive,
				@OrgRSchet_id = :OrgRSchet_id,
				@Registry_accDate = @curdate,
				@variant = 1,
				@reform = :reform,
				@pmUser_id = :pmUser_id,
				{$addToQuery}
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Registry_id as Registry_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
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

		$params = array
		(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'RegistryStacType_id' => $data['RegistryStacType_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'pmUser_id' => $data['pmUser_id']
		);
		$fields = "";
		switch ($data['RegistryType_id'])
		{
			case 1:
				$params['KatNasel_id'] = $data['KatNasel_id'];
				$fields .= "@KatNasel_id = :KatNasel_id,";
				if ($data['session']['region']['nick'] == 'perm') {
					$params['PayType_id'] = $data['PayType_id'];
					$fields .= "@PayType_id = :PayType_id,";
				}
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$fields .= "@LpuBuilding_id = :LpuBuilding_id,";
				break;
			case 2:
				$params['KatNasel_id'] = $data['KatNasel_id'];
				$fields .= "@KatNasel_id = :KatNasel_id,";
				if ($data['session']['region']['nick'] == 'perm') {
					$params['PayType_id'] = $data['PayType_id'];
					$fields .= "@PayType_id = :PayType_id,";
				}
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$fields .= "@LpuBuilding_id = :LpuBuilding_id,";
				// Переформирование по записям, пока только на полке
				if (isset($data['reform']))
				{
					$params['reform'] = $data['reform'];
					$fields .= "@reform = :reform,";
				}
				break;
			case 6:
				$params['KatNasel_id'] = $data['KatNasel_id'];
				$fields .= "@KatNasel_id = :KatNasel_id,";
				if ($data['session']['region']['nick'] == 'perm') {
					$params['PayType_id'] = $data['PayType_id'];
					$fields .= "@PayType_id = :PayType_id,";
				}

				break;
			default:
				break;
		}

		if (($data['RegistryType_id']==1) || ($data['RegistryType_id']==2) || ($data['RegistryType_id']==4) || ($data['RegistryType_id']==5) || ($data['RegistryType_id']==6))
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
					@RegistryStacType_id = :RegistryStacType_id,
					@Registry_id = :Registry_id,
					@RegistryType_id = :RegistryType_id,
					@Lpu_id = :Lpu_id,
					@OrgRSchet_id = :OrgRSchet_id,
					@Registry_begDate = :Registry_begDate,
					@Registry_endDate = :Registry_endDate,
					{$fields}
					@Registry_Num = :Registry_Num,
					@Registry_accDate = @curdate,
					@RegistryStatus_id = :RegistryStatus_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @RegistryQueue_id as RegistryQueue_id, @RegistryQueue_Position as RegistryQueue_Position, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}
	}

	/**
	 *	Переформирование реестра
	 */
	function reformRegistry($data)
	{
		$addToSelect = "";
		if ($data['session']['region']['nick'] == 'perm') {
			$addToSelect = ",PayType_id";
		}
		$query = "
			select
				Registry_id,
				Lpu_id,
				RegistryType_id,
				RegistryStatus_id,
				RegistryStacType_id,
				convert(varchar,cast(Registry_begDate as datetime),112) as Registry_begDate,
				convert(varchar,cast(Registry_endDate as datetime),112) as Registry_endDate,
				KatNasel_id,
				LpuBuilding_id,
				Registry_Num,
				Registry_Sum,
				Registry_IsActive,
				OrgRSchet_id,
				convert(varchar,cast(Registry_accDate as datetime),112) as Registry_accDate
				{$addToSelect}
			from
				{$this->scheme}.v_Registry Registry with (NOLOCK)
			where
				Registry_id = ?
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				//$data['Registry_id'] = $data['Registry_id'];
				//$data['Lpu_id'] = $data['Lpu_id'];
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['RegistryStacType_id'] = $row[0]['RegistryStacType_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['KatNasel_id'] = $row[0]['KatNasel_id'];
				if ($data['session']['region']['nick'] == 'perm') {
					$data['PayType_id'] = $row[0]['PayType_id'];
				}
				$data['LpuBuilding_id'] = $row[0]['LpuBuilding_id'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['OrgRSchet_id'] = $row[0]['OrgRSchet_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];
				//$data['pmUser_id'] = $data['pmUser_id'];
				// Переформирование реестра
				//return  $this->saveRegistry($data);
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
	 *	Еще какое-то переформирование реестра
	 */
	function reformErrRegistry($data)
	{
		$addToSelect = "";
		if ($data['session']['region']['nick'] == 'perm') {
			$addToSelect = ", R.PayType_id as PayType_id";
		}
		
		$query = "
			select
				Registry_id,
				Lpu_id,
				RegistryType_id,
				RegistryStatus_id,
				RegistryStacType_id,
				convert(varchar,cast(Registry_begDate as datetime),112) as Registry_begDate,
				convert(varchar,cast(Registry_endDate as datetime),112) as Registry_endDate,
				KatNasel_id,
				LpuBuilding_id,
				Registry_Num,
				Registry_Sum,
				Registry_IsActive,
				OrgRSchet_id,
				convert(varchar,cast(Registry_accDate as datetime),112) as Registry_accDate
				{$addToSelect}
			from
				{$this->scheme}.v_Registry Registry with (NOLOCK)
			where
				Registry_id = ?
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				//$data['Registry_id'] = $data['Registry_id'];
				//$data['Lpu_id'] = $data['Lpu_id'];
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['RegistryStacType_id'] = $row[0]['RegistryStacType_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['KatNasel_id'] = $row[0]['KatNasel_id'];
				if ($data['session']['region']['nick'] == 'perm') {
					$data['PayType_id'] = $row[0]['PayType_id'];
				}
				$data['LpuBuilding_id'] = $row[0]['LpuBuilding_id'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['OrgRSchet_id'] = $row[0]['OrgRSchet_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];
				$data['reform'] = 1;
				//$data['pmUser_id'] = $data['pmUser_id'];
				// Переформирование реестра
				if ((isset($data['session']['setting']['lpu']['check_access_reform'])) && ($data['session']['setting']['lpu']['check_access_reform']==1)) // здесь надо добавить настройку-проверку, если ЛПУ можно выгружать реестры без постановки в очередь
				{
					if ($this->checkActualRecordRegistry($data)===true) // проверка на то, что все изменения по записям уже дошли на реплику
					{
						return  $this->saveRegistry($data);
					}
					else
					{
						return array('success' => false, 'Error_Msg' => 'Переформирование реестра на данный момент невозможно, <br/> поскольку не все измененные записи актуальны для базы реестров.<br/>Дождитесь синхронизации измененных данных и повторите попытку.');
					}
				}
				else
				{
					// Постановка реестра в очередь
					return  $this->saveRegistryQueue($data);
				}
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
	 *	Какая-то операция с историей нахождения реестра в очереди
	 */
	function closeRegistryQueueHistory($data)
	{

		if (0 != $data['Registry_id'])
		{
			$params =  array(
				'Registry_id' => $data['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				Declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '';
				set nocount on
				begin try
					update {$this->scheme}.RegistryQueueHistory with (rowlock) set
						RegistryQueueHistory_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						RegistryQueueHistory_id = :Registry_id
				end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
				set nocount off
				Select :Registry_id as RegistryQueue_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$result = $this->db->query($query,$params);
			if (is_object($result))
			{
				return $result->result('array');
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
	 *	Установка признака необходимости переформирования
	 */
	function setNeedReform($data)
	{

		if ((0 != $data['Registry_id']) && (0 != $data['Evn_id']))
		{
			$query = "
				exec {$this->scheme}.p_Registry_setNeedReform :Registry_id, :Evn_id, 2
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
				'Evn_id' => $data['Evn_id']
			));
			if (is_object($result))
			{
				return $result->result('array');
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
	 *	Установка статуса реестра
	 */
	function setRegistryStatus($data)
	{
		
		if ((0 != $data['Registry_id']) && (0 != $data['RegistryStatus_id']))
		{
			//#11018 При статусах "Готов к отправке в ТФОМС" и "Отправлен в ТФОМС" запретить перемещать реестр из состояния "К оплате".
			$RegistryCheckStatus_id = $this->getFirstResultFromQuery('SELECT RegistryCheckStatus_id FROM registry (nolock) WHERE registry_id = :Registry_id', array('Registry_id'=>$data['Registry_id']));
			//"Готов к отправке в ТФОМС"
			if ($RegistryCheckStatus_id === '1') {
				throw new Exception("При статусе ''Готов к отправке в ТФОМС'' запрещено перемещать реестр из состояния ''К оплате''");
			}
			//"Отправлен в ТФОМС"
			if ($RegistryCheckStatus_id === '2') {
				throw new Exception("При статусе ''Отправлен в ТФОМС'' запрещено перемещать реестр из состояния ''К оплате''");
			}

			// Предварительно получаем тип реестра
			$RegistryType_id = 0;
			$query = "
				Select RegistryType_id from {$this->scheme}.v_Registry Registry with (NOLOCK)
				where Registry_id = :Registry_id
				";

			$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			if (is_object($r))
			{
				$res = $r->result('array');

				if ( is_array($res) && count($res) > 0 ) {
					$RegistryType_id = $res[0]['RegistryType_id'];
				}
			}
			$fields = "";
			
			if ($data['RegistryStatus_id']==3) // если перевели в работу, то снимаем признак формирования
			{
				//#11018 2. При перемещении реестра в других статусах в состояние "В работу " дополнительно сбрасывать Registry_xmlExpDT
				if ($this->isufa) {
					//для Уфы без RegistryCheckStatus_id
					$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, ";
				} else {
					$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
				}
			}

			if (($data['RegistryStatus_id']==2) && (in_array($RegistryType_id, array(1, 2, 6))) && (isset($data['session']['setting']['server']['check_registry_exists_errors']) && $data['session']['setting']['server']['check_registry_exists_errors']==1) && (!isSuperadmin())) // если переводим "к оплате" и проверка установлена, и это не суперадмин то проверяем на ошибки
			{
				if (empty($this->isufa)) { $tempscheme = $this->scheme; } else { $tempscheme = 'dbo'; }
				
				$query = "
				 Select
				(
					Select count(*) as err from {$this->scheme}.v_RegistryError RegistryError with (NOLOCK)
					left join v_RegistryData rd with (NOLOCK) on rd.Evn_id = RegistryError.Evn_id
					left join RegistryErrorType  with (NOLOCK) on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
					where RegistryError.registry_id = :Registry_id and RegistryErrorType.RegistryErrorClass_id = 1 and RegistryError.RegistryErrorClass_id = 1 and IsNull(rd.RegistryData_deleted,1)=1
				) +
				(
					Select count(*) as err from {$tempscheme}.v_RegistryErrorCom RegistryErrorCom with (NOLOCK)
					left join RegistryErrorType  with (NOLOCK) on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
					where registry_id = :Registry_id and RegistryErrorType.RegistryErrorClass_id = 1
				)
				 as err
				";

				$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
				if (is_object($r))
				{
					$res = $r->result('array');
					if ($res[0]['err']>0)
					{
						return array(array('success' => false, 'Error_Msg' => 'Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.'));
					}
				}
			}
			
			// только для Перми
			if ($data['session']['region']['nick'] == 'perm') {
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
					if (is_object($result))
					{
						return $result->result('array');
					}
					else
					{
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
					}
				} elseif ($data['RegistryStatus_id']==2) { // если переводим к оплате p_Registry_setUnPaid
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

					if (is_object($result))
					{
						return $result->result('array');
					}
					else
					{
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
					update {$this->scheme}.Registry  with (rowlock) set
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
				Select @RegistryStatus_id as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			
			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
				//'Lpu_id' => $data['Lpu_id'],
				'RegistryStatus_id' => $data['RegistryStatus_id'],
				'pmUser_id' => $data['pmUser_id']
			));
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
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
		}
	}

	/**
	 *	Установка признака активности реестра
	 */
	function setRegistryActive($data)
	{

		if (0 != $data['Registry_id'])
		{
			$data['Registry_IsActive'] = 1;
			$query = "
				Declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '',
					@Registry_IsActive bigint =  :Registry_IsActive
				set nocount on
				begin try
					update {$this->scheme}.Registry with (rowlock) set
						Registry_IsActive = @Registry_IsActive,
						Registry_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						Registry_id = :Registry_id
				end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
				set nocount off
				Select @Registry_IsActive as Registry_IsActive, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
			//'Lpu_id' => $data['Lpu_id'],
				'Registry_IsActive' => $data['Registry_IsActive'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (is_object($result))
			{
				return $result->result('array');
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
	 *	Получаем статус отправки реестра
	 */
	function getRegistryCheckStatus($data)
	{
		if ((0 != $data['Registry_id']))
		{
			$query = "
				select
					IsNull(Registry.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
					rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name
				from {$this->scheme}.Registry (nolock)
				left join RegistryCheckStatus rcs (nolock) on rcs.RegistryCheckStatus_id = Registry.RegistryCheckStatus_id 
				where
					Registry_id = :Registry_id
			";
			/*
			echo getDebugSql($query, array(
					'Registry_id' => $data['Registry_id']
			));
			exit;
			*/
			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id']
			)
			);
			if (is_object($result))
			{
				$r = $result->result('array');
				if ( is_array($r) && count($r) > 0 )
				{
					return $r;
				}
			}
			else
			{
				return array(array('RegistryCheckStatus_id' => 0, 'RegistryCheckStatus_Name' => ''));
			}
		}
		else
		{
			return array(array('RegistryCheckStatus_id' => 0, 'RegistryCheckStatus_Name' => ''));
		}
	}

	/**
	 * Добавление общей ошибки // TO-DO хранимку p_RegistryErrorCom_ins
	 */	
	function addRegistryErrorCom($data) 
	{
		$query = "
			merge into {$this->scheme}.RegistryErrorCom AS Target
			using (select top 1 :Registry_id as Registry_id, RegistryErrorType_id FROM {$this->scheme}.RegistryErrorType with (nolock) WHERE RegistryErrorType_Code = '3' and RegistryErrorClass_id = 1) as Source ON (Target.Registry_id = Source.Registry_id and Target.RegistryErrorType_id = Source.RegistryErrorType_id)
			when NOT MATCHED then
			insert 
				(Registry_id, RegistryErrorType_id, pmUser_insID, pmUser_updID, RegistryErrorCom_insDT, RegistryErrorCom_updDT)
			values
				(Source.Registry_id, Source.RegistryErrorType_id, :pmUser_id, :pmUser_id, dbo.tzGetDate(), dbo.tzGetDate());
		";
		// echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);			
	}
	
	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryExport($data)
	{
		if ((0 != $data['Registry_id']))
		{
			$query = "
				select
					case when ( Registry_expDT is null or datediff(mi, Registry_expDT, dbo.tzGetDate()) < 5 ) then RTrim(Registry_ExportPath) else NULL end as Registry_ExportPath,
					ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
					RegistryType_id
				from {$this->scheme}.Registry R (nolock)
				outer apply(
					select 
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_RegistryData RD with (nolock) where RD.Registry_id = R.Registry_id
				) RDSum
				where
					Registry_id = :Registry_id
			";

			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id']
			)
			);
			if (is_object($result))
			{
				$r = $result->result('array');
				if ( is_array($r) && count($r) > 0 )
				{
					return $r;
					/*
					 if ($r[0]['Registry_ExportPath'] == '')
					 {
						return false;
						}
						else if ($r[0]['Registry_ExportPath'] == '1')
						{
						return '1';
						}
						else
						{
						return $r[0]['Registry_ExportPath'];
						}
						*/
				}
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
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data)
	{
		$fields = '';
		$join = '';
		if ($this->scheme!='r2') {
			$fields = '
					IsNull(R.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
					IsNull(rcs.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
					rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name,
			';
			$join = 'left join RegistryCheckStatus rcs with(nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id ';
			if( $this->scheme == 'dbo' ) {
				$fields .= 'PayType.PayType_SysNick as PayType_SysNick,';
				$join .= 'left join PayType with(nolock) on PayType.PayType_id = R.PayType_id ';
			}
		}
		if ((0 != $data['Registry_id']))
		{
			// Закомментировал условие выбора пути до файла
			// @task https://redmine.swan.perm.ru/issues/60634
			/*$xmlExportPath = 'case when ( Registry_xmlExpDT is null or datediff(mi, Registry_xmlExpDT, dbo.tzGetDate()) < 5 ) then RTrim(Registry_xmlExportPath) else NULL end as Registry_xmlExportPath,';

			if (isSuperadmin()) {
				$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';
			}*/

			$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';

			if (getRegionNick() == 'ufa') {
				$fields .= ' R.RegistrySubType_id, R.Registry_IsNotInsur, ';
			}
			
			$query = "
				select
					{$xmlExportPath}
					RegistryType_id,
					OrgSmo_id,
					ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
					ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
					RDSum.RegistryData_Count as RegistryData_Count,
					{$fields}
					SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
				from {$this->scheme}.Registry R (nolock)
				outer apply(
					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_RegistryData RD with (nolock) where RD.Registry_id = R.Registry_id
				) RDSum
				{$join}
				where
					Registry_id = :Registry_id
			";

			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id']
			)
			);
			if (is_object($result))
			{
				$r = $result->result('array');
				if ( is_array($r) && count($r) > 0 )
				{
					return $r;
					/*
					 if ($r[0]['Registry_ExportPath'] == '')
					 {
						return false;
						}
						else if ($r[0]['Registry_ExportPath'] == '1')
						{
						return '1';
						}
						else
						{
						return $r[0]['Registry_ExportPath'];
						}
						*/
				}
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
	 * Установка статуса экспорта реестра
	 */
	function SetExportStatus($data) {
		if ((0 != $data['Registry_id']))
		{
			$query = "
				update {$this->scheme}.Registry with (rowlock)
				set
					Registry_ExportPath = :Status,
					Registry_expDT = dbo.tzGetDate()
				where Registry_id = :Registry_id
			";
			/*die (getDebugSQL($query, array(
			 'Registry_id' => $data['Registry_id'],
			 'Status' => $data['Status']
				)));*/
			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id'],
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
	 *	Простановка статуса реестра.
	 */
	function setRegistryCheckStatus($data) 
	{
		if (!isset($data['RegistryCheckStatus_id'])) {
			$data['RegistryCheckStatus_id'] = null;
		}
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@curdate datetime = dbo.tzGetDate();
			exec p_Registry_setRegistryCheckStatus
				@Registry_id = :Registry_id,
				@RegistryCheckStatus_id = :RegistryCheckStatus_id,
				@Registry_RegistryCheckStatusDate = @curdate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении человека в базе данных');
		}
	}
	
	
	/**
	 * Установка статуса экспорта реестра в XML
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
				inner join Lpu (nolock) on Lpu.Lpu_id = Registry.Lpu_id
				inner join Org (nolock) on Org.Org_id = Lpu.Org_id
				inner join RegistryType RT (nolock) on RT.RegistryType_id = Registry.RegistryType_id
				left join Okved (nolock) on Okved.Okved_id = Org.Okved_id
				left join [Address] LpuAddr (nolock) on LpuAddr.Address_id = Org.UAddress_id
				left join OrgRSchet ORS (nolock) on Registry.OrgRSchet_id = ORS.OrgRSchet_id
				left join v_OrgBank OB (nolock) on OB.OrgBank_id = ORS.OrgBank_id
				outer apply (
					select
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH with (NOLOCK)
						inner join v_PersonState PS with (NOLOCK) on PS.Person_id = OH.Person_id
					where
						OH.Lpu_id = Lpu.Lpu_id
						and OH.LpuUnit_id is null
						and OH.OrgHeadPost_id = 1
				) as OHDirector
				outer apply (
					select
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH with (NOLOCK)
						inner join v_PersonState PS with (NOLOCK) on PS.Person_id = OH.Person_id
					where
						OH.Lpu_id = Lpu.Lpu_id
						and OH.LpuUnit_id is null
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
	 * Получение типов документов ОМС
	 */
	function getPolisTypes($data)
	{
		$query = "
			SELECT PolisType_Code, PolisType_id, PolisType_Name		
			FROM v_PolisType (nolock)
			WHERE PolisType_CodeF008 IS NOT NULL
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
	 * Получение данных пациента
	 */
	function getPersonEdit($data)
	{
		$filter = "(1=1)";
		$params = array();
		if (isset($data['Person_id']))
		{
			$filter .= ' and Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			return false;
		}
		if (isset($data['Evn_id']))
		{
			$filter .= ' and Evn_id = :Evn_id';
			$params['Evn_id'] = $data['Evn_id'];
		}
		$query = "
			Select
				Evn_id,
				Person_id,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				RTrim(IsNull(convert(varchar,cast(Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				Polis_Num,
				Polis_Ser,
				PolisType_id,
				OrgSMO_id,
				OMSSprTerr_id
			from RegistryDataLgot RDL with (NOLOCK)
			where
			{$filter}
		";
		/*
		 echo getDebugSQL($query, $params);
		 exit;
		 */
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			if (count($res)>0)
			{
				return $res;
			}
		}
		// Если данные не найдены по событию то может быть уже есть сохраненные данные именно на человека
		if (isset($data['Evn_id']))
		{
			$query = "
				Select
					Evn_id,
					Person_id,
					Person_SurName,
					Person_FirName,
					Person_SecName,
					RTrim(IsNull(convert(varchar,cast(Person_BirthDay as datetime),104),'')) as Person_BirthDay,
					Polis_Num,
					Polis_Ser,
					PolisType_id,
					OrgSMO_id,
					OMSSprTerr_id
				from RegistryDataLgot RDL with (NOLOCK)
				where
					Person_id = :Person_id and Evn_id is null
			";
			/*
			 echo getDebugSQL($query, $params);
			 exit;
			 */
			$result = $this->db->query($query, $params);
			if (is_object($result))
			{
				$res = $result->result('array');
				if (count($res)>0)
				{
					return $res;
				}
			}
		}
		$params = array();
		$filter = "(1=1)";
		if (isset($data['Person_id']))
		{
			$filter .= ' and Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			return false;
		}
		// Если нет сохраненных данных, то берем из человека
		$query = "
			Select top 1
				Person_id,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				RTrim(IsNull(convert(varchar,cast(Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				ps.Polis_Num,
				ps.Polis_Ser,
				OrgSMO_id,
				OMSSprTerr_id,
				PolisType_id
			from v_PersonState ps with (NOLOCK)
			left join v_Polis Polis with (NOLOCK) on Polis.Polis_id = ps.Polis_id
			where
			{$filter}
		";
		/*
		 echo getDebugSQL($query, $params);
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
	 * Сохранение данных пациента
	 */
	function savePersonEdit($data)
	{
		// Сохранение нового реестра
		$params = array
		(
			'Person_id' => $data['Person_id'],
			'Evn_id' => $data['Evn_id'],
			'Server_id' => $data['Server_id'],
			'Person_SurName' => $data['Person_SurName'],
			'Person_FirName' => $data['Person_FirName'],
			'Person_SecName' => $data['Person_SecName'],
			'Person_BirthDay' => $data['Person_BirthDay'],
			'OMSSprTerr_id' => $data['OMSSprTerr_id'],
			'Polis_Ser' => $data['Polis_Ser'],
			'PolisType_id' => $data['PolisType_id'],
			'Polis_Num' => $data['Polis_Num'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Person_id bigint = :Person_id,
				@curdate datetime = dbo.tzGetDate();
			exec p_RegistryDataLgot_set
				@Person_id = @Person_id output,
				@Evn_id = :Evn_id,
				@Server_id = :Server_id,
				@Person_SurName = :Person_SurName,
				@Person_FirName = :Person_FirName,
				@Person_SecName = :Person_SecName,
				@Person_BirthDay = :Person_BirthDay,
				@OMSSprTerr_id = :OMSSprTerr_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@PolisType_id = :PolisType_id,
				@OrgSMO_id = :OrgSMO_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Person_id as Person_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении человека в базе данных');
		}
	}

	/**
	 * Удаление каких-то перс. данных
	 */
	function deletePersonEdit($data, $object, $person_id, $evn_id, $scheme = "dbo")
	{
		$params = Array();
		if ($person_id <= 0)
		{
			return false;
		}
		$params['person_id'] = $person_id;
		$params['evn_id'] = $evn_id;
		if (strpos(strtoupper($object), "EVN")!==false)
		{
			$fields = ":pmUser_id, ";
			$params['pmUser_id'] = $data['session']['pmuser_id'];
		}
		else
		{
			$fields = "";
		}

		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$scheme}.p_RegistryDataLgot_del
				:person_id, :evn_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $params);
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
	 * Проверка
	 */
	function checkDeleteRegistry($data)
	{
		if ($data['id']>0)
		{
			$sql = "
				SELECT RegistryStatus_id
				FROM {$this->scheme}.v_Registry (nolock)
				WHERE
					Registry_id = :Registry_id
			";
			/*
			 echo getDebugSql($sql, array('Registry_id' => $data['id']));
			 exit;
			 */
			$res = $this->db->query($sql, array('Registry_id' => $data['id']));
			if (is_object($res))
			{
				$resa = $res->result('array');
				if (count($resa)>0)
				{
					return ($resa[0]['RegistryStatus_id']!=4);
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 * Проверка
	 */
	function checkActualRecordRegistry($data)
	{
		if ($data['Registry_id']>0)
		{
			$sql = "
				Select COUNT(*) as rec from {$this->scheme}.v_RegistryData with (nolock)
				where needReform = 2
				and RegistryData_updDT > dbo.MirrorUpdTime()
				and Registry_id = :Registry_id
			";
			$res = $this->db->query($sql, array('Registry_id' => $data['Registry_id']));
			if (is_object($res))
			{
				$resa = $res->result('array');
				if (count($resa)>0)
				{
					return ($resa[0]['rec']==0);
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 *	Получение данных Случаи без оплаты (RegistryNoPay) для стационарных реестров
	 */
	function loadRegistryNoPay($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array('Registry_id' => $data['Registry_id']);
		$query = "
		Select
			RNP.Registry_id,
			RNP.Evn_id,
			RNP.Person_id,
			RNP.Evn_rid,
			RNP.EvnClass_id,
			RNP.Server_id,
			RNP.PersonEvn_id,
			rtrim(RNP.Person_SurName) + ' ' + rtrim(RNP.Person_FirName) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
			RTrim(IsNull(convert(varchar,cast(RNP.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name,
			RNP.RegistryNoPay_Tariff,
			RNP.RegistryNoPay_KdFact,
			RNP.RegistryNoPay_KdPlan,
			RNP.RegistryNoPay_KdPay,
			RNP.RegistryNoPay_UKLSum
		from {$this->scheme}.v_RegistryNoPay RNP with (NOLOCK)
		left join v_LpuSection LpuSection (nolock) on LpuSection.LpuSection_id = RNP.LpuSection_id
		where
			RNP.Registry_id=:Registry_id
		order by RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName, LpuSection.LpuSection_Name";
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
	 *	Получение данных Дубли посещений (RegistryDouble) для поликлин. реестров
	 */
	function loadRegistryDouble($data)
	{
	
		$join = "";
		$fields = "";
		$filter = "";
		
		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
			
		if ($this->isufa)
		{
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			
			$join .= "
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields = "
				, LB.LpuBuilding_Name
			";
		}
		
		$query = "
			Select
				-- select
				RD.Registry_id
				,RD.Evn_id
				,EPL.EvnPL_id as Evn_rid
				,RD.Person_id
				--,RD.Server_id
				--,RD.PersonEvn_id
				,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
				,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
				,EPL.EvnPL_NumCard
				,LS.LpuSection_FullName
				,MP.Person_Fio as MedPersonal_Fio
				,convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
				{$fields}
				-- end select
			from
				-- from
				{$this->scheme}.RegistryDouble RD with (NOLOCK)
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = RD.Evn_id
					--and EVPL.Person_id = RD.Person_id
				left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				outer apply(
					select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = EVPL.MedPersonal_id
				) as MP
				{$join}
				-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				{$filter}
				-- end where
			order by
				-- order by
				RD.Person_SurName, RD.Person_FirName, RD.Person_SecName
				-- end order by
		";

		if (!empty($data['withoutPaging'])) {
			$res = $this->db->query($query, $data);
			if (is_object($res))
			{
				return $res->result('array');
			}
			else
			{
				return false;
			}
		} else {
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
	}

	/**
	 * Получение данных о персонах из RegistryPerson.
	 * Эти данные получаем с реестровой базы.
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function getCountRegistryPerson($data) {
		$query = "
			select
				count(*) as rec
			from RegistryPerson with (nolock)
			where
				Person2_id = :Person_id and Person_id = :Person_did
		";
		$result = $this->db->query($query, array('Person_did' => $data['Person_did'], 'Person_id' => $data['Person_id']))->result_array();
		if (is_array($result) && count($result) == 1)
		return $result[0]['rec'];
		else
		return 0;
	}

	/**
	 * Восстановление реестра
	 */
	function registryRevive($data, $id, $scheme = "dbo")
	{
		$params = Array();
		if ($id <= 0)
		{
			return false;
		}
		$params['id'] = $id;
		$params['pmUser_id'] = $data['session']['pmuser_id'];

		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$scheme}.p_Registry_revive
				@Registry_id = :id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Message;
		";
		$res = $this->db->query($query, $params);
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
	 * Помечаем запись реестра на удаление 
	 */
	function deleteRegistryData($data)
	{
		foreach ($data['EvnIds'] as $EvnId) {
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
	 * Удаление помеченных на удаление записей и пересчет реестра 
	 */
	function refreshRegistry($data)
	{
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryData_Refresh
				@Registry_id = :Registry_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		//echo getDebugSql($query, $data);exit;
		$res = $this->db->query($query, $data);
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
	 * Проверка счета на использование в реестрах
	 */
	function ckeckOrgRSchetOnUsedInRegistry($data)
	{
		$query = "
			select top 1
				Registry_id
			from
				v_Registry with(nolock)
			where
				Lpu_id = :Lpu_id
				and OrgRSchet_id = :OrgRSchet_id
		";
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Удаление невозможно, поскольку данный счет используется в реестрах!')
			);
		} else {
			return true;
		}
	}

	/**
	 * Проверка вхождения случая в реестр
	 */
	function checkEvnInRegistry($data)
	{
		$filter = "1=1";
		$stac = false;
		if(isset($data['EvnPL_id'])) {
			$filter .= " and Evn_rid = :EvnPL_id";
		}
		if(isset($data['EvnPS_id'])) {
			$filter .= " and Evn_rid = :EvnPS_id";
			$stac = true;
		}
		if(isset($data['EvnPLStom_id'])) {
			$filter .= " and Evn_rid = :EvnPLStom_id";
		}
		if(isset($data['EvnVizitPL_id'])) {
			$filter .= " and Evn_id = :EvnVizitPL_id";
		}
		if(isset($data['EvnSection_id'])) {
			$filter .= " and Evn_id = :EvnSection_id";
			$stac = true;
		}
		if(isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and Evn_id = :EvnVizitPLStom_id";
		}	
		
		if($stac) {
			$source = "RegistryDataEvnPS";
		} else {
			$source = "RegistryData";
		}
		
		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$source} RD with (nolock)
				left join v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				left join v_EvnVizit EV with (nolock) on rd.Evn_id = EV.EvnVizit_id
				left join v_EvnSection ES with (nolock) on rd.Evn_id = ES.EvnSection_id
			where
				{$filter}
				and isnull(RD.RegistryData_deleted, 1) = 1
				and (EV.EvnVizit_IsInReg = 2 or ES.EvnSection_IsInReg = 2 or RCS.RegistryCheckStatus_Code <> 3) -- если принят частично проверяем признак IsInReg. (refs #12418)
				and R.Lpu_id = :Lpu_id
		";
		//echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>Удаление записи невозможно.')
			);
		} else {
			return true;
		}
	}
	
	
	/**
	 * Проверка вхождения отделения в реестры
	 */
	function checkLpuSectionInRegistry($data)
	{
		$filter = "1=1";

		if(isset($data['LpuUnit_id'])) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
		}
		if(isset($data['LpuSection_id'])) {
			$filter .= " and RD.LpuSection_id = :LpuSection_id";
		}
		
		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				v_RegistryData RD with(nolock)
				left join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS with(nolock) on RD.LpuSection_id = LS.LpuSection_id
			where
				{$filter}
				and R.RegistryStatus_id = 4
				and isnull(RD.RegistryData_deleted, 1) = 1
		";
		// echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			if(isset($data['LpuSection_id'])) {
				return "Изменение профиля отделения невозможно, для отделения существуют оплаченные реестры.";
			} else {
				return "Изменение типа группы отделений невозможно, для некоторых отделений существуют оплаченные реестры.";
			}
		} else {
			return "";
		}
	}
	
	/**
	 * Удаление дубля реестра
	 */
	function deleteRegistryDouble($data)
	{	
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryDouble_del
				@Registry_id = :Registry_id,
				@Evn_id = :Evn_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Удаление дублей реестра
	 */
	function deleteRegistryDoubleAll($data)
	{
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryDouble_del_all
				@Registry_id = :Registry_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Установка признака необходимости переформирования реестра
	 */
	function setRegistryIsNeedReform($data) {
		$query = "
			declare
				 @Error_Code bigint
				,@Error_Message varchar(4000);

			exec " . $this->scheme . ".p_Registry_setIsNeedReform 
				@Registry_id = :Registry_id,
				@Registry_IsNeedReform = :Registry_IsNeedReform,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Отметка на удаление записей в RegistryData, относящихся к $Evn_id, для списка реестров $registryList
	 *	Метод используется при переносе случая на другого человека (Evn->setAnotherPersonForDocument)
	 *	$registryList: ключи - Registry_id, значения - RegistryType_SysNick
	 */
	function setRegistryDataDeleted($registryList, $Evn_id = null) {
		if ( !is_array($registryList) || count($registryList) == 0 ) {
			return false;
		}

		foreach ( $registryList as $id => $type ) {
			switch ( $type ) {
				case 'omsstac':
				case 'omspol':
					$query = "
						declare
							@Error_Code bigint = null,
							@Error_Message varchar(4000) = '';

						set nocount on

						begin try
							update {$this->scheme}.RegistryData" . ($type == 'omsstac' ? "EvnPS" : "") . "  with (rowlock)
							set RegistryData_deleted = 2
							where Registry_id = :Registry_id
								and Evn_rid = :Evn_rid
						end try

						begin catch
							set @Error_Code = error_number()
							set @Error_Message = error_message()
						end catch

						set nocount off

						select @Error_Code as Error_Code, @Error_Message as Error_Msg
					";

					$queryParams = array('Registry_id' => $id, 'Evn_rid' => $Evn_id);
				break;

				case 'smp':
					$query = "
						declare
							@Error_Code bigint = null,
							@Error_Message varchar(4000) = '';

						set nocount on

						begin try
							update {$this->scheme}.RegistryDataCmp with (rowlock)
							set RegistryDataCmp_deleted = 2
							where Registry_id = :Registry_id
								and CmpCallCard_id = :CmpCallCard_id
						end try

						begin catch
							set @Error_Code = error_number()
							set @Error_Message = error_message()
						end catch

						set nocount off

						select @Error_Code as Error_Code, @Error_Message as Error_Msg
					";

					$queryParams = array('Registry_id' => $id, 'CmpCallCard_id' => $Evn_id);
				break;

				default:
					return array(array('Error_Msg' => 'Недопустимый тип реестра'));
				break;
			}

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}

			$res = $result->result('array');

			if ( !is_array($res) || count($res) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при отметке записей реестра на удаление'));
			}
			else if ( !empty($res[0]['Error_Msg']) ) {
				return $res;
			}
		}

		return array(array('Error_Msg' => ''));
	}


	/**
	 * Загрузка истории каких-то статусов
	 */
	function loadRegistryCheckStatusHistory($data) 
	{
		$query = "
			Select
				-- select
				RCSH.RegistryCheckStatusHistory_id,
				RCS.RegistryCheckStatus_Name,
				RTrim(IsNull(convert(varchar,cast(RCSH.Registry_CheckStatusDate as datetime),104) + ' ' + convert(varchar(5), RCSH.Registry_CheckStatusDate, 114), '')) as Registry_CheckStatusDate,
				RTrim(IsNull(convert(varchar,cast(RCSH.Registry_CheckStatusTFOMSDate as datetime),104) + ' ' + convert(varchar(5), RCSH.Registry_CheckStatusTFOMSDate, 114), '')) as Registry_CheckStatusTFOMSDate
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryCheckStatusHistory RCSH with (NOLOCK)
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = RCSH.RegistryCheckStatus_id
				-- end from
			where
				-- where
				RCSH.Registry_id = :Registry_id
				-- end where
			order by 
				-- order by
				ISNULL(RCSH.Registry_CheckStatusDate,RCSH.Registry_CheckStatusTFOMSDate)
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
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		if ( !empty($this->RegistryType_id) && $force == false ) {
			return false;
		}

		if ( !empty($data['RegistryType_id']) && is_numeric($data['RegistryType_id']) ) {
			$this->RegistryType_id = $data['RegistryType_id'];
		}
		else if ( !empty($data['Registry_id']) ) {
			$this->RegistryType_id = $this->__getRegistryTypeFromDB($data['Registry_id']);
		}

		$this->RegistryDataEvnField = 'Evn_id';
		$this->RegistryDataObject = 'RegistryData';
		$this->RegistryDataObjectTable = 'RegistryData';
		$this->RegistryErrorObject = 'RegistryError';
		$this->RegistryErrorComObject = 'RegistryErrorCom';
		$this->RegistryPersonObject = 'RegistryPerson';
		$this->RegistryDoubleObject = 'RegistryDouble';
		$this->RegistryNoPolis = 'RegistryNoPolis';
	}

	/**
	 * Получение типа реестра из БД
	 */
	function __getRegistryTypeFromDB($Registry_id = null) {
		if ( empty($Registry_id) ) {
			return null;
		}

		$query = "
			select top 1 RegistryType_id, 1 as sort_field
			from {$this->scheme}.v_Registry with (nolock)
			where Registry_id = :Registry_id
			union all
			select top 1 RegistryType_id, 2 as sort_field
			from {$this->scheme}.v_RegistryQueue with (nolock)
			where RegistryQueue_id = :Registry_id
		";

		if ( $this->__checkRegistryDeletedViewExists() === true ) {
			$query .= "
				union all
				select top 1 RegistryType_id, 1 as sort_field
				from {$this->scheme}.v_Registry_deleted with (nolock)
				where Registry_id = :Registry_id
			";
		}

		$query .= "
			order by sort_field
		";

		$res = $this->db->query($query, array('Registry_id' => $Registry_id));

		if ( !is_object($res) ) {
			return null;
		}

		$resp = $res->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			return null;
		}
		else {
			return $resp[0]['RegistryType_id'];
		}
	}

	/**
	 * Получение информации о наличии в $this->scheme объекта v_Registry_deleted
	 * @task https://redmine.swan.perm.ru/issues/42392
	 * @return boolean
	 */
	private function __checkRegistryDeletedViewExists() {
		$query = "
			select top 1 o.[object_id]
			from [sys].[objects] o with (nolock)
				inner join [sys].[schemas] s with (nolock) on s.[schema_id] = o.[schema_id]
			where o.[type] = 'V'
				and s.[name] = '{$this->scheme}'
				and o.[name] = 'v_Registry_deleted'
		";
		$res = $this->db->query($query);

		if ( !is_object($res) ) {
			return false;
		}

		$resp = $res->result('array');

		return (is_array($resp) && count($resp) > 0 && !empty($resp[0]['object_id']));
	}

}