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

class Perm_Registry_model extends Registry_model {
	var $scheme = "dbo";
	var $region = "perm";
	var $upload_path = 'RgistryFields/';
	var $MaxEvnField = 'RegistryData_RowNum';

	protected $_countSLUCH = 0;
	protected $_countZSL = 0;
	protected $_registryTypeLink = [];

	/**
	 * Возвращает количество выгруженных блоков SLUCH
	 */
	public function getSDZ() {
		return $this->_countSLUCH;
	}

	/**
	 * Возвращает количество выгруженных блоков Z_SL
	 */
	public function getSDZ2018() {
		return $this->_countZSL;
	}

	/**
	 * Проверка включен ли реестр в объединённый
	 */
	function checkRegistryInGroupLink($data) {
		$data['Registry_id'] = $this->getFirstResultFromQuery("
			SELECT rgl.Registry_pid as \"Registry_pid\"
			FROM {$this->scheme}.v_RegistryGroupLink rgl
				inner join {$this->scheme}.v_Registry rf on rf.Registry_id = rgl.Registry_pid
			WHERE rgl.Registry_id = :Registry_id
			LIMIT 1
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($data['Registry_id'])) {
			return true;
		}

		return false;
	}

	/**
	 * Проверка на уникальность номера объединенного реестра
	 */
	public function checkUnionRegistryNumUnique($data) {
		$data['Registry_id'] = $this->getFirstResultFromQuery("
			SELECT Registry_id as \"Registry_id\"
			FROM {$this->scheme}.v_Registry
			WHERE Registry_Num = :Registry_Num
				and date_part('year', Registry_accDate) = date_part('year', CAST(:Registry_accDate as date))
				and Lpu_id = :Lpu_id
				and Registry_id <> coalesce(:Registry_id::bigint, 0)
				and RegistryType_id = 13
			LIMIT 1
			", array(
				'Registry_id'=>$data['Registry_id'],
				'Lpu_id'=>$data['Lpu_id'],
				'Registry_Num'=>$data['Registry_Num'],
				'Registry_accDate'=>$data['Registry_accDate']
			)
		);
		if ( !empty($data['Registry_id']) ) {
			return false;
		}

