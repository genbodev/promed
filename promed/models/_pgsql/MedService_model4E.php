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

class MedService_model4E extends swPgModel {
    /**
     *  Читает список аппаратов
     */
    //	function loadApparatusList($data) {
    //
    //		$query = "
    //			SELECT
    //				MS.MedService_id
    //				,MS.MedService_Name
    //				,convert(varchar(10),MS.MedService_begDT,104) as MedService_begDT
    //				,convert(varchar(10),MS.MedService_endDT,104) as MedService_endDT
    //			FROM
    //				v_MedService MS with (NOLOCK)
    //			where
    //				MS.MedService_pid = :MedService_pid
    //			order by
    //				MS.MedService_Name
    //		";
    //
    //		// echo getDebugSql($query, $data); die();
    //
    //		$result = $this->db->query($query, $data);
    //		if (is_object($result))
    //		{
    //			return $result->result('array');
    //		}
    //		return false;
    //	}
    //
    //	/**
    //	 *  Читает для комбобокса MedService
    //	 */
    //	function loadMedServiceList($data)
    //	{
    //		$params = array();
    //		$filters = '1=1';
    //
    //		// Тоесть если хотим получить службы только для текущего ЛПУ ( по умолчанию $data['Lpu_isAll'] = 0 )
    //		if( !$data['Lpu_isAll'] ) {
    //			$filters .= ' and MS.Lpu_id = :Lpu_id';
    //			$params['Lpu_id'] = ( empty($data['Lpu_id']) ) ? $data['session']['lpu_id'] : $data['Lpu_id'];
    //		}
    //
    //		if( !empty($data['Contragent_id']) ) {
    //			$this->getContragentData($data);
    //		}
    //
    //		if (!empty($data['LpuBuilding_id']))
    //		{
    //			$filters .= ' and MS.LpuBuilding_id = :LpuBuilding_id';
    //			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
    //		}
    //		if (!empty($data['LpuUnitType_id']))
    //		{
    //			$filters .= ' and MS.LpuUnitType_id = :LpuUnitType_id';
    //			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
    //		}
    //		if (!empty($data['LpuUnit_id']))
    //		{
    //			$filters .= ' and MS.LpuUnit_id = :LpuUnit_id';
    //			$params['LpuUnit_id'] = $data['LpuUnit_id'];
    //		}
    //
    //		if (!empty($data['LpuSection_id']))
    //		{
    //			$filters .= ' and MS.LpuSection_id = :LpuSection_id';
    //			$params['LpuSection_id'] = $data['LpuSection_id'];
    //		}
    //
    //		if ( $data['MedServiceTypeIsLabOrFenceStation'] == 1 ) {//пункты лабораторий или ограда (забор).
    //			$filters .= ' and MS.MedServiceType_id IN (6, 7)';
    //		}
    //
    //		if ( !empty($data['MedServiceType_id']) ) {
    //			$filters .= ' and MS.MedServiceType_id  = :MedServiceType_id';
    //			$params['MedServiceType_id'] = $data['MedServiceType_id'];
    //		}
    //
    //		if ( !empty($data['MedService_id']) ) {
    //			if (!empty($data['ARMType']) && $data['ARMType'] == 'reglab') {
    //				// все подчинённые лаборатории
    //				$filters .= " and MS.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL with (nolock) where msl.MedService_id = :MedService_id)";
    //			} else if (!empty($data['ARMType']) && $data['ARMType'] == 'pzm') {
    //				// все подчинённые лаборатории
    //				$filters .= " and MS.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL with (nolock) where msl.MedService_id = :MedService_id)";
    //			} else {
    //				$filters .= ' and MS.MedService_id = :MedService_id';
    //			}
    //
    //			$params['MedService_id'] = $data['MedService_id'];
    //		}
    //
    //		if ( !empty($data['UslugaComplex_prescid']) ) {
    //			// фильтрация по доступным услугам (по услуге из назначения)
    //			$filters .= ' and exists (
    //				select top 1
    //					uc.UslugaComplex_id
    //				from
    //					v_UslugaComplex uc with (NOLOCK)
    //					inner join v_UslugaComplexMedService ucms with (NOLOCK) on ucms.UslugaComplex_id = uc.UslugaComplex_id
    //					outer apply (
    //						Select
    //							un.UslugaComplex_id,
    //							un.UslugaComplex_2004id,
    //							un.UslugaComplex_2011id,
    //							un.UslugaComplex_TFOMSid,
    //							un.UslugaComplex_llprofid,
    //							un.UslugaComplex_slprofid
    //						from v_UslugaComplex un with (NOLOCK)
    //						where un.UslugaComplex_id = :UslugaComplex_prescid
    //					) as ul
    //				where
    //					(ucms.MedService_id = MS.MedService_id) and (uc.UslugaComplexLevel_id in (7,8) or uc.UslugaComplex_pid is null)
    //					 and (
    //						uc.UslugaComplex_id = ul.UslugaComplex_id or
    //						uc.UslugaComplex_2004id = ul.UslugaComplex_2004id or
    //						uc.UslugaComplex_2011id = ul.UslugaComplex_2011id or
    //						uc.UslugaComplex_TFOMSid = ul.UslugaComplex_TFOMSid or
    //						uc.UslugaComplex_llprofid = ul.UslugaComplex_llprofid or
    //						uc.UslugaComplex_slprofid = ul.UslugaComplex_slprofid
    //					)
    //			)';
    //			$params['UslugaComplex_prescid'] = $data['UslugaComplex_prescid'];
    //		}
    //
    //		if ( !empty($data['MedService_pid']) ) {
    //			$filters .= ' and exists (select MedServiceLink_id from v_MedServiceLink with (nolock) where MedService_id = :MedService_pid and MedService_lid = MS.MedService_id)';
    //			$params['MedService_pid'] = $data['MedService_pid'];
    //		}
    //
    //		if (!empty($data['is_Act']))
    //		{
    //			// Актуальные службы
    //			$filters .= ' and MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)';
    //		}
    //
    //		$query = "
    //			SELECT
    //				MS.MedService_id
    //				,MS.MedService_Nick
    //				,MS.MedService_Name
    //				,MS.MedServiceType_id
    //				,MS.Lpu_id
    //				,MS.LpuBuilding_id
    //				,MS.LpuUnitType_id
    //				,MS.LpuUnit_id
    //				,MS.LpuSection_id
    //				,convert(varchar(10),MS.MedService_begDT,104) as MedService_begDT
    //				,convert(varchar(10),MS.MedService_endDT,104) as MedService_endDT
    //				,mst.MedServiceType_SysNick
    //				,lut.LpuUnitType_SysNick
    //				,ls.LpuSectionProfile_id
    //				,ls.LpuSection_Name
    //			FROM
    //				v_MedService MS with (NOLOCK)
    //				left join v_MedServiceType mst with (nolock) on ms.MedServiceType_id = mst.MedServiceType_id
    //				left join v_LpuUnit lu with (nolock) on ms.LpuUnit_id = lu.LpuUnit_id
    //				left join v_LpuUnitType lut with (nolock) on isnull(ms.LpuUnitType_id,lu.LpuUnitType_id) = lut.LpuUnitType_id
    //				left join v_LpuSection ls with (nolock) on ms.LpuSection_id = ls.LpuSection_id
    //			where
    //				{$filters}
    //			order by
    //				MS.MedService_Name
    //		";
    //
    //		// echo getDebugSql($query, $data); die();
    //
    //		$result = $this->db->query($query, $data);
    //		if (is_object($result))
    //		{
    //			return $result->result('array');
    //		}
    //		return false;
    //	}
    //
    //	/**
    //	 *	Определяет параметры контрагента
    //	 *	Ничего не возвращает, так как этого не требуется (принимает параметры по ссылке и просто дополняет массив)
    //	 */
    //	function getContragentData(&$data) {
    //		$query = "
    //			select top 1
    //				Lpu_id
    //				,Org_id
    //				,LpuSection_id
    //			from
    //				v_Contragent with(nolock)
    //			where
    //				Contragent_id = :Contragent_id
    //		";
    //		$result = $this->db->query($query, array(
    //			'Contragent_id' => $data['Contragent_id']
    //		));
    //		if ( is_object($result) ) {
    //			$result = $result->result('array');
    //			if( isset($result[0]) ) {
    //				foreach( $result[0] as $k=>$row ) {
    //					if( !empty($row) ) {
    //						$data[$k] = $row;
    //					}
    //				}
    //			}
    //		}
    //	}
    //	/**
    //	 * Возвращает состав услуги для настройки проб и биоматериала
    //	 * 08.07.2013 Сделал использование состава услуги (UslugaComplexComposition)
    //	 * 08.07.2013 По схеме работы с ЛИС даже простая услуга включает в себя состав (саму себя)
    //	 * 05.09.2013 Сделал использование состава из UslugaComplexMedService по UslugaComplexMedService_pid (refs #23929)
    //	 * todo: Этот момент в дальшейшем стоит уточнить
    //	 */
    //	function loadUslugaComplexMedServiceGridChild($data){
    //		// предварительно проверяем является ли запрашиваемая услуга простой (критерий проверки: услуга не содержит в себе других услуг)
    //		// и если является, то отображаем ее
    //		$query = "
    //			SELECT
    //				count(*) as records_count
    //			FROM
    //				v_UslugaComplexMedService s (nolock)
    //				INNER join v_UslugaComplexMedService ucm with (NOLOCK) on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid
    //				INNER JOIN v_UslugaComplex u (nolock) on u.UslugaComplex_id = ucm.UslugaComplex_id
    //			WHERE
    //				s.MedService_id = :MedService_id AND s.UslugaComplex_id = :UslugaComplex_pid
    //		";
    //		//echo getDebugSql($query, $data);die();
    //		$records_count = 0;
    //		try {
    //			$r = $this->db->query($query, $data);
    //			if (is_object($r)) {
    //				$records = $r->result('array');
    //				if (count($records)>0) {
    //					$records_count = $records[0]['records_count'];
    //				}
    //			}
    //		} catch (Exception $e) {
    //			// ничего не произошло :)
    //			$result = array(
    //				0 => array(
    //					'Error_Code' => null,
    //					'Error_Msg' => 'Ошибка при проверке услуги: '.str_replace(chr(13),' ', str_replace(chr(10),'<br> ', $e->getCode().' '.$e->getMessage()))
    //				)
    //			);
    //			return $result;
    //		}
    //		$join = "";
    //		$select = "";
    //		if ($records_count==0) { // если услуга простая, то ее и выводим
    //			$join .= "INNER JOIN v_UslugaComplex u (nolock) on u.UslugaComplex_id = s.UslugaComplex_id ";
    //			$join .= "LEFT JOIN dbo.v_RefSample r (nolock) on r.RefSample_id = s.RefSample_id ";
    //			$select .= "s.UslugaComplexMedService_id,";
    //		} else { // иначе выводим состав комплексной услуги
    //			$join .= "INNER join v_UslugaComplexMedService ucm with (NOLOCK) on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid ";
    //			$join .= "INNER JOIN v_UslugaComplex u (nolock) on u.UslugaComplex_id = ucm.UslugaComplex_id ";
    //			$join .= "LEFT JOIN dbo.v_RefSample r (nolock) on r.RefSample_id = ucm.RefSample_id ";
    //			$select .= "ucm.UslugaComplexMedService_id,";
    //		}
    //		$query = "
    //		SELECT
    //			u.UslugaComplex_Name,
    //			{$select}
    //			r.RefSample_Name,
    //			r.RefSample_id,
    //			IsNull(UC2011.UslugaComplex_Code, ''+u.UslugaComplex_Code+'') as UslugaComplex_Code,
    //			u.UslugaComplex_id,
    //			m.RefMaterial_Name,
    //			u.UslugaComplex_pid
    //		FROM
    //			v_UslugaComplexMedService s (nolock)
    //			{$join}
    //			LEFT JOIN dbo.v_RefMaterial m (nolock) on r.RefMaterial_id = m.RefMaterial_id
    //			left join v_UslugaComplex UC2011 with (NOLOCK) on u.UslugaComplex_2011id = UC2011.UslugaComplex_id
    //		WHERE
    //			s.MedService_id = :MedService_id and
    //			s.UslugaComplex_id = :UslugaComplex_pid";
    //		//echo getDebugSql($query, $data);die();
    //		$result = $this->db->query($query, $data);
    //		if (is_object($result))
    //		{
    //			return $result->result('array');
    //		}
    //		return false;
    //    }
    //
    //    /**
    //	 *  Читает для грида UslugaComplexMedService
    //	 */
    //	function loadUslugaComplexMedServiceGrid($data)
    //	{
    //		$filters = 'UCMS.MedService_id = :MedService_id and UC.UslugaComplex_pid is null';
    //		$from = 'v_UslugaComplexMedService UCMS with (NOLOCK)
    //				left join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id';
    //		if (!empty($data['UslugaComplexMedService_id']) && !empty($data['UslugaComplex_pid']))
    //		{
    //			//return array();
    //			//$filters .= ' and UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id';
    //			$filters = 'UC.UslugaComplex_pid = :UslugaComplex_pid';
    //			$from = 'v_UslugaComplex UC with (NOLOCK)
    //					left join v_UslugaComplexMedService UCMS with (NOLOCK) on UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id';
    //		}
    //
    //		$filters .= " AND UCMS.UslugaComplexMedService_pid IS NULL"; // услуги только верхнего уровня
    //
    //		if ($data['Urgency_id']==1) {
    //			$filters .= ' and UCMS.UslugaComplexMedService_begDT is not null and (UCMS.UslugaComplexMedService_endDT is null or UCMS.UslugaComplexMedService_endDT > dbo.tzGetDate())';
    //		}
    //		elseif ($data['Urgency_id']==2) {
    //			$filters .= ' and UCMS.UslugaComplexMedService_begDT is not null and UCMS.UslugaComplexMedService_endDT < dbo.tzGetDate()';
    //		}
    //
    //		$query = "
    //			SELECT
    //				UCMS.UslugaComplexMedService_id,
    //				UCMS.MedService_id,
    //				IsNull(UC2011.UslugaComplex_Code, ''+UC.UslugaComplex_Code+'') as UslugaComplex_Code,
    //				UC.UslugaComplex_id,
    //				UC.UslugaComplex_Name,
    //				case when (UCMS.UslugaComplexMedService_endDT<=getdate()) then 2 else 1 end as closed,
    //				convert(varchar(10),UCMS.UslugaComplexMedService_begDT,104) as UslugaComplexMedService_begDT,
    //				convert(varchar(10),UCMS.UslugaComplexMedService_endDT,104) as UslugaComplexMedService_endDT
    //			FROM
    //				{$from}
    //				left join v_UslugaComplex UC2011 with (NOLOCK) on UC.UslugaComplex_2011id = UC2011.UslugaComplex_id
    //			where
    //				{$filters}
    //			order by
    //				UC.UslugaComplex_Name
    //		";
    //		//echo getDebugSql($query, $data);exit;
    //		$result = $this->db->query($query, $data);
    //		if (is_object($result))
    //		{
    //			return $result->result('array');
    //		}
    //		return false;
    //	}
    //
    //	/**
    //	 * createMedServiceRefSample
    //	 * @param $data
    //	 * @return array
    //	 * @throws Exception
    //	 */
    //	function createMedServiceRefSample($data){
    //        $this->db->trans_begin();
    //        try {
    //            $RefSample_id = $this->createRefSample($data['RefSample_Name'], $data['RefMaterial_id'], $data['pmUser_id']);
    //            if ($RefSample_id ) {
    //                $Usluga_ids = json_decode($data['Usluga_ids']);
    //                foreach ($Usluga_ids as $UslugaComplexMedService_id) {
    //                    if (!$this->bindUslugaComplexMedServiceToRefSample($UslugaComplexMedService_id, $RefSample_id, $data['pmUser_id'])){
    //                        throw new Exception("Ошибка при попытке объединить услуги в пробу (UslugaComplexMedService_id: $UslugaComplexMedService_id, RefSample_id: $RefSample_id)");
    //                    }
    //                }
    //            }
    //        } catch (Exception $e) {
    //            $this->db->trans_rollback();
    //            throw $e;
    //        }
    //        $this->db->trans_commit();
    //        return array(array('RefSample_id' => $RefSample_id, 'Error_Msg' => null));
    //    }
    //
    //    /**
    //     * @param $UslugaComplexMedService_id
    //     * @param $RefSample_id
    //     * @param $pmUser_id
    //     * @return bool
    //     */
    //    function bindUslugaComplexMedServiceToRefSample($UslugaComplexMedService_id, $RefSample_id, $pmUser_id){
    //        $query = '
    //            --привязываем услугу к пробе
    //            declare
    //                @MedService_id                 bigint   ,
    //                @UslugaComplex_id              bigint   ,
    //                @UslugaComplexMedService_begDT datetime ,
    //                @UslugaComplexMedService_endDT datetime ,
    //                @UslugaComplexMedService_id  bigint,
    //                @UslugaComplexMedService_pid  bigint,
    //                @Error_Code    int,
    //                @Error_Message varchar(4000);
    //            --выбираем все поля услуги, не требующие изменения во временные переменные
    //            SELECT
    //                @MedService_id                 = MedService_id                ,
    //                @UslugaComplex_id              = UslugaComplex_id             ,
    //                @UslugaComplexMedService_begDT = UslugaComplexMedService_begDT,
    //                @UslugaComplexMedService_endDT = UslugaComplexMedService_endDT,
    //				@UslugaComplexMedService_pid = UslugaComplexMedService_pid
    //            FROM
    //                v_UslugaComplexMedService
    //            WHERE
    //                UslugaComplexMedService_id = :UslugaComplexMedService_id;
    //            -- обновляем ссылку на пробу, оставляя другие поля без изменений
    //            EXEC dbo.p_UslugaComplexMedService_upd
    //                @UslugaComplexMedService_id   = :UslugaComplexMedService_id, -- bigint
    //                @MedService_id                 = @MedService_id                ,-- bigint
    //                @UslugaComplex_id              = @UslugaComplex_id             ,-- bigint
    //                @UslugaComplexMedService_begDT = @UslugaComplexMedService_begDT,-- datetime
    //                @UslugaComplexMedService_endDT = @UslugaComplexMedService_endDT,-- datetime
    //				@UslugaComplexMedService_pid = @UslugaComplexMedService_pid,
    //                @RefSample_id                  = :RefSample_id ,-- bigint
    //                @pmUser_id      = :pmUser_id      , -- bigint
    //                @Error_Code     = @Error_Code     , -- int
    //                @Error_Message  = @Error_Message    -- varchar(4000)
    //            SELECT @UslugaComplexMedService_id as UslugaComplexMedService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //        ';
    //        $result = $this->db->query($query, array(
    //            'UslugaComplexMedService_id' => $UslugaComplexMedService_id,
    //            'RefSample_id' => $RefSample_id,
    //            'pmUser_id' => $pmUser_id
    //        ));
    //        if (is_object($result)){
    //            $res = $result->result('array');
    //            if ($res[0]['Error_Msg']) {
    //                return false;
    //            } else {
    //                return true;
    //            }
    //        }
    //        return false;
    //    }
    //
    //    /**
    //     * @param $RefSample_Name
    //     * @param $RefMaterial_id
    //     * @param $pmUser_id
    //     * @return bool
    //     */
    //    function createRefSample($RefSample_Name, $RefMaterial_id, $pmUser_id){
    //        $query = '
    //            declare
    //                @RefSample_id  bigint,
    //                @Error_Code    int,
    //                @Error_Message varchar(4000);
    //            EXEC dbo.p_RefSample_ins
    //                @RefSample_id   = @RefSample_id   output, -- bigint
    //                @RefMaterial_id = :RefMaterial_id , -- bigint
    //                @RefSample_Name = :RefSample_Name , -- varchar(50)
    //                @pmUser_id      = :pmUser_id      , -- bigint
    //                @Error_Code     = @Error_Code     , -- int
    //                @Error_Message  = @Error_Message    -- varchar(4000)
    //            SELECT @RefSample_id as RefSample_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //        ';
    //        $result = $this->db->query($query, array(
    //            'RefSample_Name' => $RefSample_Name,
    //            'RefMaterial_id' => $RefMaterial_id,
    //            'pmUser_id' => $pmUser_id
    //        ));
    //        if (is_object($result))
    //        {
    //            $res = $result->result('array');
    //            return $res[0]['RefSample_id'];
    //        }
    //        return false;
    //    }
    //
    //	/**
    //	*  Читает для грида MedServiceMedPersonal
    //	*/
    //	function loadMedServiceMedPersonalGrid($data)
    //	{
    //		$query = "
    //			SELECT
    //				MSMP.MedServiceMedPersonal_id
    //				,MSMP.MedService_id
    //				,MSMP.MedPersonal_id
    //				,MSMP.Server_id
    //				,MP.Person_Fio as MedPersonal_Name
    //				,convert(varchar(10),MSMP.MedServiceMedPersonal_begDT,104) as MedServiceMedPersonal_begDT
    //				,convert(varchar(10),MSMP.MedServiceMedPersonal_endDT,104) as MedServiceMedPersonal_endDT
    //			FROM
    //				v_MedServiceMedPersonal MSMP with (NOLOCK)
    //				left join v_MedPersonal MP with (NOLOCK) on MSMP.MedPersonal_id = MP.MedPersonal_id
    //			where
    //				MSMP.MedService_id = :MedService_id
    //			order by
    //				MedPersonal_Name
    //		";
    //
    //		$result = $this->db->query($query, $data);
    //		if (is_object($result))
    //		{
    //			return $result->result('array');
    //		}
    //		return false;
    //	}
    //
    //	/**
    //	 * проверяет есть ли служба указанного типа в ЛПУ
    //	 */
    //	function checkMedServiceExistInLpu($data) {
    //		if(empty($data['MedServiceType_id'])) { $data['MedServiceType_id'] = NULL; }
    //		if(empty($data['Lpu_id'])) { $data['Lpu_id'] = NULL; }
    //
    //		$query = "
    //			SELECT
    //				TOP 1 MS.MedService_id
    //			FROM
    //				v_MedService MS with (NOLOCK)
    //				left join v_MedServiceType MST with (NOLOCK) on MS.MedServiceType_id = MST.MedServiceType_id
    //			where
    //				MS.Lpu_id = :Lpu_id and
    //				MST.MedServiceType_id = :MedServiceType_id
    //			order by
    //				MedService_Name
    //		";
    //
    //		$result = $this->db->query($query, $data);
    //		if (is_object($result))
    //		{
    //			$response = $result->result('array');
    //			if (count($response) > 0) {
    //				return true;
    //			}
    //		}
    //		return false;
    //	}
    //
    //	/**
    //	 *  Читает для грида MedService
    //	 */
    //	function loadGrid($data)
    //	{
    //		$params = array(
    //			'Lpu_id' => $data['Lpu_id']
    //		);
    //		$level='lpu';
    //		$filters = '';
    //		if (!empty($data['LpuBuilding_id']))
    //		{
    //			$level='lpubuilding';
    //			$filters .= ' and MS.LpuBuilding_id = :LpuBuilding_id';
    //			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
    //		}
    //		if (!empty($data['LpuUnitType_id']))
    //		{
    //			$level='lpuunittype';
    //			$filters .= ' and MS.LpuUnitType_id = :LpuUnitType_id';
    //			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
    //		}
    //		if (!empty($data['LpuUnit_id']))
    //		{
    //			$level='lpuunit';
    //			$filters .= ' and MS.LpuUnit_id = :LpuUnit_id';
    //			$params['LpuUnit_id'] = $data['LpuUnit_id'];
    //		}
    //		if (!empty($data['LpuSection_id']))
    //		{
    //			$level='lpusection';
    //			$filters .= ' and MS.LpuSection_id = :LpuSection_id';
    //			$params['LpuSection_id'] = $data['LpuSection_id'];
    //		}
    //		if (empty($data['is_All']))
    //		{
    //			// только Службы Выбранного уровня
    //			switch($level){
    //				case 'lpu';
    //					$filters = ' and MS.LpuBuilding_id is null and ms.LpuUnitType_id is null and MS.LpuUnit_id is null and MS.LpuSection_id is null';
    //					break;
    //				case 'lpubuilding';
    //					$filters = ' and MS.LpuBuilding_id = :LpuBuilding_id and ms.LpuUnitType_id is null and MS.LpuUnit_id is null and MS.LpuSection_id is null';
    //					break;
    //				case 'lpuunittype';
    //					$filters = ' and MS.LpuBuilding_id = :LpuBuilding_id and MS.LpuUnitType_id = :LpuUnitType_id and MS.LpuUnit_id is null and MS.LpuSection_id is null';
    //					break;
    //				case 'lpuunit';
    //					$filters = ' and MS.LpuUnit_id = :LpuUnit_id and MS.LpuSection_id is null';
    //					break;
    //				case 'lpusection';
    //					$filters = ' and MS.LpuSection_id = :LpuSection_id';
    //					break;
    //			}
    //		}
    //
    //		if (!empty($data['is_Act']))
    //		{
    //			// Актуальные службы
    //			$filters .= ' and MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)';
    //		}
    //
    //		$query = "
    //			SELECT
    //				MS.MedService_id
    //				,MS.MedService_Name
    //				,MST.MedServiceType_Name
    //				,MS.Lpu_id
    //				,MS.LpuBuilding_id
    //				,MS.LpuUnitType_id
    //				,MS.LpuUnit_id
    //				,MS.LpuSection_id
    //				,convert(varchar(10),MS.MedService_begDT,104) as MedService_begDT
    //				,convert(varchar(10),MS.MedService_endDT,104) as MedService_endDT
    //			FROM
    //				v_MedService MS with (NOLOCK)
    //				left join v_MedServiceType MST with (NOLOCK) on MS.MedServiceType_id = MST.MedServiceType_id
    //			where
    //				MS.Lpu_id = :Lpu_id
    //				{$filters}
    //			order by
    //				MedService_Name
    //		";
    //		/*
    //		echo getDebugSql($query, $params);
    //		exit;
    //		*/
    //		$result = $this->db->query($query, $params);
    //		if (is_object($result))
    //		{
    //			return $result->result('array');
    //		}
    //		return false;
    //	}
    //
    //	/**
    //	 *  Читает одну строку для формы редактирования MedService
    //	 */
    //	function loadEditForm($data)
    //	{
    //		$query = "
    //			Select top 1
    //				ms.MedService_id,
    //				ms.MedService_Name,
    //				ms.MedService_Nick,
    //				ms.MedServiceType_id,
    //				convert(varchar(10),ms.MedService_begDT,104) as MedService_begDT,
    //				convert(varchar(10),ms.MedService_endDT,104) as MedService_endDT,
    //				ms.LpuBuilding_id,
    //				ms.LpuSection_id,
    //				ms.LpuUnitType_id,
    //				ms.LpuUnit_id,
    //				ms.Lpu_id,
    //				ms.Org_id,
    //				ms.OrgStruct_id
    //			from
    //				v_MedService ms with (NOLOCK)
    //			where
    //				ms.MedService_id = :MedService_id
    //		";
    //		$result = $this->db->query($query, $data);
    //		if ( is_object($result) )
    //		{
    //			return $result->result('array');
    //		}
    //		else
    //		{
    //			return false;
    //		}
    //	}
    //
    //	/**
    //	 *  Читает одну строку для формы редактирования аппарата
    //	 */
    //	function loadApparatusEditForm($data)
    //	{
    //		$query = "
    //			Select top 1
    //				ms.MedService_id,
    //				ms.MedService_pid,
    //				ms.MedService_Name,
    //				ms.MedService_Nick,
    //				ms.MedServiceType_id,
    //				convert(varchar(10),ms.MedService_begDT,104) as MedService_begDT,
    //				convert(varchar(10),ms.MedService_endDT,104) as MedService_endDT
    //			from
    //				v_MedService ms with (NOLOCK)
    //			where
    //				ms.MedService_id = :MedService_id
    //		";
    //		$result = $this->db->query($query, $data);
    //		if ( is_object($result) )
    //		{
    //			return $result->result('array');
    //		}
    //		else
    //		{
    //			return false;
    //		}
    //	}
    //
    //	/**
    //	 *  Записывает одну строку MedService
    //	 */
    //	function saveRecord($data)
    //	{
    //		if ($data['MedService_id'] > 0)
    //		{
    //			$proc = 'p_MedService_upd';
    //			$data['copyFromLpuSection'] = 0;
    //		}
    //		else
    //		{
    //			$proc = 'p_MedService_ins';
    //			$data['MedService_id'] = null;
    //		}
    //
    //		$params = array
    //		(
    //			'MedService_id' => $data['MedService_id'],
    //			'MedService_Name' => $data['MedService_Name'],
    //			'MedService_Nick' => $data['MedService_Nick'],
    //			'MedServiceType_id' => $data['MedServiceType_id'],
    //			'MedService_begDT' => $data['MedService_begDT'],
    //			'MedService_endDT' => $data['MedService_endDT'],
    //			'LpuBuilding_id' => $data['LpuBuilding_id'],
    //			'LpuUnitType_id' => $data['LpuUnitType_id'],
    //			'LpuSection_id' => $data['LpuSection_id'],
    //			'LpuUnit_id' => $data['LpuUnit_id'],
    //			'Lpu_id' => $data['Lpu_id'],
    //			'Org_id' => $data['Org_id'],
    //			'OrgStruct_id' => $data['OrgStruct_id'],
    //			'Server_id'=>$data['Server_id'],
    //			'pmUser_id' => $data['pmUser_id']
    //		);
    //		$query = '
    //			declare
    //				@Error_Code bigint,
    //				@Error_Message varchar(4000),
    //				@MedService_id bigint = :MedService_id;
    //
    //			exec ' .$proc. '
    //				@MedService_id = @MedService_id output,
    //				@MedService_Name = :MedService_Name,
    //				@MedService_Nick = :MedService_Nick,
    //				@MedServiceType_id = :MedServiceType_id,
    //				@MedService_begDT = :MedService_begDT,
    //				@MedService_endDT = :MedService_endDT,
    //				@LpuBuilding_id = :LpuBuilding_id,
    //				@LpuUnitType_id = :LpuUnitType_id,
    //				@LpuSection_id = :LpuSection_id,
    //				@LpuUnit_id = :LpuUnit_id,
    //				@Lpu_id = :Lpu_id,
    //				@Org_id = :Org_id,
    //				@OrgStruct_id = :OrgStruct_id,
    //				@pmUser_id = :pmUser_id,
    //				@Server_id = :Server_id,
    //				@Error_Code = @Error_Code output,
    //				@Error_Message = @Error_Message output;
    //				select @MedService_id as MedService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //		';
    //
    //		//echo getDebugSQL($query, $params);exit;
    //		$result = $this->db->query($query, $params);
    //
    //		if ( is_object($result) ) {
    //			$response = $result->result('array');
    //			if(empty($response[0]['Error_Msg']) && !empty($response[0]['MedService_id']) && !empty($data['copyFromLpuSection']) && !empty($data['LpuSection_id']))
    //			{
    //				$data['MedService_id'] = $response[0]['MedService_id'];
    //				$response[0]['Alert_Msg'] = $this->copyDataFromLpuSection($data);
    //			}
    //			return $response;
    //		}
    //		else
    //		{
    //			return false;
    //		}
    //
    //	}
    //
    //	/**
    //	 *  Сохраняет аппарат
    //	 */
    //	function saveApparatus($data)
    //	{
    //		if ($data['MedService_id'] > 0)
    //		{
    //			$proc = 'p_MedService_upd';
    //			$data['copyFromLpuSection'] = 0;
    //		}
    //		else
    //		{
    //			$proc = 'p_MedService_ins';
    //			$data['MedService_id'] = null;
    //		}
    //
    //		$params = array
    //		(
    //			'MedService_id' => $data['MedService_id'],
    //			'MedService_pid' => $data['MedService_pid'],
    //			'MedService_Name' => $data['MedService_Name'],
    //			'MedService_Nick' => $data['MedService_Nick'],
    //			'MedService_begDT' => $data['MedService_begDT'],
    //			'MedService_endDT' => $data['MedService_endDT'],
    //			'Server_id' => $data['Server_id'],
    //			'pmUser_id' => $data['pmUser_id']
    //		);
    //		$query = "
    //			declare
    //				@Error_Code bigint,
    //				@MedServiceType_id bigint,
    //				@Error_Message varchar(4000),
    //				@MedService_id bigint = :MedService_id;
    //
    //			set @MedServiceType_id = (select top 1 MedServiceType_id from v_MedServiceType (nolock) where MedServiceType_SysNick = 'app');
    //
    //			exec {$proc}
    //				@MedService_id = @MedService_id output,
    //				@MedService_pid = :MedService_pid,
    //				@MedService_Name = :MedService_Name,
    //				@MedService_Nick = :MedService_Nick,
    //				@MedServiceType_id = @MedServiceType_id,
    //				@MedService_begDT = :MedService_begDT,
    //				@MedService_endDT = :MedService_endDT,
    //				@LpuBuilding_id = null,
    //				@LpuUnitType_id = null,
    //				@LpuSection_id = null,
    //				@LpuUnit_id = null,
    //				@Lpu_id = null,
    //				@Org_id = null,
    //				@OrgStruct_id = null,
    //				@pmUser_id = :pmUser_id,
    //				@Server_id = :Server_id,
    //				@Error_Code = @Error_Code output,
    //				@Error_Message = @Error_Message output;
    //				select @MedService_id as MedService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //		";
    //
    //		//echo getDebugSQL($query, $params);exit;
    //		$result = $this->db->query($query, $params);
    //
    //		if ( is_object($result) ) {
    //			$response = $result->result('array');
    //			if(empty($response[0]['Error_Msg']) && !empty($response[0]['MedService_id']) && !empty($data['copyFromLpuSection']) && !empty($data['LpuSection_id']))
    //			{
    //				$data['MedService_id'] = $response[0]['MedService_id'];
    //				$response[0]['Alert_Msg'] = $this->copyDataFromLpuSection($data);
    //			}
    //			return $response;
    //		}
    //		else
    //		{
    //			return false;
    //		}
    //
    //	}
    //
    //	/**
    //	 * Копирование списков услуг и сотрудников из данных отделения
    //	 */
    //	function copyDataFromLpuSection($data)
    //	{
    //		$error = null;
    //		// Копирование услуг
    //		$query = '
    //			select
    //				ucp.UslugaComplex_id,
    //				cast(ucp.UslugaComplexPlace_begDT as date) as UslugaComplex_begDT,
    //				cast(ucp.UslugaComplexPlace_endDT as date) as UslugaComplex_endDT
    //			from
    //				v_UslugaComplexPlace ucp with (NOLOCK)
    //			where
    //				(ucp.Lpu_id = :Lpu_id or ucp.Lpu_id is null)
    //				and ucp.LpuSection_id = :LpuSection_id
    //				and ucp.UslugaComplexPlace_begDT is not null -- сделал чтобы копировались только услуги с датой начала
    //		';
    //		$result = $this->db->query($query, $data);
    //		if ( is_object($result) )
    //		{
    //			$response = $result->result('array');
    //			foreach($response as $row) {
    //				$row['UslugaComplexMedService_id'] = 0;
    //				$row['MedService_id'] = $data['MedService_id'];
    //				$row['pmUser_id'] = $data['pmUser_id'];
    //				$res = $this->saveUslugaComplexMedService($row);
    //				if(empty($res))
    //				{
    //					$error = 'Ошибка запроса БД при копировании услуг отделения';
    //					break;
    //				}
    //				if(!empty($res[0]['Error_Msg']))
    //				{
    //					$error = $res[0]['Error_Msg'];
    //					break;
    //				}
    //			}
    //		}
    //		if(!empty($error))
    //		{
    //			return $error;
    //		}
    //		// Копирование сотрудников
    //		$query = '
    //			select
    //				msf.MedPersonal_id,
    //				cast(msf.WorkData_begDate as date) as MedServiceMedPersonal_begDT,
    //				cast(msf.WorkData_endDate as date) as MedServiceMedPersonal_endDT
    //			from
    //				v_MedStaffFact msf with (NOLOCK)
    //				LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id=msf.LpuUnit_id
    //			where
    //				msf.Lpu_id = :Lpu_id
    //				and msf.LpuSection_id = :LpuSection_id
    //		';
    //		$result = $this->db->query($query, $data);
    //		if ( is_object($result) )
    //		{
    //			$response = $result->result('array');
    //			foreach($response as $row) {
    //				$row['MedServiceMedPersonal_id'] = 0;
    //				$row['MedService_id'] = $data['MedService_id'];
    //				$row['Server_id'] = $data['Server_id'];
    //				$row['pmUser_id'] = $data['pmUser_id'];
    //				$res = $this->saveMedServiceMedPersonalRecord($row);
    //				if(empty($res))
    //				{
    //					$error = 'Ошибка запроса БД при копировании сотрудников отделения';
    //					break;
    //				}
    //				if(!empty($res[0]['Error_Msg']))
    //				{
    //					$error = $res[0]['Error_Msg'];
    //					break;
    //				}
    //			}
    //		}
    //		return $error;
    //	}
    //
    //	/**
    //	 *  Записывает одну строку UslugaComplexMedService
    //	 */
    //	function saveUslugaComplexMedService($data)
    //	{
    //		if ($data['UslugaComplexMedService_id']>0)
    //		{
    //			$proc = 'p_UslugaComplexMedService_upd';
    //		}
    //		else
    //		{
    //			$proc = 'p_UslugaComplexMedService_ins';
    //		}
    //		$query = '
    //			declare
    //				@Error_Code bigint,
    //				@Error_Message varchar(4000),
    //				@UslugaComplexMedService_id bigint = :UslugaComplexMedService_id;
    //
    //			exec ' .$proc.'
    //				@UslugaComplexMedService_id = @UslugaComplexMedService_id output,
    //				@MedService_id = :MedService_id,
    //				@UslugaComplex_id = :UslugaComplex_id,
    //				@UslugaComplexMedService_begDT = :UslugaComplex_begDT,
    //				@UslugaComplexMedService_endDT = :UslugaComplex_endDT,
    //				@pmUser_id = :pmUser_id,
    //				@Error_Code = @Error_Code output,
    //				@Error_Message = @Error_Message output;
    //				select :UslugaComplex_id as UslugaComplex_id, @UslugaComplexMedService_id as UslugaComplexMedService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //		';
    //		//echo getDebugSql($query, $data);exit;
    //		$result = $this->db->query($query, $data);
    //
    //		if ( is_object($result) ) {
    //			return $result->result('array');
    //		}
    //		else {
    //			return false;
    //		}
    //	}
    //
    //	/**
    //	 *  Метод удаления одной записи UslugaComplexMedService
    //	 */
    //	function deleteUslugaComplexMedService($data)
    //	{
    //		$query = '
    //			declare
    //				@Error_Code int,
    //				@Error_Message varchar(4000);
    //
    //			exec p_UslugaComplexMedService_del
    //				@UslugaComplexMedService_id = :id,
    //				@Error_Code = @Error_Code output,
    //				@Error_Message = @Error_Message output;
    //				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //		';
    //
    //		$result = $this->db->query($query, $data);
    //
    //		if ( is_object($result) ) {
    //			return $result->result('array');
    //		}
    //		else {
    //			return false;
    //		}
    //	}
    //
    //	/**
    //	 *  Читает одну строку для формы редактирования MedServiceMedPersonal
    //	 */
    //	function loadMedServiceMedPersonalEditForm($data)
    //	{
    //		$query = '
    //			Select top 1
    //				msmp.MedServiceMedPersonal_id,
    //				msmp.MedService_id,
    //				msmp.MedPersonal_id,
    //				convert(varchar(10),msmp.MedServiceMedPersonal_begDT,104) as MedServiceMedPersonal_begDT,
    //				convert(varchar(10),msmp.MedServiceMedPersonal_endDT,104) as MedServiceMedPersonal_endDT,
    //				mp.Lpu_id
    //			from
    //				v_MedServiceMedPersonal msmp with (NOLOCK)
    //				left join v_MedService ms with (NOLOCK) on ms.MedService_id = msmp.MedService_id
    //				left join v_MedPersonal mp with (NOLOCK) on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = ms.Lpu_id
    //			where
    //				msmp.MedServiceMedPersonal_id = :MedServiceMedPersonal_id
    //		';
    //		$result = $this->db->query($query, $data);
    //		if ( is_object($result) )
    //		{
    //			return $result->result('array');
    //		}
    //		else
    //		{
    //			return false;
    //		}
    //	}
    //
    //	/**
    //	 *  Записывает одну строку MedServiceMedPersonal
    //	 */
    //	function saveMedServiceMedPersonalRecord($data)
    //	{
    //		if ($data['MedServiceMedPersonal_id'] > 0)
    //		{
    //			$proc = 'p_MedServiceMedPersonal_upd';
    //		}
    //		else
    //		{
    //			$proc = 'p_MedServiceMedPersonal_ins';
    //			$data['MedServiceMedPersonal_id'] = null;
    //		}
    //
    //		$params = array
    //		(
    //			'MedServiceMedPersonal_id' => $data['MedServiceMedPersonal_id'],
    //			'MedService_id' => $data['MedService_id'],
    //			'MedPersonal_id' => $data['MedPersonal_id'],
    //			'MedServiceMedPersonal_begDT' => $data['MedServiceMedPersonal_begDT'],
    //			'MedServiceMedPersonal_endDT' => $data['MedServiceMedPersonal_endDT'],
    //			'Server_id'=>$data['Server_id'],
    //			'pmUser_id' => $data['pmUser_id']
    //		);
    //		$query = '
    //			declare
    //				@Error_Code bigint,
    //				@Error_Message varchar(4000),
    //				@MedServiceMedPersonal_id bigint = :MedServiceMedPersonal_id;
    //
    //			exec ' .$proc. '
    //				@MedServiceMedPersonal_id = @MedServiceMedPersonal_id output,
    //				@MedService_id = :MedService_id,
    //				@MedPersonal_id = :MedPersonal_id,
    //				@MedServiceMedPersonal_begDT = :MedServiceMedPersonal_begDT,
    //				@MedServiceMedPersonal_endDT = :MedServiceMedPersonal_endDT,
    //				@pmUser_id = :pmUser_id,
    //				@Server_id = :Server_id,
    //				@Error_Code = @Error_Code output,
    //				@Error_Message = @Error_Message output;
    //				select @MedServiceMedPersonal_id as MedServiceMedPersonal_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
    //		';
    //
    //		//echo getDebugSQL($query, $params);exit;
    //		$result = $this->db->query($query, $params);
    //
    //		if ( is_object($result) ) {
    //			return $result->result('array');
    //		}
    //		else {
    //			return false;
    //		}
    //
    //	}
    //
    //	/**
    //	 *  Выбираем службы, доступные данному врачу (MedPersonal_id)
    //	 */
    //	function defineMedServiceListOnMedPersonal($data)
    //	{
    //		$query = "
    //			select
    //				MSMP.MedService_id,
    //				MP.Person_Fio as MedPersonal_FIO
    //			from
    //				v_MedServiceMedPersonal MSMP with(nolock)
    //				left join v_MedService MS on MS.MedService_id = MSMP.MedService_id
    //				left join v_MedPersonal MP on MP.MedPersonal_id = MSMP.MedPersonal_id
    //			where
    //				MSMP.MedPersonal_id = :MedPersonal_id
    //				and MS.Lpu_id = :Lpu_id
    //		";
    //
    //		$result = $this->db->query($query, $data);
    //
    //		$res = array();
    //		$res['medservices'] = array();
    //		$res['success'] = true;
    //		if ( is_object($result) ) {
    //			$response = $result->result('array');
    //			if(count($response) > 0) {
    //				$res['MedPersonal_FIO'] = toUTF($response[0]['MedPersonal_FIO']);
    //				foreach($response as $r) {
    //					$res['medservices'][] = $r['MedService_id'];
    //				}
    //			}
    //			return $res;
    //		}
    //		else {
    //			return false;
    //		}
    //	}
    //
    //	/**
    //	*   проверка дублирования врача на службе
    //	*/
    //	function checkDoubleMedPersonal($data)
    //	{
    //		$filter = '';
    //		if (!empty($data['MedServiceMedPersonal_id']))
    //		{
    //			$filter .= ' and MSMP.MedServiceMedPersonal_id <> :MedServiceMedPersonal_id';
    //		}
    //
    //		$query = "
    //			select
    //				MSMP.MedService_id
    //			from
    //				v_MedServiceMedPersonal MSMP with(nolock)
    //				left join v_MedService MS on MS.MedService_id = MSMP.MedService_id
    //			where
    //				MSMP.MedPersonal_id = :MedPersonal_id
    //				and MSMP.MedService_id = :MedService_id
    //				{$filter}
    //				and MS.Lpu_id = :Lpu_id
    //		";
    //
    //		$result = $this->db->query($query, $data);
    //
    //		if ( is_object($result) ) {
    //			return $result->result('array');
    //		}
    //		else {
    //			return false;
    //		}
    //	}
    //
	/**
	 * Загрузка списка персонала ЛПУ по службам и типам служб
	 * @param $data
	 * @return bool
	 */
	function loadMedServiceMedPersonalList($data)
	{
		$where = '';
		if(!empty($data['MedService_id'])) {
			$where .= ' and MSMP.MedService_id = :MedService_id';
		}

		if(!empty($data['MedServiceType_id'])) {
			$where .= ' and MS.MedServiceType_id = :MedServiceType_id';
		}

		if(!empty($data['Lpu_pid'])) {
			$data['Lpu_id'] = $data['Lpu_pid'];
		}

		$query = "
			select distinct
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Person_Fin as \"Person_Fin\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from 
				v_MedPersonal MP 

				 left join v_MedServiceMedPersonal MSMP  on MP.MedPersonal_id = MSMP.MedPersonal_id

				 left join v_MedService MS  on MS.MedService_id = MSMP.MedService_id

			where
				MP.Lpu_id = :Lpu_id
				{$where}
		";

		//echo getDebugSQL($query, $data); exit;
		return $this->queryResult($query,$data);
	}
	
