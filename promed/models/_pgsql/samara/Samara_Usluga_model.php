<?php

require_once(APPPATH.'models/_pgsql/Usluga_model.php');

class Samara_Usluga_model extends Usluga_model {
	/**
	 * Comment
	 */
	function __construct() {
		parent::__construct();
	}  

	/**
	 *	Читает список услуг для нового комбо
	 *	@param array $data
	 *	@return bool|mixed
	 */
	function loadNewUslugaComplexList($data) {
		$filters = array();
		$joinList = array();
		$lpuFilters = array();
		$options = getOptions();
		$queryParams = array();
		$beforequery = "";

		// Загружаем конкретную запись
		if ( !empty($data['UslugaComplex_id']) ) {
			$filters[] = "uc.UslugaComplex_id = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		else {
			if ( !in_array($data['session']['region']['nick'], array( 'pskov', 'samara' )) ) {
				// Место выполнения услуги - отделение
				if ( !empty($data['LpuSection_id']) ) {
					$joinList[] = "
						left join lateral(
							select
								 ls.Lpu_id
								,lu.LpuBuilding_id
								,lu.LpuUnit_id
								,ls.LpuSection_id
							from v_LpuSection ls
								inner join LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
							where ls.LpuSection_id = :LpuSection_id
						) place on true
					";
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				// Место выполнения услуги - служба
				else if ( !empty($data['MedService_id']) ) {
					$joinList[] = "
						left join lateral(
							select
								 ms.Lpu_id
								,ms.LpuBuilding_id
								,ms.LpuUnit_id
								,ms.LpuSection_id
							from v_UslugaComplexMedService ucms
								inner join MedService ms on ms.MedService_id = ucms.MedService_id
							where ucms.UslugaComplex_id = uc.UslugaComplex_id
								and ms.MedService_id = :MedService_id
						) place on true
					";
					$queryParams['MedService_id'] = $data['MedService_id'];
				}
				// Иначе тянем все услуги ЛПУ
				else {
					if (empty($data['withoutLpuFilter'])) {
						$joinList[] = "inner join v_Lpu place on place.Lpu_id = uc.Lpu_id";
						$filters[] = "uc.Lpu_id = :Lpu_id";
						$queryParams['Lpu_id'] = (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']);
					} else {
						// вообще все услуги
						$joinList[] = "left join v_Lpu place on place.Lpu_id = uc.Lpu_id";
					}
				}

				if ( !empty($data['LpuSection_id']) || !empty($data['MedService_id']) ) {
					// https://redmine.swan.perm.ru/issues/10044
					// Необходимо учесть настройки!
					// Принадлежность к Lpu, LpuBuilding, LpuUnit, LpuSection определять через exists по таблицам UslugaComplexPlace и UslugaComplexTariff:
					// exists (
					//		select 1 from v_UslugaComplexPlace t1 where " . implode(' and ', $lpuFilters) . "
					//		union
					//		select 1 from v_UslugaComplexTariff t1 where " . implode(' and ', $lpuFilters) . "
					// )

					// Проверка настройки "Доступные услуги для выбора"
					switch ( $options['usluga']['allowed_usluga'] ) {
						// «1. Все» - все услуги указанных категорий.
						case 'all':
							//
						break;

						// «2. ЛПУ» - все услуги указанных категорий, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с Lpu_id места
						// посещения/движения. 
						case 'lpu':
							$lpuFilters[] = "t1.Lpu_id = place.Lpu_id";
						break;

						// «3. Подразделения» - все услуги указанных категорий, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с
						// LpuBuilding_id места посещения/движения. 
						case 'lpubuilding':
							$lpuFilters[] = "t1.LpuBuilding_id = place.LpuBuilding_id";
						break;

						// «4. Отделения» - все услуги указанных категорий, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с
						// LpuUnit_id места посещения/движения.
						case 'lpuunit':
							$lpuFilters[] = "t1.LpuUnit_id = place.LpuUnit_id";
						break;

						// «5. Отделения» - все услуги указанных категорий, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с
						// LpuSection_id места посещения/движения.
						case 'lpusection':
							$lpuFilters[] = "t1.LpuSection_id = place.LpuSection_id";
						break;

						default:
							// Прерывать выполнение метода?
							// return false;
						break;
					}

					if ( count($lpuFilters) > 0 ) {
						$lpuFilters[] = 't1.UslugaComplex_id = uc.UslugaComplex_id';

						// Фильтр по месту выполнения услуги
						$filters[] = "exists (
							(select t1.UslugaComplexPlace_id as id from v_UslugaComplexPlace t1 where " . implode(' and ', $lpuFilters) . " limit 1)
							union
							(select t1.UslugaComplexTariff_id as id from v_UslugaComplexTariff t1 where " . implode(' and ', $lpuFilters) . " limit 1)
						)";
					}
				}
			}
			else {
				// Фактически - костыль, чтобы в дальнейшем запрос не взрывался при связке по place.Lpu_id
				$joinList[] = "left join v_Lpu place on place.Lpu_id = :Lpu_id"; //GolovinAV
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}

			// Строка поиска
			if ( !empty($data['query']) ) {
				$queryParams['queryCode'] = $data['query'] . '%';
				$queryParams['queryName'] = '%'. $data['query'] . '%';
				$filters[] = "(cast(uc.UslugaComplex_Code as varchar(50)) ilike :queryCode or rtrim(coalesce(uc.UslugaComplex_Name, '')) ilike :queryName)";
			}

			// Категория услуги
			if ( !empty($data['UslugaCategory_id']) ) {
				$filters[] = "ucat.UslugaCategory_id = :UslugaCategory_id";
				$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
			}
			else if ( !empty($data['uslugaCategoryList']) ) {
				$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);
				//todo Проверить на правильность работы в соответствии с #10044
				if ( is_array($uslugaCategoryList) && count($uslugaCategoryList) > 0 ) {
					$withoutLpuFilter = array();
					$withLpuFilter = array();
					foreach($uslugaCategoryList as $uslugacategory_sysnick) {
						if(in_array($uslugacategory_sysnick,array('lpu','lpulabprofile')))
						{
							$withLpuFilter[] = $uslugacategory_sysnick;
						}
						else
						{
							$withoutLpuFilter[] = $uslugacategory_sysnick;
						}
					}
					if(empty($withoutLpuFilter))
					{
						$withoutLpuFilter_str = null;
					}
					else
					{
						$withoutLpuFilter_str = "ucat.UslugaCategory_SysNick in ('". implode("', '", $withoutLpuFilter) ."')";
					}
					if(empty($withLpuFilter))
					{
						$withLpuFilter_str = null;
					}
					else
					{
						$withLpuFilter_str = "(ucat.UslugaCategory_SysNick in ('". implode("', '", $withLpuFilter) ."') and uc.Lpu_id = place.Lpu_id)";
					}
					$filter = '(';
					$filter .= isset($withoutLpuFilter_str)?$withoutLpuFilter_str:'';
					$filter .= (isset($withoutLpuFilter_str) && isset($withLpuFilter_str))?' or ':'';
					$filter .= isset($withLpuFilter_str)?$withLpuFilter_str:'';
					$filter .= ')';
					$filters[] = $filter;					
					//$filters[] = "ucat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "')";
				}
				else {
					$filters[] = "(ucat.UslugaCategory_SysNick in ('gost2004', 'tfoms', 'promed', 'gost2011')
						or (UslugaCategory_SysNick = 'lpu' and uc.Lpu_id = place.Lpu_id))
					";
				}
			}

			if ( !empty($data['disallowedUslugaComplexAttributeList']) ) {
				$disallowedUslugaComplexAttributeList = json_decode($data['disallowedUslugaComplexAttributeList'], true);

				if ( is_array($disallowedUslugaComplexAttributeList) && count($disallowedUslugaComplexAttributeList) > 0 ) {
					$filters[] = "not exists (
						select t1.UslugaComplexAttribute_id
						from UslugaComplexAttribute t1
							inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = uc.UslugaComplex_id
							and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $disallowedUslugaComplexAttributeList) . "')
					)";
				}
			}

			// Дата актуальности услуги
			if ( !empty($data['UslugaComplex_Date']) ) {
				$filters[] = "cast(uc.UslugaComplex_begDT as date) <= cast(:UslugaComplex_Date as date)";
				$filters[] = "(uc.UslugaComplex_endDT is null or cast(uc.UslugaComplex_endDT as date) > cast(:UslugaComplex_Date as date))";
				$queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
			}

			// Идентификатор родительской услуги
			if ( !empty($data['UslugaComplex_pid']) ) {
				$filters[] = "uc.UslugaComplex_pid = :UslugaComplex_pid";
				$queryParams['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
			}
			else {
				$filters[] = "(
					uc.UslugaComplexLevel_id in (7, 8, 10)
					or (ucat.UslugaCategory_SysNick not in ('tfoms', 'pskov_foms', 'gost2004', 'gost2011') and uc.UslugaComplex_pid is null)
					or (ucat.UslugaCategory_SysNick in ('tfoms', 'pskov_foms') and uc.UslugaComplex_pid is not null)
				)";
			}

			// Это для Уфы
			if ( $data['session']['region']['nick'] == 'ufa' ) {
				// Коды посещений, соответствующие профилю отделения, и по неотложке
				if ( !empty($data['LpuLevel_Code']) ) {
					$smpUslugaComplexCodeList = array(511824, 511825, 512824, 512825, 563824, 563825, 564825, 564824, 611824, 611825, 612824, 612825,
						663824, 663825, 664824, 664825, 811824, 811825, 864824, 864825);

					$filters[] = "(cast(uc.UslugaComplex_Code as varchar(50)) ilike :LpuLevel_Code or cast(uc.UslugaComplex_Code as varchar(50)) in ('" . implode("', '", $smpUslugaComplexCodeList) . "'))";
					$queryParams['LpuLevel_Code'] = $data['LpuLevel_Code'] . "%";
				}

				// Только коды посещений по заболеваниям
				if ( !empty($data['allowMorbusVizitOnly']) ) {
					$filters[] = "(right(cast(uc.UslugaComplex_Code as varchar(50)), 3) in ('865', '866', '836'))";
				}

				// Коды посещений без профилактических посещений и посещений по заболеваниям
				if ( !empty($data['allowNonMorbusVizitOnly']) ) {
					$filters[] = "(right(cast(uc.UslugaComplex_Code as varchar(50)), 3) not in ('805', '811', '835', '865', '866', '836'))";
				}
			}
			
			// Фильтр по МЭС'ам для Самары
			if ($data['MesFilter_Enable'] == 1 && $data['session']['region']['nick'] == 'samara' && empty($data['query'])) {
				$beforequery = "
					with mv1 as (
						Select
							coalesce(ES.Diag_id, EV.Diag_id) as Diag_id,
							coalesce(ES.EvnSection_setDate, EV.EvnVizitPL_setDate) as Evn_setDate,
							ESL.LpuType_id,
							coalesce(ES.Person_id, EV.Person_id) as Person_id,
							LUT.LpuUnitType_Code as LpuUnitType
						from 
							v_Evn E
							left join v_EvnSection ES on E.Evn_id = ES.EvnSection_id
							left join v_EvnVizitPL EV on E.Evn_id = EV.EvnVizitPL_id
							left join v_LpuSection ESLS on coalesce(ES.LpuSection_id, EV.LpuSection_id) = ESLS.LpuSection_id
							left join v_Lpu ESL on ESL.Lpu_id = ESLS.Lpu_id
							left join v_LpuUnit LU on LU.LpuUnit_id = ESLS.LpuUnit_id
							left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
						where 
							EvnSection_id = :MesFilter_Evn_id
					), mv2 as (
						select
							DATEDIFF('YEAR', PS.Person_Birthday, (select Evn_setDate from mv1)) +
							case when date_part('month', PS.Person_Birthday) > date_part('month', (select Evn_setDate from mv1))
								or (date_part('month', PS.Person_Birthday) = date_part('month', (select Evn_setDate from mv1))
								and date_part('day', PS.Person_Birthday) > date_part('day', (select Evn_setDate from mv1)))
							then -1 else 0 end as Person_Age
						from
							v_PersonState PS
						where
							PS.Person_id = (select Person_id from mv1)
							and (select Evn_setDate from mv1) between PS.Person_Birthday and dbo.tzGetDate()
					)
				
				";
				// для Услуги должен существовать МЭС с фильтрацией как и в loadMesOldCombo для Самары из EvnSection_model.
				$filters[] = "(uc.Diag_id = (select Diag_id from mv1) or uc.Diag_id is null or uc.Diag_id = (select Diag_pid from v_Diag parentDiag where parentDiag.Diag_id = (select Diag_id from mv1)))
				and uc.MesOperType_id = :MesOperType_id
				and (:LpuSectionProfile_id = uc.LpuSectionProfile_id OR uc.LpuSectionProfile_id IS NULL)
				and :UslugaComplex_Date between uc.Mes_begDT and coalesce(uc.Mes_endDT, getdate())
				and (((select LpuUnitType from mv1) = 2 and uc.MedicalCareKind_Code in (1, 21)) or
							((select LpuUnitType from mv1) = 5 and uc.MedicalCareKind_Code = 6) or
							((select LpuUnitType from mv1) = 3 and uc.MedicalCareKind_Code = 7) or
							((select LpuUnitType from mv1) = 4 and uc.MedicalCareKind_Code = 21))
				and (
							((select LpuType_id from mv1) in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and (select Person_Age from mv2) < 18 and uc.MesAgeGroup_id = 2) or 
							((select LpuType_id from mv1) not in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and
							((select Person_Age from mv2) < 15 and uc.MesAgeGroup_id = 2) or ((select Person_Age from mv2) >= 15 and uc.MesAgeGroup_id = 1)) or
							(MesAgeGroup_id IS NULL) or
							uc.MesAgeGroup_id = 3
						)
						
						
						and	(select Person_Age from mv2) is not null
						and uc.Mes_begDT is not null
						and uc.Mes_begDT <= (select Evn_setDate from mv1) 
						and (uc.Mes_endDT is null or uc.Mes_endDT > (select Evn_setDate from mv1))";
				
				$queryParams['MesFilter_Evn_id'] = $data['MesFilter_Evn_id'];
				$queryParams['MesOperType_id'] = $data['MesOperTypeList'];
				$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];				
			}

			// Для Перми - фильтрация по МЭС
			// https://redmine.swan.perm.ru/issues/15931
			if ( $data['session']['region']['nick'] == 'perm' ) {
				if ( !empty($data['Mes_id']) ) {
					$joinList[] = "inner join v_MesUsluga MesUsluga on MesUsluga.UslugaComplex_id = uc.UslugaComplex_2011id and MesUsluga.Mes_id = :Mes_id";
					$queryParams['Mes_id'] = $data['Mes_id'];
				}
			}
		}
		
		$filters[] = "(uc.UslugaComplex_endDT IS NULL OR uc.UslugaComplex_endDT >= dbo.tzGetDate())";

		// https://redmine.swan.perm.ru/issues/16141
		if ( $data['session']['region']['nick'] == 'perm' && empty($data['UslugaComplex_id']) && empty($data['Mes_id']) && !empty($data['LpuSection_id'])
			&& isset($allowedUslugaComplexAttributeList) && in_array('stom', $allowedUslugaComplexAttributeList)
		) {
			$query = "
				select
					 uc.UslugaComplex_id as \"UslugaComplex_id\"
					,ucat.UslugaCategory_id as \"UslugaCategory_id\"
					,coalesce(ucat.UslugaCategory_Name, '') as \"UslugaCategory_Name\"
					,uc.UslugaComplex_pid as \"UslugaComplex_pid\"
					,to_char(uc.UslugaComplex_begDT, 'dd.mm.yyyy') as \"UslugaComplex_begDT\"
					,to_char(uc.UslugaComplex_endDT, 'dd.mm.yyyy') as \"UslugaComplex_endDT\"
					,u.Usluga_Code as \"UslugaComplex_Code\"
					,RTRIM(u.Usluga_Name) as \"UslugaComplex_Name\"
					,ROUND(coalesce(uc.UslugaComplex_UET, 0), 2) as \"UslugaComplex_UET\"
					,null as \"FedUslugaComplex_id\"
				from v_UslugaComplex uc
					inner join Usluga U on U.Usluga_id = UC.Usluga_id
					left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
				where (1 = 1)
					and uc.LpuSection_id = :LpuSection_id
			";

			if ( !empty($data['UslugaComplex_Date']) ) {
				$query .= "
					and cast(uc.UslugaComplex_begDT as date) <= cast(:UslugaComplex_Date as date)
					and (uc.UslugaComplex_endDT is null or cast(uc.UslugaComplex_endDT as date) > cast(:UslugaComplex_Date as date))
				";
				$queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
			}

			if ( !empty($data['query']) ) {
				$queryParams['queryCode'] = $data['query'] . '%';
				$queryParams['queryName'] = '%'. $data['query'] . '%';
				$queryParams['MesOperType_id'] = $data['MesOperTypeList'];				
				$query .= "and (cast(uc.UslugaComplex_Code as varchar(50)) ilike :queryCode or rtrim(coalesce(uc.UslugaComplex_Name, '')) ilike :queryName)";
			}
		}
		else {
			if($data['MesFilter_Enable'] == 1 && $data['session']['region']['nick'] == 'samara' && !empty($data['uslugaCategoryList'])  && $data['uslugaCategoryList'] == '["ksg"]') {
				$query = "
					{$beforequery}
					
					select
						 uc.UslugaComplex_id as \"UslugaComplex_id\"
						,ucat.UslugaCategory_id as \"UslugaCategory_id\"
						,ucat.UslugaCategory_Name as \"UslugaCategory_Name\"
						,uc.UslugaComplex_pid as \"UslugaComplex_pid\"
						,to_char(uc.UslugaComplex_begDT, 'dd.mm.yyyy') as \"UslugaComplex_begDT\"
						,to_char(uc.UslugaComplex_endDT, 'dd.mm.yyyy') as \"UslugaComplex_endDT\"
						,uc.UslugaComplex_Code as \"UslugaComplex_Code\"
						,rtrim(coalesce(uc.UslugaComplex_Name, '')) as \"UslugaComplex_Name\"
						,coalesce(" . ($data['session']['region']['nick'] == 'perm' && !empty($data['Mes_id']) ? "MesUsluga.MesUsluga_UslugaCount" : "null") . ", 0) as \"UslugaComplex_UET\"
						,c.UslugaComplex_pid AS \"FedUslugaComplex_id\"
						, uc.Mes_Koikodni as \"Mes_Koikodni\"
						, uc.LpuSectionProfile_Code as \"LpuSectionProfile\"
						--, coalesce(uc.MedicalCareKind_ShortName, uc.MedicalCareKind_Name) as \"MedicalCareKind_Name\"
						, uc.MedicalCareKind_Name as \"MedicalCareKind_Name\"
						, uc.MesOperType_Name as \"MesOperType_Name\"
						, coalesce(uc.Diag_Code, '') as \"Diag_Code\"
					from
						v_MesKsgUsluga uc
						left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
						LEFT JOIN (SELECT * FROM dbo.v_UslugaComplexComposition WHERE UslugaComplexCompositionType_id = 2) c ON uc.UslugaComplex_id = c.UslugaComplex_id															
						" . implode(' ', $joinList) . "
					where
						" . implode(' and ', $filters) . "
					order by
						uc.UslugaComplex_Code
					 limit 500
				";				
			}
			else {
				$query = "
					{$beforequery}
					select
						 uc.UslugaComplex_id as \"UslugaComplex_id\"
						,ucat.UslugaCategory_id as \"UslugaCategory_id\"
						,ucat.UslugaCategory_Name as \"UslugaCategory_Name\"
						,uc.UslugaComplex_pid as \"UslugaComplex_pid\"
						,to_char(uc.UslugaComplex_begDT, 'dd.mm.yyyy') as \"UslugaComplex_begDT\"
						,to_char(uc.UslugaComplex_endDT, 'dd.mm.yyyy') as \"UslugaComplex_endDT\"
						,uc.UslugaComplex_Code as \"UslugaComplex_Code\"
						,rtrim(coalesce(uc.UslugaComplex_Name, '')) as \"UslugaComplex_Name\"
						,coalesce(" . ($data['session']['region']['nick'] == 'perm' && !empty($data['Mes_id']) ? "MesUsluga.MesUsluga_UslugaCount" : "null") . ", 0) as \"UslugaComplex_UET\"
						,c.UslugaComplex_pid AS \"FedUslugaComplex_id	\"
						,'' as \"Mes_Koikodni\"
						,'' as \"LpuSectionProfile\"
						,'' as \"MedicalCareKind_Name\"
						,'' as \"MesOperType_Name\"
					from
						v_UslugaComplex uc
						left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
						LEFT JOIN (SELECT * FROM dbo.v_UslugaComplexComposition WHERE UslugaComplexCompositionType_id = 2) c ON uc.UslugaComplex_id = c.UslugaComplex_id					
						" . implode(' ', $joinList) . "
					where
						" . implode(' and ', $filters) . "
					order by
						uc.UslugaComplex_Code
					limit 500
				";
			}
		}

		// echo getDebugSql($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
