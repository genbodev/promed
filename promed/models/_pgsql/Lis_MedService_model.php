<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
* MedService_model - модель служб
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
*/

class Lis_MedService_model extends SwPgModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает состав услуги для настройки проб и биоматериала
	 * 08.07.2013 Сделал использование состава услуги (UslugaComplexComposition)
	 * 08.07.2013 По схеме работы с ЛИС даже простая услуга включает в себя состав (саму себя)
	 * 05.09.2013 Сделал использование состава из UslugaComplexMedService по UslugaComplexMedService_pid (refs #23929)
	 * todo: Этот момент в дальшейшем стоит уточнить
	 */
	function loadUslugaComplexMedServiceGridChild($data){
		// предварительно проверяем является ли запрашиваемая услуга простой (критерий проверки: услуга не содержит в себе других услуг)
		// и если является, то отображаем ее
		$query = "
			SELECT
				count(*) as records_count
			FROM
				v_UslugaComplexMedService s
				inner join v_UslugaComplexMedService ucm on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid 
				inner JOIN v_UslugaComplex u on u.UslugaComplex_id = ucm.UslugaComplex_id 
			WHERE
				s.MedService_id = :MedService_id
				AND s.UslugaComplex_id = :UslugaComplex_pid
		";

		$records_count = 0;
		try {
			$r = $this->db->query($query, $data);
			if (is_object($r)) {
				$records = $r->result('array');
				if (count($records)>0) {
					$records_count = $records[0]['records_count'];
				}
			}
		} catch (Exception $e) {
			// ничего не произошло :)
			$result = array(
				0 => array(
					'Error_Code' => null,
					'Error_Msg' => 'Ошибка при проверке услуги: '.str_replace(chr(13),' ', str_replace(chr(10),'<br> ', $e->getCode().' '.$e->getMessage()))
				)
			);
			return $result;
		}
		$join = "";
		$select = "";
		$filter = "";
		if ($records_count==0) { // если услуга простая, то ее и выводим
            $join .= "
			INNER join v_UslugaComplexMedService ucm on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid ";
			$join .= "
			INNER JOIN v_UslugaComplex u on u.UslugaComplex_id = s.UslugaComplex_id ";
			$join .= "
			LEFT JOIN dbo.v_RefSample r on r.RefSample_id = s.RefSample_id ";
			$select .= "s.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",";
			$select .= "coalesce(LpuUslName.UslugaComplex_Name, s.UslugaComplex_Name, u.UslugaComplex_Name) as \"UslugaComplex_Name\",";
			$filter .= " and s.UslugaComplexMedService_pid is null";
		} else { // иначе выводим состав комплексной услуги
			$join .= "
			INNER join v_UslugaComplexMedService ucm on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid ";
			$join .= "
			INNER JOIN v_UslugaComplex u on u.UslugaComplex_id = ucm.UslugaComplex_id ";
			$join .= "
			LEFT JOIN dbo.v_RefSample r on r.RefSample_id = ucm.RefSample_id ";
			$join .= "
				inner join lateral(
					select
						at_child.AnalyzerTest_id
					from 
						lis.v_AnalyzerTest at_child
						inner join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
					where
						at_child.UslugaComplexMedService_id = ucm.UslugaComplexMedService_id
						and at.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid
						and coalesce(at_child.AnalyzerTest_IsNotActive, 1) = 1
						and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1
						and coalesce(a.Analyzer_IsNotActive, 1) = 1
						and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
					limit 1	
				) ATEST on true -- фильтрация услуг по активности тестов связанных с ними
			";
			$select .= "ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",";
			$select .= "coalesce(LpuUslName.UslugaComplex_Name, ucm.UslugaComplex_Name, u.UslugaComplex_Name, s.UslugaComplex_Name) as \"UslugaComplex_Name\",";
		}

		$join .= "
			left join lateral(
				select
					UCm.UslugaComplex_Name
				from
					v_UslugaComplex UCm
					left join v_UslugaCategory UCat on UCm.UslugaCategory_id = UCat.UslugaCategory_id
				where
					UCm.UslugaComplex_2011id = u.UslugaComplex_id
					and Lpu_id = :Lpu_id
					and UCat.UslugaCategory_SysNick = 'lpu'
				limit 1
			) LpuUslName  on true";

		$query = "
		SELECT
			{$select}
			r.RefSample_Name as \"RefSample_Name\",
			r.RefSample_id as \"RefSample_id\",
			u.UslugaComplex_Code as \"UslugaComplex_Code\",
			u.UslugaComplex_id as \"UslugaComplex_id\",
			m.RefMaterial_Name as \"RefMaterial_Name\",
			m.RefMaterial_id as \"RefMaterial_id\",
			u.UslugaComplex_pid as \"UslugaComplex_pid\",
			CT.ContainerType_id as \"ContainerType_id\",
			CT.ContainerType_Name as \"ContainerType_Name\",
			case when ucm.UslugaComplexMedService_IsSeparateSample = 2 then 'true' else 'false' end as \"UslugaComplexMedService_IsSeparateSample\"
		FROM
			v_UslugaComplexMedService s
			{$join}
			LEFT JOIN dbo.v_RefMaterial m on r.RefMaterial_id = m.RefMaterial_id
			LEFT JOIN dbo.ContainerType CT on r.ContainerType_id = CT.ContainerType_id
			--left join v_UslugaComplex UC2011 on u.UslugaComplex_2011id = UC2011.UslugaComplex_id
		WHERE
			s.MedService_id = :MedService_id and
			s.UslugaComplex_id = :UslugaComplex_pid
			{$filter}
		";
		//echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
    }

	/**
	 * createMedServiceRefSample
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function createMedServiceRefSample($data){
        $RefSample_id = $this->createRefSample($data);
        if ($this->isSuccessful($RefSample_id) ) {
            $Usluga_ids = json_decode($data['Usluga_ids']);
            foreach ($Usluga_ids as $UslugaComplexMedService_id) {
                if (!$this->bindUslugaComplexMedServiceToRefSample($data, $UslugaComplexMedService_id, $RefSample_id)){
                    throw new Exception("Ошибка при попытке объединить услуги в пробу (UslugaComplexMedService_id: $UslugaComplexMedService_id, RefSample_id: $RefSample_id)");
                }
            }
			return $RefSample_id;
        } else {
        	throw new Exception($RefSample_id['Error_Msg'], 500);
		}
    }

    /**
     * @param $UslugaComplexMedService_id
     * @param $RefSample_id
     * @param $pmUser_id
     * @return bool
     */
    function bindUslugaComplexMedServiceToRefSample($data, $UslugaComplexMedService_id, $RefSample_id){
		$this->load->model('Lis_UslugaComplexMedService_model');
		$resp = $this->Lis_UslugaComplexMedService_model->doSaveUslugaComplexMedService(array(
			'scenario' => self::SCENARIO_DO_SAVE,
			'UslugaComplexMedService_id' => $UslugaComplexMedService_id,
            'UslugaComplexMedService_IsSeparateSample' => ($data['UslugaComplexMedService_IsSeparateSample']=='on'? 2: 1),
			'RefSample_id' => $RefSample_id['RefSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp['UslugaComplexMedService_id'])) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * @param $RefSample_Name
     * @param $RefMaterial_id
     * @param $pmUser_id
	 * @return array
     */
    function createRefSample($data){
        $query = "
        	select
        		refsample_id as \"RefSample_id\",
        		error_code as \"Error_Code\",
        		error_message as \"Error_Msg\"
        	from dbo.p_RefSample_ins(
        		RefMaterial_id := :RefMaterial_id,
				RefSample_Name := :RefSample_Name,
				ContainerType_id := :ContainerType_id,
				pmUser_id := :pmUser_id
        	)
        ";
        $result = $this->queryResult($query, array(
            'RefSample_Name' => $data["RefSample_Name"],
            'RefMaterial_id' => $data["RefMaterial_id"],
            'ContainerType_id' => $data["ContainerType_id"],
            'pmUser_id' => $data["pmUser_id"]
        ));
        return $result[0];
    }

	/**
	 * Получение списка служб
	 */
	function loadMedServiceGrid($data)
	{
		$filter = "";
		$queryParams = array();

		if (!empty($data['MedService_id'])) {
			$filter .= " and MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		} else if (!empty($data['MedService_sid'])) {
			$filter .= " and MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL where msl.MedService_id = :MedService_sid)";
			$queryParams['MedService_sid'] = $data['MedService_sid'];
		}

		$query = "
			select
				MedService_id as \"MedService_id\",
				MedService_Name as \"MedService_Name\"
			from
				v_MedService
			where
				1 = 1
				{$filter}
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получить права врача на данной службе (право одобрять пробы)
	 */
	function getApproveRights($data) {
		$query = "
			select
				MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
				MedService_id as \"MedService_id\",
				MedPersonal_id as \"MedPersonal_id\",
				case
					when msmp.MedServiceMedPersonal_isNotWithoutRegRights = 2
						then 1
						else 0
				end as \"MedServiceMedPersonal_isNotWithoutRegRights\",
				case
					when msmp.MedServiceMedPersonal_isNotApproveRights = 2
						then 1
						else 0
				end as \"MedServiceMedPersonal_isNotApproveRights\"
			from
				v_MedServiceMedPersonal msmp
			where
				msmp.MedService_id = :MedService_id
				and msmp.MedPersonal_id = :MedPersonal_id
			limit 1
		";
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$result = $result->result('array');
			//c postgres почему-то даже при явном cast'е возвращаются varchar, а не int'ы
			if (isset($result[0])) {
				foreach ($result[0] as $key => $value) {
					$result[0][$key] = (int) $value;
				}
			}
			return [$result];
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Читает для грида UslugaComplexMedService
	 */
	function loadUslugaComplexMedServiceGrid($data)
	{
		$filters = 'UCMS.MedService_id = :MedService_id';
		$from = 'v_UslugaComplexMedService UCMS
				left join v_UslugaComplex UC on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				inner join lateral(
					select
						at.AnalyzerTest_id
					from 
						lis.v_AnalyzerTest at
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
					where
						at.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
						and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1
						and coalesce(a.Analyzer_IsNotActive, 1) = 1
						and (at.AnalyzerTest_endDT >= (select curdate from myvars) or at.AnalyzerTest_endDT is null)
					limit 1	
				) ATEST on true -- фильтрация услуг по активности тестов связанных с ними
		';
		if (!empty($data['UslugaComplexMedService_id']) && !empty($data['UslugaComplex_pid']))
		{
			//return array();
			//$filters .= ' and UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id';
			$filters = 'UC.UslugaComplex_pid = :UslugaComplex_pid';
			$from = 'v_UslugaComplex UC
					left join v_UslugaComplexMedService UCMS on UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id';
		}

		$filters .= " AND UCMS.UslugaComplexMedService_pid IS NULL"; // услуги только верхнего уровня

		if ($data['Urgency_id']==1) {
			$filters .= ' and UCMS.UslugaComplexMedService_begDT is not null and (UCMS.UslugaComplexMedService_endDT is null or UCMS.UslugaComplexMedService_endDT > (select curdate from myvars))';
		}
		elseif ($data['Urgency_id']==2) {
			$filters .= ' and UCMS.UslugaComplexMedService_begDT is not null and UCMS.UslugaComplexMedService_endDT < (select curdate from myvars)';
		}

		$query = "
			with myvars as(
				select dbo.tzgetdate() as curdate
			)
			
			SELECT
				UCMS.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				UCMS.MedService_id as \"MedService_id\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				coalesce(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				case
					when (UCMS.UslugaComplexMedService_endDT<=(select curdate from myvars))
						then 2
						else 1
				end as \"closed\",
				to_char(UCMS.UslugaComplexMedService_begDT, 'yyyy-mm-dd') as \"UslugaComplexMedService_begDT\",
				to_char(UCMS.UslugaComplexMedService_endDT, 'yyyy-mm-dd') as \"UslugaComplexMedService_endDT\",
				case when UCMS.UslugaComplexMedService_IsSeparateSample = 2 then 'true' else 'false' end as \"UslugaComplexMedService_IsSeparateSample\",
				r.RefSample_Name as \"RefSample_Name\",
				r.RefSample_id as \"RefSample_id\",
                m.RefMaterial_Name as \"RefMaterial_Name\",
                m.RefMaterial_id as \"RefMaterial_id\",
                CT.ContainerType_id as \"ContainerType_id\",
                CT.ContainerType_Name as \"ContainerType_Name\"
			FROM 
				{$from}
				LEFT JOIN dbo.v_RefSample r on r.RefSample_id = UCMS.RefSample_id 
				LEFT JOIN dbo.v_RefMaterial m on r.RefMaterial_id = m.RefMaterial_id
				LEFT JOIN dbo.ContainerType CT on r.ContainerType_id = CT.ContainerType_id
			where
				{$filters}
			order by
				UC.UslugaComplex_Name
		";
		//echo getDebugSql($query, $data);exit;
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}

	/**
	 *  Читает для комбобокса MedService
	 */
	function loadMedServiceList($data) {
		$params = array();
		$filterList = array();
		
		$this->load->library('swCache');
		$data['mode'] = (isset($data['mode']) && $data['mode']=='all')?'all':'';

		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("MedServiceList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}

		if( !empty($data['Contragent_id']) ) {
			//$this->getContragentData($data);
		}

		if (!empty($data['LpuBuilding_id']))
		{
			$filterList[] = 'MS.LpuBuilding_id = :LpuBuilding_id';
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuUnitType_id']))
		{
			$filterList[] = 'MS.LpuUnitType_id = :LpuUnitType_id';
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		if (!empty($data['LpuUnit_id']))
		{
			$filterList[] = 'MS.LpuUnit_id = :LpuUnit_id';
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if (!empty($data['LpuSection_id']))
		{
			$filterList[] = 'MS.LpuSection_id = :LpuSection_id';
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (isset($data['MedServiceTypeIsLabOrFenceStation']) && $data['MedServiceTypeIsLabOrFenceStation'] == 1 ) {//пункты лабораторий или ограда (забор).
			$filterList[] = 'MS.MedServiceType_id IN (6, 7)';
		}

		if ( !empty($data['MedServiceType_id']) ) {
			$filterList[] = 'MS.MedServiceType_id  = :MedServiceType_id';
			$params['MedServiceType_id'] = $data['MedServiceType_id'];
		}

		if ( !empty($data['MedServiceType_SysNick']) ) {
			$filterList[] = 'MST.MedServiceType_SysNick  = :MedServiceType_SysNick';
			$params['MedServiceType_SysNick'] = $data['MedServiceType_SysNick'];
		}

		if ( !empty($data['MedService_id']) ) {
			if (!empty($data['ARMType']) && $data['ARMType'] == 'reglab') {
				// все подчинённые лаборатории
				$filterList[] = "MS.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL where msl.MedService_id = :MedService_id)";
			} else if (!empty($data['ARMType']) && $data['ARMType'] == 'pzm') {
				// все подчинённые лаборатории
				$filterList[] = "MS.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL where msl.MedService_id = :MedService_id)";
			} else {
				$filterList[] = 'MS.MedService_id = :MedService_id';
			}

			$params['MedService_id'] = $data['MedService_id'];
		}

		if ( !empty($data['UslugaComplex_prescid']) ) {
			// фильтрация по доступным услугам (по услуге из назначения)
			$filterList[] = 'exists (
				select
					uc.UslugaComplex_id
				from
					v_UslugaComplex uc
					inner join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id
					left join lateral(
						Select
							un.UslugaComplex_id,
							un.UslugaComplex_2004id,
							un.UslugaComplex_2011id,
							un.UslugaComplex_TFOMSid,
							un.UslugaComplex_llprofid,
							un.UslugaComplex_slprofid
						from v_UslugaComplex un
						where un.UslugaComplex_id = :UslugaComplex_prescid
					) ul on true
				where
					(ucms.MedService_id = MS.MedService_id) and (uc.UslugaComplexLevel_id in (7,8) or uc.UslugaComplex_pid is null)
					 and (
						uc.UslugaComplex_id = ul.UslugaComplex_id or
						uc.UslugaComplex_2004id = ul.UslugaComplex_2004id or
						uc.UslugaComplex_2011id = ul.UslugaComplex_2011id or
						uc.UslugaComplex_TFOMSid = ul.UslugaComplex_TFOMSid or
						uc.UslugaComplex_llprofid = ul.UslugaComplex_llprofid or
						uc.UslugaComplex_slprofid = ul.UslugaComplex_slprofid
					)
				limit 1
			)';
			$params['UslugaComplex_prescid'] = $data['UslugaComplex_prescid'];
		}

		if ( !empty($data['MedService_pid']) ) {
			$filterList[] = 'exists (select MedServiceLink_id from v_MedServiceLink where MedService_id = :MedService_pid and MedService_lid = MS.MedService_id)';
			$params['MedService_pid'] = $data['MedService_pid'];
		}

		if (!empty($data['is_Act']))
		{
			// Актуальные службы
			$filterList[] = 'MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)';
		}

		$query = "
			SELECT
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Nick as \"MedService_Nick\",
				MS.MedService_Name as \"MedService_Name\",
				MS.MedServiceType_id as \"MedServiceType_id\",
				MS.Org_id as \"Org_id\",
				MS.Lpu_id as \"Lpu_id\",
				MS.LpuBuilding_id as \"LpuBuilding_id\",
				MS.LpuUnitType_id as \"LpuUnitType_id\",
				MS.LpuUnit_id as \"LpuUnit_id\",
				MS.LpuSection_id as \"LpuSection_id\",
				l.Lpu_Nick as \"Lpu_Name\",
				to_char(MS.MedService_begDT, 'dd.mm.yyyy') as \"MedService_begDT\",
				to_char(MS.MedService_endDT, 'dd.mm.yyyy') as \"MedService_endDT\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				ms.MedService_IsExternal as \"MedService_IsExternal\",
				ms.MedService_IsFileIntegration as \"MedService_IsFileIntegration\",
				case
					when MS.MedService_endDT is null or MS.MedService_endDT > dbo.tzGetDate()
						then 0
						else 1
				end as \"MedService_IsClosed\"
			FROM
				v_MedService MS
				inner join v_Lpu l on l.Lpu_id = MS.Lpu_id
				left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
				left join v_LpuUnit lu on ms.LpuUnit_id = lu.LpuUnit_id
				left join v_LpuUnitType lut on coalesce(ms.LpuUnitType_id,lu.LpuUnitType_id) = lut.LpuUnitType_id
				left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
			" . (count($filterList) > 0 ? 'where ' . implode(' and ', $filterList) : '') . "
			order by
				MS.MedService_Name
		";

		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$response = $result->result('array');
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
				$this->swcache->set("MedServiceList_".$data['Lpu_id'], $response);
			}
			return $response;
		}
		return false;
	}

    /**
     * saveUslugaComplexMedServiceIsSeparateSample
     * @param $data
     * @return array
     * @throws Exception
     */
    function saveUslugaComplexMedServiceIsSeparateSample($data){
        $this->load->model('Lis_UslugaComplexMedService_model');
        $resp = $this->Lis_UslugaComplexMedService_model->doSaveUslugaComplexMedService(array(
            'scenario' => SwModel::SCENARIO_DO_SAVE,
            'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
            'UslugaComplex_id' => $data['UslugaComplex_id'],
            'UslugaComplexMedService_IsSeparateSample' => ( $data['UslugaComplexMedService_IsSeparateSample'] == "true" ? 2 : 1 ),
            'session' => $data['session'],
            'pmUser_id' => $data['pmUser_id']
        ));

        if (!empty($resp['UslugaComplexMedService_id'])) {
            return $resp;
        } else {
            return false;
        }

    }
}
