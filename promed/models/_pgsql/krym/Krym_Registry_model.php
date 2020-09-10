<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry_model - модель для работы с таблицей Registry (Крым)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Valery Bondarev
 * @version      17.01.2020
 */
require_once(APPPATH . 'models/_pgsql/Registry_model.php');

class Krym_Registry_model extends Registry_model
{
	public $scheme = "r91";
	public $region = "krym";
	public $Registry_EvnNum = null;
	public $MaxEvnField = 'Evn_rid';

	private $_invalidEvnList = array();
	private $_RegistryEvnNumByNZAP = array();
	private $_RegistryList = array();
	private $_exportTimeStamp = null;

	private $_IDCASE = 0;
	private $_IDSERV = 0;
	private $_N_ZAP = 0;
	private $_SL_ID = 0;

	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *    Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	function SetXmlPackNum($data)
	{
		$query = "

				update {$this->scheme}.Registry
				set Registry_FileNum = (COALESCE((
					select max(Registry_FileNum)
					from {$this->scheme}.v_Registry r
					where Lpu_id = :Lpu_id
						and SUBSTRING(to_char(Registry_endDate, 'yyyymmdd'), 3, 4) = :Registry_endMonth
						and Registry_FileNum is not null
				), 0) + 1)
				where Registry_id = :Registry_id
		";
		$result = $this->db->query($query, $data);

		$packNum = 0;

		if (is_object($result)) {
			$response = $result->result('array');

			if (is_array($response) && count($response) > 0 && !empty($response[0]['packNum'])) {
				$packNum = $response[0]['packNum'];
			}
		}

		return $packNum;
	}

	/**
	 *    Загрузка данных по реестру
	 */
	function loadRegistryData($data)
	{
		if (empty($data['Registry_id']) || empty($data['RegistryType_id'])) {
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$filterAddQueryTemp = null;
		$filterAddQuery = "";
		if (isset($data['Filter'])) {
			$filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);

			if (is_array($filterData)) {

				foreach ($filterData as $column => $value) {

					if (is_array($value)) {
						$r = null;

						foreach ($value as $d) {
							$r .= "'" . trim(toAnsi($d)) . "',";
						}

						if ($column == 'Diag_Code')
							$column = 'D.' . $column;
						elseif ($column == 'EvnPL_NumCard')
							$column = 'RD.NumCard';
						elseif ($column == 'LpuSection_name')
							$column = 'RD.' . $column;
						elseif ($column == 'LpuBuilding_Name')
							$column = 'LB.' . $column;
						elseif ($column == 'Usluga_Code')
							$column = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
						elseif ($column == 'Paid')
							$column = 'RD.Paid_id';
						elseif ($column == 'Evn_id')
							$column = 'RD.Evn_id';
						elseif ($column == 'Evn_ident') {
							$column = 'RD.Evn_id';
						}

						$r = rtrim($r, ',');
						$filterAddQueryTemp[] = $column . ' IN (' . $r . ')';

					}
				}
			}

			if (is_array($filterAddQueryTemp)) {
				$filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
			} else
				$filterAddQuery = "";
		}
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);
		$filter = "(1=1)";
		$join = "";
		if (isset($data['Person_SurName'])) {
			$filter .= " and lower(RD.Person_SurName) like lower(:Person_SurName) ";
			$params['Person_SurName'] = rtrim($data['Person_SurName']) . "%";
		}
		if (isset($data['Person_FirName'])) {
			$filter .= " and lower(RD.Person_FirName) like lower(:Person_FirName) ";
			$params['Person_FirName'] = rtrim($data['Person_FirName']) . "%";
		}
		if (isset($data['Person_SecName'])) {
			$filter .= " and lower(RD.Person_SecName) like lower(:Person_SecName) ";
			$params['Person_SecName'] = rtrim($data['Person_SecName']) . "%";
		}
		if (!empty($data['Polis_Num'])) {
			$filter .= " and RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		if (!empty($data['MedPersonal_id'])) {
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if (!empty($data['Evn_id'])) {
			$filter .= " and RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['filterRecords'])) {
			if ($data['filterRecords'] == 2) {
				$filter .= " and COALESCE(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and COALESCE(RD.RegistryData_IsPaid,1) = 1";
			}
		}

		if (in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes())) {
			$fields = '';
			$select_mes = "'' as \"Mes_Code\",";
			if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
				$source_table = 'v_RegistryDeleted_Data';
			} else {
				$source_table = 'v_' . $this->RegistryDataObject;
				$join .= "left join v_MesOld MOLD on MOLD.Mes_id=RD.MesItog_id";
				$select_mes = "MOLD.Mes_Code || COALESCE(' ' || MOLD.MesOld_Num, '') as \"Mes_Code\",";
			}
			//УЕТ для поликлиники
			if ($data['RegistryType_id'] == 2) {
				$select_uet = "
					case when PMT.PaymedType_Code in (1,12,22,4) then 1 else
					case when PMT.PaymedType_Code=9 then RD.RegistryData_KdPay else
					case when PMT.PaymedType_Code in (10,17) then Cnt.VizitCount else
					case when PMT.PaymedType_Code=23 then Cnt.UslugaCount
					end end end end as \"RegistryData_Uet\",
				";
				$join .= "
					left join lateral (
						select
							count(distinct EvnViz.EvnVizit_id) as VizitCount,
							sum(COALESCE(case when UslugaComplex.UslugaComplex_Code = 'A.18.30.001' then EvnUsluga.EvnUsluga_Kolvo end,0)) as UslugaCount
						from v_EvnVizit EvnViz
							left join v_EvnUsluga EvnUsluga on EvnUsluga.EvnUsluga_pid=EvnViz.EvnVizit_id
							left join UslugaComplex on UslugaComplex.UslugaComplex_id=EvnUsluga.UslugaComplex_id
						where EvnViz.EvnVizit_pid = RD.Evn_rid and EvnViz.Lpu_id = RD.Lpu_id
					) Cnt on true
				";

				$fields .= "case
								when COALESCE(EPL.Lpu_CodeSMO, '') = '' then ''
								when EPL.Lpu_CodeSMO = Lpu.Lpu_f003mcod then 'Да'
								when PolkaAttachLpu.Lpu_Nick is not null then PolkaAttachLpu.Lpu_Nick
								when PolkaAttachLpu.Lpu_Nick is null then 'Нет'
							end as \"attachToMO\",
				";

				//Мо прикрепления
				$join .= "
					left join EvnPL EPL on EPL.EvnPL_id = RD.Evn_rid
					left join lateral
					(
						Select
							Latt.Lpu_Nick
						from
							v_Lpu Latt
						where
							EPL.Lpu_CodeSMO = Latt.Lpu_f003mcod
						limit 1
					) PolkaAttachLpu on true
				";
			} else {
				$select_uet = "RD.RegistryData_KdFact as \"RegistryData_Uet\", ";
			}

			if ($data['RegistryType_id'] == 6) {

				$fields .= "case
								when COALESCE(CCC.Lpu_CodeSMO, '') = '' then ''
								when CCC.Lpu_CodeSMO = Lpu.Lpu_f003mcod then 'Да'
								when CMPAttachLpu.Lpu_Nick is not null then CMPAttachLpu.Lpu_Nick
								when CMPAttachLpu.Lpu_Nick is null then 'Нет'
							end as \"attachToMO\",
				";
				$join .= "
					left join CmpCloseCard CCC on CCC.CmpCloseCard_id = RD.Evn_id
					left join lateral
					(
						Select Lcmp.Lpu_Nick
						from
							v_Lpu Lcmp
						where
							CCC.Lpu_CodeSMO = Lcmp.Lpu_f003mcod
						limit 1
					) CMPAttachLpu on true
				";
			}

			if (in_array($data['RegistryType_id'], array(2, 6))) {
				$join .= " left join lateral ( select Lpu_f003mcod from v_Lpu where Lpu_id = :Lpu_id limit 1) Lpu on true ";
			}

			if (in_array($data['RegistryType_id'], array(7, 9, 12))) {
				$join .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
				$fields .= "epd.DispClass_id as \"DispClass_id\", ";
			}

			if (in_array($data['RegistryType_id'], array(1, 14))) {
				$setDateField = 'RegistryData_ReceiptDate';
			} else {
				$setDateField = 'Evn_setDate';
			}

			if ($data['RegistryType_id'] == 6) {
				$join .= ' left join v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id';
			} else {
				$join .= ' left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id';
			}

