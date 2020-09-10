<?php
//namespace Sw\promed\libraries\sql;
/**
 * Набор классов для динамического создания запроса списка услуг для назначений
 * в зависимости от фильтров и параметров сортировки
 * Сделано так для удобства внедрения оптимизаций этого запроса
 */

abstract class SqlData {
	static $select = array();
	static $from = array();
	static $filters = array();
	static protected $orderBy = '';
	/**
	 * @param $value
	 */
	static function setOrderBy($value) {
		self::$orderBy = $value;
	}
	/**
	 * @return string
	 */
	static function getOrderBy($isLab = false) {
		return self::$orderBy;
	}
}
/*
class PzmSqlData extends SqlData {
	static $filters = array(
		'filterLab'=>'msl.MedService_lid = ms.MedService_id and msl.MedServiceLinkType_id = 1',
	);
	static $select = array(
		'MedService_id'=>'pzm.MedService_id',
		'Lpu_id'=>'pzm.Lpu_id',
		'LpuBuilding_id'=>'pzm.LpuBuilding_id',
		'LpuUnit_id'=>'pzm.LpuUnit_id',
		'LpuSection_id'=>'pzm.LpuSection_id',
		'MedServiceType_id'=>'pzm.MedServiceType_id',
		'MedService_Name'=>'pzm.MedService_Name',
		'MedService_Nick'=>'pzm.MedService_Nick',
		'UslugaComplexMedService_id'=>'null as UslugaComplexMedService_id'//override after gen sql for count
	);
	static $from = array(
		'msl'=>'v_MedServiceLink msl',
		'pzm'=>'inner join v_MedService pzm on pzm.MedServiceType_id = 7 and msl.MedService_id = pzm.MedService_id and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > (select cur_date from vars))',
		//add ucpzm and ttms after gen sql for count
	);
}*/

/**
 * Запрос может быть разным в зависимости от фильтров
 */
class AllTtmsSqlData extends SqlData
{
	static $from = array(
		'ttms'=>'v_TimetableMedService_lite ttms'
	);
	static $filters = array(
		'free'=>'ttms.Person_id is null',
		'lowerLimitTime'=>'ttms.TimetableMedService_begTime >= (select cur_dt from vars)',
		'upperLimitTime'=>'ttms.TimetableMedService_begTime < (select upper_dt from vars)'
	);
}

/**
 * Конструктор запроса списка услуг для создания назначения-направления
 * Запрос может быть разнаым в зависимости от параметров сортировки и фильтров
 */
class UslugaComplex2011SqlData extends SqlData  {
	static $UslugaComplexMedServiceKeyField = "mUslugaComplexMedService_id";
	static $MedServiceDisplayField = "dd.MedService_Nick";//override for lab
	static $TimetableBegTimeField = "TimetableMedService_begTime";//override for lab

	static $select = array(
		'UslugaComplex_2011id'=>'uc11.UslugaComplex_id as UslugaComplex_2011id',
		'UslugaComplex_msid'=>'uc.UslugaComplex_id as UslugaComplex_msid',
		'UslugaComplex_id'=>'coalesce(uc.UslugaComplex_id, uc11.UslugaComplex_id) as UslugaComplex_id',
		'UslugaComplex_Code'=>'coalesce(uc.UslugaComplex_Code, uc11.UslugaComplex_Code) as UslugaComplex_Code',
		'UslugaComplex_Name'=>'coalesce(ucms.UslugaComplex_Name, uc.UslugaComplex_Name, uc11.UslugaComplex_Name) as UslugaComplex_Name',
		'UslugaComplex_AttributeList'=>'uca.UslugaComplexAttributeType_SysNick as UslugaComplex_AttributeList',
		'UslugaCategory_id'=>'uc.UslugaCategory_id as UslugaCategory_id',
		'MedService_cnt'=>'0 as MedService_cnt',//override
		'RecordQueue_id'=>'mss.RecordQueue_id',
		'MedService_id'=>'mss.MedService_id as MedService_id',
		'UslugaComplexMedService_id'=>'ucms.UslugaComplexMedService_id as UslugaComplexMedService_id',
		'MedService_Name'=>'mss.MedService_Name as MedService_Name',
		'MedService_Nick'=>'mss.MedService_Nick as MedService_Nick',
		'MedServiceType_id'=>'mst.MedServiceType_id as MedServiceType_id',
		'MedServiceType_SysNick'=>'mst.MedServiceType_SysNick as MedServiceType_SysNick',
		'Lpu_id'=>'mss.Lpu_id as Lpu_id',
		'Lpu_Nick'=>'mss.Lpu_Nick as Lpu_Nick',
		'LpuBuilding_id'=>'lu.LpuBuilding_id as LpuBuilding_id',
		'LpuBuilding_Name'=>'lu.LpuBuilding_Name as LpuBuilding_Name',
		'LpuUnit_id'=>'lu.LpuUnit_id as LpuUnit_id',
		'LpuUnit_Name'=>'lu.LpuUnit_Name as LpuUnit_Name',
		'LpuUnit_Address'=>'lua.Address_Address as LpuUnit_Address',
		'LpuUnitType_id'=>'lu.LpuUnitType_id as LpuUnitType_id',
		'LpuUnitType_SysNick'=>'lu.LpuUnitType_SysNick as LpuUnitType_SysNick',
		'LpuSection_id'=>'ls.LpuSection_id as LpuSection_id',
		'LpuSection_Name'=>'ls.LpuSection_Name as LpuSection_Name',
		'LpuSectionProfile_id'=>'ls.LpuSectionProfile_id as LpuSectionProfile_id',
		'LpuSectionProfile_Name'=>'ls.LpuSectionProfile_Name as LpuSectionProfile_Name',
		'isComposite'=>'0 as isComposite',//override for lab
		//'lab_MedService_id'=>'null as lab_MedService_id',//override for lab
		//'pzm_MedService_id'=>'null as pzm_MedService_id',//override for lab
		//'pzm_UslugaComplexMedService_id'=>'null as pzm_UslugaComplexMedService_id',//override for lab
		//'pzm_Lpu_id'=>'null as pzm_Lpu_id',//override for lab
		//'pzm_MedServiceType_id'=>'null as pzm_MedServiceType_id',//override for lab
		//'pzm_MedServiceType_SysNick'=>'null as pzm_MedServiceType_SysNick',//override for lab
		//'pzm_MedService_Name'=>'null as pzm_MedService_Name',//override for lab
		//'pzm_MedService_Nick'=>'null as pzm_MedService_Nick',//override for lab
		'withResource'=>'0 as withResource',//override for lab
		'ElectronicQueueInfo_id' => 'eq.ElectronicQueueInfo_id as ElectronicQueueInfo_id',
		'ElectronicService_id' => 'eq.ElectronicService_id as ElectronicService_id',
		'composition_cnt' => 'composition.cnt as composition_cnt',
		//'Resource_id' => 'null as pzm_MedService_id',
		//'Resource_Name' => 'null as pzm_MedService_Name'
	);
	// Поиск услуг осуществляется по всем доступным категориям. (Не только ГОСТ 2011)
	static $from = array(
		'uc'=>'v_UslugaComplex uc',
		'ucms'=>'left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id',
		'mss'=>'left join mss on mss.MedService_id = ucms.MedService_id',
		'msl'=>'left join v_MedServiceLink msl on msl.MedService_lid = mss.MedService_id and msl.MedServiceLinkType_id = 1',
		'pzm'=>'left join v_MedService pzm on pzm.MedServiceType_id = 7
			and msl.MedService_id = pzm.MedService_id
			and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > (select cur_date from vars))
			and (pzm.Lpu_id = :Lpu_id or (pzm.Lpu_id != :Lpu_id and coalesce(pzm.MedService_IsThisLPU, 1) != 2))',
		'ucpzm'=>'left join v_UslugaComplexMedService ucpzm on ucpzm.MedService_id = pzm.MedService_id and ucpzm.UslugaComplex_id = uc.UslugaComplex_id',
		'ucres'=>'left join v_UslugaComplexResource ucres on ucres.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id',
		'res'=>'left join v_Resource res on res.Resource_id = ucres.Resource_id and (res.Resource_endDT is null OR cast(res.Resource_endDT as date) > (select cur_date from vars))',
		'mst'=>'left join v_MedServiceType mst on mss.MedServiceType_id = mst.MedServiceType_id',
		//'l'=>'left join v_Lpu l on mss.Lpu_id = l.Lpu_id',
		'lu'=>'left join v_LpuUnit lu on lu.LpuUnit_id = mss.LpuUnit_id',
		'ls'=>'left join v_LpuSection ls on mss.LpuSection_id = ls.LpuSection_id',
		'lua'=>'left join v_Address lua on lua.Address_id = coalesce(lu.Address_id,mss.UAddress_id)',
		'uc11'=>'left join lateral ( Select * from v_UslugaComplex uc11 where uc.UslugaComplex_2011id = uc11.UslugaComplex_id limit 1 ) uc11 on true',
		'eq'=>'left join lateral (
			select
				eqi.ElectronicQueueInfo_id,
				mseq.ElectronicService_id
			from
				v_ElectronicQueueInfo eqi
				inner join v_MedServiceElectronicQueue mseq on mseq.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
			where
				 eqi.MedService_id = mss.MedService_id
				 and eqi.ElectronicQueueInfo_IsOff = 1
			limit 1
		) eq on true',
		'composition'=>'left join lateral (
			select COUNT(ucoa.UslugaComplex_id) as cnt
			from v_UslugaComplexMedService ucmsoa
			inner join v_UslugaComplex ucoa on ucmsoa.UslugaComplex_id = ucoa.UslugaComplex_id
			where ucmsoa.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
		) composition on true',
		'uca'=>'left join lateral (
			select string_agg(t2.UslugaComplexAttributeType_SysNick, \',\') as UslugaComplexAttributeType_SysNick
			from v_UslugaComplexAttribute t1
			inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
			where t1.UslugaComplex_id = uc.UslugaComplex_id	
		) uca on true',
		// add pzm for lab
	);
	static $filters = array(
		'filterByUslugaComplex'=>'uc.UslugaComplex_id is not null',// чтобы при поиске по услуге фильтр был первым
		'filterByBegDate'=>'uc.UslugaComplex_begDT <= (select cur_date from vars)',
		'filterByEndDate'=>'coalesce(uc.UslugaComplex_endDT, (select cur_date from vars)) >= (select cur_date from vars)',
		'filterWithoutNoprescr'=>"not exists (
	select
		t1.UslugaComplexAttribute_id
	from v_UslugaComplexAttribute t1
		inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
	where t1.UslugaComplex_id = coalesce(uc11.UslugaComplex_id, uc.UslugaComplex_id)
		and t2.UslugaComplexAttributeType_SysNick in ('noprescr')
)",
		'filterIsThisLPU'=>'not exists( select * from v_medservice mslo where mslo.MedService_id = mss.MedService_id and mslo.MedService_IsThisLPU = 2 and mslo.lpu_id != :Lpu_id)',
	);
}


