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
require_once(APPPATH.'models/_pgsql/Registry_model.php');

class Kareliya_Registry_model extends Registry_model {
	public $scheme = "r10";
	public $region = "kareliya";
	public $Registry_EvnNum = null;
	public $MaxEvnField = 'Evn_id';
	public $registryEvnNum = [];

    protected $_IDSERV = 0;
    protected $_N_ZAP = 0;

	protected $exportPersonDataFile = '';
	protected $exportPersonDataBodyTemplate = '';
	protected $exportPersonDataFooterTemplate = '';
	protected $exportPersonDataHeaderTemplate = '';
	protected $exportSluchDataFile = '';
	protected $exportSluchDataFileTmp = '';
	protected $exportSluchDataBodyTemplate = '';
	protected $exportSluchDataFooterTemplate = '';
	protected $exportSluchDataHeaderTemplate = '';

	private $_registryTypeList = [
		1 => [ 'RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар', 'SP_Object' => 'EvnPS', 'IsBud' => true ],
		2 => [ 'RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника', 'SP_Object' => 'EvnPL', 'IsBud' => true ],
		6 => [ 'RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь', 'SP_Object' => 'SMP', 'IsBud' => true ],
		7 => [ 'RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года', 'SP_Object' => 'EvnPLDD13', 'IsBud' => false ],
		9 => [ 'RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года', 'SP_Object' => 'EvnPLOrp13', 'IsBud' => false ],
		11 => [ 'RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения', 'SP_Object' => 'EvnPLProf', 'IsBud' => false ],
		12 => [ 'RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних', 'SP_Object' => 'EvnPLProfTeen', 'IsBud' => false ],
		14 => [ 'RegistryType_id' => 14, 'RegistryType_Name' => 'ВМП', 'SP_Object' => 'EvnHTM', 'IsBud' => true ],
		15 => [ 'RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги', 'SP_Object' => 'EvnUslugaPar', 'IsBud' => true ],
	];

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
    /**
     * Получение дополнительных полей для сохранения реестра
     */
    public function getSaveRegistryAdditionalFields()
    {
        return "
			DispClass_id := :DispClass_id,
		";
    }

    /**
     *    Список случаев по пациентам без документов ОМС
     * @param $data
     * @return array|bool
     */
    public function loadRegistryNoPolis($data)
    {
        if ($data['Registry_id']<=0) {
            return false;
        }

        if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
            return false;
        }

        $params = array('Registry_id' => $data['Registry_id']);

        $this->setRegistryParamsByType($params);

        $filterAddQueryTemp = null;
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

            if (is_array($filterData)) {

                foreach($filterData as $column=>$value){

                    if(is_array($value)){
                        $r = null;

                        foreach($value as $d){
                            $r .= "'".trim($d)."',";
                        }

                        if($column == 'Evn_ident'){
                            if ($data['RegistryType_id'] == 1) {
                                $column = "RNP.Evn_sid";
                            } else if ($data['RegistryType_id'] == 14) {
                                $column = "RNP.Evn_rid";
                            } else {
                                $column = "RNP.Evn_id";
                            }

                        } else if($column == 'Person_FIO') {
                            $column = "rtrim(coalesce(RNP.Person_SurName,'')) || ' ' || coalesce(coalesce(RNP.Person_FirName,'')) || ' ' || rtrim(coalesce(RNP.Person_SecName, ''))";//'RE.'.$column;
                        } else if($column == 'LpuSection_Name') {
                            $column = "(rtrim(LpuSection.LpuSection_Code) || '. ' || LpuSection.LpuSection_Name)";//'RD.'.$column;
                        }
                        $r = rtrim($r, ',');

                        $filterAddQueryTemp[] = $column.' IN ('.$r.')';
                    }
                }

            }