	/**
	 * Загрузка списка МО
	 * @param $data
	 * @return bool
	 */
	function loadLpu($data)
	{


		$LpuSectionProfileCodes = array(
			"perm" => '134',
			"buryatiya" => '134',
			"krym" => '160',
			"ekb" => '0000',
			"astra" => '303',
			"ufa" => '0001',
			"kaluga" => '100',
			"khak" => '134',
			"kareliya" => '160',
			"kz" => '0001',
			"penza" => '160');

		if(isset($LpuSectionProfileCodes[getRegionNick()]))
		{
			$data['LpuSectionProfile_Code'] = $LpuSectionProfileCodes[getRegionNick()];
			$filterPriem = "LSP.LpuSectionProfile_Code = :LpuSectionProfile_Code";
		}
		else
			$filterPriem = "LSP.LpuSectionProfile_SysNick = 'priem'";

		$filterPriem .= " and (COALESCE(LpuSection_disDate, dbo.tzGetDate()) >= dbo.tzGetDate())";

		$filterPriem .= " and (COALESCE(L.Lpu_endDate, dbo.tzGetDate()) >= dbo.tzGetDate())";


		$declare = "";
		$querySection = "

					SELECT
							LS.LpuSection_id as \"id\",
							LS.Lpu_id as \"lpu_id\",   
							cast(L.Lpu_Nick as varchar)|| '/' || cast(RTRIM(LS.LpuSection_Name) as varchar) || '/' || cast(COALESCE(A.Address_Nick, '') as varchar) as \"name\",
							'LpuSection' as \"code\"
						FROM v_LpuSection LS 
							inner join v_Lpu L  on L.Lpu_id = LS.Lpu_id
							inner join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id					
							inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
							inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							left join v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
							inner join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
							left join v_Address A  on A.Address_id = LB.Address_id
						WHERE 
						".$filterPriem;
		$queryMO = "
				SELECT 
					Ml.Lpu_id as \"id\",
					Ml.Lpu_id as \"lpu_id\",
					Ml.Lpu_Nick as \"name\",
					'MO' as code
				FROM v_Lpu Ml  
				  WHERE 
					NOT EXISTS(
						SELECT Mls.LpuSection_id
						 FROM v_LpuSection Mls  
						 inner join v_LpuSectionProfile MLSP  on MLSP.LpuSectionProfile_id = Mls.LpuSectionProfile_id
						 inner join v_Lpu L  on L.Lpu_id = Mls.Lpu_id
						 WHERE
						 Ml.Lpu_id = mls.Lpu_id
						 AND M".$filterPriem."
					 )
					 AND COALESCE(Ml.Lpu_IsTest, 1) != 2
					 AND COALESCE(Ml.Lpu_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
				";
		$queryVolMO = 'SELECT
						cast(av.AttributeValue_ValueIdent as varchar) as AttributeValue_Value
						FROM v_AttributeVision avis 
						INNER JOIN v_AttributeValue av  ON av.AttributeVision_id = avis.AttributeVision_id
						INNER JOIN v_Attribute a  ON a.Attribute_id = av.Attribute_id
						INNER JOIN v_VolumeType vt  ON vt.VolumeType_Code = \'LpuHospitalizationSMP\' 
						WHERE
							avis.AttributeVision_TableName = \'dbo.VolumeType\'
						AND avis.AttributeVision_TablePKey = vt.VolumeType_id';

		$query = $querySection.' UNION ALL '.$queryMO;
		if((!isset($data['viewAllMO']) && ($this->getVolumeTypeLpuHospitalizationSMPExist($data) > 0)) || (isset($data['viewAllMO']) &&!$data['viewAllMO']))
			$query = 'SELECT * FROM ('.$query.') as Hosp WHERE Hosp.Lpu_id IN ( '.$queryVolMO.')';

		return $this->queryResult($declare . $query, $data);
	}
	/**
	 * Возвращает количество заведенных значений в виде объема с кодом LpuHospitalizationSMP
	 *
	 * @param array $data
	 * @return int or false on error
	 */
	public function getVolumeTypeLpuHospitalizationSMPExist( $data ){

		$sql = "SELECT
					cast(av.AttributeValue_ValueIdent as varchar) as \"AttributeValue_Value\"
				FROM v_AttributeVision avis 

				INNER JOIN v_AttributeValue av  ON av.AttributeVision_id = avis.AttributeVision_id

				INNER JOIN v_VolumeType vt  ON vt.VolumeType_Code = 'LpuHospitalizationSMP' 

				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
				AND avis.AttributeVision_TablePKey = vt.VolumeType_id";

		$VolumeValuesArr = $this->db->query($sql, $data)->result_array();
		return count($VolumeValuesArr);
	}

	/**
	 * Возвращает только экспертов БСМЭ
	 *
	 * @param array $data
	 * @return array or false on error
	 */
	public function loadMedPersonalRolesExpertList( $data ){

		$BSME_EXPERT_ROLE = 'bsmeexpert';

		$sql = "
			SELECT DISTINCT
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			FROM
				v_MedPersonal MP 

				INNER JOIN v_pmUserCache PUC  ON( PUC.MedPersonal_id = MP.MedPersonal_id )

			WHERE
				MP.Lpu_id=:Lpu_id
				AND PUC.pmUser_groups iLIKE '%{\"name\":\"".$BSME_EXPERT_ROLE."\"}%'

		";

		return $this->queryResult( $sql, $data );
	}
	
	/**
	 *  Возвращает список подстанций доступных для пользователя
	 *
	 * @param array $data
	 * @return array or false on error
	 */
	public function loadMedPersonalLpuBuildings( $data ){

		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');

		$isCallCenterArm = $this->CmpCallCard_model4E->isCallCenterArm($data);

		if($isCallCenterArm){
			return $this->CmpCallCard_model4E->loadSmpUnitsNested($data, false);
		}

		$operDpt = $this->CmpCallCard_model4E -> getOperDepartament($data);
		$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingBySessionData($data);

        $params = array(
            'LpuBuilding_pid' => $operDpt["LpuBuilding_pid"],
            'medPersonal_id' => $data['session']['medpersonal_id'],
			'SmpUnitType_Code'=>$data['session']['CurARM']['SmpUnitType_Code'],
            'Lpu_id' => $data['Lpu_id']
        );

        // Запрос на получение опер. отдела
		$sql = 'SELECT	DISTINCT 
				LB.LpuBuilding_id as "LpuBuilding_id", -- ID подстанции
                LB.LpuBuilding_Name as "LpuBuilding_Name", -- Полное название подстанции
                LB.LpuBuilding_Nick as "LpuBuilding_Nick"  -- ник
            from v_MedServiceMedPersonal msmp  -- службы врача
                    left join v_MedPersonal mp  on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = :Lpu_id -- врач в нашей МО
                    left join v_MedService MS  on MS.MedService_id = msmp.MedService_id
                    left join v_MedServiceType mst  on mst.MedServiceType_Code = 19 -- Тип СМП
                    left join v_LpuBuilding lb  on lb.LpuBuilding_id = MS.LpuBuilding_id
                    left join v_SmpUnitParam sup  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)
                    left join v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id
            where 
            msmp.MedPersonal_id = :medPersonal_id and 
            sut.SmpUnitType_Code = 4 -- опер отдел
            and (
                cast(msmp.MedServiceMedPersonal_begDT as date) <= cast(dbo.tzGetDate() as date)
                and (cast(msmp.MedServiceMedPersonal_endDT as date) >= cast(dbo.tzGetDate() as date)
                or msmp.MedServiceMedPersonal_endDT is null))';

		$operDpt = $this->queryResult( $sql, $params );

		if(!empty($operDpt)) {
            $params['LpuBuilding_pid'] = $operDpt[0]['LpuBuilding_id'];
        }

		if (!empty($lpuBuilding[0]['LpuBuilding_id'])) {
			if (!empty($operDpt[0]['LpuBuilding_id']) && !in_array($params['SmpUnitType_Code'], array('2','5')) ){ // Значит юзер под опер. отделом.
				$sql = "
                    SELECT DISTINCT
                        LB.LpuBuilding_id as \"LpuBuilding_id\", -- ID подстанции
                        LB.LpuBuilding_Name as \"LpuBuilding_Name\", -- Полное название подстанции
                        LB.LpuBuilding_Nick   as \"LpuBuilding_Nick\" -- ник
                    FROM v_SmpUnitParam sup
                    left join LpuBuilding LB  ON sup.LpuBuilding_id = LB.LpuBuilding_id
                    where sup.LpuBuilding_pid = :LpuBuilding_pid";
			} else { // Тянем подчиненные и удаленные подстанции по рабочим местам юзера.
				$sql = "SELECT	DISTINCT
                            LB.LpuBuilding_id as \"LpuBuilding_id\", -- ID подстанции
                            LB.LpuBuilding_Name as \"LpuBuilding_Name\", -- Полное название подстанции
                            LB.LpuBuilding_Nick   as \"LpuBuilding_Nick\" -- ник
                    FROM v_MedServiceMedPersonal msmp  -- службы врача
                            left join v_MedPersonal mp  on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = :Lpu_id -- врач в нашей МО
                            left join v_MedService MS  on MS.MedService_id = msmp.MedService_id
                            left join v_MedServiceType mst  on mst.MedServiceType_Code = 19 -- Тип СМП
                            left join v_LpuBuilding lb  on lb.LpuBuilding_id = MS.LpuBuilding_id
                            left join v_SmpUnitParam sup  ON  (lb.LpuBuilding_id=sup.LpuBuilding_id)
                            left join v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id
                    where msmp.MedPersonal_id = :medPersonal_id and 
                    sut.SmpUnitType_Code in (2,5) -- подчиненные и удаленные
                    and mp.Lpu_id = :Lpu_id  -- Из этой МО
                    and (
                        cast(msmp.MedServiceMedPersonal_begDT as date) <= cast(dbo.tzGetDate() as date)
                        and (cast(msmp.MedServiceMedPersonal_endDT as date) >= cast(dbo.tzGetDate() as date)
                        or msmp.MedServiceMedPersonal_endDT is null)
                    )";
			}
		}
        $res = $this->queryResult( $sql, $params );

        $response = array();
        if(count($res) > 0){
            foreach($res as $item){
                $response[$item['LpuBuilding_id']] = $item;
            }

            //Достанем список диспетчеров управляющих подстанциями
            $resMP = $this->CmpCallCard_model4E->getDispControlLpuBuilding(array_keys($response), 'LpuBuilding_id');

            //Связываем диспетчеров с подстанциями
            if(count($resMP) > 0){

                foreach($resMP as $mp){
                    if(!isset($response[$mp['LpuBuilding_id']]['MedPersonal_Name'])){
                        $response[$mp['LpuBuilding_id']]['MedPersonal_Name'] = '';
                    }
                    $response[$mp['LpuBuilding_id']]['MedPersonal_Name'] .= $mp['Person_Fin'] . ' ';
                }
            }

        }
		/*
		$sql = "
			SELECT DISTINCT
				LB.LpuBuilding_id,
				LB.LpuBuilding_Name,
				LB.LpuBuilding_Nick
			FROM
				v_LpuBuilding LB 

				inner join v_SmpUnitParam sup  ON LB.LpuBuilding_id = sup.LpuBuilding_id

				inner join v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id

			WHERE
			".$filter."
			and exists (
				-- Рабочие места в удаленных подстанциях
				select top 1 MSMP.MedServiceMedPersonal_id
				from v_MedServiceMedPersonal MSMP 

					inner join v_MedService MS  on MS.MedService_id = MSMP.MedService_id

				where MS.LpuBuilding_id = LB.LpuBuilding_id
					and MSMP.MedPersonal_id = :medPersonal_id

				union all

				-- Рабочее место в оперативном отделе
				select top 1 MSMP.MedServiceMedPersonal_id
				from v_MedServiceMedPersonal MSMP 

					inner join v_MedService MS  on MS.MedService_id = MSMP.MedService_id

				where MS.LpuBuilding_id = :LpuBuilding_pid
					and MSMP.MedPersonal_id = :medPersonal_id
			)
			and Lpu_id = :Lpu_id
		";

		$res = $this->queryResult( $sql, $params );
		$response = array();

		if(count($res) > 0){
			foreach($res as $item){
				$response[$item['LpuBuilding_id']] = $item;
			}

			//Достанем список диспетчеров управляющих подстанциями
			$resMP = $this->CmpCallCard_model4E->getDispControlLpuBuilding(array_keys($response), 'LpuBuilding_id');

			//Связываем диспетчеров с подстанциями
			if(count($resMP) > 0){

				foreach($resMP as $mp){
					if(!isset($response[$mp['LpuBuilding_id']]['MedPersonal_Name'])){
						$response[$mp['LpuBuilding_id']]['MedPersonal_Name'] = '';
					}
					$response[$mp['LpuBuilding_id']]['MedPersonal_Name'] .= $mp['Person_Fin'] . ' ';
				}
			}

		}*/

		return $response;

	}
	