/**
 * Конструктор запроса списка услуг для создания назначения-направления
 * Запрос может быть разным в зависимости от параметров сортировки и фильтров
 */
class UslugaComplexSelectListSqlBuilder {
	public static $error = '';
	private static $_options = null;
	private static $params = array();
	/**
	 * Признак, что строим запрос списка лаб.услуг,
	 * у которых в качесте места оказания не лаборатория, а пункт забора,
	 * а также есть другие особенности
	 * @var bool
	 */
	public static $isLab = false;
	/**
	 * Признак, что строим запрос списка услуг инструментальной диагностики,
	 * у которых в качесте места оказания не лаборатория, а ресурс,
	 * а также есть другие особенности
	 * @var bool
	 */
	public static $isFunc = false;
	/**
	 * Необходимость подгружать расписание
	 * @var string
	 */
	private static $needTimetable = true;
	public static $withResource = false;
	// Тип формы
	public static $formMode = 'ExtJS2';

	/**
	 * Устанавливает параметры запроса с учетом специфики служб
	 */
	private static function setSqlDataSpecific() {
		if (self::$isLab) {
			// фильтр на лаборатории и состав услуг
			/*$whereHasAnalyzerTestTpl = "exists(
				select top 1
					Analyzer.Analyzer_id
				from
					lis.v_AnalyzerTest AnalyzerTest
					inner join lis.v_Analyzer Analyzer on Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
				where
					AnalyzerTest.UslugaComplexMedService_id = {ucms_id}
					and coalesce(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1
					and coalesce(Analyzer.Analyzer_IsNotActive, 1) = 1
			)";
			UslugaComplex2011SqlData::$filters['filterIsHasAnalyzerTest'] = strtr($whereHasAnalyzerTestTpl,array('{ucms_id}'=>'ucms.UslugaComplexMedService_id'));*/
			$fromHasAnalyzerTestTpl = "
				left join lateral (
					select
						Analyzer.Analyzer_id,
						AnalyzerTest_Name
					from
						lis.v_AnalyzerTest AnalyzerTest
						inner join lis.v_Analyzer Analyzer on Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
					where
						AnalyzerTest.UslugaComplexMedService_id = {ucms_id}
						and coalesce(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1
						and coalesce(Analyzer.Analyzer_IsNotActive, 1) = 1
					limit 1
				) ATT on true
			";
			UslugaComplex2011SqlData::$from['fromHasAnalyzerTest'] = strtr($fromHasAnalyzerTestTpl,array('{ucms_id}'=>'ucms.UslugaComplexMedService_id'));

			UslugaComplex2011SqlData::$MedServiceDisplayField = 'coalesce(pzm_MedService_Nick,dd.MedService_Nick)';
			UslugaComplex2011SqlData::$select['lab_MedService_id']='mss.MedService_id as lab_MedService_id';
			if (self::$formMode != 'ExtJS6') {
				UslugaComplex2011SqlData::$select['isComposite'] = '
					case
						when mss.MedService_id is not null and exists(
							select
								*
							from
								v_UslugaComplexMedService
								' . strtr($fromHasAnalyzerTestTpl, array('{ucms_id}' => 'v_UslugaComplexMedService.UslugaComplexMedService_id')) . '
							where
								UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
						) then 1
						when mss.MedService_id is null and exists(
							select
								*
							from
								v_UslugaComplexComposition
							where
								UslugaComplex_pid = uc11.UslugaComplex_id
						) then 1
						else 0
					end as isComposite
				';
			}
			UslugaComplex2011SqlData::$select['pzm_MedService_id']='pzm.MedService_id as pzm_MedService_id';
			UslugaComplex2011SqlData::$select['pzm_UslugaComplexMedService_id']='ucpzm.UslugaComplexMedService_id as pzm_UslugaComplexMedService_id';
			UslugaComplex2011SqlData::$select['pzm_Lpu_id']='pzm.Lpu_id as pzm_Lpu_id';
			UslugaComplex2011SqlData::$select['pzm_RecordQueue_id']='pzm.RecordQueue_id as pzm_RecordQueue_id';
			UslugaComplex2011SqlData::$select['pzm_MedServiceType_id']='pzm.MedServiceType_id as pzm_MedServiceType_id';
			UslugaComplex2011SqlData::$select['pzm_MedServiceType_SysNick']="'pzm' as pzm_MedServiceType_SysNick";
			UslugaComplex2011SqlData::$select['pzm_MedService_Name']='pzm.MedService_Name as pzm_MedService_Name';
			UslugaComplex2011SqlData::$select['pzm_MedService_Nick']='pzm.MedService_Nick as pzm_MedService_Nick';
			UslugaComplex2011SqlData::$select['at_AnalyzerTest_Name'] = 'ATT.AnalyzerTest_Name as AnalyzerTest_Name';
			UslugaComplex2011SqlData::$select['uc_UslugaComplex_Name'] = 'uc.UslugaComplex_Name as UCUslugaComplex_Name';

			//PzmSqlData::$from['ttms'] = '';
			//PzmSqlData::$select['UslugaComplexMedService_id'] = 'ucpzm.UslugaComplexMedService_id as UslugaComplexMedService_id';
		}
		if (self::$isFunc && self::$formMode == 'ExtJS6') {
			UslugaComplex2011SqlData::$select['Resource_id']='res.Resource_id as Resource_id';
			UslugaComplex2011SqlData::$select['Resource_Name']='res.Resource_Name as Resource_Name';
			if (self::$needTimetable){
				UslugaComplex2011SqlData::$select['ttr_Resource_id']='res.Resource_id as ttr_Resource_id';
				UslugaComplex2011SqlData::$select['ttms_MedService_id']='mss.MedService_id AS ttms_MedService_id';
			}
		}
	}

	/**
	 * Устанавливает параметры сортировки
	 * @param array $data
	 * @return bool
	 */
	private static function setOrderParams($data) {
		$sortDir = ('DESC'==$data['dir'])?'DESC':'ASC';
		switch ($data['sort']) {
			case 'UslugaComplex_FullName':
				self::$params['pmUser_id'] = $data['pmUser_id'];
				UslugaComplex2011SqlData::setOrderBy("UslugaComplex_Code {$sortDir}");
				break;
			case 'composition':
				self::$params['pmUser_id'] = $data['pmUser_id'];
				UslugaComplex2011SqlData::setOrderBy("isComposite {$sortDir}");
				break;
			case 'Timetable':
				self::$params['pmUser_id'] = $data['pmUser_id'];
				UslugaComplex2011SqlData::setOrderBy(UslugaComplex2011SqlData::$TimetableBegTimeField." {$sortDir}");
				break;
			case 'location':
				self::$params['pmUser_id'] = $data['pmUser_id'];
				UslugaComplex2011SqlData::setOrderBy(UslugaComplex2011SqlData::$MedServiceDisplayField." {$sortDir}");
				break;
			default:
				if (empty($data['pmUser_id'])) {
					self::$error = 'Не указан пользователь';
					return false;
				}
				/*
				Порядок отображения услуг:
				Последние N услуг-служб либо услуг без служб, на которые данный врач создавал направления.
				Наше отделение
				Наша группа отделений
				Наше подразделение
				Наше ЛПУ
				Услуги в других ЛПУ
				Прочие услуги из справочника, которые не оказывается в других ЛПУ.
				 */
				self::$params['pmUser_id'] = $data['pmUser_id'];
				UslugaComplex2011SqlData::setOrderBy("DD.UslugaComplex_2011id");
				break;
		}
		return true;
	}

	/**
	 * Устанавливаем основные фильтры и параметры для них
	 * @param array $data
	 * @return bool
	 */
	private static function setBaseFilters($data) {
		if ( empty($data['allowedUslugaComplexAttributeList']) ) {
			self::$error = 'Не указаны атрибуты услуги';
			return false;
		}
		$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);
		if ( !is_array($allowedUslugaComplexAttributeList) || count($allowedUslugaComplexAttributeList) != 1 ) {
			self::$error = 'Неправильный формат атрибутов услуги';
			return false;
		}

		if (2 == self::$_options['prescription']['service_name_show_type']) {
			// При отсутствии связки услуги со справочником ГОСТ 2011 данную услугу в назначениях не отображать.
			// вместо 'uc11'=>'left join v_UslugaComplex uc11 on uc.UslugaComplex_2011id = uc11.UslugaComplex_id',
			UslugaComplex2011SqlData::$from['uc11']='inner join v_UslugaComplex uc11 on uc.UslugaComplex_2011id = uc11.UslugaComplex_id';
			// Отображение наименований услуг из Справочник ГОСТ-2011
			// вместо coalesce(uc.UslugaComplex_Code, uc11.UslugaComplex_Code) as UslugaComplex_Code
			UslugaComplex2011SqlData::$select['UslugaComplex_Code']='uc11.UslugaComplex_Code as UslugaComplex_Code';
			// вместо coalesce(ucms.UslugaComplex_Name, uc.UslugaComplex_Name, uc11.UslugaComplex_Name) as UslugaComplex_Name
			UslugaComplex2011SqlData::$select['UslugaComplex_Name']='uc11.UslugaComplex_Name as UslugaComplex_Name';
			//Фильтры по датам
			UslugaComplex2011SqlData::$filters['filterByBegDate']='uc11.UslugaComplex_begDT <= (select cur_date from vars)';
			UslugaComplex2011SqlData::$filters['filterByEndDate']='coalesce(uc11.UslugaComplex_endDT, (select cur_date from vars)) >= (select cur_date from vars)';
		}
		if (!empty(self::$_options['prescription']['enable_grouping_by_gost2011'])) {
			// При отсутствии связки услуги со справочником ГОСТ 2011 данную услугу в назначениях не отображать.
			UslugaComplex2011SqlData::$from['uc11']='inner join v_UslugaComplex uc11 on uc.UslugaComplex_2011id = uc11.UslugaComplex_id';
			//Группировка по идентификатору связанной услуги ГОСТ
			UslugaComplex2011SqlData::$select['UslugaComplex_msid']='uc.UslugaComplex_2011id as UslugaComplex_msid';
			// Отображение наименований услуг из Справочник ГОСТ-2011
			// вместо coalesce(uc.UslugaComplex_Code, uc11.UslugaComplex_Code) as UslugaComplex_Code
			UslugaComplex2011SqlData::$select['UslugaComplex_Code']='uc11.UslugaComplex_Code as UslugaComplex_Code';
			// вместо coalesce(ucms.UslugaComplex_Name, uc.UslugaComplex_Name, uc11.UslugaComplex_Name) as UslugaComplex_Name
			UslugaComplex2011SqlData::$select['UslugaComplex_Name']='uc11.UslugaComplex_Name as UslugaComplex_Name';
			//Фильтры по датам
			UslugaComplex2011SqlData::$filters['filterByBegDate']='uc11.UslugaComplex_begDT <= (select cur_date from vars)';
			UslugaComplex2011SqlData::$filters['filterByEndDate']='coalesce(uc11.UslugaComplex_endDT, (select cur_date from vars)) >= (select cur_date from vars)';
		}

		//устанавливаем фильтр на услуги по UslugaComplexAttributeType
		UslugaComplex2011SqlData::$filters['filterWithUslugaComplexAttributeType'] = "exists (
			select
				t1.UslugaComplexAttribute_id
			from v_UslugaComplexAttribute t1
				inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
			where t1.UslugaComplex_id = coalesce(uc11.UslugaComplex_id, uc.UslugaComplex_id)
				and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
			limit 1
		)";

