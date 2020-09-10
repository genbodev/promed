<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry_model - модель для работы с таблицей Registry
* модификация оригинального RegistryUfa_model.php для групповой постановке реестров на очередь формирования Task#18011 
*
* @package      Admin
* @access       public
* @version      26/04/2013
*/

require("Registry_modelVE.php");
class RegistryUfa_modelVE extends Registry_modelVE {
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
			from v_Registry R with(nolock)
			left join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = R.Lpu_id
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
     * comment
     */ 	
	function loadEvnVizitErrorData($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				r2.Registry with(nolock)
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
			$errors_join = " inner join r2.v_RegistryError re with(nolock) on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
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
				r2.v_RegistryData rd with(nolock)
				". $errors_join ."
				inner join v_EvnVizit ev with(nolock) on ev.EvnVizit_id = rd.Evn_id
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
     * comment
     */ 	
	function loadEvnSectionErrorData($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				r2.Registry with(nolock)
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
			$errors_join = " inner join r2.v_RegistryError re with(nolock) on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
			
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
				r2.v_RegistryData rd with(nolock)
				". $errors_join ."
				inner join v_EvnSection es with(nolock) on es.EvnSection_id = rd.Evn_id
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
     * comment
     */ 	
	function loadPersonInfoFromErrorRegistry($data)
	{
		$load_errors_only = true;
		// реестр может быть и без ошибок, а предварительный, проверяем это
		$query = "
			select
				count(Registry_id) as cnt
			from
				r2.Registry with(nolock)
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
			$errors_join = " inner join r2.v_RegistryError re with(nolock) on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";
		
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
				r2.v_RegistryData rd with(nolock)
				". $errors_join ."
				inner join v_PersonState ps with(nolock) on ps.Person_id = rd.Person_id
				left join Sex with(nolock) on Sex.Sex_id = ps.Sex_id
				left join Document doc with(nolock) on ps.Document_id = doc.Document_id
				left join DocumentType doct with(nolock) on doc.DocumentType_id = doct.DocumentType_id
				left join v_Address_all addr with(nolock) on addr.Address_id = ps.UAddress_id
				left join SocStatus ss with(nolock) on ss.SocStatus_id = ps.SocStatus_id
				left join Polis pls with(nolock) on pls.Polis_id = ps.Polis_id
				left join OrgSmo osmo with(nolock) on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Org smoorg with(nolock) on smoorg.Org_id = osmo.Org_id
			where
				rd.Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		$query = "
			select
				rtrim(isnull(LpuUnitSet_Code, '')) as LpuUnitSet_Code
			from
				r2.v_Registry with(nolock)
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
		}		
		// заодно получаем код ЛПУ, но здесь он будет LpuUnitSet
		/*$query = "
			select
				rtrim(isnull(Org_Code, '')) as Lpu_Code
			from
				v_Lpu with(nolock)
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
		}*/

		if ( is_object($result) ) {
			$sel_data = $result->result('array');
			return array('data' => $sel_data, 'lpu_code' => $lpu_code);
		}
		else {
			return false;
		}
	}
    /**
     * comment
     */ 	
	function loadRegistryDataForXmlUsing($type, $data)
	{
		// шапка
		$query = "
			exec [r2].[p_RegistryDD_Top] @Registry_id = ?
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
			exec r2.[p_RegistryDD_expRE] @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$visits = $result->result('array');
			$SLUCH = array();
			// привязываем услуги к случаю
			foreach( $visits as $visit )
			{
				if ( !isset($SLUCH[$visit['Evn_rid']]) )
					$SLUCH[$visit['Evn_rid']] = array();
				$SLUCH[$visit['Evn_rid']][] = $visit;
			}
			unset($visits);
		}
		else {
			return false;
		}
				
		// услуги
		$query = "
			exec r2.[p_RegistryDD_expU] @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$uslugi = $result->result('array');
			$USL = array();
			// привязываем услуги к случаю
			foreach( $uslugi as $usluga )
			{
				if ( !isset($USL[$usluga['EvnUslugaDispDop_rid']]) )
					$USL[$usluga['EvnUslugaDispDop_rid']] = array();
				$USL[$usluga['EvnUslugaDispDop_rid']][] = $usluga;
			}
			unset($uslugi);
		}
		else {
			return false;
		}
		
