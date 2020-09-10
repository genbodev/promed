<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry_model - модель для работы с таблицей Registry
 * модификация оригинального RegistryUfa_model.php для групповой постановке реестров на очередь формирования Task#18011
 *
 * @package      Admin
 * @access       public
 * @version      26/04/2013
 */

require("Registry_modelVE.php");

class RegistryUfa_modelVE extends Registry_modelVE
{
	public $scheme = "r2";
	public $region = "ufa";

	//var $scheme = "r2";
	var $isufa = true;

	/**
	 * comment
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * comment
	 */
	function loadRegistryNoPolis($data)
	{
		// Этот вообще тут не нужен пока по крайней мере
		exit;
	}

	/**
	 * comment
	 */
	function loadRegData($data)
	{
		$filter = "(1=1)";
		$params = ['Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id'], 'Registry_id' => $data['Registry_id']];
		$filter .= ' and R.Lpu_id = :Lpu_id';
		$filter .= ' and R.Registry_id = :Registry_id';

		$query = "
			select 
				R.Registry_id as \"Registry_id\",
				R.RegistryType_id as \"RegistryType_id\",
				R.Registry_Num as \"Registry_Num\",
				Lpu.Lpu_Email as \"Lpu_Email\",
				Lpu.Lpu_Nick as \"Lpu_Nick\"
			from v_Registry R
				left join v_Lpu Lpu on Lpu.Lpu_id = R.Lpu_id
			where 
				{$filter}
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * comment
	 */
	function loadEvnVizitErrorData($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as \"cnt\"
			from
				r2.Registry
			where Registry_id = :Registry_id
				and (OrgSmo_id is null
				or LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		if (!is_object($result))
			return false;
		$sel = $result->result('array');
		if ($sel[0]['cnt'] > 0)
			$load_errors_only = false;

		$errors_join = "";
		if ($load_errors_only === true)
			$errors_join = " inner join r2.v_RegistryError re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";

		$query = "
			select
				rd.Evn_id as \"ID_POS\",
				rd.Person_id as \"ID\",
				to_char(ev.EvnVizit_setDate, 'yyyymmdd') as \"DATE_POS\",
				'' as \"SMO\",
				'' as \"POL_NUM\",
				'' as \"ID_STATUS\",
				'' as \"NAM\",
				'' as \"FNAM\",
				'' as \"SEX\",
				'' as \"SNILS\",
				null as \"DATE_BORN\",
				null as \"DATE_SV\",
				null as \"FLAG\"
			from
				r2.v_RegistryData rd
				" . $errors_join . "
				inner join v_EvnVizit ev on ev.EvnVizit_id = rd.Evn_id
			where
				rd.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);
		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * comment
	 */
	function loadEvnSectionErrorData($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as \"cnt\"
			from
				r2.Registry
			where
				Registry_id = :Registry_id
				and (OrgSmo_id is null
				or LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		if (!is_object($result))
			return false;
		$sel = $result->result('array');
		if ($sel[0]['cnt'] > 0)
			$load_errors_only = false;
		$errors_join = "";
		if ($load_errors_only === true)
			$errors_join = " inner join r2.v_RegistryError re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";

		$query = "
			select
				rd.Evn_id as \"ID_POS\",
				rd.Person_id as \"ID\",
				to_char(es.EvnSection_setDT, 'yyyymmdd') as \"DATE_POS\",
				'' as \"SMO\",
				'' as \"POL_NUM\",
				'' as \"ID_STATUS\",
				'' as \"NAM\",
				'' as \"FNAM\",
				'' as \"SEX\",
				'' as \"SNILS\",
				null as \"DATE_BORN\",
				null as \"DATE_SV\",
				null as \"FLAG\"
			from
				r2.v_RegistryData rd
				" . $errors_join . "
				inner join v_EvnSection es on es.EvnSection_id = rd.Evn_id
			where
				rd.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * comment
	 */
	function loadPersonInfoFromErrorRegistry($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as \"cnt\"
			from
				r2.Registry
			where
				Registry_id = :Registry_id
				and (OrgSmo_id is null
				or LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		if (!is_object($result))
			return false;
		$sel = $result->result('array');
		if ($sel[0]['cnt'] > 0)
			$load_errors_only = false;

		$errors_join = "";
		if ($load_errors_only === true)
			$errors_join = " inner join r2.v_RegistryError re on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";

		$query = "
			select distinct
				rd.Person_id as \"ID\",
				dbo.UcWord(ps.Person_SurName) as \"FAM\",
				dbo.UcWord(ps.Person_FirName) as \"NAM\",
				dbo.UcWord(ps.Person_SecName) as \"FNAM\",
				to_char(ps.Person_BirthDay, 'yyyymmdd') as \"DATE_BORN\",
				coalesce(Sex.Sex_Code, 0) as \"SEX\",
				doct.DocumentType_Code as \"DOC_TYPE\",
				rtrim(coalesce(doc.Document_Ser, '')) as \"DOC_SER\",
				rtrim(coalesce(doc.Document_Num, '')) as \"DOC_NUM\",
				rtrim(coalesce(ps.Person_Inn, '')) as \"INN\",
				coalesce(addr.KLStreet_Code, addr.KLTown_Code, addr.KLCity_Code, addr.KLSubRGN_Code, addr.KLRGN_Code) as \"KLADR\",
				rtrim(coalesce(addr.Address_House, '')) as \"HOUSE\",
				rtrim(coalesce(addr.Address_Flat, '')) as \"ROOM\",
				smoorg.Org_Code as \"SMO\",
				rtrim(coalesce(PS.Polis_Num, '')) as \"POL_NUM\",
				case
					when ss.SocStatus_Code = 1 then 4
					when ss.SocStatus_Code = 2 then 7
					when ss.SocStatus_Code = 3 then 1
					when ss.SocStatus_Code = 4 then 3
					when ss.SocStatus_Code = 5 then 5
				else
					''
				end as \"STATUS\"
			from
				r2.v_RegistryData rd
				" . $errors_join . "
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
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		$query = "
			select
				rtrim(coalesce(LpuUnitSet_Code, '')) as \"LpuUnitSet_Code\"
			from
				r2.v_Registry
			where
				Registry_id = :Registry_id
		";
		$result_lpu = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);
		if (!is_object($result_lpu))
			return false;
		$sel_lpu = $result_lpu->result('array');
		if (count($sel_lpu) > 0)
			$lpu_code = $sel_lpu[0]['LpuUnitSet_Code'];

		if (!is_object($result))
			return false;
		$sel_data = $result->result('array');
		return ['data' => $sel_data, 'lpu_code' => $lpu_code];
	}

	/**
	 * comment
	 */
	function loadRegistryDataForXmlUsing($type, $data)
	{
		// шапка
		$query = "
			select *
			from r2.p_RegistryDD_Top(
				Registry_id := ?
			)
		";
		$result = $this->db->query($query, [$data['Registry_id']]);

		if (!is_object($result))
			return false;
		$header = $result->result('array');
		// посещения
		$query = "
			select *
			from r2.p_RegistryDD_expRE(
				Registry_id := ?
			)
		";
		$result = $this->db->query($query, [$data['Registry_id']]);

		if (!is_object($result))
			return false;
		$visits = $result->result('array');
		$SLUCH = [];
		// привязываем услуги к случаю
		foreach ($visits as $visit) {
			if (!isset($SLUCH[$visit['Evn_rid']]))
				$SLUCH[$visit['Evn_rid']] = [];
			$SLUCH[$visit['Evn_rid']][] = $visit;
		}
		unset($visits);

		// услуги
		$query = "
			select *
			from r2.p_RegistryDD_expU(
				Registry_id := ?
			)
		";
		$result = $this->db->query($query, [$data['Registry_id']]);

		if (!is_object($result))
			return false;
		$uslugi = $result->result('array');
		$USL = [];
		// привязываем услуги к случаю
		foreach ($uslugi as $usluga) {
			if (!isset($USL[$usluga['EvnUslugaDispDop_rid']]))
				$USL[$usluga['EvnUslugaDispDop_rid']] = [];
			$USL[$usluga['EvnUslugaDispDop_rid']][] = $usluga;
		}
		unset($uslugi);

		// люди
		$query = "
			select *
			from r2.p_RegistryDD_expPers(
				Registry_id := ?
			)
		";
		$result = $this->db->query($query, [$data['Registry_id']]);

		if (!is_object($result))
			return false;
		$person = $result->result('array');
		$PACIENT = [];
		// привязываем персона к случаю
		foreach ($person as $pers)
			$PACIENT[$pers['Evn_rid']] = $pers;
		unset($person);

		// собираем массив для выгрузки
		$data = [];
		$data['SCHET'] = [$header[0]];
		// массив с записями
		$data['ZAP'] = [];
		foreach ($PACIENT as $key => $value)
			$data['ZAP'][$key]['PACIENT'] = [$value];
		foreach ($SLUCH as $key => $value) {
			// для терапевта пишем услуги
			foreach ($value as $k => $val)
				if (($val['PRVS'] == '1122' || $val['PRVS'] == '1110') && isset($USL[$key]))
					$value[$k]['USL'] = $USL[$key];
				else
					$value[$k]['USL'] = [];
			$data['ZAP'][$key]['SLUCH'] = $value;
		}
		$data['PACIENT'] = $PACIENT;

		return $data;
	}

	/**
	 * comment
	 */
	function loadRegistryDataForXmlCheckVolumeUsing($type, $data)
	{
		$query = "
			select *
			from r2.p_RegistryPL_expRE(
				Registry_id := ?
			)
		";
		$result = $this->db->query($query, [$data['Registry_id']]);

		if (!is_object($result))
			return false;
		$header = $result->result('array');
		foreach ($header as $key => $value) {
			$header[$key]['SUM'] = number_format($header[$key]['SUM'], 2, '.', '');
		}
		// код ЛПУ
		$query = "
			select
				lpu_f003mcod as \"lpu_f003mcod\"
			from
				Lpu
			where
				Lpu_id = ?
			limit 1
		";
		$result = $this->db->query($query, [$data['Lpu_id']]);
		if (!is_object($result))
			return false;
		$result = $result->result('array');
		$lpu_code = $result[0]['lpu_f003mcod'];
		return ['registry_data' => $header, 'lpu_code' => $lpu_code];
	}

	/**
	 * comment
	 */
	function loadRegistryForDbfUsing($data)
	{
		$filter = "(1=1)";
		$params = ['Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id']];
		$filter .= ' and R.Lpu_id = :Lpu_id';

		if (isset($data['Registry_id'])) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		$query = "
			select 
				os.Org_Code as \"ID_SMO\",
				R.LpuUnitSet_Code as \"ID_SUBLPU\",
				1 as \"TYPE\",
				date_part('year', R.Registry_endDate) as \"YEAR\",
				date_part('month', R.Registry_endDate) as \"MONTH\",
				RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"DATE_BEG\",
				RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"DATE_END\"	
			from r2.v_Registry R
				left join RegistryType rt on rt.RegistryType_id = R.RegistryType_id
				inner join v_Lpu Lpu on Lpu.Lpu_id = R.Lpu_id
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id
				left join Org os on os.Org_id = OrgSmo.Org_id
			where 
				{$filter}
		";
		$result = $this->db->query($query, $params);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * comment
	 */
	function loadRegistry($data)
	{
		$filter = "(1=1)";
		$params = ['Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id']];
		$filter .= ' and R.Lpu_id = :Lpu_id';

		if (isset($data['Registry_id'])) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		if (isset($data['RegistryType_id'])) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}
		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id'] == 5)) {
			$query = "
			Select 
				R.RegistryQueue_id as \"Registry_id\",
				R.OrgSmo_id as \"OrgSmo_id\",
				R.LpuUnitSet_id as \"LpuUnitSet_id\",
				R.RegistryType_id as \"RegistryType_id\",
				5 as \"RegistryStatus_id\",
				2 as \"Registry_IsActive\",
				RTrim(R.Registry_Num) || ' / в очереди: ' || LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
				RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
				RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
				RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
				LpuUnitSet.LpuUnitSet_Code as \"LpuUnitSet_Code\",
				OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
				R.Lpu_id as \"Lpu_id\",
				0 as \"Registry_Count\",
				0 as \"Registry_ErrorCount\",
				0 as \"Registry_Sum\",
				1 as \"Registry_IsProgress\",
				1 as \"Registry_IsNeedReform\",
				'' as \"Registry_updDate\"
			from {$this->scheme}.v_RegistryQueue R
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id 
				left join LpuUnitSet on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id 
			where {$filter}";
		} else {
			if (isset($data['RegistryStatus_id'])) {
				$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
				$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
			}
			$query = "
			Select 
				R.Registry_id as \"Registry_id\",
				R.OrgSmo_id as \"OrgSmo_id\",
				R.LpuUnitSet_id as \"LpuUnitSet_id\",
				R.RegistryType_id as \"RegistryType_id\",
				R.RegistryStatus_id as \"RegistryStatus_id\",
				R.Registry_IsActive as \"Registry_IsActive\",
				coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
				RTrim(R.Registry_Num) as \"Registry_Num\",
				RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
				RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
				RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
				R.LpuUnitSet_Code as \"LpuUnitSet_Code\",
				R.OrgSmo_Name as \"OrgSmo_Name\",
				R.Lpu_id as \"Lpu_id\",
				coalesce(R.Registry_RecordCount, 0) as \"Registry_Count\",
				coalesce(R.Registry_ErrorCount, 0) as \"Registry_ErrorCount\",
				coalesce(R.Registry_Sum, 0.00) as \"Registry_Sum\",
				case when (RQ.RegistryQueueHistory_id is not null) and (RQ.RegistryQueueHistory_endDT is null) then 1 else 0 end as \"Registry_IsProgress\",
				RTrim(coalesce(to_char(R.Registry_updDT, 'dd.mm.yyyy'),'')) || ' ' || 
				RTrim(coalesce(to_char(R.Registry_updDT, 'hh24:mi:ss'),'')) as \"Registry_updDate\"
			from {$this->scheme}.v_Registry R
				left join lateral(
					select
						RegistryQueueHistory_id,
						RegistryQueueHistory_endDT,
						RegistryQueueHistory.Registry_id
					from {$this->scheme}.RegistryQueueHistory
					where RegistryQueueHistory.Registry_id = R.Registry_id
					order by RegistryQueueHistory_id desc
					limit 1
				) RQ on true
			where 
				{$filter}
				";
		}
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * Модель выборки Уфимских СМО
	 * модификация оригинального Demand_model_VE.php для групповой постановке реестров на очередь формирования Task#18011
	 * /?c=Demandve&m=getOrgSmoList
	 */
	function getOrgSmoUfaList()
	{
		//Для рабочей БД - необходимо корректировать запрос

		$query = "
             select  
				 O.OrgSMO_id as \"Smo_id\", 
				 REPLACE(O.OrgSMO_Nick, '(РЕСПУБЛИКА БАШКОРТОСТАН)', '') as \"Smo_Name\", 
				 REPLACE(O.OrgSMO_Nick, '(РЕСПУБЛИКА БАШКОРТОСТАН)', '') as \"Smo_Nick\"
             from v_orgSmo O
             where KLRgn_id=2
             	and OrgSmo_endDate is null
		";

		$res = $this->db->query($query, []);

		if (!is_object($res))
			return false;
		return $res->result('array');
	}

	/**
	 * saveRegistryQueue_array Task#90544
	 * Установка реестров по СМО
	 * Возвращает номера в очереди или ошиббки
	 */
	function saveUnionRegistry_array($data)
	{
		//echo "<pre>".print_r($data,1)."</pre>"; exit;
		foreach ($data['Registry_id'] as $k => $reg_id) {
			// Сохранение нового реестра
			if (0 == $reg_id) {
				$data['Registry_IsActive'][$k] = 2;
				$operation = 'insert';
			} else {
				$operation = 'update';
			}
		}

		if (!is_array($data['Registry_id']))
			return false;

		$xml = '<RD>';
		$dataTemp = [];
		foreach ($data['Registry_id'] as $k => $reg_id) {
			$xml .= '<R|*|v1="' . $data['RegistryType_id'][$k] . '"
                          |*|v2="' . $data['Registry_accDate'][$k] . '"
                          |*|v3="' . $data['Lpu_id'] . '"
                          |*|v4="' . $data['RegistryStatus_id'][$k] . '"
                          |*|v5="' . $data['Registry_IsActive'][$k] . '"
                          |*|v6="' . $data['Registry_begDate'][$k] . '"
                          |*|v7="' . $data['Registry_endDate'][$k] . '"
                          |*|v8="' . $data['OrgSmo_id'][$k] . '"
                          |*|v9="' . $data['LpuUnitSet_id'][$k] . '"
                          |*|v10="' . $data['Registry_Num'][$k] . '"
                          |*|v11="' . $data['pmUser_id'] . '"
                          |*|v12="' . $data['Registry_IsNotInsur'][$k] . '"
                          |*|v13="' . $data['Registry_Comment'][$k] . '"
                          |*|v14="' . $data['RegistrySubType'][$k] . '"
                          |*|v15="' . $data['Registry_IsNew'][$k] . '"
                          |*|v16="' . $data['Registry_IsZNO'][$k] . '"></R>';

			$dataTemp[$k]['RegistryType_id'] = $data['RegistryType_id'][$k];
			$dataTemp[$k]['Registry_accDate'] = $data['Registry_accDate'][$k];
			$dataTemp[$k]['RegistryStatus_id'] = $data['RegistryStatus_id'][$k];
			$dataTemp[$k]['Registry_IsActive'] = $data['Registry_IsActive'][$k];
			$dataTemp[$k]['Registry_begDate'] = $data['Registry_begDate'][$k];
			$dataTemp[$k]['Registry_endDate'] = $data['Registry_endDate'][$k];
			$dataTemp[$k]['OrgSmo_id'] = $data['OrgSmo_id'][$k];
			$dataTemp[$k]['Registry_Num'] = $data['Registry_Num'][$k];
			$dataTemp[$k]['Registry_IsNotInsur'] = $data['Registry_IsNotInsur'][$k];
			$dataTemp[$k]['Registry_Comment'] = $data['Registry_Comment'][$k];
			$dataTemp[$k]['RegistrySubType'] = $data['RegistrySubType'][$k];
			$dataTemp[$k]['Registry_IsNew'] = $data['Registry_IsNew'][$k];
			$dataTemp[$k]['Registry_IsZNO'] = $data['Registry_IsZNO'][$k];
		}
		$xml .= '</RD>';
		$xml = strtr($xml, [PHP_EOL => '', " " => ""]);
		$xml = str_replace("|*|", " ", $xml);
		$params = ['xml' => $xml];

		// 1. сохраняем реестр по СМО
		$query = "
			select
				response as \"responseText\"
			from r2.p_RegistryGroup_ins(
				xml := :xml
			)   
		";

		$result = $this->db->query($query, $params);
		//echo htmlspecialchars($xml); exit;

		if (is_object($result)) {
			$res = $result->result('array');

			if (is_array($res) && count($res) > 0 && !empty($res[0]['ResponseText'])) {
				$res_arr = strtr($res[0]['ResponseText'], [']' => ', {"success":"true"}]']);

				$json = json_decode($res[0]['ResponseText']);
				foreach ($dataTemp as $k => $v) {
					foreach ($json as $ke => $va) {
						$va = (array)$va;
						if ($k == $ke) {
							$dataTemp[$k]['Registry_id'] = $va['Registry_id'];
							$dataTemp[$k]['Smo_Name'] = $va['Smo_Name'];
							$dataTemp[$k]['UnitSet_id'] = $va['UnitSet_id'];
							$dataTemp[$k]['LpuUnitSet_id'] = $va['LpuUnitSet_id'];
							$dataTemp[$k]['Registry_Num'] = $va['Registry_Num'];
						}
					}
				}

				foreach ($dataTemp as $k => $v) {
					// 2. удаляем все связи
					$query = "
						delete {$this->scheme}.RegistryGroupLink
						where Registry_pid = :Registry_id
					";
					$this->db->query($query, [
						'Registry_id' => $v['Registry_id']
					]);

					// Югория-Мед объединилась с Альфастрахованием, по ней реестры не формируем
					if ($v['Registry_accDate'] >= '2017-05-25' && $v['OrgSmo_id'] == 8000233) {
						continue;
					} else if ($v['Registry_accDate'] >= '2018-10-25' && $v['OrgSmo_id'] == 8000227) {
						continue;
					}
					// 3. выполняем поиск реестров которые войдут в финальный
					if ($v['Registry_accDate'] >= '2017-05-25' && $v['OrgSmo_id'] == 8001229) {
						$filter = "and rf.OrgSmo_id in (8000233, 8001229) and coalesce(rf.Registry_IsNotInsur, 1) = 1";
					} else if ($v['Registry_accDate'] >= '2018-10-25' && $v['OrgSmo_id'] == 8001750) {
						$filter = "and rf.OrgSmo_id in (8000227, 8001750) and coalesce(rf.Registry_IsNotInsur, 1) = 1";
					} else {
						$filter = "and rf.OrgSmo_id = :OrgSmo_id and coalesce(rf.Registry_IsNotInsur, 1) = 1";
					}

					if ($v['Registry_IsNotInsur'] == 2) {
						// по незастрахованным только один может быть финальный реестр для предварительного, СМО не учитываем
						$filter = "and rf.Registry_IsNotInsur = 2";
					}
					$query = "
						with mv as (
							select
								paytype_id as pt
							from v_PayType pt
							where pt.PayType_SysNick = 'oms'
							limit 1
						)

						select
							R.Registry_id as \"Registry_id\",
							R.Registry_Num as \"Registry_Num\",
							to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
							RT.RegistryType_Name as \"RegistryType_Name\",
							RETF.FLKCount as \"FLKCount\"
						from
							{$this->scheme}.v_Registry R
							left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
							left join lateral(
								select
									count(RegistryErrorTFOMS_id) as FLKCount
								from dbo.v_RegistryErrorTFOMS
								where RegistryErrorTFOMSLevel_id = 1
									and Registry_id = R.Registry_id
							) RETF on true
						where
							R.RegistrySubType_id = 1
							" . (!empty($v['OrgSmo_id']) && $v['OrgSmo_id'] == 8 ? "and OrgSmo_id = 8" : "and OrgSmo_id is null") . "
							and R.RegistryStatus_id = 2 -- К оплате
							and R.Lpu_id = :Lpu_id
							and R.RegistryType_id = :RegistryType_id
							and R.Registry_begDate >= :Registry_begDate
							and R.Registry_endDate <= :Registry_endDate
							and R.LpuUnitSet_id = :LpuUnitSet_id
							and coalesce(R.Registry_IsNew, 1) = :Registry_IsNew
							and coalesce(R.DispClass_id, 0) = :DispClass_id
							and coalesce(r.PayType_id, (select pt from mv)) = (select pt from mv)
							and not exists( -- и не входит в другой реестр по той же смо
								select
									rgl.RegistryGroupLink_id
								from
									{$this->scheme}.v_RegistryGroupLink rgl
									inner join {$this->scheme}.v_Registry rf on rf.Registry_id = rgl.Registry_pid -- финальный реестр
								where
									rgl.Registry_id = R.Registry_id
									and coalesce(rf.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)
									{$filter}
								limit 1
							)
					";
					$result_reg = $this->db->query($query, [
						'Lpu_id' => $data['Lpu_id'],
						'OrgSmo_id' => $v['OrgSmo_id'],
						'LpuUnitSet_id' => $v['LpuUnitSet_id'],
						'RegistryType_id' => $v['RegistryType_id'],
						'Registry_begDate' => $v['Registry_begDate'],
						'Registry_endDate' => $v['Registry_endDate'],
						'Registry_IsNew' => !empty($v['Registry_IsNew']) ? $v['Registry_IsNew'] : 1,
						'Registry_IsZNO' => !empty($v['Registry_IsZNO']) ? $v['Registry_IsZNO'] : 1,
						'DispClass_id' => !empty($v['DispClass_id']) ? $v['DispClass_id'] : 0
					]);

					if (is_object($result_reg)) {
						$resp_reg = $result_reg->result('array');

						// 4. сохраняем новые связи
						foreach ($resp_reg as $one_reg) {
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

							$this->db->query($query, [
								'Registry_pid' => $v['Registry_id'],
								'Registry_id' => $one_reg['Registry_id'],
								'pmUser_id' => $data['pmUser_id']
							]);
						}
					}

					$this->recountSumKolUnionRegistry([
						'Registry_id' => $v['Registry_id']
					]);
				}

				return $res_arr;
			} else {
				$res_arr = false;
			}
		} else {
			$res_arr = false;
		}

		if ($res_arr !== false) {
			return $res_arr;
		} else {
			return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных!']];
		}
	}

	/**
	 * saveRegistryQueue_array Task#18011
	 * Установка реестров в очередь на формирование
	 * Возвращает номера в очереди или ошиббки
	 */
	function saveRegistryQueue_array($data)
	{
		foreach ($data['Registry_id'] as $k => $reg_id) {
			// Сохранение нового реестра
			if (0 == $reg_id) {
				$data['Registry_IsActive'][$k] = 2;
				$operation = 'insert';
			} else {
				$operation = 'update';
			}
		}

		if (!is_array($data['Registry_id']))
			return false;

		$xml = '<RD>';
		foreach ($data['Registry_id'] as $k => $reg_id) {
			$xml .= '<R|*|v1="' . $data['RegistryType_id'][$k] . '"
                          |*|v2="' . $data['Registry_accDate'][$k] . '"
                          |*|v3="' . $data['Lpu_id'] . '"
                          |*|v4="' . $data['RegistryStatus_id'][$k] . '"
                          |*|v5="' . $data['Registry_IsActive'][$k] . '"
                          |*|v6="' . $data['Registry_begDate'][$k] . '"
                          |*|v7="' . $data['Registry_endDate'][$k] . '"
                          |*|v8="' . $data['OrgSmo_id'][$k] . '" 
                          |*|v9="' . $data['LpuUnitSet_id'][$k] . '"
                          |*|v10="' . $data['Registry_Num'][$k] . '"
                          |*|v11="' . $data['pmUser_id'] . '"
                          |*|v12="' . $data['RegistrySubType'][$k] . '"
                          |*|v13="' . $data['Registry_IsNew'][$k] . '" ></R>';
		}
		$xml .= '</RD>';

		$xml = strtr($xml, [PHP_EOL => '', " " => ""]);
		$xml = str_replace("|*|", " ", $xml);
		$params = ['xml' => (string)$xml];

		$query = "
			select
         		response as \"responseText\"
        	from r2.p_RegistryQueueGroup_ins(
        		xml := :xml
        	) 
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result))
			$res_arr = false;