		if (!empty($data['filterByLpu_id']) || !empty($data['filterByLpu_str']) || !empty($data['filterByMedService_id'])) {
			// Если установлен фильтр по МО или по службе, то услуги из справочника ГОСТ-2011, которые нигде не оказываются, не показываем.
			// Показываем услуги любых категорий фактически заведенных в местах оказания услуги
			// вместо 'ucms'=>'left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id',
			UslugaComplex2011SqlData::$from['ucms']='inner join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id';
			// вместо 'mss'=>'left join v_MedService mss on mss.MedService_id = ucms.MedService_id',
			UslugaComplex2011SqlData::$from['mss']='inner join mss on mss.MedService_id = ucms.MedService_id';

			if ( !empty($data['filterByMedService_id']) ) {
				self::$params['filterByMedService_id'] = $data['filterByMedService_id'];
				if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'pzm') {
					UslugaComplex2011SqlData::$filters['pzm'] = 'pzm.MedService_id = :filterByMedService_id';
				} else {
					UslugaComplex2011SqlData::$from['ucms'] .= ' AND ucms.MedService_id = :filterByMedService_id';
				}
			}
		}

		if (in_array('func',$allowedUslugaComplexAttributeList)) {
			self::$withResource = true;
			UslugaComplex2011SqlData::$select['withResource'] = "1 as withResource";
			UslugaComplex2011SqlData::$filters['filterMedServiceWithResources'] = "(exists(
					select
						t2.Resource_id
					from
						v_UslugaComplexResource t1
						inner join v_Resource t2 on t1.Resource_id = t2.Resource_id
					where
						t1.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						and coalesce(cast(t2.Resource_begDT as date), (select cur_date from vars)) <= (select cur_date from vars)
						and coalesce(cast(t2.Resource_endDT as date), (select cur_date from vars)) >= (select cur_date from vars)
					limit 1
				)
			)";
			/*UslugaComplex2011SqlData::$filters['filterIsHasOnResource'] = 'exists (
				select top 1
					ucres.UslugaComplexResource_id
				from v_UslugaComplexResource ucres
				where ucres.Resource_id = res.Resource_id
					and ucres.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
			)';*/
		}


		// накладываю базовые фильтры на услуги
		// услуги из состава не показываем
		UslugaComplex2011SqlData::$from['ucms'] .= ' AND ucms.UslugaComplexMedService_pid IS NULL';
		// показываем только действующие услуги
		UslugaComplex2011SqlData::$from['ucms'] .= ' AND cast(ucms.UslugaComplexMedService_begDT as date) <= (select cur_date from vars) AND coalesce(cast(ucms.UslugaComplexMedService_endDT as date), (select cur_date from vars)) >= (select cur_date from vars)';

		//устанавливаем фильтр на места оказания (службы) по MedServiceType
		switch (true) {
			case (in_array('manproc',$allowedUslugaComplexAttributeList)):
				UslugaComplex2011SqlData::$from['mss'] .= ' AND mss.MedServiceType_id = 13';
				break;
			case (in_array('oper',$allowedUslugaComplexAttributeList) && $data['isStac']): // В оперблок можно назначать только из стационара
				UslugaComplex2011SqlData::$from['mss'] .= ' AND mss.MedServiceType_id = 57';
				break;
			case (in_array('lab',$allowedUslugaComplexAttributeList)):
				self::$isLab = true;
				UslugaComplex2011SqlData::$from['mss'] .= ' AND (mss.MedServiceType_id in (6,71))';
				break;
			case (in_array('func',$allowedUslugaComplexAttributeList)):
				self::$isFunc = true;
				UslugaComplex2011SqlData::$from['mss'] .= ' AND mss.MedServiceType_id = 8';
				break;
			case (in_array('consult',$allowedUslugaComplexAttributeList)):
				UslugaComplex2011SqlData::$from['mss'] .= ' AND mss.MedServiceType_id = 29';
				break;
			default:
				UslugaComplex2011SqlData::$from['mss'] .= ' AND 1 = 2';
				break;
		}

		if (!empty($data['filterByLpu_id'])) {
			// Фильтруем места оказания по ЛПУ
			self::$params['filterByLpu_id'] = $data['filterByLpu_id'];
			if (self::$isLab) {
				UslugaComplex2011SqlData::$filters['filterByLpu'] = "1=(case when mss.Lpu_id = :filterByLpu_id then 1 when pzm.Lpu_id = :filterByLpu_id then 1 else 0 end)";
			} else {
				UslugaComplex2011SqlData::$from['mss'] .= ' AND mss.Lpu_id = :filterByLpu_id';
			}
		}
		if (!empty($data['filterByLpu_str'])) {
			self::$params['filterByLpu_str'] = '%'.$data['filterByLpu_str'].'%';
			// Фильтруем места оказания по Lpu_Nick или Lpu_Name ?
			if (self::$isLab) {
				UslugaComplex2011SqlData::$filters['filterByLpu'] = "exists (
						select l.Lpu_id
						from v_Lpu l
						where (l.Lpu_id = mss.Lpu_id OR l.Lpu_id = pzm.Lpu_id) and l.Lpu_Nick ilike :filterByLpu_str
						limit 1
					)";
			} else {
				UslugaComplex2011SqlData::$from['mss'] .= ' AND exists (
					select l.Lpu_id
					from v_Lpu l
					where l.Lpu_id = mss.Lpu_id and l.Lpu_Nick ilike :filterByLpu_str
					limit 1
				)';
			}
		}

		if (self::$isLab) {
			UslugaComplex2011SqlData::$filters['filterIsThisLPU'] = 'not exists( select * from v_medservice mslo where mslo.MedService_id = coalesce(pzm.MedService_id, mss.MedService_id) and mslo.MedService_IsThisLPU = 2 and mslo.lpu_id != :Lpu_id)';
		}

		// накладываю базовые фильтры на места оказания
		if (!empty($data['isOnlyPolka'])) {
			// будем показывать только службы поликлинических отделений, в т.ч. стоматологических
			UslugaComplex2011SqlData::$from['mss'] .= " AND exists (
				select lut.LpuUnitType_id
				from v_LpuUnitType lut
				where lut.LpuUnitType_id = mss.LpuUnitType_id and lut.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
				limit 1
			)";
		}
		if(!empty($data['MedService_id'])){
			if (self::$isLab == true) {
				UslugaComplex2011SqlData::$filters['MedService_id'] = "  (mss.MedService_id = :MedService_id or pzm.MedService_id = :MedService_id)";
			} else {
				UslugaComplex2011SqlData::$filters['MedService_id'] = "  mss.MedService_id = :MedService_id";
			}
			self::$params['MedService_id'] = $data['MedService_id'];
		}
		if(!empty($data['pzm_MedService_id'])){
			UslugaComplex2011SqlData::$filters['pzm_MedService_id'] = "  pzm.MedService_id = :pzm_MedService_id";
			self::$params['pzm_MedService_id'] = $data['pzm_MedService_id'];
		}
		if(!empty($data['Resource_id']) && self::$formMode == 'ExtJS6'){
			UslugaComplex2011SqlData::$filters['Resource_id'] = "  res.Resource_id = :Resource_id";
			self::$params['Resource_id'] = $data['Resource_id'];
		}
		// показываем службы только уровня отделения, для корректного направления
		UslugaComplex2011SqlData::$from['mss'] .= ' AND mss.LpuSection_id is not null';
		// показываем только действующие службы
		//UslugaComplex2011SqlData::$from['mss'] .= ' AND cast(mss.MedService_begDT as date) <= (select cur_date from vars) AND coalesce(cast(mss.MedService_endDT as date), (select cur_date from vars)) >= (select cur_date from vars)';

		if (!empty($data['onlyByContract'])) {
			UslugaComplex2011SqlData::$filters['filterByContract'] = " exists(select * from v_LpuDispContract LDC where mss.Lpu_id = ldc.Lpu_oid and ldc.Lpu_id = :Lpu_id and ls.LpuSectionProfile_id = LDC.LpuSectionProfile_id and coalesce(LDC.LpuDispContract_setDate, (select cur_date from vars)) <= (select cur_date from vars) and coalesce(LDC.LpuDispContract_disDate, (select cur_date from vars)) >= (select cur_date from vars))";
		}

		if(!empty($data['composition_type']) && $data['composition_type']=='composition_tests') {
			//методика подсчета по MedService->loadCompositionMenu
			UslugaComplex2011SqlData::$from['composition'] = "left join lateral (
				select COUNT(ATEST.UslugaComplex_id) as cnt
				from v_UslugaComplexMedService ucmsoa
					inner join lateral (
						select
							uc.UslugaComplex_id
						from
							lis.v_AnalyzerTest at_child
							inner join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
							inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
							left join v_UslugaComplex uc on uc.UslugaComplex_id = at_child.UslugaComplex_id
						where
							at_child.UslugaComplexMedService_id = ucmsoa.UslugaComplexMedService_id
							and at.UslugaComplexMedService_id = ucmsoa.UslugaComplexMedService_pid
							and coalesce(at_child.AnalyzerTest_IsNotActive, 1) = 1
							and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1
							and coalesce(a.Analyzer_IsNotActive, 1) = 1
							and (at_child.AnalyzerTest_endDT >= (select cur_date from vars) or at_child.AnalyzerTest_endDT is null)
							and (uc.UslugaComplex_endDT >= (select cur_date from vars) or uc.UslugaComplex_endDT is null)
						limit 1
					) ATEST on true
				where ucmsoa.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
			) composition on true";
		}
		
		return true;
	}

	/**
	 * Устанавливаем дополнительные фильтры и параметры для них
	 * @param array $data
	 * @return bool
	 */
	private static function setAdditionalFilters($data) {
		if (!empty($data['filterByUslugaComplex_str'])) {
			self::$params['filterByUslugaComplex_str'] = '%'.$data['filterByUslugaComplex_str'].'%';
			UslugaComplex2011SqlData::$filters['filterByUslugaComplex'] = "(uc.UslugaComplex_Code || '. ' || uc.UslugaComplex_Name) ilike :filterByUslugaComplex_str";
			if (2 == self::$_options['prescription']['service_name_show_type']) {
				UslugaComplex2011SqlData::$filters['filterByUslugaComplex'] = "(uc11.UslugaComplex_Code || '. ' || uc11.UslugaComplex_Name) ilike :filterByUslugaComplex_str";
			}
		}
		if (!empty($data['filterByUslugaComplex_id'])) {
			UslugaComplex2011SqlData::$filters['filterByUslugaComplex'] = "uc.UslugaComplex_id = :filterByUslugaComplex_id";
			self::$params['filterByUslugaComplex_id'] = $data['filterByUslugaComplex_id'];

			if (2 == self::$_options['prescription']['service_name_show_type'] || !empty(self::$_options['prescription']['enable_grouping_by_gost2011'])) {
				UslugaComplex2011SqlData::$filters['filterByUslugaComplex'] = "uc11.UslugaComplex_id = :filterByUslugaComplex_id";
			}
		}
		if (!empty($data['filterByAnalyzerTestName'])) {
			UslugaComplex2011SqlData::$filters['filterByAnalyzerTestName'] = "coalesce(att.AnalyzerTest_Name, uc.UslugaComplex_Name) ilike :filterByAnalyzerTestName";
			self::$params['filterByAnalyzerTestName'] = '%'.$data['filterByAnalyzerTestName'].'%';
		}
		return true;
	}

	/**
	 * Устанавливает параметры.
	 * В зависимости от параметров будет выбрана стратегия построения запроса
	 * @param array $data
	 * @return bool
	 */
	static function setData($data) {
		if (empty($data['userLpu_id'])) {
			// этот параметр нужен для сортировки мест оказания
			// и сортировки списка услуг по умолчанию
			self::$error = 'Не указано МО пользователя';
			return false;
		}
		if (!empty($data['formMode'])) {
			self::$formMode = $data['formMode'];
		}
		self::$params['userLpu_id'] = $data['userLpu_id'];
		self::$params['Lpu_id'] = $data['Lpu_id'];
		if (empty($data['userLpuBuilding_id'])) {
			// этот параметр нужен для сортировки мест оказания
			// и сортировки списка услуг по умолчанию
			self::$error = 'Не указано строение МО пользователя';
			return false;
		}
		self::$params['userLpuBuilding_id'] = $data['userLpuBuilding_id'];

		if (empty($data['userLpuUnit_id'])) {
			// этот параметр нужен для сортировки мест оказания
			// и сортировки списка услуг по умолчанию
			self::$error = 'Не указано подразделение МО пользователя';
			return false;
		}
		self::$params['userLpuUnit_id'] = $data['userLpuUnit_id'];

		if (empty($data['userLpuSection_id'])) {
			// этот параметр нужен для сортировки мест оказания
			// и сортировки списка услуг по умолчанию
			self::$error = 'Не указано отделение пользователя';
			return false;
		}
		self::$params['userLpuSection_id'] = $data['userLpuSection_id'];

		get_instance()->load->helper('Options');
		self::$_options = getOptions();

		if (!self::setBaseFilters($data)) {
			return false;
		}
		if (!self::setAdditionalFilters($data)) {
			return false;
		}

		//Устанавливает параметры запроса с учетом специфики служб
		//после того как фильтры определены!
		self::setSqlDataSpecific();

		if (!self::setOrderParams($data)) {
			return false;
		}
		return true;
	}

	/**
	 * Создает и возвращает запрос
	 * @return string
	 */
	static function getSql($data = array()) {
		// Строим запрос после того, как определены параметры запроса с учетом специфики служб и сортировки!

		/**
		 * Внимание!!!
		 * Тут был фильтр по бирке:
		 * (MedService_id = mss.MedService_id or UslugaComplexMedService_id = ucms.UslugaComplexMedService_id)
		 * Но на рабочем запрос с таким фильтром уходит в ступор
		 * Вернули фильтр MedService_id = mss.MedService_id
		 * Если будет нужен фильтр по биркам услуг, то надо думать над оптимизацией.
		 */

		if (!empty($data['groupByMedService']) && self::$formMode == 'ExtJS6') {
			// при группировке по службе бирки не нужны
			self::$needTimetable = false;
		}

		$tplParams = self::getTplParams();
		if (self::$isLab == true) {
			$orderbyfields = "
				case when mss.Lpu_id = :userLpu_id then 1 else 2 end as s1,
				case when pzm.Lpu_id = :userLpu_id then 1 else 2 end as s2,
				case when mss.LpuBuilding_id = :userLpuBuilding_id then 1 else 2 end as s3,
				case when pzm.LpuBuilding_id = :userLpuBuilding_id then 1 else 2 end as s4,
				case when mss.LpuUnit_id = :userLpuUnit_id then 1 else 2 end as s5,
				case when pzm.LpuUnit_id = :userLpuUnit_id then 1 else 2 end as s6,
				case when mss.LpuSection_id = :userLpuSection_id then 1 else 2 end as s7,
				case when pzm.LpuSection_id = :userLpuSection_id then 1 else 2 end as s8,
				case when exists(
					select
						TimetableMedService_id
					from
						v_TimetableMedService_lite
					where
						MedService_id = mss.MedService_id
						and Person_id is null
						AND TimetableMedService_begTime >= (select cur_dt from vars)
						AND TimetableMedService_begTime < (select upper_dt from vars)
				) then 1 else 2 end as s9,
				uc.UslugaComplex_Code as s10
			";
		} else {
			$orderbyTimetable = "
				case when exists(
					select
						*
					from
						v_TimetableMedService_lite
					where
						MedService_id = mss.MedService_id
						and Person_id is null
						AND TimetableMedService_begTime >= (select cur_dt from vars)
						AND TimetableMedService_begTime < (select upper_dt from vars)
				) then 1 else 2 end as s5,
				1 as s6,
			";
			if (self::$withResource) {
				if (self::$formMode == 'ExtJS6') {
					$orderbyTimetable = "
						case when exists(
							select
								*
							from
								v_TimetableResource_lite ttr
							where
								ttr.Resource_id  = res.Resource_id 
								and ttr.Person_id is null
								AND ttr.TimetableResource_begTime >= (select cur_dt from vars)
								AND ttr.TimetableResource_begTime < (select upper_dt from vars)
						) then 1 else 2 end as s5,
						1 as s6,
					";
				} else {
					$orderbyTimetable = "
						case when exists(
							select
								ttr.TimetableResource_id
							from
								v_TimetableResource_lite ttr
								inner join UslugaComplexResource ucr on ucr.Resource_id = ttr.Resource_id 
							where
								ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
								and ttr.Person_id is null
								AND ttr.TimetableResource_begTime >= (select cur_dt from vars)
								AND ttr.TimetableResource_begTime < (select upper_dt from vars)
							limit 1
						) then 1 else 2 end as s5,
						1 as s6,
					";
				}

			}

			$orderbyfields = "
				case when mss.Lpu_id = :userLpu_id then 1 else 2 end as s1,
				case when mss.LpuBuilding_id = :userLpuBuilding_id then 1 else 2 end as s2,
				case when mss.LpuUnit_id = :userLpuUnit_id then 1 else 2 end as s3,
				case when mss.LpuSection_id = :userLpuSection_id then 1 else 2 end as s4,
				{$orderbyTimetable}
				1 as s7,
				1 as s8,
				1 as s9,
				uc.UslugaComplex_Code as s10
			";
		}

		get_instance()->load->helper('Reg');
		$upper_dt = GetMedServiceDayCount($data['filterByLpu_id']);
		if ( date("H:i") < getShowNewDayTime() && $upper_dt ) $upper_dt--;
		$upper_dt = !empty($upper_dt) ? $upper_dt : 30;
		// Закомментил https://redmine.swan.perm.ru/issues/96038
        /*if(getRegionNick() == 'ufa') //https://redmine.swan.perm.ru/issues/72692
            $upper_dt = $upper_dt-1;*/
		if (!empty($data['noDateLimit']) && $data['noDateLimit'] == 1) {
			$upper_dt = 365;
		}
		$tplParams['{with}'] = "
			with vars as (select
			GETDATE()::timestamp as cur_dt,
			(CURRENT_DATE + INTERVAL '{$upper_dt} day')::timestamp as upper_dt,
			GETDATE()::date as cur_date),
		";
		
		$tplParams['{with}'] .= '
		mss as (
			select 
				mss.MedService_id,
				mss.MedServiceType_id,
				mss.LpuUnit_id,
				mss.LpuSection_id,
				mss.MedService_Name,
				mss.RecordQueue_id,
				mss.MedService_Nick,
				mss.LpuBuilding_id,
				mss.MedService_IsThisLPU,
				l.lpu_id,
				l.lpu_nick,
				l.uaddress_id
			from v_MedService as mss 
			left join v_Lpu l on mss.Lpu_id = l.Lpu_id
			where true
				AND cast(mss.MedService_begDT as date) <= (select cur_date from vars) 
				AND coalesce(cast(mss.MedService_endDT as date), (select cur_date from vars)) >= (select cur_date from vars)
		),
		';

		$tplParams['{with}'] .= '
		tmp as (
		select
		'.$tplParams['{select}'].',
		'.$orderbyfields.'
		from
		'.$tplParams['{from}'].'
		where
		'.$tplParams['{where}'].'
		--IsThisLPU
		),';

		$addFields = self::getAddFields();

		if (!empty($data['groupByMedService'])) {
			$tplParams['{with}'] .= "
			DD as (
				Select
					ucdouble.UslugaComplex_2011id
					,1 as MedService_cnt
					,ucdouble.RecordQueue_id
					,ucdouble.UslugaComplex_msid
					,ucdouble.UslugaComplex_id
					,ucdouble.UslugaComplex_Code
					,ucdouble.UslugaComplex_Name
					,ucdouble.UslugaComplex_AttributeList
					,ucdouble.UslugaCategory_id
					,ucdouble.MedService_id
					,ucdouble.UslugaComplexMedService_id
					,ucdouble.MedService_Name
					,ucdouble.MedService_Nick
					,ucdouble.MedServiceType_id
					,ucdouble.MedServiceType_SysNick
					,ucdouble.Lpu_id
					,ucdouble.Lpu_Nick
					,ucdouble.LpuBuilding_id
					,ucdouble.LpuBuilding_Name
					,ucdouble.LpuUnit_id
					,ucdouble.LpuUnit_Name
					,ucdouble.LpuUnit_Address
					,ucdouble.LpuUnitType_id
					,ucdouble.LpuUnitType_SysNick
					,ucdouble.LpuSection_id
					,ucdouble.LpuSection_Name
					,ucdouble.LpuSectionProfile_id
					,ucdouble.LpuSectionProfile_Name
					,ucdouble.isComposite
					{$addFields['addFields']}
					{$addFields['Group_id']}
					{$addFields['Unique_id']}
					,ucdouble.withResource
					,ucdouble.composition_cnt
				from
					tmp as ucdouble
			)";

			$tplParams['{select}'] = "
				DD.UslugaComplex_2011id as \"UslugaComplex_2011id\"
				,DD.MedService_cnt as \"MedService_cnt\"
				,DD.RecordQueue_id as \"RecordQueue_id\"
				,DD.UslugaComplex_msid as \"UslugaComplex_msid\"
				,DD.UslugaComplex_id as \"UslugaComplex_id\"
				,DD.UslugaComplex_Code as \"UslugaComplex_Code\"
				,DD.UslugaComplex_Name as \"UslugaComplex_Name\"
				,DD.UslugaCategory_id as \"UslugaCategory_id\"
				,DD.MedService_id as \"MedService_id\"
				,DD.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
				,DD.MedService_Name as \"MedService_Name\"
				,DD.MedService_Nick as \"MedService_Nick\"
				,DD.MedServiceType_id as \"MedServiceType_id\"
				,DD.MedServiceType_SysNick as \"MedServiceType_SysNick\"
				,DD.Lpu_id as \"Lpu_id\"
				,DD.Lpu_Nick as \"Lpu_Nick\"
				,DD.LpuBuilding_id as \"LpuBuilding_id\"
				,DD.LpuBuilding_Name as \"LpuBuilding_Name\"
				,DD.LpuUnit_id as \"LpuUnit_id\"
				,DD.LpuUnit_Name as \"LpuUnit_Name\"
				,DD.LpuUnit_Address as \"LpuUnit_Address\"
				,DD.LpuUnitType_id as \"LpuUnitType_id\"
				,DD.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				,DD.LpuSection_id as \"LpuSection_id\"
				,DD.LpuSection_Name as \"LpuSection_Name\"
				,DD.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,DD.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				,DD.isComposite as \"isComposite\"
				,DD.withResource as \"withResource\"
				,DD.composition_cnt as \"composition_cnt\"
			";
		} else {
			$groupByLpu = "";
			$filterByLpu = "";
			if (self::$formMode == 'ExtJS6' && !empty($data['filterByUslugaComplex_id'])) {
				// для формы 6-го экста надо дублировать строки для разных МО
				$groupByLpu = ",uc.Lpu_id";
				$filterByLpu = " and uc.Lpu_id = ucdouble.Lpu_id";
			}

			if (self::$formMode != 'ExtJS6') {
				$addFields['Group_id'] = "";
			}

			$tplParams['{with}'] .= "
			DD as (
				Select
					uc.UslugaComplex_2011id
					,uccount.MedService_cnt
					,ucdouble.*
					{$addFields['Group_id']}
					{$addFields['Unique_id']}
				from
					tmp as uc
					left join lateral (
						Select count(*) as MedService_cnt
						from tmp as uccount
						where uc.UslugaComplex_msid = uccount.UslugaComplex_msid
					) uccount on true
					inner join lateral (
						Select
							ucdouble.UslugaComplex_msid
							,ucdouble.UslugaComplex_id
							,ucdouble.UslugaComplex_Code
							,ucdouble.UslugaComplex_Name
							,ucdouble.UslugaComplex_AttributeList
							,ucdouble.UslugaCategory_id
							,ucdouble.MedService_id
							,ucdouble.UslugaComplexMedService_id
							,ucdouble.MedService_Name
							,ucdouble.MedService_Nick
							,ucdouble.MedServiceType_id
							,ucdouble.MedServiceType_SysNick
							,ucdouble.Lpu_id
							,ucdouble.Lpu_Nick
							,ucdouble.LpuBuilding_id
							,ucdouble.LpuBuilding_Name
							,ucdouble.LpuUnit_id
							,ucdouble.LpuUnit_Name
							,ucdouble.LpuUnit_Address
							,ucdouble.LpuUnitType_id
							,ucdouble.LpuUnitType_SysNick
							,ucdouble.LpuSection_id
							,ucdouble.LpuSection_Name
							,ucdouble.LpuSectionProfile_id
							,ucdouble.LpuSectionProfile_Name
							,ucdouble.isComposite
							{$addFields['addFields']}
							,ucdouble.ElectronicQueueInfo_id
							,ucdouble.ElectronicService_id
							,ucdouble.withResource
							,ucdouble.composition_cnt
						from tmp as ucdouble
						where
							uc.UslugaComplex_msid = ucdouble.UslugaComplex_msid
							{$filterByLpu}
						order by
								s1, s2, s3, s4, s5, s6, s7, s8, s9, s10
						limit 1
					 ) ucdouble on true
				group by
					uc.UslugaComplex_2011id
					{$groupByLpu}
					,ucdouble.UslugaComplex_msid
					,ucdouble.UslugaComplex_id
					,ucdouble.UslugaComplex_Code
					,ucdouble.UslugaComplex_Name
					,ucdouble.UslugaComplex_AttributeList
					,ucdouble.UslugaCategory_id
					,uccount.MedService_cnt
					,ucdouble.MedService_id
					,ucdouble.UslugaComplexMedService_id
					,ucdouble.MedService_Name
					,ucdouble.MedService_Nick
					,ucdouble.MedServiceType_id
					,ucdouble.MedServiceType_SysNick
					,ucdouble.Lpu_id
					,ucdouble.Lpu_Nick
					,ucdouble.LpuBuilding_id
					,ucdouble.LpuBuilding_Name
					,ucdouble.LpuUnit_id
					,ucdouble.LpuUnit_Name
					,ucdouble.LpuUnit_Address
					,ucdouble.LpuUnitType_id
					,ucdouble.LpuUnitType_SysNick
					,ucdouble.LpuSection_id
					,ucdouble.LpuSection_Name
					,ucdouble.LpuSectionProfile_id
					,ucdouble.LpuSectionProfile_Name
					,ucdouble.isComposite
					{$addFields['addFields']}
					,ucdouble.ElectronicQueueInfo_id
					,ucdouble.ElectronicService_id
					,ucdouble.withResource
					,ucdouble.composition_cnt
			)";

			$tplParams['{select}'] = "
				DD.UslugaComplex_2011id as \"UslugaComplex_2011id\"
				,DD.MedService_cnt as \"MedService_cnt\"
				,DD.UslugaComplex_msid as \"UslugaComplex_msid\"
				,DD.UslugaComplex_id as \"UslugaComplex_id\"
				,DD.UslugaComplex_Code as \"UslugaComplex_Code\"
				,DD.UslugaComplex_Name as \"UslugaComplex_Name\"
				,DD.UslugaCategory_id as \"UslugaCategory_id\"
				,DD.MedService_id as \"MedService_id\"
				,DD.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
				,DD.MedService_Name as \"MedService_Name\"
				,DD.MedService_Nick as \"MedService_Nick\"
				,DD.MedServiceType_id as \"MedServiceType_id\"
				,DD.MedServiceType_SysNick as \"MedServiceType_SysNick\"
				,DD.Lpu_id as \"Lpu_id\"
				,DD.Lpu_Nick as \"Lpu_Nick\"
				,DD.LpuBuilding_id as \"LpuBuilding_id\"
				,DD.LpuBuilding_Name as \"LpuBuilding_Name\"
				,DD.LpuUnit_id as \"LpuUnit_id\"
				,DD.LpuUnit_Name as \"LpuUnit_Name\"
				,DD.LpuUnit_Address as \"LpuUnit_Address\"
				,DD.LpuUnitType_id as \"LpuUnitType_id\"
				,DD.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				,DD.LpuSection_id as \"LpuSection_id\"
				,DD.LpuSection_Name as \"LpuSection_Name\"
				,DD.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,DD.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				,DD.isComposite as \"isComposite\"
				,DD.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,DD.ElectronicService_id as \"ElectronicService_id\"
				,DD.withResource as \"withResource\"
				,DD.composition_cnt as \"composition_cnt\"
			";
		}

		$tplParams['{select}'] .= preg_replace(
			'/,ucdouble.([A-z0-9_]+)/',
			',DD.$1 as "$1"',
			$addFields['addFields']
		);

		if (!empty($addFields['Unique_id'])) {
			$tplParams['{select}'] .= "
				,DD.Unique_id as \"Unique_id\"
			";
		}
		if (!empty($addFields['Group_id'])) {
			$tplParams['{select}'] .= "
				,DD.Group_id as \"Group_id\"
			";
		}

		//$tplParams['{select}'] = "DD.*";
		if (self::$needTimetable) {
			if(self::$formMode == 'ExtJS6' && self::$isFunc === true){
				$tplParams['{select}'] .= "
					,ttmsx.TimetableMedService_begTime as \"TimetableMedService_begTime\"
					,ttmsx.TimetableMedService_id as \"TimetableMedService_id\"
					,ttmsx.TimetableResource_begTime as \"TimetableResource_begTime\"
					,ttmsx.TimetableResource_id as \"TimetableResource_id\"
					,ttmsx.Person_id as \"Person_id\"
				";
			} else {
				$tplParams['{select}'] .= "
					,to_char(ttmsx.TimetableMedService_begTime::timestamp, 'DD.MM.YYYY HH24:MI') as \"TimetableMedService_begTime\"
					,ttmsx.TimetableMedService_id as \"TimetableMedService_id\"
					,ttmsx.MedService_id as \"ttms_MedService_id\"
					,to_char(ttmsx.TimetableResource_begTime::timestamp, 'DD.MM.YYYY HH24:MI') as \"TimetableResource_begTime\"
					,ttmsx.TimetableResource_id as \"TimetableResource_id\"
					,ttmsx.Resource_id as \"Resource_id\"
					,ttmsx.Resource_Name as \"Resource_Name\"
					,ttmsx.Resource_id as \"ttr_Resource_id\"
				";
			}
		}
		$tplParams['{from}'] = "DD";

		if (self::$needTimetable) {
			$tplParams['{from}'] .= self::getTimetableQueryFrom();
		}

		$tplParams['{where}'] = "(1=1)";
		//$tplParams['{order}'] = "DD.UslugaComplex_2011id";

		return strtr(self::getTpl(true), $tplParams);
	}

	/**
	 * Облегченный запрос по услугам для мобильного апи
	 * @return string
	 */
	static function getSqlForApi($data = array()) {

		$tplParams = self::getTplParams();

		get_instance()->load->helper('Reg');
		$upper_dt = GetMedServiceDayCount($data['filterByLpu_id']);
		if ( date("H:i") < getShowNewDayTime() && $upper_dt ) $upper_dt--;

		$upper_dt = !empty($upper_dt) ? $upper_dt : 30;
		if (!empty($data['noDateLimit']) && $data['noDateLimit'] == 1) {
			$upper_dt = 365;
		}

		$tplParams['{with}'] = "
			drop table if exists vars;
			create temp table vars as select
			GETDATE()::timestamp as cur_dt,
			(CURRENT_DATE + INTERVAL '{$upper_dt} day')::timestamp as upper_dt,
			GETDATE()::date as cur_date;
			
			drop table if exists tmp;
			create temp table tmp as
			select ".$tplParams['{select}']." 
			from ".$tplParams['{from}']." 
			where ".$tplParams['{where}'].";
		";

		$addFields = "";

		if (self::$isLab) {
			$addFields = "
				,uc.pzm_MedService_id
				,uc.pzm_UslugaComplexMedService_id
				,uc.pzm_MedService_Name
			";
		}

		if (self::$isFunc) {
			$addFields = "
				,uc.Resource_id
				,uc.Resource_Name
			";
		}

		$tplParams['{with}'] .= "
			with DD as (
				SELECT
					uc.UslugaComplex_2011id,
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code,
					uc.UslugaComplex_Name,
					uc.UslugaComplex_AttributeList,
					uc.UslugaComplexMedService_id,
					uc.MedService_id,
					uc.Lpu_id,
					uc.MedService_Name,
					uc.composition_cnt
					{$addFields}
				from tmp as uc					
				group by
					uc.UslugaComplex_2011id,
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code,
					uc.UslugaComplex_Name,
					uc.UslugaComplex_AttributeList,
					uc.UslugaComplexMedService_id,
					uc.MedService_id,
					uc.Lpu_id,
					uc.MedService_Name,
					uc.composition_cnt
					{$addFields}
			)
		";

		$tplParams['{select}'] = "
			DD.UslugaComplex_2011id as \"UslugaComplex_2011id\"
			,DD.UslugaComplex_id as \"UslugaComplex_id\"
			,DD.UslugaComplex_Code as \"UslugaComplex_Code\"
			,DD.UslugaComplex_Name as \"UslugaComplex_Name\"
			,DD.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			,DD.MedService_id as \"MedService_id\"
			,DD.Lpu_id as \"Lpu_id\"
			,DD.MedService_Name as \"MedService_Name\"
			,DD.composition_cnt as \"composition_cnt\"
		";
		$tplParams['{select}'] .= preg_replace(
			'/,uc.([A-z0-9_]+)/',
			',DD.$1 as "$1"',
			$addFields
		);
		$tplParams['{select}'] .= "
			,to_char(ttmsx.TimetableMedService_begTime, 'DD.MM.YYYY HH24:MI') as \"TimetableMedService_begTime\"
			,ttmsx.TimetableMedService_id as \"TimetableMedService_id\"
			,ttmsx.TimetableResource_begTime as \"TimetableResource_begTime\"
			,ttmsx.TimetableResource_id as \"TimetableResource_id\"
			,ttmsx.is_pzm as \"is_pzm\"
		";

		$tplParams['{from}'] = "DD";

		if (self::$needTimetable)  $tplParams['{from}'] .= self::getTimetableQueryFrom();
		$tplParams['{where}'] = "(1=1)";

		return strtr(self::getTpl(true), $tplParams);
	}

	/**
	 * Возвращает запрос для бирок
	 */
	static function getTimetableQueryFrom() {
		$all_ttms_params = array(
			'{all_ttms_join}' => implode("\n", AllTtmsSqlData::$from),
			'{all_ttms_filters}' => implode("\n AND ", AllTtmsSqlData::$filters),
		);

		$typeOrderTime = '
			, ttms.TimetableMedService_begTime
		';
		$typeOrderLpu = '
			case
				/* При записи в свое МО: Все типы бирок кроме «резервных» */
				when DD.Lpu_id = :userLpu_id AND ttms.TimeTableType_id not in (2) then 0
				/* При записи в чужое МО: Обычная, По направлению */
				when DD.Lpu_id <> :userLpu_id AND ttms.TimeTableType_id in (1,5) then 0
				else 1
			end
		';
		// Фильтр заполнен для функциональной диагностики нового интерфейса
		$filter_pzmMS = '';
		// Фильтры для всех типов услуг кроме лабораторной
		$filter_MS = 'and 1 <> 1';
		$filter_uslPZM = 'and 1 <> 1';
		if(self::$isLab){
			$filter_pzmMS = "and DD.pzm_MedService_id is null";
			$filter_MS = "and ttms.MedService_id = dd.pzm_MedService_id";
			$filter_uslPZM = "and ttms.UslugaComplexMedService_id = dd.pzm_UslugaComplexMedService_id";
		}

		if (self::$withResource) {
			$typeOrderTime = '
				, ttr.TimetableResource_begTime
			';
			$typeOrderLpu = '
			case
				/* При записи в свое МО: Все типы бирок кроме «резервных» */
				when DD.Lpu_id = :userLpu_id AND ttr.TimeTableType_id not in (2) then 0
				/* При записи в чужое МО: Обычная, По направлению */
				when DD.Lpu_id <> :userLpu_id AND ttr.TimeTableType_id in (1,5) then 0
				else 1
			end 
		';
			if(self::$formMode == 'ExtJS6'){
				return "
				left join lateral (
					SELECT
						NULL AS TimetableMedService_id,
						cast(NULL as timestamp) AS TimetableMedService_begTime,
						to_char(ttr.TimetableResource_begTime, 'DD.MM.YYYY HH24:MI') as TimetableResource_begTime,
						ttr.TimetableResource_id,
						ttr.Person_id,
						null as is_pzm
					from 
						v_TimetableResource_lite ttr
					where
						ttr.Person_id is NULL
						AND ttr.Resource_id = DD.Resource_id
						AND ttr.TimetableResource_begTime >= (select cur_dt from vars)
						AND ttr.TimetableResource_begTime < (select upper_dt from vars)
					ORDER BY
						".$typeOrderLpu."
						".$typeOrderTime."
					limit 1
				) ttmsx on true
				";
			} else {
				return strtr('
			left join lateral
			(
				select
					TimetableMedService_id,
					MedService_id,
					TimetableMedService_begTime,
					UslugaComplexMedService_id,
					TimetableResource_id,
					TimetableResource_begTime,
					Resource_id,
					Resource_Name
				from (
					SELECT
						null as TimetableMedService_id,
						DD.MedService_id,
						cast(null as timestamp) as TimetableMedService_begTime,
						DD.UslugaComplexMedService_id,
						ttr.TimetableResource_id,
						ttr.TimetableResource_begTime,
						r.Resource_id,
						r.Resource_Name
					FROM
						v_TimetableResource_lite ttr
						inner join v_Resource r on r.Resource_id = ttr.Resource_id
						inner join v_UslugaComplexResource ucr on ucr.Resource_id = r.Resource_id
					where
						ttr.Person_id is null
						and ucr.UslugaComplexMedService_id = DD.UslugaComplexMedService_id
						AND ttr.TimetableResource_begTime >= (select cur_dt from vars)
						AND ttr.TimetableResource_begTime < (select upper_dt from vars)
						'.$filter_pzmMS.'
					ORDER BY
						'.$typeOrderLpu.'
						'.$typeOrderTime.'
					limit 1
				) ttms2
	
				order by
					case when ttms2.UslugaComplexMedService_id is not null then 0 else 1 end
				limit 1
			) ttmsx on true
			', $all_ttms_params);
			}
		} else {
			return strtr('
			left join lateral
			(
				select
					TimetableMedService_id,
					MedService_id,
					TimetableMedService_begTime,
					UslugaComplexMedService_id,
					TimetableResource_id,
					TimetableResource_begTime,
					Resource_id,
					Resource_Name,
					is_pzm
				from (
					(SELECT
						ttms.TimetableMedService_id,
						ttms.MedService_id,
						ttms.TimetableMedService_begTime,
						ttms.UslugaComplexMedService_id,
						null as TimetableResource_id,
						null as TimetableResource_begTime,
						null as Resource_id,
						null as Resource_Name,
						0 as is_pzm
					FROM
						{all_ttms_join}
					WHERE
						{all_ttms_filters}
						and ttms.MedService_id = dd.MedService_id
						and ttms.UslugaComplexMedService_id is null
					ORDER BY ' . $typeOrderLpu . '
							 ' . $typeOrderTime . '
					limit 1)
					union
	
					(SELECT
						ttms.TimetableMedService_id,
						ttms.MedService_id,
						ttms.TimetableMedService_begTime,
						ttms.UslugaComplexMedService_id,
						null as TimetableResource_id,
						null as TimetableResource_begTime,
						null as Resource_id,
						null as Resource_Name,
						0 as is_pzm
					FROM
						{all_ttms_join}
					WHERE
						{all_ttms_filters}
						and ttms.UslugaComplexMedService_id = dd.UslugaComplexMedService_id
					ORDER BY ' . $typeOrderLpu . '
							 ' . $typeOrderTime . '
					limit 1)
					union
	
					(SELECT
						ttms.TimetableMedService_id,
						ttms.MedService_id,
						ttms.TimetableMedService_begTime,
						ttms.UslugaComplexMedService_id,
						null as TimetableResource_id,
						null as TimetableResource_begTime,
						null as Resource_id,
						null as Resource_Name,
						1 as is_pzm
					FROM
						{all_ttms_join}
					WHERE
						{all_ttms_filters}
						' . $filter_MS . '
						and ttms.UslugaComplexMedService_id is null
					ORDER BY ' . $typeOrderLpu . '
							 ' . $typeOrderTime . '
					limit 1)
					union
	
					(SELECT
						ttms.TimetableMedService_id,
						ttms.MedService_id,
						ttms.TimetableMedService_begTime,
						ttms.UslugaComplexMedService_id,
						null as TimetableResource_id,
						null as TimetableResource_begTime,
						null as Resource_id,
						null as Resource_Name,
						1 as is_pzm
					FROM
						{all_ttms_join}
					WHERE
						{all_ttms_filters}
						' . $filter_uslPZM . '
					ORDER BY ' . $typeOrderLpu . '
							 ' . $typeOrderTime . '
					limit 1)
	
				) ttms2
	
				order by
					ttms2.is_pzm desc,	-- в первую очередь выбирать из пункта забора биоматериала
					case when ttms2.UslugaComplexMedService_id is not null then 0 else 1 end
				limit 1
			) ttmsx on true
			', $all_ttms_params);
		}
	}

	/**
	 * Возвращает параметры для запроса
	 * @return array
	 */
	static function getSqlParams() {
		return self::$params;
	}

	/**
	 * @return array
	 */
	private static function getTplParams() {
		// убираем ненужные джойны, если на лабораторная
		if (self::$isLab === false) {
			UslugaComplex2011SqlData::$from['pzm'] = '';
			UslugaComplex2011SqlData::$from['ucpzm'] = '';
			UslugaComplex2011SqlData::$from['msl'] = '';
		}
		if (self::$isFunc === false || (self::$isFunc === true && self::$formMode != 'ExtJS6')) {
			UslugaComplex2011SqlData::$from['ucres'] = '';
			UslugaComplex2011SqlData::$from['res'] = '';
		}
		$tplParams = array(
			'{select}' => implode("\n,", UslugaComplex2011SqlData::$select),
			'{from}' => implode("\n", UslugaComplex2011SqlData::$from),
			'{where}' => implode("\n AND ", UslugaComplex2011SqlData::$filters),
			'{order}' => UslugaComplex2011SqlData::getOrderBy(),
		);
		return $tplParams;
	}

	/**
	 * @return array
	 */
	private static function getAddFields() {
		$addFields = "";

		$Unique_id = ",cast(ucdouble.UslugaComplex_id as varchar) || coalesce('_' || cast(ucdouble.MedService_id as varchar), '') as Unique_id";
		//$Unique_id = ",cast(ucdouble.UslugaComplex_id as varchar) as Unique_id";
		$Group_id = ",cast(ucdouble.MedService_id as varchar) as Group_id";
		if (self::$isLab) {
			$addFields .= "
					,ucdouble.lab_MedService_id
					,ucdouble.pzm_MedService_id
					,ucdouble.pzm_UslugaComplexMedService_id
					,ucdouble.pzm_Lpu_id
					,ucdouble.pzm_MedServiceType_id
					,ucdouble.pzm_MedServiceType_SysNick
					,ucdouble.pzm_MedService_Name
					,ucdouble.pzm_MedService_Nick
					,ucdouble.pzm_RecordQueue_id
					,ucdouble.AnalyzerTest_Name
					,ucdouble.UCUslugaComplex_Name
				";
			$Group_id = ",cast(ucdouble.MedService_id as varchar) || coalesce('_' || cast(ucdouble.pzm_MedService_id as varchar), '') as Group_id";
			$Unique_id = ",cast(ucdouble.UslugaComplex_id as varchar) || coalesce('_' || cast(ucdouble.MedService_id as varchar), '') || coalesce('_' || cast(ucdouble.pzm_MedService_id as varchar), '') as Unique_id";
		}
		if (self::$isFunc && self::$formMode == 'ExtJS6') {
			$addFields .= "
					,ucdouble.Resource_id
					,ucdouble.Resource_Name
				";
			$Group_id = ",cast(ucdouble.MedService_id as varchar) || coalesce('_' || cast(ucdouble.Resource_id as varchar), '') as Group_id";
			$Unique_id = ",cast(ucdouble.UslugaComplex_id as varchar) || coalesce('_' || cast(ucdouble.MedService_id as varchar), '') || coalesce('_' || cast(ucdouble.Resource_id as varchar), '') as Unique_id";
			if(self::$needTimetable){
				$addFields .= "
					,ucdouble.ttr_Resource_id
					,ucdouble.ttms_MedService_id
				";
			}
		}
		return array(
			'addFields' => $addFields,
			'Unique_id' => $Unique_id,
			'Group_id' => $Group_id
		);
	}

	/**
	 * @param bool $isAdditWith
	 * @return string
	 */
	private static function getTpl($isAdditWith = false) {
		$tpl = '';
		if ($isAdditWith) {
			$tpl .= '
-- addit with
{with}
-- end addit with
			';
		}
		$tpl .= '
select
	-- select
	{select}
	-- end select
from
	-- from
	{from}
	-- end from
WHERE
	-- where
	{where}
	-- end where
ORDER BY
	-- order by
	{order}
	-- end order by
';
		return $tpl;
	}
}

/*
 * for debug
$data = array(
	'Lpu_id' => 999999,
	'userLpu_id' => 531,
	'userLpuBuilding_id' => 531,
	'userLpuUnit_id' => 531,
	'userLpuSection_id' => 531,
	'allowedUslugaComplexAttributeList' => '["lab"]',
	'pmUser_id' => 1,
	'dir' => '',
	'sort' => 'composition',
);
//$data['filterByLpu_id'] = 100083;

if (!UslugaComplexSelectListSqlBuilder::setData($data)) {
	exit(UslugaComplexSelectListSqlBuilder::$error);
}
$sql = UslugaComplexSelectListSqlBuilder::getSql();
$params = UslugaComplexSelectListSqlBuilder::getSqlParams();



$replace = array();
foreach ($params as $param => $value) {
	$replace[':'.$param] = $value;
}
$sql = strtr($sql,$replace);
if ($sql) echo $sql;
else echo 'wrong data';
*/