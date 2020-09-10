<?php

require_once(APPPATH.'models/Usluga_model.php');

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
						outer apply (
							select
								 ls.Lpu_id
								,lu.LpuBuilding_id
								,lu.LpuUnit_id
								,ls.LpuSection_id
							from v_LpuSection ls with (nolock)
								inner join LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
							where ls.LpuSection_id = :LpuSection_id
						) place
					";
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				// Место выполнения услуги - служба
				else if ( !empty($data['MedService_id']) ) {
					$joinList[] = "
						outer apply (
							select
								 ms.Lpu_id
								,ms.LpuBuilding_id
								,ms.LpuUnit_id
								,ms.LpuSection_id
							from v_UslugaComplexMedService ucms with (nolock)
								inner join MedService ms with (nolock) on ms.MedService_id = ucms.MedService_id
							where ucms.UslugaComplex_id = uc.UslugaComplex_id
								and ms.MedService_id = :MedService_id
						) place
					";
					$queryParams['MedService_id'] = $data['MedService_id'];
				}
				// Иначе тянем все услуги ЛПУ
				else {
					if (empty($data['withoutLpuFilter'])) {
						$joinList[] = "inner join v_Lpu place with (nolock) on place.Lpu_id = uc.Lpu_id";
						$filters[] = "uc.Lpu_id = :Lpu_id";
						$queryParams['Lpu_id'] = (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']);
					} else {
						// вообще все услуги
						$joinList[] = "left join v_Lpu place with (nolock) on place.Lpu_id = uc.Lpu_id";
					}
				}

				if ( !empty($data['LpuSection_id']) || !empty($data['MedService_id']) ) {
					// https://redmine.swan.perm.ru/issues/10044
					// Необходимо учесть настройки!
					// Принадлежность к Lpu, LpuBuilding, LpuUnit, LpuSection определять через exists по таблицам UslugaComplexPlace и UslugaComplexTariff:
					// exists (
					//		select 1 from v_UslugaComplexPlace t1 with (nolock) where " . implode(' and ', $lpuFilters) . "
					//		union
					//		select 1 from v_UslugaComplexTariff t1 with (nolock) where " . implode(' and ', $lpuFilters) . "
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
							select top 1 t1.UslugaComplexPlace_id as id from v_UslugaComplexPlace t1 with (nolock) where " . implode(' and ', $lpuFilters) . "
							union
							select top 1 t1.UslugaComplexTariff_id as id from v_UslugaComplexTariff t1 with (nolock) where " . implode(' and ', $lpuFilters) . "
						)";
					}
				}
			}
			else {
				// Фактически - костыль, чтобы в дальнейшем запрос не взрывался при связке по place.Lpu_id
				$joinList[] = "left join v_Lpu place with (nolock) on place.Lpu_id = :Lpu_id"; //GolovinAV
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}

			// Строка поиска
			if ( !empty($data['query']) ) {
				$queryParams['queryCode'] = $data['query'] . '%';
				$queryParams['queryName'] = '%'. $data['query'] . '%';
				$filters[] = "(cast(uc.UslugaComplex_Code as varchar(50)) like :queryCode or rtrim(isnull(uc.UslugaComplex_Name, '')) like :queryName)";
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

			/*   GolovinAV
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
			*/
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

					$filters[] = "(cast(uc.UslugaComplex_Code as varchar(50)) like :LpuLevel_Code or cast(uc.UslugaComplex_Code as varchar(50)) in ('" . implode("', '", $smpUslugaComplexCodeList) . "'))";
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
					Declare
						@Diag_id bigint,
						--@LpuSectionProfile_id bigint,
						@Person_Age int,
						@Evn_setDate datetime,
						@Person_id bigint,
						@LpuType_id bigint,
						@LpuUnitType int;
						
						Select
							@Diag_id = ISNULL(ES.Diag_id, EV.Diag_id),
							--@LpuSectionProfile_id = ESLS.LpuSectionProfile_id,
							@Evn_setDate = ISNULL(ES.EvnSection_setDate, EV.EvnVizitPL_setDate),
							@LpuType_id = ESL.LpuType_id,
							@Person_id = ISNULL(ES.Person_id, EV.Person_id),
							@LpuUnitType = LUT.LpuUnitType_Code
						from 
							v_Evn E with (nolock)
								left join v_EvnSection ES with (nolock) on E.Evn_id = ES.EvnSection_id
								left join v_EvnVizitPL EV with (nolock) on E.Evn_id = EV.EvnVizitPL_id
								left join v_LpuSection ESLS with (nolock) on ISNULL(ES.LpuSection_id, EV.LpuSection_id) = ESLS.LpuSection_id
								left join v_Lpu ESL with (nolock) on ESL.Lpu_id = ESLS.Lpu_id
								left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = ESLS.LpuUnit_id
								left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
						where 
							EvnSection_id = :MesFilter_Evn_id
							
						select
							@Person_Age = (DATEDIFF(YEAR, PS.Person_Birthday, cast(@Evn_setDate as datetime)) +
								case when MONTH(PS.Person_Birthday) > MONTH(cast(@Evn_setDate as datetime))
									or (MONTH(PS.Person_Birthday) = MONTH(cast(@Evn_setDate as datetime)) and DAY(PS.Person_Birthday) > DAY(cast(@Evn_setDate as datetime)))
								then -1 else 0 end
							)
						from
							v_PersonState PS
						where
							PS.Person_id = @Person_id
							and cast(@Evn_setDate as datetime) between PS.Person_Birthday and dbo.tzGetDate()
				
				";
				// для Услуги должен существовать МЭС с фильтрацией как и в loadMesOldCombo для Самары из EvnSection_model.
				$filters[] = "(uc.Diag_id = @Diag_id or uc.Diag_id is null or uc.Diag_id = (select Diag_pid from v_Diag parentDiag where parentDiag.Diag_id = @Diag_id))
				and uc.MesOperType_id = :MesOperType_id
				and (:LpuSectionProfile_id = uc.LpuSectionProfile_id OR uc.LpuSectionProfile_id IS NULL)
				and :UslugaComplex_Date between uc.Mes_begDT and ISNULL(uc.Mes_endDT, getdate())
				and ((@LpuUnitType = 2 and uc.MedicalCareKind_Code in (1, 21)) or
							(@LpuUnitType = 5 and uc.MedicalCareKind_Code = 6) or
							(@LpuUnitType = 3 and uc.MedicalCareKind_Code = 7) or
							(@LpuUnitType = 4 and uc.MedicalCareKind_Code = 21))
				and (
							(@LpuType_id in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and @Person_Age < 18 and uc.MesAgeGroup_id = 2) or 
							(@LpuType_id not in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and
							(@Person_Age < 15 and uc.MesAgeGroup_id = 2) or (@Person_Age >= 15 and uc.MesAgeGroup_id = 1)) or
							(MesAgeGroup_id IS NULL) or
							uc.MesAgeGroup_id = 3
						)
						
						
						and	@Person_Age is not null
						and uc.Mes_begDT is not null
						and uc.Mes_begDT <= cast(@Evn_setDate as datetime) 
						and (uc.Mes_endDT is null or uc.Mes_endDT > cast(@Evn_setDate as datetime))"; 
				
				
				/*"exists (
					select top 1
						m.Mes_id
					from v_MesOld m with (nolock)
						left join v_MesUsluga mu with (nolock) on mu.Mes_id = m.Mes_id
						left join V_MedicalCareKind mck with (nolock) on mck.MedicalCareKind_id = m.MedicalCareKind_id
					where
						mu.UslugaComplex_id = uc.UslugaComplex_id
						--1. Выборка по диагнозу
						and (m.Diag_id = @Diag_id or m.Diag_id is null or m.Diag_id = (select Diag_pid from v_Diag parentDiag where parentDiag.Diag_id = @Diag_id))
						--2. Выборка по профилю отделения
						and (:LpuSectionProfile_id = m.LpuSectionProfile_id OR m.LpuSectionProfile_id IS NULL)
						--3. Выборка по виду медицинской помощи
						and ((@LpuUnitType = 2 and mck.MedicalCareKind_Code in (1, 21)) or
							(@LpuUnitType = 5 and mck.MedicalCareKind_Code = 6) or
							(@LpuUnitType = 3 and mck.MedicalCareKind_Code = 7) or
							(@LpuUnitType = 4 and mck.MedicalCareKind_Code = 21))
						--4. по виду лечения
						and m.MesOperType_id = :MesOperType_id
						and	@Person_Age is not null
						and m.Mes_begDT is not null
						and m.Mes_begDT <= cast(@Evn_setDate as datetime) 
						and (m.Mes_endDT is null or m.Mes_endDT > cast(@Evn_setDate as datetime))
						--5. Выборка по возрасту
						and (
							(@LpuType_id in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and @Person_Age < 18 and m.MesAgeGroup_id = 2) or 
							(@LpuType_id not in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and
							(@Person_Age < 15 and m.MesAgeGroup_id = 2) or (@Person_Age >= 15 and m.MesAgeGroup_id = 1)) or
							(MesAgeGroup_id IS NULL) or
							m.MesAgeGroup_id = 3
						)
				)";*/
				
				$queryParams['MesFilter_Evn_id'] = $data['MesFilter_Evn_id'];
				$queryParams['MesOperType_id'] = $data['MesOperTypeList'];
				$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];				
			}

			// Для Перми - фильтрация по МЭС
			// https://redmine.swan.perm.ru/issues/15931
			if ( $data['session']['region']['nick'] == 'perm' ) {
				if ( !empty($data['Mes_id']) ) {
					$joinList[] = "inner join v_MesUsluga MesUsluga with (nolock) on MesUsluga.UslugaComplex_id = uc.UslugaComplex_2011id and MesUsluga.Mes_id = :Mes_id";
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
					 uc.UslugaComplex_id
					,ucat.UslugaCategory_id
					,ISNULL(ucat.UslugaCategory_Name, '') as UslugaCategory_Name
					,uc.UslugaComplex_pid
					,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
					,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
					,u.Usluga_Code as UslugaComplex_Code
					,RTRIM(u.Usluga_Name) as UslugaComplex_Name
					,ROUND(ISNULL(uc.UslugaComplex_UET, 0), 2) as UslugaComplex_UET
					,null as FedUslugaComplex_id
				from v_UslugaComplex uc with (nolock)
					inner join Usluga U with (nolock) on U.Usluga_id = UC.Usluga_id
					left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
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
				$query .= "and (cast(uc.UslugaComplex_Code as varchar(50)) like :queryCode or rtrim(isnull(uc.UslugaComplex_Name, '')) like :queryName)";
			}
		}
		else {
			if($data['MesFilter_Enable'] == 1 && $data['session']['region']['nick'] == 'samara' && !empty($data['uslugaCategoryList'])  && $data['uslugaCategoryList'] == '["ksg"]') {
				$query = "
					{$beforequery}
					
					select top 500
						 uc.UslugaComplex_id
						,ucat.UslugaCategory_id
						,ucat.UslugaCategory_Name
						,uc.UslugaComplex_pid
						,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
						,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
						,uc.UslugaComplex_Code
						,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
						,ISNULL(" . ($data['session']['region']['nick'] == 'perm' && !empty($data['Mes_id']) ? "MesUsluga.MesUsluga_UslugaCount" : "null") . ", 0) as UslugaComplex_UET
						,c.UslugaComplex_pid AS FedUslugaComplex_id					
						, uc.Mes_Koikodni
						, uc.LpuSectionProfile_Code as LpuSectionProfile
						--, isnull(uc.MedicalCareKind_ShortName, uc.MedicalCareKind_Name) as MedicalCareKind_Name
						, uc.MedicalCareKind_Name
						, uc.MesOperType_Name	
						, isnull(uc.Diag_Code, '') Diag_Code						
					from
						v_MesKsgUsluga uc with (nolock)
						left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
						LEFT JOIN (SELECT * FROM dbo.v_UslugaComplexComposition with (nolock) WHERE UslugaComplexCompositionType_id = 2) c ON uc.UslugaComplex_id = c.UslugaComplex_id															
						" . implode(' ', $joinList) . "
					where
						" . implode(' and ', $filters) . "
					order by
						uc.UslugaComplex_Code
				";				
			}
			else {
				$query = "
					{$beforequery}
					select top 500
						 uc.UslugaComplex_id
						,ucat.UslugaCategory_id
						,ucat.UslugaCategory_Name
						,uc.UslugaComplex_pid
						,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
						,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
						,uc.UslugaComplex_Code
						,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
						,ISNULL(" . ($data['session']['region']['nick'] == 'perm' && !empty($data['Mes_id']) ? "MesUsluga.MesUsluga_UslugaCount" : "null") . ", 0) as UslugaComplex_UET
						,c.UslugaComplex_pid AS FedUslugaComplex_id	
						,'' as Mes_Koikodni
						,'' as LpuSectionProfile
						,'' as MedicalCareKind_Name
						,'' as MesOperType_Name
					from
						v_UslugaComplex uc with (nolock)
						left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
						LEFT JOIN (SELECT * FROM dbo.v_UslugaComplexComposition with (nolock) WHERE UslugaComplexCompositionType_id = 2) c ON uc.UslugaComplex_id = c.UslugaComplex_id					
						" . implode(' ', $joinList) . "
					where
						" . implode(' and ', $filters) . "
					order by
						uc.UslugaComplex_Code
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
?>