		// люди
		$query = "
			exec r2.[p_RegistryDD_expPers] @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$person = $result->result('array');
			$PACIENT = array();
			// привязываем персона к случаю
			foreach( $person as $pers )
				$PACIENT[$pers['Evn_rid']] = $pers;
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
		foreach($SLUCH as $key => $value )
		{
			// для терапевта пишем услуги
			foreach($value as $k => $val)
				if ( ($val['PRVS'] == '1122' || $val['PRVS'] == '1110') && isset($USL[$key]) )
					$value[$k]['USL'] = $USL[$key];
				else
					$value[$k]['USL'] = array();
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
			exec [r2].[p_RegistryPL_expRE] @Registry_id = ?
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
				Lpu with(nolock)
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
     * comment
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
				R.LpuUnitSet_Code as ID_SUBLPU, 
				--rt.RegistryType_Code as ID_SUBLPU, -- тип
				1 as [TYPE],
				YEAR(R.Registry_endDate) as [YEAR],
				MONTH(R.Registry_endDate) as [MONTH],
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as DATE_BEG,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as DATE_END
				
			from r2.v_Registry R with (NOLOCK)
			left join RegistryType rt with(nolock) on rt.RegistryType_id = R.RegistryType_id
			inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = R.Lpu_id
			left join v_OrgSmo OrgSmo with(nolock) on OrgSmo.OrgSmo_id = R.OrgSmo_id
			left join Org os with(nolock) on os.Org_id = OrgSmo.Org_id
			--left join LpuUnitSet with(nolock) on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id
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
     * comment
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
		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id']==5))
		{
			$query = "
			Select 
				R.RegistryQueue_id as Registry_id,
				R.OrgSmo_id,
				R.LpuUnitSet_id,
				R.RegistryType_id,
				5 as RegistryStatus_id,
				2 as Registry_IsActive,
				RTrim(R.Registry_Num)+' / в очереди: '+LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				LpuUnitSet.LpuUnitSet_Code,
				OrgSmo.OrgSmo_Nick as OrgSmo_Name,
				R.Lpu_id,
				--R.OrgRSchet_id,
				0 as Registry_Count,
				0 as Registry_ErrorCount,
				0 as Registry_Sum,
				1 as Registry_IsProgress,
				1 as Registry_IsNeedReform,
				'' as Registry_updDate
			from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
			left join v_OrgSmo OrgSmo with(nolock) on OrgSmo.OrgSmo_id = R.OrgSmo_id 
			left join LpuUnitSet with(nolock) on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id 
			where {$filter}";
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
				R.LpuUnitSet_id,
				R.RegistryType_id,
				R.RegistryStatus_id,
				R.Registry_IsActive,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				RTrim(R.Registry_Num) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--R.Registry_Sum,
				R.LpuUnitSet_Code,
				R.OrgSmo_Name as OrgSmo_Name,
				R.Lpu_id,
				--R.OrgRSchet_id,
				isnull(R.Registry_RecordCount, 0) as Registry_Count,
				isnull(R.Registry_ErrorCount, 0) as Registry_ErrorCount,
				isnull(R.Registry_Sum, 0.00) as Registry_Sum,
				case when (RQ.RegistryQueueHistory_id is not null) and (RQ.RegistryQueueHistory_endDT is null) then 1 else 0 end as Registry_IsProgress,
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),''))+' '+
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),108),'')) as Registry_updDate
			from {$this->scheme}.v_Registry R with (NOLOCK)
			outer apply(
				select top 1
					RegistryQueueHistory_id,
					RegistryQueueHistory_endDT,
					RegistryQueueHistory.Registry_id
				from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
				where RegistryQueueHistory.Registry_id = R.Registry_id
				order by RegistryQueueHistory_id desc
			) RQ
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
    * Модель выборки Уфимских СМО 
    * модификация оригинального Demand_model_VE.php для групповой постановке реестров на очередь формирования Task#18011
    * /?c=Demandve&m=getOrgSmoList
    */
	function getOrgSmoUfaList() {	
	    //Для рабочей БД - необходимо корректировать запрос

		$query = "
             select  
             O.OrgSMO_id Smo_id, 
             REPLACE(O.OrgSMO_Nick,'(РЕСПУБЛИКА БАШКОРТОСТАН)','') Smo_Name, 
             REPLACE(O.OrgSMO_Nick,'(РЕСПУБЛИКА БАШКОРТОСТАН)','') Smo_Nick
             from v_orgSmo O (nolock) where KLRgn_id=2 and OrgSmo_endDate is null
		";
        
		$res = $this->db->query($query, array());

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		} 

	}

	/**
	* saveRegistryQueue_array Task#90544
	* Установка реестров по СМО
	* Возвращает номера в очереди или ошиббки 
	*/
	function saveUnionRegistry_array($data) 
	{
		//echo "<pre>".print_r($data,1)."</pre>"; exit;
		foreach($data['Registry_id'] as $k => $reg_id)
		{
		    // Сохранение нового реестра
		    if (0 == $reg_id) 
		    {
			    $data['Registry_IsActive'][$k] = 2;
			    $operation = 'insert';
		    } else {
			    $operation = 'update';
		    }
		}

        if(is_array($data['Registry_id']))
        {
        	$xml = '<RD>'; $dataTemp = array();
            foreach($data['Registry_id'] as $k => $reg_id)
            {
				$result = $this->getFirstResultFromQuery('
					select
						PayType_id
					from
						v_PayType with (nolock)
					where
						PayType_SysNick = :PayType_SysNick
				', array('PayType_SysNick' => $data['PayType_SysNick'][$k]));

                $xml .='<R|*|v1="'.$data['RegistryType_id'][$k].'"
                          |*|v2="'.$data['Registry_accDate'][$k].'"
                          |*|v3="'.$data['Lpu_id'].'"
                          |*|v4="'.$data['RegistryStatus_id'][$k].'"
                          |*|v5="'.$data['Registry_IsActive'][$k].'"
                          |*|v6="'.$data['Registry_begDate'][$k].'"
                          |*|v7="'.$data['Registry_endDate'][$k].'"
                          |*|v8="'.$data['OrgSmo_id'][$k].'"
                          |*|v9="'.$data['LpuUnitSet_id'][$k].'"
                          |*|v10="'.$data['Registry_Num'][$k].'"
                          |*|v11="'.$data['pmUser_id'].'"
                          |*|v12="'.$data['Registry_IsNotInsur'][$k].'"
                          |*|v13="'.$data['Registry_Comment'][$k].'"
                          |*|v14="'.$data['RegistrySubType'][$k].'"
                          |*|v15="'.$data['Registry_IsNew'][$k].'"
                          |*|v16="'.$data['Registry_IsZNO'][$k].'"
                          |*|v17="'.$result.'"></R>';

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
    	} else {
    		return false;
    	}

        $xml = strtr($xml, array(PHP_EOL=>'', " "=>"")); 
        $xml = str_replace("|*|", " ", $xml);
        $params = array('xml'=> $xml);
  		
  		// 1. сохраняем реестр по СМО
		$query = "
                  declare
                    @response nvarchar(max) = null,
                    @xml nvarchar(max);
                  exec r2.p_RegistryGroup_ins
                    @response = @response output,
                    @xml = :xml;
                  select @response as ResponseText;   
			     ";

		$result = $this->db->query($query, $params);
		//echo htmlspecialchars($xml); exit;

		if (is_object($result)) {
			$res = $result->result('array');

			if (is_array($res) && count($res) > 0 && !empty($res[0]['ResponseText'])) {
				$res_arr = strtr($res[0]['ResponseText'], array(']'=>', {"success":"true"}]'));

				$json = json_decode($res[0]['ResponseText']);
				foreach($dataTemp as $k => $v) {
					foreach($json as $ke => $va) {
						$va = (array) $va;
						if($k == $ke) {
							$dataTemp[$k]['Registry_id'] = $va['Registry_id'];
							$dataTemp[$k]['Smo_Name'] = $va['Smo_Name'];
							$dataTemp[$k]['UnitSet_id'] = $va['UnitSet_id'];
							$dataTemp[$k]['LpuUnitSet_id'] = $va['LpuUnitSet_id'];
							$dataTemp[$k]['Registry_Num'] = $va['Registry_Num'];
						}
					}
				}

				foreach($dataTemp as $k => $v)
				{
					// 2. удаляем все связи
					$query = "
						delete {$this->scheme}.RegistryGroupLink with (rowlock)
						where Registry_pid = :Registry_id
					";
					$this->db->query($query, array(
						'Registry_id' => $v['Registry_id']
					));

					// Югория-Мед объединилась с Альфастрахованием, по ней реестры не формируем
					if ( $v['Registry_accDate'] >= '2017-05-25' && $v['OrgSmo_id'] == 8000233 ) {
						continue;
					} else if ( $v['Registry_accDate'] >= '2018-10-25' && $v['OrgSmo_id'] == 8000227 ) {
						continue;
					}
					// 3. выполняем поиск реестров которые войдут в финальный
					if ( $v['Registry_accDate'] >= '2017-05-25' && $v['OrgSmo_id'] == 8001229 ) {
						$filter = "and rf.OrgSmo_id in (8000233, 8001229) and ISNULL(rf.Registry_IsNotInsur, 1) = 1";
					}
					else if ( $v['Registry_accDate'] >= '2018-10-25' && $v['OrgSmo_id'] == 8001750 ) {
						$filter = "and rf.OrgSmo_id in (8000227, 8001750) and ISNULL(rf.Registry_IsNotInsur, 1) = 1";
					}
					else {
						$filter = "and rf.OrgSmo_id = :OrgSmo_id and ISNULL(rf.Registry_IsNotInsur, 1) = 1";
					}

					if ($v['Registry_IsNotInsur'] == 2) {
						// по незастрахованным только один может быть финальный реестр для предварительного, СМО не учитываем
						$filter = "and rf.Registry_IsNotInsur = 2";
					}
					$query = "
						declare @PayType_id bigint = (Select top 1 PayType_id from v_PayType pt (nolock) where pt.PayType_SysNick = 'oms');
						
						select
							R.Registry_id,
							R.Registry_Num,
							convert(varchar,R.Registry_accDate,104) as Registry_accDate,
							RT.RegistryType_Name,
							RETF.FLKCount as FLKCount
						from
							{$this->scheme}.v_Registry R (nolock)
							left join v_RegistryType RT (nolock) on RT.RegistryType_id = R.RegistryType_id
							outer apply(
								select count(RegistryErrorTFOMS_id) as FLKCount from dbo.v_RegistryErrorTFOMS (nolock) where RegistryErrorTFOMSLevel_id = 1 and Registry_id = R.Registry_id
							) RETF
						where
							R.RegistrySubType_id = 1
							" . (!empty($v['OrgSmo_id']) && $v['OrgSmo_id'] == 8 ? "and OrgSmo_id = 8" : "and OrgSmo_id is null") . "
							and R.RegistryStatus_id = 2 -- К оплате
							and R.Lpu_id = :Lpu_id
							and R.RegistryType_id = :RegistryType_id
							and R.Registry_begDate >= :Registry_begDate
							and R.Registry_endDate <= :Registry_endDate
							and R.LpuUnitSet_id = :LpuUnitSet_id
							and ISNULL(R.Registry_IsNew, 1) = :Registry_IsNew
							and ISNULL(R.DispClass_id, 0) = :DispClass_id
							and ISNULL(r.PayType_id, @PayType_id) = @PayType_id
							and not exists( -- и не входит в другой реестр по той же смо
								select top 1
									rgl.RegistryGroupLink_id
								from
									{$this->scheme}.v_RegistryGroupLink rgl (nolock)
									inner join {$this->scheme}.v_Registry rf (nolock) on rf.Registry_id = rgl.Registry_pid -- финальный реестр
								where
									rgl.Registry_id = R.Registry_id
									and ISNULL(rf.Registry_IsZNO, 1) = ISNULL(:Registry_IsZNO, 1)
									{$filter}
							)
					";
					$result_reg = $this->db->query($query, array(
						'Lpu_id' => $data['Lpu_id'],
						'OrgSmo_id' => $v['OrgSmo_id'],
						'LpuUnitSet_id' => $v['LpuUnitSet_id'],
						'RegistryType_id' => $v['RegistryType_id'],
						'Registry_begDate' => $v['Registry_begDate'],
						'Registry_endDate' => $v['Registry_endDate'],
						'Registry_IsNew' => !empty($v['Registry_IsNew']) ? $v['Registry_IsNew'] : 1,
						'Registry_IsZNO' => !empty($v['Registry_IsZNO']) ? $v['Registry_IsZNO'] : 1,
						'DispClass_id' => !empty($v['DispClass_id']) ? $v['DispClass_id'] : 0
					));

					if (is_object($result_reg))
					{
						$resp_reg = $result_reg->result('array');

						//echo "<pre>".print_r($resp_reg,1)."</pre>";exit;
						
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
								'Registry_pid' => $v['Registry_id'],
								'Registry_id' => $one_reg['Registry_id'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}

					$this->recountSumKolUnionRegistry(array(
						'Registry_id' => $v['Registry_id']
					));
				}

				return $res_arr;
			} else {
				$res_arr = false;
			}
		} else {
			$res_arr = false;
		}

		if($res_arr !== false) {
			return $res_arr;
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных!'));
		}

	}
        
	/**
	* saveRegistryQueue_array Task#18011
	* Установка реестров в очередь на формирование 
	* Возвращает номера в очереди или ошиббки 
	*/
	function saveRegistryQueue_array($data) 
	{
		foreach($data['Registry_id'] as $k => $reg_id)
		{
		    // Сохранение нового реестра
		    if (0 == $reg_id) 
		    {
			    $data['Registry_IsActive'][$k] = 2;
			    $operation = 'insert';
		    } else {
			    $operation = 'update';
		    }
		}

        if(is_array($data['Registry_id']))
        {
            $xml = '<RD>';
            foreach($data['Registry_id'] as $k=>$reg_id)
            {
                $xml .='<R|*|v1="'.$data['RegistryType_id'][$k].'"
                          |*|v2="'.$data['Registry_accDate'][$k].'"
                          |*|v3="'.$data['Lpu_id'].'"
                          |*|v4="'.$data['RegistryStatus_id'][$k].'"
                          |*|v5="'.$data['Registry_IsActive'][$k].'"
                          |*|v6="'.$data['Registry_begDate'][$k].'"
                          |*|v7="'.$data['Registry_endDate'][$k].'"
                          |*|v8="'.$data['OrgSmo_id'][$k].'" 
                          |*|v9="'.$data['LpuUnitSet_id'][$k].'"
                          |*|v10="'.$data['Registry_Num'][$k].'"
                          |*|v11="'.$data['pmUser_id'].'"
                          |*|v12="'.$data['RegistrySubType'][$k].'"
                          |*|v13="'.$data['Registry_IsNew'][$k].'" ></R>';
            }
            $xml .= '</RD>';
        } else {
        	return false;
        }

        $xml = strtr($xml, array(PHP_EOL=>'', " "=>"")); 
        $xml = str_replace("|*|", " ", $xml);
        $params = array('xml'=>(string)$xml);
        
		$query = "
                  declare
                    @response nvarchar(max) = null,
                    @xml nvarchar(max);
                  exec r2.p_RegistryQueueGroup_ins
                    @response = @response output,
                    @xml = :xml;

				  select @response as ResponseText;   
			     ";

		$result = $this->db->query($query, $params);
		//echo htmlspecialchars($xml); exit;

		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) && count($res) > 0 && !empty($res[0]['ResponseText']) ) {
				$res_arr = strtr($res[0]['ResponseText'], array(']'=>', {"success":"true"}]'));
			} else {
				$res_arr = false;
			}
		} else {
			$res_arr = false;
		}

		if($res_arr !== false)
		{
			return $res_arr;
		} else {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных!'));
		}

	}

	/**
	 * Пересчёт суммы и количества в реестре по СМО
	 */
	function recountSumKolUnionRegistry($data) {
		$query = "
			select
				r.Registry_id,
				r.RegistrySubType_id,
				r.Registry_IsNotInsur,
				r.Registry_IsZNO,
				r.OrgSmo_id,
				convert(varchar(10), r.Registry_accDate, 120) as Registry_accDate
			from
				{$this->scheme}.v_Registry r (nolock)
			where
				r.Registry_id = :Registry_id
		";

		$resp = $this->queryResult($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Registry_id'])) {
			$data['Registry_id'] = $resp[0]['Registry_id'];
			if ($resp[0]['RegistrySubType_id'] != '2') {
				return array('Error_Msg' => 'Указанный реестр не является реестром по СМО');
			}

			$this->setRegistryParamsByType($data);

			$filter = "";
			if ($resp[0]['Registry_IsNotInsur'] == 2) {
				// если реестр по СМО, то не зависимо от соц. статуса
				if ($this->RegistryType_id == 6) {
					$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				} else {
					$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				}
			} else if ($resp[0]['OrgSmo_id'] == 8) {
				// инотеры
				$filter_rd = " and RD.Polis_id IS NOT NULL";
				$filter .= " and IsNull(os.OrgSMO_RegNomC,'')=''";
			}
			else {
				// @task https://redmine.swan.perm.ru//issues/109876
				if ( $resp[0]['Registry_accDate'] >= '2017-05-25' && $resp[0]['OrgSmo_id'] == 8001229 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
				}
				else if ( $resp[0]['Registry_accDate'] >= '2018-10-25' && $resp[0]['OrgSmo_id'] == 8001750 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
				}
				else {
					$filter_rd = " and RD.OrgSmo_id = RF.OrgSmo_id";
				}
			}

			if ( $resp[0]['Registry_IsZNO'] == 2 ) {
				$filter .= " and RD.RegistryData_IsZNO in (2, 3) ";
			}
			else {
				$filter .= " and ISNULL(RD.RegistryData_IsZNO, 1) = 1 ";
			}

			// считаем сумму и количество
			$resp_sum = $this->queryResult("
				select
					SUM(case when ISNULL(RD.RegistryData_IsBadVol, 1) = 1 then RD.RegistryData_Sum_R else 0 end) as Sum,
					SUM(case when ISNULL(RD.RegistryData_IsBadVol, 1) = 1 then 1 else 0 end) as Count,
					SUM(case when ISNULL(RD.RegistryData_IsBadVol, 1) = 2 then 1 else 0 end) as CountBadVol
				from
					{$this->scheme}.v_RegistryGroupLink RGL (nolock)
					inner join {$this->scheme}.v_Registry RF (nolock) on RF.Registry_id = RGL.Registry_pid
					inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rd (nolock) on rd.Registry_id = r.Registry_id {$filter_rd}
					left join v_OrgSmo os (nolock) on os.OrgSmo_id = rd.OrgSmo_id
				where
					RGL.Registry_pid = :Registry_id
					{$filter}
			", array(
				'Registry_id' => $data['Registry_id']
			));

			$this->db->query("
				update {$this->scheme}.Registry with (rowlock) set Registry_SumR = :Registry_SumR, Registry_Sum = :Registry_Sum, Registry_RecordCount = :Registry_RecordCount, Registry_CountIsBadVol = :Registry_CountIsBadVol where Registry_id = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id'],
				'Registry_SumR' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
				'Registry_Sum' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
				'Registry_RecordCount' => !empty($resp_sum[0]['Count'])?$resp_sum[0]['Count']:0,
				'Registry_CountIsBadVol' => !empty($resp_sum[0]['CountBadVol'])?$resp_sum[0]['CountBadVol']:0
			));
		}
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1: case 14: $this->RegistryDataObject = 'RegistryDataEvnPS'; break;
			case 2: $this->RegistryDataObject = 'RegistryData'; break;
			case 6: $this->RegistryDataObject = 'RegistryDataCmp'; $this->RegistryDataEvnField = 'CmpCloseCard_id'; break;
			case 7: case 9: $this->RegistryDataObject = 'RegistryDataDisp'; break;
			case 17: $this->RegistryDataObject = 'RegistryDataProf'; break;
			default: $this->RegistryDataObject = 'RegistryData'; break;
		}
	}

    /**
     * comment
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
				OrgSmo_id,
				LpuUnitSet_id,
				Registry_Num,
				Registry_IsActive,
				convert(varchar,cast(Registry_accDate as datetime),112) as Registry_accDate
			from
				r2.Registry with (NOLOCK)
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
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['OrgSmo_id'] = $row[0]['OrgSmo_id'];
				$data['LpuUnitSet_id'] = $row[0]['LpuUnitSet_id'];
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
     * comment
     */ 	
	function deleteRegistryError($data) 
	{
		$filter = "";
		$params = array();
		$join = "";
		if ($data['Registry_id']>0)
		{
			$params['Registry_id'] = $data['Registry_id'];
		}
		else 
			return false;
			
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec r2.p_RegistryError_del
				@Registry_id = :Registry_id,
				@Evn_id = NULL,
				@RegistryErrorType_id = NULL,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		// echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);
		return true;
	}
    /**
     * comment
     */ 	
	function checkErrorDataInRegistry($data) {
		$query = "Select rd.Registry_id from r2.v_RegistryData rd with(nolock)	where rd.Registry_id = :Registry_id  and (rd.Evn_id = :IDCASE OR :IDCASE IS NULL) and (rd.Person_id = :ID_PERS OR :ID_PERS IS NULL)";
			
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
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec r2.p_Registry_setPaid
					@Registry_id = :Registry_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
					
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			if ($data['Registry_id']>0)
			{
				$query = "SELECT TOP 1 RegistryErrorType_id FROM r2.RegistryErrorType with (nolock) WHERE RegistryErrorType_Code = :FLAG";
				$resp = $this->db->query($query, $params);
				if (is_object($resp))
				{
					$ret = $resp->result('array');
					if (is_array($ret) && (count($ret) > 0)) {
					
						$params['FLAG'] = $ret[0]['RegistryErrorType_id'];
						$query = "
							declare
								@Regisry_id bigint,
								@Evn_id bigint,
								@LpuSection_id bigint,
								@ErrCode int,
								@ErrMessage varchar(4000);
								
							Select 
								@Regisry_id = rd.Registry_id,
								@Evn_id = rd.Evn_id,
								@LpuSection_id = rd.LpuSection_id
							from
								r2.v_RegistryData rd with(nolock)
							where
								rd.Registry_id = :Registry_id
								and rd.Evn_id = :IDCASE
							
							exec r2.p_RegistryError_ins
								@Registry_id = @Regisry_id,
								@Evn_id = @Evn_id,
								@RegistryErrorType_id = :FLAG,
								@LpuSection_id = @LpuSection_id, 
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
								 
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
							
						$result = $this->db->query($query, $params);
						// если выполнилось, возвращаем пустой Error_Msg
						if (is_object($result)) 
						{
							$resp = $result->result('array');
							if (empty($resp[0]['Error_Msg'])) {
								return array(array('success' => true, 'Error_Msg' => ''));
							} else {
								return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса добавления ошибки'));
							}
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
					declare
						@Regisry_id bigint,
						@Evn_id bigint,
						@LpuSection_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
						
					Select 
						@Regisry_id = rd.Registry_id,
						@Evn_id = rd.Evn_id,
						@LpuSection_id = rd.LpuSection_id
					from
						r2.v_RegistryData rd with(nolock)
					where
						rd.Registry_id = :Registry_id
						and rd.Guid = :ID
					
					exec r2.p_RegistryError_ins
						@Registry_id = @Regisry_id,
						@Evn_id = @Evn_id,
						@RegistryErrorType_id = :FLAG,
						@LpuSection_id = @LpuSection_id, 
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
						 
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	}
    /**
     * comment
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
			from r2.v_Registry Registry with (NOLOCK)
				inner join Lpu with(nolock) on Lpu.Lpu_id = Registry.Lpu_id
				inner join Org with(nolock) on Org.Org_id = Lpu.Org_id
				inner join RegistryType RT with(nolock) on RT.RegistryType_id = Registry.RegistryType_id
				left join Okved with(nolock) on Okved.Okved_id = Org.Okved_id
				left join [Address] LpuAddr on LpuAddr.Address_id = Org.UAddress_id
				left join OrgRSchet ORS with(nolock) on Registry.OrgRSchet_id = ORS.OrgRSchet_id
				left join v_OrgBank OB with(nolock) on OB.OrgBank_id = ORS.OrgBank_id
				outer apply (
					select 
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH with(nolock)
						inner join v_PersonState PS with(nolock) on PS.Person_id = OH.Person_id
					where 
						OH.Lpu_id = Lpu.Lpu_id
						and OH.OrgHeadPost_id = 1
				) as OHDirector
				outer apply (
					select 
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH with(nolock)
						inner join v_PersonState PS with(nolock) on PS.Person_id = OH.Person_id
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
     * comment
     */ 
	function savePersonData($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;

			exec r2.p_RegErrorPerson_ins
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
     * comment
     */ 
	function updatePersonErrorData($data) {
		$query = "
			declare @Res bigint;

			exec r2.xp_RegistryErrorPerson_process
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
     * comment
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
     *  Получение наименования СМО реестра
     */ 
    function getSmoName($data){
        $queryParams = array('Registry_id'=>$data['Registry_id']);
        $query = "select top 1 case when R.OrgSmo_Name is null then 'Без СМО' else R.OrgSmo_Name end as Smo_Name  from r2.v_Registry R with (nolock) where R.Registry_id = :Registry_id";
        
    	$result = $this->db->query($query, $queryParams);
       
        //echo getDebugSQL($query, $queryParams);
        
		if ( is_object($result) ) {
		    //echo '<pre>' . print_r($result->result('array'), 1) . '</pre>';
			return $result->result('array');
		}
		else {
			return false;
		}        
    }
	
}