            if(is_array($filterAddQueryTemp)){
                $filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
            } else {
                $filterAddQuery = "and (1=1)";
            }
        }

        $filterAddQuery = $filterAddQuery ?? null;

        if ($data['Registry_id'] == 6) {
            $evn_join = "";
            $set_date_time = " null as \"Evn_setDT\"";
        } else {
            $evn_join = " left join v_Evn Evn on Evn.Evn_id = RNP.Evn_id";
            $set_date_time = " to_char(Evn.Evn_setDT, 'dd.mm.yyyy') || ' ' || to_char(Evn.Evn_setDT, 'hh24:mi') as \"Evn_setDT\"";
        }

        $filters = "";
        if (!empty($data['Person_OrgSmo'])) {
            $params['Person_OrgSmo'] = $data['Person_OrgSmo'];
            $filters .= " and coalesce(OrgSMO.Orgsmo_f002smocod,'') || ' ' || coalesce(OrgSMO.OrgSMO_Nick,'') ilike '%'||:Person_OrgSmo || '%'";
        }

        if (!empty($data['Person_Polis'])) {
            $params['Person_Polis'] = $data['Person_Polis'];
            $filters .= " and pol.Polis_Num ilike '%'||:Person_Polis||'%'";
        }

        if ($data['RegistryType_id'] == 1) {
            $Evn_ident = "RNP.Evn_sid as \"Evn_ident\",";
        } else if ($data['RegistryType_id'] == 14) {
            $Evn_ident = "RNP.Evn_rid as \"Evn_ident\",";
        } else {
            $Evn_ident = "RNP.Evn_id as \"Evn_ident\",";
        }

        $query = "
		Select
			RNP.Registry_id as \"Registry_id\",
			RNP.Evn_id as \"Evn_id\",
			RNP.Evn_rid as \"Evn_rid\",
			{$Evn_ident}
			RNP.Person_id as \"Person_id\",
			RNP.Server_id as \"Server_id\",
			RNP.PersonEvn_id as \"PersonEvn_id\",
			rtrim(coalesce(RNP.Person_SurName,'')) || ' ' || rtrim(coalesce(RNP.Person_FirName,'')) || ' ' || rtrim(coalesce(RNP.Person_SecName, '')) as \"Person_FIO\",
			RTrim(coalesce(to_char(cast(RNP.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
			rtrim(LpuSection.LpuSection_Code) || '. ' || LpuSection.LpuSection_Name as \"LpuSection_Name\",
			rtrim(coalesce (pol.Polis_Ser, '')) || rtrim(coalesce(' №' || pol.Polis_Num,'')) as \"Person_Polis\",
			coalesce(to_char(cast(pol.Polis_begDate as timestamp), 'dd.mm.yyyy'),'...') || ' - ' || coalesce(to_char(cast(pol.Polis_endDate as timestamp), 'dd.mm.yyyy'),'...') as \"Person_PolisDate\",
			coalesce(OrgSMO.Orgsmo_f002smocod,'') || ' ' || coalesce(OrgSMO.OrgSMO_Nick,'') as \"Person_OrgSmo\",
			{$set_date_time}
		from {$this->scheme}.v_{$this->RegistryNoPolisObject} RNP
			left join v_Person_bdz ps on ps.PersonEvn_id = RNP.PersonEvn_id and ps.Server_id = RNP.Server_id
			left join v_Polis pol on pol.Polis_id = ps.Polis_id
			left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = pol.OrgSmo_id
		left join v_LpuSection LpuSection on LpuSection.LpuSection_id = RNP.LpuSection_id
		{$evn_join}
		where
			RNP.Registry_id=:Registry_id
			{$filters}
			{$filterAddQuery}
		order by RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName, LpuSection.LpuSection_Name";
        $result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);

        $result_count = $this->db->query(getCountSQL($query), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);}
        else {
            $count = 0;
        }

        if (!is_object($result)) {
            return false;
        }

        $response = array();
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
    }

    /**
     * Получение дополнительных полей
     */
    public function getReformErrRegistryAdditionalFields()
    {
        return ",OrgSMO_id as \"OrgSMO_id\",DispClass_id as \"DispClass_id\"";
    }

    /**
     *    Получение номера выгружаемого файла реестра в отчетном периоде
     * @param $data
     * @return int
     */
    public function SetXmlPackNum($data)
    {
        $where = "";
        if ($data['KatNasel_SysNick'] == 'oblast') {
            $where .= " and kn.KatNasel_SysNick ilike 'oblast'";
        }
        else {
            $where .= " and kn.KatNasel_SysNick in ('all','inog')";
        }

        $query = "
			with cte as (
			
					select coalesce(max(Registry_FileNum), 0) + 1 as packNum
					from {$this->scheme}.v_Registry r
					inner join v_KatNasel kn on kn.KatNasel_id = r.KatNasel_id
					where Lpu_id = :Lpu_id
						and SUBSTRING(to_char(Registry_endDate, 'yyyymmdd'), 3, 4) = :Registry_endMonth
						and Registry_FileNum is not null
						{$where}
				)
				
            update {$this->scheme}.Registry
            set Registry_FileNum = (select packNum from cte)
            where Registry_id = :Registry_id;
            
			returning (select packNum from cte) as \"packNum\", '' as \"Error_Msg\";
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
     * Установка признака "Оплачен" для случаев без ошибок
     * @param $data
     * @return bool
     */
    public function setRegistryPaid($data)
    {
        $registry_list = array();
        $query = "
			select
				RT.RegistryType_SysNick
			from
				{$this->scheme}.v_Registry R
				inner join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
			where
				R.Registry_id = :Registry_id
			limit 1
		";
        $RegistryType_SysNick = $this->getFirstResultFromQuery($query, $data);

        if ($RegistryType_SysNick == 'group') {
            $query = "
				select RGL.Registry_id as \"Registry_id\"
				from {$this->scheme}.v_RegistryGroupLink RGL
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
				select
				    Error_Msg as \"Error_Msg\"
				from {$this->scheme}.p_Registry_setPaid
				(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
            $resp = $this->getFirstRowFromQuery($query, $params);
            if (!$resp || !empty($resp['Error_Msg'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Получаем состояние реестра в данный момент, и тип реестра
     * @param $data
     * @return array|false
     */
    public function GetRegistryXmlExport($data)
    {
        if (empty($data['Registry_id'])) {
            return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
        }

        $this->setRegistryParamsByType($data);

        if ($this->RegistryType_id == 13) {
            $query = "
				with RD (
					Evn_id,
					RegistryData_ItogSum
				) as (
					select
						RDE.Evn_id,
						RDE.RegistryData_ItogSum
					from
						{$this->scheme}.v_RegistryDataEvnPS RDE
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select
						RDE.Evn_id,
						RDE.RegistryData_ItogSum
					from
						{$this->scheme}.v_RegistryData RDE
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select
						RDE.Evn_id,
						RDE.RegistryData_ItogSum
					from
						{$this->scheme}.v_RegistryDataCmp RDE
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select
						RDE.Evn_id,
						RDE.RegistryData_ItogSum
					from
						{$this->scheme}.v_RegistryDataDisp RDE
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select
						RDE.Evn_id,
						RDE.RegistryData_ItogSum
					from
						{$this->scheme}.v_RegistryDataProf RDE
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select
						RDE.Evn_id,
						RDE.RegistryData_ItogSum
					from
						{$this->scheme}.v_RegistryDataPar RDE
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RDE.Registry_id
					where
						RGL.Registry_pid = :Registry_id
				)
	
				select
					RTrim(UR.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					UR.RegistryType_id as \"RegistryType_id\",
					UR.RegistryStatus_id as \"RegistryStatus_id\",
					UR.RegistryGroupType_id as \"RegistryGroupType_id\",
					coalesce(kn.KatNasel_SysNick, 'all') as \"KatNasel_SysNick\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					UR.Registry_IsZNO as \"Registry_IsZNO\",
					RSum.Registry_IsNeedReform as \"Registry_IsNeedReform\",
					RSum.Registry_Sum - round(RDSum.RegistryData_ItogSum,2) as \"Registry_SumDifference\",
					RDSum.RegistryData_Count as \"RegistryData_Count\",
					coalesce(UR.Registry_IsLocked, 1) as \"Registry_IsLocked\",
					coalesce(UR.RegistryCheckStatus_id, 0) as \"RegistryCheckStatus_id\",
					coalesce(rcs.RegistryCheckStatus_Code, -1) as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					SUBSTRING(to_char(UR.Registry_endDate, 'yyyymmdd'), 3, 4) as \"Registry_endMonth\", -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
					to_char(Registry_begDate, 'yyyymmdd) as \"Registry_begDate\"
				from {$this->scheme}.v_Registry UR
					left join v_KatNasel kn on kn.KatNasel_id = UR.KatNasel_id
					left join v_PayType pt on pt.PayType_id = UR.PayType_id
					left join lateral(
						select
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from RD
					) RDSum on true
					left join lateral (
						select
							SUM(coalesce(R.Registry_Sum,0)) as Registry_Sum,
							MAX(coalesce(R.Registry_IsNeedReform, 1)) as Registry_IsNeedReform
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
        }
        else {
            $query = "
				select
					RTrim(R.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
					R.RegistryType_id as \"RegistryType_id\",
					null as \"RegistryGroupType_id\",
					R.RegistryStatus_id as \"RegistryStatus_id\",
					R.Registry_IsZNO as \"Registry_IsZNO\",
					coalesce(kn.KatNasel_SysNick, 'all') as \"KatNasel_SysNick\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					coalesce(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as \"Registry_SumDifference\",
					RDSum.RegistryData_Count as \"RegistryData_Count\",
					coalesce(R.Registry_IsLocked, 1) as \"Registry_IsLocked\",
					coalesce(R.RegistryCheckStatus_id, 0) as \"RegistryCheckStatus_id\",
					coalesce(rcs.RegistryCheckStatus_Code,-1) as \"RegistryCheckStatus_Code\",
					rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					SUBSTRING(to_char(R.Registry_endDate, 'yyyymmdd'), 3, 4) as \"Registry_endMonth\", -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
					convert(Registry_begDate, 'yyyymmdd') as \"Registry_begDate\"
				from {$this->scheme}.v_Registry R
					left join v_KatNasel kn on kn.KatNasel_id = R.KatNasel_id
					left join v_PayType pt on pt.PayType_id = R.PayType_id
					left join lateral (
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(coalesce(RD.RegistryData_ItogSum, 0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_{$this->RegistryDataObject} RD
						where
							RD.Registry_id = R.Registry_id
					) RDSum on true
					left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				where
					R.Registry_id = :Registry_id
			";
        }

        return $this->queryResult($query, array(
            'Registry_id' => $data['Registry_id']
        ));
    }

    /**
     *    Установка еще какого-то признака
     * @param $data
     */
    public function setRegistryDataNoPolis($data)
    {
        $filter = "";

        if (!empty($data['Evn_sid'])) {
            $filter = "and rd.Evn_sid = :Evn_sid";
        }

        $this->setRegistryParamsByType($data, true);

        $query = "
			insert {$this->scheme}.{$this->RegistryNoPolisObject} (Registry_id, Evn_id, Evn_sid, Person_id, Evn_Code, Person_SurName, Person_FirName, Person_SecName, Person_BirthDay, pmUser_insID, pmUser_updID, RegistryNoPolis_insDT, RegistryNoPolis_updDT)
			select 
				rd.Registry_id, rd.Evn_id, rd.Evn_sid, rd.Person_id, '', rd.Person_SurName, rd.Person_FirName, rd.Person_SecName, rd.Person_BirthDay, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryNoPolis_insDT, dbo.tzGetDate() as RegistryNoPolis_updDT 
			from {$this->scheme}.v_{$this->RegistryDataObject} rd
			where rd.Registry_id = :Registry_id
				and rd.Evn_id = :Evn_id
				{$filter}
		";

        $result = $this->db->query($query, $data);
    }

    /**
     * Удаление данных в реестре о пациентах без полиса
     * @param $data
     * @return bool
     */
    public function deleteRegistryNoPolis($data)
    {
        $params = array('Registry_id' => $data['Registry_id']);

        $registry_list = array();
        $query = "
			select
				RT.RegistryType_SysNick as \"RegistryType_SysNick\"
			from
				{$this->scheme}.v_Registry R
				inner join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
			where
				R.Registry_id = :Registry_id
		";
        $RegistryType_SysNick = $this->getFirstResultFromQuery($query, $data);

        if ($RegistryType_SysNick == 'group') {
            $query = "
				select
				    RGL.Registry_id as \"Registry_id\",
				    R.RegistryType_id as \"RegistryType_id\"
				from {$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
				where RGL.Registry_pid = :Registry_id
			";
            $result = $this->db->query($query, $data);
            $registry_list = $result->result('array');
        } else {
            $registry_list[] = array('Registry_id' => $data['Registry_id'], 'RegistryType_id' => $data['RegistryType_id']);
        }

        foreach($registry_list as $registry) {
            $params = array(
                'Registry_id' => $registry['Registry_id'],
                'pmUser_id' => $data['pmUser_id']
            );

            $object = ($registry['RegistryType_id'] == 6 ? 'RegistryCmpNoPolis' : 'RegistryNoPolis');
            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_{$object}_del
				(
				    Registry_id := :Registry_id
				)
			";
            $resp = $this->getFirstRowFromQuery($query, $params);
            if (!$resp || !empty($resp['Error_Msg'])) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Удаление ошибок
     * @param $data
     * @return bool
     */
    public function deleteRegistryErrorTFOMS($data)
    {
        $params = array('Registry_id' => $data['Registry_id']);

        $query = "
            select
                Error_Code as Error_Code,
                Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryErrorTFOMS_del
		    (
			    Registry_id := :Registry_id
		    )
		";

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        }

        return false;
    }

    /**
     *    Загрузка данных по реестру
     * @param $data
     * @return array|bool
     */
    public function loadRegistryData($data)
    {
        if ($data['Registry_id'] == 0) {
            return false;
        }
        if ($data['RegistryType_id'] == 0) {
            return false;
        }

        if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
            return false;
        }

        $this->setRegistryParamsByType($data);

        $filterAddQueryTemp = null;
        $filterAddQuery = "";
        if(isset($data['Filter']) && in_array($this->region, array('kareliya'))){
            $filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

            if(is_array($filterData)){

                foreach($filterData as $column=>$value){

                    if(is_array($value)){
                        $r = null;

                        foreach($value as $d){
                            $r .= "'".trim(toAnsi($d))."',";
                        }

                        switch ($column) {
                            case 'Diag_Code':
                                $column = 'D.'.$column;
                                break;
                            case 'EvnPL_NumCard':
                                $column = 'RD.NumCard';
                                break;
                            case 'LpuSection_name':
                                $column = 'RD.' . $column;
                                break;
                            case 'LpuBuilding_Name':
                                $column = 'LB.' . $column;
                                break;
                            case 'Usluga_Code':
                                $column = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
                                break;
                            case 'Paid':
                                $column = 'RD.Paid_id';
                                break;
                            case 'Evn_id':
                                $column = 'RD.Evn_id';
                                break;
                            case 'Evn_ident':
                                $column = 'RD.Evn_id';
                                if ($this->RegistryType_id == 1) {
                                    $column = 'RD.Evn_sid';
                                } else if ($this->RegistryType_id == 14) {
                                    $column = 'ES.EvnSection_pid';
                                }
                                break;
                        }

                        $r = rtrim($r, ',');
                        $filterAddQueryTemp[] = $column.' IN ('.$r.')';

                    }
                }
            }

            if(is_array($filterAddQueryTemp)){
                $filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
            } else {
                $filterAddQuery = "";
            }
        }
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Lpu_id'=>$data['session']['lpu_id']
        );
        $filter="(1=1)";
        $join = "";

        if (isset($data['Person_SurName'])) {
            $filter .= " and RD.Person_SurName ilike :Person_SurName ";
            $params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
        }

        if (isset($data['Person_FirName'])) {
            $filter .= " and RD.Person_FirName ilike :Person_FirName ";
            $params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
        }

        if (isset($data['Person_SecName'])) {
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

        if ($data['RegistryType_id'] == 14) {
            $join .= " left join v_EvnSection es on es.EvnSection_id = RD.Evn_sid ";
            $Evn_ident = "ES.EvnSection_pid as \"Evn_ident\",";
            if (!empty($data['Evn_id'])) {
                $filter .= " and ES.EvnSection_pid = :Evn_id";
                $params['Evn_id'] = $data['Evn_id'];
            }
        } else if ($data['RegistryType_id'] == 1) {
            $Evn_ident = "RD.Evn_sid as \"Evn_ident\",";
            if (!empty($data['Evn_id'])) {
                $filter .= " and RD.Evn_sid = :Evn_id";
                $params['Evn_id'] = $data['Evn_id'];
            }
        } else {
            $Evn_ident = "RD.Evn_id as \"Evn_ident\",";
            if (!empty($data['Evn_id'])) {
                $filter .= " and RD.Evn_id = :Evn_id";
                $params['Evn_id'] = $data['Evn_id'];
            }
        }

        if ( !empty($data['filterRecords']) ) {
            if ($data['filterRecords'] == 2) {
                $filter .= " and coalesce(RD.RegistryData_IsPaid, 1) = 2";
            } elseif ($data['filterRecords'] == 3) {
                $filter .= " and coalesce(RD.RegistryData_IsPaid, 1) = 1";
            }
        }

        if ( in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
        {
            $fields = '';
            $select_mes = "'' as \"Mes_Code\",";
            if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
                $source_table = 'v_RegistryDeleted_Data';
            } else {
                $source_table = 'v_' . $this->RegistryDataObject;
                $join .= "left join v_MesOld MOLD on MOLD.Mes_id=RD.MesItog_id";
                $select_mes = "MOLD.Mes_Code + coalesce(' ' + MOLD.MesOld_Num, '') as Mes_Code,";
            }
            //УЕТ для поликлиники
            if ($data['RegistryType_id'] == 2) {
                $select_uet = "
					case when PMT.PaymedType_Code in (1,12,22,4) then 1 else
					case when PMT.PaymedType_Code=9 then RD.RegistryData_KdPay else
					case when PMT.PaymedType_Code in (10,17) then Cnt.VizitCount else
					case when PMT.PaymedType_Code=23 then Cnt.UslugaCount
					end end end end as RegistryData_Uet,
				";
                $join .= "
					left join lateral (
						select
							count(distinct EvnViz.EvnVizit_id) as VizitCount,
							sum(coalesce(case when UslugaComplex.UslugaComplex_Code = 'A.18.30.001' then EvnUsluga.EvnUsluga_Kolvo end,0)) as UslugaCount
						from v_EvnVizit EvnViz
							left join v_EvnUsluga EvnUsluga on EvnUsluga.EvnUsluga_pid=EvnViz.EvnVizit_id
							left join UslugaComplex on UslugaComplex.UslugaComplex_id=EvnUsluga.UslugaComplex_id
						where EvnViz.EvnVizit_pid = RD.Evn_rid and EvnViz.Lpu_id = RD.Lpu_id
					) Cnt on true
				";

                $fields .= "case
								when coalesce(EPL.Lpu_CodeSMO, '') = '' then ''
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
            //Новые колонки http://redmine.swan.perm.ru/issues/64952
            if ($data['RegistryType_id'] == 2) {
                $fields .= "LTRIM(DG.Diag_Code || ' ' || DG.Diag_Name) as \"Diag_Name\",
							vVC.vVizitCount as \"vVizit_Count\",
							vVT.VizitType_Name as \"VizitType_Name\",
				";
                $join .= "
					left join v_Diag DG on DG.Diag_id = EPL.Diag_id
					left join lateral (
						select
							count(distinct EvnViz.EvnVizit_id) as vVizitCount
						from v_EvnVizit EvnViz
						where EvnViz.EvnVizit_pid = RD.Evn_rid and EvnViz.Lpu_id = RD.Lpu_id
					) vVC on true
					left join lateral(
						select
							VT.VizitType_Name as VizitType_Name
						from
						    v_EvnVizitPL EVPL 
						    left join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
						where EVPL.EvnVizitPL_pid = RD.Evn_rid and EVPL.Lpu_id = RD.Lpu_id
					    limit 1
					) vVT on true
				";
            }
            if ($data['RegistryType_id'] == 1 || $data['RegistryType_id'] == 14) {
                $fields .= "rd.RegistryData_G162 as \"EvnSection_CoeffCTP\",";
                $fields .= "LTRIM(DG.Diag_Code || ' ' || DG.Diag_Name) as \"Diag_Name\",";
                $join .= " left join Diag DG on DG.Diag_id = RD.Diag_id ";
            }

            if ($data['RegistryType_id'] == 6) {
                $fields .= "
					case
						when coalesce(CLC.Lpu_CodeSMO, CCC.Lpu_CodeSMO) is null then ''
						when coalesce(CLC.Lpu_CodeSMO, CCC.Lpu_CodeSMO) = Lpu.Lpu_f003mcod then 'Да'
						when CMPAttachLpu.Lpu_Nick is not null then CMPAttachLpu.Lpu_Nick
						when CMPAttachLpu.Lpu_Nick is null then 'Нет'
					end as \"attachToMO\",
					CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
				";
                $join .= "
					left join v_CmpCallCard CCC on CCC.CmpCallCard_id = RD.Evn_id
					left join v_CmpCloseCard CLC on CLC.CmpCallCard_id = CCC.CmpCallCard_id
					left join lateral (
						select Lcmp.Lpu_Nick
						from v_Lpu Lcmp
						where coalesce(CLC.Lpu_CodeSMO, CCC.Lpu_CodeSMO) = Lcmp.Lpu_f003mcod
					    limit 1
					) CMPAttachLpu on true
				";
            }

            if (in_array($data['RegistryType_id'], array(2, 6))) {
                $join .= " left join lateral ( select Lpu_f003mcod from v_Lpu where Lpu_id = :Lpu_id limit 1) Lpu on true";
            }

            if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
                $join .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
                $fields .= "epd.DispClass_id as \"DispClass_id\", ";
            }

            if ( in_array($data['RegistryType_id'], array(1, 14)) ) {
                $setDateField = 'RegistryData_ReceiptDate';
            }
            else {
                $setDateField = 'Evn_setDate';
            }

            $query = "
				Select
					-- select
					RD.Evn_sid as \"Evn_id\",
					RD.Evn_id as \"Evn_sid\",
					RD.{$this->MaxEvnField} as \"MaxEvn_id\",
					{$Evn_ident}
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
					case when RD.needReform = 2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end as \"isNoEdit\",
					RD.RegistryData_deleted as \"RegistryData_deleted\",
					RTrim(RD.NumCard) as \"EvnPL_NumCard\",
					RTrim(RD.Person_FIO) as \"Person_FIO\",
					RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
					RD.LpuSection_id as \"LpuSection_id\",
					RTrim(RD.LpuSection_name) as \"LpuSection_name\",
					RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
					RTrim(coalesce(to_char(cast(RD.{$setDateField} as timestamp), 'dd.mm.yyyy'),'')) as \"EvnVizitPL_setDate\",
					RTrim(coalesce(to_char(cast(RD.Evn_disDate as timestamp), 'dd.mm.yyyy'),'')) as \"Evn_disDate\",
					RD.RegistryData_Tariff as \"RegistryData_Tariff\",
					--RD.RegistryData_KdFact as RegistryData_Uet,
					{$select_uet}
					{$fields}
					{$select_mes}
					RD.RegistryData_KdPay as \"RegistryData_KdPay\",
					RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
					RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
					coalesce(RegistryError.Err_Count,0) + coalesce(RegistryErrorTfoms.Err_Count,0) as \"Err_Count\",
					PMT.PayMedType_Code as \"PayMedType_Code\",
					RD.RegistryData_IsPaid as \"RegistryData_IsPaid\",
					RHDCR.RegistryHealDepResType_id as \"RegistryHealDepResType_id\"
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD
					left join v_PayMedType PMT on PMT.PayMedType_id = RD.PayMedType_id
					left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
					left join v_RegistryHealDepCheckRes RHDCR on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
					left join lateral (
						select RDLT.Person_id from RegistryDataLgot RDLT where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null) limit 1
					) RDL on true
					left join lateral
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
					) RegistryError on true
					left join lateral
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET where RD.Evn_id = RET.Evn_id and RD.Registry_id = RET.Registry_id
					) RegistryErrorTfoms on true
					left join lateral
					(
						Select PersonEvn_id, Server_id
						from v_PersonEvn PE
						where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.{$setDateField})
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

        if (!is_object($result)) {
            return false;
        }

        $response = array();
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
    }

    /**
     * Добавление ошибки при импорте ответа от ТФОМС
     * @param $data
     * @return array|bool
     */
    public function setErrorFromTFOMSImportRegistry($data)
    {
        $params = array();
        $params['Registry_id'] = $data['Registry_id'];
        $params['pmUser_id'] = $data['pmUser_id'];
        $params['COMMENT'] = $data['COMMENT'];
        $params['Evn_id'] = $data['Evn_id'];
        $params['Evn_sid'] = $data['Evn_sid'];

        $query = "
			select
				RegistryErrorType_id as \"RegistryErrorType_id\",
				RegistryErrorType_Code as \"RegistryErrorType_Code\"
			from {$this->scheme}.RegistryErrorType
			where RegistryErrorType_Name ilike :COMMENT
			limit 1
		";
        $resp = $this->queryResult($query, $params);

        if (!$this->isSuccessful($resp)) {
            return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
        }
        if (count($resp) > 0) {
            $params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
            $params['RegistryErrorType_Code'] = $resp[0]['RegistryErrorType_Code'];
        } else {
            $query = "
				with cte as (
				    select 
				    coalesce((
                        select max(RegistryErrorType_Code)
                        from {$this->scheme}.RegistryErrorType
                        where RegistryErrorType_Code ilike '1[0-9][0-9][0-9]'
                        limit 1
				    ), 0) + 1 as  RegistryErrorType_Code
                )
                
                select
					RegistryErrorType_id as \"RegistryErrorType_id\",
					RegistryErrorType_Code as \"RegistryErrorType_Code\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_RegistryErrorType_ins
				(
					RegistryErrorType_Code := (select RegistryErrorType_Code from cte),
					RegistryErrorType_Name := :COMMENT,
					RegistryErrorType_Descr := :COMMENT,
					RegistryErrorClass_id := 1,
					pmUser_id := :pmUser_id
				)
			";
            $resp = $this->queryResult($query, $params);
            if (!$this->isSuccessful($resp)) {
                return array(array('success' => false, 'Error_Msg' => 'Не удалось добавить ошибку в справочник!'));
            }
            $params['RegistryErrorType_id'] = $resp[0]['RegistryErrorType_id'];
            $params['RegistryErrorType_Code'] = $resp[0]['RegistryErrorType_Code'];
        }

        $params['RegistryType_id'] = $data['RegistryType_id'];

        return $this->saveRegistryErrorTFOMS($params);
    }

    /**
     * Сохранение ошибки в RegistryErrorTFOMS
     */
    function saveRegistryErrorTFOMS($params) {
        $this->setRegistryParamsByType($params, true);

        $insFields = "Evn_sid,";
        $fromFields = "rd.Evn_sid,";

        if ( $params['RegistryType_id'] == 6 ) {
            $insFields = "";
            $fromFields = "";
        }

        $filter = "";

        if (!empty($params['Evn_sid'])) {
            $filter = "and rd.Evn_sid = :Evn_sid";
        }

        $query = "
			Insert {$this->scheme}.RegistryErrorTFOMS (
				Registry_id,
			 	Evn_id,
			 	{$insFields}
			 	RegistryErrorType_id,
			 	RegistryErrorType_Code,
			 	RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
			 	RegistryErrorTFOMS_Comment,
			 	pmUser_insID,
			 	pmUser_updID,
			 	RegistryErrorTFOMS_insDT,
			 	RegistryErrorTFOMS_updDT
			)
			Select
				rd.Registry_id,
				rd.Evn_id,
				{$fromFields}
				:RegistryErrorType_id as RegistryErrorType_id,
				:RegistryErrorType_Code,
				:RegistryErrorTFOMS_FieldName,
				:RegistryErrorTFOMS_BaseElement,
				:COMMENT,
				:pmUser_id,
				:pmUser_id,
				dbo.tzGetDate() as RegistryError_insDT,
				dbo.tzGetDate() as RegistryError_updDT
			from
				{$this->scheme}.v_{$this->RegistryDataObject} rd
			where
				rd.Registry_id = :Registry_id
				and rd.Evn_id = :Evn_id
				{$filter}
		";

        if (empty($params['RegistryErrorTFOMS_FieldName'])) {
            $params['RegistryErrorTFOMS_FieldName'] = null;
        }
        if (empty($params['RegistryErrorTFOMS_BaseElement'])) {
            $params['RegistryErrorTFOMS_BaseElement'] = null;
        }

        //echo getDebugSQL($query, $params);exit;
        $result = $this->db->query($query, $params);

        if ($result === true) {
            return array(array('success' => true, 'Error_Msg' => ''));
        } else {
            return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
    }

    /**
     *	Добавить ошибку для реестра из импортируемого файла
     */
    function setErrorFromImportRegistry($data)
    {
        if (!$data['Registry_id']) {
            return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
        }

        $query = "
            SELECT 
                RegistryErrorType_id as \"RegistryErrorType_id\",
                RegistryErrorType_Descr as \"RegistryErrorType_Descr\"
            FROM
                {$this->scheme}.RegistryErrorType
            WHERE
                RegistryErrorType_Code = :OSHIB
            limit 1
        ";

        $resp = $this->db->query($query, $data);

        if (!is_object($resp)) {
            return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки '.$data['OSHIB']));
        }

        $ret = $resp->result('array');

        if (is_array($ret) && (count($ret) > 0)) {
            $params = array(
                'Evn_id' => $data['Evn_id'],
                'Evn_sid' => $data['Evn_sid'],
                'Registry_id' => $data['Registry_id'],
                'RegistryErrorType_id' => $ret[0]['RegistryErrorType_id'],
                'RegistryErrorType_Code' => $data['OSHIB'],
                'COMMENT' => empty($data['COMMENT']) ? $ret[0]['RegistryErrorType_Descr'] : $data['COMMENT'],
                'RegistryType_id' => $data['RegistryType_id'],
                'pmUser_id' => $data['pmUser_id'],
                'RegistryErrorTFOMS_FieldName' => $data['IM_POL'],
                'RegistryErrorTFOMS_BaseElement' => $data['BAS_EL']
            );
            return $this->saveRegistryErrorTFOMS($params);
        } else {
            return array(array('success' => false, 'Error_Msg' => 'Код ошибки '.$data['OSHIB']. ' не найден в бд'));
        }

    }

    /**
     *    Добавить ошибку для реестра из импортируемого файла
     * @param $data
     * @return array|bool
     */
    public function setFLKErrorFromImportRegistry($data)
    {
        if (!$data['Registry_id']) {
            return array(array('success' => false, 'Error_Msg' => 'Загрузка реестра невозможна!'));
        }

        $query = "
                SELECT
                    RegistryErrorType_id as \"RegistryErrorType_id\",
                    RegistryErrorType_Descr as \"RegistryErrorType_Descr\"
                FROM
                    {$this->scheme}.RegistryErrorType
                WHERE
                    RegistryErrorType_Code = :OSHIB
                limit 1
            ";
        $resp_ret = $this->queryResult($query, array(
            'OSHIB' => $data['OSHIB']
        ));

        $RegistryErrorType_id = null;
        if (!empty($resp_ret[0]['RegistryErrorType_id'])) {
            $RegistryErrorType_id = $resp_ret[0]['RegistryErrorType_id'];
        } else {
            $query = "
					select
					    RegistryErrorType_id as \"RegistryErrorType_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_RegistryErrorType_ins
					(
						RegistryErrorType_Code := :OSHIB,
						RegistryErrorType_Name := :COMMENT,
						RegistryErrorType_Descr := :COMMENT,
						RegistryErrorClass_id := 1,
						pmUser_id := :pmUser_id
					)
				";
            $resp_ret = $this->queryResult($query, array(
                'OSHIB' => $data['OSHIB'],
                'COMMENT' => $data['COMMENT'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (empty($resp_ret[0]['RegistryErrorType_id'])) {
                return array(array('success' => false, 'Error_Msg' => 'Не удалось добавить ошибку в справочник!'));
            }
            $RegistryErrorType_id = $resp_ret[0]['RegistryErrorType_id'];
        }

        $params = array(
            'Evn_id' => $data['Evn_id'],
            'Evn_sid' => $data['Evn_sid'],
            'Registry_id' => $data['Registry_id'],
            'RegistryErrorType_id' => $RegistryErrorType_id,
            'RegistryErrorType_Code' => $data['OSHIB'],
            'COMMENT' => $data['COMMENT'],
            'RegistryType_id' => $data['RegistryType_id'],
            'pmUser_id' => $data['pmUser_id'],
            'RegistryErrorTFOMS_FieldName' => $data['IM_POL'],
            'RegistryErrorTFOMS_BaseElement' => $data['BAS_EL']
        );

        return $this->saveRegistryErrorTFOMS($params);
    }

    /**
     *    Идентификация СМО по SMO, SMO_OGRN, SMO_OK
     * @param $data
     * @return bool|mixed
     */
    public function identifyOrgSMO($data)
    {
        if (!property_exists($this, 'orgSmoStore') || !property_exists($this, 'orgSmoStoreOO')) {
            if (isset($this->textlog)) {
                $this->textlog->add('identifyOrgSMO_LoadStore');
            }
            $this->orgSmoStore = array();
            $this->orgSmoStoreOO = array();

            $query = "
				select 
					smo.OrgSMO_id as \"OrgSMO_id\",
					smo.Orgsmo_f002smocod as \"Orgsmo_f002smocod\",
					o.Org_OGRN as \"Org_OGRN\",
					o.Org_OKATO as \"Org_OKATO\"
				from
					v_OrgSMO smo
					left join v_Org o on o.Org_id = smo.Org_id
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                foreach($resp as $resp_one) {
                    if (!empty($resp_one['Orgsmo_f002smocod'])) {
                        $this->orgSmoStore[$resp_one['Orgsmo_f002smocod']] = $resp_one['OrgSMO_id'];
                    }
                    if (!empty($resp_one['Org_OGRN']) && !empty($resp_one['Org_OKATO'])) {
                        $this->orgSmoStoreOO[$resp_one['Org_OGRN'].'_'.$resp_one['Org_OKATO']] = $resp_one['OrgSMO_id'];
                    }
                }
            }
        }

        if (!empty($data['Orgsmo_f002smocod']) && !empty($this->orgSmoStore[$data['Orgsmo_f002smocod']])) {
            return $this->orgSmoStore[$data['Orgsmo_f002smocod']];
        } else if (!empty($data['Org_OGRN']) && !empty($data['Org_OKATO']) && !empty($this->orgSmoStoreOO[$data['Org_OGRN'].'_'.$data['Org_OKATO']])) {
            return $this->orgSmoStoreOO[$data['Org_OGRN'].'_'.$data['Org_OKATO']];
        }

        return false;
    }

    /**
     *    Получение идентификатор из справочника "Территории страхования"
     * @param $data
     * @return |null
     */
    public function getOmsSprTerr($data)
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

        if (is_object($result) && count($row = $result->result('array'))) {
            return $row[0]['OmsSprTerr_id'];
        }

        return null;
    }

    /**
     *  Идентификация и корректировка полисных данных
     * @param $data
     * @return int
     */
    function identifyAndAddNewPolisToPerson($data)
    {
        $this->load->model('PersonIdentRequest_model', 'identmodel');

        // 1. отправляем запрос сервису идентификации
        $this->load->library('swPersonIdentKareliyaSoap');
        $identObject = new swPersonIdentKareliyaSoap(
            $this->config->item('IDENTIFY_SERVICE_URI'),
            $this->config->item('IDENTIFY_SERVICE_LOGIN'),
            $this->config->item('IDENTIFY_SERVICE_PASS'),
            (int)$this->config->item('IS_DEBUG')
        );

        // Формирование данных для запроса к сервису БДЗ
        $requestData = array(
            'FAM' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
            'IM' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
            'OT' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
            'birthDate' => (!empty($data['Person_Birthday']) ?  $data['Person_Birthday'] : '1900-01-01'),
            'SerPolis' => null, //$data['Polis_Ser'],
            'NumPolis' => null, //$data['Polis_Num'],
            'SerDocument' => null, //$data['Document_Ser'],
            'NumDocument' => null, //$data['Document_Num'],
            'SNILS' => null, //$data['Person_SNILS'], // тут снилс надо передавать в формате "ххх-ххх-ххх хх"
            'DATEON' => (!empty($data['DATEON'])?$data['DATEON']:date('Y-m-d')),
            'Type_Request' => 0 // без признака актуальности
        );
        //var_dump($requestData); echo "<br><br>";

        // Выполнение запроса к сервису БДЗ
        $requestResponse = $identObject->doPersonIdentRequest($requestData);
        //var_dump($requestResponse);

        if (!empty($requestResponse['errorCode'])) {
            return $requestResponse['errorCode']; // не идентифицирован
        }

        $added = false;

        if (!empty($requestResponse['identData'][0]['FAM'])) {
            /*
			$requestResponse['identData'][0]['typepolis'] -- тип полиса
			$requestResponse['identData'][0]['serpolis'] -- серия полиса
			$requestResponse['identData'][0]['numpolis'] -- номер полиса
			$requestResponse['identData'][0]['vidpolic'] -- дата выдачи
			$requestResponse['identData'][0]['closepolic'] -- дата закрытия
			$requestResponse['identData'][0]['codestrah'] -- страховая организация
			*/
            $ptResponse = $this->identmodel->getPolisTypeCode($requestResponse['identData'][0]['typepolis']);
            if ( is_array($ptResponse) && count($ptResponse) > 0 && !empty($ptResponse[0]['PolisType_id'])) {
                $data['PolisType_id'] = $ptResponse[0]['PolisType_id'];
            } else {
                return 4; // не удалось определить тип полиса
            }
            $data['Polis_Ser'] = $requestResponse['identData'][0]['serpolis'];
            $data['Polis_Num'] = $requestResponse['identData'][0]['numpolis'];

            // https://redmine.swan.perm.ru/issues/43989
            // ... реализовать разделение на серию и номер временного свидетельства, аналогично тому, как это происходит при идентификации по кнопке.
            if ($data['PolisType_id'] == 3) {
                $data['Polis_Num'] = $data['Polis_Ser'].''.$data['Polis_Num'];
                $data['Polis_Ser'] = null;
            }
            $smoIdResponse = $this->identmodel->getOrgSmoIdOnCode($requestResponse['identData'][0]['codestrah']);
            if ( is_array($smoIdResponse) && count($smoIdResponse) > 0 && !empty($smoIdResponse[0]['OrgSmo_id'])) {
                $data['OrgSMO_id'] = $smoIdResponse[0]['OrgSmo_id'];
            } else {
                return 5; // не удалось опредеилть СМО
            }
            $data['Polis_begDate'] = $requestResponse['identData'][0]['vidpolic'];
            $data['Polis_endDate'] = ((!empty($requestResponse['identData'][0]['closepolic']) && mb_substr($requestResponse['identData'][0]['closepolic'], 0, 10) != "1899-12-30")?$requestResponse['identData'][0]['closepolic']:null);

            // если единый номер и номер полиса не 16 знаков, то "не удалось определить тип полиса"
            if ($data['PolisType_id'] == 4 && mb_strlen($data['Polis_Num']) < 16) {
                return 4;
            }

            // проверяем есть ли у человека такой полис в PersonPolis, если нет добавляем
            $query = "
				select
					PersonPolis_id as \"PersonPolis_id\",
					coalesce(Polis_Ser, '') as \"Polis_Ser\",
					coalesce(Polis_Num, '') as \"Polis_Num\"
				from
					v_PersonPolis
				where
					Person_id = :Person_id
					and PolisType_id = :PolisType_id
					and OrgSMO_id = :OrgSMO_id
					and Polis_begDate = :Polis_begDate
				limit 1
			";
            //		and coalesce(Polis_Num, '') = coalesce(:Polis_Num, '')
            //		and coalesce(Polis_Ser, '') = coalesce(:Polis_Ser, '')
            $result = $this->db->query($query, $data);
            if (is_object($result)) {
                $resp = $result->result('array');

                if ( is_array($resp) && count($resp) > 0 ) {
                    // Если серия и номер не совпадают, то обновляем
                    if (
                        ($resp[0]['Polis_Ser'] != $data['Polis_Ser'] || $resp[0]['Polis_Num'] != $data['Polis_Num'])
                        && (empty($data['Polis_endDate']) || $data['Polis_endDate'] >= $data['Polis_begDate'])
                    ) {
                        $data['PersonPolis_id'] = $resp[0]['PersonPolis_id'];

                        $query = "
                            select
                                PersonPolis_id as PersonPolis_id
							from p_PersonPolis_upd
							(
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
							)
						";
                        $result = $this->db->query($query, $data);
                        $resp = $result->result('array');

                        $added = true;
                    } else {
                        $resp[0]['PersonPolis_id'] = null;
                    }
                }
                // Если документа ОМС нет, то добавляем
                else {
                    $query = "
						select
						    PersonPolis_id as PersonPolis_id;
						from p_PersonPolis_ins
						(
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
						)
					";
                    if(empty($data['Polis_endDate']) || $data['Polis_endDate']>=$data['Polis_begDate']){
                        $result = $this->db->query($query, $data);
                        $resp = $result->result('array');

                        $added = true;
                    }
                }

                // если вставили открытый полис, то все остальные открытые закрываем датой открытия нового минус один день
                if (!empty($resp[0]['PersonPolis_id']) && empty($data['Polis_endDate'])) {
                    $query = "
						update
							p
						set
							p.Polis_endDate = :Polis_endDate
						from
							Polis p
							inner join v_PersonPolis pp on pp.Polis_id = p.Polis_id
						where
							pp.Person_id = :Person_id and pp.PersonPolis_id <> :PersonPolis_id and p.Polis_endDate is null
					";

                    $this->db->query($query, array(
                        'PersonPolis_id' => $resp[0]['PersonPolis_id'],
                        'Person_id' => $data['Person_id'],
                        'Polis_endDate' => date('Y-m-d', (strtotime($data['Polis_begDate']) - 60*60*24))
                    ));
                }
            }
            // для единого номера полиса проверяем есть ли у человека такой полис в PersonPolisEdNum, если нет добавляем
            if ($data['PolisType_id'] == 4) {
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
                $result = $this->db->query($query, $data);
                if (is_object($result)) {
                    $resp = $result->result('array');

                    if (empty($resp[0]['PersonPolisEdNum_id'])) {
                        $query = "
                            select
                                PersonPolisEdNum_id as PersonPolisEdNum_id
                            from p_PersonPolisEdNum_ins
                            (
                                Server_id := :Server_id,
                                Person_id := :Person_id,
                                PersonPolisEdNum_EdNum := :Polis_Num,
                                PersonPolisEdNum_begDT := :Polis_begDate,
                                PersonPolisEdNum_insDT := :Polis_begDate,
                                pmUser_id := :pmUser_id
                            )
                        ";

                        $this->db->query($query, $data);
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
					from xp_PersonAllocatePersonEvnByEvn
					(
						Person_id := :Person_id
					)
				";

                $this->db->query($query, $data);
            }
        } else {
            return 1; // не идентифицирован
        }

        if ($added) {
            return 6; // запись изменена
        }

        return 7; // полис уже был добавлен
    }

    /**
     *    Добавление полиса пациенту
     * @param $data
     * @return bool
     */
    function addNewPolisToPerson($data)
    {
        if (!empty($data['oldPolis'])) {
            /*$query = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_PersonPolis_upd
				@PersonPolis_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@OmsSprTerr_id = :OmsSprTerr_id,
				@PolisType_id = :PolisType_id,
				@OrgSMO_id = :OrgSMO_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@Polis_begDate = :Polis_begDate,
				@Polis_endDate = :Polis_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";*/
            $query = "update Polis set Polis_endDate = :Polis_endDate where Polis_id = :Polis_id";
            $this->db->query($query, $data['oldPolis']);
        }

        //Полис единого образца
        if ($data['PolisType_id'] == 4) {
            $data['PersonPolisEdNum_EdNum'] = $data['Polis_Num'];
            $data['Polis_Ser'] = '';
            $data['Polis_Num'] = '';

            $query = "
				select
				    PersonEvn_id as \"PersonEvn_id\"
				from p_PersonPolisEdNum_ins
				(
				    PersonPolisEdNum_id = @PersonEvn_id output,
                    Server_id := :Server_id,
                    Person_id := :Person_id,
                    PersonPolisEdNum_EdNum := :PersonPolisEdNum_EdNum,
                    PersonPolisEdNum_begDT := :Polis_begDate,
                    PersonPolisEdNum_insDT := :Polis_begDate,
                    pmUser_id := :pmUser_id
			    )
			";

            $result = $this->db->query($query, $data);
            if (!is_object($result)) {
                return false;
            }
            $resp = $result->result('array');
        }

        $query = "
			select
			    PersonEvn_id as \"PersonEvn_id\"
			from p_PersonPolis_ins
			(
                Server_id := :Server_id,
                Person_id := :Person_id,
                OmsSprTerr_id := :OmsSprTerr_id,
                PolisType_id := :PolisType_id,
                OrgSMO_id := :OrgSMO_id,
                Polis_Ser := :Polis_Ser,
                Polis_Num := :Polis_Num,
                Polis_begDate := :Polis_begDate,
                Polis_endDate := NULL,
                PersonPolis_insDT := :Polis_begDate,
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
                    'PersonEvn_id' => $resp[0]['PersonEvn_id'],
                    'PersonEvn_begDT' => $data['Polis_begDate'],
                    'pmUser_id' => $data['pmUser_id']
                );

                // перевязываем случай на новую периодику
                /*$query = "
					update
						Evn
					set
						PersonEvn_id = :PersonEvn_id,
						Server_id = :Server_id
					where
						Evn_id = :Evn_id
				";*/

                $query = "
					select
					    Error_Code as \"Error_Code\",
					    Error_Message as \"Error_Msg\"					
					from xp_PersonAllocatePersonEvnByEvn
					(
						Person_id := :Person_id
					)
				";

                $this->db->query($query, $data);

                return true;
            }
        }

        return false;
    }

    /**
     *    Идентификация типа полиса по VPOLIS
     * @param $data
     * @return bool|mixed
     */
    public function identifyPolisType($data)
    {
        if (!property_exists($this, 'polisTypeStore')) {
            if (isset($this->textlog)) {
                $this->textlog->add('identifyPolisType_LoadStore');
            }
            $this->polisTypeStore = array();

            $query = "
				select 
					pt.PolisType_id as \"PolisType_id\",
					pt.PolisType_CodeF008 as \"PolisType_CodeF008\"
				from
					v_PolisType pt
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                foreach($resp as $resp_one) {
                    if (!empty($resp_one['PolisType_CodeF008'])) {
                        $this->polisTypeStore[$resp_one['PolisType_CodeF008']] = $resp_one['PolisType_id'];
                    }
                }
            }
        }

        if (!empty($data['PolisType_CodeF008']) && !empty($this->polisTypeStore[$data['PolisType_CodeF008']])) {
            return $this->polisTypeStore[$data['PolisType_CodeF008']];
        }

        return false;
    }

    /**
     *    Установка реестра в очередь на формирование
     *    Возвращает номер в очереди
     * @param $data
     * @return array
     */
    public function saveRegistryQueue($data)
    {
        if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) ) {
            return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
        }

        // Сохранение нового реестра
        if (0 == $data['Registry_id']) {
            $data['Registry_IsActive']=2;
            $operation = 'insert';
        } else {
            $operation = 'update';
        }

        $re = $this->loadRegistryQueue($data);
        if (is_array($re) && (count($re) > 0)) {
            if ($operation=='update') {
                if ($re[0]['RegistryQueue_Position']>0) {
                    return array(array('success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
                }
            }
        }

        $params = array
        (
            'Registry_id' => $data['Registry_id'],
            'Lpu_id' => $data['Lpu_id'],
            'OrgSMO_id' => $data['OrgSMO_id'],
            'RegistryType_id' => $data['RegistryType_id'],
            'RegistryStatus_id' => $data['RegistryStatus_id'],
            'RegistryStacType_id' => $data['RegistryStacType_id'],
            'Registry_begDate' => $data['Registry_begDate'],
            'Registry_endDate' => $data['Registry_endDate'],
            'Registry_Num' => $data['Registry_Num'],
            'Registry_IsActive' => $data['Registry_IsActive'],
            'OrgRSchet_id' => $data['OrgRSchet_id'],
            'Registry_accDate' => $data['Registry_accDate'],
            'DispClass_id' => $data['DispClass_id'],
            'PayType_id' => $data['PayType_id'],
            'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
            'Registry_IsZNO' => $data['Registry_IsZNO'],
            'pmUser_id' => $data['pmUser_id']
        );
        $fields = "";

        $params['KatNasel_id'] = $data['KatNasel_id'];
        $fields .= "KatNasel_id := :KatNasel_id,";

        switch ($data['RegistryType_id'])
        {
            case 1:
            case 14:
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
                $fields .= "LpuBuilding_id := :LpuBuilding_id,";
                break;
            case 2:
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
                $fields .= "LpuBuilding_id := :LpuBuilding_id,";
                // Переформирование по записям, пока только на полке
                if (isset($data['reform']))
                {
                    $params['reform'] = $data['reform'];
                    $fields .= "reform := :reform,";
                }
                break;
            default:
                break;
        }

        $query = "
		
            select
                RegistryQueue_id as \"RegistryQueue_id\",
                RegistryQueue_Position as \"RegistryQueue_Position\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"			
			from {$this->scheme}.p_RegistryQueue_ins
			(
				RegistryQueue_id := null,
				RegistryQueue_Position := null,
				RegistryStacType_id := :RegistryStacType_id,
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				Lpu_id := :Lpu_id,
				OrgSMO_id := :OrgSMO_id,
				OrgRSchet_id := :OrgRSchet_id,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				DispClass_id := :DispClass_id,
				PayType_id := :PayType_id,
				{$fields}
				Registry_Num := :Registry_Num,
				Registry_accDate := dbo.tzGetDate(),
				RegistryStatus_id := :RegistryStatus_id,
				Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
				Registry_IsZNO := :Registry_IsZNO,
				pmUser_id := :pmUser_id
			)
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        }

        return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
    }

    /**
     * Получение SL_ID по N_ZAP
     * @param $data
     * @return array|bool
     */
    public function getSLByNZAP($data)
    {
        $SLIDs = array();
        if (empty($this->Registry_EvnNum)) {
            // достаём массив Registry_EvnNum, ищем Evn_id по N_ZAP
            $query = "
                select
                    Registry_EvnNum as \"Registry_EvnNum\"
                from
                    {$this->scheme}.v_Registry
                where
                    Registry_id = :Registry_id
                limit 1
            ";
            $result = $this->db->query($query, $data);

            if (!is_object($result)) {
                return false;
            }

            $row = $result->result('array');

            if (!is_array($row) || count($row) == 0 || empty($row[0]['Registry_EvnNum'])) {
                return false;
            }

            $this->Registry_EvnNum = json_decode($row[0]['Registry_EvnNum'], true);

            unset($row);
        }

        if (!empty($this->Registry_EvnNum)) {
            foreach ($this->Registry_EvnNum as $SL_ID => $oneEvnNum) {
				if (isset($oneEvnNum[0])) {
					$oneEvnNum = $oneEvnNum[0];
				}
                if (isset($oneEvnNum['N_ZAP']) && $oneEvnNum['N_ZAP'] == $data['N_ZAP']) {
                    $SLIDs[] = $SL_ID;
                }
            }
        }

        return $SLIDs;
    }

    /**
     *    Какая-то проверка
     * @param $data
     * @param bool $isLite
     * @return array|bool|mixed
     */
    public function checkErrorDataInRegistry($data, $isLite = true)
    {
        $this->setRegistryParamsByType($data, true);

        $data['Evn_sid'] = null;

        if (!empty($data['ID_PAC'])) {
            $data['Evn_id'] = $data['ID_PAC'];
        } else {
            if ( empty($this->Registry_EvnNum) ) {
                // достаём массив Registry_EvnNum, ищем Evn_id по N_ZAP
                $query = "
					select
					    Registry_EvnNum as \"Registry_EvnNum\"
					from
					    {$this->scheme}.v_Registry
					where
					    Registry_id = :Registry_id
					limit 1
				";
                $result = $this->db->query($query, $data);

                if ( !is_object($result) )  {
                    return false;
                }

                $row = $result->result('array');

                if ( !is_array($row) || count($row) == 0 || empty($row[0]['Registry_EvnNum']) ) {
                    return false;
                }

                $this->Registry_EvnNum = json_decode($row[0]['Registry_EvnNum'], true);

                unset($row);
            }

            if ( !empty($this->Registry_EvnNum[$data['N_ZAP']]) ) {
                if (is_array($this->Registry_EvnNum[$data['N_ZAP']])) {
                    $data['Evn_id'] = $this->Registry_EvnNum[$data['N_ZAP']]['Evn_id'];
                    if (!empty($this->Registry_EvnNum[$data['N_ZAP']]['Evn_sid'])) {
                        $data['Evn_sid'] = $this->Registry_EvnNum[$data['N_ZAP']]['Evn_sid'];
                    }
                } else {
                    $data['Evn_id'] = $this->Registry_EvnNum[$data['N_ZAP']];
                }
            } else {
                return false;
            }
        }

        $params = array();
        $params['Registry_id'] = $data['Registry_id'];
        $params['Evn_id'] = $data['Evn_id'];

        $additionalFilter = "";
        $dateFilter = "";
        $oapply = "";

        if (!empty($data['Evn_sid'])) {
            $params['Evn_sid'] = $data['Evn_sid'];
            $additionalFilter .= " and rd.Evn_sid = :Evn_sid";
        } else {
            if (!empty($data['setDT']) && (empty($data['DEST_CODE']) || $data['DEST_CODE'] != '31')) { // для DEST_CODE = 31 дату DATE_1 не проверяем. refs #93664
                $params['setDT'] = mb_substr($data['setDT'], 0, 10); // с 2017-го года дата в формате 2017-02-16T14:34:00, надо отбросить время
                $dateFilter .= " and cast(rd.Evn_setDate as date) = :setDT";
            }

            if (!empty($data['disDT'])) {
                $params['disDT'] = mb_substr($data['disDT'], 0, 10); // с 2017-го года дата в формате 2017-02-16T14:34:00, надо отбросить время
                $dateFilter .= " and cast(rd.Evn_disDate as date) = :disDT";
            }

            if (!empty($data['PODR'])) {
                $params['PODR'] = $data['PODR'];
                $additionalFilter .= " and coalesce(substring(rtrim(rd.LpuSection_Code),3, length(rtrim(rd.LpuSection_Code))), :PODR) = :PODR";
            }

            if (!empty($data['NOVOR']) && mb_strlen($data['NOVOR']) > 1) {
                $params['NOVOR'] = mb_substr($data['NOVOR'], -1);
                $additionalFilter .= " and CAST(coalesce(NewBorn.PersonNewBorn_CountChild,coalesce(BirthSvid.BirthSvid_ChildCount,1)) AS CHAR(1)) = :NOVOR";

                // жестокий кусок из реестров, необходимый для определения правильного случая ребенка.
                $oapply = "
					left join lateral (
						select
						    rd1.Evn_id,
						    BirthSpecStac_OutcomDT,
						    BirthSpecStac_id
						from v_BirthSpecStac BBS
						    inner join r10.RegistryDataEvnPS rd1 on rd1.Evn_id = BBS.EvnSection_id and rd1.Registry_id = rd.Registry_id
						where
						    rd1.Evn_rid = rd.Evn_rid
                        and
                            rd1.EvnSection_NumGroup1 = rd.EvnSection_NumGroup1
						and
						    rd1.Evn_sid = rd.Evn_sid
						limit 1
					) BBS on true
					left join lateral (
						select
						    EvnPSC.EvnPS_id, Person_BirthDay,sex_id,EvnPSC.person_id,Diag_Code,PersonState.Person_SurName,PersonState.Person_FirName,PersonState.Person_SecName
						from  v_PersonNewBorn PersonNewBorn  
						inner join v_EvnPS EvnPSC on  EvnPSC.EvnPS_id=PersonNewBorn.EvnPS_id
						inner join v_EvnSection ES_Child on rd.Evn_sid = ES_Child.EvnSection_id and ES_Child.EvnSection_pid = EvnPSC.EvnPS_id
						inner join v_personstate PersonState  on EvnPSC.person_id=PersonState.person_id
						inner join v_diag diagchild on EvnPSC.diag_id=diagchild.diag_id
							where  PersonNewBorn.BirthSpecStac_id = BBS.BirthSpecStac_id
					) EvnPS_Child on true
					left join lateral (
						select
					        PersonNewBorn.PersonNewBorn_CountChild
						from
						    PersonNewBorn 
						where
						    PersonNewBorn.Person_id=coalesce(EvnPS_Child.person_id,rd.Person_id)
						order by PersonNewBorn_id desc
						limit 1
					) NewBorn on true
					left join lateral (
						select
						    BS.BirthSvid_ChildCount
						from
						    v_BirthSvid BS 
						where  BS.Person_id=coalesce(EvnPS_Child.person_id,rd.Person_id)
						order by BirthSvid_id desc
						limit 1
					) BirthSvid on true
				";
            }
        }

        $EvnData = array();
		$filter = "";

		if (isset($data['isSMP'])) {
			$filter = "and r.RegistryType_id " . ($data['isSMP'] === true ? "=" : "<>") . " 6";
		}

        $registryList = $this->queryResult("
			select
				r.Registry_id as \"Registry_id\",
				r.RegistryType_id as \"RegistryType_id\"
			from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
			where
				RGL.Registry_pid = :Registry_id
				{$filter}
		", $params);

        if ( !is_array($registryList) || count($registryList) == 0 ) {
            return false;
        }

        $registryListByType = array();

        foreach ( $registryList as $row ) {
            if ( !isset($registryListByType[$row['RegistryType_id']]) ) {
                $registryListByType[$row['RegistryType_id']] = array();
            }

            $registryListByType[$row['RegistryType_id']][] = $row['Registry_id'];
        }

        foreach ( $registryListByType as $RegistryType_id => $RegistryArray ) {
            $this->setRegistryParamsByType(array('RegistryType_id' => $RegistryType_id), true);

            if ( $this->RegistryType_id == 6 ) {
                $EvnClassCodeField = '111 as "EvnClass_Code"';
                $join = "";
            }
            else {
                $EvnClassCodeField = 'ec.EvnClass_Code as "EvnClass_Code"';
                $join = "
					inner join Evn e on e.Evn_id = rd.Evn_id
					inner join EvnClass ec on ec.EvnClass_id = e.EvnClass_id
				";
            }

            $EvnData = $this->getFirstRowFromQuery("
				-- LINE " . __LINE__ . "
				select
					rd.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					rd.Evn_id as \"Evn_id\",
					rd.MaxEvn_id as \"MaxEvn_id\",
					rd.Evn_rid as \"Evn_rid\",
					rd.Evn_sid as \"Evn_sid\",
					rd.RegistryType_SysNick as \"RegistryType_SysNick\",
					'Evn_id' as \"IdField\",
					{$EvnClassCodeField}
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id
					{$join}
				where
					RGL.Registry_pid = :Registry_id
					and rd.Evn_id = :Evn_id
				limit 1
			", $params);

            if ( is_array($EvnData) && count($EvnData) > 0 ) {
                break;
            }
        }

        if ( !is_array($EvnData) || count($EvnData) == 0 ) {
            foreach ( $registryListByType as $RegistryType_id => $RegistryArray ) {
                $this->setRegistryParamsByType(array('RegistryType_id' => $RegistryType_id), true);

                if ( $this->RegistryType_id == 6 ) {
                    $EvnClassCodeField = '111 as "EvnClass_Code"';
                    $join = "";
                }
                else {
                    $EvnClassCodeField = 'ec.EvnClass_Code as "EvnClass_Code"';
                    $join = "
						inner join Evn e on e.Evn_id = rd.Evn_id
						inner join EvnClass ec on ec.EvnClass_id = e.EvnClass_id
					";
                }

                $EvnData = $this->getFirstRowFromQuery("
					-- LINE " . __LINE__ . "
					select
						rd.Registry_id as \"Registry_id\",
						r.RegistryType_id as \"RegistryType_id\",
						rd.Evn_id as \"Evn_id\",
						rd.MaxEvn_id as \"MaxEvn_id\",
						rd.Evn_rid as \"Evn_rid\",
						rd.Evn_sid as \"Evn_sid\",
						rd.RegistryType_SysNick as \"RegistryType_SysNick\",
						'Evn_rid' as \"IdField\",
						{$EvnClassCodeField}
					from
						{$this->scheme}.v_RegistryGroupLink RGL
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
						inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id
						{$join}
						{$oapply}
					where
						RGL.Registry_pid = :Registry_id
						and rd.Evn_rid = :Evn_id
						{$additionalFilter}
						{$dateFilter}
					limit 1
				", $params);

                if ( is_array($EvnData) && count($EvnData) > 0 ) {
                    break;
                }
            }
        }

        if ( !is_array($EvnData) || count($EvnData) == 0 ) {
            $EvnData = $this->getFirstRowFromQuery("
				-- LINE " . __LINE__ . "
				select
					rd.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					e.Evn_id as \"Evn_id\",
					rd.MaxEvn_id as \"MaxEvn_id\",
					rd.Evn_rid as \"Evn_rid\",
					rd.Evn_sid as \"Evn_sid\",
					rt.RegistryType_SysNick as \"RegistryType_SysNick\",
					'Evn_id' as \"IdField\",
					ec.EvnClass_Code as \"EvnClass_Code\"
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					inner join {$this->scheme}.RegistryDataEvnPS rd on rd.Registry_id = r.Registry_id
						and Number is not null
					inner join v_EvnSection es on  es.EvnSection_id = rd.Evn_sid
					inner join v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid
					inner join RegistryType rt on rt.RegistryType_id = r.RegistryType_id
					inner join Evn e on e.Evn_id = rd.Evn_id
					inner join EvnClass ec on ec.EvnClass_id = e.EvnClass_id
					{$oapply}
				where
					RGL.Registry_pid = :Registry_id
					and es.EvnSection_pid = :Evn_id
					{$additionalFilter}
					{$dateFilter}
				limit 1
			", $params);
        }

        if ( $isLite === true || !is_array($EvnData) || count($EvnData) == 0 ) {
            return $EvnData;
        }

        $params['Evn_id'] = $EvnData['Evn_id'];
        $params['Registry_id'] = $EvnData['Registry_id'];

        $this->setRegistryParamsByType(array('RegistryType_id' => $EvnData['RegistryType_id']), true);

        $personQuery1 = "
			inner join lateral (
				select
					pbdz.PersonEvn_id,
					pbdz.Server_id,
					pbdz.Person_BirthDay,
					pbdz.Polis_id,
					pbdz.Person_SurName,
					pbdz.Person_FirName,
					pbdz.Person_SecName,
					pbdz.Person_id,
					pbdz.Person_EdNum
				from
					v_Person_bdz pbdz
				where
					pbdz.Person_id = rd.Person_id
					and pbdz.PersonEvn_insDT <= cast(rd.Evn_setDate as date)
				order by pbdz.PersonEvn_insDT desc
			    limit 1
			) ps on true
		";
        $personQuery2 = "
			inner join v_Person_reg ps on ps.PersonEvn_id = e.PersonEvn_id AND ps.Server_id = e.Server_id
		";
        if (!empty($data['IsDeputy']) && $data['IsDeputy'] == 2) {
            // надо брать данные с представителя
            $personQuery1 = "
				inner join lateral (
					select
						pbdz.PersonEvn_id,
						pbdz.Server_id,
						pbdz.Person_BirthDay,
						pbdz.Polis_id,
						pbdz.Person_SurName,
						pbdz.Person_FirName,
						pbdz.Person_SecName,
						pbdz.Person_id,
						pbdz.Person_EdNum
					from
						PersonDeputy PDEP
						inner join v_Person_bdz pbdz on pbdz.Person_id = PDEP.Person_pid
					where
						PDEP.Person_id = rd.Person_id
						and pbdz.PersonEvn_insDT <= cast(rd.Evn_setDate as date)
					order by pbdz.PersonEvn_insDT desc
				    limit 1
				) ps on true
			";
            $personQuery2 = "
				inner join lateral (
					select
						pbdz.PersonEvn_id,
						pbdz.Server_id,
						pbdz.Person_BirthDay,
						pbdz.Polis_id,
						pbdz.Person_SurName,
						pbdz.Person_FirName,
						pbdz.Person_SecName,
						pbdz.Person_id,
						pbdz.Person_EdNum
					from
						PersonDeputy PDEP
						inner join v_Person_bdz pbdz on pbdz.Person_id = PDEP.Person_pid
					where
						PDEP.Person_id = rd.Person_id
						and pbdz.PersonEvn_insDT <= cast(rd.Evn_disDate as date)
					order by pbdz.PersonEvn_insDT desc
					limit 1
				) ps on true
			";
        }

        switch ( $this->RegistryType_id ) {
            case 6:
                $query = "
					-- LINE " . __LINE__ . "
					select
						rd.Registry_id as \"Registry_id\",
						r.RegistryType_id as \"RegistryType_id\",
						ps.PersonEvn_id as \"PersonEvn_id\",
						ps.Person_id as \"Person_id\",
						rd.Evn_id as \"Evn_id\",
						111 as \"EvnClass_Code\",
						rd.MaxEvn_id as \"MaxEvn_id\",
						rd.Evn_rid as \"Evn_rid\",
						rd.Evn_sid as \"Evn_sid\",
						rd.RegistryType_SysNick as \"RegistryType_SysNick\",
						pol.Polis_id as \"Polis_id\",
						pol.Server_id as \"Server_id\",
						pol.Polis_Ser as \"Polis_Ser\",
						ps.Person_Surname as \"Person_Surname\",
						ps.Person_Firname as \"Person_Firname\",
						ps.Person_Secname as \"Person_Secname\",
						to_char(ps.Person_Birthday, 'yyyy-mm-dd') as \"Person_Birthday\",
						to_char(rd.Evn_disDate, 'yyyy-mm-dd') as \"Evn_disDate\",
						case when pol.PolisType_id = 4 then ps.Person_EdNum else pol.Polis_Num end as \"Polis_Num\",
						pol.OmsSprTerr_id as \"OmsSprTerr_id\",
						pol.PolisType_id as \"PolisType_id\",
						pol.OrgSMO_id as \"OrgSMO_id\",
						pol.PolisType_id as \"PolisType_id\",
						to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
						to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
						to_char(pol.Polis_endDate, 'dd.mm.yyyy') as \"Polis_endDate\",
						coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
					from
						{$this->scheme}.v_RegistryDataCmp rd
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
						{$personQuery1}
						left join v_Polis pol on pol.Polis_id = ps.Polis_id
					where
						r.Registry_id = :Registry_id
						and rd.Evn_id = :Evn_id
					limit 1
				";
                break;

            default:
                $filterSid = "";
                if (!empty($EvnData['Evn_sid'])) {
                    $filterSid = " and RD.Evn_sid = :Evn_sid";
                    $params['Evn_sid'] = $EvnData['Evn_sid'];
                }

                $query = "
					-- LINE " . __LINE__ . "
					select
						rd.Registry_id as \"Registry_id\",
						r.RegistryType_id as \"RegistryType_id\",
						e.PersonEvn_id as \"PersonEvn_id\",
						e.Person_id as \"Person_id\",
						e.Evn_id as \"Evn_id\",
						ec.EvnClass_Code as \"EvnClass_Code\",
						rd.MaxEvn_id as \"MaxEvn_id\",
						rd.Evn_rid as \"Evn_rid\",
						rd.Evn_sid as \"Evn_sid\",
						rd.RegistryType_SysNick as \"RegistryType_SysNick\",
						pol.Polis_id as \"Polis_id\",
						pol.Server_id as \"Server_id\",
						pol.Polis_Ser as \"Polis_Ser\",
						ps.Person_Surname as \"Person_Surname\",
						ps.Person_Firname as \"Person_Firname\",
						ps.Person_Secname as \"Person_Secname\",
						to_char(ps.Person_Birthday, 'yyyy-mm-dd') as Person_Birthday,
						to_char( case when ec.EvnClass_SysNick = 'EvnVizitPL' then rd2.Evn_disDate else rd2.Evn_disDate end, 'yyyy-mm-dd') as \"Evn_disDate\",
						case when pol.PolisType_id = 4 then ps.Person_EdNum else pol.Polis_Num end as \"Polis_Num\",
						pol.OmsSprTerr_id as \"OmsSprTerr_id\",
						pol.PolisType_id as \"PolisType_id\",
						pol.OrgSMO_id as \"OrgSMO_id\",
						pol.PolisType_id as \"PolisType_id\",
						to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
						to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
						to_char(pol.Polis_endDate, 'dd.mm.yyyy') as \"Polis_endDate\",
						coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
						inner join Evn e on e.Evn_id = rd.Evn_id
						inner join EvnClass ec on ec.EvnClass_id = e.EvnClass_id
						{$personQuery2}
						left join {$this->scheme}.{$this->RegistryDataObject} rd2 on rd2.Registry_id = rd.Registry_id and rd2.Evn_id = rd.Evn_id -- дата конца случая по поликлинике берётся отсюда (а именно конец ТАП)
						left join v_Polis pol on pol.Polis_id = ps.Polis_id
					where
						r.Registry_id = :Registry_id
						and rd.{$EvnData['IdField']} = :IdField
						{$filterSid}
					limit 1
				";
                break;
        }

        $params['IdField'] = $EvnData[$EvnData['IdField']];

        //$params['Registry_id'] = $data['Registry_id'];
        //$params['Evn_id'] = $data['Evn_id'];
        // echo getDebugSQL($query, $params);
        $row = $this->getFirstRowFromQuery($query, $params);

        if ( is_array($row) && count($row) > 0 ) {
            return $row; // возвращаем данные о случае
        }

        return false;
    }

    /**
     *    Поиск записи в реестре по SL_ID
     * @param $data
     * @return array|bool
     */
    public function checkErrorDataInRegistryBySLID($data)
    {
        $params = array();
        $params['Registry_id'] = $data['Registry_id'];
        $params['Evn_sid'] = $data['SL_ID'];

        $EvnData = array();

		$filter = "";

		if (isset($data['isSMP'])) {
			$filter = "and r.RegistryType_id " . ($data['isSMP'] === true ? "=" : "<>") . " 6";
		}

		$registryList = $this->queryResult("
			select
				r.Registry_id as \"Registry_id\",
				r.RegistryType_id as \"RegistryType_id\"
			from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
			where
				RGL.Registry_pid = :Registry_id
				{$filter}
		", $params);

        if ( !is_array($registryList) || count($registryList) == 0 ) {
            return false;
        }

        $registryListByType = array();

        foreach ( $registryList as $row ) {
            if ( !isset($registryListByType[$row['RegistryType_id']]) ) {
                $registryListByType[$row['RegistryType_id']] = array();
            }

            $registryListByType[$row['RegistryType_id']][] = $row['Registry_id'];
        }

        foreach ( $registryListByType as $RegistryType_id => $RegistryArray ) {
            $this->setRegistryParamsByType(array('RegistryType_id' => $RegistryType_id), true);

            if ( $this->RegistryType_id == 6 ) {
                $join = "
					left join v_CmpCloseCard clc on clc.CmpCallCard_id = rd.Evn_id
				";
                $select = '111 as \"EvnClass_Code\", clc.CmpCloseCard_id as \"CmpCloseCard_id\"';
                $where = 'and rd.Evn_id = :Evn_sid';
            }
            else {
                $join = "
					inner join Evn e on e.Evn_id = rd.Evn_id
					inner join EvnClass ec on ec.EvnClass_id = e.EvnClass_id
				";
                $select = 'ec.EvnClass_Code as \"EvnClass_Code\", null as \"CmpCloseCard_id\"';
                $where = 'and rd.Evn_sid = :Evn_sid';
            }

            $EvnData = $this->getFirstRowFromQuery("
				select
					rd.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					rd.Evn_id as \"Evn_id\",
					rd.MaxEvn_id as \"MaxEvn_id\",
					rd.Evn_rid as \"Evn_rid\",
					rd.Evn_sid as \"Evn_sid\",
					rd.RegistryType_SysNick as \"RegistryType_SysNick\",
					{$select}
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id
					{$join}
				where
					RGL.Registry_pid = :Registry_id
					{$where}
				limit 1
			", $params);

            if ( is_array($EvnData) && count($EvnData) > 0 ) {
                break;
            }
        }

        return $EvnData;
    }

    /**
     *    Получение списка ошибок ТФОМС
     * @param $data
     * @return array|bool
     */
    public function loadRegistryErrorTFOMS($data)
    {

        $filterAddQueryTemp = null;
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
                            $column = "rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, ''))";//'RE.'.$column;
                        elseif($column == 'LpuSection_Name')
                            $column = 'LS.'.$column;
                        elseif($column == 'RegistryErrorType_Code')
                            $column = 'ret.'.$column;
                        elseif($column == 'Evn_ident') {
                            $column = 'Evn.Evn_rid';
                            if ($this->RegistryType_id == 1) {
                                $column = 'RE.Evn_sid';
                            }
                            else if ($this->RegistryType_id == 14) {
                                $column = 'RE.Evn_id';
                            }
                        }

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

        $this->setRegistryParamsByType($data);
        //$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));

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

        $leftjoin = "";

        if ($this->RegistryType_id == 14) {
            $leftjoin .= " left join v_EvnSection es2 on es2.EvnSection_id = RE.Evn_sid ";
            $Evn_ident = "ES2.EvnSection_pid as Evn_ident,";
            if (!empty($data['Evn_id'])) {
                $filter .= " and ES2.EvnSection_pid = :Evn_id";
                $params['Evn_id'] = $data['Evn_id'];
            }
        } else if ($this->RegistryType_id == 1) {
            $Evn_ident = "RE.Evn_sid as Evn_ident,";
            if (!empty($data['Evn_id'])) {
                $filter .= " and RE.Evn_sid = :Evn_id";
                $params['Evn_id'] = $data['Evn_id'];
            }
        } else {
            $Evn_ident = "RE.Evn_id as Evn_ident,";
            if (!empty($data['Evn_id'])) {
                $filter .= " and RE.Evn_id = :Evn_id";
                $params['Evn_id'] = $data['Evn_id'];
            }
        }

        if (!empty($data['RegistryErrorTFOMS_Comment']))
        {
            $filter .= " and RE.RegistryErrorTFOMS_Comment like '%'||:RegistryErrorTFOMS_Comment||'%'";
            $params['RegistryErrorTFOMS_Comment'] = $data['RegistryErrorTFOMS_Comment'];
        }

        $addToSelect = "";

        if ( in_array($this->RegistryType_id, array(7, 9, 12)) ) {
            $leftjoin .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
            $addToSelect .= ",epd.DispClass_id as \"DispClass_id\"";
        }

        switch ( $this->RegistryType_id ) {
            case 6:
                $query = "
					Select 
						-- select
						RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
						RE.Registry_id as \"Registry_id\",
						null as \"Evn_rid\",
						RE.Evn_id as \"Evn_id\",
						RE.Evn_id as \"Evn_ident\",
						clc.CmpCloseCard_id as \"CmpCloseCard_id\",
						null as \"EvnClass_id\",
						ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
						rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
						ps.Person_id as \"Person_id\", 
						ps.PersonEvn_id as \"PersonEvn_id\", 
						ps.Server_id as \"Server_id\", 
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						RE.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
						RE.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
						RE.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
						MP.Person_Fio as \"MedPersonal_Fio\",
						LB.LpuBuilding_Name as \"LpuBuilding_Name\",
						LS.LpuSection_Name as \"LpuSection_Name\",
						coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
						case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
						{$addToSelect}
						-- end select
					from 
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join v_CmpCallCard ccc on ccc.CmpCallCard_id = RE.Evn_id
						left join v_CmpCloseCard clc on clc.CmpCallCard_id = ccc.CmpCallCard_id
						left join v_LpuSection LS on LS.LpuSection_id = coalesce(clc.LpuSection_id, ccc.LpuSection_id)
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
						left join lateral (
							select Person_Fio from v_MedPersonal where MedPersonal_id = coalesce(clc.MedPersonal_id, ccc.MedPersonal_id) limit 1
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
						{$filterAddQuery}
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
						RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
						RE.Registry_id as \"Registry_id\",
						Evn.Evn_rid as \"Evn_rid\",
						RE.Evn_id as \"Evn_id\",
						{$Evn_ident}
						Evn.EvnClass_id as \"EvnClass_id\",
						ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
						rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
						ps.Person_id as \"Person_id\",
						ps.PersonEvn_id as \"PersonEvn_id\",
						ps.Server_id as \"Server_id\",
						RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp ), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
						RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
						RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
						RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
						MP.Person_Fio as \"MedPersonal_Fio\",
						LB.LpuBuilding_Name as \"LpuBuilding_Name\",
						LS.LpuSection_Name as \"LpuSection_Name\",
						coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
						case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
						{$addToSelect}
						-- end select
					from
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
						left join v_EvnSection es on ES.EvnSection_id = RE.Evn_id
						left join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = RE.Evn_id
						left join v_LpuSection LS on LS.LpuSection_id = coalesce(ES.LpuSection_id, evpl.LpuSection_id)
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
						left join lateral (
							select Person_Fio from v_MedPersonal where MedPersonal_id = coalesce(ES.MedPersonal_id, evpl.MedPersonal_id) limit 1
						) as MP on true
						left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
						left join {$this->scheme}.RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
						-- end order by
				";
                break;
        }

        //echo getDebugSql($query, $params);die;

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
     * @param $data
     * @return array|bool
     */
    function loadRegistryErrorTFOMSFilter($data)
    {

        //Фильтр грида
        $json = isset($data['Filter']) ? toUTF(trim($data['Filter'],'"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

        $this->setRegistryParamsByType($data);

        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Value'=>($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value']))."%"
        );
        $filter="(1=1)";

        $join = "";
        $fields = "";

        if($filter_mode['type'] == 'unicFilter')
        {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if($filter_mode['cell'] == 'Person_FIO'){
                $orderBy = "rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName, '')) || ' ' || rtrim(coalesce(ps.Person_SecName, ''))";
                $field = "rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName, '')) || ' ' || rtrim(coalesce(ps.Person_SecName, ''))";//'RE.'.$column;
            }
            elseif($filter_mode['cell'] == 'LpuSection_Name'){
                $field = 'LS.LpuSection_Name';
                $orderBy = 'LS.LpuSection_Name';
            }
            elseif($filter_mode['cell'] == 'Evn_id'){
                $field = 'RE.Evn_id';
                $orderBy = 'RE.Evn_id';
            }
            elseif($filter_mode['cell'] == 'RegistryErrorType_Code'){
                $field = 'ret.RegistryErrorType_Code';
                $orderBy = 'ret.RegistryErrorType_Code';
            }
            elseif($filter_mode['cell'] == 'Evn_ident'){
                $field = 'RE.Evn_id';
                $orderBy = 'RE.Evn_id';
                if ($data['RegistryType_id'] == 14) {
                    $field = 'Evn.Evn_rid';
                    $orderBy = 'Evn.Evn_rid';
                }
            }
            else {
                $field = $filter_mode['cell'];
            }

            $orderBy = isset($orderBy) ?  $orderBy : $filter_mode['cell'];
            $Like = ($filter_mode['specific'] === false) ? "" : " and ".$orderBy." ilike  :Value";
            $with = "WITH";
            $distinct = 'DISTINCT';
        }
        else{
            return false;
        }

        $orderBy = isset($orderBy) ? $orderBy : null;

        $distinct = isset($distinct) ? $distinct : '';
        $with = isset($with) ? $with : '';

        $query = "
		Select
			-- select
			RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
			RE.Registry_id as \"Registry_id\",
			Evn.Evn_rid as \"Evn_rid\",
			RE.Evn_id as \"Evn_id\",
			Evn.EvnClass_id as \"EvnClass_id\",
			ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
			rtrim(coalesce(ps.Person_SurName,'')) || ' ' || rtrim(coalesce(ps.Person_FirName,'')) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) as \"Person_FIO\",
			ps.Person_id as \"Person_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Server_id as \"Server_id\",
			RTrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp ), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
			RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
			RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
			RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
			--MP.Person_Fio as MedPersonal_Fio,
			RTRIM(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			LS.LpuSection_Name as \"LpuSection_Name\",
			coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
			-- end select
		from
			-- from
			{$this->scheme}.v_RegistryErrorTFOMS RE
			left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			left join v_Evn Evn on Evn.Evn_id = RE.Evn_id
			left join v_EvnSection es on ES.EvnSection_id = RE.Evn_id
			left join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = RE.Evn_id
			left join v_LpuSection LS on LS.LpuSection_id = coalesce(ES.LpuSection_id, evpl.LpuSection_id)
			left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join lateral (
				select Person_Fio from v_MedPersonal where MedPersonal_id = coalesce(ES.MedPersonal_id, evpl.MedPersonal_id) limit 1
			) as MP on true
			left join v_Person_bdz ps on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
			left join {$this->scheme}.RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
			-- group by
			group by {$field}
			-- end group by
		order by
			-- order by
			{$field}
			-- end order by";

        if (!empty($data['nopaging'])) {
            $result = $this->db->query($query, $params);
            if ( is_object($result) ) {
                return $result->result('array');
            }
            else {
                return false;
            }
        }

        $result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);

        $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            if(is_array($cnt_arr) && sizeof($cnt_arr)){
                $count = $cnt_arr[0]['cnt'];
                unset($cnt_arr);
            }
            else
                return false;
        } else {
            $count = 0;
        }

        if (is_object($result)) {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            //var_dump($response);die;
            return $response;
        }

        return false;
    }

    /**
     * Удаление объединённого реестра
     * @param $data
     * @return bool
     */
    public function deleteUnionRegistry($data)
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
			from {$this->scheme}.p_Registry_del
			(
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id
			)
		";

        $result = $this->db->query($query, array(
            'Registry_id' => $data['id'],
            'pmUser_id' => $data['pmUser_id']
        ));

        if (is_object($result)) {
            return $result->result('array');
        }

        return false;
    }

    /**
     * Проверяет находится ли карта вызова в реестре?
     *
     * @param array $data Набор параметров
     * @return bool|array on error
     */
    public function checkCmpCallCardInRegistry( $data )
    {

        if ( !array_key_exists( 'CmpCallCard_id', $data ) || !$data['CmpCallCard_id'] ) {
            return array( array( 'Error_Msg' => 'Не указан идентификатор карты вызова' ) );
        }

        $sql = "
			select
				ccc.CmpCallCard_id as \"CmpCallCard_id\"
			from 
				v_CmpCallCard ccc
				inner join {$this->scheme}.RegistryDataCmp rd on rd.CmpCallCard_id = ccc.CmpCallCard_id
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
			where 
				ccc.CmpCallCard_id = :CmpCallCard_id
            and
                ((rd.RegistryDataCmp_IsPaid = 2 and r.RegistryStatus_id = 4) or r.RegistryStatus_id in (2,3))
			limit 1
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
     * Различные региональные проверки перед переформированием
     * @param $data
     * @return array|bool
     */
    public function checkBeforeSaveRegistryQueue($data)
    {
        $result = parent::checkBeforeSaveRegistryQueue($data);

        if ( $result !== true ) {
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
        if (is_object($result))
        {
            $resp = $result->result('array');
            if (count($resp) > 0) {
                return array(array('success' => false, 'Error_Msg' => '<b>По данному реестру формируется выгрузка в XML.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания выгрузки реестра.'));
            }
        }

        return true;
    }

    /**
     * Сохранение объединённого реестра
     * @param $data
     * @return array|bool
     */
    public function saveUnionRegistry($data)
    {
        // проверка уникальности номера реестра по лпу в одном году
        $KatNasel_Code = $this->getFirstResultFromQuery("select KatNasel_Code from v_KatNasel where KatNasel_id = :KatNasel_id limit 1", $data);

        if ( $KatNasel_Code === false || empty($KatNasel_Code) ) {
            return array('Error_Msg' => 'Ошибка при получении кода категории населения');
        }

        $orgsmofilter = "";
        if ( $KatNasel_Code == 1 ) {
            $orgsmofilter = "and R.OrgSMO_id = :OrgSMO_id";
        }

        $query = "
			select
				R.Registry_id as \"Registry_id\"
			from
				{$this->scheme}.v_Registry R
			where
				R.RegistryType_id = 13
				and R.Lpu_id = :Lpu_id
				and R.Registry_Num = :Registry_Num
				and date_part('year', R.Registry_accDate) = date_part('year', :Registry_accDate)
				and R.Registry_id <> coalesce(:Registry_id, 0)
				and R.KatNasel_id = :KatNasel_id
				{$orgsmofilter}
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
        }
        $query = "
			select 
			    Registry_id as \"Registry_id\",
			    {$KatNasel_Code} as \"KatNasel_Code\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$this->scheme}.{$proc}
			(
				Registry_id := :Registry_id,
				RegistryType_id := 13,
				RegistryStatus_id := 1,
				Registry_Sum := NULL,
				Registry_IsActive := 2,
				Registry_Num := :Registry_Num,
				Registry_accDate := :Registry_accDate,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				PayType_id := (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1),
				KatNasel_id := :KatNasel_id,
				RegistryGroupType_id := :RegistryGroupType_id,
				OrgSMO_id := :OrgSMO_id,
				Lpu_id := :Lpu_id,
				Registry_IsOnceInTwoYears := :Registry_IsOnceInTwoYears,
				Registry_IsZNO := :Registry_IsZNO,
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

                // 3. выполняем поиск реестров которые войдут в объединённый

                $registrytypefilter = "";
                if ($KatNasel_Code == 1) {
                    switch ($data['RegistryGroupType_id']) {
                        case 1:
                            if ( $data['Registry_begDate'] < '2018-11-01' ) {
                                $registrytypefilter = " and R.RegistryType_id in (1, 2, 6)";
                            }
                            else {
                                $registrytypefilter = " and R.RegistryType_id in (1, 2, 6) and coalesce(R.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)";
                            }
                            break;
                        case 2:
                            $registrytypefilter = " and R.RegistryType_id in (14)";
                            break;
                        case 3:
                            $registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and coalesce(R.Registry_IsOnceInTwoYears, 1) = coalesce(:Registry_IsOnceInTwoYears, 1)";
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
                        case 15:
                            $registrytypefilter = " and R.RegistryType_id IN (15)";
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
                        default:
                            $registrytypefilter = " and 1 = 0";
                            break;
                    }
                } else if ($KatNasel_Code == 2) {
                    if ( $data['Registry_begDate'] < '2019-01-01' ) {
                        $registrytypefilter .= " and R.RegistryType_id != 15 ";
                    }
                    else {
                        switch ($data['RegistryGroupType_id']) {
                            case 1:
                                $registrytypefilter = " and R.RegistryType_id in (1, 2, 6) and coalesce(R.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)";
                                break;
                            case 2:
                                $registrytypefilter = " and R.RegistryType_id IN (14)";
                                break;
                            case 3:
                                $registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and coalesce(R.Registry_IsOnceInTwoYears, 1) = coalesce(:Registry_IsOnceInTwoYears, 1)";
                                break;
                            case 4:
                                $registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 2";
                                break;
                            case 10:
                                $registrytypefilter = " and R.RegistryType_id IN (11)";
                                break;
                            case 15:
                                $registrytypefilter = " and R.RegistryType_id IN (15)";
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
                            default:
                                $registrytypefilter = " and 1 = 0";
                                break;
                        }
                    }
                } else if ($KatNasel_Code == 3) {
                    switch ($data['RegistryGroupType_id']) {
                        case 1:
                            if ( $data['Registry_begDate'] < '2018-11-01' ) {
                                $registrytypefilter = " and R.RegistryType_id in (1, 2, 6)";
                            }
                            else {
                                $registrytypefilter = " and R.RegistryType_id in (1, 2, 6) and coalesce(R.Registry_IsZNO, 1) = coalesce(:Registry_IsZNO, 1)";
                            }
                            break;
                        case 2:
                            $registrytypefilter = " and R.RegistryType_id IN (14)";
                            break;
                        case 3:
                            $registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1 and coalesce(R.Registry_IsOnceInTwoYears, 1) = coalesce(:Registry_IsOnceInTwoYears, 1)";
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
                        case 15:
                            $registrytypefilter = " and R.RegistryType_id IN (15)";
                            break;
                        case 18:
                            $registrytypefilter = " and R.RegistryType_id IN (1,2,6)";
                            break;
                        case 19:
                            $registrytypefilter = " and R.RegistryType_id IN (7,9,11,12)";
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
                        default:
                            $registrytypefilter = " and 1 = 0";
                            break;
                    }
                }

                $query = "
					with cte as (
				        Select PayType_id from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1
				    )
					
					select
						R.Registry_id as \" Registry_id\"
					from
						{$this->scheme}.v_Registry R
					where
						R.RegistryType_id <> 13
						and R.RegistryStatus_id = 2 -- к оплате
						and R.KatNasel_id = :KatNasel_id
						and R.Lpu_id = :Lpu_id
						{$orgsmofilter}
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and not exists (
							select rgl.RegistryGroupLink_id
							from {$this->scheme}.v_RegistryGroupLink rgl
								inner join {$this->scheme}.v_Registry rf on rf.Registry_id = rgl.Registry_pid
							where rgl.Registry_id = R.Registry_id
							limit 1
						)
						and coalesce(r.PayType_id, (select PayType_id from cte)) = (select PayType_id from cte)
						{$registrytypefilter}
				";
                $result_reg = $this->db->query($query, array(
                    'KatNasel_id' => $data['KatNasel_id'],
                    'OrgSMO_id' => $data['OrgSMO_id'],
                    'Lpu_id' => $data['Lpu_id'],
                    'Registry_begDate' => $data['Registry_begDate'],
                    'Registry_endDate' => $data['Registry_endDate'],
                    'Registry_IsOnceInTwoYears' => $data['Registry_IsOnceInTwoYears'],
                    'Registry_IsZNO' => $data['Registry_IsZNO'],
                ));

                if (is_object($result_reg))
                {
                    $resp_reg = $result_reg->result('array');
                    // 4. сохраняем новые связи
                    foreach($resp_reg as $one_reg) {
                        $query = "
							select
							    RegistryGroupLink_id as \"RegistryGroupLink_id\",
							    Error_Code as \"Error_Code\",
							    Error_Message as \"Error_Msg\"
							from {$this->scheme}.p_RegistryGroupLink_ins
							(
								RegistryGroupLink_id := null,
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
     * @param $data
     * @return int
     */
    public function getUnionRegistryNumber($data)
    {
        $query = "
			select
				coalesce(MAX(cast(Registry_Num as bigint)),0) + 1 as \"Registry_Num\"
			from
				{$this->scheme}.v_Registry
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
     * @param $data
     * @return bool
     */
    public function loadUnionRegistryEditForm($data)
    {
        $query = "
			select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
				R.KatNasel_id as \"KatNasel_id\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.OrgSMO_id as \"OrgSMO_id\",
				R.Lpu_id as \"Lpu_id\",
				R.Registry_IsLocked as \"Registry_IsLocked\",
				RCS.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				RCS.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\",
				coalesce(R.Registry_IsOnceInTwoYears, 1) as \"Registry_IsOnceInTwoYears\",
				coalesce(R.Registry_IsZNO, 1) as \"Registry_IsZNO\"
			from
				{$this->scheme}.v_Registry R
				left join v_RegistryCheckStatus RCS on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
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
     * @param $data
     * @return array|bool
     */
    public function loadUnionRegistryGrid($data)
    {
        $query = "
			Select 
				-- select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				coalesce(R.Registry_IsLocked, 1) as \"Registry_IsLocked\",
				to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
				R.KatNasel_id as \"KatNasel_id\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				RGT.RegistryGroupType_Name as \"RegistryGroupType_Name\",
				CASE WHEN R.Registry_IsZNO = 2 THEN 'true' ELSE 'false' END as \"Registry_IsZNO\",
				OS.OrgSMO_Nick as \"OrgSMO_Nick\",
				coalesce(RS.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
				RCS.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				RCS.RegistryCheckStatus_SysNick as \"RegistryCheckStatus_SysNick\",
				RCS.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
				-- end select
			from 
				-- from
				{$this->scheme}.v_Registry R -- объединённый реестр
				left join v_RegistryGroupType RGT on RGT.RegistryGroupType_id = R.RegistryGroupType_id
				left join v_OrgSMO OS on OS.OrgSMO_id = R.OrgSMO_id
				left join v_KatNasel KN on KN.KatNasel_id = R.KatNasel_id
				left join v_RegistryCheckStatus RCS on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				left join lateral (
					select
						SUM(coalesce(R2.Registry_SumPaid, 0)) as Registry_SumPaid
					from {$this->scheme}.v_Registry R2
						inner join {$this->scheme}.v_RegistryGroupLink RGL on R2.Registry_id = RGL.Registry_id
					where
						RGL.Registry_pid = R.Registry_id
				) RS on true
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
        }

        return false;
    }

    /**
     * Загрузка списка обычных реестров, входящих в объединённый
     * @param $data
     * @return array|bool
     */
    public function loadUnionRegistryChildGrid($data)
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
        }

        return false;
    }


    /**
     *	Получение списка дополнительных полей для выборки
     */
    function getReformRegistryAdditionalFields() {
        return ',OrgSMO_id,DispClass_id,PayType_id,Registry_IsOnceInTwoYears,Registry_IsZNO';
    }

    /**
     *	Получение списка дополнительных полей для выборки
     */
    function getLoadRegistryQueueAdditionalFields() {
        return ',
            R.DispClass_id as "DispClass_id",
            coalesce(R.Registry_IsLocked, 1) as "Registry_IsLocked",
            R.Registry_IsOnceInTwoYears as "Registry_IsOnceInTwoYears",
            R.Registry_IsZNO as "Registry_IsZNO"
        ';
    }

    /**
     *	Получение списка дополнительных полей для выборки
     */
    function getLoadRegistryAdditionalFields() {
        return ',
            R.DispClass_id as "DispClass_id",
            coalesce(R.Registry_IsLocked, 1) as "Registry_IsLocked",
            R.Registry_IsOnceInTwoYears as "Registry_IsOnceInTwoYears",
            R.Registry_IsZNO as "Registry_IsZNO"
        ';
    }

    /**
     *    Получение данных для выгрузки реестров в XML
     * @param $type
     * @param $data
     * @param $number
     * @param $Registry_EvnNum
     * @return array|bool
     */
    public function loadRegistryDataForXmlUsing($type, $data, &$number, &$Registry_EvnNum)
    {
        // Для того чтобы не захламлять память
        $this->db->save_queries = false;
        switch ($type)
        {
            case 1: //stac
                $p_schet = $this->scheme.".p_Registry_EvnPS_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPS_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPS_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPS_expPac";
                $p_ds2 = $this->scheme.".p_Registry_EvnPS_expDS2";
                $p_ds3 = $this->scheme.".p_Registry_EvnPS_expDS3";
                $p_kslp = $this->scheme.".p_Registry_EvnPS_expKSLP";
                $p_shema = $this->scheme.".p_Registry_EvnPS_expShema";
                break;
            case 2: //polka
                $p_schet = $this->scheme.".p_Registry_EvnPL_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPL_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPL_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPL_expPac";
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
            case 7: //dd
                $p_schet = $this->scheme.".p_Registry_EvnPLDD13_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPLDD13_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPLDD13_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPLDD13_expPac";
                break;
            case 8: //dd
                $p_schet = $this->scheme.".p_Registry_EvnPLDD13_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPLDD13_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPLDD13_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPLDD13_expPac";
                break;
            case 9: //orp
                $p_schet = $this->scheme.".p_Registry_EvnPLOrp13_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPLOrp13_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPLOrp13_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPLOrp13_expPac";
                break;
            case 10: //orp
                $p_schet = $this->scheme.".p_Registry_EvnPLOrp13_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPLOrp13_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPLOrp13_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPLOrp13_expPac";
                break;
            case 11: //orp
                $p_schet = $this->scheme.".p_Registry_EvnPLProf_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPLProf_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPLProf_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPLProf_expPac";
                break;
            case 12: //teen inspection
                $p_schet = $this->scheme.".p_Registry_EvnPLProfTeen_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPLProfTeen_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPLProfTeen_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPLProfTeen_expPac";
                break;
            case 13: //union registry
                $p_schet = $this->scheme.".p_Registry_EvnPL_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnPL_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnPL_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnPL_expPac";
                break;
            case 14: //htm
                $p_schet = $this->scheme.".p_Registry_EvnHTM_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnHTM_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnHTM_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnHTM_expPac";
                /*$p_ds2 = $this->scheme.".p_Registry_EvnHTM_expDS2";
				$p_ds3 = $this->scheme.".p_Registry_EvnHTM_expDS3";
				$p_kslp = $this->scheme.".p_Registry_EvnHTM_expKSLP";
				$p_shema = $this->scheme.".p_Registry_EvnHTM_expShema";*/
                break;
            case 15: //parka
                $p_schet = $this->scheme.".p_Registry_EvnUslugaPar_expScet";
                $p_vizit = $this->scheme.".p_Registry_EvnUslugaPar_expVizit";
                $p_usl = $this->scheme.".p_Registry_EvnUslugaPar_expUsl";
                $p_pers = $this->scheme.".p_Registry_EvnUslugaPar_expPac";
                break;
            default:
                return false;
                break;
        }
        // шапка
        $query = "
			select {$p_schet} (Registry_id := ?)
		";

        $result = $this->db->query($query, array($data['Registry_id']));

        if ( is_object($result) ) {
            $header = $result->result('array');
        } else {
            return false;
        }

        // случаи
        $query = "
			select {$p_vizit} (Registry_id := ?)
		";
        $result = $this->db->query($query, array($data['Registry_id']));

        if ( is_object($result) ) {
            $visits = $result->result('array');
            $SLUCH = array();
            // привязываем услуги к случаю
            foreach( $visits as $visit )
            {
                if ( !empty($visit['Evn_sid']) ) {
                    if ( !isset($SLUCH[$visit['Evn_sid']]) ) {
                        $SLUCH[$visit['Evn_sid']] = array();
                    }

                    $SLUCH[$visit['Evn_sid']][] = $visit;
                }
            }
            unset($visits);
        }
        else {
            return false;
        }

        // услуги
        if ( !empty($p_usl) ) {
            $query = "
				select {$p_usl} (Registry_id := ?)
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
                    if ( !isset($USL[$usluga['Evn_sid']]) )
                        $USL[$usluga['Evn_sid']] = array();
                    $USL[$usluga['Evn_sid']][] = $usluga;
                    $i++;
                }
                unset($uslugi);
            }
            else {
                return false;
            }
        }

        // диагнозы (DS2)
        if ( !empty($p_ds2) ) {
            $query = "
				select {$p_ds2} (Registry_id := ?)
			";
            $result = $this->db->query($query, array($data['Registry_id']));

            if ( is_object($result) ) {
                $diag2 = $result->result('array');
                $DS2 = array();

                // привязываем диагнозы к случаю
                foreach( $diag2 as $diag ) {
                    if ( !isset($DS2[$diag['Evn_sid']]) ) {
                        $DS2[$diag['Evn_sid']] = array();
                    }

                    $DS2[$diag['Evn_sid']][] = array('DS2' => $diag['DS2']);
                }

                unset($diag2);
            }
            else {
                return false;
            }
        }

        // диагнозы (DS3)
        if ( !empty($p_ds3) ) {
            $query = "
				select {$p_ds3} (Registry_id := ?)
			";
            $result = $this->db->query($query, array($data['Registry_id']));

            if ( is_object($result) ) {
                $diag3 = $result->result('array');
                $DS3 = array();

                // привязываем диагнозы к случаю
                foreach( $diag3 as $diag ) {
                    if ( !isset($DS3[$diag['Evn_sid']]) ) {
                        $DS3[$diag['Evn_sid']] = array();
                    }

                    $DS3[$diag['Evn_sid']][] = array('DS3' => $diag['DS3']);
                }

                unset($diag3);
            }
            else {
                return false;
            }
        }

        // КСЛП (SL_KOEF)
        if ( !empty($p_kslp) ) {
            $query = "
				select {$p_kslp} (Registry_id := ?)
			";
            $result = $this->db->query($query, array($data['Registry_id']));

            if ( is_object($result) ) {
                $kslp = $result->result('array');
                $SL_KOEF = array();

                // привязываем диагнозы к случаю
                foreach( $kslp as $one_kslp ) {
                    if ( !isset($SL_KOEF[$one_kslp['Evn_sid']]) ) {
                        $SL_KOEF[$one_kslp['Evn_sid']] = array();
                    }

                    $SL_KOEF[$one_kslp['Evn_sid']][] = array('IDSL' => $one_kslp['IDSL'], 'Z_SL' => $one_kslp['Z_SL']);
                }

                unset($kslp);
            }
            else {
                return false;
            }
        }

        // Схемы (SHEMA)
        if ( !empty($p_shema) ) {
            $query = "
				select {$p_shema} (Registry_id := ?)
			";
            $result = $this->db->query($query, array($data['Registry_id']));

            if ( is_object($result) ) {
                $shema = $result->result('array');
                $SHEMA = array();

                // привязываем диагнозы к случаю
                foreach( $shema as $one_shema ) {
                    if ( !isset($SHEMA[$one_shema['Evn_sid']]) ) {
                        $SHEMA[$one_shema['Evn_sid']] = array();
                    }

                    $SHEMA[$one_shema['Evn_sid']][] = array('KOD_SHEMI' => $one_shema['KOD_SHEMI']);
                }

                unset($kslp);
            }
            else {
                return false;
            }
        }

        // люди
        $query = "
			select {$p_pers} (Registry_id := ?)
		";
        $result = $this->db->query($query, array($data['Registry_id']));

        if ( is_object($result) ) {
            $person = $result->result('array');
            $PACIENT = array();
            // привязываем персона к случаю
            foreach( $person as $pers ) {
                if ( !empty($pers['Evn_sid']) ) {
                    /**
                     * @task https://redmine.swan.perm.ru/issues/84331
                     */
                    $pers['DOST'] = array();
                    $pers['DOST_P'] = array();

                    if ( empty($pers['FAM']) ) {
                        $pers['DOST'][] = array('DOST_VAL' => 2);
                    }

                    if ( empty($pers['IM']) ) {
                        $pers['DOST'][] = array('DOST_VAL' => 3);
                    }

                    if ( empty($pers['OT']) || mb_strtoupper($pers['OT']) == 'НЕТ' ) {
                        $pers['DOST'][] = array('DOST_VAL' => 1);
                    }

                    if ( $pers['NOVOR'] != '0' ) {
                        if ( empty($pers['FAM_P']) ) {
                            $pers['DOST_P'][] = array('DOST_P_VAL' => 2);
                        }

                        if ( empty($pers['IM_P']) ) {
                            $pers['DOST_P'][] = array('DOST_P_VAL' => 3);
                        }

                        if ( empty($pers['OT_P']) || mb_strtoupper($pers['OT_P']) == 'НЕТ' ) {
                            $pers['DOST_P'][] = array('DOST_P_VAL' => 1);
                        }
                    }

                    $PACIENT[$pers['Evn_sid']] = $pers;
                }
            }
            unset($person);
        }
        else {
            return false;
        }

        // собираем массив для выгрузки
        $respdata = array();
        $respdata['SCHET'] = array($header[0]);
        // массив с записями
        $respdata['ZAP'] = array();
        foreach ( $PACIENT as $key => $value ) {
            $respdata['ZAP'][$key]['PACIENT'] = array($value);
        }
        /*
		echo "<pre>";
		print_r($SLUCH);
		die();
		*/
        foreach($SLUCH as $key => $value ) {
            foreach($value as $k => $val) {
                if ( isset($USL[$key]) ) {
                    $value[$k]['USL'] = $USL[$key];
                }
                else {
                    $value[$k]['USL'] = $this->getEmptyUslugaXmlRow();
                }

                // @task https://redmine.swan.perm.ru/issues/81970
                // Доп. условие на равенство Evn_sid и Evn_id
                if ( isset($DS2[$key]) && $val['Evn_sid'] == $val['Evn_id'] ) {
                    $value[$k]['DS2_DATA'] = $DS2[$key];
                }
                else {
                    $value[$k]['DS2_DATA'] = array(array('DS2' => (!empty($value[$k]['DS2']) ? $value[$k]['DS2'] : null)));
                }

                if ( isset($DS3[$key]) ) {
                    $value[$k]['DS3_DATA'] = $DS3[$key];
                }
                else {
                    $value[$k]['DS3_DATA'] = array(array('DS3' => (!empty($value[$k]['DS3']) ? $value[$k]['DS3'] : null)));
                }

                if ( isset($SL_KOEF[$key]) ) {
                    $value[$k]['SL_KOEF'] = $SL_KOEF[$key];
                }
                else {
                    $value[$k]['SL_KOEF'] = array();
                }

                if ( isset($SHEMA[$key]) ) {
                    $value[$k]['SHEMA'] = $SHEMA[$key];
                }
                else {
                    $value[$k]['SHEMA'] = array();
                }

                unset($value[$k]['DS2']);
                unset($value[$k]['DS3']);
            }
            $respdata['ZAP'][$key]['SLUCH'] = $value;
            $respdata['ZAP'][$key]['PR_NOV'] = $value[0]['PR_NOV'];
        }

        $this->setRegistryParamsByType(array(
            'RegistryType_id' => $type
        ));

        foreach ( $respdata['ZAP'] as $key => $value )
        {
            if ( !isset($respdata['ZAP'][$key]['SLUCH']) ) {
                unset($respdata['ZAP'][$key]);
            } else {
                $number++;
                $Registry_EvnNum[$number] = array(
                    'Evn_id' => $respdata['ZAP'][$key]['SLUCH'][0]['Evn_id'],
                    'Evn_sid' => !empty($respdata['ZAP'][$key]['SLUCH'][0]['Evn_sid'])?$respdata['ZAP'][$key]['SLUCH'][0]['Evn_sid']:null
                );
                // проапдейтить поле RegistryData_RowNum
                // Только для иногородних
                // @task https://redmine.swan.perm.ru/issues/103731
                if ( $data['KatNasel_SysNick'] == 'inog' ) {
                    $this->db->query("
						update
							rd
						set
							rd.{$this->RegistryDataObject}_RowNum = :RegistryData_RowNum
						from
							{$this->scheme}.{$this->RegistryDataObject} rd
							inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
						where
							rgl.Registry_pid = :Registry_id
							and rd.{$this->RegistryDataEvnField} = :Evn_sid
					", array(
                        'Registry_id' => $data['Registry_id'],
                        'Evn_sid' => $respdata['ZAP'][$key]['SLUCH'][0]['Evn_sid'],
                        'RegistryData_RowNum' => $number
                    ));
                }
                $respdata['ZAP'][$key]['N_ZAP'] = $number;
                $respdata['ZAP'][$key]['SLUCH'][0]['IDCASE'] = $number;
            }
        }

        $respdata['PACIENT'] = array();
        // дубли по ID_PAC в файле пациентов не нужны, поэтому формируем массив без дублей
        foreach($PACIENT as $key => $pac) {
            $respdata['PACIENT'][$pac['ID_PAC']] = $pac;
        }

        return $respdata;
    }

    /**
     *	Получение данных для выгрузки реестров в XML
     *  Версия с 01.04.2018
     */
    public function loadRegistryDataForXmlUsing2018($type, $data, &$number, &$Registry_EvnNum) {
        // Для того чтобы не захламлять память
        $this->db->save_queries = false;

        switch ( $type ) {
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

            default:
                return false;
                break;
        }

        $postfix = '_2018';
        if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
            $postfix = '_bud';
        }

        $p_schet = $this->scheme . ".p_Registry_{$object}_expScet";
        if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
            $p_schet = $this->scheme . ".p_Registry_{$object}_expScet_bud";
        }
        $p_zsl = $this->scheme . ".p_Registry_{$object}_expSL" . $postfix;
        $p_vizit = $this->scheme . ".p_Registry_{$object}_expVizit" . $postfix;
        $p_usl = $this->scheme . ".p_Registry_{$object}_expUsl" . $postfix;
        $p_pers = $this->scheme . ".p_Registry_{$object}_expPac" . $postfix;

        if ( in_array($type, array(1, 14)) ) {
            $p_ds2 = $this->scheme.".p_Registry_{$object}_expDS2";
            $p_ds3 = $this->scheme.".p_Registry_{$object}_expDS3";

            if ( $type == 1 ) {
                $p_kslp = $this->scheme.".p_Registry_{$object}_expKSLP";
                $p_shema = $this->scheme.".p_Registry_{$object}_expShema";
            }
        }
        else if ( in_array($type, array(2, 7, 9, 11, 12)) ) {
            $p_ds2 = $this->scheme.".p_Registry_{$object}_expDS2_2018";
        }

        if ( in_array($type, array(1, 2, 14)) && $data['registryIsAfter20180901'] === true ) {
            $p_bdiag = $this->scheme.".p_Registry_{$object}_expBDIAG_2018";
            $p_bprot = $this->scheme.".p_Registry_{$object}_expBPROT_2018";
            $p_napr = $this->scheme.".p_Registry_{$object}_expNAPR_2018";
            $p_onkousl = $this->scheme.".p_Registry_{$object}_expONKOUSL_2018";

            if ( $type == 14 || $data['Registry_IsZNO'] == 2 ) {
                $p_cons = $this->scheme.".p_Registry_{$object}_expCONS_2018";
                $p_lek_pr = $this->scheme.".p_Registry_{$object}_expLEK_PR_2018";
            }

            if ( $type == 1 && $data['registryIsAfter20190101'] === true ) {
                $p_crit = $this->scheme.".p_Registry_{$object}_expCRIT_2018";
            }
        }

        if ( in_array($type, array(7, 9, 11, 12)) ) {
            $p_naz = $this->scheme.".p_Registry_{$object}_expNAZ_2018";
        }

		if ( in_array($type, array(1,6)) ) {
			$p_regkr = $this->scheme.".p_Registry_{$object}_expREGKR";
		}

        $BDIAG = array();
        $BPROT = array();
        $CONS = array();
        $CRIT = array();
        $DS2 = array();
        $DS3 = array();
        $LEK_PR = array();
        $NAPR = array();
        $NAZ = array();
        $ONKOUSL = array();
        $PACIENT = array();
        $SL_KOEF = array();
        $SLUCH = array();
        $SHEMA = array();
        $USL = array();
        $ZSL = array();
		$REGKR = array();

        $netValue = toAnsi('НЕТ', true);

        // шапка
        $query = "select {$p_schet} (Registry_id := :Registry_id)";
        $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

        if ( !is_object($result) ) {
            return false;
        }

        $header = $result->result('array');

        if ( !is_array($header) || count($header) == 0 ) {
            return false;
        }
        array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);

        // Люди (PACIENT)
        $query = "select {$p_pers} (Registry_id := :Registry_id)";
        $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

        if ( !is_object($result) ) {
            return false;
        }

        while ( $row = $result->_fetch_assoc() ) {
            if ( empty($row['Evn_id']) ) {
                continue;
            }
            array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

            $row['DOST'] = array();
            $row['DOST_P'] = array();

            if (!in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {

                if (empty($row['FAM'])) {
                    $row['DOST'][] = array('DOST_VAL' => 2);
                }

                if (empty($row['IM'])) {
                    $row['DOST'][] = array('DOST_VAL' => 3);
                }

                if (empty($row['OT']) || strtoupper($row['OT']) == $netValue) {
                    $row['DOST'][] = array('DOST_VAL' => 1);
                }

                if ($row['NOVOR'] != '0') {
                    if (empty($row['FAM_P'])) {
                        $row['DOST_P'][] = array('DOST_P_VAL' => 2);
                    }

                    if (empty($row['IM_P'])) {
                        $row['DOST_P'][] = array('DOST_P_VAL' => 3);
                    }

                    if (empty($row['OT_P']) || strtoupper($row['OT_P']) == $netValue) {
                        $row['DOST_P'][] = array('DOST_P_VAL' => 1);
                    }
                }
            }

            $PACIENT[$row['Evn_id']] = $row;
        }

        // Законченные случаи (ZSL)
        $query = "select {$p_zsl} (Registry_id := :Registry_id)";
        $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

        if ( !is_object($result) ) {
            return false;
        }

        while ( $row = $result->_fetch_assoc() ) {
            array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
            if ( !isset($ZSL[$row['Evn_id']]) ) {
                $ZSL[$row['Evn_id']] = array();
            }

            $ZSL[$row['Evn_id']][] = $row;
        }

        // Посещения (SL)
        $query = "select {$p_vizit} (Registry_id := :Registry_id)";
        $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

        if ( !is_object($result) ) {
            return false;
        }

        while ( $row = $result->_fetch_assoc() ) {
            array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
            if ( !isset($SLUCH[$row['Evn_id']]) ) {
                $SLUCH[$row['Evn_id']] = array();
            }

            $SLUCH[$row['Evn_id']][] = $row;
        }

        // Сведения о проведении консилиума (CONS)
        if ( !empty($p_cons) ) {
            $query = "select {$p_cons} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($CONS[$row['Evn_sid']]) ) {
                    $CONS[$row['Evn_sid']] = array();
                }

                $CONS[$row['Evn_sid']][] = $row;
            }
        }

        // Сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
        if ( !empty($p_lek_pr) ) {
            $query = "select {$p_lek_pr} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($LEK_PR[$row['EvnUslugaLEK_id']]) ) {
                    $LEK_PR[$row['EvnUslugaLEK_id']] = array();
                }

                $LEK_PR[$row['EvnUslugaLEK_id']][] = $row;
            }
        }

        // Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
        if ( !empty($p_onkousl) ) {
            $query = "select {$p_onkousl} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( $type == 14 || $data['Registry_IsZNO'] == 2 ) {
                    if ( !isset($ONKOUSL[$row['Evn_sid']]) ) {
                        $ONKOUSL[$row['Evn_sid']] = array();
                    }

                    $row['LEK_PR_DATA'] = array();

                    if ( isset($LEK_PR[$row['EvnUslugaLEK_id']]) && in_array($row['USL_TIP'], array(2, 4)) ) {
                        $LEK_PR_DATA = array();

                        foreach ( $LEK_PR[$row['EvnUslugaLEK_id']] as $rowTmp ) {
                            if ( !isset($LEK_PR_DATA[$rowTmp['REGNUM']]) ) {
                                $LEK_PR_DATA[$rowTmp['REGNUM']] = array(
                                    'REGNUM' => $rowTmp['REGNUM'],
                                    'CODE_SH' => (!empty($rowTmp['CODE_SH']) ? $rowTmp['CODE_SH'] : null),
                                    'DATE_INJ_DATA' => array(),
                                );
                            }

                            $LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $rowTmp['DATE_INJ']);
                        }

                        $row['LEK_PR_DATA'] = $LEK_PR_DATA;
                        unset($LEK_PR[$row['EvnUslugaLEK_id']]);
                    }

                    $ONKOUSL[$row['Evn_sid']][] = $row;
                }
                else {
                    $ONKOUSL[$row['EvnUsluga_id']] = $row;
                }
            }
        }

        // Услуги (USL)
        $query = "select {$p_usl} (Registry_id := :Registry_id)";
        $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

        if ( !is_object($result) ) {
            return false;
        }

        while ( $row = $result->_fetch_assoc() ) {
            array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
            $this->_IDSERV++;
            $row['IDSERV'] = $this->_IDSERV;

            if ( !isset($USL[$row['Evn_sid']]) ) {
                $USL[$row['Evn_sid']] = array();
            }

            if ( $type != 14 && $data['Registry_IsZNO'] != 2 ) {
                $row['NAPR_DATA'] = array();
                $row['ONK_USL_DATA'] = array();

                if ( isset($row['EvnUsluga_id']) && isset($ONKOUSL[$row['EvnUsluga_id']]) ) {
                    $row['ONK_USL_DATA'] = array($ONKOUSL[$row['EvnUsluga_id']]);
                    unset($ONKOUSL[$row['EvnUsluga_id']]);
                }
            }

            $USL[$row['Evn_sid']][] = $row;
        }

        // Диагнозы (DS2)
        if ( !empty($p_ds2) ) {
            $query = "select {$p_ds2} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($DS2[$row['Evn_sid']]) ) {
                    $DS2[$row['Evn_sid']] = array();
                }

                $DS2[$row['Evn_sid']][] = $row;
            }
        }

        // Диагнозы (DS3)
        if ( !empty($p_ds3) ) {
            $query = "select {$p_ds3} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($DS3[$row['Evn_sid']]) ) {
                    $DS3[$row['Evn_sid']] = array();
                }

                $DS3[$row['Evn_sid']][] = array('DS3' => $row['DS3']);
            }
        }

        // КСЛП (SL_KOEF)
        if ( !empty($p_kslp) ) {
            $query = "select {$p_kslp} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($SL_KOEF[$row['Evn_sid']]) ) {
                    $SL_KOEF[$row['Evn_sid']] = array();
                }

                $SL_KOEF[$row['Evn_sid']][] = array('IDSL' => $row['IDSL'], 'Z_SL' => $row['Z_SL']);
            }
        }

        // Классификационный критерий (CRIT)
        if ( !empty($p_crit) ) {
            $query = "select {$p_crit} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($CRIT[$row['Evn_sid']]) ) {
                    $CRIT[$row['Evn_sid']] = array();
                }

                $CRIT[$row['Evn_sid']][] = $row;
            }
        }

        // Схемы (SHEMA)
        if ( !empty($p_shema) ) {
            $query = "select {$p_shema} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($SHEMA[$row['Evn_sid']]) ) {
                    $SHEMA[$row['Evn_sid']] = array();
                }

                $SHEMA[$row['Evn_sid']][] = array('KOD_SHEMI' => $row['KOD_SHEMI']);
            }
        }

        // Назначения (NAZ)
        if ( !empty($p_naz) ) {
            $query = "select {$p_naz} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($NAZ[$row['Evn_sid']]) ) {
                    $NAZ[$row['Evn_sid']] = array();
                }

                $NAZ[$row['Evn_sid']][] = $row;
            }
        }

        // Диагностический блок (BDIAG)
        if ( !empty($p_bdiag) ) {
            $query = "select {$p_bdiag} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($BDIAG[$row['Evn_sid']]) ) {
                    $BDIAG[$row['Evn_sid']] = array();
                }

                $BDIAG[$row['Evn_sid']][] = $row;
            }
        }

        // Сведения об имеющихся противопоказаниях и отказах (BPROT)
        if ( !empty($p_bprot) ) {
            $query = "select {$p_bprot} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($BPROT[$row['Evn_sid']]) ) {
                    $BPROT[$row['Evn_sid']] = array();
                }

                $BPROT[$row['Evn_sid']][] = $row;
            }
        }

        // Направления (NAPR)
        if ( !empty($p_napr) ) {
            $query = "select {$p_napr} (Registry_id := :Registry_id)";
            $result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

            if ( !is_object($result) ) {
                return false;
            }

            while ( $row = $result->_fetch_assoc() ) {
                array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
                if ( !isset($NAPR[$row['Evn_sid']]) ) {
                    $NAPR[$row['Evn_sid']] = array();
                }

                $NAPR[$row['Evn_sid']][] = $row;
            }
        }

		// Региональный критерий (REG_KR)
		if ( !empty($p_regkr) ) {
			$query = "select {$p_regkr} (Registry_id := :Registry_id)";
			$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			while ( $row = $result->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($REGKR[$row['Evn_sid']]) ) {
					$REGKR[$row['Evn_sid']] = array();
				}
				
				$REGKR[$row['Evn_sid']][] = $row;
			}
		}

        // собираем массив для выгрузки
        $respdata = array();
        $respdata['SCHET'] = array($header[0]);

        // массив с записями
        $respdata['ZAP'] = array();

        foreach ( $PACIENT as $key => $value ) {
            $respdata['ZAP'][$key]['PACIENT'] = array($value);
        }

        foreach ( $ZSL as $key => $value ) {
            $value[0]['OS_SLUCH'] = array();

            //if (in_array($type, array(2, 7))) {
            if (!empty($PACIENT[$key]['OS_SLUCH'])) {
                $value[0]['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $PACIENT[$key]['OS_SLUCH']);
            }
            if (!empty($PACIENT[$key]['OS_SLUCH1'])) {
                $value[0]['OS_SLUCH'][] = array('OS_SLUCH_VAL' => $PACIENT[$key]['OS_SLUCH1']);
            }
            //}

            $respdata['ZAP'][$key]['Z_SL_DATA'] = $value;
            if (isset($value[0]['PR_NOV'])) {
                $respdata['ZAP'][$key]['PR_NOV'] = $value[0]['PR_NOV'];
            } else {
                $respdata['ZAP'][$key]['PR_NOV'] = null;
            }
        }

        $KSG_KPG_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL', 'SL_KOEF');
        $ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');
        $SANK_FIELDS = array('S_CODE', 'S_SUM', 'S_TIP', 'S_OSN', 'S_COM', 'S_IST', 'NACTMEK', 'DACTMEK');

        if ( $type != 14 && $data['Registry_IsZNO'] != 2 && $data['registryIsAfter20190101'] === false ) {
            $ONK_SL_FIELDS = array_merge($ONK_SL_FIELDS, array('PR_CONS1', 'PR_CONS2', 'DT_CONS1', 'DT_CONS2'));
        }

        foreach ( $SLUCH as $key => $value ) {
            foreach ( $value as $k => $visit ) {
                $Evn_sid = $visit['Evn_sid'];

                $visit['DS2_DATA'] = array();
                $visit['DS3_DATA'] = array();
                $visit['KSG_KPG'] = array();
                $visit['NAZ'] = array();
                $visit['ONK_SL_DATA'] = array();
                $visit['SANK'] = array();
                $visit['SHEMA'] = array();
                $visit['USL'] = array();

                if ( $type == 14 || $data['Registry_IsZNO'] == 2 ) {
                    $visit['CONS_DATA'] = array();
                    $visit['NAPR_DATA'] = array();
                }

                $KSG_KPG_DATA = array();
                $ONK_SL_DATA = array();
                $SANK_DATA = array();

                if ( isset($DS2[$Evn_sid]) ) {
                    $visit['DS2_DATA'] = $DS2[$Evn_sid];
                }
                else if ( !empty($visit['DS2']) ) {
                    $visit['DS2_DATA'][] = array(
                        'DS2' => $visit['DS2'],
                        'DS2_PR' => (!empty($visit['DS2_PR']) ? $visit['DS2_PR'] : null),
                        'PR_DS2_N' => (!empty($visit['PR_DS2_N']) ? $visit['PR_DS2_N'] : null)
                    );
                }

                if ( array_key_exists('DS2', $visit) ) {
                    unset($visit['DS2']);
                }

                if ( isset($DS3[$Evn_sid]) ) {
                    $visit['DS3_DATA'] = $DS3[$Evn_sid];
                }
                else if ( !empty($visit['DS3']) ) {
                    $visit['DS3_DATA'][] = array('DS3' => $visit['DS3']);
                }

                if ( array_key_exists('DS3', $visit) ) {
                    unset($visit['DS3']);
                }

                if ( isset($SHEMA[$Evn_sid]) ) {
                    $visit['SHEMA'] = $SHEMA[$Evn_sid];
                }

                if ( isset($NAZ[$Evn_sid]) ) {
                    $visit['NAZ'] = $NAZ[$Evn_sid];
                }

                $onkDS2 = false;

                if ( count($visit['DS2_DATA']) > 0 ) {
                    foreach ( $visit['DS2_DATA'] as $ds2 ) {
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
                    ($type == 14 || $data['Registry_IsZNO'] == 2)
                    && (
                        $visit['DS_ONK'] == 1
                        || (
                            !empty($visit['DS1'])
                            && (
                                substr($visit['DS1'], 0, 1) == 'C'
                                || ($data['registryIsAfter20190101'] === true && substr($visit['DS1'], 0, 3) >= 'D00' && substr($visit['DS1'], 0, 3) <= 'D09')
                                || ($visit['DS1'] == 'D70' && $onkDS2 == true)
                            )
                        )
                    )
                ) {
                    if ( isset($CONS[$Evn_sid]) ) {
                        $visit['CONS_DATA'] = $CONS[$Evn_sid];
                    }

                    if ( isset($NAPR[$Evn_sid]) ) {
                        $visit['NAPR_DATA'] = $NAPR[$Evn_sid];
                    }
                }

				$visit['REG_KR'] = array();
				if ( isset($REGKR[$Evn_sid]) ) {
					foreach($REGKR[$Evn_sid] as $reg_kr_rec){
						if(!empty($reg_kr_rec['KR_CODE'])){
							if(!isset($visit['REG_KR'][0])){
								$visit['REG_KR'][] = array('KR_CODE' => array());
							}
							$visit['REG_KR'][0]['KR_CODE'][] = array('KR_CODE_VAL' => $reg_kr_rec['KR_CODE']);
						}
					}
				}
				
				if(!empty($visit['KR_CODE'])){
					$visit['REG_KR'][] = array('KR_CODE' => array());
					$visit['REG_KR'][0]['KR_CODE'][] = array('KR_CODE_VAL' => $visit['KR_CODE']);
				}

				if (array_key_exists('KR_CODE', $visit)) {
					unset($visit['KR_CODE']);
				}

                if (
                    (empty($visit['DS_ONK']) || $visit['DS_ONK'] != 1)
                    //&& (empty($visit['P_CEL']) || $visit['P_CEL'] != '1.3')
                    && (empty($visit['REAB']) || $visit['REAB'] != '1')
                    && !empty($visit['DS1'])
                    && (
                        substr($visit['DS1'], 0, 1) == 'C'
                        || ($data['registryIsAfter20190101'] === true && substr($visit['DS1'], 0, 3) >= 'D00' && substr($visit['DS1'], 0, 3) <= 'D09')
                        || ($visit['DS1'] == 'D70' && $onkDS2 == true)
                    )
                ) {
                    $hasONKOSLData = false;
                    $ONK_SL_DATA['B_DIAG_DATA'] = array();
                    $ONK_SL_DATA['B_PROT_DATA'] = array();

                    if ( $type == 14 || $data['Registry_IsZNO'] == 2 ) {
                        $ONK_SL_DATA['ONK_USL_DATA'] = array();
                    }
                    else {
                        $ONK_SL_DATA['PR_CONS_DATA'] = array();
                        $ONK_SL_DATA['DT_CONS_DATA'] = array();
                    }

                    foreach ( $ONK_SL_FIELDS as $field ) {
                        if ( isset($visit[$field]) ) {
                            $hasONKOSLData = true;
                            if (in_array($field, array('PR_CONS1', 'PR_CONS2'))) {
                                $ONK_SL_DATA['PR_CONS_DATA'][] = array(
                                    'PR_CONS' => $visit[$field]
                                );
                            } else if (in_array($field, array('DT_CONS1', 'DT_CONS2'))) {
                                $ONK_SL_DATA['DT_CONS_DATA'][] = array(
                                    'DT_CONS' => $visit[$field]
                                );
                            } else {
                                $ONK_SL_DATA[$field] = $visit[$field];
                            }
                        }
                        else {
                            $ONK_SL_DATA[$field] = null;
                        }

                        if ( array_key_exists($field, $visit) ) {
                            unset($visit[$field]);
                        }
                    }

                    if ( isset($BDIAG[$Evn_sid]) ) {
                        $hasONKOSLData = true;
                        $ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$Evn_sid];
                        unset($BDIAG[$Evn_sid]);
                    }

                    if ( isset($BPROT[$Evn_sid]) ) {
                        $hasONKOSLData = true;
                        $ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$Evn_sid];
                        unset($BPROT[$Evn_sid]);
                    }

                    if ( ($type == 14 || $data['Registry_IsZNO'] == 2) && isset($ONKOUSL[$Evn_sid]) ) {
                        $hasONKOSLData = true;
                        $ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$Evn_sid];
                        unset($ONKOUSL[$Evn_sid]);
                    }

                    if ( $hasONKOSLData == false ) {
                        $ONK_SL_DATA = array();
                    }
                }

                if ( count($ONK_SL_DATA) > 0 ) {
                    $visit['ONK_SL_DATA'][] = $ONK_SL_DATA;
                }

                foreach ( $KSG_KPG_FIELDS as $field ) {
                    if (array_key_exists($field, $visit)) {
                        if (isset($visit[$field])) {
                            if (in_array($field, array('DKK2'))) {
                                if (!isset($KSG_KPG_DATA['DKK2_DATA'])) {
                                    $KSG_KPG_DATA['DKK2_DATA'] = array();
                                }

                                $DKK2Array = explode(',', $visit[$field]);
                                foreach ($DKK2Array as $oneDKK2) {
                                    if (!empty($oneDKK2)) {
                                        $KSG_KPG_DATA['DKK2_DATA'][] = array('DKK2_VAL' => $oneDKK2);
                                    }
                                }
                            } else {
                                $KSG_KPG_DATA[$field] = $visit[$field];
                            }
                        }
                    }

                    unset($visit[$field]);
                }

                if ( count($KSG_KPG_DATA) > 0 ) {
                    foreach ($KSG_KPG_FIELDS as $field) {
                        if ( !in_array($field, array('DKK2')) && !isset($KSG_KPG_DATA[$field]) ) {
                            $KSG_KPG_DATA[$field] = null; // заполняем недостающие поля null
                        }
                    }

                    $KSG_KPG_DATA['CRIT_DATA'] = array();
                    $KSG_KPG_DATA['SL_KOEF'] = array();

                    if ( !isset($KSG_KPG_DATA['DKK2_DATA']) ) {
                        $KSG_KPG_DATA['DKK2_DATA'] = array();
                    }

                    if ( isset($CRIT[$Evn_sid]) ) {
                        $KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$Evn_sid];
                    }

                    if ( isset($SL_KOEF[$Evn_sid]) ) {
                        $KSG_KPG_DATA['SL_KOEF'] = $SL_KOEF[$Evn_sid];
                    }
                    else if ( !empty($visit['IDSL']) && !empty($visit['Z_SL']) ) {
                        $KSG_KPG_DATA['SL_KOEF'][] = array('IDSL' => $visit['IDSL'], 'Z_SL' => $visit['Z_SL']);
                    }

                    $visit['KSG_KPG'][] = $KSG_KPG_DATA;
                }

                foreach ( $SANK_FIELDS as $field ){
                    if ( !empty($visit[$field]) ) {
                        $SANK_DATA[$field] = $visit[$field];
                    }

                    unset($visit[$field]);
                }

                if ( count($SANK_DATA) > 0 ) {
                    $visit['SANK'][] = $SANK_DATA;
                }

                if ( isset($USL[$Evn_sid]) ) {
                    $visit['USL'] = $USL[$Evn_sid];
                }

                if ( $type != 14 && $data['Registry_IsZNO'] != 2 && count($visit['USL']) > 0 ) {
                    if ( !empty($visit['DS_ONK']) && $visit['DS_ONK'] == 1 && isset($NAPR[$Evn_sid]) ) {
                        foreach ( $NAPR[$Evn_sid] as $oneNAPR ) {
                            switch ( true ) {
                                case (intval($oneNAPR['NAPR_V']) == 3 && in_array($type, array(1, 2))):
                                    $naprByUslCode = false;

                                    foreach ( $visit['USL'] as $uslIndex => $usluga ) {
                                        if ( $usluga['CODE_USL'] == $oneNAPR['NAPR_USL'] ) {
                                            $visit['USL'][$uslIndex]['NAPR_DATA'][] = $oneNAPR;
                                            $naprByUslCode = true;
                                            break;
                                        }
                                    }

                                    if ( $naprByUslCode === false ) {
                                        $visit['USL'][count($visit['USL']) - 1]['NAPR_DATA'][] = $oneNAPR;
                                    }
                                    break;

                                default:
                                    $visit['USL'][count($visit['USL']) - 1]['NAPR_DATA'][] = $oneNAPR;
                                    break;
                            }
                        }

                        unset($NAPR[$Evn_sid]);
                    }

                    if ( count($visit['ONK_SL_DATA']) == 0 ) {
                        foreach ( $visit['USL'] as $uslIndex => $usluga ) {
                            $visit['USL'][$uslIndex]['ONK_USL_DATA'] = array();
                        }
                    }
                }

                $value[$k] = $visit;
            }

            $respdata['ZAP'][$key]['Z_SL_DATA'][0]['SL'] = $value;
        }

        $this->setRegistryParamsByType(array(
            'RegistryType_id' => $type
        ));

        foreach ( $respdata['ZAP'] as $key => $value ) {
            if ( !isset($respdata['ZAP'][$key]['Z_SL_DATA']) ) {
                unset($respdata['ZAP'][$key]);
            }
            else {
                $this->_N_ZAP++;

                if ( isset($value['Z_SL_DATA'][0]['SL']) && is_array($value['Z_SL_DATA'][0]['SL'])) {
                    foreach ($value['Z_SL_DATA'][0]['SL'] as $vizit) {
                        // связь между SL_ID и Evn_id/Evn_sid, на всякий случай, не факт что нужна, вроде в SL_ID и так выгружается Evn_sid.
                        $Registry_EvnNum[$vizit['SL_ID']] = array(
                            'Evn_id' => $vizit['Evn_id'],
                            'Evn_sid' => $vizit['Evn_sid'],
                            'N_ZAP' => $this->_N_ZAP
                        );
                    }
                }

                $number++;
                $respdata['ZAP'][$key]['N_ZAP'] = $this->_N_ZAP;
                $respdata['ZAP'][$key]['Z_SL_DATA'][0]['IDCASE'] = $this->_N_ZAP;
            }
        }

        $respdata['PACIENT'] = array();
        // дубли по ID_PAC в файле пациентов не нужны, поэтому формируем массив без дублей
        foreach($PACIENT as $key => $pac) {
            $respdata['PACIENT'][$pac['ID_PAC']] = $pac;
        }

        return $respdata;
    }
    /**
     * Сохранение данных реестра ТФОМС
     */
    function saveRegistryImportTFOMS($data, $toInsert) {
        $query = "
			insert into
				{$this->scheme}.RegistryImportTFOMS (Registry_id, N_ZAP, ID_PAC, Person_SurName, Person_FirName, Person_SecName, Polis_Ser, Polis_Num, PolisType_CodeF008, Orgsmo_f002smocod, Org_OGRN, Org_OKATO, OrgSMO_Name, RegistryImportTFOMS_IsDeputy, DATE_1, DATE_2, NOVOR, PODR, pmUser_insID, pmUser_updID, RegistryImportTFOMS_insDT, RegistryImportTFOMS_updDT)
			values
		";

        $values = "";
        $queryParams = array(
            'Registry_id' => $data['Registry_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $k = 0;
        foreach($toInsert as $oneInsert) {
            $k++;
            if (!empty($values)) {
                $values .= ",";
            }
            $values .= "
				(:Registry_id, :N_ZAP{$k}, :ID_PAC{$k}, :Person_SurName{$k}, :Person_FirName{$k}, :Person_SecName{$k}, :Polis_Ser{$k}, :Polis_Num{$k}, :PolisType_CodeF008{$k}, :Orgsmo_f002smocod{$k}, :Org_OGRN{$k}, :Org_OKATO{$k}, :OrgSMO_Name{$k}, :RegistryImportTFOMS_IsDeputy{$k}, :DATE_1{$k}, :DATE_2{$k}, :NOVOR{$k}, :PODR{$k}, :pmUser_id, :pmUser_id, GETDATE(), GETDATE())
			";

            $queryParams['N_ZAP'.$k] = $oneInsert['N_ZAP'];
            $queryParams['ID_PAC'.$k] = $oneInsert['ID_PAC'];
            $queryParams['Polis_Ser'.$k] = $oneInsert['Polis_Ser'];
            $queryParams['Polis_Num'.$k] = $oneInsert['Polis_Num'];
            $queryParams['PolisType_CodeF008'.$k] = $oneInsert['PolisType_CodeF008'];
            $queryParams['Orgsmo_f002smocod'.$k] = $oneInsert['Orgsmo_f002smocod'];
            $queryParams['Org_OGRN'.$k] = $oneInsert['Org_OGRN'];
            $queryParams['Org_OKATO'.$k] = $oneInsert['Org_OKATO'];
            $queryParams['OrgSMO_Name'.$k] = $oneInsert['OrgSMO_Name'];
            $queryParams['Person_SurName'.$k] = $oneInsert['Person_SurName'];
            $queryParams['Person_FirName'.$k] = $oneInsert['Person_FirName'];
            $queryParams['Person_SecName'.$k] = $oneInsert['Person_SecName'];
            $queryParams['RegistryImportTFOMS_IsDeputy'.$k] = $oneInsert['RegistryImportTFOMS_IsDeputy'];
            $queryParams['DATE_1'.$k] = $oneInsert['DATE_1'];
            $queryParams['DATE_2'.$k] = $oneInsert['DATE_2'];
            $queryParams['NOVOR'.$k] = $oneInsert['NOVOR'];
            $queryParams['PODR'.$k] = $oneInsert['PODR'];
        }

        $query .= $values;

        $this->db->query($query, $queryParams);
        unset($query);
        unset($queryParams);
    }

    /**
     * Кэширование некоторых параметров реестра в зависимости от его типа
     */
    function setRegistryParamsByType($data = array(), $force = false) {
        parent::setRegistryParamsByType($data, $force);

        switch ( $this->RegistryType_id ) {
            case 1:
            case 14:
                $this->MaxEvnField = 'Evn_rid';

                $this->RegistryDataObject = 'RegistryDataEvnPS';
                $this->RegistryErrorObject = 'RegistryErrorEvnPS';
                $this->RegistryErrorComObject = 'RegistryErrorComEvnPS';
                $this->RegistryNoPolisObject = 'RegistryEvnPSNoPolis';
                $this->RegistryDataSLObject = 'RegistryDataEvnPSSL';
                break;

            case 6:
                $this->RegistryDataObject = 'RegistryDataCmp';
                $this->RegistryDataEvnField = 'CmpCallCard_id';
                $this->RegistryDoubleObject = 'RegistryCmpDouble';
                $this->RegistryErrorObject = 'RegistryErrorCmp';
                $this->RegistryErrorComObject = 'RegistryErrorComCmp';
                $this->RegistryNoPolisObject = 'RegistryCmpNoPolis';
                break;

            case 7:
            case 9:
                $this->RegistryDataObject = 'RegistryDataDisp';
                $this->RegistryErrorObject = 'RegistryErrorDisp';
                $this->RegistryErrorComObject = 'RegistryErrorComDisp';
                $this->RegistryNoPolisObject = 'RegistryDispNoPolis';
                break;

            case 11:
            case 12:
                $this->RegistryDataObject = 'RegistryDataProf';
                $this->RegistryErrorObject = 'RegistryErrorProf';
                $this->RegistryErrorComObject = 'RegistryErrorComProf';
                $this->RegistryNoPolisObject = 'RegistryProfNoPolis';
                break;

            case 15:
                $this->RegistryDataObject = 'RegistryDataPar';
                $this->RegistryErrorObject = 'RegistryErrorPar';
                $this->RegistryErrorComObject = 'RegistryErrorComPar';
                $this->RegistryNoPolisObject = 'RegistryParNoPolis';
                break;
        }
    }

    /**
     * Простановка признака "Заблокирован" для реестра
     * @task https://redmine.swan.perm.ru/issues/74209
     * @task https://redmine.swan.perm.ru/issues/66772
     */
    function setRegistryIsLocked($data) {
        if ( empty($data['Registry_IsLocked']) || $data['Registry_IsLocked'] != 2 ) {
            $data['Registry_IsLocked'] = null;
        }

        $query = "
        DO $$
            begin
                update {$this->scheme}.Registry
                set
                    Registry_IsLocked = :Registry_IsLocked,
                    Registry_updDT = dbo.tzGetDate(),
                    pmUser_updID = :pmUser_id
                where
                    Registry_id = :Registry_id;
    
                if ((select RegistryType_id from {$this->scheme}.Registry where Registry_id = :Registry_id) = 13 )
                then
                        update r10.Registry
                        set
                            Registry_IsLocked = :Registry_IsLocked,
                            Registry_updDT = dbo.tzGetDate(),
                            pmUser_updID = :pmUser_id
                        where Registry_id in (select Registry_id from {$this->scheme}.RegistryGroupLink where Registry_pid = :Registry_id);
                end if;
               
            END 
		$$;
			select 0 as \"Error_Code\", '' as \"Error_Msg\";
		";
        $result = $this->db->query($query, $data);

        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return array('success' => false, 'Error_Msg' => 'Ошибка при изменении признака "Заблокирован" для реестра');
        }
    }

    /**
     * Прервать идентификацию
     * @param $data
     * @return array
     */
    public function cancelRegistryIdentification($data)
    {
        $resp = $this->queryResult("
			select
				rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\"
			from
				{$this->scheme}.v_Registry r
				inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				r.Registry_id = :Registry_id
		", array(
            'Registry_id' => $data['Registry_id']
        ));

        if (empty($resp[0]['RegistryCheckStatus_Code']) || $resp[0]['RegistryCheckStatus_Code'] != 2) {
            return array('Error_Msg' => 'Действие доступно только для реестров поставленных в очередь на идентификацию');
        }

        $this->setRegistryCheckStatus(array(
            'Registry_id' => $data['Registry_id'],
            'RegistryCheckStatus_id' => null,
            'pmUser_id' => $data['pmUser_id']
        ));

        return array('Error_Msg' => '');
    }

    /**
     * Проверка статуса реестра
     * Возващает true, если реестр заблокирован
     * @task https://redmine.swan.perm.ru/issues/74209
     * @task https://redmine.swan.perm.ru/issues/66772
     * @param $data
     * @return bool
     */
    public function checkRegistryIsBlocked($data)
    {
        $query = "
			select
			    coalesce(Registry_IsLocked, 1) as \"Registry_IsLocked\"
			from
			    {$this->scheme}.v_Registry r
			where
			    r.Registry_id = :Registry_id
		    limit 1
		";
        $result = $this->db->query($query, array(
            'Registry_id' => $data['Registry_id']
        ));

        if ( !is_object($result) ) {
            return true;
        }

        $resp = $result->result('array');

        if ( is_array($resp) && count($resp) > 0 && $resp[0]['Registry_IsLocked'] == 2 ) {
            return true;
        }

        return false;
    }

    /**
     *    Установка статуса импорта реестра в XML
     * @param $data
     * @return array|bool
     */
    public function SetXmlExportStatus($data)
    {
        if (empty($data['Registry_EvnNum'])) {
            $data['Registry_EvnNum'] = null;
        }

        if (empty($data['Registry_id'])) {
            return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
        }

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
        }

        return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
    }

    /**
     * Проверка наличия оплаченных реестров внутри объединенного
     * @param $Registry_id
     * @return bool
     */
    public function hasRegistryPaid($Registry_id)
    {
        $query = "
			select
				r.Registry_id as \"Registry_id\"
			from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
			where
				RGL.Registry_pid = :Registry_pid
				and R.RegistryStatus_id = 4
			limit 1
		";

        $resp = $this->queryResult($query, array(
            'Registry_pid' => $Registry_id
        ));

        if (count($resp) > 0) {
            return true;
        }

        return false;
    }

    /**
     *    Установка статуса реестра
     * @param $data
     * @return array
     */
    public function setRegistryStatus($data)
    {
        if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
            return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
        }

        // Предварительно получаем тип реестра
        $RegistryType_id = 0;
        $RegistryStatus_id = 0;

        $res = $this->getFirstRowFromQuery("
			select
			    RegistryType_id as \"RegistryType_id\",
			    RegistryStatus_id as \"RegistryStatus_id\"
			from
			    {$this->scheme}.v_Registry Registry
			where Registry_id = :Registry_id
		", array('Registry_id' => $data['Registry_id']));

        if ( is_array($res) && count($res) > 0 ) {
            $RegistryType_id = $res['RegistryType_id'];
            $RegistryStatus_id = $res['RegistryStatus_id'];

            $data['RegistryType_id'] = $RegistryType_id;
        }

        $this->setRegistryParamsByType($data);

        $fields = "";

        if ( $data['RegistryStatus_id'] == 3 ) { // если перевели в работу, то снимаем признак формирования
            //#11018 2. При перемещении реестра в других статусах в состояние "В работу " дополнительно сбрасывать Registry_xmlExpDT
            $fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, Registry_IsLocked = null, ";
        }

        if ($data['RegistryStatus_id'] == 4) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
            $query = "
			    select
			        4 as \"RegistryStatus_id\",
			        Error_Code as \"Error_Code\",
			        Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_setPaid
				(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
            $result = $this->db->query($query, $data);
            if (!is_object($result))
            {
                return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
            }
        }
        else if ($RegistryStatus_id == 4 && $data['RegistryStatus_id'] == 2) { // если переводим из "Оплаченный" в "К оплате" p_Registry_setUnPaid
            $check154914 = $this->checkRegistryDataIsInOtherRegistry($data);

            if ( !empty($check154914) ) {
                return array(array('success' => false, 'Error_Msg' => $check154914));
            }

            $query = "
				select
				  2 as \"RegistryStatus_id\",
				  Error_Code as \"Error_Code\",
				  Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_setUnPaid
				(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
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
     *    Функция возрвращает набор данных для дерева реестра 1-го уровня (тип реестра)
     * @param $data
     * @return array
     */
	public function loadRegistryTypeNode($data) {
		$result = [];

		foreach ( $this->_registryTypeList as $row ) {
        if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
				if ( $row['IsBud'] === true ) {
					$result[] = $row;
				}
			}
			else {
				$result[] = $row;
			}
        }

        return $result;
    }

    /**
     *    Функция возрвращает набор данных для дерева реестра 2-го уровня (статус реестра)
     * @param $data
     * @return array
     */
    public function loadRegistryStatusNode($data)
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
     * Получение списка типов реестров, входящих в объединенный реестр
     * @param int $Registry_pid
     * @return array|bool
     */
    public function getUnionRegistryTypes($Registry_pid = 0)
    {
        $query = "
			select distinct r.RegistryType_id as \"RegistryType_id\"
			from {$this->scheme}.v_RegistryGroupLink rgl
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
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
     * Получение группы случаев из реестров по стационару
     * @param $data
     * @return array|false
     */
    public function getRegistryDataGroupForDelete($data)
    {
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Evn_id' => $data['Evn_id']
        );

        $this->setRegistryParamsByType($data);

        $query = "
			select
			    MaxEvn_id as \"MaxEvn_id\",
    			Evn_rid as \"Evn_rid\"
			from {$this->scheme}.v_{$this->RegistryDataObject} RD
			where RD.Registry_id = :Registry_id and RD.Evn_sid = :Evn_id
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
     *    Помечаем запись реестра на удаление
     * @param $data
     * @return array|bool|false
     */
    public function deleteRegistryData($data)
    {
        $evn_list = $data['EvnIds'];

        //На Карелии случаи в стационаре группируются
        //При удалении одного случая из группы нужно удалить всю группу
        if ($data['RegistryType_id'] == 1 || $data['RegistryType_id'] == 14) {
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
				from {$this->scheme}.p_RegistryData_del
				(
					Evn_id := :Evn_id,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					RegistryData_deleted := :RegistryData_deleted
				)
			";
            $res = $this->db->query($query, $data);
        }

        if (is_object($res)) {
            return $res->result('array');
        }

        return false;
    }

    /**
     * Установка значения поля Lpu_CodeSMO
     * @task https://redmine.swan.perm.ru/issues/58216
     * @task https://redmine.swan.perm.ru/issues/60591
     */
    public function setLpuCodeCMOForEvn($data) {
        if ( $data['EvnClass_Code'] == 111 ) {
            $object = 'CmpCallCard';
        } else if ( in_array($data['EvnClass_Code'], array(7, 8, 9, 101, 103, 104)) ) {
            $object = 'EvnPLDisp';
        } else {
            $object = 'EvnPL';
        }

        $query = "
            update {$object}
            set Lpu_CodeSMO = :Lpu_CodeSMO
            where {$object}_id = :id
		";
        $result = $this->db->query($query, array(
            'id' => (!empty($data['Evn_rid']) ? $data['Evn_rid'] : $data['Evn_id']),
            'Lpu_CodeSMO' => $data['Lpu_CodeSMO']
        ));

        if ( is_object($result) ) {
            return $result->result('array');
        }

        return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (setLpuCodeCMOForEvn)'));
    }

    /**
     * Получение данных Дубли посещений (RegistryDouble)
     * @param $data
     * @return array|bool
     */
    public function loadRegistryDouble($data)
    {
        $this->setRegistryParamsByType($data);

        $filter = "";

        if ( !empty($data['MedPersonal_id']) ) {
            $filter .= " and MP.MedPersonal_id = :MedPersonal_id";
            $params['MedPersonal_id'] = $data['MedPersonal_id'];
        }

        switch ( $this->RegistryType_id ) {
            case 6:
                $query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\",
						 RD.Evn_id as \"Evn_id\",
						 null as \"Evn_rid\",
						 RD.Person_id as \"Person_id\",
						 rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\",
						 RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
						 coalesce(CLC.Year_num, CCC.CmpCallCard_Ngod) as \"Evn_Num\",
						 ETS.EmergencyTeamSpec_Name as \"LpuSection_FullName\",
						 MP.Person_Fio as \"MedPersonal_Fio\",
						 to_char(coalesce(CCC.CmpCallCard_prmDT, CLC.AcceptTime), 'dd.mm.yyyy') as \"Evn_setDate\",
						 CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						left join v_CmpCallCard CCC on CCC.CmpCallCard_id = RD.Evn_id
						left join v_CmpCloseCard CLC on CLC.CmpCallCard_id = CCC.CmpCallCard_id
						left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = CLC.EmergencyTeamSpec_id
						left join lateral(
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = coalesce(CLC.MedPersonal_id, CCC.MedPersonal_id) limit 1
						) as MP on true
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						{$filter}
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
						 RD.Registry_id as \"Registry_id\",
						 RD.Evn_id as \"Evn_id\",
						 EPL.EvnPL_id as \"Evn_rid\",
						 RD.Person_id as \"Person_id\",
						 rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\",
						 RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
						 EPL.EvnPL_NumCard as \"Evn_Num\",
						 LS.LpuSection_FullName as \"LpuSection_FullName\",
						 MP.Person_Fio as \"MedPersonal_Fio\",
						 to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
						 null as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = RD.Evn_id
						left join v_EvnPL EPL  on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
						left join v_LpuSection LS  on LS.LpuSection_id = EVPL.LpuSection_id
						left join lateral (
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = EVPL.MedPersonal_id limit 1
						) as MP on true
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
                break;
        }

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
            }

            return false;
        }
    }

    /**
     * Запрос для проверки наличия данных для вкладки "Дублеи посещений"
     * @param string $scheme
     * @return string
     */
    public function getRegistryDoubleCheckQuery($scheme = 'dbo')
    {
        return "
            select Evn_id as \"Evn_id\" from (
                select Evn_id from {$scheme}.v_RegistryDouble where Registry_id = R.Registry_id
                union all
                select Evn_id from {$scheme}.v_RegistryCmpDouble where Registry_id = R.Registry_id
            ) t limit 1
		";
    }

    /**
     *	Комментарий
     */
    function deleteRegistryDouble($data)
    {
        $data['RegistryType_id'] = $this->RegistryType_id;

        $query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryDouble_del
			(
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				Evn_id := :Evn_id
			)
		";
        $res = $this->db->query($query, $data);
        if (is_object($res)) {
            return $res->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение дополнительных данных для печати счета
     * @param $data
     * @return bool
     */
    public function getAdditionalPrintInfo(&$data)
    {
        if ( !is_array($data) || empty($data['Registry_id']) ) {
            return false;
        }

        if ( !empty($data['KatNasel_SysNick']) && $data['KatNasel_SysNick'] == 'oblast' ) {
            $query = "
				select
					RTRIM(coalesce(o.Org_Name, o.Org_Nick)) as \"OrgP_Name\",
					oa.Address_Address as \"OrgP_Address\",
					o.Org_Phone as \"OrgP_Phone\",
					ors.OrgRSchet_RSchet as \"OrgP_RSchet\",
					ob.OrgBank_Name as \"OrgP_Bank\",
					ob.OrgBank_BIK as \"OrgP_BankBIK\",
					o.Org_INN as \"OrgP_INN\",
					o.Org_KPP as \"OrgP_KPP\",
					Okved.Okved_Code as \"OrgP_OKVED\",
					o.Org_OKPO as \"OrgP_OKPO\",
					Oktmo.Oktmo_Code as \"OrgP_OKTMO\"
				from {$this->scheme}.v_Registry r
					inner join v_OrgSmo os on os.OrgSmo_id = r.OrgSmo_id
					inner join v_Org o on o.Org_id = os.Org_id
					left join Address oa on oa.Address_id = o.UAddress_id
					left join lateral (
						select
							OrgRSchet_RSchet,
							OrgBank_id
						from v_OrgRSchet
						where Org_id = o.Org_id
							and OrgRSchetType_id = 1 -- Расчетный
						limit 1
					) ors on true
					left join v_OrgBank ob on ob.OrgBank_id = ors.OrgBank_id
					left join v_Okved Okved on Okved.Okved_id = o.Okved_id
					left join v_Oktmo Oktmo on Oktmo.Oktmo_id = o.Oktmo_id
				where r.Registry_id = :Registry_id
			    limit 1
			";
            $result = $this->db->query($query, $data);

            if ( is_object($result) ) {
                $response = $result->result('array');

                if ( is_array($response) && count($response) > 0 ) {
                    $data = array_merge($data, $response[0]);
                }
            }
            else {
                return false;
            }
        }

        return true;
    }

    /**
     *    Проверка вхождения случая в реестр
     * @param $data
     * @param string $action
     * @return array|bool
     */
    public function checkEvnInRegistry($data, $action = 'delete')
    {
        $filterList = array();

        if ( !empty($data['EvnPL_id']) ) {
            $filterList[] = "Evn_rid = :EvnPL_id";
            $data['RegistryType_id'] = 2;
        }

        if ( !empty($data['EvnPS_id']) ) {
            $filterList[] = "Evn_rid = :EvnPS_id";
            $data['RegistryType_id'] = 1;
        }

        if ( !empty($data['EvnPLStom_id']) ) {
            $filterList[] = "Evn_rid = :EvnPLStom_id";
            $data['RegistryType_id'] = 2;
        }

        if ( !empty($data['EvnVizitPL_id']) ) {
            $filterList[] = "Evn_id = :EvnVizitPL_id";
            $data['RegistryType_id'] = 2;
        }

        if ( !empty($data['EvnSection_id']) ) {
            $filterList[] = "Evn_id = :EvnSection_id";
            $data['RegistryType_id'] = 1;
        }

        if ( !empty($data['EvnVizitPLStom_id']) ) {
            $filterList[] = "Evn_id = :EvnVizitPLStom_id";
            $data['RegistryType_id'] = 2;
        }

        if ( !empty($data['EvnPLDispDop13_id']) ) {
            $filterList[] = "Evn_id = :EvnPLDispDop13_id";
            $data['RegistryType_id'] = 7;
        }

        if ( !empty($data['EvnPLDispProf_id']) ) {
            $filterList[] = "Evn_id = :EvnPLDispProf_id";
            $data['RegistryType_id'] = 11;
        }

        if ( !empty($data['EvnPLDispOrp_id']) ) {
            $filterList[] = "Evn_id = :EvnPLDispOrp_id";
            $data['RegistryType_id'] = 9;
        }

        if ( !empty($data['EvnPLDispTeenInspection_id']) ) {
            $filterList[] = "Evn_id = :EvnPLDispTeenInspection_id";
            $data['RegistryType_id'] = 12;
        }

        if ( isset($data['CmpCallCard_id']) ) {
            $filterList[] = "Evn_id = :CmpCallCard_id";
            $data['RegistryType_id'] = 6;
        }

        if(isset($data['EvnUslugaPar_id'])) {
            $filterList[] = "Evn_id = :EvnUslugaPar_id";
            $data['RegistryType_id'] = 15;
        }

        if ( count($filterList) == 0 ) {
            return false;
        }

        $this->setRegistryParamsByType($data);

        // @task https://redmine.swan.perm.ru/issues/51767
        if ( in_array($data['RegistryType_id'], array(7, 9, 11, 12)) ) {
            if ( $action == 'edit' ) {
                //return false;
            }

            $query = "
				select
				    DC.DispClass_Code as \"DispClass_Code\"
				from v_Evn E
					inner join v_EvnPLDisp EPLD on EPLD.EvnPLDisp_id = E.Evn_id
					inner join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
				where " . implode(' and ', $filterList) . "
				limit 1
			";
            $result = $this->db->query($query, $data);

            if ( !is_object($result) ) {
                return $this->createError('', 'Ошибка при определении класса диспансеризации');
            }

            $resp = $result->result('array');

            if ( is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4, 8, 11, 12)) ) {
                if ( !empty($data['EvnPLDispTeenInspection_id']) ) {
                    $data['Evn_id'] = $data['EvnPLDispTeenInspection_id'];
                }
                else {
                    $data['Evn_id'] = $data['EvnPLDispOrp_id'];
                }


                $query = "
					SELECT 
						RegistryData.Evn_id AS \"Evn_id\",
						Registry.Registry_Num AS \"Registry_Num\",
						to_char(Registry.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\"
					FROM {$this->scheme}.{$this->RegistryDataObject} RegistryData
						INNER JOIN {$this->scheme}.Registry Registry ON RegistryData.Registry_id = Registry.Registry_id
							AND Registry.RegistryType_id NOT IN (1,14,15)
							and Registry.RegistryStatus_id in (2, 4)
							AND coalesce(Registry.Registry_deleted,1) = 1
						INNER JOIN KatNasel on KatNasel.KatNasel_id = Registry.KatNasel_id
							AND KatNasel_SysNick != 'all'
						inner join v_EvnVizitDisp EVD on EVD.EvnVizitDisp_id = RegistryData.Evn_id
					WHERE coalesce(RegistryData.RegistryData_IsPaid, 1) = 2
						and EVD.EvnVizitDisp_pid = :Evn_id

					UNION ALL

					SELECT 
						RegistryData.Evn_id AS \"Evn_id\",
						Registry.Registry_Num AS \"Registry_Num\",
						to_char(Registry.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\"
					FROM {$this->scheme}.{$this->RegistryDataObject} RegistryData
						INNER JOIN {$this->scheme}.Registry Registry ON RegistryData.Registry_id = Registry.Registry_id
							AND Registry.RegistryType_id IN (1,14,15)
							and Registry.RegistryStatus_id in (2, 4)
							AND coalesce(Registry.Registry_deleted,1) = 1
						INNER JOIN KatNasel on KatNasel.KatNasel_id = Registry.KatNasel_id
							AND KatNasel_SysNick != 'all'
						inner join v_EvnVizitDisp EVD on EVD.EvnVizitDisp_id = RegistryData.Evn_id
					WHERE RegistryData.number IS NOT NULL
						and coalesce(RegistryData.RegistryData_IsPaid, 1) = 2
						and EVD.EvnVizitDisp_pid = :Evn_id
				";
                //echo getDebugSql($query, $data); exit;
                $resp = $this->queryResult($query, $data);

                $actiontxt = 'Удаление';

                switch ( $action ) {
                    case 'edit':
                        $actiontxt = 'Редактирование';
                        break;
                }

                if ( is_array($resp) && count($resp) > 0 ) {
                    return array(
                        array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.')
                    );
                }

                return false;
            }
        }

        $query = "
            select 
                Evn_id as \"Evn_id\",
                Registry_Num as \"Registry_Num\",
                Registry_accDate as \"Registry_accDate\"
            from (
                (
                    select
                        RD.Evn_id,
                        R.Registry_Num,
                        to_char(R.Registry_accDate, 'dd.mm.yyyy') as Registry_accDate
                    from
                        {$this->scheme}.v_{$this->RegistryDataObject} RD
                        inner join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
                        inner join v_KatNasel KN on KN.KatNasel_id = R.KatNasel_id
                        left join v_RegistryCheckStatus RCS on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
                    where
                        " . implode(' and ', $filterList) . "
                        and (
                            coalesce(RD.RegistryData_IsPaid, 1) = 2
                            or R.RegistryStatus_id = 2
                            or (R.RegistryStatus_id = 4 and coalesce(RD.RegistryData_IsPaid, 1) = 2)
                            or RCS.RegistryCheckStatus_Code = 1 -- реестр заблокирован
                        )
                        and R.Lpu_id = :Lpu_id
                        --and KN.KatNasel_SysNick != 'all'
                        limit 1
                )
                union all

                (
                    select
                        RD.Evn_id,
                        R.Registry_Num,
                        to_char(R.Registry_accDate, 'dd.mm.yyyy') as Registry_accDate
                    from
                        {$this->scheme}.RegistryDataTmp RD -- в процессе формирования
                        inner join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
                        inner join v_KatNasel KN on KN.KatNasel_id = R.KatNasel_id
                    where
                        " . implode(' and ', $filterList) . "
                        and R.Lpu_id = :Lpu_id
                        --and KN.KatNasel_SysNick != 'all'
                    limit 1
                )
			) t
		";
        //echo getDebugSql($query, $data); exit;
        $res = $this->db->query($query, $data);

        if ( !is_object($res) ) {
            return array(array('Error_Msg' => 'Ошибка БД!'));
        }

        $actiontxt = 'Удаление';

        switch ( $action ) {
            case 'edit':
                $actiontxt = 'Редактирование';
                break;
        }

        $resp = $res->result('array');

        if ( count($resp) > 0 ) {
            return array(
                array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.')
            );
        } else {
            return false;
        }
    }

    /**
     * Аналог checkEvnInRegistry, болеее универсальный, учитывающий настройки
     * @param $data
     * @param string $action
     * @return array|bool
     */
    public function checkEvnAccessInRegistry($data, $action = 'delete')
    {
        $dbreg = $this->load->database('registry', true);

        $filter = "";
        $join = "";

        if (isset($data['EvnPS_id'])) {
            $filter .= " and Evn_rid = :EvnPS_id";
            $data['RegistryType_id'] = 1;
			if ($action == 'edit') {
				$filter .= " and EvnClass_id <> 47";
			}
        }
        if (isset($data['EvnSection_id'])) {
            $filter .= " and Evn_id = :EvnSection_id";
            $data['RegistryType_id'] = 1;
        }
        if (isset($data['EvnPL_id'])) {
            $filter .= " and Evn_rid = :EvnPL_id";
            $data['RegistryType_id'] = 2;
			if ($action == 'edit') {
				$filter .= " and EvnClass_id <> 47";
			}
        }
        if (isset($data['EvnVizitPL_id'])) {
            $filter .= " and Evn_id = :EvnVizitPL_id";
            $data['RegistryType_id'] = 2;
        }
        if (isset($data['EvnPLStom_id'])) {
            $filter .= " and Evn_rid = :EvnPLStom_id";
            $data['RegistryType_id'] = 16;
			if ($action == 'edit') {
				$filter .= " and EvnClass_id <> 47";
			}
        }
        if (isset($data['EvnVizitPLStom_id'])) {
            $filter .= " and Evn_id = :EvnVizitPLStom_id";
            $data['RegistryType_id'] = 16;
        }
        if (isset($data['EvnPLDispDop13_id'])) {
            $filter .= " and Evn_id = :EvnPLDispDop13_id";
            $data['RegistryType_id'] = 7;
        }
        if (isset($data['EvnPLDispOrp_id'])) {
            $filter .= " and Evn_id = :EvnPLDispOrp_id";
            $data['RegistryType_id'] = 9;
        }
        if (isset($data['EvnPLDispProf_id'])) {
            $filter .= " and Evn_id = :EvnPLDispProf_id";
            $data['RegistryType_id'] = 11;
        }
        if (isset($data['EvnPLDispTeenInspection_id'])) {
            $filter .= " and Evn_id = :EvnPLDispTeenInspection_id";
            $data['RegistryType_id'] = 12;
        }
        if (isset($data['CmpCallCard_id'])) {
            $filter .= " and Evn_id = :CmpCallCard_id";
            $data['RegistryType_id'] = 6;
        }
        if(isset($data['EvnUslugaPar_id'])) {
            $filter .= " and Evn_id = :EvnUslugaPar_id";
            $data['RegistryType_id'] = 15;
        }

        if (empty($filter)) {
            return false;
        }

        // Для 2-ых этапов ДДС и МОН особая логика (в реестр попадает посещение)
        // Либо эта логика устарела, либо нужна только для каких то регионов. На Перми в реестр попадают сами карты.
        if (false && in_array($data['RegistryType_id'], array(7, 9, 11, 12))) {
            $query = "
				select 
				    DC.DispClass_Code as \"DispClass_Code\"
				from
				    v_Evn E
                    inner join v_EvnPLDisp EPLD on EPLD.EvnPLDisp_id = E.Evn_id
                    inner join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
				where (1=1)
					{$filter}
			";
            $result = $this->db->query($query, $data);

            if (!is_object($result)) {
                return array('Error_Msg' => 'Ошибка при определении класса диспансеризации');
            }

            $resp = $result->result('array');

            if (is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4, 8, 11, 12))) {
                if (isset($data['EvnPLDispTeenInspection_id'])) {
                    $filter = "EVD.EvnVizitDisp_pid = :EvnPLDispTeenInspection_id";
                } else {
                    $filter = "EVD.EvnVizitDisp_pid = :EvnPLDispOrp_id";
                }

                $join .= "inner join v_EvnVizitDisp EVD on EVD.EvnVizitDisp_id = RD.Evn_id";
            }
        }

        $this->load->model('Options_model');
        $globalOptions = $this->Options_model->getOptionsGlobals($data);
        $disableEditInReg = !empty($globalOptions['globals']['registry_disable_edit_inreg'])?intval($globalOptions['globals']['registry_disable_edit_inreg']):2;
        $disableEditPaid = !empty($globalOptions['globals']['registry_disable_edit_paid'])?intval($globalOptions['globals']['registry_disable_edit_paid']):2;

        $this->setRegistryParamsByType($data);

        if ( $action == 'delete' || ($action == 'edit' && $data['RegistryType_id'] == 15) ) {
            $query = "
				select
					RD.Evn_id as \"Evn_id\",
					RD.RegistryData_IsPaid as \"RegistryData_IsPaid\",
					R.RegistryStatus_id as \"RegistryStatus_id\",
					R.Registry_Num as \"Registry_Num\",
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\"
				from
					{$this->scheme}.v_{$this->RegistryDataObject} RD
					{$join}
					left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				where (1=1)
					{$filter}
					and R.Lpu_id = :Lpu_id
				limit 1
			";
            $res = $dbreg->query($query, $data);
            if (!is_object($res)) {
                return array('Error_Msg' => 'Ошибка БД!');
            }

            $resp = $res->result('array');

            /*
			 * У пользователя есть возможность удалять случай если:
			 *	1)	Случай не входит в реестр;
			 *	2)	Случай входит в реестр со статусом “В работе”;
			 *	3)	Случай входит в реестр со статусом “Оплаченные”, но при этом он не оплачен.
			 */
            $allowAction = (count($resp) == 0 ) || ($resp[0]['RegistryStatus_id'] == 3 && $data['RegistryType_id'] == 15) || ($resp[0]['RegistryStatus_id'] == 4 && $resp[0]['RegistryData_IsPaid'] != 2);

            if (!$allowAction) {
                $msg = "Запись используется в реестре {$resp[0]['Registry_Num']} от {$resp[0]['Registry_accDate']}.";
                if ($action == 'delete') {
                    $msg .= "<br/>Удаление записи невозможно.";
                } else {
                    $msg .= "<br/>Редактирование записи невозможно.";
                }
                return array('Error_Msg' => $msg);
            }

            return false;
        }

        if (($disableEditPaid == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditPaid == 2) {
            // проверяем признак оплаченности
            $query = "
				select
					RD.Evn_id as \"Evn_id\",
					R.Registry_Num as \"Registry_Num\",
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\"
				from
					{$this->scheme}.v_{$this->RegistryDataObject} RD
					{$join}
					left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				where (1=1)
					{$filter}
					and RD.RegistryData_IsPaid = 2
					and R.Lpu_id = :Lpu_id
				limit 1
			";
            $res = $dbreg->query($query, $data);
            if (!is_object($res)) {
                return array('Error_Msg' => 'Ошибка БД!');
            }

            $actiontxt = 'Удаление';
            switch ($action) {
                case 'delete':
                    $actiontxt = 'Удаление';
                    break;
                case 'edit':
                    $actiontxt = 'Редактирование';
                    break;
            }

            $resp = $res->result('array');
            if (count($resp) > 0) {
                if ($disableEditPaid == 2) {
                    return array('Error_Msg' => 'Запись оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.');
                } else {
                    return array('Error_Msg' => '', 'Alert_Msg' => 'Запись оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи нежелательно!');
                }
            }
        }

        if (($disableEditInReg == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditInReg == 2) {
            // проверяем наличие в реестре "К оплате"
            $query = "
				select
					RD.Evn_id as \"Evn_id\",
					R.Registry_Num as \"Registry_Num\",
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\"
				from
					{$this->scheme}.v_{$this->RegistryDataObject} RD
					{$join}
					left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
				where (1=1)
					{$filter}
					and (
						R.RegistryStatus_id in (2, 5, 6)
						or (R.RegistryStatus_id = 4 and coalesce(RD.RegistryData_IsPaid, 1) = 2)
					)
					and R.Lpu_id = :Lpu_id
				limit 1
			";
            $res = $dbreg->query($query, $data);
            if (!is_object($res)) {
                return array('Error_Msg' => 'Ошибка БД!');
            }

            $actiontxt = 'Удаление';
            switch ($action) {
                case 'delete':
                    $actiontxt = 'Удаление';
                    break;
                case 'edit':
                    $actiontxt = 'Редактирование';
                    break;
            }

            $resp = $res->result('array');
            if (count($resp) > 0) {
                if ($disableEditInReg == 2) {
                    return array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.');
                } else {
                    return array('Error_Msg' => '', 'Alert_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи нежелательно!');
                }
            }
        }

        return false;
    }

    /**
     * Идентификация людей
     */
    public function importRegistryErrorTfomsForIdent($data, $xmlfilepath, $xmlfilepath_pers)
    {
        $recall = 0;

        libxml_use_internal_errors(true);

        $persArray = array();
        $xmlString = file_get_contents($xmlfilepath_pers);
        $xml = new SimpleXMLElement($xmlString);
        unset($xmlString);
        foreach($xml->PERS as $onepers) {
            $persArray[$onepers->ID_PAC->__toString()] = array(
                'FAM' => $onepers->FAM->__toString(),
                'IM' => $onepers->IM->__toString(),
                'OT' => $onepers->OT->__toString(),
                'FAM_P' => $onepers->FAM_P->__toString(),
                'IM_P' => $onepers->IM_P->__toString(),
                'OT_P' => $onepers->OT_P->__toString()
            );
        }
        unset($xml);

        $data['dateMode'] = 120;
        $resp = $this->getRegistryNumberAndDate($data);

        $xmlString = file_get_contents($xmlfilepath);
        $xml = new SimpleXMLElement($xmlString);
        unset($xmlString);

        $nschet = $xml->SCHET->NSCHET->__toString();
        if ($nschet != $resp[0]['Registry_Num']) {
            return array('success' => false, 'Error_Msg' => toUTF('Номер счета из файла не соответствует реестру.'));
        }

        $dschet = $xml->SCHET->DSCHET->__toString();
        if ($dschet != $resp[0]['Registry_accDate']) {
            return array('success' => false, 'Error_Msg' => toUTF('Дата счета из файла не соответствует реестру.'));
        }

        // очищаем записи в RegistryImportTFOMS
        $this->db->query("
			delete from {$this->scheme}.RegistryImportTFOMS WHERE Registry_id = :Registry_id
		", array(
            'Registry_id' => $data['Registry_id']
        ));

        $this->db->save_queries = false;

        $toInsert = array();
        foreach($xml->ZAP as $onezap) {
            $recall++;

            $ID_PAC = $onezap->PACIENT->ID_PAC->__toString();
            $data['N_ZAP'] = $onezap->N_ZAP->__toString();
            $data['VPOLIS'] = $onezap->PACIENT->VPOLIS->__toString();
            $data['SPOLIS'] = $onezap->PACIENT->SPOLIS->__toString();
            $data['NPOLIS'] = $onezap->PACIENT->NPOLIS->__toString();
            $data['SMO'] = $onezap->PACIENT->SMO->__toString();
            $data['SMO_OGRN'] = $onezap->PACIENT->SMO_OGRN->__toString();
            $data['SMO_OK'] = $onezap->PACIENT->SMO_OK->__toString();
            $data['SMO_NAM'] = $onezap->PACIENT->SMO_NAM->__toString();
            $data['NOVOR'] = $onezap->PACIENT->NOVOR->__toString();

            foreach ($onezap->Z_SL->SL as $sl) {
                $data['DATE_1'] = $sl->DATE_1->__toString();
                $data['DATE_2'] = $sl->DATE_2->__toString();
                $data['PODR'] = $sl->PODR->__toString();

                $toInsertOne = array(
                    'N_ZAP' => $data['N_ZAP'],
                    'ID_PAC' => $ID_PAC,
                    'PolisType_CodeF008' => $data['VPOLIS'],
                    'Polis_Ser' => $data['SPOLIS'],
                    'Polis_Num' => $data['NPOLIS'],
                    'Orgsmo_f002smocod' => $data['SMO'],
                    'Org_OGRN' => $data['SMO_OGRN'],
                    'Org_OKATO' => $data['SMO_OK'],
                    'OrgSMO_Name' => $data['SMO_NAM'],
                    'RegistryImportTFOMS_IsDeputy' => 1,
                    'DATE_1' => $data['DATE_1'],
                    'DATE_2' => $data['DATE_2'],
                    'NOVOR' => $data['NOVOR'],
                    'PODR' => $data['PODR']
                );

                // Если заполнены данные представителя, то надо проверять ФИО представителя, а не пациента.
                if (!empty($persArray[$ID_PAC]['FAM_P'])) {
                    $toInsertOne['Person_SurName'] = $persArray[$ID_PAC]['FAM_P'];
                    $toInsertOne['Person_FirName'] = $persArray[$ID_PAC]['IM_P'];
                    $toInsertOne['Person_SecName'] = $persArray[$ID_PAC]['OT_P'];
                    $toInsertOne['RegistryImportTFOMS_IsDeputy'] = 2;
                } else {
                    $toInsertOne['Person_SurName'] = $persArray[$ID_PAC]['FAM'];
                    $toInsertOne['Person_FirName'] = $persArray[$ID_PAC]['IM'];
                    $toInsertOne['Person_SecName'] = $persArray[$ID_PAC]['OT'];
                }

                $toInsert[] = $toInsertOne;

                if (count($toInsert) >= 100) {
                    // сохраняем в r10.RegistryImportTFOMS
                    $this->saveRegistryImportTFOMS(array(
                        'Registry_id' => $data['Registry_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ), $toInsert);
                    unset($toInsert);
                    $toInsert = array();
                }
            }

            if (count($toInsert) >= 100) {
                // сохраняем в r10.RegistryImportTFOMS
                $this->saveRegistryImportTFOMS(array(
                    'Registry_id' => $data['Registry_id'],
                    'pmUser_id' => $data['pmUser_id']
                ), $toInsert);
                unset($toInsert);
                $toInsert = array();
            }
        }

        if (count($toInsert) > 0) {
            // сохраняем в r10.RegistryImportTFOMS
            $this->saveRegistryImportTFOMS(array(
                'Registry_id' => $data['Registry_id'],
                'pmUser_id' => $data['pmUser_id']
            ), $toInsert);
            unset($toInsert);
        }

        // ставим реестру статус "В очереди"
        $data['RegistryCheckStatus_id'] = $this->getFirstResultFromQuery("
                select
                    RegistryCheckStatus_id as \"RegistryCheckStatus_id\"
                from
                    v_RegistryCheckStatus
                where
                    RegistryCheckStatus_Code = :RegistryCheckStatus_Code
                limit 1
        ", array('RegistryCheckStatus_Code' => 2), true);
        $this->setRegistryCheckStatus(array(
            'Registry_id' => $data['Registry_id'],
            'RegistryCheckStatus_id' => $data['RegistryCheckStatus_id'],
            'pmUser_id' => $data['pmUser_id']
        ));

        return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'identifyRegistry'=>1, 'Message' => toUTF('Реестр успешно загружен и поставлен в очередь на идентификацию.'));
    }

    /**
     * Идентификация записей загруженных из реестра ТФОМС
     * @param $data
     * @return array
     */
    public function identifyRegistryErrorTFOMS($data)
    {
        $this->load->library('textlog', array('file'=>'identPersonInRegistry_'.date('Y-m-d').'.log'));

        $this->regDB = $this->db;
        $this->mainDB = $this->load->database('default', true);

        $this->textlog->add('');
        $this->textlog->add('Запуск');

        // получаем OmsSprTerr_id для территории с OmsSprTerr_Code = 1 (Карелия)
        $data['OmsSprTerr_id'] = $this->getOmsSprTerr(array(
            'OmsSprTerr_Code' => 1
        ));

        $this->db->save_queries = false;

        set_time_limit(0);

        // достаём все реестры поставленные в очередь на идентифицкаию
        $resp_r = $this->queryResult("			
			select
				Registry_id,
				Registry_Num,
				pmUser_updID
			from
				{$this->scheme}.v_Registry
			where
				RegistryCheckStatus_id = (select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = '2' limit 1)
				and RegistryType_id = 13 -- объединённый
		");

        $this->load->model('Messages_model');
        foreach($resp_r as $resp_rone) {
            $cnt = 0;
            $cntNoPolis = 0;

            try {
                $this->textlog->add('Начинаем обрабатывать реестр ' . $resp_rone['Registry_id']);

                $data['Registry_id'] = $resp_rone['Registry_id'];

                // проверяем что реестр "в очереди" (может его уже кто подхватил в работу)
                $resp_reg = $this->queryResult("
					select
						rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\"
					from
						{$this->scheme}.v_Registry r
						inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
					where
						r.Registry_id = :Registry_id
				", array(
                    'Registry_id' => $data['Registry_id']
                ));

                if (empty($resp_reg[0]['RegistryCheckStatus_Code']) || $resp_reg[0]['RegistryCheckStatus_Code'] != 2) {
                    continue;
                }

                // берём "в работу"
                $data['RegistryCheckStatus_id'] = $this->getFirstResultFromQuery("select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = :RegistryCheckStatus_Code limit 1", array('RegistryCheckStatus_Code' => 3), true);
                $this->setRegistryCheckStatus(array(
                    'Registry_id' => $data['Registry_id'],
                    'RegistryCheckStatus_id' => $data['RegistryCheckStatus_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));

                $this->textlog->add('deleteRegistryNoPolis');
                $this->deleteRegistryNoPolis($data);
                $this->deleteRegistryErrorTFOMS($data);

                $resp = $this->queryResult("
					select
						RegistryImportTFOMS_id as \"RegistryImportTFOMS_id\",
						ID_PAC as \"ID_PAC\"
						DATE_1 as \"DATE_1\",
						DATE_2 as \"DATE_2\",
						PODR as \"PODR\",
						NOVOR as \"NOVOR\",
						RegistryImportTFOMS_IsDeputy as \"RegistryImportTFOMS_IsDeputy\",
						Person_SurName as \"Person_SurName\",
						Person_SecName as \"Person_SecName\",
						Person_FirName as \"Person_FirName\",
						Polis_Ser as \"Polis_Ser\",
						Polis_Num as \"Polis_Num\"
					from
						{$this->scheme}.v_RegistryImportTFOMS
					where
						Registry_id = :Registry_id
				", array(
                    'Registry_id' => $data['Registry_id']
                ));

                foreach ($resp as $respone) {
                    $cnt++;
                    // надо проверить совпадение полисных данных с существующим человеком и в случае различия добавить новую периодику и перевязать случай на эту пероидку
                    // 1. проводим идентификацию случая
                    $this->textlog->add('checkErrorDataInRegistry ' . $respone['RegistryImportTFOMS_id']);

					$isSMP = false;

					if (preg_match("/^(\d+)(_SMP)?$/", $respone['ID_PAC'], $matches)) {
						$respone['ID_PAC'] = $matches[1];
						$isSMP = true;
					}

					$evnData = $this->checkErrorDataInRegistry(array(
                        'Registry_id' => $data['Registry_id'],
						'isSMP' => $isSMP,
                        'ID_PAC' => $respone['ID_PAC'],
                        'setDT' => !empty($respone['DATE_1'])? ConvertDateFormat($respone['DATE_1'],'Y-m-d') : null,
                        'disDT' => !empty($respone['DATE_2'])? ConvertDateFormat($respone['DATE_2'],'Y-m-d') : null,
                        'PODR' => $respone['PODR'],
                        'NOVOR' => $respone['NOVOR'],
                        'IsDeputy' => $respone['RegistryImportTFOMS_IsDeputy']
                    ), false);
                    if ($evnData === false) {
                        // $errorstxt .= "N_ZAP={$respone['N_ZAP']}, ID_PAC={$respone['ID_PAC']}, Пациент не обнаружен в реестре.\r\n";
                        continue;
                    }

                    // проверяем совпадение ФИО
                    if (
                        mb_strtolower($evnData['Person_Surname']) != mb_strtolower($respone['Person_SurName'])
                        || mb_strtolower($evnData['Person_Secname']) != mb_strtolower($respone['Person_SecName'])
                        || mb_strtolower($evnData['Person_Firname']) != mb_strtolower($respone['Person_FirName'])
                    ) {
                        $params = array(
                            'Evn_id' => $evnData['Evn_id'],
                            'Evn_sid' => $evnData['Evn_sid'],
                            'Registry_id' => $evnData['Registry_id'],
                            'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1020' limit 1"),
                            'RegistryErrorType_Code' => '1020',
                            'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1020' limit 1"),
                            'RegistryType_id' => $evnData['RegistryType_id'],
                            'pmUser_id' => $data['pmUser_id'],
                            'RegistryErrorTFOMS_FieldName' => '',
                            'RegistryErrorTFOMS_BaseElement' => ''
                        );
                        $this->saveRegistryErrorTFOMS($params);
                        continue;
                    }

                    // 1.1. если нет полиса, то не застрахованный
                    // При обнаружении пациента без полиса данные включаются во вкладку «4. Незастрахованные» и все записи по случаям с указанным пациентом удаляются из реестра.
                    // https://redmine.swan.perm.ru/issues/45171 Записи больше не удаляются из реестра
                    if (empty($respone['Polis_Ser']) && empty($respone['Polis_Num'])) {
                        $cntNoPolis++;

                        $this->textlog->add('setRegistryDataNoPolis');
                        $params = array();
                        $params['Evn_id'] = $evnData['Evn_id'];
                        $params['Evn_sid'] = $evnData['Evn_sid'];
                        $params['Registry_id'] = $evnData['Registry_id'];
                        $params['RegistryType_id'] = $evnData['RegistryType_id'];
                        $params['pmUser_id'] = $data['pmUser_id'];
                        if ($evnData['RegistryData_deleted'] != 2) { // если ещё не удалён..
                            try {
                                // попытаемся вставить
                                $this->setRegistryDataNoPolis($params);
                            } catch (Exception $e) {
                                // но не будем падать, т.к. данные уже могли быть вставлены, что вызывает "Violation of PRIMARY KEY constraint 'pk_RegistryNoPolis_id'. Cannot insert duplicate key in object 'r10.RegistryNoPolis'".
                                $this->textlog->add('Не удалось сохранить ' . $e->getMessage());
                            }
                        }
                        continue;
                    }

                    // 2. проводим идентификацию СМО
                    $this->textlog->add('identifyOrgSMO');
                    $respone['OrgSMO_id'] = $this->identifyOrgSMO($respone);
                    if ($respone['OrgSMO_id'] === false) {
                        // $errorstxt .= "ID_PAC={$respone['ID_PAC']}, SMO={$data['SMO']}, SMO_OGRN={$data['SMO_OGRN']}, SMO_OK={$data['SMO_OK']}, SMO_NAM={$data['SMO_NAM']}, СМО не обнаружена в справочнике.\r\n";
                        $params = array(
                            'Evn_id' => $evnData['Evn_id'],
                            'Evn_sid' => $evnData['Evn_sid'],
                            'Registry_id' => $evnData['Registry_id'],
                            'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1029' limit 1"),
                            'RegistryErrorType_Code' => '1029',
                            'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1029' limit 1"),
                            'RegistryType_id' => $evnData['RegistryType_id'],
                            'pmUser_id' => $data['pmUser_id'],
                            'RegistryErrorTFOMS_FieldName' => '',
                            'RegistryErrorTFOMS_BaseElement' => ''
                        );
                        $this->saveRegistryErrorTFOMS($params);
                        continue;
                    }

                    // 3. проводим идентификацию типа полиса
                    $this->textlog->add('identifyPolisType');
                    $respone['PolisType_id'] = $this->identifyPolisType($respone);
                    if ($respone['PolisType_id'] === false) {
                        // $errorstxt .= "VPOLIS={$data['VPOLIS']}, Тип полиса не обнаружен в справочнике.\r\n";
                        $params = array(
                            'Evn_id' => $evnData['Evn_id'],
                            'Evn_sid' => $evnData['Evn_sid'],
                            'Registry_id' => $evnData['Registry_id'],
                            'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1028' limit 1"),
                            'RegistryErrorType_Code' => '1028',
                            'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1028' limit 1"),
                            'RegistryType_id' => $evnData['RegistryType_id'],
                            'pmUser_id' => $data['pmUser_id'],
                            'RegistryErrorTFOMS_FieldName' => '',
                            'RegistryErrorTFOMS_BaseElement' => ''
                        );
                        $this->saveRegistryErrorTFOMS($params);
                        continue;
                    }

                    // https://redmine.swan.perm.ru/issues/43989
                    // ... реализовать разделение на серию и номер временного свидетельства, аналогично тому, как это происходит при идентификации по кнопке.
                    if ($respone['PolisType_id'] == 3 && mb_strlen($respone['Polis_Ser']) == 0 && mb_strlen($respone['Polis_Num']) > 6) {
                        $respone['Polis_Ser'] = mb_substr($respone['Polis_Num'], 0, 3);
                        $respone['Polis_Num'] = mb_substr($respone['Polis_Num'], 3);
                    }
                    if ($respone['PolisType_id'] == 3) {
                        $respone['Polis_Num'] = $respone['Polis_Ser'] . '' . $respone['Polis_Num'];
                        $respone['Polis_Ser'] = null;
                    }
                    // 4. проверяем совпадение полисных данных с хранящимися на случае
                    if (
                        $evnData['Polis_Ser'] != $respone['Polis_Ser']
                        || $evnData['Polis_Num'] != $respone['Polis_Num']
                        || $evnData['OrgSMO_id'] != $respone['OrgSMO_id']
                        || $evnData['PolisType_id'] != $respone['PolisType_id']
                    ) {
                        // используем рабочую БД
                        $this->db = $this->mainDB;

                        // идентифицируем человека с помощью сервиса идентификации и добавляем полис, если его нет.
                        $respone['Person_id'] = $evnData['Person_id'];
                        $respone['Person_Surname'] = $evnData['Person_Surname'];
                        $respone['Person_Firname'] = $evnData['Person_Firname'];
                        $respone['Person_Secname'] = $evnData['Person_Secname'];
                        $respone['Person_Birthday'] = $evnData['Person_Birthday'];
                        $respone['DATEON'] = $evnData['Evn_disDate'];
                        $respone['OmsSprTerr_id'] = $data['OmsSprTerr_id'];
                        $respone['Server_id'] = $data['Server_id'];
                        $respone['pmUser_id'] = $data['pmUser_id'];
                        $this->textlog->add('identifyAndAddNewPolisToPerson');
                        $resp = $this->identifyAndAddNewPolisToPerson($respone);

                        // используем опять реестровую БД
                        $this->db = $this->regDB;

                        switch ($resp) {
                            case 1:
                                // $errorstxt .= "ID_PAC={$respone['ID_PAC']}, Пациент не идентифицирован в БДЗ.\r\n";
                                $params = array(
                                    'Evn_id' => $evnData['Evn_id'],
                                    'Evn_sid' => $evnData['Evn_sid'],
                                    'Registry_id' => $evnData['Registry_id'],
                                    'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1025' limit 1"),
                                    'RegistryErrorType_Code' => '1025',
                                    'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1025' limit 1"),
                                    'RegistryType_id' => $evnData['RegistryType_id'],
                                    'pmUser_id' => $data['pmUser_id'],
                                    'RegistryErrorTFOMS_FieldName' => '',
                                    'RegistryErrorTFOMS_BaseElement' => ''
                                );
                                $this->saveRegistryErrorTFOMS($params);
                                break;

                            case 2:
                                // $errorstxt .= "ID_PAC={$respone['ID_PAC']}, Сервис идентификации недоступен.\r\n";
                                $params = array(
                                    'Evn_id' => $evnData['Evn_id'],
                                    'Evn_sid' => $evnData['Evn_sid'],
                                    'Registry_id' => $evnData['Registry_id'],
                                    'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1026' limit 1"),
                                    'RegistryErrorType_Code' => '1026',
                                    'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1026'  limit 1"),
                                    'RegistryType_id' => $evnData['RegistryType_id'],
                                    'pmUser_id' => $data['pmUser_id'],
                                    'RegistryErrorTFOMS_FieldName' => '',
                                    'RegistryErrorTFOMS_BaseElement' => ''
                                );
                                $this->saveRegistryErrorTFOMS($params);
                                break;

                            case 3:
                                // $errorstxt .= "ID_PAC={$respone['ID_PAC']}, Ошибка сервиса идентификации.\r\n";
                                $params = array(
                                    'Evn_id' => $evnData['Evn_id'],
                                    'Evn_sid' => $evnData['Evn_sid'],
                                    'Registry_id' => $evnData['Registry_id'],
                                    'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1027' limit 1"),
                                    'RegistryErrorType_Code' => '1027',
                                    'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1027' limit 1"),
                                    'RegistryType_id' => $evnData['RegistryType_id'],
                                    'pmUser_id' => $data['pmUser_id'],
                                    'RegistryErrorTFOMS_FieldName' => '',
                                    'RegistryErrorTFOMS_BaseElement' => ''
                                );
                                $this->saveRegistryErrorTFOMS($params);
                                break;

                            case 4:
                                // $errorstxt .= "ID_PAC={$respone['ID_PAC']}, Пациенту не удалось определить тип полиса.\r\n";
                                $params = array(
                                    'Evn_id' => $evnData['Evn_id'],
                                    'Evn_sid' => $evnData['Evn_sid'],
                                    'Registry_id' => $evnData['Registry_id'],
                                    'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1028' limit 1"),
                                    'RegistryErrorType_Code' => '1028',
                                    'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1028' limit 1"),
                                    'RegistryType_id' => $evnData['RegistryType_id'],
                                    'pmUser_id' => $data['pmUser_id'],
                                    'RegistryErrorTFOMS_FieldName' => '',
                                    'RegistryErrorTFOMS_BaseElement' => ''
                                );
                                $this->saveRegistryErrorTFOMS($params);
                                break;

                            case 5:
                                // $errorstxt .= "ID_PAC={$respone['ID_PAC']}, Пациенту не удалось определить СМО.\r\n";
                                $params = array(
                                    'Evn_id' => $evnData['Evn_id'],
                                    'Evn_sid' => $evnData['Evn_sid'],
                                    'Registry_id' => $evnData['Registry_id'],
                                    'RegistryErrorType_id' => $this->getFirstResultFromQuery("select RegistryErrorType_id from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1029' limit 1"),
                                    'RegistryErrorType_Code' => '1029',
                                    'COMMENT' => $this->getFirstResultFromQuery("select RegistryErrorType_Name from {$this->scheme}.v_RegistryErrorType where RegistryErrorType_Code = '1029' limit 1"),
                                    'RegistryType_id' => $evnData['RegistryType_id'],
                                    'pmUser_id' => $data['pmUser_id'],
                                    'RegistryErrorTFOMS_FieldName' => '',
                                    'RegistryErrorTFOMS_BaseElement' => ''
                                );
                                $this->saveRegistryErrorTFOMS($params);
                                break;

                            case 6:
                                break;

                            case 7:
                                break;
                        }
                    }
                }

                // Идентифицирован
                $this->textlog->add('Проставляем статус идентифицирован');
                $data['RegistryCheckStatus_id'] = $this->getFirstResultFromQuery("select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = :RegistryCheckStatus_Code limit 1", array('RegistryCheckStatus_Code' => 4), true);
                $this->setRegistryCheckStatus(array(
                    'Registry_id' => $data['Registry_id'],
                    'RegistryCheckStatus_id' => $data['RegistryCheckStatus_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
            } catch (Exception $e) {
                // Идентифицирован
                $this->textlog->add('Ошибка обработки реестра '.$e->getMessage());
                $data['RegistryCheckStatus_id'] = $this->getFirstResultFromQuery("select RegistryCheckStatus_id from v_RegistryCheckStatus where RegistryCheckStatus_Code = :RegistryCheckStatus_Code limit 1", array('RegistryCheckStatus_Code' => 5), true);
                $this->setRegistryCheckStatus(array(
                    'Registry_id' => $data['Registry_id'],
                    'RegistryCheckStatus_id' => $data['RegistryCheckStatus_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
            }

            $messageData = array(
                'autotype' => 1,
                'title' => 'Идентификация объединенного реестра',
                'type' => 1,
                'User_rid' => $resp_rone['pmUser_updID'],
                'pmUser_id' => $data['pmUser_id'],
                'text' => "Идентификация объединенного реестра {$resp_rone['Registry_Num']} завершена. Обработано записей: {$cnt}. Незастрахованных: {$cntNoPolis}."
            );
            $this->Messages_model->autoMessage($messageData);
        }

        $this->textlog->add('Все');

        return array('success' => true);
    }

    /**
     * Возвращает список настроек ФЛК
     */
    public function loadRegistryEntiesSettings($data)
    {
        if (empty($data['RegistryGroupType_id'])) {
            return false;
        }

        $where = ' AND RegistryGroupType_id = '.$data['RegistryGroupType_id'];

        $params = array();
        $query = "
			SELECT
				FLKSettings_id as \"FLKSettings_id\",
				RegistryType_id as \"RegistryType_id\",
				RegistryGroupType_id as \"RegistryGroupType_id\",
				FLKSettings_EvnData as \"FLKSettings_EvnData\",
				FLKSettings_PersonData as \"FLKSettings_PersonData\"
			FROM v_FLKSettings
			WHERE 
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then '2030-01-01'
						else FLKSettings_endDate
					end
				AND FLKSettings_EvnData iLIKE '%kareliya%'
		".$where . "\n limit 1";
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
     * @param $xml_data
     * @param $xsd_tpl
     * @param string $type
     * @param string $output_file_name
     * @return bool
     */
    public function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
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
    public function libxml_display_errors()
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
     * Чтение списка реестров
     * @param $data
     * @return bool
     */
    public function loadRegistry($data)
    {
        $filter = "(1=1)";
        $params = array('Lpu_id' => $data['Lpu_id'] ?? $data['session']['lpu_id']);
        $filter .= ' and R.Lpu_id = :Lpu_id';

        $this->setRegistryParamsByType($data);

        $IsZNOField = "case when R.Registry_IsZNO = 2 then 'true' else 'false' end as \"Registry_IsZNO\",";

        if ( !empty($data['Registry_id']) ) {
            $filter .= ' and R.Registry_id = :Registry_id';
            $params['Registry_id'] = $data['Registry_id'];
            $IsZNOField = "coalesce(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",";
        }

        if ( !empty($data['RegistryType_id']) ) {
            $filter .= ' and R.RegistryType_id = :RegistryType_id';
            $params['RegistryType_id'] = $data['RegistryType_id'];
        }

        if ( empty($data['Registry_id']) ) {
            if ( !empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud' ) {
                $filter .= " and pt.PayType_SysNick in ('bud', 'fbud', 'subrf')";
            }
            else {
                $filter .= " and coalesce(pt.PayType_SysNick, '') not in ('bud', 'fbud', 'subrf')";
            }
        }

        $loadDeleted = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 12);
        $loadQueue = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 11);

        if ($loadQueue) {
            $query = "
				select
					R.RegistryQueue_id as \"Registry_id\",
					R.KatNasel_id as \"KatNasel_id\",
					R.RegistryType_id as \"RegistryType_id\",
					12 as \"RegistryStatus_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					2 as \"Registry_IsActive\",
					RTrim(R.Registry_Num) || ' / в очереди: ' || LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
					{$IsZNOField}
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
					KatNasel.KatNasel_Name as \"KatNasel_Name\",
					KatNasel.KatNasel_SysNick as \"KatNasel_SysNick\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					R.Lpu_id as \"Lpu_id\",
					R.OrgRSchet_id as \"OrgRSchet_id\",
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
					R.DispClass_id as \"DispClass_id\",
					coalesce(R.Registry_IsLocked, 1) as \"Registry_IsLocked\",
					R.Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
					pt.PayType_id as \"PayType_id\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					pt.PayType_Name as \"PayType_Name\",
					0 as \"RegistryHealDepCheckJournal_AccRecCount\",
					0 as \"RegistryHealDepCheckJournal_DecRecCount\",
					0 as \"RegistryHealDepCheckJournal_UncRecCount\"
				from {$this->scheme}.v_RegistryQueue R
					left join KatNasel on KatNasel.KatNasel_id = R.KatNasel_id
					left join LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join v_PayType pt on pt.PayType_id = R.PayType_id
				where {$filter}
			";
        }
        // Готовые реестры
        else {
            $source_table = 'v_Registry';

            if ( isset($data['RegistryStatus_id']) ) {
                if ( $loadDeleted ) {
                    // если запрошены удаленные реестры
                    $source_table = 'v_Registry_deleted';
                }
                else {
                    $filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
                    $params['RegistryStatus_id'] = $data['RegistryStatus_id'];
                }

                // только если оплаченные!!!
                if( 4 == (int)$data['RegistryStatus_id'] ) {
                    if ( $data['Registry_accYear'] > 0 ) {
                        $filter .= ' and date_part("year", R.Registry_begDate) <= :Registry_accYear';
                        $filter .= ' and date_part("year", R.Registry_endDate) >= :Registry_accYear';
                        $params['Registry_accYear'] = $data['Registry_accYear'];
                    }
                }
            }

            $query = "
				Select
					R.Registry_id as \"Registry_id\",
					R.KatNasel_id as \"KatNasel_id\",
					R.RegistryType_id as \"RegistryType_id\",
					" . (!empty($data['RegistryStatus_id']) && 12 == (int)$data['RegistryStatus_id'] ? "12 as \"RegistryStatus_id\"" : "R.RegistryStatus_id as \"RegistryStatus_id\"") . ",
					R.RegistryStacType_id as \"RegistryStacType_id\", 
					R.Registry_IsActive as \"Registry_IsActive\",
					RTrim(R.Registry_Num) as \"Registry_Num\",
					{$IsZNOField}
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
					KatNasel.KatNasel_Name as \"KatNasel_Name\",
					KatNasel.KatNasel_SysNick as \"KatNasel_SysNick\",
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
					" . ($source_table == 'v_Registry' ? "coalesce(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\"," : "") . "
					" . ($source_table == 'v_Registry' ? "coalesce(RegistryStom.RegistryStom_KdPlan, 0) as \"RegistryStom_KdPlan\"," : "") . "
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					to_char(R.Registry_updDT, 'dd.mm.yyyy hh24:mi') as \"Registry_updDate\",
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
					to_char(RQH.RegistryQueueHistory_endDT, 'dd.mm.yyyy hh24:mi:ss') as \"ReformTime\",
					R.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
					coalesce(RegistryCheckStatus.RegistryCheckStatus_Code, -1) as \"RegistryCheckStatus_Code\",
					RegistryCheckStatus.RegistryCheckStatus_SysNick as  \"RegistryCheckStatus_SysNick\",
					case when exists (" . $this->getRegistryDoubleCheckQuery($this->scheme) . ") then 1 else 0 end as \"issetDouble\",
					R.OrgSMO_id as \"OrgSMO_id\",
					RegistryCheckStatus.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
					R.DispClass_id as \"DispClass_id\",
					coalesce(R.Registry_IsLocked, 1) as \"Registry_IsLocked\",
					R.Registry_IsOnceInTwoYears as \"Registry_IsOnceInTwoYears\",
					pt.PayType_id as \"PayType_id\",
					pt.PayType_SysNick as \"PayType_SysNick\",
					pt.PayType_Name as \"PayType_Name\",
					rhdcj.RegistryHealDepCheckJournal_AccRecCount as \"RegistryHealDepCheckJournal_AccRecCount\",
					rhdcj.RegistryHealDepCheckJournal_DecRecCount as \"RegistryHealDepCheckJournal_DecRecCount\",
					rhdcj.RegistryHealDepCheckJournal_UncRecCount as \"RegistryHealDepCheckJournal_UncRecCount\"
				from {$this->scheme}.{$source_table} R
					left join KatNasel on KatNasel.KatNasel_id = R.KatNasel_id
					left join RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join RegistryCheckStatus on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join v_PayType pt on pt.PayType_id = R.PayType_id
					left join lateral (
						select
							sum(RS.RegistryData_KdPlan) as RegistryStom_KdPlan
						from {$this->scheme}.v_RegistryData RS
						inner join v_Evn Evn on RS.Evn_id = Evn.Evn_id
						where RS.Registry_id = R.Registry_id and Evn.EvnClass_SysNick in ('EvnVizitPLStom', 'EvnPLStom', 'EvnUslugaStom')
					) RegistryStom on true
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
					left join lateral  (
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
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$this->scheme}.v_{$this->RegistryErrorComObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorCom on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryError on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE where RE.Registry_id = R.Registry_id and coalesce(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1 limit 1) RegistryPerson on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_{$this->RegistryNoPolisObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryNoPolis on true
					left join lateral (
						select
							count(RegistryNoPay.Evn_id) as RegistryNoPay_Count,
							sum(RegistryNoPay.RegistryNoPay_UKLSum) as RegistryNoPay_UKLSum
						from {$this->scheme}.v_RegistryNoPay RegistryNoPay
						where RegistryNoPay.Registry_id = R.Registry_id
					) RegistryNoPay on true
					left join lateral (
						select
							count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
						from {$this->scheme}.v_{$this->RegistryDataObject} RDnoPaid
						where RDnoPaid.Registry_id = R.Registry_id and coalesce(RDnoPaid.RegistryData_isPaid, 1) = 1
					) RegistryNoPaid on true
					left join lateral (select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id from {$this->scheme}.v_RegistryErrorTFOMS RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorTFOMS on true
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
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $data['Registry_id']
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml($data) {
		// Для того чтобы не захламлять память
		$this->db->save_queries = false;

		$this->load->library('parser');

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		try {
			$this->load->library('textlog', [ 'file'=>'exportRegistryToXml_' . date('Y_m_d') . '.log' ]);
			$this->textlog->add('');
			$this->textlog->add('Запуск формирования реестра (Registry_id = ' . $data['Registry_id'] .')');

			// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
			$res = $this->GetRegistryXmlExport($data);

			if ( !is_array($res) || count($res) == 0 ) {
				throw new Exception('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			}

			$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
			$data['PayType_SysNick'] = null;
			$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
			$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
			$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'];

			$OrgSmo_Code = ''; // OrgSmo_f002smocod
			$registryIsUnion = ($res[0]['RegistryType_id'] == 13);
			$type = $res[0]['RegistryType_id'];

			if ( 'oblast' == $data['KatNasel_SysNick'] ) {
				$OrgSmo_Code = $this->getOrgSmoCode($data['Registry_id']);

				if ( $OrgSmo_Code === false ) {
					throw new Exception('Ошибка при получении кода СМО');
				}
			}

			// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
			if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
				throw new Exception('Часть реестров нуждается в переформировании, экспорт невозможен.');
			}

			// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
			if ( !empty($res[0]['Registry_SumDifference']) ) {
				throw new Exception('Экспорт невозможен. Неверная сумма по счёту и реестрам.', 12);
			}

			// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
			if ( empty($res[0]['RegistryData_Count']) ) {
				throw new Exception('Экспорт невозможен. Нет случаев в реестре.', 13);
			}

			if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
				throw new Exception('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			}

			// если уже выгружен реестр
			if ( !empty($res[0]['Registry_xmlExportPath']) ) {
				$this->textlog->add('Получили путь из БД:' . $res[0]['Registry_xmlExportPath']);

				if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
					throw new Exception('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
				}

				if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
					if ( !empty($data['forSign']) ) {
						$this->textlog->add('Возвращаем пользователю файл в base64');
						$filebase64 = base64_encode(file_get_contents($res[0]['Registry_xmlExportPath']));
						return [ 'success' => true, 'filebase64' => $filebase64 ];
					}

					$response = [
						'success' => true,
						'Link' => $res[0]['Registry_xmlExportPath'],
					];

					if ( empty($data['onlyLink']) ) {
						$response['usePrevXml'] = true;
					}

					$this->textlog->add('Выход с передачей ссылки: ' . $response['Link']);

					return $response;
				}

				// Запрет переформирования заблокированного реестра
				// @task https://redmine.swan.perm.ru/issues/74209
				if ( !empty($res[0]['Registry_IsLocked']) && $res[0]['Registry_IsLocked'] == 2 ) {
					throw new Exception('Реестр заблокирован, переформирование невозможно.');
				}
			}

			$dateX20180401 = '20180401';
			$dateX20180901 = '20180901';
			$dateX20181101 = '20181101';
			$dateX20181201 = '20181201';
			$dateX20190101 = '20190101';
			$dateX20191101 = '20191101';
			$data['registryIsAfter20180401'] = ($res[0]['Registry_begDate'] >= $dateX20180401);
			$data['registryIsAfter20180901'] = ($res[0]['Registry_begDate'] >= $dateX20180901);
			$data['registryIsAfter20181101'] = ($res[0]['Registry_begDate'] >= $dateX20181101);
			$data['registryIsAfter20181201'] = ($res[0]['Registry_begDate'] >= $dateX20181201);
			$data['registryIsAfter20190101'] = ($res[0]['Registry_begDate'] >= $dateX20190101);
			$data['registryIsAfter20191101'] = ($res[0]['Registry_begDate'] >= $dateX20191101);

			// Если вернули тип оплаты реестра, то будем его использовать
			if ( isset($res[0]['PayType_SysNick']) ) {
				$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
			}

			$this->textlog->add('Тип оплаты реестра: ' . $data['PayType_SysNick']);
			$this->textlog->add('refreshRegistry: Пересчитываем реестр');

			// Удаление помеченных на удаление записей и пересчет реестра
			$refreshResult = $this->refreshRegistry($data);

			if ( $refreshResult === false || !is_array($refreshResult) || count($refreshResult) == 0 ) {
				throw new Exception('При обновлении данных реестра произошла ошибка.');
			}
			else if ( !empty($refreshResult[0]['Error_Msg']) ) {
				throw new Exception($refreshResult[0]['Error_Msg']);
			}

			$this->textlog->add('refreshRegistry: Реестр пересчитали');
			$this->textlog->add('Тип реестра: ' . $type);

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];

			//Проверка на наличие созданной ранее директории
			if ( !file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				mkdir( EXPORTPATH_REGISTRY . $out_dir );
			}
			$this->textlog->add('Cоздали каталог ' . EXPORTPATH_REGISTRY . $out_dir);

			// Объединенные реестры могут содержать данные любого типа
			// Получаем список типов реестров, входящих в объединенный реестр
			if ( $registryIsUnion ) {
				$registrytypes = $this->getUnionRegistryTypes($data['Registry_id']);

				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}
			}
			else {
				if ( !in_array($type, $this->getAllowedRegistryTypes($data)) ) {
					throw new Exception('Данный тип реестров не обрабатывается.');
				}

				$registrytypes = [ $type ];
			}

			$data['Status'] = '1';
			$this->SetXmlExportStatus($data);
			$this->textlog->add('SetXmlExportStatus: Установили статус реестра в 1');

			$SCHET = $this->_loadRegistrySCHETForXmlUsing($data);

			if ( $SCHET === false ) {
				throw new Exception('Ошибка при получении данных по счету.');
			}

			$first_code = 'S';
			$data_first_code = "H";
			$pers_first_code = "L";

			if ( in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]) ) {
				switch ( $type ) {
					case 1: // stac
						$first_code = 'S';
						break;
					case 2: // polka
						$first_code = 'P';
						break;
				}
			}
			else if ( $registryIsUnion ) {
				switch ( $data['RegistryGroupType_id'] ) {
					case 1:
					case 18:
						if ( $data['Registry_IsZNO'] == 2 ) {
							$data_first_code = "C";
							$pers_first_code = "LC";
						}
						else {
							$data_first_code = "H";
							$pers_first_code = "L";
						}
						break;
					case 2:
						$data_first_code = "T";
						$pers_first_code = "LT";
						break;
					case 3:
					case 19:
						$data_first_code = "DP";
						$pers_first_code = "LP";
						break;
					case 4:
						$data_first_code = "DV";
						$pers_first_code = "LV";
						break;
					case 5:
					case 27:
					case 28:
						$data_first_code = "DS";
						$pers_first_code = "LS";
						break;
					case 6:
					case 29:
					case 30:
						$data_first_code = "DU";
						$pers_first_code = "LU";
						break;
					case 7:
						$data_first_code = "DR";
						$pers_first_code = "LR";
						break;
					case 8:
						$data_first_code = "DD";
						$pers_first_code = "LD";
						break;
					case 9:
					case 31:
					case 32:
						$data_first_code = "DF";
						$pers_first_code = "LF";
						break;
					case 10:
						$data_first_code = "DO";
						$pers_first_code = "LO";
						break;
					case 15:
						$data_first_code = "X";
						$pers_first_code = "LX";
						break;
				}
			}

			$packNum = $this->SetXmlPackNum($data);

			if ( empty($packNum) ) {
				throw new Exception('Ошибка при получении номера выгружаемого пакета.');
			}

			$p_code = 'T';

			if ( in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]) ) {
				$p_code = 'Z';
			}

			if ( in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]) ) {
				$f_type = 'F'; // федеральный
				if ($data['PayType_SysNick'] == 'bud') {
					$f_type = 'L'; // местный
				}
				if ($data['PayType_SysNick'] == 'subrf') {
					$f_type = 'S'; // местный
				}
				$fname_part = "M" . $SCHET['CODE_MO'] . $p_code . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . $packNum;
				$fname = $f_type . "_" . $first_code . "_H" . $fname_part;
				$rname = $data_first_code . $fname_part;
				$pname = $pers_first_code . $fname_part;
			}
			else {
				$fname = "M" . $SCHET['CODE_MO'] . ($data['KatNasel_SysNick'] == 'oblast' ? 'S' . $OrgSmo_Code : $p_code . $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
				$rname = $data_first_code . $fname;
				$pname = $pers_first_code . $fname;
			}

			if (
				in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ])
				|| (
					!empty($data['RegistryGroupType_id'])
					&& in_array($data['RegistryGroupType_id'], [ 1, 18 ])
					&& $data['registryIsAfter20190101'] === false
				)
			) {
				$zname = $fname;
			}
			else {
				$zname = $data_first_code . $fname;
			}

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// случаи
			$this->exportSluchDataFile = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";
			// временный файл для случаев
			$this->exportSluchDataFileTmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";
			// перс. данные
			$this->exportPersonDataFile = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$this->textlog->add('Определили наименования файлов: ' . $this->exportSluchDataFile . ' и ' . $this->exportPersonDataFile);
			$this->textlog->add('Создаем XML файлы на диске');

			if ( in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]) ) {
				$data_file_version = '1.0';
				$templateModificator = "_bud";
			}
			else if ( $data['registryIsAfter20190101'] === true ) {
				$data_file_version = '3.1';
				$templateModificator = "_2019";

				if ( $data['Registry_IsZNO'] == 2 ) {
					$templateModificator = "_zno_2019";
				}
			}
			else if ( $data['registryIsAfter20180901'] === true ) {
				$data_file_version = '3.1';
				$templateModificator = "_2018";

				if ( $data['Registry_IsZNO'] == 2 ) {
					$templateModificator = "_zno_2018";
				}
			}
			else {
				$data_file_version = '3.0';

				if ( $data['registryIsAfter20180401'] === true ) {
					$templateModificator = "_2018";
				}
				else {
					$templateModificator = "";
				}
			}

			if ( in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf')) ) {
				$pers_file_version = '1.0';
			}
			else if ( $data['registryIsAfter20191101'] === true ) {
				$pers_file_version = '3.2';
			}
			else if ( $data['registryIsAfter20181201'] === true ) {
				$pers_file_version = '3.1';
			}
			else {
				$pers_file_version = '2.1';
			}

			$templ_data = "registry_kareliya_pl{$templateModificator}";
			$templ_person = "registry_" . $data['session']['region']['nick'] . "_person";

			if ( !empty($res[0]['RegistryGroupType_id']) && $res[0]['RegistryGroupType_id'] == 2 ) {
				$templ_data = "registry_kareliya_htm_2019";
			}
			else if (!empty($res[0]['RegistryGroupType_id']) && !in_array($res[0]['RegistryGroupType_id'], array(1, 15, 18))) {
				$templ_data = "registry_kareliya_disp{$templateModificator}";
			}

			$this->exportPersonDataBodyTemplate = $templ_person . '_body';
			$this->exportPersonDataFooterTemplate = $templ_person . '_footer';
			$this->exportPersonDataHeaderTemplate = $templ_person . '_header';
			$this->exportSluchDataBodyTemplate = $templ_data . '_body';
			$this->exportSluchDataFooterTemplate = $templ_data . '_footer';
			$this->exportSluchDataHeaderTemplate = $templ_data . '_header';

			// Заголовок для файла с перс. данными
			$ZGLV = [
				'VERSION' => $pers_file_version,
				'DATA' => date('Y-m-d'),
				'FILENAME' => $file_re_pers_data_sign,
				'FILENAME1' => $file_re_data_sign,
			];

			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $this->exportPersonDataHeaderTemplate, $ZGLV, true);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($this->exportPersonDataFile, $xml_pers);

			foreach ( $registrytypes as $type ) {
				$rsp = $this->_loadRegistryDataForXmlByFunc($type, $data);

				if ( $rsp === false ) {
					throw new Exception('Ошибка при выгрузке реестра (_loadRegistryDataForXmlByFunc)');
				}
			}

			$this->textlog->add('Получили все данные из БД');
			$this->textlog->add('Количество записей реестра = ' . $this->_N_ZAP);

			$SCHET['VERSION'] = $data_file_version;
			$SCHET['DATA'] = date('Y-m-d');
			$SCHET['FILENAME'] = $file_re_data_sign;
			$SCHET['SD_Z'] = $this->_N_ZAP;

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $this->exportSluchDataHeaderTemplate, $SCHET, true, false);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($this->exportSluchDataFile, $xml);
			unset($xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($this->exportSluchDataFileTmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($this->exportSluchDataFileTmp, "rb");

				if ( $fh === false ) {
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($this->exportSluchDataFile, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($this->exportSluchDataFileTmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл');

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/' . $this->exportSluchDataFooterTemplate, [], true, false);
			file_put_contents($this->exportSluchDataFile, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/' . $this->exportPersonDataFooterTemplate, [], true);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('Создан ' . $this->exportSluchDataFile);
			$this->textlog->add('Создан ' . $this->exportPersonDataFile);

			$H_registryValidate = true;
			$L_registryValidate = true;

			if ( array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]) {
				$settingsFLK = $this->loadRegistryEntiesSettings($res[0]);

				if ( is_array($settingsFLK) && count($settingsFLK) > 0 ) {
					$upload_path = 'RgistryFields/';
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;

					if ( $tplEvnDataXSD ) {
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD);
						$dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT . $upload_path . $dirTpl . "/" . $tplEvnDataXSD;

						//Проверяем валидность 1го реестра
						//Путь до шаблона
						$H_xsd_tpl = $fileEvnDataXSD;
						//Файл с ошибками, если понадобится
						$H_validate_err_file = EXPORTPATH_REGISTRY . $out_dir . "/err_" . $file_re_data_sign . '.html';
						//Проверка
						$H_registryValidate = $this->Reconciliation($this->exportSluchDataFile, $H_xsd_tpl, 'file', $H_validate_err_file);
					}

					if ( $tplPersonDataXSD ) {
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD);
						$dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$tplPersonDataXSD = IMPORTPATH_ROOT . $upload_path . $dirTpl . "/" . $tplPersonDataXSD;

						//Проверяем 2й реестр
						//Путь до шаблона
						$L_xsd_tpl = $tplPersonDataXSD;
						//Файл с ошибками, если понадобится
						$L_validate_err_file = EXPORTPATH_REGISTRY . $out_dir . "/err_" . $file_re_pers_data_sign . '.html';
						//Проверка
						$L_registryValidate = $this->Reconciliation($this->exportPersonDataFile, $L_xsd_tpl, 'file', $L_validate_err_file);
					}
				}
			}

			$file_zip_sign = $zname;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$file_evn_num_name = EXPORTPATH_REGISTRY . $out_dir . "/evnnum.txt";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $this->exportSluchDataFile, $file_re_data_sign . ".xml" );
			$zip->AddFile( $this->exportPersonDataFile, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name);

			if ( !$H_registryValidate  && !$L_registryValidate ) {
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				throw new Exception(
					'Реестр не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>'
						. ' <a target="_blank" href="'.$this->exportSluchDataFile.'">H файл реестра</a>,'
						. ' <a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a>'
						. ' <a target="_blank" href="'.$this->exportPersonDataFile.'">L файл реестра</a>,'
						. ' <a href="'.$file_zip_name.'" target="_blank">zip</a>',
					20
				);
			}
			elseif ( !$H_registryValidate ) {
				//Скинули статус
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($this->exportPersonDataFile);
				$this->textlog->add('Почистили папку за собой');

				throw new Exception(
					'Файл H реестра не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>'
						. ' (<a target="_blank" href="'.$this->exportSluchDataFile.'">H файл реестра</a>),'
						. ' <a href="'.$file_zip_name.'" target="_blank">zip</a>',
					21
				);
			}
			elseif ( !$L_registryValidate ) {
				//Скинули статус
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($this->exportSluchDataFile);
				$this->textlog->add('Почистили папку за собой');

				throw new Exception(
					'Файл L реестра не прошёл ФЛК: <a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a>'
					. ' (<a target="_blank" href="'.$this->exportPersonDataFile.'">L файл реестра</a>),'
					. ' <a href="'.$file_zip_name.'" target="_blank">zip</a>',
					22
				);
			}
			else {
				unlink($this->exportSluchDataFile);
				unlink($this->exportPersonDataFile);
				$this->textlog->add('Почистили папку за собой');

				$this->_saveRegistryEvnNum([
					'FileName' => $file_evn_num_name,
				]);

				$data['Status'] = $file_zip_name;
				$data['Registry_EvnNum'] = json_encode($this->registryEvnNum);
				$this->SetXmlExportStatus($data);

				// Пишем информацию о выгрузке в историю
				$this->dumpRegistryInformation($data, 2);

				if (!empty($data['forSign'])) {
					$this->textlog->add('Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($file_zip_name));
					$response = [ 'success' => true, 'filebase64' => $filebase64 ];
				}
				else {
					$this->textlog->add('Вернули ссылку ' . $file_zip_name);
					$response = [ 'success' => true, 'Link' => $file_zip_name ];
				}
			}
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->SetXmlExportStatus($data);
			$this->textlog->add($e->getMessage());
			$response = [ 'success' => false, 'Error_Msg' => $e->getMessage() ];
		}

		return $response;
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

	/**
	 * Сохранение Registry_EvnNum
	 */
	protected function _saveRegistryEvnNum($data) {
		$toWrite = [];

		foreach ( $this->registryEvnNum as $key => $record ) {
			$toWrite[$key] = $record;

			if ( count($toWrite) >= 1000 ) {
				$str = json_encode($toWrite) . PHP_EOL;
				@file_put_contents($data['FileName'], $str, FILE_APPEND);
				$toWrite = [];
			}
		}

		if ( count($toWrite) > 0 ) {
			$str = json_encode($toWrite) . PHP_EOL;
			file_put_contents($data['FileName'], $str, FILE_APPEND);
		}

		return true;
	}

	/**
	 * Получение Registry_EvnNum
	 */
	public function setRegistryEvnNum($data) {
		if ( !empty($data['Registry_EvnNum']) ) {
			$this->registryEvnNum = json_decode($data['Registry_EvnNum'], true);
		}
		else if ( !empty($data['Registry_xmlExportPath']) ) {
			$filename = basename($data['Registry_xmlExportPath']);
			$evnNumPath = str_replace('/' . $filename, '/evnnum.txt', $data['Registry_xmlExportPath']);

			if ( file_exists($evnNumPath) ) {
				$fileContents = file_get_contents($evnNumPath);
				$exploded = explode(PHP_EOL, $fileContents);
				$this->registryEvnNum = [];

				foreach ( $exploded as $one ) {
					if ( !empty($one) ) {
						$unjsoned = json_decode($one, true);

						if ( is_array($unjsoned) ) {
							foreach ( $unjsoned as $key => $value ) {
								$this->registryEvnNum[$key] = $value;
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @description Получение данных о счете для выгрузки объединенного реестра в XML (новые реестры)
	 */
	protected function _loadRegistrySCHETForXmlUsing($data) {
		$postfix = '';

		if ( in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]) ) {
			$postfix = '_bud';
		}

		$header = $this->getFirstRowFromQuery(
			"select * from {$this->scheme}.p_Registry_expScet{$postfix}_f(:Registry_id) limit 1",
			[ 'Registry_id' => $data['Registry_id'] ]
		);

		if ( $header !== false && is_array($header) && count($header) > 0 ) {
			array_walk_recursive($header, 'ConvertFromUTF8ToWin1251', true);
		}

		return $header;
	}
	/**
	 * @param $type
	 * @param $data
	 * @return bool
	 * @description Получение и запись в файлы данных для выгрузки реестров в XML с помощью функций БД (новые реестры)
	 */
	protected function _loadRegistryDataForXmlByFunc($type, $data) {
		$isBudRegistry = in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]);
		$object = $this->_getRegistryObjectName($type);

		if ( $isBudRegistry ) {
			$fn_pers = $this->scheme . ".p_Registry_{$object}_expPac_bud_f";
			$fn_sl = $this->scheme . ".p_Registry_{$object}_expVizit_bud_f";
			$fn_usl = $this->scheme . ".p_Registry_{$object}_expUsl_bud_f";
			$fn_zsl = $this->scheme . ".p_Registry_{$object}_expSL_bud_f";

			if ( $type == 1 ) {
				$fn_ds2 = $this->scheme . ".p_Registry_{$object}_expDS2_bud_f";
				$fn_ds3 = $this->scheme . ".p_Registry_{$object}_expDS3_bud_f";
			}
		}
		else {
			$fn_pers = $this->scheme . ".p_Registry_{$object}_expPac_2018_f";
			$fn_sl = $this->scheme . ".p_Registry_{$object}_expVizit_2018_f";
			$fn_usl = $this->scheme . ".p_Registry_{$object}_expUsl_2018_f";
			$fn_zsl = $this->scheme . ".p_Registry_{$object}_expSL_2018_f";

			if (in_array($type, [ 1, 14 ])) {
				$fn_ds2 = $this->scheme . ".p_Registry_{$object}_expDS2_f";
				$fn_ds3 = $this->scheme . ".p_Registry_{$object}_expDS3_f";

				if ($type == 1) {
					$fn_kslp = $this->scheme . ".p_Registry_{$object}_expKSLP_f";
					$fn_shema = $this->scheme . ".p_Registry_{$object}_expShema_f";
				}
			} else if (in_array($type, [2, 7, 9, 11, 12])) {
				$fn_ds2 = $this->scheme . ".p_Registry_{$object}_expDS2_2018_f";
			}

			if (in_array($type, [1, 2, 14]) && $data['registryIsAfter20180901'] === true) {
				$fn_bdiag = $this->scheme . ".p_Registry_{$object}_expBDIAG_2018_f";
				$fn_bprot = $this->scheme . ".p_Registry_{$object}_expBPROT_2018_f";
				$fn_napr = $this->scheme . ".p_Registry_{$object}_expNAPR_2018_f";
				$fn_onkousl = $this->scheme . ".p_Registry_{$object}_expONKOUSL_2018_f";

				if ($type == 14 || $data['Registry_IsZNO'] == 2) {
					$fn_cons = $this->scheme . ".p_Registry_{$object}_expCONS_2018_f";
					$fn_lek_pr = $this->scheme . ".p_Registry_{$object}_expLEK_PR_2018_f";
				}

				if ($type == 1 && $data['registryIsAfter20190101'] === true) {
					$fn_crit = $this->scheme . ".p_Registry_{$object}_expCRIT_2018_f";
				}
			}

			if (in_array($type, [7, 9, 11, 12])) {
				$fn_naz = $this->scheme . ".p_Registry_{$object}_expNAZ_2018_f";
			}

			if (in_array($type, [6])) {
				$fn_regkr = $this->scheme . ".p_Registry_{$object}_expREGKR_f";
			}
		}

		$BDIAG = [];
		$BPROT = [];
		$CONS = [];
		$CRIT = [];
		$DS2 = [];
		$DS3 = [];
		$LEK_PR = [];
		$NAPR = [];
		$NAZ = [];
		$ONKOUSL = [];
		$REGKR = [];
		$SL_KOEF = [];
		$SHEMA = [];
		$USL = [];

		$KSG_KPG_FIELDS = [ 'N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL', 'SL_KOEF' ];
		$ONK_SL_FIELDS = [ 'DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA' ];
		$SANK_FIELDS = [ 'S_CODE', 'S_SUM', 'S_TIP', 'S_OSN', 'S_COM', 'S_IST', 'NACTMEK', 'DACTMEK' ];

		if ( $type != 14 && $data['Registry_IsZNO'] != 2 && $data['registryIsAfter20190101'] === false ) {
			$ONK_SL_FIELDS = array_merge($ONK_SL_FIELDS, [ 'PR_CONS1', 'PR_CONS2', 'DT_CONS1', 'DT_CONS2' ]);
		}

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
		$altKeys = [
			 'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'USL_USL' => 'USL'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
			,'P_OTK_USL' => 'P_OTK'
		];
		$netValue = toAnsi('НЕТ', true);
		$queryParams = [
			'Registry_id' => $data['Registry_id'],
		];

		// Диагностический блок (BDIAG)
		if ( !empty($fn_bdiag) ) {
			$query = "select * from {$fn_bdiag}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BDIAG[$row['Evn_sid']]) ) {
					$BDIAG[$row['Evn_sid']] = [];
				}

				$BDIAG[$row['Evn_sid']][] = $row;
			}
		}

		// Сведения об имеющихся противопоказаниях и отказах (BPROT)
		if ( !empty($fn_bprot) ) {
			$query = "select * from {$fn_bprot}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BPROT[$row['Evn_sid']]) ) {
					$BPROT[$row['Evn_sid']] = [];
				}

				$BPROT[$row['Evn_sid']][] = $row;
			}
		}

		// Сведения о проведении консилиума (CONS)
		if ( !empty($fn_cons) ) {
			$query = "select * from {$fn_cons}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CONS[$row['Evn_sid']]) ) {
					$CONS[$row['Evn_sid']] = [];
				}

				$CONS[$row['Evn_sid']][] = $row;
			}
		}

		// Классификационный критерий (CRIT)
		if ( !empty($fn_crit) ) {
			$query = "select * from {$fn_crit}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CRIT[$row['Evn_sid']]) ) {
					$CRIT[$row['Evn_sid']] = [];
				}

				$CRIT[$row['Evn_sid']][] = $row;
			}
		}

		// Диагнозы (DS2)
		if ( !empty($fn_ds2) ) {
			$query = "select * from {$fn_ds2}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS2[$row['Evn_sid']]) ) {
					$DS2[$row['Evn_sid']] = [];
				}

				$DS2[$row['Evn_sid']][] = $row;
			}
		}

		// Диагнозы (DS3)
		if ( !empty($fn_ds3) ) {
			$query = "select * from {$fn_ds3}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS3[$row['Evn_sid']]) ) {
					$DS3[$row['Evn_sid']] = [];
				}

				$DS3[$row['Evn_sid']][] = [ 'DS3' => $row['DS3'] ];
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($fn_kslp) ) {
			$query = "select * from {$fn_kslp}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($SL_KOEF[$row['Evn_sid']]) ) {
					$SL_KOEF[$row['Evn_sid']] = [];
				}

				$SL_KOEF[$row['Evn_sid']][] = [ 'IDSL' => $row['IDSL'], 'Z_SL' => $row['Z_SL'] ];
			}
		}

		// Сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($fn_lek_pr) ) {
			$query = "select * from {$fn_lek_pr}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($LEK_PR[$row['EvnUslugaLEK_id']]) ) {
					$LEK_PR[$row['EvnUslugaLEK_id']] = [];
				}

				$LEK_PR[$row['EvnUslugaLEK_id']][] = $row;
			}
		}

		// Направления (NAPR)
		if ( !empty($fn_napr) ) {
			$query = "select * from {$fn_napr}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAPR[$row['Evn_sid']]) ) {
					$NAPR[$row['Evn_sid']] = [];
				}

				$NAPR[$row['Evn_sid']][] = $row;
			}
		}

		// Назначения (NAZ)
		if ( !empty($fn_naz) ) {
			$query = "select * from {$fn_naz}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAZ[$row['Evn_sid']]) ) {
					$NAZ[$row['Evn_sid']] = [];
				}

				$NAZ[$row['Evn_sid']][] = $row;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if ( !empty($fn_onkousl) ) {
			$query = "select * from {$fn_onkousl}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( $type == 14 || $data['Registry_IsZNO'] == 2 ) {
					if ( !isset($ONKOUSL[$row['Evn_sid']]) ) {
						$ONKOUSL[$row['Evn_sid']] = [];
					}

					$row['LEK_PR_DATA'] = [];

					if ( isset($LEK_PR[$row['EvnUslugaLEK_id']]) && in_array($row['USL_TIP'], [ 2, 4 ]) ) {
						$LEK_PR_DATA = [];

						foreach ( $LEK_PR[$row['EvnUslugaLEK_id']] as $rowTmp ) {
							if ( !isset($LEK_PR_DATA[$rowTmp['REGNUM']]) ) {
								$LEK_PR_DATA[$rowTmp['REGNUM']] = [
									'REGNUM' => $rowTmp['REGNUM'],
									'CODE_SH' => (!empty($rowTmp['CODE_SH']) ? $rowTmp['CODE_SH'] : null),
									'DATE_INJ_DATA' => [],
								];
							}

							$LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ_DATA'][] = [ 'DATE_INJ' => $rowTmp['DATE_INJ'] ];
						}

						$row['LEK_PR_DATA'] = $LEK_PR_DATA;
						unset($LEK_PR[$row['EvnUslugaLEK_id']]);
					}

					$ONKOUSL[$row['Evn_sid']][] = $row;
				}
				else {
					$ONKOUSL[$row['EvnUsluga_id']] = $row;
				}
			}
		}

		// Региональный критерий (REG_KR)
		if ( !empty($fn_regkr) ) {
			$query = "select * from {$fn_regkr}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($REGKR[$row['Evn_sid']]) ) {
					$REGKR[$row['Evn_sid']] = [];
				}

				$REGKR[$row['Evn_sid']][] = $row;
			}
		}

		// Схемы (SHEMA)
		if ( !empty($fn_shema) ) {
			$query = "select * from {$fn_shema}(:Registry_id)";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');

			if ( !is_object($queryResult) ) {
				return false;
			}

			while ( $row = $queryResult->_fetch_assoc() ) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($SHEMA[$row['Evn_sid']]) ) {
					$SHEMA[$row['Evn_sid']] = [];
				}

				$SHEMA[$row['Evn_sid']][] = [ 'KOD_SHEMI' => $row['KOD_SHEMI'] ];
			}
		}

		// Услуги (USL)
		$query = "select * from {$fn_usl}(:Registry_id)";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$queryResult = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($queryResult) ) {
			return false;
		}

		// Массив $USL
		while ( $row = $queryResult->_fetch_assoc() ) {
			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

			$this->_IDSERV++;
			$row['IDSERV'] = $this->_IDSERV;

			if ( !isset($USL[$row['Evn_sid']]) ) {
				$USL[$row['Evn_sid']] = [];
			}

			if ( $type != 14 && $data['Registry_IsZNO'] != 2 ) {
				$row['NAPR_DATA'] = [];
				$row['ONK_USL_DATA'] = [];

				if ( isset($row['EvnUsluga_id']) && isset($ONKOUSL[$row['EvnUsluga_id']]) ) {
					$row['ONK_USL_DATA'] = [ $ONKOUSL[$row['EvnUsluga_id']] ];
					unset($ONKOUSL[$row['EvnUsluga_id']]);
				}
			}

			$USL[$row['Evn_sid']][] = $row;
		}

		// 2. джойним сразу посещения + пациенты и гребем постепенно то что получилось, сразу записывая в файл
		$result = $this->db->query("
			with zsl as (
				select * from {$fn_zsl} (:Registry_id)
			),
			sl as (
				select * from {$fn_sl} (:Registry_id)
			),
			pers as (
				select * from {$fn_pers} (:Registry_id)
			)
			select
				null as 'fields_part_1',
				z.*,
				z.Evn_id as Evn_zid,
				null as 'fields_part_2',
				s.*,
				null as 'fields_part_3',
				p.*
			from
				zsl z
				inner join sl s on s.Evn_id = z.Evn_id
				inner join pers p on p.Evn_id = z.Evn_id
			order by
				p.FAM, p.IM, p.OT, p.ID_PAC, s.Evn_id, s.Evn_sid
		", $queryParams, true);

		if ( !is_object($result) ) {
			return false;
		}

		$ZAP_ARRAY = [];
		$PACIENT_ARRAY = [];

		$recKeys = []; // ключи для данных

		$prevID_PAC = null;

		while ( $one_rec = $result->_fetch_assoc() ) {
			array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);

			if ( count($recKeys) == 0 ) {
				$recKeys = $this->_getKeysForRec($one_rec);

				if ( count($recKeys) < 3 ) {
					$this->textlog->add("Ошибка, неверное количество частей в запросе");
					return false;
				}
			}

			$zsl_key = $one_rec['Evn_zid'];
			$sl_key = $one_rec['Evn_sid'];

			$ZSL = array_intersect_key($one_rec, $recKeys[1]);
			$SL = array_intersect_key($one_rec, $recKeys[2]);
			$PACIENT = array_intersect_key($one_rec, $recKeys[3]);

			$SL['Evn_id'] = $zsl_key;
			$SL['Evn_sid'] = $sl_key;

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PACIENT['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				$xml = $this->parser->parse_ext(
					'export_xml/' . $this->exportSluchDataBodyTemplate,
					[ 'ZAP' => $ZAP_ARRAY ],
					true,
					false,
					$altKeys
				);

				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = [];
				unset($xml);

				// пишем в файл пациентов
				$xml_pers = $this->parser->parse_ext(
					'export_xml/' . $this->exportPersonDataBodyTemplate,
					[ 'PACIENT' => $PACIENT_ARRAY ],
					true,
					false,
					[]
				);

				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
				unset($PACIENT_ARRAY);
				$PACIENT_ARRAY = [];
				unset($xml_pers);
			}

			$prevID_PAC = $PACIENT['ID_PAC'];

			$SL['CONS_DATA'] = [];
			$SL['DS2_DATA'] = [];
			$SL['DS3_DATA'] = [];
			$SL['KSG_KPG'] = [];
			$SL['NAPR_DATA'] = [];
			$SL['NAZ'] = [];
			$SL['ONK_SL_DATA'] = [];
			$SL['REG_KR'] = [];
			$SL['SANK'] = [];
			$SL['SHEMA'] = [];
			$SL['USL'] = [];

			$KSG_KPG_DATA = [];
			$ONK_SL_DATA = [];
			$SANK_DATA = [];

			if ( isset($DS2[$sl_key]) ) {
				$SL['DS2_DATA'] = $DS2[$sl_key];
				unset($DS2[$sl_key]);
			}
			else if ( !empty($SL['DS2']) ) {
				$SL['DS2_DATA'][] = [
					'DS2' => $SL['DS2'],
					'DS2_PR' => (!empty($SL['DS2_PR']) ? $SL['DS2_PR'] : null),
					'PR_DS2_N' => (!empty($SL['PR_DS2_N']) ? $SL['PR_DS2_N'] : null)
				];
			}

			if ( array_key_exists('DS2', $SL) ) {
				unset($SL['DS2']);
			}

			if ( array_key_exists('DS2_PR', $SL) ) {
				unset($SL['DS2_PR']);
			}

			if ( array_key_exists('PR_DS2_N', $SL) ) {
				unset($SL['PR_DS2_N']);
			}

			if ( isset($DS3[$sl_key]) ) {
				$SL['DS3_DATA'] = $DS3[$sl_key];
				unset($DS3[$sl_key]);
			}
			else if ( !empty($SL['DS3']) ) {
				$SL['DS3_DATA'][] = [ 'DS3' => $SL['DS3'] ];
			}

			if ( array_key_exists('DS3', $SL) ) {
				unset($SL['DS3']);
			}

			if ( isset($SHEMA[$sl_key]) ) {
				$SL['SHEMA'] = $SHEMA[$sl_key];
				unset($SHEMA[$sl_key]);
			}

			if ( isset($NAZ[$sl_key]) ) {
				$SL['NAZ'] = $NAZ[$sl_key];
				unset($NAZ[$sl_key]);
			}

			$onkDS2 = false;

			if ( count($SL['DS2_DATA']) > 0 ) {
				foreach ( $SL['DS2_DATA'] as $ds2 ) {
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
				($type == 14 || $data['Registry_IsZNO'] == 2)
				&& (
					$SL['DS_ONK'] == 1
					|| (
						!empty($SL['DS1'])
						&& (
							substr($SL['DS1'], 0, 1) == 'C'
							|| ($data['registryIsAfter20190101'] === true && substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
							|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
						)
					)
				)
			) {
				if ( isset($CONS[$sl_key]) ) {
					$SL['CONS_DATA'] = $CONS[$sl_key];
					unset($CONS[$sl_key]);
				}

				if ( isset($NAPR[$sl_key]) ) {
					$SL['NAPR_DATA'] = $NAPR[$sl_key];
					unset($NAPR[$sl_key]);
				}
			}

			if ( isset($REGKR[$sl_key]) ) {
				foreach ( $REGKR[$sl_key] as $reg_kr_rec ) {
					if ( !empty($reg_kr_rec['KR_CODE']) ) {
						if ( !isset($SL['REG_KR'][0]) ) {
							$SL['REG_KR'][] = [ 'KR_CODE' => [] ];
						}

						$SL['REG_KR'][0]['KR_CODE'][] = [ 'KR_CODE_VAL' => $reg_kr_rec['KR_CODE'] ];
					}
				}

				unset($REGKR[$sl_key]);
			}

			if (in_array($type, [2]) && !empty($SL['KR_CODE'])){
				$SL['REG_KR'][0]['KR_CODE'][0] = [ 'KR_CODE_VAL' => $SL['KR_CODE']];
				unset($SL['KR_CODE']);
			}

			if (
				(empty($SL['DS_ONK']) || $SL['DS_ONK'] != 1)
				&& (empty($SL['REAB']) || $SL['REAB'] != '1')
				&& !empty($SL['DS1'])
				&& (
					substr($SL['DS1'], 0, 1) == 'C'
					|| ($data['registryIsAfter20190101'] === true && substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
					|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
				)
			) {
				$hasONKOSLData = false;
				$ONK_SL_DATA['B_DIAG_DATA'] = [];
				$ONK_SL_DATA['B_PROT_DATA'] = [];

				if ( $type == 14 || $data['Registry_IsZNO'] == 2 ) {
					$ONK_SL_DATA['ONK_USL_DATA'] = [];
				}
				else {
					$ONK_SL_DATA['PR_CONS_DATA'] = [];
					$ONK_SL_DATA['DT_CONS_DATA'] = [];
				}

				foreach ( $ONK_SL_FIELDS as $field ) {
					if ( isset($SL[$field]) ) {
						$hasONKOSLData = true;
						if (in_array($field, [ 'PR_CONS1', 'PR_CONS2' ])) {
							$ONK_SL_DATA['PR_CONS_DATA'][] = [
								'PR_CONS' => $SL[$field]
							];
						}
						else if (in_array($field, [ 'DT_CONS1', 'DT_CONS2' ])) {
							$ONK_SL_DATA['DT_CONS_DATA'][] = [
								'DT_CONS' => $SL[$field]
							];
						}
						else {
							$ONK_SL_DATA[$field] = $SL[$field];
						}
					}
					else {
						$ONK_SL_DATA[$field] = null;
					}

					if ( array_key_exists($field, $SL) ) {
						unset($SL[$field]);
					}
				}

				if ( isset($BDIAG[$sl_key]) ) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$sl_key];
					unset($BDIAG[$sl_key]);
				}

				if ( isset($BPROT[$sl_key]) ) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$sl_key];
					unset($BPROT[$sl_key]);
				}

				if ( ($type == 14 || $data['Registry_IsZNO'] == 2) && isset($ONKOUSL[$sl_key]) ) {
					$hasONKOSLData = true;
					$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
					unset($ONKOUSL[$sl_key]);
				}

				if ( $hasONKOSLData == false ) {
					$ONK_SL_DATA = [];
				}
			}

			if ( count($ONK_SL_DATA) > 0 ) {
				$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
			}

			foreach ( $KSG_KPG_FIELDS as $field ) {
				if (array_key_exists($field, $SL)) {
					if (isset($SL[$field])) {
						if (in_array($field, [ 'DKK2' ])) {
							if (!isset($KSG_KPG_DATA['DKK2_DATA'])) {
								$KSG_KPG_DATA['DKK2_DATA'] = [];
							}

							$DKK2Array = explode(',', $SL[$field]);
							foreach ($DKK2Array as $oneDKK2) {
								if (!empty($oneDKK2)) {
									$KSG_KPG_DATA['DKK2_DATA'][] = [ 'DKK2_VAL' => $oneDKK2 ];
								}
							}
						} else {
							$KSG_KPG_DATA[$field] = $SL[$field];
						}
					}
				}

				unset($SL[$field]);
			}

			if ( count($KSG_KPG_DATA) > 0 ) {
				foreach ($KSG_KPG_FIELDS as $field) {
					if ( !in_array($field, [ 'DKK2' ]) && !isset($KSG_KPG_DATA[$field]) ) {
						$KSG_KPG_DATA[$field] = null; // заполняем недостающие поля null
					}
				}

				$KSG_KPG_DATA['CRIT_DATA'] = [];
				$KSG_KPG_DATA['SL_KOEF'] = [];

				if ( !isset($KSG_KPG_DATA['DKK2_DATA']) ) {
					$KSG_KPG_DATA['DKK2_DATA'] = [];
				}

				if ( isset($CRIT[$sl_key]) ) {
					$KSG_KPG_DATA['CRIT_DATA'] = $CRIT[$sl_key];
					unset($CRIT[$sl_key]);
				}

				if ( isset($SL_KOEF[$sl_key]) ) {
					$KSG_KPG_DATA['SL_KOEF'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}
				else if ( !empty($SL['IDSL']) && !empty($SL['Z_SL']) ) {
					$KSG_KPG_DATA['SL_KOEF'][] = [ 'IDSL' => $SL['IDSL'], 'Z_SL' => $SL['Z_SL'] ];
				}

				$SL['KSG_KPG'][] = $KSG_KPG_DATA;
			}

			foreach ( $SANK_FIELDS as $field ){
				if ( !empty($SL[$field]) ) {
					$SANK_DATA[$field] = $SL[$field];
				}

				unset($SL[$field]);
			}

			if ( count($SANK_DATA) > 0 ) {
				$SL['SANK'][] = $SANK_DATA;
			}

			if ( isset($USL[$sl_key]) ) {
				$SL['USL'] = $USL[$sl_key];
				unset($USL[$sl_key]);
			}

			if ( $type != 14 && $data['Registry_IsZNO'] != 2 && count($SL['USL']) > 0 ) {
				if ( !empty($SL['DS_ONK']) && $SL['DS_ONK'] == 1 && isset($NAPR[$sl_key]) ) {
					foreach ( $NAPR[$sl_key] as $oneNAPR ) {
						switch ( true ) {
							case (intval($oneNAPR['NAPR_V']) == 3 && in_array($type, [ 1, 2 ])):
								$naprByUslCode = false;

								foreach ( $SL['USL'] as $uslIndex => $usluga ) {
									if ( $usluga['CODE_USL'] == $oneNAPR['NAPR_USL'] ) {
										$SL['USL'][$uslIndex]['NAPR_DATA'][] = $oneNAPR;
										$naprByUslCode = true;
										break;
									}
								}

								if ( $naprByUslCode === false ) {
									$SL['USL'][count($SL['USL']) - 1]['NAPR_DATA'][] = $oneNAPR;
								}
								break;

							default:
								$SL['USL'][count($SL['USL']) - 1]['NAPR_DATA'][] = $oneNAPR;
								break;
						}
					}

					unset($NAPR[$sl_key]);
				}

				if ( count($SL['ONK_SL_DATA']) == 0 ) {
					foreach ( $SL['USL'] as $uslIndex => $usluga ) {
						$SL['USL'][$uslIndex]['ONK_USL_DATA'] = [];
					}
				}
			}


			if ( isset($ZAP_ARRAY[$zsl_key]) ) {
				// если уже есть законченный случай, значит добавляем в него SL
				$ZAP_ARRAY[$zsl_key]['SL'][$sl_key] = $SL;
			}
			else {
				// иначе создаём новый ZAP
				$this->_N_ZAP++;

				$ZSL['SL'] = [];

				$OS_SLUCH = [];

				if ( !empty($PACIENT['OS_SLUCH']) ) {
					$OS_SLUCH[] = ['OS_SLUCH_VAL' => $PACIENT['OS_SLUCH']];
				}

				if ( !empty($PACIENT['OS_SLUCH1']) ) {
					$OS_SLUCH[] = ['OS_SLUCH_VAL' => $PACIENT['OS_SLUCH1']];
				}

				if ( array_key_exists('OS_SLUCH', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH']);
				}

				if ( array_key_exists('OS_SLUCH1', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH1']);
				}

				$ZSL['OS_SLUCH'] = $OS_SLUCH;

				$PACIENT['DOST'] = [];
				$PACIENT['DOST_P'] = [];

				if ( !in_array($data['PayType_SysNick'], [ 'bud', 'fbud', 'subrf' ]) ) {
					if (empty($PACIENT['FAM'])) {
						$PACIENT['DOST'][] = [ 'DOST_VAL' => 2 ];
					}

					if (empty($PACIENT['IM'])) {
						$PACIENT['DOST'][] = [ 'DOST_VAL' => 3 ];
					}

					if (empty($PACIENT['OT']) || strtoupper($PACIENT['OT']) == $netValue) {
						$PACIENT['DOST'][] = [ 'DOST_VAL' => 1 ];
					}

					if ($PACIENT['NOVOR'] != '0') {
						if (empty($PACIENT['FAM_P'])) {
							$PACIENT['DOST_P'][] = [ 'DOST_P_VAL' => 2 ];
						}

						if (empty($PACIENT['IM_P'])) {
							$PACIENT['DOST_P'][] = [ 'DOST_P_VAL' => 3 ];
						}

						if (empty($PACIENT['OT_P']) || strtoupper($PACIENT['OT_P']) == $netValue) {
							$PACIENT['DOST_P'][] = [ 'DOST_P_VAL' => 1 ];
						}
					}
				}

				$ZSL['N_ZAP'] = $this->_N_ZAP;
				$ZSL['IDCASE'] = $this->_N_ZAP;
				$ZSL['PR_NOV'] = (isset($ZSL['PR_NOV']) ? $ZSL['PR_NOV'] : null);
				$ZSL['PACIENT'] = [ $PACIENT ];
				$ZSL['SL'][$sl_key] = $SL;

				if ( !isset($PACIENT_ARRAY[$PACIENT['ID_PAC']]) ) {
					$PACIENT_ARRAY[$PACIENT['ID_PAC']] = $PACIENT;
				}

				$ZAP_ARRAY[$zsl_key] = $ZSL;
			}

			if ( !isset($this->registryEvnNum[$SL['SL_ID']]) ) {
				$this->registryEvnNum[$SL['SL_ID']] = [];
			}

			$this->registryEvnNum[$SL['SL_ID']][] = [
				'Evn_id' => $zsl_key,
				'Evn_sid' => $sl_key,
				'N_ZAP' => $this->_N_ZAP,
			];
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			// пишем в файл случаи
			$xml = $this->parser->parse_ext(
				'export_xml/' . $this->exportSluchDataBodyTemplate,
				[ 'ZAP' => $ZAP_ARRAY ],
				true,
				false,
				$altKeys
			);

			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($this->exportSluchDataFileTmp, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);

			// пишем в файл пациентов
			$xml_pers = $this->parser->parse_ext(
				'export_xml/' . $this->exportPersonDataBodyTemplate,
				[ 'PACIENT' => $PACIENT_ARRAY ],
				true,
				false,
				[]
			);

			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($this->exportPersonDataFile, $xml_pers, FILE_APPEND);
			unset($PACIENT_ARRAY);
			unset($xml_pers);
		}

		return true;
	}
}