		/**
	 * определяем оперативный отдел для данной подстанции
	 *
	 * @return array
	 */
	/*
	public function getOperDepartament($data){

		$params = array();

		if(empty($data['LpuBuilding_id'])){
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0]['LpuBuilding_id'])){
				//return $this->createError(null, 'Не определена подстанция');
				//бывает что служба на верхнем уровне где нет подстанции
				$params['Lpu_id'] = $data['Lpu_id'];
				$where = ' lb.Lpu_id = :Lpu_id and COALESCE(sup.LpuBuilding_pid, 1) = 1 and sut.SmpUnitType_Code = 4';

			}
			else{
				$params['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
				$where = 'sup.LpuBuilding_id=:LpuBuilding_id';
			}
		}
		else{
			$params['LpuBuilding_id'] = $data["LpuBuilding_id"];
			$where = 'sup.LpuBuilding_id=:LpuBuilding_id';
		}

		$sql = "
			SELECT
				case when COALESCE(sup.LpuBuilding_pid, 1) != 1

					then sup.LpuBuilding_pid
					else sup.LpuBuilding_id
				end as LpuBuilding_pid
			FROM
				v_SmpUnitParam sup 

				LEFT JOIN v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id

				INNER JOIN v_LpuBuilding lb  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)

			WHERE
				{$where}
		";
		$OperDepartament = $this->db->query($sql, $params)->result_array();

		if ( isset($OperDepartament[0]) && !empty($OperDepartament[0]["LpuBuilding_pid"])) {
			 $result = $OperDepartament[0];
			 return $result;
		}
		return false;
	}
	*/
	/**
	 * Определение подстанции по CurMedService_id из сессии
	 */
	/*
	public function getLpuBuildingBySessionData( $data ){
		if ( empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор службы' ) );
		}

		return $this->getLpuBuildingByMedServiceId(array(
			'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
		));

	}
	*/
	