		$res = $result->result('array');
		if (is_array($res) && count($res) > 0 && !empty($res[0]['ResponseText'])) {
			$res_arr = strtr($res[0]['ResponseText'], [']' => ', {"success":"true"}]']);
		} else {
			$res_arr = false;
		}

		if ($res_arr !== false) {
			return $res_arr;
		} else {
			return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных!']];
		}

	}

	/**
	 * Пересчёт суммы и количества в реестре по СМО
	 */
	function recountSumKolUnionRegistry($data)
	{
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.Registry_IsZNO as \"Registry_IsZNO\",
				r.OrgSmo_id as \"OrgSmo_id\",
				to_char(r.Registry_accDate, 'yyyy-mm-dd') as \"Registry_accDate\"
			from
				{$this->scheme}.v_Registry r
			where
				r.Registry_id = :Registry_id
		";

		$resp = $this->queryResult($query, [
			'Registry_id' => $data['Registry_id']
		]);

		if (!empty($resp[0]['Registry_id'])) {
			$data['Registry_id'] = $resp[0]['Registry_id'];
			if ($resp[0]['RegistrySubType_id'] != '2') {
				return ['Error_Msg' => 'Указанный реестр не является реестром по СМО'];
			}

			$this->setRegistryParamsByType($data);

			$filter = "";
			if ($resp[0]['Registry_IsNotInsur'] == 2) {
				// если реестр по СМО, то не зависимо от соц. статуса
				if ($this->RegistryType_id == 6) {
					$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				} else {
					$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079, 'yyyymmdd')) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				}
			} else if ($resp[0]['OrgSmo_id'] == 8) {
				// инотеры
				$filter_rd = " and RD.Polis_id IS NOT NULL";
				$filter .= " and coalesce(os.OrgSMO_RegNomC,'')=''";
			} else {
				// @task https://redmine.swan.perm.ru//issues/109876
				if ($resp[0]['Registry_accDate'] >= '2017-05-25' && $resp[0]['OrgSmo_id'] == 8001229) {
					$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
				} else if ($resp[0]['Registry_accDate'] >= '2018-10-25' && $resp[0]['OrgSmo_id'] == 8001750) {
					$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
				} else {
					$filter_rd = " and RD.OrgSmo_id = RF.OrgSmo_id";
				}
			}

			if ($resp[0]['Registry_IsZNO'] == 2) {
				$filter .= " and RD.RegistryData_IsZNO in (2, 3) ";
			} else {
				$filter .= " and coalesce(RD.RegistryData_IsZNO, 1) = 1 ";
			}

			// считаем сумму и количество
			$resp_sum = $this->queryResult("
				select
					SUM(case when coalesce(RD.RegistryData_IsBadVol, 1) = 1 then RD.RegistryData_Sum_R else 0 end) as \"Sum\",
					SUM(case when coalesce(RD.RegistryData_IsBadVol, 1) = 1 then 1 else 0 end) as \"Count\",
					SUM(case when coalesce(RD.RegistryData_IsBadVol, 1) = 2 then 1 else 0 end) as \"CountBadVol\"
				from
					{$this->scheme}.v_RegistryGroupLink RGL
						inner join {$this->scheme}.v_Registry RF on RF.Registry_id = RGL.Registry_pid
						inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
						inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id {$filter_rd}
						left join v_OrgSmo os on os.OrgSmo_id = rd.OrgSmo_id
				where
					RGL.Registry_pid = :Registry_id
					{$filter}
			", [
				'Registry_id' => $data['Registry_id']
			]);

			$this->db->query("
				update {$this->scheme}.Registry
				set
					Registry_SumR = :Registry_SumR,
					Registry_Sum = :Registry_Sum,
					Registry_RecordCount = :Registry_RecordCount,
					Registry_CountIsBadVol = :Registry_CountIsBadVol
				where Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id'],
				'Registry_SumR' => !empty($resp_sum[0]['Sum']) ? $resp_sum[0]['Sum'] : 0,
				'Registry_Sum' => !empty($resp_sum[0]['Sum']) ? $resp_sum[0]['Sum'] : 0,
				'Registry_RecordCount' => !empty($resp_sum[0]['Count']) ? $resp_sum[0]['Count'] : 0,
				'Registry_CountIsBadVol' => !empty($resp_sum[0]['CountBadVol']) ? $resp_sum[0]['CountBadVol'] : 0
			]);
		}
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = [], $force = false)
	{
		parent::setRegistryParamsByType($data, $force);

		switch ($this->RegistryType_id) {
			case 1:
			case 14:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				break;
			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				break;
			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				break;
			case 17:
				$this->RegistryDataObject = 'RegistryDataProf';
				break;
			default:
				$this->RegistryDataObject = 'RegistryData';
				break;
		}
	}

	/**
	 * comment
	 */
	function reformRegistry($data)
	{
		$query = "
			select
				Registry_id as \"Registry_id\",
				Lpu_id as \"Lpu_id\",
				RegistryType_id as \"RegistryType_id\",
				RegistryStatus_id as \"RegistryStatus_id\",
				to_char(cast(Registry_begDate as timestamp), 'yyyymmdd') as \"Registry_begDate\",
				to_char(cast(Registry_endDate as timestamp), 'yyyymmdd') as \"Registry_endDate\",
				OrgSmo_id as \"OrgSmo_id\",
				LpuUnitSet_id as \"LpuUnitSet_id\",
				Registry_Num as \"Registry_Num\",
				Registry_IsActive as \"Registry_IsActive\",
				to_char(cast(Registry_accDate as timestamp), 'yyyymmdd') as \"Registry_accDate\"
			from
				r2.Registry
			where
				Registry_id = ?
		";

		$result = $this->db->query($query, [$data['Registry_id']]);
		if (!is_object($result))
			return ['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'];
		$row = $result->result('array');
		if (empty($row))
			return ['success' => false, 'Error_Msg' => 'Реестр не найден в базе'];
		$data['RegistryType_id'] = $row[0]['RegistryType_id'];
		$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
		$data['Registry_begDate'] = $row[0]['Registry_begDate'];
		$data['Registry_endDate'] = $row[0]['Registry_endDate'];
		$data['Registry_Num'] = $row[0]['Registry_Num'];
		$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
		$data['OrgSmo_id'] = $row[0]['OrgSmo_id'];
		$data['LpuUnitSet_id'] = $row[0]['LpuUnitSet_id'];
		$data['Registry_accDate'] = $row[0]['Registry_accDate'];
		return $this->saveRegistryQueue($data);
	}

	/**
	 * comment
	 */
	function deleteRegistryError($data)
	{
		$filter = "";
		$params = [];
		$join = "";
		if (empty($data['Registry_id']))
			return false;
		$params['Registry_id'] = $data['Registry_id'];

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r2.p_RegistryError_del(
				Registry_id := :Registry_id,
				Evn_id := NULL,
				RegistryErrorType_id := NULL
			)
		";
		// echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);
		return true;
	}

	/**
	 * comment
	 */
	function checkErrorDataInRegistry($data)
	{
		$query = "
			Select rd.Registry_id as \"Registry_id\"
			from r2.v_RegistryData rd
			where rd.Registry_id = :Registry_id
				and (rd.Evn_id = :IDCASE OR :IDCASE IS NULL)
				and (rd.Person_id = :ID_PERS OR :ID_PERS IS NULL)
		";

		$params['Registry_id'] = $data['Registry_id'];
		$params['IDCASE'] = (isset($data['IDCASE']) && is_numeric($data['IDCASE']) && $data['IDCASE'] > 0 ? $data['IDCASE'] : NULL);
		$params['ID_PERS'] = (isset($data['ID_PERS']) && is_numeric($data['ID_PERS']) && $data['ID_PERS'] > 0 ? $data['ID_PERS'] : NULL);

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$row = $result->result('array');
			if (count($row) > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * comment
	 */
	function setVizitNotInReg($data)
	{
		// Проставляем ошибочные посещения обратно
		$params = [];
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		if (empty($data['Registry_id']))
			return [['success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!']];

		$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from r2.p_Registry_setPaid(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
		$result = $this->db->query($query, $params);
		if (!is_object($result))
			return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];
		return $result->result('array');
	}

	/**
	 * comment
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
			if (empty($data['Registry_id']))
				return [['success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!']];

			$query = "
					SELECT
						RegistryErrorType_id as \"RegistryErrorType_id\"
					FROM r2.RegistryErrorType
					WHERE RegistryErrorType_Code = :FLAG
					limit 1
				";
			$resp = $this->db->query($query, $params);

			if (!is_object($resp))
				return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки ' . $d['FLAG']]];

			$ret = $resp->result('array');

			if (!is_array($ret) || empty($ret))
				return [['success' => false, 'Error_Msg' => 'Код ошибки ' . $d['FLAG'] . ' не найден в бд']];

			$params['FLAG'] = $ret[0]['RegistryErrorType_id'];
			$query = "
							with mv as (
								Select 
									rd.Registry_id,
									rd.Evn_id,
									rd.LpuSection_id
								from
									r2.v_RegistryData rd
								where
									rd.Registry_id = :Registry_id
									and rd.Evn_id = :IDCASE
							)
							
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from r2.p_RegistryError_ins(
								Registry_id := (select Regisry_id from mv),
								Evn_id := (select Evn_id from mv),
								RegistryErrorType_id := :FLAG,
								LpuSection_id := (select LpuSection_id from mv), 
								pmUser_id := :pmUser_id
							)
						";

			$result = $this->db->query($query, $params);

			// если выполнилось, возвращаем пустой Error_Msg
			if (!is_object($result))
				return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];

			$resp = $result->result('array');

			if (!empty($resp[0]['Error_Msg']))
				return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса добавления ошибки']];

			return [['success' => true, 'Error_Msg' => '']];
		} else {
			$params['ID'] = $d['ID'];
			if (empty($data['Registry_id']))
				return [['success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!']];

			$query = "
					with mv as (
						Select 
							rd.Registry_id,
							rd.Evn_id,
							rd.LpuSection_id
						from
							r2.v_RegistryData rd
						where
							rd.Registry_id = :Registry_id
							and rd.Guid = :ID
					)
					
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from r2.p_RegistryError_ins(
						Registry_id := (select Regisry_id from mv),
						Evn_id := (select Evn_id from mv),
						RegistryErrorType_id := :FLAG,
						LpuSection_id := (select LpuSection_id from mv), 
						pmUser_id := :pmUser_id
					)
				";

			$result = $this->db->query($query, $params);
			if (!is_object($result))
				return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];
			return $result->result('array');
		}
	}

	/**
	 * comment
	 */
	function getRegistryFields($data)
	{
		$filter = "(1 = 1)";
		$queryParams = [];

		$filter .= " and Registry.Registry_id = :Registry_id";
		$queryParams['Registry_id'] = $data['Registry_id'];

		if (!isMinZdrav()) {
			$filter .= " and Registry.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select 
				RTRIM(Registry.Registry_Num) as \"Registry_Num\",
				coalesce(to_char(Registry.Registry_accDate, 'dd.mm.yyyy'), '') as \"Registry_accDate\",
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
				date_part('month', Registry.Registry_begDate) as \"Registry_Month\",
				date_part('year', Registry.Registry_begDate) as \"Registry_Year\",
				cast(coalesce(Registry.Registry_Sum, 0.00) as double precision ) as \"Registry_Sum\",
				OHDirector.OrgHeadPerson_Fio as \"Lpu_Director\",
				OHGlavBuh.OrgHeadPerson_Fio as \"Lpu_GlavBuh\",
				RT.RegistryType_id as \"RegistryType_id\",
				RT.RegistryType_Code as \"RegistryType_Code\"
			from r2.v_Registry Registry
				inner join Lpu on Lpu.Lpu_id = Registry.Lpu_id
				inner join Org on Org.Org_id = Lpu.Org_id
				inner join RegistryType RT on RT.RegistryType_id = Registry.RegistryType_id
				left join Okved on Okved.Okved_id = Org.Okved_id
				left join Address LpuAddr on LpuAddr.Address_id = Org.UAddress_id
				left join OrgRSchet ORS on Registry.OrgRSchet_id = ORS.OrgRSchet_id
				left join v_OrgBank OB on OB.OrgBank_id = ORS.OrgBank_id
				left join lateral(
					select 
						substring(RTRIM(PS.Person_FirName), 1, 1) || '.' || substring(RTRIM(PS.Person_SecName), 1, 1) || '. ' || RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH
						inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where 
						OH.Lpu_id = Lpu.Lpu_id
						and OH.OrgHeadPost_id = 1
					limit 1
				) as OHDirector on true
				left join lateral(
					select 
						substring(RTRIM(PS.Person_FirName), 1, 1) || '.' || substring(RTRIM(PS.Person_SecName), 1, 1) || '. ' || RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH
						inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where 
						OH.Lpu_id = Lpu.Lpu_id
						and OH.OrgHeadPost_id = 2
					limit 1
				) as OHGlavBuh on true
			where " . $filter . "
		";
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result))
			return false;
		$response = $result->result('array');
		return $response[0];
	}

	/**
	 * comment
	 */
	function savePersonData($data)
	{
		$query = "
			select
				RegErrorPerson_id as \"RegErrorPerson_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r2.p_RegErrorPerson_ins(
				Registry_id := :Registry_id,
				Person_id := :Person_id,
				OrgSmo_Code := :OrgSmo_Code,
				Polis_Num := :Polis_Num,
				SocStatus_Code := :SocStatus_Code,
				Person_FirName := :Person_FirName,
				Person_SecName := :Person_SecName,
				Person_BirthDay := :Person_BirthDay,
				Sex_id := :Sex_id,
				Person_Snils := :Person_Snils,
				Person_IsFlag := :Person_IsFlag,
				Evn_setDT := :Evn_setDT,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = [
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
			'Person_IsFlag' => (in_array($data['FLAG'], [0, 1]) ? $data['FLAG'] : NULL),
			'Evn_setDT' => (!empty($data['DATE_POS']) ? $data['DATE_POS'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		];
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * comment
	 */
	function updatePersonErrorData($data)
	{
		$query = "
			select
				CountUpd as \"CountUpd\"
			from r2.xp_RegistryErrorPerson_process(
				Registry_id := :Registry_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = ['Registry_id' => $data['Registry_id'], 'pmUser_id' => $data['pmUser_id']];
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}

	/**
	 * comment
	 */
	function loadRegistryStatusNode($data)
	{
		$result = [
			['RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'],
			['RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'],
			['RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'],
			['RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'],
		];
		return $result;
	}

	/**
	 *  Получение наименования СМО реестра
	 */
	function getSmoName($data)
	{
		$queryParams = ['Registry_id' => $data['Registry_id']];
		$query = "
			select
			case when R.OrgSmo_Name is null
				then 'Без СМО'
				else R.OrgSmo_Name
			end as \"Smo_Name\"
			from r2.v_Registry R
			where R.Registry_id = :Registry_id
			limit 1
		";
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result))
			return false;
		return $result->result('array');
	}
}