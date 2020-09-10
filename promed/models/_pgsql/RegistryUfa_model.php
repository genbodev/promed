<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry_model - модель для работы с таблицей Registry
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin`
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require("Registry_model.php");

class RegistryUfa_model extends Registry_model {
	public $scheme = "r2";
	public $region = "ufa";
	public $MaxEvnField = "Evn_id";

	//Task#18694 Шаблоны для ФЛК У меня идёт проверка валидности введённой информации в поле с помощью javascript. XML реестров
	//Перенес в модель
	protected $H_xsd = '/documents/xsd/OMS-D1.xsd';
	protected $L_xsd = '/documents/xsd/OMS-D2.xsd';

	protected $persCnt = 0;
	protected $zapCnt = 0;

	private $_registryTypeList = array(
		1 => array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар', 'SP_Object' => 'EvnPS'),
		2 => array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника', 'SP_Object' => 'EvnPL'),
		6 => array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь', 'SP_Object' => 'SMP'),
		7 => array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года', 'SP_Object' => 'EvnPLDD13'),
		9 => array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года', 'SP_Object' => 'EvnPLOrp13'),
		14 => array('RegistryType_id' => 14, 'RegistryType_Name' => 'Высокотехнологичная медицинская помощь', 'SP_Object' => 'EvnHTM'),
		15 => array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги', 'SP_Object' => 'EvnUslugaPar'),
		17 => array('RegistryType_id' => 17, 'RegistryType_Name' => 'Профилактические медицинские осмотры', 'SP_Object' => 'EvnPLProf'),
	);

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
	 * Подписание реестра
	 */
	function signRegistry($data)
	{
		// 1. получаем файл
		$query = "
			SELECT
				R.Registry_xmlExportPath as \"Registry_xmlExportPath\"
			FROM
				{$this->scheme}.Registry R 
			WHERE
				Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id'=>$data['Registry_id']));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_xmlExportPath'])) {
				$data['Registry_xmlExportPath'] = $resp[0]['Registry_xmlExportPath'];
			}
		}
		if (empty($data['Registry_xmlExportPath']) || !file_exists($data['Registry_xmlExportPath'])) {
			return array('Error_Msg' => 'XML-файл реестра не найден');
		}
		$file_zip_sign = basename($data['Registry_xmlExportPath']);
		// 2. запихиваем его в зип вместе с подписью
		$out_dir = "re_xmlsigned_".time()."_".$data['Registry_id'];
		mkdir( EXPORTPATH_REGISTRY.$out_dir );
		$file_sign_name = EXPORTPATH_REGISTRY.$out_dir."/sign.txt";
		file_put_contents($file_sign_name, $data['documentSigned']);

		$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . "_signed.zip";

		$zip = new ZipArchive;
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile($data['Registry_xmlExportPath'], $file_zip_sign);
		$zip->AddFile($file_sign_name, "sign.txt");
		$zip->close();

		// 4. отдаём на клиент true =)
		return array('Error_Msg' => '', 'Link' => $file_zip_name);
	}
	
	/**
	 *  Task# Групповое исключение записей из реестров (с учётом номеров талонов)
	 *  Помечаем запись реестра на удаление 
	 *  
	 *  @data['Evn_ids'] - (array) Evn_id записей реестра
	 *  @data['RegistryData_deleted'] (int) 1|2 - состояние записи
	 *  @data['Registry_id'] - (int) id реестра
	 *  @data['RegistryType_id'] - (int) тип реестра
	 *  @data['Filter'] - (string) JSON строка с настройками фильтра
	 */
	function deleteRegistryGroupData($data)
	{   
		$Evn_ids = null;

		$RegistyryType_id = $data['RegistryType_id'];
		$Registyry_id = $data['Registry_id'];
		
		//Собирнаем список Evn_id в нужном формате    
		if($data['Evn_ids'] != null){
			//Запятая вконце нужна для хитрого SQL
			$data['Evn_ids'] = ','.trim($data['Evn_ids'],"[]"); 
			$t = explode(',', $data['Evn_ids']);
			$Evn_ids = '-'.ltrim(implode(',-',$t).',', '-,'); 
		}
  
  
  
		if($data['Type_select'] == 0){
			$Filter = null;

			if($data['Evn_ids'] != null){
				
				//Запятая в конце нужна для хитрого SQL
				//Аналог explode()
				//set @where = '(''-''+charindex(cast('+@id+' as varchar)+'','', '''+@Evn_ids+''')) > 0 '
				//set @sql = N'SELECT NumCard as EvnPL_NumCard from '+@table+' WHERE '+@where+' and Registry_id ='+cast(@Registry_id as varchar(50));
				$data['Evn_ids'] = ','.trim($data['Evn_ids'],"[]"); 
				$t = explode(',', $data['Evn_ids']);
				$Evn_ids = '-'.ltrim(implode(',-',$t).',', '-,'); 
			
               
            }
		}
		elseif($data['Type_select'] == 1){

			$Filter = json_decode(toUTF($data['Filter']), 1);

			if( $data['Filter'] == '{}' || $data['Filter'] == 'false' || !is_array($Filter) ) {
				return array(array('success'=>false,'Error_Msg'=>'Ошибка: Попытка исключения записей реестра при пустом фильтре!'));
			}
			
			$t = ' ';
			
			$table = !in_array($data['RegistryType_id'], array(6)) ? 'r2.RegistryData' : 'r2.RegistryDataCmp';
			
			  
			foreach($Filter as $c=>$v){
				$inner = array();

				if(is_array($v)){
				
					foreach($v as $k=>$d){
						$inner[] = "'".trim($d)."'";
					}
					
					//Персональный кусок для каждой модели
					if($c == 'Diag_Code')
						$c = 'D.'.$c;
					elseif($c == 'EvnPL_NumCard')
						$c = 'RD.NumCard'; 
					elseif($c == 'LpuSection_name')
						$c = 'RD.'.$c;
					elseif($c == 'LpuBuilding_Name')
						$c = 'LB.'.$c;    
					elseif($c == 'Usluga_Code')
						$c = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';

					if ( count($inner) > 0 ) {
						$t .= $c." in (".implode(",", $inner).") and ";
					}
				}
				
				if ( count($inner) == 0 ) {
					return array(array('success'=>false,'Error_Msg'=>'Ошибка: Попытка исключения записей реестра при пустом фильтре!'));
				}
			}
			$Filter = toAnsi(preg_replace("#and$#isU", "", trim($t)));
		}        

		if($data['Type_select'] == 1){
			//Работаем по фильтру
			$Evn_ids = null;
		}
		elseif($data['Type_select'] == 0){
			//Работаем по списку Evn_id 
			$Filter = null; 
		}        
		
		//Персональная проццедурка для каждой модели
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"            
			from {$this->scheme}.p_RegistryGroupDataUfa_del(
				Filter := :Filter,
				Evn_ids := :Evn_ids,
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				RegistryData_deleted := :RegistryData_deleted);
		";
		
		//@response = @response,
		
		$params = array(
		 'Evn_ids'=>$Evn_ids,
		 'Filter'=>$Filter,
		 'Registry_id'=>$data['Registry_id'],
		 'RegistryType_id'=>$data['RegistryType_id'],
		 'RegistryData_deleted'=>($data['RegistryData_deleted'] == 1) ? 2 : 1
		);

		//var_dump($params);

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
	* comment
	*/ 
	function loadRegData($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id'], 'Registry_id' => $data['Registry_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';
		$filter .= ' and R.Registry_id = :Registry_id';
		
		$query = "
			select R.Registry_id as \"Registry_id\",
                   R.RegistryType_id as \"RegistryType_id\",
                   R.Registry_Num as \"Registry_Num\",
                   Lpu.Lpu_Email as \"Lpu_Email\",
                   Lpu.Lpu_Nick as \"Lpu_Nick\"
            from v_Registry R
                 left join v_Lpu Lpu on Lpu.Lpu_id = R.Lpu_id
            where {$filter}
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
			select count(Registry_id) as \"cnt\"
            from {$this->scheme}.v_Registry
            where Registry_id =:Registry_id and
                  (OrgSmo_id is null or
                  LpuUnitSet_id is null)
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		if ( is_object($result) ) {
			$sel = $result->result('array');
			if ( is_array($sel) && count($sel) > 0 && $sel[0]['cnt'] > 0 )
				$load_errors_only = false;
		}
		else {
			return false;
		}
		
		$this->setRegistryParamsByType($data);
		$errors_join = "";
		if ( $load_errors_only === true )
			$errors_join = " inner join {$this->scheme}.v_{$this->RegistryErrorObject} re  on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";

		
		$query = "
			select rd.Evn_id as \"ID_POS\",
                   rd.Person_id as \"ID\",
                   to_char(ev.EvnVizit_setDate, 'YYYYMMDD') as \"DATE_POS\",
                   ''       as \"SMO\",
                   ''       as \"POL_NUM\",
                   ''       as \"ID_STATUS\",
                   ''       as \"NAM\",
                   ''       as \"FNAM\",
                   ''       as \"SEX\",
                   ''       as \"SNILS\",
                   null as \"DATE_BORN\",
                   null as \"DATE_SV\",
                   null as \"FLAG\"
            from {$this->scheme}.v_{$this->RegistryDataObject} rd 
                 ". $errors_join ."
                 inner join v_EvnVizit ev on ev.EvnVizit_id = rd.Evn_id
            where rd.Registry_id =:Registry_id
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
				count(Registry_id) as \"cnt\"
			from
				{$this->scheme}.v_Registry 
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
		
        $this->setRegistryParamsByType($data);
		$errors_join = "";
		if ( $load_errors_only === true )
			$errors_join = " inner join {$this->scheme}.v_{$this->RegistryErrorObject} re  on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";

		
		$query = "
			select
				rd.Evn_id as \"ID_POS\",
				rd.Person_id as \"ID\",
				to_char(es.EvnSection_setDT, 'YYYYMMDD') as \"DATE_POS\",
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
				{$this->scheme}.v_{$this->RegistryDataObject} rd 
				". $errors_join ."
				inner join v_EvnSection es  on es.EvnSection_id = rd.Evn_id
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
				count(Registry_id) as \"cnt\"
			from
				{$this->scheme}.v_Registry 
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
		
        $this->setRegistryParamsByType($data);
		$errors_join = "";
		if ( $load_errors_only === true )
			$errors_join = " inner join {$this->scheme}.v_{$this->RegistryErrorObject} re  on re.Registry_id = rd.Registry_id and re.Evn_id = rd.Evn_id and re.RegistryErrorType_id = 44 ";

		
		$query = "
			select distinct rd.Person_id as \"ID\",
                   dbo.UcWord(rd.Person_SurName) as \"FAM\",
                   dbo.UcWord(rd.Person_FirName) as \"NAM\",
                   dbo.UcWord(rd.Person_SecName) as \"FNAM\",
                   to_char(rd.Person_BirthDay, 'YYYYMMDD') as \"DATE_BORN\",
                   COALESCE(Sex.Sex_Code, 0) as \"SEX\",
                   rd.DocumentType_Code as \"DOC_TYPE\",
                   rtrim(COALESCE(rd.Document_Ser, '')) as \"DOC_SER\",
                   rtrim(COALESCE(rd.Document_Num, '')) as \"DOC_NUM\",
                   rtrim(COALESCE(ps.Person_Inn, '')) as \"INN\",
                   coalesce(addr.KLStreet_Code, addr.KLTown_Code, addr.KLCity_Code, addr.KLSubRGN_Code, addr.KLRGN_Code) as \"KLADR\",
                   rtrim(COALESCE(addr.Address_House, '')) as \"HOUSE\",
                   rtrim(COALESCE(addr.Address_Flat, '')) as \"ROOM\",
                   smoorg.Org_Code as \"SMO\",
                   rtrim(COALESCE(rd.Polis_Num, '')) as \"POL_NUM\",
                   case
                     when rd.SocStatus_Code = 1 then 4
                     when rd.SocStatus_Code = 2 then 7
                     when rd.SocStatus_Code = 3 then 1
                     when rd.SocStatus_Code = 4 then 3
                     when rd.SocStatus_Code = 5 then 5
                     else 0
                   end as \"STATUS\"
            from {$this->scheme}.v_{$this->RegistryDataObject} rd 
                 ". $errors_join ."
                 inner join v_PersonState ps on ps.Person_id = rd.Person_id
                 left join Sex on Sex.Sex_id = rd.Sex_id
                 left join v_Address_all addr on addr.Address_id = rd.Address_id
                 left join OrgSmo osmo on osmo.OrgSmo_id = rd.OrgSmo_id
                 left join Org smoorg on smoorg.Org_id = osmo.Org_id
            where rd.Registry_id =:Registry_id
		";
		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
		
		$query = "
			select
				rtrim(COALESCE(LpuUnitSet_Code, '')) as \"LpuUnitSet_Code\"
			from
				{$this->scheme}.v_Registry 
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
				rtrim(COALESCE(Org_Code, '')) as Lpu_Code

			from
				v_Lpu 

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
	function loadRegistryDataForXmlCheckVolumeUsing($type, $data)
	{
		$query = "
			SELECT {$this->scheme}.p_RegistryPL_expRE( Registry_id := ?)
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
			select lpu_f003mcod as \"lpu_f003mcod\"
            from Lpu
            where Lpu_id = ?
            limit 1
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
			select os.Org_Code as \"ID_SMO\",
                   R.LpuUnitSet_Code as \"ID_SUBLPU\",
                   --rt.RegistryType_Code as \"ID_SUBLPU\", -- тип
                   1 as \"TYPE\",
                   date_part('YEAR',R.Registry_endDate) as \"YEAR\",
                   date_part('MONTH',R.Registry_endDate) as \"MONTH\",
                   RTrim(COALESCE(to_char(cast (R.Registry_begDate as timestamp), 'DD.MM.YYYY'), '')) as \"DATE_BEG\",
                   RTrim(COALESCE(to_char(cast (R.Registry_endDate as timestamp), 'DD.MM.YYYY'), '')) as \"DATE_END\"
            from 
                 {$this->scheme}.v_Registry R
                 left join RegistryType rt on rt.RegistryType_id = R.RegistryType_id
                 inner join v_Lpu Lpu on Lpu.Lpu_id = R.Lpu_id
                 left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id
                 left join Org os on os.Org_id = OrgSmo.Org_id
                 --left join LpuUnitSet  on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id
            where {$filter}
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
	 *	Получение данных для выгрузки реестров в DBF
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
				$query = "SELECT {$this->scheme}.p_Registry_EvnPS_Exp (Registry_id := :Registry_id)";
				break;

			case 14: //htm
				$query = "SELECT {$this->scheme}.p_Registry_EvnHTM_Exp (Registry_id := :Registry_id)";
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
	 *  #Task# по плану 1.11 Установка отчётного месяца/года + номера пачки реестра
	 */ 	
	
	function updateRegistryTwoCols($data){
		/**
		echo $data['Registry_id'].'<br/>';
		echo $data['Registry_orderDate'].'<br/>';
		echo $data['Registry_pack'].'<br/>';
		*/
		$params = array(
			'Registry_id'=>$data['Registry_id'],
			'Registry_orderDate'=>$data['Registry_orderDate'],
			'Registry_pack'=>$data['Registry_pack']
		);
	   
		$query = "
			Update r2.Registry set Registry_orderDate = :Registry_orderDate, Registry_pack = :Registry_pack where Registry_id = :Registry_id
		";

		$response = $this->db->query($query, $params);

		if ( !$response ) {
		
			return false;
		}
		return true;     
	}
	/**
	 * #Task# по плану 1.11 Установка отчётного месяца/года + номера пачки реестра + влияние на имя файла реестра (по номеру пачки))
	 */     
	function getRegistryTwoCols($data){
		$params = array(
			'Registry_id'=>$data['Registry_id'],
			'Registry_orderDate'=>null,
			'Registry_pack'=>null
		);

		$query = "
			select case
                     when Registry_pack is not null then \"Registry_pack\"
                     when RegistryType_id in (1, 2, 6, 17) then 1
                     when RegistryType_id in (7, 14) then 9
                     when RegistryType_id in (9) then 8
                     else 9
                   end as \"Registry_pack\",
                   Registry_orderDate as \"Registry_orderDate\"
            from 
                {$this->scheme}.Registry
            where Registry_id =:Registry_id
		";

		$response = $this->db->query($query, $params);

		if ( is_object($response) ) {
			return $response->result('array');
		}
		else {
			return false;
		}     
	}
    
	/**
	 * #Task# по плану 1.11 добавление сведений в вкладку 0 реестра: кол-во койко-мест, сумма принятая, сумма не принятая
	 */     
	function getMoreInfoRegistry($data){
		if(!isset($data['Registry_id'])){
			return false;
		}
		
		$params = array(
			'Registry_id'=>$data['Registry_id']
		);
        

		$this->setRegistryParamsByType($data);
		
		$table =  'v_' . $this->RegistryDataObject;
		$field = ($data['RegistryType_id'] == 6) ? 'RegistryDataCmp_Sum_R' : 'RegistryData_Sum_R';

        $query = "
              Select
                     --r.Registry_SumR as sum_all,
                     SUM(case
                           when COALESCE(RD.RegistryData_IsBadVol, 1) = 2 then 0
                           else RD.RegistryData_Sum_R
                         end) as \"sum_all\",
                     -- sum(case when RETF.Evn_id is not null then RD.RegistryData_Sum_R else null end) as sum_err,
                     sum(case
                           when COALESCE(rd.Paid_id, 2) = 1 or COALESCE(RD.RegistryData_IsBadVol, 1) = 2 then RD.RegistryData_Sum_R
                           else 0
                         end) as \"sum_err\",
                     --COUNT(distinct RD.Evn_rid) as count_numcard,
                     --COUNT(distinct RD.Person_id) as count_person,
                     COALESCE(N_C.count_numcard, 0) as \"count_numcard\",
                     COALESCE(P_C.count_person, 0) as \"count_person\",
                     COUNT(distinct RETF.Evn_id) as \"tf_err_evn_count\",
                     rs.Registry_State as \"Registry_State\",
                     -- по просьбе суппорта 21/02/2014
                     --sum(case when RETF.Evn_id is null or RETF.RegistryErrorClass_id != 1 then RD.RegistryData_KdFact else 0 end) as Registry_kd_good,
                     --sum(case when RETF.Evn_id is not null and RETF.RegistryErrorClass_id = 1 then RD.RegistryData_KdFact else 0 end) as Registry_kd_err,
                     --  -- по просьбе https://redmine.swan.perm.ru/issues/77920
                     sum(case
                           when COALESCE(rd.Paid_id, 2) = 2 and COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then RD.RegistryData_KdFact
                           else 0
                         end) as \"Registry_kd_good\",
                     sum(case
                           when COALESCE(rd.Paid_id, 2) = 1 or COALESCE(RD.RegistryData_IsBadVol, 1) = 2 then RD.RegistryData_KdFact
                           else 0
                         end) as \"Registry_kd_err\",
                     r.RegistryType_id as \"RegistryType_id\",
                     -- end
                     COALESCE(UET.uet, 0) as \"uet\"
              from r2.Registry r
                   left join r2.v_{$this->RegistryDataObject} RD on RD.Registry_id = r.Registry_id
                   left join r2.v_RegistryErrorTFOMS RETF on (RETF.Registry_id = r.Registry_id and RETF.Evn_id = RD.Evn_id and COALESCE(RD.RegistryData_IsBadVol, 1) = 1)
                   LEFT JOIN LATERAL
                   (
                     Select Registry_State
                     from r2.RegistryStateLog RS
                     where RS.Registry_id = r.Registry_id
                     limit 1
                   ) rs ON true
                   LEFT JOIN LATERAL
                   (
                     select sum(RD.EvnVizit_UetOMS) as Uet
                     from r2.v_RegistryData RD
                     where RD.Registry_id =:Registry_id and
                           ((RD.LpuSectionProfile_Code in ('526', '527', '528', '529', '530', '559', '560', '561', '562', '626', '627', '628', '629', '630', '659', '660', '661', '662', '826', '827', '828', '829', '830', '859', '860', '861', '862') or
                           RD.EvnClass_id in ('6', '16')) or
                           RD.LpuSectionProfile_Code in ('577', '677', '877')) and
                           COALESCE(RD.RegistryData_IsBadVol, 1) = 1
                   ) UET ON true
                   LEFT JOIN LATERAL
                   (
                     select COUNT(distinct RD.Person_id) as count_person
                     from r2.v_{$this->RegistryDataObject} RD
                     where RD.Registry_id =:Registry_id and
                           COALESCE(RD.RegistryData_IsBadVol, 1) = 1
                   ) P_C ON true 
                   LEFT JOIN LATERAL
                   (
                     select COUNT(distinct RD.Evn_rid) as count_numcard
                     from r2.v_{$this->RegistryDataObject} RD
                     where RD.Registry_id =:Registry_id and
                           COALESCE(RD.RegistryData_IsBadVol, 1) = 1
                   ) N_C ON true
              where r.Registry_id =:Registry_id
              group by r.Registry_SumR,
                       RS.Registry_State,
                       r.RegistryType_id,
                       UET.Uet,
                       P_C.count_person,
                       N_C.count_numcard
        ";

        //echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
            $t = array();
           

			$x =   $result->result('array');
            
            if(empty($x[0])){
                 $x[0] = array('sum_all'=>0,'sum_err'=>0,'count_numcard'=>0, 'kd_err'=>0, 'kd_good'=>0, 'uet'=>0, 'tf_err_evn_count'=>0, 'count_person'=>0);
            }
            $x[0]['Registry_kd_good'] = isset($x[0]['Registry_kd_good']) ? $x[0]['Registry_kd_good'] : 0;
            $x[0]['Registry_kd_err'] = isset($x[0]['Registry_kd_err']) ? $x[0]['Registry_kd_err'] : 0;
            $x[0]['RegistryType_id'] = isset($x[0]['RegistryType_id']) ? $x[0]['RegistryType_id'] : 1;
             
            $t[0]['sum'] =  (float)$x[0]['sum_all'];//round((float)$x[0]['sum_all'] - (float)$x[0]['sum_err'], 2); //Сумма принята
            $t[1]['sum'] =  (float)$x[0]['sum_err'];
            $t[2]['sum'] =  $x[0]['count_numcard'];
            $t[3]['sum'] =  (float)$x[0]['sum_all'] + (float)$x[0]['sum_err']; //Общая сумма
            $t[4]['sum'] =  empty($x[0]['Registry_State']) ? '' : $x[0]['Registry_State'];
            #По просьбе суппорта
            $t[5]['sum'] =  is_null($x[0]['Registry_kd_good']) ? 0 : $x[0]['Registry_kd_good'];
            $t[6]['sum'] =  is_null($x[0]['Registry_kd_err']) ? 0 : $x[0]['Registry_kd_err']; 
            $t[7]['sum'] = $x[0]['RegistryType_id'];
            $t[8]['uet'] = $x[0]['uet'];

            $t[9]['sum'] = $x[0]['tf_err_evn_count'];
            $t[10]['sum'] = $x[0]['count_person'];

			if ( $t[0]['sum'] < 0.01 ) {
				$t[0]['sum'] = 0;
			}

            return $t;
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
		#Task# по плану 1.11 Установка отчётного месяца/года + номера пачки реестра
		#Добавлени вьюха r2.v_RegistryUfa
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

			$this->setRegistryParamsByType($data);
		}
		if (isset($data['RegistrySubType_id']))
		{
			$filter .= ' and COALESCE(R.RegistrySubType_id, 1) = :RegistrySubType_id';

			$params['RegistrySubType_id'] = $data['RegistrySubType_id'];
		} else {
			$filter .= ' and COALESCE(R.RegistrySubType_id, 1) = 1';

		}
		if ( isset($data['Registry_IsNew']) && !isset($data['Registry_id']) ) {
			$filter .= ' and COALESCE(R.Registry_IsNew, 1) = :Registry_IsNew';

			$params['Registry_IsNew'] = $data['Registry_IsNew'];
		}
		if (isset($data['RegistryStatus_id']))
		{
			// только если оплаченные!!!
			if( 4 == (int)$data['RegistryStatus_id'] ) {
				if( $data['Registry_accYear'] > 0 ) {
					//$filter .= ' and convert(varchar(4),cast(R.Registry_begDate as date),112) <= :Registry_accYear';
					//$filter .= ' and convert(varchar(4),cast(R.Registry_endDate as date),112) >= :Registry_accYear';
					$filter .= ' and to_char(cast(R.Registry_accDate as date),\'YYYY\') = :Registry_accYear';
					$params['Registry_accYear'] = $data['Registry_accYear'];
				}
			}
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

		if ($loadQueue)
		{
			$query = "
			Select R.RegistryQueue_id as \"Registry_id\",
                   R.OrgSmo_id as \"OrgSmo_id\",
                   R.Lpu_oid as \"Lpu_oid\",
                   R.LpuUnitSet_id as \"LpuUnitSet_id\",
                   R.RegistryType_id as \"RegistryType_id\",
                   11 as \"RegistryStatus_id\",
                   2 as \"Registry_IsActive\",
                   RTrim(R.Registry_Num) || ' / в очереди: ' || LTrim(cast (RegistryQueue_Position as varchar)) as \"Registry_Num\",
                   RTrim(COALESCE(to_char(cast (R.Registry_accDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_accDate\",
                   --RTrim(COALESCE(to_char(cast(R.Registry_insDT as timestamp), 'DD.MM.YYYY'),'')) as \"Registry_insDT\",
                   RTrim(COALESCE(to_char(cast (R.Registry_begDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_begDate\",
                   RTrim(COALESCE(to_char(cast (R.Registry_endDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_endDate\",
                   2 as \"Registry_State\",
                   LpuUnitSet.LpuUnitSet_Code as \"LpuUnitSet_Code\",
                   L.Lpu_Nick as \"Lpu_Nick\",
                   OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
                   R.Lpu_id as \"Lpu_id\",
                   R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                   DC.DispClass_id as \"DispClass_id\",
                   DC.DispClass_Name as \"DispClass_Name\",
                   null as \"RegistryCheckStatus_SysNick\",
                   pt.PayType_id as \"PayType_id\",
                   pt.PayType_Name as \"PayType_Name\",
                   pt.PayType_SysNick as \"PayType_SysNick\",
                   R.RegistrySubType_id as \"RegistrySubType_id\",
                   R.Registry_Comments as \"Registry_Comments\",
                   --R.OrgRSchet_id,
                   0 as \"Registry_Count\",
                   0 as \"Registry_CountIsBadVol\",
                   0 as \"Registry_ErrorCount\",
                   0 as \"RegistryErrorCom_IsData\",
                   0 as \"RegistryError_IsData\",
                   0 as \"RegistryErrorTFOMS_IsData\",
                   0 as \"RegistryDouble_IsData\",
                   0 as \"RegistryDataBadVol_IsData\",
                   0 as \"Registry_Sum\",
                   1 as \"Registry_IsProgress\",
                   1 as \"Registry_IsNeedReform\",
                   '' as \"Registry_updDate\",
                   0 as \"RegistryHealDepCheckJournal_AccRecCount\",
                   0 as \"RegistryHealDepCheckJournal_DecRecCount\",
                   0 as \"RegistryHealDepCheckJournal_UncRecCount\",
                   0 as \"Registry_NoErrSum\",
                   0 as \"Registry_SumPaid\"
            from 
                 {$this->scheme}.v_RegistryQueue R
                 left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id
                 left join v_Lpu l on l.Lpu_id = R.Lpu_oid
                 left join LpuUnitSet on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id
                 left join v_DispClass DC on DC.DispClass_id = R.DispClass_id
                 left join v_PayType pt on pt.PayType_id = COALESCE(R.PayType_id, (
                                                                                    select PayType_id
                                                                                    from v_PayType
                                                                                    where PayType_SysNick = 'oms'
                                                                                    limit 1
                 ))
                 left join r2.RegistryStateLog RSL on R.RegistryQueue_id = RSL.Registry_id
            where {$filter}";
		}
		else 
		{
			$source_table = 'v_RegistryUfa';
			if (isset($data['RegistryStatus_id']))
			{
				if ($loadDeleted) {
					//6 - если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
					//т.к. для удаленных реестров статус не важен - не накладываем никаких условий на статус реестра.
				} else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
			}			
   			$query = "
			Select R.Registry_id as \"Registry_id\",
                   R.OrgSmo_id as \"OrgSmo_id\",
                   R.Lpu_oid as \"Lpu_oid\",
                   R.LpuUnitSet_id as \"LpuUnitSet_id\",
                   R.RegistryType_id as \"RegistryType_id\",
                   R.RegistryStatus_id as \"RegistryStatus_id\",
                   R.Registry_IsActive as \"Registry_IsActive\",
                   R.Registry_Comments as \"Registry_Comments\",
                   COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
                   RTrim(R.Registry_Num) as \"Registry_Num\",
                   RTrim(COALESCE(to_char(cast (R.Registry_accDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_accDate\",
                   RTrim(COALESCE(to_char(cast (R.Registry_insDT as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_insDT\",
                   RTrim(COALESCE(to_char(cast (R.Registry_begDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_begDate\",
                   RTrim(COALESCE(to_char(cast (R.Registry_endDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_endDate\",
                   RSL.Registry_State as \"Registry_State\",
                   R.LpuUnitSet_Code as \"LpuUnitSet_Code\",
                   L.Lpu_Nick as \"Lpu_Nick\",
                   R.OrgSmo_Name as \"OrgSmo_Name\",
                   R.Lpu_id as \"Lpu_id\",
                   R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                   DC.DispClass_id as \"DispClass_id\",
                   DC.DispClass_Name as \"DispClass_Name\",
                   rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\",
                   rcs.RegistryCheckStatus_SysNick as \"RegistryCheckStatus_SysNick\",
                   pt.PayType_id as \"PayType_id\",
                   pt.PayType_Name as \"PayType_Name\",
                   pt.PayType_SysNick as \"PayType_SysNick\",
                   R.RegistrySubType_id as \"RegistrySubType_id\",
                   --R.OrgRSchet_id,
                   COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
                   COALESCE(R.Registry_CountIsBadVol, 0) as \"Registry_CountIsBadVol\",
                   COALESCE(R.Registry_ErrorCount, 0) as \"Registry_ErrorCount\",
                   RegistryErrorCom.RegistryErrorCom_IsData as \"RegistryErrorCom_IsData\",
                   RegistryError.RegistryError_IsData as \"RegistryError_IsData\",
                   RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\",
                   RegistryDouble.RegistryDouble_IsData as \"RegistryDouble_IsData\",
                   RegistryDataBadVol.RegistryDataBadVol_IsData as \"RegistryDataBadVol_IsData\",
                   COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
                   case
                     when RQ.RegistryQueue_id is not null then 1
                     else 0
                   end as \"Registry_IsProgress\",
                   rhdcj.RegistryHealDepCheckJournal_AccRecCount as \"RegistryHealDepCheckJournal_AccRecCount\",
                   rhdcj.RegistryHealDepCheckJournal_DecRecCount as \"RegistryHealDepCheckJournal_DecRecCount\",
                   rhdcj.RegistryHealDepCheckJournal_UncRecCount as \"RegistryHealDepCheckJournal_UncRecCount\",
                   rhdcj.RegistryHealDepCheckJournal_AccRecSum as \"Registry_NoErrSum\",
                   case
                     when RegistryStatus_id = 4 then rhdcj.RegistryHealDepCheckJournal_AccRecSum
                     else 0
                   end as \"Registry_SumPaid\",
                   RTrim(COALESCE(to_char(cast (R.Registry_updDT as timestamp), 'DD.MM.YYYY'), '')) || ' ' || RTrim(COALESCE(to_char(cast (R.Registry_updDT as timestamp), 'HH24:MI:SS'), '')) as \"Registry_updDate\",
                   R.Registry_pack as \"Registry_pack\",
                   RTrim(COALESCE(to_char(cast (R.Registry_orderDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_orderDate\",
                   CASE
                     WHEN R.Registry_IsNotInsur = 2 THEN 'true'
                     ELSE 'false'
                   END as \"Registry_IsNotInsurC\"
            from 
                 {$this->scheme}.{$source_table} R
                 left join v_Lpu l on l.Lpu_id = R.Lpu_oid
                 left join r2.RegistryStateLog RSL on R.Registry_id = RSL.Registry_id
                 left join v_DispClass DC on DC.DispClass_id = R.DispClass_id
                 left join v_PayType pt on pt.PayType_id = COALESCE(R.PayType_id, (
                                                                                    select PayType_id
                                                                                    from v_PayType
                                                                                    where PayType_SysNick = 'oms'
                                                                                    limit 1
                 ))
                 left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
                 LEFT JOIN LATERAL
                 (
                   select RegistryQueue_id
                   from {$this->scheme}.v_RegistryQueue
                   where Registry_id = R.Registry_id
                 ) RQ ON true
                 LEFT JOIN LATERAL
                 (
                   select rhdcj.RegistryHealDepCheckJournal_AccRecCount,
                          rhdcj.RegistryHealDepCheckJournal_DecRecCount,
                          rhdcj.RegistryHealDepCheckJournal_UncRecCount,
                          rhdcj.RegistryHealDepCheckJournal_AccRecSum
                   from v_RegistryHealDepCheckJournal rhdcj
                   where rhdcj.Registry_id = r.Registry_id
                   order by rhdcj.RegistryHealDepCheckJournal_Count desc,
                            rhdcj.RegistryHealDepCheckJournal_id desc
                   limit 1
                 ) rhdcj ON true
                 LEFT JOIN LATERAL
                 (
                   select case
                                  when RE.Registry_id is not null then 1
                                  else 0
                                end as RegistryErrorCom_IsData
                   from dbo.v_{$this->RegistryErrorComObject} RE
                   where RE.Registry_id = R.Registry_id
                   limit 1
                 ) RegistryErrorCom ON true
                 LEFT JOIN LATERAL
                 (
                   select case
                                  when RE.Registry_id is not null then 1
                                  else 0
                                end as RegistryError_IsData
                   from {$this->scheme}.v_{$this->RegistryErrorObject} RE
                   where RE.Registry_id = R.Registry_id
                   limit 1
                 ) RegistryError ON true
                 LEFT JOIN LATERAL
                 (
                   select case
                                  when RE.Registry_id is not null then 1
                                  else 0
                                end as RegistryErrorTFOMS_IsData
                   from {$this->scheme}.v_RegistryErrorTFOMS RE
                   where RE.Registry_id = R.Registry_id
                   limit 1
                 ) RegistryErrorTFOMS ON true
                 LEFT JOIN LATERAL
                 (
                   select case
                                  when RE.Registry_id is not null then 1
                                  else 0
                                end as RegistryDouble_IsData
                   from {$this->scheme}.v_RegistryDouble RE
                   where RE.Registry_id = R.Registry_id
                   limit 1
                 ) RegistryDouble ON true
                 LEFT JOIN LATERAL
                 (
                   select case
                                  when RE.Registry_id is not null then 1
                                  else 0
                                end as RegistryDataBadVol_IsData
                   from {$this->scheme}.v_{$this->RegistryDataObject} RE
                   where RE.Registry_id = R.Registry_id and
                         RE.{$this->RegistryDataObject}_IsBadVol = 2
                   limit 1
                 ) RegistryDataBadVol ON true
            where {$filter}
            order by R.Registry_endDate DESC,
                     R.Registry_updDT DESC
			";
		}

		// Если регистры относятся к СМП, тянем данные из базы СМП

 		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
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
		if ($data['Registry_id']<=0)
		{
			return false;
		}

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and lower(ps.Person_SurName) LIKE lower(:Person_SurName) ";

			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and lower(ps.Person_FirName) LIKE lower(:Person_FirName) ";

			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and lower(ps.Person_SecName) LIKE lower(:Person_SecName) ";

			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and lower(rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, ''))) LIKE lower(:Person_FIO) ";


			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}
		if (!empty($data['Polis_Num']))
		{
			$filter .= " and RD.Polis_Num = :Polis_Num ";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		$regData = $this->queryResult("select Registry_IsNotInsur as \"Registry_IsNotInsur\", Registry_IsZNO as \"Registry_IsZNO\", OrgSmo_id as \"OrgSmo_id\", to_char(Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\" from {$this->scheme}.v_Registry  where Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));



		if (empty($regData[0])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		}

		if ( $regData[0]['Registry_IsZNO'] == 2 ) {
			$filter .= " and RD.RegistryData_IsZNO in (2, 3)";
		}
		else {
			$filter .= " and COALESCE(RD.RegistryData_IsZNO, 1) = 1";

		}

		$Registry_accDate = $regData[0]['Registry_accDate'];
		$Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];
		$OrgSmo_id = $regData[0]['OrgSmo_id'];
		if ($Registry_IsNotInsur == 2) {
			// если реестр по СМО, то не зависимо от соц. статуса
			if ($this->RegistryType_id == 6) {
				$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
			} else {
				$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
			}
		} else if ($OrgSmo_id == 8) {
			// инотеры
			$filter_rd = " and RD.Polis_id IS NOT NULL";
			$filter .= " and COALESCE(os.OrgSMO_RegNomC,'')=''";

		}
		else {
			// @task https://redmine.swan.perm.ru//issues/109876
			if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8000233 ) {
				return false;
			}
			else if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8001229 ) {
				$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
			}
			else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8000227 ) {
				return false;
			}
			else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8001750 ) {
				$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
			}
			else {
				$filter_rd = " and RD.OrgSmo_id = RF.OrgSmo_id";
			}
		}

		if ( $this->RegistryType_id == 6 ) {
			$evnFields = "
				null as \"Evn_rid\",
				111 as \"EvnClass_id\",
			";
			$joinByEvnClass = "";
		}
		else {
			$evnFields = "
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
			";
			$joinByEvnClass = "
				left join v_Evn Evn  on Evn.Evn_id = RE.Evn_id
			";
		}

		$query = "
			select
                   -- select
                   RE.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
                   RE.Registry_id as \"Registry_id\",
                   R.RegistryType_id as \"RegistryType_id\",
                   RE.Evn_id as \"Evn_id\",
                   {$evnFields} 
                   ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
                   ret.RegistryErrorType_Name as \"RegistryErrorType_Name\",
                   RegistryErrorType_Descr || ' (' || RETF.RegistryErrorTFOMSField_Name || ')' as \"RegistryError_Comment\",
                   rtrim(COALESCE(ps.Person_SurName, '')) || ' ' || rtrim(COALESCE(ps.Person_FirName, '')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"Person_FIO\",
                   ps.Person_id as \"Person_id\",
                   ps.PersonEvn_id as \"PersonEvn_id\",
                   ps.Server_id as \"Server_id\",
                   to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
                   re.RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
                   re.RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
                   re.RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
                   COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
                   case
                     when RE.Evn_id IS NOT NULL then 1
                     else 2
                   end as \"RegistryData_notexist\",
                   retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\",
                   null as \"IsGroupEvn\",
                   LS.LpuSection_Name as \"LpuSection_Name\",
                   LB.LpuBuilding_Name as \"LpuBuilding_Name\",
                   COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
                   COALESCE(cast (msc.MedSpecClass_Code as varchar) || '. ', '') || msc.MedSpecClass_Name as \"MedSpecOms_Name\",
                   RD.MedPersonal_Fio as \"MedPersonal_Fio\",
                   to_char(RD.Evn_setDate, 'DD.MM.YYYY') as \"Evn_setDate\",
                   to_char(RD.Evn_disDate, 'DD.MM.YYYY') as \"Evn_disDate\"
                   -- end select
            from
                 -- from
                 {$this->scheme}.v_RegistryGroupLink RGL
                 inner join {$this->scheme}.v_Registry RF on RF.Registry_id = RGL.Registry_pid
                 inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
                 inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
                 inner join {$this->scheme}.{$this->RegistryDataObject} 
                 RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id 
                 {$filter_rd}
                 left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
                 left join fed.v_MedSpecClass msc on msc.MedSpecClass_id = rd.MedSpec_id
                 left join v_OrgSmo os on os.OrgSmo_id = rd.OrgSmo_id
                 left join v_LpuSection ls on LS.LpuSection_id = RD.LpuSection_id
                 left join v_LpuUnit lu on LU.LpuUnit_id = LS.LpuUnit_id
                 left join v_LpuBuilding lb on LB.LpuBuilding_id = LU.LpuBuilding_id
                 left join RegistryErrorTFOMSField RETF on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
                 left join v_Person_bdz ps on ps.PersonEvn_id = RD.PersonEvn_id and ps.Server_id = RD.Server_id 
                 {$joinByEvnClass}
                 left join r2.RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
                 left join v_RegistryErrorTFOMSLevel retl on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
                 -- end from
            where
                  -- where
                  RGL.Registry_pid =:Registry_id and
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

		$this->setRegistryParamsByType($data);

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
	 * Удаление финального реестра
	 */
	function deleteUnionRegistry($data)
	{
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.RegistryStatus_id as \"RegistryStatus_id\"
			from
				{$this->scheme}.v_Registry r 
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
				if ($resp[0]['RegistrySubType_id'] != '2') {
					return array('Error_Msg' => 'Указанный реестр не является реестром по СМО');
				}

				if ($resp[0]['RegistryStatus_id'] != 3) { // если не "В работе"
					return array('Error_Msg' => 'Действие доступно для реестров в статусе «В работе»');
				}
			}
		}

		if (empty($data['Registry_id'])) {
			return array('Error_Msg' => 'Не найден реестр для удаления');
		}

		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink
			where Registry_pid = :Registry_id
		";

		$this->db->query($query, array(
			'Registry_id' => $data['Registry_id']
		));

		// 2. удаляем сам реестр
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from {$this->scheme}.p_Registry_del(
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id);
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
	 * Удаление финального реестра (с удалением случаев из предварительных реестров).
	 */
	function deleteUnionRegistryWithData($data)
	{
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.RegistryStatus_id as \"RegistryStatus_id\",
				r.RegistryType_id as \"RegistryType_id\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.Registry_IsZNO as \"Registry_IsZNO\",
				r.OrgSmo_id as \"OrgSmo_id\",
				to_char(r.Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
			from
				{$this->scheme}.v_Registry r 
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
				if ($resp[0]['RegistrySubType_id'] != '2') {
					return array('Error_Msg' => 'Указанный реестр не является реестром по СМО');
				}

				if ($resp[0]['RegistryStatus_id'] != 3) { // если не "В работе"
					return array('Error_Msg' => 'Действие доступно для реестров в статусе «В работе»');
				}

				// достаём случаи по данному реестру
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
					$filter .= " and COALESCE(os.OrgSMO_RegNomC,'')=''";

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
					$filter_rd .= " and RD.RegistryData_IsZNO in (2, 3) ";
				}
				else {
					$filter_rd .= " and COALESCE(RD.RegistryData_IsZNO, 1) = 1 ";

				}

				$query = "
					select
						rd.Registry_id as \"Registry_id\",
						rd.Evn_id as \"Evn_id\"
					from
						{$this->scheme}.v_RegistryGroupLink rgl 
						inner join {$this->scheme}.v_Registry rf  on rf.Registry_id = rgl.Registry_pid
						inner join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rgl.Registry_id = rd.Registry_id {$filter_rd}
						left join v_OrgSmo os  on os.OrgSmo_id = rd.OrgSmo_id
					where
						rgl.Registry_pid = :Registry_id
						{$filter}
				";

				$resp_rd = $this->queryResult($query, array(
					'Registry_id' => $data['Registry_id']
				));

				$Registrys = array();

				foreach($resp_rd as $resp_rdone) {
					// Удаляем случаи
					$this->deleteRegistryData(array(
						'EvnIds' => array($resp_rdone['Evn_id']),
						'Registry_id' => $resp_rdone['Registry_id'],
						'RegistryType_id' => $resp[0]['RegistryType_id'],
						'RegistryData_deleted' => 2
					));

					if (!in_array($resp_rdone['Registry_id'], $Registrys)) {
						$Registrys[] = $resp_rdone['Registry_id'];
					}
				}

				// произвести пересчёт обычных реестров
				foreach($Registrys as $Registry_id) {
					$this->refreshRegistry(array(
						'Registry_id' => $Registry_id,
						'pmUser_id' => $data['pmUser_id']
					));
				}

				// 1. удаляем все связи
				$query = "
					delete {$this->scheme}.RegistryGroupLink
					where Registry_pid = :Registry_id
				";
				$this->db->query($query, array(
					'Registry_id' => $data['Registry_id']
				));

				// 2. удаляем сам реестр
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                        
					from {$this->scheme}.p_Registry_del(
						Registry_id := :Registry_id,
						pmUser_delID := :pmUser_id);
				";

				$result = $this->db->query($query, array(
					'Registry_id' => $data['Registry_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (is_object($result))
				{
					return $result->result('array');
				}
			}
		}

		return array('Error_Msg' => 'Не найден реестр для удаления');
	}


	/**
	 * comment
	 */
	function loadUnionRegistry($data)
	{

		#Task# по плану 1.11 Установка отчётного месяца/года + номера пачки реестра
		#Добавлени вьюха r2.v_RegistryUfa
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
		if (isset($data['Registry_IsNew']) && !isset($data['Registry_id'])) {
			$filter .= ' and COALESCE(R.Registry_IsNew, 1) = :Registry_IsNew';

			$params['Registry_IsNew'] = $data['Registry_IsNew'];
		}
		if (isset($data['RegistryStatus_id']))
		{
			// только если оплаченные!!!
			if( 4 == (int)$data['RegistryStatus_id'] ) {
				if( $data['Registry_accYear'] > 0 ) {
					$filter .= ' and to_char(cast(R.Registry_begDate as date),\'YYYY\') <= :Registry_accYear';
					$filter .= ' and to_char(cast(R.Registry_endDate as date),\'YYYY\') >= :Registry_accYear';
					$params['Registry_accYear'] = $data['Registry_accYear'];
				}
			}
		}
		if ((isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id'] == 11))
		{
			$query = "
			Select R.RegistryQueue_id as \"Registry_id\",
                   R.OrgSmo_id as \"OrgSmo_id\",
                   R.LpuUnitSet_id as \"LpuUnitSet_id\",
                   R.RegistryType_id as \"RegistryType_id\",
                   11 as \"RegistryStatus_id\",
                   2 as \"Registry_IsActive\",
                   RTrim(R.Registry_Num) || ' / в очереди: ' || LTrim(cast (RegistryQueue_Position as varchar)) as \"Registry_Num\",
                   RTrim(COALESCE(to_char(cast (R.Registry_accDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_accDate\",
                   --RTrim(COALESCE(to_char(cast(R.Registry_insDT as timestamp), 'DD.MM.YYYY'),'')) as \"Registry_insDT\",
                   RTrim(COALESCE(to_char(cast (R.Registry_begDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_begDate\",
                   RTrim(COALESCE(to_char(cast (R.Registry_endDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_endDate\",
                   2 as \"Registry_State\",
                   LpuUnitSet.LpuUnitSet_Code as \"LpuUnitSet_Code\",
                   OrgSmo.OrgSmo_Nick as \"OrgSmo_Name\",
                   R.Lpu_id as \"Lpu_id\",
                   R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                   COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",
                   R.Registry_Comments as \"Registry_Comments\",
                   DC.DispClass_id as \"DispClass_id\",
                   DC.DispClass_Name as \"DispClass_Name\",
                   --R.OrgRSchet_id,
                   0 as \"Registry_Count\",
                   0 as \"Registry_ErrorCount\",
                   0 as \"Registry_Sum\",
                   1 as \"Registry_IsProgress\",
                   1 as \"Registry_IsNeedReform\",
                   '' as \"Registry_updDate\"
            from 
                 {$this->scheme}.v_RegistryQueue R
                 left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = R.OrgSmo_id
                 left join LpuUnitSet on LpuUnitSet.LpuUnitSet_id = R.LpuUnitSet_id
                 left join r2.RegistryStateLog RSL on R.RegistryQueue_id = RSL.Registry_id
                 left join v_DispClass DC on DC.DispClass_id = R.DispClass_id
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
			Select R.Registry_id as \"Registry_id\",
                   R.OrgSmo_id as \"OrgSmo_id\",
                   R.LpuUnitSet_id as \"LpuUnitSet_id\",
                   R.RegistryType_id as \"RegistryType_id\",
                   R.RegistryStatus_id as \"RegistryStatus_id\",
                   R.Registry_IsActive as \"Registry_IsActive\",
                   R.Registry_Comments as \"Registry_Comments\",
                   COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
                   RTrim(R.Registry_Num) as \"Registry_Num\",
                   RTrim(COALESCE(to_char(cast (R.Registry_accDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_accDate\",
                   RTrim(COALESCE(to_char(cast (R.Registry_insDT as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_insDT\",
                   RTrim(COALESCE(to_char(cast (R.Registry_begDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_begDate\",
                   RTrim(COALESCE(to_char(cast (R.Registry_endDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_endDate\",
                   --R.Registry_Sum,
                   RSL.Registry_State as \"Registry_State\",
                   R.LpuUnitSet_Code as \"LpuUnitSet_Code\",
                   R.OrgSmo_Name as \"OrgSmo_Name\",
                   R.Lpu_id as \"Lpu_id\",
                   R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                   COALESCE(R.Registry_IsZNO, 1) as \"Registry_IsZNO\",
                   --R.OrgRSchet_id,
                   COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
                   COALESCE(R.Registry_ErrorCount, 0) as \"Registry_ErrorCount\",
                   COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
                   case
                     when RQ.RegistryQueue_id is not null then 1
                     else 0
                   end as \"Registry_IsProgress\",
                   RTrim(COALESCE(to_char(cast (R.Registry_updDT as timestamp), 'DD.MM.YYYY'), '')) || ' ' || RTrim(COALESCE(to_char(cast (R.Registry_updDT as timestamp), 'HH24:MI:SS'), '')) as \"Registry_updDate\",
                   DC.DispClass_id as \"DispClass_id\",
                   DC.DispClass_Name as \"DispClass_Name\",
                   R.Registry_pack as \"Registry_pack\",
                   RTrim(COALESCE(to_char(cast (R.Registry_orderDate as timestamp), 'DD.MM.YYYY'), '')) as \"Registry_orderDate\",
                   CASE
                     WHEN R.Registry_IsNotInsur = 2 THEN 'true'
                     ELSE 'false'
                   END as \"Registry_IsNotInsurC\"
            from {$this->scheme}.v_RegistryUfa R
                 left join r2.RegistryStateLog RSL on R.Registry_id = RSL.Registry_id
                 left join v_DispClass DC on DC.DispClass_id = R.DispClass_id
                 LEFT JOIN LATERAL
                 (
                   select RegistryQueue_id
                   from {$this->scheme}.v_RegistryQueue
                   where Registry_id = R.Registry_id
                   limit 1
                 ) RQ ON true
            where {$filter}
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
	* Установка реестра в очередь на формирование 
	* Возвращает номер в очереди 
	*/
	function saveRegistryQueue($data) 
	{
		// Сохранение нового реестра
		if (0 == $data['Registry_id']) 
		{
			$data['Registry_IsActive'] = 2;
			$operation = 'insert';
		}
		else
		{
			$operation = 'update';

			$query = "
				select r.OrgSmo_id as \"OrgSmo_id\",
                   rg.Registry_pid as \"Registry_pid\",
                   r.RegistrySubType_id as \"RegistrySubType_id\"
            from {$this->scheme}.v_Registry r
                 LEFT JOIN LATERAL
                 (
                   select rgl.Registry_pid
                   from {$this->scheme}.v_RegistryGroupLink rgl
                   where rgl.Registry_id = r.Registry_id
                   limit 1
                 ) rg ON true
            where r.Registry_id =:Registry_id
			";

			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка получения данных по реестру'));
			}

			$resp = $result->result('array');
			if (count($resp) == 0) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка получения данных по реестру'));
			}

			/*if (!empty($resp[0]['OrgSmo_id']) && empty($data['OrgSmo_id'])) {
				return array(array('success' => false, 'Error_Msg' => 'Поле "СМО" обязательно для заполнения'));
			}*/

			if (!empty($data['OrgSmo_id']) && empty($resp[0]['RegistrySubType_id'])) {
				// Переформирование доступно только для предварительных реестров без указания СМО (если в шапке реестра указана СМО, то переформировывать/ изменять данный реестр нельзя, действие должно быть не активно).
				return array('Error_Msg' => 'Действие доступно только для предварительных реестров без СМО');
			} else if (!empty($resp[0]['Registry_pid'])) {
				// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один реестр по СМО.
				return array('Error_Msg' => 'Предварительный реестр входит в реестр по СМО, переформирование невозможно');
			}

			$data['RegistrySubType_id'] = $resp[0]['RegistrySubType_id'];
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
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'Registry_IsNotInsur' => $data['Registry_IsNotInsur'],
			//'OrgRSchet_id' => $data['OrgRSchet_id'],
			'LpuUnitSet_id' => $data['LpuUnitSet_id'],
			'OrgSmo_id' => $data['OrgSmo_id'],
			'Lpu_oid' => $data['Lpu_oid'],
			'Registry_accDate' => $data['Registry_accDate'],
			'Registry_Comments' => $data['Registry_Comments'],
			'pmUser_id' => $data['pmUser_id'],
			'Registry_IsNew' => $data['Registry_IsNew'],
			'RegistrySubType_id' => $data['RegistrySubType_id'],
			'DispClass_id' => $data['DispClass_id'],
			'PayType_id' => $data['PayType_id']
		);
		if (in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()))
		{
			if ($data['RegistrySubType_id'] == 3) {
				// При сохранении реестра проверяется, что не существует реестра за тот же год. Если реестр существует, то выдается ошибка «В году может быть только один реестр для контроля объемов МО»
				$query = "
					(select RegistryQueue_id as \"RegistryQueue_id\"
                    from {$this->scheme}.v_RegistryQueue
                    where RegistryType_id =:RegistryType_id and
                          RegistrySubType_id =:RegistrySubType_id and
                          Lpu_id =:Lpu_id and
                          date_part('YEAR', Registry_begDate) = date_part('YEAR',:Registry_begDate) and
                          COALESCE(Lpu_oid, 0) = COALESCE(CAST(:Lpu_oid as bigint), 0) and
                          Registry_id <> COALESCE(CAST(:Registry_id as bigint), 0)
                    limit 1)
                    union all
                    (select Registry_id as \"Registry_id\"
                    from {$this->scheme}.v_Registry
                    where RegistryType_id =:RegistryType_id and
                          RegistrySubType_id =:RegistrySubType_id and
                          Lpu_id =:Lpu_id and
                          date_part('YEAR', Registry_begDate) = date_part('YEAR', :Registry_begDate) and
                          COALESCE(Lpu_oid, 0) = COALESCE(CAST(:Lpu_oid as bigint), 0) and
                          Registry_id <> COALESCE(CAST(:Registry_id as bigint), 0)
                    limit 1)
				";
				$result = $this->db->query($query, $params);
				if (is_object($result))
				{
					$resp = $result->result('array');
					if (count($resp) > 0) {
						return array(array('success' => false, 'Error_Msg' => 'В году может быть только один реестр для контроля объемов МО.'));
					}
				}
			}

			$query = "
				select RegistryQueue_id as \"RegistryQueue_id\"
                from {$this->scheme}.v_RegistryQueue
                where COALESCE(Registry_id, 0) = COALESCE(:Registry_id::bigint, 0) and
                      COALESCE(RegistryType_id, 0) = COALESCE(:RegistryType_id::bigint, 0) and
                      COALESCE(RegistrySubType_id, 0) = COALESCE(:RegistrySubType_id::bigint, 0) and
                      COALESCE(DispClass_id, 0) = COALESCE(:DispClass_id::bigint, 0) and
                      COALESCE(PayType_id, 0) = COALESCE(:PayType_id::bigint, 0) and
                      COALESCE(Lpu_id, 0) = COALESCE(:Lpu_id::bigint, 0) and
                      COALESCE(Registry_IsNotInsur, 0) = COALESCE(:Registry_IsNotInsur::bigint, 0) and
                      COALESCE(CAST(Registry_begDate as varchar), '') = COALESCE(CAST(:Registry_begDate as varchar), '') and
                      COALESCE(CAST(Registry_endDate as varchar), '') = COALESCE(CAST(:Registry_endDate as varchar), '') and
                      COALESCE(OrgSmo_id, 0) = COALESCE(:OrgSmo_id::bigint, 0) and
                      COALESCE(Lpu_oid, 0) = COALESCE(:Lpu_oid::bigint, 0) and
                      COALESCE(LpuUnitSet_id, 0) = COALESCE(:LpuUnitSet_id::bigint, 0) and
                      COALESCE(Registry_Num, '0') = COALESCE(:Registry_Num::varchar, '0')
                limit 1                  
			";

			$result = $this->db->query($query, $params);

			
			if (is_object($result)) 
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return array(array('success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b>'));
				}
			}
					  
			$query = "
				select RegistryQueue_id as \"RegistryQueue_id\", RegistryQueue_Position as \"RegistryQueue_Position\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                    
				from {$this->scheme}.p_RegistryQueue_ins(
					RegistryQueue_id := null,
					RegistryQueue_Position := null,
					Registry_id := :Registry_id,
					RegistryType_id := :RegistryType_id,
					Lpu_id := :Lpu_id,
					Registry_begDate := :Registry_begDate,
					Registry_endDate := :Registry_endDate,
					OrgSmo_id := :OrgSmo_id,
					Lpu_oid := :Lpu_oid,
					LpuUnitSet_id := :LpuUnitSet_id,
					Registry_IsNew := :Registry_IsNew,
					Registry_Num := :Registry_Num,
					Registry_accDate := dbo.tzGetDate(), 
					RegistryStatus_id := :RegistryStatus_id,
					Registry_IsNotInsur := :Registry_IsNotInsur,
					Registry_Comments := :Registry_Comments,
					RegistrySubType_id := :RegistrySubType_id,
					DispClass_id := :DispClass_id,
					PayType_id := :PayType_id,
					pmUser_id := :pmUser_id);
			";

			//echo getDebugSql($query, $params);
			//exit;

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
	 * comment
	 */ 	
	function reformRegistry($data) 
	{
		$query = "
			select r.Registry_id as \"Registry_id\",
                   r.Lpu_id as \"Lpu_id\",
                   r.RegistryType_id as \"RegistryType_id\",
                   r.RegistrySubType_id as \"RegistrySubType_id\",
                   r.RegistryStatus_id as \"RegistryStatus_id\",
                   r.DispClass_id as \"DispClass_id\",
                   r.PayType_id as \"PayType_id\",
                   to_chat(r.Registry_begDate, 'YYYYMMDD') as \"Registry_begDate\",
                   to_char(r.Registry_endDate, 'YYYYMMDD') as \"Registry_endDate\",
                   r.OrgSmo_id as \"OrgSmo_id\",
                   r.Lpu_oid as \"Lpu_oid\",
                   r.LpuUnitSet_id as \"LpuUnitSet_id\",
                   r.Registry_Num as \"Registry_Num\",
                   r.Registry_IsActive as \"Registry_IsActive\",
                   r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                   r.Registry_Comments as \"Registry_Comments\",
                   to_char(r.Registry_accDate, 'YYYYMMDD') as \"Registry_accDate\",
                   rg.Registry_pid as \"Registry_pid\"
            from {$this->scheme}.v_Registry r
                 LEFT JOIN LATERAL
                 (
                   select rgl.Registry_pid
                   from {$this->scheme}.v_RegistryGroupLink rgl
                   where rgl.Registry_id = r.Registry_id
                   limit 1
                 ) rg ON true
            where r.Registry_id = ? and
                  COALESCE(r.RegistrySubType_id, 1) <> 2
		";

		$result = $this->db->query($query, array($data['Registry_id']));
		
		if (is_object($result)) 
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				if (!empty($row[0]['OrgSMO_id'])) {
					// Переформирование доступно только для предварительных реестров без указания СМО (если в шапке реестра указана СМО, то переформировывать/ изменять данный реестр нельзя, действие должно быть не активно).
					return array('Error_Msg' => 'Действие доступно только для предварительных реестров без СМО');
				} else if (!empty($row[0]['Registry_pid'])) {
					// Переформировать/удалить предварительный реестр можно только в случае если он (предварительный реестр) не ссылается ни на один реестр по СМО.
					return array('Error_Msg' => 'Предварительный реестр входит в реестр по СМО, переформирование невозможно');
				}


				//$data['Registry_id'] = $data['Registry_id'];
				//$data['Lpu_id'] = $data['Lpu_id'];
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistrySubType_id'] = $row[0]['RegistrySubType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['DispClass_id'] = $row[0]['DispClass_id'];
				$data['PayType_id'] = $row[0]['PayType_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['Registry_IsNotInsur'] = $row[0]['Registry_IsNotInsur'];
				$data['OrgSmo_id'] = $row[0]['OrgSmo_id'];
				$data['Lpu_oid'] = $row[0]['Lpu_oid'];
				$data['LpuUnitSet_id'] = $row[0]['LpuUnitSet_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];
				$data['Registry_Comments'] = $row[0]['Registry_Comments'];
				//$data['pmUser_id'] = $data['pmUser_id'];
				// Переформирование реестра 
				//return  $this->saveRegistry($data);
				// Постановка реестра в очередь 
				return  $this->saveRegistryQueue($data);
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Реестр не найден в базе. Возможно, он был удален.');
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryError_del(
				Registry_id := :Registry_id,
				Evn_id := NULL,
				RegistryErrorType_id := NULL);
		";
		// echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);
		return true;
	}

	/**
	 * comment
	 */
	function deleteRegistryErrorTFOMS($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);

		if ( !empty($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2 ) {
			$regData = $this->queryResult("select Registry_IsNotInsur as \"Registry_IsNotInsur\", OrgSmo_id as \"OrgSmo_id\", Registry_IsZNO as \"Registry_IsZNO\", to_char(Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\" from {$this->scheme}.v_Registry  where Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));



			if (empty($regData[0])) {
				return array('Error_Msg' => 'Ошибка получения данных по реестру');
			}

			$filter = "";
			$join = "";

			if ( $regData[0]['Registry_IsNotInsur'] == 2 ) {
				// если реестр по СМО, то не зависимо от соц. статуса
				if ($this->RegistryType_id == 6) {
					$filter_rd = "and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code = '61'))";
				} else {
					$filter_rd = "and ((RD.Polis_id IS NULL and RD.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code = '61'))";
				}
			}
			else if ( $regData[0]['OrgSmo_id'] == 8 ) {
				// инотеры
				$filter_rd = "and RD.Polis_id is not null";
				$filter = "and COALESCE(os.OrgSMO_RegNomC, '' ) = ''";

				$join = "left join v_OrgSmo os  on os.OrgSmo_id = rd.OrgSmo_id";

			}
			else {
				// @task https://redmine.swan.perm.ru//issues/109876
				if ( $regData[0]['Registry_accDate'] >= '2017-05-25' && $regData[0]['OrgSmo_id'] == 8001229 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
				}
				else if ( $regData[0]['Registry_accDate'] >= '2018-10-25' && $regData[0]['OrgSmo_id'] == 8001750 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
				}
				else {
					$filter_rd = " and RD.OrgSmo_id = RF.OrgSmo_id";
				}
			}

			if ( $regData[0]['Registry_IsZNO'] == 2 ) {
				$filter_rd .= " and RD.RegistryData_IsZNO in (2, 3) ";
			}
			else {
				$filter_rd .= " and COALESCE(RD.RegistryData_IsZNO, 1) = 1 ";

			}

			$mainQuery = "
				delete from {$this->scheme}.RegistryErrorTFOMS where RegistryErrorTFOMS_id in (
					select RE.RegistryErrorTFOMS_id
                    from {$this->scheme}.v_RegistryGroupLink RGL
                         inner join {$this->scheme}.v_Registry RF on RF.Registry_id = RGL.Registry_pid
                         inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
                         inner join {$this->scheme}.v_RegistryErrorTFOMS RE on RE.Registry_id = R.Registry_id
                         inner join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id 
                                {$filter_rd} 
                         {$join}
                    where RGL.Registry_pid = :Registry_id 
                    {$filter}
				);
			";
		}
		else {
			$mainQuery = "
				delete from {$this->scheme}.RegistryErrorTFOMS where Registry_id = :Registry_id;
			";
		}

		$query = "

				{$mainQuery}
			select '' as \"Error_Code\", '' as \"Error_Msg\";
		";

		$result = $this->db->query($query, $params);

		return true;
	}

	/**
	 * comment
	 */
	function setErrorFromTFOMSImportRegistry($d, $data)
	{
		// Сохранение загружаемого реестра, точнее его ошибок

		$params = $d;
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['session']['pmuser_id'];
        $this->setRegistryParamsByType($data);
	
		// если задан IDCASE значит идёт разбор из xml, иначе из dbf
		if (!empty($params['IDCASE']) || !empty($params['SL_ID'])) {
			if ($data['Registry_id']>0)
			{
				$query = "SELECT RegistryErrorType_id  as \"RegistryErrorType_id\" FROM {$this->scheme}.RegistryErrorType  WHERE RegistryErrorType_Code = :FLAG LIMIT 1";

				$resp = $this->db->query($query, $params);
				if (is_object($resp))
				{
					$ret = $resp->result('array');
					if (is_array($ret) && (count($ret) > 0)) {

						$from = "
							{$this->scheme}.v_Registry r 

						";
						$filter = "r.Registry_id = :Registry_id";

						if (!empty($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) {
							// для финального ищем случай в предварительных
							$from = "
								{$this->scheme}.v_RegistryGroupLink rgl 
								inner join {$this->scheme}.v_Registry r  on r.Registry_id = rgl.Registry_id

							";
							$filter = "rgl.Registry_pid = :Registry_id";
						}

						if (!empty($params['IDCASE'])) {
							$filter .= " and rd.Evn_id = :IDCASE";
						} else if (!empty($params['SL_ID'])) {
							$filter .= " and rd.Evn_id = :SL_ID";
						}

						$params['FLAG'] = $ret[0]['RegistryErrorType_id'];
						$query = "
							WITH cte AS (								
							Select 
								rd.Evn_id,
								rd.Registry_id
							from
								{$from}
								inner join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Registry_id = r.Registry_id 
							where
								{$filter}
                            )                                
                            select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"							
							from {$this->scheme}.p_RegistryErrorTFOMS_ins(
								Registry_id := (SELECT Registry_id FROM cte),
								Evn_id := (SELECT Evn_id FROM cte),
								RegistryErrorType_id := :FLAG,
								RegistryErrorTFOMS_Comment := :COMMENTS,
								pmUser_id := :pmUser_id);
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
			if ($data['Registry_id']>0)
			{
				$query = "
					WITH cte AS (						
					Select 
						rd.Evn_id
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd 
					where
						rd.Registry_id = :Registry_id
						and rd.Guid = :ID
                    )					
                    select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"					
					from {$this->scheme}.p_RegistryErrorTFOMS_ins(
						Registry_id := :Registry_id,
						Evn_id := (SELECT Evn_id FROM cte),
						RegistryErrorType_id := :FLAG,
						pmUser_id := :pmUser_id);
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
	 * Получение списка ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}

		$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id  as \"RegistryType_id\" FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and lower(ps.Person_SurName) LIKE lower(:Person_SurName) ";

			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and lower(ps.Person_FirName) LIKE lower(:Person_FirName) ";

			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and lower(ps.Person_SecName) LIKE lower(:Person_SecName) ";

			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and lower(rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, ''))) LIKE lower(:Person_FIO) ";


			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}
		if (!empty($data['filterIsZNO']) && $data['filterIsZNO'] == 2 ) {
			$filter .= " and RD.RegistryData_IsZNO in (2, 3) ";
		}
		if (!empty($data['Polis_Num'])) {
			$filter .= " and RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		$addToSelect = "";
		$leftjoin = "";

		if ( in_array($data['RegistryType_id'], array(7, 17)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd  on epd.EvnPLDisp_id = RD.Evn_rid ";

			$addToSelect .= ", epd.DispClass_id as \"DispClass_id\"";
		}

		if ($data['RegistryType_id'] == 6) {
			$evn_object = 'CmpCallCard';
			$evn_fields = "
				null as \"Evn_rid\",
				null as \"EvnClass_id\",
			";
		} else {
			$evn_object = 'Evn';
			$evn_fields = "
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
			";
		}

		$query = "
			  Select
                     -- select
                     RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
                     RE.Registry_id as \"Registry_id\",
                     RE.Evn_id as \"Evn_id\",
                     {$evn_fields} 
                     ret.RegistryErrorType_Name as \"RegistryError_FieldName\",
                     rtrim(COALESCE(ps.Person_SurName, '')) || ' ' || rtrim(COALESCE(ps.Person_FirName, '')) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) as \"Person_FIO\",
                     RD.Polis_Num as \"Polis_Num\",
                     ps.Person_id as \"Person_id\",
                     ps.PersonEvn_id as \"PersonEvn_id\",
                     ps.Server_id as \"Server_id\",
                     RTrim(COALESCE(to_char(cast (ps.Person_BirthDay as timestamp), 'DD.MM.YYYY'), '')) as \"Person_BirthDay\",
                     RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
                     RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
                     RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
                     COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
                     case
                       when RD.Evn_id IS NOT NULL then 1
                       else 2
                     end as \"RegistryData_notexist\",
                     to_char(cast (RD.Evn_setDate as timestamp), 'DD.MM.YYYY') as \"Evn_setDate\",
                     to_char(cast (RD.Evn_disDate as timestamp), 'DD.MM.YYYY') as \"Evn_disDate\",
                     rd.MedPersonal_Fio as \"MedPersonal_Fio\",
                     ls.LpuSection_Name as \"LpuSection_Name\",
                     lu.LpuBuilding_Name as \"LpuBuilding_Name\",
                     COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
                     COALESCE(cast (msc.MedSpecClass_Code as varchar) || '. ', '') || msc.MedSpecClass_Name as \"MedSpecOms_Name\",
                     ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
                     ret.RegistryErrorType_Name  as \"RegistryErrorType_Name\"
                     {$addToSelect}
                     -- end select
              from
                   -- from
                   {$this->scheme}.v_RegistryErrorTFOMS RE
                   left join {$this->scheme}.v_{$this->RegistryDataObject} 
                   RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
                   left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
                   left join fed.v_MedSpecClass msc on msc.MedSpecClass_id = rd.MedSpec_id
                   left join v_{$evn_object} Evn on Evn.{$evn_object}_id = RE.Evn_id
                   left join v_Person_bdz ps on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
                   left join 
                   {$this->scheme}.RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
                   left join v_LpuSection ls on LS.LpuSection_id = RD.LpuSection_id
                   left join v_LpuUnit lu on LU.LpuUnit_id = LS.LpuUnit_id
                   left join v_LpuBuilding lb on LB.LpuBuilding_id = LU.LpuBuilding_id 
                   {$leftjoin}
                   -- end from
              where
                    -- where
                    RE.Registry_id =:Registry_id and
                    {$filter}
                    -- end where
              order by
                       -- order by
                       RET.RegistryErrorType_Code
                       -- end order by";

		//echo getDebugSql($query, $params);exit;

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
	 * comment
	 */ 	
	function checkErrorDataInRegistry($data) {
		$this->setRegistryParamsByType($data);

		$from = "
			{$this->scheme}.v_Registry r 

		";
		$filter = "r.Registry_id = :Registry_id";

		if (!empty($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) {
			// для финального ищем случай в предварительных
			$from = "
				{$this->scheme}.v_RegistryGroupLink rgl 

				inner join {$this->scheme}.v_Registry r  on r.Registry_id = rgl.Registry_id

			";
			$filter = "rgl.Registry_pid = :Registry_id";
		}

		$emptyFilter = true;
		if (!empty($data['IDCASE'])) {
			$filter .= " and rd.Evn_id = :IDCASE";
			$params['IDCASE'] = $data['IDCASE'];
			$emptyFilter = false;
		}

		if (!empty($data['ID_PERS'])) {
			$filter .= " and rd.Person_id = :ID_PERS";
			$params['ID_PERS'] = $data['ID_PERS'];
			$emptyFilter = false;
		}

		if (!empty($data['SL_ID'])) {
			$filter .= " and rd.Evn_id = :SL_ID";
			$params['SL_ID'] = $data['SL_ID'];
			$emptyFilter = false;
		}

		if ($emptyFilter) {
			return false;
		}

		$query = "
			Select
				rd.Registry_id as \"Registry_id\"
			from
				{$from}
				inner join {$this->scheme}.v_{$this->RegistryDataObject} rd  on rd.Registry_id = r.Registry_id
			where
				{$filter}
		";
			
		$params['Registry_id'] = $data['Registry_id'];
		
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
			// @task https://redmine.swan.perm.ru/issues/85241
			// Добавил в название вызываемой хранимки условие на тип реестра - финальный/предварительный
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_" . ($data['RegistrySubType_id'] == 2 ? "SMO_" : "") . "setPaid(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id);
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
        $this->setRegistryParamsByType($data);
	
		// если задан IDCASE значит идёт разбор из xml, иначе из dbf
		if (!empty($d['IDCASE'])) {
		
			$params['IDCASE'] = $d['IDCASE'];
			if ($data['Registry_id']>0)
			{
				$query = "SELECT RegistryErrorType_id  as \"RegistryErrorType_id\" FROM {$this->scheme}.RegistryErrorType  WHERE RegistryErrorType_Code = :FLAG LIMIT 1";

				$resp = $this->db->query($query, $params);
				if (is_object($resp))
				{
					$ret = $resp->result('array');
					if (is_array($ret) && (count($ret) > 0)) {
					
						$params['FLAG'] = $ret[0]['RegistryErrorType_id'];
						$query = "
							WITH cte AS (
							Select 
								rd.Registry_id,
								rd.Evn_id,
								rd.LpuSection_id
							from
								{$this->scheme}.v_{$this->RegistryDataObject} rd  
							where
								rd.Registry_id = :Registry_id
								and rd.Evn_id = :IDCASE
                                )
                            select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"							
							from {$this->scheme}.p_RegistryError_ins(
								Registry_id := (SELECT Regisry_id FROM cte),
								Evn_id := (SELECT Evn_id FROM cte),
								RegistryErrorType_id := :FLAG,
								LpuSection_id := (SELECT LpuSection_id FROM cte),  
								pmUser_id := :pmUser_id);
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
					WITH cte AS (
					Select 
						rd.Registry_id,
						rd.Evn_id,
						rd.LpuSection_id
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd 
					where
						rd.Registry_id = :Registry_id
						and rd.Guid = :ID
					)
                    select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                    
					from {$this->scheme}.p_RegistryError_ins(
						Registry_id := (SELECT Regisry_id FROM cte),
						Evn_id := (SELECT Evn_id FROM cte),
						RegistryErrorType_id := :FLAG,
						LpuSection_id := (SELECT LpuSection_id FROM cte), 
						pmUser_id := :pmUser_id)
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
			  select RTRIM(Registry.Registry_Num) as \"Registry_Num\",
                     COALESCE(to_char(cast (Registry.Registry_accDate as timestamp), 'DD.MM.YYYY'), '') as \"Registry_accDate\",
                     COALESCE(to_char(cast (Registry.Registry_insDT as timestamp), 'DD.MM.YYYY'), '') as \"Registry_insDT\",
                     RTRIM(COALESCE(Org.Org_Name, '')) as \"Lpu_Name\",
                     COALESCE(CAST(Lpu.Lpu_RegNomC as varchar), '') as \"Lpu_RegNomC\",
                     COALESCE(CAST(Lpu.Lpu_RegNomN as varchar), '') as \"Lpu_RegNomN\",
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
                     cast (COALESCE(CAST(Registry.Registry_Sum as numeric), 0) as decimal) as \"Registry_Sum\",
                     OHDirector.OrgHeadPerson_Fio as \"Lpu_Director\",
                     OHGlavBuh.OrgHeadPerson_Fio as \"Lpu_GlavBuh\",
                     RT.RegistryType_id as \"RegistryType_id\",
                     RT.RegistryType_Code as \"RegistryType_Code\"
              from 
                   {$this->scheme}.v_Registry Registry
                   inner join Lpu on Lpu.Lpu_id = Registry.Lpu_id
                   inner join Org on Org.Org_id = Lpu.Org_id
                   inner join RegistryType RT on RT.RegistryType_id = Registry.RegistryType_id
                   left join Okved on Okved.Okved_id = Org.Okved_id
                   left join Address LpuAddr on LpuAddr.Address_id = Org.UAddress_id
                   left join OrgRSchet ORS on Registry.OrgRSchet_mid = ORS.OrgRSchet_id
                   left join v_OrgBank OB on OB.OrgBank_id = ORS.OrgBank_id
                   LEFT JOIN LATERAL
                   (
                     select substring(RTRIM(PS.Person_FirName), 1, 1) || '.' || substring(RTRIM(PS.Person_SecName), 1, 1) || '. ' || RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
                     from OrgHead OH
                          inner join v_PersonState PS on PS.Person_id = OH.Person_id
                     where OH.Lpu_id = Lpu.Lpu_id and
                           OH.OrgHeadPost_id = 1
                     limit 1
                   ) as OHDirector ON true
                   LEFT JOIN LATERAL
                   (
                     select substring(RTRIM(PS.Person_FirName), 1, 1) || '.' || substring(RTRIM(PS.Person_SecName), 1, 1) || '. ' || RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
                     from OrgHead OH
                          inner join v_PersonState PS on PS.Person_id = OH.Person_id
                     where OH.Lpu_id = Lpu.Lpu_id and
                           OH.OrgHeadPost_id = 2
                     limit 1
                   ) as OHGlavBuh ON true
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
			select RegErrorPerson_id as \"RegErrorPerson_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegErrorPerson_ins(
				RegErrorPerson_id := null,
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
				pmUser_id := :pmUser_id);
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
			select CountUpd as \"CountUpd\"
			from {$this->scheme}.xp_RegistryErrorPerson_process(
				Registry_id := :Registry_id,
				pmUser_id := :pmUser_id);
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
	* Функция возрвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	*/
	public function loadRegistryTypeNode($data) {
		if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
			return array(
				$this->_registryTypeList[1],
				$this->_registryTypeList[2],
				$this->_registryTypeList[6],
				$this->_registryTypeList[14],
				$this->_registryTypeList[15]
			);
		} else {
			unset($this->_registryTypeList[15]);
			return $this->_registryTypeList;
		}
	}

	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data) {
		$this->setRegistryParamsByType($data);

		$xmlExportPath = '';

		$query = "
			  select RTrim(R.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
                     R.RegistryType_id as \"RegistryType_id\",
                     R.RegistryStatus_id as \"RegistryStatus_id\",
                     COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
                     R.OrgSmo_id as \"OrgSmo_id\",
                     R.Registry_IsNew as \"Registry_IsNew\",
                     R.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                     R.Registry_IsZNO as \"Registry_IsZNO\",
                     R.DispClass_id as \"DispClass_id\",
                     pt.PayType_SysNick as \"PayType_SysNick\",
                     COALESCE(R.RegistrySubType_id, 1) as \"RegistrySubType_id\",
                     SUBSTRING(to_char(Registry_endDate, 'YYYYMMDD'), 3, 4) as \"Registry_endMonth\" -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
              from 
                   {$this->scheme}.Registry R
                   left join v_PayType pt on pt.PayType_id = r.PayType_id
              where R.Registry_id =:Registry_id
		";

		$result = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if ( !is_object($result) ) {
			return false;
		}

		return $result->result('array');
	}
	
	/**
	 * comment
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

			if (!empty($data['RegistrySubType_id'])) {
				switch ($data['RegistrySubType_id']) {
					case 3:
						$result = array(
							array('RegistryStatus_id' => 11, 'RegistryStatus_Name' => 'В очереди'),
							array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе')
						);
						break;
					case 2:
						$result = array(
							array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
							array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
							array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные')
						);
						break;
				}
			}
		}

		return $result;
	}
	/**
	 * Task#18694 По просьбе - папки эксортированных реестров будут иметь кирилические имена, пока для одиночных не используется.
	 */ 	
	function getSmoListUfa(){
		//Для рабочей БД - необходимо корректировать запрос
		$query = "
			  SELECT os.OrgSMO_id as \"Smo_id\",
                     RTRIM(LTRIM(REPLACE (REPLACE (o.Org_Nick, ' (РЕСПУБЛИКА БАШКОРТОСТАН)', ''), ' (РЕСПУБЛИКА БАШКОРТОСТАНА)', ''))) as \"Smo_Nick\",
                     os.Orgsmo_f002smocod as \"smo_code\"
              FROM OrgSMO os
                   left join Org o on o.Org_id = os.Org_id
              WHERE os.OrgSmo_endDate is NULL
                    --AND o.Org_OKATO iLIKE '80%' 
                    AND
                    o.Org_Nick iLIKE '%ашкорт%' OR
                    o.Org_Nick iLIKE '%УФОМС%'
                    --AND o.Org_endDate is null  
                    --AND o.KLRgn_id = 2          
              ORDER BY o.Org_Name 	
		";
	
		$res = $this->db->query($query, array());

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение данных из справочников корректировки объёмов
	 */ 
	function getRegistryLimitVolumeData($data)
	{ 
		/* 
		$res_data =  array(
						array('name'=>'Дневной стационар','limit'=>200, 'fact'=>190, 'over'=>0),
						array('name'=>'Дополнительные иследования','limit'=>100, 'fact'=>110, 'over'=>0),
						array('name'=>'Гемодиализ','limit'=>250, 'fact'=>265, 'over'=>0),
						array('name'=>'Стационар','limit'=>210, 'fact'=>240, 'over'=>0),
						array('name'=>'Поликлиника','limit'=>180, 'fact'=>185, 'over'=>0),
						array('name'=>'СМП','limit'=>230, 'fact'=>230, 'over'=>0)
					  );
	   
					  
		array_walk_recursive($res_data, 'ConvertFromWin1251ToUTF8');                                        
		*/
		$queryParams = array(
							//'Lpu_id' => $data['Lpu_id'], 
							'Lpu_id' => 35,
							'Registry_ids' => implode(',', json_decode($data['Registry_ids'],1)), 
							'RegistryType_id'=>$data['RegistryType_id'],
							'YEAR'=>(int)date('Y'));                  
		
		$query = "
				 -- Поликлиника
                    select 'polka' as \"name\",
                           (
                             select
                             LIMIT
                             from r2.LIMIT_VOL_POLIK_PGG
                             where CODE_MO =:Lpu_id and
                                   YEAR =:YEAR
                             limit 1
                           ) / 12 as \"limit\",
                          COUNT(RD.Evn_id) + 2100 as \"fact\"
                    from r2.RegistryData RD
                         join r2.v_Registry R on RD.Registry_id = R.Registry_id and not exists (
                                                                                                 select *
                                                                                                 from r2.RegistryError RE
                                                                                                 where RE.Evn_id = RD.Evn_id
                         )
                         join LpuUnitSet LP on LP.Lpu_id = R.Lpu_id and LP.LpuUnitSet_Code IN (1106925, 22151, 22159, 3006, 7019, 15016, 16149, 4113, 10023, 22158, 22117, 914, 134)
                    where R.Registry_id IN (".implode(',', json_decode($data['Registry_ids'],1)).")
                    group by (RD.NumCard)
                    union all
					--CMP    
                    select 'smp' as \"name\",
                           (
                             select
                             LIMIT
                             from r2.LIMIT_VOL_SMP_PGG
                             where CODE_MO =:Lpu_id and
                                   YEAR =:YEAR
                             limit 1
                           ) / 12 as \"limit\",
                          COUNT(RD.CmpCloseCard_id) + 5000 as \"fact\"
                    from r2.RegistryDataCmp RD
                         join r2.v_Registry R on RD.Registry_id = R.Registry_id and not exists (
                                                                                                 select *
                                                                                                 from r2.RegistryErrorCmp RE
                                                                                                 where RE.CmpCloseCard_id = RD.CmpCloseCard_id
                         )
                    where R.Registry_id IN (".implode(',', json_decode($data['Registry_ids'],1)).")
                    union all
					--Гемодиализ
                    select 'gemo' as \"name\",
                           (
                             select
                             LIMIT
                             from r2.LIMIT_VOL_GEMO_PGG
                             where CODE_MO =:Lpu_id and
                                   YEAR =:YEAR
                             limit 1
                           ) / 12 as \"limit\",
                          COUNT(RD.Evn_id) + 12000 as \"fact\"
                    from r2.RegistryData RD
                         join r2.Registry R on RD.Registry_id = R.Registry_id and not exists (
                                                                                               select *
                                                                                               from r2.v_RegistryError RE
                                                                                               where RE.Evn_id = RD.Evn_id
                         )
                         join LpuUnitSet LG on LG.Lpu_id = R.Lpu_id and LG.LpuUnitSet_Code IN (1106925, 22151, 22159, 3006, 7019, 15016, 16149, 4113, 10023, 22158, 22117, 914, 134)
                    where R.Registry_id IN (".implode(',', json_decode($data['Registry_ids'],1)).")
                    union all
					--Доп. исследования
                    select 'dopis' as \"name\",
                           (
                             select
                             LIMIT
                             from r2.LIMIT_VOL_DOPIS_PGG
                             where CODE_MO =:Lpu_id and
                                   YEAR =:YEAR
                             limit 1
                           ) / 12 as \"limit\",
                          COUNT(RD.Evn_id) + 7000 as \"fact\"
                    from r2.v_RegistryData RD
                         join r2.Registry R on RD.Registry_id = R.Registry_id and not exists (
                                                                                               select *
                                                                                               from r2.v_RegistryError RE
                                                                                               where RE.Evn_id = RD.Evn_id
                         )
                         join LpuUnitSet LD on LD.Lpu_id = R.Lpu_id and LD.LpuUnitSet_Code IN (120, 124, 112, 118, 116, 22137, 22139, 22141, 22145, 4009, 7007, 11009, 6005, 10033, 13006, 14062, 15015, 16039, 22133, 22135, 17003, 22170, 19035)
                    where R.Registry_id IN (".implode(',', json_decode($data['Registry_ids'],1)).")
                    union all
					--Стационар
                    select 'stac' as \"name\",
                           (
                             select
                             LIMIT
                             from r2.LIMIT_VOL_HOSP_PGG
                             where CODE_MO =:Lpu_id and
                                   YEAR =:YEAR
                             limit 1
                           ) / 12 as \"limit\",
                          COUNT(RD.Evn_id) + 2400 as \"fact\"
                    from r2.v_RegistryDataEvnPS RD
                         join r2.Registry R on RD.Registry_id = R.Registry_id and not exists (
                                                                                               select *
                                                                                               from r2.v_RegistryError RE
                                                                                               where RE.Evn_id = RD.Evn_id
                         )
                         join dbo.LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
                         join dbo.LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
                    where LU.LpuUnitType_id = 1 and
                          R.Registry_id IN (".implode(',', json_decode($data['Registry_ids'],1)).")
                    union all
					--Дневной стационар
                    select 'dstac' as \"name\",
                           (
                             select
                             LIMIT
                             from r2.LIMIT_VOL_DHOSP_PGG
                             where CODE_MO =:Lpu_id and
                                   YEAR =:YEAR
                             limit 1
                           ) / 12 as \"limit\",
                          COUNT(RD.Evn_id) + 1100 as \"fact\"
                    from r2.v_RegistryDataEvnPS RD
                         join r2.Registry R on RD.Registry_id = R.Registry_id and not exists (
                                                                                               select *
                                                                                               from r2.v_RegistryError RE
                                                                                               where RE.Evn_id = RD.Evn_id
                         )
                         join dbo.LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
                         join dbo.LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
                    where LU.LpuUnitType_id IN (6, 7, 9) and
                          R.Registry_id IN (".implode(',', json_decode($data['Registry_ids'],1)).")
	   ";
	   
	   
		//echo getDebugSQL($query,  $queryParams);
	   
		$res = $this->db->query($query, $queryParams);

		
		//echo '<pre>' . print_r($res, 1) . '</pre>'; 
		
		if ( is_object($res) ) {
			return $res->result('array');
		} 
		else {
			return false;
		}

	}
 
	 /**
	 * Корректировка объёма
	 */    
	function cutVolumeRegisters($data){
		
		$json_params = json_decode($data['json'],1);
		
		//echo '<pre>' . print_r($json_params, 1) . '</pre>';
		
		if(empty($json_params)){
			return array('success' => false, 'Message' => toUTF('Для корректировки объёмов необходимо выбрать справочник!'));        
		}
		else{
			foreach($json_params as $k=>$v){
				switch($v['name']){
					case 'polka'  : $polka = $v['over']; break;
					case 'smp'    : $smp = $v['over']; break;
					case 'stac'   : $stac = $v['over']; break;
					case 'dstac'  : $dstac = $v['over']; break;
					case 'dopis'  : $dopis = $v['over']; break;
					case 'gemo'   : $gemo = $v['over']; break;
				}
			}
		}
		
		$over_polka = isset($polka) ? (int)$polka : 0;
		$over_smp   = isset($smp)   ? (int)$smp   : 0;
		$over_stac  = isset($stac)  ? (int)$stac : 0;
		$over_dstac = isset($dstac) ? (int)$dstac : 0;
		$over_dopis = isset($dopis) ? (int)$dopis : 0;
		$over_gemo  = isset($gemo)  ? (int)$gemo  : 0; 
		
		//Список id реестров - из которых нужно "кропать" записи             
		$Registry_ids = json_decode($data['Registry_ids'],1); 
		
		if(empty($Registry_ids)){
			return array('success' => false, 'Message' => toUTF('Для корректировки объёмов необходимо указать 1 или более реестров!'));     
		}

		$query = '
			select count(RD.Evn_id) as \"count\" from r2.v_RegistryData RD  where cast(RD.Registry_id as varchar(10)) IN ('.implode(',',$Registry_ids).') 

			-------------------
			-- СНЯТЬ КОММЕНТ --
			-------------------
			--and RD.OrgSmo_id <> NULL
		';

		$res = $this->db->query($query);

		if ( is_object($res) ) {
			$temp = $res->result('array');

			if($temp[0]['count'] == 0){
				return array('success' => false, 'Message' => toUTF('<b>Корректировка не возможна!</b><br/><br/> Возможные причины:<br/><br/> - указанные реестры не содержат ни одной записи'
																							.'<br/> - реестры, в которых не указаны СМО, корректировке не подлежат'));    
			}
			else{
				if ( is_object($res) ) {
					$temp = $res->result('array');
					if(empty($temp)){
							return array('success' => false, 'Message' => toUTF('Реестры, в которых не указаны СМО, корректировке не подлежат!'));      
						}
					else{
							$query = "
								select
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"								
								from r2.p_cutVolumeRegistersUfa(
									Registry_ids := :Registry_ids,
									Over_polka := :over_polka,
									Over_smp := :over_smp,
									Over_stac := :over_stac,
									Over_dstac := :over_dstac,
									Over_dopis := :over_dopis,
									Over_gemo := :over_gemo);
							";
							
							$queryParams = array(
								'Registry_ids'=>implode(',',$Registry_ids),
								'over_polka'  => $over_polka,
								'over_smp'    => $over_smp,
								'over_stac'   => $over_stac,
								'over_dstac'  => $over_dstac,
								'over_dopis'  => $over_dopis,
								'over_gemo'   => $over_gemo
							);
							
							$res = $this->db->query($query, $queryParams);
							
						if ( is_object($res) ) {
								$temp = $res->result('array');
							}    

						}
				}                
			}
			/**
			$Evn_ids = array();
			
			foreach($temp as $k=>$v){
				$Evn_ids[] = $v['Evn_id'];
			}  
			  
			//echo '<pre>' . print_r($Evn_ids,1) . '</pre>';
			//return $Evn_ids;
			//var_dump(!empty($Evn_ids));
			if(!empty($Evn_ids)){
				$query = '
					select RD.Evn_id, RD.Registry_id, RD.RegistryType_id from r2.v_RegistryData RD  where cast(RD.Evn_id as varchar(10)) in('.implode(',',$Evn_ids).');

				';
				
				echo getDebugSQL($query,  $queryParams);
				
				$res = $this->db->query($query);

				if ( is_object($res) ) {
					$temp = $res->result('array');
						echo '<pre>' . print_r($temp,1) . '</pre>';
				}
			}
			else{
				return array('success' => false, 'Message' => toUTF('Указанные реестры не содержат ни одной записи!')); 
			}
			*/
		} 
		else {
			return false;
		} 
			 
		//echo '<pre>' . print_r($json_params,1) . '</pre>';
		//echo '<pre>' . print_r($Registry_ids,1) . '</pre>'; 
		/*
		foreach($json_params as $k=>$v){
		  //Нужно получить все Evn_id по нужным типам реестров + их родственников
		  //При этом по реестрам по определённому периоду
		  switch($k){
			
		  }
		}
		*/
	   
		
		return array('success' => true, 'Message' => toUTF('Объёмы подкорректированы')); 
	}  
    
    /**
     * Task#25768 Ручной запуск МЭК
     */
    function startMek($data){
        //echo '<pre>' . print_r($data,1) . '</pre>';
        $queryParams = array(
            'Registry_id' => $data['Registry_id']
        );   
        
        $query = '
             SELECT r2.p_Registry_List_MEK(Registry_id := :Registry_id);       
        ';
        
		$res = $this->db->query($query, $queryParams);
							
        return array('success' => true, 'Message' => toUTF('<b>МЭК проверки для реестра запущены!</b> <br/>Состояние проверки в вкладке <b>"0.Реестр"</b>, 
                                                                                        <u>необходимо обновление вкладки</u> для просмотра этапов прохождения'));             
   

     }   
	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1:
				$this->MaxEvnField = 'Evn_rid';
			case 14:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryDataSLObject = 'RegistryDataEvnPSSL';
				$this->RegistryDataTempObject = 'RegistryDataTempEvnPS';
				break;

			case 2:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataSLObject = 'RegistryDataSL';
				$this->RegistryDataTempObject = 'RegistryData';
				$this->MaxEvnField = 'Evn_rid';
				break;

			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTempCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				break;

			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTempDisp';
				break;

			case 17:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryDataSLObject = null;
				$this->RegistryDataTempObject = 'RegistryDataTempProf';
				break;

			default:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryDataSLObject = 'RegistryDataSL';
				$this->RegistryDataTempObject = 'RegistryDataTemp';
				break;
		}
	}

	/**
	 *	Проверка вхождения случаев, в которых указано отделение, в реестр
	 *	@task https://redmine.swan.perm.ru/issues/77268
	 *	Перенес для Уфы в региональную модель для учета разных типов реестров
	 */
	function checkLpuSectionInRegistry($data) {
		$filterList = array(
			'R.RegistryStatus_id = 4',
			'COALESCE(RD.RegistryData_deleted, 1) = 1'

		);

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = "LS.LpuUnit_id = :LpuUnit_id";
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filterList[] = "RD.LpuSection_id = :LpuSection_id";
		}

		$query = "
			-- Стационар
            (select R.Registry_Num as \"Registry_Num\",
                    to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
             from {$this->scheme}.v_RegistryDataEvnPS RD
                  left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
                  left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
             where " . implode(' and ', $filterList) . "
             limit 1)
            union all

            -- Поликлиника
            (select R.Registry_Num as \"Registry_Num\",
                    to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
             from {$this->scheme}.v_RegistryData RD
                  left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
                  left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
             where " . implode(' and ', $filterList) . "
             limit 1)

            union all

            -- СМП
            (select R.Registry_Num as \"Registry_Num\",
                    to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
             from {$this->scheme}.v_RegistryDataCmp RD
                  left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
                  left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
             where " . implode(' and ', $filterList) . "
             limit 1)
            union all

            -- ДВН, ДДС, МОН
            (select R.Registry_Num as \"Registry_Num\",
                    to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
             from {$this->scheme}.v_RegistryDataDisp RD
                  left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
                  left join v_LpuSection LS on RD.LpuSection_id = LS.LpuSection_id
             where " . implode(' and ', $filterList) . "
             limit 1)
            union all

            -- ПОВН
            (select R.Registry_Num as \"Registry_Num\",
                    to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
             from {$this->scheme}.v_RegistryDataProf RD
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
	 * Получение данных о счете для выгрузки объединенного реестра в XML
	 */
	protected function _loadRegistrySCHETForXmlUsing($type, $data, $isFinal = false) {
		$queryModificator = ($isFinal === true ? ", OrgSmo_id := :OrgSmo_id, Registry_IsNotInsur := :Registry_IsNotInsur" : "");
		$queryParams = array(
			'Registry_id' => $data['Registry_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null),
			'Registry_IsNotInsur' => (!empty($data['Registry_IsNotInsur']) ? $data['Registry_IsNotInsur'] : null),
		);
		$smoRegistryModificator = ($isFinal === true ? "_SMO" : "");

		$object = $this->_getRegistryObjectName($type);

		$p_schet = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expScet";

		$query = "select {$p_schet} (Registry_id := :Registry_id{$queryModificator})";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		if ( is_object($result) ) {
			$header = $result->result('array');
			if (!empty($header[0])) {
				array_walk_recursive($header[0], 'ConvertFromUTF8ToWin1251', true);
				return array($header[0]);
			}
		}

		return false;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	protected function _loadRegistryDataForXmlUsing($type, $data, $sluchDataFile, $personDataFile, $sluchDataTemplate, $personDataTemplate, $isFinal = false) {
		$queryModificator = ($isFinal === true ? ", OrgSmo_id := :OrgSmo_id, Registry_IsNotInsur := :Registry_IsNotInsur" : "");
		$smoRegistryModificator = ($isFinal === true ? "_SMO" : "");

		$person_field = "ID_PAC";

		$object = $this->_getRegistryObjectName($type);

		$p_pers = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expPac";
		$p_vizit = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expVizit";
		$p_usl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expUsl";

		if ( in_array($type, array(1, 2, 7, 9, 14, 17)) && $isFinal == true ) {
			$p_ds2 = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expDS2";
		}

		if ( in_array($type, array(1, 14)) && $isFinal == true ) {
			$p_ds3 = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expDS3";
		}

		if ( in_array($type, array(7, 9)) && $isFinal == true ) {
			$p_naz = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expNAZ";
		}

		$queryParams = array(
			'Registry_id' => $data['Registry_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null),
			'Registry_IsNotInsur' => (!empty($data['Registry_IsNotInsur']) ? $data['Registry_IsNotInsur'] : null),
		);

		$DS2 = array();
		$DS3 = array();
		$NAZ = array();
		$PACIENT = array();
		$SLUCH = array();
		$USL = array();
		$ZAP = array();

		$altKeys = array(
			 'SL_PODR' => 'PODR'
			,'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'P_OTK_USL' => 'P_OTK'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
			,'VNOV_M_VAL' => 'VNOV_M'
		);

		// посещения
		$query = "
			select {$p_vizit} (Registry_id := :Registry_id{$queryModificator})
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$visits = $result->result('array');
			// привязываем услуги к случаю
			foreach( $visits as $visit )
			{
				if ( !empty($visit['IDCASE']) ) {
					if ( !isset($SLUCH[$visit['IDCASE']]) ) {
						$SLUCH[$visit['IDCASE']] = array();
					}

					$VNOV_M = array();

					if ( !empty($visit['VNOV_M1']) ) {
						$VNOV_M[] = array('VNOV_M_VAL' => $visit['VNOV_M1']);
					}

					if ( !empty($visit['VNOV_M2']) ) {
						$VNOV_M[] = array('VNOV_M_VAL' => $visit['VNOV_M2']);
					}

					$visit['VNOV_M'] = $VNOV_M;

					array_walk_recursive($visit, 'ConvertFromUTF8ToWin1251', true);
					$SLUCH[$visit['IDCASE']][] = $visit;
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
				select {$p_usl} (Registry_id := :Registry_id{$queryModificator})
			";
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$uslugi = $result->result('array');
				$USL = array();
				$maxIDSERV = 0;
				$setIDSERVForClones = false;

				// привязываем услуги к случаю
				foreach( $uslugi as $usluga )
				{
					if ( !isset($USL[$usluga['MaxEvn_id']]) ) {
						$USL[$usluga['MaxEvn_id']] = array();
					}

					array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);

					// @task https://redmine.swan.perm.ru/issues/113994
					// Определяем максимальный IDSERV для указания уникальных значений у услуг-клонов
					if ( $usluga['IDSERV'] > $maxIDSERV ) {
						$maxIDSERV = $usluga['IDSERV'];
					}

					// @task https://redmine.swan.perm.ru/issues/113994
					if ( $type == 14 && $usluga['CODE_USL'] == 'A16.12.004.009' && $usluga['KOL_USL'] > 1 ) {
						// Указываем признак необходимости проставить IDSERV для услуг-клонов
						$setIDSERVForClones = true;

						// Запоминаем количество услуг
						$KOL_USL = $usluga['KOL_USL'];

						// Указываем у услуги KOL_USL = 1
						$usluga['KOL_USL'] = 1;

						// Первая услуга с заполненным IDSERV
						$USL[$usluga['MaxEvn_id']][] = $usluga;

						// Для услуг-клонов обнуляем IDSERV
						$usluga['IDSERV'] = 0;

						// Клонируем услуги
						for ( $j = 2; $j <= $KOL_USL; $j++ ) {
							$USL[$usluga['MaxEvn_id']][] = $usluga;
						}
					}
					else {
						$USL[$usluga['MaxEvn_id']][] = $usluga;
					}
				}

				unset($uslugi);

				// @task https://redmine.swan.perm.ru/issues/113994
				// Указываем уникальные значения IDSERV у услуг-клонов
				if ( $setIDSERVForClones === true ) {
					foreach ( $USL as $MaxEvn_id => $uslugi ) {
						foreach ( $uslugi as $key => $usluga ) {
							if ( empty($usluga['IDSERV']) ) {
								$maxIDSERV++;
								$USL[$MaxEvn_id][$key]['IDSERV'] = $maxIDSERV;
							}
						}
					}
				}
			}
			else {
				return false;
			}
		}

		// диагнозы (DS2)
		if ( !empty($p_ds2) ) {
			$query = "
				select {$p_ds2} (Registry_id := :Registry_id{$queryModificator})
			";
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$diag2 = $result->result('array');
				$DS2 = array();

				// привязываем диагнозы к случаю
				foreach( $diag2 as $row ) {
					if ( !isset($DS2[$row['Evn_id']]) ) {
						$DS2[$row['Evn_id']] = array();
					}

					array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
					$DS2[$row['Evn_id']][] = $row;
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
				select {$p_ds3} (Registry_id := :Registry_id{$queryModificator})
			";
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$diag3 = $result->result('array');
				$DS3 = array();

				// привязываем диагнозы к случаю
				foreach( $diag3 as $row ) {
					if ( !isset($DS3[$row['Evn_id']]) ) {
						$DS3[$row['Evn_id']] = array();
					}

					array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
					$DS3[$row['Evn_id']][] = $row;
				}

				unset($diag3);
			}
			else {
				return false;
			}
		}

		// назначения (NAZ)
		if ( !empty($p_naz) ) {
			$query = "
				select {$p_naz} (Registry_id := :Registry_id{$queryModificator})
			";
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$naz = $result->result('array');
				$NAZR = array();

				// привязываем назначения к случаю
				foreach( $naz as $row ) {
					if ( !isset($NAZR[$row['Evn_id']]) ) {
						$NAZR[$row['Evn_id']] = array();
					}

					array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
					$NAZR[$row['Evn_id']][] = $row;
				}

				unset($diag2);
			}
			else {
				return false;
			}
		}

		// люди
		$query = "
			select {$p_pers} (Registry_id := :Registry_id{$queryModificator})
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$person = $result->result('array');
			$PACIENT = array();
			$netValue = toAnsi('НЕТ', true);
			// привязываем персона к случаю
			foreach( $person as $pers ) {
				if ( !empty($pers[$person_field]) ) {
					array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);

					$pers['DOST'] = array();
					$pers['DOST_P'] = array();

					if ( $pers['NOVOR'] == '0' ) {
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

					$PACIENT[$pers[$person_field]] = $pers;
				}
			}
			unset($person);
		}
		else {
			return false;
		}

		// Основной цикл сборки
		foreach ( $SLUCH as $key => $value ) {
			foreach ( $value as $k => $val ) {
				if ( isset($USL[$key]) ) {
					$value[$k]['USL'] = $USL[$key];
				}
				else {
					$value[$k]['USL'] = $this->getEmptyUslugaXmlRow();
				}

				if ( isset($DS2[$val['IDCASE']]) ) {
					$value[$k]['DS2_DATA'] = $DS2[$val['IDCASE']];
				}
				else if ( !empty($value[$k]['DS2']) ) {
					$value[$k]['DS2_DATA'] = array(array('DS2' => $value[$k]['DS2']));
				}
				else {
					$value[$k]['DS2_DATA'] = array();
				}

				if ( isset($DS3[$val['IDCASE']]) ) {
					$value[$k]['DS3_DATA'] = $DS3[$val['IDCASE']];
				}
				else if ( !empty($value[$k]['DS3']) ) {
					$value[$k]['DS3_DATA'] = array(array('DS3' => $value[$k]['DS3']));
				}
				else {
					$value[$k]['DS3_DATA'] = array();
				}

				/*$NAZR[$val['IDCASE']] = array(
					array('NAZR' => 1, 'NAZ_SP' => 1000, 'NAZ_V' => null, 'NAZ_PMP' => null, 'NAZ_PK' => null),
					array('NAZR' => 2, 'NAZ_SP' => 2000, 'NAZ_V' => null, 'NAZ_PMP' => null, 'NAZ_PK' => null),
					array('NAZR' => 3, 'NAZ_SP' => null, 'NAZ_V' => 7, 'NAZ_PMP' => null, 'NAZ_PK' => null),
					array('NAZR' => 4, 'NAZ_SP' => null, 'NAZ_V' => null, 'NAZ_PMP' => 1, 'NAZ_PK' => null),
					array('NAZR' => 5, 'NAZ_SP' => null, 'NAZ_V' => null, 'NAZ_PMP' => 500, 'NAZ_PK' => null),
					array('NAZR' => 6, 'NAZ_SP' => null, 'NAZ_V' => null, 'NAZ_PMP' => null, 'NAZ_PK' => 600)
				);*/

				$value[$k]['NAZR_DATA'] = array();
				$value[$k]['NAZ_SP_DATA'] = array();
				$value[$k]['NAZ_V_DATA'] = array();
				$value[$k]['NAZ_PMP'] = null;
				$value[$k]['NAZ_PK'] = null;

				if ( isset($NAZR[$val['IDCASE']]) ) {
					foreach ( $NAZR[$val['IDCASE']] as $row ) {
						$value[$k]['NAZR_DATA'][] = array('NAZR' => $row['NAZR']);

						if ( !empty($row['NAZ_SP']) ) {
							$value[$k]['NAZ_SP_DATA'][] = array('NAZ_SP' => $row['NAZ_SP']);
						}

						if ( !empty($row['NAZ_V']) ) {
							$value[$k]['NAZ_V_DATA'][] = array('NAZ_V' => $row['NAZ_V']);
						}

						if ( in_array($row['NAZR'], array(4, 5)) ) {
							$value[$k]['NAZ_PMP'] = $row['NAZ_PMP'];
						}
						else if ( in_array($row['NAZR'], array(6)) ) {
							$value[$k]['NAZ_PK'] = $row['NAZ_PK'];
						}
					}
				}

				unset($value[$k]['DS2']);
				unset($value[$k]['DS3']);

				$OS_SLUCH = array();

				if ( !empty($data['ZAP'][$key]['PACIENT'][0]['OS_SLUCH']) ) {
					$OS_SLUCH[] = array('OS_SLUCH_VAL' => $data['ZAP'][$key]['PACIENT'][0]['OS_SLUCH']);
				}

				if ( !empty($data['ZAP'][$key]['PACIENT'][0]['OS_SLUCH1']) ) {
					$OS_SLUCH[] = array('OS_SLUCH_VAL' => $data['ZAP'][$key]['PACIENT'][0]['OS_SLUCH1']);
				}

				$value[$k]['OS_SLUCH'] = $OS_SLUCH;
			}

			$this->zapCnt++;

			$ZAP[$key] = array(
				'N_ZAP' => $this->zapCnt,
				'PR_NOV' => (!empty($val['PR_NOV']) ? $val['PR_NOV'] : 0),
				'PACIENT' => (array_key_exists($key, $PACIENT) ? array($PACIENT[$key]) : array()),
				'SLUCH' => $value,
			);

			if ( count($ZAP) >= 1000 ) {
				// пишем в файл
				array_walk_recursive($ZAP, 'RegistryUfa_model::encodeForXmlExport');
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP), true, false, $altKeys, false);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили 1000 записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($sluchDataFile, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP);
				$ZAP = array();
			}
		}

		if ( count($ZAP) > 0 ) {
			// пишем в файл
			array_walk_recursive($ZAP, 'RegistryUfa_model::encodeForXmlExport');
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP), true, false, $altKeys, false);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . count($ZAP) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($sluchDataFile, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP);
		}

		unset($DS2);
		unset($DS3);
		unset($NAZ);
		unset($USL);

		$toFile = array();

		foreach ( $PACIENT as $onepac ) {
			$toFile[] = $onepac;

			if ( count($toFile) >= 1000 ) {
				// пишем в файл
				array_walk_recursive($toFile, 'RegistryUfa_model::encodeForXmlExport');
				$xml = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $toFile), true, false, array(), false);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($personDataFile, $xml, FILE_APPEND);
				unset($xml);
				unset($toFile);
				$toFile = array();
			}
		}

		if ( count($toFile) > 0 ) {
			// пишем в файл
			array_walk_recursive($toFile, 'RegistryUfa_model::encodeForXmlExport');
			$xml = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $toFile), true, false, array(), false);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($personDataFile, $xml, FILE_APPEND);
			unset($xml);
			unset($toFile);
		}

		unset($PACIENT);

		$response = array();
		$response['persCnt'] = $this->persCnt;
		$response['zapCnt'] = $this->zapCnt;

		return $response;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	protected function _loadRegistryDataForXmlUsingNew($type, $data, $sluchDataFile, $personDataFile, $sluchDataTemplate, $personDataTemplate, $isFinal = false) {
		$queryModificator = ($isFinal === true ? ", OrgSmo_id := :OrgSmo_id, Registry_IsNotInsur := :Registry_IsNotInsur" : "");
		$smoRegistryModificator = ($isFinal === true ? "_SMO" : "");

		$object = $this->_getRegistryObjectName($type);

		$p_pers = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expPac_2018";
		$p_sl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expVizit_2018";
		$p_usl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expUsl_2018";
		$p_zsl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expSL_2018";

		if ( in_array($type, array(1, 2, 7, 9, 14, 17)) && $isFinal == true ) {
			$p_ds2 = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expDS2_2018";
		}

		if ( in_array($type, array(1, 14)) && $isFinal == true ) {
			$p_ds3 = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expDS3";
		}

		if ( in_array($type, array(1)) && $isFinal == true ) {
			$p_kslp = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expKSLP_2018";
		}

		if ( in_array($type, array(7, 9, 17)) && $isFinal == true ) {
			$p_naz = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expNAZ_2018";
		}

		$queryParams = array(
			'Registry_id' => $data['Registry_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null),
			'Registry_IsNotInsur' => (!empty($data['Registry_IsNotInsur']) ? $data['Registry_IsNotInsur'] : null)
		);

		$DS2 = array();
		$DS3 = array();
		$KSG_KPG_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL');
		$NAZ = array();
		$PACIENT = array();
		$SL = array();
		$SL_KOEF = array();
		$USL = array();
		$ZAP = array();

		$altKeys = array(
			 'SL_PODR' => 'PODR'
			,'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'P_OTK_USL' => 'P_OTK'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
			,'VNOV_M_VAL' => 'VNOV_M'
		);

		$maxIDSERV = 0;
		$netValue = toAnsi('НЕТ', true);
		$setIDSERVForClones = false;

		// Сведения о пациентах
		$query = "select {$p_pers} (Registry_id := :Registry_id{$queryModificator})";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultPAC = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultPAC) ) {
			return false;
		}

		// Сведения о законченных случаях (Z_SL)
		$query = "select {$p_zsl} (Registry_id := :Registry_id{$queryModificator})";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultZSL = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultZSL) ) {
			return false;
		}

		// Сведения о случаях (SL)
		$query = "select {$p_sl} (Registry_id := :Registry_id{$queryModificator})";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultSL = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultSL) ) {
			return false;
		}

		// Сведения об услугах (USL)
		$query = "select {$p_usl} (Registry_id := :Registry_id{$queryModificator})";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultUSL = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultUSL) ) {
			return false;
		}

		// Диагнозы (DS2)
		if ( !empty($p_ds2) ) {
			$query = "select {$p_ds2} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS2 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS2) ) {
				return false;
			}

			// Массив $DS2
			while ( $row = $resultDS2->_fetch_assoc() ) {
				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS3)
		if ( !empty($p_ds3) ) {
			$query = "select {$p_ds3} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS3 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS3) ) {
				return false;
			}

			// Массив $DS3
			while ( $row = $resultDS3->_fetch_assoc() ) {
				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "select {$p_kslp} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultKSLP = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultKSLP) ) {
				return false;
			}

			// Массив $SL_KOEF
			while ( $row = $resultKSLP->_fetch_assoc() ) {
				if ( !isset($SL_KOEF[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Назначения (NAZ)
		if ( !empty($p_naz) ) {
			$query = "select {$p_naz} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultNAZ = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultNAZ) ) {
				return false;
			}

			// Массив $NAZ
			while ( $row = $resultNAZ->_fetch_assoc() ) {
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// Массив $PACIENT
		while ( $row = $resultPAC->_fetch_assoc() ) {
			if ( empty($row['MaxEvn_id']) ) {
				continue;
			}

			$this->persCnt++;

			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

			$row['DOST'] = array();
			$row['DOST_P'] = array();

			if ( $row['NOVOR'] == '0' ) {
				if ( empty($row['FAM']) ) {
					$row['DOST'][] = array('DOST_VAL' => 2);
				}

				if ( empty($row['IM']) ) {
					$row['DOST'][] = array('DOST_VAL' => 3);
				}

				if ( empty($row['OT']) || mb_strtoupper($row['OT'], 'windows-1251') == $netValue ) {
					$row['DOST'][] = array('DOST_VAL' => 1);
				}
			}
			else {
				if ( empty($row['FAM_P']) ) {
					$row['DOST_P'][] = array('DOST_P_VAL' => 2);
				}

				if ( empty($row['IM_P']) ) {
					$row['DOST_P'][] = array('DOST_P_VAL' => 3);
				}

				if ( empty($row['OT_P']) || mb_strtoupper($row['OT_P'], 'windows-1251') == $netValue ) {
					$row['DOST_P'][] = array('DOST_P_VAL' => 1);
				}
			}

			$PACIENT[$row['MaxEvn_id']] = $row;
		}

		// Массив $SL
		while ( $row = $resultSL->_fetch_assoc() ) {
			if ( empty($row['MaxEvn_id']) ) {
				continue;
			}

			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

			if ( !isset($SL[$row['MaxEvn_id']]) ) {
				$SL[$row['MaxEvn_id']] = array();
			}

			$row['CODE_MES1_DATA'] = array();
			$row['DS2_DATA'] = array();
			$row['DS2_N_DATA'] = array();
			$row['DS3_DATA'] = array();
			$row['KSG_KPG_DATA'] = array();
			$row['NAZ_DATA'] = array();
			$row['ONK_SL_DATA'] = array();
			$row['SANK'] = array();
			$row['USL'] = array();

			if ( !empty($row['VER_KSG']) ) {
				$KSG_KPG_DATA = array();

				foreach ( $KSG_KPG_FIELDS as $index ) {
					$KSG_KPG_DATA[$index] = $row[$index];
					unset($row[$index]);
				}

				$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

				$row['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
			}

			$SL[$row['MaxEvn_id']][] = $row;
		}

		// Массив $USL
		while ( $row = $resultUSL->_fetch_assoc() ) {
			if ( !isset($USL[$row['Evn_id']]) ) {
				$USL[$row['Evn_id']] = array();
			}

			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

			// @task https://redmine.swan.perm.ru/issues/113994
			// Определяем максимальный IDSERV для указания уникальных значений у услуг-клонов
			if ( $row['IDSERV'] > $maxIDSERV ) {
				$maxIDSERV = $row['IDSERV'];
			}

			$row['NAPR_DATA'] = array();
			$row['ONK_USL_DATA'] = array();

			// @task https://redmine.swan.perm.ru/issues/113994
			if ( $type == 14 && $row['CODE_USL'] == 'A16.12.004.009' && $row['KOL_USL'] > 1 ) {
				// Указываем признак необходимости проставить IDSERV для услуг-клонов
				$setIDSERVForClones = true;

				// Запоминаем количество услуг
				$KOL_USL = $row['KOL_USL'];

				// Указываем у услуги KOL_USL = 1
				$row['KOL_USL'] = 1;

				// Первая услуга с заполненным IDSERV
				$USL[$row['Evn_id']][] = $row;

				// Для услуг-клонов обнуляем IDSERV
				$row['IDSERV'] = 0;

				// Клонируем услуги
				for ( $j = 2; $j <= $KOL_USL; $j++ ) {
					$USL[$row['Evn_id']][] = $row;
				}
			}
			else {
				$USL[$row['Evn_id']][] = $row;
			}
		}

		// @task https://redmine.swan.perm.ru/issues/113994
		// Указываем уникальные значения IDSERV у услуг-клонов
		if ( $setIDSERVForClones === true ) {
			foreach ( $USL as $Evn_id => $uslugi ) {
				foreach ( $uslugi as $key => $usluga ) {
					if ( empty($usluga['IDSERV']) ) {
						$maxIDSERV++;
						$USL[$Evn_id][$key]['IDSERV'] = $maxIDSERV;
					}
				}
			}
		}

		// SL к Z_SL (ака ZAP)
		$indexDS2 = 'DS2_DATA';

		if ( in_array($type, array(7, 9, 17)) ) {
			$indexDS2 = 'DS2_N_DATA';
		}

		// Цепляем данные к $SL
		foreach ( $SL as $key => $arraySL ) {
			foreach ( $arraySL as $k => $array ) {
				if ( isset($USL[$array['Evn_id']]) ) {
					$arraySL[$k]['USL'] = $USL[$array['Evn_id']];
					unset($USL[$array['Evn_id']]);
				}
				else {
					$arraySL[$k]['USL'] = $this->getEmptyUslugaXmlRow();
				}

				if ( isset($DS2[$array['Evn_id']]) ) {
					$arraySL[$k][$indexDS2] = $DS2[$array['Evn_id']];
					unset($DS2[$array['Evn_id']]);
				}
				else if ( !empty($arraySL[$k]['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$arraySL[$k][$indexDS2] = array(array('DS2' => $arraySL[$k]['DS2']));
				}

				if ( isset($DS3[$array['Evn_id']]) ) {
					$arraySL[$k]['DS3_DATA'] = $DS3[$array['Evn_id']];
					unset($DS3[$array['Evn_id']]);
				}
				else if ( !empty($arraySL[$k]['DS3']) ) {
					$arraySL[$k]['DS3_DATA'] = array(array('DS3' => $arraySL[$k]['DS3']));
				}

				if ( isset($SL_KOEF[$array['Evn_id']]) && count($arraySL[$k]['KSG_KPG_DATA']) > 0 ) {
					$arraySL[$k]['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$array['Evn_id']];
					unset($SL_KOEF[$array['Evn_id']]);
				}

				if ( isset($NAZ[$array['Evn_id']]) ) {
					$arraySL[$k]['NAZ_DATA'] = $NAZ[$array['Evn_id']];
					unset($NAZ[$array['Evn_id']]);
				}

				if ( array_key_exists('DS2', $arraySL[$k]) ) {
					unset($arraySL[$k]['DS2']);
				}

				if ( array_key_exists('DS3', $arraySL[$k]) ) {
					unset($arraySL[$k]['DS3']);
				}
			}

			$SL[$key] = $arraySL;
		}

		// Основной цикл сборки
		while ( $oneZSL = $resultZSL->_fetch_assoc() ) {
			if ( empty($oneZSL['MaxEvn_id']) ) {
				continue;
			}
			else if ( !array_key_exists($oneZSL['MaxEvn_id'], $SL) ) {
				continue;
			}

			$key = $oneZSL['MaxEvn_id'];

			$this->zapCnt++;

			$OS_SLUCH = array();
			$VNOV_M = array();

			if ( !empty($PACIENT[$key]['OS_SLUCH']) ) {
				$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT[$key]['OS_SLUCH']);
			}

			if ( !empty($row['OS_SLUCH1']) ) {
				$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT[$key]['OS_SLUCH1']);
			}

			unset($PACIENT[$key]['OS_SLUCH']);
			unset($PACIENT[$key]['OS_SLUCH1']);

			if ( !empty($oneZSL['VNOV_M1']) ) {
				$VNOV_M[] = array('VNOV_M_VAL' => $oneZSL['VNOV_M1']);
			}

			if ( !empty($oneZSL['VNOV_M2']) ) {
				$VNOV_M[] = array('VNOV_M_VAL' => $oneZSL['VNOV_M2']);
			}

			$oneZSL['OS_SLUCH_DATA'] = $OS_SLUCH;
			$oneZSL['VNOV_M_DATA'] = $VNOV_M;
			$oneZSL['SL'] = $SL[$key];

			unset($SL[$key]);

			$oneZSL['N_ZAP'] = $this->zapCnt;
			$oneZSL['PACIENT'] = (array_key_exists($key, $PACIENT) ? array($PACIENT[$key]) : array());

			$ZAP[$key] = $oneZSL;

			if ( count($ZAP) >= 1000 ) {
				// пишем в файл
				array_walk_recursive($ZAP, 'RegistryUfa_model::encodeForXmlExport');
				$parseTimeStart = time();
				$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP), true, false, $altKeys, false);
				$parseTimeFinish = time();
				$this->textlog->add('Распарсили 1000 записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($sluchDataFile, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP);
				$ZAP = array();
			}
		}

		if ( count($ZAP) > 0 ) {
			// пишем в файл
			array_walk_recursive($ZAP, 'RegistryUfa_model::encodeForXmlExport');
			$parseTimeStart = time();
			$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP), true, false, $altKeys, false);
			$parseTimeFinish = time();
			$this->textlog->add('Распарсили ' . count($ZAP) . ' записей за ' . ($parseTimeFinish - $parseTimeStart) . ' секунд');
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($sluchDataFile, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP);
		}

		unset($DS2);
		unset($DS3);
		unset($NAZ);
		unset($SL);
		unset($SL_KOEF);
		unset($USL);

		$toFile = array();

		foreach ( $PACIENT as $onepac ) {
			$toFile[] = $onepac;

			if ( count($toFile) >= 1000 ) {
				// пишем в файл
				array_walk_recursive($toFile, 'RegistryUfa_model::encodeForXmlExport');
				$xml = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $toFile), true, false, array(), false);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($personDataFile, $xml, FILE_APPEND);
				unset($xml);
				unset($toFile);
				$toFile = array();
			}
		}

		if ( count($toFile) > 0 ) {
			// пишем в файл
			array_walk_recursive($toFile, 'RegistryUfa_model::encodeForXmlExport');
			$xml = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $toFile), true, false, array(), false);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($personDataFile, $xml, FILE_APPEND);
			unset($xml);
			unset($toFile);
		}

		unset($PACIENT);

		$response = array();
		$response['persCnt'] = $this->persCnt;
		$response['zapCnt'] = $this->zapCnt;

		return $response;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML с помощью функций БД
	 */
	protected function _loadRegistryDataForXmlByFunc($type, $data, $sluchDataFile, $personDataFile, $sluchDataTemplate, $personDataTemplate, $isFinal = false) {
		$object = $this->_getRegistryObjectName($type);
		$queryModificator = ($isFinal === true ? ", OrgSmo_id := :OrgSmo_id, Registry_IsNotInsur := :Registry_IsNotInsur" : "");
		$execModificator = ($isFinal === true ? ", :OrgSmo_id, :Registry_IsNotInsur" : "");
		$smoRegistryModificator = ($isFinal === true ? "_SMO" : "");

		$postfix = "_2018_f";
		if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
			$postfix = "_2018_bud";
		}

		$fn_pers = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expPac".$postfix;
		$fn_sl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expVizit".$postfix;
		$fn_usl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expUsl".$postfix;
		$fn_zsl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expSL".$postfix;

		if ( in_array($type, array(1, 2, 7, 9, 14, 17)) ) {
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$p_ds2 = $this->scheme . ".p_Registry_" . $object . $smoRegistryModificator . "_expDS2_2018_bud";
			} else {
				$p_ds2 = $this->scheme . ".p_Registry_" . $object . $smoRegistryModificator . "_expDS2_2018";
			}
		}

		if ( in_array($type, array(1, 14)) ) {
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$p_ds3 = $this->scheme . ".p_Registry_" . $object . $smoRegistryModificator . "_expDS3_bud";
			} else {
				$p_ds3 = $this->scheme . ".p_Registry_" . $object . $smoRegistryModificator . "_expDS3";
			}
		}

		if (!in_array($data['PayType_SysNick'], array('bud', 'fbud')) && in_array($type, array(1))) {
			$p_kslp = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expKSLP_2018";
			$fn_crit = $this->scheme . ".p_Registry_" . $object . $smoRegistryModificator . "_expCRIT" . $postfix;
		}

		if ( in_array($type, array(7, 9, 17)) ) {
			$p_naz = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expNAZ_2018";
		}

		if (!in_array($data['PayType_SysNick'], array('bud', 'fbud')) && in_array($type, array(1, 2, 14))) {
			$fn_bdiag = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expBDIAG".$postfix;
			$fn_bprot = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expBPROT".$postfix;
			$fn_napr = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expNAPR".$postfix;
			$fn_onkousl = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expONKOUSL".$postfix;
			$fn_cons = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expCONS".$postfix;
			$fn_lek_pr = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expLEK_PR".$postfix;
		}

		if (!in_array($data['PayType_SysNick'], array('bud', 'fbud')) && in_array($type, array(6))) {
			$fn_cons = $this->scheme.".p_Registry_".$object.$smoRegistryModificator."_expCONS".$postfix;
		}

		$queryParams = array(
			'Registry_id' => $data['Registry_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null),
			'Registry_IsNotInsur' => (!empty($data['Registry_IsNotInsur']) ? $data['Registry_IsNotInsur'] : null),
		);

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
		$SL_KOEF = array();
		$USL = array();

		$KSG_KPG_FIELDS = array('N_KSG', 'VER_KSG', 'KSG_PG', 'N_KPG', 'KOEF_Z', 'KOEF_UP', 'BZTSZ', 'KOEF_D', 'KOEF_U', 'DKK1', 'DKK2', 'SL_K', 'IT_SL');
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ', 'SOD', 'K_FR', 'WEI', 'HEI', 'BSA');

		$altKeys = array(
			 'SL_PODR' => 'PODR'
			,'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'P_OTK_USL' => 'P_OTK'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DET_USL' => 'DET'
			,'TARIF_USL' => 'TARIF'
			,'PRVS_USL' => 'PRVS'
			,'VNOV_M_VAL' => 'VNOV_M'
		);

		$indexDS2 = 'DS2_DATA';
		$maxIDSERV = 0;
		$netValue = toAnsi('НЕТ', true);
		$setIDSERVForClones = false;

		if ( in_array($type, array(7, 9, 17)) ) {
			$indexDS2 = 'DS2_N_DATA';
		}

		// Диагнозы (DS2)
		if ( !empty($p_ds2) ) {
			$query = "select {$p_ds2} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS2 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS2) ) {
				return false;
			}

			// Массив $DS2
			while ( $row = $resultDS2->_fetch_assoc() ) {
				if ( !isset($DS2[$row['Evn_id']]) ) {
					$DS2[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$DS2[$row['Evn_id']][] = $row;
			}
		}

		// Диагнозы (DS3)
		if ( !empty($p_ds3) ) {
			$query = "select {$p_ds3} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultDS3 = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultDS3) ) {
				return false;
			}

			// Массив $DS3
			while ( $row = $resultDS3->_fetch_assoc() ) {
				if ( !isset($DS3[$row['Evn_id']]) ) {
					$DS3[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$DS3[$row['Evn_id']][] = $row;
			}
		}

		// КСЛП (SL_KOEF)
		if ( !empty($p_kslp) ) {
			$query = "select {$p_kslp} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultKSLP = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultKSLP) ) {
				return false;
			}

			// Массив $SL_KOEF
			while ( $row = $resultKSLP->_fetch_assoc() ) {
				if ( !isset($SL_KOEF[$row['Evn_id']]) ) {
					$SL_KOEF[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$SL_KOEF[$row['Evn_id']][] = $row;
			}
		}

		// Назначения (NAZ)
		if ( !empty($p_naz) ) {
			$query = "select {$p_naz} (Registry_id := :Registry_id{$queryModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$resultNAZ = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($resultNAZ) ) {
				return false;
			}

			// Массив $NAZ
			while ( $row = $resultNAZ->_fetch_assoc() ) {
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// Диагностический блок (BDIAG)
		if ( !empty($fn_bdiag) ) {
			$query = "select * from {$fn_bdiag} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BDIAG
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($BDIAG[$row['Evn_id']]) ) {
					$BDIAG[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$BDIAG[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об имеющихся противопоказаниях и отказах (BPROT)
		if ( !empty($fn_bprot) ) {
			$query = "select * from {$fn_bprot} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $BPROT
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// Направления (NAPR)
		if ( !empty($fn_napr) ) {
			$query = "select * from {$fn_napr} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $NAPR
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		// Критерии
		if ( !empty($fn_crit) ) {
			$query = "select * from {$fn_crit} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив CRIT
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($CRIT[$row['Evn_id']]) ) {
					$CRIT[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				$CRIT[$row['Evn_id']][] = array(
					'CRIT' => $row['CRIT'],
				);
			}
		}

		// Сведения о проведении консилиума (CONS)
		if ( !empty($fn_cons) ) {
			$query = "select * from {$fn_cons} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $CONS
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if ( !empty($fn_onkousl) ) {
			$query = "select * from {$fn_onkousl} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $ONKOUSL
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($ONKOUSL[$row['Evn_id']]) ) {
					$ONKOUSL[$row['Evn_id']] = array();
				}

				$row['LEK_PR_DATA'] = array();

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$ONKOUSL[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($fn_lek_pr) ) {
			$query = "select * from {$fn_lek_pr} (:Registry_id{$execModificator})";
			$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
			$queryResult = $this->db->query($query, $queryParams);
			$this->textlog->add('Выполнено');
			if ( !is_object($queryResult) ) {
				return false;
			}

			// Массив $LEK_PR
			while ( $row = $queryResult->_fetch_assoc() ) {
				if ( !isset($LEK_PR[$row['EvnUsluga_id']]) ) {
					$LEK_PR[$row['EvnUsluga_id']] = array();
				}

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Услуги (USL)
		$query = "select * from {$fn_usl}(:Registry_id{$execModificator})";
		$this->textlog->add('Запуск ' . getDebugSQL($query, $queryParams));
		$resultUSL = $this->db->query($query, $queryParams);
		$this->textlog->add('Выполнено');
		if ( !is_object($resultUSL) ) {
			return false;
		}

		// Массив $USL
		while ( $row = $resultUSL->_fetch_assoc() ) {
			if ( !isset($USL[$row['Evn_id']]) ) {
				$USL[$row['Evn_id']] = array();
			}

			// @task https://redmine.swan.perm.ru/issues/113994
			// Определяем максимальный IDSERV для указания уникальных значений у услуг-клонов
			if ( $row['IDSERV'] > $maxIDSERV ) {
				$maxIDSERV = $row['IDSERV'];
			}

			array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

			// @task https://redmine.swan.perm.ru/issues/113994
			if ( $type == 14 && $row['CODE_USL'] == 'A16.12.004.009' && $row['KOL_USL'] > 1 ) {
				// Указываем признак необходимости проставить IDSERV для услуг-клонов
				$setIDSERVForClones = true;

				// Запоминаем количество услуг
				$KOL_USL = $row['KOL_USL'];

				// Указываем у услуги KOL_USL = 1
				$row['KOL_USL'] = 1;

				// Первая услуга с заполненным IDSERV
				$USL[$row['Evn_id']][] = $row;

				// Для услуг-клонов обнуляем IDSERV
				$row['IDSERV'] = 0;

				// Клонируем услуги
				for ( $j = 2; $j <= $KOL_USL; $j++ ) {
					$USL[$row['Evn_id']][] = $row;
				}
			}
			else {
				$USL[$row['Evn_id']][] = $row;
			}
		}

		// @task https://redmine.swan.perm.ru/issues/113994
		// Указываем уникальные значения IDSERV у услуг-клонов
		if ( $setIDSERVForClones === true ) {
			foreach ( $USL as $Evn_id => $uslugi ) {
				foreach ( $uslugi as $key => $usluga ) {
					if ( empty($usluga['IDSERV']) ) {
						$maxIDSERV++;
						$USL[$Evn_id][$key]['IDSERV'] = $maxIDSERV;
					}
				}
			}
		}

		// 2. джойним сразу посещения + услуги + пациенты и гребем постепенно то что получилось, сразу записывая в файл
		$result = $this->db->query("
			WITH zsl AS (
                        select *
                        from {$fn_zsl}(:Registry_id{$execModificator})),
            sl AS (            
                        select *
                        from {$fn_sl}(:Registry_id{$execModificator})),
            pers AS (            
                        select *
                        from {$fn_pers}(:Registry_id{$execModificator}))
            select null as \"fields_part_1\",
                   z.*,
                   z.MaxEvn_id as \"MaxEvn_zid\",
                   null as \"fields_part_2\",
                   s.*,
                   s.Evn_id as \"Evn_sid\",
                   null as \"fields_part_3\",
                   p.*
            from zsl z
                 inner join sl s on s.MaxEvn_id = z.MaxEvn_id
                 inner join pers p on p.MaxEvn_id = z.MaxEvn_id
            order by s.MaxEvn_id,
                     s.Evn_id
		", $queryParams, true);

		if ( !is_object($result) ) {
			return false;
		}

		$ZAP_ARRAY = array();
		$PACIENT_ARRAY = array();

		$recKeys = array(); // ключи для данных

		$prevID_PAC = null;

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

			$SL['Evn_id'] = $one_rec['Evn_sid'];

			// если нагребли больше 100 записей и предыдущий пациент был другим, то записываем всё что получилось в файл.
			if ( count($ZAP_ARRAY) >= 100 && $PACIENT['ID_PAC'] != $prevID_PAC ) {
				// пишем в файл случаи
				array_walk_recursive($ZAP_ARRAY, 'RegistryUfa_model::encodeForXmlExport');
				$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys, false);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($sluchDataFile, $xml, FILE_APPEND);
				unset($ZAP_ARRAY);
				$ZAP_ARRAY = array();
				unset($xml);

				// пишем в файл пациентов
				array_walk_recursive($PACIENT_ARRAY, 'RegistryUfa_model::encodeForXmlExport');
				$xml_pers = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $PACIENT_ARRAY), true, false, array(), false);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($personDataFile, $xml_pers, FILE_APPEND);
				unset($PACIENT_ARRAY);
				$PACIENT_ARRAY = array();
				unset($xml_pers);
			}

			$prevID_PAC = $PACIENT['ID_PAC'];

			if ( isset($ZAP_ARRAY[$zsl_key]) ) {
				// если уже есть законченный случай, значит добавляем в него SL
				$SL['CODE_MES1_DATA'] = array();
				$SL['CONS_DATA'] = array();
				$SL['DS2_DATA'] = array();
				$SL['DS2_N_DATA'] = array();
				$SL['DS3_DATA'] = array();
				$SL['KSG_KPG_DATA'] = array();
				$SL['NAPR_DATA'] = array();
				$SL['NAZ_DATA'] = array();
				$SL['ONK_SL_DATA'] = array();
				$SL['SANK'] = array();
				$SL['USL'] = array();

				$KSG_KPG_DATA = array();
				$ONK_SL_DATA = array();

				$onkDS2 = false;

				if ( isset($USL[$sl_key]) ) {
					$SL['USL'] = $USL[$sl_key];
					unset($USL[$sl_key]);
				}
				else {
					$SL['USL'] = $this->getEmptyUslugaXmlRow();
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL[$indexDS2] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}
				else if ( !empty($SL['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$SL[$indexDS2] = array(array('DS2' => $SL['DS2']));
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}
				else if ( !empty($SL['DS3']) ) {
					$SL['DS3_DATA'] = array(array('DS3' => $SL['DS3']));
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ( !empty($SL['VER_KSG']) ) {
					foreach ( $KSG_KPG_FIELDS as $index ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}

					$KSG_KPG_DATA['CRIT_DATA'] = array();
					$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

					$SL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
				}

				if ( isset($CRIT[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['CRIT_DATA'] = $CRIT[$sl_key];
					unset($CRIT[$sl_key]);
				}

				if ( isset($SL_KOEF[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				if ( count($SL[$indexDS2]) > 0 ) {
					foreach ( $SL[$indexDS2] as $ds2 ) {
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
					(isset($SL['DS_ONK']) && $SL['DS_ONK'] == 1)
					|| (
						!empty($SL['DS1'])
						&& (
							substr($SL['DS1'], 0, 1) == 'C'
							|| (substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
							|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
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

				if (
					(empty($SL['DS_ONK']) || $SL['DS_ONK'] != 1)
					//&& (empty($SL['P_CEL']) || $SL['P_CEL'] != '1.3')
					&& !empty($SL['DS1'])
					&& (
						substr($SL['DS1'], 0, 1) == 'C'
						|| (substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
						|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
					)
				) {
					$hasONKOSLData = false;
					$ONK_SL_DATA['B_DIAG_DATA'] = array();
					$ONK_SL_DATA['B_PROT_DATA'] = array();
					$ONK_SL_DATA['ONK_USL_DATA'] = array();

					foreach ( $ONK_SL_FIELDS as $field ) {
						if ( isset($SL[$field]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA[$field] = $SL[$field];
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

					if ( isset($ONKOUSL[$sl_key]) ) {
						$hasONKOSLData = true;

						// Цепляем LEK_PR
						foreach ( $ONKOUSL[$sl_key] as $recKey => $recData ) {
							if ( isset($LEK_PR[$recData['EvnUsluga_id']]) ) {
								$LEK_PR_DATA = array();

								foreach ( $LEK_PR[$recData['EvnUsluga_id']] as $row ) {
									if ( empty($row['REGNUM']) ) {
										continue;
									}

									if ( !isset($LEK_PR_DATA[$row['REGNUM']]) ) {
										$LEK_PR_DATA[$row['REGNUM']] = array(
											'REGNUM' => $row['REGNUM'],
											'CODE_SH' => (!empty($row['CODE_SH']) ? $row['CODE_SH'] : null),
											'DATE_INJ_DATA' => array(),
										);
									}

									$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
									if (!empty($row['DATE_INJ1'])) {
										while ($row['DATE_INJ'] < $row['DATE_INJ1']) {
											$row['DATE_INJ'] = date('Y-m-d', strtotime($row['DATE_INJ']) + 24 * 60 * 60);
											$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
										}
									}
								}

								$ONKOUSL[$sl_key][$recKey]['LEK_PR_DATA'] = $LEK_PR_DATA;
							}
						}

						$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
						unset($ONKOUSL[$sl_key]);
					}

					if ( $hasONKOSLData == false ) {
						$ONK_SL_DATA = array();
					}
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZAP_ARRAY[$zsl_key]['SL'][$sl_key] = $SL;
			}
			else {
				// иначе создаём новый ZAP
				$this->persCnt++;
				$this->zapCnt++;

				$SL['CODE_MES1_DATA'] = array();
				$SL['CONS_DATA'] = array();
				$SL['DS2_DATA'] = array();
				$SL['DS2_N_DATA'] = array();
				$SL['DS3_DATA'] = array();
				$SL['KSG_KPG_DATA'] = array();
				$SL['NAPR_DATA'] = array();
				$SL['NAZ_DATA'] = array();
				$SL['ONK_SL_DATA'] = array();
				$SL['SANK'] = array();
				$SL['USL'] = array();

				$ZSL['SL'] = array();

				$KSG_KPG_DATA = array();
				$ONK_SL_DATA = array();
				$OS_SLUCH = array();
				$VNOV_M = array();

				$onkDS2 = false;

				if ( !empty($PACIENT['OS_SLUCH']) ) {
					$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT['OS_SLUCH']);
				}

				if ( !empty($PACIENT['OS_SLUCH1']) ) {
					$OS_SLUCH[] = array('OS_SLUCH' => $PACIENT['OS_SLUCH1']);
				}

				if ( array_key_exists('OS_SLUCH', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH']);
				}
				if ( array_key_exists('OS_SLUCH1', $PACIENT) ) {
					unset($PACIENT['OS_SLUCH1']);
				}

				if ( !empty($ZSL['VNOV_M1']) ) {
					$VNOV_M[] = array('VNOV_M_VAL' => $ZSL['VNOV_M1']);
				}

				if ( !empty($ZSL['VNOV_M2']) ) {
					$VNOV_M[] = array('VNOV_M_VAL' => $ZSL['VNOV_M2']);
				}

				$ZSL['OS_SLUCH_DATA'] = $OS_SLUCH;
				$ZSL['VNOV_M_DATA'] = $VNOV_M;

				$PACIENT['DOST'] = array();
				$PACIENT['DOST_P'] = array();

				if (!in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
					if ($PACIENT['NOVOR'] == '0') {
						if (empty($PACIENT['FAM'])) {
							$PACIENT['DOST'][] = array('DOST_VAL' => 2);
						}

						if (empty($PACIENT['IM'])) {
							$PACIENT['DOST'][] = array('DOST_VAL' => 3);
						}

						if (empty($PACIENT['OT']) || mb_strtoupper($PACIENT['OT'], 'windows-1251') == $netValue) {
							$PACIENT['DOST'][] = array('DOST_VAL' => 1);
						}
					} else {
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
				}

				if ( isset($USL[$sl_key]) ) {
					$SL['USL'] = $USL[$sl_key];
					unset($USL[$sl_key]);
				}

				if ( isset($DS2[$sl_key]) ) {
					$SL[$indexDS2] = $DS2[$sl_key];
					unset($DS2[$sl_key]);
				}
				else if ( !empty($SL['DS2']) && $indexDS2 == 'DS2_DATA' ) {
					$SL[$indexDS2] = array(array('DS2' => $SL['DS2']));
				}

				if ( array_key_exists('DS2', $SL) ) {
					unset($SL['DS2']);
				}

				if ( isset($DS3[$sl_key]) ) {
					$SL['DS3_DATA'] = $DS3[$sl_key];
					unset($DS3[$sl_key]);
				}
				else if ( !empty($SL['DS3']) ) {
					$SL['DS3_DATA'] = array(array('DS3' => $SL['DS3']));
				}

				if ( array_key_exists('DS3', $SL) ) {
					unset($SL['DS3']);
				}

				if ( !empty($SL['VER_KSG']) ) {
					foreach ( $KSG_KPG_FIELDS as $index ) {
						$KSG_KPG_DATA[$index] = $SL[$index];
						unset($SL[$index]);
					}

					$KSG_KPG_DATA['CRIT_DATA'] = array();
					$KSG_KPG_DATA['SL_KOEF_DATA'] = array();

					$SL['KSG_KPG_DATA'] = array($KSG_KPG_DATA);
				}

				if ( isset($CRIT[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['CRIT_DATA'] = $CRIT[$sl_key];
					unset($CRIT[$sl_key]);
				}

				if ( isset($SL_KOEF[$sl_key]) && count($SL['KSG_KPG_DATA']) > 0 ) {
					$SL['KSG_KPG_DATA'][0]['SL_KOEF_DATA'] = $SL_KOEF[$sl_key];
					unset($SL_KOEF[$sl_key]);
				}

				if ( isset($NAZ[$sl_key]) ) {
					$SL['NAZ_DATA'] = $NAZ[$sl_key];
					unset($NAZ[$sl_key]);
				}

				if ( count($SL[$indexDS2]) > 0 ) {
					foreach ( $SL[$indexDS2] as $ds2 ) {
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
					(isset($SL['DS_ONK']) && $SL['DS_ONK'] == 1)
					|| (
						!empty($SL['DS1'])
						&& (
							substr($SL['DS1'], 0, 1) == 'C'
							|| (substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
							|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
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

				if (
					(empty($SL['DS_ONK']) || $SL['DS_ONK'] != 1)
					//&& (empty($SL['P_CEL']) || $SL['P_CEL'] != '1.3')
					&& !empty($SL['DS1'])
					&& (
						substr($SL['DS1'], 0, 1) == 'C'
						|| (substr($SL['DS1'], 0, 3) >= 'D00' && substr($SL['DS1'], 0, 3) <= 'D09')
						|| ($SL['DS1'] == 'D70' && $onkDS2 == true)
					)
				) {
					$hasONKOSLData = false;
					$ONK_SL_DATA['B_DIAG_DATA'] = array();
					$ONK_SL_DATA['B_PROT_DATA'] = array();
					$ONK_SL_DATA['ONK_USL_DATA'] = array();

					foreach ( $ONK_SL_FIELDS as $field ) {
						if ( isset($SL[$field]) ) {
							$hasONKOSLData = true;
							$ONK_SL_DATA[$field] = $SL[$field];
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

					if ( isset($ONKOUSL[$sl_key]) ) {
						$hasONKOSLData = true;

						// Цепляем LEK_PR
						foreach ( $ONKOUSL[$sl_key] as $recKey => $recData ) {
							if ( isset($LEK_PR[$recData['EvnUsluga_id']]) ) {
								$LEK_PR_DATA = array();

								foreach ( $LEK_PR[$recData['EvnUsluga_id']] as $row ) {
									if ( empty($row['REGNUM']) ) {
										continue;
									}

									if ( !isset($LEK_PR_DATA[$row['REGNUM']]) ) {
										$LEK_PR_DATA[$row['REGNUM']] = array(
											'REGNUM' => $row['REGNUM'],
											'CODE_SH' => (!empty($row['CODE_SH']) ? $row['CODE_SH'] : null),
											'DATE_INJ_DATA' => array(),
										);
									}

									$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
									if (!empty($row['DATE_INJ1'])) {
										while ($row['DATE_INJ'] < $row['DATE_INJ1']) {
											$row['DATE_INJ'] = date('Y-m-d', strtotime($row['DATE_INJ']) + 24 * 60 * 60);
											$LEK_PR_DATA[$row['REGNUM']]['DATE_INJ_DATA'][] = array('DATE_INJ' => $row['DATE_INJ']);
										}
									}
								}

								$ONKOUSL[$sl_key][$recKey]['LEK_PR_DATA'] = $LEK_PR_DATA;
							}
						}

						$ONK_SL_DATA['ONK_USL_DATA'] = $ONKOUSL[$sl_key];
						unset($ONKOUSL[$sl_key]);
					}

					if ( $hasONKOSLData == false ) {
						$ONK_SL_DATA = array();
					}
				}

				if ( count($ONK_SL_DATA) > 0 ) {
					$SL['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}

				$ZSL['N_ZAP'] = $this->zapCnt;
				$ZSL['PACIENT'] = array($PACIENT);
				$ZSL['SL'][$sl_key] = $SL;

				$PACIENT_ARRAY[$zsl_key] = $PACIENT;
				$ZAP_ARRAY[$zsl_key] = $ZSL;
			}
		}

		// записываем оставшееся
		if ( count($ZAP_ARRAY) > 0 ) {
			// пишем в файл случаи
			array_walk_recursive($ZAP_ARRAY, 'RegistryUfa_model::encodeForXmlExport');
			$xml = $this->parser->parse_ext('export_xml/' . $sluchDataTemplate, array('ZAP' => $ZAP_ARRAY), true, false, $altKeys, false);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($sluchDataFile, $xml, FILE_APPEND);
			unset($ZAP_ARRAY);
			unset($xml);

			// пишем в файл пациентов
			array_walk_recursive($PACIENT_ARRAY, 'RegistryUfa_model::encodeForXmlExport');
			$xml_pers = $this->parser->parse_ext('export_xml/' . $personDataTemplate, array('PACIENT' => $PACIENT_ARRAY), true, false, array(), false);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($personDataFile, $xml_pers, FILE_APPEND);
			unset($PACIENT_ARRAY);
			unset($xml_pers);
		}

		$response = array();
		$response['persCnt'] = $this->persCnt;
		$response['zapCnt'] = $this->zapCnt;

		return $response;
	}

	/**
	 *	Установка статуса реестра
	 */
	public function setRegistryStatus($data) {
		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
		}

		$registryStatusList = $this->getAllowedRegistryStatuses();

		if ( !in_array($data['RegistryStatus_id'], $registryStatusList) ) {
			return array(array('success' => false, 'Error_Msg' => "Недопустимый статус реестра"));
		}

		// Предварительно получаем тип реестра
		$RegistryType_id = 0;
		$RegistryStatus_id = 0;
		$RegistrySubType_id = null;
		$Registry_IsNew = 1;

		$r = $this->getFirstRowFromQuery("
			  select r.RegistryType_id as \"RegistryType_id\",
                     r.RegistrySubType_id as \"RegistrySubType_id\",
                     r.RegistryStatus_id as \"RegistryStatus_id\",
                     r.Registry_IsNew as \"Registry_IsNew\",
                     pt.PayType_SysNick as \"PayType_SysNick\"
              from {$this->scheme}.v_Registry r
                   left join v_PayType pt on pt.PayType_id = r.PayType_id
              where r.Registry_id =:Registry_id
              limit 1
		", array('Registry_id' => $data['Registry_id']));

		if ( $r === false ) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при получении данных реестра'));
		}

		$RegistryType_id = $r['RegistryType_id'];
		$RegistryStatus_id = $r['RegistryStatus_id'];
		$RegistrySubType_id = $r['RegistrySubType_id'];
		$Registry_IsNew = $r['Registry_IsNew'];
		$PayType_SysNick = $r['PayType_SysNick'];

		$data['RegistryType_id'] = $RegistryType_id;

		$this->setRegistryParamsByType($data);

		$fields = "";

		// если перевели в работу, то снимаем признак формирования
		if ( $data['RegistryStatus_id'] == 3 ) {
			$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
		}

		if ($RegistrySubType_id == 1 && $PayType_SysNick != 'bud' && $data['RegistryStatus_id']==2 && $RegistryStatus_id == 3) {
			// если переводим к оплате, то проверяем превышение объёмов
			$proc_volume = 'p_Registry_EvnPL_Volume';
			switch($RegistryType_id) {
				case 1:
					$proc_volume = 'p_Registry_EvnPS_Volume';
					break;
				case 2:
					$proc_volume = 'p_Registry_EvnPL_Volume';
					break;
				case 6:
					$proc_volume = 'p_Registry_SMP_Volume';
					break;
				case 7:
					$proc_volume = 'p_Registry_EvnPLDD13_Volume';
					break;
				case 9:
					$proc_volume = 'p_Registry_EvnPLOrp13_Volume';
					break;
				case 14:
					$proc_volume = 'p_Registry_EvnHTM_Volume';
					break;
				case 17:
					$proc_volume = 'p_Registry_EvnPLProf_Volume';
					break;
			}

			if ( $Registry_IsNew == 2 ) {
				$proc_volume .= '_2018';
			}

			if ( in_array($RegistryType_id, array(1, 14)) ) {
				$query = "
                  WITH cte AS (
                      SELECT CAST (VolumeType_id as bigint) as VolumeType_id,
                             CAST (KSG_code as varchar (30)) as KSG_code,
                             CAST (VolumeOverflowCount as int8) as VolumeOverflowCount
                      FROM {$this -> scheme}.{$proc_volume}(:Registry_id :=: Registry_id,:debug := 0)
                  )
                  select vt.VolumeType_Code as \"VolumeType_Code\",
                         t.VolumeOverflowCount as \"VolumeOverflowCount\",
                         t.KSG_code as \"KSG_code\"
                  from v_VolumeType vt
                       inner join cte t on t.VolumeType_id = vt.VolumeType_id    
				";
			}
			else {
				$query = "
                  WITH cte AS (
                      SELECT CAST (VolumeType_id as bigint) as VolumeType_id,
                             CAST (VolumeOverflowCount as int8) as VolumeOverflowCount
                      FROM {$this -> scheme}.{$proc_volume}(:Registry_id :=: Registry_id,:debug := 0)
                  )
                  select vt.VolumeType_Code as \"VolumeType_Code\",
                         t.VolumeOverflowCount as \"VolumeOverflowCount\"
                  from v_VolumeType vt
                       inner join cte t on t.VolumeType_id = vt.VolumeType_id    
				";
			}

			$resp = $this->queryResult($query, array(
				'Registry_id' => $data['Registry_id']
			));

			if (!empty($resp)) {
				$codes = "";
				foreach($resp as $respone) {
					$codes .= "<div>" . $respone['VolumeType_Code'] . (!empty($respone['KSG_code']) ? " (" . $respone['KSG_code'] . ")" : "") . " - " . $respone['VolumeOverflowCount'] . "</div>";
				}

				return array(array('success' => false, 'Error_Msg' => "<div>В предварительном реестре обнаружены превышения объемов:</div>{$codes}<div>Реестр невозможно перевести к оплате.</div>"));
			}
		}

		if ($RegistryStatus_id == 3 && ($data['RegistryStatus_id']==2) && (in_array($RegistryType_id, $this->getAllowedRegistryTypes())) && (isset($data['session']['setting']['server']['check_registry_exists_errors']) && $data['session']['setting']['server']['check_registry_exists_errors']==1) && (!isSuperadmin())) // если переводим "к оплате" и проверка установлена, и это не суперадмин то проверяем на ошибки
		{
			$tempscheme = 'dbo';

			$query = "
			 Select (
               Select count(*) as err
               from {$this->scheme}.v_{$this->RegistryErrorObject} RegistryError
                    left join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Evn_id = RegistryError.Evn_id
                    left join RegistryErrorType on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
               where RegistryError.registry_id =:Registry_id and
                     RegistryErrorType.RegistryErrorClass_id = 1 and
                     RegistryError.RegistryErrorClass_id = 1 and
                     COALESCE(rd.RegistryData_deleted, 1) = 1 and
                     rd.Evn_id is not null
             ) +
             (
               Select count(*) as err
               from {$tempscheme}.v_{$this -> RegistryErrorComObject} RegistryErrorCom
                    left join RegistryErrorType on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
               where registry_id =:Registry_id and
                     RegistryErrorType.RegistryErrorClass_id = 1
             ) as \"err\"
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

		// пересчитываем количество записей в реестре
		$resp_sum = $this->queryResult("
			  select SUM(case
                           when COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then RD.RegistryData_Sum_R
                           else 0
                         end) as \"Sum\",
                     SUM(case
                           when COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then 1
                           else 0
                         end) as \"Count\",
                     SUM(case
                           when COALESCE(RD.RegistryData_IsBadVol, 1) = 2 then 1
                           else 0
                         end) as \"CountBadVol\"
              from {$this->scheme}.v_{$this->RegistryDataObject} rd
              where RD.Registry_id =:Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		$query = "
			    update {$this->scheme}.Registry
				  set
					RegistryStatus_id = :RegistryStatus_id,
					Registry_updDT = dbo.tzGetDate(),
					Registry_SumR = :Registry_SumR,
					Registry_Sum = :Registry_Sum,
					Registry_RecordCount = :Registry_RecordCount,
					Registry_CountIsBadVol = :Registry_CountIsBadVol,
					{$fields}
					pmUser_updID = :pmUser_id
				where
					Registry_id = :Registry_id
                returning RegistryStatus_id as \"RegistryStatus_id\", '' as \"Error_Code\", '' as \"Error_Msg\";
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'Registry_SumR' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
			'Registry_Sum' => !empty($resp_sum[0]['Sum'])?$resp_sum[0]['Sum']:0,
			'Registry_CountIsBadVol' => !empty($resp_sum[0]['CountBadVol'])?$resp_sum[0]['CountBadVol']:0,
			'Registry_RecordCount' => !empty($resp_sum[0]['Count'])?$resp_sum[0]['Count']:0,
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
	 * Сохранение финального реестра
	 */
	function saveUnionRegistry($data) {
		if ( !empty($data['Registry_id']) ) {
			$registryData = $this->getFirstRowFromQuery("
				select 
					r.Registry_id as \"Registry_id\",
					r.RegistryType_id as \"RegistryType_id\",
					r.RegistrySubType_id as \"RegistrySubType_id\",
					r.Registry_IsZNO as \"Registry_IsZNO\"
				from {$this->scheme}.v_Registry r 
				where
					r.Registry_id = :Registry_id
                limit 1
			", $data);

			if ( $registryData === false ) {
				return array('Error_Msg' => 'Ошибка при получении данных реестра');
			}

			// @task https://redmine.swan-it.ru/issues/152091
			$data['Registry_IsZNO'] = $registryData['Registry_IsZNO'];

			if ( $registryData['RegistrySubType_id'] != 2 ) {
				return array('Error_Msg' => 'Указанный реестр не является реестром по СМО');
			}
		}

		// 1. сохраняем реестр по СМО
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "
			select Registry_id as \"Registry_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from {$this->scheme}.{$proc}(
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				RegistryStatus_id := 3,
				Registry_Sum := NULL,
				Registry_IsActive := 2,
				Registry_Num := :Registry_Num,
				Registry_accDate := :Registry_accDate,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				OrgSmo_id := :OrgSmo_id,
				LpuUnitSet_id := :LpuUnitSet_id,
				Lpu_id := :Lpu_id,
				Registry_IsNotInsur := :Registry_IsNotInsur,
				Registry_Comments := :Registry_Comments,
				RegistrySubType_id := 2,
				Registry_IsNew := :Registry_IsNew,
				DispClass_id := :DispClass_id,
				Registry_IsZNO := :Registry_IsZNO,
				pmUser_id := :pmUser_id);
		";

		$data['Registry_accDate'] = date('Y-m-d H:i:s');

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

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

			// Югория-Мед объединилась с Альфастрахованием, по ней реестры не формируем
			if ( $data['Registry_accDate'] >= '2017-05-25' && $data['OrgSmo_id'] == 8000233 ) {
				return $resp;
			} else if ( $data['Registry_accDate'] >= '2018-10-25' && $data['OrgSmo_id'] == 8000227 ) {
				return $resp;
			}
			// 3. выполняем поиск реестров которые войдут в финальный
			if ( $data['Registry_accDate'] >= '2017-05-25' && $data['OrgSmo_id'] == 8001229 ) {
				$filter = "and rf.OrgSmo_id in (8000233, 8001229) and COALESCE(rf.Registry_IsNotInsur, 1) = 1";

			}
			else if ( $data['Registry_accDate'] >= '2018-10-25' && $data['OrgSmo_id'] == 8001750 ) {
				$filter = "and rf.OrgSmo_id in (8000227, 8001750) and COALESCE(rf.Registry_IsNotInsur, 1) = 1";

			}
			else {
				$filter = "and rf.OrgSmo_id = :OrgSmo_id and COALESCE(rf.Registry_IsNotInsur, 1) = 1";

			}

			if ($data['Registry_IsNotInsur'] == 2) {
				// по незастрахованным только один может быть финальный реестр для предварительного, СМО не учитываем
				$filter = "and rf.Registry_IsNotInsur = 2";
			}

			$query = "
				select R.Registry_id as \"Registry_id\",
                       R.Registry_Num as \"Registry_Num\",
                       to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
                       RT.RegistryType_Name as \"RegistryType_Name\",
                       RETF.FLKCount as \"FLKCount\"
                from {$this->scheme}.v_Registry R
                     left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
                     LEFT JOIN LATERAL
                     (
                       select count(RegistryErrorTFOMS_id) as FLKCount
                       from dbo.v_RegistryErrorTFOMS
                       where RegistryErrorTFOMSLevel_id = 1 and
                             Registry_id = R.Registry_id
                     ) RETF ON true
                where R.RegistrySubType_id = 1
                      " . (!empty($data['OrgSmo_id']) && $data['OrgSmo_id'] == 8 ? " and OrgSmo_id = 8":"and OrgSmo_id is null") . "
                      and
                      R.RegistryStatus_id = 2 -- К оплате
                      and
                      R.Lpu_id =:Lpu_id and
                      R.RegistryType_id =:RegistryType_id and
                      R.Registry_begDate >=:Registry_begDate and
                      R.Registry_endDate <=:Registry_endDate and
                      R.LpuUnitSet_id =:LpuUnitSet_id and
                      COALESCE(R.Registry_IsNew, 1) =:Registry_IsNew and
                      COALESCE(R.DispClass_id, 0) =:DispClass_id and
                      COALESCE(r.PayType_id, (
                                               Select PayType_id
                                               from v_PayType pt
                                               where pt.PayType_SysNick = 'oms'
                                               limit 1
                      )) =
                      (
                        Select PayType_id
                        from v_PayType pt
                        where pt.PayType_SysNick = 'oms'
                        limit 1
                      ) and
                      not exists (-- и не входит в другой реестр по той же смо
                select rgl.RegistryGroupLink_id
                from {$this->scheme}.v_RegistryGroupLink rgl
                     inner join {$this->scheme}.v_Registry rf on rf.Registry_id = rgl.Registry_pid -- финальный реестр
                where rgl.Registry_id = R.Registry_id and
                      COALESCE(rf.Registry_IsZNO, 1) = COALESCE(CAST(:Registry_IsZNO as bigint), 1) 
                {$filter}
                )
			";
			$result_reg = $this->db->query($query, array(
				'Lpu_id' => $data['Lpu_id'],
				'OrgSmo_id' => $data['OrgSmo_id'],
				'LpuUnitSet_id' => $data['LpuUnitSet_id'],
				'RegistryType_id' => $data['RegistryType_id'],
				'Registry_begDate' => $data['Registry_begDate'],
				'Registry_endDate' => $data['Registry_endDate'],
				'Registry_IsNew' => !empty($data['Registry_IsNew']) ? $data['Registry_IsNew'] : 1,
				'Registry_IsZNO' => !empty($data['Registry_IsZNO']) ? $data['Registry_IsZNO'] : 1,
				'DispClass_id' => !empty($data['DispClass_id']) ? $data['DispClass_id'] : 0
			));

			if (is_object($result_reg))
			{
				$resp_reg = $result_reg->result('array');
				// 4. сохраняем новые связи
				foreach($resp_reg as $one_reg) {
					$query = "
						select RegistryGroupLink_id as \"RegistryGroupLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from {$this->scheme}.p_RegistryGroupLink_ins(
							RegistryGroupLink_id := null,
							Registry_pid := :Registry_pid,
							Registry_id := :Registry_id,
							pmUser_id := :pmUser_id);
					";

					$this->db->query($query, array(
						'Registry_pid' => $resp[0]['Registry_id'],
						'Registry_id' => $one_reg['Registry_id'],
						'pmUser_id' => $data['pmUser_id']
					));

				}
			}

			$this->recountSumKolUnionRegistry(array(
				'Registry_id' => $resp[0]['Registry_id']
			));

			// пишем информацию о формировании реестра в историю
			$this->dumpRegistryInformation(array(
				'Registry_id' => $resp[0]['Registry_id']
			), 1);
		}

		return $resp;
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
                       R.RegistryType_id as \"RegistryType_id\",
                       R.DispClass_id as \"DispClass_id\",
                       OS.OrgSmo_Name as \"OrgSmo_Name\",
                       R.Registry_Num as \"Registry_Num\",
                       COALESCE(R.Registry_IsNotInsur, 1) as \"Registry_IsNotInsur\",
                       case
                         when R.Registry_IsZNO = 2 then 'true'
                         else 'false'
                       end as \"Registry_IsZNO\",
                       R.LpuUnitSet_Code as \"LpuUnitSet_Code\",
                       to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
                       to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
                       to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
                       COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
                       R.RegistryStatus_id as \"RegistryStatus_id\",
                       COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
                       COALESCE(R.Registry_CountIsBadVol, 0) as \"Registry_CountIsBadVol\",
                       to_char(R.Registry_updDT, 'DD.MM.YYYY') || ' ' || to_char(R.Registry_updDT, 'HH24:MI:SS') as \"Registry_updDT\",
                       R.Registry_pack as \"Registry_pack\",
                       DC.DispClass_id as \"DispClass_id\",
                       DC.DispClass_Name as \"DispClass_Name\",
                       to_char(R.Registry_orderDate, 'DD.MM.YYYY') as \"Registry_orderDate\"
                       -- end select
                from
                     -- from
                     {$this->scheme}.v_RegistryUfa R -- объединённый реестр
                     left join v_OrgSmo os on os.OrgSmo_id = r.OrgSmo_id
                     left join v_DispClass DC on DC.DispClass_id = R.DispClass_id
                     -- end from
                where
                      -- where
                      R.Lpu_id =:Lpu_id and
                      R.RegistryType_id =:RegistryType_id and
                      R.RegistryStatus_id =:RegistryStatus_id and
                      R.RegistrySubType_id = 2 and
                      COALESCE(R.Registry_IsNew, 1) = COALESCE(CAST(:Registry_IsNew as bigint), 1)
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
                       R.Registry_id as \"Registry_id\",
                       R.Registry_Num as \"Registry_Num\",
                       to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
                       to_char(R.Registry_begDate, 'DD.MM.YYYY') as \"Registry_begDate\",
                       to_char(R.Registry_endDate, 'DD.MM.YYYY') as \"Registry_endDate\",
                       RT.RegistryType_Name as \"RegistryType_Name\",
                       COALESCE(CAST(R.Registry_Sum as decimal), 0.00) as \"Registry_Sum\",
                       PT.PayType_Name as \"PayType_Name\",
                       COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
                       to_char(R.Registry_updDT, 'DD.MM.YYYY') as \"Registry_updDate\"
                       -- end select
                from
                     -- from
                     {$this->scheme}.v_RegistryGroupLink RGL
                     inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id -- обычный реестр
                     left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
                     left join v_PayType PT on PT.PayType_id = R.PayType_id
                     -- end from
                where
                      -- where
                      RGL.Registry_pid =:Registry_id
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
	 * Пересчёт суммы и количества в предварительном реестре
	 */
	function recountSumKolRegistry($data) {
		$query = "
			select
				r.Registry_id as \"Registry_id\",
				r.RegistrySubType_id as \"RegistrySubType_id\",
				r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
				r.OrgSmo_id as \"OrgSmo_id\"
			from
				{$this->scheme}.v_Registry r 
			where
				r.Registry_id = :Registry_id
		";

		$resp = $this->queryResult($query, array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Registry_id'])) {
			$data['Registry_id'] = $resp[0]['Registry_id'];
			if ($resp[0]['RegistrySubType_id'] == '2') {
				return array('Error_Msg' => 'Указанный реестр не является предварительным');
			}

			$this->setRegistryParamsByType($data);

			// считаем сумму и количество
			$resp_sum = $this->queryResult("
				select SUM(case
                             when COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then RD.RegistryData_Sum_R
                             else 0
                           end) as \"Sum\",
                       SUM(case
                             when COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then 1
                             else 0
                           end) as \"Count\",
                       SUM(case
                             when COALESCE(RD.RegistryData_IsBadVol, 1) = 2 then 1
                             else 0
                           end) as \"CountBadVol\"
                from {$this->scheme}.v_{$this->RegistryDataObject} rd
                where rd.Registry_id =:Registry_id
			", array(
				'Registry_id' => $data['Registry_id']
			));

			$this->db->query("
				update {$this->scheme}.Registry set Registry_SumR = :Registry_SumR, Registry_Sum = :Registry_Sum, Registry_RecordCount = :Registry_RecordCount, Registry_CountIsBadVol = :Registry_CountIsBadVol where Registry_id = :Registry_id
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
	 * Пересчёт суммы и количества в реестре по СМО
	 */
	function recountSumKolUnionRegistry($data) {
		$query = "
			    select r.Registry_id as \"Registry_id\",
                       r.RegistrySubType_id as \"RegistrySubType_id\",
                       r.Registry_IsNotInsur as \"Registry_IsNotInsur\",
                       r.Registry_IsZNO as \"Registry_IsZNO\",
                       r.OrgSmo_id as \"OrgSmo_id\",
                       to_char(r.Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
                from {$this->scheme}.v_Registry r
                where r.Registry_id =:Registry_id
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
				$filter .= " and COALESCE(os.OrgSMO_RegNomC,'')=''";

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
				$filter .= " and COALESCE(RD.RegistryData_IsZNO, 1) = 1 ";

			}

			// считаем сумму и количество
			$resp_sum = $this->queryResult("
				select SUM(case
                             when COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then RD.RegistryData_Sum_R
                             else 0
                           end) as \"Sum\",
                       SUM(case
                             when COALESCE(RD.RegistryData_IsBadVol, 1) = 1 then 1
                             else 0
                           end) as \"Count\",
                       SUM(case
                             when COALESCE(RD.RegistryData_IsBadVol, 1) = 2 then 1
                             else 0
                           end) as \"CountBadVol\"
                from {$this->scheme}.v_RegistryGroupLink RGL
                     inner join {$this->scheme}.v_Registry RF on RF.Registry_id = RGL.Registry_pid
                     inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id
                     inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id 
                     {$filter_rd}
                     left join v_OrgSmo os on os.OrgSmo_id = rd.OrgSmo_id
                where RGL.Registry_pid =:Registry_id 
                {$filter}
			", array(
				'Registry_id' => $data['Registry_id']
			));

			$this->db->query("
				update {$this->scheme}.Registry set Registry_SumR = :Registry_SumR, Registry_Sum = :Registry_Sum, Registry_RecordCount = :Registry_RecordCount, Registry_CountIsBadVol = :Registry_CountIsBadVol where Registry_id = :Registry_id
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
	 * Добавление превышения объёма МП
	 */
	function setIsBadVol($data)
	{

		// действие доступно для предварительных реестров без СМО (в шапке реестра не указана СМО
		$resp_reg = $this->queryResult("select Registry_id as \"Registry_id\", RegistryStatus_id as \"RegistryStatus_id\", RegistrySubType_id  as \"RegistrySubType_id\" from {$this->scheme}.v_Registry  where Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));


		if ($resp_reg === false || empty($resp_reg[0]['Registry_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		} else if ($resp_reg[0]['RegistrySubType_id'] != 1) {
			return array('Error_Msg' => 'Действие доступно только для предварительных реестров без СМО');
		} else if (!empty($resp_reg[0]['RegistryStatus_id']) && !in_array($resp_reg[0]['RegistryStatus_id'], array(2,3)) && $data['RegistryData_IsBadVol'] == 2) {
			return array('Error_Msg' => 'Действие доступно только для предварительных реестров в статусе "В работе" и "К оплате"');
		} else if (!empty($resp_reg[0]['RegistryStatus_id']) && $resp_reg[0]['RegistryStatus_id'] != 3 && $data['RegistryData_IsBadVol'] != 2) {
			return array('Error_Msg' => 'Действие доступно только для предварительных реестров в статусе "В работе"');
		}

		// Снять превышение объема можно в случае если на случае установлен признак исключения из реестра (IsInReg=1).
		$this->setRegistryParamsByType($data);

		// @task https://redmine.swan.perm.ru/issues/85360
		// @task https://redmine.swan.perm.ru/issues/85362
		// @task https://redmine.swan.perm.ru/issues/130507
		// @task https://redmine.swan-it.ru/issues/154484
		if ( in_array($this->RegistryType_id, array(2, 7, 9, 17)) && is_array($data['Evn_ids']) && count($data['Evn_ids']) > 0 ) {
			$Evn_ids = $this->queryResult("
				select rd.Evn_id as \"Evn_id\"
                from {$this->scheme}.v_{$this->RegistryDataObject} rd
                where rd.Registry_id =:Registry_id and
                      rd.Evn_rid in (
                                      select Evn_rid
                                      from {$this->scheme}.v_{$this->RegistryDataObject}
			                          where Evn_id in (" . implode(",", $data['Evn_ids']) . ")
                      )
			", array('Registry_id' => $data['Registry_id']));

			if ( $Evn_ids === false ) {
				return array('Error_Msg' => 'Ошибка при получении списка идентификаторов событий');
			}

			$data['Evn_ids'] = array();

			foreach ( $Evn_ids as $Evn ) {
				$data['Evn_ids'][] = $Evn['Evn_id'];
			}
		}

		$evnArray = array();
		$proccessedEvns = array();

		foreach($data['Evn_ids'] as $Evn_id) {
			if (in_array($Evn_id, $proccessedEvns)) { // если уже обработали этот Evn, то пропускаем.
				continue;
			}

			/*if ($data['RegistryData_IsBadVol'] != 2) {
				if ($this->RegistryType_id == 6) {
					$query = "
					select
						CmpCloseCard_IsInReg as IsInReg
					from
						dbo.CmpCloseCard 

					where
						CmpCloseCard_id = :Evn_id
				";
				} else {
					$query = "
					select
						EvnVizit_IsInReg as IsInReg
					from
						EvnVizit 

					where
						Evn_id = :Evn_id

					union

					select
						EvnSection_IsInReg as IsInReg
					from
						EvnSection 

					where
						Evn_id = :Evn_id
				";
				}

				$resp = $this->queryResult($query, array(
					'Evn_id' => $Evn_id
				));

				if (!empty($resp[0]['IsInReg']) && $resp[0]['IsInReg'] == 2) {
					return array('Error_Msg' => 'Нельзя снять превышение объёма, т.к. случай имеет признак вхождения в реестр');
				}
			}*/

			// Для обращений по заболеванию (код посещения заканчивается на 865,866,836,888,889) превышения добавлять снимать нужно сразу на все посещения в ТАП. Т.е. делить ТАП нельзя, снимать целиком.
			// @task https://redmine.swan.perm.ru/issues/130507 закомментировал, теперь для всех должно быть
			/*if ($this->RegistryType_id == 2) {
				$resp = $this->queryResult("
					select
						uc.UslugaComplex_Code,
						rd.Evn_rid
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd 

						left join v_UslugaComplex uc  on uc.UslugaComplex_id = COALESCE(RD.Usluga_id, RD.UslugaComplex_id)


					where
						rd.Registry_id = :Registry_id
						and rd.Evn_id = :Evn_id
				", array(
					'Evn_id' => $Evn_id,
					'Registry_id' => $data['Registry_id']
				));

				if (!empty($resp[0]['Evn_rid']) && !empty($resp[0]['UslugaComplex_Code']) && in_array(mb_substr($resp[0]['UslugaComplex_Code'], -3), array('865', '866', '836', '888', '889'))) {
					// надо всю группу случаев
					$resp_rd = $this->queryResult("
						select
							rd.Evn_id
						from
							{$this->scheme}.v_{$this->RegistryDataObject} rd 

						where
							rd.Registry_id = :Registry_id
							and rd.Evn_rid = :Evn_rid
							and rd.Evn_id <> :Evn_id
					", array(
						'Evn_id' => $Evn_id,
						'Evn_rid' => $resp[0]['Evn_rid'],
						'Registry_id' => $data['Registry_id']
					));

					foreach ($resp_rd as $one_rd) {
						$proccessedEvns[] = $one_rd['Evn_id'];
						$evnArray[] = $one_rd['Evn_id'];
					}
				}
			} else*/
			if ($this->RegistryType_id == 1) {
				// Для движений группировка по диагнозу (Evn_rid и MaxEvn_id одинаковы) и группировка по реанимации (Evn_rid и EvnSection_NumGroup одинаковы)
				$resp = $this->queryResult("
					select rd.Evn_rid as \"Evn_rid\",
                           rd.MaxEvn_id as \"MaxEvn_id\",
                           rd.EvnSection_NumGroup as \"EvnSection_NumGroup\"
                    from {$this->scheme}.{$this->RegistryDataObject} rd
                    where rd.Registry_id =:Registry_id and
                          rd.Evn_id =:Evn_id
				", array(
					'Evn_id' => $Evn_id,
					'Registry_id' => $data['Registry_id']
				));

				if (!empty($resp[0]['Evn_rid'])) {
					$resp_rd = $this->queryResult("
						select
							rd.Evn_id as \"Evn_id\"
						from
							{$this->scheme}.{$this->RegistryDataObject} rd 
						where
							rd.Registry_id = :Registry_id
							and rd.Evn_rid = :Evn_rid
							and (rd.MaxEvn_id = :MaxEvn_id OR rd.EvnSection_NumGroup = :EvnSection_NumGroup)
							and rd.Evn_id <> :Evn_id
					", array(
						'Evn_id' => $Evn_id,
						'Evn_rid' => $resp[0]['Evn_rid'],
						'MaxEvn_id' => $resp[0]['MaxEvn_id'],
						'EvnSection_NumGroup' => $resp[0]['EvnSection_NumGroup'],
						'Registry_id' => $data['Registry_id']
					));

					foreach ($resp_rd as $one_rd) {
						$proccessedEvns[] = $one_rd['Evn_id'];
						$evnArray[] = $one_rd['Evn_id'];
					}
				}
			} else if ($this->RegistryType_id == 14) { // ВМП
				// Для движений группировка по методу ВМП (Evn_rid и HTMedicalCareClass_id одинаковы)
				$resp = $this->queryResult("
					select
						rd.Evn_rid as \"Evn_rid\",
						rd.HTMedicalCareClass_id as \"HTMedicalCareClass_id\"
					from
						{$this->scheme}.{$this->RegistryDataObject} rd 
					where
						rd.Registry_id = :Registry_id
						and rd.Evn_id = :Evn_id
				", array(
					'Evn_id' => $Evn_id,
					'Registry_id' => $data['Registry_id']
				));

				if (!empty($resp[0]['Evn_rid'])) {
					$resp_rd = $this->queryResult("
						select
							rd.Evn_id as \"Evn_id\"
						from
							{$this->scheme}.{$this->RegistryDataObject} rd 
						where
							rd.Registry_id = :Registry_id
							and rd.Evn_rid = :Evn_rid
							and rd.HTMedicalCareClass_id = :HTMedicalCareClass_id
							and rd.Evn_id <> :Evn_id
					", array(
						'Evn_id' => $Evn_id,
						'Evn_rid' => $resp[0]['Evn_rid'],
						'HTMedicalCareClass_id' => $resp[0]['HTMedicalCareClass_id'],
						'Registry_id' => $data['Registry_id']
					));

					foreach ($resp_rd as $one_rd) {
						$proccessedEvns[] = $one_rd['Evn_id'];
						$evnArray[] = $one_rd['Evn_id'];
					}
				}
			}

			$proccessedEvns[] = $Evn_id;
			$evnArray[] = $Evn_id;
		}

		if ( count($evnArray) > 0 ) {
			if ( $data['RegistryData_IsBadVol'] != 2 ) {
				if ( $this->RegistryType_id == 6 ) {
					$query = "
						select
							CmpCloseCard_IsInReg as \"IsInReg\"
						from
							dbo.CmpCloseCard 
						where
							CmpCloseCard_id in (" . implode(',', $evnArray) . ")
					";
				} else if ( in_array($this->RegistryType_id, array(1, 14)) ) {
					$query = "
						select
							EvnSection_IsInReg as \"IsInReg\"
						from
							EvnSection 
						where
							Evn_id in (" . implode(',', $evnArray) . ")
					";
				} else {
					$query = "
						select
							EvnVizit_IsInReg as \"IsInReg\"
						from
							EvnVizit 
						where
							Evn_id in (" . implode(',', $evnArray) . ")
					";
				}

				$resp = $this->queryResult($query, array());

				if ( is_array($resp) && count($resp) > 0 ) {
					foreach ( $resp as $row ) {
						if ( !empty($row['IsInReg']) && $row['IsInReg'] == 2 ) {
							return array('Error_Msg' => 'Нельзя снять превышение объёма, т.к. имеются случаи с признаком вхождения в реестр');
						}
					}
				}
			}

			$IsInReg = ($data['RegistryData_IsBadVol'] == 2 ? 1 : 2);

			$slUpdate = "";
			if (!empty($this->RegistryDataSLObject)) {
				$slUpdate = "
					update 
						r2.{$this->RegistryDataSLObject}
					set {$this->RegistryDataObject}_IsBadVol = :RegistryData_IsBadVol,
						{$this->RegistryDataSLObject}_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					from
						r2.{$this->RegistryDataSLObject} sl
						inner join r2.{$this->RegistryDataObject} viz on viz.Evn_rid = sl.Evn_rid and viz.Registry_id = sl.Registry_id
					where viz.Registry_id = :Registry_id
						and viz.{$this->RegistryDataEvnField} in (" . implode(',', $evnArray) . ");
				";
			}

			$query = "
					update r2.{$this->RegistryDataObject}
					set {$this->RegistryDataObject}_IsBadVol = :RegistryData_IsBadVol,
						RegistryData" . ($this->RegistryType_id == 6 ? "Cmp" : "") . "_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where Registry_id = :Registry_id
						and {$this->RegistryDataEvnField} in (" . implode(',', $evnArray) . ");

					{$slUpdate}

					-- снимаем признак вхождения в реестр у неоплаченных случаев
							update EvnVizit
							set EvnVizit_IsInReg = :IsInReg
							where EvnVizit_id in (" . implode(',', $evnArray) . ")
							and :RegistryType_id in (2,7,9,17);

							update EvnSection
							set EvnSection_IsInReg = :IsInReg
							where EvnSection_id in (" . implode(',', $evnArray) . ")
							and :RegistryType_id in (1,14);

							update dbo.CmpCloseCard
							set CmpCloseCard_IsInReg = :IsInReg
							where CmpCloseCard_id in (" . implode(',', $evnArray) . ")
							and :RegistryType_id in (6);
				select '' as \"Error_Code\", '' as \"Error_Msg\";
			";

			$resp_setvol = $this->queryResult($query, array(
				'IsInReg' => $IsInReg,
				'pmUser_id' => $data['pmUser_id'],
				'Registry_id' => $data['Registry_id'],
				'RegistryData_IsBadVol' => $data['RegistryData_IsBadVol'],
				'RegistryType_id' => $this->RegistryType_id,
			));

			if ( !empty($resp_setvol[0]['Error_Msg']) ) {
				return $resp_setvol;
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Переформирование реестра
	 */
	function reformUnionRegistry($data)
	{
		// получаем данные по реестру и сохраняем

		$resp = $this->queryResult("
			select
                Registry_id as \"Registry_id\",
                LpuUnitSet_Code as \"LpuUnitSet_Code\",
                RegistryType_id as \"RegistryType_id\",
                OrgSmo_Name as \"OrgSmo_Name\",
                Registry_Num as \"Registry_Num\",
                Lpu_id as \"Lpu_id\",
                Registry_accDate as \"Registry_accDate\",
                RegistryStatus_id as \"RegistryStatus_id\",
                Registry_begDate as \"Registry_begDate\",
                Registry_Sum as \"Registry_Sum\",
                Registry_IsActive as \"Registry_IsActive\",
                Registry_endDate as \"Registry_endDate\",
                Registry_ErrorCount as \"Registry_ErrorCount\",
                OrgSmo_id as \"OrgSmo_id\",
                Registry_ErrorCommonCount as \"Registry_ErrorCommonCount\",
                Registry_RecordCount as \"Registry_RecordCount\",
                LpuUnitSet_id as \"LpuUnitSet_id\",
                Registry_IsNeedReform as \"Registry_IsNeedReform\",
                Registry_downDT as \"Registry_downDT\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                Registry_insDT as \"Registry_insDT\",
                Registry_updDT as \"Registry_updDT\",
                pmUser_downID as \"pmUser_downID\",
                Registry_orderDate as \"Registry_orderDate\",
                Registry_pack as \"Registry_pack\",
                PayType_id as \"PayType_id\",
                Org_mid as \"Org_mid\",
                OrgRSchet_mid as \"OrgRSchet_mid\",
                Registry_IsNotInsur as \"Registry_IsNotInsur\",
                Registry_IsNew as \"Registry_IsNew\",
                Registry_Comments as \"Registry_Comments\",
                Registry_IsFinal as \"Registry_IsFinal\",
                Registry_CountIsBadVol as \"Registry_CountIsBadVol\",
                RegistrySubType_id as \"RegistrySubType_id\",
                Lpu_oid as \"Lpu_oid\",
                DispClass_id as \"DispClass_id\",
                Registry_ExportSign as \"Registry_ExportSign\",
                Registry_xmlExportPath as \"Registry_xmlExportPath\",
                Registry_IsZNO as \"Registry_IsZNO\",
                RegistryCheckStatus_id as \"RegistryCheckStatus_id\"
			from
				{$this->scheme}.v_Registry 
			where
				Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));
		if (empty($resp[0]['Registry_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		}

		// добавляем недостающие поля
		$resp[0]['pmUser_id'] = $data['pmUser_id'];

		// даты преобразуем в нормальные даты
		foreach($resp[0] as &$field) {
			if ($field instanceof DateTime) {
				$field = $field->format('Y-m-d H:i:s');
			}
		}

		return $this->saveUnionRegistry($resp[0]);
	}

	/**
	 * Установка статуса финального реестра
	 */
	public function setUnionRegistryStatus($data) {
		$registryStatusList = $this->getAllowedRegistryStatuses(array('RegistrySubType_id' => 2));

		if ( !in_array($data['RegistryStatus_id'], $registryStatusList) ) {
			return array(array('success' => false, 'Error_Msg' => "Недопустимый статус реестра"));
		}

		$response = array();

		foreach ( $data['Registry_ids'] as $Registry_id ) {
			$data['Registry_id'] = $Registry_id;

			if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id'])) {
				return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
			}

			// пересчитать все предварительные реестры
			$resp_reg = $this->queryResult("
				select
					Registry_id as \"Registry_id\"
				from
					{$this->scheme}.v_RegistryGroupLink
				where
					Registry_pid = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id']
			));

			foreach ( $resp_reg as $one_reg ) {
				$this->recountSumKolRegistry(array(
					'Registry_id' => $one_reg['Registry_id']
				));
			}

			$this->recountSumKolUnionRegistry(array(
				'Registry_id' => $data['Registry_id']
			));

			$query = "
					update {$this->scheme}.Registry
					set
						RegistryStatus_id = :RegistryStatus_id,
						Registry_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						Registry_id = :Registry_id
				returning RegistryStatus_id as \"RegistryStatus_id\", '' as \"Error_Code\", '' as \"Error_Msg\";
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
				'RegistryStatus_id' => $data['RegistryStatus_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}

			$response = $result->result('array');

			if ( !empty($response[0]['Error_Msg']) ) {
				return $response;
			}

			if ( $data['RegistryStatus_id'] == 4 ) {
				// пишем информацию о смене статуса в историю
				$this->dumpRegistryInformation(array('Registry_id' => $data['Registry_id']), 4);
			}
		}

		return $response;
	}

	/**
	 * Удаление связанных реестров
	 */
	function deleteUnionRegistrys($data)
	{

		$resp = $this->queryResult("
			select
				R.Registry_id as \"Registry_id\",
				R.OrgSmo_id as \"OrgSmo_id\"
			from
				{$this->scheme}.v_Registry R 
			where
				R.Registry_id = :Registry_id
				and R.RegistrySubType_id = 1
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (empty($resp[0]['Registry_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		}

		// получаем все объед. реестры

		$resp = $this->queryResult("
			select
				RGL.Registry_pid as \"Registry_pid\",
				RP.RegistryStatus_id as \"RegistryStatus_id\",
				RGL.RegistryGroupLink_id as \"RegistryGroupLink_id\"
			from
				{$this->scheme}.v_Registry RP 
				inner join {$this->scheme}.v_RegistryGroupLink RGL  on RGL.Registry_pid = RP.Registry_id
			where
				RGL.Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (empty($resp)) {
			return array('Error_Msg' => 'Нет связанных реестров по СМО');
		}

		foreach($resp as $respone) {
			if ($respone['RegistryStatus_id'] != 3) { // если не "В работе"
				return array('Error_Msg' => 'Удаление возможно, если все реестры по СМО находятся в статусе «В работе»');
			}
		}

		foreach($resp as $respone) {
			// удаляем связь
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                    
				from {$this->scheme}.p_RegistryGroupLink_del(
					RegistryGroupLink_id := :RegistryGroupLink_id);
			";

			$this->queryResult($query, array(
				'RegistryGroupLink_id' => $respone['RegistryGroupLink_id']
			));

			// если для объединённого реестра не осталось связей, удалим его.
			$resp_rgl = $this->queryResult("
				select RegistryGroupLink_id  as \"RegistryGroupLink_id\" from {$this->scheme}.v_RegistryGroupLink where Registry_pid = :Registry_pid limit 1
			", array(
				'Registry_pid' => $respone['Registry_pid']
			));

			if (empty($resp_rgl[0]['RegistryGroupLink_id'])) {
				// удаляем реестр
				$this->deleteUnionRegistry(array(
					'id' => $respone['Registry_pid'],
					'pmUser_id' => $data['pmUser_id']
				));
			} else {
				// иначе пересчитываем
				$this->recountSumKolUnionRegistry(array(
					'Registry_id' => $respone['Registry_pid']
				));
			}
		}

		// Для выбранного реестра происходит удаление всех финальных реестров созданных на его основе.
		return array('Error_Msg' => '');
	}

	/**
	 * Удаление случаев по СМО
	 */
	function deleteOrgSmoRegistryData($data)
	{
		$empty = true;

		$OrgSmo_ids = explode(',', $data['OrgSmo_ids']);
		foreach($OrgSmo_ids as $OrgSmo_id) {
			if (is_numeric($OrgSmo_id)) {
				$resp = $this->queryResult("
					select
						R.Registry_id as \"Registry_id\",
						R.RegistryStatus_id as \"RegistryStatus_id\",
						R.RegistryType_id as \"RegistryType_id\",
						to_char(r.Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
					from
						{$this->scheme}.v_Registry R 
					where
						R.Registry_id = :Registry_id
						and R.RegistrySubType_id = 1
				", array(
					'Registry_id' => $data['Registry_id']
				));

				if ($resp === false || empty($resp[0]['Registry_id'])) {
					return array('Error_Msg' => 'Ошибка получения данных по реестру');
				}

				if ($resp[0]['RegistryStatus_id'] != 3 && $resp[0]['RegistryStatus_id'] != 2) { // если не "В работе" и не "К оплате"
					return array('Error_Msg' => 'Действие доступно для предварительных реестров в статусе «В работе» или «К оплате»');
				}

				// Если реестр по СМО имеет статус к «Оплате», то необходимо вывести сообщение для пользователя
				// «Удаление невозможно реестр для СМО %наименование СМО%, № %номер реестра% от %дата реестра% находится в статусе «К оплате». Удаление производится только для реестров СМО со статусом «В работе».
				if ($resp[0]['RegistryStatus_id'] == 2) {
					$filter = " and RF.OrgSmo_id = :OrgSmo_id and RF.Registry_IsNotInsur = 1";
					if (!empty($data['Registry_IsNotInsur']) && $data['Registry_IsNotInsur'] == 2) {
						$filter = " and RF.Registry_IsNotInsur = 2"; // не застрахованные
					}

					if (!empty($data['Registry_IsZNO']) && $data['Registry_IsZNO'] == 2) {
						$filter .= " and RF.Registry_IsZNO = 2"; // ЗНО
					} else {
						$filter .= " and COALESCE(RF.Registry_IsZNO, 1) = 1";

					}

					$resp_rf = $this->queryResult("
						  select rf.Registry_id as \"Registry_id\",
                                 os.OrgSmo_Nick as \"OrgSmo_Nick\",
                                 rf.Registry_Num as \"Registry_Num\",
                                 to_char(rf.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
                                 rs.RegistryStatus_Name as \"RegistryStatus_Name\",
                                 rf.RegistryStatus_id as \"RegistryStatus_id\"
                          from {$this->scheme}.v_RegistryGroupLink RGL
                               inner join {$this->scheme}.v_Registry RF on RF.Registry_id = RGL.Registry_pid
                               left join v_OrgSmo os on os.OrgSmo_id = rf.OrgSmo_id
                               left join v_RegistryStatus rs on rs.RegistryStatus_id = rf.RegistryStatus_id
                          where RGL.Registry_id =:Registry_id 
                          {$filter}
                          limit 1
					", array(
						'Registry_id' => $resp[0]['Registry_id'],
						'OrgSmo_id' => $OrgSmo_id
					));

					if (!empty($resp_rf[0]['Registry_id']) && $resp_rf[0]['RegistryStatus_id'] <> 3) {
						return array('Error_Msg' => "Удаление невозможно, реестр для СМО {$resp_rf[0]['OrgSmo_Nick']}, № {$resp_rf[0]['Registry_Num']} от {$resp_rf[0]['Registry_accDate']} находится в статусе «{$resp_rf[0]['RegistryStatus_Name']}». Удаление производится только для реестров СМО со статусом «В работе»");
					}
				}

				$this->setRegistryParamsByType($resp[0]);

				if (!empty($data['Registry_IsNotInsur']) && $data['Registry_IsNotInsur'] == 2) {
					// если реестр по СМО, то не зависимо от соц. статуса
					if ($this->RegistryType_id == 6) {
						$filter = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))"; // незастрахованные
					} else {
						$filter = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))"; // незастрахованные
					}
				} else if ($OrgSmo_id == 8) {
					// инотеры
					$filter = " and RD.Polis_id IS NOT NULL";
					$filter .= " and COALESCE(os.OrgSMO_RegNomC,'')=''";

				}
				else {
					// @task https://redmine.swan.perm.ru//issues/109876
					if ( $resp[0]['Registry_accDate'] >= '2017-05-25' && $OrgSmo_id == 8001229 ) {
						$filter = " and RD.OrgSmo_id in (8000233, 8001229)";
					}
					else if ( $resp[0]['Registry_accDate'] >= '2018-10-25' && $OrgSmo_id == 8001750 ) {
						$filter = " and RD.OrgSmo_id in (8000227, 8001750)";
					}
					else {
						$filter = " and RD.OrgSmo_id = :OrgSmo_id";
					}
				}

				if (!empty($data['Registry_IsZNO']) && $data['Registry_IsZNO'] == 2) {
					$filter .= " and RD.RegistryData_IsZNO in (2, 3) ";
				} else {
					$filter .= " and COALESCE(RD.RegistryData_IsZNO, 1) = 1 ";

				}

				// достаём записи для удаления
				$resp_rd = $this->queryResult("
					select distinct
						RD.Evn_id as \"Evn_id\",
						RD.Registry_id as \"Registry_id\"
					from
						{$this->scheme}.v_Registry R 
						inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = R.Registry_id
						left join v_OrgSMO OS  on OS.OrgSmo_id = RD.OrgSmo_id
					where
						R.Registry_id = :Registry_id
						{$filter}
				", array(
					'Registry_id' => $data['Registry_id'],
					'OrgSmo_id' => $OrgSmo_id
				));

				if (!empty($resp_rd)) {
					$empty = false;

					foreach($resp_rd as $resp_rdone) {
						$this->deleteRegistryData(array(
							'EvnIds' => array($resp_rdone['Evn_id']),
							'Registry_id' => $resp_rdone['Registry_id'],
							'RegistryType_id' => $resp[0]['RegistryType_id'],
							'RegistryData_deleted' => 2
						));
					}

					$this->refreshRegistry(array(
						'Registry_id' => $resp[0]['Registry_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					// пересчёт реестра по СМО
					if (!empty($resp_rf[0]['Registry_id'])) {
						$this->recountSumKolUnionRegistry(array(
							'Registry_id' => $resp_rf[0]['Registry_id']
						));
					}
				}
			}
		}

		if ($empty) {
			return array('Error_Msg' => 'Нет случаев для удаления');
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Проверка случаев на включение в реестры по СМО
	 */
	function checkIncludeInUnioinRegistry($data)
	{

		$resp = $this->queryResult("
			select
				R.Registry_id as \"Registry_id\",
				R.RegistryType_id as \"RegistryType_id\",
				R.Registry_Num as \"Registry_Num\",
				to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
			from
				{$this->scheme}.v_Registry R 
			where
				R.Registry_id = :Registry_id
				and R.RegistrySubType_id = 1
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if ($resp === false || empty($resp[0]['Registry_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по реестру');
		}

		$this->setRegistryParamsByType($resp[0]);

		$notStrah = "OR (((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61')) and RP.Registry_IsNotInsur = 2) -- незастрахованные";
		// если реестр по СМО, то не зависимо от соц. статуса
		if ($this->RegistryType_id == 6) {
			$notStrah = "OR ((RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61')) and RP.Registry_IsNotInsur = 2) -- незастрахованные";
		}

		$resp_os = $this->queryResult("
			  select distinct case
                                when OS.OrgSmo_id is null then 'Незастрахованные'
                                when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
                                else 'Инотерриториальные'
                              end as \"OrgSmo_Nick\"
              from {$this->scheme}.v_Registry R
                   inner join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = R.Registry_id and COALESCE(RD.RegistryData_IsBadVol, 1) = 1 -- только на случаи без превышения
                   left join v_OrgSMO OS on OS.OrgSmo_id = RD.OrgSmo_id
              where R.Registry_id =:Registry_id and
                    not exists (
                                 select RGL.RegistryGroupLink_id
                                 from {$this->scheme}.v_RegistryGroupLink RGL
                                      inner join {$this->scheme}.v_Registry RP on RP.Registry_id = RGL.Registry_pid
                                 where RGL.Registry_id = R.Registry_id and
                                       (RP.OrgSmo_id = RD.OrgSmo_id -- реестр по смо
        --                               {$notStrah} OR
                                       (COALESCE(CAST(os.OrgSMO_RegNomC as varchar), '') = '' and
                                       RD.Polis_id is not null and
                                       RP.OrgSmo_id = 8) -- инотеры
                                       )
                    )
              order by \"OrgSmo_Nick\"
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (empty($resp_os)) {
			return array('Error_Msg' => '', 'Alert_Msg' => "Все записи реестра {$resp[0]['Registry_Num']} {$resp[0]['Registry_accDate']} включены в реестры по СМО");
		} else {
			$smos = "";
			foreach($resp_os as $resp_osone) {
				if (!empty($smos)) {
					$smos .= ",<br />";
				}
				$smos .= $resp_osone['OrgSmo_Nick'];
			}
			return array('Error_Msg' => '', 'Alert_Msg' => "Внимание! Часть записей реестра {$resp[0]['Registry_Num']} {$resp[0]['Registry_accDate']} не включена в реестры по СМО.  Необходимо провести переформирование реестров по следующим организациям: <br />{$smos}");
		}
	}

	/**
	 * Проверка включен ли реестр в объединённый
	 */
	function checkRegistryInGroupLink($data) {

		$data['Registry_id'] = $this->getFirstResultFromQuery("SELECT Registry_pid  as \"Registry_pid\" FROM {$this->scheme}.v_RegistryGroupLink  WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));



		if (!empty($data['Registry_id'])) {
			return true;
		}

		return false;
	}

	/**
	 *	Комментарий
	 */
	function loadRegistryForImportXml($data)
	{
		return $this->queryResult("
			select
                Registry_id as \"Registry_id\",
				RegistryType_id as \"RegistryType_id\",
				Registry_Num as \"Registry_Num\",
				to_char(Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\",
				OrgSmo_id as \"OrgSmo_id\",
				RegistrySubType_id as \"RegistrySubType_id\",
				Registry_IsNotInsur as \"Registry_IsNotInsur\",
				Registry_IsNew as \"Registry_IsNew\"			
			from
				{$this->scheme}.v_Registry 
			where
				Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));
	}

	/**
	 * Пересчёт объёмов МП
	 */
	function refreshRegistryVolumes($data)
	{
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"            
				from {$this->scheme}.p_Registry_Volume_Refresh(Registry_id = :Registry_id);
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Устанавливает XSD-схему в зависимости от типа реестра
	 */
	protected function setXSDSchema($RegistryType_id, $Registry_IsNew = null, $Registry_IsZNO = null, $isBud = false) {
		if ($isBud) {
			$this->H_xsd = '/documents/xsd/F-1.xsd';
			$this->L_xsd = '/documents/xsd/F-2.xsd';
		}
		else if ( $Registry_IsZNO == 2 ) {
			$this->H_xsd = '/documents/xsd/OMS-D1-C-NEW.xsd';
			$this->L_xsd = '/documents/xsd/OMS-D2-NEW.xsd';
		}
		else {
			$newRegistryModificator = ($Registry_IsNew == 2 ? '-NEW' : '');

			switch ($RegistryType_id) {
				case 1: // stac
				case 2: // polka
				case 6: // smp
					$this->H_xsd = '/documents/xsd/OMS-D1-1' . $newRegistryModificator . '.xsd';
					$this->L_xsd = '/documents/xsd/OMS-D2' . $newRegistryModificator . '.xsd';
					break;

				case 7: //dd
				case 9: //orp
				case 17: //prof
					$this->H_xsd = '/documents/xsd/OMS-D1-2' . $newRegistryModificator . '.xsd';
					$this->L_xsd = '/documents/xsd/OMS-D2' . $newRegistryModificator . '.xsd';
					break;

				case 14: // htm
					$this->H_xsd = '/documents/xsd/OMS-D1-3' . $newRegistryModificator . '.xsd';
					$this->L_xsd = '/documents/xsd/OMS-D2' . $newRegistryModificator . '.xsd';
					break;
			}
		}

		return true;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml($data) {
		$this->load->library('parser');

		$withSign = $data['withSign'];
		
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->library('textlog', array('file'=>'exportRegistryToXml.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');
		
		$reg_endmonth = date('ym'); // savage: для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
		$type = 0;
	
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$res = $this->GetRegistryXmlExport($data);
		
		//Временная заглушка - чтобы каждый раз происходило формирование архивов
		//$res[0]['Registry_xmlExportPath'] = null;

		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return array('Error_Msg' => 'Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
		}

		$data['DispClass_id'] = $res[0]['DispClass_id'];
		$data['OrgSmo_id'] = $res[0]['OrgSmo_id'];
		$data['Registry_IsNew'] = $res[0]['Registry_IsNew'];
		$data['Registry_IsNotInsur'] = $res[0]['Registry_IsNotInsur'];
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$data['RegistrySubType_id'] = $res[0]['RegistrySubType_id'];
		$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];

		$this->textlog->add('exportRegistryToXml: Тип оплаты реестра: ' . $data['PayType_SysNick']);

		if ( $data['Registry_IsNew'] == 2 ) {
			$this->textlog->add('exportRegistryToXml: Новый реестр.');
		}

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return array('Error_Msg' => 'Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
		}
		else if ((strlen($res[0]['Registry_xmlExportPath'])>0) && $data['OverrideExportOneMoreOrUseExist'] == 1) // если уже выгружен реестр
		{
			$link = $res[0]['Registry_xmlExportPath'];
			if ( empty($withSign) ) {
				if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($link));
					return array('success' => true, 'filebase64' => $filebase64);
				} else {
					$this->textlog->add('exportRegistryToXml: вернули ссылку ' . $link);
					return array('success' => true, 'Link' => $link);
				}
			}
			else {
				$this->textlog->add('exportRegistryToXml: Возвращаем пользователю хэш');
				// получаем хэш с помощью openssl
				$openssl_path = $this->config->item('OPENSSL_PATH');
				$filehash = exec("\"{$openssl_path}\" dgst -md_gost94 {$res[0]['Registry_xmlExportPath']}", $output);
				$this->textlog->add('exportRegistryToXml: Получаем хэш:'."\"{$openssl_path}\" dgst -md_gost94 {$res[0]['Registry_xmlExportPath']}".' Получили: '.$filehash);
				// обрезаем ненужное
				$filehash = preg_replace('/.*= /ui','', $filehash);
				$binaryhash = pack("H*" , $filehash);
				$filehashb64 = base64_encode($binaryhash);
				// возвращаем пользователю хэш
				return array('success' => true, 'filehash' => $filehashb64);
			}
		}
		else {
			$reg_endmonth = $res[0]['Registry_endMonth'];
			$type = $res[0]['RegistryType_id'];
			$this->textlog->add('exportRegistryToXml: Тип реестра '.$res[0]['RegistryType_id']);
		}

		if ( !in_array($type, $this->getAllowedRegistryTypes()) ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Данный тип реестров не обрабатывается.');
			return array('Error_Msg' => 'Данный тип реестров не обрабатывается.');
		}

		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->SetXmlExportStatus($data);
			
			$SCHET = $this->_loadRegistrySCHETForXmlUsing($type, $data, $data['RegistrySubType_id'] == 2);

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				switch ($type) {
					case 1: // stac
						$first_code = 'S';
						break;
					case 2: // polka
						$first_code = 'P';
						break;
					case 14: // вмп
						$first_code = 'V';
						break;

					default:
						return false;
						break;
				}
			} else {
				if ($data['Registry_IsNew'] == 2) {
					switch ($type) {
						case 1:
						case 2:
						case 6:
							if ( $data['Registry_IsZNO'] == 2 ) {
								$pcode = 'LC';
								$scode = 'C';
							}
							else {
								$pcode = 'L';
								$scode = 'H';
							}
							break;

						case 7:
							if ($data['DispClass_id'] == 2) {
								$pcode = 'LV';
								$scode = 'DV';
							} else {
								$pcode = 'LP';
								$scode = 'DP';
							}
							break;

						case 9:
							$pcode = 'LS';
							$scode = 'DS';
							break;

						case 14:
							$pcode = 'LT';
							$scode = 'T';
							break;

						case 17:
							if ($data['DispClass_id'] == 5) {
								$pcode = 'LOV';
								$scode = 'DOV';
							} else if ($data['DispClass_id'] == 10) {
								$pcode = 'LON';
								$scode = 'DON';
							} else {
								$pcode = 'LO';
								$scode = 'DO';
							}
							break;
					}
				} else {
					switch ($type) {
						case 1:
						case 2:
						case 6:
							$pcode = 'LY';
							$scode = 'HY';
							break;

						case 7:
						case 9:
						case 17:
							$pcode = 'LD';
							$scode = 'HD';
							break;

						case 14:
							$pcode = 'LT';
							$scode = 'HT';
							break;
					}
				}
			}

			if ($data['Registry_IsNew'] == 2 || in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$postfix = '';
				if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
					$postfix = '_bud';
				}
				$xml_file_body = "registry_ufa_1_body" . $postfix;
				$xml_file_header = "registry_ufa_1_header" . $postfix;
				$xml_file_footer = "registry_ufa_1_footer";

				$xml_file_person_body = "registry_ufa_2_body";
				$xml_file_person_header = "registry_ufa_2_header" . $postfix;
				$xml_file_person_footer = "registry_ufa_2_footer";
			}
			else {
				$xml_file_person_body = "registry_ufa_person_body";
				$xml_file_person_header = "registry_ufa_person_header";
				$xml_file_person_footer = "registry_ufa_person_footer";

				$xml_file_footer = "registry_ufa_all_footer";

				switch ( $type ) {
					case 1:
					case 2:
					case 6:
						$xml_file_body = "registry_ufa_pl_body";
						$xml_file_header = "registry_ufa_pl_header";
					break;

					case 7:
					case 9:
					case 17:
						$xml_file_body = "registry_ufa_disp_body";
						$xml_file_header = "registry_ufa_disp_header";
					break;

					case 14:
						$xml_file_body = "registry_ufa_hmp_body";
						$xml_file_header = "registry_ufa_hmp_header";
					break;
				}
			}

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];

			//Проверка на наличие созданной ранее директории
			if ( !file_exists(EXPORTPATH_REGISTRY.$out_dir) ) {
				mkdir( EXPORTPATH_REGISTRY.$out_dir );
			}
			$this->textlog->add('exportRegistryToXml: создали каталог ' . EXPORTPATH_REGISTRY . $out_dir);

			if ( !empty($SCHET[0]['KatNasel_Liter']) ) {
				$Liter = $SCHET[0]['KatNasel_Liter'];
			} else {
				$Liter = 'T';
			}

			//Task# Установка отчётного месяца и года реестра + установка номера пачки реестра + влияние на имя xml (по номеру пачки)
			$addColumns = $this->getRegistryTwoCols($data);

			if ( !is_array($addColumns) || !is_numeric($addColumns[0]["Registry_pack"]) ) {
				throw new Exception('Ошибка при чтении номера пачки реестра!');
			}

			$pack_index = $addColumns[0]["Registry_pack"];
			//Если установлена oerderDate - то в названиях файла и в xml нужно заменить MOUNTH и YEAR 
			if ( !empty($addColumns[0]["Registry_orderDate"]) ) {
				$orderDate = get_object_vars($addColumns[0]["Registry_orderDate"]);
				if ( !is_null($orderDate['date']) ) {
					$tDate = date_parse($orderDate['date']);
					$tDate['year'] = substr($tDate['year'], 2, 2);
					$tDate['month'] = (strlen($tDate['month']) == 2) ? $tDate['month'] : str_pad($tDate['month'], 2, "0", STR_PAD_LEFT);
					$reg_endmonth = $tDate['year'] . $tDate['month'];
				}
			}

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$f_type = 'F'; // федеральный
				if ($data['PayType_SysNick'] == 'bud') {
					$f_type = 'L'; // местный
				}
				// случаи
				$file_re_data_sign = $f_type . "_HM" . $SCHET[0]['PODR'] . "Z02_" . $reg_endmonth . $pack_index;
				// перс. данные
				$file_re_pers_data_sign = $f_type . "_LM" . $SCHET[0]['PODR'] . "Z02_" . $reg_endmonth . $pack_index;
			} else {
				// случаи
				$file_re_data_sign = $scode . "M" . $SCHET[0]['PODR'] . $Liter . $SCHET[0]['PLAT'] . "_" . $reg_endmonth . $pack_index;
				// перс. данные
				$file_re_pers_data_sign = $pcode . "M" . $SCHET[0]['PODR'] . $Liter . $SCHET[0]['PLAT'] . "_" . $reg_endmonth . $pack_index;
			}

			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";
			// временный файл для случаев
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";                
            
			$this->textlog->add('exportRegistryToXml: Определили наименования файлов: ' . $file_re_data_name . ' и ' . $file_re_pers_data_name);
			$this->textlog->add('exportRegistryToXml: Создаем XML файлы на диске');

			// Заголовок для файла с перс. данными
			$ZGLV = array(
				array(
					'VERSION' => (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) ? '1.0' : '3.2',
					'FILENAME' => $file_re_pers_data_sign,
					'FILENAME1' => $file_re_data_sign
				)
			);

			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$xml_file_person_header, $ZGLV[0], true);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			// Получаем данные
			$getDataMethod = '_loadRegistryDataForXmlUsing';

			if ($data['Registry_IsNew'] == 2 || in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				if ($data['RegistrySubType_id'] == 2 || in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
					$getDataMethod = '_loadRegistryDataForXmlByFunc';
				}
				else {
					$getDataMethod .= 'New';
				}
			}

			$this->_resetPersCnt();
			$this->_resetZapCnt();
			$response = $this->$getDataMethod($type, $data, $file_re_data_name_tmp, $file_re_pers_data_name, $xml_file_body, $xml_file_person_body, $data['RegistrySubType_id'] == 2);
			$this->textlog->add('exportRegistryToXml: ' . $getDataMethod . ': Выгрузили данные');
	
			if ( $response === false ) {
				throw new Exception($this->error_deadlock);
			}

			$this->textlog->add('exportRegistryToXml: Получили все данные из БД');
			$this->textlog->add('exportRegistryToXml: Количество записей реестра = ' . $response['zapCnt']);
			$this->textlog->add('exportRegistryToXml: Количество людей в реестре = ' . $response['persCnt']);

			$SCHET[0]['VERSION'] = (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) ? '1.0' : '3.1';
			$SCHET[0]['SD_Z'] = $response['zapCnt'];
			$SCHET[0]['FILENAME'] = $file_re_data_sign;

			if ( !empty($addColumns[0]["Registry_orderDate"]) ) {
				$orderDate = get_object_vars($addColumns[0]["Registry_orderDate"]);
				$tDate = date_parse($orderDate['date']);

				$SCHET[0]['YEAR'] = $tDate['year'];
				$SCHET[0]['MONTH'] = $tDate['month'];
			}

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $xml_file_header, $SCHET[0], true, false);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);
			unset($xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($file_re_data_name_tmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($file_re_data_name_tmp, "rb");

				if ( $fh === false ) {
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($file_re_data_name_tmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл');

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/'.$xml_file_footer, array(), true, false);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/'.$xml_file_person_footer, array(), true);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
			$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);

			//--Task#18964 ФЛК контроль
			//Тест ФЛК
			//$xml = file_get_contents(EXPORTPATH_REGISTRY.'/HM22S02002_12051.xml');
			//$xml_pers = file_get_contents(EXPORTPATH_REGISTRY.'/LM22S02002_12051.xml');

			$this->setXSDSchema($type, $data['Registry_IsNew'], $data['Registry_IsZNO'], (in_array($data['PayType_SysNick'], array('bud', 'fbud'))));

			$H_registryValidate = true;
			$L_registryValidate = true;
			if (!in_array($data['PayType_SysNick'], array('bud', 'fbud')) && array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]) {
				$settingsFLK = $this->loadRegistryEntiesSettings($res[0]);
				if(count($settingsFLK) > 0){
					$upload_path = 'RgistryFields/';
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;	

					if($tplEvnDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplEvnDataXSD;
						
						//Проверяем валидность 1го реестра
						//Путь до шаблона
						$H_xsd_tpl = $fileEvnDataXSD;
						//Файл с ошибками, если понадобится
						$H_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_data_sign.'.html';
						//Проверка
						$H_registryValidate = $this->Reconciliation($file_re_data_name, $H_xsd_tpl, 'file', $H_validate_err_file);
					}
					
					if($tplPersonDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$tplPersonDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplPersonDataXSD;
						
						//Проверяем 2й реестр
						//Путь до шаблона
						$L_xsd_tpl = $tplPersonDataXSD;
						//Файл с ошибками, если понадобится
						$L_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_pers_data_sign.'.html';
						//Проверка
						$L_registryValidate = $this->Reconciliation($file_re_pers_data_name, $L_xsd_tpl, 'file', $L_validate_err_file);
					}
				}
			} else if (!in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				// Работаем по старой схеме
				// @task https://redmine.swan.perm.ru/issues/120929

				//Проверяем валидность 1го реестра
				//Путь до шаблона
				$H_xsd_tpl = $_SERVER['DOCUMENT_ROOT'].$this->H_xsd;
				//Файл с ошибками, если понадобится
				$H_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_data_sign.'.html';
				//Проверка
				$H_registryValidate = $this->Reconciliation($file_re_data_name, $H_xsd_tpl, 'file', $H_validate_err_file);
				//Проверяем 2й реестр
				//Путь до шаблона
				$L_xsd_tpl = $_SERVER['DOCUMENT_ROOT'].$this->L_xsd;
				//Файл с ошибками, если понадобится
				$L_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_pers_data_sign.'.html';
				//Проверка
				$L_registryValidate = $this->Reconciliation($file_re_pers_data_name, $L_xsd_tpl, 'file', $L_validate_err_file);
			}

			$base_name = $_SERVER["DOCUMENT_ROOT"]."/";

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$file_zip_sign = $first_code . $SCHET[0]['PODR'];
			} else if ( in_array($type, array(2, 6, 7, 9, 17)) ) {
				$file_zip_sign = "P".$SCHET[0]['PODR'].'_'.$SCHET[0]['PLAT'];
			} elseif ( in_array($type, array(1, 14)) ) {
				$file_zip_sign = "S".$SCHET[0]['PODR'].'_'.$SCHET[0]['PLAT'];
			} else {
				$file_zip_sign = "M".$SCHET[0]['CODE_MO']."T02_".$reg_endmonth."1";
			}
			
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP ' . $file_zip_name);
		
			$data['Status'] = $file_zip_name;
			$this->SetXmlExportStatus($data);
	
			/**/
			if(!$H_registryValidate  && !$L_registryValidate){
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				return array(
					'success' => false, 
					'Error_Msg' => 'Реестр не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>,
						<a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>, 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
					'Error_Code' => 20
				);
	
			}
			elseif(!$H_registryValidate){
				//Скинули статус 
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);

				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
                
				return array(
				   'success' => false, 
				   'Error_Msg' => 'Файл H реестра не прошёл проверку ФЛК: <a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>
						(<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>),
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
				   'Error_Code' => 21
				);
            }
            elseif(!$L_registryValidate){
                //Скинули статус  
				$data['Status'] = NULL;
				$this->SetXmlExportStatus($data);                
		       
				unlink($file_re_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
                
				return array(
				   'success' => false, 
				   'Error_Msg' => 'Файл L реестра не прошёл ФЛК: <a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a> 
						(<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>), 
						<a href="'.$file_zip_name.'" target="_blank">zip</a>',
				   'Error_Code' => 22
				);
            }
            else {
				unlink($file_re_data_name);
				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

				// Пишем информацию о выгрузке в историю
				$this->dumpRegistryInformation($data, 2);

				if (empty($withSign)) {
					if (!empty($data['forSign'])) {
						$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
						$filebase64 = base64_encode(file_get_contents($file_zip_name));
						return array('success' => true, 'filebase64' => $filebase64);
					} else {
						$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
						return array('success' => true, 'Link' => $file_zip_name);
					}
				}
				else {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю хэш');
					// получаем хэш с помощью openssl
					$openssl_path = $this->config->item('OPENSSL_PATH');
					$filehash = exec("\"{$openssl_path}\" dgst -md_gost94 {$file_zip_name}", $output);
					$this->textlog->add('exportRegistryToXml: Получаем хэш:'."\"{$openssl_path}\" dgst -md_gost94 {$file_zip_name}".' Получили: '.$filehash);
					// обрезаем ненужное
					$filehash = preg_replace('/.*= /ui','', $filehash);
					$binaryhash = pack("H*" , $filehash);
					$filehashb64 = base64_encode($binaryhash);
					// возвращаем пользователю хэш
					return array('success' => true, 'filehash' => $filehashb64);
				}
			}
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->SetXmlExportStatus($data);
			$this->textlog->add("exportRegistryToXml:".toUtf($e->getMessage()));
			return array('success' => false, 'Error_Msg' => toUtf($e->getMessage()));
		}
	}

	/**
	* некоторые символы в файлах формата XML кодируются по особому (refs #8013)
	*/
	function encodeForXmlExport(&$word) 
	{
		$word = str_replace('&','&amp;amp;',$word);
		$word = str_replace('"','&amp;quot;',$word);
		$word = str_replace('\'','&amp;apos;',$word);
		$word = str_replace('<','&amp;lt;',$word);
		$word = str_replace('>','&amp;gt;',$word);
		$word = str_replace('&amp;lt;CODE&amp;gt;3&amp;lt;/CODE&amp;gt;','<CODE>3</CODE>',$word); // костыль для #12078
	}

	/**
	 *  ФЛК контроль 
	 */
	function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
    {
		libxml_use_internal_errors(true);  
		$xml = new DOMDocument();
	
		if($type == 'file'){
			$xml->load($xml_data); 
		}
		elseif($type == 'string'){
			$xml->loadXML($xml_data);   
		}
	
		if (!$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();
	
			file_put_contents($output_file_name, $res_errors);

			return false;
		}
		else
			return true;
	}

	/**
    * ФЛК контроль
    * Метод для формирования листа ошибок при сверке xml по шаблоны xsd
    * Task#18694
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
     *  Получение наименования СМО реестра
     */ 
    function getSmoName($data){
        $queryParams = array('Registry_id'=>$data['Registry_id']);
        $query = "select case when R.OrgSmo_Name is null then 'Без СМО' else R.OrgSmo_Name end as \"Smo_Name\" from r2.v_Registry R  where R.Registry_id = :Registry_id limit 1";

        
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

	/**
	 *	Получение списка случаев реестра
	 */
	public function loadRegistryData($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( isset($data['start']) && isset($data['limit']) && !($data['start'] >= 0 && $data['limit'] >= 0) )  {
			return false;
		}

		// Получаем данные по реестру
		$regData = $this->queryResult("
			select
				Registry_IsNotInsur as \"Registry_IsNotInsur\",
				Registry_IsZNO as \"Registry_IsZNO\",
				OrgSmo_id as \"OrgSmo_id\",
				RegistryType_id as \"RegistryType_id\",
				to_char(Registry_accDate, 'YYYY-MM-DD') as \"Registry_accDate\"
			from {$this->scheme}.v_Registry 
			where Registry_id = :Registry_id
		", array('Registry_id' => $data['Registry_id']));

		if ( empty($regData[0]) ) {
			return false; // Вывод ошибки не нужен, реестр находится на формировании
		}

		$data['RegistryType_id'] = $regData[0]['RegistryType_id'];

		$OrgSmo_id = $regData[0]['OrgSmo_id'];
		$Registry_accDate = $regData[0]['Registry_accDate'];
		$Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];

		$this->setRegistryParamsByType($data);

		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id'],
			'Registry_IsZNO' => $regData[0]['Registry_IsZNO'],
		);
		$fieldsList = array();
		$filterList = array();
		$joinList = array();

		if ( !empty($data['filterIsZNO']) && $data['filterIsZNO'] == 2 ) {
			$filterList[] = "RD.RegistryData_IsZNO in (2, 3)";
		}

		if ( !empty($data['Person_SurName']) ) {
			$filterList[] = "lower(RD.Person_SurName) LIKE lower(:Person_SurName)";

			$params['Person_SurName'] = trim($data['Person_SurName'])."%";
		}

		if ( !empty($data['Person_FirName']) ) {
			$filterList[] = "lower(RD.Person_FirName) LIKE lower(:Person_FirName)";

			$params['Person_FirName'] = trim($data['Person_FirName'])."%";
		}

		if ( !empty($data['Person_SecName']) ) {
			$filterList[] = "lower(RD.Person_SecName) LIKE lower(:Person_SecName)";

			$params['Person_SecName'] = trim($data['Person_SecName'])."%";
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
			$filterList[] = "RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		if ( !empty($data['LpuBuilding_id']) ) {
			$filterList[] = "LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		
		$joinList[] = "left join v_UslugaComplex U  on COALESCE(RD.Usluga_id, RD.UslugaComplex_id) = U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)";
		$joinList[] = "left join v_Diag D  on RD.Diag_id =  D.Diag_id";
		$joinList[] = "left join v_EvnSection es  on ES.EvnSection_id = RD.Evn_id";
		$joinList[] = "left join v_MesOld m  on m.Mes_id = ES.Mes_id";
		$joinList[] = "left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id";
		$joinList[] = "left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id";
		$joinList[] = "left join v_MesOld Mes  on Mes.Mes_id = rd.MesItog_id";

		$fieldsList[] = "m.Mes_Code as \"Mes_Code\"";
		$fieldsList[] = "U.UslugaComplex_Code as \"Usluga_Code\"";
		$fieldsList[] = "D.Diag_Code as \"Diag_Code\"";
		$fieldsList[] = "case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as \"Paid\"";
		$fieldsList[] = "LB.LpuBuilding_Name as \"LpuBuilding_Name\"";
		$fieldsList[] = "Mes.Mes_Code || COALESCE(' ' || Mes.MesOld_Num, '') as \"Mes_Code_KSG\"";
		$fieldsList[] = "RD.Mes_Code_KPG as \"Mes_Code_KPG\"";

		if ( $this->RegistryDataObject == 'RegistryDataEvnPS' ) {
			$joinList[] = "left join v_HTMedicalCareClass htm  on htm.HTMedicalCareClass_id = rd.HTMedicalCareClass_id";
			$joinList[] = "left join v_VolumeType vt2  on vt2.VolumeType_id = rd.VolumeType_sid";

			$fieldsList[] = "htm.HTMedicalCareClass_GroupCode as \"HTMedicalCareClass_GroupCode\"";
			$fieldsList[] = "htm.HTMedicalCareClass_Name as \"HTMedicalCareClass_Name\"";
			$fieldsList[] = "case when rd.RegistryDataEvnPS_IsBadVolSec = 2 then '<b>' + vt2.VolumeType_Code + '</b>' else vt2.VolumeType_Code end as \"VolumeType_Code2\"";

			if ( !empty($data['VolumeType_id']) ) {
				$filterList[] = "(RD.VolumeType_id = :VolumeType_id OR RD.VolumeType_sid = :VolumeType_id)";
				$params['VolumeType_id'] = $data['VolumeType_id'];
			}
		}
		else {
			if ( !empty($data['VolumeType_id']) ) {
				$filterList[] = "RD.VolumeType_id = :VolumeType_id";
				$params['VolumeType_id'] = $data['VolumeType_id'];
			}
		}

		$joinList[] = "left join v_VolumeType vt  on vt.VolumeType_id = rd.VolumeType_id";

		$fieldsList[] = "vt.VolumeType_Code as \"VolumeType_Code\"";

		if( !empty($data['RegistryData_IsBadVol']) && $data['RegistryData_IsBadVol'] == 2 ) {
			$filterList[] = "RD.RegistryData_IsBadVol = 2";
		}
		else {
			$filterList[] = "COALESCE(RD.RegistryData_IsBadVol, 1) = 1";

		}

		if ( $data['filterRecords'] == 2 ) {
			$filterList[] = "COALESCE(RD.Paid_id, 1) = 2";

		}
		else if ( $data['filterRecords'] == 3 ) {
			$filterList[] = "COALESCE(RD.Paid_id, 1) = 1";

		}

		if (!empty($data['filterIsEarlier'])) {
			if ($data['filterIsEarlier'] == 2) {
				$filterList[] = "RETLast.RegistryErrorTFOMS_id is not null";
			} else if ($data['filterIsEarlier'] == 3) {
				$filterList[] = "RETLast.RegistryErrorTFOMS_id is null";
			}
		}
		
		$select_uet = "RD.RegistryData_KdFact as \"RegistryData_Uet\"";
		if ( in_array($this->RegistryType_id, array(2, 16)) ) {
			$select_uet = "RD.EvnVizit_UetOMS as \"RegistryData_Uet\"";
		}
		$fieldsList[] = $select_uet;

		if ( in_array($data['RegistryType_id'], array(7, 9, 12, 17)) ) {
			$joinList[] = "left join v_EvnPLDisp epd  on epd.EvnPLDisp_id = RD.Evn_rid";

			$fieldsList[] = "epd.DispClass_id as \"DispClass_id\"";
		}

		if ( !empty($data['OrgSmo_id']) ) {
			if ( $data['OrgSmo_id'] == 8 ) {
				$filterList[] = "COALESCE(os.OrgSMO_RegNomC, '') = '' and RD.Polis_id IS NOT NULL";

			}
			else {
				if ( $Registry_accDate >= '2017-05-25' && $data['OrgSmo_id'] == 8000233 ) {
					$filterList[] = "RD.OrgSmo_id = -1";
				}
				else if ( $Registry_accDate >= '2017-05-25' && $data['OrgSmo_id'] == 8001229 ) {
					$filterList[] = "RD.OrgSmo_id in (8000233, 8001229)";
				}
				else if ( $Registry_accDate >= '2018-10-25' && $data['OrgSmo_id'] == 8000227 ) {
					$filterList[] = "RD.OrgSmo_id = -1";
				}
				else if ( $Registry_accDate >= '2018-10-25' && $data['OrgSmo_id'] == 8001750 ) {
					$filterList[] = "RD.OrgSmo_id in (8000227, 8001750)";
				}
				else {
					$filterList[] = "RD.OrgSmo_id = :OrgSmo_id";
					$params['OrgSmo_id'] = $data['OrgSmo_id'];
				}
			}
		}

		$joinList[] = "left join v_OrgSmo os  on os.OrgSmo_id = RD.OrgSmo_id";

		$fieldsList[] = "
			case
				when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
				when RD.Polis_id IS NOT NULL and COALESCE(os.OrgSMO_RegNomC, '') = '' then 'Инотерриториальные'

				else ''
			end as \"OrgSmo_Nick\"
		";
		$fieldsList[] = "COALESCE(RD.RegistryData_Sum_R, 0) as \"RegistryData_Sum_R\"";

		$fieldsList[] = "COALESCE(RD.Paid_id,1) as \"RegistryData_IsPaid\"";


		$evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');

		$from = "
			{$this->scheme}.v_Registry R 
			inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = R.Registry_id
		";

		if (
			!empty($data['RegistrySubType_id'])
			&& $data['RegistrySubType_id'] == 2
		) {
			// для финального берём по другому
			if ( $Registry_IsNotInsur == 2 ) {
				// если реестр по СМО, то не зависимо от соц. статуса
				if ( $this->RegistryType_id == 6 ) {
					$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				}
				else {
					$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079, 112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code = '61'))";
				}
			}
			else if ( $OrgSmo_id == 8 ) {
				// инотеры
				$filter_rd = " and RD.Polis_id IS NOT NULL";
				$filterList[] = "COALESCE(os.OrgSMO_RegNomC, '') = ''";

			}
			else {
				// @task https://redmine.swan.perm.ru//issues/109876
				if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8000233 ) {
					return false;
				}
				else if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8001229 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
				}
				else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8000227 ) {
					return false;
				}
				else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8001750 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
				} else {
					$filter_rd = " and RD.OrgSmo_id = R.OrgSmo_id";
				}
			}

			$from = "
				{$this->scheme}.v_Registry R 
				inner join {$this->scheme}.v_RegistryGroupLink RGL  on RGL.Registry_pid = R.Registry_id
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Registry_id = RGL.Registry_id {$filter_rd}

			";

			if ( $data['filterRecords'] == 2 ) {
				$filterList[] = "COALESCE(RD.Paid_id, 1) = 2";

			}
			else if ( $data['filterRecords'] == 3 ) {
				$filterList[] = "COALESCE(RD.Paid_id, 1) = 1";

			}

			if ( $params['Registry_IsZNO'] == 2 ) {
				$filterList[] = "RD.RegistryData_IsZNO in (2, 3)";
			}
			else {
				$filterList[] = "COALESCE(RD.RegistryData_IsZNO, 1) = 1";

			}
		}

		$order = "RD.Person_FIO";
		if(!empty($data['sort'])){
			$sortInfo = json_decode($data['sort'], true);

			if($sortInfo['field'] != 'MaxEvn_id'){
				if(strripos($sortInfo['field'], '_id') !== false){
					$sortInfo['field'] = 'RD.' . $sortInfo['field'];
				}

				$order = $sortInfo['field'] . ' ' . $sortInfo['direction'];
			}

		}


		$query = "
			  Select
                     -- select
                     RD.Evn_id as \"Evn_id\",
                     RD.Evn_rid as \"Evn_rid\",
                     RD.{$this->MaxEvnField} as \"MaxEvn_id\",
                     RD.EvnClass_id as \"EvnClass_id\",
                     RD.Registry_id as \"Registry_id\",
                     RD.RegistryType_id as \"RegistryType_id\",
                     RD.Person_id as \"Person_id\",
                     RD.Server_id as \"Server_id\",
                     PersonEvn.PersonEvn_id as \"PersonEvn_id\",
                     " . implode(', ', $fieldsList) . ",
                     RD.RegistryData_deleted as \"RegistryData_deleted\",
                     RTrim(RD.NumCard) as \"EvnPL_NumCard\",
                     RTrim(RD.Person_FIO) as \"Person_FIO\",
                     to_char(RD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
                     CASE
                       WHEN RD.Person_IsBDZ = 1 THEN 'true'
                       ELSE 'false'
                     END as \"Person_IsBDZ\",
                     RD.Polis_Num as \"Polis_Num\",
                     RD.LpuSection_id as \"LpuSection_id\",
                     RTrim(RD.LpuSection_name) as \"LpuSection_name\",
                     COALESCE(LSP.LpuSectionProfile_Code || '. ', '') || LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
                     COALESCE(cast (msc.MedSpecClass_Code as varchar) || '. ', '') || msc.MedSpecClass_Name as \"MedSpecOms_Name\",
                     RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
                     RTrim(COALESCE(to_char(cast (RD.{$evnVizitPLSetDateField} as timestamp), 'DD.MM.YYYY'), '')) as \"EvnVizitPL_setDate\",
                     RTrim(COALESCE(to_char(cast (RD.Evn_disDate as timestamp), 'DD.MM.YYYY'), '')) as \"Evn_disDate\",
                     RD.RegistryData_Tariff as \"RegistryData_Tariff\",
                     --RD.RegistryData_KdFact as \"RegistryData_Uet\",
                     RD.RegistryData_KdPay as \"RegistryData_KdPay\",
                     RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
                     RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
                     RegistryError.Err_Count as \"Err_Count\",
                     RegistryErrorTFOMS.ErrTfoms_Count as \"ErrTfoms_Count\",
                     RHDCR.RegistryHealDepResType_id as \"RegistryHealDepResType_id\",
                     case
                       when COALESCE(e.Evn_IsArchive, 1) = 1 then 0
                       else 1
                     end as \"archiveRecord\",
                     case
                       when RETLast.RegistryErrorTFOMS_id is not null and e.Evn_updDT >= RETLast.RegistryErrorTFOMS_insDT then 3
                       when RETLast.RegistryErrorTFOMS_id is not null and e.Evn_updDT < RETLast.RegistryErrorTFOMS_insDT then 2
                       else 1
                     end as \"RegistryData_IsEarlier\"
                     -- end select
              from
                   -- from
                   {$from}
                   left join v_Evn e on e.Evn_id = rd.Evn_id
                   left join v_LpuSection LS on LS.LpuSection_id = RD.LpuSection_id
                   left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
                   left join fed.v_MedSpecClass msc on msc.MedSpecClass_id = rd.MedSpec_id
                   left join v_RegistryHealDepCheckRes RHDCR on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id 
                   " . implode(PHP_EOL, $joinList) . "
                   LEFT JOIN LATERAL
                   (
                     select count(*) as Err_Count
                     from {$this->scheme}.v_{$this->RegistryErrorObject} RE
                     where RD.Evn_id = RE.Evn_id and
                           RD.Registry_id = RE.Registry_id
                   ) RegistryError ON true
                   LEFT JOIN LATERAL
                   (
                     select count(*) as ErrTfoms_Count
                     from {$this->scheme}.v_RegistryErrorTFOMS RET
                     where RD.Evn_id = RET.Evn_id and
                           RD.Registry_id = RET.Registry_id
                   ) RegistryErrorTFOMS ON true
                   LEFT JOIN LATERAL
                   (
                     select RegistryErrorTFOMS_id,
                            RegistryErrorTFOMS_insDT
                     from {$this->scheme}.v_RegistryErrorTFOMS RET
                     where RD.Evn_id = RET.Evn_id and
                           RD.Registry_id <> RET.Registry_id
                     order by RegistryErrorTFOMS_insDT desc
                     limit 1
                   ) RETLast ON true
                   LEFT JOIN LATERAL
                   (
                     select PersonEvn_id
                     from v_PersonEvn PE
                     where RD.Person_id = PE.Person_id and
                           PE.PersonEvn_insDT <= COALESCE(RD.Evn_disDate, RD.Evn_setDate)
                     order by PersonEvn_insDT desc
                     limit 1
                   ) PersonEvn ON true
                   -- end from
              where
                    -- where
                    R.Registry_id =:Registry_id and
                    " . implode(PHP_EOL . 'and  ', $filterList) . "
                    -- end where
              order by
                       -- order by
                       {$order} 
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

		if ( !is_object($result) ) {
			return false;
		}

		$response = array();
		$response['data'] = $result->result('array');
		$response['totalCount'] = $count;

		return $response;
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
			  SELECT FLKSettings_id as \"FLKSettings_id\",
                     RegistryType_id as \"RegistryType_id\",
                     FLKSettings_EvnData as \"FLKSettings_EvnData\",
                     FLKSettings_PersonData as \"FLKSettings_PersonData\"
              FROM v_FLKSettings
              WHERE RegistryType_id =:RegistryType_id AND
                    getdate() between FLKSettings_begDate and
                    case
                      when FLKSettings_endDate is null then '2030-01-01'
                      else FLKSettings_endDate
                    end AND
                    FLKSettings_EvnData iLIKE '%ufa%'
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
	 * Аналог checkEvnInRegistry, болеее универсальный, учитывающий настройки
	 */
	public function checkEvnAccessInRegistry($data, $action = 'delete') {
		$dbreg = $this->load->database('registry', true);

		$filterList = array();
		$object = '';
		$scheme = '';
		$registryTypeArray = array();

		if ( !empty($data['EvnPS_id']) ) {
			$object = 'EvnSection';
			$filterList[] = "EvnSection_rid = :EvnPS_id";
			$registryTypeArray[] = 1;
			$registryTypeArray[] = 14;
		}

		if ( !empty($data['EvnSection_id']) ) {
			$object = 'EvnSection';
			$filterList[] = "EvnSection_id = :EvnSection_id";
			$registryTypeArray[] = 1;
			$registryTypeArray[] = 14;
		}

		if ( !empty($data['EvnPL_id']) ) {
			$object = 'EvnVizit';
			$filterList[] = "EvnVizit_rid = :EvnPL_id";
			$registryTypeArray[] = 2;
			$registryTypeArray[] = 17;
		}

		if ( !empty($data['EvnVizitPL_id']) ) {
			$object = 'EvnVizit';
			$filterList[] = "EvnVizit_id = :EvnVizitPL_id";
			$registryTypeArray[] = 2;
			$registryTypeArray[] = 17;
		}

		if ( !empty($data['CmpCloseCard_id']) ) {
			$object = 'CmpCloseCard';
			$filterList[] = "CmpCloseCard_id = :CmpCloseCard_id";
			$registryTypeArray[] = 6;
			$scheme = $this->scheme;
		}

		if ( !empty($data['EvnPLDispDop13_id']) ) {
			$object = 'EvnVizitDispDop';
			$filterList[] = "EvnVizitDispDop_rid = :EvnPLDispDop13_id";	
			$registryTypeArray[] = 7;
		}

		if ( !empty($data['EvnPLDispOrp_id']) ) {
			$object = 'EvnVizitDispOrp';
			$filterList[] = "EvnVizitDispOrp_rid = :EvnPLDispOrp_id";	
			$registryTypeArray[] = 9;
		}

		if ( count($registryTypeArray) == 0 ) {
			return false;
		}

		$this->load->model('Options_model');
		$globalOptions = $this->Options_model->getOptionsGlobals($data);
		$disableEditInReg = !empty($globalOptions['globals']['registry_disable_edit_inreg'])?intval($globalOptions['globals']['registry_disable_edit_inreg']):2;
		$disableEditPaid = !empty($globalOptions['globals']['registry_disable_edit_paid'])?intval($globalOptions['globals']['registry_disable_edit_paid']):2;

		$checkPriorityArray = array();

		// Сперва проверяем запреты
		if ( $disableEditInReg == 2 ) {
			$checkPriorityArray[] = 'disableEditInReg';
			$checkPriorityArray[] = 'disableEditPaid';
		}
		else {
			$checkPriorityArray[] = 'disableEditPaid';
			$checkPriorityArray[] = 'disableEditInReg';
		}

		$actiontxt = 'Удаление';

		switch ( $action ) {
			case 'edit':
				$actiontxt = 'Редактирование';
				break;
		}

		foreach ( $registryTypeArray as $RegistryType_id ) {
			$data['RegistryType_id'] = $RegistryType_id;

			$this->setRegistryParamsByType($data, true);

			foreach ( $checkPriorityArray as $checkPriority ) {
				switch ( $checkPriority ) {
					case 'disableEditPaid':
						if ( ($disableEditPaid == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditPaid == 2)  {
							// Проверяем признак оплаченности
							$query = "
								select 
									R.Registry_Num as \"Registry_Num\",
									to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
								from
									" . (!empty($scheme) ? $scheme . '.' : "") . "v_{$object} evn 
									left join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Evn_id = evn.{$object}_id
									left join {$this->scheme}.v_Registry R  on R.Registry_id = RD.Registry_id
								where
									" . implode(' and ', $filterList) . "
									and evn.{$object}_IsPaid = 2
								order by R.Registry_id desc
								limit 1
							";
							$res = $dbreg->query($query, $data);

							if ( !is_object($res) ) {
								return array('Error_Msg' => 'Ошибка БД!');
							}

							$resp = $res->result('array');

							if ( count($resp) > 0 ) {
								if ( $disableEditPaid == 2 ) {
									return array('Error_Msg' => 'Запись оплачена в реестре' . (!empty($resp[0]['Registry_Num']) ? ' ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] : '') . '.<br/>' . $actiontxt . ' записи невозможно.');
								}
								else {
									return array('Error_Msg' => '', 'Alert_Msg' => 'Запись оплачена в реестре' . (!empty($resp[0]['Registry_Num']) ? ' ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] : '') . '.<br/>' . $actiontxt . ' записи нежелательно!');
								}
							}
						}
						break;

					case 'disableEditInReg':
						if ( $action == 'delete' || $disableEditInReg == 2 || ($disableEditInReg == 3 && empty($data['ignoreCheckRegistry'])) ) {
							// Если случай в реестре, то удаление запрещено, вне зависимости от настроек
							// Редактирование - в зависимости от настроек
							$query = "
								select 
									RD.Evn_id as \"Evn_id\",
									R.Registry_Num as \"Registry_Num\",
									to_char(R.Registry_accDate, 'DD.MM.YYYY') as \"Registry_accDate\"
								from
									" . (!empty($scheme) ? $scheme . '.' : "") . "v_{$object} evn 
									left join {$this->scheme}.v_{$this->RegistryDataObject} RD  on RD.Evn_id = evn.{$object}_id
									left join {$this->scheme}.v_Registry R  on R.Registry_id = RD.Registry_id
								where
									" . implode(' and ', $filterList) . "
									and evn.{$object}_IsInReg = 2
								order by R.Registry_id desc
							";
							$res = $dbreg->query($query, $data);

							if ( !is_object($res) ) {
								return array('Error_Msg' => 'Ошибка БД!');
							}

							$resp = $res->result('array');

							if ( $action == 'delete' ) {
								if ( count($resp) > 0 ) {
									return array('Error_Msg' => 'Запись используется в реестре' . (!empty($resp[0]['Registry_Num']) ? ' ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] : '') . '.<br/>' . $actiontxt . ' записи невозможно.');
								}
							}

							if ( count($resp) > 0 && (($disableEditInReg == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditInReg == 2) ) {
								if ( $disableEditInReg == 2 ) {
									return array('Error_Msg' => 'Запись используется в реестре' . (!empty($resp[0]['Registry_Num']) ? ' ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] : '') . '.<br/>' . $actiontxt . ' записи невозможно.');
								}
								else {
									return array('Error_Msg' => '', 'Alert_Msg' => 'Запись используется в реестре' . (!empty($resp[0]['Registry_Num']) ? ' ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] : '') . '.<br/>' . $actiontxt . ' записи нежелательно!');
								}
							}
						}
						break;
				}
			}
		}

		return false;
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
	 * Обнуление счетчика записей
	 */
	private function _resetPersCnt() {
		$this->persCnt = 0;
		return true;
	}

	/**
	 * Обнуление счетчика записей
	 */
	private function _resetZapCnt() {
		$this->zapCnt = 0;
		return true;
	}
}