	/**
	 * Получение идентификатора подстанции (LpuBuilding_id) по идентификатору службы(MedService_id)
	 * @param type $data
	 * @return type
	 */
	/*
	public function getLpuBuildingByMedServiceId($data) {
		$rules = array(
			array( 'field' => 'MedService_id' , 'label' => 'Идентификатор службы' , 'rules' => 'required' , 'type' => 'id' ) ,
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;

		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				SELECT
					COALESCE( MS.\"LpuBuilding_id\", 0 ) as \"LpuBuilding_id\"
				FROM
					dbo.\"v_MedService\" MS
				WHERE
					MS.\"MedService_id\" = :MedService_id
			";
		} else {
			$sql = "
				SELECT
					COALESCE( MS.LpuBuilding_id, 0 ) as LpuBuilding_id,

					sut.SmpUnitType_id
				FROM
					v_MedService MS 

				LEFT JOIN v_LpuBuilding LB  on LB.LpuBuilding_id = MS.LpuBuilding_id

				inner join v_SmpUnitParam sup  ON LB.LpuBuilding_id = sup.LpuBuilding_id

				inner join v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id

				WHERE
					MS.MedService_id = :MedService_id
					AND LB.LpuBuildingType_id = 27
			";
		}
		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		$result = $this->queryResult($sql, $queryParams);


		return $result;

	}
	*/
	//
	//	/**
	//	 * получение обслужевающей организации с указанной службой по Lpu_id
	//	 * @param $data
	//	 * @return mixed
	//	 */
	//	function getServeOrgWithMedService($data) {
	//		$query = "						
	//			select top 1
	//				LPU.Lpu_id
	//			from
	//				v_Lpu LPU 

