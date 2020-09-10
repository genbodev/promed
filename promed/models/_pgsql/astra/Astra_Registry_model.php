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
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      10.06.2013
*/
require_once(APPPATH.'models/_pgsql/Registry_model.php');

class Astra_Registry_model extends Registry_model {
	var $scheme = "r30";
	var $region = "astra";
	var $MaxEvnField = 'Evn_rid';

	private $_IDCASE = 0;
	private $_IDSERV = 0;

	private $_RegistryEvnNumByNZAP = array();

	/**
	 *	Конструктор
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
			DispClass_id := :DispClass_id,
		";
	}

	/**
	 * Получение дополнительных полей
	 */
	function getReformErrRegistryAdditionalFields() {
		return ",DispClass_id as \"DispClass_id\"";
	}

	/**
	 *	Комментарий
	 */
	function setRegistryDataIsPaid($data)
	{
		$query = "
			update {$this->scheme}.RegistryData
			set RegistryData_isPaid = :RegistryData_isPaid
			where Registry_id = :Registry_id
		";

		$result = $this->db->query($query, $data);
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1:
				$this->MaxEvnField = 'MaxEvn_id';
				break;
			case 6:
				$this->RegistryNoPolis = 'RegistryCmpNoPolis';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryDoubleObject = 'RegistryCmpDouble';
				$this->MaxEvnField = 'Evn_id';
				break;
			case 9:
			case 12:
				$this->MaxEvnField = 'Evn_id';
			break;
		}
	}

	/**
	 *	Комментарий
	 */
	function setRegistryDataNoPolis($data)
	{
		$query = "
			Insert {$this->scheme}.RegistryNoPolis (
				Registry_id, Evn_id, Person_id, Evn_Code, Person_SurName, Person_FirName, Person_SecName, Person_BirthDay, pmUser_insID, pmUser_updID, RegistryNoPolis_insDT, RegistryNoPolis_updDT
			)
			Select 
				rd.Registry_id, rd.Evn_id, rd.Person_id, '', rd.Person_SurName, rd.Person_FirName, rd.Person_SecName, rd.Person_BirthDay, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryNoPolis_insDT, dbo.tzGetDate() as RegistryNoPolis_updDT 
			from {$this->scheme}.v_RegistryData rd
			where rd.Registry_id = :Registry_id  and rd.Evn_id = :Evn_id;
			
			update {$this->scheme}.RegistryData
			set RegistryData_deleted = 2
			where Registry_id = :Registry_id
				and Evn_id = :Evn_id;
		";

		$result = $this->db->query($query, $data);
	}

	/**
	 *	Комментарий
	 */
	function deleteRegistryErrorTFOMS($data)
	{
		$filter = "";
		$params = array();
		$join = "";
		if ($data['Registry_id']>0)
		{
			$filter ="Registry_id = :Registry_id ";
			$params['Registry_id'] = $data['Registry_id'];
		}
		else
			return false;
		$query = "
			delete from {$this->scheme}.RegistryErrorTFOMS
			where {$filter};
		";
		$result = $this->db->query($query, $params);
		return true;
	}

	/**
	 *	Чтение списка реестров
	 */
	public function loadRegistry($data) {
		$resp = parent::loadRegistry($data);

		if ( !empty($data['Registry_id']) && is_array($resp) && count($resp) == 1 && empty($resp[0]['Error_Msg']) ) {
			$resp[0]['OMSSprTerr_id'] = '';

			$resp_tmp = $this->queryResult("
				select
					RegistryOMSSprTerrLink_id as \"RegistryOMSSprTerrLink_id\",
					OMSSprTerr_id as \"OMSSprTerr_id\"
				from
					{$this->scheme}.v_RegistryOMSSprTerrLink
				where
					Registry_id = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id']
			));

			foreach ( $resp_tmp as $row ) {
				if ( !empty($resp[0]['OMSSprTerr_id']) ) {
					$resp[0]['OMSSprTerr_id'] .= ",";
				}

				$resp[0]['OMSSprTerr_id'] .= $row['OMSSprTerr_id'];
			}
		}

		return $resp;
	}

	/**
	 *	Комментарий
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
		$this->setRegistryParamsByType($data);
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		);
		$filter="(1=1)";
		$join = "";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and RD.Person_SurName ilike :Person_SurName ";
			$params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and RD.Person_FirName ilike :Person_FirName ";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and RD.Person_SecName ilike :Person_SecName ";
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

		if(!empty($data['Evn_id'])) {
			$filter .= " and RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( !empty($data['filterRecords']) ) {
			if ($data['filterRecords'] == 2) {
				$filter .= " and coalesce(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and coalesce(RD.RegistryData_IsPaid,1) = 1";
			}
		}

		// Полка
		//if (($data['RegistryType_id'] == 1) || ($data['RegistryType_id'] == 2) || ($data['RegistryType_id'] == 4) || ($data['RegistryType_id'] == 5) || ($data['RegistryType_id'] == 6))
		if ( in_array($data['RegistryType_id'], array(1,2,4,5,6,7,8,9,10,11,12,14,15)) )
		{
			$fields = '';

			if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
                $source_table = 'v_RegistryDeleted_Data';
            } else {
                $source_table = 'v_RegistryData';
            }

			//УЕТ для поликлиники
			if ($data['RegistryType_id'] == 2) {
				$select_uet = "
					case
						when RD.EvnClass_id = 32 then RD.RegistryData_KdFact
						when RD.PayMedType_id = 9 then rd.RegistryData_KdPay
						else Evnpl.EvnPL_VizitCount
					end as \"RegistryData_Uet\",
				";
				$join .= "left join v_EvnPL EvnPL on EvnPL.EvnPL_id = RD.Evn_rid";
			} else {
				$select_uet = "RD.RegistryData_KdFact as \"RegistryData_Uet\", ";
			}

			if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
				$join .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
				$fields .= "epd.DispClass_id as \"DispClass_id\", ";
			}

			if ($data['RegistryType_id'] == 15) {
				$join .= " left join v_EvnUslugaPar EUP on EUP.EvnUslugaPar_id = RD.Evn_id ";
				$join .= " left join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = EUP.EvnDirection_id ";
				$join .= " left join v_EvnLabRequest  elr on elr.EvnDirection_id = EUP.EvnDirection_id ";
				$fields .= "case when efr.EvnFuncRequest_id is not null then 'true' else 'false' end as \"isEvnFuncRequest\", ";
				$fields .= "case when elr.EvnLabRequest_id is not null then 'true' else 'false' end as \"isEvnLabRequest\", ";
				$fields .= "elr.MedService_id as \"MedService_id\", ";
				$fields .= "EUP.EvnDirection_id as \"EvnDirection_id\", ";
			}

			if ( $data['RegistryType_id'] == 14 ) {
				$join .= " left join v_EvnPS eps on eps.EvnPS_id = RD.Evn_rid ";
				$fields .= "eps.EvnPS_HTMTicketNum as \"EvnPS_HTMTicketNum\", ";
				$fields .= "RTrim(coalesce(to_char(cast(eps.EvnPS_HTMBegDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnPS_HTMBegDate\", ";
				$fields .= "RTrim(coalesce(to_char(cast(eps.EvnPS_HTMHospDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnPS_HTMHospDate\", ";
			}

			if ( $data['RegistryType_id'] == 1 ) {
				$join .= " left join v_MesOld MO on RD.Mes_id = MO.Mes_id ";
				$join .= " left join lateral(
					select
						MOUC.UslugaComplex_id
					from v_MesOldUslugaComplex MOUC
						left join v_EvnUsluga EU on  MOUC.UslugaComplex_id = EU.UslugaComplex_id
					where
						MOUC.Mes_id = MO.Mes_id and EU.EvnUsluga_pid = RD.Evn_id
					limit 1
				) as MOUC on true";
				$join .= " left join v_UslugaComplex UC on MOUC.UslugaComplex_id = UC.UslugaComplex_id ";
				$fields .= "MO.Mes_Code || ' ' || MO.MesOld_Num as \"Mes_Code\", ";
				$fields .= "UC.UslugaComplex_Code as \"UslugaComplex_Code\", ";
			}


            $query = "
				Select
					-- select
					RD.Evn_id as \"Evn_id\",
					CCC.CmpCallCard_id as \"CmpCallCard_id\",
					RD.Evn_rid as \"Evn_rid\",
					RD.{$this->MaxEvnField} as \"MaxEvn_id\",
					RD.EvnClass_id as \"EvnClass_id\",
					RD.Registry_id as \"Registry_id\",
					RD.RegistryType_id as \"RegistryType_id\",
					RD.Person_id as \"Person_id\",
					PersonEvn.Server_id as \"Server_id\",
					PersonEvn.PersonEvn_id as \"PersonEvn_id\",
					case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\",
					RD.needReform as \"needReform\",
					RD.checkReform as \"checkReform\",
					RD.timeReform as \"timeReform\",
					case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end as \"isNoEdit\",
					RD.RegistryData_deleted as \"RegistryData_deleted\",
					RTrim(RD.NumCard) as \"EvnPL_NumCard\",
					RTrim(RD.Person_FIO) as \"Person_FIO\",
					RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
					RD.LpuSection_id as \"LpuSection_id\",
					RTrim(RD.LpuSection_name) as \"LpuSection_name\",
					RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
					RTrim(coalesce(to_char(cast(RD.Evn_setDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnVizitPL_setDate\",
					RTrim(coalesce(to_char(cast(RD.Evn_disDate as timestamp), 'dd.mm.yyyy'),'')) as \"Evn_disDate\",
					--RD.RegistryData_KdFact as RegistryData_Uet,
					{$select_uet}
					{$fields}
					RD.RegistryData_KdPay as \"RegistryData_KdPay\",
					RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
					coalesce(RD.RegistryData_ItogSum, 0) + coalesce(RD.RegistryData_ItogSum2, 0) as \"RegistryData_TotalSum\",
					RD.RegistryData_Tariff as \"RegistryData_Tariff\",
					RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
					RD.RegistryData_Tariff2 as \"RegistryData_Tariff2\",
					RD.RegistryData_ItogSum2 as \"RegistryData_ItogSum2\",
					RD.RegistryData_IsPaid as \"RegistryData_IsPaid\",
					RegistryError.Err_Count as \"Err_Count\",
					PMT.PayMedType_Code as \"PayMedType_Code\",
					Diag.Diag_Code as \"Diag_Code\",
					RHDCR.RegistryHealDepResType_id as \"RegistryHealDepResType_id\"
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD
					left join v_PayMedType PMT on PMT.PayMedType_id = RD.PayMedType_id
					left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
					left join CmpCloseCard CCC on CCC.CmpCloseCard_id = RD.Evn_id
					left join v_RegistryHealDepCheckRes RHDCR on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
					left join v_Diag Diag on Diag.Diag_id = RD.Diag_id
					left join lateral (
						select RDLT.Person_id from RegistryDataLgot RDLT where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null) limit 1
					) RDL on true
					left join lateral
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryError RE where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
					) RegistryError on true
					left join lateral
					(
						Select
							PersonEvn_id,
							Server_id
						from v_PersonEvn PE
						where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
						limit 1
					) PersonEvn on true
					{$join}
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
	 * Отметки об оплате случаев
	 */
	function loadRegistryDataPaid($data)
	{
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);
		$join = "";

		$fields = '';

		//УЕТ для поликлиники
		if ($data['RegistryType_id'] == 2) {
			$select_uet = "
				case
					when RD.EvnClass_id = 32 then RD.RegistryData_KdFact
					when RD.PayMedType_id = 9 then rd.RegistryData_KdPay
					else EvnPL.EvnPL_VizitCount end
				as \"RegistryData_Uet\",
			";
			$join .= "left join v_EvnPL EvnPL on EvnPL.EvnPL_id = RD.Evn_rid ";
		} else {
			$select_uet = "RD.RegistryData_KdFact as \"RegistryData_Uet\", ";
		}

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id as \"DispClass_id\", ";
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
				PersonEvn.Server_id as \"Server_id\",
				PersonEvn.PersonEvn_id as \"PersonEvn_id\",
				case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\",
				RD.needReform as \"needReform\",
				RD.checkReform as \"checkReform\",
				RD.timeReform as \"timeReform\",
				case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end as \"isNoEdit\",
				RD.RegistryData_deleted as \"RegistryData_deleted\",
				RTrim(RD.NumCard) as \"EvnPL_NumCard\",
				RD.Person_FirName as \"Person_FirName\",
				RD.Person_SurName as \"Person_SurName\",
				RD.Person_SecName as \"Person_SecName\",
				RD.Polis_Num as \"Polis_Num\",
				RTrim(RD.Person_FIO) as \"Person_FIO\",
				RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
				RD.LpuSection_id as \"LpuSection_id\",
				RTrim(RD.LpuSection_name) as \"LpuSection_name\",
				RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
				RTrim(coalesce(to_char(cast(RD.Evn_setDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnVizitPL_setDate\",
				RTrim(coalesce(to_char(cast(RD.Evn_disDate as timestamp), 'dd.mm.yyyy'),'')) as \"Evn_disDate\",
				--RD.RegistryData_KdFact as RegistryData_Uet,
				{$select_uet}
				{$fields}
				RD.RegistryData_KdPay as \"RegistryData_KdPay\",
				RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
				coalesce(RD.RegistryData_ItogSum, 0) + coalesce(RD.RegistryData_ItogSum2, 0) as \"RegistryData_TotalSum\",
				RD.RegistryData_Tariff as \"RegistryData_Tariff\",
				RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
				RD.RegistryData_Tariff2 as \"RegistryData_Tariff2\",
				RD.RegistryData_ItogSum2 as \"RegistryData_ItogSum2\",
				RD.RegistryData_IsPaid as \"RegistryData_IsPaid\",
				RegistryError.Err_Count as \"Err_Count\",
				PMT.PayMedType_Code as \"PayMedType_Code\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryData RD
				left join v_PayMedType PMT on PMT.PayMedType_id = RD.PayMedType_id
				left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
				left join lateral (
					select RDLT.Person_id from RegistryDataLgot RDLT where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null) limit 1
				) RDL on true
				left join lateral
				(
					Select count(*) as Err_Count
					from {$this->scheme}.v_RegistryError RE where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
				) RegistryError on true
				left join lateral
				(
					Select
						PersonEvn_id, Server_id
					from v_PersonEvn PE
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
					limit 1
				) PersonEvn on true
				{$join}
			-- end from
			where
				-- where
				RD.Registry_id=:Registry_id
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
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
	 *	Комментарий
	 */
	function setErrorFromImportRegistry($data)
	{
		if ($data['Registry_id']>0)
		{
			$query = "
				SELECT
					RegistryErrorType_id as \"RegistryErrorType_id\"
				FROM {$this->scheme}.RegistryErrorType
				WHERE RegistryErrorType_Code = :OSHIB 
					AND RegistryErrorType_begDT <= dbo.tzGetDate()
					AND coalesce(RegistryErrorType_endDT, dbo.tzGetDate()) >= @date
				limit 1
			";
			$resp = $this->db->query($query, $data);
			if (is_object($resp))
			{
				$ret = $resp->result('array');
				if (is_array($ret) && (count($ret) > 0)) {

					$data['OSHIB_ID'] = $ret[0]['RegistryErrorType_id'];
					$query = "
						Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_BaseElement, RegistryErrorTFOMS_Comment, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT)
						Select 
						rd.Registry_id, rd.Evn_id, :OSHIB_ID as RegistryErrorType_id, :OSHIB, :IM_POL, :BAS_EL, :COMMENT, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryError_insDT, dbo.tzGetDate() as RegistryError_updDT 
						from {$this->scheme}.RegistryData rd
						where rd.Registry_id = :Registry_id  and rd.RegistryData_RowNum = :N_ZAP";

						//echo getDebugSql($query, $params);
						//exit;

					$result = $this->db->query($query, $data);
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
					return array(array('success' => false, 'Error_Msg' => 'Код ошибки '.$data['OSHIB']. ' не найден в бд'));
				}
			} else {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки '.$data['OSHIB']));
			}
		}
		else
		{
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
		}
	}

	/**
	 * Идентификация СМО по SMO, SMO_OGRN, SMO_OK
	 */
	function identifyOrgSMO($data)
	{
		$query = "
			select
				smo.OrgSMO_id as \"OrgSMO_id\" 
			from
				v_OrgSMO smo
				left join v_Org o on o.Org_id = smo.Org_id
			where
				smo.Orgsmo_f002smocod = :SMO
				OR (o.Org_OGRN = :SMO_OGRN and o.Org_OKATO = :SMO_OK)	
			limit 1			
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]['OrgSMO_id'];
			}
		}
		return false;
	}

	/**
	 *	Получение кода территории страхования
	 */
	function getOmsSprTerr($data)
	{
		$query = "
			select
				OmsSprTerr_id as \"OmsSprTerr_id\"
			from
				v_OmsSprTerr
			where
				OmsSprTerr_Code = :OmsSprTerr_Code
			limit 1
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]['OmsSprTerr_id'];
			}
		}
		return null;
	}

	/**
	 *	Комментарий
	 */
	function addNewPolisToPerson($data)
	{
		$query = "
			select
				PersonEvn_id as \"PersonEvn_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonPolis_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				OmsSprTerr_id := :OmsSprTerr_id,
				PolisType_id := :PolisType_id,
				OrgSMO_id := :OrgSMO_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Polis_begDate := :Polis_begDate,
				Polis_endDate := NULL,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$params = array(
					'Evn_id' => $data['Evn_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $resp[0]['PersonEvn_id']
				);

				// перевязываем случай на новую периодику
				$query = "
					update
						Evn
					set
						PersonEvn_id = :PersonEvn_id,
						Server_id = :Server_id
					where
						Evn_id = :Evn_id
				";

				$this->db->query($query, $params);

				return true;
			}
		}

		return false;
	}

	/**
	 * Идентификация полиса по VPOLIS
	 */
	function identifyPolisType($data)
	{
		$query = "
			select
				pt.PolisType_id as \"PolisType_id\" 
			from
				v_PolisType pt
			where
				pt.PolisType_CodeF008 = :VPOLIS
			limit 1
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]['PolisType_id'];
			}
		}
		return false;
	}

	/**
	 *	saveRegistryQueue
	 *	Установка реестра в очередь на формирование
	 *	Возвращает номер в очереди
	 */
	public function saveRegistryQueue($data) {
		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) ) {
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}

		try {
			$this->beginTransaction();

			// Сохранение нового реестра
			if ( empty($data['Registry_id']) ) {
				$data['Registry_IsActive'] = 2;
				$operation = 'insert';
			}
			else {
				$operation = 'update';
			}

			$re = $this->loadRegistryQueue($data);

			if ( is_array($re) && (count($re) > 0) ) {
				if ( $operation == 'update' ) {
					if ( $re[0]['RegistryQueue_Position'] > 0) {
						throw new Exception('<b>Запрос МО по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра<br/>дождитесь окончания текущего формирования реестра.');
					}
				}
			}

			if ( !empty($data['Registry_id']) ) {
				$resp = $this->checkBeforeSaveRegistryQueue($data);

				if ( is_array($resp) ) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}

			$params = array(
				'Registry_id' => $data['Registry_id'],
				'Lpu_id' => $data['Lpu_id'],
				'RegistryType_id' => $data['RegistryType_id'],
				'RegistryStatus_id' => $data['RegistryStatus_id'],
				'RegistryStacType_id' => $data['RegistryStacType_id'],
				'Registry_begDate' => $data['Registry_begDate'],
				'Registry_endDate' => $data['Registry_endDate'],
				'Registry_Num' => $data['Registry_Num'],
				'Registry_IsActive' => $data['Registry_IsActive'],
				'Registry_IsFinanc' => $data['Registry_IsFinanc'],
				'OrgRSchet_id' => $data['OrgRSchet_id'],
				'Registry_accDate' => $data['Registry_accDate'],
				'DispClass_id' => $data['DispClass_id'],
				'PayType_id' => $data['PayType_id'],
				'KatNasel_id' => $data['KatNasel_id'],
				'Registry_IsZNO' => $data['Registry_IsZNO'],
				'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
				'pmUser_id' => $data['pmUser_id'],
			);

			$fields = "";

			switch ( $data['RegistryType_id'] ){
				case 1:
				case 14:
					$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
					$fields .= "@LpuBuilding_id = :LpuBuilding_id,";
					break;

				case 2:
					$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
					$fields .= "LpuBuilding_id := :LpuBuilding_id,";
					// Переформирование по записям, пока только на полке
					if ( isset($data['reform']) ) {
						$params['reform'] = $data['reform'];
						$fields .= "reform := :reform,";
					}
					break;
			}

			$query = "
				select
					RegistryQueue_id as \"RegistryQueue_id\",
					RegistryQueue_Position as \"RegistryQueue_Position\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_RegistryQueue_ins(
					RegistryStacType_id := :RegistryStacType_id,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					Lpu_id := :Lpu_id,
					OrgRSchet_id := :OrgRSchet_id,
					Registry_begDate := :Registry_begDate,
					Registry_endDate := :Registry_endDate,
					DispClass_id := :DispClass_id,
					PayType_id := :PayType_id,
					KatNasel_id := :KatNasel_id,
					Registry_IsZNO := :Registry_IsZNO,
					Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
					{$fields}
					Registry_Num := :Registry_Num,
					Registry_accDate := :Registry_accDate,
					RegistryStatus_id := :RegistryStatus_id,
					RegistryQueue_IsFinanc := :Registry_IsFinanc,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, $params);

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных');
			}

			$resp = $result->result('array');

			if ( !empty($resp[0]['RegistryQueue_id']) && (!array_key_exists('reformRegistry', $data) || $data['reformRegistry'] !== true) ) {
				$savedRecords = array();

				if ( !empty($data['OMSSprTerr_id']) ) {
					$OMSSprTerr_ids = explode(',', $data['OMSSprTerr_id']);
				}
				else {
					$OMSSprTerr_ids = array();
				}

				if ( !empty($data['Registry_id']) ) {
					// получаем территории страхования, которые нужно удалить
					$resp_tmp = $this->queryResult("
						select
							RegistryOMSSprTerrLink_id as \"RegistryOMSSprTerrLink_id\",
							OMSSprTerr_id as \"OMSSprTerr_id\"
						from
							{$this->scheme}.v_RegistryOMSSprTerrLink
						where
							Registry_id = :Registry_id
					", array(
						'Registry_id' => $data['Registry_id']
					));

					// удаляем ненужные
					if ( is_array($resp_tmp) && count($resp_tmp) > 0 ) {
						foreach ( $resp_tmp as $rec ) {
							if ( !in_array($rec['OMSSprTerr_id'], $OMSSprTerr_ids) ) {
								$this->db->query("
									select
										Error_Code as \"Error_Code\",
										Error_Message as \"Error_Msg\"
									from {$this->scheme}.p_RegistryOMSSprTerrLink_del(
										RegistryOMSSprTerrLink_id := :RegistryOMSSprTerrLink_id
									)
								", array(
									'RegistryOMSSprTerrLink_id' => $rec['RegistryOMSSprTerrLink_id']
								));
							}
							else {
								$savedRecords[] = $rec['OMSSprTerr_id'];
							}
						}
					}
				}

				// добавляем нужные
				if ( count($OMSSprTerr_ids) > 0 ) {
					foreach ( $OMSSprTerr_ids as $OMSSprTerr_id ) {
						if ( in_array($OMSSprTerr_id, $savedRecords) ) {
							continue;
						}

						$resp_tmp = $this->queryResult("
							select
								RegistryOMSSprTerrLink_id as \"RegistryOMSSprTerrLink_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from {$this->scheme}.p_RegistryOMSSprTerrLink_ins(
								Registry_id := :Registry_id,
								OMSSprTerr_id := :OMSSprTerr_id,
								RegistryQueue_id := :RegistryQueue_id,
								pmUser_id := :pmUser_id
							)
						", array(
							'Registry_id' => (!empty($data['Registry_id']) ? $data['Registry_id'] : null),
							'OMSSprTerr_id' => $OMSSprTerr_id,
							'RegistryQueue_id' => $resp[0]['RegistryQueue_id'],
							'pmUser_id' => $data['pmUser_id'],
						));

						if ( !empty($resp_tmp[0]['Error_Msg']) ) {
							throw new Exception($resp_tmp[0]['Error_Msg']);
						}
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$resp = array(array('success' => false, 'Error_Msg' => $e->getMessage()));
			$this->rollbackTransaction();
		}

		return $resp;
	}

	/**
	 * После успешного импорта реестра из ТФОМС
	 */
	function afterImportRegistryFromTFOMS($data) {
		$this->db->query("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_setIndexRep(
				Registry_id := :Registry_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'Registry_id' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 *	Комментарий
	 */
	function setErrorFromTFOMSImportRegistry($d, $data)
	{
		// Сохранение загружаемого реестра, точнее его ошибок

		$params = $d;
		$params['Registry_id'] = $d['Registry_id'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
		$params['S_DOP'] = $d['S_DOP'];
		$params['COMMENT'] = (!empty($d['COMMENT']) ? $d['COMMENT'] : null);
		$params['COMMENT_CALC'] = (!empty($d['COMMENT_CALC']) ? $d['COMMENT_CALC'] : null);

		$params['Evn_id'] = $d['Evn_id'];
		if ($data['Registry_id']>0)
		{
			$query = "
				SELECT
					RegistryErrorType_id as \"RegistryErrorType_id\",
					RegistryErrorType_Descr as \"RegistryErrorType_Descr\"
				FROM {$this->scheme}.RegistryErrorType
				WHERE RegistryErrorType_Code = :S_DOP
					AND RegistryErrorType_begDT <= dbo.tzGetDate()
					AND coalesce(RegistryErrorType_endDT, dbo.tzGetDate()) >= dbo.tzGetDate()
				limit 1
			";
			$resp = $this->db->query($query, $params);
			if (is_object($resp))
			{
				$ret = $resp->result('array');
				if (is_array($ret) && (count($ret) > 0)) {

					$params['S_DOP_ID'] = $ret[0]['RegistryErrorType_id'];
					if (!empty($params['Evn_id'])) {
						// ошибка на уровне случая
						if ($d['RegistryType_id'] == 6) {
							// если ошибка уже есть, повторно её не грузим!
							$resp_ret = $this->queryResult("
								select
									ret.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\"
								from
									{$this->scheme}.RegistryErrorTFOMS ret
									inner join {$this->scheme}.v_RegistryDataCmp rd on rd.Evn_id = ret.Evn_id
										and rd.Registry_id = ret.Registry_id
								where
									ret.Registry_id = :Registry_id
									and ret.RegistryErrorType_id = :S_DOP_ID
									and rd.Evn_id = :Evn_id
							", $params);
							if (!empty($resp_ret[0]['RegistryErrorTFOMS_id'])) {
								return array(array('success' => true, 'Error_Msg' => ''));
							}
							$query = "
								Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_Comment, RegistryErrorTFOMS_CommentCalc, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT, RegistryErrorTFOMSLevel_id)
								Select 
								rd.Registry_id, rd.CmpCloseCard_id, :S_DOP_ID as RegistryErrorType_id, :S_DOP as RegistryErrorType_Code, '' as RegistryErrorTFOMS_FieldName, :COMMENT as RegistryErrorTFOMS_Comment, :COMMENT_CALC as RegistryErrorTFOMS_CommentCalc, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryErrorTFOMS_insDT, dbo.tzGetDate() as RegistryErrorTFOMS_updDT, 2 as RegistryErrorTFOMSLevel_id
								from {$this->scheme}.RegistryDataCmp rd
								where rd.Registry_id = :Registry_id  and rd.CmpCloseCard_id = :Evn_id
							";
							$result = $this->db->query($query, $params);
						} else {
							// если ошибка уже есть, повторно её не грузим!
							$resp_ret = $this->queryResult("
								select
									ret.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\"
								from
									{$this->scheme}.RegistryErrorTFOMS ret
									inner join {$this->scheme}.v_RegistryData rd on rd.Evn_id = ret.Evn_id
										and rd.Registry_id = ret.Registry_id
								where
									ret.Registry_id = :Registry_id
									and ret.RegistryErrorType_id = :S_DOP_ID
									and rd.Evn_id = :Evn_id
							", $params);
							if (!empty($resp_ret[0]['RegistryErrorTFOMS_id'])) {
								return array(array('success' => true, 'Error_Msg' => ''));
							}
							$query = "
								Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, Evn_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_Comment, RegistryErrorTFOMS_CommentCalc, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT, RegistryErrorTFOMSLevel_id)
								Select 
								rd.Registry_id, rd.Evn_id, :S_DOP_ID as RegistryErrorType_id, :S_DOP as RegistryErrorType_Code, '' as RegistryErrorTFOMS_FieldName, :COMMENT as RegistryErrorTFOMS_Comment, :COMMENT_CALC as RegistryErrorTFOMS_CommentCalc, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryErrorTFOMS_insDT, dbo.tzGetDate() as RegistryErrorTFOMS_updDT, 2 as RegistryErrorTFOMSLevel_id
								from {$this->scheme}.RegistryData rd
								where rd.Registry_id = :Registry_id  and rd.Evn_id = :Evn_id
							";
							$result = $this->db->query($query, $params);
						}
					} else {
						// ошибка на уровне счёта
						$query = "
							Insert {$this->scheme}.RegistryErrorTFOMS (Registry_id, RegistryErrorType_id, RegistryErrorType_Code, RegistryErrorTFOMS_FieldName, RegistryErrorTFOMS_Comment, pmUser_insID, pmUser_updID, RegistryErrorTFOMS_insDT, RegistryErrorTFOMS_updDT, RegistryErrorTFOMSLevel_id)
							values
							(:Registry_id, :S_DOP_ID, :S_DOP, '', :COMMENT, :pmUser_id, :pmUser_id, dbo.tzGetDate(), dbo.tzGetDate(), 2)
						";
						$result = $this->db->query($query, $params);
					}
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
					return array(array('success' => false, 'Error_Msg' => 'Код ошибки '.$d['S_DOP']. ' не найден в бд'));
				}
			} else {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки '.$d['S_DOP']));
			}
		}
		else
		{
			return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!'));
		}
	}

	/**
	 * Проверка существования ошибки в реестре
	 */
	function existsErrorTypeInRegistry($data) {
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id'],
			'RegistryErrorType_Code' => $data['RegistryErrorType_Code']
		);

		$query = "
			select
				count(*) as \"Count\"
			from {$this->scheme}.v_RegistryErrorTFOMS RET
			where RET.Registry_id = :Registry_id
				and RET.RegistryErrorType_Code = :RegistryErrorType_Code
				and RET.Evn_id = :Evn_id
			limit 1
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$resp = $result->result('array');
			if ($resp[0]['Count'] > 0) {
				return true;
			} else {
				return false;
			}
		}
		return array('Error_Msg' => 'Не удалось проверить существование ошибок в реестре');
	}

	/**
	 * Проверка входит ли реестр в объединенный
	 */
	function checkRegistryImportAvailable($data) {
		$resp = $this->queryResult("
			select
				r.Registry_id as \"Registry_id\",
				to_char(r.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
				r.Registry_Num as \"Registry_Num\"
			from
				v_RegistryGroupLink rgl
				inner join v_Registry r on r.Registry_id = rgl.Registry_pid
			where
				rgl.Registry_id = :Registry_id
			limit 1
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Registry_id'])) {
			return array('Error_Msg' => 'Импорт реестра из ТФОМС не возможен. Данный реестр включен в объединенный реестр счетов '.$resp[0]['Registry_Num'].' '.$resp[0]['Registry_accDate'].'. Произведите импорт ответа на объединенный реестр или исключите предварительный реестр из него.');
		}

		return array('Error_Msg' => '');
	}

	/**
	 *	Комментарий
	 */
	function checkErrorDataFromTFOMSInRegistry($data) {
		if ( $data['RegistryType_id'] == 13 ) { // Объединенный
			if (!empty($data['SL_ID'])) {
				if (!empty($data['Registry_EvnNum'][$data['SL_ID']])) {
					$query = "
						select
							rd.Evn_id as \"Evn_id\",
							rd.Registry_id as \"Registry_id\",
							rd.RegistryType_id as \"RegistryType_id\"
						from 
						  	{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.RegistryData rd on RD.Registry_id = RGL.Registry_id
							inner join v_Evn E on E.Evn_id = rd.Evn_id
						where rgl.Registry_pid = :Registry_pid
							and rd.Registry_id = :Registry_id
							and rd.Evn_id = :Evn_id
						
						union all
						
						select
						rd.CmpCloseCard_id as \"Evn_id\",
						rd.Registry_id as \"Registry_id\",
						rd.RegistryType_id as \"RegistryType_id\"
						from 
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.RegistryDataCmp rd on RD.Registry_id = RGL.Registry_id
						where rgl.Registry_pid = :Registry_pid
							and rd.Registry_id = :Registry_id
							and rd.CmpCloseCard_id = :Evn_id
					";

					$params['Registry_pid'] = $data['Registry_id'];
					$params['Registry_id'] = $data['Registry_EvnNum'][$data['SL_ID']]['Registry_id'];
					$params['Evn_id'] = $data['Registry_EvnNum'][$data['SL_ID']]['Evn_id'];

					$result = $this->db->query($query, $params);

					if (is_object($result)) {
						$row = $result->result('array');

						if ( count($row) > 0 ) {
							return $row[0];
						}
					}
				}
			}
		}
		else if ( !empty($data['SL_ID']) ) {
			$query = "
				select
					rd.Evn_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.RegistryData rd 
					inner join v_Evn E on E.Evn_id = rd.Evn_id
				where rd.Registry_id = :Registry_id
					and rd.Evn_id = :SL_ID
				
				union all
				
				select
				rd.CmpCloseCard_id as \"Evn_id\",
				rd.Registry_id as \"Registry_id\",
				rd.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.RegistryDataCmp rd 
				where rd.Registry_id = :Registry_id
					and rd.CmpCloseCard_id = :SL_ID
			";

			$params['Registry_id'] = $data['Registry_id'];
			$params['SL_ID'] = $data['SL_ID'];

			$result = $this->db->query($query, $params);

			if (is_object($result))
			{
				$row = $result->result('array');
				if ( count($row) > 0 )
				{
					return $row[0];
				}
			}
		}
		else if ( !empty($data['N_ZAP']) ) {
			if ( empty($data['Registry_EvnNum']) || !is_array($data['Registry_EvnNum']) ) {
				return false;
			}
			else if ( count($this->_RegistryEvnNumByNZAP) == 0 ) {
				foreach ( $data['Registry_EvnNum'] as $SL_ID => $array ) {
					if ( !empty($array['N_ZAP']) && !isset($this->_RegistryEvnNumByNZAP[$array['N_ZAP']]) ) {
						$this->_RegistryEvnNumByNZAP[$array['N_ZAP']] = $SL_ID;
					}
				}
			}

			if ( empty($this->_RegistryEvnNumByNZAP[$data['N_ZAP']]) ) {
				return false;
			}

			$query = "
				select
					rd.Evn_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.RegistryData rd 
					inner join v_Evn E on E.Evn_id = rd.Evn_id
				where rd.Registry_id = :Registry_id  and rd.Evn_id = :SL_ID
				
				union all
				
				select
					rd.CmpCloseCard_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.RegistryDataCmp rd 
				where rd.Registry_id = :Registry_id and rd.CmpCloseCard_id = :SL_ID
			";

			$params['Registry_id'] = $data['Registry_id'];
			$params['SL_ID'] = $this->_RegistryEvnNumByNZAP[$data['N_ZAP']];

			$result = $this->db->query($query, $params);

			if (is_object($result)) {
				$row = $result->result('array');

				if ( count($row) > 0 ) {
					return $row[0];
				}
			}
		}

		return false;
	}

	/**
	 *	Комментарий
	 */
	public function checkErrorDataFromTFOMSInRegistryTest($data) {
		if ( empty($data['SL_ID']) ) {
			return false;
		}

		if ( $data['RegistryType_id'] == 13 ) { // Объединенный
			return $this->getFirstRowFromQuery("
				select
					rd.Evn_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from 
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_RegistryData rd on RD.Registry_id = RGL.Registry_id
					inner join v_Evn E on E.Evn_id = rd.Evn_id
				where rgl.Registry_pid = :Registry_pid
					and rd.Registry_id = :Registry_id
					and rd.Evn_id = :SL_ID
				
				union all
				
				select
					rd.Evn_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from 
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_RegistryDataCmp rd on RD.Registry_id = RGL.Registry_id
				where rgl.Registry_pid = :Registry_pid
					and rd.Registry_id = :Registry_id
					and rd.Evn_id = :SL_ID
			", $data);
		}
		else {
			return $this->getFirstRowFromQuery("
				select
					rd.Evn_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.v_RegistryData rd 
					inner join v_Evn E on E.Evn_id = rd.Evn_id
				where rd.Registry_id = :Registry_id
					and rd.Evn_id = :SL_ID
				
				union all
				
				select
					rd.Evn_id as \"Evn_id\",
					rd.Registry_id as \"Registry_id\",
					rd.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.v_RegistryDataCmp rd 
				where rd.Registry_id = :Registry_id
					and rd.Evn_id = :SL_ID
			", $data);
		}

		return false;
	}

	/**
	 * Формирование массива _RegistryEvnNumByNZAP
	 */
	public function setRegistryEvnNumByNZAP($data) {
		if ( empty($data['Registry_EvnNum']) || !is_array($data['Registry_EvnNum']) ) {
			return false;
		}

		foreach ( $data['Registry_EvnNum'] as $SL_ID => $array ) {
			if ( !empty($array['N_ZAP']) ) {
				if ( !isset($this->_RegistryEvnNumByNZAP[$array['N_ZAP']]) ) {
					$this->_RegistryEvnNumByNZAP[$array['N_ZAP']] = array();
				}

				$this->_RegistryEvnNumByNZAP[$array['N_ZAP']][] = array(
					'SL_ID' => $SL_ID,
					'Evn_id' => $array['Evn_id'],
					'Registry_id' => $array['Registry_id'],
				);
			}
		}

		return true;
	}

	/**
	 * Получение данных из _RegistryEvnNumByNZAP для N_ZAP
	 */
	public function getRegistryEvnNumByNZAP($N_ZAP) {
		if ( empty($N_ZAP) || !isset($this->_RegistryEvnNumByNZAP[$N_ZAP]) ) {
			return false;
		}

		return $this->_RegistryEvnNumByNZAP[$N_ZAP];
	}

	/**
	 *	Комментарий
	 */
	function checkErrorDataInRegistry($data)
	{
		$query = "
			select
				rd.Registry_id as \"Registry_id\",
				e.PersonEvn_id as \"PersonEvn_id\",
				e.Person_id as \"Person_id\",
				e.Evn_id as \"Evn_id\",
				pol.Polis_Ser as \"Polis_Ser\",
				pol.Polis_Num as \"Polis_Num\",
				pol.OrgSMO_id as \"OrgSMO_id\",
				pol.PolisType_id as \"PolisType_id\",
				to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
				coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
			from
				{$this->scheme}.v_RegistryData rd
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
				inner join Evn e on e.Evn_id = rd.Evn_id
				inner join v_Person_reg ps on ps.PersonEvn_id = e.PersonEvn_id AND ps.Server_id = e.Server_id
				left join v_Polis pol on pol.Polis_id = ps.Polis_id
			where
				rd.Registry_id = :Registry_id
				and rd.RegistryData_RowNum = :N_ZAP
		";

		$params['Registry_id'] = $data['Registry_id'];
		$params['N_ZAP'] = $data['N_ZAP'];

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]; // возвращаем данные о случае
			}
		}
		return false;
	}

	/**
	 *	Комментарий
	 */
	function loadRegistryErrorTFOMS($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}

		$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id as \"RegistryType_id\" FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));

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
			$filter .= " and ps.Person_SurName ilike :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName ilike :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName ilike :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$addToSelect = "";
		$leftjoin = "";

		if ($data['RegistryType_id'] == 6) {
			$evn_object = 'CmpCloseCard';
			$evn_fields = "
				null as \"Evn_rid\",
				null as \"EvnClass_id\",
				Evn.CmpCallCard_id as \"CmpCallCard_id\",
			";
		} else {
			$evn_object = 'Evn';
			$evn_fields = "
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
				null as \"CmpCallCard_id\",
			";
		}

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = Evn.Evn_rid ";
			$addToSelect .= "epd.DispClass_id as \"DispClass_id\", ";
		}

		if ( $data['RegistryType_id'] == 14 ) {
			$leftjoin .= " left join v_EvnPS eps on eps.EvnPS_id = RD.Evn_rid ";
			$addToSelect .= "eps.EvnPS_HTMTicketNum as \"EvnPS_HTMTicketNum\", ";
			$addToSelect .= "RTrim(coalesce(to_char(cast(eps.EvnPS_HTMBegDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnPS_HTMBegDate\", ";
			$addToSelect .= "RTrim(coalesce(to_char(cast(eps.EvnPS_HTMHospDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnPS_HTMHospDate\", ";
		}

		if ( $data['RegistryType_id'] == 1 ) {
			$leftjoin .= " left join v_MesOld MO on RD.Mes_id = MO.Mes_id ";
			$leftjoin .= " left join lateral(
					select MOUC.UslugaComplex_id from v_MesOldUslugaComplex MOUC where MOUC.Mes_id = MO.Mes_id and MOUC.UslugaComplex_id is not null limit 1
				) as MOUC on true";
			$leftjoin .= " left join v_UslugaComplex UC on MOUC.UslugaComplex_id = UC.UslugaComplex_id ";
			$addToSelect .= "MO.Mes_Code || ' ' || MO.MesOld_Num as \"Mes_Code\", ";
			$addToSelect .= "UC.UslugaComplex_Code as \"UslugaComplex_Code\", ";
		}

		$query = "
		Select 
			-- select
			RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
			RE.Registry_id as \"Registry_id\",
			RE.Evn_id as \"Evn_id\",
			{$evn_fields}
			ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
			ret.RegistryErrorType_Name as \"RegistryErrorType_Name\",
			ret.RegistryErrorType_Descr as \"RegistryErrorType_Descr\",
			rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
			ps.Person_id as \"Person_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Server_id as \"Server_id\",
			to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
			RE.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
			RE.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
			RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
			RE.RegistryErrorTFOMS_CommentCalc as \"RegistryErrorTFOMS_CommentCalc\",
			coalesce(MP.Person_Fio, RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			LS.LpuSection_Name as \"LpuSection_Name\",
			{$addToSelect}
			coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
			Diag.Diag_Code as \"Diag_Code\",
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryErrorTFOMS RE
			left join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			left join v_{$evn_object} Evn on Evn.{$evn_object}_id = RE.Evn_id
			left join v_EvnSection es on ES.EvnSection_id = RE.Evn_id
			left join v_Diag Diag on Diag.Diag_id = RD.Diag_id
			left join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = RE.Evn_id
			left join v_LpuSection LS on LS.LpuSection_id = coalesce(ES.LpuSection_id, evpl.LpuSection_id)
			left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join lateral(
				select Person_Fio from v_MedPersonal where MedPersonal_id = coalesce(ES.MedPersonal_id, evpl.MedPersonal_id) limit 1
			) as MP on true
			left join v_Person_bdz ps on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
			left join {$this->scheme}.v_RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryAdditionalFields() {
		return ',DispClass.DispClass_id as "DispClass_id",
		DispClass.DispClass_Name as "DispClass_Name",
		Registry_IsFinanc as "Registry_IsFinanc",
		R.Registry_IsOnceInTwoYears as "Registry_IsOnceInTwoYears"
		';
	}

	/**
	 *	Получение списка дополнительных джойнов для запроса
	 */
	function getLoadRegistryAdditionalJoin() {
		return '
			left join v_DispClass DispClass on DispClass.DispClass_id = R.DispClass_id
		';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryQueueAdditionalFields() {
		return ',R.DispClass_id as "DispClass_id",
		DispClass.DispClass_Name as "DispClass_Name",
		R.RegistryQueue_IsFinanc as "Registry_IsFinanc"
		';
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return ',DispClass_id as "DispClass_id",
		PayType_id as "PayType_id",
		Registry_IsFinanc as "Registry_IsFinanc",
		Registry_IsOnceInTwoYears as "Registry_IsOnceInTwoYears",
		Registry_IsZNO as "Registry_IsZNO"
		';
	}

	/**
	 *	Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	function SetXmlPackNum($data) {
		$query = "
			with mv as (
				select
					max(Registry_FileNum) as m
				from {$this->scheme}.v_Registry
				where Lpu_id = :Lpu_id
					and SUBSTRING(to_char(Registry_endDate, 'yyyymmdd'), 3, 4) = :Registry_endMonth
					and Registry_FileNum is not null
			)
			
			update {$this->scheme}.Registry
			set Registry_FileNum = coalesce((select m from mv), 0) + 1
			where Registry_id = :Registry_id
			returning (coalesce((select m from mv), 0) + 1) as \"packNum\"
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
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		$RegistryType_id = $this->getFirstResultFromQuery("select RegistryType_id as \"RegistryType_id\" from {$this->scheme}.v_Registry where Registry_id = :Registry_id limit 1", array('Registry_id' => $data['Registry_id']));

		if ( $RegistryType_id == 13 ) {
			$query = "
				select
					RTrim(Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					UR.RegistryType_id as \"RegistryType_id\",
					UR.RegistryStatus_id as \"RegistryStatus_id\",
					UR.RegistryGroupType_id as \"RegistryGroupType_id\",
					kn.KatNasel_SysNick as \"KatNasel_SysNick\",
					coalesce(pt.PayType_SysNick, 'oms') as \"PayType_SysNick\",
					RSum.Registry_IsNeedReform as \"Registry_IsNeedReform\",
					RSum.Registry_Sum - round(RDSum.RegistryData_ItogSum,2) as \"Registry_SumDifference\",
					RDSum.RegistryData_Count as \"RegistryData_Count\",
					coalesce(UR.RegistryCheckStatus_id,0) as \"RegistryCheckStatus_id\",
					coalesce(rcs.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					RTRIM(LTRIM(UR.Registry_Num)) as \"Registry_Num\",
					UR.Registry_IsFinanc as \"Registry_IsFinanc\",
					UR.DispClass_id as \"DispClass_id\",
					SUBSTRING(to_char(Registry_endDate, 'yyyymmdd'), 3, 4) as \"Registry_endMonth\", -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
					UR.Registry_IsZNO as \"Registry_IsZNO\",
					date_part('year', Registry_endDate) as \"Registry_endYear\"
				from {$this->scheme}.v_Registry UR
					left join v_KatNasel kn on kn.KatNasel_id = UR.KatNasel_id
					left join v_PayType pt on pt.PayType_id = UR.PayType_id
					left join lateral(
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(coalesce(RD.RegistryData_ItogSum, 0)) + SUM(coalesce(RD.RegistryData_ItogSum2, 0)) as RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RGL.Registry_id
						where
							RGL.Registry_pid = UR.Registry_id
					) RDSum on true
					left join lateral(
						select
							SUM(coalesce(R.Registry_Sum,0)) as Registry_Sum,
							MAX(coalesce(R.Registry_IsNeedReform, 1)) as Registry_IsNeedReform
						from
							{$this->scheme}.v_Registry R
							inner join {$this->scheme}.v_RegistryGroupLink RGL2 on RGL2.Registry_id = R.Registry_id
						where
							RGL2.Registry_pid = UR.Registry_id
					) RSum on true
					left join RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = UR.RegistryCheckStatus_id
				where
					UR.Registry_id = :Registry_id
			";
		} else {
			$query = "
				select
					RTrim(Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					R.RegistryType_id as \"RegistryType_id\",
					R.RegistryStatus_id as \"RegistryStatus_id\",
					kn.KatNasel_SysNick as \"KatNasel_SysNick\",
					coalesce(pt.PayType_SysNick, 'oms') as \"PayType_SysNick\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					coalesce(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as \"Registry_SumDifference\",
					RDSum.RegistryData_Count as \"RegistryData_Count\",
					coalesce(R.RegistryCheckStatus_id,0) as \"RegistryCheckStatus_id\",
					coalesce(rcs.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					RTRIM(LTRIM(R.Registry_Num)) as \"Registry_Num\",
					R.Registry_IsFinanc as \"Registry_IsFinanc\",
					R.DispClass_id as \"DispClass_id\",
					SUBSTRING(to_char(Registry_endDate, 'yyyymmdd'), 3, 4) as \"Registry_endMonth\", -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
					R.Registry_IsZNO as \"Registry_IsZNO\",
					date_part('year', Registry_endDate) as \"Registry_endYear\"
				from {$this->scheme}.Registry R
					left join v_PayType pt on pt.PayType_id = R.PayType_id
					left join v_KatNasel kn on kn.KatNasel_id = R.KatNasel_id
					left join lateral(
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(coalesce(RD.RegistryData_ItogSum, 0)) + SUM(coalesce(RD.RegistryData_ItogSum2, 0)) as RegistryData_ItogSum
						from {$this->scheme}.v_RegistryData RD
						where RD.Registry_id = R.Registry_id
					) RDSum on true
					left join RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				where
					R.Registry_id = :Registry_id
			";
		}

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
	 *	Получение данных для выгрузки реестров в XML
	 */
	function loadRegistryDataForXmlUsing($type, $data, &$number, &$nznumber, &$Registry_EvnNum, $registryIsUnion) {
		$dbreg = $this->load->database('registry1251', true); // получаем коннект к БД с кодировкой windows-1251

		$person_field = "ID_PAC";

		switch ( $type ) {
			case 1: $object = "EvnPS"; break; //stac
			case 2: $object = "EvnPL"; break; //polka
			case 6: $object = "SMP"; break; //смп
			case 7: $object = "EvnPLDD13"; break; //двн
			case 9: $object = "EvnPLOrp13"; break; //ддс
			case 11: $object = "EvnPLProf"; break; //повн
			case 12: $object = "EvnPLProfTeen"; break; //мон
			case 14: $object = "EvnHTM"; break; //мон
			case 15: $object = "EvnUslugaPar"; break; //параклиника
			default: return false; break;
		}

		$postfix = '_2018';
		if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
			$postfix = '_bud';
		}

		$p_zsl = $this->scheme . ".p_Registry_" . $object . "_expSL" . $postfix;
		$p_vizit = $this->scheme . ".p_Registry_" . $object . "_expVizit" . $postfix;
		$p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl" . $postfix;
		$p_pers = $this->scheme . ".p_Registry_" . $object . "_expPac" . $postfix;

		if (in_array($type, array(1))) {
			$p_kslp = $this->scheme . ".p_Registry_{$object}_expKSLP_2018";
		}

		if ($type == 2) {
			$p_usl_stom = $this->scheme . ".p_Registry_EvnPLStom_expUsl_2018";
		}

		if (in_array($type, array(1, 2, 14, 15)) && $data['PayType_SysNick'] == 'oms') {
			$p_bdiag = $this->scheme . ".p_Registry_" . $object . "_expBDIAG_2018";
		}

		if (in_array($type, array(1, 2, 14)) && $data['PayType_SysNick'] == 'oms') {
			$p_cons = $this->scheme . ".p_Registry_" . $object . "_expCONS_2018";
			$p_lek_pr = $this->scheme . ".p_Registry_" . $object . "_expLEK_PR_2018";
			$p_napr = $this->scheme . ".p_Registry_" . $object . "_expNAPR_2018";
			$p_onkousl = $this->scheme . ".p_Registry_" . $object . "_expONKOUSL_2018";

			if (in_array($type, array(1, 2, 14))) {
				$p_bprot = $this->scheme . ".p_Registry_" . $object . "_expBPROT_2018";
			}

			if (in_array($type, array(1))) {
				$p_crit = $this->scheme . ".p_Registry_" . $object . "_expCRIT_2018";
			}
		}

		if (in_array($type, array(7, 9, 11, 12))) {
			$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2_2018";
			$p_cons = $this->scheme . ".p_Registry_" . $object . "_expCONS_2018";
			$p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ_2018";
		}

		$idCaseField = 'Evn_rid';
		$netValue = toAnsi('НЕТ', true);
		$SL_ID_field = 'SL_IDCASE';
        if (in_array($data['PayType_SysNick'], ['bud', 'fbud'])) {
            $SL_ID_field = 'SL_ID';
        }
		
		$BDIAG = array();
		$BPROT = array();
		$CONS = array();
		$CRIT = array();
		$DS2 = array();
		$KSLP = array();
		$LEK_PR = array();
		$NAPR = array();
		$NAZ = array();
		$ONKUSL = array();
		$PACIENT = array();
		$SLUCH = array();
		$USL = array();
		$ZSL = array();

		// Законченные случаи
		$query = "select * from {$p_zsl}() Registry_id := :Registry_id)";
		$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result) ) {
			return false;
		}

		$expSLs = $result->result('array');

		foreach( $expSLs as $expSL ) {
			if ( !empty($expSL[strtolower($idCaseField)]) ) {
				if ( !isset($ZSL[$expSL[strtolower($idCaseField)]]) ) {
					$ZSL[$expSL[strtolower($idCaseField)]] = array();
				}

				$this->_IDCASE++;
				$expSL['IDCASE'] = $this->_IDCASE;

				$ZSL[$expSL[strtolower($idCaseField)]][] = $expSL;
			}
		}

		unset($expSLs);

		// посещения
		$query = "select * from {$p_vizit}( Registry_id := :Registry_id)";
		$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result) ) {
			return false;
		}

		$visits = $result->result('array');

		foreach ( $visits as $visit ) {
			if ( !empty($visit[strtolower($idCaseField)]) ) {
				if ( !isset($SLUCH[$visit[strtolower($idCaseField)]]) ) {
					$SLUCH[$visit[strtolower($idCaseField)]] = array();
				}

				$SLUCH[$visit[strtolower($idCaseField)]][] = $visit;
			}
		}

		unset($visits);

		// сведения о проведении консилиума
		if (!empty($p_cons)) {
			$query = "
				select * from {$p_cons}( Registry_id := ?)
			";
			$result_cons = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_cons)) {
				return false;
			}
			$result = $result_cons->result('array');

			foreach ($result as $row ) {
				if ( !isset($CONS[$row['evn_id']]) ) {
					$CONS[$row['evn_id']] = array();
				}
				
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$CONS[$row['evn_id']][] = $row;
			}
		}

		// направления
		if (!empty($p_napr)) {
			$query = "
				select * from {$p_napr}( Registry_id := ?)
			";
			$result_napr = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_napr)) {
				return false;
			}
			$result = $result_napr->result('array');

			foreach ($result as $row ) {
				if ( !isset($NAPR[$row['evn_id']]) ) {
					$NAPR[$row['evn_id']] = array();
				}
				
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$NAPR[$row['evn_id']][] = $row;
			}
		}

		// сведения о введенном противоопухолевом лекарственном препарате
		if (!empty($p_lek_pr)) {
			$query = "
				select * from {$p_lek_pr}( Registry_id := ?)
			";
			$result_lek_pr = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_lek_pr)) {
				return false;
			}
			$result = $result_lek_pr->result('array');

			foreach ( $result as $row ) {
				if ( !isset($LEK_PR[$row['evnuslugalek_id']]) ) {
					$LEK_PR[$row['evnuslugalek_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$LEK_PR[$row['evnuslugalek_id']][] = $row;
			}

			unset($result);
		}

		// сведения об услуге лечения онкологического заболевания
		if (!empty($p_onkousl)) {
			$query = "
				select * from {$p_onkousl}( Registry_id := ?)
			";
			$result_onkousl = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_onkousl)) {
				return false;
			}
			$result = $result_onkousl->result('array');

			foreach ($result as $row ) {
				if ( !isset($ONKUSL[$row['evn_id']]) ) {
					$ONKUSL[$row['evn_id']] = array();
				}

				$LEK_PR_DATA = array();

				if ( isset($LEK_PR[$row['evnuslugalek_id']]) && in_array($row['usl_tip'], array(2, 4)) ) {
					foreach ( $LEK_PR[$row['evnuslugalek_id']] as $rowTmp ) {
						if ( !isset($LEK_PR_DATA[$rowTmp['regnum']]) ) {
							$LEK_PR_DATA[$rowTmp['regnum']] = array(
								'REGNUM' => $rowTmp['regnum'],
								'CODE_SH' => $rowTmp['regnum'],
								'DATE_INJ_DATA' => array(),
							);
						}

						$LEK_PR_DATA[$rowTmp['regnum']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $rowTmp['date_inj']);
						if (!empty($rowTmp['date_inj1'])) {
							while ($rowTmp['date_inj'] < $rowTmp['date_inj1']) {
								$rowTmp['date_inj'] = date('Y-m-d', strtotime($rowTmp['date_inj']) + 24 * 60 * 60);
								$LEK_PR_DATA[$rowTmp['date_inj']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $rowTmp['date_inj']);
							}
						}
					}

					unset($LEK_PR[$row['evnuslugalek_id']]);
				}

				$row['lek_pr_dta'] = $LEK_PR_DATA;

				$ONKUSL[$row['evn_id']][] = $row;
			}
		}

		// услуги
		$query = "select * from {$p_usl} (Registry_id := :Registry_id)";
		$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result) ) {
			return false;
		}

		$uslugi = $result->result('array');

		foreach ( $uslugi as $usluga ) {
			if ( empty($usluga['date_in']) ) {
				continue;
			}

			$this->_IDSERV++;
			$usluga['idserv'] = $this->_IDSERV;

			if ( !isset($USL[$usluga['evn_id']]) ) {
				$USL[$usluga['evn_id']] = array();
			}

			$USL[$usluga['evn_id']][] = $usluga;
		}

		unset($uslugi);

		// стомат. услуги
		if ( !empty($p_usl_stom) ) {
			$query = "select * from {$p_usl_stom}( Registry_id := :Registry_id)";
			$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$uslugi = $result->result('array');

			foreach ( $uslugi as $usluga ) {
				// @task https://redmine.swan.perm.ru/issues/81284
				if ( empty($usluga['date_in']) ) {
					continue;
				}

				$this->_IDSERV++;
				$usluga['idserv'] = $this->_IDSERV;

				if ( !isset($USL[$usluga['evn_id']]) ) {
					$USL[$usluga['evn_id']] = array();
				}

				$USL[$usluga['evn_id']][] = $usluga;
			}

			unset($uslugi);
		}

		// назначения
		if ( !empty($p_naz) ) {
			$query = "select * from {$p_naz}( Registry_id := :Registry_id)";
			$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			foreach ( $resp as $row ) {
				if ( !isset($NAZ[$row['evn_id']]) ) {
					$NAZ[$row['evn_id']] = array();
				}

				$NAZ[$row['evn_id']][] = $row;
			}

			unset($resp);
		}

		// КСЛП
		if ( !empty($p_kslp) ) {
			$query = "select * from {$p_kslp}( Registry_id := :Registry_id)";
			$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			// Формируем массив KSLP
			foreach ( $resp as $one_kslp ) {
				if ( !isset($KSLP[$one_kslp['evn_id']]) ) {
					$KSLP[$one_kslp['evn_id']] = array();
				}

				$KSLP[$one_kslp['evn_id']][] = array(
					'Z_SL_KOEF' => (!empty($one_kslp['z_sl']) ? $one_kslp['z_sl'] : null),
					'IDSL' => $one_kslp['idsl'],
					'Z_SL_K' => (!empty($one_kslp['z_sl_k']) ? $one_kslp['z_sl_k'] : null),
				);
			}

		}

		// Критерии
		if ( !empty($p_crit) ) {
			$query = "select * from {$p_crit} (Registry_id := :Registry_id)";
			$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			// Формируем массив CRIT
			foreach ( $resp as $row ) {
				if ( !isset($CRIT[$row['evn_id']]) ) {
					$CRIT[$row['evn_id']] = array();
				}

				$CRIT[$row['evn_id']][] = array(
					'CRIT' => $row['crit'],
				);
			}

		}

		// диагнозы (DS2)
		if (!empty($p_ds2)) {
			$query = "select * from {$p_ds2} (Registry_id := :Registry_id)";
			$result_ds2 = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));
			if (!is_object($result_ds2)) {
				return false;
			}
			while ($row = $result_ds2->_fetch_assoc()) {
				if ( !isset($DS2[$row['evn_id']]) ) {
					$DS2[$row['evn_id']] = array();
				}

				$DS2[$row['evn_id']][] = $row;
			}
		}

		// диагностический блок
		if (!empty($p_bdiag)) {
			$query = "
				select * from {$p_bdiag} (Registry_id := ?)
			";
			$result_bdiag = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_bdiag)) {
				return false;
			}
			$result = $result_bdiag->result('array');

			foreach ($result as $row ) {
				if(!empty($row['evn_id'])){
					if ( !isset($BDIAG[$row['evn_id']]) ) {
						$BDIAG[$row['evn_id']] = array();
					}

					$BDIAG[$row['evn_id']][] = $row;
				}

			}
		}

		// сведения об имеющихся противопоказаниях и отказах
		if (!empty($p_bprot)) {
			$query = "
				select * from {$p_bprot} (Registry_id := ?)
			";
			$result_bprot = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_bdiag)) {
				return false;
			}
			$result = $result_bprot->result('array');

			foreach ($result as $row ) {
				if(!empty($row['evn_id'])){
					if ( !isset($BPROT[$row['evn_id']]) ) {
						$BPROT[$row['evn_id']] = array();
					}

					$BPROT[$row['evn_id']][] = $row;
				}

			}
		}

		// люди
		$query = "select * from {$p_pers} (Registry_id := :Registry_id)";
		$result = $dbreg->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result) ) {
			return false;
		}

		$person = $result->result('array');

		foreach ( $person as $pers ) {
			if ( empty($pers[$person_field]) ) {
				continue;
			}

			// некоторая обработка пациента
			$DOST = array();
			$DOST_P = array();

			if ( $pers['novor'] == '0' ) {
				if ( empty($pers['fam']) ) {
					$DOST[] = 2;
				}

				if ( empty($pers['im']) ) {
					$DOST[] = 3;
				}

				if ( empty($pers['ot']) || strtoupper($pers['ot']) == $netValue ) {
					$DOST[] = 1;
				}
			}
			else {
				if ( empty($pers['fam_p']) ) {
					$DOST_P[] = 2;
				}

				if ( empty($pers['im_p']) ) {
					$DOST_P[] = 3;
				}

				if ( empty($pers['ot_p']) || strtoupper($pers['ot_p']) == $netValue ) {
					$DOST_P[] = 1;
				}
			}

			$pers['dost'] = implode(';', $DOST);
			$pers['dost_p'] = implode(';', $DOST_P);

			$PACIENT[$pers[$person_field]] = $pers;
		}

		unset($person);

		// собираем массив для выгрузки
		$response = array();

		// Массив с записями
		$response['ZAP'] = array();

		foreach ( $ZSL as $key => $value ) {
			$nznumber++;
			$response['ZAP'][$key]['N_ZAP'] = $nznumber;
			$response['ZAP'][$key]['PR_NOV'] = null;
			if(isset($value[0]['PR_NOV'])){
				$response['ZAP'][$key]['PR_NOV'] = $value[0]['PR_NOV'];
			}

			$value[0]['VNOV_M'] = null;
			if(!empty($PACIENT[$value[0]['PersonEvn_id']]['VNOV_M'])){
				$value[0]['VNOV_M'] = $PACIENT[$value[0]['PersonEvn_id']]['VNOV_M'];
			}

			$value[0]['OS_SLUCH'] = array();
			foreach (['OS_SLUCH_0', 'OS_SLUCH_1', 'OS_SLUCH_2', 'OS_SLUCH_8', 'OS_SLUCH_9', 'OS_SLUCH_10'] as $osKey) {
				if (isset($PACIENT[$value[0]['PersonEvn_id']][$osKey])) {
					$value[0]['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $PACIENT[$value[0]['PersonEvn_id']][$osKey]);
				}
			}

			$response['ZAP'][$key]["Z_SL"] = $value;

			if ( is_array($value) && count($value) > 0 && !empty($value[0]['PersonEvn_id']) && isset($PACIENT[$value[0]['PersonEvn_id']]) ) {
				$response['ZAP'][$key]['PACIENT'] = array($PACIENT[$value[0]['PersonEvn_id']]);
			}
		}

		$KSG_KPG_DATA_FIELDS = array('N_KSG','VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK2', 'SL_K', 'IT_SL');
		$SANK_FIELDS = array('S_CODE', 'S_SUM', 'S_TIP', 'S_OSN', 'S_DOP', 'S_COM', 'S_DATE');
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');

		foreach ( $SLUCH as $key => $value ) {
			if ( !array_key_exists($key, $response['ZAP']) ) {
				continue;
			}

			foreach ( $value as $k => $val ) {
				$val['CONS_DATA'] = array();
				$val['KSG_KPG_DATA'] = array();
				$val['NAPR_DATA_SL'] = array();
				$val['NAZ'] = array();
				$val['ONK_SL_DATA'] = array();
				$val['SANK'] = array();
				$val['USL'] = array();

				$evnid = $val['Evn_id'];

				if ( isset($USL[$evnid]) ) {
					$val['USL'] = $USL[$evnid];
					unset($USL[$evnid]);
				}

				if ( isset($NAZ[$evnid]) ) {
					$val['NAZ'] = $NAZ[$evnid];
					unset($NAZ[$evnid]);
				}

				if ( isset($CONS[$evnid]) ) {
					$val['CONS_DATA'] = $CONS[$evnid];
					unset($CONS[$evnid]);
				} else if (array_key_exists('PR_CONS', $val) && array_key_exists('DT_CONS', $val) && (isset($val['PR_CONS']) || isset($val['DT_CONS']))) {
					$val['CONS_DATA'] = array(array(
						'PR_CONS' => $val['PR_CONS'],
						'DT_CONS' => $val['DT_CONS']
					));
					unset($val['PR_CONS']);
					unset($val['DT_CONS']);
				}

				if (empty($p_ds2) && !empty($val['DS2'])) {
					$val['DS2_SL'] = $val['DS2'];
				} else {
					$val['DS2_SL'] = null;
				}

				if ( isset($DS2[$evnid]) ) {
					$val['DS2_DATA'] = $DS2[$evnid];
					unset($DS2[$evnid]);

					if ( array_key_exists('DS2', $val) ) {
						unset($val['DS2']);
					}
				}
				else {
					$val['DS2_DATA'] = array();
				}

				if ( isset($NAPR[$evnid]) ) {
					$val['NAPR_DATA_SL'] = $NAPR[$evnid];
					unset($NAPR[$evnid]);
				}

				$SANK_DATA = array();
				foreach($SANK_FIELDS as $sankfield){
					if(isset($val[$sankfield])){
						$SANK_DATA[$sankfield] = $val[$sankfield];
					}

					if ( array_key_exists($sankfield, $val) ) {
						unset($val[$sankfield]);
					}
				}
				if(count($SANK_DATA) > 0) {
					foreach ( $SANK_FIELDS as $field ) {
						if ( !isset($SANK_DATA[$field]) ) {
							$SANK_DATA[$field] = null;
						}
					}

					$val['SANK'][] = $SANK_DATA;
				}

				$ONK_SL_DATA = array();

				if (
					(empty($val['DS_ONK']) || $val['DS_ONK'] != '1')
					&& !empty($val['DS1'])
					&& (
						substr($val['DS1'], 0, 1) == 'C'
						|| (substr($val['DS1'], 0, 3) >= 'D00' && substr($val['DS1'], 0, 3) <= 'D09')
						|| (
							substr($val['DS1'], 0, 3) == 'D70'
							&& !empty($val['DS2'])
							&& (
								(substr($val['DS2'], 0, 3) >= 'C00' && substr($val['DS2'], 0, 3) <= 'C80')
								|| substr($val['DS2'], 0, 3) == 'C97'
							)
						)
					)
				) {
					$hasOnkSLData = false;

					foreach ($ONK_SL_FIELDS as $onkslfield) {
						if (isset($val[$onkslfield])) {
							$ONK_SL_DATA[$onkslfield] = $val[$onkslfield];
							$hasOnkSLData = true;
						}
						else {
							$ONK_SL_DATA[$onkslfield] = null;
						}

						if ( array_key_exists($onkslfield, $val) ) {
							unset($val[$onkslfield]);
						}
					}

					if ( $hasOnkSLData === true ) {
						$ONK_SL_DATA['B_DIAG_DATA'] = array();
						$ONK_SL_DATA['B_PROT_DATA'] = array();
						$ONK_SL_DATA['ONK_USL_DATA_SL'] = array();

						if (isset($BDIAG[$evnid])) {
							$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$evnid];
							unset($BDIAG[$evnid]);
						}

						if (isset($BPROT[$evnid])) {
							$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$evnid];
							unset($BPROT[$evnid]);
						}

						if ( isset($ONKUSL[$evnid]) ) {
							$ONK_SL_DATA['ONK_USL_DATA_SL'] = $ONKUSL[$evnid];
							unset($ONKUSL[$evnid]);
						}

						$val['ONK_SL_DATA'][] = $ONK_SL_DATA;
					}
				}

				$KSG_KPG_DATA = array();

				foreach ( $KSG_KPG_DATA_FIELDS as $field ) {
					if ( isset($val[$field]) ) {
						$KSG_KPG_DATA[$field] = $val[$field];
					}

					unset($val[$field]);
				}

				if ( isset($KSLP[$evnid]) ) {
					$KSG_KPG_DATA['SL_KOEF_DATA'] = $KSLP[$evnid];
				}

				if ( isset($CRIT[$evnid]) ) {
					$KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$evnid];
				}

				if ( count($KSG_KPG_DATA) > 0 ) {
					foreach ( $KSG_KPG_DATA_FIELDS as $field ) {
						if ( !isset($KSG_KPG_DATA[$field]) ) {
							$KSG_KPG_DATA[$field] = null;
						}
					}

					if(!isset($KSG_KPG_DATA['SL_KOEF_DATA'])){
						$KSG_KPG_DATA['SL_KOEF_DATA'] = array();
					}

					if(!isset($KSG_KPG_DATA['CRIT_DATA'])){
						$KSG_KPG_DATA['CRIT_DATA'] = array();
					}

					$val['KSG_KPG_DATA'][] = $KSG_KPG_DATA;
				}

				if ( !isset($response['ZAP'][$key]['Z_SL'][0]['SL']) ) {
					$response['ZAP'][$key]['Z_SL'][0]['SL'] = array();
				}

				// #195439
				if (
					$data['Registry_endYear'] >= 2020 &&
					$data['Registry_IsZNO'] != 2 && (
						(!$registryIsUnion && in_array($type, [1, 2, 6])) ||
						($registryIsUnion && in_array($data['RegistryGroupType_id'], [21, 22, 23, 35]))
					)
				) {
					$val['DS_ONK'] = '';
					$val['NAPR_DATA_SL'] = [];
					$val['CONS_DATA'] = [];
					$val['ONK_SL_DATA'] = [];
				}

				$response['ZAP'][$key]['Z_SL'][0]['SL'][] = $val;

				$Registry_EvnNum[$val[$SL_ID_field]] = array(
					'Registry_id' => $val['Registry_id'],
					'Evn_id' => $evnid,
					'N_ZAP' => $response['ZAP'][$key]['N_ZAP'],
				);
			}
		}

		unset($SLUCH);

		foreach ( $response['ZAP'] as $key => $value ) {
			if ( !isset($response['ZAP'][$key]['Z_SL']) ) {
				unset($response['ZAP'][$key]);
			}
		}

		// Список пациентов
		$response['PACIENT'] = $PACIENT;
		unset($PACIENT);

		return $response;
	}


	/**
	 *	Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data) {
		if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
			$result = array(
				array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
				array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
				array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
				array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
				array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги')
			);
		} else {
			$result = array(
				array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
				array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
				array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
				array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения'),
				array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот'),
				array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
				array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'),
				array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
				array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги')
			);
		}

		return $result;
	}

	/**
	 *	Функция возрвращает набор данных для дерева реестра 2-го уровня (статус реестра)
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
				array('RegistryStatus_id' => 12, 'RegistryStatus_Name' => 'Удаленные')
			);
		}
		return $result;
	}

	/**
	 *	Список случаев по пациентам без документов ОМС
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
		$evn_join = " left join v_Evn Evn on Evn.Evn_id = RNP.Evn_id";
		$set_date_time = " to_char(Evn.Evn_setDT, 'dd.mm.yyyy hh24:mi') as \"Evn_setDT\"";

		$query = "
		Select
			RNP.Registry_id as \"Registry_id\",
			RNP.Evn_id as \"Evn_id\",
			RNP.Evn_rid as \"Evn_rid\",
			RNP.Person_id as \"Person_id\",
			RNP.Server_id as \"Server_id\",
			RNP.PersonEvn_id as \"PersonEvn_id\",
			rtrim(coalesce(RNP.Person_SurName,'')) || ' ' || rtrim(coalesce(RNP.Person_FirName,'')) || ' ' || rtrim(coalesce(RNP.Person_SecName, '')) as \"Person_FIO\",
			RTrim(coalesce(to_char(cast(RNP.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
			rtrim(LpuSection.LpuSection_Code) || '. ' || LpuSection.LpuSection_Name as \"LpuSection_Name\",
			{$set_date_time}
		from {$this->scheme}.v_RegistryNoPolis RNP
		left join v_LpuSection LpuSection on LpuSection.LpuSection_id = RNP.LpuSection_id
		{$evn_join}
		where
			RNP.Registry_id=:Registry_id
		order by RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName, LpuSection.LpuSection_Name";

		if ($data['RegistryType_id'] == 6) {
			$query = "
				Select
					RNP.Registry_id as \"Registry_id\",
					RNP.CmpCallCard_id as \"Evn_id\",
					null as \"Evn_rid\",
					RNP.Person_id as \"Person_id\",
					null as \"Server_id\",
					null as \"PersonEvn_id\",
					rtrim(coalesce(RNP.Person_SurName,'')) || ' ' || rtrim(coalesce(RNP.Person_FirName,'')) || ' ' || rtrim(coalesce(RNP.Person_SecName, '')) as \"Person_FIO\",
					RTrim(coalesce(to_char(cast(RNP.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
					rtrim(CP.CmpProfile_Code) || '. ' || CP.CmpProfile_Name as \"LpuSection_Name\",
					null as \"Evn_setDT\"
				from {$this->scheme}.v_RegistryCmpNoPolis RNP
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = RNP.CmpCallCard_id
				left join v_CmpEmergencyTeam CET on CET.CMPEmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_CmpProfile CP on CP.CmpProfile_id = CET.CmpProfile_id
				where
					RNP.Registry_id=:Registry_id
				order by RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName, CP.CmpProfile_Name";
		}

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
	 *	Установка статуса экспорта реестра в XML
	 */
	function SetXmlExportStatus($data) {
		if ( empty($data['Registry_EvnNum']) ) {
			$data['Registry_EvnNum'] = null;
		}

		if ( !empty($data['Registry_id']) ) {
			$query = "
				update
					{$this->scheme}.Registry
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

			if ( is_object($result) ) {
				return true;
			}
			else {
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 * Получение данных Дубли посещений (RegistryDouble)
	 */
	public function loadRegistryDouble($data) {
		$this->setRegistryParamsByType($data);

		switch ( $this->RegistryType_id ) {
			case 6:
				$query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\"
						,RD.Evn_id as \"Evn_id\"
						,null as \"Evn_rid\"
						,RD.Person_id as \"Person_id\"
						,rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\"
						,to_char(RD.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
						,CCC.Year_num as \"Evn_Num\"
						,ETS.EmergencyTeamSpec_Name as \"LpuSection_FullName\"
						,MP.Person_Fio as \"MedPersonal_Fio\"
						,to_char(CCC.AcceptTime, 'dd.mm.yyyy') as \"Evn_setDate\"
						,CCC.CmpCallCard_id as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						left join v_CmpCloseCard CCC on CCC.CmpCloseCard_id = RD.Evn_id
						left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = CCC.EmergencyTeamSpec_id
						left join lateral(
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = CCC.MedPersonal_id limit 1
						) as MP on true
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						-- end where
					order by
						-- order by
						RD.Person_SurName,
						RD.Person_FirName,
						RD.Person_SecName
						-- end order by
				";
			break;

			default:
				$query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\"
						,RD.Evn_id as \"Evn_id\"
						,EPL.EvnPL_id as \"Evn_rid\"
						,RD.Person_id as \"Person_id\"
						,rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\"
						,to_char(RD.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
						,EPL.EvnPL_NumCard as \"Evn_Num\"
						,LS.LpuSection_FullName as \"LpuSection_FullName\"
						,MP.Person_Fio as \"MedPersonal_Fio\"
						,to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"Evn_setDate\"
						,null as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = RD.Evn_id
						left join v_EvnPL EPL  on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
						left join v_LpuSection LS  on LS.LpuSection_id = EVPL.LpuSection_id
						left join lateral(
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = EVPL.MedPersonal_id limit 1
						) as MP on true
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						-- end where
					order by
						-- order by
						RD.Person_SurName, RD.Person_FirName, RD.Person_SecName
						-- end order by
				";
			break;
		}

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
	 * Запрос для проверки наличия данных для вкладки "Дубли посещений"
	 */
	public function getRegistryDoubleCheckQuery($scheme = 'dbo') {
		return "
			(select Evn_id from {$scheme}.v_RegistryDouble where Registry_id = R.Registry_id limit 1)
			union all
			(select Evn_id from {$scheme}.v_RegistryCmpDouble where Registry_id = R.Registry_id limit 1)
		";
	}

	/**
	 * Возвращает список настроек ФЛК
	 */
	public function loadRegistryEntiesSettings($data)
	{
		if(!$data['RegistryType_id']) return false;
		$params = array(
			'RegistryType_id' => $data['RegistryType_id']
		);
		$query = "
			SELECT
				FLKSettings_id as \"FLKSettings_id\"
				,cast(getdate() as timestamp) as \"DD\"
				,RegistryType_id as \"RegistryType_id\"
				,FLKSettings_EvnData as \"FLKSettings_EvnData\"
				,FLKSettings_PersonData as \"FLKSettings_PersonData\"
			FROM v_FLKSettings
			WHERE 
				RegistryType_id = :RegistryType_id AND
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then cast('2030-01-01' as date)
						else FLKSettings_endDate
					end
				AND FLKSettings_EvnData ilike '%astra%'
			limit 1
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
	 * Экспорт реестра по иногородним
	 */
	public function exportRegistryInog($data) {
		try {
			$this->load->library('textlog', array('file'=>'exportRegistryInog_' . date('Y_m_d') . '.log'));
			$this->textlog->add('');
			$this->textlog->add('Запуск');

			// Проверяем наличие и состояние реестра
			$this->textlog->add('GetRegistryXmlExport: Проверяем наличие и состояние реестра');
			$res = $this->GetRegistryXmlExport($data);
			$this->textlog->add('GetRegistryXmlExport: Проверка закончена');

			if ( !is_array($res) || count($res) == 0 ) {
				throw new Exception('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			}
			else if ( !in_array($res[0]['RegistryType_id'], array(1, 2, 6, 14)) ) {
				throw new Exception('Недопустимый тип реестра.');
			}
			else if ( in_array($res[0]['RegistryType_id'], array(2, 6, 14)) && !in_array($res[0]['KatNasel_SysNick'], array('inog','allinog')) ) {
				throw new Exception('Допускается выгрузка только реестров с категорией жителей "Иногородние".');
			}

			$data['Registry_Num'] = $res[0]['Registry_Num'];
			$data['RegistryType_id'] = $res[0]['RegistryType_id'];

			$this->setRegistryParamsByType($data);

			$registryInogData = $this->_getRegistryInogData($data);

			if ( $registryInogData === false || !is_array($registryInogData) ) {
				throw new Exception('Ошибка при получении данных реестра');
			}

			$dbfStructure = array(
				array("RECID",	"N",	 20, 0 ),
				array("FAM",	"C",	 40, 0 ),
				array("IM",		"C",	 40, 0 ),
				array("OT",		"C",	 40, 0 ),
				array("W",		"N",	  1, 0 ),
				array("DR",		"D",	  8, 0 ),
				array("DOCTP",	"N",	 11, 0 ),
				array("DOCS",	"C",	 10, 0 ),
				array("DOCN",	"C",	 10, 0 ),
				array("OPDOC",	"C",	 10, 0 ),
				array("SPOL",	"C",	 10, 0 ),
				array("NPOL",	"C",	 10, 0 ),
				array("ENP",	"C",	 16, 0 ),
				array("SS",		"C",	 14, 0 ),
				array("MR",		"C",	100, 0 ),
				array("ADDR",	"C",	255, 0 ),
				array("LOGRN",	"C",	 15, 0 ),
				array("DIN",	"D",	  8, 0 ),
				array("DOUT",	"D",	  8, 0 ),
				array("OKATO",	"C",	  6, 0 ),
				array("SM",		"N",	 20, 0 ),
				array("SCHET",	"C",	 20, 0 ),
			);
			$out_dir = "registry_inog_" . time() . "_" . $data['Registry_id'];

			if ( !file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				@mkdir(EXPORTPATH_REGISTRY . $out_dir);
			}

			if ( !is_dir(EXPORTPATH_REGISTRY . $out_dir) ) {
				throw new Exception('Ошибка при создании папки для выгрузки');
			}

			if ( mb_strlen($res[0]['Registry_Num']) < 5 ) {
				$Registry_Num = str_pad($res[0]['Registry_Num'], 5, '0', STR_PAD_LEFT);
			}
			else {
				$Registry_Num = mb_substr($res[0]['Registry_Num'], 0, 5);
			}

			$fileName = 'Fed' . trim($Registry_Num);
			$dbfFileName = translit($fileName) . '.dbf';
			$zipFileName = translit($fileName) . '.mpz';

			$DBF = @dbase_create(EXPORTPATH_REGISTRY . $out_dir . '/' . $dbfFileName, $dbfStructure);

			$unknownValue = 'НЕИЗВЕСТЕН';

			foreach ( $registryInogData as $row ) {
				if (
					(!empty($row['FAM']) && mb_strtoupper($row['FAM']) == $unknownValue)
					|| (!empty($row['IM']) && mb_strtoupper($row['IM']) == $unknownValue)
					|| (!empty($row['OT']) && mb_strtoupper($row['OT']) == $unknownValue)
				) {
					continue;
				}

				array_walk($row, 'ConvertFromUtf8ToCp866');

				if ( !empty($row['SS']) ) {
					if ( strlen($row['SS']) == 11 ) {
						$row['SS'] = substr($row['SS'], 0, 3) . '-' . substr($row['SS'], 3, 3) . '-' . substr($row['SS'], 6, 3) . ' ' . substr($row['SS'], -2);
					}

					if ( strlen($row['SS']) != 14 ) {
						$row['SS'] = '';
					}
				}

				if ( $row['OPDOC'] == 3 ) {
					$row['NPOL'] = '';
					$row['SPOL'] = '';
				}

				dbase_add_record($DBF, array_values($row));
			}

			@dbase_close($DBF);

			$zip = new ZipArchive();
			$res = $zip->open(EXPORTPATH_REGISTRY . $out_dir . '/' . $zipFileName, ZIPARCHIVE::CREATE);

			if ( $res !== true ) {
				switch ( true ) {
					case ($res == ZipArchive::ER_EXISTS):
						$errorMessage = 'Файл уже существует';
						break;

					case ($res == ZipArchive::ER_INCONS):
						$errorMessage = 'Несовместимый ZIP-архив';
						break;

					case ($res == ZipArchive::ER_INVAL):
						$errorMessage = 'Недопустимый аргумент';
						break;

					case ($res == ZipArchive::ER_MEMORY):
						$errorMessage = 'Ошибка динамического выделения памяти';
						break;

					case ($res == ZipArchive::ER_NOENT):
						$errorMessage = 'Нет такого файла';
						break;

					case ($res == ZipArchive::ER_NOZIP):
						$errorMessage = 'Не является ZIP-архивом';
						break;

					case ($res == ZipArchive::ER_OPEN):
						$errorMessage = 'Невозможно открыть файл';
						break;

					case ($res == ZipArchive::ER_READ):
						$errorMessage = 'Ошибка чтения';
						break;

					case ($res == ZipArchive::ER_SEEK):
						$errorMessage = 'Ошибка поиска';
						break;

					default:
						$errorMessage = 'Ошибка создания архива';
						break;
				}

				throw new Exception($errorMessage);
			}

			$zip->AddFile(EXPORTPATH_REGISTRY . $out_dir . '/' . $dbfFileName, $dbfFileName);
			$zip->close();

			unlink(EXPORTPATH_REGISTRY . $out_dir . '/' . $dbfFileName);

			if ( !file_exists(EXPORTPATH_REGISTRY . $out_dir . '/' . $zipFileName) ) {
				throw new Exception('Ошибка создания архива');
			}

			$link = EXPORTPATH_REGISTRY . $out_dir . '/' . $zipFileName;

			$response = array(array('success' => true, 'Link' => $link));
		}
		catch ( Exception $e ) {
			$this->textlog->add('Выход с сообщением: ' . $e->getMessage());

			$response = array(array('success' => false, 'Error_Msg' => $e->getMessage()));
		}

		return $response;
	}

	/**
	 * Получение данных реестра по иногородним
	 */
	protected function _getRegistryInogData($data) {
		$filterList = array("rd.Registry_id = :Registry_id");
		$joinList = array();

		if ( $data['RegistryType_id'] == 1 ) {
			$filterList[] = "(coalesce(ost.OMSSprTerr_Code, 2) = 2 or coalesce(ost.OMSSprTerr_Code, 2) > 15)";
		}

		$query = "
			select
				ps.Person_id as \"RECID\",
				RTRIM(ps.Person_Surname) as \"FAM\",
				RTRIM(ps.Person_Firname) as \"IM\",
				RTRIM(ps.Person_Secname) as \"OT\",
				sx.Sex_Code as \"W\",
				to_char(ps.Person_BirthDay, 'yyyymmdd') as \"DR\",
				dt.DocumentType_Code as \"DOCTP\",
				d.Document_Ser as \"DOCS\",
				d.Document_Num as \"DOCN\",
				pt.PolisType_CodeF008 as \"OPDOC\",
				pls.Polis_Ser as \"SPOL\",
				pls.Polis_Num as \"NPOL\",
				ps.Person_EdNum as \"ENP\",
				ps.Person_Snils as \"SS\",
				left(oj.Org_Name, 100) as \"MR\",
				left(a.Address_Address, 255) as \"ADDR\",
				o.Org_OGRN as \"LOGRN\",
				to_char(rd.Evn_setDate, 'yyyymmdd') as \"DIN\",
				to_char(rd.Evn_disDate, 'yyyymmdd') as \"DOUT\",
				left(o.Org_OKATO, 6) as \"OKATO\",
				0 as \"SM\",
				'" . (!empty($data['Registry_Num']) ? $data['Registry_Num'] : '') . "' as \"SCHET\"
			from {$this->scheme}.v_{$this->RegistryDataObject} rd
				inner join v_PersonState ps on ps.Person_id = rd.Person_id
				left join v_Sex sx on sx.Sex_id = ps.Sex_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
				left join v_OrgSMO os on os.OrgSMO_id = pls.OrgSMO_id
				left join v_Org o on o.Org_id = os.Org_id
				left join v_PolisType pt on pt.PolisType_id = pls.PolisType_id
				left join v_Address a on a.Address_id = ps.UAddress_id
				left join v_Job j on j.Job_id = ps.job_id
				left join v_Org oj on oj.Org_id = j.Org_id
			where " . implode(' and ', $filterList) . "

			union all

			select
				ps.Person_id as \"RECID\",
				RTRIM(ps.Person_Surname) as \"FAM\",
				RTRIM(ps.Person_Firname) as \"IM\",
				RTRIM(ps.Person_Secname) as \"OT\",
				sx.Sex_Code as \"W\",
				to_char(ps.Person_BirthDay, 'yyyymmdd') as \"DR\",
				dt.DocumentType_Code as \"DOCTP\",
				d.Document_Ser as \"DOCS\",
				d.Document_Num as \"DOCN\",
				pt.PolisType_CodeF008 as \"OPDOC\",
				pls.Polis_Ser as \"SPOL\",
				pls.Polis_Num as \"NPOL\",
				ps.Person_EdNum as \"ENP\",
				ps.Person_Snils as \"SS\",
				left(oj.Org_Name, 100) as \"MR\",
				left(a.Address_Address, 255) as \"ADDR\",
				o.Org_OGRN as \"LOGRN\",
				to_char(rd.Evn_setDate, 'yyyymmdd') as \"DIN\",
				to_char(rd.Evn_disDate, 'yyyymmdd') as \"DOUT\",
				left(o.Org_OKATO, 6) as \"OKATO\",
				0 as \"SM\",
				'" . (!empty($data['Registry_Num']) ? $data['Registry_Num'] : '') . "' as \"SCHET\"
			from {$this->scheme}.v_{$this->RegistryErrorObject} rd
				inner join v_PersonState ps on ps.Person_id = rd.Person_id
				left join v_Sex sx on sx.Sex_id = ps.Sex_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
				left join v_OrgSMO os on os.OrgSMO_id = pls.OrgSMO_id
				left join v_Org o on o.Org_id = os.Org_id
				left join v_PolisType pt on pt.PolisType_id = pls.PolisType_id
				left join v_Address a on a.Address_id = ps.UAddress_id
				left join v_Job j on j.Job_id = ps.job_id
				left join v_Org oj on oj.Org_id = j.Org_id
			where " . implode(' and ', $filterList) . "

			union all

			select
				ps.Person_id as \"RECID\",
				RTRIM(ps.Person_Surname) as \"FAM\",
				RTRIM(ps.Person_Firname) as \"IM\",
				RTRIM(ps.Person_Secname) as \"OT\",
				sx.Sex_Code as \"W\",
				to_char(ps.Person_BirthDay, 'yyyymmdd') as \"DR\",
				dt.DocumentType_Code as \"DOCTP\",
				d.Document_Ser as \"DOCS\",
				d.Document_Num as \"DOCN\",
				pt.PolisType_CodeF008 as \"OPDOC\",
				pls.Polis_Ser as \"SPOL\",
				pls.Polis_Num as \"NPOL\",
				ps.Person_EdNum as \"ENP\",
				ps.Person_Snils as \"SS\",
				left(oj.Org_Name, 100) as \"MR\",
				left(a.Address_Address, 255) as \"ADDR\",
				o.Org_OGRN as \"LOGRN\",
				to_char(rd.Evn_setDate, 'yyyymmdd') as \"DIN\",
				to_char(rd.Evn_disDate, 'yyyymmdd') as \"DOUT\",
				left(o.Org_OKATO, 6) as \"OKATO\",
				0 as \"SM\",
				'" . (!empty($data['Registry_Num']) ? $data['Registry_Num'] : '') . "' as \"SCHET\"
			from {$this->scheme}.v_{$this->RegistryNoPolis} rd
				inner join v_PersonState ps on ps.Person_id = rd.Person_id
				left join v_Sex sx on sx.Sex_id = ps.Sex_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
				left join v_OrgSMO os on os.OrgSMO_id = pls.OrgSMO_id
				left join v_Org o on o.Org_id = os.Org_id
				left join v_PolisType pt on pt.PolisType_id = pls.PolisType_id
				left join v_Address a on a.Address_id = ps.UAddress_id
				left join v_Job j on j.Job_id = ps.job_id
				left join v_Org oj on oj.Org_id = j.Org_id
			where " . implode(' and ', $filterList) . "

			union all

			select
				ps.Person_id as \"RECID\",
				RTRIM(ps.Person_Surname) as \"FAM\",
				RTRIM(ps.Person_Firname) as \"IM\",
				RTRIM(ps.Person_Secname) as \"OT\",
				sx.Sex_Code as \"W\",
				to_char(ps.Person_BirthDay, 'yyyymmdd') as \"DR\",
				dt.DocumentType_Code as \"DOCTP\",
				d.Document_Ser as \"DOCS\",
				d.Document_Num as \"DOCN\",
				pt.PolisType_CodeF008 as \"OPDOC\",
				pls.Polis_Ser as \"SPOL\",
				pls.Polis_Num as \"NPOL\",
				ps.Person_EdNum as \"ENP\",
				ps.Person_Snils as \"SS\",
				left(oj.Org_Name, 100) as \"MR\",
				left(a.Address_Address, 255) as \"ADDR\",
				o.Org_OGRN as \"LOGRN\",
				null as \"DIN\",
				null as \"DOUT\",
				left(o.Org_OKATO, 6) as \"OKATO\",
				0 as \"SM\",
				'" . (!empty($data['Registry_Num']) ? $data['Registry_Num'] : '') . "' as \"SCHET\"
			from {$this->scheme}.v_{$this->RegistryPersonObject} rd
				inner join v_PersonState ps on ps.Person_id = rd.Person_id
				left join v_Sex sx on sx.Sex_id = ps.Sex_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
				left join v_OrgSMO os on os.OrgSMO_id = pls.OrgSMO_id
				left join v_Org o on o.Org_id = os.Org_id
				left join v_PolisType pt on pt.PolisType_id = pls.PolisType_id
				left join v_Address a on a.Address_id = ps.UAddress_id
				left join v_Job j on j.Job_id = ps.job_id
				left join v_Org oj on oj.Org_id = j.Org_id
			where " . implode(' and ', $filterList) . "

			order by
				\"RECID\",
				\"DOUT\" desc
		";

		$response = $this->queryResult($query, $data);

		if ( $response === false || !is_array($response) ) {
			return $response;
		}

		$PersonList = array();

		foreach ( $response as $key => $row ) {
			if ( in_array($row['RECID'], $PersonList) ) {
				unset($response[$key]);
			}

			$PersonList[] = $row['RECID'];
		}

		return $response;
	}

	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry($data)
	{
		// проверка уникальности номера реестра по лпу в одном году
		$query = "
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
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				return array('Error_Msg' => 'Номер счета не должен повторяться в году');
			}
		}

		if ( !empty($data['Registry_id']) ) {
			$registryData = $this->getFirstRowFromQuery("
				select
					rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\"
				from
					{$this->scheme}.v_Registry r
					left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
				where
					r.Registry_id = :Registry_id
			", $data);

			if ( $registryData === false ) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных реестра)');
			}

			// проверка статуса реестра
			if ( $registryData['RegistryCheckStatus_Code'] == 1 ) {
				return array('Error_Msg' => 'Реестр заблокирован, изменение недопустимо');
			}
		}

		$registrytypefilter = "";
		switch ($data['RegistryGroupType_id']) {
			case 2: // Оказание высокотехнологичной медицинской помощи
				$registrytypefilter = " and R.RegistryType_id = 14";
				break;
			case 3:
				if($data['Registry_IsOnceInTwoYears'] == 2){
					// Дисп-ция взр. населения 1-ый этап + флаг «Раз в 2 года» установлен
					$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and R.Registry_IsOnceInTwoYears = 2";
				}else{
					// Дисп-ция взр. населения 1-ый этап + флаг «Раз в 2 года» НЕ установлен
					$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and coalesce(R.Registry_IsOnceInTwoYears, 1) = 1";
				}
				break;
			case 4: // Дисп-ция взр. населения 2-ой этап
				$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 2";
				break;
			case 10: // Профилактические осмотры взрослого населения
				$registrytypefilter = " and R.RegistryType_id IN (11)";
				break;
			case 21: // СМП
				$registrytypefilter = " and R.RegistryType_id IN (6)";
				break;
			case 22: // Подушевое финансирование
				$registrytypefilter = " and  
						R.RegistryType_id IN (1,2) 
						and R.Registry_IsFinanc = 2
						and coalesce(R.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)
						";
				break;
			case 23: // Неподушевое финансирование
				$registrytypefilter = " and 
						R.RegistryType_id IN (1,2) 
						and coalesce(R.Registry_IsFinanc, 1) = 1
						and coalesce(R.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)";
				break;
			case 27: // Дисп-ция детей-сирот стационарных 1-ый этап
				$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 3";
				break;
			case 28: // Дисп-ция детей-сирот стационарных 2-ой этап
				$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 4";
				break;
			case 29: // Дисп-ция детей-сирот усыновленных 1-ый этап
				$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 7";
				break;
			case 30: // Дисп-ция детей-сирот усыновленных 2-ый этап
				$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 8";
				break;
			case 31: // Профилактические осмотры несовершеннолетних 1-ый этап
				$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 10";
				break;
			case 32: // Профилактические осмотры несовершеннолетних 2-ой этап
				$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 12";
				break;
			case 35: // Неподушевое финансирование и взаиморасчёты
				$registrytypefilter = " and 
					R.RegistryType_id IN (1,2,20) 
					and coalesce(R.Registry_IsFinanc, 1) = 1
					and coalesce(R.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)";
				break;
			case 15: // Параклинические услуги
				$registrytypefilter = " and R.RegistryType_id = 15";
				break;
		}

		// 3. выполняем поиск реестров которые войдут в объединённый
		$query = "
			select
				R.Registry_id as \"Registry_id\",
				case when exists(select RE.RegistryErrorTFOMS_id from {$this->scheme}.v_RegistryErrorTFOMS RE where RE.Registry_id = R.Registry_id limit 1)
					then 1
					else 0
				end as \"existTFOMSError\"
			from
				{$this->scheme}.v_Registry R
				left join v_KatNasel kn on kn.KatNasel_id = R.KatNasel_id
			where
				R.RegistryType_id <> 13
				and R.RegistryStatus_id = 2 -- к оплате
				and (R.KatNasel_id = :KatNasel_id OR R.RegistryType_id = 1)
				and R.Lpu_id = :Lpu_id
				and R.Registry_begDate >= :Registry_begDate
				and R.Registry_endDate <= :Registry_endDate
				and not exists(select RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink where Registry_id = R.Registry_id limit 1)
				and coalesce(r.PayType_id, (Select PayType_id from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1)
					) = (Select PayType_id from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1)
				{$registrytypefilter}
		";
		$resp_reg = $this->queryResult($query, array(
			'Registry_IsZNO' => $data['Registry_IsZNO'],
			'KatNasel_id' => $data['KatNasel_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
		));

		// проверка наличия ошибок на обычных реестрах
		foreach($resp_reg as $one_reg) {
			if ($one_reg['existTFOMSError'] == 1) {
				if (empty($data['ignoreExistTFOMSError'])) {
					return array('Error_Msg' => 'YesNo', 'Error_Code' => 100, 'Alert_Msg' => 'На одном из предварительных реестров обнаружены ошибки ТФОМС. Ошибки будут удалены. Продолжить?');
				}
			}

			if (!empty($data['ignoreExistTFOMSError'])) {
				$this->deleteRegistryErrorTFOMS(array(
					'Registry_id' => $one_reg['Registry_id']
				));
			}
		}


		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "
			select
				(select KatNasel_Code from v_KatNasel where KatNasel_id = :KatNasel_id limit 1) as \"KatNasel_Code\",
				Registry_id as \"Registry_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
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
				Registry_IsZNO := :Registry_IsZNO,
				Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
				RegistryGroupType_id := :RegistryGroupType_id,
				Lpu_id := :Lpu_id,
				OrgSMO_id := null,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
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

				// 4. сохраняем новые связи
				foreach($resp_reg as $one_reg) {
					$query = "
						select
							RegistryGroupLink_id as \"RegistryGroupLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from {$this->scheme}.p_RegistryGroupLink_ins(
							Registry_pid := :Registry_pid,
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id
						)
					";

					$this->db->query($query, array(
						'Registry_pid' => $resp[0]['Registry_id'],
						'Registry_id' => $one_reg['Registry_id'],
						'pmUser_id' => $data['pmUser_id']
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
	 *	Установка статуса реестра
	 */
	function setUnionRegistryStatus($data)
	{
		if (!isSuperAdmin() && !havingGroup('RegistryUser')) {
			return array('Error_Msg' => 'Смена статуса объединенного реестра запрещена');
		}
		if ($data['RegistryStatus_id'] != 4) {
			return array('Error_Msg' => 'Перевод реестра возможен только в статус "Оплаченный"');
		}

		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			return array('Error_Msg' => 'Пустые значения входных параметров');
		}

		$query = "
			select
				RGL.Registry_id as \"Registry_id\"
			from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join v_Registry R on R.Registry_id = RGL.Registry_id
			where
				RGL.Registry_pid = :Registry_id
				and R.RegistryStatus_id != 4
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return array('Error_Msg' => 'Ошибка при получении списка предварительных реестров');
		}

		$resp = $result->result('array');

		foreach ( $resp as $respone ) {
			$query = "
				select
					4 as \"RegistryStatus_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_setPaid(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'Registry_id' => $respone['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченного'));
			}
		}

		$query = "
			with mv as (
				Select
					SUM(coalesce(R.Registry_Sum,0)) as Registry_Sum,
					SUM(coalesce(R.Registry_SumPaid,0)) as Registry_SumPaid
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id=RGL.Registry_id
				where
					RGL.Registry_pid = :Registry_id
			), mv2 as (
				select
					case
						when exists(
							select
								r.Registry_id
							from
								v_Registry r
								inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
								inner join v_RegistryGroupLink rgl on rgl.Registry_id = r.Registry_id
							where
								rgl.Registry_pid = :Registry_id
								and rcs.RegistryCheckStatus_Code = 3 -- принят частично
							limit 1
						)
					then
						(select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = 3 limit 1)
					else
						(select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = 8 limit 1)
					end as RegistryCheckStatus_id
			)

			update {$this->scheme}.Registry
			set
				RegistryStatus_id = :RegistryStatus_id,
				Registry_updDT = dbo.tzGetDate(),
				RegistryCheckStatus_id = (select RegistryCheckStatus_id from mv2),
				Registry_Sum = (select Registry_Sum from mv),
				Registry_SumPaid = (select Registry_SumPaid from mv),
				pmUser_updID = :pmUser_id
			where
				Registry_id = :Registry_id
			returning :RegistryStatus_id as \"RegistryStatus_id\", 0 as \"Error_Code\", null as \"Error_Msg\";
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
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid($data)
	{
		$query = "
		Select
			-- select
			R.Registry_id as \"Registry_id\",
			R.Registry_Num as \"Registry_Num\",
			to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
			to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
			to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
			coalesce(RS.Registry_Sum, 0.00) as \"Registry_Sum\",
			coalesce(RS.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
			R.RegistryStatus_id as \"RegistryStatus_id\",
			RES.RegistryStatus_Name as \"RegistryStatus_Name\",
			R.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
			RCS.RegistryCheckStatus_SysNick as \"RegistryCheckStatus_SysNick\",
			coalesce('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:'
				|| CAST(R.Registry_id as varchar)
				|| '});''>'
				||rcs.RegistryCheckStatus_Name
				||'</a>', ''
			) as \"RegistryCheckStatus_Name\",
			RegistryErrorFLK.FlkErrors_IsData as \"FlkErrors_IsData\",
			RegistryErrorMEK.MekErrors_IsData as \"MekErrors_IsData\",
			RegistryCount.Registry_Count as \"Registry_Count\",
			case when R.Registry_IsZNO = 2 then 'Да' else '' end as \"Registry_IsZNO\",
			to_char(R.Registry_updDT, 'dd.mm.yyyy') as \"Registry_updDT\"
			-- end select
		from
			-- from
			{$this->scheme}.v_Registry R -- объединённый реестр
			left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			left join v_RegistryStatus res on res.RegistryStatus_id = R.RegistryStatus_id
			left join lateral(
				select
					SUM(coalesce(R2.Registry_SumPaid,0)) as Registry_SumPaid,
					SUM(coalesce(R2.Registry_Sum,0)) as Registry_Sum
				from {$this->scheme}.v_Registry R2
					inner join {$this->scheme}.v_RegistryGroupLink RGL on R2.Registry_id = RGL.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
			) RS on true
			left join lateral(
				select
					case when RE.Registry_id is not null then 1 else 0 end as FlkErrors_IsData
				from
					{$this->scheme}.v_RegistryErrorTFOMS RE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RE.Registry_id
					left join RegistryErrorTFOMSType RET on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
				where
					RGL.Registry_pid = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_FLK'
				limit 1
			) RegistryErrorFLK on true
			left join lateral(
				select
					case when RE.Registry_id is not null then 1 else 0 end as MekErrors_IsData
				from
					{$this->scheme}.v_RegistryErrorTFOMS RE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RE.Registry_id
					left join RegistryErrorTFOMSType RET on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
				where RGL.Registry_pid = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_MEK'
				limit 1
			) RegistryErrorMEK on true
			left join lateral(
				select
					SUM(RSIMPLE.Registry_RecordCount) as Registry_Count
				from
					{$this->scheme}.v_Registry RSIMPLE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RSIMPLE.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
			) RegistryCount on true
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
			-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');

			$count = count($response['data']);
			if (intval($data['start']) != 0 || $count >= intval($data['limit'])) { // считаем каунт только если записей не меньше, чем лимит или не первая страничка грузится
				$result_count = $this->db->query(getCountSQLPH($query), $data);

				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			}

			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm($data)
	{
		$query = "
			select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
				R.Lpu_id as \"Lpu_id\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.Registry_IsZNO as \"Registry_IsZNO\",				
				R.Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
				R.KatNasel_id as \"KatNasel_id\"
			from
				{$this->scheme}.v_Registry R
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
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry($data)
	{
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\",
				rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
				r.RegistryType_id as \"RegistryType_id\"
			from
				{$this->scheme}.v_Registry r
				left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				r.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array(
			'Registry_id' => $data['id']
		));
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				$data['Registry_id'] = $resp[0]['Registry_id'];
				if ($resp[0]['RegistryType_id'] != '13') {
					return array('Error_Msg' => 'Указанный реестр не является объединённым');
				}
				if (!isSuperAdmin() && (!empty($resp[0]['RegistryCheckStatus_id']) && !in_array($resp[0]['RegistryCheckStatus_Code'], array('2','5')))) {
					return array('Error_Msg' => "Нельзя удалить объединённый реестр, т.к. его статус: {$resp[0]['RegistryCheckStatus_Name']}");
				} else if (isSuperAdmin() && (!empty($resp[0]['RegistryCheckStatus_id']) && in_array($resp[0]['RegistryCheckStatus_Code'], array('0','1','4','7')))) {
					return array('Error_Msg' => "Нельзя удалить объединённый реестр, т.к. его статус: {$resp[0]['RegistryCheckStatus_Name']}");
				}
			}
		}

		if (empty($data['Registry_id'])) {
			return array('Error_Msg' => 'Не найден реестр для удаления');
		}

		// 1. удаляем все связи
		$this->deleteRegistryGroupLink(array(
			'Registry_pid' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// 2. удаляем сам реестр
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_del(
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id
			)
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (is_object($result))
		{
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid($data)
	{
		$query = "
		Select
			-- select
			R.Registry_id as \"Registry_id\",
			R.Registry_Num as \"Registry_Num\",
			to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
			to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
			to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
			KN.KatNasel_Name as \"KatNasel_Name\",
			RT.RegistryType_Name as \"RegistryType_Name\",
			coalesce(R.Registry_Sum, 0.00) as \"Registry_Sum\",
			coalesce(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
			PT.PayType_Name as \"PayType_Name\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			coalesce(R.Registry_RecordCount, 0) as \"Registry_Count\",
			coalesce('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:'
				|| CAST(R.Registry_id as varchar)
				|| '});''>'||RegistryCheckStatus.RegistryCheckStatus_Name
				||'</a>',''
			) as \"RegistryCheckStatus_Name\",
			to_char(R.Registry_updDT, 'dd.mm.yyyy') as \"Registry_updDate\"
			-- end select
		from
			-- from
			{$this->scheme}.v_RegistryGroupLink RGL
			inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id -- обычный реестр
			left join v_KatNasel KN on KN.KatNasel_id = R.KatNasel_id
			left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
			left join v_PayType PT on PT.PayType_id = R.PayType_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = R.LpuBuilding_id
			left join RegistryCheckStatus on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			-- end from
		where
			-- where
			RGL.Registry_pid = :Registry_id
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
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');

			$count = count($response['data']);
			if (intval($data['start']) != 0 || $count >= intval($data['limit'])) { // считаем каунт только если записей не меньше, чем лимит или не первая страничка грузится
				$result_count = $this->db->query(getCountSQLPH($query), $data);

				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			}

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
			select distinct
				r.RegistryType_id as \"RegistryType_id\"
			from {$this->scheme}.v_RegistryGroupLink rgl
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_pid
				and r.RegistryType_id in (1,2,6,7,9,11,12,14,15,16)
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
	 * Действия, выполняемые перед удалением реестра из очереди
	 */
	public function deleteRegistryGroupLink($data) {
		$query = "
			delete {$this->scheme}.RegistryGroupLink
			where Registry_pid = :Registry_pid
		";
		$this->db->query($query, array(
			'Registry_pid' => $data['Registry_pid']
		));

		return true;
	}

	/**
	 *	Получение данных для выгрузки объединенного реестра в XML
	 */
	function loadRegistrySCHETForXmlUsing($data)
	{
		$dbreg = $this->load->database('registry1251', true); // получаем коннект к БД с кодировкой windows-1251

		$postfix = '';
		if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
			$postfix = '_bud';
		}

		$p_schet = $this->scheme . ".p_Registry_expScet" . $postfix;

		// шапка
		$query = "
			select * from {$p_schet}( Registry_id := ?)
		";

		$result = $dbreg->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');
			if (!empty($header[0])) {
				return array($header[0]);
			}
		}

		return false;
	}

	/**
	 *	Получение списка случаев для объединенного реестра
	 */
	public function loadUnionRegistryData($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( (isset($data['start']) && (isset($data['limit']))) && (!(($data['start'] >= 0) && ($data['limit'] >= 0))) ) {
			return false;
		}

		$fieldList = array();
		$filterList = array("1 = 1");
		$joinList = array();
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		);

		if ( !empty($data['Person_id']) ) {
			$filterList[] = "RD.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
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

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "RD.Person_SurName ilike :Person_SurName";
			$params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "RD.Person_FirName ilike :Person_FirName";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "RD.Person_SecName ilike :Person_SecName";
			$params['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}

		if( !empty($data['Polis_Num']) ) {
			$filterList[] = "RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		if ( !empty($data['Evn_id']) ) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ( !empty($data['filterRecords']) ) {
			if ( $data['filterRecords'] == 2 ) {
				$filterList[] = "coalesce(RD.RegistryData_IsPaid, 1) = 2";
			}
			else if ( $data['filterRecords'] == 3 ) {
				$filterList[] = "coalesce(RD.RegistryData_IsPaid, 1) = 1";
			}
		}

		$query = "
			-- addit with
			with RD (
				Evn_id,
				Evn_rid,
				EvnClass_id,
				CmpCloseCard_id,
				CmpCallCardInputType_id,
				DispClass_id,
				Person_id,
				Registry_id,
				Evn_disDate,
				Evn_setDate,
				RegistryType_id,
				Server_id,
				needReform,
				checkReform,
				timeReform,
				RegistryData_IsPaid,
				RegistryData_KdFact,
				RegistryData_deleted,
				NumCard,
				Person_FIO,
				Person_BirthDay,
				Person_IsBDZ,
				LpuSection_id,
				LpuSection_name,
				MedPersonal_Fio,
				RegistryData_Tariff,
				RegistryData_KdPay,
				RegistryData_KdPlan,
				RegistryData_ItogSum,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				Polis_Num,
				MedPersonal_id,
				IsGroupEvn,
				Evn_IsArchive
			) as (
				select
					RDE.Evn_id,
					RDE.Evn_rid,
					RDE.EvnClass_id,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					null as DispClass_id, 
					RDE.Person_id,
					RDE.Registry_id,
					RDE.Evn_disDate,
					RDE.Evn_setDate,
					RDE.RegistryType_id,
					RDE.Server_id,
					RDE.needReform,
					RDE.checkReform,
					RDE.timeReform,
					RDE.RegistryData_IsPaid,
					RDE.RegistryData_KdFact,
					RDE.RegistryData_deleted,
					RDE.NumCard,
					RDE.Person_FIO,
					RDE.Person_BirthDay,
					RDE.Person_IsBDZ,
					RDE.LpuSection_id,
					RDE.LpuSection_name,
					RDE.MedPersonal_Fio,
					RDE.RegistryData_Tariff,
					RDE.RegistryData_KdPay,
					RDE.RegistryData_KdPlan,
					RDE.RegistryData_ItogSum,
					RDE.Person_SurName,
					RDE.Person_FirName,
					RDE.Person_SecName,
					RDE.Polis_Num,
					RDE.MedPersonal_id,
					null as IsGroupEvn,
					e.Evn_IsArchive
				from
					{$this->scheme}.v_RegistryData RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					left join v_Evn e on e.Evn_id = RDE.Evn_id
				where
					RGL.Registry_pid = :Registry_id

				union all

				select
					RDE.Evn_id,
					RDE.Evn_rid,
					RDE.EvnClass_id,
					CCLC.CmpCloseCard_id,
					CCC.CmpCallCardInputType_id,
					null as DispClass_id, 
					RDE.Person_id,
					RDE.Registry_id,
					RDE.Evn_disDate,
					RDE.Evn_setDate,
					RDE.RegistryType_id,
					RDE.Server_id,
					RDE.needReform,
					RDE.checkReform,
					RDE.timeReform,
					RDE.RegistryData_IsPaid,
					RDE.RegistryData_KdFact,
					RDE.RegistryData_deleted,
					RDE.NumCard,
					RDE.Person_FIO,
					RDE.Person_BirthDay,
					RDE.Person_IsBDZ,
					RDE.LpuSection_id,
					RDE.LpuSection_name,
					RDE.MedPersonal_Fio,
					RDE.RegistryData_Tariff,
					RDE.RegistryData_KdPay,
					RDE.RegistryData_KdPlan,
					RDE.RegistryData_ItogSum,
					RDE.Person_SurName,
					RDE.Person_FirName,
					RDE.Person_SecName,
					RDE.Polis_Num,
					RDE.MedPersonal_id,
					null as IsGroupEvn,
					CCC.CmpCallCard_IsArchive as Evn_IsArchive
				from
					{$this->scheme}.v_RegistryDataCmp RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					left join v_CmpCallCard CCC on CCC.CmpCallCard_id = RDE.Evn_id
					left join v_CmpCloseCard CCLC on CCLC.CmpCallCard_id = CCC.CmpCallCard_id
				where
					RGL.Registry_pid = :Registry_id
			)
			-- end addit with

			Select
				-- select
				 RD.Evn_id as \"Evn_id\"
				,RD.Evn_rid as \"Evn_rid\"
				,RD.EvnClass_id as \"EvnClass_id\"
				,RD.CmpCloseCard_id as \"CmpCloseCard_id\"
				,RD.CmpCallCardInputType_id as \"CmpCallCardInputType_id\"
				,RD.DispClass_id as \"DispClass_id\"
				,RD.Registry_id as \"Registry_id\"
				,RD.RegistryType_id as \"RegistryType_id\"
				,RD.Person_id as \"Person_id\"
				,RD.Server_id as \"Server_id\"
				,PersonEvn.PersonEvn_id as \"PersonEvn_id\"
				-- в реестрах со статусом частично принят помечаем оплаченные случаи
				,case when RCS.RegistryCheckStatus_Code = 3 then coalesce(RD.RegistryData_IsPaid,1) else 0 end as \"RegistryData_IsPaid\"
				,case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\"
				,RD.needReform as \"needReform\"
				,RD.checkReform as \"checkReform\"
				,RD.timeReform as \"timeReform\"
				,case when RD.needReform = 2 and RQ.RegistryQueue_id is not null then 2 else 1 end as \"isNoEdit\"
				,RD.RegistryData_KdFact as \"RegistryData_Uet\"
				,RD.RegistryData_deleted as \"RegistryData_deleted\"
				,RTrim(RD.NumCard) as \"EvnPL_NumCard\"
				,RTrim(RD.Person_FIO) as \"Person_FIO\"
				,to_char(RD.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
				,CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\"
				,RD.LpuSection_id as \"LpuSection_id\"
				,RTrim(RD.LpuSection_name) as \"LpuSection_name\"
				,RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\"
				,to_char(RD.Evn_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\"
				,to_char(RD.Evn_disDate, 'dd.mm.yyyy') as \"Evn_disDate\"
				,RD.RegistryData_Tariff as \"RegistryData_Tariff\"
				,RD.RegistryData_KdPay as \"RegistryData_KdPay\"
				,RD.RegistryData_KdPlan as \"RegistryData_KdPlan\"
				,RD.RegistryData_ItogSum as \"RegistryData_ItogSum\"
				,RegistryErrorTFOMS.ErrTfoms_Count as \"ErrTfoms_Count\"
				,case when coalesce(RD.Evn_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"
				,RD.IsGroupEvn as \"IsGroupEvn\"
				-- end select
			from
				-- from
				RD
				left join v_LpuSection ls on ls.LpuSection_id = RD.LpuSection_id
				left join lateral (
					select
						RDLT.Person_id
					from RegistryDataLgot RDLT
					where RD.Person_id = RDLT.Person_id
						and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
					limit 1
				) RDL on true
				left join {$this->scheme}.RegistryQueue RQ on RQ.Registry_id = RD.Registry_id
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_RegistryCheckStatus RCS on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id
				left join lateral (
					select count(*) as ErrTfoms_Count
					from {$this->scheme}.v_RegistryErrorTFOMS RET
					where RD.Evn_id = RET.Evn_id
						and RD.Registry_id = RET.Registry_id
						and RET.RegistryErrorTFOMSLevel_id = 1
				) RegistryErrorTFOMS on true
				left join lateral (
					select
						PersonEvn_id
					from v_PersonEvn PE
					where RD.Person_id = PE.Person_id
						and PE.PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
					limit 1
				) PersonEvn on true
				-- end from
			where
				-- where
				" . (count($filterList) > 0 ? implode(' and ', $filterList) : "" ) . "
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";
		/*
		 echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		 echo getDebugSql(getCountSQLPH($query), $params);
		 exit;
		*/
		if ( !empty($data['nopaging']) ) {
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}

		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if ( is_object($result_count) ) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];

			if ( $count > 100 ) {
				return array('Error_Msg' => 'Найдено более 100 записей, необходимо указать дополнительный фильтр');
			}
			unset($cnt_arr);
		}
		else {
			$count = 0;
		}

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);

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
	 *	Список ошибок ТФОМС
	 */
	function loadUnionRegistryErrorTFOMS($data)
	{
		set_time_limit(0);
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
			$filter .= " and ps.Person_SurName ilike :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName ilike :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName ilike :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$query = "
			-- addit with
			with RE (
				Evn_id,
				Registry_id,
				RegistryType_id,
				Evn_rid,
				CmpCloseCard_id,
				CmpCallCardInputType_id,
				RegistryData_deleted,
				RegistryErrorTFOMS_id,
				RegistryErrorType_Code,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				RegistryErrorType_id,
				RegistryErrorTFOMSLevel_id,
				pmUser_insID,
				IsGroupEvn
			) as (
				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RE.RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id,
					RE.pmUser_insID,
					null as IsGroupEvn
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
						and RE.Evn_id = RD.Evn_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
				where
					RGL.Registry_pid = :Registry_id
					--and R.RegistryType_id in (2, 16)
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					CCLC.CmpCloseCard_id,
					CCC.CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RE.RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id,
					RE.pmUser_insID,
					null as IsGroupEvn
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
						and RE.Evn_id = RD.Evn_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join v_CmpCallCard CCC on CCC.CmpCallCard_id = RD.Evn_id
					left join v_CmpCloseCard CCLC on CCLC.CmpCallCard_id = CCC.CmpCallCard_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					null as Evn_id,
					R.Registry_id,
					R.RegistryType_id,
					null as Evn_rid,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					null as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id,
					RE.pmUser_insID,
					null as IsGroupEvn
				from
					{$this->scheme}.v_Registry R
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
				where
					R.Registry_id = :Registry_id
					and NULLIF(RE.RegistryErrorTFOMS_IdCase, '') is null
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id
			)
			-- end addit with

		Select
			-- select
			RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
			RE.Registry_id as \"Registry_id\",
			RE.RegistryType_id as \"RegistryType_id\",
			Evn.Evn_rid as \"Evn_rid\",
			RE.Evn_id as \"Evn_id\",
			RE.CmpCloseCard_id as \"CmpCloseCard_id\",
			RE.CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
			Evn.EvnClass_id as \"EvnClass_id\",
			ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
			RegistryErrorType_Name as \"RegistryError_FieldName\",
			RegistryErrorType_Descr || ' (' ||RETF.RegistryErrorTFOMSField_Name || ')' as \"RegistryError_Comment\",
			rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
			ps.Person_id as \"Person_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Server_id as \"Server_id\",
			RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
			RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
			RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
			RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
			coalesce(RE.RegistryData_deleted, 1) as \"RegistryData_deleted\",
			case when RE.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
			retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\",
			'ТФОМС' as \"RegistryErrorTFOMS_Source\",
			RE.IsGroupEvn as \"IsGroupEvn\"
			-- end select
		from
			-- from
			RE
			left join v_pmUserCache puc on puc.pmUser_id = RE.pmUser_insID
			left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
			left join RegistryErrorTFOMSField RETF on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
			left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
			left join RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
 			left join v_RegistryErrorTFOMSLevel retl on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
			-- end from
		where
			-- where
			1=1
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
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params, true);
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');

			$count = count($response['data']);
			if (intval($data['start']) != 0 || $count >= intval($data['limit'])) { // считаем каунт только если записей не меньше, чем лимит или не первая страничка грузится
				$result_count = $this->db->query(getCountSQLPH($query), $params);

				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			}

			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
}