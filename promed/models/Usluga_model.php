<?php
/**
 * Модель
 */
class Usluga_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 *	Удаление чего-то
	 */
	function deleteUslugaPriceList($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_UslugaPriceList_del
				@UslugaPriceList_id = :UslugaPriceList_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'UslugaPriceList_id' => $data['UslugaPriceList_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление позиции из прайс-листа услуг)');
		}
	}


	/**
	 *	Загрузка чего-то
	 */
	function loadUslugaPriceListGrid($data) {
		$query = "
			select
				-- select
				UPL.UslugaPriceList_id as UslugaPriceList_id,
				STR(UPL.UslugaPriceList_ue, 18, 2) as UslugaPriceList_UET,
				RTRIM(Usluga.Usluga_Code) as Usluga_Code,
				Usluga.Usluga_id as Usluga_id,
				RTrim(Usluga.Usluga_Name) as Usluga_Name
				-- end select
			from
				-- from
				UslugaPriceList UPL with(nolock)
				inner join Usluga with(nolock) on Usluga.Usluga_id = UPL.Usluga_id
				-- end from
			where
				-- where
				UPL.Lpu_id = :Lpu_id
				-- end where
			order by
				-- order by
				Usluga.Usluga_Name
				-- end order by
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$response = array();

		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		}
		else {
			return false;
		}

		return $response;
	}


	/**
	 *	Загрузка формы редактирования чего-то
	 */
	function loadUslugaPriceListEditForm($data) {
		$query = "
			SELECT TOP 1
				UPL.UslugaPriceList_id as UslugaPriceList_id,
				UPL.UslugaPriceList_ue as UslugaPriceList_UET,
				UPL.Usluga_id as Usluga_id
			FROM
				UslugaPriceList EPL with(nolock)
			WHERE (1 = 1)
				and UPL.UslugaPriceList_id = :UslugaPriceList_id
				and UPL.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'UslugaPriceList_id' => $data['UslugaPriceList_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Сохранение чего-то
	 */
	function saveUslugaPriceList($data) {
		$procedure = '';

		if ( !isset($data['UslugaPriceList_id']) ) {
			$procedure = 'p_UslugaPriceList_ins';
		}
		else {
			$procedure = 'p_UslugaPriceList_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :UslugaPriceList_id;
			exec " . $procedure . "
				@UslugaPriceList_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@UslugaPriceList_ue = :UslugaPriceList_ue,
				@Usluga_id = :Usluga_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as UslugaPriceList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $data['Server_id'],
			'Usluga_id' => $data['Usluga_id'],
			'UslugaPriceList_id' => $data['UslugaPriceList_id'],
			'UslugaPriceList_ue' => $data['UslugaPriceList_UET']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *  Читает дерево комплексных услуг для службы
	 */
	function loadUslugaComplexMedServiceTree($data)
	{
		
		$params = array(
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			/*
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuUnit_id'=>$data['LpuUnit_id'],
			'LpuUnitType_id'=>$data['LpuUnitType_id'],
			'LpuBuilding_id'=>$data['LpuBuilding_id'],
			*/
			'Lpu_id'=>$data['Lpu_id']
			);
		$filter = '';
		if ($data['UslugaComplex_pid']>0) {
			$filter = " and (uc.UslugaComplex_pid = :UslugaComplex_pid)";
		}
		else {
			$filter = " and (uc.UslugaComplex_pid is null)";
		}
		/*
		
		if (($data['LpuSection_id']>0) and ($data['level']==0)) {
			$filter .= " and (uc.LpuSection_id = :LpuSection_id)";
		}
		
		if (($data['LpuUnit_id']>0) and ($data['level']==0)) {
			$filter .= " and (ls.LpuUnit_id = :LpuUnit_id)";
		}
		
		if (($data['LpuUnitType_id']>0) and ($data['level']==0)) {
			$filter .= " and (lu.LpuUnitType_id = :LpuUnitType_id)";
		}
		
		if (($data['LpuBuilding_id']>0) and ($data['level']==0)) {
			$filter .= " and (lu.LpuBuilding_id = :LpuBuilding_id)";
		}
		
		
		if (($data['UslugaComplex_id']>=0) and ($data['level']==0)) {
			
			$filter = " (uc.UslugaComplex_id = :UslugaComplex_id)";
		}
		*/		
		if ($data['Urgency_id']==1) {
			$filter .= ' and UCMS.UslugaComplexMedService_begDT is not null and (UCMS.UslugaComplexMedService_endDT is null or UCMS.UslugaComplexMedService_endDT > dbo.tzGetDate())';
		}
		elseif ($data['Urgency_id']==2) {
			$filter .= ' and UCMS.UslugaComplexMedService_begDT is not null and UCMS.UslugaComplexMedService_endDT < dbo.tzGetDate()';			
		}
		
		$query = "
			SELECT
				UCMS.UslugaComplexMedService_id
				,UC.Lpu_id
				,UC.LpuSection_id
				,UC.Usluga_id
				,U.Usluga_Code
				,UC.UslugaComplex_id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,MS.MedService_Name as LpuSection_Name
				,case when Leaf.leaf_count>0 then 0 else 1 end as leaf
			FROM 
				v_UslugaComplexMedService UCMS with (NOLOCK)
				inner join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				inner join v_MedService MS with(nolock) on UCMS.MedService_id = MS.MedService_id
				left join v_Usluga U with(nolock) on U.Usluga_id = UC.Usluga_id
				outer apply (
					Select count(UslugaComplex_id) as leaf_count from v_UslugaComplex with (nolock) where UslugaComplex_pid = UC.UslugaComplex_id
				) as Leaf
			where
				UCMS.MedService_id = :MedService_id
				{$filter}
			order by
				UC.UslugaComplex_Name
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	*  Читает дерево комплексных услуг
	*/
	function loadUslugaComplexTree($data)
	{
		
		$params = array(
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			/*
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuUnit_id'=>$data['LpuUnit_id'],
			'LpuUnitType_id'=>$data['LpuUnitType_id'],
			'LpuBuilding_id'=>$data['LpuBuilding_id'],
			*/
			'Lpu_id'=>$data['Lpu_id']
			);
		$filter = "(1=1)";
		if ($data['UslugaComplex_pid']>0) {
		
			$filter = " (ucc.UslugaComplex_pid = :UslugaComplex_pid)";
		}
		else {
			$filter = " (ucc.UslugaComplex_pid is null)";
		}
		/*
		if (($data['LpuSection_id']>0) and ($data['level']==0)) {
			$filter .= " and (uc.LpuSection_id = :LpuSection_id)";
		}
		
		if (($data['LpuUnit_id']>0) and ($data['level']==0)) {
			$filter .= " and (ls.LpuUnit_id = :LpuUnit_id)";
		}
		
		if (($data['LpuUnitType_id']>0) and ($data['level']==0)) {
			$filter .= " and (lu.LpuUnitType_id = :LpuUnitType_id)";
		}
		
		if (($data['LpuBuilding_id']>0) and ($data['level']==0)) {
			$filter .= " and (lu.LpuBuilding_id = :LpuBuilding_id)";
		}
		*/
		if ($data['Urgency_id']==1) {
			$filter .= " and uc.UslugaComplex_begDT is not null and (uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT > dbo.tzGetDate())";
		}
		elseif ($data['Urgency_id']==2) {
			$filter .= " and uc.UslugaComplex_begDT is not null and uc.UslugaComplex_endDT < dbo.tzGetDate()";			
		}
		

		if (($data['UslugaComplex_id']>=0) and ($data['level']==0)) {
			
			$filter = " (uc.UslugaComplex_id = :UslugaComplex_id)";
		} elseif ($data['UslugaComplex_pid']>0) {
			$filter = " (ucc.UslugaComplex_pid = :UslugaComplex_pid)";
		} else {
			return false;
		}
		$query = "
				Select
				uc.Lpu_id,
				uc.UslugaComplex_id,
				IsNull(UC2011.UslugaComplex_Code, ''+uc.UslugaComplex_Code+'') as UslugaComplex_Code,
				uc.UslugaComplex_Name,
				case when Leaf.leaf_count>0 then 0 else 1 end as leaf
			from
				v_UslugaComplex uc with (NOLOCK)
				left join v_UslugaComplexComposition ucc with (NOLOCK) on ucc.UslugaComplex_id = uc.UslugaComplex_id -- состав услуги 
				left join v_UslugaComplex uc2011 with (NOLOCK) on uc.UslugaComplex_2011id = uc2011.UslugaComplex_id
				outer apply (
					Select count(*) as leaf_count from v_UslugaComplexComposition with (nolock) where UslugaComplex_pid = uc.UslugaComplex_id 
				) as Leaf
			where
				(uc.Lpu_id = :Lpu_id or uc.Lpu_id is null) and {$filter}
				-- todo: пока отключил, потому что головная услуга не фильтруется по дате действия
				-- and uc.UslugaComplex_begDT is not null and (uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT > dbo.tzGetDate()) -- только действующие услуги
			order by
				uc.UslugaComplex_Code
			";
				//echo $query; exit;

		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает состав комплексной услуги для определенной службы
	 * @param $data
	 * @return bool|mixed
	 */
	function loadUslugaComplexContent($data)
	{
		$params = array(
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'MedService_id' => $data['MedService_id'],
			'Lpu_id'=>$data['Lpu_id']
		);
		$filter = "(uc.Lpu_id = :Lpu_id or uc.Lpu_id is null) ";

		if ($data['MedService_id']>0) {
			$filter .= " and UCMS.MedService_id = :MedService_id ";
		}

		// Если не пришли с клиента: служба и услуга, то запрос не выполняем
		if (empty($data['UslugaComplex_pid']) || empty($data['MedService_id'])) {
			return false;
		}
		$filter .= " and ucms.UslugaComplex_id = :UslugaComplex_pid";
		
		// запрос для получения данных
		$query = "
			Select
				uc.UslugaComplex_id,
				uc.UslugaComplex_pid,
				uc.LpuSection_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				RTrim(IsNull(convert(varchar,cast(uc.UslugaComplex_begDT as datetime),104),'')) as UslugaComplex_begDT,
				RTrim(IsNull(convert(varchar,cast(uc.UslugaComplex_endDT as datetime),104),'')) as UslugaComplex_endDT,
				ls.LpuSection_Name as LpuSection_Name
			from
				v_UslugaComplex uc with (NOLOCK) -- обычная услуга
				left join v_LpuSection ls with (NOLOCK) on ls.LpuSection_id = uc.LpuSection_id
				left join v_UslugaComplexMedService m_child (nolock) on uc.UslugaComplex_id = m_child.UslugaComplex_id
				LEFT JOIN v_UslugaComplexMedService ucms WITH ( NOLOCK ) ON ucms.UslugaComplexMedService_id = m_child.UslugaComplexMedService_pid -- комлпексная услуга
				outer apply(
					select top 1
						at_child.AnalyzerTest_SortCode
					from 
						lis.v_AnalyzerTest at_child (nolock)
						inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
						inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
					where
						at_child.UslugaComplexMedService_id = m_child.UslugaComplexMedService_id
						and at.UslugaComplexMedService_id = m_child.UslugaComplexMedService_pid
				) ATEST -- фильтрация услуг по активности тестов связанных с ними
			where
				{$filter}
			order by
				ISNULL(ATEST.AnalyzerTest_SortCode, 999999999)
				";

		// возвращаем на клиент либо состав услуги, либо саму услугу (если услуга не содержит состава)
		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response)==0) { // Если у услуги нет состава
				// то берем саму услугу
				$query = "
					Select
						uc.UslugaComplex_id,
						uc.UslugaComplex_pid,
						uc.LpuSection_id,
						uc.UslugaComplex_Code,
						uc.UslugaComplex_Name,
						RTrim(IsNull(convert(varchar,cast(uc.UslugaComplex_begDT as datetime),104),'')) as UslugaComplex_begDT,
						RTrim(IsNull(convert(varchar,cast(uc.UslugaComplex_endDT as datetime),104),'')) as UslugaComplex_endDT,
						ls.LpuSection_Name as LpuSection_Name
					from
						v_UslugaComplex uc with (NOLOCK) -- обычная услуга
						left join v_LpuSection ls with (NOLOCK) on ls.LpuSection_id = uc.LpuSection_id
					where
						uc.UslugaComplex_id = :UslugaComplex_pid
				";
				$result = $this->db->query($query, $params);
				if ( is_object($result) ) {
					$response = $result->result('array');
				}
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	*  Читает одну запись (или много - для грида) из таблицы комплексных услуг
	*/
	function loadUslugaComplexView($data)
	{
		$params = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuUnit_id'=>$data['LpuUnit_id'],
			'LpuUnitType_id'=>$data['LpuUnitType_id'],
			'LpuBuilding_id'=>$data['LpuBuilding_id'],
			'Lpu_id'=>$data['Lpu_id']
			);
		$filter = "(uc.Lpu_id = :Lpu_id or uc.Lpu_id is null) ";

		if ($data['UslugaComplex_id']>0) {
			$filter .= " and uc.UslugaComplex_id = :UslugaComplex_id ";
		}
		else 
		{
			if ($data['UslugaComplex_pid']>0) {
			
				$filter .= "and (uc.UslugaComplex_pid = :UslugaComplex_pid) ";
			}
			else {
				$filter .= "and (uc.UslugaComplex_pid is null) ";
				// фильтрацию по отделению выполняем только для первого уровня
				if ($data['LpuSection_id']>0) {
					$filter .= " and uc.LpuSection_id = :LpuSection_id ";
				}
				elseif ($data['LpuUnit_id']>0) {
					$filter .= " and (lu.LpuUnit_id = :LpuUnit_id) ";
				}
				elseif ($data['LpuUnitType_id']>0) {
					$filter .= " and (lu.LpuUnitType_id = :LpuUnitType_id) ";
				}
				elseif ($data['LpuBuilding_id']>0) {
					$filter .= " and (lu.LpuBuilding_id = :LpuBuilding_id) ";
				}
			}
		}
			
		if ($data['Urgency_id']==1) {
			$filter .= "and uc.UslugaComplex_begDT is not null and (uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT > dbo.tzGetDate())";
		}
		elseif ($data['Urgency_id']==2) {
			$filter .= "and uc.UslugaComplex_begDT is not null and uc.UslugaComplex_endDT < dbo.tzGetDate()";			
		}
		
		$query = "
		Select
				uc.UslugaComplex_id,
				uc.UslugaComplex_pid,
				UCMS.UslugaComplexMedService_id,
				UCMS.MedService_id,
				uc.Lpu_id,
				uc.LpuSection_id,
				uc.Usluga_id,
				uc.UslugaGost_id,
				uc.XmlTemplate_id,
				uc.XmlTemplateSeparator_id,
				uc.UslugaGost_id,
				uc.UslugaComplex_BeamLoad,
				uc.UslugaComplex_UET,
				uc.UslugaComplex_Cost,	
				uc.UslugaComplex_DailyLimit,
				uc.UslugaComplex_isGenXml,
				uc.UslugaComplex_isAutoSum,
				u.Usluga_Name,
				u.Usluga_Code,
				uc.RefValues_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_ACode,
				uc.UslugaComplex_Name,
				RTrim(IsNull(convert(varchar,cast(uc.UslugaComplex_begDT as datetime),104),'')) as UslugaComplex_begDT,
				RTrim(IsNull(convert(varchar,cast(uc.UslugaComplex_endDT as datetime),104),'')) as UslugaComplex_endDT,	
				ls.LpuSection_Name as LpuSection_Name,
				uc.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Name,
				l.Lpu_Nick as Lpu_Name
			from
				v_UslugaComplex uc with (NOLOCK)
				left join v_Usluga u with (NOLOCK) on u.Usluga_id = uc.Usluga_id
				left join v_Lpu l with (NOLOCK) on l.Lpu_id = uc.Lpu_id
				left join v_LpuSection ls with (NOLOCK) on ls.LpuSection_id = uc.LpuSection_id
				left join v_LpuSectionProfile lsp with (NOLOCK) on lsp.LpuSectionProfile_id = uc.LpuSectionProfile_id
				left join v_UslugaComplexMedService UCMS with (NOLOCK) on UCMS.UslugaComplex_id = uc.UslugaComplex_id
				left join v_LpuUnit lu  with (NOLOCK) on lu.LpuUnit_id = ls.LpuUnit_id
			where
				{$filter}
			";
		//echo getDebugSql($query, $params);exit;
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}

    /**
     *  Читает список комплексных услуг первого уровня для комбобокса
     * @param array $data
     * @return bool|mixed
     */
	function loadUslugaComplexList($data)
	{
		$fields = "";
		$params = array(
			'Lpu_id'=>(!empty($data['Lpu_uid']))?$data['Lpu_uid']:$data['Lpu_id']
		);
		$filter = "(uc.Lpu_id = :Lpu_id or uc.Lpu_id is null)";
		$filter .= " and (uc.UslugaComplexLevel_id in (7,8) or uc.UslugaComplex_pid is null)";
		$globalFilter = "";
		
		if (!empty($data['uslugaCategoryList'])) {
			$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);
			if ( is_array($uslugaCategoryList) && count($uslugaCategoryList) > 0 ) {
				$globalFilter .= " and Cat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "')";
			}
		}
		
		if ( !empty($data['allowedUslugaComplexAttributeList']) ) {
			$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);

			if ( is_array($allowedUslugaComplexAttributeList) && count($allowedUslugaComplexAttributeList) > 0 ) {
				$globalFilter .= " and exists (
					select t1.UslugaComplexAttribute_id
					from UslugaComplexAttribute t1 with (nolock)
						inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = uc.UslugaComplex_id
						and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
				)";
			}
		}
				
		$join = '';
		$nameselect = 'RTrim(uc.UslugaComplex_Name) as UslugaComplex_Name,';
		if ($data['LpuSection_id']>0)
		{
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$filter .= " and (uc.LpuSection_id = :LpuSection_id or uc.LpuSection_id is null or uc.LpuSectionProfile_id = ls.LpuSectionProfile_id) ";
			$join .= ' left join v_LpuSection ls with (NOLOCK) on ls.LpuSection_id = :LpuSection_id';
		}
		else
		{
			if ($data['MedService_id']>0)
			{
				if ($data['linkedMesServiceOnly']) {
					$filter = '(ucms.MedService_id IN (select MedService_lid from v_MedServiceLink (nolock) where MedService_id = :MedService_id))';
				} else {
					$filter = '(ucms.MedService_id = :MedService_id)';
				}
				$params['MedService_id'] = $data['MedService_id'];
				//$filter .= " and (ucms.MedService_id = :MedService_id) ";
				$join .= ' inner join v_UslugaComplexMedService ucms with (NOLOCK) on ucms.UslugaComplex_id = uc.UslugaComplex_id';
				$fields .= ' ,ucms.UslugaComplexMedService_Time';
				$nameselect = 'ISNULL(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as UslugaComplex_Name,ucms.UslugaComplexMedService_id,';

				if (!empty($data['Resource_id'])) {
					$params['Resource_id'] = $data['Resource_id'];
					$join .= ' inner join v_UslugaComplexResource ucr (nolock) on ucr.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id';
					$filter .= " and ucr.Resource_id = :Resource_id ";
				}
				
				if ($data['medServiceComplexOnly']) {
					$filter .= " and ucms.UslugaComplexMedService_pid IS NULL ";

					// для лаборатории так же не нужны те, которых нет в анализаторах
					$MedServiceType_SysNick = $this->getFirstResultFromQuery("select mst.MedServiceType_SysNick from v_MedService ms (nolock) inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id where ms.MedService_id = :MedService_id", array(
						'MedService_id' => $data['MedService_id']
					));

					if ($MedServiceType_SysNick == 'lab') {
						$existFilter = '';
						if(!empty($data['Analyzer_id'])) {
							$params['Analyzer_id'] = $data['Analyzer_id'];
							$existFilter .= 'and at.Analyzer_id = :Analyzer_id';
						}
						$filter .= "
							and exists(
								select top 1
									at.AnalyzerTest_id
								from
									lis.v_AnalyzerTest at (nolock)
									inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
								where
									at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
									and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
									and ISNULL(a.Analyzer_IsNotActive, 1) = 1
									and (at.AnalyzerTest_endDT >= @curdate or at.AnalyzerTest_endDT is null)
									{$existFilter}
							) -- фильтрация услуг по активности исследований связанных с ними
						";
					}
				}

				$filter .= " and (ucms.UslugaComplexMedService_endDT is null or ucms.UslugaComplexMedService_endDT >= @curdate) ";
		
                if (isset($data['filter_by_exists'])) {
                    $filter .= "    AND EXISTS (
                            SELECT r.UslugaComplex_id FROM dbo.v_EvnLabRequest r with(nolock)
                            INNER JOIN v_EvnDirection_all d1 with(nolock) ON d1.EvnDirection_id = r.EvnDirection_id
                            WHERE d1.MedService_id = :MedService_id AND uc.UslugaComplex_id = r.UslugaComplex_id
                        )
                    ";
                }
				// фильтрация по назначенной услуге
				if ($data['UslugaComplex_prescid']>0) {
					$params['UslugaComplex_prescid'] = $data['UslugaComplex_prescid'];
					// Выборка для определения связи назначенной услуги с услугами лаборатории (пункта забора)
					$join .= '
					outer apply (
						Select
							un.UslugaComplex_id,
							un.UslugaComplex_2004id,
							un.UslugaComplex_2011id,
							un.UslugaComplex_TFOMSid,
							un.UslugaComplex_llprofid,
							un.UslugaComplex_slprofid
						from v_UslugaComplex un with (NOLOCK)
						where un.UslugaComplex_id = :UslugaComplex_prescid
					) as ul
					';
					$filter .= "
					     and (
							uc.UslugaComplex_id = ul.UslugaComplex_id or
							uc.UslugaComplex_2004id = ul.UslugaComplex_2004id or
							uc.UslugaComplex_2011id = ul.UslugaComplex_2011id or
							uc.UslugaComplex_TFOMSid = ul.UslugaComplex_TFOMSid or
							uc.UslugaComplex_llprofid = ul.UslugaComplex_llprofid or
							uc.UslugaComplex_slprofid = ul.UslugaComplex_slprofid
						)
                    ";
				}
			}
			else
			{
				
				/*
				если выбрать услугу, отвязанную от отделения, то как мы создадим направление на такую же услугу в конкретное отделение?
				$filter .= ' and uc.LpuSection_id is null ';
				*/
			}
		}
		
		if ( !empty($data['UslugaGost_Code']) )
		{
			switch($data['UslugaGost_Code']) {
				case 'PR'://процедуры и манипуляции
					$join .= ' 
					inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id		
					and left(ug.UslugaComplex_Code,5) in (\'A.13.%\', \'A.14.%\', \'A.15.%\', \'A.17.%\', \'A.19.%\', \'A.20.%\', \'A.21.%\') ';
				break;
				case 'FU': //услуги функциональной диагностики
					$join .= ' inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id		
					and (ug.UslugaComplex_Code like \'A.03.%\' or ug.UslugaComplex_Code like \'A.04.%\' or ug.UslugaComplex_Code like \'A.05.%\' or ug.UslugaComplex_Code like \'A.06.%\')					
					';
					
				break;
				case 'LAB'://услуги лабораторной диагностики
					$join .= ' inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id			 
					and (ug.UslugaComplex_Code like \'B.03.016.%\' or ug.UslugaComplex_Code like \'A.08.%\' or ug.UslugaComplex_Code like \'A.09.%\' or ug.UslugaComplex_Code like \'A.11.%\')
					';
				break;
				default:
					$params['UslugaGost_Code'] = $data['UslugaGost_Code'].'%';
					$join .= ' inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id and ug.UslugaComplex_Code like :UslugaGost_Code'; // TODO: Проверить условие
				break;
			}
			$filter .= " and (Cat.UslugaCategory_SysNick not in ('lpu','lpulabprofile') or uc.Lpu_id = :Lpu_id) /*and (uc.UslugaComplexLevel_id =8 or uc.UslugaComplex_pid is null) */ ";
		}
		
		if ( !empty($data['query']) )
		{
			$params['query'] = '%'. $data['query'] . '%';
			$filter .= " and (cast(uc.UslugaComplex_Code as varchar) +' '+ RTrim(uc.UslugaComplex_Name)) LIKE :query ";
		}

		if ($data['complexOnly']) {
			$filter .= " and exists( select top 1 UslugaComplexComposition_id from v_UslugaComplexComposition (nolock) where UslugaComplex_pid = uc.Uslugacomplex_id ) ";
		}
		if (!empty($data['registryType'])) {
			switch ($data['registryType']) {
				case 'BSKRegistry':
					$filter .= " and uc.UslugaComplex_Code LIKE 'A16%' ";
					break;
			}
		}

		$query = "
			declare @curdate date = dbo.tzGetDate();

			Select top 500
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				{$nameselect}
				uc.XmlTemplate_id,
				uc.UslugaComplex_isGenXml,
				case when isFunc.UslugaComplexAttribute_id is not null then 1 else 0 end as isFunc
				{$fields}
			from
				v_UslugaComplex uc with (NOLOCK)
				left join v_UslugaCategory Cat with (nolock) on Cat.UslugaCategory_id=isnull(Uc.UslugaCategory_id,5)
				{$join}
				outer apply (
					select t1.UslugaComplexAttribute_id
					from UslugaComplexAttribute t1 with (nolock)
					where t1.UslugaComplex_id = uc.UslugaComplex_id and t1.UslugaComplexAttributeType_id = 9
				) as isFunc
			where
				(
					{$filter}
					{$globalFilter}
				)
			order by case when uc.Lpu_id = :Lpu_id then 1 else 2 end, Cat.UslugaCategory_Code, uc.UslugaComplex_Code
		";
		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$response = $result->result('array');

			if (!empty($data['UslugaComplex_id'])) {
				$found = false;
				foreach($response as $resp) {
					if ($resp['UslugaComplex_id'] == $data['UslugaComplex_id']) {
						$found = true;
					}
				}

				// если не нашли среди услуг службы ту которая указана, то для неё отдельный запрос без джойна по UslugaComplexMedService
				if (!$found) {
					$query = "
						Select top 1
							uc.UslugaComplex_id,
							uc.UslugaComplex_Code,
							RTrim(uc.UslugaComplex_Name) as UslugaComplex_Name,
							uc.XmlTemplate_id,
							uc.UslugaComplex_isGenXml
						from
							v_UslugaComplex uc with (nolock)
							left join v_UslugaCategory Cat with (nolock) on Cat.UslugaCategory_id=isnull(Uc.UslugaCategory_id,5)
						where
							 uc.UslugaComplex_id = :UslugaComplex_id
					";
					$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
					$result = $this->db->query($query, $params);
					if ( is_object($result) ) {
						$response = $result->result('array');
					}
				}
			}

			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получения списка услуг связанных с методиками ИФА
	 * @param $data
	 * @return array|false
	 */
	function loadUslugaComplexMethodsIFA ($data) {
		$where = array();
		$params = array();
		$params['MedService_id'] = $data['MedService_id'];
		$where[] = "A.MedService_id = :MedService_id";

		if(!empty($data['Analyzer_id'])) {
			$where[] = 'A.Analyzer_id = :Analyzer_id';
			$params['Analyzer_id'] = $data['Analyzer_id'];
		};
		
		$where = implode(' and ', $where);

		$query = "
			select
				AnT.AnalyzerTest_id,
				MIAT.MethodsIFA_id,
				UC.UslugaComplex_id,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name,
				A.MedService_id
			from v_MethodsIFAAnalyzerTest MIAT
			inner join lis.v_AnalyzerTest AnT with(nolock) on AnT.AnalyzerTest_id = MIAT.AnalyzerTest_id
			inner join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id= AnT.UslugaComplex_id
			inner join lis.v_Analyzer A with(nolock) on A.Analyzer_id = AnT.Analyzer_id
			where $where
		";
		return $this->queryResult($query, $params);
	}

	/**
	 *	Получение списка услуг службы
	 */
	function loadUslugaComplexMedServiceList($data)
	{
		$params = array(
			'Lpu_id'=>(!empty($data['Lpu_uid']))?$data['Lpu_uid']:$data['Lpu_id']
		);
		$filter = "(uc.Lpu_id = :Lpu_id or uc.Lpu_id is null)";
		// $filter .= " and (uc.UslugaComplexLevel_id in (7,8) or uc.UslugaComplex_pid is null)"; // нужны все услуги заведённые на службу
		$join = '';
		if ($data['LpuSection_id']>0)
		{
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$filter .= " and (uc.LpuSection_id = :LpuSection_id or uc.LpuSection_id is null or uc.LpuSectionProfile_id = ls.LpuSectionProfile_id) ";
			$join .= ' left join v_LpuSection ls with (NOLOCK) on ls.LpuSection_id = :LpuSection_id';
		}
		else
		{
			if ($data['MedService_id']>0)
			{
				$params['MedService_id'] = $data['MedService_id'];
				$filter = '(ucms1.MedService_id = :MedService_id)';
				//$filter .= " and (ucms.MedService_id = :MedService_id) ";

				if (isset($data['filter_by_exists'])) {
					$filter .= "    AND EXISTS (
                            SELECT r.UslugaComplex_id FROM dbo.v_EvnLabRequest r
                            INNER JOIN v_EvnDirection_all d1 with(nolock) ON d1.EvnDirection_id = r.EvnDirection_id
                            WHERE d1.MedService_id = :MedService_id AND uc.UslugaComplex_id = r.UslugaComplex_id
                        )
                    ";
				}
			}
			else
			{

				/*
				если выбрать услугу, отвязанную от отделения, то как мы создадим направление на такую же услугу в конкретное отделение?
				$filter .= ' and uc.LpuSection_id is null ';
				*/
			}
		}

		if ( !empty($data['UslugaGost_Code']) )
		{
			switch($data['UslugaGost_Code']) {
				case 'PR'://процедуры и манипуляции
					$join .= '
					inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id
					and left(ug.UslugaComplex_Code,5) in (\'A.13.%\', \'A.14.%\', \'A.15.%\', \'A.17.%\', \'A.19.%\', \'A.20.%\', \'A.21.%\') ';
					break;
				case 'FU': //услуги функциональной диагностики
					$join .= ' inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id
					and (ug.UslugaComplex_Code like \'A.03.%\' or ug.UslugaComplex_Code like \'A.04.%\' or ug.UslugaComplex_Code like \'A.05.%\' or ug.UslugaComplex_Code like \'A.06.%\')
					';

					break;
				case 'LAB'://услуги лабораторной диагностики
					$join .= ' inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id
					and (ug.UslugaComplex_Code like \'B.03.016.%\' or ug.UslugaComplex_Code like \'A.08.%\' or ug.UslugaComplex_Code like \'A.09.%\' or ug.UslugaComplex_Code like \'A.11.%\')
					';
					break;
				default:
					$params['UslugaGost_Code'] = $data['UslugaGost_Code'].'%';
					$join .= ' inner join v_UslugaComplex  ug with(nolock) on ug.UslugaComplex_id=uc.UslugaComplex_2004id and ug.UslugaComplex_Code like :UslugaGost_Code'; // TODO: Проверить условие
					break;
			}
			$filter .= " and (Cat.UslugaCategory_SysNick not in ('lpu','lpulabprofile') or uc.Lpu_id = :Lpu_id) /*and (uc.UslugaComplexLevel_id =8 or uc.UslugaComplex_pid is null) */ ";
		}

		if ( !empty($data['query']) )
		{
			$params['query'] = '%'. $data['query'] . '%';
			$filter .= " and (cast(uc.UslugaComplex_Code as varchar) +' '+ RTrim(uc.UslugaComplex_Name)) LIKE :query ";
		}

		if ( !empty($data['begDate']) )
		{
			$params['begDate'] = $data['begDate'];
			$filter .= " and ucms1.UslugaComplexMedService_begDT <= :begDate ";
		}

		if ( !empty($data['endDate']) )
		{
			$params['endDate'] = $data['endDate'];
			$filter .= " and isnull(ucms1.UslugaComplexMedService_endDT,'2100-01-01') >= :endDate ";
		}
		
		$query = "
			Select top 500
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				RTrim(uc.UslugaComplex_Name) as UslugaComplex_Name,
				uc.XmlTemplate_id,
				uc.UslugaComplex_isGenXml,
				ms.MedService_id
			from
				v_UslugaComplex uc with (NOLOCK)
				inner join v_UslugaCategory Cat with(nolock) on Cat.UslugaCategory_id=isnull(Uc.UslugaCategory_id,5)
				INNER JOIN v_UslugaComplexMedService ucms1 with(nolock) ON uc.UslugaComplex_id = ucms1.UslugaComplex_id
				INNER JOIN v_MedService ms with(nolock) ON ucms1.MedService_id = ms.MedService_id
				{$join}
			where
				{$filter}
			order by case when uc.Lpu_id = :Lpu_id then 1 else 2 end, Cat.UslugaCategory_Code, uc.UslugaComplex_Code
		";
		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}

	/**
     *	Читает список
     *	@param array $data
     *	@return bool|mixed
     */
	function loadKsgEkbList($data) {
		$filters = array();
		$tarifffilters = "";
		$options = getOptions();
		$queryParams = array();

		if (empty($data['onDate'])) {
			$onDate = null;
			if (!empty($data['EvnPS_id'])) {
				$onDate = $this->getFirstResultFromQuery("
					select top 1
						max(isnull(ES.EvnSection_disDate, ES.EvnSection_setDate)) as onDate
					from v_EvnSection ES with(nolock)
					where ES.EvnSection_pid = :EvnPS_id
				", $data, true);
				if ($onDate === false) {
					return false;
				}
			}

			if (!empty($data['EvnSection_disDate']) && $onDate < date_create($data['EvnSection_disDate'])) {
				$data['onDate'] = $data['EvnSection_disDate'];
			} else if (!empty($data['EvnSection_setDate']) && $onDate < date_create($data['EvnSection_setDate'])) {
				$data['onDate'] = $data['EvnSection_setDate'];
			} else if (!empty($onDate)) {
				$data['onDate'] = $onDate->format('Y-m-d');
			}
		}
		//print_r(array($data['onDate']));exit;

		// Загружаем конкретную запись
		if ( !empty($data['Mes_id']) ) {
			$filters[] = "mu.Mes_id = :Mes_id";
			$queryParams['Mes_id'] = $data['Mes_id'];
		} else {
			$uslugaCategoryList = array();

			if ($data['hasLinkWithGost2011']) {
				$filters[] = "uc.UslugaComplex_2011id is not null";
			}

			// Строка поиска
			if ( !empty($data['query']) ) {
				// Добавляем поиск по строке с транслитерацией
				// http://redmine.swan.perm.ru/issues/17426
				// Исключаем случаи, когда идет поиск по шаблону кода посещения
				// https://redmine.swan.perm.ru/issues/18130
				// 2013-06-06 Убираем добавление точки (https://redmine.swan.perm.ru/issues/20035, https://redmine.swan.perm.ru/issues/20062)
				/*
				if ( strlen($data['query']) >= 2 && !is_numeric($data['query'][0]) && $data['query'][0] != '%' && is_numeric($data['query'][1]) ) {
					$data['query'] = $data['query'][0] . '.' . substr($data['query'], 1);
				}
				*/
				if ( strpos($data['query'], '%') !== false ) {
					$queryParams['queryCode'] = $data['query'];
					$filters[] = "cast(uc.UslugaComplex_Code as varchar(50)) like :queryCode";
				}
				else {
					$queryParams['queryCode'] = $data['query'] . '%';
					$queryParams['queryCodeTL'] = sw_translit($data['query']) . '%';
					$queryParams['queryName'] = '%'. $data['query'] . '%';

					$filters[] = "(cast(uc.UslugaComplex_Code as varchar(50)) like :queryCode
						or cast(uc.UslugaComplex_Code as varchar(50)) like :queryCodeTL
						or rtrim(isnull(uc.UslugaComplex_Name, '')) like :queryName
					)";
				}
			}

			// Категория услуги
			if ( !empty($data['UslugaCategory_id']) ) {
				$query = "
					select top 1 UslugaCategory_SysNick
					from v_UslugaCategory with (nolock)
					where UslugaCategory_id = :UslugaCategory_id
				";
				$result = $this->db->query($query, array('UslugaCategory_id' => $data['UslugaCategory_id']));

				if ( !is_object($result) ) {
					return false;
				}

				$response = $result->result('array');

				if ( is_array($response) && count($response) > 0 ) {
					$uslugaCategoryList[] = $response[0]['UslugaCategory_SysNick'];
				}
			}
			// Список категорий услуги
			else if ( !empty($data['uslugaCategoryList']) ) {
				$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);
			}

			if (!empty($data['DispFilter'])) {
				switch($data['DispFilter']) {
					case 'DispOrp13SecVizit':
						if ( $data['session']['region']['nick'] == 'ekb' ) {
							$filters[] = "ucp.UslugaComplexPartition_Code = '300'";
							// и должно быть в SurveyTypeLink для данного DispClass_id
							if (!empty($data['DispClass_id'])) {
								$filters[] = "uc.UslugaComplex_id in (select UslugaComplex_id from v_SurveyTypeLink stl (nolock) where stl.DispClass_id = :DispClass_id)";
								$queryParams['DispClass_id'] = $data['DispClass_id'];
							}
						} elseif ( $data['session']['region']['nick'] == 'ufa' ) {
							$filters[] = "uc.UslugaComplex_Code like 'B.%'";
						} elseif ( $data['session']['region']['nick'] == 'buryatiya' ) {
							$filters[] = "
								(uc.UslugaComplex_Code like '161%' or uc.UslugaComplex_Code like '163%') and uc.UslugaComplexLevel_id <> 1
							";
						} elseif ( $data['session']['region']['nick'] == 'pskov' ) {
							$filters[] = "uc.UslugaComplex_Code like 'B%'";
						} else {
							$filters[] = "(uc.UslugaComplex_Code like '01%' or uc.UslugaComplex_Code = '05000304')";
						}
					break;

					case 'DispOrp13SecUsluga':
						if ( $data['session']['region']['nick'] == 'ekb' ) {
							$filters[] = "ucp.UslugaComplexPartition_Code = '301'";
							// и должно быть в SurveyTypeLink для данного DispClass_id
							if (!empty($data['DispClass_id'])) {
								$filters[] = "uc.UslugaComplex_id in (select UslugaComplex_id from v_SurveyTypeLink stl (nolock) where stl.DispClass_id = :DispClass_id)";
								$queryParams['DispClass_id'] = $data['DispClass_id'];
							}
						} elseif ( $data['session']['region']['nick'] == 'ufa' ) {
							$filters[] = "(uc.UslugaComplex_Code like 'А%' or uc.UslugaComplex_Code like 'B.03.%')";
						} elseif ( $data['session']['region']['nick'] == 'pskov' ) {
							$filters[] = "(uc.UslugaComplex_Code like 'А%' or uc.UslugaComplex_Code like 'B03.%')";
						} else {
							$filters[] = "uc.UslugaComplex_Code like '02%'";
						}
					break;
				}
			}

			if (!empty($data['uslugaComplexCodeList'])) {
				$uslugaComplexCodeList = json_decode($data['uslugaComplexCodeList'], true);
				$filters[] = "uc.UslugaComplex_Code in ('" . implode("', '", $uslugaComplexCodeList) . "')";
			}

			if ( empty($data['UslugaComplexPartition_CodeList']) && is_array($uslugaCategoryList) && count($uslugaCategoryList) > 0 ) {
				$filters[] = "ucat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "')";
			}

			// Список допустимых атрибутов
			if ( !empty($data['allowedUslugaComplexAttributeList']) ) {
				$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);

				if ( is_array($allowedUslugaComplexAttributeList) && count($allowedUslugaComplexAttributeList) > 0 ) {
					if  ( $data['allowedUslugaComplexAttributeMethod'] == 'and' ) {
						foreach ( $allowedUslugaComplexAttributeList as $v ) {
							$filters[] = "exists (
								select t1.UslugaComplexAttribute_id
								from UslugaComplexAttribute t1 with (nolock)
									inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
								where t1.UslugaComplex_id = uc.UslugaComplex_id
									and t2.UslugaComplexAttributeType_SysNick = '" . $v . "'
							)";
						}
					}
					else {
						$filters[] = "exists (
							select t1.UslugaComplexAttribute_id
							from UslugaComplexAttribute t1 with (nolock)
								inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where t1.UslugaComplex_id = uc.UslugaComplex_id
								and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
						)";
					}
				}
			}

			// Список недопустимых атрибутов
			if ( !empty($data['disallowedUslugaComplexAttributeList']) ) {
				$disallowedUslugaComplexAttributeList = json_decode($data['disallowedUslugaComplexAttributeList'], true);

				if ( is_array($disallowedUslugaComplexAttributeList) && count($disallowedUslugaComplexAttributeList) > 0 ) {
					$filters[] = "not exists (
						select t1.UslugaComplexAttribute_id
						from UslugaComplexAttribute t1 with (nolock)
							inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = uc.UslugaComplex_id
							and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $disallowedUslugaComplexAttributeList) . "')
					)";
				}
			}

			// Дата актуальности услуги
			if ( !empty($data['UslugaComplex_Date']) ) {
				$filters[] = "uc.UslugaComplex_begDT <= :UslugaComplex_Date";
				$filters[] = "(uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT > :UslugaComplex_Date)";
				$queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
			}
			else {
				// [savage]: Не помню, зачем я добавил этот фильтр, но убираю, ибо http://redmine.swan.perm.ru/issues/17417
				// $filters[] = "(uc.UslugaComplex_endDT IS NULL or uc.UslugaComplex_endDT >= dbo.tzGetDate())";
			}

			if (!empty($data['UslugaComplexPartition_CodeList'])) {
				$UslugaComplexPartition_CodeList = json_decode($data['UslugaComplexPartition_CodeList'], true);
				if (!empty($UslugaComplexPartition_CodeList)) {
					$filters[] = "ucp.UslugaComplexPartition_Code IN ('" . implode("', '", $UslugaComplexPartition_CodeList) . "')";
				}

				if (!empty($data['Person_id'])) {
					/*$queryParams['PersonAgeGroup_id'] = $this->getFirstResultFromQuery("SELECT top 1 case when dbo.Age2(Person_BirthDay, dbo.tzGetDate()) < 18 then 2 else 1 end as PersonAgeGroup_id FROM v_PersonState (nolock) WHERE Person_id = :Person_id", array('Person_id' => $data['Person_id']));
					if (!empty($queryParams['PersonAgeGroup_id'])) {
						$filters[] = "ISNULL(ucpl.PersonAgeGroup_id, :PersonAgeGroup_id) = :PersonAgeGroup_id";
					}*/ // TODO это очевидно нужно будет доработать, в зависимости от новых возрастных групп в екб
					$queryParams['Sex_id'] = $this->getFirstResultFromQuery("SELECT top 1 ISNULL(Sex_id, 3) as Sex_id FROM v_PersonState (nolock) WHERE Person_id = :Person_id", array('Person_id' => $data['Person_id']));
					if (!empty($queryParams['Sex_id'])) {
						$filters[] = "ISNULL(ucpl.Sex_id, :Sex_id) = :Sex_id";
					}
				}
				if (!empty($data['MedPersonal_id']) && !empty($data['LpuSection_id'])) {
					$filters[] = "
						(ucpl.MedSpecOms_id is null or ucpl.MedSpecOms_id IN (
							select
								MedSpecOms_id
							from
								v_MedStaffFact msf (nolock)
							where
								msf.MedPersonal_id = :MedPersonal_id and msf.LpuSection_id = :LpuSection_id
						))
					";
					$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				if (!empty($data['PayType_id'])) {
					$filters[] = "ISNULL(ucpl.PayType_id, :PayType_id) = :PayType_id";
					$queryParams['PayType_id'] = $data['PayType_id'];
				}
			}

			// Идентификатор родительской услуги
			if ( !empty($data['UslugaComplex_pid']) ) {
				$filters[] = "uc.UslugaComplex_pid = :UslugaComplex_pid";
				$queryParams['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
			}
			elseif (empty($data['UslugaComplexPartition_CodeList'])) {

				$filters[] = "(
					uc.UslugaComplexLevel_id in (7, 8, 10)
					or ucat.UslugaCategory_SysNick = 'lpu' -- для услуг лпу поле UslugaComplex_pid не используется (связь в UslugaComplexComposition)
					or (ucat.UslugaCategory_SysNick not in ('tfoms', 'pskov_foms', 'gost2004', 'gost2011', 'Kod7', 'classmedus', 'lpusectiontree') and uc.UslugaComplex_pid is null)
					or (ucat.UslugaCategory_SysNick in ('tfoms', 'pskov_foms', 'stomoms', 'stomklass') and uc.UslugaComplex_pid is not null)
				)";
			}

			if ( !empty($data['UslugaComplex_2011id']) ) {
				//ищем либо по UslugaComplex_2011id либо по UslugaComplex_id
				$filters[] = "(uc.UslugaComplex_2011id = :UslugaComplex_2011id or uc.UslugaComplex_id = :UslugaComplex_2011id )";
				$queryParams['UslugaComplex_2011id'] = $data['UslugaComplex_2011id'];
			}

			if ( !empty($data['onDate']) ) {
				$filters[] = "(uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT >= :onDate)";
				$filters[] = "(uc.UslugaComplex_begDT <= :onDate)";

				$filters[] = "(mu.MesUsluga_endDT is null or mu.MesUsluga_endDT >= :onDate)";
				$filters[] = "(mu.MesUsluga_begDT <= :onDate)";

				$filters[] = "(mes.Mes_endDT is null or mes.Mes_endDT >= :onDate)";
				$filters[] = "(mes.Mes_begDT <= :onDate)";

				$queryParams['onDate'] = $data['onDate'];

				$tarifffilters .= " and (UslugaComplexTariff_endDate is null or UslugaComplexTariff_endDate >= :onDate)";
				$tarifffilters .= " and (UslugaComplexTariff_begDate <= :onDate)";

				$filters[] = " (ucpl.UslugaComplexPartitionLink_endDT is null or ucpl.UslugaComplexPartitionLink_endDT >= :onDate)";
				$filters[] = " (ucpl.UslugaComplexPartitionLink_begDT <= :onDate)";
			}

			if ( !empty($data['Diag_id']) ) {
				// если не задан более чётки фильтр по диагнозу, то используем этот
				if (empty($data['DiagFilter']) && empty($data['DiagGroupFilter'])) {
					$diagfilters = "";
					if (!empty($data['onDate'])) {
						$diagfilters .= " and (UCPDL.UslugaComplexPartitionDiagLink_endDate is null or UCPDL.UslugaComplexPartitionDiagLink_endDate >= :onDate)";
						$diagfilters .= " and (UCPDL.UslugaComplexPartitionDiagLink_begDate <= :onDate)";
					}
					$filters[] = " exists(
						select top 1
							UCPDL.UslugaComplexPartitionDiagLink_id
						from
							r66.v_UslugaComplexPartitionDiagLink UCPDL (nolock)
						where
							UCPDL.UslugaComplexPartitionLink_id = ucpl.UslugaComplexPartitionLink_id
							and UCPDL.Diag_id = :Diag_id
							{$diagfilters}

						union all

						select top 1
							UCPDL.UslugaComplexPartitionDiagLink_id
						from
							r66.v_UslugaComplexPartitionDiagLink UCPDL (nolock)
							inner join r66.v_GroupDiag gd (nolock) on gd.GroupDiagCode_id = UCPDL.GroupDiagCode_id
						where
							UCPDL.UslugaComplexPartitionLink_id = ucpl.UslugaComplexPartitionLink_id
							and gd.Diag_id = :Diag_id
							{$diagfilters}
					)";

					$queryParams['Diag_id'] = $data['Diag_id'];
				}
			}

			$checkfilter = "1=0";
			if (!empty($data['DiagFilter'])) {
				if (!empty($data['Diag_id'])) {
					$queryParams['Diag_id'] = $data['Diag_id'];

					$diagfilters = "";
					if ( !empty($data['onDate']) ) {
						$diagfilters .= " and (UCPDL.UslugaComplexPartitionDiagLink_endDate is null or UCPDL.UslugaComplexPartitionDiagLink_endDate >= :onDate)";
						$diagfilters .= " and (UCPDL.UslugaComplexPartitionDiagLink_begDate <= :onDate)";
					}

					$checkfilter .= " or exists(
						select top 1
							UCPDL.UslugaComplexPartitionDiagLink_id
						from
							r66.v_UslugaComplexPartitionDiagLink UCPDL (nolock)
						where
							UCPDL.UslugaComplexPartitionLink_id = ucpl.UslugaComplexPartitionLink_id
							and UCPDL.Diag_id = :Diag_id
							{$diagfilters}
					)";
				} else {
					$checkfilter .= " or 1=0";
				}
			}

			if (!empty($data['DiagGroupFilter'])) {
				if (!empty($data['Diag_id'])) {
					$queryParams['Diag_id'] = $data['Diag_id'];

					$diagfilters = "";
					if ( !empty($data['onDate']) ) {
						$diagfilters .= " and (UCPDL.UslugaComplexPartitionDiagLink_endDate is null or UCPDL.UslugaComplexPartitionDiagLink_endDate >= :onDate)";
						$diagfilters .= " and (UCPDL.UslugaComplexPartitionDiagLink_begDate <= :onDate)";
					}

					$checkfilter .= " or exists(
						select top 1
							UCPDL.UslugaComplexPartitionDiagLink_id
						from
							r66.v_UslugaComplexPartitionDiagLink UCPDL (nolock)
							inner join r66.v_GroupDiag gd (nolock) on gd.GroupDiagCode_id = UCPDL.GroupDiagCode_id
						where
							UCPDL.UslugaComplexPartitionLink_id = ucpl.UslugaComplexPartitionLink_id
							and gd.Diag_id = :Diag_id
							{$diagfilters}
					)";
				} else {
					$checkfilter .= " or 1=0";
				}
			}

			if (!empty($data['UslugaComplexFilter'])) {
				if (!empty($data['EvnSection_id'])) {
					$queryParams['EvnSection_id'] = $data['EvnSection_id'];

					$uslugafilters = "";
					if ( !empty($data['onDate']) ) {
						$uslugafilters .= " and (mouc.MesOldUslugaComplex_endDT is null or mouc.MesOldUslugaComplex_endDT >= :onDate)";
						$uslugafilters .= " and (mouc.MesOldUslugaComplex_begDT <= :onDate)";
					}

					$checkfilter .= " or exists(
						select top 1
							eu.EvnUsluga_id
						from
							v_EvnUsluga eu (nolock)
							inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id
							inner join v_MesUsluga mu (nolock) on mu.Mes_id = mouc.Mes_id
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and mu.UslugaComplex_id = uc.UslugaComplex_id
							and ucpl.UslugaComplexPartitionLink_IsMesSid = 2
							{$uslugafilters}
					)";
				} else {
					$checkfilter .= " or 1=0";
				}
			}

			// если ни один фильтр не задан выводим все.
			if ($checkfilter == "1=0") {
				$checkfilter = "1=1";
			}
		}

		$beforequery = "";
		if (!empty($data['PersonAgeGroupFilter'])) {
			if (!empty($data['Person_id']) && !empty($data['EvnSection_setDate'])) {
				$beforequery = "
					declare
						@Person_Age bigint,
						@Person_AgeDays bigint,
						@Person_AgeMonths bigint;

					select
						@Person_Age = dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate),
						@Person_AgeDays = datediff(day, PS.Person_BirthDay, :EvnSection_setDate),
						@Person_AgeMonths = datediff(month, PS.Person_BirthDay, :EvnSection_setDate)
					from
						v_PersonState PS (nolock)
					where
						Person_id = :Person_id;
				";

				$queryParams['Person_id'] = $data['Person_id'];
				$queryParams['EvnSection_setDate'] = $data['EvnSection_setDate'];

				$filters[] = "(
					(@Person_Age >= 18 and pag.PersonAgeGroup_Code = 1)
					or (@Person_AgeMonths < 222 and pag.PersonAgeGroup_Code = 2) -- 18 лет + 6 месяцев = 18*12+6 = 222 месяца
					or (@Person_AgeMonths <= 3 and pag.PersonAgeGroup_Code = 3)
					or (@Person_AgeDays <= 28 and pag.PersonAgeGroup_Code = 4)
					or (@Person_Age <= 4 and pag.PersonAgeGroup_Code = 5)
					or (@Person_Age < 1 and pag.PersonAgeGroup_Code = 6)
					or (@Person_Age >= 14 and pag.PersonAgeGroup_Code = 7)
					or (pag.PersonAgeGroup_Code IS NULL)
				)";
			}
		}

		$queryParams['UslugaComplexTariff_Name'] = null;
		if (!empty($data['LpuSection_id'])) {
			$query = "
				select
					LpuSection_Level
				from
					v_LpuSection (nolock)
				where
					LpuSection_id = :LpuSection_id;
			";
			$queryParams['LpuSection_Level'] = $this->getFirstResultFromQuery($query, array(
				'LpuSection_id' => $data['LpuSection_id']
			));
		}

		if (empty($queryParams['LpuSection_Level'])) {
			$queryParams['LpuSection_Level'] = null;
		}

		if (!empty($UslugaComplexPartition_CodeList)) {
			if (in_array('101', $UslugaComplexPartition_CodeList)) {
				$queryParams['UslugaComplexTariff_Name'] = '101';
			} else if (in_array('201', $UslugaComplexPartition_CodeList)) {
				$queryParams['UslugaComplexTariff_Name'] = '201';
			}
		}

		$filters = implode(' and ', $filters);
		if (!empty($checkfilter)) {
			if (!empty($filters)) {
				$filters .= " and ({$checkfilter})";
			} else {
				$filters .= "({$checkfilter})";
			}
		}

		$query = "
			{$beforequery}

			select distinct
				mu.Mes_id
				,uc.UslugaComplex_id
				,uc.UslugaComplex_2011id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,ucat.UslugaCategory_SysNick
				,uc.UslugaComplex_pid
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
				,uc.UslugaComplex_Code
				,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
				,PAG.PersonAgeGroup_Name
				,case when ucpl.UslugaComplexPartitionLink_IsMesSid = 2 then 'true' else 'false' end as UslugaComplexPartitionLink_IsMesSid
				,case when ucpl.UslugaComplexPartitionLink_IsFullPay = 2 then 'true' else 'false' end as UslugaComplexPartitionLink_IsFullPay
				,case when ucpl.UslugaComplexPartitionLink_IsUseLS = 2 then 'true' else 'false' end as UslugaComplexPartitionLink_IsUseLS
				,case when ucpl.UslugaComplexPartitionLink_Signrao = 2 then 'true' else 'false' end as UslugaComplexPartitionLink_Signrao
				,ucpl.LpuSectionProfile_id
				,ucpl.MedSpecOms_id
				,uct.UslugaComplexTariff_Tariff
			from
				v_MesUsluga mu (nolock)
				inner join v_MesOld mes with (nolock) on mes.Mes_id = mu.Mes_id
				inner join v_UslugaComplex uc with (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
				left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				left join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = uc.UslugaComplex_id
				left join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
				left join v_PersonAgeGroup pag (nolock) on pag.PersonAgeGroup_id = ucpl.PersonAgeGroup_id
				outer apply(
					select top 1
						UslugaComplexTariff_Tariff
					from
						v_UslugaComplexTariff (nolock)
					where
						UslugaComplexTariff_Name = :UslugaComplexTariff_Name
						and UslugaComplexTariff_Code = :LpuSection_Level
						and UslugaComplex_id = uc.UslugaComplex_id
						{$tarifffilters}
				) uct
			where
				{$filters}
			order by
				uc.UslugaComplex_Code
		";

		//echo getDebugSql($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка уникальности услуги ФОМС для указанного отделения
	 */
	function checkUslugaComplex($data)
	{
		$query = "
			Select top 1
				ls.LpuSection_Name,
				ISNULL(u.Usluga_Name, ug.UslugaGost_Name) as Usluga_Name,
				ISNULL(u.Usluga_Code, ug.UslugaGost_Code) as Usluga_Code
			from
				v_UslugaComplex uc with (NOLOCK) 
				left join v_LpuSection ls with (NOLOCK) on uc.LpuSection_id = ls.LpuSection_id
				left join v_Usluga u with (NOLOCK) on uc.Usluga_id = u.Usluga_id 
				left join v_UslugaGost ug with (NOLOCK) on uc.UslugaGost_id = ug.UslugaGost_id 
			where
				uc.LpuSection_id = :LpuSection_id
				and uc.Usluga_id = :Usluga_id
				and (
				     (uc.UslugaComplex_begDT <= :UslugaComplex_endDT or :UslugaComplex_endDT is null)
				     and
				     (uc.UslugaComplex_endDT >= :UslugaComplex_begDT or uc.UslugaComplex_endDT is null)
				    )
			";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Функция читает из базы список структуры ГОСТ согласно переданным параметрам и уровням
	 */
	function loadUslugaGostList($data)
	{
		$params = array(
			'UslugaGost_id' => $data['UslugaGost_id'],
			'UslugaGost_pid' => $data['UslugaGost_pid'],
			'UslugaLevel_id' => $data['UslugaLevel_id']
			);
		$filter = "(1=1)";
		if ($data['UslugaGost_id']>0) {
			$filter .= " and ug.UslugaGost_id = :UslugaGost_id";
		}
		if ($data['UslugaLevel_id']>0) {
			$filter .= " and ug.UslugaLevel_id = :UslugaLevel_id";
		}
		if ($data['UslugaGost_pid']>0) {
			$filter .= " and ug.UslugaGost_pid = :UslugaGost_pid";
		}
		// поиск для выбора в комбобокса
		if (strlen($data['query'])>0) {
			$filter .= " and ug.UslugaGost_Name like :query";
			$params['query'] = "%".$data['query']."%";
		}
		
		// поиск для списка
		if (strlen($data['UslugaGost_Name'])>0) {
			$filter .= " and ug.UslugaGost_Name like :UslugaGost_Name";
			$params['UslugaGost_Name'] = "%".$data['UslugaGost_Name']."%";
		}
		if (strlen($data['UslugaGost_Code'])>0) {
			$filter .= " and ug.UslugaGost_Code like :UslugaGost_Code";
			$params['UslugaGost_Code'] = "%".$data['UslugaGost_Code']."%";
		}
		$query = "
		Select top 30
				ug.UslugaGost_id,
				RTrim(ug.UslugaGost_Code) as UslugaGost_Code,
				RTrim(ug.UslugaGost_Name) as UslugaGost_Name,
				ug.UslugaGost_pid,
				ug.UslugaLevel_id
			from
				v_UslugaGost ug with (NOLOCK)
			where
				ug.UslugaGost_pid is not null and 
				{$filter}
			";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}


	/**
     *	Читает список тарифов комплексной услуги для комбо на формах редактирования услуг
     *	@param array $data
     *	@return bool|mixed
     */
	function loadUslugaComplexTariffList($data) {
		$filterList = array();
		$queryParams = array();
		$gridFields = '';

		if ( !empty($data['UslugaComplexTariff_id']) ) {
			$filterList[] = 'uct.UslugaComplexTariff_id = :UslugaComplexTariff_id';
			$queryParams['UslugaComplexTariff_id'] = $data['UslugaComplexTariff_id'];
		} else {
			$uc_filter = 'uct.UslugaComplex_id = :UslugaComplex_id';
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			if (isset($data['in_UslugaComplex_list'])) {
				// список для услуг, выбранных из пакета $data['UslugaComplex_id']
				$gridFields = ',uct.UslugaComplex_id';
				$uc_filter = 'uct.UslugaComplex_id in ('. $data['in_UslugaComplex_list'] .')';
				unset($queryParams['UslugaComplex_id']);
			}
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$queryParams['PayType_id'] = $data['PayType_id'];
			$queryParams['Person_id'] = $data['Person_id'];
			$queryParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];

			if (!empty($data['UEDAboveZero'])) {
				$uc_filter .= " and uct.UslugaComplexTariff_UED > 0";
			}

			$uslugaComplexTariffList = array();

			// Предварительный запрос
			$query = "
				declare
					 @LpuSection_id bigint
					,@LpuSectionProfile_id bigint
					,@LpuUnit_id bigint
					,@LpuUnitType_id bigint
					,@LpuBuilding_id bigint
					,@Lpu_id bigint
					,@LpuLevel_id bigint
					,@MesAgeGroup_id bigint
					,@MesAgeGroup_extid bigint
					,@Person_Age int
					,@Sex_id bigint;

				select top 1
					 @Person_Age = dbo.Age2(Person_BirthDay, cast(:UslugaComplexTariff_Date as datetime))
					,@Sex_id = Sex_id
				from v_PersonState with (nolock)
				where Person_id = :Person_id

				if ( @Person_Age < 18 )
					set @MesAgeGroup_id = 2; -- Дети
				else
					set @MesAgeGroup_id = 1; -- Взрослые

				-- https://redmine.swan.perm.ru/issues/99855
				if ( @Person_Age < 1 )
					set @MesAgeGroup_extid = 10; -- до 1 года
				else if ( @Person_Age <= 18 )
					set @MesAgeGroup_extid = 5; -- от 0 дней до 18 лет (включительно)

				select top 1
					 @LpuSection_id = t1.LpuSection_id
					,@LpuSectionProfile_id = t1.LpuSectionProfile_id
					,@LpuUnit_id = t2.LpuUnit_id
					,@LpuUnitType_id = t2.LpuUnitType_id
					,@LpuBuilding_id = t3.LpuBuilding_id
					,@Lpu_id = t3.Lpu_id
					,@LpuLevel_id = t4.LpuLevel_id
				from v_LpuSection t1 with (nolock)
					inner join v_LpuUnit t2 with (nolock) on t2.LpuUnit_id = t1.LpuUnit_id
					inner join v_LpuBuilding t3 with (nolock) on t3.LpuBuilding_id = t2.LpuBuilding_id
					inner join v_Lpu t4 with (nolock) on t4.Lpu_id = t3.Lpu_id
				where t1.LpuSection_id = :LpuSection_id

				select
					 uct.UslugaComplexTariff_id
					,ISNULL(uctt.UslugaComplexTariffType_Code, 0) as UslugaComplexTariffType_Code
					,ISNULL(pt.PayType_SysNick, '') as PayType_SysNick
					,uct.LpuSection_id as LpuSection_tid
					,uct.LpuSectionProfile_id as LpuSectionProfile_tid
					,uct.LpuUnitType_id as LpuUnitType_tid
					,uct.LpuBuilding_id as LpuBuilding_tid
					,uct.LpuUnit_id as LpuUnit_tid
					,uct.Lpu_id as Lpu_tid
					,uct.LpuLevel_id as LpuLevel_tid
					,uct.MesAgeGroup_id as MesAgeGroup_tid
					,uct.Sex_id as Sex_tid
					,@LpuSection_id as LpuSection_id
					,@LpuSectionProfile_id as LpuSectionProfile_id
					,@LpuUnit_id as LpuUnit_id
					,@LpuUnitType_id as LpuUnitType_id
					,@LpuBuilding_id as LpuBuilding_id
					,@Lpu_id as Lpu_id
					,@LpuLevel_id as LpuLevel_id
					,@MesAgeGroup_id as MesAgeGroup_id
					,@MesAgeGroup_extid as MesAgeGroup_extid
					,@Sex_id as Sex_id
				from v_UslugaComplexTariff uct with (nolock)
					inner join v_UslugaComplexTariffType uctt with (nolock) on uctt.UslugaComplexTariffType_id = uct.UslugaComplexTariffType_id
					inner join v_PayType pt with (nolock) on pt.PayType_id = uct.PayType_id
				where
					uct.PayType_id = :PayType_id
					and {$uc_filter}
					and cast(uct.UslugaComplexTariff_begDate as date) <= cast(:UslugaComplexTariff_Date as date)
					and (uct.UslugaComplexTariff_endDate is null or cast(uct.UslugaComplexTariff_endDate as date) >= cast(:UslugaComplexTariff_Date as date))
			";
			//echo getDebugSql($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( !is_array($response) && count($response) > 0 ) {
				return array();
			}

			$isLpuSection = false;
			$isLpuUnit = false;
			$isLpuBuilding = false;

			foreach ( $response as $row => $record ) {
				if ( (!empty($record['Sex_tid']) && $record['Sex_id'] != $record['Sex_tid'])
					|| (
						// Для Карелии учитываем дополнительные возрастные группы
						// @task https://redmine.swan.perm.ru/issues/99855
						!empty($record['MesAgeGroup_tid'])
						&& $record['MesAgeGroup_id'] != $record['MesAgeGroup_tid']
						&& ($this->regionNick != 'kareliya' || $record['MesAgeGroup_extid'] != $record['MesAgeGroup_tid'])
					)

					|| (empty($data['IsSmp']) && (
						(!empty($record['LpuSectionProfile_tid']) && $record['LpuSectionProfile_id'] != $record['LpuSectionProfile_tid'])
						|| (!empty($record['LpuUnitType_tid']) && $record['LpuUnitType_id'] != $record['LpuUnitType_tid'])
						|| (!empty($record['LpuLevel_tid']) && $record['LpuLevel_id'] != $record['LpuLevel_tid'])
						|| (!empty($record['Lpu_tid']) && $record['Lpu_id'] != $record['Lpu_tid'])
					))

					|| (!empty($data['IsSmp']) && (
						empty($record['LpuUnitType_tid'])
						|| !in_array($record['LpuUnitType_tid'], array(13,14))
						|| (!empty($record['Lpu_tid']) && !empty($data['session']['lpu_id']) && $data['session']['lpu_id'] != $record['Lpu_tid']) // фильтрация по МО пользователя для услуг СМП, т.к. у врачей СМП нет отделений.
					))

					|| $this->additionalUslugaComplexTariffCondition($record)
				) {
					unset($response[$row]);
				}
				else {
					if ( !empty($record['LpuSection_tid']) && $record['LpuSection_id'] == $record['LpuSection_tid'] ) {
						$isLpuSection = true;
					}
					else if ( empty($record['LpuSection_tid']) && !empty($record['LpuUnit_tid']) && $record['LpuUnit_id'] == $record['LpuUnit_tid'] ) {
						$isLpuUnit = true;
					}
					else if ( empty($record['LpuSection_tid']) && empty($record['LpuUnit_tid']) && !empty($record['LpuBuilding_tid']) && $record['LpuBuilding_id'] == $record['LpuBuilding_tid'] ) {
						$isLpuBuilding = true;
					}
				}
			}

			foreach ( $response as $row => $record ) {
				// Тарифы для всех ЛПУ
				if ( empty($record['Lpu_tid']) ) {
					$uslugaComplexTariffList[] = $record['UslugaComplexTariff_id'];
				}

				if (
					// Тарифы отделения
					($isLpuSection === true && $record['LpuSection_id'] == $record['LpuSection_tid'])
					// Тарифы группы отделений
					|| ($isLpuUnit === true && empty($record['LpuSection_tid']) && $record['LpuUnit_id'] == $record['LpuUnit_tid'])
					// Тарифы подразделения
					|| ($isLpuBuilding === true && empty($record['LpuSection_tid']) && empty($record['LpuUnit_tid']) && $record['LpuBuilding_id'] == $record['LpuBuilding_tid'])
					// Тарифы ЛПУ
					|| ($isLpuSection === false && $isLpuUnit === false && $isLpuBuilding === false && empty($record['LpuSection_tid']) && empty($record['LpuUnit_tid']) && empty($record['LpuBuilding_tid']))
				) {
					if (!in_array($record['UslugaComplexTariff_id'], $uslugaComplexTariffList)) {
						$uslugaComplexTariffList[] = $record['UslugaComplexTariff_id'];
					}
				}
			}

			if ( count($uslugaComplexTariffList) == 0 ) {
				return array();
			}

			$filterList[] = "uct.UslugaComplexTariff_id in (" . implode(', ', $uslugaComplexTariffList) . ")";
		}

		$joinStr = '';

		if ( !empty($data['IsForGrid']) ) {
			$joinStr = "
				left join v_PayType pt with (nolock) on pt.PayType_id = uct.PayType_id
				left join v_Lpu l with (nolock) on l.Lpu_id = uct.Lpu_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = uct.LpuBuilding_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = uct.LpuUnit_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = uct.LpuSection_id
				left join v_Sex s with (nolock) on s.Sex_id = uct.Sex_id
				left join v_MesAgeGroup mag with (nolock) on mag.MesAgeGroup_id = uct.MesAgeGroup_id
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = uct.LpuSectionProfile_id
				left join v_LpuLevel ll with (nolock) on ll.LpuLevel_id = uct.LpuLevel_id
			";
			$gridFields = "
				,ISNULL(pt.PayType_Name, '') as PayType_Name
				,ISNULL(ll.LpuLevel_Name, '') as LpuLevel_Name
				,ISNULL(l.Lpu_Nick, '') + ISNULL(', ' + lb.LpuBuilding_Name, '') + ISNULL(', ' + lu.LpuUnit_Name, '') + ISNULL(', ' + ls.LpuSection_Name, '') as Lpu_Name
				,ISNULL(lsp.LpuSectionProfile_Name, '') as LpuSectionProfile_Name
				,ISNULL(mag.MesAgeGroup_Name, '') as MesAgeGroup_Name
				,ISNULL(s.Sex_Name, '') as Sex_Name
			";
		}

		$query = "
			select
				 uct.UslugaComplexTariff_id
				,convert(varchar(10), uct.UslugaComplexTariff_begDate, 104) as UslugaComplexTariff_begDate
				,convert(varchar(10), uct.UslugaComplexTariff_endDate, 104) as UslugaComplexTariff_endDate
				,ISNULL(uct.UslugaComplexTariff_Code,'0') as UslugaComplexTariff_Code
				,ISNULL(NULLIF(uct.UslugaComplexTariff_Name, ''), '(без названия)') as UslugaComplexTariff_Name
				,LTRIM(STR(ISNULL(uct.UslugaComplexTariff_Tariff, 0), 10, 2)) as UslugaComplexTariff_Tariff
				,LTRIM(STR(ISNULL(uct.UslugaComplexTariff_UED, 0), 10, 2)) as UslugaComplexTariff_UED
				,LTRIM(STR(ISNULL(uct.UslugaComplexTariff_UEM, 0), 10, 2)) as UslugaComplexTariff_UEM
				,ISNULL(lut.LpuUnitType_Name, '') as LpuUnitType_Name
				,uct.Lpu_id
				" . $gridFields . "
			from v_UslugaComplexTariff uct with (nolock)
				inner join v_UslugaComplexTariffType uctt with (nolock) on uctt.UslugaComplexTariffType_id = uct.UslugaComplexTariffType_id
				left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = uct.LpuUnitType_id
				" . $joinStr . "
			where
				" . implode(' and ', $filterList) . "
		";
		// echo getDebugSql($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение списка тарифов
	 */
	function loadUslugaComplexTariffLloList($data) {
		$params = array();
		$filter = "";

		if(!empty($data['Year']) && $data['Year'] > 0) {
			$filter .= "
				and (uct.UslugaComplexTariff_begDate is null or datepart(year, uct.UslugaComplexTariff_begDate) <= :Year)
				and (uct.UslugaComplexTariff_endDate is null or datepart(year, uct.UslugaComplexTariff_endDate) >= :Year)
			";
			$params['Year'] = $data['Year'];
		}

		if(!empty($data['UslugaComplexTariff_Date'])) {
			$filter .= "
				and (uct.UslugaComplexTariff_begDate is null or uct.UslugaComplexTariff_begDate <= :UslugaComplexTariff_Date)
				and (uct.UslugaComplexTariff_endDate is null or uct.UslugaComplexTariff_endDate >= :UslugaComplexTariff_Date)
			";
			$params['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
		}

		$query = "
			declare @Region_id bigint;

			set @Region_id = dbo.GetRegion();

			select
				uct.UslugaComplexTariff_id,
				cast(uct.UslugaComplexTariff_Tariff as decimal(12,2)) as UslugaComplexTariff_Tariff,
				isnull(convert(varchar(10), uct.UslugaComplexTariff_begDate, 104), '') + isnull(' - '+convert(varchar(10), uct.UslugaComplexTariff_endDate,104), '') as UslugaComplexTariff_Date,
				uc.UslugaComplex_Name
			from
				UslugaComplexTariff uct with (nolock)
				left join UslugaComplex uc with (nolock) on uc.UslugaComplex_id = uct.UslugaComplex_id
				left join UslugaComplexTariffType uctt with (nolock) on uctt.UslugaComplexTariffType_id = uct.UslugaComplexTariffType_id
			where
				uctt.UslugaComplexTariffType_Code = 4 and--Тарифы ЛЛО
				(uc.Region_id is null or uc.Region_id = @Region_id)
				{$filter};
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 *	Проверка услуги по МЭС
	 */
	function checkUslugaComplexIsMes($data) {
		$query = "
			select top 1 uc.UslugaComplex_id
			from v_MesUsluga mu with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_2011id = mu.UslugaComplex_id
			where uc.UslugaComplex_id = :UslugaComplex_id
				and mu.Mes_id = :Mes_id
				and exists (
					select top 1 UslugaComplexTariff_id
					from v_UslugaComplexTariff with (nolock)
					where Lpu_id is null
						and UslugaComplex_id = uc.UslugaComplex_id
						and UslugaComplexTariff_UED = mu.MesUsluga_UslugaCount
				)
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) == 1 && !empty($response[0]['UslugaComplex_id']) ) {
			return array(array('UslugaComplex_IsMes' => 2));
		}
		else {
			return array(array('UslugaComplex_IsMes' => 1));
		}
	}

	/**
	 * Дополнительное условие для фильтрации списка тарифов
	 * @task https://redmine.swan.perm.ru/issues/29969
	 * Для Астрахани вынесено в региональную модель
	 */
	function additionalUslugaComplexTariffCondition($record) {
		return ($record['PayType_SysNick'] == 'oms' && $record['UslugaComplexTariffType_Code'] != 1);
	}

	/**
	 *	Сохранение тарифа ЛЛО
	 */
	function saveUslugaComplexTariffLlo($data) {
		$procedure = empty($data['UslugaComplexTariff_id']) ? 'p_UslugaComplexTariff_ins' : 'p_UslugaComplexTariff_upd';

		$query = "
			declare
				@UslugaComplexTariff_id bigint,
				@UslugaComplexTariffType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @UslugaComplexTariff_id = :UslugaComplexTariff_id;
			set @UslugaComplexTariffType_id = (select top 1 UslugaComplexTariffType_id from UslugaComplexTariffType with(nolock) where UslugaComplexTariffType_Code = 4); --Тарифы ЛЛО

			exec {$procedure}
				@UslugaComplexTariff_id = @UslugaComplexTariff_id output,
				@Server_id = :Server_id,
				@UslugaComplexTariffType_id = @UslugaComplexTariffType_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexTariff_Tariff = :UslugaComplexTariff_Tariff,
				@UslugaComplexTariff_begDate = :UslugaComplexTariff_begDate,
				@UslugaComplexTariff_endDate = :UslugaComplexTariff_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @UslugaComplexTariff_id as UslugaComplexTariff_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexTariff_id' => $data['UslugaComplexTariff_id'],
			'Server_id' => $data['Server_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_Tariff' => $data['UslugaComplexTariff_Tariff'],
			'UslugaComplexTariff_begDate' => $data['UslugaComplexTariff_begDate'],
			'UslugaComplexTariff_endDate' => $data['UslugaComplexTariff_endDate'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Удаление тарифа ЛЛО
	 */
	function deleteUslugaComplexTariffLlo($data) {
		$query = "
			select
				count(WhsDocumentTitleTariff_id) as cnt
			from
				v_WhsDocumentTitleTariff with(nolock)
			where
				UslugaComplexTariff_id = :UslugaComplexTariff_id;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'UslugaComplexTariff_id' => $data['id']
		));
		if ($result && $result > 0) {
			return array('Error_Msg' => 'Удаление невозможно. Тариф связан с правоустанавливающим документом.');
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_UslugaComplexTariff_del
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'UslugaComplexTariff_id' => $data['id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}


	/**
	 *	Загрузка данных тарифа ЛЛО
	 */
	function loadUslugaComplexTariffLlo($data) {
		$query = "
			select
				uct.UslugaComplexTariff_id,
				uct.UslugaComplexTariff_Tariff,
				convert(varchar(10), uct.UslugaComplexTariff_begDate, 104) as UslugaComplexTariff_begDate,
				convert(varchar(10), uct.UslugaComplexTariff_endDate,104) as UslugaComplexTariff_endDate,
				uct.UslugaComplex_id
			from
				UslugaComplexTariff uct with (nolock)
			where
				uct.UslugaComplexTariff_id = :UslugaComplexTariff_id
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение раздела услуги
	 */
	function getUslugaComplexPartition($data) {
		$params = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'MedSpecOms_id' => $data['MedSpecOms_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'Sex_id' => $data['Sex_id'],
			'PayType_id' => $data['PayType_id'],
			'IsMes' => $data['IsMes'] ? 2 : 1,
		);

		$query = "
			select
				UCP.UslugaComplexPartition_id,
				UCP.UslugaComplexPartition_Code,
				UCP.UslugaComplexPartition_Name
			from r66.v_UslugaComplexPartitionLink UCPL with(nolock)
			inner join r66.v_UslugaComplexPartition UCP with(nolock) on UCP.UslugaComplexPartition_id = UCPL.UslugaComplexPartition_id
			where
				UCPL.UslugaComplex_id = :UslugaComplex_id
				and (UCPL.Sex_id is null or UCPL.Sex_id = :Sex_id)
				and (UCPL.MedSpecOms_id is null or UCPL.MedSpecOms_id = :MedSpecOms_id)
				and (UCPL.LpuSectionProfile_id is null or UCPL.LpuSectionProfile_id = :LpuSectionProfile_id)
				and UCPL.PayType_id = :PayType_id
				and ISNULL(UCPL.UslugaComplexPartitionLink_IsMes, 1) = :IsMes
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка услуг для комбобокса (используется на форме редактирования тарифа ЛЛО)
	 */
	function loadUslugaComplexCombo($data) {
		$params = array();
		$filter = array();

		$filter[] = "(uc.Region_id is null or uc.Region_id = @Region_id)";

		if (!empty($data['UslugaComplex_id']) && $data['UslugaComplex_id'] > 0) {
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
			$filter[] = "uc.UslugaComplex_id = :UslugaComplex_id";
		} else {
			if (!empty($data['UslugaCategory_Code'])) {
				$params['UslugaCategory_Code'] = $data['UslugaCategory_Code'];
				$filter[] = "ucat.UslugaCategory_Code = :UslugaCategory_Code";
			}

			if (!empty($data['query'])) {
				$params['query'] = '%'.$data['query'].'%';
				$filter[] = "(cast(uc.UslugaComplex_Code as varchar) +' '+ RTrim(uc.UslugaComplex_Name)) LIKE :query";
			}
		}

		$query = "
			declare @Region_id bigint;

			set @Region_id = dbo.GetRegion();

			select top 500
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name
			from
				UslugaComplex uc with (nolock)
				left join v_UslugaCategory ucat with(nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
		";

		if (count($filter) > 0) {
			$query .= ' where '.join(' and ', $filter);
		}

		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение тупа атрибута и кода Гост услуги
	 */
	function getUslugaAtributTypeAndGost($data)
	{
		$params = [
			'UslugaComplex_id' => $data['UslugaComplex_id'],
		];

		$query = "
			select
			    UC.UslugaComplex_id,
				UC.UslugaComplex_Code as UslugaGost_Code,
				UCA.UslugaComplexAttribute_id,
				UGAT.UslugaComplexAttributeType_id,
				UGAT.UslugaComplexAttributeType_SysNick
			from
				v_UslugaComplex UC with(nolock)
				inner JOIN v_UslugaComplexAttribute UCA with(nolock) on UCA.UslugaComplex_id = UC.UslugaComplex_id
				inner JOIN v_UslugaComplexAttributeType UGAT with(nolock) on UGAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
            where
                UC.UslugaComplex_id = :UslugaComplex_id
		";

		$responce = $this->db->query($query, $params);
		if (is_object($responce)) {
			$responce = $responce->result('array');
		} else {
			return false;
		}

		$result = [
			'UslugaGost_Code' => null,
			'UslugaComplexAttributeType_id' => null,
			'UslugaComplexAttributeType_SysNick' => null,
		];
		if (count($responce)) {
			foreach ($responce as $item) {
				if ($item['UslugaGost_Code']) {
					$result['UslugaGost_Code'] = $item['UslugaGost_Code'];
				}
				if (in_array($item['UslugaComplexAttributeType_SysNick'], ['kt', 'mrt'])) {
					$result['UslugaComplexAttributeType_id'] = $item['UslugaComplexAttributeType_id'];
					$result['UslugaComplexAttributeType_SysNick'] = $item['UslugaComplexAttributeType_SysNick'];
				}
			}
		}
		return $result;
	}
}