	//				inner join v_MedService MS on MS.Lpu_id = LPU.Lpu_id
	//			where
	//				LPU.Lpu_id = :Lpu_id and MS.MedServiceType_id = :MedServiceType_id
	//		";
	//		$result = $this->db->query($query, $data);
	//		if ( is_object($result) ) {
	//			$response = $result->result('array');
	//			if (count($response) > 0) {
	//				return $data['Lpu_id'];
	//			}
	//		}
	//		
	//		// получение обслуживающей организации
	//		$query = "						
	//			select top 1
	//				LOS.Lpu_id
	//			from
	//				v_LpuOrgServed LOS 

	//				inner join v_Lpu LPUO  on LPUO.Org_id = LOS.Org_id

	//			where
	//				LPUO.Lpu_id = :Lpu_id
	//		";
	//		//echo getDebugSQL($query, $data);exit;
	//		$result = $this->db->query($query, $data);
	//		if ( is_object($result) ) {
	//			$response = $result->result('array');
	//			if (count($response) > 0) {
	//				return $response[0]['Lpu_id'];
	//			}
	//		}
	//		
	//		return $data['Lpu_id'];
	//	}
	//	
	/**
	 * Method description
	 */
	function getLpusWithMedService($data) {
		if (isset($data['comAction']))
		{$action = $data['comAction'];}
		else
		{$action = "AllAddress";}

		$CurArmType = $data['session']['CurArmType'];

		switch ($action)
		{
			//по месту вызова	
			case "CallAddress":
				if ( isset( $data[ 'CmpCallCard_Dom' ] ) ) {
					$currNumHouse = $data[ 'CmpCallCard_Dom' ];
				}

				$filter = "(1=1) ";

				if ((isset($data['KLStreet_id'])) && ($data['KLStreet_id']!='')){
					$filter .= " and LRS.KLStreet_id = ".$data['KLStreet_id'];
				}

				if ((isset($data['KLSubRgn_id'])) && ($data['KLSubRgn_id']!='')){
					$filter .= " and LRS.KLSubRgn_id = ".$data['KLSubRgn_id'];
				}

				if ((isset($data['KLCity_id'])) && ($data['KLCity_id']!='')){
					$filter .= " and LRS.KLCity_id = ".$data['KLCity_id'];
				}

				if ((isset($data['KLTown_id'])) && ($data['KLTown_id']!='')){
					$filter .= " and LRS.KLTown_id = ".$data['KLTown_id'];
				}

				if ((isset($data['MedServiceType_id'])) && ($data['MedServiceType_id']!='')){
					$filter .= " and MS.MedServiceType_id = ".$data['MedServiceType_id'];
				}

				if (isset($data['Person_Age'])) {
					if ( ($data['Person_Age'] > 1) && ($data['Person_Age'] < 18) ) {
						$filter .= " and lr.LpuRegionType_id = 2";
					}

					if ( $data['Person_Age'] > 18) {
						$filter .= " and lr.LpuRegionType_id = 1";
					}

					if ( $data['Person_Age'] == 0)
					{
						$result = array(
						0 => array(
								'Error_Code' => null,
								'Error_Msg' => 'Дети до года обслуживаются в СМП'
								)
						);
						return $result;
						break;
					}
				}

				$query = "
				SELECT DISTINCT
					lr.Lpu_id as \"Lpu_id\",
					LPU.Lpu_Name as \"Lpu_Name\",
					LPU.Lpu_Nick as \"Lpu_Nick\",
					LRS.LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
					FROM LpuRegionStreet LRS 
					left join v_LpuRegion lr  on lr.LpuRegion_id = LRS.LpuRegion_id
					left join v_Lpu LPU  on lr.Lpu_id = LPU.Lpu_id
					inner join v_MedService MS  on MS.Lpu_id = LPU.Lpu_id
					WHERE {$filter}
				";


				$result = $this->db->query($query);
				$res = $result->result('array');
				
				//обработка номеров домов
				$address_result = null;
				if ( is_object($result) ) {
					//var_dump(count($res));
					if (count($res) == 0 )
					{return false; break;}
					//если несколько адресов
					
					$emptyFieldSMP = array(
						'Lpu_id' => '',
						'Lpu_Name' => ' ',
						'Lpu_Nick' => ' '
					);
					
					if (count($res) > 0 ) 
					{	
						//есть номер дома	
						if ( (isset($currNumHouse)) && ($currNumHouse != '') )
						{
							foreach( $res as $k=>$row ) 
							{ 
								$houseNums = explode(",", $row["LpuRegionStreet_HouseSet"]);
								//есть ли дом через запятую
								if (in_array($currNumHouse, $houseNums))
								{									
									$address_result[] = $res[$k];	
									continue;
								}
								
								//есть ли дом в интервале
								if (strstr($row["LpuRegionStreet_HouseSet"], "-") )	
								{
									foreach( $houseNums as $n=>$str )
									{
										if (strstr($str, "-"))
										{
											$nstr = explode("-", $str);
											//проверка на букву (проверяем 2 индекс интервала и вводимое значение, не совпадают - не наш случай)
											$n1 = $currNumHouse;
											settype($n1, 'integer');
											$n2 = $nstr[1];
											settype($n2, 'integer');
											if (str_replace($n1,"", $currNumHouse) == (str_replace($n2,"", $nstr[1])) )
											{
												settype($currNumHouse, 'integer');
												settype($nstr[0], 'integer');
												settype($nstr[1], 'integer');
												//var_dump ($currNumHouse);

												if ( ($nstr[0] <= $currNumHouse) && ($nstr[1] >= $currNumHouse) )
												{
													$address_result[] = $res[$n];	
													continue;
												}
											}
										}
									}
								}			
							}
							//если есть адреса с домом - возвращаем его
							if (isset ($address_result))
							{
								//$address_result[] = $emptyFieldSMP;
								//var_dump($address_result);
								return $address_result;
								break;
							}
						}
						//нет номера дома
						else
						{
							//проверяем на дубликаты, если вдруг ввели одну улицу с разнми домами в участках
							$filterRes = null;
							$oldLpuId = null;
							foreach( $res as $l=>$row ) 
							{ 
								if ( $row['Lpu_id'] != $oldLpuId )
								{
									$filterRes[] = $row;
								}
								$oldLpuId = $row['Lpu_id'];
							}
							//$res[] = $emptyFieldSMP;
							//	var_dump($res);
							return $filterRes;
							
							break;
						}
					}				
				}
				else{
					return false;
				}
			break;
			//если обычная выборка
			case "AllAddress":
				$filter = "1=1";
				$nmpBuildings = '';
				$nmpFields = '';
				if ( !empty( $data[ 'MedServiceType_id' ] )) {
					$filter .= " and MS.MedServiceType_id = :MedServiceType_id";
				}
				
				if(!empty($data['Lpu_id'])) {
					//$filter .= " and LPU.Lpu_id = :Lpu_id";
				}
				
				if( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp', 'nmpgranddoc')) ){
					//цепляем мо с возможностью вызова врача на дом
					$filter .= " or ds.DataStorage_Value = 1";
					$nmpBuildings = " LEFT JOIN dbo.v_DataStorage ds  on (ds.Lpu_id = lpu.Lpu_id and ds.DataStorage_Name = 'homevizit_isallowed' and ds.DataStorageGroup_SysNick = 'homevizit')";
					//$data["LpuBuildingType_id"] = 28;
					//$filter .= " AND LB.LpuBuildingType_id = :LpuBuildingType_id";
					$nmpBuildings .= " LEFT JOIN LATERAL (select * from v_LpuBuilding LB  where LB.LpuBuilding_id = MS.LpuBuilding_id limit 1) as LB ON true
						inner join v_SmpUnitParam sup  ON LB.LpuBuilding_id = sup.LpuBuilding_id
						inner join v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id
					";
					$filter .= " and sut.SmpUnitType_Code = 5";
				}
				//$nmpFields = ",LB.LpuBuilding_id";
				//$nmpFields = ",LB.LpuBuildingType_id";
				
				//только открытые МО
				$filter .= " and ((dbo.tzGetDate() BETWEEN LPU.Lpu_begDate AND LPU.Lpu_endDate) OR COALESCE(LPU.Lpu_endDate, 1) = 1 )";

				
				//только открытые службы
				$filter .= ' and MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)';

				$query = "
					select DISTINCT
						LPU.Lpu_id as \"Lpu_id\"
						,LPU.Lpu_Name as \"Lpu_Name\"
						,LPU.Lpu_Nick as \"Lpu_Nick\"
						--,MS.MedService_Nick							
						{$nmpFields}
					from
						v_Lpu LPU 

						left join v_MedService MS on (MS.Lpu_id = LPU.Lpu_id)
						{$nmpBuildings}
					where
					{$filter}
				";
				
				//var_dump(getDebugSQL($query, $data)); exit;
				$result = $this->db->query($query, $data);	
				
				if ( is_object($result) ) {
					return $result->result('array');
				}
				
				break;
		}
			
		return false;
			
	}

	/**
	 * Получение списка служб НМП региона
	 */
	function loadNmpMedServiceList($data) {
		$params = array();
		$filter = "MST.MedServiceType_SysNick iLIKE 'slneotl'";

		if ( !empty( $data[ 'Lpu_ppdid' ] )) {
			$filter .= " and L.Lpu_id = :Lpu_ppdid";
			$params["Lpu_ppdid"] = $data[ 'Lpu_ppdid' ];
		}
		
		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (MS.MedService_endDT is null or MS.MedService_endDT > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and MS.MedService_endDT <= dbo.tzGetDate()";
		}
		
		$query = "
			select
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Nick as \"MedService_Nick\",
				MS.MedService_Name as \"MedService_Name\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				L.Lpu_Nick||' / '||MedService_Nick as \"NMP_Full_name\"
			from
				v_MedService MS 
				inner join v_MedServiceType MST  on MST.MedServiceType_id = MS.MedServiceType_id
				inner join v_Lpu L  on L.Lpu_id = MS.Lpu_id
			where
				{$filter}
		";
		return $this->queryResult($query, $params);
	}
	/**
	 * Загрузка списка МО
	 * @param $data
	 * @return bool
	 */
	function loadSectionProfileByMO($data)
	{
		$query = "
			SELECT
			  LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			  LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
			  LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			FROM
			  v_LpuSectionProfile LSP 
			WHERE
			  LSP.LpuSectionProfile_id IN (SELECT DISTINCT LS.LpuSectionProfile_id FROM v_LpuSection LS  WHERE LS.Lpu_id = :Lpu_id)
					";

		//echo getDebugSQL($query, $data); exit;
		return $this->queryResult($query, $data);
	}