		return true;
	}

	/**
	 * Проверка на уникальность номера простого реестра
	 * @task https://redmine.swan.perm.ru/issues/99547
	 */
	public function checkRegistryNumUnique($data) {
		return true;
	}

	/**
	 * Проверка на уникальность номера
	 */
	function checkRegistryNumFormat($Registry_Num, $Lpu_id) {

		$reg_num_arr = explode("_", $Registry_Num);

		$LpuRegNum = $this->getFirstResultFromQuery('select coalesce(Lpu_f003mcod, Lpu_interCode) from v_Lpu where Lpu_id = :Lpu_id limit 1', array('Lpu_id'=>$Lpu_id));

		if ($LpuRegNum != $reg_num_arr[0]) {
			return array('Error_Msg' => 'Реестровый номер МО не соответствует МО пользователя.');
		}

		if (substr($reg_num_arr[1], 0, 2) < '01' || substr($reg_num_arr[1], 0, 2) > '12') {
			return array('Error_Msg' => 'Месяц должен быть от 01 до 12.');
		}

		if (!in_array(substr($reg_num_arr[1], 2, 4), array(date('Y'), date('Y')-1))) {
			return array('Error_Msg' => 'Год должен быть текущий или предыдущий.');
		}

		return true;
	}

		/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data)
	{
		if ((0 != $data['Registry_id']))
		{
			$this->setRegistryParamsByType($data);

			if ($this->RegistryType_id == 13) {
				$with = "
					with RD (
						Evn_id,
						Evn_rid,
						RegistryData_ItogSum
					) as (
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryDataEvnPS RDE
							inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
						where
							RGL.Registry_pid = :Registry_id
	
						union all
	
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryData RDE
							inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
						where
							RGL.Registry_pid = :Registry_id
	
						union all
	
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryDataCmp RDE
							inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
						where
							RGL.Registry_pid = :Registry_id
	
						union all
	
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryDataDisp RDE
							inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
						where
							RGL.Registry_pid = :Registry_id
	
						union all
	
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryDataProf RDE
							inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
						where
							RGL.Registry_pid = :Registry_id
	
						union all
	
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_RegistryDataPar RDE
							inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
						where
							RGL.Registry_pid = :Registry_id
					)
				";
			} else {
				$with = "
					with RD (
						Evn_id,
						Evn_rid,
						RegistryData_ItogSum
					) as (
						select
							RDE.Evn_id,
							RDE.Evn_rid,
							RDE.RegistryData_ItogSum
						from
							{$this->scheme}.v_{$this->RegistryDataObject} RDE
						where
							RDE.Registry_id = :Registry_id
					)
				";
			}

			$query = "
				{$with}
				
				select
					RTrim(Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					RegistryType_id as \"RegistryType_id\",
					RegistryStatus_id as \"RegistryStatus_id\",
					Registry_IsNew as \"Registry_IsNew\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					coalesce(R.Registry_Sum::numeric,0) - round(RDSum.RegistryData_ItogSum::numeric,2) as \"Registry_SumDifference\",
					RDSum.RegistryData_Count as \"RegistryData_Count\",
					to_char(Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
					coalesce(R.RegistryCheckStatus_id,0) as \"RegistryCheckStatus_id\",
					coalesce(rcs.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					PayType.PayType_SysNick as \"PayType_SysNick\",
					to_char(Registry_endDate, 'YYMM') as \"Registry_endMonth\" -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
				from {$this->scheme}.Registry R
				left join lateral (
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from RD
				) RDSum on true
				left join RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				left join v_PayType PayType on PayType.PayType_id = R.PayType_id
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
	 * Получение дополнительных полей для сохранения реестра
	 */
	function getSaveRegistryAdditionalFields() {
		return "
			Org_mid := :Org_mid,
			OrgRSchet_mid := :OrgRSchet_mid,
			PayType_id := :PayType_id,
			DispClass_id := :DispClass_id,
			Registry_IsRepeated := :Registry_IsRepeated,
			LpuFilial_id := :LpuFilial_id,
		";
	}

	/**
	 * Получение данных случаев с предыдущих объед. реестров
	 */
	function getOldRegistryEvnNums($data) {
		$oldRegistry_EvnNums = array();
		$filter="";

		if(in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud'))){
			$filter='and PT.PayType_SysNick=:PayType_SysNick';
		}

		$query = "
			with cte as (
				select
					Registry_id,
					Lpu_id,
					Registry_accDate
				from v_Registry
				where Registry_id = :Registry_id
				limit 1
			)
			select
				R.Registry_id as \"Registry_id\",
				R.Registry_EvnNum as \"Registry_EvnNum\",
				R.Registry_Num as \"NSCHET_P\",
				to_char(R.Registry_accDate, 'YYYY-MM-DD') as \"DSCHET_P\"
			from
				v_Registry R
				left join v_PayType PT on PT.PayType_id = R.PayType_id
			where
				R.Lpu_id = (select Lpu_id from cte)
				and R.RegistryType_id = 13
				and R.RegistryStatus_id = 4
				and date_part('year', R.Registry_endDate) >= 2017 -- не ранее 2017 года
				and R.Registry_id <> (select Registry_id from cte)
				and R.Registry_accDate <= (select Registry_accDate from cte) -- ранее текущего реестра
				and R.Registry_EvnNum is not null -- есть соответствия с N_ZAP
				{$filter}
			order by
				R.Registry_accDate desc
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');

			foreach($resp as $index => $respone) {
				if (!empty($respone['Registry_EvnNum'])) {
					$Registry_EvnNum = json_decode($respone['Registry_EvnNum'], true);
					unset($resp[$index]['Registry_EvnNum']);
					foreach($Registry_EvnNum as $key => $Registry_EvnNumOne) {

						if (!isset($this->_registryTypeLink[$Registry_EvnNumOne['Registry_id']])) {
							$r = $this->getFirstResultFromQuery("
								select
									RegistryType_id as \"RegistryType_id\"
								from
									{$this->scheme}.v_Registry
								where
									Registry_id = :Registry_id
							", [
								'Registry_id' => $Registry_EvnNumOne['Registry_id']
							]);

							if($r === false) {
								return array('Error_Msg' => 'Ошибка при определении типа реестра');
							}

							$this->_registryTypeLink[$Registry_EvnNumOne['Registry_id']] = $r;
						}
						
						if (!empty($Registry_EvnNumOne['Evn_id'])) {
							// формируем удобный для использования массив, более новые здесь перезапишутся более старыми, если будут.
							$oldRegistry_EvnNums[$Registry_EvnNumOne['Evn_id']] = array(
								'N_ZAP_P' => isset($Registry_EvnNumOne['N_ZAP']) ? $Registry_EvnNumOne['N_ZAP'] : $key,
								'IDCASE_P' => $key,
								'NSCHET_P' => $respone['NSCHET_P'],
								'DSCHET_P' => $respone['DSCHET_P'],
								'NRTYPE_P' => $this->_registryTypeLink[$Registry_EvnNumOne['Registry_id']]
							);
						}

						if (!empty($Registry_EvnNumOne['Evn_rid'])) {
							// по Evn_rid надо искать для полки
							$oldRegistry_EvnNums[$Registry_EvnNumOne['Evn_rid']] = array(
								'N_ZAP_P' => isset($Registry_EvnNumOne['N_ZAP']) ? $Registry_EvnNumOne['N_ZAP'] : $key,
								'IDCASE_P' => $key,
								'NSCHET_P' => $respone['NSCHET_P'],
								'DSCHET_P' => $respone['DSCHET_P'],
								'NRTYPE_P' => $this->_registryTypeLink[$Registry_EvnNumOne['Registry_id']]
							);
						}
					}
				}
			}
		}

		return $oldRegistry_EvnNums;
	}
	
	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return "
			,Org_mid as \"Org_mid\"
			,OrgRSchet_mid as \"OrgRSchet_mid\"
			,PayType_id as \"PayType_id\"
			,DispClass_id as \"DispClass_id\"
			,Registry_IsRepeated as \"Registry_IsRepeated\"
			,LpuFilial_id as \"LpuFilial_id\"
		";
	}
	
	/**
	 * Проверка возможности удаления реестра
	 */
	function checkDeleteRegistry($data)
	{
		if ($data['id']>0)
		{
			$sql = "
				SELECT
					RS.RegistryStatus_SysNick as \"RegistryStatus_SysNick\",
					RCS.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\"
				FROM {$this->scheme}.v_Registry R
					left join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					left join v_RegistryCheckStatus RCS on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join lateral (
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue
						where Registry_id = R.Registry_id
						limit 1
					) RQ on true
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
				$resp = $res->result('array');
				if (count($resp)>0)
				{
					return ($resp[0]['RegistryStatus_SysNick'] != 'paid' && (empty($resp[0]['RegistryCheckStatus_Code']) || !in_array($resp[0]['RegistryCheckStatus_Code'], array(0,1,3,8))) && $resp[0]['Registry_IsProgress']!=1);
				}
			}
		}
		
		return false;
	}


	/**
	 *	Получение данных реестра для печати
	 */
	function getRegistryFields($data) {
		$filterList = array();
		$queryParams = array();

		$filterList[] = "Registry.Registry_id = :Registry_id";
		$queryParams['Registry_id'] = $data['Registry_id'];

		if ( !isMinZdrav() ) {
			$filterList[] = "Registry.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				RTRIM(Registry.Registry_Num) as \"Registry_Num\",
				coalesce(to_char(cast(Registry.Registry_accDate as timstamp), 'DD.MM.YYYY'), '') as \"Registry_accDate\",
				RTRIM(coalesce(Org.Org_Name, '')) as \"Lpu_Name\",
				coalesce(Lpu.Lpu_RegNomC, '') as \"Lpu_RegNomC\",
				coalesce(Lpu.Lpu_RegNomN, '') as \"Lpu_RegNomN\",
				RTRIM(LpuAddr.Address_Address) as \"Lpu_Address\",
				RTRIM(Org.Org_Phone) as \"Lpu_Phone\",
				ORS.OrgRSchet_RSchet as \"Lpu_Account\",
				OB.OrgBank_Name as \"LpuBank_Name\",
				OB.OrgBank_BIK as \"LpuBank_BIK\",
				Org.Org_INN as \"Lpu_INN\",
				Org.Org_KPP as \"Lpu_KPP\",
				Okved.Okved_Code as \"Lpu_OKVED\",
				Org.Org_OKPO as \"Lpu_OKPO\",
				OO.Oktmo_Code as \"Lpu_OKTMO\",
				date_part('month', Registry.Registry_begDate) as \"Registry_Month\",
				date_part('year', Registry.Registry_begDate) as \"Registry_Year\",
				cast(coalesce(Registry.Registry_Sum, 0.00) as numeric) as \"Registry_Sum\",
				cast(coalesce(Registry.Registry_SumPaid, 0.00) as numeric) as \"Registry_SumPaid\",
				OHDirector.OrgHeadPerson_Fio as \"Lpu_Director\",
				OHGlavBuh.OrgHeadPerson_Fio as \"Lpu_GlavBuh\",
				RT.RegistryType_id as \"RegistryType_id\",
				RT.RegistryType_Code as \"RegistryType_Code\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				RTRIM(coalesce(OrgM.Org_Name, '')) as \"OrgP_Name\",
				RTRIM(OrgMAddr.Address_Address) as \"OrgP_Address\",
				RTRIM(OrgM.Org_Phone) as \"OrgP_Phone\",
				case
					when Registry.Org_mid is not null then ORSM.OrgRSchet_RSchet
					when KN.KatNasel_SysNick = 'inog' then (select OrgRSchet_RSchet from v_OrgRSchet where OrgRSchet_Name = 'Иные территории' limit 1)
					else ''
				end as \"OrgP_RSchet\",
				OMB.OrgBank_Name as \"OrgP_Bank\",
				OMB.OrgBank_BIK as \"OrgP_BankBIK\",
				OrgM.Org_INN as \"OrgP_INN\",
				OrgM.Org_KPP as \"OrgP_KPP\",
				OkvedM.Okved_Code as \"OrgP_OKVED\",
				OrgM.Org_OKPO as \"OrgP_OKPO\",
				OOM.Oktmo_Code as \"OrgP_OKTMO\"
			from {$this->scheme}.v_Registry Registry
				inner join v_Lpu Lpu on Lpu.Lpu_id = Registry.Lpu_id
				inner join v_Org Org on Org.Org_id = Lpu.Org_id
				inner join v_RegistryType RT on RT.RegistryType_id = Registry.RegistryType_id
				left join Okved on Okved.Okved_id = Org.Okved_id
				left join v_Address LpuAddr on LpuAddr.Address_id = Org.UAddress_id
				left join v_OrgRSchet ORS on Registry.OrgRSchet_id = ORS.OrgRSchet_id
				left join v_OrgBank OB on OB.OrgBank_id = ORS.OrgBank_id
				left join v_KatNasel KN on KN.KatNasel_id = Registry.KatNasel_id
				left join lateral (
					select
						substring(RTRIM(PS.Person_FirName), 1, 1) || '.' || substring(RTRIM(PS.Person_SecName), 1, 1) || '. ' || RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH
						inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where
						OH.Lpu_id = Lpu.Lpu_id
						and OH.LpuUnit_id is null
						and OH.OrgHeadPost_id = 1
					limit 1
				) as OHDirector
				left join lateral (
					select
						substring(RTRIM(PS.Person_FirName), 1, 1) || '.' || substring(RTRIM(PS.Person_SecName), 1, 1) || '. ' || RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH
						inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where
						OH.Lpu_id = Lpu.Lpu_id
						and OH.LpuUnit_id is null
						and OH.OrgHeadPost_id = 2
					limit 1
				) as OHGlavBuh
				left join v_Org OrgM on OrgM.Org_id = Registry.Org_mid
				left join v_Okved OkvedM on OkvedM.Okved_id = OrgM.Okved_id
				left join v_Address OrgMAddr on OrgMAddr.Address_id = OrgM.UAddress_id
				left join v_OrgRSchet ORSM on ORSM.OrgRSchet_id = Registry.OrgRSchet_mid
				left join v_OrgBank OMB on OMB.OrgBank_id = ORSM.OrgBank_id
				left join v_Oktmo OO on OO.Oktmo_id = Org.Oktmo_id
				left join v_Oktmo OOM on OOM.Oktmo_id = OrgM.Oktmo_id
			where " . implode(' and ', $filterList) . "
		";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		if ( empty($response[0]['OrgP_Name']) ) {
			// Плательщик по-умолчанию - ТФОМС Пермского края
			$response[0]['OrgP_Address'] = 'г. Пермь, Уральская, 119';
			$response[0]['OrgP_Phone'] = 'ф. 291-50-84, 265-15-38';
			$response[0]['OrgP_KPP'] = '590601001';
			$response[0]['OrgP_OKVED'] = '75.30';
			$response[0]['OrgP_OKPO'] = '35198843';
			$response[0]['OrgP_OKTMO'] = '57701000001';

			if ( $response[0]['KatNasel_SysNick'] == 'inog' ) {
				$response[0]['OrgP_Name'] = 'УФК по Пермскому краю (ТФОМС Пермского края, л/с №03565072730)';
				$response[0]['OrgP_Bank'] = 'ГРКЦ ГУ БАНКА РОССИИ по Пермскому краю г.Пермь';
				$response[0]['OrgP_BankBIK'] = '045773001';
				$response[0]['OrgP_INN'] = '5906071680';
			}
			else {
				$response[0]['OrgP_Name'] = 'Территориальный фонд обязательного медицинского страхования Пермского края';
				$response[0]['OrgP_Bank'] = 'РКЦ Г.ПЕРМЬ';
				$response[0]['OrgP_BankBIK'] = '045744000';
				$response[0]['OrgP_INN'] = '5904071680';
			}

			if ( empty($response[0]['OrgP_RSchet']) ) {
				// Счет по-умолчанию
				$response[0]['OrgP_RSchet'] = '40404810000000010086';
			}
		}

		return $response[0];
	}

	/**
	 *	Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data) {

		$result = array(
			array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
			array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
			array('RegistryType_id' => 16, 'RegistryType_Name' => 'Стоматология'),
			array('RegistryType_id' => 4, 'RegistryType_Name' => 'Дополнительная диспансеризация'),
			array('RegistryType_id' => 5, 'RegistryType_Name' => 'Диспансеризация детей-сирот'),
			array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
			array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года'),
			array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года'),
			array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
			array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'),
			array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
			array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги')
		);

		if (!empty($data['PayType_SysNick'])) {
			switch($data['PayType_SysNick']){
				case 'bud':
					$result = array(
						array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
						array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
						array('RegistryType_id' => 16, 'RegistryType_Name' => 'Стоматология'),
						array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
						array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
						array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги')
					);
					break;
				case 'mbudtrans':
					$result = array(
						array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
						array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
						array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь'),
						array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь')
					);
					break;
			}

		}

		return $result;
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

		if (!empty($data['Registry_id'])) {
			$query = "
				select
					r.Registry_id as \"Registry_id\",
					r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
					rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					r.RegistryType_id as \"RegistryType_id\"
				from
					v_Registry r
					left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
				where
					r.Registry_id = :Registry_id
			";
			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id']
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Registry_id'])) {
					$data['Registry_id'] = $resp[0]['Registry_id'];
					if ($resp[0]['RegistryType_id'] != '13') {
						return array('Error_Msg' => 'Указанный реестр не является объединённым');
					}
					if (!isSuperAdmin() && (!empty($resp[0]['RegistryCheckStatus_id']) && !in_array($resp[0]['RegistryCheckStatus_Code'], array('2','5')))) {
						return array('Error_Msg' => "Нельзя отредактировать объединённый реестр, т.к. его статус: {$resp[0]['RegistryCheckStatus_Name']}");
					} else if (isSuperAdmin() && (!empty($resp[0]['RegistryCheckStatus_id']) && in_array($resp[0]['RegistryCheckStatus_Code'], array('0','1','4','7')))) {
						return array('Error_Msg' => "Нельзя отредактировать объединённый реестр, т.к. его статус: {$resp[0]['RegistryCheckStatus_Name']}");
					}
				}
			}
		}

		$join='';
		$data['PayType_id'] = null;
		$ptFilter = "and coalesce(PT.PayType_SysNick, '') not in ('ovd', 'bud', 'fbud', 'mbudtrans','mbudtrans_mbud')";
		$statusFilter = " and (
							R.RegistryStatus_id = 5 -- проверенные ТФОМС
							or (
								R.RegistryStatus_id = 2 -- К оплате
								and RCS.RegistryCheckStatus_Code = 1 -- Отправлен в ТФОМС
							)
						)";
		if(!empty($data['PayType_SysNick']) && in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud'))){
			$join='left join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id';
			$data['PayType_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", $data);
			$ptFilter = " and PT.PayType_id = :PayType_id ";
			$statusFilter = " and
								( 
									(RCS.RegistryCheckStatus_SysNick in ('ReadyTFOMS','SendTFOMS') and RS.RegistryStatus_SysNick='forpay') 
										or
									(RCS.RegistryCheckStatus_SysNick in ('ControlMEK','ControlFLK','ErrorFLK','ControlBDZ','ErrorBDZ','ErrorRegistry','Accept','HalfAccept','NotAccept') and RS.RegistryStatus_SysNick='tfoms')
								)
			";
		}

		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "			
			select
				Registry_id as \"Registry_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.{$proc} (
				Registry_id := :Registry_id,
				RegistryType_id := 13,
				RegistryStatus_id := 1,
				RegistryQueue_id := null,
				Registry_Sum := NULL,
				Registry_IsActive := 2,
				KatNasel_id := null,
				Registry_Num := :Registry_Num,
				PayType_id := :PayType_id,
				Registry_accDate := :Registry_accDate,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				Lpu_id := :Lpu_id,
				Registry_IsNew := :Registry_IsNew,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
				// 2. удаляем все связи
				$this->deleteRegistryGroupLink(array(
					'Registry_pid' => $resp[0]['Registry_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				// 3. выполняем поиск реестров которые войдут в объединённый
				$query = "
					select
						R.Registry_id as \"Registry_id\",
						R.Registry_Num as \"Registry_Num\",
						to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
						RT.RegistryType_Name as \"RegistryType_Name\",
						RETF.FLKCount as \"FLKCount\"
					from
						{$this->scheme}.v_Registry R
						left join v_PayType PT on PT.PayType_id = R.PayType_id
						left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
						left join v_RegistryCheckStatus RCS on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
						{$join}
						left join lateral (
							select count(RegistryErrorTFOMS_id) as FLKCount from dbo.v_RegistryErrorTFOMS where RegistryErrorTFOMSLevel_id = 1 and Registry_id = R.Registry_id
						) RETF on true
					where
						R.RegistryType_id <> 13
						and coalesce(R.Registry_IsNew, 1) = :Registry_IsNew
						{$ptFilter}
						{$statusFilter}
						and R.Lpu_id = :Lpu_id
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and not exists(
							select t1.RegistryGroupLink_id
							from {$this->scheme}.v_RegistryGroupLink t1
								inner join {$this->scheme}.v_Registry t2 on t2.Registry_id = t1.Registry_pid
							where t1.Registry_id = R.Registry_id limit 1
						)
				";
				$result_reg = $this->db->query($query, array(
					'Lpu_id' => $data['Lpu_id'],
					'Registry_IsNew' => !empty($data['Registry_IsNew']) ? $data['Registry_IsNew'] : 1,
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate'],
					'PayType_id' => $data['PayType_id']
				));

				$RegistrysFLKMore100 = "";

				if (is_object($result_reg))
				{
					$resp_reg = $result_reg->result('array');
					// 4. сохраняем новые связи
					foreach($resp_reg as $one_reg) {
						/*if ($one_reg['FLKCount'] > 100) {
							if (!empty($RegistrysFLKMore100)) {
								$RegistrysFLKMore100 .= ', ';
							}
							$RegistrysFLKMore100 .= $one_reg['Registry_Num'].' от '.$one_reg['Registry_accDate'].' ('.$one_reg['RegistryType_Name'].')';
							continue;
						}*/
						$query = "
							select
								RegistryGroupLink_id as \"RegistryGroupLink_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from {$this->scheme}.p_RegistryGroupLink_ins (
								RegistryGroupLink_id := :RegistryGroupLink_id output,
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
				}

				if (!empty($RegistrysFLKMore100)) {
					$resp[0]['RegistrysFLKMore100'] = $RegistrysFLKMore100;
				}

				//Выполняем вычисление суммы без ошибок
				$this->setNoErrorSum($resp[0]['Registry_id']);

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
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
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
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					4 as \"RegistryStatus_id\"
				from {$this->scheme}.p_Registry_setPaid (
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'Registry_id' => $respone['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
			}

			$resp_set = $result->result('array');
			if (!empty($resp_set[0]['Error_Msg'])) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный: ' . $resp_set[0]['Error_Msg']));
			} else if (empty($resp_set[0]['RegistryStatus_id'])) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
			}
		}

		$query = "
			CREATE OR REPLACE FUNCTION pg_temp.setUnionRegistryStatus_Query(
				in Registry_id bigint,
				in pmUser_id bigint,
				inout RegistryStatus_id bigint,
				out Error_Code int,
				out Error_Message text
			)
			RETURNS record
			LANGUAGE 'plpgsql'
			AS $$
			DECLARE
				p_Registry_Sum numeric;
				p_Registry_SumPaid numeric;
				p_Registry_NoErrSum numeric;
				p_Registry_id bigint;
				p_RegistryStatus_id bigint;
				p_RegistryCheckStatus_id bigint;
				p_pmUser_id bigint;
			BEGIN
				p_Registry_id = Registry_id;
				p_RegistryStatus_id = RegistryStatus_id;
				p_pmUser_id = pmUser_id;
				
				p_RegistryCheckStatus_id = (
					case
						when exists(
							select
								r.Registry_id
							from
								v_Registry r
								inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
								inner join v_RegistryGroupLink rgl on rgl.Registry_id = r.Registry_id
							where
								rgl.Registry_pid = p_Registry_id
								and rcs.RegistryCheckStatus_Code = 3 -- принят частично
							limit 1
						)
					then
						(select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = 3 limit 1)
					else
						(select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = 8 limit 1)
					end
				);
				
				Select
					SUM(coalesce(R.Registry_Sum,0)),
					SUM(coalesce(R.Registry_SumPaid,0)),
					SUM(coalesce(R.Registry_NoErrSum,0))
				into
					p_Registry_Sum,
					p_Registry_SumPaid,
					p_Registry_NoErrSum
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id=RGL.Registry_id
				where
					RGL.Registry_pid = p_Registry_id;
				
				update {$this->scheme}.Registry r
				set
					RegistryStatus_id = p_RegistryStatus_id,
					Registry_updDT = dbo.tzGetDate(),
					RegistryCheckStatus_id = p_RegistryCheckStatus_id,
					Registry_Sum = p_Registry_Sum,
					Registry_SumPaid = p_Registry_SumPaid,
					Registry_NoErrSum = p_Registry_NoErrSum,
					pmUser_updID = p_pmUser_id
				where
					r.Registry_id = p_Registry_id;
			
				exception
            	    when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
			END;
			$$;
			
			select 
				RegistryStatus_id as \"RegistryStatus_id\", 
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from pg_temp.setUnionRegistryStatus_Query(
				Registry_id := :Registry_id,
				RegistryStatus_id := :RegistryStatus_id,
				pmUser_id := :pmUser_id
			);
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
	 * Импорт ошибок МЭК от СМО
	 */
	function importRegistryFromXml($data)
	{
		$upload_path = './'.IMPORTPATH_ROOT.'importRegistryFromXml/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');

		if (!isset($_FILES['RegistryFile'])) {
			return array('Error_Msg' => 'Не выбран файл реестра!');
		}

		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			return array('Error_Msg' => $message);
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			return array('Error_Msg' => 'Данный тип файла не разрешен.');
		}

		// Правильно ли указана директория для загрузки?
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			if ($folders[$i] == '') {continue;}
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		if (!@is_dir($upload_path)) {
			return array('Error_Msg' => 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			return array('Error_Msg' => 'Загрузка файла не возможна из-за прав пользователя.');
		}

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				return array('Error_Msg' => 'Не удаётся переместить файл.');
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$xmlfile = "";

				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*.xml/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xmlfile))
		{
			return array('Error_Msg' => 'Файл не является протоколом МЭК.');
		}

		$recall = 0;
		$errors = "";

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);

		foreach (libxml_get_errors() as $error) {
			return array('Error_Msg' => 'Файл не является протоколом МЭК.');
		}

		libxml_clear_errors();

		// 1. получаем имя файла протокола
		$FNAME = "";
		$dom_fname = $dom->getElementsByTagName('FNAME');
		foreach($dom_fname as $dom_onefname) {
			$FNAME = $dom_onefname->nodeValue;
		}

		if (empty($FNAME)) {
			return array('Error_Msg' => 'Ошибка получения имени файла');
		}

		// 2. получаем исходное имя реестра
		$FNAME_1 = "";
		$dom_fname = $dom->getElementsByTagName('FNAME_1');
		foreach($dom_fname as $dom_onefname) {
			$FNAME_1 = $dom_onefname->nodeValue;
		}

		if (empty($FNAME)) {
			return array('Error_Msg' => 'Ошибка получения имени реестра');
		}

		if (!preg_match('/^MEK_S([0-9]*)M([0-9]*)\_([0-9]*)$/', $FNAME)) {
			return array('Error_Msg' => 'Некорректное наименование файла протокола СМО');
		}


		// 3. При импорте протокола проверить, что он от нужного реестра и СМО
		// 3.1. берём код СМО из имени файла протокола (код между HM и T)
		$data['Orgsmo_f002smocod'] = preg_replace("/MEK_S([0-9]*)M.*/","$1", $FNAME);
		if (empty($data['Orgsmo_f002smocod'])) {
			return array('Error_Msg' => 'Ошибка получения кода СМО');
		}
		// 3.2. ищем СМО в БД
		$data['OrgSMO_id'] = $this->getFirstResultFromQuery("
			select
				OrgSMO_id as \"OrgSMO_id\"
			from
				v_OrgSMO
			where
				Orgsmo_f002smocod = :Orgsmo_f002smocod
		", $data);
		if (empty($data['OrgSMO_id'])) {
			return array('Error_Msg' => 'Не найдена СМО с кодом '.$data['Orgsmo_f002smocod']);
		}
		// 3.3. берём код МО из исходного имени реестра
		$data['Lpu_f003mcod'] = preg_replace("/.*M([0-9]*)[TS].*/","$1", $FNAME_1);
		if (empty($data['Lpu_f003mcod'])) {
			return array('Error_Msg' => 'Ошибка получения кода МО');
		}
		// 3.4. ищем МО в БД
		$data['Lpu_id'] = $this->getFirstResultFromQuery("
			select
				Lpu_id as \"Lpu_id\"
			from
				v_Lpu
			where
				Lpu_f003mcod = :Lpu_f003mcod
		", $data);
		if (empty($data['Lpu_id'])) {
			return array('Error_Msg' => 'Не найдена МО с кодом '.$data['Lpu_f003mcod']);
		}
		// 3.5. берём дату из исходного имени реестра
		$data['Registry_xmlExpDT'] = preg_replace("/.*_([0-9]{4})1/","$1", $FNAME_1);
		if (empty($data['Registry_xmlExpDT'])) {
			return array('Error_Msg' => 'Ошибка получения даты реестра');
		}
		// 4. проверяем что реестр выгружен тем же месяцем и реестр от той же МО
		$query = "
			select
				Registry_id as \"Registry_id\",
				Lpu_id as \"Lpu_id\",
				SUBSTRING(cast(date_part('year', Registry_xmlExpDT) as varchar),3,2) || RIGHT('0' || cast(date_part('month', Registry_xmlExpDT) as varchar), 2) as \"Registry_xmlExpDT\"
			from
				{$this->scheme}.v_Registry
			where
				Registry_id = :Registry_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (empty($resp[0]['Registry_id'])) {
				return array('Error_Msg' => 'Реестр не найден');
			}
			if ($resp[0]['Lpu_id'] != $data['Lpu_id']) {
				return array('Error_Msg' => 'МО реестра не соответствует файлу протокола');
			}
			if ($resp[0]['Registry_xmlExpDT'] != $data['Registry_xmlExpDT']) {
				return array('Error_Msg' => 'Дата выгрузки реестра не соответсвутет дате в файле протокола');
			}
		}

		// 5. достаём массив Registry_EvnNum
		$Registry_EvnNum = null;
		$query = "
			select
				Registry_EvnNum as \"Registry_EvnNum\"
			from
				{$this->scheme}.v_Registry
			where
				Registry_id = :Registry_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				$Registry_EvnNum = $row[0]['Registry_EvnNum'];
			}
		}

		if (empty($Registry_EvnNum)) {
			return array('Не заполнено поле Registry_EvnNum, импорт ошибок невозможен');
		}

		$Registry_EvnNum = json_decode($Registry_EvnNum, true);

		$result = $this->db->query($query, $data);

		$Registry_Task = 'x_'.time().'_'.rand(10000,99999);

		// 6. грузим новые ошибки
		$dom_pr = $dom->getElementsByTagName('PR');
		foreach($dom_pr as $dom_onepr) {
			$recall++;

			$Evn_id = null;
			$Registry_id = null;
			$RegistryErrorType_id = null;
			$OSHIB = null;
			$IM_POL = null;
			$BAS_EL = null;
			$COMMENT = null;
			$SEVERITY = null;
			$IDCASE = null;

			$dom_oshib = $dom_onepr->getElementsByTagName('OSHIB');
			foreach($dom_oshib as $dom_oneoshib) {
				$OSHIB = $dom_oneoshib->nodeValue;
			}

			$dom_im_pol = $dom_onepr->getElementsByTagName('IM_POL');
			foreach($dom_im_pol as $dom_oneim_pol) {
				$IM_POL = $dom_oneim_pol->nodeValue;
			}

			$dom_bas_el = $dom_onepr->getElementsByTagName('BAS_EL');
			foreach($dom_bas_el as $dom_onebas_el) {
				$BAS_EL = $dom_onebas_el->nodeValue;
			}

			$dom_comment = $dom_onepr->getElementsByTagName('COMMENT');
			foreach($dom_comment as $dom_onecomment) {
				$COMMENT = $dom_onecomment->nodeValue;
			}

			$dom_severity = $dom_onepr->getElementsByTagName('SEVERITY');
			foreach($dom_severity as $dom_oneseverity) {
				$SEVERITY = $dom_oneseverity->nodeValue;
			}

			$dom_idcase = $dom_onepr->getElementsByTagName('IDCASE');
			foreach($dom_idcase as $dom_oneidcase) {
				$IDCASE = $dom_oneidcase->nodeValue;
			}

			if (!empty($IDCASE)) {
				// определяем случай по IDCASE
				if (!empty($Registry_EvnNum[$IDCASE]['Evn_id']) && !empty($Registry_EvnNum[$IDCASE]['Registry_id'])) {
					$Evn_id = $Registry_EvnNum[$IDCASE]['Evn_id'];
					$Registry_id = $Registry_EvnNum[$IDCASE]['Registry_id'];
				}

				if (
					!isset($this->_registryTypesList[$Registry_id])
					|| $this->RegistryType_id != $this->_registryTypesList[$Registry_id]
				) {
					$this->setRegistryParamsByType(['Registry_id' => $Registry_id], true);
				}

				// проверяем СМО в случае
				$OrgSMO_id = $this->getFirstResultFromQuery("
					select
						p.OrgSMO_id as \"OrgSMO_id\"
					from {$this->scheme}.v_{$this->RegistryDataObject} rd
						inner join v_Person_all ps on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
						inner join v_Polis p on p.Polis_id = ps.Polis_id
					where
						rd.Evn_id = :Evn_id
						and rd.Registry_id = :Registry_id
					limit 1
				", array('Registry_id' => $Registry_id, 'Evn_id' => $Evn_id));

				if (empty($OrgSMO_id) || $OrgSMO_id != $data['OrgSMO_id']) {
					$errors .= 'СМО в случае с IDCASE = '.$IDCASE.' отличается от СМО файла протокола.'.PHP_EOL;
				} else if (empty($Evn_id) || empty($Registry_id)) {
					$errors .= 'Не удалось найти в реестре случай с IDCASE = '.$IDCASE.'.'.PHP_EOL;
				} else {
					$params = array(
						'Registry_id' => $Registry_id,
						'Evn_id' => null,
						'CmpCallCard_id' => null,
						'RegistryErrorTFOMSType_id' => 3, // Ошибки МЭК
						'RegistryErrorType_Code' => $OSHIB,
						'RegistryErrorTFOMS_FieldName' => $IM_POL,
						'RegistryErrorTFOMS_BaseElement' => $BAS_EL,
						'RegistryErrorTFOMS_Comment' => $COMMENT,
						'RegistryErrorTFOMS_Severity' => $SEVERITY,
						'RegistryErrorTFOMS_IdCase' => $IDCASE,
						'Registry_Task' => $Registry_Task,
						'OrgSMO_id' => $data['OrgSMO_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					// @task https://jira.is-mis.ru/browse/PROMEDWEB-5220
					// Для СМП ошибки пишутся с заполнением поля CmpCallCard_id
					$params[$this->RegistryDataEvnField] = $Evn_id;

					$resp_save = $this->saveRegistryErrorTFOMS($params);
				}
			}
		}

		// запускаем парсилку p_RegistryErrorTFOMS_parse
		$query = "
			select
				Error_Code as Error_Code,
				Error_Message as Error_Msg
			from {$this->scheme}.p_RegistryErrorTFOMS_parse (
				Registry_id = :Registry_id,
				Registry_Task = :Registry_Task,
				RegistryErrorTFOMSType_id = 3,
				OrgSMO_id = :OrgSMO_id,
				pmUser_id = :pmUser_id
			)
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'Registry_Task' => $Registry_Task,
			'OrgSMO_id' => $data['OrgSMO_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (is_object($result))
		{
			$resp = $result->result('array');
		}


		//Выполняем вычисление суммы без ошибок
		$this->setNoErrorSum($data['Registry_id']);

		$errorFile = null;
		if (!empty($errors)) {
			// записываем ошибки, отдаем пользователю файл
			$out_dir = "re_xmlimport_".time()."_".$data['Registry_id'];
			mkdir( EXPORTPATH_REGISTRY.$out_dir );
			$errorFile = EXPORTPATH_REGISTRY.$out_dir."/errors.txt";
			file_put_contents($errorFile, $errors);
		}

		return array('Error_Msg' => '', 'recAll' => $recall, 'Registry_id' => $data['Registry_id'], 'errorFile' => $errorFile);
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid($data)
	{
		$filter = "";
		if (!empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2) {
			$filter .= " and R.Registry_IsNew = 2";
		} else {
			$filter .= " and coalesce(R.Registry_IsNew, 1) = 1";
		}

		if(!empty($data['PayType_SysNick']) && (empty($data['Registry_IsNew']) || $data['Registry_IsNew'] == 1)){

			if($data['PayType_SysNick'] == 'mbudtrans'){
				$filter .= "and PT.PayType_SysNick in ('mbudtrans','mbudtrans_mbud') ";
			}else {
				$filter .= " and coalesce(PT.PayType_SysNick, 'oms') = :PayType_SysNick ";
			}
		}

		$query = "
		Select
			-- select
			R.Registry_id as \"Registry_id\",
			R.Registry_Num as \"Registry_Num\",
			to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
			to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
			to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
			coalesce(RS.Registry_Sum, 0.00) as \"Registry_Sum\",
			coalesce(RS.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
			R.RegistryStatus_id as \"RegistryStatus_id\",
			R.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
			RCS.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\",
			coalesce('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:' || CAST(R.Registry_id as varchar) || '});''>'||rcs.RegistryCheckStatus_Name||'</a>','') as \"RegistryCheckStatus_Name\",
			RegistryErrorFLK.FlkErrors_IsData as \"FlkErrors_IsData\",
			RegistryErrorMEK.MekErrors_IsData as \"MekErrors_IsData\",
			RegistryErrorBDZ.BdzErrors_IsData as \"BdzErrors_IsData\",
			RegistryCount.Registry_Count as \"Registry_Count\",
			coalesce(to_char(R.Registry_sendDT,'DD.MM.YYYY HH24:MI:SS'),'') as \"Registry_sendDate\",
			RS.Registry_NoErrSum as \"Registry_NoErrSum\",
			PT.PayType_SysNick as \"PayType_SysNick\",
			PT.PayType_Name as \"PayType_Name\",
			to_char(R.Registry_updDT,'DD.MM.YYYY') as \"Registry_updDT\"
			-- end select
		from
			-- from
			{$this->scheme}.v_Registry R -- объединённый реестр
			left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			left join v_PayType PT on PT.PayType_id = R.PayType_id
			left join lateral (
				select
					SUM(coalesce(R2.Registry_SumPaid,0)) as Registry_SumPaid,
					SUM(coalesce(R2.Registry_Sum,0)) as Registry_Sum,
					SUM(coalesce(R2.Registry_NoErrSum,0)) as Registry_NoErrSum
				from {$this->scheme}.v_Registry R2
					inner join {$this->scheme}.v_RegistryGroupLink RGL on R2.Registry_id = RGL.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
			) RS on true
			left join lateral (
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
			left join lateral (
				select case when RE.Registry_id is not null then 1 else 0 end as MekErrors_IsData
				from
					{$this->scheme}.v_RegistryErrorTFOMS RE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RE.Registry_id
					left join RegistryErrorTFOMSType RET on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
				where RGL.Registry_pid = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_MEK'
				limit 1
			) RegistryErrorMEK on true
			left join lateral (
				select
					case when RE.Registry_id is not null then 1 else 0 end as BdzErrors_IsData
				from
					{$this->scheme}.v_RegistryErrorBDZ RE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RE.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
				limit 1
			) RegistryErrorBDZ on true
			left join lateral (
				select
					SUM(RSIMPLE.Registry_RecordCount) as Registry_Count
				from
					dbo.v_Registry RSIMPLE
					inner join dbo.v_RegistryGroupLink RGL on RGL.Registry_id = RSIMPLE.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
			) RegistryCount on true
			-- end from
		where
			-- where
			R.Lpu_id = :Lpu_id
			and R.RegistryType_id = 13
			{$filter}
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
				to_char(R.Registry_accDate,'DD.MM.YYYY') as \"Registry_accDate\",
				to_char(R.Registry_begDate,'DD.MM.YYYY') as \"Registry_begDate\",
				to_char(R.Registry_endDate,'DD.MM.YYYY') as \"Registry_endDate\",
				R.Lpu_id as \"Lpu_id\",
				R.PayType_id as \"PayType_id\"
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
				v_Registry r
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
			from {$this->scheme}.p_Registry_del (
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
			to_char(R.Registry_accDate,'DD.MM.YYYY') as \"Registry_accDate\",
			to_char(R.Registry_begDate,'DD.MM.YYYY') as \"Registry_begDate\",
			to_char(R.Registry_endDate,'DD.MM.YYYY') as \"Registry_endDate\",
			KN.KatNasel_Name as \"KatNasel_Name\",
			RT.RegistryType_Name as \"RegistryType_Name\",
			coalesce(R.Registry_Sum, 0.00) as \"Registry_Sum\",
			coalesce(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
			PT.PayType_Name as \"PayType_Name\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			coalesce(to_char(R.Registry_sendDT, 'DD.MM.YYYY HH24:MI:SS'), '') as \"Registry_sendDate\",
			coalesce(R.Registry_RecordCount, 0) as \"Registry_Count\",
			R.Registry_NoErrSum as \"Registry_NoErrSum\",
			coalesce('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:' || CAST(R.Registry_id as varchar) || '});''>'||RegistryCheckStatus.RegistryCheckStatus_Name||'</a>','') as \"RegistryCheckStatus_Name\",
			to_char(R.Registry_updDT,'DD.MM.YYYY') as \"Registry_updDate\"
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
	 *	Установка статуса реестра
	 */
	public function setRegistryStatus($data) {
		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			throw new Exception("Пустые значения входных параметров");
		}

		$resp_reg = $this->queryResult("
			select
				r.Registry_id as \"Registry_id\",
				r.RegistryStatus_id as \"RegistryStatus_id\",
				r.RegistryType_id as \"RegistryType_id\",
				r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				pt.PayType_SysNick as \"PayType_SysNick\"
			from
				{$this->scheme}.v_Registry r
				left join v_PayType pt on pt.PayType_id = r.PayType_id
			where
				r.Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (empty($resp_reg[0]['Registry_id'])) {
			throw new Exception("Ошибка получения данных реестра");
		}

		if ($data['RegistryStatus_id'] == 4 && !in_array($resp_reg[0]['PayType_SysNick'], array('bud', 'fbud'))) {
			throw new Exception("Перевод в оплаченные запрещён");
		}

		//#11018 При статусах "Готов к отправке в ТФОМС" и "Отправлен в ТФОМС" запретить перемещать реестр из состояния "К оплате".
		if ( !isSuperAdmin() ) {
			//"Готов к отправке в ТФОМС"
			if ($resp_reg[0]['RegistryCheckStatus_id'] == 1) {
				throw new Exception("При статусе 'Готов к отправке в ТФОМС' запрещено перемещать реестр из состояния 'К оплате'");
			}
			//"Отправлен в ТФОМС"
			if ($resp_reg[0]['RegistryCheckStatus_id'] == 2) {
				throw new Exception("При статусе 'Отправлен в ТФОМС' запрещено перемещать реестр из состояния 'К оплате'");
			}
			//"Принят частично"
			if ($resp_reg[0]['RegistryCheckStatus_id'] == 4) {
				throw new Exception("При статусе 'Принят частично' запрещено перемещать реестр из состояния 'К оплате'");
			}
			//"Проведён контроль (ФЛК)"
			if ($resp_reg[0]['RegistryCheckStatus_id'] == 5) {
				throw new Exception("При статусе 'Проведен контроль (ФЛК)' запрещено перемещать реестр из состояния 'К оплате'");
			}
			//"Принят"
			if ($resp_reg[0]['RegistryCheckStatus_id'] == 15) {
				throw new Exception("При статусе 'Принят' запрещено перемещать реестр из состояния 'К оплате'");
			}
		}

		$data['RegistryType_id'] = $resp_reg[0]['RegistryType_id'];

		$this->setRegistryParamsByType($data);

		$fields = "";

		if ( $data['RegistryStatus_id'] == 3 ) {
			// если перевели в работу, то снимаем признак формирования
			$fields .= "Registry_ExportPath := null, Registry_xmlExportPath := null, Registry_xmlExpDT := null, RegistryCheckStatus_id := null, ";
		}

		if ( $data['RegistryStatus_id'] == 2 && in_array($this->RegistryType_id, $this->getAllowedRegistryTypes()) ) {
			// если переводим "к оплате", проверка установлена и это не суперадмин, то проверяем на ошибки
			if ( isset($data['session']['setting']['server']['check_registry_exists_errors']) && $data['session']['setting']['server']['check_registry_exists_errors']==1 && !isSuperadmin() ) {
				$query = "
					Select
					(
						Select count(*) as err
						from {$this->scheme}.v_{$this->RegistryErrorObject} RegistryError
							left join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = RegistryError.Registry_id and rd.Evn_id = RegistryError.Evn_id
							left join RegistryErrorType  on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
						where RegistryError.registry_id = :Registry_id
							and RegistryErrorType.RegistryErrorClass_id = 1
							and RegistryError.RegistryErrorClass_id = 1
							and coalesce(rd.RegistryData_deleted,1)=1
							and rd.Evn_id is not null
					) +
					(
						Select count(*) as err
						from {$this->scheme}.v_{$this->RegistryErrorComObject} RegistryErrorCom
							left join RegistryErrorType  on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
						where registry_id = :Registry_id
							and RegistryErrorType.RegistryErrorClass_id = 1
					)
					as err
				";

				$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
				if ( is_object($r) ) {
					$res = $r->result('array');

					if ( is_array($res) && count($res) > 0 && $res[0]['err'] > 0 ) {
						throw new Exception('Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.');
					}
				}
			}

			// https://redmine.swan.perm.ru/issues/63102
			$query = "
				select Evn_id
				from {$this->scheme}.v_{$this->RegistryDataObject}
				where Registry_id = :Registry_id
					and RegistryData_deleted = 2
				limit 1
			";
			$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			if ( is_object($r) ) {
				$res = $r->result('array');
				if ( is_array($res) && count($res) > 0 && !empty($res[0]['Evn_id']) ) {
					throw new Exception('Обнаружены удаленные записи! Необходимо произвести пересчет реестра перед тем, как переводить его к оплате.');
				}
			}
		}

		$this->beginTransaction();

		// если переводим к оплате p_Registry_setUnPaid
		// и при этом текущий статус "Оплаченные"
		// @task https://redmine.swan.perm.ru/issues/86197
		// Проверка на вхождение случаев в другие реестры
		// @task https://redmine.swan.perm.ru/issues/110861
		if ( $resp_reg[0]['RegistryStatus_id'] == 4 && $data['RegistryStatus_id'] == 2 ) {
			$check110861 = $this->checkRegistryDataIsInOtherRegistry($data);

			if ( !empty($check110861) ) {
				throw new Exception($check110861);
			}

			$query = "
				select
					Error_Code as \"Error_Code\", 
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_setUnPaid (
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				throw new Exception('Ошибка при выполнении запроса к базе данных (отметка к оплате)');
			}

			$res = $result->result('array');

			if ( !is_array($res) || count($res) == 0 ) {
				$this->rollbackTransaction();
				throw new Exception('Ошибка при отметке к оплате');
			}
			else if ( !empty($res[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				throw new Exception($res[0]['Error_Msg']);
			}
		}

		$query = "
			update {$this->scheme}.Registry
			set
				RegistryStatus_id = :RegistryStatus_id,
				Registry_updDT = dbo.tzGetDate(),
				{$fields}
				pmUser_updID = :pmUser_id
			where
				Registry_id = :Registry_id
			returning :RegistryStatus_id as RegistryStatus_id, null as Error_Code, null as Error_Msg
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			throw new Exception('Ошибка при выполнении запроса к базе данных (смена статуса реестра)');
		}

		$res = $result->result('array');

		if ( !is_array($res) || count($res) == 0 ) {
			$this->rollbackTransaction();
			throw new Exception('Ошибка при отметке к оплате');
		}
		else if ( !empty($res[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			throw new Exception($res[0]['Error_Msg']);
		}

		if ( $data['RegistryStatus_id'] == 4 ) {
			// пишем информацию о смене статуса в историю
			$this->dumpRegistryInformation(array('Registry_id' => $data['Registry_id']), 4);
		}

		$this->commitTransaction();

		return $res;
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
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryErrorComObject = 'RegistryErrorComEvnPS';
				$this->RegistryPersonObject = 'RegistryPersonEvnPS';
			break;

			case 2:
			case 16:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryErrorObject = 'RegistryError';
				$this->RegistryErrorComObject = 'RegistryErrorCom';
				$this->RegistryPersonObject = 'RegistryPerson';
			break;

			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryErrorObject = 'RegistryErrorCmp';
				$this->RegistryErrorComObject = 'RegistryErrorComCmp';
				$this->RegistryPersonObject = 'RegistryPersonCmp';
				$this->RegistryDataEvnField = 'CmpCallCard_id';
			break;

			case 4:
			case 5:
			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
				$this->RegistryErrorComObject = 'RegistryErrorComDisp';
				$this->RegistryPersonObject = 'RegistryPersonDisp';
			break;

			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
				$this->RegistryErrorComObject = 'RegistryErrorComProf';
				$this->RegistryPersonObject = 'RegistryPersonProf';
			break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryErrorObject = 'RegistryErrorPar';
				$this->RegistryErrorComObject = 'RegistryErrorComPar';
				$this->RegistryPersonObject = 'RegistryPersonPar';
			break;
		}
	}

	/**
	 * Получение списка типов реестров, входящих в объединенный реестр
	 */
	function getUnionRegistryTypes($Registry_pid = 0) {
		$query = "
			select distinct r.RegistryType_id as \"RegistryType_id\"
			from {$this->scheme}.v_RegistryGroupLink rgl
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_pid and r.RegistryType_id in (1,2,6,7,9,11,12,14,15,16)
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
	 *	Получение данных для выгрузки объединенного реестра в XML
	 */
	function loadRegistrySCHETForXmlUsingCommonUnion($data)
	{
		$p_schet = $this->scheme.".p_Registry_expScet_2015";

		// шапка
		$query = "
			select * from {$p_schet}(Registry_id := ?)
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');
			if (!empty($header[0])) {
				array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);
				return array(array_change_key_case($header[0], CASE_UPPER));
			}
		}

		return false;
	}

	/**
	 *	Получение данных для выгрузки простого реестра в XML
	 */
	function loadRegistrySCHETForXmlUsingCommonCustom($type, $data) {
		$before2015 = $data['before2015'];

		switch ( $type ) {
			case 1: $object = 'EvnPS'; break;
			case 2: case 16: $object = 'EvnPL'; break;
			case 4: $object = 'EvnPLDD'; break;
			case 5: $object = 'EvnPLOrp'; break;
			case 6: $object = 'SMP'; break;
			case 7: $object = 'EvnPLDD13'; break;
			case 9: $object = 'EvnPLOrp13'; break;
			case 11: $object = 'EvnPLProf'; break;
			case 12: $object = 'EvnPLProfTeen'; break;
			case 14: $object = 'EvnHTM'; break;
			case 15: $object = 'EvnUslugaPar'; break;
			default: return false; break;
		}

		if (in_array($data['PayType_SysNick'], array('mbudtrans', 'mbudtrans_mbud'))) {
			if ($data['PayType_SysNick'] == 'mbudtrans') {
				$p_schet = "r59.p_Registry_MBT_expScet";
			} else if ($data['PayType_SysNick'] == 'mbudtrans_mbud') {
				$p_schet = "r59.p_Registry_MBT_expScet";
			}

			$query = "
				select * from {$p_schet} (:Registry_id)
			";
		} else {
			$p_schet = $this->scheme . ".p_Registry_" . $object . "_expScet";

			// шапка
			$query = "
			select * from {$p_schet} (Registry_id := :Registry_id)
		";
		}



		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');
			if ( !empty($header[0]) ) {
				array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);
				return array(array_change_key_case($header[0], CASE_UPPER));
			}
		}

		return false;
	}

	/**
	 * Обновление Registry_EvnNum (добавление Evn_rid)
	 */
	function addRidInRegistryEvnNum() {
		set_time_limit(0);

		for ($i = 0; $i < 20; $i++) {
			$resp = $this->queryResult("
				select
					ur.Registry_id as \"Registry_id\", 
					ur.Registry_EvnNum as \"Registry_EvnNum\"
				from
					v_Registry ur
				where
					ur.RegistryType_id = 13
					and ur.Registry_EvnNum is not null
					and ur.Registry_expDT is null
				limit 100
			");

			foreach ($resp as $respone) {
				// разбираем
				$Registry_EvnNum = json_decode($respone['Registry_EvnNum'], true);
				if (!empty($Registry_EvnNum)) {
					$rdata = array();
					foreach ($Registry_EvnNum as $key => $value) {
						$value['key'] = $key;
						$rdata[$value['Evn_id']] = $value;
					}

					// обрабатываем
					$Evns = array();
					foreach ($Registry_EvnNum as $key => $value) {
						if (!empty($value['Evn_id'])) {
							$Evns[] = $value['Evn_id'];
						}

						if (count($Evns) >= 50) {
							$resp_ev = $this->queryResult("
								select Evn_id as \"Evn_id\", Evn_rid as \"Evn_rid\" 
								from Evn where Evn_id in ('" . implode("','", $Evns) . "')
							");
							foreach ($resp_ev as $resp_evone) {
								if (!empty($rdata[$resp_evone['Evn_id']])) {
									$rdata[$resp_evone['Evn_id']]['Evn_rid'] = $resp_evone['Evn_rid'];
								}
							}

							unset($Evns);
							$Evns = array();
						}
					}

					if (count($Evns) > 0) {
						$resp_ev = $this->queryResult("
							select Evn_id as \"Evn_id\", Evn_rid as \"Evn_rid\" 
							from Evn where Evn_id in ('" . implode("','", $Evns) . "')
						");
						foreach ($resp_ev as $resp_evone) {
							if (!empty($rdata[$resp_evone['Evn_id']])) {
								$rdata[$resp_evone['Evn_id']]['Evn_rid'] = $resp_evone['Evn_rid'];
							}
						}

						unset($Evns);
						$Evns = array();
					}

					// собираем вновь
					$Registry_EvnNum = array();
					foreach ($rdata as $value) {
						$key = $value['key'];
						unset($value['key']);
						$Registry_EvnNum[$key] = $value;
					}

					// зажсониваем
					$Registry_EvnNum = json_encode($Registry_EvnNum);
					// апдейтим реестр
					$this->db->query("
						update Registry set Registry_EvnNum = :Registry_EvnNum, Registry_updDT = dbo.tzGetDate(), Registry_expDT = dbo.tzGetDate(), pmUser_updID = 1 where Registry_id = :Registry_id
					", array(
						'Registry_id' => $respone['Registry_id'],
						'Registry_EvnNum' => $Registry_EvnNum
					));
				}
			}

			unset($resp);
		}
	}

	/**
	 * Получает ключи
	 */
	function getKeysForRec($rec) {
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
	 *	Получение данных для выгрузки простого реестра в XML
	 */
	function loadRegistryDataForXmlUsingCommonCustom($isUnion, $type, $data, $Registry_EvnNum, $oldRegistry_EvnNums, $file_re_data_name, $file_re_pers_data_name) {
		$before2015 = $data['before2015'];
		$person_field = "ID_PAC";

		$templ_body = "registry_pl_body";
		$templ_person_body = "registry_person_body";

		if ( in_array($data['PayType_SysNick'], array('bud', 'fbud')) ) {
			$templ_body .= '_bud';
			$templ_person_body .= '_bud';
		}
		else if ( $before2015 ) {
			$templ_body .= '_2014';
			$templ_person_body .= '_2014';
		}

		switch ( $type ) {
			case 1: //stac
				$p_vizit = $this->scheme.".p_Registry_EvnPS_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPS_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPS_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnPS_expDS2";
				$p_ds3 = $this->scheme.".p_Registry_EvnPS_expDS3";

				if ( $before2015 ) {
					$p_vizit = $this->scheme.".p_Registry_EvnPS_expVizit_old";
					$p_usl = $this->scheme.".p_Registry_EvnPS_expUsl_old";
					$p_pers = $this->scheme.".p_Registry_EvnPS_expPac_old";
				}
				break;

			case 2: //polka
			case 16: //stom
				$p_vizit = $this->scheme.".p_Registry_EvnPL_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPL_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPL_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnPL_expDS2";
				break;

			case 4: //dd
				$p_vizit = $this->scheme.".p_Registry_EvnPLDD_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLDD_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLDD_expPac";
				break;

			case 5: //orp
				$p_vizit = $this->scheme.".p_Registry_EvnPLOrp_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLOrp_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLOrp_expPac";
				break;

			case 6: //smp
				$p_vizit = $this->scheme.".p_Registry_SMP_expVizit";
				$p_usl = $this->scheme.".p_Registry_SMP_expUsl";
				$p_pers = $this->scheme.".p_Registry_SMP_expPac";
				break;

			case 7: //dd
				$p_vizit = $this->scheme.".p_Registry_EvnPLDD13_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLDD13_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLDD13_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnPLDD13_expDS2";
				$p_naz = $this->scheme.".p_Registry_EvnPLDD13_expNAZ";
				break;

			case 9: //orp
				$p_vizit = $this->scheme.".p_Registry_EvnPLOrp13_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLOrp13_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLOrp13_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnPLOrp13_expDS2";
				break;

			case 11: //prof
				$p_vizit = $this->scheme.".p_Registry_EvnPLProf_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLProf_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLProf_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnPLProf_expDS2";
				$p_naz = $this->scheme.".p_Registry_EvnPLProf_expNAZ";
				break;

			case 12: //teen inspection
				$p_vizit = $this->scheme.".p_Registry_EvnPLProfTeen_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLProfTeen_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLProfTeen_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnPLProfTeen_expDS2";
				break;

			case 14: //htm
				$p_vizit = $this->scheme.".p_Registry_EvnHTM_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnHTM_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnHTM_expPac";
				$p_ds2 = $this->scheme.".p_Registry_EvnHTM_expDS2";
				break;

			case 15: //par
				$p_vizit = $this->scheme.".p_Registry_EvnUslugaPar_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnUslugaPar_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnUslugaPar_expPac";
				break;

			default:
				return false;
				break;
		}

		// посещения
		$query = "
			select * from {$p_vizit} (Registry_id := ?)
		";
		$result_sluch = $this->db->query($query, array($data['Registry_id']));
		if (!is_object($result_sluch)) {
			return false;
		}

		// услуги
		$query = "
			select * from {$p_usl} (Registry_id := ?)
		";
		$result_usl = $this->db->query($query, array($data['Registry_id']));
		if (!is_object($result_usl)) {
			return false;
		}

		// люди
		$query = "
			select * from {$p_pers} (Registry_id := ?)
		";
		$result_pac = $this->db->query($query, array($data['Registry_id']));
		if (!is_object($result_pac)) {
			return false;
		}

		// диагнозы
		$DS2 = array();
		$DS3 = array();

		if ( !empty($p_ds2) ) {
			$query = "
				select * from {$p_ds2} (Registry_id := ?)
			";
			$result_ds2 = $this->db->query($query, array($data['Registry_id']));
			if (!is_object($result_ds2)) {
				return false;
			}

			while ($diag = $result_ds2->_fetch_assoc()) {
				array_walk_recursive($diag, 'ConvertFromUTF8ToWin1251', true);
				$diag = array_change_key_case($diag, CASE_UPPER);
				$diag['Evn_id'] = $diag['EVN_ID'];
				$diag['NumCard'] = $diag['NUMCARD'];
				$diag['MaxEvn_id'] = $diag['MAXEVN_ID'];
				$DS2[$diag['MaxEvn_id']][] = $diag;
			}
		}

		if (!empty($p_ds3)) {
			$query = "
				select * from {$p_ds3} (Registry_id := ?)
			";
			$result_ds3 = $this->db->query($query, array($data['Registry_id']));
			if (!is_object($result_ds3)) {
				return false;
			}

			while ($diag = $result_ds3->_fetch_assoc()) {
				array_walk_recursive($diag, 'ConvertFromUTF8ToWin1251', true);
				$diag = array_change_key_case($diag, CASE_UPPER);
				$diag['Evn_id'] = $diag['EVN_ID'];
				$diag['NumCard'] = $diag['NUMCARD'];
				$diag['MaxEvn_id'] = $diag['MAXEVN_ID'];
				$DS3[$diag['MaxEvn_id']][] = $diag;
			}
		}

		// назначения
		$NAZ_DISP = array();

		if ( !empty($p_naz) ) {
			$query = "
				exec * from {$p_naz} (Registry_id := ?)
			";
			$result_naz = $this->db->query($query, array($data['Registry_id']));
			if (!is_object($result_naz)) {
				return false;
			}

			while ($naz_one = $result_naz->_fetch_assoc()) {
				array_walk_recursive($naz_one, 'ConvertFromUTF8ToWin1251', true);
				$naz_one = array_change_key_case($naz_one, CASE_UPPER);
				$naz_one['Evn_id'] = $naz_one['EVN_ID'];
				$naz_one['NumCard'] = $naz_one['NUMCARD'];
				$naz_one['MaxEvn_id'] = $naz_one['MAXEVN_ID'];
				$NAZ_DISP[$naz_one['MaxEvn_id']][] = $naz_one;
			}
		}

		// Формируем массив услуг
		$USL = array();

		while ( $usluga = $result_usl->_fetch_assoc() ) {
			array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);
			$USL[$usluga['MaxEvn_id']][] = $usluga;
		}

		// Формируем массив услуг пациентов
		$PACIENT = array();
		$netValue = toAnsi('НЕТ', true);

		while ( $pers = $result_pac->_fetch_assoc() ) {
			array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);
			$pers = array_change_key_case($result_pac, CASE_UPPER);
			$pers['DOST'] = array();
			$pers['DOST_P'] = array();

			if ( $pers['NOVOR'] == '0' || in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				if ( empty($pers['FAM']) ) {
					$pers['DOST'][] = array('DOST_VAL' => 2);
				}

				if ( empty($pers['IM']) ) {
					$pers['DOST'][] = array('DOST_VAL' => 3);
				}

				if ( empty($pers['OT']) || mb_strtoupper($pers['OT'], 'windows-1251') == $netValue ) {
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

				if ( empty($pers['OT_P']) || mb_strtoupper($pers['OT_P'], 'windows-1251') == $netValue ) {
					$pers['DOST_P'][] = array('DOST_P_VAL' => 1);
				}
			}

			$PACIENT[$pers['ID_PAC']] = $pers;
		}

		// Идём по людям, как набираем 1000 записей -> пишем сразу в файл.
		$ZAP = array();

		$altKeys = array(
			 'USL_LPU' => 'LPU'
			,'USL_LPU_1' => 'LPU_1'
			,'USL_PODR' => 'PODR'
			,'USL_UCHASTOK' => 'UCHASTOK'
			,'USL_PUNKT' => 'PUNKT'
			,'USL_PROFIL' => 'PROFIL'
			,'USL_DET' => 'DET'
			,'USL_P_OTK' => 'P_OTK'
			,'TARIF_USL' => 'TARIF'
			,'USL_PRVS' => 'PRVS'
		);

		while ( $visit = $result_sluch->_fetch_assoc() ) {
			array_walk_recursive($visit, 'ConvertFromUTF8ToWin1251', true);
			$visit = array_change_key_case($visit, CASE_UPPER);
			if ( empty($visit['IDCASE']) ) {
				continue;
			}
			
			$visit['Evn_id'] = $visit['EVN_ID'];
			$visit['Evn_rid'] = $visit['EVN_RID'];
			$visit['Evn_predid'] = $visit['EVN_PREDID'];

			$key = $visit['IDCASE'];
			$this->_countSLUCH++;

			// Привязываем услугу
			$visit['USL'] = !empty($USL[$key]) ? $USL[$key] : $this->getEmptyUslugaXmlRow();
			unset($USL[$key]);

			$visit['DS2_DATA'] = array();
			$visit['DS3_DATA'] = array();
			$visit['NAZ_DISP'] = array();

			// Привязываем диагнозы
			if ( isset($DS2[$visit['IDCASE']]) ) {
				$visit['DS2_DATA'] = $DS2[$visit['IDCASE']];
			}
			else if ( !empty($visit['DS2']) ) {
				$visit['DS2_DATA'] = array(array('DS2' => $visit['DS2'], 'DS2_PR' => (!empty($visit['DS2_PR']) ? $visit['DS2_PR'] : null)));
			}

			if ( isset($DS3[$visit['IDCASE']]) ) {
				$visit['DS3_DATA'] = $DS3[$visit['IDCASE']];
			}
			else if ( !empty($visit['DS3']) ) {
				$visit['DS3_DATA'] = array(array('DS3' => $visit['DS3']));
			}

			if ( isset($NAZ_DISP[$visit['IDCASE']]) ) {
				$visit['NAZ_DISP'] = $NAZ_DISP[$visit['IDCASE']];
			}

			if ( array_key_exists('DS2', $visit) ) {
				unset($visit['DS2']);
			}

			if ( array_key_exists('DS3', $visit) ) {
				unset($visit['DS3']);
			}

			$ZAP[$key] = array(
				'SLUCH' => array($visit),
				'PACIENT' => array($PACIENT[$key])
			);

			if ( $type == 1 && $before2015 ) {
				$ZAP[$key]['PR_NOV'] = $visit['PR_NOV'];
				$ZAP[$key]['NSCHET_P'] = $visit['NSCHET_P'];
				$ZAP[$key]['DSCHET_P'] = $visit['DSCHET_P'];
				$ZAP[$key]['N_ZAP_P'] = $visit['N_ZAP_P'];
			}
			else {
				if (!empty($visit['Evn_id'])) {
					$Evn_id = $visit['Evn_id'];
				} else {
					$Evn_id = $visit['CASEGUID']; // тут не всегда Evn_id теперь, зато есть отдельное поле Evn_id #126945
				}

				$ZAP[$key]['PR_NOV'] = "";
				$ZAP[$key]['NSCHET_P'] = "";
				$ZAP[$key]['DSCHET_P'] = "";
				$ZAP[$key]['N_ZAP_P'] = "";
				$ZAP[$key]['SLUCH'][0]['IDCASE_P'] = "";

				if (!empty($visit['Evn_predid']) && !empty($oldRegistry_EvnNums[$visit['Evn_predid']])) {
					// если нашёлся в предыдущих реестрах
					// заполняем поля о предыдущем случае
					//$ZAP[$key]['PR_NOV'] = 1;
					$ZAP[$key]['NSCHET_P'] = $oldRegistry_EvnNums[$visit['Evn_predid']]['NSCHET_P'];
					$ZAP[$key]['DSCHET_P'] = $oldRegistry_EvnNums[$visit['Evn_predid']]['DSCHET_P'];
					$ZAP[$key]['N_ZAP_P'] = $oldRegistry_EvnNums[$visit['Evn_predid']]['N_ZAP_P'];
					$ZAP[$key]['SLUCH'][0]['IDCASE_P'] = $oldRegistry_EvnNums[$visit['Evn_predid']]['IDCASE_P'];
				} else if (!empty($visit['Evn_rid']) && !empty($oldRegistry_EvnNums[$visit['Evn_rid']])) {
					// если нашёлся в предыдущих реестрах
					// заполняем поля о предыдущем случае
					//$ZAP[$key]['PR_NOV'] = 1;
					$ZAP[$key]['NSCHET_P'] = $oldRegistry_EvnNums[$visit['Evn_rid']]['NSCHET_P'];
					$ZAP[$key]['DSCHET_P'] = $oldRegistry_EvnNums[$visit['Evn_rid']]['DSCHET_P'];
					$ZAP[$key]['N_ZAP_P'] = $oldRegistry_EvnNums[$visit['Evn_rid']]['N_ZAP_P'];
					$ZAP[$key]['SLUCH'][0]['IDCASE_P'] = $oldRegistry_EvnNums[$visit['Evn_rid']]['IDCASE_P'];
				} else if (!empty($oldRegistry_EvnNums[$Evn_id])) {
					// если нашёлся в предыдущих реестрах
					// заполняем поля о предыдущем случае
					//$ZAP[$key]['PR_NOV'] = 1;
					$ZAP[$key]['NSCHET_P'] = $oldRegistry_EvnNums[$Evn_id]['NSCHET_P'];
					$ZAP[$key]['DSCHET_P'] = $oldRegistry_EvnNums[$Evn_id]['DSCHET_P'];
					$ZAP[$key]['N_ZAP_P'] = $oldRegistry_EvnNums[$Evn_id]['N_ZAP_P'];
					$ZAP[$key]['SLUCH'][0]['IDCASE_P'] = $oldRegistry_EvnNums[$Evn_id]['IDCASE_P'];
				}
			}

			$ZAP[$key]['N_ZAP'] = $key;

			if ( count($ZAP) >= 100 ) {
				// пишем в файл
				$xml = $this->parser->parse_ext('export_xml/' . $templ_body, array('ZAP' => $ZAP), true, false, $altKeys);
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
			$xml = $this->parser->parse_ext('export_xml/' . $templ_body, array('ZAP' => $ZAP), true, false, $altKeys);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP);
		}

		unset($USL);

		$toFile = array();

		foreach ( $PACIENT as $onepac ) {
			$toFile[] = $onepac;

			if ( count($toFile) >= 100 ) {
				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_body, array('PACIENT' => $toFile), true, false, $altKeys);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($toFile);
				$toFile = array();
			}
		}

		if ( count($toFile) > 0 ) {
			// пишем в файл
			$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_body, array('PACIENT' => $toFile), true, false, $altKeys);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($toFile);
		}

		unset($toFile);
		unset($PACIENT);

		return true;
	}

	/**
	 * Получение данных для выгрузки реестров в XML на основе функций, а не хранимок
	 */
	function loadRegistryDataForXmlByFunc2018($isUnion, $type, $data, &$Registry_EvnNum, $oldRegistry_EvnNums, $file_re_data_name, $file_re_pers_data_name)
	{
		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . (memory_get_usage() / 1024 / 1024) . " MB");

		if ($data['PayType_SysNick'] == 'mbudtrans_mbud') {
			$templ_body = "registry_pl_body_mbudtrans_mbud";
			$templ_person_body = "registry_person_body_mbudtrans_mbud";
		}else {
			$templ_body = "registry_pl_body_2018";
			$templ_person_body = "registry_person_body";
		}

		$scheme = 'r59';

		switch ($type) {
			case 1: //stac
				if ($data['PayType_SysNick'] == 'mbudtrans') {
					$p_zsl = $scheme . ".p_Registry_EvnPS_MBT_expSL";
					$p_pers = $scheme . ".p_Registry_EvnPS_MBT_expPac";
					$p_sl = $scheme . ".p_Registry_EvnPS_MBT_expVizit";
					$p_usl = $scheme . ".p_Registry_EvnPS_MBT_expUsl";
					$p_crit = $scheme . ".p_Registry_EvnPS_MBT_expCRIT";
					$p_kslp = $scheme . ".p_Registry_EvnPS_MBT_expKSLP";
					$p_ds2 = $scheme . ".p_Registry_EvnPS_MBT_expDS2";
					$p_ds3 = $scheme . ".p_Registry_EvnPS_MBT_expDS3";
				}else if ($data['PayType_SysNick'] == 'mbudtrans_mbud') {
					$p_zsl = $scheme . ".p_Registry_EvnPS_MBT_SSZ_expSL";
					$p_pers = $scheme . ".p_Registry_EvnPS_MBT_SSZ_expPac";
					$p_sl = $scheme . ".p_Registry_EvnPS_MBT_SSZ_expVizit";
					$p_usl = $scheme . ".p_Registry_EvnPS_MBT_SSZ_expUsl";
					$p_ds2 = $scheme . ".p_Registry_EvnPS_SSZ_MBT_expDS2";
					$p_ds3 = $scheme . ".p_Registry_EvnPS_MBT_SSZ_expDS3";
				} else {
					$p_sl = $scheme . ".p_Registry_EvnPS_expVizit_2018";
					$p_zsl = $scheme . ".p_Registry_EvnPS_expSL_2018";
					$p_usl = $scheme . ".p_Registry_EvnPS_expUsl_2018";
					$p_pers = $scheme . ".p_Registry_EvnPS_expPac_2018";
					$p_ds2 = $scheme . ".p_Registry_EvnPS_expDS2_2018";
					$p_ds3 = $scheme . ".p_Registry_EvnPS_expDS3_2018";
					$p_kslp = $scheme . ".p_Registry_EvnPS_expKSLP_2018";
					$p_bdiag = $scheme . ".p_Registry_EvnPS_expBDIAG_2018";
					$p_bprot = $scheme . ".p_Registry_EvnPS_expBPROT_2018";
					$p_napr = $scheme . ".p_Registry_EvnPS_expNAPR_2018";
					$p_onkousl = $scheme . ".p_Registry_EvnPS_expONKOUSL_2018";
					$p_cons = $scheme . ".p_Registry_EvnPS_expCONS_2018";
					$p_lekpr = $scheme . ".p_Registry_EvnPS_expLEK_PR_2018";
					$p_crit = $scheme . ".p_Registry_EvnPS_expCRIT_2018";
				}

				break;
			case 2: //polka
				if ($data['PayType_SysNick'] == 'mbudtrans_mbud') {
					$p_zsl = $scheme . ".p_Registry_EvnPL_MBT_SSZ_expSL";
					$p_pers = $scheme . ".p_Registry_EvnPL_MBT_SSZ_expPac";
					$p_sl = $scheme . ".p_Registry_EvnPL_MBT_SSZ_expVizit";
					$p_usl = $scheme . ".p_Registry_EvnPL_MBT_SSZ_expUsl";
					$p_ds2 = $scheme . ".p_Registry_EvnPL_MBT_SSZ_expDS2";
				}else {
					$p_sl = $scheme . ".p_Registry_EvnPL_expVizit_2018";
					$p_zsl = $scheme . ".p_Registry_EvnPL_expSL_2018";
					$p_usl = $scheme . ".p_Registry_EvnPL_expUsl_2018";
					$p_pers = $scheme . ".p_Registry_EvnPL_expPac_2018";
					$p_ds2 = $scheme . ".p_Registry_EvnPL_expDS2_2018";
					$p_bdiag = $scheme . ".p_Registry_EvnPL_expBDIAG_2018";
					$p_bprot = $scheme . ".p_Registry_EvnPL_expBPROT_2018";
					$p_napr = $scheme . ".p_Registry_EvnPL_expNAPR_2018";
					$p_onkousl = $scheme . ".p_Registry_EvnPL_expONKOUSL_2018";
					$p_cons = $scheme . ".p_Registry_EvnPL_expCONS_2018";
				}
				break;
			case 6: //smp
				if ($data['PayType_SysNick'] == 'mbudtrans') {
					$p_sl = $scheme . ".p_Registry_SMP_MBT_expVizit_2018";
					$p_zsl = $scheme . ".p_Registry_SMP_MBT_expSL_2018";
					$p_usl = $scheme . ".p_Registry_SMP_MBT_expUsl_2018";
					$p_pers = $scheme . ".p_Registry_SMP_MBT_expPac_2018";
					$p_ds2 = $scheme . ".p_Registry_SMP_MBT_expDS2";
				}else if ($data['PayType_SysNick'] == 'mbudtrans_mbud') {
					$p_sl = $scheme . ".p_Registry_SMP_MBT_SSZ_expVizit_2018";
					$p_zsl = $scheme . ".p_Registry_SMP_MBT_SSZ_expSL_2018";
					$p_usl = $scheme . ".p_Registry_SMP_MBT_SSZ_expUsl_2018";
					$p_pers = $scheme . ".p_Registry_SMP_MBT_SSZ_expPac_2018";
					$p_ds2 = $scheme . ".p_Registry_SMP_MBT_SSZ_expDS2";
				} else {
					$p_sl = $scheme . ".p_Registry_SMP_expVizit_2018";
					$p_zsl = $scheme . ".p_Registry_SMP_expSL_2018";
					$p_usl = $scheme . ".p_Registry_SMP_expUsl_2018";
					$p_pers = $scheme . ".p_Registry_SMP_expPac_2018";
					//$p_bdiag = $scheme . ".p_Registry_SMP_expBDIAG_2018";
				}
				break;
			case 7: //dd
				$p_sl = $scheme . ".p_Registry_EvnPLDD13_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnPLDD13_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnPLDD13_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnPLDD13_expPac_2018";
				$p_ds2 = $scheme . ".p_Registry_EvnPLDD13_expDS2_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnPLDD13_expBDIAG_2018";
				$p_napr = $scheme . ".p_Registry_EvnPLDD13_expNAPR_2018";
				$p_naz = $scheme . ".p_Registry_EvnPLDD13_expNAZ_2018";
				$p_cons = $scheme . ".p_Registry_EvnPLDD13_expCONS_2018";
				break;
			case 9: //orp
				$p_sl = $scheme . ".p_Registry_EvnPLOrp13_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnPLOrp13_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnPLOrp13_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnPLOrp13_expPac_2018";
				$p_ds2 = $scheme . ".p_Registry_EvnPLOrp13_expDS2_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnPLOrp13_expBDIAG_2018";
				//$p_napr = $scheme . ".p_Registry_EvnPLOrp13_expNAPR_2018";
				//$p_naz = $scheme . ".p_Registry_EvnPLOrp13_expNAZ_2018";
				$p_cons = $scheme . ".p_Registry_EvnPLOrp13_expCONS_2018";
				break;
			case 11: //prof
				$p_sl = $scheme . ".p_Registry_EvnPLProf_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnPLProf_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnPLProf_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnPLProf_expPac_2018";
				$p_ds2 = $scheme . ".p_Registry_EvnPLProf_expDS2_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnPLProf_expBDIAG_2018";
				$p_napr = $scheme . ".p_Registry_EvnPLProf_expNAPR_2018";
				$p_naz = $scheme . ".p_Registry_EvnPLProf_expNAZ_2018";
				$p_cons = $scheme . ".p_Registry_EvnPLProf_expCONS_2018";
				break;
			case 12: //teen inspection
				$p_sl = $scheme . ".p_Registry_EvnPLProfTeen_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnPLProfTeen_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnPLProfTeen_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnPLProfTeen_expPac_2018";
				$p_ds2 = $scheme . ".p_Registry_EvnPLProfTeen_expDS2_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnPLProfTeen_expBDIAG_2018";
				//$p_napr = $scheme . ".p_Registry_EvnPLProfTeen_expNAPR_2018";
				//$p_naz = $scheme . ".p_Registry_EvnPLProfTeen_expNAZ_2018";
				$p_cons = $scheme . ".p_Registry_EvnPLProfTeen_expCONS_2018";
				break;
			case 14: //htm
				$p_sl = $scheme . ".p_Registry_EvnHTM_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnHTM_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnHTM_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnHTM_expPac_2018";
				$p_ds2 = $scheme . ".p_Registry_EvnHTM_expDS2_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnHTM_expBDIAG_2018";
				$p_bprot = $scheme . ".p_Registry_EvnHTM_expBPROT_2018";
				$p_napr = $scheme . ".p_Registry_EvnHTM_expNAPR_2018";
				$p_onkousl = $scheme . ".p_Registry_EvnHTM_expONKOUSL_2018";
				$p_cons = $scheme . ".p_Registry_EvnHTM_expCONS_2018";
				$p_lekpr = $scheme . ".p_Registry_EvnHTM_expLEK_PR_2018";
				break;
			case 15: //par
				$p_sl = $scheme . ".p_Registry_EvnUslugaPar_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnUslugaPar_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnUslugaPar_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnUslugaPar_expPac_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnUslugaPar_expBDIAG_2018";
				$p_cons = $scheme . ".p_Registry_EvnUslugaPar_expCONS_2018";
				break;
			case 16: //stom
				$p_sl = $scheme . ".p_Registry_EvnPLStom_expVizit_2018";
				$p_zsl = $scheme . ".p_Registry_EvnPLStom_expSL_2018";
				$p_usl = $scheme . ".p_Registry_EvnPLStom_expUsl_2018";
				$p_pers = $scheme . ".p_Registry_EvnPLStom_expPac_2018";
				$p_ds2 = $scheme . ".p_Registry_EvnPLStom_expDS2_2018";
				$p_bdiag = $scheme . ".p_Registry_EvnPLStom_expBDIAG_2018";
				$p_bprot = $scheme . ".p_Registry_EvnPLStom_expBPROT_2018";
				$p_napr = $scheme . ".p_Registry_EvnPLStom_expNAPR_2018";
				$p_onkousl = $scheme . ".p_Registry_EvnPLStom_expONKOUSL_2018";
				$p_cons = $scheme . ".p_Registry_EvnPLStom_expCONS_2018";
				break;
			default:
				return false;
		}

		// 1. нагребаем то что можно не джойнить сразу (небольшой объем данных) - DS2 / DS3 / NAZ
		// 1.1. диагнозы DS2
		$DS2 = array();
		if (!empty($p_ds2)) {
			$result = $this->db->query("
				select * from {$p_ds2} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$DS2[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		// 1.2. диагнозы DS3
		$DS3 = array();
		if (!empty($p_ds3)) {
			$result = $this->db->query("
				select * from {$p_ds3} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$DS3[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		// 1.3. назначения
		$NAZ_DISP = array();
		if (!empty($p_naz)) {
			$result = $this->db->query("
				select * from {$p_naz} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$NAZ_DISP[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		// 1.4 ксг
		$KSG_KPG_DATA = array();

		// 1.5. КСЛП
		$KSLP = array();
		if (!empty($p_kslp)) {
			$result = $this->db->query("
				select * from {$p_kslp} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$KSLP[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// 1.6. Данные диагностического блока
		$BDIAG = array();
		if (!empty($p_bdiag)) {
			$result = $this->db->query("
				select * from {$p_bdiag} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$BDIAG[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// 1.7. Сведения об имеющихся противопоказаниях и отказах
		$BPROT = array();
		if (!empty($p_bprot)) {
			$result = $this->db->query("
				select * from {$p_bprot} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$BPROT[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// 1.8. Направления
		$NAPR = array();
		if (!empty($p_napr)) {
			$result = $this->db->query("
				select * from {$p_napr} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$NAPR[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// Хитрая логика для диспансеризации, направления берём из назначений %)
		foreach($NAZ_DISP as $key => $oneNAZ) {
			if (isset($oneNAZ[0]) && array_key_exists('NAPR_DATE', $oneNAZ[0])) {
				$NAPR[$key] = $oneNAZ;
			}
		}

		// 1.9. Сведения об услуге при лечении онкологического заболевания
		$ONKOUSL = array();
		if (!empty($p_onkousl)) {
			$result = $this->db->query("
				select * from {$p_onkousl} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$one_rec['LEK_PR_DATA'] = array();
				$ONKOUSL[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// 1.10. Сведения о проведении консилиума
		$CONS = array();
		if (!empty($p_cons)) {
			$result = $this->db->query("
				select * from {$p_cons} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$CONS[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// 1.11. Сведения о введённом противоопухолевом лекарственном препарате
		$LEK_PR = array();
		if (!empty($p_lekpr)) {
			$result = $this->db->query("
				select * from {$p_lekpr} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['EvnUsluga_id'] = $one_rec['EVNUSLUGA_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$LEK_PR[$one_rec['EvnUsluga_id']][] = $one_rec;
			}
		}

		// 1.12. Сведения о классификационных критериях
		$CRIT = array();
		if (!empty($p_crit)) {
			$result = $this->db->query("
				select * from {$p_crit} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec = array_change_key_case($one_rec, CASE_UPPER);
				$one_rec['Evn_id'] = $one_rec['EVN_ID'];
				$one_rec['NumCard'] = $one_rec['NUMCARD'];
				$one_rec['MaxEvn_id'] = $one_rec['MAXEVN_ID'];
				$CRIT[$one_rec['Evn_id']][] = $one_rec;
			}
		}

		// 1.13. Сведения о случае лечения онкологического заболевания
		$ONK_SL_DATA = array();

		$KSG_KPG_DATA_FIELDS = array('KSG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'SL_K', 'IT_SL');
		$NAZ_DISP_FIELDS = array('NAZR', 'NAZ_SP', 'NAZ_V', 'NAZ_PMP', 'NAZ_PK');
		$ONK_SL_DATA_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');

		$altKeys = array(
			 'USL_LPU' => 'LPU'
			,'USL_LPU_1' => 'LPU_1'
			,'USL_PODR' => 'PODR'
			,'USL_UCHASTOK' => 'UCHASTOK'
			,'USL_PUNKT' => 'PUNKT'
			,'USL_PROFIL' => 'PROFIL'
			,'USL_DET' => 'DET'
			,'USL_P_OTK' => 'P_OTK'
			,'TARIF_USL' => 'TARIF'
			,'USL_PRVS' => 'PRVS'
		);

		$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных DS2/DS3/NAZ: " . (memory_get_usage() / 1024 / 1024) . " MB");

		// 2. джойним сразу посещения + услуги + пациенты и гребем постепенно то что получилось, сразу записывая в файл
		$result = $this->db->query("
			with
			zsl as (
				select * from {$p_zsl} (:Registry_id)
			), 
			sl as (
				select * from {$p_sl} (:Registry_id)
			), 
			usl as (
				select * from {$p_usl} (:Registry_id)
			), 
			pers as (
				select * from {$p_pers} (:Registry_id)
			)
			select
				null as \"fields_part_1\",
				z.*,
				z.MaxEvn_id as \"MaxEvn_zid\",
				z.registry_id as \"Registry_zid\",
				null as \"fields_part_2\",
				s.*,
				s.Evn_id as \"Evn_sid\",
				null as \"fields_part_3\",
				p.*,
				null as \"fields_part_4\",
				u.*,
				u.Evn_id as \"Evn_uid\"
			from
				zsl z
				inner join sl s on s.MaxEvn_id = z.MaxEvn_id
				inner join pers p on p.MaxEvn_id = z.MaxEvn_id
				left join usl u on u.Evn_id = s.Evn_id
			order by
				p.ID_PAC,
				s.Evn_id
		", array('Registry_id' => $data['Registry_id']), true);
		if (!is_object($result)) {
			return false;
		}
		$ZAP_ARRAY = array();
		$PACIENT_ARRAY = array();
		$netValue = toAnsi('НЕТ', true);

		$recKeys = array(); // ключи для данных

		$prevID_PAC = null;
		while ($one_rec = $result->_fetch_assoc()) {
			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
			$one_rec = array_change_key_case($one_rec, CASE_UPPER);
			$one_rec['fields_part_1'] = $one_rec['FIELDS_PART_1'];
			$one_rec['MaxEvn_zid'] = $one_rec['MAXEVN_ZID'];
			$one_rec['Registry_zid'] = $one_rec['REGISTRY_ZID'];
			$one_rec['fields_part_2'] = $one_rec['FIELDS_PART_2'];
			$one_rec['Evn_sid'] = $one_rec['EVN_SID'];
			$one_rec['fields_part_3'] = $one_rec['FIELDS_PART_3'];
			$one_rec['fields_part_4'] = $one_rec['FIELDS_PART_4'];
			$one_rec['Evn_uid'] = $one_rec['EVN_UID'];

			if (empty($recKeys)) {
				$recKeys = $this->getKeysForRec($one_rec);
				if (count($recKeys) < 4) {
					$this->textlog->add("Ошибка, неверное количество частей в запросе");
					return false;
				}
			}

			// Костыль для реализации условия выгрузки NAPR_DATA и ONK_USL_DATA в последней услуге случая для стационара и ВМП
			//
			// upd: Осталось только для NAPR_DATA
			// @task https://redmine.swan.perm.ru/issues/142848
			if (
				in_array($type, array(1, 14)) && !empty($zsl_key) && !empty($sl_key) && $sl_key != $one_rec['Evn_sid'] . '_' . $one_rec['Registry_zid']
				&& count($ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key]['USL']) > 1
			) {
				foreach ( $ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key]['USL'] as $i => $row ) {
					if ( $i == count($ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key]['USL']) - 1 ) {
						continue;
					}

					$ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key]['USL'][$i]['NAPR_DATA'] = array();
				}
			}

			$zsl_key = $one_rec['MaxEvn_zid'] . '_' . $one_rec['Registry_zid'];
			$sl_key = $one_rec['Evn_sid'] . '_' . $one_rec['Registry_zid'];

			$ZSL = array_intersect_key($one_rec, $recKeys[1]);
			$SLUCH = array_intersect_key($one_rec, $recKeys[2]);
			$PACIENT = array_intersect_key($one_rec, $recKeys[3]);
			$USL = array_intersect_key($one_rec, $recKeys[4]);

			$SLUCH['Evn_id'] = $one_rec['Evn_sid'];
			$SLUCH['MaxEvn_id'] = $one_rec['MaxEvn_zid'];

			if ( empty($SLUCH['CodeRefusal']) ) {
				$SLUCH['CodeRefusal'] = '';
			}

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if (count($ZAP_ARRAY) >= 100 && $PACIENT['ID_PAC'] != $prevID_PAC) {
				// пишем в файл случаи
				$xml = $this->parser->parse_ext('export_xml/' . $templ_body, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = array();
				unset($xml);
				// пишем в файл пациентов
				$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_body, array('PACIENT' => $PACIENT_ARRAY), true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($PACIENT_ARRAY);
				$PACIENT_ARRAY = array();
				unset($xml_pers);
			}
			$prevID_PAC = $PACIENT['ID_PAC'];

			if (isset($ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key])) {
				// если уже есть случай, значит добавляем услугу
				if (!empty($USL['DATE_IN'])) {
					$ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key]['USL'][] = $USL;
				}
			} else if (isset($ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0])) {
				$this->_countSLUCH++;

				$SLUCH['CONS_DATA'] = array();
				$SLUCH['NAPR_DATA'] = array();
				$SLUCH['NAZ_DISP'] = array();
				$SLUCH['ONK_SL_DATA'] = array();

				// если уже есть законченный случай, значит добавляем в него SLUCH
				// Привязываем всякую дичь

				$SLUCH['DS2_DATA'] = array(array('DS2' => null));
				if (empty($SLUCH['DS2']) && empty($SLUCH['DS2_PR']) && empty($SLUCH['DS2_DN'])) {
					$SLUCH['DS2_NDATA'] = array();
				} else {
					$SLUCH['DS2_NDATA'] = array(array(
						'DS2' => (!empty($SLUCH['DS2']) ? $SLUCH['DS2'] : null),
						'DS2_PR' => (!empty($SLUCH['DS2_PR']) ? $SLUCH['DS2_PR'] : null),
						'DS2_DN' => (!empty($SLUCH['DS2_DN']) ? $SLUCH['DS2_DN'] : null)
					));
				}
				if (isset($DS2[$SLUCH['Evn_id']])) {

					if (in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud'))) {
						$SLUCH['DS2_DATA'] = $DS2[$SLUCH['Evn_id']];
					} else {
						$SLUCH['DS2_NDATA'] = $DS2[$SLUCH['Evn_id']];
					}

				}

				if ( array_key_exists('DS2', $SLUCH) ) {
					unset($SLUCH['DS2']);
				}
				if ( array_key_exists('DS2_PR', $SLUCH) ) {
					unset($SLUCH['DS2_PR']);
				}
				if ( array_key_exists('DS2_DN', $SLUCH) ) {
					unset($SLUCH['DS2_DN']);
				}

				if (isset($DS3[$SLUCH['Evn_id']])) {
					$SLUCH['DS3_DATA'] = $DS3[$SLUCH['Evn_id']];
				} else {
					$SLUCH['DS3_DATA'] = array(array('DS3' => (!empty($SLUCH['DS3']) ? $SLUCH['DS3'] : null)));
				}

				if ( array_key_exists('DS3', $SLUCH) ) {
					unset($SLUCH['DS3']);
				}

				$onkDS2 = false;

				if ( isset($SLUCH['DS2_NDATA']) && count($SLUCH['DS2_NDATA']) > 0 ) {
					foreach ( $SLUCH['DS2_NDATA'] as $row ) {
						if ( empty($row['DS2']) ) {
							continue;
						}

						$code = substr($row['DS2'], 0, 3);

						if ( ($code >= 'C00' && $code <= 'C80') || $code == 'C97' ) {
							$onkDS2 = true;
						}
					}
				}

				if (isset($NAZ_DISP[$SLUCH['Evn_id']])) {
					$SLUCH['NAZ_DISP'] = $NAZ_DISP[$SLUCH['Evn_id']];
				} else {
					$SLUCH['NAZ_DISP'] = array(array());
					$hasNAZDISPData = false;

					foreach ( $NAZ_DISP_FIELDS as $field ) {
						if ( !empty($SLUCH[$field]) ) {
							$hasNAZDISPData = true;
							$SLUCH['NAZ_DISP'][0][$field] = $SLUCH[$field];
						}
						else {
							$SLUCH['NAZ_DISP'][0][$field] = null;
						}

						if ( array_key_exists($field, $SLUCH) ) {
							unset($SLUCH[$field]);
						}
					}

					if ( $hasNAZDISPData === false ) {
						$SLUCH['NAZ_DISP'] = array();
					}
				}

				if (
					(!empty($SLUCH['DS_ONK']) && $SLUCH['DS_ONK'] == 1)
					|| (
						!empty($SLUCH['DS1'])
						&& (
							(substr($SLUCH['DS1'], 0, 3) >= 'C00' && substr($SLUCH['DS1'], 0, 3) <= 'C97')
							|| (substr($SLUCH['DS1'], 0, 3) >= 'D00' && substr($SLUCH['DS1'], 0, 3) <= 'D09')
							|| (substr($SLUCH['DS1'], 0, 3) == 'D70' && $onkDS2 === true)
						)
					)
				) {
					if ( isset($NAPR[$SLUCH['Evn_id']]) ) {
						$SLUCH['NAPR_DATA'] = $NAPR[$SLUCH['Evn_id']];
					}

					if ( isset($CONS[$SLUCH['Evn_id']]) ) {
						$SLUCH['CONS_DATA'] = $CONS[$SLUCH['Evn_id']];
					}
				}

				if (isset($KSG_KPG_DATA[$SLUCH['Evn_id']])) {
					$SLUCH['KSG_KPG_DATA'] = $KSG_KPG_DATA[$SLUCH['Evn_id']];
				} else {
					$SLUCH['KSG_KPG_DATA'] = array(array());
					$hasKSGKPGData = false;

					foreach ( $KSG_KPG_DATA_FIELDS as $field ) {
						if ( isset($SLUCH[$field]) ) {
							$hasKSGKPGData = true;
							$SLUCH['KSG_KPG_DATA'][0][$field] = $SLUCH[$field];
						}
						else {
							$SLUCH['KSG_KPG_DATA'][0][$field] = null;
						}

						if ( array_key_exists($field, $SLUCH) ) {
							unset($SLUCH[$field]);
						}
					}

					if (isset($CRIT[$SLUCH['Evn_id']])) {
						$SLUCH['KSG_KPG_DATA'][0]['CRIT_DATA'] = $CRIT[$SLUCH['Evn_id']];
					}
					else {
						$SLUCH['KSG_KPG_DATA'][0]['CRIT_DATA'] = array();
					}

					if (isset($KSLP[$SLUCH['Evn_id']])) {
						$SLUCH['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $KSLP[$SLUCH['Evn_id']];
					}
					else {
						$SLUCH['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = array();
					}

					if ( $hasKSGKPGData === false ) {
						$SLUCH['KSG_KPG_DATA'] = array();
					}
				}

				if (
					!empty($SLUCH['DS1'])
					&& (
						(substr($SLUCH['DS1'], 0, 3) >= 'C00' && substr($SLUCH['DS1'], 0, 3) <= 'C97')
						|| (substr($SLUCH['DS1'], 0, 3) >= 'D00' && substr($SLUCH['DS1'], 0, 3) <= 'D09')
						|| (substr($SLUCH['DS1'], 0, 3) == 'D70' && $onkDS2 === true)
					)
					&& (
						!in_array($type, array(1, 14))
						|| $SLUCH['PROFIL'] != '158'
					)
					&& $SLUCH['DS_ONK'] == 0
				) {
					if (isset($ONK_SL_DATA[$SLUCH['MaxEvn_id']])) {
						$SLUCH['ONK_SL_DATA'] = $ONK_SL_DATA[$SLUCH['MaxEvn_id']];
					} else {
						$SLUCH['ONK_SL_DATA'] = array(array());
						$hasONKOSLData = false;

						$SLUCH['ONK_SL_DATA'][0]['B_DIAG_DATA'] = array();
						$SLUCH['ONK_SL_DATA'][0]['B_PROT_DATA'] = array();
						$SLUCH['ONK_SL_DATA'][0]['ONK_USL_DATA'] = array();

						foreach ( $ONK_SL_DATA_FIELDS as $field ) {
							if ( isset($SLUCH[$field]) ) {
								$hasONKOSLData = true;
								$SLUCH['ONK_SL_DATA'][0][$field] = $SLUCH[$field];
							}
							else {
								$SLUCH['ONK_SL_DATA'][0][$field] = null;
							}

							if ( array_key_exists($field, $SLUCH) ) {
								unset($SLUCH[$field]);
							}
						}

						if (isset($BDIAG[$SLUCH['Evn_id']])) {
							$SLUCH['ONK_SL_DATA'][0]['B_DIAG_DATA'] = $BDIAG[$SLUCH['Evn_id']];
						}

						if (isset($BPROT[$SLUCH['Evn_id']])) {
							$SLUCH['ONK_SL_DATA'][0]['B_PROT_DATA'] = $BPROT[$SLUCH['Evn_id']];
						}

						if ( isset($ONKOUSL[$SLUCH['Evn_id']]) ) {
							foreach ( $ONKOUSL[$SLUCH['Evn_id']] as $onkuslKey => $onkuslRow ) {
								if ( isset($LEK_PR[$onkuslRow['EvnUsluga_id']]) ) {
									$LEK_PR_DATA = array();

									foreach ( $LEK_PR[$onkuslRow['EvnUsluga_id']] as $row ) {
										if ( !isset($LEK_PR_DATA[$row['REGNUM']]) ) {
											$LEK_PR_DATA[$row['REGNUM']] = array(
												'REGNUM' => $row['REGNUM'],
												'CODE_SH' => (!empty($row['CODE_SH']) ? $row['CODE_SH'] : null),
												'DATE_INJ_DATA' => array(),
											);
										}

										$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
									}

									$ONKOUSL[$SLUCH['Evn_id']][$onkuslKey]['LEK_PR_DATA'] = $LEK_PR_DATA;

									unset($LEK_PR[$onkuslRow['EvnUsluga_id']]);
								}
							}

							$SLUCH['ONK_SL_DATA'][0]['ONK_USL_DATA'] = $ONKOUSL[$SLUCH['Evn_id']];
						}

						if ( $hasONKOSLData === false ) {
							$SLUCH['ONK_SL_DATA'] = array();
						}
					}
				}

				// заполянем Registry_EvnNum
				if (!empty($SLUCH['Evn_id'])) {
					$Evn_id = $SLUCH['Evn_id'];
				} else {
					$Evn_id = $SLUCH['CASEGUID']; // тут не всегда Evn_id теперь, зато есть отдельное поле Evn_id #126945
				}
				$Registry_EvnNum[$this->_countSLUCH] = array(
					'Evn_id' => $Evn_id,
					'Registry_id' => $ZSL['Registry_zid'],
					'N_ZAP' => $this->_countZSL
				);
				if (in_array($type, array(2, 16))) {
					$Registry_EvnNum[$this->_countSLUCH]['Evn_rid'] = $ZSL['Evn_rid'];
				}

				$SLUCH['IDCASE_P'] = '';
				if (!empty($SLUCH['Evn_predid']) && !empty($oldRegistry_EvnNums[$SLUCH['Evn_predid']]) && ($type == $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['NRTYPE_P'])) {
					// если нашёлся в предыдущих реестрах заполняем поля о предыдущем случае
					$SLUCH['IDCASE_P'] = $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['IDCASE_P'];
				} else if (!empty($SLUCH['Evn_rid']) && !empty($oldRegistry_EvnNums[$SLUCH['Evn_rid']]) && ($type == $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['NRTYPE_P'])) {
					// если нашёлся в предыдущих реестрах заполняем поля о предыдущем случае
					$SLUCH['IDCASE_P'] = $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['IDCASE_P'];
				} else if (!empty($oldRegistry_EvnNums[$Evn_id]) && ($type == $oldRegistry_EvnNums[$Evn_id]['NRTYPE_P']) ) {
					// если нашёлся в предыдущих реестрах заполняем поля о предыдущем случае
					$SLUCH['IDCASE_P'] = $oldRegistry_EvnNums[$Evn_id]['IDCASE_P'];
				}

				if ($isUnion) {
					$SLUCH['IDCASE'] = $this->_countSLUCH;
				}

				$SLUCH['USL'] = array();
				if (!empty($USL['DATE_IN'])) {
					$SLUCH['USL'][] = $USL;
				}

				$ZAP_ARRAY[$zsl_key]['Z_SL_DATA'][0]['SLUCH'][$sl_key] = $SLUCH;
			} else {
				// иначе создаём новый ZAP
				$this->_countSLUCH++;
				$this->_countZSL++;

				$SLUCH['CONS_DATA'] = array();
				$SLUCH['NAPR_DATA'] = array();
				$SLUCH['NAZ_DISP'] = array();
				$SLUCH['ONK_SL_DATA'] = array();
				$SLUCH['USL'] = array();

				if (!empty($USL['DATE_IN'])) {
					$SLUCH['USL'][] = $USL;
				}

				$PACIENT['DOST'] = array();
				$PACIENT['DOST_P'] = array();

				if (isset($PACIENT['NOVOR']) && (is_null($PACIENT['NOVOR']) || $PACIENT['NOVOR'] == '0')) {
					if (empty($PACIENT['FAM'])) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 2);
					}

					if (empty($PACIENT['IM'])) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 3);
					}

					if (empty($PACIENT['OT']) || mb_strtoupper($PACIENT['OT'], 'windows-1251') == $netValue) {
						$PACIENT['DOST'][] = array('DOST_VAL' => 1);
					}
				} else if (!(in_array($data['PayType_SysNick'], array('mbudtrans', 'mbudtrans_mbud')) && $type = 6)) {
					if (empty($PACIENT['FAM_P'])) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 2);
					}

					if (empty($PACIENT['IM_P'])) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 3);
					}

					if (empty($PACIENT['OT_P']) || mb_strtoupper($PACIENT['OT_P'], 'windows-1251') == $netValue) {
						$PACIENT['DOST_P'][] = array('DOST_P_VAL' => 1);
					}
				}

				// Привязываем всякую дичь
				$SLUCH['DS2_DATA'] = array(array('DS2' => null));
				if (empty($SLUCH['DS2']) && empty($SLUCH['DS2_PR']) && empty($SLUCH['DS2_DN'])) {
					$SLUCH['DS2_NDATA'] = array();
				} else {
					$SLUCH['DS2_NDATA'] = array(array(
						'DS2' => (!empty($SLUCH['DS2']) ? $SLUCH['DS2'] : null),
						'DS2_PR' => (!empty($SLUCH['DS2_PR']) ? $SLUCH['DS2_PR'] : null),
						'DS2_DN' => (!empty($SLUCH['DS2_DN']) ? $SLUCH['DS2_DN'] : null)
					));
				}
				if (isset($DS2[$SLUCH['Evn_id']])) {

					if (in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud'))) {
						$SLUCH['DS2_DATA'] = $DS2[$SLUCH['Evn_id']];
					} else {
						$SLUCH['DS2_NDATA'] = $DS2[$SLUCH['Evn_id']];
					}
				}

				if ( array_key_exists('DS2', $SLUCH) ) {
					unset($SLUCH['DS2']);
				}
				if ( array_key_exists('DS2_PR', $SLUCH) ) {
					unset($SLUCH['DS2_PR']);
				}
				if ( array_key_exists('DS2_DN', $SLUCH) ) {
					unset($SLUCH['DS2_DN']);
				}

				if (isset($DS3[$SLUCH['Evn_id']])) {
					$SLUCH['DS3_DATA'] = $DS3[$SLUCH['Evn_id']];
				} else {
					$SLUCH['DS3_DATA'] = array(array('DS3' => (!empty($SLUCH['DS3']) ? $SLUCH['DS3'] : null)));
				}

				if ( array_key_exists('DS3', $SLUCH) ) {
					unset($SLUCH['DS3']);
				}

				$onkDS2 = false;

				if ( isset($SLUCH['DS2_NDATA']) && count($SLUCH['DS2_NDATA']) > 0 ) {
					foreach ( $SLUCH['DS2_NDATA'] as $row ) {
						if ( empty($row['DS2']) ) {
							continue;
						}

						$code = substr($row['DS2'], 0, 3);

						if ( ($code >= 'C00' && $code <= 'C80') || $code == 'C97' ) {
							$onkDS2 = true;
						}
					}
				}

				if (isset($NAZ_DISP[$SLUCH['Evn_id']])) {
					$SLUCH['NAZ_DISP'] = $NAZ_DISP[$SLUCH['Evn_id']];
				} else {
					$SLUCH['NAZ_DISP'] = array(array());
					$hasNAZDISPData = false;

					foreach ( $NAZ_DISP_FIELDS as $field ) {
						if ( !empty($SLUCH[$field]) ) {
							$hasNAZDISPData = true;
							$SLUCH['NAZ_DISP'][0][$field] = $SLUCH[$field];
						}
						else {
							$SLUCH['NAZ_DISP'][0][$field] = null;
						}

						if ( array_key_exists($field, $SLUCH) ) {
							unset($SLUCH[$field]);
						}
					}

					if ( $hasNAZDISPData === false ) {
						$SLUCH['NAZ_DISP'] = array();
					}
				}

				if (
					(!empty($SLUCH['DS_ONK']) && $SLUCH['DS_ONK'] == 1)
					|| (
						!empty($SLUCH['DS1'])
						&& (
							(substr($SLUCH['DS1'], 0, 3) >= 'C00' && substr($SLUCH['DS1'], 0, 3) <= 'C97')
							|| (substr($SLUCH['DS1'], 0, 3) >= 'D00' && substr($SLUCH['DS1'], 0, 3) <= 'D09')
							|| (substr($SLUCH['DS1'], 0, 3) == 'D70' && $onkDS2 === true)
						)
					)
				) {
					if ( isset($NAPR[$SLUCH['Evn_id']]) ) {
						$SLUCH['NAPR_DATA'] = $NAPR[$SLUCH['Evn_id']];
					}

					if ( isset($CONS[$SLUCH['Evn_id']]) ) {
						$SLUCH['CONS_DATA'] = $CONS[$SLUCH['Evn_id']];
					}
				}

				if (isset($KSG_KPG_DATA[$SLUCH['Evn_id']])) {
					$SLUCH['KSG_KPG_DATA'] = $KSG_KPG_DATA[$SLUCH['Evn_id']];
				} else {
					$SLUCH['KSG_KPG_DATA'] = array(array());
					$hasKSGKPGData = false;

					foreach ( $KSG_KPG_DATA_FIELDS as $field ) {
						if ( isset($SLUCH[$field]) ) {
							$hasKSGKPGData = true;
							$SLUCH['KSG_KPG_DATA'][0][$field] = $SLUCH[$field];
						}
						else {
							$SLUCH['KSG_KPG_DATA'][0][$field] = null;
						}

						if ( array_key_exists($field, $SLUCH) ) {
							unset($SLUCH[$field]);
						}
					}

					if (isset($CRIT[$SLUCH['Evn_id']])) {
						$SLUCH['KSG_KPG_DATA'][0]['CRIT_DATA'] = $CRIT[$SLUCH['Evn_id']];
					}
					else {
						$SLUCH['KSG_KPG_DATA'][0]['CRIT_DATA'] = array();
					}

					if (isset($KSLP[$SLUCH['Evn_id']])) {
						$SLUCH['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $KSLP[$SLUCH['Evn_id']];
					}
					else {
						$SLUCH['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = array();
					}

					if ( $hasKSGKPGData === false ) {
						$SLUCH['KSG_KPG_DATA'] = array();
					}
				}

				if (
					!empty($SLUCH['DS1'])
					&& (
						(substr($SLUCH['DS1'], 0, 3) >= 'C00' && substr($SLUCH['DS1'], 0, 3) <= 'C97')
						|| (substr($SLUCH['DS1'], 0, 3) >= 'D00' && substr($SLUCH['DS1'], 0, 3) <= 'D09')
						|| (substr($SLUCH['DS1'], 0, 3) == 'D70' && $onkDS2 === true)
					)
					&& (
						!in_array($type, array(1, 14))
						|| $SLUCH['PROFIL'] != '158'
					)
					&& $SLUCH['DS_ONK'] == 0
				) {
					if (isset($ONK_SL_DATA[$SLUCH['MaxEvn_id']])) {
						$SLUCH['ONK_SL_DATA'] = $ONK_SL_DATA[$SLUCH['MaxEvn_id']];
					} else {
						$SLUCH['ONK_SL_DATA'] = array(array());
						$hasONKOSLData = false;

						$SLUCH['ONK_SL_DATA'][0]['B_DIAG_DATA'] = array();
						$SLUCH['ONK_SL_DATA'][0]['B_PROT_DATA'] = array();
						$SLUCH['ONK_SL_DATA'][0]['ONK_USL_DATA'] = array();

						foreach ( $ONK_SL_DATA_FIELDS as $field ) {
							if ( isset($SLUCH[$field]) ) {
								$hasONKOSLData = true;
								$SLUCH['ONK_SL_DATA'][0][$field] = $SLUCH[$field];
							}
							else {
								$SLUCH['ONK_SL_DATA'][0][$field] = null;
							}

							if ( array_key_exists($field, $SLUCH) ) {
								unset($SLUCH[$field]);
							}
						}

						if (isset($BDIAG[$SLUCH['Evn_id']])) {
							$SLUCH['ONK_SL_DATA'][0]['B_DIAG_DATA'] = $BDIAG[$SLUCH['Evn_id']];
						}

						if (isset($BPROT[$SLUCH['Evn_id']])) {
							$SLUCH['ONK_SL_DATA'][0]['B_PROT_DATA'] = $BPROT[$SLUCH['Evn_id']];
						}

						if ( isset($ONKOUSL[$SLUCH['Evn_id']]) ) {
							foreach ( $ONKOUSL[$SLUCH['Evn_id']] as $onkuslKey => $onkuslRow ) {
								if ( isset($LEK_PR[$onkuslRow['EvnUsluga_id']]) ) {
									$LEK_PR_DATA = array();

									foreach ( $LEK_PR[$onkuslRow['EvnUsluga_id']] as $row ) {
										if ( !isset($LEK_PR_DATA[$row['REGNUM']]) ) {
											$LEK_PR_DATA[$row['REGNUM']] = array(
												'REGNUM' => $row['REGNUM'],
												'CODE_SH' => (!empty($row['CODE_SH']) ? $row['CODE_SH'] : null),
												'DATE_INJ_DATA' => array(),
											);
										}

										$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
									}

									$ONKOUSL[$SLUCH['Evn_id']][$onkuslKey]['LEK_PR_DATA'] = $LEK_PR_DATA;

									unset($LEK_PR[$onkuslRow['EvnUsluga_id']]);
								}
							}

							$SLUCH['ONK_SL_DATA'][0]['ONK_USL_DATA'] = $ONKOUSL[$SLUCH['Evn_id']];
						}

						if ( $hasONKOSLData === false ) {
							$SLUCH['ONK_SL_DATA'] = array();
						}
					}
				}

				// заполянем Registry_EvnNum
				if (!empty($SLUCH['Evn_id'])) {
					$Evn_id = $SLUCH['Evn_id'];
				} else {
					$Evn_id = $SLUCH['CASEGUID']; // тут не всегда Evn_id теперь, зато есть отдельное поле Evn_id #126945
				}
				$Registry_EvnNum[$this->_countSLUCH] = array(
					'Evn_id' => $Evn_id,
					'Registry_id' => $ZSL['Registry_zid'],
					'N_ZAP' => $this->_countZSL
				);
				if (in_array($type, array(2, 16))) {
					$Registry_EvnNum[$this->_countSLUCH]['Evn_rid'] = $ZSL['Evn_rid'];
				}

				$SLUCH['IDCASE_P'] = '';
				$PR_NOV = 0;
				$NSCHET_P = '';
				$DSCHET_P = '';
				$N_ZAP_P = '';
				if (!empty($SLUCH['Evn_predid']) && !empty($oldRegistry_EvnNums[$SLUCH['Evn_predid']]) && ($type == $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['NRTYPE_P'])) {
					// если нашёлся в предыдущих реестрах заполняем поля о предыдущем случае
					$PR_NOV = 1;
					$NSCHET_P = $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['NSCHET_P'];
					$DSCHET_P = $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['DSCHET_P'];
					$N_ZAP_P = $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['N_ZAP_P'];
					$SLUCH['IDCASE_P'] = $oldRegistry_EvnNums[$SLUCH['Evn_predid']]['IDCASE_P'];
				} else if (!empty($SLUCH['Evn_rid']) && !empty($oldRegistry_EvnNums[$SLUCH['Evn_rid']]) && ($type == $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['NRTYPE_P'])) {
					// если нашёлся в предыдущих реестрах заполняем поля о предыдущем случае
					$PR_NOV = 1;
					$NSCHET_P = $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['NSCHET_P'];
					$DSCHET_P = $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['DSCHET_P'];
					$N_ZAP_P = $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['N_ZAP_P'];
					$SLUCH['IDCASE_P'] = $oldRegistry_EvnNums[$SLUCH['Evn_rid']]['IDCASE_P'];
				} else if (!empty($oldRegistry_EvnNums[$Evn_id]) && ($type == $oldRegistry_EvnNums[$Evn_id]['NRTYPE_P']) ) {
					// если нашёлся в предыдущих реестрах заполняем поля о предыдущем случае
					$PR_NOV = 1;
					$NSCHET_P = $oldRegistry_EvnNums[$Evn_id]['NSCHET_P'];
					$DSCHET_P = $oldRegistry_EvnNums[$Evn_id]['DSCHET_P'];
					$N_ZAP_P = $oldRegistry_EvnNums[$Evn_id]['N_ZAP_P'];
					$SLUCH['IDCASE_P'] = $oldRegistry_EvnNums[$Evn_id]['IDCASE_P'];
				}

				// IDCASE = N_ZAP = ID_PAC
				$N_ZAP = $this->_countZSL;
				$PACIENT['ID_PAC'] = $this->_countZSL;
				if ($isUnion) {
					$SLUCH['IDCASE'] = $this->_countSLUCH;
				}

				$ZSL['Z_SL_ID'] = $SLUCH['IDCASE']; // Z_SL_ID = SLUCH.IDCASE первого по счёту SLUCH. (refs #134711)
				$ZSL['SLUCH'] = array(
					$sl_key => $SLUCH
				);

				if (!$isUnion) {
					// только для предварительных реестров
					$N_ZAP = $ZSL['Z_SL_ID']; // N_ZAP = Z_SL_ID. (refs #135639)
				}

				$ZAP_ARRAY[$zsl_key] = array(
					'PACIENT' => array($PACIENT),
					'Z_SL_DATA' => array($ZSL),
					'N_ZAP' => $N_ZAP,
					'PR_NOV' => $PR_NOV,
					'NSCHET_P' => $NSCHET_P,
					'DSCHET_P' => $DSCHET_P,
					'N_ZAP_P' => $N_ZAP_P
				);

				$PACIENT_ARRAY[$zsl_key] = $PACIENT;
			}
		}

		// записываем оставшееся
		if (count($ZAP_ARRAY) > 0) {
			// Костыль для реализации условия выгрузки NAPR_DATA и ONK_USL_DATA в последней услуге случая для стационара и ВМП
			//
			// upd: Осталось только для NAPR_DATA
			// @task https://redmine.swan.perm.ru/issues/142848
			if ( in_array($type, array(1, 14)) ) {
				foreach ( $ZAP_ARRAY as $zslKey => $ZAP ) {
					foreach ( $ZAP['Z_SL_DATA'][0]['SLUCH'] as $slKey => $SLUCH ) {
						if ( count($SLUCH['USL']) <= 1 ) {
							continue;
						}

						foreach ( $SLUCH['USL'] as $i => $row ) {
							if ( $i == count($SLUCH['USL']) - 1 ) {
								continue;
							}

							$ZAP_ARRAY[$zslKey]['Z_SL_DATA'][0]['SLUCH'][$slKey]['USL'][$i]['NAPR_DATA'] = array();
						}
					}
				}
			}

			// пишем в файл случаи
			$xml = $this->parser->parse_ext('export_xml/' . $templ_body, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);
			// пишем в файл пациентов
			$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_body, array('PACIENT' => $PACIENT_ARRAY), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($PACIENT_ARRAY);
			unset($xml_pers);
		}

		$this->textlog->add("Задействовано памяти после выгрузки случаев и пациентов: " . (memory_get_usage() / 1024 / 1024) . " MB");

		return true;
	}

	/**
	 * Отправка в ТФОМС
	 */
	function sendUnionRegistryToTFOMS($data)
	{
		// 1. получаем файл
		$query = "
			SELECT
				R.Registry_EvnNum as \"Registry_EvnNum\",
				R.Registry_xmlExportPath as \"Registry_xmlExportPath\",
				R.Registry_ExportSign as \"Registry_ExportSign\",
				R.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				RCS.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\"
			FROM
				{$this->scheme}.v_Registry R
				left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			WHERE
				Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id'=>$data['Registry_id']));
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_xmlExportPath'])) {
				$data['Registry_xmlExportPath'] = $resp[0]['Registry_xmlExportPath'];
			}

			if (!empty($resp[0]['Registry_ExportSign'])) {
				$data['Registry_ExportSign'] = $resp[0]['Registry_ExportSign'];
			}

			if (!empty($resp[0]['RegistryCheckStatus_id']) && !in_array($resp[0]['RegistryCheckStatus_Code'],array(4,6,12))) {
				return array('Error_Msg' => 'Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра не возможна. ');
			}
		}
		if (empty($data['Registry_xmlExportPath']) || !file_exists($data['Registry_xmlExportPath'])) {
			return array('Error_Msg' => 'XML-файл реестра не найден');
		}
		if (empty($data['Registry_ExportSign']) || !file_exists($data['Registry_ExportSign'])) {
			return array('Error_Msg' => 'Подписанный XML-файл реестра не найден');
		}

		// 3. сохраняем ссылку новый файл
		$data['Status'] = $resp[0]['Registry_xmlExportPath'];
		$data['Registry_EvnNum'] = $resp[0]['Registry_EvnNum'];
		$data['RegistryCheckStatus_id'] = 1;
		$this->SetXmlExportStatus($data);

		// 4. отдаём на клиент true =)
		return array('Error_Msg' => '');
	}

	/**
	 *	Установка статуса экспорта реестра в XML
	 */
	function SetXmlExportStatus($data)
	{
		if ($this->scheme=='dbo') {
			$this->setRegistryCheckStatus($data);
		}

		if (empty($data['Registry_EvnNum']))
		{
			$data['Registry_EvnNum'] = null;
		}

		if (empty($data['Registry_ExportSign']))
		{
			$data['Registry_ExportSign'] = null;
		}

		if ((0 != $data['Registry_id']))
		{
			$query = "
				update {$this->scheme}.Registry
				set
					Registry_xmlExportPath = :Status,
					Registry_ExportSign = :Registry_ExportSign,
					Registry_EvnNum = :Registry_EvnNum,
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
					'Registry_ExportSign' => $data['Registry_ExportSign'],
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
				array('RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'Проверенные ТФОМС'),
				array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
				array('RegistryStatus_id' => 12, 'RegistryStatus_Name' => 'Удаленные')
			);
		}

		return $result;
	}

	/**
	 *	Функция возрвращает массив годов, в которых есть реестры
	 */
	function getYearsList($data)
	{
		if (12 == (int)$data['RegistryStatus_id']) {
			//12 - если запрошены удаленные реестры
			$query = "
				select distinct
					date_part('year', Registry_begDate) as \"reg_year\"
				from
					{$this->scheme}.v_Registry_deleted
				where
					Lpu_id = :Lpu_id
					and RegistryType_id = :RegistryType_id
			";
		} else {
			$query = "
				select distinct
					date_part('year', Registry_begDate) as \"reg_year\"
				from
					{$this->scheme}.Registry
				where
					Lpu_id = :Lpu_id
					and RegistryStatus_id = :RegistryStatus_id
					and RegistryType_id = :RegistryType_id
			";
		}
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$response = array(array('reg_year' => date('Y')));
			}
		} else {
			return false;
		}

		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable)) {
			$archdb = $this->load->database('archive', true);
			$result = $archdb->query($query, $data);
			if ( is_object($result) ) {
				$resp_arch = $result->result('array');
				foreach($resp_arch as $resp) {
					if (!in_array($resp, $response)) {
						$response[] = $resp;
					}
				}
			} else {
				return false;
			}
		}

		return $response;
	}

	/**
	 *	Загрузка списка реестров
	 */
	function loadRegistry($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';

		$this->setRegistryParamsByType($data);

		$addToSelect = "";
		$leftjoin = "";

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			if (empty($data['RegistryStatus_id']) || $data['RegistryStatus_id'] != 11) {
				$addToSelect .= "
					, case when coalesce(R.Registry_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"
				";
				if (empty($_REQUEST['useArchive'])) {
					// только актуальные
					$filter .= " and coalesce(R.Registry_IsArchive, 1) = 1";
				} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
					// только архивные
					$filter .= " and coalesce(R.Registry_IsArchive, 1) = 2";
				} else {
					// все из архивной
					$filter .= "";
				}
			}
		}

		$addToSelect .= ", 
			R.DispClass_id as \"DispClass_id\", 
			R.Registry_IsRepeated as \"Registry_IsRepeated\", 
			R.PayType_id as \"PayType_id\", 
			pt.PayType_Name as \"PayType_Name\", 
			pt.PayType_SysNick as \"PayType_SysNick\"";
		$leftjoin = "left join v_PayType pt on pt.PayType_id = R.PayType_id";

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

		if (!empty($data['Registry_IsNew']) && $data['Registry_IsNew'] == 2) {
			$filter .= " and R.Registry_IsNew = 2";
		} else {
			$filter .= " and coalesce(R.Registry_IsNew, 1) = 1";
		}

		if (empty($data['Registry_id'])) {
			switch ($data['PayType_SysNick']) {
				// реесты по бюджету
				case 'bud':
					$filter .= " and pt.PayType_SysNick in ('bud','fbud')";
					break;
				case 'mbudtrans':
					$filter .= " and pt.PayType_SysNick in ('mbudtrans','mbudtrans_mbud')";
					break;
				default:
					$filter .= " and COALESCE(pt.PayType_SysNick, '') not in ('bud','fbud','mbudtrans','mbudtrans_mbud')";
					break;
			}
		}

		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id']==11))
		{//запрос для реестров в очереди

			$addToSelect .= ", 
				0 as \"MekErrors_IsData\", 
				0 as \"FlkErrors_IsData\", 
				0 as \"BdzErrors_IsData\", 
				0 as \"Registry_SumPaid\", 
				'' as \"Registry_sendDate\", 
				Org_mid as \"Org_mid\", 
				OrgRSchet_mid as \"OrgRSchet_mid\", 
				0 as \"Registry_NoErrSum\"";
			$addToSelect .= $this->getLoadRegistryQueueAdditionalFields();

			$query = "
				Select
					R.RegistryQueue_id as \"Registry_id\",
					R.KatNasel_id as \"KatNasel_id\",
					R.RegistryType_id as \"RegistryType_id\",
					11 as \"RegistryStatus_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					2 as \"Registry_IsActive\",
					RTrim(R.Registry_Num)||' / в очереди: '||LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
					coalesce(to_char(R.Registry_accDate, 'DD.MM.YYYY'),'') as \"Registry_accDate\",
					coalesce(to_char(R.Registry_begDate, 'DD.MM.YYYY'),'') as \"Registry_begDate\",
					coalesce(to_char(R.Registry_endDate, 'DD.MM.YYYY'),'') as \"Registry_endDate\",
					--R.Registry_Sum as \"Registry_Sum\",
					KatNasel.KatNasel_Name as \"KatNasel_Name\",
					KatNasel.KatNasel_SysNick as \"KatNasel_SysNick\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.OrgRSchet_id as \"OrgRSchet_id\",
					R.LpuFilial_id as \"LpuFilial_id\",
					R.LpuBuilding_id as \"LpuBuilding_id\",
					LpuBuilding.LpuBuilding_Name as \"LpuBuilding_Name\",
					0 as \"Registry_Count\",
					0 as \"Registry_RecordPaidCount\",
					0 as \"Registry_KdCount\",
					0 as \"Registry_KdPaidCount\",
					0 as \"Registry_Sum\",
					1 as \"Registry_IsProgress\",
					1 as \"Registry_IsNeedReform\",
					'' as \"Registry_updDate\",
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
					0 as \"RegistryErrorCom_IsData\",
					0 as \"RegistryError_IsData\",
					0 as \"RegistryPerson_IsData\",
					0 as \"RegistryNoPolis_IsData\",
					0 as \"RegistryNoPay_IsData\",
					0 as \"RegistryErrorTFOMS_IsData\",
					0 as \"RegistryErrorTFOMSType_id\",
					0 as \"RegistryNoPay_Count\",
					0 as \"RegistryNoPay_UKLSum\",
					0 as \"RegistryNoPaid_Count\",
					null as \"RegistryCheckStatus_id\",
					-1 as \"RegistryCheckStatus_Code\",
					'' as \"RegistryCheckStatus_Name\",
					null as \"RegistryCheckStatus_SysNick\",
					1 as \"Registry_IsNeedReform\",
					case when rqherror.Error_Code is not null then 2 else 1 end as \"createError\",
				    0 as \"RegistryHealDepCheckJournal_AccRecCount\",
					0 as \"RegistryHealDepCheckJournal_DecRecCount\",
					0 as \"RegistryHealDepCheckJournal_UncRecCount\"
					{$addToSelect}
				from {$this->scheme}.v_RegistryQueue R
					left join KatNasel on KatNasel.KatNasel_id = R.KatNasel_id
					left join LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id 
					{$leftjoin}
					left join lateral (
						select Error_Code
						from {$this->scheme}.v_RegistryQueueHistory
						where Registry_id = r.Registry_id
						order by RegistryQueueHistory_id desc
						limit 1
					) rqherror on true
				where {$filter}
			";
		}
		else
		{//для всех реестров, кроме тех что в очереди
			$source_table = 'v_Registry';
			if (isset($data['RegistryStatus_id']))
			{
				if (12 == (int)$data['RegistryStatus_id']) {
					//12 - если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
					//т.к. для удаленных реестров статус не важен - не накладываем никаких условий на статус реестра.
				} else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
				// только если оплаченные!!!
				if( in_array((int)$data['RegistryStatus_id'], array(4,12)) ) {
					if( $data['Registry_accYear'] > 0 ) {
						$filter .= " and date_part('year', R.Registry_begDate) <= :Registry_accYear";
						$filter .= " and date_part('year', R.Registry_endDate) >= :Registry_accYear";
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}

			$addToSelect .= ", 
				RegistryErrorMEK.MekErrors_IsData as \"MekErrors_IsData\", 
				RegistryErrorFLK.FlkErrors_IsData as \"FlkErrors_IsData\", 
				RegistryErrorBDZ.BdzErrors_IsData as \"BdzErrors_IsData\",
				coalesce(to_char(R.Registry_sendDT, 'DD.MM.YYYY HH24:MI:SS'), '') as \"Registry_sendDate\",
				coalesce(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\", 
				R.DispClass_id as \"DispClass_id\", 
				R.Registry_IsRepeated as \"Registry_IsRepeated\", 
				DispClass_Name as \"DispClass_Name\"
			";
			$leftjoin .= "
				left join v_DispClass DispClass on R.DispClass_id = DispClass.DispClass_id
				left join lateral (
					select
						rcsh.RegistryCheckStatusHistory_id,
						rcsh.Registry_CheckStatusDate,
						rcsh.Registry_id
					from
						dbo.v_RegistryCheckStatusHistory rcsh
					where
						(rcsh.Registry_id = R.Registry_id or rcsh.Registry_id in (select RGL.Registry_pid from {$this->scheme}.RegistryGroupLink RGL where RGL.Registry_id = R.Registry_id)) -- по обычному реестру и по объединённым, в которые он входит, в том числе по удалённым
						and rcsh.RegistryCheckStatus_id = 2
					order by Registry_CheckStatusDate desc
					limit 1 
				) rcsh on true
				left join {$this->scheme}.v_RegistryCheckStatusHistory rcsh2 on rcsh2.Registry_id = rcsh.Registry_id
					and rcsh2.Registry_CheckStatusDate > rcsh.Registry_CheckStatusDate
				left join lateral (
					select case when RE.Registry_id is not null then 1 else 0 end as MekErrors_IsData
					from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join RegistryErrorTFOMSType RET on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
					where RE.Registry_id = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_MEK' 
					and RE.RegistryCheckStatusHistory_id = coalesce(rcsh2.RegistryCheckStatusHistory_id, rcsh.RegistryCheckStatusHistory_id)
					limit 1
				) RegistryErrorMEK on true
				left join lateral (
					select case when RE.Registry_id is not null then 1 else 0 end as FlkErrors_IsData
					from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join RegistryErrorTFOMSType RET on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
					where RE.Registry_id = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_FLK'
					and RE.RegistryCheckStatusHistory_id = coalesce(rcsh2.RegistryCheckStatusHistory_id, rcsh.RegistryCheckStatusHistory_id)
					limit 1
				) RegistryErrorFLK on true
				left join lateral (
					select case when RE.Registry_id is not null then 1 else 0 end as BdzErrors_IsData 
					from RegistryErrorBDZ RE 
					where RE.Registry_id = R.Registry_id
					limit 1
				) RegistryErrorBDZ on true

			";
			$addToSelect .= ", coalesce('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:' || CAST(R.Registry_id as varchar) || '});''>'||RegistryCheckStatus.RegistryCheckStatus_Name||'</a>','') as \"RegistryCheckStatus_Name\"";
			$addToSelect .= ", 
				R.Org_mid as \"Org_mid\", 
				R.OrgRSchet_mid as \"OrgRSchet_mid\", 
				R.Registry_NoErrSum as \"Registry_NoErrSum\"";

			$addToSelect .= $this->getLoadRegistryAdditionalFields();
			$leftjoin .= $this->getLoadRegistryAdditionalJoin();

			$query = "
				Select
					R.Registry_id as \"Registry_id\",
					UNIONR.Registry_Num as \"RegistryUnion_Num\",
					R.KatNasel_id as \"KatNasel_id\",
					R.RegistryType_id as \"RegistryType_id\",
					" . (!empty($data['RegistryStatus_id']) && 12 == (int)$data['RegistryStatus_id'] ? "12" : "R.RegistryStatus_id") . " as \"RegistryStatus_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					R.Registry_IsActive as \"Registry_IsActive\",
					R.Registry_IsRecalc as \"Registry_IsRecalc\",
					RTrim(R.Registry_Num) as \"Registry_Num\",
					to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
					--R.Registry_Sum as \"Registry_Sum\",
					KatNasel.KatNasel_Name as \"KatNasel_Name\",
					KatNasel.KatNasel_SysNick as \"KatNasel_SysNick\",
					R.LpuFilial_id as \"LpuFilial_id\",
					R.LpuBuilding_id as \"LpuBuilding_id\",
					LpuBuilding.LpuBuilding_Name as \"LpuBuilding_Name\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.OrgRSchet_id as \"OrgRSchet_id\",
					coalesce(R.Registry_RecordCount, 0) as \"Registry_Count\",
					coalesce(R.Registry_RecordPaidCount, 0) as \"Registry_RecordPaidCount\",
					coalesce(R.Registry_KdCount, 0) as \"Registry_KdCount\",
					coalesce(R.Registry_KdPaidCount, 0) as \"Registry_KdPaidCount\",
					coalesce(R.Registry_Sum, 0.00) as \"Registry_Sum\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					--coalesce(RData.Registry_Count, 0) as \"Registry_Count\",
					--coalesce(RData.Registry_Sum, 0.00) as \"Registry_Sum\",
					to_char(R.Registry_updDT, 'DD.MM.YYYY HH24:MI:SS') as \"Registry_updDate\",

					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
					RegistryErrorCom.RegistryErrorCom_IsData as \"RegistryErrorCom_IsData\",
					RegistryError.RegistryError_IsData as \"RegistryError_IsData\",
					RegistryPerson.RegistryPerson_IsData as \"RegistryPerson_IsData\",
					RegistryNoPolis.RegistryNoPolis_IsData as \"RegistryNoPolis_IsData\",
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\",
					RegistryErrorTFOMS.RegistryErrorTFOMSType_id as \"RegistryErrorTFOMSType_id\",
					case when RegistryNoPay_Count>0 then 1 else 0 end as \"RegistryNoPay_IsData\",
					RegistryNoPay.RegistryNoPay_Count as \"RegistryNoPay_Count\",
					RegistryNoPay.RegistryNoPay_UKLSum as \"RegistryNoPay_UKLSum\",
					RegistryNoPaid.RegistryNoPaid_Count as \"RegistryNoPaid_Count\",
					to_char(RQH.RegistryQueueHistory_endDT, 'DD.MM.YYYY HH24:MI:SS') as \"ReformTime\",
					R.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
					coalesce(RegistryCheckStatus.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					RegistryCheckStatus.RegistryCheckStatus_SysNick as \"RegistryCheckStatus_SysNick\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					case when RegistryDouble.Evn_id is not null then 1 else 0 end as \"issetDouble\",
					case when RegistryDoublePL.Evn_id is not null then 1 else 0 end as \"issetDoublePl\",
					case when rqherror.Error_Code is not null then 2 else 1 end as \"createError\",
					rhdcj.RegistryHealDepCheckJournal_AccRecCount as \"RegistryHealDepCheckJournal_AccRecCount\",
					rhdcj.RegistryHealDepCheckJournal_DecRecCount as \"RegistryHealDepCheckJournal_DecRecCount\",
					rhdcj.RegistryHealDepCheckJournal_UncRecCount as \"RegistryHealDepCheckJournal_UncRecCount\"
					{$addToSelect}
				from {$this->scheme}.{$source_table} R
					left join KatNasel on KatNasel.KatNasel_id = R.KatNasel_id
					left join RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join RegistryCheckStatus on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					{$leftjoin}
					left join lateral (
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue
						where Registry_id = R.Registry_id
						limit 1
					) RQ on true
					left join lateral (
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
					left join lateral (
						select
							ru.Registry_Num
						from
							{$this->scheme}.v_Registry ru
							inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_pid = ru.Registry_id
						where
							rgl.Registry_id = r.Registry_id
						limit 1
					) unionr on true
					left join lateral (
						select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData 
						from {$this->scheme}.v_{$this->RegistryErrorComObject} RE 
						where RE.Registry_id = R.Registry_id 
						limit 1
					) RegistryErrorCom on true
					left join lateral (
						select case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData 
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE 
						where RE.Registry_id = R.Registry_id 
						limit 1
					) RegistryError on true
					left join lateral (
						select case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData 
						from {$this->scheme}.v_{$this->RegistryPersonObject} RE 
						where RE.Registry_id = R.Registry_id and coalesce(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1 and coalesce(RE.{$this->RegistryPersonObject}_IsMerge, 1) = 1 
						limit 1
					) RegistryPerson on true
					left join lateral (
						select case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData 
						from {$this->scheme}.v_RegistryNoPolis RE 
						where RE.Registry_id = R.Registry_id 
						limit 1
					) RegistryNoPolis on true
					left join lateral (
						select
							count(RegistryNoPay.Evn_id) as RegistryNoPay_Count,
							sum(RegistryNoPay.RegistryNoPay_UKLSum) as RegistryNoPay_UKLSum
						from {$this->scheme}.v_RegistryNoPay RegistryNoPay
						where RegistryNoPay.Registry_id = R.Registry_id
					) RegistryNoPay on true
					left join lateral (
						select count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
						from {$this->scheme}.v_{$this->RegistryDataObject} RDnoPaid
						where RDnoPaid.Registry_id = R.Registry_id
							and coalesce(RDnoPaid.RegistryData_isPaid, 1) = 1
					) RegistryNoPaid on true
					left join lateral (
						select
							case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData,
							RE.RegistryErrorTFOMSType_id
						from {$this->scheme}.v_RegistryErrorTFOMS RE
						where RE.Registry_id = R.Registry_id
						limit 1
					) RegistryErrorTFOMS on true
					left join lateral (
						select Error_Code
						from {$this->scheme}.v_RegistryQueueHistory
						where Registry_id = r.Registry_id
						order by RegistryQueueHistory_id desc
						limit 1
					) rqherror on true
					left join lateral (
						select RD.Evn_id
						from dbo.v_RegistryDouble RD
							inner join dbo.v_RegistryData RData on RData.Evn_id = RD.Evn_id
								and RData.Registry_id = RD.Registry_id
						WHERE RD.Registry_id = R.Registry_id
						limit 1
					) RegistryDouble on true
					left join lateral (
						select RD.Evn_id
						from dbo.v_RegistryDoublePL RD
							inner join dbo.v_RegistryData RData on RData.Evn_id = RD.Evn_id
								and RData.Registry_id = RD.Registry_id
						WHERE RD.Registry_id = R.Registry_id
						limit 1
					) RegistryDoublePL on true
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
			$filterList[] = "(RD.Evn_id = :Evn_id OR RD.EvnSectionKSG_id = :Evn_id)";
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
				EvnSectionKSG_id,
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
				Evn_didDate,
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
					RDE.EvnSectionKSG_id,
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
					RDE.Evn_didDate,
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
					{$this->scheme}.v_RegistryDataEvnPS RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					left join v_Evn e on e.Evn_id = RDE.Evn_id
				where
					RGL.Registry_pid = :Registry_id

				union all

				select
					RDE.Evn_id,
					null as EvnSectionKSG_id,
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
					RDE.Evn_didDate,
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
					null as EvnSectionKSG_id,
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
					RDE.Evn_didDate,
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

				union all

				select
					RDE.Evn_id,
					null as EvnSectionKSG_id,
					RDE.Evn_rid,
					RDE.EvnClass_id,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					epd.DispClass_id,
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
					RDE.Evn_didDate,
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
					epd.EvnPLDisp_IsArchive as Evn_IsArchive
				from
					{$this->scheme}.v_RegistryDataDisp RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RDE.Evn_rid
				where
					RGL.Registry_pid = :Registry_id

				union all

				select
					RDE.Evn_id,
					null as EvnSectionKSG_id,
					RDE.Evn_rid,
					RDE.EvnClass_id,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					epd.DispClass_id,
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
					RDE.Evn_didDate,
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
					epd.EvnPLDisp_IsArchive as Evn_IsArchive
				from
					{$this->scheme}.v_RegistryDataProf RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RDE.Evn_rid
				where
					RGL.Registry_pid = :Registry_id

				union all

				select
					RDE.Evn_id,
					null as EvnSectionKSG_id,
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
					RDE.Evn_didDate,
					RDE.RegistryData_Tariff,
					RDE.RegistryData_KdPay,
					RDE.RegistryData_KdPlan,
					RDE.RegistryData_ItogSum,
					RDE.Person_SurName,
					RDE.Person_FirName,
					RDE.Person_SecName,
					RDE.Polis_Num,
					RDE.MedPersonal_id,
					case when GroupInfo.Evn_id is not null then 2 else 1 end as IsGroupEvn,
					e.Evn_IsArchive
				from
					{$this->scheme}.v_RegistryDataPar RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					left join lateral (
						select Evn_id
						from {$this->scheme}.RegistryDataPar
						where Registry_id = RDE.Registry_id
							and MaxEvn_id = RDE.Evn_id
							and Number is null
						limit 1
					) GroupInfo on true
					left join v_Evn e on e.Evn_id = RDE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
			)
			-- end addit with

			Select
				-- select
				 RD.Evn_id as \"Evn_id\"
			 	,RD.EvnSectionKSG_id as \"EvnSectionKSG_id\"
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
				,case when RD.needReform = 2 and RQ.RegistryQueue_id is not null then 2 else 1 end \"isNoEdit\"
				,RD.RegistryData_KdFact as \"RegistryData_Uet\"
				,RD.RegistryData_deleted as \"RegistryData_deleted\"
				,RTrim(RD.NumCard) as \"EvnPL_NumCard\"
				,RTrim(RD.Person_FIO) as \"Person_FIO\"
				,to_char(RD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
				,CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\"
				,RD.LpuSection_id as \"LpuSection_id\"
				,RTrim(RD.LpuSection_name) as \"LpuSection_name\"
				,RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\"
				,to_char((case when R.RegistryType_id = 7 then RD.Evn_didDate else RD.Evn_setDate end), 'DD.MM.YYYY') as \"EvnVizitPL_setDate\"
				,to_char(RD.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\"
				,to_char(RD.RegistryData_Tariff, 'DD.MM.YYYY') as \"RegistryData_Tariff\"
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
					select RDLT.Person_id
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
					where RD.Evn_id = COALESCE(RET.CmpCallCard_id, RET.Evn_id)
						and RD.Registry_id = RET.Registry_id
						and RET.RegistryErrorTFOMSLevel_id = 1
				) RegistryErrorTFOMS on true
				left join lateral (
					select PersonEvn_id
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

		$this->setRegistryParamsByType($data);

		if ( empty($this->RegistryDataObject) ) {
			$response = array();
			$response['data'] = array();
			$response['totalCount'] = 0	;
			return $response;
		}

		$filter="(1=1)";
		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$returnQueryOnly = isset($data['returnQueryOnly']);

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
			$filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			if ( $this->RegistryType_id == 1 ) {
				$filter .= " and (RE.Evn_id = :Evn_id OR RD.EvnSectionKSG_id = :Evn_id) ";
				$params['Evn_id'] = $data['Evn_id'];
			} else {
				$filter .= " and RE.{$this->RegistryDataEvnField} = :Evn_id ";
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		$addToSelect = "";
		$leftjoin = "";

		$addToSelect .= ", retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\"";
		$leftjoin .= " left join v_RegistryErrorTFOMSLevel retl on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id";

		if ( in_array($this->RegistryType_id, [7, 9, 12]) ) {
			$leftjoin .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id as \"DispClass_id\"";
		}

		if ( $this->RegistryType_id == 15 ) {
			$leftjoin .= "
				left join lateral (
					select Evn_id
					from {$this->scheme}.{$this->RegistryDataObject}
					where Registry_id = RE.Registry_id
						and MaxEvn_id = RD.Evn_id
						and Number is null
					limit 1
				) GroupInfo on true
			";
			$addToSelect .= ",case when GroupInfo.Evn_id is not null then 2 else 1 end as \"IsGroupEvn\"";
		}

		if ( $this->RegistryType_id == 1 ) {
			$addToSelect .= ", RD.EvnSectionKSG_id as \"EvnSectionKSG_id\"";
		}

		$from = "
			left join lateral (
				select
					rcsh.RegistryCheckStatusHistory_id,
					rcsh.Registry_CheckStatusDate,
					rcsh.Registry_id
				from
					{$this->scheme}.v_RegistryCheckStatusHistory rcsh
				where
					(rcsh.Registry_id = R.Registry_id or rcsh.Registry_id in (select RGL.Registry_pid from {$this->scheme}.RegistryGroupLink RGL where RGL.Registry_id = R.Registry_id)) -- по обычному реестру и по объединённым, в которые он входит, в которые он входит, в том числе по удалённым
					and rcsh.RegistryCheckStatus_id = 2
				order by Registry_CheckStatusDate desc 
			) rcsh on true
			left join {$this->scheme}.v_RegistryCheckStatusHistory rcsh2 on rcsh2.Registry_id = rcsh.Registry_id
				and rcsh2.Registry_CheckStatusDate > rcsh.Registry_CheckStatusDate
			inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
				and coalesce(rcsh2.RegistryCheckStatusHistory_id, rcsh.RegistryCheckStatusHistory_id) = RE.RegistryCheckStatusHistory_id
		";
		if (!empty($data['loadHistory'])) {
			// грузим историю ошибок
			$from = "
				inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
				left join {$this->scheme}.v_RegistryCheckStatusHistory rcsh on rcsh.RegistryCheckStatusHistory_id = re.RegistryCheckStatusHistory_id 
			";
			$addToSelect .= ",to_char(rcsh.Registry_CheckStatusDate, 'DD.MM.YYYY') as Registry_CheckStatusDate";

			if (!empty($data['Registry_CheckStatusDate'])) {
				$filter .= " and cast(rcsh.Registry_CheckStatusDate as date) = :Registry_CheckStatusDate ";
				$params['Registry_CheckStatusDate'] = $data['Registry_CheckStatusDate'];
			}
		}

		if ( $this->RegistryType_id == 6 ) {
			$query = "
				Select
					-- select
					RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
					RE.Registry_id as \"Registry_id\",
					RE.CmpCallCard_id as \"Evn_rid\",
					RE.CmpCallCard_id as \"Evn_id\",
					null as \"EvnClass_id\",
					CCLC.CmpCloseCard_id as \"CmpCloseCard_id\",
					CCC.CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
					ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
					ret.RegistryErrorType_Name as \"RegistryError_FieldName\",
					ret.RegistryErrorType_Descr as \"RegistryError_Comment\",
					rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
					ps.Person_id as \"Person_id\",
					ps.PersonEvn_id as \"PersonEvn_id\",
					ps.Server_id as \"Server_id\",
					coalesce(to_char(ps.Person_BirthDay, 'DD.MM.YYYY'),'') as \"Person_BirthDay\",
					RE.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
					RE.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
					RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
					MP.Person_Fio as \"MedPersonal_Fio\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
					case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
					coalesce(osmo.OrgSMO_Nick, puc.pmUser_Name) as \"RegistryErrorTFOMS_Source\",
					uc.UslugaComplex_Code as \"UslugaComplex_Code\",
					uc.UslugaComplex_Name as \"UslugaComplex_Name\"
					{$addToSelect}
					-- end select
				from
					-- from
					{$this->scheme}.v_Registry R
					{$from}
					left join v_OrgSMO osmo on osmo.OrgSMO_id = RE.OrgSMO_id
					left join v_pmUserCache puc on puc.pmUser_id = RE.pmUser_insID
					left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.CmpCallCard_id
					left join v_CmpCallCard ccc on ccc.CmpCallCard_id = RE.CmpCallCard_id
					left join v_CmpCloseCard cclc on cclc.CmpCallCard_id = ccc.CmpCallCard_id
					left join lateral (
						select
							 pa.PersonEvn_id
							,pa.Server_id
							,pa.Person_id
							,coalesce(pa.Person_SurName, '') as Person_Surname
							,coalesce(pa.Person_FirName, '') as Person_Firname
							,coalesce(pa.Person_SecName, '') as Person_Secname
							,pa.Person_BirthDay as Person_Birthday
							,coalesce(pa.Sex_id, 0) as Sex_id
							,pa.Person_EdNum
							,pa.Polis_id
						from
							v_Person_bdz pa
						where
							Person_id = CCC.Person_id
							and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
						order by
							PersonEvn_insDT desc
						limit 1
					) PS on true
					left join RegistryErrorTFOMSField RETF on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
					left join RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
					left join v_EvnUsluga eu on eu.EvnUsluga_id = RE.Evn_uid
					left join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					left join lateral (
						select Person_Fio from v_MedPersonal where MedPersonal_id = ccc.MedPersonal_id limit 1
					) as MP on true
					{$leftjoin}
					-- end from
				where
					-- where
					R.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RE.RegistryErrorType_Code
					-- end order by
			";
		} else {
			$query = "
				Select
					-- select
					RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
					RE.Registry_id as \"Registry_id\",
					Evn.Evn_rid as \"Evn_rid\",
					RE.Evn_id as \"Evn_id\",
					Evn.EvnClass_id as \"EvnClass_id\",
					null as \"CmpCloseCard_id\",
					null as \"CmpCallCardInputType_id\",
					ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
					ret.RegistryErrorType_Name as \"RegistryError_FieldName\",
					ret.RegistryErrorType_Descr || ' (' || RETF.RegistryErrorTFOMSField_Name || ')' as \"RegistryError_Comment\",
					rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
					ps.Person_id as \"Person_id\",
					ps.PersonEvn_id as \"PersonEvn_id\",
					ps.Server_id as \"Server_id\",
					coalesce(to_char(ps.Person_BirthDay, 'DD.MM.YYYY'),'') as \"Person_BirthDay\",
					RE.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
					RE.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
					RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
					MP.Person_Fio as \"MedPersonal_Fio\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
					case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
					coalesce(osmo.OrgSMO_Nick, puc.pmUser_Name) as \"RegistryErrorTFOMS_Source\",
					uc.UslugaComplex_Code as \"UslugaComplex_Code\",
					uc.UslugaComplex_Name as \"UslugaComplex_Name\"
					{$addToSelect}
					-- end select
				from
					-- from
					{$this->scheme}.v_Registry R
					{$from}
					left join v_OrgSMO osmo on osmo.OrgSMO_id = RE.OrgSMO_id
					left join v_pmUserCache puc on puc.pmUser_id = RE.pmUser_insID
					left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
					left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
					left join RegistryErrorTFOMSField RETF on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
					left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
					left join RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
					left join v_EvnUsluga eu on eu.EvnUsluga_id = RE.Evn_uid
					left join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					left join v_EvnSection es on ES.EvnSection_id = RD.Evn_id
					left join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = RD.Evn_id
					left join lateral (
						select Person_Fio from v_MedPersonal where MedPersonal_id = coalesce(ES.MedPersonal_id, evpl.MedPersonal_id) limit 1
					) as MP on true
					{$leftjoin}
					-- end from
				where
					-- where
					R.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RE.RegistryErrorType_Code
					-- end order by
			";
		}

		//echo getDebugSql($query, $params);exit;

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
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

		$this->setRegistryParamsByType($data);

		$addToSelect = '';
		$filter="(1=1)";
		$params = [
			'Registry_id' => $data['Registry_id']
		];
		$returnQueryOnly = isset($data['returnQueryOnly']);

		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['RegistryErrorBDZType_id']))
		{
			if ($data['RegistryErrorBDZType_id'] == 1) {
				$filter .= " and RE.BDZ_id is null ";
			} else if ($data['RegistryErrorBDZType_id'] == 2) {
				$filter .= " and RE.BDZ_id is not null ";
			}
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.{$this->RegistryDataEvnField} = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$from = "
			left join lateral (
				select
					rcsh.RegistryCheckStatusHistory_id,
					rcsh.Registry_CheckStatusDate,
					rcsh.Registry_id
				from
					{$this->scheme}.v_RegistryCheckStatusHistory rcsh
				where
					(rcsh.Registry_id = R.Registry_id or rcsh.Registry_id in (select RGL.Registry_pid from {$this->scheme}.RegistryGroupLink RGL where RGL.Registry_id = R.Registry_id)) -- по обычному реестру и по объединённым, в которые он входит, в которые он входит, в том числе по удалённым
					and rcsh.RegistryCheckStatus_id = 2
				order by Registry_CheckStatusDate desc
				limit 1 
			) rcsh on true
			left join {$this->scheme}.v_RegistryCheckStatusHistory rcsh2 on rcsh2.Registry_id = rcsh.Registry_id
				and rcsh2.Registry_CheckStatusDate > rcsh.Registry_CheckStatusDate
			inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
				and coalesce(rcsh2.RegistryCheckStatusHistory_id, rcsh.RegistryCheckStatusHistory_id) = RE.RegistryCheckStatusHistory_id
		";
		if (!empty($data['loadHistory'])) {
			// грузим историю ошибок
			$from = "
				inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
				left join {$this->scheme}.v_RegistryCheckStatusHistory rcsh on rcsh.RegistryCheckStatusHistory_id = re.RegistryCheckStatusHistory_id 
			";
			$addToSelect .= ",to_char(rcsh.Registry_CheckStatusDate, 'DD.MM.YYYY') as \"Registry_CheckStatusDate\"";

			if (!empty($data['Registry_CheckStatusDate'])) {
				$filter .= " and cast(rcsh.Registry_CheckStatusDate as date) = :Registry_CheckStatusDate ";
				$params['Registry_CheckStatusDate'] = $data['Registry_CheckStatusDate'];
			}
		}

		if ($this->RegistryType_id == 6) {
			$query = "
				Select 
					-- select
					RE.RegistryErrorBDZ_id as \"RegistryErrorBDZ_id\",
					RE.Registry_id as \"Registry_id\",
					RE.CmpCallCard_id as \"Evn_rid\",
					RE.CmpCallCard_id as \"Evn_id\",
					null as \"EvnClass_id\",
					case when (rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) <> rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, ''))) then
						rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) || '<br/><font color=\"red\">'||rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, ''))||'</font>'
					else
						rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, ''))
					end as \"Person_FIO\",
					ps.Person_id as \"Person_id\", 
					re.Person_id as \"Person2_id\",
					ps.PersonEvn_id as \"PersonEvn_id\", 
					ps.Server_id as \"Server_id\", 
					case when RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) != RTrim(coalesce(to_char(cast(re.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) then
						RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) || '<br/><font color=\"red\">'||RTrim(coalesce(to_char(cast(re.Person_BirthDay as timestamp),'DD.MM.YYYY'),''))||'</font>'
					else
						RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),''))
					end as \"Person_BirthDay\",
					case when rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,'')) <> rtrim(coalesce(re.Polis_Ser, '')) ||' №'||rtrim(coalesce(re.Polis_Num,'')) then
						rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,'')) || '<br/><font color=\"red\">'||rtrim(coalesce(re.Polis_Ser, '')) ||' №'||rtrim(coalesce(re.Polis_Num,'')) ||'</font>'
					else 
						rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,''))
					end
					as \"Person_Polis\",
					-- coalesce(to_char(cast(pol.Polis_begDate as timestamp),'DD.MM.YYYY'),'...') || ' - ' || coalesce(to_char(cast(pol.Polis_endDate as timestamp),'DD.MM.YYYY'),'...') as \"Person_PolisDate\",
					coalesce(to_char(cast(CCC.CmpCallCard_prmDT as timestamp),'DD.MM.YYYY'),'...') as \"Person_EvnDate\",
					coalesce(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') || '-' || coalesce(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') || coalesce('<br/><font color=\"red\">'||coalesce(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') || '-' || coalesce(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')||' '||RTrim(OrgSMO2.OrgSMO_Nick)||'<font>','') as \"Person_OrgSmo\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
					case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
					RE.RegistryErrorBDZ_Comment as \"RegistryErrorBDZ_Comment\"
					-- end select
				from 
					-- from
					{$this->scheme}.v_Registry R
					{$from}
					left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.CmpCallCard_id
					left join v_CmpCallCard ccc on ccc.CmpCallCard_id = RE.CmpCallCard_id
					left join lateral (
						select
							 pa.PersonEvn_id
							,pa.Server_id
							,pa.Person_id
							,coalesce(pa.Person_SurName, '') as Person_Surname
							,coalesce(pa.Person_FirName, '') as Person_Firname
							,coalesce(pa.Person_SecName, '') as Person_Secname
							,pa.Person_BirthDay as Person_Birthday
							,coalesce(pa.Sex_id, 0) as Sex_id
							,pa.Person_EdNum
							,pa.Polis_id
						from
							v_Person_bdz pa
						where
							Person_id = CCC.Person_id
							and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
						order by
							PersonEvn_insDT desc
						limit 1 
					) PS on true
					left join v_Polis pol on pol.Polis_id = ps.Polis_id
					left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = RE.OrgSmo_id
					left join v_OrgSmo OrgSmo2 on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
					-- end from
				where
					-- where
					R.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RE.RegistryErrorBDZ_id
					-- end order by
			";
		} else {
			$query = "
				Select 
					-- select
					RE.RegistryErrorBDZ_id as \"RegistryErrorBDZ_id\",
					RE.Registry_id as \"Registry_id\",
					Evn.Evn_rid as \"Evn_rid\",
					RE.Evn_id as \"Evn_id\",
					Evn.EvnClass_id as \"EvnClass_id\",
					case when (rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) <> rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, ''))) then
						rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) || '<br/><font color=\"red\">'||rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, ''))||'</font>'
					else
						rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, ''))
					end as \"Person_FIO\",
					ps.Person_id as \"Person_id\", 
					re.Person_id as \"Person2_id\",
					ps.PersonEvn_id as \"PersonEvn_id\", 
					ps.Server_id as \"Server_id\", 
					case when RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) != RTrim(coalesce(to_char(cast(re.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) then
						RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) || '<br/><font color=\"red\">'||RTrim(coalesce(to_char(cast(re.Person_BirthDay as timestamp),'DD.MM.YYYY'),''))||'</font>'
					else
						RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),''))
					end as \"Person_BirthDay\",
					case when rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,'')) <> rtrim(coalesce(re.Polis_Ser, '')) ||' №'||rtrim(coalesce(re.Polis_Num,'')) then
						rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,'')) || '<br/><font color=\"red\">'||rtrim(coalesce(re.Polis_Ser, '')) ||' №'||rtrim(coalesce(re.Polis_Num,'')) ||'</font>'
					else 
						rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,''))
					end
					as \"Person_Polis\",
					-- coalesce(to_char(cast(pol.Polis_begDate as timestamp),'DD.MM.YYYY'),'...') || ' - ' || coalesce(to_char(cast(pol.Polis_endDate as timestamp),'DD.MM.YYYY'),'...') as \"Person_PolisDate\",
					coalesce(to_char(cast(Evn.Evn_setDT as timestamp),'DD.MM.YYYY'),'...') || ' - ' || coalesce(to_char(cast(Evn.Evn_disDate as timestamp),'DD.MM.YYYY'),'...') as \"Person_EvnDate\",
					coalesce(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') || '-' || coalesce(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') || coalesce('<br/><font color=\"red\">'||coalesce(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') || '-' || coalesce(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')||' '||RTrim(OrgSMO2.OrgSMO_Nick)||'<font>','') as \"Person_OrgSmo\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
					case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
					RE.RegistryErrorBDZ_Comment as \"RegistryErrorBDZ_Comment\"
					-- end select
				from 
					-- from
					{$this->scheme}.v_Registry R
					{$from}
					left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
					left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
					left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
					left join v_Polis pol on pol.Polis_id = ps.Polis_id
					left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = RE.OrgSmo_id
					left join v_OrgSmo OrgSmo2 on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
					-- end from
				where
					-- where
					R.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RE.RegistryErrorBDZ_id
					-- end order by
			";
		}

		//echo getDebugSql($query, $params);exit;

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
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
			$filter .= " and (RE.Evn_id = :Evn_id OR RE.EvnSectionKSG_id = :Evn_id) ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$query = "
			-- addit with
			with RegErr as (
				select
					row_number() OVER (ORDER BY RE.RegistryErrorTFOMS_id) AS id,
					R.RegistryType_id,
					RE.Registry_id,
					COALESCE(RE.CmpCallCard_id, RE.Evn_id) as Evn_id,
					RE.RegistryErrorTFOMS_id,
					RE.RegistryErrorType_Code,
					RE.RegistryErrorTFOMS_FieldName,
					RE.RegistryErrorTFOMS_BaseElement,
					RE.RegistryErrorTFOMS_Comment,
					RE.RegistryErrorType_id,
					RE.RegistryErrorTFOMSLevel_id,
					RE.OrgSMO_id,
					RE.Evn_uid,
					RE.pmUser_insID
				from 
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
				where 
					1 = 1
					and RGL.Registry_pid = :Registry_id
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id
			),
			RE (
				Evn_id,
				EvnSectionKSG_id,
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
				OrgSMO_id,
				Evn_uid,
				pmUser_insID,
				IsGroupEvn
			) as (
				select
					RD.Evn_id,
					RD.EvnSectionKSG_id,
					RD.Registry_id,
					RRER.RegistryType_id,
					RD.Evn_rid,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RRER.RegistryErrorTFOMS_id,
					RRER.RegistryErrorType_Code,
					RRER.RegistryErrorTFOMS_FieldName,
					RRER.RegistryErrorTFOMS_BaseElement,
					RRER.RegistryErrorTFOMS_Comment,
					RRER.RegistryErrorType_id,
					RRER.RegistryErrorTFOMSLevel_id,
					RRER.OrgSMO_id,
					RRER.Evn_uid,
					RRER.pmUser_insID,
					null as IsGroupEvn
				from
					RegErr RRER
					inner join {$this->scheme}.v_RegistryDataEvnPS RD on RD.Registry_id = RRER.Registry_id
				where
					1=1
					and RRER.Evn_id = RD.Evn_id
					and RRER.RegistryType_id IN (1, 14)

				union all

				select
					RD.Evn_id,
					null as EvnSectionKSG_id,
					RD.Registry_id,
					RRER.RegistryType_id,
					RD.Evn_rid,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RRER.RegistryErrorTFOMS_id,
					RRER.RegistryErrorType_Code,
					RRER.RegistryErrorTFOMS_FieldName,
					RRER.RegistryErrorTFOMS_BaseElement,
					RRER.RegistryErrorTFOMS_Comment,
					RRER.RegistryErrorType_id,
					RRER.RegistryErrorTFOMSLevel_id,
					RRER.OrgSMO_id,
					RRER.Evn_uid,
					RRER.pmUser_insID,
					null as IsGroupEvn
				from
					RegErr RRER
					inner join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RRER.Registry_id
				where
					1 = 1
					and RRER.Evn_id = RD.Evn_id
					and RRER.RegistryType_id in (2, 16)

				union all

				select
					RD.Evn_id,
					null as EvnSectionKSG_id,
					RD.Registry_id,
					RRER.RegistryType_id,
					RD.Evn_rid,
					CCLC.CmpCloseCard_id,
					CCC.CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RRER.RegistryErrorTFOMS_id,
					RRER.RegistryErrorType_Code,
					RRER.RegistryErrorTFOMS_FieldName,
					RRER.RegistryErrorTFOMS_BaseElement,
					RRER.RegistryErrorTFOMS_Comment,
					RRER.RegistryErrorType_id,
					RRER.RegistryErrorTFOMSLevel_id,
					RRER.OrgSMO_id,
					RRER.Evn_uid,
					RRER.pmUser_insID,
					null as IsGroupEvn
				from
					RegErr RRER
					inner join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RRER.Registry_id
					left join v_CmpCallCard CCC on CCC.CmpCallCard_id = RD.Evn_id
					left join v_CmpCloseCard CCLC on CCLC.CmpCallCard_id = CCC.CmpCallCard_id
				where
					1 = 1
					and RRER.Evn_id = RD.Evn_id
					and RRER.RegistryType_id = 6

				union all

				select
					RD.Evn_id,
					null as EvnSectionKSG_id,
					RD.Registry_id,
					RRER.RegistryType_id,
					RD.Evn_rid,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RRER.RegistryErrorTFOMS_id,
					RRER.RegistryErrorType_Code,
					RRER.RegistryErrorTFOMS_FieldName,
					RRER.RegistryErrorTFOMS_BaseElement,
					RRER.RegistryErrorTFOMS_Comment,
					RRER.RegistryErrorType_id,
					RRER.RegistryErrorTFOMSLevel_id,
					RRER.OrgSMO_id,
					RRER.Evn_uid,
					RRER.pmUser_insID,
					null as IsGroupEvn
				from
					RegErr RRER
					inner join {$this->scheme}.v_RegistryDataDisp RD on RD.Registry_id = RRER.Registry_id
				where
					1 = 1
					and RD.Evn_id = RRER.Evn_id
					and RRER.RegistryType_id IN (4, 5, 7, 9)

				union all

				select
					RD.Evn_id,
					null as EvnSectionKSG_id,
					RD.Registry_id,
					RRER.RegistryType_id,
					RD.Evn_rid,
					null as CmpCloseCard_id,
					null as CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RRER.RegistryErrorTFOMS_id,
					RRER.RegistryErrorType_Code,
					RRER.RegistryErrorTFOMS_FieldName,
					RRER.RegistryErrorTFOMS_BaseElement,
					RRER.RegistryErrorTFOMS_Comment,
					RRER.RegistryErrorType_id,
					RRER.RegistryErrorTFOMSLevel_id,
					RRER.OrgSMO_id,
					RRER.Evn_uid,
					RRER.pmUser_insID,
					null as IsGroupEvn
				from
					RegErr RRER
					inner join {$this->scheme}.v_RegistryDataProf RD on RD.Registry_id = RRER.Registry_id
				where
					1 = 1
					and RRER.Evn_id = RD.Evn_id
					and RRER.RegistryType_id IN (11, 12)

				union all

				select
					RD.Evn_id,
					null as EvnSectionKSG_id,
					RD.Registry_id,
					RRER.RegistryType_id,
					RD.Evn_rid,
					null as CmpCloseCard_id,
                    null as CmpCallCardInputType_id,
					RD.RegistryData_deleted,
					RRER.RegistryErrorTFOMS_id,
					RRER.RegistryErrorType_Code,
					RRER.RegistryErrorTFOMS_FieldName,
					RRER.RegistryErrorTFOMS_BaseElement,
					RRER.RegistryErrorTFOMS_Comment,
					RRER.RegistryErrorType_id,
					RRER.RegistryErrorTFOMSLevel_id,
					RRER.OrgSMO_id,
					RRER.Evn_uid,
					RRER.pmUser_insID,
					case when GroupInfo.Evn_id is not null then 2 else 1 end as IsGroupEvn
				from
					RegErr RRER
					inner join {$this->scheme}.v_RegistryDataPar RD on RD.Registry_id = RRER.Registry_id
					left join lateral (
						select Evn_id
						from {$this->scheme}.RegistryDataPar
						where Registry_id = RD.Registry_id
							and MaxEvn_id = RD.Evn_id
							and Number is null
						limit 1
					) GroupInfo on true
				where
					1 = 1
					and RRER.Evn_id = RD.Evn_id
					and RRER.RegistryType_id = 15
				
				union all

				select
					null as Evn_id,
					null as EvnSectionKSG_id,
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
					RE.OrgSMO_id,
					RE.Evn_uid,
					RE.pmUser_insID,
					null as IsGroupEvn
				from
					{$this->scheme}.v_Registry R
					inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
				where
					R.Registry_id = :Registry_id
					and RE.RegistryErrorTFOMS_IdCase =  ''
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
				RE.EvnSectionKSG_id as \"EvnSectionKSG_id\",
				RE.CmpCloseCard_id as \"CmpCloseCard_id\",
				RE.CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
				Evn.EvnClass_id as \"EvnClass_id\",
				ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				RegistryErrorType_Name as \"RegistryError_FieldName\",
				ret.RegistryErrorType_Descr as \"RegistryError_Comment\",
				rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) as \"Person_BirthDay\",
				RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
				RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
				RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				coalesce(RE.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RE.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\",
				coalesce(osmo.OrgSMO_Nick, puc.pmUser_Name) as \"RegistryErrorTFOMS_Source\",
				RE.OrgSMO_id as \"OrgSMO_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				MP.Person_Fio as \"MedPersonal_Fio\",
				RE.IsGroupEvn as \"IsGroupEvn\"
				-- end select
			from
				-- from
				RE
				left join v_OrgSMO osmo on osmo.OrgSMO_id = RE.OrgSMO_id
				left join v_pmUserCache puc on puc.pmUser_id = RE.pmUser_insID
				left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
				left join RegistryErrorTFOMSField RETF on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
				left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_RegistryErrorTFOMSLevel retl on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
				left join v_EvnUsluga eu on eu.EvnUsluga_id = RE.Evn_uid
				left join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				left join v_EvnSection es on ES.EvnSection_id = RE.Evn_id
				left join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = RE.Evn_id
				left join v_CmpCallCard ccc with on ccc.CmpCallCard_id = RE.Evn_id
				left join lateral(
					select
						Person_Fio
					from v_MedPersonal
					where MedPersonal_id = COALESCE(ES.MedPersonal_id, evpl.MedPersonal_id,ccc.MedPersonal_id)
					limit 1
				) as MP on true
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
				-- end order by
		";
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

	/**
	 *	Список ошибок БДЗ
	 */
	function loadUnionRegistryErrorBDZ($data)
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
			$filter .= " and rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['RegistryErrorBDZType_id']))
		{
			if ($data['RegistryErrorBDZType_id'] == 1) {
				$filter .= " and RE.BDZ_id is null ";
			} else if ($data['RegistryErrorBDZType_id'] == 2) {
				$filter .= " and RE.BDZ_id is not null ";
			}
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
				BDZ_id,
				Registry_id,
				RegistryType_id,
				Evn_rid,
				RegistryData_deleted,
				OrgSmo_id,
				OrgSmo_bdzid,
				RegistryErrorBDZ_id,
				Person_id,
				Person_BirthDay,
				Polis_Ser,
				Polis_Num,
				RegistryErrorBDZ_Comment,
				Person_SurName,
				Person_FirName,
				Person_SecName
			) as (
				select
					RD.Evn_id,
					RE.BDZ_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					RD.RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join {$this->scheme}.v_RegistryDataEvnPS RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id IN (1, 14)
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					RD.Evn_id,
					RE.BDZ_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					RD.RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join {$this->scheme}.v_RegistryData RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id in (2, 16)
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					RD.Evn_id,
					RE.BDZ_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					RD.RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join {$this->scheme}.v_RegistryDataCmp RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.CmpCallCard_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 6
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					RD.Evn_id,
					RE.BDZ_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					RD.RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join {$this->scheme}.v_RegistryDataDisp RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id IN (4, 5, 7, 9)
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					RD.Evn_id,
					RE.BDZ_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					RD.RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join {$this->scheme}.v_RegistryDataProf RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id IN (11, 12)
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					RD.Evn_id,
					RE.BDZ_id,
					RD.Registry_id,
					R.RegistryType_id,
					RD.Evn_rid,
					RD.RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_RegistryGroupLink RGL
					inner join v_Registry R on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
					left join v_RegistryCheckStatusHistory RCSH on RCSH.RegistryCheckStatusHistory_id = RE.RegistryCheckStatusHistory_id
					left join {$this->scheme}.v_RegistryDataPar RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				where
					RGL.Registry_pid = :Registry_id
					and R.RegistryType_id = 15
					and coalesce(RCSH.Registry_id, :Registry_id) = :Registry_id

				union all

				select
					null as Evn_id,
					RE.BDZ_id,
					R.Registry_id,
					R.RegistryType_id,
					null as Evn_rid,
					null as RegistryData_deleted,
					RE.OrgSmo_id,
					RE.OrgSmo_bdzid,
					RE.RegistryErrorBDZ_id,
					RE.Person_id,
					RE.Person_BirthDay,
					RE.Polis_Ser,
					RE.Polis_Num,
					RE.RegistryErrorBDZ_Comment,
					RE.Person_SurName,
					RE.Person_FirName,
					RE.Person_SecName
				from
					v_Registry R
					inner join {$this->scheme}.v_RegistryErrorBDZ RE on RE.Registry_id = R.Registry_id
				where
					R.Registry_id = :Registry_id
					and RE.RegistryErrorBDZ_IdCase is null
			)
			-- end addit with

			Select
				-- select
				RE.RegistryErrorBDZ_id as \"RegistryErrorBDZ_id\",
				RE.Registry_id as \"Registry_id\",
				RE.RegistryType_id as \"RegistryType_id\",
				Evn.Evn_rid as \"Evn_rid\",
				RE.Evn_id as \"Evn_id\",
				Evn.EvnClass_id as \"EvnClass_id\",
				case when (rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) <> rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, ''))) then
					rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) || '<br/><font color=\"red\">'||rtrim(coalesce(re.Person_SurName,'')) || ' ' || rtrim(coalesce(re.Person_FirName,'')) || ' ' || rtrim(coalesce(re.Person_SecName, ''))||'</font>'
				else
					rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, ''))
				end as \"Person_FIO\",
				ps.Person_id as \"Person_id\",
				re.Person_id as \"Person2_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				case when RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) != RTrim(coalesce(to_char(cast(re.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) then
					RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),'')) || '<br/><font color=\"red\">'||RTrim(coalesce(to_char(cast(re.Person_BirthDay as timestamp),'DD.MM.YYYY'),''))||'</font>'
				else
					RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp),'DD.MM.YYYY'),''))
				end as \"Person_BirthDay\",
				case when rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,'')) <> rtrim(coalesce(re.Polis_Ser, '')) ||' №'||rtrim(coalesce(re.Polis_Num,'')) then
					rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,'')) || '<br/><font color=\"red\">'||rtrim(coalesce(re.Polis_Ser, '')) ||' №'||rtrim(coalesce(re.Polis_Num,'')) ||'</font>'
				else
					rtrim(coalesce(pol.Polis_Ser, '')) ||' №'||rtrim(coalesce(pol.Polis_Num,''))
				end
				as \"Person_Polis\",
				-- coalesce(to_char(cast(pol.Polis_begDate as timestamp),'DD.MM.YYYY'),'...') || ' - ' || coalesce(to_char(cast(pol.Polis_endDate as timestamp),'DD.MM.YYYY'),'...') as \"Person_PolisDate\",
				coalesce(to_char(cast(Evn.Evn_setDT as timestamp),'DD.MM.YYYY'),'...') || ' - ' || coalesce(to_char(cast(Evn.Evn_disDate as timestamp),'DD.MM.YYYY'),'...') as \"Person_EvnDate\",
				coalesce(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') || '-' || coalesce(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') || coalesce('<br/><font color=\"red\">'||coalesce(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') || '-' || coalesce(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')||' '||RTrim(OrgSMO2.OrgSMO_Nick)||'<font>','') as \"Person_OrgSmo\",
				RegistryErrorBDZ_Comment as \"RegistryError_Comment\",
				coalesce(RE.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RE.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\",
				RE.RegistryErrorBDZ_Comment as \"RegistryErrorBDZ_Comment\"
				-- end select
			from
				-- from
				RE
				left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
				left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join v_Polis pol on pol.Polis_id = ps.Polis_id
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = RE.OrgSmo_id
				left join v_OrgSmo OrgSmo2 on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
				-- end from
			where
				-- where
				1=1
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorBDZ_id
				-- end order by
		";

		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Проверка на дубли при добавлении реестра в очередь
	 * @task https://redmine.swan.perm.ru/issues/76752
	 */
	function checkRegistryQueueDoubles($data) {
		$query = "
			select RegistryQueue_id as \"RegistryQueue_id\"
			from v_RegistryQueue
			where Lpu_id = :Lpu_id
				and RegistryType_id = :RegistryType_id
				and coalesce(KatNasel_id, 0) = coalesce(:KatNasel_id, 0)
				and Registry_begDate <= :Registry_endDate
				and :Registry_begDate <= Registry_endDate
				and PayType_id = :PayType_id
				and coalesce(Registry_IsRepeated, 0) = coalesce(:Registry_IsRepeated, 0)
				and (
					coalesce(LpuBuilding_id, 0) = coalesce(:LpuBuilding_id, 0)
					or LpuBuilding_id is null
					or :LpuBuilding_id is null
				)
				and (
					coalesce(LpuFilial_id, 0) = coalesce(:LpuFilial_id, 0)
					or LpuFilial_id is null
					or :LpuFilial_id is null
				)
				and coalesce(DispClass_id, 0) = coalesce(:DispClass_id, 0)
				and coalesce(RegistryStacType_id, 0) = coalesce(:RegistryStacType_id, 0)
			limit 1
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return 'Ошибка при проверке на дубли реестров в очереди';
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['RegistryQueue_id']) ) {
			return 'Реестр с указанными параметрами уже формируется. Дождитесь окончания формирования реестра';
		}

		return '';
	}

	/**
	 *	Проверка вхождения случаев, в которых указано отделение, в реестр
	 *	@task https://redmine.swan.perm.ru/issues/77268
	 *	Перенес для Перми в региональную модель для учета разных типов реестров
	 */
	function checkLpuSectionInRegistry($data) {
		$filterList = array(
			'R.RegistryStatus_id = 4',
			'coalesce(RD.RegistryData_deleted, 1) = 1'
		);

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = "LS.LpuUnit_id = :LpuUnit_id";
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = "RD.LpuSection_id = :LpuSection_id";
		}

		$query = "
			-- Стационар
			(select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataEvnPS RD
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
			limit 1)

			union all

			-- Поликлиника
			(select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryData RD
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
			limit 1)

			union all

			-- СМП
			(select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataCmp RD
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
			limit 1)

			union all

			-- ДВН, ДДС, МОН
			(select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataDisp RD
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
			limit 1)

			union all

			-- ПОВН
			(select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataProf RD
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
			limit 1)

			union all

			-- Параклиника
			(select
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_RegistryDataPar RD
				left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
			where " . implode(' and ', $filterList) . "
			limit 1)
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
	 * Установка признака объединенных двойников
	 */
	function setRegistryPersonIsMerged($data) {
		$this->setRegistryParamsByType($data);

		$query = "
			update {$this->scheme}.{$this->RegistryPersonObject}
			set
				{$this->RegistryPersonObject}_IsMerge = 2
			where
				Registry_id = :Registry_id
				and MaxEvnPerson_id = :MaxEvnPerson_id
			returning null as \"Error_Msg\"
		";
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 && !empty($response[0]['Error_Msg']) ) {
			return $response[0]['Error_Msg'];
		}
		else {
			return '';
		}
	}

	/**
	 * Удаление помеченных на удаление записей и пересчет реестра 
	 */
	public function refreshRegistry($data) {
		$Registry_IsRecalc = $this->getFirstResultFromQuery("select Registry_IsRecalc from {$this->scheme}.v_Registry where Registry_id = :Registry_id limit 1", $data);

		if ( $Registry_IsRecalc == 2 ) {
			return array(
				array('Error_Msg' => 'Реестр уже пересчитывается.')
			);
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryData_Refresh (
				Registry_id := :Registry_id,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSql($query, $data);exit;
		$res = $this->db->query($query, $data);


		if ( !is_object($res) ) {
			return false;
		}

		// Выполняем вычисление суммы без ошибок
		$response = $this->setNoErrorSum($data['Registry_id']);

		if ( !is_array($response) || !empty($response[0]['Error_Msg']) ) {
			return array(
				array('Error_Msg' => 'Произошла ошибка при вычислении суммы без ошибок.')
			);
		}

		return $res->result('array');
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
			select MaxEvn_id as \"MaxEvn_id\", Evn_rid as \"Evn_rid\"
			from {$this->scheme}.v_{$this->RegistryDataObject} RD
			where RD.Registry_id = :Registry_id and RD.Evn_id = :Evn_id
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp) || count($resp) == 0) {
			return  $resp;
		}
		$params = array_merge($params, $resp[0]);

		$query = "
			select RD.Evn_id as \"Evn_id\"
			from {$this->scheme}.v_{$this->RegistryDataObject} RD
			where RD.Registry_id = :Registry_id and RD.Evn_rid = :Evn_rid
		";

		return $this->queryResult($query, $params);
	}

	/**
	 *	Помечаем запись реестра на удаление
	 */
	function deleteRegistryData($data)
	{
		$evn_list = $data['EvnIds'];

		//В Перми случаи в полке групируются по Evn_rid
		//При удалении одного случая из группы нужно удалить всю группу
		if ($data['RegistryType_id'] == 2) {
			$new_evn_list = array();

			foreach ($evn_list as $EvnId) {
				$resp = $this->getRegistryDataGroupForDelete(array(
					'Registry_id' => $data['Registry_id'],
					'Evn_id' => $EvnId
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				foreach($resp as $item) {
					$new_evn_list[] = $item['Evn_id'];
				}
			}
			$evn_list = array_unique($new_evn_list);
		}

		foreach ($evn_list as $EvnId) {
			$data['Evn_id'] = $EvnId;

			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_RegistryData_del (
					Evn_id := :Evn_id,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					RegistryData_deleted := :RegistryData_deleted
				)
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
	 *	Получение списка случаев реестра
	 */
	public function loadRegistryData($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( (isset($data['start']) && (isset($data['limit']))) && (!(($data['start'] >= 0) && ($data['limit'] >= 0))) ) {
			return false;
		}

		// В зависимости от типа реестра возвращаем разные наборы данных
		$this->setRegistryParamsByType($data);

		$fieldList = array();
		$filterList = array();
		$joinList = array();
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "RD.Person_SurName ilike :Person_SurName";
			$params['Person_SurName'] = trim($data['Person_SurName']) . "%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "RD.Person_FirName ilike :Person_FirName";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "RD.Person_SecName ilike :Person_SecName";
			$params['Person_SecName'] = rtrim($data['Person_SecName'])."%";
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
			if ( $this->RegistryType_id == 1 ) {
				$filterList[] = "(RD.Evn_id = :Evn_id OR RD.EvnSectionKSG_id = :Evn_id)";
				$params['Evn_id'] = $data['Evn_id'];
			} else {
				$filterList[] = "RD.Evn_id = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		if ( !empty($data['filterRecords']) ) {
			if ( $data['filterRecords'] == 2 ) {
				$filterList[] = "coalesce(RD.RegistryData_IsPaid, 1) = 2";
			}
			else if ( $data['filterRecords'] == 3 ) {
				$filterList[] = "coalesce(RD.RegistryData_IsPaid, 1) = 1";
			}
		}

		if ( $this->RegistryType_id == 15 ) {
			$joinList[] = "
				left join lateral (
					select Evn_id
					from {$this->scheme}.{$this->RegistryDataObject}
					where Registry_id = R.Registry_id
						and MaxEvn_id = RD.Evn_id
						and Number is null
					limit 1
				) GroupInfo on true
			";
			$fieldList[] = "case when GroupInfo.Evn_id is not null then 2 else 1 end as \"IsGroupEvn\"";

			$joinList[] = "left join v_EvnUslugaPar EUP on EUP.EvnUslugaPar_id = RD.Evn_id";
			$joinList[] = "left join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = EUP.EvnDirection_id";
			$joinList[] = "left join v_EvnLabRequest  elr on elr.EvnDirection_id = EUP.EvnDirection_id";
			$fieldList[] = "case when efr.EvnFuncRequest_id is not null then 'true' else 'false' end as \"isEvnFuncRequest\"";
			$fieldList[] = "case when elr.EvnLabRequest_id is not null then 'true' else 'false' end as \"isEvnLabRequest\"";
			$fieldList[] = "elr.MedService_id as \"MedService_id\"";
			$fieldList[] = "EUP.EvnDirection_id as \"EvnDirection_id\"";
		}

		// @task https://redmine.swan.perm.ru//issues/113170
		if ( $this->RegistryType_id == 6 ) {
			$joinList[] = "
				left join v_CmpCallCard ccc on ccc.CmpCallCard_id = rd.Evn_id
				left join v_CmpCloseCard cclc on cclc.CmpCallCard_id = ccc.CmpCallCard_id
			";
			$fieldList[] = "case when coalesce(ccc.CmpCallCard_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"";
			$fieldList[] = "cclc.CmpCloseCard_id as \"CmpCloseCard_id\"";
			$fieldList[] = "ccc.CmpCallCardInputType_id as \"CmpCallCardInputType_id\"";
		}
		else {
			$joinList[] = "
				left join v_Evn e on e.Evn_id = rd.Evn_id
			";
			$fieldList[] = "case when coalesce(e.Evn_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"";
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 12)) ) {
			$joinList[] = "left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid";
			$fieldList[] = "epd.DispClass_id";
		}

		if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
			$source_table = 'v_RegistryDeleted_Data';
		}
		else {
			$source_table = 'v_' . $this->RegistryDataObject;
		}

		if ( $this->RegistryType_id == 2 ) {
			$joinList[] = "
				left join lateral (
					select count(*) as EvnPLCrossed_Count
					from {$this->scheme}.v_RegistryDouble RE
					where RD.Evn_id = RE.Evn_id
						and RD.Registry_id = RE.Registry_id
				) RegistryDouble on true
			";
			$fieldList[] = "RegistryDouble.EvnPLCrossed_Count as \"EvnPLCrossed_Count\"";
		}

		if ( $this->RegistryType_id == 1 ) {
			$fieldList[] = $data['RegistryStatus_id'] == 12 ? "null as \"EvnSectionKSG_id\"" : "RD.EvnSectionKSG_id as \"EvnSectionKSG_id\"";
		}

		// https://redmine.swan.perm.ru/issues/35331
		$evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');

		// https://redmine.swan-it.ru/issues/168479
		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		$query = "
			Select
				-- select
				 RD.Evn_id as \"Evn_id\"
				,RD.Evn_rid as \"Evn_rid\"
				,RD.RegistryData_RowNum + RD.Evn_id as \"MaxEvn_id\"
				,RD.EvnClass_id as \"EvnClass_id\"
				,RD.Registry_id as \"Registry_id\"
				,RD.RegistryType_id as \"RegistryType_id\"
				,RD.Person_id as \"Person_id\"
				,RD.Server_id as \"Server_id\"
				,PersonEvn.PersonEvn_id as \"PersonEvn_id\"
				-- в реестрах со статусом частично принят помечаем оплаченные случаи
				,case when RCS.RegistryCheckStatus_Code = 3 then coalesce(RD.RegistryData_IsPaid, 1) else 0 end as \"RegistryData_IsPaid\"
				,case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\"
				,RD.needReform as \"needReform\"
				,RD.checkReform as \"checkReform\"
				,RD.timeReform as \"timeReform\"
				,case when RD.needReform = 2 and RQ.RegistryQueue_id is not null then 2 else 1 end \"isNoEdit\"
				,RD.RegistryData_KdFact as \"RegistryData_Uet\"
				,RD.RegistryData_deleted as \"RegistryData_deleted\"
				,RTRIM(RD.NumCard) as \"EvnPL_NumCard\"
				,RTRIM(RD.Person_FIO) as \"Person_FIO\"
				,to_char(RD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
				,CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\"
				,RD.LpuSection_id as \"LpuSection_id\"
				,RD.LpuSection_name as \"LpuSection_name\"
				,RTRIM(RD.MedPersonal_Fio) as \"MedPersonal_Fio\"
				,to_char(RD.{$evnVizitPLSetDateField}, 'DD.MM.YYYY') as \"EvnVizitPL_setDate\"
				,to_char(RD.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\"
				,RD.RegistryData_Tariff as \"RegistryData_Tariff\"
				,RD.RegistryData_KdPay as \"RegistryData_KdPay\"
				,RD.RegistryData_KdPlan as \"RegistryData_KdPlan\"
				,RD.RegistryData_ItogSum as \"RegistryData_ItogSum\"
				,RegistryError.Err_Count as \"Err_Count\"
				,RegistryErrorTFOMS.ErrTfoms_Count as \"ErrTfoms_Count\"
				,RHDCR.RegistryHealDepResType_id as \"RegistryHealDepResType_id\"
				" . (count($fieldList) > 0 ? "," . implode(', ', $fieldList) : "" ) . "
				-- end select
			from
				-- from
				{$this->scheme}.v_Registry R
				inner join {$this->scheme}.{$source_table} RD on RD.Registry_id = R.Registry_id
				left join v_LpuSection LS on LS.LpuSection_id = RD.LpuSection_id
				left join v_RegistryHealDepCheckRes RHDCR on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
				left join lateral (
					select RDLT.Person_id
					from RegistryDataLgot RDLT
					where RD.Person_id = RDLT.Person_id
						and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
					limit 1
				) RDL on true
				left join {$this->scheme}.RegistryQueue RQ on RQ.Registry_id = RD.Registry_id
				left join v_RegistryCheckStatus RCS on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id
				left join lateral (
					select count(*) as Err_Count
					from {$this->scheme}.v_{$this->RegistryErrorObject} RE
					where RD.Evn_id = RE.Evn_id
						and RD.Registry_id = RE.Registry_id
				) RegistryError on true
				left join lateral (
					select count(*) as ErrTfoms_Count
					from {$this->scheme}.v_RegistryErrorTFOMS RET
					where RD.Evn_id = RET.{$this->RegistryDataEvnField}
						and RD.Registry_id = RET.Registry_id
				) RegistryErrorTFOMS on true
				left join lateral (
					select PersonEvn_id
					from v_PersonEvn PE
					where RD.Person_id = PE.Person_id
						and PE.PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
					limit 1
				) PersonEvn on true
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "" ) . "
				-- end from
			where
				-- where
				R.Registry_id = :Registry_id
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "" ) . "
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";
		//echo getDebugSQL($query,$params);die;

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
	 *	Получение списка ошибок
	 */
	public function loadRegistryError($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		if ( empty($data['nopaging']) ) {
			if ( !(($data['start'] >= 0) && ($data['limit'] >= 0)) ) {
				return false;
			}
		}

		if ( $this->RegistryType_id == 1 ) {
			$EvnIdField = 'Evn_pid';
		}
		else {
			$EvnIdField = 'Evn_id';
		}

		$fieldList = array();
		$filterList = array();
		$joinList = array();
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "RE.Person_SurName ilike :Person_SurName";
			$params['Person_SurName'] = $data['Person_SurName'] . "%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "RE.Person_FirName ilike :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName'] . "%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "RE.Person_SecName ilike :Person_SecName";
			$params['Person_SecName'] = $data['Person_SecName'] . "%";
		}

		if ( !empty($data['RegistryError_Code']) ) {
			$filterList[] = "RE.RegistryErrorType_Code = :RegistryError_Code";
			$params['RegistryError_Code'] = $data['RegistryError_Code'];
		}

		if ( !empty($data['RegistryErrorType_id']) ) {
			$filterList[] = "RE.RegistryErrorType_id = :RegistryErrorType_id";
			$params['RegistryErrorType_id'] = $data['RegistryErrorType_id'];
		}

		if ( !empty($data['LpuBuilding_id']) ) {
			$filterList[] = "LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "RE.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( !empty($data['Evn_id']) ) {
			if ( !empty($data['Evn_id']) ) {
				if ( $this->RegistryType_id == 1 ) {
					$filterList[] = "(RE.{$EvnIdField} = :Evn_id OR RE.EvnSectionKSG_id = :Evn_id)";
					$params['Evn_id'] = $data['Evn_id'];
				} else {
					$filterList[] = "RE.{$EvnIdField} = :Evn_id";
					$params['Evn_id'] = $data['Evn_id'];
				}
			}
		}

		if ( !in_array($this->RegistryDataObject, array('RegistryDataCmp', 'RegistryDataPar')) ) {
			$joinList[] = "left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_pid";
		}
		else if ( in_array($this->RegistryDataObject, array('RegistryDataCmp')) ) {
			$joinList[] = "left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_rid";
		}
		else {
			$joinList[] = "left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id";
		}

		if ( $this->RegistryType_id == 15 ) {
			$joinList[] = "
				left join lateral (
					select Evn_id
					from {$this->scheme}.{$this->RegistryDataObject}
					where Registry_id = RE.Registry_id
						and MaxEvn_id = RD.Evn_id
						and Number is null
					limit 1
				) GroupInfo on true
			";
			$fieldList[] = "case when GroupInfo.Evn_id is not null then 2 else 1 end as \"IsGroupEvn\"";
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 12)) ) {
			$joinList[] = "left join v_EvnPLDisp epd on epd.EvnPLDisp_id = coalesce(RD.Evn_rid, RE.Evn_rid)";
			$fieldList[] = "epd.DispClass_id as \"DispClass_id\"";
		}

		// @task https://redmine.swan.perm.ru//issues/113170
		if ( $this->RegistryType_id == 6 ) {
			$joinList[] = "
				left join v_CmpCallCard ccc on ccc.CmpCallCard_id = rd.Evn_id
				left join v_CmpCloseCard cclc on cclc.CmpCallCard_id = ccc.CmpCallCard_id
			";
			$fieldList[] = "cclc.CmpCloseCard_id as \"CmpCloseCard_id\"";
			$fieldList[] = "ccc.CmpCallCardInputType_id as \"CmpCallCardInputType_id\"";
			$EvnIdField = 'Evn_rid';
		}

		if ( $this->RegistryType_id == 1 ) {
			$fieldList[] = "RE.EvnSectionKSG_id as \"EvnSectionKSG_id\"";
		}

		$query = "
			Select
				-- select
				 ROW_NUMBER() OVER (ORDER by RE.Registry_id, RE.RegistryErrorType_id, RE.Evn_id) as \"RegistryError_id\"
				,RE.Registry_id as \"Registry_id\"
				,RE.{$EvnIdField} as \"Evn_id\"
				,RE.{$EvnIdField} as \"Evn_ident\"
				,RE.Evn_rid as \"Evn_rid\"
				,RE.EvnClass_id as \"EvnClass_id\"
				,RE.RegistryErrorType_id as \"RegistryErrorType_id\"
				,RE.RegistryErrorType_Code as \"RegistryErrorType_Code\"
				,RD.needReform as \"needReform\"
				,RE.RegistryErrorType_Form as \"RegistryErrorType_Form\"
				,RE.MedStaffFact_id as \"MedStaffFact_id\"
				,case when RD.needReform = 2 and RQ.RegistryQueue_id is not null then 2 else 1 end \"isNoEdit\"
				,RE.LpuUnit_id as \"LpuUnit_id\"
				,RE.MedPersonal_id as \"MedPersonal_id\"
				,RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\"
				,RTrim(RE.RegistryErrorType_Name) as \"RegistryErrorType_Name\"
				,RE.RegistryError_Desc as \"RegistryError_Desc\"
				,RE.RegistryErrorType_Descr as \"RegistryErrorType_Descr\"
				,RE.Person_id as \"Person_id\"
				,RE.Server_id as \"Server_id\"
				,RE.PersonEvn_id as \"PersonEvn_id\"
				,RTrim(RE.Person_FIO) as \"Person_FIO\"
				,to_char(RE.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
				,CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\"
				,RE.LpuSection_id as \"LpuSection_id\"
				,RTrim(RE.LpuSection_name) as \"LpuSection_name\"
				,to_char(RE.Evn_setDate, 'DD.MM.YYYY') as \"Evn_setDate\"
				,to_char(RE.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\"
				,RE.RegistryErrorClass_id as \"RegistryErrorClass_id\"
				,RTrim(RE.RegistryErrorClass_Name) as \"RegistryErrorClass_Name\"
				,coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
				,case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
				" . (count($fieldList) > 0 ? "," . implode(', ', $fieldList) : "" ) . "
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryErrorObject} RE
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "" ) . "
				left join {$this->scheme}.RegistryQueue RQ on RQ.Registry_id = RD.Registry_id
				left join lateral (
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = RE.MedPersonal_id
					limit 1
				) MP on true
				-- end from
			where
				-- where
				RE.Registry_id = :Registry_id
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "" ) . "
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code,
				RE.Registry_id,
				RE.RegistryErrorType_id,
				RE.Evn_id
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
		//echo getDebugSQL($query, $params);exit;

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
						then '2030-01-01'
						else FLKSettings_endDate
					end
				AND RegistryGroupType_id is null
				AND FLKSettings_EvnData ilike '%perm%'
			LIMIT 1;
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
	 * Получение данных о пересечениях ТАП (RegistryDouble) для поликлин. реестров
	 */
	public function loadRegistryDouble($data) {
		$query = "
			select
				-- select
				 RD.Evn_id as \"Evn_id\"
				,RD.Person_id as \"Person_id\"
				,EPL.EvnPL_id as \"Evn_rid\"
				,RD.Registry_id as \"Registry_id\"
				,EVPL.Server_id as \"Server_id\"
				,EVPL.PersonEvn_id as \"PersonEvn_id\"
				,rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\"
				,to_char(RD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\"
				,EPL.EvnPL_NumCard as \"EvnPL_NumCard\"
				,(
					select
						string_agg(cast(t1.EvnPL_NumCard as varchar), ',')
					FROM
						v_EvnPL t1
					WHERE
						t1.EvnPL_id in (
							select EvnPL_id from v_EvnPLCrossed where EvnPL_cid = EPL.EvnPL_id
							union all
							select EvnPL_cid from v_EvnPLCrossed where EvnPL_id = EPL.EvnPL_id
						)
				) as \"EvnPLCrossed_NumCards\"
				,RData.RegistryData_deleted as \"RegistryData_deleted\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryDouble RD
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = RD.Evn_id
				left join v_EvnPL EPL on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				inner join {$this->scheme}.v_RegistryData RData on RData.Evn_id = RD.Evn_id
					and RData.Registry_id = RD.Registry_id
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
	}

	/**
	 *	Сохранение ошибки
	 */
	function saveRegistryErrorTFOMS($data)
	{
		if (empty($data['RegistryErrorTFOMSType_id'])) {
			$data['RegistryErrorTFOMSType_id'] = null;
		}

		if (empty($data['OrgSMO_id'])) {
			$data['OrgSMO_id'] = null;
		}

		if (empty($data['RegistryErrorType_id'])) {
			$data['RegistryErrorType_id'] = null;
		}

		if (empty($data['RegistryErrorTFOMSLevel_id'])) {
			$data['RegistryErrorTFOMSLevel_id'] = null;
		}

		if (empty($data['RegistryErrorTFOMS_Severity'])) {
			$data['RegistryErrorTFOMS_Severity'] = null;
		}

		if (empty($data['RegistryErrorTFOMS_IdCase'])) {
			$data['RegistryErrorTFOMS_IdCase'] = null;
		}

		if (empty($data['Registry_Task'])) {
			$data['Registry_Task'] = null;
		}

		if($data['session']['region']['nick']=='astra'){ //https://redmine.swan.perm.ru/issues/65516
			$data['RegistryErrorTFOMSLevel_id'] = '1';
		}

		$query = "
			select 
				RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryErrorTFOMS_ins(
				Registry_id := :Registry_id,
				Evn_id := :Evn_id,
				RegistryErrorType_id := :RegistryErrorType_id,
				RegistryErrorType_Code := :RegistryErrorType_Code,
				RegistryErrorTFOMS_FieldName := :RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement := :RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment := :RegistryErrorTFOMS_Comment,
				RegistryErrorTFOMSType_id := :RegistryErrorTFOMSType_id,
				RegistryErrorTFOMSLevel_id := :RegistryErrorTFOMSLevel_id,
				RegistryErrorTFOMS_Severity := :RegistryErrorTFOMS_Severity,
				RegistryErrorTFOMS_IdCase := :RegistryErrorTFOMS_IdCase,
				Registry_Task := :Registry_Task,
				Registry_Task := :Registry_Task,
				CmpCallCard_id := :CmpCallCard_id,
				OrgSMO_id := :OrgSMO_id,
				pmUser_id := :pmUser_id
			);
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			//Выполняем вычисление суммы без ошибок
			$response = $this->setNoErrorSum($data['Registry_id']);
			if (!is_array($response) || !empty($response[0]['Error_Msg'])) {
				return array(
					array('Error_Msg' => 'Произошла ошибка при вычислении суммы без ошибок.')
				);
			}

			return $result->result('array');
		}

		return false;
	}
}