			$query = "
				Select
					-- select
					RD.Evn_id as \"Evn_id\",
					RD.Evn_sid as \"Evn_sid\",
					RD.Evn_id as \"Evn_ident\",
					RD.Evn_rid as \"Evn_rid\",
					RD.{$this->MaxEvnField} as \"MaxEvn_id\",
					RD.EvnClass_id as \"EvnClass_id\",
					RD.Registry_id as \"Registry_id\",
					RD.RegistryType_id as \"RegistryType_id\",
					RD.Person_id as \"Person_id\",
					PersonEvn.Server_id as \"Server_id\,
					PersonEvn.PersonEvn_id as \"PersonEvn_id\",
					RD.needReform as \"needReform\", RD.checkReform as \"checkReform\", RD.timeReform as \"timeReform\",
					case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end as \"isNoEdit\",
					RD.RegistryData_deleted as \"RegistryData_deleted\",
					RTrim(RD.NumCard) as \"EvnPL_NumCard\",
					RTrim(RD.Person_FIO) as \"Person_FIO\",
					RTrim(COALESCE(to_char(cast(RD.Person_BirthDay as timestamp),'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
					RD.LpuSection_id as \"LpuSection_id\",
					RTrim(RD.LpuSection_name) as \"LpuSection_name\",
					LB.LpuBuilding_id as \"LpuBuilding_id\",
					rtrim(LB.LpuBuilding_Name) as \"LpuBuilding_Name\",
					RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
					RTrim(COALESCE(to_char(cast(RD.{$setDateField} as timestamp),'dd.mm.yyyy'),'')) as \"EvnVizitPL_setDate\",
					RTrim(COALESCE(to_char(cast(RD.Evn_disDate as timestamp),'dd.mm.yyyy'),'')) as \"Evn_disDate\",
					RD.RegistryData_Tariff as \"RegistryData_Tariff\",
					--RD.RegistryData_KdFact as RegistryData_Uet,
					{$select_uet}
					{$fields}
					{$select_mes}
					RD.RegistryData_KdPay as \"RegistryData_KdPay\",
					RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
					RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
					COALESCE(RegistryError.Err_Count, 0) || COALESCE(RegistryErrorTFOMS.Err_Count, 0) as \"Err_Count\",
					PMT.PayMedType_Code as \"PayMedType_Code\",
					RD.RegistryData_IsPaid as \"RegistryData_IsPaid\",
					RHDCR.RegistryHealDepResType_id as \"RegistryHealDepResType_id\",
					COALESCE(existWarningError.RegistryErrorClass_id, 1) as \"existWarningError\",
					D.Diag_Code as \"Diag_Code\",
					COALESCE(OrgSMO.Orgsmo_f002smocod, '') || ' ' || COALESCE(OrgSMO.OrgSMO_Nick, '') as \"Person_OrgSmo\"
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD
					left join v_PayMedType PMT on PMT.PayMedType_id = RD.PayMedType_id
					left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
					left join v_LpuSection LS on LS.LpuSection_id = RD.LpuSection_id
					left join v_RegistryHealDepCheckRes RHDCR  on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
					left join lateral
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryError RE  where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
					) RegistryError on true
					left join lateral
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET  where RD.Evn_id = RET.{$this->RegistryDataEvnField} and RD.Registry_id = RET.Registry_id
					) RegistryErrorTFOMS on true
					left join lateral
					(
						Select PE.PersonEvn_id, PE.Server_id
						from v_PersonEvn PE
						inner join v_Person_bdz ps  on ps.PersonEvn_id = PE.PersonEvn_id
						where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.{$setDateField})
						order by PE.PersonEvn_insDT desc
						limit 1
					) PersonEvn on true
					left join lateral
					(
						Select  RE.RegistryErrorClass_id
						from {$this->scheme}.v_RegistryError RE where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id and RE.RegistryErrorClass_id = 2
						limit 1
					) existWarningError on true
					left join v_Diag D on RD.Diag_id = D.Diag_id
					left join v_Person_bdz ps on ps.PersonEvn_id = PersonEvn.PersonEvn_id and ps.Server_id = PersonEvn.Server_id
					left join v_Polis pol on pol.Polis_id = ps.Polis_id
					left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = pol.OrgSmo_id
					{$join}
				-- end from
				where
					-- where
					RD.Registry_id=:Registry_id
					and
					{$filter}
					{$filterAddQuery}
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}

		//echo getDebugSQL($query, $params);die;
		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		echo getDebugSql(getCountSQLPH($query), $params);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *    Установка реестра в очередь на формирование
	 *    Возвращает номер в очереди
	 */
	public function saveRegistryQueue($data)
	{
		if (!in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes())) {
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}

		// Сохранение нового реестра
		if (empty($data['Registry_id'])) {
			$data['Registry_IsActive'] = 2;
			$operation = 'insert';
		} else {
			$operation = 'update';
			$data['Registry_IsZNO'] = $this->getFirstResultFromQuery("select Registry_IsZNO as \"Registry_IsZNO\" from {$this->scheme}.v_Registry where Registry_id = :Registry_id limit 1", $data);
		}

		$re = $this->loadRegistryQueue($data);

		if (is_array($re) && (count($re) > 0)) {
			if ($operation == 'update') {
				if ($re[0]['RegistryQueue_Position'] > 0) {
					return array(array('success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
				}
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
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'Registry_IsRepeated' => $data['Registry_IsRepeated'],
			'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
			'Registry_IsZNO' => $data['Registry_IsZNO'],
			'DispClass_id' => $data['DispClass_id'],
			'PayType_id' => $data['PayType_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					RegistryQueue_id as \"RegistryQueue_id\"
				from {$this->scheme}.p_RegistryQueue_ins (
					--RegistryQueue_Position := RegistryQueue_Position output,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					Lpu_id := :Lpu_id,
					OrgRSchet_id := :OrgRSchet_id,
					Registry_begDate := :Registry_begDate,
					Registry_endDate := :Registry_endDate,
					Registry_Num := :Registry_Num,
					Registry_accDate := dbo.tzGetDate(),
					RegistryStatus_id := :RegistryStatus_id,
					Registry_IsRepeated := :Registry_IsRepeated,
					DispClass_id := :DispClass_id,
					PayType_id := :PayType_id,
					Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
					Registry_IsZNO := :Registry_IsZNO,
					pmUser_id := :pmUser_id
				);
			";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$resp = $result->result('array');

			if (!empty($resp[0]['RegistryQueue_id']) && !empty($data['LpuBuilding_id'])) {
				$savedLB = array();
				$LpuBuilding_ids = explode(',', $data['LpuBuilding_id']);
				if (!empty($data['Registry_id'])) {
					// получаем LpuBuilding'и, которые нужно удалить
					$resp_lb = $this->queryResult("
						select
							RegistryLpuBuilding_id as \"RegistryLpuBuilding_id\",
							LpuBuilding_id as \"LpuBuilding_id\"
						from
							{$this->scheme}.v_RegistryLpuBuilding
						where
							Registry_id = :Registry_id
					", array(
						'Registry_id' => $data['Registry_id']
					));

					// удаляем не нужные
					foreach ($resp_lb as $one_lb) {
						if (!in_array($one_lb['LpuBuilding_id'], $LpuBuilding_ids)) {
							$this->db->query("
								select
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from {$this->scheme}.p_RegistryLpuBuilding_del (
									RegistryLpuBuilding_id := :RegistryLpuBuilding_id
								);
							", array(
								'RegistryLpuBuilding_id' => $one_lb['RegistryLpuBuilding_id']
							));
						} else {
							$savedLB[] = $one_lb['LpuBuilding_id'];
						}
					}
				}

				// добавляем нужные
				foreach ($LpuBuilding_ids as $one) {
					if (!in_array($one, $savedLB)) {
						$this->db->query("
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\",
								RegistryLpuBuilding_id as \"RegistryLpuBuilding_id\"
							FROM {$this->scheme}.p_RegistryLpuBuilding_ins (
								Registry_id := :Registry_id,
								LpuBuilding_id := :LpuBuilding_id,
								RegistryQueue_id := :RegistryQueue_id,
								pmUser_id := :pmUser_id
							);
						", array(
							'Registry_id' => $data['Registry_id'],
							'LpuBuilding_id' => $one,
							'RegistryQueue_id' => $resp[0]['RegistryQueue_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}
			}

			return $resp;
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry($data)
	{
		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink
			where Registry_pid = :Registry_id
		";
		$this->db->query($query, array(
			'Registry_id' => $data['id']
		));

		// 2. удаляем сам реестр
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_del (
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id
			);
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['id']
		, 'pmUser_id' => $data['pmUser_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Различные региональные проверки перед переформированием
	 */
	public function checkBeforeSaveRegistryQueue($data)
	{
		$result = parent::checkBeforeSaveRegistryQueue($data);

		if ($result !== true) {
			return $result;
		}

		$query = "
			select
				R.Registry_id as \"Registry_id\"
			from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_pid
			where
				RGL.Registry_id = :Registry_id
				and R.Registry_xmlExportPath = '1'
			limit 1
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$resp = $result->result('array');

			if (count($resp) > 0) {
				return array(array('success' => false, 'Error_Msg' => '<b>По данному реестру формируется выгрузка в XML.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания выгрузки реестра.'));
			}
		}

		return true;
	}

	/**
	 * Сохранение объединённого реестра
	 */
	public function saveUnionRegistry($data)
	{
		// проверка уникальности номера реестра по МО в одном году
		$query = "
			select
				Registry_id as \"Registry_id\"
			from
				{$this->scheme}.v_Registry
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and Registry_Num = :Registry_Num
				and EXTRACT(YEAR FROM Registry_accDate) = EXTRACT(YEAR FROM :Registry_accDate)
				and Registry_id <> COALESCE(:Registry_id, 0)
			limit 1
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
			$data['Registry_IsZNO'] = $this->getFirstResultFromQuery("select Registry_IsZNO as \"Registry_IsZNO\" from {$this->scheme}.v_Registry  where Registry_id = :Registry_id limit 1", $data);
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				Registry_id as \"Registry_id\"
			from {$this->scheme}.{$proc} (
				Registry_id := :Registry_id,
				RegistryType_id := 13,
				RegistryStatus_id := 1,
				Registry_Sum := NULL,
				Registry_IsActive := 2,
				Registry_Num := :Registry_Num,
				Registry_accDate := :Registry_accDate,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				RegistryGroupType_id := :RegistryGroupType_id,
				OrgSMO_id := :OrgSMO_id,
				KatNasel_id := :KatNasel_id,
				Lpu_id := :Lpu_id,
				Registry_IsRepeated := :Registry_IsRepeated,
				Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
				Registry_IsZNO := :Registry_IsZNO,
				pmUser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
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

				// 3. выполняем поиск реестров которые войдут в объединённый
				$registrytypefilter = "";

				switch ($data['RegistryGroupType_id']) {
					case 1:
						if ($data['Registry_begDate'] < '2018-09-25') {
							$registrytypefilter = " and R.RegistryType_id IN (1, 2, 6, 15)";
						} else {
							$registrytypefilter = " and R.RegistryType_id IN (1, 2, 6, 15) and COALESCE(R.Registry_IsZNO, 1) = COALESCE(:Registry_IsZNO, 1)";
						}
						break;
					case 2:
						$registrytypefilter = " and R.RegistryType_id IN (14)";
						break;
					case 3:
						$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and COALESCE(R.Registry_IsOnceInTwoYears, 1) = COALESCE(:Registry_IsOnceInTwoYears, 1)";
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
					case 16:
						$registrytypefilter = " and R.RegistryType_id IN (18)";
						break;
					case 17:
						$registrytypefilter = " and R.RegistryType_id IN (19)";
						break;
					case 27:
						$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 3";
						break;
					case 28:
						$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 4";
						break;
					case 29:
						$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 7";
						break;
					case 30:
						$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 8";
						break;
					case 31:
						$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 10";
						break;
					case 32:
						$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 12";
						break;
				}

				$katNaselFilter = "
					and not exists(
						select t1.RegistryGroupLink_id
						from {$this->scheme}.v_RegistryGroupLink t1
							inner join {$this->scheme}.v_Registry t2 on t2.Registry_id = t1.Registry_pid
						where t1.Registry_id = R.Registry_id
							and t2.KatNasel_id = :KatNasel_id
							and COALESCE(t2.OrgSMO_id, 0) = COALESCE(:OrgSMO_id, 0)
						limit 1
					)
				";
				if (strtotime($data['Registry_begDate']) >= strtotime('25.12.2017')) {
					$katNaselFilter = "";
				}

				$query = "
					select
						R.Registry_id as \"Registry_id\"
					from
						{$this->scheme}.v_Registry R 
					where
						R.RegistryType_id <> 13
						-- https://redmine.swan.perm.ru//issues/113169
						and (R.RegistryType_id = 6 or R.Registry_IsLoadTFOMS = 2)
						and COALESCE(R.Registry_IsRepeated, 1) = COALESCE(:Registry_IsRepeated, 1)
						and R.RegistryStatus_id = 2 -- к оплате
						and R.Lpu_id = :Lpu_id
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and COALESCE(r.PayType_id, (Select PayType_id from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1)) = (Select PayType_id from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1)
						{$katNaselFilter}
						{$registrytypefilter}
				";
				$result_reg = $this->db->query($query, array(
					'Lpu_id' => $data['Lpu_id'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate'],
					'Registry_IsRepeated' => $data['Registry_IsRepeated'],
					'KatNasel_id' => $data['KatNasel_id'],
					'OrgSMO_id' => $data['OrgSMO_id'],
					'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
					'Registry_IsZNO' => $data['Registry_IsZNO'],
				));

				if (is_object($result_reg)) {
					$resp_reg = $result_reg->result('array');

					// 4. сохраняем новые связи
					foreach ($resp_reg as $one_reg) {
						$query = "
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\",
								RegistryGroupLink_id as \"RegistryGroupLink_id\"
							from {$this->scheme}.p_RegistryGroupLink_ins (
								Registry_pid := :Registry_pid,
								Registry_id := :Registry_id,
								pmUser_id := :pmUser_id
							);
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
				COALESCE(MAX(cast(Registry_Num as bigint)), 0) + 1 as \"Registry_Num\"
			from
				{$this->scheme}.v_Registry
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and Registry_Num ~ '^(([-+]?[0-9]+(\.[0-9]+)?)|([-+]?\.[0-9]+))$'
				and COALESCE(
			        STRPOS(
			             Registry_Num
			            ,(
			                SELECT
			                    ( REGEXP_MATCHES(
			                        Registry_Num
			                        ,'(' || REPLACE( REPLACE( TRIM( '%.%', '%' ), '%', '.*?' ), '_', '.' ) || ')'
			                        ,'i'
			                    ) )[ 1 ]
			                LIMIT 1
			            )
			        )
			        ,0
			    ) = 0
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
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
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate,'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(R.Registry_begDate,'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(R.Registry_endDate,'dd.mm.yyyy') as \"Registry_endDate\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.KatNasel_id as \"KatNasel_id\",
				R.OrgSMO_id as \"OrgSMO_id\",
				R.Lpu_id as \"Lpu_id\",
				COALESCE(R.Registry_IsRepeated, 1) as \"Registry_IsRepeated\",
				COALESCE(R.Registry_IsOnceInTwoYears, 1) as \"Registry_IsOnceInTwoYears\",
				COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\"
			from
				{$this->scheme}.v_Registry R
			where
				R.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
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
			select
				-- select
				UR.Registry_id as \"Registry_id\",
				UR.Registry_Num as \"Registry_Num\",
				case when ChildStatus.RegistryStatus_id = 4 then 1 else 0 end AS \"RegistryChildCheckStatus\",
				to_char(UR.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(UR.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(UR.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
				RGT.RegistryGroupType_Name as \"RegistryGroupType_Name\",
				CASE WHEN UR.Registry_IsZNO = 2 THEN 'true' ELSE 'false' END as \"Registry_IsZNO\",
				KN.KatNasel_id as \"KatNasel_id\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				OS.OrgSMO_Nick as \"OrgSMO_Nick\",
				--coalesce(RS.Registry_SumPaid, 0.00) as Registry_SumPaid
				COALESCE(RDSum1.Registry_Sum, 0.00) + COALESCE(RDSum2.Registry_Sum, 0.00) + COALESCE(RDSumN.Registry_Sum, 0.00) + COALESCE(RDSumSmp1.Registry_Sum, 0.00) + COALESCE(RDSumSmp2.Registry_Sum, 0.00)  + COALESCE(RDSumSmpN.Registry_Sum, 0.00) as \"Registry_SumPaid\",
				UR.Registry_xmlExportPath as \"Registry_xmlExportPath\"
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry UR -- объединённый реестр
				left join v_RegistryGroupType RGT on RGT.RegistryGroupType_id = UR.RegistryGroupType_id
				left join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
				left join v_OrgSMO OS on OS.OrgSMO_id = UR.OrgSMO_id
				left join lateral(
					select 
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as Registry_Sum
					from
						{$this->scheme}.v_RegistryData RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
						inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
					where
						RGL.Registry_pid = UR.Registry_id
						and KN.KatNasel_Code = 1 -- Жители области
						and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
						and RD.OrgSmo_id = UR.OrgSmo_id
						and RD.RegistryData_IsPaid = 2
				) RDSum1 on true
				left join lateral(
					select 
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryData RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
						inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
					where
						RGL.Registry_pid = UR.Registry_id
						and KN.KatNasel_Code = 2 -- Иногородние
						and OST.OmsSprTerr_Code > 100
						and OST.OmsSprTerr_Code <> 1135
						and RD.RegistryData_IsPaid = 2
				) RDSum2 on true
				left join lateral(
					select
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as Registry_Sum
					from
						{$this->scheme}.v_RegistryData RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = UR.Registry_id
						and UR.KatNasel_id is null
				) RDSumN on true
				left join lateral(
					select 
						SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryDataCmp RDC
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDC.Registry_id
						inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDC.OmsSprTerr_id
					where
						RGL.Registry_pid = UR.Registry_id
						and KN.KatNasel_Code = 1 -- Жители области
						and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
						and RDC.OrgSmo_id = UR.OrgSmo_id
						and RDC.RegistryData_IsPaid = 2
				) RDSumSmp1 on true
				left join lateral(
					select 
						SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryDataCmp RDC
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDC.Registry_id
						inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDC.OmsSprTerr_id
					where
						RGL.Registry_pid = UR.Registry_id
						and KN.KatNasel_Code = 2 -- Иногородние
						and OST.OmsSprTerr_Code > 100
						and OST.OmsSprTerr_Code <> 1135
						and RDC.RegistryData_IsPaid = 2
				) RDSumSmp2 on true
				LEFT JOIN LATERAL(
					select
						SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as Registry_Sum
					from
						{$this->scheme}.v_RegistryDataCmp RDC
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDC.Registry_id
					where
						RGL.Registry_pid = UR.Registry_id
						and UR.KatNasel_id is null
				) RDSumSmpN on true
				left join lateral(
					select
						SUM(COALESCE(R2.Registry_SumPaid,0)) as Registry_SumPaid
					from {$this->scheme}.v_Registry R2
						inner join {$this->scheme}.v_RegistryGroupLink RGL on R2.Registry_id = RGL.Registry_id
					where
						RGL.Registry_pid = UR.Registry_id
				) RS on true

				left join lateral(
					select 
						R3.RegistryStatus_id
					from {$this->scheme}.v_Registry R3 
						inner join {$this->scheme}.v_RegistryGroupLink RGL on R3.Registry_id = RGL.Registry_id
					where
						RGL.Registry_pid = UR.Registry_id and R3.RegistryStatus_id = 4
					limit 1
				) ChildStatus on true
				-- end from
			where
				-- where
				UR.Lpu_id = :Lpu_id
				and UR.RegistryType_id = 13
				-- end where
			order by
				-- order by
				UR.Registry_endDate DESC,
				UR.Registry_updDT DESC
				-- end order by
		";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;

			foreach ($response['data'] as $key => $row) {
				if (empty($row['Registry_xmlExportPath'])) {
					continue;
				}

				$fileNameParts = explode('/', $row['Registry_xmlExportPath']);

				$response['data'][$key]['Registry_xmlExportPath'] = $fileNameParts[count($fileNameParts) - 1];
			}

			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid($data)
	{
		$query = "
			select 
				-- select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				RST.RegistryStatus_Name as \"RegistryStatus_Name\",
				to_char(R.Registry_accDate,'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(R.Registry_begDate,'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(R.Registry_endDate,'dd.mm.yyyy') as \"Registry_endDate\",
				RT.RegistryType_Name as \"RegistryType_Name\",
				COALESCE(RDSum1.Registry_Sum, 0.00) + COALESCE(RDSum2.Registry_Sum, 0.00) + COALESCE(RDSumN.Registry_Sum, 0.00) + COALESCE(RDSumSmp1.Registry_Sum, 0.00) + COALESCE(RDSumSmp2.Registry_Sum, 0.00) + COALESCE(RDSumSmpN.Registry_Sum, 0.00) as \"Registry_Sum\",
				COALESCE(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
				to_char(R.Registry_updDT, 'dd.mm.yyyy') as \"Registry_updDate\"
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryGroupLink RG
				inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid -- объединенный реестр
				inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id -- обычный реестр
				left join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
				left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
				left join v_RegistryStatus RST on RST.RegistryStatus_id = R.RegistryStatus_id
				left join lateral(
					select 
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryData RD 
						inner join v_OmsSprTerr OST  on OST.OmsSprTerr_id = RD.OmsSprTerr_id
					where
						RD.Registry_id = R.Registry_id
						and KN.KatNasel_Code = 1 -- Жители области
						and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
						and RD.OrgSmo_id = UR.OrgSmo_id
				) RDSum1 on true
				left join lateral(
					select 
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryData RD 
						inner join v_OmsSprTerr OST  on OST.OmsSprTerr_id = RD.OmsSprTerr_id
					where
						RD.Registry_id = R.Registry_id
						and KN.KatNasel_Code = 2 -- Иногородние
						and OST.OmsSprTerr_Code > 100
						and OST.OmsSprTerr_Code <> 1135
				) RDSum2 on true
				left join lateral(
					select 
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryData RD 
					where
						RD.Registry_id = R.Registry_id
						and UR.KatNasel_id is null
				) RDSumN on true
				left join lateral(
					select 
						SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryDataCmp RDC 
						inner join v_OmsSprTerr OST  on OST.OmsSprTerr_id = RDC.OmsSprTerr_id
					where
						RDC.Registry_id = R.Registry_id
						and KN.KatNasel_Code = 1 -- Жители области
						and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
						and RDC.OrgSmo_id = UR.OrgSmo_id
				) RDSumSmp1 on true
				left join lateral(
					select 
						SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryDataCmp RDC 
						inner join v_OmsSprTerr OST  on OST.OmsSprTerr_id = RDC.OmsSprTerr_id
					where
						RDC.Registry_id = R.Registry_id
						and KN.KatNasel_Code = 2 -- Иногородние
						and OST.OmsSprTerr_Code > 100
						and OST.OmsSprTerr_Code <> 1135
				) RDSumSmp2 on true
				left join lateral(
					select 
						SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as Registry_Sum
					from 
						{$this->scheme}.v_RegistryDataCmp RDC 
					where
						RDC.Registry_id = R.Registry_id
						and UR.KatNasel_id is null
				) RDSumSmpN on true
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_pid
				-- end where
			order by
				-- order by
				R.Registry_id
				-- end order by
		";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *    Получение данных для выгрузки реестров в XML
	 */
	public function loadRegistryDataForXmlUsing2018($type, $data, &$Registry_EvnNum, $file_re_data_name, $file_re_pers_data_name, $registry_data_template_body, $person_data_template_body, $registryIsUnion = true)
	{
		$this->textlog->add("Задействовано памяти до выполнения запросов на получение данных: " . (memory_get_usage() / 1024 / 1024) . " MB");

		if (empty($this->_exportTimeStamp)) {
			$this->_exportTimeStamp = time();
		}

		$this->setRegistryParamsByType(array(
			'RegistryType_id' => $type
		));

		$RegistryDataEvnField = $this->RegistryDataEvnField;

		switch ($type) {
			case 1: //stac
				$object = "EvnPS";
				break;

			case 2: //polka
				$object = "EvnPL";
				break;

			case 6: //smp
				$object = "SMP";
				break;

			case 7: //dd
				$object = "EvnPLDD13";
				break;

			case 9: //orp
				$object = "EvnPLOrp13";
				break;

			case 11: //prof
				$object = "EvnPLProf";
				break;

			case 12: //teen inspection
				$object = "EvnPLProfTeen";
				break;

			case 14: //htm
				$object = "EvnHTM";
				break;

			case 15: //parka
				$object = "EvnUslugaPar";
				break;

			case 18: //calcdisp
				$object = "EvnUslugaParDD";
				break;

			case 19: //calcusluga
				$object = "EvnPLUsluga";
				break;

			default:
				return false;
				break;
		}

		$postfix = '_2018';
		if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
			$postfix = '_bud';
		}

		$p_zsl = $this->scheme . ".p_Registry_" . $object . "_expSL" . $postfix;
		$p_vizit = $this->scheme . ".p_Registry_" . $object . "_expVizit" . $postfix;
		$p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl" . $postfix;
		$p_pers = $this->scheme . ".p_Registry_" . $object . "_expPac" . $postfix;

		if (in_array($type, array(1, 14))) {
			$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2";
		}

		if (
			!in_array($data['PayType_SysNick'], array('bud', 'fbud'))
			&& (
				in_array($type, array(2, 14))
				|| (in_array($type, array(1)) && !$data['isVzaimoraschet'])
				|| (in_array($type, array(1, 19)) && $data['registryIsAfter20180925'] !== true)
			)
		) {

			$p_bdiag = $this->scheme . ".p_Registry_" . $object . "_expBDIAG" . $postfix;
			$p_bprot = $this->scheme . ".p_Registry_" . $object . "_expBPROT" . $postfix;
			$p_napr = $this->scheme . ".p_Registry_" . $object . "_expNAPR" . $postfix;
			$p_onkousl = $this->scheme . ".p_Registry_" . $object . "_expONKOUSL" . $postfix;
		}

		if (!in_array($data['PayType_SysNick'], array('bud', 'fbud')) && in_array($type, array(1, 2, 14)) && $data['registryIsAfter20180925'] === true) {
			$p_cons = $this->scheme . ".p_Registry_" . $object . "_expCONS" . $postfix;
			$p_lek_pr = $this->scheme . ".p_Registry_" . $object . "_expLEK_PR" . $postfix;
		}

		if (!in_array($data['PayType_SysNick'], array('bud', 'fbud')) && in_array($type, array(1)) && $data['registryIsAfter20181225'] === true) {
			$p_crit = $this->scheme . ".p_Registry_" . $object . "_expCRIT" . $postfix;
		}

		if (in_array($type, array(2, 7, 9, 11, 12))) {
			$p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2" . $postfix;
		}

		if (in_array($type, array(1, 14))) {
			$p_ds3 = $this->scheme . ".p_Registry_" . $object . "_expDS3";
		}

		if (in_array($type, array(1, 6))) {
			$p_ls = $this->scheme . ".p_Registry_" . $object . "_expLS";
		}

		if (in_array($type, array(7, 9, 11, 12))) {
			$p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ" . $postfix;
			if ($postfix == '_2018') {
				$p_naz_params = 'Registry_id := :Registry_id';
			} else {
				$p_naz_params = 'Registry_id := :Registry_id, KatNasel_id := :KatNasel_id, OrgSMO_id := :OrgSMO_id';
			}
		}

		if (in_array($type, array(1))) {
			$p_kslp = $this->scheme . ".p_Registry_{$object}_expKSLP";
		}

		if (in_array($type, array(1)) && $data['registryIsAfter20181225'] === false) {
			$p_dkk2 = $this->scheme . ".p_Registry_{$object}_expDKK2";
		}

		// люди
		$query = "select * from {$p_pers} (Registry_id := :Registry_id)";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_pac = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_pac)) {
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// Законченные случаи
		$query = "select * from {$p_zsl} (Registry_id := :Registry_id)";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_zsl = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_zsl)) {
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// посещения
		$query = "select * from {$p_vizit} (Registry_id := :Registry_id)";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_sluch = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_sluch)) {
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// услуги
		$query = "select * from {$p_usl} (Registry_id := :Registry_id)";
		$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
		$result_usl = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		$this->textlog->add('Выполнено');
		if (!is_object($result_usl)) {
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса: " . (memory_get_usage() / 1024 / 1024) . " MB");

		$BDIAG = array();
		$BPROT = array();
		$CONS = array();
		$CRIT = array();
		$DKK2 = array();
		$DS2 = array();
		$DS3 = array();
		$KSLP = array();
		$LEK_PR = array();
		$LS = array();
		$NAPR = array();
		$NAZ = array();
		$ONKOUSL = array();
		$PACIENT = array();
		$USL = array();
		$ZAP = array();
		$ZSL = array();

		$rowNumArray = [];
		$rowNumInsertQuery = "

				insert into {$this->scheme}.RegistryDataRowNum (Registry_id, {$this->RegistryDataEvnField}, RegistryData_RowNum, RegistryDataRowNum_Session)
				values
				{values_array}
				returning null as Error_Code, null as Error_Msg
		";

		$netValue = toAnsi('НЕТ', true);

		// диагностический блок (BDIAG)
		if (!empty($p_bdiag)) {
			$query = "select * from {$p_bdiag} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_bdiag = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_bdiag)) {
				return false;
			}
			while ($row = $result_bdiag->_fetch_assoc()) {
				if (!isset($BDIAG[$row['Evn_id']])) {
					$BDIAG[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$BDIAG[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива BDIAG: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// сведения об имеющихся противопоказаниях и отказах (BPROT)
		if (!empty($p_bprot)) {
			$query = "select * from {$p_bprot} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_bprot = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_bprot)) {
				return false;
			}
			while ($row = $result_bprot->_fetch_assoc()) {
				if (!isset($BPROT[$row['Evn_id']])) {
					$BPROT[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$BPROT[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива BPROT: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// сведения о проведении консилиума (CRIT)
		if (!empty($p_crit)) {
			$query = "select * from {$p_crit} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_cons = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_cons)) {
				return false;
			}
			while ($row = $result_cons->_fetch_assoc()) {
				if (!isset($CRIT[$row['Evn_id']])) {
					$CRIT[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$CRIT[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива CRIT: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// сведения о проведении консилиума (CONS)
		if (!empty($p_cons)) {
			$query = "select * from {$p_cons} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_cons = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_cons)) {
				return false;
			}
			while ($row = $result_cons->_fetch_assoc()) {
				if (!isset($CONS[$row['Evn_id']])) {
					$CONS[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$CONS[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива CONS: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// дополнительные классификационные критерии (DKK2)
		if (!empty($p_dkk2)) {
			$query = "select * from {$p_dkk2} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_dkk2 = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_dkk2)) {
				return false;
			}
			while ($row = $result_dkk2->_fetch_assoc()) {
				if (!isset($DKK2[$row['Evn_id']])) {
					$DKK2[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$DKK2[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива DKK2: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// диагнозы (DS2)
		if (!empty($p_ds2)) {
			$query = "select * from {$p_ds2} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds2 = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_ds2)) {
				return false;
			}
			while ($row = $result_ds2->_fetch_assoc()) {
				if (!isset($DS2[$row['Evn_id']])) {
					$DS2[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$DS2[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива DS2: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// диагнозы (DS3)
		if (!empty($p_ds3)) {
			$query = "select * from {$p_ds3} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ds3 = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_ds3)) {
				return false;
			}
			while ($row = $result_ds3->_fetch_assoc()) {
				if (!isset($DS3[$row['Evn_id']])) {
					$DS3[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$DS3[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива DS3: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// ЛС (LS)
		if (!empty($p_ls)) {
			$query = "select * from {$p_ls} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_ls = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_ls)) {
				return false;
			}
			while ($row = $result_ls->_fetch_assoc()) {
				if (!isset($LS[$row['Evn_id']])) {
					$LS[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$LS[$row['Evn_id']][] = array('LS_ID' => $row['LS_ID'], 'LS_FORM' => $row['LS_FORM'], 'LS_ED_COL' => $row['LS_ED_COL']);
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива LS: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// направления (NAPR)
		if (!empty($p_napr)) {
			$query = "select * from {$p_napr} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($query_result)) {
				return false;
			}
			while ($row = $query_result->_fetch_assoc()) {
				if (!isset($NAPR[$row['Evn_id']])) {
					$NAPR[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$NAPR[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива NAPR: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// назначения (NAZ)
		if (!empty($p_naz)) {
			$query = "select * from {$p_naz} ( {$p_naz_params})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'], 'KatNasel_id' => $data['KatNasel_id'], 'OrgSMO_id' => $data['OrgSMO_id'])));
			$result_naz = $this->db->query($query, array('Registry_id' => $data['Registry_id'], 'KatNasel_id' => $data['KatNasel_id'], 'OrgSMO_id' => $data['OrgSMO_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($result_naz)) {
				return false;
			}
			while ($row = $result_naz->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$NAZ[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива NAZ: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// Выгружаем КСЛП
		if (!empty($p_kslp)) {
			$query = "select * from {$p_kslp} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$result_kslp = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');

			if (!is_object($result_kslp)) {
				return false;
			}

			// Формируем массив KSLP
			while ($row = $result_kslp->_fetch_assoc()) {
				if (!isset($KSLP[$row['Evn_id']])) {
					$KSLP[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$KSLP[$row['Evn_id']][] = array('Z_SL_KOEF' => $row['Z_SL'], 'IDSL' => $row['IDSL']);
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива KSLP: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if (!empty($p_onkousl)) {
			$query = "select * from {$p_onkousl} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($query_result)) {
				return false;
			}
			while ($row = $query_result->_fetch_assoc()) {
				if (!isset($ONKOUSL[$row['Evn_id']])) {
					$ONKOUSL[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$row['LEK_PR_DATA'] = array();

				$ONKOUSL[$row['Evn_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива ONKOUSL: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
		if (!empty($p_lek_pr)) {
			$query = "select * from {$p_lek_pr} (Registry_id := :Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, array('Registry_id' => $data['Registry_id'])));
			$query_result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			$this->textlog->add('Выполнено');
			if (!is_object($query_result)) {
				return false;
			}
			while ($row = $query_result->_fetch_assoc()) {
				if (!isset($LEK_PR[$row['EvnUsluga_id']])) {
					$LEK_PR[$row['EvnUsluga_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}

			$this->textlog->add("Задействовано памяти после выполнения запроса и формирования массива LEK_PR: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		// Формируем массив пациентов
		while ($pers = $result_pac->_fetch_assoc()) {
			if (!empty($pers['Evn_rid'])) {
				$pers['DOST'] = array();
				$pers['DOST_P'] = array();

				if (!in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
					if ($pers['NOVOR'] != '0') {
						if (empty($pers['FAM_P'])) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => 2);
						}

						if (empty($pers['IM_P'])) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => 3);
						}

						if (empty($pers['OT_P']) || strtoupper($pers['OT_P']) == $netValue) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => 1);
						}
					} else {
						if (empty($pers['FAM'])) {
							$pers['DOST'][] = array('DOST_VAL' => 2);
						}

						if (empty($pers['IM'])) {
							$pers['DOST'][] = array('DOST_VAL' => 3);
						}

						if (empty($pers['OT']) || strtoupper($pers['OT']) == $netValue) {
							$pers['DOST'][] = array('DOST_VAL' => 1);
						}
					}
				}

				array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);

				$PACIENT[$pers['Evn_rid']] = $pers;
			}
		}

		$this->textlog->add("Задействовано памяти после формирования массива PACIENT: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// Массив законченных случаев
		while ($expSL = $result_zsl->_fetch_assoc()) {
			if (!isset($ZSL[$expSL['Evn_rid']])) {
				$ZSL[$expSL['Evn_rid']] = array();
			}

			array_walk_recursive($expSL, 'ConvertFromUTF8ToWin1251', true);

			$ZSL[$expSL['Evn_rid']][] = $expSL;
		}

		$this->textlog->add("Задействовано памяти после формирования массива ZSL: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// Формируем массив услуг
		while ($usluga = $result_usl->_fetch_assoc()) {
			if (!isset($USL[$usluga['Evn_id']])) {
				$USL[$usluga['Evn_id']] = array();
			}

			array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);

			$USL[$usluga['Evn_id']][] = $usluga;
		}

		$this->textlog->add("Задействовано памяти после формирования массива USL: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
		$altKeys = array(
			'LPU_USL' => 'LPU'
		, 'LPU_1_USL' => 'LPU_1'
		, 'PODR_USL' => 'PODR'
		, 'PROFIL_USL' => 'PROFIL'
		, 'PROFIL_K_USL' => 'PROFIL_K'
		, 'DET_USL' => 'DET'
		, 'TARIF_USL' => 'TARIF'
		, 'PRVS_USL' => 'PRVS'
		, 'P_OTK_USL' => 'P_OTK'
		, 'Z_SL_KOEF' => 'Z_SL'
		);

		foreach ($PACIENT as $key => $value) {
			$ZAP[$key]['PACIENT'] = array($value);
		}

		$this->textlog->add("Задействовано памяти после формирования массива ZAP: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// Пишем в файл с перс. данными
		$toFile = array();
		foreach ($PACIENT as $onepac) {
			$toFile[] = $onepac;
			if (count($toFile) >= 1000) {
				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $toFile), true);
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
			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, array('PACIENT' => $toFile), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($toFile);
		}

		unset($PACIENT);
		unset($toFile);

		$this->textlog->add("Задействовано памяти после записи данных в файл с перс. данными и удаления массива PACIENT: " . (memory_get_usage() / 1024 / 1024) . " MB");

		foreach ($ZSL as $key => $value) {
			$this->_IDCASE++;
			$this->_N_ZAP++;

			$value[0]['OS_SLUCH'] = array();
			if (!empty($ZAP[$key]['PACIENT'][0]['OS_SLUCH'])) {
				$value[0]['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $ZAP[$key]['PACIENT'][0]['OS_SLUCH']);
				unset($ZAP[$key]['PACIENT'][0]['OS_SLUCH']);
			}
			if (!empty($ZAP[$key]['PACIENT'][0]['OS_SLUCH1'])) {
				$value[0]['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $ZAP[$key]['PACIENT'][0]['OS_SLUCH1']);
				unset($ZAP[$key]['PACIENT'][0]['OS_SLUCH1']);
			}
			$value[0]['VNOV_M'] = null;
			if (!empty($ZAP[$key]['PACIENT'][0]['VNOV_M'])) {
				$value[0]['VNOV_M'] = $ZAP[$key]['PACIENT'][0]['VNOV_M'];
				unset($ZAP[$key]['PACIENT'][0]['VNOV_M']);
			}
			if (!empty($ZAP[$key]['PACIENT'][0]['SOC_STATUS'])) {
				$value[0]['SOC_STATUS'] = $ZAP[$key]['PACIENT'][0]['SOC_STATUS'];
			}

			$value[0]['IDCASE'] = $this->_IDCASE;

			$ZAP[$key]['N_ZAP'] = $this->_N_ZAP;
			if (isset($value[0]['PR_NOV'])) {
				$ZAP[$key]['PR_NOV'] = $value[0]['PR_NOV'];
			} else if (!isset($ZAP[$key]['PR_NOV'])) {
				$ZAP[$key]['PR_NOV'] = null;
			}

			$ZAP[$key]['Z_SL'] = $value;
			unset($ZSL[$key]);
		}

		unset($ZSL);

		$this->textlog->add("Задействовано памяти после добавления блоков Z_SL в ZAP и удаления массива ZSL: " . (memory_get_usage() / 1024 / 1024) . " MB");

		$SD_Z = 0;

		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');
		$SANK_FIELDS = array('S_CODE', 'S_SUM', 'S_TIP', 'S_OSN', 'DATE_ACT', 'NUM_ACT', 'CODE_EXP', 'S_COM', 'S_IST', 'S_VID', 'S_YEAR', 'S_MONTH');

		$this->textlog->add('Начинаем обработку случаев');

		while ($visit = $result_sluch->_fetch_assoc()) {
			if (empty($visit['Evn_id'])) {
				continue;
			}

			array_walk_recursive($visit, 'ConvertFromUTF8ToWin1251', true);

			$this->_SL_ID++;
			$visit['SL_ID'] = $this->_SL_ID;

			$key = $visit['Evn_id'];

			if (!array_key_exists('PACIENT', $ZAP[$visit['Evn_rid']])) {
				$Registry_id = ($registryIsUnion == true ? $visit['registry_sid'] : $data['Registry_id']);

				if (!array_key_exists($Registry_id, $this->_invalidEvnList)) {
					$Registry_Num = $this->getFirstResultFromQuery("select Registry_Num as \"Registry_Num\" from {$this->scheme}.v_Registry  where Registry_id = :Registry_id limit 1", array('Registry_id' => $Registry_id));

					$this->_invalidEvnList[$Registry_id] = array();
					$this->_invalidEvnList[$Registry_id]['RegistryType_id'] = $type;
					$this->_invalidEvnList[$Registry_id]['Registry_Num'] = $Registry_Num;
					$this->_invalidEvnList[$Registry_id]['EvnList'] = array();
				}

				$this->_invalidEvnList[$Registry_id]['EvnList'][] = $key;
			}

			// При наличии ошибок в структуре файла нет смысла в дальнейшей работе с данными
			if (count($this->_invalidEvnList) > 0) {
				continue;
			}

			if (isset($DKK2[$key])) {
				$visit['DKK2_DATA'] = $DKK2[$key];
				unset($DKK2[$key]);

				if (array_key_exists('DKK2', $visit)) {
					unset($visit['DKK2']);
				}
			} else if (!empty($visit['DKK2'])) {
				$visit['DKK2_DATA'] = array(array('DKK2' => $visit['DKK2']));
				unset($visit['DKK2']);
			} else {
				$visit['DKK2_DATA'] = array();
			}

			if (isset($CRIT[$key])) {
				$visit['CRIT_DATA'] = $CRIT[$key];
				unset($CRIT[$key]);

				if (array_key_exists('CRIT', $visit)) {
					unset($visit['CRIT']);
				}
			} else if (!empty($visit['CRIT'])) {
				$visit['CRIT_DATA'] = array(array('CRIT' => $visit['CRIT']));
				unset($visit['CRIT']);
			} else {
				$visit['CRIT_DATA'] = array();
			}

			if (isset($DS2[$key])) {
				$visit['DS2_DATA'] = $DS2[$key];
				unset($DS2[$key]);

				if (array_key_exists('DS2', $visit)) {
					unset($visit['DS2']);
				}
			} else if (!empty($visit['DS2'])) {
				$visit['DS2_DATA'] = array(array('DS2' => $visit['DS2']));
				unset($visit['DS2']);
			} else {
				$visit['DS2_DATA'] = array();
				$visit['DS2_N_DATA'] = array();
			}

			if (isset($DS3[$key])) {
				$visit['DS3_DATA'] = $DS3[$key];
				unset($DS3[$key]);

				if (array_key_exists('DS3', $visit)) {
					unset($visit['DS3']);
				}
			} else if (!empty($visit['DS3'])) {
				$visit['DS3_DATA'] = array(array('DS3' => $visit['DS3']));
				unset($visit['DS3']);
			} else {
				$visit['DS3_DATA'] = array();
			}

			$visit['CONS_DATA'] = array();
			$visit['NAPR_DATA'] = array();
			$visit['ONK_SL_DATA'] = array();
			$ONK_SL_DATA = array();

			$onkDS2 = false;

			if (isset($visit['DS2_DATA']) && count($visit['DS2_DATA']) > 0) {
				foreach ($visit['DS2_DATA'] as $ds2) {
					if (empty($ds2['DS2'])) {
						continue;
					}

					$code = substr($ds2['DS2'], 0, 3);

					if (($code >= 'C00' && $code <= 'C80') || $code == 'C97') {
						$onkDS2 = true;
					}
				}
			}

			if (
				(empty($visit['DS_ONK']) || $visit['DS_ONK'] != 1)
				//&& (empty($visit['P_CEL']) || $visit['P_CEL'] != '1.3')
				&& (empty($visit['USL_OK']) || $visit['USL_OK'] != 4)
				&& (empty($visit['REAB']) || $visit['REAB'] != 1)
				&& !empty($visit['DS1'])
				&& (
					substr($visit['DS1'], 0, 1) == 'C'
					|| ($data['registryIsAfter20181225'] === true && substr($visit['DS1'], 0, 3) >= 'D00' && substr($visit['DS1'], 0, 3) <= 'D09')
					|| ($visit['DS1'] == 'D70' && $onkDS2 == true)
				)
			) {
				$hasONKOSLData = false;
				$ONK_SL_DATA['B_DIAG_DATA'] = array();
				$ONK_SL_DATA['B_PROT_DATA'] = array();
				$ONK_SL_DATA['ONK_USL_DATA'] = array();

				foreach ($ONK_SL_FIELDS as $field) {
					if (isset($visit[$field]) && strlen((string)$visit[$field]) > 0) {
						$hasONKOSLData = true;
						$ONK_SL_DATA[$field] = $visit[$field];
					} else {
						$ONK_SL_DATA[$field] = null;
					}

					if (array_key_exists($field, $visit)) {
						unset($visit[$field]);
					}
				}

				if (isset($BDIAG[$key])) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$key];
					unset($BDIAG[$key]);
				}

				if (isset($BPROT[$key])) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$key];
					unset($BPROT[$key]);
				}

				if (isset($ONKOUSL[$key])) {
					$hasONKOSLData = true;

					// LEK_PR
					if ($data['registryIsAfter20180925'] === true) {
						// Записи группировать по REGNUM
						foreach ($ONKOUSL[$key] as $recKey => $recData) {
							if (isset($LEK_PR[$recData['EvnUsluga_id']]) && $recData['USL_TIP'] == 2) {
								$LEK_PR_DATA = array();

								foreach ($LEK_PR[$recData['EvnUsluga_id']] as $row) {
									if (!isset($LEK_PR_DATA[$row['REGNUM']])) {
										$LEK_PR_DATA[$row['REGNUM']] = array(
											'REGNUM' => $row['REGNUM'],
											'CODE_SH' => (!empty($row['CODE_SH']) ? $row['CODE_SH'] : null),
											'DATE_INJ_DATA' => array(),
										);
									}

									$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
								}

								$ONKOUSL[$key][$recKey]['LEK_PR_DATA'] = $LEK_PR_DATA;
							}
						}
					}

					$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$key];
					unset($ONKOUSL[$key]);
				} // @task https://redmine.swan-it.ru/issues/149235
				else if ($hasONKOSLData === true && $data['registryIsAfter20180925'] === true && $data['Registry_IsZNO'] == 2) {
					$ONK_SL_DATA['ONK_USL_DATA'] = array(
						array(
							'USL_TIP' => 5,
							'HIR_TIP' => null,
							'LEK_TIP_L' => null,
							'LEK_TIP_V' => null,
							'LEK_PR_DATA' => array(),
							'LUCH_TIP' => null,
							'PPTR' => null,
						)
					);
				}

				if ($hasONKOSLData == false) {
					$ONK_SL_DATA = array();
				}
			}

			if (count($ONK_SL_DATA) > 0) {
				$visit['ONK_SL_DATA'][] = $ONK_SL_DATA;
			}

			// NAPR
			if (
				isset($NAPR[$key])
			) {
				$visit['NAPR_DATA'] = $NAPR[$key];
				unset($NAPR[$key]);
			}

			// CONS
			if (
				isset($CONS[$key])
				&& $data['registryIsAfter20180925'] === true
				&& (
					(!empty($visit['DS_ONK']) && $visit['DS_ONK'] == 1)
					|| (
						!empty($visit['DS1'])
						&& (
							substr($visit['DS1'], 0, 1) == 'C'
							|| ($data['registryIsAfter20181225'] === true && substr($visit['DS1'], 0, 3) >= 'D00' && substr($visit['DS1'], 0, 3) <= 'D09')
							|| ($visit['DS1'] == 'D70' && $onkDS2 == true)
						)
					)
				)
			) {
				$visit['CONS_DATA'] = $CONS[$key];
				unset($CONS[$key]);
			}

			$visit['SL_KOEF_DATA'] = array();
			if (isset($KSLP[$key])) {
				$visit['SL_KOEF_DATA'] = $KSLP[$key];
				unset($KSLP[$key]);
			}

			$visit['SANK'] = array();
			$SANK_DATA = array();
			foreach ($SANK_FIELDS as $sankfield) {
				if (!empty($visit[$sankfield])) {
					$SANK_DATA[$sankfield] = $visit[$sankfield];
				}
				unset($visit[$sankfield]);
			}
			if (count($SANK_DATA) > 0) {
				$visit['SANK'][] = $SANK_DATA;
			}

			// Привязываем услуги
			if (isset($USL[$key])) {
				// Услуга осмотра должна выгружаться последней
				// @task https://redmine.swan-it.ru/issues/148456
				foreach ($USL[$key] as $idx => $oneUsl) {
					if (!empty($oneUsl['IsVizitCode']) && $oneUsl['IsVizitCode'] == 2) {
						unset($USL[$key][$idx]);
						$USL[$key][] = $oneUsl;
					}
				}
				$visit['USL'] = $USL[$key];
				unset($USL[$key]);
			} else {
				$visit['USL'] = $this->getEmptyUslugaXmlRow();
			}

			if (isset($LS[$key])) {
				$visit['LS_DATA'] = $LS[$key];
				unset($LS[$key]);
			} else {
				$visit['LS_DATA'] = array();
				//$visit['LS_DATA'][] = array('LS_ID' => null, 'LS_FORM' => null, 'LS_ED_COL' => null);
			}

			$visit['NAZ_DATA'] = array();
			if (isset($NAZ[$key])) {
				$visit['NAZ_DATA'] = $NAZ[$key];
				unset($NAZ[$key]);
			}

			if (!isset($ZAP[$visit['Evn_rid']]['Z_SL'][0]['SL'])) {
				$ZAP[$visit['Evn_rid']]['Z_SL'][0]['SL'] = array();
			}
			$ZAP[$visit['Evn_rid']]['Z_SL'][0]['SL'][] = $visit;

			//$Registry_EvnNum[$visit['SL_ID']] = $key;
			$Registry_EvnNum[$visit['SL_ID']] = array(
				'r' => ($registryIsUnion == true ? $visit['registry_sid'] : $data['Registry_id']),
				'e' => $key,
				'n' => $ZAP[$visit['Evn_rid']]['N_ZAP'],
			);

			$rowNumArray[] = [
				'Registry_id' => ($registryIsUnion == true ? $visit['registry_sid'] : $data['Registry_id']),
				'Evn_id' => $key,
				'RegistryData_RowNum' => $visit['SL_ID'],
			];

			if (count($rowNumArray) == 1000) {
				// пишем связку номеров записей и случаев в отдельную таблицу
				$rowNumInsertQueryBody = '';

				foreach ($rowNumArray as $row) {
					$rowNumInsertQueryBody .= "({$row['Registry_id']}, {$row['Evn_id']}, {$row['RegistryData_RowNum']}, {$this->_exportTimeStamp}),";
				}

				$this->textlog->add("Добавляем 1000 записей в {$this->scheme}.RegistryDataRowNum...");

				$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

				if ($result === false || !is_array($result) || !empty($result['Error_Msg'])) {
					$this->textlog->add("Ошибка при выполнении запроса");
					$this->textlog->add(print_r($result, true));
					return false;
				}

				$this->textlog->add("... выполнено");

				unset($rowNumArray);
				$rowNumArray = [];
			}
		}

		if (count($rowNumArray) > 0) {
			// пишем связку номеров записей и случаев в отдельную таблицу
			$rowNumInsertQueryBody = '';

			foreach ($rowNumArray as $row) {
				$rowNumInsertQueryBody .= "({$row['Registry_id']}, {$row['Evn_id']}, {$row['RegistryData_RowNum']}, {$this->_exportTimeStamp}),";
			}

			$this->textlog->add("Добавляем " . count($rowNumArray) . " записей в {$this->scheme}.RegistryDataRowNum...");

			$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

			if ($result === false || !is_array($result) || !empty($result['Error_Msg'])) {
				$this->textlog->add("Ошибка при выполнении запроса");
				$this->textlog->add(print_r($result, true));
				return false;
			}

			$this->textlog->add("... выполнено");

			unset($rowNumArray);
		}

		$this->textlog->add("Задействовано памяти после добавления блоков SL в Z_SL: " . (memory_get_usage() / 1024 / 1024) . " MB");

		unset($DS2);
		unset($DS3);
		unset($KSLP);
		unset($LS);
		unset($NAZ);
		unset($USL);

		$this->textlog->add("Задействовано памяти после добавления удаления массивов DS2, DS3, KSLP, LS, NAZ, USL: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// Пишем в файл с данными случаев
		$toFile = array();
		foreach ($ZAP as $key => $onezap) {
			$toFile[] = $onezap;
			unset($ZAP[$key]);

			if (count($toFile) >= 1000) {
				$this->textlog->add("Сформировали массив toFile, количество записей: " . count($toFile));
				$this->textlog->add("Задействовано памяти: " . (memory_get_usage() / 1024 / 1024) . " MB");
				$SD_Z += count($toFile);
				// пишем в файл
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $toFile), true, false, $altKeys);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили ' . count($toFile) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				unset($toFile);
				$toFile = array();
				$this->textlog->add("Очистили массив toFile, задействовано памяти: " . (memory_get_usage() / 1024 / 1024) . " MB");
			}
		}

		if (count($toFile) > 0) {
			$this->textlog->add("Сформировали массив toFile, количество записей: " . count($toFile));
			$this->textlog->add("Задействовано памяти: " . (memory_get_usage() / 1024 / 1024) . " MB");
			$SD_Z += count($toFile);
			// пишем в файл
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, array('ZAP' => $toFile), true, false, $altKeys);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . count($toFile) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			unset($toFile);
			$this->textlog->add("Очистили массив toFile, задействовано памяти: " . (memory_get_usage() / 1024 / 1024) . " MB");
		}

		unset($ZAP);

		$this->textlog->add("Задействовано памяти после записи данных в файл со случаями и удаления массива ZAP: " . (memory_get_usage() / 1024 / 1024) . " MB");

		return $SD_Z;
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false)
	{
		parent::setRegistryParamsByType($data, $force);

		switch ($this->RegistryType_id) {
			case 2:
			case 14:
				$this->MaxEvnField = 'Evn_id';
				break;
			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryDoubleObject = 'RegistryCmpDouble';
				$this->MaxEvnField = 'Evn_id';
				break;
		}
	}

	/**
	 * Простановка статуса реестра
	 */
	function setRegistryCheckStatus($data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_setRegistryCheckStatus (
				Registry_id := :Registry_id,
				RegistryCheckStatus_id := (select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_SysNick = :RegistryCheckStatus_SysNick limit 1),
				Registry_RegistryCheckStatusDate := dbo.tzGetDate(),
				pmUser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при изменении статуса реестра');
		}
	}

	/**
	 * Проверка статуса реестра
	 * Возващает true, если статус "Заблокирован"
	 */
	function checkRegistryIsBlocked($data)
	{
		$query = "
			select
				rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\"
			from
				{$this->scheme}.v_Registry r
				left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				r.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if (!is_object($result)) {
			return true;
		}

		$resp = $result->result('array');

		if (is_array($resp) && count($resp) > 0 && $resp[0]['RegistryCheckStatus_Code'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *    Установка статуса экспорта реестра в XML
	 */
	function SetXmlExportStatus($data)
	{
		if (empty($data['Registry_EvnNum'])) {
			$data['Registry_EvnNum'] = null;
		}

		if (!empty($data['Registry_id'])) {
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

			if (is_object($result)) {
				return true;
			} else {
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		} else {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 *    Установка статуса реестра
	 */
	function setRegistryStatus($data)
	{
		if (empty($data['Registry_id']) || empty($data['RegistryStatus_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
		}

		// Предварительно получаем тип реестра
		$RegistryType_id = 0;
		$RegistryStatus_id = 0;

		$query = "
			select RegistryType_id as \"RegistryType_id\", RegistryStatus_id as \"RegistryStatus_id\"
			from {$this->scheme}.v_Registry Registry
			where Registry_id = :Registry_id
		";
		$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if (is_object($r)) {
			$res = $r->result('array');

			if (is_array($res) && count($res) > 0) {
				$RegistryType_id = $res[0]['RegistryType_id'];
				$RegistryStatus_id = $res[0]['RegistryStatus_id'];

				$data['RegistryType_id'] = $RegistryType_id;
			}
		}

		$this->setRegistryParamsByType($data);

		$fields = "";

		if ($data['RegistryStatus_id'] == 3) { // если перевели в работу, то снимаем признак формирования
			$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
		}

		if ($data['RegistryStatus_id'] == 4) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					4 as \"RegistryStatus_id\"
				from {$this->scheme}.p_Registry_setPaid (
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				);
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
			}

			// @task https://redmine.swan.perm.ru//issues/113626
			// 3) Проставлять _indexrep = 1 у всех случаев предварительного реестра при переводе его в оплаченные
			$response = $this->afterImportRegistryFromTFOMS(array(
				'Registry_id' => $data['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!is_array($response)) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при обработке реестра!'));
			} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
				return array(array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']));
			}
		} elseif ($RegistryStatus_id == 4 && $data['RegistryStatus_id'] == 2) { // если переводим из "Оплаченный" в "К оплате" p_Registry_setUnPaid
			$check154914 = $this->checkRegistryDataIsInOtherRegistry($data);

			if (!empty($check154914)) {
				return array(array('success' => false, 'Error_Msg' => $check154914));
			}

			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					2 as \"RegistryStatus_id\"
				from {$this->scheme}.p_Registry_setUnPaid (
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				);
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке к оплате'));
			}
		}

		$query = "
				update {$this->scheme}.Registry set
					RegistryStatus_id = :RegistryStatus_id,
					Registry_updDT = dbo.tzGetDate(),
					{$fields}
					pmUser_updID = :pmUser_id
				where
					Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!is_object($result)) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		if ($data['RegistryStatus_id'] == 4) {
			// пишем информацию о смене статуса в историю
			$this->dumpRegistryInformation(array('Registry_id' => $data['Registry_id']), 4);
		}

		return $result->result('array');
	}

	/**
	 *    Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data)
	{
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
				array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года'),
				array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года'),
				array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
				array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'),
				array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
				array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги'),
				array('RegistryType_id' => 18, 'RegistryType_Name' => 'Взаиморасчеты по диспансеризации'),
				array('RegistryType_id' => 19, 'RegistryType_Name' => 'Взаиморасчеты по лечебно-диагностическим услугам'),
			);
		}

		return $result;
	}

	/**
	 *    Функция возвращает наименование типа реестра по идентификатору
	 */
	function getRegistryTypeById($RegistryType_id = null)
	{
		$result = '';

		$registryTypeList = $this->loadRegistryTypeNode(array());

		foreach ($registryTypeList as $row) {
			if ($row['RegistryType_id'] == $RegistryType_id) {
				$result = $row['RegistryType_Name'];
				break;
			}
		}

		return $result;
	}

	/**
	 *    Чтение списка реестров
	 */
	function loadRegistry($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';

		$this->setRegistryParamsByType($data);

		$IsZNOField = "case when R.Registry_IsZNO = 2 then 'true' else 'false' end as \"Registry_IsZNO\",";

		if (!empty($data['Registry_id'])) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
			$IsZNOField = "COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",";
		}

		if (!empty($data['RegistryType_id'])) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		if (empty($data['Registry_id'])) {
			if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
				// реесты по бюджету
				$filter .= " and pt.PayType_SysNick in ('bud','fbud')";
			} else {
				$filter .= " and COALESCE(pt.PayType_SysNick, '') not in ('bud','fbud')";
			}
		}

		$loadDeleted = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 12);
		$loadQueue = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 11);

		// Очередь
		if ($loadQueue) {
			$query = "
				select
					R.RegistryQueue_id as \"Registry_id\",
					R.RegistryType_id as \"RegistryType_id\",
					11 as \"RegistryStatus_id\",
					2 as \"Registry_IsActive\",
					R.Registry_IsRepeated as \"Registry_IsRepeated\",
					R.Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
					R.Registry_Num || ' / в очереди: ' || cast(RegistryQueue_Position as varchar) as \"Registry_Num\",
					{$IsZNOField}
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
					R.Lpu_id as \"Lpu_id\",
					R.OrgRSchet_id as \"OrgRSchet_id\",
					R.DispClass_id as \"DispClass_id\,
					r.PayType_id as \"PayType_id\,
					pt.PayType_SysNick as \"PayType_SysNick\",
					0 as \"Registry_Count\",
					0 as \"Registry_RecordPaidCount\",
					0 as \"Registry_KdCount\",
					0 as \"Registry_KdPaidCount\",
					0 as \"Registry_Sum\",
					0 as \"Registry_SumPaid\",
					1 as \"Registry_IsProgress\",
					1 as \"Registry_IsNeedReform\",
					'' as \"Registry_updDate\",
					null as \"RegistryCheckStatus_id\",
					null as \"RegistryCheckStatus_SysNick\",
					'' as \"RegistryCheckStatus_Name\",
					0 as \"RegistryError_IsData\",
					0 as \"RegistryErrorTFOMS_IsData\",
					0 as \"RegistryNoPolis_IsData\",
				    0 as \"RegistryHealDepCheckJournal_AccRecCount\",
					0 as \"RegistryHealDepCheckJournal_DecRecCount\",
					0 as \"RegistryHealDepCheckJournal_UncRecCount\"
				from {$this->scheme}.v_RegistryQueue R
					left join v_PayType pt on pt.PayType_id = R.PayType_id
				where {$filter}
			";
		} // Готовые реестры
		else {
			$source_table = 'v_Registry';

			if (!empty($data['RegistryStatus_id'])) {
				if ($loadDeleted) {
					// если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
				} else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}

				// только если оплаченные!!!
				if (4 == (int)$data['RegistryStatus_id']) {
					if ($data['Registry_accYear'] > 0) {
						$filter .= ' and EXTRACT(YEAR FROM R.Registry_begDate) <= :Registry_accYear';
						$filter .= ' and EXTRACT(YEAR FROM R.Registry_endDate) >= :Registry_accYear';
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}

			$query = "
				select
					R.Registry_id as \"Registry_id\",
					R.RegistryType_id as \"RegistryType_id\",
					case when ChildStatus.RegistryStatus_id = 4 then 1 else 0 end AS \"RegistryChildCheckStatus\",
					" . (!empty($data['RegistryStatus_id']) && 12 == (int)$data['RegistryStatus_id'] ? "12 as \"RegistryStatus_id\"" : "R.RegistryStatus_id as \"RegistryStatus_id\"") . ",
					R.Registry_IsActive as \"Registry_IsActive\",
					R.Registry_IsRepeated as \"Registry_IsRepeated\",
					R.Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
					R.Registry_Num as \"Registry_Num\",
					{$IsZNOField}
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
					R.Lpu_id as \"Lpu_id\",
					R.OrgRSchet_id as \"OrgRSchet_id\",
					R.DispClass_id as \"DispClass_id\",
					r.PayType_id as \"PayType_id\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
					COALESCE(R.Registry_RecordPaidCount, 0) as \"Registry_RecordPaidCount\",
					COALESCE(R.Registry_KdCount, 0) as \"Registry_KdCount\",
					COALESCE(R.Registry_KdPaidCount, 0) as \"Registry_KdPaidCount\",
					COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
					COALESCE(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					to_char(R.Registry_updDT, 'dd.mm.yyyy') || ' ' || to_char(R.Registry_updDT, 'hh24:mi:ss') as \"Registry_updDate\",
					to_char(RQH.RegistryQueueHistory_endDT, 'dd.mm.yyyy') || ' ' || to_char(RQH.RegistryQueueHistory_endDT, 'hh24:mi:ss') as \"ReformTime\",
					R.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
					rcs.RegistryCheckStatus_SysNick as \"RegistryCheckStatus_SysNick\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					case when exists (
						select re.Registry_id as \"Registry_id\" from {$this->scheme}.v_{$this->RegistryErrorObject} RE where RE.Registry_id = R.Registry_id limit 1
					) then 1 else 0 end as \"RegistryError_IsData\",
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\",
					RegistryNoPolis.RegistryNoPolis_IsData as \"RegistryNoPolis_IsData\",
					case when exists (" . $this->getRegistryDoubleCheckQuery($this->scheme) . ") then 1 else 0 end as \"issetDouble\",
					R.Registry_xmlExportPath as \"Registry_xmlExportPath\",
					rhdcj.RegistryHealDepCheckJournal_AccRecCount as \"RegistryHealDepCheckJournal_AccRecCount\",
					rhdcj.RegistryHealDepCheckJournal_DecRecCount as \"RegistryHealDepCheckJournal_DecRecCount\",
					rhdcj.RegistryHealDepCheckJournal_UncRecCount as \"RegistryHealDepCheckJournal_UncRecCount\"
				from {$this->scheme}.{$source_table} R
					left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join v_PayType pt  on pt.PayType_id = R.PayType_id
					left join lateral(
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue 
						where Registry_id = R.Registry_id
						limit 1
					) RQ on true
					left join lateral(
						select RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
						limit 1
					) RQH on true
					left join lateral (
						select 
							rhdcj.RegistryHealDepCheckJournal_AccRecCount,
							rhdcj.RegistryHealDepCheckJournal_DecRecCount,
							rhdcj.RegistryHealDepCheckJournal_UncRecCount
						from
							v_RegistryHealDepCheckJournal rhdcj 
						where
							rhdcj.Registry_id = r.Registry_id
						order by
							rhdcj.RegistryHealDepCheckJournal_Count desc,
							rhdcj.RegistryHealDepCheckJournal_id desc
						limit 1
					) rhdcj on true
					left join lateral (select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData from {$this->scheme}.v_RegistryErrorTFOMS RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorTFOMS on true
					left join lateral (select case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_RegistryNoPolis RE where RE.Registry_id = R.Registry_id limit 1) RegistryNoPolis on true

					left join lateral(
						select
							R3.RegistryStatus_id
						from {$this->scheme}.v_Registry R3
							inner join {$this->scheme}.v_RegistryGroupLink RGL on R3.Registry_id = RGL.Registry_id
						where
							RGL.Registry_pid = R.Registry_id and R3.RegistryStatus_id = 4
						limit 1
					) ChildStatus on true
				where
					{$filter}
				order by
					R.Registry_endDate DESC,
					RQH.RegistryQueueHistory_endDT DESC
			";
		}
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$resp = $result->result('array');

			foreach ($resp as $key => $row) {
				if (empty($row['Registry_xmlExportPath'])) {
					continue;
				}

				$fileNameParts = explode('/', $row['Registry_xmlExportPath']);

				$resp[$key]['Registry_xmlExportPath'] = $fileNameParts[count($fileNameParts) - 1];
			}

			if (!empty($data['Registry_id']) && !empty($resp[0])) {
				$resp[0]['LpuBuilding_id'] = '';
				$resp_lb = $this->queryResult("
					select
						RegistryLpuBuilding_id as \"RegistryLpuBuilding_id\",
						LpuBuilding_id as \"LpuBuilding_id\"
					from
						{$this->scheme}.v_RegistryLpuBuilding
					where
						Registry_id = :Registry_id
				", array(
					'Registry_id' => $data['Registry_id']
				));

				foreach ($resp_lb as $one_lb) {
					if (!empty($resp[0]['LpuBuilding_id'])) {
						$resp[0]['LpuBuilding_id'] .= ",";
					}
					$resp[0]['LpuBuilding_id'] .= $one_lb['LpuBuilding_id'];
				}
			}

			return $resp;
		} else {
			return false;
		}
	}

	/**
	 *    Функция возрвращает набор данных для дерева реестра 2-го уровня (статус реестра)
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
				array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные')
			);
		}
		return $result;
	}

	/**
	 * Получаем состояние реестра в данный момент и тип реестра
	 */
	function GetRegistryXmlExport($data)
	{
		$RegistryType_id = $this->getFirstResultFromQuery("select RegistryType_id as \"RegistryType_id\" from {$this->scheme}.v_Registry where Registry_id = :Registry_id limit 1", array('Registry_id' => $data['Registry_id']));

		if ($RegistryType_id == 13) {
			// Объединенный реестр
			$query = "
				select
					RTrim(UR.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					UR.RegistryType_id as \"RegistryType_id\",
					null as \"DispClass_id\",
					UR.KatNasel_id as \"KatNasel_id\",
					KN.KatNasel_SysNick as \"KatNasel_SysNick\",
					UR.OrgSMO_id as \"OrgSMO_id\",
					OS.OrgSmo_f002smocod as \"OrgSmo_f002smocod\",
					to_char(UR.Registry_begDate, 'yyyymmdd') as \"Registry_begDate\",
					COALESCE(UR.Registry_IsZNO, 1) as \"Registry_IsZNO\",
					UR.Registry_FileNum as \"Registry_FileNum\",
					UR.RegistryStatus_id as \"RegistryStatus_id\",
					UR.RegistryGroupType_id as \"RegistryGroupType_id\",
					RSum.Registry_IsNeedReform as \"Registry_IsNeedReform\",
					RSum.Registry_Sum
						- round(COALESCE(RDSum1.RegistryData_ItogSum,0),2) - round(COALESCE(RDSum2.RegistryData_ItogSum,0),2) - round(COALESCE(RDSumN.RegistryData_ItogSum,0),2)
						- round(COALESCE(RDSumSmp1.RegistryData_ItogSum,0),2) - round(COALESCE(RDSumSmp2.RegistryData_ItogSum,0),2) - round(COALESCE(RDSumSmpN.RegistryData_ItogSum,0),2)
					as \"Registry_SumDifference\",
					COALESCE(RDSum1.RegistryData_Count, 0) + COALESCE(RDSum2.RegistryData_Count, 0) + COALESCE(RDSumN.RegistryData_Count, 0) + COALESCE(RDSumSmp1.RegistryData_Count, 0) + coalesce(RDSumSmp2.RegistryData_Count, 0) + coalesce(RDSumSmpN.RegistryData_Count, 0) as RegistryData_Count,
					COALESCE(UR.RegistryCheckStatus_id,0) as \"RegistryCheckStatus_id\",
					COALESCE(rcs.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					SUBSTRING(to_char(UR.Registry_endDate, 'yyyymmdd'), 3, 4) as \"Registry_endMonth\",
					pt.PayType_SysNick as \"PayType_SysNick\"
				from {$this->scheme}.v_Registry UR 
					left join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					left join v_OrgSmo OS on OS.OrgSmo_id = UR.OrgSmo_id
					left join v_PayType pt on pt.PayType_id = UR.PayType_id
					left join lateral(
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RGL.Registry_id
							inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
						where
							RGL.Registry_pid = UR.Registry_id
							and KN.KatNasel_Code = 1 -- Жители области
							and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
							and RD.OrgSmo_id = UR.OrgSmo_id
					) RDSum1 on true
					left join lateral(
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RGL.Registry_id
							inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
						where
							RGL.Registry_pid = UR.Registry_id
							and KN.KatNasel_Code = 2 -- Иногородние
							and OST.OmsSprTerr_Code > 100
							and OST.OmsSprTerr_Code <> 1135
					) RDSum2 on true
					left join lateral(
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RGL.Registry_id
						where
							RGL.Registry_pid = UR.Registry_id
							and UR.KatNasel_id is null
					) RDSumN on true
					left join lateral(
						select 
							COUNT(RDC.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.v_RegistryDataCmp RDC on RDC.Registry_id = RGL.Registry_id
							inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDC.OmsSprTerr_id
						where
							RGL.Registry_pid = UR.Registry_id
							and KN.KatNasel_Code = 1 -- Жители области
							and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
							and RDC.OrgSmo_id = UR.OrgSmo_id
					) RDSumSmp1 on true
					left join lateral(
						select 
							COUNT(RDC.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_RegistryGroupLink RGL 
							inner join {$this->scheme}.v_RegistryDataCmp RDC on RDC.Registry_id = RGL.Registry_id
							inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDC.OmsSprTerr_id
						where
							RGL.Registry_pid = UR.Registry_id
							and KN.KatNasel_Code = 2 -- Иногородние
							and OST.OmsSprTerr_Code > 100
							and OST.OmsSprTerr_Code <> 1135
					) RDSumSmp2 on true
					left join lateral(
						select 
							COUNT(RDC.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RDC.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_RegistryGroupLink RGL
							inner join {$this->scheme}.v_RegistryDataCmp RDCon RDC.Registry_id = RGL.Registry_id
						where
							RGL.Registry_pid = UR.Registry_id
							and UR.KatNasel_id is null
					) RDSumSmpN on true
					left join lateral(
						select
							SUM(COALESCE(R.Registry_Sum,0)) as Registry_Sum,
							MAX(COALESCE(R.Registry_IsNeedReform, 1)) as Registry_IsNeedReform
						from
							{$this->scheme}.v_Registry R
							inner join {$this->scheme}.v_RegistryGroupLink RGL2 on RGL2.Registry_id = R.Registry_id
						where
							RGL2.Registry_pid = UR.Registry_id
					) RSum on true
					left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = UR.RegistryCheckStatus_id
				where
					UR.Registry_id = :Registry_id
			";
		} else {
			// Простые реестры
			$this->setRegistryParamsByType($data);

			$query = "
				select
					RTrim(R.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					R.RegistryType_id as \"RegistryType_id\",
					R.DispClass_id as \"DispClass_id\",
					R.KatNasel_id as \"KatNasel_id\",
					null as \"KatNasel_SysNick\",
					R.OrgSMO_id as \"OrgSMO_id\",
					null as \"OrgSmo_f002smocod\",
					to_char(R.Registry_begDate, 'yyyymmdd') as \"Registry_begDate\",
					COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",
					R.Registry_FileNum as \"Registry_FileNum\",
					R.RegistryStatus_id as \"RegistryStatus_id\",
					R.Registry_IsNeedReform as \"Registry_IsNeedReform\",
					R.Registry_Sum - round(RDSum.RegistryData_ItogSum, 2) as \"Registry_SumDifference\",
					RDSum.RegistryData_Count as \"RegistryData_Count\",
					COALESCE(R.RegistryCheckStatus_id, 0) as \"RegistryCheckStatus_id\",
					COALESCE(RCS.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					RCS.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					SUBSTRING(to_char(R.Registry_endDate, 'yyyymmdd'), 3, 4) as \"Registry_endMonth\",
					pt.PayType_SysNick as \"PayType_SysNick\"
				from {$this->scheme}.v_Registry R
					left join v_PayType pt on pt.PayType_id = R.PayType_id
					left join lateral(
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(COALESCE(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_{$this->RegistryDataObject} RD
						where
							RD.Registry_id = R.Registry_id
					) RDSum on true
					left join v_RegistryCheckStatus RCS on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				where
					R.Registry_id = :Registry_id
			";
		}

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if (is_object($result)) {
			$r = $result->result('array');

			if (is_array($r) && count($r) > 0) {
				return $r;
			}
		} else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/**
	 * Комментарий
	 */
	function deleteRegistryDouble($data)
	{
		$data['RegistryType_id'] = $this->RegistryType_id;

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryDouble_del (
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				Evn_id := :Evn_id
			);
		";
		$res = $this->db->query($query, $data);

		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Получение данных Дубли посещений (RegistryDouble) для поликлин. реестров
	 */
	function loadRegistryDouble($data)
	{
		$join = "";
		$fields = "";
		$filter = "";

		if (!empty($data['MedPersonal_id'])) {
			$filter .= " and MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if (in_array($this->region, array('ufa', 'pskov', 'buryatiya', 'penza'))) {
			if (!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}

			$join .= "
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields = "
				, LB.LpuBuilding_Name as \"LpuBuilding_Name\"
			";
		}

		$query = "
			select
				-- select
				 RD.Registry_id as \"Registry_id\"
				,RD.Evn_id as \"Evn_id\"
				,COALESCE(ES.EvnSection_rid, EPL.EvnPL_id) as \"Evn_rid\"
				,RD.Person_id as \"Person_id\"
				,rtrim(COALESCE(RD.Person_SurName,'')) || ' ' || rtrim(COALESCE(RD.Person_FirName,'')) || ' ' || rtrim(COALESCE(RD.Person_SecName, '')) as \"Person_FIO\"
				,to_char(RD.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
				,EPL.EvnPL_NumCard as \"Evn_Num\"
				,LS.LpuSection_FullName as \"LpuSection_FullName\"
				,MP.Person_Fio as \"MedPersonal_Fio\"
				,to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"Evn_setDate\"
				,null as \"CmpCallCard_id\"
				{$fields}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryDouble RD
				left join v_EvnSection ES on ES.EvnSection_id = RD.Evn_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_rid = RD.Evn_id
				left join v_EvnPL EPL on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join lateral(
					select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = EVPL.MedPersonal_id limit 1
				) as MP on true
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
			if (is_object($res)) {
				return $res->result('array');
			} else {
				return false;
			}
		} else {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
			$result_count = $this->db->query(getCountSQLPH($query), $data);

			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
			if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
		}
	}

	/**
	 *    Удаление ошибок
	 */
	function deleteRegistryErrorTFOMS($data)
	{
		$params = array('Registry_id' => $data['Registry_id']);

		if (!empty($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
			$query = "
					delete {$this->scheme}.RegistryErrorTFOMS where Registry_id = :Registry_id and Evn_id = :Evn_id;
			";
		} else {
			$query = "
				declare
					@KatNasel_Code int,
					@OrgSmo_id bigint,
					@RegistryType_id bigint,
					@ErrCode int,
					@ErrMsg varchar(400);
	
				set nocount on;
	
				begin try
					select
						@RegistryType_id = RegistryType_id,
						@OrgSmo_id = OrgSmo_id,
						@KatNasel_Code = kn.KatNasel_Code
					from {$this->scheme}.v_Registry r
						left join v_KatNasel kn on kn.KatNasel_id = r.KatNasel_id
					where r.Registry_id = :Registry_id
					limit 1;
	
					if ( @RegistryType_id = 13 )
						begin
							if ( @KatNasel_Code is null )
								begin
									delete {$this->scheme}.RegistryErrorTFOMS with (rowlock)
									from {$this->scheme}.v_RegistryData RDE (nolock)
										inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
									where RegistryErrorTFOMS.Evn_id = RDE.Evn_id
										and RGL.Registry_pid = :Registry_id
								end
							else if ( @KatNasel_Code = 1 )
								begin
									delete {$this->scheme}.RegistryErrorTFOMS with (rowlock)
									from {$this->scheme}.v_RegistryData RDE (nolock)
										inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
										inner join v_OmsSprTerr OST (nolock) on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
									where RegistryErrorTFOMS.Evn_id = RDE.Evn_id
										and RGL.Registry_pid = :Registry_id
										and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
										and RDE.OrgSmo_id = @OrgSmo_id
		
									delete {$this->scheme}.RegistryErrorTFOMS with (rowlock)
									from {$this->scheme}.v_RegistryDataCmp RDE (nolock)
										inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
										inner join v_OmsSprTerr OST (nolock) on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
									where RegistryErrorTFOMS.CmpCloseCard_id = RDE.Evn_id
										and RGL.Registry_pid = :Registry_id
										and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
										and RDE.OrgSmo_id = @OrgSmo_id
								end
							else if ( @KatNasel_Code = 2 )
								begin
									delete {$this->scheme}.RegistryErrorTFOMS with (rowlock)
									from {$this->scheme}.v_RegistryData RDE (nolock)
										inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
										inner join v_OmsSprTerr OST (nolock) on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
									where RegistryErrorTFOMS.Evn_id = RDE.Evn_id
										and RGL.Registry_pid = :Registry_id
										and OST.OmsSprTerr_Code > 100
										and OST.OmsSprTerr_Code <> 1135
		
									delete {$this->scheme}.RegistryErrorTFOMS with (rowlock)
									from {$this->scheme}.v_RegistryDataCmp RDE (nolock)
										inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
										inner join v_OmsSprTerr OST (nolock) on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
									where RegistryErrorTFOMS.CmpCloseCard_id = RDE.Evn_id
										and RGL.Registry_pid = :Registry_id
										and OST.OmsSprTerr_Code > 100
										and OST.OmsSprTerr_Code <> 1135
								end
							end
					else
						delete {$this->scheme}.RegistryErrorTFOMS with (rowlock) where Registry_id = :Registry_id;
				end try
				begin catch
					set @ErrCode = error_number();
					set @ErrMsg = error_message();
				end catch
	
				set nocount off;
	
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
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
	 * @param $data
	 * @return array|false
	 * @decription Добавление записи о незастрахованном
	 */
	public function setRegistryNoPolis($data)
	{
		$params = array(
			'Evn_id' => $data['Evn_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Registry_id' => $data['Registry_id'],
		);

		if ($data['RegistryType_id'] == 6) {
			$mainQuery = "
				insert into {$this->scheme}.RegistryCmpNoPolis (Registry_id, CmpCloseCard_id, Person_id, Person_SurName, Person_FirName, Person_SecName,
					Person_Birthday, pmUser_insID, pmUser_updID, RegistryCmpNoPolis_insDT, RegistryCmpNoPolis_updDT)
				select
					rd.Registry_id,
					rd.Evn_id,
					rd.Person_id,
					ps.Person_Surname,
					ps.Person_Firname,
					ps.Person_Secname,
					ps.Person_Birthday,
					:pmUser_id,
					:pmUser_id,
					dbo.tzGetDate(),
					dbo.tzGetDate()
				from {$this->scheme}.v_RegistryDataCmp rd
					inner join v_PersonState ps on ps.Person_id = rd.Person_id
				where rd.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
				limit 1
			";
		} else {
			$mainQuery = "
				insert into {$this->scheme}.RegistryNoPolis (Registry_id, Evn_id, Person_id, Person_SurName, Person_FirName, Person_SecName,
					Person_Birthday, pmUser_insID, pmUser_updID, RegistryNoPolis_insDT, RegistryNoPolis_updDT)
				select
					rd.Registry_id,
					rd.Evn_id,
					rd.Person_id,
					ps.Person_Surname,
					ps.Person_Firname,
					ps.Person_Secname,
					ps.Person_Birthday,
					:pmUser_id,
					:pmUser_id,
					dbo.tzGetDate(),
					dbo.tzGetDate()
				from {$this->scheme}.v_RegistryData rd
					inner join Evn on Evn.Evn_id = rd.Evn_id
					inner join v_Person_reg ps  on ps.PersonEvn_id = Evn.PersonEvn_id
						and ps.Server_id = Evn.Server_id
				where rd.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
				limit 1
			";
		}

		return $this->queryResult("
				{$mainQuery}
		", $params);
	}

	/**
	 * @param $data
	 * @return array|false
	 * @decription Проверка наличия записи о незастрахованном
	 */
	public function checkRegistryNoPolis($data)
	{
		return $this->getFirstRowFromQuery("
			select Evn_id as \"Evn_id\"
			from {$this->scheme}.RegistryNoPolis 
			where Registry_id = :Registry_id
				and Evn_id = :Evn_id
			limit 1
		", $data);
	}

	/**
	 * Удаление записей о незастрахованных
	 */
	public function deleteRegistryNoPolis($data)
	{
		$params = array('Registry_id' => $data['Registry_id']);

		if (!empty($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ($data['RegistryType_id'] == 6) {
			$mainQuery = "delete {$this->scheme}.RegistryCmpNoPolis where Registry_id = :Registry_id" . (!empty($data['Evn_id']) ? " and CmpCloseCard_id = :Evn_id" : "") . ";";
		} else {
			$mainQuery = "delete {$this->scheme}.RegistryNoPolis where Registry_id = :Registry_id" . (!empty($data['Evn_id']) ? " and Evn_id = :Evn_id" : "") . ";";
		}

		return $this->queryResult("
				{$mainQuery}
		", $params);
	}

	/**
	 *    Установка признака загрузки ответа ТФОМС для простого реестра
	 */
	public function setRegistryIsLoadTFOMS($data)
	{
		return $this->getFirstRowFromQuery("
					update {$this->scheme}.Registry
					set Registry_IsLoadTFOMS = 2
					where Registry_id = :Registry_id;
			",
			array('Registry_id' => $data['Registry_id'])
		);
	}

	/**
	 *    Комментарий
	 */
	function checkErrorDataInRegistry($data)
	{
		if ($data['RegistryType_id'] == 13) { // Объединенный
			if (!empty($data['Evn_id'])) {
				$Evn_id = $data['Evn_id'];
			} else if (!empty($data['SL_ID'])) {
				if (!empty($data['Registry_EvnNum'][$data['SL_ID']])) {
					$Evn_id = $data['Registry_EvnNum'][$data['SL_ID']]['e'];
				}
			}

			if (!empty($Evn_id)) {
				$query = "
					(select
						r.Registry_id as \"Registry_id\",
						r.RegistryType_id as \"RegistryType_id\",
						ps.PersonEvn_id as \"PersonEvn_id\",
						ps.Server_id as \"Server_id\",
						ps.Person_id as \"Person_id\",
						case
							when ps.PolisType_id = 4 then ps.Person_EdNum
							else ps.Polis_Num
						end as \"Polis_Num\",
						ost.KLRgn_id as \"KLRgn_id\",
						rd.Evn_id as \"Evn_id\",
						rd.Evn_rid as \"Evn_rid\",
						COALESCE(rd.RegistryData_deleted, 1) as \"RegistryData_deleted\",
						COALESCE(rd.RegistryData_Tariff, 0) as \"RegistryData_Tariff\",
						COALESCE(rd.RegistryData_ItogSum, 0) as \"RegistryData_ItogSum\"
					from
						{$this->scheme}.v_RegistryDataCmp rd
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = r.Registry_id
						inner join v_PersonState ps on ps.Person_id = rd.Person_id
						left join Polis pls on pls.Polis_id = ps.Polis_id
						left join v_OMSSprTerr ost  on ost.OmsSprTerr_id = pls.OmsSprTerr_id
					where
						rgl.Registry_pid = :Registry_id
						and rd.Evn_id = :Evn_id
					limit 1)
	
					union all
	
					(select
						r.Registry_id as \"Registry_id\",
						r.RegistryType_id as \"RegistryType_id\",
						ps.PersonEvn_id as \"PersonEvn_id\",
						ps.Server_id as \"Server_id\",
						ps.Person_id as \"Person_id\",
						case
							when pls.PolisType_id = 4 then ps.Person_EdNum
							else pls.Polis_Num
						end as \"Polis_Num\",
						ost.KLRgn_id as \"KLRgn_id\",
						rd.Evn_id as \"Evn_id\",
						rd.Evn_id as \"Evn_rid\",
						COALESCE(rd.RegistryData_deleted, 1) as \"RegistryData_deleted\",
						COALESCE(rd.RegistryData_Tariff, 0) as \"RegistryData_Tariff\",
						COALESCE(rd.RegistryData_ItogSum, 0) as \"RegistryData_ItogSum\"
					from
						{$this->scheme}.v_RegistryData rd
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = r.Registry_id
						inner join v_PersonState ps on ps.Person_id = rd.Person_id
						left join Polis pls on pls.Polis_id = ps.Polis_id
						left join v_OMSSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
					where
						rgl.Registry_pid = :Registry_id
						and rd.Evn_id = :Evn_id
					limit 1)
				";

				$params['Registry_id'] = $data['Registry_id'];
				$params['Evn_id'] = $Evn_id;

				$result = $this->db->query($query, $params);

				if (is_object($result)) {
					$row = $result->result('array');

					if (count($row) > 0) {
						return $row[0]; // возвращаем данные о случае
					}
				}
			}

			return false;
		} else if ($data['RegistryType_id'] == 6) { // СМП
			if (!property_exists($this, 'cacheRegistryDataCmpRowNum') || !isset($this->cacheRegistryDataCmpRowNum)) {
				$this->cacheRegistryDataCmpRowNum = [];
				$resp_r = $this->queryResult("
					select
						rd.Evn_id as \"Evn_id\",
						COALESCE(rdrn.RegistryData_RowNum, rd.RegistryData_RowNum) as \"RowNum\"
					from
						{$this->scheme}.v_RegistryDataCmp rd
						left join {$this->scheme}.RegistryDataRowNum rdrn on rdrn.CmpCloseCard_id = rd.Evn_id and rdrn.Registry_id = rd.Registry_id
					where
						rd.Registry_id = :Registry_id
				", [
					'Registry_id' => $data['Registry_id']
				]);

				foreach ($resp_r as $one_r) {
					$this->cacheRegistryDataCmpRowNum[$one_r['RowNum']] = [
						'e' => $one_r['Evn_id']
					];
				}
			}

			if (!empty($this->cacheRegistryDataCmpRowNum[$data['SL_ID']]['e'])) {
				$params['Evn_id'] = $this->cacheRegistryDataCmpRowNum[$data['SL_ID']]['e'];
			} else {
				return false;
			}

			$query = "
				select
					r.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					ps.PersonEvn_id as \"PersonEvn_id\",
					ps.Server_id as \"Server_id\",
					ps.Person_id as \"Person_id\",
					case
						when ps.PolisType_id = 4 then ps.Person_EdNum
						else ps.Polis_Num
					end as \"Polis_Num\",
					ost.KLRgn_id as \"KLRgn_id\",
					rd.Evn_id as \"Evn_id\",
					rd.Evn_id as \"Evn_rid\",
					COALESCE(rd.RegistryData_deleted, 1) as \"RegistryData_deleted\",
					COALESCE(rd.RegistryData_Tariff, 0) as \"RegistryData_Tariff\",
					COALESCE(rd.RegistryData_ItogSum, 0) as \"RegistryData_ItogSum\"
				from
					{$this->scheme}.v_RegistryDataCmp rd
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
					inner join v_PersonState ps on ps.Person_id = rd.Person_id
					left join Polis pls on pls.Polis_id = ps.Polis_id
					left join v_OMSSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
				where
					rd.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
				limit 1
			";
		} else {
			if (!property_exists($this, 'cacheRegistryDataRowNum') || !isset($this->cacheRegistryDataRowNum)) {
				$this->cacheRegistryDataRowNum = [];
				$resp_r = $this->queryResult("
					select
						rd.Evn_id as \"Evn_id\",
						rd.Evn_rid as \"Evn_rid\",
						COALESCE(rdrn.RegistryData_RowNum, rd.RegistryData_RowNum) as \"RowNum\"
					from
						{$this->scheme}.v_RegistryData rd
						left join {$this->scheme}.RegistryDataRowNum rdrn on rdrn.Evn_id = rd.Evn_id and rdrn.Registry_id = rd.Registry_id
					where
						rd.Registry_id = :Registry_id
				", [
					'Registry_id' => $data['Registry_id']
				]);

				foreach ($resp_r as $one_r) {
					$this->cacheRegistryDataRowNum[$one_r['RowNum']] = [
						'e' => $one_r['Evn_id'],
						'er' => $one_r['Evn_rid']
					];
				}
			}

			if (!empty($this->cacheRegistryDataRowNum[$data['SL_ID']]['e'])) {
				$params['Evn_id'] = $this->cacheRegistryDataRowNum[$data['SL_ID']]['e'];
				$params['Evn_rid'] = $this->cacheRegistryDataRowNum[$data['SL_ID']]['er'];
			} else {
				return false;
			}

			$query = "
				select
					r.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					e.PersonEvn_id as \"PersonEvn_id\",
					e.Server_id as \"Server_id\",
					e.Person_id as \"Person_id\",
					case
						when pls.PolisType_id = 4 then ps.Person_EdNum
						else pls.Polis_Num
					end as \"Polis_Num\",
					ost.KLRgn_id as \"KLRgn_id\",
					e.Evn_id as \"Evn_id\",
					e.Evn_rid as \"Evn_rid\",
					COALESCE(rd.RegistryData_deleted, 1) as \"RegistryData_deleted\",
					COALESCE(rd.RegistryData_Tariff, 0) as \"RegistryData_Tariff\",
					COALESCE(rd.RegistryData_ItogSum, 0) as \"RegistryData_ItogSum\"
				from
					{$this->scheme}.v_RegistryData rd
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
					inner join Evn e on e.Evn_id = rd.Evn_id
					inner join v_PersonState ps on ps.Person_id = e.Person_id
					left join Polis pls on pls.Polis_id = ps.Polis_id
					left join v_OMSSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
				where
					rd.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
					and rd.Evn_rid = :Evn_rid
				limit 1
			";
		}

		$params['Registry_id'] = $data['Registry_id'];
		$params['SL_ID'] = $data['SL_ID'];

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$row = $result->result('array');

			if (count($row) > 0) {
				return $row[0]; // возвращаем данные о случае
			}
		}

		return false;
	}

	/**
	 * Добавление ошибки при импорте ответа от ТФОМС
	 */
	function setErrorFromTFOMSImportRegistry($data)
	{
		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['RegistryType_id'] = $data['RegistryType_id'];
		$params['RegistryErrorClass_id'] = !empty($data['RegistryErrorClass_id']) ? $data['RegistryErrorClass_id'] : 1;
		$params['RegistryErrorStageType_id'] = !empty($data['RegistryErrorStageType_id']) ? $data['RegistryErrorStageType_id'] : null;
		$params['Evn_id'] = $data['Evn_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['S_CODE'] = $data['S_CODE'];
		$params['S_COM'] = $data['S_COM'];
		$params['IM_POL'] = !empty($data['IM_POL']) ? $data['IM_POL'] : null;
		$params['BAS_EL'] = !empty($data['BAS_EL']) ? $data['BAS_EL'] : null;
		$params['SL_ID'] = !empty($data['SL_ID']) ? $data['SL_ID'] : null;

		$params['RegistryErrorType_id'] = $this->getFirstResultFromQuery("
			select RegistryErrorType_id as \"RegistryErrorType_id\"
			from {$this->scheme}.RegistryErrorType
			where RegistryErrorType_Code = :S_CODE
			limit 1
		", $params);

		if ($params['RegistryErrorType_id'] === false) {
			$result = $this->getFirstRowFromQuery("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					RegistryErrorType_id as \"RegistryErrorType_id\"
				from {$this->scheme}.p_RegistryErrorType_ins (
					RegistryErrorType_Code := :S_CODE,
					RegistryErrorType_Name := :S_CODE,
					RegistryErrorType_Descr := :S_CODE,
					RegistryErrorClass_id := :RegistryErrorClass_id,
					RegistryErrorStageType_id := :RegistryErrorStageType_id,
					pmUser_id := :pmUser_id
				);
			", $params);

			if ($result === false || !is_array($result) || count($result) == 0) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к БД (добавление нового типа ошибки)'));
			}

			$params['RegistryErrorType_id'] = $result['RegistryErrorType_id'];
		}

		return $this->saveRegistryErrorTFOMS($params);
	}

	/**
	 * Сохранение ошибки в RegistryErrorTFOMS
	 */
	function saveRegistryErrorTFOMS($params)
	{
		$datatable = $this->scheme . '.v_RegistryData';
		$evnField = 'Evn_id';

		if ($params['RegistryType_id'] == 6) {
			$datatable = $this->scheme . '.v_RegistryDataCmp';
			$evnField = 'CmpCloseCard_id';
		}

		$query = "
				insert into {$this->scheme}.RegistryErrorTFOMS (
					Registry_id,
					{$evnField},
					RegistryErrorType_id,
					RegistryErrorType_Code,
					RegistryErrorTFOMS_FieldName,
					RegistryErrorTFOMS_BaseElement,
					RegistryErrorTFOMS_RowNum,
					RegistryErrorTFOMS_Comment,
					RegistryErrorClass_id,
					pmUser_insID,
					pmUser_updID,
					RegistryErrorTFOMS_insDT,
					RegistryErrorTFOMS_updDT
				)
				select
					rd.Registry_id,
					rd.Evn_id,
					:RegistryErrorType_id as RegistryErrorType_id,
					:S_CODE as RegistryErrorType_Code,
					:IM_POL as RegistryErrorTFOMS_FieldName,
					:BAS_EL as RegistryErrorTFOMS_BaseElement,
					:SL_ID as RegistryErrorTFOMS_RowNum,
					:S_COM as RegistryErrorTFOMS_Comment,
					:RegistryErrorClass_id as RegistryErrorClass_id,
					:pmUser_id as pmUser_insID,
					:pmUser_id as pmUser_updID,
					dbo.tzGetDate() as RegistryError_insDT,
					dbo.tzGetDate() as RegistryError_updDT
				from
					{$datatable} rd
				where
					rd.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении ошибки'));
		}
	}

	/**
	 *    Получение списка ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		} else if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		$filter = "(1=1)";

		if (!empty($data['Person_SurName'])) {
			$filter .= " and lower(ps.Person_SurName) like lower(:Person_SurName) ";
			$params['Person_SurName'] = $data['Person_SurName'] . "%";
		}
		if (!empty($data['Person_FirName'])) {
			$filter .= " and lower(ps.Person_FirName) like lower(:Person_FirName) ";
			$params['Person_FirName'] = $data['Person_FirName'] . "%";
		}
		if (!empty($data['Person_SecName'])) {
			$filter .= " and lower(ps.Person_SecName) like lower(:Person_SecName) ";
			$params['Person_SecName'] = $data['Person_SecName'] . "%";
		}
		if (!empty($data['RegistryErrorType_Code'])) {
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (!empty($data['Person_FIO'])) {
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO'] . "%";
		}
		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id ";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if (!empty($data['Evn_id'])) {
			if ($this->RegistryType_id == 6)
				$filter .= "and RE.{$this->RegistryDataEvnField} = :Evn_id";
			else
				$filter .= " and RE.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$leftjoin = "";

		if (!empty($data['RegistryErrorTFOMS_Comment'])) {
			$filter .= " and RE.RegistryErrorTFOMS_Comment ilike '%'||:RegistryErrorTFOMS_Comment||'%'";
			$params['RegistryErrorTFOMS_Comment'] = $data['RegistryErrorTFOMS_Comment'];
		}

		$addToSelect = "";

		if (in_array($this->RegistryType_id, array(7, 9, 12))) {
			$leftjoin .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ",epd.DispClass_id as \"DispClass_id\"";
		}

		switch ($this->RegistryType_id) {
			case 6:
				$query = "
					Select 
						-- select
						RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
						RE.Registry_id as \"Registry_id\",
						null as \"Evn_rid\",
						RE.{$this->RegistryDataEvnField} as \"Evn_id\",
						R.RegistryType_id as \"RegistryType_id\",
						null as \"EvnClass_id\",
						ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
						rtrim(COALESCE(ps.Person_SurName,'')) || ' ' || rtrim(COALESCE(ps.Person_FirName,'')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"Person_FIO\",
						ps.Person_id as \"Person_id\", 
						ps.PersonEvn_id as \"PersonEvn_id\", 
						ps.Server_id as \"Server_id\", 
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						RE.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
						RE.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
						ret.RegistryErrorType_Descr as \"RegistryErrorType_Descr\",
						RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
						MP.Person_Fio as \"MedPersonal_Fio\",
						LB.LpuBuilding_Name as \"LpuBuilding_Name\", 
						LS.LpuSection_Name as \"LpuSection_Name\",
						COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
						case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
						{$addToSelect}
						-- end select
					from 
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.{$this->RegistryDataEvnField}
						left join {$this->scheme}.v_Registry R on R.Registry_id = RE.Registry_id
						left join v_CmpCloseCard ccc on ccc.CmpCloseCard_id = RE.{$this->RegistryDataEvnField}
						left join v_LpuSection LS on LS.LpuSection_id = ccc.LpuSection_id
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
						left join lateral(
							select Person_Fio from v_MedPersonal where MedPersonal_id = ccc.MedPersonal_id limit 1
						) as MP on true
						left join lateral (
							select 
								PersonEvn_id,
								Server_id,
								Person_BirthDay,
								Polis_id,
								Person_SurName,
								Person_FirName,
								Person_SecName,
								Person_id,
								Person_EdNum
							from v_Person_bdz
							where Person_id = rd.Person_id
								and PersonEvn_insDT <= cast(rd.Evn_setDate as date)
							order by PersonEvn_insDT desc
							limit 1
						) ps on true
						left join {$this->scheme}.RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
						-- end order by
				";
				break;

			default:
				$query = "
					Select
						-- select
						RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
						RE.Registry_id as \"Registry_id\",
						Evn.Evn_rid as \"Evn_rid\",
						RE.Evn_id as \"Evn_id\",
						Evn.EvnClass_id as \"EvnClass_id\",
						R.RegistryType_id as \"RegistryType_id\",
						ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
						rtrim(COALESCE(ps.Person_SurName,'')) || ' ' || rtrim(COALESCE(ps.Person_FirName,'')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"Person_FIO\",
						ps.Person_id as \"Person_id\",
						ps.PersonEvn_id as \"PersonEvn_id\",
						ps.Server_id as \"Server_id\",
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						ret.RegistryErrorType_Descr as \"RegistryErrorType_Descr\",
						RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
						RD.MedPersonal_Fio as \"MedPersonal_Fio\",
						LB.LpuBuilding_Name as \"LpuBuilding_Name\",
						LS.LpuSection_Name as \"LpuSection_Name\",
						COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
						case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
						{$addToSelect}
						-- end select
					from
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join {$this->scheme}.v_Registry R on R.Registry_id = RE.Registry_id
						left join v_LpuSection LS on LS.LpuSection_id = RD.LpuSection_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
						left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
						left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
						left join {$this->scheme}.RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
						-- end order by
				";
				break;
		}

		//echo getDebugSql($query, $params);die;

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *    Идентификация СМО
	 */
	function identifyOrgSMO($data)
	{
		if (!property_exists($this, 'orgSmoStore')) {
			$this->orgSmoStore = array();

			// Берём только открытые СМО на текущий момент (refs #120674)
			$query = "
				select OrgSMO_id as \"OrgSMO_id\", 
					Orgsmo_f002smocod as \"Orgsmo_f002smocod\", 
					KLRgn_id as \"KLRgn_id\" 
				from v_OrgSMO 
				where Orgsmo_f002smocod is not null 
					and COALESCE(OrgSMO_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');

				foreach ($resp as $resp_one) {
					$this->orgSmoStore[$resp_one['Orgsmo_f002smocod']] = $resp_one;
				}
			}
		}

		if (!empty($data['Orgsmo_f002smocod']) && !empty($this->orgSmoStore[$data['Orgsmo_f002smocod']])) {
			return $this->orgSmoStore[$data['Orgsmo_f002smocod']];
		}

		return false;
	}

	/**
	 *    Получение идентификатор из справочника "Территории страхования"
	 */
	function getOmsSprTerr($data)
	{

		$filter = "(1=1)";

		if (!empty($data['OmsSprTerr_Code'])) {
			$filter .= " and OmsSprTerr_Code = :OmsSprTerr_Code";
		}

		if (!empty($data['KLRgn_id'])) {
			$filter .= " and KLRgn_id = :KLRgn_id";
		}
		$query = "
			select OmsSprTerr_id as \"OmsSprTerr_id\"
			from v_OmsSprTerr
			where {$filter}
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$row = $result->result('array');

			if (count($row) > 0) {
				return $row[0]['OmsSprTerr_id'];
			}
		}

		return null;
	}

	/**
	 *  Корректировка полисных данных
	 */
	function addNewPolisToPerson($data)
	{
		$added = false;

		if (!empty($data['Polis_begDate']) && $data['Polis_begDate'] instanceof DateTime) {
			$data['Polis_begDate'] = $data['Polis_begDate']->format('Y-m-d');
		} else if (empty($data['Polis_begDate'])) {
			$data['Polis_begDate'] = null;
		}

		if (!empty($data['Polis_endDate']) && $data['Polis_endDate'] instanceof DateTime) {
			$data['Polis_endDate'] = $data['Polis_endDate']->format('Y-m-d');
		} else if (empty($data['Polis_endDate'])) {
			$data['Polis_endDate'] = null;
		}

		if (!property_exists($this, 'mainDB')) {
			$this->mainDB = $this->load->database('default', true);
		}

		// проверяем есть ли у человека такой полис в PersonPolis, если нет добавляем
		$query = "
			select
				PersonPolis_id as \"PersonPolis_id\",
				Server_id as \"Server_id\",
				OrgSMO_id as \"OrgSMO_id\",
				to_char(Polis_begDate, 'yyyy-mm-dd hh24:mi:ss') as \"Polis_begDate\",
				to_char(Polis_endDate, 'yyyy-mm-dd hh24:mi:ss') as \"Polis_endDate\",
				Polis_Ser as \"Polis_Ser\",
				pp.OmsSprTerr_id as \"OmsSprTerr_id\"
			from
				v_PersonPolis pp
			where
				Person_id = :Person_id
				and Polis_Num = :Polis_Num
			limit 1
		";
		$result = $this->mainDB->query($query, $data);

		if (!is_object($result)) {
			return 'Ошибка при выполнении запроса к БД (проверка наличия полиса)';
		}

		// Если в коде 9 цифр, то считаем что это "3. Временное свидетельство"
		if (mb_strlen($data['Polis_Num']) == 16) {
			$data['PolisType_id'] = 4;
		} else if (mb_strlen($data['Polis_Num']) == 9) {
			$data['PolisType_id'] = 3;
		} else {
			return '';
		}

		$resp = $result->result('array');

		if (is_array($resp) && count($resp) > 0) {
			if (
				(!empty($data['OrgSMO_id']) && $resp[0]['OrgSMO_id'] != $data['OrgSMO_id']) // если есть запись, но пришедшая СМО отличается, то просто обновляем СМО в полисе
				|| !empty($data['Polis_begDate']) // если пришла дата полиса, значит точно полис надо обновить
			) {
				$data['PersonPolis_id'] = $resp[0]['PersonPolis_id'];
				if (empty($data['Polis_begDate'])) {
					// если новые данные не пришли, то берем из тех что есть
					$data['Polis_begDate'] = $resp[0]['Polis_begDate'];
					$data['Polis_endDate'] = $resp[0]['Polis_endDate'];
					$data['Polis_Ser'] = $resp[0]['Polis_Ser'];
				}
				$data['Server_id'] = $resp[0]['Server_id'];
				//$data['OmsSprTerr_id'] = $resp[0]['OmsSprTerr_id']; // территорию страхования не меняем refs #131425

				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\",
						PersonPolis_id as \"PersonPolis_id\"
					from p_PersonPolis_upd (
						PersonPolis_id := :PersonPolis_id,
						Server_id := :Server_id,
						Person_id := :Person_id,
						OmsSprTerr_id := :OmsSprTerr_id,
						PolisType_id := :PolisType_id,
						OrgSMO_id := :OrgSMO_id,
						Polis_Ser := :Polis_Ser,
						Polis_Num := :Polis_Num,
						Polis_begDate := :Polis_begDate,
						Polis_endDate := :Polis_endDate,
						PersonPolis_insDT := :Polis_begDate,
						pmUser_id := :pmUser_id
					);
				";
				$result = $this->mainDB->query($query, $data);
				$resp = $result->result('array');

				$added = true;
			} else {
				$resp[0]['PersonPolis_id'] = null;
			}
		} // Если документа ОМС нет, то добавляем
		else if (!empty($data['Polis_begDate'])) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					PersonPolis_id as \"PersonPolis_id\"
				from p_PersonPolis_ins (
					Server_id := :Server_id,
					Person_id := :Person_id,
					OmsSprTerr_id := :OmsSprTerr_id,
					PolisType_id := :PolisType_id,
					OrgSMO_id := :OrgSMO_id,
					Polis_Ser := :Polis_Ser,
					Polis_Num := :Polis_Num,
					Polis_begDate := :Polis_begDate,
					Polis_endDate := :Polis_endDate,
					PersonPolis_insDT := :Polis_begDate,
					pmUser_id := :pmUser_id
				);
			";
			if (empty($data['Polis_endDate']) || $data['Polis_endDate'] >= $data['Polis_begDate']) {
				$result = $this->mainDB->query($query, $data);
				$resp = $result->result('array');

				$added = true;
			}
		}

		// если вставили открытый полис, то все остальные открытые закрываем датой открытия нового минус один день
		if (!empty($resp[0]['PersonPolis_id']) && !empty($data['Polis_begDate']) && empty($data['Polis_endDate'])) {
			$query = "
				update
					Polis
				set
					Polis_endDate = :Polis_endDate
				where
					Polis_id in (
						select Polis_id from v_PersonPolis
						where Person_id = :Person_id
						and PersonPolis_id <> :PersonPolis_id
					)
					and Polis_endDate is null
			";

			$this->mainDB->query($query, array(
				'PersonPolis_id' => $resp[0]['PersonPolis_id'],
				'Person_id' => $data['Person_id'],
				'Polis_endDate' => date('Y-m-d', (strtotime($data['Polis_begDate']) - 60 * 60 * 24))
			));
		}

		// для единого номера полиса проверяем есть ли у человека такой полис в PersonPolisEdNum, если нет добавляем
		if (!empty($data['Polis_begDate']) && $data['PolisType_id'] == 4) {
			$query = "
				select
					PersonPolisEdNum_id as \"PersonPolisEdNum_id\"
				from
					v_PersonPolisEdNum
				where
					Person_id = :Person_id
					and PersonPolisEdNum_EdNum = :Polis_Num
					and PersonPolisEdNum_begDT = :Polis_begDate
				limit 1
			";
			$result = $this->mainDB->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');

				if (empty($resp[0]['PersonPolisEdNum_id'])) {
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PersonPolisEdNum_ins (
							Server_id := :Server_id,
							Person_id := :Person_id,
							PersonPolisEdNum_EdNum := :Polis_Num,
							PersonPolisEdNum_begDT := :Polis_begDate,
							PersonPolisEdNum_insDT := :Polis_begDate,
							pmUser_id := :pmUser_id
						);
					";

					$result = $this->mainDB->query($query, $data);
					$added = true;
				}
			}
		}

		// запускаем xp_PersonAllocatePersonEvnByEvn, если что то добавили
		if ($added) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from xp_PersonAllocatePersonEvnByEvn (
					Person_id := :Person_id
				);
			";

			$this->mainDB->query($query, $data);
		}

		return '';
	}

	/**
	 *    Получение данных объединенного реестра
	 */
	function loadUnionRegistryData($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		}

		if (empty($data['forPrint']) && (isset($data['start']) && (isset($data['limit']))) && (!(($data['start'] >= 0) && ($data['limit'] >= 0)))) {
			return false;
		}

		$fieldList = array();
		$filterList = array('(1 = 1)');
		$joinList = array();
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);

		if (!empty($data['Person_SurName'])) {
			$filterList[] = "lower(RD.Person_SurName) like lower(:Person_SurName)";
			$params['Person_SurName'] = rtrim($data['Person_SurName']) . "%";
		}

		if (!empty($data['Person_FirName'])) {
			$filterList[] = "lower(RD.Person_FirName) like lower(:Person_FirName)";
			$params['Person_FirName'] = rtrim($data['Person_FirName']) . "%";
		}

		if (!empty($data['Person_SecName'])) {
			$filterList[] = "lower(RD.Person_SecName) like lower(:Person_SecName)";
			$params['Person_SecName'] = rtrim($data['Person_SecName']) . "%";
		}

		if (!empty($data['Polis_Num'])) {
			$filterList[] = "RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if ($data['filterRecords'] == 2) {
			$filterList[] = "COALESCE(RD.RegistryData_IsPaid,1) = 2";
		} else if ($data['filterRecords'] == 3) {
			$filterList[] = "COALESCE(RD.RegistryData_IsPaid,1) = 1";
		}

		if (empty($data['forPrint'])) {
			$joinList[] = "left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id";
			$joinList[] = "
				left join lateral (
					Select count(*) as ErrTfoms_Count
					from {$this->scheme}.v_RegistryErrorTFOMS RET
					where RD.Evn_id = RET.Evn_id
						and RD.Registry_id = RET.Registry_id
						and RET.RegistryErrorTFOMSLevel_id = 1
				) RegistryErrorTFOMS on true
			";
			$joinList[] = "
				left join lateral (
					Select PersonEvn_id
					from v_PersonEvn PE
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= COALESCE(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
					limit 1
				) PersonEvn on true
			";

			$fieldList[] = "PersonEvn.PersonEvn_id as \"PersonEvn_id\"";
			$fieldList[] = "RegistryErrorTFOMS.ErrTfoms_Count as \"ErrTfoms_Count\"";
			$fieldList[] = "RD.needReform as \"needReform\"";
			$fieldList[] = "RD.checkReform as \"checkReform\"";
			$fieldList[] = "RD.timeReform as \"timeReform\"";
			$fieldList[] = "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end as \"isNoEdit\"";
			$fieldList[] = "RD.RegistryData_KdFact as \"RegistryData_Uet\"";

			// в реестрах со статусом частично принят помечаем оплаченные случаи
			$joinList[] = "left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id";
			$joinList[] = "left join v_RegistryCheckStatus RCS on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id";
			$fieldList[] = "case when RCS.RegistryCheckStatus_Code = 3 then COALESCE(RD.RegistryData_IsPaid,1) else 0 end as RegistryData_IsPaid";

			$joinList[] = "left join v_MesOld MOLD on MOLD.Mes_id = RD.MesItog_id";
			$fieldList[] = "MOLD.Mes_Code || COALESCE(' ' || MOLD.MesOld_Num, '') as \"Mes_Code\"";
		}

		$query = "
			-- addit with
			with RD (
				Evn_id,
				Evn_rid,
				EvnClass_id,
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
				MesItog_id,
				MedPersonal_id
			) as (
				select
					RDE.Evn_id as Evn_id,
					RDE.Evn_rid as Evn_rid,
					RDE.EvnClass_id as EvnClass_id,
					epd.DispClass_id as DispClass_id,
					RDE.Person_id as Person_id,
					RDE.Registry_id as Registry_id,
					RDE.Evn_disDate as Evn_disDate,
					RDE.Evn_setDate as Evn_setDate,
					RDE.RegistryType_id as RegistryType_id,
					RDE.Server_id as Server_id,
					RDE.needReform as needReform,
					RDE.checkReform as checkReform,
					RDE.timeReform as timeReform,
					RDE.RegistryData_IsPaid as RegistryData_IsPaid,
					RDE.RegistryData_KdFact as RegistryData_KdFact,
					RDE.RegistryData_deleted as RegistryData_deleted,
					RDE.NumCard as NumCard,
					RDE.Person_FIO as Person_FIO,
					RDE.Person_BirthDay as Person_BirthDay,
					RDE.Person_IsBDZ as Person_IsBDZ,
					RDE.LpuSection_id as LpuSection_id,
					RDE.LpuSection_name as LpuSection_name,
					RDE.MedPersonal_Fio as MedPersonal_Fio,
					RDE.RegistryData_Tariff as RegistryData_Tariff,
					RDE.RegistryData_KdPay as RegistryData_KdPay,
					RDE.RegistryData_KdPlan as RegistryData_KdPlan,
					RDE.RegistryData_ItogSum as RegistryData_ItogSum,
					RDE.Person_SurName as Person_SurName,
					RDE.Person_FirName as Person_FirName,
					RDE.Person_SecName as Person_SecName,
					RDE.Polis_Num as Polis_Num,
					RDE.MesItog_id as MesItog_id,
					RDE.MedPersonal_id as MedPersonal_id
				from
					{$this->scheme}.v_RegistryData RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
					left join v_EvnPLDisp epd with on epd.EvnPLDisp_id = RDE.Evn_rid
				where
					RGL.Registry_pid = :Registry_id
					and KN.KatNasel_Code = 1 -- Жители области
					and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
					and UR.OrgSmo_id = RDE.OrgSmo_id

				union all

				select
					RDE.Evn_id as Evn_id,
					RDE.Evn_rid as Evn_rid,
					RDE.EvnClass_id as EvnClass_id,
					epd.DispClass_id as DispClass_id,
					RDE.Person_id as Person_id,
					RDE.Registry_id as Registry_id,
					RDE.Evn_disDate as Evn_disDate,
					RDE.Evn_setDate as Evn_setDate,
					RDE.RegistryType_id as RegistryType_id,
					RDE.Server_id as Server_id,
					RDE.needReform as needReform,
					RDE.checkReform as checkReform,
					RDE.timeReform as timeReform,
					RDE.RegistryData_IsPaid as RegistryData_IsPaid,
					RDE.RegistryData_KdFact as RegistryData_KdFact,
					RDE.RegistryData_deleted as RegistryData_deleted,
					RDE.NumCard as NumCard,
					RDE.Person_FIO as Person_FIO,
					RDE.Person_BirthDay as Person_BirthDay,
					RDE.Person_IsBDZ as Person_IsBDZ,
					RDE.LpuSection_id as LpuSection_id,
					RDE.LpuSection_name as LpuSection_name,
					RDE.MedPersonal_Fio as MedPersonal_Fio,
					RDE.RegistryData_Tariff as RegistryData_Tariff,
					RDE.RegistryData_KdPay as RegistryData_KdPay,
					RDE.RegistryData_KdPlan as RegistryData_KdPlan,
					RDE.RegistryData_ItogSum as RegistryData_ItogSum,
					RDE.Person_SurName as Person_SurName,
					RDE.Person_FirName as Person_FirName,
					RDE.Person_SecName as Person_SecName,
					RDE.Polis_Num as Polis_Num,
					RDE.MesItog_id as MesItog_id,
					RDE.MedPersonal_id as MedPersonal_id
				from
					{$this->scheme}.v_RegistryData RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
					left join v_EvnPLDisp epd with on epd.EvnPLDisp_id = RDE.Evn_rid
				where
					RGL.Registry_pid = :Registry_id
					and KN.KatNasel_Code = 2 -- Иногородние
					and OST.OmsSprTerr_Code > 100
					and OST.OmsSprTerr_Code <> 1135

				union all

				select
					RDE.Evn_id as Evn_id,
					RDE.Evn_rid as Evn_rid,
					RDE.EvnClass_id as EvnClass_id,
					epd.DispClass_id as DispClass_id,
					RDE.Person_id as Person_id,
					RDE.Registry_id as Registry_id,
					RDE.Evn_disDate as Evn_disDate,
					RDE.Evn_setDate as Evn_setDate,
					RDE.RegistryType_id as RegistryType_id,
					RDE.Server_id as Server_id,
					RDE.needReform as needReform,
					RDE.checkReform as checkReform,
					RDE.timeReform as timeReform,
					RDE.RegistryData_IsPaid as RegistryData_IsPaid,
					RDE.RegistryData_KdFact as RegistryData_KdFact,
					RDE.RegistryData_deleted as RegistryData_deleted,
					RDE.NumCard as NumCard,
					RDE.Person_FIO as Person_FIO,
					RDE.Person_BirthDay as Person_BirthDay,
					RDE.Person_IsBDZ as Person_IsBDZ,
					RDE.LpuSection_id as LpuSection_id,
					RDE.LpuSection_name as LpuSection_name,
					RDE.MedPersonal_Fio as MedPersonal_Fio,
					RDE.RegistryData_Tariff as RegistryData_Tariff,
					RDE.RegistryData_KdPay as RegistryData_KdPay,
					RDE.RegistryData_KdPlan as RegistryData_KdPlan,
					RDE.RegistryData_ItogSum as RegistryData_ItogSum,
					RDE.Person_SurName as Person_SurName,
					RDE.Person_FirName as Person_FirName,
					RDE.Person_SecName as Person_SecName,
					RDE.Polis_Num as Polis_Num,
					RDE.MesItog_id as MesItog_id,
					RDE.MedPersonal_id as MedPersonal_id
				from
					{$this->scheme}.v_RegistryData RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					inner join {$this->scheme}.v_Registry UR  on UR.Registry_id = RGL.Registry_pid
					left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RDE.Evn_rid
				where
					RGL.Registry_pid = :Registry_id
					and UR.KatNasel_id is null

				union all

				select
					RDE.Evn_id as Evn_id,
					RDE.Evn_rid as Evn_rid,
					RDE.EvnClass_id as EvnClass_id,
					null as DispClass_id,
					RDE.Person_id as Person_id,
					RDE.Registry_id as Registry_id,
					RDE.Evn_disDate as Evn_disDate,
					RDE.Evn_setDate as Evn_setDate,
					RDE.RegistryType_id as RegistryType_id,
					RDE.Server_id as Server_id,
					RDE.needReform as needReform,
					RDE.checkReform as checkReform,
					RDE.timeReform as timeReform,
					RDE.RegistryData_IsPaid as RegistryData_IsPaid,
					RDE.RegistryData_KdFact as RegistryData_KdFact,
					RDE.RegistryData_deleted as RegistryData_deleted,
					RDE.NumCard as NumCard,
					RDE.Person_FIO as Person_FIO,
					RDE.Person_BirthDay as Person_BirthDay,
					RDE.Person_IsBDZ as Person_IsBDZ,
					RDE.LpuSection_id as LpuSection_id,
					RDE.LpuSection_name as LpuSection_name,
					RDE.MedPersonal_Fio as MedPersonal_Fio,
					RDE.RegistryData_Tariff as RegistryData_Tariff,
					RDE.RegistryData_KdPay as RegistryData_KdPay,
					RDE.RegistryData_KdPlan as RegistryData_KdPlan,
					RDE.RegistryData_ItogSum as RegistryData_ItogSum,
					RDE.Person_SurName as Person_SurName,
					RDE.Person_FirName as Person_FirName,
					RDE.Person_SecName as Person_SecName,
					RDE.Polis_Num as Polis_Num,
					null as MesItog_id,
					RDE.MedPersonal_id as MedPersonal_id
				from
					{$this->scheme}.v_RegistryDataCmp RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and KN.KatNasel_Code = 1 -- Жители области
					and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)
					and UR.OrgSmo_id = RDE.OrgSmo_id

				union all

				select
					RDE.Evn_id as Evn_id,
					RDE.Evn_rid as Evn_rid,
					RDE.EvnClass_id as EvnClass_id,
					null as DispClass_id,
					RDE.Person_id as Person_id,
					RDE.Registry_id as Registry_id,
					RDE.Evn_disDate as Evn_disDate,
					RDE.Evn_setDate as Evn_setDate,
					RDE.RegistryType_id as RegistryType_id,
					RDE.Server_id as Server_id,
					RDE.needReform as needReform,
					RDE.checkReform as checkReform,
					RDE.timeReform as timeReform,
					RDE.RegistryData_IsPaid as RegistryData_IsPaid,
					RDE.RegistryData_KdFact as RegistryData_KdFact,
					RDE.RegistryData_deleted as RegistryData_deleted,
					RDE.NumCard as NumCard,
					RDE.Person_FIO as Person_FIO,
					RDE.Person_BirthDay as Person_BirthDay,
					RDE.Person_IsBDZ as Person_IsBDZ,
					RDE.LpuSection_id as LpuSection_id,
					RDE.LpuSection_name as LpuSection_name,
					RDE.MedPersonal_Fio as MedPersonal_Fio,
					RDE.RegistryData_Tariff as RegistryData_Tariff,
					RDE.RegistryData_KdPay as RegistryData_KdPay,
					RDE.RegistryData_KdPlan as RegistryData_KdPlan,
					RDE.RegistryData_ItogSum as RegistryData_ItogSum,
					RDE.Person_SurName as Person_SurName,
					RDE.Person_FirName as Person_FirName,
					RDE.Person_SecName as Person_SecName,
					RDE.Polis_Num as Polis_Num,
					null as MesItog_id,
					RDE.MedPersonal_id as MedPersonal_id
				from
					{$this->scheme}.v_RegistryDataCmp RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					inner join {$this->scheme}.v_Registry UR  on UR.Registry_id = RGL.Registry_pid
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST  on OST.OmsSprTerr_id = RDE.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and KN.KatNasel_Code = 2 -- Иногородние
					and OST.OmsSprTerr_Code > 100
					and OST.OmsSprTerr_Code <> 1135

				union all

				select
					RDE.Evn_id as Evn_id,
					RDE.Evn_rid as Evn_rid,
					RDE.EvnClass_id as EvnClass_id,
					null as DispClass_id,
					RDE.Person_id as Person_id,
					RDE.Registry_id as Registry_id,
					RDE.Evn_disDate as Evn_disDate,
					RDE.Evn_setDate as Evn_setDate,
					RDE.RegistryType_id as RegistryType_id,
					RDE.Server_id as Server_id,
					RDE.needReform as needReform,
					RDE.checkReform as checkReform,
					RDE.timeReform as timeReform,
					RDE.RegistryData_IsPaid as RegistryData_IsPaid,
					RDE.RegistryData_KdFact as RegistryData_KdFact,
					RDE.RegistryData_deleted as RegistryData_deleted,
					RDE.NumCard as NumCard,
					RDE.Person_FIO as Person_FIO,
					RDE.Person_BirthDay as Person_BirthDay,
					RDE.Person_IsBDZ as Person_IsBDZ,
					RDE.LpuSection_id as LpuSection_id,
					RDE.LpuSection_name as LpuSection_name,
					RDE.MedPersonal_Fio as MedPersonal_Fio,
					RDE.RegistryData_Tariff as RegistryData_Tariff,
					RDE.RegistryData_KdPay as RegistryData_KdPay,
					RDE.RegistryData_KdPlan as RegistryData_KdPlan,
					RDE.RegistryData_ItogSum as RegistryData_ItogSum,
					RDE.Person_SurName as Person_SurName,
					RDE.Person_FirName as Person_FirName,
					RDE.Person_SecName as Person_SecName,
					RDE.Polis_Num as Polis_Num,
					null as MesItog_id,
					RDE.MedPersonal_id as MedPersonal_id
				from
					{$this->scheme}.v_RegistryDataCmp RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
				where
					RGL.Registry_pid = :Registry_id
					and UR.KatNasel_id is null
			)
			-- end addit with

			Select
				-- select
				RD.Evn_id as \"Evn_id\",
				RD.Evn_rid as \"Evn_rid\",
				RD.EvnClass_id as \"EvnClass_id\",
				RD.DispClass_id as \"DispClass_id\",
				RD.Registry_id as \"Registry_id\",
				RD.RegistryType_id as \"RegistryType_id\",
				RD.Person_id as \"Person_id\",
				RD.Server_id as \"Server_id\",
				RD.RegistryData_deleted as \"RegistryData_deleted\",
				RTrim(RD.NumCard) as \"EvnPL_NumCard\",
				RTrim(RD.Person_FIO) as \"Person_FIO\",
				to_char(RD.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
				RD.LpuSection_id as \"LpuSection_id\",
				lb.LpuBuilding_Name as \"LpuBuilding_Name\",
				RTrim(RD.LpuSection_name) as \"LpuSection_name\",
				RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
				to_char(RD.Evn_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				to_char(RD.Evn_disDate, 'dd.mm.yyyy') as \"Evn_disDate\",
				RD.RegistryData_Tariff as \"RegistryData_Tariff\",
				RD.RegistryData_KdPay as \"RegistryData_KdPay\",
				RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
				RD.RegistryData_ItogSum as \"RegistryData_ItogSum\"
				" . (count($fieldList) > 0 ? "," . implode(', ', $fieldList) : "") . "
				-- end select
			from
				-- from
				RD
				left join v_Evn e on e.Evn_id = rd.Evn_id
				left join v_LpuSection ls on ls.LpuSection_id = RD.LpuSection_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
				" . implode(PHP_EOL, $joinList) . "
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";
		if (!empty($data['nopaging']) || !empty($data['forPrint'])) {
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				if (!empty($data['nopaging'])) {
					return $result->result('array');
				}

				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = count($response['data']);
				return $response;
			} else {
				return false;
			}
		}

		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];

			if ($count > 100) {
				return array('Error_Msg' => 'Найдено более 100 записей, необходимо наложить дополнительный фильтр');
			}

			unset($cnt_arr);
		} else {
			$count = 0;
		}

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *    Список ошибок ТФОМС объединенного реестра
	 */
	function loadUnionRegistryErrorTFOMS($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		} else if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filterList = array("(1 = 1)");

		if (!empty($data['RegistryErrorType_Code'])) {
			$filterList[] = "RE.RegistryErrorType_Code = :RegistryErrorType_Code";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}

		if (!empty($data['Person_FIO'])) {
			$filterList[] = "rtrim(coalesce(ps.Person_SurName,ps_ccc.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,ps_ccc.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, ps_ccc.Person_SecName, '')) ilike :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'] . "%";
		}

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RE.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$query = "
			-- addit with
			with RE (
				Evn_id,
				Registry_id,
				RegistryType_id,
				Evn_rid,
				RegistryData_deleted,
				RegistryErrorTFOMS_id,
				RegistryErrorType_Code,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				RegistryErrorType_id,
				RegistryErrorTFOMSLevel_id
			) as (
				select
					RD.Evn_id as Evn_id,
					RD.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					RD.Evn_rid as Evn_rid,
					RD.RegistryData_deleted as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id <> 6
					and KN.KatNasel_Code = 1 -- Жители области
					and UR.OrgSmo_id = RD.OrgSmo_id
					and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)

				union all

				select
					RD.Evn_id as Evn_id,
					RD.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					RD.Evn_rid as Evn_rid,
					RD.RegistryData_deleted as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
					inner join v_KatNasel KN  on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id <> 6
					and KN.KatNasel_Code = 2 -- Иногородние
					and OST.OmsSprTerr_Code > 100
					and OST.OmsSprTerr_Code <> 1135

				union all

				select
					RD.Evn_id as Evn_id,
					RD.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					RD.Evn_rid as Evn_rid,
					RD.RegistryData_deleted as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id <> 6
					and UR.KatNasel_id is null

				union all

				select
					RD.Evn_id as Evn_id,
					RD.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					RD.Evn_rid as Evn_rid,
					RD.RegistryData_deleted as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE  on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
					inner join v_KatNasel KN  on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and KN.KatNasel_Code = 1 -- Жители области
					and UR.OrgSmo_id = RD.OrgSmo_id
					and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)

				union all

				select
					RD.Evn_id as Evn_id,
					RD.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					RD.Evn_rid as Evn_rid,
					RD.RegistryData_deleted as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL 
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD  on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and KN.KatNasel_Code = 2 -- Иногородние
					and OST.OmsSprTerr_Code >= 100
					and OST.OmsSprTerr_Code <> 1135

				union all

				select
					RD.Evn_id as Evn_id,
					RD.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					RD.Evn_rid as Evn_rid,
					RD.RegistryData_deleted as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and UR.KatNasel_id is null

				union all

				select
					null as Evn_id,
					R.Registry_id as Registry_id,
					R.RegistryType_id as RegistryType_id,
					null as Evn_rid,
					null as RegistryData_deleted,
					RE.RegistryErrorTFOMS_id as RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code as RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName as RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement as RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment as RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id as RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id as RegistryErrorTFOMSLevel_id
				from
					{$this->scheme}.v_Registry R 
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
				where
					R.Registry_id = :Registry_id
					and NULLIF(RE.RegistryErrorTFOMS_IdCase, '') is null
			)
			-- end addit with

			select
				-- select
				RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				RE.Registry_id as \"Registry_id\",
				RE.RegistryType_id as \"RegistryType_id\",
				Evn.Evn_rid as \"Evn_rid\",
				RE.Evn_id as \"Evn_id\",
				Evn.EvnClass_id as \"EvnClass_id\",
				ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				ret.RegistryErrorType_Name as \"RegistryError_FieldName\",
				ret.RegistryErrorType_Descr || ' (' || RETF.RegistryErrorTFOMSField_Name || ')' as \"RegistryError_Comment\",
				rtrim(coalesce(ps.Person_SurName,ps_ccc.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,ps_ccc.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, ps_ccc.Person_SecName, '')) as \"Person_FIO\",
				COALESCE(ps.Person_id, CCC.Person_id) as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				to_char(cast(COALESCE(ps.Person_BirthDay, ps_ccc.Person_BirthDay) as date), 'dd.mm.yyyy') as \"Person_BirthDay\",
				RE.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
				RE.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
				RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				COALESCE(RE.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RE.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\"
				-- end select
			from
				-- from
				RE
				left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
				left join v_CmpCloseCard CCC  on CCC.CmpCloseCard_id = RE.Evn_id
				left join RegistryErrorTFOMSField RETF  on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
				left join v_Person_bdz ps  on ps.PersonEvn_id = Evn.PersonEvn_id
					and ps.Server_id = Evn.Server_id
				left join v_PersonState ps_ccc  on ps_ccc.Person_id = CCC.Person_id
				left join RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_RegistryErrorTFOMSLevel retl on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);

		if (is_object($result)) {
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
		} else {
			return false;
		}
	}

	/**
	 * Сохранение значений тарифа и итоговой суммы в случае реестра
	 */
	function setRegistryDataParams($params)
	{
		$datatable = 'RegistryData';
		$evnField = 'Evn_id';

		if ($params['RegistryType_id'] == 6) {
			$datatable = 'RegistryDataCmp';
			$evnField = 'CmpCloseCard_id';
		}

		$query = "
				update {$this->scheme}.{$datatable}
				set
					{$datatable}_Tariff = :TARIF,
					{$datatable}_ItogSum = :SUM_M,
					{$datatable}_updDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
					and {$evnField} = :Evn_id;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении ошибки'));
		}
	}

	/**
	 * Сохранение итоговой суммы в случае реестра
	 */
	public function setRegistryDataSLParams($params)
	{
		$query = "
				update {$this->scheme}.RegistryDataSL
				set
					RegistryData_ItogSum = :SUMV,
					RegistryDataSL_updDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
					and Evn_rid = :Evn_rid;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении ошибки'));
		}
	}

	/**
	 * Сохранение значений тарифа и итоговой суммы в услуге из случая
	 */
	function setRegistryUslugaParams($params)
	{
		$datatable = 'RegistryUsluga' . ($params['RegistryType_id'] == 6 ? 'Cmp' : '');

		$query = "
				update {$this->scheme}.{$datatable}
				set
					{$datatable}_TARIF = :TARIF,
					{$datatable}_SUMV = :SUMV,
					{$datatable}_updDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
					and EvnUsluga_id = :EvnUsluga_id;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении ошибки'));
		}
	}

	/**
	 * Пересчет суммы в реестре
	 */
	function recountRegistrySum($data)
	{
		$datatable = 'RegistryData' . ($data['RegistryType_id'] == 6 ? 'Cmp' : '');

		$query = "
				update {$this->scheme}.Registry 
				set Registry_Sum = (select SUM({$datatable}_ItogSum) from {$this->scheme}.{$datatable} where Registry_id = :Registry_id and {$datatable}_ItogSum is not null)
				where Registry_id = :Registry_id;
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к БД (пересчет суммы реестра)'));
		}
	}

	/**
	 * Получение списка дополнительных полей для выборки
	 */
	public function getReformRegistryAdditionalFields()
	{
		return ',DispClass_id,PayType_id,Registry_IsRepeated,Registry_IsOnceInTwoYears,Registry_IsZNO';
	}

	/**
	 * После успешного импорта реестра из ТФОМС
	 */
	function afterImportRegistryFromTFOMS($data)
	{
		return $this->getFirstRowFromQuery("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_setIndexRep (
				Registry_id := :Registry_id,
				pmUser_id := :pmUser_id
			);
		", array(
			'Registry_id' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 * Действия, выполняемые перед удалением реестра из очереди
	 */
	public function beforeDeleteRegistryQueue($RegistryQueue_id = null)
	{
		$query = "
				delete from {$this->scheme}.RegistryLpuBuilding where RegistryQueue_id = :RegistryQueue_id;
		";
		$result = $this->db->query($query, array('RegistryQueue_id' => $RegistryQueue_id));

		if (!is_object($result)) {
			return false;
		}

		$response = $result->result('array');

		return (is_array($response) && count($response) > 0 && empty($response[0]['Error_Msg']));
	}

	/**
	 *    Получение данных для выгрузки реестров в XML
	 */
	public function loadRegistrySCHETForXmlUsing($data, $type = 13)
	{
		$query = "exec {$this->scheme}.p_Registry_expScet @Registry_id = :Registry_id";
		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'KatNasel_id' => ($type == 13 ? $data['KatNasel_id'] : null),
			'OrgSMO_id' => ($type == 13 ? $data['OrgSMO_id'] : null),
		));

		if (!is_object($result)) {
			return false;
		}

		$header = $result->result('array');

		if (!is_array($header) || count($header) == 0) {
			return false;
		}

		if (!preg_match("/^\d+\.\d{2}$/", $header[0]['SUMMAV'])) {
			$header[0]['SUMMAV'] = round($header[0]['SUMMAV'], 2);
		}

		array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);

		return array($header[0]);
	}

	/**
	 * Возвращает список настроек ФЛК
	 */
	public function loadRegistryEntiesSettings($data)
	{
		if (!$data['RegistryType_id']) return false;

		if ($data['RegistryType_id'] == 13 && isset($data['RegistryGroupType_id'])) {
			$where = ' AND RegistryGroupType_id = ' . $data['RegistryGroupType_id'];
		} else {
			$where = ' AND RegistryType_id = ' . $data['RegistryType_id'];
		}

		$params = array();
		$query = "
			SELECT
				FLKSettings_id as \"FLKSettings_id\"
				,cast(getdate() as timestamp) as \"DD\"
				,RegistryType_id as \"RegistryType_id\"
				,FLKSettings_EvnData as \"FLKSettings_EvnData\"
				,FLKSettings_PersonData as \"FLKSettings_PersonData\
			FROM v_FLKSettings
			WHERE 
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then '2030-01-01'
						else FLKSettings_endDate
					end
				AND FLKSettings_EvnData LIKE '%krym%'
			limit 1
		" . $where;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  ФЛК контроль
	 */
	function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
	{
		if (!file_exists($xsd_tpl) || !$xml_data) return false;

		libxml_use_internal_errors(true);
		$xml = new DOMDocument();

		if ($type == 'file') {
			$xml->load($xml_data);
		} elseif ($type == 'string') {
			$xml->loadXML($xml_data);
		}

		if (!@$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();

			file_put_contents($output_file_name, $res_errors);
			return false;
		} else {
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
		foreach ($errors as $error) {
			$return = "<br/>\n";
			switch ($error->level) {
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
			if ($error->file) {
				$return .= " in <b>$error->file</b>";
			}

			$return .= " on line <b>$error->line</b>\n";
			print $return;
		}
		libxml_clear_errors();
	}

	/**
	 *    Помечаем запись реестра на удаление
	 */
	public function deleteRegistryData($data)
	{
		$RegistryType_id = $this->getFirstResultFromQuery("select RegistryType_id as \"RegistryType_id\" from {$this->scheme}.v_Registry where Registry_id = :Registry_id limit 1", $data);

		$EvnIdByRegistryArray = array();
		$RegistryTypeArray = array();

		if ($RegistryType_id == 13) {
			$UnionRegistryEvnIdArray = $this->queryResult("
				select
					RGL.Registry_id as \"Registry_id\",
					R.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
				where
					RGL.Registry_pid = :Registry_id
					and R.Lpu_id = :Lpu_id
			", $data);

			if ($UnionRegistryEvnIdArray !== false) {
				foreach ($UnionRegistryEvnIdArray as $row) {
					$EvnIdByRegistryArray[$row['Registry_id']] = $data['EvnIds'];
					$RegistryTypeArray[$row['Registry_id']] = $row['RegistryType_id'];
				}
			}
		} else {
			$EvnIdByRegistryArray[$data['Registry_id']] = $data['EvnIds'];
			$RegistryTypeArray[$data['Registry_id']] = $RegistryType_id;
		}

		foreach ($EvnIdByRegistryArray as $Registry_id => $EvnIds) {
			foreach ($EvnIds as $EvnId) {
				$params = array(
					'Evn_id' => $EvnId,
					'Registry_id' => $Registry_id,
					'RegistryData_deleted' => $data['RegistryData_deleted'],
					'RegistryType_id' => $RegistryTypeArray[$Registry_id],
				);

				$query = "
					select
		                Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_RegistryData_del (
						Evn_id := :Evn_id,
						Registry_id := :Registry_id,
						RegistryType_id := :RegistryType_id,
						RegistryData_deleted := :RegistryData_deleted
						);
				";
				$res = $this->db->query($query, $params);
			}
		}

		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление помеченных на удаление записей и пересчет реестра
	 * В региональных моделях: Пермь
	 */
	public function refreshRegistry($data)
	{
		$RegistryType_id = $this->getFirstResultFromQuery("select RegistryType_id as \"RegistryType_id\" from {$this->scheme}.v_Registry where Registry_id = :Registry_id limit 1", $data);

		if ($RegistryType_id == 13) {
			$RegistryArray = $this->queryResult("
				select RGL.Registry_id as \"Registry_id\"
				from {$this->scheme}.v_RegistryGroupLink RGL
				where RGL.Registry_pid = :Registry_id
			", $data);
		} else {
			$RegistryArray = array(array('Registry_id' => $data['Registry_id']));
		}

		if ($RegistryArray !== false) {
			foreach ($RegistryArray as $row) {
				// Обнуляем сумму к оплате
				$this->updateRegistrySumPaid(array('Registry_id' => $row['Registry_id']));

				$query = "
					select
		                Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_RegistryData_Refresh (
						Registry_id := :Registry_id,
						pmUser_id := :pmUser_id
						);
				";
				//echo getDebugSql($query, $data);exit;
				$res = $this->db->query($query, array(
					'Registry_id' => $row['Registry_id'],
					'pmUser_id' => $data['pmUser_id'],
				));

				if (!is_object($res)) {
					return false;
				}

				$response = $res->result('array');

				if (!empty($response[0]['Error_Msg'])) {
					return $response;
				}
			}
		}

		if (isset($response)) {
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *    Помечаем записи реестра на удаление
	 */
	public function deleteRegistryDataAll($data)
	{
		$this->setRegistryParamsByType($data);

		$EvnIds = array();

		if (!empty($data['type'])) {
			switch ($data['type']) {
				case 'RegistryError':
					$query = "
						select
							RE.Evn_id as \"Evn_id\",
							RE.Registry_id as \"Registry_id\",
							R.RegistryType_id as \"RegistryType_id\"
						from
							{$this->scheme}.v_{$this->RegistryErrorObject} RE
							inner join {$this->scheme}.v_Registry R on R.Registry_id = RE.Registry_id
						where
							R.Registry_id = :Registry_id
					";

					$result = $this->queryResult($query, array(
						'Registry_id' => $data['Registry_id']
					));

					if (is_array($result)) {
						foreach ($result as $row) {
							$EvnIds[] = array(
								'Evn_id' => $row['Evn_id'],
								'Registry_id' => $row['Registry_id'],
								'RegistryType_id' => $row['RegistryType_id'],
							);
						}
					}
					break;

				case 'RegistryErrorTFOMS':
					$data['nopaging'] = true;

					if (!empty($data['filters'])) {
						$data = array_merge($data, json_decode($data['filters'], true));
					}

					if ($data['RegistryType_id'] == 13) {
						$RegistryErrorTFOMSData = $this->_getUnionRegistryErrorTFOMS($data);
					} else {
						$RegistryErrorTFOMSData = $this->_loadRegistryErrorTFOMS($data);
					}

					if (is_array($RegistryErrorTFOMSData)) {
						foreach ($RegistryErrorTFOMSData as $row) {
							$EvnIds[] = array(
								'Evn_id' => $row['Evn_id'],
								'Registry_id' => $row['Registry_id'],
								'RegistryType_id' => $row['RegistryType_id'],
							);
						}
					}
					break;
			}
		}

		foreach ($EvnIds as $row) {
			$params = array(
				'Evn_id' => $row['Evn_id'],
				'Registry_id' => $row['Registry_id'],
				'RegistryType_id' => $row['RegistryType_id'],
				'RegistryData_deleted' => $data['RegistryData_deleted'],
			);

			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_RegistryData_del (
					Evn_id := :Evn_id,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					RegistryData_deleted := :RegistryData_deleted
				);
			";
			$resp = $this->queryResult($query, $params);

			if ($resp === false) {
				return false;
			} else if (!empty($resp[0]['Error_Msg'])) {
				return $resp[0];
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Список ошибок ТФОМС предварительного реестра для действия "Удалить все случаи по ошибкам"
	 */
	protected function _loadRegistryErrorTFOMS($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$filterList = array(
			"RE.Registry_id = :Registry_id"
		);
		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		if (!empty($data['Person_SurName'])) {
			$filterList[] = "lower(ps.Person_SurName) like lower(:Person_SurName)";
			$params['Person_SurName'] = $data['Person_SurName'] . "%";
		}

		if (!empty($data['Person_FirName'])) {
			$filterList[] = "lower(ps.Person_FirName) like lower(:Person_FirName)";
			$params['Person_FirName'] = $data['Person_FirName'] . "%";
		}

		if (!empty($data['Person_SecName'])) {
			$filterList[] = "lower(ps.Person_SecName) like lower(:Person_SecName)";
			$params['Person_SecName'] = $data['Person_SecName'] . "%";
		}

		if (!empty($data['RegistryErrorType_Code'])) {
			$filterList[] = "RE.RegistryErrorType_Code = :RegistryErrorType_Code";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}

		if (!empty($data['Person_FIO'])) {
			$filterList[] = "rtrim(COALESCE(ps.Person_SurName, '')) || ' ' || rtrim(COALESCE(ps.Person_FirName, '')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) ilike :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'] . "%";
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filterList[] = "LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if (!empty($data['Evn_id'])) {
			if ($this->RegistryType_id == 6) {
				$filterList[] = "RE.{$this->RegistryDataEvnField} = :Evn_id";
			} else {
				$filterList[] = "RE.Evn_id = :Evn_id";
			}

			$params['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['RegistryErrorTFOMS_Comment'])) {
			$filterList[] = "RE.RegistryErrorTFOMS_Comment ilike '%'||:RegistryErrorTFOMS_Comment||'%'";
			$params['RegistryErrorTFOMS_Comment'] = $data['RegistryErrorTFOMS_Comment'];
		}

		switch ($this->RegistryType_id) {
			case 6:
				$query = "
					select 
						RE.Registry_id as \"Registry_id\",
						RE.{$this->RegistryDataEvnField} as \"Evn_id\",
						R.RegistryType_id as \"RegistryType_id\"
					from 
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join {$this->scheme}.v_Registry R on R.Registry_id = RE.Registry_id
						left join v_CmpCloseCard ccc on ccc.CmpCloseCard_id = RE.{$this->RegistryDataEvnField}
						left join v_LpuSection LS on LS.LpuSection_id = ccc.LpuSection_id
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
						left join lateral (
							select
								PersonEvn_id,
								Server_id,
								Person_BirthDay,
								Polis_id,
								Person_SurName,
								Person_FirName,
								Person_SecName,
								Person_id,
								Person_EdNum
							from v_Person_bdz
							where Person_id = rd.Person_id
								and PersonEvn_insDT <= cast(rd.Evn_setDate as date)
							order by PersonEvn_insDT desc
							limit 1
						) ps on true
					where
						" . implode(' and ', $filterList) . "
				";
				break;

			default:
				$query = "
					select
						RE.Registry_id as \"Registry_id\",
						RE.Evn_id as \"Evn_id\",
						R.RegistryType_id as \"RegistryType_id\"
					from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join {$this->scheme}.v_Registry R on R.Registry_id = RE.Registry_id
						left join v_Evn Evnon Evn.Evn_id = RE.Evn_id
						left join v_LpuSection LS on LS.LpuSection_id = RD.LpuSection_id
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
						left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
					where
						" . implode(' and ', $filterList) . "
				";
				break;
		}

		return $this->queryResult($query, $params);
	}

	/**
	 * Список ошибок ТФОМС объединенного реестра для действия "Удалить все случаи по ошибкам"
	 */
	protected function _getUnionRegistryErrorTFOMS($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		}

		$filterList = array("(1 = 1)");
		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		if (!empty($data['RegistryErrorType_Code'])) {
			$filterList[] = "RE.RegistryErrorType_Code = :RegistryErrorType_Code";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}

		if (!empty($data['Person_FIO'])) {
			$filterList[] = "rtrim(COALESCE(ps.Person_SurName,'')) || ' ' || rtrim(COALESCE(ps.Person_FirName,'')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) ilike :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'] . "%";
		}

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RE.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$query = "
			with RE (
				Evn_id,
				Registry_id,
				RegistryType_id
			) as (
				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id <> 6
					and KN.KatNasel_Code = 1 -- Жители области
					and UR.OrgSmo_id = RD.OrgSmo_id
					and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)

				union all

				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id <> 6
					and KN.KatNasel_Code = 2 -- Иногородние
					and OST.OmsSprTerr_Code > 100
					and OST.OmsSprTerr_Code <> 1135

				union all

				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id <> 6
					and UR.KatNasel_id is null

				union all

				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and KN.KatNasel_Code = 1 -- Жители области
					and UR.OrgSmo_id = RD.OrgSmo_id
					and (OST.OmsSprTerr_Code <= 100 or OST.OmsSprTerr_Code = 1135)

				union all

				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
					inner join v_KatNasel KN on KN.KatNasel_id = UR.KatNasel_id
					inner join v_OmsSprTerr OST on OST.OmsSprTerr_id = RD.OmsSprTerr_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and KN.KatNasel_Code = 2 -- Иногородние
					and OST.OmsSprTerr_Code >= 100
					and OST.OmsSprTerr_Code <> 1135

				union all

				select
					RD.Evn_id,
					RD.Registry_id,
					R.RegistryType_id
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry UR on UR.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RE.Registry_id
						and RD.Evn_id = RE.CmpCloseCard_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and UR.KatNasel_id is null
			)

			select
				RE.Registry_id,
				RE.RegistryType_id,
				RE.Evn_id
			from
				RE
				left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
				left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id
					and ps.Server_id = Evn.Server_id
			where
				" . implode(' and ', $filterList) . "

			union all

			select
				RE.Registry_id,
				RE.RegistryType_id,
				RE.Evn_id
			from
				RE
				left join v_CmpCloseCard CCC on CCC.CmpCloseCard_id = RE.Evn_id
				left join v_PersonState ps on ps.Person_id = CCC.Person_id
			where
				" . implode(' and ', $filterList) . "
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Список случаев по пациентам без документов ОМС
	 */
	public function loadRegistryNoPolis($data)
	{
		if (empty($data['Registry_id'])) {
			return false;
		}

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$filterList = array('RNP.Registry_id = :Registry_id');
		$params = array('Registry_id' => $data['Registry_id']);

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RNP.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['Person_FIO'])) {
			$filterList[] = "rtrim(COALESCE(RNP.Person_SurName,'')) || ' ' || rtrim(COALESCE(RNP.Person_FirName,'')) || ' ' || rtrim(COALESCE(RNP.Person_SecName, '')) ilike '%' || :Person_FIO || '%'";
			$params['Person_FIO'] = $data['Person_FIO'];
		}

		if (!empty($data['Person_OrgSmo'])) {
			$filterList[] = "COALESCE(OrgSMO.Orgsmo_f002smocod, '')|| ' ' ||COALESCE(OrgSMO.OrgSMO_Nick, '') ilike '%' || :Person_OrgSmo || '%'";
			$params['Person_OrgSmo'] = $data['Person_OrgSmo'];
		}

		if (!empty($data['Person_Polis'])) {
			$params['Person_Polis'] = $data['Person_Polis'];
			$filterList[] = "pol.Polis_Num ilike '%' || :Person_Polis || '%'";
		}

		$query = "
			select
				RNP.Registry_id as \"Registry_id\",
				RNP.Evn_id as \"Evn_id\",
				RNP.Evn_rid as \"Evn_rid\",
				RNP.Person_id as \"Person_id\",
				RNP.Server_id as \"Server_id\",
				RNP.PersonEvn_id as \"PersonEvn_id\",
				rtrim(COALESCE(RNP.Person_SurName, '')) || ' ' || rtrim(COALESCE(RNP.Person_FirName, '')) || ' ' || rtrim(COALESCE(RNP.Person_SecName, '')) as \"Person_FIO\",
				to_char(RNP.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				rtrim(LpuSection.LpuSection_Code) || '. ' || LpuSection.LpuSection_Name as \"LpuSection_Name\",
				rtrim(COALESCE(pol.Polis_Ser, '')) || rtrim(COALESCE(' № ' || pol.Polis_Num, '')) as \"Person_Polis\",
				NULLIF(COALESCE(to_char(pol.Polis_begDate, 'dd.mm.yyyy'), '...') || ' - ' || COALESCE(to_char(pol.Polis_endDate, 'dd.mm.yyyy'), '...'), '... - ...') as \"Person_PolisDate\",
				COALESCE(OrgSMO.Orgsmo_f002smocod, '') || ' ' || COALESCE(OrgSMO.OrgSMO_Nick, '') as \"Person_OrgSmo\"
			from
				{$this->scheme}.v_RegistryNoPolis RNP 
				left join v_Person_bdz ps  on ps.PersonEvn_id = RNP.PersonEvn_id
					and ps.Server_id = RNP.Server_id
				left join v_Polis pol on pol.Polis_id = ps.Polis_id
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = pol.OrgSmo_id
				left join v_LpuSection LpuSection on LpuSection.LpuSection_id = RNP.LpuSection_id
			where
				" . implode(' and ', $filterList) . "
			order by
				RNP.Person_SurName,
				RNP.Person_FirName,
				RNP.Person_SecName,
				LpuSection.LpuSection_Name
		";
		$result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);

		$result_count = $this->db->query(getCountSQL($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает признак наличия некорректных случаев в выгрузке реестра в XML
	 */
	public function hasInvalidEvns()
	{
		return (count($this->_invalidEvnList) > 0);
	}

	/**
	 * Возвращает список некорректных случаев в выгрузке реестра в XML
	 */
	public function getInvalidEvnList()
	{
		return $this->_invalidEvnList;
	}

	/**
	 * Формирование массива _RegistryEvnNumByNZAP
	 */
	public function setRegistryEvnNumByNZAP($Registry_EvnNum)
	{
		if (empty($Registry_EvnNum) || !is_array($Registry_EvnNum)) {
			return false;
		}

		foreach ($Registry_EvnNum as $s => $array) {
			if (!empty($array['n'])) {
				if (!isset($this->_RegistryEvnNumByNZAP[$array['n']])) {
					$this->_RegistryEvnNumByNZAP[$array['n']] = array();
				}

				$this->_RegistryEvnNumByNZAP[$array['n']][] = array(
					's' => $s,
					'e' => $array['e'],
					'r' => $array['r'],
				);
			}
		}

		return true;
	}

	/**
	 * Получение данных из _RegistryEvnNumByNZAP для N_ZAP
	 */
	public function getRegistryEvnNumByNZAP($N_ZAP)
	{
		if (empty($N_ZAP) || !isset($this->_RegistryEvnNumByNZAP[$N_ZAP])) {
			return false;
		}

		return $this->_RegistryEvnNumByNZAP[$N_ZAP];
	}

	/**
	 * Установка признака "Оплачен" для Z_SL
	 */
	public function setRegistryDataZSLIsPaid($data)
	{
		$filterList = array(
			'Registry_id = :Registry_id',
		);

		if (array_key_exists($data['Registry_id'], $this->_RegistryList)) {
			$data['RegistryType_id'] = $this->_RegistryList[$data['Registry_id']];
		}

		$this->setRegistryParamsByType($data, true);

		if (!array_key_exists($data['Registry_id'], $this->_RegistryList)) {
			$this->_RegistryList[$data['Registry_id']] = $this->RegistryType_id;
		}

		if (!empty($data['Evn_rid'])) {
			$filterList[] = "Evn_rid = :Evn_rid";
		} else if (!empty($data['Evn_id'])) {
			$data['Evn_rid'] = $this->getFirstResultFromQuery("
				select Evn_rid as \"Evn_rid\"
				from {$this->scheme}.v_{$this->RegistryDataObject}
				where Registry_id = :Registry_id
					and Evn_id = :Evn_id
				limit 1
			", $data);

			if ($data['Evn_rid'] === false) {
				return false;
			}

			$filterList[] = "Evn_rid = :Evn_rid";
		} else {
			return false;
		}

		return $this->getFirstRowFromQuery("

				update {$this->scheme}.{$this->RegistryDataObject}
				set {$this->RegistryDataObject}_IsPaid = :RegistryData_IsPaid
				where " . implode(' and ', $filterList) . ";
		", $data);
	}

	/**
	 *    Переформирование реестра
	 */
	function reformRegistry($data)
	{
		$addToSelect = $this->getReformRegistryAdditionalFields();

		$query = "
			select
				--Registry_id,
				--Lpu_id,
				RegistryType_id as \"RegistryType_id\",
				RegistryStatus_id as \"RegistryStatus_id\",
				RegistryStacType_id as \"RegistryStacType_id\",
				to_char(cast(Registry_begDate as timestamp),'yyyy-mm-dd') as \"Registry_begDate\",
				to_char(cast(Registry_endDate as timestamp),'yyyy-mm-dd') as \"Registry_endDate\",
				KatNasel_id as \"KatNasel_id\",
				LpuBuilding_id as \"LpuBuilding_id\",
				Registry_Num as \"Registry_Num\",
				Registry_Sum as \"Registry_Sum\",
				Registry_IsActive as \"Registry_IsActive\",
				OrgRSchet_id as \"OrgRSchet_id\",
				to_char(cast(Registry_accDate as timestamp),'yyyy-mm-dd') as \"Registry_accDate\"
				{$addToSelect}
			from
				{$this->scheme}.v_Registry Registry
			where
				Registry_id = :Registry_id
		";

		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if (is_object($result)) {
			$row = $result->result('array');
			if (is_array($row) && count($row) > 0) {
				foreach ($row[0] as $key => $value) {
					$data[$key] = $value;
				}
				$data['reformRegistry'] = true;
				// Обнуляем сумму к оплате
				$this->updateRegistrySumPaid($data);
				// Переформирование реестра
				//return  $this->saveRegistry($data);
				// Постановка реестра в очередь
				return $this->saveRegistryQueue($data);
			} else {
				return array('success' => false, 'Error_Msg' => 'Реестр не найден в базе');
			}
		} else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/**
	 * Изменение значения «Сумма к оплате»
	 */
	function updateRegistrySumPaid($data)
	{

		if (!empty($data['Registry_id'])) {
			$query = "
				update
					{$this->scheme}.Registry
				set
					Registry_SumPaid = :Registry_SumPaid
				where
					Registry_id = :Registry_id
			";
			$result = $this->db->query($query,
				array(
					'Registry_id' => $data['Registry_id'],
					'Registry_SumPaid' => !empty($data['Registry_SumPaid']) ? $data['Registry_SumPaid'] : null
				)
			);

			if (is_object($result)) {
				return true;
			} else {
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}

	}

	/**
	 * @param null $Registry_id
	 * @param bool $Registry_IsUnion
	 * @return array|bool
	 */
	public function clearRegistryDataRowNum($Registry_id = null, $Registry_IsUnion = false)
	{
		return $this->getFirstRowFromQuery("
				" . ($Registry_IsUnion === true ? "
				delete from {$this->scheme}.RegistryDataRowNum where Registry_id in (
					select rgl.Registry_id
					from {$this->scheme}.v_RegistryGroupLink as rgl
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					where
						rgl.Registry_pid = :Registry_id
				)" : "
				delete from {$this->scheme}.RegistryDataRowNum where Registry_id = :Registry_id
				") . ";
		", [
			'Registry_id' => $Registry_id,
		]);
	}
}