	/**
	 * loadList
	 * @param $filter
	 * @return bool
	 */
	//	function loadList($filter) {
	//		$where = array();
	//		$p = array();
	//		$order_clause = null;
	//		if (isset($filter['MedService_id']) && $filter['MedService_id']) {
	//			$where[] = 'ms.MedService_id = :MedService_id';
	//			$p['MedService_id'] = $filter['MedService_id'];
	//		}
	//		if (isset($filter['Server_id']) && $filter['Server_id']) {
	//			$where[] = 'ms.Server_id = :Server_id';
	//			$p['Server_id'] = $filter['Server_id'];
	//		}
	//		if (isset($filter['MedService_Name']) && $filter['MedService_Name']) {
	//			$where[] = 'ms.MedService_Name = :MedService_Name';
	//			$p['MedService_Name'] = $filter['MedService_Name'];
	//		}
	//		if (isset($filter['MedService_Nick']) && $filter['MedService_Nick']) {
	//			$where[] = 'ms.MedService_Nick = :MedService_Nick';
	//			$p['MedService_Nick'] = $filter['MedService_Nick'];
	//		}
	//		if (isset($filter['MedServiceType_id']) && $filter['MedServiceType_id']) {
	//			$where[] = 'ms.MedServiceType_id = :MedServiceType_id';
	//			$p['MedServiceType_id'] = $filter['MedServiceType_id'];
	//		}
	//		if (!empty($filter['MedServiceType_SysNick'])) {
	//			$where[] = 'mst.MedServiceType_SysNick = :MedServiceType_SysNick';
	//			$p['MedServiceType_SysNick'] = $filter['MedServiceType_SysNick'];
	//		}
	//		if (isset($filter['Lpu_id']) && $filter['Lpu_id']) {
	//			$where[] = 'ms.Lpu_id = :Lpu_id';
	//			$p['Lpu_id'] = $filter['Lpu_id'];
	//		}
	//		if (isset($filter['LpuBuilding_id']) && $filter['LpuBuilding_id']) {
	//			$where[] = 'ms.LpuBuilding_id = :LpuBuilding_id';
	//			$p['LpuBuilding_id'] = $filter['LpuBuilding_id'];
	//		}
	//		if (isset($filter['LpuUnitType_id']) && $filter['LpuUnitType_id']) {
	//			$where[] = 'ms.LpuUnitType_id = :LpuUnitType_id';
	//			$p['LpuUnitType_id'] = $filter['LpuUnitType_id'];
	//		}
	//		if (isset($filter['LpuUnit_id']) && $filter['LpuUnit_id']) {
	//			$where[] = 'ms.LpuUnit_id = :LpuUnit_id';
	//			$p['LpuUnit_id'] = $filter['LpuUnit_id'];
	//		}
	//		if (isset($filter['LpuSection_id']) && $filter['LpuSection_id']) {
	//			$where[] = 'ms.LpuSection_id = :LpuSection_id';
	//			$p['LpuSection_id'] = $filter['LpuSection_id'];
	//		}
	//		if (isset($filter['MedService_begDT']) && $filter['MedService_begDT']) {
	//			$where[] = 'ms.MedService_begDT = :MedService_begDT';
	//			$p['MedService_begDT'] = $filter['MedService_begDT'];
	//		}
	//		if (isset($filter['MedService_endDT']) && $filter['MedService_endDT']) {
	//			$where[] = 'ms.MedService_endDT = :MedService_endDT';
	//			$p['MedService_endDT'] = $filter['MedService_endDT'];
	//		}
	//		if (isset($filter['Org_id']) && $filter['Org_id']) {
	//			$where[] = 'ms.Org_id = :Org_id';
	//			$p['Org_id'] = $filter['Org_id'];
	//		}
	//		// выбираем только связанные службы
	//		// Пока реализована связь только лаборатории с пунктами забора
	//		$join = '';
	//		if ($filter['MedService_lid']>0) { // Если есть связь с лабораторией
	//			if ($filter['MedServiceType_SysNick']=='pzm') { // Если тип служб = пункт забора
	//				$join = 'inner join v_MedServiceLink msl on ms.MedService_id= msl.MedService_id and msl.MedService_lid = :MedService_lid';
	//			} else { // Если тип служб = лаборатория, других связей пока нет
	//				$join = 'inner join v_MedServiceLink msl on ms.MedService_id= msl.MedService_lid and msl.MedService_id = :MedService_lid';
	//			}
	//			$p['MedService_lid'] = $filter['MedService_lid'];
	//		}
	//		
	//		//  Показать только несвязанные
	//		if ( $filter['NotLinkedMedService_id'] > 0 ) {
	//			if ($filter['MedServiceType_SysNick']=='pzm') { // Если тип служб = пункт забора
	//				$where[] = 'ms.MedService_id not in (select MedService_id from v_MedServiceLink where MedService_lid = :NotLinkedMedService_id)';
	//			} else { // Если тип служб = лаборатория
	//				$where[] = 'ms.MedService_id not in (select MedService_lid as MedService_id from v_MedServiceLink where MedService_id = :NotLinkedMedService_id)';
	//			}
	//			$p['NotLinkedMedService_id'] = $filter['NotLinkedMedService_id'];
	//		}
	//		
	//		if ( $filter['order'] == 'lpu' && !empty($filter['session']['lpu_id']) ) {
	//			$p['OurLpu_id'] = $filter['session']['lpu_id'];
	//			$order_clause = "ORDER BY (case when ms.Lpu_id = :OurLpu_id then 1 else 0 end) DESC, Lpu_id_Nick ASC";
	//		}
	//		
	//
	//		$where_clause = implode(' AND ', $where);
	//		if (strlen($where_clause)) {
	//			$where_clause = 'WHERE '.$where_clause;
	//		}
	//		$q = "
	//		SELECT
	//			ms.MedService_id,
	//			ms.Server_id,
	//			ms.MedService_Name,
	//			ms.MedService_Nick,
	//			ms.MedServiceType_id,
	//			ms.Lpu_id,
	//			ms.LpuBuilding_id,
	//			ms.LpuUnitType_id,
	//			ms.LpuUnit_id,
	//			ms.LpuSection_id,
	//			ms.MedService_begDT,
	//			ms.MedService_endDT,
	//			ms.Org_id,
	//			mst.MedServiceType_Name MedServiceType_id_Name,
	//			Lpu_id_ref.Lpu_Nick Lpu_id_Nick,
	//			LpuBuilding_id_ref.LpuBuilding_Name LpuBuilding_id_Name,
	//			COALESCE(LpuBuilding_id_ref_Address_ref.Address_Address,'(нет адреса)') Address_Address,

	//			LpuUnitType_id_ref.LpuUnitType_Name LpuUnitType_id_Name,
	//			LpuUnit_id_ref.LpuUnit_Name LpuUnit_id_Name,
	//			LpuSection_id_ref.LpuSection_Name LpuSection_id_Name,
	//			Org_id_ref.Org_Name Org_id_Name
	//		FROM
	//			dbo.v_MedService ms 

	//			LEFT JOIN dbo.v_MedServiceType mst  ON mst.MedServiceType_id = ms.MedServiceType_id

	//			LEFT JOIN dbo.v_Lpu Lpu_id_ref  ON Lpu_id_ref.Lpu_id = ms.Lpu_id

	//			LEFT JOIN dbo.v_LpuBuilding LpuBuilding_id_ref  ON LpuBuilding_id_ref.LpuBuilding_id = ms.LpuBuilding_id

	//    		LEFT JOIN dbo.v_Address LpuBuilding_id_ref_Address_ref  ON LpuBuilding_id_ref_Address_ref.Address_id = LpuBuilding_id_ref.Address_id

	//			LEFT JOIN dbo.v_LpuUnitType LpuUnitType_id_ref  ON LpuUnitType_id_ref.LpuUnitType_id = ms.LpuUnitType_id

	//			LEFT JOIN dbo.v_LpuUnit LpuUnit_id_ref  ON LpuUnit_id_ref.LpuUnit_id = ms.LpuUnit_id

	//			LEFT JOIN dbo.v_LpuSection LpuSection_id_ref  ON LpuSection_id_ref.LpuSection_id = ms.LpuSection_id

	//			LEFT JOIN dbo.v_Org Org_id_ref  ON Org_id_ref.Org_id = ms.Org_id

	//			{$join}
	//		$where_clause
	//		$order_clause
	//		";
	//		//echo getDebugSQL($q, $p);
	//		$result = $this->db->query($q, $p);
	//		if ( is_object($result) ) {
	//			return $result->result('array');
	//		}
	//		else {
	//			return false;
	//		}
	//	}
	//	
	//	
	//	/**
	//	 * Получение данных по службе для регистратуры
	//	 */
	//	function getMedServiceInfoForReg($data) 
	//	{
	//		$params = array(
	//			'MedService_id' => $data['MedService_id']
	//		);
	//		$sql = "
	//			select TOP 1
	//				ms.Lpu_id
	//			from v_MedService ms
	//			where 
	//				ms.MedService_id = :MedService_id
	//		";
	//		$result = $this->db->query($sql, $params);
	//		if (is_object($result))
	//		{
	//			$res = $result->result('array');
	//			return $res[0];
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//	}
	//	
	//	/**
	//	 * Получение данных по услуге для регистратуры
	//	 */
	//	function getUslugaComplexInfoForReg($data) 
	//	{
	//		$params = array(
	//			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
	//		);
	//		$sql = "
	//			select TOP 1
	//				ms.Lpu_id
	//			from v_UslugaComplexMedService ucms 

	//			left join v_MedService ms  on ucms.MedService_id = ms.MedService_id

	//			where ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
	//		";
	//		$result = $this->db->query($sql, $params);
	//		if (is_object($result))
	//		{
	//			$res = $result->result('array');
	//			return $res[0];
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//	}
	//
	//	/**
	//	 *  Читает для формы направления на службы
	//	 */
	//	function loadUslugaComplexMedServiceList($data)
	//	{
	//		$filter = '';
	//		$add_join = '';
	//		$params = array(
	//			'LpuSection_id' => $data['LpuSection_id'],
	//		);
	//		switch (true) {
	//			case (!empty($data['EvnPrescr_id'])):
	//				// только службы, оказывающие услугу из назначения либо услугу, связанную с ней по эталонным полям
	//				$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
	//				switch ($data['PrescriptionType_Code']) {
	//					case '6':
	//						$add_join = 'inner join v_EvnUslugaCommon EP  on EP.EvnPrescrProc_id = :EvnPrescr_id

	//				inner join v_UslugaComplex EPUC  on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';

	//						break;
	//					case '7':
	//						$add_join = 'inner join v_EvnPrescrOperUsluga EP  on EP.EvnPrescrOper_id = :EvnPrescr_id

	//				inner join v_UslugaComplex EPUC  on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';

	//						break;
	//					case '11':
	//						$add_join = 'inner join v_EvnPrescrLabDiag EP  on EP.EvnPrescrLabDiag_id = :EvnPrescr_id

	//				inner join v_UslugaComplex EPUC  on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';

	//						break;
	//					case '12':
	//						$add_join = 'inner join v_EvnPrescrFuncDiagUsluga EP  on EP.EvnPrescrFuncDiag_id = :EvnPrescr_id

	//				inner join v_UslugaComplex EPUC  on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';

	//						break;
	//					case '13':
	//						$add_join = 'inner join v_EvnPrescrConsUsluga EP  on EP.EvnPrescrConsUsluga_id = :EvnPrescr_id

	//				inner join v_UslugaComplex EPUC  on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';

	//						break;
	//					default:
	//						return false;
	//				}
	//				$filter .= " and (
	//					UC.UslugaComplex_id = EPUC.UslugaComplex_id
	//					OR UC.UslugaComplex_2004id = EPUC.UslugaComplex_2004id
	//					OR UC.UslugaComplex_2011id = EPUC.UslugaComplex_2011id
	//					OR UC.UslugaComplex_slprofid = EPUC.UslugaComplex_slprofid
	//				) ";
	//				break;
	//			// Фильтры по типу направления
	//			// в зависимости от типа направления доступны разные службы
	//			case (9 == $data['DirType_id']):
	//				// на ВК и МСЭ
	//				//$filter .= " and ms.LpuUnit_id is null and mst.MedServiceType_SysNick in ('vk', 'mse')  ";
	//				$filter .= " /*and ms.LpuUnit_id is null*/ and mst.MedServiceType_SysNick iLIKE 'vk' ";

	//				break;
	//			default:
	//				$filter .= " and mst.MedServiceType_SysNick not in ('patb', 'okadr', 'mstat', 'dpoint', 'merch', 'regpol', 'sprst', 'slneotl', 'smp', 'minzdravdlo')  ";
	//				break;
	//		}
	//
	//		$sql = "
	//			select
	//				-- select
	//				ms.MedService_id,
	//				ucms.UslugaComplexMedService_id,
	//				uc.UslugaComplex_id,
	//				ms.Lpu_id,
	//				ms.LpuBuilding_id,
	//				lu.LpuUnitType_id,
	//				ms.LpuUnit_id,
	//				ms.LpuSection_id,
	//				ls.LpuSectionProfile_id,
	//				ms.MedServiceType_id,
	//				lu.LpuUnitType_SysNick,
	//				l.Lpu_Nick,
	//				ms.MedService_Name,
	//				ms.MedService_Nick,
	//				mst.MedServiceType_SysNick,
	//				uc.UslugaComplex_Name,
	//				ls.LpuSectionProfile_Name,
	//				lu.LpuBuilding_Name,
	//				lu.LpuUnit_Name,
	//				ls.LpuSection_Name,
	//				lua.Address_Address as LpuUnit_Address,
	//				to_char(ttms.TimetableMedService_begTime, 'DD.MM.YYYY') as FirstFreeDate,

	//				to_char(ttms.TimetableMedService_begTime, 'HH24:MI:SS') as FirstFreeTime

	//				,user_ls.Lpu_id as user_Lpu_id
	//				,user_lu.LpuBuilding_id as user_LpuBuilding_id
	//				,user_ls.LpuUnit_id as user_LpuUnit_id
	//				,user_ls.LpuSection_id as user_LpuSection_id
	//				-- end select
	//			from
	//				-- from
	//				v_MedService ms 

	//				inner join v_UslugaComplexMedService ucms  on ucms.MedService_id = ms.MedService_id

	//				left join v_UslugaComplex uc  on uc.UslugaComplex_id = ucms.UslugaComplex_id

	//				left join v_LpuUnit lu  on lu.LpuUnit_id = ms.LpuUnit_id

	//				left join v_Lpu l  on ms.Lpu_id = l.Lpu_id

	//				left join v_LpuSection ls  on ms.LpuSection_id = ls.LpuSection_id

	//				left join v_MedServiceType mst  on ms.MedServiceType_id = mst.MedServiceType_id

	//				inner join v_LpuSection user_ls  on user_ls.LpuSection_id = :LpuSection_id

	//				inner join v_LpuUnit user_lu  on user_lu.LpuUnit_id = user_ls.LpuUnit_id

	//				left join v_Address lua  on lua.Address_id = COALESCE(lu.Address_id,l.UAddress_id)


	//				LEFT JOIN LATERAL (

	//					select top 1 ttms.TimetableMedService_begTime from v_TimetableMedService_lite ttms 

