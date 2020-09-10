<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Org_model - модель, для работы с таблицей Org и производными (OrgDep, OrgSmo)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.05.2009
*/

class Org_model extends swModel {

	public $inputRules = array(
		'createOrgRSchet' => array(
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgRSchetType_id', 'label' => 'Идентификатор типа расчетного счета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgRSchet_RSchet', 'label' => 'Номер расчетного счета', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'OrgBank_id', 'label' => 'Идентификатор банка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Okv_id', 'label' => 'Идентификатор валюты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgRSchet_Name', 'label' => 'Наименование расчетного счета', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'OrgRSchet_begDate', 'label' => 'Дата открытия расчетного счета', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'OrgRSchet_endDate', 'label' => 'Дата закрытия расчетного счета', 'rules' => '', 'type' => 'date')
		),
		'updateOrgRSchet' => array(
			array('field' => 'OrgRSchet_id', 'label' => 'Идентификатор расчетного счета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgRSchetType_id', 'label' => 'Идентификатор типа расчетного счета', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgRSchet_RSchet', 'label' => 'Номер расчетного счета', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgBank_id', 'label' => 'Идентификатор банка', 'rules' => '', 'type' => 'id'),
			array('field' => 'Okv_id', 'label' => 'Идентификатор валюты', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgRSchet_Name', 'label' => 'Наименование расчетного счета', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgRSchet_begDate', 'label' => 'Дата открытия расчетного счета', 'rules' => '', 'type' => 'date'),
			array('field' => 'OrgRSchet_endDate', 'label' => 'Дата закрытия расчетного счета', 'rules' => '', 'type' => 'date')
		),
		'deleteOrgRSchet' => array(
			array('field' => 'OrgRSchet_id', 'label' => 'Идентификатор расчетного счета', 'rules' => 'required', 'type' => 'id')
		),
		'getOrgRSchetList' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required', 
				'type' => 'id'
			)
		),
		'createOrgRSchetKBK' => array(
			array('field' => 'OrgRSchet_id', 'label' => 'Идентификатор расчетного счета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgRSchet_KBK', 'label' => 'КБК организации', 'rules' => 'required', 'type' => 'string')
		),
		'updateOrgRSchetKBK' => array(
			array('field' => 'OrgRSchetKBK_id', 'label' => 'Идентификатор привязки КБК к расчетному счету', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgRSchet_KBK', 'label' => 'КБК организации', 'rules' => '', 'type' => 'string')
		),
		'deleteOrgRSchetKBK' => array(
			array('field' => 'OrgRSchetKBK_id', 'label' => 'Идентификатор привязки КБК к расчетному счету', 'rules' => 'required', 'type' => 'id'),
		),
		'createOrgHead' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgHeadPost_id', 'label' => 'Идентификатор руководящей должности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgHead_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgHead_Fax', 'label' => 'Факс', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgHead_Email', 'label' => 'Электронная почта', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgHead_CommissDate', 'label' => 'Дата приказа о назначении', 'rules' => '', 'type' => 'date')
		),
		'updateOrgHead' => array(
			array('field' => 'OrgHead_id', 'label' => 'Идентификатор руководящей единицы организации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OrgHeadPost_id', 'label' => 'Идентификатор руководящей должности', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgHead_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgHead_Fax', 'label' => 'Факс', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgHead_Email', 'label' => 'Электронная почта', 'rules' => '', 'type' => 'string'),
			array('field' => 'OrgHead_CommissDate', 'label' => 'Дата приказа о назначении', 'rules' => '', 'type' => 'date')
		),
		'deleteOrgHead' => array(
			array('field' => 'OrgHead_id', 'label' => 'Идентификатор руководящей единицы организации', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка ЛПУ связанных с ТОУЗ пользователя
	 */
	function getTouzOrgs($data)
	{
		$query = "
			select distinct
				l.Lpu_id
			from
				v_Lpu l (nolock)
				inner join v_Org o (nolock) on o.Org_id = l.Org_tid
				inner join v_OrgType ot (nolock) on ot.OrgType_id = o.OrgType_id
			where
				o.Org_id in (".implode($data['orgs'], ',').")
				and ot.OrgType_SysNick = 'touz'
		";
		
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			$lpus = array();
			foreach($resp as $respone) {
				$lpus[] = $respone['Lpu_id'];
			}
			return $lpus;
		}
		
		return array();
	}
	
	/**
	 * Проверка есть ли среди списка оргназиация с типом ТОУЗ
	 */
	function checkIsTouzOrg($data)
	{
		$query = "
			select top 1
				o.Org_id
			from
				v_Org o (nolock)
				inner join v_OrgType ot (nolock) on ot.OrgType_id = o.OrgType_id
			where
				o.Org_id in (".implode($data['orgs'], ',').")
				and ot.OrgType_SysNick = 'touz'
		";
		
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Org_id'])) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	* Получение типа организации
	*/
	function getOrgType($data)
	{
		// Загружаем БД, если она еще не была загружена
		if (!isset($this->db)) {
			$this->load->database();
		}
		$query = "
			SELECT TOP 1
				case
					when Org.Org_id IN ('68320020775') then 'touz' -- костыль для минздрава
					when os.OrgSMO_id IS NOT NULL then 'smo' -- это и всё, что ниже можно впринципе убрать если будут всем проставлены правильные OrgType_id
					when l.Lpu_id IS NOT NULL then 'lpu'
					when ofa.OrgFarmacy_id IS NOT NULL then 'farm'
					when ob.OrgBank_id IS NOT NULL then 'bank'
					when od.OrgDep_id IS NOT NULL then 'dep'
					when oa.OrgAnatom_id IS NOT NULL then 'anatom'
					else ot.OrgType_SysNick
				end as OrgType_SysNick,
				case
					when Org.Org_id IN ('68320020775') then 15 -- костыль для минздрава
					when os.OrgSMO_id IS NOT NULL then 3 -- это и всё, что ниже можно впринципе убрать если будут всем проставлены правильные OrgType_id
					when l.Lpu_id IS NOT NULL then 11
					when ofa.OrgFarmacy_id IS NOT NULL then 4
					when ob.OrgBank_id IS NOT NULL then 2
					when od.OrgDep_id IS NOT NULL then 1
					when oa.OrgAnatom_id IS NOT NULL then 16
					else Org.OrgType_id
				end as OrgType_id
			FROM Org with (nolock)
				left join v_OrgType ot with (nolock) on ot.OrgType_id = Org.OrgType_id
				left join OrgSMO os with (nolock) on os.Org_id = Org.Org_id
				left join OrgBank ob with (nolock) on ob.Org_id = Org.Org_id
				left join OrgFarmacy ofa with (nolock) on ofa.Org_id = Org.Org_id
				left join OrgDep od with (nolock) on od.Org_id = Org.Org_id
				left join OrgAnatom oa with (nolock) on oa.Org_id = Org.Org_id
				left join Lpu l with (nolock) on l.Org_id = Org.Org_id
			WHERE Org.Org_id = :Org_id
		";
		
		$result = '';
		$res = $this->db->query($query, array('Org_id' => $data['Org_id']));
		if ( is_object($res) ) {
			$rows = $res->result('array');
			if (count($rows)>0) {
				$result = $rows[0];
			}
		}
		return $result;
	}
	
	/**
	* Проверка списка организаций на сопадение по OGRN
	*/
	function checkOGRNEntry($org_list, $OGRN)
	{
		if (empty($org_list) || empty($OGRN)) {
			return false;
		}
		
		$query = "
			select top 1 Org_id
			from v_Org with (nolock)
			where
				Org_id in (:orgIdList)
				and Org_OGRN = :OGRN
		";
		
		$params = array(
			'orgIdList' => implode(",", $org_list),
			'OGRN' => $OGRN
		);
		
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) ) {
			$response = $result->result('array');
			
			return count($response) > 0;
		}
		else {
			return false;
		}
	}
	
	/**
	* Включает или выключает доступ организации в систему
	*/
	function giveOrgAccess($data) 
	{
		$query = "
			UPDATE 
				Org
			SET
				Org_isAccess = :grant
			WHERE
				Org_id = :Org_id
		";
		$this->db->query($query, $data);
		
		return array(array('Error_Msg' => ''));
	}
	
	/**
	* Возвращает список  руководства
	*/
	function loadOrgHeadGrid($data) {
		$filter = "";
		$queryParams = array();
		
		$lpu_unit_filter = " and oh.LpuUnit_id is null ";
		if ( isset($data['LpuUnit_id']) && $data['LpuUnit_id'] > 0 )
		{
			$lpu_unit_filter = " and oh.LpuUnit_id = :LpuUnit_id ";
		}
		else
			$data['LpuUnit_id'] = null;
			
		if(isset($data['fromMZ']) && $data['fromMZ'] == '2')
		{
			$addLpuFilter = "";
		}
		else
		{
			if( !empty($data['OrgHead_id']) ){
				$addLpuFilter = " and (oh.Lpu_id = :Lpu_id OR oh.OrgHead_id = :OrgHead_id)";
			}else{
				$addLpuFilter = " and oh.Lpu_id = :Lpu_id";
			}
		}
		$query = "
			SELECT
				oh.OrgHead_id,
				oh.Person_id,
				rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHeadPerson_Fio,
				oh.OrgHead_Phone,
				oh.OrgHead_Fax,
				ohp.OrgHeadPost_id,
				rtrim(ohp.OrgHeadPost_Name) as OrgHeadPost_Name,
				convert(varchar,oh.OrgHead_CommissDate,112) as OrgHead_CommissDate -- 112=ггггммдд
			FROM
				v_OrgHead oh with(nolock)
				inner join v_OrgHeadPost ohp with(nolock) on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
				inner join v_PersonState ps with(nolock) on ps.Person_id = oh.Person_id
			WHERE (1=1)
				{$addLpuFilter}
				{$lpu_unit_filter}
		";
		$res = $this->db->query($query, $data);
       	//echo json_return_errors($data['Lpu_id'].' '.$data['LpuUnit_id']);
       	//return false;
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* Возвращает список  расчетных счетов ЛПУ
	*/
	function loadOrgRSchetGrid($data) {
		$filter = "";
		$queryParams = array();

		$org = "(select top 1 Org_id from Lpu with(nolock) where Lpu_id = :Lpu_id)";
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$query = "
			SELECT
				ors.OrgRSchet_id,
				ors.OrgRSchet_Name,
				ors.OrgRSchet_RSchet,
				ob.OrgBank_Name
			FROM
				v_OrgRSchet ors with(nolock)
				inner join v_OrgBank ob with(nolock) on ob.OrgBank_id = ors.OrgBank_id
			WHERE
				ors.Org_id = {$org}
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*   Возвращает список  расчетных счетов ЛПУ
	*/
	function loadOrgRSchetListForAPI($data) {
		$where = " (1=1) ";
		if(!empty($data['Org_id'])){
			$where .= " and ors.Org_id = :Org_id";
		}
		if(!empty($data['Lpu_id'])){
			$where .= " and l.Lpu_id = :Lpu_id";
		}
		if(!empty($data['OrgRSchet_id'])){
			$where .= " and ors.OrgRSchet_id = :OrgRSchet_id";
		}
		$query = "
			SELECT
				ors.OrgRSchet_id,
				ors.OrgRSchet_Name,
				ors.OrgRSchet_RSchet,
				ors.OrgRSchetType_id,
				ors.OrgBank_id,
				ors.Okv_id,
				convert(varchar,ors.OrgRSchet_begDate,104) as OrgRSchet_begDate,
				convert(varchar,ors.OrgRSchet_endDate,104) as OrgRSchet_endDate
			FROM
				v_OrgRSchet ors with(nolock)
				left join v_Lpu l with (nolock) on l.Org_id = ors.Org_id
			WHERE
				{$where}
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
	 * Возвращает список расчетных счетов организации
	 */
	function loadOrgRSchetList($data) {
		$filter = "(1 = 0)";
		$queryParams = array();

		if ( !empty($data['Org_id']) ) {
			$filter = "Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}
		else if ( !empty($data['Lpu_id']) ) {
			$filter = "Org_id = (select top 1 Org_id from v_Lpu with (nolock) where Lpu_id = :Lpu_id)";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		else {
			return false;
		}

		$query = "
			SELECT
				 OrgRSchet_id
				,OrgRSchet_Name
			FROM
				v_OrgRSchet with (nolock)
			WHERE
				{$filter}
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* Возвращает список КБК на расчетный счет ЛПУ
	*/
	function loadOrgRSchetKBKGrid($data) {
		$filter = "";
		$queryParams = array(
			'OrgRSchet_id' => $data['OrgRSchet_id']
		);
		if (!empty($data['fromAPI']) && !empty($data['Lpu_id'])) {
			$filter .= " and L.Lpu_id = ".$data['Lpu_id'];
		};
		$query = "
			SELECT
				orskbk.OrgRSchetKBK_id,
				orskbk.OrgRSchet_KBK
			FROM
				v_OrgRSchetKBK orskbk with(nolock)
				left join v_OrgRSchet ors with(nolock) on ors.OrgRSchet_id = orskbk.OrgRSchet_id
				left join v_Lpu L with (nolock) on L.Org_id = ors.Org_id
			WHERE
				orskbk.OrgRSchet_id = ?
		".$filter;
		$res = $this->db->query($query, array($data['OrgRSchet_id']));

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Функция отображения данных справочника организаций 
	* Позволяет выбрать все данные часть данных согласно фмльтрам, либо данные по id элемента справочника
	*/
	function getOrgView($data)
	{
		$params = array();
		$filter = "(1=1)";
		//$params['Lpu_id'] = $_SESSION['lpu_id'];

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		
		if (isset($data['Nick']))
		{
			$filter = $filter." and Org.Org_Nick like ('%'+:OrgNick+'%')";
			$params['OrgNick'] = $data['Nick'];
		}
		if (isset($data['Name']))
		{
			$filter = $filter." and Org.Org_Name like ('%'+:OrgName+'%')";
			$params['OrgName'] = $data['Name'];
		}
		
		if ((isset($data['Org_id'])) && ($data['Org_id']>0))
		{
			$filter = $filter." and Org.Org_id = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		}
		
		if (!empty($data['Type'])) {
			$filter .= " and Org.OrgType_id = :OrgType_id";
			$params['OrgType_id'] = $data['Type'];
		}

		if (!empty($data['OnlyOrgStac']) && $data['OnlyOrgStac'] == 2) {
			$filter .= " and exists (select top 1 OrgStac_id from fed.v_OrgStac with (nolock) where Org_id = Org.Org_id)";
		}

		if(!empty($data['LpuArr']) && isset($data['mode']) && $data['mode'] == 'lpu')
		{
			$filter .= " and Lp.Lpu_id in ({$data['LpuArr']})";
			//$params['LpuArr'] = $data['LpuArr'];
		}
		
		$query = "
			Select 
				-- select
				Org.Org_id,
				RTrim(Org.Org_Nick) as Org_Nick,
				RTrim(Org.Org_Name) as Org_Name,
				ot.OrgType_SysNick as Org_Type,
				Org.OrgType_id as OrgType_id,
				RTrim(UAddress.Address_Address) as UAddress_Address,
				RTrim(PAddress.Address_Address) as PAddress_Address,
				case when ISNULL(Org.Org_IsAccess,1) = 2 then 'true' else 'false' end as Org_IsAccess,
				case when Org.Org_id < 2000000 and Org.Server_id = 0 then 'true' else 'false' end as Org_External,
				L.Lpu_id
				-- end select
			from
				-- from
				v_Org Org with (nolock)
				left join v_Lpu L with(nolock) on L.Org_id = Org.Org_id
				left join v_OrgType ot with (nolock) on Org.OrgType_id = ot.OrgType_id
				left join v_Address UAddress with (nolock) on UAddress.Address_id = Org.UAddress_id
				left join v_Address PAddress with (nolock) on PAddress.Address_id = Org.PAddress_id
				-- end from
			where 
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				Org.Org_Name
				-- end order by
		";
		
		// ищем только ЛПУ
		if ( isset($data['mode']) && $data['mode'] == 'lpu' )
		{
			$query = "
				Select
					-- select
					Org.Org_id,
					Lp.Lpu_id,
					RTrim(Org.Org_Nick) as Org_Nick,
					RTrim(Org.Org_Name) as Org_Name,
					RTrim(Org.Org_OGRN) as Org_OGRN,
					RTrim(Lp.Lpu_Ouz) as Lpu_Ouz,
					'lpu' as Org_Type,
					RTrim(IsNull(convert(varchar,cast(Lp.Lpu_begDate as datetime),104),'')) as Lpu_begDate,
					RTrim(IsNull(convert(varchar,cast(Lp.Lpu_endDate as datetime),104),'')) as Lpu_endDate,
					RTrim(UAddress.Address_Address) as UAddress_Address,
					RTrim(KLAreaStat.KLArea_Name) as KLArea_Name,
					RTrim(PAddress.Address_Address) as PAddress_Address,
					RTrim(LpOMS.LpuPeriodOMS_begDate) as LpuPeriodOMS_begDate, 
					case when LpOMS.LpuPeriodOMS_begDate is not null then 'true' else 'false' end as OMS,
					case when LpDLO.LpuPeriodDLO_begDate is not null then 'true' else 'false' end as DLO,
					case when ISNULL(Org.Org_IsAccess,1) = 2 then 'true' else 'false' end as Org_IsAccess
					-- end select
				from 
					-- from
					v_Org Org with (nolock)
					outer apply(
						select top 1
							kla.KLArea_Name
						from
							v_OrgServiceTerr (nolock) ost
							left join v_KLAreaStat kla (nolock) on isnull(kla.KLRgn_id, 0) = isnull(ost.KLRgn_id, 0)
								and isnull(kla.KLSubRgn_id, 0) = isnull(ost.KLSubRgn_id, 0)
								and isnull(kla.KLCity_id, 0) = isnull(ost.KLCity_id, 0)
								and isnull(kla.KLTown_id, 0) = isnull(ost.KLTown_id, 0)
						where
							Org_id = Org.Org_id
					) KLAreaStat
					inner join Lpu Lp with (nolock) on Lp.Org_id = Org.Org_id
					left join v_Address_all UAddress with (nolock) on UAddress.Address_id = Org.UAddress_id
					left join v_Address PAddress with (nolock) on PAddress.Address_id = Org.PAddress_id
					--left join v_LpuPeriodOMS LpOMS with (nolock) on LpOMS.Lpu_id = Lp.Lpu_id and LpOMS.LpuPeriodOMS_endDate is null
					--left join v_LpuPeriodDLO LpDLO with (nolock) on LpDLO.Lpu_id = Lp.Lpu_id and LpDLO.LpuPeriodDLO_endDate is null
					outer apply(
						select top 1 LpuPeriodOMS_begDate
						from v_LpuPeriodOMS with(nolock)
						where Lpu_id = Lp.Lpu_id and LpuPeriodOMS_endDate is null
						order by LpuPeriodOMS_updDT desc
					) LpOMS
					outer apply(
						select top 1 LpuPeriodDLO_begDate
						from v_LpuPeriodDLO with(nolock)
						where Lpu_id = Lp.Lpu_id and LpuPeriodDLO_endDate is null
						order by LpuPeriodDLO_updDT desc
					) LpDLO
					-- end from
				where 
					-- where
					{$filter}
					-- end where
				order by
					-- order by
					Org_Nick
					-- end order by
			";
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	 * Возвращает список всех ЛПУ (в том числе удалённых)
	 */
	function getLpuAllList()
    {
		$sql = "
			SELECT
				Lpu_id,
				Org_id
			FROM
				Lpu
			ORDER BY
				Lpu_id
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }
	 
	/**
	 * Возвращает список всех Аптек (в том числе удалённых)
	 */
	function getOrgFarmacyAllList()
    {
		$sql = "
			SELECT
				OrgFarmacy_id,
				Org_id
			FROM
				OrgFarmacy with (nolock)
			ORDER BY
				OrgFarmacy_id
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }


	/**
	 * Получаем список ЛПУ
	 */

	public function getAllLpuList()
	{
		$query = "
			SELECT
				l.Lpu_id
				,l.Lpu_Nick as Lpu_Name
			FROM
				dbo.v_Lpu_all l WITH (NOLOCK)
    	";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список ЛПУ)'));
		}
	}
	 
	/**
	 * Возвращает список ЛПУ
	 */
	function getLpuList($data) {
		$filter = "";
		$filterorg = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and L.Org_id = :Org_id";
			$filterorg .= " and O.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];

			$query = "
				SELECT
					O.Org_id,
					L.Lpu_id,
					O.Org_Code,
					O.Org_Nick,
					O.Org_Name,
					O.Org_f003mcod as Lpu_f003mcod
				FROM
					Org O with (nolock)
					left join v_Lpu_all L (nolock) on L.Org_id = O.Org_id
				WHERE (O.OrgType_id=11 or L.Lpu_id is not null)		-- Не у всех ЛПУ в таблице Org проставлен OrgType_id
					" . $filterorg ."
			";
		}
		elseif ( isset($data['Lpu_oid']) ) {
			$filter .= " and L.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_oid'];

			$query = "
				SELECT
					L.Org_id as Org_id,
					L.Lpu_id as Lpu_id,
					null as Org_Code,
					rtrim(L.Lpu_Nick) as Org_Nick,
					rtrim(L.Lpu_Name) as Org_Name,
					L.Lpu_f003mcod as Lpu_f003mcod
				FROM
					v_Lpu_all L with (nolock)
				WHERE (1 = 1)
					" . $filter . "
			";
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and L.Lpu_Name LIKE :Lpu_Name";
				$filterorg .= " and O.Org_Name LIKE :Lpu_Name";
				$queryParams['Lpu_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and L.Lpu_Nick LIKE :Lpu_Nick";
				$filterorg .= " and O.Org_Nick LIKE :Lpu_Nick";
				$queryParams['Lpu_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
			
			if ( isset($data['DispClass_id']) ) {
				if (!empty($data['Disp_consDate'])) {
					$queryParams['Disp_consDate'] = $data['Disp_consDate'];
				} else {
					$queryParams['Disp_consDate'] = NULL;
				}
				$filter .= " and exists(select top 1 LMTL.LpuMobileTeamLink_id from v_LpuMobileTeamLink LMTL (nolock) inner join v_LpuMobileTeam LMT (nolock) on LMT.LpuMobileTeam_id = LMTL.LpuMobileTeam_id where LMT.Lpu_id = L.Lpu_id and LMTL.DispClass_id = :DispClass_id and LMT.LpuMobileTeam_begDate <= :Disp_consDate and (LMT.LpuMobileTeam_endDate >= :Disp_consDate or LMT.LpuMobileTeam_endDate is NULL) )";
				$queryParams['DispClass_id'] = $data['DispClass_id'];
			}
			
			if ( !empty($data['OrgType_id']) ) {
				$filter .= " and o.OrgType_id = {$data['OrgType_id']}";
			}

			$query = "";
			$AdditionalFeatureOrgFilter = '';
			$AdditionalFeatureFilter = '';
			$AdditionalFeatureJoin = '';
			if(isset($data['AdditionalFeature_id']) && $this->getRegionNick() == 'vologda') {
				switch($data['AdditionalFeature_id']){
					case 1://Краевые, областные
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join fed.MOAreaFeature MOAF with(nolock) on MOAF.MOAreaFeature_id = L.MOAreaFeature_id and MOAF.MOAreaFeature_id = \'2\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 2://Городские
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join fed.MOAreaFeature MOAF with(nolock) on MOAF.MOAreaFeature_id = L.MOAreaFeature_id and MOAF.MOAreaFeature_id = \'6\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 3://Ведомственные
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join LpuSubjectionLevel LSL with(nolock) on LSL.LpuSubjectionLevel_id = L.LpuSubjectionLevel_id and LSL.LpuSubjectionLevel_pid = \'1\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 4://ЦРБ
						$AdditionalFeatureJoin = '
							inner join LpuLevel LL with(nolock) on LL.LpuLevel_id = L.LpuLevel_id and LL.LpuLevel_SysNick = \'RegionHosp\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 5://Участковые
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$query .= "declare @curDate date = dbo.tzGetDate();";
						$AdditionalFeatureJoin = '
							inner join v_LpuPeriodFondHolder LPFH with(nolock) on LPFH.Lpu_id = L.Lpu_id  and LPFH.LpuPeriodFondHolder_begDate <= @curDate and ( LPFH.LpuPeriodFondHolder_endDate is null or LPFH.LpuPeriodFondHolder_endDate >= @curDate )
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 6://ФАП
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join LpuBuilding LB with (nolock) on LB.Lpu_id = L.Lpu_id
							inner join LpuUnit LU with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
							inner join LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id and LUT.LpuUnitType_SysNick = \'fap\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 7://Амбулатория
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join LpuBuilding LB with (nolock) on LB.Lpu_id = L.Lpu_id
							inner join LpuUnit LU with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
							inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id and LUT.LpuUnitType_SysNick = \'polka\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 8://Круглосуточный стационар
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join LpuBuilding LB with (nolock) on LB.Lpu_id = L.Lpu_id
							inner join LpuUnit LU with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
							inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id and LUT.LpuUnitType_SysNick in (\'stac\',\'dstac\',\'pstac\')
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 9://СМП
						$AdditionalFeatureOrgFilter = 'and 1 <> 1';
						$AdditionalFeatureJoin = '
							inner join LpuBuilding LB with (nolock) on LB.Lpu_id = L.Lpu_id and LB.LpuBuildingType_id = \'27\'
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						break;
					case 10://МО других территорий
						$AdditionalFeatureOrgFilter = 'and O.Region_id  <> \''.getRegionNumber().'\'';
						$AdditionalFeatureJoin = '
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id <> \''.getRegionNumber().'\'';
						break;
					case 11://Частная мед клиника
						$AdditionalFeatureOrgFilter = 'and O.Region_id  = \''.getRegionNumber().'\' and O.Okfs_id = \'6\'';
						$AdditionalFeatureJoin = '
							inner join lpu la with (nolock) on l.lpu_id = la.lpu_id and la.Region_id = \''.getRegionNumber().'\'';
						$AdditionalFeatureFilter = 'and l.LpuOwnership_id <> \'1\'';
						break;
					default://Без фильтров
						$AdditionalFeatureOrgFilter = '';
						$AdditionalFeatureFilter = '';
						$AdditionalFeatureJoin = '';
						break;
				}
			}

			if (empty($data['onlyFromDictionary']) || $data['onlyFromDictionary'] == false) {
				$query .= "
				SELECT
					O.Org_id,
					O.OrgType_id,
					null as Lpu_id,
					O.Org_Code,
					O.Org_Nick,
					O.Org_Name,
					O.Org_f003mcod as Lpu_f003mcod,
					convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
					convert(varchar(10), o.Org_endDate, 104) as Org_endDate
				FROM
					v_Org O with(nolock)
				WHERE 
					O.OrgType_id = 11
					and not exists (
						select top 1 
							Lpu_id 
						from v_Lpu_all with(nolock) 
						where 
							Org_id = O.Org_id
					)
					{$filterorg}
					{$AdditionalFeatureOrgFilter}
					union all
				";
			}

			$query .= "
				SELECT
					L.Org_id as Org_id,
					o.OrgType_id as OrgType_id,
					L.Lpu_id as Lpu_id,
					null as Org_Code,
					rtrim(L.Lpu_Nick) as Org_Nick,
					rtrim(L.Lpu_Name) as Org_Name,
					isnull(d.Lpu_f003mcod, o.Org_f003mcod) as Lpu_f003mcod,
					convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
					convert(varchar(10), o.Org_endDate, 104) as Org_endDate
				FROM v_Lpu_all L with(nolock)
					left join v_Org o with(nolock) on o.Org_id = l.Org_id
					outer apply(
						select top 1 
							Lpu_f003mcod 
						from v_Lpu lp with(nolock) 
						where 
							lp.Lpu_id = L.Lpu_id
					) d
					{$AdditionalFeatureJoin}
				WHERE (1 = 1)
					{$filter}
					{$AdditionalFeatureFilter}
			";
		}


		//echo getDebugSQL($query, $queryParams);exit();
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Возвращает список организация, проводящих патологоанатомические экспертизы
	 */
	function getOrgAnatomList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and O.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and isnull(OA.OrgAnatom_Name, L.Lpu_Name) LIKE :Org_Name";
				$queryParams['Org_Name'] = "%" . $data['Org_Name'] . "%";
			}
			if ( isset($data['Org_Nick']) ) {
				$filter .= " and isnull(OA.OrgAnatom_Nick, L.Lpu_Nick) LIKE :Org_Nick";
				$queryParams['Org_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
			if (!empty($data['activeInDate'])) {
				$filter .= "
				and (
					(L.Lpu_begDate is not null and :activeInDate between L.Lpu_begDate and COALESCE(L.Lpu_endDate, :activeInDate))
					or
					(L.Lpu_begDate is null and :activeInDate between o.Org_begDate and COALESCE(o.Org_endDate, :activeInDate))
				)";
				$queryParams['activeInDate'] = $data['activeInDate'];
			}
		}

		$query = "
			SELECT
				O.Org_id,
				OA.OrgAnatom_id,
				L.Lpu_id,
				null as Org_Code,
				rtrim(OA.OrgAnatom_Nick) as Org_Nick,
				rtrim(OA.OrgAnatom_Name) as Org_Name,
				OA.Server_id,
				convert(varchar(10), O.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), O.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgAnatom OA with (nolock)
				left join v_Lpu L (nolock) on L.Org_id = OA.Org_id
				left join v_Org O (nolock) on O.Org_id = OA.Org_id
			WHERE (1 = 1)
				" . $filter . "
			UNION
			SELECT
				O.Org_id,
				null as OrgAnatom_id,
				L.Lpu_id,
				null as Org_Code,
				rtrim(L.Lpu_Nick) as Org_Nick,
				rtrim(L.Lpu_Name) as Org_Name,
				L.Server_id,
				convert(varchar(10), O.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), O.Org_endDate, 104) as Org_endDate
			FROM
				v_Lpu L (nolock)
				left join v_Org O (nolock) on O.Org_id = L.Org_id
				left join v_OrgAnatom OA with(nolock) on OA.Org_id = L.Org_id
			WHERE (1 = 1) and OA.OrgAnatom_id is null
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Возвращает список организация, проводящих патологоанатомические экспертизы
	 * Изначальный вариант! @https://redmine.swan.perm.ru/issues/115815
	 */
	function getOrgAnatomOldList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and OrgAnatom_id = :OrgAnatom_id";
			$queryParams['OrgAnatom_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and OrgAnatom_Name LIKE :OrgAnatom_Name";
				$queryParams['OrgAnatom_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and OrgAnatom_Nick LIKE :OrgAnatom_Nick";
				$queryParams['OrgAnatom_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				v_OrgAnatom.OrgAnatom_id as Org_id,
				L.Lpu_id,
				v_OrgAnatom.Org_id as Org_pid,
				null as Org_Code,
				rtrim(v_OrgAnatom.OrgAnatom_Nick) as Org_Nick,
				rtrim(v_OrgAnatom.OrgAnatom_Name) as Org_Name,
				v_OrgAnatom.Server_id,
				convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), o.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgAnatom with (nolock)
				left join v_Lpu_all L (nolock) on L.Org_id = v_OrgAnatom.OrgAnatom_id
				left join v_Org o (nolock) on o.Org_id = v_OrgAnatom.Org_id
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает список военкоматов
	 */
	function getOrgMilitaryList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and OrgMilitary_id = :OrgMilitary_id";
			$queryParams['OrgMilitary_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and OrgMilitary_Name LIKE :OrgMilitary_Name";
				$queryParams['OrgMilitary_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and OrgMilitary_Nick LIKE :OrgMilitary_Nick";
				$queryParams['OrgMilitary_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				OrgMilitary_id as Org_id,
				Org_id as Org_pid,
				null as Org_Code,
				rtrim(OrgMilitary_Nick) as Org_Nick,
				rtrim(OrgMilitary_Name) as Org_Name,
				Server_id
			FROM
				v_OrgMilitary with(nolock)
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Возвращает список стационарных учреждений
	 */
	function getOrgStacList($data) {
		$filter = "";
		$queryParams = array();

		if ( $data['OrgType'] == 'orgstaceducation' ) {
			$filter .= 'and o.OrgType_id in (7, 8, 9, 10)';
		}

		if ( !empty($data['Org_id']) ) {
			$filter .= " and o.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}
		else {
			if ( !empty($data['Org_Name']) ) {
				$filter .= " and o.Org_Name LIKE :Org_Name";
				$queryParams['Org_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( !empty($data['Org_Nick']) ) {
				$filter .= " and o.Org_Nick LIKE :Org_Nick";
				$queryParams['Org_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				o.Org_id as Org_id,
				os.OrgStac_id as Org_pid,
				os.OrgStac_Code as Org_Code,
				rtrim(o.Org_Nick) as Org_Nick,
				rtrim(o.Org_Name) as Org_Name,
				o.Server_id,
				convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), o.Org_endDate, 104) as Org_endDate
			FROM
				fed.v_OrgStac os with (nolock)
				inner join dbo.v_Org o with (nolock) on o.Org_id = os.Org_id
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Возвращает список банков
	*/
	function getOrgBankList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and OrgBank_id = :OrgBank_id";
			$queryParams['OrgBank_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and OrgBank_Name LIKE :OrgBank_Name";
				$queryParams['OrgBank_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and OrgBank_Nick LIKE :OrgBank_Nick";
				$queryParams['OrgBank_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				ob.OrgBank_id as Org_id,
				ob.Org_id as Org_pid,
				rtrim(ob.OrgBank_Code) as Org_Code,
				rtrim(ob.OrgBank_Nick) as Org_Nick,
				rtrim(ob.OrgBank_Name) as Org_Name,
				ob.Server_id,
				convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), o.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgBank ob with(nolock)
				left join v_Org o (nolock) on o.Org_id = ob.Org_id
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Возвращает список организаций выдавших документ
	*/
	function getOrgDepList($data) {
		$filter = "";
		$queryParams = array();

		if ( !empty($data['Org_id']) ) {
			$filter .= " and od.OrgDep_id = :OrgDep_id";
			$queryParams['OrgDep_id'] = $data['Org_id'];
		}
		else if ( !empty($data['Org_pid']) ) {
			$filter .= " and od.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_pid'];
		}
		else {
			if ( !empty($data['Org_Name']) ) {
				$filter .= " and od.OrgDep_Name LIKE :OrgDep_Name";
				$queryParams['OrgDep_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( !empty($data['Org_Nick']) ) {
				$filter .= " and od.OrgDep_Nick LIKE :OrgDep_Nick";
				$queryParams['OrgDep_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				od.OrgDep_id as Org_id,
				od.Org_id as Org_pid,
				rtrim(od.Org_Code) as Org_Code,
				rtrim(od.OrgDep_Nick) as Org_Nick,
				rtrim(od.OrgDep_Name) as Org_Name,
				og.Server_id,
				convert(varchar(10), og.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), og.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgDep od with (nolock)
				inner join Org og with (nolock) on og.Org_id = od.Org_id
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Возвращает список аптек
	*/
	function getOrgFarmacyList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and OrgFarmacy_id = :OrgFarmacy_id";
			$queryParams['OrgFarmacy_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and OrgFarmacy_Name LIKE :OrgFarmacy_Name";
				$queryParams['OrgFarmacy_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and OrgFarmacy_Nick LIKE :OrgFarmacy_Nick";
				$queryParams['OrgFarmacy_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				ofr.OrgFarmacy_id as Org_id,
				og.Org_id as Org_pid,
				rtrim(ofr.OrgFarmacy_Code) as Org_Code,
				rtrim(ofr.OrgFarmacy_Nick) as Org_Nick,
				rtrim(ofr.OrgFarmacy_Name) as Org_Name,
				og.Server_id,
				convert(varchar(10), og.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), og.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgFarmacy ofr with (nolock)
				inner join Org og with (nolock) on og.Org_id = ofr.Org_id
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Возвращает список организаций, выдающих лицензии
	*/
	function getOrgLicList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and Org_Name LIKE :Org_Name";
				$queryParams['Org_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and Org_Nick LIKE :Org_Nick";
				$queryParams['Org_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				og.Org_id,
				og.Org_id as Org_pid,
				rtrim(og.Org_Code) as Org_Code,
				rtrim(og.Org_Nick) as Org_Nick,
				rtrim(og.Org_Name) as Org_Name,
				og.Server_id,
				convert(varchar(10), og.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), og.Org_endDate, 104) as Org_endDate
			FROM
				Org og with(nolock)
			WHERE 
				og.OrgType_id = 6
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	

	/**
	* Возвращает список организаций для комбобокса
	*/
	function getOrgColoredList($data) {
		$filter = "";
		$select = "";
		$join = "";
		$queryParams = array();

		if( isset($data['query'])){
			$filter .= ' and (o.Org_Name LIKE :query or o.Org_Nick LIKE :query)';
			$queryParams['query'] = '%'.$data['query'].'%';
		};

		if( isset($data['Org_id'])){
			$filter .= ' and o.Org_id = :Org_id';
			$queryParams['Org_id'] = $data['Org_id'];
		};
		
		if( isset($data['OrgType'])){
			$filter .= ' and o.OrgType_id = :OrgType';
			$queryParams['OrgType'] = $data['OrgType'];
		};
		if ( !empty($data['needOrgType'] )) {
			$select .= " , ot.OrgType_Name , ot.OrgType_SysNick";
		}

		if (!empty($data['needOrgType'] ) || !empty($data['OrgType_Code'])) {
			$join .= " left join v_OrgType ot with (nolock) on ot.OrgType_id = o.OrgType_id";
		}

		$query = "
			SELECT
				o.Org_id,
				RTRIM(o.Org_Code) as Org_Code,
				RTRIM(o.Org_Nick) as Org_Nick,
				RTRIM(o.Org_Name) as Org_Name,
                L.Lpu_id
				{$select}
			FROM
				v_Org o with(nolock)
				left join v_Lpu_all l (nolock) on L.Org_id = O.Org_id
				{$join}
			WHERE (1 = 1)
				" . $filter . "
		";
		//echo getdebugsql($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает список организаций для моб приложения
	 */
	function getOrgListForApi($data) {

		$filter = ""; $queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and o.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}

		if (!empty($data['query'])) {
			$filter .= " and o.Org_Nick LIKE :query";
			$queryParams['query'] = "%" . $data['query'] . "%";
		}

		if ( isset($data['Org_Name']) ) {
			$filter .= " and o.Org_Name LIKE :Org_Name";
			$queryParams['Org_Name'] = "%" . $data['Org_Name'] . "%";
		}

		if ( isset($data['Org_Nick']) ) {
			$filter .= " and o.Org_Nick LIKE :Org_Nick";
			$queryParams['Org_Nick'] = "%" . $data['Org_Nick'] . "%";
		}

		$query = "
			SELECT TOP 1000
			-- select 
				o.Org_id,
				o.OrgType_id,
				RTRIM(o.Org_Nick) as Org_Nick,
				RTRIM(o.Org_StickNick) as Org_StickNick,
				RTRIM(o.Org_Name) as Org_Name
			-- end select
			FROM
			-- from
				v_Org o with (nolock)
				left join v_Address a (nolock) on a.Address_id = ISNULL(o.UAddress_id, o.PAddress_id)
				left join fed.OrgStac OS with (nolock) on OS.Org_id = o.Org_id
				left join v_Lpu_all L (nolock) on L.Org_id = O.Org_id
			-- end from
			WHERE
			-- where
				(1 = 1)
				" . $filter . "
				and o.Org_pid is null
			-- end where
			ORDER BY 
			-- order by
				o.Org_id
			-- end order by
		";

		//echo getdebugsql($query, $queryParams);die;
		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}


	/**
	* Возвращает список организаций
	*/
	function getOrgList($data) {
		$filter = "";
		
		$join = "";
		$select = "";
		$queryParams = array();
		
		if ( !empty($data['needOrgOGRN'] )) {
			$select .= " , o.Org_OGRN";
		}

		if ( !empty($data['needOrgType'] )) {
			$select .= " , ot.OrgType_Name , ot.OrgType_SysNick";
		}

		if (!empty($data['needOrgType'] ) || !empty($data['OrgType_Code'])) {
			$join .= " left join v_OrgType ot with (nolock) on ot.OrgType_id = o.OrgType_id";
		}

		if (!empty($data['WithOrgStacCode']) && $data['WithOrgStacCode'] == 'on') {
			$filter .= " and OS.OrgStac_Code is not null";
		}
		
		if ( isset($data['Org_id']) ) {
			$filter .= " and o.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_IsAccess']) ) {
				$filter .= " and o.Org_IsAccess = :Org_IsAccess";
				$queryParams['Org_IsAccess'] = $data['Org_IsAccess'];
			}

			if ( isset($data['OrgType_id']) ) {
				$filter .= " and o.OrgType_id = :OrgType_id";
				$queryParams['OrgType_id'] = $data['OrgType_id'];
			}

			if ( isset($data['Org_Name']) ) {
				$filter .= " and o.Org_Name LIKE :Org_Name";
				$queryParams['Org_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and o.Org_Nick LIKE :Org_Nick";
				$queryParams['Org_Nick'] = "%" . $data['Org_Nick'] . "%";
			}

			if (isset($data['OrgServed_Type'])) {
				$filter .= " and o.OrgType_id = :OrgType_id";
				$queryParams['OrgType_id'] = $data['OrgServed_Type'];
			}
			
			if (!empty($data['DepartAffilType_id'])) {
				$filter .= " and o.DepartAffilType_id = :DepartAffilType_id";
				$queryParams['DepartAffilType_id'] = $data['DepartAffilType_id'];			
			}

			if (!empty($data['OrgType_Code'])) {
				$filter .= " and ot.OrgType_Code = :OrgType_Code";
				$queryParams['OrgType_Code'] = $data['OrgType_Code'];
			}

			if ($data['Org_pid'] != null) {
				if ($data['Org_pid'] > 0) {
					$filter .=  " and o.Org_pid = :Org_pid";
					$queryParams['Org_pid'] = $data['Org_pid'];
				} else {
					$filter .=  " and o.Org_pid is null";
				}
			}

			if (!empty($data['WithoutOrgEndDate'])) {
				$filter .= " and o.Org_EndDate is null";
			}
		}
		
		if (!empty($data['query'])) {
			$filter .= " and o.Org_Nick LIKE :query";
			$queryParams['query'] = "%" . $data['query'] . "%";
		}
		
		if (!empty($data['Lpu_sid'])) {
			$filter .= " and L.Lpu_id = :Lpu_sid";
			$queryParams['Lpu_sid'] = $data['Lpu_sid'];
		}
		
		if (!empty($data['isNotForSystem'])) {
			$filter .= " and (L.Lpu_id is null or o.Org_IsNotForSystem = 2)";
		}

		$query = "
			SELECT top 100
				o.Org_id,
				o.OrgType_id,
				L.Lpu_id,
				o.Org_id as Org_pid,
				RTRIM(o.Org_Code) as Org_Code,
				RTRIM(o.Org_Nick) as Org_Nick,
				RTRIM(o.Org_Name) as Org_Name,
				RTRIM(o.Org_StickNick) as Org_StickNick,
				o.Server_id,
				a.Address_Address as Org_Address,
				OS.OrgStac_Code,
				convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), o.Org_endDate, 104) as Org_endDate
				{$select}
			FROM
				Org o with (nolock)
				left join v_Address a (nolock) on a.Address_id = ISNULL(o.UAddress_id, o.PAddress_id)
				left join fed.OrgStac OS with (nolock) on OS.Org_id = o.Org_id
				left join v_Lpu_all L (nolock) on L.Org_id = O.Org_id
				{$join}
			WHERE (1 = 1)
				" . $filter . "
		";

		//echo getdebugsql($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Возвращает список типов организаций
	*/
	function getOrgTypeList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_IsAccess']) ) {
			$filter .= " and exists(select top 1 o.Org_id from v_Org o (nolock) where o.OrgType_id = ot.OrgType_id and o.Org_IsAccess = :Org_IsAccess)";
			$queryParams['Org_IsAccess'] = $data['Org_IsAccess'];
		}

		if (!empty($data['query'])) {
			$filter .= " and ot.OrgType_Name LIKE :query";
			$queryParams['query'] = "%" . $data['query'] . "%";
		}

		$query = "
			SELECT top 100
				ot.OrgType_id,
				ot.OrgType_Name
			FROM
				v_OrgType ot with (nolock)
			WHERE (1 = 1)
				" . $filter . "
		";

		//echo getdebugsql($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

    /**
	* Возвращает список аптек
	*/
	function getOrgFarmacyNewList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['OrgFarmacy_id']) ) {
			$filter .= " and OrgFarmacy_id = :OrgFarmacy_id";
			$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		}
		else {
			if ( isset($data['OrgFarmacy_Name']) ) {
				$filter .= " and OrgFarmacy_Name LIKE :OrgFarmacy_Name";
				$queryParams['OrgFarmacy_Name'] = "%" . $data['OrgFarmacy_Name'] . "%";
			}

			if ( isset($data['OrgFarmacy_Nick']) ) {
				$filter .= " and OrgFarmacy_Nick LIKE :OrgFarmacy_Nick";
				$queryParams['OrgFarmacy_Nick'] = "%" . $data['OrgFarmacy_Nick'] . "%";
			}
		}

		$query = "
			SELECT
				OrgFarmacy_id,
				RTRIM(OrgFarmacy_Code) as OrgFarmacy_Code,
				RTRIM(OrgFarmacy_Nick) as OrgFarmacy_Nick,
				RTRIM(OrgFarmacy_Name) as OrgFarmacy_Name
			FROM
				v_OrgFarmacy with (nolock)
			WHERE (1 = 1)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Возвращает список страховых организаций
	*/
	function getOrgSmoList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and o.OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and smo.OrgSMO_Name LIKE :OrgSMO_Name";
				$queryParams['OrgSMO_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and smo.OrgSMO_Nick LIKE :OrgSMO_Nick";
				$queryParams['OrgSMO_Nick'] = "%" . $data['Org_Nick'] . "%";
			}

			if ( isset($data['OMSSprTerr_Code']) && $data['OMSSprTerr_Code'] >= 0 && $data['OMSSprTerr_Code'] < 100 && $data['session']['region']['nick'] == 'perm' ) {
				$filter .= " and smo.OrgSmo_RegNomC is not null";
			}

			if (!empty($data['KLRgn_id'])) {
				$filter .= " and smo.KLRgn_id = :KLRgn_id";
				$queryParams['KLRgn_id'] = $data['KLRgn_id'];
			}

			//Если выбрана территория - Башкортостан (OMSSprTerr_Code 61)
			if ( isset($data['OMSSprTerr_Code']) && (($data['session']['region']['nick'] == 'ufa' && $data['OMSSprTerr_Code'] == 61) || $data['session']['region']['nick'] == 'kareliya') ) {
				//, то в списке должны быть только действующие СМО на текущую дату
				$filter .= " AND (smo.OrgSmo_endDate is null OR CAST(smo.OrgSmo_endDate as DATE) >= CAST(dbo.tzGetDate() as DATE))";
			}

		}

		$query = "
			SELECT
				smo.Org_id,
				smo.OrgSMO_id,
				o.Org_id as Org_pid,
				rtrim(o.Org_Code) as Org_Code,
				rtrim(smo.OrgSMO_Nick) as Org_Nick,
				rtrim(smo.OrgSMO_Name) as Org_Name,
				o.Server_id,
				convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), o.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgSmo smo with(nolock)
				LEFT JOIN v_Org o with(nolock) on o.Org_id = smo.Org_id
			WHERE (1 = 1)
				" . $filter . "
		";
        //echo getDebugSQL($query, $queryParams);
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* Возвращает список страховых организаций
	*/
	function getOrgSmoDmsList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and o.OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['Org_id'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and smo.OrgSMO_Name LIKE :OrgSMO_Name";
				$queryParams['OrgSMO_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and smo.OrgSMO_Nick LIKE :OrgSMO_Nick";
				$queryParams['OrgSMO_Nick'] = "%" . $data['Org_Nick'] . "%";
			}

			if ( isset($data['OMSSprTerr_Code']) && $data['OMSSprTerr_Code'] >= 0 && $data['OMSSprTerr_Code'] < 100 && $data['session']['region']['nick'] == 'perm' ) {
				$filter .= " and smo.OrgSmo_RegNomC is not null";
			}

			if (!empty($data['KLRgn_id'])) {
				$filter .= " and smo.KLRgn_id = :KLRgn_id";
				$queryParams['KLRgn_id'] = $data['KLRgn_id'];
			}

			//Если выбрана территория - Башкортостан (OMSSprTerr_Code 61)
			if ( isset($data['OMSSprTerr_Code']) && (($data['session']['region']['nick'] == 'ufa' && $data['OMSSprTerr_Code'] == 61) || $data['session']['region']['nick'] == 'kareliya') ) {
				//, то в списке должны быть только действующие СМО на текущую дату
				$filter .= " AND (smo.OrgSmo_endDate is null OR CAST(smo.OrgSmo_endDate as DATE) >= CAST(dbo.tzGetDate() as DATE))";
			}
			
		}

		$query = "
			SELECT
				smo.OrgSMO_id as Org_id,
				o.Org_id as Org_pid,
				rtrim(o.Org_Code) as Org_Code,
				rtrim(o.Org_Nick) as Org_Nick,
				rtrim(o.Org_Name) as Org_Name,
				o.Server_id,
				convert(varchar(10), o.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), o.Org_endDate, 104) as Org_endDate
			FROM
				v_OrgSmo smo with(nolock)
				INNER JOIN v_Org o with(nolock) on o.Org_id = smo.Org_id
			WHERE (OrgSmo_isDMS = 2)
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*  Возвращает список аптек
	*  Используется на форме просмотра списка аптек
	*/
	function loadOrgFarmacyList($data)
	{
		$sql = "
			SELECT 
				OrgFarmacy.OrgFarmacy_id,
				OrgFarmacy.Org_id,
				Org.Org_Name as OrgFarmacy_Name,
				Org.Org_Nick as OrgFarmacy_Nick,
				OrgFarmacy.OrgFarmacy_HowGo,
				Addr.Address_Address as OrgFarmacy_Address,
				OrgFarmacy.OrgFarmacy_ACode,
				Org.Org_Phone as OrgFarmacy_Phone,
				CASE WHEN (ISNULL(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2) THEN 'false' ELSE 'true' END as OrgFarmacy_IsDisabled,
				CASE WHEN (ISNULL(OrgFarmacy.OrgFarmacy_IsFedLgot, 2) = 2) THEN 'true' ELSE 'false' END as OrgFarmacy_IsFedLgot,
				CASE WHEN (ISNULL(OrgFarmacy.OrgFarmacy_IsRegLgot, 2) = 2) THEN 'true' ELSE 'false' END as OrgFarmacy_IsRegLgot,
				CASE WHEN (ISNULL(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2) THEN 'true' ELSE 'false' END as OrgFarmacy_IsNozLgot
			FROM OrgFarmacy with (nolock)
				INNER JOIN Org with (nolock) ON Org.Org_id = OrgFarmacy.Org_id
				LEFT JOIN [Address] Addr with (nolock) ON Addr.Address_id = Org.PAddress_id
			WHERE ISNULL(OrgFarmacy.OrgFarmacy_IsFarmacy, 2) = 2
			ORDER BY Org.Org_Nick
		";
		$res=$this->db->query($sql);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка МО для проставления OID
	 * @param array $data
	 * @return array|bool
	 */
	function loadLpuSetOIDGrid($data)
	{
		$query = "
			select
				-- select
				L.Lpu_id,
				L.Lpu_Nick,
				L.Lpu_Name,
				convert(varchar(10), L.Lpu_begDate, 104) as Lpu_begDate,
				convert(varchar(10), L.Lpu_endDate, 104) as Lpu_endDate,
				--IsFRMO.YesNo_Code as Lpu_isFRMO,
				L.Lpu_isFRMO,
				PT.PassportToken_tid
				-- end select
			from
				-- from
				v_Lpu L with (nolock)
				left join fed.v_PassportToken PT with (nolock) on PT.Lpu_id = L.Lpu_id
				left join v_YesNo IsFRMO with(nolock) on IsFRMO.YesNo_id = L.Lpu_isFRMO
				-- end from
			where
				-- where
				1=1
				--PT.PassportToken_tid is null
				-- end where
			order by
				-- order by
				L.Lpu_Nick
				-- end order by
		";

		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']));
		$count_result = $this->queryResult(getCountSQLPH($query));

		if (!is_array($result) || !is_array($count_result)) {
			return false;
		}

		return array(
			'data' => $result,
			'totalCount' => $count_result[0]['cnt']
		);
	}

	/**
	 * Cохранение значения флага ФРМО
	 * @param array $data
	 * @return array
	 */
	function setLpuIsFRMO($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_isFRMO' => $data['Lpu_isFRMO'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			declare @date datetime = (select top 1 dbo.tzGetDate())
			set nocount on
			begin try
				update Lpu with(rowlock)
				set
					Lpu_isFRMO = :Lpu_isFRMO,
					Lpu_updDT = @date,
					pmUser_updID = :pmUser_id
				where Lpu_id = :Lpu_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('Ошибка при сохранении значения флага ФРМО');
		}
		return $response;
	}

	/**
	 * Cохранение значения OID
	 * @param array $data
	 * @return array
	 */
	function setLpuOID($data) {
		$params = array(
			'PassportToken_id' => null,
			'PassportToken_tid' => !empty($data['PassportToken_tid'])?$data['PassportToken_tid']:null,
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$params['PassportToken_id'] = $this->getFirstResultFromQuery("
			select top 1 PassportToken_id from fed.v_PassportToken with(nolock) where Lpu_id = :Lpu_id
		", $data, true);
		if ($params['PassportToken_id'] === false) {
			return $this->createError('','Ошибка при поиске PassportToken_id');
		}

		return $this->savePassportToken($params);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function savePassportToken($data) {
		$params = array(
			'PassportToken_id' => !empty($data['PassportToken_id'])?$data['PassportToken_id']:null,
			'PassportToken_tid' => !empty($data['PassportToken_tid'])?$data['PassportToken_tid']:null,
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['PassportToken_id'])) {
			$procedure = "fed.p_PassportToken_ins";
		} else {
			$procedure = "fed.p_PassportToken_upd";
		}
		$query = "
			declare 
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@PassportToken_id bigint;
			set @PassportToken_id = :PassportToken_id;
			exec {$procedure}
				@PassportToken_id = @PassportToken_id output,
				@PassportToken_tid = :PassportToken_tid,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id;
			select @PassportToken_id as PassportToken_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('Ошибка при сохранении PassportToken');
		}
		return $response;
	}

	/**
	*  Получение списка МО у которых не проставлен ОИД
	*/
	function loadLpuWithoutOID($data)
	{
		$query = "
			Select
				-- select
				L.Lpu_id,
				L.Lpu_Nick,
				L.Lpu_Name
				-- end select
			from
				-- from
				v_Lpu L with (nolock)
				left join fed.v_PassportToken PT with (nolock) on PT.Lpu_id = L.Lpu_id
				-- end from
			where
				-- where
				PT.PassportToken_tid is null
				-- end where
			order by
				-- order by
				L.Lpu_Nick
				-- end order by
		";

		//echo getDebugSQL(getLimitSQL($query, $data['start'], $data['limit']), array()));die();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), array());
		$result_count = $this->db->query(getCountSQLPH($query), array());

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
	* Сохраняет руководство организации
	*/
	function setOID($data) {

		$query = "
			select
				PassportToken_id
			from
				fed.v_PassportToken with(nolock)
			where
				Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if (is_array($response) && count($response) == 1 && !empty($response[0]['PassportToken_id'])){
			$passportTokenParams = $response[0];
			$proc = 'upd';
		} else if (is_array($response) && count($response) == 0){
			$proc = 'ins';
			$passportTokenParams['PassportToken_id'] = 0;
		} else {
			return false;
		}

		$passportTokenParams['pmUser_id'] = $data['pmUser_id'];
		$passportTokenParams['Lpu_id'] = $data['Lpu_id'];

		$query = "
			declare
				@PassportToken_id bigint = :PassportToken_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec fed.p_PassportToken_{$proc}
				@PassportToken_id = @PassportToken_id output,
				@PassportToken_tid = -1,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id
			select @PassportToken_id as PassportToken_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		//echo getDebugSQL($query, $passportTokenParams);die;
		$result = $this->db->query($query, $passportTokenParams);

		if (is_object($result)){
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*  Удаляет данные по руководителю
	*/
	function deleteOrgHead($data) {
		$query = "
			select count(BirthSvid_id) as Count
			from v_BirthSvid with(nolock)
			where OrgHead_id = :OrgHead_id
		";
		$result = $this->db->query($query, array(
			'OrgHead_id' => $data['OrgHead_id']
		));
		if ( !is_object($result) ) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
		$resp = $result->result('array');
		if ($resp[0]['Count'] > 0) {
			return array('Error_Msg' => "Невозможно удалить руководителя.<br/>Ссылка на руководителя имеется в свидетельствах о рождении.");
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_OrgHead_del
				@OrgHead_id = :OrgHead_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'OrgHead_id' => $data['OrgHead_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление руководителя)');
		}
	}
	
	/**
	*  Удаляет данные по счету организации
	*/
	function deleteOrgRSchet($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_OrgRSchet_del
				@OrgRSchet_id = :OrgRSchet_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление счета)');
		}
	}
	
	/**
	*  Возвращает данные по руководителю
	*/
	function loadOrgHead($data) {
		$query = "
			SELECT TOP 1
				OrgHead_id,
				Person_id,
				OrgHeadPost_id,
				OrgHead_Phone,
				OrgHead_Fax,
				OrgHead_Email,
				OrgHead_Mobile,
				OrgHead_CommissNum,
				convert(varchar(10), OrgHead_CommissDate, 104) as OrgHead_CommissDate,
				OrgHead_Address,
				Server_id,
				Lpu_id,
				LpuUnit_id
			FROM
				v_OrgHead with(nolock)
			WHERE
				OrgHead_id = ?
		";
		$res = $this->db->query($query, array($data['OrgHead_id']));

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	*  Возвращает данные по счету организации
	*/
	function loadOrgRSchet($data) {
		$filter = "";
		$queryParams = array(
			'OrgRSchet_id' => $data['OrgRSchet_id']
		);

		if (isset($data['session']['Org_id'])) {
			$filter .= " and ors.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['session']['org_id'];
		}
		if (!empty($data['fromAPI']) && !empty($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and L.Lpu_id = :Lpu_id";
		}
		$query = "
			SELECT TOP 1
				ORS.OrgRSchet_id,
				ORS.OrgRSchet_RSchet,
				ORS.OrgRSchetType_id,
				ORS.OrgBank_id,
				convert(varchar(10), ORS.OrgRSchet_begDate, 104) as OrgRSchet_begDate,
				convert(varchar(10), ORS.OrgRSchet_endDate, 104) as OrgRSchet_endDate,
				ORS.Okv_id,
				ORS.OrgRSchet_Name,
				ORS.Org_id,
				ORS.Server_id
			FROM
				v_OrgRSchet ors with(nolock)
				left join v_Lpu L with (nolock) on L.Org_id = ors.Org_id
			WHERE
				ors.OrgRSchet_id = :OrgRSchet_id
				{$filter}
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	*  Возвращает данные по КБК счета организации
	*/
	function loadOrgRSchetKBK($data) {
		$filter = "";
		$queryParams = array(
			'OrgRSchetKBK_id' => $data['OrgRSchetKBK_id']
		);

		if (isset($data['session']['Org_id'])) {
			$filter .= " and ors.Org_id = :Org_id";
			$queryParams['Org_id'] = $data['session']['org_id'];
		}
		
		if (!empty($data['fromAPI']) && !empty($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and L.Lpu_id = :Lpu_id";
		}

		$query = "
			SELECT TOP 1
				orsk.OrgRSchetKBK_id,
				orsk.OrgRSchet_id,
				orsk.OrgRSchet_KBK
			FROM
				v_OrgRSchetKBK orsk with(nolock)
				left join v_OrgRSchet ors with(nolock) on ors.OrgRSchet_id = orsk.OrgRSchet_id
				left join v_Lpu L with (nolock) on L.Org_id = ors.Org_id
			WHERE
				orsk.OrgRSchetKBK_id = :OrgRSchetKBK_id
				{$filter}
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение идентификатора ЛПУ по идентификатору организации
	 */
	function getLpuOnOrg($data) {
		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "SELECT \"Lpu_id\" FROM dbo.\"v_Lpu\" WHERE \"Org_id\"=? LIMIT 1";
			$query = $this->db->query( $sql, array( $data['Org_id'] ) );
			if ( is_object( $query ) ) {
				$result = $query->row_array();
				if ( sizeof( $result ) ) {
					return $result['Lpu_id'];
				}
			}

			return null;
		} else {
			$query = "
				SELECT TOP 1
					Lpu_id
				FROM v_Lpu Lpu (nolock)
				WHERE Lpu.Org_id = :Org_id
			";
			$result = NULL;
			$res = $this->db->query($query, array('Org_id' => $data['Org_id']));
			if ( is_object($res) ) {
				$rows = $res->result('array');
				if (count($rows)>0) {
					$result = $rows[0]['Lpu_id'];
				}
			}
			return $result;
		}
	}

	/**
	 * Получение идентификатора аптеки по идентификатору организации
	 */
	function getOrgFarmacyOnOrg($data) {
		$query = "
			SELECT TOP 1
				orf.OrgFarmacy_id
			FROM v_OrgFarmacy orf (nolock)
			WHERE orf.Org_id = :Org_id
		";
		$result = NULL;
		$res = $this->db->query($query, array('Org_id' => $data['Org_id']));
		if ( is_object($res) ) {
			$rows = $res->result('array');
			if (count($rows)>0) {
				$result = $rows[0]['OrgFarmacy_id'];
			}
		}
		return $result;
	}

	/**
	 * Получение идентификатора организации по идентификатору ЛПУ
	 */
	function getOrgOnLpu($data) {
		$query = "
			SELECT TOP 1
				Org_id
			FROM v_Lpu Lpu (nolock)
			WHERE Lpu.Lpu_id = :Lpu_id
		";
		$result = NULL;
		$res = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
		if ( is_object($res) ) {
			$rows = $res->result('array');
			if (count($rows)>0) {
				$result = $rows[0]['Org_id'];
			}
		}
		return $result;
	}

	/**
	*  Возвращает данные по организации
	*/
	function getOrgData($data) {
		$query = "
			SELECT TOP 1
				Org.Org_id as Org_id,
				Org.OrgType_id as OrgType_id,
				RTRIM(Org.Org_Code) as Org_Code,
				RTRIM(Org.Org_Nick) as Org_Nick,
				RTRIM(Org.Org_StickNick) as Org_StickNick,
				Org.Org_Description,
				Org.Org_rid,
				Org.Org_nid,
				convert(varchar(10), Org.Org_begDate, 104) as Org_begDate,
				convert(varchar(10), Org.Org_endDate, 104) as Org_endDate,
				RTRIM(Org.Org_Name) as Org_Name,
				RTRIM(Org.Org_Phone) as Org_Phone,
				RTRIM(Org.Org_Email) as Org_Email,
				RTRIM(Org.Org_INN) as Org_INN,
				RTRIM(Org.Org_OGRN) as Org_OGRN,
				RTRIM(Org.Org_OKPO) as Org_OKPO,
				RTRIM(Org.Org_KPP) as Org_KPP,
				RTRIM(Org.Org_OKATO) as Org_OKATO,
				Org.Oktmo_id,
				Org.Org_ONMSZCode,
				Oktmo.Oktmo_Code as Oktmo_Name,
				Org.Okfs_id as Okfs_id,
				Org.Okopf_id as Okopf_id,
				RTRIM(Org.Okved_id) as Okved_id,
				Org.Org_Marking as Org_Marking,
				case when Org.Org_IsNotForSystem = 2 then 'true' else 'false' end as Org_IsNotForSystem,
				UAD.Address_id as UAddress_id,
				UAD.Address_Zip as UAddress_Zip,
				UAD.KLCountry_id as UKLCountry_id,
				UAD.KLRGN_id as UKLRGN_id,
				UAD.KLSubRGN_id as UKLSubRGN_id,
				UAD.KLCity_id as UKLCity_id,
				UAD.KLTown_id as UKLTown_id,
				UAD.KLStreet_id as UKLStreet_id,
				UAD.Address_House as UAddress_House,
				UAD.Address_Corpus as UAddress_Corpus,
				UAD.Address_Flat as UAddress_Flat,
				UAD.PersonSprTerrDop_id as UPersonSprTerrDop_id,
				UAD.Address_Address as UAddress_Address,
				UAD.Address_Address as UAddress_AddressText,
				PAD.Address_id as PAddress_id,
				PAD.Address_Zip as PAddress_Zip,
				PAD.KLCountry_id as PKLCountry_id,
				PAD.KLRGN_id as PKLRGN_id,
				PAD.KLSubRGN_id as PKLSubRGN_id,
				PAD.KLCity_id as PKLCity_id,
				PAD.KLTown_id as PKLTown_id,
				PAD.KLStreet_id as PKLStreet_id,
				PAD.PersonSprTerrDop_id as PPersonSprTerrDop_id,
				PAD.Address_House as PAddress_House,
				PAD.Address_Corpus as PAddress_Corpus,
				PAD.Address_Flat as PAddress_Flat,
				PAD.Address_Address as PAddress_Address,
				PAD.Address_Address as PAddress_AddressText,
				os.OrgSMO_id,
				ofa.OrgFarmacy_id,
				ob.OrgBank_id,
				od.OrgDep_id,
				oa.OrgAnatom_id,
				l.Lpu_f003mcod,
				Org.Org_f003mcod,
				-- если есть записи в таблицах то жёстко устанавливаем тип.
				case
					when Org.Org_id IN ('68320020775') then 'touz' -- костыль для минздрава
					when os.OrgSMO_id IS NOT NULL then 'smo' -- это и всё, что ниже можно впринципе убрать если будут всем проставлены правильные OrgType_id
					when l.Lpu_id IS NOT NULL then 'lpu'
					when ofa.OrgFarmacy_id IS NOT NULL then 'farm'
					when ob.OrgBank_id IS NOT NULL then 'bank'
					when od.OrgDep_id IS NOT NULL then 'dep'
					when oa.OrgAnatom_id IS NOT NULL then 'anatom'
					else ot.OrgType_SysNick
				end as OrgType_SysNick,
				case
					when Org.Org_id IN ('68320020775') then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'touz') -- костыль для минздрава
					when os.OrgSMO_id IS NOT NULL then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'smo') -- это и всё, что ниже можно впринципе убрать если будут всем проставлены правильные OrgType_id
					when ot.OrgType_id is not null and ot.OrgType_id = 20 then ot.OrgType_id -- костыль для определения ИП, можно будет убрать если OrgType_id будет работать
					when l.Lpu_id IS NOT NULL then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'lpu')
					when ofa.OrgFarmacy_id IS NOT NULL then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'farm')
					when ob.OrgBank_id IS NOT NULL then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'bank')
					when od.OrgDep_id IS NOT NULL then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'dep')
					when oa.OrgAnatom_id IS NOT NULL then (select top 1 OrgType_id from v_OrgType with (nolock) where OrgType_SysNick = 'anatom')
					else ot.OrgType_id
				end as OrgType_id,
				-- дополнительные поля по аптеке
				RTRIM(ofa.OrgFarmacy_HowGo) as OrgFarmacy_HowGo,
				RTRIM(ofa.OrgFarmacy_ACode) as OrgFarmacy_ACode,
				ISNULL(ofa.OrgFarmacy_IsEnabled, 1) as OrgFarmacy_IsEnabled,
				ISNULL(ofa.OrgFarmacy_IsFedLgot, 1) as OrgFarmacy_IsFedLgot,
				ISNULL(ofa.OrgFarmacy_IsRegLgot, 1) as OrgFarmacy_IsRegLgot,
				ISNULL(ofa.OrgFarmacy_IsNozLgot, 1) as OrgFarmacy_IsNozLgot,
				ISNULL(ofa.OrgFarmacy_IsNarko, 1) as OrgFarmacy_IsNarko,
				-- дополнительные поля по банку
				ISNULL(ob.OrgBank_KSchet, '') as OrgBank_KSchet,
				ISNULL(ob.OrgBank_BIK, '') as OrgBank_BIK,
				-- дополнительные поля по СМО
				ISNULL(os.OrgSMO_isDMS, 1) as OrgSMO_isDMS,
				os.OrgSMO_RegNomC,
				os.OrgSMO_RegNomN,
				os.Orgsmo_f002smocod,
				os.KLRGN_id as KLRGNSmo_id,
				ISNULL(Org.Org_IsAccess, 1) as Org_IsAccess,
				ostac.OrgStac_Code
			FROM Org with (nolock)
				left join [Address] PAD with (nolock) on PAD.Address_id = Org.PAddress_id
				left join [Address] UAD with (nolock) on UAD.Address_id = Org.UAddress_id
				left join v_OrgType ot with (nolock) on ot.OrgType_id = Org.OrgType_id
				left join OrgSMO os with (nolock) on os.Org_id = Org.Org_id
				left join OrgBank ob with (nolock) on ob.Org_id = Org.Org_id
				left join OrgFarmacy ofa with (nolock) on ofa.Org_id = Org.Org_id
				left join OrgDep od with (nolock) on od.Org_id = Org.Org_id
				left join OrgAnatom oa with (nolock) on oa.Org_id = Org.Org_id
				left join Lpu l with (nolock) on l.Org_id = Org.Org_id
				left join v_Oktmo Oktmo (nolock) on Oktmo.Oktmo_id = Org.Oktmo_id
				outer apply (
					select top 1 OrgStac_Code
					from fed.OrgStac with (nolock)
					where Org_id = Org.Org_id
				) ostac
			WHERE Org.Org_id = :Org_id
		";
		//echo getDebugSQL($query,array('Org_id' => $data['Org_id']));exit();
		$res = $this->db->query($query, array('Org_id' => $data['Org_id']));

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	*  Возвращает данные по ЛПУ
	*/
	function getLpuData($data) {
		$query = "
			SELECT TOP 1
				Lp.Org_id as Org_id,
				Lp.Lpu_id as Lpu_id,
				RTRIM(Org.Org_Code) as Org_Code,
				RTRIM(Org.Org_Nick) as Org_Nick,
				RTRIM(Org.Org_Name) as Org_Name,
				RTRIM(Org.Org_Phone) as Org_Phone,
				RTRIM(Org.Org_Email) as Org_Email,
				RTRIM(Org.Org_INN) as Org_INN,
				RTRIM(Org.Org_OGRN) as Org_OGRN,
				RTRIM(Org.Okved_id) as Okved_id,
				UAD.Address_id as UAddress_id,
				UAD.Address_Zip as UAddress_Zip,
				UAD.KLCountry_id as UKLCountry_id,
				UAD.KLRGN_id as UKLRGN_id,
				UAD.KLSubRGN_id as UKLSubRGN_id,
				UAD.KLCity_id as UKLCity_id,
				UAD.KLTown_id as UKLTown_id,
				UAD.KLStreet_id as UKLStreet_id,
				UAD.Address_House as UAddress_House,
				UAD.Address_Corpus as UAddress_Corpus,
				UAD.Address_Flat as UAddress_Flat,
				UAD.Address_Address as UAddress_Address,
				UAD.Address_Address as UAddress_AddressText,
				PAD.Address_id as PAddress_id,
				PAD.Address_Zip as PAddress_Zip,
				PAD.KLCountry_id as PKLCountry_id,
				PAD.KLRGN_id as PKLRGN_id,
				PAD.KLSubRGN_id as PKLSubRGN_id,
				PAD.KLCity_id as PKLCity_id,
				PAD.KLTown_id as PKLTown_id,
				PAD.KLStreet_id as PKLStreet_id,
				PAD.Address_House as PAddress_House,
				PAD.Address_Corpus as PAddress_Corpus,
				PAD.Address_Flat as PAddress_Flat,
				PAD.Address_Address as PAddress_Address,
				PAD.Address_Address as PAddress_AddressText,
				Lp.LpuType_id,			
				Lp.Lpu_RegNomC,
				Lp.Lpu_RegNomN,
				Lp.Lpu_IsOMS,
				Lp.Lpu_Ouz,
				Org.Org_OKPO,
				Org.Okonh_id,
				Org.Org_OKATO,
				Org.Okogu_id,
				Org.Okopf_id,
				Org.Okfs_id
			FROM v_Lpu_all Lp with(nolock)
				inner join Org with(nolock) on Org.Org_id = Lp.Org_id
				left join [Address] PAD on PAD.Address_id = Org.PAddress_id
				left join [Address] UAD on UAD.Address_id = Org.UAddress_id
			WHERE Lp.Org_id = :Org_id
		";
		$res = $this->db->query($query, array('Org_id' => $data['Org_id']));

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение паспорта ЛПУ
	 */
	function getLpuPassport($data) {
		$query = "
			SELECT TOP 1
				Lpu.Lpu_id as Lpu_id,
				Lpu.Lpu_AmbulanceCount,
				Lpu.Lpu_IsEmailFixed
			FROM v_Lpu Lpu with(nolock)
				--inner join Org with(nolock) on Org.Org_id = Lpu.Org_id
			WHERE Lpu.Lpu_id = :Lpu_id
		";
		$res = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	*  Возвращает максимальный Org_Code + 1, для автогенерации кода организации
	*/
	function getMaxOrgCode() {
		$query = "
			select
				case when
					MAX(org_code) >= 2147483647
				then
					0
				else
					MAX(org_code) + 1
				end as Org_Code,
				case when
					MAX(org_code) >= 2147483647
				then
					'Достигнут максимальный код организации в БД'  
				else
					'' 
				end as Error_Msg
			from Org with(nolock)
		";
		$res = $this->db->query($query);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Получение сисника для OrgType_id
	*/	
	function getOrgTypeSysNick($OrgType_id) 
	{
		$query = "
			select
				OrgType_SysNick
			from 
				v_OrgType with (nolock)
			where
				OrgType_id = :OrgType_id
		";
		$res = $this->db->query($query, array( 'OrgType_id' => $OrgType_id ));

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0]['OrgType_SysNick'];
			}
		}

		return '';
	}

	/**
	* Получение наименования организации для Org_id
	*/
	function getOrgName($Org_id)
	{
		$query = "
			select
				Org_Name
			from
				v_Org with (nolock)
			where
				Org_id = :Org_id
		";
		$res = $this->db->query($query, array( 'Org_id' => $Org_id ));

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0]['Org_Name'];
			}
		}

		return '';
	}
	
	/**
	* Проверяет что дата открытия организации наследователя больше даты закрытия сохраняемой организации
	*/
	function checkOrgRidBegDate($data) 
	{
		$query = "
			select
				Org_id
			from 
				v_Org with (nolock)
			where
				Org_id = :Org_rid and Org_begDate >= :Org_endDate
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	* Сохраняет руководство организации
	*/
	function saveOrgHead($data) {
		$procedure_action = '';	

		if ( !isset($data['OrgHead_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :OrgHead_id;

			exec p_OrgHead_" . $procedure_action . "
				@Server_id  = :Server_id,
				@Person_id  = :Person_id,
				@OrgHead_id = @Res output,
				@Lpu_id = :Lpu_id,
				@LpuUnit_id = :LpuUnit_id,
				@OrgHeadPost_id = :OrgHeadPost_id,
				@OrgHead_Phone = :OrgHead_Phone,
				@OrgHead_Fax = :OrgHead_Fax,
				@OrgHead_Email = :OrgHead_Email,
				@OrgHead_Mobile = :OrgHead_Mobile,
				@OrgHead_CommissNum = :OrgHead_CommissNum,
				@OrgHead_CommissDate = :OrgHead_CommissDate,
				@OrgHead_Address = :OrgHead_Address,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as OrgHead_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'OrgHeadPost_id' => $data['OrgHeadPost_id'],
			'OrgHead_Phone' => $data['OrgHead_Phone'],
			'OrgHead_Fax' => $data['OrgHead_Fax'],
			'OrgHead_Email' => $data['OrgHead_Email'],
			'OrgHead_Mobile' => $data['OrgHead_Mobile'],
			'OrgHead_CommissNum' => $data['OrgHead_CommissNum'],
			'OrgHead_CommissDate' => $data['OrgHead_CommissDate'],
			'OrgHead_Address' => $data['OrgHead_Address'],
			'LpuUnit_id' => isset($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		if ( isset($data['OrgHead_id']) && $data['OrgHead_id'] > 0 )
			$queryParams['OrgHead_id'] = $data['OrgHead_id'];
		else
			$queryParams['OrgHead_id'] = null;
		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* Сохраняет руководство организации
	*/
	function saveLpuEmail($data) {
		$query = "
			UPDATE
				Org
			SET Org_Email = ?
			WHERE
				Org_id = (select Org_id from Lpu with(nolock) where Lpu_id = ?)
				and (IsNull(Org_IsEmailFixed,1) != 2)
		";
		
		$res = $this->db->query($query, array($data['Lpu_Email'], $data['Lpu_id']));
		
		$query = "
			UPDATE
				Lpu
			SET Lpu_AmbulanceCount = ?
			WHERE
				Lpu_id = ?
		";
		
		$res = $this->db->query($query, array($data['Lpu_AmbulanceCount'], $data['Lpu_id']));

		$response[0]['Error_Msg'] = '';
		return $response;
	}

	/**
	* Сохраняет расчетный счет организации
	*/
	function saveOrgRSchet($data) {
		$procedure_action = '';	

		if ( empty($data['OrgRSchet_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}
		
		$org = "(select top 1 Org_id from Lpu with(nolock) where Lpu_id = :Lpu_id)";
		if (!empty($data['Org_id'])) {
			$org = ":Org_id";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@Og_id bigint;
			set @Res = :OrgRSchet_id;
			set @Og_id = {$org};

			exec p_OrgRSchet_" . $procedure_action . "
				@Server_id  = :Server_id,
				@OrgRSchet_id = @Res output,
				@Org_id = @Og_id,
				@OrgBank_id = :OrgBank_id,
				@OrgRSchetType_id = :OrgRSchetType_id,
				@Okv_id = :Okv_id,
				@OrgRSchet_begDate = :OrgRSchet_begDate,
				@OrgRSchet_endDate = :OrgRSchet_endDate,
				@OrgRSchet_RSchet = :OrgRSchet_RSchet,
				@OrgRSchet_Name = :OrgRSchet_Name,				 
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as OrgRSchet_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['Org_id'],
			'Server_id' => $data['Server_id'],
			'OrgBank_id' => $data['OrgBank_id'],
			'OrgRSchetType_id' => $data['OrgRSchetType_id'],
			'Okv_id' => $data['Okv_id'],
			'OrgRSchet_begDate' => $data['OrgRSchet_begDate'],
			'OrgRSchet_endDate' => $data['OrgRSchet_endDate'],
			'OrgRSchet_RSchet' => $data['OrgRSchet_RSchet'],
			'OrgRSchet_Name' => $data['OrgRSchet_Name'],
			'pmUser_id' => $data['pmUser_id']
		);
		if ( isset($data['OrgRSchet_id']) && $data['OrgRSchet_id'] > 0 )
			$queryParams['OrgRSchet_id'] = $data['OrgRSchet_id'];
		else
			$queryParams['OrgRSchet_id'] = null;
		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Сохраняет КБК расчетного счета организации
	*/
	function saveOrgRSchetKBK($data) {
		$procedure_action = '';	

		if ( !isset($data['OrgRSchetKBK_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :OrgRSchetKBK_id;

			exec p_OrgRSchetKBK_" . $procedure_action . "
				@OrgRSchetKBK_id = @Res output,
				@OrgRSchet_id = :OrgRSchet_id,
				@OrgRSchet_KBK = :OrgRSchet_KBK,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as OrgRSchetKBK_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'OrgRSchet_KBK' => $data['OrgRSchet_KBK'],
			'pmUser_id' => $data['pmUser_id']
		);
		if ( isset($data['OrgRSchetKBK_id']) && $data['OrgRSchetKBK_id'] > 0 )
			$queryParams['OrgRSchetKBK_id'] = $data['OrgRSchetKBK_id'];
		else
			$queryParams['OrgRSchetKBK_id'] = null;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение записей «Организация». Метод для API.
	 */
	function getOrgForAPI($data) {
		$filter = "";
		$params = array();

		if (!empty($data['Org_id'])) {
			$filter .= " and Org_id = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		} else if (!empty($data['Org_Code'])) {
			$filter .= " and Org_Code = :Org_Code";
			$params['Org_Code'] = $data['Org_Code'];
		} else if (!empty($data['Org_Name'])) {
			$filter .= " and Org_Name = :Org_Name";
			$params['Org_Name'] = $data['Org_Name'];
		} else if (!empty($data['Org_Nick'])) {
			$filter .= " and Org_Nick = :Org_Nick";
			$params['Org_Nick'] = $data['Org_Nick'];
		} else {
			return array();
		}

		$query = "
			select
				Org_id,
				Org_Code,
				Org_Name,
				Org_Nick,
				OrgType_id,
				UAddress_id
			from
				v_Org (nolock)
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение записей «Адрес» для организации. Метод для API.
	 */
	function getAddressForAPI($data) {
		$query = "
			select
				a.Address_Address,
				a.KLCountry_id,
				a.Address_id
			from
				v_Org o (nolock)
				left join v_Address a (nolock) on a.Address_id = o.UAddress_id
			where
				o.Org_id = :Org_id
		";

		return $this->queryResult($query, array(
			'Org_id' => $data['Org_id']
		));
	}
	
	/**
	* Сохраняет организацию
	*/
	function saveOrg($data) {
		$procedure_action = '';
		
		if ( !isSuperAdmin() && !havingGroup('AdminOrgReference') && isset($data['Org_id']) && $data['Org_id'] > 0 && $data["isminzdrav"]==false)
		{
			// проверка на возможность редактирования
			$sql = "
				select top 1 Server_id
				from Org with (nolock)
				where Org_id = ?
			";
			$res = $this->db->query($sql, array($data['Org_id']));
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ( $sel[0]['Server_id'] == 0 )
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'Вы не можете редактировать эту организацию.';
					return $sel;
				}
			}
		}
		
		// проверяем INN
		if ( $data['Org_INN'] != '' )
		{
			$sql = "
				select
					dbo.CheckINN('{$data['Org_INN']}') as is_inn_valid
			";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ($sel[0]['is_inn_valid'] > 0)
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'ИНН не соответствует алгоритму формирования.';
					return $sel;
				}
			}
			else
			{
				$sel[0]['Error_Code'] = 1;
				$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ИНН';
				return $sel;
			}
			
			/*$id_filter = "";
			if ( isset($data['Org_id']) && $data['Org_id'] > 0 )
				$id_filter = " and Org_id <> {$data['Org_id']} ";			
			if ( !isset($data['check_double_inn_cancel']) && !($data['check_double_inn_cancel'] == 2) )
			{
				// проверка на двойников по INN
				$sql = "
					select 
						top 1 
						rtrim(Org_Nick),
						count(Org_id) as cnt
					from Org with(nolock)
					where Org_INN = '{$data['Org_INN']}'
					{$id_filter}
					group by Org_Nick
					having count(Org_id) > 0
				";
				$res = $this->db->query($sql);
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ( is_array($sel) && count($sel) > 0 && $sel[0]['cnt'] > 0 )
					{
						$sel[0]['Error_Code'] = '777';
						$sel[0]['Error_Msg'] = 'ИНН совпывмаывмывмва
						ад
						ает с ИНН организации "'.$sel[0]['Org_Nick'].'" ';
						return $sel;
					}
				}
				else
				{
					$sel[0]['Error_Code'] = 1;
					$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ИНН';
					return $sel;
				}
			}*/
		}
		
		// проверяем ОГРН
		if ( $data['Org_OGRN'] != '' )
		{
			$sql = "
				select
					dbo.CheckOGRN('{$data['Org_OGRN']}') as is_ogrn_valid
			";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ($sel[0]['is_ogrn_valid'] > 0)
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'ОГРН не соответствует алгоритму формирования.';
					return $sel;
				}
			}
			else
			{
				$sel[0]['Error_Code'] = 1;
				$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ОГРН';
				return $sel;
			}
			
			$id_filter = "";
			if ( isset($data['Org_id']) && $data['Org_id'] > 0 )
				$id_filter = " and Org_id <> {$data['Org_id']} ";			
			if ( !isset($data['check_double_ogrn_cancel']) || !($data['check_double_ogrn_cancel'] == 2) )
			{
				// проверка на двойников по OGRN
				$sql = "
					select 
						top 1 
						rtrim(Org_Name) as Org_Name, 
						count(Org_id) as cnt
					from Org with (nolock)
					where Org_OGRN = '{$data['Org_OGRN']}'
					{$id_filter}
					group by Org_Name
					having count(Org_id) > 0
				";
				$res = $this->db->query($sql);
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ( is_array($sel) && count($sel) > 0 && $sel[0]['cnt'] > 0 )
					{
						if ( !isset($data['check_double_inn_cancel']) || !($data['check_double_inn_cancel'] == 2) )
							$sel[0]['Error_Code'] = '888';
						else
							$sel[0]['Error_Code'] = '889';
						$sel[0]['Error_Msg'] = 'ОГРН совпадает с ОГРН организации: "' . $sel[0]['Org_Name'] . '".';
						return $sel;
					}
				}
				else
				{
					$sel[0]['Error_Code'] = 1;
					$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ОГРН';
					return $sel;
				}
			}
		}
		
		// проверяем INN
		if ( $data['Org_INN'] != '' )
		{
			$id_filter = "";
			if ( isset($data['Org_id']) && $data['Org_id'] > 0 )
				$id_filter = " and Org_id <> {$data['Org_id']} ";			
			if ( !isset($data['check_double_inn_cancel']) || !($data['check_double_inn_cancel'] == 2) )
			{
				// проверка на двойников по INN
				$sql = "
					select 
						top 1 
						rtrim(Org_Nick) as Org_Nick,
						count(Org_id) as cnt
					from Org with (nolock)
					where Org_INN = '{$data['Org_INN']}'
					{$id_filter}
					group by Org_Nick
					having count(Org_id) > 0
				";
				$res = $this->db->query($sql);
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ( is_array($sel) && count($sel) > 0 && $sel[0]['cnt'] > 0 )
					{						
						if ( !isset($data['check_double_ogrn_cancel']) || !($data['check_double_ogrn_cancel'] == 2) )
							$sel[0]['Error_Code'] = '777';
						else
							$sel[0]['Error_Code'] = '778';
						$sel[0]['Error_Msg'] = 'ИНН совпадает с ИНН организации: "'.$sel[0]['Org_Nick'].'". ';
						return $sel;
					}
				}
				else
				{
					$sel[0]['Error_Code'] = 1;
					$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ИНН';
					return $sel;
				}
			}
		}

		if (getRegionNick() == 'ufa') {
			if (empty($data['OrgType_SysNick'])) {
				$data['OrgType_SysNick'] = $this->getOrgTypeSysNick($data['OrgType_id']);
			}
			if (!empty($data['OrgType_SysNick']) && in_array($data['OrgType_SysNick'], array('preschool', 'secschool', 'proschool', 'highschool'))) {
				if (empty($data['UAddress_Address'])) {
					return array('Error_Msg' => 'Не заполнено обязательное поле "Юридический адрес"');
				}
				if (empty($data['PAddress_Address'])) {
					return array('Error_Msg' => 'Не заполнено обязательное поле "Фактический адрес"');
				}
				if (empty($data['Org_INN'])) {
					return array('Error_Msg' => 'Не заполнено обязательное поле "ИНН"');
				}
				if (empty($data['Org_KPP'])) {
					return array('Error_Msg' => 'Не заполнено обязательное поле "КПП"');
				}
				// проверка на уникальность пары ИНН/КПП
				$filter = "";
				if (!empty($data['Org_id'])) {
					$filter .= " and Org_id <> :Org_id";
				}
				$query = "
					select top 1
						Org_id,
						Org_Nick
					from
						v_Org (nolock)
					where
						Org_INN = :Org_INN
						and Org_KPP = :Org_KPP
						{$filter}
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (!empty($resp[0]['Org_id'])) {
						return array('Error_Msg' => 'Пара ИНН/КПП совпадает с организацией '.$resp[0]['Org_Nick'].'. Сохранение невозможно.');
					}
				}
			}
		}
		
		// Сохраняем или редактируем адрес
		if (empty($data['fromAPI'])) {
			// PAddress
			if ( !isset($data['PAddress_Address']) ) {
				$data['PAddress_id'] = NULL;
			}
			else {
				if ( !isset($data['PAddress_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :PAddress_id;
	
					exec p_Address_" . $procedure_action . "
						@Server_id = :Server_id,
						@Address_id = @Res output,
						@KLAreaType_id = NULL,
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@PersonSprTerrDop_id =:PersonSprTerrDop_id,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
					select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
				";

				$queryParams = array(
					'PAddress_id' => $data['PAddress_id'],
					'Server_id' => $data['Server_id'],
					'KLCountry_id' => $data['PKLCountry_id'],
					'KLRgn_id' => $data['PKLRGN_id'],
					'KLSubRgn_id' => $data['PKLSubRGN_id'],
					'KLCity_id' => $data['PKLCity_id'],
					'KLTown_id' => $data['PKLTown_id'],
					'KLStreet_id' => $data['PKLStreet_id'],
					'PersonSprTerrDop_id'=>$data['PPersonSprTerrDop_id'],
					'Address_Zip' => $data['PAddress_Zip'],
					'Address_House' => $data['PAddress_House'],
					'Address_Corpus' => $data['PAddress_Corpus'],
					'Address_Flat' => $data['PAddress_Flat'],
					'Address_Address' => $data['PAddress_Address'],
					'pmUser_id' => $data['pmUser_id']
				);
				$res = $this->db->query($query, $queryParams);

				if ( is_object($res) ) {
					$response = $res->result('array');

					if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
						$data['PAddress_id'] = $response[0]['Address_id'];
					}
					else {
						return $response;
					}
				}
				else {
					return false;
				}
			}

			// UAddress
			if (!isset($data['UAddress_Address'])) {
				$data['UAddress_id'] = NULL;
			} else {
				if (!isset($data['UAddress_id'])) {
					$procedure_action = "ins";
				} else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :UAddress_id;
	
					exec p_Address_" . $procedure_action . "
						@Server_id = :Server_id,
						@Address_id = @Res output,
						@KLAreaType_id = NULL,
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@PersonSprTerrDop_id =:PersonSprTerrDop_id,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
					select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
				";

				$queryParams = array(
					'UAddress_id' => $data['UAddress_id'],
					'Server_id' => $data['Server_id'],
					'KLCountry_id' => $data['UKLCountry_id'],
					'KLRgn_id' => $data['UKLRGN_id'],
					'KLSubRgn_id' => $data['UKLSubRGN_id'],
					'KLCity_id' => $data['UKLCity_id'],
					'KLTown_id' => $data['UKLTown_id'],
					'PersonSprTerrDop_id' => $data['UPersonSprTerrDop_id'],
					'KLStreet_id' => $data['UKLStreet_id'],
					'Address_Zip' => $data['UAddress_Zip'],
					'Address_House' => $data['UAddress_House'],
					'Address_Corpus' => $data['UAddress_Corpus'],
					'Address_Flat' => $data['UAddress_Flat'],
					'Address_Address' => $data['UAddress_Address'],
					'pmUser_id' => $data['pmUser_id']
				);
				$res = $this->db->query($query, $queryParams);

				if (is_object($res)) {
					$response = $res->result('array');

					if (isset($response[0]) && strlen($response[0]['Error_Msg']) == 0) {
						$data['UAddress_id'] = $response[0]['Address_id'];
					} else {
						return $response;
					}
				} else {
					return false;
				}
			}
		}

		if ( !isset($data['Org_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@Okogu_id			bigint,
				@Okonh_id			bigint,
				@Org_OKDP			varchar(20),
				@Org_Rukovod		varchar(100) = :Org_Rukovod,
				@Org_Buhgalt		varchar(100) = :Org_Buhgalt,
				@Org_ONMSZCode      varchar(100) = :Org_ONMSZCode,
                @Org_IsEmailFixed	bigint,
                @Org_KBK			varchar(20),
                @Org_pid			bigint,
				@Org_isAccess		bigint,
                @Org_RGN			varchar(15),
                @Org_WorkTime		varchar(64),
                @Org_Www			varchar(100),
				@DepartAffilType_id	bigint;
			set @Res = :Org_id;

			select
				@Okogu_id			= Okogu_id,
				@Okonh_id			= Okonh_id,
				@Org_OKDP			= Org_OKDP,
				@Org_Rukovod		= isnull(:Org_Rukovod,Org_Rukovod),
				@Org_Buhgalt		= isnull(:Org_Buhgalt,Org_Buhgalt),
                @Org_IsEmailFixed	= Org_IsEmailFixed,
                @Org_KBK			= Org_KBK,
                @Org_pid			= Org_pid,
				@Org_isAccess		= ISNULL(Org_isAccess, 1),
                @Org_RGN			= Org_RGN,
                @Org_WorkTime		= Org_WorkTime,
                @Org_Www			= Org_Www,
				@DepartAffilType_id = DepartAffilType_id
            from
                v_org with (nolock)
            where
                org_id = :Org_id;

			exec p_Org_" . $procedure_action . "
				@Server_id = :Server_id,
				@Org_id = @Res {$out},
				@Org_Code = :Org_Code,
				@Org_Nick = :Org_Nick,
				@Org_rid = :Org_rid,
				@Org_nid = :Org_nid,
				@Org_begDate = :Org_begDate,
				@Org_endDate = :Org_endDate,
				@Org_Description = :Org_Description,
				@Org_Name = :Org_Name,
				@Okved_id = :Okved_id,
				@Oktmo_id = :Oktmo_id,
				@Org_INN = :Org_INN,
				@Org_OGRN = :Org_OGRN,
				@Org_Phone = :Org_Phone,
				@Org_Email = :Org_Email,
				@OrgType_id = :OrgType_id,
				@UAddress_id = :UAddress_id,
				@PAddress_id = :PAddress_id,
				@Okopf_id    = :Okopf_id   ,
				@Okogu_id    = @Okogu_id   ,
				@Okonh_id    = @Okonh_id   ,
				@Okfs_id			= :Okfs_id,
				@Org_KPP			= :Org_KPP,
				@Org_OKPO			= :Org_OKPO,
				@Org_OKATO			= :Org_OKATO,
				@Org_OKDP			= @Org_OKDP,
				@Org_Rukovod		= @Org_Rukovod,
				@Org_Buhgalt		= @Org_Buhgalt,
				@Org_StickNick		= :Org_StickNick,
                @Org_IsEmailFixed	= @Org_IsEmailFixed,
                @Org_KBK			= @Org_KBK,
                @Org_pid			= @Org_pid,
                @Org_RGN			= @Org_RGN,
                @Org_ONMSZCode      = @Org_ONMSZCode,
                @Org_WorkTime		= @Org_WorkTime,
                @Org_Www			= @Org_Www,
				@Org_isAccess		= @Org_isAccess,
				@DepartAffilType_id = @DepartAffilType_id,
				@Org_IsNotForSystem = :Org_IsNotForSystem,
				@Org_Marking		= :Org_Marking,
				@Org_f003mcod		= :Org_f003mcod,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as Org_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Org_id' => $data['Org_id'],
			'Server_id' => $data['Server_id'],
			'Org_Code' => $data['Org_Code'],
			'Org_Nick' => $data['Org_Nick'],
			'Org_StickNick' => $data['Org_StickNick'],
			'Org_Description' => $data['Org_Description'],
			'Org_rid' => $data['Org_rid'],
			'Org_nid' => !empty($data['Org_nid']) ? $data['Org_nid'] : $data['Org_rid'],
			'Org_begDate' => $data['Org_begDate'],
			'Org_endDate' => $data['Org_endDate'],
			'Org_Name' => $data['Org_Name'],
			'Okved_id' => $data['Okved_id'],
			'Oktmo_id' => $data['Oktmo_id'],
			'Okopf_id' => $data['Okopf_id'],
			'Okfs_id' => $data['Okfs_id'],
			'Org_INN' => $data['Org_INN'],
			'Org_OKATO' => $data['Org_OKATO'],
			'Org_ONMSZCode' => $data['Org_ONMSZCode'],
			'Org_KPP' => $data['Org_KPP'],
			'Org_OGRN' => $data['Org_OGRN'],
			'Org_OKPO' => $data['Org_OKPO'],
			'Org_Phone' => $data['Org_Phone'],
			'Org_Email' => $data['Org_Email'],
			'OrgType_id' => $data['OrgType_id'],
			'UAddress_id' => $data['UAddress_id'],
			'PAddress_id' => $data['PAddress_id'],
			'KLCountry_id' => $data['KLCountry_id'],
			'KLRGN_id' => $data['KLRGN_id'],
			'KLSubRGN_id' => $data['KLSubRGN_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Org_Rukovod' => isset($data['Org_Rukovod']) && !empty($data['Org_Rukovod']) ? $data['Org_Rukovod'] : null,
			'Org_Buhgalt' => isset($data['Org_Buhgalt']) && !empty($data['Org_Buhgalt']) ? $data['Org_Buhgalt'] : null,
			'Org_IsNotForSystem' => isset($data['Org_IsNotForSystem'])?$data['Org_IsNotForSystem']:null,
			'Org_Marking' => $data['Org_Marking'],
			'Org_f003mcod' => !empty($data['Org_f003mcod']) ? $data['Org_f003mcod'] : null,
		);
		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$response = $res->result('array');

			if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
				$data['Org_id'] = $response[0]['Org_id'];
			}
			else {
				return $response;
			}
		}
		else {
			return false;
		}

		// https://redmine.swan.perm.ru/issues/31050
		if ( !empty($data['OrgStac_Code']) ) {
			// Проверяем код на дубли
			$query = "
				select top 1 OrgStac_id
				from fed.v_OrgStac with (nolock)
				where OrgStac_Code = :OrgStac_Code
					and Org_id != :Org_id
			";
			$resTmp = $this->db->query($query, $data);

			if ( !is_object($resTmp) ) {
				return false;
			}

			$response = $resTmp->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				return array(array('Error_Code' => '347', 'Error_Msg' => 'Указанный код стационарного учреждения уже используется для другой организации'));
			}

			$data['OrgStac_id'] = null;

			// Получаем идентификатор стационарного учреждения для организации
			if ( $procedure_action == 'upd' ) {
				$query = "
					select top 1 OrgStac_id
					from fed.v_OrgStac with (nolock)
					where Org_id = :Org_id
				";
				$resTmp = $this->db->query($query, $data);

				if ( !is_object($resTmp) ) {
					return false;
				}

				$response = $resTmp->result('array');

				if ( is_array($response) && count($response) == 1 && !empty($response[0]['OrgStac_id']) ) {
					$data['OrgStac_id'] = $response[0]['OrgStac_id'];
				}
			}

			$query = "
				declare
					@Reg bigint,
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);

				set @Reg = dbo.getRegion();
				set @Res = :OrgStac_id;

				exec fed.p_OrgStac_" . (!empty($data['OrgStac_id']) ? "upd" : "ins") . "
					@OrgStac_id = @Res output,
					@OrgStac_Code = :OrgStac_Code,
					@Org_id = :Org_id,
					@Region_id = @Reg,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as OrgStac_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			$resTmp = $this->db->query($query, $data);

			if ( !is_object($resTmp) ) {
				return false;
			}

			$response = $resTmp->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				return $response;
			}
		}
		else if ( $procedure_action == 'upd' ) {
			$query = "
				select OrgStac_id
				from fed.v_OrgStac with (nolock)
				where Org_id = :Org_id
			";
			//echo getDebugSQL($query, array('Org_id' => $data['Org_id']));
			$resTmp = $this->db->query($query, array('Org_id' => $data['Org_id']));

			if ( !is_object($resTmp) ) {
				return false;
			}

			$response = $resTmp->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$query = "
					declare
						@ErrCode bigint,
						@ErrMsg varchar(4000);

					exec fed.p_OrgStac_del
						@OrgStac_id = :OrgStac_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				foreach ( $response as $array ) {
					$resTmp = $this->db->query($query, array('OrgStac_id' => $array['OrgStac_id']));
					//echo getDebugSQL($query, array('OrgStac_id' => $array['OrgStac_id']));
					if ( !is_object($resTmp) ) {
						return false;
					}

					$respTmp = $resTmp->result('array');

					if ( !is_array($respTmp) || count($respTmp) == 0 ) {
						return false;
					}
					else if ( !empty($respTmp[0]['Error_Msg']) ) {
						return $respTmp;
					}
				}
			}
		}

		// Сохраняем данные, если редактируется OrgAnatom, OrgDep, OrgFarmacy или OrgSmo
		switch ($data['OrgType_SysNick']) {
			case 'anatom':
				if ( !isset($data['OrgAnatom_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgAnatom_id;

					exec p_OrgAnatom_" . $procedure_action . "
						@Server_id = :Server_id,
						@OrgAnatom_id = @Res output,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgAnatom_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgAnatom_id' => !isset($data['OrgAnatom_id'])?NULL:$data['OrgAnatom_id'],
					'Server_id' => $data['Server_id'],
					'Org_id' => $data['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				$res = $this->db->query($query, $queryParams);
			break;
			
			case 'dep':
				if ( !isset($data['OrgDep_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgDep_id;

					exec p_OrgDep_" . $procedure_action . "
						@Server_id = :Server_id,
						@OrgDep_id = @Res output,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgDep_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgDep_id' => !isset($data['OrgDep_id'])?NULL:$data['OrgDep_id'],
					'Server_id' => $data['Server_id'],
					'Org_id' => $data['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				$res = $this->db->query($query, $queryParams);
			break;
			
			case 'smo':
				if ( !isset($data['OrgSMO_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@LicenceNumber varchar(20),
						@RegLicenceNumber varchar(20),
						@SysNick varchar(20),
						@begDate datetime,
						@endDate datetime,
						@IsTFOMS bigint,
						@FCode bigint,
						@Fedid bigint,
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgSMO_id;

					" . (!empty($data['OrgSMO_id']) ? "
					select top 1
						@LicenceNumber = OrgSMO_LicenceNumber,
						@RegLicenceNumber = OrgSMO_RegLicenceNumber,
						@SysNick = OrgSMO_SysNick,
						@begDate = OrgSmo_begDate,
						@endDate = OrgSmo_endDate,
						@IsTFOMS = OrgSMO_IsTFOMS,
						@FCode = OrgSmo_FCode,
						@Fedid = OrgSmo_Fedid
					from v_OrgSMO with (nolock)
					where OrgSMO_id = @Res" : "") . "

					exec p_OrgSMO_" . $procedure_action . "
						@OrgSMO_id = @Res output,
						@Org_id = :Org_id,
						@OrgSMO_LicenceNumber = @LicenceNumber,
						@OrgSMO_RegLicenceNumber = @RegLicenceNumber,
						@OrgSMO_SysNick = @SysNick,
						@OrgSMO_RegNomC = :OrgSMO_RegNomC,
						@OrgSMO_RegNomN = :OrgSMO_RegNomN,
						@OrgSmo_begDate = @begDate,
						@OrgSmo_endDate = @endDate,
						@KLRGN_id = :KLRGNSmo_id,
						@Orgsmo_f002smocod = :Orgsmo_f002smocod,
						@OrgSMO_isDMS = :OrgSMO_isDMS,
						@OrgSMO_IsTFOMS = @IsTFOMS,
						@OrgSMO_FCode = @FCode,
						--@OrgSMO_Fedid = @Fedid,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgSMO_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgSMO_id' => !isset($data['OrgSMO_id'])?NULL:$data['OrgSMO_id'],
					'Org_id' => $data['Org_id'],
					'OrgSMO_RegNomC' => $data['OrgSMO_RegNomC'],
					'OrgSMO_RegNomN' => $data['OrgSMO_RegNomN'],
					'OrgSMO_isDMS' => $data['OrgSMO_isDMS'],
					'KLRGNSmo_id' => $data['KLRGNSmo_id'],
					'Orgsmo_f002smocod' => $data['Orgsmo_f002smocod'],
					'pmUser_id' => $data['pmUser_id']
				);

				$res = $this->db->query($query, $queryParams);
			break;

			case 'farm':
				if ( !isset($data['OrgFarmacy_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgFarmacy_id;

					exec p_OrgFarmacy_" . $procedure_action . "
						@OrgFarmacy_id = @Res output,
						@Org_id = :Org_id,
						@OrgFarmacy_ACode = :OrgFarmacy_ACode,
						@OrgFarmacy_HowGo = :OrgFarmacy_HowGo,
						@OrgFarmacy_IsEnabled = :OrgFarmacy_IsEnabled,
						@OrgFarmacy_IsFedLgot = :OrgFarmacy_IsFedLgot,
						@OrgFarmacy_IsRegLgot = :OrgFarmacy_IsRegLgot,
						@OrgFarmacy_IsNozLgot = :OrgFarmacy_IsNozLgot,
						@OrgFarmacy_IsNarko = :OrgFarmacy_IsNarko,
						@OrgFarmacy_IsFarmacy = :OrgFarmacy_IsFarmacy,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgFarmacy_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgFarmacy_id' => !isset($data['OrgFarmacy_id'])?NULL:$data['OrgFarmacy_id'],
					'Org_id' => $data['Org_id'],
					'OrgFarmacy_ACode' => $data['OrgFarmacy_ACode'],
					'OrgFarmacy_HowGo' => $data['OrgFarmacy_HowGo'],
					'OrgFarmacy_IsEnabled' => $data['OrgFarmacy_IsEnabled'],
					'OrgFarmacy_IsFedLgot' => $data['OrgFarmacy_IsFedLgot'],
					'OrgFarmacy_IsRegLgot' => $data['OrgFarmacy_IsRegLgot'],
					'OrgFarmacy_IsNozLgot' => $data['OrgFarmacy_IsNozLgot'],
					'OrgFarmacy_IsNarko' => $data['OrgFarmacy_IsNarko'],
					'OrgFarmacy_IsFarmacy' => $data['OrgFarmacy_IsFarmacy'],
					'pmUser_id' => $data['pmUser_id']
				);

				// echo getDebugSql($query, $queryParams); die();
				$res = $this->db->query($query, $queryParams);
			break;
			
			case 'bank':
				if ( !isset($data['OrgBank_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgBank_id;

					exec p_OrgBank_" . $procedure_action . "
						@OrgBank_id = @Res output,
						@Org_id = :Org_id,
						@OrgBank_KSchet = :OrgBank_KSchet,
						@OrgBank_BIK = :OrgBank_BIK,
						@Okved_id = :Okved_id,
						@pmUser_id = :pmUser_id,
						@Server_id = :Server_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgBank_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgBank_id' => !isset($data['OrgBank_id'])?NULL:$data['OrgBank_id'],
					'Org_id' => $data['Org_id'],
					'OrgBank_KSchet' => $data['OrgBank_KSchet'],
					'OrgBank_BIK' => $data['OrgBank_BIK'],
					'Okved_id' => $data['Okved_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				// echo getDebugSql($query, $queryParams); die();
				$res = $this->db->query($query, $queryParams);
			break;
			
			default:
				break;
		}
		if ( is_object($res) ) {
			$response = $res->result('array');
			$response[0]['Org_id'] = $data['Org_id'];
			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	* сохраняет/обновляет организацию пришедшую в XML файле, без проверок.
	*/
	function saveOrgXml($data) {
		if (!empty($data['Org_id'])) {
			$proc = 'upd';
		} else {
			$data['Org_id'] = null;
			if ( empty($data['OrgType_id']) ) {
				$data['OrgType_id'] = 19;
			}
			$proc = 'ins';
		}

		if (!empty($data['Okved_Code'])) {
			$data['Okved_id'] = $this->getFirstResultFromQuery("select Okved_id from Okved with(nolock) where Okved_Code = :Okved_Code", $data);
		}

		if (!empty($data['Okopf_Code'])) {
			$data['Okopf_id'] = $this->getFirstResultFromQuery("select Okopf_id from Okopf with(nolock) where Okopf_Code = :Okopf_Code", $data);
		}

		foreach( array('Org_id', 'Org_Code', 'Org_Nick', 'Org_StickNick', 'Org_begDate', 'Org_endDate', 'Okopf_id', 'Okogu_id', 'Okonh_id', 'Okfs_id', 'Org_Name', 'Okved_id', 'Org_INN', 'Org_OGRN', 'Org_KPP', 'Org_OKPO', 'Org_OKATO', 'Org_OKDP', 'Org_Phone', 'Org_Email', 'Org_Rukovod', 'Org_Buhgalt', 'UAddress_id', 'PAddress_id', 'Org_isAccess', 'KLCountry_id', 'KLRGN_id', 'KLSubRGN_id', 'KLCity_id', 'KLTown_id', 'OrgType_id') as $row) {
			if (empty($data[$row])) {
				$data[$row] = null;
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :Org_id;

			exec p_Org_" . $proc . "
				@Server_id = 1,
				@Org_id = @Res output,
				@Org_Code = :Org_Code,
				@Org_Nick = :Org_Nick,
				@Org_StickNick = :Org_StickNick,
				@Org_begDate = :Org_begDate,
				@Org_endDate = :Org_endDate,
				@Okopf_id = :Okopf_id,
				@Okogu_id = :Okogu_id,
				@Okonh_id = :Okonh_id,
				@Okfs_id = :Okfs_id,
				@OrgType_id = :OrgType_id,
				@Org_Name = :Org_Name,
				@Okved_id = :Okved_id,
				@Org_INN = :Org_INN,
				@Org_OGRN = :Org_OGRN,
				@Org_KPP = :Org_KPP,
				@Org_OKPO = :Org_OKPO,
				@Org_OKATO = :Org_OKATO,
				@Org_OKDP = :Org_OKDP,
				@Org_Phone = :Org_Phone,
				@Org_Email = :Org_Email,
				@Org_Rukovod = :Org_Rukovod,
				@Org_Buhgalt = :Org_Buhgalt,
				@UAddress_id = :UAddress_id,
				@PAddress_id = :PAddress_id,
				@Org_isAccess = :Org_isAccess,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as Org_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		//echo getDebugSQL($query, $data);die;
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$response = $res->result('array');

			//Проставляем Org_Code = Org_id
			if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
				$data['Org_id'] = $response[0]['Org_id'];
				$data['Org_Code'] = substr($response[0]['Org_id'], -4,4) ;
				if ($proc == 'ins') {
					$res = $this->db->query("
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = :Org_id;

						exec p_Org_upd
							@Server_id = 1,
							@Org_id = @Res output,
							@Org_Code = :Org_Code,
							@Org_Nick = :Org_Nick,
							@Org_begDate = :Org_begDate,
							@Org_endDate = :Org_endDate,
							@Okopf_id = :Okopf_id,
							@Okogu_id = :Okogu_id,
							@Okonh_id = :Okonh_id,
							@Okfs_id = :Okfs_id,
							@OrgType_id = :OrgType_id,
							@Org_Name = :Org_Name,
							@Okved_id = :Okved_id,
							@Org_INN = :Org_INN,
							@Org_OGRN = :Org_OGRN,
							@Org_KPP = :Org_KPP,
							@Org_OKPO = :Org_OKPO,
							@Org_OKATO = :Org_OKATO,
							@Org_OKDP = :Org_OKDP,
							@Org_Phone = :Org_Phone,
							@Org_Email = :Org_Email,
							@Org_Rukovod = :Org_Rukovod,
							@Org_Buhgalt = :Org_Buhgalt,
							@UAddress_id = :UAddress_id,
							@PAddress_id = :PAddress_id,
							@Org_isAccess = :Org_isAccess,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;

						select @Res as Org_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					", $data);
				}

				$response =  $res->result('array');
			}

			return $response;

		} else {
			return false;
		}
	}


	/**
	* сравнивает и обновляет адрес для добавляемой организации
	*/
	function saveOrgXmlAddress($data) {

		//Добавляем все коды КЛАДР до 13 знаков нулями, улицу - до 17-ти
		$queryParams = array(
			'Org_id' => !empty($data['Org_id'])?:null,
			'pmUser_id' => !empty($data['pmUser_id'])?$data['pmUser_id']:null,
			'UAddress_id' => !empty($data['UAddress_id'])?$data['UAddress_id']:null,
			'PAddress_id' => !empty($data['PAddress_id'])?$data['PAddress_id']:null,
			'Address_Zip' => !empty($data['INDEKS'])?$data['INDEKS']:null,
			'Address_House' => !empty($data['DOM'])?$data['DOM']:null,
			'Address_Flat' => !empty($data['KVART'])?$data['KVART']:null,
			'REGION' => !empty($data['REGION'])?str_pad($data['REGION'], 13, 0):null,
			'RAION' => !empty($data['RAION'])?str_pad($data['RAION'], 13, 0):null,
			'GOROD' => !empty($data['GOROD'])?str_pad($data['GOROD'], 13, 0):null,
			'NASPUNKT' => !empty($data['NASPUNKT'])?str_pad($data['NASPUNKT'], 13, 0):null,
			'STREET' => !empty($data['STREET'])?str_pad($data['STREET'], 17, 0):null
		);
		$queryParams['xml_loader'] = true;

		//Тащим идентификаторы Региона, района, города и улицы
		$KLRgnQuery = $this->getFirstRowFromQuery("select top 1 * from KLArea with(nolock) where KLAdr_Code = ".$queryParams['REGION']);
		$queryParams['KLRgn_id'] = $KLRgnQuery['KLArea_id'];
		$queryParams['KLCountry_id'] = $KLRgnQuery['KLCountry_id'];
		$queryParams['Server_id'] = $KLRgnQuery['Server_id'];
		$queryParams['KLSubRgn_id'] = $this->getFirstResultFromQuery("select top 1 KLArea_id from KLArea with(nolock) where KLAdr_Code = ".$queryParams['RAION']);
		$queryParams['KLCity_id'] = $this->getFirstResultFromQuery("select top 1 KLArea_id from KLArea with(nolock) where KLAdr_Code = ".$queryParams['GOROD']);
		$queryParams['KLTown_id'] = $this->getFirstResultFromQuery("select top 1 KLArea_id from KLArea with(nolock) where KLAdr_Code = ".$queryParams['NASPUNKT']);
		$queryParams['KLStreet_id'] = $this->getFirstResultFromQuery("select top 1 KLStreet_id from KLStreet with(nolock) where KLAdr_Code = ".$queryParams['STREET']);

		//Сохраняем/обновляем адреса организации
		$UAddress = $this->saveAddress('UAddress_id', $queryParams);
		$response['UAddress_id'] = (is_array($UAddress) && !empty($UAddress[0]['Address_id']))?$UAddress[0]['Address_id']:null;

		$PAddress = $this->saveAddress('PAddress_id', $queryParams);
		$response['PAddress_id'] = (is_array($PAddress) && !empty($PAddress[0]['Address_id']))?$PAddress[0]['Address_id']:null;

		return $response;
	}


	/**
	* Сохраняет аддрес в зависимости от типа
	*/
	function saveAddress($type, $data) {
		$xml_loader = !empty($data['xml_loader']);//при загрузке организаций в ImportSchema.php -> importXmlOrg

		if (!empty($data[$type])) {
			$proc = 'upd';
			$queryParams = $this->getFirstRowFromQuery("
				select top 1
					Server_id,
					Address_id,
					KLAreaType_id,
					KLCountry_id,
					KLRgn_id,
					KLSubRgn_id,
					KLCity_id,
					KLTown_id,
					KLStreet_id,
					Address_Zip,
					Address_House,
					Address_Corpus,
					Address_Flat,
					Address_Address
				from
					Address (nolock)
				where
					Address_id = :{$type}
			", $data);

			foreach ($data as $key => &$value) {
				if (empty($value)) {
					unset($data[$key]);
				}
			}

			$data = array_merge($queryParams, $data);
		}

		if (empty($data['Address_id'])){
			$proc = 'ins';
			$data['Address_id'] = null;
		}

		foreach ( array('Server_id', 'Address_id', 'KLCountry_id', 'KLRgn_id', 'KLSubRgn_id', 'KLCity_id', 'KLTown_id', 'KLStreet_id', 'Address_Zip', 'Address_House', 'Address_Corpus', 'Address_Flat', 'Address_Address') as $row) {
			if (empty($data[$row])){
				$data[$row] = null;
			}
		}

		if($xml_loader) $data['Address_Address'] = null;//переформируем значение в хр.процедуре, чтобы изменения в полях адреса отображались на формах

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :Address_id;

			exec p_Address_{$proc}
				@Server_id = 1,
				@Address_id = @Res output,
				@KLAreaType_id = NULL,
				@KLCountry_id = :KLCountry_id,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				@Address_Zip = :Address_Zip,
				@Address_House = :Address_House,
				@Address_Corpus = :Address_Corpus,
				@Address_Flat = :Address_Flat,
				@Address_Address = :Address_Address,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
		";

		//echo getDebugSQL($query, $data);die;
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	* Сохраняет ЛПУ
	*/
	function saveLpu($data) {
		$procedure_action = '';

		// PAddress
		if ( !isset($data['PAddress_Address']) ) {
			$data['PAddress_id'] = NULL;
		}
		else {
			if ( !isset($data['PAddress_id']) ) {
				$procedure_action = "ins";
			}
			else {
				$procedure_action = "upd";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :PAddress_id;

				exec p_Address_" . $procedure_action . "
					@Server_id = :Server_id,
					@Address_id = @Res output,
					@KLAreaType_id = NULL,
					@KLCountry_id = :KLCountry_id,
					@KLRgn_id = :KLRgn_id,
					@KLSubRgn_id = :KLSubRgn_id,
					@KLCity_id = :KLCity_id,
					@KLTown_id = :KLTown_id,
					@KLStreet_id = :KLStreet_id,
					@Address_Zip = :Address_Zip,
					@Address_House = :Address_House,
					@Address_Corpus = :Address_Corpus,
					@Address_Flat = :Address_Flat,
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
			";

			$queryParams = array(
				'PAddress_id' => $data['PAddress_id'],
				'Server_id' => $data['Server_id'],
				'KLCountry_id' => $data['PKLCountry_id'],
				'KLRgn_id' => $data['PKLRGN_id'],
				'KLSubRgn_id' => $data['PKLSubRGN_id'],
				'KLCity_id' => $data['PKLCity_id'],
				'KLTown_id' => $data['PKLTown_id'],
				'KLStreet_id' => $data['PKLStreet_id'],
				'Address_Zip' => $data['PAddress_Zip'],
				'Address_House' => $data['PAddress_House'],
				'Address_Corpus' => $data['PAddress_Corpus'],
				'Address_Flat' => $data['PAddress_Flat'],
				'Address_Address' => $data['PAddress_Address'],
				'pmUser_id' => $data['pmUser_id']
			);
			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$response = $res->result('array');

				if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
					$data['PAddress_id'] = $response[0]['Address_id'];
				}
				else {
					return $response;
				}
			}
			else {
				return false;
			}
		}

		// UAddress
		if ( !isset($data['UAddress_Address']) ) {
			$data['UAddress_id'] = NULL;
		}
		else {
			if ( !isset($data['UAddress_id']) ) {
				$procedure_action = "ins";
			}
			else {
				$procedure_action = "upd";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :UAddress_id;

				exec p_Address_" . $procedure_action . "
					@Server_id = :Server_id,
					@Address_id = @Res output,
					@KLAreaType_id = NULL,
					@KLCountry_id = :KLCountry_id,
					@KLRgn_id = :KLRgn_id,
					@KLSubRgn_id = :KLSubRgn_id,
					@KLCity_id = :KLCity_id,
					@KLTown_id = :KLTown_id,
					@KLStreet_id = :KLStreet_id,
					@Address_Zip = :Address_Zip,
					@Address_House = :Address_House,
					@Address_Corpus = :Address_Corpus,
					@Address_Flat = :Address_Flat,
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
			";

			$queryParams = array(
				'UAddress_id' => $data['UAddress_id'],
				'Server_id' => $data['Server_id'],
				'KLCountry_id' => $data['UKLCountry_id'],
				'KLRgn_id' => $data['UKLRGN_id'],
				'KLSubRgn_id' => $data['UKLSubRGN_id'],
				'KLCity_id' => $data['UKLCity_id'],
				'KLTown_id' => $data['UKLTown_id'],
				'KLStreet_id' => $data['UKLStreet_id'],
				'Address_Zip' => $data['UAddress_Zip'],
				'Address_House' => $data['UAddress_House'],
				'Address_Corpus' => $data['UAddress_Corpus'],
				'Address_Flat' => $data['UAddress_Flat'],
				'Address_Address' => $data['UAddress_Address'],
				'pmUser_id' => $data['pmUser_id']
			);

			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$response = $res->result('array');

				if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
					$data['UAddress_id'] = $response[0]['Address_id'];
				}
				else {
					return $response;
				}
			}
			else {
				return false;
			}
		}

		if ( !isset($data['Org_id']) ) {
			$procedure_action = "ins";
			$data['KLCountry_id'] = null;
			$data['KLRGN_id'] = null;
			$data['KLSubRGN_id'] = null;
			$data['KLCity_id'] = null;
			$data['KLTown_id'] = null;
			$data['Org_OGRN'] = null;
			$data['Okved_id'] = null;
			$data['Okopf_id'] = null;
			$data['Okogu_id'] = null;
			$data['Okonh_id'] = null;
			$data['Okfs_id'] = null;
			$data['Org_KPP'] = null;
			$data['Org_OKPO'] = null;
			$data['Org_OKATO'] = null;
			$data['Org_OKDP'] = null;
			$data['Org_Rukovod'] = null;
			$data['Org_Buhgalt'] = null;
			$data['Org_isAccess'] = 1;
		}
		else {
			// получаем данные организации, которые не передаются с формы
			$query = "
				select 
					*
				from
					Org with(nolock)
				where
					Org_id = ?
			";
			$res = $this->db->query($query, array($data['Org_id']));

			if ( is_object($res) ) {
				$sel = $res->result('array');
				if ( count($sel) == 1 )
				{
					$data['KLCountry_id'] = $sel[0]['KLCountry_id'];
					$data['KLRGN_id'] = $sel[0]['KLRGN_id'];
					$data['KLSubRGN_id'] = $sel[0]['KLSubRGN_id'];
					$data['KLCity_id'] = $sel[0]['KLCity_id'];
					$data['KLTown_id'] = $sel[0]['KLTown_id'];
					$data['Org_OGRN'] = $sel[0]['Org_OGRN'];
					$data['Okved_id'] = $sel[0]['Okved_id'];
					$data['Okopf_id'] = $sel[0]['Okopf_id'];
					$data['Okogu_id'] = $sel[0]['Okogu_id'];
					$data['Okonh_id'] = $sel[0]['Okonh_id'];
					$data['Okfs_id'] = $sel[0]['Okfs_id'];
					$data['Org_KPP'] = $sel[0]['Org_KPP'];
					$data['Org_OKPO'] = $sel[0]['Org_OKPO'];
					$data['Org_OKATO'] = $sel[0]['Org_OKATO'];
					$data['Org_OKDP'] = $sel[0]['Org_OKDP'];
					$data['Org_Rukovod'] = $sel[0]['Org_Rukovod'];
					$data['Org_Buhgalt'] = $sel[0]['Org_Buhgalt'];
					$data['Org_isAccess'] = $sel[0]['Org_isAccess'];
					$data['Org_IsNotForSystem'] = $sel[0]['Org_IsNotForSystem'];
				}
				else
					return false;
			}
			else {
				return false;
			}			
			$procedure_action = "upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :Org_id;

			exec p_Org_" . $procedure_action . "
				@Server_id = :Server_id,
				@Org_id = @Res output,
				@Org_Code = :Org_Code,
				@Org_Nick = :Org_Nick,
				@Okopf_id = :Okopf_id,
				@Okogu_id = :Okogu_id,
				@Okonh_id = :Okonh_id,
				@Okfs_id = :Okfs_id,
				@Org_Name = :Org_Name,
				@Okved_id = :Okved_id,
				@Org_INN = :Org_INN,
				@Org_OGRN = :Org_OGRN,
				@Org_KPP = :Org_KPP,
				@Org_OKPO = :Org_OKPO,
				@Org_OKATO = :Org_OKATO,
				@Org_OKDP = :Org_OKDP,
				@Org_Phone = :Org_Phone,
				@Org_Email = :Org_Email,
				@Org_Rukovod = :Org_Rukovod,
				@Org_Buhgalt = :Org_Buhgalt,
				@UAddress_id = :UAddress_id,
				@PAddress_id = :PAddress_id,
				
				@Org_isAccess = :Org_isAccess,
				
				@KLCountry_id = :KLCountry_id,
				@KLRGN_id = :KLRGN_id,
				@KLSubRGN_id = :KLSubRGN_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@Org_IsNotForSystem = :Org_IsNotForSystem,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as Org_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Org_id' => $data['Org_id'],
			'Server_id' => $data['Server_id'],
			'Org_Code' => $data['Org_Code'],
			'Org_Nick' => $data['Org_Nick'],
			'Org_Name' => $data['Org_Name'],
			'Okved_id' => $data['Okved_id'],
			'Org_INN' => $data['Org_INN'],
			'Org_OGRN' => $data['Org_OGRN'],
			'Org_Phone' => $data['Org_Phone'],
			'Org_Email' => $data['Org_Email'],
			'UAddress_id' => $data['UAddress_id'],
			'PAddress_id' => $data['PAddress_id'],			
			'pmUser_id' => $data['pmUser_id'],
			'Okopf_id' => $data['Okopf_id'],
			'Okogu_id' => $data['Okogu_id'],
			'Okonh_id' => $data['Okonh_id'],
			'Okfs_id' => $data['Okfs_id'],
			'Org_KPP' => $data['Org_KPP'],
			'Org_OKPO' => $data['Org_OKPO'],
			'Org_OKATO' => $data['Org_OKATO'],
			'Org_OKDP' => $data['Org_OKDP'],
			'Org_Rukovod' => $data['Org_Rukovod'],
			'Org_Buhgalt' => $data['Org_Buhgalt'],
			'Org_isAccess' => $data['Org_isAccess'],
			'Org_IsNotForSystem' => isset($data['Org_IsNotForSystem'])?$data['Org_IsNotForSystem']:null
		);

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$response = $res->result('array');

			if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
				$data['Org_id'] = $response[0]['Org_id'];
			}
			else {
				return $response;
			}
		}
		else {
			return false;
		}

		// Сохраняем данные конкретно ЛПУ
		// Проверяем, есть ли уже ЛПУ
		$query = "
			select 
				top 1 
				*,
				convert(varchar,cast(Lpu_DogDate as datetime),112) as Lpu_DogDate1,
				convert(varchar,cast(Lpu_RegDate as datetime),112) as Lpu_RegDate1,
				convert(varchar,cast(Lpu_begDate as datetime),112) as Lpu_begDate1,
				convert(varchar,cast(Lpu_endDate as datetime),112) as Lpu_endDate1,
				convert(varchar,cast(Lpu_dloBegDate as datetime),112) as Lpu_dloBegDate1,
				convert(varchar,cast(Lpu_dloEndDate as datetime),112) as Lpu_dloEndDate1,
				convert(varchar,cast(Lpu_OmsBegDate as datetime),112) as Lpu_OmsBegDate1,
				convert(varchar,cast(Lpu_OmsEndDate as datetime),112) as Lpu_OmsEndDate1
			from
				Lpu with(nolock)
			where
				Org_id = ?
		";
		$res = $this->db->query($query, array($data['Org_id']));

		if ( is_object($res) ) {
			$sel = $res->result('array');
			if ( count($sel) == 1 )
			{
				$procedure_action = "upd";
				$data['Lpu_id'] = $sel[0]['Lpu_id'];
				$data['Lpu_SysNick'] = $sel[0]['Lpu_SysNick'];
				$data['Lpu_RegNomC2'] = $sel[0]['Lpu_RegNomC2'];
				$data['Lpu_RegNomN2'] = $sel[0]['Lpu_RegNomN2'];
				$data['Lpu_Ouz'] = $sel[0]['Lpu_Ouz'];
				$data['Lpu_IsEnable'] = $sel[0]['Lpu_IsEnable'];
				$data['Lpu_IsOblast'] = $sel[0]['Lpu_IsOblast'];
				$data['Lpu_IsInDir'] = $sel[0]['Lpu_IsInDir'];
				$data['Lpu_Otv'] = $sel[0]['Lpu_Otv'];
				$data['KLAreaType_id'] = $sel[0]['KLAreaType_id'];
				$data['Org_lid'] = $sel[0]['Org_lid'];
				//$data['VedPrin_id'] = $sel[0]['VedPrin_id'];
				$data['LpuLevel_id'] = $sel[0]['LpuLevel_id'];
				$data['Lpu_PensRegNum'] = $sel[0]['Lpu_PensRegNum'];
				$data['Lpu_DogNum'] = $sel[0]['Lpu_DogNum'];
				$data['Lpu_DogDate'] = $sel[0]['Lpu_DogDate1'];
				$data['Lpu_RegNum'] = $sel[0]['Lpu_RegNum'];
				$data['Lpu_RegDate'] = $sel[0]['Lpu_RegDate1'];
				$data['Lpu_begDate'] = $sel[0]['Lpu_begDate1'];
				$data['Lpu_endDate'] = $sel[0]['Lpu_endDate1'];
				$data['Lpu_dloBegDate'] = $sel[0]['Lpu_dloBegDate1'];
				$data['Lpu_dloEndDate'] = $sel[0]['Lpu_dloEndDate1'];
				$data['Lpu_OmsBegDate'] = $sel[0]['Lpu_OmsBegDate1'];
				$data['Lpu_OmsEndDate'] = $sel[0]['Lpu_OmsEndDate1'];
				$data['Lpu_AmountPeople'] = $sel[0]['Lpu_AmountPeople'];
				$data['Lpu_VizitFact'] = $sel[0]['Lpu_VizitFact'];
				$data['Lpu_KoikiFact'] = $sel[0]['Lpu_KoikiFact'];
				$data['Lpu_FarPoint'] = $sel[0]['Lpu_FarPoint'];
				$data['Lpu_NaselGor'] = $sel[0]['Lpu_NaselGor'];
				$data['Lpu_NaselSel'] = $sel[0]['Lpu_NaselSel'];
				$data['Lpu_NaselChild'] = $sel[0]['Lpu_NaselChild'];
				$data['Lpu_NaselVzr'] = $sel[0]['Lpu_NaselVzr'];
				$data['Lpu_NaselRab'] = $sel[0]['Lpu_NaselRab'];
			}
			else
			{
				$procedure_action = "ins";
				$data['Lpu_id'] = null;
				$data['Lpu_SysNick'] = null;
				$data['Lpu_RegNomC2'] = null;
				$data['Lpu_RegNomN2'] = null;
				$data['Lpu_Ouz'] = null;
				$data['Lpu_IsEnable'] = null;
				$data['Lpu_IsOblast'] = null;
				$data['Lpu_IsInDir'] = null;
				$data['Lpu_Otv'] = null;
				$data['KLAreaType_id'] = null;
				$data['Org_lid'] = null;
				//$data['VedPrin_id'] = null;
				$data['LpuLevel_id'] = null;
				$data['Lpu_PensRegNum'] = null;
				$data['Lpu_DogNum'] = null;
				$data['Lpu_DogDate'] = null;
				$data['Lpu_RegNum'] = null;
				$data['Lpu_RegDate'] = null;
				$data['Lpu_begDate'] = null;
				$data['Lpu_endDate'] = null;
				$data['Lpu_dloBegDate'] = null;
				$data['Lpu_dloEndDate'] = null;
				$data['Lpu_OmsBegDate'] = null;
				$data['Lpu_OmsEndDate'] = null;
				$data['Lpu_AmountPeople'] = null;
				$data['Lpu_VizitFact'] = null;
				$data['Lpu_KoikiFact'] = null;
				$data['Lpu_FarPoint'] = null;
				$data['Lpu_NaselGor'] = null;
				$data['Lpu_NaselSel'] = null;
				$data['Lpu_NaselChild'] = null;
				$data['Lpu_NaselVzr'] = null;
				$data['Lpu_NaselRab'] = null;
			}
		}
		else {
			return false;
		}				

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :Lpu_id;

			exec p_Lpu_" . $procedure_action . "
				@Server_id = :Server_id,
				@Lpu_id = @Res output,
				@Org_id = :Org_id,
				@LpuType_id = :LpuType_id,
				@Lpu_RegNomC = :Lpu_RegNomC,
				@Lpu_RegNomN = :Lpu_RegNomN,
				@Lpu_Ouz = :Lpu_Ouz,
				@Lpu_IsOMS = :Lpu_IsOMS,
				@Lpu_SysNick = :Lpu_SysNick,
				@Lpu_RegNomC2 = :Lpu_RegNomC2,
				@Lpu_RegNomN2 = :Lpu_RegNomN2,
				@Lpu_IsEnable = :Lpu_IsEnable,
				@Lpu_IsOblast = :Lpu_IsOblast,
				@Lpu_IsInDir = :Lpu_IsInDir,
				@Lpu_Otv = :Lpu_Otv,
				@KLAreaType_id = :KLAreaType_id,
				@Org_lid = :Org_lid,
				@LpuLevel_id = :LpuLevel_id,
				@Lpu_PensRegNum = :Lpu_PensRegNum,
				@Lpu_DogNum = :Lpu_DogNum,
				@Lpu_DogDate = :Lpu_DogDate,
				@Lpu_RegNum = :Lpu_RegNum,
				@Lpu_RegDate = :Lpu_RegDate,
				@Lpu_begDate = :Lpu_begDate,
				@Lpu_endDate = :Lpu_endDate,
				@Lpu_dloBegDate = :Lpu_dloBegDate,
				@Lpu_dloEndDate = :Lpu_dloEndDate,
				@Lpu_OmsBegDate = :Lpu_OmsBegDate,
				@Lpu_OmsEndDate = :Lpu_OmsEndDate,
				@Lpu_AmountPeople = :Lpu_AmountPeople,
				@Lpu_VizitFact = :Lpu_VizitFact,
				@Lpu_KoikiFact = :Lpu_KoikiFact,
				@Lpu_FarPoint = :Lpu_FarPoint,
				@Lpu_NaselGor = :Lpu_NaselGor,
				@Lpu_NaselSel = :Lpu_NaselSel,
				@Lpu_NaselChild = :Lpu_NaselChild,
				@Lpu_NaselVzr = :Lpu_NaselVzr,
				@Lpu_NaselRab = :Lpu_NaselRab,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as Lpu_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = $data;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}

	}

	/**
	 *  Получение идентифкатора организации
	 */
	function getBaseOrgId($data) {
		$table = 'Org';

		switch ($data['OrgType']) {
			case 'anatom':
				$table = 'OrgAnatom';
			break;

			case 'dep':
				$table = 'OrgDep';
			break;

			case 'farm':
				$table = 'OrgFarmacy';
			break;
		}

		$query = "
			select top 1 Org_id
			from " . $table . " with (nolock)
			where " . $table . "_id = :Org_id
		";
		$res = $this->db->query($query, array('Org_id' => $data['Org_id']));

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка организаций
	 */
	function getOrgForContragents($data) {
		$params = array();
		$filter = "(1=1)";

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$params['Lpu_id'] = $data['Lpu_id'];

		if (isset($data['Name'])) {
			$filter = $filter." and Org.Org_Name like ('%'+:OrgName+'%')";
			$params['OrgName'] = $data['Name'];
		}

		if (!empty($data['Type'])) {
			$filter .= " and Org.OrgType_id = :OrgType_id";
			$params['OrgType_id'] = $data['Type'];
		}

		if (!empty($data['endDate'])) {
			$filter .= " and (Org.Org_endDate is null or cast(Org.Org_endDate as date) >= cast(:endDate as date))";
			$params['endDate'] = $data['endDate'];
		}

		$query = "
			Select
				Org.Org_id,
				RTrim(Org.Org_Nick) as Org_Nick,
				RTrim(Org.Org_Name) as Org_Name,
				ot.OrgType_SysNick as Org_Type,
				RTrim(UAddress.Address_Address) as UAddress_Address,
				RTrim(PAddress.Address_Address) as PAddress_Address,
				case when ISNULL(Org.Org_IsAccess,1) = 2 then 'true' else 'false' end as Org_IsAccess,
				Contragent.Contragent_Code
			from v_Org Org with (nolock)
			left join v_OrgType ot with (nolock) on Org.OrgType_id = ot.OrgType_id
			left join v_Address UAddress with (nolock) on UAddress.Address_id = Org.UAddress_id
			left join v_Address PAddress with (nolock) on PAddress.Address_id = Org.PAddress_id
			outer apply (
				select top 1
					c.Contragent_Code
				from
					Contragent c with(nolock)
				where
					c.Org_id = Org.Org_id and
					isnull(c.Lpu_id, 0) = isnull(:Lpu_id, 0)
			) as Contragent
			where
				{$filter}
			order by Org.Org_Name
		";
		$r = $this->db->query($query, $params);
		if (is_object($r)) {
			$r = $r->result('array');
			return $r;
		} else {
			return false;
		}
	}

	/**
	 * Получение организации по номеру лицензии
	 */
	function getOrgByLicenceRegNum($num) {
		if (!empty($num)) {
			$query = "
				select
					o.Org_id,
					rtrim(o.Org_Name) as Org_Name
				from
					Org o with (nolock)
					inner join OrgLicence ol with (nolock) on ol.Org_id = o.Org_id
				where
					ol.OrgLicence_RegNum = :OrgLicence_RegNum;
			";
			$r = $this->db->query($query, array(
				'OrgLicence_RegNum' => $num
			));
			if (is_object($r)) {
				$r = $r->result('array');
				return $r;
			}
		}
		return false;
	}

	/**
	 * Получение ОГРН организации
	 */
	function getOrgOGRN($data) {
		$params = array('Org_id' => $data['Org_id']);

		$query = "
			select top 1 O.Org_OGRN
			from v_Org O with(nolock)
			where O.Org_id = :Org_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при запросе ОГРН'));
		}
		$response = $result->result('array');
		if (count($response) == 0) {
			return array(array('Error_Msg' => 'Не найдена указанная организация'));
		}
		return $response;
	}

	/**
	 * Получение списка связанных, правопреемником которых является текущая МО
	 */
	function getLinkedLpuIdList($data) {
		if ( empty($data['Lpu_id']) ) {
			return array(0);
		}

		$methodResponse = array($data['Lpu_id']);

		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select Lpu_id
			from v_Lpu with (nolock)
			where Lpu_pid = :Lpu_id
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return $methodResponse;
		}

		$queryResponse = $result->result('array');

		if ( is_array($queryResponse) ) {
			foreach ( $queryResponse as $row ) {
				$methodResponse[] = $row['Lpu_id'];
			}
		}

		return $methodResponse;
	}

	/**
	 * Получение признака, что у текущей МО есть правопреемник
	 */
	function getLpuIsTransit($data) {
		$methodResponse = 0;

		if ( empty($data['Lpu_id']) ) {
			return $methodResponse;
		}

		$queryParams = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select top 1 Lpu_pid
			from v_Lpu with (nolock)
			where Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$queryResponse = $result->result('array');

			if ( is_array($queryResponse) && count($queryResponse) > 0 && !empty($queryResponse[0]['Lpu_pid'])) {
				$methodResponse = 1;
			}
		}

		return $methodResponse;
	}

	/**
	 * Получение идентификатора организации минздрава
	 */
	function getMinzdravOrgId() {
		$mzorg_id = $this->getFirstResultFromQuery("select dbo.GetMinzdravDloOrgId() as Org_id");
		return !empty($mzorg_id) ? $mzorg_id : null;
	}

	/**
	 * Функция проверки организации на использование в информационном обмене с федеральным порталом
	 * @param  string $actionName 	Название операции
	 */
	function checkOrgHasMIID($data, $actionName)
	{
		$filter = " AND OP.Org_id = :Org_id";
		$params['Org_id'] = $data['Org_id'];

		$query = "
			select OP.OrgProducer_id 
			from passport.v_OrgProducer OP with (nolock)
			WHERE
				OP.OrgProducer_MIID <> -1
				{$filter}
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return array('success' => false, 'Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.');
		}

		$response = $result->result('array');
		if ( isset($response[0]) && $response[0]['OrgProducer_id'] != 0 ) {
			return array('success' => false, 'Error_Msg' => $actionName.' данных организации недоступно. Запись была использована в информационном обмене с федеральным порталом.');
		} else {
			return false;
		}
	}
	
	/**
	 * получение признака тестовой МО
	 */
	function getLpuIsTest($data){
		if(empty($data['Lpu_id'])) return 0;		
		$lpu = $this->getFirstRowFromQuery("select top 1 Lpu_IsTest from v_Lpu with(nolock) where Lpu_id = ".$data['Lpu_id']);
		if(!empty($lpu['Lpu_IsTest']) && $lpu['Lpu_IsTest'] == 2){
			return 1;
		}else{
			return 0;
		}
	}

	function getOrgSysNick($data)
	{
		return $this->getFirstResultFromQuery("
			select 
				OT.OrgType_SysNick 
			from 
				v_Org O with(nolock) 
				left join v_OrgType OT with(nolock) on OT.OrgType_id = O.OrgType_id
			where 
				Org_id = :Org_id
		", $data);
	}
}