	//					where ttms.Person_id is null
	//					and (ttms.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id OR ttms.MedService_id = ucms.MedService_id)
	//					and ttms.TimetableMedService_begTime >= GETDATE()
	//				) ttms
	//				{$add_join}
	//				-- end from
	//			WHERE
	//				-- where
	//				(ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))
	//				--and uc.UslugaComplex_pid is null
	//				{$filter}
	//				-- end where
	//			ORDER BY
	//				-- order by
	//				case when ms.Lpu_id=user_ls.Lpu_id then '' else l.Lpu_Nick end,
	//				case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then '' else lu.LpuBuilding_Name end,
	//				case when ms.LpuUnit_id=user_ls.LpuUnit_id then '' else lu.LpuUnit_Name end,
	//				case when ms.LpuSection_id=user_ls.LpuSection_id then '' else ls.LpuSection_Name end,
	//				uc.UslugaComplex_Name
	//				-- end order by
	//		";
	//		/*
	//				left join v_UslugaComplex uc  on uc.UslugaComplex_id = ucms.UslugaComplex_id

	//				left join v_UslugaComplexAttribute uca  on uca.UslugaComplex_id = uc.UslugaComplex_id

	//				left join v_UslugaComplexAttributeType ucat  on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id

	//		 */
	//
	//		// echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
	//		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
	//		$result_count = $this->db->query(getCountSQLPH($sql), $params);
	//
	//		if (is_object($result_count))
	//		{
	//			$cnt_arr = $result_count->result('array');
	//			$count = $cnt_arr[0]['cnt'];
	//			unset($cnt_arr);
	//		}
	//		else
	//		{
	//			$count = 0;
	//		}
	//		if (is_object($result))
	//		{
	//			$response = array();
	//			$response['data'] = $result->result('array');
	//			$response['totalCount'] = $count;
	//			return $response;
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//
	//	}
	//
	//	/**
	//	 * Загрузка данных в грид формы выбора службы по известной услуге
	//	 *
	//	 * Порядок отображения служб:
	//	 * @todo Последние N служб, на которые данный врач создавал направления.
	//	 * Наше отделение
	//	 * Наша группа отделений
	//	 * Наше подразделение
	//	 * Наше ЛПУ
	//	 * службы в других ЛПУ
	//	 */
	//	function getMedServiceSelectList($data)
	//	{
	//		$params = array(
	//			'LpuSection_id' => $data['userLpuSection_id'],
	//		);
	//		$params['UslugaComplex_id'] = $data['filterByUslugaComplex_id'];
	//		/*
	//		//$uslugaCategoryList = array('gost2011');
	//		$filterField = 'UslugaComplex_2011id';
	//		// будем показывать только услуги с совпадающим $filterField
	//		$result = $this->db->query(
	//			"select {$filterField} from v_UslugaComplex   where UslugaComplex_id = :UslugaComplex_id",

	//			array('UslugaComplex_id'=>$data['filterByUslugaComplex_id'])
	//		);
	//		if (is_object($result)) {
	//			$result = $result->result('array');
	//			if (count($result) > 0) {
	//				$params[$filterField] = $result[0][$filterField];
	//			}
	//		}
	//		if (empty($params[$filterField])) {
	//			return false;
	//		}
	//		*/
	//		$sql = "
	//			select
	//				-- select
	//				COALESCE(pzm.MedService_id, ms.MedService_id) as MedService_id,

	//				ucms.UslugaComplexMedService_id,
	//				lpu.Lpu_id,
	//				lpu.Lpu_Nick,
	//				lu.LpuBuilding_id,
	//				lu.LpuBuilding_Name,
	//				lu.LpuUnitType_id,
	//				lu.LpuUnitType_SysNick,
	//				lu.LpuUnit_id,
	//				lu.LpuUnit_Name,
	//				lua.Address_Address as LpuUnit_Address,
	//				ls.LpuSection_id,
	//				ls.LpuSection_Name,
	//				ls.LpuSectionProfile_id,
	//				ms.MedService_Name,
	//				ms.MedService_Nick,
	//				mst.MedServiceType_id,
	//				mst.MedServiceType_SysNick,
	//				uc.UslugaComplex_id,
	//				uc.UslugaComplex_Name
	//				,ttms.TimetableMedService_id as TimetableMedService_id
	//				,ttms.MedService_id as ttms_MedService_id
	//				,to_char(ttms.TimetableMedService_begTime, 'DD.MM.YYYY') +' '

	//				+to_char(ttms.TimetableMedService_begTime, 'HH24:MI:SS') as TimetableMedService_begTime

	//				,case when ms.MedServiceType_id = 6 then ms.MedService_id else null end as lab_MedService_id
	//				,pzm.MedService_id as pzm_MedService_id
	//				,pzm.Lpu_id as pzm_Lpu_id
	//				,pzm.MedServiceType_id as pzm_MedServiceType_id
	//				,'pzm' as pzm_MedServiceType_SysNick
	//				,pzm.MedService_Name as pzm_MedService_Name
	//				,pzm.MedService_Nick as pzm_MedService_Nick
	//				-- end select
	//			from
	//				-- from
	//				v_UslugaComplexMedService ucms 

	//				inner join v_LpuSection user_ls  on user_ls.LpuSection_id = :LpuSection_id

	//				inner join v_LpuUnit user_lu  on user_lu.LpuUnit_id = user_ls.LpuUnit_id

	//				-- услуга лаборатории или службы другого типа
	//				left join v_UslugaComplex uc  on uc.UslugaComplex_id = ucms.UslugaComplex_id

	//
	//				inner join v_MedService ms  on ucms.MedService_id = ms.MedService_id

	//				left join v_MedServiceLink msl  on ms.MedServiceType_id = 6

	//					and msl.MedService_lid = ms.MedService_id
	//					and msl.MedServiceLinkType_id = 1
	//				left join v_MedService pzm  on ms.MedServiceType_id = 6

	//					and pzm.MedServiceType_id = 7
	//					and msl.MedService_id = pzm.MedService_id
	//					and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > cast(GETDATE() as date))
	//				left join v_UslugaComplexMedService ucpzm  on ms.MedServiceType_id = 6

	//					and ucpzm.MedService_id = pzm.MedService_id
	//					and ucms.UslugaComplex_id = ucpzm.UslugaComplex_id
	//				left join v_MedServiceType mst  on mst.MedServiceType_id = ms.MedServiceType_id--COALESCE(pzm.MedServiceType_id, ms.MedServiceType_id) =


	//				left join v_Lpu lpu  on lpu.Lpu_id = ms.Lpu_id --COALESCE(pzm.Lpu_id, ms.Lpu_id)


	//				left join v_LpuUnit lu  on lu.LpuUnit_id = COALESCE(pzm.LpuUnit_id, ms.LpuUnit_id)


	//				left join v_LpuSection ls  on ms.LpuSection_id = ls.LpuSection_id

	//				left join v_Address lua  on lua.Address_id = COALESCE(lu.Address_id, lpu.UAddress_id)


	//
	//				LEFT JOIN LATERAL (

	//					select top 1
	//						TimetableMedService_id,
	//						MedService_id,
	//						TimetableMedService_begTime
	//					from v_TimetableMedService_lite 

	//					where Person_id is null
	//					and (
	//						UslugaComplexMedService_id = ucpzm.UslugaComplexMedService_id
	//						OR MedService_id = pzm.MedService_id
	//						OR UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
	//						OR MedService_id = ucms.MedService_id
	//					)
	//					and TimetableMedService_begTime >= GETDATE()
	//				) ttms
	//				-- end from
	//			WHERE
	//				-- where
	//				ucms.UslugaComplex_id = :UslugaComplex_id
	//				and ms.LpuSection_id is not null
	//				and (
	//					ms.MedServiceType_id = (
	//						case when exists (
	//							select t1.UslugaComplexAttribute_id
	//							from v_UslugaComplexAttribute t1 

	//								inner join v_UslugaComplexAttributeType t2  on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id

	//							where t1.UslugaComplex_id = ucms.UslugaComplex_id
	//								and t2.UslugaComplexAttributeType_SysNick iLIKE 'lab'

	//						) then 6 else 0 end
	//					) OR ms.MedServiceType_id is not null
	//				)
	//				and ucms.UslugaComplexMedService_pid IS NULL -- только 0 уровня
	//				and cast(ms.MedService_begDT as date) <= cast(GETDATE() as date)
	//				and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))
	//				and cast(ucms.UslugaComplexMedService_begDT as date) <= cast(GETDATE() as date)
	//				and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > cast(GETDATE() as date))
	//				-- end where
	//			ORDER BY
	//				-- order by
	//				case when ms.Lpu_id=user_ls.Lpu_id then '' else lpu.Lpu_Nick end,
	//				case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then '' else lu.LpuBuilding_Name end,
	//				case when ms.LpuUnit_id=user_ls.LpuUnit_id then '' else lu.LpuUnit_Name end,
	//				case when ms.LpuSection_id=user_ls.LpuSection_id then '' else ls.LpuSection_Name end
	//				-- end order by
	//		";
	//
	//		/*
	//		,user_ls.Lpu_id as user_Lpu_id
	//		,user_lu.LpuBuilding_id as user_LpuBuilding_id
	//		,user_ls.LpuUnit_id as user_LpuUnit_id
	//		,user_ls.LpuSection_id as user_LpuSection_id
	//		 */
	//
	//		// echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
	//		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
	//		$result_count = $this->db->query(getCountSQLPH($sql), $params);
	//
	//		if (is_object($result_count))
	//		{
	//			$cnt_arr = $result_count->result('array');
	//			$count = $cnt_arr[0]['cnt'];
	//			unset($cnt_arr);
	//		}
	//		else
	//		{
	//			$count = 0;
	//		}
	//		if (is_object($result))
	//		{
	//			$response = array();
	//			$response['data'] = $result->result('array');
	//			$response['totalCount'] = $count;
	//			return $response;
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//	}
	//
	//	/**
	//	 * Загрузка данных в грид услуг левой части формы добавления назначений услуг
	//	 *
	//	 * Если услуга оказывается службой, то категория услуги не имеет значения
	//	 * В противном случае отображаются услуги категории ГОСТ 2011
	//	 */
	//	function getUslugaComplexSelectList($data)
	//	{
	//		$filter = '';
	//		$filter_ms = '';
	//		$params = array(
	//			'LpuSection_id' => $data['userLpuSection_id'],
	//		);
	//		$uslugaCategoryList = array('gost2011');
	//		$filterField = 'UslugaComplex_2011id';
	//		if (!empty($data['uslugaCategoryList'])) {
	//			//категория услуги в фильтре по услуге
	//			//$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);
	//		}
	//		//если есть служба, то показываем любые категории
	//		//если нет службы, то показываем только услуги указанной категории
	//		$filter_cat = " and (ms.MedService_id is not null or cat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "'))";
	//
	//		if ( !empty($data['allowedUslugaComplexAttributeList']) ) {
	//			$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);
	//
	//			if ( is_array($allowedUslugaComplexAttributeList) && count($allowedUslugaComplexAttributeList) > 0 ) {
	//				$filter .= " and exists (
	//					select t1.UslugaComplexAttribute_id
	//					from v_UslugaComplexAttribute t1 

	//						inner join v_UslugaComplexAttributeType t2  on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id

	//					where t1.UslugaComplex_id = uc.UslugaComplex_id
	//						and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
	//				)";
	//				/*switch (true) {
	//					case (in_array('manproc',$allowedUslugaComplexAttributeList)):
	//						$filter_ms .= ' and ms.MedServiceType_id = 13';
	//						break;
	//					case (in_array('oper',$allowedUslugaComplexAttributeList)):
	//						$filter_ms .= ' and ms.MedServiceType_id = 5';
	//						break;
	//					case (in_array('lab',$allowedUslugaComplexAttributeList)):
	//						$filter_ms .= ' and ms.MedServiceType_id = 6';
	//						break;
	//					case (in_array('func',$allowedUslugaComplexAttributeList)):
	//						$filter_ms .= ' and ms.MedServiceType_id = 8';
	//						break;
	//					case (in_array('consult',$allowedUslugaComplexAttributeList)):
	//						$filter_ms .= ' and ms.MedServiceType_id = 29';
	//						break;
	//				}*/
	//			}
	//		}
	//
	//		if (!empty($data['filterByLpu_id'])) {
	//			// Фильтруем места оказания по ЛПУ
	//			$filter_ms .= ' and ms.Lpu_id = :Lpu_id';
	//			$filter .= ' and ms.MedService_id is not null';
	//			$params['Lpu_id'] = $data['filterByLpu_id'];
	//		}
	//
	//		if (!empty($data['filterByLpu_str'])) {
	//			// Фильтруем места оказания по ЛПУ Lpu_Nick или Lpu_Name ?
	//			$filter .= ' and l.Lpu_Nick iLIKE :lpuQuery';

	//			$params['lpuQuery'] = '%'.$data['filterByLpu_str'].'%';
	//		}
	//
	//		if (!empty($data['filterByUslugaComplex_id'])) {
	//			// будем показывать только услуги с совпадающим $filterField
	//			$result = $this->db->query(
	//				"select {$filterField} from v_UslugaComplex   where UslugaComplex_id = :UslugaComplex_id",

	//				array('UslugaComplex_id'=>$data['filterByUslugaComplex_id'])
	//			);
	//			if (is_object($result))
	//			{
	//				$result = $result->result('array');
	//				if (count($result) > 0) {
	//					$filter .= " and uc.{$filterField} = :{$filterField}";
	//					$params[$filterField] = $result[0][$filterField];
	//				}
	//			}
	//		}
	//
	//		if (!empty($data['filterByUslugaComplex_str'])) {
	//			$filter .= ' and (uc.UslugaComplex_Code iLIKE :ucQuery or uc.UslugaComplex_Name iLIKE :ucQuery)';

	//			$params['ucQuery'] = '%'.$data['filterByUslugaComplex_str'].'%';
	//		}
	//
	//		$sql = "
	//			select
	//				-- select
	//				uc.UslugaComplex_id,
	//				uc.UslugaComplex_Code,
	//				uc.UslugaComplex_Name,
	//				COALESCE((( select

	//						COUNT(ms.MedService_id)
	//					from v_MedService ms 

	//					inner join v_UslugaComplexMedService ucms  on ms.MedService_id = ucms.MedService_id

	//					where uc.UslugaComplex_id = ucms.UslugaComplex_id and ms.LpuSection_id is not null
	//					and ucms.UslugaComplexMedService_pid IS NULL
	//					{$filter_ms}
	//					and cast(ms.MedService_begDT as date) <= cast(GETDATE() as date)
	//					and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))
	//					and cast(ucms.UslugaComplexMedService_begDT as date) <= cast(GETDATE() as date)
	//					and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > cast(GETDATE() as date))
	//				)),0) as MedService_cnt,
	//				ms.MedService_id,
	//				ms.UslugaComplexMedService_id,
	//				ms.MedService_Name,
	//				ms.MedService_Nick,
	//				mst.MedServiceType_id,
	//				mst.MedServiceType_SysNick,
	//				l.Lpu_id,
	//				l.Lpu_Nick,
	//				lu.LpuBuilding_id,
	//				lu.LpuBuilding_Name,
	//				lu.LpuUnit_id,
	//				lu.LpuUnit_Name,
	//				lua.Address_Address as LpuUnit_Address,
	//				lu.LpuUnitType_id,
	//				lu.LpuUnitType_SysNick,
	//				ls.LpuSection_id,
	//				ls.LpuSection_Name,
	//				ls.LpuSectionProfile_id,
	//				ls.LpuSectionProfile_Name,
	//				ttms.MedService_id as ttms_MedService_id
	//				,ttms.TimetableMedService_id
	//				,to_char(ttms.TimetableMedService_begTime, 'DD.MM.YYYY') +' '

	//				+to_char(ttms.TimetableMedService_begTime, 'HH24:MI:SS') as TimetableMedService_begTime

	//				,case
	//					when ms.MedService_id is not null and exists(
	//						select top 1 UslugaComplexMedService_id
	//						from v_UslugaComplexMedService 

	//						where UslugaComplexMedService_pid = ms.UslugaComplexMedService_id
	//					) then 1
	//					when ms.MedService_id is null and exists(
	//						select top 1 UslugaComplex_id
	//						from v_UslugaComplexComposition 

	//						where UslugaComplex_pid = uc.UslugaComplex_id
	//					) then 1
	//					else 0
	//				end as isComposite
	//				,pzm.MedService_id as pzm_MedService_id
	//				,pzm.Lpu_id as pzm_Lpu_id
	//				,pzm.MedServiceType_id as pzm_MedServiceType_id
	//				,pzm.MedServiceType_SysNick as pzm_MedServiceType_SysNick
	//				,pzm.MedService_Name as pzm_MedService_Name
	//				,pzm.MedService_Nick as pzm_MedService_Nick
	//				-- end select
	//			from
	//				-- from
	//				v_UslugaComplex uc 

	//				inner join v_LpuSection user_ls  on user_ls.LpuSection_id = :LpuSection_id

	//				inner join v_LpuUnit user_lu  on user_lu.LpuUnit_id = user_ls.LpuUnit_id

	//				LEFT JOIN LATERAL (

	//					select top 1
	//						ms.MedService_id,
	//						ms.Lpu_id,
	//						ms.LpuBuilding_id,
	//						ms.LpuUnit_id,
	//						ms.LpuSection_id,
	//						ms.MedServiceType_id,
	//						ms.MedService_Name,
	//						ms.MedService_Nick,
	//						ucms.UslugaComplexMedService_id
	//					from v_MedService ms 

	//						inner join v_UslugaComplexMedService ucms  on ms.MedService_id = ucms.MedService_id

	//						inner join v_Lpu tl  on tl.Lpu_id = ms.Lpu_id -- https://redmine.swan.perm.ru/issues/25958

	//					where
	//						uc.UslugaComplex_id = ucms.UslugaComplex_id
	//						--показываем службы только уровня отделения, для корректного направления
	//						and ms.LpuSection_id is not null
	//						and ucms.UslugaComplexMedService_pid IS NULL
	//					{$filter_ms}
	//					and cast(ms.MedService_begDT as date) <= cast(GETDATE() as date)
	//					and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))
	//					and cast(ucms.UslugaComplexMedService_begDT as date) <= cast(GETDATE() as date)
	//					and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > cast(GETDATE() as date))
	//				) ms
	//				inner join v_UslugaCategory cat  on cat.UslugaCategory_id = uc.UslugaCategory_id

	//					 {$filter_cat}
	//				LEFT JOIN LATERAL (

	//					select top 1
	//						pzm.MedService_id,
	//						pzm.Lpu_id,
	//						pzm.LpuBuilding_id,
	//						pzm.LpuUnit_id,
	//						pzm.LpuSection_id,
	//						pzm.MedServiceType_id,
	//						'pzm' as MedServiceType_SysNick,
	//						pzm.MedService_Name,
	//						pzm.MedService_Nick,
	//						ucpzm.UslugaComplexMedService_id
	//					from v_MedServiceLink msl 

	//					left join v_MedService pzm  on pzm.MedServiceType_id = 7

	//						and msl.MedService_id = pzm.MedService_id
	//						and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > cast(GETDATE() as date))
	//					left join v_UslugaComplexMedService ucpzm  on ucpzm.MedService_id = pzm.MedService_id

	//						and ucpzm.UslugaComplex_id = uc.UslugaComplex_id
	//					where msl.MedService_lid = ms.MedService_id and msl.MedServiceLinkType_id = 1
	//					{$filter_ms}
	//				) pzm
	//
	//				left join v_LpuUnit lu  on lu.LpuUnit_id = ms.LpuUnit_id

	//				left join v_Lpu l  on ms.Lpu_id = l.Lpu_id

	//				left join v_LpuSection ls  on ms.LpuSection_id = ls.LpuSection_id

	//				left join v_MedServiceType mst  on ms.MedServiceType_id = mst.MedServiceType_id

	//				left join v_Address lua  on lua.Address_id = COALESCE(lu.Address_id,l.UAddress_id)


	//				LEFT JOIN LATERAL (

	//					select top 1
	//					ttms.TimetableMedService_id,
	//					ttms.TimetableMedService_begTime,
	//					ttms.MedService_id
	//					from v_TimetableMedService_lite ttms 

	//					where ttms.Person_id is null
	//					and (
	//						ttms.UslugaComplexMedService_id = pzm.UslugaComplexMedService_id
	//						OR ttms.MedService_id = pzm.MedService_id
	//						OR ttms.UslugaComplexMedService_id = ms.UslugaComplexMedService_id
	//						OR ttms.MedService_id = ms.MedService_id
	//					)
	//					and ttms.TimetableMedService_begTime >= GETDATE()
	//				) ttms
	//				-- end from
	//			WHERE
	//				-- where
	//				(1=1)
	//				{$filter}
	//				-- end where
	//			ORDER BY
	//				-- order by
	//				case when ms.MedService_id is null then 2 else 1 end,
	//				case when ms.Lpu_id=user_ls.Lpu_id then '' else l.Lpu_Nick end,
	//				case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then '' else lu.LpuBuilding_Name end,
	//				case when ms.LpuUnit_id=user_ls.LpuUnit_id then '' else lu.LpuUnit_Name end,
	//				case when ms.LpuSection_id=user_ls.LpuSection_id then '' else ls.LpuSection_Name end,
	//				uc.UslugaComplex_Name
	//				-- end order by
	//		";
	//
	//		/*
	//		Порядок отображения услуг:
	//		@todo Последние N услуг-служб либо услуг без служб, на которые данный врач создавал направления.
	//		Наше отделение
	//		Наша группа отделений
	//		Наше подразделение
	//		Наше ЛПУ
	//		Услуги в других ЛПУ
	//		Прочие услуги из справочника, которые не оказывается в других ЛПУ.
	//		 */
	//
	//		/*
	//		,user_ls.Lpu_id as user_Lpu_id
	//		,user_lu.LpuBuilding_id as user_LpuBuilding_id
	//		,user_ls.LpuUnit_id as user_LpuUnit_id
	//		,user_ls.LpuSection_id as user_LpuSection_id
	//		 */
	//
	//		// echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
	//		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
	//		$result_count = $this->db->query(getCountSQLPH($sql), $params);
	//		// echo getDebugSQL(getCountSQLPH($sql), $params); die;
	//
	//		if (is_object($result_count))
	//		{
	//			$cnt_arr = $result_count->result('array');
	//			$count = $cnt_arr[0]['cnt'];
	//			unset($cnt_arr);
	//		}
	//		else
	//		{
	//			$count = 0;
	//		}
	//		if (is_object($result))
	//		{
	//			$response = array();
	//			$response['data'] = $result->result('array');
	//			$response['totalCount'] = $count;
	//			return $response;
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//	}
	//
	//	/**
	//	 *  Загружает список услуг служб для назначения лабораторной диагностики
	//	 */
	//	function getUslugaComplexMedServiceList($data)
	//	{
	//		$filter = 'ms.Lpu_id = :Lpu_id
	//		and ms.MedServiceType_id = 6
	//		and ucms.UslugaComplexMedService_pid IS NULL -- только 0 уровня
	//		and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))';
	//		$add_join = '';
	//		$params = array(
	//			'Lpu_id' => $data['Lpu_id'],
	//			'LpuSection_id' => $data['LpuSection_id'],
	//		);
	//
	//		if (!empty($data['uslugaCategoryList'])) {
	//			$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);
	//			if ( is_array($uslugaCategoryList) && count($uslugaCategoryList) > 0 ) {
	//				$filter .= " and cat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "')";
	//			}
	//		}
	//
	//		if ( !empty($data['allowedUslugaComplexAttributeList']) ) {
	//			$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);
	//
	//			if ( is_array($allowedUslugaComplexAttributeList) && count($allowedUslugaComplexAttributeList) > 0 ) {
	//				$filter .= " and exists (
	//					select t1.UslugaComplexAttribute_id
	//					from v_UslugaComplexAttribute t1 

	//						inner join v_UslugaComplexAttributeType t2  on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id

	//					where t1.UslugaComplex_id = uc.UslugaComplex_id
	//						and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
	//				)";
	//			}
	//		}
	//
	//		$sql = "
	//			select
	//				-- select
	//				ms.MedService_id,
	//				ucms.UslugaComplexMedService_id,
	//				uc.UslugaComplex_id,
	//				ms.Lpu_id,
	//				ms.LpuBuilding_id,
	//				lu.LpuUnitType_id,
	//				ms.LpuUnit_id,
	//				ms.LpuSection_id,
	//				ls.LpuSectionProfile_id,
	//				ms.MedServiceType_id,
	//				lu.LpuUnitType_SysNick,
	//				ms.MedService_Name,
	//				ms.MedService_Nick,
	//				mst.MedServiceType_SysNick,
	//				uc.UslugaComplex_Name
	//				,case when exists(
	//					select top 1 UslugaComplexMedService_id
	//					from v_UslugaComplexMedService 

	//					where UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
	//				) then 1 else 0 end as isComposite
	//				,pzm.MedService_id as pzm_MedService_id
	//				,pzm.MedService_Name as pzm_MedService_Name
	//				,pzm.MedService_Nick as pzm_MedService_Nick
	//				,pzm.MedServiceType_id as pzm_MedServiceType_id
	//				,pzm.MedServiceType_SysNick as pzm_MedServiceType_SysNick
	//				,ttms.MedService_id as ttms_MedService_id
	//				-- end select
	//			from
	//				-- from
	//				v_MedService ms 

	//				inner join v_LpuSection user_ls  on user_ls.LpuSection_id = :LpuSection_id

	//				inner join v_LpuUnit user_lu  on user_lu.LpuUnit_id = user_ls.LpuUnit_id

	//				inner join v_UslugaComplexMedService ucms  on ucms.MedService_id = ms.MedService_id

	//				inner join v_UslugaComplex uc  on uc.UslugaComplex_id = ucms.UslugaComplex_id

	//				inner join v_UslugaCategory cat  on cat.UslugaCategory_id = uc.UslugaCategory_id

	//				inner join v_LpuUnit lu  on lu.LpuUnit_id = ms.LpuUnit_id

	//				-- inner джойном исключаем криво заведенные службы
	//				inner join v_LpuSection ls  on ms.LpuSection_id = ls.LpuSection_id

	//				inner join v_MedServiceType mst  on ms.MedServiceType_id = mst.MedServiceType_id

	//				LEFT JOIN LATERAL (

	//					select top 1
	//						pzm.MedService_id,
	//						pzm.MedService_Name,
	//						pzm.MedService_Nick,
	//						pzm.MedServiceType_id,
	//						mstpzm.MedServiceType_SysNick
	//					from v_MedServiceLink msl 

	//					inner join v_MedService pzm  on msl.MedService_id = pzm.MedService_id

	//					inner join v_MedServiceType mstpzm  on pzm.MedServiceType_id = mstpzm.MedServiceType_id

	//					where msl.MedService_lid = ms.MedService_id
	//					and pzm.Lpu_id = ms.Lpu_id
	//					and msl.MedServiceLinkType_id = 1
	//					and mstpzm.MedServiceType_SysNick iLIKE 'pzm'

	//					and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > cast(GETDATE() as date))
	//				) pzm
	//				LEFT JOIN LATERAL (

	//					select top 1
	//						MedService_id
	//					from v_TimetableMedService_lite 

	//					where Person_id is null
	//					and (MedService_id = pzm.MedService_id OR UslugaComplexMedService_id = ucms.UslugaComplexMedService_id OR MedService_id = ucms.MedService_id)
	//					and TimetableMedService_begTime >= GETDATE()
	//				) ttms
	//				{$add_join}
	//				-- end from
	//			WHERE
	//				-- where
	//				{$filter}
	//				-- end where
	//			ORDER BY
	//				-- order by
	//				case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then null else COALESCE(lu.LpuBuilding_Name,'-') end,

	//				uc.UslugaComplex_Name
	//				-- end order by
	//		";
	//
	//		//echo getDebugSQL($sql, $params);
	//		$result = $this->db->query($sql, $params);
	//		if (is_object($result))
	//		{
	//			return $result->result('array');
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//	}
	//
	//	/**
	//	 *  Загружает состав услуг в меню
	//	 */
	//	function loadCompositionMenu($data)
	//	{
	//		if (empty($data['UslugaComplexMedService_pid'])) {
	//			$params = array('UslugaComplex_pid' => $data['UslugaComplex_pid']);
	//			$sql = "
	//			select
	//				UC.UslugaComplex_id,
	//				UC.UslugaComplex_Code,
	//				UC.UslugaComplex_Name
	//			from v_UslugaComplexComposition UCC 

	//			inner join v_UslugaComplex UC  on UCC.UslugaComplex_id = UC.UslugaComplex_id

	//			where UCC.UslugaComplex_pid = :UslugaComplex_pid
	//			";
	//		} else {
	//			$params = array('UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid']);
	//			$sql = "
	//			select
	//				UC.UslugaComplex_id,
	//				UC.UslugaComplex_Code,
	//				UC.UslugaComplex_Name
	//			from v_UslugaComplexMedService UCMS 

	//			left join v_UslugaComplexMedService UCCMS  on UCCMS.UslugaComplexMedService_pid = UCMS.UslugaComplexMedService_id

	//			left join v_UslugaComplexComposition UCC on UCCMS.UslugaComplex_id is null and UslugaComplex_pid = UCMS.UslugaComplex_id

	//			inner join v_UslugaComplex UC  on COALESCE(UCCMS.UslugaComplex_id, UCC.UslugaComplex_id) = UC.UslugaComplex_id


	//			where UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_pid
	//			";
	//		}
	//		//echo getDebugSQL($sql, $params);
	//		$result = $this->db->query($sql, $params);
	//		if (is_object($result))
	//		{
	//			return $result->result('array');
	//		}
	//		else
	//		{
	//			return false;
	//		}
	//	}
}
